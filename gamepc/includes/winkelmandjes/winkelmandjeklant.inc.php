<?php

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['User_id'])) {
    header("Location: inlog.php"); // Redirect naar inlogpagina als de gebruiker niet is ingelogd
    exit();
}

// Verbinding maken met de database
include '../PrivateW/connection_GamePC.php';

// Initialiseer het totaalbedrag en winkelmanditems
$totalAmount = 0;
$cartItems = [];

try {
    // Verkrijg de user_id van de ingelogde gebruiker
    $user_id = $_SESSION['User_id'];

    // SQL query om winkelmanditems op te halen
    $sql = "SELECT producten.Product_id, producten.Naam_product, producten.Beschrijving, producten.Prijs, winkelmandje.Aantal
            FROM producten
            INNER JOIN winkelmandje ON producten.Product_id = winkelmandje.Product_id
            WHERE winkelmandje.User_id = :user_id";

    // Bereid de query voor en bind de user_id
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Haal de resultaten op
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bereken het totaalbedrag
    foreach ($cartItems as $item) {
        $totalAmount += $item['Prijs'] * $item['Aantal'];
    }

} catch (PDOException $e) {
    // Foutafhandelingslogica
    error_log("Database Error: " . $e->getMessage());
    $error_message = "Er is een probleem opgetreden bij het laden van je winkelmandje. Probeer het later opnieuw.";
} catch (Exception $e) {
    // Algemene fout
    $error_message = $e->getMessage();
}

?>

<div class="form-container product-overview">
    <div class="form-box">
        <h2>Jouw winkelmand</h2>
        <table class="styled-table">
            <thead>
            <tr>
                <th>Naam</th>
                <th>Beschrijving</th>
                <th>Prijs</th>
                <th>Aantal</th>
                <th>Actie</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['Naam_product']); ?></td>
                        <td><?= htmlspecialchars($item['Beschrijving']); ?></td>
                        <td class="price">&euro;<?= number_format($item['Prijs'], 2); ?></td>
                        <td>
                            <div class="quantity-container">
                                <input type="number"
                                       name="aantal"
                                       value="<?= htmlspecialchars($item['Aantal']); ?>"
                                       min="1"
                                       required
                                       class="quantity-input"
                                       data-product-id="<?= htmlspecialchars($item['Product_id']); ?>">
                            </div>
                        </td>
                        <td>
                            <form method="post" action="php/winkelmandjeverwijder.php">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['Product_id']); ?>">
                                <button type="submit" class="delete-button">Verwijderen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Je winkelmand is leeg.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($cartItems)): ?>
            <div class="total-amount">
                <strong>Totaalbedrag: &euro;<span id="total-amount"><?= number_format($totalAmount, 2); ?></span></strong>
            </div>

            <div class="order-button">
                <form action="index.php?page=CheckoutCustomer" method="post">
                    <button type="submit" class="place-order-button">Bestellen</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const quantityInputs = document.querySelectorAll(".quantity-input");

        // Update totaalbedrag dynamisch
        function updateTotal() {
            let total = 0;
            document.querySelectorAll("tr").forEach(row => {
                const price = parseFloat(row.querySelector(".price")?.textContent.replace("€", "").trim() || 0);
                const quantity = parseInt(row.querySelector(".quantity-input")?.value || 0);
                total += price * quantity;
            });

            document.getElementById("total-amount").textContent = total.toFixed(2);
        }

        // Event listener voor het aanpassen van de hoeveelheid
        quantityInputs.forEach(input => {
            input.addEventListener("change", function () {
                const productId = this.dataset.productId;
                const quantity = this.value;

                // Maak FormData voor de POST
                const formData = new FormData();
                formData.append("product_id", productId);
                formData.append("quantity", quantity);

                // Voer AJAX-verzoek uit om de hoeveelheid bij te werken
                fetch("php/winkelmandjeupdate.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data === "success") {
                            console.log("Aantal succesvol bijgewerkt.");
                            updateTotal();
                        } else {
                            console.error("Fout bij het bijwerken van aantal:", data);
                        }
                    })
                    .catch(error => console.error("Fetch error:", error));
            });
        });
    });
</script>
