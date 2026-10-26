<div class="form-container product-overview">
    <div class="form-box">
        <h2>Winkelmand gast</h2>

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
            <?php
            $totalAmount = 0;

            // Check if the cart exists in the session
            if (isset($_SESSION['winkelmand']) && !empty($_SESSION['winkelmand'])) {
                $session_cart = $_SESSION['winkelmand'];
                foreach ($session_cart as $productid => $amount) {
                    $sql = "SELECT producten.Product_id, producten.Naam_product, producten.Beschrijving, producten.Prijs, producten.Foto, categorien.Categorie_naam
                            FROM producten
                            LEFT JOIN categorien ON producten.Categorie_id = categorien.Categorie_id
                            WHERE producten.Product_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $productid, PDO::PARAM_INT);
                    $stmt->execute();
                    $products = $stmt->fetch(PDO::FETCH_ASSOC);

                    $subtotal = $products['Prijs'] * $amount;
                    $totalAmount += $subtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($products['Naam_product']); ?></td>
                        <td><?= htmlspecialchars($products['Beschrijving']); ?></td>
                        <td>&euro;<span class="price" data-price="<?= $products['Prijs'] ?>"><?= number_format($products['Prijs'], 2); ?></span></td>
                        <td>
                            <input type="number" name="aantal" value="<?= htmlspecialchars($amount); ?>" min="1"
                                   data-product-id="<?= htmlspecialchars($products['Product_id']); ?>"
                                   class="quantity-input">
                        </td>
                        <td>
                            <form method="post" action="PHP/CartRemove.php" class="remove-product-form">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($products['Product_id']); ?>">
                                <button type="submit" class="delete-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5">Je winkelmand is leeg.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php if (isset($_SESSION['winkelmand']) && !empty($_SESSION['winkelmand'])): ?>
            <div class="total-amount">
                <strong>Total Amount: &euro;<span id="total-amount"><?= number_format($totalAmount, 2); ?></span></strong>
            </div>

            <div class="order-button">
                <form action="index.php?page=CheckoutGuest" method="post">
                    <button type="submit" class="place-order-button">Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

