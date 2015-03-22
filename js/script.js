/**
 * Script File
 */

//Global variables
var currentSearch,color, currentURL, margin, layout_gravity, width, height, diameter, format, max_amount, max_range, padding, duration, delay, svg, node;

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
    margin = {top: 5, right: 0, bottom: 0, left: 0};

    //d3 svg
    layout_gravity = -0.01;

    width = 960,
        height = 960;
    diameter = 960,
        format = d3.format(",d");
    max_amount = 80;
    max_range = 60;
    //color = d3.scale.category20c();
    padding = 2;

    duration = 1500;
    delay = 0;

    appendDiseaseCategoriesList();

    svg = d3.select("#graph").append("svg")
        .attr("width", diameter)
        .attr("height", diameter);

    node = d3.select('svg').append("g").attr("id", "bubble-nodes")
        .attr("transform", "translate("+margin.left+","+margin.top+")");

    node.append("rect")
        .attr("id", "bubble-background")
        .attr("width", width)
        .attr("height", height);

    $( "#selectRadio" ).buttonset();
    bindEvent();

    ajaxRequestAllData('getToxicants');

});

/**
 * Add disease-category list to the left side bar
 */
function appendDiseaseCategoriesList(){
    $('#checkboxFilters').append("<p>Disease Category : </p>");
    $('#checkboxFilters').append('<div>'+
        '<input type="checkbox" checked="checked" id="checkall_dc">'+
        '<span>Select All</span>'+
        '</div>');
    getAllDiseaseCategories(function(result){
        result.children.forEach(function(item){
            var i = $('<div>'+
                '<input type="checkbox" checked="checked" name="dc" id='+item.id+'>'+
                '<span>'+item.name+'</span>'+
                '</div>');
            $('#checkboxFilters').append(i);
        });
    });
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
}

/**
 *
 * @param root
 */
