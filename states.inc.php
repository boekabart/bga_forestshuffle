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
 * states.inc.php
 *
 * ForestShuffle game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

require_once 'modules/php/constants.inc.php';

$machinestates = [

    // The initial state. Please do not modify.
    ST_GAME_SETUP => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
            "" => ST_CHOOSE_SETUP,
        ]
    ],

    ST_CHOOSE_SETUP => [
        "name" => "chooseSetup",
        "description" => clienttranslate('Starting'),
        "descriptionmyturn" => clienttranslate('Starting'),
        "type" => GAME,
        "args" => "stChooseSetup",
        "transitions" => [
            'normal' => ST_MULLIGAN,
            'draft' => ST_DRAFT,
        ]
    ],

    ST_MULLIGAN => [
        "name" => "mulligan",
        "description" => clienttranslate('Waiting for other players decision'),
        "descriptionmyturn" => clienttranslate('If you have no tree card, ${you} can change your cards (once)'),
        "type" => MULTI,
        "args" => "argMulligan",
        "action" => "stMulligan",
        "possibleactions" => [CHANGE_CARDS, PASS_MULLIGAN],
        "transitions" => [
            "" => ST_NEXT_PLAYER
        ]
    ],

    ST_DRAFT => [
        "name" => "draft",
        "description" => clienttranslate('Draft round (${draftTurn}/${nRounds}): Waiting for other players decision'),
        "descriptionmyturn" => clienttranslate('Draft round (${draftTurn}/${nRounds}): You must choose ${nKeep} card(s) for you, ${nRight} card(s) for your neighbors and ${nClearing} card(s) for the clearing'),
        "descriptionmyturnDuo" => clienttranslate('Draft round (${draftTurn}/${nRounds}): You must choose ${nKeep} card for you, ${nRight} card for your opponent and ${nClearing} card for the clearing'),
        "type" => MULTI,
        "args" => "argDraft",
        "possibleactions" => ['actGiveCards', 'actChangeMind'],
        "transitions" => [
            "" => ST_CONFIRM
        ]
    ],

    ST_CONFIRM => [
        "name" => "confirm",
        "description" => clienttranslate('Dealing cards'),
        "descriptionmyturn" => clienttranslate('Dealing cards'),
        "type" => GAME,
        "action" => "stConfirm",
        "transitions" => [
            END_TURN => ST_NEXT_PLAYER,
            'keepDrafting' => ST_DRAFT,
        ]
    ],

    ST_NEXT_PLAYER => [
        "name" => "nextPlayer",
        'description' => '',
        "type" => GAME,
        "action" => "stNextPlayer",
        "transitions" => [
            "" => ST_PLAYER_TURN
        ]
    ],

    ST_PLAYER_TURN => [
        "name" => "playerTurn",
        'description' => clienttranslate('${actplayer} must play or take cards'),
        'descriptionmyturn' => clienttranslate('${you} must play or take cards'),
        "type" => ACTIVE_PLAYER,
        "args" => 'argPlayerTurn',
        "action" => 'stPlayerTurn',
        'updateGameProgression' => true,
        "possibleactions" => [PLAY_CARD, TAKE_CARD],
        "transitions" => [
            "zombiePass" => ST_NEXT_PLAYER,
            TAKE_CARD => ST_RETAKE,
            PLAY_AGAIN => ST_PLAYER_TURN,
            PLAY_ALL => ST_PLAY_ALL,
            FREE_PLAY => ST_FREE_PLAY,
            FREE_PLAY_ALL => ST_FREE_PLAY_ALL,
            WINTER => ST_PRE_END_OF_GAME,
            RACCOON => ST_HIBERNATE,
            BEAR => ST_HIBERNATE_BEAR,
            DISCARD_ALL => ST_DISCARD_ALL,
            TAKE_CARD_FROM_CLEARING => ST_TAKE_CARD_FROM_CLEARING,
            GYPAETUS => ST_HIBERNATE_GYPAETUS, //deprecated
            ADD_TO_CLEARING => ST_ADD_TO_CLEARING,
            'end' => ST_PERFORM_ACTIONS
        ]
    ],

    ST_RETAKE => [
        "name" => "retake",
        'description' => clienttranslate('${actplayer} must take a second card'),
        'descriptionmyturn' => clienttranslate('${you} must take a second card'),
        "type" => ACTIVE_PLAYER,
        "args" => 'argPlayerTurn',
        "action" => 'stRetake',
        "possibleactions" => [TAKE_CARD, PASS],
        "transitions" => [
            "zombiePass" => ST_NEXT_PLAYER,
            PASS => ST_NEXT_PLAYER,
            TAKE_CARD => ST_PERFORM_ACTIONS,
            WINTER => ST_PRE_END_OF_GAME
        ]
    ],

    ST_CHECK_CLEARING => [ //discard cards if there are 10 or more cards in clearing
        "name" => "checkClearing",
        'description' => '',
        "type" => GAME,
        "action" => 'stCheckClearing',
        "transitions" => [
            '' => ST_NEXT_PLAYER,
        ]
    ],

    // ST_TREE_SAPLING_FROM_CLEARING => [ //discard cards if there are 10 or more cards in clearing
    //     "name" => TREE_SAPLING_FROM_CLEARING,
    //     'description' => '',
    //     "type" => GAME,
    //     "action" => 'stTreeSaplingFromClearing',
    //     "transitions" => [
    //         '' => ST_PERFORM_ACTIONS,
    //         WINTER => ST_PRE_END_OF_GAME,
    //     ]
    // ],

    ST_HIBERNATE => [
        "name" => HIBERNATE,
        'description' => clienttranslate('${actplayer} can put cards in his cave'),
        'descriptionmyturn' => clienttranslate('${you} can put cards in your cave'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => [HIBERNATE, PASS],
        "args" => 'argHibernate', //send possible cards
        "action" => 'stPlayerTurn',
        "transitions" => [
            'end' => ST_PERFORM_ACTIONS,
            WINTER => ST_PRE_END_OF_GAME,
            HIBERNATE => ST_CHECK_CLEARING //probably useless
        ]
    ],

    ST_HIBERNATE_BEAR => [
        "name" => HIBERNATE_BEAR,
        'description' => clienttranslate('${actplayer} can put cards in his cave'),
        "type" => GAME,
        "action" => 'stHibernateBear',
        "transitions" => [
            'end' => ST_PERFORM_ACTIONS
        ]
    ],

    ST_HIBERNATE_GYPAETUS => [ //deprecated
        "name" => HIBERNATE_GYPAETUS,
        'description' => clienttranslate('${actplayer} can put 2 cards from the clearing in his cave'),
        'descriptionmyturn' => clienttranslate('${you} can put 2 cards from the clearing in your cave'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => [HIBERNATE_GYPAETUS, PASS],
        "args" => 'argHibernateGypaetus', //send possible cards
        "action" => 'stPlayerTurn',
        "transitions" => [
            'end' => ST_PERFORM_ACTIONS,
        ]
    ],

    ST_TAKE_CARD_FROM_CLEARING => [
        "name" => TAKE_CARD_FROM_CLEARING,
        'description' => clienttranslate('${actplayer} can put ${nb} card(s) from the clearing in his cave'),
        'descriptionmyturn' => clienttranslate('${you} can put ${nb} card(s) from the clearing in your cave'),
        'descriptiontohand' => clienttranslate('${actplayer} can take ${nb} card(s) from the clearing in his hand'),
        'descriptionmyturntohand' => clienttranslate('${you} can take ${nb} card(s) from the clearing in your hand'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => ['actChooseCard', PASS],
        "args" => 'argTakeCardFromClearing', //send possible cards
        "action" => 'stTakeCardFromClearing',
        "transitions" => [
            'end' => ST_PERFORM_ACTIONS,
        ]
    ],

    ST_FREE_PLAY => [ //can play a card for free. Card must be from specific type
        "name" => FREE_PLAY,
        'description' => clienttranslate('${actplayer} can play a card with ${type} symbol for free'),
        'descriptionmyturn' => clienttranslate('${you} can play a card with ${type} symbol for free'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => [PLAY_CARD, PASS],
        "args" => 'argFreePlay', //send possible types
        "action" => 'stPlayerTurn', //just add time
        "transitions" => [
            "zombiePass" => ST_NEXT_PLAYER,
            TAKE_CARD => ST_RETAKE,
            PLAY_AGAIN => ST_FREE_PLAY,
            PLAY_ALL => ST_PLAY_ALL,
            FREE_PLAY => ST_FREE_PLAY,
            FREE_PLAY_ALL => ST_FREE_PLAY_ALL,
            WINTER => ST_PRE_END_OF_GAME,
            RACCOON => ST_HIBERNATE,
            BEAR => ST_HIBERNATE_BEAR,
            DISCARD_ALL => ST_DISCARD_ALL,
            TAKE_CARD_FROM_CLEARING => ST_TAKE_CARD_FROM_CLEARING,
            GYPAETUS => ST_HIBERNATE_GYPAETUS, //deprecated
            ADD_TO_CLEARING => ST_ADD_TO_CLEARING,
            'end' => ST_PERFORM_ACTIONS
        ]
    ],

    ST_FREE_PLAY_ALL => [ //can play a card for free. Card must be from specific type
        "name" => FREE_PLAY_ALL,
        'description' => clienttranslate('${actplayer} can play one card with ${type} symbol for free (and additional ${type} cards after)'),
        'descriptionmyturn' => clienttranslate('${you} can play one card with ${type} symbol for free (and additional ${type} cards after)'),
        'descriptionsapling' => clienttranslate('${actplayer} can immediately play one card as a tree sapling (and additional tree sapling after)'),
        'descriptionmyturnsapling' => clienttranslate('${you} can immediately play one card as a tree sapling (and additional tree sapling after)'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => [PLAY_CARD, PASS],
        "args" => 'argFreePlay', //send possible types
        "action" => 'stPlayerTurn', //just add time
        "transitions" => [
            "zombiePass" => ST_NEXT_PLAYER,
            TAKE_CARD => ST_RETAKE,
            PLAY_AGAIN => ST_FREE_PLAY_ALL,
            PLAY_ALL => ST_PLAY_ALL,
            FREE_PLAY_ALL => ST_FREE_PLAY_ALL,
            FREE_PLAY => ST_FREE_PLAY,
            WINTER => ST_PRE_END_OF_GAME,
            RACCOON => ST_HIBERNATE,
            BEAR => ST_HIBERNATE_BEAR,
            DISCARD_ALL => ST_DISCARD_ALL,
            GYPAETUS => ST_HIBERNATE_GYPAETUS, //deprecated
            TAKE_CARD_FROM_CLEARING => ST_TAKE_CARD_FROM_CLEARING,
            ADD_TO_CLEARING => ST_ADD_TO_CLEARING,
            'end' => ST_PERFORM_ACTIONS
        ]
    ],

    ST_PLAY_ALL => [ //state where player can play cards by paying
        "name" => "playAll",
        'description' => clienttranslate('${actplayer} can play another card(s)'),
        'descriptionmyturn' => clienttranslate('${you} can play another card(s)'),
        'descriptionovercost' => clienttranslate('${actplayer} can play another card(s) (costs increased by ${overcost})'),
        'descriptionmyturnovercost' => clienttranslate('${you} can play another card(s) (costs increased by ${overcost})'),
        "type" => ACTIVE_PLAYER,
        "possibleactions" => [PLAY_CARD, PASS, 'undo'],
        "args" => 'argPlayAll', //send available card
        "action" => 'stPlayerTurn',
        "transitions" => [
            "zombiePass" => ST_CHECK_CLEARING,
            PLAY_CARD => ST_PLAY_ALL,
            WINTER => ST_PRE_END_OF_GAME,
            'end' => ST_PERFORM_ACTIONS
        ]
    ],

    ST_PERFORM_ACTIONS => [
        "name" => "performActions",
        "type" => GAME,
        "action" => 'stPerformActions',
        "transitions" => [
            PLAY_AGAIN => ST_PLAYER_TURN,
            PLAY_ALL => ST_PLAY_ALL,
            FREE_PLAY_ALL => ST_FREE_PLAY_ALL,
            FREE_PLAY => ST_FREE_PLAY,
            WINTER => ST_PRE_END_OF_GAME,
            RACCOON => ST_HIBERNATE,
            BEAR => ST_HIBERNATE_BEAR,
            DISCARD_ALL => ST_DISCARD_ALL,
            GYPAETUS => ST_HIBERNATE_GYPAETUS, //deprecated
            TAKE_CARD_FROM_CLEARING => ST_TAKE_CARD_FROM_CLEARING,
            ADD_TO_CLEARING => ST_ADD_TO_CLEARING,
            'end' => ST_CHECK_CLEARING
        ]
    ],

    ST_ADD_TO_CLEARING => [
        'name' => 'addToClearing',
        'type' => GAME,
        'action' => 'stAddToClearing',
        'transitions' => [
            WINTER => ST_PRE_END_OF_GAME,
            'end' => ST_PERFORM_ACTIONS
        ],
    ],

    ST_DISCARD_ALL => [
        'name' => DISCARD_ALL,
        'type' => GAME,
        'action' => 'stDiscardAll',
        'transitions' => [
            'end' => ST_PERFORM_ACTIONS
        ],
    ],

    ST_PRE_END_OF_GAME => [
        'name' => 'preEndOfGame',
        'type' => GAME,
        'action' => 'stPreEndOfGame',
        'transitions' => ['' => ST_END_GAME],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"

    ]
];
