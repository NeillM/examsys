function setUpLabelling(num, flashId, lang, image, config, answer, extra, colour, mode) {
	console.log('>>> setUpLabelling');
	this.canvas = document.getElementById('canvas'+num);
	this.canv_rect = this.canvas.getBoundingClientRect();
  
	if (this.canvas && this.canvas.getContext){
		this.canvas.onmouseup   = this.ql_mouseDragUp.bind(this);
		this.canvas.onmousedown = this.ql_mouseDragDown.bind(this);
		this.canvas.onmousemove = this.ql_mouseDragMove.bind(this);
		this.canvas.tabIndex 		= 1000; //force keyboard events
		this.canvas.onkeydown   = this.ql_mouseDragMove.bind(this);
		this.canvas.onkeyup     = this.ql_mouseDragMove.bind(this);
		this.canvas.onkeypress  = this.ql_mouseDragMove.bind(this);
		this.intervalID = window.setInterval(this.ql_redraw_canvas.bind(this), 10);
		//this.ql_redraw_canvas();
	}
	if (this.canvas && !this.canvas.getContext){
		alert ('Canvas not supported');
	}

	if (this.canvas && this.canvas.getContext){
		this.context = this.canvas.getContext('2d');
		this.context.lineWidth = 1;

		//---------- num, 
 		console.log('num: '+num);
		this.q_Num = num;
		//---------- flashId, 
 		console.log('flashId: '+flashId);
		//---------- lang, 
 		console.log('lang: '+lang);
		//---------- image,
 		console.log('image: '+image);
		
		this.gen_img = new Image();  
		function gen_img_onload() {
			//console.log (this.redraw_once,'******',this.gen_img);        
			this.redraw_once = true;
      this.ql_redraw_canvas;        
		}
		this.gen_img.onload = gen_img_onload.bind(this);
		this.gen_img.src = '/media/'+image; 
    //console.log ('******',this.gen_img);
    
		//---------- mode 
		console.log('mode: '+mode);
		if (mode=='edit') this.yOffset = 0;
		if (mode=='answer') this.yOffset = 25;
    this.qmode = mode;
		
		//---------- config, 
		console.log('config: '+config);

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
    ////console.log(new Array(this.fontSizePos,existingInfo[3]));

		//4144959;1;16777215;10;0;100;19;0;0;single;label;0$0$264$82$label a|1$0$365$185$label b|2$0$5$60$|3$0$110$60$|4$0$5$90$|5$0$110$90$|6$0$5$120$|7$0$110$120$|8$0$5$150$|9$0$110$150$|10$0$5$180$|11$0$110$180$|12$0$5$210$|13$0$110$210$|14$0$5$240$|15$0$110$240$|16$0$5$270$|17$0$110$270$|18$0$5$300$|19$0$110$300$|;
		this.currentColours[2]	= existingInfo[4];	                        // text colour
		this.labelWidth			    = Number(existingInfo[5]);					        // text label width
		this.labelWidthEffect   = this.labelWidth;
		this.labelHeight			  = Number(existingInfo[6]); 					        // text label height
		this.labelHeightEffect  = this.labelHeight;
		this.imglabelWidth		  = Number(existingInfo[7]);					        // image label width
		this.imgLabelHeight		  = Number(existingInfo[8]);					        // image label height
		this.labelType			    = existingInfo[9];							            // single/multiple
		this.qType				      = existingInfo[10];							            // label/menu
		this.existingLabelInfo 	= existingInfo[11];						 				      // one label?
		if (typeof(existingInfo[11])!='undefined') this.existingLabelInfo = existingInfo[11].split("|"); // divides each label
		//console.log(existingInfo,this.labelWidthEffect);
		
    //reading lines/arrows/bobbles
    for (i=12; i<existingInfo.length; i++) {
			if (existingInfo[i]!='') {
				var shapeTemp = existingInfo[i].split("$");
				this.shapeBox.push(shapeTemp);
			}
    }
    ////console.log(this.shapeBox);

    //colours recalc
    ////console.log(this.currentColours);
    for (i=0;i<this.currentColours.length;i++) this.currentColours[i] = hexifycolour(this.currentColours[i]);
    ////console.log(this.currentColours);

		this.imagesLoaded 	= 0;
    if (typeof(this.existingLabelInfo)!='undefined') {
			for (i=0; i<this.existingLabelInfo.length; i++) {
				if (this.existingLabelInfo[i]!='') {
					var myLabelInfo = this.existingLabelInfo[i].split("$"); //divides each bit of info about label
					var mli_index = Number(myLabelInfo[0]);                	//index
					var mli_combo = Number(myLabelInfo[1]);         				//combo indicator?  >0
					var mli_pos_x = Number(myLabelInfo[2]);          				//pos_x
					var mli_pos_y = Number(myLabelInfo[3]);          				//pos_y
					var mli_answr = myLabelInfo[4];                       	//answer

					////console.log('myLabelInfo');

					var myLabelType = "text"; // text or image label?
					if (mli_answr.indexOf('.jpeg') != -1 || mli_answr.indexOf('.jpg') != -1 || mli_answr.indexOf('.png') != -1 || mli_answr.indexOf('.gif') != -1) myLabelType = "image";        

					if (mli_answr != "" && mli_combo==0) {
						this.pholderBox.push(mli_index);
						this.pho_index = this.pholderBox.length-1;
						//updating this.pholderBox array
						this.pholderBox[this.pho_index]    = new Array ();
						this.pholderBox[this.pho_index][0] = mli_index;   							//index
						if (mli_pos_x>220) 
							this.pholderBox[this.pho_index][1] = mli_pos_x;     					//pos_x
						else
							this.pholderBox[this.pho_index][1] = -500;					

						this.pholderBox[this.pho_index][2] = mli_pos_y - this.yOffset;  //pos_y
						this.pholderBox[this.pho_index][3] = myLabelType;            		//type: text/image
						if (myLabelType=='image') {
							var mli_answr_label = mli_answr.split("~");
							this.pholderBox[this.pho_index][4] = mli_answr_label[0];	  	//answer ie. 'beetle3.png' from 'beetle3.png~80~75'
						}
						else {
						this.pholderBox[this.pho_index][4] = mli_answr;					      	//answer ie. 'spider'
						}
						this.pholderBox[this.pho_index][5] = '';	                      //corectness
						//console.log(this.pholderBox);

						this.answerBox.push(mli_index);
						this.ans_index = this.answerBox.length-1;
						this.answerBox[this.ans_index]    = new Array ();
						this.answerBox[this.ans_index][0] = mli_index;								  //index
						this.answerBox[this.ans_index][1] = myLabelType;								//type: text/image
						this.answerBox[this.ans_index][2] = mli_answr;								  //label
						this.labelTxt.push(mli_answr);
						this.answerBox[this.ans_index][3] = this.answerBox[this.ans_index][4] = ''; //empty for non-image
						if (myLabelType=='image') {
							var existingImageInfo = myLabelInfo[4].split("~");
							this.answerBox[this.ans_index][2] = existingImageInfo[0];	    //filename
							this.max_num_images++;
							this.answerBox[this.ans_index][3] = existingImageInfo[1];	    //image oryginal width
							this.answerBox[this.ans_index][4] = existingImageInfo[2];	    //image oryginal height
							}
						this.answerBox[this.ans_index][5] = mli_pos_x;	                //pos_x
						this.answerBox[this.ans_index][6] = mli_pos_y - this.yOffset;	  //pos_y
						this.answerBox[this.ans_index][7] = mli_pos_x;	                //initial pos_x
						this.answerBox[this.ans_index][8] = mli_pos_y - this.yOffset;	  //initial pos_y
						this.answerBox[this.ans_index][9] = '';	                        //corectness
						//this.answerBox[mli_index][10] = 0;	                          //image instance
						//console.log(this.answerBox);

						////console.log(this.pholderBox);
						////console.log(this.answerBox);
						
					}
				}
			}
		}

    ////console.log('1>>');
    ////console.log('this.answerBox');

		//scaling?
		var scale_x,scale_y;
		if (this.imglabelWidth>200) scale_x=200/this.imglabelWidth;
		if (this.imgLabelHeight>200) scale_y=200/this.imgLabelHeight;
		this.scale_i = scale_x;
		if (this.scale_i<scale_y) this.scale_i = scale_y;
		
		
    //calculating the image boxes
		var i_spacex = 8;
		var i_spacey = 5;
    temp_x = i_spacex;
		temp_y = i_spacey + 25 - this.yOffset;
    ////console.log('this.answerBox.length');
    ////console.log(this.answerBox);
		for (i=0;i<this.answerBox.length;i++) {
      //alert(i+ this.answerBox[i][1]);
			if (this.answerBox[i][1]=='image') {
			this.answerBox[i][7]=temp_x;
			this.answerBox[i][8]=temp_y;
      if (this.qmode!='edit') {
        this.answerBox[i][5]=temp_x;
        this.answerBox[i][6]=temp_y;
      }
			temp_x += this.imglabelWidth +i_spacex;
			if ((temp_x + this.imglabelWidth + i_spacex) > 230) {
				temp_x = i_spacex;
				temp_y += this.imgLabelHeight + i_spacey;
				}
			}
		}
    ////console.log('this.answerBox');

		//calculating the text boxes
		if (temp_x > i_spacex) {
			temp_x = i_spacex;
			temp_y += this.imgLabelHeight + i_spacey;
			}
		for (i=0;i<this.answerBox.length;i++) {
			if (this.answerBox[i][1]=='text') {
			this.answerBox[i][7]=temp_x;
			this.answerBox[i][8]=temp_y;
      if (this.qmode!='edit') {
        this.answerBox[i][5]=temp_x;
        this.answerBox[i][6]=temp_y;
      }
			temp_x += this.labelWidthEffect +i_spacex;
			if ((temp_x + this.labelWidthEffect + i_spacex) > 230) {
				temp_x = i_spacex;
				temp_y += this.labelHeightEffect + i_spacey;
				}
			}
		}
		
		//loading label images and drawing boxes
		this.context.fillStyle=this.currentColours[0];
		this.context.StrokeStyle=this.currentColours[1];
		
		/*
				function ql_load_image_array(_self,index){
			if (index<_self.answerBox.length && _self.answerBox[index][1]=='image') {
				_self.answerBox[index][10] = new Image();
				_self.answerBox[index][10].onload = 
				
				function gen_img_onload(){
					this.imagesLoaded ++;
					if (this.imagesLoaded == _self.max_num_images) {
						_self.allImagesLoaded = true;
						_self.redraw_once = true;
						_self.ql_redraw_canvas;
						}
				}  
				_self.answerBox[index][10].src = '/media/'+_self.answerBox[index][2];
			}
			if ((index+1)<_self.answerBox.length) ql_load_image_array(_self,index+1);
		}
		*/
		
		function gen_img_onload(){
			this.imagesLoaded ++;

			if (this.imagesLoaded == this.max_num_images) {
				this.allImagesLoaded = true;
				this.redraw_once = true;
				this.ql_redraw_canvas;
			}
		}  		
			
		function ql_load_image_array(_self,index){
			if (index<_self.answerBox.length && _self.answerBox[index][1]=='image') {
				_self.answerBox[index][10] = new Image();
				_self.answerBox[index][10].onload = gen_img_onload.bind(_self);
				_self.answerBox[index][10].src = '/media/'+_self.answerBox[index][2];
			}
			if ((index+1)<_self.answerBox.length) ql_load_image_array(_self,index+1);
		}
		ql_load_image_array(this,0);
		
    
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
        ////console.log (new Array(this.pholderBox[i][1],this.pholderBox[i][2]-this.yOffset,st[i].position().left-c.left,st[i].position().top-c.top))
      }
    }    
    */
    ////console.log('this.pholderBox');
    
		//---------- answer, 
		////console.log('answer: '+answer);
    // sort out existing answer info
    if (answer != '') this.is_an_answer = true
    if (answer != "" && answer != undefined && answer != "undefined" && answer != null && answer != "null") {
      var answer_l1 = answer.split(";");
      var answer_l2 = answer_l1[1].split('$');
      for (i=0; i<answer_l2.length/4; i++) {
        if (answer_l2[i*4]!='') {
          var ans_x = Number(answer_l2[i*4+0]);
          var ans_y = Number(answer_l2[i*4+1]);
          var ans_n = answer_l2[i*4+2];
          var ans_b = answer_l2[i*4+3];
          for (j=0;j<this.answerBox.length;j++) {
            if (this.answerBox[j][2]==ans_n) {
            ////console.log (new Array(j,ans_x,ans_y,ans_n,ans_b));
              this.answerBox[j][5] = ans_x;
              this.answerBox[j][6] = ans_y+25-this.yOffset;
              this.answerBox[j][9] = ans_b;
              //this.pholderBox[j][5] = ans_b;
              //alert(ans_n+j);
              //alert($("#cb_"+j).length);
              //$("#cb_"+j + " option[value='2']").attr('selected', 'selected');
              $("#cb_"+j).val(3);
            }
          }
        }      
      }    
    }      
    
		//---------- extra, 
		//console.log('extra: '+extra);
    //1~0~Mark per Option

    if (extra != "" && extra != undefined && extra != "undefined" && extra != null && extra != "null") {
      var extra_l1 = extra.split('~');
      this.marks_per_correct = extra_l1[0];
      this.marks_per_incorrect = extra_l1[1];
      this.marking_method = extra_l1[2];
      //alert(this.marking_method);
      }
		//---------- colour 
		////console.log('colour: '+colour);
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
	//this.ql_mouseDragMovef();
	//console.log('<<< setUpLabelling');
}
  
