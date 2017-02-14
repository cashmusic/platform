(function() {

    var share_buttons = new function () {
        this.element = null;
        this.buttons = "";

        this.template = function (type, url) {
            var type = type.toLowerCase().trim();
            return "<a target='blank' class='cm-share-button cm-share-button-" + type + "' href='" + url + "'>" + type + "</a>";
        };

        this.buildButtons = function (element, options) {

            this.element = document.querySelector(element);

            this.buttons += this.template("facebook", "https://www.facebook.com/dialog/feed?app_id=440889266256683&link="
                + encodeURIComponent(options.url)
                + "&name=" + encodeURIComponent(options.title)
                + "&caption=" + encodeURIComponent(options.caption)
                + "&description=" + encodeURIComponent(options.description)
                + "&picture=" + encodeURIComponent(options.preview_image_url)
            );

            this.buttons += this.template("tumblr",
                "http://www.tumblr.com/share/link?url="
                + encodeURIComponent(options.url)
                + "&name=" + encodeURIComponent(options.title)
                + "&caption=" + encodeURIComponent(options.description)
            );

            this.buttons += this.template("twitter",
                "https://twitter.com/intent/tweet?text="
                + encodeURIComponent(options.title)
                + "&url=" + encodeURIComponent(options.url)
            );

            this.buttons += this.template("reddit",
                "http://www.reddit.com/submit?url="
                + encodeURIComponent(options.url)
                + "&title=" + encodeURIComponent(options.title)
            );

            this.element.innerHTML = this.element.innerHTML + this.buttons;

        };
    };
});