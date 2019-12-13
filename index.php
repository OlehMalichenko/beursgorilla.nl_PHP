<?php

////////////////////////////////////////
//!!!observe the date format: dd-mm-yyyy
//!!!observeer het datumformaat: dd-mm-jjjj
$dateStart = '03-05-2019';
$dateEnd = '31-05-2019';
///////////////////////////////////////


//////////////////////////////////////
//!!!only professionals should change this code
//!!!alleen professionals zouden deze code moeten wijzigen
ini_set('max_execution_time', 1000);
header('Content-type: text/plain; charset=utf-8');
require_once 'phpQuery.php';

//call main function
createJSON();

function createJSON(){
    global $dateStart, $dateEnd;
    // format date
    $dateStartRev = trim(str_replace('-', '', $dateStart));
    $dateEndRev = trim(str_replace('-', '', $dateEnd));
    // get array from function
    $arr = getArrayForJson();
    // create JSON
    $JSON = json_encode($arr,JSON_UNESCAPED_UNICODE);
    // put .txt file in folder
    $check = file_put_contents("JSONs/JSON_" . $dateStartRev . "_" . $dateEndRev . ".txt", $JSON);
    // check process
    if ($check <> FALSE){
        echo 'Your data is in the "JSONs" folder.'.PHP_EOL.'The folder "JSONs" is located in the directory of this script.'.PHP_EOL.''.PHP_EOL.'Uw gegevens bevinden zich in de map "JSONs".'.PHP_EOL.'De map "JSONs" bevindt zich in de map van dit script.';
    } else {
        echo 'Error. Something went wrong.'.PHP_EOL.''.PHP_EOL.'Fout. Er ging iets mis.';
    }
}

function getArrayForJson(){
    global $dateStart, $dateEnd;
    $url = 'https://www.beursgorilla.nl/agendadata';
    // create POST request from date(global). PageSize - 1000
    $post = "datum=". $dateStart ."+tot+". $dateEnd ."&PageNumber=1&PageSize=1000&SortField=TODO&SortDirection=TODO&PageData=agendadata";
    $h = getHtmlFrom($url, $post);
    $html = phpquery::newDocument($h);
    $arr = array();
    $bloks = $html ->find('.blok');
    //the run in tag 'block's
    foreach ($bloks as $b){
        $arrBlokLi = array();
        $blok = pq($b);
        $blokName = (string)str_replace(' ', '-', trim($blok->find(':first')->text()));
        $blokLi = $blok->find('li');
        $id = 0;
        //the run in tag 'li's
        foreach ($blokLi as $l){
            $li = pq($l);
            // ignore this 'li' 
            if($li->hasClass('heading')){continue;}
            // put data in array
            $arrLi = [
                'datum' => (string)trim(str_replace(PHP_EOL, " ", $li->find('.datum')->text())),
                'tijd' => (string)trim(str_replace(PHP_EOL, " ", $li->find('.tijd')->text())),
                'agenda' => (string)trim(str_replace(PHP_EOL, " ", $li->find('.agenda')->text())),
                'plaats' => (string)trim(str_replace(PHP_EOL, " ", $li->find('.plaats')->text())) ];
            // put data in next UP array
            $arrBlokLi['item-' . $id] = $arrLi;
            $id++;
        }
        // put data in result array
        $arr[$blokName] = $arrBlokLi;
    }   
    return $arr;
}

function getHtmlFrom($url, $post, $cookiefile = 'tmp/cookie.txt'){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);   
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}