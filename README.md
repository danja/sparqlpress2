# SparqlPress2
SPARQL tools for Wordpress

Attempting update of [SparqlPress](http://bzr.mfd-consult.dk/sparqlpress/) for [ARC2](https://github.com/semsol/arc2).

### Status 

See [devlog.md](devlog.md) for *verbose* details (environment setup will be in [install.md](install.md)).

**2021-03-30**

Wordpress, ARC2 and even PHP itself have changed quite a lot since the original SparqlPress. 

I spent a fair amount of time trying to modify the old code to match the new environment. This started to feel very inefficient - especially since I'm not particularly familiar with Wordpress, ARC2 or even PHP...

Now going in the other direction, starting from (working) Wordpress plugin & ARC2, thinking I'll use the original SparqlPress code more as a reference.

In this direction, so far I've got a 'Hello World!'-ish WP plugin working, and a basic ARC2 test (loads a FOAF file from the Web into a triplestore, queries it with SPARQL, displays the results).

----

*The goal for SparqlPress is easy-to-use, low-barrier-of-entry, access to the linked data web. There are two, intimately-related sides to the idea: producing data, and consuming it. One goal is to make it easy for Wordpress to expose more data in SPARQL-friendly form. Another is to make it easier to use a Wordpress installation as a personal, perhaps even private, local aggregation of such data.* 

...

*SparqlPress explores the addition of an RDF store to the Wordpress weblogging system through PHP-based extensions, providing a basic Personal Semantic Web Aggregator that can integrate interesting data from nearby in the Web, exposing it to local and remote applications via the SPARQL query language and protocol. The primary goal is to populate the local store with an interesting subset of the nearby Semantic Web, through discovery and crawling of RDF data from the websites (typically blogs; initially Wordpress blogs running the FOAF/SKOS plugin).*

- [danbri](https://danbri.org/), [2008](http://www.semanlink.net/tag/sparqlpress.html)

danbri 2021 : "It would be cool to get SparqlPress updated to work with 2021 Wordpress and ARC2 code"

* [Original project Wiki notes](http://wiki.foaf-project.org/SparqlPress)
