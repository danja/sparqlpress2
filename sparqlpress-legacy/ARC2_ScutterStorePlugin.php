<?php
/*
homepage: http://bzr.mfd-consult.dk/scutter-store/
license:  http://arc.semsol.org/license

class:    ARC2 Scutter Store Plugin
author:   Morten Høybye Frederiksen
version:  $ bzr-revision-id $

Danny Note -
from https://web.archive.org/web/20080803191253/http://bzr.mfd-consult.dk/scutter-store/ARC2_ScutterStorePlugin.php
*/

ARC2::inc('Store');

class ARC2_ScutterStorePlugin extends ARC2_Store {

  function __construct($a = '', &$caller) {
    parent::__construct($a, $caller);
  }

  function ARC2_ScutterStorePlugin($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
  }

  /*
    cloneTables: Clone all tables in store, including content, to new set of tables with new prefix
  */
  function cloneTables($newprefix) {
      $con = $this->getDBCon();
      $this->optimizeTables(3);

      // Create destination tables.
      $dest = ARC2::getStore(array_merge($this->a, array('store_name' => $newprefix)));
      $dest->drop();
      $dest->setUp();
      $newprefix = $dest->getTablePrefix();

      // Copy data
    $tbls = $this->getTables();
    $prefix = $this->getTablePrefix();
    $dest->lockTables($this->getTables());
    foreach ($tbls as $tbl) {
      mysql_query('INSERT INTO ' . $newprefix . $tbl . ' SELECT * FROM ' . $prefix . $tbl);
      if ($err = mysql_error()) {
        $this->addError($err . ' while cloning ' . $tbl . ' from ' . $prefix . ' to ' . $newprefix);
        $dest->reset();
        break;
      }
    }
    $dest->unlockTables();
      $dest->optimizeTables(3);
  }

  /*
    ns: Return scutter vocabulary namespace IRI (seeAlso: http://wiki.foaf-project.org/ScutterVocab)
  */
  function ns() {
    return $this->v('scutter_ns', 'http://purl.org/net/scutter/', $this->a);
  }

  /*
    addGraph: Register a graph (URL) and queue for later fetching.
              Origin (foaf:maker) is recorded as either:
              * An IRI, if the supplied origin argument is a string
              * A description of a person, if the supplied origin argument is an array,
                with turtle-style property/objects:
                $origin = array(
                  'a foaf:Person',
                  'foaf:name "Joe Triple"',
                  'foaf:weblog <http://www.example.org/~joe/blog>');
  */
  function addGraph($url, $origin, $fail=false) {
    # Check for existing graph, don't want to add twice.
    $rs = $this->query('PREFIX scutter: <'.$this->ns().'> ASK { ?graph scutter:source <'.$url.'> }', 'raw');
    if ($rs) {
      if (!$fail)
        return false;
      return $this->addError('Unable to add, graph already registered with scutter: "'.$url.'"');
    }

    # Determine shadow scutter graph.
    $gid = md5($url);

    # Create and fill template for graph information.
    $t = '@prefix scutter: <' . $this->ns() . '> .
      @prefix dct: <http://purl.org/dc/terms/> .
      @prefix foaf: <http://xmlns.com/foaf/0.1/> .
      [ a scutter:Representation ;
        scutter:source ?url ;
        dct:date ?date ';
    if (is_array($origin))
      $t .= '; scutter:origin [ a <http://www.w3.org/2002/12/cal/ical#Vevent> ;
        foaf:maker [ ' . join('; ', $origin) . ' ] ] ] .';
    else
      $t .= '; scutter:origin <' . $origin . '> ] .';
    $v = array('url' => $url, 'date' => gmdate('Y-m-d\TH:i:s\Z'));
    $i = $this->getFilledTemplate($t, $v);

