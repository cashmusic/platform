/*
 * BitmapData.js by Peter Nitsch - https://github.com/pnitsch/BitmapData.js
 * HTML5 Canvas API implementation of the AS3 BitmapData class. 
 */

const halfColorMax = 0.00784313725;

var BlendMode = new function() {
	this.ADD = "add";
	this.ALPHA = "alpha";
	this.DARKEN = "darken";
	this.DIFFERENCE = "difference";
	this.ERASE = "erase";
	this.HARDLIGHT = "hardlight";
	this.INVERT = "invert";
	this.LAYER = "layer";
	this.LIGHTEN = "lighten";
	this.HARDLIGHT = "hardlight";
	this.MULTIPLY = "multiply";
	this.NORMAL = "normal";
	this.OVERLAY = "overlay";
	this.SCREEN = "screen";
	this.SHADER = "shader";
	this.SUBTRACT = "subtract";
};

var BitmapDataChannel = new function() {
	this.ALPHA = 8;
	this.BLUE = 4;
	this.GREEN = 2;
	this.RED = 1;
};

// RGB <-> Hex conversion
function hexToRGB (hex) { return { r: ((hex & 0xff0000) >> 16), g: ((hex & 0x00ff00) >> 8), b: ((hex & 0x0000ff)) }; };
function RGBToHex(rgb) { return rgb.r<<16 | rgb.g<<8 | rgb.b; };

// 256-value binary Vector struct
function histogramVector(n) { 
	var v=[]; 
	for (var i=0; i<256; i++) { v[i] = n; }
	return v
}

// Park-Miller-Carta Pseudo-Random Number Generator
function PRNG() {
	this.seed = 1;
	this.next = function() { return (this.gen() / 2147483647); };
	this.nextRange = function(min, max)	{ return min + ((max - min) * this.next()) };
	this.gen = function() { return this.seed = (this.seed * 16807) % 2147483647; };
};

