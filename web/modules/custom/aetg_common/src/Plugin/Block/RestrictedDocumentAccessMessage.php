<?php

namespace Drupal\aetg_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Restricted Document Access Message block.
 *
 * @Block(
 *   id = "aetg_restriction_message_block",
 *   admin_label = @Translation("AETG Restricted Document Access Message Block"),
 *   category = @Translation("Aetg")
 * )
 */
class RestrictedDocumentAccessMessage extends BlockBase implements ContainerFactoryPluginInterface {

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

    if ($this->account->isAuthenticated()) {
      return $build;
    }


    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface && $node->id() != '392' &&
      ($node->hasField('field_acceda_a_formularios') && !$node->get('field_acceda_a_formularios')->isEmpty() ||
        $node->hasField('field_adjunto') && !$node->get('field_adjunto')->isEmpty())
    ) {
      $build = [
        'content' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t("Para poder ver los documentos o formularios asociados a esta página <a href='/user/register'>debes estar registrado/a</a>."),
          '#attributes' => ['class' => 'restriction-message'],
        ],
      ];
    }

    return $build;
  }

}
