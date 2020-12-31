<?
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　エクスポート
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "change") {
		include("../../_www/problem_lib/problem_regist.php");
		$ERROR = change();
	} elseif (ACTION == "delete") {
		$ERROR = delete();
	}

	$html .= select_course();

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {

	global $L_UNIT_TYPE;			// 2012/09/10 add oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$A_TABLE = T_COURSE;
	$B_TABLE = T_STAGE;
	$C_TABLE = T_LESSON;
	$D_TABLE = T_UNIT;

	// add start oda 2014/10/08
	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			if (substr($value,0,25) == "practice__export__course_") {
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,25);						// コース番号を取得する
			}
		}
	}
	// add end oda 2014/10/08

	$flag = 0;

	// update start oda 2014/10/08 権限制御修正
	//$sql  = "SELECT * FROM ".T_COURSE.
	//		" WHERE state!='1' ORDER BY list_num;";
	$sql  = "SELECT * FROM ".T_COURSE;
	$sql .= " WHERE state!='1'";
	if ($course_list) { $sql .= " AND course_num NOT IN (".$course_list.") "; }
	$sql .= " ORDER BY list_num;";
	// update end oda 2014/10/08

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		if ($max) {

			if (!$_POST[course_num]) { $selected = "selected"; } else { $selected = ""; }
			$course_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_POST[course_num] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
				$course_num_html .= "<option value=\"{$list['course_num']}\" $selected>{$list['course_name']}</option>\n";
				if ($selected) { $L_NAME[course_name] = $list['course_name']; }
			}

			if (!$_POST[stage_num]) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" $selected>----------</option>\n";

			if ($_POST[course_num]) {
				$sql  = "SELECT * FROM $B_TABLE" .
						" WHERE state!='1' AND course_num='$_POST[course_num]' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max2 = $cdb->num_rows($result);
					if ($max2) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST[stage_num] == $list[stage_num]) { $selected = "selected"; } else { $selected = ""; }
							$stage_num_html .= "<option value=\"{$list[stage_num]}\" $selected>{$list[stage_name]}</option>\n";
							if ($selected) { $L_NAME[stage_name] = $list[stage_name]; }
						}
					}
				}
			}

			if (!$_POST[lesson_num]) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" $selected>----------</option>\n";

			if ($_POST[stage_num]) {
				$sql  = "SELECT * FROM $C_TABLE" .
						" WHERE state!='1' AND stage_num='$_POST[stage_num]' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max3 = $cdb->num_rows($result);
					if ($max3) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST[lesson_num] == $list[lesson_num]) { $selected = "selected"; } else { $selected = ""; }
							$lesson_num_html .= "<option value=\"{$list[lesson_num]}\" $selected>{$list[lesson_name]}</option>\n";
							if ($selected) { $L_NAME[lesson_name] = $list[lesson_name]; }
						}
					}
				}
			}

			if (!$_POST[unit_num]) { $selected = "selected"; } else { $selected = ""; }
			$unit_html .= "<option value=\"\" $selected>----------</option>\n";

			if ($_POST[lesson_num]) {
				$sql  = "SELECT * FROM $D_TABLE" .
						" WHERE state!='1' AND lesson_num='$_POST[lesson_num]' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max4 = $cdb->num_rows($result);
					if ($max4) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST[unit_num] == $list[unit_num]) { $selected = "selected"; } else { $selected = ""; }
							$unit_html .= "<option value=\"{$list[unit_num]}\" $selected>{$list[unit_name]}</option>\n";
							if ($selected) { $L_NAME[unit_name] = $list[unit_name]; }
						}
					}
				}
			}

			if (!$_POST[block_num]) { $selected = "selected"; } else { $selected = ""; }
			$block_html .= "<option value=\"\" $selected>----------</option>\n";

			if ($_POST[unit_num]) {
				$sql  = "SELECT * FROM " . T_BLOCK .
						" WHERE state!='1' AND unit_num='$_POST[unit_num]' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max5 = $cdb->num_rows($result);
					if ($max5) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST[block_num] == $list[block_num]) { $selected = "selected"; } else { $selected = ""; }
