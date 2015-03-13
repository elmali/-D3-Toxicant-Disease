<?php
include 'global.php';

function getDC(){
	$conn->query("SELECT * FROM toxins_category;");
}

function getAllContaminantUI(){
	global $conn;
	$result = $conn->query("select count(distinct r.disease_id) as number, s.name as name, s.ID as id FROM toxins_links as r LEFT join toxins_contaminant as s ON r.contaminant_id=s.ID group by r.contaminant_id");
	
	$data = array("name"=>"Contaminant", "children" => array());
	$toxins_category = array();
	while($rows = $result->fetch_assoc()){
		$tempData = array('name' => $rows['name'], 'size' => $rows['number'], 'id' => $rows['number']);
		array_push($data['children'], $tempData);
		
	}
	echo json_encode($data);
}

?>