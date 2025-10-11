<?php
/**
 * Rename translation files from old text domain to new text domain
 * 
 * This script renames all translation files from:
 * vat-guard-woocommerce-* to eu-vat-guard-for-woocommerce-*
 * 
 * Usage: php rename-translation-files.php
 * 
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 */

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

$languages_dir = __DIR__;
$old_prefix = 'vat-guard-woocommerce';
$new_prefix = 'eu-vat-guard-for-woocommerce';

// Find all files with the old prefix
$old_files = glob($languages_dir . '/' . $old_prefix . '*');

if (empty($old_files)) {
    echo "No files found with prefix '{$old_prefix}'\n";
    exit(0);
}

echo "Renaming translation files...\n";
echo "Old prefix: {$old_prefix}\n";
echo "New prefix: {$new_prefix}\n\n";

$renamed_count = 0;
$failed_count = 0;

foreach ($old_files as $old_file) {
    $filename = basename($old_file);
    $new_filename = str_replace($old_prefix, $new_prefix, $filename);
    $new_file = $languages_dir . '/' . $new_filename;
    
    echo "Renaming: {$filename} → {$new_filename}... ";
    
    if (rename($old_file, $new_file)) {
        echo "✓ Success\n";
        $renamed_count++;
    } else {
        echo "✗ Failed\n";
        $failed_count++;
    }
}

echo "\nRename complete!\n";
echo "Successfully renamed: {$renamed_count} files\n";
echo "Failed to rename: {$failed_count} files\n";

if ($renamed_count > 0) {
    echo "\nTranslation files are now using the correct text domain: {$new_prefix}\n";
    echo "WordPress should now be able to load translations properly.\n";
}