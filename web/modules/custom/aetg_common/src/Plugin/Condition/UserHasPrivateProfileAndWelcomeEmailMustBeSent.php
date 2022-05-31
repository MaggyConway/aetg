<?php

namespace Drupal\aetg_common\Plugin\Condition;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Send welcome email to the user or not' condition.
 *
 * @Condition(
 *   id = "rules_send_welcome_email",
 *   label = @Translation("User has private profile and Welcome email must be sent"),
 *   category = @Translation("User"),
 *   context_definitions = {
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field"),
 *       description = @Translation("The name of the field to check for."),
 *       assignment_restriction = "input"
 *     ),
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User"),
 *       description = @Translation("Specifies the user account for which to send the email. If left empty, the currently logged in user will be used."),
 *       assignment_restriction = "selector",
 *       required = FALSE
 *     ),
 *   }
 * )
 *
 * @todo Add access callback information from Drupal 7.
 */
class UserHasPrivateProfileAndWelcomeEmailMustBeSent extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a UserHasEntityFieldAccess object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Evaluate if the user has access to the field of an entity.
   *
   * @param string $field_name
   *   The name of the field to check to send or not.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user account.
   *
   * @return bool
   *   TRUE if the welcome email must be sent, FALSE otherwise.
   */
  protected function doEvaluate($field_name, AccountInterface $user) {
    $user_roles = array_intersect($user->getRoles(), ['asociado', 'escuela']);

    if (empty($user_roles)) {
      return FALSE;
    }

    $profile_type = in_array('asociado', $user_roles) ? 'informacion_privada' : 'escuela';

    /** @var \Drupal\profile\ProfileStorage $profile_storage */
    $profile_storage = $this->entityTypeManager->getStorage('profile');
    $profile = $profile_storage->loadByUser($user, $profile_type);

    if (empty($profile)) {
      return FALSE;
    }

    /*
    if (!$profile->hasField($field_name) || $profile->get($field_name)->isEmpty()) {
      return FALSE;
    }

    $term = $profile->get($field_name)->entity;

    if ($term instanceof TermInterface) {
      return $term->id() == 970;
    }

    return FALSE;
    */
    return TRUE;
  }

}
