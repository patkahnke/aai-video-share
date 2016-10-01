<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\TopTenViewsBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\key\KeyRepository;
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
  private $keyRepository;

  /**
   * TopTenViewsBlock constructor.
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param ProofAPIRequests $proofAPIRequests
   * @param ProofAPIUtilities $proofAPIUtilities
   * @param KeyRepository $keyRepository
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              ProofAPIRequests $proofAPIRequests,
                              ProofAPIUtilities $proofAPIUtilities,
                              KeyRepository $keyRepository)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
    $this->keyRepository = $keyRepository;
  }


  /**
   * Builds a render array for the Top Ten Views block.
   * Gets all the videos through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Builds the render array via the function BuildVideoListBlockPage attached to ProofAPIUtilities
   * Includes the javascript file "commands" in the render array to build the DOM with jQuery
   * @return array
   */
  public function build()
  {
    $authKey = $this->keyRepository->getKey('proof_api')->getKeyValue();
    $route = 'videos?page&per_page';
    $videos = $this->proofAPIRequests->getCurl($authKey, $route);
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'view_tally', 'overlay', 10);
    $build = $this->proofAPIUtilities->buildVideoListBlockPage($videos, 'Most Viewed Videos');

    return $build;
  }

  /**
   * Gets the ProofAPIRequests and ProofAPIUtilities services from the service container
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');
    $keyRepository = $container->get('key.repository');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $proofAPIRequests,
      $proofAPIUtilities,
      $keyRepository
      );
  }

}
