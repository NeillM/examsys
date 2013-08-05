function setUpArea(num, flashId, lang, image, config, answer, extra, colour, mode) {
	this.canvas = document.getElementById('canvas'+num);
  this.draw_limit = new Array(0,27,this.canvas.width-2,this.canvas.height-2);
	if (this.canvas && this.canvas.getContext){
		this.canvas.onmouseup   = this.qa_mouseDragUp.bind(this);
		this.canvas.onmousedown = this.qa_mouseDragDown.bind(this);
		this.canvas.onmousemove = this.qa_mouseDragMove.bind(this);
		this.canvas.tabIndex 		= 1000; //force keyboard events
		this.canvas.onkeydown   = this.qa_mouseDragMove.bind(this);
		this.canvas.onkeyup     = this.qa_mouseDragMove.bind(this);
		var intervalID = window.setInterval(this.qa_redraw_canvas.bind(this), 10);
	}
	if (this.canvas && !this.canvas.getContext){
		alert ('Canvas not supported');
	}
  
	if (this.canvas && this.canvas.getContext){
		this.context = this.canvas.getContext('2d');
		this.context.lineWidth = 1;

		//---------- num, 
 		if (this.nikotest==1) console.log('num:\n'+num);
    this.q_Num = num;
		//---------- flashId, 
 		if (this.nikotest==1) console.log('flashId:\n'+flashId);
		//---------- lang, 
    //	var langfilename = "../../lang/" + String_from_JSL + "/question/edit/area.txt";

 		if (this.nikotest==1) console.log('lang:\n'+lang);
		//---------- image,
 		if (this.nikotest==1) console.log('image:\n'+image);
		
		//gen_img
		this.gen_img = new Image();  
		function gen_img_onload() {
			this.gen_img_loaded = true;
			this.redraw_once = true;
			this.qa_redraw_canvas;
		}  
		this.gen_img.onload = gen_img_onload.bind(this);
		this.gen_img.src = ''+image; 

    //console.log(this.gen_img.src);
       
		//---------- mode 
		if (this.nikotest==1) console.log('mode:\n'+mode);
		this.yOffset = 2;
    this.qmode = mode;
		if (mode=='1') this.qmode = 'answer';
		if (mode=='2') this.qmode = 'edit';

		
		//---------- config, 
		if (this.nikotest==1) console.log('config:\n'+config); 
    this.qconfig=this.yoffset_fix(config,this.yOffest_fix);
    //this.yOffest_fix
    if (config!='') this.global_delpoint_avail = true;

		//---------- answer, 
		if (this.nikotest==1) console.log('answer:\n'+answer);
    for (i=0; i<this.labelsBox.length; i++) this.answerBox[i] = new Array('','');
    if (answer != "" && answer != undefined && answer != "undefined" && answer != null && answer != "null" && answer != "u") {
      this.is_an_answer = true;
      var answer_l1 = answer.split("|");
      for (i=0; i<answer_l1.length; i++) {
        this.answerBox[i] = this.yoffset_fix(answer_l1[i],this.yOffest_fix).split(",");
      }    
    }      
    if (answer=='u') this.allUnaswered=true;
    //console.log('this.answerBox');
    this.qanswer=answer;
    
		//---------- colour 
		if (this.nikotest==1) console.log('colour:\n'+colour);
		this.currentColours[3] = colour;
    
    //menubar
 		this.menu_img = new Image();  
		function menu_img_onload(){
			this.menu_img_loaded = true;
      this.menu_ready++;
      this.redraw_once = true;
      this.qa_redraw_canvas;
		}
		this.menu_img.onload = menu_img_onload.bind(this);
		this.menu_img.src = '/html5/images/combined.png'; 
	}
	//this.redraw_once = true;
}

function yoffset_fix(data,fix) {
  var data_in = data.split(',');
  var data_out = '';
  for (var n=0;n<data_in.length/2;n++) {
    data_out += data_in[n*2]+',';
    data_out += (parseInt(data_in[n*2+1].trim(), 16)+fix).toString(16)+',';
  }
  data_out = data_out.substr(0,data_out.length-1);
  //console.log (data_out);
  return data_out;
}

