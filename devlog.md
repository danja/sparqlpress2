# SparqlPress2 Development log.

```
sudo /opt/lampp/lampp start
tail -f /opt/lampp/logs/php_error_log

sudo rm -r /opt/lampp/apps/sparqlpress
sudo rm -r /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1

cd ~/sparqlpress
./zip.sh
```

**2021-02-18**

Oooh, unexpected - on fresh startup, reloading gave this - note line after warnings:


```
Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 61

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 63

Warning: Undefined array key "sparqlpress_store_clear" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 65

SparqlPress is now ready to go! You can check the status of SparqlPress on the SparqlPress Information Page.


Fatal error: Uncaught Error: Class "ARC2_ScutterStorePlugin" not found in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/ARC2.php:354 Stack trace: #0 /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php(126): ARC2::getComponent('ScutterStorePlu...', Array) #1 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(287): sparqlpress_scutter_option_page_submit('') #2 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(311): WP_Hook->apply_filters(NULL, Array) #3 /opt/lampp/apps/wordpress/htdocs/wp-includes/plugin.php(484): WP_Hook->do_action(Array) #4 /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/sparqlpress.php(123): do_action('sparqlpress_opt...') #5 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(287): sparqlpress->option_page_handler('') #6 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(311): WP_Hook->apply_filters('', Array) #7 /opt/lampp/apps/wordpress/htdocs/wp-includes/plugin.php(484): WP_Hook->do_action(Array) #8 /opt/lampp/apps/wordpress/htdocs/wp-admin/admin.php(259): do_action('settings_page_s...') #9 /opt/lampp/apps/wordpress/htdocs/wp-admin/options-general.php(10): require_once('/opt/lampp/apps...') #10 {main} thrown in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/ARC2.php on line 354

```

Oh. But that takes me back to "The SparqlPress store has not been configured or isn't working correctly."

A quick google later, found a blog post by Benjamin Nowack that mentioned ARC2 plugins (and the "This Week's Semantic Web" blog I used to do for Talis!).

http://bnode.org/blog/2008/03/31/new-arc2-plugins

took me to -

https://web.archive.org/web/20110308145833/http://arc.semsol.org/download/plugins

----

Just occurred to me - 'set up scutter' is an checkbox on the initial SparqlPress page.
Retrying without that checked...

Progress!

Page starts with:
```
Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 61

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 63

Warning: Undefined array key "sparqlpress_store_clear" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 65

Warning: Undefined array key "sparqlpress_scutter" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 123

Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 131

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 133
```

![Screenshot](            Screenshot from 2021-02-18 22-44-04.png)

I checked all SPARQL Write features.

Took me back to the SparqlPress config pages, with the previous stuff preceded by:

```
Warning: Undefined array key "sparqlpress_store" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 59

Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 61

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 63

Warning: Undefined array key "sparqlpress_store_clear" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 65

```
**SparqlPress options were updated.**
```
Warning: Undefined array key "sparqlpress_endpoint" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/endpoint.php on line 80

Warning: Undefined array key "sparqlpress_endpoint_timeout" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/endpoint.php on line 89

Warning: Undefined array key "sparqlpress_scutter" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 123

Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 131

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 133

Warning: Undefined array key "sparqlpress_scutter_active" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 137

Warning: Undefined array key "sparqlpress_scutter_ifps" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 137

Warning: Undefined array key "sparqlpress_debug" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/debug.php on line 46
```

Checking with phpmyadmin, 6 new tables called wp_sparqlpress_* have been created. **Yay!**

store.php line 58 added

```
print_r($_POST);
```

re-installed plugin,

http://localhost/wordpress/wp-admin/options-general.php?page=sparqlpress.php

produces this at top:

```
Array ( [_wpnonce] => 94e0a048d8 [_wp_http_referer] => /wordpress/wp-admin/options-general.php?page=sparqlpress.php [sparqlpress_action] => create [sparqlpress_store] => t [Submit] => Start using SparqlPress! [sparqlpress_endpoint] => t ) localhost:3306
bn_wordpress
```

