<div class="vcard">
	<h2 class="visuallyhidden"><% _t('CustomSiteConfig.ADDRESSHEADER', 'Address') %></h2>
	<p>
		<% if CompanyName %><strong class="fn org">$CompanyName</strong><br /><% end_if %>
		<span class="adr">
			<% if Address %><span class="street-address">$Address</span><br /><% end_if %>
			<% if Postcode %><span class="postal-code">$Postcode</span> <% end_if %><% if Suburb || State %><span class="locality">$Suburb<% if State %>, $State<% end_if %></span><br /><% end_if %>
			<% if Country %><span class="country">$CountryName</span><% end_if %>
		</span><br />
		<% if Telephone %><span class="adresse-eingerueckt"><% _t('Addressable.TELEPHONE', 'Telephone') %>: </span><span class="tel"><span class="work">$Telephone</span></span><br /><% end_if %>
		<% if Fax %><span class="adresse-eingerueckt"><% _t('Addressable.FAX', 'Fax') %>: </span><span class="tel"><span class="fax">$Fax</span></span><br /><% end_if %>
		<% if Email %><span class="adresse-eingerueckt"><% _t('Addressable.EMAIL', 'Email') %>: </span><a class="email" href="mailto:$Email" title="<% _t('Addressable.EMAIL', 'Email') %>">$Email</a><% end_if %>
	</p>
</div>

<div id="dynamicAddressMap" style="width: {$MapWidth}px; height: {$MapHeight}px;"></div>

<p><a href="$GoogleRoutingLink" class="button externer_link"><% _t('DynamicAddressMap.ss.CALCULATEROUTE', 'Get routing information') %></a></p>

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
		var map = new google.maps.Map(document.getElementById("dynamicAddressMap"), myOptions);
		
		// add address marker
		var marker = new google.maps.Marker({
			position: latlng,
			map: map,
			title: "$CompanyName"
		});
	}
	initialize();
</script>
