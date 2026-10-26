<?php
session_start();
require "../../Private/connection_foh.php";
require "Showmessage.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=opera");
    exit;
}

/* =========================================
   FUNCTIE VOOR FOUTMELDING
========================================= */
function foutmelding($melding)
{
    ShowMessage('error', $melding);

    header("Location: ../index.php?page=Opera_aanmaken_producer");
    exit;
}

/* =========================================
   DATABASE CONTROLEREN
========================================= */
if ($pdo === null) {
    foutmelding("Database niet beschikbaar");
}

/* =========================================
   FORMULIERGEGEVENS OPHALEN
========================================= */
$naam = trim($_POST['naam'] ?? '');
$genreId = (int)($_POST['genre_id'] ?? 0);
$omschrijving = trim($_POST['omschrijving'] ?? '');

$instrumentIds = $_POST['instrument_id'] ?? [];
$instrumentAantallen = $_POST['instrument_aantal'] ?? [];

$zangvariantIds = $_POST['zangvariant_id'] ?? [];
$zangvariantAantallen = $_POST['zangvariant_aantal'] ?? [];


/* =========================================
   BASIS VALIDATIE
========================================= */
if ($naam === '') {
    foutmelding("Naam is verplicht");
}
if ($genreId <= 0) {
    foutmelding("Kies een geldig genre");
}
if ($omschrijving === '') {
    foutmelding("Omschrijving is verplicht");
}


/* =========================================
   COVER UPLOADEN
========================================= */
$coverBestandsnaam = null;
if (
    isset($_FILES['cover']) &&
    $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $cover = $_FILES['cover'];
    /* Uploadfout controleren */
    if ($cover['error'] !== UPLOAD_ERR_OK) {
        foutmelding("De cover kon niet worden geüpload");
    }
    /* Maximale bestandsgrootte: 5 MB */
    if ($cover['size'] > 5 * 1024 * 1024) {
        foutmelding("De cover mag maximaal 5 MB zijn");
    }
    /* Bestandstype controleren */
    $toegestaneTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        foutmelding("Bestandstype kon niet worden gecontroleerd");
    }

    $mimeType = finfo_file($finfo, $cover['tmp_name']);
    finfo_close($finfo);
    if (!isset($toegestaneTypes[$mimeType])) {
        foutmelding("Alleen JPG, PNG en WEBP afbeeldingen zijn toegestaan");
    }

    /* Images-map */
    $imagesMap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Images';
    if (!is_dir($imagesMap)) {
        if (!mkdir($imagesMap, 0755, true)) {
            foutmelding("De Images map kon niet worden aangemaakt");
        }
    }

    /* Unieke bestandsnaam maken */
    $extensie = $toegestaneTypes[$mimeType];

    $coverBestandsnaam =
        'opera_' .
        bin2hex(random_bytes(8)) .
        '.' .
        $extensie;

    $coverPad =
        $imagesMap .
        DIRECTORY_SEPARATOR .
        $coverBestandsnaam;

    /* Bestand verplaatsen */
    if (!move_uploaded_file($cover['tmp_name'], $coverPad)) {
        foutmelding("De cover kon niet worden opgeslagen");
    }
}


/* =========================================
   INSTRUMENTEN VERWERKEN
========================================= */
$instrumenten = [];
$gebruikteInstrumenten = [];

for ($i = 0; $i < count($instrumentIds); $i++) {
    $instrumentId = (int)($instrumentIds[$i] ?? 0);
    $aantal = (int)($instrumentAantallen[$i] ?? 0);
    /* Lege rijen overslaan */
    if ($instrumentId === 0 && $aantal === 0) {
        continue;
    }

    if ($instrumentId <= 0) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Kies een geldig instrument");
    }

    if ($aantal <= 0) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Het aantal instrumenten moet minimaal 1 zijn");
    }

    /* Dubbel instrument voorkomen */
    if (in_array($instrumentId, $gebruikteInstrumenten, true)) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Hetzelfde instrument mag maar één keer worden toegevoegd");
    }

    $gebruikteInstrumenten[] = $instrumentId;
    $instrumenten[] = [
        'id' => $instrumentId,
        'aantal' => $aantal
    ];
}


