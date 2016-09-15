<?php

/**
 * @file
 * Contains \Drupal\proof_api\Controller\ProofAPIController.
 */

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\proof_api\Ajax\ViewCommand;
use Drupal\proof_api\Ajax\VoteCommand;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for proof_api module routes.
 */
class ProofAPIController extends ControllerBase
{
  private $proofAPIRequests;
  private $proofAPIUtilities;

  /**
   * ProofAPIController constructor.
   * @param ProofAPIRequests $proofAPIRequests
   * @param ProofAPIUtilities $proofAPIUtilities
   * @todo Move as much of this logic as possible to ProofAPIUtilities
   */
  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }


  /**
   * Sends request to get all videos through ProofAPIRequests
   * Creates a render array to display all the videos, with most recent first
   * @return AjaxResponse|mixed
   */
  public function allVideos()
  {
    /** @var array $response */
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


    /** @var array $page */
    $page = array(
        '#theme' => 'videos',
        '#videos' => $response,
        '#redirectTo' => 'proof_api.all_videos',
        '#cache' => array
      (
        'max-age' => 0,
      ),
    );

    /**
     * attach js and css libraries
     * attach global variables for jQuery to reference when building the page
     * @var array $page */
    $page['#attached']['library'][] = 'proof_api/proof-api';
    $page['#attached']['drupalSettings']['videoArray'] = $response;
    $page['#attached']['drupalSettings']['redirectTo'] = 'proof_api.all_videos';

    return $page;
  }

  /**
   * Sends request to get all videos through ProofAPIRequests
   * Creates a render array to display the top ten videos by views, with most viewed first
   * @todo Move as much of this logic as possible to ProofAPIUtilities
   * @return array
   */
  public function topTenByViews()
  {
      $response = $this->proofAPIRequests->getAllVideos();
      $viewTally = array();

      foreach ($response as $video) {
          $viewTally[] = $video['attributes']['view_tally'];
      }

      array_multisort($viewTally, SORT_DESC, $response);
      $response = array_slice($response, 0, 10, true);


      for ($i = 0; $i < count($response); $i++) {
          $url = $response[$i]['attributes']['url'];
          $embedURL = $this->proofAPIUtilities->convertToEmbedURL($url);
          $response[$i]['attributes']['embedURL'] = $embedURL;
      };

      $page[] = array(
          '#theme' => 'videos',
          '#videos' => $response,
          '#redirectTo' => 'proof_api.top_ten_by_views',
          '#cache' => array
          (
              'max-age' => 0,
          ),
      );

      $page['#attached']['library'][] = 'proof_api/proof-api';

      return $page;
  }

  /**
   * Sends request to get all videos through ProofAPIRequests
   * Creates a render array to display the top ten videos by votes, with highest voted first
   * @todo Move as much of this logic as possible to ProofAPIUtilities
   * @return array
   */
  public function topTenByVotes()
  {
      $response = $this->proofAPIRequests->getAllVideos();
      $voteTally = array();

      foreach ($response as $video) {
          $voteTally[] = $video['attributes']['vote_tally'];
      }

      array_multisort($voteTally, SORT_DESC, $response);
      $response = array_slice($response, 0, 10, true);


      for ($i = 0; $i < count($response); $i++) {
          $url = $response[$i]['attributes']['url'];
          $embedURL = $this->proofAPIUtilities->convertToEmbedURL($url);
          $response[$i]['attributes']['embedURL'] = $embedURL;
      };

      $page[] = array(
          '#theme' => 'videos',
          '#videos' => $response,
          '#redirectTo' => 'proof_api.top_ten_by_votes',
          '#cache' => array
          (
              'max-age' => 0,
          ),
      );

      $page['#attached']['library'][] = 'proof_api/proof-api';

      return $page;
  }


  /**
   * Validates that video is being posted on a weekday
   * If so, redirects to proof_api.new_video_form
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   * @todo refactor error response to a modal so user can stay on the same page
   */
  public function newVideo()
  {
    if (date('N') < 6)
    {
      return $this->redirect('proof_api.new_video_form');
    } else {
      $page = array(
        '#theme' => 'bootstrap_modal',
        '#body' => 'Sorry - You can only post a new video on weekdays.'
      );
    };
    return $page;
  }

  public function voteUpOne($videoID, $voteID)
  {
    $this->proofAPIRequests->postNewVoteUp($videoID);
    $newVideoData = $this->proofAPIRequests->getAllVideos();
    $voteTally = null;

    for ($i = 0; $i < count($newVideoData); $i++) {
      if ($newVideoData[$i]['id'] === $videoID) {
        $voteTally = $newVideoData[$i]['attributes']['vote_tally'];
      }
    };

    $response = new AjaxResponse();
    $response->addCommand(new VoteCommand($voteTally, $voteID));

    return $response;
  }

  public function voteDownOne($videoID, $voteID)
  {
    $this->proofAPIRequests->postNewVoteDown($videoID);
    $newVideoData = $this->proofAPIRequests->getAllVideos();
    $voteTally = null;

    for ($i = 0; $i < count($newVideoData); $i++) {
      if ($newVideoData[$i]['id'] === $videoID) {
        $voteTally = $newVideoData[$i]['attributes']['vote_tally'];
      }
    };

    $response = new AjaxResponse();
    $response->addCommand(new VoteCommand($voteTally, $voteID));

    return $response;
  }

  /**
   * Gets a specific video resource through the ProofAPIRequests service
   * This causes a "view" resource to be created automatically
   * Returns a Trusted Redirect Response to the video url
   * @param $videoID
   * @return TrustedRedirectResponse
   */
  public function viewVideo($videoID)
  {
    $response = $this->proofAPIRequests->getVideo($videoID);
    $json = json_decode($response, true);
    $url = $json['data']['attributes']['url'];
    return new TrustedRedirectResponse($url);
  }

  public function newView($videoID, $viewID)
  {
    $this->proofAPIRequests->postNewView($videoID);
    $newVideoData = $this->proofAPIRequests->getAllVideos();
    $viewTally = null;

    for ($i = 0; $i < count($newVideoData); $i++) {
      if ($newVideoData[$i]['id'] === $videoID) {
        $viewTally = $newVideoData[$i]['attributes']['view_tally'];
      }
    };

    $response = new AjaxResponse();
    $response->addCommand(new ViewCommand($viewTally, $viewID));

    return $response;

  }

  /**
   * Gets the ProofAPIRequests and ProofAPIUtilities services from the services container
   * @param ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');
    return new static($proofAPIRequests, $proofAPIUtilities);
  }
}