<?php

/* THIS FILE IS RESPONSIBLE FOR SENDING CALLBACK DATA TO MAIN APPLICATION */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$ocr = $app['controllers_factory'];

$ocr->post('/', function (Request $request) use ($app) {
            $returnCode = 400;
            if ($file = $request->files->get('imgFile')) {
                if (!$file->getError()) {
                    $returnCode = 200;
                    $tesseract = new \TesseractOCR($file->getRealPath());
                    //$tesseract->setTempDir(__DIR__.'/temp-ocr');
//                    echo $tesseract->recognize();die;
                    //$tesseract->setWhitelist(range('A', 'Z'), range(0, 9), '_-@.');
//print_r(explode(PHP_EOL, preg_replace('"(\r?\n){2,}"', PHP_EOL, $tesseract->recognize())));die;
                    $output['data'] = processOCRData(explode(PHP_EOL, preg_replace('"(\r?\n){2,}"', PHP_EOL, $tesseract->recognize())));
                } else {
                    $output['data'] = $file->getErrorMessage();
                }
            } else {
                $output['data'] = 'Please upload a file';
            }
            return $output['data'];
            //return new Response(json_encode($output), $returnCode);
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
    $html = '';
    $html = '<b>Sender :</b>';
    $html .= isset($output['SENDER']) ? $output['SENDER'] : 'NOT FOUND';
    $html .= '<br/><b>Recipient :</b>';
    $html .= isset($output['RECIPIENT']) ? $output['RECIPIENT'] : 'NOT FOUND';
    $html .= '<br/><b>Tracking Code :</b>';
    $html .= isset($output['TRACKING_CODE']) ? $output['TRACKING_CODE'] : 'NOT FOUND';
    return $html;
    //return $output;
}        

return $ocr;
