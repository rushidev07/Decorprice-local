/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
/*jshint browser:true*/
/*global alert:true*/
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'useDefault',
    'collapsable',
    'mage/translate',
    'mage/backend/validation'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.customOptions', {
        options: {
            selectionItemCount: {}
        },

        _create: function () {
            this.baseTmpl = mageTemplate('#custom-option-base-template');
            this.rowTmpl = mageTemplate('#custom-option-select-type-row-template');

            this._initOptionBoxes();
            this._initSortableSelections();
            this._bindCheckboxHandlers();
            this._bindReadOnlyMode();
            this._addValidation();
        },

        _addValidation: function () {
            $.validator.addMethod(
                'required-option-select', function (value) {
                    return (value !== '');
                }, $.mage.__('Select type of option.'));

            $.validator.addMethod(
                'required-option-select-type-rows', function (value, element) {
                    var optionContainerElm = element.up('div[id*=_type_]'),
                        selectTypesFlag = false,
                        selectTypeElements = $('#' + optionContainerElm.id + ' .select-type-value');

                    selectTypeElements.each(function () {
                        if (!$(this).closest('tr').hasClass('ignore-validate')) {
                            selectTypesFlag = true;
                        }
                    });

                    return selectTypesFlag;
                }, $.mage.__('Please add rows to option.'));
        },

        _initOptionBoxes: function () {
            if (!this.options.isReadonly) {
                this.element.sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    items: '#field_options_container_top > div',
                    update: this._updateOptionBoxPositions,
                    tolerance: 'pointer'
                });
            }
            var syncOptionTitle = function (event) {
                var currentValue = $(event.target).val(),
                    optionBoxTitle = $('.admin__collapsible-title > span', $(event.target).closest('.fieldset-wrapper')),
                    newOptionTitle = $.mage.__('New Field');
                currentValue = $('<div>'+currentValue+'</div>').text();
                optionBoxTitle.text(currentValue === '' ? newOptionTitle : currentValue);
            };
            this._on({
                /**
                 * Reset field value to Default
                 */
                'click .use-default-label': function (event) {
                    $(event.target).closest('label').find('input').prop('checked', true).trigger('change');
                },

                /**
                 * Remove custom option or option row for 'select' type of custom option
                 */
                'click button[id^=field_option_][id$=_delete]': function (event) {
                    var element = $(event.target).closest('#field_options_container_top > div.fieldset-wrapper,tr');

                    if (element.length) {
                        $('#field_' + element.attr('id').replace('field_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate').hide();
                        this.refreshSortableElements();
                    }
                },
                /**
                 * Minimize custom option block
                 */
                'click #field_options_container_top [data-target$=-content]': function () {
                    if (this.options.isReadonly) {
                        return false;
                    }
                },

                /**
                 * Add new custom option
                 */
                'click #add_new_defined_option': function (event) {
                    this.addOption(event);
                },

                /**
                 * Add new option row for 'select' type of custom option
                 */
                'click button[id^=field_option_][id$=_add_select_row]': function (event) {
                    this.addSelection(event);
                },

                /**
                 * Import custom options from fields
                 */
                'click #import_new_defined_option': function () {
                    var importContainer = $('#import-container'),
                        widget = this;

                    importContainer.dialog({
                        title: $.mage.__('Select Product'),
                        autoOpen: false,
                        minWidth: 980,
                        width: '75%',
                        modal: true,
                        resizable: true,
                        position: {
                            my: 'left top',
                            at: 'center top',
                            of: 'body'
                        },
                        create: function (event, ui) {
                            $(document).on('click', '#fieldGrid_massaction-form button', function () {
                                $('#import-custom-options-apply-button').trigger('click', 'massActionTrigger');
                            });
                        },
                        open: function () {
                            $(this).closest('.ui-dialog').addClass('ui-dialog-active');

                            var topMargin = $(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 135;
                            $(this).closest('.ui-dialog').css('margin-top', topMargin);

                            $(this).addClass('admin__scope-old'); // ToDo UI: remove with old styles removal
                        },
                        close: function () {
                            $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                        },
                        buttons: [
                            {
                                text: $.mage.__('Import'),
                                id: 'import-custom-options-apply-button',
                                'class': 'action-primary action-import',
                                click: function (event, massActionTrigger) {
                                    var request = [];
                                    $(this).find('input[name=field]:checked').map(function () {
                                        request.push(this.value);
                                    });

                                    if (request.length === 0) {
                                        if (!massActionTrigger) {
                                            alert($.mage.__('Please select items.'));
                                        }

                                        return;
                                    }

                                    $.post(widget.options.customOptionsUrl, {
                                        'fields[]': request,
                                        form_key: widget.options.formKey
                                    }, function ($data) {
                                        $.parseJSON($data).each(function (el) {
                                            el.id = widget.getFreeOptionId(el.id);
                                            el.option_id = el.id;

                                            if (typeof el.optionValues !== 'undefined') {
                                                for (var i = 0; i < el.optionValues.length; i++) {
                                                    el.optionValues[i].option_id = el.id;
                                                }
                                            }
                                            //Adding option
                                            widget.addOption(el);
                                            //Will save new option on server side
                                            $('#field_option_' + el.id + '_option_id').val(0);
                                            $('#option_' + el.id + ' input[name$="option_type_id]"]').val(-1);
                                        });
                                        importContainer.dialog('close');
                                    });
                                }
                            },
                            {
                                text: $.mage.__('Cancel'),
                                id: 'import-custom-options-close-button',
                                'class': 'action-close',
                                click: function () {
                                    $(this).dialog('close');
                                }
                            }]
                    });
                    importContainer.load(
                        this.options.fieldGridUrl,
                        {form_key: this.options.formKey},
                        function () {
                            importContainer.dialog('open');
                        }
                    );
                },

                /**
                 * Change custom option type
                 */
                'change select[id^=field_option_][id$=_type]': function (event, data) {


                    data = data || {};
                    var widget = this,
                        currentElement = $(event.target),
                        parentId = '#' + currentElement.closest('.fieldset-alt').attr('id'),
                        group = currentElement.find('[value="' + currentElement.val() + '"]').closest('optgroup').attr('label'),
                        previousGroup = $(parentId + '_previous_group').val(),
                        previousBlock = $(parentId + '_type_' + previousGroup),
                        tmpl;

                    if (typeof group !== 'undefined') {
                        group = group.toLowerCase();
                    }

                    if (group == 'hidden') {
                        $(parentId).find('.field-option-default').show();
                        $(parentId).find('.field-option-req').hide();
                    } else {
                        $(parentId).find('.field-option-default').hide();
                        $(parentId).find('.field-option-req').show();
                    }

                    if (previousGroup !== group) {
                        if (previousBlock.length) {
                            previousBlock.addClass('ignore-validate').hide();
                        }
                        $(parentId + '_previous_group').val(group);

                        if (typeof group === 'undefined') {
                            return;
                        }
                        var disabledBlock = $(parentId).find(parentId + '_type_' + group);

                        if (disabledBlock.length) {
                            disabledBlock.removeClass('ignore-validate').show();
                        } else {
                            if ($.isEmptyObject(data)) {
                                data.option_id = $(parentId + '_id').val();
                                data.price = data.sku = '';
                            }
                            data.group = group;
                            console.log('#custom-option-' + group + '-type-template');
                            tmpl = widget.element.find('#custom-option-' + group + '-type-template').html();
                            tmpl = mageTemplate(tmpl, {
                                data: data
                            });

                            $(tmpl).insertAfter($(parentId));

                            if (data.price_type) {
                                var priceType = $('#' + widget.options.fieldId + '_' + data.option_id + '_price_type');
                                priceType.val(data.price_type).attr('data-store-label', data.price_type);
                            }
                            this._bindUseDefault(widget.options.fieldId + '_' + data.option_id, data);
                            //Add selections
                           // console.log(data.optionValues);
                            if (data.optionValues) {
                                data.optionValues.each(function (value) {
                                    widget.addSelection(value);
                                });
                            }
                        }
                    }
                },
                //Sync title
                'change .field-option-label > .control > input[id$="_label"]': syncOptionTitle,
                'keyup .field-option-label > .control > input[id$="_label"]': syncOptionTitle,
                'paste .field-option-label > .control > input[id$="_label"]': syncOptionTitle
            });
        },

        _initSortableSelections: function () {
            if (!this.options.isReadonly) {
                this.element.find('[id^=field_option_][id$=_type_select] tbody').sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    helper: function (event, ui) {
                        ui.children().each(function () {
                            $(this).width($(this).width());
                        });

                        return ui;
                    },
                    update: this._updateSelectionsPositions,
                    tolerance: 'pointer'
                });
            }
        },

        /**
         * Sync sort order checkbox with hidden dropdown
         */
        _bindCheckboxHandlers: function () {
            this._on({
                'change [id^=field_option_][id$=_required]': function (event) {
                    var $this = $(event.target);
                    $this.closest('#field_options_container_top > div').find('[name$="[is_require]"]').val($this.is(':checked') ? 1 : 0);
                }
            });
            this.element.find('[id^=field_option_][id$=_required]').each(function () {
                $(this).prop('checked', $(this).closest('#field_options_container_top > div').find('[name$="[is_require]"]').val() > 0);
            });

            this._on({
                'change [id^=field_option_][id$=_after_email]': function (event) {
                    var $this = $(event.target);
                    console.log('aha');
                    $this.closest('#field_options_container_top > div').find('[name$="[after_email_field]"]').val($this.is(':checked') ? 1 : 0);
                }
            });
            this.element.find('[id^=field_option_][id$=_after_email]').each(function () {
                console.log('bag');
             console.log( $(this).closest('#field_options_container_top > div').find('[name$="[after_email_field]"]').val() +' << ' +$(this).attr('id'));
                $(this).prop('checked', $(this).closest('#field_options_container_top > div').find('[name$="[after_email_field]"]').val() > 0);
            });


        },

        /**
         * Update Custom option position
         */
        _updateOptionBoxPositions: function () {
            $(this).find('div[id^=option_]:not(.ignore-validate) .fieldset-alt > [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },

        /**
         * Update selections positions for 'select' type of custom option
         */
        _updateSelectionsPositions: function () {
            $(this).find('tr:not(.ignore-validate) [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },

        /**
         * Disable input data if "Read Only"
         */
        _bindReadOnlyMode: function () {
            if (this.options.isReadonly) {
                $('div.field-custom-options').find('button,input,select,textarea').each(function () {
                    $(this).prop('disabled', true);

                    if ($(this).is('button')) {
                        $(this).addClass('disabled');
                    }
                });
            }
        },

        _bindUseDefault: function (id, data) {
            var title = $('#' + id + '_label'),
                price = $('#' + id + '_price'),
                priceType = $('#' + id + '_price_type');
            console.log(title);
            //enable 'use default' link for title
            if (data.checkboxScopeTitle) {
                title.useDefault({
                    field: '.field',
                    useDefault: 'label[for$=_label]',
                    checkbox: 'input[id$=_label_use_default]',
                    label: 'span'
                });
            }
            //enable 'use default' link for price and price_type
            if (data.checkboxScopePrice) {
                price.useDefault({
                    field: '.field',
                    useDefault: 'label[for$=_price]',
                    checkbox: 'input[id$=_price_use_default]',
                    label: 'span'
                });
                //@TODO not work set default value for second field
                priceType.useDefault({
                    field: '.field',
                    useDefault: 'label[for$=_price]',
                    checkbox: 'input[id$=_price_use_default]',
                    label: 'span'
                });
            }
        },

        /**
         * Add selection value for 'select' type of custom option
         */
        addSelection: function (event) {

            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                rowTmpl;

            console.log(element);
            if (typeof element !== 'undefined') {
                data.id = $(element).closest('#field_options_container_top > div')
                    .find('[name^="field[options]"][name$="[id]"]').val();
                data.option_type_id = -1;

                if (!this.options.selectionItemCount[data.id]) {
                    this.options.selectionItemCount[data.id] = 1;
                }

                data.select_id = this.options.selectionItemCount[data.id];
                data.price = data.sku = '';
            } else {
                data = event;
                data.id = data.option_id;
                data.select_id = data.option_type_id;
                this.options.selectionItemCount[data.id] = data.item_count;
            }

            rowTmpl = this.rowTmpl({
                data: data
            });

            $(rowTmpl).appendTo($('#select_option_type_row_' + data.id));

            //set selected price_type value if set

            this._bindUseDefault(this.options.fieldId + '_' + data.id + '_select_' + data.select_id, data);
            this.refreshSortableElements();
            this.options.selectionItemCount[data.id] = parseInt(this.options.selectionItemCount[data.id], 10) + 1;
        },

        /**
         * Add custom option
         */
        addOption: function (event) {
            console.log(event);
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                baseTmpl;

            if (typeof element !== 'undefined') {
                data.id = this.options.itemCount;
                data.type = '';
                data.option_id = 0;
            } else {
                data = event;
                this.options.itemCount = data.id;
            }

            console.log(data);

            baseTmpl = this.baseTmpl({
                data: data
            });

            $(baseTmpl)
                .appendTo(this.element.find('#field_options_container_top'))
                .find('.collapse').collapsable();

            //set selected type value if set
            if (data.type) {
                $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.type).trigger('change', data);
            }

            //set selected is_require value if set
            if (data.is_require) {
                $('#' + this.options.fieldId + '_' + data.id + '_is_require').val(data.is_require).trigger('change');
            }

            if (data.after_email_field) {
                $('#' + this.options.fieldId + '_' + data.id + '_after_email_field').val(data.after_email_field).trigger('change');
            }

            this.refreshSortableElements();
            this._bindCheckboxHandlers();
            this._bindReadOnlyMode();
            this.options.itemCount++;
            $('#' + this.options.fieldId + '_' + data.id + '_label').trigger('change');
        },

        refreshSortableElements: function () {
            if (!this.options.isReadonly) {
                this.element.sortable('refresh');
                this._updateOptionBoxPositions.apply(this.element);
                this._updateSelectionsPositions.apply(this.element);
                this._initSortableSelections();
            }

            return this;
        },

        getFreeOptionId: function (id) {
            return $('#' + this.options.fieldId + '_' + id).length ? this.getFreeOptionId(parseInt(id, 10) + 1) : id;
        }
    });

});
