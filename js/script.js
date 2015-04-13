/**
 * Script File
 */

//Global variables
var currentSearch,pastForce,color, currentURL, margin, layout_gravity, width,
    height, diameter, format, max_amount, max_range, padding, duration, delay,
    svg, node, is_deeper_view;

/**
 * This function executes when DOM is ready
 */
$( document ).ready(function() {

    currentURL ={
        domain:"Toxicants",
        specificData:""
    };
    location.hash = queryString.stringify(currentURL);
    currentSearch="";
    margin = {top: 5, right: 0, bottom: 5, left: 20};

    //d3 svg
    layout_gravity = -0.01;

    width = 960,
    height = 1500;
    diameter = 960,
        format = d3.format(",d");
    max_amount = 80;
    max_range = 60;
    //color = d3.scale.category20c();
    padding = 2;

    duration = 1500;
    delay = 0;

    appendDiseaseCategoriesList();
    appendTopToxicantsList();
    appendTopDiseasesList();

    // Hide dropdown lists
    $('.top-toxicants').hide();
    $('.top-diseases').hide();

    // Don't show top toxicant at landing view.
    hideTopToxicants();

    svg = d3.select("#graph").append("svg")
        .attr("width", width + margin.right + margin.left)
        .attr("height", height + margin.top + margin.bottom);

    node = d3.select('svg').append("g").attr("id", "bubble-nodes")
        .attr("transform", "translate("+margin.left+","+margin.top+")");

    node.append("rect")
        .attr("id", "bubble-background")
        .attr("width", width)
        .attr("height", height);

    svg.append("marker") 
       .attr("id","triangle")
       .attr("viewBox","0 0 15 15")
       .attr("refX",0)
       .attr("refY",5)
      // .attr("markerUnits","strokeWidth")
       .attr("markerWidth",10)
       .attr("markerHeight", 10)
       .attr("orient", "270")
       .append("svg:path")
       .attr("fill","white")
       .attr("d", "M 0 0 L 10 5 L 0 10 z");

    $( "#selectRadio" ).buttonset();
    bindEvent();

    toolTip();

    ajaxRequestAllData('getDiseases');


});



/**
 * Add disease-category list to the left side bar
 */
function appendDiseaseCategoriesList(){
    $('#checkboxFilters').append("<p class='checkbox'> Disease Category </p>");
    $('#checkboxFilters').append('<div class="disease-category">'+
        '<p><input type="checkbox" checked="checked" id="checkall_dc">'+
        '<label for="checkall_dc"><span></span>Select All</label><p>'+
        '</div>');
    getAllDiseaseCategories(function(result){
        result.children.forEach(function(item){
            var i = $(
                '<p><input type="checkbox" checked="checked" name="dc" id='+item.id+'>'+
                '<label for="'+item.id+'"><span></span>'+item.name+'</label></p>');
            $('.disease-category').append(i);
        });
    });
    
    $('.checkbox').mouseover(function(){
        $('.checkbox').css('background-color',"#8096A6");
        $('.disease-category').show();
    });

    $('#checkboxFilters').mouseleave(function(){
        $('.checkbox').css('background-color',"#002E4C");
        $('.disease-category').hide();
    });
}

function hideDiseaseCategoriesList(){
    $('#checkboxFilters').hide();
}

function showDiseaseCategoriesList(){
    $('#checkboxFilters').show();
}



/**
 * Add top <num> toxicants list to the right side bar
 */
function appendTopToxicantsList(){
    $('#toxicantList').append("<p class='toptoxicants'>Top Toxicants </p>");
    $('#toxicantList').append('<div class="top-toxicants">'+
        '</div>');
    getAllTopToxicants(function(result){
        result.children.forEach(function(item){
            var i = $(
                '<p><label><input type="radio" name="top_toxicant" id="top_toxicant_'+item.id+'" value="'+item.name+'">'+capitalizeFirstLetter(item.name)+'</label><p>');
            $('.top-toxicants').append(i);
        });
    });
    
    $('.toptoxicants').mouseover(function(){
        $('.toptoxicants').css('background-color',"#8096A6");
        $('.top-toxicants').show();
    });

    $('#toxicantList').mouseleave(function(){
        $('.toptoxicants').css('background-color',"#002E4C");
        $('.top-toxicants').hide();
    });
}

