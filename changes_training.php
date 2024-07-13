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
$sql_current = "SELECT id, seminar_id, name_of_seminar, from_date, to_date, location, last_date_of_submission 
                FROM training_forms 
                WHERE last_date_of_submission >= ? 
                ORDER BY id DESC";
$stmt_current = $conn->prepare($sql_current);
if (!$stmt_current) {
    die("Prepare failed: " . $conn->error);
}
$stmt_current->bind_param("s", $currentDate);
$stmt_current->execute();
$current_trainings = $stmt_current->get_result();

// Fetch past training forms where the last date of submission is earlier than today's date
$sql_past = "SELECT id, seminar_id, name_of_seminar, from_date, to_date, location, last_date_of_submission 
             FROM training_forms 
             WHERE last_date_of_submission < ? 
             ORDER BY id DESC";
$stmt_past = $conn->prepare($sql_past);
if (!$stmt_past) {
    die("Prepare failed: " . $conn->error);
}
$stmt_past->bind_param("s", $currentDate);
$stmt_past->execute();
$past_trainings = $stmt_past->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <script src="dashboard_script.js" defer></script>
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
<div class="container">
    <div class="training-list">
    <h2>Latest Training Event Forms</h2>
        <?php if ($current_trainings->num_rows > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Training Program ID</th>
                        <th>Name of Training Program</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Venue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($training = $current_trainings->fetch_assoc()) :
                        $seminar_id = $training['seminar_id'];

                        // Check if any application for this seminar_id has director_status = 1
                        $application_query = "SELECT director_status FROM applications WHERE seminar_id = ?";
                        $stmt_application = $conn->prepare($application_query);
                        if (!$stmt_application) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt_application->bind_param("s", $seminar_id);
                        $stmt_application->execute();
                        $application_result = $stmt_application->get_result();

                        $disable_buttons = false;
                        if ($application_result) {
                            while ($application = $application_result->fetch_assoc()) {
                                if ($application['director_status'] == 1) {
                                    $disable_buttons = true;
                                    break;
                                }
                            }
                            $application_result->free(); // Free the result set
                        } else {
                            echo "Error retrieving applications: " . $conn->error;
                        }
                        $stmt_application->close();
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($training['seminar_id']); ?></td>
                            <td><?php echo htmlspecialchars($training['name_of_seminar']); ?></td>
                            <td><?php echo htmlspecialchars($training['from_date']); ?></td>
                            <td><?php echo htmlspecialchars($training['to_date']); ?></td>
                            <td><?php echo htmlspecialchars($training['location']); ?></td>
                            <td>
                                <button 
                                    onclick="navigateTo('edit_training.php?training_id=<?php echo $training['id']; ?>')" 
                                    <?php if ($disable_buttons) echo 'disabled'; ?>
                                >
                                    Edit
                                </button>
                                <button 
                                    onclick="deleteTraining(<?php echo $training['id']; ?>)" 
                                    <?php if ($disable_buttons) echo 'disabled'; ?>
                                >
                                    Delete
                                </button>
                                <button onclick="viewFullApplication(<?php echo $training['id']; ?>)">
                                    View Training Program Details
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No current training forms available at the moment.</p>
        <?php endif; ?>
        <h2>Past Training Event Forms</h2>
        <?php if ($past_trainings->num_rows > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Training Program ID</th>
                        <th>Name of Training Program</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Venue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($training = $past_trainings->fetch_assoc()) : 
                    $seminar_id = $training['seminar_id'];

                    // Check if any application for this seminar_id has director_status = 1
                    $application_query = "SELECT director_status FROM applications WHERE seminar_id = ?";
                    $stmt_application = $conn->prepare($application_query);
                    if (!$stmt_application) {
                        die("Prepare failed: " . $conn->error);
                    }
                    $stmt_application->bind_param("s", $seminar_id);
                    $stmt_application->execute();
                    $application_result = $stmt_application->get_result();

                    $disable_buttons = false;
                    if ($application_result) {
                        while ($application = $application_result->fetch_assoc()) {
                            if ($application['director_status'] == 1) {
                                $disable_buttons = true;
                                break;
                            }
                        }
                        $application_result->free(); // Free the result set
                    } else {
                        echo "Error retrieving applications: " . $conn->error;
                    }
                    $stmt_application->close();
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($training['seminar_id']); ?></td>
                        <td><?php echo htmlspecialchars($training['name_of_seminar']); ?></td>
                        <td><?php echo htmlspecialchars($training['from_date']); ?></td>
                        <td><?php echo htmlspecialchars($training['to_date']); ?></td>
                        <td><?php echo htmlspecialchars($training['location']); ?></td>
                        <td>
                            <button onclick="viewFullApplication(<?php echo $training['id']; ?>)">
                                View Training Program Details
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No past training forms available at the moment.</p>
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

    function deleteTraining(trainingId) {
        if (confirm('Are you sure you want to delete this training form?')) {
            window.location.href = 'delete_training.php?training_id=' + trainingId;
        }
    }
</script>
</body>
</html>

<?php
$stmt_current->close();
$stmt_past->close();
$conn->close();
?>