function qa_menuBuild() {
  this.imgdata = menuImages['toolbar/vert_0.png'];
	this.context.drawImage(this.menu_img,this.imgdata.left+0.5,this.imgdata.top,this.imgdata.width-1,this.imgdata.height,0,0,this.canvas.width,this.imgdata.height);
  
  if (this.qmode=='this.test') {
    var spac = 3;
    var posx = 3;    
    var posy = 3;
    posx = this.menuBuild_icons('toolbar/ico_cross_off.png',posx,posy,0,'',lang_string['But_Delete_point'],lang_string['tt_Delete_point'])+spac;
    posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
    posx = this.menuBuild_icons('toolbar/ico_erase.png',posx,posy,0,'',lang_string['But_Clear_All'],lang_string['tt_Clear_All'])+spac;
    posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
    posx = this.menuBuild_icons('toolbar/ico_zoom.png',posx,posy,0,'',lang_string['Magnify'],lang_string['tt_Magnify'])+spac;
    posx = this.menuBuild_icons('toolbar/ico_help.png',this.canvas.width-23,posy,0,'','','')+spac;    
    
		area_buttons[0] = new Array('toolbar/ico_cross_on.png',lang_string['But_Delete_point']);
		area_buttons[5] = new Array('toolbar/ico_area.png',lang_string['But_your_answer']);
		area_buttons[6] = new Array('toolbar/ico_tick.png',lang_string['But_correct_answer']);
		area_buttons[7] = new Array('toolbar/ico_warn.png',lang_string['But_show_error']);
  }
  
  if (this.qmode=='edit' || this.qmode=='answer') {
    var spac = 3;
    var posx = 3;    
    var posy = 3;
    posx = this.menuBuild_icons('toolbar/ico_cross_off.png',posx,posy,0,'+',lang_string['But_Delete_point'],lang_string['tt_Delete_point'])+spac;
    posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
    posx = this.menuBuild_icons('toolbar/ico_erase.png',posx,posy,0,'',lang_string['But_Clear_All'],lang_string['tt_Clear_All'])+spac;
    posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
    posx = this.menuBuild_icons('toolbar/ico_zoom.png',posx,posy,0,'+',lang_string['Magnify'],lang_string['tt_Magnify'])+spac;
    posx = this.menuBuild_icons('toolbar/ico_help.png',this.canvas.width-23,posy,0,'-','','')+spac;    

    //zoom button pressed by default
    this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][6]=2;
    this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][5]=2;
  }
}

function qa_test() {
  //alert('this.x');
  this.do_the_test =false;
  this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
  this.context.globalAlpha = 0.5;
  var col = '#FF0000';
  //if (this.qmode=='answer' && 
  if (this.qanswer!='') this.polyDrawH(this.context,'',col,0,this.yOffset,this.qanswer.split(','),'t');     
  col = '#0000FF';  
  if (this.qconfig!='') this.polyDrawH(this.context,'',col,0,this.yOffset,this.qconfig.split(','),'t'); 
  this.context.globalAlpha = 1;

var timgd = this.context.getImageData(1,1,this.canvas.width-2,this.canvas.height-2);
var timgp = timgd.data;
var li1=li2=li3=0;
var trsh = 64; //84
for (j=0; j<timgp.length; j+=4) {
  //var col =this.hexifycolour(''+(timgp[j+0]*256+timgp[j+1])*256+timgp[j+2]*1);
  var col = '#'+timgp[j+0].toString(16)+timgp[j+1].toString(16)+timgp[j+2].toString(16);
  if (timgp[j+0]*1 > trsh && timgp[j+2]*1 > trsh && timgp[j+1]*1 ==0) li1++;
  if (timgp[j+0]*1 > trsh && timgp[j+2]*1 ==0   && timgp[j+1]*1 ==0) li2++;
  if (timgp[j+0]*1 == 0  && timgp[j+2]*1 > trsh && timgp[j+1]*1 ==0) li3++;
}
//console.log(li1+','+li2+','+li3);

var result_in = Math.round(li1/(li1+li3)*1000);
var result_out = Math.round(li2/(li1+li3)*1000);
var result_er = 1000 - result_in + result_out;
//console.log(result_er / 10+','+result_in / 10+','+result_out / 10+','+li1+','+li2+','+li3);

return li1+','+li2+','+li3;
//this.context.putImageData(timgd, 0,0);
}

