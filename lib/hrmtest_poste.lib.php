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
 * \file    lib/hrmtest_poste.lib.php
 * \ingroup hrmtest
 * \brief   Library files with common functions for Poste
 */

/**
 * Prepare array of tabs for Poste
 *
 * @param	Poste	$object		Poste
 * @return 	array					Array of tabs
 */
function postePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("hrmtest@hrmtest");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/hrmtest/poste_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
		$nbNote = 0;
		if (!empty($object->note_private)) {
			$nbNote++;
		}
		if (!empty($object->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = dol_buildpath('/hrmtest/poste_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
		}
		$head[$h][2] = 'note';
		$h++;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
	$upload_dir = $conf->hrmtest->dir_output."/poste/".dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
	$nbLinks = Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/hrmtest/poste_document.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($nbFiles + $nbLinks) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
	}
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath("/hrmtest/poste_agenda.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'agenda';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@hrmtest:/hrmtest/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'poste@hrmtest');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'poste@hrmtest', 'remove');

	return $head;
}

/**
 * 		Show html area for list of positions
 *
 *		@param	Conf		$conf		Object conf
 * 		@param	Translate	$langs		Object langs
 * 		@param	DoliDB		$db			Database handler
 * 		@param	Job			$object		Job object
 *      @param  string		$backtopage	Url to go once position is created
 *      @return	int
 */
function show_positions($conf, $langs, $db, $object, $backtopage = '')
{
	global $user, $conf, $extrafields, $hookmanager;
	global $contextpage;

	$optioncss = GETPOST('optioncss', 'alpha');
	$sortfield = GETPOST("sortfield", 'alpha');
	$sortorder = GETPOST("sortorder", 'alpha');
	$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');

	$search_name    = GETPOST("search_name", 'alpha');
	$search_datestart = GETPOST("search_address", 'alpha');
	$search_dateend   = GETPOST("search_poste", 'alpha');
	$search_contract = GETPOST("search_contract", 'int');

	$position = new Position($db);

	$extrafields->fetch_name_optionals_label($position->table_element);

	$position->fields = array(
		'name'      =>array('type'=>'varchar(128)', 'label'=>'Name', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>10, 'searchall'=>1),
		'datestart' =>array('type'=>'date', 'label'=>'DateStart', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>20),
		'dateend'   =>array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>30),
		'constract' =>array('type'=>'varchar(128)', 'label'=>'>Constract', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>40),
		'constract' =>array('type'=>'varchar(128)', 'label'=>'>Constract', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'showoncombobox'=>1, 'index'=>1, 'position'=>40),
	);

	// Definition of fields for list
	$arrayfields = array(
		'p.rowid'=>array('label'=>"TechnicalID", 'checked'=>(!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) ? 1 : 0), 'enabled'=>(!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) ? 1 : 0), 'position'=>1),
		'p.name'=>array('label'=>"Name", 'checked'=>1, 'position'=>10),
		'p.datestart'=>array('label'=>"PostOrFunction", 'checked'=>1, 'position'=>20),
		'p.dateend'=>array('label'=>(empty($conf->dol_optimize_smallscreen) ? $langs->trans("Address").' / '.$langs->trans("Phone").' / '.$langs->trans("Email") : $langs->trans("Address")), 'checked'=>1, 'position'=>30),
		'p.constract'=>array('label'=>"Status", 'checked'=>1, 'position'=>50, 'class'=>'center'),
		'p.'=>array('label'=>"Status", 'checked'=>1, 'position'=>50, 'class'=>'center'),
	);
	// Extra fields
	if (!empty($extrafields->attributes[$position->table_element]['label']) && is_array($extrafields->attributes[$position->table_element]['label']) && count($extrafields->attributes[$position->table_element]['label'])) {
		foreach ($extrafields->attributes[$position->table_element]['label'] as $key => $val) {
			if (!empty($extrafields->attributes[$position->table_element]['list'][$key])) {
				$arrayfields["ef.".$key] = array(
					'label'=>$extrafields->attributes[$position->table_element]['label'][$key],
					'checked'=>(($extrafields->attributes[$position->table_element]['list'][$key] < 0) ? 0 : 1),
					'position'=>1000 + $extrafields->attributes[$position->table_element]['pos'][$key],
					'enabled'=>(abs($extrafields->attributes[$position->table_element]['list'][$key]) != 3 && $extrafields->attributes[$position->table_element]['perms'][$key]));
			}
		}
	}

	// Initialize array of search criterias
	$search = array();
	foreach ($arrayfields as $key => $val) {
		$queryName = 'search_'.substr($key, 2);
		if (GETPOST($queryName, 'alpha')) {
			$search[substr($key, 2)] = GETPOST($queryName, 'alpha');
		}
	}
	$search_array_options = $extrafields->getOptionalsFromPost($position->table_element, '', 'search_');

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		$search_name = '';
		$search_datestart = array();
		$search_dateend = '';
		$search_constract = '';
		$search = array();
		$search_array_options = array();


		foreach ($position->fields as $key => $val) {
			$search[$key] = '';
		}
	}

	$position->fields = dol_sort_array($position->fields, 'position');
	$arrayfields = dol_sort_array($arrayfields, 'position');

	$newcardbutton = '';
	if ($user->rights->job->position->creer) {
		$addposition = $langs->trans("AddPosition");
		$newcardbutton .= dolGetButtonTitle($addposition, '', 'fa fa-plus-circle', DOL_URL_ROOT.'/hrmtest/position.php?fk_job='.$object->id.'&amp;action=create&amp;backtopage='.urlencode($backtopage));
	}

	print "\n";

	$title = $langs->trans("PositionsForJob");
	print load_fiche_titre($title, $newcardbutton, '');

	$token = '';
	if (function_exists('newToken')) $token = "&token=".newToken();

	print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'" name="formfilter">';
	print '<input type="hidden" name="token" value="'.$token.'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="fk_job" value="'.$object->id.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="page" value="'.$page.'">';

	$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
