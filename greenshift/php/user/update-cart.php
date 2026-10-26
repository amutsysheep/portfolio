<?php
session_start();
include "../../connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    header('location: /');
    exit;
}

$user_id = $_SESSION['userid'];
$cart_item_id = isset($_POST['cart_item_id']) ? (int)$_POST['cart_item_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$cart_item_id || !in_array($action, ['increment', 'decrement'])) {
    die("Invalid request.");
}

try {
    // Step 1: Fetch cart item + user check
    $stmt = $pdo->prepare("
        SELECT ci.quantity AS current_quantity, ci.cart_id, p.quantity AS stock_quantity, c.userid
        FROM cart_items ci
        JOIN lesson_packages p ON ci.lessonpackagesid = p.id
        JOIN cart c ON ci.cart_id = c.id
        WHERE ci.cart_item_id = ?
    ");
    $stmt->execute([$cart_item_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['userid'] != $user_id) {
        die("Unauthorized or item not found.");
    }

    $current_quantity = (int)$row['current_quantity'];
    $stock_quantity = (int)$row['stock_quantity'];

    // Step 2: Determine new quantity
    $new_quantity = $current_quantity;

    if ($action === 'increment' && $current_quantity < $stock_quantity) {
        $new_quantity++;
    } elseif ($action === 'decrement' && $current_quantity > 1) {
        $new_quantity--;
    }

    // Step 3: Update if changed
    if ($new_quantity !== $current_quantity) {
        $update_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $update_stmt->execute([$new_quantity, $cart_item_id]);
    }

    // Redirect back to cart page
    header("Location: ../../index.php?page=basket");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>