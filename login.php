<?php
session_start();

$error_message = '';

if (isset($_POST['login'])) 
{
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = mysqli_connect("localhost", "root", "", "permission_request_system");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT * FROM emp WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) { // Plain text comparison
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>










<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login_styles.css">
    <title>Login</title>
   
</head>

<body>
    <div class="header">
        <img src="drdo_logo_0.png" alt="DRDO Logo">
        <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
        <img src="drdo_logo_0.png" alt="DRDO Logo">
    </div>
    <div class="main-content">
        <div class="image-container">
            <h1>HRD DIVISION</h1>
            <img src="public_blue_users.png" alt="Image">
        </div>
        <div class="login-container">
            <div class="login-box">
                <h2>Login</h2>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <input type="submit" class="btn" name="login" value="Login">
                </form>
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <!-- <div class="register-link">
                    Don't have an account? <a href="register.php">Register here</a>
                </div> -->
            </div>
        </div>
    </div>
    <footer>
        <p>Designed and maintained by QRS&IT group</p>
    </footer>
</body>

</html>
