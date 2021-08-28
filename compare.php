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

 * \file        class/compare.php
 * \ingroup     hrmtest
 * \brief       This file compares skills of user groups
 *
 * Displays a table in three parts.
 * 1-  the left part displays the list of users of the selected group 1.
 *
 * 2- the central part displays the skills. display of the maximum score for this group and the number of occurrences.
 *
 * 3-  the right part displays the members of group 2 or the job to be compared
 *
 *
 *
 */

require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

ini_set('display_errors', 1);

dol_include_once('/hrmtest/class/skill.class.php');
dol_include_once('/hrmtest/class/job.class.php');
dol_include_once('/hrmtest/class/evaluation.class.php');
dol_include_once('/hrmtest/class/position.class.php');
dol_include_once('/hrmtest/lib/hrmtest.lib.php');

$langs->load('hrmtest@hrmtest');
$css = array();
$css[] = '/hrmtest/css/style.css';
llxHeader('', 'HRMtest Comparaison', '', '', 0, 0, '', $css);
print load_fiche_titre($langs->trans("Comparer"));

//$PDOdb = new TPDOdb;
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
$job = new Job($db);
$form = new Form($db);

$fk_usergroup2 = 0;
$fk_job = (int)GETPOST('fk_job');
if ($fk_job <= 0) $fk_usergroup2 = GETPOST('fk_usergroup2');

$fk_usergroup1 = GETPOST('fk_usergroup1');

?><form action="<?php echo $_SERVER['PHP_SELF'] ?>">

	<div class="tabBar">
		<table class="border" width="100%">
			<tr>
				<td>Groupe à comparer</td>
				<td><?php echo $form->select_dolgroups($fk_usergroup1, 'fk_usergroup1', 1); ?></td>
			</tr>
			<tr>
				<td>Second élèment à comparer</td>

				<td><?php echo $form->select_dolgroups($fk_usergroup2, 'fk_usergroup2', 1) . ' ' . $langs->trans('Or') . ' ' . select_jobs($fk_job, 'fk_job', 1); ?></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><?php echo displaySubmitButton($langs->trans('Filter'), 'bt1'); ?></td>
				<td></td>
			</tr>
		</table>
	</div>
</form>

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

			$userlist1 = displayUsersListWithPicto($TUser1, $fk_usergroup1, 'userlist1');


			$skill = new Skill($db);
			$TSkill1 = getSkillForUsers($TUser1);

			if ($fk_job > 0)
			{
				$TSkill2 = getSkillForJob($fk_job);

				$job = new Job($db);
				$job->fetch($fk_job);
				$userlist2 = '<ul>
								  <li>
									  <h3>' . $job->ref . '</h3>
									  <p>'  . $job->description . '</p>
							   	  </li>
						  	  </ul>';
			}
			else
			{
				$userlist2 = displayUsersListWithPicto($TUser2, $fk_usergroup2, 'userlist2');
				$TSkill2 = getSkillForUsers($TUser2);

			}

			$TMergedSkills = mergeSkills($TSkill1, $TSkill2);

			echo $userlist1;

			echo '</td>';

			echo '<td id="" style="width:20%" valign="top">' . skillList($TMergedSkills) . '</td>';
			echo '<td id="" style="width:5%" valign="top">' . rate($TMergedSkills, 'rate1') . '</td>';
			echo '<td id="" style="width:10%" valign="top">' . diff($TMergedSkills) . '</td>';
			echo '<td id="" style="width:5%" valign="top">' . rate($TMergedSkills, 'rate2') . '</td>';

			echo '<td id="list-user-right" style="width:30%" valign="top">';

			echo $userlist2;

			echo '</td></tr>';

			echo '</table>';

			endf();
			?>

			<div style="background:#eee;border-radius:5px 0;margin:30px 0 10px;font-style:italic;padding:5px;">
				<h4>Légende</h4>
				<p>
					<span style="vertical-align:middle" class="toohappy diffnote little"></span> Compétence acquise par
					un ou plusieurs utilisateurs mais non demandé par le second élément de comparaison
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
					par tous les utilisateurs et demandé par le second élément de comparaison
				</p>
				<div style="clear:both"></div>
			</div>
	</div>
