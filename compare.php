<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/compare.php
 * \ingroup     hrmtest
 * \brief       This file compares skills of user groups
 */


//require 'config.php';

ini_set('display_errors', 1);

dol_include_once('/hrmtest/class/skill.class.php');
dol_include_once('/hrmtest/class/job.class.php');
dol_include_once('/hrmtest/class/evaluation.class.php');

$langs->load('hrmtest@hrmtest');
$css = array();
$css[] = '/hrmtest/css/style.css';
llxHeader('', 'HRMtest Comparaison', '', '', 0, 0, '', $css);
print load_fiche_titre($langs->trans("Comparer"));

$PDOdb = new TPDOdb;
$form = new Form($db);
?>
	<script type="text/javascript">

		$(document).ready(function () {

			$("li[fk_user]").click(function () {

				if ($(this).hasClass('disabled')) {
					$(this).removeClass('disabled');
				} else {
					$(this).addClass('disabled');
				}


				$userl = $(this).closest('ul');
				listname = $userl.attr('name');

				var TId = [];

				$userl.find('li').each(function (i, item) {

					if ($(item).hasClass('disabled')) {
						TId.push($(item).attr('fk_user'));
					}

				});

				$('#' + listname + '_excluded_id').val(TId.join(','));

			});

		});


	</script>


<?php

$formCore = new TFormCore('auto', 'formCompare', 'post');

$fk_usergroup2 = 0;
$fk_job = (int)GETPOST('fk_job');
if ($fk_job <= 0) $fk_usergroup2 = GETPOST('fk_usergroup2');

$fk_usergroup1 = GETPOST('fk_usergroup1');

?>
	<div class="tabBar">
		<table class="border" width="100%">
			<tr>
				<td>Groupe à comparer</td>
				<td><?php echo $form->select_dolgroups($fk_usergroup1, 'fk_usergroup1', 1); ?></td>
			</tr>
			<tr>
				<td>Second élèment à comparer</td>

				<td><?php echo $form->select_dolgroups($fk_usergroup2, 'fk_usergroup2', 1) . ' ' . $langs->trans('Or') . ' ' . $formCore->combo_sexy('', 'fk_job', Job::getCombo(), $fk_job, 1, '', '', 'flat', '', 'false', 1); ?></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><?php echo $formCore->btsubmit($langs->trans('Filter'), 'bt1'); ?></td>
				<td></td>
			</tr>
		</table>
	</div>

	<div id="compare" width="100%" style="position:relative;">
		<table width="100%">
			<tr>
				<th></th>
				<th>Compétences</th>
				<th>Notes</th>
				<th>Différences</th>
				<th>Notes</th>
				<th></th>
			</tr>

			<?php
			echo '<tr><td id="list-user-left" style="width:30%" valign="top">';

			$TUser1 = $TUser2 = array();

			$liste1 = _liste_user($PDOdb, $TUser1, $fk_usergroup1, 'liste1');

			$TCompetence1 = TNodeGPEC::getCompetenceForUsers($PDOdb, $TUser1);
			if ($fk_job > 0) {

				$TCompetence2 = TNodeGPEC::getCompetenceForEmploi($PDOdb, $fk_job);

				$job = new Job($db);
				$job->load($PDOdb, $fk_job);
				$liste2 = '<ul><li>
	      <h3>' . $job->label . '</h3>
	      <p>' . $job->description . '</p>
	    </li></ul>';

			} else {
				$liste2 = _liste_user($PDOdb, $TUser2, $fk_usergroup2, 'liste2');
				$TCompetence2 = TNodeGPEC::getCompetenceForUsers($PDOdb, $TUser2);
			}

			$TCompetence = _merge_competence($TCompetence1, $TCompetence2);

			echo $liste1;

			echo '</td>';

			echo '<td id="" style="width:20%" valign="top">' . _listeCompetence($TCompetence) . '</td>';
			echo '<td id="" style="width:5%" valign="top">' . _note($TCompetence, 'note1') . '</td>';
			echo '<td id="" style="width:10%" valign="top">' . _diff($TCompetence) . '</td>';
			echo '<td id="" style="width:5%" valign="top">' . _note($TCompetence, 'note2') . '</td>';

			echo '<td id="list-user-right" style="width:30%" valign="top">';

			echo $liste2;

			echo '</td></tr>';

			echo '</table>';

			$formCore->end();
			?>

			<div style="background:#eee;border-radius:5px 0;margin:30px 0 10px;font-style:italic;padding:5px;">
				<h4>Légende</h4>
				<p>
					<span style="vertical-align:middle" class="toohappy diffnote little"></span> Compétence acquise par
					un ou plusieurs utilisateur mais non demandé par le second élément de comparaison
				</p>
				<p>
					<span style="vertical-align:middle" class="veryhappy diffnote little"></span> Niveau max supérieur à
					celui demandé
				</p>
				<p>
					<span style="vertical-align:middle" class="happy diffnote little"></span> Niveau max égal à celui
					demandé
				</p>
				<p>
					<span style="vertical-align:middle" class="sad diffnote little"></span> Niveau max inférieur à celui
					demandé
				</p>
				<p>
					<span style="vertical-align:middle" class="toosad diffnote little"></span> Compétence non acquise
					par tous les utilisateur et demandé par le second élément de comparaison
				</p>
				<div style="clear:both"></div>
			</div>
	</div>
<?php
dol_fiche_end();
llxFooter();

