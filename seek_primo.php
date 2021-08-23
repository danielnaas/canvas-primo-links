<html>
    <head>
        <style>
            body {
                background-color: white;
            }
            
            p {
              color: white;  
              font-family: Arial;
            }
            
            a {
              color: blue;  
            }
            table {
                border: 1px solid black;
            }
            td {
                border: 1px solid black;
                width: 300px;
            }
        </style>
    </head>
    <body>
<h1>Canvas Course Review</h1>
<br/>
<?php




// Base URLs for Canvas API 
// Courses
$apiCourses = 'https://redacted/api/v1/courses?';
$paramCourses = 'enrollment_type=teacher&enrollment_role=TeacherEnrollment';

$apiModules = 'https://redacted/api/v1/courses/1792974/modules';
$paramModules = '';

$apiPages = 'https://redacted/api/v1/courses/1792974/pages';
$paramPages = '';

$apiDiscussions = 'https://redacted/api/v1/courses/1792974/discussion_topics';
$paramDiscussions = '';

$apiAssignments = 'https://redacted/api/v1/courses/1792974/assignments?per_page=100';
$paramAssignments = '';

// Function to get stuff from Canvas API
function callCanvas($api, $param) {
    $canvasUrl = $api . $param;
    
    // My Integration Token
    $canvasToken = 'Authorization: Bearer redacted';

    // create a new cURL resource
    
    $ch = curl_init();
    
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $canvasUrl);
    //curl_setopt($ch, CURLOPT_HTTPHEADER,array('Accept: application/json')); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $canvasToken ));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);

    
    // grab URL and pass it to the browser
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $canvasResponse = json_decode($response, true);

    return $canvasResponse;
}

//Get the course IDs

$courseList = callCanvas($apiCourses, $paramCourses);

$arrayCount = count($courseList); 

//echo '<br/>Number of Courses: ' . $arrayCount;

//for ($c=0; $c < $arrayCount; $c++) {
//    echo '<br/>Course ID: ' . $courseList[$c]["id"] . '  Course Name: ' . $courseList[$c]["name"];
//}

echo '<h2>course 1792974</h2>';
echo '<ul>';
// Using the Course ID to review various parts of a course

// MODULES //////////////////////////////////////////////////////////////////////
echo '<li><h3>Modules:</h3>';

// Get the list of modules

$moduleList = callCanvas($apiModules, $paramModules);

