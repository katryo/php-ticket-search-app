
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
            width: 1000px;
        }

        .event-search {
            background: #eee;
            border-style: solid;
            border-color: gray;
        }
    </style>
</head>
<body>
<div class="event-search">
    <h1>Events Search</h1>
    <hr>
    <form action="#" method="get">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" name="keyword" required>
        </div>

        <div>
            <label for="category">Category</label>
            <select name="category" id="category">
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
            <input type="radio" name="from" value="here"> here<br>
            <input type="radio" name="from" value="there">
            <input type="text" name="location" placeholder="location">
        </div>
        
        <div>
            <input type="submit" value="Search">
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

