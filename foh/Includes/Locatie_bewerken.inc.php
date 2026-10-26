<?php
require_once 'PHP/ShowMessage.php';

// Controleren of er een geldig ID is
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    ShowMessage('error', 'Ongeldige locatie.');
    header('Location: Index.php?page=locaties');
    exit;
}

$locatie_id = (int) $_GET['id'];

// Locatie ophalen
$sql = "
    SELECT
        Locatie_id,
        Naam,
        Zaal,
        Plaats,
        Straatnaam,
        Huisnummer,
        Postcode
    FROM locaties
    WHERE Locatie_id = :locatie_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':locatie_id' => $locatie_id
]);

$locatie = $stmt->fetch(PDO::FETCH_ASSOC);

// Controleren of locatie bestaat
if (!$locatie) {
    ShowMessage('error', 'De locatie bestaat niet.');
    header('Location: Index.php?page=locaties');
    exit;
}

?>

<div class="beheer-container">

    <div class="beheer-header formulier-header">
        <h2>Locatie bewerken</h2>
    </div>

    <?php DisplayMessage(); ?>

    <form
        action="PHP/Locatie_bewerken.php?id=<?= (int) $locatie['Locatie_id'] ?>"
        method="POST"
        class="beheer-form"
    >

        <div class="form-group">
            <label for="Naam">Naam</label>
            <input
                type="text"
                id="Naam"
                name="Naam"
                value="<?= htmlspecialchars($locatie['Naam']) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="Zaal">Zaal</label>
            <input
                type="text"
                id="Zaal"
                name="Zaal"
                value="<?= htmlspecialchars($locatie['Zaal'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="Plaats">Plaats</label>
            <input
                type="text"
                id="Plaats"
                name="Plaats"
                value="<?= htmlspecialchars($locatie['Plaats']) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="Straatnaam">Straatnaam</label>
            <input
                type="text"
                id="Straatnaam"
                name="Straatnaam"
                value="<?= htmlspecialchars($locatie['Straatnaam'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="Huisnummer">Huisnummer</label>
            <input
                type="text"
                id="Huisnummer"
                name="Huisnummer"
                value="<?= htmlspecialchars($locatie['Huisnummer'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label for="Postcode">Postcode</label>
            <input
                type="text"
                id="Postcode"
                name="Postcode"
                value="<?= htmlspecialchars($locatie['Postcode'] ?? '') ?>"
                maxlength="6"
            >
        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="button-primary"
            >
                Opslaan
            </button>

            <a
                href="index.php?page=Locatie_overzicht"
                class="button-primary"
            >
                Annuleren
            </a>

        </div>

    </form>

</div>