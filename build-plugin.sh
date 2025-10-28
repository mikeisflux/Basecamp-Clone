#!/bin/bash

# Basecamp WP Pro - WordPress Plugin Build Script
# Creates a production-ready zip file for WordPress plugin installation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Basecamp WP Pro - WordPress Plugin Builder ===${NC}\n"

# Configuration
PLUGIN_SLUG="basecamp-wp-pro"
VERSION="2.0.0"
BUILD_DIR="build"
DIST_DIR="dist"
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf ${BUILD_DIR}
rm -rf ${DIST_DIR}
mkdir -p ${PLUGIN_DIR}
mkdir -p ${DIST_DIR}

# Copy WordPress plugin files
echo -e "${YELLOW}Copying plugin files...${NC}"

# Main plugin file
cp basecamp-wp-pro.php ${PLUGIN_DIR}/

# Uninstall script
cp uninstall.php ${PLUGIN_DIR}/

# WordPress README
cp README.md ${PLUGIN_DIR}/readme.txt

# Core directories
cp -r includes/ ${PLUGIN_DIR}/
cp -r templates/ ${PLUGIN_DIR}/
cp -r assets/ ${PLUGIN_DIR}/
cp -r languages/ ${PLUGIN_DIR}/

# Documentation (user-facing)
cp FEATURES_COMPLETE.txt ${PLUGIN_DIR}/
cp LICENSE ${PLUGIN_DIR}/ 2>/dev/null || echo "GPL-2.0" > ${PLUGIN_DIR}/LICENSE

# Clean up any development files that might have been copied
echo -e "${YELLOW}Cleaning development files...${NC}"
find ${PLUGIN_DIR} -name ".DS_Store" -delete
find ${PLUGIN_DIR} -name "*.swp" -delete
find ${PLUGIN_DIR} -name "*.swo" -delete
find ${PLUGIN_DIR} -name ".gitkeep" -delete

# Create zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd ${BUILD_DIR}
zip -r "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" ${PLUGIN_SLUG}/ -q
cd ..

# Get file size
FILESIZE=$(du -h "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" | cut -f1)

# Success message
echo -e "\n${GREEN}✓ Build complete!${NC}"
echo -e "Plugin: ${PLUGIN_SLUG}"
echo -e "Version: ${VERSION}"
echo -e "Location: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"
echo -e "Size: ${FILESIZE}"
echo -e "\n${GREEN}Ready for WordPress installation!${NC}"
echo -e "\nInstallation instructions:"
echo -e "1. Go to WordPress Admin > Plugins > Add New > Upload Plugin"
echo -e "2. Choose file: ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip"
echo -e "3. Click 'Install Now'"
echo -e "4. Click 'Activate Plugin'"
echo -e "5. Access at: /basecamp/ (or your-site.com/basecamp/)"

# Optional WebSocket server notice
echo -e "\n${YELLOW}Note:${NC} WebSocket server is optional and separate."
echo -e "Plugin works perfectly with AJAX polling (default)."
echo -e "See websocket-server/README.md for WebSocket setup.\n"
