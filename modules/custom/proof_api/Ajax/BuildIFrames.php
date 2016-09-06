<?php

//adapted from mike-miles.com

namespace Drupal\proof_api\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class BuildIFramesCommand implements CommandInterface {

    protected $page;

    //Constructs a BuildIFramesCommand object
    public function __construct($page) {
        $this->page = $page;
    }

    //Implements Drupal\Core\Ajax\CommandInterface:render()
    public function render() {
        return array(
            'command' => 'buildIFrames',
            'content' => $this->page->content,
        );
    }
}