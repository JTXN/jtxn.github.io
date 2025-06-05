<?php

require_once "functions.php";

class PVP
{
   function __construct()
   {
      $this->gl_file();
      $this->ul_file();
      $this->ml_file();
   }

   public function gl_file()
   {
      $this->process_file(
         'pvp/gl.html',
         'presets/great_league.json'
      );
   }

   public function ul_file()
   {
      $this->process_file(
         'pvp/ul.html',
         'presets/ultra_league.json'
      );
   }

   public function ml_file()
   {
      $this->process_file(
         'pvp/ml.html',
         'presets/master_league.json'
      );
   }

   private function process_file(string $inputPath, string $outputPath, int $limit = 80): void
   {
      if (!file_exists($inputPath)) return;

      $html = file_get_contents($inputPath);
      $nameToIdMap = json_decode(file_get_contents('name_to_id.json'), true);

      $dom = new DOMDocument();
      libxml_use_internal_errors(true);
      $dom->loadHTML($html);
      libxml_clear_errors();

      $xpath = new DOMXPath($dom);
      $nodes = $xpath->query('//div[contains(@class, "rankings-container")]//span[contains(@class, "name")]');

      $ids = [];
      $count = 0;

      foreach ($nodes as $node) {
         if ($count >= $limit) break;

         $name = Pogo::clean_name($node->textContent);
         $id = $nameToIdMap[$name] ?? null;

         if ($id !== null) {
            $ids[] = $id;
         }

         $count++;
      }

      file_put_contents($outputPath, json_encode($ids));
   }
}
new PVP();
