<?
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　リスト作成
 *
 * 履歴
 * 2012/03/26 初期設定
 *
 * @author Azet
 */

/*
	//	add ookawara
	course_num→pk_course_num
	couse_num_html→pk_course_num_html
	m_course_num→m_pk_course_num

	stage_num→pk_stage_num
	stage_name→pk_stage_name
	stage_num_html→pk_stage_num_html
	m_stage_num→m_pk_stage_num

	lesson_num→pk_lesson_num
	lesson_name→pk_lesson_name
	lesson_num_html→pk_lesson_num_html
	m_lesson_num→m_pk_lesson_num

	unit_num→pk_unit_num
	unit_name→pk_unit_name
	unit_num_html→pk_unit_num_html
	m_unit_num→m_pk_unit_num

	block_num→pk_block_num
	block_name→pk_block_name
	block_num_html→pk_block_num_html
	m_block_num→m_pk_block_num

	package_list_num→pk_list_num
	list_name→pk_list_name
	m_package_list_num→m_pk_list_num
	c_package_list_num→c_pk_list_num
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
	}

	list($html,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK) = select_course();
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} else {
			$html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} else {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK);
		}
	} else {
		if ($_POST[pk_course_num]&&$_POST[pk_stage_num]&&$_POST[pk_lesson_num]&&$_POST[pk_unit_num]&&$_POST[pk_block_num]) {
			$html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK);
		}
	}

	return $html;
}


/**
 * コース選択処理
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
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form>\n";

	if (!$_POST[pk_course_num]) {
		$html .= "<br>\n";
		$html .= "リストを設定するコースを選択してください。<br>\n";
	} elseif ($_POST[pk_course_num]&&!$_POST[pk_stage_num]) {
		$html .= "<br>\n";
		$html .= "リストを設定するステージを選択してください。<br>\n";
	} elseif ($_POST[pk_course_num]&&$_POST[pk_stage_num]&&!$_POST[pk_lesson_num]) {
		$html .= "<br>\n";
		$html .= "リストを設定するレッスンを選択してください。<br>\n";
	} elseif ($_POST[pk_course_num]&&$_POST[pk_stage_num]&&$_POST[pk_lesson_num]&&!$_POST[pk_unit_num]) {
		$html .= "<br>\n";
		$html .= "リストを設定するユニットを選択してください。<br>\n";
	} elseif ($_POST[pk_course_num]&&$_POST[pk_stage_num]&&$_POST[pk_lesson_num]&&$_POST[pk_unit_num]&&!$_POST[pk_block_num]) {
		$html .= "<br>\n";
		$html .= "リストを設定するブロックを選択してください。<br>\n";
	}

	return array($html,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK);
}


/**
 * ユニット選択処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @param array $L_UNIT
 * @param array $L_BLOCK
 * @return string HTML
 */
function unit_list($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {

		$sql  = "SELECT category_type FROM ".T_PACKAGE_BLOCK.
				" WHERE mk_flg!='1' AND pk_block_num='".$_POST['pk_block_num']."'";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['category_type'] != 3) {
			$html .= "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$_POST[pk_stage_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_lesson_num\" value=\"$_POST[pk_lesson_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_unit_num\" value=\"$_POST[pk_unit_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_block_num\" value=\"$_POST[pk_block_num]\">\n";
			$html .= "<input type=\"submit\" value=\"リスト新規登録\">\n";
			$html .= "</form>\n";
		} else {
			$html .= "<br>\n";
			$html .= "このカテゴリはユニットキー設定専用です。<br>\n";
			return $html;
		}
	}

	$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE mk_flg!='1' AND pk_block_num='$_POST[pk_block_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br>\n";
		$html .= "今現在登録されているリストは有りません。<br>\n";
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
	$html .= "<th>ステージ名</th>\n";
	$html .= "<th>レッスン名</th>\n";
	$html .= "<th>ユニット名</th>\n";
	$html .= "<th>ブロック名</th>\n";
	$html .= "<th>リスト名</th>\n";
	$html .= "<th>備考</th>\n";
//	$html .= "<th>カテゴリタイプ</th>\n";
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
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td>{$up_submit}</td>\n";
			$html .= "<td>{$down_submit}</td>\n";
		}
		$html .= "<td>{$pk_list_num}</td>\n";
		$html .= "<td>{$L_COURSE[$_POST[pk_course_num]]}</td>\n";
		$html .= "<td>{$L_STAGE[$pk_stage_num]}</td>\n";
		$html .= "<td>{$L_LESSON[$pk_lesson_num]}</td>\n";
		$html .= "<td>{$L_UNIT[$pk_unit_num]}</td>\n";
		$html .= "<td>{$L_BLOCK[$pk_block_num]}</td>\n";
		$html .= "<td>{$pk_list_name}</td>\n";
		$html .= "<td>{$remarks}</td>\n";
//		$html .= "<td>{$L_CATEGORY_TYPE[$category_type]}</td>\n";
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
 * @return string HTML
 */
function addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK) {

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

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_LIST_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('type'=>'text','name'=>'pk_list_name','value'=>$_POST['pk_list_name']);
	$INPUTS[CATEGORYTYPE] = array('type'=>'select','name'=>'category_type','array'=>$L_CATEGORY_TYPE,'check'=>$_POST['category_type']);
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
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 表示の為のフォーム
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
 * @return string HTML
 */
function viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE pk_list_num='$_POST[pk_list_num]' AND mk_flg!='1' LIMIT 1;";

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
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$pk_list_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_LIST_FORM);

	if (!$pk_list_num) { $pk_list_num = "---"; }
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>$pk_list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('type'=>'text','name'=>'pk_list_name','value'=>$pk_list_name);
	$INPUTS[CATEGORYTYPE] = array('type'=>'select','name'=>'category_type','array'=>$L_CATEGORY_TYPE,'check'=>$category_type);
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
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * POSTデータ確認の機能
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

	if (!$_POST['pk_course_num']) { $ERROR[] = "登録するリストのコース情報が確認できません。"; }
	if (!$_POST['pk_stage_num']) { $ERROR[] = "登録するリストのステージ情報が確認できません。"; }
	if (!$_POST['pk_lesson_num']) { $ERROR[] = "登録するリストのステージ情報が確認できません。"; }
	if (!$_POST['pk_unit_num']) { $ERROR[] = "登録するリストのユニット情報が確認できません。"; }
	if (!$_POST['pk_unit_num']) { $ERROR[] = "登録するリストのユニット情報が確認できません。"; }

	if (!$_POST['pk_list_name']) { $ERROR[] = "リスト名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
					" WHERE mk_flg!='1' AND pk_unit_num='$_POST[pk_unit_num]' AND pk_block_name='$_POST[pk_block_name]'";
		} else {
			$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
					" WHERE mk_flg!='1' AND pk_unit_num='$_POST[pk_unit_num]' AND pk_block_num!='$_POST[pk_block_num]'" .
					" AND pk_block_name='$_POST[pk_block_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたブロック名は既に登録されております。"; }
	}
//	if (!$_POST['category_type']) { $ERROR[] = "カテゴリタイプが未選択です。"; }
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
 * @return string HTML
 */
function check_html($L_COURSE,$L_STAGE,$L_LESSON,$L_UNIT,$L_BLOCK) {

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
		$sql = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE pk_list_num='$_POST[pk_list_num]' AND mk_flg!='1' LIMIT 1;";

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
	$make_html->set_file(PACKAGE_LIST_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$pk_list_num) { $pk_list_num = "---"; }
	$INPUTS[LESSONNUM] = array('result'=>'plane','value'=>$pk_list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$L_COURSE[$_POST[pk_course_num]]);
	$INPUTS[UNITGROUPNAME] = array('result'=>'plane','value'=>$L_STAGE[$_POST[pk_stage_num]]);
	$INPUTS[LESSON] = array('result'=>'plane','value'=>$L_LESSON[$_POST[pk_lesson_num]]);
	$INPUTS[UNIT] = array('result'=>'plane','value'=>$L_UNIT[$_POST[pk_unit_num]]);
	$INPUTS[BLOCK] = array('result'=>'plane','value'=>$L_BLOCK[$_POST[pk_block_num]]);
	$INPUTS[PACKAGELISTNAME] = array('result'=>'plane','value'=>$pk_list_name);
	$INPUTS[CATEGORYTYPE] = array('result'=>'plane','value'=>$L_CATEGORY_TYPE[$category_type]);
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
	$html .= "<input type=\"hidden\" name=\"pk_list_num\" value=\"$pk_list_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * DB登録処理
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
	$INSERT_DATA[ins_date] = "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] = "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

	$ERROR = $cdb->insert(T_PACKAGE_LIST,$INSERT_DATA);

	if (!$ERROR) {
		$pk_list_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $pk_list_num;
		$where = " WHERE pk_list_num='$pk_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
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
function sqlGuard($val) {

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
 * DB変更処理
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

		$INSERT_DATA[pk_list_name] = $_POST[pk_list_name];
		$INSERT_DATA[remarks] = $_POST[remarks];
//		$INSERT_DATA[category_type] = $_POST[category_type];
		$INSERT_DATA[display] = $_POST[display];
		$INSERT_DATA[upd_syr_id] = "updateline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_list_num='$_POST[pk_list_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[mk_flg] = 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] = "now()";	//	add ookawara 2012/03/27
		$where = " WHERE pk_list_num='$_POST[pk_list_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * PACKAGE_LISTを上がる機能
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

	$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE pk_list_num='$_POST[pk_list_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_lesson_num = $list['pk_lesson_num'];
		$m_pk_unit_num = $list['pk_unit_num'];
		$m_pk_block_num = $list['pk_block_num'];
		$m_pk_list_num = $list['pk_list_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_list_num || !$m_list_num) { $ERROR[] = "移動するブロック情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
				" WHERE mk_flg!='1' AND pk_block_num='$m_pk_block_num' AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_list_num = $list['pk_list_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_list_num || !$c_list_num) { $ERROR[] = "移動されるブロック情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_list_num='$m_pk_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_list_num='$c_pk_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * PACKAGE_LISTを下がる機能
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

	$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE pk_list_num='$_POST[pk_list_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_lesson_num = $list['pk_lesson_num'];
		$m_pk_unit_num = $list['pk_unit_num'];
		$m_pk_block_num = $list['pk_block_num'];
		$m_pk_list_num = $list['pk_list_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_list_num || !$m_list_num) { $ERROR[] = "移動するブロック情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
				" WHERE mk_flg!='1' AND pk_block_num='$m_pk_block_num' AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_list_num = $list['pk_list_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_list_num || !$c_list_num) { $ERROR[] = "移動されるブロック情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_list_num='$m_pk_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_list_num='$c_pk_list_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_LIST,$INSERT_DATA,$where);
	}

	return $ERROR;
}
?>