<?php
session_start();
include "../../connection/connection.php";

$studentId = $_GET['userid'] ?? null;

if (!$studentId) {
    $_SESSION['message'] = "Geen leerling geselecteerd.";
    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();
}

// Verwerk formulier indien verzonden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname   = trim($_POST['firstname']);
    $infix       = trim($_POST['infix']);
    $lastname    = trim($_POST['lastname']);
    $email       = trim($_POST['email']);
    $address     = trim($_POST['address']);
    $zipcode     = trim($_POST['zipcode']);
    $city        = trim($_POST['residence']);
    $housenumber = trim($_POST['housenumber']);
    $phone       = trim($_POST['phonenumber'] ?? '');

    try {
        $pdo->beginTransaction();

        // Adres updaten
        $stmt = $pdo->prepare("
            UPDATE address 
            SET address = :address, housenumber = :housenumber, city = :city, postalcode = :zipcode, phonenumber = :phone 
            WHERE addressid = (SELECT addressid FROM user WHERE userid = :studentId)
        ");
        $stmt->execute([
            ':address'     => $address,
            ':housenumber' => $housenumber,
            ':city'        => $city,
            ':zipcode'     => $zipcode,
            ':phone'       => $phone,
            ':studentId'   => $studentId
        ]);

        // Gebruiker updaten
        $query = "UPDATE user 
                  SET firstname = :firstname, infix = :infix, lastname = :lastname, email = :email
                  WHERE userid = :studentId";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':firstname' => $firstname,
            ':infix'     => $infix,
            ':lastname'  => $lastname,
            ':email'     => $email,
            ':studentId' => $studentId
        ]);

        $pdo->commit();

        $_SESSION['message'] = "Leerlinggegevens succesvol bijgewerkt!";
        header("Location: ../../index.php?page=studentoverview&panel=Admin");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Fout bij bijwerken: " . $e->getMessage();
        header("Location: ../../index.php?page=editstudent&panel=Admin&userid=" . $studentId);
        exit();
    }
}
?>
