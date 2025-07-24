<?php 
    session_start();
    include_once("connect.php"); 

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $query = "SELECT id, password FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name']; 
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="design.css">
    <script src="javascript.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="pageWrapper">
        <div class="title">
            <h1>STUDENT ALLOWANCE TRACKER</h1>
        </div>
        
        <div class="mainContainer">
            <div class="container">
                <form method="POST" class="form">
                    <h1>Login</h1>

                    <div class="inputBx">
                        <span>Email:</span>
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="text" name="email" placeholder="Enter email" required>
                    </div>

                    <div class="inputBx">
                        <span>Password:</span>
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" id="myInput" placeholder="Enter password" required>
                        
                        <div class="showPass">
                            <br> 
                            <input type="checkbox" onclick="showPass()" id="showPassword">
                            <label for="showPassword">Show Password</label>
                        </div>
                    </div>

                    <div class="inputBx">
                        <input type="submit" value="Login">
                    </div>

                    <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <p class="registerText">Don't have an account? <a href="register.php">Register here</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>