# DecorPrice — Magento 2.4.3 Local Dev Setup

## Project overview

| Item | Value |
|------|-------|
| Platform | Magento 2.4.3 (PHP 8.1) |
| Repo path | `/Users/11ahyconsulting/Documents/magento_root` |
| Local URL | `http://decorprice.local:8080/` |
| Admin URL | `http://decorprice.local:8080/admin_0dzrUX` |
| Apache port | 8080 |
| PHP-FPM port | 9000 (127.0.0.1) |
| DB host | 127.0.0.1 |
| DB name | `dev1` |
| DB user/pass | `dev1` / `dev1pass` |
| Magento mode | developer |

---

## Local stack (all Homebrew)

| Service | Version | Managed by |
|---------|---------|------------|
| Apache (httpd) | 2.4.67 | `sudo apachectl` (root-owned master PID) |
| PHP-FPM | 8.1.34 | `brew services start php@8.1` |
| MySQL | 8.x | `brew services start mysql` |

**Important:** Apache master process is root-owned (started via `sudo apachectl start`). Use
`sudo apachectl -k graceful` to reload config, `sudo apachectl -k stop` to fully stop.
`brew services restart httpd` only manages the user-level plist and does NOT kill the root master.

---

## /etc/hosts entry required

```
127.0.0.1   decorprice.local
```

---

## Apache vhost config

File: `/usr/local/etc/httpd/extra/httpd-vhosts.conf`

```apache
<VirtualHost *:8080>
    ServerName decorprice.local
    DocumentRoot "/Users/11ahyconsulting/Documents/magento_root/pub"
    <Directory "/Users/11ahyconsulting/Documents/magento_root/pub">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
    ErrorLog "/usr/local/var/log/httpd/decorprice-error.log"
    CustomLog "/usr/local/var/log/httpd/decorprice-access.log" combined
</VirtualHost>
```

**Note:** httpd.conf main config also requires `Include /usr/local/etc/httpd/extra/httpd-vhosts.conf`
and port 8080 Listen directive.

---

## app/etc/env.php — base URL override (no DB changes needed)

The DB stores `https://www.decorprice.com/` as base_url. Override in `app/etc/env.php`:

```php
'system' => [
    'default' => [
        'web' => [
            'unsecure' => ['base_url' => 'http://decorprice.local:8080/'],
            'secure'   => ['base_url' => 'http://decorprice.local:8080/'],
        ]
    ]
],
```

This key is already present in `app/etc/env.php` as of 2026-08-03.

---

## macOS TCC (Full Disk Access) — REQUIRED for both daemons

Both Apache and PHP-FPM run as daemons. macOS TCC blocks daemon access to `~/Documents` without
explicit Full Disk Access grants.

Go to: **System Settings → Privacy & Security → Full Disk Access** → add:

| Binary | Actual path |
|--------|-------------|
| httpd | `/usr/local/Cellar/httpd/2.4.67/bin/httpd` |
| php-fpm | `/usr/local/Cellar/php@8.1/8.1.34/sbin/php-fpm` |

After granting FDA, restart both:
```bash
sudo apachectl -k graceful   # reloads config on running root master
brew services restart php@8.1
```

---

## DI compile / cache

```bash
cd /Users/11ahyconsulting/Documents/magento_root
php bin/magento setup:di:compile
php bin/magento cache:flush
```

DI compile runs clean as of 2026-08-03. The `AbstractModel.php:189` null guard patch is in place.

### Patch applied — AbstractModel.php

File: `vendor/magento/framework/Model/AbstractModel.php` line ~189

Null guard added for `method_exists()` call to prevent fatal on AttributeSet interceptor
during DI compile with PHP 8.1.

---

## DB stores / websites

| store_id | code | name |
|----------|------|------|
| 0 | admin | Admin |
| 1 | default | www.DecorPrice.com |
| 2 | lightingselectioncom_store | www.LightingSelection.com |

The DB has two storefronts. For local dev only `store_id=1` (DecorPrice) is relevant.

---

## Next steps after local setup is complete

1. Verify site loads at `http://decorprice.local:8080/` (HTTP 200)
2. Verify admin loads at `http://decorprice.local:8080/admin_0dzrUX`
3. Begin **FalcoSense integration** — see `FALCOSENSE_IMPLEMENTATION_PLAN.md`

---

## Troubleshooting quick reference

| Symptom | Cause | Fix |
|---------|-------|-----|
| 403 Forbidden | vhost path wrong OR TCC blocking httpd | Check DocumentRoot path; grant FDA to httpd |
| 404 (Magento) | base_url mismatch | env.php `system` override (see above) |
| `Operation not permitted` in error log | TCC blocking PHP-FPM | Grant FDA to php-fpm binary |
| `AH01630: client denied` | Apache `<Directory />` deny wins | Check DocumentRoot exists at the path; path typo |
| DI compile fatal `method_exists(null)` | PHP 8.1 strict null on AttributeSet | Null guard patch in AbstractModel.php:189 |
| `brew services restart httpd` has no effect | Root-owned Apache master ignores user launchd | Use `sudo apachectl -k graceful` instead |

---

## Log locations

| Log | Path |
|-----|------|
| Apache error | `/usr/local/var/log/httpd/decorprice-error.log` |
| Apache access | `/usr/local/var/log/httpd/decorprice-access.log` |
| PHP-FPM | `/usr/local/var/log/php-fpm.log` |
| Magento | `var/log/system.log`, `var/log/exception.log` |
