# VAT Guard for WooCommerce

**VAT Guard for WooCommerce** is a powerful plugin that adds advanced EU VAT number management and validation to your WooCommerce store. It helps you collect, validate, and manage company VAT numbers for your B2B customers, ensuring compliance and a smooth checkout experience.

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

## Requirements
- WordPress 5.0+
- WooCommerce 4.0+
- PHP 7.2+
- SOAP enabled (for VIES validation)

## Support
For questions, suggestions, or issues, please open an issue on the GitHub repository or contact the plugin author.

---
**Thank you for using VAT Guard for WooCommerce!**
