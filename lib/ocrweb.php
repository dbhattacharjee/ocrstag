<?php

function getOCRText($filePath, $license_code, $username) {


    $url = 'http://www.ocrwebservice.com/restservices/processDocument?gettext=false&outputformat=txt';

// Extraction text with English and german language using zonal OCR
// $url = 'http://www.ocrwebservice.com/restservices/processDocument?language=english,german&zone=0:0:600:400,500:1000:150:400';
// Convert first 5 pages of multipage document into doc and txt
// $url = 'http://www.ocrwebservice.com/restservices/processDocument?language=english&pagerange=1-5&outputformat=doc,txt';
// Full path to uploaded document

    $fp = fopen($filePath, 'r');
    $session = curl_init();

    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_USERPWD, "$username:$license_code");

    curl_setopt($session, CURLOPT_UPLOAD, true);
    curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($session, CURLOPT_TIMEOUT, 200);
    curl_setopt($session, CURLOPT_HEADER, false);


// For SSL using
//curl_setopt($session, CURLOPT_SSL_VERIFYPEER, true);
// Specify Response format to JSON or XML (application/json or application/xml)
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    curl_setopt($session, CURLOPT_INFILE, $fp);
    curl_setopt($session, CURLOPT_INFILESIZE, filesize($filePath));

    $result = curl_exec($session);

    $httpCode = curl_getinfo($session, CURLINFO_HTTP_CODE);
    curl_close($session);
    fclose($fp);

    if ($httpCode == 401) {
        throw new Exception('Unauthorized request');
    }

    // Output response
    $data = json_decode($result);

    if ($httpCode != 200) {
        // OCR error
        throw new Exception($data->ErrorMessage);
    }

    return $data->OutputFileUrl;
}

?>