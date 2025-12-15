<?php session_start();
if (isset($_SESSION['user'])) header('Location: dashboard.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <title>Sport Equipment Inventory - Login</title>
  <link rel="stylesheet" href="css/style.css">

</head>

<body class="login-body">
  <div class="login-container">
    <div class="login-left">
      <img src="logo_skc.jpg" class="logo" alt="logo" />
      <h1>SPORT EQUIPMENT<br>INVENTORY</h1>
    </div>

    <div class="login-right">
      <h3>Sign In</h3>

      <?php if(isset($_GET['err'])):?>
        <div class="error"><?php echo htmlspecialchars($_GET['err']); ?></div>
      <?php endif; ?>

      <form action="backend/login.php" method="post">
        <input name="username" placeholder="Username" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit">Continue</button>

      </form>
    </div>
  </div>
</body>

</html>