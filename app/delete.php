<?php
include 'db.php';

$id = (int)$_GET['id'];
$conn->query("DELETE * FROM users WHERE id=$id");

header("Location: index.php");
exit;
?>
