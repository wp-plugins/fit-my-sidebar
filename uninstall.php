<?php 
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	echo "ACCESS FORBIDDEN";
    exit();
}
	delete_option('fms_row_configs');
	delete_option('fms_rows_per_img');
	delete_option('fms_px_per_row');
	delete_option('fms_extr_for_feat');
	delete_option('fms_chars_per_row');
?>
