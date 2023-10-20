define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isMethodSelected: ko.observable(false),
        deliveryLocation: ko.observable(null)
    };
});
