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

// Check if training_id parameter is set
if (!isset($_GET['training_id'])) {
    echo "Error: Training ID parameter is missing.";
    exit;
}

$training_id = $_GET['training_id'];

// Fetch training form details based on training_id
$sql = "SELECT * FROM training_forms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $training_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $training = $result->fetch_assoc();
    $filePath = $training['notice'];

    if (file_exists($filePath)) {
        // Set headers to initiate the file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        flush(); // Flush system output buffer
        readfile($filePath);
        exit;
    } else {
        echo "Error: File does not exist.";
    }
} else {
    echo "Error: Training form not found.";
}

$stmt->close();
$conn->close();
?>
