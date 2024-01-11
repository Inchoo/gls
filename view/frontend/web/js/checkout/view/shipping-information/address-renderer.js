/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'uiComponent',
    'GLSCroatia_Shipping/js/checkout/model/gls-data',
    'Magento_Customer/js/customer-data'
], function (Component, glsData, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'GLSCroatia_Shipping/checkout/shipping-information/address-renderer'
        },

        isParcelShopDeliverySelected: glsData.isParcelShopDeliverySelected,
        parcelShopDeliveryPoint: glsData.parcelShopDeliveryPoint,

        getCountryName: function (countryId) {
            var countryData = customerData.get('directory-data')();
            return countryData[countryId] ? countryData[countryId].name : '';
        }
    });
});
