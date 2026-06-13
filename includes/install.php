<?php
if (!defined('ABSPATH')) exit;

function cdp_activate_plugin() {
    cdp_create_or_update_tables();
    cdp_protect_download_dir();
    update_option('cdp_version', CDP_VERSION);
}

function cdp_deactivate_plugin() {
    // Keep customer data and downloadable files. Do not delete anything on deactivation.
}

function cdp_maybe_upgrade_plugin() {
    if (get_option('cdp_version') !== CDP_VERSION) {
        cdp_create_or_update_tables();
        cdp_protect_download_dir();
        update_option('cdp_version', CDP_VERSION);
    }
}

function cdp_protect_download_dir() {
    wp_mkdir_p(CDP_DOWNLOAD_DIR);
    $htaccess = trailingslashit(CDP_DOWNLOAD_DIR) . '.htaccess';
    if (!file_exists($htaccess)) file_put_contents($htaccess, "Deny from all\n");
    $index = trailingslashit(CDP_DOWNLOAD_DIR) . 'index.html';
    if (!file_exists($index)) file_put_contents($index, '');
}

function cdp_create_or_update_tables() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset = $wpdb->get_charset_collate();

    dbDelta("CREATE TABLE " . cdp_table('products') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NULL,
        description TEXT NULL,
        current_version_id BIGINT UNSIGNED NULL,
        active TINYINT(1) DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id), KEY active (active), KEY slug (slug)
    ) $charset;");

    dbDelta("CREATE TABLE " . cdp_table('product_versions') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT UNSIGNED NOT NULL,
        version_label VARCHAR(100) NOT NULL,
        release_date DATE NULL,
        file_path TEXT NULL,
        file_name VARCHAR(255) NULL,
        file_size BIGINT UNSIGNED DEFAULT 0,
        release_notes LONGTEXT NULL,
        is_latest TINYINT(1) DEFAULT 0,
        active TINYINT(1) DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id), KEY product_id (product_id), KEY is_latest (is_latest), KEY active (active)
    ) $charset;");

    dbDelta("CREATE TABLE " . cdp_table('product_assets') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT UNSIGNED NOT NULL,
        version_id BIGINT UNSIGNED NULL,
        asset_type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        file_path TEXT NULL,
        file_name VARCHAR(255) NULL,
        file_size BIGINT UNSIGNED DEFAULT 0,
        external_url TEXT NULL,
        sort_order INT DEFAULT 0,
        active TINYINT(1) DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id), KEY product_id (product_id), KEY version_id (version_id), KEY asset_type (asset_type), KEY active (active)
    ) $charset;");

    dbDelta("CREATE TABLE " . cdp_table('assignments') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        version_id BIGINT UNSIGNED NULL,
        token VARCHAR(128) NOT NULL,
        expires_at DATETIME NULL,
        max_downloads INT UNSIGNED DEFAULT 0,
        download_count INT UNSIGNED DEFAULT 0,
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id), UNIQUE KEY token (token), KEY user_id (user_id), KEY product_id (product_id), KEY version_id (version_id)
    ) $charset;");

    dbDelta("CREATE TABLE " . cdp_table('download_logs') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        assignment_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        version_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(100) NULL,
        user_agent TEXT NULL,
        downloaded_at DATETIME NOT NULL,
        PRIMARY KEY (id), KEY assignment_id (assignment_id), KEY user_id (user_id), KEY product_id (product_id), KEY version_id (version_id)
    ) $charset;");

    dbDelta("CREATE TABLE " . cdp_table('licenses') . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        subscription_plan VARCHAR(255) NOT NULL,
        quantity INT UNSIGNED DEFAULT 1,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        notes TEXT NULL,
        created_by BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id), KEY user_id (user_id), KEY product_id (product_id), KEY end_date (end_date)
    ) $charset;");
}
