<?php

namespace FOS\Models\Species\hCard;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Common Pipistrelle
 */

class CommonPipistrelle extends \FOS\Models\Species
{
    public $name = 'Common Pipistrelle';
    public $nb = 3;
    public $tags = [WOODLAND, BAT];
    public $cost = 1;

    //
    public function effect()
    {
    }

    //
    public function bonus()
    {
    }

    //5 [points] if at least 3 different [bat]
    public function score($playerId, $forests)
    {
        return $this->countBats($playerId);
    }
}
