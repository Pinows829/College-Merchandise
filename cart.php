<?php
require_once __DIR__ . '/db.php';
need(['student']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['act'] ?? '';
    $sid = (int)($_POST['size'] ?? 0);
    if ($action === 'add' && $sid > 0) {
        $qty = min(20, max(1, (int)($_POST['qty'] ?? 1)));
        $st=$db->prepare('SELECT stock FROM product_sizes WHERE id=?'); $st->bind_param('i',$sid); $st->execute();
        $sizeRow=$st->get_result()->fetch_assoc();
        $cartQty=(int)($_SESSION['cart'][$sid] ?? 0);
        if ($sizeRow && $sizeRow['stock'] > 0 && $cartQty < $sizeRow['stock']) {
            $_SESSION['cart'][$sid]=min($cartQty+$qty,(int)$sizeRow['stock']);
            $_SESSION['flash']='Item added to your cart.';
        } else $_SESSION['flash']='That size is currently out of stock.';
    }
    if ($action === 'del') {
        unset($_SESSION['cart'][$sid]);
    }
    go('cart.php');
}

$items = []; $total = 0;
$st=$db->prepare("SELECT ps.id, ps.size, ps.stock, p.name, p.price FROM product_sizes ps JOIN products p ON p.id=ps.product_id WHERE ps.id=?");
foreach ($_SESSION['cart'] ?? [] as $sid => $qty) {
    $sid=(int)$sid; $st->bind_param('i',$sid); $st->execute(); $r=$st->get_result()->fetch_assoc();
    if ($r) { $r['qty'] = (int)$qty; $total += $r['price'] * $r['qty']; $items[] = $r; }
}

head('Your cart', 'index.php');

if (!empty($_SESSION['flash'])) { echo '<div class="ok">'.e($_SESSION['flash']).'</div>'; unset($_SESSION['flash']); }
if (!$items) {
    echo '<div class="empty-state"><span>🛍</span><b>Your cart is waiting for something nice</b><p>Browse the college collections and add your favorites.</p><a class="btn" href="index.php">Browse merchandise</a></div>';
} else { ?>
<div class="section-heading"><div><p class="eyebrow">READY WHEN YOU ARE</p><h2>Your cart</h2></div></div><div class="table-wrap"><table>
  <tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr>
  <?php foreach ($items as $i): ?>
  <tr>
    <td><?= e($i['name']) ?> (<?= e($i['size']) ?>)</td>
    <td><?= $i['qty'] ?><?php if($i['qty']>$i['stock']): ?><small class="stock-warning"> Only <?= (int)$i['stock'] ?> left</small><?php endif; ?></td>
    <td><?= money($i['price'] * $i['qty']) ?></td>
    <td>
      <form method="post">
        <input type="hidden" name="act" value="del">
        <input type="hidden" name="size" value="<?= $i['id'] ?>">
        <button>Remove</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="2">Total</th><th colspan="2"><?= money($total) ?></th></tr>
</table></div>
<p>
  <a class="btn alt" href="index.php">Continue shopping</a>
  <a class="btn" href="checkout.php">Proceed to payment</a>
</p>
<?php }
foot(); ?>
