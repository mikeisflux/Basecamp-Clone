#!/bin/bash

# ProjectFOB - WordPress Plugin Build Script
# Creates a production-ready zip file for WordPress plugin installation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== ProjectFOB - WordPress Plugin Builder ===${NC}\n"

# Configuration
PLUGIN_SLUG="projectfob"
VERSION="2.8.0"
BUILD_DIR="build"
DIST_DIR="dist"
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf ${BUILD_DIR}
mkdir -p ${PLUGIN_DIR}
mkdir -p ${DIST_DIR}

# Copy WordPress plugin files
echo -e "${YELLOW}Copying plugin files...${NC}"

# Main plugin file
cp projectfob.php ${PLUGIN_DIR}/

# Uninstall script (if exists)
if [ -f "uninstall.php" ]; then
    cp uninstall.php ${PLUGIN_DIR}/
fi

# WordPress README (if exists)
if [ -f "README.md" ]; then
    cp README.md ${PLUGIN_DIR}/readme.txt
fi

# Core directories
echo -e "${YELLOW}Copying core directories...${NC}"
cp -r includes/ ${PLUGIN_DIR}/
cp -r templates/ ${PLUGIN_DIR}/

# Assets directory (if exists)
if [ -d "assets" ]; then
    cp -r assets/ ${PLUGIN_DIR}/
else
    mkdir -p ${PLUGIN_DIR}/assets
fi

# Languages directory (if exists)
if [ -d "languages" ]; then
    cp -r languages/ ${PLUGIN_DIR}/
else
    mkdir -p ${PLUGIN_DIR}/languages
fi

# Documentation files
if [ -f "FEATURES_COMPLETE.txt" ]; then
    cp FEATURES_COMPLETE.txt ${PLUGIN_DIR}/
fi

if [ -f "LICENSE" ]; then
    cp LICENSE ${PLUGIN_DIR}/
else
    echo "GPL-2.0" > ${PLUGIN_DIR}/LICENSE
fi

if [ -f "BUG_FIXES.md" ]; then
    cp BUG_FIXES.md ${PLUGIN_DIR}/
fi

# Clean up any development files that might have been copied
echo -e "${YELLOW}Cleaning development files...${NC}"
find ${PLUGIN_DIR} -name ".DS_Store" -delete 2>/dev/null || true
find ${PLUGIN_DIR} -name "*.swp" -delete 2>/dev/null || true
find ${PLUGIN_DIR} -name "*.swo" -delete 2>/dev/null || true
find ${PLUGIN_DIR} -name ".gitkeep" -delete 2>/dev/null || true
find ${PLUGIN_DIR} -name ".git" -type d -exec rm -rf {} + 2>/dev/null || true
find ${PLUGIN_DIR} -name "node_modules" -type d -exec rm -rf {} + 2>/dev/null || true

# Remove WebSocket server (separate installation)
if [ -d "${PLUGIN_DIR}/websocket-server" ]; then
    rm -rf ${PLUGIN_DIR}/websocket-server
fi

# Create installation guide
echo -e "${YELLOW}Creating installation guide...${NC}"
cat > ${PLUGIN_DIR}/INSTALLATION.txt << 'EOF'
=== ProjectFOB Installation Guide ===

Thank you for choosing ProjectFOB - the complete SaaS project management platform!

== Quick Start ==

1. Upload the plugin:
   - Go to WordPress Admin > Plugins > Add New > Upload Plugin
   - Choose the projectfob-2.1.0.zip file
   - Click "Install Now"
   - Click "Activate Plugin"

2. Configure PayPal (Required for subscriptions):
   - Go to WordPress Admin > ProjectFOB > Settings
   - Enter your PayPal Client ID and Client Secret
   - Get credentials from: https://developer.paypal.com/dashboard/applications
   - Your PayPal account: divinitycomicsinc@gmail.com

3. Configure Cloudflare R2 (Required for file storage):
   - Go to WordPress Admin > ProjectFOB > Settings
   - Enter your R2 Access Key ID and Secret Access Key
   - Bucket: projectfob
   - Endpoint: https://e17dbcdbd648aab85b2e0e8391896b12.r2.cloudflarestorage.com

4. Set up PayPal Webhook:
   - In PayPal Developer Dashboard, add webhook URL:
     https://your-site.com/wp-json/projectfob/v1/webhooks/paypal
   - Subscribe to all "BILLING.SUBSCRIPTION.*" events
   - Copy Webhook ID to ProjectFOB settings

5. Access your site:
   - Public pricing page: https://your-site.com/projectfob/pricing
   - User dashboard: https://your-site.com/projectfob/
   - Admin dashboard: WordPress Admin > ProjectFOB

== Subscription Plans ==

Your platform offers 4 subscription tiers:
- Starter: $9/month (5 projects, 10 users, 10GB storage)
- Professional: $29/month (25 projects, 50 users, 100GB storage)
- Business: $79/month (unlimited projects, 250 users, 500GB storage)
- Enterprise: $289/month (unlimited everything)

== Features Included ==

✓ Complete project management suite
✓ Real-time collaboration (WebSocket + AJAX fallback)
✓ PayPal subscription billing
✓ Cloudflare R2 cloud storage
✓ Usage tracking and quota enforcement
✓ Analytics dashboard
✓ Google Calendar integration
✓ Email digests
✓ Import/Export functionality
✓ Advanced search
✓ Team collaboration tools

== Support ==

For support and documentation:
- Email: support@projectfob.com
- Documentation: https://docs.projectfob.com

== Technical Requirements ==

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- SSL certificate (required for PayPal)
- PayPal Business Account
- Cloudflare R2 Account

Enjoy ProjectFOB!
EOF

# Create zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd ${BUILD_DIR}
zip -r "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" ${PLUGIN_SLUG}/ -q
cd ..

# Get file size
FILESIZE=$(du -h "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" | cut -f1)

# Success message
echo -e "\n${GREEN}✓ Build complete!${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "Plugin:   ${GREEN}ProjectFOB${NC}"
echo -e "Version:  ${GREEN}${VERSION}${NC}"
echo -e "Location: ${GREEN}${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip${NC}"
echo -e "Size:     ${GREEN}${FILESIZE}${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -e "\n${GREEN}✓ Ready for WordPress installation!${NC}\n"

echo -e "${YELLOW}Installation Steps:${NC}"
echo -e "  1. Go to WordPress Admin > Plugins > Add New"
echo -e "  2. Click 'Upload Plugin'"
echo -e "  3. Choose: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"
echo -e "  4. Click 'Install Now' then 'Activate'"
echo -e "  5. Configure PayPal & R2 in settings"
echo -e "  6. Access at: /projectfob/pricing\n"

echo -e "${YELLOW}Important:${NC}"
echo -e "  • PayPal account: divinitycomicsinc@gmail.com"
echo -e "  • R2 Bucket: projectfob"
echo -e "  • SSL certificate required"
echo -e "  • Set up PayPal webhook for subscriptions\n"

echo -e "${GREEN}All done! 🚀${NC}\n"
