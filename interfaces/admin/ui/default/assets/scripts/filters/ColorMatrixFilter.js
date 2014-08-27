function ColorMatrixFilter(matrix)
{
	this.matrix=matrix || [
							1, 0, 0, 0, 0,
							0, 1, 0, 0, 0,
							0, 0, 1, 0, 0,
							0, 0, 0, 1, 0
						  ];
	
	this.run=function(sourceRect, image, copy)
	{
		var numPixel=image.length/4;
		var m=this.matrix;
		
		for(var i=0;i<numPixel;i++)
		{
			var r=i*4;
			var g=r+1;
			var b=r+2;
			var a=r+3;
			
			var oR=image[r];
			var oG=image[g];
			var oB=image[b];
			var oA=image[a];
			
			image[r] = (m[0]  * oR) + (m[1]  * oG) + (m[2]  * oB) + (m[3]  * oA) + m[4];
	 		image[g] = (m[5]  * oR) + (m[6]  * oG) + (m[7]  * oB) + (m[8]  * oA) + m[9];
	 		image[b] = (m[10] * oR) + (m[11] * oG) + (m[12] * oB) + (m[13] * oA) + m[14];
	 		image[a] = (m[15] * oR) + (m[16] * oG) + (m[17] * oB) + (m[18] * oA) + m[19];
		}
	}
	
	this.clone=function()
	{
		return new ColorMatrixFilter(this.matrix);
	}
}
