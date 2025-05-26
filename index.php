<?php require_once 'functions.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pokemon Go - Meta</title>
   <link rel="stylesheet" href="main.css">
</head>

<?php
$PoGo = new Pogo();
$formatted = $PoGo->format_range_numbers($PoGo->raidIds);
$output = $formatted ? trim(implode(",", $formatted)) : "";
?>

<body>
   <header>
      <h1>Pokemon Go - Raid</h1>
      <a href="https://db.pokemongohub.net/" rel="nofollow" target="_blank" style="font-size: 12px; font-weight: 400;">(source PoGoDB)</a>
   </header>
   <section class="main">
      <div id="control">
         <textarea id="ids"><?php echo $output; ?></textarea>
         <div id="filters">
            <button id="distance-20">Distance under 20</button>
            <button id="distance20-">Distance over 20</button>
            <button id="traded">Traded</button>
            <button id="!traded" class="exclude">Exclude Traded</button>
            <button id="shadow">Shadow</button>
            <button id="!shadow" class="exclude">Exclude Shadow</button>
            <button id="shiny">shiny</button>
            <button id="!shiny" class="exclude">Exclude shiny</button>
            <button id="eggsonly">Babies</button>
            <button id="evolve">Can Evolve</button>
            <button id="costume">Costume</button>
            <button id="dynamax">Dynamax</button>
            <button id="legendary">Legendary</button>
            <button id="lucky">lucky</button>
            <button id="mythical">mythical</button>
         </div>
      </div>
      <button id="copyButton">Copy</button>
   </section>

   <script src="main.js"></script>
</body>

</html>