/**
 * 通过ajax调用后台url读取数据库数据生成无限级联下拉菜单
 * 程序会自动生成4个hidden控件name分别为：
 * container.attr("id")+"_value"：记录最后一个select的value
 * container.attr("id")+"_value_array"：记录所有select的value，用英文逗号分隔
 * container.attr("id")+"_text"：记录最后一个select选择的text
 * container.attr("id")+"_text_array"：记录所有select选择的text，用英文逗号分隔
 * 所以，
 * 要求container必须要有id属性
 * 要求所有选项的text中不能存在英文逗号
 * 使用方法：
 *  HTML：
 *  <div id="container"></div>
 *  JS：
 * 	$("#container").linkage({
 *   	url: "{:url('index/service/district')}",	// 请求路径
 *   	default_values: [],	// 初始ID值
 *   	root_father: "0",	// 最高级的父ID
 *   	father_keyname: "father_id",	// 查询时父ID参数对应的键名，默认为father_id
 *   	level: -1,	// 加载级别，小于等0表示加载到底
 *   	select_css: "form-control",	// 样式名称，默认为bootstrap的form-control
 *   	container_css: "form-inline",	// 容器样式名称，默认为bootstrap的form-inline
 *   	value_key: "district_id",	// <option value="id">name</option>
 *   	text_key: "district_name",
 *   	span_css: "",	// 如果要在select外层包一层span则定义span的css样式名称，如h-ui的span_css: "select-box"
 * });
 *
 */
