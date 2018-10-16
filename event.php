
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

    </script>
</head>
<body>
<div class="event-search">
    <h1 class="event-search-heading">Events Search</h1>
    <hr>
    <form action="#" method="get">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" name="keyword" required>
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
            <input type="text" name="distance">

            <label for="from">from</label>
            <input type="radio" name="from" value="here" onclick="handleHere();"> here<br>
            <input type="radio" name="from" value="there" onclick="handleThere();">

            <input type="text" name="location" placeholder="location" id="js-input-location">
        </div>
        
        <div>
            <input type="submit" value="Search" disabled>
            <button>Clear</button>
        </div>

    </form>

    <?php if ($_GET['keyword']) {; ?>
        aieee
    <?php }; ?>

    <p><?php echo do_it('abced') ?></p>

</div>
</body>
</html>

