<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\TopTenViewsBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
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
  private $proofAPIUtilities;

  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              ProofAPIRequests $proofAPIRequests,
                              ProofAPIUtilities $proofAPIUtilities)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'view_tally', 'overlay', 10);
    $build = $this->proofAPIUtilities->buildVideoListBlockPage($videos, 'Most Viewed Videos');

    return $build;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $proofAPIRequests,
      $proofAPIUtilities
      );
  }

}
