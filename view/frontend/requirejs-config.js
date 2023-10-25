var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'GLSCroatia_Shipping/js/checkout/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'GLSCroatia_Shipping/js/checkout/view/shipping-mixin': true
            }
        }
    }
};
