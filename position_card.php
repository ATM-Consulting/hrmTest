<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       position_card.php
 *		\ingroup    hrmtest
 *		\brief      Page to create/edit/view position
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
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/hrmtest/class/position.class.php');
dol_include_once('/hrmtest/class/job.class.php');
dol_include_once('/hrmtest/lib/hrmtest_position.lib.php');

$action = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');



DisplayJob($langs, $db, $conf, $user, $hookmanager, $permissiontoadd, $lineid, $text);


$object = new Position($db);
// Part to create
if ($action == 'create') {
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Position")), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}

	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>' . "\n";

	print dol_get_fiche_end();

	print '<div class="center">';

	print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '&nbsp; ';
	print '<input type="' . ($backtopage ? "submit" : "button") . '" class="button button-cancel" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '"' . ($backtopage ? '' : ' onclick="javascript:history.go(-1)"') . '>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

if ($action == 'view')
	DisplayPositionList($conf, $db, $user, $hookmanager, $langs);



/**
 * @param $langs
 * @param DoliDB $db
 * @param $conf
 * @param $user
 * @param HookManager $hookmanager
 * @param $permissiontoadd
 * @param array $lineid
 * @param $text
 * @return array
 */
function DisplayJob($langs, DoliDB $db, $conf, $user, HookManager $hookmanager, $permissiontoadd, $lineid, $text)
{
	// Load translation files required by the page
	$langs->loadLangs(array("hrmtest@hrmtest", "other"));

	// Get parameters
	$id = GETPOST('fk_job', 'int');
	$fk_job = GETPOST('fk_job', 'int');

	$ref = GETPOST('ref', 'alpha');
	$action = GETPOST('action', 'aZ09');
	$confirm = GETPOST('confirm', 'alpha');
	$cancel = GETPOST('cancel', 'aZ09');
	$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'positioncard'; // To manage different context of search
	$backtopage = GETPOST('backtopage', 'alpha');
	$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
	//$lineid   = GETPOST('lineid', 'int');

	// Initialize technical objects
	$object = new Job($db);


	$extrafields = new ExtraFields($db);

	$diroutputmassaction = $conf->hrmtest->dir_output . '/temp/massgeneration/' . $user->id;
	$hookmanager->initHooks(array('positioncard', 'globalcard')); // Note that conf->hooks_modules contains array

	// Fetch optionals attributes and labels
	$extrafields->fetch_name_optionals_label($object->table_element);

	$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

	// Initialize array of search criterias
	$search_all = GETPOST("search_all", 'alpha');
	$search = array();
	foreach ($object->fields as $key => $val) {
		if (GETPOST('search_' . $key, 'alpha')) {
			$search[$key] = GETPOST('search_' . $key, 'alpha');
		}
	}

	if (empty($action) && empty($id) && empty($ref)) {
		$action = 'view';
	}

	// Load object
	include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


	$permissiontoread = $user->rights->hrmtest->position->read;
	//$permissiontoadd = $user->rights->hrmtest->position->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->hrmtest->position->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->hrmtest->position->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->hrmtest->position->write; // Used by the include of actions_dellink.inc.php
	$upload_dir = $conf->hrmtest->multidir_output[isset($object->entity) ? $object->entity : 1] . '/position';

	// Security check (enable the most restrictive one)
	//if ($user->socid > 0) accessforbidden();
	//if ($user->socid > 0) $socid = $user->socid;
	//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
	//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
	//if (empty($conf->hrmtest->enabled)) accessforbidden();
	//if (!$permissiontoread) accessforbidden();


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

		$backurlforlist = dol_buildpath('/hrmtest/position_list.php', 1);

		if (empty($backtopage) || ($cancel && empty($fk_job))) {
			if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
				if (empty($fk_job) && (($action != 'add' && $action != 'create') || $cancel)) {
					$backtopage = $backurlforlist;
				} else {
					$backtopage = dol_buildpath('/hrmtest/position_card.php', 1) . '?fk_job=' . ($fk_job > 0 ? $fk_job : '__ID__');
				}
			}
		}

		$triggermodname = 'HRMTEST_POSITION_MODIFY'; // Name of trigger action code to execute when we modify record

		// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
		include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

		// Actions when linking object each other
		include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

		// Actions when printing a doc from card
		include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

		// Action to move up and down lines of object
		//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

		// Action to build doc
		include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

		if ($action == 'set_thirdparty' && $permissiontoadd) {
			$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
		}
		if ($action == 'classin' && $permissiontoadd) {
			$object->setProject(GETPOST('projectid', 'int'));
		}

		// Actions to send emails
		$triggersendname = 'HRMTEST_POSITION_SENTBYMAIL';
		$autocopy = 'MAIN_MAIL_AUTOCOPY_POSITION_TO';
		$trackid = 'position' . $object->id;
		include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
	}


	/*
	 * View
	 *
	 * Put here all code to build page
	 */

	$form = new Form($db);
	$formfile = new FormFile($db);
	$formproject = new FormProjets($db);

	$title = $langs->trans("Position");
	$help_url = '';
	llxHeader('', $title, $help_url);

	// Example : Adding jquery code
	// print '<script type="text/javascript" language="javascript">
	// jQuery(document).ready(function() {
	// 	function init_myfunc()
	// 	{
	// 		jQuery("#myid").removeAttr(\'disabled\');
	// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
	// 	}
	// 	init_myfunc();
	// 	jQuery("#mybutton").click(function() {
	// 		init_myfunc();
	// 	});
	// });
	// </script>';


	// Part to edit record
	if (($id || $ref) && $action == 'edit') {
		print load_fiche_titre($langs->trans("Position"), '', 'object_' . $object->picto);

		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="' . $object->id . '">';
		if ($backtopage) {
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
		}
		if ($backtopageforcancel) {
			print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
		}

		print dol_get_fiche_head();

		print '<table class="border centpercent tableforfieldedit">' . "\n";

		// Common attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

		// Other attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

		print '</table>';

		print dol_get_fiche_end();

		print '<div class="center"><input type="submit" class="button button-save" name="save" value="' . $langs->trans("Save") . '">';
		print ' &nbsp; <input type="submit" class="button button-cancel" name="cancel" value="' . $langs->trans("Cancel") . '">';
		print '</div>';

		print '</form>';
	}

	// Part to show record
	if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
		$res = $object->fetch_optionals();

		$head = positionPrepareHead($object);
		print dol_get_fiche_head($head, 'position_card', $langs->trans("Workstation"), -1, $object->picto);

		$formconfirm = '';

		// Confirmation to delete
		if ($action == 'delete') {
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeletePosition'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
		}
		// Confirmation to delete line
		if ($action == 'deleteline') {
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
		}

		// Confirmation of action xxxx
		if ($action == 'xxx') {
			$formquestion = array();
			/*
			$forcecombo=0;
			if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
			$formquestion = array(
				// 'text' => $langs->trans("ConfirmClone"),
				// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
				// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
				// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
			);
			*/
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
		}

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
		$linkback = '<a href="' . dol_buildpath('/hrmtest/position_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($fk_job) ? '&fk_job=' . $fk_job : '') . '">' . $langs->trans("BackToList") . '</a>';

		$morehtmlref = '<div class="refidno">';

		$morehtmlref .= '</div>';

		dol_banner_tab($object, 'fk_job', $linkback, 1, 'ref', 'ref', $morehtmlref, '', 0, '', '', 'arearefnobottom');


		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';
		print '<div class="underbanner clearboth"></div>';
		print '<table class="border centpercent tableforfield">' . "\n";

		// Common attributes
		//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
		//unset($object->fields['fk_project']);				// Hide field already shown in banner
		//unset($object->fields['fk_soc']);					// Hide field already shown in banner
		include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

		// Other attributes. Fields from hook formObjectOptions and Extrafields.
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

		print '</table>';
		print '</div>';
		print '</div>';

		print '<div class="clearboth"></div>';

		print dol_get_fiche_end();


	}
	return array($fk_job, $object, $action);
}


