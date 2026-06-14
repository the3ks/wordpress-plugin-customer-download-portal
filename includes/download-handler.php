<?php
if (!defined('ABSPATH')) exit;
function cdp_handle_download_request(){
 if(empty($_GET['cdp_download'])||empty($_GET['token'])) return;
 if(!is_user_logged_in()) wp_die('Please login to download this file.','Login required',['response'=>401]);
 global $wpdb; $token=sanitize_text_field(wp_unslash($_GET['token'])); $user_id=get_current_user_id(); $a=cdp_table('assignments'); $p=cdp_table('products'); $v=cdp_table('product_versions');
 $item=$wpdb->get_row($wpdb->prepare("SELECT a.*, p.title, v.id matched_version_id, COALESCE(v.id, latest.id) resolved_version_id, COALESCE(v.version_label, latest.version_label) version_label, COALESCE(v.file_path, latest.file_path) file_path, COALESCE(v.file_name, latest.file_name) file_name, COALESCE(v.file_size, latest.file_size) file_size FROM $a a JOIN $p p ON p.id=a.product_id LEFT JOIN $v v ON v.id=a.version_id AND v.product_id=a.product_id AND v.active=1 LEFT JOIN $v latest ON latest.product_id=a.product_id AND latest.is_latest=1 AND latest.active=1 WHERE a.token=%s LIMIT 1",$token));
 if(!$item || (int)$item->user_id !== (int)$user_id) wp_die('Invalid download link.','Invalid download',['response'=>403]);
 if($item->version_id && !$item->matched_version_id) wp_die('Invalid download link.','Invalid download',['response'=>403]);
 if($item->expires_at && strtotime($item->expires_at) < current_time('timestamp')) wp_die('This download link has expired.','Expired',['response'=>403]);
 if((int)$item->max_downloads>0 && (int)$item->download_count >= (int)$item->max_downloads) wp_die('Download limit reached.','Limit reached',['response'=>403]);
 $file_path=cdp_resolve_protected_file_path($item->file_path);
 if(!$file_path) wp_die('File not found.','Missing file',['response'=>404]);
 $wpdb->query($wpdb->prepare("UPDATE $a SET download_count=download_count+1 WHERE id=%d",$item->id));
 $wpdb->insert(cdp_table('download_logs'),['assignment_id'=>$item->id,'user_id'=>$user_id,'product_id'=>$item->product_id,'version_id'=>$item->resolved_version_id,'ip_address'=>isset($_SERVER['REMOTE_ADDR'])?sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])):'','user_agent'=>isset($_SERVER['HTTP_USER_AGENT'])?sanitize_textarea_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])):'','downloaded_at'=>current_time('mysql')]);
 nocache_headers(); while(ob_get_level()) ob_end_clean();
 header('Content-Description: File Transfer'); header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.basename($item->file_name).'"'); header('Content-Length: '.filesize($file_path)); header('X-Content-Type-Options: nosniff'); readfile($file_path); exit;
}

function cdp_handle_asset_download_request(){
 if(empty($_GET['cdp_asset_download'])||empty($_GET['asset_id'])) return;
 if(!is_user_logged_in()) wp_die('Please login to download this file.','Login required',['response'=>401]);
 $asset_id=absint($_GET['asset_id']);
 if(!$asset_id || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce']??'')), 'cdp_asset_download_'.$asset_id)) wp_die('Invalid download link.','Invalid download',['response'=>403]);
 global $wpdb; $user_id=get_current_user_id(); $assets=cdp_table('product_assets'); $products=cdp_table('products'); $assignments=cdp_table('assignments');
 $asset=$wpdb->get_row($wpdb->prepare("SELECT a.*, p.title product_title FROM $assets a JOIN $products p ON p.id=a.product_id JOIN $assignments ass ON ass.product_id=a.product_id AND ass.user_id=%d WHERE a.id=%d AND a.active=1 AND a.asset_type IN ('documentation','video','kb') LIMIT 1",$user_id,$asset_id));
 if(!$asset) wp_die('Invalid download link.','Invalid download',['response'=>403]);
 $file_path=cdp_resolve_protected_file_path($asset->file_path);
 if(!$file_path) wp_die('File not found.','Missing file',['response'=>404]);
 nocache_headers(); while(ob_get_level()) ob_end_clean();
 $file_name=$asset->file_name ?: basename($file_path);
 header('Content-Description: File Transfer'); header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.basename($file_name).'"'); header('Content-Length: '.filesize($file_path)); header('X-Content-Type-Options: nosniff'); readfile($file_path); exit;
}
