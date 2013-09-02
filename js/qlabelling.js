function setUpLabelling(num, doorId, lang, image, config, answer, extra, colour, mode) {
	this.canvas = document.getElementById('canvas'+num);
	this.canv_rect = this.canvas.getBoundingClientRect();
  
	if (this.canvas && this.canvas.getContext){
		this.canvas.onmouseup   = this.ql_mouseDragUp.bind(this);
		this.canvas.onmousedown = this.ql_mouseDragDown.bind(this);
		this.canvas.onmousemove = this.ql_mouseDragMove.bind(this);
		this.canvas.tabIndex 		= 1000; //force keyboard events
		if (this.canvas.addEventListener)
    {
      this.canvas.addEventListener("keydown",	ql_mouseDragMove.bind(this),false);
      this.canvas.addEventListener("keyup",		ql_mouseDragMove.bind(this),false);
      this.canvas.addEventListener("keypress",ql_mouseDragMove.bind(this),false);
    }
    else if (this.canvas.attachEvent)
    {
      this.canvas.attachEvent("onkeydown", 	ql_mouseDragMove.bind(this));
      this.canvas.attachEvent("onkeyup", 		ql_mouseDragMove.bind(this));
      this.canvas.attachEvent("onkeypress", ql_mouseDragMove.bind(this));
    }
		else
		{
			this.canvas.onkeydown   = ql_mouseDragMove.bind(this);
			this.canvas.onkeyup     = ql_mouseDragMove.bind(this);
			this.canvas.onkeypress  = ql_mouseDragMove.bind(this);
		}

		this.intervalID = window.setInterval(this.ql_redraw_canvas.bind(this), 10);
	}
	if (this.canvas && !this.canvas.getContext){
		alert ('Canvas not supported');
	}

	if (this.canvas && this.canvas.getContext){
		this.context = this.canvas.getContext('2d');
		this.context.lineWidth = 1;

		//---------- num, 
		this.q_Num = num;
		//---------- doorId, 
		this.doorId = doorId;
		
		this.gen_img = new Image();  
		function gen_img_onload() {
			this.redraw_once = true;
      this.ql_redraw_canvas;        
		}
		this.gen_img.onload = gen_img_onload.bind(this);
		this.gen_img.src = '/media/'+image; 
    
		//---------- mode 
		this.yOffset = 25;
		if (mode=='edit') this.yOffset = 0;
    this.qmode = mode;
		
		//---------- config, 
 		if (config=='') config = '#3f3f3f;1;#ffffff;10;#000000;100;19;200;200;single;label;$$$$;';

		var existingInfo = config.split(';');
		this.currentColours[1] 	= existingInfo[0];	                        // line colour
		this.lineThickness		  = Number(existingInfo[1]);				          // line thickness
		this.currentColours[0]	= existingInfo[2];	                        // fill colour
		for (i=0; i<this.fontChoices.length; i++) {
			if (this.fontChoices[i] == existingInfo[3]) {
				this.fontSizePos = i;										                        // text size
				break;
			}
		}

		this.currentColours[2]	= existingInfo[4];	                        // text colour
		this.labelWidth			    = Number(existingInfo[5]);					        // text label width
		this.labelWidthEffect   = this.labelWidth;
		this.labelHeight			  = Number(existingInfo[6]); 					        // text label height
		this.labelHeightEffect  = this.labelHeight;
		this.imglabelWidth		  = Number(existingInfo[7]);					        // image label width
		this.imglabelHeight		  = Number(existingInfo[8]);					        // image label height
		this.labelType			    = existingInfo[9];							            // single/multiple
		this.qType				      = existingInfo[10];							            // label/menu
		this.existingLabelInfo 	= existingInfo[11];						 				      // one label?
		if (typeof(existingInfo[11])!='undefined') {
			this.existingLabelInfo = existingInfo[11].split("|"); // divides each label
		}
		
		for (i=0;i<this.existingLabelInfo.length; i++) 
			if (typeof(this.existingLabelInfo[i])=='undefined' || this.existingLabelInfo[i]=='') 
				this.existingLabelInfo[i] = '$$$$';
		
		//add empty labels to 20
		for (i=this.existingLabelInfo.length; i<20; i++) 
			this.existingLabelInfo.push('$$$$');
		
		//arrays of default positions of labels
		var apx = new Array();
		var apy = new Array();
		var tmpx = 5;
		var tmpy = 30;
		for (i=0; i<20; i++) {
			apx.push(tmpx);
			apy.push(tmpy);
			tmpx += this.labelWidth + this.i_spacex;
			if ((tmpx+this.labelWidth)>220) {
				tmpx = 5;
				tmpy += this.labelHeight + this.i_spacey;
			}
		}
		//reading lines/arrows/bobbles
    for (i=12; i<existingInfo.length; i++) {
			if (existingInfo[i]!='') {
				var shapeTemp = existingInfo[i].split("$");
				this.shapeBox.push(shapeTemp);
			}
    }
    //colours recalc
    for (i=0;i<this.currentColours.length;i++) this.currentColours[i] = hexifycolour(this.currentColours[i]);

		this.imagesLoaded 	= 0;
		var blank_count = 0;
    if (typeof(this.existingLabelInfo)!='undefined') {
			for (i=0; i<this.existingLabelInfo.length; i++) {
				var myLabelInfo = this.existingLabelInfo[i].split("$"); //divides each bit of info about label
				var mli_index = (myLabelInfo[0]!=''?Number(myLabelInfo[0]):i); 	//index

				var yes_to_add = true;
				if (typeof(myLabelInfo[4])=='undefined') yes_to_add = false;
				if (this.qmode=='analysis' && myLabelInfo[4]=='') yes_to_add = false;
				if (this.qmode=='script' && myLabelInfo[4]=='') yes_to_add = false;
				if (mli_index > 19) yes_to_add = false;				
				
				if (yes_to_add) {
					var mli_combo = (myLabelInfo[1]!=''?Number(myLabelInfo[1]):0); 	//combo indicator?  >0
					var mli_pos_xa = Number(myLabelInfo[2]);  											//pos_x
					var mli_pos_ya = Number(myLabelInfo[3]);  											//pos_y
					var mli_pos_xb = apx[mli_index-blank_count];										//pos_x
					var mli_pos_yb = apy[mli_index-blank_count];										//pos_y
					var mli_answr =  myLabelInfo[4];                       					//answer
					
					if (typeof(myLabelInfo[4])=='undefined' || myLabelInfo[4]=='') blank_count++;

					var myLabelType = "text"; // text or image label?
					if (typeof(mli_answr)!='undefined' && (mli_answr.indexOf('.jpeg') != -1 || mli_answr.indexOf('.jpg') != -1 || mli_answr.indexOf('.png') != -1 || mli_answr.indexOf('.gif') != -1)) myLabelType = "image";        

					var tmp_pholder = new Array();
					this.pho_index = this.pholderBox.length-1;
					//updating this.pholderBox array
					tmp_pholder[0] = mli_index;   									//index
					if (mli_pos_xa>=220) {
						tmp_pholder[1] = mli_pos_xa;     							//pos_x						
					}else{
						tmp_pholder[1] = -500;					
					}
					tmp_pholder[2] = mli_pos_ya - this.yOffset; 		//pos_y
					tmp_pholder[3] = myLabelType;            				//type: text/image
					if (myLabelType=='image') {
						var mli_answr_label = mli_answr.split("~");
						tmp_pholder[4] = mli_answr_label[0];	  			//answer ie. 'beetle3.png' from 'beetle3.png~80~75'
					}else {
					tmp_pholder[4] = mli_answr;					      			//answer ie. 'spider'
					}
					tmp_pholder[5] = '';	                      		//corectness					
					tmp_pholder[6] = mli_combo;	                  	//combo
					if (mli_combo==0) this.pholderBox[mli_index] = tmp_pholder;
					
					var tmp_answer = new Array();
					tmp_answer[0] = mli_index;								  	//index
					tmp_answer[1] = myLabelType;									//type: text/image
					tmp_answer[2] = mli_answr;								  	//label
					this.labelTxt.push(mli_answr);
					tmp_answer[3] = tmp_answer[4] = ''; 					//empty for non-image
					if (myLabelType=='image') {
						var existingImageInfo = myLabelInfo[4].split("~");
						tmp_answer[2] = existingImageInfo[0];	    	//filename
						this.max_num_images++;
						tmp_answer[3] = existingImageInfo[1];	    	//image oryginal width
						tmp_answer[4] = existingImageInfo[2];	    	//image oryginal height
						}
					if (((this.qmode=='edit' || this.qmode=='analysis') && mli_pos_xa<220) || this.qmode=='answer' || this.qmode=='script'){
						tmp_answer[5] = mli_pos_xb;	                //pos_x - new
						tmp_answer[6] = mli_pos_yb - this.yOffset;	//pos_y - new
						tmp_answer[7] = mli_pos_xb;	                //initial pos_x
						tmp_answer[8] = mli_pos_yb - this.yOffset;	//initial pos_y
					}else{
						tmp_answer[5] = mli_pos_xa;	                //pos_x from data
						tmp_answer[6] = mli_pos_ya - this.yOffset;	//pos_y from data
						tmp_answer[7] = mli_pos_xa;	                //initial pos_x
						tmp_answer[8] = mli_pos_ya - this.yOffset;	//initial pos_y
					}
					tmp_answer[9] = '';	                        	//corectness
					tmp_answer[10] = mli_combo;	                  //combo

					if (typeof(this.answerBox[mli_index])=='undefined') this.answerBox[mli_index] = new Array();
					this.answerBox[mli_index][mli_combo] = tmp_answer;
					
					//duplicates in edit just in case of swich to multi
					if (this.labelType == 'single' && this.qmode == 'edit' && tmp_answer[2]!=''){
						var tmp_answer2 = tmp_answer.slice(0);
						tmp_answer2[5] = mli_pos_xb;
						tmp_answer2[6] = mli_pos_yb - this.yOffset;
						tmp_answer2[7] = mli_pos_xb;
						tmp_answer2[8] = mli_pos_yb - this.yOffset;
						tmp_answer2[10] = (mli_combo+1);
						this.answerBox[mli_index][mli_combo+1] = tmp_answer2;
					}	
										
				}else{
					blank_count++;
				}
			}
		}
		//calculating order number of the pholderBox for analysis as [7]
		var nr = 0;
		for (i=0;i<this.pholderBox.length;i++) 
			if (typeof(this.pholderBox[i])!='undefined' && this.pholderBox[i][1] > -500) this.pholderBox[i][7] = nr++;
		
		//scaling?
		var scale_x,scale_y;
		if (this.imglabelWidth>200) scale_x=200/this.imglabelWidth;
		if (this.imglabelHeight>200) scale_y=200/this.imglabelHeight;
		this.scale_i = scale_x;
		if (this.scale_i<scale_y) this.scale_i = scale_y;

		//loading label images and drawing boxes
		this.context.fillStyle=this.currentColours[0];
		this.context.StrokeStyle=this.currentColours[1];
		
	function ql_gen_img_onload(){
		this.imagesLoaded ++;
		if (this.imagesLoaded == this.max_num_images) {
			this.allImagesLoaded = true;
			this.redraw_once = true;
			this.ql_redraw_canvas;
		}
	}  		
	
	//loading images
	if (typeof(this.answerBox)!='undefined')
		for (i=0;i<this.answerBox.length;i++) {
			j=0;
			if (typeof(this.answerBox[i][j])!='undefined' && this.answerBox[i][j][1]=="image") {
				this.answerBox[i][j][11] = new Image();
				this.answerBox[i][j][11].onload = ql_gen_img_onload.bind(this);
				this.answerBox[i][j][11].src = '/media/'+this.answerBox[i][j][2];
			}
		}


    if (this.max_num_images==0) {
      this.allImagesLoaded = true;
      this.redraw_once = true;
      this.ql_redraw_canvas;
      }
		
    //setting up comboboxes
		/*
    canvasbox = document.getElementById('canvasbox');
    if (this.qType=='menu') {
      var s = $('<select />');
      this.labelTxt.sort();
      $('<option />', {value: '', text: ''}).appendTo(s);
      for(i=0;i<this.labelTxt.length;i++) {
        $('<option />', {value: this.labelTxt[i], text: this.labelTxt[i]}).appendTo(s);
      }
      //$('<br />').appendTo(s);
      //s.appendTo('div#canvasbox');
     
      var c = $('this.canvas').position();
      var st = new Array();
      for(i=0;i<this.pholderBox.length;i++) {
        st[i] = s.clone().appendTo(canvasbox).attr('id','cb_'+i).change(function() {
            var tid = 1*this.id.split('_')[1];
            var tiv = this.value;
            alert(tid+tiv);
            //alert(tiv+this.pholderBox[tid][4]);
            if (tiv==this.pholderBox[tid][4]) 
              this.answerBox[tid][9]='t';
            else
              this.answerBox[tid][9]='f';
              
              ReturnInfo();
          });
          
        st[i].css({position: "relative",top: c.top-st[i].position().top+1*this.pholderBox[i][2]+"px",left: c.left-st[i].position().left+1*this.pholderBox[i][1]+"px"});
      }
    }    
    */
    
		//---------- answer, 
		// sort out existing answer info
    if (answer != '') this.is_an_answer = true
    if (answer != "" && answer != undefined && answer != "undefined" && answer != null && answer != "null") {
      var answer_l1 = answer.split(";");
      var answer_l2 = answer_l1[1].split('$');
      for (l=0; l<answer_l2.length/4; l++) {
        if (answer_l2[l*4]!='') {
          var ans_x = Number(answer_l2[l*4+0]);
          var ans_y = Number(answer_l2[l*4+1]);
          var ans_n = answer_l2[l*4+2];
          var ans_b = answer_l2[l*4+3];
          for (i=0;i<this.answerBox.length;i++) {
						for (j=0;j<this.answerBox[i].length;j++) {
							if (typeof(this.answerBox[i][j])!='undefined' && this.answerBox[i][j][2]==ans_n) {
								this.answerBox[i][j][5] = ans_x;
								this.answerBox[i][j][6] = ans_y+25-this.yOffset;
								this.answerBox[i][j][9] = ans_b;
								$("#cb_"+i).val(3);
							}
						}
          }
        }      
      }    
    }      
    
		//---------- extra, 
    if (extra != "" && extra != undefined && extra != "undefined" && extra != null && extra != "null") {
      var extra_l1 = extra.split('~');
      this.marks_per_correct = extra_l1[0];
      this.marks_per_incorrect = extra_l1[1];
      this.marking_method = extra_l1[2];
      }
		//---------- colour 
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
		this.menu_img.src = '/js/images/combined.png'; 
	}
}

