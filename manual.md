# SparqlPress Manual

*work in progress*

TODO : shift installation notes here

## Functionality

**2021-04-28**

Menu items at the moment are mostly just to hook into little tests, except for  'SPARQL' which is more or less as it needs to be.

* SparqlPress - lists IDs/URLs of Posts
* DANBRI - reads https://danbri.org/foaf.rdf into the ARC2 store, runs a SPARQL query to list names
* SPARQL - SPARQL Endpoint Query UI
* Store Admin - this *will* offer some control

The actual SPARQL endpoint is at: 

http://*[host]*/*[site name]*/wp-json/sparqlpress/v1/sparql

This *should* be standards-compliant, supporting a variety of HTTP methods & SPARQL/RDF formats (not explored).

There is no real security in place yet, though (I think) standard WP admin rights will be needed to access the SparqlPress menus.


