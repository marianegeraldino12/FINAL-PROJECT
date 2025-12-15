<?php
// Siguraduhin na ang path ay tama
require_once 'db.php'; 
session_start(); 

// I-check kung naka-login
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// 1. Inayos ang pagkuha ng POST data (ginamit ang single quotes at inayos ang default value)
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');
$location = $_POST['location'] ?? '';

// *Opsyonal: Kung gusto mong isama ang 'type' field tulad ng sa events.php form*
// $type = $_POST['type'] ?? '';

// 2. Inayos ang SQL statement: Inalis ang extra comma at inayos ang VALUES.
// Assuming 4 columns: title, description, date, location
$stmt = $conn->prepare("INSERT INTO events (title, description, date, location) VALUES (?, ?, ?, ?)");

// 3. Tama na ang bind_param para sa 4 string parameters
$stmt->bind_param("ssss", $title, $description, $date, $location);

if ($stmt->execute()) {
    // Success: Idinagdag ang exit()
    header('Location: ../events.php?status=success');
    exit(); // Mahalaga ito!
} else {
    // Error
    // I-log ang error para sa debugging, huwag ipakita sa user
    error_log("Event insert failed: " . $stmt->error);
    header('Location: ../events.php?status=insert_error');
    exit(); // Mahalaga ito!
}

$stmt->close();
?>