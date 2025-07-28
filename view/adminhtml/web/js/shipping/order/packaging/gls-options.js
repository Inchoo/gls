/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';

    return function (config, element) {
        var initGlsOptions = function () {
            var packaging = window.Packaging,
                originSendCreateLabelRequest = packaging.prototype.sendCreateLabelRequest;

            Object.extend(packaging.prototype, {
                // override "sendCreateLabelRequest" method
                sendCreateLabelRequest: function () {
                    var childElements = element.querySelectorAll('input[name], select[name], textarea[name]');

                    childElements.forEach(el => {
                        this.paramsCreateLabelRequest['gls[' + el.name + ']'] = el.value;
                    });

                    // call parent method
                    originSendCreateLabelRequest.call(this);
                }
            });
        };

        // execute custom code after the 'Packaging' object has been initialized.
        if ($(document).data('packagingInited')) {
            initGlsOptions();
        } else {
            $(document).on('packaging:inited', initGlsOptions);
        }
    }
});