function combo_scope(answer_set) {
	var vc = '';
  for (var v=0;v<answer_set.length;v++){
		vc += ((answer_set[v][5]<220)?'-':'+')+answer_set[v][10]+','; 
		}
	return vc;
}
	
function ql_draw_box(i,j,temp_x,temp_y) {
  if (this.answerBox[i][j][1]=='image') this.context.fillRect(temp_x,temp_y,this.imglabelWidth,this.imglabelHeight);
  if (this.answerBox[i][j][1]=='text') this.context.fillRect(temp_x,temp_y,this.labelWidthEffect,this.labelHeightEffect);

    this.context.shadowColor = 'white';
    this.context.shadowBlur = 0;
    this.context.shadowOffsetX = 0;
    this.context.shadowOffsetY = 0;
  
  if (this.answerBox[i][j][1]=='image') {
		if (typeof(this.answerBox[i][j][11])!='undefined'){
			this.context.drawImage(this.answerBox[i][j][11],temp_x+(this.imglabelWidth-this.answerBox[i][j][3])*0.5,temp_y+(this.imglabelHeight-this.answerBox[i][j][4])*0.5);
			this.context.strokeRect(temp_x+0.5,temp_y+0.5,this.imglabelWidth,this.imglabelHeight);
		} else {
			console.log(i,j,this.answerBox[i][j][11]);
		}
  }
  if (this.answerBox[i][j][1]=='text') {
    this.context.textAlign="center";
		this.context.font="12px Arial";
    this.context.fillStyle=this.currentColours[2];
		var tmp_text = this.answerBox[i][j][2];
		if (this.qmode=='script') {
			this.context.fillStyle='#000000';
			if (this.drag_box_id ==i && this.drag_box_combo == j && temp_x>220){
				this.context.fillStyle=this.currentColours[2];	
				for (var a=0;a<this.pholderBox.length;a++) 
					if (this.pholderBox[a][1]==temp_x && this.pholderBox[a][2]==temp_y) tmp_text = this.pholderBox[a][4];
			}	
		}
    var wrapped = this.wrapText(tmp_text,this.labelWidthEffect);
		this.fillWrappedText(this.context,wrapped[0],Math.round(temp_x+this.labelWidthEffect*0.5)+0.5,Math.round(temp_y+this.fontSizes[this.fontSizePos])+0.5);
    this.context.fillStyle=this.currentColours[0];
    this.context.strokeRect(temp_x+0.5,temp_y+0.5,this.labelWidthEffect,this.labelHeightEffect);
  }
	if (this.qmode=='script' && this.answerBox[i][j][9]!='') {
		if (!(this.qmode=='script' && this.drag_box_id ==i && this.drag_box_combo == j)){
			this.imgdata = menuImages['toolbar/ico_tick_g.png'];
			if (this.answerBox[i][j][9]=='f') this.imgdata = menuImages['toolbar/ico_tick_r.png'];
			this.context.drawImage(this.menu_img,this.imgdata.left,this.imgdata.top,this.imgdata.width,this.imgdata.height,temp_x+0.5+this.labelWidthEffect-20,temp_y+0.5+this.labelHeightEffect-20,this.imgdata.width,this.imgdata.height);
		}
	}
	if (this.qmode=='analysis' && temp_x>=220) {
		var temp_col = this.context.fillStyle;
		this.context.fillStyle=this.currentColours[1];
		this.context.fillRect(temp_x-16,temp_y,16,15);
		this.context.textAlign="center";
		this.context.fillStyle='#fff';
		this.context.font="bold 13px Arial";
		this.char_labels=this.pholderBox[i][7]+1;
		this.context.fillText(String.fromCharCode(64+this.char_labels), temp_x-8,temp_y+12);
		this.context.fillStyle = temp_col;
	}
}

