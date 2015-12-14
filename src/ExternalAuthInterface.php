<?php

/**
 * @file
 * Contains Drupal\externalauth\ExternalAuthInterface.
 */

namespace Drupal\externalauth;

/**
 * Interface ExternalAuthInterface.
 *
 * @package Drupal\externalauth
 */
interface ExternalAuthInterface {

  /**
   * Load a Drupal user based on an external authname.
   * D7 equivalent: user_external_load().
   *
   * @param string $authname
   *   The unique, external authentication name provided by authentication provider.
   * @param $provider
   *   The module providing external authentication.
   * @return \Drupal\user\UserInterface
   */
  public function load($authname, $provider);

  /**
   * Log a Drupal user in based on an external authname.
   *
   * @param string $authname
   *   The unique, external authentication name provided by authentication provider.
   * @param $provider
   *   The module providing external authentication.
   * @return \Drupal\user\UserInterface|bool
   */
  public function login($authname, $provider);

  /**
   * Register a Drupal user based on an external authname.
   *
   * @param string $authname
   *   The unique, external authentication name provided by authentication provider.
   * @param string $provider
   *   The module providing external authentication.
   * @param string $username
   *   Optionally provide a username. Make sure it is unique. If not provided,
   *   the username will default to <provider>_<authname>
   * @return \Drupal\user\UserInterface
   */
  public function register($authname, $provider, $username = NULL);

  /**
   * Login, and optionally register, a Drupal user based on an external authname.
   *
   * @param string $authname
   *   The unique, external authentication name provided by authentication provider.
   * @param $provider
   *   The module providing external authentication.
   * @return \Drupal\user\UserInterface
   */
  public function loginRegister($authname, $provider);
}
