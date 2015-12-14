<?php
/**
 * @file
 * Contains Drupal\externalauth\ExternalAuth.
 */

namespace Drupal\externalauth;

use Drupal\Core\Entity\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\externalauth\Event\ExternalAuthEvents;
use Drupal\externalauth\Event\ExternalAuthRegisterEvent;
use Drupal\externalauth\Event\ExternalAuthAuthmapAlterEvent;

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
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @param EntityManagerInterface $entityManager
   * @param AuthmapInterface $authmap
   * @param LoggerInterface $logger
   * @param EventDispatcherInterface $eventDispatcher
   */
  public function __construct(EntityManagerInterface $entityManager, AuthmapInterface $authmap, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher) {
    $this->entityManager = $entityManager;
    $this->authmap = $authmap;
    $this->logger = $logger;
    $this->eventDispatcher = $eventDispatcher;
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
  public function register($authname, $provider) {
    $username = $provider . '_' . $authname;
    $authmap_event = $this->eventDispatcher->dispatch(ExternalAuthEvents::AUTHMAP_ALTER, new ExternalAuthAuthmapAlterEvent($provider, $authname, $username, NULL));
    $entity_storage = $this->entityManager->getStorage('user');
    $account = $entity_storage->create(
      array(
        'name' => $authmap_event->getUsername(),
        'init' => $authmap_event->getAuthname(),
        'status' => 1,
        'access' => (int) $_SERVER['REQUEST_TIME'],
      )
    );

    $account->enforceIsNew();
    $account->save();
    $this->authmap->save($account, $provider, $authmap_event->getAuthname(), $authmap_event->getData());
    $this->eventDispatcher->dispatch(ExternalAuthEvents::REGISTER, new ExternalAuthRegisterEvent($account, $provider, $authmap_event->getAuthname(), $authmap_event->getData()));
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
   * @inheritdoc
   *
   * @codeCoverageIgnore
   */
  public function userLoginFinalize($account) {
    user_login_finalize($account);
    $this->logger->notice('External login of user %name', array('%name' => $account->getAccountName()));
    return $account;
  }
}
