<?php
require "PHP/Showmessage.php";

$genres = [];
$instrumenten = [];
$zangvarianten = [];

if ($pdo !== null) {

    // Genres ophalen
    $stmt = $pdo->prepare("
        SELECT Genre_id, Genre
        FROM genres
        ORDER BY Genre
    ");
    $stmt->execute();
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Instrumenten ophalen
    $stmt = $pdo->prepare("
        SELECT Instrument_id, Instrument
        FROM instrumenten
        ORDER BY Instrument
    ");
    $stmt->execute();
    $instrumenten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Zangvarianten ophalen
    $stmt = $pdo->prepare("
        SELECT Zangvariant_id, Zangvariant
        FROM zangvarianten
        ORDER BY Zangvariant
    ");
    $stmt->execute();
    $zangvarianten = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="opera-aanmaken-container">
    <div class="opera-aanmaken-header">
        <h1>Nieuwe opera aanmaken</h1>
        <p>
            Vul de gegevens van de opera in en geef aan welke instrumenten
            en zangvarianten nodig zijn.
        </p>
        <?php DisplayMessage(); ?>
    </div>


    <?php if ($pdo === null) { ?>
        <div class="opera-foutmelding">
            Veld niet gevonden
        </div>

    <?php } else { ?>
        <form
            action="PHP/Opera_aanmaken_producer.php"
            method="POST"
            enctype="multipart/form-data"
            class="opera-aanmaken-form"
        >

            <!-- =========================
                 ALGEMENE INFORMATIE
            ========================== -->
            <div class="opera-form-section">
                <div class="opera-section-title">
                    <h2>Algemene informatie</h2>
                    <p>
                        Basisgegevens van de opera.
                    </p>
                </div>

                <div class="opera-form-grid">
                    <div class="opera-form-group">
                        <label for="naam">
                            Naam
                        </label>
                        <input
                            type="text"
                            id="naam"
                            name="naam"
                            placeholder="Naam van de opera"
                            required
                        >
                    </div>

                    <div class="opera-form-group">
                        <label for="genre">
                            Genre
                        </label>
                        <select
                            id="genre"
                            name="genre_id"
                            required
                        >
                            <option value="">
                                Kies een genre
                            </option>
                            <?php foreach ($genres as $genre) { ?>
                                <option
                                    value="<?= (int)$genre['Genre_id']; ?>"
                                >
                                    <?= htmlspecialchars($genre['Genre']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="opera-form-group">

                    <label for="omschrijving">
                        Omschrijving
                    </label>
                    <textarea
                        id="omschrijving"
                        name="omschrijving"
                        rows="6"
                        placeholder="Geef een korte omschrijving van de opera..."
                        required
                    ></textarea>
                </div>

                <div class="opera-form-group">
                    <label for="cover">
                        Coverafbeelding
                    </label>
                    <div class="opera-file-wrapper">
                        <input
                            type="file"
                            id="cover"
                            name="cover"
                            accept="image/jpeg,image/png,image/webp"
                        >
                    </div>
                    <span class="opera-input-hint">
                        JPG, PNG of WEBP
                    </span>
                </div>
            </div>

            <!-- =========================
                 INSTRUMENTEN
            ========================== -->
            <div class="opera-form-section">
                <div class="opera-section-title">
                    <h2>
                        Benodigde instrumenten
                    </h2>
                    <p>
                        Voeg de instrumenten toe die nodig zijn voor deze opera
                        en geef per instrument het benodigde aantal aan.
                    </p>
                </div>
                <div
                    id="instrumenten-container"
                    class="opera-dynamic-container"
                >
                    <div class="opera-dynamic-row">
                        <select
                            name="instrument_id[]"
                            class="instrument-select"
                        >
                            <option value="">
                                Kies een instrument
                            </option>
                            <?php foreach ($instrumenten as $instrument) { ?>

                                <option
                                    value="<?= (int)$instrument['Instrument_id']; ?>"
                                >
                                    <?= htmlspecialchars($instrument['Instrument']); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <input
                            type="number"
                            name="instrument_aantal[]"
                            min="1"
                            placeholder="Aantal"
                        >
                        <button
                            type="button"
                            class="opera-row-delete"
                            onclick="verwijderRij(this)"
                            aria-label="Instrument verwijderen"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    class="opera-row-add"
                    onclick="voegInstrumentToe()"
                >
                    <span>+</span>
                    Instrument toevoegen
                </button>
            </div>

            <!-- =========================
                 ZANGVARIANTEN
            ========================== -->
            <div class="opera-form-section">
                <div class="opera-section-title">
                    <h2>
                        Benodigde zangvarianten
                    </h2>
                    <p>
                        Voeg de benodigde zangvarianten toe en geef per variant
                        het benodigde aantal zangers aan.
                    </p>
                </div>
                <div
                    id="zangvarianten-container"
                    class="opera-dynamic-container"
                >
                    <div class="opera-dynamic-row">
                        <select
                            name="zangvariant_id[]"
                            class="zangvariant-select"
                        >
                            <option value="">
                                Kies een zangvariant
                            </option>
                            <?php foreach ($zangvarianten as $zangvariant) { ?>

                                <option
                                    value="<?= (int)$zangvariant['Zangvariant_id']; ?>"
                                >
                                    <?= htmlspecialchars($zangvariant['Zangvariant']); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <input
                            type="number"
                            name="zangvariant_aantal[]"
                            min="1"
                            placeholder="Aantal"
                        >
                        <button
                            type="button"
                            class="opera-row-delete"
                            onclick="verwijderRij(this)"
                            aria-label="Zangvariant verwijderen"
                        >
                            ×
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="opera-row-add"
                    onclick="voegZangvariantToe()"
                >
                    <span>+</span>
                    Zangvariant toevoegen
                </button>
            </div>

            <!-- =========================
                 ACTIES
            ========================== -->
            <div class="opera-form-actions">
                <a
                    href="index.php?page=Opera_overzicht_producer"
                    class="opera-annuleren-button"
                >
                    Annuleren
                </a>
                <button
                    type="submit"
                    class="opera-opslaan-button"
                >
                    Opera aanmaken
                </button>
            </div>
        </form>
    <?php } ?>
</div>

<script>
    function voegInstrumentToe() {
        const container = document.getElementById('instrumenten-container');
        const eersteRij = container.querySelector('.opera-dynamic-row');
        const nieuweRij = eersteRij.cloneNode(true);

        nieuweRij.querySelector('.instrument-select').selectedIndex = 0;
        nieuweRij.querySelector('input').value = '';

        container.appendChild(nieuweRij);
    }


    function voegZangvariantToe() {
        const container = document.getElementById('zangvarianten-container');
        const eersteRij = container.querySelector('.opera-dynamic-row');
        const nieuweRij = eersteRij.cloneNode(true);

        nieuweRij.querySelector('.zangvariant-select').selectedIndex = 0;
        nieuweRij.querySelector('input').value = '';

        container.appendChild(nieuweRij);
    }


    function verwijderRij(button) {

        const rij = button.closest('.opera-dynamic-row');
        const container = rij.parentElement;
        const rijen = container.querySelectorAll('.opera-dynamic-row');

        if (rijen.length > 1) {
            rij.remove();
        } else {
            rij.querySelector('select').selectedIndex = 0;
            rij.querySelector('input').value = '';
        }
    }
</script>