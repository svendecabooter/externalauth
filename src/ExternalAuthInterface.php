<?php

/**
 * @file
 * Contains Drupal\externalauth\ExternalAuthInterface.
 */

namespace Drupal\externalauth;

use Drupal\user\UserInterface;

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
   * @return \Drupal\user\UserInterface
   */
  public function register($authname, $provider);

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

  /**
   * Finalize logging in the external user.
   * Encapsulates user_login_finalize.
   *
   * @param \Drupal\user\UserInterface $account
   * @return \Drupal\user\UserInterface
   *
   * @codeCoverageIgnore
   */
  public function userLoginFinalize(UserInterface $account);

  /**
   * Link a pre-existing Drupal user to a given authname
   *
   * @param string $authname
   *   The unique, external authentication name provided by authentication provider.
   * @param string $provider
   *   The module providing external authentication.
   * @param \Drupal\user\UserInterface $account
   */
  public function linkExistingAccount($authname, $provider, UserInterface $account);

}
