<?php
session_start();
include_once("connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get total expenses
$result = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions WHERE user_id = $user_id");
$row = mysqli_fetch_assoc($result);
$total_spent = $row['total'] ?? 0;

// Get fixed allowance
$result = mysqli_query($conn, "SELECT amount FROM allowance WHERE user_id = $user_id");
$row = mysqli_fetch_assoc($result);
$allowance = $row['amount'] ?? 0;

// Calculate remaining
$remaining = $allowance - $total_spent;
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
    <h2>Welcome to Your Dashboard</h2>
    <p><strong>Fixed Allowance:</strong> ₱<?php echo number_format($allowance, 2); ?></p>
    <p><strong>Total Spent:</strong> ₱<?php echo number_format($total_spent, 2); ?></p>
    <p><strong>Remaining Balance:</strong> ₱<?php echo number_format($remaining, 2); ?></p>

    <hr>
    <p><a href="set_allowance.php">Set/Update Monthly Allowance</a></p>
    <p><a href="add_transaction.php">Add a new transaction</a></p>
    <p><a href="logout.php">Logout</a></p>
    <hr>
    <h3>Transaction History</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount (₱)</th>
        </tr>
        <?php
        $transactions = mysqli_query($conn, "SELECT date, description, amount FROM transactions WHERE user_id = $user_id ORDER BY date DESC");
        if (mysqli_num_rows($transactions) > 0) {
            while ($row = mysqli_fetch_assoc($transactions)) {
                echo "<tr>
                        <td>{$row['date']}</td>
                        <td>{$row['description']}</td>
                        <td>" . number_format($row['amount'], 2) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No transactions found.</td></tr>";
        }
        ?>
    </table>

</body>
</html>
