<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "permission_request_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Update the SQL query to order by id in descending order
$sql = "SELECT id, seminar_id, name_of_seminar, location, from_date, to_date FROM applications WHERE name_of_applicant=? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
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
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 980px;
    margin: 40px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Fix table layout */
    margin-bottom: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    white-space: nowrap; /* Prevent text wrapping */
    overflow: hidden;
    text-overflow: ellipsis; /* Handle overflow with ellipsis */
}
th {
    background-color: #f4f4f4;
}
.button-group {
    display: flex;
    justify-content: center; /* Center button in the cell */
}
.button-group button {
    background-color: #1976D2;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    white-space: nowrap; /* Prevent text wrapping */
    overflow: hidden;
    text-overflow: ellipsis;
}
.button-group button:hover {
    background-color: #135a9e;
}
.track {
    text-align: center;
    color: #c4006e;
}
/* Adjust column widths to fit content properly */
th:nth-child(1), td:nth-child(1) { /* Seminar ID column */
    width: 280px; /* Adjust width as needed */
}
th:nth-child(2), td:nth-child(2) { /* Name of Seminar column */
    width: 150px; /* Adjust width as needed */
}
th:nth-child(3), td:nth-child(3) { /* Location column */
    width: 100px; /* Adjust width as needed */
}
th:nth-child(4), td:nth-child(4) { /* From Date column */
    width: 100px; /* Adjust width as needed */
}
th:nth-child(5), td:nth-child(5) { /* To Date column */
    width: 100px; /* Adjust width as needed */
}
th:nth-child(6), td:nth-child(6) { /* Actions column */
    width: 150px; /* Adjust width as needed */
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
    <h1 class="track">Track Your Applications</h1>
    
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Seminar ID</th><th>Name of Seminar</th><th>Location</th><th>From Date</th><th>To Date</th><th>Actions</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['seminar_id'] . "</td>";
            echo "<td>" . $row['name_of_seminar'] . "</td>";
            echo "<td>" . $row['location'] . "</td>";
            echo "<td>" . $row['from_date'] . "</td>";
            echo "<td>" . $row['to_date'] . "</td>";
            echo "<td class='button-group'>
                    <button onclick=\"location.href='status.php?id=" . $row['id'] . "'\">View Status</button>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No applications found.</p>";
    }
    ?>
</div>
</body>
</html>
