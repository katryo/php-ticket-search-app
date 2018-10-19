
<?php
include 'geoHash.php';

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

function fetch_venue_detail($keyword) {
    $consumer_key = getenv('TICKET_CONSUMER_KEY');
    $url = 'https://app.ticketmaster.com/discovery/v2/venues?apikey='
        . $consumer_key
        . '&keyword='
        . urlencode($keyword);
    $response = file_get_contents($url);
    return $response;
}

function if_value_echo($val) {
    if (isset($val)) {
        echo ' value="' . $val . '"';
    }
}

function if_category_echo_selected($candidate) {
    if (isset($_GET['category'])) {
        if ($_GET['category'] == $candidate) {
            echo 'selected';
        }
    }
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

        a {
            color: black;
            text-decoration: none;
        }

        a:hover {
            color: #999;
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

        .venue-button {
            border: none;
            hover: #eee;
            color: black;
            background: white;
        }

        .venue-map-div {
            width: 400px;
            height: 300px;
        }

        .venue-map-buttons {
            position: absolute;
            width: 100px;
            z-index: 10;
            text-align: center;
        }

        .venue-map-button {
            width: 100px;
            height: 40px;
            background: #eee;
        }

        .venue-map-button:hover {
            background: #ddd;
        }

        .icon {
            width: 100px;
        }

        .no-records {
            background: #fafafa;
            text-align: center;
        }

        .no-venue-info {
            background: #fafafa;
            text-align: center;
        }

        .search-results {
            margin: auto;
            margin-top: 40px;
            width: 100%;
        }

        .seat-img {
            float: right;
            height: 250px;
        }

        .event-detail {
            margin: auto;
            width: 80%;
        }

        .venue-detail {
            margin-top: 20px;
            margin: auto;
            width: 80%;
        }

        .map-table {
            border: solid;
            width: 100%;
        }

        .map-outer-div {
            width: 500px;
        }

        .map-in-table {
            width: 400px;
            height: 300px;
            margin: auto;
        }

        .travel-button {
            border: none;
            display: block;
            background: #eee;
        }

        .travel-buttons {
            margin: 10px;
            background: #eee;
            float: left;
        }

        .venue-button-in-td {
            font-size: 14px;
            border: none;
            color: black;
            background: white;
        }

        .venue-button-in-td:hover {
            color: #aaa;
        }

        label {
            font-weight: bold;
        }

        table {
            text-align: left;
        }

        table {
            border-collapse: collapse;
        }

        .hidden {
            display: none;
        }

        #search-results table, #search-results td, #search-results tr, #search-results th {
            border: 1px solid #bbb;
        }

        .venue-detail table, .venue-detail td, .venue-detail tr, .venue-detail th {
            border: 1px solid #bbb;
        }

        .venue-info-opener {
            width: 200px;
            margin: auto;
            text-align: center;
        }

        .venue-info-text {
            color: #bbb;
            text-align: center;
        }

        .venue-info-arrow {
            width: 50px;
        }

        .venue-photo {
            max-width: 80%;
        }

        .venue-photos-table {
            border: 1px solid #bbb;
            width: 100%;
            text-align: center;
            margin: auto;
        }

        .detail-table {
            margin: auto;
        }

        h2 {
            text-align: center;
        }

        .event-detail-wrapper {
            min-height: 250px;
        }

        .search-results-table {
            width: 100%;
        }

        .search-results-table th {
            text-align: center;
        }

    </style>
<!--    <script async defer-->
<!--            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCI4NLVqVTo5cjbycAY5KomPBd542pHpXk">-->
<!--    </script>-->
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB-c1cm-GD-42YecmJJ_kzk-7l-X4nFp6A">
    </script>




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
            window.document.getElementById('js-input-distance').value = '';
            window.document.getElementById('js-input-from-here').checked = true;
            document.getElementById('js-input-from-there').checked = false;
            document.getElementById('js-input-location').value = '';
            document.getElementById('js-input-location').required = false;
            document.getElementById('js-input-location').disabled = true;
            document.getElementById('search-results').innerHTML = '';
            document.getElementById('js-event-detail-show').innerHTML = '';
            document.getElementById('js-venue-detail-show').innerHTML = '';
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

            if (localDate && localTime) {
                tdDate.innerText = localDate + ' ' + localTime;
            } else if (localDate) {
                tdDate.innerText = localDate;
            } else {
                tdDate.innerText = 'N/A';
            }
            return tdDate;
        }

        function generateTdIcon(event) {
            const images = event.images;
            const tdImage = document.createElement('td');
            if (images && images.length > 0) {
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
            let newUrl = new URL(window.location.href);
            const searchParams = newUrl.searchParams;
            searchParams.append('event_id', event.id);
            const ans = '?' + searchParams.toString();
            eventLink.href = ans;
            // eventLink.href = window.location.href + '&event_id=' + event.id;
            eventLink.innerText = event.name;
            tdName.appendChild(eventLink);
            return tdName;
        }

        function generateTdGenre(event) {
            const classifications = event.classifications;
            let genre = 'N/A';
            if (classifications && classifications.length > 0) {
                genre = classifications[0].segment.name;
            }
            const tdGenre = document.createElement('td');
            tdGenre.innerText = genre;
            return tdGenre;
        }

        function appendTravelButtonOnMap(wrapper, elem, label, travel, here, there) {
            const button = document.createElement('button');
            button.innerText = label;
            button.classList.add('venue-map-button');
            button.onclick = function() {
                route(elem.service, elem.renderer, travel, here, there);
            };
            wrapper.appendChild(button);
        }


        function getHere() {
            let location;
            const url = new URL(window.location.href);
            const from = url.searchParams.get('from').toString();

            let here;
            if (from === 'here') {
                const location = fetchLocation();
                here = new google.maps.LatLng(parseFloat(location[0]), parseFloat(location[1]));
            } else {
                location = url.searchParams.get('location').toString();
                here = location;
            }
            return here;
        }

        function generateVenueMap(td, lat, lng) {
            const mapDiv = document.createElement('div');
            mapDiv.classList.add('venue-map-div');
            const loc = {lat: lat, lng: lng};

            const map = new google.maps.Map(mapDiv, {
                zoom: 13,
                center: loc
            });

            const marker = new google.maps.Marker({
                position: loc,
                map: map,
            });

            td.map = map;
            td.service = new google.maps.DirectionsService;
            td.renderer = new google.maps.DirectionsRenderer({
                draggable: false,
                map: map,
            });

            const here = getHere();

            const buttonsDiv = document.createElement('div');
            buttonsDiv.classList.add('venue-map-buttons');
            appendTravelButtonOnMap(buttonsDiv, td, 'Walk there', 'WALKING', here, loc);
            appendTravelButtonOnMap(buttonsDiv, td, 'Bike there', 'BICYCLING', here, loc);
            appendTravelButtonOnMap(buttonsDiv, td, 'Drive there', 'DRIVING', here, loc);

            const divButtonAndMap = document.createElement('div');
            divButtonAndMap.appendChild(buttonsDiv);
            divButtonAndMap.appendChild(mapDiv);
            td.appendChild(divButtonAndMap);
        }

        function resetTd(td) {
            td.removeChild(td.childNodes[1]);
            td.removeAttribute('id');
        }

        function toggleMap(td, lng, lat) {
            const opening = document.getElementById('js-map-open');
            if (opening) {
                resetTd(opening);
            }
            if (td !== opening) {
                td.id = 'js-map-open';
                generateVenueMap(td, lat, lng);
            }
        }

        function generateTdVenue(event) {
            let venue = 'N/A';
            if (event._embedded.venues && event._embedded.venues.length > 0 && event._embedded.venues[0].name) {
                venue = event._embedded.venues[0].name;
            }
            const venueButton = document.createElement('div');
            venueButton.innerText = venue;
            venueButton.classList.add('venue-button-in-td');

            const tdVenue = document.createElement('td');

            if (event._embedded.venues[0].location && event._embedded.venues[0].location.longitude) {
                venueButton.onclick = function() {
                    toggleMap(tdVenue,
                        parseFloat(event._embedded.venues[0].location.longitude),
                        parseFloat(event._embedded.venues[0].location.latitude)
                    );
                };
            }

            tdVenue.appendChild(venueButton);
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
            table.classList.add('search-results-table');

            for (let headName of ['Date', 'Icon', 'Event', 'Genre', 'Venue']) {
                const th = document.createElement('th');
                th.innerText = headName;
                table.appendChild(th);
            }

            for (let event of events) {
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

        function wrapWithTr(elem) {
            const tr = document.createElement('tr');
            tr.appendChild(elem);
            return tr;
        }

        function appendTrWrappedElem(elem, table) {
            const tr = wrapWithTr(elem);
            table.appendChild(tr);
        }

        function appendThToTable(text, table) {
            const thArtist = document.createElement('th');
            thArtist.innerText = text;
            appendTrWrappedElem(thArtist, table);
        }

        function appendTdTextToTable(text, table) {
            const td = document.createElement('td');
            td.innerText = text;
            appendTrWrappedElem(td, table);
        }

        function appendTdToTable(elem, table) {
            const td = document.createElement('td');
            td.appendChild(elem);
            appendTrWrappedElem(td, table);
        }

        function renderDetailTable(detail) {
            const table = document.createElement('table');
            table.classList.add('detail-table');
            const tdDate = generateTdDate(detail);

            const thDate = document.createElement('th');
            thDate.innerText = 'Date';

            appendTrWrappedElem(thDate, table);
            appendTrWrappedElem(tdDate, table);

            if (detail._embedded.attractions && detail._embedded.attractions.length > 0) {
                appendThToTable('Artist / Team', table);

                const tdArtist = document.createElement('td');
                let artistLinks = [];
                for (let attraction of detail._embedded.attractions) {
                    if (attraction.name && attraction.url) {
                        const link = document.createElement('a');
                        link.innerText = attraction.name;
                        link.href = attraction.url;
                        link.target = '_blank';
                        artistLinks.push(link);
                    } else if (attraction.name) {
                        const name = document.createElement('span');
                        name.innerText = attraction.name;
                        artistLinks.push(name);
                    }
                }
                for (let i = 0; i < artistLinks.length; i++) {
                    tdArtist.appendChild(artistLinks[i]);
                    if (i < artistLinks.length-1) {
                        let span = document.createElement('span');
                        span.innerText = ' / ';
                        tdArtist.appendChild(span);
                    }
                }
                appendTrWrappedElem(tdArtist, table);
            }

            if (detail._embedded.venues && detail._embedded.venues.length > 0) {
                appendThToTable('Venue', table);

                const tdArtist = document.createElement('td');
                tdArtist.innerText = detail._embedded.venues[0].name;
                appendTrWrappedElem(tdArtist, table);
            }

            if (detail.classifications && detail.classifications.length > 0) {
                appendThToTable('Genre', table);

                const tdGenre = document.createElement('td');
                let genres = [];
                for (let classification of detail.classifications) {
                    if (classification.subGenre && classification.subGenre.name !== 'Undefined') {
                        genres.push(classification.subGenre.name);
                    }
                    if (classification.genre && classification.genre.name !== 'Undefined') {
                        genres.push(classification.genre.name);
                    }
                    if (classification.segment && classification.segment.name !== 'Undefined') {
                        genres.push(classification.segment.name);
                    }
                    if (classification.subtype && classification.subtype.name !== 'Undefined') {
                        genres.push(classification.type.name);
                    }
                    if (classification.type && classification.type.name !== 'Undefined') {
                        genres.push(classification.type.name);
                    }
                }
                tdGenre.innerText = genres.join(' | ');
                appendTrWrappedElem(tdGenre, table);
            }

            if (detail.priceRanges && detail.priceRanges.length > 0) {
                appendThToTable('Price Ranges', table);
                const priceRange = detail.priceRanges[0];
                let priceRangeText;
                if (priceRange.min && priceRange.max) {
                    priceRangeText = priceRange.min + ' - ' + priceRange.max + ' ' + priceRange.currency;
                } else if (priceRange.min) {
                    priceRangeText = priceRange.min + ' ' + priceRange.currency;
                } else {
                    priceRangeText = priceRange.max + ' ' + priceRange.currency;
                }
                const tdPriceRange = document.createElement('td');
                tdPriceRange.innerText = priceRangeText;
                appendTrWrappedElem(tdPriceRange, table);
            }


            if (detail.dates && detail.dates.status) {
                appendThToTable('Ticket Status', table);
                const status = detail.dates.status.code;
                const tdStatus = document.createElement('td');
                tdStatus.innerText = status;
                appendTrWrappedElem(tdStatus, table);
            }

            if (detail.url) {
                appendThToTable('Buy Ticket At', table);
                const url = detail.url;
                const tdUrl = document.createElement('td');
                const linkToUrl = document.createElement('a');
                linkToUrl.href = url;
                linkToUrl.innerText = 'Ticketmaster';
                linkToUrl.target = '_blank';
                tdUrl.appendChild(linkToUrl);
                appendTrWrappedElem(tdUrl, table);
            }

            const wrap = document.createElement('div');
            wrap.classList.add('event-detail-wrapper');
            document.getElementById('js-event-detail-show').appendChild(wrap);

            if (detail.seatmap) {
                const seatImg = document.createElement('img');
                seatImg.src = detail.seatmap.staticUrl;
                seatImg.classList.add('seat-img');
                wrap.appendChild(seatImg);
            }

            wrap.appendChild(table);
            // const tdVenue = generateTdVenue(detail);
        }

        function renderEventName(name) {
            const h2 = document.createElement('h2');
            h2.innerText = name;
            document.getElementById('js-event-detail-show').appendChild(h2);
        }

        function getVenueDetailOnPage() {
            const elem = document.getElementById('js-venue-detail');
            if (elem) {
                return JSON.parse(elem.innerText);
            }
            return false;
        }

        function appendThTdInTr(thText, tdText, table) {
            const th = document.createElement('th');
            th.innerText = thText;

            const td = document.createElement('td');
            td.innerText = tdText;

            const tr = document.createElement('tr');
            tr.appendChild(th);
            tr.appendChild(td);
            table.appendChild(tr);
        }

        function appendThTdElemInTr(thText, tdElem, table) {
            const th = document.createElement('th');
            th.innerText = thText;

            const td = document.createElement('td');
            td.appendChild(tdElem);

            const tr = document.createElement('tr');
            tr.appendChild(th);
            tr.appendChild(td);
            table.appendChild(tr);
        }


        function route(service, renderer, travelMode, origin, destination) {
            const request = {
                origin: origin,
                destination: destination,
                travelMode: travelMode
            };
            service.route(request, function (result, status) {
               if (status === 'OK') {
                   renderer.setDirections(result);
               }
            });
        }

        function appendTravelButton(wrapper, elem, label, travel, here, there) {
            const button = document.createElement('button');
            button.innerText = label;
            button.classList.add('travel-button');
            button.onclick = function() {
                route(elem.service, elem.renderer, travel, here, there);
            };
            wrapper.appendChild(button);
        }

        function generateMap(mapOuterDiv, lat, lng) {
            const mapDiv = document.createElement('div');
            mapDiv.classList.add('map-in-table');

            const loc = {lat: lat, lng: lng};
            const map = new google.maps.Map(mapDiv, {
                zoom: 13,
                center: loc
            });
            mapOuterDiv.map = map;

            const marker = new google.maps.Marker({
                position: loc,
                map: map,
            });
            mapOuterDiv.service = new google.maps.DirectionsService;
            mapOuterDiv.renderer = new google.maps.DirectionsRenderer({
                draggable: false,
                map: map,
            });
            mapOuterDiv.classList.add('map-outer-div');

            const here = getHere();

            const buttonsDiv = document.createElement('div');
            buttonsDiv.classList.add('travel-buttons');
            appendTravelButton(buttonsDiv, mapOuterDiv, 'Walk there', 'WALKING', here, loc);
            appendTravelButton(buttonsDiv, mapOuterDiv, 'Bike there', 'BICYCLING', here, loc);
            appendTravelButton(buttonsDiv, mapOuterDiv, 'Drive there', 'DRIVING', here, loc);
            mapOuterDiv.appendChild(buttonsDiv);
            mapOuterDiv.appendChild(mapDiv);
            return mapOuterDiv;
        }

        function processMap(detail, table) {
            const mapOuterDiv = document.createElement('div');
            mapOuterDiv.id = 'js-map-outer-div';
            generateMap(mapOuterDiv, parseFloat(detail.location.latitude), parseFloat(detail.location.longitude));

            appendThTdElemInTr('Map', mapOuterDiv, table);
        }

        function resetMap() {
            if (document.venueDetail) {
                const detail = document.venueDetail;
                const mapOuterDiv = document.getElementById('js-map-outer-div');
                mapOuterDiv.innerHTML = '';
                generateMap(mapOuterDiv, parseFloat(detail.location.latitude), parseFloat(detail.location.longitude));
            }
        }

        function showDetail(venueTextDiv, arrow, table, textClicked, venueInfoDiv) {

            if (textClicked === venueInfoClickedText) {
                const photos = document.getElementById('js-venue-photos');
                if (photos && photos.childNodes.length === 2) {
                    hideDetail(photos.childNodes[0], photos.childNodes[1], document.getElementById('js-venue-photos-table'), venuePhotosInitialText);
                }
            } else {
                const infos = document.getElementById('js-venue-info');
                if (infos && infos.childNodes.length === 2) {
                    hideDetail(infos.childNodes[0], infos.childNodes[1], document.getElementById('js-venue-info-table'), venueInfoInitialText);
                }
            }

            table.classList.remove('hidden');
            venueTextDiv.innerText = textClicked;
            arrow.src = 'arrow_up.png';
        }

        function hideDetail(venueTextDiv, arrow, table, textInitial) {
            if (textInitial === venueInfoInitialText) {
                resetMap();
            }
            table.classList.add('hidden');
            venueTextDiv.innerText = textInitial;
            arrow.src = 'arrow_down.png';
        }

        function toggleVenueDetail(venueTextDiv, arrow, table, textInitial, textClicked, venueInfoDiv) {
            if (table.classList.contains('hidden')) {
                showDetail(venueTextDiv, arrow, table, textClicked, venueInfoDiv);
            } else {
                hideDetail(venueTextDiv, arrow, table, textInitial);
            }
        }

        function renderVenueDetail(detail) {
            console.log(detail);
            document.venueDetail = detail;
            const table = document.createElement('table');
            if (detail.name) {
                appendThTdInTr('Name', detail.name, table);
            } else {
                appendThTdInTr('Name', 'N/A', table);
            }

            if (detail.location && detail.location.longitude && detail.location.latitude) {
                processMap(detail, table);
            } else {
                appendThTdInTr('Map', 'N/A', table);
            }

            if (detail.address && detail.address.line1) {
                appendThTdInTr('Address', detail.address.line1, table);
            } else {
                appendThTdInTr('Address', 'N/A', table);
            }

            if (detail.city && detail.city.name && detail.state && detail.state.stateCode) {
                const city = [detail.city.name, detail.state.stateCode].join(', ');
                appendThTdInTr('City', city, table);
            } else {
                appendThTdInTr('City', 'N/A', table);
            }

            if (detail.postalCode) {
                appendThTdInTr('Postal Code', detail.postalCode, table);
            } else {
                appendThTdInTr('Postal Code', 'N/A', table);
            }

            if (detail.name && detail.url) {
                const upcomingLink = document.createElement('a');
                upcomingLink.href = detail.url;
                upcomingLink.target = '_blank';
                upcomingLink.innerText = detail.name + ' Tickets';
                appendThTdElemInTr('Upcoming Events', upcomingLink, table);
            } else {
                appendThTdElemInTr('Upcoming Events', 'N/A', table);
            }

            table.classList.add('map-table');
            return table;
        }

        function renderNoVenueInfo() {
            const div = document.createElement('div');
            div.innerText = 'No Venue Info Found';
            div.classList.add('no-venue-info');
            return div;
        }

        function wrapWithToggle(tableOrDiv, textInitial, textClicked, id) {
            tableOrDiv.classList.add('hidden');

            const venueTextDiv = document.createElement('div');
            venueTextDiv.classList.add('venue-info-text');
            venueTextDiv.innerText = textInitial;

            const arrow = document.createElement('img');
            arrow.src = 'arrow_down.png';
            arrow.classList.add('venue-info-arrow');

            const venueInfoDiv = document.createElement('div');
            venueInfoDiv.classList.add('venue-info-opener');
            venueInfoDiv.appendChild(venueTextDiv);
            venueInfoDiv.appendChild(arrow);

            venueInfoDiv.onclick = function() {
                toggleVenueDetail(venueTextDiv, arrow, tableOrDiv, textInitial, textClicked, venueInfoDiv);
            };
            venueInfoDiv.id = id;
            return venueInfoDiv;
        }

        function generateNoPhotos() {
            const div = document.createElement('div');
            div.classList.add('no-venue-info');
            div.innerText = 'No Venue Photos Found';
            return div
        }

        function generateVenuePhotosTable(detail) {
            const images = detail.images;
            const table = document.createElement('table');
            table.classList.add('venue-photos-table');
            if (images && images.length > 0) {
                for (let image of images) {
                    let img = document.createElement('img');
                    img.src = image.url;
                    img.classList.add('venue-photo');
                    let tr = wrapWithTr(img);
                    table.appendChild(tr);
                }
            } else {
                return generateNoPhotos();
            }
            return table;
        }

        const venueInfoInitialText = 'click to show venue info';
        const venueInfoClickedText = 'click to hide venue info';
        const venuePhotosInitialText = 'click to show venue photos';
        const venuePhotosClickedText = 'click to hide venue photos';

        function generateDetail (detail) {
            renderEventName(detail.name);
            renderDetailTable(detail);

            const venueDetail = getVenueDetailOnPage();
            let tableOrDiv;
            let photosElem;
            if (venueDetail
                && venueDetail._embedded
                && venueDetail._embedded.venues
                && venueDetail._embedded.venues.length > 0) {
                tableOrDiv = renderVenueDetail(venueDetail._embedded.venues[0]);
                photosElem = generateVenuePhotosTable(venueDetail._embedded.venues[0]);
            } else {
                tableOrDiv = renderNoVenueInfo();
                photosElem = generateNoPhotos();
            }
            tableOrDiv.id = 'js-venue-info-table';
            photosElem.id = 'js-venue-photos-table';

            const venueInfoDiv = wrapWithToggle(tableOrDiv, venueInfoInitialText, venueInfoClickedText, 'js-venue-info');
            const venuePhotosDiv = wrapWithToggle(photosElem, venuePhotosInitialText, venuePhotosClickedText, 'js-venue-photos');

            document.getElementById('js-venue-detail-show').appendChild(venueInfoDiv);
            document.getElementById('js-venue-detail-show').appendChild(tableOrDiv);

            document.getElementById('js-venue-detail-show').appendChild(venuePhotosDiv);
            document.getElementById('js-venue-detail-show').appendChild(photosElem);
        }

        window.onload = function() {
            startLocating();

            const detail = getEventDetailOnPage();
            if (detail) {
                generateDetail(detail);
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
    <form action="" method="get">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" name="keyword" id="js-input-keyword" <?php if_value_echo($_GET['keyword']); ?>  required>
        </div>

        <div>
            <label for="category">Category</label>
            <select name="category" id="category">
                <option value="default" <?php if_category_echo_selected('default') ?> >Default</option>
                <option value="music" <?php if_category_echo_selected('music') ?>>Music</option>
                <option value="sports" <?php if_category_echo_selected('sports') ?>>Sports</option>
                <option value="arts" <?php if_category_echo_selected('arts') ?>>Arts & Theatre</option>
                <option value="film" <?php if_category_echo_selected('film') ?>>Film</option>
                <option value="miscellaneous" <?php if_category_echo_selected('miscellaneous') ?>>Miscellaneous</option>
            </select>
        </div>

        <div>
            <label for="distance">Distance(miles)</label>
            <input type="number" name="distance" id="js-input-distance" placeholder="10"
                <?php if (isset($_GET['distance'])) {
                    echo 'value=' . '"' . $_GET['distance'] . '"';
                };
                ?>
            >

            <label for="from">from</label>
            <input type="radio" name="from" value="here" id="js-input-from-here" onclick="handleHere();"
            <?php if (isset($_GET['from']) && ($_GET['from'] == 'there')) {
            } else {
                echo 'checked';
            }
             ?>
            > here<br>
            <input type="radio" name="from" value="there" class="location-radio" id="js-input-from-there" onclick="handleThere();"
                <?php if (isset($_GET['from']) && ($_GET['from'] == 'there')) {
                    echo 'checked';
                }
                ?>
            >

            <input type="text" name="location" placeholder="location" id="js-input-location"
                   <?php if_value_echo($_GET['location']); ?>

            <?php if (isset($_GET['from']) && ($_GET['from'] == 'there')) {
            } else {
                echo 'disabled';
            }
            ?>
            >
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
<div id="js-event-detail-show" class="event-detail"></div>
<div id="js-venue-detail-show" class="venue-detail"></div>

<?php

if (isset($_GET['event_id'])) {
    $event_detail = fetch_event_detail($_GET['event_id']);
    $obj = json_decode($event_detail, true);
    $venues = $obj['_embedded']['venues'];
    if (count($venues) > 0) {
        $name = $venues[0]['name'];
        sleep(2); // To avoid API limit
        $venue_detail = fetch_venue_detail($name);
        echo '<div id="js-venue-detail" style="display: none">' . $venue_detail . '</div>';
    }
    echo '<div id="js-event-detail" style="display: none">' . $event_detail . '</div>';
} else if (isset($_GET['keyword'])) {
    $lat = '';
    $lon = '';

    if ($_GET['from'] == 'there') {
        $address = $_GET['location'];
        $response = json_decode(fetch_location($address), true);
        $location = $response['results'][0]['geometry']['location'];
        $lat = $location['lat'];
        $lon = $location['lng'];
    } else {
        $lat = $_GET['this-lat'];
        $lon = $_GET['this-lon'];
    }

    $geo_point = encode(floatval($lat), floatval($lon));

    if (isset($_GET['distance']) && $_GET['distance'] != '') {
        $distance = (int)$_GET['distance'];
    } else {
        $distance = 10;
    }
    $search_results = search_events($geo_point, $_GET['keyword'], $_GET['category'], $distance);
    echo '<div id="js-events-response" style="display: none">' . $search_results . '</div>';
}

?>

</body>
</html>

