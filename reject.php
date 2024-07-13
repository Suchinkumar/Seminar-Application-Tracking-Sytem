<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "permission_request_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$application_id = $_GET['id'];
$dynamic_role = $_GET['dynamic_role'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $remarks = $_POST['remarks'] ?? '';

    // Determine which field to update based on the dynamic_role
    switch ($dynamic_role) {
        case 'group_head':
            $sql = "UPDATE applications SET group_head_status = -1, remarks = ?, group_head_time = NOW() WHERE id = ?";
            break;
        case 'ad':
            $sql = "UPDATE applications SET ad_status = -1, remarks = ?, ad_time = NOW() WHERE id = ?";
            break;
        case 'tcp_hr_head':
            $sql = "UPDATE applications SET tcp_hr_head_status = -1, remarks = ?, tcp_hr_head_time = NOW() WHERE id = ?";
            break;
        case 'tcp_hr_ad':
            $sql = "UPDATE applications SET tcp_hr_ad_status = -1, remarks = ?, tcp_hr_ad_time = NOW() WHERE id = ?";
            break;
        case 'director':
            $sql = "UPDATE applications SET director_status = -1, remarks = ?, director_time = NOW() WHERE id = ?";
            break;
        default:
            die("Unauthorized access.");
    }
    

    // Debugging: Print the SQL query and parameters
    error_log("SQL Query: $sql");
    error_log("Remarks: $remarks, Application ID: $application_id");

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $remarks, $application_id);

    if ($stmt->execute()) {
        $message = "Application rejected successfully!";
    } else {
        $message = "Error: " . $stmt->error;
        // Debugging: Log the error
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Application</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 16px;
            margin: 10px 0 5px;
            color: #333;
        }
        textarea {
            font-family: 'Arial', sans-serif;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        p {
            margin-top: 20px;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .message {
            font-size: 18px;
            color: green;
        }
    </style>
</head>
<body>
    <?php include 'home_button.php'; ?>
    <div class="container">
        <h1>Reject Application</h1>
        <?php if (!empty($message)) { echo '<p class="message">' . $message . '</p>'; } ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $application_id . '&dynamic_role=' . $dynamic_role; ?>" method="post">
            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks" rows="4" cols="50"></textarea>
            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
