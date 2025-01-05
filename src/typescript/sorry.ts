/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Nertz implementation : © cardboardsphen, bga-dev@sphen.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * nertz.js
 *
 * Nertz user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

/// <amd-module name="bgagame/nertz"/>
/// <reference path="./types/all-bga-types.d.ts"/>

import 'dojo';
import 'dojo/_base/declare';
import 'ebg/counter';
import * as GameGui from 'ebg/core/gamegui';

/**
 * Client implementation of Nertz.
 */
export default class Nertz extends GameGui {
    // enable dynamic method calls
    [key: string]: any;

    constructor() {
        super();
        console.log('nertz constructor');
    }

    override setup(gamedatas: BGA.Gamedatas | any): void {
        console.log('Starting game setup');

        this.setupNotifications = () => {
            console.log('notifications subscriptions setup');

            this.bgaSetupPromiseNotifications({prefix: 'notification_'});
        };
        this.setupNotifications();

        console.log('Ending game setup');
    }

    override onEnteringState(stateName: string, args: any): void {
        console.log('Entering state: ' + stateName, args);

        var methodName = 'enteringState_' + stateName;
        if (typeof this[methodName] === 'function') {
            console.log('Calling ' + methodName);
            this[methodName](args);
        }
    }

    override onLeavingState(stateName: string): void {
        console.log('Leaving state: ' + stateName);
        var methodName = 'leavingState_' + stateName;
        if (typeof this[methodName] === 'function') {
            console.log('Calling ' + methodName);
            this[methodName]();
        }
    }

    override onUpdateActionButtons(stateName: string, args: any): void {
        console.log('onUpdateActionButtons: ' + stateName, args);
        var methodName = 'updateActionButtons_' + stateName;
        if (typeof this[methodName] === 'function') {
            console.log('Calling ' + methodName);
            this[methodName](args);
        }
    }

    ///////////////////////////////////////////////////
    //// State Handling

    ///////////////////////////////////////////////////
    //// Notification Handling

    ///////////////////////////////////////////////////
    //// Utility functions
}

define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui', 'ebg/counter'], dojo.declare('bgagame.nertz', ebg.core.gamegui, new Nertz()));
