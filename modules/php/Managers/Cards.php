<?php

namespace FOS\Managers;

use FOS\Core\Game;
use FOS\Core\Globals;
use FOS\Helpers\Utils;
use FOS\Helpers\Collection;
use FOS\Core\Notifications;
use FOS\Core\Stats;

/* Class to manage all the Cards for Fos */

class Cards extends \FOS\Helpers\Pieces
{
    protected static $table = 'cards';
    protected static $prefix = 'card_';
    protected static $autoIncrement = true;
    protected static $autoremovePrefix = false;
    protected static $customFields = ['extra_datas', 'tree', 'position'];

    protected static $autoreshuffle = false;

    protected static function cast($row)
    {
        $data = self::getCards()[$row[static::$prefix . 'id']];
        return new \FOS\Models\Card($row, $data);
    }

    public static function getUiData()
    {

        return [
            'deck_count' => static::countInLocation('deck'),
            'discard_count' => static::countInLocation('discard'),
            'clearing' => static::getInLocation('clearing'),
            'winterCards' => static::getInLocation(W_CARD)->getIds()
        ];
    }

    /* Creation of the Cards */
    public static function setupNewGame($players, $options)
    {
        $cards = [];
        // Create the deck
        foreach (self::getCards() as $id => $card) {

            $cards[] = [
                'card_id' => $id,
                'location' => $card['deck'],
                'position' => 0
            ];
        }

        static::create($cards);

        $cardToRemove = Globals::isDraftMode() ? CARD_TO_REMOVE_DRAFT : CARD_TO_REMOVE;

        $expansionNb = 0;

        if (Globals::isAlpine()) {
            static::moveAllInLocation(ALPINE_DECK, 'predeck');
            $expansionNb++;
        }
        //TODO to handle 2 expansions
        if (Globals::isEdge()) {
            static::moveAllInLocation(EDGE_DECK, 'predeck');
            $expansionNb++;
        }

        //then add all basic cards to predeck
        static::moveAllInLocation(BASIC_DECK, 'predeck');

        static::shuffle('predeck');

        static::pickForLocation($cardToRemove[$expansionNb][count($players)], 'predeck', 'trash');


        static::pickForLocation(intdiv(static::countInLocation('predeck'), 3), 'predeck', 'deck');
        static::pickForLocation(2, 'deckW', 'deck');
        static::shuffle('deck');
        $card = static::getTopOf('deckW');
        static::insertOnTop($card->getId(), 'deck');

        while (static::countInLocation('predeck') > 0) {
            $card = static::getTopOf('predeck');
            static::insertOnTop($card->getId(), 'deck');
        }

        //draft mode or not
        if (Globals::isDraftMode()) {
            $nbCards = DRAFT[count($players)]['draw'];
            foreach ($players as $pId => $player) {
                static::pickForLocation($nbCards, 'deck', 'draft', $pId);
            }
        } else {
            $nbCards = 6;
            foreach ($players as $pId => $player) {
                static::pickForLocation($nbCards, 'deck', 'hand', $pId);
            }
        }

        Globals::setCardsNumber(static::countInLocation('deck'));
    }

    public static function getAllOfTypeFromClearing($types)
    {
        $cards = Cards::getInLocation(CLEARING);
        $matchingCardsIds = [];
        foreach ($cards as $cardId => $card) {
            foreach ($card->getSpecies() as $specie) {
                foreach ($types as $type) {
                    if ($specie->is($type)) {
                        $matchingCardsIds[] = $cardId;
                        break;
                    }
                }
                if (in_array($cardId, $matchingCardsIds)) {
                    break;
                }
            }
        }
        return $matchingCardsIds;
    }

    public static function pickCard()
    {
        $card = null;
        while ($card == null) {
            $card = static::getTopOf('deck');
            //if deck was already empty break
            if ($card == null) break;

            if ($card->getType() == W_CARD) {
                $cardId = $card->getId();
                static::move($cardId, W_CARD);
                Notifications::newWinterCard($cardId);

                $card = null;

                if (Cards::countInLocation(W_CARD) == 3) {
                    break;
                }
            }
        }
        return $card;
    }

