<?php
require_once __DIR__ . '/db.php';
$id  = (int)($_GET['id'] ?? 0);
$cat = $_GET['cat'] ?? '';
$courseId = (int)($_GET['course'] ?? 0);

$st = $db->prepare("SELECT * FROM colleges WHERE id=? AND code IN ('CCICT','CAS','COE','COT','COED','CME')"); $st->bind_param('i',$id); $st->execute();
$college = $st->get_result()->fetch_assoc();
if (!$college) go('index.php');

$courseQuery=$db->prepare('SELECT id,name FROM courses WHERE college_id=? ORDER BY name'); $courseQuery->bind_param('i',$id); $courseQuery->execute(); $courses=$courseQuery->get_result();
$validCourse=false; foreach ($courses as $courseRow) if ((int)$courseRow['id']===$courseId) $validCourse=true;
$courses->data_seek(0);
if (!$validCourse) $courseId=0;

$st = $db->prepare("SELECT p.*,course.name course_name,COALESCE(inv.stock,0) stock FROM products p LEFT JOIN courses course ON course.id=p.course_id LEFT JOIN (SELECT product_id,SUM(stock) stock FROM product_sizes GROUP BY product_id) inv ON inv.product_id=p.id WHERE p.college_id=? AND (?=0 OR p.course_id=? OR p.course_id IS NULL) AND (?='' OR p.category=?) ORDER BY p.id DESC");
$st->bind_param("iiiss", $id, $courseId, $courseId, $cat, $cat);
$st->execute();
$products = $st->get_result();

head($college['code'] . ' Merchandise', 'index.php');
?>
<section class="page-intro"><div><p class="eyebrow">COLLEGE COLLECTION</p><h1><?= e($college['code']) ?></h1><p class="muted"><?= e($college['name']) ?> merchandise for campus and beyond.</p></div></section>
<?php if ($courses->num_rows): ?><form class="course-filter" method="get"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="cat" value="<?= e($cat) ?>"><label for="course-filter">Shop by course</label><select id="course-filter" name="course" onchange="this.form.submit()"><option value="0">All courses</option><?php while($course=$courses->fetch_assoc()): ?><option value="<?= (int)$course['id'] ?>" <?= (int)$course['id']===$courseId?'selected':'' ?>><?= e($course['name']) ?></option><?php endwhile; ?></select></form><?php endif; ?>
<div class="tabs">
  <?php foreach (['' => 'All', 'Shirts' => 'Shirts', 'Jackets' => 'Jackets', 'Accessories' => 'Accessories'] as $k => $label): ?>
    <a class="btn <?= $cat == $k ? '' : 'alt' ?>" href="?id=<?= $id ?>&course=<?= $courseId ?>&cat=<?= rawurlencode($k) ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="grid">
  <?php while ($p = $products->fetch_assoc()): ?>
    <div class="card product-card">
      <a class="product-visual" href="product.php?id=<?= (int)$p['id'] ?>"><img src="<?= BASE ?>/assets/merch-<?= e(strtolower($p['category'])) ?>.jpg" alt="" loading="lazy"><span class="product-category"><?= e($p['category']) ?></span><span class="product-college"><?= e($college['code']) ?> COLLECTION</span></a>
      <div class="product-info"><small><?= e($college['code']) ?><?= $p['course_name'] ? ' · '.e($p['course_name']) : '' ?> · <?= e($p['category']) ?></small><b><?= e($p['name']) ?></b><div class="product-bottom"><strong><?= money($p['price']) ?></strong><a class="round-link" aria-label="View <?= e($p['name']) ?>" href="product.php?id=<?= (int)$p['id'] ?>">↗</a></div></div>
    </div>
  <?php endwhile; ?>
</div>
<?php if (!$products->num_rows): ?><div class="empty-state"><span>📦</span><b>No items in this collection yet</b><p>Try another category or check back later.</p></div><?php endif; ?>
<?php foot(); ?>
