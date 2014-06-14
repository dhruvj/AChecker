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

if (!defined("AC_INCLUDE_PATH"))
    die("Error: AC_INCLUDE_PATH is not defined.");
include_once (AC_INCLUDE_PATH . "lib/simple_html_dom.php");
include_once(AC_INCLUDE_PATH . 'classes/Utility.class.php');

/**
 * Crawler
 * Class for crawling through webpages in a breadth first manner 
 * @access	public
 * @author	Dhruv Jagetiya
 * @package checker
 */
class Crawler {

    //private variables
    var $baseURL;       // Base Url from which crawling begins
    var $levelOfReview; //Maximum level of review
    var $totalLinks;    // Total number of links to be reviewed
    var $linksPerLevel; // Links to be considered per level
    var $linksPerPage;  // Links to be considered per page
    var $domainName;    // Domain of base url
    var $visited = array();    // Visited URLs, don't visit again!

    /**
     * Constructor to initialize the crawling process
     * @param string $URL
     * @param number $levelOfReview
     * @param number $totalLinks
     * @param number $linksPerLevel
     * @param number $linksPerPage
     */

    function Crawler($URL, $levelOfReview, $totalLinks, $linksPerLevel, $linksPerPage) {
        $this->baseURL = $URL;
        $this->levelOfReview = $levelOfReview;
        if ($this->levelOfReview == "homepage") {
            $this->levelOfReview = -1;
        } else if ($this->levelOfReview == "all") {
            $this->levelOfReview = 0;
        }
        $this->totalLinks = $totalLinks;
        $this->linksPerLevel = $linksPerLevel;
        $this->linksPerPage = $linksPerPage;
    }
    /**
     * Get the html of page
     * @param string $URL
     * @return string, html of the page, similar to file_get_contents function
     */
    function getHTMLfromURL($URL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     * Check if URL exists or not
     * @param string $URL
     * @return boolean
     */
    function urlExists($URL) {
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $content = curl_exec($ch);
        $type = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($type == "200") return true;
        else return false;
    }

    /**
     * Check if the content type returned by URL is text/html
     * @param string $URL
     * @return boolean
     */
    function isContentTypeHTML($URL) {
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $content = curl_exec($ch);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if(strpos($type, "text/html") !== false) return true;
        else return false;
    }

    /**
     * convert relative url to absolute
     * @param string $rel
     * @param string $base
     * @return absolute url corresponding to given relative url
     */
    function relativeToAbsoluteURL($rel, $base) {
        /* return if already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '')
            return $rel;

        /* queries and anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return $base;

        /* parse base URL and convert to local variables:
          $scheme, $host, $path */
        extract(parse_url($base));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($rel[0] == '/')
            $path = '';

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
            
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }

    /**
     * Checks if a URL is relative or not
     * @param String $URL
     * @return if relative url return true else false
     */
    function isRelativeURL($URL) {
        if (strpos($URL, 'http://') === 0 || strpos($URL, 'https://') === 0) {
            return false;
        }
        return true;
    }

    /**
     * @param string $URL
     * @return an array of all valid(connectable) uri present on a page
     */
    function getAllValidLinks($URL) {
        $extractedURL = parse_url($URL);
        $base = $extractedURL['host'];
        // add www if not present
        if (strpos($base, "www.") !== 0) {
            $base = "www." . $base;
        }
        $URL = $extractedURL['scheme'] . "://" . $base . $extractedURL['path'] . ($extractedURL['query'] != '' ? ("?" . $extractedURL['query']) : "");
        $validuri = array();
        // Create DOM from URL
        $html = str_get_dom($this->getHTMLfromURL($URL));
        // Find all a tags 
        foreach ($html->find('a') as $element) {
            $nextURL = $element->href;
            if ($this->isRelativeURL($nextURL) === true) {   // if it is a relative url then it will be from same domain
                $nextURL = $this->relativeToAbsoluteURL($nextURL, $URL);
                if($this->isContentTypeHTML($nextURL) === false) {
                    continue;
                } 
                if (in_array($nextURL, $this->visited) === false) {
                    if ($this->urlExists($nextURL) !== false) {
                        if (in_array($nextURL, $this->visited) === false) {    //don't add same URL again
                            array_push($validuri, $nextURL);
                            array_push($this->visited, $nextURL);
                        }
                    }
                }
            } else {
                $extractedNextURL = parse_url($nextURL);
                $baseNextURL = $extractedNextURL['host'];
                if (strpos($baseNextURL, "www.") !== 0) {
                    $baseNextURL = "www." . $baseNextURL;
                }
                $nextURL = $extractedNextURL['scheme'] . "://" . $baseNextURL . $extractedNextURL['path'] . ($extractedNextURL['query'] != '' ? ("?" . $extractedNextURL['query']) : "");
                if($this->isContentTypeHTML($nextURL) === false) {
                    continue;
                }
                if (strcmp($base, $baseNextURL) == 0) {  //check if it belongs to same domain
                    if (in_array($nextURL, $this->visited) === false) {
                        if ($this->urlExists($nextURL) !== false) {
                            if (in_array($nextURL, $this->visited) === false) {    //don't add same URL again
                                array_push($validuri, $nextURL);
                                array_push($this->visited, $nextURL);
                            }
                        }
                    }
                }
            }
            // limit number of url by $this->linksPerPage And don't break when 0(infinity)
            if ((count($validuri) >= $this->linksPerPage) && ($this->linksPerPage != 0))
                break;
        }
        return $validuri;
    }

    /**
     * Crawling process
     */
    function initiate() {
        $result = array();
        $countLinks = 1;    //keep counting of links found, 1 for baseurl
        $q = new SplQueue();
        $q->enqueue(array("level" => 0, "url" => $this->baseURL));
        array_push($this->visited, $this->baseURL);    // Mark the base URL visited
        while (!$q->isEmpty()) {
            $node = $q->dequeue();
            $result[$node[level]][] = $node[url];
            $nextLevel = ($node[level] + 1);    // next level
            //limit the level of review by $this->levelOfReview except 0(infinity)
            if (($this->levelOfReview >= $nextLevel || ($this->levelOfReview == 0)) && ($this->totalLinks > $countLinks || $this->totalLinks == 0)) {
                $nextURLs = $this->getAllValidLinks($node[url]);
                foreach ($nextURLs as $url) {
                    $q->enqueue(array("level" => $nextLevel, "url" => $url));
                    ++$countLinks;
                    if ($countLinks >= $this->totalLinks && $this->totalLinks != 0)
                        break;
                }
            }
        }
    }

}