<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_store_init');

function sparqlpress_store_init() {
  global $sparqlpress, $wpdb;
  $sparqlpress->store = false;
  add_action('sparqlpress_option_page_form', 'sparqlpress_store_option_page_form', 1);
  add_action('sparqlpress_option_page_submit', 'sparqlpress_store_option_page_submit', 1);
  $config = array(
    /* db */
    'db_host' => DB_HOST,
    'db_name' => DB_NAME,
    'db_user' => DB_USER,
    'db_pwd' => DB_PASSWORD,
    /* store */
    'store_name' => $wpdb->prefix . 'sparqlpress'
  );
  $sparqlpress->store = ARC2::getStore($config);
  if ($sparqlpress->store->isSetup())
    add_filter('sparqlpress_info_stats', 'sparqlpress_store_info_stats');

  function sparqlpress_store_option_page_form() {
    global $sparqlpress;
    if (!$sparqlpress->store || !$sparqlpress->store->isSetUp()) {
      print '
        <div class="create">
          <p class="submit">
            <input type="hidden" name="sparqlpress_store" value="t" />
            <input style="background: url('.$sparqlpress->location.'files/sparqlpress-button.png)" type="submit" name="Submit" value="' . __('Start using SparqlPress!') . '" />
            <br />
            The necessary database tables for SparqlPress will be created.
          </p>
        </div>';
    } else {
      print '
        <fieldset><legend>Store</legend>
          <div class="field">
            <input type="checkbox" id="sparqlpress_store_reset" name="sparqlpress_store_reset" value="t" />
            <label for="sparqlpress_store_reset">Delete all data from from SparqlPress</label>
          </div>
          <div class="field">
            <input type="checkbox" id="sparqlpress_store_delete" name="sparqlpress_store_delete" value="t" />
            <label for="sparqlpress_store_delete">Remove all SparqlPress tables from database</label>
          </div>
          <div class="field">
            <input type="checkbox" id="sparqlpress_store_clear" name="sparqlpress_store_clear" value="t" />
            <label for="sparqlpress_store_clear">Clear all SparqlPress options and reset to default values</label>
          </div>
        </fieldset>';
    }
  }

  function sparqlpress_store_option_page_submit() {
    print_r($_POST); /// DANNY
    global $sparqlpress;
    if ($_POST['sparqlpress_store']=='t' && !$sparqlpress->store->isSetUp())
      $sparqlpress->store->setUp();
    if ($_POST['sparqlpress_store_reset']=='t' && $sparqlpress->store->isSetUp())
      $sparqlpress->store->reset();
    if ($_POST['sparqlpress_store_delete']=='t' && $sparqlpress->store->isSetUp())
      $sparqlpress->store->drop();
    if ($_POST['sparqlpress_store_clear']=='t') {
      $sparqlpress->options = array();
    }
  }

  function sparqlpress_store_info_stats($stats) {
    global $sparqlpress;
    $stats[] = $sparqlpress->store->query('SELECT DISTINCT COUNT(?g) as ?Graphs WHERE { GRAPH ?g { ?s ?p ?o } }', 'row');
    $stats[] = $sparqlpress->store->query('SELECT COUNT(?s) as ?Triples WHERE { ?s ?p ?o }', 'row');
    $stats[] = $sparqlpress->store->query('SELECT COUNT(?s) as ?Quads WHERE { GRAPH ?g { ?s ?p ?o } }', 'row');
    return $stats;
  }

}

?>
