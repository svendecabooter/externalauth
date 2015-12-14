Description:
============

The ExternalAuth module provides a generic service for logging in and registering users that are authenticated against
an external site or service and storing the authentication details.
It is the Drupal 8 equivalent of user_external_login_register() and related functions, as well as the authmap table in
Drupal 7 core.

Usage:
======

Install this module if it's required as dependency for an external authentication Drupal module.
Module authors that provide external authentication methods can use this helper service to provide a consistent API for
storing and retrieving external authentication data.