function qa_redraw_canvas() {
  function qa_redraw_canvas_main(_self,tx,ty) {
		//console.log('xxx',_self);
    _self.context.globalAlpha = 1;
 		_self.context.fillStyle='#ffffff';
    _self.context.fillRect(-30,-30,_self.canvas.width+60,_self.canvas.height+60); 
    _self.context.drawImage(_self.gen_img,tx,ty);
    _self.context.globalAlpha = 0.75;
    _self.context.lineWidth = 3;
    var col = '#385D8A';    
    //if (_self.qanswer!='') _self.polyDrawH(_self.context,'',col,0,_self.yOffset,_self.qanswer.split(','),true);
    if (_self.qconfig!='') _self.polyDrawH(_self.context,col,'',tx-1,ty-28+_self.yOffset,_self.qconfig.split(','),'h'); 
    //draw temp polygon
    if (_self.qconfig=='' && _self.poly_temp!='') {
      var poly_temp_ext = _self.poly_temp;
      poly_temp_ext += Math.round(_self.x).toString(16)+','+Math.round(_self.y).toString(16);
      //console.log(poly_temp_ext);
      _self.polyDrawH(_self.context,col,'',tx-1,ty-28+_self.yOffset,poly_temp_ext.split(','),'d');
      }
    _self.context.globalAlpha = 1;
  }
  
	if (this.gen_img_loaded && this.menu_img_loaded && (this.dragging || this.redraw_once || this.mov_id!=-1 || this.mouse_moved || this.ShiftChange)) {
 		this.redraw_once = false;
    this.mouse_moved =false;
    this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
    qa_redraw_canvas_main(this,1,28-this.yOffset);
    //testing the answer
    if (this.do_the_test) {
      //this.test_result = this.qa_test();
      //console.log(this.test_result);
    }
    this.context.lineWidth = this.lineThickness;
		this.context.strokeStyle=this.currentColours[1];
    
    //buttons
    if (this.buttonBox.length==0) this.qa_menuBuild();
		//console.log(this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']]);
    //cross red or gray
    if (this.global_delpoint_avail) {
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][0] = 'toolbar/ico_cross_on.png';
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7] = '+';
    } else {
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][0] = 'toolbar/ico_cross_off.png';
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7] = '-';
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][5] = 0;
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6] = 0;
    }

    //clear all active?
    if (this.qconfig!='') {
      this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][7] = '';
    } else {
      this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][7] = '-';
    }

    this.menuRebuild(this.context);

    //frames
    this.context.strokeStyle='#7f9db9'; 
    this.context.strokeRect(0.5,0.5,this.canvas.width-1,25); 
    
    if (this.global_clearpnl) this.build_msgbox((this.canvas.width/2-130),(this.canvas.height/2-40),260,80,lang_string['popUp_msg'],lang_string['popUp_yes'],lang_string['popUp_no'],'');
   
    //tooltip
    if (this.buttonOver!=-1) this.tooltip_draw(this.context,this.buttonBox[this.buttonOver]);
  
    
    if (this.global_zoom  && !this.isShift && !this.global_clearpnl && this.y>26) {
      //mask
      this.context.save();
      this.context.beginPath();
      
      var px = mx = this.x;
      var py = my = this.y;
      //reposition magnifying glas near the top edge
      var ty = 2*43+28-this.yOffset;
      if (this.y<ty) {py=ty; my = this.y*2-ty;}
      //reposition magnifying glas near the right edge
      var tx = this.canvas.width-2*43;
      if (this.x>(tx)) {px = tx; mx = this.x*2-tx;}
      
      this.context.arc(px+43, py-43, 42, 0, Math.PI * 2, false);
      this.context.clip();
      this.context.scale(2,2);
      qa_redraw_canvas_main(this,(1-mx)/2+22,(28-this.yOffset-my)/2-8);
      this.context.restore();

      //cursor
      this.imgdata = menuImages['toolbar/loupe.png'];
      this.context.drawImage(this.menu_img,this.imgdata.left,this.imgdata.top,this.imgdata.width,this.imgdata.height,px,py-this.imgdata.height,this.imgdata.width,this.imgdata.height);
    }	
    // border
    this.context.strokeStyle='#7f9db9'; 
    this.context.strokeRect(0.5,0.5,this.canvas.width-1,this.canvas.height-1); 
  }
}

