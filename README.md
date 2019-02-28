<h1>Amazon API Gateway & Authentication</h1>
<h2>Access using PHP</h2>

Here trying to demonstrate a that how to calculate signature for AWS Authorization.

<b>I refer this link to implement the steps as describe</b>
https://docs.aws.amazon.com/general/latest/gr/sigv4_signing.html

<h2>How to use?</h2>
Step 1: Include the file where calculation and cURL implemented within two function.
<pre>
include('./aws_secure_request.php');
</pre>
Step 2: Set the values in configuration variable.
<pre>
// Configuration values
$host = '&lt;your host name&gt;';
$accessKey = '&lt;access key&gt;';
$secretKey = '&lt;secret key&gt;';
$region = '&lt;region&gt;';
$service = '&lt;service&gt;';

$requestUrl	= '&lt;full url&gt;';
$uri = '&lt;method path&gt;';
$httpRequestMethod = '&lt;http verb&gt;';
</pre>
<p>
    <i>For example <br/>
    $region = 'ap-southeast-1'<br/>
    $service = 'execute-api'<br/>
    $requestUrl = 'https://host-domain/object/identifier'<br/>
    $uri = '/object/identifier'<br/>
    $httpRequestMethod = 'PUT'
    </i>
</p>
Step 3: Prepare data to send to API.
<pre>
$data = json_encode(array(
    "username" => "sample-demo",
    "firstName" => "Sample",
    "lastName" => "Demo",
    "date_of_birth" => "1999-05-08"
));
</pre>
Step 4: Now call the method to generate signature get all headers need to send.
<pre>
$headers = 
    calcualteAwsSignatureAndReturnHeaders(
        $host, $uri, $requestUrl, 
        $accessKey, $secretKey, $region
        $service, $httpRequestMethod, 
        $data, TRUE);
</pre>
Step 5: Final step to call API using cURL.
<pre>
$result = 
    callToAPI(
        $requestUrl, $httpRequestMethod, 
        $headers, $data, TRUE);
</pre>
Note: Last parameter as <b>TRUE</b> in above two function can be use to show <b>DEBUG</b> information, otherwise just send FALSE.

You can use <i>aws_request_sample.php<i> to start.
