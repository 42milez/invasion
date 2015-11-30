<?php namespace Invasion\Object;

class Bullet extends Object {
    private $actorId;

    function __construct($actorId) {
        $this->actorId = $actorId;
        parent::__construct();
    }

    function getActorId() {
        return $this->actorId;
    }
}
