<?php

require_once __DIR__ . "/../PHP/Showmessage.php";

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header("Location: ../Index.php?page=login");
    exit();
}

// Actieve opera's voor de dropdown
$stmt = $pdo->prepare("
    SELECT operas.Opera_id, operas.Naam
    FROM operas
    WHERE operas.Actief = 1
    ORDER BY operas.Naam
");
$stmt->execute();
$Operas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actieve locaties voor de dropdown
$stmt = $pdo->prepare("
    SELECT locaties.Locatie_id, locaties.Naam, locaties.Zaal, locaties.Plaats,
           locaties.Straatnaam, locaties.Huisnummer
    FROM locaties
    WHERE locaties.Actief = 1
    ORDER BY locaties.Naam, locaties.Zaal
");
$stmt->execute();
$Locaties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="voorstelling-edit">

    <div class="voorstelling-edit-container">

        <header class="voorstelling-edit-header">
            <h1>Voorstelling aanmaken</h1>
            <p>Voeg een nieuwe voorstelling toe</p>
        </header>

        <?php DisplayMessage(); ?>

        <section class="voorstelling-edit-panel">

            <form method="POST" action="PHP/Voorstelling_aanmaken.php" class="voorstelling-form">

                <div class="voorstelling-form-group full-width">
                    <label for="Opera_id">Opera</label>
                    <select name="Opera_id" id="Opera_id" required>
                        <option value="">-- Kies een opera --</option>
                        <?php foreach ($Operas as $opera): ?>
                            <option value="<?= (int)$opera['Opera_id'] ?>">
                                <?= htmlspecialchars($opera['Naam']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="voorstelling-form-group full-width">
                    <label for="Locatie_id">Locatie</label>
                    <select name="Locatie_id" id="Locatie_id" required>
                        <option value="">-- Kies een locatie --</option>
                        <?php foreach ($Locaties as $locatie): ?>
                            <?php
                            $label = $locatie['Naam']
                                . ' - Zaal ' . $locatie['Zaal']
                                . ' (' . $locatie['Plaats'] . ', '
                                . $locatie['Straatnaam'] . ' ' . $locatie['Huisnummer'] . ')';
                            ?>
                            <option value="<?= (int)$locatie['Locatie_id'] ?>">
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="voorstelling-form-group full-width">
                    <label for="Datum">Datum</label>
                    <input type="date" name="Datum" id="Datum" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="voorstelling-form-group">
                    <label for="Starttijd">Starttijd</label>
                    <input type="time" name="Starttijd" id="Starttijd" required>
                </div>

                <div class="voorstelling-form-group">
                    <label for="Eindtijd">Eindtijd</label>
                    <input type="time" name="Eindtijd" id="Eindtijd" required>
                </div>

                <div class="voorstelling-form-actions">
                    <a href="index.php?page=Voorstellingen_beheren"
                       class="voorstelling-btn voorstelling-btn-cancel">
                        Annuleren
                    </a>

                    <button type="submit" class="voorstelling-btn voorstelling-btn-save">
                        Aanmaken
                    </button>
                </div>

            </form>

        </section>

    </div>

</div>