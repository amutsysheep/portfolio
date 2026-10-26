<?php
session_start();

include "../../connection/connection.php";

$instructorId = $_GET['userid'] ?? null;

if (!$instructorId) {
    $_SESSION['message'] = "Instructor ID not provided.";
    header("Location: ../../index.php?page=editinstructor&panel=Admin&userid=" . $_SESSION['userid']);
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

        // Update address
        $stmt = $pdo->prepare("
            UPDATE address 
            SET address = :address, housenumber = :housenumber, city = :city, postalcode = :zipcode, phonenumber = :phone 
            WHERE addressid = (SELECT addressid FROM user WHERE userid = :instructorId)
        ");
        $stmt->execute([
            ':address'      => $address,
            ':housenumber'  => $housenumber,
            ':city'         => $city,
            ':zipcode'      => $zipcode,
            ':phone'        => $phone,
            ':instructorId' => $instructorId
        ]);

        // Update user
        $query = "UPDATE user SET firstname = :firstname, infix = :infix, lastname = :lastname, email = :email";
        $params = [
            ':firstname'     => $firstname,
            ':infix'         => $infix,
            ':lastname'      => $lastname,
            ':email'         => $email,
            ':instructorId'  => $instructorId
        ];

        $query .= " WHERE userid = :instructorId";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $pdo->commit();

        $_SESSION['message'] = "Instructor details updated successfully!";
        header("Location: ../../index.php?page=instructormangement&panel=Admin&userid=" . $_SESSION['userid']);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: ../../index.php?page=editinstructor&panel=Admin&userid=" . $_SESSION['userid']);
        exit();
    }
}
?>
