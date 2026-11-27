<?php
include '../Includes/db.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {      // runs only when the form is submitted
    $name = trim($_POST['name']);
    $sid  = trim($_POST['sid']);
    $email = trim($_POST['email']);
    $college = (int)$_POST['college'];

    if (strlen($_POST['password']) < 8) {
        $err = 'Password must be at least 8 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter a valid school email.';
    } else {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);   // never store the real password
        $st = $db->prepare("INSERT INTO users(student_id,email,password,full_name,college_id) VALUES(?,?,?,?,?)");
        $st->bind_param("ssssi", $sid, $email, $hash, $name, $college);
        if ($st->execute()) go('auth/login.php');
        else $err = 'That Student ID or email is already registered.';
    }
}

$colleges = $db->query("SELECT * FROM colleges");
head('Create account');
?>
<form class="form card" method="post">
  <h2>Register</h2>
  <?php if ($err) echo "<div class='err'>".e($err)."</div>"; ?>

  <label>Full name</label>
  <input name="name" required>

  <label>Student ID</label>
  <input name="sid" placeholder="23-1234-567" required>

  <label>School email</label>
  <input type="email" name="email" placeholder="name@school.edu.ph" required>

  <label>College</label>
  <select name="college" required>
    <option value="">Select your college</option>
    <?php while ($c = $colleges->fetch_assoc()) echo "<option value='{$c['id']}'>".e($c['code'])."</option>"; ?>
  </select>

  <label>Password</label>
  <input type="password" name="password" placeholder="At least 8 characters" required>

  <button class="full">Create account</button>
  <p class="muted">Already registered? <a href="login.php">Log in</a></p>
</form>
<?php foot(); ?>