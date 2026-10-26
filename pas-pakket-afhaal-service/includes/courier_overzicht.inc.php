<?php
/* ===== SESSION + ROL CHECK ===== */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    $_SESSION['error'] = 'Geen toegang.';
    header('Location: index.php?page=login');
    exit;
}

require 'connection/connection.php';

/* ===== USER → COURIER ===== */
$user_id = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT courier_id
    FROM couriers
    WHERE user_id = :uid
");
$stmt->execute([':uid' => $user_id]);

$courier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$courier) {
    echo '<p>Courier niet gevonden.</p>';
    exit;
}

$courier_id = (int) $courier['courier_id'];

/* ===== DATUMFILTER ===== */
$filterDatum = $_GET['datum'] ?? '';
if ($filterDatum) {
    $d = DateTime::createFromFormat('Y-m-d', $filterDatum);
    $filterDatum = $d ? $d->format('Y-m-d') : '';
}

/* ===== PAKKETTEN OPHALEN ===== */
$sql = "
    SELECT
        p.pakket_id,
        p.track_trace_code,
        p.gewicht,
        ps.status AS status_naam,
        p.status_id, 
        gp.ophaal_datum,
        u.naam AS klant_naam,
        u.achternaam AS klant_achternaam
    FROM geclaimde_pakketten gp
    JOIN pakketten p ON p.pakket_id = gp.pakket_id
    JOIN pakket_status ps ON ps.status_id = p.status_id
    JOIN gebruikers u ON u.user_id = p.user_id
    WHERE gp.courier_id = :cid
";

$params = [':cid' => $courier_id];

if ($filterDatum) {
    $sql .= " AND DATE(gp.ophaal_datum) = :datum";
    $params[':datum'] = $filterDatum;
}

$sql .= " ORDER BY gp.ophaal_datum DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$pakketten = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== HELPER ===== */
function fmt_date($d) {
    return $d ? (new DateTime($d))->format('d-m-Y') : '—';
}
?>

<!-- ===== MELDINGEN ===== -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="pakketten-pagina">
    <h2>Mijn geclaimde pakketten</h2>

    <!-- ===== FILTER FORM ===== -->
    <form method="GET" class="status-filter">
        <input type="hidden" name="page" value="courier_overzicht">

        <label for="datum">Filter datum:</label>
        <input
            type="date"
            name="datum"
            id="datum"
            value="<?= htmlspecialchars($filterDatum) ?>"
        >
        <button type="submit">Filter</button>

        <?php if ($filterDatum): ?>
            <a href="index.php?page=courier_overzicht" class="btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (!$pakketten): ?>
        <p>Je hebt nog geen pakketten geclaimd.</p>
    <?php else: ?>
        <div class="courier-table-wrapper">
            <table class="courier-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Track & Trace</th>
                    <th>Status</th>
                    <th>Klant</th>
                    <th>Gewicht</th>
                    <th>Ophaaldatum</th>
                    <th>Actie</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pakketten as $p): ?>
                    <tr>
                        <td>#<?= $p['pakket_id'] ?></td>
                        <td><?= htmlspecialchars($p['track_trace_code']) ?></td>
                        <td><?= htmlspecialchars($p['status_naam']) ?></td>
                        <td><?= htmlspecialchars($p['klant_naam'] . ' ' . $p['klant_achternaam']) ?></td>
                        <td><?= htmlspecialchars($p['gewicht']) ?> kg</td>
                        <td><?= fmt_date($p['ophaal_datum']) ?></td>
                        <td>
                            <?php if ((int)$p['status_id'] === 2): ?>
                                <form method="POST" action="PHP/ophalen_pakket.php">
                                    <input type="hidden" name="pakket_id" value="<?= $p['pakket_id'] ?>">
                                    <button type="submit">Ophalen</button>
                                </form>
                            <?php elseif ((int)$p['status_id'] === 3): ?>
                                <form method="POST" action="PHP/doorgeven_magazijn.php">
                                    <input type="hidden" name="pakket_id" value="<?= $p['pakket_id'] ?>">
                                    <button type="submit">Doorgeven</button>
                                </form>

                            <?php elseif ((int)$p['status_id'] === 5): ?>
                                <form method="POST" action="PHP/pakket_bezorgd.php">
                                    <input type="hidden" name="pakket_id" value="<?= (int)$p['pakket_id'] ?>">
                                    <button type="submit" class="btn btn-success">
                                        ✔ Pakket bezorgd
                                    </button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
