<?php
session_start();

if (!isset($_SESSION['otp_access']) || $_SESSION['otp_access'] !== true) {
    header("Location: ../login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredOtp = $_POST['otp'];

    if (isset($_SESSION['otp']) && time() < $_SESSION['otp_expiration']) {
        if ($enteredOtp == $_SESSION['otp']) {
            // OTP is correct and not expired
            // Proceed to login the user
            $_SESSION['userid'] = $_SESSION['userid_temp'];
            $_SESSION['rolename'] = $_SESSION['role_temp'];

            // Remove OTP and temporary user details from session
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiration']);
            unset($_SESSION['userid_temp']);
            unset($_SESSION['role_temp']);

            // Redirect based on user role
            if ($_SESSION['rolename'] == 'Admin') {
                header("location: ../../index.php?page=home&panel=Admin");
                unset($_SESSION['otp_access']);
                exit();
            } elseif ($_SESSION['rolename'] == 'Driving_Instructor') {
                header("location: ../../index.php?page=home&panel=Driving_Instructor");
                unset($_SESSION['otp_access']);
                exit();
            }elseif ($_SESSION['rolename'] == 'Client') {
                header("location: ../../index.php?page=home");
                unset($_SESSION['otp_access']);
                exit();
            }
        } else {
            // OTP is incorrect
            $_SESSION['message'] = "Invalid OTP!";
            header("location: index.php?page=home");
            exit();
        }
    } else {
        // OTP has expired
        $_SESSION['message'] = "OTP has expired. Please request a new one.";
        header("location: index.php?page=home");
        exit();
    }
}

?>
