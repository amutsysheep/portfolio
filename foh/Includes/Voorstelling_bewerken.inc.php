<?php
require_once __DIR__ . "/../PHP/Showmessage.php";

$voorstelling_id = $_GET['Voorstelling_id'] ?? '';

if (empty($voorstelling_id)) {
    echo "<p>Geen voorstelling geselecteerd.</p>";
    exit();
}


/*
 * Voorstelling ophalen
 */
$stmt = $pdo->prepare("
    SELECT
        voorstellingen.Voorstelling_id,
        voorstellingen.Opera_id,
        voorstellingen.Locatie_id,
        voorstellingen.Starttijd,
        voorstellingen.Eindtijd,
        locaties.Naam,
        locaties.Zaal,
        locaties.Plaats,
        locaties.Straatnaam,
        locaties.Huisnummer
    FROM voorstellingen
    INNER JOIN locaties
        ON voorstellingen.Locatie_id = locaties.Locatie_id
    WHERE voorstellingen.Voorstelling_id = :Voorstelling_id
");

$stmt->execute([
    'Voorstelling_id' => $voorstelling_id
]);

$voorstelling = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voorstelling) {
    echo "<p>Voorstelling niet gevonden.</p>";
    exit();
}


/*
 * Datum en tijden uit de DATETIME-waarden halen voor het formulier
 */
$startObj = new DateTime($voorstelling['Starttijd']);
$eindObj  = new DateTime($voorstelling['Eindtijd']);

$datumWaarde = $startObj->format('Y-m-d');
$startWaarde = $startObj->format('H:i');
$eindWaarde  = $eindObj->format('H:i');


/*
 * Alle opera's ophalen (actieve + de opera die nu aan de voorstelling hangt)
 */
$stmt = $pdo->prepare("
    SELECT
        operas.Opera_id,
        operas.Naam
    FROM operas
    WHERE operas.Actief = 1
       OR operas.Opera_id = :Opera_id
    ORDER BY operas.Naam ASC
");

$stmt->execute([
    'Opera_id' => $voorstelling['Opera_id']
]);

$operas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
 * Alle locaties ophalen
 */
$stmt = $pdo->prepare("
    SELECT
        Locatie_id,
        Naam,
        Zaal,
        Plaats,
        Straatnaam,
        Huisnummer
    FROM locaties
    ORDER BY Naam ASC
");

$stmt->execute();

$locaties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="voorstelling-edit">

    <div class="voorstelling-edit-container">

        <div class="voorstelling-edit-header">
            <h1>Voorstelling bewerken</h1>
            <p>Pas de gegevens van deze voorstelling aan.</p>
        </div>
        <?php DisplayMessage(); ?>

        <div class="voorstelling-edit-panel">

            <form
                    method="POST"
                    action="PHP/Voorstelling_bewerken.php"
                    class="voorstelling-form"
            >

                <input
                        type="hidden"
                        name="Voorstelling_id"
                        value="<?= htmlspecialchars($voorstelling['Voorstelling_id']) ?>"
                >


                <!-- Opera -->

                <div class="voorstelling-form-group full-width">

                    <label for="opera">
                        Opera
                    </label>

                    <select
                            id="opera"
                            name="Opera_id"
                            required
                    >

                        <?php foreach ($operas as $opera): ?>

                            <option
                                    value="<?= (int)$opera['Opera_id'] ?>"
                                <?= (int)$opera['Opera_id'] === (int)$voorstelling['Opera_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($opera['Naam']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Datum -->

                <div class="voorstelling-form-group">

                    <label for="datum">
                        Datum
                    </label>

                    <input
                            type="date"
                            id="datum"
                            name="Datum"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($datumWaarde) ?>"
                            required
                    >

                </div>


                <!-- Locatie -->

                <div class="voorstelling-form-group">

                    <label for="locatie">
                        Locatie
                    </label>

                    <select
                            id="locatie"
                            name="Locatie_id"
                            required
                    >

                        <?php foreach ($locaties as $locatie): ?>

                            <option
                                    value="<?= htmlspecialchars($locatie['Locatie_id']) ?>"
                                <?= $locatie['Locatie_id'] == $voorstelling['Locatie_id'] ? 'selected' : '' ?>
                            >

                                <?= htmlspecialchars($locatie['Naam']) ?>
                                -
                                <?= htmlspecialchars($locatie['Zaal']) ?>
                                -
                                <?= htmlspecialchars($locatie['Plaats']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Starttijd -->

                <div class="voorstelling-form-group">

                    <label for="starttijd">
                        Starttijd
                    </label>

                    <input
                            type="time"
                            id="starttijd"
                            name="Starttijd"
                            value="<?= htmlspecialchars($startWaarde) ?>"
                            required
                    >

                </div>


                <!-- Eindtijd -->

                <div class="voorstelling-form-group">

                    <label for="eindtijd">
                        Eindtijd
                    </label>

                    <input
                            type="time"
                            id="eindtijd"
                            name="Eindtijd"
                            value="<?= htmlspecialchars($eindWaarde) ?>"
                            required
                    >

                </div>


                <!-- Knoppen -->

                <div class="voorstelling-form-actions">

                    <a
                            href="index.php?page=Voorstellingen_beheren"
                            class="voorstelling-btn voorstelling-btn-cancel"
                    >
                        Annuleren
                    </a>

                    <button
                            type="submit"
                            class="voorstelling-btn voorstelling-btn-save"
                    >
                        Opslaan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>