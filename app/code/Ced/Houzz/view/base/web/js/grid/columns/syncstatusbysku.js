define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function(Column, $, modal) {
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

        getProductId: function (row) {
            return row[this.index + '_productid'];
        }

    });
});
function syncStatus( id ) {
    var adminUrl = jQuery('#admin_url').val();
    var param = 'id='+id;
    jQuery.ajax({
        url: adminUrl+"admin/houzz/products/syncstatusbysku",
        data: param,
        type: "POST",
        showLoader: true,
        success: function(response){
            if(response == 1){
                try {
                    window.location.href=window.location.href;
                }
                catch(err) {
                    window.location.href=window.location.href;
                }
            }else{
                window.location.href=window.location.href;
            }
        }
    });
}