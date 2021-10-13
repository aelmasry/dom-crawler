<?php
set_time_limit(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$certificate = __DIR__ . "/cacert.pem";
ini_set('curl.cainfo', $certificate);
ini_set('openssl.cafile', $certificate);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;
// use Rap2hpoutre\FastExcel\FastExcel;

if (isset($argv)) {
    $urlParam = $argv[1];
} else {
    if (empty($_GET['url'])) {
        die('Url not exists');
    }

    $urlParam = $_GET['url'];
}



for ($i = 50; $i <= 79; $i++) {
    $base_uri = urldecode($urlParam . '?q=%3Arelevance%3AbrowseAllStoresFacetOff%3AbrowseAllStoresFacetOff&page=' . $i);

    try {

        $client = new Client(['base_uri' => $base_uri]);

        $options = [
            'base_uri' => $base_uri,
            'http_errors' => true,
            'verify' => $certificate,
            // 'connect_timeout' => 20,
            // 'read_timeout' => 20,
            // 'timeout' => 2,
            // 'proxy' => '195.182.152.238:38178',
            'debug' => false,
            'curl' => [
                CURLOPT_CAINFO => $certificate,
                CURLOPT_SSL_VERIFYPEER => true,
                // CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                //     // 'CURLOPT_INTERFACE' => '193.34.132.82:80'
                // CURLOPT_PROXY => '119.28.155.202:9999',
                //     CURLOPT_HTTPPROXYTUNNEL => 1,
                // CURLOPT_HTTPHEADER => [
                //     'X-FORWARDED-FOR' => '8.8.8.8',
                //     'CLIENT-IP' => $_SERVER['REMOTE_ADDR'],
                // ],
            ],
            'protocols'       => ['https'], // only allow https URLs
            'cookies'         => false,
            // 'headers'  => [
            //     'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            //     'X-FORWARDED-FOR' => '8.8.8.8',
            //     'CLIENT-IP' => $_SERVER['REMOTE_ADDR'],
            // ],
        ];

        $response = $client->request('GET', $base_uri, $options);

        $crawler = new Crawler($response->getBody()->getContents());

        $conn = new mysqli('127.0.0.1', 'root', 'root', 'test') or die("Connect failed: %s\n" . $conn->error);

        $productsInfo = $crawler
            ->filter('.product-frame')
            ->each(function (Crawler $nodeCrawler) use ($conn) {
                $productsInfo = json_decode($nodeCrawler->attr('data-product-ga'), true);
                // $products[]= [
                //     'id' => $productsInfo['id'],
                //     'name' => $productsInfo['name'],
                //     'price' => $productsInfo['price'],
                // ];

                $sql = "INSERT INTO products (code, name, price, department)
                        VALUES ('" . $productsInfo['id'] . "', '" . $productsInfo['name'] . "', '" . $productsInfo['price'] . "', 'Baby')";

                $conn->query($sql);
            });

        sleep(5);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
    }
}
