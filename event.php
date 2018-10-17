
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

function fetch_event_detail($event_id) {
    $consumer_key = getenv('TICKET_CONSUMER_KEY');
    $url = 'https://app.ticketmaster.com/discovery/v2/events/' . $event_id . '?apikey=' . $consumer_key;
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
            width: 1000px;
        }

        .event-search {
            margin: auto;
            margin-top: 40px;
            background: #fafafa;
            border-style: solid;
            border-color: #eee;
            padding: 5px;
            width: 800px;
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

        .no-records {
            background: #fafafa;
            text-align: center;
        }

        .search-results {
            margin-top: 40px;
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
            return xmlHttp.responseText;
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
            const responseElement = document.getElementById('js-events-response');
            if (!responseElement) {
                return false;
            }
            const eventsStr = responseElement.innerText;
            if (eventsStr) {
                const json = JSON.parse(eventsStr);
                if (json._embedded) {
                    return json._embedded.events;
                }
                return [];
            } else {
                return false;
            }
        }

        function generateTdDate(event) {
            const localDate = event.dates.start.localDate;
            const localTime = event.dates.start.localTime;
            const tdDate = document.createElement('td');
            tdDate.innerText = localDate + ' ' + localTime;
            return tdDate;
        }

        function generateTdIcon(event) {
            const images = event.images;
            const tdImage = document.createElement('td');
            if (images.length > 0) {
                const iconUrl = event.images[0].url;
                const image = document.createElement('img');
                image.src = iconUrl;
                image.classList.add('icon');
                tdImage.appendChild(image);
            } else {
                tdImage.innerText = 'N/A';
            }
            return tdImage;
        }

        function generateTdName(event) {
            // Event name
            const tdName = document.createElement('td');
            const eventLink = document.createElement('a');
            eventLink.href = window.location.href + '&event_id=' + event.id;
            eventLink.innerText = event.name;
            tdName.appendChild(eventLink);
            return tdName;
        }

        function generateTdGenre(event) {
            const classifications = event.classifications;
            let genre = 'N/A';
            if (classifications.length > 0) {
                genre = classifications[0].segment.name;
            }
            const tdGenre = document.createElement('td');
            tdGenre.innerText = genre;
            return tdGenre;
        }

        function generateTdVenue(event) {
            let venue = 'N/A';
            if (event._embedded.venues && event._embedded.venues.length > 0) {
                venue = event._embedded.venues[0].name;
            }
            const tdVenue = document.createElement('td');
            tdVenue.innerText = venue;
            return tdVenue;
        }

        function generateTr(event) {
            const tr = document.createElement('tr');

            tr.appendChild(generateTdDate(event));
            tr.appendChild(generateTdIcon(event));
            tr.appendChild(generateTdName(event));
            tr.appendChild(generateTdGenre(event));
            tr.appendChild(generateTdVenue(event));

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

        function renderNoRecords() {
            const div = document.createElement('div');
            div.classList.add('no-records');
            div.innerText = 'No Records has been found';
            document.getElementById('search-results').appendChild(div);
        }

        function getEventDetailOnPage() {
            const detailElem = document.getElementById('js-event-detail');
            if (!detailElem) {
                return false;
            }
            const json = detailElem.innerText;
            return JSON.parse(json);
        }

        function renderDetailTable($detail) {
            
        }

        window.onload = function() {
            startLocating();

            const detail = getEventDetailOnPage();
            if (detail) {
                console.log(detail);
            } else {
                const events = getEventsOnPage();
                if (events === false) {
                    return;
                }
                if (events.length > 0) {
                    renderEvents(events);
                } else {
                    renderNoRecords();
                }
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

    </form>
</div>

<div id="search-results" class="search-results"></div>

<?php

if (isset($_GET['event_id'])) {
    $event_detail = fetch_event_detail($_GET['event_id']);
    echo '<div id="js-event-detail" style="display: none">' . $event_detail . '</div>';
} else if (isset($_GET['keyword'])) {
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
    if (isset($_GET['distance'])) {
        $distance = (int)$_GET['distance'];
    } else {
        $distance = 10;
    }
    $search_results = search_events($geo_point, $_GET['keyword'], $_GET['category'], $distance);
    echo '<div id="js-events-response" style="display: none">' . $search_results . '</div>';
}

?>
<p><?php echo do_it('abced') ?></p>

</body>
</html>

