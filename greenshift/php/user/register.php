<?php

session_start();

require_once "../../connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../');
    exit();
}

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('../../env/phpmailer');
$dotenv->load();
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

// Helper function om bevestigingsmail te sturen
function sendConfirmationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int) $_ENV['SMTP_PORT'];

        $mail->setFrom('info@greenshift.nl', 'GreenShift');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Bevestig uw account';
        $mail->Body    = "Klik op de onderstaande link om uw account te bevestigen:<br><br>
                          <a href='http://localhost/greenshift/php/user/confirm.php?token=$token'>Account bevestigen</a>";
        $mail->AltBody = "Klik op de onderstaande link om uw account te bevestigen:\n\n
                          http://localhost/php/confirm.php?token=$token";

        $mail->send();
    } catch (Exception $e) {
        echo "Bericht kon niet worden verzonden. Mailer fout: {$mail->ErrorInfo}";
    }
}

// Helperfunctie: Adres valideren met OSM
function validateAddressWithOSM($street, $housenumber, $zipcode, $city) {
    $address = urlencode("{$street}, {$housenumber}, {$zipcode}, {$city}");
    $url = "https://nominatim.openstreetmap.org/search?q=$address&format=json";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: BogamoApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) return false;

    $data = json_decode($response, true);
    return !empty($data) ? $data[0] : false;
}

// POST data ophalen
$firstname = $_POST['firstname'] ?? '';
$infix = $_POST['infix'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$city = $_POST['city'] ?? '';
$street = $_POST['address'] ?? '';
$zipcode = $_POST['zipcode'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$repeat_password = $_POST['password_confirm'] ?? '';
$housenumber = $_POST['housenumber'] ?? '';

// Verplicht veld-check
$requiredFields = [
    'firstname' => $firstname,
    'lastname' => $lastname,
    'city' => $city,
    'address' => $street,
    'zipcode' => $zipcode,
    'email' => $email,
    'password' => $password,
    'password_confirm' => $repeat_password,
    'housenumber' => $housenumber,
];

foreach ($requiredFields as $fieldName => $fieldValue) {
    if (empty(trim($fieldValue))) {
        $_SESSION['message'] = ucfirst(str_replace('-', ' ', $fieldName)) . ' is verplicht.';
        $_SESSION['register-history'] = [
            'firstname' => $firstname,
            'infix' => $infix,
            'lastname' => $lastname,
            'city' => $city,
            'address' => $street,
            'zipcode' => $zipcode,
            'housenumber' => $housenumber,
            'email' => $email,
        ];
        header('Location: ../../index.php?page=register');
        exit();
    }
}

// Formdata bewaren
$formData = [
    'firstname' => $firstname,
    'infix' => $infix,
    'lastname' => $lastname,
    'city' => $city,
    'address' => $street,
    'zipcode' => $zipcode,
    'housenumber' => $housenumber,
    'email' => $email,
];

// Bestaat e-mail al?
$Stmt = $pdo->prepare("SELECT email FROM user WHERE email = :email");
$Stmt->bindParam(':email', $email);
$Stmt->execute();

if ($Stmt->rowCount() > 0) {
    $_SESSION['message'] = 'Dit e-mailadres bestaat al. Kies een ander adres.';
    $_SESSION['register-history'] = $formData;
    header('Location: ../../index.php?page=register');
    exit();
}

// Wachtwoorden gelijk?
if ($password !== $repeat_password) {
    $_SESSION['message'] = "Wachtwoorden komen niet overeen.";
    $_SESSION['register-history'] = $formData;
    header('Location: ../../index.php?page=register');
    exit();
}

// Wachtwoord sterkte
if (!(strlen($password) >= 8) || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    $_SESSION['message'] = 'Het wachtwoord moet minimaal 15+ tekens bevatten, inclusief een hoofdletter, een kleine letter en een speciaal teken.';
    $_SESSION['register-history'] = $formData;
    header('Location: ../../index.php?page=register');
    exit();
}

// Adrescontrole
$addressCheck = validateAddressWithOSM($street, $housenumber ,$zipcode, $city);
if (!$addressCheck) {
    $_SESSION['message'] = 'Ongeldig adres. Controleer uw invoer en probeer opnieuw.';
    $_SESSION['register-history'] = $formData;
    header('Location: ../../index.php?page=register');
    exit();
}

// Alles oké, gegevens opslaan
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(16));

$pdo->beginTransaction();
try {
    $sql2 = 'INSERT INTO address (address, city, housenumber, postalcode) VALUES(:address, :city, :housenumber, :postalcode)';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':address', $street);
    $stmt2->bindParam(':city', $city);
    $stmt2->bindParam(':housenumber', $housenumber);
    $stmt2->bindParam(':postalcode', $zipcode);
    $stmt2->execute();

    $addressid = $pdo->lastInsertId();

    $sql1 = 'INSERT INTO user (firstname, infix, lastname, email, password, roleid, active, addressid, confirmationtoken) 
             VALUES(:firstname, :infix, :lastname, :email, :password, 2, 0, :addressid, :confirmationtoken)';
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->bindParam(':firstname', $firstname);
    $stmt1->bindParam(':infix', $infix);
    $stmt1->bindParam(':lastname', $lastname);
    $stmt1->bindParam(':email', $email);
    $stmt1->bindParam(':password', $hashedPassword);
    $stmt1->bindParam(':addressid', $addressid);
    $stmt1->bindParam(':confirmationtoken', $token);
    $stmt1->execute();

    $pdo->commit();
    unset($_SESSION['register-history']);
    $_SESSION['message'] = "Gebruiker succesvol geregistreerd. Controleer uw e-mail om het account te bevestigen.";
    sendConfirmationEmail($email, $token);
    header('Location: ../../index.php?page=register');
} catch (PDOException $error) {
    $pdo->rollBack();
    $_SESSION['message'] = "Registratie mislukt: " . $error->getMessage();
    $_SESSION['register-history'] = $formData;
    header('Location: ../../index.php?page=register');
}

exit();
