<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ForestShuffle implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * forestshuffle.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */
$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'FOS') {
        array_shift($classParts);
        $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            var_dump('Cannot find file : ' . $file);
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

require_once 'modules/php/constants.inc.php';

use FOS\Managers\Players;
use FOS\Managers\Cards;
use FOS\Core\Globals;
use FOS\Core\Preferences;
use FOS\Core\Stats;
use FOS\Core\CheatModule;
// use FOS\Helpers\Log;

class ForestShuffle extends Table
{
    use FOS\DebugTrait;
    use FOS\States\GamesTrait;
    use FOS\States\MulliganTrait;
    use FOS\States\PlayerTurnTrait;
    use FOS\States\PlayAllTrait;
    use FOS\States\FreePlayTrait;
    use FOS\States\HibernateTrait;

    public static $instance = null;
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::$instance = $this;

        // EXPERIMENTAL to avoid deadlocks.  This locks the global table early in the game constructor.
        $this->bSelectGlobalsForUpdate = true;

        self::initGameStateLabels([
            'logging' => 10,
        ]);
        // Stats::checkExistence();
    }

    public static function get()
    {
        return self::$instance;
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "forestshuffle";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        Globals::setupNewGame($players, $options);
        Players::setupNewGame($players, $options);
        Preferences::setupNewGame($players, $this->player_preferences);
        Cards::setupNewGame($players, $options);

        $this->gamestate->setAllPlayersMultiactive();
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    public function getAllDatas()
    {
        $pId = $this->getCurrentPId();
        [$_, $scoreByCards] = $this->getScores();
        return [
            'prefs' => Preferences::getUiData($pId),
            'players' => Players::getUiData($pId),
            'cards' => Cards::getUiData(),
            'scoresByCards' => $scoreByCards,
            'cheatModule' => Globals::isCheatMode() ? CheatModule::getUiData() : null
        ];
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $nbWinter = Cards::countInLocation(W_CARD);
        if ($nbWinter == 1) {
            $base = 33;
            $remaining = 67;
        } else if ($nbWinter == 2) {
            $base = 66;
            $remaining = 34;
        } else {
            $base = 0;
            $remaining = 100;
        }
        $nbCards = Globals::getCardsNumber();
        if (!$nbCards) {
            $nbCards = 161; //magic number
        }
        $remainingDeck = Cards::countInLocation('deck');

        return $base + floor($remaining * ($nbCards - $remainingDeck) / $nbCards);
    }

    function actChangePreference($pref, $value)
    {
        Preferences::set($this->getCurrentPId(), $pref, $value);
    }
    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    /////////////////////////////////////
    //////////// Prevent deadlock ///////
    /////////////////////////////////////

    // Due to deadlock issues involving the playersmultiactive and player tables,
    //   standard tables are queried FOR UPDATE when any operation occurs -- AJAX or refreshing a game table.
    //
    // Otherwise at least two situations have been observed to cause deadlocks:
    //   * Multiple players in a live game with tabs open, two players trading multiactive state back and forth.
    //   * Two players trading multiactive state back and forth, another player refreshes their game page.
    // function queryStandardTables()
    // {
    //     // Query the standard global table.
    //     self::DbQuery('SELECT global_id, global_value FROM global WHERE 1 ORDER BY global_id FOR UPDATE');
    //     // Query the standard player table.
    //     self::DbQuery('SELECT player_id id, player_score score FROM player WHERE 1 ORDER BY player_id FOR UPDATE');
    //     // Query the playermultiactive  table. DO NOT USE THIS is you don't use $this->bIndependantMultiactiveTable=true
    //     // self::DbQuery(
    //     //     'SELECT ma_player_id player_id, ma_is_multiactive player_is_multiactive FROM playermultiactive ORDER BY player_id FOR UPDATE'
    //     // );

    //     // TODO should the stats table be queried as well?
    // }

    /** This is special function called by framework BEFORE any of your action functions */
    // protected function initTable()
    // {
    //     $this->queryStandardTables();
    // }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {

                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === MULTI) {
            if ($statename == "draft") {
                $this->actAutomaticGiveCards($active_player);
                return;
            } else {
                // Make sure player is in a non blocking status for role turn
                $this->gamestate->setPlayerNonMultiactive($active_player, '');

                return;
            }
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
     * upgradeTableDb
     *  - int $from_version : current version of this game database, in numerical form.
     *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
     */
    public function upgradeTableDb($from_version) {}

    /////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public function getCurrentPId()
    {
        return $this->getCurrentPlayerId();
    }

    // Exposing protected method translation
    public function translate($text)
    {
        return self::_($text);
    }

    // Shorthand
    public function getArgs()
    {
        return $this->gamestate->state()['args'];
    }

    public static function getTranslatableText()
    {
        return [
            clienttranslate('Blackberries'),
            clienttranslate('Gain 2 points for each card with a plant symbol'),
            clienttranslate('Bullfinch'),
            clienttranslate('Gain 2 points for each card with an insect symbol'),
            clienttranslate('Camberwell Beauty'),
            clienttranslate('Gain points for each set of different butterflies'),
            clienttranslate('Chaffinch'),
            clienttranslate('5 points if it\'s on a Beech'),
            clienttranslate('Chanterelle'),
            clienttranslate('Whenever you play a card with a tree symbol receive 1 card'),
            clienttranslate('Common Toad'),
            clienttranslate('Up to 2 Common Toads may share this spot'),
            clienttranslate('Gain 5 points if 2 Common Toads share this spot'),
            clienttranslate('Eurasian Jay'),
            clienttranslate('Take another turn after this one'),
            clienttranslate('Gain 3 points'),
            clienttranslate('Fire Salamander'),
            clienttranslate('Play a card with a paw symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Gain points according to the number of Fire Salamander you have'),
            clienttranslate('Fireflies'),
            clienttranslate('Gain points according to the number of Fireflies you have'),
            clienttranslate('Fly Agaric'),
            clienttranslate('Whenever you play a card with a paw symbol receive 1 card'),
            clienttranslate('Goshawk'),
            clienttranslate('Gain 3 points for each card with a bird symbol'),
            clienttranslate('Great Spotted Woodpecker'),
            clienttranslate('Receive 1 card'),
            clienttranslate('Gain 10 points if no other forest has more trees'),
            clienttranslate('Hedgehog'),
            clienttranslate('Gain 2 points for each card with a butterfly symbol'),
            clienttranslate('Large Tortoiseshell'),
            clienttranslate('Mole'),
            clienttranslate('immediately play any number of cards by paying their cost'),
            clienttranslate('Moss'),
            clienttranslate('Gain 10 points if you have at least 10 trees'),
            clienttranslate('Parasol Mushroom'),
            clienttranslate('Whenever you play a card below a tree receive 1 card'),
            clienttranslate('Peacock Butterfly'),
            clienttranslate('Penny Bun'),
            clienttranslate('Whenever you play a card atop a tree receive 1 card'),
            clienttranslate('Pond Turtle'),
            clienttranslate('Purple Emperor'),
            clienttranslate('Red Squirrel'),
            clienttranslate('Gain 5 points if it’s on an Oak'),
            clienttranslate('Silver-Washed Fritillary'),
            clienttranslate('Stag Beetle'),
            clienttranslate("Bechstein's bat"),
            clienttranslate('Play a card with a bird symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Gain 1 point for each card with a paw symbol'),
            clienttranslate('Tawny Owl'),
            clienttranslate('Receive 2 cards'),
            clienttranslate('Gain 5 points'),
            clienttranslate('Tree Ferns'),
            clienttranslate('Gain 6 point for each card with an amphibian symbol'),
            clienttranslate('Tree Frog'),
            clienttranslate('Gain 5 points for each Gnat'),
            clienttranslate('Wild Strawberries'),
            clienttranslate('Gain 10 points if you have all 8 different tree species'),
            clienttranslate('Wood Ant'),
            clienttranslate('Gain 2 points for each card below a tree'),
            clienttranslate('Barbastelle Bat'),
            clienttranslate('Gain 5 points if you have at least 3 different bat species'),
            clienttranslate('Beech Marten'),
            clienttranslate('Gain 5 points per fully occupied tree'),
            clienttranslate('Brown Bear'),
            clienttranslate('Place all cards from the clearing in your cave'),
            clienttranslate('Receive 1 card and take another turn after this one'),
            clienttranslate('Brown Long-Eared Bat'),
            clienttranslate('European Badger'),
            clienttranslate('Play a card with a paw symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Gain 2 points'),
            clienttranslate('European Fat Dormouse'),
            clienttranslate('Gain 15 points if a bat also occupies this tree'),
            clienttranslate('European Hare'),
            clienttranslate('Any number of European Hares may share this spot'),
            clienttranslate('Gain 1 point for each European Hare'),
            clienttranslate('Fallow Deer'),
            clienttranslate('Receive 2 cards'),
            clienttranslate('Gain 3 points for each card with cloven-hoofed animal symbol'),
            clienttranslate('Gnat'),
            clienttranslate('Play any number of bat cards for free'),
            clienttranslate('Gain 1 point for each card with a bat symbol'),
            clienttranslate('Greater Horseshoe Bat'),
            clienttranslate('Gain 5 points if you have at least 3 different bat species'),
            clienttranslate('Lynx'),
            clienttranslate('Gain 10 points if you have at least 1 Roe Deer'),
            clienttranslate('Raccoon'),
            clienttranslate('Place any number of cards from hand in your cave; draw an equal number of cards from the deck'),
            clienttranslate('Red Deer'),
            clienttranslate('Play a card with a deer symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Gain 1 point for each card with a tree or plant symbol'),
            clienttranslate('Red Fox'),
            clienttranslate('Receive 1 card for each European Hare'),
            clienttranslate('Gain 2 points for each European Hare'),
            clienttranslate('Roe Deer'),
            clienttranslate('Gain 3 points for each card with a matching tree symbol'),
            clienttranslate('Squeaker'),
            clienttranslate('Gain 1 point'),
            clienttranslate('Violet Carpenter Bee'),
            clienttranslate('The tree this bee occupies counts as one additional tree of its type'),
            clienttranslate('Wild Boar'),
            clienttranslate('Gain 10 points if you have at least 1 Squeaker'),
            clienttranslate('Wolf'),
            clienttranslate('Receive 1 card for each Deer'),
            clienttranslate('Gain 5 points for each card with a deer symbol'),
            clienttranslate('Linden'),
            clienttranslate('Gain 1 point or 3 points if no other forest has more Linden Trees'),
            clienttranslate('Oak'),
            clienttranslate('Gain 10 points if you have all 8 different tree species'),
            clienttranslate('Silver Fir'),
            clienttranslate('Play a card with a paw symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Gain 2 points for each card attached to this Silver Fir'),
            clienttranslate('Birch'),
            clienttranslate('Gain 1 point'),
            clienttranslate('Beech'),
            clienttranslate('Gain 5 points if you have at least 4 Beeches'),
            clienttranslate('Sycamore'),
            clienttranslate('Gain 1 point for each card with a tree symbol'),
            clienttranslate('Douglas Fir'),
            clienttranslate('Horse Chestnut'),
            clienttranslate('Gain points according to the number of Horse Chestnuts you have'),
            clienttranslate('Paw'),
            clienttranslate('Bat'),
            clienttranslate('Bird'),
            clienttranslate('Cloven-hoofed animal'),
            clienttranslate('Deer'),
            clienttranslate('Insect'),
            clienttranslate('Plant'),
            clienttranslate('Mushroom'),
            clienttranslate('Amphibian'),
            clienttranslate('Tree'),
            //Alpine Shuffle
            clienttranslate('mountain'),
            clienttranslate('Mountain'),
            clienttranslate('Larix decidua'),
            clienttranslate('Play a card with a mountain symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Pinus cembra'),
            clienttranslate('Gain 1 point for each card with a mountain symbol'),
            clienttranslate('Craterellus Cornucopiodes'),
            clienttranslate('Whenever you play a card with a mountain symbol receive 1 card'),
            clienttranslate('Parnassius phoebus'),
            clienttranslate('Gentiana'),
            clienttranslate('Vaccinium myrtillus'),
            clienttranslate('Gain 2 points for each different cloven-hoofed animal'),
            clienttranslate('Ichthyosaura alpestris'),
            clienttranslate('Play a card with a mountain symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Aquila chrysaetos'),
            clienttranslate('Gain 1 point for each card with a paw or amphibian symbol'),
            clienttranslate('Corvus corax'),
            clienttranslate('Capra ibex'),
            clienttranslate('Gain 10 points'),
            clienttranslate('Lepus timidus'),
            clienttranslate('Counts as a European Hare'),
            clienttranslate('Marmota marmota'),
            clienttranslate('Gain 3 points for each different plants'),
            clienttranslate('Rupicapra rupicapra'),
            clienttranslate('Tetrao urogallus'),
            clienttranslate('Gain points according to the number of Ichthyosaura you have'),
            clienttranslate('Place 2 cards from the clearing in your cave'),
            clienttranslate('Gain 1 point for each card in your cave'),
            clienttranslate('Tree sapling'),
            clienttranslate('Hypsugo Savii'),
            clienttranslate('Gypaetus Barbatus'),
            clienttranslate('Leontopodium Nivale'),
            clienttranslate("Gain 2 points for each different bird"),
            clienttranslate("Gain 3 points for each card with a butterfly symbol"),
            clienttranslate("Play a card with a plant symbol for free (you can’t use its effect or bonus)"),
            clienttranslate("Play a card with a amphibian symbol for free (you can’t use its effect or bonus)"),
            clienttranslate("Play a card with a butterfly symbol for free (you can’t use its effect or bonus)"),
            clienttranslate("Play a card with a mountain symbol, and a card with an insect symbol for free (you can’t use its effect or bonus)"),
            clienttranslate('Gain 1 point for each card with a plant symbol'),
            //EDGE
            clienttranslate('Blackthorn'),
            clienttranslate('Common Hazel'),
            clienttranslate('Elderberry'),
            clienttranslate('Whenever you play a card with a butterfly symbol receive 1 card'),
            clienttranslate('Play a card with a butterfly symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Whenever you play a card with a bat symbol receive 1 card'),
            clienttranslate('Play a card with a bat symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Whenever you play a card with a plant symbol receive 1 card'),
            clienttranslate('Play a card with a plant symbol for free (you can’t use its effect or bonus)'),
            clienttranslate('Bee Swarm'),
            clienttranslate('Put all cards with a plant, shrub or tree symbol from the clearing in your cave'),
            clienttranslate('Gain 2 points for each card with an oak or beech symbol'),
            clienttranslate('European Wildcat'),
            clienttranslate('Gain 1 point for each card with a woodland edge symbol'),
            clienttranslate('Take 1 card from the clearing'),
            clienttranslate('Common Pipistrelle'),
            clienttranslate('Crane Fly'),
            clienttranslate('Take all cards with a bat symbol from the clearing into your hand'),
            clienttranslate('European Polecat'),
            clienttranslate('Gain 10 points if this is the only card on a tree or shrub'),
            clienttranslate('Map Butterfly'),
            clienttranslate('Digitalis'),
            clienttranslate('Gain points for different plants'),
            clienttranslate('Stinging Nettle'),
            clienttranslate('Any number of butteflies may share a slot on this tree or shrub'),
            clienttranslate('Gain 1 point for each card with an insect symbol'),
            clienttranslate('Water Vole'),
            clienttranslate('Immediately play any number of cards from hand as tree saplings'),
            clienttranslate('Eurasian Magpie'),
            clienttranslate('Put 2 cards from the clearing into your cave'),
            clienttranslate('Nightingale'),
            clienttranslate('Gain 5 points if it’s on a shrub'),
            clienttranslate('Gain 3 points for each card with a bat symbol'),
            clienttranslate('Barn Owl'),
            clienttranslate('Take another turn after this one if you have at least one bat in your forest'),
            clienttranslate('Woodland Edge'),
            clienttranslate('Shrub'),

            clienttranslate("Wild Boar (Female)"),
            clienttranslate("Beehive"),
            clienttranslate("European Bison"),
            //clienttranslate("Mosquito"),
            clienttranslate("Hazel Doormouse"),
            //clienttranslate("Urtica"),
            clienttranslate("Great Green Bush-Cricket"),
            clienttranslate("European Water Vole"),
            clienttranslate("Remove all cards in the clearing from the game"),
            clienttranslate("Play a squeaker for free"),
            clienttranslate("Gain 10 points for each squeaker"),
        ];
    }
}
