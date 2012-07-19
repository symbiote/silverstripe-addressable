<% if ShowGoogleMap %>
	<div id="GoogleMapsAddressMap" style="width: {$MapWidth}px; height: {$MapHeight}px;"></div>
	<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>
	<script type="text/javascript">
		function initialize() {
			
			// Create LatLng for address
			var latlng = new google.maps.LatLng($Lat, $Lng);
			
			// Setup map options
			var myOptions = {
				zoom: $MapZoom,
				center: latlng,
				panControl: $MapPanControl,
				zoomControl: $MapZoomControl,
				zoomControlOptions: {
					style: google.maps.ZoomControlStyle.$MapZoomStyle
				},
				panControl: $MapPanControl,
				mapTypeControl: $MapTypeControl,
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.$MapTypeStyle
				},
				mapTypeId: google.maps.MapTypeId.$MapTypeId,
				scaleControl: $MapScaleControl,
				streetViewControl: $MapStreetViewControl,
				overviewMapControl: $MapOverviewMapControl
			};
			
			// Create map
			var map = new google.maps.Map(document.getElementById("GoogleMapsAddressMap"), myOptions);
			
			// add address marker
			var marker = new google.maps.Marker({
				position: latlng,
				map: map,
				title: "$CompanyName"
			});
		}
		initialize();
	</script>
<% end_if %>

<% if ShowRoutingLink %>
	<p><a href="$GoogleRoutingLink"><% _t('GoogleMapsAddressMap.ss.CALCULATEROUTE', 'Get routing information') %></a></p>
<% end_if %>