function qa_mouseDragMove(e){
	this.canv_rect = this.canvas.getBoundingClientRect();
	this.loc_lft = this.canv_rect.left;
  this.loc_top = this.canv_rect.top;
	if (e.type=='mousemove') {
		var xm = e.clientX;
		var ym = e.clientY;
		//var xm = e.pageX;
		//var ym = e.pageY;
		this.x = xm - this.loc_lft;
		this.y = ym - this.loc_top;
	}
	this.ShiftChange = false;
	
	if (e.type=='keyup') {
		this.isShift = false;
		this.ShiftChange = true;
		this.isCtrl = false;
	}
	if (e.type=='keydown') {
		if (window.event) {
			this.isShift = window.event.shiftKey ? true : false;
			this.isCtrl = window.event.ctrlKey ? true : false;
		} else {
			this.isShift = e.shiftKey ? true : false;
			this.isCtrl = e.ctrlKey ? true : false;
		}
		this.ShiftChange = true;
	}
	//console.log(this.isShift,this.isCtrl,this.dragging);
	if (this.dragging){ //this.dragging
		//new position of dragged element
    if (this.handler_sqr>-1) {
      var pp = this.qconfig.split(',');
      pp[(this.handler_sqr*2-2)] = this.x.toString(16);
      pp[(this.handler_sqr*2-1)] = this.y.toString(16);
      this.qconfig = pp.join(',');
      this.redraw_once = true;
      this.qa_redraw_canvas;
    }
  } else { //change of cursor
    this.drag_box_id = -1;
		//console.log(this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height));
		if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
			var over_object = false;
			
      //this.test for labelsBoxes
      for (i=0;i<this.labelsBox.length;i++) {
				if (this.labelsBox[i][1]=='image') {
					if (this.testWithin(this.x,this.y,this.labelsBox[i][5],this.labelsBox[i][6],imgLabelWidth,imgLabelHeight)==true) {
						over_object = true;
						this.drag_box_id = i;
					}
				}
				if (this.labelsBox[i][1]=='text') {
					if (this.testWithin(this.x,this.y,this.labelsBox[i][5],this.labelsBox[i][6],labelWidth,labelHeight)==true) {
						over_object = true;
						this.drag_box_id = i;
					}
				}
			}	
      
      //this.test for buttons
      var buttonTest = -1;
      for (var i=0;i<this.buttonBox.length;i++) {
        this.buttonBox[i][5] = this.buttonBox[i][6];
        //if (this.buttonBox[i][5]==2) alert(i);
        if (this.buttonBox[i][0]=='toolbar/ico_drop.png') this.buttonBox[i][5] = this.buttonBox[i-1][5];
        
				if (this.buttonBox[i][0].indexOf('vert_')==-1 && this.testWithin(this.x,this.y,this.buttonBox[i][1],this.buttonBox[i][2],this.buttonBox[i][3],this.buttonBox[i][4])==true) {
          over_object = true;
          buttonTest = i;
          this.buttonBox[i][5] = 1;
          
          //double button
          var j=i;
          if (this.buttonBox[i][0]=='toolbar/ico_drop.png') j=i-1;
          if (i<this.buttonBox.length-1 && this.buttonBox[i+1][0]=='toolbar/ico_drop.png') j=i+1;
          this.buttonBox[j][5] = 1;
        }
      }
      
      if (this.buttonOver != buttonTest) {
        this.buttonOver = buttonTest;
        this.redraw_once = true;
        this.qa_redraw_canvas;
      }
      
      //this.test for buttonBoxPanels
      this.labelsBoxPanelOver=-1;
      for (var i=0;i<this.labelsBoxPanel.length;i++) {
        if (this.testWithin(this.x,this.y,this.labelsBoxPanel[i][0],this.labelsBoxPanel[i][1],this.labelsBoxPanel[i][2],this.labelsBoxPanel[i][3])==true) {
          over_object = true;
          this.labelsBoxPanelOver=i;
        }
      }

      //this.test for handler points
      
      if (this.qconfig!='') {
        var pp = this.qconfig.split(',');
        this.handler_dot = -1;
        this.handler_sqr = -1;
        for (var n=1;n<pp.length/2;n++) {
          var ttx = (parseInt(pp[n*2].trim(), 16)-parseInt(pp[n*2-2].trim(), 16))/2+parseInt(pp[n*2-2].trim(), 16);
          var tty = (parseInt(pp[n*2+1].trim(), 16)-parseInt(pp[n*2-1].trim(), 16))/2+parseInt(pp[n*2-1].trim(), 16);
          if (this.testWithin(this.x,this.y,ttx-3,tty-3,7,7)) this.handler_dot = n;
          if (this.testWithin(this.x,this.y,parseInt(pp[n*2-2].trim(), 16)-3,parseInt(pp[n*2-1].trim(), 16)-1,7,7)) this.handler_sqr = n;
        }
      }
      
      var cur = 'default';
      if (this.y>25) cur = 'crosshair';
      //if (global_edit) cur = 'not-allowed';
			//if (global_edit && this.test_result!='') cur = 'move';
			//if (global_edit && this.test_result!='' && this.test_result.indexOf('$')<this.test_result.length-1) cur = 'default';
 			if (this.global_delpoint || this.isCtrl) cur = 'url(/html5/images/cur_cross.cur) 6 5, default';
			if (over_object) cur = 'pointer';
      if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0]=='toolbar/ico_help.png') cur = 'help';
      if (this.global_clearpnl) cur = 'default';
 			//if (this.handler_sqr>-1) cur = 'url(/html5/images/cur_cross.cur) 6 5, default';
      e.target.style.cursor = cur;
      
		}
	}
  if (this.oldx!=this.x || this.oldy!=this.y) this.mouse_moved = true;
  this.oldx=this.x;
  this.oldy=this.y;
  
  //this.freehand draw  
  if (this.qconfig=='' && this.y>28 && this.poly_temp_points[7]!=0 && this.freehand) {
    this.angle1 = this.angle2 = this.distn = this.dx = this.dy = -1;
    if (this.poly_temp_points[3]!=0 && this.poly_temp_points[5]!=0) 
      this.angle1 = Math.atan2(this.poly_temp_points[5]-this.poly_temp_points[3],this.poly_temp_points[4]-this.poly_temp_points[2]);
    if (this.poly_temp_points[5]!=0) 
      this.angle2 = Math.atan2(this.y-this.poly_temp_points[5],this.x-this.poly_temp_points[4]);
    
    if (this.poly_temp_points[5]==0) {
      this.dx = this.x - this.poly_temp_points[6];
      this.dy = this.y - this.poly_temp_points[7];
    } else {
      this.dx = this.x - this.poly_temp_points[4];
      this.dy = this.y - this.poly_temp_points[5];
    }
    
    this.distn = Math.sqrt(this.dx*this.dx+this.dy*this.dy);
    
    var add_point = false;
    
    //if one just started freedrawing
    if (this.poly_temp_points[3]==0 && this.poly_temp_points[5]==0 && this.distn > 10) {
      //because this is this.freehand and no point has been added - add starting one first
      this.poly_temp += Math.round(this.poly_temp_points[6]).toString(16)+','+Math.round(this.poly_temp_points[7]).toString(16)+',';
      this.poly_temp_points[0] = this.poly_temp_points[4] = this.poly_temp_points[6];
      this.poly_temp_points[1] = this.poly_temp_points[5] = this.poly_temp_points[7];
      //and then dirct to add the actual one
      add_point = true;
    }
      
    //console.log(this.angle1,this.angle2,this.distn,Math.abs(this.angle2-this.angle1),(1/this.distn*3));  
    
    //checking the angle (dependend on the distance)
    if (this.poly_temp_points[3]!=0 && this.poly_temp_points[5]!=0 && this.distn > 10 && Math.abs(this.angle2-this.angle1)>(1/this.distn*3)) add_point = true;
        
    if (add_point) {
      this.poly_temp += Math.round(this.x).toString(16)+','+Math.round(this.y).toString(16)+',';
      this.poly_temp_points[2] = this.poly_temp_points[4];
      this.poly_temp_points[3] = this.poly_temp_points[5];
      this.poly_temp_points[4] = this.x;
      this.poly_temp_points[5] = this.y;
      //this.poly_temp_points[6] = this.x;
      //this.poly_temp_points[7] = this.y;
      //console.log(this.poly_temp);
    }
  }
  //this.freehand draw end
}