<?php
dol_fiche_end();
llxFooter();


/**
 * @param $TMergedSkills
 * @return string
 */
function diff(&$TMergedSkills)
{

	$out = '<ul class="diff">';

	foreach ($TMergedSkills as $id => &$sk) {
		$class = 'diffnote';

		if (empty($sk->rate2)) $class .= ' toohappy';
		else if (empty($sk->rate1)) $class .= ' toosad';
		else if ($sk->rate1 == $sk->rate2) $class .= ' happy';
		else if ($sk->rate2 < $sk->rate1) $class .= ' veryhappy';
		else if ($sk->rate2 > $sk->rate1) $class .= ' sad';

		$out .= '<li fk_skill="' . $id . '" class="' . $class . '" style="text-align:center;">
	      <span class="' . $class . '">&nbsp;</span>
	    </li>';

	}

	$out .= '</ul>';

	return $out;
}

/**
 * @param $TMergedSkills
 * @param $field
 * @return string
 */
function rate(&$TMergedSkills, $field)
{
	global $langs;

	$out = '<ul class="competence">';

	foreach ($TMergedSkills as $id => &$sk) {
		$class = "note";
		$how_many = 0;
		if (empty($sk->{$field})) {
			$note = 'x';
			$class .= ' none';
		} else {
			$note = $sk->{$field};
			$how_many = ($field === 'rate1') ? $sk->how_many_max1 : $sk->how_many_max2;
		}

		$out .= '<li fk_skill="' . $id . '" style="text-align:center;">
	      <p><span class="' . $class . ' classfortooltip" title="' . $langs->trans('Evaluation') . ' Max">' . $note . '</span>' . ($how_many > 0 ? '<span class="bubble classfortooltip" title="' . $langs->trans('HowManyUserWithThisMaxNote') . '">' . $how_many . '</span>' : '') . '</p>
	    </li>';

	}

	$out .= '</ul>';

	return $out;

}

/**
 * @param $TMergedSkills
 * @return string
 */
function skillList(&$TMergedSkills)
{

	$out = '<ul class="competence">';

	foreach ($TMergedSkills as $id => &$sk) {

		$out .= '<li fk_skill="' . $id . '">
	      <h3>' . $sk->label . '</h3>
	      <p>' . $sk->description . '</p>
	    </li>';

	}

	$out .= '</ul>';

	return $out;

}

/**
 *  create an array of lines [ skillLabel,dscription, maxrank on group1 , minrank needed for this skill ]
 *
 * @param $TSkill1
 * @param $TSkill2
 * @return array
 */
function mergeSkills($TSkill1, $TSkill2)
{

	$Tab = array();

	foreach ($TSkill1 as &$sk) {

			if (empty($Tab[$sk->fk_skill])) $Tab[$sk->fk_skill] = new stdClass;

			$Tab[$sk->fk_skill]->rate1 = $sk->rank;
			$Tab[$sk->fk_skill]->how_many_max1 = $sk->how_many_max;
			$Tab[$sk->fk_skill]->label = $sk->label;
			$Tab[$sk->fk_skill]->description = $sk->description;
	}

	foreach ($TSkill2 as &$sk) {

			if (empty($Tab[$sk->fk_skill])) $Tab[$sk->fk_skill] = new stdClass;
			$Tab[$sk->fk_skill]->rate2 = $sk->rank;
			$Tab[$sk->fk_skill]->label = $sk->label;
			$Tab[$sk->fk_skill]->description = $sk->description;
			$Tab[$sk->fk_skill]->how_many_max2 = $sk->how_many_max;
	}

	return $Tab;

}

/**
 * @param $TUser
 * @param int $fk_usergroup
 * @param string $namelist
 * @return string
 */
