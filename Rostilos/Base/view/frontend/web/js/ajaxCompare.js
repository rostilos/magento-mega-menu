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

    $.widget('ub.AjaxCompare', {

        options: {
            element: null,
            processStart: "processStart",
            processStop: "processStop",
            ajaxCompareUrl: null,
            compareBtnSelector: 'a.action.tocompare',
            messageTimeout: 5000
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var self = this;
            if (self.options.ajaxCompareUrl.length) {
                $('body').on('click', self.options.compareBtnSelector, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var params = $(this).data('post').data;
                    params['isAjaxCompare'] = true;
                    self.ajaxCompare(params);
                });
            }
        },

        ajaxCompare: function (params) {
            var self = this;
            $.ajax({
                url: self.options.ajaxCompareUrl,
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
                        $(".page.messages").find('.messages').slideDown();
                    } else {
                        $('[data-placeholder="messages"]').html($t('No response from server. Please try again.'));
                    }
                    setTimeout(function () {
                        $(".page.messages").find('.messages').slideUp();
                    }, self.options.messageTimeout);
                }
            });
        },

    });

    return $.ub.AjaxCompare;
});
