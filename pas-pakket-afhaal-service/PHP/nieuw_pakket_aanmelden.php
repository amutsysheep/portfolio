<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../Private/connection.php';
require_once 'pakket_mailer.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function back_to_form(): void {
    header('Location: ../index.php?page=pakket_aanmelden_klant');
    exit;
}

function generate_track_trace_code(): string {
    return substr(bin2hex(random_bytes(16)), 0, 12);
}

function get_status_id(PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT status_id FROM pakket_status WHERE status = ? LIMIT 1");
    $stmt->execute(['Aangemeld']);
    $status_id = $stmt->fetchColumn();

    if (!$status_id) {
        $status_id = $pdo->query("SELECT status_id FROM pakket_status ORDER BY status_id ASC LIMIT 1")->fetchColumn();
    }

    if (!$status_id) {
        throw new Exception("Geen statussen gevonden in pakket_status.");
    }

    return (int)$status_id;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['pakket_fout'] = 'Ongeldige aanvraag (geen POST).';
    back_to_form();
}

/* TODO: zodra login werkt -> $_SESSION['user_id'] gebruiken */
$user_id = 10;

/* ophalen */
$lengte  = (int)($_POST['lengte'] ?? 0);
$breedte = (int)($_POST['breedte'] ?? 0);
$hoogte  = (int)($_POST['hoogte'] ?? 0);
$gewicht = (float)($_POST['gewicht'] ?? 0);
$omschrijving = trim($_POST['omschrijving'] ?? '');

$ophaal_postcode   = trim($_POST['ophaal_postcode'] ?? '');
$ophaal_huisnummer = trim($_POST['ophaal_huisnummer'] ?? '');
$ophaal_straat     = trim($_POST['ophaal_straat'] ?? '');
$ophaal_plaats     = trim($_POST['ophaal_plaats'] ?? '');

$zelfde_als_ophaal = isset($_POST['zelfde_als_ophaal']) && $_POST['zelfde_als_ophaal'] == '1';

$aflever_postcode   = trim($_POST['aflever_postcode'] ?? '');
$aflever_huisnummer = trim($_POST['aflever_huisnummer'] ?? '');
$aflever_straat     = trim($_POST['aflever_straat'] ?? '');
$aflever_plaats     = trim($_POST['aflever_plaats'] ?? '');

$ophaaldatum  = $_POST['ophaaldatum'] ?? '';
$afleverdatum = $_POST['afleverdatum'] ?? '';

$errors = [];

if ($lengte <= 0 || $breedte <= 0 || $hoogte <= 0) $errors[] = 'Afmetingen moeten groter dan 0 zijn.';
if ($gewicht <= 0) $errors[] = 'Gewicht moet groter dan 0 zijn.';

if ($ophaal_postcode === '' || $ophaal_huisnummer === '' || $ophaal_straat === '' || $ophaal_plaats === '') {
    $errors[] = 'Vul alle velden van het ophaaladres in.';
}

if ($zelfde_als_ophaal) {
    $aflever_postcode   = $ophaal_postcode;
    $aflever_huisnummer = $ophaal_huisnummer;
    $aflever_straat     = $ophaal_straat;
    $aflever_plaats     = $ophaal_plaats;
} else {
    if ($aflever_postcode === '' || $aflever_huisnummer === '' || $aflever_straat === '' || $aflever_plaats === '') {
        $errors[] = 'Vul alle velden van het afleveradres in (of vink "zelfde als ophaal" aan).';
    }
}

try {
    $today = new DateTime('today');
    $tomorrow = (clone $today)->modify('+1 day');

    if ($ophaaldatum === '' || $afleverdatum === '') throw new Exception('Datums ontbreken.');

    $d1 = new DateTime($ophaaldatum);
    $d2 = new DateTime($afleverdatum);

    if ($d1 < $tomorrow) $errors[] = 'Ophaaldatum moet minimaal morgen zijn.';

    $min = (clone $d1)->modify('+1 day');
    $max = (clone $d1)->modify('+7 days');

    if ($d2 < $min || $d2 > $max) $errors[] = 'Afleverdatum moet 1–7 dagen na ophaaldatum liggen.';
} catch (Exception $e) {
    $errors[] = 'Ongeldige datum ingevuld.';
}

if ($errors) {
    $_SESSION['pakket_fout'] = implode(' | ', $errors);
    back_to_form();
}

$ophaaladres = trim($ophaal_straat . ' ' . $ophaal_huisnummer . ', ' . $ophaal_postcode . ' ' . $ophaal_plaats);
$bezorgadres = trim($aflever_straat . ' ' . $aflever_huisnummer . ', ' . $aflever_postcode . ' ' . $aflever_plaats);

try {
    $pdo->beginTransaction();

    $status_id = get_status_id($pdo);

    // Track & trace (zoals je groepsgenoot)
    do {
        $tt = generate_track_trace_code();
        $check = $pdo->prepare("SELECT COUNT(*) FROM pakketten WHERE track_trace_code = ?");
        $check->execute([$tt]);
    } while ((int)$check->fetchColumn() > 0);

    $pak = $pdo->prepare("
        INSERT INTO pakketten (
            user_id, ophaaladres, bezorgadres,
            gewicht, breedte, hoogte, lengte,
            status_id, pakketten_aangemeld,
            track_trace_code, ophaal_datum, aflever_datum
        )
        VALUES (
            :user_id, :ophaaladres, :bezorgadres,
            :gewicht, :breedte, :hoogte, :lengte,
            :status_id, NOW(),
            :tt, :ophaal_datum, :aflever_datum
        )
    ");

    $pak->execute([
        ':user_id' => $user_id,
        ':ophaaladres' => $ophaaladres,
        ':bezorgadres' => $bezorgadres,
        ':gewicht' => $gewicht,
        ':breedte' => $breedte,
        ':hoogte' => $hoogte,
        ':lengte' => $lengte,
        ':status_id' => $status_id,
        ':tt' => $tt,
        ':ophaal_datum' => $ophaaldatum,
        ':aflever_datum' => $afleverdatum,
    ]);

    $pakket_id = (int)$pdo->lastInsertId();

    $timezone = new DateTimeZone('Europe/Amsterdam');
    $dt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

    $hist = $pdo->prepare("
        INSERT INTO pakket_status_history (pakket_id, status_id, tijdstempel, notitie)
        VALUES (:pakket_id, :status_id, :ts, :notitie)
    ");
    $hist->execute([
        ':pakket_id' => $pakket_id,
        ':status_id' => $status_id,
        ':ts' => $dt,
        ':notitie' => $omschrijving !== '' ? $omschrijving : 'Pakket aangemeld.',
    ]);

    $pdo->commit();

    // ✅ Mail versturen (zoals jij), maar mailfout mag DB nooit slopen
    try {
        $data = pakketStatusMailData($pdo, $pakket_id);
        $trackUrl = "http://localhost/pas-pakket-afhaal-service/index.php?page=pakket_traceren&code=" . urlencode($data['track_trace_code']);

        stuurPakketStatusMail(
            $data['klant_email'],
            $data['track_trace_code'],
            $data['status'],
            $dt,
            $trackUrl
        );
    } catch (Exception $mailE) {
        // Optioneel: zet een sessie melding als je wil debuggen
        // $_SESSION['mail_fout'] = $mailE->getMessage();
    }

    $_SESSION['pakket_bevestiging'] = "Pakket aangemeld! Track & Trace: $tt";
    back_to_form();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['pakket_fout'] = "DB fout: " . $e->getMessage();
    back_to_form();
}
