<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

$user_id = $_SESSION['user_id'];

include 'connection/connection.php';

try {
    $stmt = $pdo->prepare("
        SELECT
            g.Naam, g.Achternaam, g.Email, g.Telefoonnummer,
            r.rol,
            a.Straat, a.Postcode, a.Plaats
        FROM gebruikers g
        JOIN rollen r ON g.rol_id = r.rol_id
        LEFT JOIN adressen a ON g.adress_id = a.adress_id
        WHERE g.user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute(['user_id' => $user_id]);
    $gebruiker_gegevens = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gebruiker_gegevens) {
        echo "Fout: Gebruiker niet gevonden.";
        exit();
    }

} catch (PDOException $e) {
    die("Fout bij ophalen van gegevens: " . $e->getMessage());
}
?>

<div class="profile-pagina">
<div class="profile-container">
    <h2>Mijn Profiel</h2>

    <div class="data-block">
        <h3>Persoonlijke Gegevens</h3>
        <table class="profile-table">
            <tr>
                <td>Rol</td>
                <td><?php echo $gebruiker_gegevens['rol']; ?></td>
            </tr>
            <tr>
                <td>Naam</td>
                <td><?php echo $gebruiker_gegevens['Naam'] . ' ' . $gebruiker_gegevens['Achternaam']; ?></td>
            </tr>
            <tr>
                <td>E-mailadres</td>
                <td><?php echo $gebruiker_gegevens['Email']; ?></td>
            </tr>
            <tr>
                <td>Telefoonnummer</td>
                <td><?php echo $gebruiker_gegevens['Telefoonnummer']; ?></td>
            </tr>
            <tr>
                <td>Wachtwoord</td>
                <td>
                    <span id="password-field">********</span>
                </td>
            </tr>
        </table>
    </div>

    <?php if ($gebruiker_gegevens['Straat']): ?>
        <div class="data-block">
            <h3>Adresgegevens</h3>
            <table class="profile-table">
                <tr>
                    <td>Straat + Huisnr.</td>
                    <td><?php echo $gebruiker_gegevens['Straat']; ?></td>
                </tr>
                <tr>
                    <td>Postcode</td>
                    <td><?php echo $gebruiker_gegevens['Postcode']; ?></td>
                </tr>
                <tr>
                    <td>Plaats</td>
                    <td><?php echo $gebruiker_gegevens['Plaats']; ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div class="profile-actions">
        <a href="index.php?page=profiel_bewerken" class="btn btn-primary">Gegevens bewerken</a>
    </div>
</div>
</div>