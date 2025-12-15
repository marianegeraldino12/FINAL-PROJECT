<?php
require_once 'db.php';
$id = (int)($_POST['id'] ?? 0);
$item = $_POST['item'] ?? '';
$category = $_POST['category'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 0);
$cond = $_POST['cond'] ?? '';
$location = $_POST['location'] ?? '';
$last = $_POST['last_update'] ?? date('Y-m-d');

if($id){
  $stmt = $conn->prepare("UPDATE inventory SET item=?,category=?,quantity=?,cond=?,location=?,last_update=? WHERE id=?");
  $stmt->bind_param("ssisssi",$item,$category,$quantity,$cond,$location,$last,$id);
  $stmt->execute();
}
header('Location: ../inventory.php');
