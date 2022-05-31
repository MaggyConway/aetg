<?php

namespace Drupal\aetg_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Profile operations link block.
 *
 * @Block(
 *   id = "aetg_edit_user_links",
 *   admin_label = @Translation("AETG Edit user links block"),
 *   category = @Translation("Aetg")
 * )
 */
class EditUserLinksBlock extends BlockBase implements ContainerFactoryPluginInterface {

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

    if ($user = $this->routeMatch->getParameter('user')) {
      $user = $user instanceof UserInterface ? $user : $this->entityTypeManager->getStorage('user')->load($user);
    }

    if ($user instanceof UserInterface) {

      $user_role = $user->hasRole('asociado') ? 'asociado' : ($user->hasRole('escuela') ? 'escuela' : 'authenticated');

      $is_admin = $this->account->hasPermission('administer profile')
        && $this->account->hasPermission('administer users');
      $is_owner = $this->account->id() === $user->id();

      if (!($is_admin || $is_owner)) {
        return [];
      }

      $build['tabs'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'tabs',
            'edit-user-profile-tabs'
            ],
        ],
      ];

      if ($is_admin) {

        $display = ($user->hasRole('administrator') || $user->hasRole('personal_aetg') ? 'admin_account' : 'edit_account');

        $route_name = 'entity.user.edit_form';

        $build['tabs']['edit_account'] = [
          '#type' => 'link',
          '#title' => t('Edit account'),
          '#url' => Url::fromRoute($route_name, ['user' => $user->id()], ['query' => ['display' => $display]]),
          '#attributes' => [
            'title' => t('Edit account'),
            'class' => ['edit-link',],
          ],
        ];

        if ($display === 'admin_account') {
          $build['tabs']['edit_account']['#title'] = t('Edit as Admin/Personal AETG account');
        }

        // If it's current page.
        if ($route_name === $this->routeMatch->getRouteName()) {
          $build['tabs']['edit_account']['#attributes']['class'][] = 'active';
        }
      }

      if ($user_role  !== 'authenticated') {

        $route_name = 'profile.user_page.single';

        foreach (['private', 'public'] as $profile_type) {

          $display = $is_admin && $user_role == 'escuela' && $profile_type == 'private' ? 'default' : 'edit_profile';

          $url = Url::fromRoute($route_name, [
            'user' => $user->id(),
            'profile_type' => $profile_type_by_role[$user_role][$profile_type]
          ], ['query' => ['display' => $display]]);

          $build['tabs'][$profile_type] = [
            '#type' => 'link',
            '#title' => t('Edit '. $profile_type . ' profile'),
            '#url' => $url,
            '#attributes' => [
              'title' => t('Edit profile'),
              'class' => ['edit-link', $profile_type],
            ],
          ];

          // If it's current page.
          if ($route_name === $this->routeMatch->getRouteName()
            && ($profile = $this->routeMatch->getParameter('profile_type'))
            && $profile instanceof ProfileTypeInterface
            && $profile->id() === $profile_type_by_role[$user_role][$profile_type]
          ) {
            $build['tabs'][$profile_type]['#attributes']['class'][] = 'active';
          }
        }
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url.path'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['user_list'];
  }

}
