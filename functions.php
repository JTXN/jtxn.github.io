<?php

class Pogo
{
    public $types = [
        'bug',
        'dark',
        'dragon',
        'electric',
        'fairy',
        'fighting',
        'fire',
        'flying',
        'ghost',
        'grass',
        'ground',
        'ice',
        'normal',
        'poison',
        'psychic',
        'rock',
        'steel',
        'water'
    ];
    public $cachedFile = 'cached_pokemon_ids.json';
    public $raidIds = [];

    public function __construct()
    {
        // echo "FOO BAR";
        $this->raidIds = $this->get_cached_raid_pokemon();
    }

    public function getbyid($id)
    {
        $path = "api-data/data/api/v2/pokemon/" . $id;
        if (!file_exists($path)) {
            return;
        }
        $json = json_decode(file_get_contents("$path/index.json"), true);

        echo "$id :  {$json['name']} <br>";

        // echo "<img src='{$json['sprites']['front_default']}' height='64' width='64'>";

    }

    public function format_range_numbers(array $numbers): array
    {
        sort($numbers); // Sort numbers ascending
        $ranges = [];

        $start = $end = null;

        foreach ($numbers as $number) {
            if ($start === null) {
                $start = $end = $number;
            } elseif ($number == $end + 1) {
                $end = $number;
            } else {
                // Save previous range
                $ranges[] = ($start == $end) ? "$start" : "$start-$end";
                $start = $end = $number;
            }
        }

        // Add the last range
        if ($start !== null) {
            $ranges[] = ($start == $end) ? "$start" : "$start-$end";
        }

        return $ranges;
    }


    public function store_cached_pokemon($allIds)
    {
        $jsonData = json_encode($allIds);

        if (file_put_contents($this->cachedFile, $jsonData)) {
            echo "Pokemon IDs successfully stored in {$this->cachedFile}";
        } else {
            echo "Error storing Pokemon IDs.\n";
        }
    }

    public function get_cached_raid_pokemon()
    {
        if (file_exists($this->cachedFile)) {
            $cachedData = file_get_contents($this->cachedFile);
            $allIds = json_decode($cachedData, true);

            if (is_array($allIds)) {
                return $allIds;
            } else {
                $allIds = $this->get_all_raid_pokemon_from_db();
                $this->store_cached_pokemon($allIds);
                return $allIds;
            }
        } else {
            echo "Cached Pokemon IDs file not found. You'll need to run the data extraction again.\n";
            return;
        }
    }

    public function get_all_raid_pokemon_from_db()
    {
        $pokemonNames = [];
        $pokemonIds = [];
        $allIds = [];

        foreach ($this->types as $type) {
            if (!file_exists("pogodb/$type.html")) continue;

            $htmlContent = file_get_contents("pogodb/$type.html");

            $nameToIdMap = json_decode(file_get_contents('name-to-id.json'), true);

            libxml_use_internal_errors(true); // Suppress HTML5 warnings
            $doc = new DOMDocument();
            $doc->loadHTML($htmlContent);
            libxml_clear_errors();

            $tbody = $doc->getElementsByTagName('tbody')->item(0);

            if ($tbody) {
                $max = 30;
                $count = 0;
                $rows = $tbody->getElementsByTagName('tr');

                foreach ($rows as $row) {
                    if ($count++ >= $max) {
                        break;
                    }
                    $link = $row->getElementsByTagName('a')->item(0);
                    if ($link) {
                        $name = $this->clean_name($link->textContent);

                        if (isset($pokemonNames[$name])) {
                            continue;
                        }

                        $pokemonNames[$name] = true;

                        $id = $nameToIdMap[$name] ?? null;

                        if ($id === null) {
                            continue;
                        }

                        $allIds[] = $id; // Push ID into array
                    }
                }
            }
        }
        return $allIds;
    }




    public function invert_selection(array $selected, int $total = 1000): array
    {
        $all = range(1, $total);
        return array_values(array_diff($all, $selected));
    }

    public function clean_name($uncleanedname)
    {
        if (!$uncleanedname) return;
        $name = strtolower(trim($uncleanedname));
        // Remove anything inside parentheses, including the space before it
        $name = preg_replace('/\s*\(.*?\)/', '', $name);

        // Normalize spacing
        $name = preg_replace('/\s+/', ' ', $name);

        $name = preg_replace('/aria\s+form(e)?/i', '', $name);
        $name = preg_replace('/shadow|mega|gigantamax|tera|complete form(e)?|galarian|standard mode|hisuian|alolan|paldean|origin form(e)?|battle bond|dawn wings|dusk mane|school form|incarnate form|therian form/i', '', $name);

        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);
        return $name;
    }
}
