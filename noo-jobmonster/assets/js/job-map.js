var infoBox;
(function($){
	"use strict";
	function isTouch(){
		return !!('ontouchstart' in window) || ( !! ('onmsgesturechange' in window) && !! window.navigator.maxTouchPoints);
	}
	var map_style_dark=[{featureType:"all",elementType:"labels.text.fill",stylers:[{saturation:36},{color:"#000000"},{lightness:40}]},{featureType:"all",elementType:"labels.text.stroke",stylers:[{visibility:"on"},{color:"#000000"},{lightness:16}]},{featureType:"all",elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"administrative",elementType:"all",stylers:[{lightness:"-1"}]},{featureType:"administrative",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"administrative",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:17},{weight:1.2}]},{featureType:"administrative.country",elementType:"all",stylers:[{lightness:"20"}]},{featureType:"administrative.country",elementType:"geometry.stroke",stylers:[{visibility:"on"},{color:nooJobGmapL10n.primary_color}]},{featureType:"administrative.country",elementType:"labels.text",stylers:[{color:nooJobGmapL10n.primary_color},{visibility:"simplified"}]},{featureType:"administrative.country",elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"administrative.province",elementType:"all",stylers:[{lightness:"20"}]},{featureType:"administrative.province",elementType:"labels.text",stylers:[{color:nooJobGmapL10n.primary_color},{visibility:"off"}]},{featureType:"administrative.locality",elementType:"all",stylers:[{lightness:"0"},{color:nooJobGmapL10n.primary_color},{saturation:"9"},{visibility:"simplified"}]},{featureType:"landscape",elementType:"geometry",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"poi",elementType:"geometry",stylers:[{color:"#000000"},{lightness:21}]},{featureType:"poi",elementType:"geometry.fill",stylers:[{color:"#3e3e3e"}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{color:nooJobGmapL10n.primary_color},{lightness:29},{weight:.2}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{color:"#000000"},{lightness:18}]},{featureType:"road.local",elementType:"geometry",stylers:[{color:"#000000"},{lightness:16}]},{featureType:"transit",elementType:"geometry",stylers:[{color:"#000000"},{lightness:19}]},{featureType:"water",elementType:"all",stylers:[{visibility:"simplified"},{lightness:"-62"}]},{featureType:"water",elementType:"geometry",stylers:[{color:"#13232a"},{lightness:17}]}];
	var map_style_light=[{featureType:"administrative",elementType:"labels.text.fill",stylers:[{color:"#444444"}]},{featureType:"landscape",elementType:"all",stylers:[{color:"#f2f2f2"}]},{featureType:"poi",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"poi.park",elementType:"all",stylers:[{visibility:"on"},{color:"#bcd9c3"}]},{featureType:"road",elementType:"all",stylers:[{saturation:-100},{lightness:45}]},{featureType:"road.highway",elementType:"all",stylers:[{visibility:"simplified"}]},{featureType:"road.arterial",elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"transit",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"transit.station",elementType:"all",stylers:[{visibility:"off"},{weight:"0.28"}]},{featureType:"transit.station",elementType:"labels.text",stylers:[{color:"#555555"}]},{featureType:"transit.station",elementType:"labels.icon",stylers:[{saturation:"-66"}]},{featureType:"transit.station.rail",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"water",elementType:"all",stylers:[{color:"#d5def2"},{visibility:"on"}]},{featureType:"water",elementType:"labels.text.fill",stylers:[{color:"#ffffff"}]},{featureType:"water",elementType:"labels.text.stroke",stylers:[{visibility:"on"}]},{featureType:"administrative.country",elementType:"geometry.stroke",stylers:[{visibility:"on"},{color:nooJobGmapL10n.primary_color}]},{featureType:"administrative.country",elementType:"labels.text",stylers:[{color:nooJobGmapL10n.primary_color},{visibility:"simplified"}]},{featureType:"administrative.province",elementType:"labels.text",stylers:[{color:nooJobGmapL10n.primary_color},{visibility:"off"}]},{featureType:"administrative.locality",elementType:"all",stylers:[{lightness:"0"},{color:nooJobGmapL10n.primary_color},{saturation:"9"},{visibility:"simplified"}]}];
	var map_style_apple=[{featureType:"landscape.man_made",elementType:"geometry",stylers:[{color:"#f7f1df"}]},{featureType:"landscape.natural",elementType:"geometry",stylers:[{color:"#d0e3b4"}]},{featureType:"landscape.natural.terrain",elementType:"geometry",stylers:[{visibility:"off"}]},{featureType:"poi",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"poi.business",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"poi.medical",elementType:"geometry",stylers:[{color:"#fbd3da"}]},{featureType:"poi.park",elementType:"geometry",stylers:[{color:"#bde6ab"}]},{featureType:"road",elementType:"geometry.stroke",stylers:[{visibility:"off"}]},{featureType:"road",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{color:"#ffe15f"}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{color:"#efd151"}]},{featureType:"road.arterial",elementType:"geometry.fill",stylers:[{color:"#ffffff"}]},{featureType:"road.local",elementType:"geometry.fill",stylers:[{color:"black"}]},{featureType:"transit.station.airport",elementType:"geometry.fill",stylers:[{color:"#cfb2db"}]},{featureType:"water",elementType:"geometry",stylers:[{color:"#a2daf2"}]}];
	var map_style_nature=[{featureType:"landscape",stylers:[{hue:"#FFA800"},{saturation:0},{lightness:0},{gamma:1}]},{featureType:"road.highway",stylers:[{hue:"#53FF00"},{saturation:-73},{lightness:40},{gamma:1}]},{featureType:"road.arterial",stylers:[{hue:"#FBFF00"},{saturation:0},{lightness:0},{gamma:1}]},{featureType:"road.local",stylers:[{hue:"#00FFFD"},{saturation:0},{lightness:30},{gamma:1}]},{featureType:"water",stylers:[{hue:"#00BFFF"},{saturation:6},{lightness:8},{gamma:1}]},{featureType:"poi",stylers:[{hue:"#679714"},{saturation:33.4},{lightness:-25.4},{gamma:1}]}];
	function noo_job_map_initialize(){
		var mapSearchBox = $('.noo-job-map');
		var mapBox = mapSearchBox.find('#gmap'),
			latitude = mapBox.attr('data-latitude') ? mapBox.attr('data-latitude') : nooJobGmapL10n.latitude,
			longitude = mapBox.attr('data-longitude') ? mapBox.attr('data-longitude') : nooJobGmapL10n.longitude,
			zoom = mapBox.attr('data-zoom') ? mapBox.attr('data-zoom') : nooJobGmapL10n.zoom,
			fit_bounds = mapBox.attr('data-fit_bounds') ? ( mapBox.attr('data-fit_bounds') == 'yes' ) : true;
		var myPlace    = new google.maps.LatLng( parseFloat( latitude ), parseFloat( longitude ) );
		var style = mapBox.attr('data-map_style') ? mapBox.attr('data-map_style') : '';
		var map_style = map_style_dark;
		switch (style) {
			case "none":
				map_style = [];
				break;
			case "light":
				map_style = map_style_light;
				break;
			case "apple":
				map_style = map_style_apple;
				break;
			case "nature":
				map_style = map_style_nature;
				break;
		}
		var myOptions = {
			    flat:false,
			    noClear:false,
			    zoom: parseInt(zoom),
			    scrollwheel: false,
			    streetViewControl:false,
			    disableDefaultUI: false,
			    scaleControl:false,
			    navigationControl:false,
			    mapTypeControl:false,
			    draggable: !isTouch(),
			    center: myPlace,
			    mapTypeId: google.maps.MapTypeId.ROADMAP,
			    styles : map_style
		};
		var gmarkers = [],
			map = new google.maps.Map(mapBox.get(0),myOptions );
			google.maps.visualRefresh = true;
			
		google.maps.event.addListener(map, 'tilesloaded', function() {
			mapSearchBox.find('.gmap-loading').hide();
		});

		var input = new google.maps.places.Autocomplete($("#map-location-search")[0]);
		input.bindTo("bounds", map);
		google.maps.event.addListener(input, "place_changed", function() {
			var place = input.getPlace();
			if( place.geometry ) {
				if( place.geometry.viewport )
					map.fitBounds(place.geometry.viewport);
				else 
					map.setCenter(place.geometry.location);
			}
		});

		var infoboxOptions = {
                content: document.createElement("div"),
                disableAutoPan: true,
                maxWidth: 500,
                boxClass:"myinfobox",
                zIndex: null,			
                closeBoxMargin: "-13px 0px 0px 0px",
                closeBoxURL: "",
                infoBoxClearance: new google.maps.Size(1, 1),
                isHidden: false,
                pane: "floatPane",
                enableEventPropagation: false                   
        };               
		infoBox = new InfoBox(infoboxOptions);
		
		var clickMarkerListener = function(marker){
			var infoContent = '<div class="gmap-infobox"><a class="info-close" onclick="return infoBox.close();" href="javascript:void(0)">x</a>\
				<div class="loop-item-wrap"> \
					<div class="item-featured"><a href="' + marker.url + '">' + marker.image + '</a></div> \
					<div class="loop-item-content"> \
					 	<h4 class="loop-item-title"><a href="' + marker.url + '">' + marker.title + '</a></h4>';
					 	

			if( marker.company_url != '' || marker.type != '' ) {
				infoContent += '<p class="content-meta">';
					 		
				if( marker.company_url != '' ) {
					infoContent += '<span class="job-company"> <a href="' + marker.company_url + '">' + marker.company + '</a></span>';
				}
				if( marker.type != '' ) {
					infoContent += '<span class="job-type"> <a href="' + marker.type_url + '" style="color: ' + marker.type_color + '"><i class="fa fa-bookmark"></i>' + marker.type + '</a></span>';
				}

				infoContent += '</p>';
			}
			infoContent += '</div></div>';

			infoBox.setContent(infoContent);
			infoBox.open(map,marker);

			map.setCenter(marker.position); 
			map.panBy(50,-120);
		};
			
		var markers = $.parseJSON(nooJobGmapL10n.marker_data);
		if(markers.length){
			var bounds = new google.maps.LatLngBounds();
			for(var i = 0; i < markers.length ; i ++){
				var marker = markers[i];
				var markerPlace = new google.maps.LatLng(marker.latitude,marker.longitude);
				var gmarker = new google.maps.Marker({
					position: markerPlace,
					map: map,
					title: marker.title,
					url: marker.url,
					image: marker.image,
					type: marker.type,
					type_url: marker.type_url,
					type_color: marker.type_color,
					company: marker.company,
					company_url: marker.company_url,
					term_url: marker.term_url,
					icon: nooJobGmapL10n.theme_uri + '/assets/images/map-marker.png'
				});
				gmarkers.push(gmarker);
				bounds.extend( gmarker.getPosition() );
				google.maps.event.addListener(gmarker, 'click', function(e) {
					clickMarkerListener(this);
				});
			}

			if( gmarkers.length > 0 && fit_bounds ) {
				map.fitBounds(bounds);
			}
		 }
		
		var clusterStyles = [{
				textColor: '#ffffff',    
				opt_textColor: '#ffffff',
				url: nooJobGmapL10n.theme_uri + '/assets/images/cloud.png',
				height: 72,
				width: 72,
				textSize:15
			}
		];
		var mcluster = new MarkerClusterer(map, gmarkers,{
			gridSize: 50,
			ignoreHidden:true, 
			styles: clusterStyles
		});
		mcluster.setIgnoreHidden(true);
	 }
	google.maps.event.addDomListener(window, 'load', noo_job_map_initialize);
})(jQuery);