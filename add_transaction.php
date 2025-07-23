<?php
    session_start();
    include_once("connect.php");

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $error = "";
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $date = $_POST['date'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];

        if ($date && $description && $amount) {
            $query = "INSERT INTO transactions (user_id, date, description, amount) VALUES ('$user_id', '$date', '$description', '$amount')";
            if (mysqli_query($conn, $query)) {
                $success = "Transaction added successfully!";
            } else {
                $error = "Failed to add transaction: " . mysqli_error($conn);
            }
        } else {
            $error = "All fields are required.";
        }
    }
?>

<!DOCTYPE html>
<html>
<head><title>Add Transaction</title></head>
<body>
    <h2>Add Transaction</h2>
    <?php
        if ($error) echo "<p style='color:red;'>$error</p>";
        if ($success) echo "<p style='color:green;'>$success</p>";
    ?>
    <form method="POST">
        Date: <input type="date" name="date" required><br>
        Description: <input type="text" name="description" required><br>
        Amount: <input type="number" step="0.01" name="amount" required><br>
        <input type="submit" value="Add Transaction">
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
