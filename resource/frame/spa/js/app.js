class App{
	constructor(appName, version){
		this.resourceDomain = 'http://s.holdon.com/';
		this.appName = appName;
		this.version = version;
		this.headObj = document.getElementsByTagName('head')[0];
		this.cssObj = document.createElement('link');
		this.cssObj.rel = 'stylesheet';
		this.jsObj = document.createElement('script');
		this.jsObj.async = 'async';
		this.imgObj = new Image();
		
	}
	
	/**
	 * 载入文件
	 */
	load(fileName, callBack = null){
		let suffix = fileName.substr(fileName.lastIndexOf('.') + 1);
		if(fileName.indexOf('http') < 0){
			fileName = this.resourceDomain + fileName;
		}
		//加载css文件
		if(suffix == 'css'){
			this.cssObj.href = fileName;
			this.headObj.appendChild(this.cssObj);
			this.cssObj.onload = callBack;
		}
		//加载js文件
		else if(suffix == 'js'){
			this.jsObj.src = fileName;
			this.headObj.appendChild(this.jsObj);
			this.jsObj.onload = callBack;
			
			/*
			let xhr = new XMLHttpRequest();
			xhr.open('get', fileName, true);
			xhr.send();
			xhr.onreadystatechange = function(){
				if(xhr.readyState == 4 && xhr.status == 200){
					//console.log(xhr.responseText)
					eval(xhr.responseText)
				}
			}
			//*/
		}
		//加载图片
		else{
			this.imgObj.src = fileName;
			this.imgObj.onload = callBack;
		}
		return this;
	}
	
	/**
	 * 校对版本号
	 */
	info(){
		console.log(this)
		console.log('newVersion:', App.getAttr('version'));
		console.log('localVersion:', localStorage.version);
	}
	getAttr(name){
		return this.name;
	}
}


//*
let version = '1.0.00';//版本号
let appName = 'admin';//应用名
let app = new App(appName, version);
app.load('/frame/spa/css/app.css')
.load('/frame/spa/js/login.js', app.info);


//*/
