'use strict';

//Cookie util
class Cookie {
	constructor() {
		this.cookie = new Array();
		this.refresh();
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
			this.cookie[ orginal[i].split('=')[0].trim() ] = decodeURIComponent(orginal[i].split('=')[1].trim());
		}
	}
}
var cookie = new Cookie();

//Modal
function modal(content) {
	var modalContainer = document.getElementById('modal_container');
	var modal = document.getElementById('modal');
	var modalContent = document.getElementById('modal_content');
	
	//Close modal
	if (typeof content == 'undefined') {
		modal.style.top = '-100%';
		setTimeout( () => modalContainer.style.background = 'transparent', 400);
		setTimeout( () => modalContent.innerHTML = '', 1000);
		setTimeout( () => modalContainer.style.display = 'none', 1400);
	}
	
	//Display modal
	else {
		modalContent.innerHTML = content;
		setTimeout( () => modalContainer.style.background = 'rgba(0,0,0,0.7)', 50);
		modalContainer.style.display = 'block';
		setTimeout( () => modal.style.top = '100px', 400);
	}
	
}
function modalFormat(contents) {
	var display = '';
	contents.forEach((x,i) => { //First element: i = 0 => Title
		if (i)
			display += '<p>' + x + '</p>';
		else
			display += '<h2>' + x + '</h2>';
	});
	modal(display);
}

//Load a file to a tag and save the content as dataURL
function loadBlob(file) {
	return new Promise( function(resolve,reject) {
		var reader = new FileReader();
		reader.readAsDataURL(file);
		reader.onload = () => resolve([
			reader.result.replace(/^data\:.*?\/.*?\;base64\,/,'').replace('-','+').replace('_','/'),
			window.URL.createObjectURL(file)
		]);
		reader.onerror = () => reject('Error');
		reader.onabort = () => reject('Abort');
	} );
}
function loadBlobDisplay(file,target) {
	loadBlob(file).then(
		([base64,url]) => {
			target.dataset.data64 = base64;
			target.src = url;
		},
		(errInfo) => {
			var errSpan = document.createElement('p');
			errSpan.innerText = 'File reader error: ' + errInfo;
			target.replaceWith(errSpan);
		}
	);
}



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