<?php
/**
 * VAT Rate Importer Class
 *
 * @package EU_VAT_Guard_For_WooCommerce
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class VAT_Guard_Rate_Importer {
    
    /**
     * Current EU VAT rates data
     * Updated as of 2024
     */
    private static $eu_vat_rates = array(
        'AT' => array( // Austria
            'country_name' => 'Austria',
            'standard' => 20,
            'reduced' => array(10, 13),
            'special' => array(
                'food' => 10,
                'books' => 10,
                'pharmaceuticals' => 10,
                'hotels' => 13
            )
        ),
        'BE' => array( // Belgium
            'country_name' => 'Belgium',
            'standard' => 21,
            'reduced' => array(6, 12),
            'special' => array(
                'food' => 6,
                'books' => 6,
                'pharmaceuticals' => 6,
                'hotels' => 12
            )
        ),
        'BG' => array( // Bulgaria
            'country_name' => 'Bulgaria',
            'standard' => 20,
            'reduced' => array(9),
            'special' => array(
                'food' => 9,
                'books' => 9,
                'pharmaceuticals' => 9
            )
        ),
        'HR' => array( // Croatia
            'country_name' => 'Croatia',
            'standard' => 25,
            'reduced' => array(5, 13),
            'special' => array(
                'food' => 5,
                'books' => 5,
                'pharmaceuticals' => 5,
                'hotels' => 13
            )
        ),
        'CY' => array( // Cyprus
            'country_name' => 'Cyprus',
            'standard' => 19,
            'reduced' => array(5, 9),
            'special' => array(
                'food' => 5,
                'books' => 5,
                'pharmaceuticals' => 5,
                'hotels' => 9
            )
        ),
        'CZ' => array( // Czech Republic
            'country_name' => 'Czech Republic',
            'standard' => 21,
            'reduced' => array(10, 15),
            'special' => array(
                'food' => 15,
                'books' => 10,
                'pharmaceuticals' => 10
            )
        ),
        'DK' => array( // Denmark
            'country_name' => 'Denmark',
            'standard' => 25,
            'reduced' => array(),
            'special' => array()
        ),
        'EE' => array( // Estonia
            'country_name' => 'Estonia',
            'standard' => 20,
            'reduced' => array(9),
            'special' => array(
                'food' => 9,
                'books' => 9,
                'pharmaceuticals' => 9
            )
        ),
        'FI' => array( // Finland
            'country_name' => 'Finland',
            'standard' => 24,
            'reduced' => array(10, 14),
            'special' => array(
                'food' => 14,
                'books' => 10,
                'pharmaceuticals' => 10
            )
        ),
        'FR' => array( // France
            'country_name' => 'France',
            'standard' => 20,
            'reduced' => array(5.5, 10),
            'special' => array(
                'food' => 5.5,
                'books' => 5.5,
                'pharmaceuticals' => 2.1,
                'hotels' => 10
            )
        ),
        'DE' => array( // Germany
            'country_name' => 'Germany',
            'standard' => 19,
            'reduced' => array(7),
            'special' => array(
                'food' => 7,
                'books' => 7,
                'pharmaceuticals' => 7
            )
        ),
        'GR' => array( // Greece
            'country_name' => 'Greece',
            'standard' => 24,
            'reduced' => array(6, 13),
            'special' => array(
                'food' => 13,
                'books' => 6,
                'pharmaceuticals' => 6
            )
        ),
        'HU' => array( // Hungary
            'country_name' => 'Hungary',
            'standard' => 27,
            'reduced' => array(5, 18),
            'special' => array(
                'food' => 5,
                'books' => 5,
                'pharmaceuticals' => 5,
                'hotels' => 18
            )
        ),
        'IE' => array( // Ireland
            'country_name' => 'Ireland',
            'standard' => 23,
            'reduced' => array(9, 13.5),
            'special' => array(
                'food' => 0,
                'books' => 0,
                'pharmaceuticals' => 0,
                'hotels' => 13.5
            )
        ),
        'IT' => array( // Italy
            'country_name' => 'Italy',
            'standard' => 22,
            'reduced' => array(4, 5, 10),
            'special' => array(
                'food' => 4,
                'books' => 4,
                'pharmaceuticals' => 10
            )
        ),
        'LV' => array( // Latvia
            'country_name' => 'Latvia',
            'standard' => 21,
            'reduced' => array(5, 12),
            'special' => array(
                'food' => 12,
                'books' => 5,
                'pharmaceuticals' => 12
            )
        ),
        'LT' => array( // Lithuania
            'country_name' => 'Lithuania',
            'standard' => 21,
            'reduced' => array(5, 9),
            'special' => array(
                'food' => 9,
                'books' => 9,
                'pharmaceuticals' => 9
            )
        ),
        'LU' => array( // Luxembourg
            'country_name' => 'Luxembourg',
            'standard' => 17,
            'reduced' => array(3, 8, 14),
            'special' => array(
                'food' => 3,
                'books' => 3,
                'pharmaceuticals' => 3,
                'hotels' => 14
            )
        ),
        'MT' => array( // Malta
            'country_name' => 'Malta',
            'standard' => 18,
            'reduced' => array(5, 7),
            'special' => array(
                'food' => 5,
                'books' => 5,
                'pharmaceuticals' => 5,
                'hotels' => 7
            )
        ),
        'NL' => array( // Netherlands
            'country_name' => 'Netherlands',
            'standard' => 21,
            'reduced' => array(9),
            'special' => array(
                'food' => 9,
                'books' => 9,
                'pharmaceuticals' => 9
            )
        ),
        'PL' => array( // Poland
            'country_name' => 'Poland',
            'standard' => 23,
            'reduced' => array(5, 8),
            'special' => array(
                'food' => 5,
                'books' => 5,
                'pharmaceuticals' => 8
            )
        ),
        'PT' => array( // Portugal
            'country_name' => 'Portugal',
            'standard' => 23,
            'reduced' => array(6, 13),
            'special' => array(
                'food' => 6,
                'books' => 6,
                'pharmaceuticals' => 6,
                'hotels' => 13
            )
        ),
        'RO' => array( // Romania
            'country_name' => 'Romania',
            'standard' => 19,
            'reduced' => array(5, 9),
            'special' => array(
                'food' => 9,
                'books' => 5,
                'pharmaceuticals' => 9
            )
        ),
        'SK' => array( // Slovakia
            'country_name' => 'Slovakia',
            'standard' => 20,
            'reduced' => array(10),
            'special' => array(
                'food' => 10,
                'books' => 10,
                'pharmaceuticals' => 10
            )
        ),
        'SI' => array( // Slovenia
            'country_name' => 'Slovenia',
            'standard' => 22,
            'reduced' => array(5, 9.5),
            'special' => array(
                'food' => 9.5,
                'books' => 9.5,
                'pharmaceuticals' => 9.5
            )
        ),
        'ES' => array( // Spain
            'country_name' => 'Spain',
            'standard' => 21,
            'reduced' => array(4, 10),
            'special' => array(
                'food' => 4,
                'books' => 4,
                'pharmaceuticals' => 4,
                'hotels' => 10
            )
        ),
        'SE' => array( // Sweden
            'country_name' => 'Sweden',
            'standard' => 25,
            'reduced' => array(6, 12),
            'special' => array(
                'food' => 12,
                'books' => 6,
                'pharmaceuticals' => 6
            )
        )
    );

    /**
     * Initialize the importer
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'handle_import_request'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'eu-vat-guard',
            esc_html__('VAT Rate Importer', 'eu-vat-guard-for-woocommerce'),
            esc_html__('VAT Rate Importer', 'eu-vat-guard-for-woocommerce'),
            'manage_woocommerce',
            'eu-vat-guard-rate-importer',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if ('eu-vat-guard_page_eu-vat-guard-rate-importer' !== $hook) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_style(
            'vat-importer-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/admin-vat-importer.css',
            array(),
            '1.2.0'
        );
    }

    /**
     * Handle import request
     */
    public function handle_import_request() {
        if (!isset($_POST['eu_vat_guard_import_rates']) || !wp_verify_nonce(wp_unslash($_POST['eu_vat_guard_import_nonce']), 'eu_vat_guard_import_rates')) {
            return;
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'eu-vat-guard-for-woocommerce'));
        }

        $selected_countries = isset($_POST['selected_countries']) ? array_map('sanitize_text_field', wp_unslash($_POST['selected_countries'])) : array();
        $include_special = isset($_POST['include_special_rates']) ? sanitize_text_field(wp_unslash($_POST['include_special_rates'])) : 'no';
        
        if (empty($selected_countries)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . esc_html__('Please select at least one country to import.', 'eu-vat-guard-for-woocommerce') . '</p></div>';
            });
            return;
        }

        $imported_count = $this->import_vat_rates($selected_countries, $include_special === 'yes');
        
        add_action('admin_notices', function() use ($imported_count) {
            echo '<div class="notice notice-success"><p>' . 
                 sprintf(
                     esc_html__('Successfully imported %d VAT rates.', 'eu-vat-guard-for-woocommerce'),
                     $imported_count
                 ) . 
                 '</p></div>';
        });
    }

    /**
     * Import VAT rates for selected countries
     */
    private function import_vat_rates($selected_countries, $include_special = false) {
        $imported_count = 0;
        
        foreach ($selected_countries as $country_code) {
            if (!isset(self::$eu_vat_rates[$country_code])) {
                continue;
            }
            
            $country_data = self::$eu_vat_rates[$country_code];
            
            // Import standard rate
            $this->create_or_update_tax_rate($country_code, 'standard', $country_data['standard'], $country_data['country_name']);
            $imported_count++;
            
            // Import reduced rates
            foreach ($country_data['reduced'] as $rate) {
                $this->create_or_update_tax_rate($country_code, 'reduced', $rate, $country_data['country_name']);
                $imported_count++;
            }
            
            // Import special rates if requested
            if ($include_special && !empty($country_data['special'])) {
                foreach ($country_data['special'] as $category => $rate) {
                    $this->create_or_update_tax_rate($country_code, $category, $rate, $country_data['country_name']);
                    $imported_count++;
                }
            }
        }
        
        return $imported_count;
    }

    /**
     * Create or update tax rate in WooCommerce
     */
    private function create_or_update_tax_rate($country_code, $type, $rate, $country_name) {
        global $wpdb;
        
        $tax_rate_name = sprintf('%s %s (%s%%)', $country_name, ucfirst($type), $rate);
        
        // Check if rate already exists
        $existing_rate = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates 
             WHERE tax_rate_country = %s 
             AND tax_rate_name = %s",
            $country_code,
            $tax_rate_name
        ));
        
        if ($existing_rate) {
            // Update existing rate
            $wpdb->update(
                $wpdb->prefix . 'woocommerce_tax_rates',
                array(
                    'tax_rate' => $rate,
                    'tax_rate_priority' => $type === 'standard' ? 1 : 2,
                    'tax_rate_compound' => 0,
                    'tax_rate_shipping' => $type === 'standard' ? 1 : 0,
                ),
                array('tax_rate_id' => $existing_rate->tax_rate_id),
                array('%f', '%d', '%d', '%d'),
                array('%d')
            );
        } else {
            // Create new rate
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_tax_rates',
                array(
                    'tax_rate_country' => $country_code,
                    'tax_rate_state' => '',
                    'tax_rate' => $rate,
                    'tax_rate_name' => $tax_rate_name,
                    'tax_rate_priority' => $type === 'standard' ? 1 : 2,
                    'tax_rate_compound' => 0,
                    'tax_rate_shipping' => $type === 'standard' ? 1 : 0,
                    'tax_rate_order' => 0,
                    'tax_rate_class' => $type === 'standard' ? '' : sanitize_title($type)
                ),
                array('%s', '%s', '%f', '%s', '%d', '%d', '%d', '%d', '%s')
            );
        }
        
        // Clear WooCommerce tax cache
        WC_Cache_Helper::get_transient_version('taxes', true);
    }

    /**
     * Get EU countries that WooCommerce sells to
     */
    private function get_wc_eu_countries() {
        $wc_countries = WC()->countries->get_allowed_countries();
        $eu_countries = array();
        
        foreach (self::$eu_vat_rates as $code => $data) {
            if (isset($wc_countries[$code])) {
                $eu_countries[$code] = $data['country_name'];
            }
        }
        
        return $eu_countries;
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $eu_countries = $this->get_wc_eu_countries();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('EU VAT Rate Importer', 'eu-vat-guard-for-woocommerce'); ?></h1>
            
            <div class="vat-importer-notice">
                <p><?php esc_html_e('Import current EU VAT rates into your WooCommerce tax settings. This tool will create or update tax rates for the selected countries.', 'eu-vat-guard-for-woocommerce'); ?></p>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('eu_vat_guard_import_rates', 'eu_vat_guard_import_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Select Countries', 'eu-vat-guard-for-woocommerce'); ?></label>
                        </th>
                        <td>
                            <div class="vat-importer-countries">
                                <p>
                                    <label>
                                        <input type="checkbox" id="select-all-countries"> 
                                        <strong><?php esc_html_e('Select All', 'eu-vat-guard-for-woocommerce'); ?></strong>
                                    </label>
                                </p>
                                <hr>
                                <?php foreach ($eu_countries as $code => $name): ?>
                                    <p>
                                        <label>
                                            <input type="checkbox" name="selected_countries[]" value="<?php echo esc_attr($code); ?>" class="country-checkbox">
                                            <?php echo esc_html($name); ?> (<?php echo esc_html($code); ?>)
                                            <span class="description">
                                                - <?php echo esc_html(self::$eu_vat_rates[$code]['standard']); ?>% 
                                                <?php esc_html_e('standard', 'eu-vat-guard-for-woocommerce'); ?>
                                                <?php if (!empty(self::$eu_vat_rates[$code]['reduced'])): ?>
                                                    , <?php echo esc_html(implode('%, ', self::$eu_vat_rates[$code]['reduced'])); ?>% 
                                                    <?php esc_html_e('reduced', 'eu-vat-guard-for-woocommerce'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Only countries that are enabled in your WooCommerce selling locations are shown.', 'eu-vat-guard-for-woocommerce'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="include_special_rates"><?php esc_html_e('Include Special Rates', 'eu-vat-guard-for-woocommerce'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="include_special_rates" value="yes" id="include_special_rates">
                                <?php esc_html_e('Import special VAT rates (food, books, pharmaceuticals, etc.)', 'eu-vat-guard-for-woocommerce'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Special rates will be imported as separate tax classes that you can assign to specific products.', 'eu-vat-guard-for-woocommerce'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="eu_vat_guard_import_rates" class="button-primary vat-importer-submit" 
                           value="<?php esc_attr_e('Import Selected VAT Rates', 'eu-vat-guard-for-woocommerce'); ?>">
                </p>
            </form>
            
            <div class="postbox vat-rates-overview">
                <h3 class="hndle"><?php esc_html_e('Current EU VAT Rates Overview', 'eu-vat-guard-for-woocommerce'); ?></h3>
                <div class="inside">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Country', 'eu-vat-guard-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Standard Rate', 'eu-vat-guard-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Reduced Rates', 'eu-vat-guard-for-woocommerce'); ?></th>
                                <th><?php esc_html_e('Special Rates', 'eu-vat-guard-for-woocommerce'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (self::$eu_vat_rates as $code => $data): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($data['country_name']); ?></strong> (<?php echo esc_html($code); ?>)</td>
                                    <td><?php echo esc_html($data['standard']); ?>%</td>
                                    <td>
                                        <?php if (!empty($data['reduced'])): ?>
                                            <?php echo esc_html(implode('%, ', $data['reduced'])); ?>%
                                        <?php else: ?>
                                            <em><?php esc_html_e('None', 'eu-vat-guard-for-woocommerce'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($data['special'])): ?>
                                            <?php foreach ($data['special'] as $category => $rate): ?>
                                                <span class="description"><?php echo esc_html(ucfirst($category)); ?>: <?php echo esc_html($rate); ?>%</span><br>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <em><?php esc_html_e('None', 'eu-vat-guard-for-woocommerce'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#select-all-countries').change(function() {
                $('.country-checkbox').prop('checked', this.checked);
            });
            
            $('.country-checkbox').change(function() {
                if (!this.checked) {
                    $('#select-all-countries').prop('checked', false);
                } else if ($('.country-checkbox:checked').length === $('.country-checkbox').length) {
                    $('#select-all-countries').prop('checked', true);
                }
            });
        });
        </script>
        <?php
    }
}

// Initialize the importer
if (is_admin()) {
    new VAT_Guard_Rate_Importer();
}