<?php
/* Copyright (C) 2021 grégory Blémand  <contact@atm-consulting.fr>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       skill_tab.php
 *        \ingroup    hrmtest
 *        \brief      Page to add/delete/view skill to jobs/users
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
dol_include_once('/hrmtest/class/skill.class.php');
dol_include_once('/hrmtest/class/skillrank.class.php');
dol_include_once('/hrmtest/lib/hrmtest_skill.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("hrmtest@hrmtest", "other"));

$id = GETPOST('id', 'int');
$fk_skill = GETPOST('fk_skill', 'int');
$objecttype = GETPOST('objecttype', 'alpha');
$TNote = GETPOST('TNote', 'array');
$lineid = GETPOST('lineid', 'int');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'skillcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

$TAuthorizedObjects = array('job', 'user');
$skill = new SkillRank($db);

// Initialize technical objects
if (in_array($objecttype, $TAuthorizedObjects)) {
	if ($objecttype == 'job') {
		dol_include_once('/hrmtest/class/job.class.php');
		$object = new Job($db);
	} else if ($objecttype == "user") {
		$object = new User($db);
	}
} else accessforbidden($langs->trans('ErrorBadObjectType'));

$hookmanager->initHooks(array('skilltab', 'globalcard')); // Note that conf->hooks_modules contains array

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoread = $user->rights->hrmtest->skill->read;
$permissiontoadd = $user->rights->hrmtest->skill->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->hrmtest->skill->delete;

// Security check (enable the most restrictive one)
if ($user->socid > 0) accessforbidden();
if (empty($conf->hrmtest->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/hrmtest/skill_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/hrmtest/skill_list.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
			}
		}
	}

	if ($action == 'addSkill') {
		$error = 0;

		if ($fk_skill <= 0) {
			setEventMessage('ErrNoSkillSelected', 'errors');
			$error++;
		}

		if (!$error) {
			$skillAdded = new SkillRank($db);
			$skillAdded->fk_skill = $fk_skill;
			$skillAdded->fk_object = $id;
			$skillAdded->objecttype = $objecttype;
			$ret = $skillAdded->create($user);
			if ($ret < 0) setEventMessage($skillAdded->error, 'errors');
			else unset($fk_skill);
		}
	} else if ($action == 'saveSkill') {
		if (!empty($TNote)) {
			foreach ($TNote as $skillId => $rank) {
				$TSkills = $skill->fetchAll('ASC', 't.rowid', 0, 0, array('customsql' => 'fk_object=' . $id . ' AND objecttype="' . $objecttype . '" AND fk_skill = ' . $skillId));
				if (is_array($TSkills) && !empty($TSkills)) {
					foreach ($TSkills as $tmpObj) {
						$tmpObj->rank = $rank;
						$tmpObj->update($user);
					}
				}
			}
		}

	} else if ($action == 'confirm_deleteskill' && $confirm == 'yes') {
		$skillToDelete = new SkillRank($db);
		$ret = $skillToDelete->fetch($lineid);
		if ($ret > 0) {
			$skillToDelete->delete($user);
		}
	}
}

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Skill");
$help_url = '';
llxHeader('', $title, $help_url);

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	// view configuration
	if ($objecttype == 'job') {
		dol_include_once('/hrmtest/lib/hrmtest_job.lib.php');
		$head = jobPrepareHead($object);
		$listLink = dol_buildpath('/hrmtest/job_list.php', 1);
	} else if ($objecttype == "user") {
		require_once DOL_DOCUMENT_ROOT . "/core/lib/usergroups.lib.php";
		$head = user_prepare_head($object);
		$listLink = dol_buildpath('/user/list.php', 1);
	}

	print dol_get_fiche_head($head, 'skill_tab', $langs->trans("Workstation"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	/*if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteSkill'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}*/
	// Confirmation to delete line
	if ($action == 'ask_deleteskill') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&objecttype=' . $objecttype . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteskill', '', 0, 1);
	}
	// Clone confirmation
	/*if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}*/

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . $listLink . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	// table of skillRank linked to current object
	$TSkills = $skill->fetchAll('ASC', 't.rowid', 0, 0, array('customsql' => 'fk_object=' . $id . ' AND objecttype="' . $objecttype . '"'));

	$TAlreadyUsedSkill = array();
	if (is_array($TSkills) && !empty($TSkills)) {
		foreach ($TSkills as $skillElement) {
			$TAlreadyUsedSkill[] = $skillElement->fk_skill;
		}
	}

	if ($objecttype != 'user')
	{
		// form pour ajouter des compétences
		print '<form name="addSkill" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
		print '<input type="hidden" name="objecttype" value="' . $objecttype . '">';
		print '<input type="hidden" name="id" value="' . $id . '">';
		print '<input type="hidden" name="action" value="addSkill">';
		print '<div class="div-table-responsive-no-min">';
		print '<table id="tablelines" class="noborder noshadow" width="100%">';
		print '<tr><td>' . $langs->trans('AddSkill') . '</td><td>' . $langs->trans('Rank') . '</td><td></td></tr>';
		print '<tr>';
		foreach ($skill->fields as $key => $infos) {
			if ($key == 'fk_skill') {
				print '<td>' . $skill->showInputField($infos, $key, $$key) . '</td>';
			}
		}
		print '<td><input class="button reposition" type="submit" value="' . $langs->trans('Add') . '"></td>';
		print '</tr>';
		print '</table>';
		print '</div>';
		print '</form>';

	}

	print '</div>';
	print '<br>';

	print '<div class="clearboth"></div>';

	if ($objecttype != 'user')
	{
		print '<form name="saveSkill" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
		print '<input type="hidden" name="objecttype" value="' . $objecttype . '">';
		print '<input type="hidden" name="id" value="' . $id . '">';
		print '<input type="hidden" name="action" value="saveSkill">';
	}
	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow" width="100%">';
	print '<tr class="liste_titre">';
	foreach ($skill->fields as $key => $infos) {
		if ($infos['visible'] > 0) print '<th class="linecol' . $key . '">' . $langs->trans($infos['label']) . '</th>';
	}
	print '<th class="linecoledit"></th>';
	print '<th class="linecoldelete"></th>';
	print '</tr>';
	if (!is_array($TSkills) || empty($TSkills)) {
		print '<tr><td>' . $langs->trans("NoRecordFound") . '</td></tr>';
	} else {
		foreach ($TSkills as $skillElement) {
			print '<tr>';
			foreach ($skill->fields as $key => $infos) {
				if ($infos['visible'] > 0) print '<td class="linecol' . $key . '">' . $skillElement->showOutputField($infos, $key, $skillElement->{$key}) . '</td>';
			}
			print '<td class="linecoledit"></td>';
			print '<td class="linecoldelete">';
			if ($objecttype != 'user')
			{
				print '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?id=' . $skillElement->fk_object . '&amp;objecttype=' . $objecttype . '&amp;action=ask_deleteskill&amp;lineid=' . $skillElement->id . '">';
				print img_delete();
				print '</a>';
			}
			print '</td>';
			print '</tr>';
		}
	}

	print '</table>';
	if ($objecttype != 'user') print '<td><input class="button pull-right" type="submit" value="' . $langs->trans('Save') . '"></td>';
	print '</div>';
	if ($objecttype != 'user') print '</form>';


	// liste des compétences liées

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	/*if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}*/


	// Buttons for actions

	/*if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {


			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));
		}
		print '</div>'."\n";
	}*/
	llxFooter();
}
