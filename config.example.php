<?php
// TImezone
date_default_timezone_set('America/New_York');

// SQLite database PDO DSN
$dsn = 'sqlite:/path/to/db.sqlite3';

// Authentication tokens (empty array will disable)
$tokens = ['supersecret'];

// Maximum age of banned IP entries
$max_age = '1 day';

// (optional) Shell command to run when IP is added or deleted
$command = '';

// IP addresses to ignore
// (Reserved addresses are filtered regardless of this setting)
$ignore = ['10.0.0.1'];

// (optional) Write banned IP list to file path
$to_file = 'banned.txt';
