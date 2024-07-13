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
    <title>Print Application Form</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 900px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .header {
            background-color: #0056b3;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .header h1 {
            color: white;
            text-align: center;
            margin: 0;
            flex-grow: 1;
        }
        .header img {
            height: 50px;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-header {
            background: #0056b3;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
        }
        .section-header h2 {
            color: white;
            margin: 0;
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
        }
        label {
            font-weight: bold;
            color: #555;
            flex-basis: 30%;
        }
        .value {
            flex-basis: 70%;
            font-style: italic;
            color: #000;
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
            /* -webkit-print-color-adjust: exact; Ensures colored print */
        }
        .btn:hover {
            background-color: #45a049;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            color: black;
        }
        .table td {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="drdo_logo_0.png" alt="DRDO Logo">
            <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
            <img src="drdo_logo_0.png" alt="DRDO Logo">
        </div>
        <?php include 'home_button.php'; ?>
        <h1>Application Form</h1>
        <?php if ($application): ?>
            <div class="section">
                <div class="section-header">
                    <h2>A. General Information</h2>
                </div>
                <table class="table">
                    <!-- <tr>
                        <th>Application Number:</th>
                        <td><?php echo htmlspecialchars($application['id']); ?></td>
                    </tr> -->
                    <tr>
                        <th>Username of Applicant:</th>
                        <td><?php echo htmlspecialchars($application['name_of_applicant']); ?></td>
                    </tr>
                    <!-- <tr>
                        <th>First Name of Applicant:</th>
                        <td><?php echo htmlspecialchars($first_name); ?></td>
                    </tr>
                    <tr>
                        <th>Applicant ID:</th>
                        <td><?php echo htmlspecialchars($emp_id); ?></td>
                    </tr> -->
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
                        <th>Designation:</th>
                        <td><?php echo htmlspecialchars($application['designation']); ?></td>
                    </tr>
                    <tr>
                        <th>Group:</th>
                        <td><?php echo htmlspecialchars_decode($application['group_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Qualification:</th>
                        <td><?php echo htmlspecialchars($application['qualification']); ?></td>
                    </tr>
                    <tr>
                        <th>From Date:</th>
                        <td><?php echo htmlspecialchars($application['from_date']); ?></td>
                    </tr>
                    <tr>
                        <th>To Date:</th>
                        <td><?php echo htmlspecialchars($application['to_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td><?php echo htmlspecialchars($application['duration']); ?></td>
                    </tr>
                    <tr>
                        <th>Place:</th>
                        <td><?php echo htmlspecialchars($application['place']); ?></td>
                    </tr>
                    <tr>
                        <th>Location/Venue:</th>
                        <td><?php echo htmlspecialchars($application['location']); ?></td>
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
                </table>
            </div>
            <div class="section">
                <div class="section-header">
                    <h2>B. Additional Information</h2>
                </div>
                <table class="table">
                    <tr>
                        <th>Last Date of Submission:</th>
                        <td><?php echo htmlspecialchars($application['last_date_of_submission']); ?></td>
                    </tr>
                    <tr>
                        <th>Last Training Program Attended:</th>
                        <td><?php echo htmlspecialchars($application['last_conference_attended']); ?></td>
                    </tr>
                    <!-- <tr>
                        <th>Name of Group Head:</th>
                        <td><?php echo htmlspecialchars($application['name_of_group_head']); ?></td>
                    </tr> -->
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
                            <th>TCP-HR-HEAD Remarks:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_head_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['tcp_hr_ad_remarks'])): ?>
                        <tr>
                            <th>TCP-HR-AD Remarks:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_ad_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($application['director_remarks'])): ?>
                        <tr>
                            <th>Director Remarks:</th>
                            <td><?php echo htmlspecialchars($application['director_remarks']); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="section">
                <div class="section-header">
                    <h2>C. Past Training Details</h2>
                </div>
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
            <div class="section">
                <div class="section-header">
                    <h2>D. Application Approval Status</h2>
                </div>
                <table class="table">
                    <tr>
                        <th>Group Head Status:</th>
                        <td><?php echo "Approved"; ?></td>
                    </tr>
                    <?php if (!empty($application['group_head_time'])): ?>
                        <tr>
                            <th>Approved by Group Head on:</th>
                            <td><?php echo htmlspecialchars($application['group_head_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>AD Status:</th>
                        <td><?php echo "Approved"; ?></td>
                    </tr>
                    <?php if (!empty($application['ad_time'])): ?>
                        <tr>
                            <th>Approved by AD on:</th>
                            <td><?php echo htmlspecialchars($application['ad_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>TCP-HR-HEAD Status:</th>
                        <td><?php echo "Approved"; ?></td>
                    </tr>
                    <?php if (!empty($application['tcp_hr_head_time'])): ?>
                        <tr>
                            <th>Approved by TCP HR HEAD on:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_head_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>TCP-HR-AD Status:</th>
                        <td><?php echo "Approved"; ?></td>
                    </tr>
                    <?php if (!empty($application['tcp_hr_ad_time'])): ?>
                        <tr>
                            <th>Approved by TCP HR AD on:</th>
                            <td><?php echo htmlspecialchars($application['tcp_hr_ad_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Director Status:</th>
                        <td><?php echo "Approved"; ?></td>
                    </tr>
                    <?php if (!empty($application['director_time'])): ?>
                        <tr>
                            <th>Approved by Director on:</th>
                            <td><?php echo htmlspecialchars($application['director_time']); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="button-container">
                <button class="btn" onclick="window.print()">Print</button>
            </div>
        <?php else: ?>
            <p>Application not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
