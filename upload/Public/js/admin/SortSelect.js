/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 后台排序($$)*/

function sortSelect(sFormName,sSortPan,sSearchName,nJumpNum){
	//对象属性
	this.formObj=document.getElementsByName(sFormName)[0];
	this.sortPan=document.getElementsByName(sSortPan)[0];
	this.searchObj=document.getElementsByName(sSearchName)[0];
	this.jumpNum=document.getElementsByName(nJumpNum)[0];

	//方法：列表搜索
	this.search=function(){
		var nLength=this.sortPan.options.length;
		for(nI=0;nI<nLength;nI++){
			if(this.sortPan.options[nI].text.indexOf(this.searchObj.value)!=-1){
				this.sortPan.item(nI).selected=true;
				break;
			}
		}
	}
	
	//方法：跳转
	this.jump=function(){
		var nN=this.jumpNum.value;
		var nIIndex=this.sortPan.selectedIndex;
		var nI=nN-1;
		if(nI==nIIndex) return;
		if(nI<nIIndex){
			for(nK=0;nK<nIIndex-nI;nK++)this.moveUp();
		}
		else {
			for(nK=0;nK<nI-nIIndex;nK++)this.moveDown();
		}
	}
	
	//方法：上移一位
	this.moveUp=function(){
		try{
			var nIIndex=this.sortPan.selectedIndex;

			if(nIIndex==0){
				return;
			}

			var sCurName=this.sortPan.item(nIIndex).text;
			var nIlength,nIplace;

			nIlength=sCurName.length;
			nIplace=sCurName.indexOf(".");
			var sNameState,sNameSpace,

			sNameState=sCurName.substr(nIplace+1,nIlength),
			sNameSpace=sCurName.substr(0,nIplace+1);

			var sNameStateMiddle,
			sNameStateMiddle=sNameState;

			var sCurName1=this.sortPan.item(nIIndex-1).text;
			var nIlength1,nIplace1,
			nIlength1=sCurName1.length;
			nIplace1=sCurName1.indexOf(".");

			var sNameState1,sNameSpace1
			sNameState1=sCurName1.substr(nIplace1+1,nIlength1),
			sNameSpace1=sCurName1.substr(0,nIplace1+1),
			sNameState=sNameState1,
			sNameState1=sNameStateMiddle;

			this.sortPan.item(nIIndex).text=sNameSpace+sNameState
			this.sortPan.item(parseInt(nIIndex)-1).text=sNameSpace1+sNameState1
			var sCurValue=this.sortPan.item(nIIndex).value;
			this.sortPan.item(nIIndex).value=this.sortPan.item(parseInt(nIIndex)-1).value;
			this.sortPan.item(parseInt(nIIndex)-1).value=sCurValue;
			this.sortPan.item(parseInt(nIIndex)-1).selected=true;
		}catch(e){
			return;
		}
	}

	//方法：移动到第一位
	this.moveFirst=function(){
		try{
			var nIIndex=this.sortPan.selectedIndex;

			if(nIIndex==0){
				return;
			}

			while(nIIndex>0){
				var sCurName=this.sortPan.item(nIIndex).text;
				var nIlength,nIplace,

				nIlength=sCurName.length;
				nIplace=sCurName.indexOf(".");

				var sNameState,sNameSpace,
				sNameState=sCurName.substr(nIplace+1,nIlength),
				sNameSpace=sCurName.substr(0,nIplace+1);

				var sNameStateMiddle,
				sNameStateMiddle=sNameState;

				var sCurName1=this.sortPan.item(nIIndex-1).text;
				var nIlength1,nIplace1,
				nIlength1=sCurName1.length;
				nIplace1=sCurName1.indexOf(".");

				var sNameState1,sNameSpace1,
				sNameState1=sCurName1.substr(nIplace1+1,nIlength1),
				sNameSpace1=sCurName1.substr(0,nIplace1+1),
				sNameState=sNameState1,
				sNameState1=sNameStateMiddle;

				this.sortPan.item(nIIndex).text=sNameSpace+sNameState
				this.sortPan.item(parseInt(nIIndex)-1).text=sNameSpace1+sNameState1
				var sCurValue=this.sortPan.item(nIIndex).value;
				this.sortPan.item(nIIndex).value=this.sortPan.item(parseInt(nIIndex)-1).value;
				this.sortPan.item(parseInt(nIIndex)-1).value=sCurValue;
				this.sortPan.item(parseInt(nIIndex)-1).selected=true;
				nIIndex=nIIndex-1
			}
		}catch(e){
			return;
		}
	}
	
	//方法：下移一位
	this.moveDown=function(){
		try{
			var nIIndex=this.sortPan.selectedIndex;
			if(nIIndex==this.sortPan.length-1){
				return;
			}

			var sCurName=this.sortPan.item(nIIndex).text;
			var nIlength,nIplace,
			nIlength=sCurName.length;
			nIplace=sCurName.indexOf(".");

			var sNameState,sNameSpace,
			sNameState=sCurName.substr(nIplace+1,nIlength),
			sNameSpace=sCurName.substr(0,nIplace+1);

			var sNameStateMiddle,
			sNameStateMiddle=sNameState;

			var sCurName1=this.sortPan.item(nIIndex+1).text;
			var nIlength1,nIplace1,

			nIlength1=sCurName1.length;
			nIplace1=sCurName1.indexOf(".");

			var sNameState1,sNameSpace1,
			sNameState1=sCurName1.substr(nIplace1+1,nIlength1),
			sNameSpace1=sCurName1.substr(0,nIplace1+1),
			sNameState=sNameState1,
			sNameState1=sNameStateMiddle;

			this.sortPan.item(nIIndex).text=sNameSpace+sNameState;
			this.sortPan.item(parseInt(nIIndex)+1).text=sNameSpace1+sNameState1;
			var sCurValue=this.sortPan.item(nIIndex).value;
			this.sortPan.item(nIIndex).value=this.sortPan.item(parseInt(nIIndex)+1).value;
			this.sortPan.item(parseInt(nIIndex)+1).value=sCurValue;
			this.sortPan.item(parseInt(nIIndex)+1).selected=true;
		}catch(e){
			return;
		}
		
	}
	
	//方法：移动到最后
	this.moveEnd=function(){
		try{
			var nIIndex=this.sortPan.selectedIndex;
			if(nIIndex==this.sortPan.length-1){
				return;
			}

			while(nIIndex<(this.sortPan.length-1)){
				var sCurName=this.sortPan.item(nIIndex).text;
				var nIlength,nIplace,
				nIlength=sCurName.length;
				nIplace=sCurName.indexOf(".");

				var sNameState,sNameSpace,
				sNameState=sCurName.substr(nIplace+1,nIlength),
				sNameSpace=sCurName.substr(0,nIplace+1);

				var sNameStateMiddle,
				sNameStateMiddle=sNameState;

				var sCurName1=this.sortPan.item(nIIndex+1).text;
				var nIlength1,nIplace1,
				nIlength1=sCurName1.length;
				nIplace1=sCurName1.indexOf(".");

				var sNameState1,sNameSpace1,
				sNameState1=sCurName1.substr(nIplace1+1,nIlength1),
				sNameSpace1=sCurName1.substr(0,nIplace1+1),
				sNameState=sNameState1,
				sNameState1=sNameStateMiddle;

				this.sortPan.item(nIIndex).text=sNameSpace+sNameState;
				this.sortPan.item(parseInt(nIIndex)+1).text=sNameSpace1+sNameState1;
				var sCurValue=this.sortPan.item(nIIndex).value;
				this.sortPan.item(nIIndex).value=this.sortPan.item(parseInt(nIIndex)+1).value;
				this.sortPan.item(parseInt(nIIndex)+1).value=sCurValue;
				this.sortPan.item(parseInt(nIIndex)+1).selected=true;
				nIIndex=nIIndex+1
			}
		}catch(e){
			return;
		}
	}
	
	//方法：排序保存
	this.ok=function(){
		var sStr='';
		var nIplace;

		for(nI=0;nI<=this.sortPan.options.length-1;nI++){
			nIplace=this.sortPan.options[nI].text.indexOf(".");
			sStr+=this.sortPan.options[nI].value+":"+this.sortPan.options[nI].text.substr(0,nIplace)+",";
		}

		this.formObj.moveResult.value=sStr.substr(0,sStr.length-1);
	}

}