function displayUsersListWithPicto(&$TUser, $fk_usergroup = 0, $namelist = 'list-user')
{
	global $db, $langs, $conf, $form;

	$out = '';
	if ($fk_usergroup > 0) {

		$list = $namelist . '_excluded_id';

		$excludedIdsList = GETPOST($list);


		$sql = "SELECT DISTINCT u.rowid FROM " . MAIN_DB_PREFIX . "user u
		LEFT JOIN " . MAIN_DB_PREFIX . "usergroup_user ugu ON (u.rowid = ugu.fk_user)
		WHERE 1
		AND u.statut > 0
		AND ugu.fk_usergroup=" . $fk_usergroup;

		$res = $db->query($sql);

		$out .= '<ul name="' . $namelist . '">';

		$TExcludedId = explode(',', $excludedIdsList);

		$form = new Form($db);
		$out .= hidden($list, $excludedIdsList);

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

			$evaluation = Evaluation::getLastEvaluationForUser($user->id);

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


/**
 *
 * 		Allow to get skill(s) of a user
 *
 * 		@param $TUser
 * 		@return array|int
 */
function getSkillForUsers($TUser)
{
	global $db;

	//Je remonte l'utilisateur qui a la note la plus haute dans un groupe donné pour toutes les compétences évaluées dans ce groupe
	if(empty($TUser)) return array();

	$sql = 'SELECT sk.rowid, sk.label, sk.description, sk.skill_type, sr.fk_object, sr.objecttype, sr.fk_skill, ';
	$sql.= " MAX(sr.rank) as rank";
	$sql.=' FROM '.MAIN_DB_PREFIX.'hrmtest_skill sk';
	$sql.='	LEFT JOIN '.MAIN_DB_PREFIX.'hrmtest_skillrank sr ON (sk.rowid = sr.fk_skill)';
	$sql.='	WHERE sr.objecttype = "'.SkillRank::SKILLRANK_TYPE_USER.'"';
	$sql.=' AND sr.fk_object IN ('.implode(',',$TUser).')';
	$sql.=" GROUP BY sk.rowid "; // group par competence

	$resql = $db->query($sql);
	$Tab = array();

	if ($resql){
		//Pour chaque compétence, on compte le nombre de fois que la note max a été atteinte au sein d'un groupe donné
		$num = 0;
		while($obj = $db->fetch_object($resql) ) {

			$sql1 = "SELECT count(*) as how_many_max FROM ".MAIN_DB_PREFIX."hrmtest_skillrank sr";
			$sql1.=" WHERE sr.rank = ".(int)$obj->rank;
			$sql1.=" AND sr.objecttype = '".Skillrank::SKILLRANK_TYPE_USER."'";
			$sql1.=" AND sr.fk_skill = ".$obj->fk_skill;
			$sql1.=" AND sr.fk_object IN (".implode(',',$TUser).")";
			$resql1 = $db->query($sql1);

			$objMax = $db->fetch_object($resql1);

			$Tab[$num] = new stdClass();
			$Tab[$num]->fk_skill = $obj->fk_skill;
			$Tab[$num]->label = $obj->label;
			$Tab[$num]->description = $obj->description;
			$Tab[$num]->skill_type = $obj->skill_type;
			$Tab[$num]->fk_object = $obj->fk_object;
			$Tab[$num]->objectType = SkillRank::SKILLRANK_TYPE_USER;
			$Tab[$num]->rank = $obj->rank;
			$Tab[$num]->how_many_max = $objMax->how_many_max;

			$num++;
		}
	}else{
		dol_print_error($db);
	}

return $Tab;

}

/**
 * 		Allow to get skill(s) of a job
 *
 * 		@param $fk_job
 * 		@return array|int
 */
function getSkillForJob($fk_job)
{
	global $db;

	if (empty($fk_job)) return array();

	$sql = 'SELECT sk.rowid, sk.label, sk.description, sk.skill_type, sr.fk_object, sr.objecttype, sr.fk_skill, ';
	$sql.= " MAX(sr.rank) as rank";
	$sql.=' FROM '.MAIN_DB_PREFIX.'hrmtest_skill sk';
	$sql.='	LEFT JOIN '.MAIN_DB_PREFIX.'hrmtest_skillrank sr ON (sk.rowid = sr.fk_skill)';
	$sql.='	WHERE sr.objecttype = "'.SkillRank::SKILLRANK_TYPE_JOB.'"';
	$sql.=' AND sr.fk_object IN ('.$fk_job.')';
	$sql.=' GROUP BY sk.rowid '; // group par competence*/

	$resql = $db->query($sql);
	$Tab = array();


	if ($resql){
		$num = 0;
		while($obj = $db->fetch_object($resql) ) {

			$Tab[$num] = new stdClass();
			$Tab[$num]->fk_skill = $obj->fk_skill;
			$Tab[$num]->label = $obj->label;
			$Tab[$num]->description = $obj->description;
			$Tab[$num]->skill_type = $obj->skill_type;
			//$Tab[$num]->date_start = '';
			//$Tab[$num]->date_end = '';
			$Tab[$num]->fk_object = $obj->fk_object;
			$Tab[$num]->objectType = SkillRank::SKILLRANK_TYPE_JOB;
			$Tab[$num]->rank = $obj->rank;
			$Tab[$num]->how_many_max = $obj->how_many_max;

			$num++;
		}
	}else{
		dol_print_error($db);
	}


	return $Tab;
}

/**
 * duplicated with modified data from $form Class
 *
 * @param string $selected
 * @param string $htmlname
 * @param int $show_empty
 * @param int $disabled
 * @param string $enableonly
 * @param string $force_entity
 * @param false $multiple
 * @param string $morecss
 * @return string
 */
function select_jobs($selected = '', $htmlname = 'groupid', $show_empty = 0,  $disabled = 0, $enableonly = '', $force_entity = '0', $multiple = false, $morecss = '')
{
	// phpcs:enable
	global $conf, $user, $langs,$db;

	if (!is_array($selected)) {
		$selected = array($selected);
	}
	$out = '';

	// On recherche les groupes
	$sql = "SELECT j.rowid, j.ref as name";
	$sql .= " FROM ".MAIN_DB_PREFIX."hrmtest_job as j ";
	$sql .= " ORDER BY j.ref ASC";


	$resql = $db->query($sql);
	if ($resql) {
		// Enhance with select2
		include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
		$out .= ajax_combobox($htmlname);
		$out .= '<select class="flat minwidth200'.($morecss ? ' '.$morecss : '').'" id="'.$htmlname.'" name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').' '.($disabled ? ' disabled' : '').'>';
		$num = $db->num_rows($resql);
		$i = 0;
		if ($num) {
			if ($show_empty && !$multiple) {
				$out .= '<option value="-1"'.(in_array(-1, $selected) ? ' selected' : '').'>&nbsp;</option>'."\n";
			}

			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				$disableline = 0;
				if (is_array($enableonly) && count($enableonly) && !in_array($obj->rowid, $enableonly)) {
					$disableline = 1;
				}

				$out .= '<option value="'.$obj->rowid.'"';
				if ($disableline) {
					$out .= ' disabled';
				}
				if ((is_object($selected[0]) && $selected[0]->id == $obj->rowid) || (!is_object($selected[0]) && in_array($obj->rowid, $selected))) {
					$out .= ' selected';
				}
				$out .= '>';

				$out .= $obj->name;
				if (!empty($conf->multicompany->enabled) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1) {
					$out .= " (".$obj->label.")";
				}

				$out .= '</option>';
				$i++;
			}
		} else {
			if ($show_empty) {
				$out .= '<option value="-1"'.(in_array(-1, $selected) ? ' selected' : '').'></option>'."\n";
			}
			$out .= '<option value="" disabled>'.$langs->trans("NoUserGroupDefined").'</option>';
		}
		$out .= '</select>';
	} else {
		dol_print_error($db);
	}

	return $out;
}
