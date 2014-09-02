var Difference = new function () {

	this.vsSrc = [
		"attribute vec2 pos;",
		"varying vec2 p;",	

		"void main(void) {",		
		"	p=pos;",
		"	gl_Position = vec4(pos.x, pos.y, 0.0, 1.0);",
		"}"
	].join("\n");
	
	this.fsSrc = [
		"#ifdef GL_ES",
		"precision highp float;",
		"#endif",

		"uniform vec2 r;",
		"uniform vec2 t;",
		"uniform sampler2D tex0;",
		"uniform sampler2D tex1;",
		"uniform mat3 rMat;",

		"void main(void) {",
		"	vec3 t2 = rMat*vec3(gl_FragCoord.x-t.x, gl_FragCoord.y-t.y, 0.0);",
		"	vec2 p1 = 1.0 - 2.0 * gl_FragCoord.xy / r.xy;",
		"	vec2 p2 = 1.0 - 2.0 * t2.xy / r.xy;",
		"	vec3 col = abs(texture2D(tex0,p1).xyz - texture2D(tex1,p2).xyz);",
		"	gl_FragColor = vec4(col, 1.0);",
		"}"
	].join("\n");
	
};