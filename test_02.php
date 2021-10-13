<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
// use Rap2hpoutre\FastExcel\FastExcel;
use Spatie\SslCertificate\SslCertificate;

$base_uri = null;
if (php_sapi_name() == "cli") {
    $base_uri = $argv[1];
}else {
    if (!empty($_GET['url'])) {
        $base_uri = $_GET['url'];
    }
}

if (is_null($base_uri)) {
    die('Url not exists');
}

$urlArray = parse_url($base_uri);
$hostName = $urlArray['host'];

// $certificate = SslCertificate::createForHostName($hostName);

// $certificateProperties = $certificate->getRawCertificateFields();
// var_dump($certificateProperties);
// $ipAddress = $certificate->getRemoteAddress();

// SslCertificate::download()
//     ->fromIpAddress($ipAddress)
//     ->forHost($hostName);

$certificate = __DIR__ . "/cacert.pem";

// ini_set('curl.cainfo', $certificate);
// ini_set('openssl.cafile', $certificate);

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

        $response = $client->request('GET', $base_uri, $options);

        var_dump($response);
        die;

        $crawler = new Crawler($response->getBody()->getContents());

        $productsInfo = $crawler
            ->filter('.product-frame')
            ->each(function (Crawler $nodeCrawler) {
                $productsInfo = json_decode($nodeCrawler->attr('data-product-ga'), true);
                var_dump($productsInfo);
                die;
                // $products[]= [
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
