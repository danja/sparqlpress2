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

