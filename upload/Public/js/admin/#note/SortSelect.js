//Javascript代码
var move = new sortList('form1','sort','search','jumpNum');

//HTML代码
<form method="post" name = 'form1' action="">
<input name="search" type="text">
<input type="button" onclick="move.search()" value="查 询" >
<select name="sort" size="12">
<option value=1>1.name1</option>
<option value=2>2.name2</option>
<option value=3>3.name3</option>
</select>
<input type="button" onclick="move.moveFirst()" value="第一" >
<input type="button" onclick="move.moveUp()" value="上移" >
<input type="button" onclick="move.moveDown()" value="下移" >
<input type="button" onclick="move.moveEnd()" value="最后" >
<input type="text" name="jumpNum" size="5">
<input type="button" onclick="move.jump()" value="跳转" >
<input type="hidden" name="moveResult">
<input type="button" onclick="move.ok()" value="确定">
</form>

//结果
确定后排序的结果保存在moveResult中，格式为
编号:序号,编号:序号
