function leafletMapInitialize(map_container_id, map_data, markers) {
    // @TODO: should check for SVG support before proceeding.
    markers = markers ? markers : null;


    var map_js_classname = 'js-map';

    //console.log(map_data);
    //console.log(markers);

    var $mapEl = document.getElementById(map_container_id);
    var map_id = map_container_id + '__map';
    
    var default_icon_colour = 'red';
    var icon_colours = {
        'red':    'rgb(253,117,103)',
        'orange': 'rgb(255,153,0)',
        'yellow': 'rgb(253,245,105)',
        'green':  'rgb(0,230,77)',
        'blue':   'rgb(105,145,253)',
        'purple': 'rgb(142,103,253)',
        'pink':   'rgb(230,97,172)'
    };

    var html_el = document.querySelector('html');
    console.log(html_el);

    // Add the JS class name ...
    if (html_el.classList) {
        html_el.classList.add(map_js_classname);
    } else {
        html_el.className += ' ' + map_js_classname;
    }

    $mapEl.innerHTML = '';

    var map_div = document.createElement('div');
    map_div.id = map_id;
    $mapEl.appendChild(map_div);// ($('<div id="' + map_id + '" class="' + classes + '" />'));

    var map = L.map(map_id, {
        center: [map_data.lat, map_data.lng],
        minZoom: 2,
        zoom: map_data.zoom,
        fullscreenControl: true,
        fullscreenControlOptions: {
            position: 'topleft'
        }
    });

    // Mapbox tiles (https://www.mapbox.com/)
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        maxZoom: 18,
        id: 'mapbox.streets',
        accessToken: map_data.token
    }).addTo(map);

    if (markers) {
        markers.forEach(function(marker, i) {
            if (marker.lat == 'true') {
                marker.lat = map_data.lat;
            }

            if (marker.lng == 'true') {
                marker.lng = map_data.lng;
            }

            var icon_colour = (marker.color == '') ? default_icon_colour : marker.color;
            var svg_marker = new L.Marker.SVGMarker([marker.lat, marker.lng], {
                iconOptions: {
                    fillOpacity: 1,
                    iconSize: [21,32],
                    circleFillColor: 'rgb(0,0,0)',
                    circleWeight: 0,
                    circleRatio: 0.15,
                    weight: 1.5,
                    color: 'rgb(0,0,0)',
                    fillColor: icon_colours[icon_colour]
                }
            });

            if (typeof marker.popup == 'string') {
                // Individual marker popup content;
                svg_marker.bindPopup(marker.popup);
            }/* else if (typeof map_data.popuptemplate == 'string') {
                // Global popup template present, check there's data:
                if (typeof marker.popupdata == 'object') {
                    //console.log( marker_data.popupdata );

                    // Render the template:
                    template = twig({
                        data: map_data.popuptemplate
                    });
                    svg_marker.bindPopup(template.render(marker.popupdata));
                }
            }*/

            svg_marker.addTo(map);
        });
    }

    // For some reason if the map is in a left pane, the centering isn't correct until a resize
    // event is fired. I can't figure out why, so I'm just firing the event manually for now:
    //window.dispatchEvent(new Event('resize'));
    if (typeof (Event) === 'function') {
        // modern browsers
        window.dispatchEvent(new Event('resize'));
    } else {
        //This will be executed on old browsers and especially IE
        var resizeEvent = window.document.createEvent('UIEvents');
        resizeEvent.initUIEvent('resize', true, false, window, 0);
        window.dispatchEvent(resizeEvent);
    }

    return;
}
