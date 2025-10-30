# ProjectFOB Debug Instructions

## Fatal Error During Activation? Here's How to Debug It

The plugin now includes comprehensive debugging tools to help identify activation errors.

---

## Method 1: Standalone Debug Script (Recommended)

This script runs outside of WordPress's plugin activation process and shows you exactly what's happening.

### Steps:

1. **Download `debug-activation.php`** from the plugin package
2. **Upload it to your WordPress root directory** (same folder as `wp-config.php`)
3. **Access it in your browser:**
   ```
   https://yourdomain.com/debug-activation.php
   ```
4. **Review the detailed output** showing:
   - All files being checked
   - Each class being loaded
   - Exact error message and line number if something fails
5. **Download the log file** `projectfob-activation-debug.log` for review

---

## Method 2: In-Plugin Debug Mode

This enables logging during the actual WordPress plugin activation.

### Steps:

1. **Download `enable-debug-mode.php`** from the plugin package
2. **Upload it to your WordPress root directory**
3. **Access it in your browser:**
   ```
   https://yourdomain.com/enable-debug-mode.php
   ```
4. **You'll see:** "✓ Debug mode enabled for ProjectFOB"
5. **Now try to activate the plugin** through WordPress Admin → Plugins
6. **Download the debug log:**
   ```
   wp-content/plugins/projectfob/debug.log
   ```
7. **Review the log** to see exactly where it failed

### What Gets Logged:

- PHP version and WordPress version
- Each file as it loads
- Each class as it instantiates
- Full error messages with file paths and line numbers
- Complete stack traces for any errors

---

## Method 3: WordPress Debug Mode

Enable WordPress's built-in debugging:

### Steps:

1. **Edit `wp-config.php`** in your WordPress root
2. **Add or update these lines** (before "That's all, stop editing!"):
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```
3. **Try to activate the plugin**
4. **Check the debug log:**
   ```
   wp-content/debug.log
   ```

---

## What to Look For in Logs

### Common Issues:

1. **"Class not found"** → Missing file or incorrect class name
2. **"Call to undefined function"** → Missing WordPress function or dependency
3. **"Cannot redeclare"** → Class loaded twice (conflict)
4. **"Parse error"** → PHP syntax error in a file
5. **"Declaration must be compatible"** → Method signature mismatch

### Log Format:

```
[2025-10-30 12:34:56] [INFO] Loading database classes...
[2025-10-30 12:34:56] [INFO] ✓ Database class loaded
[2025-10-30 12:34:56] [ERROR] Fatal error: Class 'PFOB_Something' not found
File: /path/to/file.php:123
Trace:
#0 /path/to/file.php(123): method_name()
#1 {main}
```

---

## Sending Debug Info to Support

When reporting the error, please include:

1. **PHP Version** (from log or phpinfo())
2. **WordPress Version** (from log or WP admin)
3. **The complete error message** from the log
4. **File path and line number** where it failed
5. **Last successful step** before the error

---

## Quick Troubleshooting

### If you see "requires PHP 8.3 or higher":
- Your server is running PHP < 8.3
- Ask your host to upgrade PHP to 8.3+

### If you see "Class not found" errors:
- Files may not have uploaded completely
- Re-upload the plugin via zip file

### If you see "Cannot write to log file":
- Fix permissions: `chmod 755 wp-content/plugins/projectfob`

---

## Disabling Debug Mode

Once debugging is complete:

1. Delete the `ENABLE_DEBUG` file from `wp-content/plugins/projectfob/`
2. Or re-upload the plugin (will overwrite debug flags)

---

## Need More Help?

Include these files when reporting issues:
- `projectfob-activation-debug.log`
- `wp-content/plugins/projectfob/debug.log`
- `wp-content/debug.log`

The more log information you provide, the faster we can identify and fix the issue!
