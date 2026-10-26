<?php
session_start();

include "../../connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name =($_POST['name'] ?? '');
    $number_of_lessons =($_POST['number_of_lessons'] ?? '');
    $price = ($_POST['price'] ?? '');
    $description =($_POST['description'] ?? '');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(" 
INSERT INTO lesson_packages  (name , number_of_lessons ,price  ,description)
values (:name, :number_of_lessons, :price, :description)
");

    $stmt->execute([
        ':name' => $name ?? '',
        ':number_of_lessons' => $number_of_lessons ?? '',
        ':price' => $price ?? '',
        ':description' => $description ?? '',
    ]);

    $pdo->commit();

    $_SESSION['message'] = "Lespakket is toegevoegd.";
    header("Location: ../../index.php?page=lessonpackage&panel=Admin");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>