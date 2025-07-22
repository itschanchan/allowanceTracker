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
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $stmt->close();

            // Insert user-plain-text password 
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $success = "Registration successful. <a href='login.php'>Log in here</a>.";
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
    <h2>Register</h2>
    <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
    ?>
    <form method="POST" action="register.php">
        Full Name: <input type="text" name="name" required /><br>
        Email: <input type="email" name="email" required /><br>
        Password: <input type="password" name="password" required /><br>
        <input type="submit" value="Register" />
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>
