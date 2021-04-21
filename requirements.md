# SparqlPress2 Requirements

*brain dump for now*

## Target Audience

* Any WP users
  * SEO
* Geeks

## Goal

* Increase a WP site's Web 'surface area' by exposing site data in RDF
* Provide a SPARQL endpoint
* Augment data
  * scutter
  * backlinks

## Infrastructure

* Implemented as WP plugin
* Includes ARC2

## Use Cases

```
    // Data Generation, using WP info
    .... include_once('foaf/skos/sioc');

    // Data Storage, infrastructure (read)
    include_once('store.php');
    include_once('endpoint.php');

    // Data Storage, infrastructure (write)
    include_once('scutter.php');

    // Data Linking, administration of linking
    include_once('linking/accounts.php');
    include_once('linking/grouping.php');
    include_once('linking/gsg.php');
    include_once('linking/rapleaf.php');
    include_once('linking/sha1sum.php');
    include_once('linking/me.php');
    include_once('linking/accounts-as-homepages.php');

    // Data Usage, use of infrastructure and linking
    include_once('widgets/foafnaut-html.php');
    include_once('widgets/triplecount.php');
    include_once('display/comments.php');
    ```

**Scanning**

Grab all the regular metadata, plus :
* URLs in content
  * (ScutterPlus these?)

**ScutterPlus**

The original SparqlPress included a scutter (RDF spider), presumably supported following links and grabbing RDF there. This shouldn't be difficut to implement in a basic form. Potentially more useful would be to follow links in content and pull out metadata from HTTP/HTML (page title, date, author etc), add this to store.

### Admin

* Init ARC2 Store

#### Options

* include content

#### Buttons

* Scan/Update Content 
   * copy metadata to store 
* Backup ARC2 data
* Download ARC2 data


### Regular Operation

* View Post metadata
* Extra search box (simple regex?)
* On Post
  * copy metadata to store

