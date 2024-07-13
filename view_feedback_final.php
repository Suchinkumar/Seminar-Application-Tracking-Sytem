<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "permission_request_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee details for the current session user
$sql_emp = "SELECT * FROM emp WHERE username=?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("s", $username);
$stmt_emp->execute();
$result_emp = $stmt_emp->get_result();
$emp_data = $result_emp->fetch_assoc();
$current_session_user_id = $emp_data['id'];
$current_session_group_id = $emp_data['group_id'];
$current_session_internal_desig = $emp_data['internal_desig_id'];
$stmt_emp->close();

// Determine the role of the current session user
$sql_group_info_2 = "SELECT group_head_id, ad_id FROM group_info WHERE group_id=2";
$stmt_group_info_2 = $conn->prepare($sql_group_info_2);
$stmt_group_info_2->execute();
$result_group_info_2 = $stmt_group_info_2->get_result();
$group_info_2 = $result_group_info_2->fetch_assoc();
$stmt_group_info_2->close();

$role = '';
if ($current_session_user_id == $group_info_2['group_head_id']) {
    $role = 'tcp_hr_head';
} elseif ($current_session_user_id == $group_info_2['ad_id']) {
    $role = 'tcp_hr_ad';
} elseif ($current_session_internal_desig == 1) {
    $role = 'director';
} else {
    $role = 'employee';
}

// Fetch feedback
$sql_feedback = "SELECT f.*, e.username AS feedback_giver_username 
                 FROM feedback f
                 JOIN emp e ON f.submitted_by = e.username
                 ORDER BY f.id DESC";
$result_feedback = $conn->query($sql_feedback);
if (!$result_feedback) {
    die("Error fetching feedback: " . $conn->error);
}

