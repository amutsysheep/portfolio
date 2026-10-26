<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';  // Load Composer autoload (adjust path if needed)



$dotenv = Dotenv\Dotenv::createImmutable('../../env/phpmailer'); // Adjust path to root

$dotenv->load();



require '../../vendor/phpmailer/phpmailer/src/Exception.php';

require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';

require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

include "../../connection/connection.php";



// Helper function to send OTP email using PHPMailer

function sendOtpEmail($email, $otp) {

    $mail = new PHPMailer(true);



    try {

        $mail->isSMTP();

        $mail->Host       = $_ENV['SMTP_HOST'];

        $mail->SMTPAuth   = true;

        $mail->Username   = $_ENV['SMTP_USER'];

        $mail->Password   = $_ENV['SMTP_PASS'];

        $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;

        $mail->Port       = (int) $_ENV['SMTP_PORT'];



        // Recipients

        $mail->setFrom('info@GreenShift.nl', 'GreenShift');          // Sender email and name

        $mail->addAddress($email);                           // Recipient



        // Content

        $mail->isHTML(true);

        $mail->Subject = 'Your OTP Code for Login';

        $mail->Body    = "Your OTP code is: <b>$otp</b>. Please enter it to complete your login.";

        $mail->AltBody = "Your OTP code is: $otp. Please enter it to complete your login.";



        $mail->send();

    } catch (Exception $e) {

        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

    }

}



$email = $_POST['email'];

$password = $_POST['password'];



// Check user credentials in the database

$sql = "SELECT u.userid,

    r.rolename,

    u.email,

    u.password,

    u.roleId,

    u.active

    FROM user AS u

    INNER JOIN role AS r ON u.roleid = r.roleid

    WHERE u.email = :email AND u.active = 1";



$stmt = $pdo->prepare($sql);

$stmt->bindParam(':email', $email);

$stmt->execute();



// Fetch the user from the database

$user = $stmt->fetch(PDO::FETCH_ASSOC);



if ($user && password_verify($password, $user['password'])) {

    if ($user['active'] == 1) {

        // Generate OTP

        $otp = rand(100000, 999999); // 6-digit OTP

        $otpExpiration = time() + 300; // OTP is valid for 5 minutes (300 seconds)



        // Store OTP in session (you can also store in DB if needed)

        $_SESSION['otp'] = $otp;

        $_SESSION['otp_expiration'] = $otpExpiration;

        $_SESSION['userid_temp'] = $user['userid'];

        $_SESSION['role_temp'] = $user['rolename'];

        $_SESSION['otp_access'] = true;

        // Send OTP email

        sendOtpEmail($user['email'], $otp);



        // Redirect user to OTP verification page

        header("location: ../../index.php?page=otp");

        exit();

    } elseif ($user['active'] == 0) {

        $_SESSION['message'] = "Please check your email for verification.";

        header("location: ../../index.php?page=login");

        exit();

    } elseif ($user['active'] == 0 && $user['confirmtoken'] == "" ) {

        $_SESSION['message'] = "Please contact our admins; your account has been disabled or deleted.";

        header("location: ../../index.php?page=login");

        exit();

    }

} else {

    // Invalid email or password

    $_SESSION['message'] = "Invalid email or password! Or your account is inactive. Please contact our admins.";

    header('Location: ../../index.php?page=login');

    exit();

}

?>