//	$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
	//if ($massactionbutton) $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);

	print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
	print "\n".'<table class="tagtable liste">'."\n";

	$param = "fk_job=".urlencode($object->id);
	if ($search_name != '') {
		$param .= '&search_name='.urlencode($search_name);
	}
	if ($search_datestart != '') {
		$param .= '&search_datestart='.urlencode($search_datestart);
	}
	if ($search_dateend != '') {
		$param .= '&search_dateend='.urlencode($search_dateend);
	}
	if ($search_constract != '') {
		$param .= '&search_constract='.urlencode($search_constract);
	}
	if ($optioncss != '') {
		$param .= '&optioncss='.urlencode($optioncss);
	}

	// Add $param from extra fields
	$extrafieldsobjectkey = $position->table_element;
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

	$sql = "SELECT p.rowid, p.ref, p.description, p.date_creation, p.tms, p.fk_contrat, p.fk_user, p.fk_job, p.date_end, p.date_start, p.commentaire_abandon";
	$sql .= " FROM ".MAIN_DB_PREFIX."hrmtest_position as p";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."hrmtest_job as j on (p.fk_job = j.rowid)";
	//TODO Si le module contrat existe et est activé
//	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."contrat as c on (p.fk_contrat = c.rowid)";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u on (p.fk_user = u.rowid)";
//	$sql .= " WHERE p.ref = ".$object->id;

	if ($search_name) {
		$sql .= natural_search(array('p.lastname', 'p.firstname'), $search_name);
	}
	if ($search_datestart) {
		$sql .= natural_search('p.datestart', $search_datestart);
	}
	if ($search_dateend) {
		$sql .= natural_search('p.dateend', $search_dateend);
	}
	if ($search_constract) {
		$sql .= natural_search('p.constract', $search_constract);
	}

	dol_syslog('core/lib/hrmtest_poste.lib.php :: show_positions', LOG_DEBUG);
	$result = $db->query($sql);
	if (!$result) {
		dol_print_error($db);
	}

	$num = $db->num_rows($result);

	// Fields title search
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
	foreach ($position->fields as $key => $val) {
		$align = '';
		if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$align .= ($align ? ' ' : '').'center';
		}
		if (in_array($val['type'], array('timestamp'))) {
			$align .= ($align ? ' ' : '').'nowrap';
		}
		if ($key == 'status' || $key == 'statut') {
			$align .= ($align ? ' ' : '').'center';
		}
		if (!empty($arrayfields['p.'.$key]['checked']) || !empty($arrayfields['sc.'.$key]['checked']))
			print '<td class="liste_titre'.($align ? ' '.$align : '').'">';
		else
			print '<input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.(!empty($search[$key]) ? dol_escape_htmltag($search[$key]) : '').'">';

			print '</td>';
	}
	// Extra fields
	$extrafieldsobjectkey = $position->table_element;
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

	// Fields from hook
	$parameters = array('arrayfields'=>$arrayfields);
	$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $position); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print '<td class="liste_titre" align="right">';
