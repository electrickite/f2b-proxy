<?php
date_default_timezone_set('America/New_York');

$dsn = 'sqlite:/path/to/db.sqlite3';
$tokens = ['supersecret'];
$max_age = '1 day';
$command = '';
$to_file = 'banned.txt';
