<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class WildBoarFemale extends \FOS\Models\Species
{
    public $name = 'Wild Boar (Female)';
    public $nb = 3;
    public $tags = [WOODLAND, CLOVEN];
    public $cost = 2;

    //Remove all cards from the clearing from the game 
    public function effect()
    {
        $player = Players::getActive();
        $player->addActionToPendingAction(DISCARD_ALL);
        return DISCARD_ALL;
    }

    //[ card ↴] squeaker
    public function bonus()
    {
        return $this->freePlay(SQUEAKER);
    }

    //10 [points] per squeaker
    public function score($playerId, $forests)
    {
        return $this->pointsByName($playerId, 10, 'Squeaker');
    }
}
