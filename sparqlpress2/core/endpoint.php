<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

error_log('endpoint.php');

add_action('sparqlpress_init', 'sparqlpress_endpoint_init');

if (!function_exists('sparqlpress_endpoint_init')) {
    function sparqlpress_endpoint_init() // endpoint
    {
        global $sparqlpress;
        $sparqlpress->endpoint = false;
        add_action('sparqlpress_option_page_form', 'sparqlpress_endpoint_option_page_form', 3);
        add_action('sparqlpress_option_page_submit', 'sparqlpress_endpoint_option_page_submit', 3);

        // if (!is_array($sparqlpress->options['endpoint']))
        if (!array_key_exists('endpoint', $sparqlpress->options)) {
            $sparqlpress->options['endpoint'] = array(
                'endpoint_active' => 0,
                'endpoint_features' => array('select', 'ask', 'construct', 'describe'),
                'endpoint_timeout' => 60,
                'endpoint_max_limit' => 150,
                'endpoint_read_key' => '',
                'endpoint_write_key' => substr(md5(DB_USER . DB_NAME), -10)
            );
        }
        if ($sparqlpress->store && $sparqlpress->options['endpoint']['endpoint_active']) {
            $sparqlpress->endpoint = ARC2::getStoreEndpoint(array_merge(array('store_allow_extension_functions' => 1), $sparqlpress->store->a, $sparqlpress->options['endpoint']));
            if ($sparqlpress->endpoint->isSetup()) {
                add_action('sparqlpress_request_sparql', 'sparqlpress_endpoint_sparql_request');
            }
        }

        function sparqlpress_endpoint_option_page_form()
        {
            global $sparqlpress;
            if (!$sparqlpress->store || !$sparqlpress->store->isSetUp()) {
                print '
        <div class="field">
          <input type="checkbox" id="sparqlpress_endpoint" name="sparqlpress_endpoint" checked="checked" value="t" />
          <label for="sparqlpress_endpoint">Also set up a secure public <a href="http://www.w3.org/TR/rdf-sparql-query/">SPARQL</a> endpoint</label>
        </div>';
            } else {
                print '
        <fieldset><legend>SPARQL endpoint</legend>
          <input type="hidden" name="sparqlpress_endpoint_action" value="update" />
          <div class="field">
            <input type="checkbox" id="sparqlpress_endpoint_active" name="sparqlpress_endpoint_active" value="t" ' . ($sparqlpress->options['endpoint']['endpoint_active'] ? 'checked="checked"' : '') . ' />
            <label for="sparqlpress_endpoint_active">Activate the SPARQL endpoint at</label> <a href="' . get_bloginfo('wpurl') . '/sparqlpress/sparql">/sparqlpress/sparql</a>
          </div>
          <div class="field">
            <label for="sparqlpress_endpoint_max_limit">Max number of results:</label>
            <input type="text" size="8" id="sparqlpress_endpoint_max_limit" name="sparqlpress_endpoint_max_limit" value="' . $sparqlpress->options['endpoint']['endpoint_max_limit'] . '" />
          </div>
          <div class="field">
            <p><a href="http://www.w3.org/TR/rdf-sparql-query/">SPARQL Read</a> features:</p>
              ' . sparqlpress_endpoint_get_features_fields(array('select', 'ask', 'construct', 'describe')) . '
            <label for="sparqlpress_endpoint_read_key">Required API key:</label>
            <input type="text" id="sparqlpress_endpoint_read_key" name="sparqlpress_endpoint_read_key" value="' . $sparqlpress->options['endpoint']['endpoint_read_key'] . '" />
          </div>
          <div class="field">
            <p><a href="http://arc.semsol.org/docs/v2/sparql+">SPARQL Write</a> features:</p>
              ' . sparqlpress_endpoint_get_features_fields(array('load', 'insert', 'delete')) . '
            <label for="sparqlpress_endpoint_write_key">Required API key:</label>
            <input type="text" id="sparqlpress_endpoint_write_key" name="sparqlpress_endpoint_write_key" value="' . $sparqlpress->options['endpoint']['endpoint_write_key'] . '" />
          </div>
        </fieldset>';
            }
        }

        function sparqlpress_endpoint_get_features_fields($flds)
        {
            global $sparqlpress;
            $r = '';
            $vals = $sparqlpress->options['endpoint']['endpoint_features'];
            if (!$vals) {
                $vals = array();
            }
            foreach ($flds as $fld) {
                $chk_code = in_array($fld, $vals) ? ' checked="checked"' : '';
                $r .= '<input type="checkbox" id="sparqlpress_endpoint_features_' . $fld . '" name="sparqlpress_endpoint_features[]" value="' . $fld . '"' . $chk_code . ' /> <label for="sparqlpress_endpoint_features_' . $fld . '">' . strtoupper($fld) . '</label><br />';
            }
            return $r;
        }

        function sparqlpress_endpoint_option_page_submit()
        {
            global $sparqlpress;

            // DANNY
            print_r($_POST);

            if (array_key_exists('sparqlpress_endpoint', $_POST) && $_POST['sparqlpress_endpoint'] == 't') {
                $sparqlpress->options['endpoint']['endpoint_active'] = 1;
                if (!$sparqlpress->endpoint) {
                    $sparqlpress->endpoint = ARC2::getStoreEndpoint(array_merge($sparqlpress->store->a, $sparqlpress->options['endpoint']));
                }
                if (!$sparqlpress->endpoint->isSetUp()) {
                    $sparqlpress->endpoint->setUp();
                }
            }

            // DANNY
            // Warning: Undefined array key "sparqlpress_endpoint_timeout"
            // in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/endpoint.php on line 94
            if (isset($_POST['sparqlpress_endpoint_action']) && is_array($sparqlpress->options['endpoint'])) {
                foreach ($sparqlpress->options['endpoint'] as $k => $v) {
                    if (array_key_exists('sparqlpress_' . $k, $_POST)) { // maybe should look why it wants endpoint_timeout
                        $sparqlpress->options['endpoint'][$k] = $_POST['sparqlpress_' . $k];
                    }
                }
            }
        }


        function sparqlpress_endpoint_sparql_request()
        {
            global $sparqlpress;
            if (
                $sparqlpress->endpoint && $sparqlpress->endpoint->isSetup()
                && $sparqlpress->options['endpoint']['endpoint_active']
            ) {
                $sparqlpress->endpoint->go();
            } else {
                print "The SPARQL endpoint is not active.";
            }
            exit;
        }
    }
}
