=== EU VAT Guard for WooCommerce ===
Contributors: stormlabs, bytefarmer
Tags: woocommerce, vat, eu, tax, b2b, vies, validation, company
Requires: woocommerce
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage EU VAT numbers and company information for WooCommerce customers and B2B with validation and tax exemption support.

== Description ==

**EU VAT Guard for WooCommerce** is a powerful plugin that adds advanced EU VAT number management and validation to your WooCommerce store. It helps you collect, validate, and manage company VAT numbers for your B2B customers, ensuring compliance and a smooth checkout experience.

= Key Features =

* **Company Name & VAT Number Fields** - Adds company name and VAT number fields to registration, account, and checkout forms
* **EU VAT Number Validation** - Offline format validation for all EU VAT numbers with optional real-time VIES validation (uses European Commission's VIES service)
* **Automatic VAT Exemption** - Applies reverse charge VAT exemption for valid B2B transactions between EU member states
* **VAT Rate Importer** - Import current EU VAT rates for all 27 member states with special categories support
* **WooCommerce Integration** - VAT numbers are saved to orders and displayed in admin and emails
* **Block Checkout Support** - Full compatibility with WooCommerce's new block-based checkout
* **Advanced Customization** - Custom labels, messages, and exemption rules
* **WPML Compatible** - Full multilingual support for international stores
* **PDF Integration** - Compatible with WooCommerce PDF Invoices & Packing Slips

= VAT Exemption Rules =

VAT exemption is automatically applied when ALL conditions are met:
* Customer provides a valid EU VAT number
* VAT number country differs from your store's base country
* Shipping method is NOT local pickup
* Billing and shipping countries match the VAT number country

= Supported Features =

* Classic WooCommerce checkout
* Block-based checkout (Cart & Checkout blocks)
* Customer registration and account pages
* Admin order management
* Email notifications
* All 27 EU member states VAT formats
* VIES real-time validation (optional)
* PDF invoice integration (WooCommerce PDF Invoices & Packing Slips)
* Advanced customization options
* WPML multilingual support
* Custom field labels and messages

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/eu-vat-guard-for-woocommerce/` or install via WordPress admin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **WooCommerce > VAT Guard** to configure settings
4. Choose which fields are required and enable VIES validation if desired

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce blocks? =

Yes! The plugin fully supports both classic WooCommerce checkout and the new block-based checkout system.

= What VAT number formats are supported? =

All 27 EU member states VAT number formats are supported with proper validation.

= Is VIES validation required? =

No, VIES validation is optional. You can use format-only validation or enable real-time VIES checking.

= Does this handle VAT exemptions automatically? =

Yes, the plugin automatically applies VAT exemptions for valid B2B transactions between different EU countries.

== Screenshots ==

1. Admin settings page with easy configuration options
2. VAT number field on checkout page
3. Company and VAT fields on registration form
4. VAT information displayed in order admin

== Changelog ==

= 1.3.0 =
* **Major Code Refactoring** - Implemented proper PHP namespacing for all classes
* Added namespace `Stormlabs\EUVATGuard` to prevent conflicts with other plugins
* Added comprehensive plugin constants for better code organization
* Added option name constants for all WordPress options
* Added meta key constants for consistent data handling
* Improved code structure following WordPress best practices
* Enhanced backward compatibility with class aliases
* Added proper use statements for WordPress and WooCommerce classes
* Fixed admin order edit mode detection for VAT field display
* Improved VAT field display - now shows read-only when not in edit mode
* Updated all translation files (Dutch, French, German) with missing strings
* Added complete documentation for namespace changes and naming conventions
* Enhanced security and maintainability of codebase
* No breaking changes - fully backward compatible with existing installations

= 1.2.0 =
* Added VAT Rate Importer tool for importing current EU VAT rates
* Added comprehensive database of all 27 EU member states' VAT rates
* Added support for special VAT categories (food, books, pharmaceuticals, hotels)
* Added country selection interface with "Select All" functionality
* Added automatic WooCommerce tax rate creation and updating
* Added complete VAT rates overview table
* Changed admin menu structure to dedicated main menu
* Enhanced WooCommerce integration with smart country filtering

= 1.1.0 =
* Added Advanced settings tab with exemption rules customization
* Added custom field labels and exemption messages
* Added WPML compatibility for custom strings
* Added PDF invoice integration for WooCommerce PDF Invoices & Packing Slips
* Added PDF template helper functions
* Improved admin interface with separate option groups
* Fixed admin options not saving correctly between tabs
* Enhanced security with proper nonce verification suppression
* Updated text domain for better WordPress.org compatibility

= 1.0.0 =
* Initial release
* Company name and VAT number fields
* EU VAT number format validation
* Optional VIES real-time validation
* Automatic VAT exemption for B2B transactions
* WooCommerce block checkout support
* Admin settings interface

== Upgrade Notice ==

= 1.3.0 =
Major code refactoring with proper namespacing and improved code structure. Fully backward compatible. Includes translation updates and enhanced admin interface. Recommended for all users.

= 1.1.0 =
Major update with advanced customization options, PDF invoice integration, and WPML support. Recommended for all users.

= 1.0.0 =
Initial release of EU VAT Guard for WooCommerce.

== External Services ==

This plugin optionally uses the European Commission's VIES (VAT Information Exchange System) service for real-time VAT number validation.

**VIES Service Details:**
* **Service:** European Commission VIES VAT validation service
* **Purpose:** Real-time validation of EU VAT numbers to verify their authenticity
* **Data Sent:** Only the VAT number (country code and number) that customers enter during checkout, registration, or account updates
* **When Data is Sent:** Only when VIES validation is enabled in plugin settings AND a customer enters a VAT number
* **Service URL:** https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl
* **Privacy Policy:** https://ec.europa.eu/info/privacy-policy_en
* **Terms of Service:** https://ec.europa.eu/taxation_customs/vies/faq.html

**Important Notes:**
* VIES validation is completely optional and disabled by default
* No personal data (names, addresses, emails) is ever sent to VIES
* Only the VAT number itself is transmitted for validation
* The plugin works fully without VIES validation using offline format checking
* You can disable VIES validation at any time in the plugin settings

== Requirements ==

* WordPress 5.0 or higher
* WooCommerce 4.0 or higher
* PHP 7.2 or higher
* SOAP extension (for VIES validation)

== Support ==

For support, feature requests, or bug reports, please contact us through our website or the WordPress.org support forums.