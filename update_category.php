<?php
    session_start();
    include_once("connect.php");

    if (!isset($_SESSION['user_id'])) {
        exit("Error: Unauthorized access. Please log in.");
    }

    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'] ?? null;
    $category = trim($_POST['category'] ?? '');

    if (!$id || !$category) {
        exit("Error: Invalid input data.");
    }

    $stmt = $conn->prepare("UPDATE transactions SET category = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $category, $id, $user_id);

    if ($stmt->execute()) {
        echo "Success: Category updated.";
    } else {
        echo "Error: Failed to update the category.";
    }
?>