function ql_draw_box(i,temp_x,temp_y) {
	//console.log('>>> ql_draw_box');
  ////console.log(this.answerBox[i]);  
  if (this.answerBox[i][1]=='image') this.context.fillRect(temp_x,temp_y,this.imglabelWidth,this.imgLabelHeight);
  if (this.answerBox[i][1]=='text') this.context.fillRect(temp_x,temp_y,this.labelWidthEffect,this.labelHeightEffect);

    this.context.shadowColor = 'white';
    this.context.shadowBlur = 0;
    this.context.shadowOffsetX = 0;
    this.context.shadowOffsetY = 0;
  
  if (this.answerBox[i][1]=='image') {
  //this.context.fillRect(temp_x,temp_y,this.imglabelWidth,this.imgLabelHeight);
  this.context.drawImage(this.answerBox[i][10],temp_x+(this.imglabelWidth-this.answerBox[i][3])*0.5,temp_y+(this.imgLabelHeight-this.answerBox[i][4])*0.5);
  this.context.strokeRect(temp_x+0.5,temp_y+0.5,this.imglabelWidth,this.imgLabelHeight);
  }
  
  if (this.answerBox[i][1]=='text') {
    //this.context.fillRect(temp_x,temp_y,this.labelWidthEffect,this.labelHeightEffect);
    this.context.fillStyle=this.currentColours[2];
    this.context.textAlign="center";
		//console.log('>>>',387,this.labelWidthEffect);
    var wrapped = this.wrapText(this.answerBox[i][2],this.labelWidthEffect);
		//console.log('xxxxx',this.labelWidthEffect,wrapped);
		this.fillWrappedText(this.context,wrapped[0],temp_x+this.labelWidthEffect*0.5,temp_y+this.flashFontSize[this.fontSizePos]+0.5);
    this.context.fillStyle=this.currentColours[0];
    this.context.strokeRect(temp_x+0.5,temp_y+0.5,this.labelWidthEffect,this.labelHeightEffect);
  }
	//console.log('<<< ql_draw_box');
}

