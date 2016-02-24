<?php

function processOCRDataFromFile($filePath) {
    
    $output = array();
    $trackingWord = array('TRACKING #:', 'TRACKING //:', 'TRACKING s:');
    $shiptoWord = array('SHIP TO:', 'SHIP');
    $data = explode(PHP_EOL, file_get_contents($filePath));
    
    if (!$cnt = count($data)) {
        return $output;
    }
    $recipientFound = $trackingCodeFound = false;

    for ($i = 0; $i < $cnt; $i++) {
        $data[$i] = trim($data[$i]);
        $data[$i] = filter_var($data[$i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        if ($data[0] != '' && !in_array($data[0], $shiptoWord)) {
            $output['SENDER'] = $data[0];
        }

        if (!$recipientFound) {
            if (findMatch($shiptoWord, $data[$i])) {
                for ($j = ($i); $j < $cnt; $j++) {
                    $data[$j] = trim($data[$j]);
                    $data[$j] = filter_var($data[$j], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                    if ($data[$j] != '' && !in_array($data[$j], $shiptoWord)) {
                        $output['RECIPIENT'] = trim(str_replace($shiptoWord, '', $data[$j]));
                        $recipientFound = true;
                        break;
                    }
                }
            }
        }
        
        if (!$trackingCodeFound) {
            if (findMatch($trackingWord, $data[$i])) {
                $output['TRACKING_CODE'] = trim(str_replace($trackingWord, '', $data[$i]));
                $trackingCodeFound = true;
            }
        }
    }
    return $output;
}

function findMatch($matchList, $data) {
    $flag = false;
    foreach($matchList as $word) {
        if(stripos($data, $word) !== false) {
            $flag = true;
            break;
        }
    }
    return $flag;
}

$ret = processOCRDataFromFile('http://localhost/ocr/web/file5.txt');

echo '<pre>';print_r($ret);die;
?>
