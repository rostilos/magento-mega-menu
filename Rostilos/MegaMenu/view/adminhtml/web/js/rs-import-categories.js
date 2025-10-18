(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            'jquery',
            'mage/translate',
            'jquery/ui',
            'Magento_Ui/js/modal/modal',
            'Rostilos_MegaMenu/js/jquery/jstree/tree-suggest',
            'mage/backend/validation'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    $.widget('rs.RSImportCategories', {
        options: {
            suggestOptions: [],
        },
        _create: function () {
            var $widget = this;
            $('#category_ids').before($('<input>', {
                id: 'category_ids-suggest',
                placeholder: $.mage.__('start typing to search categories by name...')
            }));
            $('#category_ids-suggest').treeSuggest($widget.options.suggestOptions)
                .on('suggestbeforeselect', function (event, obj) {
                    if (typeof obj.item != 'undefined') {
                        if (obj.item.is_root) {
                            $('#import_categories_messages').addClass('warning').html($.mage.__('You can\'t select the root category.'));
                            return false;
                        }else {
                            $('#import_categories_messages').html('').removeClass('warning error success');
                            /*//select all child node => coming soon.
                            var val = '{"id":"'+obj.item.id+'","is_root":'+obj.item.is_root+',"is_active":"'+obj.item.is_active+'","label":"'+obj.item.label+'"}';
                            $('a[data-suggest-option="'+val+'"]').siblings('ul').find('li a').each(function(i, el) {
                                var $cNode = $(el);
                                //console.log($cNode.attr('data-suggest-option'));
                                $(event.target).trigger('select', $.parseJSON($cNode.attr('data-suggest-option')));
                            });*/
                        }
                    }
                    //$(event.target).treeSuggest('close'); //use this for one select only
                });

            var $frmImportCategories = $('#frm-import-categories');
            $frmImportCategories.mage('validation', {
                errorPlacement: function (error, element) {
                    error.insertAfter(element.is('#category_ids') ?
                        $('#category_ids-suggest').closest('.mage-suggest') :
                        element);
                }
            }).on('highlight.validate', function (e) {
                var options = $(this).validation('option');
                if ($(e.target).is('#category_ids')) {
                    options.highlight($('#category_ids-suggest').get(0),
                        options.errorClass, options.validClass || '');
                }
            });

            $widget.element.modal({
                type: 'slide',
                modalClass: 'rs-import-categories-dialog form-inline',
                title: $.mage.__('Import Categories'),
                opened: function () {
                    $('#import_categories_messages').removeClass('warning error success').html('');
                    $('#category_ids-suggest').focus();
                },
                closed: function () {
                    var validationOptions = $frmImportCategories.validation('option');
                    //reset form fields
                    $('#category_ids-suggest').val('');
                    //un highlight
                    validationOptions.unhighlight($('#category_ids-suggest').get(0),
                        validationOptions.errorClass, validationOptions.validClass || '');
                    $frmImportCategories.validation('clearError');
                },
                buttons: [{
                    text: '<i class="fa fa-download"></i> ' + $.mage.__('Import Categories'),
                    class: 'action-primary',
                    click: function (e) {
                        if (!$frmImportCategories.valid()) {
                            return;
                        }
                        var $btnImport = $(e.currentTarget);
                        $btnImport.prop('disabled', true);

                        //make selected category_ids
                        var import_type = $('#import_type').val();
                        var category_ids = '';
                        var parent_id = $('#parent_id').val();
                        $('#category_ids').find('option').each(function () {
                            if (category_ids.length) {
                                category_ids += ',' + $(this).val();
                            } else {
                                category_ids += $(this).val();
                            }
                        });

                        //ajax request to import selected categories
                        $.ajax({
                            type: 'POST',
                            url: $widget.options.saveUrl,
                            data: {
                                import_type: import_type,
                                category_ids: category_ids,
                                parent_id: parent_id,
                                form_key: FORM_KEY
                            },
                            //dataType: 'json',
                            context: $('body')
                        }).success(function (rs) {
                            if (rs.success) {
                                //reset form fields
                                var $suggest = $('#category_ids-suggest');
                                $('#category_ids-suggest').val('');
                                $suggest.val('');
                                $widget._resetSelector();
                                //close modal
                                $($widget.element).modal('closeModal');

                                //expand menu items
                                if (!parseInt(parent_id)) {
                                    if ($('#rostilos-mega-menu-0').length) {
                                        $('#rostilos-mega-menu-0').prepend(rs.menu_items);
                                    } else {
                                        $('#nestable').html('<ol id ="rostilos-mega-menu-0" class="dd-list">' + rs.menu_items + '</ol>');
                                    }

                                } else {
                                    $("li[data-id='" + parent_id +"']").append(rs.menu_items);
                                }

                                //alert message
                                $.mage.alert({title: rs.message});

                                setTimeout(function() {
                                    //re-fresh menu items list
                                    if ($('#menu_group_id').length) {
                                        $('#menu_group_id').trigger('change');
                                    }
                                }, 3000);
                            } else {
                                //show errors
                                $('#import_categories_messages').addClass('error').html(rs.message);
                            }
                        }).complete(
                            function () {
                                $btnImport.prop('disabled', false);
                            }
                        );
                    }
                }]
            });
        },

        _resetSelector: function () {
            $('#category_ids').find('option').each(function () {
                $('#category_ids-suggest').treeSuggest('removeOption', null, this);
            });
        }

    });

    return $.rs.RSImportCategories;
}));
