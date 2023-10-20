define([
    'GLSCroatia_Shipping/js/model/gls-data',
    'mage/utils/wrapper'
], function (glsData, wrapper) {
    'use strict';

    return function (payload) {
        return wrapper.wrap(payload, function (originalAction) {
            var result = originalAction();

            if (glsData.isMethodSelected() && glsData.deliveryLocation()) {
                result.addressInformation.extension_attributes.gls_delivery_location = JSON.stringify(glsData.deliveryLocation());
            }

            return result;
        });
    };
});
