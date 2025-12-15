<?php
require 'db.php';
$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header('Location: ../events.php');
?>