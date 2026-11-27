<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$product = ['name'=>'','college_id'=>'','category'=>'Shirts','price'=>'','description'=>''];
$sizesText = "S:0\nM:0\nL:0\nXL:0";
$err = '';
if ($id) {
    $st = $db->prepare('SELECT * FROM products WHERE id=?'); $st->bind_param('i',$id); $st->execute();
    $product = $st->get_result()->fetch_assoc();
    if (!$product) go('admin/products.php');
    $sz = $db->prepare('SELECT size,stock FROM product_sizes WHERE product_id=? ORDER BY id'); $sz->bind_param('i',$id); $sz->execute();
    $list=[]; foreach ($sz->get_result() as $s) $list[]=$s['size'].':'.$s['stock'];
    if ($list) $sizesText=implode("\n",$list);
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $product=['name'=>trim($_POST['name']??''),'college_id'=>(int)($_POST['college_id']??0),'category'=>$_POST['category']??'','price'=>(float)($_POST['price']??0),'description'=>trim($_POST['description']??'')];
    $sizesText=trim($_POST['sizes']??''); $parsed=[];
    foreach (preg_split('/\R/', $sizesText) as $line) {
        if (trim($line)==='') continue;
        if (!preg_match('/^\s*([^:]{1,30})\s*:\s*(\d+)\s*$/', $line, $m)) { $err='Enter stock one size per line using the format M:10.'; break; }
        $parsed[]=[trim($m[1]),(int)$m[2]];
    }
    if (!$err && (!$product['name'] || !$product['college_id'] || $product['price']<0 || !$parsed)) $err='Complete all product fields and add at least one size with stock.';
    if (!$err && !in_array($product['category'],['Shirts','Jackets','Accessories'],true)) $err='Choose a valid category.';
    if (!$err) {
        $db->begin_transaction();
        try {
            if ($id) {
                $st=$db->prepare('UPDATE products SET college_id=?,name=?,category=?,description=?,price=? WHERE id=?');
                $st->bind_param('isssdi',$product['college_id'],$product['name'],$product['category'],$product['description'],$product['price'],$id); $st->execute();
            } else {
                $st=$db->prepare('INSERT INTO products (college_id,name,category,description,price) VALUES (?,?,?,?,?)');
                $st->bind_param('isssd',$product['college_id'],$product['name'],$product['category'],$product['description'],$product['price']); $st->execute(); $id=$db->insert_id;
            }
            $up=$db->prepare('INSERT INTO product_sizes (product_id,size,stock) VALUES (?,?,?) ON DUPLICATE KEY UPDATE stock=VALUES(stock)');
            foreach ($parsed as [$size,$stock]) { $up->bind_param('isi',$id,$size,$stock); $up->execute(); }
            $db->commit(); go('admin/products.php');
        } catch (Throwable $e) { $db->rollback(); $err='Could not save the product. Check that the product details are valid.'; }
    }
}
$colleges=$db->query('SELECT id,code,name FROM colleges ORDER BY code');
head($id?'Edit product':'Add product');
?>
<section class="page-intro"><div><p class="eyebrow">CATALOG</p><h1><?= $id?'Edit product':'Add a product' ?></h1><p class="muted">Add the details students need to choose the right merchandise.</p></div><a class="btn alt" href="<?= BASE ?>/admin/products.php">Back to products</a></section>
<form class="form card product-editor" method="post"><input type="hidden" name="id" value="<?= (int)$id ?>">
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
<label>Product name</label><input name="name" maxlength="160" value="<?= e($product['name']) ?>" required>
<div class="form-split"><div><label>College</label><select name="college_id" required><option value="">Select college</option><?php while($c=$colleges->fetch_assoc()): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$product['college_id']===(int)$c['id']?'selected':'' ?>><?= e($c['code'].' — '.$c['name']) ?></option><?php endwhile; ?></select></div><div><label>Category</label><select name="category"><?php foreach(['Shirts','Jackets','Accessories'] as $cat): ?><option <?= $product['category']===$cat?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?></select></div></div>
<label>Price (₱)</label><input type="number" name="price" min="0" step="0.01" value="<?= e($product['price']) ?>" required>
<label>Description</label><textarea name="description" rows="4" required><?= e($product['description']) ?></textarea>
<label>Sizes and stock</label><textarea name="sizes" rows="5" placeholder="S:10&#10;M:10&#10;L:8" required><?= e($sizesText) ?></textarea><small class="field-help">Enter one size per line, like <b>S:10</b>. Existing sizes not listed keep their current stock.</small>
<button class="full" type="submit">Save product</button></form>
<?php foot(); ?>
