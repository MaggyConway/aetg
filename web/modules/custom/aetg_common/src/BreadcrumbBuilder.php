<?php

/**
 * @file
 * Contains Drupal\aetg_common\BreadcrumbBuilder.
 */

namespace Drupal\aetg_common;

use Drupal\Component\Utility\Html;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\custom_breadcrumbs\BreadcrumbBuilder as CustomBreadcrumbBuilder;
use Drupal\custom_breadcrumbs\Entity\CustomBreadcrumbs;
use Drupal\custom_breadcrumbs\Form\CustomBreadcrumbsForm;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BreadcrumbBuilder.
 */
class BreadcrumbBuilder extends CustomBreadcrumbBuilder {

  /**
   * Provides an alias cleaner.
   *
   * @var Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * BreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   LanguageManager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\Core\Controller\TitleResolverInterface $titleResolver
   *   Title resolver.
   * @param \Drupal\Core\Utility\Token $token
   *   Token.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   Alias manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   Path matcher.
   * @param \Drupal\Core\Routing\AdminContext $routerAdminContext
   *   Router admin context.
   * @param \Drupal\pathauto\AliasCleanerInterface $aliasCleaner
   *   Provides an alias cleaner.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              EntityTypeManagerInterface $entityTypeManager,
                              LanguageManagerInterface $languageManager,
                              RequestStack $requestStack,
                              TitleResolverInterface $titleResolver,
                              Token $token,
                              AliasManagerInterface $aliasManager,
                              PathMatcherInterface $pathMatcher,
                              AdminContext $routerAdminContext,
                              AliasCleanerInterface $aliasCleaner) {
    parent::__construct($configFactory, $entityTypeManager, $languageManager, $requestStack, $titleResolver, $token, $aliasManager, $pathMatcher, $routerAdminContext);
    $this->aliasCleaner = $aliasCleaner;
  }

  /**
   * {@inheritdoc}
   */
  protected function applyBreadcrumb(Breadcrumb &$breadcrumb, CustomBreadcrumbs $customBreadcrumbs, $entity) {
    $paths = $customBreadcrumbs->getMultiValues('breadcrumbPaths');
    $titles = $customBreadcrumbs->getMultiValues('breadcrumbTitles');
    $extraContexts = $customBreadcrumbs->getMultiValues('extraCacheContexts');
    $token_vars = [];

    if ($entity instanceof EntityInterface) {
      $token_vars = [CustomBreadcrumbsForm::getTokenEntity($entity->getEntityTypeId()) => $entity];
    }

    foreach ($paths as $key => $path) {
      if (isset($titles[$key])) {
        $href = file_url_transform_relative($this->token->replace($path, $token_vars, ['clear' => TRUE]));

         // My code starts here.
        $args = explode('/', $href);
        foreach ($args as $index => $arg) {
          $args[$index] = $this->aliasCleaner->cleanString($arg);
        }
        $href = implode('/', $args);
        // My code ends here.

        $link_title = $this->token->replace($titles[$key], $token_vars, ['clear' => TRUE]);
        $link_title = Html::decodeEntities($link_title);

        // Skip empty href, for example when token is empty.
        if (empty($href) || empty($link_title)) {
          continue;
        }

        if ($href === '<nolink>') {
          $link = Link::createFromRoute($this->prepareTitle($link_title), $href);
          $breadcrumb->addLink($link);
        }
        else {
          if ($this->checkHierarchyToken($href)) {
            $field_name = explode(':', $href)[1];
            $field_name = str_replace('>', '', $field_name);
            if ($entity instanceof EntityInterface) {
              if ($entity->hasField($field_name)) {
                $term = $entity->get($field_name)->entity;
                if ($term instanceof TermInterface) {
                  $parents = $this->getAllParents($term->id());
                  foreach (array_reverse($parents) as $parent) {
                    $link = $parent->toLink($this->prepareTitle($parent->label()));
                    $breadcrumb->addLink($link);
                    $breadcrumb->addCacheableDependency($parent);
                  }
                }
              }
            }
            continue;
          }
          else {
            $url = Url::fromUserInput($href);
            $link = Link::fromTextAndUrl($this->prepareTitle($link_title), $url);
            $breadcrumb->addLink($link);
          }
        }
        $breadcrumb->addCacheableDependency($entity);
        $breadcrumb->addCacheableDependency($customBreadcrumbs);
      }
    }

    if (array_filter($extraContexts)) {
      $breadcrumb->addCacheContexts($extraContexts);
    }
  }

  /**
   * Helper method to trim title.
   *
   * @param string $title
   *   Title.
   *
   * @return string
   *   Substring.
   */
  private function prepareTitle($title) {
    if ($length = $this->customBreadcrumbsSettings['trim_title']) {
      // We should catch the case when title is array or object.
      if (is_string($title) && strlen($title) > $length) {
        return substr($title, 0, $length) . '...';
      }
    }

    return $title;
  }

}
