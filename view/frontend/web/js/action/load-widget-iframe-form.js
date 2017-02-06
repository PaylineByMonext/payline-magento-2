define(
    [
        'jquery',
        'mage/url',
    ],
    function ($, urlBuilder) {
        'use strict';

        return function (urlPath, iframeFormId, iframeFormContainerId) {
            $('<iframe>', {
                src: urlBuilder.build(urlPath),
                id:  iframeFormId,
                frameborder: 0
            })
            .css({'width': '100%'})
            .appendTo('#'+iframeFormContainerId)
            .on('load', function() {
                // this below part of code is from http://stackoverflow.com/a/21438822
                var self = this;
                try {
                    var target = $(self).contents()[0].body;

                    var observer = new MutationObserver(function(mutations) {
                        $(self).height('auto');
                        var newHeight = $('html', $(self).contents()[0]).height();
                        $(self).height(newHeight);
                    });

                    var config = {
                        attributes: true,
                        childList: true,
                        characterData: true,
                        subtree: true
                    };

                    observer.observe(target, config);
                } catch(err) {
                    //console.log(err);
                    $(self).height(680);
                }
            });
        };
    }
);
