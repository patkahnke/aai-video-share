<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\TopTenViewsBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of links to the top ten videos, by views.
 *
 * @Block(
 *   id = "top_ten_views_block",
 *   admin_label = @Translation("Most Viewed Videos"),
 * )
 */
class TopTenViewsBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  private $proofAPIRequests;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProofAPIRequests $proofAPIRequests)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->proofAPIRequests = $proofAPIRequests;
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $response = $this->proofAPIRequests->getAllVideos();
    $viewTally = array();

    foreach ($response as $video) {
      $viewTally[] = $video['attributes']['view_tally'];
    }

    array_multisort($viewTally, SORT_DESC, $response);
    $response = array_slice($response, 0, 10, true);
    $title = 'Most Viewed Videos';

    return array(
      '#title' => $title,
      '#videos' => $response,
      '#theme' => 'top_ten_block',
      '#attached' => ['library' => ['proof_api/proof-api']],
    );
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $proofAPIRequests
      );
  }

}
