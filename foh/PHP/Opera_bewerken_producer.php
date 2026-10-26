<?php
session_start();
require "../../Private/connection_foh.php";
require "Showmessage.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
}

/* =========================================
   FOUTMELDING
========================================= */
function foutmelding($melding, $operaId = 0)
{
    ShowMessage('error', $melding);

    $url = "../index.php?page=Opera_bewerken_producer";

    if ($operaId > 0) {
        $url .= "&id=" . $operaId;
    }

    header("Location: " . $url);

    exit;
}


if ($pdo === null) {
    foutmelding("Database niet beschikbaar");
}


/* =========================================
   GEGEVENS OPHALEN
========================================= */
$operaId = (int)($_POST['opera_id'] ?? 0);
$naam = trim($_POST['naam'] ?? '');
$genreId = (int)($_POST['genre_id'] ?? 0);
$omschrijving = trim($_POST['omschrijving'] ?? '');
$instrumentIds =
    $_POST['instrument_id'] ?? [];
$instrumentAantallen =
    $_POST['instrument_aantal'] ?? [];
$zangvariantIds =
    $_POST['zangvariant_id'] ?? [];
$zangvariantAantallen =
    $_POST['zangvariant_aantal'] ?? [];

/* =========================================
   BASIS VALIDATIE
========================================= */
if ($operaId <= 0) {
    foutmelding("Ongeldige opera");
}
if ($naam === '') {
    foutmelding(
        "Naam is verplicht",
        $operaId
    );
}
if ($genreId <= 0) {
    foutmelding(
        "Kies een geldig genre",
        $operaId
    );
}
if ($omschrijving === '') {
    foutmelding(
        "Omschrijving is verplicht",
        $operaId
    );
}


/* =========================================
   BESTAANDE COVER OPHALEN
========================================= */
$stmt = $pdo->prepare("
    SELECT Cover_afbeelding
    FROM operas
    WHERE Opera_id = :opera_id
");
$stmt->execute([
    ':opera_id' => $operaId
]);
$bestaandeOpera = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$bestaandeOpera) {

    foutmelding("Opera niet gevonden");
}
$oudeCover = $bestaandeOpera['Cover_afbeelding'] ?? null;
$nieuweCover = $oudeCover;
$nieuweCoverPad = null;

/* =========================================
   NIEUWE COVER
========================================= */
if (
    isset($_FILES['cover']) &&
    $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $cover = $_FILES['cover'];


    if ($cover['error'] !== UPLOAD_ERR_OK) {
        foutmelding(
            "De nieuwe cover kon niet worden geüpload",
            $operaId
        );
    }

    if ($cover['size'] > 5 * 1024 * 1024) {
        foutmelding(
            "De cover mag maximaal 5 MB zijn",
            $operaId
        );
    }

    $toegestaneTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        foutmelding(
            "Bestandstype kon niet worden gecontroleerd",
            $operaId
        );
    }

    $mimeType =
        finfo_file(
            $finfo,
            $cover['tmp_name']
        );

    finfo_close($finfo);

    if (!isset($toegestaneTypes[$mimeType])) {
        foutmelding(
            "Alleen JPG en PNG afbeeldingen zijn toegestaan",
            $operaId
        );
    }

    $imagesMap =
        dirname(__DIR__) .
        DIRECTORY_SEPARATOR .
        'Images';

    if (!is_dir($imagesMap)) {
        if (!mkdir($imagesMap, 0755, true)) {
            foutmelding(
                "De Images map kon niet worden aangemaakt",
                $operaId
            );
        }
    }

    $extensie =
        $toegestaneTypes[$mimeType];

    $nieuweCover =
        'opera_' .
        bin2hex(random_bytes(8)) .
        '.' .
        $extensie;

    $nieuweCoverPad =
        $imagesMap .
        DIRECTORY_SEPARATOR .
        $nieuweCover;

    if (!move_uploaded_file(
        $cover['tmp_name'],
        $nieuweCoverPad
    )) {
        foutmelding(
            "De nieuwe cover kon niet worden opgeslagen",
            $operaId
        );
    }
}

/* =========================================
   INSTRUMENTEN VERWERKEN
========================================= */
$instrumenten = [];
$gebruikteInstrumenten = [];

for ($i = 0; $i < count($instrumentIds); $i++) {
    $instrumentId =
        (int)($instrumentIds[$i] ?? 0);

    $aantal =
        (int)($instrumentAantallen[$i] ?? 0);

    if ($instrumentId === 0 && $aantal === 0) {
        continue;
    }

    if ($instrumentId <= 0) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Kies een geldig instrument",
            $operaId
        );
    }

    if ($aantal <= 0) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Het aantal instrumenten moet minimaal 1 zijn",
            $operaId
        );
    }

    if (
        in_array(
            $instrumentId,
            $gebruikteInstrumenten,
            true
        )
    ) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Hetzelfde instrument mag maar één keer worden toegevoegd",
            $operaId
        );
    }

    $gebruikteInstrumenten[] =
        $instrumentId;

    $instrumenten[] = [
        'id' => $instrumentId,
        'aantal' => $aantal
    ];
}

