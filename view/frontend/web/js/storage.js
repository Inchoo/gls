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
            deliveryLocation: null,
            locationChanged: false
        };
    }

    return {
        getDeliveryLocation: function () {
            return getData().deliveryLocation;
        },

        setDeliveryLocation: function (data) {
            var obj = getData();
            obj.deliveryLocation = data;
            obj.locationChanged = true;
            saveData(obj);
        },

        hasLocationChanged: function () {
            return getData().locationChanged;
        }
    }
});
