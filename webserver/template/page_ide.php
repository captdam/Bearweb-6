<div>
	<div id="admin_works"></div>
	<div id="admin_window" class="bearform">
		<a id="a_admin_new" onclick="windowChange(this.id.substr(2))">New</a>
		<a id="a_admin_config" onclick="windowChange(this.id.substr(2))">Config</a>
		<a id="a_admin_content" onclick="windowChange(this.id.substr(2))">Content</a>
		<a id="a_admin_addon" onclick="windowChange(this.id.substr(2))">Add-on</a>
		<a id="a_admin_preview" onclick="windowChange(this.id.substr(2))">Preview</a>
		<a id="a_admin_action" onclick="windowChange(this.id.substr(2))">Action</a>
		<div id="admin_new">
			<h2>New</h2>
			<div class="info">
				在这里创建一个作品，或者在左侧选择一个已有作品进行编辑。<br />
			</div>
			<label>Category</label>
			<select id="createpage_url_prefix">
				<option value="Blog">Blog</option><option value="Note">Note</option><option value="Resource">Resource</option>			</select>
			<div class="info">
				Category（Base URL）。<br />
				选择这个作品所应该被归类的Category。
			</div>
			<label>URL</label>
			<input id="createpage_url" type="text" />
			<div class="info">
				作品URL，Category/URL将会作为这个作品的URL地址，子项目（Add-on）的地址将会是Category/URL/xxx。<br />
				只能使用英文字母/数字/下划线/冒号/减号，Category/URL/xxx总长度在128字符以内，尽量不要使用大写字母。
			</div>
			<label>Action</label>
			<button onclick="createNew()">创建主题</button>
			<div class="info">
				点击按钮创建作品。<br />
				创建成功后请在左侧导航栏选择作品进入编辑器。
			</div>
		</div>
		<div id="admin_config">
			<h2>Config</h2>
			<div class="info">
				在这里对整个作品进行设置，这些设置将被应用在作品正文页面与其所使用的资源上。
			</div>
			<label>Base URL</label>
			<input id="modifypage_url" type="text" value="SELECT OR CREATE ONE FIRST" readonly="true" autocomplete="off"/>
			<div class="info">
				资源Base地址，作为这个作品的URL地址，子项目（Add-on）的地址将会是Category/URL/xxx。<br />
				只能使用英文字母/数字/下划线/冒号/减号，Category/URL/xxx总长度在128字符以内，尽量不要使用大写字母。
			</div>
			<label>Title</label>
			<input id="modifypage_title" type="text" value="" />
			<div class="info">
				资源名称，一个简短而符合资源内容的名字标题。
			</div>
			<label>Keywords</label>
			<input id="modifypage_keywords" type="text"  value=""/>
			<div class="info">
				填写关键词，关键词将用于建立索引以便于站内搜索，外部搜索引擎（例如Google，度娘）也将会使用关键字抓取页面。<del>其实以前有某些网站使用关键字恶意刷积分，所以现在keywords已经不再作为搜索引擎排名的标准了，但是keywords将仍然被显示在搜索结果页。</del><br />
				使用英文逗号（,）分隔关键词。请不要使用意思相近的词语叠加。
			</div>
			<label>Description</label>
			<textarea id="modifypage_description" type="text"></textarea>
			<div class="info">
				关于这一作品的简短介绍。<br />
				这里的简介将会被搜索引擎显示在搜索结果的简介里；同时，在分享文章时（例如分享到空间），这里的内容也会被直接使用作为分享内容的简介。推荐30-100字，太长或太短的简介将会被认定为无意义（虽然Beardle会保存这个简介，但是外部服务器，比如搜索引擎，将会在网页中随机截取一段文字）。
			</div>
			<label>Status</label>
			<p class="info">页面状态，根据资源情况选择即可。</p>
			<select id="modifypage_status">
				<option value="O" style="background-color: lime;" selected>OK 正常</option>
				<option value="P" style="background-color: red;">Pending 挂起，自己可见</option>
				<option value="C" style="background-color: orange;">Construction 施工中，内容不完整或会改变</option>
				<option value="D" style="background-color: yellow;" selected>Deprecated 不推荐，随时可能删除</option>
				<option value="S" style="background-color: yellow;">Special 特殊页面，必要但是没有实际意义，或者说是不希望被搜索引擎抓取</option>
			</select>
			<div class="info">
				资源状态选择，将会应用到所有附加资源（Add-on）。<br />
				注意：如果使用了P，除了你和管理员，任何人都将无法访问这个作品与这个作品的附加资源（Add-on）内容。这个作品将不会显示在网页的首页与板块首页上，但是任然会出现在作者资料页上。
			</div>
			<label>版权信息*</label>
			<select id="modifypage_copyright" onchange="altInput(this)">
				<option value="" selected>请选择...</option>
				<option value="All rights reserved">All rights reserved 无授权，适用于原创但是不希望被他人使用或是再次转载的内容</option>
				<option value="CC BY">CC BY 知识共享协议，署名原作者</option>
				<option value="CC BY-ND">CC BY-ND 知识共享协议，署名原作者，不允许发布改编过的版本</option>
				<option value="CC BY-SA">CC BY-SA 知识共享协议，需要署名原作者，可以发布改编过的版本但是必须使用同样共享协议</option>
				<option value="CC BY-NC">CC BY-NC 知识共享协议，需要署名原作者，不允许商业使用</option>
				<option value="CC BY-NC-ND">CC BY-NC-ND 知识共享协议，需要署名原作者，不允许发布改编过的版本，不允许商业使用</option>
				<option value="CC BY-NC-SA">CC BY-NC-SA 知识共享协议，需要署名原作者，可以发布改编过的版本但是必须使用同样共享协议，不允许商业使用</option>
				<option value="MIT">MIT MIT协议，允许任何方式的修改与再发布（代码专用）</option>
				<option value="GNU3.0">GNU3.0 GNU3.0协议（代码专用）</option>
				<option value="Apache2.0">Apache2.0 = Apache2.0协议（代码专用）</option>
				<option value="*">* = 其他协议，在下面注明</option>
			</select>
			<div class="info">
				版权信息，请一定选择正确的版权信息，不然将可能有很麻烦的知识产权问题（在国内这个问题不明显，在国外如果侵犯到版权法将会又非常严厉的惩罚）。<br />
				拒绝转载作品，允许引用，但是请一定在文章结尾注明使用了xx为Reference。
			</div>
		</div>
		<div id="admin_content">
			<h2>Content</h2>
			<div class="info">
				在这里编辑作品的文章。<br />
				点击Add-on区域的内容可以将其加入正文。要添加附加资源（Add-on），点击上方的Add-on标签。要预览文章效果，点击上方的Preview标签。
			</div>
			<label>Add-on</label>
			<ul id="modifypage_addon_selector"></ul>
			<label>Data</label>
			<textarea id="modifypage_data" type="text"></textarea>
			<select id="modifypage_data_format">
				<option value="N/A" selected>N/A = 不使用任何转码</option>
				<option value="BeardleV1">Beardle V1</option>
				<option value="BeardleV2">Beardle V2 （推荐使用）</option>
			</select>
			<div class="info">
				<b>Beardle V2</b> 格式帮助：<br />
				<b>板块类</b><br />
				使用<b>#</b>开头所在的行将会被作为大标题，同时也将被用于大段落之间的分割。外观上，将会使用普蓝色背景色条与白色文字。比如，#This is a title。<br />
				使用<b>##</b>开头所在的行将会被作为小标题，同时也将被用于小段落之间的分割。比如，#This is a subtitle。<br />
				使用<b>-----</b>（大于或等于5个减号）开头的行将会被识别为小段分隔线，其后内容会被忽略。小段之间将会用不同的背景色作为区分（白色/浅灰白）。<br />
				使用<b>|</b>（竖线）开头的行将会被识别为小结。外观上，小结的左侧将会加上蓝色色条。<br />
				所有行都会被使用段落样式（p），段落之间会有间距。如果要不使用段落样式，或是想要在小结里面插入多行内容，可以用<b>[n]</b>进行无样式提行。<br />
				
				<b>内容类</b><br />
				使用<b>[image]image_URL[/image]</b>可以插入图片，用实际URL代替image_url，该行不要包括任何其他东西。比如，[image]/web/banner.jpg[/image]。<br />
				使用<b>[b]content[/b]</b>可以加粗显示，用实际内容代替content。比如，[b]Something important[/b]。<br />
				使用<b>[i]content[/i]</b>可以斜体显示，用实际内容代替content。比如，[i]Something not important[/i]。<br />
				使用<b>[del]content[/del]</b>可以打上删除线，用实际内容代替content。比如，[del]Some joke[/del]。<br />
				使用<b>[link]link_URL[/link]</b>可以超链接，用实际URL代替link_url。比如，[link]http://example.com/somepage[/link]（外部URL）或[link]/project/something[/link]（内部URL）。<br />
				如果一段文字被作为了标题，那么他将不会被用于内容转码。如果不清楚，可以使用Preview进行测试。<br />
				如果以上没有合适的，或者想要进行什么<del>骚操作</del>也可以使用原生HTTP，但是那样会有板块崩裂的危险。<br />
			</div>
			<br /><hr /><br />
			<div class="info">
				<b>Beardle V1</b> 格式帮助：<br />
				三空行分一大节，两空分一分段，一空行分一小结。大节第一行作为大节标题，该标题会使用蓝色底，白色大号字体，大节与大节之间靠蓝色标题条区分。分段第一行会作为分段标题，分段之间靠背景颜色（灰与白）区分。小节没有标题行，小节内每一行都会作为一个段落，不同小节段落使用左侧色标组区分。<br />
				使用[b]content[/b]可以加粗显示，用实际内容代替content。比如，[b]Something important[/b]。<br />
				使用[i]content[/i]可以斜体显示，用实际内容代替content。比如，[i]Something not important[/i]。<br />
				使用[del]content[/del]可以打上删除线，用实际内容代替content。比如，[del]Something not important[/del]。<br />
				使用[link]link_URL[/link]可以超链接，用实际URL代替link_url。比如，[link]http://example.com[/link]。<br />
				使用[image]image_URL[/image]可以插入图片，用实际URL代替image_url。比如，[image]/web/banner.jpg[/image]。<br />
				以上项目的URL可以是绝对路径也可以是相对路径，可以是站内路径也可以是站外路径，站外路径记得http://头。如果以上没有合适的，也可以使用原生HTTP。<br />
			</div>
		</div>
		<div id="admin_addon">
			<h2>Add-on</h2>
			<div class="info">
				点击上传框或者把文件拖进去就可以上传附加资源。<br />
				Title只能使用英文字母/数字/下划线/冒号/减号/小数点，Base_URL+title总长度在128字符以内，尽量不要使用大写字母。非原创图片请一定在版权信息栏写出原出处，例如：example.com。<br />
				目前只支持图片类型的智能处理（推荐png与jpeg/jpg，其他格式图片不保证可以处理）。对于符合标准（图片长宽大于1500*1000）且不是引用（版权信息留空）的图片，将会加上水印。<br />
				添加文件后，点击上传按钮，文件文件将被上传。文件上传分为2个步骤：创建-编辑，你可以在File query查看上传进度，因为网络问题，文件上传可能会消耗一定时间，<b>在文件上传的时候请一定不要做任何其他操作。</b>，以免发生意外。文件上传完成后，回到“Content”标签即可查看已上传的文件。<br />
				如果选择了错误的文件，在上传前可以点击“Remove”将其从上传队列中移除。如果上传了错误的文件（已经上传到了服务器），回到“Content”标签选择该资源删除即可。<br />
				注意，这个页面只是上传工具，服务器上已有文件以“Content”标签为准。当你离开这个标签时，File query将会被重置，你将失去所有未上传的进度。另外，点击“上传”时只会上传状态为“Ready”的文件，也就是说，你可以在上传了部分文件后，添加更多的文件，此时点击“上传”就会上传新添加的文件，已上传的文件将不会受到影响。
			</div>
			<label>File loader</label>
			<div id="upload_files_mask" onclick="document.getElementById('upload_files_input').click();">
				<div>DRAG AND DROP HERE or CLICK to add files</div>
			</div>
			<input id="upload_files_input" type="file" accept="image/*" multiple />
			<label>File query</label>
			<div id="upload_files_query"></div>
			<label>Action</label>
			<button onclick="xUpload()">上传</button>
		</div>
		<div id="upload_files_preview_template" style="display: none;">
			<b>Title 资源名称，一个简单而符合资源内容的名字</b>
			<input class="x_view_title" type="text" value="" />
			<b>版权信息 如果是引用，请在这里写出出处，否则留空</b>
			<input class="x_view_copyright" type="text" />
			<a onclick="this.parentNode.parentNode.remove();">Remove from query</a>
			<span class="x_view_uploadStatus">Ready</span>
		</div>
		<div id="admin_preview">
			<h2>Preview</h2>
			<div class="info">
				下面就是这篇文章发表后的大致外观。
			</div>
			<div id="admin_preview_content"></div>
		</div>
		<div id="admin_action">
			<h2>Action</h2>
			<div class="info">
				点击按钮就可以将文章发布，发布前请再三确认Config设置正确。目前暂时不提供删除功能，要删除文章，请联系管理员。
			</div>
			<button onclick="uploadWork()">上传修改</button>
		</div>
	</div>
