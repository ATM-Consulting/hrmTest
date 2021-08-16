<?php
/* Copyright (C) ---Put here your own copyright and developer email---
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
 * \file    lib/hrmtest_job.lib.php
 * \ingroup hrmtest
 * \brief   Library files with common functions for Job
 */

/**
 * Prepare array of tabs for Job
 *
 * @param	Job	$object		Job
 * @return 	array					Array of tabs
 */
function jobPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("hrmtest@hrmtest");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/hrmtest/job_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'job_card';
	$h++;

	$head[$h][0] = dol_buildpath("/hrmtest/skill_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Skill");
	$head[$h][2] = 'skill_card';
	$h++;

	$head[$h][0] = dol_buildpath("/hrmtest/position.php", 1).'?fk_job='.$object->id;
	$head[$h][1] = $langs->trans("Position");
	$head[$h][2] = 'position';
	$h++;


	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'job@hrmtest');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'job@hrmtest', 'remove');

	return $head;
}
