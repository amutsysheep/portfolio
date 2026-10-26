
    <?php
    $_SESSION['Rol_id'] = ['Rol_id'];

    $message_producer_beheer = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);

    if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 1) {
        $_SESSION['error'] = $_SESSION['Rol_id'];
        header('Location: index.php?page=Login');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            Producer_id,
            Voornaam,
            Tussenvoegsel,
            Achternaam,
            Producer_status_id
        FROM producers
        WHERE Producer_status_id = 3
    ");

    $stmt->execute();

    $pendingProducers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT
        Producer_id,
        Voornaam,
        Tussenvoegsel,
        Achternaam,
        Producer_status_id
    FROM producers
    WHERE Producer_status_id = 1
");

$stmt->execute();

$approvedProducers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="producer-management">

    <div class="producer-management-container">

        <header class="producer-management-header">
            <h1>Producer Beheer</h1>
            <p>
                Beheer producer-aanmeldingen en goedgekeurde producers.
            </p>
        </header>


        <?php if ($message_producer_beheer): ?>

            <div class="producer-message">
                <?= htmlspecialchars($message_producer_beheer) ?>
            </div>

        <?php endif; ?>


        <!-- WACHTENDE PRODUCERS -->

        <section class="producer-management-panel">

            <div class="producer-panel-header">

                <div>
                    <h2>Nieuwe producer-aanmeldingen</h2>

                    <p>
                        Producers die wachten op goedkeuring.
                    </p>
                </div>

                <span class="producer-count">
                    <?= count($pendingProducers) ?> aanvragen
                </span>

            </div>


            <div class="producer-table-wrapper">

                <table class="producer-table">

                    <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($pendingProducers)): ?>

                        <tr>
                            <td colspan="5" class="producer-empty">
                                Er zijn momenteel geen nieuwe producer-aanmeldingen.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($pendingProducers as $producer): ?>

                            <?php
                            $volledigeNaam = trim(
                                $producer['Voornaam'] . ' ' .
                                $producer['Tussenvoegsel'] . ' ' .
                                $producer['Achternaam']
                            );
                            ?>

                            <tr>

                                <td>
                                    <strong class="producer-table-name">
                                        <?= htmlspecialchars($volledigeNaam) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="producer-status producer-status-pending">
                                        In afwachting
                                    </span>
                                </td>

                                <td>

                                    <div class="producer-actions">

                                        <a
                                            href="PHP/Producer_goedkeuren.php?Producer_id=<?= $producer['Producer_id'] ?>"
                                            class="producer-action producer-action-approve"
                                            onclick="return confirm('Deze producer goedkeuren?');"
                                        >
                                            Goedkeuren
                                        </a>

                                        <button
                                            type="button"
                                            class="producer-action producer-action-reject"
                                            onclick="openProducerRejectModal(
                                            <?= $producer['Producer_id'] ?>,
                                                '<?= htmlspecialchars($volledigeNaam, ENT_QUOTES) ?>'
                                                )"
                                        >
                                            Afwijzen
                                        </button>

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


<!-- AFWIJZEN MODAL -->

<div id="producerRejectModal" class="producer-modal hidden">

    <div class="producer-modal-content">

        <button
            type="button"
            class="producer-modal-close"
            onclick="closeProducerRejectModal()"
        >
            ×
        </button>

        <h2>Producer afwijzen</h2>

        <p id="producerRejectName"></p>

        <form method="POST" action="PHP/Producer_afwijzen.php">

            <input
                type="hidden"
                name="Producer_id"
                id="producerRejectId"
            >

            <label for="producerRejectReason">
                Reden van afwijzing
            </label>

            <textarea
                name="reason"
                id="producerRejectReason"
                required
                placeholder="Waarom wordt deze producer afgewezen?"
            ></textarea>

            <div class="producer-modal-actions">

                <button
                    type="submit"
                    class="producer-modal-confirm"
                >
                    Afwijzen
                </button>

                <button
                    type="button"
                    class="producer-modal-cancel"
                    onclick="closeProducerRejectModal()"
                >
                    Annuleren
                </button>

            </div>

        </form>

    </div>

</div>


<script>

    function openProducerRejectModal(producerId, name) {

        document.getElementById('producerRejectId').value = producerId;

        document.getElementById('producerRejectName').textContent =
            'Je staat op het punt ' + name + ' af te wijzen.';

        document
            .getElementById('producerRejectModal')
            .classList
            .remove('hidden');
    }


    function closeProducerRejectModal() {

        document
            .getElementById('producerRejectModal')
            .classList
            .add('hidden');
    }

</script>