<?php
    $itemsInfoHTML = file_get_contents('iteminfo.json');
    $itemsInfo = json_decode($itemsInfoHTML, true);
    $knownoverlap = array(49404, ... 12561);


    $items = [];

    foreach ($itemsInfo as $iteminfo) {
     
        $id = $iteminfo["id"];
        echo "current item info: ".json_encode($iteminfo)."<br>";

        if(!in_array($id, $knownoverlap)) {
            try {
                $start = microtime(true);
                $itemGraphHTML = file_get_contents('https://secure.runescape.com/m=itemdb_rs/api/graph/'.$id.'.json');
                $itemGraph = json_decode($itemGraphHTML, true);
                $max = 0;
                $min = 99999999;
                $todaysPrice = $itemGraph['daily'][array_key_last($itemGraph['daily'])];
                $time_elapsed_secs = microtime(true) - $start;
                if($time_elapsed_secs < 5){
                    sleep(5-$time_elapsed_secs);
                }
                foreach ($itemGraph['daily'] as $date => $price) {
                    $max = ($price > $max) ? $price : $max;
                    $min = ($price < $min) ? $price : $min;
                }
                echo "max:".$max." min:".$min." todays price:".$todaysPrice."<br>";
                if(($max*.9 > $min*1.1) && $todaysPrice*$iteminfo["buylimit"] > 2000000){
                    $items[$id] = $iteminfo;
                    echo "pushed item<br><br>";
                } else{
                    echo "did not push item<br><br>";
                }
            } catch (Exception $e) {

            }
        } else {
            echo 'item already in overlap <br><br>';
        }
    }

    sort($items);
    // encode items array to json
    $json = json_encode($items, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

    //write json to file
    if (file_put_contents("iteminfo_filtered.json", $json)) {
        echo nl2br("JSON file created successfully...\n");
    } else {
        echo nl2br("Oops! Error creating json file...\n");
    }

    // in case writing to file didnt work for some reason, echo items so you dont have to start over
    echo $json;

?>