function hideTopToxicants()
{
    $('#toxicantList').hide();
}


function showTopToxicants()
{
    $('#toxicantList').show();
}



/**
 * Add top <num> diseases list 
 */
function appendTopDiseasesList(){
    $('#diseaseList').append("<p class='topdiseases'>Top Diseases </p>");
    $('#diseaseList').append('<div class="top-diseases">'+'</div>');
    getAllTopDiseases(function(result){
        result.children.forEach(function(item){
            var i = $(
                '<p><label><input type="radio" name="top_disease" id="top_disease_'+item.id+'" value="'+item.name+'">'+capitalizeFirstLetter(item.name)+'</label><p>');
            $('.top-diseases').append(i);
        });
    });
    
    $('.topdiseases').mouseover(function(){
        $('.topdiseases').css('background-color',"#8096A6");
        $('.top-diseases').show();
    });

    $('#diseaseList').mouseleave(function(){
        $('.topdiseases').css('background-color',"#002E4C");
        $('.top-diseases').hide();
    });
}

function hideTopDiseases()
{
    $('#diseaseList').hide();
}

function showTopDiseases()
{
    $('#diseaseList').show();
}


/**
 *
 * @param root
 * @returns {Array}
 */
function classes(root) {
    var dataNode = [];

    max_amount = d3.max(root.children, function(d) { return +d.size;} );
    (max_amount < 5) ? max_range = 40 : max_range = 60;
    var radius_scale = d3.scale.pow().exponent(0.5).domain([0, max_amount]).range([0, max_range]);

    root.children.forEach(function(node){
        dataNode.push({packageName: name, className: node.name, value: node.size, id:node.id, r:radius_scale(node.size)});
    });
    color = d3.scale.linear().domain([0, max_range]).range([0,8]);
    return dataNode;
    //x:Math.random() * 900, y:Math.random() * 900
}

/**
 *
 * @param root
 */
