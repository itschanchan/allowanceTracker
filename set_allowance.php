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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Allowance</title>
    <link rel="stylesheet" href="design.css">
    <script src="javascript.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="mainContainer">
        <div class="title">
            <h1>STUDENT ALLOWANCE TRACKER</h1>
        </div>

        <div class="container">
            <form method="POST" class="form">
                <h1>Set / Update Allowance</h1>

                <div class="inputBx">
                    <span>Allowance Amount (₱):</span>
                    <ion-icon name="wallet-outline"></ion-icon>
                    <input type="number" name="amount" step="0.01" min="0" placeholder="Enter amount" required>
                </div>

                <div class="inputBx">
                    <input type="submit" value="Save">
                </div>

                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>

                <p class="registerText"><a href="dashboard.php">← Back to Dashboard</a></p>
            </form>
        </div>
    </div>
</body>
</html>
