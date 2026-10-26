<?php
require "PHP/Showmessage.php";

$opera = null;
$genres = [];
$instrumenten = [];
$zangvarianten = [];
$operaInstrumenten = [];
$operaZangvarianten = [];
$operaId = (int)($_GET['id'] ?? 0);


/* =========================================
   OPERA ID CONTROLEREN
========================================= */
if ($operaId <= 0) {
    $operaFout = 'Veld niet gevonden';
}

/* =========================================
   GEGEVENS OPHALEN
========================================= */
if ($pdo !== null && $operaId > 0) {
    /* ---------- OPERA ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Opera_id,
            Genre_id,
            Naam,
            Omschrijving,
            Cover_afbeelding,
            Actief
        FROM operas
        WHERE Opera_id = :opera_id
    ");

    $stmt->execute([
        ':opera_id' => $operaId
    ]);
    $opera = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$opera) {
        $operaFout = 'Veld niet gevonden';
    }

    /* ---------- GENRES ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Genre_id,
            Genre
        FROM genres
        ORDER BY Genre
    ");

    $stmt->execute();
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ---------- INSTRUMENTEN ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Instrument_id,
            Instrument
        FROM instrumenten
        ORDER BY Instrument
    ");

    $stmt->execute();
    $instrumenten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ---------- ZANGVARIANTEN ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Zangvariant_id,
            Zangvariant
        FROM zangvarianten
        ORDER BY Zangvariant
    ");

    $stmt->execute();
    $zangvarianten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ---------- OPERA INSTRUMENTEN ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Instrument_id,
            Aantal
        FROM opera_instrument
        WHERE Opera_id = :opera_id
        ORDER BY Instrument_id
    ");

    $stmt->execute([
        ':opera_id' => $operaId
    ]);
    $operaInstrumenten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ---------- OPERA ZANGVARIANTEN ---------- */
    $stmt = $pdo->prepare("
        SELECT
            Zangvariant_id,
            Aantal
        FROM opera_zangvariant
        WHERE Opera_id = :opera_id
        ORDER BY Zangvariant_id
    ");

    $stmt->execute([
        ':opera_id' => $operaId
    ]);

    $operaZangvarianten = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="opera-aanmaken-container">
    <div class="opera-aanmaken-header">
        <h1>Opera bewerken</h1>
        <p>
            Pas de gegevens van de opera aan en sla de wijzigingen op.
        </p>
        <?php DisplayMessage(); ?>
    </div>

    <?php if ($pdo === null || isset($operaFout)) { ?>
        <div class="opera-foutmelding">
            Veld niet gevonden
        </div>
    <?php } else { ?>
        <form
            action="PHP/Opera_bewerken_producer.php"
            method="POST"
            enctype="multipart/form-data"
            class="opera-aanmaken-form"
        >
            <input
                type="hidden"
                name="opera_id"
                value="<?= (int)$opera['Opera_id']; ?>"
            >
            <div class="opera-form-section">
                <div class="opera-section-title">
                    <h2>Algemene informatie</h2>
                    <p>
                        Pas de basisgegevens van de opera aan.
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
                            value="<?= htmlspecialchars($opera['Naam']); ?>"
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
                                    <?= (int)$genre['Genre_id'] === (int)$opera['Genre_id'] ? 'selected' : ''; ?>
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
                    ><?= htmlspecialchars($opera['Omschrijving']); ?></textarea>
                </div>

                <div class="opera-form-group">
                    <label>
                        Huidige cover
                    </label>
                    <?php if (!empty($opera['Cover_afbeelding'])) { ?>
                        <div style="margin-bottom: 15px;">
                            <img
                                src="Images/<?= htmlspecialchars($opera['Cover_afbeelding']); ?>"
                                alt="Huidige cover"
                                style="
                                    width: 180px;
                                    height: 240px;
                                    object-fit: cover;
                                    border-radius: 12px;
                                    display: block;
                                "
                            >
                        </div>

                    <?php } else { ?>
                        <div class="opera-input-hint">
                            Er is momenteel geen cover ingesteld.
                        </div>
                    <?php } ?>

                    <label for="cover">
                        Nieuwe cover
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
                        Laat dit veld leeg om de huidige cover te behouden.
                        JPG of PNG.
                    </span>
                </div>
            </div>

            <!-- =========================
                 INSTRUMENTEN
            ========================== -->
            <div class="opera-form-section">
                <div class="opera-section-title">
                    <h2>Benodigde instrumenten</h2>
                    <p>
                        Pas de benodigde instrumenten en aantallen aan.
                    </p>
                </div>

                <div
                    id="instrumenten-container"
                    class="opera-dynamic-container"
                >
                    <?php if (!empty($operaInstrumenten)) { ?>
                        <?php foreach ($operaInstrumenten as $operaInstrument) { ?>
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
                                            <?= (int)$instrument['Instrument_id'] === (int)$operaInstrument['Instrument_id'] ? 'selected' : ''; ?>
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
                                    value="<?= (int)$operaInstrument['Aantal']; ?>"
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
                        <?php } ?>
                    <?php } else { ?>

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
                    <?php } ?>
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
                    <h2>Benodigde zangvarianten</h2>
                    <p>
                        Pas de benodigde zangvarianten en aantallen aan.
                    </p>
                </div>

                <div
                    id="zangvarianten-container"
                    class="opera-dynamic-container"
                >

                    <?php if (!empty($operaZangvarianten)) { ?>
                        <?php foreach ($operaZangvarianten as $operaZangvariant) { ?>
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
                                            <?= (int)$zangvariant['Zangvariant_id'] === (int)$operaZangvariant['Zangvariant_id'] ? 'selected' : ''; ?>
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
                                    value="<?= (int)$operaZangvariant['Aantal']; ?>"
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
                        <?php } ?>
                    <?php } else { ?>
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
                    <?php } ?>
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
                    href="index.php?page=opera"
                    class="opera-annuleren-button"
                >
                    Annuleren
                </a>
                <button
                    type="submit"
                    class="opera-opslaan-button"
                >
                    Wijzigingen opslaan
                </button>
            </div>
        </form>
    <?php } ?>
</div>

<script>

    function voegInstrumentToe() {
        const container =
            document.getElementById('instrumenten-container');
        const eersteRij =
            container.querySelector('.opera-dynamic-row');
        const nieuweRij =
            eersteRij.cloneNode(true);

        nieuweRij.querySelector('.instrument-select').selectedIndex = 0;
        nieuweRij.querySelector('input').value = '';
        container.appendChild(nieuweRij);
    }


    function voegZangvariantToe() {
        const container =
            document.getElementById('zangvarianten-container');
        const eersteRij =
            container.querySelector('.opera-dynamic-row');
        const nieuweRij =
            eersteRij.cloneNode(true);

        nieuweRij.querySelector('.zangvariant-select').selectedIndex = 0;
        nieuweRij.querySelector('input').value = '';

        container.appendChild(nieuweRij);
    }


    function verwijderRij(button) {
        const rij =
            button.closest('.opera-dynamic-row');
        const container =
            rij.parentElement;
        const rijen =
            container.querySelectorAll('.opera-dynamic-row');

        if (rijen.length > 1) {
            rij.remove();
        } else {
            rij.querySelector('select').selectedIndex = 0;
            rij.querySelector('input').value = '';
        }
    }
</script>