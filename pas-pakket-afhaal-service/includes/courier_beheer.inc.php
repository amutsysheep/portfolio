<?php

$message_courier_beheer = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 3) {
    $_SESSION['error'] = 'Geen toegang.';
    header('Location: index.php?page=login');
    exit;
}

// Haal alle couriers op die nog niet verwerkt zijn
$stmt = $pdo->prepare("
    SELECT 
        g.user_id,
        g.naam,
        g.tussenvoegsel,
        g.achternaam,
        g.email,
        c.bedrijfsnaam,
        c.is_Goedgekeurd,
        c.aangemeld_op
    FROM gebruikers g
    INNER JOIN couriers c ON g.user_id = c.user_id
    WHERE g.rol_id = 2 AND (c.is_Goedgekeurd = 0 OR c.is_Goedgekeurd IS NULL)
");
$stmt->execute();
$couriers = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Couriers ophalen die goedgekeurd zijn
$approvedcouriers = $pdo->prepare("
    SELECT
        g.user_id,
        g.naam,
        g.tussenvoegsel,
        g.achternaam,
        g.email,
        c.bedrijfsnaam,
        c.is_Goedgekeurd,
        c.aangemeld_op,
        c.beoordeeld_op
    FROM gebruikers g 
    INNER JOIN couriers c ON g.user_id = c.user_id
    WHERE g.rol_id = 2 AND (c.is_Goedgekeurd = 1)
");
$approvedcouriers->execute();
$appcouriers = $approvedcouriers->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="courier-beheer-pagina">
    <div class="courier-beheer-container">
        <div class="courier-beheer-header">
            <h1>Courier Beheer</h1>
            <h3>Overzicht van couriers die wachten op goedkeuring.</h3>
        </div>

        <?php if ($message_courier_beheer): ?>
            <div class="message"><?= htmlspecialchars($message_courier_beheer) ?></div>
        <?php endif; ?>

        <div class="table-wrapper-courier-beheer">
            <table>
                <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Bedrijfsnaam</th>
                    <th>Aangemeld op</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($couriers as $cour): ?>
                    <?php
                    $volledigeNaam = trim($cour['naam'] . ' ' . $cour['tussenvoegsel'] . ' ' . $cour['achternaam']);
                    $status = '<span class="status-pending">In afwachting</span>';
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($volledigeNaam) ?></td>
                        <td><?= htmlspecialchars($cour['email']) ?></td>
                        <td><?= htmlspecialchars($cour['bedrijfsnaam']) ?></td>
                        <td><?= htmlspecialchars($cour['aangemeld_op']) ?></td>
                        <td><?= $status ?></td>

                        <td class="knoppen-courier-beheer">

                            <!-- Goedkeuren -->
                            <a href="PHP/courier_goedkeuren.php?user_id=<?= $cour['user_id'] ?>"
                               class="activate"
                               onclick="return confirm('Deze courier goedkeuren?');">
                                Goedkeuren
                            </a>

                            <!-- Afwijzen (modal openen) -->
                            <button
                                    class="edit"
                                    onclick="openDeclineModal(<?= $cour['user_id'] ?>, '<?= htmlspecialchars($volledigeNaam) ?>')">
                                Afwijzen
                            </button>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="courier-beheer-header">
            <h3>Overzicht van couriers die goedgekeurd zijn.</h3>
        </div>
        <div class="table-wrapper-courier-beheer">
            <table>
                <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Bedrijfsnaam</th>
                    <th>Aangemeld op</th>
                    <th>Beoordeeld op</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($appcouriers as $cour): ?>
                    <?php
                    $volledigeNaam = trim($cour['naam'] . ' ' . $cour['tussenvoegsel'] . ' ' . $cour['achternaam']);
                    $status = '<span class="status-pending">Goedgekeurd</span>';
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($volledigeNaam) ?></td>
                        <td><?= htmlspecialchars($cour['email']) ?></td>
                        <td><?= htmlspecialchars($cour['bedrijfsnaam']) ?></td>
                        <td><?= htmlspecialchars($cour['aangemeld_op']) ?></td>
                        <td><?= htmlspecialchars($cour['beoordeeld_op']) ?></td>
                        <td><?= $status ?></td>

                        <td class="knoppen-courier-beheer">

                            <a href="PHP/courier_verwijderen.php?user_id=<?= $cour['user_id'] ?>"
                               class="edit"
                               onclick="return confirm('Deze courier verwijderen?');">
                                Verwijderen
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM -->
<div id="declineModal" class="decline-modal hidden">
    <div class="decline-content">
        <h3>Courier Afwijzen</h3>
        <p id="courierName"></p>

        <form method="POST" action="PHP/courier_afwijzen.php">
            <input type="hidden" name="user_id" id="declineUserId">

            <label>Reden van afwijzing:</label>
            <textarea name="reason" required placeholder="Waarom wordt deze courier afgewezen?"></textarea>

            <div class="decline-buttons">
                <button type="submit" class="decline-confirm">Bevestigen</button>
                <button type="button" class="decline-cancel" onclick="closeDeclineModal()">Annuleren</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeclineModal(userId, name) {
        document.getElementById('declineUserId').value = userId;
        document.getElementById('courierName').textContent = "Je staat op het punt " + name + " af te wijzen.";
        document.getElementById('declineModal').classList.remove('hidden');
    }

    function closeDeclineModal() {
        document.getElementById('declineModal').classList.add('hidden');
    }
</script>