function ql_redraw_box(i,j) {
	if (typeof this.answerBox[i][j] != 'undefined' && (this.labelType == 'multiple' || this.answerBox[i][j][10]==0)) {
    temp_x = this.answerBox[i][j][5];
    temp_y = this.answerBox[i][j][6];
		
    //setting shadow
    if (((this.drag_box_id==i && this.drag_box_combo==j) || (this.mov_id == i && this.mov_combo == j)) && this.panelOptionOver==-1 && this.qmode!='script')  {
      this.context.shadowColor = '#AAA';
      this.context.shadowBlur = 8;
      this.context.shadowOffsetX = 2;
      this.context.shadowOffsetY = 2;
    }
    
    //slowing down (need to be after setting shadow not to leave shadow after animation)
    if (this.mov_id == i && this.mov_combo == j) {
		
      temp_x = this.mov_x = this.mov_x-(this.mov_x-this.answerBox[i][j][5])/this.slow_speed;
      temp_y = this.mov_y = this.mov_y-(this.mov_y-this.answerBox[i][j][6])/this.slow_speed;
      //end of slowing down  
      if (Math.abs(this.mov_x-this.answerBox[i][j][5])<1) {
        temp_x = this.answerBox[i][j][5];
        temp_y = this.answerBox[i][j][6];
        this.mov_id = -1; //box in place -> clear mov_id -> no box to move anymore
				this.mov_combo = -1;
        this.drag_box_id = -1;
				this.drag_box_combo = -1;
        this.redraw_once = true;
      }     
    }	
		this.ql_draw_box(i,j,temp_x,temp_y);

  }	
}

function ql_panelBoxBuild (but_name,pan_name) {
  var temp_but = this.buttonBox[this.buttonBoxNames[but_name]];
  var imgdata = menuImages[pan_name];
  var tmp_but_num = this.buttonBoxNames[but_name];
  this.ql_panelBox.push(tmp_but_num);
  this.ql_panelBox[tmp_but_num] = new Array();
  this.ql_panelBox[tmp_but_num][0] = tmp_but_num;
  this.ql_panelBox[tmp_but_num][1] = but_name;
  this.ql_panelBox[tmp_but_num][2] = pan_name;
  this.ql_panelBox[tmp_but_num][3] = temp_but[1];
  this.ql_panelBox[tmp_but_num][4] = temp_but[2]+25;
  this.ql_panelBox[tmp_but_num][5] = imgdata.width;
  this.ql_panelBox[tmp_but_num][6] = imgdata.height;
}

function ql_menuBuild() {
  var toolb1 = new Array('toolbar/ico_bucket.png','toolbar/ico_brush.png','toolbar/ico_letter.png','toolbar/ico_size.png','toolbar/ico_lines.png');
  var toolt1 = new Array('fillcolour','linecolour','textcolour','textsize','lines');
  var toolb2 = new Array('toolbar/ico_erase.png','toolbar/ico_resize.png','toolbar/ico_line.png','toolbar/ico_bobble.png','toolbar/ico_arrow.png')
  var toolt2 = new Array('erase','edit','line','bobble','arrow')
  var imgdata = menuImages['toolbar/vert_0.png'];
	this.context.drawImage(this.menu_img,imgdata.left,imgdata.top,imgdata.width,imgdata.height,0,0,this.canvas.width,imgdata.height);
	var posx = this.menuBuild_icons('toolbar/vert_1.png',0,0,0,'','','');
  var spac = 4;
  posx = 4;
  var posy = 3;
  for (i=0;i<toolb1.length;i++) {
    posx = this.menuBuild_icons(toolb1[i],posx,posy,0,'','',lang_string[toolt1[i]])+spac;
    posx = this.menuBuild_icons('toolbar/ico_drop.png',posx-2,posy,0,'','','')+spac;
  }
 
	posx = this.menuBuild_icons('toolbar/vert_1.png',220,0,0,'','');
  spac = 5;
  posx = 224;
  for (i=0;i<toolb2.length;i++) {
    posx = this.menuBuild_icons(toolb2[i],posx,posy,0,'a','',lang_string[toolt2[i]])+spac;
  }
	this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][5]=2;
	this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6]=2;
	
	posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
	if (this.labelType == 'multiple') {
		posx = this.menuBuild_icons('toolbar/ico_single.png',posx,posy,0,'b','',lang_string['single'])+spac;
		posx = this.menuBuild_icons('toolbar/ico_multiple.png',posx,posy,2,'b','',lang_string['multiple'])+spac;
	}else{
		posx = this.menuBuild_icons('toolbar/ico_single.png',posx,posy,2,'b','',lang_string['single'])+spac;
		posx = this.menuBuild_icons('toolbar/ico_multiple.png',posx,posy,0,'b','',lang_string['multiple'])+spac;	
	}
	posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
  posx = this.menuBuild_icons('toolbar/ico_label.png',posx,posy,2,'c','',lang_string['label'])+spac;
  posx = this.menuBuild_icons('toolbar/ico_help.png',this.canvas.width-23,posy,0,'-','','')+spac;    
  
  //setting the this.ql_panelBox array
  this.ql_panelBoxBuild('toolbar/ico_bucket.png','toolbar/pan_colours.png');
  this.ql_panelBoxBuild('toolbar/ico_brush.png','toolbar/pan_colours.png');
  this.ql_panelBoxBuild('toolbar/ico_letter.png','toolbar/pan_colours.png'); 
  this.ql_panelBoxBuild('toolbar/ico_size.png','toolbar/pan_sizes.png');
  this.ql_panelBoxBuild('toolbar/ico_lines.png','toolbar/pan_lines.png');
}

