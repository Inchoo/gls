/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define(function () {
    'use strict';

    var scriptCreated = false;

    return {
        /**
         * Create GLS delivery point map <script> element.
         */
        createMapScript: function (url) {
            var element, firstScriptElement;

            if (!scriptCreated) {
                element = document.createElement('script');
                element.src = url;
                element.type = 'module'
                element.async = true;
                element.defer = true;

                firstScriptElement = document.getElementsByTagName('script')[0];
                firstScriptElement.parentNode.insertBefore(element, firstScriptElement);

                scriptCreated = true;
            }
        }
    };
});
