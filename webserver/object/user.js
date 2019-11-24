'use strict';
var user = {

	//Util - Get profile of a user [UNI]
	view: function(username,callback) {
		ajax('GET','/api/user/profile?username='+username,{},function(status,response){
			callback(status,response);
		});
	},

	//Register a user - ONLY use on login page
	register: function() {
		//Reset UI
		document.getElementById('registerPasswordRepeat').style.borderColor = 'black';
		document.getElementById('registerUsername').style.borderColor = 'black';
		document.getElementById('registerNickname').style.borderColor = 'black';
		document.getElementById('registerPassword').style.borderColor = 'black';
		//Get values
		var username = document.getElementById('registerUsername').value;
		var nickname = document.getElementById('registerNickname').value;
		var password = document.getElementById('registerPassword').value;
		//Check values
		try {
			if (password != document.getElementById('registerPasswordRepeat').value) {
				document.getElementById('registerPasswordRepeat').style.borderColor = 'red';
				throw '';
			}
			if (!/^[A-Za-z0-9]{2,16}$/.test(username)) {
				document.getElementById('registerUsername').style.borderColor = 'red';
				throw '';
			}
			if (!/^[^~!@#$%^&*()_\+`\-=\|\\\\{\}\[\];:"\',.\/\<\>\?\s]{2,16}$/.test(nickname)) {
				document.getElementById('registerNickname').style.borderColor = 'red';
				throw '';
			}
			if (!/^[A-Za-z0-9]{6,16}$/.test(password)) {
				document.getElementById('registerPassword').style.borderColor = 'red';
				throw '';
			}
		} catch(e) {
			return;
		}
		//Send request
		ajax(
			'POST',
			'/api/user/register',
			{ //Sending data
				"username" : username,
				"password" : md5(password),
				"nickname" : nickname
			},
			function(status,response){ //HTTP Callback
				if (status == 201) {
					document.getElementById('loginUsername').value = username;
					document.getElementById('loginPassword').value = password;
					user.login();
				}
				else
					alert(response.Error);
			}
		);
	},

	//Send login to get token - ONLY use on login page
	login: function() {
		//Get values
		var username = document.getElementById('loginUsername').value;
		var password = document.getElementById('loginPassword').value;
		//Check values
		if (!/^[A-Za-z0-9]{2,16}$/.test(username)) {
			alert('用户名格式错误');
			return;
		}
		//Send request
		ajax(
			'POST',
			'/api/user/login',
			{ //Sending data
				"username" : username,
				"password" : md5(password)
			},
			function(status,response){ //HTTP Callback
				if (status == 201)
					window.location.reload();
				else
					alert(response.Error);
			}
		);
	},

	//Ask to reset token (logout)
	logout: function() {
		//confirm
		if (!confirm('确定要退出？这可能会造成你尚未完成的工作被系统中断。'))
			return;
		//Send request
		ajax('GET','/api/user/logout',{},function(status,response){
			if (status == 410)
				window.location.reload();
			else
				alert(response.Error);
		});
	},

	//Modify profile
	modifyProfile: function() {
		//Reset UI
		document.getElementById('modifyNickname').style.borderColor = 'black';
		document.getElementById('modifyEmail').style.borderColor = 'black';
		//Get values
		var nickname = document.getElementById('modifyNickname').value;
		var email = document.getElementById('modifyEmail').value;
		var avatar = document.getElementById('modifyAvatarPreview').dataset.data64;
		//Check values
		if (!/^[^~!@#$%^&*()_\+`\-=\|\\\\{\}\[\];:"\',.\/\<\>\?\s]{2,16}$/.test(nickname)) {
			document.getElementById('modifyNickname').style.borderColor = 'red';
			return;
		}
		if (!/^.+@.+\..+$/.test(email)) {
			document.getElementById('modifyEmail').style.borderColor = 'red';
			return;
		}
		//Send request
		var sendData = {
			"nickname" : nickname,
			"email" : email
		}
		if (typeof avatar != 'undefined')
			sendData['avatar'] = avatar;
		ajax(
			'POST',
			'/api/user/update',
			sendData,
			function(status,response){ //HTTP Callback
				if (status == 201)
					alert('资料修改成功');
				else
					alert(response.Error);
			}
		);
	},

	//Modify account
	modifyAccount: function() {
		//Reset UI
		document.getElementById('modifyPasswordOld').style.borderColor = 'black';
		document.getElementById('modifyPasswordNew').style.borderColor = 'black';
		document.getElementById('modifyPasswordRepeat').style.borderColor = 'black';
		//Get values
		var passwordOld = document.getElementById('modifyPasswordOld').value;
		var passwordNew = document.getElementById('modifyPasswordNew').value;
		//Check values
		if (passwordNew != document.getElementById('modifyPasswordRepeat').value) {
			document.getElementById('modifyPasswordRepeat').style.borderColor = 'red';
			return;
		}
		if (!/^[A-Za-z0-9]{6,16}$/.test(passwordNew)) {
			document.getElementById('modifyPasswordNew').style.borderColor = 'red';
			return;
		}
		if (!/^[A-Za-z0-9]{6,16}$/.test(passwordOld)) {
			document.getElementById('modifyPasswordNew').style.borderColor = 'red';
			return;
		}
		//Send request
		ajax(
			'POST',
			'/api/user/update',
			{ //Sending data
				"password_old" : md5(passwordOld),
				"password_new" : md5(passwordNew)
			},
			function(status,response){ //HTTP Callback
				if (status == 201)
					alert('密码修改成功');
				else
					alert(response.Error);
			}
		);
	},

	//Show user online (update lastActive) [INI]
	active: function() {
		ajax('GET','/api/user/at',{},function(status,response){
			//User
			if (status == 200) {
				//New page or just login: Need to rewrite the header
				if (window.username == null) {
					window.username = response.Username;
					var navUser = document.getElementById('header_nav_user');
					navUser.textContent = '';
					var x = document.createElement('img');
					x.src = '/user/photo?username=' + username;
					navUser.appendChild(x);
					var y = document.createElement('span');
					y.textContent = username;
					navUser.appendChild(y);
				}
			}
			//Visitor
			else {
				//User just logout: Need to rewrite the header and provide notice
				if (window.username != null) {
					window.username = null;
					var navUser = document.getElementById('header_nav_user');
					if (navUser.lastChild) {
						navUser.lastChild.remove();
						navUser.textContent = '登录';
					}
					notice('已下线','你的账户已登出，在重新登录前，你所提交的操作将可能出错。请重新登录后。');
				}
			}
		});
	}
};

window.username = null;

user.active();
setInterval(function(){
	user.active();
},60000);
