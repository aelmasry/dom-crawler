<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use Rap2hpoutre\FastExcel\FastExcel;
use Spatie\SslCertificate\SslCertificate;

if (empty($_GET['url'])) {
    die('Url not exists');
}

$base_uri = $_GET['url'];

$urlArray = parse_url($base_uri);
var_dump($urlArray);
die;

$certificate = __DIR__ . "/cacert.pem";
ini_set('curl.cainfo', $certificate);
ini_set('openssl.cafile', $certificate);

for ($i = 1; $i < 5; $i++)
{
    try {
        $client = new Client([
            'base_uri' => $base_uri,
            'http_errors' => false,
            'timeout' => 30,
            'verify' => $certificate,
            // 'proxy' => '140.227.211.47:8080',
            'debug' => false,
            // 'curl' => [
            //     CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            //     // 'CURLOPT_INTERFACE' => '193.34.132.82:80'
            //     CURLOPT_PROXY => '193.34.132.82:80',
            //     CURLOPT_HTTPPROXYTUNNEL => 1,
            // ]
        ]);
        // $client->setDefaultOption('verify',  $certificate);

        $response = $client->request('GET', $base_uri, [
            'q' => urlencode(':relevance:browseAllStoresFacetOff:browseAllStoresFacetOff'),
            'page' => $i,
            'allow_redirects' => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'protocols'       => ['https'], // only allow https URLs
                'track_redirects' => true,
            ],
            'headers'           => [
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            ],

        ]);

        $crawler = new Crawler($response->getBody()->getContents());

        $products = array();
        $productsInfo = $crawler
            ->filter('.product-frame')
            ->each(function (Crawler $nodeCrawler) {
                $productsInfo = json_decode($nodeCrawler->attr('data-product-ga'), true);

                $products[] = [
                    'id' => $productsInfo['id'],
                    'name' => $productsInfo['name'],
                    'price' => $productsInfo['price'],
                    'department' => 'Frozen Food',
                ];
            });

            sleep(10);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
    }
}


(new FastExcel($products))->export('file.xlsx');
