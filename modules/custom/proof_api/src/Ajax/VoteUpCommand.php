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

  public function __construct($voteTally) {
    $this->voteTally = $voteTally;
  }
  public function render() {

    return array(
      'command' => 'voteUp',
      'voteTally' => $this->voteTally,
    );
  }
}