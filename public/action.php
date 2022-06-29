<?php
include realpath(dirname(__FILE__) . '/../config.php');

function send_response($code, $message) {
    http_response_code($code);
    header('Content-Type: text/plain');
    echo $message;
    exit;
}

function updated($msg) {
    global $command;
    global $to_file;
    global $db;

    if (!empty($command)) {
        shell_exec($command);
    }

    if ($to_file) {
        $file = @fopen($to_file, 'w');
        if ($file) {
            try {
                $stmt = $db->query("SELECT DISTINCT addr FROM banned ORDER BY addr");
                foreach ($stmt as $row) {
                    @fwrite($file, inet_ntop($row['addr']) . "\n");
                }
            } catch (Exception $e) {
                // Ignore exception
            }
        }
        @fclose($file);
    }

    send_response(200, $msg);
}

$db = new PDO($dsn);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

$addr = !empty($_POST['addr']) && filter_var($_POST['addr'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) ? $_POST['addr'] : null;
$host = !empty($_POST['host']) ? substr(trim(filter_var($_POST['host'], FILTER_SANITIZE_STRING)), 0, 16) : null;
$token = !empty($_POST['token']) ? filter_var($_POST['token'], FILTER_SANITIZE_STRING) : null;

if (!empty($tokens) && (empty($token) || !in_array($token, $tokens))) {
    send_response(403, 'Forbidden');
}

if (empty($addr) || empty($host)) {
    send_response(400, 'Bad request');
}

if (in_array($addr, $ignore)) {
    send_response(200, "{$addr} ignored");
}

$packed_addr = inet_pton($addr);
$version = filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : 6;

if (empty($_POST['action']) || $_POST['action'] != 'delete') {
    $query = "INSERT INTO banned (addr, version, host) VALUES (?, ?, ?)
                ON CONFLICT
                DO UPDATE SET updated_at=CURRENT_TIMESTAMP";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$packed_addr, $version, $host])) {
        updated("{$addr} added");
    } else {
        send_response(500, "Error: {$addr} was not added");
    }
} else {
    $query = "DELETE FROM banned WHERE addr=? AND host=?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$packed_addr, $host])) {
        updated("{$addr} deleted");
    } else {
        send_response(500, "Error: {$addr} was not deleted");
    }
}
