<?php

namespace Drupal\matt\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Portfolio navigation' block.
 *
 * @Block(
 *   id = "node_navigation",
 *   admin_label = @Translation("Node navigation"),
 *   category = @Translation("Content")
 * )
 */
class NodeNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->nodeStorage = $container->get('entity_type.manager')->getStorage('node');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if (!$node instanceof NodeInterface) {
      return [];
    }

    $previous = $this->getSibling($node, 'previous');
    $next = $this->getSibling($node, 'next', [
      $previous instanceof NodeInterface ? $previous->id() : NULL
    ]);

    return [
      '#theme' => 'node_navigation',
      '#previous' => $previous,
      '#next' => $next,
    ];
  }

  /**
   * Get previous node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A given node.
   * @param string $rel
   *   Get node from the past or future (default: `next`).
   * @param array $excluded
   *   (optional) Exclude some nodes.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Either the previous node or nothing
   */
  public function getSibling($node, $rel, $excluded = []) {
    $excluded[] = $node->id();
    $created_order = $rel == 'previous' ? '<=' : '>=';
    $sort_order = $rel == 'previous' ? 'DESC' : 'ASC';

    $categories = explode(', ', $node->get('categories')->getString());
    if (empty($categories)) {
      return NULL;
    }

    $q = $this->nodeStorage->getQuery();
    $q->condition('created', $node->created->getString(), $created_order);

    // Portfolio
    if (in_array('1', $categories)) {
      $q->condition('categories', ['1'], 'IN');
    }

    // Page
    if (in_array('2', $categories)) {
      $q->condition('categories', ['2'], 'IN');
    }

    // Post
    if (in_array('3', $categories)) {
      $q->condition('categories', ['3'], 'IN');
    }

    $q->condition('status', NodeInterface::PUBLISHED);
    $q->condition('nid', $excluded, 'NOT IN');
    $q->sort('created', $sort_order);
    $q->range(0, 1);
    $q->accessCheck(FALSE);
    $nids = $q->execute();
    $nid = reset($nids) ?? NULL;

    return $nid ? $this->nodeStorage->load($nid) : NULL;
  }
}
