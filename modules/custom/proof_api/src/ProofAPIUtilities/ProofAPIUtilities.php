<?php

/**
 * @file
 * Contains \Drupal\proof_api\ProofAPIUtilities\ProofAPIUtilities.
 */

namespace Drupal\proof_api\ProofAPIUtilities;

/**
 * A service that provides helpful functions to be accessed by the proof_api module.
 * @todo refactor more functions from the ProofAPIController to this service.
 */
class ProofAPIUtilities {

  /**
   * Parses two provided urls and checks to see if they match each other (accounting for "http" and "https").
   * @param $url1
   * @param $url2
   * @return bool
   */
  public function urlsMatch($url1, $url2) {
    $urlMatches = FALSE;
    $url1 = preg_replace('#^https?://#', '', $url1);
    $url2 = preg_replace('#^https?://#', '', $url2);
    if ($url1 === $url2) {
      $urlMatches = TRUE;
    };
    return $urlMatches;
  }

  /**
   * Checks to see if two provided slugs match each other.
   * @param $slug1
   * @param $slug2
   * @return bool
   */
  public function slugsMatch($slug1, $slug2) {
    $slugMatches = false;
    if ($slug1 === $slug2) {
      $slugMatches = true;
    };
    return $slugMatches;
  }

  /**
   * Calls the slugsMatch and urlsMatch functions to see if two provided video entries match each other.
   * @param $newUrl
   * @param $newSlug
   * @param $response
   * @return bool
   */
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

  /**
   * Parses a url to see whether it came from a video source that this module supports (currently Youtube and Vimeo).
   * @param $url
   * @return null|string
   */
  function checkVideoOrigin($url) {
        $videoType = null;

        if (preg_match('/youtu/', $url)) {
            $videoType = 'youtube';
        } else if (preg_match('/vimeo/', $url)) {
            $videoType = 'vimeo';
        }
        return $videoType;
    }

  /**
   * Converts a Youtube url to an embeddable Youtube url.
   * @param $url
   * @return mixed
   */
  function convertYoutube($url) {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "www.youtube.com/embed/$2",
            $url);
    }

  /**
   * Converts a Vimeo url to an embeddable Vimeo url.
   * @param $url
   * @return string
   */
  function convertVimeo($url) {
        $vimeoID = preg_replace('#^https?://vimeo.com/#', '', $url);
        $embedURL = 'player.vimeo.com/video/' . $vimeoID;

        return $embedURL;
    }

  /**
   * Accepts a supported url (currently Youtube and Vimeo - as determined by checkVideoOrigin) and calls the appropriate
   *  conversion function for the url.
   * @param $url
   * @return mixed|null|string
   */
  function convertToEmbedURL($url) {
        $videoType = $this->checkVideoOrigin($url);
        $embedURL = null;

        if ($videoType === 'youtube') {
            $embedURL = $this->convertYoutube($url);
        } else if ($videoType === 'vimeo') {
            $embedURL = $this->convertVimeo($url);
        }

        $urlAttributes = '?wmode=opaque&frameborder="0"style="border: solid 4px #37474F"allowfullscreen="allowfullscreen"';
        $embedURL = $embedURL . $urlAttributes;

        return $embedURL;
    }
}