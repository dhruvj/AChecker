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

if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined in checker_input_form.php.");

if (!isset($graph)) die(_AC("no_instance"));

include_once(AC_INCLUDE_PATH. "classes/HTMLRpt.class.php");
include_once(AC_INCLUDE_PATH. "classes/HTMLByGuidelineRpt.class.php");
include_once(AC_INCLUDE_PATH. "classes/Utility.class.php");
include_once(AC_INCLUDE_PATH. "classes/DAO/UserLinksDAO.class.php");
include_once(AC_INCLUDE_PATH. "classes/DAO/UserDecisionsDAO.class.php");

$savant->assign('graph', $graph);
if (isset($_POST["enable_html_validation"])) {
    $savant->assign('enable_html_validation', true);
} else {
    $savant->assign('enable_html_validation', false);
}
if (isset($_POST["enable_css_validation"])) {
    $savant->assign('enable_css_validation', true);
} else {
    $savant->assign('enable_css_validation', false);
}

$savant->display('checker/crawler_results.tmpl.php');
?>
