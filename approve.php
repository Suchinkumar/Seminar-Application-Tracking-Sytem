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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = $_POST['remarks'];
    
    if ($dynamic_role === 'group_head') {
        $sql = "UPDATE applications SET group_head_status = 1, group_head_remarks = ?, group_head_time = NOW() WHERE id = ?";
    } elseif ($dynamic_role === 'ad') {
        $sql = "UPDATE applications SET ad_status = 1, ad_remarks = ?, ad_time = NOW() WHERE id = ?";
    } elseif ($dynamic_role === 'tcp_hr_head') {
        $sql = "UPDATE applications SET tcp_hr_head_status = 1, tcp_hr_head_remarks = ?, tcp_hr_head_time = NOW() WHERE id = ?";
    } elseif ($dynamic_role === 'tcp_hr_ad') {
        $sql = "UPDATE applications SET tcp_hr_ad_status = 1, tcp_hr_ad_remarks = ?, tcp_hr_ad_time = NOW() WHERE id = ?";
    } elseif ($dynamic_role === 'director') {
        $sql = "UPDATE applications SET director_status = 1, director_remarks = ?, director_time = NOW() WHERE id = ?";
    } else {
        die("Unauthorized access.");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $remarks, $application_id);

    if ($stmt->execute()) {
        if ($dynamic_role === 'director') {
            // Insert the application data into the training_details table
            $insert_sql = "
                INSERT INTO training_details (username, name_of_seminar, to_date, from_date, duration, type_of_event, location, place)
                SELECT name_of_applicant, name_of_seminar, to_date, from_date, duration, type_of_event, location, place
                FROM applications
                WHERE id = ?
            ";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("i", $application_id);

            if ($insert_stmt->execute()) {
                $message = "Application approved and details added to training records successfully.";
            } else {
                $message = "Error adding details to training records: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        } else {
            $message = "Application approved successfully.";
        }
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $message = "Please submit the form.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        p {
            font-size: 18px;
            color: #666;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        textarea {
            width: 100%;
            height: 100px;
            /* padding: 10px; */
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
            background-color: #f7f7f7;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .color {
            color: #ff008f;
        }
    </style>
</head>
<body>
    <?php include 'home_button.php'; ?>
    <div class="container">
        <h1 class="color">Approve Application</h1>
        <p><?php echo $message; ?></p>
        <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
        <form method="POST" action="">
            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks" required></textarea>
            <button type="submit">Submit</button>
        </form>
        <?php } ?>
    </div>
</body>
</html>
