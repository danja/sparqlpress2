<?php

error_log('endpoint.php run');



// echo $_SERVER['REQUEST_URI'];
/*
function yasgui_head() {
    echo '<link href="https://unpkg.com/@triply/yasgui/build/yasgui.min.css" rel="stylesheet" type="text/css" />';
    echo '<script src="https://unpkg.com/@triply/yasgui/build/yasgui.min.js"></script>';
}

add_action( 'admin_head', 'yasgui_head' );
*/

/*
add_action('admin_enqueue_scripts', 'yasgui_head');
 
function yasgui_head($hook) {
    // your-slug => The slug name to refer to this menu used in "add_submenu_page"
        // tools_page => refers to Tools top menu, so it's a Tools' sub-menu page
    if ( 'sparqlpress_page_endpoint' != $hook ) {
        error_log('function yasgui_head hook = '.$hook);
        return;
    }
 
    wp_enqueue_style('yasgui-css', plugins_url('admin/css/yasgui.min.css',__FILE__ ));
    wp_enqueue_script('yasgui-js', plugins_url('admin/js/yasgui.min.js',__FILE__ ));
}
*/

/*
echo '<div id="yasgui"></div>';
echo '<script>';
echo 'const yasgui = new Yasgui(document.getElementById("yasgui"));';
echo '</script>';
*/

?>



    <div id="yasgui"></div>
    <script>
        const yasgui = new Yasgui(document.getElementById("yasgui"));
    </script>

