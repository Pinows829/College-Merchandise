<?php
session_start();
try {
    $db = new mysqli("localhost", "root", "", "merch_db");
    $db->set_charset("utf8mb4");
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and import merch_db.sql using phpMyAdmin.');
}

// Derive the app URL from this project's location under the web server.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if (basename($scriptDir) === 'admin') $scriptDir = dirname($scriptDir);
define('BASE', rtrim($scriptDir, '/'));

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function me(){ return $_SESSION['u'] ?? null; }
function go($page){ header("Location: ".BASE."/$page"); exit; }

function need($roles = null){
    $u = me();
    if (!$u) go('login.php');
    if ($roles && !in_array($u['role'], $roles)) go('index.php');
}

// where each role lands after login
function home_for($role){ return $role == 'student' ? 'index.php' : 'admin/dashboard.php'; }

function money($amount){ return '₱' . number_format((float)$amount, 2); }
function cart_count(){ return array_sum(array_map('intval', $_SESSION['cart'] ?? [])); }

// Shared site header and navigation.
function head($title, $back = null){ $u = me(); ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | College Merchandise</title>
<link rel="stylesheet" href="<?= BASE ?>/style.css">
</head><body>
<header class="bar"><div class="wrap bar-in">
  <a class="brand" href="<?= BASE ?>/index.php"><span class="brand-mark">C</span><span>Campus<span class="brand-light">Merch</span></span></a>
  <nav class="main-nav">
    <a href="<?= BASE ?>/index.php">Shop</a>
    <?php if ($u && $u['role'] === 'student'): ?>
      <a href="<?= BASE ?>/orders.php">My orders</a>
      <a href="<?= BASE ?>/cart.php">Cart <span class="nav-count"><?= cart_count() ?></span></a>
    <?php elseif ($u && $u['role'] === 'admin'): ?>
      <a href="<?= BASE ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?= BASE ?>/admin/products.php">Products</a>
      <a href="<?= BASE ?>/admin/orders.php">Orders</a>
    <?php endif; ?>
  </nav>
  <div class="nav-account">
    <?php if ($u): ?><span class="nav-user"><?= e($u['full_name'] ?? 'Account') ?></span><a class="nav-logout" href="<?= BASE ?>/logout.php">Log out</a>
    <?php else: ?><a href="<?= BASE ?>/login.php">Log in</a><a class="nav-register" href="<?= BASE ?>/register.php">Create account</a><?php endif; ?>
  </div>
</div></header>
<main class="wrap">
<?php if ($back): ?><a class="page-back" href="<?= BASE.'/'.$back ?>">← Back</a><?php endif; ?>
<?php }

// bottom of every page
function foot(){ ?>
</main><footer class="site-footer"><div class="wrap footer-inner"><span>Campus<span class="footer-brand">Merch</span></span><small>College pride, made to wear.</small><small>© <?= date('Y') ?> CampusMerch</small></div></footer></body></html>
<?php }