function qa_mouseDragDown(e){
	if (this.testWithin(e.clientX,e.clientY,0,0,this.canvas.width,this.canvas.height)){
		this.x = e.clientX - this.loc_lft;
		this.y = e.clientY - this.loc_top;
		if (this.drag_box_id>-1) {
			this.sub_x = this.x - this.labelsBox[this.drag_box_id][5];
			this.sub_y = this.y - this.labelsBox[this.drag_box_id][6];
		}
		if (this.panelOptionOver==-1) this.dragging = true;	
	}
  if (this.handler_dot>-1 && !this.global_delpoint) {
    var pp1 = this.qconfig.split(',');
    var pp2 = pp1.slice(0,this.handler_dot*2);
    pp2.push(Math.round(this.x).toString(16));
    pp2.push(Math.round(this.y).toString(16));
    this.qconfig = pp2.join(',');
    this.qconfig += ','+pp1.slice(this.handler_dot*2,pp1.length).join(',');
    this.handler_sqr = this.handler_dot+1;
    this.handler_dot = -1;
    this.redraw_once = true;
    this.qa_redraw_canvas;
  }
  //this.freehand
  if (this.qconfig=='' && this.y>28) {
    this.poly_temp_points[6] = this.x;
    this.poly_temp_points[7] = this.y;
    this.freehand = true;
  }
}

