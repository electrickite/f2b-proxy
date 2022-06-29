<?php
include dirname(__FILE__) . '/config.php';

$db = new PDO($dsn);

$db->exec("CREATE TABLE IF NOT EXISTS banned (
           addr BLOB NOT NULL,
           version INTEGER NOT NULL,
           host TEXT NOT NULL,
           updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (addr, host))");

$db->exec("CREATE INDEX IF NOT EXISTS
           updated_idx ON banned (updated_at)");
$db->exec("CREATE INDEX IF NOT EXISTS
           version_idx ON banned (version)");
