<?php
require_once __DIR__ . '/db.php';
$id = (int)($_GET['id'] ?? 0);
$st = $db->prepare("SELECT p.*,c.code college_code,(SELECT COALESCE(SUM(stock),0) FROM product_sizes WHERE product_id=p.id) stock FROM products p JOIN colleges c ON c.id=p.college_id WHERE p.id=?");
$st->bind_param('i',$id); $st->execute(); $p = $st->get_result()->fetch_assoc();
if (!$p) go('index.php');

$st = $db->prepare("SELECT * FROM product_sizes WHERE product_id=? ORDER BY id"); $st->bind_param('i',$id); $st->execute(); $sizes = $st->get_result();

head($p['name'], 'college.php?id=' . (int)$p['college_id']);
?>
<section class="product-detail card">
  <div class="detail-visual"><span class="product-category"><?= e($p['category']) ?></span><span class="detail-monogram"><?= e(substr($p['college_code'],0,1)) ?></span><span class="product-college"><?= e($p['college_code']) ?> COLLECTION</span></div>
  <div class="detail-copy"><p class="eyebrow"><?= e($p['college_code']) ?> · <?= e($p['category']) ?></p><h1><?= e($p['name']) ?></h1>
  <strong class="detail-price"><?= money($p['price']) ?></strong>
  <p class="detail-description"><?= e($p['description']) ?></p>

  <?php if (me() && me()['role']==='student'): ?><form method="post" action="cart.php">
    <input type="hidden" name="act" value="add">

    <label>Size</label>
    <div class="chips">
      <?php $first = true; while ($s = $sizes->fetch_assoc()): ?>
        <label>
          <input type="radio" name="size" value="<?= $s['id'] ?>"
                 <?= $s['stock'] < 1 ? 'disabled' : '' ?>
                 <?= ($first && $s['stock'] > 0) ? 'checked' : '' ?>>
          <span><?= e($s['size']) ?> · <?= $s['stock'] ?> left</span>
        </label>
        <?php if ($s['stock'] > 0) $first = false; ?>
      <?php endwhile; ?>
    </div>

    <label>Quantity</label>
    <input type="number" name="qty" value="1" min="1" max="20">

    <button class="full" <?= (int)$p['stock']===0 ? 'disabled':'' ?>><?= (int)$p['stock']===0 ? 'Out of stock' : 'Add to cart' ?></button>
  </form><?php elseif (!me()): ?><a class="btn full" href="login.php">Log in to add to cart</a><?php else: ?><p class="muted">Administrator view</p><?php endif; ?>
  </div>
</section>
<?php foot(); ?>
