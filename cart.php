<?php
include 'Includes/db.php';
need(['student']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['act'] == 'add' && isset($_POST['size'])) {
        $sid = (int)$_POST['size'];
        $_SESSION['cart'][$sid] = ($_SESSION['cart'][$sid] ?? 0) + max(1, (int)$_POST['qty']);
    }
    if ($_POST['act'] == 'del') {
        unset($_SESSION['cart'][(int)$_POST['size']]);
    }
    go('cart.php');
}

$items = []; $total = 0;
foreach ($_SESSION['cart'] ?? [] as $sid => $qty) {
    $r = $db->query("SELECT ps.id, ps.size, p.name, p.price
                     FROM product_sizes ps JOIN products p ON p.id = ps.product_id
                     WHERE ps.id = " . (int)$sid)->fetch_assoc();
    if ($r) { $r['qty'] = $qty; $total += $r['price'] * $qty; $items[] = $r; }
}

head('Your cart', 'index.php');

if (!$items) {
    echo '<p>Your cart is empty. <a href="index.php">Browse merchandise</a></p>';
} else { ?>
<table>
  <tr><th>Item</th><th>Qty</th><th>Price</th><th></th></tr>
  <?php foreach ($items as $i): ?>
  <tr>
    <td><?= e($i['name']) ?> (<?= e($i['size']) ?>)</td>
    <td><?= $i['qty'] ?></td>
    <td>₱<?= number_format($i['price'] * $i['qty']) ?></td>
    <td>
      <form method="post">
        <input type="hidden" name="act" value="del">
        <input type="hidden" name="size" value="<?= $i['id'] ?>">
        <button>Remove</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="2">Total</th><th colspan="2">₱<?= number_format($total) ?></th></tr>
</table>
<p>
  <a class="btn alt" href="index.php">Continue shopping</a>
  <a class="btn" href="checkout.php">Proceed to payment</a>
</p>
<?php }
foot(); ?>