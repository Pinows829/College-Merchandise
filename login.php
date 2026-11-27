<?php
require_once __DIR__ . '/db.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $st = $db->prepare("SELECT * FROM users WHERE student_id=? OR email=?");
    $st->bind_param("ss", $login, $login);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);          // don't keep the hash in the session
        $_SESSION['u'] = $user;            // this is what "logged in" means
        go(home_for($user['role']));
    }
    $err = 'Wrong Student ID/email or password.';
}

head('College Merchandise');
?>
<form class="form card" method="post">
  <h2>Welcome back</h2>
  <?php if ($err) echo "<div class='err'>".e($err)."</div>"; ?>

  <label>Student ID / Email</label>
  <input name="login" required>

  <label>Password</label>
  <input type="password" name="password" required>

  <button class="full">Log in</button>
  <p class="muted">No account? <a href="register.php">Register</a></p>
</form>
<?php foot(); ?>
