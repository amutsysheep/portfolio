<?php
// Identify user or guest
$user_id = $_SESSION['userid'] ?? null;
$session_id = null;

if (!$user_id) {
    if (!isset($_SESSION['guest_id'])) {
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }
    $session_id = $_SESSION['guest_id'];
}

// Get active cart
if ($user_id) {
    $stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE userid = ? AND status = 'active' LIMIT 1");
    $stmt_cart->execute([$user_id]);
} else {
    $stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE session_id = ? AND status = 'active' LIMIT 1");
    $stmt_cart->execute([$session_id]);
}

$cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

$cart_items = [];
$total_items = 0;
$subtotal = 0.0;

if ($cart) {
    $cart_id = $cart['id'];

    $sql_items = "
        SELECT 
            ci.cart_item_id,
            ci.quantity,
            p.id AS product_id,
            p.name,
            p.price,
            p.quantity AS stock_quantity
        FROM cart_items ci
        JOIN lesson_packages p ON ci.lessonpackagesid = p.id
        WHERE ci.cart_id = ?
    ";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$cart_id]);
    $all_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    $removed_items = 0;

    foreach ($all_items as $item) {
        if ($item['stock_quantity'] <= 0) {
            // Automatically remove out-of-stock item from cart
            $stmt_remove = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
            $stmt_remove->execute([$item['cart_item_id']]);
            $removed_items++;
            continue;
        }

        $cart_items[] = $item;
        $total_items += $item['quantity'];
        $subtotal += $item['quantity'] * $item['price'];
    }

    // Optionally show a user message
    if ($removed_items > 0) {
        $_SESSION['message'] = "$removed_items item" . ($removed_items > 1 ? 's were' : ' was') . " removed from your cart due to being out of stock.";
    }
}

$shipping = 0.00; // Not implemented yet
$tax = round($subtotal * 0.00, 2);
$total = $subtotal + $tax;
?>

<!-- Shopping Basket Layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center pb-6 border-b border-gray-200">
        <h1 class="text-2xl font-semibold text-green-600">Shopping Basket</h1>
        <span class="text-gray-600"><?= $total_items ?> <?= $total_items === 1 ? 'Item' : 'Items' ?></span>
    </div>
    <?php
    if (isset($_SESSION['message'])) {
        echo '<p class="text-black text-center">' . $_SESSION['message'] . '</p>';
        unset($_SESSION['message']);
    }
    ?>
    <?php if ($total_items > 0): ?>
        <div class="mt-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="flex-1">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
                            <div class="flex gap-6">
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                        <form method="POST" action="php/user/remove-item-from-cart.php">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <button type="submit" class="text-gray-400 hover:text-gray-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="text-green-600 font-medium mt-2">€<?= number_format($item['price'], 2) ?></div>
                                    <div class="mt-4 flex items-center gap-4">
                                        <label class="text-gray-600">Qty:</label>
                                        <form method="POST" action="php/user/update-cart.php" class="flex items-center gap-2">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">

                                            <?php
                                            $disableDecrement = $item['quantity'] <= 1;
                                            $disableIncrement = $item['quantity'] >= $item['stock_quantity'];
                                            ?>

                                            <button type="submit" name="action" value="decrement"
                                                    class="px-2 py-1 text-lg font-bold bg-gray-200 rounded hover:bg-gray-300 <?= $disableDecrement ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                                    <?= $disableDecrement ? 'disabled' : '' ?>
                                                    aria-label="Decrease quantity">
                                                −
                                            </button>

                                            <span class="w-6 text-center"><?= $item['quantity'] ?></span>

                                            <button type="submit" name="action" value="increment"
                                                    class="px-2 py-1 text-lg font-bold bg-gray-200 rounded hover:bg-gray-300 <?= $disableIncrement ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                                    <?= $disableIncrement ? 'disabled' : '' ?>
                                                    aria-label="Increase quantity">
                                                +
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-96">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-3 text-gray-600">
                            <div class="flex justify-between">
                                <span>Subtotal (<?= $total_items ?> items)</span>
                                <span>€<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="flex justify-between"><span>Tax</span><span>€<?= number_format($tax, 2) ?></span></div>
                            <div class="pt-3 border-t border-gray-200 font-medium text-lg flex justify-between">
                                <span>Total</span><span>€<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                        <a href="index.php?page=checkout">
                            <button type="submit" class="w-full mt-6 bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-colors font-medium">
                                Proceed to Checkout
                            </button>
                        </a>
                    </div>

                    <a href="index.php?page=driving-lesson-package" class="block w-full mt-4 text-center text-green-600 hover:text-green-700 font-medium">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty Basket -->
        <div class="mt-12 bg-white rounded-lg shadow-sm p-8 text-center">
            <div class="max-w-md mx-auto">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Your basket is empty</h3>
                <p class="mt-2 text-gray-500">Looks like you haven't added any items yet.</p>
                <a href="index.php?page=driving-lesson-package" class="mt-6 inline-block bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-colors font-medium">
                    Start Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
