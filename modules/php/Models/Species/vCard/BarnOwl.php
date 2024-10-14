<?php

namespace FOS\Models\Species\vCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class BarnOwl extends \FOS\Models\Species
{
    public $name = 'Barn Owl';
    public $nb = 2;
    public $tags = [WOODLAND, BIRD];
    public $cost = 2;

    //[action ⟳] if at least one bat 
    public function effect()
    {
        if (Players::get($this->card->getState())->countInForest('tags', BAT) > 0) {
            return $this->playAgain();
        }
    }

    //
    public function bonus()
    {
    }

    //3 [points] x [bat] 
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 3, BAT);
    }
}
