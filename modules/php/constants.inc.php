<?php

const CARD_TO_REMOVE = [
	0 => [
		2 => 30,
		3 => 20,
		4 => 10,
		5 => 0
	],
	1 => [
		2 => 55,
		3 => 40,
		4 => 25,
		5 => 10
	],
	2 => [
		2 => 90,
		3 => 60,
		4 => 45,
		5 => 30
	]
];

const CARD_TO_REMOVE_DRAFT = [
	0 => [
		2 => 20,
		3 => 10,
		4 => 0,
		5 => 0
	],
	1 => [
		2 => 35,
		3 => 20,
		4 => 5,
		5 => 0
	],
	2 => [
		2 => 50,
		3 => 35,
		4 => 20,
		5 => 15
	],
];

const DRAFT = [
	2 => [
		'draw' => 5,
		'keep' => 1,
		'giveRight' => 1,
		'giveLeft' => 0,
		'clearing' => 1,
		'remove' => 2,
		'rounds' => 3
	],
	3 => [
		'draw' => 6,
		'keep' => 1,
		'giveRight' => 1,
		'giveLeft' => 1,
		'clearing' => 1,
		'remove' => 2,
		'rounds' => 2
	],
	4 => [
		'draw' => 9,
		'keep' => 2,
		'giveRight' => 2,
		'giveLeft' => 2,
		'clearing' => 1,
		'remove' => 2,
		'rounds' => 1
	],
	5 => [
		'draw' => 7,
		'keep' => 2,
		'giveRight' => 2,
		'giveLeft' => 2,
		'clearing' => 1,
		'remove' => 0,
		'rounds' => 1
	]
];
/*
* Game Constants
*/

const PURPLE_EMPEROR = 'Purple Emperor';
const LARGE_TORTOISESHELL = 'Large Tortoiseshell';
const GNAT = 'Gnat';
const BEECH = 'Beech';
const TREE = 'Tree';
const TOP = 'onTop';
const BOTTOM = 'onBottom';
const LEFT = 'onLeft';
const RIGHT = 'onRight';
const SAPLING = 'sapling';
const CHANTERELLE = 'Chanterelle';
const BULLFINCH = 'Bullfinch';
const PENNY_BUN = 'Penny Bun';
const CAMBERWELL_BEAUTY = 'Camberwell Beauty';
const TREE_FERNS = 'Tree Ferns';
const CHAFFINCH = 'Chaffinch';
const BIRCH = 'Birch';
const HEDGEHOG = 'Hedgehog';
const EUROPEAN_BADGER = 'European Badger';
const GOSHAWK = 'Goshawk';
const POND_TURTLE = 'Pond Turtle';
const RED_SQUIRREL = 'Red Squirrel';
const WOLF = 'Wolf';
const WILD_STRAWBERRIES = 'Wild Strawberries';
const OAK = 'Oak';
const BROWN_BEAR = 'Brown Bear';
const RED_DEER = 'Red Deer';
const GREAT_SPOTTED_WOODPECKER = 'Great Spotted Woodpecker';
const SQUEAKER = 'Squeaker';
const FIRE_SALAMANDER = 'Fire Salamander';
const V_CARD = 'vCard';
const H_CARD = 'hCard';
const FLY_AGARIC = 'Fly Agaric';
const W_CARD = 'wCard';
const TAWNY_OWL = 'Tawny Owl';
const GREATER_HORSESHOE_BAT = 'Greater Horseshoe Bat';
const FALLOW_DEER = 'Fallow Deer';
const BECHSTEIN = "Bechstein's bat";
const RED_FOX = 'Red Fox';
const RACCOON = 'Raccoon';
const BEECH_MARTEN = 'Beech Marten';
const PEACOCK_BUTTERFLY = 'Peacock Butterfly';
const WILD_BOAR = 'Wild Boar';
const SILVER_FIR = 'Silver Fir';
const SYCAMORE = 'Sycamore';
const EUROPEAN_HARE = 'European Hare';
const TREE_FROG = 'Tree Frog';
const HORSE_CHESTNUT = 'Horse Chestnut';
const FIREFLIES = 'Fireflies';
const BLACKBERRIES = 'Blackberries';
const DOUGLAS_FIR = 'Douglas Fir';
const MOSS = 'Moss';
const EUROPEAN_FAT_DORMOUSE = 'European Fat Dormouse';
const MOLE = 'Mole';
const COMMON_TOAD = 'Common Toad';
const PARASOL_MUSHROOM = 'Parasol Mushroom';
const ROE_DEER = 'Roe Deer';
const STAG_BEETLE = 'Stag Beetle';
const LINDEN = 'Linden';
const EURASIAN_JAY = 'Eurasian Jay';
const BARBASTELLE_BAT = 'Barbastelle Bat';
const VIOLET_CARPENTER_BEE = 'Violet Carpenter Bee';
const LYNX = 'Lynx';
const WOOD_ANT = 'Wood Ant';
//Alpine Shuffle from here
const VACCINIUM_MYRTILLUS = 'VacciniumMyrtillus';
const ICHTHYOSAURA_ALPESTRIS = 'Ichthyosaura Alpestris';
const LARIX = 'Larix';
const CRATERELLUS_CORNUCOPIODES = 'Craterellus Cornucopiodes';
const LARIX_DECIDUA = 'Larix Decidua';
const AQUILA_CHRYSAETOS = 'Aquila Chrysaetos';
const PINUS_CEMBRA = 'Pinus Cembra';
const LEPUS_TIMIDUS = 'Lepus Timidus';
const RUPICAPRA_RUPICAPRA = 'Rupicapra Rupicapra';
const GENTIANA = 'Gentiana';
const TETRAO_UROGALLUS = 'Tetrao Urogallus';
const PARNASSIUS_PHOEBUS = 'Parnassius Phoebus';
const PINUS = 'Pinus';
const MARMOTA_MARMOTA = 'Marmota Marmota';
const CAPRA_IBEX = 'Capra Ibex';
const CORVUS_CORAX = 'Corvus Corax';
const GYPAETUS_BARBATUS = 'Gypaetus Barbatus';
const LEONTOPODIUM_NIVALE = 'Leontopodium Nivale';
const HYPSUGO_SAVII = "Hypsugo Savii";

