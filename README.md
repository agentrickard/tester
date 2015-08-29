Tester
===

Tester is a drop-in Drupal 7 plugin that provides drush commands for spidering
your site.

It is not a module. It should be installed as a global drush extension.

Drush searches for commandfiles in the following locations:

-   The "/path/to/drush/commands" folder.
-   Folders listed in the 'include' option (see `drush topic docs-configuration`).
-   The system-wide drush commands folder, e.g. /usr/share/drush/commands
-   The ".drush" folder in the user's HOME folder.
-   /drush and /sites/all/drush in the current Drupal installation
-   All enabled modules in the current Drupal installation

The purpose is to automatically load URLs in order to track PHP version errors.

# Usage

* Download and install
* `drush dl tester` or `git clone git@github.com:palantirnet/tester.git`
* `cp -R tester ~/.drush`
* Run a test
* `drush tester-crawl http://example.com`
* Note that you must supply the URL to the site root. No trailing slash is
required.

The command is aliased to `tc`, so this works as well:

* `drush tc http://example.com`

# Coverage

The module comes with four sets of base tests:

* Crawl the home page and a 404 page.
* Crawl all node pages.
* Crawl all internal menu links that do not require arguments.
* Crawl all user pages.
