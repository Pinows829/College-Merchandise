<?php
include 'Includes/db.php';
need(['student']);

$uid = me()['id'];
$orders = $db->query("SELECT * FROM orders WHERE user_id=$uid ORDER BY id DESC");
$steps = ['Pending','Paid','Preparing','Ready for Pickup','Completed'];

head('My orders', 'index.php');
if (isset($_GET['placed'])) echo '<div class="ok">Order placed! Pick up at the college office, Mon–Fri 9am–4pm.</div>';
if (!$orders->num_rows) echo '<p>No orders yet.</p>';

while ($o = $orders->fetch_assoc()):
    $items = $db->query("SELECT p.name, ps.size, oi.quantity, oi.unit_price
                         FROM order_items oi
                         JOIN product_sizes ps ON ps.id = oi.product_size_id
                         JOIN products p ON p.id = ps.product_id
                         WHERE oi.order_id = {$o['id']}");
?>
<div class="card" style="margin-bottom:10px">
  <div class="row">
    <b><?= e($o['order_code']) ?></b>
    <span class="badge"><?= e($o['status']) ?></span>
    <span class="muted" style="margin-left:auto"><?= e(date('d M Y', strtotime($o['created_at']))) ?></span>
  </div>
  <?php while ($i = $items->fetch_assoc()): ?>
    <div><?= e($i['name']) ?> (<?= e($i['size']) ?>) × <?= $i['quantity'] ?> — ₱<?= number_format($i['unit_price'] * $i['quantity']) ?></div>
  <?php endwhile; ?>
  <div><b>Total ₱<?= number_format($o['total']) ?></b> · <?= e($o['payment_method']) ?></div>
  <div class="muted">
    <?php $idx = array_search($o['status'], $steps);
    foreach ($steps as $k => $s) echo ($idx !== false && $k <= $idx ? '✓ ' : '○ ') . e($s) . ' &nbsp; '; ?>
  </div>
</div>
<?php endwhile; foot(); ?>