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
    public $cachedFile = 'raid.json';
    public $raidIds = [];

    /**
     * display_all_pokemon
     * format_range_numbers
     * store_cached_pokemon
     * get_all_raid_pokemon_from_db
     * get_cached_raid_pokemon
     * invert_selection
     * clean_name
     * */

    public function __construct()
    {
        // $this->raidIds = $this->get_cached_raid_pokemon();
    }

    public function display_all_pokemon()
    {
        if (!file_exists("name_to_id.json")) return;
        $pokemons = json_decode(file_get_contents('name_to_id.json'), true);
        foreach ($pokemons as $pokemon => $id) {
            $img_url = "images/{$id}.png";
            echo "<button data-id='$id'><span>$id</span>";
            echo "<img src='$img_url' width='64' height='64'>";
            echo $pokemon;
            echo "</button>";
        }
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
                /* $allIds = $this->get_all_raid_pokemon_from_db();
                $this->store_cached_pokemon($allIds);
                return $allIds; */
            }
        } else {
            echo "Cached Pokemon IDs file not found. You'll need to run the data extraction again.\n";
            return;
        }
    }



    public function invert_selection(array $selected, int $total = 1000): array
    {
        $all = range(1, $total);
        return array_values(array_diff($all, $selected));
    }

    public static function clean_name($uncleanedname)
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
} // class
