/*
 * jQuery Hoverbox 1.0
 * http://koteako.com/hoverbox/
 *
 * Copyright (c) 2009 Eugeniy Kalinin
 * Dual licensed under the MIT and GPL licenses.
 * http://koteako.com/hoverbox/license/
 */
jQuery.fn.hoverbox = function(options) {
    var settings = jQuery.extend({
        id: 'tooltip',
        top: 0,
        left: 15
    }, options);

    var handle;

    function tooltip(event) {
        if ( ! handle) {
            // Create an empty div to hold the tooltip
            handle = $('<div style="position:absolute" id="'+settings.id+'"></div>').appendTo(document.body).hide();
        }

        if (event) {
            // Make the tooltip follow a cursor
            handle.css({
                top: (event.pageY - settings.top) + "px",
                left: (event.pageX + settings.left) + "px"
            });
        }

        return handle;
    }

    this.each(function() {
        $(this).hover(
            function(e) {
                if (this.title) {
                    // Remove default browser tooltips
                    this.t = this.title;
                    this.title = '';
                    this.alt = '';

                    tooltip(e).html(this.t).fadeIn('fast');
                }
            },
            function() {
                if (this.t) {
                    this.title = this.t;
                    tooltip().hide();
                }
            }
        );

        $(this).mousemove(tooltip);
    });
};