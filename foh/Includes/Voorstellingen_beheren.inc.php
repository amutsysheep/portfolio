<?php

require_once __DIR__ . "/../PHP/Showmessage.php";

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header("Location: ../Index.php?page=login");
    exit();
}

$stmt = $pdo->prepare("
    SELECT voorstellingen.Voorstelling_id, voorstellingen.Locatie_id,
           voorstellingen.Starttijd, voorstellingen.Eindtijd,
           locaties.Naam AS Locatie_naam, locaties.Zaal, locaties.Plaats,
           locaties.Straatnaam, locaties.Huisnummer,
           operas.Naam AS Opera_naam
    FROM voorstellingen
    INNER JOIN locaties
        ON voorstellingen.Locatie_id = locaties.Locatie_id
    INNER JOIN operas
        ON voorstellingen.Opera_id = operas.Opera_id
    WHERE voorstellingen.Actief = 1
    ORDER BY voorstellingen.Starttijd ASC
");
$stmt->execute();
$Voorstellingen = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="producer-management">

    <div class="producer-management-container">

        <header class="producer-management-header">
            <h1>Voorstelling Beheer</h1>
            <p>
                Beheer voorstellingen
            </p>
        </header>

        <?php DisplayMessage(); ?>


        <section class="producer-management-panel">

            <div class="producer-panel-header">

                <div>
                    <h2>Alle voorstellingen</h2>

                    <p>
                        Beheer voorstellingen.
                    </p>
                </div>

                <div class="producer-panel-header-actions">

                    <span class="producer-count">
                        <?= count($Voorstellingen) ?> Voorstellingen
                    </span>

                    <a
                            href="index.php?page=Voorstellingen_aanmaken"
                            class="producer-action producer-action-approve"
                    >
                        + Nieuwe voorstelling
                    </a>

                </div>

            </div>


            <div class="producer-table-wrapper">

                <table class="producer-table">

                    <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Locatie</th>
                        <th>Datum</th>
                        <th>Zaal</th>
                        <th>Starttijd</th>
                        <th>Eindtijd</th>
                        <th>Opera</th>
                        <th>Acties</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($Voorstellingen)): ?>

                        <tr>
                            <td colspan="8" class="producer-empty">
                                Er zijn momenteel geen voorstellingen.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($Voorstellingen as $voorstelling): ?>

                            <?php
                            $volledigeNaam = trim($voorstelling['Locatie_naam']);

                            $Locaties = trim(
                                $voorstelling['Plaats'] . ', ' .
                                $voorstelling['Straatnaam'] . ' ' .
                                $voorstelling['Huisnummer']
                            );

                            $start = new DateTime($voorstelling['Starttijd']);
                            $eind  = new DateTime($voorstelling['Eindtijd']);

                            $datumTekst = $start->format('d-m-Y');
                            $startTekst = $start->format('H:i');


                            $eindTekst = $start->format('Y-m-d') === $eind->format('Y-m-d')
                                ? $eind->format('H:i')
                                : $eind->format('d-m H:i');
                            ?>

                            <tr>

                                <td>
                                    <strong class="producer-table-name">
                                        <?= htmlspecialchars($volledigeNaam) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($Locaties) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($datumTekst) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($voorstelling['Zaal']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($startTekst) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($eindTekst) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="producer-table-name">
                                        <?= htmlspecialchars($voorstelling['Opera_naam']) ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="producer-actions">

                                        <a
                                                href="index.php?page=Voorstelling_bewerken&Voorstelling_id=<?= (int)$voorstelling['Voorstelling_id'] ?>"
                                                class="producer-action producer-action-approve"
                                        >
                                            Bewerken
                                        </a>

                                        <a
                                                href="PHP/Voorstelling_verwijderen.php?Voorstelling_id=<?= (int)$voorstelling['Voorstelling_id'] ?>"
                                                class="producer-action producer-action-reject"
                                                onclick="return confirm('Weet je zeker dat je deze voorstelling wilt verwijderen?');"
                                        >
                                            Verwijderen
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</div>