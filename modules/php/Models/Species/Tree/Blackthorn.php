<?php

namespace FOS\Models\Species\tree;

use FOS\Core\Notifications;
use FOS\Managers\Players;
use FOS\Managers\Cards;

/*
 * Beech
 */

class Blackthorn extends \FOS\Models\Species
{
    public $name = 'Blackthorn';
    public $nb = 4;
    public $tags = [WOODLAND, SHRUB];
    public $cost = 2;

    //whenever you play a card with  [butterfly]: [1 in card] 
    public function specialEffect($card, $specie, $position)
    {
        if (in_array(BUTTERFLY, $specie->tags)) $this->takeOneCardEffect();
    }

    //[↴ butterflycard]
    public function bonus()
    {
        return $this->freePlay(BUTTERFLY);
    }
}
