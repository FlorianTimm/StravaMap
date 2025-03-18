var vectorLayer = null;
var map = new ol.Map({
    target: 'map',
    layers: [
        new ol.layer.Tile({
            source: new ol.source.OSM(),
            opacity: 0.6
        })
    ],
    view: new ol.View({
        center: ol.proj.fromLonLat([10, 53.8]),
        zoom: 6
    })
});

function daten_laden() {
    document.getElementById("laden").disabled = true;
    document.body.className = 'wait';
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("login").style.display = 'none';

            load_geo();
        }
    };
    xhttp.open("GET", "load.php", true);
    xhttp.send();
}

function styleFilter(feature) {
    let von = document.getElementById("von").value;
    let bis = document.getElementById("bis").value;
    let ride = document.getElementById("ride").checked;
    let walk = document.getElementById("walk").checked;
    let hike = document.getElementById("hike").checked;
    let run = document.getElementById("run").checked;
    let other = document.getElementById("other").checked;
    let athlete_count = document.getElementById("athlete_count").checked;

    console.log(von);

    if (!ride && feature.get('type') == 'Ride') return false;
    if (!walk && feature.get('type') == 'Walk') return false;
    if (!hike && feature.get('type') == 'Hike') return false;
    if (!run && feature.get('type') == 'Run') return false;
    if (!other && feature.get('type') != 'Ride' && feature.get('type') != 'Walk' && feature.get('type') != 'Hike' && feature.get('type') != 'Run') return false;
    if (von && feature.get('starttime') < von) return false;
    if (bis && feature.get('starttime') > bis) return false;

    if (athlete_count && feature.get('athlete_count') < 2) return false;

    return true;
}

function filter() {
    if (vectorLayer)
        vectorLayer.getSource().changed();
}

function load_geo() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var vectorSource = new ol.source.Vector({
                features: (new ol.format.GeoJSON()).readFeatures(this.responseText, {
                    dataProjection: 'EPSG:4326',
                    featureProjection: 'EPSG:3857'
                })
            });

            vectorLayer = new ol.layer.Vector({
                source: vectorSource,
                style: function (feature, resolution) {
                    if (!styleFilter(feature)) {
                        return null;
                    }
                    let typ = feature.get('type');
                    let color = "red";
                    switch (typ) {
                        case "Ride":
                            color = 'red';
                            break;
                        case "Walk":
                            color = 'orange';
                            break;
                        case "Hike":
                            color = 'blue';
                            break;
                        case "Run":
                            color = 'cyan';
                            break;
                        default:
                            color = 'grey';
                    }

                    return new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: color,
                            width: 2
                        })
                    })
                },
            });

            let select = new ol.interaction.Select({
                layers: [vectorLayer],
                hitTolerance: 10
            });
            select.on("select", function (event) {
                if (event.selected.length == 0) {
                    document.getElementById("gfi").innerHTML = "";
                    return;
                }
                let auswahl = event.selected[0];
                let props = auswahl.getProperties();
                console.log(props);
                let info = "<h2>" + props.name + "</h2>";
                if (props.starttime)
                    info += "<p>Datum: " + props.starttime + "</p>";
                if (props.elapsed_time)
                    info += "<p>Dauer: " + new Date(props.elapsed_time * 1000).toISOString().substring(11, 16) + "</p>";
                if (props.athlete_count)
                    info += "<p>Teilnehmer: " + props.athlete_count + "</p>";
                if (props.type)
                    info += "<p>Art: " + props.type + "</p>";
                if (props.distance)
                    info += "<p>Strecke: " + Math.round(props.distance / 1000, 1) + " km</p>";
                if (props.avg_speed)
                    info += "<p>Geschwindigkeit: " + Math.round(props.avg_speed * 3.6, 1) + " km/h</p>";
                if (props.elevation)
                    info += "<p>Anstieg: " + props.elevation + " m</p>";
                if (props.description)
                    info += "<p>" + props.description + "</p>";
                info += "<p><a target='_strava' href='https://www.strava.com/activities/" + auswahl.get("id") + "'>Auf Strava anzeigen</a></p>";
                document.getElementById("gfi").innerHTML = info;
            });
            map.addLayer(vectorLayer);
            map.addInteraction(select);
            document.body.className = "";
            document.getElementById("ausblenden").style.display = "none";
            document.getElementById("login").style.display = "none";
        }
    };
    xhttp.open("GET", "./wfs/?SERVICE=WFS&REQUEST=GetFeature&TYPENAMES=gis:linien&outputFormat=application/json&user=<?php echo $user ?>", true);
    xhttp.send();
}