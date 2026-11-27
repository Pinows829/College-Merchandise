<?php
require_once __DIR__ . '/db.php';
$q = trim($_GET['q'] ?? '');                       // what the student typed in search
$colleges = $db->query("SELECT * FROM colleges WHERE code IN ('CCICT','CAS','COE','COT','COED','CME') ORDER BY FIELD(code,'CCICT','CAS','COE','COT','COED','CME')");

$st = $db->prepare("SELECT p.*, c.code college_code, course.name course_name, COALESCE(inv.stock,0) stock FROM products p JOIN colleges c ON c.id=p.college_id LEFT JOIN courses course ON course.id=p.course_id LEFT JOIN (SELECT product_id,SUM(stock) stock FROM product_sizes GROUP BY product_id) inv ON inv.product_id=p.id WHERE c.code IN ('CCICT','CAS','COE','COT','COED','CME') AND p.name LIKE ? ORDER BY p.id DESC LIMIT 12");
$like = "%$q%";
$st->bind_param("s", $like);
$st->execute();
$products = $st->get_result();

head('College Merchandise');
?>
<section class="hero-panel"><div class="hero-copy"><p class="eyebrow">YOUR CAMPUS. YOUR PRIDE.</p><h1>Wear your<br><span>college spirit.</span></h1><p>Find official college merchandise made for campus life.</p><a class="btn" href="#catalog">Explore the collection <span aria-hidden="true">→</span></a></div><div class="hero-art" aria-hidden="true"><div class="hero-circle"><span>CM</span></div><i class="spark spark-one">✦</i><i class="spark spark-two">✦</i><span class="hero-note">Made for<br>campus days</span></div></section>
<form class="search-form" role="search"><span class="search-icon" aria-hidden="true">⌕</span><input name="q" value="<?= e($q) ?>" placeholder="Search shirts, hoodies, accessories..." aria-label="Search products"><button type="submit">Search</button></form>

<?php if (!$q): ?>
  <div class="section-heading"><div><p class="eyebrow">FIND YOUR COLLECTION</p><h2>Shop by college</h2></div></div>
  <div class="college-grid">
    <?php while ($c = $colleges->fetch_assoc()): ?>
      <a class="college-tile" href="college.php?id=<?= (int)$c['id'] ?>"><span class="college-emblem"><?= e(substr($c['code'],0,1)) ?></span><span><b><?= e($c['code']) ?></b><small><?= e($c['name']) ?></small></span><span class="tile-arrow">↗</span></a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<section id="catalog" class="catalog-section"><div class="section-heading"><div><p class="eyebrow"><?= $q ? 'SEARCH' : 'CAMPUS FAVORITES' ?></p><h2><?= $q ? 'Search results' : 'Featured merchandise' ?></h2></div><span class="result-count"><?= $products->num_rows ?> items</span></div>
<div class="grid">
  <?php while ($p = $products->fetch_assoc()): ?>
    <div class="card product-card">
      <a class="product-visual" href="product.php?id=<?= (int)$p['id'] ?>"><span class="product-category"><?= e($p['category']) ?></span><span class="product-mark"><?= e(substr($p['college_code'],0,1)) ?></span><span class="product-college"><?= e($p['college_code']) ?> COLLECTION</span></a>
      <div class="product-info"><small><?= e($p['college_code']) ?><?= $p['course_name'] ? ' · '.e($p['course_name']) : '' ?> · <?= e($p['category']) ?></small><b><?= e($p['name']) ?></b><div class="product-bottom"><strong><?= money($p['price']) ?></strong><a class="round-link" aria-label="View <?= e($p['name']) ?>" href="product.php?id=<?= (int)$p['id'] ?>">↗</a></div></div>
    </div>
  <?php endwhile; ?>
</div>
<?php if (!$products->num_rows): ?><div class="empty-state"><span>⌕</span><b>No products found</b><p>Try another search term.</p></div><?php endif; ?></section>
<?php foot(); ?>
