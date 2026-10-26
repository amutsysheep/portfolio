<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../Private/pas_connection.php';

if (!isset($_GET['pakket_id']) || !ctype_digit($_GET['pakket_id'])) {
    die('Ongeldig pakket');
}

$pakket_id = (int)$_GET['pakket_id'];

// Couriers ophalen
$couriers = $pdo->query("SELECT courier_id, bedrijfsnaam FROM couriers ORDER BY bedrijfsnaam")->fetchAll(PDO::FETCH_ASSOC);

// Opslaan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['courier_id']) || !ctype_digit($_POST['courier_id'])) {
        die('Selecteer een courier');
    }

    $courier_id = (int)$_POST['courier_id'];

    // Claim pakket
    $stmt = $pdo->prepare("
        INSERT INTO geclaimde_pakketten (pakket_id, courier_id)
        VALUES (:pakket_id, :courier_id)
    ");
    $stmt->execute([
        ':pakket_id' => $pakket_id,
        ':courier_id' => $courier_id
    ]);

    // Status aanpassen (bijv. status_id = 2 = In behandeling)
    $pdo->prepare("
        UPDATE pakketten
        SET status_id = 2
        WHERE pakket_id = :id
    ")->execute([':id' => $pakket_id]);

    header('Location: index.php?page=alle_pakketten_overzicht');
    exit;
}
?>

<h1>Courier toewijzen</h1>

<form method="post">
    <label>Courier:</label>
    <select name="courier_id" required>
        <option value="">-- Kies courier --</option>
        <?php foreach ($couriers as $c): ?>
            <option value="<?= (int)$c['courier_id'] ?>">
                <?= htmlspecialchars($c['bedrijfsnaam']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <button type="submit">Toewijzen</button>
</form>

<a href="index.php?page=alle_pakketten_overzicht">← Terug</a>


