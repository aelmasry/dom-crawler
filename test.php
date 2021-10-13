<?php
// # #!/usr/bin/php
set_time_limit(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

$certificate = __DIR__ . "/cacert.pem";
ini_set('curl.cainfo', $certificate);
ini_set('openssl.cafile', $certificate);

if (isset($argv)) {
    $base_uri = $argv[1];
} else {
    if (empty($_GET['url'])) {
        die('Url not exists');
    }

    $base_uri = $_GET['url'];
}

try {

    $client = new Client(['base_uri' => $base_uri]);

    $options = [
        'base_uri' => $base_uri,
        'http_errors' => true,
        'verify' => $certificate,
        // 'connect_timeout' => 20,
        // 'read_timeout' => 20,
        // 'timeout' => 2,
        'debug' => false,
        'protocols'       => ['https'], // only allow https URLs
        'cookies'         => false,
    ];

    $response = $client->request('GET', $base_uri, $options);

    $crawler = new Crawler($response->getBody()->getContents());

    $productsInfo = $crawler
        ->filter('.item-card')->filter('.to-li-sm')
        ->each(function (Crawler $nodeCrawler) {
            foreach($nodeCrawler as $nodes) {
                echo '<p>'.$nodes->nodeValue.'</p>';
            }
        });

} catch (GuzzleHttp\Exception\ClientException $e) {
    $response = $e->getResponse();
    $responseBodyAsString = $response->getBody()->getContents();
}
