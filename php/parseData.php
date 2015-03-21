<?php
include 'global.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST["action"];
    $filter = $_POST["filter"];
}else{
    $action = $_REQUEST['action'];
    $filter = $_REQUEST['filter'];
}

switch ($action) {
    case 'getDiseaseCategories':
        /*
         * Pass POST variable "action" > 'getDiseaseCategories'
         * POST variable "filter" > NA
        */
        getDiseaseCategories();
        break;
    case 'getToxicants':
        /*
         * Pass POST variable "action" > 'getToxicants'
         * POST variable "filter" > NA
        */
        getAllContaminants();
        break;
    case 'getDiseases':
        /*
         * Pass POST variable "action" > 'getDiseases'
         * POST variable "filter" > NA
        */
        getAllDiseases();
        break;
    case 'fetchFromDiseaseCategory':
        /*
         * Pass POST variable "action" > 'fetchFromDiseaseCategory'
         * Pass POST variable "filter" > Disease Category ID
        */
        fetchFromDiseaseCategory();
        break;
    case 'fetchFromToxicant':
        /*
         * Pass POST variable "action" > 'fetchFromToxicant'
         * Pass POST variable "filter" > Toxicant ID
        */
        fetchFromToxicant();
        break;
    case 'fetchFromDisease':
        /*
         * Pass POST variable "action" > 'fetchFromDisease'
         * Pass POST variable "filter" > Disease ID
        */
        fetchFromDisease();
        break;
    case 'fetchReferencesOfDisease':
        /*
         * Pass POST variable "action" > 'fetchReferencesOfDisease'
         * Pass POST variable "filter" > Disease ID
        */
        fetchDiseaseReference();
        break;
    case 'getFilterToxicantsByDC':
        getFilterToxicantsByDC();
        break;
}

/**
 * This method returns the data required for high level view.
 * Output:
 *      List All "Toxicants" with corresponding
 *                  "id" (Toxicant ID) [Unique identifier]
 *                  "name" (Name of toxicant), [Display name in bubble]
 *                  "size" (Number of diseases), [Determining factor for Size/Color of bubble]
 * Sample:
 * {
 *  "name":"Contaminants",
 *  "children":[
 *          {"name":"metals","size":"9","id":"2317"},
 *          {"name":"arsine","size":"3","id":"2318"}
 *          ]
 * }
 */
function getAllContaminants(){
    global $conn;

//     $filename = __DIR__.'/../json/Contaminants.json';

//     if(file_exists($filename)){
//         $data = file_get_contents($filename);
//         unlink($filename);
//     }
//    else{
    $stmt = 'SELECT count(distinct r.disease_id) as size, s.name as name, s.ID as id
                    FROM toxins_links as r LEFT join toxins_contaminant as s ON r.contaminant_id=s.ID
                    group by r.contaminant_id';
    $result = $conn->query($stmt);

    $data = array("name"=>"Contaminants", "children" => array());
    while($rows = $result->fetch_assoc()){
        $tempData = array('name' => $rows['name'], 'size' => $rows['size'], 'id' => $rows['id']);
        array_push($data['children'], $tempData);
    }
    // $fp = fopen($filename, "w") or die("can't open file");
    // fwrite($fp, json_encode($data));
    // fclose($fp);
//    }
    echo json_encode($data);
}

/**
 * This method returns the data required for all views.
 * Output:
 *      List all Disease Categories with corresponding
 *                  "id" (DiseaseCategory ID) [Unique identifier]
 *                  "name" (Name of disease category), [Display name in bubble]
 * Sample:
 * {
 *  "name":"Category",
 *  "children":[
 *          {"id":"136","name":"Renal (Kidney)"},
 *          {"id":"137","name":"Cardio-vascular"}
 *          ]
 * }
 */
function getDiseaseCategories(){
    global $conn;

    $filename = __DIR__.'/../json/DiseaseCategories.json';

    if(file_exists($filename)){
//        $data = file_get_contents($filename);
        unlink($filename);
    }
//    else{

    $stmt = 'SELECT ID as id, name FROM toxins_category';
    $result = $conn->query($stmt);

    $_category = array("name"=>"Category", "children"=> array());
    while($rows = $result->fetch_assoc()){
        $_ = array('id' => $rows['id'], 'name' => $rows['name']);
        array_push($_category['children'], $_);
    }

    $fp = fopen($filename, "w") or die("can't open file");
    fwrite($fp, json_encode($_category));
    fclose($fp);
//    }
    echo json_encode($_category);
}

