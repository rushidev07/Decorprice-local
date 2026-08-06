/**
 * Created by pp on 11-19-2015.
 */
var config = {
    map: {
        '*': {
            "select2": "Unirgy_Dropship/js/select2/js/select2",
            "varienForm": "Unirgy_Dropship/js/varien/form",
            "varien/form": "Unirgy_Dropship/js/varien/form"
        }
    },
    'shim': {
        "select2": ["jquery"],
        "varienForm": ["prototype"],
        "varien/form": ["prototype"],
        "Unirgy_Dropship/js/modal": ["prototype"],
        "Unirgy_Dropship/js/varien/form": ["prototype"]
    }
};
