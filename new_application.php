<?php
session_start();
$error_message = '';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "permission_request_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee data
$sql_emp = "SELECT * FROM emp WHERE username = ?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("s", $username);
$stmt_emp->execute();
$result_emp = $stmt_emp->get_result();
$emp_data = $result_emp->fetch_assoc();
$stmt_emp->close();

$designation = $emp_data['internal_desig_id'] ?? '';
$designation_title = '';
switch ($designation) {
    case 1:
        $designation_title = 'Director';
        break;
    case 2:
        $designation_title = 'AD';
        break;
    case 3:
        $designation_title = 'Head';
        break;
    case 4:
        $designation_title = 'Scientist';
        break;
    default:
        $designation_title = 'Unknown Designation';
        break;
}

//fetch fax number and date of joining present designation
$sql_applications = "SELECT * FROM applications1 WHERE name_of_applicant=?";
$stmt_applications = $conn->prepare($sql_applications);
$stmt_applications->bind_param("s", $username);
$stmt_applications->execute();
$result_applications = $stmt_applications->get_result();
$application_data = $result_applications->fetch_assoc();

// View training details of the database
$sql_training = "SELECT * FROM training_details WHERE username=? ORDER BY id DESC LIMIT 5";
$stmt_training = $conn->prepare($sql_training);
$stmt_training->bind_param("s", $username);
$stmt_training->execute();
$result_training = $stmt_training->get_result();



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert application details into applications1 table
    $designation = htmlspecialchars($_POST['designation'] ?? '');
    $fax_no = $_POST['fax_no'];
    $date_of_joining = $_POST['date_of_joining'];

    if ($application_data) {
        // Update existing record
        $sql_update = "UPDATE applications1 SET fax_no=?, date_of_joining=? WHERE  name_of_applicant=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sss", $fax_no, $date_of_joining, $username);
        $stmt_update->execute();
    } else {
        // Insert new record
        $sql_insert = "INSERT INTO applications1 ( name_of_applicant, fax_no, date_of_joining) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $username, $fax_no, $date_of_joining);
        $stmt_insert->execute();
    }
    // $stmt->close();

    // Insert application details into applications table
    $name_of_applicant = htmlspecialchars($_POST['name_of_applicant'] ?? '');
    $qualification = htmlspecialchars($_POST['qualification'] ?? '');
    $from_date = htmlspecialchars($_POST['from_date'] ?? '');
    $to_date = htmlspecialchars($_POST['to_date'] ?? '');
    $duration = htmlspecialchars($_POST['duration'] ?? '');
    $name_of_seminar = htmlspecialchars($_POST['name_of_seminar'] ?? '');
    $place = htmlspecialchars($_POST['place'] ?? '');
    $location = htmlspecialchars($_POST['location'] ?? '');
    $last_date_of_submission = htmlspecialchars($_POST['last_date_of_submission'] ?? '');
    $last_conference_attended = htmlspecialchars($_POST['last_conference_attended'] ?? '');
    $name_of_group_head = htmlspecialchars($_POST['name_of_group_head'] ?? '');
    $type_of_event = htmlspecialchars($_POST['type_of_event'] ?? '');

    $sql = "INSERT INTO applications (name_of_applicant, designation, qualification, from_date, to_date, duration, name_of_seminar, place,location, last_date_of_submission, last_conference_attended, name_of_group_head, submission_date, type_of_event) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", $name_of_applicant, $designation, $qualification, $from_date, $to_date, $duration, $name_of_seminar, $place, $location, $last_date_of_submission, $last_conference_attended, $name_of_group_head, $type_of_event);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        if ($name_of_group_head === "NIL") {
            $update_sql = "UPDATE applications SET group_head_status = 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $last_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        echo "Application submitted successfully!";
        header("Location: print_application.php?id=$last_id");
        exit;
    } else {
        $error_message .= $stmt->error . "<br>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Part 1</title>
    <link rel="stylesheet" href="new_application.css">
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
            background-color: #f0f8ff;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #1e88e5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #1a237e;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #b3e5fc;
            border-radius: 4px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #1e88e5;
            outline: none;
        }
        .add-btn,
        .remove-btn {
            margin-top: 10px;
            padding: 10px 20px;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
    }
    .add-btn {
        background-color: #0d2163;
    }
    .remove-btn {
        background-color: #0d2163;
    }
    .viewpast {
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 10px 20px;
        border: none;
        color: white;
        cursor: pointer;
        border-radius: 5px;
        background-color: #0d2163;
    }
    table {
            width: 100%;
            border-collapse: collapse;
        }

        th,td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    
    .error-message {
        color: #dc3545;
        margin-top: 10px;
        text-align: center;
        font-size: 14px;
        padding: 8px;
        border-radius: 6px;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
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
        <h2>Personal Details</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <table>
    <tr>
        <td><label>First Name:</label></td>
        <td><?php echo htmlspecialchars($emp_data['first_name'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label>Last Name:</label></td>
        <td><?php echo htmlspecialchars($emp_data['last_name'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label>Group ID:</label></td>
        <td><?php echo htmlspecialchars($emp_data['group_id'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label for="designation">Designation:</label></td>
        <td><?php echo htmlspecialchars($designation_title); ?></td>
    </tr>
    <tr>
        <td><label for="gender">Gender:</label></td>
        <td><?php echo htmlspecialchars($emp_data['gender'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label for="dob">Date of Birth:</label></td>
        <td><?php echo htmlspecialchars($emp_data['dob'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label for="mobile_no">Mobile No.:</label></td>
        <td><?php echo htmlspecialchars($emp_data['mobile_no'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><label for="email_id">Email ID:</label></td>
        <td><?php echo htmlspecialchars($emp_data['email_id'] ?? ''); ?></td>
    </tr>
</table>

            <br>
            <h2>Extra Details</h2>
<br>
            <p>Note: Optional Fields</p>
            <br>
            <div class="form-group">
                <label for="fax_no">Fax No.:</label>
                <input type="tel" id="fax_no" name="fax_no" value="<?php echo htmlspecialchars($application_data['fax_no'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="date_of_joining">Date of Joining Present Designation:</label>
                <input type="date" id="date_of_joining" name="date_of_joining" value="<?php echo htmlspecialchars($application_data['date_of_joining'] ?? ''); ?>">
            </div>

        <!-- <h2>Application Form - Part 2</h2> -->



        <h2>Past Training Details</h2>
        <?php if ($result_training->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>From date</th>
                        <th>To date</th>
                        <th>Type of event</th>
                        <th>Location</th>
                        <th>Place</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_training->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name_of_seminar']); ?></td>
                        <td><?php echo htmlspecialchars($row['from_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['to_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['type_of_event']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['place']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No previous data available.</p>
        <?php endif; ?>

        <br>
        <h2>Application Form</h2>
        <label for="name_of_applicant">Username:</label>
        <input type="text" id="name_of_applicant" name="name_of_applicant" value="<?php echo htmlspecialchars($emp_data['username'] ?? ''); ?>" required>

        <label for="designation">Designation:</label>
        <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($designation_title); ?>" required>

        <label for="qualification">Qualification:</label>
        <input type="text" id="qualification" name="qualification" required>

        <label for="from_date">From Date:</label>
        <input type="date" id="from_date" name="from_date" required min="2024-01-01" required>

        <label for="to_date">To Date:</label>
        <input type="date" id="to_date" name="to_date" required min="2024-01-01" onchange="updateDuration()" required>

        <label for="duration">Duration(days):</label>
        <input type="text" id="duration" name="duration" readonly>

        <label for="name_of_seminar">Name of Seminar:</label>
        <input type="text" id="name_of_seminar" name="name_of_seminar" required>
        <label for="type_of_event">Type of Event:</label>
        <select id="type_of_event" name="type_of_event" required>
            <option value="Conference">Conference</option>
            <option value="Seminar">Seminar</option>
            <option value="CEP">CEP</option>
            <option value="Symposium">Symposium</option>
            <option value="Workshop">Workshop</option>
            <option value="Webinar">Webinar</option>
            <option value="Exhibition">Exhibition</option>
            <option value="Others">Others</option>
        </select>

        <label for="place">Place:</label>
        <input type="text" id="place" name="place" required>

       <label for="location">Location:</label>
    <input type="text" id="location" name="location" required>

        <label for="last_date_of_submission">Last Date of Submission:</label>
        <input type="date" id="last_date_of_submission" name="last_date_of_submission" onchange="updateDuration()" required>

        <label for="last_conference_attended">Last Conference Attended:</label>
        <input type="date" id="last_conference_attended" name="last_conference_attended">

        <label for="name_of_group_head">Name of Group Head (If doesn't exist then write "NIL" ):</label>
        <input type="text" id="name_of_group_head" name="name_of_group_head" required>

        <input type="submit" value="Submit Application">
        <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </form>
</div>
<script>
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
</body>
</html>