/**
 * This method returns the data required for deeper view.
 * Output:
 *      List "Diseases" related to a particular Disease Category
 *                  "id" (Disease ID) [Unique identifier]
 *                  "name" (Name of disease), [Display name in bubble]
 *                  "size" (Number of toxicants), [Determining factor for Size/Color of bubble]
 *                  "notes" (Description of disease), [Tooltip content]
 * Sample:
 * {
 *  "name":"DiseaseByDC",
 *  "children":[
 *          {"id":620,"name":"Acute tubular necrosis","notes":"Cigar...","size":42}
 *          ]
 * }
 */
function fetchFromDiseaseCategory(){
    global $conn;
    global $filter;

    $filename = __DIR__.'/../json/tempDC.json';

    if(file_exists($filename)){
        unlink($filename);
    }
//    else{

    $stmt = $conn->prepare('SELECT D.ID as id, D.name as name, D.notes as notes, count(L.contaminant_id) as size
                    FROM toxins_disease as D, toxins_disease_category as C, toxins_links as L
                    WHERE D.ID = C.did AND C.did = L.disease_id AND C.cid = ?
                    GROUP BY D.ID');
    $stmt->bind_param('i', $filter);
    $stmt->execute();
    $notes ="";
    $size ="";
    $stmt->bind_result($id, $name, $notes, $size);

    $data = array("name"=>"DiseaseByDC", "children"=> array());
    while($stmt->fetch()){
        $_ = array('id' => $id, 'name' => $name, 'notes' => $notes, 'size' => $size);
        array_push($data['children'], $_);
    }

    $fp = fopen($filename, "w") or die("can't open file");
    fwrite($fp, json_encode($data));
    fclose($fp);
//    }
    echo json_encode($data);
}

/**
 * This method returns the data required for homepage or high-level view.
 * Output:
 *      List all "Diseases" with corresponding
 *                  "id" (Disease ID) [Unique identifier]
 *                  "name" (Name of disease), [Display name in bubble]
 *                  "size" (Number of toxicants), [Determining factor for Size/Color of bubble]
 *                  "notes" (Description of disease), [Tooltip content]
 * Sample:
 * {
 *  "name":"Diseases",
 *  "children":[
 *          {"id":"620","name":"Acute tubular necrosis","size":"44","notes":"Cigarette smoker.."}
 *      ]
 * }
 */
function getAllDiseases(){
    global $conn;
    $filename = __DIR__.'/../json/Diseases.json';

    if(file_exists($filename)){
        //    $data = file_get_contents($filename);
        unlink($filename);
    }
//    else{
    $stmt = 'SELECT D.ID as id, D.name as name, D.notes as notes, count(L.contaminant_id) as size
                    FROM toxins_disease as D, toxins_links as L
                    WHERE D.ID = L.disease_id
					GROUP BY D.ID';
//                    "SELECT D.ID as id, D.name as name, D.notes as notes, R.name as ref
//                    FROM toxins_disease as D, toxins_disease_reference as RD, toxins_reference as R
//                    WHERE D.ID = RD.did AND RD.rid = R.ID";
    $result = $conn->query($stmt);

    $data = array("name"=>"Diseases", "children" => array());

    while($rows = $result->fetch_assoc()){
        $tempData = array('id' => $rows['id'], 'name' => $rows['name'], 'size' => $rows['size'], 'notes' => $rows['notes']);
        array_push($data['children'], $tempData);
    }
    $fp = fopen($filename, "w") or die("can't open file");
    fwrite($fp, json_encode($data));
    fclose($fp);
    //   }
    echo json_encode($data);
}

/**
 * This method returns the data required for deeper view.
 * Output:
 *      List all "Diseases" related to a particular Toxicants
 *                  "id" (Disease ID) [Unique identifier]
 *                  "name" (Name of disease), [Display name in bubble]
 *                  "notes" (Description of disease),
 *                  "strength" (1: Strong, 2: Good, 3: Limited)
 * Sample:
 * {
 *  "name":"DiseasesByToxicant",
 *  "children":[
 *          {"id":790,"name":"Hearing loss","notes":"Co-exposure to noise and solvents may have a combined effect on hearing loss.","strength":1}
 *      ]
 * }
 */
function fetchFromToxicant(){
    global $conn;
    global $filter;

    // $filename = __DIR__.'/../json/tempT.json';

    // if(file_exists($filename)){
    //     unlink($filename);
    // }
//    else{

    $stmt = $conn->prepare('SELECT D.ID as id, D.name as name, D.notes as notes, L.evidence as strength
                                    FROM toxins_disease as D, toxins_links as L
                                    WHERE D.ID = L.disease_id AND L.contaminant_id = ?');
    $stmt->bind_param('i', $filter);
    $stmt->execute();
    $str ="";
    $notes ="";
    $stmt->bind_result($id, $name, $notes, $str);

    $data = array("name"=>"DiseasesByToxicant", "children"=> array());
    while($stmt->fetch()){
        $_ = array('id' => $id, 'name' => $name, 'notes' => $notes, 'size' => $str);
        array_push($data['children'], $_);
    }

    // $fp = fopen($filename, "w") or die("can't open file");
    // fwrite($fp, json_encode($data));
    // fclose($fp);
    //   }
    echo json_encode($data);
}

/**
 * This method returns the data required for deeper view.
 * Output:
 *      List all "Toxicants" related to a particular Disease
 *                  "id" (Toxicant ID) [Unique identifier]
 *                  "name" (Name of toxicant), [Display name in bubble]
 *                  "strength" (1: Strong, 2: Good, 3: Limited)
 * Sample:
 * {
 *  "name":"ToxicantsByDisease",
 *  "children":[
 *          {"id":2317,"name":"metals","strength":1},
 *          {"id":2318,"name":"arsine","strength":1}
 *          ]
 * }
 */
function fetchFromDisease(){
    global $conn;
    global $filter;

    $filename = __DIR__.'/../json/tempD.json';

    if(file_exists($filename)){
        unlink($filename);
    }
//    else{

    $stmt = $conn->prepare("SELECT C.ID as id, C.name as name, L.evidence as strength
                                  FROM toxins_contaminant as C, toxins_links as L
                                  WHERE C.ID = L.contaminant_id AND L.disease_id = ?");
    $stmt->bind_param('i', $filter);
    $stmt->execute();
    $str ="";
    $stmt->bind_result($id, $name, $str);

    $data = array("name"=>"ToxicantsByDisease", "children"=> array());
    while($stmt->fetch()){
        $d = array('id' => $id, 'name' => $name, 'strength' => $str);
        array_push($data['children'], $d);
    }

    $fp = fopen($filename, "w") or die("can't open file");
    fwrite($fp, json_encode($data));
    fclose($fp);
    //   }
    echo json_encode($data);
}

/**
 * This method returns reference list related to a particular disease
 * Output:
 *      List all "Reference Links" related to a particular Disease
 *                  "id" (Reference ID) [Unique identifier]
 *                  "name" (Description), [Display name in bubble]
 */
function fetchDiseaseReference(){
//TODO
}

/**
 * Get filteted toxicants results by disease category
 *
 */
function getFilterToxicantsByDC(){
    global $conn;
    global $filter;
    if($filter){
        $cstring = implode(",",$filter);

        $stmt = 'SELECT count(distinct r.disease_id) as size, s.name as name, s.ID as id ,r.disease_id
                                FROM toxins_links as r LEFT join toxins_contaminant as s ON r.contaminant_id=s.ID
                                WHERE r.disease_id in (SELECT did FROM toxins_disease_category WHERE cid in ('.$cstring.'))
                                group by r.contaminant_id';

        $result = $conn->query($stmt);
        $data = array("name"=>"Contaminants", "children" => array());
        while($rows = $result->fetch_assoc()){
            $tempData = array('name' => $rows['name'], 'size' => $rows['size'], 'id' => $rows['id']);
            array_push($data['children'], $tempData);
        }

        echo json_encode($data);
    }
    else{
        $data = array("name"=>"Contaminants", "children" => array());
        echo json_encode($data);
    }
}


?>