//	print $form->showFilterButtons();
	print '</td>';
	print '</tr>'."\n";


	// Fields title label
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
	foreach ($position->fields as $key => $val) {
		$align = '';
		if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$align .= ($align ? ' ' : '').'center';
		}
		if (in_array($val['type'], array('timestamp'))) {
			$align .= ($align ? ' ' : '').'nowrap';
		}
		if (!empty($arrayfields['p.'.$key]['checked'])) {
			print getTitleFieldOfList($val['label'], 0, $_SERVER['PHP_SELF'], 'p.'.$key, '', $param, ($align ? 'class="'.$align.'"' : ''), $sortfield, $sortorder, $align.' ')."\n";
		}
		if (!empty($arrayfields['sc.'.$key]['checked'])) {
			print getTitleFieldOfList($arrayfields['sc.'.$key]['label'], 0, $_SERVER['PHP_SELF'], '', '', $param, ($align ? 'class="'.$align.'"' : ''), $sortfield, $sortorder, $align.' ')."\n";
		}
	}
	// Extra fields
	$extrafieldsobjectkey = $position->table_element;
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
	$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', 'align="center"', $sortfield, $sortorder, 'maxwidthsearch ')."\n";
	print '</tr>'."\n";

	$i = -1;

	if ($num || (GETPOST('button_search') || GETPOST('button_search.x') || GETPOST('button_search_x'))) {
		$i = 0;

		while ($i < $num) {
			$obj = $db->fetch_object($result);

			$position->id = $obj->rowid;
			$position->ref = $obj->rowid;
			$position->name = $obj->name;
			$position->firstname = $obj->firstname;
			$position->date_start = $obj->datestart;
			$position->date_end = $obj->dateend;
			$position->constract = $obj->constract;

			$position->fetch_optionals();

			if (is_array($position->array_options)) {
				foreach ($position->array_options as $key => $val) {
					$obj->$key = $val;
				}
			}

			print '<tr class="oddeven">';

			// ID
			if (!empty($arrayfields['p.rowid']['checked'])) {
				print '<td>';
				print $position->id;
				print '</td>';
			}

			// Name
			if (!empty($arrayfields['p.name']['checked'])) {
				print '<td>';
				print $form->showphoto('position', $position, 0, 0, 0, 'photorefnoborder valignmiddle marginrightonly', 'small', 1, 0, 1);
				print $position->getNomUrl(0, '', 0, '&backtopage='.urlencode($backtopage));
				print '</td>';
			}

			// Date start
			if (!empty($arrayfields['p.datestart']['checked'])) {
				print '<td>';
				if ($obj->datestart) {
					print $obj->datestart;
				}
				print '</td>';
			}

			// Date end
			if (!empty($arrayfields['p.dateend']['checked'])) {
				print '<td>';
				if ($obj->dateend) {
					print $obj->dateend;
				}
				print '</td>';
			}

			// Constract
			if (!empty($arrayfields['p.constract']['checked'])) {
				print '<td>';
				if ($obj->constract) {
					print $obj->constract;
				}
				print '</td>';
			}

			// Extra fields
			$extrafieldsobjectkey = $position->table_element;
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';

			// Actions
			print '<td align="right">';

			print '</td>';

			print "</tr>\n";
			$i++;
		}
	} else {
		$colspan = 1;
		foreach ($arrayfields as $key => $val) {
			if (!empty($val['checked'])) {
				$colspan++;
			}
		}
		print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
	}
	print "\n</table>\n";
	print '</div>';

	print '</form>'."\n";

	return $i;
}