function appendCircles(root){

    //calculating layout values
    var nodes = classes(root);
    var bubbleNode = node.selectAll("circle").data(nodes);
    // var label = d3.select("node")
    //             .append("div")
    //             .attr("id", "bubble-labels");
    var bubbleText = node.selectAll(".bubble-label").data(nodes);

    // update
    bubbleNode.attr('r', 0)
        .attr("id", function(d){ return d.id;})
        .attr('class','bubble-circle')
        .attr("stroke", "black")
        .attr("stroke-width", 1)
        .style("fill", function(d,i) {
            return colorbrewer.Spectral[9][Math.floor(color(d.r))];
        });

    // enter
    bubbleNode.enter()
        .append('circle')
        .attr('r', 0)
        .attr('class','bubble-circle bubble')
        .attr("id", function(d){ return d.id;})
        .attr("stroke", "black")
        .attr("stroke-width", 1)
        .style("fill", function(d,i) {
            return colorbrewer.Spectral[9][Math.floor(color(d.r))];
        });

    // exit
    bubbleNode.exit()
        .remove();

    bubbleNode.transition().duration(duration).attr("r", function(d){return d.r;});

    if(currentURL.specificData!=""){
        //bubbleText = label.selectAll(".bubble-label").data(nodes);

        bubbleText.text(function(d) {
            var circleName = d.className.substring(0, d.r / 3);
            return circleName;
        });

        bubbleText.enter()
            .append('a')
            .attr("class", "bubble-label  bubble")
            .attr("xlink:href", function(d){ return "https://www.google.ca/#q="+d.className;})
            .attr("target","_blank")
            .append('text')
            //.attr("dy", ".3em")
            .style("text-anchor", "middle")
            .attr("fill","black")
            .text(function(d) {
                var circleName = d.className.substring(0, Math.round(d.r / 3));
                return circleName;
            });
        bubbleText.exit().remove();
    }else{
        bubbleText.text(function(d) {
            //var circleName = d.className.substring(0, d.r / 3);
            return "";
        });


        bubbleText.exit().remove();
    }

    //tooltip  and event for circle
    var allCirlces = d3.selectAll('circle');
    var damper = 0.1;
    //**new
    //**new
    function tick(e) {
        bubbleNode.each(move_towards_center(e.alpha))
            .attr("transform", function(d){ return 'translate(' + d.x + ',' + d.y + ')';} );

        bubbleText.attr("transform", function(d){ return 'translate(' + (margin.left + d.x) + ',' + (margin.top + d.y)  + ')';} );
        // .style("left", function(d){ return (margin.left + d.x) - d.dx / 2 + "px"})
        // .style("top", function(d){ return (margin.top + d.y) - d.dy / 2 + "px"});
    }
    var force = d3.layout.force()
        .nodes(nodes)
        .size([width, height]);

    force.gravity(layout_gravity)
        .charge(charge())
        .friction(0.9)
        .on("tick", tick)
        .start();

    function charge(){
        return function(d){
            return  -Math.pow(d.r, 2.0) / 8;
        };
    }

    // Move nodes toward cluster focus.
    function move_towards_center(alpha) {
        return function (d) {
            var center = width/2;

            d.y += (center - d.y) *  (damper + 0.02)*alpha;
            d.x += (center - d.x) * (damper + 0.02)* alpha;
        };
    }

    allCirlces.on("click",function(d){
        currentURL = queryString.parse(location.hash);
        if(currentURL.specificData==""){
            currentURL.specificData =d.id;
            location.hash = queryString.stringify(currentURL);
            $.ajax({
                url: 'php/parseData.php',
                data:{
                    action:"fetchFromToxicant",
                    filter:d.id
                },
                success: function(response){
                    if (response){
                        try{
                            var result = JSON.parse(response);
                            appendCircles(result);
                            refreshSearchList(result.children);
                            $("#graphTitle").text("Diseases realted to " +root.name);
                        }catch(e){
                            console.log(e); //error
                        }
                }}
            })
        }
    });

    // allCirlces.on("mouseover" ,function(){
    //          d3.select(this).attr("stroke-width", 3);
    //     }).on("mouseout",function(){
    //         d3.select(this).attr("stroke-width", 1);
    //     });

    d3.selectAll('.bubble').each(function(d){
        var currentCircle = d3.select(this);
        var titleString = "<text id='tooltipTitle'>"+capitalizeFirstLetter(d.className)+"</text>";
        var textString = "Related cases: "+d.value;
        $(currentCircle).qtip({
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
            currentURL.specificData="";
            if(ac=="getToxicants") currentURL.domain = 'Toxicants';
            else if(ac="getDiseases")  currentURL.domain = 'Diseases';
            location.hash = queryString.stringify(currentURL);
            appendCircles(result);
            refreshSearchList(result.children); }
    });
}

/**
 * NYI
 */
function checkboxFilters(){

}

/**
 *
 */
function bindEvent(){
    $(document).on("click","#checkall_dc",function(){
        if($(this).is(':checked')){
            $("input[name=dc]").each(function(){
                $(this).prop("checked",true);
            });
            if(currentURL.specificData==""){

                ajaxRequestAllData('getToxicants');

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
        if(currentURL.specificData==""){
            var filter = [];
            $("input[name=dc]:checked").each(function(){
                filter.push($(this).prop("id"));
            });
            getFilterToxicantsByDC(filter, function(result){
                currentURL.specificData="";
                location.hash = queryString.stringify(currentURL);
                appendCircles(result);
                refreshSearchList(result.children);
            });
        }
    });

    $("#bubbleFilter").multipleSelect({
        filter: true,
        single: true,
        onClose: function() {
            var searchID = "#"+$('select').multipleSelect('getSelects');
            currentSearch = searchID;
            $(searchID).trigger('mouseover');
        }
    });

    $('#graph').on("click",function(){
        if(currentSearch!=""){
            $(currentSearch).trigger('mouseout');
        }
    })

    $(document).on('click', "#AToxicants", function(){
        ajaxRequestAllData('getToxicants');
    })

    $(document).on('click', "#ADiseases", function(){
        ajaxRequestAllData('getDiseases');
    })
}