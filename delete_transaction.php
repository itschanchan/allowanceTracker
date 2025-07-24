<?php
    session_start();
    include_once("connect.php");

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "DELETE FROM transactions WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: dashboard.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid request";
    }
?>