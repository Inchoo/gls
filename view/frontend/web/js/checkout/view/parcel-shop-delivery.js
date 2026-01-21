/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

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
            mapScriptUrl: 'https://map.gls-croatia.com/widget/gls-dpm.js',
            shippingMethodCodes: [],
            mapTypeFilters: {},
            supportedCountries: [],
            lockerMapSaturation: null,

            listens: {
                '${ $.parentName }:isSelected': 'shippingMethodChanged'
            },

            imports: {
                currentShippingMethodCode: '${ $.parentName }:isSelected'
            }
        },

        countryCode: ko.observable(null),
        isShippingMethodSelected: glsData.isParcelShopDeliverySelected,
        selectorTitle: ko.observable(null),
        deliveryPoint: glsData.parcelShopDeliveryPoint,
        typeFilter: ko.observable(null),
        filterSaturation: ko.observable(null),

        initialize: function () {
            var checkoutConfig = window.checkoutConfig.glsData;

            if (!checkoutConfig) {
                this.destroy();
                return this;
            }

            this._super();

            if (checkoutConfig.mapScriptUrl) {
                this.mapScriptUrl = checkoutConfig.mapScriptUrl;
            }

            this.shippingMethodCodes = checkoutConfig.parcelShopDelivery.shippingMethodCodes;
            if (checkoutConfig.parcelShopDelivery.mapTypeFilters) {
                this.mapTypeFilters = checkoutConfig.parcelShopDelivery.mapTypeFilters;
            }
            if (checkoutConfig.parcelShopDelivery.lockerMapSaturation) {
                this.lockerMapSaturation = checkoutConfig.parcelShopDelivery.lockerMapSaturation;
            }

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

            if (this.currentShippingMethodCode) {
                this.isShippingMethodSelected(this.isLockerShopShippingMethod(this.currentShippingMethodCode));
            }

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

            this.filterSaturation(this.getFilterSaturation(this.typeFilter(), this.countryCode()));
        },

        shippingMethodChanged: function (newShippingMethodCode) {
            var isShippingMethodSelected = this.isLockerShopShippingMethod(newShippingMethodCode),
                shippingMethod = quote.shippingMethod();

            this.removeDeliveryPoint();

            if (isShippingMethodSelected) {
                scriptLoader.createMapScript(this.mapScriptUrl);
                this.typeFilter(newShippingMethodCode in this.mapTypeFilters ? this.mapTypeFilters[newShippingMethodCode] : null);
                this.selectorTitle(shippingMethod.carrier_title + ' ' + shippingMethod.method_title);
                this.filterSaturation(this.getFilterSaturation(this.typeFilter(), this.countryCode()));
            } else {
                this.typeFilter(null);
                this.selectorTitle(null);
                this.filterSaturation(null);
            }

            this.isShippingMethodSelected(isShippingMethodSelected);
        },

        isLockerShopShippingMethod: function (shippingMethodCode) {
            return this.shippingMethodCodes.includes(shippingMethodCode);
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

                    if (glsData.shippingSaveProcessorCallback) {
                        glsData.shippingSaveProcessorCallback();
                    }
                }
            });
        },

        getCountryName: function (countryId) {
            var countryData = customerData.get('directory-data')();
            return countryData[countryId] ? countryData[countryId].name : '';
        },

        getFilterSaturation: function (typeFilter, countryId) {
            if (typeFilter === 'parcel-locker' && countryId === 'hu') {
                return this.lockerMapSaturation;
            }

            return null;
        }
    });
});