function ql_redraw_canvas() {
	this.char_labels = 0;
	this.draw_limit = new Array(0,27,this.canvas.width-2,this.canvas.height-2);
  function draw_shape(_self,tt,tx1,ty1,tx2,ty2) {
    //drawing the line, bobble or arrow...
    _self.context.beginPath();
    _self.context.moveTo(tx1,ty1);
    _self.context.lineTo(tx2,ty2);
    _self.context.stroke();
    
    if (tt=='arrow') {
      _self.context.lineWidth = 1;
      var xx = tx2-tx1;
      var yy = ty2-ty1;
      var rr = Math.atan2(yy,xx);
      var pp=0.5;
      var tt= 4+1.3*_self.lineThickness;
      var hh=Math.abs(tt/Math.cos(tt));
      var x1 = 1*tx2+Math.cos(rr)*tt/2;
      var y1 = 1*ty2+Math.sin(rr)*tt/2;
      var x2 = Math.round(x1+Math.cos(rr-Math.PI+pp)*tt); 
      var y2 = Math.round(y1+Math.sin(rr-Math.PI+pp)*tt);
      var x3 = Math.round(x1+Math.cos(rr-Math.PI-pp)*tt); 
      var y3 = Math.round( y1+Math.sin(rr-Math.PI-pp)*tt);
      _self.context.beginPath();
      _self.context.moveTo(x1,y1);
      _self.context.lineTo(x2,y2);
      _self.context.lineTo(x3,y3);
      _self.context.lineTo(x1,y1);
      _self.context.fill();
      _self.context.stroke();
      _self.context.lineWidth = _self.lineThickness;
    }
    
    if (tt=='bobble') {
      _self.context.beginPath();
      _self.context.arc(tx2,ty2, 2+0.5*_self.lineThickness, 0 , 2 * Math.PI, false);
      _self.context.fill();
      _self.context.stroke();
    }
  }
 
	if (this.allImagesLoaded && this.menu_img_loaded && (this.dragging || this.redraw_once || this.mov_id!=-1 || (this.global_add != '' &&  this.shape_x1>-1) || this.global_move || this.global_erase)){
		this.redraw_once = false;
    //store this.lineThickness 
    var hold_lineThickness = this.lineThickness;

		for (i=0;i<this.shapeBox.length;i++) {
			//recalculating against limits
			if (this.shapeBox[i][2]<this.draw_limit[0]) this.shapeBox[i][2]=this.draw_limit[0];
			if (this.shapeBox[i][4]<this.draw_limit[0]) this.shapeBox[i][4]=this.draw_limit[0];
			if (this.shapeBox[i][2]>this.draw_limit[2]) this.shapeBox[i][2]=this.draw_limit[2];
			if (this.shapeBox[i][4]>this.draw_limit[2]) this.shapeBox[i][4]=this.draw_limit[2];
			if (this.shapeBox[i][3]<this.draw_limit[1]) this.shapeBox[i][3]=this.draw_limit[1];
			if (this.shapeBox[i][5]<this.draw_limit[1]) this.shapeBox[i][5]=this.draw_limit[1];
			if (this.shapeBox[i][3]>this.draw_limit[3]) this.shapeBox[i][3]=this.draw_limit[3];
			if (this.shapeBox[i][5]>this.draw_limit[3]) this.shapeBox[i][5]=this.draw_limit[3];
		}    
		//testing
    if ((this.global_move || this.global_erase) && typeof this.x != 'undefined') {
      this.lineThickness = 1.5*hold_lineThickness+2;
      this.activ_shape = -1;
      this.context.lineWidth = this.lineThickness;
      this.context.fillStyle = this.context.strokeStyle='#ff0000';
      for (i=0;i<this.shapeBox.length;i++) {
        this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
        draw_shape(this,this.shapeBox[i][1],this.shapeBox[i][2],this.shapeBox[i][3]-this.yOffset,this.shapeBox[i][4],this.shapeBox[i][5]-this.yOffset);
        var timgd = this.context.getImageData(this.x,this.y,1,1);
        var timgp = timgd.data;
        if (hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2]))== '#ff0000') this.activ_shape=i;
      }
    }
    //testing end

		this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
 		this.context.drawImage(this.gen_img,220,25-this.yOffset);

    //frames
    this.context.lineWidth = 1;
    this.context.strokeStyle='#7f9db9';  
    this.context.strokeRect(220.5,0.5,this.canvas.width-220,this.canvas.height-1); 
    
    if (this.global_move && this.activ_shape_move>-1) {      
      var tx = this.activ_shape_x - this.x;
      var ty = this.activ_shape_y - this.y;      
      var shape_end = 0
      if (Math.abs(this.shapeBox[this.activ_shape_move][2]-this.activ_shape_x)<5 && Math.abs(this.shapeBox[this.activ_shape_move][3]-this.activ_shape_y)<5) shape_end =1;
      if (Math.abs(this.shapeBox[this.activ_shape_move][4]-this.activ_shape_x)<5 && Math.abs(this.shapeBox[this.activ_shape_move][5]-this.activ_shape_y)<5) shape_end =2;
      
      //move whole
      if (shape_end==0 || shape_end==1) {
        this.shapeBox[this.activ_shape_move][2] -= tx; 
        this.shapeBox[this.activ_shape_move][3] -= ty;
      }
      if (shape_end==0 || shape_end==2) {
        this.shapeBox[this.activ_shape_move][4] -= tx;
        this.shapeBox[this.activ_shape_move][5] -= ty;
      }
      this.activ_shape_x = this.x;
      this.activ_shape_y = this.y;
    }
    
    //draw background for active shape
    if ((this.global_move || this.global_erase) && this.activ_shape>-1) {
      this.context.lineWidth = this.lineThickness;
      this.context.fillStyle = this.context.strokeStyle='#ffaaaa';
      this.context.lineCap = 'round';
      draw_shape(this,this.shapeBox[this.activ_shape][1],this.shapeBox[this.activ_shape][2],this.shapeBox[this.activ_shape][3]-this.yOffset,this.shapeBox[this.activ_shape][4],this.shapeBox[this.activ_shape][5]-this.yOffset);
    }
    
    //restore this.lineThickness 
    this.lineThickness = hold_lineThickness;
    this.context.lineCap = 'butt';
    //draw line, arrow, bobble
    this.context.lineWidth = this.lineThickness;
		this.context.strokeStyle=this.currentColours[1];
    this.context.fillStyle = this.currentColours[1];
    for (i=0;i<this.shapeBox.length;i++) {
      draw_shape(this,this.shapeBox[i][1],this.shapeBox[i][2],this.shapeBox[i][3]-this.yOffset,this.shapeBox[i][4],this.shapeBox[i][5]-this.yOffset);
    }		
		
		//draw handlers for active shape
    if (this.global_move && this.activ_shape>-1) {
			this.edtDot(this.context,'#cc0000',this.shapeBox[this.activ_shape][2],this.shapeBox[this.activ_shape][3]-this.yOffset,2+0.1*this.lineThickness);
			this.edtDot(this.context,'#cc0000',this.shapeBox[this.activ_shape][4],this.shapeBox[this.activ_shape][5]-this.yOffset,2+0.1*this.lineThickness);

			this.context.strokeStyle=this.currentColours[1];
			this.context.fillStyle = this.currentColours[1];
		}

    if (this.shape_x1>-1 && this.shape_x2==-1) draw_shape(this,this.global_add,this.shape_x1,this.shape_y1-this.yOffset,this.x,this.y-this.yOffset);
 		this.context.font=this.fontSizes[this.fontSizePos]+"px Arial";
		var loc_width,loc_height;
    if (this.qType!='menu' && this.allImagesLoaded) {
			loc_width = this.imglabelWidth;loc_height = this.imglabelHeight;
      
			//draw placeholders
			if (this.qmode!='edit') {
				for (i=0;i<this.pholderBox.length;i++) {
					if (typeof(this.pholderBox[i])!='undefined') {
						//drawing background (unanswered)
						this.context.fillStyle=this.currentColours[0];
						if (this.pholderBox[i][5]=='' && this.is_an_answer && this.qmode!='edit' && this.qmode!='script') this.context.fillStyle=this.currentColours[3];

						//selecting width and height
						if (this.pholderBox[i][3]=='text' ) {loc_width = this.labelWidthEffect;loc_height=this.labelHeightEffect;}

						//fill and strike background rectangle
						if (this.is_an_answer) this.context.fillRect(this.pholderBox[i][1]+0.5,this.pholderBox[i][2]+0.5,loc_width,loc_height);
						this.context.strokeRect(this.pholderBox[i][1]+0.5,this.pholderBox[i][2]+0.5,loc_width,loc_height);
					}
				}
			}
			this.context.fillStyle=this.currentColours[0]; //resetting colour
			
		  //edit box
			if (this.qmode=='edit' && this.active_box_id>-1) {
        loc_width = this.imglabelWidth;loc_height = this.imglabelHeight;
        if (this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][3]=='text') {
					loc_width = this.labelWidthEffect;
					loc_height = this.labelHeightEffect;
				}	
				var text_len = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].length;
				if (this.key_code=='39') this.edit_box_pos++; 				//arror right
				if (this.key_code=='37') this.edit_box_pos--; 				//arrow left
				if (this.key_code=='35') this.edit_box_pos=text_len; 	//end
				if (this.key_code=='36') this.edit_box_pos=0; 				//home	
				if (this.edit_box_pos<0) this.edit_box_pos=0;
				if (this.edit_box_pos>text_len) this.edit_box_pos=text_len;
				if (this.key_code==0 && this.char_code!='') {					//characters
					var temp_t = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(0,this.edit_box_pos)+this.char_code+this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(this.edit_box_pos);
					var metrics_temp = this.context.measureText(temp_t);
					this.answerBox[this.active_box_id][this.active_box_combo][2] = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4] = temp_t;
					this.edit_box_pos++;
				}
				if (this.key_code=='46') { //del
					var temp_t = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(0,this.edit_box_pos)+this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(this.edit_box_pos+1);
					this.answerBox[this.active_box_id][this.active_box_combo][2] = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4] = temp_t;
				}
				if (this.key_code=='8') { //backspace
					var temp_t = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(0,this.edit_box_pos-1)+this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4].substr(this.edit_box_pos);
					this.answerBox[this.active_box_id][this.active_box_combo][2] = this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4] = temp_t;
					this.edit_box_pos--;
				}
				
				this.char_code ='';
				this.key_code = 0;
			}      

			//scaling up the labelwidth
			this.labelWidthEffect = this.labelWidth;
			this.labelHeightEffect = this.labelHeight;
			for (i=0;i<this.answerBox.length;i++) {
				for (j=0;j<this.answerBox[i].length;j++) {
					if (typeof(this.answerBox[i][j])!='undefined') {
						var wrapTemp = this.wrapText(this.answerBox[i][j][2],this.labelWidthEffect);				
						if (typeof(wrapTemp)!='undefined') {
							if (wrapTemp[2] > this.labelWidthEffect) this.labelWidthEffect = wrapTemp[2]+8;
							if (wrapTemp[1] > this.labelHeightEffect) this.labelHeightEffect = wrapTemp[1]+4;
						}
					}
				}
      }
			//if (this.qmode=='edit' || this.qmode=='analysis') 
			{
				var tmpx = 5;
				var tmpy = 30-this.yOffset;
				var tmpw,tmph,tmpn = 0;
				for (i=0;i<this.answerBox.length;i++) {
					if (typeof(this.answerBox[i])!='undefined'){
						for (j=0;j<this.answerBox[i].length;j++) {
							if (typeof(this.answerBox[i][j])!='undefined' &&
									this.answerBox[i][j][5]<220 &&
									(this.labelType=='multiple' || j==0)) {
								var index = this.answerBox[i][j][0];
								var tmpw = this.labelWidthEffect;
								var tmph = this.labelHeightEffect;
								if (this.answerBox[i][j][1]=='image') {
									tmpw = this.imglabelWidth;							
									tmph = this.imglabelHeight;
								}
								ax = tmpx;
								if (this.answerBox[i][j][2]=='' && (this.qmode=='answer' || this.qmode=='script')) ax = -500;
								
								this.answerBox[i][j][7] = ax;
								this.answerBox[i][j][8] = tmpy;
								if (i!=this.drag_box_id || j!=this.drag_box_combo) {
									this.answerBox[i][j][5] = ax;
									this.answerBox[i][j][6] = tmpy;
									if (this.answerBox[i][j][10]==0) {
										this.pholderBox[i][j][1] = ax;
										this.pholderBox[i][j][2] = tmpy;
									}
								}
								if (!(this.answerBox[i][j][2]=='' && (this.qmode=='answer' || this.qmode=='script'))) {
									tmpx += this.i_spacex + tmpw;
									if (tmpn < tmph) tmpn = tmph;									
									if ((tmpx + tmpw)>220) {
										tmpx = 5;
										tmpy += tmpn + this.i_spacey;
										tmpn = 0;
									}
								}
							}
						}
					}
				}
			}
		
			for (i=this.answerBox.length-1;i>=0;i--) {
				for (j=this.answerBox[i].length-1;j>=0;j--) {
					if (typeof(this.answerBox[i][j])!='undefined' && !(this.drag_box_id==i && this.drag_box_combo==j) && !(this.mov_id==i && this.mov_combo==j)) {
						this.ql_redraw_box(i,j);
					}
				}
      }
			this.context.fillStyle=this.currentColours[0]; //resetting colour
			//redraw active label to have it on top
      if (this.active_box_id>-1) this.ql_redraw_box(this.active_box_id,this.active_box_combo);
			//redraw dragged shape to have it on top
			var drag_mix = this.drag_box_id+':'+this.drag_box_combo;
			var active_mix = this.active_box_id+':'+this.active_box_combo;
			var mov_mix = this.mov_id+':'+this.mov_combo;
			
      if (this.drag_box_id>-1 && drag_mix!=active_mix) this.ql_redraw_box(this.drag_box_id,this.drag_box_combo);
      //redraw animated shape to have it on top
      if (this.mov_id>-1 && mov_mix!=drag_mix && mov_mix!=active_mix) this.ql_redraw_box(this.mov_id,this.mov_combo);
						
			if (this.qmode=='edit' && this.active_box_id>-1 && this.active_box_id!=this.mov_id) {
        loc_width = this.imglabelWidth;loc_height = this.imglabelHeight;
        if (this.answerBox[this.active_box_id][this.active_box_combo][1]=='text') {
					loc_width = this.labelWidthEffect;
					loc_height= this.labelHeightEffect;
				}

				//draw handlers for active label
				this.context.strokeStyle='#cc0000';
				this.context.strokeRect(
					this.answerBox[this.active_box_id][this.active_box_combo][5]-this.lineThickness/2+0.5,
					this.answerBox[this.active_box_id][this.active_box_combo][6]-this.lineThickness/2+0.5,
					loc_width+this.lineThickness,
					loc_height+this.lineThickness);

				this.edtDot(
					this.context,'#cc0000',
					this.answerBox[this.active_box_id][this.active_box_combo][5]-this.lineThickness/2+0.5,
					this.answerBox[this.active_box_id][this.active_box_combo][6]-this.lineThickness/2+0.5,
					2.5+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#cc0000',
					this.answerBox[this.active_box_id][this.active_box_combo][5]-this.lineThickness/2+0.5,
					this.answerBox[this.active_box_id][this.active_box_combo][6]+loc_height+this.lineThickness/2+0.5,
					2.5+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#cc0000',
					this.answerBox[this.active_box_id][this.active_box_combo][5]+loc_width+this.lineThickness/2+0.5,
					this.answerBox[this.active_box_id][this.active_box_combo][6]-this.lineThickness/2+0.5,
					2.5+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#cc0000',
					this.answerBox[this.active_box_id][this.active_box_combo][5]+loc_width+this.lineThickness/2+0.5,
					this.answerBox[this.active_box_id][this.active_box_combo][6]+loc_height+this.lineThickness/2+0.5,
					2.5+0.1*this.lineThickness);
				this.context.strokeStyle=this.currentColours[1];
			}
			
			//cursor blink
			if (this.qmode=='edit' && this.active_box_id>-1) {
				this.edit_box_blink++;
				if (this.edit_box_blink>40) this.edit_box_blink=0;
				if (this.edit_box_blink>20) {
					var text_all = this.wrapText(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][4],this.labelWidthEffect)[0];
					var text_temp = '';
					if (this.edit_box_pos>0) text_temp = text_all.substr(0,this.edit_box_pos);
					var wrap_temp = text_temp.split('|');
					var text_part_line = wrap_temp.length-1;
					
					var text_part = wrap_temp[text_part_line]
					var text_full = text_all.split('|')[text_part_line];
										
					var metrics_part = this.context.measureText(text_part);
					var metrics_full = this.context.measureText(text_full);
					
					this.context.strokeStyle='#000000';					
				  this.context.beginPath();
					var temp_x = Math.round(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][1]+(this.labelWidthEffect-metrics_full.width)/2+metrics_part.width)-0.5;
					var temp_y = Math.round(this.fontSizes[this.fontSizePos]*text_part_line+this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2]+4)-0.5;
					this.context.moveTo(temp_x,temp_y);
					this.context.lineTo(temp_x,temp_y+this.fontSizes[this.fontSizePos]);
					this.context.stroke();
					this.context.strokeStyle=this.currentColours[1];
					
				}
			}
		}
    
    //buttons
    if (this.qmode=='edit') {
      if (this.buttonBox.length==0) this.ql_menuBuild();
        this.menuRebuild(this.context);
        
        this.context.fillStyle=this.currentColours[0];
        this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_bucket.png']][1]+2,this.buttonBox[this.buttonBoxNames['toolbar/ico_bucket.png']][2]+14,16,3);
        this.context.fillStyle=this.currentColours[1];
        this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_brush.png']][1]+2,this.buttonBox[this.buttonBoxNames['toolbar/ico_brush.png']][2]+14,16,3);
        this.context.fillStyle=this.currentColours[2];
        this.context.fillRect(this.buttonBox[this.buttonBoxNames['toolbar/ico_letter.png']][1]+2,this.buttonBox[this.buttonBoxNames['toolbar/ico_letter.png']][2]+14,16,3);

        this.panelOverColour = '';
        m = 0;
        //draw colourtable
        for (n=0;n<this.colorReference.length;n++) if (this.currentColours[0]==this.colorReference[n]) m = n;
        this.menuRebuild_panel(this.panelActiveParts,this.ql_panelBox,'toolbar/ico_bucket.png','toolbar/pan_colours.png',0,m);
        //draw linetable
        for (n=0;n<this.colorReference.length;n++) if (this.currentColours[1]==this.colorReference[n]) m = n;
        this.menuRebuild_panel(this.panelActiveParts,this.ql_panelBox,'toolbar/ico_brush.png','toolbar/pan_colours.png',0,m);
        //draw fontcolourtable
        for (n=0;n<this.colorReference.length;n++) if (this.currentColours[2]==this.colorReference[n]) m = n;
        this.menuRebuild_panel(this.panelActiveParts,this.ql_panelBox,'toolbar/ico_letter.png','toolbar/pan_colours.png',0,m);         
        //draw sizetable
        this.menuRebuild_panel(this.panelActiveParts,this.ql_panelBox,'toolbar/ico_size.png','toolbar/pan_sizes.png',1,this.fontSizePos);
        
        //display char size number on menu button
        var tp = this.panelActiveParts['toolbar/pan_sizes.png'][this.fontSizePos].split(',');
        var imgdata = menuImages['toolbar/pan_sizes.png'];
        var temp_but = this.buttonBox[this.buttonBoxNames['toolbar/ico_size.png']];
        this.context.drawImage(this.menu_img,imgdata.left+1*tp[0],imgdata.top+1*tp[1],18,18,(temp_but[1]*1-1),temp_but[2],18,18);

        //draw linetable
        this.menuRebuild_panel(this.panelActiveParts,this.ql_panelBox,'toolbar/ico_lines.png','toolbar/pan_lines.png',2,this.lineThickness-1);
    }
    //tooltip
		this.draw_limit = new Array(0,27,this.canvas.width-2,this.canvas.height-2);
    if (this.buttonOver!=-1 && this.buttonClicked!=1 && this.buttonClicked!=3 && this.buttonClicked!=5 && this.buttonClicked!=7 && this.buttonClicked!=9) this.tooltip_draw(this.context,this.buttonBox[this.buttonOver]);

    // border
    this.context.lineWidth = 1;
    this.context.strokeStyle='#7f9db9';  
		this.context.strokeRect(0.5,0.5,this.canvas.width-1,this.canvas.height-1); //border
	}
}
			 
