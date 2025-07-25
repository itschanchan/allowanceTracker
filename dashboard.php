<?php
    session_start();
    include_once("connect.php");

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

    // Handle Set Budget Form
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
                $_SESSION['success'] = "Transaction added successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to add transaction.";
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "All fields are required and amount must be valid.";
        }
    }

    // Total spent
    $result = mysqli_query($conn, "SELECT SUM(amount) AS total FROM transactions WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $total_spent = $row['total'];

    // Allowance
    $result = mysqli_query($conn, "SELECT amount FROM allowance WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $allowance = $row['amount'] ?? 0;

    $remaining = $allowance - $total_spent;

    // Budget fetch
    $result = mysqli_query($conn, "SELECT amount FROM budget WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($result);
    $budget = $row['amount'] ?? 0;

    // Show warning if remaining is below budget
    $over_budget = ($remaining < $budget) && ($budget > 0);

    // Handle Clean Everything
    if (isset($_POST['clean_everything'])) {
        mysqli_query($conn, "DELETE FROM transactions WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM allowance WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM budget WHERE user_id = $user_id");

        $success = "All data has been cleared.";
    }
    
?>

<?php
    $categoryData = [];
    $query = "SELECT category, SUM(amount) as total FROM transactions WHERE user_id = $user_id GROUP BY category ORDER BY total DESC";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $cat = (!empty($row['category'])) ? $row['category'] : 'Miscellaneous / Other';
        $total = $row['total'];
        $categoryData[$cat] = $total;
    }

    $categoryLabels = json_encode(array_keys($categoryData));
    $categoryValues = json_encode(array_values($categoryData));
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="javascript.js"></script>
</head>
<body>
    <div class="pageWrapper">
        <!-- Title Section -->
        <div class="mainContainer">
            <!-- Navigation Bar -->
            <nav class="navbar">
                <div class="logo">Student Allowance Tracker</div>
                <div class="nav-right">
                    <span class="welcome-msg">Welcome, <?php echo htmlspecialchars($name); ?>!</span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </nav>

            <div class="container">
                <!-- Grid Area for Draggable Boxes -->
                <div class="gridDraggableArea" id="gridDraggable">

                    <!-- Dashboard Box -->
                    <div class="box dashboardBox">
                        <div class="drag-handle">
                            <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                        </div>
                        <h1>Dashboard</h1> <br>
                        <div class="stats">
                            <p><strong>Fixed Allowance:</strong> ₱<?php echo number_format($allowance, 2); ?></p>
                            <p><strong>Total Spent:</strong> ₱<?php echo number_format($total_spent, 2); ?></p>
                            <p><strong>Remaining Balance:</strong> ₱<?php echo number_format($remaining, 2); ?></p>
                            <p><strong>Budget Limit:</strong> ₱<?php echo number_format($budget, 2); ?></p>

                            <?php if ($remaining < $budget && $budget > 0): ?>
                                <p style="color: orange; font-weight: bold;">⚠ Warning: Your remaining balance is below your budget limit!</p>
                            <?php endif; ?>
                        </div>

                        <h3 style="margin-top: 30px;">Transaction History</h3>
                        <div class="table-wrapper">
                            <table>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount (₱)</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                                <?php
                                $transactions = mysqli_query($conn, "SELECT id, date, description, amount, category FROM transactions WHERE user_id = $user_id ORDER BY date DESC");

                                if (mysqli_num_rows($transactions) > 0) {
                                    $categoryOptions = [
                                        "Food & Dining",
                                        "Transportation",
                                        "Education & School Supplies",
                                        "Personal Care & Wellness",
                                        "Entertainment & Recreation",
                                        "Medical & Health",
                                        "Utilities & Bills",
                                        "Miscellaneous / Other"
                                    ];

                                    while ($row = mysqli_fetch_assoc($transactions)) {
                                        $id = $row['id'];
                                        $date = $row['date'];
                                        $desc = $row['description'];
                                        $amount = number_format($row['amount'], 2);
                                        $category = $row['category'] ?? 'Miscellaneous / Other';

                                        $editBtn = "<button class='action-btn edit' onclick='editRow({$id})'>Edit</button>";
                                        $saveBtn = "<button class='action-btn save' onclick='saveRow({$id})' style='display:none;'>Save</button>";
                                        $deleteBtn = "<a href='delete_transaction.php?id={$id}' class='action-btn delete' onclick=\"return confirm('Are you sure you want to delete this transaction?');\">Delete</a>";

                                        echo "<tr id='row-{$id}'>
                                            <td class='date'>{$date}</td>
                                            <td class='desc'>{$desc}</td>
                                            <td class='amount'>{$amount}</td>
                                            <td class='category'>
                                                <select class='category-select' id='category-{$id}'>";
                                                    foreach ($categoryOptions as $option) {
                                                        $selected = ($category === $option) ? "selected" : "";
                                                        echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
                                                    }
                                        echo "  </select>
                                            </td>
                                            <td class='action'>
                                                {$editBtn}
                                                {$saveBtn}
                                                {$deleteBtn}
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No transactions found.</td></tr>";
                                }
                                ?>
                            </table>
                        </div>
                        
                    </div>

                    <!-- Pie Chart Box -->
                    <div class="box chartBox">
                        <div class="drag-handle">
                            <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                        </div>
                        <h2>Spending Distribution</h2>
                        <canvas id="spendingPieChart">

                        </canvas>
                    </div>

                    <!-- Budget Box -->
                    <div class="box budgetBox">
                        <div class="drag-handle">
                            <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                        </div>
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
                                <label for="budgetSlider">Set Budget Limit (₱):</label>
                                <input
                                    type="range"
                                    id="budgetSlider"
                                    name="budget_slider"
                                    min="0"
                                    max="<?php echo $allowance; ?>"
                                    step="5"
                                    value="<?php echo $budget; ?>">
                                <p>Selected Budget:</p>
                                <div class="pesoInputWrapper">
                                    <span class="peso-symbol">₱</span>
                                    <input
                                        type="number"
                                        id="budgetInput"
                                        name="budget"
                                        min="0"
                                        max="<?php echo $allowance; ?>"
                                        step="5"
                                        value="<?php echo $budget; ?>">
                                </div>
                                <input type="hidden" name="set_budget" value="1">
                            </div>
                            <div class="inputBx">
                                <input type="submit" value="Set Budget">
                            </div>
                        </form>
                    </div>

                    <!-- Tabs Section -->
                    <div class="tabWrapper box">
                        <div class="drag-handle">
                            <ion-icon name="ellipsis-vertical-outline"></ion-icon>
                        </div>
                        <div class="tabsContainer">
                            <ul>
                                <li><a href="#setAllowanceContent" class="active">Set Allowance</a></li>
                                <li><a href="#addTransactionContent">Add Transaction</a></li>
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
                <!-- Clean Everything Button -->
                    <div class="box" style="text-align: center; margin-top: 20px;">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete all your data? This action cannot be undone.');">
                            <input type="hidden" name="clean_everything" value="1">
                            <input type="submit" value="🧹 Clean Everything! ✨" class="clean-everything-btn">
                        </form>
                    </div>
            </div>
        </div>
    </div>

<!-- Imported category labels from javascript.js file -->
<script>
window.categoryLabels = <?php echo $categoryLabels; ?>;
window.categoryValues = <?php echo $categoryValues; ?>;
</script>

</body>
</html>
