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

// Fetch available training forms where the last date of submission is greater than or equal to today's date
$currentDate = date('Y-m-d');
$sql = "SELECT id, seminar_id, name_of_seminar, from_date, to_date, location ,last_date_of_submission FROM training_forms 
        WHERE last_date_of_submission < ? AND id NOT IN (SELECT training_id FROM user_applications WHERE username = ?) ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $currentDate, $username);
$stmt->execute();
$trainings = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Training Forms</title>
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
<?php include 'home_button.php'; ?>
<br>
<h1>Past Training Event Forms</h1>
<div class="container">
    <div class="training-list">
        <?php if ($trainings->num_rows > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Training Program ID</th>
                        <th>Name of Training program</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Venue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($training = $trainings->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($training['seminar_id']); ?></td>
                            <td><?php echo htmlspecialchars($training['name_of_seminar']); ?></td>
                            <td><?php echo htmlspecialchars($training['from_date']); ?></td>
                            <td><?php echo htmlspecialchars($training['to_date']); ?></td>
                            <td><?php echo htmlspecialchars($training['location']); ?></td>
                            <td>
                                <button onclick="viewFullApplication(<?php echo $training['id']; ?>)">View Training Event Details</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No training forms available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }

    function viewFullApplication(trainingId) {
        window.location.href = 'view_full_application.php?training_id=' + trainingId;
    }
</script>
</body>
</html>
