<?php
session_start();
include "../../connection/connection.php";

if (!isset($_SESSION['userid'])) {
    $_SESSION['message'] = "You must be logged in to checkout!";
    header("Location: ../../index.php?page=login");
    exit;
}

$user_id = (int)$_SESSION['userid'];

// 🔹 1. Get user address
$stmt_addr = $pdo->prepare("SELECT addressid FROM user WHERE userid = ?");
$stmt_addr->execute([$user_id]);
$user_data = $stmt_addr->fetch(PDO::FETCH_ASSOC);

if (!$user_data || !$user_data['addressid']) {
    $_SESSION['message'] = "Please add a valid address before checkout.";
    header("Location: ../../index.php?page=profile");
    exit;
}

$address_id = $user_data['addressid'];

// 🔹 2. Get active cart and items
$stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE userid = ? AND status = 'active' LIMIT 1");
$stmt_cart->execute([$user_id]);
$cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    $_SESSION['message'] = "Your cart is empty.";
    header("Location: ../../index.php?page=basket");
    exit;
}

$cart_id = $cart['id'];

$stmt_items = $pdo->prepare("
    SELECT ci.lessonpackagesid, ci.quantity, lp.name, lp.price, lp.number_of_lessons
    FROM cart_items ci
    JOIN lesson_packages lp ON ci.lessonpackagesid = lp.id
    WHERE ci.cart_id = ?
");
$stmt_items->execute([$cart_id]);
$cart_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    $_SESSION['message'] = "Your cart is empty.";
    header("Location: ../../index.php?page=basket");
    exit;
}

// 🔹 3. Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// 🔹 4. Create order record
$order_id = strtoupper(substr(uniqid('ORD'), 0, 10));
$stmt_order = $pdo->prepare("
    INSERT INTO orders (order_id, user_id, addressid, total_amount, status)
    VALUES (?, ?, ?, ?, 'paid')
");
$stmt_order->execute([$order_id, $user_id, $address_id, $total]);

// 🔹 5. Prepare insert statements
$stmt_order_item = $pdo->prepare("
    INSERT INTO order_items (order_id, lesson_packages_id, product_title, quantity, price_per_item)
    VALUES (?, ?, ?, ?, ?)
");

$stmt_student_packages = $pdo->prepare("
    INSERT INTO student_packages (student_id, package_id, start_date, remaining_lessons)
    VALUES (?, ?, NOW(), ?)
");

// 🔹 6. Process each item
foreach ($cart_items as $item) {
    // Insert into order_items
    $stmt_order_item->execute([
        $order_id,
        $item['lessonpackagesid'],
        $item['name'],
        $item['quantity'],
        $item['price']
    ]);

    // Reduce stock
    $stmt_stock = $pdo->prepare("UPDATE lesson_packages SET quantity = quantity - ? WHERE id = ?");
    $stmt_stock->execute([$item['quantity'], $item['lessonpackagesid']]);

    // Insert into student_packages
    $remaining_lessons = $item['number_of_lessons'] * $item['quantity'];
    $stmt_student_packages->execute([$user_id, $item['lessonpackagesid'], $remaining_lessons]);
}

// 🔹 7. Clear cart and mark expired
$stmt_clear = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
$stmt_clear->execute([$cart_id]);

$stmt_deactivate = $pdo->prepare("UPDATE cart SET status = 'expired' WHERE id = ?");
$stmt_deactivate->execute([$cart_id]);

// 🔹 8. Done
$_SESSION['message'] = "Payment successful! Order ID: $order_id";
header("Location: ../../index.php?page=ordersuccess&order_id=" . urlencode($order_id));
exit;
?>
