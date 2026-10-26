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

if ($cart_item_id <= 0) {
    die("Invalid item ID.");
}

try {
    // Step 1: Confirm ownership of the cart item
    $stmt = $pdo->prepare("
        SELECT ci.cart_id, c.userid
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        WHERE ci.cart_item_id = ?
    ");
    $stmt->execute([$cart_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item || $item['userid'] != $user_id) {
        die("Item not found or permission denied.");
    }

    // Step 2: Delete the cart item
    $delete_stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
    $delete_stmt->execute([$cart_item_id]);

    // Step 3: Redirect back to cart
    header("Location: ../../index.php?page=basket");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>