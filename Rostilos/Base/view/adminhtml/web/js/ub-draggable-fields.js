/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.ubDraggableFields', {

        options: {
            rowsContainer: '[data-role=row-container]',
            orderInput: '[data-role=sort-control]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var $self = this;

            var $rowsContainer = $self.element.find(this.options.rowsContainer);
            $rowsContainer.sortable({
                axis: 'y',
                tolerance: 'pointer',
                update: function () {
                    $rowsContainer.find($self.options.orderInput).each(function (index, element) {
                        $(element).val(index);
                    });
                }
            });
        }
    });

    return $.mage.ubDraggableFields;
});
