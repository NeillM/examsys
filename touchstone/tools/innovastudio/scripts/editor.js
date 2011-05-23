var oUtil=new InnovaEditorUtil();
function InnovaEditorUtil()
{
this.langDir="english";
try{if(LanguageDirectory)this.langDir=LanguageDirectory;}catch(e){;}
var oScripts=document.getElementsByTagName("script");
for(var i=0;i<oScripts.length;i++)
{
var sSrc=oScripts[i].src.toLowerCase();
if(sSrc.indexOf("scripts/editor.js")!=-1) this.scriptPath=oScripts[i].src.replace(/editor.js/ig,"");
}
this.scriptPathLang=this.scriptPath+"language/"+this.langDir+"/";
if(this.langDir=="english")
document.write("<scr"+"ipt src='"+this.scriptPathLang+"editor_lang.js'></scr"+"ipt>");

this.oName;this.oEditor;this.obj;
this.oSel;
this.sType;
this.bInside=bInside;
this.useSelection=true;
this.arrEditor=[];
this.onSelectionChanged=function(){return true;};
this.activeElement;
}
function bInside(oElement)
{
while(oElement!=null)
{
if(oElement.contentEditable=="true")return true;
oElement=oElement.parentElement;
}
return false;
}
function checkFocus()
{
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;

if(oSel.parentElement!=null)
{
if(!bInside(oSel.parentElement()))return false;
}
else
{
if(!bInside(oSel.item(0)))return false;
}
return true;
}
function iwe_focus()
{
var oEditor=eval("idContent"+this.oName);
oEditor.focus()
}

function InnovaEditor(oName)
{
this.oName=oName;
this.RENDER=RENDER;
this.IsSecurityRestricted=false;

this.loadHTML=loadHTML;
this.putHTML=putHTML;
this.getHTMLBody=getHTMLBody;
this.getXHTMLBody=getXHTMLBody;
this.getHTML=getHTML;
this.getXHTML=getXHTML;
this.getTextBody=getTextBody;
this.initialRefresh=false;
this.preserveSpace=false;

this.bInside=bInside;
this.checkFocus=checkFocus;
this.focus=iwe_focus;

this.onKeyPress=function(){return true;};

this.styleSelectionHoverBg="#acb6bf";
this.styleSelectionHoverFg="white";

this.cleanEmptySpan=cleanEmptySpan;
this.cleanFonts=cleanFonts;
this.cleanTags=cleanTags;
this.replaceTags=replaceTags;
this.cleanDeprecated=cleanDeprecated;

this.doClean=doClean;
this.applySpanStyle=applySpanStyle;
this.applyLine=applyLine;
this.applyBold=applyBold;
this.applyItalic=applyItalic;

this.doOnPaste=doOnPaste;
this.isAfterPaste=false;

this.doCmd=doCmd;
this.applyParagraph=applyParagraph;
this.applyBullets=applyBullets;
this.applyNumbering=applyNumbering;
this.applyJustifyLeft=applyJustifyLeft;
this.applyJustifyCenter=applyJustifyCenter;
this.applyJustifyRight=applyJustifyRight;
this.doPaste=doPaste;
this.doPasteText=doPasteText;
this.applySpan=applySpan;
this.makeAbsolute=makeAbsolute;
this.insertHTML=insertHTML;
this.clearAll=clearAll;
this.insertCustomTag=insertCustomTag;
this.selectParagraph=selectParagraph;
this.selectedText=selectedText;
this.ClearSelectedText = ClearSelectedText;

this.hide=hide;
this.dropShow=dropShow;

this.width="620";
this.height="350";
this.publishingPath="";

var oScripts=document.getElementsByTagName("script");
for(var i=0;i<oScripts.length;i++)
{
var sSrc=oScripts[i].src.toLowerCase();
if(sSrc.indexOf("scripts/editor.js")!=-1) this.scriptPath=oScripts[i].src.replace(/editor.js/,"");
}

this.iconPath="icons/";
this.iconWidth=23;//25;
this.iconHeight=25;//24;
this.iconOffsetTop;//not used

this.writeIconToggle=writeIconToggle;
this.writeIconStandard=writeIconStandard;
this.writeDropDown=writeDropDown;
this.writeBreakSpace=writeBreakSpace;
this.dropTopAdjustment=-1;
this.dropLeftAdjustment=0;

this.runtimeBorder=runtimeBorder;
this.runtimeBorderOn=runtimeBorderOn;
this.runtimeBorderOff=runtimeBorderOff;
this.IsRuntimeBorderOn=true;
this.runtimeStyles=runtimeStyles;

this.applyColor=applyColor;
this.customColors=[];//["#ff4500","#ffa500","#808000","#4682b4","#1e90ff","#9400d3","#ff1493","#a9a9a9"];
this.expandSelection=expandSelection;

this.arrElm=new Array(300);
this.getElm=iwe_getElm;

this.features=[];
this.buttonMap=["Save","FullScreen","Preview","Print","Search","SpellCheck","|",
"Cut","Copy","Paste","PasteWord","PasteText","|","Undo","|",
"ForeColor","BackColor","|","Bookmark","Hyperlink",
"Image","Flash","Media","ContentBlock","InternalLink","InternalImage","CustomObject","|",
"Table","Guidelines","Absolute","|","Characters","Line",
"Form","RemoveFormat","HTMLFullSource","HTMLSource","XHTMLFullSource",
"XHTMLSource","ClearAll","BRK",
"StyleAndFormatting","Styles","|","CustomTag","Paragraph","FontName","FontSize","|",
"Bold","Italic","Underline","Strikethrough","Superscript","Subscript","|",
"JustifyLeft","JustifyCenter","JustifyRight","JustifyFull","|",
"Numbering","Bullets","|","Indent","Outdent","LTR","RTL"];//complete, default

this.btnStyles=false;this.btnParagraph=true;this.btnFontName=true;this.btnFontSize=true;
this.btnCut=true;this.btnCopy=true;this.btnPaste=true;this.btnPasteText=false;this.btnUndo=true;
this.btnBold=true;this.btnItalic=true;this.btnUnderline=true;
this.btnStrikethrough=false;this.btnSuperscript=false;this.btnSubscript=false;
this.btnJustifyLeft=true;this.btnJustifyCenter=true;this.btnJustifyRight=true;this.btnJustifyFull=true;
this.btnNumbering=true;this.btnBullets=true;this.btnIndent=true;this.btnOutdent=true;
this.btnHyperlink=true;this.btnBookmark=true;this.btnCharacters=true;this.btnCustomTag=false;
this.btnTable=true;this.btnGuidelines=true;
this.btnAbsolute=true;this.btnPasteWord=true;this.btnLine=true;
this.btnForm=true;this.btnRemoveFormat=true;
this.btnXHTMLFullSource=false;this.btnXHTMLSource=true;
this.btnClearAll=false;

this.btnContentBlock=false;
this.cmdContentBlock=";";//needs ;
this.btnInternalLink=false;
this.cmdInternalLink=";";//needs ;
this.btnCustomObject=false;
this.cmdCustomObject=";";//needs ;
this.btnInternalImage=false;
this.cmdInternalImage=";";//needs ;

this.css="";
this.arrStyle=[];
this.isCssLoaded=false;
this.openStyleSelect=openStyleSelect;

this.arrCustomTag=[];//eg.[["Full Name","{%full_name%}"],["Email","{%email%}"]];

this.docType="";
this.html="<html>";
this.headContent="";
this.preloadHTML="";

this.onSave=function(){document.getElementById("iwe_btnSubmit"+this.oName).click()};
this.useBR=false;
this.useDIV=true;

this.doUndo=doUndo;
this.saveForUndo=saveForUndo;
this.arrUndoList=[];
this.arrRedoList=[];

this.useTagSelector=true;
this.TagSelectorPosition="bottom";
this.moveTagSelector=moveTagSelector;
this.selectElement=selectElement;
this.removeTag=removeTag;
this.doClick_TabCreate=doClick_TabCreate;
this.doRefresh_TabCreate=doRefresh_TabCreate;

this.arrCustomButtons = [["CustomName1","alert(0)","caption here","btnSave.gif"],
["CustomName2","alert(0)","caption here","btnSave.gif"]];

this.onSelectionChanged=function(){return true;};

this.spellCheckMode="ieSpell";//NetSpell

this.REPLACE=REPLACE;
this.idTextArea;
this.mode="HTMLBody";
}

function saveForUndo()
{
var oEditor=eval("idContent"+this.oName);
var obj=eval(this.oName);
if(obj.arrUndoList[0])
if(oEditor.document.body.innerHTML==obj.arrUndoList[0][0])return;
for(var i=20;i>1;i--)obj.arrUndoList[i-1]=obj.arrUndoList[i-2];
obj.focus();
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;

if(sType=="None")
obj.arrUndoList[0]=[oEditor.document.body.innerHTML,
oEditor.document.selection.createRange().getBookmark(),"None"];
else if(sType=="Text")
obj.arrUndoList[0]=[oEditor.document.body.innerHTML,
oEditor.document.selection.createRange().getBookmark(),"Text"];
else if(sType=="Control")
{
oSel.item(0).selThis="selThis";
obj.arrUndoList[0]=[oEditor.document.body.innerHTML,null,"Control"];
oSel.item(0).removeAttribute("selThis",0);
}
this.arrRedoList=[];//clear redo list

if(this.btnUndo) makeEnableNormal(eval("document.all.btnUndo"+this.oName));
}
function doUndo()
{
var oEditor=eval("idContent"+this.oName);
var obj=eval(this.oName);
if(!obj.arrUndoList[0])return;
for(var i=20;i>1;i--)obj.arrRedoList[i-1]=obj.arrRedoList[i-2];
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;
if(sType=="None")
this.arrRedoList[0]=[oEditor.document.body.innerHTML,
oEditor.document.selection.createRange().getBookmark(),"None"];
else if(sType=="Text")
this.arrRedoList[0]=[oEditor.document.body.innerHTML,
oEditor.document.selection.createRange().getBookmark(),"Text"];
else if(sType=="Control")
{
oSel.item(0).selThis="selThis";
this.arrRedoList[0]=[oEditor.document.body.innerHTML,null,"Control"];
oSel.item(0).removeAttribute("selThis",0);
}
sHTML=obj.arrUndoList[0][0];
var arrA = String(sHTML).match(/<A[^>]*>/ig);
if(arrA)
for(var i=0;i<arrA.length;i++)
{
sTmp = arrA[i].replace(/href=/,"href_iwe=");
sHTML=String(sHTML).replace(arrA[i],sTmp);
}
var arrB = String(sHTML).match(/<IMG[^>]*>/ig);
if(arrB)
for(var i=0;i<arrB.length;i++)
{
sTmp = arrB[i].replace(/src=/,"src_iwe=");
sHTML=String(sHTML).replace(arrB[i],sTmp);
}
var arrC = String(sHTML).match(/<AREA[^>]*>/ig);
if(arrC)
for(var i=0;i<arrC.length;i++)
{
sTmp = arrC[i].replace(/href=/,"href_iwe=");
sHTML=String(sHTML).replace(arrC[i],sTmp);
}
oEditor.document.body.innerHTML=sHTML;
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].getAttribute("href_iwe"))
{
oEditor.document.all[i].href=oEditor.document.all[i].getAttribute("href_iwe");
oEditor.document.all[i].removeAttribute("href_iwe",0);
}
if(oEditor.document.all[i].getAttribute("src_iwe"))
{
oEditor.document.all[i].src=oEditor.document.all[i].getAttribute("src_iwe");
oEditor.document.all[i].removeAttribute("src_iwe",0);
}
}
this.runtimeBorder(false);
this.runtimeStyles();
var oRange=oEditor.document.body.createTextRange();
if(obj.arrUndoList[0][2]=="None")
{
oRange.moveToBookmark(obj.arrUndoList[0][1]);
oRange.select();
}
else if(obj.arrUndoList[0][2]=="Text")
{
oRange.moveToBookmark(obj.arrUndoList[0][1]);
oRange.select();
}
else if(obj.arrUndoList[0][2]=="Control")
{
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].selThis=="selThis")
{
var oSelRange=oEditor.document.body.createControlRange();
oSelRange.add(oEditor.document.all[i]);
oSelRange.select();
oEditor.document.all[i].removeAttribute("selThis",0);
}
}
}
for(var i=0;i<19;i++)obj.arrUndoList[i]=obj.arrUndoList[i+1];
obj.arrUndoList[19]=null;
realTime(this.oName);
}

var bOnSubmitOriginalSaved=false;
function REPLACE(idTextArea, sMode)
{
this.idTextArea=idTextArea;
var oTextArea=document.getElementById(idTextArea);
oTextArea.style.display="none";
var oForm=GetElement(oTextArea,"FORM");
if(oForm)
{
if(!bOnSubmitOriginalSaved)
{
onsubmit_original=oForm.onsubmit;

bOnSubmitOriginalSaved=true;
}
oForm.onsubmit = new Function("return onsubmit_new()");
}

var sContent=document.getElementById(idTextArea).value;
sContent=sContent.replace(/&/g,"&amp;");
sContent=sContent.replace(/</g,"&lt;");
sContent=sContent.replace(/>/g,"&gt;");

this.RENDER(sContent);
}
function onsubmit_new()
{
var sContent;
for(var i=0;i<oUtil.arrEditor.length;i++)
{
var oEdit=eval(oUtil.arrEditor[i]);

var oEditor=eval("idContent"+oEdit.oName);
var allSpans = oEditor.document.getElementsByTagName("SPAN");
for (var j=0; j<allSpans.length; j++)
{
if ((allSpans[j].innerHTML=="") && (allSpans[j].parentElement.children.length==1))
{
allSpans[j].innerHTML = "&nbsp;";
}
}

if(oEdit.mode=="XHTMLBody")sContent=oEdit.getXHTMLBody();
if(oEdit.mode=="XHTML")sContent=oEdit.getXHTML();
document.getElementById(oEdit.idTextArea).value=sContent;
}
if(onsubmit_original)return onsubmit_original();
}
function onsubmit_original(){}

