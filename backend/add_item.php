<?php
require_once 'db.php';
session_start(); 

$item = $_POST['item'] ?? '';
$category = $_POST['category'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 0);
$cond = $_POST['cond'] ?? '';
$location = $_POST['location'] ?? '';
$last = $_POST['last_update'] ?? date('Y-m-d');

// Inayos ang bind_param string: ssisss (String, String, Integer, String, String, String)
$stmt = $conn->prepare("INSERT INTO inventory (item,category,quantity,cond,location,last_update) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssisss", $item, $category, $quantity, $cond, $location, $last);

if ($stmt->execute()) {
    // Success
    header('Location: ../inventory.php');
} else {
    // Error
    // Sa production, mas mainam na mag-log ng error kaysa mag-echo
    // echo "Error: " . $stmt->error;
    header('Location: ../inventory.php?status=insert_error');
}

$stmt->close();
exit();
?>