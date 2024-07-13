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

// Fetch employee details
$sql_emp = "SELECT * FROM emp WHERE username='$username'";
$result_emp = mysqli_query($conn, $sql_emp);
if (!$result_emp) {
    die("Error fetching data: " . mysqli_error($conn));
}
$emp_data = mysqli_fetch_assoc($result_emp);
$group_id = $emp_data['group_id'];
$role = $emp_data['role'];
$desig_id = $emp_data['desig_id'];

// Fetch pending applications based on the new hierarchy
if ($role == 'group_head') {
    $sql_pending = "SELECT a.* FROM applications a
                    JOIN emp e ON a.name_of_applicant = e.username
                    WHERE e.group_id = '$group_id' AND a.group_head_status = 0  ORDER BY a.id DESC";
} elseif ($role == 'ad') {
    $sql_pending = "SELECT a.* FROM applications a
                    JOIN emp e ON a.name_of_applicant = e.username
                    WHERE a.group_head_status = 1 AND a.ad_status = 0  ORDER BY a.id DESC";
} elseif ($role == 'tcp_hr_head') {
    $sql_pending = "SELECT a.* FROM applications a
                    JOIN emp e ON a.name_of_applicant = e.username
                    WHERE a.ad_status = 1 AND a.tcp_hr_head_status = 0  ORDER BY a.id DESC";
} elseif ($role == 'tcp_hr_ad') {
    $sql_pending = "SELECT a.* FROM applications a
                    JOIN emp e ON a.name_of_applicant = e.username
                    WHERE a.tcp_hr_head_status = 1 AND a.tcp_hr_ad_status = 0  ORDER BY a.id DESC";
} elseif ($role == 'director') {
    $sql_pending = "SELECT a.* FROM applications a
                    JOIN emp e ON a.name_of_applicant = e.username
                    WHERE a.tcp_hr_ad_status = 1 AND a.director_status = 0  ORDER BY a.id DESC";
} else {
    $sql_pending = "";
}



if ($sql_pending) {
    $result_pending = mysqli_query($conn, $sql_pending);
    if (!$result_pending) {
        die("Error fetching pending applications: " . mysqli_error($conn));
    }
} else {
    $result_pending = false;
}

// Fetch past applications based on the new hierarchy
$sql_past = "SELECT a.* FROM applications a
             JOIN emp e ON a.name_of_applicant = e.username
             WHERE a.seminar_id = -1 AND (
                (e.group_id = '$group_id' AND '$role' = 'group_head' AND a.group_head_status != 0)
             OR ('$role' = 'ad' AND a.ad_status != 0)
             OR ('$role' = 'tcp_hr_head' AND a.tcp_hr_head_status != 0)
             OR ('$role' = 'tcp_hr_ad' AND a.tcp_hr_ad_status != 0)
             OR ('$role' = 'director' AND a.director_status != 0)
             ) ORDER BY a.id DESC";



$result_past = mysqli_query($conn, $sql_past);

if (!$result_past) {
    die("Error fetching past applications: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
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
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            /* color: #333; */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #1976D2;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-links a {
            color: #1976D2;
            text-decoration: none;
            margin: 0 5px;
            font-weight: bold;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .view-button {
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        .disabled-button {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
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
        <h1>Inbox</h1>
        <h2>Pending Applications</h2>
        <?php
        if ($result_pending && mysqli_num_rows($result_pending) > 0) {
            echo "<table><tr><th>Application No</th><th>Applicant Name</th><th>Designation</th><th>Seminar Name</th><th>From Date</th><th>To Date</th><th>Action</th></tr>";

            while ($row = mysqli_fetch_assoc($result_pending)) {
                $id = $row["id"];
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name_of_applicant"] . "</td><td>" . $row["designation"] . "</td><td>" . $row["name_of_seminar"] . "</td><td>" . $row["from_date"] . "</td><td>" . $row["to_date"] . "</td>";
                echo "<td class='action-links'>";
                if (($role == 'group_head' && $row['group_head_status'] == 0) || 
                    ($role == 'ad' && $row['group_head_status'] == 1 && $row['ad_status'] == 0) ||
                    ($role == 'tcp_hr_head' && $row['ad_status'] == 1 && $row['tcp_hr_head_status'] == 0) ||
                    ($role == 'tcp_hr_ad' && $row['tcp_hr_head_status'] == 1 && $row['tcp_hr_ad_status'] == 0) ||
                    ($role == 'director' && $row['tcp_hr_ad_status'] == 1 && $row['director_status'] == 0)) {
                    echo "<a href='approve.php?id=" . $id . "'>Approve</a> | ";
                    echo "<a href='reject.php?id=" . $id . "'>Reject</a> | ";
                } else {
                    echo "<span class='disabled-button'>Approve</span> | ";
                    echo "<span class='disabled-button'>Reject</span> | ";
                }
                echo "<a href='view_application.php?id=" . $id . "' class='view-button'>View Full Application</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No pending applications found</p>";
        }
        ?>

        <h2>Past Applications</h2>
        <?php
        if (mysqli_num_rows($result_past) > 0) {
            echo "<table><tr><th>Application No</th><th>Applicant Name</th><th>Designation</th><th>Seminar Name</th><th>From Date</th><th>To Date</th><th>Action</th></tr>";

            while ($row = mysqli_fetch_assoc($result_past)) {
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name_of_applicant"] . "</td><td>" . $row["designation"] . "</td><td>" . $row["name_of_seminar"] . "</td><td>" . $row["from_date"] . "</td><td>" . $row["to_date"] . "</td>";
                echo "<td class='action-links'><a href='view_application.php?id=" . $row["id"] . "' class='view-button'>View Full Application</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No past applications found</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
