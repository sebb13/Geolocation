var options = {
  enableHighAccuracy: true,
  timeout: 5000,
  maximumAge: 0
};
function myPosition(position) {
	$('#LATITUDE').val(position.coords.latitude);
	$('#LONGITUDE').val(position.coords.longitude);
	console.log($('#LATITUDE').val() + ' : ' + $('#LONGITUDE').val());
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