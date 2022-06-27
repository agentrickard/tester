Tester
===

Tester is a Drupal module that provides drush commands for spidering
your site. The purpose is to automatically load URLs in order to track PHP version errors.

## Use case

Suppose we have a Drupal 9.3 site running on PHP 7.4. We would like to do the following:

* Confirm that it will run without error on PHP 8.1.

While code analysis tools like [Drupal check](https://github.com/mglaman/drupal-check) can help review code for errors, we would also like to ensure that pages load as expected without PHP errors, warnings, or notices.

# Usage

* Download and install the module with composer or `git clone git@github.com:agentickard/tester.git`
* `drush en tester`
* Run a test
* `drush tester-crawl http://example.com`
* Note that you must supply the URL to the site root. No trailing slash is required.

The command is aliased to `tc`, so this works as well:

* `drush tc http://example.com`

# Coverage

The module comes with four sets of base tests:

* Crawl the home page, a 403 page, and a 404 page.
* Crawl all node pages.
* Crawl all internal menu links that do not require arguments.
* Crawl all user pages.

## History

This module was originally written for Drupal 7 during the PHP 7 conversion cycle. The original code is in the /drupal7 folder.
