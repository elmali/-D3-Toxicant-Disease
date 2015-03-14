var SERVER = "php/parseData.php";
document.observe("dom:loaded", fetchFromToxicant);

/**
 * Get all toxicants in the DB
 * @return {obj}
 */
function getAllToxicants(){
    alert("Ok!");
    new Ajax.Request(SERVER, {
        method : "POST",
        parameters:{
            action : "getToxicants"
        },
        onSuccess: function(ajax){
            alert("P here");
            var toxicants = JSON.parse(ajax.responseText);
            console.log(toxicants);
        },
        onFailure: function(ajax){
            alert("getAllToxicants : Unexpected error");
        }
    });
}
/**
 * Get all toxicants in the DB
 * @return {obj}
 */
function fetchFromToxicant(){
    alert("Ok!");
    new Ajax.Request(SERVER, {
        method : "POST",
        parameters:{
            action : "fetchFromToxicant",
            filter : 2317
        },
        onSuccess: function(ajax){
            alert("P here");
            var toxicants = JSON.parse(ajax.responseText);
            console.log(toxicants);
        },
        onFailure: function(ajax){
            alert("getAllToxicants : Unexpected error");
        }
    });
}

/**
 * Get all toxicants in the DB
 * @return {obj}
 * Formate for data=
 *	{
 *		name:"Toxicants",
 *		children:[
 *			{name:"LEAD",size:"",ID:"" },
 *			{name:"COPPER",size:"",ID:"" }
 *		]
 *	}
 */
function getAllT(){
    var cities = TAFFY();
    $.ajax({
        url: "json/toxicants.json",
        success: function(data) {

            //data downloaded so we call parseJSON function
            //and pass downloaded data
            var json = $.parseJSON(data);
            //now json variable contains data in json format
            //let's display a few items
            console.log(json);
        }
    });
    //return data;
}

/**
 * Get all disease in the DB
 * @return {obj}  data
 * Formate for data=
 *	{
 *		name:"Diseases",
 *		children:[
 *			{name:"ADHD",size:"",ID:"" },
 *			{name:"",size:"",ID:"" }
 *		]
 *	}
 */
function getAllD(){
    //return data;
}

/**
 * Get filtered Toxicants list, filtered by tid and cid
 * @param  {array}	tid  // array of toxicant IDs which should be contained in data
 * @param  {array}	cid  // array of disease IDs. If one of memeber in tid does not have connection with cid at all,
 * 						 // this toxicant should not be returned in data.
 * @return {obj} data
 * Formate for data=
 *	{
 *		name:"Toxicants",
 *		children:[
 *			{name:"LEAD",size:"",ID:"" },
 *			{name:"COPPER",size:"",ID:"" }
 *		]
 *	}
 */
function getFilteredT(tid,cid){
    //return data;
}

/**
 * Get filtered Diseases list, filtered by tid and cid
 * @param  {array} tid // array of toxicant IDs. Members in cid should have relattion with memeber in tid
 * @param  {array} cid // array of Diseases IDs which should contained in data.
 * @return {obj} data
 * Formate for data=
 *	{
 *		name:"Diseases",
 *		children:[
 *			{name:"ADHD",size:"",ID:"" },
 *			{name:"",size:"",ID:"" }
 *		]
 *	}
 */
function getFilterdD(tid,cid){
    //return data;
}

function getTwithD(tid,cid){

}

function getDwithT(tid,cid){

}