function appendCircles(root){

    clearSearch();

    var radius = d3.scale.sqrt().range([0, 12]);
    //calculating layout values
    var nodes = classes(root);
    var bubbleNode = node.selectAll("circle").data(nodes);
    node.selectAll(".bubble-label").remove();
    var bubbleText = node.selectAll(".bubble-label").data(nodes);

    // update
    bubbleNode.attr('r', 0)
        .attr("id", function(d){ return d.id;})
        .attr('class','bubble-circle')
        .attr("stroke", "white")
        .attr("stroke-width", 1)
        .style("fill", function(d,i) {
            return colorbrewer.GnBu[9][Math.floor(color(d.r))];
        });

    // enter
    bubbleNode.enter()
        .append('circle')
        .attr('r', 0)
        .attr('class','bubble-circle bubble')
        .attr("id", function(d){ return d.id;})
        .attr("stroke", "white")
        .attr("stroke-width", 1)
        .style("fill", function(d,i) {
            return colorbrewer.GnBu[9][Math.floor(color(d.r))];
        });

    // exit
    bubbleNode.exit()
        .remove();

    bubbleNode.transition().duration(duration).attr("r", function(d){return d.r;});
    updateVariableURL();
    if(currentURL.specificData!=""){

        bubbleText.enter()
            .append('a')
            .attr("class", "bubble-label  bubble")
            .attr("xlink:href", function(d){ return "https://www.google.ca/#q="+d.className;})
            .attr("target","_blank")
            .append('text')
            .style("text-anchor", "middle")
            .attr("fill","black")
            .style("font-weight", "bold")
            .style("font-family", "verdana")
            .style("font-size","10px")
            .attr("dy", ".3em")
            .text(function(d) {
                var circleName = d.className.substring(0, Math.round(d.r / 3));
                return circleName;
            });


        svg.append("line")                 // attach a line
            .attr("stroke", "white")         // colour the line
            .attr("stroke-width", 2)        // adjust line width
            .attr("stroke-dasharray", ("10,3"))  // stroke-linecap type
            .attr("x1", 10)     // x position of the first end of the line
            .attr("y1", 50)      // y position of the first end of the line
            .attr("x2", 10)     // x position of the second end of the line
            .attr("marker-start","url(#triangle)")
            .attr("y2", height*4.5/5);   // y position of the second end of the line
             


        svg.append("text").attr("class","axislabels")
            .style("text-anchor", "middle")
            .attr("fill","white")
            .attr("transform","translate(50 ,"+ 70 +")")
            .text("Strong");
        

        
    }else{
        d3.select("line").remove();
        d3.selectAll(".axislabels").remove();
        
        bubbleText.enter()
                .append('text')
                .attr("class", "bubble-label  bubble")
                .style("text-anchor", "middle")
                .attr("fill","white")
                .style("font-weight", "bold")
                .style("font-family", "verdana")
                .style("font-size","15px")
                .style("visibility",function(d,i){
                    if(d.r<40) return "hidden";
                })
                //.attr("fill","black")
                .attr("dy", ".3em")
                .text(function(d) {
                    var circleName = d.className.substring(0, Math.round(d.r / 3));
                    return circleName;
                });    

    }


    //tooltip  and event for circle
    var allCirlces = d3.selectAll('circle');


    allCirlces.on("click",function(d){
        if(pastForce!=null){
            pastForce.stop();
        }
        is_deeper_view = true;

        // Hide left hand side Disease Categories List after
        // entering into deeper view
        hideDiseaseCategoriesList();
        hideTopToxicants();
        // Hide top diseases list
        hideTopDiseases();

        currentURL = queryString.parse(location.hash);
        if(currentURL.specificData==""){
            currentURL.specificData = d.id;
            location.hash = queryString.stringify(currentURL);
            if (currentURL.domain=="Toxicants"){
                var separators = ['\\\+', '\\\(', '\\\)', '\\*', '/'];
                var showName = d.className.split(new RegExp(separators.join('|'), 'g')); 
                $("#graphTitle").text("Diseases related to " + showName[0]);
                ajaxRequestDeepView("fetchFromToxicant",d.id);
            }
            else {
                var separators = ['\\\+', '\\\(', '\\\)', '\\*', '/'];
                var showName = d.className.split(new RegExp(separators.join('|'), 'g')); 
                $("#graphTitle").text("Toxicants related to " + showName[0]);
                ajaxRequestDeepView("fetchFromDisease",d.id);
            }
        }
    });
 
    d3.selectAll('circle').each(function(d){
        var currentCircle = d3.select(this);
        var titleString = "<text id='tooltipTitle'>"+capitalizeFirstLetter(d.className)+"</text>";
        updateVariableURL();
        if(currentURL.specificData==""){
            var textString = "Related cases: "+d.value;
        }else{
            var relationship = ["LIMITED","GOOD","STRONG"];
            var textString = "Strength of evidence: "+relationship[d.value-1];
        }
        
        $(currentCircle[0]).qtip({
            content: {
                title: titleString,
                text: textString
            },
            position: {
                my: 'bottom left',
                at: 'top right'
            },
            style:{
                classes:'qtip-bootstrap qtip2Css'
            }
        });

     

        $(currentCircle[0]).on({
            mouseover: function () {
                d3.select(this).attr("stroke-width", 4);
            },
            mouseout: function () {
                d3.select(this).attr("stroke-width", 1);
            }
        });
    });
    


    animation(bubbleNode,bubbleText,nodes);

}


