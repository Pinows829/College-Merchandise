<?php
require_once __DIR__ . '/db.php';
need(['student']);

$cart = $_SESSION['cart'] ?? [];
if (!$cart) go('cart.php');
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pm  = ($_POST['pm'] ?? '') == 'GCash' ? 'GCash' : 'Cash on Pickup';
    $ref = trim($_POST['ref'] ?? '');

    if ($pm == 'GCash' && $ref === '') {
        $err = 'Enter your GCash reference number.';
    } else {
        $db->begin_transaction();
        try {
            $total = 0; $rows = [];
            $find=$db->prepare("SELECT ps.id, ps.stock, p.price, p.name FROM product_sizes ps JOIN products p ON p.id=ps.product_id WHERE ps.id=? FOR UPDATE");
            foreach ($cart as $sid => $qty) {
                // lock the row and re-check stock
                $sid=(int)$sid; $qty=min(20,max(1,(int)$qty));
                $find->bind_param('i',$sid); $find->execute(); $r=$find->get_result()->fetch_assoc();
                if (!$r || $r['stock'] < $qty) throw new Exception("Not enough stock for " . ($r['name'] ?? 'an item') . ". Please update your cart.");
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
            $upOrder=$db->prepare('UPDATE orders SET order_code=? WHERE id=?'); $upOrder->bind_param('si',$code,$oid); $upOrder->execute();

            $addItem=$db->prepare('INSERT INTO order_items(order_id,product_size_id,quantity,unit_price) VALUES(?,?,?,?)');
            $takeStock=$db->prepare('UPDATE product_sizes SET stock=stock-? WHERE id=? AND stock>=?');
            foreach ($rows as $r) {
                $addItem->bind_param('iiid',$oid,$r['id'],$r['q'],$r['price']); $addItem->execute();
                $takeStock->bind_param('iii',$r['q'],$r['id'],$r['q']); $takeStock->execute();
            }

            $db->commit();
            unset($_SESSION['cart']);
            go('orders.php?placed=' . $oid);
        } catch (Throwable $x) {
            $db->rollback();
            $err = $x->getMessage();
        }
    }
}

$total = 0; $lines = [];
$summary=$db->prepare("SELECT ps.size, p.name, p.price FROM product_sizes ps JOIN products p ON p.id=ps.product_id WHERE ps.id=?");
foreach ($cart as $sid => $qty) {
    $sid=(int)$sid; $qty=min(20,max(1,(int)$qty)); $summary->bind_param('i',$sid); $summary->execute(); $r=$summary->get_result()->fetch_assoc();
    if (!$r) continue;
    $total += $r['price'] * $qty;
    $lines[] = [$r, $qty];
}
if (!$lines) go('cart.php');

head('Checkout', 'cart.php');
if ($err) echo "<div class='err'>" . e($err) . "</div>";
?>
<div class="card checkout-card">
  <h2>Order summary</h2>
  <?php foreach ($lines as [$r, $qty]): ?>
    <div class="row"><span><?= e($r['name']) ?> (<?= e($r['size']) ?>) × <?= $qty ?></span>
    <b style="margin-left:auto"><?= money($r['price'] * $qty) ?></b></div>
  <?php endforeach; ?>
  <h3>Total <?= money($total) ?></h3>

  <form method="post">
    <label>Payment method</label>
    <select name="pm"><option>GCash</option><option>Cash on Pickup</option></select>

    <label>GCash reference number (required for GCash)</label>
    <input name="ref" maxlength="100">

    <button class="full">Place order</button>
  </form>
</div>
<?php foot(); ?>
