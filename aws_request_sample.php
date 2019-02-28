<?php
include('./aws_secure_request.php');

// Configuration values
$host					= '<host name>';
$accessKey	 			= '<access key>';
$secretKey 				= '<secret key>';
$region 				= '<region>';
$service 				= '<service>';

/**
* You should modify the script
* for
*	1. full request url
*	2. uri for AWS signature
*	3. request method GET / POST / PUT
* 	4. actual data of the request
* and call the above functions
*/
$requestUrl	= '<full url>';
$uri = '<method path>';
$httpRequestMethod = '<http verb>';

$data = json_encode(array());

$headers = calcualteAwsSignatureAndReturnHeaders($host, $uri, $requestUrl, 
			$accessKey, $secretKey, $region, $service, 
			$httpRequestMethod, $data, TRUE);

$result = callToAPI($requestUrl, $httpRequestMethod, $headers, $data, TRUE);

print_r($result);