<?php
/**
 * Compile PO files to MO files for VAT Guard for WooCommerce
 * 
 * This script compiles all .po files in the languages directory to .mo files
 * Run this script after updating translations to generate the binary files
 * WordPress needs for displaying translations.
 * 
 * Usage: php compile-translations.php
 * 
 * Note: This is a CLI-only script. Output escaping is not required since
 * it cannot be executed in a web context where XSS attacks are possible.
 * 
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

$languages_dir = __DIR__;
$po_files = glob($languages_dir . '/*.po');

if (empty($po_files)) {
    echo "No .po files found in {$languages_dir}\n";
    exit(1);
}

echo "Compiling translation files...\n";

foreach ($po_files as $po_file) {
    $mo_file = str_replace('.po', '.mo', $po_file);
    $basename = basename($po_file);

    echo "Compiling {$basename}... ";

    if (compile_po_to_mo($po_file, $mo_file)) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed\n";
    }
}

echo "\nCompilation complete!\n";

/**
 * Compile a PO file to MO format
 * 
 * @param string $po_file Path to the .po file
 * @param string $mo_file Path to the output .mo file
 * @return bool Success status
 */
function compile_po_to_mo($po_file, $mo_file)
{
    if (!file_exists($po_file)) {
        return false;
    }

    $po_content = file_get_contents($po_file);
    if ($po_content === false) {
        return false;
    }

    // Parse PO file
    $translations = parse_po_file($po_content);

    if (empty($translations)) {
        return false;
    }

    // Generate MO file content
    $mo_content = generate_mo_file($translations);

    if ($mo_content === false) {
        return false;
    }

    // Write MO file
    return file_put_contents($mo_file, $mo_content) !== false;
}

/**
 * Parse PO file content
 * 
 * @param string $content PO file content
 * @return array Parsed translations
 */
function parse_po_file($content)
{
    $translations = [];
    $lines = explode("\n", $content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;

    foreach ($lines as $line) {
        $line = trim($line);

        if (empty($line) || $line[0] === '#') {
            continue;
        }

        if (strpos($line, 'msgid ') === 0) {
            // Save previous translation if exists
            if (!empty($current_msgid) && !empty($current_msgstr)) {
                $translations[$current_msgid] = $current_msgstr;
            }

            $current_msgid = substr($line, 7, -1); // Remove 'msgid "' and '"'
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
        } elseif (strpos($line, 'msgstr ') === 0) {
            $current_msgstr = substr($line, 8, -1); // Remove 'msgstr "' and '"'
            $in_msgid = false;
            $in_msgstr = true;
        } elseif ($line[0] === '"' && $line[-1] === '"') {
            $content = substr($line, 1, -1);
            if ($in_msgid) {
                $current_msgid .= $content;
            } elseif ($in_msgstr) {
                $current_msgstr .= $content;
            }
        }
    }

    // Save last translation
    if (!empty($current_msgid) && !empty($current_msgstr)) {
        $translations[$current_msgid] = $current_msgstr;
    }

    return $translations;
}

/**
 * Generate MO file binary content
 * 
 * @param array $translations Array of msgid => msgstr pairs
 * @return string|false MO file binary content or false on failure
 */
function generate_mo_file($translations)
{
    if (empty($translations)) {
        return false;
    }

    $keys = array_keys($translations);
    $values = array_values($translations);

    // MO file header
    $magic = 0x950412de;
    $revision = 0;
    $count = count($translations);

    // Calculate offsets
    $key_start = 28;
    $value_start = $key_start + 8 * $count;
    $key_offsets = [];
    $value_offsets = [];
    $key_table = '';
    $value_table = '';

    $current_offset = $value_start + 8 * $count;

    // Build key table and calculate offsets
    foreach ($keys as $key) {
        $key_offsets[] = [strlen($key), $current_offset];
        $key_table .= $key . "\0";
        $current_offset += strlen($key) + 1;
    }

    // Build value table and calculate offsets
    foreach ($values as $value) {
        $value_offsets[] = [strlen($value), $current_offset];
        $value_table .= $value . "\0";
        $current_offset += strlen($value) + 1;
    }

    // Build MO file
    $mo = pack('V', $magic);
    $mo .= pack('V', $revision);
    $mo .= pack('V', $count);
    $mo .= pack('V', $key_start);
    $mo .= pack('V', $value_start);
    $mo .= pack('V', 0); // Hash table offset (not used)
    $mo .= pack('V', 0); // Hash table size (not used)

    // Key offsets
    foreach ($key_offsets as $offset) {
        $mo .= pack('V', $offset[0]);
        $mo .= pack('V', $offset[1]);
    }

    // Value offsets
    foreach ($value_offsets as $offset) {
        $mo .= pack('V', $offset[0]);
        $mo .= pack('V', $offset[1]);
    }

    // Key and value tables
    $mo .= $key_table . $value_table;

    return $mo;
}