    # Insert into storage.
    $r = $this->insert($i, $this->ns() . '#' . $gid);
    $this->consolidateIFP($this->ns() . 'source');
    return $r;
}

  /*
    queueGraph: Queue registerede graph for immediate fetching.
  */
  function queueGraph($url) {
    # Check for existing graph, must exist.
    $rs = $this->query('PREFIX scutter: <'.$this->ns().'> ASK { ?graph scutter:source <'.$url.'> }');
    if (!$rs['result'])
      return $this->addError('Unable to queue, graph not registered with scutter: "'.$url.'"');

    # Determine shadow scutter graph.
    $gid = md5($url);

    # Remove link to latest fetch.
    $r = $this->query('DELETE FROM <' . $this->ns() . '#' . $gid . '> { ?r <' . $this->ns() . 'latestFetch> ?f }');
    return $r;
}

  /*
    skipGraph: Mark a previously registered graph (URL) as to be skipped, i.e. not to be fetched, for a specific textual reason,
               optionally attributed to a resource described as an array, with turtle-style property/objects:
               $who = array(
                 'a foaf:Person',
                 'foaf:name "Joe Triple"',
                 'foaf:weblog <http://www.example.org/~joe/blog>');
  */
  function skipGraph($url, $reason, $who=false, $delete_content=false) {
    # Check for existing graph, can't mark a nonexisting graph.
    $rs = $this->query('PREFIX scutter: <'.$this->ns().'> ASK { ?graph scutter:source <'.$url.'> }');
    if (!$rs['result'])
      return $this->addError('Unable to skip, graph not registered with scutter: "'.$url.'"');

    # Check if already skipped, no reason to skip more than once.
    $rs = $this->query('PREFIX scutter: <'.$this->ns().'> ASK { ?graph scutter:source <'.$url.'> . ?graph scutter:skip ?reason }');
    if ($rs['result'])
      return $this->addError('Unable to skip, graph already marked to be skipped: "'.$url.'"');

    # Delete graph content?
    if ($delete_content)
      $this->query('DELETE FROM <' . $url . '>');

    # Determine shadow scutter graph.
    $gid = md5($url);

    # Create and fill template for graph information.
    $t = '@prefix scutter: <' . $this->ns() . '> .
      @prefix dct: <http://purl.org/dc/terms/> .
      @prefix foaf: <http://xmlns.com/foaf/0.1/> .
      [ scutter:source ?url ;
        scutter:skip [ '.($who?'foaf:maker [ ' . join('; ', $who) . ' ] ;':'').'
          a scutter:Reason; dct:date ?date; dct:description ?desc ] ] .';
    $v = array('url' => $url, 'desc' => $reason,
      'date' => gmdate('Y-m-d\TH:i:s\Z'));
    $i = $this->getFilledTemplate($t, $v);

    # Insert into storage.
    $r = $this->insert($i, $this->ns() . '#' . $gid);
    $this->consolidateIFP($this->ns() . 'source');
    return $r;
  }

  /*
    removeGraph: Unregister and delete a previously registered graph (URL).
  */
  function removeGraph($url) {
    # Check for existing graph, can't remove a nonexisting graph.
    $rs = $this->query('PREFIX scutter: <'.$this->ns().'> ASK { ?graph scutter:source <'.$url.'> }');
    if (!$rs['result'])
      return $this->addError('Unable to remove, graph not registered with scutter: "'.$url.'"');

    # Determine shadow scutter graph.
    $gid = md5($url);

    # Remove graph description and graph itself.
    $rs = $this->query('DELETE FROM <' . $this->ns() . '#' . $gid . '>');
    return $this->query('DELETE FROM <' . $url . '>');
  }

  /*
    go: Find and fetch next graph(s) in queue.
  */
  function go($count=1) {
    # Repeat until done...
    $sleep = $this->v('scutter_sleep', 10, $this->a);
    $res = array('g_count' => 0, 'graphs' => array());
    while ($count) {
      if (!$this->getLock())
        return $this->addError('Unable to obtain scutter lock.');
      # Find a new representation that should be fetched (not skipped).
      $row = $this->query('
PREFIX scutter: <'.$this->ns().'>
SELECT ?url
WHERE { ?r scutter:source ?url .
  OPTIONAL { ?r scutter:skip ?s } .
  OPTIONAL { ?r scutter:latestFetch ?f }
  FILTER ( isIri(?url) && regex(str(?url), "^http:") && !BOUND(?s) && !BOUND(?f) )
} LIMIT 1', 'row');
      if ($e = $this->getErrors()) {
         $this->releaseLock();
        return $this->addError('Oops: "' . join("\n", $e) . '"');
      }
      if (!isset($row['url'])) {
        # Find a representation that should be updated (not skipped).
        $row = $this->query('
PREFIX scutter: <'.$this->ns().'>
PREFIX mysql: <http://web-semantics.org/ns/mysql/>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT ?url
WHERE { ?r scutter:source ?url .
  OPTIONAL { ?r scutter:skip ?s } .
  ?r scutter:latestFetch ?f .
  ?f scutter:interval ?interval ; dct:date ?date
  FILTER ( isIri(?url) && regex(str(?url), "^http:") && !BOUND(?s) &&
      (mysql:unix_timestamp(mysql:replace(mysql:replace(?date,"T",""),"Z","")) + ?interval < mysql:unix_timestamp("' . gmdate('Y-m-d H:i:s') . '") ) )
} LIMIT 1', 'row');
        if ($e = $this->getErrors()) {
          $this->releaseLock();
          return $this->addError('Oops: "' . join("\n", $e) . '"');
        }
        if (!isset($row['url'])) {
          $this->releaseLock();
          return $res;
        }
      }
      # Perform and register fetch...
      if (is_array($r = $this->fetch($row['url']))) {
        foreach ($r as $k => $v) {
          if (is_numeric($v)) {
            if (!isset($res[$k]))
              $res[$k] = $v;
            elseif (is_numeric($res[$k]))
              $res[$k] += $v;
          }
        }
        $res['g_count']++;
        $res['graphs'][] = $row['url'];
      }
       $this->releaseLock();
       $count--;
      if ($count)
        sleep($sleep);
    }
    return $res;
  }

  /*
    getLock
  */
  function getLock($t_out = 10, $t_out_init = '') { // DANNY added $t_out = 10, $t_out_init = ''
      $con = $this->getDBCon();
    $k = 'scutter_running_since';
    $tbl = $this->getTablePrefix() . 'setting';
    $lock = gmdate('Y-m-d\TH:i:s\Z');
    # Try to obtain lock
    $sql = "INSERT INTO " . $tbl . " (k, val) VALUES ('" . md5($k) . "', '" . mysql_real_escape_string(serialize($lock)) . "')";
    if (!mysql_query($sql) || !mysql_affected_rows()) {
      # Is it stale (more than minimum interval)?
      $v = $this->getSetting($k);
      $minimum_interval = $this->v('scutter_minimum_interval', 60*60, $this->a);
      if ($v && ((strtotime(preg_replace('|[a-z]|i', ' ', $v)) + $minimum_interval) < mktime())) {
        $sql = "DELETE FROM " . $tbl . " WHERE k = '" . md5($k) . "' AND val = '" . mysql_real_escape_string(serialize($v)) . "'";
        if (!mysql_query($sql) || !mysql_affected_rows())
          return $this->addError('Fatal error, unable to remove stale scutter lock');
        return $this->getLock();
      }
      return false;
    }
    return $lock;
  }

  /*
    releaseLock
  */
  function releaseLock() {
    $this->removeSetting('scutter_running_since');
  }

  /*
    fetch: Fetch a graph and register results, if graph is "scheduled" or forced.
  */
  function fetch($url, $force=false) {
    $default_interval = $this->v('scutter_interval', 60*60*24, $this->a);
    $minimum_interval = $this->v('scutter_minimum_interval', 60*60, $this->a);
    $maximum_interval = $this->v('scutter_maximum_interval', 60*60*24*7, $this->a);

    # Find information from latest fetch.
    $row = $this->query('
PREFIX scutter: <'.$this->ns().'>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT ?interval ?etag ?lm ?ct ?cl ?count ?error
WHERE { ?r scutter:source <' . $url . '> ; scutter:latestFetch ?f .
  ?f scutter:interval ?interval ; scutter:rawTripleCount ?count .
  OPTIONAL { ?f scutter:etag ?etag }
  OPTIONAL { ?f scutter:lastModified ?lm }
  OPTIONAL { ?f scutter:contentType ?ct }
  OPTIONAL { ?f scutter:contentLength ?cl }
  OPTIONAL { ?f scutter:error ?e . ?e dct:description ?error }
  FILTER (isIri(?url) && regex(str(?url), "^http:"))
} LIMIT 1', 'row');
    if ($this->getErrors())
      return $this->addError('Unable to find information about latest fetch for <' . $url . '>.');

    # Prepare for fetch.
    $gid = md5($url);
    $interval = @$row['interval'];
    $etag = @$row['etag'];
    $lm = @$row['lm'];
    $ct = @$row['ct'];
    $cl = @$row['cl'];
    $count = @$row['count'];
    if (($error = @$row['error']) || $force) {
      $etag = '';
      $lm = '';
    }

    # Perform fetch with support for redirects.
    $location = $url;
    $newcount = 0;
    $newerror = '';
    $r = array();
    do {
      # Perform GET.
      $get = $this->get($location, $etag, $lm);
      # Internal error...
      if (!is_array($get)) {
        $newerror = $get;
        $get = array('code' => '502', 'headers' => array());
      }
      # 204:
      if ('204'==$get['code'] || ('2'==substr($get['code'],0,1)) && empty($get['body'])) {
        $newerror = 'Hmm, no content was returned from ' . $location . ($url!=$location?' (' . $url . ')':'');
        $get['code'] = '204';
      }
      # 2xx: OK, parse and decrease interval iff succesful.
      elseif ('2'==substr($get['code'],0,1)) {
        # Parse.
        $format = ARC2::getFormat($get['body'], $get['headers']['Content-Type']);
        $parser = ARC2::getRDFParser(array('format' => $format));
        $parser->parse($location, $get['body']);
        if ('html'==$format)
          $parser->extractRDF();
        if ($e = $parser->getErrors()) {
          $interval *= 2;
          if ('rss'!=$format && 'atom'!=$format)
            $newerror = 'Unable to parse ' . $location . ($url!=$location?' (' . $url . ')':'') . ': ' . join(' ', $e);
        } else {
          if (!$force)
            $interval = (int)($interval/2);
          if ($parser->countTriples()) {
            $newcount = $parser->parser->countTriples();
            $this->query('DELETE FROM <' . $url . '>');
            $i = $parser->getSimpleIndex(0);
            $r = $this->insert($i, $url);
            if (!$r['t_count'] && $newcount)
              $newerror = 'Oops, none of the ' . $newcount . ' parsed triples were inserted from ' . $location . ($url!=$location?' (' . $url . ')':'');
          }
        }
      }
      # 301: Permanently moved, mark as skipped, create new Representation for later fetch.
      elseif ('301'==$get['code'] && isset($get['headers']['Location'])) {
        $this->skipGraph($url, sprintf('Moved, new location: %s', $get['headers']['Location']));
        $this->addGraph($get['headers']['Location'], $url);
      }
      # 304: Not changed, increase interval.
      elseif ('304'==$get['code']) {
        $interval += $minimum_interval;
        $newcount = $count;
        if (!isset($get['headers']['Last-Modified'])) $get['headers']['Last-Modified'] = $lm;
        if (!isset($get['headers']['ETag'])) $get['headers']['ETag'] = $etag;
        if (!isset($get['headers']['Content-Type'])) $get['headers']['Content-Type'] = $ct;
        if (!isset($get['headers']['Content-Length'])) $get['headers']['Content-Length'] = $cl;
      }
      # 3xx: Temporary redirect, refetch from new location.
      elseif ('3'==substr($get['code'],0,1)) {
        $location = $get['headers']['Location'];
      }
      # 404, 5xx: Not found or server error, increase interval.
      elseif ('404'==$get['code'] || '5'==substr($get['code'],0,1)) {
        if (!empty($newerror))
          $newerror = sprintf('Retrieval error: %s, will keep trying', $get['code']);
        $interval += $default_interval;
        # Check for permanent errors.
        if ($interval >= $maximum_interval) {
          $rs = $this->query('
PREFIX scutter: <' . $this->ns() . '>
ASK { ?r scutter:source <' . $url . '> ; scutter:fetch ?f .
  ?f scutter:status ?status .
  FILTER (?status != "' . $get['code'] . '") }');
          if (!$this->getErrors() && $rs && is_array($rs) && !$rs['result']) {
            $newerror = sprintf('Permanent retrieval error: %s, marked as skipped', $get['code']);
            $this->skipGraph($url, $newerror);
          }
        }
      }
      # 4xx: Gone or other error, mark as skipped.
      else {
        $newerror = sprintf('Retrieval error: %s, marked as skipped', $get['code']);
        $this->skipGraph($url, $newerror);
      }
    } while (!$newerror && '3'==substr($get['code'],0,1) && '301'!=$get['code'] && '304'!=$get['code']);

    # Remove lastestFetch (but keep fetch), if present.
    if ($interval) {
      $this->query('DELETE FROM <' . $this->ns() . '#' . $gid . '> { ?r <' . $this->ns() . 'latestFetch> ?f }');
      if ($interval < $minimum_interval)
        $interval = $minimum_interval;
      if ($interval > $maximum_interval)
        $interval = $maximum_interval;
    } else
      $interval = $default_interval;

    # Create and fill template for graph information.
    $now = gmmktime();
    $t = '@prefix scutter: <' . $this->ns() . '> .
      @prefix dct: <http://purl.org/dc/terms/> .
      @prefix foaf: <http://xmlns.com/foaf/0.1/> .
      [ scutter:source ?url ;
        scutter:fetch _:f' . $gid . $now . ' ;
        scutter:latestFetch _:f' . $gid . $now . ' ] .
      _:f' . $gid . $now . ' a scutter:Fetch ;
        dct:date ?date ;
        scutter:interval ?interval ;
        scutter:status ?status ;
        scutter:etag ?etag ;
        scutter:lastModified ?lm ;
        scutter:contentType ?ct ;
        scutter:contentLength ?cl ;
        scutter:rawTripleCount ?count . ';
    if ($newerror)
      $t .= '_:f' . $gid . $now . ' scutter:error
        [ a scutter:Reason; dct:description ?error ] .';
    $v = array('url' => $url, 'interval' => $interval, 'status' => $get['code'],
      'count' => $newcount, 'error' => $newerror,
      'date' => gmdate('Y-m-d\TH:i:s\Z'));
    if (isset($get['headers']['ETag']))
      $v['etag'] = $get['headers']['ETag'];
    if (isset($get['headers']['Last-Modified']))
      $v['lm'] = $get['headers']['Last-Modified'];
    if (isset($get['headers']['Content-Type']))
      $v['ct'] = $get['headers']['Content-Type'];
    if (isset($get['headers']['Content-Length']))
      $v['cl'] = $get['headers']['Content-Length'];
    $i = $this->getFilledTemplate($t, $v);

    # Insert into storage.
    $rr = $this->insert($i, $this->ns() . '#' . $gid);
    $rrr = $this->consolidateIFP($this->ns() . 'source');

    return array_merge($rrr, $r, array('t_count' => $newcount));
  }

  /*
    get
  */
  function get($url, $etag='', $lastModified='') {
    # @@@TODO: File system caching
    $cache_path = $this->v('scutter_cache_path', '.', $this->a);
    return $this->curlGet($url, $etag, $lastModified);
  }

  /*
    curlGet
  */
  function curlGet($url, $etag='', $lastModified='') {
    if (!function_exists('curl_init'))
      return $this->addError('Unable to get, no CURL!');
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, str_replace(' ', '%20', $url));
    curl_setopt($curl, CURLOPT_USERAGENT, 'CURL; ARC2::ScutterStorePlugin (' . @$_SERVER['HTTP_HOST']. ')');
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/rdf+xml;q=1.0,text/turtle;q=0.9,application/xml;q=0.8,*/*;q=0.1'));
    if ($lastModified)
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('If-Modified-Since: '.$lastModified));
    if ($etag)
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('If-None-Match: '.$etag));
    # Execute...
    $response = curl_exec($curl);
    if (curl_errno($curl))
      return sprintf('Unable to get %s with CURL:', $url).' '.curl_error($curl).curl_close($curl);
    curl_close($curl);
    if (!preg_match("|\r\n\r\n|", $response))
      return array('code'=>'502', 'headers'=>array(), 'body'=>'');
    do {
      list($headers, $response) = explode("\r\n\r\n", $response, 2);
      $lines = explode("\r\n", $headers);
      $line = array_shift($lines);
      if (preg_match('|^HTTP/[0-9]\.[0-9] ([0-9]{3})|', $line, $M))
        $code=$M[1];
      else
        $code='502';
    } while ('1'==substr($code, 0, 1));
    $headers = array();
    foreach ($lines as $line) {
      list($header, $value) = explode(': ', $line, 2);
      if ('Location'==$header)
        $headers[$header] = $this->resolve_url($url, $value);
      else
        $headers[$header] = $value;
    }
    return array('code'=>$code, 'headers'=>$headers, 'body'=>$response);
  }

  /*
    resolve_url
  */
  function resolve_url($base, $url) {
    if (!strlen($base)) return $url;
    // Step 2
    if (!strlen($url)) return $base;
    // Step 3
    if (preg_match('!^[a-z]+:!i', $url)) return $url;
    $base = parse_url($base);
    if ($url[0] == "#") { // DANNY {} -> []
      // Step 2 (fragment)
      $base['fragment'] = substr($url, 1);
      return $this->unparse_url($base);
    }
    unset($base['fragment']);
    unset($base['query']);
    if (substr($url, 0, 2) == "//") {
      // Step 4
      return $this->unparse_url(array(
          'scheme'=>$base['scheme'],
          'path'=>substr($url,2),));
    } else if ($url[0] == "/") {
      // Step 5
      $base['path'] = $url;
    } else {
      // Step 6
      $path = explode('/', $base['path']);
      $url_path = explode('/', $url);
      // Step 6a: drop file from base
      array_pop($path);
      // Step 6b, 6c, 6e: append url while removing "." and ".." from
      // the directory portion
      $end = array_pop($url_path);
      foreach ($url_path as $segment) {
        if ($segment == '.') {
          // skip
        } else if ($segment == '..' && $path && $path[sizeof($path)-1] != '..') {
          array_pop($path);
        } else {
          $path[] = $segment;
        }
      }
      // Step 6d, 6f: remove "." and ".." from file portion
      if ($end == '.') {
        $path[] = '';
      } else if ($end == '..' && $path && $path[sizeof($path)-1] != '..') {
        $path[sizeof($path)-1] = '';
      } else {
        $path[] = $end;
      }
      // Step 6h
      $base['path'] = join('/', $path);
    }
    // Step 7
    return $this->unparse_url($base);
  }

  /*
    unparse_url
  */
  function unparse_url($parsed) {
    if (!is_array($parsed)) return false;
    $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
    $uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
    $uri .= isset($parsed['host']) ? $parsed['host'] : '';
    $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
    if (isset($parsed['path'])) {
      $uri .= (substr($parsed['path'], 0, 1) == '/') ? $parsed['path'] : ('/'.$parsed['path']);
    }
    $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
    $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
    return $uri;
  }

}

?>
