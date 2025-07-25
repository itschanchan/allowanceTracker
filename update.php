<?php
    session_start();
    include_once("connect.php");

    if (!isset($_SESSION['user_id'])) {
        echo "unauthorized";
        exit;
    }

    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $date = trim($_POST['date'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : -1;

        if ($id > 0 && !empty($date) && !empty($desc) && $amount >= 0) {
            $query = "UPDATE transactions SET date = ?, description = ?, amount = ? 
                    WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                echo "prepare_failed: " . $conn->error;
                exit;
            }

            $stmt->bind_param("ssdii", $date, $desc, $amount, $id, $user_id);

            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "invalid input";
        }
    } else {
        echo "invalid request";
    }
?>
