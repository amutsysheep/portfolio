<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// tijdelijke test login (verwijder later als echte login werkt)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 2;
}

// DB connectie: in veel setups komt $pdo al uit index.php.
// Maar als $pdo niet bestaat, laden we hem hier als fallback.
if (!isset($pdo)) {
    require_once __DIR__ . '/../Private/pas_connection.php';
}

// Validatie pakket_id
if (!isset($_GET['pakket_id']) || !ctype_digit($_GET['pakket_id'])) {
    echo '<p>Ongeldig pakket.</p><a href="index.php?page=mijn_pakketten">← Terug</a>';
    return;
}

$pakket_id = (int)$_GET['pakket_id'];
$user_id   = (int)$_SESSION['user_id'];

// Query: alleen pakket van deze klant (veilig)
$sql = "
    SELECT
        p.pakket_id,
        p.track_trace_code,
        p.gewicht,
        p.lengte,
        p.breedte,
        p.hoogte,
        p.ophaal_datum,
        p.aflever_datum,
        ps.status,
        u.naam,
        u.achternaam
    FROM pakketten p
    JOIN gebruikers u ON u.user_id = p.user_id
    JOIN pakket_status ps ON ps.status_id = p.status_id
    WHERE p.pakket_id = :id AND p.user_id = :user_id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $pakket_id,
    ':user_id' => $user_id
]);

$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    echo '<p>Pakket niet gevonden (of je hebt geen toegang).</p><a href="index.php?page=mijn_pakketten">← Terug</a>';
    return;
}

function fmt($d) {
    return $d ? (new DateTime($d))->format('d-m-Y') : '—';
}
?>

<div class="pakket-details">
    <h1 class="pakket-details__title">Pakket details</h1>

    <ul class="pakket-details__list">
        <li class="pakket-details__item">
            <strong>Trackingnummer:</strong> <?= htmlspecialchars($p['track_trace_code']) ?>
        </li>

        <li class="pakket-details__item">
            <strong>Klant:</strong> <?= htmlspecialchars($p['naam'] . ' ' . $p['achternaam']) ?>
        </li>

        <li class="pakket-details__item">
            <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?>
        </li>

        <li class="pakket-details__item">
            <strong>Gewicht:</strong> <?= htmlspecialchars($p['gewicht']) ?> kg
        </li>

        <li class="pakket-details__item">
            <strong>Afmetingen:</strong>
            <?= (int)$p['lengte'] ?> x <?= (int)$p['breedte'] ?> x <?= (int)$p['hoogte'] ?> cm
        </li>

        <li class="pakket-details__item">
            <strong>Afhaaldatum:</strong> <?= fmt($p['ophaal_datum']) ?>
        </li>

        <li class="pakket-details__item">
            <strong>Afleverdatum:</strong> <?= fmt($p['aflever_datum']) ?>
        </li>
    </ul>

    <a class="pakket-details__back" href="index.php?page=mijn_pakketten">← Terug naar mijn pakketten</a>
</div>
