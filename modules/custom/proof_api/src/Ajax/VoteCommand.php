<?php

/**
 * @file
 * Contains \Drupal\proof_api\Ajax\ViewCommand.
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class VoteCommand implements CommandInterface
{
  protected $voteTally;
  protected $voteID;

  /**
   * VoteCommand constructor.
   * @param $voteTally
   * @param $voteID
   */
  public function __construct($voteTally, $voteID) {
    $this->voteTally = $voteTally;
    $this->voteID = $voteID;
  }

  /**
   * @return array
   */
  public function render() {

    return array(
      'command' => 'vote',
      'voteTally' => $this->voteTally,
      'voteID' => $this->voteID,
    );
  }
}