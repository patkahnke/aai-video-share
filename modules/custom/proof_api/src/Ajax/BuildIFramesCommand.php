<?php

/**
 * @file
 * Contains \Drupal\proof_api\Ajax\BuildIFramesCommand.
 *
 *
 * NOTE: This class is still in development - not currently being used
 * adapted from mike-miles.com
 * @todo complete work on this Ajax callback function route
 */

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class BuildIFramesCommand implements CommandInterface {

    protected $page;

  /**
   * BuildIFramesCommand constructor.
   * @param $page
   */
  public function __construct($page) {
        $this->page = $page;
    }

  /**
   * Implements Drupal\Core\Ajax\CommandInterface:render()
   * @return array
   */
  public function render() {
        return array(
            'command' => 'buildIFrames',
            'content' => $this->page,
        );
    }
}