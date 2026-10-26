<?php
session_start();
include "../../connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    header('location: /');
    exit;
}

if(!isset($_SESSION['userid'])) {
    $_SESSION['message'] = "You must be logged in to add to cart!";
    header('location: ../../index.php?page=login');
    exit;
}


// Set session-based guest identifier if not logged in
if (!isset($_SESSION['userid']) && !isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = bin2hex(random_bytes(16)); // Create unique guest session ID
}


$user_id = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : null;
$session_id = isset($_SESSION['guest_id']) ? $_SESSION['guest_id'] : null;

$product_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$quantity_to_add = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity_to_add <= 0) {
    echo("Invalid product or quantity.");
    header('Location: ../../index.php?page=login');
    exit();
}

try {
    // 🔍 Step 1: Check available stock for the product
    $stmt_stock = $pdo->prepare("SELECT quantity FROM lesson_packages WHERE id = ?");
    $stmt_stock->execute([$product_id]);
    $product = $stmt_stock->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    $available_stock = (int)$product['quantity'];

    // 🛒 Step 2: Get or create active cart
    if ($user_id !== null) {
        $stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE userid = ? AND status = 'active' LIMIT 1");
        $stmt_cart->execute([$user_id]);
    } else {
        $stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE session_id = ? AND status = 'active' LIMIT 1");
        $stmt_cart->execute([$session_id]);
    }

    $cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['id'];
    } else {
        $stmt_create = $pdo->prepare("INSERT INTO cart (userid, session_id) VALUES (?, ?)");
        $stmt_create->execute([$user_id, $session_id]);
        $cart_id = $pdo->lastInsertId();
    }

    // 🔄 Step 3: Check if product already exists in the cart
    $stmt_check_item = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND cart_id = ?");
    $stmt_check_item->execute([$cart_id, $product_id]);
    $existing_item = $stmt_check_item->fetch(PDO::FETCH_ASSOC);

    $current_cart_qty = $existing_item ? (int)$existing_item['quantity'] : 0;
    $new_total_qty = $current_cart_qty + $quantity_to_add;

    // 🚫 Validate against available stock
    if ($new_total_qty > $available_stock) {
        $_SESSION['message'] = "Only $available_stock item(s) in stock. You already have $current_cart_qty in your basket.";
        header("Location: ../../index.php?page=basket");
        exit;
    }

    // ✅ Step 4: Insert or update the cart item
    if ($existing_item) {
        $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $stmt_update->execute([$new_total_qty, $existing_item['cart_item_id']]);
    } else {
        $stmt_insert = $pdo->prepare("INSERT INTO cart_items (cart_id, lessonpackagesid ,quantity) VALUES (?, ?, ?)");
        $stmt_insert->execute([$cart_id, $product_id, $quantity_to_add]);
    }

    $_SESSION['message'] = "Item added to your cart.";
    header("Location: ../../index.php?page=basket");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
