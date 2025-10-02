# DEPRECATED REPOSITORY
This project is now on drupal.org
https://www.drupal.org/project/tester

**This repository will be archived on JAN 1, 2026.**

Update your composer configuration:

```
composer require 'drupal/tester:^1.0'
```

Tester
===

Tester is a Drupal module that provides drush commands for spidering
your site. The purpose is to automatically load URLs in order to track PHP version errors.

## Use case

Suppose we have a Drupal 9.3 site running on PHP 7.4. We would like to do the following:

* Confirm that it will run without error on PHP 8.2.

While code analysis tools like [Drupal check](https://github.com/mglaman/drupal-check) can help review code for errors, we would also like to ensure that pages load as expected without PHP errors, warnings, or notices.

# Usage

* Download and install the module with composer or `git clone git@github.com:agentickard/tester.git`
* `drush en tester`
* Run a test
* `drush tester-crawl http://example.com`
* Note that you may supply the URL to the site root. No trailing slash is required.
  * If you do not supply a URL, the default `$base_url` value will be used.
  * Note that local sites (such as DDEV) may not support HTTPS using cURL due to invalid certificates. By default, tester will run using HTTP. To force HTTPS, pass the `--verify` flag.
* If user 1 is active on the site, the `--admin` flag will log the tester in as user 1 using a one-time login link.
* You may instead pass a `--user` and `--password` combo to login.
* You may pass the `--errors` flag to only return errors instead of the full crawl list.
* For more, run `drush help tc`.

The command is aliased to `tc`, so this works as well:

* `drush tc http://example.com`

# Coverage

The module comes with four sets of base tests:

* Crawl the home page, a 403 page, and a 404 page.
* Crawl all node pages and node/add pages
* Crawl all internal menu links that do not require arguments or tokens.
* Crawl all user pages.

## History

This module was originally written for Drupal 7 during the PHP 7 conversion cycle. The original code is in the /drupal7 folder.
