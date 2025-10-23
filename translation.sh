#!/bin/bash

PLUGIN_SLUG="eu-vat-guard-for-woocommerce"
TEXT_DOMAIN="eu-vat-guard-for-woocommerce"
LANGUAGES_DIR="languages"
POT_FILE="${LANGUAGES_DIR}/${TEXT_DOMAIN}.pot"

# Step 1: Generate/update POT file
echo "Generating POT file..."
wp i18n make-pot . "${POT_FILE}" --exclude=node_modules,vendor,tests

# Step 2: Update all existing PO files
echo "Updating existing PO files..."
for po_file in ${LANGUAGES_DIR}/*.po; do
    if [ -f "$po_file" ]; then
        echo "  Updating $(basename $po_file)..."
        msgmerge --update --backup=none "$po_file" "${POT_FILE}"
    fi
done