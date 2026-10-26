<?php

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['userid'];

// Kleine veilige outputfunctie om null-waarden te voorkomen
function safe($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Get active cart
$stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE userid = ? AND status = 'active' LIMIT 1");
$stmt_cart->execute([$user_id]);
$cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

// Fetch cart items
$cart_items = [];
$subtotal = 0.0;

if ($cart) {
    $stmt_items = $pdo->prepare("
        SELECT ci.quantity, p.id AS lesson_packages_id, p.name, p.number_of_lessons, p.price
        FROM cart_items ci
        JOIN lesson_packages p ON ci.lessonpackagesid = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt_items->execute([$cart['id']]);
    $cart_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cart_items as $item) {
        $subtotal += $item['quantity'] * $item['price'];
    }
}

// Totals
$shipping = 0.00;
$tax = $subtotal * 0.21;
$total = $subtotal + $shipping + $tax;

// Get the user's address using the linked addressid
$stmt_user = $pdo->prepare("SELECT u.*, a.* 
    FROM user u
    LEFT JOIN address a ON u.addressid = a.addressid
    WHERE u.userid = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- CHECKOUT FORM -->
    <div class="lg:col-span-2 space-y-6">
        <form class="bg-white p-6 rounded-lg shadow space-y-6" method="POST" action="php/user/checkout_process.php">
            <h2 class="text-xl font-semibold text-green-600">Checkout</h2>

            <?php if (!empty($user['addressid'])): ?>
                <!-- Show linked address -->
                <div class="bg-gray-50 p-4 rounded-md border">
                    <p class="font-medium text-gray-800">
                        <?= safe(trim("{$user['first_name']} {$user['infix']} {$user['last_name']}")) ?>
                    </p>
                    <p><?= safe("{$user['address']} {$user['housenumber']}") ?></p>
                    <p><?= safe("{$user['postalcode']} {$user['city']}") ?></p>
                    <?php if (!empty($user['phonenumber'])): ?>
                        <p><?= safe($user['phonenumber']) ?></p>
                    <?php endif; ?>
                    <input type="hidden" name="address_id" value="<?= safe($user['addressid']) ?>">
                </div>

            <?php else: ?>
                <!-- If no address is linked to the user -->
                <p class="text-gray-600">You don’t have a saved address yet. Please add one:</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input name="firstname" placeholder="First name" class="border p-2 rounded" required />
                    <input name="infix" placeholder="Infix" class="border p-2 rounded" />
                    <input name="lastname" placeholder="Last name" class="border p-2 rounded" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input name="street" placeholder="Street" class="border p-2 rounded" required />
                    <input name="housenumber" placeholder="House #" class="border p-2 rounded" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input name="zipcode" placeholder="Zipcode" class="border p-2 rounded" required />
                    <input name="city" placeholder="City" class="border p-2 rounded" required />
                </div>
                <input name="phonenumber" placeholder="Phone number" class="border p-2 rounded w-full" />
            <?php endif; ?>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded mt-4">
                Pay Now
            </button>
        </form>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="bg-white p-6 rounded-lg shadow space-y-4 self-start">
        <h3 class="text-lg font-semibold text-gray-800">Order Summary</h3>

        <?php foreach ($cart_items as $item): ?>
            <div class="flex gap-4 border-b pb-4">
                <div>
                    <h4 class="font-medium"><?= safe($item['name']) ?></h4>
                    <p class="text-sm text-gray-500"><?= safe($item['number_of_lessons']) ?> lessons</p>
                    <p class="text-sm">Qty: <?= safe($item['quantity']) ?></p>
                    <p class="text-green-600 font-medium mt-1">&euro;<?= number_format($item['price'], 2) ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="text-sm text-gray-700 space-y-2 pt-4">
            <div class="flex justify-between"><span>Subtotal</span><span>&euro;<?= number_format($subtotal, 2) ?></span></div>
            <div class="flex justify-between"><span>Shipping</span><span>&euro;<?= number_format($shipping, 2) ?></span></div>
            <div class="flex justify-between"><span>Tax</span><span>&euro;<?= number_format($tax, 2) ?></span></div>
            <div class="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>&euro;<?= number_format($total, 2) ?></span></div>
        </div>
    </div>
</div>
