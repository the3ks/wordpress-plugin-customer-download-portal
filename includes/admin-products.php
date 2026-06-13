<?php
if (!defined('ABSPATH')) exit;

function cdp_render_products_page() {
    if (!current_user_can('manage_options')) return;
    global $wpdb;
    $products = cdp_table('products'); $versions = cdp_table('product_versions'); $assets = cdp_table('product_assets');

    if (isset($_POST['cdp_add_product'])) {
        check_admin_referer('cdp_add_product');
        $title = sanitize_text_field($_POST['title'] ?? '');
        if ($title) {
            $wpdb->insert($products, ['title'=>$title,'slug'=>sanitize_title($title),'description'=>sanitize_textarea_field($_POST['description'] ?? ''),'active'=>1,'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);
            echo '<div class="updated"><p>Product added.</p></div>';
        } else echo '<div class="error"><p>Product title is required.</p></div>';
    }

    if (isset($_POST['cdp_add_version'])) {
        check_admin_referer('cdp_add_version');
        $product_id = absint($_POST['product_id'] ?? 0); $label = sanitize_text_field($_POST['version_label'] ?? '');
        if ($product_id && $label && !empty($_FILES['version_file']['name'])) {
            $upload = cdp_safe_file_upload($_FILES['version_file'], 'downloads');
            if (is_wp_error($upload)) echo '<div class="error"><p>'.esc_html($upload->get_error_message()).'</p></div>';
            else {
                $is_latest = !empty($_POST['is_latest']) ? 1 : 0;
                if ($is_latest) $wpdb->update($versions, ['is_latest'=>0], ['product_id'=>$product_id], ['%d'], ['%d']);
                $wpdb->insert($versions, ['product_id'=>$product_id,'version_label'=>$label,'release_date'=>sanitize_text_field($_POST['release_date'] ?? ''),'file_path'=>$upload['path'],'file_name'=>$upload['name'],'file_size'=>$upload['size'],'release_notes'=>wp_kses_post($_POST['release_notes'] ?? ''),'is_latest'=>$is_latest,'active'=>1,'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);
                if ($is_latest) $wpdb->update($products, ['current_version_id'=>$wpdb->insert_id,'updated_at'=>current_time('mysql')], ['id'=>$product_id]);
                echo '<div class="updated"><p>Version added.</p></div>';
            }
        } else echo '<div class="error"><p>Product, version label, and download file are required.</p></div>';
    }

    if (isset($_POST['cdp_add_asset'])) {
        check_admin_referer('cdp_add_asset');
        $product_id = absint($_POST['asset_product_id'] ?? 0); $title = sanitize_text_field($_POST['asset_title'] ?? '');
        $type = sanitize_text_field($_POST['asset_type'] ?? 'documentation');
        $external = esc_url_raw($_POST['external_url'] ?? ''); $file_path=null; $file_name=null; $file_size=0;
        if (!empty($_FILES['asset_file']['name'])) {
            $upload = cdp_safe_file_upload($_FILES['asset_file'], 'assets');
            if (is_wp_error($upload)) echo '<div class="error"><p>'.esc_html($upload->get_error_message()).'</p></div>';
            else { $file_path=$upload['path']; $file_name=$upload['name']; $file_size=$upload['size']; }
        }
        if ($product_id && $title && ($external || $file_path)) {
            $wpdb->insert($assets, ['product_id'=>$product_id,'version_id'=>absint($_POST['asset_version_id'] ?? 0) ?: null,'asset_type'=>$type,'title'=>$title,'description'=>sanitize_textarea_field($_POST['asset_description'] ?? ''),'file_path'=>$file_path,'file_name'=>$file_name,'file_size'=>$file_size,'external_url'=>$external,'sort_order'=>absint($_POST['sort_order'] ?? 0),'active'=>1,'created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);
            echo '<div class="updated"><p>Asset added.</p></div>';
        } elseif (!isset($upload) || !is_wp_error($upload)) echo '<div class="error"><p>Product, title, and either file or URL are required.</p></div>';
    }

    if (isset($_POST['cdp_delete_product'])) { check_admin_referer('cdp_delete_product'); $id=absint($_POST['product_id']); $wpdb->delete($products,['id'=>$id]); echo '<div class="updated"><p>Product deleted from catalog. Existing files are kept.</p></div>'; }
    if (isset($_POST['cdp_delete_version'])) { check_admin_referer('cdp_delete_version'); $id=absint($_POST['version_id']); $wpdb->delete($versions,['id'=>$id]); echo '<div class="updated"><p>Version deleted from catalog. Existing file is kept.</p></div>'; }
    if (isset($_POST['cdp_delete_asset'])) { check_admin_referer('cdp_delete_asset'); $id=absint($_POST['asset_id']); $wpdb->delete($assets,['id'=>$id]); echo '<div class="updated"><p>Asset deleted from catalog. Existing file is kept.</p></div>'; }

    $product_rows = $wpdb->get_results("SELECT * FROM $products ORDER BY id DESC");
    $version_rows = $wpdb->get_results("SELECT v.*, p.title product_title FROM $versions v JOIN $products p ON p.id=v.product_id ORDER BY v.id DESC LIMIT 200");
    $asset_rows = $wpdb->get_results("SELECT a.*, p.title product_title FROM $assets a JOIN $products p ON p.id=a.product_id ORDER BY a.id DESC LIMIT 200");
    ?>
    <div class="wrap"><h1>Customer Download Products</h1>
    <h2>Add Product</h2><form method="post"><?php wp_nonce_field('cdp_add_product'); ?><table class="form-table"><tr><th>Title</th><td><input name="title" class="regular-text" required></td></tr><tr><th>Description</th><td><textarea name="description" class="large-text" rows="3"></textarea></td></tr></table><p><button class="button button-primary" name="cdp_add_product" value="1">Add Product</button></p></form>

    <h2>Add Product Version / Download</h2><form method="post" enctype="multipart/form-data"><?php wp_nonce_field('cdp_add_version'); ?><table class="form-table"><tr><th>Product</th><td><select name="product_id" required><option value="">Select</option><?php foreach($product_rows as $p) echo '<option value="'.esc_attr($p->id).'">'.esc_html($p->title).'</option>'; ?></select></td></tr><tr><th>Version</th><td><input name="version_label" class="regular-text" placeholder="2.0.0" required></td></tr><tr><th>Release date</th><td><input type="date" name="release_date"></td></tr><tr><th>Download file</th><td><input type="file" name="version_file" required></td></tr><tr><th>Release notes</th><td><textarea name="release_notes" class="large-text" rows="5"></textarea></td></tr><tr><th>Latest</th><td><label><input type="checkbox" name="is_latest" value="1" checked> Mark as latest version</label></td></tr></table><p><button class="button button-primary" name="cdp_add_version" value="1">Add Version</button></p></form>

    <h2>Add Product Asset</h2><form method="post" enctype="multipart/form-data"><?php wp_nonce_field('cdp_add_asset'); ?><table class="form-table"><tr><th>Product</th><td><select name="asset_product_id" required><option value="">Select</option><?php foreach($product_rows as $p) echo '<option value="'.esc_attr($p->id).'">'.esc_html($p->title).'</option>'; ?></select></td></tr><tr><th>Version ID</th><td><input type="number" name="asset_version_id"> <span class="description">Optional. Leave empty for product-level documentation.</span></td></tr><tr><th>Type</th><td><select name="asset_type"><option value="documentation">Documentation</option><option value="release_note">Release Note</option><option value="video">Video</option><option value="kb">Knowledge Base</option></select></td></tr><tr><th>Title</th><td><input name="asset_title" class="regular-text" required></td></tr><tr><th>Description</th><td><textarea name="asset_description" class="large-text" rows="2"></textarea></td></tr><tr><th>File</th><td><input type="file" name="asset_file"></td></tr><tr><th>External URL</th><td><input type="url" name="external_url" class="regular-text"></td></tr><tr><th>Sort order</th><td><input type="number" name="sort_order" value="0"></td></tr></table><p><button class="button button-primary" name="cdp_add_asset" value="1">Add Asset</button></p></form>

    <h2>Products</h2><table class="widefat striped"><thead><tr><th>ID</th><th>Title</th><th>Description</th><th>Action</th></tr></thead><tbody><?php foreach($product_rows as $p): ?><tr><td><?php echo esc_html($p->id); ?></td><td><?php echo esc_html($p->title); ?></td><td><?php echo esc_html(wp_trim_words($p->description,20)); ?></td><td><form method="post"><?php wp_nonce_field('cdp_delete_product'); ?><input type="hidden" name="product_id" value="<?php echo esc_attr($p->id); ?>"><button class="button" name="cdp_delete_product" value="1" onclick="return confirm('Delete product catalog record? Files are kept.');">Delete</button></form></td></tr><?php endforeach; ?></tbody></table>

    <h2>Versions / Downloads</h2><table class="widefat striped"><thead><tr><th>ID</th><th>Product</th><th>Version</th><th>Release Date</th><th>File</th><th>Latest</th><th>Action</th></tr></thead><tbody><?php foreach($version_rows as $v): ?><tr><td><?php echo esc_html($v->id); ?></td><td><?php echo esc_html($v->product_title); ?></td><td><?php echo esc_html($v->version_label); ?></td><td><?php echo esc_html($v->release_date); ?></td><td><?php echo esc_html($v->file_name); ?></td><td><?php echo $v->is_latest ? 'Yes':'No'; ?></td><td><form method="post"><?php wp_nonce_field('cdp_delete_version'); ?><input type="hidden" name="version_id" value="<?php echo esc_attr($v->id); ?>"><button class="button" name="cdp_delete_version" value="1">Delete</button></form></td></tr><?php endforeach; ?></tbody></table>

    <h2>Assets</h2><table class="widefat striped"><thead><tr><th>ID</th><th>Product</th><th>Type</th><th>Title</th><th>File/URL</th><th>Action</th></tr></thead><tbody><?php foreach($asset_rows as $a): ?><tr><td><?php echo esc_html($a->id); ?></td><td><?php echo esc_html($a->product_title); ?></td><td><?php echo esc_html($a->asset_type); ?></td><td><?php echo esc_html($a->title); ?></td><td><?php echo esc_html($a->file_name ?: $a->external_url); ?></td><td><form method="post"><?php wp_nonce_field('cdp_delete_asset'); ?><input type="hidden" name="asset_id" value="<?php echo esc_attr($a->id); ?>"><button class="button" name="cdp_delete_asset" value="1">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php
}
