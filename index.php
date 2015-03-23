<?php
//Include header.php in the beginning
    $pageTitle = "Toxicants-Diseases";
    require('php/header.php');
?>

<div id='wrap'>
    <div class="left selectList">
        <div id="checkboxFilters">
        </div>
    </div>

    <div id="view_selection"  class="center">
        <div>
            <div id="selectRadio">
              <input type="radio" id="ADiseases" name="radio" checked="checked">
              <label for="ADiseases">By Disease</label>
              <input type="radio" id="AToxicants" name="radio" >
              <label for="AToxicants">By Toxicant</label>
              <span id="graphTitle">All diseases</span>
          </div>
        </div>

        <div id="graph">
        </div>
    </div>
  
    <div class="right searchList" >
        <span>Search circle</span>
        <select id='bubbleFilter' style="width:10%"></select>
    </div>

    <div class="clearBoth">
    </div>
</div>

<?php
    require("php/footer.php");
?>
