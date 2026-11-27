<?php
require_once __DIR__ . '/db.php';
$id  = (int)($_GET['id'] ?? 0);
$cat = $_GET['cat'] ?? '';                          // '' means All

$st = $db->prepare('SELECT * FROM colleges WHERE id=?'); $st->bind_param('i',$id); $st->execute();
$college = $st->get_result()->fetch_assoc();
if (!$college) go('index.php');

$st = $db->prepare("SELECT p.*,COALESCE(inv.stock,0) stock FROM products p LEFT JOIN (SELECT product_id,SUM(stock) stock FROM product_sizes GROUP BY product_id) inv ON inv.product_id=p.id WHERE p.college_id=? AND (?='' OR p.category=?) ORDER BY p.id DESC");
$st->bind_param("iss", $id, $cat, $cat);
$st->execute();
$products = $st->get_result();

head($college['code'] . ' Merchandise', 'index.php');
?>
<section class="page-intro"><div><p class="eyebrow">COLLEGE COLLECTION</p><h1><?= e($college['name']) ?></h1><p class="muted">Show your campus pride with official <?= e($college['code']) ?> merchandise.</p></div></section>
<div class="tabs">
  <?php foreach (['' => 'All', 'Shirts' => 'Shirts', 'Jackets' => 'Jackets', 'Accessories' => 'Accessories'] as $k => $label): ?>
    <a class="btn <?= $cat == $k ? '' : 'alt' ?>" href="?id=<?= $id ?>&cat=<?= $k ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="grid">
  <?php while ($p = $products->fetch_assoc()): ?>
    <div class="card product-card">
      <a class="product-visual" href="product.php?id=<?= (int)$p['id'] ?>"><span class="product-category"><?= e($p['category']) ?></span><span class="product-mark"><?= e(substr($college['code'],0,1)) ?></span><span class="product-college"><?= e($college['code']) ?> COLLECTION</span></a>
      <div class="product-info"><small><?= e($college['code']) ?> · <?= e($p['category']) ?></small><b><?= e($p['name']) ?></b><div class="product-bottom"><strong><?= money($p['price']) ?></strong><a class="round-link" aria-label="View <?= e($p['name']) ?>" href="product.php?id=<?= (int)$p['id'] ?>">↗</a></div></div>
    </div>
  <?php endwhile; ?>
</div>
<?php if (!$products->num_rows): ?><div class="empty-state"><span>📦</span><b>No items in this collection yet</b><p>Try another category or check back later.</p></div><?php endif; ?>
<?php foot(); ?>
