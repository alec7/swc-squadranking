<?php
namespace App;


class Args
{
    public $playerId;

    /**
     * Args constructor.
     */
    public function __construct()
    {
        $this->playerId = config('sod.player_id');
    }


}
