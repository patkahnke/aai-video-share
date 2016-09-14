<?php

/**
 * @file
 * Contains \Drupal\proof_api\Controller\ProofAPIController.
 */

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\proof_api\Ajax\VoteUpCommand;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

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


    $page = array(
        '#theme' => 'videos',
        '#videos' => $response,
        '#redirectTo' => 'proof_api.all_videos',
        '#cache' => array
      (
        'max-age' => 0,
      ),
    );

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

  public function voteUpOne($videoID, $voteTally, $votesID)
  {
    $this->proofAPIRequests->postNewVoteUp($videoID);
    $voteTally++;
    $response = new AjaxResponse();
    $response->addCommand(new VoteUpCommand($voteTally, $votesID));

    return $response;
  }

  /**
   * Posts new Vote Down through the ProofAPIRequests service
   * Redirects back to the page of origin
   * @param $videoID
   * @param $redirectTo
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function voteDownOne($videoID, $redirectTo)
  {
    $this->proofAPIRequests->postNewVoteDown($videoID);
    return $this->redirect($redirectTo);
  }

  /**
   * Function is NOT used on embedded videos, only video links!
   * Gets a specific video resource through the ProofAPIRequests service
   * This causes a "view" resource to be created automatically
   * Returns a Trusted Redirect Response to the video url
   * @param $videoID
   * @return TrustedRedirectResponse
   * @todo solve the issue of how to create a new "view" resource on embedded videos that don't use this function
   */
  public function viewVideo($videoID)
  {
    $response = $this->proofAPIRequests->getVideo($videoID);
    $json = json_decode($response, true);
    $url = $json['data']['attributes']['url'];
    return new TrustedRedirectResponse($url);
  }

  /**
   * COMING!! A function that will be called when an embedded video is played
   * - possibly by subscribing to onPlayerStateChange() through the Youtube Player API
   * Posts a new view resource related to a specific video through the ProofAPIRequests service
   * @param $videoID
   * @return Response
   * @todo set up an event listener on iFrame through the Youtube Player API to create a call to this function
   */
  public function newView($videoID)
  {
      $this->proofAPIRequests->postNewView($videoID);
      return new Response();
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