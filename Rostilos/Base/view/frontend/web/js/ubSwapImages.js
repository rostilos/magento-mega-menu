/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */

define([
    'jquery',
    'matchMedia'
], function ($) {
    'use strict';

    $.widget('mage.ubSwapImages', {
        options: {
            "mobileBreakPoint": 767,
        },

        /**
         * Initialize widget
         */
        _create: function () {
            var self = this;

            mediaCheck({
                media: '(max-width: ' + self.options.mobileBreakPoint + 'px)',
                entry:function() {
                    var mobileSrc = $(self.element).data("mobile-src");
                    if (mobileSrc !== undefined && mobileSrc.length) {
                        self.element.attr("src", mobileSrc);
                    }
                    var mobileW = $(self.element).data("mobile-w");
                    if (mobileW !== undefined && mobileW) {
                        self.element.attr("width", mobileW);
                    }
                    var mobileH = $(self.element).data("mobile-h");
                    if (mobileH !== undefined && mobileH) {
                        self.element.attr("height", mobileH);
                    }
                },
                exit: function() {
                    var desktopSrc = $(self.element).data("desktop-src");
                    if (desktopSrc != undefined && desktopSrc.length) {
                        self.element.attr("src", desktopSrc);
                    }
                    var desktopW = $(self.element).data("desktop-w");
                    if (desktopW !== undefined && desktopW) {
                        self.element.attr("width", desktopW);
                    }
                    var desktopH = $(self.element).data("desktop-h");
                    if (desktopH !== undefined && desktopH) {
                        self.element.attr("height", desktopH);
                    }
                }
            });
        },

    });

    return $.mage.ubSwapImages;
});
