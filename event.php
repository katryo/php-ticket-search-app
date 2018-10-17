
<?php
function do_it($something){
    return $something . "abc";
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

        window.onload = function() {
            startLocating();
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

    <?php if ($_GET['keyword']) {; ?>
        aieee
    <?php }; ?>

    <p><?php echo do_it('abced') ?></p>

</div>
</body>
</html>

