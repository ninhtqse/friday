<?
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　スキル
 *
 * 履歴
 * 2009/05/25 変更履歴 メール送信用スキル名skill_name2追加
 *
 * @author Azet
 */

//Ozaki

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
		elseif (ACTION == "del") { $ERROR = del(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= select_course();
	}

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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_COURSE .
			" WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if ($max) {
		$html = "<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
		$html .= "<table class=\"stage_form\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"stage_form_menu\">編集コース</td>\n";
		$html .= "</tr>";
		$html .= "<tr>\n";
		$html .= "<td class=\"stage_form_cell\"><select name=\"course_num\" onchange=\"submit();\">\n";

		if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			$course_name_ = $list['course_name'];
			$L_COURSE[$course_num_] = $course_name_;
			if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			$html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
		}

		$html .= "</select></td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
	} else {
		$html = "<br>\n";
		$html .= "コースを設定してからご利用下さい。<br>\n";
	}

	if ($_POST['course_num'] > 0) {
		$html .= skill_list($L_COURSE);
	} else {
		$html .= "<br>\n";
		$html .= "スキルを設定するコースを選択してください。<br>\n";
	}

	return $html;
}

/**
 * 単元一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @return string HTML
 */
function skill_list($L_COURSE) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"submit\" value=\"スキル新規登録\">\n";
//	$html .= "<br>新規登録時表示は非表示・変更時も表示できるLessonが登録されているかチェックする。\n";
	$html .= "</form>\n";

	//add start hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応
	$html .= "<br>\n";
	$html .= FTP_EXPLORER_MESSAGE; 
	//add end hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応

	//	ファイルアップロード用仮設定
	$skill_img_ftp = FTP_URL."skill_img/".$_POST[course_num]."/";
	$skill_voice_ftp = FTP_URL."skill_voice/".$_POST[course_num]."/";

	$html .= "<br>\n";
	$html .= "<a href=\"".$skill_img_ftp."\" target=\"_blank\">スキル画像フォルダー($skill_img_ftp)</a><br>\n";
	$html .= "<a href=\"".$skill_voice_ftp."\" target=\"_blank\">スキル音声フォルダー($skill_voice_ftp)</a><br>\n";

	$sql  = "SELECT * FROM " . T_SKILL .
			" WHERE state!='1' AND course_num='$_POST[course_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br>\n";
		$html .= "今現在登録されているスキルは有りません。<br>\n";
		return $html;
	}
	$html .= "<br>\n";
	$html .= "<table class=\"stage_form\">\n";
	$html .= "<tr class=\"stage_form_menu\">\n";
	$html .= "<th>登録番号</th>\n";
	$html .= "<th>コース名</th>\n";
	$html .= "<th>スキル名</th>\n";
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
	while ($list = $cdb->fetch_assoc($result)) {
		$html .= "<tr class=\"stage_form_cell\">\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$list[course_num]."\">\n";
		$html .= "<input type=\"hidden\" name=\"skill_num\" value=\"".$list[skill_num]."\">\n";
		$html .= "<td>".$list[list_num]."</td>\n";
		$html .= "<td>".$L_COURSE[$_POST[course_num]]."</td>\n";
		$skill_name_ = $list[skill_name];
		$skill_name_ = str_replace("&lt;","<",$skill_name_);
		$skill_name_ = str_replace("&gt;",">",$skill_name_);
		$html .= "<td>".$skill_name_."</td>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
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
 * @return string HTML
 */
function addform($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM " . T_COURSE .
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name = $list['course_name'];
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
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_SKILL_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[SKILLNUM] = array('type'=>'text','name'=>'list_num','size'=>'10','value'=>$_POST[list_num]);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name);
	$INPUTS[SKILLNAME] = array('type'=>'text','name'=>'skill_name','size'=>'40','value'=>$_POST['skill_name'],'size'=>'50');
	$INPUTS[SKILLNAME2] = array('type'=>'text','name'=>'skill_name2','size'=>'40','value'=>$_POST['skill_name2'],'size'=>'50');
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	$newform = new form_parts();
	$newform->set_form_type("text");
	$newform->set_form_name("return_status");
	$newform->set_form_value($_POST['return_status']);
	$return_status .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("button");
	$newform->set_form_id("return_status");
	$newform->set_form_value("追加");
	$return_status .= $newform->make();

	$INPUTS[RETURNSTATUS] = array('result'=>'plane','value'=>$return_status);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"skill_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
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
 * @return string HTML
 */
function viewform($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM " . T_COURSE .
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name = $list['course_name'];
	}

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_SKILL .
			" WHERE skill_num='$_POST[skill_num]' AND state!='1' LIMIT 1;";
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

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= "<input type=\"hidden\" name=\"skill_num\" value=\"$skill_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_SKILL_FORM);

	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS[SKILLNUM] = array('type'=>'text','name'=>'list_num','size'=>'10','value'=>$list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name);
	$INPUTS[SKILLNAME] = array('type'=>'text','name'=>'skill_name','size'=>'40','value'=>$skill_name);
	$INPUTS[SKILLNAME2] = array('type'=>'text','name'=>'skill_name2','size'=>'40','value'=>$skill_name2);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"skill_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
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

	if (!$_POST['course_num']) { $ERROR[] = "登録するスキルのコース情報が確認できません。"; }
	if (!$_POST['list_num']) { $ERROR[] = "スキル登録番号が未入力です。"; }
	else {
		if ($mode == "add") {
			$sql = "SELECT * FROM " . T_SKILL .
				" WHERE state!='1' AND course_num='$_POST[course_num]' AND list_num='$_POST[list_num]'";

			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if ($count > 0) { $ERROR[] = "入力されたスキル登録番号は既に登録されております。"; }
		}
	}

	if (!$_POST['skill_name']) { $ERROR[] = "スキル名が未入力です。"; }
	elseif (strlen($_POST['skill_name2'])>255) { $ERROR[] = "メール送信用スキル名が長すぎます。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM " . T_SKILL .
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND skill_name='$_POST[skill_name]'";
		} else {
			$sql  = "SELECT * FROM " . T_SKILL .
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND skill_num!='$_POST[skill_num]'" .
					" AND skill_name='$_POST[skill_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
//		if ($count > 0) { $ERROR[] = "入力されたスキル名は既に登録されております。"; }
	}
	return $ERROR;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM " . T_SKILL .
			" WHERE skill_num='{$_POST[skill_num]}' AND state!='1' LIMIT 1;";
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

	//	コース名
	$sql  = "SELECT * FROM " . T_COURSE .
			" WHERE course_num='$course_num' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name = $list['course_name'];
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_SKILL_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[SKILLNUM] = array('result'=>'plane','value'=>$list_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name);
	$skill_name = ereg_replace("&lt;","<",$skill_name);
	$skill_name = ereg_replace("&gt;",">",$skill_name);
	$INPUTS[SKILLNAME] = array('result'=>'plane','value'=>$skill_name);
	$INPUTS[SKILLNAME2] = array('result'=>'plane','value'=>$skill_name2);
	$remarks = ereg_replace("&lt;","<",$remarks);
	$remarks = ereg_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
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
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
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
	$INSERT_DATA[update_time] = "now()";

	$ERROR = $cdb->insert(T_SKILL,$INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

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

	if (MODE == "詳細") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE skill_num='$_POST[skill_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_SKILL,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA[state] = 1;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE skill_num='$_POST[skill_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_SKILL,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}
?>