var iconHeight;//icons related
function RENDER(sPreloadHTML)
{
iconHeight=this.iconHeight;//icons related

if(sPreloadHTML.substring(0,4)=="<!--" &&
sPreloadHTML.substring(sPreloadHTML.length-3)=="-->")
sPreloadHTML=sPreloadHTML.substring(4,sPreloadHTML.length-3);

if(sPreloadHTML.substring(0,4)=="<!--" &&
sPreloadHTML.substring(sPreloadHTML.length-6)=="--&gt;")
sPreloadHTML=sPreloadHTML.substring(4,sPreloadHTML.length-6);

sPreloadHTML=sPreloadHTML.replace(/&lt;/g,"<");
sPreloadHTML=sPreloadHTML.replace(/&gt;/g,">");
sPreloadHTML=sPreloadHTML.replace(/&amp;/g,"&");

if(this.cmdContentBlock!=";")this.btnContentBlock=true;
if(this.cmdInternalLink!=";")this.btnInternalLink=true;
if(this.cmdInternalImage!=";")this.btnInternalImage=true;
if(this.cmdCustomObject!=";")this.btnCustomObject=true;
if(this.arrCustomTag.length>0)this.btnCustomTag=true;
if(this.mode=="XHTMLBody"){this.btnXHTMLSource=true;this.btnXHTMLFullSource=false;}
if(this.mode=="XHTML"){this.btnXHTMLFullSource=true;this.btnXHTMLSource=false;}

var bUseFeature=false;
if(this.features.length>0)
{
bUseFeature=true;
for(var i=0;i<this.buttonMap.length;i++)
eval(this.oName+".btn"+this.buttonMap[i]+"=true");//ex: oEdit1.btnStyleAndFormatting=true (no problem), oEdit1.btn|=true (no problem), oEdit1.btnBRK=true (no problem)

this.btnTextFormatting=false;this.btnListFormatting=false;
this.btnBoxFormatting=false;this.btnParagraphFormatting=false;
this.btnCssText=false;this.btnCssBuilder=false;
for(var j=0;j<this.features.length;j++)
eval(this.oName+".btn"+this.features[j]+"=true");//ex: oEdit1.btnTextFormatting=true

for(var i=0;i<this.buttonMap.length;i++)
{
sButtonName=this.buttonMap[i];
bBtnExists=false;
for(var j=0;j<this.features.length;j++)
if(sButtonName==this.features[j])bBtnExists=true;//ada;

if(!bBtnExists)//tdk ada; set false
eval(this.oName+".btn"+sButtonName+"=false");//ex: oEdit1.btnBold=false, oEdit1.btn|=false (no problem), oEdit1.btnBRK=false (no problem)
}
//Remove:"TextFormatting","ListFormatting",dst.=>tdk perlu(krn diabaikan)
this.buttonMap=this.features;
}

this.preloadHTML=sPreloadHTML;
var sHTMLDropMenus="";
var sHTMLIcons="";
var sTmp="";

for(var i=0;i<this.buttonMap.length;i++)
{
sButtonName=this.buttonMap[i];
switch(sButtonName)
{
case "|":
sHTMLIcons+=this.writeBreakSpace();
break;
case "BRK":
sHTMLIcons+="</td></tr></table><table cellpadding=0 cellspacing=0><tr><td dir=ltr style='padding:0px'>";
break;
case "Paragraph":
if(this.btnParagraph)
{
sHTMLDropMenus+="<table id=dropParagraph"+this.oName+" cellpadding=0 cellspacing=0 "+
"style='z-index:1;display:none;position:absolute;border:#80788D 1px solid;"+
"cursor:default;background-color:#fbfbfd;' unselectable=on>";
for(var j=0;j<this.arrParagraph.length;j++)
{
sHTMLDropMenus+="<tr><td onclick=\""+this.oName+".applyParagraph('<"+this.arrParagraph[j][1]+">')\" "+
"style=\"padding:0;padding-left:5px;padding-right:5px;font-family:tahoma;color:black;\" "+
"onmouseover=\"this.style.backgroundColor='#708090';this.style.color='#FFFFFF';\" "+
"onmouseout=\"this.style.backgroundColor='';this.style.color='#000000';\" unselectable=on align=center>"+
"<"+this.arrParagraph[j][1]+" style=\"\margin-bottom:4px\" unselectable=on> "+
this.arrParagraph[j][0]+"</"+this.arrParagraph[j][1]+"></td></tr>";
}
sHTMLDropMenus+="</table>";
sHTMLIcons+=this.writeDropDown("btnParagraph"+this.oName,this.oName+".selectParagraph();"+this.oName+".dropShow(this,dropParagraph"+this.oName+")","btnParagraph.gif",getTxt("Paragraph"),77);
}
break;
case "Cut":
if(this.btnCut)sHTMLIcons+=this.writeIconStandard("btnCut"+this.oName,this.oName+".doCmd('Cut')","btnCut.gif",getTxt("Cut"));
break;
case "Copy":
if(this.btnCopy)sHTMLIcons+=this.writeIconStandard("btnCopy"+this.oName,this.oName+".doCmd('Copy')","btnCopy.gif",getTxt("Copy"));
break;
case "Paste":
if(this.btnPaste)sHTMLIcons+=this.writeIconStandard("btnPaste"+this.oName,this.oName+".doPaste()","btnPaste.gif",getTxt("Paste"));
break;
case "PasteWord":
if(this.btnPasteWord)sHTMLIcons+=this.writeIconStandard("btnPasteWord"+this.oName,this.oName+".hide();modelessDialogShow('"+this.scriptPath+"paste_word.htm',400,280)","btnPasteWord.gif",getTxt("Paste from Word"));
break;
case "PasteText":
if(this.btnPasteText)sHTMLIcons+=this.writeIconStandard("btnPasteText"+this.oName,this.oName+".doPasteText()","btnPasteText.gif",getTxt("Paste Text"));
break;
case "Undo":
if(this.btnUndo)sHTMLIcons+=this.writeIconStandard("btnUndo"+this.oName,this.oName+".doUndo()","btnUndo.gif",getTxt("Undo"));
break;
case "Bold":
if(this.btnBold)sHTMLIcons+=this.writeIconToggle("btnBold"+this.oName,this.oName+".applyBold()","btnBold.gif",getTxt("Bold"));
break;
case "Italic":
if(this.btnItalic)sHTMLIcons+=this.writeIconToggle("btnItalic"+this.oName,this.oName+".applyItalic()","btnItalic.gif",getTxt("Italic"));
break;
case "Underline":
if(this.btnUnderline)sHTMLIcons+=this.writeIconToggle("btnUnderline"+this.oName,this.oName+".applyLine('underline')","btnUnderline.gif",getTxt("Underline"));
break;
case "Superscript":
if(this.btnSuperscript)sHTMLIcons+=this.writeIconToggle("btnSuperscript"+this.oName,this.oName+".doCmd('Superscript')","btnSuperscript.gif",getTxt("Superscript"));
break;
case "Subscript":
if(this.btnSubscript)sHTMLIcons+=this.writeIconToggle("btnSubscript"+this.oName,this.oName+".doCmd('Subscript')","btnSubscript.gif",getTxt("Subscript"));
break;
case "JustifyLeft":
if(this.btnJustifyLeft)sHTMLIcons+=this.writeIconToggle("btnJustifyLeft"+this.oName,this.oName+".applyJustifyLeft()","btnLeft.gif",getTxt("Justify Left"));
break;
case "JustifyCenter":
if(this.btnJustifyCenter)sHTMLIcons+=this.writeIconToggle("btnJustifyCenter"+this.oName,this.oName+".applyJustifyCenter()","btnCenter.gif",getTxt("Justify Center"));
break;
case "JustifyRight":
if(this.btnJustifyRight)sHTMLIcons+=this.writeIconToggle("btnJustifyRight"+this.oName,this.oName+".applyJustifyRight()","btnRight.gif",getTxt("Justify Right"));
break;
case "Numbering":
if(this.btnNumbering)sHTMLIcons+=this.writeIconToggle("btnNumbering"+this.oName,this.oName+".applyNumbering()","btnNumber.gif",getTxt("Numbering"));
break;
case "Bullets":
if(this.btnBullets)sHTMLIcons+=this.writeIconToggle("btnBullets"+this.oName,this.oName+".applyBullets()","btnList.gif",getTxt("Bullets"));
break;
case "CustomTag":
if(this.btnCustomTag)
{
sHTMLDropMenus+="<table id=dropCustomTag"+this.oName+" cellpadding=0 cellspacing=0 "+
"style='z-index:1;display:none;position:absolute;border:#80788D 1px solid;"+
"cursor:default;background-color:#fbfbfd;' unselectable=on><tr><td valign=top style='padding:0px;'>";

sHTMLDropMenus+="<table cellpadding=0 cellspacing=0>";
for(var j=0;j<this.arrCustomTag.length;j++)
{
sHTMLDropMenus+="<tr><td onclick=\""+this.oName+".insertCustomTag("+j+")\" "+
"style=\"padding:1px;padding-left:5px;padding-right:5px;font-family:tahoma;font-size:11px;color:black;\" "+
"onmouseover=\"this.style.backgroundColor='#708090';this.style.color='#FFFFFF';\" "+
"onmouseout=\"this.style.backgroundColor='';this.style.color='#000000';\" unselectable=on align=center>"+
this.arrCustomTag[j][0]+"</td></tr>";

if(j==14||j==29||j==44||j==59||j==74||j==89||j==104)
{
if(j!=this.arrCustomTag.length-1)
{
sHTMLDropMenus+="</table>";
sHTMLDropMenus+="</td><td valign=top style='padding:0px;border-left:#80788D 1 solid'>";//main
sHTMLDropMenus+="<table cellpadding=0 cellspacing=0>";
}
}
}
sHTMLDropMenus+="</table>";
sHTMLDropMenus+="</td></tr></table>";
sHTMLIcons+=this.writeDropDown("btnCustomTag"+this.oName,this.oName+".dropShow(this,dropCustomTag"+this.oName+")","btnCustomTag.gif",getTxt("Tags"),60);
}
break;
case "ContentBlock":
if(this.btnContentBlock)sHTMLIcons+=this.writeIconStandard("btnContentBlock"+this.oName,this.oName+".hide();"+this.cmdContentBlock,"btnContentBlock.gif",getTxt("Content Block"));
break;
case "Table":
if(this.btnTable)
{
sHTMLDropMenus+="<table id=dropTable"+this.oName+" cellpadding=0 cellspacing=0 "+
"style='z-index:1;display:none;position:absolute;border:#80788D 1px solid;"+
"cursor:default;background-color:#fbfbfd;' unselectable=on>"+
"<tr><td id=\"mnuTableSize"+this.oName+"\" onclick=\"if(this.style.color!='gray'){modelessDialogShow('"+this.scriptPath+"table_size.htm',240,262);"+
"dropTable"+this.oName+".style.display='none'}\""+
"style=\"padding:2px;padding-top:1px;font-family:Tahoma;font-size:11px;color:black\""+
"onmouseover=\"if(this.style.color!='gray'){this.style.backgroundColor='#708090';this.style.color='#FFFFFF';}\""+
"onmouseout=\"if(this.style.color!='gray'){this.style.backgroundColor='';this.style.color='#000000';}\" unselectable=on>"+getTxt("Table Size")+" </td></tr>"+
"<tr><td id=\"mnuTableEdit"+this.oName+"\" onclick=\"if(this.style.color!='gray'){modelessDialogShow('"+this.scriptPath+"table_edit.htm',358,380);"+
"dropTable"+this.oName+".style.display='none'}\""+
"style=\"padding:2px;padding-top:1px;font-family:Tahoma;font-size:11px;color:black\""+
"onmouseover=\"if(this.style.color!='gray'){this.style.backgroundColor='#708090';this.style.color='#FFFFFF';}\""+
"onmouseout=\"if(this.style.color!='gray'){this.style.backgroundColor='';this.style.color='#000000';}\" unselectable=on>"+getTxt("Edit Table")+" </td></tr>"+
"<tr><td id=\"mnuCellEdit"+this.oName+"\" onclick=\"if(this.style.color!='gray'){modelessDialogShow('"+this.scriptPath+"table_editCell.htm',427,440);"+
"dropTable"+this.oName+".style.display='none'}\""+
"style=\"padding:2px;padding-top:1px;font-family:Tahoma;font-size:11px;color:black\""+
"onmouseover=\"if(this.style.color!='gray'){this.style.backgroundColor='#708090';this.style.color='#FFFFFF';}\""+
"onmouseout=\"if(this.style.color!='gray'){this.style.backgroundColor='';this.style.color='#000000';}\" unselectable=on>"+getTxt("Edit Cell")+" </td></tr>"+
"</table>";

sHTMLDropMenus+="<table width=195 id=dropTableCreate"+this.oName+" onmouseout='doOut_TabCreate();event.cancelBubble=true' style='position:absolute;display:none;cursor:default;background:#f3f3f8;border:#8a867a 1px solid;' cellpadding=0 cellspacing=2 border=0 unselectable=on>";
for(var m=0;m<8;m++)
{
sHTMLDropMenus+="<tr>";
for(var n=0;n<8;n++)
{
sHTMLDropMenus+="<td onclick='"+this.oName+".doClick_TabCreate()' onmouseover='doOver_TabCreate()' style='background:#ffffff;font-size:1px;border:#8a867a 1px solid;width:20px;height:20px;' unselectable=on>&nbsp;</td>";
}
sHTMLDropMenus+="</tr>";
}
sHTMLDropMenus+="<tr><td colspan=8 onclick=\""+this.oName+".hide();modelessDialogShow('"+this.scriptPath+"table_insert.htm',300,322);\" onmouseover=\"this.innerText='"+getTxt("Advanced Table Insert")+"';this.style.border='#777777 1px solid';this.style.backgroundColor='#8d9aa7';this.style.color='#ffffff'\" onmouseout=\"this.style.border='#f3f3f8 1px solid';this.style.backgroundColor='#f3f3f8';this.style.color='#000000'\" align=center style='font-family:verdana;font-size:10px;font-color:black;border:#f3f3f8 1px solid;' unselectable=on>"+getTxt("Advanced Table Insert")+"</td></tr>";
sHTMLDropMenus+="</table>";

sHTMLIcons+=this.writeIconStandard("btnTable"+this.oName,this.oName+".dropShow(this,dropTableCreate"+this.oName+")","btnTable.gif",getTxt("Insert Table"));
sHTMLIcons+=this.writeIconStandard("btnTableEdit"+this.oName,this.oName+".dropShow(this,dropTable"+this.oName+")","btnTableEdit.gif",getTxt("Edit Table/Cell"));
}
break;
case "Guidelines":
if(this.btnGuidelines)sHTMLIcons+=this.writeIconStandard("btnGuidelines"+this.oName,this.oName+".runtimeBorder(true)","btnGuideline.gif",getTxt("Show/Hide Guidelines"));
break;
case "Absolute":
if(this.btnAbsolute)sHTMLIcons+=this.writeIconStandard("btnAbsolute"+this.oName,this.oName+".makeAbsolute()","btnAbsolute.gif",getTxt("Absolute"));
break;
case "Characters":
if(this.btnCharacters)sHTMLIcons+=this.writeIconStandard("btnCharacters"+this.oName,this.oName+".hide();modelessDialogShow('"+this.scriptPath+"characters.htm',750,202)","btnSymbol.gif",getTxt("Special Characters"));
break;
case "XHTMLSource":
if(this.btnXHTMLSource)sHTMLIcons+=this.writeIconStandard("btnXHTMLSource"+this.oName,"setActiveEditor('"+this.oName+"');"+this.oName+".hide();modalDialogShow('"+this.scriptPath+"source_xhtml.htm',700,550);","btnSource.gif",getTxt("View/Edit Source"));
break;
default:
for(j=0;j<this.arrCustomButtons.length;j++)
{
if(sButtonName==this.arrCustomButtons[j][0])
{
sCbName=this.arrCustomButtons[j][0];
//sCbCommand=this.arrCustomButtons[j][1];
sCbCaption=this.arrCustomButtons[j][2];
sCbImage=this.arrCustomButtons[j][3];
if(this.arrCustomButtons[j][4])
sHTMLIcons+=this.writeIconStandard("btn"+sCbName+this.oName,"eval("+this.oName+".arrCustomButtons["+j+"][1])",sCbImage,sCbCaption,this.arrCustomButtons[j][4]);
else
sHTMLIcons+=this.writeIconStandard("btn"+sCbName+this.oName,"eval("+this.oName+".arrCustomButtons["+j+"][1])",sCbImage,sCbCaption);
}
}
break;
}
}

var sHTML="";

sHTML+="<iframe name=idFixZIndex"+this.oName+" id=idFixZIndex"+this.oName+" frameBorder=0 style='display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)' src='"+this.scriptPath+"blank.gif' ></iframe>"; //src='javascript:;'
sHTML+="<table id=idArea"+this.oName+" name=idArea"+this.oName+" border=0 "+
"cellpadding=0 cellspacing=0 width='"+this.width+"' height='"+this.height+"'>"+
"<tr><td colspan=2 style=\"position:relative;padding:0px;padding-left:1;border:#cfcfcf 1px solid;border-bottom:0;background:url('"+this.scriptPath+"icons/bg.gif')\">"+
"<table cellpadding=0 cellspacing=0><tr><td dir=ltr style='padding:0px'>"+
sHTMLIcons+
"</td></tr></table>"+
"</td></tr>"+
"<tr id=idTagSelTopRow"+this.oName+"><td colspan=2 id=idTagSelTop"+this.oName+" height=0 style='padding:0px'></td></tr>";

sHTML+="<tr><td colspan=2 valign=top height=100% style='padding:0px;background:white'>";

sHTML+="<table cellpadding=0 cellspacing=0 width=100% height=100%><tr><td width=100% height=100% style='padding:0px'>";//StyleSelect

if(this.IsSecurityRestricted)
sHTML+="<iframe security='restricted' style='width:100%;height:100%;' src='"+this.scriptPath+"blank.gif'"+
" name=idContent"+ this.oName + " id=idContent"+this.oName+
" contentEditable=true></iframe>";//prohibit running ActiveX controls
else
sHTML+="<iframe style='width:100%;height:100%;' src='"+this.scriptPath+"blank.gif'"+
" name=idContent"+ this.oName + " id=idContent"+this.oName+
" contentEditable=true></iframe>";

sHTML+="<iframe style='width:1px;height:1px;overflow:auto;' src='"+this.scriptPath+"blank.gif'"+
" name=idContentWord"+ this.oName +" id=idContentWord"+ this.oName+
" contentEditable=true onfocus='"+this.oName+".hide()'></iframe>";

sHTML+="</td><td id=idStyles"+this.oName+" style='padding:0px;background:#E9E8F2' valign=top></td></tr></table>"//StyleSelect

sHTML+="</td></tr>";
sHTML+="<tr id=idTagSelBottomRow"+this.oName+"><td colspan=2 id=idTagSelBottom"+this.oName+" style='padding:0px;'></td></tr>";
sHTML+="</table>";

sHTML+=sHTMLDropMenus;//dropdown

sHTML+="<input type=submit name=iwe_btnSubmit"+this.oName+" id=iwe_btnSubmit"+this.oName+" value=SUBMIT style='display:none' >";//hidden submit button

document.write(sHTML);

if(this.useTagSelector)
{
if(this.TagSelectorPosition=="bottom")this.TagSelectorPosition="top";
else this.TagSelectorPosition="bottom";
this.moveTagSelector()
}

/*var oWord=eval("idContentWord"+this.oName);
oWord.document.designMode="on";
oWord.document.open("text/html","replace");
oWord.document.write("<html><head></head><body></body></html>");
oWord.document.close();
oWord.document.body.contentEditable=true;*/

oUtil.oName=this.oName;//default active editor
oUtil.oEditor=eval("idContent"+this.oName);
oUtil.obj=eval(this.oName);

oUtil.arrEditor.push(this.oName);

if(this.btnTable)
{
this.arrElm[0]=this.getElm("btnTableEdit");
this.arrElm[1]=this.getElm("mnuTableSize");
this.arrElm[2]=this.getElm("mnuTableEdit");
this.arrElm[3]=this.getElm("mnuCellEdit");
}
if(this.btnParagraph)this.arrElm[4]=this.getElm("btnParagraph");
if(this.btnFontName)this.arrElm[5]=this.getElm("btnFontName");
if(this.btnFontSize)this.arrElm[6]=this.getElm("btnFontSize");
if(this.btnCut)this.arrElm[7]=this.getElm("btnCut");
if(this.btnCopy)this.arrElm[8]=this.getElm("btnCopy");
if(this.btnPaste)this.arrElm[9]=this.getElm("btnPaste");
if(this.btnPasteWord)this.arrElm[10]=this.getElm("btnPasteWord");
if(this.btnPasteText)this.arrElm[11]=this.getElm("btnPasteText");
if(this.btnUndo)this.arrElm[12]=this.getElm("btnUndo");
if(this.btnBold)this.arrElm[14]=this.getElm("btnBold");
if(this.btnItalic)this.arrElm[15]=this.getElm("btnItalic");
if(this.btnUnderline)this.arrElm[16]=this.getElm("btnUnderline");
if(this.btnSuperscript)this.arrElm[18]=this.getElm("btnSuperscript");
if(this.btnSubscript)this.arrElm[19]=this.getElm("btnSubscript");
if(this.btnNumbering)this.arrElm[20]=this.getElm("btnNumbering");
if(this.btnBullets)this.arrElm[21]=this.getElm("btnBullets");
if(this.btnJustifyLeft)this.arrElm[22]=this.getElm("btnJustifyLeft");
if(this.btnJustifyCenter)this.arrElm[23]=this.getElm("btnJustifyCenter");
if(this.btnJustifyRight)this.arrElm[24]=this.getElm("btnJustifyRight");
if(this.btnForeColor)this.arrElm[30]=this.getElm("btnForeColor");
if(this.btnBackColor)this.arrElm[31]=this.getElm("btnBackColor");
if(this.btnLine)this.arrElm[32]=this.getElm("btnLine");

//Normalize button position if the editor is placed in relative positioned element
eval("idArea"+this.oName).style.position="absolute";
window.setTimeout("eval('idArea"+this.oName+"').style.position='';",1);

var arrA = String(this.preloadHTML).match(/<HTML[^>]*>/ig);
if(arrA)
{
this.loadHTML("");
window.setTimeout(this.oName+".putHTML("+this.oName+".preloadHTML)",0);
}
else
{
this.loadHTML(sPreloadHTML)
}
}

