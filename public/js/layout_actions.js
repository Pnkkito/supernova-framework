sidebarSwitch = function(side,action){
	var q = 130;
	var t = 500;
	switch (action){
		case 'close': var quantity = '-='+q; var spanquantity = '-='+(q-10); break;
		case 'open':  var quantity = '+='+q; var spanquantity = '+='+(q-10); break;
	}
	switch (side){
		case 'sideLeft':
			$('aside[id="' + side + '"]').animate({
				width: quantity,
				}, t);
			$('aside[id="' + side + '"] div').animate({
				width: spanquantity,
				}, t);
			$('#content').animate({
				marginLeft: quantity
			},t);
		break;
	}
}

$(document).ready(function(){
	$('aside').mouseenter(function(){
		sidebarSwitch($(this).attr('id'),'open');
	});
	$('aside').mouseleave(function(){
		sidebarSwitch($(this).attr('id'),'close');
	})
});
