<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$conn = mysqli_connect("localhost", "root", "", "permission_request_system");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch employee details for the current session user
$sql_emp = "SELECT * FROM emp WHERE username='$username'";
$result_emp = mysqli_query($conn, $sql_emp);
if (!$result_emp) {
    die("Error fetching data: " . mysqli_error($conn));
}
$emp_data = mysqli_fetch_assoc($result_emp);
$current_session_user_id = $emp_data['id'];
$current_session_group_id = $emp_data['group_id'];
$current_session_internal_desig = $emp_data['internal_desig_id'];

// Determine the role of the current session user
$sql_group_info_2 = "SELECT group_head_id, ad_id FROM group_info WHERE group_id='$current_session_group_id'";
$result_group_info_2 = mysqli_query($conn, $sql_group_info_2);
if (!$result_group_info_2) {
    die("Error fetching group info: " . mysqli_error($conn));
}
$group_info_2 = mysqli_fetch_assoc($result_group_info_2);
$group_head_id_2 = $group_info_2['group_head_id'];
$ad_id_2 = $group_info_2['ad_id'];

$role = '';
if ($current_session_user_id == $group_head_id_2) {
    $role = 'tcp_hr_head';
} elseif ($current_session_user_id == $ad_id_2) {
    $role = 'tcp_hr_ad';
} elseif ($current_session_internal_desig == 1) {
    $role = 'ad';
} else {
    $role = 'employee'; // Default role if none of the conditions are met
}

// Fetch feedback
$sql_feedback = "SELECT f.*, e.username AS feedback_giver_username 
                 FROM feedback f
                 JOIN emp e ON f.submitted_by = e.username
                 ORDER BY f.id DESC";
$result_feedback = mysqli_query($conn, $sql_feedback);
if (!$result_feedback) {
    die("Error fetching feedback: " . mysqli_error($conn));
}

