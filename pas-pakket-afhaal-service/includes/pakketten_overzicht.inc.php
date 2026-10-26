<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

require 'connection/connection.php';

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        p.pakket_id,
        p.track_trace_code,
        p.Gewicht,
        p.pakketten_aangemeld,
        s.status
    FROM pakketten p
    JOIN pakket_status s ON p.status_id = s.status_id
    WHERE p.user_id = :user_id
    ORDER BY p.pakket_id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$pakketten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php elseif (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success']; unset($_SESSION['succes']); ?>
    </div>
<?php endif; ?>

<div class="pakketten-pagina">
    <h2>Mijn pakketten</h2>

    <?php if (empty($pakketten)): ?>
        <p>Geen aangemelde pakketten gevonden.</p>
    <?php else: ?>
        <table class="pakketten-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Track & Trace</th>
                <th>Status</th>
                <th>Datum aangemeld</th>
                <th>Gewicht</th>
                <th>Acties</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($pakketten as $p): ?>
                <tr>
                    <td>#<?= $p['pakket_id'] ?></td>
                    <td><?= htmlspecialchars($p['track_trace_code']) ?></td>
                    <td><?= htmlspecialchars($p['status']) ?></td>
                    <td><?= htmlspecialchars($p['pakketten_aangemeld']) ?></td>
                    <td><?= htmlspecialchars($p['Gewicht']) ?> kg</td>
                    <td>
                        <a class="bekijk-btn" href="index.php?page=pakket_details&id=<?= $p['pakket_id'] ?>">
                            Bekijken
                        </a>


                        <?php if ($p['status'] === 'aangemeld'): ?>
                            <button class="annuleer-btn" data-id="<?= $p['pakket_id'] ?>">
                                Annuleren
                            </button>
                        <?php elseif ($p['status'] === 'pakket bezorgd'): ?>
                            <button class="verwijder-btn" data-id="<?= $p['pakket_id'] ?>">
                                Verwijderen
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ================= ANNULEREN MODAL ================= -->
<div id="annuleer-modal" class="modal hidden">
    <div class="modal-box">
        <h3>Pakket annuleren</h3>
        <p>Weet je zeker dat je dit pakket wilt annuleren?</p>
        <div class="modal-actions">
            <button class="modal-close">Nee</button>
            <a id="annuleer-confirm" class="confirm-btn">
                Ja, annuleren
            </a>
        </div>
    </div>
</div>

<!-- ================= VERWIJDEREN MODAL ================= -->
<div id="verwijder-modal" class="modal hidden">
    <div class="modal-box">
        <h3>Pakket verwijderen</h3>
        <p>Weet je zeker dat je dit pakket wilt verwijderen?</p>
        <div class="modal-actions">
            <button class="modal-close">Nee</button>
            <a id="verwijder-confirm" class="confirm-btn danger">
                Ja, verwijderen
            </a>
        </div>
    </div>
</div>

<script>
    const annuleerModal = document.getElementById('annuleer-modal');
    const verwijderModal = document.getElementById('verwijder-modal');

    const annuleerConfirm = document.getElementById('annuleer-confirm');
    const verwijderConfirm = document.getElementById('verwijder-confirm');

    document.querySelectorAll('.annuleer-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            annuleerConfirm.href =
                'PHP/pakketten_annuleren.php?id=' + btn.dataset.id;
            annuleerModal.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.verwijder-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            verwijderConfirm.href =
                'PHP/pakketten_verwijderen.php?id=' + btn.dataset.id;
            verwijderModal.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            annuleerModal.classList.add('hidden');
            verwijderModal.classList.add('hidden');
        });
    });

    annuleerModal.addEventListener('click', (e) => {
        if (e.target === annuleerModal) {
            annuleerModal.classList.add('hidden');
        }
    });

    verwijderModal.addEventListener('click', (e) => {
        if (e.target === verwijderModal) {
            verwijderModal.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            annuleerModal.classList.add('hidden');
            verwijderModal.classList.add('hidden');
        }
    });
</script>

