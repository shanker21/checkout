define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate',
    'mage/dropdown'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var mixin = {
        isButtonEnable: function () {
            var linkUrls  = window.checkout.baseUrl+"createorder/order/editorderflag";
            $.ajax({
                url: linkUrls,
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                if (data.editOrderFlag==1) {
                    $("#top-cart-btn-checkout").addClass('hide');
                }

            });

            return true;
            
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});