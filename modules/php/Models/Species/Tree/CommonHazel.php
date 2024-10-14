<?php

namespace FOS\Models\Species\tree;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class CommonHazel extends \FOS\Models\Species
{
    public $name = 'Common Hazel';
    public $nb = 4;
    public $tags = [WOODLAND, SHRUB];
    public $cost = 2;

    //whenever you play a card with  [bat]: [1 in card] 
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(BAT, $specie->tags)) $this->takeOneCardEffect();
    }

    //[↴ batcard]
    public function bonus()
    {
        return $this->freePlay(BAT);
    }
}
