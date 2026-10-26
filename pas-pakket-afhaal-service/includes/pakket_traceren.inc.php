<?php
require '../../private/connection.php';

?>

<div class="track-trace-container">
    <h2>Volg je pakket</h2>

    <form method="GET" class="track-form">
        <input type="hidden" name="page" value="pakket_traceren">
        <input
                type="text"
                name="code"
                placeholder="Track & Trace code"
                required
                value="<?= htmlspecialchars($_GET['code'] ?? '') ?>"
        >
        <button type="submit">Zoeken</button>
    </form>

    <?php
    if (!empty($_GET['code'])) {
        $code = trim($_GET['code']);

        // Pakket ophalen
        $stmt = $pdo->prepare("
            SELECT 
                p.pakket_id,
                p.status_id,
                ps.status
            FROM pakketten p
            JOIN pakket_status ps ON ps.status_id = p.status_id
            WHERE p.track_trace_code = :code
        ");
        $stmt->execute([':code' => $code]);
        $pakket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pakket) {
            echo '<p class="error">Geen pakket gevonden.</p>';
        } else {
            echo '<h3>Huidige status: <span>' . htmlspecialchars($pakket['status']) . '</span></h3>';

            /* ===== PROGRESS BAR BOLLETJES ===== */
            $stapLabels = ['Aangemeld','Opgehaald','Magazijn','Onderweg','Bezorgd'];
            $totalSteps = count($stapLabels);

            // Map status_id naar stap nummer
            $stappen = [1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 5];
            $huidigeStap = $stappen[$pakket['status_id']] ?? 1;

            // Breedte gevulde lijn
            $percentage = ($huidigeStap - 1) / ($totalSteps - 1) * 100;
            ?>

            <div class="progress-circles">
                <div class="progress-filled" style="width: <?= $percentage ?>%"></div>
                <?php foreach($stapLabels as $index => $label): ?>
                    <div class="circle-container">
                        <div class="circle <?= ($index+1) <= $huidigeStap ? 'filled' : '' ?>"></div>
                        <div class="circle-label"><?= $label ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            /* ===== LAATSTE 5 STATUSSEN ===== */
            $stmt = $pdo->prepare("
                SELECT 
                    ps.status,
                    h.tijdstempel
                FROM pakket_status_history h
                JOIN pakket_status ps ON ps.status_id = h.status_id
                WHERE h.pakket_id = :pid
                ORDER BY h.tijdstempel DESC
                LIMIT 5
            ");
            $stmt->execute([':pid' => $pakket['pakket_id']]);
            $history = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

            echo '<ul class="status-list">';
            foreach ($history as $h) {
                echo '<li>';
                echo htmlspecialchars($h['status']);
                echo '<span>' . date('d-m-Y H:i', strtotime($h['tijdstempel'])) . '</span>';
                echo '</li>';
            }
            echo '</ul>';
        }
    }
    ?>
</div>