<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);
$products = $db->query("SELECT p.id,p.name,p.category,p.price,c.code,COALESCE(inv.stock,0) stock
                        FROM products p JOIN colleges c ON c.id=p.college_id
                        LEFT JOIN (SELECT product_id,SUM(stock) stock FROM product_sizes GROUP BY product_id) inv ON inv.product_id=p.id
                        ORDER BY p.id DESC");
head('Manage products');
?>
<section class="page-intro"><div><p class="eyebrow">CATALOG</p><h1>Products</h1><p class="muted">Add merchandise and keep product details and stock up to date.</p></div><a class="btn" href="<?= BASE ?>/admin/product-form.php">＋ Add product</a></section>
<section class="panel">
<?php if (!$products->num_rows): ?><div class="empty-state"><span>📦</span><b>Your catalog is empty</b><p>Add the first product to start selling.</p><a class="btn" href="<?= BASE ?>/admin/product-form.php">Add a product</a></div>
<?php else: ?><div class="table-wrap"><table><thead><tr><th>Product</th><th>College</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody>
<?php while ($p = $products->fetch_assoc()): ?><tr><td><b><?= e($p['name']) ?></b></td><td><?= e($p['code']) ?></td><td><?= e($p['category']) ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?> units</td><td><a class="btn btn-small alt" href="<?= BASE ?>/admin/product-form.php?id=<?= (int)$p['id'] ?>">Edit</a></td></tr><?php endwhile; ?>
</tbody></table></div><?php endif; ?>
</section>
<?php foot(); ?>
