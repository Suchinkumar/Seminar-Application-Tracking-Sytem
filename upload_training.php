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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $type_of_event = $_POST['type_of_event'];
    if ($type_of_event === 'Others' && !empty($_POST['other_event'])) {
        $type_of_event = 'Others/' . $_POST['other_event'];
    }
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $duration = (new DateTime($to_date))->diff(new DateTime($from_date))->days + 1; // Duration in days
    $name_of_seminar = $_POST['name_of_seminar'];
    $place = $_POST['place'];
    $location = $_POST['location'];
    $last_date_of_submission = $_POST['last_date_of_submission'];
    $eligibility_criteria = $_POST['eligibility_criteria'];
    $remarks = $_POST['remarks'];

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

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO training_forms (type_of_event, from_date, to_date, duration, name_of_seminar, place, location, last_date_of_submission, eligibility_criteria, remarks, notice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssssss", $type_of_event, $from_date, $to_date, $duration, $name_of_seminar, $place, $location, $last_date_of_submission, $eligibility_criteria, $remarks, $filePath);

    if ($stmt->execute()) {
        // Retrieve the auto-generated ID
        $last_id = $stmt->insert_id;

        // Generate the seminar ID
        $year = date('Y');
        $month = date('m');
        $seminar_id = "CFEES/TCP&HR/$type_of_event/$year/$month/$last_id";

        // Update the row with the seminar ID
        $update_stmt = $conn->prepare("UPDATE training_forms SET seminar_id = ? WHERE id = ?");
        $update_stmt->bind_param("si", $seminar_id, $last_id);
        $update_stmt->execute();
        $update_stmt->close();

        echo "<script>
        alert('Training Event Form Uploaded Successfully. Training Form ID is : $seminar_id');
        window.location.href = 'changes_training.php';
    </script>";
    exit;  // Ensure no further code is executed after redirect
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Upload Training Form</title>
    <style>
        /* Global styles */
        h1 {
            text-align: center;
        }
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1em;
            border-radius: 5px;
        }
        .form-group button:hover {
            background-color: #00264d;
        }
    </style>
    <script>
        function checkEventType() {
            var eventType = document.getElementById("type_of_event").value;
            var otherEventContainer = document.getElementById("other_event_container");
            var otherEventInput = document.getElementById("other_event");

            if (eventType === "Others") {
                otherEventContainer.style.display = "block";
                otherEventInput.required = true;
            } else {
                otherEventContainer.style.display = "none";
                otherEventInput.required = false;
                otherEventInput.value = "";  // Clear the input field
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var date = new Date();
            date.setDate(date.getDate() - 15);
            var minDate = date.toISOString().split('T')[0];
            document.getElementById('from_date').min = minDate;
        });

        function navigateTo(url) {
            window.location.href = url;
        }

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
    <h1>Upload Training Program Form</h1>
    <div class="container">
        <form action="upload_training.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="type_of_event">Type of Training Program:</label>
                <select id="type_of_event" name="type_of_event" onchange="checkEventType()">
                    <option value="Conference">Conference</option>
                    <option value="Seminar">Seminar</option>
                    <option value="CEP">CEP</option>
                    <option value="Symposium">Symposium</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Webinar">Webinar</option>
                    <option value="Exhibition">Exhibition</option>
                    <option value="MQP">MQP</option>
                    <option value="Certification Course">Certification Course</option>
                    <option value="Course">Course</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group" id="other_event_container" style="display: none;">
                <label for="other_event">Please specify:</label>
                <input type="text" id="other_event" name="other_event">
            </div>
            <div class="form-group">
                <label for="name_of_seminar">Name of Training Program:</label>
                <input type="text" id="name_of_seminar" name="name_of_seminar" required>
            </div>
            <div class="form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" required min="">
            </div>
            <div class="form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" required onchange="updateDuration()">
            </div>
            <div class="form-group">
                <label for="duration">Duration (days):</label>
                <input type="number" id="duration" name="duration" readonly>
            </div>
            <div class="form-group">
                <label for="place">Place:</label>
                <input type="text" id="place" name="place" required>
            </div>
            <div class="form-group">
                <label for="location">Location / Venue:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="last_date_of_submission">Last Date of Submission:</label>
                <input type="date" id="last_date_of_submission" name="last_date_of_submission" required onchange="updateDuration()">
            </div>
            <div class="form-group">
                <label for="eligibility_criteria">Eligibility Criteria:</label>
                <textarea id="eligibility_criteria" name="eligibility_criteria" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="remarks">Remarks:</label>
                <textarea id="remarks" name="remarks" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="notice">Upload Notice (Word/PDF):</label>
                <input type="file" id="notice" name="notice" accept=".pdf,.doc,.docx">
            </div>
            <div class="form-group">
                <button type="submit">Upload Form</button>
            </div>
        </form>
    </div>
</body>
</html>
