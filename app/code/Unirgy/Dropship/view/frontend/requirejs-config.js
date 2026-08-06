/**
 * Created by pp on 11-19-2015.
 */
var config = {
    map: {
        '*': {
            "select2": "Unirgy_Dropship/js/select2/js/select2.full",
            "unirgyModal": "Unirgy_Dropship/js/modal",
            "calendar": "mage/calendar",
            "varienForm": "Unirgy_Dropship/js/varien/form",
            "varien/form": "Unirgy_Dropship/js/varien/form",
            "unirgyDynRows": "Unirgy_Dropship/js/dynRows"
        }
    },
    'shim': {
        "select2": ["jquery"],
        "unirgyModal": ["prototype"],
        "varienForm": ["prototype"],
        "varien/form": ["prototype"],
        "Unirgy_Dropship/js/modal": ["prototype"],
        "Unirgy_Dropship/js/varien/form": ["prototype"],
        "unirgyDynRows": ["prototype","jquery"]
    }
};
