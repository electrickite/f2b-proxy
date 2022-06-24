<?php
include realpath(dirname(__FILE__) . '/../config.php');

$db = new PDO($dsn);

$version = !empty($_GET['ipv']) ? trim($_GET['ipv']) : null;

if ($version) {
    $query = "SELECT DISTINCT addr FROM banned WHERE version=? ORDER BY addr";
    $params = [$version];
} else {
    $query = "SELECT DISTINCT addr FROM banned ORDER BY addr";
    $params = [];
}

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit;
}

header('Content-Type: text/plain');

$has_ip = false;
foreach ($stmt as $row) {
    $has_ip = true;
    echo inet_ntop($row['addr']) . "\n";
}
if (!$has_ip) {
    echo "\n";
}