/**
 * @param $conf
 * @param DoliDB $db
 * @param $user
 * @param HookManager $hookmanager
 * @param $langs
 * @param array $fk_job
 * @return array|void
 */
function DisplayPositionList($conf, DoliDB $db, $user, HookManager $hookmanager, $langs)
{
	require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
	require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';

	// load hrmtest libraries
	require_once __DIR__ . '/class/position.class.php';

	// for other modules
	//dol_include_once('/othermodule/class/otherobject.class.php');

	// Load translation files required by the page
	$langs->loadLangs(array("hrmtest@hrmtest", "other"));

	$action = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
	$massaction = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)
	$show_files = GETPOST('show_files', 'int'); // Show files area generated by bulk actions ?
	$confirm = GETPOST('confirm', 'alpha'); // Result of a confirmation
	$cancel = GETPOST('cancel', 'alpha'); // We click on a Cancel button
	$toselect = GETPOST('toselect', 'array'); // Array of ids of elements selected into a list
	$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'positionlist'; // To manage different context of search
	$backtopage = GETPOST('backtopage', 'alpha'); // Go back to a dedicated page
	$optioncss = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')
	$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');



	$id = GETPOST('id', 'int');
	$fk_job = GETPOST('fk_job', 'int');

	// Load variable for pagination
	$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
	$sortfield = GETPOST('sortfield', 'aZ09comma');
	$sortorder = GETPOST('sortorder', 'aZ09comma');
	$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
	if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
		// If $page is not defined, or '' or -1 or if we click on clear filters
		$page = 0;
	}
	$offset = $limit * $page;
	$pageprev = $page - 1;
	$pagenext = $page + 1;

	// Initialize technical objects
	$object = new Position($db);

	$extrafields = new ExtraFields($db);
	$diroutputmassaction = $conf->hrmtest->dir_output . '/temp/massgeneration/' . $user->id;
	$hookmanager->initHooks(array('positionlist')); // Note that conf->hooks_modules contains array

	// Fetch optionals attributes and labels
	$extrafields->fetch_name_optionals_label($object->table_element);
	//$extrafields->fetch_name_optionals_label($object->table_element_line);

	$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

	// Default sort order (if not yet defined by previous GETPOST)
	if (!$sortfield) {
		reset($object->fields);                    // Reset is required to avoid key() to return null.
		$sortfield = "t." . key($object->fields); // Set here default search field. By default 1st field in definition.
	}
	if (!$sortorder) {
		$sortorder = "ASC";
	}

	// Initialize array of search criterias
	$search_all = GETPOST('search_all', 'alphanohtml') ? GETPOST('search_all', 'alphanohtml') : GETPOST('sall', 'alphanohtml');
	$search = array();
	foreach ($object->fields as $key => $val) {
		if (GETPOST('search_' . $key, 'alpha') !== '') {
			$search[$key] = GETPOST('search_' . $key, 'alpha');
		}
		if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
			$search[$key . '_dtstart'] = dol_mktime(0, 0, 0, GETPOST('search_' . $key . '_dtstartmonth', 'int'), GETPOST('search_' . $key . '_dtstartday', 'int'), GETPOST('search_' . $key . '_dtstartyear', 'int'));
			$search[$key . '_dtend'] = dol_mktime(23, 59, 59, GETPOST('search_' . $key . '_dtendmonth', 'int'), GETPOST('search_' . $key . '_dtendday', 'int'), GETPOST('search_' . $key . '_dtendyear', 'int'));
		}
	}

	// List of fields to search into when doing a "search in all"
	$fieldstosearchall = array();
	foreach ($object->fields as $key => $val) {
		if (!empty($val['searchall'])) {
			$fieldstosearchall['t.' . $key] = $val['label'];
		}
	}

	// Definition of array of fields for columns
	$arrayfields = array();
	foreach ($object->fields as $key => $val) {
		// If $val['visible']==0, then we never show the field
		if (!empty($val['visible'])) {
			$visible = (int)dol_eval($val['visible'], 1);
			$arrayfields['t.' . $key] = array(
				'label' => $val['label'],
				'checked' => (($visible < 0) ? 0 : 1),
				'enabled' => ($visible != 3 && dol_eval($val['enabled'], 1)),
				'position' => $val['position'],
				'help' => isset($val['help']) ? $val['help'] : ''
			);
		}
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_array_fields.tpl.php';

	$object->fields = dol_sort_array($object->fields, 'position');
	$arrayfields = dol_sort_array($arrayfields, 'position');

	$permissiontoread = $user->rights->hrmtest->position->read;
	$permissiontoadd = $user->rights->hrmtest->position->write;
	$permissiontodelete = $user->rights->hrmtest->position->delete;

	// Security check
	if (empty($conf->hrmtest->enabled)) {
		accessforbidden('Module not enabled');
	}

	// Security check (enable the most restrictive one)
	if ($user->socid > 0) accessforbidden();
	//if ($user->socid > 0) accessforbidden();
	//$socid = 0; if ($user->socid > 0) $socid = $user->socid;
	//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
	//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
	//if (empty($conf->hrmtest->enabled)) accessforbidden();
	//if (!$permissiontoread) accessforbidden();


	/*
	 * Actions
	 */

	if (GETPOST('cancel', 'alpha')) {
		$action = 'list';
		$massaction = '';
	}
	if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
		$massaction = '';
	}

	$parameters = array();
	$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
	if ($reshook < 0) {
		setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	}

	if (empty($reshook)) {
		// Selection of new fields
		include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

		// Purge search criteria
		if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
			foreach ($object->fields as $key => $val) {
				$search[$key] = '';
				if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
					$search[$key . '_dtstart'] = '';
					$search[$key . '_dtend'] = '';
				}
			}
			$toselect = array();
			$search_array_options = array();
		}
		if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
			|| GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
			$massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
		}

		// Mass actions
		$objectclass = 'Position';
		$objectlabel = 'Position';
		$uploaddir = $conf->hrmtest->dir_output;
		include DOL_DOCUMENT_ROOT . '/core/actions_massactions.inc.php';
	}




	/*
	 * View
	 */

	$form = new Form($db);

	$now = dol_now();

	//$help_url="EN:Module_Position|FR:Module_Position_FR|ES:Módulo_Position";
	$help_url = '';
	$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv("Positions"));
	$morejs = array();
	$morecss = array();


	// Build and execute select
	// --------------------------------------------------------------------
	$sql = 'SELECT ';
	$sql .= $object->getFieldList('t');
	// Add fields from extrafields
	if (!empty($extrafields->attributes[$object->table_element]['label'])) {
		foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
			$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef." . $key . ' as options_' . $key . ', ' : '');
		}
	}
	// Add fields from hooks
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object); // Note that $action and $object may have been modified by hook
	$sql .= preg_replace('/^,/', '', $hookmanager->resPrint);
	$sql = preg_replace('/,\s*$/', '', $sql);
	$sql .= " FROM " . MAIN_DB_PREFIX . $object->table_element . " as t";
	if (isset($extrafields->attributes[$object->table_element]['label']) && is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) {
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . $object->table_element . "_extrafields as ef on (t.rowid = ef.fk_object)";
	}
	// Add table from hooks
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters, $object); // Note that $action and $object may have been modified by hook
	$sql .= $hookmanager->resPrint;
	if ($object->ismultientitymanaged == 1) {
		$sql .= " WHERE t.entity IN (" . getEntity($object->element) . ")";
	} else {
		$sql .= " WHERE 1 = 1";
	}
	foreach ($search as $key => $val) {
		if (array_key_exists($key, $object->fields)) {
			if ($key == 'status' && $search[$key] == -1) {
				continue;
			}
			$mode_search = (($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key])) ? 1 : 0);
			if ((strpos($object->fields[$key]['type'], 'integer:') === 0) || (strpos($object->fields[$key]['type'], 'sellist:') === 0) || !empty($object->fields[$key]['arrayofkeyval'])) {
				if ($search[$key] == '-1' || $search[$key] === '0') {
					$search[$key] = '';
				}
				$mode_search = 2;
			}
			if ($search[$key] != '') {
				$sql .= natural_search($key, $search[$key], (($key == 'status') ? 2 : $mode_search));
			}
		} else {
			if (preg_match('/(_dtstart|_dtend)$/', $key) && $search[$key] != '') {
				$columnName = preg_replace('/(_dtstart|_dtend)$/', '', $key);
				if (preg_match('/^(date|timestamp|datetime)/', $object->fields[$columnName]['type'])) {
					if (preg_match('/_dtstart$/', $key)) {
						$sql .= " AND t." . $columnName . " >= '" . $db->idate($search[$key]) . "'";
					}
					if (preg_match('/_dtend$/', $key)) {
						$sql .= " AND t." . $columnName . " <= '" . $db->idate($search[$key]) . "'";
					}
				}
			}
		}
	}
	if ($search_all) {
		$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
	}
	//$sql.= dolSqlDateFilter("t.field", $search_xxxday, $search_xxxmonth, $search_xxxyear);
	// Add where from extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_sql.tpl.php';
	// Add where from hooks
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object); // Note that $action and $object may have been modified by hook
	$sql .= $hookmanager->resPrint;

	/* If a group by is required
	$sql .= " GROUP BY ";
	foreach($object->fields as $key => $val) {
		$sql .= 't.'.$key.', ';
	}
	// Add fields from extrafields
	if (!empty($extrafields->attributes[$object->table_element]['label'])) {
		foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
			$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.', ' : '');
		}
	}
	// Add where from hooks
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldListGroupBy', $parameters, $object);    // Note that $action and $object may have been modified by hook
	$sql .= $hookmanager->resPrint;
	$sql = preg_replace('/,\s*$/', '', $sql);
	*/

	$sql .= $db->order($sortfield, $sortorder);

	// Count total nb of records
	$nbtotalofrecords = '';
	if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
		$resql = $db->query($sql);
		$nbtotalofrecords = $db->num_rows($resql);
		if (($page * $limit) > $nbtotalofrecords) {    // if total of record found is smaller than page * limit, goto and load page 0
			$page = 0;
			$offset = 0;
		}
	}
	// if total of record found is smaller than limit, no need to do paging and to restart another select with limits set.
	if (is_numeric($nbtotalofrecords) && ($limit > $nbtotalofrecords || empty($limit))) {
		$num = $nbtotalofrecords;
	} else {
		if ($limit) {
			$sql .= $db->plimit($limit + 1, $offset);
		}

		$resql = $db->query($sql);
		if (!$resql) {
			dol_print_error($db);
			exit;
		}

		$num = $db->num_rows($resql);
	}

	// Direct jump if only one record found
	if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all && !$page) {
		$obj = $db->fetch_object($resql);
		$id = $obj->rowid;
		header("Location: " . dol_buildpath('/hrmtest/position_card.php', 1) . '?id=' . $id);
		exit;
	}


	// Output page
	// --------------------------------------------------------------------


	// Example : Adding jquery code
	// print '<script type="text/javascript" language="javascript">
	// jQuery(document).ready(function() {
	// 	function init_myfunc()
	// 	{
	// 		jQuery("#myid").removeAttr(\'disabled\');
	// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
	// 	}
	// 	init_myfunc();
	// 	jQuery("#mybutton").click(function() {
	// 		init_myfunc();
	// 	});
	// });
	// </script>';

	$arrayofselected = is_array($toselect) ? $toselect : array();

	$param = '';
	if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
		$param .= '&contextpage=' . urlencode($contextpage);
	}
	if ($limit > 0 && $limit != $conf->liste_limit) {
		$param .= '&limit=' . urlencode($limit);
	}
	foreach ($search as $key => $val) {
		if (is_array($search[$key]) && count($search[$key])) {
			foreach ($search[$key] as $skey) {
				$param .= '&search_' . $key . '[]=' . urlencode($skey);
			}
		} else {
			$param .= '&search_' . $key . '=' . urlencode($search[$key]);
		}
	}
	if ($optioncss != '') {
		$param .= '&optioncss=' . urlencode($optioncss);
	}
	// Add $param from extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_param.tpl.php';
	// Add $param from hooks
	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldListSearchParam', $parameters, $object); // Note that $action and $object may have been modified by hook
	$param .= $hookmanager->resPrint;

	// List of mass actions available
	$arrayofmassactions = array(
		//'validate'=>img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Validate"),
		//'generate_doc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("ReGeneratePDF"),
		//'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
		//'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
	);
	if ($permissiontodelete) {
		$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"') . $langs->trans("Delete");
	}
	if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) {
		$arrayofmassactions = array();
	}
	$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

	print '<form method="POST" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">' . "\n";
	if ($optioncss != '') {
		print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
	}
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
	print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
	print '<input type="hidden" name="page" value="' . $page . '">';
	print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';

	$newcardbutton = dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', dol_buildpath('/hrmtest/position_card.php', 1) . '?action=create&backtopage=' . urlencode($_SERVER['PHP_SELF'] . '?fk_job=' . $fk_job), '', $permissiontoadd);

	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_' . $object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

	// Add code for pre mass action (confirmation or email presend form)
	$topicmail = "SendPositionRef";
	$modelmail = "position";
	$objecttmp = new Position($db);
	$trackid = 'xxxx' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/tpl/massactions_pre.tpl.php';

	if ($search_all) {
		foreach ($fieldstosearchall as $key => $val) {
			$fieldstosearchall[$key] = $langs->trans($val);
		}
		print '<div class="divsearchfieldfilter">' . $langs->trans("FilterOnInto", $search_all) . join(', ', $fieldstosearchall) . '</div>';
	}

	$moreforfilter = '';
	/*$moreforfilter.='<div class="divsearchfield">';
	$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
	$moreforfilter.= '</div>';*/

	$parameters = array();
	$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$moreforfilter .= $hookmanager->resPrint;
	} else {
		$moreforfilter = $hookmanager->resPrint;
	}

	if (!empty($moreforfilter)) {
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		print '</div>';
	}

	$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
	$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
	$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

	print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
	print '<table class="tagtable nobottomiftotal liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";


	// Fields title search
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
	foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if ($key == 'status') {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
		} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
		} elseif (in_array($val['type'], array('timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
		} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
		}
		if (!empty($arrayfields['t.' . $key]['checked'])) {
			print '<td class="liste_titre' . ($cssforfield ? ' ' . $cssforfield : '') . '">';
			if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
				print $form->selectarray('search_' . $key, $val['arrayofkeyval'], (isset($search[$key]) ? $search[$key] : ''), $val['notnull'], 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
			} elseif ((strpos($val['type'], 'integer:') === 0) || (strpos($val['type'], 'sellist:') === 0)) {
				print $object->showInputField($val, $key, (isset($search[$key]) ? $search[$key] : ''), '', '', 'search_', 'maxwidth125', 1);
			} elseif (!preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
				print '<input type="text" class="flat maxwidth75" name="search_' . $key . '" value="' . dol_escape_htmltag(isset($search[$key]) ? $search[$key] : '') . '">';
			} elseif (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
				print '<div class="nowrap">';
				print $form->selectDate($search[$key . '_dtstart'] ? $search[$key . '_dtstart'] : '', "search_" . $key . "_dtstart", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
				print '</div>';
				print '<div class="nowrap">';
				print $form->selectDate($search[$key . '_dtend'] ? $search[$key . '_dtend'] : '', "search_" . $key . "_dtend", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
				print '</div>';
			}
			print '</td>';
		}
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_input.tpl.php';

	// Fields from hook
	$parameters = array('arrayfields' => $arrayfields);
	$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
	print '</tr>' . "\n";


	// Fields title label
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
	foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if ($key == 'status') {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
		} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
		} elseif (in_array($val['type'], array('timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
		} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
			$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
		}
		if (!empty($arrayfields['t.' . $key]['checked'])) {
			print getTitleFieldOfList($arrayfields['t.' . $key]['label'], 0, $_SERVER['PHP_SELF'], 't.' . $key, '', $param, ($cssforfield ? 'class="' . $cssforfield . '"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield . ' ' : '')) . "\n";
		}
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters = array('arrayfields' => $arrayfields, 'param' => $param, 'sortfield' => $sortfield, 'sortorder' => $sortorder);
	$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ') . "\n";
	print '</tr>' . "\n";

	// Detect if we need a fetch on each output line
	$needToFetchEachLine = 0;
	if (isset($extrafields->attributes[$object->table_element]['computed']) && is_array($extrafields->attributes[$object->table_element]['computed']) && count($extrafields->attributes[$object->table_element]['computed']) > 0) {
		foreach ($extrafields->attributes[$object->table_element]['computed'] as $key => $val) {
			if (preg_match('/\$object/', $val)) {
				$needToFetchEachLine++; // There is at least one compute field that use $object
			}
		}
	}


	// Loop on record
	// --------------------------------------------------------------------
	$i = 0;
	$totalarray = array();
	$totalarray['nbfield'] = 0;
	while ($i < ($limit ? min($num, $limit) : $num)) {
		$obj = $db->fetch_object($resql);
		if (empty($obj)) {
			break; // Should not happen
		}

		// Store properties in $object
		$object->setVarsFromFetchObj($obj);

		// Show here line of result
		print '<tr class="oddeven">';
		foreach ($object->fields as $key => $val) {
			$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
			if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			} elseif ($key == 'status') {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'center';
			}

			if (in_array($val['type'], array('timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
			} elseif ($key == 'ref') {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'nowrap';
			}

			if (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && !in_array($key, array('rowid', 'status')) && empty($val['arrayofkeyval'])) {
				$cssforfield .= ($cssforfield ? ' ' : '') . 'right';
			}
			//if (in_array($key, array('fk_soc', 'fk_user', 'fk_warehouse'))) $cssforfield = 'tdoverflowmax100';

			if (!empty($arrayfields['t.' . $key]['checked'])) {
				print '<td' . ($cssforfield ? ' class="' . $cssforfield . '"' : '') . '>';
				if ($key == 'status') {
					print $object->getLibStatut(5);
				} elseif ($key == 'rowid') {
					print $object->showOutputField($val, $key, $object->id, '');
				} else {
					print $object->showOutputField($val, $key, $object->$key, '');
				}
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
				if (!empty($val['isameasure'])) {
					if (!$i) {
						$totalarray['pos'][$totalarray['nbfield']] = 't.' . $key;
					}
					if (!isset($totalarray['val'])) {
						$totalarray['val'] = array();
					}
					if (!isset($totalarray['val']['t.' . $key])) {
						$totalarray['val']['t.' . $key] = 0;
					}
					$totalarray['val']['t.' . $key] += $object->$key;
				}
			}
		}
		// Extra fields
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_print_fields.tpl.php';
		// Fields from hook
		$parameters = array('arrayfields' => $arrayfields, 'object' => $object, 'obj' => $obj, 'i' => $i, 'totalarray' => &$totalarray);
		$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) { // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			$selected = 0;
			if (in_array($object->id, $arrayofselected)) {
				$selected = 1;
			}
			print '<input id="cb' . $object->id . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $object->id . '"' . ($selected ? ' checked="checked"' : '') . '>';
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}

		print '</tr>' . "\n";

		$i++;
	}

	// Show total line
	include DOL_DOCUMENT_ROOT . '/core/tpl/list_print_total.tpl.php';

	// If no record found
	if ($num == 0) {
		$colspan = 1;
		foreach ($arrayfields as $key => $val) {
			if (!empty($val['checked'])) {
				$colspan++;
			}
		}
		print '<tr><td colspan="' . $colspan . '" class="opacitymedium">' . $langs->trans("NoRecordFound") . '</td></tr>';
	}


	$db->free($resql);

	$parameters = array('arrayfields' => $arrayfields, 'sql' => $sql);
	$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print '</table>' . "\n";
	print '</div>' . "\n";

	print '</form>' . "\n";

	if (in_array('builddoc', $arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords)) {
		$hidegeneratedfilelistifempty = 1;
		if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) {
			$hidegeneratedfilelistifempty = 0;
		}

		require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
		$formfile = new FormFile($db);

		// Show list of available documents
		$urlsource = $_SERVER['PHP_SELF'] . '?sortfield=' . $sortfield . '&sortorder=' . $sortorder;
		$urlsource .= str_replace('&amp;', '&', $param);

		$filedir = $diroutputmassaction;
		$genallowed = $permissiontoread;
		$delallowed = $permissiontoadd;

		print $formfile->showdocuments('massfilesarea_hrmtest', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
	}
	return array($arrayfields, $object, $action, $obj, $totalarray);
}

// End of page
llxFooter();
$db->close();
