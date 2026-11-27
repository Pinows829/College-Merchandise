<?php
require_once dirname(__DIR__) . '/db.php';
need(['admin']);
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $collegeId=(int)($_POST['college_id']??0);
    $name=trim($_POST['name']??'');
    if (!$collegeId || !$name) $err='Choose a college and enter a course name.';
    else {
        try {
            $st=$db->prepare('INSERT INTO courses (college_id,name) VALUES (?,?)');
            $st->bind_param('is',$collegeId,$name); $st->execute();
            go('admin/courses.php');
        } catch (mysqli_sql_exception $exception) { $err='That course is already listed for this college.'; }
    }
}
$colleges=$db->query("SELECT id,code,name FROM colleges WHERE code IN ('CCICT','CAS','COE','COT','COED','CME') ORDER BY FIELD(code,'CCICT','CAS','COE','COT','COED','CME')");
$courses=$db->query("SELECT courses.id,courses.name,colleges.code,colleges.name college_name FROM courses JOIN colleges ON colleges.id=courses.college_id WHERE colleges.code IN ('CCICT','CAS','COE','COT','COED','CME') ORDER BY FIELD(colleges.code,'CCICT','CAS','COE','COT','COED','CME'),courses.name");
head('Manage courses');
?>
<section class="page-intro"><div><p class="eyebrow">COLLECTIONS</p><h1>Course collections</h1><p class="muted">Add the courses students can shop by inside each college.</p></div></section>
<div class="admin-columns course-admin-columns">
<form class="card course-editor" method="post"><p class="eyebrow">NEW COLLECTION</p><h2>Add a course</h2><?php if($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
<label>College</label><select name="college_id" required><option value="">Select college</option><?php while($c=$colleges->fetch_assoc()): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['code'].' — '.$c['name']) ?></option><?php endwhile; ?></select>
<label>Course name</label><input name="name" maxlength="180" placeholder="e.g. Bachelor of Science in Information Technology (BSIT)" required>
<button class="full" type="submit">Add course</button></form>
<section class="panel course-list-panel"><div class="panel-heading"><div><p class="eyebrow">AVAILABLE FILTERS</p><h2>Courses</h2></div><span class="result-count"><?= $courses->num_rows ?> listed</span></div>
<?php if(!$courses->num_rows): ?><div class="empty-state compact"><span>＋</span><b>No course collections yet</b><p>Add course names to make them available in the shop.</p></div><?php else: ?><ul class="course-list"><?php while($c=$courses->fetch_assoc()): ?><li><span class="college-code-pill"><?= e($c['code']) ?></span><span><?= e($c['name']) ?></span></li><?php endwhile; ?></ul><?php endif; ?>
</section></div>
<?php foot(); ?>
