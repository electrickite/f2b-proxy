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

$addr = !empty($_POST['addr']) && filter_var($_POST['addr'], FILTER_VALIDATE_IP) ? $_POST['addr'] : null;
$host = !empty($_POST['host']) ? substr(trim(filter_var($_POST['host'], FILTER_SANITIZE_STRING)), 0, 16) : null;
$token = !empty($_POST['token']) ? filter_var($_POST['token'], FILTER_SANITIZE_STRING) : null;

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
$version = filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : 6;

if (empty($_POST['action']) || $_POST['action'] != 'delete') {
    $stmt = $db->prepare("INSERT OR IGNORE INTO banned (addr, version, host) VALUES (?, ?, ?)");
    if ($stmt->execute([$packed_addr, $version, $host])) {
        updated($addr . ' added');
    } else {
        send_response(500, 'Internal server error');
    }
} else {
    $stmt = $db->prepare("DELETE FROM banned WHERE addr=? AND host=?");
    if ($stmt->execute([$packed_addr, $host])) {
        updated($addr . ' deleted');
    } else {
        send_response(500, 'Internal server error');
    }
}
