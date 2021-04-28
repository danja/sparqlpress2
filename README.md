# SparqlPress2
SPARQL Tools for Wordpress

Wordpress, [ARC2](https://github.com/semsol/arc2) and even PHP have changed significanctly since the original [SparqlPress](http://bzr.mfd-consult.dk/sparqlpress/), not to mention the Web itself. Because of this, SparqlPress2 is being rebuilt from the ground up.

### Installation

SparqlPress is built as a standard Wordpress plugin, in zip format. 

**NOTE : although there is little likelihood of the current version causing any damage to an existing system, to try it out a disposable Wordpress installation is recommended. Some new database tables are created that aren't removed on uninstall, cruft will be left behind.**

There are some notes on installing Wordpress from scratch in [install.md](install.md).

#### To install on an existing Wordpress installation :

* Download the file [sparqlpress.zip](https://github.com/danja/sparqlpress2/blob/main/sparqlpress.zip) to a temporary location

* Navigate to the **Admin Dashboard** (http://*[host]*/*[name]*/wp-admin/index.php)

* In the left-hand menu you should see an entry **Plugins**, click this

* Click **Add New**

* Click **Upload Plugin**

* Click **Choose File** and select the downloaded [sparqlpress.zip](https://github.com/danja/sparqlpress2/blob/main/sparqlpress.zip) 

* Click **Install Now**

Possibly after a short delay, you should see a message *Plugin installed successfully.*

* Click **Activate Plugin**

A new menu item **SparqlPress** should now have appeared.

What is does will be documented in the [manual](manual.md).


#### Uninstalling

SparqlPress can be installed from the Wordpress Dashboard by selecting **Plugins**, scrolling to the Sparqlpress entry and clicking **Deactivate**, then once deactivated clicking **Delete**.

An alternative is deleting the associate files, eg.

```sudo rm -r /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress*```

For a full clean-up, you will also need to DROP the database tables created by SparqlPress using an SQL client such as mysqladmin (command line) or phpMyAdmin (over web). 

### Status 2021-04-28

It doesn't do anything useful yet.

See [devlog.md](devlog.md) for *verbose* details 

I've made rough start on [requirements](requirements.md).

----

### Legacy Notes

*The goal for SparqlPress is easy-to-use, low-barrier-of-entry, access to the linked data web. There are two, intimately-related sides to the idea: producing data, and consuming it. One goal is to make it easy for Wordpress to expose more data in SPARQL-friendly form. Another is to make it easier to use a Wordpress installation as a personal, perhaps even private, local aggregation of such data.* 

...

*SparqlPress explores the addition of an RDF store to the Wordpress weblogging system through PHP-based extensions, providing a basic Personal Semantic Web Aggregator that can integrate interesting data from nearby in the Web, exposing it to local and remote applications via the SPARQL query language and protocol. The primary goal is to populate the local store with an interesting subset of the nearby Semantic Web, through discovery and crawling of RDF data from the websites (typically blogs; initially Wordpress blogs running the FOAF/SKOS plugin).*

- [danbri](https://danbri.org/), [2008](http://www.semanlink.net/tag/sparqlpress.html)

danbri 2021 : "It would be cool to get SparqlPress updated to work with 2021 Wordpress and ARC2 code"

* [Original project Wiki notes](http://wiki.foaf-project.org/SparqlPress)
