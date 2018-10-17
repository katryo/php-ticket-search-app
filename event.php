
<?php
include 'geoHash.php';

function do_it($something){
    return $something . "abc";
}

function fetch_location($address) {
    $app_key = getenv('G_MAP_API_KEY');
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $app_key;
    $response = file_get_contents($url);
    return $response;
}

function search_events($geo_point, $keyword, $segment, $distance) {
    $consumer_key = getenv('TICKET_CONSUMER_KEY');
    $segment_id = '';

    if ($segment == 'default') {
        $url = 'https://app.ticketmaster.com/discovery/v2/events.json?apikey='
            . $consumer_key
            . '&keyword='
            . urlencode($keyword)
            . '&radius='
            . $distance
            . '&unit=miles&geoPoint='
            . $geo_point;
    } else {
        switch ($segment) {

            case 'music':
                $segment_id = 'KZFzniwnSyZfZ7v7nJ';
                break;

            case 'sports':
                $segment_id = 'KZFzniwnSyZfZ7v7nE';
                break;

            case 'arts':
                $segment_id = 'KZFzniwnSyZfZ7v7na';
                break;

            case 'film':
                $segment_id = 'KZFzniwnSyZfZ7v7nn';
                break;

            case 'miscellaneous':
                $segment_id = 'KZFzniwnSyZfZ7v7n1';
                break;
        }

        $url = 'https://app.ticketmaster.com/discovery/v2/events.json?apikey='
            . $consumer_key
            . '&keyword='
            . urlencode($keyword)
            . '&segmentId='
            . $segment_id
            . '&radius='
            . $distance
            . '&unit=miles&geoPoint='
            . $geo_point;
    }
    $response = file_get_contents($url);
    return $response;
}

?>

<html>
<head>
    <title>Events Search</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body {
            margin: auto;
            width: 800px;
        }

        .event-search {
            margin-top: 40px;
            background: #fafafa;
            border-style: solid;
            border-color: #eee;
            padding: 5px;
        }

        .event-search-heading {
            text-align: center;
            font-style: italic;
        }

        .location-radio {
            margin-left: 290px;
        }

        .buttons {
            margin-left: 120px;
        }

        .icon {
            width: 100px;
        }
        
        label {
            font-weight: bold;
        }
    </style>
    <script>
        'use strict';

        function handleThere() {
            const input = window.document.getElementById('js-input-location');
            input.required = true;
            input.disabled = false;
        }

        function handleHere() {
            const input = window.document.getElementById('js-input-location');
            input.required = false;
            input.disabled = true;
        }

        function handleClear() {
            window.document.getElementById('js-input-keyword').value = '';
            window.document.getElementById('category').value = 'default';
            window.document.getElementById('js-input-distance').value = '10';
            window.document.getElementById('js-input-from-here').checked = true;
            window.document.getElementById('js-input-from-there').checked = false;
            window.document.getElementById('js-input-location').value = '';
            window.document.getElementById('js-input-location').required = false;
            window.document.getElementById('js-input-location').disabled = false;
        }

        function loadJson(url) {
            const xmlHttp = new XMLHttpRequest();
            xmlHttp.open('GET', url, false);
            xmlHttp.overrideMimeType('application/json');
            xmlHttp.send();
            const jsonDoc = xmlHttp.responseText;
            return jsonDoc;
        }

        function fetchLocation() {
            let jsonVal;
            try {
                const jsonDoc = loadJson('http://ip-api.com/json');
                jsonVal = JSON.parse(jsonDoc);
            } catch(err) {
                return [0.0, 0.0];
            }
            return [jsonVal.lat, jsonVal.lon];
        }

        function startLocating() {
            const loc = fetchLocation();
            console.log(loc);
            window.document.getElementById('js-this-lat').value = loc[0];
            window.document.getElementById('js-this-lon').value = loc[1];
            window.document.getElementById('js-submit').disabled = false;
        }

        function getEventsOnPage() {
            const eventsStr = document.getElementById('js-events-response').innerText;
            if (eventsStr) {
                return JSON.parse(eventsStr)._embedded.events;
            } else {
                return [];
            }
        }

        function generateTr(event) {
            const tr = document.createElement('tr');

            const localDate = event.dates.start.localDate;
            const localTime = event.dates.start.localTime;
            const tdDate = document.createElement('td');
            tdDate.innerText = localDate + ' ' + localTime;
            tr.appendChild(tdDate);

            const images = event.images;
            const tdImage = document.createElement('td');
            if (images.length > 0) {
                const iconUrl = event.images[0].url;
                const image = document.createElement('img');
                image.src = iconUrl;
                image.class = 'icon';
                tdImage.appendChild(image);
            } else {
                tdImage.innerText = 'N/A';
            }
            tr.appendChild(tdImage);

            const tdName = document.createElement('td');
            tdName.innerText = event.name;
            tr.appendChild(tdName);

            return tr;
        }

        function renderEvents(events) {
            const table = document.createElement('table');

            for (let headName of ['Date', 'Icon', 'Event', 'Genre', 'Venue']) {
                const th = document.createElement('th');
                th.innerText = headName;
                table.appendChild(th);
            }

            for (let event of events) {
                console.log(event);
                const tr = generateTr(event);
                table.appendChild(tr);
            }
            document.getElementById('search-results').appendChild(table);
        }

        window.onload = function() {
            startLocating();
            const events = getEventsOnPage();
            if (events.length > 0) {
                renderEvents(events);
            }
        }

    </script>
</head>
<body>
<div class="event-search">
    <h1 class="event-search-heading">Events Search</h1>
    <hr>
    <form action="#" method="get">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" name="keyword" id="js-input-keyword" required>
        </div>

        <div>
            <label for="category">Category</label>
            <select name="category" id="category">
                <option value="default">Default</option>
                <option value="music">Music</option>
                <option value="sports">Sports</option>
                <option value="arts">Arts</option>
                <option value="theatre">Theatre</option>
                <option value="film">Film</option>
                <option value="miscellaneous">Miscellaneous</option>
            </select>
        </div>

        <div>
            <label for="distance">Distance(miles)</label>
            <input type="text" name="distance" id="js-input-distance" placeholder="10">

            <label for="from">from</label>
            <input type="radio" name="from" value="here" id="js-input-from-here" onclick="handleHere();" checked> here<br>
            <input type="radio" name="from" value="there" class="location-radio" id="js-input-from-there" onclick="handleThere();">

            <input type="text" name="location" placeholder="location" id="js-input-location">
            <input type="hidden" name="this-lon" id="js-this-lon">
            <input type="hidden" name="this-lat" id="js-this-lat">
        </div>
        
        <div class="buttons">
            <input type="submit" value="Search" id="js-submit" disabled>
            <button onclick="handleClear(); return false;">Clear</button>
        </div>

        <div id="search-results"></div>

    </form>

    <?php

    if ($_GET['keyword']) {
        $lat = '';
        $lon = '';

        if ($_GET['from'] == 'there') {
            $address = $_GET['location'];
            $response = fetch_location($address);
            if ($response) {
            } else {
                $location = $response['results'][0]['geometry']['location'];
                $lat = $location['lat'];
                $lon = $location['lon'];
            }
        } else {
            $lat = $_GET['this-lat'];
            $lon = $_GET['this-lon'];
        }
        $geo_point = encode(floatval($lat), floatval($lon));
        $search_results = search_events($geo_point, $_GET['keyword'], $_GET['category'], (int)$_GET['distance']);
        echo '<div id="js-events-response" style="display: none">' . $search_results . '</div>';
    }

    ?>

    <p><?php echo do_it('abced') ?></p>

</div>
</body>
</html>

