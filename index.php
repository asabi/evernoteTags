#!/usr/bin/php
<?php
require 'vendor/autoload.php';

$config = parse_ini_file(__DIR__.'/config.ini');

date_default_timezone_set('America/Los_Angeles');

define('ERRORLOG',__DIR__.'/logs/errors-'.date('Y-m-d h:i:s'));

if (false && !isset($argv[1])){
  echo "Please specify tag\n\n";
  error_log("Please specify a tag\n", 3, ERRORLOG);
  exit;
}

$createTable = false;
if (!file_exists(__DIR__.'/tags.sqlite')) {
  $createTable = true;
}

$db = new SQLite3(__DIR__.'/tags.sqlite');



if ($createTable) {
  $db->exec('CREATE TABLE tags (guid varchar(255), name varchar (255), parentGuid varchar(255))');
  populateTagsTable($db, $config);
}

$tagName = $argv[1];

if (strtolower($tagName) == 'refresh') {
  populateTagsTable($db, $config);
  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><items>";
  $xml.='<item uid="Refresh" arg="Refresh" valid="NO" autocomplete="Refresh" type="file"><title>Refresh complete</title><subtitle>Refresh</subtitle><icon type="file:skipcheck">'.__DIR__.'/icons/refresh.png</icon></item>'."\n";
  $xml.='</items>';

  echo $xml;
  exit;
}

searchTag($db, $tagName);


function searchTag($db, $tagName) {
  $stmt = $db->prepare("SELECT * FROM tags WHERE name LIKE :tag ORDER BY name ASC");
  $tagNameWild = "%$tagName%";
  $stmt->bindValue(':tag', $tagNameWild, SQLITE3_TEXT);

  $results = $stmt->execute();
  if (!$results) {
    echo "error";exit;
  }

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><items>";

  $tagName = htmlspecialchars($tagName , ENT_XML1, 'UTF-8');
  while ($row = $results->fetchArray()) {
    // Make the output XML safe
    $row['name'] = htmlspecialchars($row['name'] , ENT_XML1, 'UTF-8');
    $xml.='<item uid="'.$row['guid'].'" arg="'.$row['name'].'" valid="YES" autocomplete="'.$row['name'].'" type="file:skipcheck"><title>'.$row['name'].'</title><subtitle>'.$tagName.'</subtitle><icon type="fileicon">'.__DIR__.'/icons/tag.png</icon></item>'."\n";
  }

  $xml .= '</items>';

  echo $xml;

}

function populateTagsTable($db, $config) {
  //set this to false to use in production
  $db->exec("DELETE FROM tags");

  $client = new \Evernote\Client($config['token'], $config['sandbox']);
  $advancedClient = $client->getAdvancedClient();
  $noteStore = $advancedClient->getNoteStore();

  $tags = $noteStore->listTags();

  foreach ($tags as $tagInfo) {
    $stmt = $db->prepare("INSERT INTO tags (guid, name, parentGuid) VALUES (:id,:name,:parent)");

    $stmt->bindParam(':id',$tagInfo->guid,SQLITE3_TEXT);
    $stmt->bindParam(':name',$tagInfo->name,SQLITE3_TEXT);
    $stmt->bindParam(':parent',$tagInfo->parentGuid,SQLITE3_TEXT);

    $result = $stmt->execute();
  }
}
