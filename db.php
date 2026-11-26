<?php
session_start();
$db = new mysqli("localhost", "root", "", "merch_db");
if ($db->connect_error) die("Database connection failed. Is MySQL running in XAMPP?");
$db->set_charset("utf8mb4");

const BASE = '/merch';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function me(){ return $_SESSION['u'] ?? null; }
function go($page){ header("Location: ".BASE."/$page"); exit; }

function need($roles = null){
    $u = me();
    if (!$u) go('auth/login.php');
    if ($roles && !in_array($u['role'], $roles)) go('index.php');
}

// where each role lands after login
function home_for($role){ return $role == 'student' ? 'index.php' : 'admin/dashboard.php'; }

// top of every page
function head($title, $back = null){ $u = me(); ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | College Merchandise</title>
<link rel="stylesheet" href="<?= BASE ?>/assets/style.css">
</head><body>
<header class="bar"><div class="wrap bar-in">
  <?php if ($back): ?><a href="<?= BASE.'/'.$back ?>">‹ Back</a><?php endif; ?>
  <strong><?= e($title) ?></strong>
  <nav><?php if ($u): ?><a href="<?= BASE ?>/auth/logout.php">Log out</a><?php endif; ?></nav>
</div></header>
<main class="wrap">
<?php }

// bottom of every page
function foot(){ echo '</main></body></html>'; }