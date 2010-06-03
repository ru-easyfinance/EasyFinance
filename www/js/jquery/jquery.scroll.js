/*****************************************************************************
 * jQuery Headline Scroll
 * Version: 2.0 released 2009-02-11
 * Author: Dave Ross <dave@csixty4.com>
 * License: BSD
 * Requires: jQuery 1.2.1 or higher (may work with others, not tested)
 *
 * To make an element scroll automatically, assign it a height through CSS
 * and give it the class "autoscroll".
 *
 * To control scroll speed, give new values to these global variables:
 *   scrollerIntervalMs = {how often to scroll, in milliseconds, default 200}
 *   scrollerStep       = {# of pixels to scroll each interval, default 1}
 *
 * Copyright (c) 2009, Dave Ross <dave@csixty4.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Dave Ross ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <copyright holder> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ****************************************************************************/

var scrollerIntervalMs = 200;

function jQueryAutoScroll() {}

jQueryAutoScroll.initScroller = function()
{
    $('.autoscroll').each( function() {

            // Add event handlers to toggle pausing
            $(this).mouseover(function() { jQueryAutoScroll.doPause(this); });
            $(this).mouseout(function() { jQueryAutoScroll.doPause(this); });

            // Default each elements' scrollerStep to 1 if not set
            if($(this).attr('scrollerStep') == undefined)
                if(undefined === window.scrollerStep)
                    $(this).attr('scrollerStep', 1);
                else
                    $(this).attr('scrollerStep', scrollerStep);

    });

    // Set interval to scroll another step every scrollerIntervalMs
    setInterval("jQueryAutoScroll.doScroll()", scrollerIntervalMs);
}

jQueryAutoScroll.doScroll = function()
{
    $('.autoscroll').filter(':not(".paused")').each( function() {

        var scrollerStep = parseInt($(this).attr('scrollerStep'), 10);

        if($(this).hasClass('horizontalscroll'))
        {
            var x = this.scrollLeft;

            // The height of the div, as defined by CSS
            var divWidth = parseInt($(this).css('width'));

            // The height of the div's contents
            var contentWidth = this.scrollWidth;

            // Increment the position we're scrolling to by 1px
            x += scrollerStep;

            // Start over if we've scrolled too far
            if((x <= 0) || (x >= (contentWidth - divWidth)))
            {
                 if($(this).hasClass('reversingscroll'))
                 {
                     scrollerStep *= -1;
                     x = x + scrollerStep;
                 }
                 else
                 {
                     x = 0;
                 }
            }

            // Scroll!
            this.scrollLeft = x;
        }
        else
        {
            var x = this.scrollTop;

            // The height of the div, as defined by CSS
            var divHeight = parseInt($(this).css('height'));

            // The height of the div's contents
            var contentHeight = this.scrollHeight;

            // Increment the position we're scrolling to by 1px
            x += scrollerStep;

            // Start over if we've scrolled too far
            if((x <= 0) || (x >= (contentHeight - divHeight)))
            {
                 if($(this).hasClass('reversingscroll'))
                 {
                     scrollerStep *= -1;
                     x = x + scrollerStep;
                 }
                 else
                 {
                     x = 0;
                 }
            }

            // Scroll!
            this.scrollTop = x;
        }

        $(this).attr('scrollerStep', scrollerStep);

    });
}

jQueryAutoScroll.doPause = function(el)
{
    $(el).toggleClass('paused');
}

$(document).ready(function() {
   jQueryAutoScroll.initScroller();
});