function qa_ReturnInfo() {
  var questions_correct = 0;
  var questions_incorrect = 0;
  var questions_total = 0;
  var questions_result = '';
  var temp_answ = new Array();
  //console.log(this.answerBox);
	for (i=0;i<this.answerBox.length;i++) {
    temp_answ[i] = this.answerBox[i][1]+','+this.answerBox[i][2];
    questions_total++;
  }
  for (i=0;i<this.labelsBox.length;i++) {
    
    if (this.labelsBox[i][9]=='t') questions_correct++;
    if (this.labelsBox[i][9]=='f') questions_incorrect++;
    if (this.labelsBox[i][9]=='t' || this.labelsBox[i][9]=='f') questions_result+=this.labelsBox[i][5]+'$'+(this.labelsBox[i][6]-25+this.yOffset)+'$'+this.labelsBox[i][2]+'$'+this.labelsBox[i][9]+'$';
  }  
  var marks_max = this.marks_per_correct * questions_total;
  var marks_total = this.marks_per_correct * questions_correct + this.marks_per_incorrect * questions_incorrect;
  if (this.marking_method != 'Mark per Option') {
    marks_total = this.marks_per_incorrect;
    marks_max = this.marks_per_correct;
    if (questions_correst == questions_total) marks_total = this.marks_per_correct;
  }
  var result = marks_total+'$'+marks_max+';'+questions_result;
  
  var flashTarget = (typeof flashTarget === 'undefined' || flashTarget == '') ? 'q' : flashTarget;
}

function qa_mouseDblClick(){
  this.global_dblclick = true;
}

