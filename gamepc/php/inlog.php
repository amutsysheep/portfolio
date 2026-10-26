<?php
session_start();
include '../../PrivateW/connection_GamePC.php'; // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../composer/vendor/autoload.php'; // Include Composer's autoload file

if (isset($_POST['Email']) && isset($_POST['Wachtwoord'])) {
    $email = $_POST['Email'];
    $Wachtwoord = $_POST['Wachtwoord'];

    try {
        // Fetch hashed password, role, and user ID from the database
        $sql = "SELECT Wachtwoord, Email, Rol_id, User_id FROM gebruikers WHERE Email = :Email";
        $statement = $pdo->prepare($sql);
        $statement->bindParam(":Email", $email);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $storedPassword = $result['Wachtwoord'];
            $Rol_id = $result['Rol_id'];
            $User_id = $result['User_id'];

            if (password_verify($Wachtwoord, $storedPassword)) {
                // Generate OTP
                $otp = rand(100000, 999999); // 6-digit OTP
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_expiry'] = time() + 300; // OTP valid for 5 minutes
                $_SESSION['User_id_temp'] = $User_id;
                $_SESSION['Rol_temp'] = $Rol_id;

                // Send OTP email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->SMTPDebug = 3; // Verbose debug output
                    $mail->Debugoutput = function($str, $level) {
                        error_log("SMTP DEBUG [$level]: $str"); // Log to PHP error log
                        echo "SMTP DEBUG [$level]: $str<br>"; // Display on the page (for development)
                    };
                    // Server settings
                    $mail->isSMTP();                                            // Set mailer to use SMTP
                    $mail->Host = 'smtp.gmail.com';                               // Set SMTP server
                    $mail->SMTPAuth = true;                                       // Enable SMTP authentication
                    $mail->Username = 'gamepcverify@gmail.com';                   // SMTP username
                    $mail->Password ='jtgsvddxihehxqyn';                       // SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
                    $mail->Port = 587;

                    // Disable SSL certificate verification
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // Recipients
                    $mail->setFrom('gamepcverify@gmail.com', 'GamePC Verificatie');
                    $mail->addAddress($email);                                    // Add recipient

                    // Content
                    $mail->isHTML(true);                                          // Set email format to HTML
                    $mail->Subject = 'Uw verificatiecode';
                    $mail->Body    = "Uw verificatiecode is: $otp";

                    $mail->send();

                    // Redirect to OTP verification page
                    header('Location: ../index.php?page=OTPVerify');
                    exit();
                } catch (Exception $e) {
                    $_SESSION['Alert'] = "Er is een fout opgetreden bij het verzenden van de e-mail. Probeer opnieuw.";
                    header('Location: ../index.php?page=inlog');
                    exit();
                }
            } else {
                $_SESSION['Alert'] = 'Fout: Onjuist wachtwoord.';
                header('Location: ../index.php?page=inlog');
                exit();
            }
        } else {
            $_SESSION['Alert'] = 'Fout: Geen gebruiker met dit e-mailadres.';
            header('Location: ../index.php?page=inlog');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['Alert'] = 'Fout: Database probleem.';
        header('Location: ../index.php?page=inlog');
        exit();
    }
} else {
    $_SESSION['Alert'] = 'Vul zowel e-mail als wachtwoord in.';
    header('Location: ../index.php?page=inlog');
    exit();
}
?>
