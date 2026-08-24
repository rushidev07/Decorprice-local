# DecorPrice — Local Setup Documentation

**Platform:** Magento 2.4.3 | **PHP:** 8.1 | **MySQL:** 8.x | **OS:** macOS (Homebrew)
**Completed:** 2026-08-06 | **Local URL:** http://decorprice.local:8080/

---

## Overview

| Item | Value |
|------|-------|
| Repo path | `/Users/11ahyconsulting/Documents/magento_root` |
| Local URL | `http://decorprice.local:8080/` |
| Admin URL | `http://decorprice.local:8080/admin_0dzrUX` |
| Apache port | 8080 |
| PHP-FPM port | 9000 (127.0.0.1) |
| DB host | 127.0.0.1 |
| DB name | `dev1` |
| DB user / pass | `dev1` / `dev1pass` |
| Magento mode | developer |

---

## Phase 1 — Stack Installation

### Step 1 — Install Local Stack via Homebrew

Installed Apache 2.4 (port 8080), PHP-FPM 8.1, and MySQL 8 using Homebrew.

```bash
brew install httpd php@8.1 mysql
brew services start php@8.1
brew services start mysql
sudo apachectl start
```

> **Note:** Apache master process runs as root. Use `sudo apachectl -k graceful` to reload
> config and `sudo apachectl -k stop` to fully stop. `brew services restart httpd` does NOT
> kill the root master process.

---

### Step 2 — Configure /etc/hosts

Added a local domain entry so the browser resolves `decorprice.local` to localhost.

**File:** `/etc/hosts`
```
127.0.0.1   decorprice.local
```

---

### Step 3 — Create Apache VirtualHost

Created the vhost at `/usr/local/etc/httpd/extra/httpd-vhosts.conf`.
DocumentRoot set to `pub/`, PHP-FPM proxied via FastCGI on port 9000.

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

Also required in `httpd.conf`:
```apache
Listen 8080
Include /usr/local/etc/httpd/extra/httpd-vhosts.conf
```

---

### Step 4 — Grant macOS Full Disk Access (TCC)

macOS TCC blocks daemon processes from accessing `~/Documents` without an explicit FDA grant.
Both Apache and PHP-FPM must be added individually.

**System Settings → Privacy & Security → Full Disk Access** — added:

| Binary | Path |
|--------|------|
| httpd | `/usr/local/Cellar/httpd/2.4.67/bin/httpd` |
| php-fpm | `/usr/local/Cellar/php@8.1/8.1.34/sbin/php-fpm` |

After granting, restart both daemons:
```bash
sudo apachectl -k graceful
brew services restart php@8.1
```

---

## Phase 2 — Database & Magento Configuration

### Step 5 — Create MySQL Database and Import Production Dump

Created the local database and user, then imported the production SQL dump.

```bash
mysql -u root -p -e "
  CREATE DATABASE dev1;
  CREATE USER 'dev1'@'127.0.0.1' IDENTIFIED BY 'dev1pass';
  GRANT ALL PRIVILEGES ON dev1.* TO 'dev1'@'127.0.0.1';
  FLUSH PRIVILEGES;
"
mysql -h 127.0.0.1 -u dev1 -pdev1pass dev1 < dev1.sql
```

---

### Step 6 — Override Base URLs in app/etc/env.php

The production database stores `https://www.decorprice.com/` as the base URL.
Overridden in `app/etc/env.php` without modifying the database:

```php
'system' => [
    'default' => [
        'web' => [
            'unsecure' => [
                'base_url' => 'http://decorprice.local:8080/',
            ],
            'secure' => [
                'base_url'                => 'http://decorprice.local:8080/',
                'use_in_frontend'         => '0',
                'use_in_adminhtml'        => '0',
                'enable_upgrade_insecure' => '0',
            ],
        ],
    ],
],
```

> The `enable_upgrade_insecure = 0` entry is critical — see Step 13 for context.

---

## Phase 3 — PHP 8.1 & MySQL 8 Compatibility Fixes

### Step 7 — Patch AbstractModel.php (PHP 8.1 Null Guard)

**File:** `vendor/magento/framework/Model/AbstractModel.php:189`

PHP 8.1 enforces strict null typing. The `method_exists()` call was receiving a null
value from an AttributeSet interceptor during DI compile, causing a fatal error.

**Fix:** Added a null guard before the `method_exists()` call.

---

### Step 8 — Apply PHP 8.1 Compatibility Patches to Vendor

