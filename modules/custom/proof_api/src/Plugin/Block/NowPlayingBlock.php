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
    $response = $this->proofAPIRequests->getAllVideos();
    $createdAt = array();

    foreach ($response as $video) {
      $createdAt[] = $video['attributes']['created_at'];
    }

    array_multisort($createdAt, SORT_DESC, $response);

    for ($i = 0; $i < count($response); $i++) {
      $url = $response[$i]['attributes']['url'];
      $embedURL = $this->proofAPIUtilities->convertToEmbedURL($url);
      $response[$i]['attributes']['embedURL'] = $embedURL;
    };

    $response = array_slice($response, 0, 1, true);

    $title = 'Now Playing - ' . $response[0]['attributes']['title'];

    $build = array(
      '#videos' => $response,
      '#title' => $title,
      '#theme' => 'now_playing',
    );

    /**
     * attach js and css libraries
     * attach global variables for jQuery to reference when building the page
     */
    $build['#attached']['library'][] = 'proof_api/proof-api';
    $build['#attached']['drupalSettings']['videoArray'] = $response;
    $build['#attached']['drupalSettings']['redirectTo'] = 'proof_api.all_videos';

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
