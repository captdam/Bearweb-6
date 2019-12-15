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
	},1000*60*10); //Check every 10mins, renew every 30 mins
} );

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

//Process header: Location navigator
ready().then( () => {
	var localInd = document.createElement('span');
	var url = '';
	localInd.setAttribute('id','main_header_location');
	localInd.textContent = '当前位置：';
	var linker = document.createElement('a');
	linker.href = '/';
	linker.textContent = '主页';
	localInd.appendChild(linker);
	decodeURI(window.location.pathname).split('/').map(function(x,i) {
		if (i == 0) return;
		if (x == '') return;
		url += '/' + x;
		var linker = document.createElement('a');
		linker.href = url;
		linker.textContent = x;
		localInd.appendChild(linker);
	});
	document.getElementById('main_title').insertBefore(localInd,document.querySelector('#main_title>h1'));
} );

//Process page status indecator
ready().then( () => {
	var pageStatus = document.querySelector('html').dataset.pagestatus;
	var textTable = {
		"C" : ["施工中","此页面正在施工中，页面内容随时可能发生更改。"],
		"D" : ["不推荐","由于某些<del>黑幕</del>，本页面的所提到的的内容是不被推荐的内容，本页面随时可能会被删除或锁定（无法访问）。"],
//		"S" : ["特殊页面","总之这个页面的内容都是特殊内容。Web技术层面来解释的话，这个页面是可以被访问但是不应该出现在网站地图的内容。"],
		"A" : ["授权限制","本页面带有权限管理，只有作者，管理员，或是白名单内的用户才可访问。"],
		"P" : ["挂起页面","本页面已被挂起，只有作者与管理员才可以访问。"]
	}
	if (!(pageStatus in textTable))
		return;
	notice(textTable[pageStatus][0],textTable[pageStatus][1]);
} );

//Giving style for content and content list
ready().then( () => {
	//Adding style for content list
	if (!/mobile/i.test(window.navigator.userAgent)) { //Desktop: switch to DPTR with bg-image
		Array.prototype.slice.call(document.querySelectorAll('.contentlist')).map(function(x){
			//No poster, do nothing
			if (x.dataset.bgimage == '/NONE')
				return;
			//Adding bg-image
			x.classList.add('imagefill');
			var opacity = x.dataset.imagefillopacity ? x.dataset.imagefillopacity : '0.8';
			x.style.backgroundImage = 'linear-gradient(to bottom,rgba(255,255,255,' + opacity + '),rgba(255,255,255,' + opacity + ')),url(\'' + x.dataset.bgimage + '\')';
			//Backup orginal HTML and reset element
			var elementList = [];
			x.firstChild.childNodes.forEach(function(y){
				elementList.push(y);
			});
			while(x.lastChild)
				x.lastChild.remove();
			//Adding header
			var header = document.createElement('h2');
			header.textContent = elementList.shift().textContent;
			x.appendChild(header);
			//Using PLTR template for further info
			var pltr = document.createElement('div');
			pltr.classList.add('pltr');
			var pltrImage = document.createElement('img');
			pltrImage.src = x.dataset.bgimage;
			pltr.appendChild(pltrImage);
			var pltrText = document.createElement('div');
			while(elementList.length) {
				pltrText.appendChild(elementList.shift());
			}
			pltr.appendChild(pltrText);
			x.appendChild(pltr);
		});
	}
	else { //Mobile: remove description
		Array.prototype.slice.call(document.querySelectorAll('.contentlist .content_description')).map(function(x){
			x.remove();
		});
	}
	//Adding style for keywords
	Array.prototype.slice.call(document.querySelectorAll('.content_keywords')).map(function(x){
		var keywords = x.textContent.split(', ');
		x.textContent = '';
		keywords.map(function(y){
			var word = document.createElement('a');
			word.textContent = y;
			var color = '#';
			color += Math.floor(Math.random()*128+128).toString(16);
			color += Math.floor(Math.random()*128+128).toString(16);
			color += Math.floor(Math.random()*128+128).toString(16);
			word.style.backgroundColor = color;
			word.href = '/search?search=' + y;
			word.target = 'search';
			x.appendChild(word);
		});
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