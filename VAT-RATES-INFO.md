# EU VAT Rates Information

This document provides information about the VAT Rate Importer feature in EU VAT Guard for WooCommerce v1.2.0.

## Overview

The VAT Rate Importer allows you to easily import current EU VAT rates for all 27 member states directly into your WooCommerce tax settings. This eliminates the need to manually configure tax rates for each EU country.

## Features

### Country Selection
- Select individual countries or use "Select All" for all EU member states
- Only countries enabled in your WooCommerce selling locations are shown
- Real-time preview of standard and reduced rates for each country

### Rate Types

#### Standard Rates
- The main VAT rate applied to most goods and services
- Automatically imported for all selected countries

#### Reduced Rates
- Lower VAT rates for specific categories of goods
- Common for essential items like food, books, and pharmaceuticals

#### Special Rates
- Category-specific rates (food, books, pharmaceuticals, hotels)
- Optional import - can be enabled/disabled
- Creates separate WooCommerce tax classes for easy product assignment

## Current EU VAT Rates (2024)

| Country | Standard | Reduced | Special Categories |
|---------|----------|---------|-------------------|
| Austria | 20% | 10%, 13% | Food: 10%, Books: 10%, Hotels: 13% |
| Belgium | 21% | 6%, 12% | Food: 6%, Books: 6%, Hotels: 12% |
| Bulgaria | 20% | 9% | Food: 9%, Books: 9% |
| Croatia | 25% | 5%, 13% | Food: 5%, Books: 5%, Hotels: 13% |
| Cyprus | 19% | 5%, 9% | Food: 5%, Books: 5%, Hotels: 9% |
| Czech Republic | 21% | 10%, 15% | Food: 15%, Books: 10% |
| Denmark | 25% | - | No reduced rates |
| Estonia | 20% | 9% | Food: 9%, Books: 9% |
| Finland | 24% | 10%, 14% | Food: 14%, Books: 10% |
| France | 20% | 5.5%, 10% | Food: 5.5%, Books: 5.5%, Pharma: 2.1%, Hotels: 10% |
| Germany | 19% | 7% | Food: 7%, Books: 7% |
| Greece | 24% | 6%, 13% | Food: 13%, Books: 6% |
| Hungary | 27% | 5%, 18% | Food: 5%, Books: 5%, Hotels: 18% |
| Ireland | 23% | 9%, 13.5% | Food: 0%, Books: 0%, Hotels: 13.5% |
| Italy | 22% | 4%, 5%, 10% | Food: 4%, Books: 4%, Pharma: 10% |
| Latvia | 21% | 5%, 12% | Food: 12%, Books: 5% |
| Lithuania | 21% | 5%, 9% | Food: 9%, Books: 9% |
| Luxembourg | 17% | 3%, 8%, 14% | Food: 3%, Books: 3%, Hotels: 14% |
| Malta | 18% | 5%, 7% | Food: 5%, Books: 5%, Hotels: 7% |
| Netherlands | 21% | 9% | Food: 9%, Books: 9% |
| Poland | 23% | 5%, 8% | Food: 5%, Books: 5%, Pharma: 8% |
| Portugal | 23% | 6%, 13% | Food: 6%, Books: 6%, Hotels: 13% |
| Romania | 19% | 5%, 9% | Food: 9%, Books: 5% |
| Slovakia | 20% | 10% | Food: 10%, Books: 10% |
| Slovenia | 22% | 5%, 9.5% | Food: 9.5%, Books: 9.5% |
| Spain | 21% | 4%, 10% | Food: 4%, Books: 4%, Hotels: 10% |
| Sweden | 25% | 6%, 12% | Food: 12%, Books: 6% |

## How It Works

### Import Process
1. Navigate to **EU VAT Guard > VAT Rate Importer** in your WordPress admin
2. Select the countries you want to import rates for
3. Choose whether to include special VAT categories
4. Click "Import Selected VAT Rates"

### WooCommerce Integration
- Creates or updates tax rates in WooCommerce tax tables
- Standard rates get priority 1, reduced rates get priority 2
- Special rates create separate tax classes (e.g., "food", "books")
- Automatically clears WooCommerce tax cache

### Tax Class Assignment
After importing, you can assign the new tax classes to products:
1. Go to **Products > Edit Product**
2. In the **General** tab, find **Tax Class**
3. Select the appropriate class (e.g., "Food (Belgium)" for 6% rate)

## Best Practices

### Before Importing
- Backup your WooCommerce tax settings
- Review your current tax configuration
- Ensure your store's base country is correctly set

### After Importing
- Review the imported rates in **WooCommerce > Settings > Tax**
- Test checkout with different countries and tax classes
- Update product tax classes as needed

### Maintenance
- VAT rates can change (though rarely)
- Re-run the importer to update rates when needed
- The tool will update existing rates rather than duplicate them

## Troubleshooting

### Common Issues

**No countries showing**
- Check that countries are enabled in **WooCommerce > Settings > General > Selling locations**

**Rates not applying at checkout**
- Verify tax calculation is enabled in WooCommerce
- Check that customer's country matches imported rates
- Ensure products have correct tax class assigned

**Duplicate rates**
- The importer prevents duplicates by checking existing rates
- If you see duplicates, they may have been created manually

### Support
For technical support or questions:
- Email: dev@stormlabs.be
- Website: https://stormlabs.be/

## Legal Notice

VAT rates are provided for convenience and are based on publicly available information as of 2024. Always verify current rates with official tax authorities before relying on them for business purposes. Stormlabs is not responsible for any tax compliance issues arising from the use of this tool.