/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'GLSCroatia_Shipping/js/checkout/model/gls-data',
    'mage/translate'
], function (glsData, $t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validateShippingInformation: function () {
                var result = this._super();

                if (result && glsData.isParcelShopDeliverySelected() && !glsData.parcelShopDeliveryPoint()) {
                    result = false;
                    this.errorValidationMessage($t('Please select a GLS Delivery Point.'));
                }

                return result;
            }
        });
    };
});
