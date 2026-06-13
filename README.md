# Customer Download Portal

Custom WordPress plugin for a customer portal where administrators grant access to digital products, downloads, licenses, release notes, documentation, and support links.

This plugin is intentionally **not an e-commerce plugin**. It has no cart, checkout, payment gateway, or public purchase flow. Customers log in and only see the products and license records assigned to their WordPress user account.

## Current Version

`2.0.0`

## Customer Portal Shortcode

Create a WordPress page such as **Customer Portal** or **My Downloads** and add:

```text
[customer_downloads]
```

The shortcode renders these tabs:

```text
Downloads
Licenses
Release Notes
Documentation
Support Tickets
```

## Feature Summary

### Downloads

- Admin creates products.
- Admin adds product versions.
- Each product version can have its own downloadable file.
- Admin assigns a product to a WordPress user.
- Assignment can target a specific version or default to the latest active version.
- Customer receives a unique secure token download URL.
- Download link validates the logged-in user, token, expiry, and download limit before streaming the file.
- Downloads are logged.

### Licenses

The Licenses tab is informational only. It shows license/subscription records granted to the logged-in user.

License fields:

- Product
- Subscription plan
- Quantity
- Subscription start date
- Subscription end date
- Status: Active, Upcoming, or Expired
- Internal notes

The plugin does **not** perform license activation, software activation, license key validation, or device binding.

### Release Notes

- Release notes are stored at the product version level.
- Customers only see release notes for products assigned to them.
- Release notes are listed by product and version.

### Documentation

Product assets can be added as documentation, video, or knowledge base links.

Supported asset fields:

- Product
- Optional version ID
- Asset type
- Title
- Description
- Protected file upload
- External URL
- Sort order

In the current implementation, external URL assets are directly visible to customers. Uploaded protected documentation files are stored but not exposed through a separate secure asset-download handler yet.

### Support Tickets

The Support Tickets tab is a simple support information page.

Admin can configure:

- Support email
- Support phone
- Support portal URL

The plugin does not include a built-in ticketing engine in v2. Recommended integrations are Zendesk, Freshdesk, Jira Service Management, Microsoft Forms, Google Forms, or a dedicated support portal.

## Admin Menu

After activation, WordPress admin shows:

```text
Customer Downloads
├── Products
├── Assignments
├── Licenses
└── Settings
```

### Products

Used to manage:

- Product catalog records
- Product versions/downloads
- Product documentation/assets
- Release notes

### Assignments

Used to grant product access to users.

Assignment fields:

- WordPress user ID
- Product
- Optional version ID
- Max downloads, where `0 = unlimited`
- Optional expiration date/time

### Licenses

Used to create license/subscription records for users.

### Settings

Used to configure:

- Support email
- Support phone
- Support portal URL
- Whether plugin data should be removed on uninstall

## Architecture

### Core Entities

```text
Product
├── Product Versions
│   ├── Download file
│   ├── Version label
│   ├── Release date
│   ├── Release notes
│   └── Latest flag
├── Product Assets
│   ├── Documentation
│   ├── Video
│   └── Knowledge Base
├── Assignments
└── Licenses
```

### Database Tables

The plugin creates these tables using the active WordPress table prefix:

```text
wp_cdp_products
wp_cdp_product_versions
wp_cdp_product_assets
wp_cdp_assignments
wp_cdp_download_logs
wp_cdp_licenses
```

Replace `wp_` with the actual WordPress database prefix.

### Table Purposes

#### `cdp_products`

Stores product catalog records.

Important columns:

- `id`
- `title`
- `slug`
- `description`
- `current_version_id`
- `active`

#### `cdp_product_versions`

Stores product version records and their downloadable files.

Important columns:

- `product_id`
- `version_label`
- `release_date`
- `file_path`
- `file_name`
- `file_size`
- `release_notes`
- `is_latest`
- `active`

#### `cdp_product_assets`

Stores documentation, video, and knowledge base resources.

Important columns:

- `product_id`
- `version_id`
- `asset_type`
- `title`
- `description`
- `file_path`
- `file_name`
- `external_url`
- `sort_order`

