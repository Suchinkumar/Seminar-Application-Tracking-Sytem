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
    echo "Error: Seminar ID parameter is missing.";
    exit;
}

// Decode the seminar_id
$seminar_id = urldecode($_GET['seminar_id']);

// Fetch seminar details based on seminar_id
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
    $filePath = $training['notice'];

    // Check if file exists
    if (file_exists($filePath)) {
        // Determine the content type based on file extension
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch ($fileExtension) {
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'docx':
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            default:
                $contentType = 'application/octet-stream';
        }

        // Set headers to initiate the file download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "Error: File '$filePath' does not exist.";
    }
} else {
    echo "Error: Training form not found.";
}

$stmt->close();
$conn->close();
?>
