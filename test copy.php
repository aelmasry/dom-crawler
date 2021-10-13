<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ .'/simplehtmldom/simple_html_dom.php';

// use Symfony\Component\DomCrawler\Crawler;

$certificate = __DIR__ . "/cacert.pem";
// ini_set('curl.cainfo', $certificate);
// ini_set('openssl.cafile', $certificate);

// if (empty($_GET['url'])) {
//     die('Url not exists');
// }

// $base_uri = $_GET['url'];

// $html = file_get_html($base_uri);
// // $html = file_get_contents($base_uri);
// echo $html;
// // foreach ($html->find('img') as $element) {
// //     var_dump($element);
// //     die;
// // }

$arrContextOptions = array(
    "ssl" => array(
        'cafile' => $certificate,
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
);

$response = file_get_contents("https://www.shoprite.co.za/c-2413/All-Departments/Food", false, stream_context_create($arrContextOptions));

echo $response;
