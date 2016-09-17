<?php

/**
 * @file
 * Contains \Drupal\proof_api\Plugin\Block\TopTenVotesBlock.
 */

namespace Drupal\proof_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of links to the top ten videos, by votes.
 *
 * @Block(
 *   id = "top_ten_votes_block",
 *   admin_label = @Translation("Highest Voted Videos"),
 * )
 */
class TopTenVotesBlock extends BlockBase implements ContainerFactoryPluginInterface
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
    $voteTally = array();

    foreach ($response as $video) {
      $voteTally[] = $video['attributes']['vote_tally'];
    }

    array_multisort($voteTally, SORT_DESC, $response);
    $response = array_slice($response, 0, 10, true);
    $title = 'Highest Voted Videos';

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