**So, looks like another load of bits to chip away at**

note...
array_key_exists($key, $table)



**2021-02-15** [afternoon]

ARC2 appears to have added/changed it's (PHP) namespace setup.

Had to add
```
use MysqliDb;
```
to ```MysqliDbExtended```

see https://www.php.net/manual/en/language.namespaces.importing.php

Next :
```
PHP Warning:  mysqli::__construct(): (HY000/1045): Access denied for user 'user'@'localhost' (using password: YES) in /home/danny/sparqlpress/sparqlpress2/arc/vendor/thingengineer/mysqli-database-class/MysqliDb.php on line 323
```

So added global permissions to ```user:secret``` with phpmyadmin.

Still no joy...

popped this into ```MysqliDb.php```:
```
foreach ($params as $pms) {
  echo $pms . '<br>';
}
```
which showed:
```
localhost
user
secret
```

```
/opt/lampp/apps/wordpress/htdocs/wp-content/plugins$ sudo rm -r  sparqlpress
```




**2021-02-15** [morning]

Fatal error: Uncaught Error: Class "ARC2\Store\Adapter\AbstractAdapter" not found in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/src/ARC2/Store/Adapter/mysqliAdapter.php:17

added ```ide-html``` and ```atom-beautify``` to Atom to simplify testing

```http://localhost/sparqlpress/play```

->

```/opt/lampp/htdocs/sparqlpress/play```

->

```~/sparqlpress/sparqlpress2/play/index.html```

[[
previously :
```
cd /opt/lampp/htdocs
sudo ln -s ~/sparqlpress/sparqlpress2 sparqlpress
```
]]

Grr, logging not working, tweaked

```
/opt/lampp/apps/wordpress/htdocs/wp-config.php

@ini_set ('display_errors', 0);
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_LOG', '/home/danny/sparqlpress/wp-errors.log' );
define( 'WP_DEBUG_DISPLAY', true );
```

```
sudo /opt/lampp/lampp stop
sudo /opt/lampp/lampp start
```
Ok, finally! found,

```
/opt/lampp/logs/php_error_log
```

note also XAMPP GUI -

```
sudo /opt/lampp/manager-linux-x64.run
```

The default method of ARC2 install now uses composer. To install manually (as in the plugin) have to add files & includes.

trying ```http://localhost/sparqlpress/play/arctest.php```

Created db & user using phpmyadmin

CREATE USER 'user'@'%' IDENTIFIED VIA mysql_native_password USING '***';GRANT USAGE ON *.* TO 'user'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;GRANT ALL PRIVILEGES ON `arc_test_db`.* TO 'user'@'%';

opening ```http://localhost/sparqlpress/play/arctest.php```
 getting :
```
PHP Parse error:  syntax error, unexpected string content "", expecting "-" or identifier or variable or number in /home/danny/sparqlpress/sparqlpress2/play/arctest.php on line 42
```



**2021-02-11**

Starting with:

```
Fatal error: Array and string offset access syntax with curly braces is no longer supported in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/pear/JSON.php on line 156
```

pear install Services_JSON

Notice: Trying to access array offset on value of type bool in PEAR/REST.php on line 187
PHP Notice:  Trying to access array offset on value of type bool in /usr/share/php/PEAR/REST.php on line 187

mkdir -p /tmp/pear/cache

pear install Services_JSON

that appeared to work, but JSON.php wasn't visible. It was the only file in the pear dir so I downloaded what appeared to be the latest version, manually placed at pear/JSON.php :

https://pear.php.net/package/Services_JSON/download

Still get same error, so went through JSON.php and changed all the eg. a${1} to a$[1].

Plugin now gets further, but now get two series of errors of the forms:

```
Deprecated: register_sidebar_widget is deprecated since version 2.8.0! Use wp_register_sidebar_widget() instead. in /opt/lampp/apps/wordpress/htdocs/wp-includes/functions.php on line 4859
...
Warning: Undefined array key "endpoint" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/endpoint.php on line 13
```

