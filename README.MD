HOW TO SETUP
---------------------

 * Install Lando CLI  by following the commands on https://docs.lando.dev/basics/installation.html#system-requirements
 * Once done, go into the root directory of the project to  run `lando start` to create docker containers
 * Once finished locate the database and run `lando db-import drupal8_test.sql.zip` to get current instance of the database
 * locate the url for the site, if not found type in `lando info` and look under urls
 * username:password  = test:test



Things Done
---------------------
* Drupal 8 Project created using composer
* Customer content  type created containing fields balance, customer name, id
* View created that contains a page to show list of customers  `/customerlists/`and a page that filters by the customer id, i.e. a customer with id=1 will be shown at `/customerlists/1`
* Rest UI installed using composer and enabled, RestFul web services enabled, serialization enabled. RestUi provides a convenient interface to enable REST ENDPOINTS. REST enable for content to PUT, PATCH, DELETE, GET, POST
* Feeds installed using composer, Feed type created which allows csv files to be parsed on every 1hour cron run for customer content type.
