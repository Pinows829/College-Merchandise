<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);

$summary = [
    'products' => (int)$db->query("SELECT COUNT(*) n FROM products")->fetch_assoc()['n'],
    'orders' => (int)$db->query("SELECT COUNT(*) n FROM orders")->fetch_assoc()['n'],
    'pending' => (int)$db->query("SELECT COUNT(*) n FROM orders WHERE status='Pending'")->fetch_assoc()['n'],
    'revenue' => (float)$db->query("SELECT COALESCE(SUM(total),0) n FROM orders WHERE status IN ('Paid','Preparing','Ready for Pickup','Completed')")->fetch_assoc()['n'],
    'stock' => (int)$db->query("SELECT COALESCE(SUM(stock),0) n FROM product_sizes")->fetch_assoc()['n'],
];
$recent = $db->query("SELECT o.id,o.order_code,o.total,o.status,o.created_at,u.full_name
                      FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 8");
$lowStock = $db->query("SELECT p.name, ps.size, ps.stock FROM product_sizes ps
                        JOIN products p ON p.id=ps.product_id WHERE ps.stock <= 5
                        ORDER BY ps.stock ASC, p.name LIMIT 6");
head('Admin dashboard');
?>
<section class="page-intro">
  <div><p class="eyebrow">STORE MANAGEMENT</p><h1>Dashboard</h1><p class="muted">A quick view of your campus merchandise store.</p></div>
  <a class="btn" href="<?= BASE ?>/admin/product-form.php">＋ Add product</a>
</section>
<section class="stats-grid">
  <article class="stat-card"><span class="stat-icon">▦</span><span class="stat-label">Products</span><strong><?= $summary['products'] ?></strong><small>Listed in your store</small></article>
  <article class="stat-card"><span class="stat-icon gold-icon">↗</span><span class="stat-label">Orders</span><strong><?= $summary['orders'] ?></strong><small><?= $summary['pending'] ?> pending attention</small></article>
  <article class="stat-card"><span class="stat-icon red-icon">₱</span><span class="stat-label">Confirmed sales</span><strong><?= money($summary['revenue']) ?></strong><small>Paid and fulfilled orders</small></article>
  <article class="stat-card"><span class="stat-icon">□</span><span class="stat-label">Units in stock</span><strong><?= $summary['stock'] ?></strong><small>Across all sizes</small></article>
</section>
<div class="admin-columns">
  <section class="panel">
    <div class="panel-heading"><div><p class="eyebrow">LATEST ACTIVITY</p><h2>Recent orders</h2></div><a class="text-link" href="<?= BASE ?>/admin/orders.php">View all →</a></div>
    <?php if (!$recent->num_rows): ?><div class="empty-state"><span>🧾</span><b>No orders yet</b><p>New student orders will appear here.</p></div>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>Order</th><th>Student</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody>
      <?php while ($o = $recent->fetch_assoc()): ?><tr>
        <td><a class="text-link" href="<?= BASE ?>/admin/orders.php?id=<?= (int)$o['id'] ?>"><?= e($o['order_code'] ?: ('#'.$o['id'])) ?></a></td>
        <td><?= e($o['full_name']) ?></td><td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
        <td><?= money($o['total']) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ','-', $o['status']))) ?>"><?= e($o['status']) ?></span></td>
      </tr><?php endwhile; ?>
    </tbody></table></div><?php endif; ?>
  </section>
  <aside class="panel stock-panel">
    <div class="panel-heading"><div><p class="eyebrow">INVENTORY</p><h2>Low stock</h2></div><a class="text-link" href="<?= BASE ?>/admin/products.php">Manage →</a></div>
    <?php if (!$lowStock->num_rows): ?><div class="empty-state compact"><span>✓</span><b>Stock looks good</b><p>No sizes are running low.</p></div>
    <?php else: ?><ul class="stock-list"><?php while ($s = $lowStock->fetch_assoc()): ?><li><span><b><?= e($s['name']) ?></b><small>Size <?= e($s['size']) ?></small></span><span class="stock-pill <?= $s['stock'] <= 2 ? 'stock-critical' : '' ?>"><?= (int)$s['stock'] ?> left</span></li><?php endwhile; ?></ul><?php endif; ?>
  </aside>
</div>
<?php foot(); ?>
