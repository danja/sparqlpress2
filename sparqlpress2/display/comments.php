<?php
/*
Revision: tag:morten@mfd-consult.dk,2010-02-19:13:15:36-y5cxg4ndacyey7n2
*/

add_action('sparqlpress_init', 'sparqlpress_display_comments_init');

function sparqlpress_display_comments_init() {
  global $sparqlpress;
  add_action('sparqlpress_option_page_form', 'sparqlpress_display_comments_option_page_form');
  add_action('sparqlpress_option_page_submit', 'sparqlpress_display_comments_option_page_submit');
  // if (!is_array($sparqlpress->options['display_comments']))
  if (!array_key_exists('display_comments', $sparqlpress->options))
    $sparqlpress->options['display_comments'] = array(
        'show_links' => 't');
  if ($sparqlpress->options['display_comments']['show_links']
      && $sparqlpress->store && $sparqlpress->store->isSetUp())
    add_filter('get_comment_author_link', 'sparqlpress_display_comments_author_link');

  function sparqlpress_display_comments_option_page_form() {
    global $sparqlpress;
    if ($sparqlpress->store && $sparqlpress->store->isSetUp()) {
      print '
        <fieldset><legend>Comment Display Options</legend>
          <input type="hidden" name="sparqlpress_display_comments_action" value="update" />
          <div class="field">
            <input type="checkbox" id="sparqlpress_display_comments_show_links" name="sparqlpress_display_comments_show_links" value="t" ' . ($sparqlpress->options['display_comments']['show_links'] ? 'checked="checked"' : '') . ' />
            <label for="sparqlpress_display_comments_show_links">Show links to profile and accounts</a>
          </div>
        </fieldset>';
    }
  }

  function sparqlpress_display_comments_option_page_submit() {
    global $sparqlpress;
    if (isset($_POST['sparqlpress_display_comments_action']) && is_array($sparqlpress->options['display_comments'])) {
      foreach ($sparqlpress->options['display_comments'] as $k => $v)
        $sparqlpress->options['display_comments'][$k] = $_POST['sparqlpress_display_comments_'.$k];
    }
  }

  function sparqlpress_display_comments_author_link($html) {
    global $comment, $sparqlpress, $openid, $userdata;

    // Show links?
    if (!$sparqlpress->options['display_comments']['show_links'])
      return $html;

    // Comment author must be registered user.
    if (!$comment->user_id)
      return $html;

    // OpenID plugin must be available
    if (!isset($openid) || !is_a($openid, 'WordpressOpenID')
        || !method_exists($openid->logic->store, 'get_my_identities'))
      return $html;

    // User must have OpenID identity URL
    $u = $userdata;
    $userdata = get_userdata($comment->user_id);
    $o = $openid->logic->store->get_my_identities();
    $userdata = $u;
    if (!is_array($o) || !sizeof($o) || !is_array($o[0]) || !isset($o[0]['url']))
      return $html;

    // Find user info in SparqlPress store, based on OpenID.
    $identity = $o[0]['url'];
    $query = '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT ?person ?foafdoc ?homepage ?weblog ?image
WHERE {
  GRAPH ?foafdoc {
    ?person foaf:openid <' . $identity . '> .
    OPTIONAL { ?person foaf:homepage ?homepage }
    OPTIONAL { ?person foaf:weblog ?weblog }
    OPTIONAL { ?person foaf:img ?image }
  }
}
LIMIT 1';
    $row = $sparqlpress->store->query($query, 'row');

    // Show info
    if ($row && !empty($row['person'])) {
      if (!empty($row['image']))
        $html = $html . ' <img src="' . htmlspecialchars($row['image']) . '" height="16" alt="Image" /> ';
      if (!empty($row['foafdoc'])) {
        $html = ' <a href="' . htmlspecialchars($row['foafdoc']) . '" title="View FOAF profile"><img src="' . $sparqlpress->location . 'files/foaf.png' . '" width="26" height="14" alt="FOAF" /></a> ' . $html;
        $html = ' <a href="http://xml.mfd-consult.dk/foaf/explorer/?foaf=' . htmlspecialchars($row['foafdoc']) . '" title="Explore FOAF profile"><img src="' . $sparqlpress->location . 'files/foaf-explorer.png' . '" width="16" height="16" alt="FOAF Explorer" /></a>' . $html;
      }
      if (!empty($row['homepage']))
        $html = ' <a href="' . htmlspecialchars($row['homepage']) . '" title="Homepage"><img src="' . $sparqlpress->location . 'files/home.png' . '" width="16" height="16" alt="Home" /></a> ' . $html;
      if (!empty($row['weblog']))
        $html = ' <a href="' . htmlspecialchars($row['weblog']) . '" title="Weblog"><img src="' . $sparqlpress->location . 'files/blog.png' . '" width="16" height="16" alt="Weblog" /></a> ' . $html;

      // Get account information, if present
      $query = '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?account ?service ?accountname ?profile
WHERE {
  <' . $row['person'] . '> foaf:holdsAccount ?account .
  ?account foaf:accountServiceHomepage ?service .
  OPTIONAL { ?account foaf:accountName ?accountname }
  OPTIONAL { ?account foaf:accountProfilePage ?profile }
  FILTER (isIri(?account) || bound(?profile))
} GROUP BY ?account';
      $rows = $sparqlpress->store->query($query, 'rows', '', true);
      $serviceicons = array(
          'amazon' => 'amazon.png',
          'bebo' => 'bebo.png',
          'del.icio.us' => 'delicious.png',
          'facebook' => 'facebook.png',
          'flickr' => 'flickr.png',
          'friendster' => 'friendster.png',
          'jaiku' => 'jaiku.png',
          'last.fm' => 'lastfm.png',
          'linkedin' => 'linkedin.png',
          'livejournal' => 'livejournal.png',
          'multiply' => 'multiply.png',
          'myspace' => 'myspace.png',
          'plaxo' => 'plaxo.png',
          'technorati' => 'technorati.png',
          'twitter' => 'twitter.png',
          'en.wikipedia.org' => 'wikipedia.png');
      foreach ($rows as $row) {
        $row['service'] = preg_replace('|^http://(www\.)?([^/]+?)(\.com)?(/.*)?$|', '$2', $row['service']);
        if (empty($serviceicons[$row['service']]))
          $serviceicons[$row['service']] = 'account.png';
        $href = $row['profile'] ? $row['profile'] : $row['account'];
        $title = $row['accountname'] ? $row['accountname'] . ' @ ' . $row['service'] : $row['service'];
        $html = ' <a href="' . $href . '" title="' . $title . '"><img src="' . $sparqlpress->location . 'files/' . $serviceicons[$row['service']] . '" alt="' . $row['service'] . '"/></a>' . $html;
      }
    }

    return $html;
  }

}

?>
