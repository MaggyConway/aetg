<?php

namespace Drupal\aetg_common\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AetgCommonEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new SmctCommonEventSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, AccountInterface $current_user, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => [['redirectToPrivateProfile', -100], ['checkUserFields']],
    ];
  }

  /**
   *  Redirect the member/school user to their private profile if they don't have a public profile.
   */
  public function redirectToPrivateProfile(GetResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    /** @var UserInterface $user */
    $user = $this->routeMatch->getParameter('user');

    if ($route_name === 'entity.user.canonical'
      && $user instanceof UserInterface
      && ($this->currentUser->id() === $user->id() || in_array('administrator', $this->currentUser->getRoles()))
      && !empty(array_intersect($user->getRoles(), ['asociado', 'escuela']))
    ) {

      /** @var \Drupal\profile\ProfileStorageInterface $profile_storage */
      $profile_storage = $this->entityTypeManager->getStorage('profile');
      $profile_type = $user->hasRole('asociado') ? 'informacion_profesional' : 'school_professional_information';
      $public_profile = $profile_storage->loadByUser($user, $profile_type);

      if (empty($public_profile)) {
        $event->setResponse(new RedirectResponse(
          Url::fromRoute('view.profiles.page_1', ['user' => $user->id()])->toString()
        ));
      }
    }
  }

  /**
   * Check User fields.
   */
  public function checkUserFields(GetResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    $current_path = Url::fromRoute('<current>')->toString();
    if ($this->currentUser->isAnonymous() || in_array($route_name, ['entity.user.edit_form', 'user.logout', 'social_auth_google.redirect_to_google', 'social_auth_google.callback']) || strpos($current_path, '/admin/') === 0) {
      return;
    }

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->entityTypeManager->getStorage('user')
      ->load($this->currentUser->id());

    if ($user->hasRole('administrator') || $user->hasRole('personal_aetg')) {
      return;
    }

    if ($user->hasField('field_full_name') && $user->get('field_full_name')->isEmpty()) {
      $this->messenger->addError(t('Please, fill in the required fields.'));

      $event->setResponse(new RedirectResponse(
        Url::fromRoute('entity.user.edit_form', ['user' => $user->id()],
          ['query' => ['display' => 'edit_account', 'destination' => $current_path]])->toString()));
    }
  }

}
