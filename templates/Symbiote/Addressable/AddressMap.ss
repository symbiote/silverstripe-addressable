<% if $Type == "Google" %>
    <div class="addressMap">
        <a href="https://maps.google.com/?q=$Address">
            <img src="https://maps.googleapis.com/maps/api/staticmap?size={$Width}x{$Height}&scale={$Scale}&markers=$Address&key=$Key" alt="$FullAddress.ATT" />
        </a>
    </div>
<% else %>
    <div class="addressMap">
        <img src="https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/geojson(%7B%22type%22%3A%22Point%22%2C%22coordinates%22%3A%5B{$Lng}%2C{$Lat}%5D%7D)/{$Lng},{$Lat},15/{$Width}x{$Height}?access_token={$Key}" alt="$FullAddress.ATT" />
    </div>
<% end_if %>
$Gugus
