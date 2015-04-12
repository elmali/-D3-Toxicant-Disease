<?php
//Include header.php in the beginning
    $pageTitle = "Toxicants-Diseases";
    require('php/header.php');
?>

<div style="margin:0 auto;text-align:center;">
    <div id="selectRadio">
        <input type="radio" id="ADiseases" name="radio" checked="checked">
        <label for="ADiseases">By Disease</label>
        <input type="radio" id="AToxicants" name="radio" >
        <label for="AToxicants">By Toxicant</label>
        <span id="graphTitle">All diseases</span>
    </div>
</div>

<div id='wrap'>
    <div id="checkboxFilters">
    </div>

	<div id="toxicantList">
    </div>
	
	<div class="searchList" >
        <div id="searchSection">
          <span>Search circle</span>
          <select id='bubbleFilter' style="width:10%"></select>
		</div>
	</div>
</div>




<div id="graph">
</div>

<footer>
By MSS Students 
Follow us on Facebook
<a href="https://www.facebook.com/groups/233141136866476/"><img src="css/images/fbicon.jpg" alt="Facebook" height="50" width="50"/></a>
</footer>

<?php
    require("php/footer.php");
?>
