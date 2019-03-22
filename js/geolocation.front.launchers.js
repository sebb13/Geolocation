var options = {
	enableHighAccuracy: true,
	timeout: 5000,
	maximumAge: 0
};
function myPosition(position) {
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
	promise.success(function() {
		if(console && console.log) {
			console.log('setPosition ok');
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
	navigator.geolocation.getCurrentPosition(myPosition, error, options);
	$('div#ajaxFrame').on('click', '#locReload', function(){
		navigator.geolocation.getCurrentPosition(myPosition, error, options);
	});
}