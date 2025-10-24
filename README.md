# EU VAT Guard for WooCommerce

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce Version](https://img.shields.io/badge/WooCommerce-4.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.2%2B-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**EU VAT Guard for WooCommerce** is a powerful plugin that adds advanced EU VAT number management and validation to your WooCommerce store. It helps you collect, validate, and manage company VAT numbers for your B2B customers, ensuring compliance and a smooth checkout experience.

## Features

- **Company Name & VAT Number Fields**
  - Adds company name and VAT number fields to registration, account, and checkout forms.
  - Fields can be set as required or optional via the admin settings.

- **EU VAT Number Validation**
  - Offline (format/structure) validation for all EU VAT numbers.
  - Optional real-time validation with the official [VIES](https://ec.europa.eu/taxation_customs/vies/) webservice.
  - Option to allow checkout if VIES is unavailable (configurable in settings).

- **Advanced Customization** *(New in v1.1.0)*
  - Custom field labels and exemption messages
  - Option to disable VAT exemption while keeping validation
  - WPML compatibility for multilingual stores
  - Advanced settings tab for fine-tuning

- **PDF Invoice Integration** *(New in v1.1.0)*
  - Full compatibility with WooCommerce PDF Invoices & Packing Slips
  - VAT numbers and exemption status on PDF invoices
  - Template helper functions for custom implementations

- **VAT Rate Importer** *(New in v1.2.0)*
  - Import current EU VAT rates for all 27 member states
  - Support for standard, reduced, and special category rates
  - WooCommerce-style country selection interface
  - Automatic tax class creation and rate management

- **WooCommerce Integration**
  - VAT number is pre-filled for logged-in users at checkout.
  - VAT number is saved to the order and displayed in the WooCommerce admin order screen and order emails.

- **Admin Settings Page**
  - Located under WooCommerce > VAT Guard.
  - Configure which fields are required.
  - Enable/disable VIES validation and error handling.
  - Friendly thank you message and easy-to-use options.

## Getting Started

1. **Install the Plugin**
   - Upload the plugin folder to your `/wp-content/plugins/` directory, or install via the WordPress admin.
   - Activate the plugin through the 'Plugins' menu in WordPress.

2. **Configure Settings**
   - Go to **WooCommerce > VAT Guard** in your WordPress admin.
   - Choose which fields are required (Company Name, VAT Number).
   - Enable VIES validation if you want real-time VAT number checks.
   - Optionally, allow checkout if VIES is unavailable.

3. **Usage**
   - Customers will see the new fields on registration, account, and checkout forms.
   - VAT numbers are validated according to your settings.
   - VAT numbers are shown in the order admin and in order emails.

## Development

### Requirements
- WordPress 5.0+
- WooCommerce 4.0+
- PHP 7.2+
- SOAP extension (for VIES validation)

### File Structure
```
eu-vat-guard-for-woocommerce/
├── includes/
│   ├── class-vat-guard.php              # Main plugin class
│   ├── class-vat-guard-admin.php        # Admin interface
│   ├── class-vat-guard-vies.php         # VIES validation
│   └── class-vat-guard-block-integration.php # Block checkout support
├── assets/
│   └── js/
│       └── vat-guard-block-checkout.js  # Block checkout JavaScript
├── languages/
│   ├── compile-translations.php         # Translation compiler
│   └── *.po, *.mo                      # Translation files
├── readme.txt                          # WordPress.org readme
├── README.md                           # This file
└── vat-guard-woocommerce.php           # Main plugin file
```

### Translation

The plugin supports all 27 EU languages. To compile translations:

```bash
cd languages/
php compile-translations.php
```

### WordPress.org Submission

This plugin uses:
- **Text Domain**: `eu-vat-guard-for-woocommerce`
- **Plugin Slug**: `eu-vat-guard-for-woocommerce`
- **Stable Tag**: `1.3.0`

## Changelog

### Version 1.3.0 *(Latest)*
- 🏗️ **Major**: Implemented proper PHP namespacing (`Stormlabs\EUVATGuard`)
- 📦 **New**: Added comprehensive plugin constants for better code organization
- 🔧 **Improved**: Enhanced code structure following WordPress best practices
- 🌍 **Updated**: Completed translations for Dutch, French, and German
- 🎨 **Enhanced**: Admin order edit VAT field now shows read-only when not editing
- 📚 **Added**: Comprehensive documentation (NAMESPACE-CHANGES.md, NAMING-CONVENTIONS.md)
- ✅ **Maintained**: 100% backward compatibility with existing installations
- 🔒 **Security**: Enhanced code security through proper namespacing

### Version 1.2.0
- ✨ **New**: VAT Rate Importer tool for all 27 EU member states
- ✨ **New**: Support for special VAT categories (food, books, pharmaceuticals, hotels)
- 🔧 **Improved**: Enhanced WooCommerce integration with smart country filtering
- 📊 **Added**: Complete VAT rates overview table

### Version 1.1.0
- ✨ **New**: Advanced settings tab with exemption rules customization
- ✨ **New**: Custom field labels and exemption messages
- ✨ **New**: WPML compatibility for custom strings
- ✨ **New**: PDF invoice integration for WooCommerce PDF Invoices & Packing Slips
- ✨ **New**: PDF template helper functions
- 🔧 **Improved**: Admin interface with separate option groups
- 🐛 **Fixed**: Admin options not saving correctly between tabs
- 🔒 **Enhanced**: Security with proper nonce verification suppression
- 📝 **Updated**: Text domain for better WordPress.org compatibility

### Version 1.0.0
- 🎉 Initial release
- Company name and VAT number fields
- EU VAT number format validation
- Optional VIES real-time validation
- Automatic VAT exemption for B2B transactions
- WooCommerce block checkout support
- Admin settings interface

## Support

- **WordPress.org**: [Plugin Support Forum](https://wordpress.org/support/plugin/eu-vat-guard-for-woocommerce/)
- **Email**: dev@stormlabs.be
- **Website**: [https://stormlabs.be/](https://stormlabs.be/)

## License

This plugin is licensed under the GPLv2 or later.

---
**Thank you for using EU VAT Guard for WooCommerce!**
