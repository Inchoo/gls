define([
    'uiComponent',
    'GLSCroatia_Shipping/js/model/gls-data',
    'Magento_Customer/js/customer-data'
], function (Component, glsData, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'GLSCroatia_Shipping/checkout/shipping-information/address-renderer'
        },

        isMethodSelected: glsData.isMethodSelected,
        deliveryLocation: glsData.deliveryLocation,

        getCountryName: function (countryId) {
            var countryData = customerData.get('directory-data')();
            return countryData[countryId] ? countryData[countryId].name : '';
        }
    });
});
