function radianToDegree(angle) { return angle * (180.0 / Math.PI); }
function degreeToRadian(angle) { return Math.PI * angle / 180.0; }

function Matrix(a, b, c, d, tx, ty) {
	this.elements = [a||1, c||0, tx||0, 
					 b||0, d||1, ty||0];

	this.__defineGetter__("a", function() { return this.elements[0]; });  
	this.__defineSetter__("a", function(n) { this.elements[0]=n; });  
	this.__defineGetter__("b", function() { return this.elements[3]; });  
	this.__defineSetter__("b", function(n) { this.elements[3]=n; });
	this.__defineGetter__("c", function() { return this.elements[1]; });  
	this.__defineSetter__("c", function(n) { this.elements[1]=n; });
	this.__defineGetter__("d", function() { return this.elements[4]; });  
	this.__defineSetter__("d", function(n) { this.elements[4]=n; });
	this.__defineGetter__("tx", function() { return this.elements[2]; });  
	this.__defineSetter__("tx", function(n) { this.elements[2]=n; });
	this.__defineGetter__("ty", function() { return this.elements[5]; });  
	this.__defineSetter__("ty", function(n) { this.elements[5]=n; });
	
	this.clone = function() {	
	};
	
	this.concat = function(m) {	
	};
	
	this.identity = function() {
		this.elements = [1, 0, 0, 1, 0, 0];
	};
	
	this.scale = function(sx, sy) {
		if (sx && !sy) {
			sy = sx;
		}
		if (sx && sy) {
			this.elements[0] *= sx;
			this.elements[1] *= sy;
			this.elements[3] *= sx;
			this.elements[4] *= sy;
		}
	};
	
	this.translate = function(dx, dy) {
		this.elements[2] = dx * this.elements[0] + dy * this.elements[1] + this.elements[2];
		this.elements[5] = dx * this.elements[3] + dy * this.elements[4] + this.elements[5];
	};
	
	this.angle = 0; // faster but dumber method
	
	this.rotate = function(angle) {
		this.angle += angle;
		
		r = radianToDegree(angle);
		c = Math.cos(angle);
		s = Math.sin(angle);
		
		temp1 = this.elements[0];
		temp2 = this.elements[1];
		this.elements[0] =  c * temp1 + s * temp2;
		this.elements[1] = -s * temp1 + c * temp2;
		
		temp1 = this.elements[3];
		temp2 = this.elements[4];
		this.elements[3] =  c * temp1 + s * temp2;
		this.elements[4] = -s * temp1 + c * temp2;
		
	};
	
	
}