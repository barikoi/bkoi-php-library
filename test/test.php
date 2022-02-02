<?php



require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__."/../");
$dotenv->load();
$client = new Barikoi\BkoiPhpLibrary\BarikoiApiClient(
    getenv('BARIKOI_API_KEY')
);

try {
    $res = $client->getGeocode("514148");

    echo "<pre>";
    var_dump(json_decode( $res->getBody()->getContents() ));
    echo "</pre>";
  }
  
  //catch exception
  catch(Exception $e) {
    echo 'Message: ' .$e->getMessage();
  }