;(function($) {
	$.fn.linkage = function(options) {
		// 默认参数设置
		var settings = {
			url: "/index/service/district",	// 请求路径
			default_values: [],	// 初始ID值
			root_father: "0",	// 最高级的父ID
			father_keyname: "father_id",	// 查询时父ID参数对应的键名，默认为father_id
			level: -1,	// 加载级别，小于等0表示加载到底
			select_css: "form-control",	// 样式名称，默认为bootstrap的form-control
			container_css: "",	// 容器样式名称，比如bootstrap的form-inline
			value_key: "district_id",	// <option value="id">name</option>
			text_key: "district_name",
			span_css: "",	// 如果要在select外层包一层span则定义span的css样式名称，如h-ui的span_css: "select-box"
			select_style: "",	// 要对select使用的style
			required: 1,	// 配合jquery.validation的required属性
		}
		// 合并参数
		if(options) {
			$.extend(settings, options);
		}

		// 链式原则
		return this.each(function() {
			init($(this), settings.root_father, settings.default_values);

			/**
			 * 初始化
			 * @param  {[type]} container 容器对象
			 * @param  {[type]} default_values      初始值
			 * @return {[type]}           [description]
			 */
			function init(container, root_father, default_values) {
				$(container).addClass(settings.container_css);
				// 创建隐藏域，赋初始值
				var _input = $("<input type='hidden' name='"+container.attr("id")+"_value"+"'/>").appendTo(container).val(settings.default_values);
				var _input = $("<input type='hidden' name='"+container.attr("id")+"_value_array"+"'/>").appendTo(container).val(settings.default_values);
				var _input = $("<input type='hidden' name='"+container.attr("id")+"_text"+"'/>").appendTo(container).val(settings.default_values);
				var _input = $("<input type='hidden' name='"+container.attr("id")+"_text_array"+"'/>").appendTo(container).val(settings.default_values);
				createSelect(container, root_father, default_values);
			}

			/**
			 * 创建下拉框
			 * @param  {[type]} container 容器对象
			 * @param  {[type]} father_id  父ID
			 * @param  {[type]} id        自身ID
			 * @return {[type]}           [description]
			 */
			function createSelect(container, father_id, default_values) {
				// 加载条件1或：当前已加载级别未达到设定最大级别
				// 加载条件2或：设定最大级别不大于0
				// 加载条件3和：已选父级ID大于等于0
				// 加载条件4和：有子级
				if(
					($("select", container).length<settings.level || settings.level<=0)
					&& father_id!="-1") {
					// 创建select对象，并将select对象放入container内
					var _select = $("<select></select>");
					// 为_select应用style属性
					_select.attr("style", settings.select_style);
					// 为_select添加required属性
					if(settings.required) {_select.attr("required",true);}
					var _fid = father_id;
					var param = {};
					_key = settings.father_keyname;
					param[_key] = _fid;
					var default_id = -1;
					// 发送ajax请求，返回的data必须为json格式
					// 使用$.getJSON支持跨域请求
					var xhr = $.getJSON(settings.url, param, function(data,status,xhr) {
						if(data.length>0) {
							// 在结果集中过滤一遍default_values[0]，
							// 如果结果集中包含default_values[0]，则default_id定义为default_values[0]
							// 如果不包含，则default_id默认等于-1
							for(var i=0; i<data.length; i++) {
								if(data[i][settings.value_key]==default_values[0]) {
									default_id = default_values[0];
								}
							}
							// 添加所有option节点，并设定默认值为id，未设定id时值为-1
							addOptions(container, _select, data).val(default_id);
							// 根据span_css判断是否需要在select外层包一层span
							if(!settings.span_css) {
								// 将select挂到container中并设定css样式名
								_select.appendTo(container).addClass(settings.select_css);
							} else {
								var _span = $("<span></span>");
								_select.appendTo(_span).addClass(settings.select_css);
								_span.appendTo(container).addClass(settings.span_css);
							}
							// 当传入的default_values长度大于0时
							if(default_values.length>0) {
								// 将传入的default_values中的index为0的元素从数组中删除
								default_values.splice(0,1);
								// 把新的default_values传入下一次递归createSelect中
								createSelect(container, default_id, default_values);
							}
						}
						// 将container中的select们的当前状态记录到hidden域中
						saveVal(container);
					});
					xhr.fail(function(jqXHR, textStatus, ex) {
					    $(container).append('request failed, code: ' + jqXHR.status);
					});
				} else {
					saveVal(container);
				}
			}

			/**
			 * 为下拉框添加<option>
			 * @param {[type]} container 容器对象
			 * @param {[type]} select    下拉框
			 * @param {[type]} data      JSON格式数据
			 */
			function addOptions(container, select, data) {
				select.append($('<option value="-1">请选择</option>'));
				for(var i=0; i<data.length; i++) {
					select.append($('<option value="'+data[i][settings.value_key]+'">'+data[i][settings.text_key]+'</option>'));
				}
				// 为select绑定change事件
				select.bind("change", function() {
					_onchange(container, $(this), $(this).val());
				});
				return select;
			}

			/**
			 * select的change事件
			 * @param  {[type]} container 容器对象
			 * @param  {[type]} select    下拉框对象
			 * @param  {[type]} id        当前下拉框的值
			 * @return {[type]}           [description]
			 */
			function _onchange(container, select, id) {
				if(settings.span_css) {
					// select外如果有span的话，则操作后面的span
					var nextAll = select.parent().nextAll("span");
				} else {
					// select外没有span的话，则操作后面的select
					var nextAll = select.nextAll("select");
				}
				// 如果当前select对象的值是空或-1，则将其后面的select对象全部移除
				if(!id || id=="-1" || nextAll.length>0) {
					nextAll.remove();
				}
				// saveVal(container);
				createSelect(container, id, -1);
			}

			/**
			 * 将选择的值保存在隐藏域中
			 * @param  {[type]} container 容器对象
			 * @return {[type]}           [description]
			 */
			function saveVal(container) {
				// 定义4个hidden，name分别是：
				// container.attr("id")+"_value"：记录最后一个select的value
				// container.attr("id")+"_value_array"：记录所有select的value
				// container.attr("id")+"_text"：记录最后一个select的text
				// container.attr("id")+"_text_array"：记录所有select的text
				var value = "";
				var text = "";
				var value_array = new Array();
				var text_array = new Array();
				$("select", container).each(function() {
					value = $(this).val();
					if($(this).val()!="-1") {
						value_array.push($(this).val());
						text = $(this).find("option:selected").text();
						text_array.push($(this).find("option:selected").text());
					}
				});
				// 为隐藏域对象赋值
				$("input[name='"+container.attr("id")+"_value"+"']",container).val(value);
				$("input[name='"+container.attr("id")+"_value_array"+"']",container).val(value_array.join(','));
				$("input[name='"+container.attr("id")+"_text"+"']",container).val(text);
				$("input[name='"+container.attr("id")+"_text_array"+"']",container).val(text_array.join(','));
				// console.log($("input[name='"+container.attr("id")+"_value"+"']",container).val());
				// console.log($("input[name='"+container.attr("id")+"_value_array"+"']",container).val());
				// console.log($("input[name='"+container.attr("id")+"_text"+"']",container).val());
				// console.log($("input[name='"+container.attr("id")+"_text_array"+"']",container).val());
			}
		});
	}
})(jQuery);