define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, storage) {
    'use strict';

    var cacheKey = 'gls-data';

    function saveData(data)
    {
        storage.set(cacheKey, data);
    }

    function getData()
    {
        var data = storage.get(cacheKey)();

        if ($.isEmptyObject(data)) {
            data = initData();
            saveData(data);
        }

        return data;
    }

    function initData()
    {
        return {
            deliveryPoint: null,
            deliveryPointChanged: false
        };
    }

    return {
        get: function (propertyKey) {
            var data = getData();

            if (data.hasOwnProperty(propertyKey)) {
                return data[propertyKey];
            }

            return null;
        },

        set: function (propertyKey, value) {
            var data = getData();

            data[propertyKey] = value;

            saveData(data);
        },

        getDeliveryPoint: function () {
            return this.get('deliveryPoint');
        },

        setDeliveryPoint: function (value) {
            this.set('deliveryPoint', value);
            this.set('deliveryPointChanged', true);
        },

        hasDeliveryPointChanged: function () {
            return this.get('deliveryPointChanged');
        }
    }
});
