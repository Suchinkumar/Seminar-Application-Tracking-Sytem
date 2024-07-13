<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #00509e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #d1e7ff;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #00509e;
            color: #ffffff;
        }

        td {
            background-color: #f0f8ff;
        }

        tr:nth-child(even) td {
            background-color: #d1e7ff;
        }

        tr:hover td {
            background-color: #b3d4ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'home_button.php'; ?>
        <h1>Feedback Details</h1>
        <?php
        // Database connection parameters
        $servername = "localhost"; // replace with your server name
        $username = "root"; // replace with your database username
        $password = ""; // replace with your database password
        $dbname = "permission_request_system"; // replace with your database name

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get the id from the request (e.g., from a GET request)
        $id = $_GET['id']; // replace with appropriate method to get id

        // Query to get the specific row by id
        $sql = "SELECT * FROM feedback WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the row
            $row = $result->fetch_assoc();

            // Start the table
            echo "<table>";
            echo "<tr><th>Column Name</th><th>Value</th></tr>";

            // Column labels mapping
            $columnLabels = [
                'submitted_by' => 'Submitted By', 
                'designation' => 'Designation', 
                'user_group' => 'User Group ID', 
                'programme_type' => 'Programme Type', 
                'programme_title' => 'Programme Title',
                'seminar_id' => 'Seminar ID', 
                'no_of_days' => 'Number of Days', 
                'from_date' => 'From Date', 
                'to_date' => 'To Date', 
                'training_fee' => 'Training Fee', 
                'overview' => 'Overview', 
                'effectiveness' => 'Effectiveness', 
                'programme' => 'Programme', 
                'conduct' => 'Conduct', 
                'instructor' => 'Instructor', 
                'aids' => 'Aids', 
                'ad_remarks' => 'AD Remarks', 
                'tcp_hr_head_remarks' => 'HR Head Remarks', 
                'tcp_hr_ad_remarks' => 'HR Admin Remarks', 
            ];

            // Loop through columns and print only non-null values
            foreach ($columnLabels as $column => $label) {
                if (!is_null($row[$column])) {
                    echo "<tr><td>" . htmlspecialchars($label) . "</td><td>" . htmlspecialchars($row[$column]) . "</td></tr>";
                }
            }

            // End the table
            echo "</table>";
        } else {
            echo "<p>No record found.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
