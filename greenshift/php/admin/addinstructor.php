<?php
session_start();
include "../../connection/connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $firstname  = trim($_POST['firstname']);
    $infix      = trim($_POST['infix']);
    $lastname   = trim($_POST['lastname']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address    = trim($_POST['address']);
    $zipcode    = trim($_POST['zipcode']);
    $city       = trim($_POST['residence']);
    $housenumber= isset($_POST['housenumber']) ? trim($_POST['housenumber']) : '';
    $phone      = isset($_POST['phonenumber']) ? trim($_POST['phonenumber']) : '';

    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;
    $confirmationtoken = bin2hex(random_bytes(16));
    $active = 1;


    // Email exists check

    $Stmt = $pdo->prepare("SELECT email FROM user WHERE email = :email");

    $Stmt->bindParam(':email', $email);

    $Stmt->execute();


    if ($Stmt->rowCount() > 0) {

        $_SESSION['message'] = 'E-mailadres bestaat al. Kies een ander e-mailadres.';

        header('Location: ../../index.php?page=instructormangement&panel=Admin');

        exit();

    }

    try {
        $pdo->beginTransaction();

        // 1. Insert into address
        $stmt = $pdo->prepare("
            INSERT INTO address (first_name, infix, last_name, address, housenumber, city, postalcode, phonenumber) 
            VALUES (:first_name, :infix, :last_name, :address, :housenumber, :city, :postalcode, :phonenumber)
        ");
        $stmt->execute([
            ':first_name' => $firstname,
            ':infix' => $infix,
            ':last_name' => $lastname,
            ':address' => $address,
            ':housenumber' => $housenumber,
            ':city' => $city,
            ':postalcode' => $zipcode,
            ':phonenumber' => $phone
        ]);
        $addressid = $pdo->lastInsertId();

        // 2. Check or create role "Driving_Instructor"
        $stmt = $pdo->prepare("SELECT roleid FROM role WHERE rolename = :rolename LIMIT 1");
        $stmt->execute([':rolename' => 'Driving_Instructor']);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            $stmt = $pdo->prepare("INSERT INTO role (rolename) VALUES (:rolename)");
            $stmt->execute([':rolename' => 'Driving_Instructor']);
            $roleid = $pdo->lastInsertId();
        } else {
            $roleid = $role['roleid'];
        }

        // 3. Insert into user
        $stmt = $pdo->prepare("
            INSERT INTO user (firstname, infix, lastname, email, password, roleid, addressid, created_at, updated_at, last_login, confirmationtoken, active) 
            VALUES (:firstname, :infix, :lastname, :email, :password, :roleid, :addressid, :created_at, :updated_at, NULL, :confirmationtoken, :active)
        ");
        $stmt->execute([
            ':firstname' => $firstname,
            ':infix' => $infix,
            ':lastname' => $lastname,
            ':email' => $email,
            ':password' => $password,
            ':roleid' => $roleid,
            ':addressid' => $addressid,
            ':created_at' => $created_at,
            ':updated_at' => $updated_at,
            ':confirmationtoken' => $confirmationtoken,
            ':active' => $active
        ]);
        $userid = $pdo->lastInsertId();

        // 4. Insert into instructors table
        $stmt = $pdo->prepare("
            INSERT INTO instructors (user_id, address_id, name, email, phone) 
            VALUES (:user_id, :address_id, :name, :email, :phone)
        ");
        $stmt->execute([
            ':user_id' => $userid,
            ':address_id' => $addressid,
            ':name' => $firstname . ($infix ? ' ' . $infix : '') . ' ' . $lastname,
            ':email' => $email,
            ':phone' => $phone
        ]);

        $pdo->commit();

        header("Location: ../../index.php?page=instructormangement&panel=Admin");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
