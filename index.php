<!DOCTYPE html>
<meta charset="utf-8">
<head>
	<title>Toxicants</title>
	<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/2.2.1/jquery.qtip.min.css">
	<link rel="stylesheet" href="plugin/multiple-select/multiple-select.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="css/style.css">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
	<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/2.2.1/jquery.qtip.min.js"></script>
	<script type="text/javascript" src="plugin/multiple-select/jquery.multiple.select.js"></script>
  <script type="text/javascript" src="plugin/query-string/query-string.js"></script>
</head>



<body>

<div id='wrap'>
	<div class="left selectList">
		<div>
			<p>Toxicants Filter</p>
			<select id='librarylist' style="width:10%"></select>
			<p>Diseases Filter</p>
			<select id='diseasesList' style="width:10%"></select>
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
    color = d3.scale.category20c();

var bubble = d3.layout.pack()
    .sort(null)
    .size([diameter, diameter])
    .padding(1.5);

var svg = d3.select("#graph").append("svg")
    .attr("width", diameter)
    .attr("height", diameter)
    .attr("class", "bubble");



var toxicantsData;


function removeSVGelements(){
	svg.selectAll("*").exit()
    .transition()
    .duration(500)
    .attr("x", 960)
  	//d3.select("svg").remove();
}

function classes(root) {
  var classes = [];

  function recurse(name, node) {
    if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
    else classes.push({packageName: name, className: node.name, value: node.size, ID:node.ID});
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
	var bubbleText = svg.selectAll('text')
	             		.data(nodes);

	var duration = 200; var delay = 0;
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
            colorshow = (d.value>2000) ? "rgb(245,110,96)": "rgb(71,245,96)";
            return colorshow;
            //color(d.packageName);
      })

	bubbleText.attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
            .attr("dy", ".3em")
            .style("text-anchor", "middle")
            .text(function(d) {
            var circleName = d.className.substring(0, d.r / 3);
            return circleName;
          });


	// enter
	bubbleNode.enter().append('circle')
	  .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
	  .attr('r', function(d) { return d.r; })
	  .attr('class', function(d) { return d.className; })
	  .attr("id", function(d){ return d.ID;})
    .style("fill", function(d,i) {
      			//console.log(d.hey);
      			var colorshow;
      			colorshow = (d.value>2000) ? "rgb(245,110,96)": "rgb(71,245,96)";
      			return colorshow;
      			//color(d.packageName);
    })
	  .style('opacity', 0) .transition()
	  .duration(duration * 1.2)
	  .style('opacity', 1);

	bubbleText.enter().append('text')
			  .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; })
			  .attr("dy", ".3em")
      		  .style("text-anchor", "middle")
      		  .text(function(d) {
      			var circleName = d.className.substring(0, d.r / 3);
      			return circleName;
      		});
	// exit
	bubbleNode.exit()
	  	      .transition()
	  	      .duration(duration + delay)
	  	      .style('opacity', 0).remove();

	bubbleText.exit().remove();


    //tooltip  and event for circle
    var allCirlces = d3.selectAll('circle');
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
    })

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

	/**
	var node = svg.selectAll(".node")
	      		  .data(bubble.nodes(classes(root))
	              .filter(function(d) { return !d.children; }))
	    		  .enter().append("g")
	              .attr("class", "node")
	      		  .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });



  	node.append("title")
      	.text(function(d) { return d.className + ": " + format(d.value); });


  	node.append("circle")
      	.attr("r", function(d) { return d.r; })
        .attr("id", function(d){ return d.ID;})
      	.style("fill", function(d,i) {
      			//console.log(d.hey);
      			var colorshow;
      			colorshow = (d.value>2000) ? "rgb(245,110,96)": "rgb(71,245,96)";
      			return colorshow;
      			//color(d.packageName);
      	}).on("mouseover", function(){
      		    d3.select(this).transition().attr("r", function(d) { return d.r*1.2; })

      	}).on("mouseleave", function(){
      		  d3.select(this).transition().attr("r", function(d) { return d.r; })
      	});







  	node.append("a")
    		.attr("xlink:href", function(d){return "https://www.google.com/?#q="+d.className.substring(0, d.r / 3);})
    		.attr("target","_blank")
    		.append("text")
        .attr("dy", ".3em")
      	.style("text-anchor", "middle")
      	.text(function(d) {
      			var circleName = d.className.substring(0, d.r / 3);
      			return circleName;
      	});


    // New line
    node.append("text")
        .attr("dy", ".3em")
        .style("text-anchor", "middle")
        .text(function(d) {
            var circleName = d.className.substring(0, d.r / 3);
            return circleName;
        })
        .append('tspan')
        .text("testing")
        .attr("dy", "1.4em")
        .attr("x", 0);

    **/
}

