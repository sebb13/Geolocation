var options = {
  enableHighAccuracy: true,
  timeout: 5000,
  maximumAge: 0
};
function myPosition(position) {
	$('#LATITUDE').val(position.coords.latitude);
	$('#LONGITUDE').val(position.coords.longitude);
	console.log(position.coords.accuracy);
	var promise = genericRequest({
			app_token: getToken(), 
			content: getCurrentPage(),
			exw_action: 'Geolocation::setPosition',
			latitude: position.coords.latitude,
			longitude: position.coords.longitude,
			altitude: position.coords.altitude,
			speed: position.coords.speed,
			accuracy: position.coords.accuracy
		});
	promise.success(function(data) {
		if(console && console.log) {
			console.log(data);
		}
	});
	promise.error(function() {
		if(console && console.log) {
			console.log('setPosition fail');
		}
	});
}
function error(err) {
	if(console && console.log) {
		console.log("ERREUR "+err.code+": "+err.message);
	}
}
if(navigator.geolocation) {
	$('div#ajaxFrame').on('click', '#locateMeButton', function(e){
		e.preventDefault();
		e.stopPropagation();
		navigator.geolocation.getCurrentPosition(myPosition, error, options);
	});
}