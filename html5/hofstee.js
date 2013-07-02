// JavaScript Document
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

//
//
// Hofstee plot
//
// @author Nikodem Miranowicz
// @version 1.0
// @copyright Copyright (c) 2013 The University of Nottingham
// @package
//

function hofstee_plot(canvas_id,result_type) {
	var scale_x = 3;
	var scale_y = 5;
	var graph_w = scale_x*100;
	var graph_h = scale_y*100;
	var graph_x = 70;
	var graph_y = 540-graph_h;
	var dragging = false;
	var redraw = true;
	var boundaries = [];
	var temp_boundaries = [];
	var active_line = 0;
	var a1,a2,b1,b2;
	var tx1,tx2,ty1,ty2,xs,ys;
	var d_x = 40;
	var d_y = 200;
	var x,y,i,j,delta;

	//recalculating data
	var graph_data = [];
	var data,last_data = 0;
	var canvas,context;
	j=-1;
	for (i=0;i<marks.length;i++) {
		data = 1*marks[i];
		if (data!=last_data) j++;
		graph_data[j] = [data,(i+1)*100/marks.length,i];
		last_data =  data;
		}	

	temp_boundaries = [marks[0],marks[marks.length-1],0,100];
	boundaries = [marks[0],marks[marks.length-1],0,100];

	if (document.getElementById('x1_'+result_type).value!='') boundaries[0] = Number(document.getElementById('x1_'+result_type).value.replace('%',''));
	if (document.getElementById('x2_'+result_type).value!='') boundaries[1] = Number(document.getElementById('x2_'+result_type).value.replace('%',''));
	if (document.getElementById('y1_'+result_type).value!='') boundaries[2] = Number(document.getElementById('y1_'+result_type).value.replace('%',''));
	if (document.getElementById('y2_'+result_type).value!='') boundaries[3] = Number(document.getElementById('y2_'+result_type).value.replace('%',''));

	canvas = document.getElementById(canvas_id);

	if (canvas && canvas.getContext){
		canvas.onmouseup   = g_mouseDragUp;
		canvas.onmousedown = g_mouseDragDown;
		canvas.onmousemove = g_mouseDragMove;
		var intervalID = window.setInterval(g_redraw_canvas, 10);
	}

	if (canvas && !canvas.getContext){
		alert ('canvas not supported');
	}

	if (canvas && canvas.getContext){
		context = canvas.getContext('2d');
	}

	function drawLine(cc,xx,yy,ww,hh) {
		context.strokeStyle = cc;
		context.beginPath();
		context.moveTo(xx-0.5,yy-0.5);
		context.lineTo(xx+ww-0.5,yy+hh-0.5);
		context.stroke();
	}
		
	function g_redraw_canvas() {
		function act(line_nr) {
			if (line_nr == active_line) {
				if (line_nr == 1 || line_nr == 2) {
					context.shadowColor = '#D7E3BC';
				} else {
					context.shadowColor = '#F88';
				}
				context.shadowBlur = 5;
			} else {
				context.shadowColor = 'white';
				context.shadowBlur = 0;
			}
		}
		
		if (dragging || redraw) {
			context.clearRect(0,0,canvas.width,canvas.height);
			context.shadowColor = 'white';
			context.shadowBlur = 0;
			 
			//drawing a graph  
			context.lineWidth = 1;
			drawLine('#000',graph_x,canvas.height-graph_y,0,-graph_h);
			drawLine('#000',graph_x,canvas.height-graph_y,graph_w,0);
			for (i=1;i<=10;i++) {
				drawLine('#DDD',graph_x,canvas.height-graph_y-i*scale_y*10,graph_w,0);
			}
			var ty = canvas.height-graph_y;
			var ty_old = ty;
			context.beginPath();
			context.strokeStyle = '#000';
			context.moveTo(graph_x,ty-0.5);
			for (i=0;i<graph_data.length;i++) {
				ty=canvas.height-graph_y-scale_y*graph_data[i][1];
				if (ty != ty_old) context.lineTo(graph_x+graph_data[i][0]*scale_x,ty-0.5);
				ty_old = ty;
			}
			context.stroke();
			delta=0.1; if (document.getElementById('checkbox').checked) delta = 1;
			if (delta==1) {
				for (i=0;i<4;i++) {
					boundaries[i] = Math.round(boundaries[i]);
				}
			}
			//position of boudary lines
			var x1 = graph_x+graph_w/100*boundaries[0];
			var x2 = graph_x+graph_w/100*boundaries[1];
			var y3 = canvas.height-graph_y-graph_h/100*boundaries[2];
			var y4 = canvas.height-graph_y-graph_h/100*boundaries[3];
											 
			//standing labels
			context.font="bold 13px Arial";
			context.textAlign="center";
			context.fillText(lang_correct,graph_x+graph_w/2,canvas.height-graph_y+35);
			context.save();
			context.rotate(-Math.PI/2);
			context.fillText(lang_cohort,-graph_h/2-graph_y,graph_x-45);
			context.restore();
			
			//graph ticks and labels
			context.font="11px Arial";
			context.textAlign="right";
			for (i=0;i<=10;i++) {
				drawLine('#000',graph_x,canvas.height-graph_y-scale_y*10*i,-5,0);
				context.fillText(i*10+'%',graph_x-10,canvas.height-graph_y-scale_y*10*i+3);
			}
			context.textAlign="center";
			for (i=0;i<=10;i++) {
				drawLine('#000',graph_x+scale_x*10*i,canvas.height-graph_y,0,5);
				context.fillText(i*10+'%',graph_x+scale_x*10*i+5,canvas.height-graph_y+15);
			}
			
			//moving labels
			context.font="13px Arial";
			context.textAlign="center";
			context.fillStyle = '#76923C';
			var divert = 0;
			if (Math.abs(x1-x2)<50) divert = (50-Math.abs(x1-x2))/2;
			if (divert > 15) divert = 15;
			context.fillText(Math.round(boundaries[0]*10)/10+'%',x1,canvas.height-graph_y-graph_h-5);
			context.fillText(Math.round(boundaries[1]*10)/10+'%',x2,canvas.height-graph_y-graph_h-5-divert);
			context.textAlign="right";
			context.fillStyle = '#C00000';
			divert = 0;
			if (Math.abs(y3-y4)<25) divert = -4*(25-Math.abs(y3-y4));
			if (divert < -40) divert = -40;
			context.fillText(Math.round(boundaries[2]*10)/10+'%',graph_x+graph_w+40,y3+5);
			context.fillText(Math.round(boundaries[3]*10)/10+'%',graph_x+graph_w+40-divert,y4+5);      
			
			context.fillStyle = '#000';
			//drawing cyan line
			drawLine('#00C0C0',x1,y4,x2-x1,y3-y4);
			
			//boxplot
			//stats: max_mark, max_percent, min_mark, min_percent, q1, q2, q3, mean_mark, mean_percent, stdev_mark, stdev_percent
			var box1=5,box2=20,box3=box1+box2/2;
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[3]),box1,0,box2); 
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[1]),box1,0,box2);
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[3]),box3,Math.round(scale_x*(stats[4]-stats[3])),0);
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[6]),box3,Math.round(scale_x*(stats[1]-stats[6])),0);
			
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[4]),box1,0,box2);
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[8]),box1,0,box2);
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[6]),box1,0,box2);		
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[4]),box1,Math.round(scale_x*(stats[6]-stats[4])),0);
			drawLine('#00C0C0',Math.round(graph_x+scale_x*stats[4]),box1+box2,Math.round(scale_x*(stats[6]-stats[4])),0);
			
			/*
			labels for boxplot
			context.font="11px Arial";
			context.textAlign="left";
			context.fillStyle = '#C00000';
			context.strokeStyle = '#C00000';
			context.save();
			context.rotate(-Math.PI/2);
			context.fillText('100',-90,100);
			context.fillText(Math.round(stats[3]*10)/10+'%',-box1+5,Math.round(graph_x+5*stats[3])+2);
			context.fillText(Math.round(stats[1]*10)/10+'%',-box1+5,Math.round(graph_x+5*stats[1])+2);
			context.fillText(Math.round(stats[8]*100)/100+'%',-box1+5,Math.round(graph_x+5*stats[8])+2);
			context.fillText(Math.round(stats[4]*100)/100+'%',-box1+5,Math.round(graph_x+5*stats[4])+2);
			context.fillText(Math.round(stats[6]*100)/100+'%',-box1+5,Math.round(graph_x+5*stats[6])+2);
			context.restore();
			*/
			
			//legend
			context.font="11px Arial";
			context.textAlign="left";
			var leg1=graph_w+130,leg2=10,leg3=10,leg4=15;
			
			context.fillText(lang_maximumscore,leg1+leg3+0.5,leg2+1*leg4+0.5);
			context.fillText(lang_topquartile,leg1+leg3+0.5,leg2+2*leg4+0.5);
			context.fillText(lang_median,leg1+leg3+0.5,leg2+3*leg4+0.5);
			context.fillText(lang_lowerquartile,leg1+leg3+0.5,leg2+4*leg4+0.5);
			context.fillText(lang_minimumscore,leg1+leg3+0.5,leg2+5*leg4+0.5);
			
			function max_text_len(max,text){
				var metrics = context.measureText(text);
				var textWidth = metrics.width;
				if (max<textWidth) max=textWidth;
				return max;
			}
			
			var max_len=0;
			max_len = max_text_len(max_len,lang_maximumscore);
			max_len = max_text_len(max_len,lang_topquartile);
			max_len = max_text_len(max_len,lang_median);
			max_len = max_text_len(max_len,lang_lowerquartile);
			max_len = max_text_len(max_len,lang_minimumscore);
			
			context.fillText('- '+Math.round(stats[1]*10)/10+'%',leg1+leg3+max_len+10.5,leg2+1*leg4+0.5);		
			context.fillText('- '+Math.round(stats[6]*10)/10+'%',leg1+leg3+max_len+10.5,leg2+2*leg4+0.5);		
			context.fillText('- '+Math.round(stats[8]*10)/10+'%',leg1+leg3+max_len+10.5,leg2+3*leg4+0.5);		
			context.fillText('- '+Math.round(stats[4]*10)/10+'%',leg1+leg3+max_len+10.5,leg2+4*leg4+0.5);		
			context.fillText('- '+Math.round(stats[3]*10)/10+'%',leg1+leg3+max_len+10.5,leg2+5*leg4+0.5);		
			
			context.strokeRect(leg1+0.5,leg2+0.5,max_len+leg3+60,85);

			//searching for intersection
			//a and b for the first line
			a1=0;b1=x2;
			if (x2!=x1) {
				a1 = (y3-y4)/(x2-x1);
				b1 = y4 - a1 * x1;
			}
			tx2 = graph_x;
			ty2 = canvas.height-graph_y;
			xs = ys = '';
			for (i=0;i<graph_data.length;i++) {
				tx1 = graph_x+graph_data[i][0]*scale_x;
				ty1 = canvas.height-graph_y-scale_y*graph_data[i][1];
				
				//a and b for the second line
				a2=0;b2=tx1;
				if (tx2!=tx1) {
					a2 = (ty2-ty1)/(tx2-tx1);
					b2 = ty1 - a2*tx1;
				}
						
				if (a1 != a2) {
					cx = (b2 - b1)/(a1 - a2);
					if (tx2==tx1) cx=tx1;
					cy = a1*cx + b1;
				}	
				
				if ((cx>=tx2) && (cx<=tx1) && ((cy>=ty2 && cy<=ty1) || (cy>=ty1 && cy<=ty2))) {
					if (cx>=x1 && cx<=x2 && cy>=y4 && cy<=y3) {
						xs = Math.round((cx-graph_x)/scale_x*10)/10;
						ys = Math.round(-(cy-canvas.height+graph_y)/scale_y*10)/10;
						var dcx = Math.round(cx);
						var dcy = Math.round(cy);
						for (j=1;j<(ys*scale_y/5);j++) drawLine('#00C0C0',dcx,dcy+5*j,0,-3);
						for (j=1;j<(xs*scale_x/5);j++) drawLine('#00C0C0',dcx-5*j,dcy,3,0);
						xs+='%';
						ys+='%';
						if (xs == undefined) xs = '';
						if (ys == undefined) ys = '';
					}
				}
				tx2 = tx1;
				ty2 = ty1;
			}
			
			//textboxes outside canvas
			document.getElementById('x1_'+result_type).value = Math.round(boundaries[0]*10)/10+'%';
			document.getElementById('x2_'+result_type).value = Math.round(boundaries[1]*10)/10+'%';
			document.getElementById('y1_'+result_type).value = Math.round(boundaries[2]*10)/10+'%';
			document.getElementById('y2_'+result_type).value = Math.round(boundaries[3]*10)/10+'%';
			document.getElementById('xs_'+result_type).value = xs;
			document.getElementById('ys_'+result_type).value = ys;

			//drawing boundaries
			var gap = 0;
			act(1);
			drawLine('#9BBB59',Math.round(x1),canvas.height-graph_y-gap,0,-graph_h+2*gap);      
			act(2);
			drawLine('#9BBB59',Math.round(x2),canvas.height-graph_y-gap,0,-graph_h+2*gap);      
			act(3);
			drawLine('#C00000',graph_x+gap,Math.round(y3),graph_w-2*gap,0);
			act(4);
			drawLine('#C00000',graph_x+gap,Math.round(y4),graph_w-2*gap,0);

		}
		redraw = false;
	}

	function testWithin(ax,ay,bx,by,cx,cy) {
		var testres = false;
		if ((ax > bx) && (ax < (bx + cx)) && (ay > by) && (ay < (by + cy))) testres = true;
		return testres;
	}

	function g_mouseDragUp(e) {
		dragging = false;
		if (testWithin(x,y,d_x-5,d_y-15+00,20,20)) {boundaries[0] = temp_boundaries[0];redraw = true;g_redraw_canvas;}
		if (testWithin(x,y,d_x-5,d_y-15+20,20,20)) {boundaries[1] = temp_boundaries[1];redraw = true;g_redraw_canvas;}
		if (testWithin(x,y,d_x-5,d_y-15+40,20,20)) {boundaries[2] = temp_boundaries[2];redraw = true;g_redraw_canvas;}
		if (testWithin(x,y,d_x-5,d_y-15+60,20,20)) {boundaries[3] = temp_boundaries[3];redraw = true;g_redraw_canvas;}
	}

	function g_mouseDragDown(e) {
		dragging = true;
	}

	function g_mouseDragMove(e) {
		rect = canvas.getBoundingClientRect();
		loc_lft = rect.left;
		loc_top = rect.top;
		var xm = e.clientX;
		var ym = e.clientY;
		x = xm - loc_lft;
		y = ym - loc_top; 

		if (dragging) {
			if (active_line==1 || active_line==2) boundaries[active_line-1] = (x-graph_x)/graph_w*100;
			if (active_line==3 || active_line==4) boundaries[active_line-1] = (canvas.height-y-graph_y)/graph_h*100;
			if (boundaries[active_line-1]>100) boundaries[active_line-1] = 100;
			if (boundaries[active_line-1]<0) boundaries[active_line-1] = 0;
			
			//swap active lines if dragging over
			if (boundaries[1]<boundaries[0]) {
				boundaries[1] = [boundaries[0], boundaries[0] = boundaries[1]][0];
				active_line = 3 - active_line;
			} 
			if (boundaries[3]<boundaries[2]) {
				boundaries[3] = [boundaries[2], boundaries[2] = boundaries[3]][0];
				active_line = 7 - active_line;
			}       
			if (active_line>-1) g_redraw_canvas;
		} else {
			var old_active_line = active_line;
			active_line = 0;
			var treshold = 3
			if (Math.abs(graph_x+graph_w/100*boundaries[0]-x)<treshold) active_line = 1;
			if (Math.abs(graph_x+graph_w/100*boundaries[1]-x)<treshold) active_line = 2;
			if (Math.abs(canvas.height-graph_y-graph_h/100*boundaries[2]-y)<treshold) active_line = 3;
			if (Math.abs(canvas.height-graph_y-graph_h/100*boundaries[3]-y)<treshold) active_line = 4;
			
			var cur = 'default';
			if (active_line>0) cur = 'col-resize';
			if (active_line>2) cur = 'row-resize';
			e.target.style.cursor = cur;
			
			if (active_line!=old_active_line) {
				redraw = true;    
				g_redraw_canvas;
			}
		}
	}

	function tfchange(event,keys) {
		if (result_type == event.target.name.substr(3)) {
			target = ((event.target.name[0]=='x')?0:2)+1*event.target.name[1]-1;
			var ev0 = boundaries[target];
			var ev = Number(event.target.value.replace('%',''));
			if (isNaN(ev)) ev = ev0;
			
			if (keys) {
				if (event.keyCode==37 || event.keyCode==40) ev-=delta;
				if (event.keyCode==38 || event.keyCode==39) ev+=delta;
			}
			
			if (ev<0) ev = 0;
			if (ev>100) ev = 100;
			boundaries[target] = ev;
			if (boundaries[1]<boundaries[0]) boundaries[1] = [boundaries[0], boundaries[0] = boundaries[1]][0];
			if (boundaries[3]<boundaries[2]) boundaries[3] = [boundaries[2], boundaries[2] = boundaries[3]][0];
			
			redraw = true;
			g_redraw_canvas;
		}
	}

	$("#checkbox").change(function(event) {
		redraw = true;
		g_redraw_canvas;	
	});
		
	$(".tf").keypress(function(event) {
		if (event.keyCode<=40 && event.keyCode>=37) tfchange(event,true);
	});

	$(".tf").blur(function(event) {
		tfchange(event,false);
	});
}