function animation(bubbleNode,bubbleText,nodes){

    var space = 8;  
    var damper = 0.1;   
    
    if(pastForce!=null){
        pastForce.stop();
    }
    updateVariableURL();
    (currentURL.specificData=="")? (space = 8):(space = 5.5);



    function tick(e) {

        bubbleNode.each(move_towards_center(e.alpha))
            .attr("transform", function(d){ return 'translate(' + (d.x+margin.left) + ',' + (margin.top + d.y)  + ')';} );
        bubbleText.attr("transform", function(d){ return 'translate(' + (margin.left + d.x) + ',' + (margin.top + d.y)  + ')';} );
//        setTimeout(function() {
             continuousColor(bubbleNode);
//        }, 2000);
    }


    var force = d3.layout.force()
        .nodes(nodes)
        .size([width, height]);

    function charge(){
        return function(d){
            return  -Math.pow(d.r, 2.0) / space;
        };
    }



    // Move nodes toward cluster focus.
    function move_towards_center(alpha) {
        return function (d) {
            var center = width/2;
            updateVariableURL();
            if(currentURL.specificData=""){
                d.y += (center - d.y) *  (damper + 0.02)*alpha;
                d.x += (center - d.x) * (damper + 0.02)* alpha;                 
            }else{
                var yCenter = height/5;
                switch (d.value) {
                    case 3:
                        d.y += (yCenter*1.6 - d.y) *  (damper + 0.02)*alpha;
                        break;
                    case 2:
                        d.y += (yCenter*2.5 - d.y) *  (damper + 0.02)*alpha;
                        break;
                    case 1:
                        d.y += (yCenter*3 - d.y) *  (damper + 0.02)*alpha;
                        break;
                    default:
                        d.y += (center - d.y) *  (damper + 0.02)*alpha;
                        break;
                }                                             

                d.x += (center - d.x) * (damper + 0.02)* alpha; 
            }               

        };
            
    }

    pastForce = force.gravity(layout_gravity)
                    .charge(charge())
                    .friction(0.9)
                    .on("tick", tick)
                    .start();


}



function continuousColor(color_nodes){
        // When it is deeper view, change the colors to be continuous.
        if(is_deeper_view){
            // Color range
              
            var colorP = d3.scale.linear()
                        .domain([100, 600, 900])
                        .range(["rgb(8, 64, 129)", "rgb(78, 179, 211)", "rgb(204, 235, 197)"]);

            color_nodes.style("fill", function(d,i) {
                                  var pofy = Math.round(d.y);
                                  return colorP(pofy); })
                       .attr("fill-opacity", 0.85);
        }
}

function updateVariableURL(){
    currentURL = queryString.parse(location.hash);
}

function changeURL(){
    location.hash = queryString.stringify(currentURL);
}


/**
 *
 * @param data
 */
function refreshSearchList(data){
    $('#bubbleFilter').empty();
    $.each(data, function(key, value) {
        $('#bubbleFilter')
            .append($("<option></option>")
                .attr("value",value.id)
                .text(value.name));
    });
    $('select').multipleSelect('refresh');
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}


function ajaxRequestAllData(ac){
    $.ajax({
        url: 'php/parseData.php',
        data:{ action:ac },
        success: function(response){
            var result = JSON.parse(response);           
            if(ac=="getToxicants") currentURL.domain = 'Toxicants';
            else if(ac="getDiseases")  currentURL.domain = 'Diseases';
            currentURL.specificData="";
            location.hash = queryString.stringify(currentURL);
            appendCircles(result);
            refreshSearchList(result.children); }
    });
}

function ajaxRequestDeepView(ac,id){
    $.ajax({
        url: 'php/parseData.php',
        data:{
            action:ac,
            filter:id
        },
        success: function(response){
            if (response){
                try{
                    var result = JSON.parse(response);
                    appendCircles(result);
                    refreshSearchList(result.children);
                }catch(e){
                    console.log(e); //error
                }
            }
        }
    });
}



function changeGraphTitle(text){
    $("#graphTitle").text(text);
}

function clearSearch(){
    if(currentSearch!=""){
            $(currentSearch).trigger('mouseout');
    }
}

/**
 *
 */
