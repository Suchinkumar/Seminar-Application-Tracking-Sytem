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

// Fetch seminar IDs
$sql_seminars = "SELECT DISTINCT seminar_id, name_of_seminar FROM applications ORDER BY seminar_id";
$result_seminars = mysqli_query($conn, $sql_seminars);
if (!$result_seminars) {
    die("Error fetching seminars: " . mysqli_error($conn));
}

$seminar_id = isset($_POST['seminar_id']) ? $_POST['seminar_id'] : '';

// Fetch applicants for the selected seminar ID
$sql_applicants_pending = "";
$sql_applicants_past = "";
if ($seminar_id) {
    // Fetch pending applications
    $sql_applicants_pending = "SELECT a.*, e.group_id, e.internal_desig_id, e.id AS applicant_id FROM applications a 
                                JOIN emp e ON a.name_of_applicant = e.username 
                                WHERE a.seminar_id='$seminar_id'";
    // if ($role == 'group_head' || $role == 'ad') {
    //     $sql_applicants_pending .= " AND e.group_id='$current_session_group_id'";
    // }
    $sql_applicants_pending .= " ORDER BY a.seminar_id";
    $result_applicants_pending = mysqli_query($conn, $sql_applicants_pending);
    if (!$result_applicants_pending) {
        die("Error fetching pending applicants: " . mysqli_error($conn));
    }

    // Fetch past applications
    $sql_applicants_past = "SELECT a.*, e.group_id, e.internal_desig_id, e.id AS applicant_id FROM applications a 
                            JOIN emp e ON a.name_of_applicant = e.username 
                            WHERE a.seminar_id='$seminar_id'";
    // if ($role == 'group_head' || $role == 'ad') {
    //     $sql_applicants_past .= " AND e.group_id='$current_session_group_id'";
    // }
    $sql_applicants_past .= " ORDER BY a.seminar_id";
    $result_applicants_past = mysqli_query($conn, $sql_applicants_past);
    if (!$result_applicants_past) {
        die("Error fetching past applicants: " . mysqli_error($conn));
    }
} else {
    $result_applicants_pending = false;
    $result_applicants_past = false;
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
    <form method="post" action="">
        <label for="seminar_id">Choose Training Program ID:</label>
        <select name="seminar_id" id="seminar_id" onchange="this.form.submit()">
            <option value="">Select Training Program ID</option>
            <?php
while ($row = mysqli_fetch_assoc($result_seminars)) {
    if ($row['seminar_id'] != -1) { // Add this condition to check for seminar_id not equal to -1
        $selected = $seminar_id == $row['seminar_id'] ? 'selected' : '';
        echo "<option value='" . $row['seminar_id'] . "' $selected>" . $row['name_of_seminar'] . " ----->> " . $row['seminar_id'] . "</option>";
    }
}
?>

        </select>
    </form>

    <h2>Pending Applications</h2>
    <?php
    if ($result_applicants_pending && mysqli_num_rows($result_applicants_pending) > 0) {
        echo "<table><tr><th>Application No</th><th>Applicant username</th><th>Designation</th><th>Name of training program</th><th>From Date</th><th>To Date</th><th>Action</th></tr>";

        while ($row = mysqli_fetch_assoc($result_applicants_pending)) {
            $id = $row["id"];
            $applicant_id = $row["applicant_id"];
            $applicant_group_id = $row["group_id"];

            // Fetch group_head_id and ad_id for the applicant's group
            $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id='$applicant_group_id'";
            $result_group_info = mysqli_query($conn, $sql_group_info);
            if (!$result_group_info) {
                die("Error fetching group info: " . mysqli_error($conn));
            }
            $group_info = mysqli_fetch_assoc($result_group_info);
            $group_head_id = $group_info['group_head_id'];
            $ad_id = $group_info['ad_id'];

            // Determine the role of the current session user with respect to the applicant's application
            // $applicant_role = '';
            if ($current_session_user_id == $group_head_id) {
                $role = 'group_head';
            } elseif ($current_session_user_id == $ad_id) {
                $role = 'ad';
            }

            if (($role == 'group_head' && $row["group_head_status"] == 0) ||
                ($role == 'ad' && $row["group_head_status"] == 1 && $row["ad_status"] == 0) ||
                ($role == 'tcp_hr_head' && $row["ad_status"] == 1 && $row["tcp_hr_head_status"] == 0) ||
                ($role == 'tcp_hr_ad' && $row["tcp_hr_head_status"] == 1 && $row["tcp_hr_ad_status"] == 0) ||
                ($role == 'director' && $row["tcp_hr_ad_status"] == 1 && $row["director_status"] == 0)) {
                    echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name_of_applicant"] . "</td><td>" . $row["designation"] . "</td><td>" . $row["name_of_seminar"] . "</td><td>" . $row["from_date"] . "</td><td>" . $row["to_date"] . "</td>";
                    echo "<td class='action-links'>";
                    echo "<a href='approve.php?id=" . $id . "&dynamic_role=" . $role . "'>Approve</a> | ";
                    echo "<a href='reject.php?id=" . $id . "&dynamic_role=" . $role . "'>Reject</a> | ";
                    echo "<a href='view_application.php?id=" . $id . "' class='view-button'>View / Print Full Application</a></td></tr>";
            } 
            else {
            }
            echo "</td></tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No pending applications.</p>";
    }
    ?>

    <h2>Past Applications</h2>
    <?php
    if ($result_applicants_past && mysqli_num_rows($result_applicants_past) > 0) {
        echo "<table><tr><th>Application No</th><th>Applicant username</th><th>Designation</th><th>Name of training program</th><th>From Date</th><th>To Date</th><th>Action</th></tr>";

        while ($row = mysqli_fetch_assoc($result_applicants_past)) {
            $id = $row["id"];
            $applicant_id = $row["applicant_id"];
            $applicant_group_id = $row["group_id"];

            // Fetch group_head_id and ad_id for the applicant's group
            $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id='$applicant_group_id'";
            $result_group_info = mysqli_query($conn, $sql_group_info);
            if (!$result_group_info) {
                die("Error fetching group info: " . mysqli_error($conn));
            }
            $group_info = mysqli_fetch_assoc($result_group_info);
            $group_head_id = $group_info['group_head_id'];
            $ad_id = $group_info['ad_id'];

            // Determine the role of the current session user with respect to the applicant's application
            // $applicant_role = '';
            if ($current_session_user_id == $group_head_id) {
                // $applicant_role = 'group_head';
                $role = 'group_head';
            } elseif ($current_session_user_id == $ad_id) {
                // $applicant_role = 'ad';
                $role = 'ad';
            }
            if (($role == 'group_head' && $row["group_head_status"] != 0) ||
                ($role == 'ad' && $row["ad_status"] != 0) ||
                ($role == 'tcp_hr_head' && $row["tcp_hr_head_status"] != 0) ||
                ($role == 'tcp_hr_ad' && $row["tcp_hr_ad_status"] != 0) ||
                ($role == 'director' && $row["director_status"] != 0)) {
                // echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name_of_applicant"] . "</td><td>" . $row["designation"] . "</td><td>" . $row["name_of_seminar"] . "</td><td>" . $row["from_date"] . "</td><td>" . $row["to_date"] . "</td>";
                // echo "<a href='view_application.php?id=" . $id . "' class='view-button'>View Full Application</a>";;
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name_of_applicant"] . "</td><td>" . $row["designation"] . "</td><td>" . $row["name_of_seminar"] . "</td><td>" . $row["from_date"] . "</td><td>" . $row["to_date"] . "</td>";
            echo "<td class='action-links'><a href='view_application.php?id=" . $row["id"] . "' class='view-button'>View / Print Full Application</a></td></tr>";
            } else {
                // echo "NO RECORDS FOUND";
            }
            echo "</td></tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No past applications.</p>";
    }
    ?>
</div>
</body>
</html>
