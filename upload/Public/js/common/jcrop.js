/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 裁剪($$)*/

$(function(){
	var jcrop_api, boundx, boundy;
	$('#cropbox').Jcrop({
		aspectRatio: 1,
		onSelect: updateCoords,
		onChange:showCoords,
		onRelease:clearCoords
	},function(){
		var bounds = this.getBounds();
		boundx = bounds[0];
		boundy = bounds[1];
		jcrop_api = this;
		});

	function updatePreview(c){
		if (parseInt(c.w) > 0){
			var rx = 100 / c.w;
			var ry = 100 / c.h;
			$('#preview').css({
				width: Math.round(rx * boundx) + 'px',
				height: Math.round(ry * boundy) + 'px',
				marginLeft: '-' + Math.round(rx * c.x) + 'px',
				marginTop: '-' + Math.round(ry * c.y) + 'px'
			});
		}
	};

	function updateCoords(c){
		updatePreview(c);
		$('#x').val(c.x);
		$('#y').val(c.y);
		$('#w').val(c.w);
		$('#h').val(c.h);
	};

	function showCoords(c){
		updatePreview(c);
		$('#x1').val(c.x);
		$('#y1').val(c.y);
		$('#x2').val(c.x2);
		$('#y2').val(c.y2);
		$('#w').val(c.w);
		$('#h').val(c.h);
	};

	function clearCoords(){
		$('#coords input').val('');
	};

});

function checkCoords(){
	if (parseInt($('#w').val())){ 
		return true;
	}
	windsforceAlert(Q.L('请先裁剪，然后点击提交','Common'),'',3);

	return false;
};
