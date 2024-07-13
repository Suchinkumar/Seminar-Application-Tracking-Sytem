<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

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
// $username = $_SESSION['username'];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seminar_id = $_POST['seminar_id'];
    $name_of_applicant = $_POST['name_of_applicant'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $duration = $_POST['duration'];
    $name_of_seminar = $_POST['name_of_seminar'];
    $place = $_POST['place'];

    $sql = "INSERT INTO applications (name_of_applicant, from_date, to_date, duration, name_of_seminar, place, seminar_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name_of_applicant, $from_date, $to_date, $duration, $name_of_seminar, $place, $seminar_id);

    if ($stmt->execute()) {
        header('Location: new_application.php');
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Fetch training details
$training_id = $_GET['training_id'];
$sql = "SELECT seminar_id, name_of_seminar, from_date, to_date, place,last_date_of_submission,duration FROM training_forms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $training = $result->fetch_assoc();
} else {
    die("Training form not found.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Training</title>
    <!-- Link to CSS for styling the form -->
    <link rel="stylesheet" href="form_styles.css">
</head>
<body>
<div class="header">
    <img src="drdo_logo_0.png" alt="DRDO Logo">
    <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
    <img src="drdo_logo_0.png" alt="DRDO Logo">
</div>
<div class="navbar">
    <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
    <div class="logout">
        <button onclick="navigateTo('logout.php')">Logout</button>
    </div>
</div>
<h1>Apply for Training</h1>
<div class="container">
    <form action="" method="POST">
        <input type="hidden" name="seminar_id" value="<?php echo htmlspecialchars($training['seminar_id']); ?>">
        <div class="form-group">
            <label for="name_of_applicant">Username:</label>
            <input type="text" id="name_of_applicant" name="name_of_applicant" required>
        </div>
        <div class="form-group">
            <label for="from_date">From Date:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($training['from_date']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="to_date">To Date:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($training['to_date']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="duration">Duration:</label>
            <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($training['duration']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="name_of_seminar">Name of Seminar:</label>
            <input type="text" id="name_of_seminar" name="name_of_seminar" value="<?php echo htmlspecialchars($training['name_of_seminar']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="place">Place:</label>
            <input type="text" id="place" name="place" value="<?php echo htmlspecialchars($training['place']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="last_date_of_submission">Last date of submission:</label>
            <input type="text" id="last_date_of_submission" name="last_date_of_submission" value="<?php echo htmlspecialchars($training['last_date_of_submission']); ?>" readonly>
        </div>
        <div class="form-group">
            <button type="submit">Submit Application</button>
        </div>
    </form>
</div>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }
</script>
</body>
</html>
