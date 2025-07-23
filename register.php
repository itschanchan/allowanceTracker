<?php
    session_start();
    include_once("connect.php"); 

    $error = "";
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Basic validation
        if ($name === '' || $email === '' || $password === '') {
            $error = "All fields are required.";
        } 
        else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Email is already registered.";
            } 
            else {
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $password);

                if ($stmt->execute()) {
                    $success = "Registration successful. <a href='login.php'>Log in here</a>.";
                } 
                else {
                    $error = "Registration failed: " . $stmt->error;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="design.css">
    <script src="javascript.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="title">
        <h1>STUDENT ALLOWANCE TRACKER</h1>
    </div>
    <div class="mainContainer">
        <div class="container">
            <form method="POST" action="register.php" id="registerForm" class="form">
                <h1>Register</h1>

                <?php
                    if (!empty($error)) echo "<p class='error'>$error</p>";
                    if (!empty($success)) echo "<p style='color:green;'>$success</p>";
                ?>

                <div class="inputBx">
                    <span>Full Name:</span>
                    <ion-icon name="person-outline"></ion-icon>
                    <input type="text" name="name" placeholder="Enter your name" required>
                </div>

                <div class="inputBx">
                    <span>Email:</span>
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="inputBx">
                    <span>Password:</span>
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" placeholder="Create a password" id="myInput" required>
  
                </div>

                <div class="inputBx">
                    <span>Re-Enter Passwrod: </span>
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" placeholder="Re-enter your password" id="myInput2" required>

                    <div class="showPass">
                        <br> <input type="checkbox" onclick="showPass()" id="showPassword">
                        <label for="showPassword">Show Password</label>
                    </div>
                </div>

                <div class="inputBx">
                    <input type="submit" value="Register">
                </div>

                <p class="registerText">Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>
    </div>
</body>
</html>

