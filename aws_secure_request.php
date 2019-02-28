<?php
/**
* This is a demo script
* for calculate AWS 4 authorization signature
* and send request to API
* Author: Avik Das
* Date: 27 Feb, 2019
*/

/**
* This function is in use
* for calculate the authorization signature
*/
function calcualteAwsSignatureAndReturnHeaders($host, $uri, $requestUrl, 
			$accessKey, $secretKey, $region, $service, 
			$httpRequestMethod, $data, $debug = TRUE){

	$terminationString	= 'aws4_request';
	$algorithm 		= 'AWS4-HMAC-SHA256';
	$phpAlgorithm 		= 'sha256';
	$canonicalURI		= $uri;
	$canonicalQueryString	= '';
	$signedHeaders		= 'content-type;host;x-amz-date';

	$currentDateTime = new DateTime('UTC');
	$reqDate = $currentDateTime->format('Ymd');
	$reqDateTime = $currentDateTime->format('Ymd\THis\Z');

	// Create signing key
	$kSecret = $secretKey;
	$kDate = hash_hmac($phpAlgorithm, $reqDate, 'AWS4' . $kSecret, true);
	$kRegion = hash_hmac($phpAlgorithm, $region, $kDate, true);
	$kService = hash_hmac($phpAlgorithm, $service, $kRegion, true);
	$kSigning = hash_hmac($phpAlgorithm, $terminationString, $kService, true);

	// Create canonical headers
	$canonicalHeaders = array();
	$canonicalHeaders[] = 'content-type:application/x-www-form-urlencoded';
	$canonicalHeaders[] = 'host:' . $host;
	$canonicalHeaders[] = 'x-amz-date:' . $reqDateTime;
	$canonicalHeadersStr = implode("\n", $canonicalHeaders);

	// Create request payload
	$requestHasedPayload = hash($phpAlgorithm, $data);

	// Create canonical request
	$canonicalRequest = array();
	$canonicalRequest[] = $httpRequestMethod;
	$canonicalRequest[] = $canonicalURI;
	$canonicalRequest[] = $canonicalQueryString;
	$canonicalRequest[] = $canonicalHeadersStr . "\n";
	$canonicalRequest[] = $signedHeaders;
	$canonicalRequest[] = $requestHasedPayload;
	$requestCanonicalRequest = implode("\n", $canonicalRequest);
	$requestHasedCanonicalRequest = hash($phpAlgorithm, utf8_encode($requestCanonicalRequest));
	if($debug){
		echo "<h5>Canonical to string</h5>";
		echo "<pre>";
		echo $requestCanonicalRequest;
		echo "</pre>";
	}

	// Create scope
	$credentialScope = array();
	$credentialScope[] = $reqDate;
	$credentialScope[] = $region;
	$credentialScope[] = $service;
	$credentialScope[] = $terminationString;
	$credentialScopeStr = implode('/', $credentialScope);

	// Create string to signing
	$stringToSign = array();
	$stringToSign[] = $algorithm;
	$stringToSign[] = $reqDateTime;
	$stringToSign[] = $credentialScopeStr;
	$stringToSign[] = $requestHasedCanonicalRequest;
	$stringToSignStr = implode("\n", $stringToSign);
	if($debug){
		echo "<h5>String to Sign</h5>";
		echo "<pre>";
		echo $stringToSignStr;
		echo "</pre>";
	}

	// Create signature
	$signature = hash_hmac($phpAlgorithm, $stringToSignStr, $kSigning);

	// Create authorization header
	$authorizationHeader = array();
	$authorizationHeader[] = 'Credential=' . $accessKey . '/' . $credentialScopeStr;
	$authorizationHeader[] = 'SignedHeaders=' . $signedHeaders;
	$authorizationHeader[] = 'Signature=' . ($signature);
	$authorizationHeaderStr = $algorithm . ' ' . implode(', ', $authorizationHeader);


	// Request headers
	$headers = array();
	$headers[] = 'authorization:'.$authorizationHeaderStr;
	$headers[] = 'content-length:'.strlen($data);
	$headers[] = 'content-type: application/x-www-form-urlencoded';
	$headers[] = 'host: ' . $host;
	$headers[] = 'x-amz-date: ' . $reqDateTime;

	return $headers;
}// End calcualteAwsSignatureAndReturnHeaders

/**
* This function is in use
* for send request with authorization header
*/
function callToAPI($requestUrl, $httpRequestMethod, $headers, $data, $debug=TRUE)
{
	// Execute the call
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $requestUrl,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_POST => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => $httpRequestMethod,
	  CURLOPT_POSTFIELDS => $data,
	  CURLOPT_VERBOSE => 0,
	  CURLOPT_SSL_VERIFYHOST => 0,
	  CURLOPT_SSL_VERIFYPEER => 0,
	  CURLOPT_HEADER => false,
	  CURLINFO_HEADER_OUT=>true,
	  CURLOPT_HTTPHEADER => $headers,
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if($debug){
		$headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
		echo "<h5>Request</h5>";
		echo "<pre>";
		echo $headers;
		echo "</pre>";
	}

	curl_close($curl);

	if ($err) {
		if($debug){
			echo "<h5>Error:" . $responseCode . "</h5>";
			echo "<pre>";
			echo $err;
			echo "</pre>";
		}
	} else {
		if($debug){
			echo "<h5>Response:" . $responseCode . "</h5>";
			echo "<pre>";
			echo $response;
			echo "</pre>";
		}
	}
	
	return array(
		"responseCode" => $responseCode,
		"response" => $response,
		"error" => $err
	);
}// End callToAPI
