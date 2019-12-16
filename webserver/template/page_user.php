<?php if (isset($BW->client['UserInfo'])): ?>
	<div class="main_content_title">
		<h1><?= $PAGEDATA['Title'] ?></h1>
		<p>Yoo, <?= $BW->client['UserInfo']['Username'] ?></p>
	</div>
	<div>
		<h2>基本资料</h2>
		<div class="pltr bearform">
			<img id="modifyAvatarPreview" src="/user/photo?username=<?= $BW->client['UserInfo']['Username'] ?>" style="width: 200px; height: 200px;" />
			<div>
				<label>Username</label>
				<input value="<?= $BW->client['UserInfo']['Username'] ?>" disabled />
				<label>Nickname</label>
				<input id="modifyNickname" value="<?= $BW->client['UserInfo']['Nickname'] ?>" />
				<label>分组</label>
				<input value="<?= implode(', ',$BW->client['UserInfo']['Group']) ?>" disabled />
				<label>Email</label>
				<input id="modifyEmail" value="<?= $BW->client['UserInfo']['Email'] ?>" />
				<label>头像</label>
				<input id="modifyAvatar" type="file" accept="image/*" />
			</div>
		</div>
	</div>
	<div class="bearform">
		<h2>密码修改</h2>
		<label>旧密码</label>
		<input id="modifyPasswordOld" type="password" />
		<ul class="info">
			<li>以前的密码。</li>
		</ul>
		<label>新密码</label>
		<input id="modifyPasswordNew" type="password" />
		<ul class="info">
			<li>英文与数字组合。</li>
			<li>6-16个字符。</li>
			<li>将来可修改。</li>
			<li>请牢记本密码作为你的登录凭证。</li>
		</ul>
		<label>重复密码</label>
		<input id="modifyPasswordRepeat" type="password" />
		<ul class="info">
			<li>重复一遍新密码。</li>
		</ul>
		<button onclick="user.modifyAccount()">修改密码</button>
	</div>
	<div class="bearform">
		<h2>Bearcraft信息修改</h2>
		<label>皮肤</label>
		<input id="modifyMCSkin" type="file" accept="image/jpeg"/>
		<label>披风</label>
		<input id="modifyMCCape" type="file" accept="image/jpeg"/>
	</div>
	<div>
		*****
	</div>
	<div class="bearform">
		<button onclick="logoutPrompt()">退出账户</button>
	</div>
	<script>
		function logoutPrompt() {
			modal([
  <?php if ($PAGELANG == 'en'): ?>
				'<h2>Are you double trible quadruple sure you want to logout?</h2>'+
				'<p>This will end the current session including all tabs you open in the browser from the same site. If there is any pending progress (or upload in progress), such as editing a page, leaving comments, you will permanently loss your progress.</p>'+
				'<button onclick="logout()">Yes, I am sure</button>'
  <?php else: ?>
				'<h2>确定肯定坚定不否定要退出？</h2>'+
				'<p>退出登录将会终止当前会话的所有任务（包括你在其他tab打开的本站的页面）。如果你有尚未提交或正在提交的任务（例如：编辑某个页面，留言），你也许会永远地失去这些作业进程。</p>'+
				'<button onclick="logout()">对，退出，不怂</button>'
  <?php endif; ?>
			]);
		}
		function logout() {
			ajaxAPI('POST','/api/user/logout',{}).then(
				([status,response]) => {
  <?php if ($PAGELANG == 'en'): ?>
					modalFormat(['You have been logout','Give me 5 seconds...']);
  <?php else: ?>
					modalFormat(['成功退出','等待5秒……']);
  <?php endif; ?>
					setTimeout(function(){window.location.reload()},5000);
				},
				([status,response]) => {
  <?php if ($PAGELANG == 'en'): ?>
					modalFormat(['Fail to logout','API error '+status,response]);
  <?php else: ?>
					modalFormat(['退出失败','API错误 '+status,response]);
  <?php endif; ?>
				}
			);
		}
		
	
	
		document.getElementById('modifyAvatar').addEventListener('change',function(){
			var avatar = this.files[0];
			var reader = new FileReader();
			reader.readAsDataURL(avatar);
			reader.onload = function(){
				var preview = document.getElementById('modifyAvatarPreview');
				preview.dataset.data64 = reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/');
				preview.src = window.URL.createObjectURL(avatar);
			};
		});
		document.getElementById('modifyMCSkin').addEventListener('change',function(){
			var image = this.files[0];
			var reader = new FileReader();
			reader.readAsDataURL(image);
			reader.onload = function(){
				var data64 = reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/');
				ajax(
					'POST',
					'/api/user/update',
					{
						"mcskin" : data64
					},
					function(status,response){ //HTTP Callback
						if (status == 201)
							alert('MC Skin修改成功');
						else
							alert(response.Error);
					}
				);
			};
		});
		document.getElementById('modifyMCCape').addEventListener('change',function(){
			var image = this.files[0];
			var reader = new FileReader();
			reader.readAsDataURL(image);
			reader.onload = function(){
				var data64 = reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/');
				ajax(
					'POST',
					'/api/user/update',
					{
						"mccape" : data64
					},
					function(status,response){ //HTTP Callback
						if (status == 201)
							alert('MC cape修改成功');
						else
							alert(response.Error);
					}
				);
			};
		});
	</script>
	
