/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'jquery',
    'GLSCroatia_Shipping/js/scriptLoader'
], function ($, scriptLoader) {
    'use strict';

    $.widget('mage.glsMapSelector', {
        options: {
            saveUrl: '',
            mapScriptUrl: '',
            mapSelectorButtonId: '',
            glsMapDialogId: '',
            deliveryPointAddressId: ''
        },

        _create: function () {
            scriptLoader.createMapScript(this.options.mapScriptUrl);

            document.getElementById(this.options.glsMapDialogId).addEventListener('change', (e) => {
                var addressContainer = document.getElementById(this.options.deliveryPointAddressId);

                var textarea = document.createElement('textarea');
                textarea.name = 'delivery_data';
                textarea.value = JSON.stringify(e.detail);
                textarea.style.display = 'none';
                addressContainer.appendChild(textarea);

                submitAndReloadArea(addressContainer, this.options.saveUrl);
            });

            document.getElementById(this.options.mapSelectorButtonId).addEventListener('click', () => {
                this.openMap();
            });
        },

        openMap: function () {
            document.getElementById(this.options.glsMapDialogId).showModal();
        }
    });

    return $.mage.glsMapSelector;
});
