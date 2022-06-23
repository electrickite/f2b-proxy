<?php
include dirname(__FILE__) . '/config.php';
date_default_timezone_set('America/New_York');

$db = new PDO($dsn);

$db->exec("CREATE TABLE IF NOT EXISTS banned (
           addr BLOB NOT NULL,
           host TEXT NOT NULL,
           created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (addr, host))");

$db->exec("CREATE INDEX IF NOT EXISTS
           created_idx ON banned (created_at)");
