/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'jquery',
    'mage/template',
], function ($, mageTemplate) {
    'use strict';

    return function () {
        $('.box-order-shipping-method .box-content').append(mageTemplate('#parcel-shop-delivery-template'));
    };
});
