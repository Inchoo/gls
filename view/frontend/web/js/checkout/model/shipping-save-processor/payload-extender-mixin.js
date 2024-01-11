/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'GLSCroatia_Shipping/js/checkout/model/gls-data',
    'mage/utils/wrapper'
], function (glsData, wrapper) {
    'use strict';

    return function (payload) {
        return wrapper.wrap(payload, function (originalAction) {
            var result = originalAction();

            if (glsData.isParcelShopDeliverySelected() && glsData.parcelShopDeliveryPoint()) {
                result.addressInformation.extension_attributes.gls_parcel_shop_delivery_point = JSON.stringify(glsData.parcelShopDeliveryPoint());
            }

            return result;
        });
    };
});
