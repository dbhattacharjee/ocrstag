<?php

/* THIS FILE IS RESPONSIBLE FOR SENDING CALLBACK DATA TO MAIN APPLICATION */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../lib/ocrweb.php';

$ocr = $app['controllers_factory'];


$ocr->post('/', function (Request $request) use ($app) {
            $returnCode = 400;
            if ($file = $request->files->get('imgFile')) {
                if (!$file->getError()) {
                    $returnCode = 200;
                    try {
                        $output['data'] = processOCRDataFromFile(getOCRText($file->getRealPath(), OCR_LICENSE_CODE, OCR_USERNAME));
                    } catch (\Exception $e) {
                        $returnCode = 400;
                        $output['data'] = $e->getMessage();
                    }
                } else {
                    $output['data'] = $file->getErrorMessage();
                }
            } else {
                $output['data'] = 'Please upload a file';
            }
            return new Response(json_encode($output), $returnCode);
        });

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

/*
  $ocr->post('/', function (Request $request) use ($app) {
  $returnCode = 400;
  if ($file = $request->files->get('imgFile')) {
  if (!$file->getError()) {
  $returnCode = 200;
  $tesseract = new \TesseractOCR($file->getRealPath());
  //$tesseract->setTempDir(__DIR__.'/temp-ocr');
  //                    echo $tesseract->recognize();die;
  $tesseract->setWhitelist(range('A', 'Z'), range(0, 9), '_-@.:');
  //print_r(explode(PHP_EOL, preg_replace('"(\r?\n){2,}"', PHP_EOL, $tesseract->recognize())));die;
  $output['data'] = processOCRData(explode(PHP_EOL, preg_replace('"(\r?\n){2,}"', PHP_EOL, $tesseract->recognize())));
  } else {
  $output['data'] = $file->getErrorMessage();
  }
  } else {
  $output['data'] = 'Please upload a file';
  }
  //return $output['data'];
  return new Response(json_encode($output), $returnCode);
  });


  function processOCRData($data) {

  $output = array();
  $trackingWord = 'TRACKING #:';
  $shiptoWord = 'SHIP TO:';

  if (!$cnt = count($data)) {
  return $output;
  }
  $recipientFound = $trackingCodeFound = false;

  for ($i = 0; $i < $cnt; $i++) {
  $data[$i] = trim($data[$i]);
  if ($data[0] != '' && $data[0] != $shiptoWord) {
  $output['SENDER'] = $data[0];
  }
  if (!$recipientFound) {
  if ($data[$i] == $shiptoWord) {
  for ($j = ($i + 1); $j < $cnt; $j++) {
  if ($data[$j] != '') {
  $output['RECIPIENT'] = $data[$j];
  $recipientFound = true;
  break;
  }
  }
  }
  }

  if (!$trackingCodeFound) {
  $temp = explode($trackingWord, $data[$i]);
  if (count($temp) > 1) {
  $output['TRACKING_CODE'] = trim($temp[1]);
  $trackingCodeFound = true;
  }
  }
  }
  return $output;
  }
 */

return $ocr;