function bindEvent(){


    $(document).on("click","#checkall_dc",function(){
        updateVariableURL();
        if($(this).is(':checked')){
            $("input[name=dc]").each(function(){
                $(this).prop("checked",true);
            });
            if(currentURL.specificData==""){
                var type;
                (currentURL.domain=="Toxicants") ? (type = "getToxicants"):(type = "getDiseases")
                ajaxRequestAllData(type);
            }

        }else{
            $("input[name=dc]").each(function(){
                $(this).prop("checked",false);
            });
            if(currentURL.specificData==""){
                var result = {name:"empty", children:[]};
                appendCircles(result);
                refreshSearchList(result.children);
            }

        }
    });




    //send ajax request to php to request new set of toxicants data
    $(document).on("click","input[name=dc]",function(){
        updateVariableURL();
        if(currentURL.specificData==""){
            var filter = [];
            $("input[name=dc]:checked").each(function(){
                filter.push($(this).prop("id"));
            });
            updateVariableURL();
            (currentURL.domain=="Toxicants") ? (ac = 'getFilterToxicantsByDC') : (ac = 'getFilterDiseasesByDC')
            currentURL.specificData="";
            changeURL();
            allDataFilterByDC(ac,filter, function(result){
                appendCircles(result);
                refreshSearchList(result.children);
            });
        }
    });

    $("#bubbleFilter").multipleSelect({
        filter: true,
        single: true,
        onClose: function() {
            updateVariableURL();            
            if(currentURL.specificData==""){
                if (currentSearch !=""){
                    $(currentSearch).trigger('mouseout');
                    currentSearch ="";
                }
                var searchID = "#"+$('select').multipleSelect('getSelects');
                var jqueryNode = $(searchID);  //jquery
                var d3Node = d3.selectAll( jqueryNode.toArray() ); //d3
                var event = document.createEvent("SVGEvents");
                event.initEvent("click",true,true);
                d3Node.node().dispatchEvent(event);                
            }else{
                if (currentSearch !=""){
                    $(currentSearch).trigger('mouseout');
                    currentSearch ="";
                }
                var searchID = "#"+$('select').multipleSelect('getSelects');
                currentSearch = searchID;
                $(searchID).trigger('mouseover');
            }

        }
    });



    $('#graph').on("click",function(){
        if(currentSearch!=""){
            $(currentSearch).trigger('mouseout');
        }
    })


    $(document).on('click', "#AToxicants", function(){        
        // Reset deeper view flag.
        is_deeper_view = false;

        // Append left hand side Disease Categories List after
        // going to the landing view
        showDiseaseCategoriesList();
        showTopToxicants();
        // Hide top diseases list
        hideTopDiseases();

        var filter = [];
        changeGraphTitle("All toxicants");
        $("input[name=dc]:checked").each(function(){
            filter.push($(this).prop("id"));
        });
        getFilterToxicantsByDC(filter, function(result){
            currentURL.specificData="";
            currentURL.domain="Toxicants";
            location.hash = queryString.stringify(currentURL);
            appendCircles(result);
            refreshSearchList(result.children);
        });

    })

    $(document).on('click', "#ADiseases", function(){
        // Reset deeper view flag.
        is_deeper_view = false;

        // Append left hand side Disease Categories List after
        // going to the landing view
        showDiseaseCategoriesList();
        hideTopToxicants();
        showTopDiseases();

        changeGraphTitle("All diseases");
        currentURL.domain="Diseases";
        currentURL.specificData="";
        location.hash = queryString.stringify(currentURL);
        ajaxRequestAllData('getDiseases');
    })


    // Top toxicant radio list
    // Clicking on the radio buttons will show its related diseases in the
    // deeper view.
    $(document).on("change","input[name=top_toxicant]",function(){
        // Get the selected toxicant id
        var radio_id = '#' + $("input[name=top_toxicant]:checked").attr('id').replace("top_toxicant_", "");
        var jqueryNodeRadio = $(radio_id);
        var d3Node_radio = d3.selectAll( jqueryNodeRadio.toArray() ); 
        var event_radio = document.createEvent("SVGEvents");
        event_radio.initEvent("click",true,true);
        d3Node_radio.node().dispatchEvent(event_radio);
   });


    // Top diseases radio list
    // Clicking on the radio buttons will show its related toxicants in the
    // deeper view.
    $(document).on("change","input[name=top_disease]",function(){
        // Get the selected disease id
        var top_disease_radio_id = '#' + $("input[name=top_disease]:checked").attr('id').replace("top_disease_", "");
        var jqueryNodeTopDisease = $(top_disease_radio_id);
        var d3Node_top_disease = d3.selectAll( jqueryNodeTopDisease.toArray() ); 
        var event_top_disease = document.createEvent("SVGEvents");
        event_top_disease.initEvent("click",true,true);
        d3Node_top_disease.node().dispatchEvent(event_top_disease);
   });


}



function toolTip(){
        $('#diseaseList').qtip({
            content: {
                text: "This list shows the top 100 diseases in descending order."
            },
            position: {
                my: 'middle left',
                at: 'middle right'
            },
            show: {
                event: 'click'
            },
            style:{
                classes:'qtip-bootstrap qtip2Css'
            }
        });
}