//Edge Expansion
const WOODLAND = 'Woodland Edge';
const MAP_BUTTERFLY = "Map Butterfly";
const SAMBUCUS = "Sambucus";
const COMMON_HAZEL = "Common Hazel";
const BLACKTHORN = "Blackthorn";
const WILD_BOAR_FEMALE_ = "Wild Boar Female";
const BEEHIVE = "Beehive";
const EUROPEAN_BISON = "European Bison";
const EUROPEAN_WILDCAT = "European Wildcat";
const COMMON_PIPISTRELLE = "Common Pipistrelle";
const MOSQUITO = "Mosquito";
const EUROPEAN_POLECAT = "European Polecat";
const HAZEL_DOORMOUSE = "Hazel Doormouse";
const URTICA = "Urtica";
const DIGITALIS = "Digitalis";
const GREAT_GREEN_BUSH_CRICKET = "Great Green Bush-Cricket";
const EUROPEAN_WATER_VOLE = "European Water Vole";
const EURASIAN_MAGPIE = "Eurasian Magpie";
const COMMON_NIGHTINGALE = "Common Nightingale";
const BARN_OWL = "Barn Owl";
const SQUEAKER_EDGE = 'Squeaker Edge';
// TAGS

const MOUNTAIN = 'mountain';
const INSECT = 'Insect';
const BUTTERFLY = 'Butterfly';
const BIRD = 'Bird';
const PLANT = 'Plant';
const MUSHROOM = 'Mushroom';
const AMPHIBIAN = 'Amphibian';
const DEER = 'Deer';
const SHRUB = "Shrub";
const PAW = 'Paw';
const BAT = "Bat";
const CLOVEN = 'Cloven-hoofed animal';
/*
* DECKS
*/
const BASIC_DECK = 'basic';
const ALPINE_DECK = 'alpine';
const EDGE_DECK = 'edge';

