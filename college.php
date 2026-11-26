<?php
include 'Includes/db.php';
need(['student']);

$id  = (int)$_GET['id'];
$cat = $_GET['cat'] ?? '';                          // '' means All

$college = $db->query("SELECT * FROM colleges WHERE id=$id")->fetch_assoc();
if (!$college) go('index.php');

$st = $db->prepare("SELECT * FROM products WHERE college_id=? AND (?='' OR category=?)");
$st->bind_param("iss", $id, $cat, $cat);
$st->execute();
$products = $st->get_result();

head($college['code'] . ' Merchandise', 'index.php');
?>
<div class="tabs">
  <?php foreach (['' => 'All', 'Shirts' => 'Shirts', 'Jackets' => 'Jackets', 'Accessories' => 'Accessories'] as $k => $label): ?>
    <a class="btn <?= $cat == $k ? '' : 'alt' ?>" href="?id=<?= $id ?>&cat=<?= $k ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="grid">
  <?php while ($p = $products->fetch_assoc()): ?>
    <div class="card">
      <div class="ph">No photo</div>
      <b><?= e($p['name']) ?></b>
      <div>₱<?= number_format($p['price']) ?></div>
      <a class="btn" href="product.php?id=<?= $p['id'] ?>">View details</a>
    </div>
  <?php endwhile; ?>
</div>
<?php foot(); ?>