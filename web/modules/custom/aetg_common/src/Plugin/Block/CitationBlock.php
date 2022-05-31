<?php

namespace Drupal\aetg_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Utility\Token;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Citation block.
 *
 * @Block(
 *   id = "aetg_citation_block",
 *   admin_label = @Translation("AETG Citation Block"),
 *   category = @Translation("Aetg")
 * )
 */
class CitationBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The token replacement instance.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Utility\Token $token
   *   The token replacement instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->token = $token;
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
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $build = [
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Citación recomendada:'),
        '#attributes' => ['class' => 'block__title'],
      ],
      'content' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => ['class' => 'citation__content'],
      ],
    ];

    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface) {

      $resource_type = $node->get('field_tipo_de_recurso')->getValue()[0]['target_id'];
      $citation = '';

      if ($resource_type === '50') {
        // Tesinas id = 50.

        $field_autor = '';
        if ($node->hasField('field_autor') && !$node->get('field_autor')->isEmpty()) {
          $field_autor = $this->getAuthorNames($node->get('field_autor')->referencedEntities());
        }

        $citation = $field_autor . "<em>[node:title]</em> [en línea]. AETG, [node:field_fecha_de_publicacion:date:custom:Y]. [[node:url]]. [Fecha de consulta: [current-date:short]]";
      }
      elseif ($resource_type === '53' || $resource_type === '458') {
        // Revistas y Boletines id = 13, 15.
        $citation = "[node:title] [en línea]. AETG, [node:field_fecha_de_publicacion:date:custom:Y]. [[node:url]].  [Fecha de consulta: [current-date:short]]";
      }
      elseif ($resource_type === '51' || $resource_type === '290') {
        // Articulos y Monografias(Libros) id = 51, 290.

        $field_autor = '';
        if ($node->hasField('field_autor') && !$node->get('field_autor')->isEmpty()) {
          $field_autor = $this->getAuthorNames($node->get('field_autor')->referencedEntities());
        }

        $citation = $field_autor . "''[node:title]'', en <em>[node:field_editorial]</em>. ([node:field_fecha_de_publicacion:date:custom:Y]).";
      }
      elseif ($resource_type === '52') {
        // Video id = 52.
        $citation = "<em>[node:title]</em> [Video]. AETG, [node:field_fecha_de_publicacion:date:custom:Y]. [[node:url]]. [Fecha de consulta: [current-date:short]]";
      }

      $build['content']['#value'] = $this->t($this->token->replace($citation, ['node' => $node]));
    }

    return $build;
  }

  /**
   * @param TermInterface[] $terms
   * @return string
   */
  private function getAuthorNames($terms) {
    $author_names = '';
    foreach ($terms as $key => $author) {
      /** @var TermInterface $author */
      $author_names .= $author->getName();
      if ($key !== count($terms) - 1) {
        $author_names .= ', ';
      } else {
        $author_names .= '. ';
      }
    }
    return $author_names;
  }

}