</div>

<script>
	//Get info about all my work (index)
	function getMyWorks() {
		var window = document.getElementById('admin_works');
		removeAllChild(window);
		
		ajaxAPI('GET','/user/works',{}).then(
			([status,response]) => {
				var topics = response.Work;
				var list = [];
				
				topics.map( x => {list.push({
						"tag" : "a",
						"textContent" : x.Title,
						"onclick" : () => getOneWork(x.URL)
				}) } );
				
				list.push({
					"tag" : "a",
					"textContent" : " ==> Refresh <== ",
					"onclick" : getMyWorks
				});
				
				list.map( x => window.appendChild(domStructure(x)) );
			},
			([status,response]) => {
				window.appendChild(domStructure({
					"tag" : "a",
					"textContent" : " ==> Retry <== ",
					"onclick" : getMyWorks
				}));
			}
		);
	}
	getMyWorks();
	
	function getOneWork(url) {
		console.log(url);
	}







	//Get info about my work
	function getOneOfMyWorks(workURL) {
		ajax('GET','/api/page/get?page='+workURL,null,function(code,body){
			//Fail to get page info
			if (code != 200)
				return alert('Error: Cannot get page: ' + bodyError);
			//Write page info to workspace (window)
			data = body.Work;
			document.getElementById('modifypage_url').value = data.URL;
			document.getElementById('modifypage_title').value = data.Title;
			document.getElementById('modifypage_keywords').value = data.Keywords;
			document.getElementById('modifypage_description').value = data.Description;
			document.getElementById('modifypage_status').value = data.Status;
			//Process JSON and Config
			window.json = Object.assign({},data.JSON);
			window.etag = data.LastModify;
			document.getElementById('modifypage_data').value = json.orginalData;
			document.getElementById('modifypage_data_format').value = json.dataFormat;
			//Process license info
			var license = new Array();
			document.querySelectorAll('#modifypage_copyright>option').forEach(function(x){
				license.push(x.value);
			});
			if (license.includes(data.Copyright)) 
				document.getElementById('modifypage_copyright').value = data.Copyright;
			else {
				document.getElementById('modifypage_copyright').value = '*';
				altInput(document.getElementById('modifypage_copyright'));
				document.getElementById('modifypage_copyright').value = data.Copyright;
			}
			
			windowChange('admin_config');
		});
	}
	
	//Get add-on of a work
	function getSubWork(workURL) {
		ajax('GET','/api/page/get?sub&page='+workURL,null,function(code,body){
			//Fail to get page info
			if (code != 200)
				return alert('Error: Cannot get add-on resource: ' + body.Error);
			//Write page info to workspace (window)
			var addonSpace = document.getElementById('modifypage_addon_selector');
			data = body.Work;
			data.map(function(x){
				var res = document.createElement('li');
				res.appendChild(document.createTextNode(x.URL));
				//View in new browser window
				var view = document.createElement('a');
				view.textContent = '浏览';
				view.target = '_blank';
				view.href = '/' + x.URL;
				res.appendChild(view);
				//Insert into page
				var add = document.createElement('a');
				add.textContent = '插入';
				add.addEventListener('click',function(){
					var text = document.getElementById('modifypage_data');
					text.focus();
					var before = text.value.substr(0,text.selectionStart);
					var self = '[image]/' + x.URL + '[/image]\n';
					var after = text.value.substr(text.selectionEnd,text.value.length);
					text.value = before + self + after;
					text.setSelectionRange(before.length+self.length,before.length+self.length);
				});
				res.appendChild(add);
				//Delete on server
				var del = document.createElement('a');
				del.textContent = '删除';
				del.addEventListener('click',function(){
					remove(x.URL,x.LastModify,function(){
						res.remove();
					});
				});
				res.appendChild(del);
				addonSpace.appendChild(res);
			});
		});
	}
	
	//Change window section
	function windowChange(name) {
		//Reset all
		Array.prototype.slice.call(document.querySelectorAll('#admin_window>a')).map(function(x){
			x.style.backgroundColor = '#CCCCCC';
		});
		Array.prototype.slice.call(document.querySelectorAll('#admin_window>div')).map(function(x){
			x.style.display = 'none';
		});
		//Show selected one
		document.getElementById('a_'+name).style.backgroundColor = '#DDDDDD';
		document.getElementById(name).style.display = 'block';
		//Check if a work is load
		if (name != 'admin_new' && document.getElementById('modifypage_url').value.trim() == 'SELECT OR CREATE ONE FIRST') {
			windowChange('admin_new'); //Switch to create new window
			alert('SELECT OR CREATE ONE FIRST');
			return;
		}
		//Content page, refresh add-on list
		if (name == 'admin_content') {
			var fileList = document.getElementById('modifypage_addon_selector');
			while (fileList.lastChild)
				fileList.lastChild.remove();
			getSubWork(document.getElementById('modifypage_url').value);
		}
		//Add-on tool, reset query
		if (name == 'admin_addon') {
			var query = document.getElementById('upload_files_query');
			while (query.lastChild)
				query.lastChild.remove();
		}
		//Select preview, re-render the preview
		if (name == 'admin_preview') {
			if (document.getElementById('modifypage_data_format').value == 'BeardleV1')
				bearBB1(document.getElementById('modifypage_data').value);
			else if (document.getElementById('modifypage_data_format').value == 'BeardleV2')
				bearBB2(document.getElementById('modifypage_data').value);
			else
				document.getElementById('admin_preview_content').innerHTML = document.getElementById('modifypage_data').value;
		}
	}
	windowChange('admin_new'); //Switch to create new window
	
	//Create new work
	function createNew() {
		var url = document.getElementById('createpage_url_prefix').value.trim().toLowerCase() + '/' + document.getElementById('createpage_url').value.trim();
		if (!/^[A-Za-z0-9_\?\-\:\/\.]{10,128}$/.test(url)) {
			alert('URL格式错误');
			return;
		}
		ajax('GET','/api/page/create?ide&page='+url,null,function(code,body){ //Get request
			if (code == 201) {
				getMyWork();
				alert('操作成功');
			}
			else
				alert('操作失败，错误码：' + body.Error);
		});
	}
	
	//Upload resources
	setTimeout(function(){
		//Set event listener
		document.getElementById('upload_files_mask').addEventListener('dragenter',function(evt){
			evt.stopPropagation();
			evt.preventDefault();
		},false);
		document.getElementById('upload_files_mask').addEventListener('dragover',function(evt){
			evt.stopPropagation();
			evt.preventDefault();
		},false);
		document.getElementById('upload_files_mask').addEventListener('drop',function(evt){
			evt.stopPropagation();
			evt.preventDefault();
			var files = evt.dataTransfer.files;
			for (var i = 0; i < files.length; i++)
				xUploadRender(files[i]);
		},false);
		document.getElementById('upload_files_input').addEventListener('change',function(){
			for (var i = 0; i < this.files.length; i++)
				xUploadRender(this.files[i]);
		});
	},0);
	//Render file query
	function xUploadRender(file) {
		var name = file.name;
		var type = file.type;
		var reader = new FileReader();
		reader.readAsDataURL(file);
		reader.onload = function(){
			//Create HTML
			var preview = document.createElement('div');
			preview.classList.add('x_upload_preview');
			preview.classList.add('bearform');
			preview.classList.add('pltr');
			preview.dataset.data64 = reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/');
			preview.dataset.mime = type;
			//Preview (left)
			if (type.substr(0,6) == 'image/') {
				var left = document.createElement('img');
				left.src = window.URL.createObjectURL(file);
				preview.appendChild(left);
			}
			else {
				var left = document.createElement('div');
				left.innerHTML = 'No preview';
				preview.appendChild(left);
			}
			//Info (right)
			var right = document.createElement('div');
			right.innerHTML = document.getElementById('upload_files_preview_template').innerHTML;
			right.querySelector('.x_view_title').value = name;
			preview.appendChild(right);
			//Append to HTML
			document.getElementById('upload_files_query').appendChild(preview);
		};
	}
	//Upload
	function xUpload() {
		Array.prototype.slice.call(document.querySelectorAll('.x_upload_preview')).map(function(x){
			//Get info
			var upload	= x.querySelector('.x_view_uploadStatus');
			var title	= x.querySelector('.x_view_title').value.trim();
			var copyright	= x.querySelector('.x_view_copyright').value.trim();
			var url		= document.getElementById('modifypage_url').value.trim() + '/' + title;
			//Check status, skip files in progress
			if (upload.textContent != 'Ready' && upload.textContent.substr(0,5) != 'Error')
				return;
			//Process title
			if (title == '') {
				upload.textContent = 'Error: 填写title.';
				upload.style.color = 'red';
				return;
			}
			if (!/^[A-Za-z0-9_\?\-\:\/\.]{1,128}$/.test(title)) {
				upload.textContent = 'Error: Title格式错误.';
				upload.style.color = 'red';
				return;
			}
			//Process copyright info
			if (copyright != '')
				copyright = 'Reference=' + copyright;
			else
				copyright = 'All rights reserved';
			//Create URL
			upload.textContent = 'Creating...';
			upload.style.color = 'gray';
			ajax('GET','/api/page/create?page='+url,null,function(code,body){
				//Fail to create URL
				if (code != 201) {
					upload.textContent = 'Error: Cannot create URL: ' + body.Error;
					upload.style.color = 'red';
					return;
				}
				//Prepare post data
				var post = new Array();
				post['Author']		= 'Captdam';
				post['Title']		= title;
				post['MIME']		= x.dataset.mime;
				post['Keywords']	= '';
				post['Description']	= '';
				post['Category']	= 'Content';
				post['TemplateMain']	= 'object';
				post['TemplateSub']	= 'externalimage';
				post['Data']		= '';
				post['Binary']		= x.dataset.data64;
				post['JSON']		= '{}';
				post['Status']		= 'P';
				post['Copyright']	= copyright;
				post['Etag']		= body.Data.LastModify;
				//Modify page
				upload.textContent = 'Editing...';
				upload.style.color = 'gray';
				ajax('POST','/api/page/modify?page='+url,post,function(code,body) {
					if (code == 201) {
						upload.textContent = 'Processed! Success.';
						upload.style.color = 'green';
					}
					else {
						upload.textContent = 'Page created but process are interupted, please report. You can delete it in "content" tag and try again.';
						upload.style.color = 'red';
					}
				});
			});
		});
	}
	
	//Delete page/resource
	function remove(url,eTag,callback) {
		if(window.confirm('认真的？删除后将无法恢复')) {
			ajax('POST','/api/page/delete?page='+url,{"Etag":eTag},function(code,body){
				if (code == 410) {
					callback();
				}
				else {
					alert('Error: ' + body.Error);
				}
			});
		}
	}
	
	//Upload work
	function uploadWork() {
		var url = document.getElementById('modifypage_url').value;
		//Process data
		json.dataFormat = document.getElementById('modifypage_data_format').value;
		json.orginalData = document.getElementById('modifypage_data').value;
		var data = json.orginalData
		if (json.dataFormat == 'BeardleV1')
			data = bearBB1(json.orginalData);
		else if (json.dataFormat == 'BeardleV2')
			data = bearBB2(json.orginalData);
		else
			json.dataFormat = 'N/A';
		//Prepare post data
		var post = new Array();
		post['Author']		= 'Captdam';
		post['Title']		= document.getElementById('modifypage_title').value.trim();
		post['MIME']		= 'text/html';
		post['Keywords']	= document.getElementById('modifypage_keywords').value.trim();
		post['Description']	= document.getElementById('modifypage_description').value.trim();
		post['Category']	= url.split('/')[0][0].toUpperCase() + url.split('/')[0].slice(1);
		post['TemplateMain']	= 'page';
		post['TemplateSub']	= 'content';
		post['Data']		= data;
		post['Binary']		= '';
		post['JSON']		= JSON.stringify(json);
		post['Status']		= document.getElementById('modifypage_status').value;
		post['Copyright']	= document.getElementById('modifypage_copyright').value;
		post['Etag']		= etag;
		//Modify page
		if(!window.confirm('认真的？'))
			return;
		ajax('POST','/api/page/modify?ide&page='+url,post,function(code,body) {
			if (code == 201) {
				alert('\^o^/ 页面修改成功');
				getMyWork(url);
			}
			else {
				alert('Error: ' + body.Error);
			}
		});
	}
	
	//BearBB
	function bearBB1(input) {
		var blogContent = input.trim();
		var blogFinal = '';
		var superDivs = blogContent.split(/\n\n\n\n/);
		superDivs.map(function(a){
			var current = a.split(/\n/);
			var currentHeader = current.shift();
			blogFinal += '<div class="main_content_title"><h2>' + currentHeader + '</h2></div>';
			var divs = current.join('\n').split(/\n\n\n/);
			divs.map(function(b){
				blogFinal += '<div>';
				var current = b.split(/\n/);
				var currentHeader = current.shift();
				blogFinal += '<h3>' + currentHeader + '</h3>';
				var sections = current.join('\n').split(/\n\n/);
				sections.map(function(c){
					blogFinal += '<section>';
					var paragraphs = c.split(/\n/);
					paragraphs.map(function(d){
						d = d.replace(/\[b\](.*?)\[\/b\]/,'<b>$1</b>');
						d = d.replace(/\[i\](.*?)\[\/i\]/,'<i>$1</i>');
						d = d.replace(/\[del\](.*?)\[\/del\]/,'<del>$1</del>');
						d = d.replace(/\[link\](.*?)\[\/link\]/,'<a href="$1">$1</a>');
						d = d.replace(/\[image\](.*?)\[\/image\]/,'<img class="clickimage" src="$1" onclick="var x = window.open(\'$1?NR\',\'_blank\');"/>');
						blogFinal += '<p>' + d.trim(); + '</p>';
					});
					blogFinal += '</section>';
				});
				blogFinal += '</div>';
			});
		});
		document.getElementById('admin_preview_content').innerHTML = blogFinal;
		return blogFinal;
	}
	function bearBB2(orginal) {
		var preview = document.getElementById('admin_preview_content');
		while (preview.lastChild)
			preview.lastChild.remove();
		var dptr = document.createElement('div');
		var splited = orginal.split(/\n/).map(function(x){
			x = x.trim();
			//Remove empty lines
			if (x == '')
				return;
			//## Subtitle
			if (x.substr(0,2) == '##') {
				preview.appendChild(dptr); //Commit DIV
				dptr = document.createElement('div');
				var dptrx = document.createElement('h3'); //Append to new div
				dptrx.appendChild(document.createTextNode(x.substr(2).trim()));
				dptr.appendChild(dptrx);
			}
			//# Title
			else if (x.substr(0,1) == '#') {
				preview.appendChild(dptr); //Commit DIV
				dptr = document.createElement('div');
				var dptrx = document.createElement('div'); //Append to base
				dptrx.classList.add('main_content_title');
				var dptrxx = document.createElement('h2');
				dptrxx.appendChild(document.createTextNode(x.substr(1).trim()));
				dptrx.appendChild(dptrxx);
				preview.appendChild(dptrx);
			}
			//----- New DIV
			else if (x.substr(0,5) == '-----') {
				preview.appendChild(dptr);
				dptr = document.createElement('div');
			}
			//[image] Image
			else if (/^\[image\]([^\[\]]+?)\[\/image\]$/.test(x)) {
				x = x.replace(/\[image\]([^\[\]]+?)\[\/image\]/,'$1');
				var dptrxx = document.createElement('a');
				dptrxx.href = x + '?NR';
				dptrxx.target = '_blank';
				var dptrx = document.createElement('img');
				dptrx.classList.add('clickimage');
				dptrx.src = x;
				dptrxx.appendChild(dptrx);
				dptr.appendChild(dptrxx);
			}
			//Normal
			else {
				var isSection = false;
				if (x.substr(0,1) == '|') {
					isSection = true;
					x = x.substr(1).trim();
				}
				x = x.replace(/\[b\]([^\[\]]+?)\[\/b\]/g,'<b>$1</b>');
				x = x.replace(/\[i\]([^\[\]]+?)\[\/i\]/g,'<i>$1</i>');
				x = x.replace(/\[del\]([^\[\]]+?)\[\/del\]/g,'<del>$1</del>');
				x = x.replace(/\[link\]([^\[\]]+?)\[\/link\]/g,'<a href="$1">$1</a>');
				x = x.replace(/\[n\]/g,'<br />');
				var dptrx = document.createElement('p');
				dptrx.innerHTML = x; //Use innerHTML to phase
				if (isSection) {
					var dptrxx = document.createElement('section');
					dptrxx.appendChild(dptrx);
					dptr.appendChild(dptrxx);
				}
				else {
					dptr.appendChild(dptrx);
				}
			}
		});
		preview.appendChild(dptr);
		Array.prototype.slice.call(preview.childNodes).map(function(x){
			if (x.innerHTML.trim() == '')
				x.remove();
		});
		return preview.innerHTML;
	}