function BitmapData(width, height, transparent, fillColor, canvas) {
	this.width = width;
	this.height = height;
	this.rect = new Rectangle(0, 0, this.width, this.height);
	this.transparent = transparent || false;

	this.canvas = canvas || document.createElement("canvas");
	this.context = this.canvas.getContext("2d");
	this.canvas.setAttribute('width', this.width);
	this.canvas.setAttribute('height', this.height);
	
	this.drawingCanvas = document.createElement("canvas");
	this.drawingContext = this.drawingCanvas.getContext("2d");

	this.imagedata = this.context.createImageData(this.width, this.height);
	this.__defineGetter__("data", function() { return this.imagedata; });  	
	this.__defineSetter__("data", function(source) { this.imagedata = source; });
	
	
	/*** WebGL functions ***/
	
	this.glCanvas = document.createElement("canvas");
	this.gl = null;
	this.program = null;
	this.gpuEnabled = true;
	try { this.gl = this.glCanvas.getContext("experimental-webgl"); } 
	catch (e) { this.gpuEnabled = false; }
	
	this.va = null;
	this.tex0 = null;
	this.tex1 = null;
	this.glPixelArray = null;
	
	this.initProgram = function(effect) {
		var gl = this.gl;
		var program = gl.createProgram();

		var vs = gl.createShader(gl.VERTEX_SHADER);
		var fs = gl.createShader(gl.FRAGMENT_SHADER);

		gl.shaderSource(vs, effect.vsSrc);
		gl.shaderSource(fs, effect.fsSrc);
		gl.compileShader(vs);
		gl.compileShader(fs);
		
		if (!gl.getShaderParameter(vs, gl.COMPILE_STATUS)) { gl.deleteProgram( program ); }
		if (!gl.getShaderParameter(fs, gl.COMPILE_STATUS)) { gl.deleteProgram( program ); }

		gl.attachShader(program, vs);
		gl.attachShader(program, fs);
		gl.deleteShader(vs);
		gl.deleteShader(fs);

		gl.linkProgram(program);
		if( this.program != null ) gl.deleteProgram( this.program );
		this.program = program;
		
		gl.viewport( 0, 0, this.canvas.width, this.canvas.height );
		gl.useProgram(program);
		
		var vertices = new Float32Array(
			[-1.0, -1.0, 
			1.0, -1.0, 
			-1.0,  1.0, 
			1.0, -1.0, 
			1.0,  1.0, 
			-1.0, 1.0]);
			
	    this.va = gl.createBuffer();
	    gl.bindBuffer(gl.ARRAY_BUFFER, this.va);
	    gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);
	};
	
	this.initTexture = function(pos, image) {
		var gl = this.gl;
		var tex = gl.createTexture();

		gl.enable(gl.TEXTURE_2D);
		gl.bindTexture(gl.TEXTURE_2D, tex);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.REPEAT);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.REPEAT);
		gl.generateMipmap(gl.TEXTURE_2D)
		gl.bindTexture(gl.TEXTURE_2D, null);
		
		if( pos == 0 ) {
			if(this.tex0 != null) gl.deleteTexture(this.tex0); 
			this.tex0 = tex;
			
			this.glCanvas.setAttribute('width', image.width);
			this.glCanvas.setAttribute('height', image.height);
			this.glPixelArray = new Uint8Array(image.width * image.height * 4);
		} else {
			if(this.tex1 != null) gl.deleteTexture(this.tex1); 
			this.tex1 = tex;
		}
	};
	
	this.drawGL = function(matrix) {
		var gl = this.gl;
		var program = this.program;
		var ra = [matrix.a, matrix.c, 0, matrix.b, matrix.d, 0, 0, 0, 1];
		
		var p = gl.getAttribLocation(program, "pos");
		var ur = gl.getUniformLocation(program, "r");
		var ut = gl.getUniformLocation(program, "t");
		var t0 = gl.getUniformLocation(program, "tex0");
		var t1 = gl.getUniformLocation(program, "tex1");
		var rm = gl.getUniformLocation(program, "rMat");

		gl.bindBuffer(gl.ARRAY_BUFFER, this.va);

		gl.uniform2f(ur, this.glCanvas.width*2, this.glCanvas.height*2);
		gl.uniformMatrix3fv(rm, false, new Float32Array(ra));
		gl.uniform2f(ut, matrix.tx, matrix.ty);

		gl.vertexAttribPointer(p, 2, gl.FLOAT, false, 0, 0);
		gl.enableVertexAttribArray(p);

		gl.uniform1i(t0, 0 ); 
		gl.activeTexture(gl.TEXTURE0); 
		gl.bindTexture(gl.TEXTURE_2D, this.tex0); 
		
		gl.uniform1i(t1, 1 ); 
		gl.activeTexture(gl.TEXTURE1); 
		gl.bindTexture(gl.TEXTURE_2D, this.tex1);

		gl.drawArrays(gl.TRIANGLES, 0, 6);
		gl.disableVertexAttribArray(p);

		gl.flush();
		
		var w = this.glCanvas.width;
		var h = this.glCanvas.height;
		var arr = this.glPixelArray;
		gl.readPixels(0, 0, w, h, gl.RGBA, gl.UNSIGNED_BYTE, arr);
		
		var pos;
		var data = this.imagedata.data;
		for (var y=0; y<h; y++) {
			for (var x=0; x<w; x++) {
				pos = (x + y * w) * 4; 
				data[pos] = arr[pos];
				data[pos+1] = arr[pos+1];
				data[pos+2] = arr[pos+2];
			}
		}
	};
	
	
	
	/*** Canvas2D functions ***/
	
	this.setPixel = function(x, y, color) {
		var rgb = hexToRGB(color);
		var pos = (x + y * this.width) * 4;
		var data = this.imagedata.data;

		data[pos+0] = rgb.r;
		data[pos+1] = rgb.g;
		data[pos+2] = rgb.b;
	};
	
	this.getPixel = function(x, y) {
		var pos = (x + y * this.width) * 4;
		var data = this.imagedata.data;
		var rgb = {
			r: data[pos+0],
			g: data[pos+1],
			b: data[pos+2]
		};
		
		return RGBToHex(rgb);
	};
	
	this.clear = function(rect) {
		rect=rect || this.rect;
		this.context.clearRect(rect.x, rect.y, rect.width, rect.height);
		this.imagedata = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
	};
	
	this.clone = function() {
		this.context.putImageData(this.imagedata, 0, 0);
		
		var result = new BitmapData(this.width, this.height, this.transparent);
		result.data = this.context.getImageData(0, 0, this.width, this.height);
		return result;
	};
	
	this.colorTransform=function(rect, colorTransform)
	{
		rect=rect || this.rect;
		colorTransform=colorTransform || new ColorTransform();
		
		var data=this.imagedata.data;
		var xMax=rect.x+rect.height;
		var yMax=rect.y+rect.height;
	
		for(var y=rect.y;y<yMax;y++)
		{
			for(var x=rect.x;x<xMax;x++)
			{
				var r=(y*this.width+x)*4;
				var g=r+1;
				var b=r+2
				var a=r+3;
				
				data[r]=data[r]*colorTransform.redMultiplier+colorTransform.redOffset;
				data[g]=data[g]*colorTransform.greenMultiplier+colorTransform.greenOffset;
				data[b]=data[b]*colorTransform.blueMultiplier+colorTransform.blueOffset;
				data[a]=data[a]*colorTransform.alphaMultiplier+colorTransform.alphaOffset;
			}
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	}
	
	this.applyFilter = function(sourceBitmapData, sourceRect, destPoint, filter) {
		var copy = this.clone();
		filter.run(sourceRect, this.imagedata.data, copy.imagedata.data);
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	this.compare = function(otherBitmapData) {
		if(this.width != otherBitmapData.width) return -3;
		if(this.height != otherBitmapData.height) return -4;
		if(this.imagedata === otherBitmapData.data) return 0; 
		
		var otherRGB, thisRGB, dif;
		var result = new BitmapData(this.width, this.height);
		for (var y = 0; y < this.height; y++) {
			for (var x = 0; x < this.width; x++) {
				otherRGB = hexToRGB( otherBitmapData.getPixel(x, y) );
				thisRGB = hexToRGB( this.getPixel(x, y) );
				
				dif = {
					r: Math.abs(otherRGB.r - thisRGB.r),
					g: Math.abs(otherRGB.g - thisRGB.g),
					b: Math.abs(otherRGB.b - thisRGB.b)
				};
				
				result.setPixel(x, y, RGBToHex(dif));
			}
		}
		
		return result;
	};
	
	this.copyCanvas = function(sourceCanvas, sourceRect, destPoint, blendMode) {
		this.context.putImageData(this.imagedata, 0, 0);
		
		var bw = this.canvas.width - sourceRect.width - destPoint.x;
		var bh = this.canvas.height - sourceRect.height - destPoint.y

		var dw = (bw < 0) ? sourceRect.width + (this.canvas.width - sourceRect.width - destPoint.x) : sourceRect.width;
		var dh = (bh < 0) ? sourceRect.height + (this.canvas.height - sourceRect.height - destPoint.y) : sourceRect.height;
		
		if(blendMode && blendMode != BlendMode.NORMAL) {

			var sourceData = sourceCanvas.getContext("2d").getImageData(sourceRect.x, sourceRect.y, dw, dh).data;
			var sourcePos, destPos;
			var data = this.imagedata.data;
			
			for (var y=0; y<dh; y++) {
				for (var x=0; x<dw; x++) {
					sourcePos = (x + y * dw) * 4;
					destPos = ((x+destPoint.x) + (y+destPoint.y) * this.width) * 4;
					
					switch(blendMode) {
						case BlendMode.ADD:
							data[destPos] = Math.min(data[destPos] + sourceData[sourcePos], 255);
							data[destPos+1] = Math.min(data[destPos+1] + sourceData[sourcePos+1], 255);
							data[destPos+2] = Math.min(data[destPos+2] + sourceData[sourcePos+2], 255);
						break;
						
						case BlendMode.SUBTRACT:
							data[destPos] = Math.max(sourceData[sourcePos] - data[destPos], 0);
							data[destPos+1] = Math.max(sourceData[sourcePos+1] - data[destPos+1], 0);
							data[destPos+2] = Math.max(sourceData[sourcePos+2] - data[destPos+2], 0);
						break;
						
						case BlendMode.INVERT:
							data[destPos] = 255 - sourceData[sourcePos];
							data[destPos+1] = 255 - sourceData[sourcePos+1];
							data[destPos+2] = 255 - sourceData[sourcePos+1];
						break;
						
						case BlendMode.MULTIPLY:
							data[destPos] = Math.floor(sourceData[sourcePos] * data[destPos] / 255);
							data[destPos+1] = Math.floor(sourceData[sourcePos+1] * data[destPos+1] / 255);
							data[destPos+2] = Math.floor(sourceData[sourcePos+2] * data[destPos+2] / 255);
						break;
						
						case BlendMode.LIGHTEN:
							if(sourceData[sourcePos] > data[destPos]) data[destPos] = sourceData[sourcePos];
							if(sourceData[sourcePos+1] > data[destPos+1]) data[destPos+1] = sourceData[sourcePos+1];
							if(sourceData[sourcePos+2] > data[destPos+2]) data[destPos+2] = sourceData[sourcePos+2];
						break;
						
						case BlendMode.DARKEN:
							if(sourceData[sourcePos] < data[destPos]) data[destPos] = sourceData[sourcePos];
							if(sourceData[sourcePos+1] < data[destPos+1]) data[destPos+1] = sourceData[sourcePos+1];
							if(sourceData[sourcePos+2] < data[destPos+2]) data[destPos+2] = sourceData[sourcePos+2];
						break;

						case BlendMode.DIFFERENCE:
							data[destPos] = Math.abs(sourceData[sourcePos] - data[destPos]);
							data[destPos+1] = Math.abs(sourceData[sourcePos+1] - data[destPos+1]);
							data[destPos+2] = Math.abs(sourceData[sourcePos+2] - data[destPos+2]);
						break;
						
						case BlendMode.SCREEN:
							data[destPos] = (255 - ( ((255-data[destPos])*(255-sourceData[sourcePos])) >> 8));
							data[destPos+1] = (255 - ( ((255-data[destPos+1])*(255-sourceData[sourcePos+1])) >> 8));
							data[destPos+2] = (255 - ( ((255-data[destPos+2])*(255-sourceData[sourcePos+2])) >> 8));
						break;

						case BlendMode.OVERLAY:
							if(sourceData[sourcePos] < 128) data[destPos] = data[destPos] * sourceData[sourcePos] * halfColorMax;
							else data[destPos] = 255 - (255-data[destPos])*(255-sourceData[sourcePos])*halfColorMax;
							
							if(sourceData[sourcePos+1] < 128) data[destPos+1] = data[destPos+1] * sourceData[sourcePos+1] * halfColorMax;
							else data[destPos+1] = 255 - (255-data[destPos+1])*(255-sourceData[sourcePos+1])*halfColorMax;
							
							if(sourceData[sourcePos+2] < 128) data[destPos+2] = data[destPos+2] * sourceData[sourcePos+2] * halfColorMax;
							else data[destPos+2] = 255 - (255-data[destPos+2])*(255-sourceData[sourcePos+2])*halfColorMax;
						break;
						
						case BlendMode.HARDLIGHT:
							if(data[destPos] < 128) data[destPos] = data[destPos] * sourceData[sourcePos] * halfColorMax;
							else data[destPos] = 255 - (255-data[destPos])*(255-sourceData[sourcePos])*halfColorMax;
							
							if(data[destPos+1] < 128) data[destPos+1] = data[destPos+1] * sourceData[sourcePos+1] * halfColorMax;
							else data[destPos+1] = 255 - (255-data[destPos+1])*(255-sourceData[sourcePos+1])*halfColorMax;
							
							if(data[destPos+2] < 128) data[destPos+2] = data[destPos+2] * sourceData[sourcePos+2] * halfColorMax;
							else data[destPos+2] = 255 - (255-data[destPos+2])*(255-sourceData[sourcePos+2])*halfColorMax;
						break;	
						
					}
				}
			}
			
		} else {
			this.context.drawImage(sourceCanvas, 
				sourceRect.x, sourceRect.y, dw, dh, 
				destPoint.x, destPoint.y, dw, dh);
			
			this.imagedata = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	this.copyChannel = function(sourceBitmapData, sourceRect, destPoint, sourceChannel, destChannel) {
		var sourceColor, sourceRGB, rgb;
		var redChannel = BitmapDataChannel.RED;
		var greenChannel = BitmapDataChannel.GREEN;
		var blueChannel = BitmapDataChannel.BLUE;
		
		for (var y=0; y<sourceRect.height; y++) {
			for (var x=0; x<sourceRect.width; x++) {
				sourceColor = sourceBitmapData.getPixel(sourceRect.x+x, sourceRect.y+y);
				sourceRGB = hexToRGB(sourceColor);
				switch(sourceChannel) {
					case redChannel: channelValue = sourceRGB.r; break;
					case greenChannel: channelValue = sourceRGB.g; break;
					case blueChannel: channelValue = sourceRGB.b; break;
				}
				
				rgb = hexToRGB( this.getPixel(destPoint.x+x, destPoint.y+y) ); // redundancy
				switch(destChannel){
					case redChannel: rgb.r = channelValue; break;
					case greenChannel: rgb.g = channelValue; break;
					case blueChannel: rgb.b = channelValue; break;
				}
				
				this.setPixel(destPoint.x+x, destPoint.y+y, RGBToHex(rgb));
			}
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	this.copyPixels = function(sourceBitmapData, sourceRect, destPoint, alphaBitmapData, alphaPoint, mergeAlpha) {
		this.copyCanvas(sourceBitmapData.canvas, sourceRect, destPoint);
	};
	
	this.draw = function(source, matrix, colorTransform, blendMode, clipRect, smoothing) {

		/*
		 * currently only supports Image object
		 * TODO: implement instanceof switches
		 */
		
		sourceMatrix = matrix || new Matrix();
		sourceRect = clipRect || new Rectangle(0, 0, source.width, source.height);
		
		if(blendMode && this.gpuEnabled) {
			// TO DO
		}
		
		this.drawingCanvas.setAttribute('width', source.width);
		this.drawingCanvas.setAttribute('height', source.height);
		
		this.drawingContext.transform(
			sourceMatrix.a,
			sourceMatrix.b,
			sourceMatrix.c,
			sourceMatrix.d,
			sourceMatrix.tx,
			sourceMatrix.ty);
			
		this.drawingContext.drawImage(source, 
			0, 0, source.width, source.height, 
			0, 0, source.width, source.height);
		
		this.copyCanvas(this.drawingCanvas, sourceRect, new Point(sourceRect.x, sourceRect.y), blendMode);
	}
	
	this.fillRect = function(rect, color) {
		this.context.putImageData(this.imagedata, 0, 0);
		var rgb = hexToRGB(color);

		this.context.fillStyle = "rgb("+rgb.r+","+rgb.g+","+rgb.b+")";  
		this.context.fillRect (rect.x, rect.y, rect.width, rect.height);
		this.imagedata = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
	};
	
	this.floodFill = function(x, y, color) {
		var queue = new Array();
		queue.push(new Point(x, y));

		var old = this.getPixel(x, y);
		var iterations = 0;

		var searchBmp = new BitmapData(this.width, this.height, true, 0xffffff);
		var currPoint, newPoint;
	
		while (queue.length > 0) {
			currPoint = queue.shift();
			++iterations;

			if (currPoint.x < 0 || currPoint.x >= this.width) continue;
			if (currPoint.y < 0 || currPoint.y >= this.height) continue;

			searchBmp.setPixel(currPoint.x, currPoint.y, 0x00);

			if (this.getPixel(currPoint.x, currPoint.y) == old) {
				this.setPixel(currPoint.x, currPoint.y, color);

				if (searchBmp.getPixel(currPoint.x + 1, currPoint.y) == 0xffffff) {
					queue.push(new Point(currPoint.x + 1, currPoint.y));
				} 
				if (searchBmp.getPixel(currPoint.x, currPoint.y + 1) == 0xffffff) {
					queue.push(new Point(currPoint.x, currPoint.y + 1));
				} 
				if (searchBmp.getPixel(currPoint.x - 1, currPoint.y) == 0xffffff) {
					queue.push(new Point(currPoint.x - 1, currPoint.y));
				} 
				if (searchBmp.getPixel(currPoint.x, currPoint.y - 1) == 0xffffff) {
					queue.push(new Point(currPoint.x, currPoint.y - 1));
				}
			}
		}       

	};
	
	this.histogram = function(hRect) {
		hRect = hRect || this.rect;
		
		var rgb = { r: [], g: [], b: [] };
		var rv = histogramVector(0);
		var gv = histogramVector(0);
		var bv = histogramVector(0);
		
		var p = hRect.width*hRect.height;
		var itr = -1;
		var pos;
		var color = [];
		
		var bw = this.canvas.width - hRect.width - hRect.x;
		var bh = this.canvas.height - hRect.height - hRect.y
		var dw = (bw < 0) ? hRect.width + (this.canvas.width - hRect.width - hRect.x) : hRect.width;
		var dh = (bh < 0) ? hRect.height + (this.canvas.height - hRect.height - hRect.y) : hRect.height;
		
		var data = this.imagedata.data;
		
		for(var y=hRect.y; y<dh; y++) {
			for(var x=hRect.x; x<dw; x++) {
				pos = (x + y * this.width) * 4;
				color[itr++] = data[pos+0];
				color[itr++] = data[pos+1];
				color[itr++] = data[pos+2];
			}
		}
		
		itr = 0;
		for(var i=0; i<p; i+=Math.floor(p/256)) {
			px = itr*3;
			rv[itr] = color[px+0];
			gv[itr] = color[px+1];
			bv[itr] = color[px+2];
			itr++;
		}
		
		rgb.r = rv;
		rgb.g = gv;
		rgb.b = bv;

		return rgb;
	};
				
	this.noise = function(randomSeed, low, high, channelOptions, grayScale) {
		this.rand = this.rand || new PRNG();
		this.rand.seed = randomSeed;
		
		var redChannel = BitmapDataChannel.RED;
		var greenChannel = BitmapDataChannel.GREEN;
		var blueChannel = BitmapDataChannel.BLUE;
		
		var data = this.imagedata.data;
		
		low = low || 0;
		high = high || 255;
		channelOptions = channelOptions || 7;
		grayScale = grayScale || false;
		
		var pos, cr, cg, cb, gray;
		for (var y=0; y<this.height; y++) {
			for (var x=0; x<this.width; x++) {
				pos = (x + y * this.width) * 4;

				cr = this.rand.nextRange(low, high);
				cg = this.rand.nextRange(low, high);
				cb = this.rand.nextRange(low, high);
				
				if(grayScale) {
					gray = (cr + cg + cb) / 3;
					cr = cg = cb = gray;
				}
				
				data[pos+0] = (channelOptions & redChannel) ? (1 * cr) : 0x00;
				data[pos+1] = (channelOptions & greenChannel) ? (1 * cg) : 0x00;
				data[pos+2] = (channelOptions & blueChannel) ? (1 * cb) : 0x00;
			}
		}	
	};
	
	this.paletteMap = function(sourceBitmapData, sourceRect, destPoint, redArray, greenArray, blueArray, alphaArray) {
		var bw = this.canvas.width - sourceRect.width - destPoint.x;
		var bh = this.canvas.height - sourceRect.height - destPoint.y

		var dw = (bw < 0) ? sourceRect.width + (this.canvas.width - sourceRect.width - destPoint.x) : sourceRect.width;
		var dh = (bh < 0) ? sourceRect.height + (this.canvas.height - sourceRect.height - destPoint.y) : sourceRect.height;
		
		var sourceData = sourceBitmapData.imagedata.data;
		var sourcePos, destPos, sourceHex;
		var r, g, b, pos;
		
		var sx = sourceRect.x;
		var sy = sourceRect.y;
		var sw = sourceBitmapData.width;
		var dx = destPoint.x;
		var dy = destPoint.y;
		
		var data = this.imagedata.data;
		var w = this.width;
		
		for (var y=0; y<dh; y++) {
			for (var x=0; x<dw; x++) {
				sourcePos = ((x+sx) + (y+sy) * sw) * 4;
				
				r = sourceData[sourcePos+0];
				g = sourceData[sourcePos+1];
				b = sourceData[sourcePos+2];

				pos = ((x+dx) + (y+dy) * w) * 4;

				data[pos+0] = redArray[r];
				data[pos+1] = greenArray[g];
				data[pos+2] = blueArray[b];
			}
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	this.perlinNoise = function(baseX, baseY, randomSeed, channelOptions, grayScale) {
		this.rand = this.rand || new PRNG();
		this.rand.seed = randomSeed;
		
		var redChannel = BitmapDataChannel.RED;
		var greenChannel = BitmapDataChannel.GREEN;
		var blueChannel = BitmapDataChannel.BLUE;
		
		channelOptions = channelOptions || 7;
		grayScale = grayScale || false;
		
		var data = this.imagedata.data;
		
		var numChannels = 0;
		if(channelOptions & redChannel){
			this.simplexR = this.simplexR || new SimplexNoise(this.rand);
			this.simplexR.setSeed(randomSeed);
			numChannels++;
		} 
		if(channelOptions & greenChannel) {
			this.simplexG = this.simplexG || new SimplexNoise(this.rand);
			this.simplexG.setSeed(randomSeed+1);
			numChannels++;
		}
		if(channelOptions & blueChannel) {
			this.simplexB = this.simplexB || new SimplexNoise(this.rand);
			this.simplexB.setSeed(randomSeed+2);
			numChannels++;
		}
		
		var pos, cr, cg, cb;
		for(var y=0; y<this.height; y++) {
			for(var x=0; x<this.width; x++) {
				pos = (x + y * this.width) * 4;
				
				cr = (channelOptions & redChannel) ? Math.floor(((this.simplexR.noise(x/baseX, y/baseY)+1)*0.5)*255) : 0x00;
				cg = (channelOptions & greenChannel) ? Math.floor(((this.simplexG.noise(x/baseX, y/baseY)+1)*0.5)*255) : 0x00;
				cb = (channelOptions & blueChannel) ? Math.floor(((this.simplexB.noise(x/baseX, y/baseY)+1)*0.5)*255) : 0x00;

				if(grayScale) {
					gray = (cr + cg + cb) / numChannels;
					cr = cg = cb = gray;
				}
				
				data[pos+0] = cr;
				data[pos+1] = cg;
				data[pos+2] = cb;
			}
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	this.threshold = function(sourceBitmapData, sourceRect, destPoint, operation, threshold, color, mask, copySource) {
		color = color || 0;
		mask = mask || 0xffffff;
		copySource = copySource || false;
		
		var bw = this.canvas.width - sourceRect.width - destPoint.x;
		var bh = this.canvas.height - sourceRect.height - destPoint.y

		var dw = (bw < 0) ? sourceRect.width + (this.canvas.width - sourceRect.width - destPoint.x) : sourceRect.width;
		var dh = (bh < 0) ? sourceRect.height + (this.canvas.height - sourceRect.height - destPoint.y) : sourceRect.height;
		
		var sourceData = sourceBitmapData.imagedata.data;
		var sourcePos, destPos, sourceHex;
		
		var sx = sourceRect.x;
		var sy = sourceRect.y;
		var sw = sourceBitmapData.width;
		
		for (var y=0; y<dh; y++) {
			for (var x=0; x<dw; x++) {
				sourcePos = ((x+sx) + (y+sy) * sw) * 4;
				sourceHex = RGBToHex({r:sourceData[sourcePos], g:sourceData[sourcePos+1], b:sourceData[sourcePos+2]});
				
				switch(operation) {
					case "<": 
						if((sourceHex & mask) < (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color); 
						}
					break;
					
					case "<=": 
						if((sourceHex & mask) <= (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color); 
						}
					break;
					
					case ">": 
						if((sourceHex & mask) > (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color); 
						}
					break;
					
					case ">=": 
						if((sourceHex & mask) <= (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color); 
						}
					break;
					
					case "==": 
						if((sourceHex & mask) == (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color); 
						}
					break;
					
					case "!=": 
						if((sourceHex & mask) != (threshold & mask)) {
							if(copySource) this.setPixel(x+destPoint.x, y+destPoint.y, sourceHex); else this.setPixel(x+destPoint.x, y+destPoint.y, color);  
						}
					break;
				}
		
			}
		}
		
		this.context.putImageData(this.imagedata, 0, 0);
	};
	
	if(fillColor) this.fillRect(this.rect, fillColor);
	else this.fillRect(this.rect, 0);
	return this;
};

HTMLCanvasElement.prototype._bitmapData = null;
HTMLCanvasElement.prototype.__defineGetter__("bitmapData", function() { 
	if(!this._bitmapData) {
		this._bitmapData = new BitmapData(this.width, this.height, false, 0, this);
	}
	return this._bitmapData;
});  	