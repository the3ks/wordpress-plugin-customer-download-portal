<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (get_option('cdp_delete_data_on_uninstall', 'no') !== 'yes') return;

global $wpdb;
foreach (['download_logs','assignments','licenses','product_assets','product_versions','products'] as $name) {
    $table = $wpdb->prefix . 'cdp_' . $name;
    $wpdb->query("DROP TABLE IF EXISTS `$table`");
}

$download_dir = WP_CONTENT_DIR . '/customer-download-files';
if (is_dir($download_dir)) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($download_dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $fileinfo) {
        $fileinfo->isDir() ? @rmdir($fileinfo->getRealPath()) : @unlink($fileinfo->getRealPath());
    }
    @rmdir($download_dir);
}
foreach (['cdp_version','cdp_delete_data_on_uninstall','cdp_support_email','cdp_support_phone','cdp_support_url'] as $opt) delete_option($opt);
