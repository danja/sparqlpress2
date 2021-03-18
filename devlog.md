# SparqlPress2 Development log.

```
sudo /opt/lampp/lampp start
tail -f /opt/lampp/logs/php_error_log

sudo rm -r /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress*

(sudo rm -r /opt/lampp/apps/sparqlpress)

cd ~/sparqlpress
./zip.sh
```

**2021-03-18**

Now on SparqlPress Information and Statistics,
```
Fatal error: Uncaught Error: Call to a member function escape() on null in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/arc/store/ARC2_Store.php:731
...
```

That appears to be a reference to a non-existent DB. Scutter?

Finding it hard to navigate through classes in Atom IDE so have shifted over to VS Code. PHP Intelephense extension was needed to get outline view.

in SparqlPress Options :

```
Fatal error: Uncaught Exception: Table bitnami_wordpress.wp_sparqlpress_scutter_setting doesn't exist query: SELECT val FROM wp_sparqlpress_scutter_setting WHERE k = bddc1130bbd1649841fcdcbef002e0cb in /home/danny/sparqlpress/sparqlpress2/arc/vendor/thingengineer/mysqli-database-class/MysqliDb.php:2002 
...
```

Temporarily disabled this chunk by scutter.php, line 34 (in function sparqlpress_scutter_init()) calls to :
```
 $sparqlpress->scutter = ARC2::getComponent('ScutterStorePlugin', array_merge($sparqlpress->store->a, $sparqlpress->options['scutter'], array('store_name' => $wpdb->prefix . 'sparqlpress_scutter')));
 ```

**2021-03-17**

Made some silly syntax errors last session - fixed.

SparqlPress -> UPDATE Options, now getting:
```
Fatal error: Uncaught Error: Class "ARC2_ScutterStorePlugin" not found in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/arc/ARC2.php:354
```

Found mortenf's : http://bzr.mfd-consult.dk/scutter-store/
```
ARC2_ScutterStorePlugin.php 2008-02-25 22:14   21K  
ARC2_SeeAlsosTrigger.php
```
but not available there, had to use archive.org.

downloaded to ~/sparqlpress2 dir, to scutter.php added

include 'ARC2_ScutterStorePlugin.php';

Now -
```
Deprecated: Required parameter $caller follows optional parameter $a in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/ARC2_ScutterStorePlugin.php on line 18

Deprecated: Required parameter $caller follows optional parameter $a in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/ARC2_ScutterStorePlugin.php on line 22

Fatal error: Array and string offset access syntax with curly braces is no longer supported in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/ARC2_ScutterStorePlugin.php on line 524

Notice: is_embed was called incorrectly. Conditional query tags do not work before the query is run. Before then, they always return false. Please see Debugging in WordPress for more information. (This message was added in version 3.1.0.) in /opt/lampp/apps/wordpress/htdocs/wp-includes/functions.php on line 5313

Notice: is_search was called incorrectly. Conditional query tags do not work before the query is run. Before then, they always return false. Please see Debugging in WordPress for more information. (This message was added in version 3.1.0.) in /opt/lampp/apps/wordpress/htdocs/wp-includes/functions.php on line 5313
```


https://php.watch/versions/8.0/deprecate-required-param-after-optional
**2021-03-15**

Updating Options, getting -
```Warning: Undefined array key "sparqlpress_endpoint_timeout" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/endpoint.php on line 94

Warning: Undefined array key "sparqlpress_scutter_active" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/scutter.php on line 137

Warning: Undefined array key "sparqlpress_scutter_ifps" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/scutter.php on line 137

Warning: Undefined array key "sparqlpress_scutter_cron_cycles_current" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress/scutter.php on line 137
```
Added checks of the form:
```
array_key_exists('sparqlpress_endpoint', $_POST)
```

**2021-03-06**

I am getting closer, piano, piano. ARC2 appears to be creating the triplestore in MySQL, but then not doing anything with it. Need to find the endpoint (SparqlPress gives 404).

Googling for any obvious related changes to ARC2, https://rhiaro.co.uk/2014/05/arc2-sparql is quite funny. But not the issue here. I guess I need to RTFM a bit more on ARC2.

