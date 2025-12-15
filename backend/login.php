<?php
session_start();
require_once 'db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    header('Location: ../index.php?err=Fill+credentials');
    exit;
}

$stmt = $conn->prepare("SELECT id,username,password,role FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s",$username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    // dev: comparing plain text password (because sample SQL inserted plain).
    // For production, use password_hash() and password_verify()
    if ($password === $row['password']) {
        $_SESSION['user'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        header('Location: ../dashboard.php');
        exit;
    }
}
header('Location: ../index.php?err=Invalid+credentials');
