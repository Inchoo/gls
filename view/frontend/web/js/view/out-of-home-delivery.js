define([
    'uiComponent',
    'ko',
    'GLSCroatia_Shipping/js/model/gls-data',
    'GLSCroatia_Shipping/js/storage',
    'GLSCroatia_Shipping/js/scriptLoader',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (Component, ko, glsData, storage, scriptLoader, quote, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'GLSCroatia_Shipping/checkout/out-of-home-delivery',
            mapElementId: 'gls-map-dialog',
            shippingMethodCode: 'gls_oohd',
            supportedCountries: [],

            listens: {
                '${ $.parentName }:isSelected': 'shippingMethodChanged'
            },

        },

        countryCode: ko.observable(null),
        isMethodSelected: glsData.isMethodSelected,
        deliveryLocation: glsData.deliveryLocation,

        initialize: function () {
            this._super();

            if (!window.checkoutConfig.glsData) {
                this.destroy();
                return this;
            }

            if (Array.isArray(window.checkoutConfig.glsData.supportedCountries)) {
                this.supportedCountries = window.checkoutConfig.glsData.supportedCountries;
            }

            this._initStorage();
            this._initCountryCode();

            return this;
        },

        _initStorage: function () {
            if (window.checkoutConfig.glsData.deliveryLocation && !storage.hasLocationChanged()) {
                storage.setDeliveryLocation(window.checkoutConfig.glsData.deliveryLocation);
            }

            this.deliveryLocation(storage.getDeliveryLocation());
            this.deliveryLocation.subscribe(function (newDeliveryLocation) {
                storage.setDeliveryLocation(newDeliveryLocation);
            });

            return this;
        },

        _initCountryCode: function () {
            var shippingAddress = quote.shippingAddress();

            if (shippingAddress && shippingAddress.countryId) {
                this.setCountryCode(shippingAddress.countryId);
            }

            if (!this.countryCode()) {
                this.removeDeliveryLocation();
            }

            this.countryCode.subscribe(function () {
                this.removeDeliveryLocation();
            }.bind(this));

            quote.shippingAddress.subscribe(function (shippingAddress) {
                this.setCountryCode(shippingAddress.countryId);
            }.bind(this));

            return this;
        },

        setCountryCode: function (countryCode) {
            if (countryCode && this.supportedCountries.includes(countryCode)) {
                this.countryCode(countryCode.toLowerCase());
            } else {
                this.countryCode(null);
            }
        },

        shippingMethodChanged: function (currentShippingMethodCode) {
            var isMethodSelected = currentShippingMethodCode === this.shippingMethodCode;

            if (isMethodSelected) {
                scriptLoader.createMapScript();
            }

            this.isMethodSelected(isMethodSelected);
        },

        openMap: function () {
            document.getElementById(this.mapElementId).showModal();
        },

        removeDeliveryLocation: function () {
            this.deliveryLocation(null);
        },

        initMap: function () {
            var mapElement = document.getElementById(this.mapElementId);

            mapElement.addEventListener('change', (e) => {
                if (this.isMethodSelected()) {
                    this.deliveryLocation(e.detail);
                }
            });
        },

        getCountryName: function (countryId) {
            var countryData = customerData.get('directory-data')();
            return countryData[countryId] ? countryData[countryId].name : '';
        }
    });
});