Multiple vendor files passed null values to functions that reject null in PHP 8.1.

| File | Problem | Fix |
|------|---------|-----|
| `vendor/magento/framework/Translate.php:335` | `str_replace()` received null `$key`/`$value` | Cast: `(string)($x ?? '')` |
| `vendor/magento/framework/Escaper.php` | `preg_replace_callback()` received null `$string` | Cast: `$string = (string)($string ?? '')` |
| `vendor/magento/framework/View/Layout/Generator/Block.php:302` | PHP 8 named-param conflict in `call_user_func_array` | Wrapped args with `array_values()` |
| `vendor/magento/module-email/Model/Template/Filter.php:1161` | `sprintf()` received a `Phrase` object | Cast: `(string)` before `sprintf()` |

---

### Step 9 — Fix MySQL 8 Strict Mode

MySQL 8 enforces strict SQL mode by default which rejects invalid DATETIME writes.

**File:** `vendor/magento/framework/DB/Adapter/Pdo/Mysql.php:428`

Changed:
```php
'SQL_MODE' => '',
```
To:
```php
'SQL_MODE' => 'NO_ENGINE_SUBSTITUTION',
```

Also added to `app/etc/env.php` under the `db` connection `initStatements`:
```php
'initStatements' => 'SET NAMES utf8; SET sql_mode = \'NO_ENGINE_SUBSTITUTION\';'
```

---

### Step 10 — Fix Magetrend EOP Campaign Collection

**File:** `app/code/Magetrend/Eop/Model/ResourceModel/Campaign/Collection.php:82`

MySQL 8 error 1525: DATETIME columns cannot be compared to an empty string.

**Fix:** Removed `OR start_date = ''` and `OR end_date = ''` comparisons from the
collection WHERE clause.

---

### Step 11 — Run DI Compile

```bash
php bin/magento setup:di:compile
php bin/magento cache:flush
```

Compiled clean after all PHP 8.1 patches were in place. Generated interceptors for all
custom modules including `Ahy_SmartSearchLuma`.

---

### Step 12 — Fix bin/magento CLI (CompilerPreparation.php)

**File:** `setup/src/Magento/Setup/Console/CompilerPreparation.php:131`

`getFirstArgument()` returns null when no command is provided (e.g. `bin/magento --version`).
PHP 8.1 `preg_replace_callback()` rejects null — this caused the CLI itself to crash.

**Fix:**
```php
$cmdName = (string)($this->input->getFirstArgument() ?? '');
if (!$invalidate && $cmdName !== '') { ... }
```

---

### Step 13 — Disable upgrade-insecure-requests CSP (env.php)

The production database had `web/secure/enable_upgrade_insecure = 1`, which instructs
the browser to silently upgrade all HTTP sub-resource requests to HTTPS. On the local
HTTP-only setup this caused every CSS and JS file to be blocked by the browser — the
site loaded with broken styles and no JavaScript.

**Fix:** Already included in the `env.php` `system` overrides added in Step 6:
```php
'enable_upgrade_insecure' => '0',
```

---

### Step 14 — Run app:config:import

Required after `env.php` changes to force Magento to clear its internal config hash
check and re-read the overrides.

```bash
php bin/magento app:config:import
php bin/magento cache:flush
```

---

### Step 15 — Fix AbstractSimpleObjectBuilder.php (PCRE2 Regex)

**File:** `vendor/magento/framework/Api/AbstractSimpleObjectBuilder.php:71`

PHP 8.1 uses PCRE2 which rejects the `\I` escape sequence (unrecognised escape).
The regex `(\\Interceptor)?` produced `\I` at runtime, crashing the homepage — all
CMS block content was blank.

**Fix:** Escaped the backslash properly:
```php
// Before
'(\\Interceptor)?'

// After
'(\\\\Interceptor)?'
```

---

### Step 16 — Rename Match.php → MatchQuery.php (module-elasticsearch)

**File:** `vendor/magento/module-elasticsearch/SearchAdapter/Query/Builder/Match.php`

The file was named `Match.php` but contained `class MatchQuery`. The PSR-4 autoloader
expects the filename to match the class name — this mismatch crashed all category pages.

```bash
mv vendor/magento/module-elasticsearch/SearchAdapter/Query/Builder/Match.php \
   vendor/magento/module-elasticsearch/SearchAdapter/Query/Builder/MatchQuery.php
```

---

