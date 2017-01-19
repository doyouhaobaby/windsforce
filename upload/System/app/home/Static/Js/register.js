/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 用户注册验证($$)*/

/* 注册数据处理 */
function registerSubmit(){
	$("#register_submit").attr("disabled", "disabled");
	$("#register_submit").text(Q.L('注册','Common')+'...');
	Q.AjaxSubmit('register_form',Q.U('home://public/register_user'),'result',registerComplete);
};

function registerComplete(data,status){
	$("#register_submit").attr("disabled", false);
	$("#register_submit").text(Q.L('注册','Common'));
	if(status==1){
		sUrl=data.jumpurl?data.jumpurl:Q.U('home://public/login');
		setTimeout(function(){window.location.href=sUrl;},1000);
	}
};

/* 注册验证 */
$(document).ready(function(){
	var validator=$("#register_form").validate({
		rules: {
			user_name: {
				required: true,
				maxlength: 50, 
				remote:Q.U('home://public/check_user')
			},
			user_nikename: {
				maxlength: 50
			},
			user_password: {
				required: true,
				minlength: 6,
				maxlength: 32
			},
			user_password_confirm: {
				required: true,
				minlength: 6,
				maxlength: 32,
				equalTo: "#user_password"
			},
			user_email: {
				required: true,
				email: true,
				maxlength: 150,
				remote:Q.U('home://public/check_email')
			},
			user_terms: "required"
		},
		messages: {
			user_name: {
				required: Q.L("请输入你的注册用户名",'Common'),
				maxlength: jQuery.format(Q.L("注册用户名最多 {0} 字符",'Common')),
				remote: jQuery.format(Q.L("{0} 该用户已经被占用了",'Common'))
			},
			user_nikename: {
				maxlength: jQuery.format(Q.L("用户昵称最多 {0} 字符",'Common'))
			},
			user_password: {
				required: Q.L("请输入你的用户密码",'Common'),
				minlength: jQuery.format(Q.L("用户密码最少 {0} 字符",'Common')),
				maxlength: jQuery.format(Q.L("用户密码最多 {0} 字符",'Common'))
			},
			user_password_confirm: {
				required: Q.L("请输入你的确认密码",'Common'),
				minlength: jQuery.format(Q.L("确认密码最少 {0} 字符",'Common')),
				maxlength: jQuery.format(Q.L("确认密码最多 {0} 字符",'Common')),
				equalTo: Q.L("两次填写的密码不一致",'Common')
			},
			user_email: {
				required: Q.L("E-mail 地址不能为空",'Common'),
				email: Q.L("请输入一个正确的E-mail 地址",'Common'),
				maxlength: jQuery.format(Q.L("E-mail 地址最多 {0} 字符",'Common')),
				remote: jQuery.format(Q.L("{0} 该E-mail 地址已经被占用",'Common'))
			},
			user_terms: " "
		},
		errorPlacement: function(error,element){
			error.appendTo(element.parent().find('.input_errortips'));
		},
		submitHandler: function(){
			registerSubmit();
		},
		success: function(label){
			label.remove();
		}
	});
});
