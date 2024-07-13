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

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM applications WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    if ($application) {
        $username = $application['name_of_applicant'];
        $sql_emp = "SELECT first_name, id FROM emp WHERE username=?";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->bind_param("s", $username);
        $stmt_emp->execute();
        $result_emp = $stmt_emp->get_result();
        $employee = $result_emp->fetch_assoc();

        if ($employee) {
            $first_name = htmlspecialchars($employee['first_name']);
            $emp_id = htmlspecialchars($employee['id']);
        } else {
            echo "Employee details not found.<br>";
        }

        // Fetch past training details
        $sql_training = "SELECT * FROM training_details WHERE username=? ORDER BY id DESC LIMIT 5";
        $stmt_training = $conn->prepare($sql_training);
        $stmt_training->bind_param("s", $username);
        $stmt_training->execute();
        $result_training = $stmt_training->get_result();
    } else {
        echo "No application found.";
    }
} else {
    die("Invalid application ID.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .application-details {
            font-size: 14px;
            margin-top: 20px;
        }
        .application-details h2 {
            background-color: #1976D2;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .application-details table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .application-details th, .application-details td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .application-details th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .application-details td {
            background-color: #fafafa;
        }
        .print-button {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 20px;
            background-color: #1976D2;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .print-button:hover {
            background-color: #135a9e;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<br>
    <?php include 'home_button.php'; ?>
    <div class="container">
        <h1>Application Details</h1>
        <?php if ($application): ?>
        <div class="application-details">
            <h2>Applicant Information</h2>
            <table>
                <tr>
                    <th>Username of Applicant:</th>
                    <td><?php echo htmlspecialchars($application['name_of_applicant']); ?></td>
                </tr>
                <tr>
                    <th>Designation:</th>
                    <td><?php echo htmlspecialchars($application['designation']); ?></td>
                </tr>
                <tr>
                    <th>Qualification:</th>
                    <td><?php echo htmlspecialchars($application['qualification']); ?></td>
                </tr>
                <tr>
                    <th>Name of Training Program:</th>
                    <td><?php echo htmlspecialchars($application['name_of_seminar']); ?></td>
                </tr>
                <tr>
                    <th>Training Program ID:</th>
                    <td><?php echo htmlspecialchars($application['seminar_id']); ?></td>
                </tr>
                <tr>
                    <th>Type of Training Program:</th>
                    <td><?php echo htmlspecialchars($application['type_of_event']); ?></td>
                </tr>
                <tr>
                    <th>Place:</th>
                    <td><?php echo htmlspecialchars($application['place']); ?></td>
                </tr>
                <tr>
                    <th>Location/Venue:</th>
                    <td><?php echo htmlspecialchars($application['location']); ?></td>
                </tr>
                <tr>
                    <th>Last Date of Submission:</th>
                    <td><?php echo htmlspecialchars($application['last_date_of_submission']); ?></td>
                </tr>
                <tr>
                    <th>Last Training Program Attended:</th>
                    <td><?php echo htmlspecialchars($application['last_conference_attended']); ?></td>
                </tr>
                <?php if (!empty($application['is_paid'])): ?>
                <tr>
                    <th>Is it a Paid Training Program?</th>
                    <td><?php echo htmlspecialchars($application['is_paid'] ? 'Yes' : 'No'); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['transaction_in_favour_of'])): ?>
                <tr>
                    <th>Transaction in Favour of:</th>
                    <td><?php echo htmlspecialchars($application['transaction_in_favour_of']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['transaction_amount'])): ?>
                <tr>
                    <th>Transaction Amount:</th>
                    <td><?php echo htmlspecialchars($application['transaction_amount']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['transaction_mode'])): ?>
                <tr>
                    <th>Transaction Mode:</th>
                    <td><?php echo htmlspecialchars($application['transaction_mode']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['group_head_remarks'])): ?>
                        <tr>
                            <th>Group Head Remarks:</th>
                            <td><?php echo htmlspecialchars($application['group_head_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['ad_remarks'])): ?>
                        <tr>
                            <th>AD Remarks:</th>
                            <td><?php echo htmlspecialchars($application['ad_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['tcp_hr_head_remarks'])): ?>
                        <tr>
                            <th>TCP HR Head Remarks:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_head_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['tcp_hr_ad_remarks'])): ?>
                        <tr>
                            <th>TCP HR AD Remarks:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_ad_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['headquaters_remarks'])): ?>
                        <tr>
                            <th>Remarks:</th>
                            <td><?php echo htmlspecialchars($application['headquaters_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['director_remarks'])): ?>
                        <tr>
                            <th>Director Remarks:</th>
                            <td><?php echo htmlspecialchars($application['director_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php if (!empty($application['group_head_time'])): ?>
                <tr>
                    <th>Group Head Status:</th>
                    <td><?php echo ($application['group_head_status'] == 1) ? "Approved" : "Not approved"; ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['ad_status'])): ?>
                <tr>
                    <th>AD Status:</th>
                    <td><?php echo ($application['ad_status'] == 1) ? "Approved" : "Not approved"; ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['tcp_hr_head_status'])): ?>
                <tr>
                    <th>TCP HR HEAD Status:</th>
                    <td><?php echo ($application['tcp_hr_head_status'] == 1) ? "Approved" : "Not approved"; ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['tcp_hr_ad_status'])): ?>
                <tr>
                    <th>TCP HR AD Status:</th>
                    <td><?php echo ($application['tcp_hr_ad_status'] == 1) ? "Approved" : "Not approved"; ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['director_status'])): ?>
                <tr>
                    <th>Director Status:</th>
                    <td><?php echo ($application['director_status'] == 1) ? "Approved" : "Not approved"; ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['group_head_time'])): ?>
                <tr>
                    <th>Action taken by Group Head on:</th>
                    <td><?php echo htmlspecialchars($application['group_head_time']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['ad_time'])): ?>
                <tr>
                    <th>Action taken by AD on:</th>
                    <td><?php echo htmlspecialchars($application['ad_time']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['tcp_hr_head_time'])): ?>
                <tr>
                    <th>Action taken by TCP HR HEAD on:</th>
                    <td><?php echo htmlspecialchars($application['tcp_hr_head_time']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['tcp_hr_ad_time'])): ?>
                <tr>
                    <th>Action taken by TCP HR AD on:</th>
                    <td><?php echo htmlspecialchars($application['tcp_hr_ad_time']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($application['headquaters_time'])): ?>
                        <tr>
                            <th>Forwarded to headquaters on:</th>
                            <td><?php echo htmlspecialchars($application['headquaters_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php if (!empty($application['director_time'])): ?>
                <tr>
                    <th>Action taken by Director on:</th>
                    <td><?php echo htmlspecialchars($application['director_time']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            <h2>Past Training Details</h2>

            <?php if ($result_training->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name of training program</th>
                        <th>From date</th>
                        <th>To date</th>
                        <th>Type of training program</th>
                        <th>Location/Venue</th>
                        <th>Place</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_training->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name_of_seminar']); ?></td>
                        <td><?php echo htmlspecialchars($row['from_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['to_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['type_of_event']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['place']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No previous data available.</p>
        <?php endif; ?>
        </div>
        <div class="button-container">
            <button class="btn" onclick="viewFullApplication('<?php echo htmlspecialchars($application['seminar_id'], ENT_QUOTES, 'UTF-8'); ?>')">View Training Event Details</button>
            <a href="javascript:window.print()" class="btn">Print Application</a>
        </div>
        <?php else: ?>
            <p>Application not found.</p>
        <?php endif; ?>
    </div>
</body>
<script>
    function viewFullApplication(seminar_id) {
        window.location.href = 'view_training_heads.php?seminar_id=' + encodeURIComponent(seminar_id);
    }
</script>
</html>
