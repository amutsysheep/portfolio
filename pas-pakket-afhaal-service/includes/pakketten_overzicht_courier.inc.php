<?php
// SESSION START + ROL CHECK
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['rol_id']) || !in_array((int)$_SESSION['rol_id'], [2, 3], true)) {
    $_SESSION['error'] = 'Geen toegang.';

    header('Location: index.php?page=login');
    exit;
}

require 'connection/connection.php';

$courier_id = $_SESSION['user_id']; // rol 2 = courier

// FILTER: per dag/datum
$filterDatum = isset($_GET['datum']) ? $_GET['datum'] : null;

// Pakketten ophalen
$sql = "
SELECT
    p.pakket_id,
    p.track_trace_code,
    p.gewicht,
    p.pakketten_aangemeld,
    p.status_id,
    ps.status AS status_naam,
    u.naam AS klant_naam,
    u.achternaam AS klant_achternaam,
    p.ophaal_datum,
    gp.afgeleverd_op
FROM pakketten p
JOIN gebruikers u ON u.user_id = p.user_id
JOIN pakket_status ps ON ps.status_id = p.status_id
LEFT JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
";

// Alleen niet-geclaimde pakketten + filter op datum optioneel
$where = [];
$params = [];

if ($filterDatum) {
    $where[] = "DATE(p.pakketten_aangemeld) = :datum";
    $params[':datum'] = $filterDatum;
}

// Toon alleen niet-geclaimde pakketten of pakketten van deze courier
$where[] = "(gp.courier_id IS NULL OR gp.courier_id = :courier_id)";
$params[':courier_id'] = $courier_id;

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.pakket_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pakketten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functie voor datum
function fmt_date($d) {
    return $d ? (new DateTime($d))->format('d-m-Y') : '—';
}
?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php elseif (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="pakketten-pagina">
    <h2>Pakketten (Courier)</h2>

    <!-- Datum filter -->
    <form method="GET" class="status-filter">
        <input type="hidden" name="page" value="pakketten_overzicht_courier">

        <label for="datum">Filter datum:</label>

        <input
            type="date"
            name="datum"
            id="datum"
            value="<?= htmlspecialchars($filterDatum ?? '') ?>"
        >

        <button type="submit">Filter</button>

        <?php if ($filterDatum): ?>
            <a href="index.php?page=pakketten_overzicht_courier" class="btn-sm">
                Reset
            </a>
        <?php endif; ?>
    </form>



    <div class="courier-table-wrapper">
        <table class="courier-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Track & Trace</th>
                <th>Status</th>
                <th>Klant</th>
                <th>Gewicht</th>
                <th>Pakket Aangemeld</th>
                <th>Ophaaldatum</th>
                <th>Acties</th>
            </tr>
            </thead>
            <tbody>

            <?php if (!$pakketten): ?>
                <tr>
                    <td colspan="7">Geen pakketten gevonden</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pakketten as $p): ?>
                    <tr>
                        <td>#<?= $p['pakket_id'] ?></td>
                        <td><?= htmlspecialchars($p['track_trace_code']) ?></td>
                        <td><?= htmlspecialchars($p['status_naam']) ?></td>
                        <td><?= htmlspecialchars($p['klant_naam'] . ' ' . $p['klant_achternaam']) ?></td>
                        <td><?= htmlspecialchars($p['gewicht']) ?> kg</td>
                        <td><?= htmlspecialchars($p['pakketten_aangemeld']) ?></td>
                        <td><?= fmt_date($p['ophaal_datum']) ?></td>
                        <td>
                            <?php
                            $status_id = (int)$p['status_id'];
                            ?>

                            <?php if ($status_id === 1): ?>
                                <form method="POST" action="PHP/claim_pakketten.php" style="display:inline">
                                    <input type="hidden" name="pakket_id" value="<?= $p['pakket_id'] ?>">
                                    <button type="submit">Claim</button>
                                </form>

                            <?php elseif ($status_id === 4): ?>
                                <form method="POST" action="PHP/ophalen_pakket2.php" style="display:inline">
                                    <input type="hidden" name="pakket_id" value="<?= $p['pakket_id'] ?>">
                                    <button type="submit">Ophalen</button>
                                </form>

                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>
