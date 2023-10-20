define([
    'GLSCroatia_Shipping/js/model/gls-data',
    'mage/translate'
],function (glsData, $t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validateShippingInformation: function () {
                var result = this._super();

                if (result && glsData.isMethodSelected() && !glsData.deliveryLocation()) {
                    result = false;
                    this.errorValidationMessage($t('Please select a GLS delivery location.'));
                }

                return result;
            }
        });
    };
});