function ql_redraw_box(i) {
	//console.log('>>> ql_redraw_box');        
	if (typeof this.answerBox[i][1] != 'undefined') {
    temp_x = this.answerBox[i][5];
    temp_y = this.answerBox[i][6];

    //setting shadow
    if ((this.drag_box_id==i || this.mov_id == i) && this.panelOptionOver==-1)  {
      this.context.shadowColor = '#AAA';
      this.context.shadowBlur = 8;
      this.context.shadowOffsetX = 2;
      this.context.shadowOffsetY = 2;
    }
    
    //slowing down (need to be after setting shadow not to leave shadow after animation)
    if (this.mov_id == i) {
      temp_x = this.mov_x = this.mov_x-(this.mov_x-this.answerBox[i][5])/this.slow_speed;
      temp_y = this.mov_y = this.mov_y-(this.mov_y-this.answerBox[i][6])/this.slow_speed;
      //end of slowing down  
      if (Math.abs(this.mov_x-this.answerBox[i][5])<1) {
        temp_x = this.answerBox[i][5];
        temp_y = this.answerBox[i][6];
        this.mov_id = -1;
        this.drag_box_id = -1;
        this.redraw_once = true;
      }     
    }	
    if (this.labelType == 'multiple' && this.mov_id != i && this.drag_box_id!=i) this.ql_draw_box(i,this.answerBox[i][7],this.answerBox[i][8]);
    this.ql_draw_box(i,temp_x,temp_y);
  }	
	//console.log('<<< ql_redraw_box');        
}

function ql_panelBoxBuild (but_name,pan_name) {
	//console.log('>>> ql_panelBoxBuild');        
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
	//console.log('<<< ql_panelBoxBuild');
}

function ql_menuBuild() {
	//console.log('>>> ql_menuBuild');
  //var imgdatab = menuImages['toolbar/but_back1'+'.png'];
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
	posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
	posx = this.menuBuild_icons('toolbar/ico_single.png',posx,posy,0,'b','',lang_string['single'])+spac;
	posx = this.menuBuild_icons('toolbar/ico_multiple.png',posx,posy,2,'b','',lang_string['multiple'])+spac;
	posx = this.menuBuild_icons('toolbar/vert_2.png',posx,posy,0,'','','')+spac;
  posx = this.menuBuild_icons('toolbar/ico_label.png',posx,posy,2,'c','',lang_string['label'])+spac;
	posx = this.menuBuild_icons('toolbar/ico_menu.png',posx,posy,0,'c','',lang_string['menu'])+spac;
  posx = this.menuBuild_icons('toolbar/ico_help.png',this.canvas.width-23,posy,0,'-','','')+spac;    

  //this.context.drawImage(this.menu_img,imgdata.left,imgdata.top,imgdata.width,imgdata.height,0,0,imgdata.width,imgdata.height);  
  
  //setting the this.ql_panelBox array
  this.ql_panelBoxBuild('toolbar/ico_bucket.png','toolbar/pan_colours.png');
  this.ql_panelBoxBuild('toolbar/ico_brush.png','toolbar/pan_colours.png');
  this.ql_panelBoxBuild('toolbar/ico_letter.png','toolbar/pan_colours.png'); 
  this.ql_panelBoxBuild('toolbar/ico_size.png','toolbar/pan_sizes.png');
  this.ql_panelBoxBuild('toolbar/ico_lines.png','toolbar/pan_lines.png');
	//console.log('<<< ql_menuBuild');
}

