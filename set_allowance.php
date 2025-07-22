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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = $_POST['amount'];

    if (is_numeric($amount) && $amount >= 0) {
        // Check if user already has allowance
        $check = mysqli_query($conn, "SELECT * FROM allowance WHERE user_id = $user_id");
        if (mysqli_num_rows($check) > 0) {
            // Update
            $query = "UPDATE allowance SET amount = '$amount' WHERE user_id = $user_id";
        } else {
            // Insert
            $query = "INSERT INTO allowance (user_id, amount) VALUES ('$user_id', '$amount')";
        }

        if (mysqli_query($conn, $query)) {
            $success = "Allowance set successfully!";
        } else {
            $error = "Failed to set allowance.";
        }
    } else {
        $error = "Please enter a valid amount.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Set Allowance</title></head>
<body>
    <h2>Set / Update Allowance</h2>
    <?php
        if ($error) echo "<p style='color:red;'>$error</p>";
        if ($success) echo "<p style='color:green;'>$success</p>";
    ?>
    <form method="POST">
        Allowance Amount (₱): <input type="number" name="amount" step="0.01" min="0" required><br><br>
        <input type="submit" value="Save">
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
