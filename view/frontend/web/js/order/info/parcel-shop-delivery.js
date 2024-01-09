define([
    'jquery',
    'mage/template',
], function ($, mageTemplate) {
    'use strict';

    return function () {
        $('.box-order-shipping-method .box-content').append(mageTemplate('#parcel-shop-delivery-template'));
    };
});
