<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
// use Rap2hpoutre\FastExcel\FastExcel;


if (empty($_GET['url'])) {
    die('Url not exists');
}

$baseEndPoint = $_GET['url'];

$certificate = __DIR__ . "/cacert.pem";
ini_set('curl.cainfo', $certificate);
ini_set('openssl.cafile', $certificate);

// $list = "<table><table><tr><th>Code</th><th>Name</th><th>Price</th></tr>";
// Create connection
$conn = new mysqli($servername, $username, $password);

for ($i = 1; $i < 9; $i++) {

    try {

        $client = new Client(['http_errors' => false]);
        // $client->setDefaultOption('verify',  $certificate);

        $response = $client->request('GET', $baseEndPoint, [
            'q' => urlencode(':relevance:browseAllStoresFacetOff:browseAllStoresFacetOff'),
            'page' => $i,
            'cookies'           => false,
            'verify'            => $certificate,
            'headers'           => [
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            ],
        ]);

        $crawler = new Crawler($response->getBody()->getContents());

        $crawler->filter('.item-card')->filter('h3')
            ->each(function (Crawler $nodeCrawler) {
                foreach($nodeCrawler as $nodeCraw) {
                    $list[] = [
                        'title' => $nodeCraw->nodeValue,
                    ];
                }



            });



    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
    }
}