</script>
<style>
	#admin_works {
		width: 200px;
		height: 1000px;
		display: block;
		position: absolute;
		z-index: 50;
		overflow-x: hidden;
		overflow-y: scroll;
		background-color: #F6F6F6;
		transition: all 1s
	}
	#admin_works:hover {
		width: 500px;
	}
	#admin_works a {
		display: block;
		white-space: nowrap;
		margin: 5px 0;
	}
	#admin_works a:before {
		content: '- '
	}
	
	#admin_window {
		margin-left: 200px;
		padding-left: 10px;
		min-height: 1000px;
		display: block;
		background-color: #E6E6E6;
	}
	#admin_window>a {
		display: inline-block;
		min-width: 65px;
		padding: 0 5px;
		margin-right: 15px;
	}
	#admin_window>div {
		display: none;
		background-color: #DDDDDD;
		margin-top: 0;
		padding: 5px;
	}
	#admin_window label {
		margin-top: 25px;
	}
	
	#modifypage_addon_selector a {
		padding-left: 20px;
	}
	
	#upload_files_mask {
		height: 62px;
		padding-top: 50px;
		background-color: #CCCCCC;
		text-align: center;
	}
	#upload_files_input {
		display: none;
	}
	.x_upload_preview {
		background-color: #CCCCCC;
		margin-bottom: 5px;
	}
	.x_upload_preview input {
		padding: 0;
	}
	
	#admin_preview_content>div:nth-child(2n) {
		background-color: #F6F6F6;
		padding: 12px 0;
	}
	#admin_preview_content>div:nth-child(2n+1) {
		background-color: #EEEEEE;
		padding: 12px 0;
	}
</style>

<script>
	if (/mobile/i.test(window.navigator.userAgent)) {
		removeAllChild(document.getElementById('main_content'));
		var x = document.createElement('div');
<?php if ($PAGELANG == 'en'): ?>
		x.textContent = 'Error. The publisher\'s IDE supports desktop only!';
<?php else: ?>
		x.textContent = '错误！整合式发布系统只支持桌面系统！';
<?php endif; ?>
		document.getElementById('main_content').append(x);
	}
</script>
