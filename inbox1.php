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

$sql_group_info_2 = "SELECT group_head_id, ad_id FROM group_info WHERE group_id=2";
$result_group_info_2 = mysqli_query($conn, $sql_group_info_2);
if (!$result_group_info_2) {
    die("Error fetching group info: " . mysqli_error($conn));
}
$group_info_2 = mysqli_fetch_assoc($result_group_info_2);
$group_head_id_2 = $group_info_2['group_head_id'];
$ad_id_2 = $group_info_2['ad_id'];

// Determine the role of the current session user
$role = '';
if ($current_session_user_id == $group_head_id_2) {
    $role = 'tcp_hr_head';
} elseif ($current_session_user_id == $ad_id_2) {
    $role = 'tcp_hr_ad';
} elseif ($current_session_internal_desig == 1) {
    $role = 'director';
} else {
    $role = 'employee'; // Default role if none of the conditions are met
}

// $role = '';
// if ($current_session_group_id == 2 && $current_session_internal_desig == 3) {
//     $role = 'tcp_hr_head';
// } elseif ($current_session_group_id == 2 && $current_session_internal_desig == 2) {
//     $role = 'tcp_hr_ad';
// } elseif ($current_session_internal_desig == 1) {
//     $role = 'director';
// } else {
//     $role = 'employee'; // Default role if none of the conditions are met
// }

// Fetch applications
$sql_applications = "SELECT a.*, e.username AS applicant_username, e.group_id AS applicant_group_id 
                     FROM applications a
                     JOIN emp e ON a.name_of_applicant = e.username
                     ORDER BY a.id DESC";

$result_applications = mysqli_query($conn, $sql_applications);
if (!$result_applications) {
    die("Error fetching applications: " . mysqli_error($conn));
}

