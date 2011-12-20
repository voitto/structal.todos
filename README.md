Structal Todos
========================================================
Structal Todos is a simple real-time example app taken from Spine.js (www.spinejs.com). It extends Spine by adding a poll method that checks the server for changes. When the app is open in two windows, changes in one window can be seen in the other. 

Structal Todos utilizes the Structal toolset that includes the ORM library Mullet.php and the client-side MVC library Spine.js. Installation is a snap and only requires inputting a few database settings. Soon this step won't be necessary either as a database will automatically be generated for a user upon download.

Requirements
--------------------------------------------------------------------------------------------------
A Windows or Unix-based Web server with PHP version 5.2 or greater
A SQL database (MySQL, PostgreSQL, SQLite) or noSQL database (MongoDB, CouchDB).

Installing Structal Todos
--------------------------------------------------------------------------------------------------
1. Download Structal Todos from http://github.com/voitto/structal.todos
2. Place the folder onto the web server that is to display Structal Todos.
3. Enter the database settings for the web server into index.php. An example configuration is as follows:

$config = array(
  "",       // host name ('localhost' | '' | IP | name)
  'brian',  // db user name
  'dbPass',       // db user password
  'todos',   // db name
  5432,     // port number (3306/mysql | 5432/pgsql | 443/ssl)
  'pgsql'   // db type (mysql | pgsql | couchdb | mongodb | sqlite | remote)
);

4. Run the app! When the app is open in two windows, a change seen in one window should be seen in the other.

Bugs
--------------------------------------------------------------------------------------------------
Any help listing bugs at [Structal Todo's Issues Page](http://github.com/structal.todos/issues) is greatly appreciated!

More Info
--------------------------------------------------------------------------------------------------
[http://structal.org](http://structal.org)
#structal channel on [Freenode IRC](http://webchat.freenode.net)

