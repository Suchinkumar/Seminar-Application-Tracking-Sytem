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
$sql_emp = "SELECT * FROM emp WHERE username=?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("s", $username);
$stmt_emp->execute();
$result_emp = $stmt_emp->get_result();
$emp_data = $result_emp->fetch_assoc();
$stmt_emp->close();

if (!isset($_GET['training_id'])) {
    echo "Error: Training ID parameter is missing.";
    exit;
}
// $message = '';
// $designation = $emp_data['internal_desig_id'] ?? '';
// $designation_title = '';
// switch ($designation) {
//     case 1:
//         $designation_title = 'Director';
//         break;
//     case 2:
//         $designation_title = 'AD';
//         break;
//     case 3:
//         $designation_title = 'Head';
//         break;
//     case 4:
//         $designation_title = 'Scientist';
//         break;
//     default:
//         $designation_title = 'Unknown Designation';
//         break;
// }

$designation_id = $emp_data['internal_desig_id'] ?? null;
$designation_title = 'Unknown Designation';

if ($designation_id !== null) {
    // Prepare and execute the SQL query to fetch the designation title
    $sql_desig = "SELECT role FROM internal_desig WHERE id=?";
    $stmt_desig = $conn->prepare($sql_desig);
    $stmt_desig->bind_param("i", $designation_id);
    $stmt_desig->execute();
    $result_desig = $stmt_desig->get_result();

    // Fetch the result
    if ($result_desig->num_rows > 0) {
        $row_desig = $result_desig->fetch_assoc();
        $designation_title = $row_desig['role'];
    }

    $stmt_desig->close();
}

$group_id = $emp_data['group_id'] ?? null;
$group_name = 'Unknown Group';

if ($group_id !== null) {
    // Prepare and execute the SQL query to fetch the group name
    $sql_group = "SELECT group_name FROM group_info WHERE group_id=?";
    $stmt_group = $conn->prepare($sql_group);
    $stmt_group->bind_param("i", $group_id);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();

    // Fetch the result
    if ($result_group->num_rows > 0) {
        $row_group = $result_group->fetch_assoc();
        $group_name = $row_group['group_name'];
    }

    $stmt_group->close();
}


$training_id = $_GET['training_id'];

// Fetch training form details
$sql = "SELECT seminar_id, name_of_seminar, from_date, to_date, place, last_date_of_submission, duration, location, type_of_event FROM training_forms WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare statement failed: " . $conn->error);
}

$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $training = $result->fetch_assoc();
} else {
    die("Training form not found.");
}

// Check if user has already applied for this training
$sql = "SELECT id FROM user_applications WHERE username = ? AND training_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $username, $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "You have already applied for this training.";
    $stmt->close();
    $conn->close();
    exit;
}

// Fetch fax number and date of joining present designation
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

