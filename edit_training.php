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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['training_id'])) {
    $trainingId = $_GET['training_id'];

    // Fetch the training form data
    $sql = "SELECT * FROM training_forms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trainingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $training = $result->fetch_assoc();
    } else {
        echo "Training form not found.";
        exit;
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update the training form data
    $trainingId = $_POST['training_id'];
    $seminar_id = $_POST['seminar_id'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $duration = $_POST['duration'];
    $name_of_seminar = $_POST['name_of_seminar'];
    $place = $_POST['place'];
    $last_date_of_submission = $_POST['last_date_of_submission'];
    $eligibility_criteria = $_POST['eligibility_criteria'];
    $remarks = $_POST['remarks'];
    $type_of_event = $_POST['type_of_event'];
    $location= $_POST['location'];

    // Handle file upload
    $filePath = NULL;
    if (isset($_FILES['notice']) && $_FILES['notice']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['notice']['tmp_name'];
        $fileName = $_FILES['notice']['name'];
        $uploadDir = 'C:/xampp/htdocs/project_1/training_event_proof/';
        $destPath = $uploadDir . time() . '_' . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $filePath = $destPath;
        } else {
            echo "Error moving the uploaded file.";
            exit;
        }
    }

    $sql = "UPDATE training_forms SET seminar_id = ?, from_date = ?, to_date = ?, duration = ?, name_of_seminar = ?, place = ?, last_date_of_submission = ?, eligibility_criteria = ?, remarks = ?, type_of_event = ?, location = ?";
    if ($filePath) {
        $sql .= ", notice = ?";
    }
    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($filePath) {
        $stmt->bind_param("sssissssssssi", $seminar_id, $from_date, $to_date, $duration, $name_of_seminar, $place, $last_date_of_submission, $eligibility_criteria, $remarks, $type_of_event, $location, $filePath, $trainingId);
    } else {
        $stmt->bind_param("sssisssssssi", $seminar_id, $from_date, $to_date, $duration, $name_of_seminar, $place, $last_date_of_submission, $eligibility_criteria, $remarks, $type_of_event, $location, $trainingId);
    }

    if ($stmt->execute()) {
        echo "<script>
            alert('Training Event Form Updated Successfully. Seminar ID: $seminar_id');
            window.location.href = 'dashboard.php';
        </script>";
        exit; // Ensure no further code is executed after redirect
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Training Form</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var date = new Date();
            date.setDate(date.getDate() - 15);
            var minDate = date.toISOString().split('T')[0];
            document.getElementById('from_date').min = minDate;
        });

        function updateDuration() {
            const fromDate = new Date(document.getElementById('from_date').value);
            const toDate = new Date(document.getElementById('to_date').value);
            if (toDate >= fromDate) {
                const duration = (toDate - fromDate) / (1000 * 60 * 60 * 24);
                document.getElementById('duration').value = duration + 1;  // Adding 1 to include both start and end dates
            } else {
                alert('To Date should be greater than or equal to From Date.');
                document.getElementById('to_date').value = '';
                document.getElementById('duration').value = '';
            }
            document.getElementById('last_date_of_submission').max = document.getElementById('to_date').value;
        }
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .header {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
        }
        .header img {
            width: 50px;
            vertical-align: middle;
        }
        .header h1 {
            display: inline;
            margin-left: 10px;
        }
        .container {
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            width: 100%;
        }
        .form-group button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
<?php include 'home_button.php'; ?>
<br>
    <h1>Edit Training Form</h1>
    <form action="edit_training.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="training_id" value="<?php echo htmlspecialchars($training['id']); ?>">
        <div class="form-group">
            <label for="seminar_id">Training Program ID:</label>
            <input type="text" id="seminar_id" name="seminar_id" value="<?php echo htmlspecialchars($training['seminar_id']); ?>" readonly>
        </div>
        <div class="form-group">
                <label for="type_of_event">Type of Training Program:</label>
                <input type="text" id="type_of_event" name="type_of_event" value="<?php echo htmlspecialchars($training['type_of_event']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="from_date">From Date:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($training['from_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="to_date">To Date:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($training['to_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="duration">Duration (days):</label>
            <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($training['duration']); ?>" required readonly>
        </div>
        <div class="form-group">
            <label for="name_of_seminar">Name of Training Program:</label>
            <input type="text" id="name_of_seminar" name="name_of_seminar" value="<?php echo htmlspecialchars($training['name_of_seminar']); ?>" required>
        </div>
        <div class="form-group">
            <label for="place">Place:</label>
            <input type="text" id="place" name="place" value="<?php echo htmlspecialchars($training['place']); ?>" required>
        </div>
        <div class="form-group">
            <label for="location">Location / Venue:</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($training['location']); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_date_of_submission">Last Date of Submission:</label>
            <input type="date" id="last_date_of_submission" name="last_date_of_submission" value="<?php echo htmlspecialchars($training['last_date_of_submission']); ?>" required>
        </div>
        <div class="form-group">
            <label for="eligibility_criteria">Eligibility Criteria:</label>
            <textarea id="eligibility_criteria" name="eligibility_criteria" rows="4" required><?php echo htmlspecialchars($training['eligibility_criteria']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks" rows="4"><?php echo htmlspecialchars($training['remarks']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="notice">Change Notice(If not leave blank):</label>
            <input type="file" id="notice" name="notice">
        </div>
        <div class="form-group">
            <button type="submit">Update Form</button>
        </div>
    </form>
</div>
</body>
</html>
