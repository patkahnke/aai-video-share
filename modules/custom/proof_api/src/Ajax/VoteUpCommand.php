<?php
/**
 * Created by PhpStorm.
 * User: patrickkahnke
 * Date: 9/13/16
 * Time: 3:16 PM
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class VoteUpCommand implements CommandInterface
{
  protected $voteTally;
  protected $votesID;

  public function __construct($voteTally, $votesID) {
    $this->voteTally = $voteTally;
    $this->votesID = $votesID;
  }
  public function render() {

    return array(
      'command' => 'voteUp',
      'voteTally' => $this->voteTally,
      'votesID' => $this->votesID,
    );
  }
}