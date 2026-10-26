<?php
session_start();
require "../../../private/connection.php";

// Alleen admins
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    $_SESSION['message'] = "Geen rechten.";
    header("Location: ../../index.php?page=login");
    exit();
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['message'] = "Ongeldige courier.";
    header("Location: ../../index.php?page=courier_beheer");
    exit();
}

$stmtCourier = $pdo->prepare("SELECT courier_id FROM couriers WHERE user_id = :user_id LIMIT 1");
$stmtCourier->execute(['user_id' => $user_id]);
$courier_id = $stmtCourier->fetchColumn();

if (!$courier_id) {
    $_SESSION['message'] = "Courier niet gevonden.";
    header("Location: ../../index.php?page=courier_beheer");
    exit();
}

// Check of courier nog actieve pakketten heeft
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM geclaimde_pakketten 
    WHERE courier_id = :courier_id 
      AND status_id NOT IN (6, 8)
");
$stmt->execute(['courier_id' => $courier_id]);
$actievePakketten = $stmt->fetchColumn();

// Bepaal nieuwe status
if ($actievePakketten > 0) {
    $nieuweStatus = 5; //Courier heeft nog pakketten
    $melding = "Courier wordt verwijderd zodra alle pakketten zijn afgerond.";
} else {
    // Direct verwijderd
    $nieuweStatus = 4; //Courier heeft geen pakketten
    $melding = "Courier succesvol verwijderd.";
}

// Update status
$update = $pdo->prepare("
    UPDATE couriers 
    SET is_Goedgekeurd = :status 
    WHERE user_id = :user_id
");
$update->execute([
    'status' => $nieuweStatus,
    'user_id' => $user_id
]);

$_SESSION['message'] = $melding;
header("Location: ../index.php?page=courier_beheer");
exit();
