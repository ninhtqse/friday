<?
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　ユニットキー設定
 *
 * @author Azet
 */

/*
	//	add ookawara 2012/03/26
	course→pk_course
	course_num→pk_course_num
	couse_num_html→pk_course_num_html
	m_course_num→m_pk_course_num

	stage→pk_stage
	stage_num→pk_stage_num
	stage_name→pk_stage_name
	stage_num_html→pk_stage_num_html
	m_stage_num→m_pk_stage_num

	lesson→pk_lesson
	lesson_num→pk_lesson_num
	lesson_name→pk_lesson_name
	lesson_num_html→pk_lesson_num_html
	m_lesson_num→m_pk_lesson_num

	unit→pk_unit
	unit_num→pk_unit_num
	unit_name→pk_unit_name
	unit_num_html→pk_unit_num_html
	m_unit_num→m_pk_unit_num

	block→pk_block
	block_num→pk_block_num
	block_name→pk_block_name
	block_num_html→pk_block_num_html
	m_block_num→m_pk_block_num

	package_list→pk_list
	package_list_num→pk_list_num
	list_name→pk_list_name
	list_num_html→pk_list_num_html
	m_list_num→m_pk_list_num

	package_unit_list_num→pk_unit_list_num
	m_package_unit_list_num→m_pk_unit_list_num
	c_package_unit_list_num→c_pk_unit_list_num

	write_type→course_num
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

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	list($html,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST) = select_course();
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= unit_list($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} else {
			$html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} else {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST);
		}
	} else {
		if ($_POST[pk_course_num]) {
			$html .= unit_list($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST);
		}
	}

	return $html;
}


/**
 * コース選択一覧を作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE mk_flg!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return array($html,$L_COURSE);
	} else {
		if (!$_POST['pk_course_num']) { $selected = "selected"; } else { $selected = ""; }
		$pk_course_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$pk_course_num_ = $list['pk_course_num'];
			$pk_course_name_ = $list['pk_course_name'];
			$L_COURSE[$pk_course_num_] = $pk_course_name_;
			if ($_POST['pk_course_num'] == $pk_course_num_) { $selected = "selected"; } else { $selected = ""; }
			$pk_course_num_html .= "<option value=\"{$pk_course_num_}\" $selected>{$pk_course_name_}(".$list['pk_course_num'].")</option>\n";
		}
	}

	if ($_POST[pk_course_num]) {
		$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
				" WHERE pk_course_num='$_POST[pk_course_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$pk_stage_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['pk_course_num']) { $selected = "selected"; } else { $selected = ""; }
			$pk_stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STAGE[$list[pk_stage_num]] = $list[pk_stage_name];
				if ($_POST[pk_stage_num] == $list[pk_stage_num]) { $selected = "selected"; } else { $selected = ""; }
				$pk_stage_num_html .= "<option value=\"{$list[pk_stage_num]}\" $selected>{$list[pk_stage_name]}(".$list['pk_stage_num'].")</option>\n";
			}
		}
	} else {
		$pk_stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST[pk_stage_num]) {
		$sql  = "SELECT * FROM ".T_PACKAGE_LESSON.
				" WHERE pk_stage_num='$_POST[pk_stage_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$pk_lesson_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['pk_lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$pk_lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_LESSON[$list[pk_lesson_num]] = $list[pk_lesson_name];
				if ($_POST[pk_lesson_num] == $list[pk_lesson_num]) { $selected = "selected"; } else { $selected = ""; }
				$pk_lesson_num_html .= "<option value=\"{$list[pk_lesson_num]}\" $selected>{$list[pk_lesson_name]}(".$list['pk_lesson_num'].")</option>\n";
			}
		}
	} else {
		$pk_lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST[pk_lesson_num]) {
		$sql  = "SELECT * FROM ".T_PACKAGE_UNIT.
				" WHERE pk_lesson_num='$_POST[pk_lesson_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$pk_unit_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['pk_unit_num']) { $selected = "selected"; } else { $selected = ""; }
			$pk_unit_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_UNIT[$list[pk_unit_num]] = $list[pk_unit_name];
				if ($_POST[pk_unit_num] == $list[pk_unit_num]) { $selected = "selected"; } else { $selected = ""; }
				$pk_unit_num_html .= "<option value=\"{$list[pk_unit_num]}\" $selected>{$list[pk_unit_name]}(".$list['pk_unit_num'].")</option>\n";
			}
		}
	} else {
		$pk_unit_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST[pk_unit_num]) {
		$sql  = "SELECT * FROM ".T_PACKAGE_BLOCK.
				" WHERE pk_unit_num='$_POST[pk_unit_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
//			$pk_block_num_html .= "<option value=\"\">ブロック内にリストがありません。</option>\n";
			$pk_block_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['pk_unit_num']) { $selected = "selected"; } else { $selected = ""; }
			$pk_block_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_BLOCK[$list[pk_block_num]] = $list[pk_block_name];
				if ($_POST[pk_block_num] == $list[pk_block_num]) { $selected = "selected"; } else { $selected = ""; }
				$pk_block_num_html .= "<option value=\"{$list[pk_block_num]}\" $selected>{$list[pk_block_name]}(".$list['pk_block_num'].")</option>\n";
			}
		}
	} else {
		$pk_block_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST[pk_block_num]) {
		$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
				" WHERE pk_block_num='$_POST[pk_block_num]' AND mk_flg!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
//			$pk_block_num_html .= "<option value=\"\">ブロック内にリストがありません。</option>\n";
			$pk_list_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['pk_unit_num']) { $selected = "selected"; } else { $selected = ""; }
			$pk_list_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_PACKAGE_LIST[$list[pk_list_num]] = $list[pk_list_name];
				if ($_POST[pk_list_num] == $list[pk_list_num]) { $selected = "selected"; } else { $selected = ""; }
				$pk_list_num_html .= "<option value=\"{$list[pk_list_num]}\" $selected>{$list[pk_list_name]}(".$list['pk_list_num'].")</option>\n";
			}
		}
	} else {
		$pk_list_num_html .= "<option value=\"\">--------</option>\n";
	}

			$html = "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"unit_form\">\n";
			$html .= "<tr class=\"unit_form_menu\">\n";
			$html .= "<td>コース</td>\n";
			$html .= "<td>ステージ</td>\n";
			$html .= "<td>レッスン</td>\n";
			$html .= "<td>ユニット</td>\n";
			$html .= "<td>ブロック</td>\n";
			$html .= "<td>リスト</td>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$html .= "<td><select name=\"pk_course_num\" onchange=\"submit_pk_course();\">\n";
			$html .= $pk_course_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"pk_stage_num\" onchange=\"submit_pk_stage();\">\n";
			$html .= $pk_stage_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"pk_lesson_num\" onchange=\"submit_pk_lesson();\">\n";
			$html .= $pk_lesson_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"pk_unit_num\" onchange=\"submit_pk_unit();\">\n";
			$html .= $pk_unit_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"pk_block_num\" onchange=\"submit_pk_block();\">\n";
			$html .= $pk_block_num_html;
			$html .= "</select></td>\n";
			$html .= "<td><select name=\"pk_list_num\" onchange=\"submit();\">\n";
			$html .= $pk_list_num_html;
			$html .= "</select></td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form>\n";

	if (!$_POST[pk_course_num]) {
		$html .= "<br>\n";
		$html .= "ユニットキーリストを設定するコースを選択してください。<br>\n";
	}

	return array($html,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST);
}

/**
 * ユニット一覧を作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @param array $L_UNIT
 * @param array $L_BLOCK
 * @param mixed $L_PACKAGE_LIST
 * @return string HTML
 */
