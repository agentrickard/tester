# PHP Error Generator

This module is a development tool for creating known errors so that we can develop and test the main `tester` module.

It should never be used in production.

## Error generation

We use the list from [PHP Error Constants](https://www.php.net/manual/en/errorfunc.constants.php), and only can only test for non-fatal errors.

* E_WARNING
* E_NOTICE
* E_RECOVERABLE_ERROR
* E_DEPRECATED
