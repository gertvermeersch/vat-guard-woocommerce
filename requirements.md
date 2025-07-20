# VAT Guard for WooCommerce – Requirements

## 1. Purpose & Scope
VAT Guard provides EU VAT compliance for WooCommerce stores.  The free (Lite) plugin covers essential validation and tax‑rate handling for small merchants; the Pro add‑on automates high‑volume workflows (evidence capture, OSS/IOSS reporting, rate syncing) and offers premium support.

## 2. Definitions
| Term | Meaning |
|------|---------|
| **Lite** | GPL‑licensed plugin hosted on WordPress.org. |
| **Pro** | Commercial add‑on distributed via EDD Software Licensing or Freemius. |
| **Store Owner** | WordPress user with capability `manage_woocommerce`. |
| **Order Evidence** | Country, VAT number, IP address, timestamp data stored for audits. |

## 3. Functional Requirements
### 3.1 Lite (Free)
1. **VAT Number Field** automatically appears on WooCommerce checkout when billing country ≠ store base country.
2. **EC VIES Validation**:  
   * Real‑time SOAP request on checkout submit.  
   * On success → mark order meta `vat_guard_vat_number_valid = true` and remove tax if customer is in another EU country.
3. **EU Standard Tax Rates Importer** accessible at **WooCommerce → VAT Guard → Tools → Import rates**.
4. **Basic Reporting**: CSV export (order_id, date, country, VAT number, VAT collected) under **WooCommerce → Reports → VAT Guard**.
5. **Price Display Adjustment**: front‑end prices include or exclude VAT based on GeoIP (MaxMind free DB). Fallback to store base.
6. **HPOS Compatibility**: all CRUD via `wc_get_orders()`, `wc_get_products()`.
7. **Translations Ready**: every string wrapped in `__(…)` or `_e(…)`.

### 3.2 Pro (Paid)
1. **Rate Sync Service**  
   * Daily cron updates standard & reduced rates (ECB JSON feed).  
   * Admin notice if sync fails > 24 h.
2. **Extended Validation APIs**: UK HMRC, Norway, Switzerland, Australia.
3. **Bulk Validator**: tools tab to validate all saved customer VAT numbers; results downloadable as CSV.
4. **Evidence Pack**  
   * Save and display IP address, billing vs. shipping mismatch warnings.  
   * Customer self‑certification modal when mismatch occurs.
5. **OSS / IOSS CSV Generator**  
   * Choose period, currency conversion via ECB rate of last day of quarter.  
   * Supports low‑value consignments ≤ €150 and UK £135 rule.
6. **Dashboard Threshold Widget**: progress bar toward €10 000 cross‑border threshold.
7. **PDF Invoice Integration**: inject VAT lines into WP Overnight PDF plugin if installed.
8. **White‑Label Mode**: hide Lite upgrade notices, switch footer credit.
9. **Support Levels**: priority email (Starter), Slack channel (Business+), agency white‑label (Agency).

## 4. Non‑Functional Requirements
| Area | Requirement |
|------|-------------|
| **Coding Standard** | Pass `phpcs` WordPress ruleset with zero errors; automated in CI. |
| **Security** | Escape all output with `esc_html__`, `esc_attr__`; sanitize input (`sanitize_text_field`, `filter_var`). Nonces on every POST. |
| **Performance** | < 50 ms overhead per checkout request; VIES calls async via AJAX to avoid blocking PHP execution. |
| **Compatibility** | PHP 7.4 – 8.3; WordPress 5.8+; WooCommerce 6.5+; HPOS & legacy order tables. |
| **Accessibility** | WCAG 2.1 AA for all admin UI. |
| **I18n** | Use `.pot` file generated in CI. |

## 5. Pricing & Licensing Tiers
| Tier | Sites | Annual Price* | Features |
|------|-------|---------------|----------|
| Starter | 1 | €59 | All Pro features, standard support |
| Business | 3 | €99 | Priority email |
| Agency | 25 | €199 | Slack + white‑label |
| Lifetime (launch) | 1 | €179 one‑off | Early‑bird, limited 200 licences |
* VAT inclusive; automatic reverse‑charge where applicable.

## 6. Architecture Overview
* **Core Class**: `Vat_Guard_Lite` loaded on `plugins_loaded` (priority 11).  
* **Modules** as autoloaded classes in `src/Module/*` implementing `Module_Interface` with `init()`.
* **Data**: custom table `wp_vat_guard_rates` (rate_id, country, type, rate, created_at).  
  Ships with empty table; Lite populates via importer.
* **Services**:  
  * `Vies_Client` uses WooCommerce HTTP API with 5 s timeout, caches for 7 days.  
  * `Rate_Sync_Service` cron via `wp_schedule_event( 'vat_guard_sync_rates_daily', 'daily', … )`.

## 7. CI / CD & Release
1. **Git branching**: `main` (stable), `develop`, feature branches.
2. **GitHub Actions**: lint (`phpcs`), PHPUnit, build artefact zip.
3. **Tagging**: Semantic Versioning `MAJOR.MINOR.PATCH` (start at 1.0.0).
4. **Deploy Lite**: action commits artefacts to WordPress SVN trunk & tag.
5. **Deploy Pro**: action uploads zip to EDD store and triggers license update feed.

## 8. Documentation
* README.md + readme.txt (WordPress format) in repo root.
* Developer docs: `/docs/` MkDocs site (class diagrams, hooks).
* User docs: public site docs.vat‑guard.com.

## 9. Road‑map & Milestones
| Version | Target Date | Major Items |
|---------|-------------|-------------|
| 1.0.0 | 2025‑09‑30 | Lite launch on wp.org, free features complete |
| 1.1.0 | 2025‑11‑15 | Pro launch: rate sync, OSS CSV, extended APIs |
| 1.2.0 | 2026‑01‑31 | Subscriptions support, IOSS automation |
| 2.0.0 | 2026‑06‑30 | Multi‑currency & SaaS tax engine integration |

---
**End of Requirements**

