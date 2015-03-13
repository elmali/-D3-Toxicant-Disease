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
    <div>
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

//d3 svg

var diameter = 960,
    format = d3.format(",d"),
    color = d3.scale.linear()
    .domain([0,960])
    .range([0,9]);
    //color = d3.scale.category20c();

var bubble = d3.layout.pack()
    .size([diameter, diameter])
    .padding(2)
    .sort(null);

    /**
    .sort(function comparator(a, b) {
      return b.value - a.value;
    });
    **/
    
var svg = d3.select("#graph").append("svg")
    .attr("width", diameter)
    .attr("height", diameter)
    .attr("class", "bubble");

function classes(root) {
  var classes = [];

  function recurse(name, node) {
    if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
    else classes.push({packageName: name, className: node.name, value: node.size, id:node.id});
  }

  recurse(null, root);
  return {children: classes};
}




function appendCircles(root){
  //change graph title
  if(currentURL.specificData!=""){
    $("#graphTitle").text("Diseases realted to " +root.name);
  }


  //calculating layout values
  var nodes = bubble.nodes(classes(root))
                .filter(function(d) { return !d.children; }); // filter out the outer bubble
  
  var bubbleNode = svg.selectAll('circle')
                     .data(nodes);

  //** new
  //
  var radius = d3.scale.sqrt().range([0, 12]);
  var padding = 2;
  var force = d3.layout.force()
    .nodes(nodes)
    .size([960, 960])
    .gravity(0)
    .charge(0)
    .on("tick", tick)
    .start();

  var duration = 0; var delay = 0;
  // update

  bubbleNode.transition()
     .duration(duration)
     .delay(function(d, i) {delay = i * 7; return delay;})
     .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
     .attr('r', function(d) { return d.r; })
     .attr('class', function(d) { return d.className; })
    .attr("id", function(d){ return d.ID;})
      .style("fill", function(d,i) {
            //console.log(d.hey);
            var colorshow;
            colorshow = (d.value > 20) ? "rgb(245,110,96)": "rgb(71,245,96)";
            return colorshow;
            //color(d.packageName);
      })



  // enter
  bubbleNode.enter().append('circle')
    .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
    .attr('r', function(d) { return d.r; })
    .attr('class', function(d) { return d.className; })
    .attr("id", function(d){ return d.ID;})
    .style("fill", function(d,i) {
            
            return colorbrewer.Spectral[9][Math.floor(color(d.y))];
            //color(d.packageName);
    })
    .style('opacity', 0) .style('opacity', 1).call(force.drag);;


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
  
  // exit
  bubbleNode.exit()
            .transition()
            .duration(duration + delay)
            .style('opacity', 0).remove();

  


    //tooltip  and event for circle
    var allCirlces = d3.selectAll('circle');
    
    //**new
    //**new
    function tick(e) {
      allCirlces.each(gravity(0.2 * e.alpha))
      .each(collide(0.5))
      .attr("cx", function (d) { 
        return d.x;
      })
      .attr("cy", function (d) {
        return d.y;
      });
    }


    // Move nodes toward cluster focus.
function gravity(alpha) {
    return function (d) {
        d.y += (d.y/100 - d.y) * alpha;
        d.x += (d.x/100 - d.x) * alpha;
    };
}

// Resolve collisions between nodes.
function collide(alpha) {
    var quadtree = d3.geom.quadtree(nodes);
    return function (d) {

        var r = d.radius + radius.domain()[1] + padding,
            nx1 = d.x - r,
            nx2 = d.x + r,
            ny1 = d.y - r,
            ny2 = d.y + r;
        quadtree.visit(function (quad, x1, y1, x2, y2) {
            if (quad.point && (quad.point !== d)) {
                var x = d.x - quad.point.x,
                    y = d.y - quad.point.y,
                    l = Math.sqrt(x * x + y * y),
                    r = d.radius + quad.point.radius + (d.color !== quad.point.color) * padding;
                if (l < r) {
                    l = (l - r) / l * alpha;
                    d.x -= x *= l;
                    d.y -= y *= l;
                    quad.point.x += x;
                    quad.point.y += y;
                }
            }
            return x1 > nx2 || x2 < nx1 || y1 > ny2 || y2 < ny1;
        });
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
                {"name":"ADHD","size":2000,"ID":"d555"},
                {"name":"onedisease","size":3000,"ID":"d556"}
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
    })
}

function getToxicants(){
  root = {
     "name": "Toxicants",
     "children": [
      {"name": "LEAD", "size": 3000,"ID":"t2321"},
      {"name": "COPPER", "size": 3500,"ID":"t2348"},
      {"name": "ACRYLATES", "size": 2000,"ID":"add","ID":"t2348"}
     ]
   };

   appendCircles(root);
}

function bindEvent(){
  $(document).on("click","#checkall_dc",function(){
    if($(this).is(':checked')){
        $("input[name=dc]").each(function(){
        $(this).prop("checked",true);
    });
    }else{
      $("input[name=dc]").each(function(){
        $(this).prop("checked",false);
      });
    }
  });
  $(document).on("click","input[name=dc]",function(){
    if($(this).is(':checked')){
        alert($(this).prop("id"));
    }
  }); 
}

$( document ).ready(function() {
  $( "#selectRadio" ).buttonset();
  bindEvent();
  $.ajax({
    url: 'php/parseData.php',
    data:{
        action:"getAllContaminantUI"
    },
    success: function(response){
        var result = JSON.parse(response);
        //console.log(result);
        appendCircles(result);
    }
  })











  
});

</script>

</body>
</html>