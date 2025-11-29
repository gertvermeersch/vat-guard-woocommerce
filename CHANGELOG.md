# Changelog

All notable changes to EU VAT Guard for WooCommerce will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.6] - 2024-11-29

### Improved
- **Admin Interface**: Updated admin page with direct links to WordPress.org support and reviews
- **User Experience**: Replaced email contact with convenient support forum and review links
- **Accessibility**: Better access to help resources and feedback channels

### Technical
- Enhanced admin interface for better community engagement
- Improved user guidance for support and feedback

## [1.3.5] - 2024-11-28

### Compatibility
- **WordPress 6.9**: Tested and confirmed compatible with WordPress 6.9
- **Technical**: Updated version number for WordPress 6.9 release compatibility

## [1.3.4] - 2025-11-13

### Changed
- **Code Optimization**: Removed redundant PDF integration initialization from admin constructor
- **Improved Structure**: PDF integration now loads only during AJAX requests when needed
- **Performance**: Reduced unnecessary code execution in admin context

### Technical
- PDF integration initialization moved exclusively to AJAX context in admin class
- Cleaner separation of concerns between admin UI and AJAX handlers
- Improved code structure and maintainability

## [1.3.3] - 2025-10-27

### Added
- **Order Display**: VAT information now displays on order confirmation pages and My Account order views
- **Smart Display Logic**: When block checkout is enabled, only shows VAT exemption status (VAT number shown by WooCommerce)
- **Classic Checkout Display**: When block checkout is disabled, shows complete VAT information section

### Changed
- **Meta Key Prefixes**: All meta keys now use `_eu_vat_guard_` prefix for better organization and conflict prevention
  - User meta: `vat_number` → `_eu_vat_guard_vat_number`
  - User meta: `company_name` → `_eu_vat_guard_company_name`
  - Order meta: `billing_eu_vat_number` → `_eu_vat_guard_order_vat_number`
  - Order meta: `billing_is_vat_exempt` → `_eu_vat_guard_order_vat_exempt`
- **Constants Usage**: All code now uses defined constants instead of hardcoded meta key strings

### Fixed
- **Admin Notices**: Fixed admin error notices not displaying when VAT validation fails in order editing
- **Block Integration**: Fixed undefined constant error in block checkout sanitize callback
- **Order Confirmation**: VAT information now properly displays regardless of block support setting
- **VAT Rate Importer**: Fixed VAT rate database issue

### Technical
- Improved code maintainability with consistent meta key usage
- Enhanced error handling for admin VAT validation
- Better separation of concerns between classic and block checkout displays

## [1.3.2] - 2025-10-26

## [1.3.1] - 2025-10-24

### Fixed

- Plugin checker flagged unsafe output (false positive)
- Notes for translators added

## [1.3.0] - 2025-10-23

### Added
- **Namespacing**: Implemented proper PHP namespace `Stormlabs\EUVATGuard` for all classes
- **Constants**: Added comprehensive plugin constants for better code organization
  - Plugin constants: `EU_VAT_GUARD_VERSION`, `EU_VAT_GUARD_PLUGIN_FILE`, etc.
  - Option name constants for all WordPress options
  - Meta key constants for consistent data handling
- **Documentation**: Created comprehensive documentation files
  - `NAMESPACE-CHANGES.md` - Details about namespace implementation
  - `NAMING-CONVENTIONS.md` - Complete naming conventions guide
- **Translations**: Completed missing translations for Dutch (nl_NL), French (fr_FR), and German (de_DE)
- **Admin Enhancement**: VAT field now displays as read-only when not in edit mode

### Changed
- **Code Structure**: Refactored all classes to use proper namespacing
- **Class Names**: Updated class names within namespace (e.g., `EU_VAT_Guard` → `VAT_Guard`)
- **Use Statements**: Added proper use statements for WordPress and WooCommerce classes
- **Code Organization**: Improved overall code structure following WordPress best practices

### Fixed
- Admin order edit mode detection for VAT field display
- VAT field now correctly shows as read-only text when viewing order (not editing)
- Translation strings for admin interface in multiple languages

### Backward Compatibility
- Added class alias for `EU_VAT_Guard` to maintain backward compatibility
- All existing integrations continue to work without changes
- No database migrations required
- User meta keys kept unchanged for compatibility

### Security
- Enhanced code security with proper namespacing
- Improved code maintainability and reduced conflict potential

## [1.2.1] - 2025-10-22

### Fixed
- Minor bug fixes and improvements

## [1.2.0] - 2025-10-21

### Added
- VAT Rate Importer tool for importing current EU VAT rates
- Comprehensive database of all 27 EU member states' VAT rates
- Support for special VAT categories (food, books, pharmaceuticals, hotels)
- Country selection interface with "Select All" functionality
- Automatic WooCommerce tax rate creation and updating
- Complete VAT rates overview table

### Changed
- Admin menu structure to dedicated main menu
- Enhanced WooCommerce integration with smart country filtering

## [1.1.0] - 2025-10-19

### Added
- Advanced settings tab with exemption rules customization
- Custom field labels and exemption messages
- WPML compatibility for custom strings
- PDF invoice integration for WooCommerce PDF Invoices & Packing Slips
- PDF template helper functions

### Changed
- Improved admin interface with separate option groups

### Fixed
- Admin options not saving correctly between tabs
- Enhanced security with proper nonce verification suppression

### Updated
- Text domain for better WordPress.org compatibility

## [1.0.0] - 2025-10-16

### Added
- Initial release
- Company name and VAT number fields
- EU VAT number format validation
- Optional VIES real-time validation
- Automatic VAT exemption for B2B transactions
- WooCommerce block checkout support
- Admin settings interface

---

## Version Numbering

We use [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for new functionality in a backward compatible manner
- **PATCH** version for backward compatible bug fixes

## Links

- [Plugin Homepage](https://stormlabs.be/)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/eu-vat-guard-for-woocommerce/)
- [GitHub Repository](#) (if applicable)
- [Support Forum](https://wordpress.org/support/plugin/eu-vat-guard-for-woocommerce/)