function unit_list($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
/*
		if (!$_POST['pk_block_num']) {
			if ($_POST['pk_unit_num'] && count($L_BLOCK) > 0) {
				$html .= "<br>\n";
				$html .= "ブロックを指定してください。<br>\n";
				return $html;
			} elseif ($_POST['pk_lesson_num'] && count($L_UNIT) > 0 && !$_POST['pk_unit_num']) {
				$html .= "<br>\n";
				$html .= "ユニットを指定してください。<br>\n";
				return $html;
			} elseif ($_POST['pk_stage_num'] && count($L_LESSON) > 0 && !$_POST['pk_lesson_num']) {
				$html .= "<br>\n";
				$html .= "レッスンを指定してください。<br>\n";
				return $html;
			}
		}
*/
		$html .= "<br>\n";
		$html .= "インポートする場合は、ファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";
		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "<br /><br />\n";
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";
			$html .= $expList;
		}
		//-------------------------------------------------
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
		$html .= "</form>\n";

		if ($L_BLOCK) {
			$sql  = "SELECT category_type FROM ".T_PACKAGE_BLOCK.
					" WHERE mk_flg!='1' AND pk_block_num='".$_POST['pk_block_num']."'";
		} elseif ($L_UNIT) {
			$sql  = "SELECT category_type FROM ".T_PACKAGE_UNIT.
					" WHERE mk_flg!='1' AND pk_unit_num='".$_POST['pk_unit_num']."'";
		} elseif ($L_LESSON) {
			$sql  = "SELECT category_type FROM ".T_PACKAGE_LESSON.
					" WHERE mk_flg!='1' AND pk_lesson_num='".$_POST['pk_lesson_num']."'";
		} elseif ($L_STAGE) {
			$sql  = "SELECT category_type FROM ".T_PACKAGE_STAGE.
					" WHERE mk_flg!='1' AND pk_stage_num='".$_POST['pk_stage_num']."'";
		} elseif ($L_COURSE) {
			$sql  = "SELECT category_type FROM ".T_PACKAGE_COURSE.
					" WHERE mk_flg!='1' AND pk_course_num='".$_POST['pk_course_num']."'";
		}
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['category_type'] != 3&&!$_POST['pk_list_num']) {
			$html .= "<br>\n";
			$html .= "このカテゴリにはユニットキーを設定できません。<br>\n";
			return $html;
		} else {
			$html .= "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";
			$html .= "<input type=\"submit\" value=\"ユニットキーリスト新規登録\">\n";
			$html .= "</form>\n";
		}
	}

	if (!$_POST['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
	elseif (!$_POST['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
	elseif (!$_POST['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
	elseif (!$_POST['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
 	elseif (!$_POST['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
 	else { $category_key = 6; $parent = "pk_list"; }
	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE mk_flg!='1'".
			" AND ".$parent."_num='".$_POST[$parent.'_num']."'".
			" ORDER BY list_num;";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br>\n";
		$html .= "今現在登録されているユニットキーリストは有りません。<br>\n";
		return $html;
	}
	$html .= "<br>\n";
	$html .= "<table class=\"stage_form\">\n";
	$html .= "<tr class=\"stage_form_menu\">\n";
	if (!ereg("practice__view",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>↑</th>\n";
		$html .= "<th>↓</th>\n";
	}
	$html .= "<th>登録番号</th>\n";
	$html .= "<th>コース名</th>\n";
	if ($_POST['pk_stage_num']) { $html .= "<th>ステージ名</th>\n"; }
	if ($_POST['pk_lesson_num']) { $html .= "<th>レッスン名</th>\n"; }
	if ($_POST['pk_unit_num']) { $html .= "<th>ユニット名</th>\n"; }
	if ($_POST['pk_block_num']) { $html .= "<th>ブロック名</th>\n"; }
	$html .= "<th>教科</th>\n";
	$html .= "<th>ユニットキー</th>\n";
	$html .= "<th>表示・非表示</th>\n";
	if (!ereg("practice__view",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>詳細</th>\n";
	}
	if (!ereg("practice__del",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>削除</th>\n";
	}
	$html .= "</tr>\n";

	$i = 1;
	while ($list = $cdb->fetch_assoc($result)) {

		$pk_stage_num = $list['pk_stage_num'];
		$list_num = $list['list_num'];
		$LINE[$list_num] = $pk_stage_num;
		foreach ($list AS $KEY => $VAL) {
			$$KEY = $VAL;
		}

		$up_submit = $down_submit = "&nbsp;";
		if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
		if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

		$html .= "<tr class=\"stage_form_cell\">\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"{$_POST[pk_course_num]}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"{$pk_stage_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"{$pk_lesson_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"{$pk_unit_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"{$pk_block_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"{$pk_list_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"pk_unit_list_num\" value=\"{$pk_unit_list_num}\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td>{$up_submit}</td>\n";
			$html .= "<td>{$down_submit}</td>\n";
		}
		$html .= "<td>{$pk_unit_list_num}</td>\n";
		$html .= "<td>{$L_COURSE[$_POST[pk_course_num]]}</td>\n";
		if ($_POST['pk_stage_num']) { $html .= "<td>{$L_STAGE[$pk_stage_num]}</td>\n"; }
		if ($_POST['pk_lesson_num']) { $html .= "<td>{$L_LESSON[$pk_lesson_num]}</td>\n"; }
		if ($_POST['pk_unit_num']) { $html .= "<td>{$L_UNIT[$pk_unit_num]}</td>\n"; }
		if ($_POST['pk_block_num']) { $html .= "<td>{$L_BLOCK[$pk_block_num]}</td>\n"; }
		$html .= "<td>{$L_WRITE_TYPE[$course_num]}</td>\n";
		$html .= "<td>{$unit_key}</td>\n";
		$html .= "<td>{$L_DISPLAY[$display]}</td>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
		}
		$html .= "</form>\n";
		$html .= "</tr>\n";
		++$i;
	}
	$html .= "</table>\n";
	return $html;
}


/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @param array $L_UNIT
 * @param array $L_BLOCK
 * @param mixed $L_PACKAGE_LIST
 * @return string HTML
 */
function addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='$_POST[pk_course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$pk_course_name_ = $list['pk_course_name'];
	}

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_UNIT_LIST_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('result'=>'plane','value'=>$L_PACKAGE_LIST[$_POST[pk_list_num]]);
	$INPUTS[WRITETYPE] = array('type'=>'select','name'=>'course_num','array'=>$L_WRITE_TYPE,'check'=>$_POST['course_num']);
	$INPUTS[UNITKEY] = array('type'=>'text','name'=>'unit_key','value'=>$_POST['unit_key']);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$display = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $display . "<label for=\"display\">{$L_DISPLAY[1]}</label> " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 表示フォームを作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @param array $L_UNIT
 * @param array $L_BLOCK
 * @param mixed $L_PACKAGE_LIST
 * @return string HTML
 */
function viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' AND mk_flg!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。<br>$sql";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_list_num\" value=\"$pk_unit_list_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_UNIT_LIST_FORM);

	if (!$pk_unit_list_num) { $pk_unit_list_num = "---"; }
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>$pk_unit_list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('result'=>'plane','value'=>$L_PACKAGE_LIST[$_POST[pk_list_num]]);	//	add ookawara 2012/03/27
	$INPUTS[WRITETYPE] = array('type'=>'select','name'=>'course_num','array'=>$L_WRITE_TYPE,'check'=>$course_num);
	$INPUTS[UNITKEY] = array('type'=>'text','name'=>'unit_key','value'=>$unit_key);
	$remarks = ereg_replace("&lt;","<",$remarks);
	$remarks = ereg_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$female = $newform->make();
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";	//add hasegawa 201511/17 リストの値を保持
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$mode = MODE;

	if (!$_POST['pk_course_num']) { $ERROR[] = "登録するユニットキーリストのコース情報が確認できません。"; }
//	if (!$_POST['pk_stage_num']) { $ERROR[] = "登録するユニットキーリストのステージ情報が確認できません。"; }
//	if (!$_POST['pk_lesson_num']) { $ERROR[] = "登録するブロックのステージ情報が確認できません。"; }
//	if (!$_POST['pk_unit_num']) { $ERROR[] = "登録するブロックのユニット情報が確認できません。"; }
//	if (!$_POST['pk_block_num']) { $ERROR[] = "登録するブロックのユニット情報が確認できません。"; }
	if (!$_POST['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
	elseif (!$_POST['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
	elseif (!$_POST['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
	elseif (!$_POST['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
	elseif (!$_POST['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
 	else { $category_key = 6; $parent = "pk_list"; }
	if (!$_POST['unit_key']) { $ERROR[] = "ユニットキーが未入力です。"; }
	elseif (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state!='1'".
				" AND display='1'".
				" AND course_num='".$_POST['course_num']."'".
				" AND unit_key='".$_POST['unit_key']."'";

		if ($result = $cdb->query($sql)) {
			$unit_count = $cdb->num_rows($result);
		}
		if (!$unit_count) { $ERROR[] = "入力されたユニットキーは存在しません。"; }
		if (!$ERROR) {
			if ($mode == "add") {
				$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
						" WHERE mk_flg!='1'".
						" AND ".$parent."_num='".$_POST[$parent.'_num']."'".
						" AND course_num='$_POST[course_num]'".
						" AND unit_key='$_POST[unit_key]'";
			} else {
				$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
						" WHERE mk_flg!='1'".
						" AND ".$parent."_num='".$_POST[$parent.'_num']."'".
						" AND pk_unit_list_num!='$_POST[pk_unit_list_num]'" .
						" AND course_num='$_POST[course_num]'".
						" AND unit_key='$_POST[unit_key]'";
			}
			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if ($count > 0) { $ERROR[] = "入力されたユニットキーは既に登録されております。"; }
		}
	}
	if (!$_POST['course_num']) { $ERROR[] = "教科が未選択です。"; }
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @param array $L_UNIT
 * @param array $L_BLOCK
 * @param mixed $L_PACKAGE_LIST
 * @return string HTML
 */
function check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK,$L_PACKAGE_LIST) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' AND mk_flg!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_UNIT_LIST_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$pk_unit_list_num) { $pk_unit_list_num = "---"; }
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>$pk_unit_list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('result'=>'plane','value'=>$L_PACKAGE_LIST[$_POST[pk_list_num]]);	//	add ookawara 2012/03/27
	$INPUTS[WRITETYPE] = array('result'=>'plane','value'=>$L_WRITE_TYPE[$course_num]);
	$INPUTS[UNITKEY] = array('result'=>'plane','value'=>$unit_key);
	$remarks = ereg_replace("&lt;","<",$remarks);
	$remarks = ereg_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>$remarks);
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$pk_course_num\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	}
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$pk_course_num\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$pk_stage_num\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$pk_lesson_num\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$pk_unit_num\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$pk_block_num\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$_POST[pk_list_num]\">\n";	//add hasegawa 201511/17 リストの値を保持
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	if (!$_POST['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
	elseif (!$_POST['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
	elseif (!$_POST['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
	elseif (!$_POST['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
 	elseif (!$_POST['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
 	else { $category_key = 6; $parent = "pk_list"; }
	$INSERT_DATA[category_key] = $category_key;
	$INSERT_DATA[ins_date] = "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] = "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

	$ERROR = $cdb->insert(T_PACKAGE_UNIT_LIST,$INSERT_DATA);

	if (!$ERROR) {
		$pk_unit_list_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $pk_unit_list_num;
		$where = " WHERE pk_unit_list_num='$pk_unit_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

// --- UPD20081009_2
/**
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * SQLインジェクション対策
 * @author Azet
 * @param mixed $val
 * @return mixed
 */
function sqlGuard($val)
{

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

    // Stripslashes ( magic_quotes_gpc=on な環境用 )
    if (get_magic_quotes_gpc()) {
        $val = stripslashes($val);
    }
    // 数値以外をクオートする
    if (!is_numeric($val)) {
        $val = $cdb->real_escape($val);
    }
    return $val;
}
// --- UPD20081009_2 ここまで

/**
 * DB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "詳細") {

		$INSERT_DATA[course_num] = $_POST[course_num];
		$INSERT_DATA[unit_key] = $_POST[unit_key];
//		$INSERT_DATA[remarks] = $_POST[remarks];
		$INSERT_DATA[display] = $_POST[display];
		$INSERT_DATA[upd_syr_id] = "updateline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[mk_flg] = 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] = "now()";	//	add ookawara 2012/03/27
		$where = " WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * PACKAGE_UNIT_LISTを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_stage_num = $list['pk_stage_num'];	//	add ookawara 2012/03/27
		$m_pk_lesson_num = $list['pk_lesson_num'];
		$m_pk_unit_num = $list['pk_unit_num'];
		$m_pk_block_num = $list['pk_block_num'];
		$m_pk_list_num = $list['pk_list_num'];
		$m_pk_unit_list_num = $list['pk_unit_list_num'];	//	add m_ ookawara 2012/03/27
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_unit_list_num || !$m_list_num) { $ERROR[] = "移動するユニットキーリスト情報が取得できません。"; }

	if (!$ERROR) {
		if (!$_POST['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
		elseif (!$_POST['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
		elseif (!$_POST['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
		elseif (!$_POST['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
	 	elseif (!$_POST['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
	 	else { $category_key = 6; $parent = "pk_list"; }
		$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
				" WHERE mk_flg!='1'".
				" AND ".$parent."_num='".$_POST[$parent.'_num']."'".
				" AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
//		$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
//				" WHERE mk_flg!='1' AND pk_block_num='$m_pk_block_num' AND list_num<'$m_list_num'" .
//				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_unit_list_num = $list['pk_unit_list_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_unit_list_num || !$c_list_num) { $ERROR[] = "移動されるユニットキーリスト情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_unit_list_num='$m_pk_unit_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_unit_list_num='$c_pk_unit_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * PACKAGE_UNIT_LISTを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_unit_list_num='$_POST[pk_unit_list_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_stage_num = $list['pk_stage_num'];	//	add ookawara 2012/03/27
		$m_pk_lesson_num = $list['pk_lesson_num'];
		$m_pk_unit_num = $list['pk_unit_num'];
		$m_pk_block_num = $list['pk_block_num'];
		$m_pk_list_num = $list['pk_list_num'];
		$m_pk_unit_list_num = $list['pk_unit_list_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_unit_list_num || !$m_list_num) { $ERROR[] = "移動するユニットキーリスト情報が取得できません。"; }

	if (!$ERROR) {
		if (!$_POST['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
		elseif (!$_POST['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
		elseif (!$_POST['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
		elseif (!$_POST['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
	 	elseif (!$_POST['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
	 	else { $category_key = 6; $parent = "pk_list"; }
		$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
				" WHERE mk_flg!='1'".
				" AND ".$parent."_num='".$_POST[$parent.'_num']."'".
				" AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
//		$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
//				" WHERE mk_flg!='1' AND pk_block_num='$m_pk_block_num' AND list_num>'$m_list_num'" .
//				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_unit_list_num = $list['pk_unit_list_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_unit_list_num || !$c_list_num) { $ERROR[] = "移動されるユニットキーリスト情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_unit_list_num='$m_pk_unit_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_unit_list_num='$c_pk_unit_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['unit_list'],1,1);
	if ($ERROR) { return $ERROR; }

	$filename = "unit_list.txt";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}
	echo $csv_line;

	exit;
}

/**
 * CSV情報を準備する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_CSV_COLUMN コラム名
 * @param string $head_mode='1' boolのように
 * @param string $csv_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1',$csv_mode='1') {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
//		$csv_line .= "\"".$head_name."\"\t";
		$csv_line .= "".$head_name."\t";
	}
	$csv_line .= "\n";

	$where = "";
	if ($_POST['pk_course_num']) { $where .= " AND pk_course_num='".$_POST['pk_course_num']."'"; }
	if ($_POST['pk_stage_num']) { $where .= " AND pk_stage_num='".$_POST['pk_stage_num']."'"; }
	if ($_POST['pk_lesson_num']) { $where .= " AND pk_lesson_num='".$_POST['pk_lesson_num']."'"; }
	if ($_POST['pk_unit_num']) { $where .= " AND pk_unit_num='".$_POST['pk_unit_num']."'"; }
	if ($_POST['pk_block_num']) { $where .= " AND pk_block_num='".$_POST['pk_block_num']."'"; }
 	if ($_POST['pk_list_num']) { $where .= " AND pk_list_num='".$_POST['pk_list_num']."'"; }
	$L_EXPORT_LIST = array();
//	if ($csv_mode == 1) { $where = ""; }
	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
		" WHERE mk_flg='0'".$where." ORDER BY list_num";
	if ($result = $cdb->query($sql)) {
		$i = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			$i++;
			foreach ($L_CSV_COLUMN as $key => $val) {
				$L_EXPORT_LIST[$i][$key] = $list[$key];
			}
		}
		$cdb->free_result($result);
	}
	foreach ($L_EXPORT_LIST as $array_key => $array_val) {
		foreach ($array_val as $key => $val) {
			$csv_line .= "".$val."\t";
		}
		$csv_line .= "\n";
	}
//	$csv_line .= $sql;

	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//$csv_line = replace_decode_sjis($csv_line);
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	//-------------------------------------------------

	return array($csv_line,$ERROR);
}


/**
 * csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	$cdb = $GLOBALS['cdb'];
	$ERROR = array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "ユニットキーCSVファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$|(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ユニットキーCSVファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ユニットキーCSVファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
//		return $ERROR;																		// del oda 2014/08/12 課題要望一覧No336
		return array($html,$ERROR);															// add oda 2014/08/12 課題要望一覧No336 メッセージが表示されないので、リターン値を修正
	}

	$ERROR = array();
	$L_IMPORT_LINE = file($file_tmp_name);
	$L_LIST_NAME = explode("\t",trim($L_IMPORT_LINE[0]));

	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);

//		$import_line = trim($L_IMPORT_LINE[$i]);

		if (count($L_IMPORT_LINE) == 0) {
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode("\t",$L_IMPORT_LINE[$i]);

		if (!is_array($L_VALUE)) {
			$ERROR[] = $i."行目のcsv入力値が不正なのでスキップしました。<br>";
			continue;
		}
		foreach ($L_VALUE as $key => $val) {
//			if ($val === "") { continue; }
			if ($L_LIST_NAME[$key] == "") { continue; }
			$val = preg_replace("/^\"|\"$/","",$val);
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);
			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$val = replace_encode_sjis($val);
			//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );
			if ( $code != 'UTF-8' ) {
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//--------------------------------------------------
			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}

		//レコードがあれば更新なければ新規
		$sql = "SELECT * FROM ". T_PACKAGE_UNIT_LIST.
			 " WHERE pk_unit_list_num='".$CHECK_DATA['pk_unit_list_num']."';";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['pk_unit_list_num']) {
			if ($list['pk_course_num'] == $CHECK_DATA['pk_course_num']
				&& $list['pk_stage_num'] == $CHECK_DATA['pk_stage_num']
				&& $list['pk_lesson_num'] == $CHECK_DATA['pk_lesson_num']
				&& $list['pk_unit_num'] == $CHECK_DATA['pk_unit_num']
				&& $list['pk_block_num'] == $CHECK_DATA['pk_block_num']
				&& $list['pk_list_num'] == $CHECK_DATA['pk_list_num']
			) {
				$ins_mode = "upd";
			} else {
				$ERROR[] = $i."行目のカテゴリ番号が不正または、対象外なのでスキップしました。<br>";
				continue;
			}
		} else { $ins_mode = "add"; }
		if (!$CHECK_DATA['display']) {
			$CHECK_DATA['display'] = 1;
		}

//pre($L_LIST_NAME);
//pre($CHECK_DATA);
		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }

		$INSERT_DATA = $CHECK_DATA;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {

			$INSERT_DATA[ins_date] = "now()";
			$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] = "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

			$SYS_ERROR[$i] = $cdb->insert(T_PACKAGE_UNIT_LIST,$INSERT_DATA);
			if (!$SYS_ERROR[$i]) {
				$pk_unit_list_num = $cdb->insert_id();
				unset($INSERT_DATA);
				$INSERT_DATA[list_num] = $pk_unit_list_num;
				$where = " WHERE pk_unit_list_num='$pk_unit_list_num' LIMIT 1;";

				$SYS_ERROR[$i] = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
			}
		} else {
			$INSERT_DATA[upd_syr_id] = "updateline";	//	add ookawara 2012/03/27
			$INSERT_DATA[upd_date] = "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

			$where = " WHERE pk_unit_list_num='".$INSERT_DATA['pk_unit_list_num']."' LIMIT 1;";
			$SYS_ERROR[$i] = $cdb->update(T_PACKAGE_UNIT_LIST,$INSERT_DATA,$where);
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
	}
//pre($L_ID_NUM);
//pre($DISP_SORT);
	//各エラー結合
	if(is_array($DATA_ERROR)) {
		foreach($DATA_ERROR as $key => $val) {
			if (!$DATA_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$DATA_ERROR[$key]);
		}
	}
	if(is_array($SYS_ERROR)) {
		foreach($SYS_ERROR as $key => $val) {
			if (!$SYS_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$SYS_ERROR[$key]);
		}
	}
	if (!$ERROR) { $html = "<br>正常に全て登録が完了しました。"; }
	else { $html = "<br>エラーのある行数以外の登録が完了しました。"; }

	return array($html,$ERROR);
}


/**
 * csvインポートチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$CHECK_DATA
 * @param mixed $ins_mode
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data(&$CHECK_DATA,$ins_mode,$line_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$CHECK_DATA['pk_course_num']) { $ERROR[] = "登録するユニットキーリストのコース情報が確認できません。"; }
	if ($_POST['pk_course_num'] && $_POST['pk_course_num'] != $CHECK_DATA['pk_course_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }
	elseif ($_POST['pk_stage_num'] && $_POST['pk_stage_num'] != $CHECK_DATA['pk_stage_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }
	elseif ($_POST['pk_lesson_num'] && $_POST['pk_lesson_num'] != $CHECK_DATA['pk_lesson_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }
	elseif ($_POST['pk_unit_num'] && $_POST['pk_unit_num'] != $CHECK_DATA['pk_unit_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }
	elseif ($_POST['pk_block_num'] && $_POST['pk_block_num'] != $CHECK_DATA['pk_block_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }
	elseif ($_POST['pk_list_num'] && $_POST['pk_list_num'] != $CHECK_DATA['pk_list_num']) { $ERROR[] = "インポート対象外カテゴリのためスキップ"; }

	if ($CHECK_DATA['pk_list_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_LIST.
				" WHERE mk_flg!='1' AND pk_list_num='".$CHECK_DATA['pk_list_num']."'";
	} elseif ($CHECK_DATA['pk_block_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_BLOCK.
				" WHERE mk_flg!='1' AND pk_block_num='".$CHECK_DATA['pk_block_num']."'";
	} elseif ($CHECK_DATA['pk_unit_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_UNIT.
				" WHERE mk_flg!='1' AND pk_unit_num='".$CHECK_DATA['pk_unit_num']."'";
	} elseif ($CHECK_DATA['pk_lesson_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_LESSON.
				" WHERE mk_flg!='1' AND pk_lesson_num='".$CHECK_DATA['pk_lesson_num']."'";
	} elseif ($CHECK_DATA['pk_stage_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_STAGE.
				" WHERE mk_flg!='1' AND pk_stage_num='".$CHECK_DATA['pk_stage_num']."'";
	} elseif ($CHECK_DATA['pk_course_num']) {
		$sql  = "SELECT category_type FROM ".T_PACKAGE_COURSE.
				" WHERE mk_flg!='1' AND pk_course_num='".$CHECK_DATA['pk_course_num']."'";
	}
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['category_type'] != 3) {
		$ERROR[] = "ユニットキーを設定できないカテゴリを指定します。\n";
	}

	if (!$CHECK_DATA['pk_stage_num']) { $category_key = 1; $parent = "pk_course"; }
	elseif (!$CHECK_DATA['pk_lesson_num']) { $category_key = 2; $parent = "pk_stage"; }
	elseif (!$CHECK_DATA['pk_unit_num']) { $category_key = 3; $parent = "pk_lesson"; }
	elseif (!$CHECK_DATA['pk_block_num']) { $category_key = 4; $parent = "pk_unit"; }
	elseif (!$CHECK_DATA['pk_list_num']) { $category_key = 5; $parent = "pk_block"; }
 	else { $category_key = 6; $parent = "pk_list"; }
	$CHECK_DATA['category_key'] = $category_key;
	if (!$CHECK_DATA['unit_key']) { $ERROR[] = "ユニットキーが未入力です。"; }
	elseif (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state!='1'".
				" AND display='1'".
				" AND course_num='".$CHECK_DATA['course_num']."'".
				" AND unit_key='".$CHECK_DATA['unit_key']."'";
		if ($result = $cdb->query($sql)) {
			$unit_count = $cdb->num_rows($result);
		}
		if (!$unit_count) { $ERROR[] = "入力されたユニットキーは存在しません。"; }
		if (!$ERROR) {
			if ($ins_mode == "add") {
				$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
						" WHERE mk_flg!='1'".
						" AND ".$parent."_num='".$CHECK_DATA[$parent.'_num']."'".
						" AND course_num='$CHECK_DATA[course_num]'".
						" AND unit_key='$CHECK_DATA[unit_key]'";
			} else {
				$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
						" WHERE mk_flg!='1'".
						" AND ".$parent."_num='".$CHECK_DATA[$parent.'_num']."'".
						" AND pk_unit_list_num!='$CHECK_DATA[pk_unit_list_num]'" .
						" AND course_num='$CHECK_DATA[course_num]'".
						" AND unit_key='$CHECK_DATA[unit_key]'";
			}
			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if ($count > 0) { $ERROR[] = "入力されたユニットキーは既に登録されております。;"; }
		}
	}

	// add start oda 2014/08/12 課題要望一覧No338-339
	if ($ins_mode == "upd") {
		// 表示順が未設定の場合、エラーとする 課題要望一覧No338
		if (!$CHECK_DATA['list_num']) { $ERROR[] = "表示順が未設定です。"; }
		// 表示順が数値以外の場合、エラーとする  課題要望一覧No338
		if (preg_match("/[^0-9]/",$CHECK_DATA['list_num'])) {
			$ERROR[] = "表示順は数字以外の指定はできません。";
		}
	}
	// add end oda 2014/08/12

	if (!$CHECK_DATA['course_num']) { $ERROR[] = "教科が未設定です。"; }
	if (!$CHECK_DATA['display']) { $ERROR[] = "表示・非表示が未設定です。"; }
	// add start oda 2014/08/21 課題要望一覧No341
	if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
		$ERROR[] = "表示・非表示は数字以外の指定はできません。";
	} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
		$ERROR[] = "表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
	}
	// add end oda 2014/08/21

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}
?>