<?php
session_start();
require '../connection/connection.php';
require_once 'pakket_mailer.php';

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header('Location: ../index.php?page=login');
    exit;
}

if (!isset($_POST['pakket_id']) || !ctype_digit($_POST['pakket_id'])) {
    $_SESSION['error'] = 'Ongeldig pakket';
    header('Location: ../index.php?page=pakketten_overzicht_courier');
    exit;
}

$pakket_id = (int) $_POST['pakket_id'];
$user_id   = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT courier_id
        FROM couriers
        WHERE user_id = :uid
        FOR UPDATE
    ");
    $stmt->execute([':uid' => $user_id]);
    $courier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courier) {
        throw new Exception('Courier niet gevonden');
    }



    $courier_id = (int)$courier['courier_id'];

    $stmt = $pdo->prepare("
        SELECT p.status_id, gp.courier_id AS geclaimd_courier
        FROM pakketten p
        LEFT JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
        WHERE p.pakket_id = :pid
        FOR UPDATE
    ");
    $stmt->execute([':pid' => $pakket_id]);
    $pakket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pakket) {
        throw new Exception('Pakket niet gevonden');
    }

    $status_id        = (int)$pakket['status_id'];
    $geclaimd_courier = $pakket['geclaimd_courier'];

    if (($status_id === 2 || $status_id === 4) && ($geclaimd_courier === null || (int)$geclaimd_courier === $courier_id)) {

        if ($geclaimd_courier === null) {
            $stmt = $pdo->prepare("
                INSERT INTO geclaimde_pakketten (pakket_id, courier_id, ophaal_datum)
                VALUES (:pid, :cid, NOW())
            ");
            $stmt->execute([':pid' => $pakket_id, ':cid' => $courier_id]);
        }

        $nieuwe_status = ($status_id === 2) ? 3 : 5;

        $timezone = new DateTimeZone('Europe/Amsterdam');
        $dt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            UPDATE pakketten
            SET status_id = :sid
            WHERE pakket_id = :pid
              AND status_id = :oud
        ");
        $stmt->execute([
            ':sid' => $nieuwe_status,
            ':pid' => $pakket_id,
            ':oud' => $status_id
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new Exception('Geen statuswijziging uitgevoerd');
        }

        $stmt = $pdo->prepare("
            INSERT INTO pakket_status_history (pakket_id, status_id, tijdstempel, notitie)
            VALUES (:pid, :sid, :ts, 'Pakket opgehaald door courier')
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

        $_SESSION['success'] = 'Pakket succesvol opgehaald!';

    } else {
        throw new Exception('Dit pakket kan niet door jou opgehaald worden');
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../index.php?page=pakketten_overzicht_courier');
exit;
