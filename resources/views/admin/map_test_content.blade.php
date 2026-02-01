{{--<div style="width: 100%; height: 600px;" id="mapContainer"></div>--}}
<div style="width: 100%; height: 600px;" id="mapContainer"></div>
<script>

    function moveMapToDnipro(map){
        map.setCenter({lat: 48.468538, lng: 35.0277714});
        map.setZoom(12);
    }

    function switchMapLanguage(map, platform){
        // Create default layers
        let defaultLayers = platform.createDefaultLayers({
            lg: 'ru'
        });
        // Set the normal map variant of the vector map type
        map.setBaseLayer(defaultLayers.vector.normal.map);

        // Display default UI components on the map and change default
        // language to simplified Chinese.
        // Besides supported language codes you can also specify your custom translation
        // using H.ui.i18n.Localization.
        var ui = H.ui.UI.createDefault(map, defaultLayers, 'ru-RU');

        // Remove not needed settings control
        ui.removeControl('mapsettings');
    }

    function addMarkerToGroup(group, coordinate, html) {
        var marker = new H.map.Marker(coordinate);

        // add custom data to the marker
        marker.setData(html);
        group.addObject(marker);
    }

    function addInfoBubble(map) {
        var group = new H.map.Group();

        map.addObject(group);

        // add 'tap' event listener, that opens info bubble, to the group
        group.addEventListener('tap', function (evt) {
            // event target is the marker itself, group is a parent event target
            // for all objects that it contains
            var bubble = new H.ui.InfoBubble(evt.target.getGeometry(), {
                // read custom data
                content: evt.target.getData()
            });
            // show info bubble
            ui.addBubble(bubble);
        }, false);

        map.addLayer(layer);

        addMarkerToGroup(group, {lat:48.468538, lng:35.0277714}, 'Manchester City' + 'City of Manchester Stadium Capacity: 48,000');
        addMarkerToGroup(group, {lat:48.468528, lng:35.0277724}, 'Liverpool' + 'Anfield Capacity: 45,362');

    }

    // Initialize the platform object:
    var platform = new H.service.Platform({
        'apikey': 'YvFl8GxhPSHuC5wPUnBODuy-8L6F6YBGcdjx8uSdYAE'
    });

    // Obtain the default map types from the platform object
    var defaultLayers = platform.createDefaultLayers();

    var map = new H.Map(
        document.getElementById('mapContainer'),
        defaultLayers.vector.normal.map,
        {
            zoom: 6,
            center: { lat: 48.468538, lng: 35.0277714 },
            pixelRatio: window.devicePixelRatio || 1
        });

    // add a resize listener to make sure that the map occupies the whole container
    window.addEventListener('resize', () => map.getViewPort().resize());

    //Step 3: make the map interactive
    // MapEvents enables the event system
    // Behavior implements default interactions for pan/zoom (also on mobile touch environments)
    var behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));

    // Create the default UI components
    var ui = H.ui.UI.createDefault(map, defaultLayers);

    var dataId = [];
    var dataPoints = [];
    var dataTexts = [];

    @foreach($orders as $order)

        var html = 'Order id: {{ $order->id . '; Адрес: ' . $order->addr }}';
        var infotext = new H.clustering.DataPoint({{ $order->lat }}, {{ $order->lon }}, null, html);
        // info.setData();
        dataPoints.push(infotext);

        // [1][2][1][3][3][0][0][2].entries
        // [1][0][0][0][3][2][2][3].entries
        // [1][2][1][3][3][0][1][3].entries
        // [0][1][0][0][3][2][1][2][3][1].entries
        // [1][2][0].entries
        // [1][0][2][0][2][3][1][3][2][1][3][2][1][3][2][2][2][2].entries

        {{--dataId.push({{ $order->id }});--}}
        {{--dataPoints[{{ $order->id }}] = new H.clustering.DataPoint({{ $order->lat }}, {{ $order->lon }});--}}
        {{--dataTexts[{{ $order->id }}] = '{{ $order->addr }}';--}}
    @endforeach
    var clusteredDataProvider = new H.clustering.Provider(dataPoints);
    var layer = new H.map.layer.ObjectLayer(clusteredDataProvider);
    map.addLayer(layer);

    // Add an event listener to the Provider - this listener is called when a maker
    // has been tapped:
    clusteredDataProvider.addEventListener('tap', function(event) {
        // Log data bound to the marker that has been tapped:

        var data = Object.values(event.target.getData());
        // var data2 = event.target.getData();
        console.log(Object.keys(data));

        var descr = data[1]['data'];

        var i1 = 0;
        var i2 = 0;
        var i3 = 0;
        var i4 = 0;
        var i5 = 0;
        var i6 = 0;
        var i7 = 0;
        var i8 = 0;
        var i9 = 0;
        data.forEach(function(item, index, array) {
            i1++;
            if (i1 == 2) {
                let data2 = Object.values(array[index]);
                data2.forEach(function(item2, index2, array2) {
                    i2++;
                    if (i2 == 1) {
                        let data3 = Object.values(array2[index2]);
                        data3.forEach(function(item3, index3, array3) {
                            i3++;
                            if (i3 == 1) {
                                let data4 = Object.values(array3[index3]);
                                data4.forEach(function(item4, index4, array4) {
                                    i4++;
                                    if (i4 == 1) {
                                        let data5 = Object.values(array4[index4]);
                                        data5.forEach(function(item5, index5, array5) {
                                            i5++;
                                            if (i5 == 1) {
                                                let data6 = Object.values(array5[index5]);
                                                data6.forEach(function(item6, index6, array6) {
                                                    i6++;
                                                    if (i6 == 1) {
                                                        let data7 = Object.values(array6[index6]);
                                                        var d7 = data7['entries'][0] != 'undefined' && data7['entries'][0] != null && data7['entries'][0] != '' ? Object.values(data7['entries'][0]) : '';
                                                        console.log('data7: '+ d7);
                                                        data7.forEach(function(item7, index7, array7) {
                                                            i7++;
                                                            if (i7 == 1) {
                                                                let data8 = Object.values(array7[index7]);
                                                                var d8 = data8['entries'][0] != 'undefined' && data8['entries'][0] != null && data8['entries'][0] != '' ? Object.values(data8['entries'][0]) : '';
                                                                console.log('data8: '+ d8);
                                                                data8.forEach(function(item8, index8, array8) {
                                                                    i8++;
                                                                    console.log('index8: '+item8);
                                                                    if (i8 == 1) {
                                                                        let data9 = Object.values(array8[index8]);
                                                                        var d9 = data9 != 'undefined' && data9 != null && data9 != '' ? Object.values(data9) : '';
                                                                        console.log('data9: '+ d9);
                                                                        data9.forEach(function(item9, index9, array9) {
                                                                            i9++;
                                                                            if (i9 == 9) {
                                                                                var d10 = item9 != 'undefined' && item9 != null && item9 != '' ? Object.values(item9) : '';
                                                                                d10.forEach(function(item10, index10, array10) {

                                                                                    console.log('d10 item10: '+Object.values(item10));
                                                                                });
                                                                            }
                                                                            console.log('index9: '+item9);
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
        // console.log(data[1]['a'][0]['data']);

        // for (let [key, value] of Object.entries(data2)) {
        //     console.log(`${key}: ${value}`);
        // }
        console.log(data);
        // console.log(dataId[id]);

        if (descr != 'undefined' && descr != null && descr != '') {

            var bubble = new H.ui.InfoBubble(event.target.getGeometry(), {
                // read custom data
                content: descr
            });
            // show info bubble
            ui.addBubble(bubble);

        }

    });

    // Now use the map as required...
    window.onload = function () {
        moveMapToDnipro(map);
    }

    // Create an info bubble object at a specific geographic location:

    switchMapLanguage(map, platform);

    // addInfoBubble(map);

</script>
