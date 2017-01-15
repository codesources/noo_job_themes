
if ( jQuery('#googleMap').length > 0 ) {
    $obj = jQuery('#googleMap');

    var myCenter = new google.maps.LatLng($obj.data('lat'),$obj.data('lon'));
    var myMaker = new google.maps.LatLng($obj.data('lat'),$obj.data('lon'));

    function initialize() {
        
        var mapProp = {
            center: myCenter,
            zoom: 14,
            scrollwheel: false,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
            }
        };
        var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
        var marker = new google.maps.Marker({
            position: myMaker,
            icon: $obj.data('icon'),
            animation: google.maps.Animation.DROP,
        });
        marker.setMap(map);

        var infoBox = new google.maps.InfoWindow();
        infoBox.setContent(
            '<p><strong> ' + $obj.data('address') + '</strong></p>'
        );
        marker.addListener('click', function() {
            infoBox.open(map, marker);
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);

}