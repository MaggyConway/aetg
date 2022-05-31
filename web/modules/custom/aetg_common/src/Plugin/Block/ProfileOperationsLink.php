<?php

namespace Drupal\aetg_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\UserInterface;
use Drupal\webform\Entity\Webform;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Profile operations link block.
 *
 * @Block(
 *   id = "aetg_profile_operations_link",
 *   admin_label = @Translation("AETG Profile operations links"),
 *   category = @Translation("Aetg")
 * )
 */
class ProfileOperationsLink extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The current user.
   *
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'profile_type' => $this->t('public'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['profile_type'] = [
      '#type' => 'select',
      '#title' => t('Profile type'),
      '#default_value' => $this->configuration['profile_type'],
      '#options' => [
        'public' => t('public professional information'),
        'private' => t('private profile'),
      ],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['profile_type']
      = $form_state->getValue('profile_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $build = [];
    $profile_type_by_role = [
      'asociado' => [
        'public' => 'informacion_profesional',
        'private' => 'informacion_privada',
      ],
      'escuela' => [
        'public' => 'school_professional_information',
        'private' => 'escuela',
        ],
      ];

    if ($this->routeMatch->getRouteName() == 'views.ajax') {
      $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');
      $fake_request = Request::create($previousUrl);
      $url_object = \Drupal::service('path.validator')->getUrlIfValid($fake_request->getRequestUri());
      if ($url_object instanceof Url) {
        $user = $url_object->getRouteParameters();
        $user = $user['user'] ?? NULL;
        $user = $user instanceof UserInterface ? $user : $this->entityTypeManager->getStorage('user')->load($user);
      }
    }
    elseif ($user = $this->routeMatch->getParameter('user')) {
      $user = $user instanceof UserInterface ? $user : $this->entityTypeManager->getStorage('user')->load($user);
    }

    if (empty($user) && $this->account->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->account->id());
    }

    if ($user instanceof UserInterface) {
      // Get profile storage to use ProfileStorage methods.
      /** @var \Drupal\profile\ProfileStorageInterface $profile_storage */
      $profile_storage = $this->entityTypeManager->getStorage('profile');

      $profile_type = $this->configuration['profile_type'];
      $user_role = NULL;
      foreach ($profile_type_by_role as $key => $value) {
        if ($user->hasRole($key)) {
          $user_role = $key;
          break;
        }
      }

      if (empty($user_role)) {

        // If the user is a basic user and they have already sent private profile
        foreach (array_column($profile_type_by_role, 'public') as $public_profile_type) {
          /** @var ProfileInterface $user_profile */
          if ($profile_storage->loadByUser($user, $public_profile_type) instanceof ProfileInterface) {
            return [];
          }
        }

        // If the user is a basic user without a private profile
        $build['buttons']['become_member'] = [
          '#type' => 'link',
          '#title' => t('Hazte socio'),
          '#url' => Url::fromRoute('entity.webform.canonical', ['webform' => 'become_associated_user']),
          '#attributes' => [
            'title' => t('Become a member'),
            'class' => ['become-member-link', 'btn-primary', 'btn-link'],
          ],
        ];

        return $build;
      }

      $is_admin = $this->account->hasPermission('administer profile')
        && $this->account->hasPermission('administer users');
      $is_owner = $this->account->id() === $user->id();

      if (!($is_admin || $is_owner)) {
        return [];
      }

      // Load an active profile for a user.
      /** @var ProfileInterface $user_profile */
      $user_profile = $profile_storage->loadMultipleByUser($user, $profile_type_by_role[$user_role][$profile_type]);
      $user_profile = current($user_profile);

      if ($user_profile instanceof ProfileInterface) {

        $build['buttons']['view'] = [
          '#type' => 'link',
          '#title' => t('Public profile'),
          '#url' => $user->toUrl(),
          '#attributes' => [
            'title' => t('View profile'),
            'class' => ['view-link', 'btn-primary', 'btn-link'],
          ],
        ];

        if ($profile_type === 'public') {
          $build['buttons']['view']['#url'] = Url::fromRoute('view.profiles.page_1', ['user' => $user->id()]);
          $build['buttons']['view']['#title'] =  t('Private profile');
        } elseif (empty($profile_storage->loadByUser($user, $profile_type_by_role[$user_role]['public']))) {
          $build['buttons']['view']['#url'] = Url::fromRoute('profile.user_page.single', ['user' => $user->id(), 'profile_type' => $profile_type_by_role[$user_role]['public']], ['query' => ['display' => 'edit_profile']]);
        }

        $display = $is_admin && $user_role == 'escuela' && $profile_type == 'private' ? 'default' : 'edit_profile';
        $url = Url::fromRoute('profile.user_page.single', ['user' => $user->id(), 'profile_type' => $user_profile->bundle()], ['query' => ['display' => $display]]);
        $build['buttons']['edit'] = [
          '#type' => 'link',
          '#title' => t('Edit'),
          '#url' => $url,
          '#attributes' => [
            'title' => t('Edit profile'),
            'class' => ['edit-link', 'btn-primary', 'btn-link'],
          ],
        ];

        if ($profile_type === 'public') {
          $build['buttons']['delete'] = [
            '#type' => 'link',
            '#title' => t('Delete'),
            '#url' => $user_profile->toUrl('delete-form'),
            '#attributes' => [
              'title' => t('Delete profile'),
              'class' => ['delete-link', 'btn-primary', 'btn-link'],
            ],
          ];
        }

      }
      else {
        // Reference to create profile.
        $build['buttons']['add'] = [
          '#type' => 'link',
          '#title' => t('Create public profile'),
          '#url' => Url::fromRoute('profile.user_page.single', ['user' => $user->id(), 'profile_type' => $profile_type_by_role[$user_role][$profile_type]]),
          '#attributes' => [
            'title' => t('Create public profile'),
            'class' => ['add-link', 'btn-primary', 'btn-link'],
          ],
        ];
      }
    }

    return $build;
  }

}
