# Cache Exclusion Instructions for ProjectFOB

## Problem
HostGator Managed WordPress uses aggressive caching that can break ProjectFOB's dynamic features. You need to exclude ProjectFOB URLs from caching.

---

## Method 1: HostGator Caching Plugin (EASIEST)

If you see a "Caching" menu item in WordPress Admin:

1. **Go to WordPress Admin → Caching**
2. **Look for "Cache Exclusions" or "Exclude URLs"**
3. **Add these exclusions:**
   ```
   /projectfob/*
   /wp-json/projectfob/*
   ```
4. **Save and clear cache**

---

## Method 2: Upload .htaccess File (IF YOU HAVE ACCESS)

**IMPORTANT:** HostGator Managed WordPress uses **Nginx**, not Apache, so `.htaccess` might NOT work. But if you have Apache, here's what to do:

### Step 1: Download Current .htaccess
1. Use FTP or File Manager to download your current `.htaccess` file
2. Save a backup copy

### Step 2: Add ProjectFOB Rules
Add this code at the **BOTTOM** of your `.htaccess` file:

```apache
# BEGIN ProjectFOB - Disable Caching for Dynamic Pages
<IfModule mod_headers.c>
    # Disable caching for ProjectFOB pages
    <FilesMatch "\.php$">
        <If "%{REQUEST_URI} =~ m#/projectfob/#">
            Header set Cache-Control "no-cache, no-store, must-revalidate"
            Header set Pragma "no-cache"
            Header set Expires "0"
        </If>
    </FilesMatch>
</IfModule>

# Disable caching for REST API endpoints
<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_URI} ^/wp-json/projectfob/(.*)$
    RewriteRule .* - [E=no-cache:1]
</IfModule>

<IfModule mod_headers.c>
    Header always set Cache-Control "no-cache, no-store, must-revalidate" env=no-cache
    Header always set Pragma "no-cache" env=no-cache
    Header always set Expires "0" env=no-cache
</IfModule>
# END ProjectFOB
```

### Step 3: Upload
1. Upload the modified `.htaccess` file
2. Clear all caches
3. Test ProjectFOB

**Complete .htaccess file available:** See `.htaccess-projectfob` file in dist folder

---

## Method 3: W3 Total Cache Plugin (If Installed)

1. **Go to:** Performance → Page Cache → Advanced
2. **Find:** "Never cache the following pages"
3. **Add:**
   ```
   /projectfob/*
   wp-json/projectfob/*
   ```
4. **Save Settings & Clear Cache**

---

## Method 4: WP Super Cache Plugin (If Installed)

1. **Go to:** Settings → WP Super Cache → Advanced
2. **Find:** "Rejected URIs" or "Don't cache the following pages"
3. **Add:**
   ```
   /projectfob/
   /wp-json/projectfob/
   ```
4. **Save Settings & Clear Cache**

---

## Method 5: Contact HostGator Support (RECOMMENDED)

Since you're on **Managed WordPress**, HostGator controls the caching:

**Tell Support:**
> "I need to exclude these URLs from server-level caching:
> - /projectfob/*
> - /wp-json/projectfob/*
>
> These are dynamic application pages that cannot be cached."

They can add exclusions to their Nginx configuration.

---

## Method 6: Add wp-config.php Constant

Add this to your `wp-config.php` file (above the "That's all" line):

```php
// Disable caching for ProjectFOB
define('DONOTCACHEPAGE', true);
```

**Note:** This disables caching for ALL pages, not just ProjectFOB.

---

## How to Test if Caching is Excluded

1. **Clear all caches**
2. **Visit a ProjectFOB page:** https://yourdomain.com/projectfob/pricing
3. **Check response headers:**
   - Open browser DevTools (F12)
   - Go to Network tab
   - Refresh the page
   - Click on the page request
   - Look for these headers:
     ```
     Cache-Control: no-cache, no-store, must-revalidate
     Pragma: no-cache
     Expires: 0
     ```

If you see these headers, caching is properly disabled.

---

## Complete .htaccess File

The file `.htaccess-projectfob` contains a complete, ready-to-use .htaccess configuration with:
- Standard WordPress rewrite rules
- ProjectFOB cache exclusions
- REST API cache exclusions

**To use:**
1. Download `.htaccess-projectfob` from the dist folder
2. Rename it to `.htaccess`
3. Upload to your WordPress root directory
4. **BACKUP your current .htaccess first!**

---

## Important Notes for HostGator Managed WordPress

- **Nginx vs Apache:** Managed WordPress uses Nginx, so `.htaccess` rules may not apply
- **Server-level caching:** HostGator has caching at the server level that you can't control via .htaccess
- **Best solution:** Contact HostGator support to exclude ProjectFOB URLs from server caching
- **Plugin caching:** Even if you fix .htaccess, plugin caching can still interfere

---

## Still Having Issues?

If none of these work:
1. Run the diagnostic tool: `test-hosting-compatibility.php`
2. Install the debug logger plugin: `projectfob-debug-logger.php`
3. Click on ProjectFOB links and check the debug log
4. Send the log for analysis
