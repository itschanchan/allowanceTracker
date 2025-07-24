<?php
    session_start();
    include_once("connect.php");

    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        exit("Unauthorized");
    }

    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'] ?? null;
    $category = trim($_POST['category'] ?? '');

    if (!$id || !$category) {
        http_response_code(400);
        exit("Invalid data");
    }

    $stmt = $conn->prepare("UPDATE transactions SET category = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $category, $id, $user_id);

    if ($stmt->execute()) {
        echo "Category updated.";
    } else {
        http_response_code(500);
        echo "Failed to update.";
    }
?>
