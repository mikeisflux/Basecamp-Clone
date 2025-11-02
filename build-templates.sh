#!/bin/bash

# ProjectFOB - Templates Build Script
# Creates a separate zip file for templates only

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== ProjectFOB - Templates Builder ===${NC}\n"

# Configuration
VERSION="2.6.8"
DIST_DIR="dist"

# Create dist directory if it doesn't exist
mkdir -p ${DIST_DIR}

# Create zip file from templates directory
echo -e "${YELLOW}Creating templates archive...${NC}"
cd templates
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

echo -e "\n${GREEN}✓ Ready for upload!${NC}\n"

echo -e "${YELLOW}Upload Instructions:${NC}"
echo -e "  1. Upload to your WordPress site"
echo -e "  2. Extract to wp-content/plugins/projectfob/templates/"
echo -e "  3. Replace existing templates directory\n"

echo -e "${GREEN}All done! 🚀${NC}\n"
