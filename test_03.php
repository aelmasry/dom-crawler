<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;
// use Rap2hpoutre\FastExcel\FastExcel;


if (empty($_GET['url'])) {
    die('Url not exists');
}

$base_uri = $_GET['url'];

$certificate = __DIR__ . "/cacert.pem";
ini_set('curl.cainfo', $certificate);
ini_set('openssl.cafile', $certificate);

// $list = "<table><table><tr><th>Code</th><th>Name</th><th>Price</th></tr>";

for ($i = 1; $i < 9; $i++)
{
    try {

        $client = new Client(['base_uri' => $base_uri]);

        $options = [
            'base_uri' => $base_uri,
            'http_errors' => false,
            'force_ip_resolve' => 'v4',
            // 'connect_timeout' => 20,
            // 'read_timeout' => 20,
            // 'timeout' => 20,
            'verify' => $certificate,
            // 'proxy' => '195.182.152.238:38178',
            'debug' => true,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                //     // 'CURLOPT_INTERFACE' => '193.34.132.82:80'
                CURLOPT_PROXY => '119.28.155.202:9999',
                //     CURLOPT_HTTPPROXYTUNNEL => 1,
            ],
            'q' => urlencode(':relevance:browseAllStoresFacetOff:browseAllStoresFacetOff'),
            'page' => $i,
            'max'             => 10,        // allow at most 10 redirects.
            'strict'          => true,      // use "strict" RFC compliant redirects.
            'referer'         => true,      // add a Referer header
            'protocols'       => ['https'], // only allow https URLs
            'cookies'         => false,
            'headers'  => [
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            ],
        ];

        // $request = $client->request('GET', $base_uri, $options);

        // $promise = $client->sendAsync($request)->then(function ($response) {
        //     echo 'I completed! ' . $response->getBody();
        // });
        // $promise->wait();
        // var_dump($response->getBody());
        // die;

        $response = $client->request('GET', $base_uri, $options);
        var_dump($response->getStatusCode());
        die;

        $crawler = new Crawler($response->getBody()->getContents());

        $conn = new mysqli('127.0.0.1', 'root', 'root', 'test') or die("Connect failed: %s\n" . $conn->error);

        $productsInfo = $crawler
            ->filter('.product-frame')
            ->each(function (Crawler $nodeCrawler) use($conn) {
                $productsInfo = json_decode($nodeCrawler->attr('data-product-ga'), true);
                $sql = "INSERT INTO products (code, name, price, department)
                        VALUES ('".$productsInfo['id']. "', '" . $productsInfo['name'] . "', '".$productsInfo['price'] . "', 'Frozen Food')";

                $conn->query($sql);
                // $list[]= [
                //     'id' => $productsInfo['id'],
                //     'name' => $productsInfo['name'],
                //     'price' => $productsInfo['price'],
                // ];
            });

            sleep(10);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
    }
}
