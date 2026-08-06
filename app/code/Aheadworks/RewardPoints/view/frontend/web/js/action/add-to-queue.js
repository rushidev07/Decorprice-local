define(
    [
        'jquery'
    ],
    function (
        $
    ) {
        'use strict';

        var previous = new $.Deferred().resolve(),
            addToQueue;

        addToQueue = function (action) {
            return previous = previous.then(action);
        };

        return addToQueue;
    }
);
