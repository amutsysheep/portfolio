<?php
if (!isset($_SESSION['userid'])) {
    header("Location: index.php?page=login");
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "<p class='text-red-500 text-center mt-10'>Invalid order ID.</p>";
    exit;
}

$order_id = $_GET['order_id'];

// ✅ Fetch order info
$stmt_order = $pdo->prepare("
    SELECT o.order_id, o.total_amount, o.status, o.ordered_at,
           a.first_name, a.last_name, a.address, a.housenumber, a.city, a.postalcode
    FROM orders o
    JOIN address a ON o.addressid = a.addressid
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt_order->execute([$order_id, $_SESSION['userid']]);
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p class='text-red-500 text-center mt-10'>Order not found.</p>";
    exit;
}

// ✅ Fetch order items
$stmt_items = $pdo->prepare("
    SELECT product_title, quantity, price_per_item
    FROM order_items
    WHERE order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-3xl mx-auto mt-16 bg-white shadow-lg rounded-2xl p-8">
    <div class="flex flex-col items-center text-center">
        <div class="text-green-500 text-6xl mb-4">✔️</div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Thank you for your payment!</h1>
        <p class="text-gray-500">Your order has been successfully placed.</p>
    </div>

    <div class="border-t border-gray-200 my-8"></div>

    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Order Summary</h2>

        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
            <p><span class="font-medium text-gray-700">Order ID:</span> <?= htmlspecialchars($order['order_id']); ?></p>
            <p><span class="font-medium text-gray-700">Status:</span>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                    <?= $order['status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                    <?= htmlspecialchars(ucfirst($order['status'])); ?>
                </span>
            </p>
            <p><span class="font-medium text-gray-700">Order Date:</span> <?= date('d-m-Y H:i', strtotime($order['ordered_at'])); ?></p>
        </div>
    </div>

    <div class="border-t border-gray-200 my-8"></div>

    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Items Purchased</h2>

        <div class="overflow-hidden border border-gray-200 rounded-xl">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Product</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-center">Price</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-4 py-3"><?= htmlspecialchars($item['product_title']); ?></td>
                        <td class="px-4 py-3 text-center"><?= (int)$item['quantity']; ?></td>
                        <td class="px-4 py-3 text-center">€<?= number_format($item['price_per_item'], 2); ?></td>
                        <td class="px-4 py-3 text-right font-medium">€<?= number_format($item['price_per_item'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            <p class="text-xl font-semibold text-gray-800">
                Total: €<?= number_format($order['total_amount'], 2); ?>
            </p>
        </div>
    </div>

    <div class="border-t border-gray-200 my-8"></div>

    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Shipping Address</h2>
        <div class="bg-gray-50 rounded-xl p-4 leading-relaxed">
            <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
            <?= htmlspecialchars($order['address']) . ' ' . htmlspecialchars($order['housenumber']); ?><br>
            <?= htmlspecialchars($order['postalcode']) . ' ' . htmlspecialchars($order['city']); ?>
        </div>
    </div>

    <div class="text-center mt-10">
        <a href="index.php?page=dashboard"
           class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-full transition">
            Go to Dashboard
        </a>
    </div>
</div>