### Step 17 — Rename Match.php → MatchQuery.php (framework/Search)

**File:** `vendor/magento/framework/Search/Request/Query/Match.php`

Same PSR-4 class name mismatch as Step 16. This file's mismatch specifically crashed
the search results page.

```bash
mv vendor/magento/framework/Search/Request/Query/Match.php \
   vendor/magento/framework/Search/Request/Query/MatchQuery.php
```

---

## Phase 4 — Media, Modules & Theme

### Step 18 — Replace Broken pub/media Symlinks

All 17 entries in `pub/media/` were symlinks pointing to `/home2/media/` — the
production server path that does not exist locally. Each was removed and replaced
with a real local directory.

**Directories replaced:**

| Symlink (was → `/home2/media/…`) | Created locally |
|-----------------------------------|-----------------|
| catalog | `pub/media/catalog/product/cache/` |
| captcha | `pub/media/captcha/` |
| wysiwyg | `pub/media/wysiwyg/` |
| logo | `pub/media/logo/` |
| theme | `pub/media/theme/` |
| favicon | `pub/media/favicon/` |
| tmp | `pub/media/tmp/` |
| videos | `pub/media/videos/` |
| weltpixel | `pub/media/weltpixel/` |
| mageplaza | `pub/media/mageplaza/` |
| pdfs | `pub/media/pdfs/` |
| houzz | `pub/media/houzz/` |
| ga4Export | `pub/media/ga4Export/` |
| gtmExport | `pub/media/gtmExport/` |
| ced_fbnative | `pub/media/ced_fbnative/` |
| googlebase-exports | `pub/media/googlebase-exports/` |
| vendor | `pub/media/vendor/` |

> Product catalog images require an rsync from the production server to display locally.
> CMS/banner images use absolute URLs in the DB and load directly from `www.decorprice.com`.

---

### Step 19 — Enable FalcoSense Module (Ahy_SmartSearchLuma)

The module was already copied to `app/code/Ahy/SmartSearchLuma/`. The `di.xml` was
cleaned (removed Algolia/ThemeCustomizations references) and `config.xml` API URLs
were blanked for local use.

```bash
php bin/magento module:enable Ahy_SmartSearchLuma
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

Confirmed schema version `1.0.0` registered in `setup_module` table.

---

### Step 20 — Create Pearl Theme FalcoSense Layout Overrides

Phase 3 Pearl theme audit confirmed container names. Phase 4 layout overrides created at:

```
app/design/frontend/Pearl/weltpixel_ls/Ahy_SmartSearchLuma/layout/
  default.xml                  → injects FalcoSense search bar into header-wrapper
  ahy_smartsearch_active.xml   → removes native search/layered nav when frontend is enabled
```

> **Status:** `frontend_enabled = 0` — backend sync not yet configured (needs API credentials).

---

## Final State

| Check | Status |
|-------|--------|
| `http://decorprice.local:8080/` | ✅ HTTP 200 |
| `http://decorprice.local:8080/admin_0dzrUX` | ✅ HTTP 200 |
| All category pages | ✅ HTTP 200 |
| Search page | ✅ HTTP 200 |
| Cart / Login / Register / Contact | ✅ HTTP 200 |
| CSS / JS / Theme rendering | ✅ Correct |
| DI compile (`setup:di:compile`) | ✅ Clean |
| Product catalog images | ⚠️ Needs rsync from production server |
| FalcoSense frontend | ⏳ Pending API credentials |

---

## Next Steps

1. Rsync product catalog images from production server (optional for dev work):
   ```bash
   rsync -avz --progress user@server:/home2/media/catalog/product/ \
     pub/media/catalog/product/
   ```
2. Enter FalcoSense API credentials in **Admin → Stores → Configuration → Ahy → Smart Search**
3. Run full product sync:
   ```bash
   php bin/magento smartsearchluma:sync:full
   ```
4. Start the message queue consumer:
   ```bash
   php bin/magento queue:consumers:start smartSearchLumaFullSync
   ```
5. Enable the FalcoSense frontend: set `frontend_enabled = Yes` in admin config

---

## Log Locations

| Log | Path |
|-----|------|
| Apache error | `/usr/local/var/log/httpd/decorprice-error.log` |
| Apache access | `/usr/local/var/log/httpd/decorprice-access.log` |
| PHP-FPM | `/usr/local/var/log/php-fpm.log` |
| Magento system | `var/log/system.log` |
| Magento exceptions | `var/log/exception.log` |
