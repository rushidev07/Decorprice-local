define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Column, $, modal) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
        },

        getTitle: function (row) {
            return row[this.index + '_title'];
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        },

        getFeedId: function (row) {
            return row[this.index + '_feedid'];
        },

        getFeedErrors: function (row) {
            return row[this.index + '_feederrors'];
        },
        startView: function (row) {
            if (this.getFeedErrors(row)) {
                //console.log(this.getFeedId(row));
                var previewPopup = $('<div/>',{id : 'houzzpopup'+this.getFeedId(row) });
                var data = $.parseJSON(this.getFeedErrors(row));
                var result = '<table class="data-grid" style="margin-bottom:25px"><tr><th style="padding:15px">SKU</th><th style="padding:15px">Errors</th></tr>';
                $.each(data, function(index, value){
                    if(!Array.isArray(value)) {
                        value = [ value ];
                    }
                    $.each(value, function(j, sku) {
                        $.each(sku, function (i, error) {
                            var slno = i;
                            $.each(error, function (k, skuerror) {
                                var errors = "";
                                if(Array.isArray(skuerror)) {
                                    $.each(skuerror, function (errorIndex, errorMsg) {
                                        $.each(errorMsg, function (errorIndexs, errorMsgs) {
                                            errors += errorIndexs + ': ' + errorMsgs + '<br>';
                                        });
                                    });
                                }else{
                                    $.each(skuerror, function (errorIndex, errorMsg) {
                                        errors += errorIndex + ': ' + errorMsg + '<br>';
                                    });
                                }
                                result += '<tr><td>' + slno + '</td><td>' + errors + '</td></tr>';
                            });
                        });
                    });
                });
                result += '</table>';
                var houzzpopup = previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalLeftMargin: 15,
                    buttons: [],
                    opened: function (row) {
                        houzzpopup.append(result);
                    },
                    closed: function (row) { }
                }).trigger('openModal');
            }
        },

        getFieldHandler: function (row) {
            return this.startView.bind(this, row);
        },

    });

});
