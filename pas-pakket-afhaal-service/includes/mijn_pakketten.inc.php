<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * TIJDELIJKE TEST LOGIN
 * Verwijder dit later als echte login werkt
 */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2; // Test gebruiker
}

// DB connectie (komt uit index.php, maar check voor zekerheid)
if (!isset($pdo)) {
    require_once __DIR__ . '/../Private/pas_connection.php';
}

$klantId = $_SESSION['user_id'];

// ===== DATA OPHALEN =====
$sql = "
    SELECT 
        p.pakket_id,
        p.track_trace_code,
        ps.status,
        p.ophaal_datum,
        p.aflever_datum
    FROM pakketten p
    JOIN pakket_status ps ON p.status_id = ps.status_id
    WHERE p.user_id = :user_id
    ORDER BY p.ophaal_datum DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $klantId]);
$pakketten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="mijn-pakketten">
    <header class="mijn-pakketten__header">
        <h2 class="mijn-pakketten__title">Pakketten Overzicht</h2>
    </header>

    <div class="mijn-pakketten__table-wrap">
        <table class="mijn-pakketten__table">
            <thead class="mijn-pakketten__thead">
            <tr class="mijn-pakketten__tr mijn-pakketten__tr--head">
                <th class="mijn-pakketten__th">Trackingnummer</th>
                <th class="mijn-pakketten__th">Status</th>
                <th class="mijn-pakketten__th">Ophaaldatum</th>
                <th class="mijn-pakketten__th">Afleverdatum</th>
                <th class="mijn-pakketten__th mijn-pakketten__th--actions">Acties</th>
            </tr>
            </thead>

            <tbody class="mijn-pakketten__tbody">
            <?php if (empty($pakketten)): ?>
                <tr class="mijn-pakketten__tr mijn-pakketten__tr--empty">
                    <td class="mijn-pakketten__td mijn-pakketten__td--empty" colspan="5">
                        Geen pakketten gevonden.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pakketten as $pakket): ?>
                    <tr class="mijn-pakketten__tr">
                        <td class="mijn-pakketten__td mijn-pakketten__td--tt">
                            <?= htmlspecialchars($pakket['track_trace_code']) ?>
                        </td>

                        <td class="mijn-pakketten__td mijn-pakketten__td--status">
                            <span class="mijn-pakketten__status-pill">
                                <?= htmlspecialchars($pakket['status']) ?>
                            </span>
                        </td>

                        <td class="mijn-pakketten__td">
                            <?= $pakket['ophaal_datum']
                                ? date('d-m-Y', strtotime($pakket['ophaal_datum']))
                                : '—'
                            ?>
                        </td>

                        <td class="mijn-pakketten__td">
                            <?= $pakket['aflever_datum']
                                ? date('d-m-Y', strtotime($pakket['aflever_datum']))
                                : '—'
                            ?>
                        </td>

                        <td class="mijn-pakketten__td mijn-pakketten__td--actions">
                            <a class="mijn-pakketten__btn"
                               href="index.php?page=pakket_details&pakket_id=<?= (int)$pakket['pakket_id'] ?>">
                                Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
