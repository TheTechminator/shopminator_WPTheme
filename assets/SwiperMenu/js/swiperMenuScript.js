function SwiperMenu (swiperID, userFunc) {
	swiperContainer = document.getElementById(swiperID);
	swiperSide = swiperContainer.children[0];
	swipableArea = swiperContainer.children[1];
	//swiperOpen = swiperContainer.children[2];
	swiperClose = swiperSide.children[0];
	userData = {
		direction : "right",
		transition : "300ms",
		fast : 150,
		swiperOpenBtns: "swiperMenuNavOpenBtns",
	};

	if(userFunc == undefined) userFunc = function() {};

	/* -------------------------------------------------------------------- */
	/*swiperSide.style.transition = "transform "+userData.transition+" linear";
	swipableArea.style.transition = "opacity "+userData.transition+" linear";*/
	swiperSide.style.transition = "transform "+userData.transition;
	swipableArea.style.transition = "opacity "+userData.transition;

	preventDefault = function(e) {
      	e.preventDefault();
   	};

   	disableScroll = function() {
     	 document.body.addEventListener('touchmove', preventDefault, { passive: false });
   	};

   	enableScroll = function() {
      	document.body.removeEventListener('touchmove', preventDefault, { passive: false });
   	};

   	pointer = {
		x : -1,
		y : -1,
		firstX : -1,
		firstY: -1,
	};

	touch = {
		id : -1,
		x : -1,
		y : -1,
		firstX : -1,
		firstY: -1,
		add : function() {
			touch.id = event.touches[event.touches.length-1].identifier;
			touch.firstX = event.touches[touch.getIndex()].clientX;
			touch.firstY = event.touches[touch.getIndex()].clientY;

			pointer.firstX = touch.firstX;
			if(menu.status == "closed") {
				pointer.firstX = 0;
				touch.firstX = 0;
			}

			pointer.firstY = touch.firstY;

			touch.x = touch.firstX;
			touch.y = touch.firstY;

		},
		getIndex : function() {
			for(var i = 0; i<event.touches.length; i++){
				if(event.touches[i].identifier == touch.id){
					return i;
				}
			}
			return -1;
		},
		refresh : function() {
			touch.x = event.touches[touch.getIndex()].clientX;
			touch.y = event.touches[touch.getIndex()].clientY;

			pointer.x = touch.x;
			pointer.y = touch.y;
		},
		clear: function() {
			touch.id = -1;
		}
	};

	menu = {
		width : swiperSide.offsetWidth,
		status : "closed",
		element : swiperSide.style,
		area : swipableArea.style,
		closing : false,
		move : function() {
	        var transform = 0;

	        if(menu.status == "closed"){
		        if(pointer.x < menu.width){
		        	transform = pointer.x;
		        }else{
		        	transform = menu.width;
		        }

		        if(pointer.x < 0){
		        	transform = 0;
		        }

		        menu.area.opacity = (transform / menu.width) / 2;

		        menu.element.transform = "translateX("+transform+"px)";
	        }else if(menu.status == "opened"){
	        	var calcInPosX = pointer.firstX - pointer.x;

	        	if(pointer.firstX > menu.width){
	        		pointer.firstX = menu.width;
	        		calcInPosX = 0;
	        	}else if(calcInPosX <= 0){
		            if(menu.width > pointer.x) {
		               	pointer.firstX = pointer.x;
		            }
		            calcInPosX = 0;
		        }

		        menu.area.opacity = (((menu.width)-calcInPosX) / menu.width) / 2;

		        menu.element.transform = 'translateX('+((menu.width)-calcInPosX)+'px)';
	        }
		},
		moveEnd : function() {
			var phase = getPhase(menu.width/2);

			if(phase == "fast"){
				/* -------------------------------------------------------------------- */
				menu.element.transition = "transform " + userData.fast + "ms";
				menu.area.transition = "opacity " + userData.fast + "ms";
				/* -------------------------------------------------------------------- */

				setTimeout(function() {
					/* -------------------------------------------------------------------- */
					menu.element.transition = "transform "+userData.transition;
					menu.area.transition = "opacity "+userData.transition;
					/* -------------------------------------------------------------------- */
				}, userData.fast);
			}else {
				/* -------------------------------------------------------------------- */
				menu.element.transition = "transform "+userData.transition;
				menu.area.transition = "opacity "+userData.transition;
				/* -------------------------------------------------------------------- */
			}

			if(menu.status == "closed") {
				if(phase == "end" || phase == "fast"){
					menu.open();
				}else if(phase == "cancel"){
					menu.close();
				}
			}else if(menu.status == "opened"){
				if(phase == "end" || phase == "fast"){
					menu.close();
				}else if(phase == "cancel"){
					menu.open();
				}
			}
		},
		moveBegin : function() {
			menu.element.transition = "0s";
			menu.area.transform = "translateX(100vw)";
			menu.area.transition = '0s';
		},
		open : function() {
			menu.element.transform = "translateX("+menu.width+"px)";
			menu.status = "opened";
			menu.area.transform = "translateX(100vw)";
			menu.area.opacity = '0.5';

			if(userFunc.menuOpenEvent != undefined) userFunc.menuOpenEvent();
		},
		close : function() {
			menu.element.transform = "translateX(0px)";
			menu.status = "closed";
			menu.area.opacity = '0';
			menu.closing = true;
			setTimeout(function() {
				menu.area.transform = "translateX(0px)";
				menu.closing = false;
			}, parseInt(userData.transition));

			if(userFunc.menuCloseEvent != undefined) userFunc.menuCloseEvent();
		},
	};

	window.addEventListener("resize", function(){
		menu.width = swiperSide.offsetWidth;
		if(menu.status == "closed") {
			menu.element.transition = "0s";
			menu.element.transform = "translateX(0px)";
			menu.area.transform = "translateX(0px)";
			setTimeout(function() {
				/* -------------------------------------------------------------------- */
				menu.element.transition = "transform "+userData.transition;
				/* -------------------------------------------------------------------- */
			}, 100);
		}else if(menu.status == "opened"){
			menu.element.transition = "0s";
			menu.element.transform = "translateX("+menu.width+"px)";
			menu.area.transform = "translateX(100vw)";
			setTimeout(function() {
				/* -------------------------------------------------------------------- */
				menu.element.transition = "transform "+userData.transition;
				/* -------------------------------------------------------------------- */
			}, 100);
		}
	});


	drag = {
		direction : "",
		time : 0,
		now : 0,
		date : 0,
		maxTime : 100,
		begin : function() {
			window.requestAnimationFrame(drag.do);
			drag.now = 1;
			menu.moveBegin();
			disableScroll();
			drag.startTimer();
		},
		do : function() {
			menu.move();
			if (drag.now == 1) {
			    window.requestAnimationFrame(drag.do);
			}else{
				menu.moveEnd();
			}
		},
		end : function() {
			drag.direction = "";
			drag.now = 0;
			enableScroll();
		},
		startTimer : function() {
			drag.time = drag.getTime();
		},
		getTime : function() {
			date = new Date();
			return date.getTime();
		},
		isFastDrag : function() {
			if(drag.time + drag.maxTime >= drag.getTime()){
				return true;
			}else {
				return false;
			}
		},
	};

	getDirection = function() {
	   var dirX = pointer.firstX - pointer.x;
	   var dirY = pointer.firstY - pointer.y;

	   if(Math.abs(dirX) > Math.abs(dirY)){
	   		if(dirX>=0){
		    	return "left";
		  	}else{
		      	return "right";
		   	}
	   	}else{
	   		if(dirY>=0){
		    	return "top";
		  	}else{
		      	return "bottom";
		   	}
	   	}
	};

	getPhase = function(distance) {
	   	var dis = Math.abs(pointer.firstX - pointer.x);
	   	//var dirY = pointer.firstY - pointer.y;

	   	if(drag.isFastDrag()){
	   		return "fast";
	   	}else {
		   	if(dis>distance){
		     	return "end";
		   	}else{
		      	return "cancel";
		   	}
	   	}
	};

	touchStart = function() {
		if(touch.id == -1 && !menu.closing) {
			touch.add();
		}
	};

	touchMove = function() {
		if(touch.id != -1){
			touch.refresh();
			if(drag.direction == ""){
				drag.direction = getDirection();
				if(menu.status == "closed" && drag.direction == userData.direction){
					drag.begin();
				}else if(menu.status == "opened" && getUserReverseDirection() == drag.direction){
					drag.begin();
				}
			}
		}
	};

	getUserReverseDirection = function() {
		var dir = userData.direction;

		var rDir = "";

		switch (dir) {
			case "right":
				rDir = "left";
				break;
			case "left":
				rDir = "right";
				break;
			case "top":
				rDir = "bottom";
				break;
			case "bottom":
				rDir = "top";
				break;
		}

		return rDir;
	}

	touchEnd = function() {
		if(touch.getIndex() == -1) { 
			touch.clear();
			drag.end();
		}
	};

	touchEndArea = function() {
		if(touch.getIndex() == -1) { 
			touch.clear();
			drag.end();

			if(touch.x == touch.firstX) menu.close();
		}
	};

	initOpenBtns = function() {
		var openBtns = document.getElementsByClassName(userData.swiperOpenBtns);
		for (i = 0; i<openBtns.length; i++) {
			openBtns[i].addEventListener("click", menu.open, false);
		}
	}

	initOpenBtns();

	//swiperOpen.addEventListener("click", menu.open, false);
	swiperClose.addEventListener("click", menu.close, false);

	swipableArea.addEventListener("touchstart", touchStart, false);
	swipableArea.addEventListener("touchmove", touchMove, false);
	swipableArea.addEventListener("touchend", touchEndArea, false);

	swiperSide.addEventListener("touchstart", touchStart, false);
	swiperSide.addEventListener("touchmove", touchMove, false);
	swiperSide.addEventListener("touchend", touchEnd, false);
}