$applications = [];
while ($row = mysqli_fetch_assoc($result_applications)) {
    // Fetch the applicant_id using the username from the 'emp' table
    $applicant_username = $row['applicant_username'];
    $sql_applicant_id = "SELECT id FROM emp WHERE username='$applicant_username'";
    $result_applicant_id = mysqli_query($conn, $sql_applicant_id);
    if (!$result_applicant_id) {
        die("Error fetching applicant ID: " . mysqli_error($conn));
    }
    $applicant_id_row = mysqli_fetch_assoc($result_applicant_id);
    $applicant_id = $applicant_id_row['id'];

    //applicant role
    $applicant_role = '';
    if ($applicant_id == $group_head_id_2) {
    $applicant_role = 'tcp_hr_head';
     } elseif ($applicant_id == $ad_id_2) {
    $applicant_role = 'tcp_hr_ad';
    } 

    // Fetch group_head_id and ad_id for the applicant's group
    $applicant_group_id = $row['applicant_group_id'];
    $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id='$applicant_group_id'";
    $result_group_info = mysqli_query($conn, $sql_group_info);
    if (!$result_group_info) {
        die("Error fetching group info: " . mysqli_error($conn));
    }
    $group_info = mysqli_fetch_assoc($result_group_info);
    $group_head_id = $group_info['group_head_id'];
    $ad_id = $group_info['ad_id'];



    $group_head_status = $row['group_head_status'];
    $ad_status = $row['ad_status'];
    $tcp_hr_head_status = $row['tcp_hr_head_status'];
    $tcp_hr_ad_status = $row['tcp_hr_ad_status'];
    $director_status = $row['director_status'];

    if ($applicant_id == $group_head_id) {
        $group_head_status = 1;
    }

    if ($applicant_id == $ad_id) {
        $group_head_status = 1;
        $ad_status = 1;
    }

    if ($applicant_role == 'tcp_hr_head') {
        $group_head_status = 1;
        $ad_status = 1;
        $tcp_hr_head_status = 1;
    }

    if ($applicant_role == 'tcp_hr_ad') {
        $group_head_status = 1;
        $ad_status = 1;
        $tcp_hr_head_status = 1;
        $tcp_hr_ad_status = 1;
    }


    $application_id = $row['id'];
    $sql_update_status = "UPDATE applications 
                          SET group_head_status = '$group_head_status',
                              ad_status = '$ad_status',
                              tcp_hr_head_status = '$tcp_hr_head_status',
                              tcp_hr_ad_status = '$tcp_hr_ad_status'
                          WHERE id = '$application_id'";
    $result_update_status = mysqli_query($conn, $sql_update_status);
    if (!$result_update_status) {
        die("Error updating application status: " . mysqli_error($conn));
    }

    // Add the updated row to the applications array
    $row['group_head_status'] = $group_head_status;
    $row['ad_status'] = $ad_status;
    $row['tcp_hr_head_status'] = $tcp_hr_head_status;
    $row['tcp_hr_ad_status'] = $tcp_hr_ad_status;


    // Determine the role of the current session user with respect to the applicant's application
    if ($current_session_user_id == $group_head_id) {
        $row['dynamic_role'] = 'group_head';
    } elseif ($current_session_user_id == $ad_id) {
        $row['dynamic_role'] = 'ad';
    } else {
        $row['dynamic_role'] = $role;
    }

    $applications[] = $row;
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
            background-color: #1976d2c9;
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
        .inbox {
            color: #6d0094;
        }
        p {
            text-align: center;
            font-size: 18px;
            color: #dd1313;
            font-weight: bold;
        }
        .color {
            color: #80007d;
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
        <!-- <h1>Inbox</h1> -->
        <h2 class="color">Pending Applications</h2>
        <?php
        $has_pending_applications = false;
        echo "<table><tr><th>Training Event ID</th><th>Applicant username</th><th>Designation</th><th>Name of Training Program</th><th>From Date</th><th>To Date</th><th>Venue</th><th>Action</th></tr>";
        foreach ($applications as $application) {
            $id = $application["id"];
            $dynamic_role = $application['dynamic_role'];
            $show_row = false;
        
            if (($dynamic_role == 'group_head' && $application['group_head_status'] == 0) || 
                ($dynamic_role == 'ad' && $application['group_head_status'] == 1 && $application['ad_status'] == 0) ||
                ($dynamic_role == 'tcp_hr_head' && $application['ad_status'] == 1 && $application['tcp_hr_head_status'] == 0) ||
                ($dynamic_role == 'tcp_hr_ad' && $application['tcp_hr_head_status'] == 1 && $application['tcp_hr_ad_status'] == 0) ||
                ($dynamic_role == 'director' && $application['tcp_hr_ad_status'] == 1 && $application['director_status'] == 0)) {
                $show_row = true;
                $has_pending_applications = true;
            }
        
            if ($show_row) {
                echo "<tr><td>" . htmlspecialchars($application["seminar_id"]) . "</td><td>" . htmlspecialchars($application["name_of_applicant"]) . "</td><td>" . htmlspecialchars($application["designation"]) . "</td><td>" . htmlspecialchars($application["name_of_seminar"]) . "</td><td>" . htmlspecialchars($application["from_date"]) . "</td><td>" . htmlspecialchars($application["to_date"]) . "</td><td>" . htmlspecialchars($application["location"]) . "</td>";
                echo "<td class='action-links'>";
                echo "<a href='approve.php?id=" . htmlspecialchars($id) . "&dynamic_role=" . htmlspecialchars($dynamic_role) . "'>Approve</a> | ";
                echo "<a href='reject.php?id=" . htmlspecialchars($id) . "&dynamic_role=" . htmlspecialchars($dynamic_role) . "'>Reject</a> | ";
                echo "<a href='view_application.php?id=" . htmlspecialchars($id) . "' class='view-button'>View Full Application</a>";
                if($dynamic_role == 'director'){
                    echo " | <a href='headquaters.php?id=" . htmlspecialchars($id) . "&dynamic_role=" . htmlspecialchars($dynamic_role) . "' class='view-button'>Headquarters permission required</a>";
                }
                echo "</td></tr>";
            }
        }        
        echo "</table>";
        if (!$has_pending_applications) {
            echo "<p>No pending applications found</p>";
        }
        ?>

        <h2 class="color">Past Applications</h2>
        <?php
        $has_past_applications = false;
        echo "<table><tr><th>Training Event ID</th><th>Applicant username</th><th>Designation</th><th>Name of Training Program</th><th>From Date</th><th>To Date</th><th>Venue</th><th>Action</th></tr>";
        foreach ($applications as $application) {
            if ((
                ($application['dynamic_role'] == 'group_head' && $application['group_head_status'] != 0) ||
                ($application['dynamic_role'] == 'ad' && $application['ad_status'] != 0) ||
                ($application['dynamic_role'] == 'tcp_hr_head' && $application['tcp_hr_head_status'] != 0) ||
                ($application['dynamic_role'] == 'tcp_hr_ad' && $application['tcp_hr_ad_status'] != 0) ||
                ($application['dynamic_role'] == 'director' && $application['director_status'] != 0)
            )) {
                $has_past_applications = true;
                echo "<tr><td>" . $application["seminar_id"] . "</td><td>" . $application["name_of_applicant"] . "</td><td>" . $application["designation"] . "</td><td>" . $application["name_of_seminar"] . "</td><td>" . $application["from_date"] . "</td><td>" . $application["to_date"] . "</td><td>" . $application["location"] . "</td>";
                echo "<td class='action-links'><a href='view_application.php?id=" . $application["id"] . "' class='view-button'>View Full Application</a></td></tr>";
            }
        }
        echo "</table>";
        if (!$has_past_applications) {
            echo "<p>No past applications found</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
