<?php

/**
 * @file
 * Contains \Drupal\proof_api\Ajax\ViewCommand.
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class ViewCommand implements CommandInterface
{
  protected $viewTally;
  protected $viewID;

  /**
   * ViewCommand constructor.
   * @param $viewTally
   * @param $viewID
   */
  public function __construct($viewTally, $viewID) {
    $this->viewTally = $viewTally;
    $this->viewID = $viewID;
  }

  /**
   * @return array
   */
  public function render() {

    return array(
      'command' => 'view',
      'viewTally' => $this->viewTally,
      'viewID' => $this->viewID,
    );
  }
}