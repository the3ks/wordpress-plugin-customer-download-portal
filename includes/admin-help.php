<?php
if (!defined('ABSPATH')) exit;

function cdp_render_help_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap cdp-help-page">
        <h1>Customer Download Portal Help</h1>
        <p>
            Use this plugin to give existing WordPress users access to private product downloads,
            release notes, documentation links, license records, and support contact details.
            It does not handle checkout, payment, or public purchases.
        </p>

        <style>
            .cdp-help-page .cdp-help-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-top:16px}
            .cdp-help-page .cdp-help-card{background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:16px}
            .cdp-help-page .cdp-help-card h2{margin-top:0;font-size:18px}
            .cdp-help-page .cdp-help-card ol,.cdp-help-page .cdp-help-card ul{margin-left:20px}
            .cdp-help-page code{background:#f0f0f1;padding:2px 5px;border-radius:3px}
            .cdp-help-page .cdp-help-note{border-left:4px solid #2271b1;background:#fff;padding:12px 16px;margin:16px 0}
            .cdp-help-page .cdp-help-warning{border-left-color:#d63638}
        </style>

        <div class="cdp-help-note">
            <strong>Shortcode:</strong> create a customer-facing page and place <code>[customer_downloads]</code> in the page content.
        </div>

        <div class="cdp-help-grid">
            <div class="cdp-help-card">
                <h2>Recommended Setup</h2>
                <ol>
                    <li>Create a WordPress page named Customer Portal or My Downloads.</li>
                    <li>Add the shortcode <code>[customer_downloads]</code>.</li>
                    <li>Add the page to your site menu or customer account area.</li>
                    <li>Go to Customer Downloads &gt; Products and add your first product.</li>
                    <li>Add at least one product version and upload the protected download file.</li>
                    <li>Go to Assignments and assign the product to a WordPress user ID.</li>
                    <li>Optionally add license records, documentation links, and support details.</li>
                </ol>
            </div>

            <div class="cdp-help-card">
                <h2>Products And Versions</h2>
                <ul>
                    <li>A product is the customer-visible software, document package, or digital item.</li>
                    <li>A version contains the actual downloadable file, version label, release date, and release notes.</li>
                    <li>Mark one active version as latest when you want assignments without a Version ID to use it.</li>
                    <li>Uploaded files are moved to <code>wp-content/customer-download-files/</code>.</li>
                </ul>
            </div>

            <div class="cdp-help-card">
                <h2>Assignments</h2>
                <ul>
                    <li>Assignments grant one WordPress user access to one product.</li>
                    <li>Leave Version ID empty to use the product's latest active version.</li>
                    <li>Set Max downloads to <code>0</code> for unlimited downloads.</li>
                    <li>Set Expires at when a download link should stop working after a date and time.</li>
                    <li>The secure token link only works for the assigned logged-in user.</li>
                </ul>
            </div>

            <div class="cdp-help-card">
                <h2>Licenses</h2>
                <ul>
                    <li>Licenses are informational records shown to the assigned customer.</li>
                    <li>The plugin shows Active, Upcoming, or Expired based on start and end dates.</li>
                    <li>It does not perform license activation, key validation, or device binding.</li>
                </ul>
            </div>

            <div class="cdp-help-card">
                <h2>Documentation</h2>
                <ul>
                    <li>External documentation, video, and knowledge base URLs are visible to assigned customers.</li>
                    <li>Protected documentation file uploads are shown as secure download links to assigned logged-in customers.</li>
                    <li>External URLs can still be used for documentation, videos, and knowledge base pages hosted elsewhere.</li>
                </ul>
            </div>

            <div class="cdp-help-card">
                <h2>Support Details</h2>
                <ul>
                    <li>Go to Settings to configure support email, phone, and support portal URL.</li>
                    <li>The Support Tickets tab displays those details to logged-in customers.</li>
                    <li>This plugin does not include a built-in ticketing system.</li>
                </ul>
            </div>
        </div>

        <div class="cdp-help-note cdp-help-warning">
            <strong>Security checklist:</strong>
            customers must be logged in, direct file URLs should never be exposed, and Nginx sites should deny direct web access to
            <code>wp-content/customer-download-files/</code> if that directory is reachable from the public web root.
        </div>

        <div class="cdp-help-grid">
            <div class="cdp-help-card">
                <h2>Uninstall Behavior</h2>
                <p>
                    By default, deleting the plugin keeps database tables and uploaded files. To remove all plugin data during uninstall,
                    enable the deletion option in Customer Downloads &gt; Settings before deleting the plugin.
                </p>
            </div>

            <div class="cdp-help-card">
                <h2>Common Troubleshooting</h2>
                <ul>
                    <li>If a user sees no downloads, confirm the assignment uses the correct WordPress user ID.</li>
                    <li>If a latest-version assignment fails, confirm the product has one active version marked as latest.</li>
                    <li>If a file cannot download, confirm the uploaded file still exists in the protected download directory.</li>
                    <li>If support details are blank, configure them in Settings.</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
