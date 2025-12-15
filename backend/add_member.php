<?php
require 'db.php';
$name=$_POST['name'] ?? '';
$course=$_POST['course'] ?? '';
$year=(int)($_POST['year_level'] ?? 0);
$contact=$_POST['contact'] ?? '';
$status=$_POST['status'] ?? 'Active';
$stmt = $conn->prepare("INSERT INTO members(name,course,year_level,contact,status) VALUES(?,?,?,?,?)");
$stmt->bind_param("ssiss",$name,$course,$year,$contact,$status);
$stmt->execute();
header('Location: ../members.php');
