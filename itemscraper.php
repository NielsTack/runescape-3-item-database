<?php
$html =  file_get_contents('https://runescape.wiki/w/Calculator:Grand_Exchange_buying_limits#Z');
$dom = new DomDocument();
@ $dom->loadHTML($html);
$xpath = new DOMXpath($dom);
$tables = $xpath->query("//table[contains(@class,'wikitable')]");

$alphabetrange = range('a', 'z');
$categoryrange = range(1,37);
$pagerange = 1;
$items = [];



// loop over category and alphabet to get each item
foreach ($categoryrange as $category) {
    foreach ($alphabetrange as $alphabet) {
        // also loop over each page
        $pagerange = 1;
        $error = false;
        while($error == false){
          
            try {
                $start = microtime(true);
                $json = file_get_contents('https://secure.runescape.com/m=itemdb_rs/api/catalogue/items.json?category='.$category.'&alpha='.$alphabet.'&page='.$pagerange);
                $objects = json_decode($json, true);
                echo nl2br('succesfully parsed category='.$category.'&alpha='.$alphabet.'&page='.$pagerange."\n");
                $time_elapsed_secs = microtime(true) - $start;

                // sleep so content has time to load and not too many requests are made too soon after eachother
                if($time_elapsed_secs < 5){
                    sleep(5-$time_elapsed_secs);
                }

                // debugging
                // echo nl2br($time_elapsed_secs."\n");
                // echo nl2br('itemarray ='.$objects['items']."\n");
                // echo nl2br('type ='.gettype($objects['items'])."\n");
                // echo nl2br('count ='.count($objects['items'])."\n");

                // loop over each item in the page and push it as key value pair to items array
                if($objects['items']){
                    foreach ($objects['items'] as $object ) {
                        foreach($tables as $table) {
                            foreach($table->getElementsByTagName('tr') as $tablerow) {
                                error_reporting(0);
                                $itemname =  $tablerow->getElementsByTagName('td')[0]->nodeValue;
                                $buylimit = $tablerow->getElementsByTagName('td')[1]->nodeValue;
                                if($object['name'] == $itemname){
                                    $infoArray = array('name' => $object['name'],'buylimit' => $buylimit,'type' => $object['type'],'icon' => $object['icon'],'icon_large' => $object['icon_large'],'id' => intval($object['id']));
                                    $id = intval($object['id']);
                                    $items[$id] = $infoArray;
                                    // echo nl2br(json_encode($items, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) ."\n");
                                }
                            }
                        }
                    }
                    $pagerange++;
                } else {

                    $error = true;
                }

  
            } catch (Exception $e) {
                $error = true;
                
            }

        }
        

    }
}


sort($items);
// encode items array to json

$json = json_encode($items, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
//write json to file
if (file_put_contents("iteminfo.json", $json)) {
    echo nl2br("JSON file created successfully...\n");
} else {
    echo nl2br("Oops! Error creating json file...\n");
}

// in case writing to file didnt work for some reason, echo items so you dont have to start over
echo $json;



?>