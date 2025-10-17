/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */

define([
    'jquery',
    'mage/translate',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget',
    'Magento_Catalog/js/catalog-add-to-cart'
], function ($, $t) {
    'use strict';

    $.widget('ub.AjaxAddCart', {

        options: {
            element: null,
            formKeySelector: 'INPUT[name="form_key"]',
            processStart: "processStart",
            processStop: "processStop",
            ajaxAddCartUrl: null,
            ajaxAddCartFormSelector: 'form[data-role="tocart-form"]',
            messagesSelector: null,
            messageTimeout: 5000
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this.bindAjaxAddCart();
        },

        bindAjaxAddCart: function () {
            var self = this,
                stateClass = 'ajax-cart-applied';
            $(self.options.ajaxAddCartFormSelector).each(function (index, el) {
                if ($(el).attr('action').match(/checkout\/cart\/add/g)
                    && !$(el).hasClass(stateClass)) {
                    $.mage.catalogAddToCart({
                        "bindSubmit": true,
                        "messagesSelector": self.options.messagesSelector
                    }, el);
                    $(el).addClass(stateClass);
                }
            });

            $(document).on('ajax:addToCart', function (e, data) {
                if (typeof data.response.backUrl == "undefined") {
                    self.showMiniCart();

                    //set timeout to close sidebar
                    clearInterval(timeOut);
                    var timeOut = setTimeout(function () {
                        self.hideMiniCart();
                    }, self.options.messageTimeout);
                }
            });
            $(document).on('ajax:addToCart:error', function (e, data) {
                $("[data-placeholder=\"messages\"]").html(data.response.messages);
            });
        },

        showMiniCart: function () {
            $(".page.messages").find('.messages').slideDown();
            var $btn = $('#minicart').find('.btn-toggle');
            if (!$btn.hasClass('active')) {
                $btn.trigger('click');
            }
        },

        hideMiniCart: function () {
            var $btn = $('#minicart').find('.btn-toggle');
            if ($btn.hasClass('active')) {
                $btn.trigger('click');
            }
            $(".page.messages").find('.messages').slideUp();
        }

    });

    return $.ub.AjaxAddCart;
});
