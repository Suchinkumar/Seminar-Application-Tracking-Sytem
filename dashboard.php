<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Database connection
$servername = "localhost";  // Change if necessary
$usernameDB = "root";       // Change if necessary
$passwordDB = "";           // Change if necessary
$dbname = "permission_request_system";

// Create connection
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user details from the database
$sql = "SELECT id, group_id, internal_desig_id FROM emp WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $group_id = $row['group_id'];
    $internal_desig_id = $row['internal_desig_id'];
} else {
    // Handle case where user details are not found
    header('Location: login.php');
    exit;
}

$stmt->close();

// Determine user role
if ($internal_desig_id == 1) {
    $role = 'director';
} else {
    // Check group_info table for group_head_id and ad_id
    $sql = "SELECT group_head_id, ad_id FROM group_info WHERE group_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $group_head_id = $row['group_head_id'];
        $ad_id = $row['ad_id'];
    } else {
        // Handle case where group info is not found
        $role = 'scientist';
    }

    $stmt->close();

    // Determine role based on group_id and user id
    if ($group_id == 2 && $id == $group_head_id) {
        $role = 'tcp_hr_head';
    } elseif ($group_id == 2 && $id == $ad_id) {
        $role = 'tcp_hr_ad';
    } elseif ($id == $group_head_id) {
        $role = 'group_head';
    } elseif ($id == $ad_id) {
        $role = 'ad';
    } else {
        $role = 'scientist';
    }
}

// Fetch available training forms where the last date of submission is greater than or equal to today's date
$currentDate = date('Y-m-d');
$sql = "SELECT id, seminar_id, name_of_seminar, from_date, to_date, location, last_date_of_submission 
        FROM training_forms 
        WHERE last_date_of_submission >= ? 
        AND id NOT IN (SELECT training_id FROM user_applications WHERE username = ?) 
        ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $currentDate, $username);
$stmt->execute();
$trainings = $stmt->get_result();

// Fetch all training forms for the "View All" page
$sql_all = "SELECT id, seminar_id, name_of_seminar, from_date, to_date, location, last_date_of_submission 
            FROM training_forms 
            WHERE last_date_of_submission >= ? 
            AND id NOT IN (SELECT training_id FROM user_applications WHERE username = ?) 
            ORDER BY id DESC";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("ss", $currentDate, $username);
$stmt_all->execute();
$all_trainings = $stmt_all->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard_styles.css">
</head>
<body>
<div class="header">
    <img src="drdo_logo_0.png" alt="DRDO Logo">
    <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
    <img src="drdo_logo_0.png" alt="DRDO Logo">
</div>
<div class="navbar">
 <h2>Welcome!</h2>
    <div class="logout">
        <button onclick="navigateTo('logout.php')">Logout</button>
    </div>
</div>
<h1>Dashboard</h1>
<div class="container">
<?php if ($role === 'director') : ?>
    <div class="dir-card-container">
        <div id="inbox-card" class="dir-card">
            <button onclick="navigateTo('inbox1.php')">Inbox</button>
        </div>
    </div>
    
<?php else : ?>
    <div class="card-container">
        <!-- <div id="new-app-card" class="card">
            <button onclick="navigateTo('new_application.php')">Apply for new Application</button>
        </div> -->
        <?php if ($role === 'tcp_hr_ad' || $role === 'tcp_hr_head') : ?>
             <div id="inbox-card" class="card">
                <button onclick="navigateTo('inbox2.php')">Inbox</button>
            </div>
        <?php else : ?>
             <div id="inbox-card" class="card">
                <button onclick="navigateTo('inbox1.php')">Inbox</button>
             </div>
        <?php endif; ?>
        <div id="track-app-card" class="card">
            <button onclick="navigateTo('pre_status.php')">Submitted Applications</button>
        </div>
        <div id="track-app-card" class="card">
            <button onclick="navigateTo('view_feedback.php')">View Feedback Forms</button>
        </div>
    </div>
    <?php if ($role === 'tcp_hr_ad' || $role === 'tcp_hr_head') : ?>
        <br>
        <div class="card-container">
            <button id="new-app-card" class="card" onclick="navigateTo('upload_training.php')">Upload New Training Event</button>
            <button id="new-app-card" class="card" onclick="navigateTo('changes_training.php')">Manage Training Events</button>
            <!-- <button id="new-app-card" class="card" onclick="navigateTo('inbox2.php')">Manage applications</button> -->
            <button id="new-app-card" class="card" onclick="navigateTo('report.php')">Generate report</button>
        </div>
    <?php else : ?>
    <?php endif; ?>
    <?php if ($role === 'director') : ?>
    <?php else : ?>
        <div class="training-list">
        <br>
        <h2>Current Training Events Available</h2>
            <?php if ($trainings->num_rows > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Training Program ID</th>
                            <th>Name of Training Program</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Venue</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        while ($training = $trainings->fetch_assoc()) : 
                            if ($count >= 3) break;
                            $count++;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($training['seminar_id']); ?></td>
                                <td><?php echo htmlspecialchars($training['name_of_seminar']); ?></td>
                                <td><?php echo htmlspecialchars($training['from_date']); ?></td>
                                <td><?php echo htmlspecialchars($training['to_date']); ?></td>
                                <td><?php echo htmlspecialchars($training['location']); ?></td>
                                <td>
                                    <button onclick="navigateTo('direct_application.php?training_id=<?php echo $training['id']; ?>')">Apply</button>
                                    <button onclick="viewFullApplication(<?php echo $training['id']; ?>)">View Training Event Details</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if ($all_trainings->num_rows > 4) : ?>
                    <div class="vall">
                        <button onclick="navigateTo('view_all_trainings.php')">View All</button>
                    </div>
                    
                <?php endif; ?>
            <?php else : ?>
                <div class="notf">
                    <p>No training forms available at the moment.</p>
                </div>
            <?php endif; ?>
            <h2>Past Training Events </h2>
            <div class="vpt">
                <button onclick="navigateTo('view_past_trainings.php')">View Past Training Forms</button>
            </div>
            
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }

    function viewFullApplication(trainingId) {
        window.location.href = 'view_full_application.php?training_id=' + trainingId;
    }

    function deleteTraining(trainingId) {
        if (confirm('Are you sure you want to delete this training form?')) {
            window.location.href = 'delete_training.php?training_id=' + trainingId;
        }
    }
</script>
</body>
</html>