// Returns a flattened hierarchy containing all leaf nodes under the root.
d3.select(self.frameElement).style("height", diameter + "px");



function readAllDiseases(){
	d3.json("json/allDiseases.json", function(error, root) {

		var listHtml = "";

  		for(var i=0; i<root.children.length; i++){

  			listHtml += "<optgroup  label="+root.children[i].name+">";
  			for(var j=0; j<root.children[i].children.length;j++){
  				listHtml += "<option value='" + root.children[i].children[j].ID + "' selected='selected'>"
  				+ root.children[i].children[j].name + "</option>";
  			}

  			listHtml += "</optgroup>";
  		}

  		$("#diseasesList").empty().append(listHtml);
  		$("#diseasesList").multipleSelect({
          filter: true,
          onClose: function() {
              currentURL.specificData="";
              console.log(currentURL);
              location.hash = queryString.stringify(currentURL);
          		var selectedValue = $("#diseasesList").multipleSelect("getSelects");
          		console.log(selectedValue);
          		if($.inArray('d001', selectedValue)!=-1){
                root = {
					   "name": "Toxicants",
					   "children": [
					    {"name": "LEAD", "size": 3000,"ID":"t2321"},
					    {"name": "COPPER", "size": 3500,"ID":"t2348"},
					    {"name": "ACRYLATES", "size": 2000,"ID":"add","ID":"t2348"}
					   ]
				   };
				   appendCircles(root);
          		}else{
          			          			root = {
					   "name": "Toxicants",
					   "children": [
					    {"name": "LEAD", "size": 3000,"ID":"t2321"},
					    {"name": "COPPER", "size": 3500,"ID":"t2348"},

					   ]
				   };
				   appendCircles(root);
          		}

          }
        });
	});
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

$( document ).ready(function() {
	readAllDiseases();
	getToxicants();
	$( "#selectRadio" ).buttonset();

});


/**
$( document ).ready(function() {
  	d3.json("json/toxicants.json", function(error, root) {

	toxicantsData = root;
  	var detail = "";

  	var i = 0;
  	for(var key in toxicantsData){
  			var selected = "";
  			if(i == 0) selected = "selected='selected'";
  			detail += "<option value='" + key + "'" + selected + ">"
  				+ toxicantsData[key].name + "</option>";
  			i++;
  	}
  	$("#librarylist").empty().append(detail);
  	$("#librarylist").multipleSelect({
          filter: true,
          single: true,
          onClose:
            function() {
					var selectedValue = $("#librarylist").multipleSelect("getSelects");
    				if(toxicantsData[selectedValue]){

    					appendCircles(toxicantsData[selectedValue]);

    					var listDetails="";
    		        	for(var j=0; j<toxicantsData[selectedValue].children.length; j++){

  				  		  listDetails += "<option value='" + toxicantsData[selectedValue].children[j].ID + "'>"
  				  				           + toxicantsData[selectedValue].children[j].name + "</option>";

    			  		}
    			  		$("#bubbleFilter").empty().append(listDetails).multipleSelect("refresh");

    				}
          }
      });

		appendCircles(toxicantsData["t2321"]);
		var listDetails="";
		for(var j=0; j<toxicantsData["t2321"].children.length;j++){
  			listDetails += "<option value='" + toxicantsData["t2321"].children[j].ID + "'>"
  				+ toxicantsData["t2321"].children[j].name + "</option>";
  			//console.log(toxicantsData[key]["children"]+'555');
		}
		$("#bubbleFilter").empty().append(listDetails);
  	$("#bubbleFilter").multipleSelect({
          filter: true,
          single: true,
          onClose: function(){
          	var circleElement = '#'+ $("#bubbleFilter").multipleSelect("getSelects");
          	d3.selectAll("circle").attr("stroke",null);
            d3.select(circleElement).attr("stroke","black").attr("stroke-width","6");
          }
    });
  	$("#graph").click(function(){
      	d3.selectAll("circle").attr("stroke",null);
  	});

	});

	$( "#selectRadio" ).buttonset();

	readAllDiseases();

});
**/

</script>

</body>