function ql_redraw_canvas() {
	////console.log('>>> ql_redraw_canvas');
	this.draw_limit = new Array(220,27,this.canvas.width-2,this.canvas.height-2);

	////console.log(this.canvas.id);
  function draw_shape(_self,tt,tx1,ty1,tx2,ty2) {
    //drawing the line, bobble or arrow...
		//console.log(_self.context.lineWidth);
		//_self.context.lineWidth = _self.lineThickness;
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
      //var aa=rr-Math.PI+pp;
      var x1 = 1*tx2+Math.cos(rr)*tt/2;
      var y1 = 1*ty2+Math.sin(rr)*tt/2;
      var x2 = Math.round(x1+Math.cos(rr-Math.PI+pp)*tt); 
      var y2 = Math.round(y1+Math.sin(rr-Math.PI+pp)*tt);
      var x3 = Math.round(x1+Math.cos(rr-Math.PI-pp)*tt); 
      var y3 = Math.round( y1+Math.sin(rr-Math.PI-pp)*tt);
      ////console.log (new Array('arrow',xx,yy,rr,x1,y1,x2,y2));
      //_self.context.lineJoin = "Miter"; //sharp edges
      _self.context.beginPath();
      _self.context.moveTo(x1,y1);
      _self.context.lineTo(x2,y2);
      _self.context.lineTo(x3,y3);
      _self.context.lineTo(x1,y1);
      _self.context.fill();
      _self.context.stroke();
      //_self.context.lineJoin = "Round"; //...back again
      _self.context.lineWidth = _self.lineThickness;
    }
    
    if (tt=='bobble') {
      _self.context.beginPath();
      _self.context.arc(tx2,ty2, 2+0.5*_self.lineThickness, 0 , 2 * Math.PI, false);
      _self.context.fill();
      _self.context.stroke();
    }
  }
 
  //if (this.buttonOver>-1) //console.log(this.buttonBox[this.buttonOver][5],this.buttonBox[this.buttonOver][6]);
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
	if (this.allImagesLoaded && this.menu_img_loaded && (this.dragging || this.redraw_once || this.mov_id!=-1 || (this.global_add != '' &&  this.shape_x1>-1) || this.global_move || this.global_erase)){
		this.redraw_once = false;
    //store this.lineThickness 
    var hold_lineThickness = this.lineThickness;
    
    //testing
    if ((this.global_move || this.global_erase) && typeof this.x != 'undefined') {
      this.lineThickness = 1.5*hold_lineThickness+2;
      this.activ_shape = -1;
      this.context.lineWidth = this.lineThickness;
      this.context.fillStyle = this.context.strokeStyle='#ff0000';
      for (i=0;i<this.shapeBox.length;i++) {
        this.context.clearRect(0,0,this.canvas.width,this.canvas.height);
        draw_shape(this,this.shapeBox[i][1],this.shapeBox[i][2],this.shapeBox[i][3],this.shapeBox[i][4],this.shapeBox[i][5]-this.yOffset);
        var timgd = this.context.getImageData(this.x,this.y,1,1);
        var timgp = timgd.data;
        if (hexifycolour(''+((timgp[0]*256+timgp[1])*256+1*timgp[2]))== '#ff0000') this.activ_shape=i;
      }
      ////console.log(this.activ_shape);
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
      
      ////console.log(shape_end);     
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
      draw_shape(this,this.shapeBox[this.activ_shape][1],this.shapeBox[this.activ_shape][2],this.shapeBox[this.activ_shape][3],this.shapeBox[this.activ_shape][4],this.shapeBox[this.activ_shape][5]-this.yOffset);
    }
    
    //restore this.lineThickness 
    this.lineThickness = hold_lineThickness;
    this.context.lineCap = 'butt';
    //draw line, arrow, bobble
    this.context.lineWidth = this.lineThickness;
		this.context.strokeStyle=this.currentColours[1];
    this.context.fillStyle = this.currentColours[1];
    for (i=0;i<this.shapeBox.length;i++) {
      draw_shape(this,this.shapeBox[i][1],this.shapeBox[i][2],this.shapeBox[i][3],this.shapeBox[i][4],this.shapeBox[i][5]-this.yOffset);
    }		
		
		//draw handlers for active shape
    if (this.global_move && this.activ_shape>-1) {
			this.edtDot(this.context,'#ff0000',this.shapeBox[this.activ_shape][2],this.shapeBox[this.activ_shape][3]-this.yOffset,2+0.1*this.lineThickness);
			this.edtDot(this.context,'#ff0000',this.shapeBox[this.activ_shape][4],this.shapeBox[this.activ_shape][5]-this.yOffset,2+0.1*this.lineThickness);

			this.context.strokeStyle=this.currentColours[1];
			this.context.fillStyle = this.currentColours[1];
		}

    if (this.shape_x1>-1 && this.shape_x2==-1) draw_shape(this,this.global_add,this.shape_x1,this.shape_y1,this.x,this.y);
    //alert(this.allImagesLoaded);
 		this.context.font=this.flashFontSize[this.fontSizePos]+"px Arial";
		var loc_width,loc_height;
    if (this.qType!='menu' && this.allImagesLoaded) {
      //draw placeholders
      for (i=0;i<this.pholderBox.length;i++) {

        //drawing background (unanswered)
        this.context.fillStyle=this.currentColours[0];
        if (this.pholderBox[i][5]=='' && this.is_an_answer && this.qmode!='edit') this.context.fillStyle=this.currentColours[3];

        //selecting width and height
        loc_width = this.imglabelWidth;loc_height = this.imgLabelHeight;
        if (this.pholderBox[i][3]=='text' ) {loc_width = this.labelWidthEffect;loc_height=this.labelHeightEffect;}

        //fill and strike background rectangle
        if (this.is_an_answer) this.context.fillRect(this.pholderBox[i][1]+0.5,this.pholderBox[i][2]+0.5,loc_width,loc_height);
        this.context.strokeRect(this.pholderBox[i][1]+0.5,this.pholderBox[i][2]+0.5,loc_width,loc_height);
        this.context.fillStyle=this.currentColours[0]; //resetting colour
      }
			
		  //edit box
			if (this.qmode=='edit' && this.active_box_id>-1) {
        loc_width = this.imglabelWidth;loc_height = this.imgLabelHeight;
        if (this.pholderBox[this.active_box_id][3]=='text') {
					loc_width = this.labelWidthEffect;
					loc_height = this.labelHeightEffect;
				}	
				//if (this.key_code!=0) console.log('key_code',this.key_code);
				//if (this.char_code!='') console.log('char_code',this.char_code);
				var text_len = this.pholderBox[this.active_box_id][4].length;
				if (this.key_code=='39') this.edit_box_pos++; 				//arror right
				if (this.key_code=='37') this.edit_box_pos--; 				//arrow left
				if (this.key_code=='35') this.edit_box_pos=text_len; 	//end
				if (this.key_code=='36') this.edit_box_pos=0; 				//home	
				if (this.edit_box_pos<0) this.edit_box_pos=0;
				if (this.edit_box_pos>text_len) this.edit_box_pos=text_len;
				if (this.key_code==0 && this.char_code!='') {					//characters
					var temp_t = this.pholderBox[this.active_box_id][4].substr(0,this.edit_box_pos)+this.char_code+this.pholderBox[this.active_box_id][4].substr(this.edit_box_pos);
					var metrics_temp = this.context.measureText(temp_t);
					this.answerBox[this.active_box_id][2] = this.pholderBox[this.active_box_id][4] = temp_t;
					//console.log(this.pholderBox[this.active_box_id][4]);
					this.edit_box_pos++;
				}
				if (this.key_code=='46') { //del
					var temp_t = this.pholderBox[this.active_box_id][4].substr(0,this.edit_box_pos)+this.pholderBox[this.active_box_id][4].substr(this.edit_box_pos+1);
					this.answerBox[this.active_box_id][2] = this.pholderBox[this.active_box_id][4] = temp_t;
				}
				if (this.key_code=='8') { //backspace
					var temp_t = this.pholderBox[this.active_box_id][4].substr(0,this.edit_box_pos-1)+this.pholderBox[this.active_box_id][4].substr(this.edit_box_pos);
					this.answerBox[this.active_box_id][2] = this.pholderBox[this.active_box_id][4] = temp_t;
					this.edit_box_pos--;
				}
				
				this.char_code ='';
				this.key_code = 0;
			}      

			//scaling up the labelwidth
			this.labelWidthEffect = this.labelWidth;
			this.labelHeightEffect = this.labelHeight;
      for (i=0;i<this.answerBox.length;i++) {
		    //document.getElementById('canvas_edit_box').innerHTML = '';
				var wrapTemp = this.wrapText(this.answerBox[i][2],this.labelWidthEffect);				
				//fillWrappedText(this.context,this.answerBox[i][2],this.answerBox[i][5]+this.labelWidthEffect*0.5,this.answerBox[i][6]+this.flashFontSize[this.fontSizePos]+0.5);
				if (wrapTemp[2] > this.labelWidthEffect) this.labelWidthEffect = wrapTemp[2]+8;
				if (wrapTemp[1] > this.labelHeightEffect) this.labelHeightEffect = wrapTemp[1]+4;
				//console.log(this.labelWidthEffect,this.labelHeightEffect);
		    //document.getElementById('canvas_edit_box').innerHTML = this.labelWidthEffect+':'+this.labelHeightEffect;
      }

      //draw all initial frames and images
      for (i=0;i<this.answerBox.length;i++) {
			  if (this.drag_box_id!=i && this.mov_id!=i) this.ql_redraw_box(i);
      }
			if (this.qmode=='edit' && this.active_box_id>-1) {
        loc_width = this.imglabelWidth;loc_height = this.imgLabelHeight;
        if (this.pholderBox[this.active_box_id][3]=='text') {loc_width = this.labelWidthEffect;loc_height=this.labelHeightEffect;}

				//draw handlers for active label
				this.context.strokeStyle='#ff0000';
				this.context.strokeRect(
					this.pholderBox[this.active_box_id][1]-this.lineThickness/2+0.5,
					this.pholderBox[this.active_box_id][2]-this.lineThickness/2+0.5,
					loc_width+this.lineThickness,
					loc_height+this.lineThickness);
				this.edtDot(
					this.context,'#ff0000',
					this.pholderBox[this.active_box_id][1]-this.lineThickness/2+0.5,
					this.pholderBox[this.active_box_id][2]-this.lineThickness/2+0.5,
					2+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#ff0000',
					this.pholderBox[this.active_box_id][1]-this.lineThickness/2+0.5,
					this.pholderBox[this.active_box_id][2]+loc_height+this.lineThickness/2+0.5,
					2+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#ff0000',
					this.pholderBox[this.active_box_id][1]+loc_width+this.lineThickness/2+0.5,
					this.pholderBox[this.active_box_id][2]-this.lineThickness/2+0.5,
					2+0.1*this.lineThickness);
				this.edtDot(
					this.context,'#ff0000',
					this.pholderBox[this.active_box_id][1]+loc_width+this.lineThickness/2+0.5,
					this.pholderBox[this.active_box_id][2]+loc_height+this.lineThickness/2+0.5,
					2+0.1*this.lineThickness);
				this.context.strokeStyle=this.currentColours[1];
			}
			
			this.context.fillStyle=this.currentColours[0]; //resetting colour
			//redraw active label to have it on top
      if (this.active_box_id>-1) this.ql_redraw_box(this.active_box_id);
			//redraw dragged shape to have it on top
      if (this.drag_box_id>-1) this.ql_redraw_box(this.drag_box_id);
      //redraw animated shape to have it on top
      if (this.mov_id>-1) this.ql_redraw_box(this.mov_id);
			
			//cursor blink
			if (this.qmode=='edit' && this.active_box_id>-1) {
				this.edit_box_blink++;
				if (this.edit_box_blink>40) this.edit_box_blink=0;
				if (this.edit_box_blink>20) {
					var text_all = this.wrapText(this.pholderBox[this.active_box_id][4],this.labelWidthEffect)[0];
					var text_temp = '';
					if (this.edit_box_pos>0) text_temp = text_all.substr(0,this.edit_box_pos);
					var wrap_temp = text_temp.split('|');
					var text_part_line = wrap_temp.length-1;
					
					var text_part = wrap_temp[text_part_line]
					var text_full = text_all.split('|')[text_part_line];
					//console.log(text_full,text_part,text_part_line);
										
					var metrics_part = this.context.measureText(text_part);
					var metrics_full = this.context.measureText(text_full);
					
					this.context.strokeStyle='#000000';					
				  this.context.beginPath();
					var temp_x = Math.round(this.pholderBox[this.active_box_id][1]+(this.labelWidthEffect-metrics_full.width)/2+metrics_part.width)-0.5;
					var temp_y = Math.round(this.flashFontSize[this.fontSizePos]*text_part_line+this.pholderBox[this.active_box_id][2]+4)-0.5;
					this.context.moveTo(temp_x,temp_y);
					this.context.lineTo(temp_x,temp_y+this.flashFontSize[this.fontSizePos]);
					this.context.stroke();
					this.context.strokeStyle=this.currentColours[1];
					
				}
			}
		}
    
    //buttons
    if (this.qmode=='edit') {
			//console.log(buttonBox);
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
        this.menuRebuild_panel(this.ql_panelActiveParts,this.ql_panelBox,'toolbar/ico_bucket.png','toolbar/pan_colours.png',0,m);
        //draw linetable
        for (n=0;n<this.colorReference.length;n++) if (this.currentColours[1]==this.colorReference[n]) m = n;
        this.menuRebuild_panel(this.ql_panelActiveParts,this.ql_panelBox,'toolbar/ico_brush.png','toolbar/pan_colours.png',0,m);
        //draw fontcolourtable
        for (n=0;n<this.colorReference.length;n++) if (this.currentColours[2]==this.colorReference[n]) m = n;
        this.menuRebuild_panel(this.ql_panelActiveParts,this.ql_panelBox,'toolbar/ico_letter.png','toolbar/pan_colours.png',0,m);         
        //draw sizetable
        this.menuRebuild_panel(this.ql_panelActiveParts,this.ql_panelBox,'toolbar/ico_size.png','toolbar/pan_sizes.png',1,this.fontSizePos);
        
        //display char size number on menu button
        ////console.log(this.ql_panelActiveParts);
        ////console.log(this.fontSizePos);
        var tp = this.ql_panelActiveParts['toolbar/pan_sizes.png'][this.fontSizePos].split(',');
        var imgdata = menuImages['toolbar/pan_sizes.png'];
        var temp_but = this.buttonBox[this.buttonBoxNames['toolbar/ico_size.png']];
        this.context.drawImage(this.menu_img,imgdata.left+1*tp[0],imgdata.top+1*tp[1],18,18,(temp_but[1]*1-1),temp_but[2],18,18);

        //draw linetable
        this.menuRebuild_panel(this.ql_panelActiveParts,this.ql_panelBox,'toolbar/ico_lines.png','toolbar/pan_lines.png',2,this.lineThickness-1);
    }
    //tooltip
		this.draw_limit = new Array(0,27,this.canvas.width-2,this.canvas.height-2);
    if (this.buttonOver!=-1 && this.buttonClicked!=1 && this.buttonClicked!=3 && this.buttonClicked!=5 && this.buttonClicked!=7 && this.buttonClicked!=9) this.tooltip_draw(this.context,this.buttonBox[this.buttonOver]);

    // border
    this.context.lineWidth = 1;
    this.context.strokeStyle='#7f9db9';  
		this.context.strokeRect(0.5,0.5,this.canvas.width-1,this.canvas.height-1); //border
	}
	////console.log('<<< ql_redraw_canvas');
}