function _diff(&$TCompetence)
{

	$out = '<ul class="diff">';

	foreach ($TCompetence as $id => &$c) {
		$class = 'diffnote';

		if (empty($c->note2)) $class .= ' toohappy';
		else if (empty($c->note1)) $class .= ' toosad';
		else if ($c->note1 == $c->note2) $class .= ' happy';
		else if ($c->note2 < $c->note1) $class .= ' veryhappy';
		else if ($c->note2 > $c->note1) $class .= ' sad';

		$out .= '<li fkcompetence="' . $id . '" class="' . $class . '" style="text-align:center;">
	      <span class="' . $class . '">&nbsp;</span>
	    </li>';

	}

	$out .= '</ul>';

	return $out;
}

function _note(&$TCompetence, $field)
{
	global $langs;

	$out = '<ul class="competence">';

	foreach ($TCompetence as $id => &$c) {
		$class = "note";
		$how_many = 0;
		if (empty($c->{$field})) {
			$note = 'x';
			$class .= ' none';
		} else {
			$note = $c->{$field};
			$how_many = ($field === 'note1') ? $c->how_many_max1 : $c->how_many_max2;
		}

		$out .= '<li fkcompetence="' . $id . '" style="text-align:center;">
	      <p><span class="' . $class . ' classfortooltip" title="' . $langs->trans('Evaluation') . ' Max">' . $note . '</span>' . ($how_many > 0 ? '<span class="bubble classfortooltip" title="' . $langs->trans('HowManyUserWithThisMaxNote') . '">' . $how_many . '</span>' : '') . '</p>
	    </li>';

	}

	$out .= '</ul>';

	return $out;

}

function _listeCompetence(&$TCompetence)
{

	$out = '<ul class="competence">';

	foreach ($TCompetence as $id => &$c) {

		$out .= '<li fkcompetence="' . $id . '">
	      <h3>' . $c->label . '</h3>
	      <p>' . $c->description . '</p>
	    </li>';

	}

	$out .= '</ul>';

	return $out;

}

function _merge_competence($TCompetence1, $TCompetence2)
{
	global $PDOdb;
	$Tab = array();

	foreach ($TCompetence1 as &$c) {
		$comp = new TNodeGPEC();
		$comp->load($PDOdb, $c->rowid);
		if (in_array($comp->object_type, array("COMP", "RISK", "TASK"))) {

			if (empty($Tab[$c->rowid])) $Tab[$c->rowid] = new stdClass;

			$Tab[$c->rowid]->label = $c->label;
			$Tab[$c->rowid]->note1 = $c->note;

			$Tab[$c->rowid]->how_many_max1 = $c->how_many_max;

			$Tab[$c->rowid]->description = $c->description;
		}

	}

	foreach ($TCompetence2 as &$c) {
		$comp = new TNodeGPEC();
		$comp->load($PDOdb, $c->rowid);
		if (in_array($comp->object_type, array("COMP", "RISK", "TASK"))) {

			if (empty($Tab[$c->rowid])) $Tab[$c->rowid] = new stdClass;

			$Tab[$c->rowid]->label = $c->label;
			$Tab[$c->rowid]->note2 = $c->note;
			$Tab[$c->rowid]->description = $c->description;
			$Tab[$c->rowid]->how_many_max2 = $c->how_many_max;
		}

	}

	return $Tab;

}

function _liste_user(&$PDOdb, &$TUser, $fk_usergroup = 0, $listename = 'list-user')
{
	global $db, $langs, $conf, $form;

	$out = '';
	if ($fk_usergroup > 0) {

		$liste = $listename . '_excluded_id';

		$listeExcludedIds = GETPOST($liste);


		$sql = "SELECT DISTINCT u.rowid FROM " . MAIN_DB_PREFIX . "user u
		LEFT JOIN " . MAIN_DB_PREFIX . "usergroup_user ugu ON (u.rowid = ugu.fk_user)
		WHERE 1
		AND u.statut > 0
		AND ugu.fk_usergroup=" . $fk_usergroup;

		$res = $db->query($sql);

		$out .= '<ul name="' . $listename . '">';

		$TExcludedId = explode(',', $listeExcludedIds);

		$formCore = new TFormCore;
		$out .= $formCore->hidden($liste, $listeExcludedIds);

		while ($obj = $db->fetch_object($res)) {

			$class = '';

			$user = new User($db);
			$user->fetch($obj->rowid);

			$name = $user->getFullName($langs);
			if (empty($name)) $name = $user->login;

			if (in_array($user->id, $TExcludedId)) {
				$class .= ' disabled';
			} else {
				if (!in_array($user->id, $TUser)) $TUser[] = $user->id;
			}


			$desc = '';

			$job = Job::getLastJobForUser($user->id);
			$desc .= $job;

			$evaluation = Evaluation::getLastEvaluationForUser($PDOdb, $user->id);

			if (!empty($evaluation)) {
				$desc .= ' - ' . $langs->trans('DateLastEval') . ' : ' . dol_print_date($evaluation->date_eval);
			}


			if (!empty($user->array_options['options_DDA'])) $desc .= '<br />' . $langs->trans('Anciennete') . ' : ' . dol_print_date(strtotime($user->array_options['options_DDA']));

			$out .= '<li fk_user="' . $user->id . '" class="' . $class . '">
		      ' . $form->showphoto('userphoto', $user, 0, 0, 0, 'photoref', 'small', 1, 0, 1) . '
		      <h3>' . $name . '</h3>
		      <p>' . $desc . '</p>
		    </li>';


		}

		$out .= '</ul>';
	}

	return $out;
}
