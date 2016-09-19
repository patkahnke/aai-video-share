<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\MostRecentVideosBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of links to the most recent ten videos.
 *
 * @Block(
 *   id = "most_recent_videos_block",
 *   admin_label = @Translation("Most Recent Videos"),
 * )
 */
class MostRecentVideosBlock extends BlockBase implements ContainerFactoryPluginInterface
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
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 10);
    $build = $this->proofAPIUtilities->buildVideoListBlockPage($videos, 'Most Recent Videos');

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