function ql_mouseDragMove(e){
	var ev = e || window.event;
	if (ev.type=='keydown') {
		this.isShift = ev.shiftKey ? true : false;
		this.isCtrl = ev.ctrlKey ? true : false;
		this.ShiftChange = true;
	}
	if (ev.type=='keypress') { 
		this.key_code = ev.keyCode;
		this.char_code = String.fromCharCode(ev.charCode);
	}
	if (ev.type=='keyup') { 
		this.isShift = false;
		this.ShiftChange = true;
		this.isCtrl = false;
		this.key_code = ev.keyCode;
		this.char_code = String.fromCharCode(ev.charCode);
	}		
	if (ev.type=='mousemove') {
		this.canv_rect = this.canvas.getBoundingClientRect();
		this.loc_lft = this.canv_rect.left;
		this.loc_top = this.canv_rect.top;
		this.x = ev.clientX - this.loc_lft;
		this.y = ev.clientY - this.loc_top;
	}	
	
	//dragging labels handlers
	if (typeof(this.active_box_handler)!='undefined' && this.active_box_handler!=-1) {
		var dim = new Array(this.answerBox[this.active_box_id][this.active_box_combo][5],this.answerBox[this.active_box_id][this.active_box_combo][6],this.answerBox[this.active_box_id][this.active_box_combo][5]+this.labelWidthEffect,this.answerBox[this.active_box_id][this.active_box_combo][6]+this.labelHeightEffect);
		if (this.active_box_handler==1 || this.active_box_handler==4) {
			this.answerBox[this.active_box_id][this.active_box_combo][5] = this.x;
			this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][1] = this.x;
		}		
		if (this.active_box_handler==1 || this.active_box_handler==2) {
			this.answerBox[this.active_box_id][this.active_box_combo][6] = this.y;
			this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2] = this.y;
		}
		if (this.active_box_handler==1){
			dim[0] = this.x;
			dim[1] = this.y;			
		}
		if (this.active_box_handler==2){
			dim[2] = this.x;
			dim[1] = this.y;			
		}
		if (this.active_box_handler==3){
			dim[2] = this.x;
			dim[3] = this.y;			
		}
		if (this.active_box_handler==4){
			dim[0] = this.x;
			dim[3] = this.y;			
		}
		this.labelWidth  = dim[2]-dim[0];
		this.labelHeight = dim[3]-dim[1];
	}
	
	if (this.dragging && this.drag_box_id>-1){ //this.dragging
		//new position of dragged shape
		if (this.qmode=='answer' || this.global_move) {
			this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.x - this.sub_x;
			this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.y - this.sub_y;
		}
	
		//limits
		this.draw_limit = new Array(1,(26-this.yOffset),this.canvas.width-this.labelWidthEffect-2,this.canvas.height-this.labelHeightEffect-2);
		if (this.qmode=='edit') this.draw_limit = new Array(0,26,this.canvas.width-this.labelWidthEffect-2,this.canvas.height-this.labelHeightEffect-2);
		

		if (this.answerBox[this.drag_box_id][this.drag_box_combo][5]<this.draw_limit[0]) this.answerBox[this.drag_box_id][this.drag_box_combo][5]=this.draw_limit[0];
		if (this.answerBox[this.drag_box_id][this.drag_box_combo][6]<this.draw_limit[1]) this.answerBox[this.drag_box_id][this.drag_box_combo][6]=this.draw_limit[1];
		if (this.answerBox[this.drag_box_id][this.drag_box_combo][5]>this.draw_limit[2]) this.answerBox[this.drag_box_id][this.drag_box_combo][5]=this.draw_limit[2];
		if (this.answerBox[this.drag_box_id][this.drag_box_combo][6]>this.draw_limit[3]) this.answerBox[this.drag_box_id][this.drag_box_combo][6]=this.draw_limit[6];
		
		if (this.qmode=='edit'){			
			this.pholderBox[this.answerBox[this.drag_box_id][this.drag_box_combo][0]][1] = this.answerBox[this.drag_box_id][this.drag_box_combo][5];
			this.pholderBox[this.answerBox[this.drag_box_id][this.drag_box_combo][0]][2] = this.answerBox[this.drag_box_id][this.drag_box_combo][6];
			
		}
	}	else { //change of cursor
    var drag_box_old = this.drag_box_id+':'+this.drag_box_combo;
		this.drag_box_id = -1; 
		this.drag_box_combo = -1;	
		if (this.qmode!='analysis' && this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
			var over_object = false;
			
      for (i=0;i<this.answerBox.length;i++) {
				for (j=0;j<this.answerBox[i].length;j++) {
					if (typeof(this.answerBox[i][j])!='undefined' && (this.labelType == 'multiple' || this.answerBox[i][j][10]==0)) {

						if (this.answerBox[i][j][1]=='image') {
							if (this.testWithin(this.x,this.y,this.answerBox[i][j][5],this.answerBox[i][j][6],this.imglabelWidth,this.imglabelHeight)==true) {
								over_object = true;
								if (this.drag_box_id==-1 || this.answerBox[i][j][3]!='') {
									this.drag_box_id = i;
									this.drag_box_combo = j;
									}
							}
						}
						if (this.answerBox[i][j][1]=='text') {
							if (this.testWithin(this.x,this.y,this.answerBox[i][j][5],this.answerBox[i][j][6],this.labelWidthEffect,this.labelHeightEffect)==true) {
								over_object = true;
								if (this.drag_box_id==-1 || this.answerBox[i][j][3]!='') {
									this.drag_box_id = i;
									this.drag_box_combo = j;
									}
							}
						}
					}
				}
			}	
			if (drag_box_old != this.drag_box_id+':'+this.drag_box_combo && this.qmode=='script') this.redraw_once = true;

      //test for buttons
      var buttonTest = -1;
      for (var i=0;i<this.buttonBox.length;i++) {
        this.buttonBox[i][5] = this.buttonBox[i][6];
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
        this.ql_redraw_canvas;
      }

      //test for panels
      var panelOptionTest = -1;
      this.panelOver = -1;
      
      if (this.buttonClicked>-1 && typeof this.ql_panelBox[this.buttonClicked] != 'undefined') {
      var tmp_but=-1,tmp_pan=-1;
      if (this.testWithin(this.x,this.y,this.ql_panelBox[this.buttonClicked][3],this.ql_panelBox[this.buttonClicked][4],this.ql_panelBox[this.buttonClicked][5],this.ql_panelBox[this.buttonClicked][6])==true) {
        tmp_but = this.buttonBox[this.buttonClicked];
        if (typeof this.ql_panelBox[this.buttonClicked][2]!='undefined') tmp_pan = this.ql_panelBox[this.buttonClicked][2];
        this.panelOver=this.buttonClicked;
        over_object = true;
        this.drag_box_id = -1;
				this.drag_box_combo = -1;
				var test_width = 19;
				if (tmp_pan=='toolbar/pan_sizes.png') test_width = 22;
				if (tmp_pan=='toolbar/pan_lines.png') test_width = 130;
        for(i=0;i<this.panelActiveParts[tmp_pan].length;i++) {
          var tp = this.panelActiveParts[tmp_pan][i].split(',');
          if (this.testWithin(this.x,this.y,tmp_but[1]+1*tp[0]+0.5,tmp_but[2]+25+1*tp[1]+0.5,test_width,20)==true) panelOptionTest=i;
          }
        }
      }
      if (this.panelOptionOver != panelOptionTest) {
        this.panelOptionOver = panelOptionTest;
        this.redraw_once = true;
        this.ql_redraw_canvas;
      }
		
			
      var cur = 'default';
			if (over_object) cur = 'pointer';
			if (this.global_move && this.activ_shape>-1 && this.y>28) cur = 'move';
			if (this.active_box_handler!=-1) cur = 'move';
 			if (this.global_erase && this.activ_shape>-1 && this.y>28) cur = 'url(/js/images/cur_erase.cur), default';//cur_cross
      if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0]=='toolbar/ico_help.png') cur = 'help';
      e.target.style.cursor = cur;
		}
	}

}

