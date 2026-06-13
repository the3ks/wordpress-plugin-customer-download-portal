<?php
if (!defined('ABSPATH')) exit;

function cdp_table($name) {
    global $wpdb;
    return $wpdb->prefix . 'cdp_' . $name;
}

function cdp_generate_token() {
    try { return bin2hex(random_bytes(32)); }
    catch (Exception $e) { return wp_generate_password(64, false, false); }
}

function cdp_status_for_dates($start, $end) {
    $today = current_time('Y-m-d');
    if ($start && $today < $start) return 'Upcoming';
    if ($end && $today > $end) return 'Expired';
    return 'Active';
}

function cdp_download_url($token) {
    return add_query_arg(['cdp_download' => '1', 'token' => rawurlencode($token)], home_url('/'));
}

function cdp_safe_file_upload($file, $subdir = '') {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    wp_mkdir_p(CDP_DOWNLOAD_DIR);
    $base = trailingslashit(CDP_DOWNLOAD_DIR) . trim($subdir, '/');
    wp_mkdir_p($base);
    $uploaded = wp_handle_upload($file, ['test_form' => false]);
    if (!empty($uploaded['error'])) return new WP_Error('upload_error', $uploaded['error']);
    $source = $uploaded['file'];
    $safe_name = wp_unique_filename($base, sanitize_file_name(basename($source)));
    $dest = trailingslashit($base) . $safe_name;
    if (!@rename($source, $dest)) return new WP_Error('move_error', 'Could not move uploaded file to protected directory.');
    return ['path' => $dest, 'name' => $safe_name, 'size' => filesize($dest)];
}

function cdp_get_assigned_product_ids($user_id) {
    global $wpdb;
    $rows = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT product_id FROM " . cdp_table('assignments') . " WHERE user_id = %d", $user_id));
    return array_map('intval', $rows ?: []);
}
