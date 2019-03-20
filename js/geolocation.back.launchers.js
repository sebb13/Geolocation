if(navigator.geolocation) {
	$('div#ajaxFrame').on('click', '#locateMeButton', function(e){
		e.preventDefault();
		e.stopPropagation();
		navigator.geolocation.getCurrentPosition(function(position) {
			$('#LATITUDE').val(position.coords.latitude);
			$('#LONGITUDE').val(position.coords.longitude);
		});
	});
}