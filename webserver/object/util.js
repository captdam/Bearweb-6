'use strict';

//Get cookie by name
class Cookie {
	constructor() {
		this.cookie = new Array();
	}
	get(name) {
		this.refresh();
		return (typeof name == 'undefined') ? this.cookie : this.cookie[name];
	}
	set(name,value) {
		document.cookie = name + '=' + value;
	}
	remove(name) {
		document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT';
	}
	refresh() {
		var orginal = document.cookie.split(/\;s*/);
		var size = orginal.length;
		for (var i = 0; i < size; i++) {
			this.cookie[orginal[i].split('=')[0]] = orginal[i].split('=')[1];
		}
	}
}
var cookie = new Cookie();

//Scroll page with animation
function scroll(toLocation,stepCount,stepTime) {
	//Get data
	if (typeof stepCount != 'number')
		stepCount = 50;
	if (typeof stepTime != 'number')
		stepTime = 40;
	var orginalLocation = document.documentElement.scrollTop + document.body.scrollTop;
	//Calculate position
	var stepLocation = [];
	var fireTime = [];
	for (var i = 0; i < stepCount; i++) {
		stepLocation[i] = toLocation + (orginalLocation - toLocation) * ( 0.5 + 0.5 * Math.cos( 0 - Math.PI * i / stepCount ) );
		fireTime[i] = i * stepTime;
	}
	//set animation
	for (var i = 0; i < stepCount; i++) {
		//Turn command into string beecause stepLocation will be changed later
		setTimeout(new Function('window.scrollTo(0,' + stepLocation[i] + ')'),fireTime[i]);
	}
	setTimeout(function(){
		window.scrollTo(0,toLocation);
	},stepCount*stepTime);
}

//Create alternative <input> for <select> if value contains *
function altInput(x) { //use "this"
	var oldInput = document.querySelector('input[id='+x.id.replace(/_alt$/,'')+']');
	if (oldInput) {
		oldInput.remove();
		x.id = x.id.replace(/_alt$/,'');
	}
	else {
		if (x.value.includes('*')) {
			var newInput = document.createElement('input');
			newInput.id = 'modifypage_copyright';
			x.id += '_alt';
			x.parentNode.insertBefore(newInput,x.nextSibling);
		}
	}
}

//Append notice to page
function notice(note,desc) {
	var x = document.createElement('div');
	var noticeHead = document.createElement('b');
	noticeHead.textContent = note;
	var noticeDesc = document.createElement('i');
	noticeDesc.textContent = desc+'（点击关闭这个提示）';
	noticeDesc.classList.add('info');
	x.appendChild(noticeHead);
	x.appendChild(noticeDesc);
	x.classList.add('cover_bottom');
	x.addEventListener('click',function(){
		this.remove();
	});
	document.getElementById('main_content').appendChild(x);
}

//Open/close spoiler
/*
function spoiler(spoilerSwitchID,spoilerContentID){
	//Ini
	var switchElement = [];
	var contentElement = [];
	//Get element by HTML ID
	if (typeof spoilerSwitchID == 'string') //Inputs should be string or array
		switchElement[0] = spoilerSwitchID;
	else
		switchElement = spoilerSwitchID;
	if (typeof spoilerContentID == 'string')
		contentElement[0] = spoilerContentID;
	else
		contentElement = spoilerContentID;
	//Add event listener
	switchElement.map(function(x){
		var currentSwitch = document.getElementById(x);
		contentElement.map(function(y){
			currentSwitch.addEventListener('click',function(){
				var currentContent = document.getElementById(y);
				console.log(currentContent.style.maxHeight,currentContent.scrollHeight,currentContent.clientHeight);
				console.log(currentContent.style.maxHeight == '0px');
				if (currentContent.style.maxHeight == '0px') {
					currentContent.style.maxHeight = (currentContent.scrollHeight + currentContent.clientHeight) + 'px';
					currentContent.style.opacity = '1';
				}
				else {
					currentContent.style.maxHeight = '0px';
					currentContent.style.opacity = '0';
				}

			});
		});
	});
	//Adding CSS
	switchElement.map(function(x){
		var current = document.getElementById(x);
		if (!current.classList.contains('spoiler_switch'))
			current.classList.add('spoiler_switch');
	});
	contentElement.map(function(x){
		var current = document.getElementById(x);
		current.style.maxHeight = '0px';
		if (!current.classList.contains('spoiler_content'))
			current.classList.add('spoiler_content');
	});
}
*/