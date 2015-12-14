<?php
/**
 * @file
 * Contains Drupal\externalauth\ExternalAuth.
 */

namespace Drupal\externalauth;

use Drupal\Core\Entity\EntityManagerInterface;
use Psr\Log\LoggerInterface;
/**
 * Class ExternalAuth.
 *
 * @package Drupal\externalauth
 */
class ExternalAuth implements ExternalAuthInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The authmap service.
   *
   * @var \Drupal\externalauth\AuthmapInterface
   */
  protected $authmap;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @param EntityManagerInterface $entityManager
   * @param AuthmapInterface $authmap
   * @param LoggerInterface $logger
   */
  public function __construct(EntityManagerInterface $entityManager, AuthmapInterface $authmap, LoggerInterface $logger) {
    $this->entityManager = $entityManager;
    $this->authmap = $authmap;
    $this->logger = $logger;
  }

  /**
   * @inheritdoc
   */
  public function load($authname, $provider) {
    if ($uid = $this->authmap->getUid($authname, $provider)) {
      return $this->entityManager->getStorage('user')->load($uid);
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function login($authname, $provider) {
    $account = $this->load($authname, $provider);
    if ($account) {
      return $this->userLoginFinalize($account);
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function register($authname, $provider, $username = NULL) {
    if (!$username) {
      $username = $provider . '_' . $authname;
    }
    $entity_storage = $this->entityManager->getStorage('user');
    $account = $entity_storage->create(
      array(
        'name' => $username,
        'init' => $username,
        'status' => 1,
        'access' => (int) $_SERVER['REQUEST_TIME'],
      )
    );

    $account->enforceIsNew();
    $account->save();
    $data = NULL;
    // @TODO: allow altering of data
    $this->authmap->save($account, $provider, $authname, $data);
    $this->logger->notice('External registration of user %name from provider %provider and authname %authname', array('%name' => $account->getAccountName(), '%provider' => $provider, '%authname' => $authname));

    return $account;
  }

  /**
   * @inheritdocs
   */
  public function loginRegister($authname, $provider) {
    $account = $this->login($authname, $provider);
    if (!$account) {
      $account = $this->register($authname, $provider);
      return $this->userLoginFinalize($account);
    }
    return $account;
  }

  /**
   * Finalize logging in the external user.
   * Encapsulates user_login_finalize.
   *
   * @param UserInterface $account
   * @return UserInterface
   *
   * @codeCoverageIgnore
   */
  protected function userLoginFinalize($account) {
    user_login_finalize($account);
    $this->logger->notice('External login of user %name', array('%name' => $account->getAccountName()));
    return $account;
  }
}