function qa_mouseDragUp(){
	this.dragging = false;
  
  this.button_test();

  this.global_zoom = false;
  if (this.buttonBox[this.buttonBoxNames['toolbar/ico_zoom.png']][6]==2) this.global_zoom = true;
  this.global_delpoint = false;
	//console.log(this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7]);
	if (this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6]==2 && this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][7]=='+') this.global_delpoint = true;
  if (this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][5]==2 && this.qconfig!='') this.global_clearpnl = true;
  
	//console.log(this.global_delpoint,this.global_clearpnl);
  //this.test panel buttons
  if (this.panel_buttons.length>0) {
    for (n=0;n<this.panel_buttons.length;n++) {
      if (this.testWithin(this.x,this.y,this.panel_buttons[n][1],this.panel_buttons[n][2],this.panel_buttons[n][3],this.panel_buttons[n][4])) this.panel_button_selected = this.panel_buttons[n][0];
        }
  }
  
  //polygon & this.freehand
  //distance of up from down as for "click"
  this.dx = this.x - this.poly_temp_points[6]; 
  this.dy = this.y - this.poly_temp_points[7];
  this.distn = Math.sqrt(this.dx*this.dx+this.dy*this.dy);
  if (this.qconfig=='' && this.y>28) {
    //condition for the finish
    if ((this.poly_temp.length>2 && (Math.abs(this.poly_temp_points[0]-this.x)<3 && Math.abs(this.poly_temp_points[1]-this.y)<3)) || (Math.abs(this.poly_temp_points[8]-this.x)<3 && Math.abs(this.poly_temp_points[9]-this.y)<3))  {      
      this.qconfig = this.poly_temp + Math.round(this.poly_temp_points[0]).toString(16)+','+Math.round(this.poly_temp_points[1]).toString(16);
			this.global_delpoint_avail = true; 
      this.poly_temp = '';
      this.poly_temp_points = new Array(0,0,0,0,0,0,0,0,0,0);
    } else {
      //??
      if (!this.freehand || this.distn<5) this.poly_temp += Math.round(this.x).toString(16)+','+Math.round(this.y).toString(16)+',';
      //remember the starting point
      if (this.poly_temp_points[1] == 0) {
        this.poly_temp_points[0] = this.x;
        this.poly_temp_points[1] = this.y;
      }
      //remember the second last and the last point
      this.poly_temp_points[2] = this.poly_temp_points[4];
      this.poly_temp_points[3] = this.poly_temp_points[5];
      this.poly_temp_points[4] = this.poly_temp_points[8] = this.x;
      this.poly_temp_points[5] = this.poly_temp_points[9] = this.y;
      this.poly_temp_points[6] = 0;
      this.poly_temp_points[7] = 0;
    }
    //console.log('>>>',this.poly_temp);
  }
  this.freehand = false;
  
  if (this.panel_button_selected!='') {
    this.panel_buttons = new Array();
    this.global_clearpnl = false;
    this.global_delpoint = false;
    if (this.panel_button_selected=='Y') {
			this.qconfig='';
			this.global_delpoint_avail = false;
		}
    this.panel_button_selected = '';
  }  
  
  if (this.handler_sqr!=-1 && (this.global_delpoint || this.isCtrl)) {
    var pp1 = this.qconfig.split(',');
    if (this.handler_sqr>1 && this.handler_sqr<pp1.length/2) {
      this.qconfig = pp1.slice(0,this.handler_sqr*2-2).join(',');
      this.qconfig += ','+pp1.slice(this.handler_sqr*2,pp1.length).join(',');
    } else {
      this.qconfig = pp1.slice(2,pp1.length-2).join(',');
      this.qconfig += ',' + pp1.slice(2,4).join(',');
    }
    
    //clear whole array
    if (pp1.length <= 6) {
      this.qconfig='';
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][6]=0;
      this.buttonBox[this.buttonBoxNames['toolbar/ico_cross_off.png']][5]=0;
      this.global_delpoint = false;      
      //e.target.style.cursor = 'crosshair';
      }
    this.handler_sqr = -1; 
  }  
	this.redraw_once = true;
  this.do_the_test =true;
	this.qa_redraw_canvas;
  this.qa_ReturnInfo();
  
}
function rqa(num) {

	this.setUpArea	 = 	setUpArea;
	this.yoffset_fix	 = 	yoffset_fix;
	this.qa_menuBuild	 = 	qa_menuBuild;
	this.qa_test	 				= 	qa_test;
	this.qa_redraw_canvas	 = 	qa_redraw_canvas;
	this.qa_mouseDragMove	 = 	qa_mouseDragMove;
	this.qa_mouseDragDown	 = 	qa_mouseDragDown;
	//this.qa_onkeydown	 		 = 	qa_onkeydown;
	//this.qa_onkeyup  	 		 = 	qa_onkeyup;
	this.qa_ReturnInfo	   = 	qa_ReturnInfo;
	this.qa_mouseDblClick	 = 	qa_mouseDblClick;
	this.qa_mouseDragUp	   = 	qa_mouseDragUp;
	this.rqa	 = 	rqa	;

	this.hexifycolour=hexifycolour;
	this.textHeight=textHeight;
	this.wrapText=wrapText;
	this.findPos=findPos;
	this.testWithin=testWithin;
	this.edtDot=edtDot;
	this.lineDraw=lineDraw;
	this.ellipseDraw=ellipseDraw;
	this.rectDraw=rectDraw;
	this.polyDrawH=polyDrawH;
	this.menuBuild_icons= menuBuild_icons;
	this.menuRebuild=menuRebuild;
	this.menuRebuild_panel=menuRebuild_panel;
	this.button_test=button_test;
	this.build_msgbox=build_msgbox;
	this.tooltip_draw=tooltip_draw;
	
  this.nikotest = 1;

	this.test; 
	this.x,this.y,this.sub_x,this.sub_y;
  this.oldx,this.oldy;
  this.mouse_moved=false;
	this.isCtrl = this.isShift = false;
  this.ShiftChange = false;
	//var scale_i=1;                                          //label image scale
	this.drag_box_id=-1;                               //index of box beeing dragged
  this.menu_ready = 1;
  this.do_the_test = false;
  this.test_result = '';

	//var allImagesLoaded = false;
	//var max_num_images = 0;
	this.answerBox = new Array(); 			          // sublevels of this keep all the answer data
	this.labelsBox = new Array(); 			            // sublevels of this keep all the label data
  this.labelsBoxPanel = new Array();
  this.buttonBox = new Array();                 // sublevels of this keep all the buttons data
  this.qa_panelBox = new Array();                   // sublevels of this keep the panels data
  this.buttonBoxNames = new Array();      // transcription of button names into its index in ButtonBox (?)
  this.qa_panelActiveParts = new Array();       // array of positions panel's active elements
  this.buttonClicked = -1;                            // index of the button that was clicked
  this.buttonOver =-1;                                // index of the button the mouse is over
  this.panelOptionOver =-1;                       // index of the option on panel the mouse is over
  this.panelOver =-1                                   // index of the panel the mouse is over
  this.activeLabel = 0;
  this.labelsBoxPanelOver = -1;
  this.allUnaswered = false;
  this.global_zoom = true;
  this.global_delpoint = false;
  this.global_delpoint_avail = false;
  this.global_clearpnl = false;
  this.global_dblclick = false;
  this.panel_buttons = new Array();
  this.panel_button_selected = '';
  //vars for polygon
  this.handler_dot = this.handler_sqr = this.handler_clk = -1;
  this.poly_temp = '';
  this.freehand = false;
  this.angle1, this.angle2, this.distn, this.dx, this.dy;
  this.poly_temp_points = new Array(0,0,0,0,0,0,0,0,0,0); //first point, second last point, last point, last down, last up ... mouse points
  this.draw_limit = new Array(); //used to limit polygon, ellipse and sqare positions
  this.any_overlaping = this.overlapping_show = false;


  //defining panel's active parts
	this.qa_panelActiveParts.push('toolbar/pan_colours.png');
  this.qa_panelActiveParts['toolbar/pan_colours.png'] = new Array();
  //'toolbar/pan_colours.png
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][00+i] = (i*18+1)+','+19;
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][10+i] = (i*18+1)+','+(39+12*0);
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][20+i] = (i*18+1)+','+(39+12*1);
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][30+i] = (i*18+1)+','+(39+12*2);
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][40+i] = (i*18+1)+','+(39+12*3);
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][50+i] = (i*18+1)+','+(39+12*4);
  for(i=0;i<10;i++) this.qa_panelActiveParts['toolbar/pan_colours.png'][60+i] = (i*18+1)+','+121;
	this.yOffset ; 						                 // coords of everything made in label_add.swf include toolbar 
  this.yOffest_fix =3;                          // special fix for yOffest in area (menubar in flash was smaller)
  this.is_an_answer = false;
  this.q_Num;

	this.currentColours = Array('#FFFFFF','#3F3F3F','#000000','#FF0000');  // fill, line, text colours
	this.lineThickness  = 1; 									                                                            // current thickness of borders around draggable labels and manually drawn lines / arrows (in pixels) 
	//var fontChoices    = Array(9, 10, 11, 12, 14, 16, 18); 		                          // font size in drop down menu
	this.flashFontSize  = Array(11, 12, 14, 16, 18, 20, 22); 	                          // font size equivalent in Flash (not standard sizes)
	this.fontSizePos    = 1; 									                                                            // current font size for labels (index from array above);
	this.dragging = false;
	this.redraw_once = false;
	this.gen_img, this.menu_img;
	this.gen_img_loaded = false;
	this.menu_img_loaded = false;
	this.loc_lft = this.loc_top = 0;
  this.canv_rect;
  this.mov_id = -1;
  //var mov_x=0;
  //var mov_y=0;
  this.context;
  this.canvas;
  this.marks_per_correct = 1;
  this.marks_per_incorrect = 0;
  this.marking_method = 'Mark per Option';
  this.qmode,this.qanswer,this.qconfig;
  this.imgdata,this.imgdatab,this.imgdatac;
}
