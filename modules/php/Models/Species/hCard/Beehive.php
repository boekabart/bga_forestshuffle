<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Beehive extends \FOS\Models\Species
{
    public $name = BEEHIVE;
    public $nb = 3;
    public $tags = [WOODLAND, INSECT];
    public $cost = 1;

    //Place all [plants], [shrubs] and [trees] from the clearing in your cave
    public function effect()
    {
        return $this->takeCardsFromClearing(0, [PLANT, SHRUB, TREE], CAVE, BEEHIVE);
    }

    //
    public function bonus()
    {
    }

    //1 [points] x [plant]
    public function score($playerId, $forests)
    {
        return $this->pointsByTag($playerId, 1, PLANT);
    }
}
