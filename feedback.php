<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'permission_request_system');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM applications WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement for applications: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    if ($application) {
        $feedback_submitted = $application['feedback_submitted'];
        $username = htmlspecialchars($application['name_of_applicant'], ENT_QUOTES, 'UTF-8');
        $seminar_id = urlencode($application['seminar_id']);  // URL encode seminar_id
        
        if ($feedback_submitted == 1) {
            header("Location: user_print_feedback.php?submitted_by=$username&seminar_id=$seminar_id");
            exit;
        }

        $sql_emp = "SELECT first_name, group_id FROM emp WHERE username=?";
        $stmt_emp = $conn->prepare($sql_emp);
        if (!$stmt_emp) {
            die("Error preparing statement for emp: " . $conn->error);
        }
        $stmt_emp->bind_param("s", $username);
        $stmt_emp->execute();
        $result_emp = $stmt_emp->get_result();
        $employee = $result_emp->fetch_assoc();

        if ($employee) {
            $first_name = htmlspecialchars($employee['first_name'], ENT_QUOTES, 'UTF-8');
            $group_id = htmlspecialchars($employee['group_id'], ENT_QUOTES, 'UTF-8');
        } else {
            echo "Employee details not found.<br>";
        }
    } else {
        die("Application not found or feedback already submitted.");
    }
} else {
    die("Invalid application ID.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $designation = htmlspecialchars($_POST['designation'], ENT_QUOTES, 'UTF-8');
    $user_group = htmlspecialchars($_POST['user_group'], ENT_QUOTES, 'UTF-8');
    $programme_type = htmlspecialchars($_POST['programme_type'], ENT_QUOTES, 'UTF-8');
    $programme_title = htmlspecialchars($_POST['programme_title'], ENT_QUOTES, 'UTF-8');
    $no_of_days = (int)$_POST['no_of_days'];
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8');
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $training_fee = (float)($_POST['training_fee'] ?? 0.0);
    $overview = htmlspecialchars($_POST['overview'], ENT_QUOTES, 'UTF-8');
    $seminar_id = urldecode($_POST['seminar_id'] ?? '');  // URL decode seminar_id
    $submitted_by = $username;

    // Satisfaction ratings
    $effectiveness = (int)$_POST['effectiveness'];
    $programme = (int)$_POST['programme'];
    $conduct = (int)$_POST['conduct'];
    $instructor = (int)$_POST['instructor'];
    $aids = (int)$_POST['aids'];

    $stmt = $conn->prepare("INSERT INTO feedback (designation, user_group, programme_type, programme_title, no_of_days, from_date, to_date, training_fee, overview, location, submitted_by, seminar_id, effectiveness, programme, conduct, instructor, aids) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error preparing statement for feedback: " . $conn->error);
    }
    $stmt->bind_param("ssssissssssssiiii", $designation, $user_group, $programme_type, $programme_title, $no_of_days, $from_date, $to_date, $training_fee, $overview, $location, $submitted_by, $seminar_id, $effectiveness, $programme, $conduct, $instructor, $aids);

    if ($stmt->execute()) {
        // Update feedback_submitted column to 1
        $stmt_update = $conn->prepare("UPDATE applications SET feedback_submitted=1 WHERE id=?");
        if (!$stmt_update) {
            die("Error preparing statement for applications update: " . $conn->error);
        }
        $stmt_update->bind_param("i", $id);
        if ($stmt_update->execute()) {
            echo "Feedback submitted successfully!";
            header("Location: pre_status.php");
            exit;
        } else {
            echo "Error executing statement for applications update: " . $stmt_update->error;
        }
    } else {
        echo "Error executing statement for feedback: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Report</title>
    <link rel="stylesheet" href="feedbackstyle.css">
</head>
<body>
    <div class="header">
        <img src="drdo_logo_0.png" alt="DRDO Logo">
        <h1>Centre for Fire, Explosive and Environment Safety (CFEES)</h1>
        <img src="drdo_logo_0.png" alt="DRDO Logo">
    </div>
    <div class="navbar">
        <h2>Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!</h2>
        <div class="logout">
            <button onclick="navigateTo('dashboard.php/logout.php')">Logout</button>
        </div>
    </div>
    <div class="container">
        <?php include 'home_button.php'; ?>
        <h2 class="fr">Feedback Report</h2>
        <form action="feedback.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label for="designation">Designation:</label>
                <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($application['designation'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="user_group">Group:</label>
                <input type="text" id="user_group" name="user_group" value="<?php echo $group_id; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($application['location'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="programme_type">Type Of Training Program:</label>
                <input type="text" id="programme_type" name="programme_type" value="<?php echo htmlspecialchars($application['type_of_event'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="seminar_id">Training Program ID:</label>
                <input type="text" id="seminar_id" name="seminar_id" value="<?php echo htmlspecialchars(urldecode($seminar_id), ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="programme_title">Complete Title of the Programme:</label>
                <input type="text" id="programme_title" name="programme_title" value="<?php echo htmlspecialchars($application['name_of_seminar'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="no_of_days">No. of Days:</label>
                <input type="number" id="no_of_days" name="no_of_days" value="<?php echo $application['duration']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" value="<?php echo $application['from_date']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" value="<?php echo $application['to_date']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="training_fee">Training Fee (if any):</label>
                <input type="number" step="0.01" id="training_fee" name="training_fee" value="<?php echo $application['is_paid']; ?>">
            </div>
            <div class="form-group">
                <label for="overview">Overview of the Programme:</label>
                <textarea id="overview" name="overview" required></textarea>
            </div>
            <h3>Feedback</h3>
            <table class="feedback-table">
                <tr>
                    <th>Quality Aspect</th>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                </tr>
                <tr>
                    <td>Effectiveness of Training Module</td>
                    <td><input type="radio" name="effectiveness" value="1" required></td>
                    <td><input type="radio" name="effectiveness" value="2"></td>
                    <td><input type="radio" name="effectiveness" value="3"></td>
                    <td><input type="radio" name="effectiveness" value="4"></td>
                    <td><input type="radio" name="effectiveness" value="5"></td>
                </tr>
                <tr>
                    <td>Training Programme</td>
                    <td><input type="radio" name="programme" value="1" required></td>
                    <td><input type="radio" name="programme" value="2"></td>
                    <td><input type="radio" name="programme" value="3"></td>
                    <td><input type="radio" name="programme" value="4"></td>
                    <td><input type="radio" name="programme" value="5"></td>
                </tr>
                <tr>
                    <td>Mode of conduct of Programme</td>
                    <td><input type="radio" name="conduct" value="1" required></td>
                    <td><input type="radio" name="conduct" value="2"></td>
                    <td><input type="radio" name="conduct" value="3"></td>
                    <td><input type="radio" name="conduct" value="4"></td>
                    <td><input type="radio" name="conduct" value="5"></td>
                </tr>
                <tr>
                    <td>Interaction of Instructors</td>
                    <td><input type="radio" name="instructor" value="1" required></td>
                    <td><input type="radio" name="instructor" value="2"></td>
                    <td><input type="radio" name="instructor" value="3"></td>
                    <td><input type="radio" name="instructor" value="4"></td>
                    <td><input type="radio" name="instructor" value="5"></td>
                </tr>
                <tr>
                    <td>Training Aids</td>
                    <td><input type="radio" name="aids" value="1" required></td>
                    <td><input type="radio" name="aids" value="2"></td>
                    <td><input type="radio" name="aids" value="3"></td>
                    <td><input type="radio" name="aids" value="4"></td>
                    <td><input type="radio" name="aids" value="5"></td>
                </tr>
            </table>
            <button type="submit" class="submit">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
