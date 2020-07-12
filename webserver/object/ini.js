'use strict';

//Page load promise
function ready() {
	return new Promise( (resolve,reject) => {
		window.addEventListener('load',resolve);
	} );
}

//Request desktop version (phone request desktop version)
ready().then( () => {
	var ua = window.navigator.userAgent;
	if (!/mobile/i.test(ua))
		document.querySelector('meta[name=viewport]').content = 'width=1024';
} );

//Renew session
ready().then( () => {
	setInterval(function(){
		var lastComTime = cookie.get('LastCom') * 1000; //Set by server-side framework
		if ( Date.now() > lastComTime + 1000 * 60 * 30 )
			ajax('HEAD','/api/user/renew',{});
	},1000*60*10); //Check every 10mins, renew every 30mins. 
} ); //Using cookie instead of variable to share the same process between tabs

//Process page HTML head: Phone menu
ready().then( () => {
	var menu = document.getElementById('phone_menu_button');
	var nav = document.getElementById('header_nav');
	var search = document.getElementById('search');
	menu.addEventListener('click',function(){
		if (menu.textContent == '≡') {
			menu.textContent = '×';
			nav.style.display = 'block';
			search.style.display = 'block';
		}
		else {
			menu.textContent = '≡';
			nav.style.display = 'none';
			search.style.display = 'none';
		}
	});
} );

//Process side tool bar
ready().then( () => {
	var side = document.getElementById('side');
	var buttons = side.querySelectorAll('img');
	buttons[0].addEventListener('click',function(){ //Top of page
		scroll(0,30,50);
	});
} );

//Add style for HTML <SELECT>
ready().then( () => {
	Array.prototype.slice.call(document.querySelectorAll('select')).map(function(x){
		x.addEventListener('change',function(y){
			Array.prototype.slice.call(x.querySelectorAll('option')).map(function(z){
				if (z.value == x.value)
					x.style.cssText = z.style.cssText;
			});
		});
		Array.prototype.slice.call(x.querySelectorAll('option')).map(function(z){ //Ini
			if (z.value == x.value)
				x.style.cssText = z.style.cssText;
		});
	});
} );