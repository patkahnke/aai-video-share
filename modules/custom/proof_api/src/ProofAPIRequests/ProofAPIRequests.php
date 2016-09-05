<?php

/*
Make all requests to Proof API
*/

namespace Drupal\proof_api\ProofAPIRequests;

class ProofAPIRequests
{
    public function listAllVideos()
    {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, 'https://proofapi.herokuapp.com/videos?page&per_page');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);

      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-Auth-Token: kFDTf2t7HVfA24Red68sE31K'
      ));

      $response = curl_exec($ch);
      curl_close($ch);

      $json = json_decode($response, true);
      $response = $json['data'];

      return $response;
    }

    public function listTopTenByViews()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://proofapi.herokuapp.com/videos?page&per_page');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Auth-Token: kFDTf2t7HVfA24Red68sE31K'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function listTopTenByVotes()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://proofapi.herokuapp.com/videos?page&per_page');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Auth-Token: kFDTf2t7HVfA24Red68sE31K'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function postNewMovie($title, $url, $slug)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://proofapi.herokuapp.com/videos");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
            \"title\": \"{$title}\",
            \"url\": \"{$url}\",
            \"slug\": \"{$slug}\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth-Token: kFDTf2t7HVfA24Red68sE31K"
        ));

        curl_exec($ch);
        curl_close($ch);
    }

    public function postNewVoteUp($videoID)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://proofapi.herokuapp.com/videos/{$videoID}/votes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"opinion\": 1
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth-Token: kFDTf2t7HVfA24Red68sE31K"
        ));

        curl_exec($ch);
        curl_close($ch);
    }

    public function postNewVoteDown($videoID)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://proofapi.herokuapp.com/videos/{$videoID}/votes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"opinion\": -1
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth-Token: kFDTf2t7HVfA24Red68sE31K"
        ));

        curl_exec($ch);
        curl_close($ch);
    }


    public function postNewView($videoID)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://proofapi.herokuapp.com/views");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{
        \"video_id\": \"$videoID\"
        }");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth-Token: kFDTf2t7HVfA24Red68sE31K"
        ));

        curl_exec($ch);
        curl_close($ch);
    }

    public function getVideo($videoID)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://proofapi.herokuapp.com/videos/{$videoID}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "X-Auth-Token: kFDTf2t7HVfA24Red68sE31K"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

