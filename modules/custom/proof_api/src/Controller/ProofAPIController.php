<?php

/**
 * @file
 * Contains \Drupal\proof_api\Controller\ProofAPIController.
 */

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
//use Drupal\proof_api\Ajax\NewVideoFormCommand; Keeping this for reference to revidit this command later
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
   */
  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }

  /**
   * Builds a render array for the All Videos page.
   * Gets all the videos through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Builds the render array via the function BuildVideoListPage attached to ProofAPIUtilities
   * Includes the javascript file "commands" in the render array to build the DOM using jQuery
   * @return array
   */
  public function allVideos()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 1000);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.all_videos', 0);

    return $page;
  }

  /**
   * Builds a render array for the Top Ten By Views page.
   * Gets all the videos through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Builds the render array via the function BuildVideoListPage attached to ProofAPIUtilities
   * Includes the javascript file "commands" in the render array to build the DOM using jQuery
   * @return array
   */
  public function topTenByViews()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'view_tally', 'overlay', 10);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.top_ten_by_views', 300);

    return $page;
  }

  /**
   * Builds a render array for Top Ten By Votes page.
   * Gets all the videos through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Builds the render array via the function BuildVideoListPage attached to ProofAPIUtilities
   * Includes the javascript file "commands" in the render array to build the DOM using jQuery
   * @return array
   */
  public function topTenByVotes()
  {
    $videos = $this->proofAPIRequests->getAllVideos();
    $videos = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'vote_tally', 'overlay', 10);
    $page = $this->proofAPIUtilities->buildVideoListPage($videos, 'proof_api.top_ten_by_votes', 300);

    return $page;
  }


  /**
   * Redirects to the New Video Form page.
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function newVideo() {
    $response = $this->redirect('proof_api.new_video_form');
    return $response;
  }

  /**
   * Works in conjunction with the Now Playing page.
   * Takes in a video ID variable from a link in one of the video list blocks
   * Gets the video resource that matches the videoID through the ProofAPIRequests service
   * Prepares the raw data received from ProofAPIRequests using the pre-render function SortAndPrepVideos attached to ProofAPIUtilities
   * Creates a new videoID by concatenating the existing videoID with the user number
   * Stores the new videoID and the prepared video resource in KeyValueStore, to be retrieved by the NowPlaying function
   * Redirects to the home page, which uses the NowPlaying function to retrieve the stored video information and render it onto the page
   * PURPOSE: this method allows for the linked video to be played in the home screen, without moving to a new view, and counts the video
   * @todo get the autoplay script working properly so the video plays in the box by merely clicking the link
   * @param $videoID
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function viewVideo($videoID)
  {
    $keyValueStore = $this->keyValue('proof_api');
    $videos = array();

    $videos[0] = $this->proofAPIRequests->getVideo($videoID);
    $video = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'video-box', 1);

    $user = \Drupal::currentUser();
    $userID = $user->id();
    $videoID = $videoID . $userID;
    $keyValueStore->set('requestedVideo' . $userID, $video);
    $keyValueStore->set('requestedVideoID' .$userID, $videoID);

    return $this->redirect('proof_api.home');
  }

  /**
   * Builds a render array for the Now Playing page, which is also set as the home page
   * By default, displays the most recently added video in the "Now Playing" box
   * Retrieves two video IDs from the KeyValueStore: currentVideoID and requestedVideoID
   * If the currentVideoID matches the requestedVideoID:
   *  - then no new video has been requested, so the most recently added video is displayed
   *  - gets all videos through the ProofAPIRequests service
   *  - prepares the video through the SortAndPrepVideos function attached to ProofAPIUtilities
   *  - creates an id for the current video by concatenating the videoID with the userID
   *  - stores the current video info and the requested video info in the KeyValueStore - BOTH being listed as the current video
   * If the requestedVideoID is different from the currentVideoID, then:
   *  - the requested video resource is retrieved from the KeyValueStore
   *  - the video data is prepped through the SortAndPrepVideos function attached to ProofAPIUtilities
   *  - the requested video data is set to match the current video data in KeyValueStorage
   * Render array is built through the ProofAPIUtilities function BuildNowPlayingPage
   * Includes the javascript file "commands" in the render array to build the DOM with jQuery
   * @return array
   */
  public function nowPlaying()
  {
    $user = \Drupal::currentUser();
    $userID = $user->id();
    $keyValueStore = $this->keyValue('proof_api');
    $currentVideoID = $keyValueStore->get('currentVideoID' . $userID);
    $requestedVideoID = $keyValueStore->get('requestedVideoID' . $userID);

    if ($currentVideoID === $requestedVideoID) {

      $videos = $this->proofAPIRequests->getAllVideos();
      $currentVideo = $this->proofAPIUtilities->sortAndPrepVideos($videos, 'created_at', 'overlay', 1);

      $currentVideoID = $currentVideo[0]['id'] . $userID;

      $keyValueStore->set('currentVideo' . $userID, $currentVideo);
      $keyValueStore->set('requestedVideo' . $userID, $currentVideo);
      $keyValueStore->set('currentVideoID' . $userID, $currentVideoID);
      $keyValueStore->set('requestedVideoID' . $userID, $currentVideoID);

      $response = $currentVideo;

    } else {
      $requestedVideo = $keyValueStore->get('requestedVideo' . $userID);
      $requestedVideo = $this->proofAPIUtilities->sortAndPrepVideos($requestedVideo, 'created_at', 'video-box', 1);

      $currentVideo = $keyValueStore->get('currentVideo' . $userID);
      $currentVideoID = $keyValueStore->get('currentVideoID' . $userID);
      $keyValueStore->set('requestedVideo' . $userID, $currentVideo);
      $keyValueStore->set('requestedVideoID' . $userID, $currentVideoID);

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