/*
* Actions
*/
const PLAY_CARD = "playCard";
const CHANGE_CARDS = "changeCards";
const PASS_MULLIGAN = "passMulligan";
const PASS = "pass";
const ZOMBIE_PASS = "zombie_pass";
const TAKE_CARD = "takeCard";
const WINTER = "winter";
const HIBERNATE = "hibernate";
const HIBERNATE_BEAR = "hibernateBear";
const FREE_PLAY = "freePlay";
const FREE_PLAY_ALL = "freePlayAll";
const HIBERNATE_GYPAETUS = "actChooseCard";
const PLAY_ALL = "play_all";
const PLAY_AGAIN = "play_again";
const ADD_TO_CLEARING = "addToClearing";
const BEAR = "bear";
const GYPAETUS = "gypaetus";
const TAKE_CARD_FROM_CLEARING = 'takeCardFromClearing';
const TREE_SAPLING_FROM_CLEARING = 'treeSaplingFromClearing';
const DISCARD_ALL = 'discardAll';

/*
 * State constants
 */
const ST_GAME_SETUP = 1; //(shuffle cards -> discard from 0 to 30 dépending of the nb players, divide by 3,  put 2 W card in the last pile then shuffle, then put a W card on top then the 2 others piles)

const ST_MULLIGAN = 2; //(each player can redraw if he has no tree)
const ST_CHOOSE_SETUP = 3; //to fork between mulligan or draft
const ST_DRAFT = 20;
const ST_CONFIRM = 21; //move cards and eventually reveal them

const ST_NEXT_PLAYER = 4;
const ST_PLAYER_TURN = 5;
const ST_RETAKE = 6; // -> pre_end
const ST_NEXT_PHASE = 7; //automatic effect or bonus + dispatch effect -> bonus -> next player -> player_turn
const ST_PLAY_ALL = 9;
const ST_FREE_PLAY = 10; //can be only once 
const ST_HIBERNATE = 11;
const ST_HIBERNATE_BEAR = 15;

const ST_PERFORM_ACTIONS = 12;

const ST_CHECK_CLEARING = 13;

const ST_ADD_TO_CLEARING = 14;

const ST_HIBERNATE_GYPAETUS = 16;
const ST_TAKE_CARD_FROM_CLEARING = 18; //same as previous but generic

const ST_FREE_PLAY_ALL = 17; //can be any number 
const ST_DISCARD_ALL = 19;

const ST_TREE_SAPLING_FROM_CLEARING = 20;

const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;

const OPTION_SCORE = 100;
const OPTION_VISIBLE_SCORE = 0;
const OPTION_HIDDEN_SCORE = 1;

const OPTION_DRAFT_VARIANT = 103;
const OPTION_DRAFT = 1;
const OPTION_NO_DRAFT = 0;

const OPTION_ALPINE_VARIANT = 102;
const OPTION_ALPINE = 0;
const OPTION_NO_ALPINE = 1;

const OPTION_EDGE_VARIANT = 104;
const OPTION_EDGE = 0;
const OPTION_NO_EDGE = 1;

/****
 * Cheat Module
 */

const OPTION_DEBUG = 110;
const OPTION_DEBUG_OFF = 0;
const OPTION_DEBUG_ON = 1;

/******************
 ****** STATS ******
 ******************/

const STAT_NAME_PLAYED_CARDS = "played_cards";
const STAT_NAME_TREE_POINTS = "tree_points";
const STAT_NAME_TOP_BOTTOM_POINTS = "top_bottom_points";
const STAT_NAME_LEFT_RIGHT_POINTS = "left_right_points";
const STAT_NAME_TAKE_ACTION = "take_action";
const STAT_NAME_CAVE_POINTS = "cave_points";

/*
*  ██████╗ ███████╗███╗   ██╗███████╗██████╗ ██╗ ██████╗███████╗
* ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔══██╗██║██╔════╝██╔════╝
* ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ██████╔╝██║██║     ███████╗
* ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ██╔══██╗██║██║     ╚════██║
* ╚██████╔╝███████╗██║ ╚████║███████╗██║  ██║██║╚██████╗███████║
*  ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚═╝  ╚═╝╚═╝ ╚═════╝╚══════╝
*                                                               
*/


const GAME = "game";
const MULTI = "multipleactiveplayer";
const PRIVATESTATE = "private";
const END_TURN = 'endTurn';
const ACTIVE_PLAYER = "activeplayer";

//location
const CAVE = 'cave';
const HAND = 'hand';
const CLEARING = 'clearing';
