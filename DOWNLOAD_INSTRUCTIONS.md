# How to Download ProjectFOB Files from GitHub

## ⚠️ IMPORTANT: You're on the wrong branch!

The files you're looking for are **NOT** on the main branch. They're on a feature branch.

---

## 📍 Where Your Files Are Located

**Branch Name**: `claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU`

**Repository**: `mikeisflux/Basecamp-Clone`

---

## 🎯 Step-by-Step Instructions

### Step 1: Go to Your Repository

Visit: `https://github.com/mikeisflux/Basecamp-Clone`

---

### Step 2: Switch to the Correct Branch

1. Look at the top left of the page
2. You'll see a dropdown that says **"main"** or **"master"**
3. **Click on that dropdown**
4. A search box will appear
5. Type: `claude/basecamp`
6. You should see: `claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU`
7. **Click on it** to switch to that branch

---

### Step 3: Navigate to the dist Folder

1. You should now see the ProjectFOB files in the file list
2. Click on the **"dist"** folder
3. You should see:
   - `projectfob-2.0.0.zip` (177 KB) - **The Plugin**
   - `projectfob-theme.zip` (15 KB) - **The Theme**
   - `test-rewrite-rules.php` - **Diagnostic Tool**

---

### Step 4: Download Files

#### Option A: Download Individual Files

1. Click on `projectfob-2.0.0.zip`
2. Click the **"Download"** button (or right-click "Download raw file")
3. Go back and repeat for `projectfob-theme.zip`

#### Option B: Download Entire Branch

1. While on the `claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU` branch
2. Click the green **"Code"** button (top right)
3. Select **"Download ZIP"**
4. Extract the ZIP file
5. Navigate to the `dist/` folder inside
6. Your files are there!

---

## 📦 What You're Downloading

### 1. projectfob-2.0.0.zip (177 KB)
- **The Plugin** - Backend functionality
- Install via WordPress Admin > Plugins > Add New > Upload
- Provides all project management features
- Subscription billing
- Cloud storage

### 2. projectfob-theme.zip (15 KB)
- **The Theme** - Frontend design
- Install via WordPress Admin > Appearance > Themes > Add New > Upload
- Provides homepage, navigation, styling
- Beautiful hero section
- Responsive design

### 3. test-rewrite-rules.php (5 KB)
- **Diagnostic Tool** - For troubleshooting
- Upload to WordPress root if routes don't work
- Helps debug rewrite rule issues

---

## 🚨 Why You Can't See the Files

You were probably looking at the **main** or **master** branch, which contains a different project (`verify-my-collectible`).

Our ProjectFOB work is on a **feature branch** called `claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU`.

Think of branches like different versions of your code existing at the same time:
- **main branch** = Old project (verify-my-collectible)
- **feature branch** = New project (ProjectFOB) ✅ You want this one!

---

## ✅ Verification

Once you switch to the correct branch, you should see these files in the root directory:
- `projectfob.php` (main plugin file)
- `projectfob-theme/` (theme directory)
- `dist/` (distribution files)
- `THEME_INSTALLATION.md` (installation guide)

If you see these files, you're on the right branch! ✅

If you see `verify-my-collectible` files, you're on the wrong branch ❌

---

## 🔗 Direct Link (if GitHub allows)

Try this direct link to the dist folder on the correct branch:

`https://github.com/mikeisflux/Basecamp-Clone/tree/claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU/dist`

(Copy and paste this URL into your browser)

---

## 💡 Alternative: Use Git Command Line

If you have Git installed, you can download the files directly:

```bash
# Clone the repository
git clone https://github.com/mikeisflux/Basecamp-Clone.git

# Navigate into the directory
cd Basecamp-Clone

# Switch to the correct branch
git checkout claude/basecamp-wordpress-clone-011CUXXousoHfPTssGNVteYU

# The files are now in the dist/ folder
ls dist/
```

---

## 📞 Still Can't Find Them?

If you still can't find the files:

1. **Screenshot what you see** on GitHub
2. **Verify the branch name** shown in the dropdown
3. **Check the URL** - it should contain the branch name
4. **Look at the file list** - do you see `projectfob.php` or `verify-my-collectible`?

The files are definitely there - they're just on a different branch than you're currently viewing! 🎯
