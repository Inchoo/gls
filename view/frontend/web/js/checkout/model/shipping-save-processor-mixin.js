/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'GLSCroatia_Shipping/js/checkout/model/gls-data'
], function ($, quote, glsData) {
    'use strict';

    return function (shippingSaveProcessor) {
        var originSaveShippingInformation = shippingSaveProcessor.saveShippingInformation;

        shippingSaveProcessor.saveShippingInformation = function (type) {
            var shippingMethod = quote.shippingMethod();

            if (shippingMethod
                && shippingMethod['carrier_code'] === 'gls'
                && (shippingMethod['method_code'] === 'locker' || shippingMethod['method_code'] === 'shop')
            ) {
                var deferred = $.Deferred();

                glsData.shippingSaveProcessorCallback = function () {
                    return originSaveShippingInformation.call(shippingSaveProcessor, type);
                };

                return deferred.resolve();
            } else {
                return originSaveShippingInformation.call(shippingSaveProcessor, type);
            }
        };

        return shippingSaveProcessor;
    };
});
