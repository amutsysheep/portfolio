<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../private/connection.php';


$statusFilter = isset($_GET['status_id']) && ctype_digit($_GET['status_id']) ? (int)$_GET['status_id'] : null;

// Status opties ophalen voor dropdown
$statusRows = $pdo->query("SELECT status_id, status FROM pakket_status ORDER BY status_id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pakketten ophalen (laatste eerst)
$sql = "
SELECT
    p.pakket_id,
    p.track_trace_code,
    p.gewicht,
    p.lengte,
    p.breedte,
    p.hoogte,
    ps.status AS status_naam,

    u.user_id,
    u.naam AS klant_naam,
    u.achternaam AS klant_achternaam,

    
    COALESCE(gp.ophaal_datum, p.ophaal_datum)   AS ophaal_datum,
    COALESCE(gp.afgeleverd_op, p.aflever_datum) AS aflever_datum,

    c.bedrijfsnaam AS courier_bedrijfsnaam

FROM pakketten p
JOIN gebruikers u ON u.user_id = p.user_id
JOIN pakket_status ps ON ps.status_id = p.status_id

LEFT JOIN geclaimde_pakketten gp ON gp.pakket_id = p.pakket_id
LEFT JOIN couriers c ON c.courier_id = gp.courier_id
";

$params = [];
if ($statusFilter !== null) {
    $sql .= " WHERE p.status_id = :status_id ";
    $params[':status_id'] = $statusFilter;
}

$sql .= " ORDER BY p.pakket_id DESC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pakketten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// helper voor datum netjes tonen
function fmt_date($d): string {
    if (!$d) return '—';
    try {
        return (new DateTime($d))->format('d-m-Y');
    } catch (Exception $e) {
        return '—';
    }
}
?>

<main class="content">
    <section class="pakket-panel">
        <div class="panel-header">
            <h1>Pakketen  Overzicht</h1>

            <div class="filter-wrapper">
                <label for="status-filter">Sorteren op status:</label>

                <select id="status-filter" name="status-filter">
                    <option value="alle" <?= $statusFilter === null ? 'selected' : '' ?>>Alle</option>
                    <?php foreach ($statusRows as $s): ?>
                        <option value="<?= (int)$s['status_id'] ?>" <?= ($statusFilter === (int)$s['status_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Trackingnummer</th>
                    <th>Klant</th>
                    <th>Status</th>
                    <th>Ophaaldatum</th>
                    <th>Afleverdatum</th>
                    <th>Gewicht</th>
                    <th>Afmeting</th>
                    <th>Courier</th>
                    <th>Acties</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($pakketten)): ?>
                    <tr>
                        <td colspan="9">Geen pakketten gevonden.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pakketten as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['track_trace_code']) ?></td>
                            <td><?= htmlspecialchars($p['klant_naam'] . ' ' . $p['klant_achternaam']) ?></td>
                            <td><?= htmlspecialchars($p['status_naam']) ?></td>

                            <td><?= fmt_date($p['ophaal_datum'] ?? null) ?></td>
                            <td><?= fmt_date($p['aflever_datum'] ?? null) ?></td>

                            <td><?= htmlspecialchars($p['gewicht']) ?> kg</td>
                            <td><?= (int)$p['lengte'] ?>x<?= (int)$p['breedte'] ?>x<?= (int)$p['hoogte'] ?></td>
                            <td><?= htmlspecialchars($p['courier_bedrijfsnaam'] ?? 'Ongeclaimd') ?></td>

                            <td class="actions">
                                <a class="btn" href="index.php?page=pakket_details&pakket_id=<?= (int)$p['pakket_id'] ?>">Details</a>

                                <?php if (empty($p['courier_bedrijfsnaam'])): ?>
                                    <a class="btn" href="index.php?page=pakket_toewijzen&pakket_id=<?= (int)$p['pakket_id'] ?>">Toewijzen</a>
                                <?php else: ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </section>
</main>

<script>
    // Dropdown filter: stuur naar dezelfde pagina met status_id
    (function () {
        const sel = document.getElementById('status-filter');
        if (!sel) return;

        sel.addEventListener('change', function () {
            const v = sel.value;
            const base = 'index.php?page=alle_pakketten_overzicht';
            if (v === 'alle') {
                window.location.href = base;
            } else {
                window.location.href = base + '&status_id=' + encodeURIComponent(v);
            }
        });
    })();
</script>
