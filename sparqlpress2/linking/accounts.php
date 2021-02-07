<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_linking_accounts_init');

function sparqlpress_linking_accounts_init() {
  global $sparqlpress;
  if ($sparqlpress->scutter && $sparqlpress->scutter->isSetUp()) {
    add_action('show_user_profile', 'sparqlpress_linking_accounts_form');
    add_action('edit_user_profile', 'sparqlpress_linking_accounts_form');
    add_action('profile_update','sparqlpress_linking_accounts_submit');
    add_action('check_passwords','sparqlpress_linking_accounts_submit');
  }

  function sparqlpress_linking_accounts_form() {
    global $sparqlpress;
    if (!$sparqlpress->scutter || !$sparqlpress->scutter->isSetUp())
      return;
    print '
      <fieldset id="sparqlpress_linking_accounts">
      <legend>Online Accounts</legend>
      <div id="message" class="updated fade"><p>This is a placeholder for maintaining <a href="http://xmlns.com/foaf/spec/#term_OnlineAccount">online accounts</a> with SparqlPress.</p></div>
      </fieldset>';
  }

  function sparqlpress_linking_accounts_submit() {
    global $sparqlpress;
    if (!$sparqlpress->scutter || !$sparqlpress->scutter->isSetUp())
      return;
  }

}

?>