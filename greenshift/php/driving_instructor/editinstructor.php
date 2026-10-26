<?php
session_start();

include "../../connection/connection.php";

$userid = $_SESSION['userid'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname   = trim($_POST['firstname']);
    $infix       = trim($_POST['infix']);
    $lastname    = trim($_POST['lastname']);
    $email       = trim($_POST['email']);
    $password    = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
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
            WHERE addressid = (SELECT addressid FROM user WHERE userid = :userid)
        ");
        $stmt->execute([
            ':address'      => $address,
            ':housenumber'  => $housenumber,
            ':city'         => $city,
            ':zipcode'      => $zipcode,
            ':phone'        => $phone,
            ':userid'       => $userid
        ]);

        // Update user
        $query = "UPDATE user SET firstname = :firstname, infix = :infix, lastname = :lastname, email = :email";
        $params = [
            ':firstname' => $firstname,
            ':infix'     => $infix,
            ':lastname'  => $lastname,
            ':email'     => $email,
            ':userid'    => $userid
        ];

        if ($password) {
            $query .= ", password = :password";
            $params[':password'] = $password;
        }

        $query .= " WHERE userid = :userid";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $pdo->commit();

        $_SESSION['message'] = "Your details have been updated successfully!";
        header("Location: ../../index.php?page=editinstructor&panel=Driving_Instructor");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: ../../index.php?page=editinstructor&panel=Driving_Instructor");
        exit();
    }
}