function ql_mouseDragDown(e){
	this.x = e.clientX - this.canv_rect.left;
	this.y = e.clientY - this.canv_rect.top;
	if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
		if (this.drag_box_id>-1) {
			this.sub_x = this.x - this.answerBox[this.drag_box_id][this.drag_box_combo][5];
			this.sub_y = this.y - this.answerBox[this.drag_box_id][this.drag_box_combo][6];
		}
		if (this.panelOptionOver==-1) this.dragging = true;	
	}
  
  this.activ_shape_move = this.activ_shape;
  this.activ_shape_x = this.x;
  this.activ_shape_y = this.y;
	
	//test for label handlers
	if (this.active_box_id>-1 && this.answerBox[this.active_box_id][this.active_box_combo][1]=='text') {
		var tt  = 2.5+0.1*this.lineThickness;
		var tx1 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][1]-this.x)<tt);
		var tx2 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][1]+this.labelWidthEffect-this.x)<tt);
		var ty1 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2]-this.y)<tt);
		var ty2 = (Math.abs(this.pholderBox[this.answerBox[this.active_box_id][this.active_box_combo][0]][2]+this.labelHeightEffect-this.y)<tt);
		if (tx1 && ty1) this.active_box_handler = 1;
		if (tx2 && ty1) this.active_box_handler = 2;
		if (tx2 && ty2) this.active_box_handler = 3;
		if (tx1 && ty2) this.active_box_handler = 4;
	}
}