    public static function getCards()
    {
        $f = function ($data) {
            return [
                'type' => $data[0],
                'species' => $data[1],
                'tree_symbol' => $data[2],
                'deck' => $data[3]
            ];
        };

        return [
            1 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            2 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            3 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            4 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            5 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            6 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            7 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            8 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            9 => $f([TREE, [LINDEN], [LINDEN], BASIC_DECK]),
            10 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            11 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            12 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            13 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            14 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            15 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            16 => $f([TREE, [OAK], [OAK], BASIC_DECK]),
            17 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            18 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            19 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            20 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            21 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            22 => $f([TREE, [SILVER_FIR], [SILVER_FIR], BASIC_DECK]),
            23 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            24 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            25 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            26 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            27 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            28 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            29 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            30 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            31 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            32 => $f([TREE, [BIRCH], [BIRCH], BASIC_DECK]),
            33 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            34 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            35 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            36 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            37 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            38 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            39 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            40 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            41 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            42 => $f([TREE, [BEECH], [BEECH], BASIC_DECK]),
            43 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            44 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            45 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            46 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            47 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            48 => $f([TREE, [SYCAMORE], [SYCAMORE], BASIC_DECK]),
            49 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            50 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            51 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            52 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            53 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            54 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            55 => $f([TREE, [DOUGLAS_FIR], [DOUGLAS_FIR], BASIC_DECK]),
            56 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            57 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            58 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            59 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            60 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            61 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            62 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            63 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            64 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            65 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            66 => $f([TREE, [HORSE_CHESTNUT], [HORSE_CHESTNUT], BASIC_DECK]),
            67 => $f([W_CARD, [''], [''], 'deckW']),
            68 => $f([W_CARD, [''], [''], 'deckW']),
            69 => $f([W_CARD, [''], [''], 'deckW']),
            70 => $f([H_CARD, [EUROPEAN_HARE, EUROPEAN_BADGER], [LINDEN, DOUGLAS_FIR], BASIC_DECK]),
            71 => $f([H_CARD, [EUROPEAN_HARE, GREATER_HORSESHOE_BAT], [OAK, LINDEN], BASIC_DECK]),
            72 => $f([H_CARD, [EUROPEAN_HARE, RED_FOX], [SILVER_FIR, OAK], BASIC_DECK]),
            73 => $f([H_CARD, [RACCOON, EUROPEAN_HARE], [DOUGLAS_FIR, SYCAMORE], BASIC_DECK]),
            74 => $f([H_CARD, [WILD_BOAR, EUROPEAN_HARE], [SYCAMORE, SILVER_FIR], BASIC_DECK]),
            75 => $f([H_CARD, ['Brown Long-Eared Bat', EUROPEAN_HARE], [SYCAMORE, LINDEN], BASIC_DECK]),
            76 => $f([H_CARD, [RACCOON, ROE_DEER], [SILVER_FIR, BEECH], BASIC_DECK]),
            77 => $f([H_CARD, ['Brown Long-Eared Bat', EUROPEAN_BADGER], [SYCAMORE, DOUGLAS_FIR], BASIC_DECK]),
            78 => $f([H_CARD, [BARBASTELLE_BAT, WILD_BOAR], [HORSE_CHESTNUT, OAK], BASIC_DECK]),
            79 => $f([H_CARD, [BROWN_BEAR, RACCOON], [LINDEN, SILVER_FIR], BASIC_DECK]),
            80 => $f([H_CARD, [BEECH_MARTEN, BROWN_BEAR], [SYCAMORE, HORSE_CHESTNUT], BASIC_DECK]),
            81 => $f([H_CARD, [RED_DEER, BROWN_BEAR], [LINDEN, BEECH], BASIC_DECK]),
            82 => $f([H_CARD, [BARBASTELLE_BAT, BEECH_MARTEN], [SILVER_FIR, HORSE_CHESTNUT], BASIC_DECK]),
            83 => $f([H_CARD, [LYNX, EUROPEAN_HARE], [DOUGLAS_FIR, BIRCH], BASIC_DECK]),
            84 => $f([H_CARD, [WILD_BOAR, BEECH_MARTEN], [BIRCH, OAK], BASIC_DECK]),
            85 => $f([H_CARD, [EUROPEAN_BADGER, GNAT], [HORSE_CHESTNUT, OAK], BASIC_DECK]),
            86 => $f([H_CARD, [RED_FOX, VIOLET_CARPENTER_BEE], [LINDEN, DOUGLAS_FIR], BASIC_DECK]),
            87 => $f([H_CARD, [WILD_BOAR, ROE_DEER], [SYCAMORE, HORSE_CHESTNUT], BASIC_DECK]),
            88 => $f([H_CARD, [FALLOW_DEER, WILD_BOAR], [LINDEN, DOUGLAS_FIR], BASIC_DECK]),
            89 => $f([H_CARD, [FALLOW_DEER, ROE_DEER], [LINDEN, BIRCH], BASIC_DECK]),
            90 => $f([H_CARD, [RED_DEER, FALLOW_DEER], [SILVER_FIR, SYCAMORE], BASIC_DECK]),
            91 => $f([H_CARD, [VIOLET_CARPENTER_BEE, LYNX], [DOUGLAS_FIR, BEECH], BASIC_DECK]),
            92 => $f([H_CARD, [EUROPEAN_FAT_DORMOUSE, BARBASTELLE_BAT], [BEECH, OAK], BASIC_DECK]),
            93 => $f([H_CARD, [GREATER_HORSESHOE_BAT, EUROPEAN_FAT_DORMOUSE], [BEECH, DOUGLAS_FIR], BASIC_DECK]),
            94 => $f([H_CARD, [RED_FOX, WOLF], [LINDEN, SILVER_FIR], BASIC_DECK]),
            95 => $f([H_CARD, [EUROPEAN_FAT_DORMOUSE, 'Brown Long-Eared Bat'], [SILVER_FIR, BEECH], BASIC_DECK]),
            96 => $f([H_CARD, [BECHSTEIN, EUROPEAN_FAT_DORMOUSE], [BEECH, OAK], BASIC_DECK]),
            97 => $f([H_CARD, [GNAT, VIOLET_CARPENTER_BEE], [BIRCH, DOUGLAS_FIR], BASIC_DECK]),
            98 => $f([H_CARD, [WOLF, GNAT], [DOUGLAS_FIR, HORSE_CHESTNUT], BASIC_DECK]),
            99 => $f([H_CARD, [ROE_DEER, SQUEAKER], [LINDEN, SYCAMORE], BASIC_DECK]),
            100 => $f([H_CARD, [BECHSTEIN, WOLF], [OAK, SILVER_FIR], BASIC_DECK]),
            101 => $f([H_CARD, [ROE_DEER, LYNX], [SILVER_FIR, LINDEN], BASIC_DECK]),
            102 => $f([H_CARD, [BEECH_MARTEN, BECHSTEIN], [BEECH, BIRCH], BASIC_DECK]),
            103 => $f([H_CARD, [EUROPEAN_HARE, RED_DEER], [BEECH, HORSE_CHESTNUT], BASIC_DECK]),
            104 => $f([H_CARD, [WOLF, GREATER_HORSESHOE_BAT], [SYCAMORE, LINDEN], BASIC_DECK]),
            105 => $f([H_CARD, [SQUEAKER, RED_DEER], [HORSE_CHESTNUT, OAK], BASIC_DECK]),
            106 => $f([H_CARD, [RED_FOX, SQUEAKER], [BEECH, OAK], BASIC_DECK]),
            107 => $f([H_CARD, [LYNX, RACCOON], [DOUGLAS_FIR, BIRCH], BASIC_DECK]),
            108 => $f([H_CARD, [SQUEAKER, LYNX], [OAK, SILVER_FIR], BASIC_DECK]),
            109 => $f([H_CARD, [EUROPEAN_HARE, BEECH_MARTEN], [BIRCH, HORSE_CHESTNUT], BASIC_DECK]),
            110 => $f([H_CARD, [LYNX, RED_FOX], [HORSE_CHESTNUT, DOUGLAS_FIR], BASIC_DECK]),
            111 => $f([H_CARD, [EUROPEAN_HARE, RED_DEER], [BIRCH, HORSE_CHESTNUT], BASIC_DECK]),
            112 => $f([H_CARD, [VIOLET_CARPENTER_BEE, EUROPEAN_HARE], [SILVER_FIR, SYCAMORE], BASIC_DECK]),
            113 => $f([H_CARD, [EUROPEAN_BADGER, FALLOW_DEER], [HORSE_CHESTNUT, BIRCH], BASIC_DECK]),
            114 => $f([V_CARD, [GOSHAWK, MOSS], [DOUGLAS_FIR, LINDEN], BASIC_DECK]),
            115 => $f([V_CARD, [GREAT_SPOTTED_WOODPECKER, WOOD_ANT], [LINDEN, BIRCH], BASIC_DECK]),
            116 => $f([V_CARD, [CHAFFINCH, WOOD_ANT], [BIRCH, BEECH], BASIC_DECK]),
            117 => $f([V_CARD, [TAWNY_OWL, STAG_BEETLE], [BEECH, SYCAMORE], BASIC_DECK]),
            118 => $f([V_CARD, ['Silver-Washed Fritillary', FIRE_SALAMANDER], [OAK, HORSE_CHESTNUT], BASIC_DECK]),
            119 => $f([V_CARD, [PURPLE_EMPEROR, POND_TURTLE], [HORSE_CHESTNUT, SYCAMORE], BASIC_DECK]),
            120 => $f([V_CARD, [CAMBERWELL_BEAUTY, POND_TURTLE], [SYCAMORE, BIRCH], BASIC_DECK]),
            121 => $f([V_CARD, [LARGE_TORTOISESHELL, FIRE_SALAMANDER], [SILVER_FIR, DOUGLAS_FIR], BASIC_DECK]),
            122 => $f([V_CARD, [BULLFINCH, TREE_FROG], [DOUGLAS_FIR, LINDEN], BASIC_DECK]),
            123 => $f([V_CARD, [CHAFFINCH, STAG_BEETLE], [SYCAMORE, BIRCH], BASIC_DECK]),
            124 => $f([V_CARD, [GOSHAWK, WOOD_ANT], [SILVER_FIR, BEECH], BASIC_DECK]),
            125 => $f([V_CARD, [GREAT_SPOTTED_WOODPECKER, COMMON_TOAD], [LINDEN, OAK], BASIC_DECK]),
            126 => $f([V_CARD, [EURASIAN_JAY, TREE_FERNS], [BIRCH, HORSE_CHESTNUT], BASIC_DECK]),
            127 => $f([V_CARD, [TAWNY_OWL, WILD_STRAWBERRIES], [BEECH, SYCAMORE], BASIC_DECK]),
            128 => $f([V_CARD, ['Silver-Washed Fritillary', BLACKBERRIES], [OAK, SILVER_FIR], BASIC_DECK]),
            129 => $f([V_CARD, [PURPLE_EMPEROR, MOSS], [HORSE_CHESTNUT, DOUGLAS_FIR], BASIC_DECK]),
            130 => $f([V_CARD, [CAMBERWELL_BEAUTY, FIREFLIES], [SYCAMORE, LINDEN], BASIC_DECK]),
            131 => $f([V_CARD, [LARGE_TORTOISESHELL, BLACKBERRIES], [SILVER_FIR, BIRCH], BASIC_DECK]),
            132 => $f([V_CARD, [BULLFINCH, HEDGEHOG], [DOUGLAS_FIR, BEECH], BASIC_DECK]),
            133 => $f([V_CARD, [PEACOCK_BUTTERFLY, HEDGEHOG], [SILVER_FIR, OAK], BASIC_DECK]),
            134 => $f([V_CARD, [RED_SQUIRREL, COMMON_TOAD], [DOUGLAS_FIR, HORSE_CHESTNUT], BASIC_DECK]),
            135 => $f([V_CARD, [RED_SQUIRREL, FIREFLIES], [HORSE_CHESTNUT, SYCAMORE], BASIC_DECK]),
            136 => $f([V_CARD, [CHAFFINCH, COMMON_TOAD], [BEECH, SILVER_FIR], BASIC_DECK]),
            137 => $f([V_CARD, [EURASIAN_JAY, FIREFLIES], [BIRCH, DOUGLAS_FIR], BASIC_DECK]),
            138 => $f([V_CARD, ['Silver-Washed Fritillary', MOSS], [BEECH, LINDEN], BASIC_DECK]),
            139 => $f([V_CARD, [PEACOCK_BUTTERFLY, CHANTERELLE], [OAK, SILVER_FIR], BASIC_DECK]),
            140 => $f([V_CARD, [PEACOCK_BUTTERFLY, FIREFLIES], [HORSE_CHESTNUT, BEECH], BASIC_DECK]),
            141 => $f([V_CARD, [LARGE_TORTOISESHELL, MOLE], [SYCAMORE, OAK], BASIC_DECK]),
            142 => $f([V_CARD, [GOSHAWK, HEDGEHOG], [SILVER_FIR, HORSE_CHESTNUT], BASIC_DECK]),
            143 => $f([V_CARD, [GREAT_SPOTTED_WOODPECKER, WILD_STRAWBERRIES], [DOUGLAS_FIR, SYCAMORE], BASIC_DECK]),
            144 => $f([V_CARD, [EURASIAN_JAY, FLY_AGARIC], [SYCAMORE, SILVER_FIR], BASIC_DECK]),
            145 => $f([V_CARD, [TAWNY_OWL, PENNY_BUN], [BIRCH, DOUGLAS_FIR], BASIC_DECK]),
            146 => $f([V_CARD, [RED_SQUIRREL, FIRE_SALAMANDER], [BEECH, LINDEN], BASIC_DECK]),
            147 => $f([V_CARD, [PURPLE_EMPEROR, TREE_FROG], [BIRCH, OAK], BASIC_DECK]),
            148 => $f([V_CARD, [PEACOCK_BUTTERFLY, COMMON_TOAD], [LINDEN, BEECH], BASIC_DECK]),
            149 => $f([V_CARD, [CAMBERWELL_BEAUTY, TREE_FROG], [BIRCH, OAK], BASIC_DECK]),
            150 => $f([V_CARD, [BULLFINCH, PARASOL_MUSHROOM], [DOUGLAS_FIR, HORSE_CHESTNUT], BASIC_DECK]),
            151 => $f([V_CARD, [GOSHAWK, COMMON_TOAD], [OAK, SYCAMORE], BASIC_DECK]),
            152 => $f([V_CARD, [EURASIAN_JAY, TREE_FERNS], [HORSE_CHESTNUT, SILVER_FIR], BASIC_DECK]),
            153 => $f([V_CARD, [TAWNY_OWL, COMMON_TOAD], [SYCAMORE, DOUGLAS_FIR], BASIC_DECK]),
            154 => $f([V_CARD, [BULLFINCH, TREE_FERNS], [SILVER_FIR, LINDEN], BASIC_DECK]),
            155 => $f([V_CARD, [RED_SQUIRREL, WILD_STRAWBERRIES], [OAK, BIRCH], BASIC_DECK]),
            156 => $f([V_CARD, ['Silver-Washed Fritillary', BLACKBERRIES], [OAK, BEECH], BASIC_DECK]),
            157 => $f([V_CARD, [PURPLE_EMPEROR, FLY_AGARIC], [LINDEN, OAK], BASIC_DECK]),
            158 => $f([V_CARD, [CAMBERWELL_BEAUTY, CHANTERELLE], [HORSE_CHESTNUT, BIRCH], BASIC_DECK]),
            159 => $f([V_CARD, [LARGE_TORTOISESHELL, MOLE], [BEECH, SYCAMORE], BASIC_DECK]),
            160 => $f([V_CARD, [CHAFFINCH, PARASOL_MUSHROOM], [SYCAMORE, SILVER_FIR], BASIC_DECK]),
            161 => $f([V_CARD, [GREAT_SPOTTED_WOODPECKER, PENNY_BUN], [LINDEN, DOUGLAS_FIR], BASIC_DECK]),
            //Alpine Shuffle from here
            162 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            163 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            164 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            165 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            166 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            167 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            168 => $f([TREE, [LARIX_DECIDUA], [LARIX], ALPINE_DECK]),
            169 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            170 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            171 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            172 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            173 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            174 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            175 => $f([TREE, [PINUS_CEMBRA], [PINUS], ALPINE_DECK]),
            176 => $f([H_CARD, [MARMOTA_MARMOTA, RUPICAPRA_RUPICAPRA], [BEECH, PINUS], ALPINE_DECK]),
            177 => $f([H_CARD, [MARMOTA_MARMOTA, TETRAO_UROGALLUS], [LARIX, DOUGLAS_FIR], ALPINE_DECK]),
            178 => $f([H_CARD, [LEPUS_TIMIDUS, MARMOTA_MARMOTA], [SILVER_FIR, PINUS], ALPINE_DECK]),
            179 => $f([H_CARD, [CAPRA_IBEX, MARMOTA_MARMOTA], [LARIX, BIRCH], ALPINE_DECK]),
            180 => $f([H_CARD, [RUPICAPRA_RUPICAPRA, TETRAO_UROGALLUS], [LARIX, BEECH], ALPINE_DECK]),
            181 => $f([H_CARD, [LEPUS_TIMIDUS, RUPICAPRA_RUPICAPRA], [LARIX, DOUGLAS_FIR], ALPINE_DECK]),
            182 => $f([H_CARD, [HYPSUGO_SAVII, CAPRA_IBEX], [SILVER_FIR, PINUS], ALPINE_DECK]),
            183 => $f([H_CARD, [TETRAO_UROGALLUS, HYPSUGO_SAVII], [LARIX, PINUS], ALPINE_DECK]),
            184 => $f([H_CARD, [TETRAO_UROGALLUS, CAPRA_IBEX], [PINUS, DOUGLAS_FIR], ALPINE_DECK]),
            185 => $f([V_CARD, [PARNASSIUS_PHOEBUS, CRATERELLUS_CORNUCOPIODES], [PINUS, LARIX], ALPINE_DECK]),
            186 => $f([V_CARD, [PARNASSIUS_PHOEBUS, LEONTOPODIUM_NIVALE], [DOUGLAS_FIR, PINUS], ALPINE_DECK]),
            187 => $f([V_CARD, [PARNASSIUS_PHOEBUS, VACCINIUM_MYRTILLUS], [LARIX, BIRCH], ALPINE_DECK]),
            188 => $f([V_CARD, [PARNASSIUS_PHOEBUS, ICHTHYOSAURA_ALPESTRIS], [SILVER_FIR, PINUS], ALPINE_DECK]),
            189 => $f([V_CARD, [AQUILA_CHRYSAETOS, CRATERELLUS_CORNUCOPIODES], [BEECH, PINUS], ALPINE_DECK]),
            190 => $f([V_CARD, [GYPAETUS_BARBATUS, GENTIANA], [SILVER_FIR, LARIX], ALPINE_DECK]),
            191 => $f([V_CARD, [AQUILA_CHRYSAETOS, ICHTHYOSAURA_ALPESTRIS], [LARIX, DOUGLAS_FIR], ALPINE_DECK]),
            192 => $f([V_CARD, [CORVUS_CORAX, GENTIANA], [LARIX, BEECH], ALPINE_DECK]),
            193 => $f([V_CARD, [CORVUS_CORAX, VACCINIUM_MYRTILLUS], [DOUGLAS_FIR, PINUS], ALPINE_DECK]),
            194 => $f([V_CARD, [GYPAETUS_BARBATUS, ICHTHYOSAURA_ALPESTRIS], [LARIX, SILVER_FIR], ALPINE_DECK]),
            195 => $f([V_CARD, [GYPAETUS_BARBATUS, LEONTOPODIUM_NIVALE], [PINUS, LARIX], ALPINE_DECK]),
            196 => $f([V_CARD, [AQUILA_CHRYSAETOS, GENTIANA], [SILVER_FIR, PINUS], ALPINE_DECK]),
            197 => $f([H_CARD, [HYPSUGO_SAVII, LEPUS_TIMIDUS], [LARIX, PINUS], ALPINE_DECK]),
            //Edge
            198 => $f([TREE, [SAMBUCUS], [LINDEN], EDGE_DECK]),
            199 => $f([TREE, [SAMBUCUS], [SYCAMORE], EDGE_DECK]),
            200 => $f([TREE, [SAMBUCUS], [BIRCH], EDGE_DECK]),
            201 => $f([TREE, [SAMBUCUS], [OAK], EDGE_DECK]),
            202 => $f([TREE, [COMMON_HAZEL], [HORSE_CHESTNUT], EDGE_DECK]),
            203 => $f([TREE, [COMMON_HAZEL], [OAK], EDGE_DECK]),
            204 => $f([TREE, [COMMON_HAZEL], [BEECH], EDGE_DECK]),
            205 => $f([TREE, [COMMON_HAZEL], [BIRCH], EDGE_DECK]),
            206 => $f([TREE, [BLACKTHORN], [DOUGLAS_FIR], EDGE_DECK]),
            207 => $f([TREE, [BLACKTHORN], [BIRCH], EDGE_DECK]),
            208 => $f([TREE, [BLACKTHORN], [SILVER_FIR], EDGE_DECK]),
            209 => $f([TREE, [BLACKTHORN], [SYCAMORE], EDGE_DECK]),
            210 => $f([V_CARD, [MAP_BUTTERFLY, DIGITALIS], [LINDEN, DOUGLAS_FIR], EDGE_DECK]),
            211 => $f([V_CARD, [MAP_BUTTERFLY, URTICA], [SYCAMORE, BIRCH], EDGE_DECK]),
            212 => $f([V_CARD, [MAP_BUTTERFLY, GREAT_GREEN_BUSH_CRICKET], [OAK, SILVER_FIR], EDGE_DECK]),
            213 => $f([V_CARD, [MAP_BUTTERFLY, EUROPEAN_WATER_VOLE], [SILVER_FIR, SYCAMORE], EDGE_DECK]),
            214 => $f([V_CARD, [EURASIAN_MAGPIE, DIGITALIS], [BEECH, BIRCH], EDGE_DECK]),
            215 => $f([V_CARD, [EURASIAN_MAGPIE, URTICA], [SILVER_FIR, HORSE_CHESTNUT], EDGE_DECK]),
            216 => $f([V_CARD, [EURASIAN_MAGPIE, GREAT_GREEN_BUSH_CRICKET], [BIRCH, BEECH], EDGE_DECK]),
            217 => $f([V_CARD, [COMMON_NIGHTINGALE, DIGITALIS], [BEECH, SYCAMORE], EDGE_DECK]),
            218 => $f([V_CARD, [COMMON_NIGHTINGALE, URTICA], [OAK, SYCAMORE], EDGE_DECK]),
            219 => $f([V_CARD, [COMMON_NIGHTINGALE, EUROPEAN_WATER_VOLE], [HORSE_CHESTNUT, BEECH], EDGE_DECK]),
            220 => $f([V_CARD, [BARN_OWL, DIGITALIS], [BIRCH, OAK], EDGE_DECK]),
            221 => $f([V_CARD, [BARN_OWL, GREAT_GREEN_BUSH_CRICKET], [SYCAMORE, OAK], EDGE_DECK]),
            222 => $f([H_CARD, [WILD_BOAR_FEMALE_, BEEHIVE], [BIRCH, SYCAMORE], EDGE_DECK]),
            223 => $f([H_CARD, [EUROPEAN_BISON, WILD_BOAR_FEMALE_], [OAK, SYCAMORE], EDGE_DECK]),
            224 => $f([H_CARD, [WILD_BOAR_FEMALE_, EUROPEAN_WILDCAT], [SILVER_FIR, HORSE_CHESTNUT], EDGE_DECK]),
            225 => $f([H_CARD, [COMMON_PIPISTRELLE, SQUEAKER_EDGE], [LINDEN, SILVER_FIR], EDGE_DECK]),
            226 => $f([H_CARD, [SQUEAKER_EDGE, MOSQUITO], [HORSE_CHESTNUT, BEECH], EDGE_DECK]),
            227 => $f([H_CARD, [EUROPEAN_POLECAT, SQUEAKER_EDGE], [SILVER_FIR, DOUGLAS_FIR], EDGE_DECK]),
            228 => $f([H_CARD, [BEEHIVE, COMMON_PIPISTRELLE], [BEECH, SYCAMORE], EDGE_DECK]),
            229 => $f([H_CARD, [EUROPEAN_WILDCAT, BEEHIVE], [OAK, BIRCH], EDGE_DECK]),
            230 => $f([H_CARD, [COMMON_PIPISTRELLE, EUROPEAN_BISON], [BIRCH, BEECH], EDGE_DECK]),
            231 => $f([H_CARD, [EUROPEAN_BISON, EUROPEAN_POLECAT], [BEECH, SYCAMORE], EDGE_DECK]),
            232 => $f([H_CARD, [MOSQUITO, EUROPEAN_POLECAT], [BIRCH, OAK], EDGE_DECK]),
            233 => $f([H_CARD, [EUROPEAN_WILDCAT, MOSQUITO], [SYCAMORE, OAK], EDGE_DECK]),
        ];
    }
}
