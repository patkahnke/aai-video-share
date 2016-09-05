<?php
/**
 * Created by PhpStorm.
 * User: patrickkahnke
 * Date: 9/2/16
 * Time: 12:55 AM
 */

namespace Drupal\proof_api\ProofAPIUtilities;

class ProofAPIUtilities {

  public function urlsMatch($url1, $url2) {
    $urlMatches = FALSE;
    $url1 = preg_replace('#^https?://#', '', $url1);
    $url2 = preg_replace('#^https?://#', '', $url2);
    if ($url1 === $url2) {
      $urlMatches = TRUE;
    };
    return $urlMatches;
  }

  public function slugsMatch($slug1, $slug2) {
    $slugMatches = false;
    if ($slug1 === $slug2) {
      $slugMatches = true;
    };
    return $slugMatches;
  }

  public function videosMatch($newUrl, $newSlug, $response) {
    $videoMatches = false;
    foreach ($response as $video) {
      $url1 = $newUrl;
      $url2 = $video['attributes']['url'];
      $slug1 = $newSlug;
      $slug2 = $video['attributes']['slug'];
      if ($this->urlsMatch($url1, $url2) || $this->slugsMatch($slug1, $slug2)) {
        $videoMatches = TRUE;
      };
    };
    return $videoMatches;
  }

  public function getEmbedCode($url) {

  }
}