function iwe_getElm(s)
{
return document.getElementById(s+this.oName)
}

function loadHTML(sHTML)//hanya utk first load.
{
var oEditor=eval("idContent"+this.oName);

var sStyle="";
if(this.css!="") sStyle="<link href='"+this.css+"' rel='stylesheet' type='text/css'>"

var oDoc=oEditor.document.open("text/html","replace");
if(this.publishingPath!="")
{
var arrA = String(this.preloadHTML).match(/<base[^>]*>/ig);
if(!arrA)
{
sHTML=this.docType+"<HTML><HEAD><BASE HREF=\""+this.publishingPath+"\"/>"+this.headContent+sStyle+"</HEAD><BODY contentEditable=true>" + sHTML + "</BODY></HTML>";
}
}
else
{
sHTML=this.docType+"<HTML><HEAD>"+this.headContent+sStyle+"</HEAD><BODY contentEditable=true>"+sHTML+"</BODY></HTML>";
}
oDoc.write(sHTML);
oDoc.close();
oEditor.document.body.contentEditable=true;
oEditor.document.execCommand("2D-Position", true, true);//make focus
oEditor.document.execCommand("MultipleSelection", true, true);//make focus
oEditor.document.execCommand("LiveResize", true, true);//make focus
oEditor.document.body.onkeyup = new Function("editorDoc_onkeyup('"+this.oName+"')");
oEditor.document.body.onmouseup = new Function("editorDoc_onmouseup('"+this.oName+"')");
oEditor.document.body.onkeydown=new Function("doKeyPress(eval('idContent"+this.oName+"').event,'"+this.oName+"')");
this.runtimeBorder(false);
this.runtimeStyles();
oEditor.document.body.onpaste = new Function(this.oName+".doOnPaste()");
oEditor.document.body.oncut = new Function(this.oName+".saveForUndo()");
oEditor.document.body.style.lineHeight="1.2";
oEditor.document.body.style.lineHeight="";
if(this.initialRefresh)
{
oEditor.document.execCommand("SelectAll");
window.setTimeout("eval('idContentWord"+this.oName+"').document.execCommand('SelectAll');",0);
}
if(this.arrStyle.length>0)
{
var oElement=oEditor.document.createElement("<STYLE>");
var n=oEditor.document.styleSheets.length;
oEditor.document.documentElement.childNodes[0].appendChild(oElement);
for(var i=0;i<this.arrStyle.length;i++)
{
selector=this.arrStyle[i][0];
style=this.arrStyle[i][3];
oEditor.document.styleSheets(n).addRule(selector,style);
}
}

this.cleanDeprecated();
}
function putHTML(sHTML)//used by source editor
{
var oEditor=eval("idContent"+this.oName);
var arrA=String(sHTML).match(/<!DOCTYPE[^>]*>/ig);
if(arrA)
for(var i=0;i<arrA.length;i++)
{
this.docType=arrA[i];
}
else this.docType="";//back to default value
var arrB=String(sHTML).match(/<HTML[^>]*>/ig);
if(arrB)
for(var i=0;i<arrB.length;i++)
{
s=arrB[i];
s=s.replace(/\"[^\"]*\"/ig,function(x){
x=x.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/'/g, "&apos;").replace(/\s+/ig,"#_#");
return x});
s=s.replace(/<([^ >]*)/ig,function(x){return x.toLowerCase()});
s=s.replace(/ ([^=]+)=([^" >]+)/ig," $1=\"$2\"");
s=s.replace(/ ([^=]+)=/ig,function(x){return x.toLowerCase()});
s=s.replace(/#_#/ig," ");
this.html=s;
}
else this.html="<html>";//back to default value

if(this.publishingPath!="")
{
var arrA = sHTML.match(/<base[^>]*>/ig);
if(!arrA)
{
sHTML="<BASE HREF=\""+this.publishingPath+"\"/>"+sHTML;
}
}

var oDoc=oEditor.document.open("text/html","replace");
oDoc.write(sHTML);
oDoc.close();
oEditor.document.body.contentEditable=true;
oEditor.document.execCommand("2D-Position",true,true);
oEditor.document.execCommand("MultipleSelection",true,true);
oEditor.document.execCommand("LiveResize",true,true);
oEditor.document.body.onkeyup=new Function("editorDoc_onkeyup('"+this.oName+"')");
oEditor.document.body.onmouseup=new Function("editorDoc_onmouseup('"+this.oName+"')");
oEditor.document.body.onkeydown=new Function("doKeyPress(eval('idContent"+this.oName+"').event,'"+this.oName+"')");
this.runtimeBorder(false);
this.runtimeStyles();
this.cleanDeprecated();
}
function getTextBody()
{
var oEditor=eval("idContent"+this.oName);
return oEditor.document.body.innerText;
}
function getHTML()
{
var oEditor=eval("idContent"+this.oName);
this.cleanDeprecated();

sHTML=oEditor.document.documentElement.outerHTML;
sHTML=String(sHTML).replace(/ contentEditable=true/g,"");
sHTML = String(sHTML).replace(/\<PARAM NAME=\"Play\" VALUE=\"0\">/ig,"<PARAM NAME=\"Play\" VALUE=\"-1\">");
sHTML=this.docType+sHTML;//restore doctype (if any)
return sHTML;
}
function getHTMLBody()
{
var oEditor=eval("idContent"+this.oName);
this.cleanDeprecated();

sHTML=oEditor.document.body.innerHTML;
sHTML=String(sHTML).replace(/ contentEditable=true/g,"");
sHTML = String(sHTML).replace(/\<PARAM NAME=\"Play\" VALUE=\"0\">/ig,"<PARAM NAME=\"Play\" VALUE=\"-1\">");
return sHTML;
}
var sBaseHREF="";
function getXHTML()
{
var oEditor=eval("idContent"+this.oName);
this.cleanDeprecated();
sHTML=oEditor.document.documentElement.outerHTML;
var arrTmp=sHTML.match(/<BASE([^>]*)>/ig);
if(arrTmp!=null)sBaseHREF=arrTmp[0];
for(var i=0;i<oEditor.document.all.length;i++)
if(oEditor.document.all[i].tagName=="BASE")oEditor.document.all[i].removeNode();
for(var i=0;i<oEditor.document.all.length;i++)
if(oEditor.document.all[i].tagName=="BASE")oEditor.document.all[i].removeNode();
sBaseHREF=sBaseHREF.replace(/<([^ >]*)/ig,function(x){return x.toLowerCase()});
sBaseHREF=sBaseHREF.replace(/ [^=]+="[^"]+"/ig,function(x){
x=x.replace(/\s+/ig,"#_#");
x=x.replace(/^#_#/," ");
return x});
sBaseHREF=sBaseHREF.replace(/ ([^=]+)=([^" >]+)/ig," $1=\"$2\"");
sBaseHREF=sBaseHREF.replace(/ ([^=]+)=/ig,function(x){return x.toLowerCase()});
sBaseHREF=sBaseHREF.replace(/#_#/ig," ");
sBaseHREF=sBaseHREF.replace(/>$/ig," \/>").replace(/\/ \/>$/ig,"\/>");
sHTML=recur(oEditor.document.documentElement,"");
sHTML=this.docType+this.html+sHTML+"\n</html>";//restore doctype (if any) & html
sHTML=sHTML.replace(/<\/title>/,"<\/title>"+sBaseHREF);//restore base href
return sHTML;
}
function getXHTMLBody()
{
var oEditor=eval("idContent"+this.oName);
this.cleanDeprecated();
sHTML=oEditor.document.documentElement.outerHTML;
var arrTmp=sHTML.match(/<BASE([^>]*)>/ig);
if(arrTmp!=null)sBaseHREF=arrTmp[0];
for(var i=0;i<oEditor.document.all.length;i++)
if(oEditor.document.all[i].tagName=="BASE")oEditor.document.all[i].removeNode();
for(var i=0;i<oEditor.document.all.length;i++)
if(oEditor.document.all[i].tagName=="BASE")oEditor.document.all[i].removeNode();
sHTML=recur(oEditor.document.body,"");
return sHTML;
}
function ApplyExternalStyle(oName)
{
var oEditor=eval("idContent"+oName);
var sTmp="";
for(var j=0;j<oEditor.document.styleSheets.length;j++)
{
var myStyle=oEditor.document.styleSheets(j);
for(var i=0;i<myStyle.rules.length;i++)
{
sSelector=myStyle.rules.item(i).selectorText;
sCssText=myStyle.rules.item(i).style.cssText.replace(/"/g,"&quot;");
var itemCount = sSelector.split(".").length;
if(itemCount>1) 
{
sCaption=sSelector.split(".")[1];
sTmp+=",[\""+sSelector+"\",true,\""+sCaption+"\",\""+ sCssText + "\"]";
}
else sTmp+=",[\""+sSelector+"\",false,\"\",\""+ sCssText + "\"]";
}
}
var arrStyle = eval("["+sTmp.substr(1)+"]"); 
eval(oName).arrStyle=arrStyle;//Update arrStyle property
}
function doApplyStyle(oName,sClassName)
{
if(!eval(oName).checkFocus())return;
var oEditor=eval("idContent"+oName);
var oSel=oEditor.document.selection.createRange();
eval(oName).saveForUndo();
if(oUtil.activeElement)
{
oElement=oUtil.activeElement;
oElement.className=sClassName;
}
else if (oSel.parentElement)
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
oElement.className=sClassName;
}
else
{
eval(oName).applySpanStyle([],sClassName);
}
}
else 
{
oElement=oSel.item(0);
oElement.className=sClassName;
}
realTime(oName);
}
function openStyleSelect()
{
if(!this.isCssLoaded)ApplyExternalStyle(this.oName);
this.isCssLoaded=true;//make only 1 call to ApplyExternalStyle()
var bShowStyles=false;
var idStyles=document.getElementById("idStyles"+this.oName);
if(idStyles.innerHTML!="")
{
if(idStyles.style.display=="")
idStyles.style.display="none";
else
idStyles.style.display="";
return;
}
idStyles.style.display="";
var h=document.getElementById("idContent"+this.oName).offsetHeight-27;
var arrStyle=this.arrStyle;
var sHTML="";
sHTML+="<div unselectable=on style='width:200px;margin:1px;margin-top:0;margin-right:2px;' align=right>"
sHTML+="<table style='margin-right:1px;margin-bottom:3px;width:14px;height:14px;background:#E9E8F2;' cellpadding=0 cellspacing=0 unselectable=on>"+
"<tr><td onclick=\""+this.oName+".openStyleSelect();\" onmouseover=\"this.style.border='#708090 1px solid';this.style.color='white';this.style.backgroundColor='9FA7BB'\" onmouseout=\"this.style.border='white 1px solid';this.style.color='black';this.style.backgroundColor=''\" style=\"cursor:default;padding:1px;border:white 1px solid;font-family:verdana;font-size:10px;font-color:black;line-height:9px;\" align=center valign=top unselectable=on>x</td></tr>"+
"</table></div>";
var sBody="";
for(var i=0;i<arrStyle.length;i++)
{
sSelector=arrStyle[i][0];
if(sSelector=="BODY")sBody=arrStyle[i][3];
}
sHTML+="<div unselectable=on style='overflow:auto;width:200px;height:"+h+"px;padding-left:3px;'>";
sHTML+="<table name='tblStyles"+this.oName+"' id='tblStyles"+this.oName+"' cellpadding=0 cellspacing=0 style='background:#fcfcfc;"+sBody+";width:100%;height:100%;margin:0;'>";
for(var i=0;i<arrStyle.length;i++)
{
sSelector=arrStyle[i][0];
isOnSelection=arrStyle[i][1];
sCssText=arrStyle[i][3];
sCaption=arrStyle[i][2];
if(isOnSelection)
{
if(sSelector.split(".").length>1)//sudah pasti
{
var tmpSelector = sSelector;
if (sSelector.indexOf(":")>0) tmpSelector = sSelector.substring(0, sSelector.indexOf(":"));
bShowStyles=true;
sHTML+="<tr style=\"cursor:default\" onmouseover=\"if(this.style.marginRight!='1px'){this.style.background='"+this.styleSelectionHoverBg+"';this.style.color='"+this.styleSelectionHoverFg+"'}\" onmouseout=\"if(this.style.marginRight!='1px'){this.style.background='';this.style.color=''}\">";
sHTML+="<td unselectable=on onclick=\"doApplyStyle('"+this.oName+"','"+tmpSelector.split(".")[1]+"')\" style='padding:2px;'>";
if(sSelector.split(".")[0]=="")
sHTML+="<span unselectable=on style=\""+sCssText+";margin:0;\">"+sCaption+"</span>";
else
sHTML+="<span unselectable=on style=\""+sCssText+";margin:0;\">"+sSelector+"</span>";
sHTML+="</td></tr>";
}
}
}
sHTML+="<tr><td height=50%>&nbsp;</td></tr></table></div>";//50% spy di style selector tdk keloar scroll (kalau ada doctype)
if(bShowStyles)document.getElementById("idStyles"+this.oName).innerHTML=sHTML;
else{alert("No stylesheet found.")}
}
/*function editorDoc_onkeydown(oName)
{
realTime(oName);
}*/
function editorDoc_onkeyup(oName)
{
if(eval(oName).isAfterPaste)
{
eval(oName).cleanDeprecated();
eval(oName).runtimeBorder(false);
eval(oName).runtimeStyles();
eval(oName).isAfterPaste=false;
}
realTime(oName);
}
function editorDoc_onmouseup(oName)
{
oUtil.activeElement=null;//focus ke editor, jgn pakai selection dari tag selector
oUtil.oName=oName;oUtil.oEditor=eval("idContent"+oName);oUtil.obj=eval(oName);eval(oName).hide();//pengganti onfocus
realTime(oName);
}
function setActiveEditor(oName)
{
oUtil.oName=oName;
oUtil.oEditor=eval("idContent"+oName);
oUtil.obj=eval(oName);
}
var arrTmp=[];
function GetElement(oElement,sMatchTag)//Used in realTime() only.
{
while (oElement!=null&&oElement.tagName!=sMatchTag)
{
if(oElement.tagName=="BODY")return null;
oElement=oElement.parentElement;
}
return oElement;
}
var arrTmp2=[];//TAG SELECTOR
function realTime(oName,bTagSel)
{
if(!eval(oName).checkFocus())return;
var oEditor=eval("idContent"+oName);
var oSel=oEditor.document.selection.createRange();
var obj=eval(oName);
if(obj.btnTable)
{
obj.arrElm[1].style.color="gray";
obj.arrElm[2].style.color="gray";
obj.arrElm[3].style.color="gray";
var oTable=(oSel.parentElement!=null?GetElement(oSel.parentElement(),"TABLE"):GetElement(oSel.item(0),"TABLE"));
if (oTable)
{
obj.arrElm[1].style.color="black";
obj.arrElm[2].style.color="black";
obj.arrElm[3].style.color="gray";
makeEnableNormal(obj.arrElm[0]);
}
else makeDisabled(obj.arrElm[0]);
var oTD=(oSel.parentElement!=null?GetElement(oSel.parentElement(),"TD"):GetElement(oSel.item(0),"TD"));
if (oTD)
{
obj.arrElm[1].style.color="black";
obj.arrElm[2].style.color="black";
obj.arrElm[3].style.color="black";
}
}
if(obj.btnParagraph)
{
if(oEditor.document.queryCommandEnabled("FormatBlock"))
makeEnableNormal(obj.arrElm[4]);
else makeDisabled(obj.arrElm[4]);
}
if(obj.btnFontName)
{
if(oEditor.document.queryCommandEnabled("FontName"))
makeEnableNormal(obj.arrElm[5]);
else makeDisabled(obj.arrElm[5]);
}
if(obj.btnFontSize)
{
if(oEditor.document.queryCommandEnabled("FontSize"))
makeEnableNormal(obj.arrElm[6]);
else makeDisabled(obj.arrElm[6]);
}
if(obj.btnCut)
{
if(oEditor.document.queryCommandEnabled("Cut"))
makeEnableNormal(obj.arrElm[7]);
else makeDisabled(obj.arrElm[7]);
}
if(obj.btnCopy)
{
if(oEditor.document.queryCommandEnabled("Copy"))
makeEnableNormal(obj.arrElm[8]);
else makeDisabled(obj.arrElm[8]);
}
if(obj.btnPaste)
{
if(oEditor.document.queryCommandEnabled("Paste"))
makeEnableNormal(obj.arrElm[9]);
else makeDisabled(obj.arrElm[9]);
}
if(obj.btnPasteWord)
{
if(oEditor.document.queryCommandEnabled("Paste"))
makeEnableNormal(obj.arrElm[10]);
else makeDisabled(obj.arrElm[10]);
}
if(obj.btnPasteText)
{
if(oEditor.document.queryCommandEnabled("Paste"))
makeEnableNormal(obj.arrElm[11]);
else makeDisabled(obj.arrElm[11]);
}
if(obj.btnUndo)
{
if(!obj.arrUndoList[0])makeDisabled(obj.arrElm[12]);
else makeEnableNormal(obj.arrElm[12]);
}
if(obj.btnBold)
{
if(oEditor.document.queryCommandEnabled("Bold"))
{
if(oEditor.document.queryCommandState("Bold"))
makeEnablePushed(obj.arrElm[14]);
else makeEnableNormal(obj.arrElm[14]);
}
else makeDisabled(obj.arrElm[14]);
}
if(obj.btnItalic)
{
if(oEditor.document.queryCommandEnabled("Italic"))
{
if(oEditor.document.queryCommandState("Italic"))
makeEnablePushed(obj.arrElm[15]);
else makeEnableNormal(obj.arrElm[15]);
}
else makeDisabled(obj.arrElm[15]);
}
if(obj.btnUnderline)
{
if(oEditor.document.queryCommandEnabled("Underline"))
{
if(oEditor.document.queryCommandState("Underline"))
makeEnablePushed(obj.arrElm[16]);
else makeEnableNormal(obj.arrElm[16]);
}
else makeDisabled(obj.arrElm[16]);
}
if(obj.btnSuperscript)
{
if(oEditor.document.queryCommandEnabled("Superscript"))
{
if(oEditor.document.queryCommandState("Superscript"))
makeEnablePushed(obj.arrElm[18]);
else makeEnableNormal(obj.arrElm[18]);
}
else makeDisabled(obj.arrElm[18]);
}
if(obj.btnSubscript)
{
if(oEditor.document.queryCommandEnabled("Subscript"))
{
if(oEditor.document.queryCommandState("Subscript"))
makeEnablePushed(obj.arrElm[19]);
else makeEnableNormal(obj.arrElm[19]);
}
else makeDisabled(obj.arrElm[19]);
}
if(obj.btnNumbering)
{
if(oEditor.document.queryCommandEnabled("InsertOrderedList"))
{
if(oEditor.document.queryCommandState("InsertOrderedList"))
makeEnablePushed(obj.arrElm[20]);
else makeEnableNormal(obj.arrElm[20]);
}
else makeDisabled(obj.arrElm[20]);
}
if(obj.btnBullets)
{
if(oEditor.document.queryCommandEnabled("InsertUnorderedList"))
{
if(oEditor.document.queryCommandState("InsertUnorderedList"))
makeEnablePushed(obj.arrElm[21]);
else makeEnableNormal(obj.arrElm[21]);
}
else makeDisabled(obj.arrElm[21]);
}
if(obj.btnJustifyLeft)
{
if(oEditor.document.queryCommandEnabled("JustifyLeft"))
{
if(oEditor.document.queryCommandState("JustifyLeft"))
makeEnablePushed(obj.arrElm[22]);
else makeEnableNormal(obj.arrElm[22]);
}
else makeDisabled(obj.arrElm[22]);
}
if(obj.btnJustifyCenter)
{
if(oEditor.document.queryCommandEnabled("JustifyCenter"))
{
if(oEditor.document.queryCommandState("JustifyCenter"))
makeEnablePushed(obj.arrElm[23]);
else makeEnableNormal(obj.arrElm[23]);
}
else makeDisabled(obj.arrElm[23]);
}
if(obj.btnJustifyRight)
{
if(oEditor.document.queryCommandEnabled("JustifyRight"))
{
if(oEditor.document.queryCommandState("JustifyRight"))
makeEnablePushed(obj.arrElm[24]);
else makeEnableNormal(obj.arrElm[24]);
}
else makeDisabled(obj.arrElm[24]);
}
if(oSel.parentElement)
{
if(obj.btnForeColor)makeEnableNormal(obj.arrElm[30]);
if(obj.btnBackColor)makeEnableNormal(obj.arrElm[31]);
if(obj.btnLine)makeEnableNormal(obj.arrElm[32]);
}
else
{
if(obj.btnForeColor)makeDisabled(obj.arrElm[30]);
if(obj.btnBackColor)makeDisabled(obj.arrElm[31]);
if(obj.btnLine)makeDisabled(obj.arrElm[32]);
}
try{oUtil.onSelectionChanged()}catch(e){;}
try{obj.onSelectionChanged()}catch(e){;}
var idStyles=document.getElementById("idStyles"+oName);
if(idStyles.innerHTML!="")
{
var oElement;
if(oUtil.activeElement)
oElement=oUtil.activeElement;
else
{
if (oSel.parentElement)oElement=oSel.parentElement();
else oElement=oSel.item(0);
}
var sCurrClass=oElement.className;
var oRows=document.getElementById("tblStyles"+oName).rows;
for(var i=0;i<oRows.length-1;i++)
{
sClass=oRows[i].childNodes[0].innerText;
if(sClass.split(".").length>1 && sClass!="")sClass=sClass.split(".")[1];
if(sCurrClass==sClass)
{
oRows[i].style.marginRight="1px";
oRows[i].style.backgroundColor=obj.styleSelectionHoverBg;
oRows[i].style.color=obj.styleSelectionHoverFg;
}
else
{
oRows[i].style.marginRight="";
oRows[i].style.backgroundColor="";
oRows[i].style.color="";
}
}
}
if(obj.useTagSelector && !bTagSel)
{
if (oSel.parentElement)oElement=oSel.parentElement();
else oElement=oSel.item(0);
var sHTML="";var i=0;
arrTmp2=[];//clear
while (oElement!=null && oElement.tagName!="BODY")
{
arrTmp2[i]=oElement;
var sTagName = oElement.tagName;
sHTML = "&nbsp; &lt;<span id=tag"+oName+i+" unselectable=on style='text-decoration:underline;cursor:hand' onclick=\""+oName+".selectElement("+i+")\">" + sTagName + "</span>&gt;" + sHTML;
oElement = oElement.parentElement;
i++;
}
sHTML = "&nbsp;&lt;BODY&gt;" + sHTML;
eval("idElNavigate"+oName).innerHTML = sHTML;
eval("idElCommand"+oName).style.display="none";
}
if(obj.isAfterPaste)
{
obj.cleanDeprecated();
obj.runtimeBorder(false);
obj.runtimeStyles();
obj.isAfterPaste=false;
}
}
function moveTagSelector()
{
var sTagSelTop="<table unselectable=on ondblclick='"+this.oName+".moveTagSelector()' width='100%' cellpadding=0 cellspacing=0><tr style='background:#e9e8f2;font-family:arial;font-size:10px;color:black;'>"+
"<td id=idElNavigate"+ this.oName +" style='padding:1px;width:100%' valign=top>&nbsp;</td>"+
"<td align=right valign=top nowrap>"+
"<span id=idElCommand"+ this.oName +" unselectable=on style='display:none;text-decoration:underline;cursor:hand;padding-right:5;' onclick='"+this.oName+".removeTag()'>"+getTxt("Remove Tag")+"</span>"+
"</td></tr></table>";

var sTagSelBottom="<table unselectable=on ondblclick='"+this.oName+".moveTagSelector()' width='100%' cellpadding=0 cellspacing=0><tr style='background:#e4e3ed;font-family:arial;font-size:10px;color:black;'>"+
"<td id=idElNavigate"+ this.oName +" style='padding:1px;width:100%' valign=top>&nbsp;</td>"+
"<td align=right valign=top nowrap>"+
"<span id=idElCommand"+ this.oName +" unselectable=on style='display:none;text-decoration:underline;cursor:hand;padding-right:5;' onclick='"+this.oName+".removeTag()'>"+getTxt("Remove Tag")+"</span>"+
"</td></tr></table>";
if(this.TagSelectorPosition=="top")
{
eval("idTagSelTop"+this.oName).innerHTML="";
eval("idTagSelBottom"+this.oName).innerHTML=sTagSelBottom;
eval("idTagSelTopRow"+this.oName).style.display="none";
eval("idTagSelBottomRow"+this.oName).style.display="block";
this.TagSelectorPosition="bottom"
}
else//if(this.TagSelectorPosition=="bottom")
{
eval("idTagSelTop"+this.oName).innerHTML=sTagSelTop;
eval("idTagSelBottom"+this.oName).innerHTML="";
eval("idTagSelTopRow"+this.oName).style.display="block";
eval("idTagSelBottomRow"+this.oName).style.display="none";
this.TagSelectorPosition="top"
}
}
function selectElement(i)
{
var oEditor=eval("idContent"+this.oName);
var oSelRange = oEditor.document.body.createControlRange();
var oActiveElement;
try
{
oSelRange.add(arrTmp2[i]);
oSelRange.select();
realTime(this.oName,true);
oActiveElement = arrTmp2[i];
if(oActiveElement.tagName!="TD"&&
oActiveElement.tagName!="TR"&&
oActiveElement.tagName!="TBODY"&&
oActiveElement.tagName!="LI")
eval("idElCommand"+this.oName).style.display="block";
}
catch(e)
{
try
{
var oSelRange = oEditor.document.body.createTextRange();
oSelRange.moveToElementText(arrTmp2[i]);
oSelRange.select();
realTime(this.oName,true);
oActiveElement = arrTmp2[i];
if(oActiveElement.tagName!="TD"&&
oActiveElement.tagName!="TR"&&
oActiveElement.tagName!="TBODY"&&
oActiveElement.tagName!="LI")
eval("idElCommand"+this.oName).style.display="block";
}
catch(e){return;}
}
for(var j=0;j<arrTmp2.length;j++)eval("tag"+this.oName+j).style.background="";
eval("tag"+this.oName+i).style.background="DarkGray";
if(oActiveElement)
oUtil.activeElement=oActiveElement;//Set active element in the Editor
}
function removeTag()
{
if(!this.checkFocus())return;//Focus stuff
eval(this.oName).saveForUndo();//Save for Undo
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;
if(sType=="Control")
{
oSel.item(0).outerHTML="";
this.focus();
realTime(this.oName);
return;
}
var oActiveElement=oUtil.activeElement;
var oSelRange = oEditor.document.body.createTextRange();
oSelRange.moveToElementText(oActiveElement);
oSel.setEndPoint("StartToStart",oSelRange);
oSel.setEndPoint("EndToEnd",oSelRange);
oSel.select();
this.saveForUndo();
sHTML=oActiveElement.innerHTML;
var arrA = String(sHTML).match(/<A[^>]*>/g);
if(arrA)
for(var i=0;i<arrA.length;i++)
{
sTmp = arrA[i].replace(/href=/,"href_iwe=");
sHTML=String(sHTML).replace(arrA[i],sTmp);
}
var arrB = String(sHTML).match(/<IMG[^>]*>/g);
if(arrB)
for(var i=0;i<arrB.length;i++)
{
sTmp = arrB[i].replace(/src=/,"src_iwe=");
sHTML=String(sHTML).replace(arrB[i],sTmp);
}
var oTmp=oActiveElement.parentElement;
if(oTmp.innerHTML==oActiveElement.outerHTML)//<b><u>TEXT</u><b> (<u> is selected)
{
oTmp.innerHTML=sHTML;
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].getAttribute("href_iwe"))
{
oEditor.document.all[i].href=oEditor.document.all[i].getAttribute("href_iwe");
oEditor.document.all[i].removeAttribute("href_iwe",0);
}
if(oEditor.document.all[i].getAttribute("src_iwe"))
{
oEditor.document.all[i].src=oEditor.document.all[i].getAttribute("src_iwe");
oEditor.document.all[i].removeAttribute("src_iwe",0);
}
}
var oSelRange = oEditor.document.body.createTextRange();
oSelRange.moveToElementText(oTmp);
oSel.setEndPoint("StartToStart",oSelRange);
oSel.setEndPoint("EndToEnd",oSelRange);
oSel.select();
realTime(this.oName);
this.selectElement(0);
return;
}
else
{
oActiveElement.outerHTML="";
oSel.pasteHTML(sHTML);
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].getAttribute("href_iwe"))
{
oEditor.document.all[i].href=oEditor.document.all[i].getAttribute("href_iwe");
oEditor.document.all[i].removeAttribute("href_iwe",0);
}
if(oEditor.document.all[i].getAttribute("src_iwe"))
{
oEditor.document.all[i].src=oEditor.document.all[i].getAttribute("src_iwe");
oEditor.document.all[i].removeAttribute("src_iwe",0);
}
}
this.focus();
realTime(this.oName);
}
this.runtimeBorder(false);
this.runtimeStyles();
}
function runtimeBorderOn()
{
this.runtimeBorderOff();//reset

var oEditor=eval("idContent"+this.oName);
var oTables=oEditor.document.getElementsByTagName("TABLE");
for(i=0;i<oTables.length;i++)
{
var oTable=oTables[i];
if(oTable.border==0)
{
var oCells=oTable.getElementsByTagName("TD")
for(j=0;j<oCells.length;j++)
{
if(oCells[j].style.borderLeftWidth=="0px"||
oCells[j].style.borderLeftWidth==""||
oCells[j].style.borderLeftWidth=="medium")
{
oCells[j].runtimeStyle.borderLeftWidth=1;
oCells[j].runtimeStyle.borderLeftColor="#BCBCBC";
oCells[j].runtimeStyle.borderLeftStyle="dotted";
}
if(oCells[j].style.borderRightWidth=="0px"||
oCells[j].style.borderRightWidth==""||
oCells[j].style.borderRightWidth=="medium")
{
oCells[j].runtimeStyle.borderRightWidth=1;
oCells[j].runtimeStyle.borderRightColor="#BCBCBC";
oCells[j].runtimeStyle.borderRightStyle="dotted";
}
if(oCells[j].style.borderTopWidth=="0px"||
oCells[j].style.borderTopWidth==""||
oCells[j].style.borderTopWidth=="medium")
{
oCells[j].runtimeStyle.borderTopWidth=1;
oCells[j].runtimeStyle.borderTopColor="#BCBCBC";
oCells[j].runtimeStyle.borderTopStyle="dotted";
}
if(oCells[j].style.borderBottomWidth=="0px"||
oCells[j].style.borderBottomWidth==""||
oCells[j].style.borderBottomWidth=="medium")
{
oCells[j].runtimeStyle.borderBottomWidth=1;
oCells[j].runtimeStyle.borderBottomColor="#BCBCBC";
oCells[j].runtimeStyle.borderBottomStyle="dotted";
}
}
}
}
}
function runtimeBorderOff()
{
var oEditor=eval("idContent"+this.oName);
var oTables=oEditor.document.getElementsByTagName("TABLE");
for(i=0;i<oTables.length;i++)
{
var oTable=oTables[i];
if(oTable.border==0)
{
var oCells=oTable.getElementsByTagName("TD");
for(j=0;j<oCells.length;j++)
{
oCells[j].runtimeStyle.borderWidth="";
oCells[j].runtimeStyle.borderColor="";
oCells[j].runtimeStyle.borderStyle="";
}
}
}
}
function runtimeBorder(bToggle)
{
if(bToggle)
{
if(this.IsRuntimeBorderOn)
{
this.runtimeBorderOff();
this.IsRuntimeBorderOn=false;
}
else
{
this.runtimeBorderOn();
this.IsRuntimeBorderOn=true;
}
}
else
{//refresh based on the current status
if(this.IsRuntimeBorderOn) this.runtimeBorderOn();
else this.runtimeBorderOff();
}
}
function runtimeStyles()
{
var oEditor=eval("idContent"+this.oName);
var oForms=oEditor.document.getElementsByTagName("FORM");
for (i=0;i<oForms.length;i++) oForms[i].runtimeStyle.border="#7bd158 1px dotted";

var oBookmarks=oEditor.document.getElementsByTagName("A");
for (i=0;i<oBookmarks.length;i++)
{
var oBookmark=oBookmarks[i];
if(oBookmark.name||oBookmark.NAME)
{
if(oBookmark.innerHTML=="")oBookmark.runtimeStyle.width="1px";
oBookmark.runtimeStyle.padding="0px";
oBookmark.runtimeStyle.paddingLeft="1px";
oBookmark.runtimeStyle.paddingRight="1px";
oBookmark.runtimeStyle.border="#888888 1px dotted";
oBookmark.runtimeStyle.borderLeft="#cccccc 10px solid";
}
}
}
function cleanFonts()
{
var oEditor=eval("idContent"+this.oName);
var allFonts=oEditor.document.body.getElementsByTagName("FONT");
if(allFonts.length==0)return false;

var f;
while(allFonts.length>0)
{
f=allFonts[0];
if(f.hasChildNodes && f.childNodes.length==1 && f.childNodes[0].nodeType==1 && f.childNodes[0].nodeName=="SPAN") 
{
copyAttribute(f.childNodes[0],f);
f.removeNode(false);
}
else
if(f.parentElement.nodeName=="SPAN" && f.parentElement.childNodes.length==1)
{
copyAttribute(f.parentElement,f);
f.removeNode(false);
}
else
{
var newSpan=oEditor.document.createElement("SPAN");
copyAttribute(newSpan,f);
newSpan.innerHTML=f.innerHTML;
f.replaceNode(newSpan);
}
}
return true;
}
function cleanTags(elements,sVal)
{
var oEditor=eval("idContent"+this.oName);
var f;
while(elements.length>0)
{
f=elements[0];
if(f.hasChildNodes && f.childNodes.length==1 && f.childNodes[0].nodeType==1 && f.childNodes[0].nodeName=="SPAN") 
{
if(sVal=="bold")f.childNodes[0].style.fontWeight="bold";
if(sVal=="italic")f.childNodes[0].style.fontStyle="italic";
if(sVal=="line-through")f.childNodes[0].style.textDecoration="line-through";
if(sVal=="underline")f.childNodes[0].style.textDecoration="underline";
f.removeNode(false);
}
else
if(f.parentElement.nodeName=="SPAN" && f.parentElement.childNodes.length==1)
{//font is the only child node of span.
if(sVal=="bold")f.parentElement.style.fontWeight="bold";
if(sVal=="italic")f.parentElement.style.fontStyle="italic";
if(sVal=="line-through")f.parentElement.style.textDecoration="line-through";
if(sVal=="underline")f.parentElement.style.textDecoration="underline";
f.removeNode(false);
}
else
{
var newSpan=oEditor.document.createElement("SPAN");
if(sVal=="bold")newSpan.style.fontWeight="bold";
if(sVal=="italic")newSpan.style.fontStyle="italic";
if(sVal=="line-through")newSpan.style.textDecoration="line-through";
if(sVal=="underline")newSpan.style.textDecoration="underline";
newSpan.innerHTML=f.innerHTML;
f.replaceNode(newSpan);
}
}
}
function replaceTags(sFrom,sTo)
{
var oEditor=eval("idContent"+this.oName);
var elements=oEditor.document.getElementsByTagName(sFrom);

var newSpan;
var count=elements.length;
while(count > 0) 
{
f=elements[0];
newSpan=oEditor.document.createElement(sTo);
newSpan.innerHTML=f.innerHTML;
f.replaceNode(newSpan);     
count--;
}
}
function cleanDeprecated()
{
var oEditor=eval("idContent"+this.oName);
var elements;
elements=oEditor.document.body.getElementsByTagName("STRIKE");
this.cleanTags(elements,"line-through");
elements=oEditor.document.body.getElementsByTagName("S");
this.cleanTags(elements,"line-through");
elements=oEditor.document.body.getElementsByTagName("U");
this.cleanTags(elements,"underline");
this.replaceTags("DIR","DIV");
this.replaceTags("MENU","DIV");
this.replaceTags("CENTER","DIV");
this.replaceTags("XMP","PRE");
this.replaceTags("BASEFONT","SPAN");//will be removed by cleanEmptySpan()
elements=oEditor.document.body.getElementsByTagName("APPLET");
var count=elements.length;
while(count>0) 
{
f=elements[0];
f.removeNode(false);  
count--;
}
this.cleanFonts();
this.cleanEmptySpan();
return true;
}
function applyBold()
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();
this.doCmd("bold");
return;
var currState=oEditor.document.queryCommandState("Bold");
if(oUtil.activeElement) oElement=oUtil.activeElement;
else 
{
if(oSel.parentElement)
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
}
else
{
if(currState)
{
this.applySpanStyle([["fontWeight",""]]);
this.cleanEmptySpan();
}
else this.applySpanStyle([["fontWeight","bold"]]);
if(currState==oEditor.document.queryCommandState("Bold")&&currState==true)
this.applySpanStyle([["fontWeight","normal"]]);
return;
}
}
else oElement=oSel.item(0);
}
if(currState)oElement.style.fontWeight="";
else oElement.style.fontWeight="bold";
if(currState==oEditor.document.queryCommandState("Bold")&&currState==true)
oElement.style.fontWeight="normal";
}
function applyItalic()
{
if(!this.checkFocus())return;//Focus stuff
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();
this.doCmd("italic");
return;
var currState=oEditor.document.queryCommandState("Italic");
if(oUtil.activeElement) oElement=oUtil.activeElement;
else 
{
if(oSel.parentElement)
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
}
else
{
if(currState)
{
this.applySpanStyle([["fontStyle",""]]);
this.cleanEmptySpan();
}
else this.applySpanStyle([["fontStyle","italic"]]);
if(currState==oEditor.document.queryCommandState("Italic")&&currState==true)
this.applySpanStyle([["fontStyle","normal"]]);
return;
}
}
else oElement=oSel.item(0);
}
if(currState)oElement.style.fontStyle="";
else oElement.style.fontStyle="italic";
if(currState==oEditor.document.queryCommandState("Italic")&&currState==true)
oElement.style.fontStyle="normal";
}
function GetUnderlinedTag(oElement)
{
while (oElement!=null&&oElement.style.textDecoration.indexOf("underline")==-1)
{
if(oElement.tagName=="BODY")return null;
oElement=oElement.parentElement;
}
return oElement;
}
function GetOverlinedTag(oElement)
{
while (oElement!=null&&oElement.style.textDecoration.indexOf("line-through")==-1)
{
if(oElement.tagName=="BODY")return null;
oElement=oElement.parentElement;
}
return oElement;
}
function applyLine(sCmd)
{
if(!this.checkFocus())return;//Focus stuff
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();
if(!oSel.parentElement)return;
var bIsUnderlined=oEditor.document.queryCommandState("Underline");
var bIsOverlined=oEditor.document.queryCommandState("Strikethrough");
if(bIsUnderlined && !bIsOverlined)
{
if(sCmd=="underline")
{
oElement=GetUnderlinedTag(oSel.parentElement())
if(oElement)
oElement.style.textDecoration=oElement.style.textDecoration.replace("underline","")
}
else
{//biasa => apply "line-through"
if(oSel.text=="")
{
oElement=oSel.parentElement();
oElement.style.textDecoration=oElement.style.textDecoration+" line-through"
}
else 
{
this.applySpanStyle([["textDecoration","line-through"]]);//limitation:
}
}
}
else if(bIsOverlined && !bIsUnderlined)
{
if(sCmd=="line-through")
{
oElement=GetOverlinedTag(oSel.parentElement())
if(oElement)
oElement.style.textDecoration=oElement.style.textDecoration.replace("line-through","")
}
else//"underline"
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
oElement.style.textDecoration=oElement.style.textDecoration+" underline"
}
else 
{
this.applySpanStyle([["textDecoration","underline"]]);
}
}
}
else if(bIsUnderlined && bIsOverlined)
{
if(sCmd=="underline")
{
oElement=GetUnderlinedTag(oSel.parentElement())
if(oElement)
oElement.style.textDecoration=oElement.style.textDecoration.replace("underline","")
}
else
{
oElement=GetOverlinedTag(oSel.parentElement())
if(oElement)
oElement.style.textDecoration=oElement.style.textDecoration.replace("line-through","")
}
}
else
{//clean text
if(sCmd=="underline")
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
oElement.style.textDecoration="underline"
}
else this.applySpanStyle([["textDecoration","underline"]]);
}
else
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
oElement.style.textDecoration="line-through"
}
else this.applySpanStyle([["textDecoration","line-through"]]);
}
}
return;
var currState1=oEditor.document.queryCommandState("Underline");
var currState2=oEditor.document.queryCommandState("Strikethrough");
var sValue;
if(sCmd=="underline")
{
if(currState1&&currState2)sValue="line-through";
else if(!currState1&&currState2)sValue="underline line-through";
else if(currState1&&!currState2)sValue="";
else if(!currState1&&!currState2)sValue="underline";
}
else
{
if(currState1&&currState2)sValue="underline";
else if(!currState1&&currState2)sValue="";
else if(currState1&&!currState2)sValue="underline line-through";
else if(!currState1&&!currState2)sValue="line-through";
}
if(oUtil.activeElement) oElement=oUtil.activeElement;
else 
{
if(oSel.parentElement)
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
}
else
{
if(sValue=="")
{
this.applySpanStyle([["textDecoration",""]]);
this.cleanEmptySpan();
}
else this.applySpanStyle([["textDecoration",sValue]]);
if((sCmd=="underline"&&currState1==oEditor.document.queryCommandState("Underline")&&currState1==true) ||
(sCmd=="line-through"&&currState2==oEditor.document.queryCommandState("Strikethrough")&&currState2==true))
{
this.applySpanStyle([["textDecoration",""]]);
this.cleanEmptySpan();
}
return;
}
}
else oElement=oSel.item(0);
}
oElement.style.textDecoration=sValue;
if((sCmd=="underline"&&currState1==oEditor.document.queryCommandState("Underline")&&currState1==true) ||
(sCmd=="line-through"&&currState2==oEditor.document.queryCommandState("Strikethrough")&&currState2==true))
{
this.applySpanStyle([["textDecoration",""]]);
this.cleanEmptySpan();
}
}
function applyColor(sType,sColor)
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();

