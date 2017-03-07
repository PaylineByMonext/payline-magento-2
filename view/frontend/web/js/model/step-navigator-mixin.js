define(
    [
        'mage/utils/wrapper',
        
    ],
    function (wrapper) {
        'use strict';
    
	return function(target) {
            return wrapper.extend(target, {
                handleHash : function(orig) {
                    if(window.location.hash.indexOf('#payment?paylinetoken') !== -1) {
                        window.location.href = window.location.href.replace('#payment', '') + '#payment';
                        return false;
                    } else {
                        return orig();
                    }
                }
            });
        };
    }
);
