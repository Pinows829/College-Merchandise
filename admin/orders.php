<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);
$states=['Pending','Paid','Preparing','Ready for Pickup','Completed'];
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $oid=(int)($_POST['order_id']??0); $status=$_POST['status']??'';
    if ($oid>0 && in_array($status,$states,true)) { $st=$db->prepare('UPDATE orders SET status=? WHERE id=?'); $st->bind_param('si',$status,$oid); $st->execute(); }
    go('admin/orders.php'.($oid?'?id='.$oid:''));
}
$selected=(int)($_GET['id']??0);
$sql="SELECT o.*,u.full_name,u.student_id,u.email FROM orders o JOIN users u ON u.id=o.user_id";
$orders=$db->query($sql.' ORDER BY o.id DESC');
$detail=null; $items=null;
if ($selected) {
    $st=$db->prepare('SELECT o.*,u.full_name,u.student_id,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?'); $st->bind_param('i',$selected); $st->execute(); $detail=$st->get_result()->fetch_assoc();
    if($detail){$st=$db->prepare('SELECT p.name,ps.size,oi.quantity,oi.unit_price FROM order_items oi JOIN product_sizes ps ON ps.id=oi.product_size_id JOIN products p ON p.id=ps.product_id WHERE oi.order_id=?');$st->bind_param('i',$selected);$st->execute();$items=$st->get_result();}
}
head('Manage orders');
?>
<section class="page-intro"><div><p class="eyebrow">FULFILLMENT</p><h1>Orders</h1><p class="muted">Review student purchases and keep each order’s status current.</p></div></section>
<?php if($detail): ?><section class="panel order-detail"><div class="panel-heading"><div><p class="eyebrow">ORDER DETAILS</p><h2><?= e($detail['order_code']?:('#'.$detail['id'])) ?></h2></div><a class="text-link" href="<?= BASE ?>/admin/orders.php">Close details ×</a></div>
<div class="detail-grid"><div><small>Student</small><b><?= e($detail['full_name']) ?></b><span><?= e($detail['student_id']) ?> · <?= e($detail['email']) ?></span></div><div><small>Payment</small><b><?= e($detail['payment_method']) ?></b><span><?= $detail['gcash_ref']? 'Ref: '.e($detail['gcash_ref']):'Pickup payment' ?></span></div><div><small>Placed</small><b><?= e(date('M j, Y · g:i a',strtotime($detail['created_at']))) ?></b><span>Total <?= money($detail['total']) ?></span></div></div>
<div class="table-wrap"><table><thead><tr><th>Item</th><th>Size</th><th>Qty</th><th>Unit price</th></tr></thead><tbody><?php while($i=$items->fetch_assoc()): ?><tr><td><?= e($i['name']) ?></td><td><?= e($i['size']) ?></td><td><?= (int)$i['quantity'] ?></td><td><?= money($i['unit_price']) ?></td></tr><?php endwhile; ?></tbody></table></div>
<form class="status-form" method="post"><input type="hidden" name="order_id" value="<?= (int)$detail['id'] ?>"><label>Update status</label><select name="status"><?php foreach($states as $s): ?><option <?= $detail['status']===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><button type="submit">Save status</button></form></section><?php elseif($selected): ?><div class="err">Order not found.</div><?php endif; ?>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow">ALL PURCHASES</p><h2>Order history</h2></div></div>
<?php if(!$orders->num_rows): ?><div class="empty-state"><span>🧾</span><b>No orders yet</b><p>Student orders will show up here.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Order</th><th>Student</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody><?php while($o=$orders->fetch_assoc()): ?><tr><td><b><?= e($o['order_code']?:('#'.$o['id'])) ?></b></td><td><?= e($o['full_name']) ?></td><td><?= e(date('M j, Y',strtotime($o['created_at']))) ?></td><td><?= e($o['payment_method']) ?></td><td><?= money($o['total']) ?></td><td><span class="status status-<?= e(strtolower(str_replace(' ','-',$o['status']))) ?>"><?= e($o['status']) ?></span></td><td><a class="btn btn-small alt" href="<?= BASE ?>/admin/orders.php?id=<?= (int)$o['id'] ?>">Details</a></td></tr><?php endwhile; ?></tbody></table></div><?php endif; ?></section>
<?php foot(); ?>
