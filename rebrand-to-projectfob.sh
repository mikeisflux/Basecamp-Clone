#!/bin/bash

# ProjectFOB Rebrand Script
# Converts all Warcampaign references to ProjectFOB

set -e

echo "🚀 Rebranding to ProjectFOB..."

# Find all PHP, JS, and template files
FILES=$(find . -type f \( -name "*.php" -o -name "*.js" -o -name "*.css" -o -name "*.txt" -o -name "*.md" -o -name "*.sh" \) \
    ! -path "./node_modules/*" \
    ! -path "./.git/*" \
    ! -path "./build/*" \
    ! -path "./dist/*" \
    ! -name "rebrand-to-projectfob.sh")

echo "📝 Updating file contents..."

for file in $FILES; do
    # Skip binary files
    if file "$file" | grep -q "text"; then
        # Replace class names and constants
        sed -i 's/class WC_/class PFOB_/g' "$file"
        sed -i 's/WC_VERSION/PFOB_VERSION/g' "$file"
        sed -i 's/WC_PLUGIN_DIR/PFOB_PLUGIN_DIR/g' "$file"
        sed -i 's/WC_PLUGIN_URL/PFOB_PLUGIN_URL/g' "$file"
        sed -i 's/WC_PLUGIN_BASENAME/PFOB_PLUGIN_BASENAME/g' "$file"
        sed -i 's/WC_R2_ENDPOINT/PFOB_R2_ENDPOINT/g' "$file"
        sed -i 's/WC_R2_BUCKET/PFOB_R2_BUCKET/g' "$file"
        sed -i 's/WC_PLAN_/PFOB_PLAN_/g' "$file"

        # Replace function prefixes and options
        sed -i 's/wc_paypal_/pfob_paypal_/g' "$file"
        sed -i 's/wc_r2_/pfob_r2_/g' "$file"
        sed -i 's/wc_subscriptions/pfob_subscriptions/g' "$file"
        sed -i 's/wc_billing_history/pfob_billing_history/g' "$file"
        sed -i 's/wc_usage_tracking/pfob_usage_tracking/g' "$file"
        sed -i "s/'wc_/'pfob_/g" "$file"
        sed -i 's/"wc_/"pfob_/g' "$file"

        # Replace REST API namespace
        sed -i 's/warcampaign\/v1/projectfob\/v1/g' "$file"
        sed -i "s/'warcampaign\/v1'/'projectfob\/v1'/g" "$file"

        # Replace URL routes
        sed -i 's/\/warcampaign\//\/projectfob\//g' "$file"
        sed -i 's/warcampaign\//projectfob\//g' "$file"

        # Replace text and branding
        sed -i 's/Warcampaign/ProjectFOB/g' "$file"
        sed -i 's/warcampaign/projectfob/g' "$file"

        # Replace query vars and page slugs
        sed -i 's/wc_public_page/pfob_public_page/g' "$file"
        sed -i 's/wc_save_settings/pfob_save_settings/g' "$file"
        sed -i 's/wc_settings/pfob_settings/g' "$file"

        # Replace AJAX actions
        sed -i 's/wc_test_paypal_connection/pfob_test_paypal_connection/g' "$file"
        sed -i 's/wc_test_r2_connection/pfob_test_r2_connection/g' "$file"
        sed -i 's/wc_sync_paypal_plans/pfob_sync_paypal_plans/g' "$file"

        # Replace JavaScript variables
        sed -i 's/wcAdmin/pfobAdmin/g' "$file"

        # Replace CSS classes (be careful here)
        sed -i 's/wc-admin/pfob-admin/g' "$file"
        sed -i 's/wc-settings/pfob-settings/g' "$file"
        sed -i 's/wc-stats/pfob-stats/g' "$file"
        sed -i 's/wc-dashboard/pfob-dashboard/g' "$file"
        sed -i 's/wc-stat-card/pfob-stat-card/g' "$file"
        sed -i 's/wc-plan/pfob-plan/g' "$file"
        sed -i 's/wc-quick-links/pfob-quick-links/g' "$file"
        sed -i 's/wc-link-card/pfob-link-card/g' "$file"
        sed -i 's/wc-revenue/pfob-revenue/g' "$file"
        sed -i 's/wc-help/pfob-help/g' "$file"

        # Add tagline where appropriate
        if grep -q "Complete project management" "$file"; then
            sed -i 's/Complete project management and team collaboration SaaS platform/Every great plan deploys from the FOB. Complete project management and team collaboration SaaS platform/g' "$file"
        fi
    fi
done

echo "📁 Renaming service class files..."
for file in includes/services/class-wc-*.php; do
    if [ -f "$file" ]; then
        newname=$(echo "$file" | sed 's/class-wc-/class-pfob-/')
        mv "$file" "$newname"
        echo "  Renamed: $file -> $newname"
    fi
done

echo "📁 Renaming model class files..."
for file in includes/models/class-wc-*.php; do
    if [ -f "$file" ]; then
        newname=$(echo "$file" | sed 's/class-wc-/class-pfob-/')
        mv "$file" "$newname"
        echo "  Renamed: $file -> $newname"
    fi
done

echo "📁 Renaming API endpoint files..."
for file in includes/api/class-wc-*.php; do
    if [ -f "$file" ]; then
        newname=$(echo "$file" | sed 's/class-wc-/class-pfob-/')
        mv "$file" "$newname"
        echo "  Renamed: $file -> $newname"
    fi
done

echo "✅ Rebrand complete!"
echo ""
echo "ProjectFOB - Every great plan deploys from the FOB"
echo ""
