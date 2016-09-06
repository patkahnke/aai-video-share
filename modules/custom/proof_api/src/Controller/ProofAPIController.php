<?php

namespace Drupal\proof_api\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\proof_api\ProofAPIRequests\ProofAPIRequests;
use Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Ajax\CommandInterface;

class ProofAPIController extends ControllerBase
{
  private $proofAPIRequests;
  private $proofAPIUtilities;

  public function __construct(ProofAPIRequests $proofAPIRequests, ProofAPIUtilities $proofAPIUtilities)
  {
    $this->proofAPIRequests = $proofAPIRequests;
    $this->proofAPIUtilities = $proofAPIUtilities;
  }

  /**
   * @return array
   */
  public function allVideos()
  {
      $response = $this->proofAPIRequests->listAllVideos();
      $createdAt = array();

    foreach ($response as $video) {
        $createdAt[] = $video['attributes']['created_at'];
    }

    array_multisort($createdAt, SORT_DESC, $response);

      for ($i = 0; $i < count($response); $i++) {
          $string = $response[$i]['attributes']['url'];
          $embedURL = $this->proofAPIUtilities->convertYoutube($string);
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

    $page['#attached']['library'] = 'proof_api/proof_api.commands';

    return $page;
  }

  public function topTenByViews()
  {
    $response = $this->proofAPIRequests->listTopTenByViews();
    $json = json_decode($response, true);
    $dataArray = $json['data'];
    $viewTally = array();
    foreach ($dataArray as $movie) {
      $viewTally[] = $movie['attributes']['view_tally'];
    };
    array_multisort($viewTally, SORT_DESC, $dataArray);
    $dataArray = array_slice($dataArray, 0, 10, true);
    $page = array(
      '#theme' => 'movies',
      '#videos' => $dataArray,
      '#redirectTo' => 'proof_api.top_ten_by_views',
      '#cache' => array
      (
        'max-age' => 300,
      ),
    );
    return $page;
  }

  public function topTenByVotes()
  {
    $response = $this->proofAPIRequests->listTopTenByVotes();
    $json = json_decode($response, true);
    $dataArray = $json['data'];
    $voteTally = array();
    foreach ($dataArray as $movie) {
      $voteTally[] = $movie['attributes']['vote_tally'];
    }
    array_multisort($voteTally, SORT_DESC, $dataArray);
    $dataArray = array_slice($dataArray, 0, 10, true);
    $page = array(
      '#theme' => 'movies',
      '#videos' => $dataArray,
      '#redirectTo' => 'proof_api.top_ten_by_votes',
      '#cache' => array
      (
        'max-age' => 300,
      ),
    );
    return $page;
  }

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

  public function voteUp($videoID, $redirectTo)
  {
    $this->proofAPIRequests->postNewVoteUp($videoID);
    return $this->redirect($redirectTo);
  }

  public function voteDown($videoID, $redirectTo)
  {
    $this->proofAPIRequests->postNewVoteDown($videoID);
    return $this->redirect($redirectTo);
  }

  public function viewVideo($videoID)
  {
    $response = $this->proofAPIRequests->getVideo($videoID);
    $json = json_decode($response, true);
    $url = $json['data']['attributes']['url'];
    return new TrustedRedirectResponse($url);
  }

  public function newView($videoID)
  {
      $this->proofAPIRequests->postNewView($videoID);
      return new Response();
  }

  public function readMessageCallback($method, $mid) {
      $message = proof_api_load_message($mid);

      $response = new AjaxResponse();

      $response->addCommand( new ReadMessageCommand($message));
  }

  public static function create(ContainerInterface $container)
  {
    $proofAPIRequests = $container->get('proof_api.proof_api_requests');
    $proofAPIUtilities = $container->get('proof_api.proof_api_utilities');
    return new static($proofAPIRequests, $proofAPIUtilities);
  }
}