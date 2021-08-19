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
 *    \file       position_card.php
 *        \ingroup    hrmtest
 *        \brief      Page to create/edit/view position
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
dol_include_once('/hrmtest/class/position.class.php');
dol_include_once('/hrmtest/class/job.class.php');
dol_include_once('/hrmtest/lib/hrmtest_position.lib.php');
//dol_include_once('/hrmtest/position.php');

$action 	= GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$id 	= GETPOST('id', 'int');

// Initialize technical objects
$object = new Position($db);
$res = $object->fetch($id);
if ($res < 0) {
	dol_print_error($db, $object->error);
}


$langs->loadLangs(array("hrmtest@hrmtest", "other"));


DisplayPositionCard($conf, $langs, $db, $object, $permissiontoadd, $lineid);

/**
 * 		Show the card of a position
 *
 *		@param	Conf			 $conf			  Object conf
 * 		@param	Translate		 $langs			  Object langs
 * 		@param	DoliDB			 $db			  Database handler
 * 		@param	Position		 $object		  Position object
 * 		@param $permissiontoadd	 $permissiontoadd Rights/permissions
 * 		@param $lineid			 $lineid		  Id of a permission line
 * 		@return array
 */
function DisplayPositionCard($conf, $langs, $db, $object, $permissiontoadd, $lineid)
{

	global $user,$langs, $db, $conf, $extrafields, $hookmanager;

	// Get parameters
	$id 	= GETPOST('id', 'int');
	$fk_job = GETPOST('fk_job', 'int');

	$ref 	= GETPOST('ref', 'alpha');
	$action = GETPOST('action', 'aZ09');
	$confirm = GETPOST('confirm', 'alpha');
	$cancel = GETPOST('cancel', 'aZ09');
	$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'positioncard'; // To manage different context of search
	$backtopage = GETPOST('backtopage', 'alpha');
	$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
	$lineid   = GETPOST('lineid', 'int');

	// Initialize technical objects
	$object = new Position($db);
	$res = $object->fetch($id);
	if ($res < 0) {
		dol_print_error($db, $object->error);
	}

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
	$permissiontoadd = $user->rights->hrmtest->position->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
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
					$backtopage = dol_buildpath('/hrmtest/position.php', 1) . '?fk_job=' . ($fk_job > 0 ? $fk_job : '__ID__');
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

		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?ref=' . $object->ref . '">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="' . $object->id . '">';
		print '<input type="hidden" name="ref" value="' . $object->ref . '">';

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


		$head = PositionCardPrepareHead($object);
		print dol_get_fiche_head($head, 'position', $langs->trans("Workstation"), -1, $object->picto);

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
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), '', 'confirm_xxx', $formquestion, 0, 1, 220);
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
		$linkback = '<a href="' . dol_buildpath('/hrmtest/position.php', 1) . '?restore_lastsearch_values=1' . (!empty($object->fk_job) ? '&fk_job=' . $object->fk_job : '') . '">' . $langs->trans("BackToList") . '</a>';

		$morehtmlref = '<div class="refidno">';

		$morehtmlref .= '</div>';

		dol_banner_tab($object, 'id', $linkback, 1, 'rowid', $ref, $morehtmlref);


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

		/*
		 * Action bar
		 */
		print '<div class="tabsAction">';

		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook


		//Modify
		if ($user->rights->societe->contact->creer) {
			print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit">' . $langs->trans('Modify') . '</a>';
		}
		// Delete
		if ($user->rights->societe->contact->supprimer) {
			print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken() . '' . ($backtopage ? '&backtopage=' . urlencode($backtopage) : '') . '">' . $langs->trans('Delete') . '</a>';
		}

	}
	return array($fk_job, $object, $action);
}

//if ($action != 'presend') {
//	$formfile = new FormFile($db);
//	print '<div class="fichecenter"><div class="fichehalfleft">';
//
//	if (empty($conf->global->SOCIETE_DISABLE_BUILDDOC)) {
//		print '<a name="builddoc"></a>'; // ancre
//
//		/*
//		 * Generated documents
//		 */
//		$filedir = $conf->societe->multidir_output[$object->entity].'/'.$object->id;
//		$urlsource = $_SERVER["PHP_SELF"]."?socid=".$object->id;
//		$genallowed = $user->rights->societe->lire;
//		$delallowed = $user->rights->societe->creer;
//
//		print $formfile->showdocuments('company', $object->id, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 0, 0, 0, 28, 0, 'entity='.$object->entity, 0, '', $object->default_lang);
//	}
//
//
//	print '</div><div class="fichehalfright"><div class="ficheaddleft">';
//
//	$MAXEVENT = 10;
//
//	$morehtmlright = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', DOL_URL_ROOT.'/societe/agenda.php?socid='.$object->id);
//
//	// List of actions on element
//	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
//	$formactions = new FormActions($db);
//	$somethingshown = $formactions->showactions($object, '', $object->id, 1, '', $MAXEVENT, '', $morehtmlright); // Show all action for thirdparty
//
//	print '</div></div></div>';
//}

if ($action != 'presend') {

	$formfile = new FormFile($db);
	$form = new Form($db);

	print '<div class="fichecenter"><div class="fichehalfleft">';
	print '<a name="builddoc"></a>'; // ancre

	$includedocgeneration = 0;

	// Documents
	if ($includedocgeneration) {
		$objref = dol_sanitizeFileName($object->ref);
		$relativepath = $objref . '/' . $objref . '.pdf';
		$filedir = $conf->hrmtest->dir_output . '/' . $object->element . '/' . $objref;
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $user->rights->hrmtest->position->read; // If you can read, you can build the PDF to read content
		$delallowed = $user->rights->hrmtest->position->write; // If you can create/edit, you can remove a file on card
		print $formfile->showdocuments('hrmtest:position', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
	}

	// Show links to link elements
	$linktoelem = $form->showLinkToObjectBlock($object, null, array('position'));
	$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	$MAXEVENT = 10;

	$morehtmlright = '<a href="' . dol_buildpath('/hrmtest/position_agenda.php', 1) . '?id=' . $object->id . '">';
	$morehtmlright .= $langs->trans("SeeAll");
	$morehtmlright .= '</a>';

	// List of actions on element
	include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	$formactions = new FormActions($db);
	$somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

	print '</div></div></div>';
}


// End of page
llxFooter();
$db->close();
