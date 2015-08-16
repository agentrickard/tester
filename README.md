Tester
===

Tester is a drop-in Drupal 7 module that provides drush commands for spidering your site.

The purpose is to automatically load URLs in order to track PHP version errors.

# Usage

* Download and install
* `drush en tester -y`
* Run a test
* `drush tester-crawl http://example.com`
* Note that you must supply the URL to the site root. No trailing slash is required.

The command is aliased to `tc`, so this works as well:

* `drush tc http://example.com`

# Coverage

The module comes with four sets of base tests:

* Crawl the home page and a 404 page.
* Crawl all node pages.
* Crawl all internal menu links that do not require arguments.
* Crawl all user pages.
