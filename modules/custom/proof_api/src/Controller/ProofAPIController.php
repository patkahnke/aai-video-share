<?php

/**
 * @file
 * Contains \Drupal\proof_api\Controller\ProofAPIController.
 */

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
//use Drupal\proof_api\Ajax\NewVideoFormCommand;
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

  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }

  public function allVideos()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 1000);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.all_videos', 0);

    return $page;
  }

  public function topTenByViews()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'view_tally', 'overlay', 10);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.top_ten_by_views', 300);

    return $page;
  }

  public function topTenByVotes()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'vote_tally', 'overlay', 10);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.top_ten_by_votes', 300);

    return $page;
  }

  public function newVideo() {
    $response = $this->redirect('proof_api.new_video_form');
    return $response;
  }

  public function viewVideo($videoID)
  {
    $keyValueStore = $this->keyValue('proof_api');
    $videos = array();

    $videos[0] = $this->proofAPIRequests->getVideo($videoID);
    $video = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'video-box', 1);

    $user = \Drupal::currentUser();
    $userID = $user->id();
    $videoID = $videoID . $userID;
    $keyValueStore->set('requestedVideo', $video);
    $keyValueStore->set('requestedVideoID', $videoID);

    return $this->redirect('proof_api.home');
  }

  public function nowPlaying()
  {
    $keyValueStore = $this->keyValue('proof_api');
    $currentVideoID = $keyValueStore->get('currentVideoID');
    $requestedVideoID = $keyValueStore->get('requestedVideoID');

    if ($currentVideoID === $requestedVideoID) {

      $videos = $this->proofAPIRequests->getAllVideos();
      $currentVideo = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 1);

      $user = \Drupal::currentUser();
      $userID = $user->id();

      $currentVideoID = $currentVideo[0]['id'] . $userID;

      $keyValueStore->set('currentVideo', $currentVideo);
      $keyValueStore->set('requestedVideo', $currentVideo);
      $keyValueStore->set('currentVideoID', $currentVideoID);
      $keyValueStore->set('requestedVideoID', $currentVideoID);

      $response = $currentVideo;

    } else {
      $requestedVideo = $keyValueStore->get('requestedVideo');
      $requestedVideo = $this->proofAPIUtilities->sortAndPrepVideos($requestedVideo, 'created_at', 'video-box', 1);

      $currentVideo = $keyValueStore->get('currentVideo');
      $currentVideoID = $keyValueStore->get('currentVideoID');
      $keyValueStore->set('requestedVideo', $currentVideo);
      $keyValueStore->set('requestedVideoID', $currentVideoID);

      $response = $requestedVideo;
    };

    $page = $this->proofAPIUtilities->buildNowPlayingPage($response);

    return $page;
  }

  /**
   * Posts a new +1 vote on a specific video through the ProofAPIRequests service, then
   * gets all videos through ProofAPIRequests and searches for the updated vote tally from the affected video
   * (the reason for getting all videos rather than the specific one is because when a specific video is requested, the
   * Proof API automatically creates a new "view" on that video, which would inflate the view count).
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
   * Proof API automatically creates a new "view" on that video, which would inflate the view count).
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
   * Posts a new "view" resource on a specific video through the ProofAPIRequests service, then
   * gets all videos through ProofAPIRequests and searches for the updated view tally from the affected video.
   * (the reason for getting all videos rather than the specific one is because when a specific video is requested, the
   * Proof API automatically creates a new "view" resource on that video, which would inflate the view count.
   * Returns an AJAX response containing the new view tally, as well as the "view" callback command which updates the DOM.
   * @param $videoID
   * @param $viewID
   * @return AjaxResponse
   */
  public function newView($videoID, $viewID)
  {
    $this->proofAPIRequests->postNewView($videoID);
    $videoData = $this->proofAPIRequests->getAllVideos();
    $viewTally = null;

    for ($i = 0; $i < count($videoData); $i++) {
      if ($videoData[$i]['id'] === $videoID) {
        $viewTally = $videoData[$i]['attributes']['view_tally'];
      }
    };

    $response = new AjaxResponse();
    $response->addCommand(new ViewCommand($viewTally, $viewID));

    return $response;
  }

  /*@todo Figure out why this method didn't work - I had added ajax and the class "use-ajax" to the link - removed it for the current method.
  @todo would be preferable to have this method work so user doesn't go all the way to the form before getting a weekend error response
   * public function newVideo()
  {
    $response = new AjaxResponse();

    if (date('N') < 6) {
      $response->addCommand(new NewVideoFormCommand());

    } else {
      $title = 'Sorry - you cannot add a video on a weekend.';
      $content = array (
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      );
      $response->addCommand(new OpenModalDialogCommand($title, $content));
    };
      return $response;
  }*/

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