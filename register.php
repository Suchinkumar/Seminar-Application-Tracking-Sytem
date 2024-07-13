<?php
$error_message = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $mobile_no = $_POST['mobile_no'];
    $cadre_id = $_POST['cadre_id'];
    $desig_id = $_POST['desig_id'];
    $internal_desig_id = $_POST['internal_desig_id'];
    $group_id = $_POST['group_id'];

    $conn = mysqli_connect("localhost", "root", "", "permission_request_system");
    if (!$conn) {
        die("Connection failed: ". mysqli_connect_error());
    }

    $stmt = $conn->prepare("INSERT INTO emp (username, password, email_id, first_name, middle_name, last_name, gender, dob, mobile_no, cadre_id, desig_id, internal_desig_id, group_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssisiisi", $username, $password, $email, $first_name, $middle_name, $last_name, $gender, $dob, $mobile_no, $cadre_id, $desig_id, $internal_desig_id, $group_id);

    if ($stmt->execute()) {
        echo "New account created successfully!";
        header('Location: login.php');
        exit;
    } else {
        $error_message=$stmt->error;
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
    <title>Register</title>
    <!-- Link to register page CSS -->
    <link rel="stylesheet" href="register_styles.css">
    <!-- Link to register page JavaScript -->
    <!-- <script src="register_script.js" defer></script> -->
</head>
<body>
    <style>
        .header {
            background-color: rgb(0, 15, 56);
            color: rgb(243, 248, 255);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        h1 {
            text-align: center;
        }
        .header img {
            height: 50px;
        }
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            text-align: center;
            font-size: 14px;
            padding: 8px;
            border-radius: 6px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
    <div class="header">
        <img src="drdo_logo_0.png" alt="DRDO Logo">
        <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
        <img src="drdo_logo_0.png" alt="DRDO Logo">
    </div>
    <div class="container">
        <h2>Register</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email_id" required>
            </div>
            <div class="form-group">
                <label for="mobile_no">Mobile No:</label>
                <input type="tel" id="mobile_no" name="mobile_no" required>
            </div>
            <div class="form-group">
                <label for="cadre_id">Cadre ID:</label>
                <input type="number" id="cadre_id" name="cadre_id" required>
            </div>
            <div class="form-group">
                <label for="desig_id">Designation ID:</label>
                <input type="number" id="desig_id" name="desig_id" required>
            </div>
            <div class="form-group">
                <label for="internal_desig_id">Internal Designation ID:</label>
                <input type="number" id="internal_desig_id" name="internal_desig_id" required>
            </div>
            <div class="form-group">
                <label for="group_id">Group ID:</label>
                <input type="number" id="group_id" name="group_id" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="submit" class="btn" name="register" value="Register">
        </form>
        <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
