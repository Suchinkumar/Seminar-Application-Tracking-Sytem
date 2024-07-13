<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['training_id'])) {
    $trainingId = $_GET['training_id'];

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

    // Delete the training form
    $sql = "DELETE FROM training_forms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trainingId);

    if ($stmt->execute()) {
        echo "Training form deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: dashboard.php');
    exit;
} else {
    echo "Invalid request.";
}
?>
