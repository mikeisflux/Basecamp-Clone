# ProjectFOB Theme + Plugin Installation Guide

**Complete installation guide for ProjectFOB SaaS Platform**

---

## 🎯 What You're Installing

1. **ProjectFOB Plugin** - Backend functionality (projects, tasks, subscriptions, etc.)
2. **ProjectFOB Theme** - Frontend design (homepage, styling, navigation)

**Both are required for a complete SaaS platform**

---

## 📦 Downloads

You'll need both files from the `dist/` folder:

1. `projectfob-2.0.0.zip` (177KB) - The plugin
2. `projectfob-theme.zip` (~20KB) - The theme

---

## 🚀 Step-by-Step Installation

### Step 1: Install the Plugin

1. Go to **WordPress Admin** > **Plugins** > **Add New**
2. Click **"Upload Plugin"**
3. Choose `projectfob-2.0.0.zip`
4. Click **"Install Now"**
5. Click **"Activate Plugin"**
6. Wait for page to load (plugin auto-flushes rewrite rules)

**✓ Plugin installed**

---

### Step 2: Install the Theme

1. Go to **WordPress Admin** > **Appearance** > **Themes**
2. Click **"Add New"** > **"Upload Theme"**
3. Choose `projectfob-theme.zip`
4. Click **"Install Now"**
5. Click **"Activate"**

**✓ Theme installed**

---

### Step 3: Flush Permalinks (CRITICAL!)

1. Go to **Settings** > **Permalinks**
2. Don't change anything - just click **"Save Changes"**
3. This flushes the rewrite rules for ProjectFOB routes

**✓ Permalinks flushed**

---

### Step 4: Configure ProjectFOB Plugin

1. Go to **WordPress Admin** > **ProjectFOB** > **Settings**
2. Configure **PayPal** credentials:
   - Client ID (from PayPal Developer Dashboard)
   - Client Secret
   - Sandbox Mode (on/off)
3. Configure **Cloudflare R2** credentials:
   - Access Key ID
   - Secret Access Key
   - Bucket: `projectfob`
4. Click **"Save Settings"**
5. Test connections using the buttons

**✓ Plugin configured**

---

### Step 5: Customize Theme (Optional)

1. Go to **Appearance** > **Customize**
2. Update:
   - **Site Identity** > Upload logo
   - **ProjectFOB Settings** > Colors
   - **ProjectFOB Settings** > Hero text
3. Click **"Publish"**

**✓ Theme customized**

---

### Step 6: Create Navigation Menu (Optional)

1. Go to **Appearance** > **Menus**
2. Create a new menu called "Primary Menu"
3. Add pages:
   - Home
   - Custom Link: "Pricing" → `/projectfob/pricing`
   - Blog
   - About/Contact (if you have these pages)
4. Assign to **"Primary Menu"** location
5. Click **"Save Menu"**

**✓ Menu created**

---

## ✅ Test Your Installation

Visit these URLs to verify everything works:

### 1. Homepage
**URL**: `https://your-site.com/`

**Should see**:
- Hero section with "Every great plan deploys from the FOB"
- Features grid
- "View Pricing" button
- Professional design

---

### 2. Pricing Page
**URL**: `https://your-site.com/projectfob/pricing`

**Should see**:
- 4 subscription plans (Starter, Professional, Business, Enterprise)
- Pricing details
- "Choose Plan" buttons

---

### 3. Dashboard (requires login)
**URL**: `https://your-site.com/projectfob/`

**Should see**:
- Login prompt if not logged in
- Dashboard with projects if logged in

---

## 🔍 Troubleshooting

### Problem: "Page Not Found" on /projectfob/ routes

**Solution**:
1. Go to **Settings** > **Permalinks**
2. Click **"Save Changes"** (even if you don't change anything)
3. Try the URL again

---

### Problem: Homepage shows "Plugin not activated" message

**Solution**:
1. Go to **Plugins** > **Installed Plugins**
2. Find "ProjectFOB" and click **"Activate"**
3. Refresh homepage

---

### Problem: Theme looks broken/unstyled

**Solution**:
1. Make sure both plugin AND theme are activated
2. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
3. Clear any caching plugins
4. Check browser console for JavaScript errors

---

### Problem: "Still shows duplicate menu in admin"

**Solution**:
1. Deactivate plugin completely
2. Delete plugin
3. Re-install fresh copy from `dist/projectfob-2.0.0.zip`
4. Activate again

---

## 📋 What You Get

### Homepage Features:
- ✅ Professional hero section with gradient background
- ✅ Features showcase with icons
- ✅ Call-to-action sections
- ✅ Blog posts integration
- ✅ Responsive mobile design

### Available Pages:
- ✅ `/` - Homepage (theme)
- ✅ `/projectfob/pricing` - Pricing plans (plugin)
- ✅ `/projectfob/signup` - Signup form (plugin)
- ✅ `/projectfob/` - Dashboard (plugin, requires login)
- ✅ All project management pages at `/projectfob/*`

### Admin Features:
- ✅ ProjectFOB menu in WordPress admin
- ✅ Dashboard with statistics
- ✅ Settings for PayPal & R2
- ✅ User management
- ✅ Billing & analytics

---

## 🎨 Customization

### Change Colors:
**Appearance** > **Customize** > **ProjectFOB Settings**

### Change Logo:
**Appearance** > **Customize** > **Site Identity** > **Upload Logo**

### Change Hero Text:
**Appearance** > **Customize** > **ProjectFOB Settings**

### Add Footer Widgets:
**Appearance** > **Widgets** > **Footer 1/2/3**

---

## 💡 Important Notes

1. **Theme + Plugin Work Together**
   - Theme provides the homepage and styling
   - Plugin provides the functionality
   - Both are required

2. **URLs**
   - Theme handles: `/` (homepage)
   - Plugin handles: `/projectfob/*` (all app pages)
   - Your WordPress pages/posts work normally

3. **Database Tables**
   - Plugin creates custom tables for projects, tasks, etc.
   - These are separate from WordPress posts/pages
   - Safe to use alongside existing WordPress content

4. **Subscription System**
   - Requires PayPal configuration
   - Requires Cloudflare R2 configuration
   - Configuration done in ProjectFOB > Settings

---

## 📞 Support

If you encounter issues:

1. **Check the diagnostic tool**: Upload `test-rewrite-rules.php` to your WordPress root and visit it
2. **Enable debug mode**: Add `define('WP_DEBUG', true);` to `wp-config.php`
3. **Check error logs**: `/wp-content/debug.log`

---

## ✨ You're Done!

Your ProjectFOB SaaS platform is now ready. Users can:

1. Visit your homepage
2. View pricing
3. Sign up for an account
4. Access the project management dashboard
5. Collaborate on projects with their team

**Welcome to ProjectFOB! 🎉**
