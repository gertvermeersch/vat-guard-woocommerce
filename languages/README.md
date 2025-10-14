# EU VAT Guard for WooCommerce - Translations

This directory contains translation files for EU VAT Guard for WooCommerce plugin.

## Available Languages

The plugin is fully translated into the following languages:

### Western Europe
- **German (Germany)** - `de_DE` - Deutsch
- **French (France)** - `fr_FR` - Français  
- **Spanish (Spain)** - `es_ES` - Español
- **Italian (Italy)** - `it_IT` - Italiano
- **Dutch (Netherlands)** - `nl_NL` - Nederlands
- **Portuguese (Portugal)** - `pt_PT` - Português

### Nordic Countries
- **Swedish (Sweden)** - `sv_SE` - Svenska
- **Danish (Denmark)** - `da_DK` - Dansk
- **Finnish (Finland)** - `fi_FI` - Suomi

### Eastern Europe
- **Polish (Poland)** - `pl_PL` - Polski
- **Czech (Czech Republic)** - `cs_CZ` - Čeština
- **Slovak (Slovakia)** - `sk_SK` - Slovenčina
- **Hungarian (Hungary)** - `hu_HU` - Magyar
- **Romanian (Romania)** - `ro_RO` - Română
- **Bulgarian (Bulgaria)** - `bg_BG` - Български
- **Croatian (Croatia)** - `hr_HR` - Hrvatski
- **Slovenian (Slovenia)** - `sl_SI` - Slovenščina

### Baltic States
- **Estonian (Estonia)** - `et_EE` - Eesti
- **Latvian (Latvia)** - `lv_LV` - Latviešu
- **Lithuanian (Lithuania)** - `lt_LT` - Lietuvių

### Other EU Countries
- **Greek (Greece)** - `el_GR` - Ελληνικά
- **Maltese (Malta)** - `mt_MT` - Malti
- **Welsh (United Kingdom)** - `cy_GB` - Cymraeg
- **Irish (Ireland)** - `ga_IE` - Gaeilge

## File Structure

- `eu-vat-guard.pot` - Translation template file
- `eu-vat-guard-[locale].po` - Translation files for each language
- `eu-vat-guard-[locale].mo` - Compiled translation files (generated automatically)

## Translation Coverage

All translation files include:

- VAT number field labels and placeholders
- Validation error messages
- Exemption status messages
- Admin interface strings
- Block checkout integration strings
- VIES validation messages

## Country-Specific VAT Terminology

The translations use appropriate VAT terminology for each country:

- **Germany**: USt-IdNr. (Umsatzsteuer-Identifikationsnummer)
- **France**: Numéro de TVA
- **Spain**: Número de IVA
- **Italy**: Partita IVA
- **Netherlands**: BTW-nummer
- **Sweden**: Momsregistreringsnummer
- **Denmark**: CVR-nummer
- **Finland**: ALV-numero
- **Poland**: Numer VAT
- **Czech Republic**: DIČ (Daňové identifikační číslo)
- **And many more...**

## Contributing Translations

To contribute a new translation or improve an existing one:

1. Copy the `eu-vat-guard.pot` template file
2. Rename it to `eu-vat-guard-[locale].po`
3. Translate all strings using a PO editor like Poedit
4. Test the translation in your WordPress installation
5. Submit a pull request or contact the plugin author

## Generating MO Files

WordPress requires compiled `.mo` files for translations to work. These are automatically generated from `.po` files when:

- Using translation tools like Poedit
- Using WP-CLI: `wp i18n make-mo languages/`
- Using build tools in your development workflow

## Plugin Text Domain

The plugin uses the text domain: `eu-vat-guard-for-woocommerce`

## WordPress Language Packs

If your language is not available, you can also contribute translations through WordPress.org's translation system at:
https://translate.wordpress.org/projects/wp-plugins/eu-vat-guard/

## Support

For translation-related issues or questions, please contact:
- Email: dev@stormlabs.be
- Website: https://stormlabs.be/

---

*Last updated: January 14, 2025 - Version 1.1.0*