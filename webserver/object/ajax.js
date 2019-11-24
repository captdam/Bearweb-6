function ajax(method,url,post,callback) {
	'use strict';
	var body = '';
	for (var key in post) {
		body += key + '=' + encodeURIComponent(post[key]) + '&';
	}
	var xhr = new XMLHttpRequest();
	xhr.open(method,url);
	xhr.overrideMimeType('application/json');
	xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	xhr.onload = function() {
		callback(this.status,JSON.parse(this.responseText));
	}
	xhr.withCredentials = true;
	xhr.send(body.slice(0,-1));
}