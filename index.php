<?php
include 'Includes/db.php';
need(['student']);

$q = trim($_GET['q'] ?? '');                       // what the student typed in search
$colleges = $db->query("SELECT * FROM colleges");

$st = $db->prepare("SELECT * FROM products WHERE name LIKE ? LIMIT 8");
$like = "%$q%";
$st->bind_param("s", $like);
$st->execute();
$products = $st->get_result();

head('College Merchandise');
?>
<form><input name="q" value="<?= e($q) ?>" placeholder="Search for products..."></form>

<?php if (!$q): ?>
  <h2>Browse by college</h2>
  <div class="grid">
    <?php while ($c = $colleges->fetch_assoc()): ?>
      <a class="card btn alt" href="college.php?id=<?= $c['id'] ?>"><?= e($c['code']) ?></a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<h2><?= $q ? 'Search results' : 'Featured merchandise' ?></h2>
<div class="grid">
  <?php while ($p = $products->fetch_assoc()): ?>
    <div class="card">
      <div class="ph">No photo</div>
      <b><?= e($p['name']) ?></b>
      <div>₱<?= number_format($p['price']) ?></div>
      <a class="btn" href="product.php?id=<?= $p['id'] ?>">View</a>
    </div>
  <?php endwhile; ?>
</div>
<?php foot(); ?>