// 2012/09/10 del start oda
//							if ($list[block_type] == 1) {
//								$block_name = "ドリル";
//							} elseif ($list[block_type] == 2) {
//								$block_name = "診断A";
//							} elseif ($list[block_type] == 3) {
//								$block_name = "診断B";
//							}
//							if ($list[display] == "2") { $block_name .= "(非表示)"; }
// 2012/09/10 del end oda
							$block_name = $L_UNIT_TYPE[$list['block_type']];											// 2012/09/10 add oda
							$block_html .= "<option value=\"{$list[block_num]}\" $selected>{$block_name}</option>\n";
							if ($selected) { $L_NAME[block_name] = $block_name; }
						}
					}
				}
			}

			$html .= "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"unit_form\">\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$html .= "<td class=\"stage_form_menu\">コース</td>\n";
			$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
			$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
			$html .= "<td class=\"stage_form_menu\">ユニット</td>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "<td class=\"stage_form_menu\">ドリル</td>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"stage_form_cell\">\n";
			$html .= "<td>\n";
			$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
			$html .= $course_num_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"stage_num\" onchange=\"submit_stage()\">\n";
			$html .= $stage_num_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td><select name=\"lesson_num\" onchange=\"submit_lesson()\">\n";
			$html .= $lesson_num_html;
			$html .= "</select></td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"unit_num\" onchange=\"submit_unit()\">\n";
			$html .= $unit_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"block_num\" onchange=\"submit()\">\n";
			$html .= $block_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form><br>\n";
		}
		else {
			$flag = 1;
			$html = "<br>\n";
			$html .= "コースを設定してからご利用下さい。<br>\n";
		}
	}

	if ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num'] && $_POST['unit_num'] && $_POST['block_num']) {
		$html .= download($L_NAME);
	} elseif ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num'] && $_POST['unit_num']) {
		if (!$max5) {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "ドリルが設定されておりません。<br>\n";
		}
		else {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "ドリルを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num']) {
		if (!$max4) {
			$html .= "ユニットが設定されておりません。<br>\n";
		}
		else {
			$html .= "ユニットを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num'] && $_POST['stage_num']) {
		if (!$max3) {
			$html .= "Lessonが設定されておりません。<br>\n";
		}
		else {
			$html .= "Lessonを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num']) {
		if (!$max2) {
			$html .= "ステージが設定されておりません。<br>\n";
		}
		else {
			$html .= "ステージを選択してください。<br>\n";
		}
	} elseif ($flag != 1) {
		$html .= "コース、ステージ、Lesson、ユニットを選択してください。<br>\n";
	}

	$msg = $MSG['msg'];
	$ERROR = $MSG['error'];
	if ($msg) {
		$html .= "<br>\n";
		$html .= "$msg<br>\n";
	}
	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	return $html;
}

/**
 * downloadする為のメッセージ・テンプレート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function download() {

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	プルダウンを作成
	//	下記に$expListを追加
	$expList = "";
	if ( is_array($L_EXP_CHA_CODE) ) {
		$expList = "海外版の場合は、出力形式について[Unicode]選択して、ダウンロードボタンをクリックしてください。<br />\n";
		$expList .= "<b>出力形式：</b>";
		$expList .= "<select name=\"exp_list\">\n";
		foreach( $L_EXP_CHA_CODE as $key => $val ){
			$expList .= "<option value=\"".$key."\">".$val."</option>\n";
		}
		$expList .= "</select>\n";

	}
	//-------------------------------------------------


	$A_TABLE = T_PROBLEM;
	$sql = "SELECT * FROM $A_TABLE";
	$sql .= " WHERE course_num='$_POST[course_num]' AND block_num='$_POST[block_num]'";
	$sql .= " ORDER BY display_problem_num";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if  ($max) {
		$html .=<<<EOT
<form action="./make_csv.php" method="POST">
<input type="hidden" name="course_num" value="$_POST[course_num]">
<input type="hidden" name="stage_num" value="$_POST[stage_num]">
<input type="hidden" name="lesson_num" value="$_POST[lesson_num]">
<input type="hidden" name="unit_num" value="$_POST[unit_num]">
<input type="hidden" name="block_num" value="$_POST[block_num]">
$expList
<input type="submit" value="サーバ上の問題情報CSVをDownloadする。">
</form>

EOT;
	} else {
		$html .= "指定したユニットには、問題が登録されていません。";
	}

	return $html;
}

?>