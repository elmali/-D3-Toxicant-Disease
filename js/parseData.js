var GET = "php/parseData.php";
//document.observe("dom:loaded", fetchFromToxicant);

/**
 * Get all toxicants from parseData.php via ajax
 * @param callback
 */
function getAllToxicants(callback){
    $.ajax({
        url: GET,
        data:{
            action:"getToxicants"
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    callback(result);
                }catch(e){
                    console.log(e); //error
                }
            }
        }
    });
}

/**
 * Get all diseases of given toxicant id from parseData.php via ajax
 * @param tid
 * @param callback
 */
function fetchFromToxicant(tid, callback){
    $.ajax({
        url: GET,
        data:{
            action:"fetchFromToxicant",
            filter:tid
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    callback(result);
                }catch(e){
                    console.log(e); //error
                }
            }
        }
    });
}

/**
 * Get filteted toxicants results by given disease category from parseData.php via ajax
 * @param filter
 * @param callback
 */
function getFilterToxicantsByDC(filter, callback){
    $.ajax({
        url: GET,
        data:{
            action:"getFilterToxicantsByDC" ,
            filter:filter
        },
        success: function(response){
            var result = JSON.parse(response);
            callback(result);
        }
    });
}

/**
 * Get all disease categories from parseData.php via ajax
 * @param callback
 */
function getAllDiseaseCategories(callback){
    $.ajax({
        url: GET,
        data:{
            action:"getDiseaseCategories"
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    callback(result);
                }
                catch (e){
                    console.log(e);
                }
            }
        }
    });
}



/**
 * Get all top toxicants from parseData.php via ajax
 * @param callback
 * TODO
 */
function getAllTopToxicants(callback){
    $.ajax({
        url: GET,
        data:{
            action:"getTopToxicants"
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    callback(result);
                }
                catch (e){
                    console.log(e);
                }
            }
        }
    });
}


function allDataFilterByDC(ac,filter,callback){
    $.ajax({
        url: GET,
        data:{
            action:ac,
            filter:filter
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    callback(result);
                }
                catch (e){
                    console.log(e);
                }
            }
        }
    });    
}
