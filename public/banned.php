<?php
include realpath(dirname(__FILE__) . '/../config.php');

$db = new PDO($dsn);

try {
    $stmt = $db->query("SELECT DISTINCT addr FROM banned ORDER BY addr");
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit;
}

header('Content-Type: text/plain');
foreach ($stmt as $row) {
    echo inet_ntop($row['addr']) . "\n";
}
