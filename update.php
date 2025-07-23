<?php
    include_once("connect.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $date = $_POST['date'];
        $desc = $_POST['description'];
        $amount = $_POST['amount'];

        if (is_numeric($amount) && !empty($desc) && !empty($date)) {
            $query = "UPDATE transactions SET date = ?, description = ?, amount = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssdi", $date, $desc, $amount, $id);
            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "error";
            }
        } else {
            echo "invalid";
        }
    }
?>