$from_date = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert or update application details in applications1 table
    $designation = htmlspecialchars($_POST['designation'] ?? '');
    $group_name = htmlspecialchars_decode($_POST['group_name'] ?? '');
    $fax_no = htmlspecialchars($_POST['fax_no'] ?? '');
    $date_of_joining = htmlspecialchars($_POST['date_of_joining'] ?? '');

    if ($application_data) {
        // Update existing record
        $sql_update = "UPDATE applications1 SET fax_no=?, date_of_joining=? WHERE name_of_applicant=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sss", $fax_no, $date_of_joining, $username);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Insert new record
        $sql_insert = "INSERT INTO applications1 (name_of_applicant, fax_no, date_of_joining) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $username, $fax_no, $date_of_joining);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

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
    // $name_of_group_head = htmlspecialchars($_POST['name_of_group_head'] ?? '');
    $type_of_event = htmlspecialchars($_POST['type_of_event'] ?? '');
    $seminar_id = htmlspecialchars_decode($_POST['seminar_id'] ?? ''); // Decode HTML entities here
    // $organizing_agency = htmlspecialchars($_POST['organizing_agency'] ?? '');
    $is_paid = htmlspecialchars($_POST['is_paid'] ?? '');
    $transaction_in_favour_of = htmlspecialchars($_POST['transaction_in_favour_of'] ?? '');
    $transaction_amount = htmlspecialchars($_POST['transaction_amount'] ?? '');
    $transaction_mode = htmlspecialchars($_POST['transaction_mode'] ?? '');

    $sql = "INSERT INTO applications (name_of_applicant, designation,group_name, qualification, from_date, to_date, duration, name_of_seminar, place, location, last_date_of_submission, last_conference_attended, type_of_event, seminar_id, is_paid, transaction_in_favour_of, transaction_amount, transaction_mode) 
            VALUES (?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ssssssssssssssssss", 
        $name_of_applicant, $designation,$group_name, $qualification, $from_date, $to_date, $duration, 
        $name_of_seminar, $place, $location, $last_date_of_submission, $last_conference_attended, 
        $type_of_event, $seminar_id, $is_paid, 
        $transaction_in_favour_of, $transaction_amount, $transaction_mode);


    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        // Fetch group_head_id and ad_id for the applicant's group
        $applicant_group_id = $emp_data['group_id'];
        $sql_group_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id=?";
        $stmt_group_info = $conn->prepare($sql_group_info);
        $stmt_group_info->bind_param("i", $applicant_group_id);
        $stmt_group_info->execute();
        $result_group_info = $stmt_group_info->get_result();
        $group_info = $result_group_info->fetch_assoc();
        $group_head_id = $group_info['group_head_id'];
        $ad_id = $group_info['ad_id'];
        

        // Determine the role of the applicant
        // $applicant_role = $emp_data['role']; // Assuming the role is stored in emp_data['role']

        $sql_group_2_info = "SELECT group_head_id, ad_id FROM group_info WHERE group_id=2";
        $result_group_2_info = mysqli_query($conn, $sql_group_2_info);
        if (!$result_group_2_info) {
            die("Error fetching group info for group_id 2: " . mysqli_error($conn));
        }
        $group_2_info = mysqli_fetch_assoc($result_group_2_info);
        $group_2_head_id = $group_2_info['group_head_id'];
        $group_2_ad_id = $group_2_info['ad_id'];


        // Set the initial status values
        $group_head_status = 0;
        $ad_status = 0;
        $tcp_hr_head_status = 0;
        $tcp_hr_ad_status = 0;

        // Set status values based on applicant's role and group memberships
        if ($emp_data['id'] == $group_head_id) {
            $group_head_status = 1;
        }

        if ($emp_data['id'] == $ad_id) {
            $group_head_status = 1;
            $ad_status = 1;
        }

        if ($emp_data['id']  == $group_2_head_id) {
            $group_head_status = 1;
            $ad_status = 1;
            $tcp_hr_head_status = 1;
        }

        if ($emp_data['id'] == $group_2_ad_id) {
            $group_head_status = 1;
            $ad_status = 1;
            $tcp_hr_head_status = 1;
            $tcp_hr_ad_status = 1;
        }

        // Update the application status in the database
        $sql_update_status = "UPDATE applications 
                              SET group_head_status = ?, ad_status = ?, tcp_hr_head_status = ?, tcp_hr_ad_status = ?
                              WHERE id = ?";
        $stmt_update_status = $conn->prepare($sql_update_status);
        $stmt_update_status->bind_param("iiiii", $group_head_status, $ad_status, $tcp_hr_head_status, $tcp_hr_ad_status, $last_id);
        $stmt_update_status->execute();

        // Insert the application record into user_applications
        $sql = "INSERT INTO user_applications (username, training_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $training_id);

        if ($stmt->execute()) {
            echo "Application successful.";
            // echo "<script type='text/javascript'>alert('Your application has been forwarded.');</script>";

        } else {
            echo "Failed to apply for training. Please try again.";
        }
        // echo "<script type='text/javascript'>alert('Your application has been forwarded.');</script>";
        echo "<script type='text/javascript'>
                alert('Your application has been forwarded.');
                window.location.href = 'print_application.php?id=$last_id';
              </script>";
        // header("Location: print_application.php?id=$last_id");
        // echo "<script type='text/javascript'>alert('Your application has been forwarded.');</script>";
        exit;
    } else {
        $error_message = $stmt->error;
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
    height: 55px;
}

