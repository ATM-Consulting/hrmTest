<?php
/* Copyright (C) 2021 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    hrmtest/lib/hrmtest.lib.php
 * \ingroup hrmtest
 * \brief   Library files with common functions for HrmTest
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function hrmtestAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("hrmtest@hrmtest");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/hrmtest/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/hrmtest/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/hrmtest/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'hrmtest');

	return $head;
}

/**
 * @param $pLib
 * @param $pName
 * @param string $plus
 * @param string $class
 * @param false $autoDisabled
 * @return string
 */
function displaySubmitButton($pLib,$pName,$plus="", $class='button', $autoDisabled = false){
	$field = "<INPUT class='".$class."' TYPE='SUBMIT' NAME='$pName' VALUE=\"$pLib\" ";

	if($autoDisabled && stripos($plus, 'onclick')===false) {
		$field.=' onclick="this.disabled=true" ';
	}

	$field.=" $plus>\n";
	return $field;
}

/**
 * @param $pName
 * @param $pVal
 * @param string $plus
 * @return string
 */
function hidden($pName,$pVal,$plus=""){
	$field = '<input id="'.$pName.'" TYPE="HIDDEN" NAME="'.$pName.'" VALUE="'.$pVal.'" '.$plus.'> ';
	return $field;
}

/**
 * encapsulate end_form function
 */
function endf() {
	print end_form();
}

/**
 *  return end form
 * @return string
 */
function end_form(){
	return "</FORM>\n";
}

