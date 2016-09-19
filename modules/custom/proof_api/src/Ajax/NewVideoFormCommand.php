<?php

/**
 * @file
 * Contains \Drupal\proof_api\Ajax\NewVideoFormCommand.
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class NewVideoFormCommand implements CommandInterface
{
  /**
   * @return array
   */
  public function render() {

    return array(
      'command' => 'newVideoForm',
    );
  }
}