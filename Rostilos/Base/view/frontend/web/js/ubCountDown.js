/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */

define([
    'jquery',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.ubCountDown', {

        options: {
            element: null,
            timer: null,
            countStep: -1,
            timeOut: 0,
            autoPlay: true,
            zeroPrefix: true,
            endDate: null,
            expiredMessage: "Expired",
            displayStyle: '1',
            displayFormat: "<div>%%D%% Days</div><div>%%H%% Hours</div><div>%%M%% Minutes</div><div>%%S%% Seconds</div>"
        },

        /**
         * Initialize widget
         */
        _create: function() {

            var self = this;

            if (self.options.endDate == null || self.options.endDate == '') {
                return;
            }

            self.options.countStep = Math.ceil(self.options.countStep);
            self.options.timeOut = (Math.abs(self.options.countStep) - 1) * 1000 + 990;
            self.options.displayFormat = $(self.options.element).html();

            var endDate = new Date(self.options.endDate);
            var currentDate = new Date();
            if (self.options.countStep > 0) {
                var dateDiff = new Date(currentDate - endDate);
            } else {
                var dateDiff = new Date(endDate - currentDate);
            }
            var seconds = Math.floor(dateDiff.valueOf() / 1000);

            self.playBack(seconds);
        },

        calculateTime: function (secs, num1, num2) {
            var self = this;

            var str = ((Math.floor(secs / num1)) % num2).toString();
            if (self.options.zeroPrefix && str.length < 2) {
                str = "0" + str;
            }
            if (self.options.displayStyle == 1) {
                return "<span class='number'>" + str + "</span>";
            } else if (self.options.displayStyle == 2) {
                var number = str.split("");
                if (typeof(number[2]) == "undefined") {
                    return "<span class=\"number\"><span>" + number[0] + "</span><span>" + number[1] + "</span></span>";
                } else {
                    return "<span class=\"number\"><span>" + number[0] + "</span><span>" + number[1] + "</span><span>" + number[2] + "</span></span>";
                }
            }
        },

        playBack: function (secs) {
            var self = this;
            if (secs < 0) {
                var result = '<div class="rs-countdown-expired"> ' + self.options.expiredMessage + "</div>";
                $(self.options.element).html(result);
                return;
            }

            clearInterval(self.timer);
            var result = self.options.displayFormat.replace(/%%D%%/g, self.calculateTime(secs, 86400, 100000));
            result = result.replace(/%%H%%/g, self.calculateTime(secs, 3600, 24));
            result = result.replace(/%%M%%/g, self.calculateTime(secs, 60, 60));
            result = result.replace(/%%S%%/g, self.calculateTime(secs, 1, 60));
            $(self.options.element).html(result);

            if (self.options.autoPlay) {
                self.options.timer = null;
                self.options.timer = setTimeout(function () {
                    self.playBack((secs + self.options.countStep));
                }, self.options.timeOut);
            }
        }
    });

    return $.mage.ubCountDown;
});
