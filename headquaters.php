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
    $remarks = $_POST['headquaters_remarks'] ?? '';

    // Set current date and time
    $headquarters_time = date('Y-m-d H:i:s');

    // Determine which field to update based on the dynamic_role
    switch ($dynamic_role) {
        case 'director':
            $sql = "UPDATE applications SET director_status = 0, headquaters_remarks = ?, headquaters_time = ? WHERE id = ?";
            break;
        default:
            die("Unauthorized access.");
    }

    // Debugging: Print the SQL query and parameters
    error_log("SQL Query: $sql");
    error_log("Remarks: $remarks, Headquarters Time: $headquarters_time, Application ID: $application_id");

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssi", $remarks, $headquarters_time, $application_id);

    if ($stmt->execute()) {
        $message = "Remarks uploaded successfully!";
    } else {
        $message = "Error: " . $stmt->error;
        // Debugging: Log the error
        error_log("Execute failed: " . $stmt->error);
    }

    $stmt->close();
} else {
    // Fetch existing remarks from the database
    $sql = "SELECT headquaters_remarks FROM applications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->bind_result($existing_remarks);
    $stmt->fetch();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Remarks to Applicant</title>
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
        <h1>Send Remarks to Applicant</h1>
        <?php if (!empty($message)) { echo '<p class="message">' . $message . '</p>'; } ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $application_id . '&dynamic_role=' . $dynamic_role; ?>" method="post">
            <label for="headquaters_remarks">Remarks:</label>
            <textarea id="headquaters_remarks" name="headquaters_remarks" rows="4" cols="50"><?php echo htmlspecialchars($existing_remarks ?? ''); ?></textarea>
            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