$arrayCount = count($moduleList); 
echo '<ul><li>Number of Modules: ' . $arrayCount;
echo '<ul>';
for ($c=0; $c < $arrayCount; $c++) {
    // CHECK ITEMS PER MODULE
    $moduleListUrl = 'https://redacted/api/v1/courses/1792974/modules/' . $moduleList[$c]["id"] . '/items?per_page=100';
    $moduleListParam = '';
    
    $moduleItems = callCanvas($moduleListUrl, $moduleParam);
    $countModuleItems = count($moduleItems);
    echo '<li>Module ID: ' . $moduleList[$c]["id"] . ' -- Module Name: ' . $moduleList[$c]["name"] . ' -- Module Item Count: ' . $countModuleItems . '</li>';
    // Go through module items
    echo '<ul>';
    for ($d=0; $d < $countModuleItems; $d++) {
        if (strpos($moduleItems[$d]["external_url"], 'saa-primo') > 0) {
            $checkPrimo = 'yes';
            echo '<li>Module Item ID: ' . $moduleItems[$d]["id"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
            $moduleUpdate = oneLink($moduleItems[$d]["external_url"]);
            echo 'MODNEWURL: ' . $moduleUpdate;
            $updateUrl = $apiModules . '/' . $moduleList[$c]["id"] . '/items/' . $moduleItems[$d]["id"];
            echo 'MODURL: ' . $updateUrl;
            $wikiArray = array("module_item"=>array("external_url"=> $moduleUpdate));
            $wikiJson = json_encode($wikiArray);
        
            //echo '<pre>';
            //var_dump($wikiJson);
            //echo '</pre>';
            //echo '<br/>' . $updateUrl;
            putPage($updateUrl, $wikiJson);
            
            
        } else {
            echo '<li>Module Item ID: ' . $moduleItems[$d]["id"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
            $checkPrimo = 'no';
        }
        
    }
    echo '</ul>';

}
echo '</ul>';

echo '</li></ul>';

echo '</li>';

// PAGES //////////////////////////////////////////////////////////////////////
echo '<li><h3>Pages:</h3>';

$pagesList = callCanvas($apiPages, $paramPages);

$arrayCount = count($pagesList); 
echo '<ul><li>Number of Pages: ' . $arrayCount;
echo '<ul>';

for ($c=0; $c < $arrayCount; $c++) {
    // CHECK INDIVIDUAL PAGE using page_id and course_id
    $pageUrl = 'https://redacted/api/v1/courses/1792974/pages/' . $pagesList[$c]["url"];
    $pageUrlParam = '';
    
    $pageContent = getPage($pageUrl);
    //var_dump($pageContent);
    
    if(strpos($pageContent, "saa-primo") > 0) {
        $checkPrimo = 'yes';
        echo '<li>Page ID: ' . $pagesList[$c]["page_id"] . ' -- Page Name: ' . $pagesList[$c]["title"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
        $bodyUpdate = processContentArea($pageContent);
        $updateUrl = $pagesList[$c]["url"];
        $wikiArray = array("wiki_page"=>array("url"=>$updateUrl,"body"=> $bodyUpdate));
        $wikiJson = json_encode($wikiArray);
        
        //echo '<pre>';
        //var_dump($wikiJson);
        //echo '</pre>';
        //echo '<br/>' . $updateUrl;
        putPage($pageUrl, $wikiJson);
    } else {
        $checkPrimo = 'no';
        echo '<li>Page ID: ' . $pagesList[$c]["page_id"] . ' -- Page Name: ' . $pagesList[$c]["title"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
    }
}

echo '</ul>';

echo '</li></ul>';

echo '</li>';

// DISCUSSION TOPICS //////////////////////////////////////////////////////////
echo '<li><h3>Discussion Topics:</h3>';
echo '<ul>';

$discussionsList = callCanvas($apiDiscussions, $paramDiscussions);

$arrayCount = count($discussionsList); 
echo '<li>Number of Discussion Topics: ' . $arrayCount;

echo '<ul>';

for ($c=0; $c < $arrayCount; $c++) {
    
    if(substr_count($discussionsList[$c]["message"], 'saa-primo') > 0) {
        $checkPrimo = 'yes';
        echo '<li>Discussion Topic ID: ' . $discussionsList[$c]["id"] . ' -- Discussion Topic Name: ' . $discussionsList[$c]["title"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
       
        $messageUpdate = processContentArea($discussionsList[$c]["message"]);
        $discussionId = $discussionsList[$c]["id"];
        $updateUrl = $apiDiscussions . '/' . $discussionId;
        //echo '<br/>UPDATE URL: ' . $updateUrl;
        //$wikiArray = array("wiki_page"=>array("id"=>$updateUrl,"message"=> $bodyUpdate));
        $wikiArray = array("id"=>$discussionId,"message"=> $messageUpdate);
        $wikiJson = json_encode($wikiArray);
        
        //echo '<pre>';
        //var_dump($wikiJson);
        //echo '</pre>';
        //echo '<br/>' . $updateUrl;
        putPage($updateUrl, $wikiJson);
    } else {
        $checkPrimo = 'no';
        echo '<li>Discussion Topic ID: ' . $discussionsList[$c]["id"] . ' -- Discussion Topic Name: ' . $discussionsList[$c]["title"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
    }

}

echo '</ul>';

echo '</li>';

echo '</ul>';

// ASSIGNMENTS ////////////////////////////////////////////////////////////////
echo '<li><h3>Assignments:</h3>';
$assignmentList = callCanvas($apiAssignments, $paramAssignments);

echo '<ul>';

$arrayCount = count($assignmentList); 
echo '<li>Number of Assignments: ' . $arrayCount;
echo '<ul>';

for ($c=0; $c < $arrayCount; $c++) {
    
    if(substr_count($assignmentList[$c]["description"], 'saa') > 0) {
        $checkPrimo = 'yes';
        echo '<li>Assignment ID: ' . $assignmentList[$c]["id"] . ' -- Assignment Name: ' . $assignmentList[$c]["name"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
        $descUpdate = processContentArea($assignmentList[$c]["description"]);
        $assignmentId = $assignmentList[$c]["id"];
        $updateUrl = 'https://redacted/api/v1/courses/1792974/assignments/' . $assignmentId;
        $wikiArray = array("assignment"=>array("id"=>$assignmentId,"description"=> $descUpdate));
        $wikiJson = json_encode($wikiArray);
        
        //echo '<pre>';
        //var_dump($wikiJson);
        //echo '</pre>';
        //echo '<br/>' . $updateUrl;
        putPage($updateUrl, $wikiJson);
    } else {
        $checkPrimo = 'no';
        echo '<li>Assignment ID: ' . $assignmentList[$c]["id"] . ' -- Assignment Name: ' . $assignmentList[$c]["name"] . ' -- Has Primo: ' . $checkPrimo . '</li>';
    }
    
    //$checkPrimo = str_contains($assignmentList[$c]["description"], 'saa');

}

echo '</li>';

echo '</ul>';


echo '<br/><br/>';

// Function to replace PID with MMS for Alma stuff
function pidToMms($primoId) {
        $url = "redacted" . $primoId . "&library=01SAA_UKY";
        $xml = simplexml_load_file($url);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        //echo"<br/><br/>JSON: " . print_r($json);
        $arrCount = count($array["OAI-PMH"]["ListRecords"]["record"]["metadata"]["record"]["controlfield"]) - 1;
        $MMS = $array["OAI-PMH"]["ListRecords"]["record"]["metadata"]["record"]["controlfield"][$arrCount];

        return $MMS;
}




// PROCESS BODY TEXT ///////////////////////////////////////////////////////////
function processContentArea($contentHtml) {
    
    $bodyStuff = $contentHtml;
    
    preg_match_all('/[\"]https:\/\/saa-primo\S*[\"]/', $contentHtml, $matches);
    //var_dump($matches[0]);
    
    
    
    for ($c=0; $c < count($matches[0]); $c++) {
        //echo '<br/><br/>Link: ' . $matches[0][$c];
        $oldLink = $matches[0][$c];
        // trying CDI first
        if (preg_match('/[tT][nN][_][a-zA-Z_0-9.\/\-]+/', $matches[0][$c], $linkId)) {
            //echo '<br/>ID: ' . substr($linkId[0], 3);
            // add ID to new link style
            // link base: https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=PC&vid=01SAA_UKY:UKY&docid=
            $newLink = '"https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=PC&vid=01SAA_UKY:UKY&docid=' . substr($linkId[0], 3) . '"';
        } else if (preg_match('/[uU][kK][yY][_][aA][lL][mM][aA][0-9]+/', $matches[0][$c], $linkId)) {
            //echo '<br/>ID: ' . substr($linkId[0], 8);
            // call the separate RTA function
            
            // link base: https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=L&vid=01SAA_UKY:UKY&docid=
            $newLink = '"https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=L&vid=01SAA_UKY:UKY&docid=alma' . pidToMms(substr($linkId[0], 8)) . '"';
        } else if (preg_match('/exploreuk.uky.edu\/[a-zA-Z_0-9]+/', $matches[0][$c], $linkId)) {
            // make this one the exploreuk just in case
            $newLink = '"https://saalck-uky.primo.exlibrisgroup.com/discovery/search?query=any,contains,' . $linkId[0] . '&tab=Everything&search_scope=Default&vid=01SAA_UKY:UKY&lang=en&offset=0"';
        } else {
            // do string replace on these bits to catch others: base url, vid, inst, tab, scope
            $oldParts = ['saa-primo.hosted.exlibrisgroup.com/primo-explore', 'vid=UKY', 'tab=default_tab', 'tab=alma_tab', 'tab=cr_tab', 'tab=exploreuk_tab', 'search_scope=default_scope', 'search_scope=alma_scope', 'search_scope=Course%20Reserves', 'search_scope=exploreuk_scope', 'lang=en_US'];
            $newParts = ['saalck-uky.primo.exlibrisgroup.com/discovery','vid=01SAA_UKY:UKY','tab=Everything', 'tab=LibraryCatalog', 'tab=CourseReserves', 'tab=LocalCollections','search_scope=Default', 'search_scope=MyInstitution', 'search_scope=CourseReserves', 'search_scope=ExploreUK', 'lang=en'];
        
            $newLink = str_replace($oldParts, $newParts, $matches[0][$c]);
        }
        echo '<br/>OLD: <a href=' . $oldLink . '>' . $oldLink . '</a>';
        echo '<br/><strong>NEW: </strong><a href=' . $newLink . '>' . $newLink . '</a>';
        $bodyStuff = str_replace($oldLink, $newLink, $bodyStuff);
        //echo '<br/><br/>New body: ' . $bodyStuff;
        
    }
    echo '<p>CONVERSION DONE</p>';
    return $bodyStuff;
}


// GET PAGE AND UPDATE PAGE FUNCTIONS //////////////////////////////////////////
function getPage($pageUrl) {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $pageUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array('Authorization: Bearer REMOVED'),
));

$response = curl_exec($curl);

curl_close($curl);
    //echo $response;
    $array = json_decode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    //$array = html_entity_decode($array);
    
    //echo '<br/>ARRAY CHECK<br/>';
    //var_dump($array);
    return $array["body"];
}



function putPage($pageUrl, $pageJson) {
    echo '<br/>STARTING PUT';
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $pageUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS => $pageJson,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer REMOVED',
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo '<br/>PUT RESPONSE: ';
var_dump($response);
}

function oneLink($oneLinkUrl) {
    $oldLink = $oneLinkUrl;
   if (preg_match('/[tT][nN][_][a-zA-Z_0-9.\/\-]+/', $oneLinkUrl, $linkId)) {
            //echo '<br/>ID: ' . substr($linkId[0], 3);
            // add ID to new link style
            // link base: https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=PC&vid=01SAA_UKY:UKY&docid=
            $newLink = 'https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=PC&vid=01SAA_UKY:UKY&docid=' . substr($linkId[0], 3);
        } else if (preg_match('/[uU][kK][yY][_][aA][lL][mM][aA][0-9]+/', $oneLinkUrl, $linkId)) {
            //echo '<br/>ID: ' . substr($linkId[0], 8);
            // call the separate RTA function
            
            // link base: https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=L&vid=01SAA_UKY:UKY&docid=
            $newLink = 'https://saalck-uky.primo.exlibrisgroup.com/discovery/fulldisplay?context=L&vid=01SAA_UKY:UKY&docid=alma' . pidToMms(substr($linkId[0], 8)) ;
        } else if (preg_match('/exploreuk.uky.edu\/[a-zA-Z_0-9]+/', $oneLinkUrl, $linkId)) {
            // make this one the exploreuk just in case
            $newLink = 'https://saalck-uky.primo.exlibrisgroup.com/discovery/search?query=any,contains,' . $linkId[0] . '&tab=Everything&search_scope=Default&vid=01SAA_UKY:UKY&lang=en&offset=0';
        } else {
            // do string replace on these bits to catch others: base url, vid, inst, tab, scope
            $oldParts = ['saa-primo.hosted.exlibrisgroup.com/primo-explore', 'vid=UKY', 'tab=default_tab', 'tab=alma_tab', 'tab=cr_tab', 'tab=exploreuk_tab', 'search_scope=default_scope', 'search_scope=alma_scope', 'search_scope=Course%20Reserves', 'search_scope=exploreuk_scope', 'lang=en_US'];
            $newParts = ['saalck-uky.primo.exlibrisgroup.com/discovery','vid=01SAA_UKY:UKY','tab=Everything', 'tab=LibraryCatalog', 'tab=CourseReserves', 'tab=LocalCollections','search_scope=Default', 'search_scope=MyInstitution', 'search_scope=CourseReserves', 'search_scope=ExploreUK', 'lang=en'];
        
            $newLink = str_replace($oldParts, $newParts, $matches[0][$c]);
        }
        echo '<br/>OLD: <a href=' . $oldLink . '>' . $oldLink . '</a>';
        echo '<br/><strong>NEW: </strong><a href=' . $newLink . '>' . $newLink . '</a>'; 
        
        return $newLink;
}

echo 'DONE REVIEWING COURSE';

?>


</body>
</html>