//Base AJAX function
function ajax(method,url,post,progress) {
	'use strict';
	
	var request = '';
	for (var key in post) {
		request += key + '=' + encodeURIComponent(post[key]) + '&';
	}
	request = request.slice(0,-1);
	
	return new Promise( function(resolve,reject) {
		var xhr = new XMLHttpRequest();
		xhr.open(method,url);
		xhr.overrideMimeType('application/json');
		xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		xhr.withCredentials = true;
		
		//Promise only accepts one param
		//Use array to cast multi params into one param
		
		xhr.onload = () => resolve([xhr.status,xhr.response]);
		
		xhr.onerror = () => reject([0,'XHR error']);
		xhr.onabort = () => reject([0,'XHR abort']);
		xhr.ontimeout = () => reject([0,'XHR timeout']);
		
		if (typeof progress != 'undefined') {
			xhr.onprogress = (e) => {
				progress( e.loaded , e.lengthComputable?e.total:-1 );
			}
		}
		
		xhr.send(request);
	} );
}

//AJAX to API, return must be JSON
function ajaxAPI(method,url,post,progress) {
	'use strict';
	
	return new Promise( (API_OK,API_FAIL) => {
		ajax(method,url,post,progress).then(
			([status,response]) => { //XHR success
			
				//API success => return JSON
				try {
					API_OK([ status, JSON.parse(response) ]); //API returns JSON
					return;
				} catch(e) {
					if (!(e instanceof SyntaxError)) throw e;
				}
				
				//API fail => return HTML contains error info
				var error = /BW_\w+Error - [^\<]+/.exec(response);
				if (error)
					API_FAIL([status,error[0]]); //BW error template info in response
				else
					API_FAIL([status,response]); //Raw response
			},
			([status,response]) => { //XML fail
				API_FAIL([status,response]);
			}
		);
	} );
}