<?php
// VAT Guard for WooCommerce VIES validation helper
if (!defined('ABSPATH')) {
    exit;
}

class EU_VAT_Guard_VIES
{
    /**
     * Check VAT number validity using the VIES SOAP API
     * @param string $country Two-letter country code
     * @param string $vat VAT number (without country code)
     * @return bool|null true=valid, false=invalid, null=error/unavailable
     */
    public static function check_vat($country, $vat)
    {
        $wsdl = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
        try {
            $client = @new SoapClient($wsdl, ['exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE]);
            $result = $client->checkVat([
                'countryCode' => $country,
                'vatNumber' => $vat
            ]);
            return $result->valid ? true : false;
        } catch (Exception $e) {
            if (ini_get('display_errors')) {
                // Show the actual error if display_errors is enabled
                echo '<div style="color:red;font-size:small;">VIES error: ' . esc_html($e->getMessage()) . '</div>';
            }
            return null; // VIES unavailable or error
        }
    }
}
