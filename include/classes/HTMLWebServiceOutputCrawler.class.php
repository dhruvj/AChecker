<?php
/************************************************************************/
/* AChecker                                                             */
/************************************************************************/
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

/*
 * This file defines all the html templates used to generate web service html output
 */
if (!defined("AC_INCLUDE_PATH")) die("Error: AC_INCLUDE_PATH is not defined.");

include_once(AC_INCLUDE_PATH.'classes/HTMLRpt.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/UserLinksDAO.class.php');

class HTMLWebServiceOutputCrawler {

	var $graph;                  // from parameter. instance of AccessibilityValidator
	var $userLinkID;                  // from parameter. user_links.user_link_id
	var $rootURL;
	var $htmlRpt;                     // instance of HTMLRpt. Generate error detail

	/**
	* Constructor
	* @access  public
	* @param   $rootURL : rootURL to begin crawler with
	*          $graph: graph of website structure
    *          $userLinkID: user link id
	* @author  Dhruv Jagetiya
	*/
	function HTMLWebServiceOutputCrawler($rootURL, $graph, $userLinkID) {
		$this->graph = $graph;
		$this->rootURL = $rootURL;
		$this->userLinkID = $userLinkID;
		$this->generateRpt();
	}
	
	/**
	* create html report
	* @access  private
	* @param   none
	* @author  Dhruv Jagetiya
	*/
	private function generateRpt() {
		$this->htmlRpt = "";
        $htmlReport = "".
        $levelCount = 0;
        $htmlReport .= "<table>";
        $htmlReport .= "<tr><th> ID </th><th> URL </th><th> Belongs To </th></tr>";
        foreach ($this->graph as $level) {
            $htmlReport .= "<tr><th colspan='2'> Level-".($levelCount++)."</th></tr>";
            foreach ($level as $urlinfo) {
                $htmlReport .= "<tr>";
                $htmlReport .= "<td>".($urlinfo[1])."</td>";
                $htmlReport .= "<td>".$urlinfo[0]."</td>";
                $htmlReport .= "<td>".$urlinfo[2]."</td>";
                $htmlReport .= "<tr>";
            }
        }
        $htmlReport .= "</table>";
        $this->htmlRpt .= $htmlReport;
	}


	/**
	* return main report
	* @access  public
	* @param   none
	* @return  return main report
	* @author  Dhruv Jagetiya
	*/
	public function getWebServiceOutput()
	{
		return $this->htmlRpt;
	}
}

?>