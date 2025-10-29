#!/bin/bash

echo "🚀 Complete Rebrand to ProjectFOB..."
echo "Removing ALL references to Basecamp, basecamp, warcampaign, BCWP, bcwp"

# Find all PHP, CSS, JS files (excluding .git and node_modules)
FILES=$(find . -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.txt" -o -name "*.md" \) \
    ! -path "./.git/*" \
    ! -path "./node_modules/*" \
    ! -path "./vendor/*" \
    ! -path "./dist/*")

echo "📝 Updating file contents..."

for file in $FILES; do
    # Skip if file doesn't exist or is the script itself
    [ ! -f "$file" ] && continue
    [[ "$file" == *"rebrand"* ]] && continue

    # PHP Class names: BCWP_ → PFOB_
    sed -i 's/class BCWP_/class PFOB_/g' "$file"
    sed -i 's/new BCWP_/new PFOB_/g' "$file"
    sed -i 's/BCWP_\([A-Za-z_]*\)::/PFOB_\1::/g' "$file"

    # Constants: BCWP_ → PFOB_
    sed -i 's/BCWP_VERSION/PFOB_VERSION/g' "$file"
    sed -i 's/BCWP_PLUGIN_DIR/PFOB_PLUGIN_DIR/g' "$file"
    sed -i 's/BCWP_PLUGIN_URL/PFOB_PLUGIN_URL/g' "$file"
    sed -i 's/BCWP_PLUGIN_BASENAME/PFOB_PLUGIN_BASENAME/g' "$file"
    sed -i 's/BCWP_R2_ENDPOINT/PFOB_R2_ENDPOINT/g' "$file"
    sed -i 's/BCWP_R2_BUCKET/PFOB_R2_BUCKET/g' "$file"

    # Functions: bcwp_ → pfob_
    sed -i 's/function bcwp_/function pfob_/g' "$file"
    sed -i 's/bcwp_\([a-z_]*\)(/pfob_\1(/g' "$file"

    # Variables: $bcwp_ → $pfob_
    sed -i 's/\$bcwp_/\$pfob_/g' "$file"

    # Database tables: bcwp_ → pfob_
    sed -i "s/\${prefix}bcwp_/\${prefix}pfob_/g" "$file"
    sed -i "s/{wpdb->prefix}bcwp_/{wpdb->prefix}pfob_/g" "$file"
    sed -i "s/'bcwp_/'pfob_/g" "$file"
    sed -i 's/"bcwp_/"pfob_/g' "$file"

    # CSS classes: bcwp- → pfob-
    sed -i 's/bcwp-/pfob-/g' "$file"

    # JavaScript: bcwp → pfob
    sed -i 's/bcwpAdmin/pfobAdmin/g' "$file"
    sed -i 's/bcwpData/pfobData/g' "$file"
    sed -i 's/\.bcwp/.pfob/g' "$file"

    # Text domain
    sed -i "s/'basecamp-wp-pro'/'projectfob'/g" "$file"
    sed -i 's/"basecamp-wp-pro"/"projectfob"/g' "$file"

    # Package names in comments
    sed -i 's/@package.*Basecamp_WP_Pro/@package    ProjectFOB/g' "$file"
    sed -i 's/@subpackage.*Basecamp_WP_Pro/@subpackage ProjectFOB/g' "$file"

    # User-facing text: "Basecamp" → "ProjectFOB"
    sed -i 's/Basecamp WP Pro/ProjectFOB/g' "$file"
    sed -i "s/__( 'Basecamp/__( 'ProjectFOB/g" "$file"
    sed -i 's/esc_html( "Basecamp/esc_html( "ProjectFOB/g' "$file"
    sed -i "s/get_option( 'bcwp_company_name', 'Basecamp' )/get_option( 'pfob_company_name', 'ProjectFOB' )/g" "$file"

    # Query vars
    sed -i "s/'bcwp_page'/'pfob_page'/g" "$file"
    sed -i "s/'bcwp_project'/'pfob_project'/g" "$file"
    sed -i "s/'bcwp_item'/'pfob_item'/g" "$file"
    sed -i 's/"bcwp_page"/"pfob_page"/g' "$file"
    sed -i 's/"bcwp_project"/"pfob_project"/g' "$file"
    sed -i 's/"bcwp_item"/"pfob_item"/g' "$file"

    # Global variables
    sed -i 's/global \$bcwp_page/global \$pfob_page/g' "$file"
    sed -i 's/global \$bcwp_project/global \$pfob_project/g' "$file"
    sed -i 's/global \$bcwp_item/global \$pfob_item/g' "$file"

    # Option names that start with bcwp_
    sed -i "s/get_option( 'bcwp_/get_option( 'pfob_/g" "$file"
    sed -i "s/update_option( 'bcwp_/update_option( 'pfob_/g" "$file"
    sed -i "s/add_option( 'bcwp_/add_option( 'pfob_/g" "$file"
    sed -i "s/delete_option( 'bcwp_/delete_option( 'pfob_/g" "$file"

    # Hook names
    sed -i "s/'bcwp_/'pfob_/g" "$file"
    sed -i 's/"bcwp_/"pfob_/g' "$file"

    # Admin menu slugs
    sed -i "s/'basecamp-wp-pro'/'projectfob'/g" "$file"

    # Nonces
    sed -i "s/wp_create_nonce( 'bcwp_/wp_create_nonce( 'pfob_/g" "$file"
    sed -i "s/check_ajax_referer( 'bcwp_/check_ajax_referer( 'pfob_/g" "$file"
done

echo "📁 Renaming class files..."

# Rename BCWP class files to PFOB
for file in $(find includes -name "class-bcwp-*.php" 2>/dev/null); do
    if [ -f "$file" ]; then
        newname=$(echo "$file" | sed 's/class-bcwp-/class-pfob-/')
        if [ "$file" != "$newname" ]; then
            mv "$file" "$newname"
            echo "  Renamed: $file -> $newname"
        fi
    fi
done

echo "📁 Renaming directories..."

# Rename any bcwp directories to pfob
if [ -d "assets/css" ]; then
    # CSS files
    for file in assets/css/bcwp-*.css 2>/dev/null; do
        if [ -f "$file" ]; then
            newname=$(echo "$file" | sed 's/bcwp-/pfob-/')
            mv "$file" "$newname" 2>/dev/null
            echo "  Renamed: $file -> $newname"
        fi
    done
fi

if [ -d "assets/js" ]; then
    # JS files
    for file in assets/js/bcwp-*.js 2>/dev/null; do
        if [ -f "$file" ]; then
            newname=$(echo "$file" | sed 's/bcwp-/pfob-/')
            mv "$file" "$newname" 2>/dev/null
            echo "  Renamed: $file -> $newname"
        fi
    done
fi

echo ""
echo "✅ Complete rebrand finished!"
echo ""
echo "Summary of changes:"
echo "  • BCWP_* classes → PFOB_* classes"
echo "  • bcwp_ functions → pfob_ functions"
echo "  • \$bcwp_ variables → \$pfob_ variables"
echo "  • bcwp- CSS classes → pfob- CSS classes"
echo "  • bcwp_* database tables → pfob_* database tables"
echo "  • bcwp_* options → pfob_* options"
echo "  • 'basecamp-wp-pro' text domain → 'projectfob'"
echo "  • All user-facing 'Basecamp' text → 'ProjectFOB'"
echo "  • Query vars updated (bcwp_page → pfob_page, etc.)"
echo "  • All file names updated"
echo ""
