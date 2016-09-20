<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\NowPlayingBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * NOTE: This block is not currently being used, but is being held for future reference.
 * Provides an iFrame for a video. Used on the front page to display most recent video added, and also to play videos
 * from the front page.
 *
 * @Block(
 *   id = "now_playing_block",
 *   admin_label = @Translation("Now Playing"),
 * )
 */
class NowPlayingBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  private $proofAPIRequests;
  private $proofAPIUtilities;


  /**
   * NowPlayingBlock constructor.
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param ProofAPIRequests $proofAPIRequests
   * @param ProofAPIUtilities $proofAPIUtilities
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              ProofAPIRequests $proofAPIRequests,
                              ProofAPIUtilities $proofAPIUtilities)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }

  /**
   * /**
   * Builds a render array for the Most Recent Videos block.
   * Gets all the videos through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Builds the render array via the function BuildVideoListBlockPage attached to ProofAPIUtilities
   * Includes the javascript file "commands" in the render array to build the DOM with jQuery
   * @return array
   */
  public function build()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 1);
    $build = $this->proofAPIUtilities->buildVideoListBlockPage($videos, 'Now Playing');

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
