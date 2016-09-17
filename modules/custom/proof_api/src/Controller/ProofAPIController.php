<?php

/**
 * @file
 * Contains \Drupal\proof_api\Controller\ProofAPIController.
 */

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
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
   * Calls ProofAPIUtilities to convert urls to embeddable urls
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

    /**
     * attach js and css libraries
     * attach global variables for jQuery to reference when building the page
     */
    $page['#attached']['library'][] = 'proof_api/proof-api';
    $page['#attached']['drupalSettings']['videoArray'] = $response;
    $page['#attached']['drupalSettings']['redirectTo'] = 'proof_api.all_videos';

    return $page;
  }

  /**
   * Sends request to get all videos through ProofAPIRequests
   * Creates a render array to display the top ten videos by views, with most viewed first
   * Calls ProofAPIUtilities to convert urls to embeddable urls
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

    /**
     * attach js and css libraries
     * attach global variables for jQuery to reference when building the page
     */
    $page['#attached']['library'][] = 'proof_api/proof-api';
    $page['#attached']['drupalSettings']['videoArray'] = $response;
    $page['#attached']['drupalSettings']['redirectTo'] = 'proof_api.all_videos';

    return $page;
  }

  /**
   * Sends request to get all videos through ProofAPIRequests
   * Creates a render array to display the top ten videos by votes, with highest voted first
   * Calls ProofAPIUtilities to convert urls to embeddable urls
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

    /**
     * attach js and css libraries
     * attach global variables for jQuery to reference when building the page
     */
    $page['#attached']['library'][] = 'proof_api/proof-api';
    $page['#attached']['drupalSettings']['videoArray'] = $response;
    $page['#attached']['drupalSettings']['redirectTo'] = 'proof_api.all_videos';

    return $page;
  }

  /**
   * Validates that video is being posted on a weekday
   * If so, redirects to proof_api.new_video_form
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function newVideo()
  {
    $response = new AjaxResponse();

    if (date('N') < 6) {
      $response = $this->redirect('proof_api.new_video_form');

    } else {
      $title = 'Sorry - you cannot add a video on weekends.';
      $content = array();
      $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
        $response->addCommand(new OpenModalDialogCommand($title, $content));
    };
      return $response;
  }

  /**
   * Posts a new +1 vote on a specific video through the ProofAPIRequests service, then
   * gets all videos through ProofAPIRequests and searches for the updated vote tally from the affected video
   * (the reason for getting all videos rather than the specific one is because when a specific video is requested, the
   * Proof API automatically creates a new "view" on that video, which would inflate the view count.
   * Returns an AJAX response containing the new vote tally, as well as the "vote" callback command which updates the DOM.
   * @param $videoID
   * @param $voteID
   * @return AjaxResponse
   */
  public function voteUpOne($videoID, $voteID)
  {
    $user = \Drupal::currentUser();
    $keyValueStore = $this->keyValue('proof_api');
    $today = date('Ymd');
    $userID = $user->id();
    $voteCheckID = $videoID . $userID;
    $voteCheck = $keyValueStore->get($voteCheckID);
    $voteTally = null;
    $response = new AjaxResponse();

    if ($voteCheck === $today) {
        $title = 'Sorry - you have already voted on this video today';
        $content = array (
          '#attached' => ['library' => ['core/drupal.dialog.ajax']],
        );
        $response->addCommand(new OpenModalDialogCommand($title, $content));
      } else {

      $this->proofAPIRequests->postNewVoteUp($videoID);
      $newVideoData = $this->proofAPIRequests->getAllVideos();

      for ($i = 0; $i < count($newVideoData); $i++) {
        if ($newVideoData[$i]['id'] === $videoID) {
          $voteTally = $newVideoData[$i]['attributes']['vote_tally'];
        }
      };

      $keyValueStore->set($voteCheckID, $today);
      $response->addCommand(new VoteCommand($voteTally, $voteID));

    };

    return $response;
  }

  /**
   * Verifies that the user has not voted on a video yet today.
   * Posts a new -1 vote on a specific video through the ProofAPIRequests service, then
   * gets all videos through ProofAPIRequests and searches for the updated vote tally from the affected video.
   * (the reason for getting all videos rather than the specific one is because when a specific video is requested, the
   * Proof API automatically creates a new "view" on that video, which would inflate the view count.
   * Returns an AJAX response containing the new vote tally, as well as the "vote" callback command which updates the DOM.
   * @param $videoID
   * @param $voteID
   * @return AjaxResponse
   */
  public function voteDownOne($videoID, $voteID)
  {
    $user = \Drupal::currentUser();
    $keyValueStore = $this->keyValue('proof_api');
    $today = date('Ymd');
    $userID = $user->id();
    $voteCheckID = $videoID . $userID;
    $voteCheck = $keyValueStore->get($voteCheckID);
    $voteTally = null;
    $response = new AjaxResponse();

    if ($voteCheck === $today) {
      $title = 'Sorry - you have already voted on this video today';
      $content = array (
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      );
      $response->addCommand(new OpenModalDialogCommand($title, $content));

    } else {

      $this->proofAPIRequests->postNewVoteDown($videoID);
      $newVideoData = $this->proofAPIRequests->getAllVideos();

      for ($i = 0; $i < count($newVideoData); $i++) {
        if ($newVideoData[$i]['id'] === $videoID) {
          $voteTally = $newVideoData[$i]['attributes']['vote_tally'];
        }
      };

      $keyValueStore->set($voteCheckID, $today);
      $response->addCommand(new VoteCommand($voteTally, $voteID));
    };

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

  /**
   * Posts a new "view" resource on a specific video through the ProofAPIRequests service, then
   * gets all videos through ProofAPIRequests and searches for the updated view tally from the affected video.
   * (the reason for getting all videos rather than the specific one is because when a specific video is requested, the
   * Proof API automatically creates a new "view" resource on that video, which would inflate the view count.
   * Returns an AJAX response containing the new vote tally, as well as the "vote" callback command which updates the DOM.
   * @param $videoID
   * @param $viewID
   * @return AjaxResponse
   */
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