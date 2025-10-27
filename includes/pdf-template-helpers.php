<?php
/**
 * PDF Template Helper Functions for EU VAT Guard
 * 
 * These functions can be used in PDF invoice templates to display VAT information
 */

use Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display VAT number in PDF template
 * 
 * @param WC_Order $order The WooCommerce order object
 * @param string $label Custom label (optional)
 */
function eu_vat_guard_pdf_vat_number($order, $label = null)
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return;
    }

    $vat_info = VAT_Guard_PDF_Integration::get_pdf_vat_info($order);
    if (!empty($vat_info['vat_number'])) {
        $display_label = $label ?: $vat_info['vat_label'];
        echo '<div class="pdf-vat-number">';
        echo '<strong>' . esc_html($display_label) . ':</strong> ' . esc_html($vat_info['vat_number']);
        echo '</div>';
    }
}

/**
 * Display VAT exemption status in PDF template
 * 
 * @param WC_Order $order The WooCommerce order object
 * @param string $style 'badge', 'text', or 'table-row'
 */
function eu_vat_guard_pdf_exemption_status($order, $style = 'text')
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return;
    }

    $vat_info = VAT_Guard_PDF_Integration::get_pdf_vat_info($order);
    if (!$vat_info['is_exempt']) {
        return;
    }

    switch ($style) {
        case 'badge':
            echo '<span class="vat-exempt-badge" style="background: #46b450; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.9em;">';
            echo esc_html($vat_info['exemption_message']);
            echo '</span>';
            break;

        case 'table-row':
            echo '<tr class="vat-exempt-row">';
            echo '<th>' . esc_html__('VAT Status', 'eu-vat-guard-for-woocommerce') . '</th>';
            echo '<td style="color: #46b450; font-weight: bold;">' . esc_html($vat_info['exemption_message']) . '</td>';
            echo '</tr>';
            break;

        default: // 'text'
            echo '<div class="vat-exempt-text" style="color: #46b450; font-weight: bold; margin: 5px 0;">';
            echo esc_html($vat_info['exemption_message']);
            echo '</div>';
            break;
    }
}

/**
 * Display complete VAT information block
 * 
 * @param WC_Order $order The WooCommerce order object
 * @param string $style Display style: 'table' or 'div'
 */
function eu_vat_guard_pdf_vat_block($order, $style = 'table')
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return;
    }

    VAT_Guard_PDF_Integration::display_pdf_vat_block($order, $style);
}

/**
 * Get VAT information array for custom implementations
 * 
 * @param WC_Order $order The WooCommerce order object
 * @return array VAT information
 */
function eu_vat_guard_get_pdf_info($order)
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return [];
    }

    return VAT_Guard_PDF_Integration::get_pdf_vat_info($order);
}

/**
 * Check if order has VAT exemption
 * 
 * @param WC_Order $order The WooCommerce order object
 * @return bool
 */
function eu_vat_guard_is_exempt($order)
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return false;
    }

    $vat_info = VAT_Guard_PDF_Integration::get_pdf_vat_info($order);
    return $vat_info['is_exempt'];
}

/**
 * Get order VAT number
 * 
 * @param WC_Order $order The WooCommerce order object
 * @return string
 */
function eu_vat_guard_get_vat_number($order)
{
    if (!class_exists('Stormlabs\EUVATGuard\VAT_Guard_PDF_Integration')) {
        return '';
    }

    $vat_info = VAT_Guard_PDF_Integration::get_pdf_vat_info($order);
    return $vat_info['vat_number'];
}