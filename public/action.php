<?php
include realpath(dirname(__FILE__) . '/../config.php');

function send_response($code, $message) {
    http_response_code($code);
    header('Content-Type: text/plain');
    echo $message;
}

function updated($msg) {
    global $command;
    global $to_file;
    global $db;

    if (!empty($command)) {
        shell_exec($command);
    }

    if ($to_file) {
        try {
            $file = fopen($to_file, 'w');
            $stmt = $db->query("SELECT DISTINCT addr FROM banned ORDER BY addr");
            foreach ($stmt as $row) {
                fwrite($file, inet_ntop($row['addr']) . "\n");
            }
            fclose($file);
        } catch (Exception $e) {
            // Ignore exception
        }
    }

    send_response(200, $msg);
}

$db = new PDO($dsn);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$addr = empty($_POST['addr']) ? null : filter_var($_POST['addr'], FILTER_SANITIZE_STRING);
$host = empty($_POST['host']) ? null : filter_var($_POST['host'], FILTER_SANITIZE_STRING);
$token = empty($_POST['token']) ? null : filter_var($_POST['token'], FILTER_SANITIZE_STRING);

if (empty($addr) || empty($host)) {
    send_response(400, 'Bad request');
    exit;
}

if (!empty($tokens)
    && (empty($token) || !in_array($token, $tokens))
) {
    send_response(403, 'Forbidden');
    exit;
}

$packed_addr = inet_pton($addr);
if (empty($_POST['action']) || $_POST['action'] != 'delete') {
    $stmt = $db->prepare("INSERT INTO banned (addr, host) VALUES (?, ?)");
    if ($stmt->execute([$packed_addr, $host])) {
        updated($addr . ' added');
    } else {
        send_response(409, $addr . ' was not be added');
    }
} else {
    $stmt = $db->prepare("DELETE FROM banned WHERE addr=? AND host=?");
    if ($stmt->execute([$packed_addr, $host]) && $stmt->rowCount() > 0) {
        updated($addr . ' deleted');
    } else {
        send_response(400, $addr . ' was not deleted');
    }
}
