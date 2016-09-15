<?php
/**
 * Created by PhpStorm.
 * User: patrickkahnke
 * Date: 9/13/16
 * Time: 3:16 PM
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class VoteCommand implements CommandInterface
{
  protected $voteTally;
  protected $voteID;

  public function __construct($voteTally, $voteID) {
    $this->voteTally = $voteTally;
    $this->voteID = $voteID;
  }
  public function render() {

    return array(
      'command' => 'vote',
      'voteTally' => $this->voteTally,
      'voteID' => $this->voteID,
    );
  }
}