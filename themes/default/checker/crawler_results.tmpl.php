<?php
/* * ********************************************************************* */
/* AChecker                                                             */
/* * ********************************************************************* */
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/* * ********************************************************************* */
// $Id$

global $addslashes, $congrats_msg_for_likely, $congrats_msg_for_potential;
;

include_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH . 'classes/DAO/UserLinksDAO.class.php');
?>
<?php
$graphForJavascript = "";
foreach ($this->graph as $level) {
    $graphForJavascript .= "[";
    foreach ($level as $urlinfo) {
        $graphForJavascript .= "['" . $urlinfo[0] . "', '" . $urlinfo[1] . "', '" . $urlinfo[2] . "'],";
    }
    $graphForJavascript = rtrim($graphForJavascript, ",");
    $graphForJavascript .= "],";
}
$graphForJavascript = rtrim($graphForJavascript, ",");
?>
<script src="<?php echo AC_BASE_HREF . "/jscripts/lib/jquery.jOrgChart.js" ?>" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo AC_BASE_HREF ?>themes/default/jquery.jOrgChart.css"/>
<script language="javascript" type="text/javascript"> 
    function createNode(url, id) {
        var li = document.createElement("li");
        var ul = document.createElement("ul");
        ul.setAttribute("id", id);
        li.innerHTML = url;
        li.appendChild(ul);
        return li;
    }
    $( document ).ready(function() {
        var graph = [<?php echo $graphForJavascript; ?>];
        var root = createNode(graph[0][0][0], graph[0][0][1]);
        $("#graph").append(root);
        for(var level = 1; level < graph.length; ++level) {
            for(var url = 0; url < graph[level].length; ++url) {
                document.getElementById(graph[level][url][2]).appendChild(createNode(graph[level][url][0], graph[level][url][1]));
            }
        }
        $("#graph").jOrgChart({
            chartElement: "#AC_overview"
        });
    });
</script>
    
<div id="output_div" >
    <div class="center-input-form">
        <a name="report" title="<?php echo _AC("report_start"); ?>"></a>
        <fieldset class="group_form"><legend class="group_form"><?php echo _AC("accessibility_review"); ?></legend>
             <div class="topnavlistcontainer"><br />
        <ul class="navigation">
            <li class="navigation"><a href="javascript:void(0);" accesskey="1" title="<?php echo _AC("menu_overview"); ?> Alt+1" id="AC_menu_overview" onclick="AChecker.output.onClickTab('AC_overview');"><span class="nav"><?php echo _AC("menu_overview"); ?></span></a></li>

            <li class="navigation"><a href="javascript:void(0);" accesskey="2" title="<?php echo _AC("menu_report"); ?> Alt+2" id="AC_menu_report" onclick="AChecker.output.onClickTab('AC_report');"><span class="nav"><?php echo _AC("menu_report"); ?></span></a></li>

        </ul>
    </div>
            <br>
    <div id ="AC_overview">
   

          
                <ul id="graph" style="display:none">

                </ul>
            

    </div>
    <div id ="AC_report">
        
          
<table>
    <tr>
        <th> URL </th>
        <th> Report </th>
    </tr>
        <?php
        $count = 0;
foreach ($this->graph as $level) {
    echo "<tr><td colspan='2'> Level-".$count++."</tr>";
    
    foreach ($level as $urlinfo) {
        echo "<tr>";
        echo "<td>".$urlinfo[0]."</td>"."<td>"."<form target='_blank' method='post' id = 'form_".$count."' action = '".AC_BASE_HREF."checker/index.php'><input type='hidden' name='byCrawler' value='1'></form> <a href='javascript:void(0);' onclick=\"javascript:document.getElementById('form_".($count++)."').submit()\"> Report </a>"."</td>";
        echo "<tr>";
    }
}
?>
               
         
</table>
   

    </div>
        </fieldset>
    <div class="center">
		
	</div>
   
    </div>
    
</div><br />
