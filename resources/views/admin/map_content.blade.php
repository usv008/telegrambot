<div id="map" class="shadow rounded" style="width: 100%; height: 600px;"></div>
<div id="legend"></div>

<script>


    function initMap() {

        // var map;
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: {lat: 48.468538, lng: 35.0277714}
        });

        var contentString = {
            komsa: { content: '<h4>ECO&PIZZA - Комса</h4><p>ул. Старокозацкая, 66 А (бывшая ул. Комсомольская), в районе военного госпиталя</p><p><a href="https://ecopizza.com.ua/" target="_blank">ССЫЛКА</a></p>' },
            palermo: { content: '<h4>ECO&PIZZA - Палермо</h4><p>пр. Слобожанский, 14 А (бывший пр. имени Газеты Правды)</p><p><a href="https://ecopizza.com.ua/" target="_blank">ССЫЛКА</a></p>' }
            {{ \App\Http\Controllers\MapController::contentStringAdd($res5_2, $gps5_2) }}
            {{ \App\Http\Controllers\MapController::contentStringAdd($res5_4, $gps5_4) }}
            {{ \App\Http\Controllers\MapController::contentStringAdd($res10_2, $gps10_2) }}
            {{ \App\Http\Controllers\MapController::contentStringAdd($res10_4, $gps10_4) }}
        };

        var iconBase = 'https://telegrambot.ecopizza.com.ua/assets/img/';
        var iconBaseGoogle = 'http://maps.google.com/mapfiles/ms/icons/';

        var icons = {
            komsa: {
                name: 'К',
                icon: iconBase + 'logo.png'
            },
            palermo: {
                name: 'П',
                icon: iconBase + 'logo.png'
            },
            komsa_2: {
                name: 'К П',
                icon: iconBaseGoogle + 'red-dot.png'
            },
            komsa_4: {
                name: 'К Р',
                icon: iconBaseGoogle + 'blue-dot.png'
            },
            palermo_2: {
                name: 'П П',
                icon: iconBaseGoogle + 'yellow-dot.png'
            },
            palermo_4: {
                name: 'П Р',
                icon: iconBaseGoogle + 'green-dot.png'
            }
        };

        var features = [
            { position: new google.maps.LatLng(48.468538, 35.027771), id: 'komsa', type: 'komsa', title: 'Комса' },
            { position: new google.maps.LatLng(48.4914503, 35.0677432), id: 'palermo', type: 'palermo', title: 'Комса' }
            {{ \App\Http\Controllers\MapController::featuresAdd('komsa', 2, $res5_2, $gps5_2) }}
            {{ \App\Http\Controllers\MapController::featuresAdd('komsa', 4, $res5_4, $gps5_4) }}
            {{ \App\Http\Controllers\MapController::featuresAdd('palermo', 2, $res10_2, $gps10_2 ) }}
            {{ \App\Http\Controllers\MapController::featuresAdd('palermo', 4, $res10_4, $gps10_4) }}

        ];

        // Create markers.
        features.forEach(function(feature) {
            // var iconType = feature.type;
            // if (feature.type = 'komsa') markerAnimation = google.maps.Animation.BOUNCE;

            var marker = new google.maps.Marker({
                position: feature.position,
                icon: icons[feature.type].icon,
                // animation: google.maps.Animation.DROP,
                title: feature.title,
                map: map
            });
            if (feature.type == 'komsa_2' || feature.type == 'palermo_2') marker.setAnimation(google.maps.Animation.BOUNCE);
            var infowindow = new google.maps.InfoWindow({
                content: contentString[feature.id].content
            });
            marker.addListener('click', function() {
                infowindow.open(map, marker);
                marker.setAnimation(null);
            });
            // infowindow.addListener(infowindow,'closeclick',function(){
            //     if (feature.type == 'komsa_2' || feature.type == 'palermo_4') marker.setAnimation(google.maps.Animation.BOUNCE);
            // });
        });

        var legend = document.getElementById('legend');
        for (var key in icons) {
            var type = icons[key];
            var name = type.name;
            var icon = type.icon;
            var div = document.createElement('div');
            if (name !== 'К' && name !== 'П') div.innerHTML = '<img src="' + icon + '"> ' + name;
            legend.appendChild(div);
        }

        map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);

    }

</script>

<script async defer
{{--        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8&callback=initMap">--}}
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAv5qq13I_3XwuZY8m5Q3GXju-cI0KU5gQ&callback=initMap">
</script>


<div id="mapdiv"></div>
<script src="http://www.openlayers.org/api/OpenLayers.js"></script>
<script>
    map = new OpenLayers.Map("mapdiv");
    map.addLayer(new OpenLayers.Layer.OSM());

    var pois = new OpenLayers.Layer.Text( "My Points",
        { location:"./textfile.txt",
            projection: map.displayProjection
        });
    map.addLayer(pois);
    // create layer switcher widget in top right corner of map.
    var layer_switcher= new OpenLayers.Control.LayerSwitcher({});
    map.addControl(layer_switcher);
    //Set start centrepoint and zoom
    var lonLat = new OpenLayers.LonLat( 9.5788, 48.9773 )
        .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
        );
    var zoom=11;
    map.setCenter (lonLat, zoom);

</script>
