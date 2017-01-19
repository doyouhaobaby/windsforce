/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 后台将Option变成可以选择的checkbox($$)*/

function selectAll(){
	var arrColInputs=document.getElementsByTagName("input");

	for(var nI=0;nI<arrColInputs.length;nI++){
		arrColInputs[nI].checked=true;
	}
}

function unSelectAll(){
	var arrColInputs=document.getElementsByTagName("input");

	for(var nI=0;nI<arrColInputs.length;nI++){
		arrColInputs[nI].checked=false;
	}
}

function inverSelect(){
	var arrColInputs=document.getElementsByTagName("input");

	for(var nI=0;nI<arrColInputs.length;nI++){
		arrColInputs[nI].checked=!arrColInputs[nI].checked;
	}
}

function addEvent(element,event,func){
	if(Q.Browser.Ie){
		element.attachEvent(event,func);
	}else if(element.addEventListener){
		element.addEventListener(event,func,false);
	}
}

function CreateManySelects(){
	var oSelectObj=document.getElementsByTagName('select');

	if(oSelectObj ){
		for(var nI=0;nI<oSelectObj.length;nI++){
			if(!oSelectObj[nI].multiple ){
				continue;
			}

			var oSelect=oSelectObj[nI];
			var bDisabled=oSelect.disabled ? true : false;
			var nW=oSelect.offsetWidth;
			var nH=oSelect.offsetHeight;
			var oDivEle=document.createElement('div');

			oDivEle.style.overflow='auto';
			oDivEle.style.width=nW + "px";
			oDivEle.style.height=nH + "px";
			oDivEle.style.border="2px inset white";
			oDivEle.style.font="10pt Arial";
			oDivEle.className='customMultipleSelect';
			oOptionObj=oSelect.getElementsByTagName('option');
			for(var nJ=0;nJ<oOptionObj.length;++nJ){
				var oSpanEle=document.createElement('div');
				oSpanEle.style.paddingLeft="20px";
				oSpanEle.style.cursor="default";
				oSpanEle.className='customMultipleSelect_option'
				addEvent(oSpanEle,'onmousedown',function(){
					if(Q.Browser.Ie && event.srcElement.tagName.toLowerCase()=='div' && !event.srcElement.firstChild.disabled){
						event.srcElement.childNodes[0].checked=!event.srcElement.childNodes[0].checked;
					}
				})

				var oInputEle=document.createElement('input');
				oInputEle.type="checkbox";
				oInputEle.className=oSelectObj[nI].className;

				if(oOptionObj[nJ].selected){
					oInputEle.checked=true;
					oInputEle.defaultChecked=true;
				}

				if(bDisabled){
					oInputEle.disabled=true;
				}

				oInputEle.value=oOptionObj[nJ].value;
				oInputEle.style.marginLeft="-16px";
				oInputEle.style.marginRight="5px";
				oInputEle.style.marginTop="-2px";
				oInputEle.name=oSelect.name;

				var oTextLabel=document.createTextNode(oOptionObj[nJ].text);
				oSpanEle.appendChild(oInputEle);
				oSpanEle.appendChild(oTextLabel);
				oDivEle.appendChild(oSpanEle);
			}

			oSelect.parentNode.insertBefore(oDivEle,oSelect);
			oSelect.parentNode.removeChild(oSelect );
		}
	}
}

addEvent(window,Q.Browser.Ie?'onload':'load',CreateManySelects);
