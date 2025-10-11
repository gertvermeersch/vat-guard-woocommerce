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
- **Stable Tag**: `1.0.0`

## Support

- **WordPress.org**: [Plugin Support Forum](https://wordpress.org/support/plugin/eu-vat-guard-for-woocommerce/)
- **Email**: dev@stormlabs.be
- **Website**: [https://stormlabs.be/](https://stormlabs.be/)

## License

This plugin is licensed under the GPLv2 or later.

---
**Thank you for using EU VAT Guard for WooCommerce!**
