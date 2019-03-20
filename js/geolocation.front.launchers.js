function myPosition(position) {
	var promise = genericRequest({
			app_token: getToken(), 
			content: getCurrentPage(),
			exw_action: 'Geolocation::setPosition',
			latitude: position.coords.latitude,
			longitude: position.coords.longitude,
			altitude: position.coords.altitude,
			speed: position.coords.speed
		});
	promise.success(function() {
		if(console && console.log) {
			console.log('setPosition ok');
		};
	});
}
if(navigator.geolocation) {
	navigator.geolocation.getCurrentPosition(myPosition);
	$('meta[name=app_current_page]').change(function(){
		navigator.geolocation.getCurrentPosition(myPosition);
	});
	$('div#ajaxFrame').on('click', '#locReload', function(){
		navigator.geolocation.getCurrentPosition(myPosition);
	});
}