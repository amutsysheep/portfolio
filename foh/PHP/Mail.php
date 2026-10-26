<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendTwoFactorCode($email, $code)
{
    $envPath = __DIR__ . '/../env/phpmailer/.env';

    if (!file_exists($envPath)) {
        return false;
    }

    $env = parse_ini_file($envPath);

    if ($env === false) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'];
        $mail->Password = $env['SMTP_PASS'];
        $mail->Port = (int)$env['SMTP_PORT'];

        if ($env['SMTP_SECURE'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $env['SMTP_USER'],
            'Flex Opera House'
        );

        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Je verificatiecode | Flex Opera House';

        $mail->Body = '
        <!DOCTYPE html>
        <html lang="nl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verificatiecode - Flex Opera House</title>
        </head>

        <body style="
            margin: 0;
            padding: 0;
            background-color: #030812;
            font-family: Arial, Helvetica, sans-serif;
            color: #ffffff;
        ">

            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background-color: #030812; padding: 40px 15px;">

                <tr>
                    <td align="center">

                        <table width="600" cellpadding="0" cellspacing="0" border="0"
                               style="
                                   max-width: 600px;
                                   width: 100%;
                                   background-color: #0a1224;
                                   border-radius: 18px;
                                   overflow: hidden;
                                   border: 1px solid #162660;
                               ">

                            <!-- Header -->
                            <tr>
                                <td align="center"
                                    style="
                                        padding: 35px 30px;
                                        background: linear-gradient(
                                            135deg,
                                            #025EC4,
                                            #0ECCED
                                        );
                                    ">

                                    <div style="
                                        font-size: 28px;
                                        font-weight: bold;
                                        letter-spacing: 1px;
                                        color: #ffffff;
                                    ">
                                        FLEX OPERA HOUSE
                                    </div>

                                    <div style="
                                        margin-top: 8px;
                                        font-size: 13px;
                                        color: #D0E6FD;
                                        letter-spacing: 2px;
                                        text-transform: uppercase;
                                    ">
                                        Veilig inloggen
                                    </div>

                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 40px 35px;">

                                    <h1 style="
                                        margin: 0 0 15px;
                                        font-size: 25px;
                                        color: #ffffff;
                                    ">
                                        Bevestig je login
                                    </h1>

                                    <p style="
                                        margin: 0 0 25px;
                                        font-size: 15px;
                                        line-height: 1.7;
                                        color: #D0E6FD;
                                    ">
                                        Er is zojuist geprobeerd om in te loggen
                                        op je Flex Opera House-account.
                                    </p>

                                    <p style="
                                        margin: 0 0 15px;
                                        font-size: 15px;
                                        color: #ffffff;
                                    ">
                                        Gebruik onderstaande verificatiecode:
                                    </p>

                                    <!-- Code -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center"
                                                style="
                                                    padding: 25px;
                                                    background-color: #030812;
                                                    border: 1px solid #025EC4;
                                                    border-radius: 12px;
                                                ">

                                                <span style="
                                                    font-size: 36px;
                                                    font-weight: bold;
                                                    letter-spacing: 10px;
                                                    color: #0ECCED;
                                                ">
                                                    ' . htmlspecialchars($code) . '
                                                </span>

                                            </td>
                                        </tr>
                                    </table>

                                    <p style="
                                        margin: 25px 0 0;
                                        font-size: 14px;
                                        line-height: 1.6;
                                        color: #D0E6FD;
                                    ">
                                        Deze code is <strong style="color: #ffffff;">
                                        5 minuten geldig</strong>.
                                    </p>

                                    <div style="
                                        margin-top: 30px;
                                        padding: 18px;
                                        background-color: #101a30;
                                        border-left: 3px solid #0ECCED;
                                        border-radius: 6px;
                                    ">

                                        <p style="
                                            margin: 0;
                                            font-size: 13px;
                                            line-height: 1.6;
                                            color: #D0E6FD;
                                        ">
                                            <strong style="color: #ffffff;">
                                                Was jij dit niet?
                                            </strong><br>
                                            Dan hoef je niets te doen.
                                            Je kunt deze e-mail veilig negeren.
                                        </p>

                                    </div>

                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td align="center"
                                    style="
                                        padding: 25px 30px;
                                        background-color: #060d1c;
                                        border-top: 1px solid #162660;
                                    ">

                                    <p style="
                                        margin: 0 0 8px;
                                        font-size: 13px;
                                        color: #D0E6FD;
                                    ">
                                        Flex Opera House
                                    </p>

                                    <p style="
                                        margin: 0;
                                        font-size: 11px;
                                        color: #6f7f9d;
                                    ">
                                        Dit is een automatisch verzonden e-mail.
                                        Je kunt hier niet op antwoorden.
                                    </p>

                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>

            </table>

        </body>
        </html>
        ';

        $mail->AltBody =
            "Flex Opera House\n\n" .
            "Je verificatiecode is: " . $code . "\n\n" .
            "Deze code is 5 minuten geldig.\n\n" .
            "Was jij dit niet? Dan kun je deze e-mail negeren.";

        return $mail->send();

    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}