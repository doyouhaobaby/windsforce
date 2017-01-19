/**
 * jQuery's jqfaceedit Plugin
 *
 * @author cdm
 * @version 0.2
 * @copyright Copyright(c) 2012.
 * @date 2012-08-09
 */

 /**
  * Modify by 小牛哥QEEPHP
  */
(function($) {
    var em = [
                {'id':1,'title':$EMOTIONSMSG.emotions_1,'url':'1.gif','phrase':'[emotions_1]'},{'id':2,'title':$EMOTIONSMSG.emotions_2,'url':'2.gif','phrase':'[emotions_2]'},
                {'id':3,'title':$EMOTIONSMSG.emotions_3,'url':'3.gif','phrase':'[emotions_3]'},{'id':4,'title':$EMOTIONSMSG.emotions_4,'url':'4.gif','phrase':'[emotions_4]'},
                {'id':5,'title':$EMOTIONSMSG.emotions_5,'url':'5.gif','phrase':'[emotions_5]'},{'id':6,'title':$EMOTIONSMSG.emotions_6,'url':'6.gif','phrase':'[emotions_6]'},
                {'id':7,'title':$EMOTIONSMSG.emotions_7,'url':'7.gif','phrase':'[emotions_7]'},{'id':8,'title':$EMOTIONSMSG.emotions_8,'url':'8.gif','phrase':'[emotions_8]'},
                {'id':9,'title':$EMOTIONSMSG.emotions_9,'url':'9.gif','phrase':'[emotions_9]'},{'id':10,'title':$EMOTIONSMSG.emotions_10,'url':'10.gif','phrase':'[emotions_10]'},
                {'id':11,'title':$EMOTIONSMSG.emotions_11,'url':'11.gif','phrase':'[emotions_11]'},{'id':12,'title':$EMOTIONSMSG.emotions_12,'url':'12.gif','phrase':'[emotions_12]'},
                {'id':13,'title':$EMOTIONSMSG.emotions_13,'url':'13.gif','phrase':'[emotions_13]'},{'id':14,'title':$EMOTIONSMSG.emotions_14,'url':'14.gif','phrase':'[emotions_14]'},
                {'id':15,'title':$EMOTIONSMSG.emotions_15,'url':'15.gif','phrase':'[emotions_15]'},{'id':16,'title':$EMOTIONSMSG.emotions_16,'url':'16.gif','phrase':'[emotions_16]'},
                {'id':17,'title':$EMOTIONSMSG.emotions_17,'url':'17.gif','phrase':'[emotions_17]'},{'id':18,'title':$EMOTIONSMSG.emotions_18,'url':'18.gif','phrase':'[emotions_18]'},
                {'id':19,'title':$EMOTIONSMSG.emotions_19,'url':'19.gif','phrase':'[emotions_19]'},{'id':20,'title':$EMOTIONSMSG.emotions_20,'url':'20.gif','phrase':'[emotions_20]'},
                {'id':21,'title':$EMOTIONSMSG.emotions_21,'url':'21.gif','phrase':'[emotions_21]'},{'id':22,'title':$EMOTIONSMSG.emotions_22,'url':'22.gif','phrase':'[emotions_22]'},
                {'id':23,'title':$EMOTIONSMSG.emotions_23,'url':'23.gif','phrase':'[emotions_23]'},{'id':24,'title':$EMOTIONSMSG.emotions_24,'url':'24.gif','phrase':'[emotions_24]'},
                {'id':25,'title':$EMOTIONSMSG.emotions_25,'url':'25.gif','phrase':'[emotions_25]'},{'id':26,'title':$EMOTIONSMSG.emotions_26,'url':'26.gif','phrase':'[emotions_26]'},
                {'id':27,'title':$EMOTIONSMSG.emotions_27,'url':'27.gif','phrase':'[emotions_27]'},{'id':28,'title':$EMOTIONSMSG.emotions_28,'url':'28.gif','phrase':'[emotions_28]'},
                {'id':29,'title':$EMOTIONSMSG.emotions_29,'url':'29.gif','phrase':'[emotions_29]'},{'id':30,'title':$EMOTIONSMSG.emotions_30,'url':'30.gif','phrase':'[emotions_30]'},
                {'id':31,'title':$EMOTIONSMSG.emotions_31,'url':'31.gif','phrase':'[emotions_31]'},{'id':32,'title':$EMOTIONSMSG.emotions_32,'url':'32.gif','phrase':'[emotions_32]'},
                {'id':33,'title':$EMOTIONSMSG.emotions_33,'url':'33.gif','phrase':'[emotions_33]'},{'id':34,'title':$EMOTIONSMSG.emotions_34,'url':'34.gif','phrase':'[emotions_34]'},
                {'id':35,'title':$EMOTIONSMSG.emotions_35,'url':'35.gif','phrase':'[emotions_35]'},{'id':36,'title':$EMOTIONSMSG.emotions_36,'url':'36.gif','phrase':'[emotions_36]'},
                {'id':37,'title':$EMOTIONSMSG.emotions_37,'url':'37.gif','phrase':'[emotions_37]'},{'id':38,'title':$EMOTIONSMSG.emotions_38,'url':'38.gif','phrase':'[emotions_38]'},
                {'id':39,'title':$EMOTIONSMSG.emotions_39,'url':'39.gif','phrase':'[emotions_39]'},{'id':40,'title':$EMOTIONSMSG.emotions_40,'url':'40.gif','phrase':'[emotions_40]'},
                {'id':41,'title':$EMOTIONSMSG.emotions_41,'url':'41.gif','phrase':'[emotions_41]'},{'id':42,'title':$EMOTIONSMSG.emotions_42,'url':'42.gif','phrase':'[emotions_42]'},
                {'id':43,'title':$EMOTIONSMSG.emotions_43,'url':'43.gif','phrase':'[emotions_43]'},{'id':44,'title':$EMOTIONSMSG.emotions_44,'url':'44.gif','phrase':'[emotions_44]'},
                {'id':45,'title':$EMOTIONSMSG.emotions_45,'url':'45.gif','phrase':'[emotions_45]'},{'id':46,'title':$EMOTIONSMSG.emotions_46,'url':'46.gif','phrase':'[emotions_46]'},
                {'id':47,'title':$EMOTIONSMSG.emotions_47,'url':'47.gif','phrase':'[emotions_47]'},{'id':48,'title':$EMOTIONSMSG.emotions_48,'url':'48.gif','phrase':'[emotions_48]'},
                {'id':49,'title':$EMOTIONSMSG.emotions_49,'url':'49.gif','phrase':'[emotions_49]'},{'id':50,'title':$EMOTIONSMSG.emotions_50,'url':'50.gif','phrase':'[emotions_50]'},
                {'id':51,'title':$EMOTIONSMSG.emotions_51,'url':'51.gif','phrase':'[emotions_51]'},{'id':52,'title':$EMOTIONSMSG.emotions_52,'url':'52.gif','phrase':'[emotions_52]'},
                {'id':53,'title':$EMOTIONSMSG.emotions_53,'url':'53.gif','phrase':'[emotions_53]'},{'id':54,'title':$EMOTIONSMSG.emotions_54,'url':'54.gif','phrase':'[emotions_54]'},
                {'id':55,'title':$EMOTIONSMSG.emotions_55,'url':'55.gif','phrase':'[emotions_55]'},{'id':56,'title':$EMOTIONSMSG.emotions_56,'url':'56.gif','phrase':'[emotions_56]'},
                {'id':57,'title':$EMOTIONSMSG.emotions_57,'url':'57.gif','phrase':'[emotions_57]'},{'id':58,'title':$EMOTIONSMSG.emotions_58,'url':'58.gif','phrase':'[emotions_58]'},
                {'id':59,'title':$EMOTIONSMSG.emotions_59,'url':'59.gif','phrase':'[emotions_59]'},{'id':60,'title':$EMOTIONSMSG.emotions_60,'url':'60.gif','phrase':'[emotions_60]'}
            ];
    //textarea设置光标位置
    function setCursorPosition(ctrl, pos) {
        if(ctrl.setSelectionRange) {
            ctrl.focus();
            ctrl.setSelectionRange(pos, pos);
        } else if(ctrl.createTextRange) {// IE Support
            var range = ctrl.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }

    //获取多行文本框光标位置
    function getPositionForTextArea(obj)
    {
        var Sel = document.selection.createRange();
        var Sel2 = Sel.duplicate();
        Sel2.moveToElementText(obj);
        var CaretPos = -1;
        while(Sel2.inRange(Sel)) {
            Sel2.moveStart('character');
            CaretPos++;
        }
       return CaretPos ;

    }

    $.fn.extend({
        jqfaceedit : function(options) {
            var defaults = {
                txtAreaObj : '', //TextArea对象
                containerObj : '', //表情框父对象
                textareaid: 'msg',//textarea元素的id
                popName : '', //iframe弹出框名称,containerObj为父窗体时使用
                emotions : em, //表情信息json格式，id表情排序号 title标题 url表情文件名 phrase表情使用的替代短语
                top : 0, //相对偏移
                left : 0 //相对偏移
            };
            
            var options = $.extend(defaults, options);
            var cpos=0;//光标位置，支持从光标处插入数据
            var textareaid = options.textareaid;
            
            return this.each(function() {
                var Obj = $(this);
                var container = options.containerObj;
                if ( document.selection ) {//ie
                    //options.txtAreaObj.bind("click keyup",function(e){//点击或键盘动作时设置光标值
                        //e.stopPropagation();
                        //cpos = getPositionForTextArea(document.getElementById(textareaid)?document.getElementById(textareaid):window.frames[options.popName].document.getElementById(textareaid));
                   //});
                }
                $(Obj).bind("click", function(e) {
                    e.stopPropagation();
                    var faceHtml = '<div id="face">';
                    faceHtml += '<div id="texttb"><a class="f_close" title="'+$EMOTIONSMSG.other_close+'" href="javascript:void(0);"></a></div>';
                    faceHtml += '<div id="facebox">';
                    faceHtml += '<div id="face_detail" class="facebox clearfix"><ul>';

                    for( i = 0; i < options.emotions.length; i++) {
                        faceHtml += '<li text=' + options.emotions[i].phrase + ' type=' + i + '><img title=' + options.emotions[i].title + ' src="'+_ROOT_+'/Public/js/emotions/emotions/'+ options.emotions[i].url + '"  style="cursor:pointer; position:relative;"   /></li>';
                    }
                    faceHtml += '</ul></div>';
                    faceHtml += '</div><div class="arrow arrow_t"></div></div>';

                    container.find('#face').remove();
                    container.append(faceHtml);
                    
                    container.find("#face_detail ul >li").bind("click", function(e) {
                        var txt = $(this).attr("text");
                        var faceText = txt;

                        if(getObjectClass(options.txtAreaObj)=='init'){
                            var tclen = options.txtAreaObj.val().length;

                            var tc = document.getElementById(textareaid);
                            if ( options.popName ) {
                                tc = window.frames[options.popName].document.getElementById(textareaid);
                            }
                            var pos = 0;
                            if( typeof document.selection != "undefined") {//IE
                                options.txtAreaObj.focus();
                                setCursorPosition(tc, cpos);//设置焦点
                                document.selection.createRange().text = faceText;
                                //计算光标位置
                                pos = getPositionForTextArea(tc); 
                                options.txtAreaObj.val(options.txtAreaObj.val() + faceText);
                            } else {//火狐
                                //计算光标位置
                                pos = tc.selectionStart + faceText.length;
                                options.txtAreaObj.val(options.txtAreaObj.val().substr(0, tc.selectionStart) + faceText +     options.txtAreaObj.val().substring(tc.selectionStart, tclen));
                            }
                            cpos = pos;
                            setCursorPosition(tc, pos);//设置焦点
                        } else {
                            options.txtAreaObj.insertHtml(faceText);
                        }

                        container.find("#face").remove();
                    });
                    //关闭表情框
                    container.find(".f_close").bind("click", function() {
                        container.find("#face").remove();
                    });
                    //处理js事件冒泡问题
                    $('body').bind("click", function(e) {
                        e.stopPropagation();
                        container.find('#face').remove();
                        $(this).unbind('click');
                    });
                    if(options.popName != '') {
                        $(window.frames[options.popName].document).find('body').bind("click", function(e) {
                            e.stopPropagation();
                            container.find('#face').remove();
                        });
                    }
                    container.find('#face').bind("click", function(e) {
                        e.stopPropagation();
                    });
                    var offset = $(e.target).offset();
                    offset.top += options.top;
                    offset.left += options.left;
                    container.find("#face").css(offset).show();
                });
            });
        },
        //表情文字符号转换为html格式
        emotionsToHtml : function(options) {
            return this.each(function() {
                var msgObj = $(this);
                var rContent = msgObj.html();

                var regx = /(\[[\u4e00-\u9fa5]*\w*\]){1}/g;
                //正则查找“[]”格式
                var rs = rContent.match(regx);
                if(rs) {
                    for( i = 0; i < rs.length; i++) {
                        for( n = 0; n < em.length; n++) {
                            if(em[n].phrase == rs[i]) {
                                var t = "<img src='"+_ROOT_+"/Public/js/emotions/emotions/"  + em[n].url + "' />";
                                rContent = rContent.replace(rs[i], t);
                                break;
                            }
                        }
                    }
                }
                msgObj.html(rContent);
            });
        }
    })
})(jQuery);
