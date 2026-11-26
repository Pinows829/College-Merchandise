<?php
include 'Includes/db.php';
need(['student']);

$id = (int)$_GET['id'];
$p = $db->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$p) go('index.php');

$sizes = $db->query("SELECT * FROM product_sizes WHERE product_id=$id");

head($p['name'], 'college.php?id=' . (int)$p['college_id']);
?>
<div class="card">
  <div class="ph">No photo</div>
  <h2><?= e($p['name']) ?></h2>
  <b>₱<?= number_format($p['price']) ?></b>
  <p><?= e($p['description']) ?></p>

  <form method="post" action="cart.php">
    <input type="hidden" name="act" value="add">

    <label>Size</label>
    <div class="chips">
      <?php $first = true; while ($s = $sizes->fetch_assoc()): ?>
        <label>
          <input type="radio" name="size" value="<?= $s['id'] ?>"
                 <?= $s['stock'] < 1 ? 'disabled' : '' ?>
                 <?= ($first && $s['stock'] > 0) ? 'checked' : '' ?>>
          <span><?= e($s['size']) ?> (<?= $s['stock'] ?> left)</span>
        </label>
        <?php if ($s['stock'] > 0) $first = false; ?>
      <?php endwhile; ?>
    </div>

    <label>Quantity</label>
    <input type="number" name="qty" value="1" min="1" max="20">

    <button class="full">Add to cart</button>
  </form>
</div>
<?php foot(); ?>