<?php else: ?>
	<div class="main_content_title">
  <?php if ($PAGELANG == 'en'): ?>
		<h2>Login</h2>
		<p>Use your username and password to login to <?= SITENAME ?></p>
  <?php else: ?>
		<h2>登录</h2>
		<p>使用用户名与密码登录<?= SITENAME ?></p>
  <?php endif; ?>
	</div>
	<div>
		<form class="bearform" onsubmit="event.preventDefault();login();">
  <?php if ($PAGELANG == 'en'): ?>
			<label>Username</label>
			<input id="loginUsername" type="text" autofocus="true" />
			<label>Password</label>
			<input id="loginPassword" type="password" />
			<button type="submit">Login</button>
  <?php else: ?>
			<label>用户名</label>
			<input id="loginUsername" type="text" autofocus="true" />
			<label>密码</label>
			<input id="loginPassword" type="password" />
			<button type="submit">登录</button>
  <?php endif; ?>
		</form>
	</div>
	<script>
		function login() {
			var usernameField = document.getElementById('loginUsername');
			var passwordField = document.getElementById('loginPassword');
			
			var username = usernameField.value;
			var password = passwordField.value;
			
			usernameField.style.borderColor = 'black';
			passwordField.style.borderColor = 'black';
			
			if (!/^[A-Za-z0-9]{2,16}$/.test(username)) {
				usernameField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to login','Username bad format.']);
  <?php else: ?>
				modalFormat(['登陆失败','用户名格式错误。']);
  <?php endif; ?>
				return;
			}
			if (!/^[A-Za-z0-9]{6,16}$/.test(password)) {
				passwordField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to login','Password bad format.']);
  <?php else: ?>
				modalFormat(['登陆失败','密码格式错误。']);
  <?php endif; ?>
				return;
			}
			
			
			
			ajaxAPI(
				'POST',
				'/api/user/login',
				{
					"username" : username,
					"password" : md5( md5(password) + cookie.get('Salt') )
				}
			).then(
				([status,response]) => {
  <?php if ($PAGELANG == 'en'): ?>
					modalFormat(['You have been logout','Give me 5 seconds...']);
  <?php else: ?>
					modalFormat(['成功退出','等待5秒……']);
  <?php endif; ?>
					setTimeout(function(){window.location.reload()},5000);
				},
				([status,response]) => {
					passwordField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
					modalFormat(['Fail to login','API error '+status,response]);
  <?php else: ?>
					modalFormat(['登陆失败','API错误 '+status,response]);
  <?php endif; ?>
				}
			);
		}
	</script>
	<div class="info">
  <?php if ($PAGELANG == 'en'): ?>
		<p>Having issue?</p>
		<ul>
			<li>Make sure you are running the latest version of browser. Some old versions may not support some functions.</li>
			<li>Enable JavaScript on this site.</li>
			<li>Did you accidentally enable the CAPSLOCK?</li>
			<li>Of course, you should enter the correct username/password.</li>
		</ul>
		<p>We use JavaScript to send async request to server-side APIs.</p>
  <?php else: ?>
		<p>遇到问题？</p>
		<ul>
			<li>确保你的浏览器是最新版本，一些老版本浏览器可能不支持某些功能。</li>
			<li>允许本站执行JavaScript。</li>
			<li>用户名与密码使用正确格式，检测是否打开了大写锁定。</li>
			<li>当然，输入正确的用户名与密码。</li>
		</ul>
		<p>我们使用JavaScript在后台异步向API发送XMLHttpRequest来执行所有操作。</p>
  <?php endif; ?>
	</div>
	<div class="main_content_title">
  <?php if ($PAGELANG == 'en'): ?>
		<h2>Register</h2>
		<p>No account? Create one (￣▽￣)／</p>
  <?php else: ?>
		<h2>注册</h2>
		<p>没有Beardle账号？注册一个就好啦(￣▽￣)／</p>
  <?php endif; ?>
		
	</div>
	<div>
		<form class="bearform" onsubmit="event.preventDefault();register();">
  <?php if ($PAGELANG == 'en'): ?>
			<label>Username</label>
			<input id="registerUsername" type="text" />
			<ul class="info">
				<li>Alphanumeric.</li>
				<li>2-16 characters.</li>
				<li>Cannot modify later.</li>
				<li>This is your identification when login, please keep a copy in a secure location.</li>
			</ul>
			<label>Nickname</label>
			<input id="registerNickname" type="text" />
			<ul class="info">
				<li>Any character in any language is fine, but no special character.</li>
				<li>2-16 characters.</li>
				<li>Can modify later.</li>
			</ul>
			<label>Password</label>
			<input id="registerPassword" type="password" />
			<ul class="info">
				<li>Alphanumeric.</li>
				<li>6-16 characters.</li>
				<li>Can modify later.</li>
				<li>This is your identification verification when login, please keep a copy in a secure location.</li>
			</ul>
			<label>Repeat password</label>
			<input id="registerPasswordRepeat" type="password" />
			<ul class="info">
				<li>Type your password again.</li>
			</ul>
			<button type="submit">Register</button>
  <?php else: ?>
			<label>用户名</label>
			<input id="registerUsername" type="text" />
			<ul class="info">
				<li>英文与数字组合。</li>
				<li>2-16个字符。</li>
				<li>将来不可修改。</li>
				<li>请牢记本用户名作为你的登录凭证。</li>
			</ul>
			<label>昵称</label>
			<input id="registerNickname" type="text" />
			<ul class="info">
				<li>可以使用任意语言任意字符，但请不要使用特殊字符。</li>
				<li>2-16个字符以内。</li>
				<li>将来可修改。</li>
			</ul>
			<label>密码</label>
			<input id="registerPassword" type="password" />
			<ul class="info">
				<li>英文与数字组合。</li>
				<li>6-16个字符。</li>
				<li>将来可修改。</li>
				<li>请牢记本密码作为你的登录凭证。</li>
			</ul>
			<label>重复密码</label>
			<input id="registerPasswordRepeat" type="password" />
			<ul class="info">
				<li>重复一遍密码。</li>
			</ul>
			<button type="submit">注册</button>
  <?php endif; ?>
		</form>
	</div>
	<script>
		function register() {
			var usernameField = document.getElementById('registerUsername');
			var nicknameField = document.getElementById('registerNickname');
			var passwordField = document.getElementById('registerPassword');
			var passwordFieldAUX = document.getElementById('registerPasswordRepeat');
			
			var username = usernameField.value;
			var nickname = nicknameField.value;
			var password = passwordField.value;
			
			usernameField.style.borderColor = 'black';
			nicknameField.style.borderColor = 'black';
			passwordField.style.borderColor = 'black';
			passwordFieldAUX.style.borderColor = 'black';
			
			if (!/^[A-Za-z0-9]{2,16}$/.test(username)) {
				usernameField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to register','Username bad format.']);
  <?php else: ?>
				modalFormat(['注册失败','用户名格式错误。']);
  <?php endif; ?>
				return;
			}
			if (!/^[^~!@#$%^&*()_\+`\-=\|\\\\{\}\[\];:"\',.\/\<\>\?\s]{2,16}$/.test(nickname)) {
				nicknameField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to register','Nickname bad format.']);
  <?php else: ?>
				modalFormat(['注册失败','昵称格式错误。']);
  <?php endif; ?>
				return;
			}
			if (!/^[A-Za-z0-9]{6,16}$/.test(password)) {
				passwordField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to register','Password bad format.]);
  <?php else: ?>
				modalFormat(['注册失败','密码格式错误。']);
  <?php endif; ?>
				return;
			}
			if ( password != passwordFieldAUX.value ) {
				passwordFieldAUX.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
				modalFormat(['Fail to register','Passwords not matched.']);
  <?php else: ?>
				modalFormat(['注册失败','密码不匹配。']);
  <?php endif; ?>
				return;
			}
			
			ajaxAPI(
				'POST',
				'/api/user/register',
				{
					"username" : username,
					"password" : md5(password),
					"nickname" : nickname
				}
			).then(
				([status,response]) => {
					document.getElementById('loginUsername').value = username;
					document.getElementById('loginPassword').value = password;
					login();
				},
				([status,response]) => {
					passwordField.style.borderColor = 'red';
  <?php if ($PAGELANG == 'en'): ?>
					modalFormat(['Fail to register','API error '+status,response]);
  <?php else: ?>
					modalFormat(['注册失败','API错误 '+status,response]);
  <?php endif; ?>
				}
			);
			
			
		}
	</script>
	
<?php endif; ?>