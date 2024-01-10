/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

define([
    'ko'
], function (ko) {
    'use strict';

    return {
        parcelShopDeliveryPoint: ko.observable(),
        isParcelShopDeliverySelected: ko.observable(false)
    };
});
