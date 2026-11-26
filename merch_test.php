<?php
include 'includes/db.php';
$result = $db->query("SELECT * FROM colleges");
while ($row = $result->fetch_assoc()) {
    echo $row['code'] . " - " . $row['name'] . "<br>";
}