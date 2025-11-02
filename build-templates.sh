#!/bin/bash

# ProjectFOB - Templates Build Script
# Creates an uploadable "theme" package that auto-installs templates

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== ProjectFOB - Templates Builder ===${NC}\n"

# Configuration
VERSION="2.6.9"
DIST_DIR="dist"
WRAPPER_DIR="template-wrapper"

# Create dist directory if it doesn't exist
mkdir -p ${DIST_DIR}

# Sync latest templates to wrapper
echo -e "${YELLOW}Syncing latest templates...${NC}"
rm -rf ${WRAPPER_DIR}/templates
cp -r templates ${WRAPPER_DIR}/templates

# Create zip file from template-wrapper directory
echo -e "${YELLOW}Creating uploadable templates package...${NC}"
cd ${WRAPPER_DIR}
zip -r "../${DIST_DIR}/projectfob-templates-${VERSION}.zip" . -q
cd ..

# Get file size
FILESIZE=$(du -h "${DIST_DIR}/projectfob-templates-${VERSION}.zip" | cut -f1)

# Success message
echo -e "\n${GREEN}✓ Templates build complete!${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "Templates: ${GREEN}ProjectFOB Templates${NC}"
echo -e "Version:   ${GREEN}${VERSION}${NC}"
echo -e "Location:  ${GREEN}${DIST_DIR}/projectfob-templates-${VERSION}.zip${NC}"
echo -e "Size:      ${GREEN}${FILESIZE}${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -e "\n${GREEN}✓ Ready for WordPress upload!${NC}\n"

echo -e "${YELLOW}Installation Instructions:${NC}"
echo -e "  1. Go to WordPress Admin > Appearance > Themes"
echo -e "  2. Click 'Add New' then 'Upload Theme'"
echo -e "  3. Choose: ${DIST_DIR}/projectfob-templates-${VERSION}.zip"
echo -e "  4. Click 'Install Now'"
echo -e "  5. Click 'Activate'"
echo -e "  6. Templates will auto-install and theme will switch back"
echo -e "  7. Clear all caches (browser + WordPress)\n"

echo -e "${YELLOW}What's Fixed in v2.6.8:${NC}"
echo -e "  ✅ Removed ALL column layouts from adminland"
echo -e "  ✅ Fixed subscription details vertical stacking"
echo -e "  ✅ Fixed capabilities list layout"
echo -e "  ✅ Everything displays as standard web page\n"

echo -e "${GREEN}All done! 🚀${NC}\n"