$feedback_list = [];
while ($row = mysqli_fetch_assoc($result_feedback)) {
    $feedback_giver_group_id = $row['user_group'];
    $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id='$feedback_giver_group_id'";
    $result_group_info = mysqli_query($conn, $sql_group_info);
    if (!$result_group_info) {
        die("Error fetching group info: " . mysqli_error($conn));
    }
    $group_info = mysqli_fetch_assoc($result_group_info);
    $group_head_id = $group_info['group_head_id'];
    $ad_id = $group_info['ad_id'];

    $ad_status = $row['ad_status'] ?? 0;

    if ($current_session_user_id == $ad_id && isset($_POST['ad_remarks'])) {
        $ad_status = 1;
        $sql_update_status = "UPDATE feedback 
                              SET ad_status = '$ad_status'
                              WHERE id = '{$row['id']}'";
        mysqli_query($conn, $sql_update_status);
    }
    $row['ad_status'] = $ad_status;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $feedback_id = $_POST['feedback_id'];
    
        // Check if AD remarks are set
        if (isset($_POST['ad_remarks'])) {
            $ad_remarks = $_POST['ad_remarks'];
            $sql = "UPDATE feedback SET ad_remarks = ?, ad_status = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $ad_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }
    
        // Check if TCP HR AD remarks are set
        if (isset($_POST['tcp_hr_ad_remarks'])) {
            $tcp_hr_ad_remarks = $_POST['tcp_hr_ad_remarks'];
            $sql = "UPDATE feedback SET tcp_hr_ad_remarks = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $tcp_hr_ad_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }
    
        // Check if TCP HR Head remarks are set
        if (isset($_POST['tcp_hr_head_remarks'])) {
            $tcp_hr_head_remarks = $_POST['tcp_hr_head_remarks'];
            $sql = "UPDATE feedback SET tcp_hr_head_remarks = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $tcp_hr_head_remarks, $feedback_id);
            $stmt->execute();
            $stmt->close();
        }
    
        // Redirect back to the feedback page
        header('Location: view_feedback.php');
        exit;
    }


    if ($current_session_user_id == $group_head_id) {
        $row['dynamic_role'] = 'group_head';
    } elseif ($current_session_user_id == $ad_id) {
        $row['dynamic_role'] = 'ad';
    } else {
        $row['dynamic_role'] = $role;
    }

    if ($current_session_user_id == $ad_id || ($row['ad_status'] == 1 && in_array($role, ['tcp_hr_ad', 'tcp_hr_head']))) {
        $feedback_list[] = $row;
    }
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
        <?php if (!empty($feedback_list)): ?>
            <!-- <h3>Forms</h3> -->
            <table>
                <thead>
                    <tr>
                        <th>Submitted By</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedback_list as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['submitted_by']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>
                                <button class="button" onclick="toggleDetails('<?php echo $row['id']; ?>')">View / Add remarks</button>
                                <button class="button print-button" onclick="printFeedback('<?php echo $row['id']; ?>')">Print</button>
                            </td>
                        </tr>
                        <tr id="details-<?php echo $row['id']; ?>" class="hidden">
                            <td colspan="3">
                                <div id="feedback-<?php echo $row['id']; ?>">
                                    <table>
                                        <tr>
                                            <th>Designation</th>
                                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Group</th>
                                            <td><?php echo htmlspecialchars($row['user_group']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Programme Type</th>
                                            <td><?php echo htmlspecialchars($row['programme_type']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Programme Title</th>
                                            <td><?php echo htmlspecialchars($row['programme_title']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Organising Institution</th>
                                            <td><?php echo htmlspecialchars($row['organising_institution']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>No. of Days</th>
                                            <td><?php echo htmlspecialchars($row['no_of_days']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>From Date</th>
                                            <td><?php echo htmlspecialchars($row['from_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>To Date</th>
                                            <td><?php echo htmlspecialchars($row['to_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Training Fee</th>
                                            <td><?php echo htmlspecialchars($row['training_fee']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Overview</th>
                                            <td><?php echo htmlspecialchars($row['overview']); ?></td>
                                        </tr>
                                    </table>
                                    <h3>Satisfaction Ratings:</h3>
                                    <table class="feedback-table">
                                        <tr>
                                            <th>Quality Aspect</th>
                                            <th>Rating</th>
                                        </tr>
                                        <tr>
                                            <td>Effectiveness of Training Module</td>
                                            <td><?php echo htmlspecialchars($row['effectiveness']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Training Programme</td>
                                            <td><?php echo htmlspecialchars($row['programme']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Mode of conduct of Programme</td>
                                            <td><?php echo htmlspecialchars($row['conduct']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Interaction of Instructors</td>
                                            <td><?php echo htmlspecialchars($row['instructor']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Training Aids</td>
                                            <td><?php echo htmlspecialchars($row['aids']); ?></td>
                                        </tr>
                                    </table>
                                    <?php if ($row['dynamic_role'] == 'ad' && $row['ad_status'] == 0): ?>
                                        <form method="post">
                                            <label for="ad_remarks">AD Remarks:</label>
                                            <textarea name="ad_remarks" id="ad_remarks" required></textarea>
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="button">Submit</button>
                                        </form>
                                    <?php elseif ($row['dynamic_role'] == 'ad' && $row['ad_status'] == 1): ?>
                                        <p>AD Remarks: <?php echo htmlspecialchars($row['ad_remarks']); ?></p>
                                    <?php elseif ($row['dynamic_role'] == 'tcp_hr_ad' && $row['ad_status'] == 1 && empty($row['tcp_hr_ad_remarks'])): ?>
                                        <form method="post">
                                            <p>AD Remarks: <?php echo htmlspecialchars($row['ad_remarks']); ?></p>
                                            <p>TCP HR Head Remarks: <?php echo htmlspecialchars($row['tcp_hr_head_remarks']); ?></p>
                                            <label for="tcp_hr_ad_remarks">TCP HR AD Remarks:</label>
                                            <textarea name="tcp_hr_ad_remarks" id="tcp_hr_ad_remarks" required></textarea>
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="button">Submit</button>
                                        </form>
                                    <?php elseif ($row['dynamic_role'] == 'tcp_hr_ad' && $row['ad_status'] == 1 && !empty($row['tcp_hr_ad_remarks'])): ?>
                                        <p>AD Remarks: <?php echo htmlspecialchars($row['ad_remarks']); ?></p>
                                        <p>TCP HR AD Remarks: <?php echo htmlspecialchars($row['tcp_hr_ad_remarks']); ?></p>
                                        <p>TCP HR Head Remarks: <?php echo htmlspecialchars($row['tcp_hr_head_remarks']); ?></p>
                                    <?php elseif ($row['dynamic_role'] == 'tcp_hr_head' && $row['ad_status'] == 1 && empty($row['tcp_hr_head_remarks'])): ?>
                                        <form method="post">
                                            <p>AD Remarks: <?php echo htmlspecialchars($row['ad_remarks']); ?></p>
                                            <p>TCP HR AD Remarks: <?php echo htmlspecialchars($row['tcp_hr_ad_remarks']); ?></p>
                                            <label for="tcp_hr_head_remarks">TCP HR Head Remarks:</label>
                                            <textarea name="tcp_hr_head_remarks" id="tcp_hr_head_remarks" required></textarea>
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="button">Submit</button>
                                        </form>
                                    <?php elseif ($row['dynamic_role'] == 'tcp_hr_head' && !empty($row['tcp_hr_head_remarks'])): ?>
                                        <p>AD Remarks: <?php echo htmlspecialchars($row['ad_remarks']); ?></p>
                                        <p>TCP HR AD Remarks: <?php echo htmlspecialchars($row['tcp_hr_ad_remarks']); ?></p>
                                        <p>TCP HR Head Remarks: <?php echo htmlspecialchars($row['tcp_hr_head_remarks']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No feedback available.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(id) {
            var detailsRow = document.getElementById('details-' + id);
            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
            } else {
                detailsRow.classList.add('hidden');
            }
        }

        function printFeedback(id) {
            var feedbackDiv = document.getElementById('feedback-' + id);
            var newWindow = window.open('', '', 'width=800,height=600');
            newWindow.document.write('<html><head><title>Feedback Details</title></head><body>' + feedbackDiv.innerHTML + '</body></html>');
            newWindow.document.close();
            newWindow.print();
        }
    </script>
</body>
</html>
