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

$username = $_SESSION['username'];

// Update the SQL query to order by id in descending order
$sql = "SELECT * FROM applications WHERE name_of_applicant=? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <style>
        .header {
            background-color: #002147;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header img {
            height: 50px;
        }
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
            margin-bottom: 20px;
        }
        .progress-container {
            position: relative;
            width: 100%;
            margin: 40px 0;
            height: 40px;
        }
        .progress-bar {
            position: absolute;
            width: 100%;
            height: 5px;
            background: #d3e5ff;
            border-radius: 5px;
        }
        .progress-dot {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: -8px;
            transform: translateX(-50%);
            transition: background 0.3s;
        }
        .progress-dot.completed {
            background: #4CAF50;
        }
        .progress-dot.pending {
            background: #1976D2;
        }
        .progress-dot.rejected {
            background: #FF0000;
        }
        .progress-dot.grey {
            background: #CCCCCC;
        }
        .progress-label {
            position: absolute;
            top: 25px;
            transform: translateX(-50%);
            font-size: 12px;
            color: #555;
        }
        .application-details {
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .application-details h3 {
            color: #1976D2;
        }
        .status-message {
            font-size: 16px;
            font-weight: bold;
            color: #1976D2;
        }
        .print-link {
            display: block;
            text-align: center;
            margin: 20px 0;
            background-color: #1976D2;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .print-link:hover {
            background-color: #135a9e;
        }
        hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
        }
        .button-group button {
            background-color: #1976D2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button-group button:hover {
            background-color: #135a9e;
        }
        .remarks {
            font-size: 14px;
            color: #333;
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
<br>
<?php include 'home_button.php'; ?>
<div class="container">
    <h1>Track Your Applications</h1>
    
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='application-details'>";
            echo "<h3>Application " . $row["id"] . "</h3>";
            echo "<div class='progress-container'>";
            echo "<div class='progress-bar'></div>";

            $progress_steps = [
                'Application Submitted' => 0,
                'Group Head Status' => 16,
                'AD Status' => 33,
                'TCP-HR Head Status' => 50,
                'TCP-HR AD Status' => 66,
                'Director Status' => 83,
                'Completed' => 100
            ];

            $current_progress = 16;
            $status = 'Pending';
            $is_rejected = false;

            // Determine the current progress and status
            if ($row["group_head_status"] == 1) {
                $current_progress = 33;
                if ($row["ad_status"] == 1) {
                    $current_progress = 50;
                    if ($row["tcp_hr_head_status"] == 1) {
                        $current_progress = 66;
                        if ($row["tcp_hr_ad_status"] == 1) {
                            $current_progress = 83;
                            if ($row["director_status"] == 1) {
                                $current_progress = 100;
                                $status = 'Approved';
                            } elseif ($row["director_status"] == -1) {
                                $status = 'Rejected by Director';
                                $is_rejected = true;
                                $current_progress = 83;
                            } else {
                                $status = 'Pending Director Approval';
                            }
                        } elseif ($row["tcp_hr_ad_status"] == -1) {
                            $status = 'Rejected by TCP-HR AD';
                            $is_rejected = true;
                            $current_progress = 66;
                        } else {
                            $status = 'Pending TCP-HR AD Approval';
                        }
                    } elseif ($row["tcp_hr_head_status"] == -1) {
                        $status = 'Rejected by TCP-HR Head';
                        $is_rejected = true;
                        $current_progress = 50;
                    } else {
                        $status = 'Pending TCP-HR Head Approval';
                    }
                } elseif ($row["ad_status"] == -1) {
                    $status = 'Rejected by AD';
                    $is_rejected = true;
                    $current_progress = 33;
                } else {
                    $status = 'Pending AD Approval';
                }
            } elseif ($row["group_head_status"] == -1) {
                $status = 'Rejected by Group Head';
                $is_rejected = true;
                $current_progress = 16;
            } else {
                $status = 'Pending Group Head Approval';
            }

            foreach ($progress_steps as $step => $percent) {
                $dot_class = 'progress-dot pending';

                if ($step === 'Application Submitted' || ($step === 'Completed' && $current_progress === 100)) {
                    $dot_class = 'progress-dot completed';
                } elseif ($percent < $current_progress) {
                    $dot_class = 'progress-dot completed';
                } elseif ($percent == $current_progress && $is_rejected) {
                    $dot_class = 'progress-dot rejected';
                } elseif ($percent == $current_progress && !$is_rejected) {
                    $dot_class = 'progress-dot pending';
                } elseif ($percent > $current_progress) {
                    $dot_class = 'progress-dot grey';
                }

                echo "<div class='$dot_class' style='left: $percent%;'></div>";
                echo "<div class='progress-label' style='left: $percent%;'>$step</div>";
            }

            echo "</div>";
            echo "<p class='status-message'>Status: " . htmlspecialchars($status) . "</p>";
            if (!is_null($row['group_head_remarks'])) {
                echo "<p class='remarks'><strong>Remarks by Group Head:</strong> " . htmlspecialchars($row['group_head_remarks']) . "</p>";
            }
            if (!is_null($row['ad_remarks'])) {
                echo "<p class='remarks'><strong>Remarks by AD:</strong> " . htmlspecialchars($row['ad_remarks']) . "</p>";
            }
            if (!is_null($row['tcp_hr_head_remarks'])) {
                echo "<p class='remarks'><strong>Remarks by TCP HR HEAD:</strong> " . htmlspecialchars($row['tcp_hr_head_remarks']) . "</p>";
            }
            if (!is_null($row['tcp_hr_ad_remarks'])) {
                echo "<p class='remarks'><strong>Remarks by TCP HR AD:</strong> " . htmlspecialchars($row['tcp_hr_ad_remarks']) . "</p>";
            }
            if (!is_null($row['director_remarks'])) {
                echo "<p class='remarks'><strong>Remarks by Director:</strong> " . htmlspecialchars($row['director_remarks']) . "</p>";
            } 
            if ($status === "Approved") {
                echo "<a href='print_application2.php?id=" . $row["id"] . "' target='_blank' class='print-link'>Print Application Form</a>";
                echo "<a href='feedback.php?id=" . $row["id"] . "'class='print-link'>Submit/View feedback</a>";
            } 
            elseif (strpos($status, "Rejected") !== false) {
                echo "<p class='remarks'><strong>Reason for rejection:</strong> " . htmlspecialchars($row['remarks']) . "</p>";
            }
            else{
                if (!is_null($row['remarks'])) {
                    echo "<p class='remarks'><strong>Remarks :</strong> " . htmlspecialchars($row['remarks']) . "</p>";
                }
                echo "<a href='print_application.php?id=" . $row["id"] . "' target='_blank' class='print-link'>View Application Details</a>";
            }

            echo "<hr>";
            echo "</div>";
        }
    } else {
        echo "<p>No applications found for user: " . htmlspecialchars($username) . "</p>";
    }
    ?>
</div>
</body>
</html>