/* =========================================
   ZANGVARIANTEN VERWERKEN
========================================= */
$zangvarianten = [];
$gebruikteZangvarianten = [];

for ($i = 0; $i < count($zangvariantIds); $i++) {
    $zangvariantId = (int)($zangvariantIds[$i] ?? 0);
    $aantal = (int)($zangvariantAantallen[$i] ?? 0);

    /* Lege rijen overslaan */
    if ($zangvariantId === 0 && $aantal === 0) {
        continue;
    }

    if ($zangvariantId <= 0) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Kies een geldige zangvariant");
    }

    if ($aantal <= 0) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Het aantal zangers moet minimaal 1 zijn");
    }

    /* Dubbele zangvariant voorkomen */
    if (in_array($zangvariantId, $gebruikteZangvarianten, true)) {
        if ($coverBestandsnaam !== null && file_exists($coverPad)) {
            unlink($coverPad);
        }
        foutmelding("Dezelfde zangvariant mag maar één keer worden toegevoegd");
    }

    $gebruikteZangvarianten[] = $zangvariantId;
    $zangvarianten[] = [
        'id' => $zangvariantId,
        'aantal' => $aantal
    ];
}


/* =========================================
   DATABASE TRANSACTIE
========================================= */
try {
    $pdo->beginTransaction();

    /* =====================================
       OPERA AANMAKEN
    ===================================== */
    $sql = "
        INSERT INTO operas (
            Genre_id,
            Naam,
            Omschrijving,
            Aangemaakt_op,
            Cover_afbeelding,
            Actief
        )
        VALUES (
            :genre_id,
            :naam,
            :omschrijving,
            NOW(),
            :cover_afbeelding,
            1
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':genre_id' => $genreId,
        ':naam' => $naam,
        ':omschrijving' => $omschrijving,
        ':cover_afbeelding' => $coverBestandsnaam
    ]);


    /* =====================================
       NIEUWE OPERA ID OPHALEN
    ===================================== */
    $operaId = (int)$pdo->lastInsertId();
    if ($operaId <= 0) {
        throw new Exception("Opera kon niet worden aangemaakt");
    }


    /* =====================================
       INSTRUMENTEN OPSLAAN
    ===================================== */
    if (!empty($instrumenten)) {
        $sql = "
            INSERT INTO opera_instrument (
                Opera_id,
                Instrument_id,
                Aantal
            )
            VALUES (
                :opera_id,
                :instrument_id,
                :aantal
            )
        ";

        $stmtInstrument = $pdo->prepare($sql);

        foreach ($instrumenten as $instrument) {
            $stmtInstrument->execute([
                ':opera_id' => $operaId,
                ':instrument_id' => $instrument['id'],
                ':aantal' => $instrument['aantal']
            ]);
        }
    }


    /* =====================================
       ZANGVARIANTEN OPSLAAN
    ===================================== */
    if (!empty($zangvarianten)) {
        $sql = "
            INSERT INTO opera_zangvariant (
                Opera_id,
                Zangvariant_id,
                Aantal
            )
            VALUES (
                :opera_id,
                :zangvariant_id,
                :aantal
            )
        ";

        $stmtZangvariant = $pdo->prepare($sql);

        foreach ($zangvarianten as $zangvariant) {

            $stmtZangvariant->execute([
                ':opera_id' => $operaId,
                ':zangvariant_id' => $zangvariant['id'],
                ':aantal' => $zangvariant['aantal']
            ]);
        }
    }


    /* =====================================
       ALLES OPSLAAN
    ===================================== */
    $pdo->commit();

    ShowMessage('success', 'Opera succesvol aangemaakt.');

    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;


} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (
        isset($coverBestandsnaam) &&
        $coverBestandsnaam !== null &&
        isset($coverPad) &&
        file_exists($coverPad)
    ) {
        unlink($coverPad);
    }

    ShowMessage('error', 'Opera kon niet worden aangemaakt.');

    header("Location: ../index.php?page=Opera_aanmaken_producer");
    exit;
}