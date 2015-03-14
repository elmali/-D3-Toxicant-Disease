<?php

include 'php/global.php';
include 'php/parseData.php';

//dc
$result = $conn->query("SELECT * FROM toxins_category;");
$toxins_category = array();
while($rows = $result->fetch_assoc()){
  array_push($toxins_category,$rows);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Toxicants</title>
  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" type="text/css" href="plugin/jquery.qtip.custom/jquery.qtip.min.css">
  <link rel="stylesheet" href="plugin/multiple-select/multiple-select.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="css/style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
  <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>
  <script type="text/javascript" src="plugin/jquery.qtip.custom/jquery.qtip.min.js"></script>
  <script type="text/javascript" src="plugin/multiple-select/jquery.multiple.select.js"></script>
  <script type="text/javascript" src="plugin/query-string/query-string.js"></script>
  <script type="text/javascript" src="js/colorbrewer.js"></script>
</head>
<body>
  <div id='wrap'>
  <div class="left selectList">
    <div id="checkboxFilters">
      <p>Disease Category : </p>
      <?php  
          echo "<div><input checked='checked' type='checkbox' id='checkall_dc'>" . "<span>Select All</span></div>";
          foreach ($toxins_category as $value) {
            echo "<div><input checked='checked' type='checkbox' name='dc' id='" . $value['ID'] . "'>" . "<span>" . $value['name'] . "</span></div>";
          }
      ?>      
    </div>
  </div>
  <div id="view_selection"  class="center">
    <div>
      <div id="selectRadio">
          <input type="radio" id="AToxicants" name="radio" checked="checked">
          <label for="AToxicants">By Toxicant</label>
          <input type="radio" id="ADiseases" name="radio" >
          <label for="ADiseases">By Disease</label>
          <span id="graphTitle">All Toxicants</span>
      </div>
    </div>
    <div id="graph"></div>
  </div>
  
  <div class="right searchList" >
    <span>Search circle</span>
    <select id='bubbleFilter' style="width:10%"></select>
  </div>
  <div class="clearBoth"></div>
</div>

<script>
var currentURL ={
  domain:"AToxicants",
  specificData:""
};
location.hash = queryString.stringify(currentURL);

var margin = {top: 5, right: 0, bottom: 0, left: 0};
var max_range = 60;
var max_amount = 83;
var radius_scale = d3.scale.pow().exponent(0.5).domain([0, max_amount]).range([2, max_range]);
//d3 svg
var layout_gravity = -0.01;
var width = 960,
    height = 960;
var diameter = 960,
    format = d3.format(",d"),
    color = d3.scale.linear().domain([0, max_amount]).range([0,8]);
    //color = d3.scale.category20c();

  
var svg = d3.select("#graph").append("svg")
    .attr("width", diameter)
    .attr("height", diameter);

function classes(root) {
  var dataNode = [];

  root.children.forEach(function(node){
    dataNode.push({packageName: name, className: node.name, value: node.size, id:node.id, r:radius_scale(node.size)});
  });

  return dataNode;
}


function appendCircles(root){
  //change graph title
  if(currentURL.specificData!==""){
    $("#graphTitle").text("Diseases realted to " +root.name);
  }


  //calculating layout values
  var nodes = classes(root);
  
  var bubbleNode = svg.selectAll('circle')
                     .data(nodes);

  var padding = 2;

  var duration = 1500; var delay = 0;
  // update

  bubbleNode
    //.transition().duration(duration)
   //  .delay(function(d, i) {delay = i * 7; return delay;})
   //  .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
     .attr('r', 0)
     .attr("id", function(d){ return d.id;})
     .attr("stroke", "black")
     .attr("stroke-width", 1)   
      .style("fill", function(d,i) {  
           return colorbrewer.Spectral[9][Math.floor(color(d.r))];
      });



  // enter
  bubbleNode.enter().append('circle')
  //  .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
    .attr('r', 0)
    .attr("id", function(d){ return d.id;})
    .attr("stroke", "black")
    .attr("stroke-width", 1)
    .style("fill", function(d,i) {
          return colorbrewer.Spectral[9][Math.floor(color(d.r))];
            
    });

  // exit
  bubbleNode.exit()
    //        .transition()
    //        .duration(duration + delay)
            .style('opacity', 0).remove();

  bubbleNode.transition().duration(duration).attr("r", function(d){return d.r;});
  
  if(currentURL.specificData!=""){
    var bubbleText = svg.selectAll('text')
                  .data(nodes);

    bubbleText.attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
              .attr("dy", ".3em")
              .style("text-anchor", "middle")
              .text(function(d) {
              var circleName = d.className.substring(0, d.r / 3);
              return circleName;
            });

    bubbleText.enter().append('text')
                      .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
                      .attr("dy", ".3em")
                      .style("text-anchor", "middle")
                      .text(function(d) {
                        var circleName = d.className.substring(0, Math.round(d.r / 3));
                        return circleName;
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
      .attr("cx", function (d) { 
        return d.x;
      })
      .attr("cy", function (d) {
        return d.y;
      });
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
        /**
        if(currentURL.sort == 1){
          var target = width/2;
          if(d.value > 10) target = width*3/8; else target = width*5/8;
          d.y += (center - d.y) *  (damper + 0.02)*alpha;
          d.x += (target - d.x) * (damper + 0.02)* alpha;
        }else{
          d.y += (center - d.y) *  (damper + 0.02)*alpha;
          d.x += (center - d.x) * (damper + 0.02)* alpha;
        }
        **/
        d.y += (center - d.y) *  (damper + 0.02)*alpha;
        d.x += (center - d.x) * (damper + 0.02)* alpha;
    };
}

    
    allCirlces.on("click",function(){
        currentURL = queryString.parse(location.hash);
        if(currentURL.specificData==""){
            currentURL.specificData ="t002";
            location.hash = queryString.stringify(currentURL);
            var data = {
              "name":"LEAD",
              "children":[
                {"name":"ADHD","size":10,"ID":"d555"},
                {"name":"onedisease","size":20,"ID":"d556"}
              ]
            };
            appendCircles(data);
        }
    });

  allCirlces.each(function(d){
        var currentCircle = d3.select(this);
        var showText = d.className + ": " + format(d.value);
        $(currentCircle).qtip({
          content: {
              text:showText
          },
          position: {
              my: 'bottom left',
              at: 'top right'
          },
          style:{
            classes:'qtip-bootstrap'
          }
      });
    });
}

function getToxicants(){
  root = {
     "name": "Toxicants",
     "children": [
      {"name": "LEAD", "size": 30,"ID":"t2321"},
      {"name": "COPPER", "size": 20,"ID":"t2348"},
      {"name": "ACRYLATES", "size": 10,"ID":"add"}
     ]
   };

   appendCircles(root);
}

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

function checkboxFilters(){

}

function bindEvent(){

  $(document).on("click","#checkall_dc",function(){
    if($(this).is(':checked')){
      $("input[name=dc]").each(function(){
        $(this).prop("checked",true);
      });
      $.ajax({
        url: 'php/parseData.php',
        data:{ action:"getToxicants" },
        success: function(response){
                var result = JSON.parse(response);
                appendCircles(result);
                refreshSearchList(result.children); }
      });
    }else{
      $("input[name=dc]").each(function(){
        $(this).prop("checked",false);
      });
      var result = {name:"", children:[]};
      appendCircles(result);
      refreshSearchList(result.children);
    }
  });

  //send ajax request to php to request new set of toxicants data
  //w
  $(document).on("click","input[name=dc]",function(){
    if($(this).is(':checked')){
        
    }
  }); 

  $("#bubbleFilter").multipleSelect({
        filter: true,
        single: true
  });
}

$( document ).ready(function() {
  $( "#selectRadio" ).buttonset();
  bindEvent();
  $.ajax({
    url: 'php/parseData.php',
    data:{
        action:"getToxicants"
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

 
});

</script>

</body>
</html>