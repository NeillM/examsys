<?php
print <<<END
<html>
<head>
  <title>Calculator</title>
  <link rel="stylesheet" type="text/css" href="scal.css" />
</head>
<body onkeypress="return checkButton(event);" onkeyup="return checkShift(event);" oncontextmenu="return false;">
<script language="JavaScript" src="calc.js"></script>
<script language="JavaScript">
  var arr_zn = ["7","8","9","/","C","4","5","6","*","sqr","1","2","3","-","=","0","z",".","+"],
  T = self.opener.sCal, a_img = [], i, j, l;
  function ch_img(v1, v2) {
    document.images[v1].src = 'images/'+v1+'_'+v2+'.gif';
  }
  function on_load() {
    //T.TCRmntr('C');
    T.t_load = true;
    if (T.control_obj.value == '') from_p = '0';
    else from_p = T.control_obj.value;
    document.forms[0].elements[0].value = from_p;
  }
  for (i = 0; i < document.links.length; i++) {
    l = document.links[i];
    l.onmousedown = Function("ch_img(" + i + ",0)")
    l.onmouseout = Function("ch_img(" + i + ",1)")
    l.onmouseup = l.onmouseover = Function("ch_img(" + i + ",2)")
    l.onclick = l.ondblclick = Function("T.TCRmntr('" + arr_zn[i] + "')");
  }

  var urlParams = {};
  (function () {
    var e,
            a = /\+/g,  // Regex for replacing addition symbol with a space
            r = /([^&=]+)=?([^&]*)/g,
            d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
            q = window.location.search.substring(1);

    while (e = r.exec(q))
      urlParams[d(e[1])] = d(e[2]);
  })();
</script>

<form name="sCal">
<div align="center" style="margin-top:6px">
<table>
<tr>
 <td colspan="6"></td>
 </tr>
  <tr><td colspan="5"><div id="ans" class="ans">0</div></td></tr>
  <tr><td colspan="5"><textarea name="IOx" rows="3" cols="16" class="LCD"></textarea></td></tr>
  <tr><td colspan="5"><div name="memory" id="memory" class="memory">&nbsp;</div></td></tr>

<tr>

END;

  if (isset($_GET['calc']) and $_GET['calc'] != 2) {
print <<<END
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('x / Math.abs(x) * Math.log(Math.abs(x) + Math.sqrt(x * x + 1))')">asinh</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork(' 2 * Math.log(Math.sqrt((x + 1) / 2) + Math.sqrt((x - 1) / 2))')">acosh</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork(' Math.log((x - 1) / (x + 1)) / 2')">atanh</a></div></td>

END;

} else {
  print <<<END
 <td></td><td></td><td></td>
END;
  }
print <<<END

  <td><div class="b">
<script language="javascript">
  if (urlParams["form"] != "undefined") {
    document.write('<a href="#" class="ret" onclick="returntoform()">RET</a>')
  }
</script>
</div></td>
<td><div class="b"><a href="#" class="off" onClick="window.close()">OFF</a></td>
</tr>

END;

  if (isset($_GET['calc']) and $_GET['calc'] != 2) {
print <<<END

<tr>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('( Math.exp(x) - 1 / Math.exp(x) )/ 2')">sinh</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('( Math.exp(x) + 1 / Math.exp(x) )/ 2')">cosh</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('( Math.exp(x) - 1 / Math.exp(x) )/ ( Math.exp(x) + 1 / Math.exp(x) )')">tanh</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.exp(x)')">&nbsp;e<sup><i><span style="font-family:'Times New Roman',serif; font-size:130%; padding-left:2px">x</span></i></sup></a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.pow(10,x)')">&nbsp;10<sup><i><span style="font-family:'Times New Roman',serif; font-size:130%; padding-left:2px">x</span></i></sup></a></div></td>
</tr>

<tr>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.asin(x)*180/Math.PI')">asin</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.acos(x)*180/Math.PI')">acos</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.atan(x)*180/Math.PI')">atan</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.log(x)')">ln</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.log(x)*Math.LOG10E')">log</a></div></td>
</tr>


<tr>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.sin(x*Math.PI/180)')">sin</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.cos(x*Math.PI/180)')">cos</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.tan(x*Math.PI/180)')">tan</a></div></td>
  <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.PI')"><span style="font-family:'Times New Roman',serif; font-size:150%">&pi;</span></a></div></td>
  <td><div class="b"><a href="#" class="b1"  onclick="Xwork('Math.E')">e</a></div></td>
</tr>

  <tr>
   <td><div class="b"><a href="#" class="b1" onclick="Xwork('Math.sqrt(x)')" style="font-family:'Times New Roman'">&radic;</a></div></td>
   <td><div class="b"><a href="#" class="b1" onclick="xSquare()"><span style="font-family:'Times New Roman'; font-size:150%"><i>x</i></span><span style="font-size:90%; padding-left:1px"><sup>2</sup></span></a></div></td>
   <td><div class="b"><a href="#" class="b1" onclick="recip()">1/<span style="font-family:'Times New Roman',serif; font-size:110%"><i>x</i></span></a></div></td>
   <td><div class="b"><a href="#" class="b1" onclick="Xwork('for(j=x;j>2;j--){x*=j-1;}')"><span style="font-family:'Times New Roman',serif; font-size:150%"><i>x</i></span>!</a></div></td>
   <td><div class="b"><a href="#" class="b1" onclick="xPlusEq('^')">&nbsp;<span style="font-family:'Times New Roman',serif; font-size:150%"><i>x</i></span><span style="font-size:90%; padding-left:1px"><sup><i>y</i></sup></span></a></div></td>
  </tr>

END;

  }
    ?>
<tr>

  <td><div class="b"><a href="#" class="b2" onclick="xMultEq('-1')">&plusmn;</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('(')">(</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(')')">)</a></div></td>
  <td><div class="b"><a href="#" class="del" onClick="BkSpace()">DEL</a></div></td>
  <td><div class="b"><a href="#" class="ac" onclick="Clear()">AC</a></div></td>
  </td>
</tr>

<tr>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(7)">7</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(8)">8</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(9)">9</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('/')">&divide;</a></div></td>
  <td><div class="b"><a href="#" class="b2 memBut" onclick="Mclear()">MC</a></div></td>
</tr>

<tr>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(4)">4</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(5)">5</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(6)">6</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('*')">&times;</a></div></td>
  <td><div class="b"><a href="#" class="b2 memBut" onclick="MtoX()">MR</a></div></td>
</tr>

<tr>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(1)">1</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(2)">2</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq(3)">3</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('-')">-</a></div></td>
  <td><div class="b"><a href="#" class="b2 memBut" onclick="Mminus()">M-</a></div></td>
</tr>

<tr>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('0')">0</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('.')">.</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xEval()">=</a></div></td>
  <td><div class="b"><a href="#" class="b2" onclick="xPlusEq('+')">+</a></div></td>
  <td><div class="b"><a href="#" class="b2 memBut" onclick="Mplus()">M+</a></div></td>
</tr>

</table>
</div>
</form>
<script language="JavaScript">
if (urlParams["form"]!="undefined") {
  x=window.opener.document[urlParams["form"]][urlParams["field"]].value;
  Ox();
}
</script>
</body>
</html>
