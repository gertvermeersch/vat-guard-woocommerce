# Naming Conventions for EU VAT Guard

## Overview
This document outlines the naming conventions used throughout the EU VAT Guard plugin to ensure uniqueness and prevent conflicts with other plugins.

## Namespace
All PHP classes use the namespace: `Stormlabs\EUVATGuard`

## Constants

### Plugin Constants
All plugin-level constants use the `EU_VAT_GUARD_` prefix:
- `EU_VAT_GUARD_VERSION` - Plugin version
- `EU_VAT_GUARD_PLUGIN_FILE` - Main plugin file path
- `EU_VAT_GUARD_PLUGIN_DIR` - Plugin directory path
- `EU_VAT_GUARD_PLUGIN_URL` - Plugin URL
- `EU_VAT_GUARD_PLUGIN_BASENAME` - Plugin basename

### Option Name Constants
All WordPress options use the `eu_vat_guard_` prefix:
- `EU_VAT_GUARD_OPTION_PREFIX` - Base prefix for all options
- `EU_VAT_GUARD_OPTION_REQUIRE_COMPANY` - Require company name setting
- `EU_VAT_GUARD_OPTION_REQUIRE_VAT` - Require VAT number setting
- `EU_VAT_GUARD_OPTION_REQUIRE_VIES` - Require VIES validation setting
- `EU_VAT_GUARD_OPTION_IGNORE_VIES_ERROR` - Ignore VIES errors setting
- `EU_VAT_GUARD_OPTION_ENABLE_BLOCK_CHECKOUT` - Enable block checkout setting
- `EU_VAT_GUARD_OPTION_DISABLE_EXEMPTION` - Disable VAT exemption setting
- `EU_VAT_GUARD_OPTION_COMPANY_LABEL` - Custom company label
- `EU_VAT_GUARD_OPTION_VAT_LABEL` - Custom VAT label
- `EU_VAT_GUARD_OPTION_EXEMPTION_MESSAGE` - Custom exemption message

### Meta Key Constants
Meta keys are defined as constants for consistency:
- `EU_VAT_GUARD_META_VAT_NUMBER` = `'vat_number'` - User meta for VAT number
- `EU_VAT_GUARD_META_COMPANY_NAME` = `'company_name'` - User meta for company name
- `EU_VAT_GUARD_META_ORDER_VAT` = `'billing_eu_vat_number'` - Order meta for VAT number
- `EU_VAT_GUARD_META_ORDER_EXEMPT` = `'billing_is_vat_exempt'` - Order meta for exemption status
- `EU_VAT_GUARD_META_BLOCK_VAT` = `'_wc_other/eu-vat-guard/vat_number'` - Block checkout VAT meta

## Class Names

### Main Classes
All classes follow the pattern: `VAT_Guard_*` within the `Stormlabs\EUVATGuard` namespace

- `VAT_Guard` - Main plugin class
- `VAT_Guard_Admin` - Admin functionality
- `VAT_Guard_VIES` - VIES validation
- `VAT_Guard_Block_Integration` - WooCommerce Blocks integration
- `VAT_Guard_Rate_Importer` - VAT rate importer

### Full Qualified Class Names
When referencing classes from outside the namespace:
```php
\Stormlabs\EUVATGuard\VAT_Guard
\Stormlabs\EUVATGuard\VAT_Guard_Admin
\Stormlabs\EUVATGuard\VAT_Guard_VIES
\Stormlabs\EUVATGuard\VAT_Guard_Block_Integration
\Stormlabs\EUVATGuard\VAT_Guard_Rate_Importer
```

## WordPress Options

### Settings Options
All settings are stored with the `eu_vat_guard_` prefix:

**Basic Settings:**
- `eu_vat_guard_require_company` (boolean)
- `eu_vat_guard_require_vat` (boolean)
- `eu_vat_guard_require_vies` (boolean)
- `eu_vat_guard_ignore_vies_error` (boolean)
- `eu_vat_guard_enable_block_checkout` (boolean)

**Advanced Settings:**
- `eu_vat_guard_disable_exemption` (boolean)
- `eu_vat_guard_company_label` (string)
- `eu_vat_guard_vat_label` (string)
- `eu_vat_guard_exemption_message` (string)

### Settings Groups
- `eu_vat_guard_basic_options` - Basic settings group
- `eu_vat_guard_advanced_options` - Advanced settings group

## User Meta Keys

### Current Keys (Backward Compatible)
- `vat_number` - User's VAT number
- `company_name` - User's company name

**Note:** These keys don't have the plugin prefix for backward compatibility with existing installations. They are defined as constants to maintain consistency.

## Order Meta Keys

### Current Keys
- `billing_eu_vat_number` - Order VAT number (has prefix ✓)
- `billing_is_vat_exempt` - Order VAT exemption status
- `_wc_other/eu-vat-guard/vat_number` - Block checkout VAT number (WooCommerce Blocks format)

## Hooks and Filters

### Action Hooks
All custom action hooks use the `eu_vat_guard_` prefix:
- `eu_vat_guard_customer_vat_updated` - Fired when customer VAT is updated

### Filter Hooks
All custom filter hooks use the `eu_vat_guard_` prefix:
- `eu_vat_guard_validate_vat_number` - Filter VAT validation result
- `eu_vat_guard_vat_exempt_countries` - Filter exempt countries list

## JavaScript/CSS Assets

### File Names
- `vat-guard-checkout.js` - Checkout scripts
- `vat-guard-admin.css` - Admin styles

### JavaScript Objects
- `vatGuardCheckout` - Checkout JavaScript object
- `vatGuardAdmin` - Admin JavaScript object

## Database Tables
Currently, the plugin doesn't create custom database tables. All data is stored using WordPress options and meta APIs.

## Best Practices

### When Adding New Options
Always use the `eu_vat_guard_` prefix:
```php
add_option('eu_vat_guard_new_setting', $default_value);
get_option('eu_vat_guard_new_setting', $default_value);
```

### When Adding New Meta Keys
Define as a constant first:
```php
define('EU_VAT_GUARD_META_NEW_KEY', 'eu_vat_guard_new_key');
update_post_meta($post_id, EU_VAT_GUARD_META_NEW_KEY, $value);
```

### When Adding New Classes
Use the namespace and follow the naming pattern:
```php
namespace Stormlabs\EUVATGuard;

class VAT_Guard_New_Feature {
    // Class implementation
}
```

### When Adding New Hooks
Use the plugin prefix:
```php
do_action('eu_vat_guard_custom_action', $data);
apply_filters('eu_vat_guard_custom_filter', $value, $args);
```

## Migration Considerations

### Future Improvements
If we decide to update meta keys to use proper prefixes in the future, we would need:

1. **Migration Script** to update existing data:
```php
// Example migration for user meta
$users = get_users();
foreach ($users as $user) {
    $old_vat = get_user_meta($user->ID, 'vat_number', true);
    if ($old_vat) {
        update_user_meta($user->ID, 'eu_vat_guard_vat_number', $old_vat);
        // Keep old meta for backward compatibility
    }
}
```

2. **Backward Compatibility Layer** to check both old and new keys
3. **Version Check** to run migration only once
4. **Admin Notice** to inform users about the migration

### Current Status
✅ All options properly prefixed
✅ All classes properly namespaced
✅ All constants properly prefixed
⚠️ User meta keys kept without prefix for backward compatibility
⚠️ Some order meta keys kept without prefix for backward compatibility

## Summary
The plugin follows WordPress best practices for naming conventions with proper prefixes and namespacing to ensure uniqueness and prevent conflicts. All new code should follow these conventions.