function ql_mouseDragMove(e){
	//console.log('>>> ql_mouseDragMove');
	if (e.type=='keydown') {
		var ev = window.event ? event : e;
		this.isShift = ev.shiftKey ? true : false;
		this.isCtrl = ev.ctrlKey ? true : false;
		this.ShiftChange = true;
	}
	if (e.type=='keypress') { 
		var ev = window.event ? event : e;
		this.key_code = ev.keyCode;
		this.char_code = String.fromCharCode(ev.charCode);
	}
	if (e.type=='keyup') { 
		this.isShift = false;
		this.ShiftChange = true;
		this.isCtrl = false;
	}
	
	this.x = e.clientX - this.canv_rect.left;
	this.y = e.clientY -this.canv_rect.top;
	if (this.dragging && this.drag_box_id>-1){ //this.dragging
		//new position of dragged shape
		if (this.qmode!='edit' || this.global_move) {
			this.answerBox[this.drag_box_id][5] = this.x - this.sub_x;
			this.answerBox[this.drag_box_id][6] = this.y - this.sub_y;
		}
		//limits
			if (this.answerBox[this.drag_box_id][5]<1) this.answerBox[this.drag_box_id][5]=1;
			if (this.answerBox[this.drag_box_id][6]<(26-this.yOffset)) this.answerBox[this.drag_box_id][6]=26-this.yOffset;
			if (this.answerBox[this.drag_box_id][5]>(this.canvas.width-this.labelWidthEffect-2)) this.answerBox[this.drag_box_id][5]=this.canvas.width-this.labelWidthEffect-2;
			if (this.answerBox[this.drag_box_id][6]>(this.canvas.height-this.labelHeightEffect-2)) this.answerBox[this.drag_box_id][6]=this.canvas.height-this.labelHeightEffect-2;		
		
		if (this.qmode=='edit'){			
			this.pholderBox[this.drag_box_id][1] = this.answerBox[this.drag_box_id][5];
			this.pholderBox[this.drag_box_id][2] = this.answerBox[this.drag_box_id][6];
		}
	}
	else { //change of cursor
    this.drag_box_id = -1;        
		if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
			var over_object = false;
			
      //test for this.answerBoxes
      for (i=0;i<this.answerBox.length;i++) {
				if (this.answerBox[i][1]=='image') {
					if (this.testWithin(this.x,this.y,this.answerBox[i][5],this.answerBox[i][6],this.imglabelWidth,this.imgLabelHeight)==true) {
						over_object = true;
						this.drag_box_id = i;
					}
				}
				if (this.answerBox[i][1]=='text') {
					if (this.testWithin(this.x,this.y,this.answerBox[i][5],this.answerBox[i][6],this.labelWidthEffect,this.labelHeightEffect)==true) {
						over_object = true;
						this.drag_box_id = i;
					}
				}
			}	
      
      //test for buttons
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
				var test_width = 19;
				if (tmp_pan=='toolbar/pan_sizes.png') test_width = 22;
				if (tmp_pan=='toolbar/pan_lines.png') test_width = 130;
        for(i=0;i<this.ql_panelActiveParts[tmp_pan].length;i++) {
          var tp = this.ql_panelActiveParts[tmp_pan][i].split(',');
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
      //if (this.global_edit) cur = 'not-allowed';
			//if (this.global_edit && test_result!='' && test_result.indexOf('$')<test_result.length-1) cur = 'default';
			if (over_object) cur = 'pointer';
			if (this.global_move && this.activ_shape>-1 && this.y>28) cur = 'move';
 			if (this.global_erase && this.activ_shape>-1 && this.y>28) cur = 'url(/html5/images/cur_erase.cur) 6 5, default';//cur_cross
      if (this.buttonOver>-1 && this.buttonBox[this.buttonOver][0]=='toolbar/ico_help.png') cur = 'help';
      e.target.style.cursor = cur;
		
		}
	}
	//console.log('<<< ql_mouseDragMove');
}