So...

```
~/sparqlpress$ grep -r register_sidebar_widget
```

Oh, cool, Atom IDE does search/replace in folders.

Now bunch of errors of the form:
```
Fatal error: Uncaught ArgumentCountError: Too few arguments to function wp_register_sidebar_widget(), 2 passed in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/widgets/triplecount.php on line 86 and at least 3 expected...
```
modifying:

```
wp_register_sidebar_widget('TripleCount', 'widget_sparqlpress_triplecount');
->
wp_register_sidebar_widget('TripleCount_sidebar_id', 'TripleCount', 'widget_sparqlpress_triplecount');
```
assuming the same ID needs to be used eg. wp_register_sidebar_widget & wp_register_widget_control

```
wp_register_sidebar_widget('FOAFNaut_sidebar_id', 'FOAFNaut', 'widget_sparqlpress_foafnaut_html');
wp_register_widget_control('FOAFNaut_sidebar_id', 'FOAFNaut', 'widget_sparqlpress_foafnaut_html_control', 460, 615);
```

Now  :
```
Warning: Undefined array key "endpoint" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/endpoint.php on line 13

Warning: Undefined array key "scutter" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/scutter.php on line 11

Warning: Undefined array key "display_comments" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/display/comments.php on line 12

Warning: Undefined array key "debug" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/debug.php on line 13
```

```
// if (!is_array($sparqlpress->options['endpoint']))
if (!array_key_exists('endpoint', $sparqlpress->options))

// if (!is_array($sparqlpress->options['scutter']))
if (!array_key_exists('scutter', $sparqlpress->options))

// if (!is_array($sparqlpress->options['display_comments']))
if (!array_key_exists('display_comments', $sparqlpress->options))

//  if (!is_array($sparqlpress->options['debug']))
if (!array_key_exists('debug', $sparqlpress->options))  
```

Ok, that all appears to have worked.

Now the plugin is getting to 'Start Using SparqlPress'.

Now getting:
```
Fatal error: Uncaught Error: Class "ARC2\Store\Adapter\AbstractAdapter" not found in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/src/ARC2/Store/Adapter/mysqliAdapter.php:17
...
```


**2021-02-07**

Wordpress does recognise a zip of the SparqlPress files as a plugin. When trying to install, it gives a critical error.

http://localhost/wordpress/wp-admin/plugin-install.php

Ah, turn on debugging -
```
sudo gedit /opt/lampp/apps/wordpress/htdocs/wp-config.php

// changed:
// define( 'WP_DEBUG', false );
define( 'WP_DEBUG', true );
// added:
define( 'WP_DEBUG_LOG', '/home/danny/sparqlpress/wp-errors.log' );

```
https://wordpress.org/support/article/debugging-in-wordpress/

**Ooh, useful!!**

```
Warning: include_once(arc/ARC2.php): Failed to open stream: No such file or directory in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/sparqlpress.php on line 36
```
Line 36:
```
include_once('arc/ARC2.php');
```

ok, arc2 install moved, next error:
```
Fatal error: Array and string offset access syntax with curly braces is no longer supported in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/pear/JSON.php on line 156
```

Hmm. pear is/was the packaging thing, no? (pre-composer). Looks like updates needed around there...

----

**2021-02-06** Just started. Set up this repo.

Rather than attempting to get the previous version working with the 2010 setup, (and risk a mess of redundant packages) I've gone ahead and installed the 2021 target environment (Apache2, MariaDB, WordPress, ARC2 - see [install.md](install.md)).

Docs for the original version are minimal, so I guess I'll start by fumbling around...

I'd already got the [Atom IDE](https://atom.io/) installed, so have added ide-php package.

To have a look around I did :
```
cd /opt/lampp/htdocs
sudo ln -s ~/sparqlpress/sparqlpress2 sparqlpress
```
