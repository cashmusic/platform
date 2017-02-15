(function() {
    'use strict';
    var cm = window.cashmusic;

    cm.shareButtons = {

        template: function (type, url) {
            var type = type.toLowerCase().trim();
            return "<a target='blank' class='cm-share-button cm-share-button-" + type + "' href='" + url + "'>" + type + "</a>";
        },

        buildButtons: function (element, options) {

            var element = document.querySelector(element);

            var buttons = this.template("facebook", "https://www.facebook.com/dialog/feed?app_id=440889266256683&link="
                + encodeURIComponent(options.url)
                + "&name=" + encodeURIComponent(options.title)
                + "&caption=" + encodeURIComponent(options.caption)
                + "&description=" + encodeURIComponent(options.description)
                + "&picture=" + encodeURIComponent(options.preview_image_url)
            );

            buttons += this.template("tumblr",
                "http://www.tumblr.com/share/link?url="
                + encodeURIComponent(options.url)
                + "&name=" + encodeURIComponent(options.title)
                + "&caption=" + encodeURIComponent(options.description)
            );

            buttons += this.template("twitter",
                "https://twitter.com/intent/tweet?text="
                + encodeURIComponent(options.title)
                + "&url=" + encodeURIComponent(options.url)
            );

            buttons += this.template("reddit",
                "http://www.reddit.com/submit?url="
                + encodeURIComponent(options.url)
                + "&title=" + encodeURIComponent(options.title)
            );

            element.innerHTML = element.innerHTML + buttons;

        }
    };
}());