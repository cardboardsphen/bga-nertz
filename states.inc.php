<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Nertz implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * Nertz game states description
 *
 */

require_once("modules/php/Constants.inc.php");

//    !! It is not a good idea to modify this file when a game is running !!
$machinestates = [
    // The initial state. Please do not modify.
    States::START => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => States::GET_READY]
    ],

    States::GET_READY => [
        "name" => "getReady",
        "type" => "multipleactiveplayer",
        "description" => "",
        "descriptionmyturn" => "",
        "possibleactions" => [],
        "transition" => ["startRound" => States::EVERYONE_PLAY]
    ],

    States::EVERYONE_PLAY => [
        "name" => "everyonePlay",
        "type" => "multipleactiveplayer",
        "description" => "",
        "descriptionmyturn" => "",
        "possibleactions" => [],
        "transition" => ["nertz" => States::NEXT_ROUND]
    ],

    States::NEXT_ROUND => [
        "name" => "nextRound",
        "type" => "game",
        "action" => "stNextRound",
        "updategameprogression" => true,
        "transitions" => ["anotherRound" => States::GET_READY, "someoneWon" => States::END]
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    States::END => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],

];
