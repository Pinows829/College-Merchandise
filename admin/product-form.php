<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$product = ['name'=>'','college_id'=>'','course_id'=>0,'category'=>'Shirts','price'=>'','description'=>''];
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
    $product=['name'=>trim($_POST['name']??''),'college_id'=>(int)($_POST['college_id']??0),'course_id'=>(int)($_POST['course_id']??0),'category'=>$_POST['category']??'','price'=>(float)($_POST['price']??0),'description'=>trim($_POST['description']??'')];
    $sizesText=trim($_POST['sizes']??''); $parsed=[];
    foreach (preg_split('/\R/', $sizesText) as $line) {
        if (trim($line)==='') continue;
        if (!preg_match('/^\s*([^:]{1,30})\s*:\s*(\d+)\s*$/', $line, $m)) { $err='Enter stock one size per line using the format M:10.'; break; }
        $parsed[]=[trim($m[1]),(int)$m[2]];
    }
    if (!$err && (!$product['name'] || !$product['college_id'] || $product['price']<0 || !$parsed)) $err='Complete all product fields and add at least one size with stock.';
    if (!$err && !in_array($product['category'],['Shirts','Jackets','Accessories'],true)) $err='Choose a valid category.';
    if (!$err && $product['course_id']) {
        $checkCourse=$db->prepare('SELECT id FROM courses WHERE id=? AND college_id=?');
        $checkCourse->bind_param('ii',$product['course_id'],$product['college_id']); $checkCourse->execute();
        if (!$checkCourse->get_result()->fetch_assoc()) $err='Choose a course that belongs to the selected college.';
    }
    if (!$err) {
        $db->begin_transaction();
        try {
            if ($id) {
                if ($product['course_id']) {
                    $st=$db->prepare('UPDATE products SET college_id=?,course_id=?,name=?,category=?,description=?,price=? WHERE id=?');
                    $st->bind_param('iisssdi',$product['college_id'],$product['course_id'],$product['name'],$product['category'],$product['description'],$product['price'],$id);
                } else {
                    $st=$db->prepare('UPDATE products SET college_id=?,course_id=NULL,name=?,category=?,description=?,price=? WHERE id=?');
                    $st->bind_param('isssdi',$product['college_id'],$product['name'],$product['category'],$product['description'],$product['price'],$id);
                }
                $st->execute();
            } else {
                if ($product['course_id']) {
                    $st=$db->prepare('INSERT INTO products (college_id,course_id,name,category,description,price) VALUES (?,?,?,?,?,?)');
                    $st->bind_param('iisssd',$product['college_id'],$product['course_id'],$product['name'],$product['category'],$product['description'],$product['price']);
                } else {
                    $st=$db->prepare('INSERT INTO products (college_id,course_id,name,category,description,price) VALUES (?,NULL,?,?,?,?)');
                    $st->bind_param('isssd',$product['college_id'],$product['name'],$product['category'],$product['description'],$product['price']);
                }
                $st->execute(); $id=$db->insert_id;
            }
            $up=$db->prepare('INSERT INTO product_sizes (product_id,size,stock) VALUES (?,?,?) ON DUPLICATE KEY UPDATE stock=VALUES(stock)');
            foreach ($parsed as [$size,$stock]) { $up->bind_param('isi',$id,$size,$stock); $up->execute(); }
            $db->commit(); go('admin/products.php');
        } catch (Throwable $e) { $db->rollback(); $err='Could not save the product. Check that the product details are valid.'; }
    }
}
$colleges=$db->query("SELECT id,code,name FROM colleges WHERE code IN ('CCICT','CAS','COE','COT','COED','CME') ORDER BY FIELD(code,'CCICT','CAS','COE','COT','COED','CME')");
$courses=$db->query('SELECT id,college_id,name FROM courses ORDER BY name');
head($id?'Edit product':'Add product');
?>
<section class="page-intro"><div><p class="eyebrow">CATALOG</p><h1><?= $id?'Edit product':'Add a product' ?></h1><p class="muted">Add the details students need to choose the right merchandise.</p></div><a class="btn alt" href="<?= BASE ?>/admin/products.php">Back to products</a></section>
<form class="form card product-editor" method="post"><input type="hidden" name="id" value="<?= (int)$id ?>">
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
<label>Product name</label><input name="name" maxlength="160" value="<?= e($product['name']) ?>" required>
<div class="form-split"><div><label>College</label><select id="product-college" name="college_id" required><option value="">Select college</option><?php while($c=$colleges->fetch_assoc()): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$product['college_id']===(int)$c['id']?'selected':'' ?>><?= e($c['code'].' — '.$c['name']) ?></option><?php endwhile; ?></select></div><div><label>Course collection</label><select id="product-course" name="course_id"><option value="0">College-wide merchandise</option><?php while($course=$courses->fetch_assoc()): ?><option data-college="<?= (int)$course['college_id'] ?>" value="<?= (int)$course['id'] ?>" <?= (int)$product['course_id']===(int)$course['id']?'selected':'' ?>><?= e($course['name']) ?></option><?php endwhile; ?></select></div></div>
<label>Price (₱)</label><input type="number" name="price" min="0" step="0.01" value="<?= e($product['price']) ?>" required>
<label>Description</label><textarea name="description" rows="4" required><?= e($product['description']) ?></textarea>
<label>Sizes and stock</label><textarea name="sizes" rows="5" placeholder="S:10&#10;M:10&#10;L:8" required><?= e($sizesText) ?></textarea><small class="field-help">Enter one size per line, like <b>S:10</b>. Existing sizes not listed keep their current stock.</small>
<button class="full" type="submit">Save product</button></form>
<script>
const collegeSelect=document.getElementById('product-college');
const courseSelect=document.getElementById('product-course');
function filterCourses(){
  const collegeId=collegeSelect.value;
  [...courseSelect.options].forEach(option=>{
    const allowed=!option.dataset.college||option.dataset.college===collegeId;
    option.hidden=!allowed;
    if(!allowed&&option.selected) courseSelect.value='0';
  });
}
collegeSelect.addEventListener('change',filterCourses);
filterCourses();
</script>
<?php foot(); ?>