function ql_mouseDragUp(){
	this.active_box_id = this.drag_box_id;
	this.active_box_combo = this.drag_box_combo;
	this.dragging = false;
	this.active_box_handler = -1;
	
	if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0]=='toolbar/ico_help.png') window.open('/help/staff/index.php?id=60');

  //erase shape
  if (this.global_erase && this.activ_shape>-1) {
    this.shapeBox.splice(this.activ_shape,1);
  }
  this.activ_shape_move = this.activ_shape = -1;

  if (this.drag_box_id>-1 && this.qmode=='edit' && this.answerBox[this.drag_box_id][this.drag_box_combo][5]<220) {
		this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
		this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8]; 
		this.mov_x = this.x - this.sub_x;
		this.mov_y = this.y - this.sub_y;
	}

  if (this.drag_box_id>-1 && this.labelType == 'multiple'){
		var index = this.answerBox[this.drag_box_id][this.drag_box_combo][0];
		var combo_nr = 0;
		if (this.answerBox[this.drag_box_id][this.drag_box_combo][5]>=220 && this.answerBox[this.drag_box_id][this.drag_box_combo][7]<220) {
			for (i=0;i<this.answerBox.length;i++) {
				for (j=0;j<this.answerBox[i].length;j++) {
					if (typeof(this.answerBox[i][j])!='undefined' && typeof(this.answerBox[i][j][0])!='undefined' && index == this.answerBox[i][j][0] && combo_nr < this.answerBox[i][j][10]) combo_nr = this.answerBox[i][j][10];
					}
				}
			//duplicate dragged with new combo_nr
			combo_nr++;
			var that_box = this.answerBox[this.drag_box_id][this.drag_box_combo].slice(0);
			that_box[10] = combo_nr;
			//reset copy
			that_box[5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
			that_box[6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8];
			this.answerBox[this.drag_box_id][combo_nr] = that_box;
		}
		if (this.answerBox[this.drag_box_id][this.drag_box_combo][5]<220 && this.answerBox[this.drag_box_id][this.drag_box_combo][7]<220) {
			var duplicate = false;
			for (i=0;i<this.answerBox.length;i++) {
				for (j=0;j<this.answerBox[i].length;j++) {
					if (typeof(this.answerBox[i][j])!='undefined' && typeof(this.answerBox[i][j][0])!='undefined' && index == this.answerBox[i][j][0] && this.answerBox[this.drag_box_id][this.drag_box_combo][5] == this.answerBox[i][j][5] && this.drag_box_id != i && combo_nr>1) duplicate = true;
				}
			}
			if (duplicate) {
				this.answerBox[this.drag_box_id][this.drag_box_combo].splice(drag_box_combo,1);
				this.drag_box_id = this.active_box_id = this.mov_id = -1;
				this.drag_box_combo = this.active_box_combo = this.mov_combo = -1;			
			}
		}
	}
	
  //this.dragging shapes
  if (this.drag_box_id>-1 && this.qmode=='answer') {
		//testing against the position of placeholders
		var dest_box=-1;
		for (i=0;i<this.pholderBox.length;i++) {
			if (typeof(this.pholderBox[i])!='undefined') {
				var loc_width = this.imglabelWidth,loc_height = this.imglabelHeight;
				if (this.pholderBox[i][3]=='text' ) {loc_width = this.labelWidthEffect;loc_height=this.labelHeightEffect;}
				if (this.testWithin(this.x,this.y,this.pholderBox[i][1],this.pholderBox[i][2],loc_width,loc_height)==true) dest_box = i;
			}
		}
		
		this.mov_id = this.drag_box_id;
		this.mov_combo = this.drag_box_combo;
		this.mov_x = this.x - this.sub_x;
		this.mov_y = this.y - this.sub_y;
		if (dest_box>-1 && this.answerBox[this.drag_box_id][this.drag_box_combo][1]==this.pholderBox[dest_box][3]) {
      //removing any shape previously put into that position
      for (i=0;i<this.answerBox.length;i++) {
				for (j=0;j<this.answerBox[i].length;j++) {
					if (typeof(this.answerBox[i][j])!='undefined' && typeof(this.pholderBox[dest_box])!='undefined' && this.answerBox[i][j][5] == this.pholderBox[dest_box][1] && this.answerBox[i][j][6] == this.pholderBox[dest_box][2] && i != this.drag_box_id) {
						this.mov_id = i;
						this.mov_combo = j;
						this.mov_x = this.answerBox[i][j][5];
						this.mov_y = this.answerBox[i][j][6];						
						this.answerBox[i][j][5] = this.answerBox[i][j][7];
						this.answerBox[i][j][6] = this.answerBox[i][j][8];						
						this.answerBox[i][j][9] = '';
					}
				}
      }
      //is it correctly dropped label
      if (this.answerBox[this.drag_box_id][this.drag_box_combo][2]==this.pholderBox[dest_box][4]) {
        this.answerBox[this.drag_box_id][this.drag_box_combo][9]='t'
      }else{
        this.answerBox[this.drag_box_id][this.drag_box_combo][9]='f'
			}
      this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.pholderBox[dest_box][1];
      this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.pholderBox[dest_box][2];
    }
    else {
      //label dropped outside and target is sent back
      this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
      this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8];
      this.answerBox[this.drag_box_id][this.drag_box_combo][9] = '';
    }
  }
  //'erase' label
  if (this.global_erase && this.drag_box_id>-1) {
    this.answerBox[this.drag_box_id][this.drag_box_combo][5] = this.answerBox[this.drag_box_id][this.drag_box_combo][7];
    this.answerBox[this.drag_box_id][this.drag_box_combo][6] = this.answerBox[this.drag_box_id][this.drag_box_combo][8];
    this.answerBox[this.drag_box_id][this.drag_box_combo][9] = '';
  }
    
	if (this.buttonBox.length>0) {
		//test for buttons
		this.buttonClicked = -1;
		//release buttons without set
		for (i=0;i<this.buttonBox.length;i++) {
			if (this.buttonBox[i][7]=='') this.buttonBox[i][5] = this.buttonBox[i][6] = 0;
		} 
		
		if (this.buttonOver != -1) {
			//testing button sets
			var butSet = this.buttonBox[this.buttonOver][7];
			for (i=0;i<this.buttonBox.length;i++) {
				if (butSet == this.buttonBox[i][7]) this.buttonBox[i][5] = this.buttonBox[i][6] = 0;
			}
			
			//double button?
			var j=i=this.buttonOver;
			if (this.buttonBox[i][0]=='toolbar/ico_drop.png') i=j-1;
			if (i<this.buttonBox.length-1 && this.buttonBox[i+1][0]=='toolbar/ico_drop.png') j=i+1;
			this.buttonOver = i;
			this.buttonBox[j][5] = 2;
			this.buttonBox[this.buttonOver][5] = this.buttonBox[this.buttonOver][6] = 2;
			this.buttonClicked = this.buttonOver;
		}
		
		//drawing the line, bobble or arrow
		if (this.global_add != '') {
			if (this.shape_x1==-1) {
				this.shape_x1 = this.x;
				this.shape_y1 = this.y;
			} else {
				this.shape_x2 = this.x;
				this.shape_y2 = this.y;
		
				this.shapeBox.push(new Array(this.shapeBox.length,this.global_add,this.shape_x1,this.shape_y1,this.shape_x2,this.shape_y2));
				this.buttonBox[this.buttonBoxNames['toolbar/ico_'+this.global_add+'.png']][5] = 0;
				this.buttonBox[this.buttonBoxNames['toolbar/ico_'+this.global_add+'.png']][6] = 0;
				this.global_add = '';
				this.shape_x1 = this.shape_y1 = this.shape_x2 = this.shape_y2 = -1;
			}
		}
		//button effects
		this.global_erase = false;
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_erase.png']][6]==2) this.global_erase = true;
		this.global_move = false;
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_resize.png']][6]==2) this.global_move = true;
		
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_single.png']][6]==2) this.labelType = 'single';
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_multiple.png']][6]==2) {
			this.labelType = 'multiple';
		}

		//state of drawing buttons
		this.global_add = '';
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_line.png']][6]==2) this.global_add = 'line';
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_bobble.png']][6]==2) this.global_add = 'bobble';
		if (this.buttonBox[this.buttonBoxNames['toolbar/ico_arrow.png']][6]==2) this.global_add = 'arrow';
  }
  //this.panelOver
  if (this.panelOver==1 && this.panelOverColour!='') this.currentColours[0] = this.panelOverColour;
  if (this.panelOver==3 && this.panelOverColour!='') this.currentColours[1] = this.panelOverColour;
  if (this.panelOver==5 && this.panelOverColour!='') this.currentColours[2] = this.panelOverColour;
  if (this.panelOver==7) this.fontSizePos = this.panelOptionOver;
  if (this.panelOver==9) this.lineThickness = this.panelOptionOver+1;

	this.redraw_once = true;
	this.ql_redraw_canvas;
  this.ql_ReturnInfo();
}

