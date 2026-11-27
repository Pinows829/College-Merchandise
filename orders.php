<?php
require_once __DIR__ . '/db.php';
need(['student']);

$uid = me()['id'];
$orders = $db->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY id DESC");
$steps = ['Pending','Paid','Preparing','Ready for Pickup','Completed'];

head('My orders', 'index.php');
?>
<section class="page-intro"><div><p class="eyebrow">YOUR CAMPUS PURCHASES</p><h1>My orders</h1><p class="muted">Follow each order from confirmation to pickup.</p></div><a class="btn alt" href="index.php">Continue shopping</a></section>
<?php
if (isset($_GET['placed'])) echo '<div class="ok">Order placed! We’ll update your status here. Pick up at the college office when your order is ready.</div>';
if (!$orders->num_rows) echo '<div class="empty-state"><span>🧾</span><b>No orders yet</b><p>Your orders will appear here after checkout.</p><a class="btn" href="index.php">Browse merchandise</a></div>';

while ($o = $orders->fetch_assoc()):
    $items = $db->query("SELECT p.name, ps.size, oi.quantity, oi.unit_price
                         FROM order_items oi
                         JOIN product_sizes ps ON ps.id = oi.product_size_id
                         JOIN products p ON p.id = ps.product_id
                         WHERE oi.order_id = {$o['id']}");
?>
<div class="card order-card">
  <div class="row">
    <div><small class="eyebrow">ORDER</small><b><?= e($o['order_code'] ?: '#'.$o['id']) ?></b></div>
    <span class="status status-<?= e(strtolower(str_replace(' ','-',$o['status']))) ?>"><?= e($o['status']) ?></span>
    <span class="muted order-date"><?= e(date('d M Y', strtotime($o['created_at']))) ?></span>
  </div>
  <div class="order-lines">
  <?php while ($i = $items->fetch_assoc()): ?>
    <div><span><?= e($i['name']) ?> <small><?= e($i['size']) ?> · Qty <?= (int)$i['quantity'] ?></small></span><b><?= money($i['unit_price'] * $i['quantity']) ?></b></div>
  <?php endwhile; ?>
  </div><div class="order-total"><span><?= e($o['payment_method']) ?><?php if($o['gcash_ref']): ?> · Ref <?= e($o['gcash_ref']) ?><?php endif; ?></span><b>Total <?= money($o['total']) ?></b></div>
  <div class="order-progress">
    <?php $idx = array_search($o['status'], $steps);
    foreach ($steps as $k => $s): ?><span class="progress-step <?= ($idx !== false && $k <= $idx ? 'is-done' : '') ?>"><i><?= ($idx !== false && $k < $idx ? '✓' : $k+1) ?></i><small><?= e($s) ?></small></span><?php endforeach; ?>
  </div>
</div>
<?php endwhile; foot(); ?>