function ql_mouseDragDown(e){
	//console.log('>>> ql_mouseDragDown');
	this.x = e.clientX - this.canv_rect.left;
	this.y = e.clientY - this.canv_rect.top;
	if (this.testWithin(this.x,this.y,0,0,this.canvas.width,this.canvas.height)){
		if (this.drag_box_id>-1) {
			this.sub_x = this.x - this.answerBox[this.drag_box_id][5];
			this.sub_y = this.y - this.answerBox[this.drag_box_id][6];
		}
		if (this.panelOptionOver==-1) this.dragging = true;	
	}
  
  this.activ_shape_move = this.activ_shape;
  this.activ_shape_x = this.x;
  this.activ_shape_y = this.y;
	//console.log('<<< ql_mouseDragDown');
}

function ql_mouseDragUp(){
	this.active_box_id = this.drag_box_id;
	this.dragging = false;
  //erase shape
  if (this.global_erase && this.activ_shape>-1) {
    this.shapeBox.splice(this.activ_shape,1);
  }
  this.activ_shape_move = this.activ_shape = -1;

  //this.dragging shapes
  if (this.drag_box_id>-1 && this.qmode!='edit') {
		//testing against the position of placeholders
		var dest_box=-1;
		for (i=0;i<this.pholderBox.length;i++) {
			var loc_width = this.imglabelWidth,loc_height = this.imgLabelHeight;
			if (this.pholderBox[i][3]=='text' ) {loc_width = this.labelWidthEffect;loc_height=this.labelHeightEffect;}
			if (this.testWithin(this.x,this.y,this.pholderBox[i][1],this.pholderBox[i][2],loc_width,loc_height)==true) dest_box = i;
		}
		
		this.mov_id = this.drag_box_id;
		this.mov_x = this.x - this.sub_x;
		this.mov_y = this.y - this.sub_y;
		if (dest_box>-1 && this.answerBox[this.drag_box_id][1]==this.pholderBox[dest_box][3]) {
      //removing any shape previously put into that position
      for (i=0;i<this.answerBox.length;i++) {
        if (this.answerBox[i][5] == this.pholderBox[dest_box][1] && this.answerBox[i][6] == this.pholderBox[dest_box][2] && i != this.drag_box_id) {
          this.answerBox[i][5] = this.answerBox[i][7];
          this.answerBox[i][6] = this.answerBox[i][8];
          this.answerBox[i][9] = '';
        }
      }
      //is it correctly dropped label
      if (this.answerBox[this.drag_box_id][2]==this.pholderBox[dest_box][4]) 
        this.answerBox[this.drag_box_id][9]='t'
      else
        this.answerBox[this.drag_box_id][9]='f'

      this.answerBox[this.drag_box_id][5] = this.pholderBox[dest_box][1];
      this.answerBox[this.drag_box_id][6] = this.pholderBox[dest_box][2];

    }
    else {
      //label dropped outside and target is sent back
      this.answerBox[this.drag_box_id][5] = this.answerBox[this.drag_box_id][7];
      this.answerBox[this.drag_box_id][6] = this.answerBox[this.drag_box_id][8];
      this.answerBox[this.drag_box_id][9] = '';
    }
  }
  
  //'erase' label
  if (this.global_erase && this.drag_box_id>-1) {
    this.answerBox[this.drag_box_id][5] = this.answerBox[this.drag_box_id][7];
    this.answerBox[this.drag_box_id][6] = this.answerBox[this.drag_box_id][8];
    this.answerBox[this.drag_box_id][9] = '';
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
				//console.log(this.shapeBox[this.shapeBox.length-1]);
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
	//console.log('<<< ql_mouseDragUp');
}

function ql_ReturnInfo() {
	//console.log('>>> ql_ReturnInfo');

  var questions_correct = 0;
  var questions_incorrect = 0;
  var questions_total = 0;
  var questions_result = '';
  var temp_answ = new Array();


	for (i=0;i<this.pholderBox.length;i++) {
    if (this.pholderBox[i][4] != "" && this.pholderBox[i][1]>220) {
      temp_answ[this.pholderBox[i][4]] = this.pholderBox[i][1]+','+this.pholderBox[i][2];
      questions_total++;
      }
  }
	////console.log(temp_answ);

  for (i=0;i<this.answerBox.length;i++) {
    
    if (this.answerBox[i][9]=='t') questions_correct++;
    if (this.answerBox[i][9]=='f') questions_incorrect++;
    if (this.answerBox[i][9]=='t' || this.answerBox[i][9]=='f') questions_result+=this.answerBox[i][5]+'$'+(this.answerBox[i][6]-25+this.yOffset)+'$'+this.answerBox[i][2]+'$'+this.answerBox[i][9]+'$';
  }  
  var marks_max = this.marks_per_correct * questions_total;
  var marks_total = this.marks_per_correct * questions_correct + this.marks_per_incorrect * questions_incorrect;
  if (this.marking_method != 'Mark per Option') {
    marks_total = this.marks_per_incorrect;
    marks_max = this.marks_per_correct;
    if (questions_correst == questions_total) marks_total = this.marks_per_correct;
  }
  var result = marks_total+'$'+marks_max+';'+questions_result;

  ////console.log(this.answerBox);
  ////console.log(this.pholderBox);
  ////console.log(result);
  
  var flashTarget = (typeof flashTarget === 'undefined' || flashTarget == '') ? 'q' : flashTarget;
  var target_field = document.getElementById(flashTarget+this.q_Num);
  if (questions_result!='' && target_field) target_field.value = questions_result;
	//console.log('<<< ql_ReturnInfo');
}

function rql(num) {
	//console.log('>>> rql');

	this.setUpLabelling = setUpLabelling;
	this.ql_draw_box = ql_draw_box;
	this.ql_redraw_box = ql_redraw_box;
	this.ql_panelBoxBuild = ql_panelBoxBuild;
	this.ql_menuBuild = ql_menuBuild;
	this.ql_redraw_canvas = ql_redraw_canvas;
	this.ql_ReturnInfo = ql_ReturnInfo;
	this.ql_mouseDragMove = ql_mouseDragMove;
	this.ql_mouseDragDown = ql_mouseDragDown;
	this.ql_mouseDragUp = ql_mouseDragUp;
	
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
	
	this.test; 
	this.x,this.y,this.z,this.sub_x,this.sub_y,this.m;
	this.i,this.j;
	
	this.scale_i=1;                          	//label image scale
	this.drag_box_id=-1;                      //index of box beeing dragged
	this.active_box_id=-1;                    //index of box beeing active
  this.menu_ready = 1;
	this.edit_box_blink = 0;
	this.edit_box_pos = 0;
	this.key_code = 0;
	this.char_code = ''

	this.allImagesLoaded = false;
	this.max_num_images = 0;
	//this.maxLabels = 20; 					          // max no. of different labels (text & image)
	//this.numLabelInstances = new Array();   // no. of instances of each label
	this.pholderBox = new Array(); 			      // label no. that's correct answer for each placeholder
                                            // distractor placeholders have answer of -1
                                            // sublevels of this keep all the placeholder data
	this.answerBox = new Array(); 			      // sublevels of this keep all the label datA
  this.shapeBox = new Array();            // sublevels of this keep all the lines/arrows/bobbles data
  this.buttonBox = new Array();             // sublevels of this keep all the buttons data
  this.ql_panelBox = new Array();           // sublevels of this keep the panels data
  this.buttonBoxNames = new Array();      	// transcription of button names into its index in ButtonBox (?)
  this.buttonClicked = -1;                  // index of the button that was clicked
  this.buttonOver =-1;                      // index of the button the mouse is over
  this.panelOptionOver =-1;                 // index of the option on panel the mouse is over
  this.panelOver =-1                        // index of the panel the mouse is over
  this.panelOverColour = '';
  this.colorReference = new Array();
  this.global_edit = false;
  this.global_erase = false;
  this.shape_x1 = this.shape_y1 = this.shape_x2 = this.shape_y2 = -1  // temporary params of a new line/arrow/bobble
  this.global_add = '';
  this.global_move = false;
  this.activ_shape = this.activ_shape_move = this.activ_shape_x = this.activ_shape_y = -1;

  this.ql_panelActiveParts = new Array();   // array of positions panel's active shapes
  //defining panel's active parts
	this.ql_panelActiveParts.push('toolbar/pan_colours.png');
  this.ql_panelActiveParts['toolbar/pan_colours.png'] = new Array();
  //'toolbar/pan_colours.png
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][00+i] = (i*18+1)+','+19;
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][10+i] = (i*18+1)+','+(39+12*0);
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][20+i] = (i*18+1)+','+(39+12*1);
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][30+i] = (i*18+1)+','+(39+12*2);
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][40+i] = (i*18+1)+','+(39+12*3);
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][50+i] = (i*18+1)+','+(39+12*4);
  for(i=0;i<10;i++) this.ql_panelActiveParts['toolbar/pan_colours.png'][60+i] = (i*18+1)+','+121;
  
	//'toolbar/pan_sizes.png
	this.ql_panelActiveParts.push('toolbar/pan_sizes.png');
  this.ql_panelActiveParts['toolbar/pan_sizes.png'] = new Array();
  for(i=0;i<7;i++) this.ql_panelActiveParts['toolbar/pan_sizes.png'][i] = 3+','+(i*19+3);
  //'toolbar/pan_lines.png
  this.ql_panelActiveParts.push('toolbar/pan_lines.png');
  this.ql_panelActiveParts['toolbar/pan_lines.png'] = new Array();
  for(i=0;i<7;i++) this.ql_panelActiveParts['toolbar/pan_lines.png'][i] = 3+','+(i*19+3);

	this.labelInstanceDepth = new Array();  // depth new instances are created on inside each labelGroup clip
	this.labelTxt = new Array(); 			      // stores text on each label
	this.labelTypes = new Array(); 			    // is label a "text" or "image" label?
	this.imageNames = new Array(); 			    // names of images on each label
	this.imageDimensions = new Array(); 		// individual dimensions of draggable images

	this.labelCoords = new Array(); 			  // coords for each label
	this.comboCoords = new Array(); 			  // coords for comboboxes - used temporarily in setting them up

	this.distractorTxt = new Array(); 		  // distractors in comboboxes
	this.depthSwapperLabel = new Array(); 	// selected labels are swapped with this clip so label will be on top of all others in same group
	this.qType = "label"; 					        // draggable label ("label"), drop down menu ("menu")
	this.labelType = "single"; 				      // are labels unique or repeated ("single" / "multiple")?
	this.yOffset ; 						              // coords of everything made in label_add.swf include toolbar 
                                          // this need to be removed as image here is loaded with this.y coord = 0
	//this.markIncorrect = 0;
	//this.marksPossible = 0;
	//this.myMarks = 0;
  //this.markingType;                     // are they marked for each individual label or as a whole question?
  this.is_an_answer = false;
  this.q_Num;
  this.slow_speed = 5;                    //parameter of slowing down speed
	
	this.currentColours = Array('#FFFFFF','#3F3F3F','#000000','#FF0000'); // fill, line, text, unanswered colours
	this.lineThickness  = 1; 									                            // current thickness of borders around draggable labels and manually drawn lines / arrows (in pixels) 
	this.fontChoices    = Array(9, 10, 11, 12, 14, 16, 18); 		          // font size in drop down menu
	this.flashFontSize  = Array(11, 12, 14, 16, 18, 20, 22); 	            // font size equivalent in Flash (not standard sizes)
	this.fontSizePos    = 1; 									                            // current font size for labels (index from array above);
  this.draw_limit = new Array(); 																				//used to limit polygon, ellipse and sqare positions
	this.dragging = false;
	this.redraw_once = false;
	this.gen_img;
	this.menu_img;
	this.gen_img_loaded = false;
	this.menu_img_loaded = false;
  this.mov_id = -1;
  this.mov_x=0;
  this.mov_y=0;
  this.canvas;
	this.context;
  this.canv_rect;
  this.marks_per_correct = 1;
  this.marks_per_incorrect = 0;
  this.marking_method = 'Mark per Option';
  this.qmode;
	//console.log('<<< rql');
}