Oh Benji I could scream.

https://github.com/semsol/arc2/wiki/SPARQL-Endpoint-Setup

All the info you need (in 2012) except where the endpoint will be.



**2021-03-03**

PHP Warning:  PHP Startup: Unable to load dynamic library 'imagick.so'

installed php-imagick

*Hah!*

Just saw something rather important in the ARC2 docs I'd missed before:

```
$store = ARC2::getStore($config);
// since version 2.3+
$store->createDBCon();
$store->setup();
```
so added these to store.php :
```
$sparqlpress->store->createDBCon();
$sparqlpress->store->setup();
```

**2021-03-02**

(nothing worth documenting the past few days, spinning tyres)

at http://localhost/phpmyadmin/db_sql.php?db=bitnami_wordpress

show variables like '%log_file%'

| Variable_name             | Value                  |
| ------------------------- | ---------------------- |
| aria_log_file_size        | 1073741824             |
| binlog_file_cache_size    | 16384                  |
| general_log_file          | danny-desktop.log      |
| innodb_log_file_size      | 5242880                |
| innodb_log_files_in_group | 2                      |
| slow_query_log_file       | danny-desktop-slow.log |

hmm, no sign of danny-desktop.log...

Warning: An unexpected error occurred. Something may be wrong with WordPress.org or this server’s configuration. If you continue to have problems, please try the support forums. (WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.) in /opt/lampp/apps/wordpress/htdocs/wp-includes/update.php on line 408

**2021-02-27**

It's going in a loop on unconfigured.

I thought it was a stupid DB connection problem until finding *errno = 0* is no error.


There appear to be a load of callbacks getting addressed by WordPress via its:
```
apply_filters( string $tag, mixed $value )
```

Which, by asking for scutter to be turned on, includes

http://bzr.mfd-consult.dk/scutter-store/

```
Fatal error: Uncaught Error: Class "ARC2_ScutterStorePlugin" not found in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/ARC2.php:356 Stack trace: #0 /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php(126): ARC2::getComponent('ScutterStorePlu...', Array) #1 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(287): sparqlpress_scutter_option_page_submit('') #2 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(311): WP_Hook->apply_filters(NULL, Array) #3 /opt/lampp/apps/wordpress/htdocs/wp-includes/plugin.php(484): WP_Hook->do_action(Array) #4 /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/sparqlpress.php(123): do_action('sparqlpress_opt...') #5 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(287): sparqlpress->option_page_handler('') #6 /opt/lampp/apps/wordpress/htdocs/wp-includes/class-wp-hook.php(311): WP_Hook->apply_filters('', Array) #7 /opt/lampp/apps/wordpress/htdocs/wp-includes/plugin.php(484): WP_Hook->do_action(Array) #8 /opt/lampp/apps/wordpress/htdocs/wp-admin/admin.php(259): do_action('settings_page_s...') #9 /opt/lampp/apps/wordpress/htdocs/wp-admin/options-general.php(10): require_once('/opt/lampp/apps...') #10 {main} thrown in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/arc/ARC2.php on line 356
```



**2021-02-23 **
```
Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 64

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 66

Warning: Undefined array key "sparqlpress_store_clear" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/store.php on line 68
```

in ```store.php, function sparqlpress_store_option_page_submit()``` :

added existent checks -

```
if (array_key_exists('sparqlpress_store', $_POST) && $_POST['sparqlpress_store']=='t' && !$sparqlpress->store->isSetUp())
  $sparqlpress->store->setUp();
```

ditto for:
```

Warning: Undefined array key "sparqlpress_scutter" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 123

Warning: Undefined array key "sparqlpress_store_reset" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 131

Warning: Undefined array key "sparqlpress_store_delete" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/scutter.php on line 133

Warning: Undefined array key "sparqlpress_debug" in /opt/lampp/apps/wordpress/htdocs/wp-content/plugins/sparqlpress-1/debug.php on line 46
```

```$sparqlpress->store->isSetup()``` is returning '0'

store is created in ```store.php:23``` with :

```$sparqlpress->store = ARC2::getStore($config);```




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
