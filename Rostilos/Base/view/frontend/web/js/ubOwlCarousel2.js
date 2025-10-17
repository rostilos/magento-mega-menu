/**
 * Copyright © 2017 Rostilos.com All rights reserved.
 */
define([
    "jquery",
    "owlCarousel2"
], function($) {
    'use strict';

    return function ($config) {
        var $element = $config.element;
        var $totalItems = $($element + ' .owl-lazy').length;

        $($element).owlCarousel({
            lazyLoad: true,
            loop: ($totalItems > 5),
            margin: 20,
            nav: true,
            navText: ['&larr;', '&rarr;'],
            responsiveClass: true,
            responsiveBaseElement: $element,
            responsive:{
                0: {
                    items: 1
                },
                500: {
                    items: 2
                },
                768: {
                    items: 3
                },
                960: {
                    items: 5
                },
                1200: {
                    items: 5
                }
            }
        });
    };
});
