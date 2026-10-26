<?php
session_start();
require '../connection/connection.php';
require_once 'pakket_mailer.php';

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header('Location: ../index.php?page=login');
    exit;
}

if (!isset($_POST['pakket_id']) || !ctype_digit($_POST['pakket_id'])) {
    $_SESSION['error'] = 'Ongeldig pakket.';
    header('Location: ../index.php?page=courier_overzicht');
    exit;
}

$pakket_id = (int)$_POST['pakket_id'];
$user_id   = (int)$_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT courier_id, is_goedgekeurd
        FROM couriers
        WHERE user_id = :uid
        FOR UPDATE
    ");
    $stmt->execute([':uid' => $user_id]);
    $courier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courier) {
        throw new Exception('Courier niet gevonden.');
    }

    $courier_id     = (int)$courier['courier_id'];
    $is_goedgekeurd = (int)$courier['is_goedgekeurd'];

    $stmt = $pdo->prepare("
        SELECT p.status_id, gp.courier_id
        FROM pakketten p
        JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
        WHERE p.pakket_id = :pid
        FOR UPDATE
    ");
    $stmt->execute([':pid' => $pakket_id]);
    $pakket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pakket) {
        throw new Exception('Pakket niet gevonden.');
    }

    if ((int)$pakket['status_id'] !== 5) {
        throw new Exception('Dit pakket kan niet als bezorgd worden gemarkeerd.');
    }

    if ((int)$pakket['courier_id'] !== $courier_id) {
        throw new Exception('Dit pakket hoort niet bij jou.');
    }

    $timezone = new DateTimeZone('Europe/Amsterdam');
    $dt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        UPDATE pakketten
        SET status_id = 6
        WHERE pakket_id = :pid
          AND status_id = 5
    ");
    $stmt->execute([':pid' => $pakket_id]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception('Geen statuswijziging uitgevoerd');
    }

    $stmt = $pdo->prepare("
        INSERT INTO pakket_status_history
        (pakket_id, status_id, tijdstempel, notitie)
        VALUES (:pid, 6, :ts, 'Pakket bezorgd door courier')
    ");
    $stmt->execute([':pid' => $pakket_id, ':ts' => $dt]);

    if ($is_goedgekeurd === 5) {
        $stmt = $pdo->prepare("
            UPDATE couriers
            SET is_goedgekeurd = 4
            WHERE courier_id = :cid
        ");
        $stmt->execute([':cid' => $courier_id]);
    }

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

    $_SESSION['success'] = 'Pakket succesvol bezorgd!';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../index.php?page=courier_overzicht');
exit;
