STORE ADMIN

<?php

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

<!-- form action="<?php echo admin_url('admin-post.php'); ?>"  method="POST" -->

<form action="<?php echo site_url('wp-json/sparqlpress/v1/create_store'); ?>" method="POST">

    <!-- http://localhost/wordpress/wp-json/sparqlpress/v1/scan_posts -->

    <input type="hidden" name="action" value="create_store">
    <!-- input type="text" name="test" value="TEST" -->
    <?php submit_button('Create Store'); ?>
</form>

<form action="<?php echo site_url('wp-json/sparqlpress/v1/scan_posts'); ?>" method="POST">

    <!-- http://localhost/wordpress/wp-json/sparqlpress/v1/scan_posts -->

    <input type="hidden" name="action" value="sparqlpress_scan">
    <!-- input type="text" name="test" value="TEST" -->
    <?php submit_button('Scan Posts'); ?>
</form>