if(oUtil.activeElement)
{
oElement=oUtil.activeElement;
if(sType=="ForeColor")oElement.style.color=sColor;
else oElement.style.backgroundColor=sColor;
}
else if(oSel.parentElement)
{
if(oSel.text=="")
{
oElement=oSel.parentElement();
if(oElement.tagName=="BODY")return;
if(sType=="ForeColor")oElement.style.color=sColor;
else oElement.style.backgroundColor=sColor;
}
else
{
if(sType=="ForeColor")this.applySpanStyle([["color",sColor]]);
else this.applySpanStyle([["backgroundColor",sColor]]);
}
}
if(sColor=="")
{
this.cleanEmptySpan();
realTime(this.oName);
}
}
function applySpanStyle(arrStyles,sClassName)
{
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.hide();
oSel.select();
this.saveForUndo();
if(oSel.parentElement)
{
var tempRange=oEditor.document.body.createTextRange();
var oEl=oSel.parentElement();
var allSpans=oEditor.document.getElementsByTagName("SPAN");//ok - WARNING: hrs cek semua span.
for (var i=0;i<allSpans.length;i++)
{
tempRange.moveToElementText(allSpans[i]);
if (oSel.inRange(tempRange))
copyStyleClass(allSpans[i],arrStyles,sClassName);
}
}
this.doCmd("fontname","");
replaceWithSpan(oEditor,arrStyles,sClassName);
this.cleanEmptySpan();
realTime(this.oName);
}
function doClean()
{
if(!this.checkFocus())return;//Focus stuff
var oEditor=eval("idContent"+this.oName);
this.saveForUndo();
this.doCmd('RemoveFormat');
if(oUtil.activeElement)
{
var oActiveElement=oUtil.activeElement;
oActiveElement.removeAttribute("className",0);
oActiveElement.removeAttribute("style",0);
if(oActiveElement.tagName=="H1"||oActiveElement.tagName=="H2"||
oActiveElement.tagName=="H3"||oActiveElement.tagName=="H4"||
oActiveElement.tagName=="H5"||oActiveElement.tagName=="H6"||
oActiveElement.tagName=="PRE"||oActiveElement.tagName=="P"||
oActiveElement.tagName=="DIV")
{
if(this.useDIV)this.doCmd('FormatBlock','<DIV>');
else this.doCmd('FormatBlock','<P>');
}
}
else
{
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;
if (oSel.parentElement)
{
if(oSel.text=="")
{
oEl=oSel.parentElement();
if(oEl.tagName=="BODY")return;
else
{
oEl.removeAttribute("className",0);
oEl.removeAttribute("style",0);
if(oEl.tagName=="H1"||oEl.tagName=="H2"||
oEl.tagName=="H3"||oEl.tagName=="H4"||
oEl.tagName=="H5"||oEl.tagName=="H6"||
oEl.tagName=="PRE"||oEl.tagName=="P"||oEl.tagName=="DIV")
{
if(this.useDIV)this.doCmd('FormatBlock','<DIV>');
else this.doCmd('FormatBlock','<P>');
}
}
}
else
{
this.applySpanStyle([
["backgroundColor",""],
["color",""],
["fontFamily",""],
["fontSize",""],
["fontWeight",""],
["fontStyle",""],
["textDecoration",""],
["letterSpacing",""],
["verticalAlign",""],
["textTransform",""],
["fontVariant",""]
],"");
return;
}
}
else
{
oEl=oSel.item(0);
oEl.removeAttribute("className",0);
oEl.removeAttribute("style",0);
}
}
this.cleanEmptySpan();
realTime(this.oName);
}
function cleanEmptySpan()//WARNING: blm bisa remove span yg bertumpuk dgn style sama,dst.
{
var bReturn=false;
var oEditor=eval("idContent"+this.oName);
var allSpans=oEditor.document.getElementsByTagName("SPAN");
if(allSpans.length==0)return false;
var emptySpans=[];
var reg = /<\s*SPAN\s*>/gi;
for(var i=0;i<allSpans.length;i++)
{
if(allSpans[i].outerHTML.search(reg)==0)
emptySpans[emptySpans.length]=allSpans[i];
}
var theSpan,theParent;
for(var i=0;i<emptySpans.length;i++)
{
theSpan=emptySpans[i];
theSpan.removeNode(false);
bReturn=true;
}
return bReturn;
}
function copyStyleClass(newSpan,arrStyles,sClassName)
{
if(arrStyles)
for(var i=0;i<arrStyles.length;i++)
{
eval("newSpan.style."+arrStyles[i][0]+"=\""+arrStyles[i][1]+"\"");
}
if(newSpan.style.fontFamily=="")
{
newSpan.style.cssText=newSpan.style.cssText.replace("FONT-FAMILY: ; ","");
newSpan.style.cssText=newSpan.style.cssText.replace("FONT-FAMILY: ","");
}
if(sClassName!=null)
{
newSpan.className=sClassName;
if(newSpan.className=="")newSpan.removeAttribute("className",0);//WARNING: this will remove span (for empty attributes).
}
}
function copyAttribute(newSpan,f)
{
if((f.face!=null)&&(f.face!=""))newSpan.style.fontFamily=f.face;
if((f.size!=null)&&(f.size!=""))
{
var nSize="";
if(f.size==1)nSize="8pt";
else if(f.size==2)nSize="10pt";
else if(f.size==3)nSize="12pt";
else if(f.size==4)nSize="14pt";
else if(f.size==5)nSize="18pt";
else if(f.size==6)nSize="24pt";
else if(f.size>=7)nSize="36pt";
else if(f.size<=-2||f.size=="0")nSize="8pt";
else if(f.size=="-1")nSize="10pt";
else if(f.size==0)nSize="12pt";
else if(f.size=="+1")nSize="14pt";
else if(f.size=="+2")nSize="18pt";
else if(f.size=="+3")nSize="24pt";
else if(f.size=="+4"||f.size=="+5"||f.size=="+6")nSize="36pt";
else nSize="";
if(nSize!="")newSpan.style.fontSize=nSize;
}
if((f.style.backgroundColor!=null)&&(f.style.backgroundColor!=""))newSpan.style.backgroundColor=f.style.backgroundColor;
if((f.color!=null)&&(f.color!=""))newSpan.style.color=f.color;
if((f.className!=null)&&(f.className!=""))newSpan.className=f.className;
}
function replaceWithSpan(oEditor,arrStyles,sClassName)
{
var oSel=oEditor.document.selection.createRange();
var oSpanStart;
oSel.select();
var nSelLength=oSel.text.length;
var allFonts=new Array();
if (oSel.parentElement().nodeName=="FONT" && oSel.parentElement().innerText==oSel.text)
{
oSel.moveToElementText(oSel.parentElement());
allFonts[0]=oSel.parentElement();
} 
else 
{
allFonts=oEditor.document.getElementsByTagName("FONT");
}
var tempRange=oEditor.document.body.createTextRange();
var newSpan;
var count=allFonts.length;
while(count>0)
{
f=allFonts[0];
if(f==null||f.parentElement==null){count--;continue}
tempRange.moveToElementText(f);
var sTemp="f";var nLevel=0;
while(eval(sTemp+".parentElement"))
{
nLevel++;
sTemp+=".parentElement";
}
var bBreak=false;
for(var j=nLevel;j>0;j--)
{
sTemp="f";
for(var k=1;k<=j;k++)sTemp+=".parentElement";
if(!bBreak)
if (eval(sTemp).nodeName=="SPAN" && eval(sTemp).innerText==f.innerText)
{
newSpan=eval(sTemp);
if(arrStyles||sClassName)copyStyleClass(newSpan,arrStyles,sClassName);
else copyAttribute(newSpan,f);
f.removeNode(false);
bBreak=true;
}
}
if(bBreak)
{
continue;
}

newSpan=oEditor.document.createElement("SPAN");
if(arrStyles||sClassName)copyStyleClass(newSpan,arrStyles,sClassName);
else copyAttribute(newSpan,f);
newSpan.innerHTML=f.innerHTML;
f.replaceNode(newSpan);
count--;
if(!oSpanStart)oSpanStart=newSpan;
}
var rng = oEditor.document.selection.createRange();
if(oSpanStart)
{
rng.moveToElementText(oSpanStart);
rng.select();
}
rng.moveEnd("character",nSelLength-rng.text.length);
rng.select();

//adjustments
rng.moveEnd("character",nSelLength-rng.text.length);
rng.select();
rng.moveEnd("character",nSelLength-rng.text.length);
rng.select();
}
function doOnPaste()
{
this.isAfterPaste=true;
this.saveForUndo();
}
function doPaste()
{
this.saveForUndo();
this.doCmd("Paste");
this.runtimeBorder(false);
}
function doCmd(sCmd,sOption)
{
if(!this.checkFocus())return;

if(sCmd=="Cut"||sCmd=="Copy"||sCmd=="Superscript"||sCmd=="Subscript"||
sCmd=="Indent"||sCmd=="Outdent"||sCmd=="InsertHorizontalRule"||
sCmd=="BlockDirLTR"||sCmd=="BlockDirRTL")
this.saveForUndo();

var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;
var oTarget=(sType=="None"?oEditor.document:oSel);
oTarget.execCommand(sCmd,false,sOption);
realTime(this.oName);
}
function applyParagraph(val)
{
this.hide();
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.hide();
oSel.select();
this.saveForUndo();
this.doCmd("FormatBlock",val);
}
function applyBullets()
{
if(!this.checkFocus())return;
this.saveForUndo();
this.doCmd("InsertUnOrderedList");
makeEnableNormal(eval("document.all.btnNumbering"+this.oName));
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var oElement=oSel.parentElement();
while (oElement!=null&&oElement.tagName!="OL"&&oElement.tagName!="UL")
{
if(oElement.tagName=="BODY")return;
oElement=oElement.parentElement;
}
oElement.removeAttribute("type",0);
oElement.style.listStyleImage="";
}
function applyNumbering()
{
if(!this.checkFocus())return;
this.saveForUndo();
this.doCmd("InsertOrderedList");
makeEnableNormal(eval("document.all.btnBullets"+this.oName));

var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var oElement=oSel.parentElement();
while (oElement!=null&&oElement.tagName!="OL"&&oElement.tagName!="UL")
{
if(oElement.tagName=="BODY")return;
oElement=oElement.parentElement;
}
oElement.removeAttribute("type",0);
oElement.style.listStyleImage="";
}
function applyJustifyLeft()
{
if(!this.checkFocus())return;

this.saveForUndo();
this.doCmd("JustifyLeft");
if(this.btnJustifyCenter) makeEnableNormal(eval("document.all.btnJustifyCenter"+this.oName));
if(this.btnJustifyRight) makeEnableNormal(eval("document.all.btnJustifyRight"+this.oName));
if(this.btnJustifyFull) makeEnableNormal(eval("document.all.btnJustifyFull"+this.oName));
}
function applyJustifyCenter()
{
if(!this.checkFocus())return;
this.saveForUndo();
this.doCmd("JustifyCenter");
if(this.btnJustifyLeft) makeEnableNormal(eval("document.all.btnJustifyLeft"+this.oName));
if(this.btnJustifyRight) makeEnableNormal(eval("document.all.btnJustifyRight"+this.oName));
if(this.btnJustifyFull) makeEnableNormal(eval("document.all.btnJustifyFull"+this.oName));
}
function applyJustifyRight()
{
if(!this.checkFocus())return;
this.saveForUndo();
this.doCmd("JustifyRight");
if(this.btnJustifyLeft) makeEnableNormal(eval("document.all.btnJustifyLeft"+this.oName));
if(this.btnJustifyCenter) makeEnableNormal(eval("document.all.btnJustifyCenter"+this.oName));
if(this.btnJustifyFull) makeEnableNormal(eval("document.all.btnJustifyFull"+this.oName));
}
function doPasteText()
{
if(!this.checkFocus())return;
var oWord=eval("idContentWord"+this.oName);
oWord.document.designMode="on";
oWord.document.open("text/html","replace");
oWord.document.write("<html><head></head><body></body></html>");
oWord.document.close();
oWord.document.body.contentEditable=true;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();
var oWord = eval("idContentWord"+this.oName);
oWord.focus();
oWord.document.execCommand("SelectAll");
oWord.document.execCommand("Paste");
var sHTML = oWord.document.body.innerHTML;
sHTML = sHTML.replace(/(<br>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/tr>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/div>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h1>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h2>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h3>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h4>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h5>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<\/h6>)/gi, "$1&lt;REPBR&gt;");
sHTML = sHTML.replace(/(<p>)/gi, "$1&lt;REPBR&gt;");
oWord.document.body.innerHTML=sHTML; 
oSel.pasteHTML(oWord.document.body.innerText.replace(/<REPBR>/gi, "<br>"));
}
function insertCustomTag(index)
{
this.hide();
if(!this.checkFocus())return;
this.insertHTML(this.arrCustomTag[index][1]);
this.hide();
this.focus();
}
function insertHTML(sHTML)
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();
var arrA = String(sHTML).match(/<A[^>]*>/ig);
if(arrA)
for(var i=0;i<arrA.length;i++)
{
sTmp = arrA[i].replace(/href=/,"href_iwe=");
sHTML=String(sHTML).replace(arrA[i],sTmp);
}
var arrB = String(sHTML).match(/<IMG[^>]*>/ig);
if(arrB)
for(var i=0;i<arrB.length;i++)
{
sTmp = arrB[i].replace(/src=/,"src_iwe=");
sHTML=String(sHTML).replace(arrB[i],sTmp);
}
if(oSel.parentElement)oSel.pasteHTML(sHTML);
else oSel.item(0).outerHTML=sHTML;
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].getAttribute("href_iwe"))
{
oEditor.document.all[i].href=oEditor.document.all[i].getAttribute("href_iwe");
oEditor.document.all[i].removeAttribute("href_iwe",0);
}
if(oEditor.document.all[i].getAttribute("src_iwe"))
{
oEditor.document.all[i].src=oEditor.document.all[i].getAttribute("src_iwe");
oEditor.document.all[i].removeAttribute("src_iwe",0);
}
}
}
function clearAll()
{
if(confirm(getTxt("Are you sure you wish to delete all contents?"))==true)
{
var oEditor=eval("idContent"+this.oName);
this.saveForUndo();
oEditor.document.body.innerHTML="";
}
}
function selectedText() {
  if(!this.checkFocus())return;//Focus stuff
  var oEditor=eval("idContent"+this.oName);
  var oSel=oEditor.document.selection.createRange();
  var html = oSel.htmlText;
  return html;
}
function ClearSelectedText() {
  if(!this.checkFocus())return;//Focus stuff
  var oEditor=eval("idContent"+this.oName);
  var oSel=oEditor.document.selection.createRange();
  oEditor.document.selection.clear();
}
function applySpan()
{
if(!this.checkFocus())return;//Focus stuff
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var sType=oEditor.document.selection.type;
if(sType=="Control"||sType=="None")return;
sHTML=oSel.htmlText;
var oParent=oSel.parentElement();
if(oParent)
if(oParent.innerText==oSel.text)
{
/*
for(var j=0;j<arrTmp2.length;j++)
{
if(arrTmp2[j]==oParent)
{
alert(arrTmp2[j].tagName)
}
}*/
if(oParent.tagName=="DIV")
{
idSpan=oParent;
return idSpan;
}
}
var arrA = String(sHTML).match(/<A[^>]*>/ig);
if(arrA)
for(var i=0;i<arrA.length;i++)
{
sTmp = arrA[i].replace(/href=/,"href_iwe=");
sHTML=String(sHTML).replace(arrA[i],sTmp);
}
var arrB = String(sHTML).match(/<IMG[^>]*>/ig);
if(arrB)
for(var i=0;i<arrB.length;i++)
{
sTmp = arrB[i].replace(/src=/,"src_iwe=");
sHTML=String(sHTML).replace(arrB[i],sTmp);
}
oSel.pasteHTML("<DIV id='idSpan__abc'>"+sHTML+"</DIV>");
var idSpan=oEditor.document.all.idSpan__abc;
var oSelRange=oEditor.document.body.createTextRange();
oSelRange.moveToElementText(idSpan);
oSel.setEndPoint("StartToStart",oSelRange);
oSel.setEndPoint("EndToEnd",oSelRange);
oSel.select();
for(var i=0;i<oEditor.document.all.length;i++)
{
if(oEditor.document.all[i].getAttribute("href_iwe"))
{
oEditor.document.all[i].href=oEditor.document.all[i].getAttribute("href_iwe");
oEditor.document.all[i].removeAttribute("href_iwe",0);
}
if(oEditor.document.all[i].getAttribute("src_iwe"))
{
oEditor.document.all[i].src=oEditor.document.all[i].getAttribute("src_iwe");
oEditor.document.all[i].removeAttribute("src_iwe",0);
}
}
idSpan.removeAttribute("id",0);
return idSpan;
}
function makeAbsolute()
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
this.saveForUndo();

