/**
* The Point object represents a location in a two-dimensional coordinate system, where x represents the horizontal axis and y represents the vertical axis.
* @constructor
* @author Leandro Ferreira
*/
function Point(a,b){return this.x=a||0,this.y=b||0,this.__defineGetter__("length",function(){return Math.sqrt(this.x*this.x+this.y*this.y)}),this.add=function(a){return new Point(this.x+a.x,this.y+a.y)},this.clone=function(){return new Point(this.x,this.y)},Point.distance=function(){var c=p2.x-p1.x,d=p2.y-p1.y;return Math.sqrt(c*c+d*d)},this.equals=function(a){return this.x==a.x&&this.y==a.y},Point.interpolate=function(a,b,c){var d=new Point;return d.x=p1.x+c*(p2.x-p1.x),d.y=p1.y+c*(p2.y-p1.y),d},this.normalize=function(a){var b=a/this.length;this.x*=b,this.y*=b},this.offset=function(a,b){this.x+=a,this.y+=b},Point.polar=function(a,b){return new Point(a*Math.cos(b),a*Math.sin(b))},this.subtract=function(a){return new Point(this.x-a.x,this.y=a.y)},this}