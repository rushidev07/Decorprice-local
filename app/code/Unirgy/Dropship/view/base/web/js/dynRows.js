define([
    'jquery', 'mage/template', "prototype"
], function (jQuery, mageTemplate) {
    window.UnirgyDynRows = Class.create({
        initialize: function (options, dataRows)
        {
            this.lastRowNum = 0;
            this.parentId = options.parentId;
            this.tableId = this.__id(options.tableId);
            this.bodyId = this.__id(options.bodyId);
            this.deleteId = this.__id(options.deleteId);
            this.addBtnId = this.__id(options.addBtnId);
            this.rowTplId = options.rowTplId;
            this.rowTpl = options.rowTpl;
            this.suffixId = this.__id(options.suffixId);
            this.selectFields = options.selectFields || {};
            this.numericVars = options.numericVars || [];
            this.toggleFields = options.toggleFields || [];
            this.calendarFields = options.calendarFields || [];
            this.calendarConfig = options.calendarConfig || {};
            this.addRowCallback = options.addRowCallback;
            var addRowHandler = this.addRow.bind(this);
            this.addBtnId && $(this.addBtnId) && $(this.addBtnId).observe('click', function(e) {
                e.stop(); addRowHandler()
            });
            if (dataRows) {
                for (dataRows in __dr) {
                    this.addRow(__dr);
                }
            }
            this.subrowsConfig = [];
            this.initData = options.data || {};
        },

        tbody: function()
        {
            return $$('#'+this.tableId+' tbody.'+this.bodyId)[0];
        },

        __id: function(val)
        {
            return typeof this.parentId != 'undefined'
                ? val.replace(/___data_parent_row___/g, this.parentId)
                : val
        },

        addSubrowConfig: function(config)
        {
            this.subrowsConfig.push(config);
        },

        addRow: function (inData)
        {
            var rowTpl;
            if (this.rowTpl) {
                rowTpl = mageTemplate(this.rowTpl);
            } else {
                rowTpl = mageTemplate('#'+this.rowTplId);
            }
            var data = {
                row: this.lastRowNum,
                parent_row: this.lastRowNum
            };
            if (typeof this.parentId != 'undefined') {
                data.parent_row = this.parentId;
            }
            Object.extend(data, this.initData);
            Object.extend(data, inData);
            var rowHtml = rowTpl(data);
            var regExTpl = [
                new Template('<option (([^>]*(alt="?#{key}"?|value="?#{value}"?(?=[\\s>]))){2})'),
                new Template('<option $1 selected="selected"'),
                new Template('<input (([^>]*(alt="?#{key}"?|value="?#{value}"?(?=[\\s>])|type="?checkbox"?)){3})'),
                new Template('<input $1 checked="checked"')
            ];
            var selectFields = $H(this.selectFields);
            selectFields.each(function(pair){
                var varsForEval, value=data[pair.key], key=pair.key;
                for (var rxIdx=0; rxIdx<regExTpl.length; rxIdx+=2) {
                    varsForEval = [];
                    if (value) {
                        if (!Object.isArray(value)) {
                            value = String.interpret(value).split(',');
                        }
                        value.each(function(val){
                            varsForEval.push({key: key, value: val});
                        })
                    } else {
                        varsForEval.push({key: key, value: value});
                    }
                    for (var vfeIdx=0; vfeIdx<varsForEval.length; vfeIdx++) {
                        var varForEval = varsForEval[vfeIdx];
                        var rxFind = regExTpl[rxIdx].evaluate(varForEval);
                        var rxReplace = regExTpl[rxIdx+1].evaluate(varForEval);
                        rowHtml = rowHtml.replace(new RegExp(rxFind, 'i'), rxReplace);
                    }
                }
            }.bind(this));

            rowHtml = rowHtml.replace(/___data_parent_row___/g, data.parent_row);
            var suffixIdTpl = new Template(this.suffixId.replace(/___data_parent_row___/g, data.parent_row));

            this.tbody().insert('<tr>'+rowHtml+'</tr>')
            var trs = this.tbody().childElements()
            var tr = trs[trs.length-1]
            tr.addClassName(this.lastRowNum%2 ? 'odd' : 'even')

            var toggleFields = $A(this.toggleFields);
            toggleFields.each(function(key){
                var __sk = key;
                if (typeof this.parentId != 'undefined') {
                    __sk = __sk + '_'+data.row;
                }
                var yesKey = suffixIdTpl.evaluate({key: 'yes__'+__sk});
                var noKey = suffixIdTpl.evaluate({key: 'no__'+__sk});
                if (!data[key]) {
                    $$('.'+yesKey).invoke('hide');
                    $$('.'+noKey).invoke('show');
                } else {
                    $$('.'+yesKey).invoke('show');
                    $$('.'+noKey).invoke('hide');
                }
            }.bind(this));

            var calendarFields = $A(this.calendarFields);
            calendarFields.each(function(key){
                var __sk = key;
                if (typeof this.parentId != 'undefined') {
                    __sk = __sk + '_'+data.row;
                }
                var __cf = suffixIdTpl.evaluate({key: __sk});
                jQuery('#'+__cf).calendar(this.calendarConfig);
            }.bind(this));

            var subrows = [];
            $A(this.subrowsConfig).each(function(__sc){
                __sc.parentId = data.row
                var __dr = new UnirgyDynRows(__sc);
            });

            var del = $(tr).select('button.'+this.deleteId)[0];
            if (del) {
                $(del).observe('click', function(e) { e.stop(); $(del.parentNode.parentNode).remove() })
            }
            this.lastRowNum++;
            if (this.addRowCallback) {
                this.addRowCallback(data, tr);
            }
        }
    });
    return window.UnirgyDynRows;
});
