<?php

global $wpdb;

/*
error_log("------------------------------------------");
error_log($wpdb->dbhost);
error_log($wpdb->dbname);
error_log($wpdb->dbuser);
error_log($wpdb->dbpassword);

error_log($_SERVER['SERVER_NAME']);
error_log("------------------------------------------");
*/

/* was attempt at direct call
add_action('admin_post_sparqlpress_scan', 'sparqlpress_scan_test');

function sparqlpress_scan_test()
{
    if (isset($_POST['test'])) {
        echo esc_html($_POST['test']);
        error_log('TEST = ' . $_POST['test']);
    }

    die(__FUNCTION__);
}
*/
?>

<!--     <input type="text" name="add_data" value="">     <label for="add_data">label</label> -->

<form action="<?php echo site_url('wp-json/sparqlpress/v1/create_store'); ?>" method="POST">
    <input type="hidden" name="action" value="create_store">
    <?php submit_button('Create Store'); ?>
</form>
<hr>

<form action="<?php echo site_url('wp-json/sparqlpress/v1/add_data'); ?>" method="POST" enctype="multipart/form-data">
    <input type="file" id="sparqlpress_data" name="sparqlpress_data" multiple="false">
    <?php submit_button('Add Data'); ?>
</form>

<hr>
<form action="<?php echo site_url('wp-json/sparqlpress/v1/scan_posts'); ?>" method="POST">
    <input type="hidden" name="action" value="sparqlpress_scan">
    <?php submit_button('Scan Posts'); ?>
</form>