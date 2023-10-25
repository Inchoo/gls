define([
    'uiComponent',
    'ko',
    'GLSCroatia_Shipping/js/checkout/model/gls-data',
    'GLSCroatia_Shipping/js/storage',
    'GLSCroatia_Shipping/js/scriptLoader',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (Component, ko, glsData, storage, scriptLoader, quote, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'GLSCroatia_Shipping/checkout/parcel-shop-delivery',
            mapElementId: 'gls-map-dialog',
            shippingMethodCode: null,
            supportedCountries: [],

            listens: {
                '${ $.parentName }:isSelected': 'shippingMethodChanged'
            }
        },

        countryCode: ko.observable(null),
        isShippingMethodSelected: glsData.isParcelShopDeliverySelected,
        deliveryPoint: glsData.parcelShopDeliveryPoint,

        initialize: function () {
            var checkoutConfig = window.checkoutConfig.glsData;

            if (!checkoutConfig) {
                this.destroy();
                return this;
            }

            this._super();

            this.shippingMethodCode = checkoutConfig.parcelShopDelivery.shippingMethodCode;

            if (Array.isArray(checkoutConfig.supportedCountries)) {
                this.supportedCountries = checkoutConfig.supportedCountries;
            }

            if (checkoutConfig.parcelShopDelivery.deliveryPoint && !storage.hasDeliveryPointChanged()) {
                storage.setDeliveryPoint(glsCheckoutConfig.deliveryPoint); // init persistence storage
            }
            this.deliveryPoint(storage.getDeliveryPoint());
            this.deliveryPoint.subscribe(function (newDeliveryPoint) {
                storage.setDeliveryPoint(newDeliveryPoint); // update persistence storage
            });

            this._initCountryCode();

            return this;
        },

        _initCountryCode: function () {
            var shippingAddress = quote.shippingAddress();

            if (shippingAddress && shippingAddress.countryId) {
                this.setCountryCode(shippingAddress.countryId);
            }

            if (!this.countryCode()) {
                this.removeDeliveryPoint();
            }

            this.countryCode.subscribe(function () {
                this.removeDeliveryPoint();
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
            var isShippingMethodSelected = currentShippingMethodCode === this.shippingMethodCode;

            if (isShippingMethodSelected) {
                scriptLoader.createMapScript();
            }

            this.isShippingMethodSelected(isShippingMethodSelected);
        },

        openMap: function () {
            document.getElementById(this.mapElementId).showModal();
        },

        removeDeliveryPoint: function () {
            this.deliveryPoint(null);
        },

        initMap: function () {
            var mapElement = document.getElementById(this.mapElementId);

            mapElement.addEventListener('change', (e) => {
                if (this.isShippingMethodSelected()) {
                    this.deliveryPoint(e.detail);
                }
            });
        },

        getCountryName: function (countryId) {
            var countryData = customerData.get('directory-data')();
            return countryData[countryId] ? countryData[countryId].name : '';
        }
    });
});
