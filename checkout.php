<?php
include 'Includes/db.php';
need(['student']);

$cart = $_SESSION['cart'] ?? [];
if (!$cart) go('cart.php');
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pm  = $_POST['pm'] == 'GCash' ? 'GCash' : 'Cash on Pickup';
    $ref = trim($_POST['ref'] ?? '');

    if ($pm == 'GCash' && $ref === '') {
        $err = 'Enter your GCash reference number.';
    } else {
        $db->begin_transaction();
        try {
            $total = 0; $rows = [];
            foreach ($cart as $sid => $qty) {
                // lock the row and re-check stock
                $r = $db->query("SELECT ps.id, ps.stock, p.price, p.name
                                 FROM product_sizes ps JOIN products p ON p.id = ps.product_id
                                 WHERE ps.id = " . (int)$sid . " FOR UPDATE")->fetch_assoc();
                if (!$r || $r['stock'] < $qty) throw new Exception("Not enough stock for " . ($r['name'] ?? 'an item') . ".");
                $r['q'] = $qty;
                $total += $r['price'] * $qty;
                $rows[] = $r;
            }

            $uid = me()['id'];
            $st = $db->prepare("INSERT INTO orders(user_id,total,payment_method,gcash_ref) VALUES(?,?,?,?)");
            $st->bind_param("idss", $uid, $total, $pm, $ref);
            $st->execute();
            $oid  = $db->insert_id;
            $code = 'ORD-' . date('Y') . '-' . str_pad($oid, 3, '0', STR_PAD_LEFT);
            $db->query("UPDATE orders SET order_code='$code' WHERE id=$oid");

            foreach ($rows as $r) {
                $db->query("INSERT INTO order_items(order_id,product_size_id,quantity,unit_price)
                            VALUES($oid,{$r['id']},{$r['q']},{$r['price']})");
                $db->query("UPDATE product_sizes SET stock = stock - {$r['q']} WHERE id = {$r['id']}");
            }

            $db->commit();
            unset($_SESSION['cart']);
            go('orders.php?placed=' . $oid);
        } catch (Exception $x) {
            $db->rollback();
            $err = $x->getMessage();
        }
    }
}

$total = 0; $lines = [];
foreach ($cart as $sid => $qty) {
    $r = $db->query("SELECT ps.size, p.name, p.price FROM product_sizes ps
                     JOIN products p ON p.id = ps.product_id WHERE ps.id = " . (int)$sid)->fetch_assoc();
    $total += $r['price'] * $qty;
    $lines[] = [$r, $qty];
}

head('Checkout', 'cart.php');
if ($err) echo "<div class='err'>" . e($err) . "</div>";
?>
<div class="card">
  <h2>Order summary</h2>
  <?php foreach ($lines as [$r, $qty]): ?>
    <div class="row"><span><?= e($r['name']) ?> (<?= e($r['size']) ?>) × <?= $qty ?></span>
    <b style="margin-left:auto">₱<?= number_format($r['price'] * $qty) ?></b></div>
  <?php endforeach; ?>
  <h3>Total ₱<?= number_format($total) ?></h3>

  <form method="post">
    <label>Payment method</label>
    <select name="pm"><option>GCash</option><option>Cash on Pickup</option></select>

    <label>GCash reference number (if paying by GCash)</label>
    <input name="ref">

    <button class="full">Place order</button>
  </form>
</div>
<?php foot(); ?>