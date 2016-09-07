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

    function checkVideoOrigin($url) {
        $videoType = null;

        if (preg_match('/youtu/', $url)) {
            $videoType = 'youtube';
        } else if (preg_match('/vimeo/', $url)) {
            $videoType = 'vimeo';
        }
        return $videoType;
    }

    function convertYoutube($url) {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "www.youtube.com/embed/$2",
            $url);
    }

    function convertVimeo($url) {
        $vimeoID = preg_replace('#^https?://vimeo.com/#', '', $url);
        $embedURL = 'player.vimeo.com/video/' . $vimeoID;

        return $embedURL;
    }

    function convertToEmbedURL($url) {
        $videoType = $this->checkVideoOrigin($url);
        $embedURL = null;

        if ($videoType === 'youtube') {
            $embedURL = $this->convertYoutube($url);
        } else if ($videoType === 'vimeo') {
            $embedURL = $this->convertVimeo($url);
        }

        return $embedURL;
    }
}