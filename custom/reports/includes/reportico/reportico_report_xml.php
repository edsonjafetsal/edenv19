<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2014 Peter Deed

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        reportico_report_xml.php
 *
 * Base class for all report output formats.
 * Defines base functionality for handling report 
 * page headers, footers, group headers, group trailers
 * data lines
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */
require_once("reportico_report.php");

class reportico_report_xml extends reportico_report
{
	var	$record_template;
	var	$results = array();
	var	$line_ct = 0;
	
	function __construct ()
	{
		$this->page_width = 595;
		$this->page_height = 842;
		$this->column_spacing = "2%";
	}

	function start ()
	{

		reportico_report::start();
		$title = $this->reporttitle;
		$this->results=array(
			"title" => $title,
			"timestamp" => date("Y-m-d\TH:i:s\Z", time()),
			"displaylike" => array(),
			"data" => array()
			);

		$ct=0;
	}

	function finish ()
	{
		reportico_report::finish();
		$xmlroot =  preg_replace("/\.xml$/", "", $this->query->xmloutfile);
		$xml =  $this->arrayToXML($this->results,  new SimpleXMLElement('<'.$xmlroot.'/>'))->asXml();
        	$len = strlen($xml);

		if ( ob_get_length() > 0 )
            ob_end_clean();

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: text/xml');

		header("Content-Length: $len");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Disposition: inline; filename=reportico.xml');
		
		echo $xml;

		die;
	}

	function arrayToXML1($root_element_name,$ar)
	{
    		$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><".$root_element_name."></".$root_element_name.">");
		$f = function($f,$c,$a){
            		foreach($a as $k=>$v) {
                		if(is_array($v)) {
                    		$ch=$c->addChild($k);
                    		$f($f,$ch,$v);
                		} else {
                    		$c->addChild($k,$v);
                		}
            		}};
    		$f($f,$xml,$ar);
    		return $xml->asXML();
	}
	function arrayToXml(array $arr, SimpleXMLElement $xml)
	{
    		foreach ($arr as $k => $v) {
				if ( is_array ($v) && count($v) == 0 )
					continue;
				if ( preg_match("/^dataline_/", $k ) ) $k = "dataline";
        		    is_array($v) ? $this->arrayToXml($v, $xml->addChild($k)) : $xml->addChild($k, $v);
    		}
    		return $xml;
	}


	function format_column(& $column_item)
	{
		if ( !$this->show_column_header($column_item) )
				return;

		$k =& $column_item->column_value;
		$padstring = str_pad($k,20);
	}

	function each_line($val)
	{
		reportico_report::each_line($val);

		// Set the values for the fields in the record
		$linekey = "dataline_".($this->line_ct + 1);
		$this->results["data"][$linekey] = array();

       	if ( $this->line_ct == 0 )
       	{
               $qn = get_query_column("golap", $this->columns ) ;
               if ( $qn )
                       {
                   $arr = explode ( ",", $qn->column_value );
                   foreach ( $arr as $k => $v )
                   {
                       $arr1 = explode ( "=", $v );
                       $this->results["displaylike"][$arr1[0]] = $arr1[1];
                   }
               }
       	}

		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$qn = get_query_column($col->query_name, $this->columns ) ;
			$coltitle = $col->derive_attribute( "column_title",  $col->query_name);
			$coltitle = str_replace("_", " ", $coltitle);
			$coltitle = ucwords(strtolower($coltitle));
			$coltitle = sw_translate($coltitle);

		    $disp = $col->derive_attribute( "column_display",  "show" );
		    if ( $disp == "hide" ) continue;

			$this->results["data"][$linekey][preg_replace("/ /", "", $coltitle)] = $qn->column_value;

       		}
		$this->line_ct++;
		
	}

}

?>
