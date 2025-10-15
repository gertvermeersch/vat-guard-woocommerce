# Changelog

All notable changes to EU VAT Guard for WooCommerce will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-01-14

### Added
- **VAT Rate Importer**: New dedicated admin tool for importing current EU VAT rates
- **Comprehensive VAT Database**: Complete database of all 27 EU member states' VAT rates
- **Country Selection Interface**: WooCommerce-style country selection with "Select All" option
- **Reduced Rate Support**: Optional import of reduced VAT rates for specific goods
- **Smart Rate Management**: Automatic creation and updating of WooCommerce tax rates
- **Clean Rate Display**: Tax rates display as clean percentages (e.g., "21%") on checkout
- **Rate Overview Table**: Complete overview of all EU VAT rates
- **WooCommerce Integration**: Only shows countries enabled in WooCommerce selling locations

### Changed
- **Admin Menu Structure**: Moved to dedicated main menu with submenu items
- **Menu Icon**: Added shield icon for better visual identification
- **Version**: Updated to 1.2.0

### Features
- **27 EU Countries**: Austria, Belgium, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Ireland, Italy, Latvia, Lithuania, Luxembourg, Malta, Netherlands, Poland, Portugal, Romania, Slovakia, Slovenia, Spain, Sweden
- **Multiple Rate Types**: Standard rates and optional reduced rates
- **Tax Class Creation**: Automatic creation of appropriate WooCommerce tax classes
- **Clean Display**: Tax rates show as simple percentages on checkout (e.g., "21%" instead of "Belgium Standard (21%)")
- **Rate Updates**: Smart updating of existing rates without duplication
- **Cache Management**: Automatic clearing of WooCommerce tax cache after import

### Technical
- **Database Integration**: Direct integration with WooCommerce tax rate tables
- **Performance Optimized**: Efficient batch processing of tax rate imports
- **Error Handling**: Comprehensive validation and error reporting
- **Security**: Full nonce verification and capability checks

## [1.1.0] - 2025-01-14

### Added
- **Advanced Settings Tab**: New admin tab for fine-tuning exemption rules and customization
- **Custom Field Labels**: Override default "Company Name" and "VAT Number" labels
- **Custom Exemption Messages**: Override default "VAT exempt for this order" message
- **Disable VAT Exemption Option**: Collect VAT numbers without applying tax exemptions
- **WPML Integration**: Custom strings are automatically registered for translation
- **PDF Invoice Integration**: Full compatibility with WooCommerce PDF Invoices & Packing Slips
- **PDF Template Helpers**: Helper functions for custom PDF template implementations
- **Static PDF Methods**: `EU_VAT_Guard::get_pdf_vat_info()` and `EU_VAT_Guard::display_pdf_vat_block()`

### Changed
- **Admin Interface**: Separated basic and advanced settings into different option groups
- **Text Domain**: Updated from `eu-vat-guard` to `eu-vat-guard-for-woocommerce` for WordPress.org compatibility
- **Option Names**: Shortened from `vat_guard_woocommerce_*` to `eu_vat_guard_*` format
- **Performance**: Admin hooks now only load on admin pages (wrapped in `is_admin()`)

### Fixed
- **Admin Options Bug**: Settings on one tab no longer clear settings on other tabs
- **Security Issues**: Replaced `_e()` with `esc_html_e()` and `esc_attr_e()` for proper output escaping
- **Input Sanitization**: Added proper `wp_unslash()` and sanitization for all `$_POST` data
- **Nonce Verification**: Added appropriate suppression comments for false positive warnings
- **Translation Loading**: Fixed text domain to match plugin folder name

### Security
- **Enhanced Input Validation**: All user input now properly sanitized with `wp_unslash()` and `sanitize_text_field()`
- **Output Escaping**: All output properly escaped with `esc_html_e()`, `esc_attr_e()`, and `esc_attr()`
- **Nonce Verification**: Proper handling of WordPress and WooCommerce form submissions
- **CLI Script Security**: Added appropriate escaping suppressions for CLI-only scripts

### Developer
- **Code Quality**: Improved WordPress Coding Standards compliance
- **Documentation**: Enhanced inline documentation and README files
- **Template Integration**: Easy integration with custom PDF templates
- **Helper Functions**: Comprehensive API for developers

## [1.0.0] - 2025-01-07

### Added
- Initial release
- Company name and VAT number fields on registration, account, and checkout
- EU VAT number format validation for all 27 EU member states
- Optional VIES real-time validation with EU webservice
- Automatic VAT exemption (reverse charge) for valid B2B transactions
- WooCommerce Block checkout support
- Admin settings interface under WooCommerce menu
- Email integration showing VAT numbers in order emails
- Admin order screen VAT number display

### Features
- **Validation Rules**: Comprehensive VAT number format validation
- **VIES Integration**: Real-time validation with official EU service
- **Block Support**: Full compatibility with WooCommerce's new checkout blocks
- **B2B Focus**: Designed for business-to-business transactions
- **EU Compliance**: Follows EU VAT regulations for cross-border transactions

---

## Release Notes

### Version 1.1.0 Highlights

This major update focuses on **customization** and **integration**:

- **🎨 Advanced Customization**: New admin tab allows complete control over labels and messages
- **🌍 WPML Ready**: Full multilingual support for international stores  
- **📄 PDF Integration**: Seamless integration with popular PDF invoice plugins
- **🔧 Developer Friendly**: Comprehensive API and helper functions
- **🛡️ Enhanced Security**: WordPress.org ready with proper security measures

### Migration Notes

- **Settings Migration**: Existing settings are automatically preserved
- **Backward Compatibility**: All existing functionality continues to work
- **New Features**: New features are opt-in and don't affect existing behavior

### Support

For questions about this release:
- **Email**: dev@stormlabs.be
- **Website**: [stormlabs.be](https://stormlabs.be/)
- **WordPress.org**: [Plugin Support Forum](https://wordpress.org/support/plugin/eu-vat-guard-for-woocommerce/)