function ql_ReturnInfo() {
  var questions_correct = 0;
  var questions_incorrect = 0;
  var questions_total = 0;
  var questions_result = '';
  var answer_result = '';
  var temp_answ = new Array();

	if (this.qmode == 'answer') {
		for (i=0;i<this.pholderBox.length;i++) {
			if (typeof(this.pholderBox[i])!='undefined' && this.pholderBox[i][4] != "" && this.pholderBox[i][1]>220) {
				temp_answ[this.pholderBox[i][4]] = this.pholderBox[i][1]+','+this.pholderBox[i][2];
				questions_total++;
				}
		}
		for (i=0;i<this.answerBox.length;i++) {
			for (j=0;j<this.answerBox[i].length;j++) {
				if (typeof(this.answerBox[i][j])!='undefined') {
					if (this.answerBox[i][j][9]=='t') questions_correct++;
					if (this.answerBox[i][j][9]=='f') questions_incorrect++;
					if (this.answerBox[i][j][9]=='t' || this.answerBox[i][j][9]=='f') answer_result+=this.answerBox[i][j][5]+'$'+(this.answerBox[i][j][6]-25+this.yOffset)+'$'+this.answerBox[i][j][2]+'$'+this.answerBox[i][j][9]+'$';
				}
			}
		}  
		var marks_max = this.marks_per_correct * questions_total;
		var marks_total = this.marks_per_correct * questions_correct + this.marks_per_incorrect * questions_incorrect;
		if (this.marking_method != 'Mark per Option') {
			marks_total = this.marks_per_incorrect;
			marks_max = this.marks_per_correct;
			if (questions_correct == questions_total) marks_total = this.marks_per_correct;
		}
		questions_result = marks_total+'$'+marks_max+';'+answer_result;
		var target_field = document.getElementById('q'+this.q_Num);
	}
	
	if (this.qmode == 'edit') {
		questions_result += parseInt(hexifycolour(this.currentColours[1]).substr(1), 16)+';';
		questions_result += this.lineThickness +';';
		questions_result += parseInt(hexifycolour(this.currentColours[0]).substr(1), 16)+';';
		questions_result += this.fontChoices[this.fontSizePos]+';';
		questions_result += parseInt(hexifycolour(this.currentColours[2]).substr(1), 16)+';';
		questions_result += this.labelWidth +';';
		questions_result += this.labelHeight +';';
		questions_result += this.imglabelWidth +';';
		questions_result += this.imglabelHeight +';';
		questions_result += this.labelType +';';
		questions_result += this.qType +';';

		for (i=0;i<this.answerBox.length;i++) {
			if (this.labelType=='single') {
				if (this.answerBox[i][0][2]!=''){
					questions_result += i+'$';
					questions_result += this.answerBox[i][0][10]+'$';
					questions_result += this.answerBox[i][0][5]+'$';
					questions_result += this.answerBox[i][0][6]+'$';
					questions_result += this.answerBox[i][0][2]+'|';
				}
			}else{
				for (j=0;j<this.answerBox[i].length;j++) {
					if (this.answerBox[i][j][2]!=''){
						questions_result += i+'$';
						questions_result += this.answerBox[i][j][10]+'$';
						questions_result += this.answerBox[i][j][5]+'$';
						questions_result += this.answerBox[i][j][6]+'$';
						questions_result += this.answerBox[i][j][2]+'|';
					}
				}
			}
		}
		questions_result += ';';
		var target_field = document.getElementById('points'+this.q_Num);
	}
	if (questions_result!='' && target_field) target_field.value = questions_result;	
}

function rql(num) {
	this.setUpLabelling 					= setUpLabelling;
	this.ql_draw_box 							= ql_draw_box;
	this.ql_redraw_box 						= ql_redraw_box;
	this.ql_panelBoxBuild 				= ql_panelBoxBuild;
	this.ql_menuBuild 						= ql_menuBuild;
	this.ql_redraw_canvas 				= ql_redraw_canvas;
	this.ql_ReturnInfo 						= ql_ReturnInfo;
	this.ql_mouseDragMove 				= ql_mouseDragMove;
	this.ql_mouseDragDown 				= ql_mouseDragDown;
	this.ql_mouseDragUp 					= ql_mouseDragUp;
	this.def_colour_panel_parts 	=	def_colour_panel_parts;

	this.hexifycolour=hexifycolour;
	this.textHeight=textHeight;
	this.wrapText=wrapText;
	this.fillWrappedText = fillWrappedText;
	this.findPos=findPos;
	this.testWithin=testWithin;
	this.edtDot=edtDot;
	this.lineDraw=lineDraw;
	this.ellipseDraw=ellipseDraw;
	this.rectDraw=rectDraw;
	this.polyDrawH=polyDrawH;
	this.menuBuild_icons=menuBuild_icons;
	this.menuRebuild=menuRebuild;
	this.menuRebuild_panel=menuRebuild_panel;
	this.button_test=button_test;
	this.build_msgbox=build_msgbox;
	this.tooltip_draw=tooltip_draw;
	this.combo_scope = combo_scope;

	this.test; 
	this.x,this.y,this.z,this.sub_x,this.sub_y,this.m;
	this.i,this.j;
	
	this.scale_i = 1;                          	//label image scale
	this.drag_box_id = -1;                      //index of box beeing dragged
	this.drag_box_combo = -1;                      
	this.active_box_id = -1;                    //index of box beeing active
	this.active_box_combo = -1;                    
  this.mov_id = -1;
  this.mov_combo = -1;
  this.mov_x=0;
  this.mov_y=0;
	this.active_box_handler=-1;
  this.menu_ready = 1;
	this.edit_box_blink = 0;
	this.edit_box_pos = 0;
	this.key_code = 0;
	this.char_code = ''
	this.i_spacex = 5;
	this.i_spacey = 5;//11;

  this.nikotest = 1;

	this.allImagesLoaded = false;
	this.max_num_images = 0;
	this.pholderBox = new Array(); 			      // label no. that's correct answer for each placeholder
                                            // distractor placeholders have answer of -1
                                            // sublevels of this keep all the placeholder data
	this.answerBox = new Array(); 			      // sublevels of this keep all the label data
  this.shapeBox = new Array();            	// sublevels of this keep all the lines/arrows/bobbles data
  this.buttonBox = new Array();             // sublevels of this keep all the buttons data
  this.ql_panelBox = new Array();           // sublevels of this keep the panels data
  this.buttonBoxNames = new Array();      	// transcription of button names into its index in ButtonBox (?)
  this.buttonClicked = -1;                  // index of the button that was clicked
  this.buttonOver =-1;                      // index of the button the mouse is over
  this.panelOptionOver =-1;                 // index of the option on panel the mouse is over
  this.panelOver =-1                        // index of the panel the mouse is over
  this.panelOverColour = '';
  this.colorReference = new Array();
  this.panelActiveParts = new Array();      // array of positions panel's active elements
  this.global_edit = false;
  this.global_erase = false;
  this.shape_x1 = this.shape_y1 = this.shape_x2 = this.shape_y2 = -1  // temporary params of a new line/arrow/bobble
  this.global_add = '';
  this.global_move = false;
  this.activ_shape = this.activ_shape_move = this.activ_shape_x = this.activ_shape_y = -1;

  //defining panel's active parts
  //toolbar/pan_colours.png
	this.def_colour_panel_parts();
  
	this.panelActiveParts.push('toolbar/pan_sizes.png');
  this.panelActiveParts['toolbar/pan_sizes.png'] = new Array();
  for(i=0;i<7;i++) this.panelActiveParts['toolbar/pan_sizes.png'][i] = 3+','+(i*19+3);
  //'toolbar/pan_lines.png
  this.panelActiveParts.push('toolbar/pan_lines.png');
  this.panelActiveParts['toolbar/pan_lines.png'] = new Array();
  for(i=0;i<7;i++) this.panelActiveParts['toolbar/pan_lines.png'][i] = 3+','+(i*19+3);

	this.labelInstanceDepth = new Array();  // depth new instances are created on inside each labelGroup clip
	this.labelTxt = new Array(); 			      // stores text on each label
	this.labelTypes = new Array(); 			    // is label a "text" or "image" label?
	this.imageNames = new Array(); 			    // names of images on each label
	this.imageDimensions = new Array(); 		// individual dimensions of draggable images

	this.labelCoords = new Array(); 			  // coords for each label
	this.comboCoords = new Array(); 			  // coords for comboboxes - used temporarily in setting them up

	this.distractorTxt = new Array(); 		  // distractors in comboboxes
	this.depthSwapperLabel = new Array(); 	// selected labels are swapped with this clip so label will be on top 
																					// of all others in same group
	this.qType = "label"; 					        // draggable label ("label"), drop down menu ("menu")
	this.labelType = "single"; 				      // are labels unique or repeated ("single" / "multiple")?
	this.yOffset ; 						              // coords of everything made in label_add.swf include toolbar 
                                          // this need to be removed as image here is loaded with this.y coord = 0
  this.is_an_answer = false;
  this.q_Num;
	this.doorId;
  this.slow_speed = 7;                    //parameter of slowing down speed
	
	this.currentColours = Array('#FFFFFF','#3F3F3F','#000000','#FF0000'); // fill, line, text, unanswered colours
	this.lineThickness  = 1; 									                            // current thickness of borders around draggable labels and manually drawn lines / arrows (in pixels) 
	this.fontChoices    = Array(9, 10, 11, 12, 14, 16, 18); 		          // font size in drop down menu
	this.fontSizes  = Array(11, 12, 14, 16, 18, 20, 22); 	            		// font size equivalent in Flash (not standard sizes)
	this.fontSizePos    = 1; 									                            // current font size for labels (index from array above);
  this.draw_limit = new Array(); 																				//used to limit polygon, ellipse and sqare positions
	this.dragging = false;
	this.redraw_once = false;
	this.gen_img;
	this.menu_img;
	this.gen_img_loaded = false;
	this.menu_img_loaded = false;
	this.loc_lft = this.loc_top = 0;
  this.canvas;
	this.context;
  this.canv_rect;
  this.marks_per_correct = 1;
  this.marks_per_incorrect = 0;
  this.marking_method = 'Mark per Option';
  this.qmode;
	this.char_labels;
	this.imglabelWidth;
	this.imglabelHeight;
}