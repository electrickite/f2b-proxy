<?php
include dirname(__FILE__) . '/config.php';

$db = new PDO($dsn);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$db->query("DELETE FROM banned WHERE updated_at < datetime('now', '-{$max_age}')");