#### `cdp_assignments`

Stores access grants from admin to customer.

Important columns:

- `user_id`
- `product_id`
- `version_id`
- `token`
- `expires_at`
- `max_downloads`
- `download_count`

#### `cdp_download_logs`

Stores download history.

Important columns:

- `assignment_id`
- `user_id`
- `product_id`
- `version_id`
- `ip_address`
- `user_agent`
- `downloaded_at`

#### `cdp_licenses`

Stores informational subscription/license records.

Important columns:

- `user_id`
- `product_id`
- `subscription_plan`
- `quantity`
- `start_date`
- `end_date`
- `notes`

## File Storage

Protected files are stored in:

```text
wp-content/customer-download-files/
```

On activation, the plugin creates:

```text
wp-content/customer-download-files/.htaccess
wp-content/customer-download-files/index.html
```

`.htaccess` blocks direct access on Apache/LiteSpeed environments. For Nginx, configure an equivalent rule if the directory is web-accessible.

Recommended Nginx rule:

```nginx
location ^~ /wp-content/customer-download-files/ {
    deny all;
    return 403;
}
```

For maximum security, store files outside the public web root and update the plugin constant/path logic accordingly.

## Security Guidelines

- Customers must be logged in.
- Download token must belong to the logged-in user.
- Direct file URLs should never be exposed.
- Files are streamed through PHP after validation.
- Download count and expiration are enforced before streaming.
- Download activity is logged.
- Admin-only screens require `manage_options`.
- Nonces are used for admin form submissions.

## Install / Upgrade Behavior

### Activation

- Creates or updates database tables.
- Creates protected download directory.
- Keeps existing data if tables already exist.

### Deactivation

- Does not delete database tables.
- Does not delete uploaded files.
- This is intentional and safe for troubleshooting.

### Deleting the Plugin

Default behavior:

- Keeps database tables.
- Keeps uploaded files.

Optional behavior:

- If admin enables **Remove all plugin database tables and protected files when plugin is deleted**, `uninstall.php` will drop all plugin tables and remove `wp-content/customer-download-files/`.

## Recommended Workflow

1. Install and activate plugin.
2. Create a WordPress page named **Customer Portal**.
3. Add shortcode: `[customer_downloads]`.
4. Add the page to your website menu.
5. Go to **Customer Downloads → Products**.
6. Add a Product.
7. Add a Product Version and upload the download file.
8. Add documentation/assets if needed.
9. Go to **Customer Downloads → Assignments**.
10. Assign the product to a user.
11. Go to **Customer Downloads → Licenses**.
12. Add subscription/license records for that user.
13. Customer logs in and opens the portal page.

## Known Limitations in v2.0.0

- Admin screens are intentionally simple and use WordPress user IDs instead of searchable user selectors.
- Documentation file uploads are stored but not yet served through a separate secure asset-download handler.
- No built-in ticketing engine.
- No bulk assignment.
- No CSV import/export.
- No email notification when a product is assigned.
- No frontend design customization settings beyond basic embedded CSS.

## Suggested Future Enhancements

- Searchable user dropdown in admin screens.
- Bulk product assignment.
- Email notification template when assigning a product.
- Secure documentation asset download handler.
- CSV import/export for licenses and assignments.
- Product-level customer view with grouped downloads, release notes, and docs.
- Role/capability customization.
- License key generation if software activation is needed later.
- REST API endpoints for integration with external CRM/ERP.

## Development Notes

This plugin follows a small, classic WordPress plugin structure:

```text
customer-download-portal/
├── customer-download-portal.php
├── uninstall.php
├── README.md
├── readme.txt
├── includes/
│   ├── helpers.php
│   ├── install.php
│   ├── admin-products.php
│   ├── admin-assignments.php
│   ├── admin-licenses.php
│   ├── admin-settings.php
│   ├── shortcode-portal.php
│   └── download-handler.php
└── assets/
```

Code style is intentionally simple procedural PHP to keep the plugin easy to review, modify, and maintain.
