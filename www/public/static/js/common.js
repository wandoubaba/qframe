/* 项目自定义js */

// iframe高度自适应
function iframe_height_fix(iframe_obj, plus) {
	iframe_obj.on("load",function() {
		iframe_obj.height(0);
		var mainheight = iframe_obj.contents().find("body").height();
		mainheight = mainheight==0 ? 400 : mainheight;
		iframe_obj.height(mainheight+plus);
	});
}

// 在iframe内关闭modal弹层
function iframe_modal_close() {
	window.parent.$('.modal').modal('hide');
}

/**
 * 创建并显示bootstrap模态框，id为automodal
 * @param  {[type]} title       		设置模态框显示的标题
 * @param  {[type]} url         		设置模态框要加载的url
 * @param  {[type]} iframe_height_plus 	让iframe高度在识别基础上增加多少
 * @return {[type]}			            [description]
 */
function modal_show_iframe(title, url, iframe_height_plus) {
	$("#automodal").remove();
	// 最外层节点.modal
	var modal_obj = $("<div></div>");
	modal_obj.attr({
		"id":"automodal",
		"role":"dialog",
		"aria-labelledby":"modalLabel"
	}).addClass("modal fade");
	// .modal-dialog节点
	var modal_dialog = $("<div></div>");
	modal_dialog.attr({"aria-hidden":"true"}).addClass("modal-dialog");
	// .modal-content节点
	var modal_content = $("<div></div>");
	modal_content.addClass("modal-content");
	// .modal-header节点
	var modal_header = $("<div></div>");
	modal_header.addClass("modal-header");
	// 右上角关闭按钮节点，appendTo(modal_header)
	var close_button = $("<button></button>");
	close_button.attr({"type":"button","data-dismiss":"modal","aria-hidden":"true"}).addClass("close").append("<i class=\"fa fa-close\"></i>").appendTo(modal_header);
	// .modal-title节点，appendTo(modal_header)
	var modal_title = $("<h4></h4>");
	modal_title.attr({"id":"modalLabel"}).addClass("modal-title").appendTo(modal_header);
	// .modal_body节点
	var modal_body = $("<div></div>");
	modal_body.addClass("modal-body");
	// iframe节点，appendTo(modal_body)
	var iframe = $("<iframe></iframe>");
	iframe.attr({"width":"100%","frameborder":"0"}).appendTo(modal_body);
	// 把modal_header和modal_body节点append到modal_content中，再把modal_content节点appendTo(modal_dialog)
	modal_content.append(modal_header).append(modal_body).appendTo(modal_dialog);
	// 把modal_dialog节点appendTo(modal_obj)
	modal_dialog.appendTo(modal_obj);
	// 把modal_obj节点append到$("body")中
	$("body").append(modal_obj);
	// 设置模态框标题
	$(modal_obj).find(".modal-title").text(title);
	// 设置iframe要加载的url
	$(modal_obj).find("iframe").attr("src",url);
	// 根据加载内容调整iframe高度，并增加iframe_height_plus以做调整
	iframe_height_fix($(modal_obj).find("iframe"), iframe_height_plus);
	// 显示模态框
	$(modal_obj).modal("show");
}