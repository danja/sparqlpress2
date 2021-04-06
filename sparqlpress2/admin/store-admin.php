STORE ADMIN

<?php

add_action( 'admin_post_sparqlpress_scan', 'sparqlpress_scan_test' );

function sparqlpress_scan_test() {
    if ( isset ( $_POST['test'] ) ){
        echo esc_html( $_POST['test'] );
        error_log('TEST = '.$_POST['test']);
    }

    die( __FUNCTION__ );
}


?>

<form action="<?php echo admin_url( 'admin-post.php' ); ?>"  method="POST">
<input type="hidden" name="action" value="sparqlpress_scan">
<input type="text" name="test" value="">
<?php submit_button( 'Send' ); ?>
</form>