<?php
require_once 'db.php';
$id = (int)($_GET['id'] ?? 0);
if($id){
  $stmt = $conn->prepare("DELETE FROM inventory WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
}
header('Location: ../inventory.php');
