var uploadPlugin = function (name) {
	var swfu;
	var Example=this;

	this.nember=0;
	this.Name=name;
	this.sysParameter = {
		"upload_url": Q.U('home://attachment/flash_upload'),
		"file_post_name": "Filedata",
		"post_params": {"__auth__":sAuth,"user_id":nUserid,"user_name":sUsername,'attachmentcategory_id':nAttachmentcategoryId,'__PHPSESSID__':sPHPSESSID},
		"http_success": [201,201,203],
		"use_query_string":false,
		"assume_success_timeout":0,
		"file_types":sAllAllowType,
		"file_types_description":Q.L('所有允许类型','Common'),
		"file_size_limit":max_upload_size,
		"file_upload_limit":nUploadFlashLimit,
		"debug":false,
		"prevent_swf_caching":true,
		"preserve_relative_urls":false
	};
	this.UIParameter={
		"button_placeholder_id": "selectfile",
		"button_image_url": _ROOT_ + "/Public/js/swfupload/images/add.png",
		"button_width": 100,
		"button_height": 27,
		"button_text": "",
		"button_text_style": "",
		"button_text_left_padding": 0,
		"button_text_top_padding": 0,
		"button_action": SWFUpload.BUTTON_ACTION.SELECT_FILES,
		"button_disabled": false,
		"button_cursor": SWFUpload.CURSOR.HAND,
		"button_window_mode": SWFUpload.WINDOW_MODE.TRANSPARENT
	};
	this.fileQueueError=false;

	this.Init = function () {
		swfu = new SWFUpload({
			upload_url: this.sysParameter.upload_url,
			flash_url: _ROOT_ + "/Public/js/swfupload/swfupload.swf",
			flash9_url: _ROOT_ + "/Public/js/swfupload/swfupload_fp9.swf",
			file_post_name: this.sysParameter.file_post_name,
			post_params: this.sysParameter.post_params,
			use_query_string: this.sysParameter.use_query_string,
			http_success: this.sysParameter.http_success, //表示服务端在遇到列表内的值后也会返回数据不光是200状态才能返回
			assume_success_timeout: this.sysParameter.assume_success_timeout, //上传超时
			file_types: this.sysParameter.file_types,
			file_types_description: this.sysParameter.file_types_description,
			file_size_limit: this.sysParameter.file_size_limit,
			file_upload_limit: this.sysParameter.file_upload_limit,
			debug: this.sysParameter.debug,
			prevent_swf_caching: this.sysParameter.prevent_swf_caching,
			preserve_relative_urls: this.sysParameter.preserve_relative_urls,

			button_placeholder_id: this.UIParameter.button_placeholder_id,
			button_image_url: this.UIParameter.button_image_url,
			button_width: this.UIParameter.button_width,
			button_height: this.UIParameter.button_height,
			button_text: this.UIParameter.button_text,
			button_text_style: this.UIParameter.button_text_style,
			button_text_left_padding: this.UIParameter.button_text_left_padding,
			button_text_top_padding: this.UIParameter.button_text_top_padding,
			button_action: this.UIParameter.button_action,
			button_disabled: this.UIParameter.button_disabled,
			button_cursor: this.UIParameter.button_cursor,
			button_window_mode: this.UIParameter.button_window_mode,

			swfupload_loaded_handler: this.swfupload_loaded_function, //上传组件初始化成功
			swfupload_load_failed_handler: this.swfupload_load_failed_function, //上传组件加载失败
			file_queued_handler: this.file_queued_function, //文件选择后
			file_queue_error_handler: this.file_queue_error_function, //文件选择后出错
			file_dialog_complete_handler: this.file_dialog_complete_function, //文件选择后(多少文件添加进来)
			upload_start_handler: this.upload_start_function, //上传前
			upload_progress_handler: this.upload_progress_function, //上传进行中
			upload_error_handler: this.upload_error_function, //上传错误
			upload_success_handler: this.upload_success_function, //上传成功，还没有保存
			upload_complete_handler: this.upload_complete_function //真正上传成功，上载的数据已经保存，可又上传下一个文件了（如果有）
		});

		setBotton("upload", 3);
		setBotton("delete", 3);

		$("#delete").click(function () { Example.RemoveAllFile() });

		if(nUploadIsauto==0){
			$("#upload").click(function () { Example.StartUpload() });
		}
	}

	this.addPostParam=function(key,value){
		swfu.addPostParam(key,value);
	}

	this.swfupload_loaded_function = function () {
		setMessage(Q.L('上传组件初始化成功','Common'));
	}

	this.swfupload_load_failed_function = function () {
		setMessage(Q.L('上传组件初始化失败，请确认您的浏览器已经安装了flash player插件','Common'),1);
	}

	this.file_queued_function = function (file) {
		Example.nember++;
		$("#attachment_flash_box").append('<tr id="tag_' + file.index + '" tag="item">' +
			'<td class="no">' + Example.nember + '<input type="hidden" name="attachids[]" class="attachids" id="tag_attachid_' + file.index + '" value="" /></td>' +
			'<td class="status"><img src="' + getStatusImg(0) + '" title="'+Q.L('等待上传','Common')+'" /></td>' +
			'<td class="name" title="' + file.name + '">' + file.name + '</td>' +
			'<td class="size">' + getFormatSize(file.size) + '</td>' +
			'<td class="flashprogress"><span id="pr"><img src="' + _ROOT_ + '/Public/js/swfupload/images/im.jpg" width="0" /></span>&nbsp;<span tag="con">0</span>%</td>'+
			'<td class="exec"><img src="' + _ROOT_ + '/Public/js/swfupload/images/remove.png" style="cursor:pointer;" title="'+Q.L('移除文件','Common')+'" onclick="' + Example.Name + '.RemoveFile(\'' + file.id + '\',this,\'' + file.index + '\');" /> '+(insert_attach==1?'<img id="insertattach_' + file.index + '" src="' + _ROOT_ + '/Public/js/swfupload/images/insert.png" style="cursor:pointer;display:none;" title="'+Q.L('插入附件','Common')+'" onclick="' + Example.Name + '.InsertIn(\'' + file.index + '\');" />':'')
			+'</td>' +
		'</tr>');

		//if (swfu.getStats().files_queued >= Example.sysParameter.file_upload_limit)
		//swfu.setButtonDisabled(true);

		if (swfu.getStats().files_queued > 0) {
			setBotton("upload", 0);
			setBotton("delete", 0);
		}
	}

	this.file_queue_error_function = function (file, code, message) {
		switch (code) {
			case -100:
				setMessage(Q.L('添加文件出错！您添加的文件太多，一次最多允许添加个','Common') + Example.sysParameter.file_upload_limit.toString() + " "+Q.L('文件','Common'),1);
				this.fileQueueError=true;
				break;
			case -110:
				setMessage(Q.L('添加文件出错！您添加太大，最大允许添加','Common')+ Example.sysParameter.file_size_limit + " "+Q.L('的文件','Common'),1);
				this.fileQueueError=true;
				break;
			case -120:
				setMessage(Q.L('添加文件出错！您添加的文件是0字节','Common'),1);
				this.fileQueueError=true;
				break;
			case -130:
				setMessage(Q.L('添加文件出错！您添加的文件类型不正确','Common'),1);
				this.fileQueueError=true;
				break;
		}

	}

	this.file_dialog_complete_function = function (selected, queued, total) {
		if (selected == 0) {
			setMessage(Q.L('请选择文件，可以多选','Common'),1);
		} else if (queued == 0) {
			if(this.fileQueueError===false){
				setMessage(Q.L('添加文件失败！共有0个文件入队，这可能是因为您一次选择的文件太多，或文件太大','Common'),1);
			}
			this.fileQueueError=false;
		} else {
			setMessage(Q.L('添加文件成功！共有','Common') + total.toString() + Q.L('个文件加入了上传列队','Common'));
			$(".attachment_flash_mainbox").show();
			$("#upload").show();
			$("#delete").show();
			jQuery('#cancel-upload').prop('disabled', false);

			if(nUploadIsauto==1){
				Example.StartUpload();
			}
		}
	}

	this.upload_start_function = function (file) {
		//swfu.addPostParam("FileType", Example.sysParameter.file_types.replace(/;/gi, ","));
		//swfu.addPostParam("FileSize", getbyte(Example.sysParameter.file_size_limit));

		if (swfu.getStats().files_queued > 0){
			return true;
		}
	}

	this.upload_progress_function = function (file, bytes, total) {
		$("#tag_" + file.index + " #pr img").attr("width", parseInt(getPercentageimg(bytes, total)).toString());
		$("#tag_" + file.index + " #pr img").attr("height", 9);
		$("#tag_" + file.index + " .flashprogress span[tag='con']").html(getPercentage(bytes, total).toString());
		$("#tag_" + file.index + " .status img").attr("src", getStatusImg(1));
		$("#tag_" + file.index + " .status img").attr("title", Q.L('正在上传文件','Common'));
		$("#tag_" + file.index + " .exec img").attr("disabled", true);
		$("#tag_" + file.index + " .exec img").attr("title", Q.L('文件正在上传，不可以删除','Common'));
	}

	this.upload_error_function = function (file, code, message) {
		switch (code) {
			case -200:
				setMessage(Q.L('上传出错！服务器错误','Common'),1);
				break;
			case -210:
				setMessage(Q.L('上传出错！找不到上传路径','Common'),1);
				break;
			case -220:
				setMessage(Q.L('上传出错！可能是上传目录没有权限','Common'),1);
				break;
			case -230:
				setMessage(Q.L('上传出错！安全错误，上传违反了安全约束','Common'),1);
				break;
			case -240:
				setMessage(Q.L('上传出错！上传文件的数量超过了限定的值','Common'),1);
				break;
			case -250:
				setMessage(Q.L('上传出错！尝试初始化上传时出现了错误','Common'),1);
				break;
			case -260:
				setMessage(Q.L('上传出错！没有找到要上传的文件','Common'),1);
				break;
			case -270:
				setMessage(Q.L('上传出错！未知错误','Common'),1);
				break;
			case -280:
				setMessage(Q.L('上传出错！取消了文件','Common') + file.name + " "+Q.L('的上传','Common'),1);
				break;
			case -290:
				setMessage(Q.L('上传出错！暂停了文件','Common')+ file.name + " "+Q.L('的上传','Common'),1);
				break;
		}
	}

	this.upload_success_function = function (file, data, response) {
		// if async-upload returned an error message, place it in the media item div and return
		if ( data.match('upload-error') ) {
			/*alert(data.replace('upload-error:',''));*/
			$("#tag_" + file.index + " .flashprogress").html(data.replace('upload-error:',''));
			return false;
		}

		//如果成功上传并写入数据库,data返回的是attach数据表里的id
		$("#tag_attachid_" + file.index).val(data);
		$("#insertattach_" + file.index).show();

		$("#tag_" + file.index + " .status img").attr("src", getStatusImg(3));
		$("#tag_" + file.index + " .status img").attr("title", Q.L('上传成功','Common'));
		$("#tag_" + file.index + " .flashprogress").html(Q.L('文件上传成功','Common'));
		$("#tag_" + file.index).attr("disabled", true);
		$("#tag_" + file.index).attr("disabled", true);
		$("#tag_" + file.index + " .exec img").attr("disabled", false);
		$("#tag_" + file.index + " .exec img").attr("title", Q.L('移除此上传完成的文件','Common'));
		
		//$("#tag_" + file.index + " .exec #insertattach_" + file.index).attr("title", Q.L('插入附件','Common'));

		if(insert_attach==1){
			$("#insertattach_" + file.index).attr("title", Q.L('插入附件','Common'));
		}

		if(swfu.getStats().files_queued==0){
			if(nDialog==0){
				$("#flash-upload").attr("disabled",false);
			}else{
				setTimeout(function(){
					window.location.href=Q.U('home://attachment/lists?cid='+nAttachmentcategoryId+'&dialog=1&function='+sFunction+'&filetype='+nFiletype+'&full='+nFull);
				},1000);
			}
		}

		/*
		$("#tag_" + file.index + " .status img").attr("src", getStatusImg(2));
		$("#tag_" + file.index + " .status img").attr("title", Q.L('上传成功，正在保存','Common'));
		$("#tag_" + file.index + " .flashprogress").html(Q.L('上传成功，正在保存','Common'));
		*/
	}

	this.upload_complete_function = function (file) {
		if (swfu.getStats().files_queued > 0) {
			swfu.startUpload();
		} else {
			swfu.setButtonDisabled(false);
			setBotton("delete", 0);
		}
	}

	this.InsertIn = function (index) {
		var attachid = $("#tag_attachid_" + index).val();
		addattach(attachid);
	}

	this.RemoveFile = function (id, obj, index) {
		if(confirm(Q.L('您确定要删除这个附件吗?','Common'))){
			var attachid = $("#tag_attachid_" + index).val();
			$.get( Q.U('home://attachment/delete_attachment'), {id:attachid},function(data){});
			Example.nember--;
			$(obj).parent().parent().remove();
			swfu.cancelUpload(id, true);
			if (swfu.getStats().files_queued < Example.sysParameter.file_upload_limit)
				swfu.setButtonDisabled(false);

			var objfile = swfu.getFile(id);
			if (!objfile)
				swfu.setStats({ "successful_uploads": swfu.getStats().successful_uploads - 1 });

			if (swfu.getStats().files_queued <= 0 && swfu.getStats().successful_uploads <= 0) {
				$(".attachment_flash_mainbox").hide();
				$("#upload").hide();
				$("#delete").hide();
				//setBotton("upload", 3);
				//setBotton("delete", 3);
			}
		}
	}

	this.RemoveAllFile = function () {
		if(confirm(Q.L('您确定要删除所有附件吗?','Common')))
		{
			var attachids = '';
			var comma = '';
			$('.attachment_flash_mainbox .attachids').each(function(){
				attachids = attachids + comma + $(this).val();
				comma = ',';
			});
			$.post( Q.U('home://attachment/delete_attachments'), {ids:attachids},function(data){});
			Example.nember = 0;
			for (var i = 0; i < swfu.getStats().files_queued; i++)
				swfu.cancelUpload();
			swfu.setStats({ "successful_uploads": 0 });
			$(".attachment_flash_mainbox ul[tag='item']").remove();
			swfu.setButtonDisabled(false);
			$(".attachment_flash_mainbox").hide();
			$("#upload").hide();
			$("#delete").hide();

			//setBotton("upload", 3);
			//setBotton("delete", 3);
		}
	}

	this.StartUpload = function () {
		if (swfu.getStats().files_queued <= 0) {
			setMessage(Q.L('请先添加文件','Common'),1);
			return;
		}
		setBotton("upload", 3);
		setBotton("delete", 3);
		swfu.setButtonDisabled(true);

		swfu.startUpload();
	}
}

function swfuploadPreLoad() {
	if ( !uploaderMode ) {
		switchUploader(1);
	} else {
		switchUploader(0);
	}
}

function switchUploader(s) {
	if ( s ) {
		$('#flash-upload-ui').show();
		$('#html-upload-ui').hide();
	} else {
		$('#flash-upload-ui').hide();
		$('#html-upload-ui').show();
	}
}