if(oSel.parentElement)
{
var oElement=oSel.parentElement();
oElement.style.position="absolute";
}
else
this.doCmd("AbsolutePosition");
}
function expandSelection()
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
if(oSel.text!="")return;
oSel.expand("word");
oSel.select();
if(oSel.text.substr(oSel.text.length*1-1,oSel.text.length)==" ")
{
oSel.moveEnd("character",-1);
oSel.select();
}
}
function selectParagraph()
{
if(!this.checkFocus())return;
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
if(oSel.parentElement)
{
if(oSel.text=="")
{
var oElement=oSel.parentElement();
while (oElement!=null&&
oElement.tagName!="H1"&&oElement.tagName!="H2"&&
oElement.tagName!="H3"&&oElement.tagName!="H4"&&
oElement.tagName!="H5"&&oElement.tagName!="H6"&&
oElement.tagName!="PRE"&&oElement.tagName!="P"&&
oElement.tagName!="DIV")
{
if(oElement.tagName=="BODY")return;
oElement=oElement.parentElement;
}
var oSelRange = oEditor.document.body.createControlRange();
try
{
oSelRange.add(oElement);
oSelRange.select();
}
catch(e)
{
var oSelRange = oEditor.document.body.createTextRange();
try{oSelRange.moveToElementText(oElement);
oSelRange.select()
}catch(e){;}
}
}
}
}
function doOver_TabCreate()
{
var oTD=event.srcElement;
var oTable=oTD.parentElement.parentElement.parentElement;
var nRow=oTD.parentElement.rowIndex;
var nCol=oTD.cellIndex;
var rows=oTable.rows;
rows[rows.length-1].childNodes[0].innerHTML="<div align=right>"+(nRow*1+1) + " x " + (nCol*1+1) + " Table ... &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style='text-decoration:underline'>Advanced</span>&nbsp;</div>";
for(var i=0;i<rows.length-1;i++)
{
var oRow=rows[i];
for(var j=0;j<oRow.childNodes.length;j++)
{
var oCol=oRow.childNodes[j];
if(i<=nRow&&j<=nCol)oCol.style.backgroundColor="#316ac5";
else oCol.style.backgroundColor="#ffffff";
}
}
event.cancelBubble=true;
}
function doOut_TabCreate()
{
var oTable=event.srcElement;
if(oTable.tagName!="TABLE")return;
var rows=oTable.rows;
rows[rows.length-1].childNodes[0].innerText=getTxt("Advanced Table Insert");
for(var i=0;i<rows.length-1;i++)
{
var oRow=rows[i];
for(var j=0;j<oRow.childNodes.length;j++)
{
var oCol=oRow.childNodes[j];
oCol.style.backgroundColor="#ffffff";
}
}
event.cancelBubble=true;
}
function doRefresh_TabCreate()
{
var oTable=eval("dropTableCreate"+this.oName);
var rows=oTable.rows;
rows[rows.length-1].childNodes[0].innerText=getTxt("Advanced Table Insert");
for(var i=0;i<rows.length-1;i++)
{
var oRow=rows[i];
for(var j=0;j<oRow.childNodes.length;j++)
{
var oCol=oRow.childNodes[j];
oCol.style.backgroundColor="#ffffff";
}
}
}
function doClick_TabCreate()
{
this.hide();
if(!this.checkFocus())return;//Focus stuff
var oEditor=eval("idContent"+this.oName);
var oSel=oEditor.document.selection.createRange();
var oTD=event.srcElement;
var nRow=oTD.parentElement.rowIndex+1;
var nCol=oTD.cellIndex+1;
this.saveForUndo();
var sHTML="<table style='border-collapse:collapse;width:100%;'>";
for(var i=1;i<=nRow;i++)
{
sHTML+="<tr>";
for(var j=1;j<=nCol;j++)
{
sHTML+="<td></td>";
}
sHTML+="</tr>";
}
sHTML+="</table>";
if(oSel.parentElement)
oSel.pasteHTML(sHTML);
else
oSel.item(0).outerHTML = sHTML;
realTime(this.oName);
this.runtimeBorder(false);
this.runtimeStyles();
}
function doKeyPress(evt,oName)
{
if(!eval(oName).arrUndoList[0]){eval(oName).saveForUndo();}
if(evt.ctrlKey)
{
if(evt.keyCode==89)
{//CTRL-Y (Redo)
if (!evt.altKey) eval(oName).doRedo();
}
if(evt.keyCode==90)
{//CTRL-Z (Undo)
if (!evt.altKey) eval(oName).doUndo();
}
if(evt.keyCode==65)
{//CTRL-A (Select All) => spy jalan di modal dialog
if (!evt.altKey) eval(oName).doCmd("SelectAll");
}
}
if(evt.keyCode==37||evt.keyCode==38||evt.keyCode==39||evt.keyCode==40)//Arrow
{
eval(oName).saveForUndo();
}
if(evt.keyCode==13)
{
if(eval(oName).useDIV && !eval(oName).useBR)
{
var oSel=document.selection.createRange();
if(oSel.parentElement)
{
eval(oName).saveForUndo();
if(GetElement(oSel.parentElement(),"FORM"))
{
var oSel=document.selection.createRange();
oSel.pasteHTML('<br>');
evt.cancelBubble=true;
evt.returnValue=false;
oSel.select();
oSel.moveEnd("character", 1);
oSel.moveStart("character", 1);
oSel.collapse(false);
return false;
}
else
{
var oEl = GetElement(oSel.parentElement(),"H1");
if(!oEl) oEl = GetElement(oSel.parentElement(),"H2");
if(!oEl) oEl = GetElement(oSel.parentElement(),"H3");
if(!oEl) oEl = GetElement(oSel.parentElement(),"H4");
if(!oEl) oEl = GetElement(oSel.parentElement(),"H5");
if(!oEl) oEl = GetElement(oSel.parentElement(),"H6");
if(!oEl) oEl = GetElement(oSel.parentElement(),"PRE");
if(!oEl)eval(oName).doCmd("FormatBlock","<div>");
return true;
}
}
}
if((eval(oName).useDIV && eval(oName).useBR)||
(!eval(oName).useDIV && eval(oName).useBR))
{
var oSel=document.selection.createRange();
oSel.pasteHTML('<br>');
evt.cancelBubble=true;
evt.returnValue=false;
oSel.select();
oSel.moveEnd("character", 1);
oSel.moveStart("character", 1);
oSel.collapse(false);
return false;
}
eval(oName).saveForUndo();
}
eval(oName).onKeyPress()
}
function dropShow(oEl,box)
{
this.hide();
box.style.display="block";
var nTop=0;
var nLeft=0;
oElTmp=oEl;
while(oElTmp.tagName!="BODY" && oElTmp.tagName!="HTML")
{
if(oElTmp.style.top!="")
nTop+=oElTmp.style.top.substring(1,oElTmp.style.top.length-2)*1;
else nTop+=oElTmp.offsetTop;
oElTmp = oElTmp.offsetParent;
}
oElTmp=oEl;
while(oElTmp.tagName!="BODY" && oElTmp.tagName!="HTML")
{
if(oElTmp.style.left!="")
nLeft+=oElTmp.style.left.substring(1,oElTmp.style.left.length-2)*1;
else nLeft+=oElTmp.offsetLeft;
oElTmp=oElTmp.offsetParent;
}
box.style.left=nLeft+this.dropLeftAdjustment;
box.style.top=nTop+1+this.dropTopAdjustment;
}
function hide()
{
if(this.btnPreview)eval("dropPreview"+this.oName).style.display="none";
if(this.btnTextFormatting||this.btnParagraphFormatting||this.btnListFormatting||this.btnBoxFormatting||this.btnCssText||this.btnCssBuilder)eval("dropStyle"+this.oName).style.display="none";
if(this.btnParagraph)eval("dropParagraph"+this.oName).style.display="none";
if(this.btnFontName)eval("dropFontName"+this.oName).style.display="none";
if(this.btnFontSize)eval("dropFontSize"+this.oName).style.display="none";
if(this.btnTable)eval("dropTable"+this.oName).style.display="none";
if(this.btnTable)eval("dropTableCreate"+this.oName).style.display="none";
if(this.btnForm)eval("dropForm"+this.oName).style.display="none";
if(this.btnCustomTag)eval("dropCustomTag"+this.oName).style.display="none";
if(this.btnTable)this.doRefresh_TabCreate();
}
function modelessDialogShow(url,width,height)
{
window.showModelessDialog(url,window,
"dialogWidth:"+width+"px;dialogHeight:"+height+"px;edge:Raised;center:1;help:0;resizable:1;");
}
function modalDialogShow(url,width,height)
{
window.showModalDialog(url,window,
"dialogWidth:"+width+"px;dialogHeight:"+height+"px;edge:Raised;center:1;help:0;resizable:1;maximize:1");
}
function windowOpen(url,width,height)
{
window.open(url,"","width="+width+"px,height="+height+"px;toolbar=no,menubar=no,location=no,directories=no,status=yes")
}
function lineBreak1(tag) //[0]<TAG>[1]text[2]</TAG>
{
arrReturn = ["\n","",""];
if(tag=="A"||tag=="B"||tag=="CITE"||tag=="CODE"||tag=="EM"||
tag=="FONT"||tag=="I"||tag=="SMALL"||tag=="STRIKE"||tag=="BIG"||
tag=="STRONG"||tag=="SUB"||tag=="SUP"||tag=="U"||tag=="SAMP"||
tag=="S"||tag=="VAR"||tag=="BASEFONT"||tag=="KBD"||tag=="TT")
arrReturn=["","",""];
if(tag=="TEXTAREA"||tag=="TABLE"||tag=="THEAD"||tag=="TBODY"||
tag=="TR"||tag=="OL"||tag=="UL"||tag=="DIR"||tag=="MENU"||
tag=="FORM"||tag=="SELECT"||tag=="MAP"||tag=="DL"||tag=="HEAD"||
tag=="BODY"||tag=="HTML")
arrReturn=["\n","","\n"];
if(tag=="STYLE"||tag=="SCRIPT")
arrReturn=["\n","",""];
if(tag=="BR"||tag=="HR")
arrReturn=["","\n",""];
return arrReturn;
}
function fixAttr(s)
{
s = String(s).replace(/&/g, "&amp;");
s = String(s).replace(/</g, "&lt;");
s = String(s).replace(/"/g, "&quot;");
return s;
}
function fixVal(s)
{
s = String(s).replace(/&/g, "&amp;");
s = String(s).replace(/</g, "&lt;");
var x = escape(s);
x = unescape(x.replace(/\%A0/gi, "-*REPL*-"));
s = x.replace(/-\*REPL\*-/gi, "&nbsp;");
return s;
}
function recur(oEl,sTab)
{
var sHTML="";
for(var i=0;i<oEl.childNodes.length;i++)
{
var oNode=oEl.childNodes(i);
if(oNode.nodeType==1)//tag
{
var sTagName = oNode.nodeName;
var sCloseTag = oNode.outerHTML.substr(oNode.outerHTML.lastIndexOf("<"));
sCloseTag = sCloseTag.replace(/[<>\/]/gi, "");

var bDoNotProcess=false;
if(sTagName.substring(0,1)=="/")
{
bDoNotProcess=true;//do not process
}
else
{
var sT= sTab;
sHTML+= lineBreak1(sTagName)[0];
if(lineBreak1(sTagName)[0] !="") sHTML+= sT;
}

if(bDoNotProcess)
{
;//do not process
}
else if(sTagName=="OBJECT" || sTagName=="EMBED")
{
s=oNode.outerHTML;
s=s.replace(/\"[^\"]*\"/ig,function(x){
x=x.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/'/g, "&apos;").replace(/\s+/ig,"#_#");
return x});
s=s.replace(/<([^ >]*)/ig,function(x){return x.toLowerCase()});
s=s.replace(/ ([^=]+)=([^"' >]+)/ig," $1=\"$2\"");//new
s=s.replace(/ ([^=]+)=/ig,function(x){return x.toLowerCase()});
s=s.replace(/#_#/ig," ");
s=s.replace(/<param([^>]*)>/ig,"\n<param$1 />").replace(/\/ \/>$/ig," \/>");//no closing tag
if(sTagName=="EMBED")
if(oNode.innerHTML=="")
s=s.replace(/>$/ig," \/>").replace(/\/ \/>$/ig,"\/>");//no closing tag
s=s.replace(/<param name=\"Play\" value=\"0\" \/>/,"<param name=\"Play\" value=\"-1\" \/>");
sHTML+=s;
}
else if(sTagName=="TITLE")
{
sHTML+="<title>"+oNode.innerHTML+"</title>";
}
else
{
if(sTagName=="AREA")
{
var sCoords=oNode.coords;
var sShape=oNode.shape;
}
var oNode2=oNode.cloneNode();
if (oNode.checked) oNode2.checked=oNode.checked;
s=oNode2.outerHTML.replace(/<\/[^>]*>/,"");

if(sTagName=="STYLE")
{
var arrTmp=s.match(/<[^>]*>/ig);
s=arrTmp[0];
}
s=s.replace(/\"[^\"]*\"/ig,function(x){
x=x.replace(/&/g, "&amp;").replace(/&amp;amp;/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\s+/ig,"#_#");
return x});
s=s.replace(/<([^ >]*)/ig,function(x){return x.toLowerCase()});
s=s.replace(/ ([^=]+)=([^" >]+)/ig," $1=\"$2\"");
s=s.replace(/ ([^=]+)=/ig,function(x){return x.toLowerCase()});
s=s.replace(/#_#/ig," ");
s=s.replace(/(<hr[^>]*)(noshade)/ig,"$1noshade=\"noshade\"");
s=s.replace(/(<input[^>]*)(checked)/ig,"$1checked=\"checked\"");
s=s.replace(/(<select[^>]*)(multiple)/ig,"$1multiple=\"multiple\"");
s=s.replace(/(<option[^>]*)(selected)/ig,"$1selected=\"true\"");
s=s.replace(/(<input[^>]*)(readonly)/ig,"$1readonly=\"readonly\"");
s=s.replace(/(<input[^>]*)(disabled)/ig,"$1disabled=\"disabled\"");
s=s.replace(/(<td[^>]*)(nowrap )/ig,"$1nowrap=\"nowrap\" ");
s=s.replace(/(<td[^>]*)(nowrap\>)/ig,"$1nowrap=\"nowrap\"\>");
s=s.replace(/ contenteditable=\"true\"/ig,"");
if(sTagName=="AREA")
{
s=s.replace(/ coords=\"0,0,0,0\"/ig," coords=\""+sCoords+"\"");
s=s.replace(/ shape=\"RECT\"/ig," shape=\""+sShape+"\"");
}
var bClosingTag=true;
if(sTagName=="IMG"||sTagName=="BR"||
sTagName=="AREA"||sTagName=="HR"||
sTagName=="INPUT"||sTagName=="BASE"||
sTagName=="LINK")//no closing tag
{
s=s.replace(/>$/ig," \/>").replace(/\/ \/>$/ig,"\/>");//no closing tag
bClosingTag=false;
}
sHTML+=s;
if(sTagName!="TEXTAREA")sHTML+= lineBreak1(sTagName)[1];
if(sTagName!="TEXTAREA")if(lineBreak1(sTagName)[1] !="") sHTML+= sT;//If new line, use base Tabs
if(bClosingTag)
{
s=oNode.outerHTML;
if(sTagName=="SCRIPT")
{
s = s.replace(/<script([^>]*)>[\n+\s+\t+]*/ig,"<script$1>");//clean spaces
s = s.replace(/[\n+\s+\t+]*<\/script>/ig,"<\/script>");//clean spaces
s = s.replace(/<script([^>]*)>\/\/<!\[CDATA\[/ig,"");
s = s.replace(/\/\/\]\]><\/script>/ig,"");
s = s.replace(/<script([^>]*)>/ig,"");
s = s.replace(/<\/script>/ig,"");
s = s.replace(/^\s+/,'').replace(/\s+$/,'');
sHTML+="\n"+
sT + "//<![CDATA[\n"+
sT + s + "\n"+
sT + "//]]>\n"+sT;
}
if(sTagName=="STYLE")
{
s = s.replace(/<style([^>]*)>[\n+\s+\t+]*/ig,"<style$1>");//clean spaces
s = s.replace(/[\n+\s+\t+]*<\/style>/ig,"<\/style>");//clean spaces
s = s.replace(/<style([^>]*)><!--/ig,"");
s = s.replace(/--><\/style>/ig,"");
s = s.replace(/<style([^>]*)>/ig,"");
s = s.replace(/<\/style>/ig,"");
s = s.replace(/^\s+/,"").replace(/\s+$/,"");
sHTML+="\n"+
sT + "<!--\n"+
sT + s + "\n"+
sT + "-->\n"+sT;
}
if(sTagName=="DIV"||sTagName=="P")
{
if(oNode.innerHTML==""||oNode.innerHTML=="&nbsp;")
{
sHTML+="&nbsp;";
}
else sHTML+=recur(oNode,sT+"\t");
}
else
{
sHTML+=recur(oNode,sT+"\t");
}
if(sTagName!="TEXTAREA")sHTML+=lineBreak1(sTagName)[2];
if(sTagName!="TEXTAREA")if(lineBreak1(sTagName)[2] !="")sHTML+=sT;//If new line, use base Tabs
if (sCloseTag.indexOf(":") >= 0) //deteksi jika tag tersebut adalah custom tag.
{
sHTML+="</" + sCloseTag.toLowerCase() + ">";//spy bisa <a:b>
} 
else 
{
sHTML+="</" + sTagName.toLowerCase() + ">";
}
}
}
}
else if(oNode.nodeType==3)//text
{
sHTML+= fixVal(oNode.nodeValue);
}
else if(oNode.nodeType==8)
{
if(oNode.outerHTML.substring(0,2)=="<"+"%")
{
sTmp=(oNode.outerHTML.substring(2));
sTmp=sTmp.substring(0,sTmp.length-2);
sTmp = sTmp.replace(/^\s+/,"").replace(/\s+$/,"");
var sT= sTab;
sHTML+="\n" +
sT + "<%\n"+
sT + sTmp + "\n" +
sT + "%>\n"+sT;
}
else
{//comments
sTmp=oNode.nodeValue;
sTmp = sTmp.replace(/^\s+/,"").replace(/\s+$/,"");
sHTML+="\n" +
sT + "<!--\n"+
sT + sTmp + "\n" +
sT + "-->\n"+sT;
}
}
else
{
;
}
}
return sHTML;
}
var buttonArrays=[];
var buttonArraysCount=0;
function writeIconToggle(id,command,img,title)
{
w=this.iconWidth;
h=this.iconHeight;
imgPath=this.scriptPath+this.iconPath+img;
sHTML=""+
"<td unselectable='on' style='padding:0px;padding-right:1px;VERTICAL-ALIGN: top;margin-left:0;margin-right:1px;margin-bottom:1px;width:"+w+"px;height:"+h+"px;'>"+
"<span unselectable='on' style='position:absolute;clip: rect(0 "+w+"px "+h+"px 0)'>"+
"<img name=\""+id+"\" id=\""+id+"\" btnIndex=\""+buttonArraysCount+"\" unselectable='on' src='"+imgPath+"' style='position:absolute;top:0;width:"+w+"px'"+
"onmouseover='doOver(this)' "+
"onmouseout='doOut(this)' "+
"onmousedown='doDown(this)' "+
"onmouseup=\"if(doUpToggle(this)){"+command+"}\" alt=\""+title+"\">"+
"</span></td>";
sHTML="<table align=left cellpadding=0 cellspacing=0 style='table-layout:fixed;'><tr>"+sHTML+"</tr></table>";
buttonArrays.push(["inactive"]);
buttonArraysCount++;
return sHTML;
}
function writeIconStandard(id,command,img,title,width)
{
w=this.iconWidth;
h=this.iconHeight;
if(width)w=width;
imgPath=this.scriptPath+this.iconPath+img;
sHTML=""+
"<td unselectable='on' style='padding:0px;padding-right:1px;VERTICAL-ALIGN: top;margin-left:0;margin-right:1px;margin-bottom:1px;width:"+w+"px;height:"+h+"px;'>"+
"<span unselectable='on' style='position:absolute;clip: rect(0 "+w+"px "+h+"px 0)'>"+
"<img name=\""+id+"\" id=\""+id+"\" btnIndex=\""+buttonArraysCount+"\" unselectable='on' src='"+imgPath+"' style='position:absolute;top:0;width:"+w+"px'"+
"onmouseover='doOver(this)' "+
"onmouseout='doOut(this)' "+
"onmousedown='doDown(this)' "+
"onmouseup=\"if(doUp(this)){"+command+"}\" alt=\""+title+"\">"+
"</span></td>";
sHTML="<table align=left cellpadding=0 cellspacing=0 style='table-layout:fixed;'><tr>"+sHTML+"</tr></table>";
buttonArrays.push(["inactive"]);
buttonArraysCount++;
return sHTML;
}
function writeBreakSpace()
{
w=this.iconWidth;
h=this.iconHeight;
imgPath=this.scriptPath+this.iconPath+"brkspace.gif";
sHTML=""+
"<td unselectable='on' style='padding:0px;padding-left:0px;padding-right:0px;VERTICAL-ALIGN:top;margin-bottom:1px;width:5px;height:"+h+"px;'>"+
"<img unselectable='on' src='"+imgPath+"'></td>";
sHTML="<table align=left cellpadding=0 cellspacing=0 style='table-layout:fixed;'><tr>"+sHTML+"</tr></table>";
return sHTML;
}
function writeDropDown(id,command,img,title,width)
{
w=width;
h=this.iconHeight;
imgPath=this.scriptPath+this.iconPath+oUtil.langDir+"/"+img;
sHTML=""+
"<td unselectable='on' style='padding:0px;padding-right:1px;VERTICAL-ALIGN: top;margin-left:0;margin-right:1px;margin-bottom:1px;width:"+w+"px;height:"+h+"px;'>"+
"<span unselectable='on' style='position:absolute;clip: rect(0 "+w+"px "+h+"px 0)'>"+
"<img name=\""+id+"\" id=\""+id+"\" btnIndex=\""+buttonArraysCount+"\" unselectable='on' src='"+imgPath+"' style='position:absolute;top:0;width:"+w+"px'"+
"onmouseover='doOver(this)' "+
"onmouseout='doOut(this)' "+
"onmousedown='doDown(this)' "+
"onmouseup=\"if(doUp(this)){"+command+"}\" alt=\""+title+"\">"+
"</span></td>";
sHTML="<table align=left cellpadding=0 cellspacing=0 style='table-layout:fixed;'><tr>"+sHTML+"</tr></table>";
buttonArrays.push(["inactive"]);
buttonArraysCount++;
return sHTML;
}
function doOver(btn)
{
btnArr=buttonArrays[btn.btnIndex];
if(btnArr[0]=="inactive")btn.style.top=-iconHeight;
}
function doDown(btn)
{
btnArr=buttonArrays[btn.btnIndex];
if(btnArr[0]!="disabled")btn.style.top=-iconHeight*2;
}
var bCancel=false;
function doOut(btn)
{
if(btn.style.top=="-"+iconHeight*2+"px")
{
bCancel=true;
}
btnArr=buttonArrays[btn.btnIndex];
if(btnArr[0]=="active")btn.style.top=-iconHeight*3;
if(btnArr[0]=="inactive")btn.style.top=0;
}
function doUpToggle(btn)
{
if(bCancel)
{//lagi pushed tapi mouseout (cancel)
bCancel=false;btn.style.top=0;
return false;
}
btnArr = buttonArrays[btn.btnIndex];
if(btnArr[0]=="inactive")
{
btn.style.top=-iconHeight*3;
btnArr[0]="active";
return true;
}
if(btnArr[0]=="active")
{
btn.style.top=-iconHeight;
btnArr[0]="inactive";
return true;
}
}
function doUp(btn)
{
if(bCancel)
{
bCancel=false;btn.style.top=0;
return false;
}
btnArr=buttonArrays[btn.btnIndex];
if(btnArr[0]=="disabled") return false;
btn.style.top=-iconHeight;
return true;
}
function makeEnablePushed(btn)
{
btnArr=buttonArrays[btn.btnIndex];
btnArr[0]="active";
btn.style.top=-iconHeight*3;
}
function makeEnableNormal(btn)
{
btnArr=buttonArrays[btn.btnIndex];
btnArr[0]="inactive";
btn.style.top=0;
}
function makeDisabled(btn)
{
btnArr=buttonArrays[btn.btnIndex];
btnArr[0]="disabled";
btn.style.top=-iconHeight*4;
}