Fail2Ban Proxy Helper
=====================
A small utility to assist with brute force mitigation for servers behind a
reverse proxy or load balancer.

Fail2Ban is a great tool for mitigating brute force and other attacks. In a
standard configuration, it will analyze log files for suspicious activity and
use the host firewall to block the offending IP address for a period of time.
This works reasonably well when clients connect directly to the server.
However, when the server sits behind a proxy or load balancer, the host
firewall only sees connections coming from the proxy's IP address.

One solution is to perform the actual blocking of clients at the edge firewall.
To accomplish this, fail2ban needs to send the firewall banned and unbanned IPs.
This tool serves as a part of this process:

  1. Fail2Ban running on a server detects an attack.
  2. The Fail2Ban banaction sends a POST request (via cURL or similar) to this
     helper script.
  3. The IP address in the POST request is recorded in an SQLite database.
  4. The firewall checks the list of banned IPs provided by the helper and
     blocks them

Dependencies
------------
  * PHP 7.0+
  * PHP PDO SQLite extension
  * SQLite 3.24+
  * PHP-capable web server

Installation
------------
  1. Copy `config.example.php` to `config.php` and edit as needed.
  2. Run `php init.php` to create the SQLite database.
  3. Expose the `public` directory using a PHP-capable web server.

### Configuration
  * `$dsn` - The PHP PDO DSN for the SQLite database
  * `$tokens` - An array of authentication tokens used for adding and deleting
    IPs. Leaving the array empty will allow adds and deletes without tokens
  * `max_age` - Maximum age of an IP entry. Used by `prune.php`
  * `command` - Shell command to run after an IP is added or removed
  * `ignore` - Array of IP addresses to ignore
  * `to_file` - Optional path of file to write banned IP list to after each
    update

Use
---
Banned IPs can be listed, added, and deleted by making GET and POST requests
to `banned.php` and `action.php`. In addition, if `$to_file` is set in
`config.php`, the list of banned IPs will be written to that path.

### List Banned IPs

    $ curl http://localhost:8080/banned.php

### List Banned IPs by Protocol Version

    $ curl "http://localhost:8080/banned.php?ipv=4"
    $ curl "http://localhost:8080/banned.php?ipv=6"

### Add IP

    $ curl -X POST -F addr=10.0.0.100 -F host=myserver -F action=add -F token=mysecret http://localhost:8080/action.php

### Delete IP

    $ curl -X POST -F addr=10.0.0.100 -F host=myserver -F action=delete -F token=mysecret http://localhost:8080/action.phpa

### Pruning old entries
While Fail2Ban can be configured to both add and remove banned IPs, old
records may also be removed periodically using the `prune.php` script. Simply
run via cron or similar and it will remove entries from the database older
than the `max_age` config value.