.header h1 {
    font-size: 22px;
    margin: 0;
    color: #f4f4f4;
}

.navbar {
    /* background-color: #0059b3; */
    color: black;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar h2 {
    margin: 0;
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
            /* border-bottom: 1px solid #ddd; */
            border: inset 2px #000422;
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
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd; /* Add a border around the table */
        }

       

        th {
            background-color: #f2f2f2;
        }
        .color {
            color:#ff009e;
        }

        .p{
            color: #ff009e;
            font-size: 10px;
            text-align: center;
            
        }
    </style>
    <script>
        function toggleTransactionFields() {
            var isPaid = document.getElementById("is_paid").value.toLowerCase();
            var transactionFields = document.getElementById("transaction_fields");

            if (isPaid === "yes") {
                transactionFields.style.display = "block";
            } else {
                transactionFields.style.display = "none";
            }
        }
    </script>
    <script>
        function toggleOtherField() {
            var transactionMode = document.getElementById("transaction_mode").value;
            var otherField = document.getElementById("other_transaction_mode");
            var otherInput = document.getElementById("other_mode_input");

            if (transactionMode === "others") {
                otherField.style.display = "block";
                otherInput.name = "transaction_mode";  // Change name to match transaction_mode
            } else {
                otherField.style.display = "none";
                otherInput.name = "";  // Clear name when not needed
            }
        }

        function updateTransactionMode() {
            var transactionMode = document.getElementById("transaction_mode");
            var otherInput = document.getElementById("other_mode_input");

            if (transactionMode.value === "others") {
                transactionMode.value = otherInput.value;  // Set the value of transaction_mode to the other input value
            }
        }
    </script>
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
    <h2 class="color">Personal Details</h2>
        <form method="post">
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
        <td><label>Group:</label></td>
        <td><?php echo htmlspecialchars($group_name); ?></td>
    </tr>
    <tr>
        <td><label for="designation">Designation:</label></td>
        <td><?php echo htmlspecialchars($designation_title); ?></td>
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
            <h2 class="color">Past Training Details</h2>

            <?php if ($result_training->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name of training program</th>
                        <th>From date</th>
                        <th>To date</th>
                        <th>Type of training program</th>
                        <th>Location/Venue</th>
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
                        <?php if($from_date) :?>

                        <?php else: ?>
                            
                        <?php endif; ?>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No previous data available.</p>
        <?php endif; ?>

        <br>
            <div class="form-group">


            <!-- <label for="name_of_applicant">Username:</label> -->
            <input type="hidden" id="name_of_applicant" name="name_of_applicant" value="<?php echo htmlspecialchars($emp_data['username'] ?? ''); ?>" required>
    </div>
    <div class="form-group">

            <!-- <label for="designation">Designation:</label> -->
            <input type="hidden" id="designation" name="designation" value="<?php echo htmlspecialchars($designation_title); ?>" required>
            </div>
            <div class="form-group">

            <!-- <label for="designation">Designation:</label> -->
            <input type="hidden" id="group_name" name="group_name" value="<?php echo htmlspecialchars($group_name); ?>" required>
            </div>
    
            <!-- </form> -->


            <h2 class="color">Application Form</h2>

            

            <div class="form-group">
            <label for="name_of_seminar">Name of Training Program:</label>
            <input type="text" id="name_of_seminar" name="name_of_seminar" value="<?php echo htmlspecialchars($training['name_of_seminar']); ?>" readonly>
        </div>
        <div class="form-group">
        <label for="seminar_id">Training program ID:</label>
        <input  type="text" id="seminar_id" name="seminar_id" value="<?php echo htmlspecialchars($training['seminar_id']); ?>" readonly>
        </div>
        <div class="form-group">
           <label for="type_of_event">Type of training program:</label>
           <input  type="text" id="type_of_event" name="type_of_event" value="<?php echo htmlspecialchars($training['type_of_event']); ?>">
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
            <label for="place">Place:</label>
            <input type="text" id="place" name="place" value="<?php echo htmlspecialchars($training['place']); ?>" readonly>
        </div>
        <div class="form-group">

            <label for="location">Location/Venue:</label>
            <input type="location" id="location" name="location" value="<?php echo htmlspecialchars($training['location']); ?>" readonly>
</div>
            <div class="form-group">
            <label for="last_date_of_submission">Last date of submission:</label>
            <input type="text" id="last_date_of_submission" name="last_date_of_submission" value="<?php echo htmlspecialchars($training['last_date_of_submission']); ?>" readonly>
        </div>
        <div class="form-group">
        <label for="last_conference_attended">Last Training Program Attended:</label>
        <input type="date" id="last_conference_attended" name="last_conference_attended" value="<?php echo $from_date ? $from_date : ''; ?>">
        </div>
        <div class="form-group">

            <label for="qualification">Qualification:</label>
            <input type="text" id="qualification" name="qualification" required>
</div>
<!-- <div class="form-group">

            <label for="name_of_group_head">Name of Group Head (If doesn't exist then write "NIL" ):</label>
            <input type="text" id="name_of_group_head" name="name_of_group_head" required>
</div> -->
        <!-- <div class="form-group">
        <label for="organizing_agency">Organizing Agency:</label>
        <input type="text" id="organizing_agency" name="organizing_agency"><br>
        </div> -->
        <div class="form-group">
        <label for="is_paid">Is it a Paid Training Program? (Yes/No):</label>
<select id="is_paid" name="is_paid" onchange="toggleTransactionFields()">
    <option value="">Select</option>
    <option value="Yes">Yes</option>
    <option value="No">No</option>
</select>
</div>

        <div class="form-group">
        <div id="transaction_fields" style="display:none;">
            <label for="transaction_in_favour_of">Transaction in Favour of:</label>
            <input type="text" id="transaction_in_favour_of" name="transaction_in_favour_of"><br>

            <label for="transaction_amount">Transaction Amount:</label>
            <input type="number" id="transaction_amount" name="transaction_amount"><br>

            <form onsubmit="updateTransactionMode()">
        <label for="transaction_mode">Transaction Mode:</label>
        <select id="transaction_mode" name="transaction_mode" onchange="toggleOtherField()">
            <option value="NULL"></option>
            <option value="credit_card">Credit Card</option>
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
            <option value="bank_transfer">NEFT/RTGS</option>
            <option value="net_banking">Net Banking</option>
            <option value="others">Others</option>
        </select>
        <br>

        <div id="other_transaction_mode" style="display: none;">
            <label for="other_mode_input">Please specify:</label>
            <input type="text" id="other_mode_input" name="">
        </div>

        </div>
        <h2 class="color">Optional Fields </h2>
        <div class="form-group">
                <label for="fax_no">Fax No.:</label>
                <input type="tel" id="fax_no" name="fax_no" value="<?php echo htmlspecialchars($application_data['fax_no'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="date_of_joining">Date of Joining Present Designation:</label>
                <input type="date" id="date_of_joining" name="date_of_joining" value="<?php echo htmlspecialchars($application_data['date_of_joining'] ?? ''); ?>">
        </div>
        </div>
        <input type="submit" value="Submit Application">
            <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            </form>
    </div>
</body>
</html>
