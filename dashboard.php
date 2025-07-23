<?php
    session_start();
    include_once("connect.php");

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['name'] ?? 'User';
    $error = "";
    $success = "";

    $result = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $total_spent = $row['total'] ?? 0;

    $result = mysqli_query($conn, "SELECT amount FROM allowance WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $allowance = $row['amount'] ?? 0;

    $remaining = $allowance - $total_spent;

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $amount = isset($_POST['amount']) ? $_POST['amount'] : '';

        if (is_numeric($amount) && $amount >= 0) {
            // Check if user already has allowance
            $check = mysqli_query($conn, "SELECT * FROM allowance WHERE user_id = $user_id");
            if (mysqli_num_rows($check) > 0) {
                $query = "UPDATE allowance SET amount = '$amount' WHERE user_id = $user_id";
            } 
            else {
                $query = "INSERT INTO allowance (user_id, amount) VALUES ('$user_id', '$amount')";
            }

            if (mysqli_query($conn, $query)) {
                $success = "Allowance set successfully!";
            } 
            else {
                $error = "Failed to set allowance.";
            }
        } 
        else {
            $error = "Please enter a valid amount.";
        }
    }

    // Handle add transaction
    if (isset($_POST['add_transaction'])) {
        $date = $_POST['date'];
        $description = $_POST['description'];
        $amount = $_POST['trans_amount'];

        if ($date && $description && $amount) {
            $query = "INSERT INTO transactions (user_id, date, description, amount) VALUES ('$user_id', '$date', '$description', '$amount')";
            if (mysqli_query($conn, $query)) {
                $success = "Transaction added successfully!";
            } 
            else {
                $error = "Failed to add transaction: " . mysqli_error($conn);
            }
        } 
        else {
            $error = "All fields are required.";
        }
    }

    // Get totals
    $result = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $total_spent = $row['total'];

    $result = mysqli_query($conn, "SELECT amount FROM allowance WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $allowance = $row['amount'];

    $remaining = $allowance - $total_spent;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="design_dashboard.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="javascript.js"></script>
</head>
<body>
    <div class="mainContainer">
        <nav class="navbar">
            <div class="logo">Student Allowance Tracker</div>
            
            <div class="nav-right">
                <span class="welcome-msg">Welcome, <?php echo htmlspecialchars($name); ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </nav>

        <div class="container">
        <div class="sideBySideWrapper">
            
            <!-- Left Side: Dashboard Info -->
            <div class="box dashboardBox">
                <h1>Dashboard</h1> <br>

                <div class="stats">
                    <p><strong>Fixed Allowance:</strong> ₱<?php echo number_format($allowance, 2); ?></p>
                    <p><strong>Total Spent:</strong> ₱<?php echo number_format($total_spent, 2); ?></p>
                    <p><strong>Remaining Balance:</strong> ₱<?php echo number_format($remaining, 2); ?></p>
                </div>

                <h3 style="margin-top: 30px;">Transaction History</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount (₱)</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $transactions = mysqli_query($conn, "SELECT id, date, description, amount FROM transactions WHERE user_id = $user_id ORDER BY date DESC");
                    if (mysqli_num_rows($transactions) > 0) {
                        while ($row = mysqli_fetch_assoc($transactions)) {
                            $id = $row['id'];
                            $date = $row['date'];
                            $desc = $row['description'];
                            $amount = number_format($row['amount'], 2);

                            $editBtn = "<button class='action-btn edit' onclick='editRow({$id})'>Edit</button>";
                            $saveBtn = "<button class='action-btn save' onclick='saveRow({$id})' style='display:none;'>Save</button>";
                            $deleteBtn = "
                                <a href='delete_transaction.php?id={$id}' 
                                class='action-btn delete' 
                                onclick=\"return confirm('Are you sure you want to delete this transaction?');\">
                                    Delete
                                </a>";

                            echo "
                            <tr id='row-{$id}'>
                                <td class='date'>{$date}</td>
                                <td class='desc'>{$desc}</td>
                                <td class='amount'>{$amount}</td>
                                <td class='action'>
                                    {$editBtn}
                                    {$saveBtn}
                                    {$deleteBtn}
                                </td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No transactions found.</td></tr>";
                    }
                    ?>
                </table>
            </div>

            <!-- Right Side: Tabs & Forms -->
            <div class="tabWrapper box">
                <!-- Tabs -->
                <div class="tabsContainer">
                    <ul aria-labelledby="tabs-title">
                        <li><a id="tab-1" href="#setAllowanceContent" class="active">Set Allowance</a></li>
                        <li><a id="tab-2" href="#addTransactionContent">Add Transaction</a></li>
                    </ul>
                </div>

                <!-- Set Allowance Tab Content -->
                <div class="tab-content active" id="setAllowanceContent">
                    <form method="POST" class="form">
                        <h2>Set / Update Allowance</h2>
                        <div class="inputBx">
                            <ion-icon name="wallet-outline"></ion-icon>
                            <label for="amount">Allowance Amount (₱):</label>
                            <input type="number" name="amount" step="0.01" min="0" placeholder="Enter amount" required>
                            <input type="hidden" name="set_allowance" value="1">
                        </div>
                        <div class="inputBx">
                            <input type="submit" value="Set">
                        </div>
                    </form>
                </div>

                <!-- Add Transaction Tab Content -->
                <div class="tab-content" id="addTransactionContent">
                    <form method="POST" class="form">
                        <h2>Add Transaction</h2>
                        <div class="inputBx">
                            <label for="date">Date:</label>
                            <input type="date" name="date" required>
                        </div>
                        <div class="inputBx">
                            <label for="description">Description:</label>
                            <input type="text" name="description" placeholder="What did you spend on?" required>
                        </div>
                        <div class="inputBx">
                            <label for="trans_amount">Amount (₱):</label>
                            <input type="number" name="trans_amount" step="0.01" min="0" required>
                            <input type="hidden" name="add_transaction" value="1">
                        </div>
                        <div class="inputBx">
                            <input type="submit" value="Add">
                        </div>
                    </form>
                </div>

                <!-- Messages -->
                <?php
                    if (!empty($error)) echo "<p class='error'>$error</p>";
                    if (!empty($success)) echo "<p class='success'>$success</p>";
                ?>
            </div>

        </div>
    </div>
</body>
</html>