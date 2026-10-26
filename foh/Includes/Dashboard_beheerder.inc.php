<?php

include"../Private/connection_Foh.php";

$stmt = $pdo->prepare("SELECT operas.Naam, operas.Aangemaakt_op, producers.Voornaam, producers.Tussenvoegsel, producers.Achternaam 
                        FROM operas
                        JOIN producers
                        ON operas.Producer_id = producers.Producer_id
                        ORDER BY operas.Aangemaakt_op DESC
                        LIMIT 4");
$stmt->execute();
$createdOperas = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("SELECT * FROM operas");
$stmt->execute();
$amountOperas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM locaties");
$stmt->execute();
$createdLocaties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM producers  WHERE Producer_status_id = 1");
$stmt->execute();
$approvedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT 
                        producers.Producer_id,
                        producers.Voornaam,
                        producers.Tussenvoegsel,
                        producers.Achternaam,
                        COUNT(operas.Opera_id) AS opera_count
                        FROM producers
                        LEFT JOIN operas
                        ON producers.Producer_id = operas.Producer_id
                        GROUP BY
                            producers.Producer_id,
                            producers.Voornaam,
                            producers.Tussenvoegsel,
                            producers.Achternaam
                        ");

$stmt->execute();
$producers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Producer_id, Producer_status_id, Voornaam, Tussenvoegsel, Achternaam FROM producers WHERE Producer_status_id = 3");
$stmt->execute();
$createdProducers = $stmt->fetchAll(PDO::FETCH_ASSOC);



$dashboardStats = [
    'created_operas' => count($amountOperas),
    'active_locations' => count($createdLocaties),
    'approved_producers' => count($approvedUsers),
    'new_registrations' => count($createdProducers),

];

?>

<div class="admin-dashboard">
    <div class="admin-dashboard-container">

        <header class="admin-dashboard-header">
            <h1>Home Dashboard</h1>
        </header>

        <section class="admin-statistics">

            <div class="admin-stat-card">
                <span class="admin-stat-label">
                    Aangemaakte opera's
                </span>

                <strong class="admin-stat-value">
                    <?= $dashboardStats['created_operas'] ?>
                </strong>
            </div>


            <div class="admin-stat-card">
                <span class="admin-stat-label">
                    Actieve locaties
                </span>

                <strong class="admin-stat-value">
                    <?= $dashboardStats['active_locations'] ?>
                </strong>

                <span class="admin-stat-description">
                    door heel Nederland
                </span>
            </div>


            <div class="admin-stat-card">
                <span class="admin-stat-label">
                    Goedgekeurde producers
                </span>

                <strong class="admin-stat-value">
                    <?= $dashboardStats['approved_producers'] ?>
                </strong>
            </div>


            <div class="admin-stat-card">
                <span class="admin-stat-label">
                    Nieuwe aanmeldingen
                </span>

                <strong class="admin-stat-value">
                    <?= $dashboardStats['new_registrations'] ?>
                </strong>
            </div>

        </section>


        <section class="admin-dashboard-main-grid">


            <div class="admin-dashboard-panel admin-recent-operas">

                <div class="admin-panel-header">

                    <div>
                        <h2>Recent aangemaakte opera's</h2>
                        <p>Titel · Datum aangemaakt · Producer</p>
                    </div>

                </div>


                <div class="opera-list">

                    <?php foreach ($createdOperas as $opera): ?>

                        <div class="opera-list-item">

                            <span class="opera-title">
                                <?= htmlspecialchars($opera['Naam']) ?>
                            </span>

                            <span class="opera-date">
                                <?= htmlspecialchars($opera['Aangemaakt_op']) ?>
                            </span>

                            <span class="opera-producer">
                                <?= htmlspecialchars(
                                    $opera['Voornaam'] . ' ' .
                                    $opera['Tussenvoegsel'] . ' ' .
                                    $opera['Achternaam']
                                ) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>


                <a href="index.php?page=Opera_overzicht_beheerders" class="admin-panel-link">
                    Bekijk alle opera's →
                </a>

            </div>


            <div class="admin-dashboard-panel admin-locations">

                <div class="admin-panel-header">

                    <h2>Locaties</h2>

                    <a href="index.php?page=Locatie_overzicht" class="admin-panel-link">
                        Bekijk alle →
                    </a>

                </div>


                <ul class="location-list">

                    <?php foreach ($createdLocaties as $location): ?>

                        <li class="location-list-item">
                            <span class="location-icon">✦</span>

                            <?= htmlspecialchars($location['Naam'].', '.$location['Plaats']) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        </section>


        <section class="admin-dashboard-secondary-grid">

            <div class="admin-dashboard-panel admin-producers">

                <div class="admin-panel-header">

                    <h2>Producers</h2>

                    <a href="#" class="admin-panel-link">
                        Bekijk alle →
                    </a>

                </div>


                <div class="producer-list">

                    <?php foreach ($producers as $producer): ?>

                        <div class="producer-list-item">

                            <span class="producer-name">
                                • <?= htmlspecialchars($producer['Voornaam'] . ' ' .
                                                             $producer['Tussenvoegsel'] . ' ' .
                                                             $producer['Achternaam']) ?>
                            </span>

                            <span class="producer-opera-count">
                                <?= $producer['opera_count'] ?> opera's
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <div class="admin-dashboard-panel admin-new-registrations">

                <div class="admin-panel-header">

                    <div>
                        <h2>Nieuw aangemelde producers</h2>

                        <p>
                            <?= $dashboardStats['new_registrations'] ?>
                            aanvragen wachten op je beoordeling
                        </p>
                    </div>

                </div>


                <div class="registration-list">

                    <?php foreach ($createdProducers as $registration): ?>

                        <div class="registration-item">

                            <div class="registration-info">

                                <strong>
                                    <?= htmlspecialchars($registration['Voornaam'] . ' ' .
                                        $registration['Tussenvoegsel'] . ' ' .
                                        $registration['Achternaam']) ?>
                                </strong>

                                <span>
<!--                                    --><?php //= htmlspecialchars($registration['registered']) ?>
                                </span>

                            </div>


                            <a href="index.php?page=Producers_goedkeuren" class="registration-action">
                                Goedkeuren / afkeuren →
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

    </div>
</div>

