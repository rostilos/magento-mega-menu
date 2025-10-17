/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */

define([
    'jquery',
    'mage/translate',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget'
], function ($, $t) {
    'use strict';

    $.widget('ub.AjaxWishlist', {

        options: {
            element: null,
            customerId: null,
            loginUrl: null,
            formKeySelector: 'INPUT[name="form_key"]',
            processStart: "processStart",
            processStop: "processStop",
            ajaxWishlistUrl: null,
            wishlistBtnSelector: '[data-action="add-to-wishlist"]',
            messageTimeout: 5000
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var self = this;
            if (self.options.ajaxWishlistUrl.length) {
                $('body').on('click', self.options.wishlistBtnSelector, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (self.options.customerId ) { //has login
                        var params = $(this).data('post').data;
                        params['isAjaxWishlist'] = true;
                        params['form_key'] = $(self.options.formKeySelector).val();
                        self.ajaxWishlist(params);
                    } else {
                        window.location.href = self.options.loginUrl;
                    }
                });
            }
        },

        ajaxWishlist: function (params) {
            var self = this;
            $.ajax({
                url: self.options.ajaxWishlistUrl,
                data: params,
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('body').trigger(self.options.processStart);
                },
                success: function (res) {
                    $('body').trigger(self.options.processStop);

                    if (res.message) {
                        //$('[data-placeholder="messages"]').html(res.message);
                    } else {
                        $('[data-placeholder="messages"]').html($t('No response from server. Please try again.'));
                    }

                    $(".page.messages").find('.messages').slideDown();
                    setTimeout(function () {
                        $(".page.messages").find('.messages').slideUp();
                    }, self.options.messageTimeout);
                }
            });
        },

    });

    return $.ub.AjaxWishlist;
});