$feedback_list = [];
while ($row = $result_feedback->fetch_assoc()) {
    $feedback_giver_group_id = $row['user_group'];
    $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id=?";
    $stmt_group_info = $conn->prepare($sql_group_info);
    $stmt_group_info->bind_param("i", $feedback_giver_group_id);
    $stmt_group_info->execute();
    $result_group_info = $stmt_group_info->get_result();
    $group_info = $result_group_info->fetch_assoc();
    $stmt_group_info->close();

    $ad_status = $row['ad_status'] ?? 0;
    $tcp_hr_ad_status = $row['tcp_hr_ad_status'] ?? 0;
    $tcp_hr_head_status = $row['tcp_hr_head_status'] ?? 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
        $feedback_id = $_POST['feedback_id'];

        // Check if AD remarks are set
        if (isset($_POST['ad_remarks']) && $role == 'ad') {
            $ad_remarks = $_POST['ad_remarks'];
            $sql = "UPDATE feedback SET ad_remarks = ?, ad_status = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $ad_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }

        // Check if TCP HR AD remarks are set
        if (isset($_POST['tcp_hr_ad_remarks']) && $role == 'tcp_hr_ad') {
            $tcp_hr_ad_remarks = $_POST['tcp_hr_ad_remarks'];
            $sql = "UPDATE feedback SET tcp_hr_ad_remarks = ?, tcp_hr_ad_status = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $tcp_hr_ad_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }

        // Check if TCP HR Head remarks are set
        if (isset($_POST['tcp_hr_head_remarks']) && $role == 'tcp_hr_head') {
            $tcp_hr_head_remarks = $_POST['tcp_hr_head_remarks'];
            $sql = "UPDATE feedback SET tcp_hr_head_remarks = ?, tcp_hr_head_status = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $tcp_hr_head_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect back to the feedback page
        header('Location: view_feedback.php');
        exit;
    }

    // Determine dynamic role for display purposes
    $row['dynamic_role'] = $role;
    if ($current_session_user_id == $group_info['group_head_id']) {
        $row['dynamic_role'] = 'group_head';
    } elseif ($current_session_user_id == $group_info['ad_id']) {
        $row['dynamic_role'] = 'ad';
    }

    $feedback_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <link rel="stylesheet" href="view_feedback_style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #004080;
            color: white;
        }
        .header img {
            height: 60px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .container {
            padding: 20px;
        }
        h2, h3 {
            text-align: center;
            color: #004080;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .feedback-table th, .feedback-table td {
            text-align: center;
        }
        .hidden {
            display: none;
        }
        .button {
            padding: 8px 12px;
            background-color: #004080;
            color: white;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #003060;
        }
        .print-button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="drdo_logo_0.png" alt="DRDO Logo">
        <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
        <img src="drdo_logo_0.png" alt="DRDO Logo">
    </div>
    
    <div class="container">
    <?php include 'home_button.php'; ?>
    <h2>Feedback Forms</h2>
    
    <h3>Pending Forms</h3>
    <?php
    $has_pending_feedback = false;
    echo "<table><tr><th>Submitted By</th><th>Name</th><th>Actions</th></tr>";
    foreach ($feedback_list as $row) {
        $id = $row['id'];
        $dynamic_role = $row['dynamic_role'];
        $show_row = false;

        if (($dynamic_role == 'ad' && $row['ad_status'] == 0) || 
            ($dynamic_role == 'tcp_hr_ad' && $row['ad_status'] == 1 && $row['tcp_hr_ad_status'] == 0) ||
            ($dynamic_role == 'tcp_hr_head' && $row['ad_status'] == 1 && $row['tcp_hr_head_status'] == 0)) {
            $show_row = true;
            $has_pending_feedback = true;
        }

        if ($show_row) {
            echo "<tr>
                    <td>{$row['feedback_giver_username']}</td>
                    <td>{$row['name']}</td>
                    <td>
                        <button class='button' onclick=\"document.getElementById('feedback-{$id}').classList.toggle('hidden');\">Review</button>
                        <button class='button' onclick=\"location.href='admin_view_feedback.php?id={$id}'\">View Feedback</button>
                        <form method='post' action='view_feedback.php'>
                            <div id='feedback-{$id}' class='hidden'>
                                <input type='hidden' name='feedback_id' value='{$id}'>";
            if ($dynamic_role == 'ad') {
                echo "<textarea name='ad_remarks' placeholder='AD Remarks'></textarea>";
            } elseif ($dynamic_role == 'tcp_hr_ad') {
                echo "<textarea name='tcp_hr_ad_remarks' placeholder='TCP HR AD Remarks'></textarea>";
            } elseif ($dynamic_role == 'tcp_hr_head') {
                echo "<textarea name='tcp_hr_head_remarks' placeholder='TCP HR Head Remarks'></textarea>";
            }
            echo "<button type='submit' class='button'>Submit</button>
                            </div>
                        </form>
                    </td>
                  </tr>";
        }
    }
    if (!$has_pending_feedback) {
        echo "<tr><td colspan='3'>No pending feedback forms.</td></tr>";
    }
    echo "</table>";
    ?>

    <h3>Past Forms</h3>
    <?php
    $has_past_feedback = false;
    echo "<table><tr><th>Submitted By</th><th>Name</th><th>Actions</th></tr>";
    foreach ($feedback_list as $row) {
        $id = $row['id'];
        $dynamic_role = $row['dynamic_role'];
        $show_row = false;

        if (($dynamic_role == 'ad' && $row['ad_status'] == 1) || 
            ($dynamic_role == 'tcp_hr_ad' && $row['tcp_hr_ad_status'] == 1) ||
            ($dynamic_role == 'tcp_hr_head' && $row['tcp_hr_head_status'] == 1)) {
            $show_row = true;
            $has_past_feedback = true;
        }

        if ($show_row) {
            echo "<tr>
                    <td>{$row['feedback_giver_username']}</td>
                    <td>{$row['name']}</td>
                    <td>
                        <button class='button' onclick=\"document.getElementById('feedback-{$id}').classList.toggle('hidden');\">View</button>
                         <button class='button' onclick=\"location.href='admin_view_feedback.php?id={$id}'\">View Feedback</button>
                        <div id='feedback-{$id}' class='hidden'>
                            <p><strong>AD Remarks:</strong> {$row['ad_remarks']}</p>
                            <p><strong>TCP HR AD Remarks:</strong> {$row['tcp_hr_ad_remarks']}</p>
                            <p><strong>TCP HR Head Remarks:</strong> {$row['tcp_hr_head_remarks']}</p>
                            <button class='button print-button' onclick='window.print()'>Print</button>
                        </div>
                    </td>
                  </tr>";
        }
    }
    if (!$has_past_feedback) {
        echo "<tr><td colspan='3'>No past feedback forms.</td></tr>";
    }
    echo "</table>";
    ?>
    </div>
</body>
</html>