$zangvarianten = [];
$gebruikteZangvarianten = [];

for ($i = 0; $i < count($zangvariantIds); $i++) {
    $zangvariantId =
        (int)($zangvariantIds[$i] ?? 0);
    $aantal =
        (int)($zangvariantAantallen[$i] ?? 0);

    if ($zangvariantId === 0 && $aantal === 0) {
        continue;
    }

    if ($zangvariantId <= 0) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Kies een geldige zangvariant",
            $operaId
        );
    }

    if ($aantal <= 0) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Het aantal zangers moet minimaal 1 zijn",
            $operaId
        );
    }


    if (
        in_array(
            $zangvariantId,
            $gebruikteZangvarianten,
            true
        )
    ) {
        if ($nieuweCoverPad !== null && file_exists($nieuweCoverPad)) {
            unlink($nieuweCoverPad);
        }
        foutmelding(
            "Dezelfde zangvariant mag maar één keer worden toegevoegd",
            $operaId
        );
    }

    $gebruikteZangvarianten[] =
        $zangvariantId;

    $zangvarianten[] = [
        'id' => $zangvariantId,
        'aantal' => $aantal
    ];
}


try {
    $pdo->beginTransaction();

    /* =====================================
       OPERA BIJWERKEN
    ===================================== */
    $sql = "
        UPDATE operas
        SET
            Genre_id = :genre_id,
            Naam = :naam,
            Omschrijving = :omschrijving,
            Cover_afbeelding = :cover_afbeelding
        WHERE Opera_id = :opera_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':genre_id' => $genreId,
        ':naam' => $naam,
        ':omschrijving' => $omschrijving,
        ':cover_afbeelding' => $nieuweCover,
        ':opera_id' => $operaId
    ]);

    /* =====================================
       OUDE INSTRUMENTEN VERWIJDEREN
    ===================================== */
    $stmt = $pdo->prepare("
        DELETE FROM opera_instrument
        WHERE Opera_id = :opera_id
    ");

    $stmt->execute([
        ':opera_id' => $operaId
    ]);

    /* =====================================
       NIEUWE INSTRUMENTEN OPSLAAN
    ===================================== */
    if (!empty($instrumenten)) {
        $stmtInstrument = $pdo->prepare("
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
        ");

        foreach ($instrumenten as $instrument) {
            $stmtInstrument->execute([
                ':opera_id' => $operaId,
                ':instrument_id' => $instrument['id'],
                ':aantal' => $instrument['aantal']
            ]);
        }
    }

    /* =====================================
       OUDE ZANGVARIANTEN VERWIJDEREN
    ===================================== */
    $stmt = $pdo->prepare("
        DELETE FROM opera_zangvariant
        WHERE Opera_id = :opera_id
    ");

    $stmt->execute([
        ':opera_id' => $operaId
    ]);

    /* =====================================
       NIEUWE ZANGVARIANTEN OPSLAAN
    ===================================== */
    if (!empty($zangvarianten)) {
        $stmtZangvariant = $pdo->prepare("
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
        ");

        foreach ($zangvarianten as $zangvariant) {
            $stmtZangvariant->execute([
                ':opera_id' => $operaId,
                ':zangvariant_id' => $zangvariant['id'],
                ':aantal' => $zangvariant['aantal']
            ]);
        }
    }
    $pdo->commit();

    if (
        $oudeCover !== null &&
        $oudeCover !== '' &&
        $nieuweCover !== $oudeCover
    ) {
        $oudeCoverPad =
            dirname(__DIR__) .
            DIRECTORY_SEPARATOR .
            'Images' .
            DIRECTORY_SEPARATOR .
            $oudeCover;

        if (file_exists($oudeCoverPad)) {
            unlink($oudeCoverPad);
        }
    }

    ShowMessage('success', 'Opera succesvol bewerkt.');

    header("Location: ../index.php?page=Opera_overzicht_producer");

    exit;


} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    /* Nieuwe cover verwijderen als opslaan mislukt */
    if (
        $nieuweCoverPad !== null &&
        file_exists($nieuweCoverPad)
    ) {
        unlink($nieuweCoverPad);
    }

    ShowMessage('error', 'Opera kon niet worden bijgewerkt.');

    header(
        "Location: ../index.php?page=Opera_bewerken_producer&id=" . $operaId
    );

    exit;
}