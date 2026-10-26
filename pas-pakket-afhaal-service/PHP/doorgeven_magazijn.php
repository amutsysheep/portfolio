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

    $courier_id = (int) $courier['courier_id'];

    $stmt = $pdo->prepare("
        SELECT p.status_id
        FROM pakketten p
        JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
        WHERE p.pakket_id = :pid
          AND gp.courier_id = :cid
          AND p.status_id IN (2,3)
        FOR UPDATE
    ");
    $stmt->execute([':pid' => $pakket_id, ':cid' => $courier_id]);
    $pakket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pakket) {
        throw new Exception('Pakket kan niet doorgegeven worden');
    }

    $timezone = new DateTimeZone('Europe/Amsterdam');
    $dt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

    $oude_status = (int)$pakket['status_id'];
    $nieuwe_status = $oude_status + 1;
    if ($nieuwe_status > 4) $nieuwe_status = 4;

    $stmt = $pdo->prepare("
        UPDATE pakketten
        SET status_id = :sid
        WHERE pakket_id = :pid
          AND status_id = :oud
    ");
    $stmt->execute([
        ':sid' => $nieuwe_status,
        ':pid' => $pakket_id,
        ':oud' => $oude_status
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new Exception('Geen statuswijziging uitgevoerd');
    }

    $stmt = $pdo->prepare("DELETE FROM geclaimde_pakketten WHERE pakket_id = :pid");
    $stmt->execute([':pid' => $pakket_id]);

    $stmt = $pdo->prepare("
        INSERT INTO pakket_status_history (pakket_id, status_id, tijdstempel, notitie)
        VALUES (:pid, :sid, :ts, 'Doorgegeven door courier')
    ");
    $stmt->execute([':pid' => $pakket_id, ':sid' => $nieuwe_status, ':ts' => $dt]);

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

    $_SESSION['success'] = 'Pakket succesvol doorgegeven';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../index.php?page=pakketten_overzicht_courier');
exit;
