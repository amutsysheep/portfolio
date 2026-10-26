<?php
session_start();
require '../connection/connection.php';
require_once 'pakket_mailer.php';

/* ===== ROL CHECK ===== */
if (!isset($_SESSION['rol_id']) || !in_array((int)$_SESSION['rol_id'], [2,3], true)) {
    header('Location: ../index.php?page=geen_toegang');
    exit;
}

/* ===== INPUT CHECK ===== */
if (!isset($_POST['pakket_id']) || !ctype_digit($_POST['pakket_id'])) {
    $_SESSION['error'] = 'Ongeldig pakket ID';
    header('Location: ../index.php?page=pakketten_overzicht_courier');
    exit;
}

$pakket_id = (int) $_POST['pakket_id'];
$user_id   = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    /* ===== 1️⃣ Haal courier + goedkeuring op ===== */
    $stmt = $pdo->prepare("
        SELECT courier_id, is_goedgekeurd
        FROM couriers
        WHERE user_id = :uid
        FOR UPDATE
    ");
    $stmt->execute([':uid' => $user_id]);
    $courier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courier) {
        throw new Exception('Courier niet gevonden');
    }

    if ((int)$courier['is_goedgekeurd'] === 5) {
        throw new Exception('Je kunt geen pakketten meer claimen');
    }

    $courier_id = (int) $courier['courier_id'];

    /* ===== 2️⃣ Haal huidige status + check claimbaar ===== */
    $stmt = $pdo->prepare("
        SELECT p.status_id
        FROM pakketten p
        LEFT JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
        WHERE p.pakket_id = :pid
          AND gp.pakket_id IS NULL
        FOR UPDATE
    ");
    $stmt->execute([':pid' => $pakket_id]);
    $pakket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pakket || (int)$pakket['status_id'] !== 1) {
        throw new Exception('Dit pakket kan niet geclaimd worden');
    }

    $timezone = new DateTimeZone('Europe/Amsterdam');
    $dt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

    $oude_status = (int)$pakket['status_id'];
    $nieuwe_status = $oude_status + 1;

    /* ===== 3️⃣ Update pakketstatus (alleen als oud nog klopt) ===== */
    $stmt = $pdo->prepare("
        UPDATE pakketten
        SET status_id = :nieuw
        WHERE pakket_id = :pid
          AND status_id = :oud
    ");
    $stmt->execute([
        ':nieuw' => $nieuwe_status,
        ':pid'   => $pakket_id,
        ':oud'   => $oude_status
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception('Geen statuswijziging uitgevoerd');
    }

    /* ===== 4️⃣ Insert geclaimd pakket ===== */
    $stmt = $pdo->prepare("
        INSERT INTO geclaimde_pakketten (pakket_id, courier_id, ophaal_datum)
        VALUES (:pid, :cid, CURDATE())
    ");
    $stmt->execute([
        ':pid' => $pakket_id,
        ':cid' => $courier_id
    ]);

    /* ===== 5️⃣ Status history ===== */
    $stmt = $pdo->prepare("
        INSERT INTO pakket_status_history
        (pakket_id, status_id, tijdstempel, notitie)
        VALUES (:pid, :sid, :ts, 'Geclaimd door courier')
    ");
    $stmt->execute([
        ':pid' => $pakket_id,
        ':sid' => $nieuwe_status,
        ':ts'  => $dt
    ]);

    $pdo->commit();

    $data = pakketStatusMailData($pdo, $pakket_id);
    $trackUrl =
        "http://localhost/pas-pakket-afhaal-service/index.php?page=pakket_traceren&code=" .
        urlencode($data['track_trace_code']);

    stuurPakketStatusMail(
        $data['klant_email'],
        $data['track_trace_code'],
        $data['status'],
        $dt,
        $trackUrl
    );

    $_SESSION['success'] = 'Pakket succesvol geclaimd';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = 'Pakket kan niet geclaimd worden: ' . $e->getMessage();
}

header('Location: ../index.php?page=pakketten_overzicht_courier');
exit;
