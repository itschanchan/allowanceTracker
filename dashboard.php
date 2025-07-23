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

    // Handle Set Allowance Form
    if (isset($_POST['set_allowance'])) {
        $amount = isset($_POST['amount']) ? $_POST['amount'] : '';

        if (is_numeric($amount) && $amount >= 0) {
            $check = mysqli_query($conn, "SELECT * FROM allowance WHERE user_id = $user_id");
            if (mysqli_num_rows($check) > 0) {
                $query = "UPDATE allowance SET amount = '$amount' WHERE user_id = $user_id";
            } else {
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

    // ✅ Handle Set Budget Form
    if (isset($_POST['set_budget'])) {
        $budget_input = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;

        if ($budget_input >= 0) {
            $check_budget = mysqli_query($conn, "SELECT * FROM budget WHERE user_id = $user_id");
            if (mysqli_num_rows($check_budget) > 0) {
                $query = "UPDATE budget SET amount = '$budget_input' WHERE user_id = $user_id";
            } else {
                $query = "INSERT INTO budget (user_id, amount) VALUES ('$user_id', '$budget_input')";
            }

            if (mysqli_query($conn, $query)) {
                $success = "Budget set successfully!";
            } else {
                $error = "Failed to set budget.";
            }
        } else {
            $error = "Invalid budget amount.";
        }
    }

    // Handle Add Transaction Form
    if (isset($_POST['add_transaction'])) {
        $date = $_POST['date'];
        $description = $_POST['description'];
        $amount = $_POST['trans_amount'];

        if ($date && $description && is_numeric($amount) && $amount >= 0) {
            $query = "INSERT INTO transactions (user_id, date, description, amount) VALUES ('$user_id', '$date', '$description', '$amount')";
            if (mysqli_query($conn, $query)) {
                $success = "Transaction added successfully!";
            } else {
                $error = "Failed to add transaction: " . mysqli_error($conn);
            }
        } else {
            $error = "All fields are required and amount must be valid.";
        }
    }

    // Total spent
    $result = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $total_spent = $row['total'] ?? 0;

    // Allowance
    $result = mysqli_query($conn, "SELECT amount FROM allowance WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $allowance = $row['amount'] ?? 0;

    $remaining = $allowance - $total_spent;

    // 🔁 Budget fetch
    $result = mysqli_query($conn, "SELECT amount FROM budget WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $budget = $row['amount'] ?? 0;

    // ✅ Show warning if remaining is below budget
    $over_budget = ($remaining < $budget) && ($budget > 0);
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
    <div class="pageWrapper">
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
                    
                    <!-- LEFT: Dashboard + Budget -->
                    <div class="leftWrapper">
                        <!-- Dashboard Box -->
                        <div class="box dashboardBox">
                            <h1>Dashboard</h1> <br>
                            <div class="stats">
                                <p><strong>Fixed Allowance:</strong> ₱<?php echo number_format($allowance, 2); ?></p>
                                <p><strong>Total Spent:</strong> ₱<?php echo number_format($total_spent, 2); ?></p>
                                <p><strong>Remaining Balance:</strong> ₱<?php echo number_format($remaining, 2); ?></p>
                                
                                <!-- ✅ Budget Limit -->
                                <p><strong>Budget Limit:</strong> ₱<?php echo number_format($budget, 2); ?></p>

                                <!-- ✅ Show warning if remaining balance is below budget -->
                                <?php if ($remaining < $budget && $budget > 0): ?>
                                    <p style="color: orange; font-weight: bold;">⚠ Warning: Your remaining balance is below your budget limit!</p>
                                <?php endif; ?>
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
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No transactions found.</td></tr>";
                                }
                                ?>
                            </table>
                        </div>


                        <!-- Budget Box -->
                        <div class="box budgetBox">
                            <h2>Budget / Spending Limit</h2>
                            <div class="stats">
                                <p><strong>Budget Limit:</strong> ₱<?php echo number_format($budget, 2); ?></p>
                                <p><strong>Total Spent:</strong> ₱<?php echo number_format($total_spent, 2); ?></p>
                                <p>
                                    <strong>Status:</strong> 
                                    <?php if ($budget == 0): ?>
                                        No budget set.
                                    <?php elseif ($over_budget): ?>
                                        <span style="color: red;">Over Budget</span>
                                    <?php else: ?>
                                        <span style="color: green;">Within Budget</span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <form method="POST" class="form" style="margin-top: 20px;">
                                <div class="inputBx">
                                    <label for="budget">Set Budget Limit (₱):</label>
                                    <input 
                                        type="range" 
                                        name="budget" 
                                        id="budgetSlider" 
                                        min="0" 
                                        max="<?php echo $allowance; ?>" 
                                        step="1" 
                                        value="<?php echo $budget; ?>" 
                                        oninput="document.getElementById('budgetDisplay').innerText = this.value">

                                    <p>Selected Budget: ₱<span id="budgetDisplay"><?php echo $budget; ?></span></p>
                                    <input type="hidden" name="set_budget" value="1">
                                </div>
                                <div class="inputBx">
                                    <input type="submit" value="Set Budget">
                                </div>
                            </form>
                        </div>

                    </div>

                    <!-- RIGHT: Tabs -->
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
                                    <ion-icon name="document-outline"></ion-icon>
                                    <input type="text" name="description" placeholder="What did you spend on?" required>
                                </div>
                                <div class="inputBx">
                                    <label for="trans_amount">Amount (₱):</label>
                                    <ion-icon name="cash-outline"></ion-icon>
                                    <input type="number" name="trans_amount" step="0.01" min="0" required>
                                    <input type="hidden" name="add_transaction" value="1">
                                </div>
                                <div class="inputBx">
                                    <input type="submit" value="Add">
                                </div>
                            </form>
                        </div>

                        <?php
                        if (!empty($error)) {
                            echo "<p class='error'>$error</p>";
                        } elseif (!empty($success)) {
                            echo "<p class='success'>$success</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
