# Pre-Release Checklist for Version 1.3.0

## Version Numbers ✅
- [x] Main plugin file header: `1.3.0`
- [x] `EU_VAT_GUARD_VERSION` constant: `1.3.0`
- [x] readme.txt Stable tag: `1.3.0`
- [x] README.md Stable tag: `1.3.0`
- [x] CHANGELOG.md: `1.3.0` entry added
- [x] RELEASE-NOTES-1.3.0.md created

## Code Quality ✅
- [x] All classes use proper namespace `Stormlabs\EUVATGuard`
- [x] All constants defined and documented
- [x] No PHP errors or warnings
- [x] All diagnostics clean
- [x] Backward compatibility maintained
- [x] Class alias added for `EU_VAT_Guard`

## Documentation ✅
- [x] CHANGELOG.md updated
- [x] readme.txt changelog updated
- [x] README.md changelog updated
- [x] RELEASE-NOTES-1.3.0.md created
- [x] NAMESPACE-CHANGES.md created
- [x] NAMING-CONVENTIONS.md created

## Translations ✅
- [x] Dutch (nl_NL) - Complete
- [x] French (fr_FR) - Complete
- [x] German (de_DE) - Significantly improved
- [x] POT file up to date
- [ ] MO files compiled (run: `bash translation.sh`)

## Testing Checklist

### Basic Functionality
- [ ] Plugin activates without errors
- [ ] Plugin deactivates without errors
- [ ] Settings page loads correctly
- [ ] Settings save correctly

### Frontend Testing
- [ ] Registration form shows VAT fields
- [ ] Account page shows VAT fields
- [ ] Classic checkout shows VAT fields
- [ ] Block checkout shows VAT fields
- [ ] VAT validation works
- [ ] VAT exemption applies correctly

### Admin Testing
- [ ] Admin menu appears correctly
- [ ] Settings tabs work
- [ ] VAT Rate Importer works
- [ ] Order admin shows VAT info
- [ ] Order edit VAT field works (read-only/edit modes)

### Compatibility Testing
- [ ] Works with latest WordPress (6.8)
- [ ] Works with latest WooCommerce
- [ ] No conflicts with common plugins
- [ ] Block checkout compatibility
- [ ] Classic checkout compatibility

### Backward Compatibility
- [ ] Existing installations upgrade smoothly
- [ ] Old class name `EU_VAT_Guard` still works
- [ ] All existing data intact
- [ ] No database errors

## Files to Include in Release

### Core Files
- [x] vat-guard-woocommerce.php
- [x] readme.txt
- [x] README.md
- [x] CHANGELOG.md
- [x] RELEASE-NOTES-1.3.0.md
- [x] NAMESPACE-CHANGES.md
- [x] NAMING-CONVENTIONS.md
- [x] LICENSE.txt (if exists)

### Includes Directory
- [x] includes/class-vat-guard.php
- [x] includes/class-vat-guard-admin.php
- [x] includes/class-vat-guard-vies.php
- [x] includes/class-vat-guard-block-integration.php
- [x] includes/class-vat-guard-rate-importer.php
- [x] includes/pdf-template-helpers.php

### Languages Directory
- [x] All .po files
- [ ] All .mo files (compile before release)
- [x] .pot file

### Assets Directory (if exists)
- [ ] Screenshots
- [ ] Icons
- [ ] Banners

## Pre-Release Actions

### Code
- [ ] Run PHP CodeSniffer (WordPress standards)
- [ ] Run PHP linter
- [ ] Check for deprecated functions
- [ ] Remove debug code
- [ ] Remove console.log statements

### Translations
- [ ] Compile all .mo files: `cd languages && bash ../translation.sh`
- [ ] Test translations in different languages
- [ ] Verify WPML compatibility

### Documentation
- [ ] Proofread all documentation
- [ ] Check all links work
- [ ] Verify code examples
- [ ] Update screenshots if needed

### Package
- [ ] Create clean build directory
- [ ] Copy only necessary files
- [ ] Exclude development files (.git, .DS_Store, etc.)
- [ ] Create ZIP file
- [ ] Test ZIP installation

## WordPress.org Submission

### Before Submission
- [ ] Test on clean WordPress install
- [ ] Test with default theme
- [ ] Test with no other plugins
- [ ] Verify readme.txt format
- [ ] Check plugin header format
- [ ] Verify license compatibility

### Submission Checklist
- [ ] Update SVN trunk
- [ ] Create SVN tag for 1.3.0
- [ ] Update assets (if changed)
- [ ] Verify plugin page displays correctly
- [ ] Test download and installation

## Post-Release

### Monitoring
- [ ] Monitor support forum
- [ ] Check for error reports
- [ ] Monitor plugin stats
- [ ] Check compatibility reports

### Communication
- [ ] Announce on website
- [ ] Social media announcement
- [ ] Email newsletter (if applicable)
- [ ] Update documentation site

## Notes

### Known Issues
- None currently

### Future Improvements
- Consider migrating user meta keys to prefixed versions in future major release
- Add automated testing suite
- Consider adding REST API endpoints

### Support Preparation
- Be ready to answer questions about namespace changes
- Prepare FAQ about backward compatibility
- Have rollback instructions ready (just in case)

---

**Release Manager:** _________________  
**Date Completed:** _________________  
**Release Date:** January 29, 2025
