<?php
require_once 'functions.php';

class Raiders
{
   public $PoGo;

   function __construct()
   {
      $PoGo = new Pogo();
   }
   public function get_all_raid_pokemon_from_db()
   {
      $pokemonNames = [];
      $allIds = [];

      foreach ($this->PoGo->types as $type) {
         $file = "raid/$type.html";
         if (!file_exists($file)) continue;

         $htmlContent = file_get_contents($file);

         $nameToIdMap = json_decode(file_get_contents('name_to_id.json'), true);

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
                  $name = Pogo::clean_name($link->textContent);

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
}
