<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "permission_request_system"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Report Generation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
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
        h1 {
            color: #283b90;
            text-align: center;
            margin-top: 20px;
        }
        form {
            background-color: #e6e6f2;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #7f007b;
            font-size: 16px;
            font-weight: bold;
        }
        p {
            margin: 5px 0 15px;
            color: #000;
            font-size: 14px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], input[type="date"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #003366;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        input[type="submit"]:hover {
            background-color: #002244;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #003366;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        #printButton {
            background-color: #004488;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        #printButton:hover {
            background-color: #003366;
        }
        .note {
            text-align: center;
            color: #f00;
            font-size: 17px;
            font-weight: bold;
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }
        function generateReport() {
            alert("Scroll down to view the generated report.");
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
    <h1>Generate Report</h1>
    <hr>
    <p class= "note" text-align:center">
    Note: You can apply one or more filters to generate a report. The report will be based on the selected filters. 
    </p>
    <hr>
    <form method="post" action="">
        <label for="year">Year:</label>
        <p>Select a year to generate a list of applications approved in that year.</p>
        <input type="number" id="year" name="year" min="2000" max="2100">

        <label for="from_date">From Date:</label>
        <p>Select a start date to generate a list of applications approved after this date.</p>
        <input type="date" id="from_date" name="from_date">

        <label for="to_date">To Date:</label>
        <p>Select an end date to generate a list of applications approved up to this date.</p>
        <input type="date" id="to_date" name="to_date">

        <label for="username">Username:</label>
        <p>Enter a username to generate a list of applications approved for that user.</p>
        <input type="text" id="username" name="username">

        <input type="submit" value="Generate Report" onclick="generateReport()">
    </form>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
        $to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
        $username = isset($_POST['username']) ? $_POST['username'] : '';
    
        $query = "SELECT applications.*, emp.username 
                  FROM applications 
                  JOIN emp ON applications.name_of_applicant = CONCAT(emp.username)
                  WHERE 1=1";
    
        if (!empty($year)) {
            $query .= " AND YEAR(applications.from_date) = '$year'";
        }
        if (!empty($from_date) && !empty($to_date)) {
            $query .= " AND applications.from_date >= '$from_date' AND applications.to_date <= '$to_date'";
        }
        if (!empty($username)) {
            $query .= " AND emp.username = '$username'";
        }
    
        $result = $conn->query($query);
    
        if ($result->num_rows > 0) {
            echo "<div id='report'>";
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Seminar Name</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Location</th>
                        <th>Remarks</th>
                    </tr>";
    
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$row['name_of_seminar']}</td>
                        <td>{$row['from_date']}</td>
                        <td>{$row['to_date']}</td>
                        <td>{$row['location']}</td>
                        <td>{$row['remarks']}</td>
                    </tr>";
            }
            echo "</table>";
            echo "</div>";
            echo "<button id='printButton' onclick='printReport()'>Print Report</button>";
        } else {
            echo "<p style='text-align:center;'>No results found.</p>";
        }
    }
    
    $conn->close();
    ?>
</body>
</html>
