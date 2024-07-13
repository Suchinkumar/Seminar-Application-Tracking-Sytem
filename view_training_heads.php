<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$servername = "localhost";
$usernameDB = "root";
$passwordDB = "";
$dbname = "permission_request_system";

// Create connection
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if seminar_id parameter is set
if (!isset($_GET['seminar_id'])) {
    echo "Error: Training event ID parameter is missing.";
    exit;
}

$seminar_id = $_GET['seminar_id'];

// Fetch training form details based on seminar_id
$sql = "SELECT * FROM training_forms WHERE seminar_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $seminar_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $training = $result->fetch_assoc();
} else {
    echo "Error: Training form not found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Full Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #002147;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header img {
            height: 55px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
            color: #f4f4f4;
        }
        .navbar {
            color: black;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h2 {
            margin: 0;
        }
        .navbar .logout button {
            background-color: #ff3333;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .navbar .logout button:hover {
            background-color: #cc0000;
        }
        .container {
            padding: 20px;
            background-color: white;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        h1 {
            text-align: center;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .details th {
            background-color: #f2f2f2;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button-container button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            border-radius: 5px;
        }
        .button-container button:hover {
            background-color: #00264d;
        }
        a {
            color: #004080;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="header">
    <img src="drdo_logo_0.png" alt="DRDO Logo">
    <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
    <img src="drdo_logo_0.png" alt="DRDO Logo">
</div>
<div class="navbar">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <div class="logout">
        <button onclick="navigateTo('logout.php')">Logout</button>
    </div>
</div>
<h1>Training Form Details</h1>
<div class="container">
    <div class="details">
        <table>
            <tr>
                <th>Field</th>
                <th>Details</th>
            </tr>
            <tr>
                <td><strong>Training program ID:</strong></td>
                <td><?php echo htmlspecialchars($training['seminar_id']); ?></td>
            </tr>
            <tr>
                <td><strong>From Date:</strong></td>
                <td><?php echo htmlspecialchars($training['from_date']); ?></td>
            </tr>
            <tr>
                <td><strong>To Date:</strong></td>
                <td><?php echo htmlspecialchars($training['to_date']); ?></td>
            </tr>
            <tr>
                <td><strong>Duration (days):</strong></td>
                <td><?php echo htmlspecialchars($training['duration']); ?></td>
            </tr>
            <tr>
                <td><strong>Name of Training Program:</strong></td>
                <td><?php echo htmlspecialchars($training['name_of_seminar']); ?></td>
            </tr>
            <tr>
                <td><strong>Type of Training Program:</strong></td>
                <td><?php echo htmlspecialchars($training['type_of_event']); ?></td>
            </tr>
            <tr>
                <td><strong>Place:</strong></td>
                <td><?php echo htmlspecialchars($training['place']); ?></td>
            </tr>
            <tr>
                <td><strong>Last Date of Submission:</strong></td>
                <td><?php echo htmlspecialchars($training['last_date_of_submission']); ?></td>
            </tr>
            <tr>
                <td><strong>Eligibility Criteria:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($training['eligibility_criteria'])); ?></td>
            </tr>
            <?php if (!empty($training['remarks'])) : ?>
                <tr>
                    <td><strong>Remarks:</strong></td>
                    <td><?php echo nl2br(htmlspecialchars($training['remarks'])); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($training['notice'])) : ?>
                <tr>
                    <td><strong>Attached Notice:</strong></td>
                    <td><a href="download_notice_head.php?seminar_id=<?php echo urlencode($training['seminar_id']); ?>">Download Notice</a>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="button-container">
        <button onclick="navigateTo('dashboard.php')">Back to Dashboard</button>
    </div>
</div>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }
</script>
</body>
</html>
