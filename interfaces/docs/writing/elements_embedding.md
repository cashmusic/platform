We use our (tiny) custom javascript library, [cashmusic.js](http://cashmusic.github.io/cashmusic.js/), 
to create iframe embeds for elements. They can be styled to match any site with full user control over
CSS and markup. Embedding is pretty straightforward and happens with a single copy and paste code.

In a basic example, an element is embedded in place by id only:

<script src="https://gist.github.com/jessevondoom/5856499.js"></script>

Endpoint and id are always required, but you can also choose to have the element 
appear in an overlay (lightboxed.) A lightboxed element will create a link inline 
with the caption passed in to the window.cashmusic.embed function. You can also 
pass in an object specifying size and position of the element inside the overlay. 

For embed calls after page load, provide a target element as the final argument to 
window.cashmusic.embed. This will place the embed, iframe or lightbox link, inside 
the first matching element. The target should be a string that will work with 
document.querySelector, like "#id", "#id .class", or similar.

For styling, all iframe embeds are placed in a &lt;div&gt; classed with "cashmusic embed" 
and lightboxed embed links are placed in a &lt;span&gt; classed "cashmusic embed".

An example with all options:

<script src="https://gist.github.com/jessevondoom/5860605.js"></script>

We're also working on a new JSON object based embed call. It's mostly for clear 
formatting, but you'll notice a new CSS override option not available by the 
standard method. More on that soon...

<script src="https://gist.github.com/jessevondoom/ccfb4f71f7a905d82470.js"></script>