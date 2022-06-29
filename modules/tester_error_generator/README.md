# PHP Error Generator

This module is a development tool for creating known errors so that we can develop and test the main `tester` module.

It should never be used in production.

## Error generation

We use the list from [trigger_error()](https://www.php.net/manual/en/function.trigger-error.php), and only can only test E_USER errors, which is sufficient for development of the Drush command.

* E_USER_WARNING
* E_USER_NOTICE
* E_USER_DEPRECATED
