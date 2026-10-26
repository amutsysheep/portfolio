<?php
session_start();

include "../../connection/connection.php";

$instructorId = $_GET['id'] ?? null;

if (!$instructorId) {
    $_SESSION['message'] = "Lesson Packages ID not provided.";
    header("Location: ../../index.php?page=editlessonpackages&panel=Admin&id= $instructorId");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name =($_POST['name'] ?? '');
    $number_of_lessons =($_POST['number_of_lessons'] ?? '');
    $price = ($_POST['price'] ?? '');
    $description =($_POST['description'] ?? '');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(" 
UPDATE lesson_packages set name = :name, number_of_lessons = :number_of_lessons, price = :price, description = :description
WHERE id = :id
");

    $stmt->execute([
        ':id' => $instructorId,
        ':name' => $name ?? '',
        ':number_of_lessons' => $number_of_lessons ?? '',
        ':price' => $price ?? '',
        ':description' => $description ?? '',
    ]);

    $pdo->commit();

    $_SESSION['message'] = "lesson packages details updated successfully!";
    header("Location: ../../index.php?page=lessonpackage&panel=Admin&id= $instructorId");
    exit();

}catch(Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Error: " . $e->getMessage();
    header("Location: ../../index.php?page=editlessonpackages&panel=Admin&id= $instructorId");
    exit();
}
