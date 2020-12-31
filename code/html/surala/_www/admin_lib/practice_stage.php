<?
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　ステージ
 *
 * 履歴
 * 2008.10.08 変更履歴 画面・フィールド（「単元名」）追加
 *
 * @author Azet
 */

// UPD20081008_1  K.Tezuka

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
	// 2012/08/28 add start oda
	if (ACTION == "status_check") {
		$ERROR = status_check();
	}
	// 2012/08/28 add end oda
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "status_update") { $ERROR = status_update(); }	// 2012/08/28 add oda
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

	// 2012/08/28 add start oda
	} elseif (MODE == "status_mod") {
		if (ACTION == "status_check") {
			if (!$ERROR) { $html .= status_check_html(); }
			else { $html .= status_viewform($ERROR); }
		} elseif (ACTION == "status_update") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= status_viewform($ERROR); }
		} else {
			$html .= status_viewform($ERROR);
		}
	// 2012/08/28 add end oda
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

	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
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
		}
		else {
			$html = "<br>\n";
			$html .= "コースを設定してからご利用下さい。<br>\n";
		}
	}

	if ($_POST['course_num'] > 0) {
		$html .= menu_status_list();
		$html .= stage_list($L_COURSE);
	} else {
		$html .= "<br>\n";
		$html .= "ステージを設定するコースを選択してください。<br>\n";
	}

	return $html;
}


/**
 * メニューステータスリスト
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function menu_status_list() {
	// 2012/08/28 add oda

	global $L_STATUS_SETUP;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	// ステータス編集画面遷移用領域
	$html .= "<br>\n";
	$html .= "【ステータス設定】\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "	<input type=\"hidden\" name=\"mode\" value=\"status_mod\">\n";
	$html .= "	<input type=\"hidden\" name=\"menu_status_num\" value=\"".$_POST['menu_status_num']."\">\n";
	$html .= "	<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "	<input type=\"submit\" value=\"ステータス編集\">\n";
	$html .= "</form>\n";

	// メニューステータス情報取得
	$sql  = "SELECT * FROM ".T_MENU_STATUS.
			" WHERE mk_flg = '0'".
			"   AND course_num = '".$_POST['course_num']."'".
			"   AND stage_num = '0'".
			"   AND lesson_num = '0'".
			"   AND unit_num = '0'".
			"   AND display = '1'";


	if ($result = $cdb->query($sql)) {

		$max = $cdb->num_rows($result);

		if ($max) {
			$html .= "<br>\n";
			$html .= "<table class=\"stage_form\">\n";
			$html .= "	<tr>\n";
			$html .= "		<td class=\"stage_form_menu\">ステータス</td>\n";
			$html .= "		<td class=\"stage_form_menu\">出力内容</td>\n";
			$html .= "		<td class=\"stage_form_menu\">単位</td>\n";
			$html .= "	</tr>";

			while ($list = $cdb->fetch_assoc($result)) {

				// ステータス１表示
				for ($i=1; $i<7; $i++) {
					if ($list['status'.$i] > 0) {
						$html .= "	<tr>\n";
						$html .= "		<td class=\"stage_form_cell\">ステータス".$i."</td>\n";
						$html .= "		<td class=\"stage_form_cell\">".$L_STATUS_SETUP[$list['status'.$i]]."</td>\n";
						$html .= "		<td class=\"stage_form_cell\">".$list['status'.$i.'_comment']."</td>\n";
						$html .= "	</tr>\n";
					}
				}
			}
			$html .= "	</table>\n";
		}
	}
	$html .= "<hr>\n";

	return $html;
}


/**
 * ステータス修正画面
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function status_viewform($ERROR) {
	// 2012/08/28 add oda

	global $L_DISPLAY, $L_STATUS_SETUP;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='".$_POST['course_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name = $list['course_name'];
	}

	// 画面情報取得
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql  = "SELECT * FROM ".T_MENU_STATUS.
				" WHERE mk_flg = '0'".
				"   AND course_num = '".$_POST['course_num']."'".
				"   AND stage_num = '0'".
				"   AND lesson_num = '0'".
				"   AND unit_num = '0'";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		} else {
			$course_num = $_POST['course_num'];
			$status1 = 0;
			$status2 = 0;
			$status3 = 0;
			$status4 = 0;
			$status5 = 0;
			$status6 = 0;
			$status1_comment = "";
			$status2_comment = "";
			$status3_comment = "";
			$status4_comment = "";
			$status5_comment = "";
			$status6_comment = "";
		}
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"status_check\" />\n";
	$html .= "<input type=\"hidden\" name=\"menu_status_num\" value=\"$menu_status_num\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\" />\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	$INPUTS[COURSENUM]       = array('result'=>'plane','value'=>$course_num);
	$INPUTS[COURSENAME]      = array('result'=>'plane','value'=>$course_name);
	$INPUTS[COURSENAMETITLE] = array('result'=>'plane','value'=>"コース名");
	$INPUTS[STATUS1]         = array('type'=>'select','name'=>'status1','array'=>$L_STATUS_SETUP,'check'=>$status1);
	$INPUTS[STATUS1COMMENT]  = array('type'=>'text','name'=>'status1_comment','value'=>$status1_comment);
	$INPUTS[STATUS2]         = array('type'=>'select','name'=>'status2','array'=>$L_STATUS_SETUP,'check'=>$status2);
	$INPUTS[STATUS2COMMENT]  = array('type'=>'text','name'=>'status2_comment','value'=>$status2_comment);
	$INPUTS[STATUS3]         = array('type'=>'select','name'=>'status3','array'=>$L_STATUS_SETUP,'check'=>$status3);
	$INPUTS[STATUS3COMMENT]  = array('type'=>'text','name'=>'status3_comment','value'=>$status3_comment);
	$INPUTS[STATUS4]         = array('type'=>'select','name'=>'status4','array'=>$L_STATUS_SETUP,'check'=>$status4);
	$INPUTS[STATUS4COMMENT]  = array('type'=>'text','name'=>'status4_comment','value'=>$status4_comment);
	$INPUTS[STATUS5]         = array('type'=>'select','name'=>'status5','array'=>$L_STATUS_SETUP,'check'=>$status5);
	$INPUTS[STATUS5COMMENT]  = array('type'=>'text','name'=>'status5_comment','value'=>$status5_comment);
	$INPUTS[STATUS6]         = array('type'=>'select','name'=>'status6','array'=>$L_STATUS_SETUP,'check'=>$status6);
	$INPUTS[STATUS6COMMENT]  = array('type'=>'text','name'=>'status6_comment','value'=>$status6_comment);

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
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "	<input type=\"submit\" value=\"変更確認\">";
	$html .= "	<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";

	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "	<input type=\"hidden\" name=\"mode\" value=\"back_menu\" />\n";
	$html .= "	<input type=\"hidden\" name=\"menu_status_num\" value=\"$menu_status_num\" />\n";
	$html .= "	<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "	<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * メニューステータス確認画面
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function status_check_html() {
	// 2012/08/28 add oda
	global $L_DISPLAY, $L_STATUS_SETUP;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				$val = "status_update";
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"status_update\" />\n";
		$sql  = "SELECT * FROM ".T_MENU_STATUS.
				" WHERE mk_flg = '0'".
				"   AND menu_status_num = '".$_POST['menu_status_num']."'";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		}
	}

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num = '".$course_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name = $list['course_name'];
	}

	$button = "更新";
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[COURSENUM]       = array('result'=>'plane','value'=>$course_num);
	$INPUTS[COURSENAME]      = array('result'=>'plane','value'=>$course_name);
	$INPUTS[COURSENAMETITLE] = array('result'=>'plane','value'=>"コース名");
	$INPUTS[STATUS1]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status1]);
	$INPUTS[STATUS1COMMENT]  = array('result'=>'plane','value'=>$status1_comment);
	$INPUTS[STATUS2]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status2]);
	$INPUTS[STATUS2COMMENT]  = array('result'=>'plane','value'=>$status2_comment);
	$INPUTS[STATUS3]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status3]);
	$INPUTS[STATUS3COMMENT]  = array('result'=>'plane','value'=>$status3_comment);
	$INPUTS[STATUS4]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status4]);
	$INPUTS[STATUS4COMMENT]  = array('result'=>'plane','value'=>$status4_comment);
	$INPUTS[STATUS5]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status5]);
	$INPUTS[STATUS5COMMENT]  = array('result'=>'plane','value'=>$status5_comment);
	$INPUTS[STATUS6]         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status6]);
	$INPUTS[STATUS6COMMENT]  = array('result'=>'plane','value'=>$status6_comment);
	$INPUTS[DISPLAY]         = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\" />\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\" />\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\" />";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"status_mod\" />\n";
	}
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * ステータス内容チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function status_check() {
	// 2012/08/28 add oda

	if (!$_POST['status1'] && $_POST['status1_comment']) {
		$ERROR[] = "ステータス１の単位のみ設定しております。ステータス１を設定して下さい。";
	}
	if (!$_POST['status2'] && $_POST['status2_comment']) {
		$ERROR[] = "ステータス２の単位のみ設定しております。ステータス２を設定して下さい。";
	}
	if (!$_POST['status3'] && $_POST['status3_comment']) {
		$ERROR[] = "ステータス３の単位のみ設定しております。ステータス３を設定して下さい。";
	}
	if (!$_POST['status4'] && $_POST['status4_comment']) {
		$ERROR[] = "ステータス４の単位のみ設定しております。ステータス４を設定して下さい。";
	}
	if (!$_POST['status5'] && $_POST['status5_comment']) {
		$ERROR[] = "ステータス５の単位のみ設定しております。ステータス５を設定して下さい。";
	}
	if (!$_POST['status6'] && $_POST['status6_comment']) {
		$ERROR[] = "ステータス６の単位のみ設定しております。ステータス６を設定して下さい。";
	}

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未選択です。";
	}

	return $ERROR;
}


/**
 * ステータス情報更新
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function status_update() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}

	// データ存在チェック
	$sql  = "SELECT * FROM ".T_MENU_STATUS.
			" WHERE mk_flg = '0'".
			"   AND menu_status_num = '".$_POST['menu_status_num']."'";

	$result = $cdb->query($sql);
	$rec_count = $cdb->num_rows($result);

	// 登録処理
	if ($rec_count == 0) {
		$INSERT_DATA['upd_syr_id']	= 'add';
		$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']	= "now()";
		$INSERT_DATA['ins_syr_id']	= 'add';
		$INSERT_DATA['ins_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 	= "now()";

		$ERROR = $cdb->insert(T_MENU_STATUS,$INSERT_DATA);
	// 更新処理
	} else {
		$INSERT_DATA['upd_syr_id']	= 'update';
		$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']	= "now()";
		$where  = " WHERE menu_status_num = '".$_POST['menu_status_num']."'";
		$where .= " LIMIT 1;";

		$ERROR = $cdb->update(T_MENU_STATUS,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * ステージ一覧の処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @return string HTML
 */
function stage_list($L_COURSE) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$html .= "<br>\n";
	$html .= "【ステージリスト設定】\n";

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"submit\" value=\"ステージ新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE state!='1' AND course_num='$_POST[course_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているステージは有りません。<br>\n";
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
		$html .= "<th>ステージキー</th>\n";
        // --- UPD20081008_1  フィールド追加
		$html .= "<th>単元名</th>\n";
        // --- UPD20081008_1  ここまで
//		$html .= "<th>クリアーステージキー</th>\n";
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

			$material_flash_path = MATERIAL_FLASH_DIR . $list['course_num'] . "/" . $list['stage_num'] . "/";
			if (!file_exists($material_flash_path)) {
				@mkdir($material_flash_path, 0777);
				@chmod($material_flash_path, 0777);
			}
			$temp_path = STUDENT_TEMP_DIR . $list['course_num'] . "/" . $list['stage_num'] . "/";
			if (!file_exists($temp_path)) {
				@mkdir($temp_path, 0777);
				@chmod($temp_path, 0777);
			}

			$material_prob_path = MATERIAL_PROB_DIR . $list['course_num'] ."/" . $list['stage_num'] . "/";
			if (!file_exists($material_prob_path)) {
				@mkdir($material_prob_path,0777);
				@chmod($material_prob_path,0777);
			}

			$material_temp_path = MATERIAL_TEMP_DIR . $list['course_num'] ."/" . $list['stage_num'] . "/";
			if (!file_exists($material_temp_path)) {
				@mkdir($material_temp_path,0777);
				@chmod($material_temp_path,0777);
			}
			$material_voice_path = MATERIAL_VOICE_DIR . $list['course_num'] ."/" . $list['stage_num'] . "/";
			if (!file_exists($material_voice_path)) {
				@mkdir($material_voice_path,0777);
				@chmod($material_voice_path,0777);
			}

			$stage_num_ = $list['stage_num'];
			$list_num_ = $list['list_num'];
			$LINE[$list_num_] = $stage_num_;
			foreach ($list AS $KEY => $VAL) {
				$KEY .= "_";
				$$KEY = $VAL;
			}

			if ($clear_stage_key_) {
				$clear_stage_key_ = eregi_replace("^:|:$","",$clear_stage_key_);
			}

			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			$html .= "<tr class=\"stage_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST[course_num]}\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$stage_num_}\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>{$up_submit}</td>\n";
				$html .= "<td>{$down_submit}</td>\n";
			}
			$html .= "<td>{$stage_num_}</td>\n";
			$html .= "<td>{$L_COURSE[$course_num_]}</td>\n";
			$html .= "<td>{$stage_name_}</td>\n";
			$html .= "<td>{$stage_key_}</td>\n";

			//	add ookawara 2010/02/04
			$stage_name2_ = str_replace("&lt;","<",$stage_name2_);
			$stage_name2_ = str_replace("&gt;",">",$stage_name2_);

            // --- UPD20081008_1  単元名　追加
			$html .= "<td>{$stage_name2_}</td>\n";
            // --- UPD20081008_1
//			$html .= "<td>{$clear_stage_key_}</td>\n";
			$html .= "<td>{$L_DISPLAY[$display_]}</td>\n";
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
	}
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

	global $L_DISPLAY,$L_ONOFF_TYPE,$L_SCREEN_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name_ = $list['course_name'];
	}

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_STAGE_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name_);
	$INPUTS[STAGENAME] = array('type'=>'text','name'=>'stage_name','value'=>$_POST['stage_name']);
	$INPUTS[STAGEKEY] = array('type'=>'text','name'=>'stage_key','value'=>$_POST['stage_key']);
	$INPUTS[CLEARSTAGEKEY] = array('type'=>'text','name'=>'clear_stage_key','value'=>$_POST['clear_stage_key']);
/*
	//	del ookawara 2010/02/04
    // --- UPD20081008_1  単元名　追加
    $wESC  =  htmlspecialchars( $_POST['stage_name2'] );
    $INPUTS[STAGENAME2] = array('type'=>'text','name'=>'stage_name2','value'=>$wESC);
    // --- UPD20081008_1 ここまで
*/
    $INPUTS[STAGENAME2] = array('type'=>'text','name'=>'stage_name2','value'=>$_POST['stage_name2']);

    // 2012/08/20 add start oda
    $INPUTS[SURALAUNIT] = array('type'=>'text','name'=>'surala_unit','value'=>$_POST['surala_unit']);
    $surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
    $surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
    $INPUTS[SURALAUNITATT] 	= array('result'=>'plane','value'=>$surala_unit_att);

	$INPUTS[SCREENDISPLAY] = array('type'=>'select','name'=>'screen_display','array'=>$L_SCREEN_DISPLAY,'check'=>$_POST['screen_display']);
	// 2012/08/20 add end oda

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// if ($_POST['signboard_flg'] == "") {
	// 	$_POST['signboard_flg'] = "0";
	// }
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$_POST['signboard_name']);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$_POST['remarks']); //add kimura 2018/10/31 すらら英単語

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
	$display = $display . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\" />\n";
	$html .= "<input type=\"reset\" value=\"クリア\" />\n";
	$html .= "</form>\n";
/*
	$html .= "<b>クリアーステージキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないステージが有る場合<br>\n";
	$html .= "　クリアーしなければいけないステージのステージキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるステージが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なステージが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：s1::s3::s5<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"stage_list\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
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

	global $L_DISPLAY,$L_ONOFF_TYPE,$L_SCREEN_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name_ = $list['course_name'];
	}

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='$_POST[stage_num]' AND state!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		if ($clear_stage_key) {
			$clear_stage_key = eregi_replace("^:|:$","",$clear_stage_key);
		}

		$status_display = "";

    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    	$surala_unit = change_unit_num_to_key_all($surala_unit);		// 2012/09/27 add oda
		// 2012/08/20 add end oda
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$stage_num\" />\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_STAGE_FORM);

	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>$stage_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name_);
	$INPUTS[STAGENAME] = array('type'=>'text','name'=>'stage_name','value'=>$stage_name);
	$INPUTS[STAGEKEY] = array('type'=>'text','name'=>'stage_key','value'=>$stage_key);
	$INPUTS[CLEARSTAGEKEY] = array('type'=>'text','name'=>'clear_stage_key','value'=>$clear_stage_key);

	//	add ookawara 2010/02/04
	$stage_name2 = str_replace("&lt;","<",$stage_name2);
	$stage_name2 = str_replace("&gt;",">",$stage_name2);

    // --- UPD20081008_1  単元名　追加
    $INPUTS[STAGENAME2] = array('type'=>'text','name'=>'stage_name2','value'=>$stage_name2);
    // --- UPD20081008_1 ここまで

    // 2012/08/20 add start oda
    $INPUTS[SURALAUNIT] = array('type'=>'text','name'=>'surala_unit','value'=>$surala_unit);
    $surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
    $surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
    $INPUTS[SURALAUNITATT] 	= array('result'=>'plane','value'=>$surala_unit_att);

	$INPUTS[SCREENDISPLAY] = array('type'=>'select','name'=>'screen_display','array'=>$L_SCREEN_DISPLAY,'check'=>$screen_display);
    // 2012/08/20 add end oda

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$signboard_name);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語

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
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
/*
	$html .= "<b>クリアーステージキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないステージが有る場合<br>\n";
	$html .= "　クリアーしなければいけないステージのステージキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるステージが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なステージが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：s1::s3::s5<br>\n";
	$html .= "<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\" />\n";
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

	if (!$_POST['course_num']) { $ERROR[] = "登録するステージのコース情報が確認できません。"; }

	if (!$_POST['stage_name']) { $ERROR[] = "ステージ名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_STAGE.
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND stage_name='$_POST[stage_name]'";
		} else {
			$sql  = "SELECT * FROM ".T_STAGE.
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND stage_num!='$_POST[stage_num]'" .
					" AND stage_name='$_POST[stage_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたステージ名は既に登録されております。"; }
	}
	$_POST['stage_key'] = mb_convert_kana($_POST['stage_key'],"as","UTF-8");
	$_POST['stage_key'] = trim($_POST['stage_key']);
	if (!$_POST['stage_key']) { $ERROR[] = "ステージキーが未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_STAGE.
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND stage_key='$_POST[stage_key]'";
		} else {
			$sql  = "SELECT * FROM ".T_STAGE.
					" WHERE state!='1' AND course_num='$_POST[course_num]' AND stage_num!='$_POST[stage_num]'" .
					" AND stage_key='$_POST[stage_key]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたステージキーは既に登録されております。"; }
	}
	if ($_POST['clear_stage_key']) {
		$_POST['clear_stage_key'] = mb_convert_kana($_POST['clear_stage_key'],"as","UTF-8");
		$_POST['clear_stage_key'] = trim($_POST['clear_stage_key']);
		$clear_stage_key_ = ":".$_POST['clear_stage_key'].":";
		$check_stage_key = ":".$_POST['stage_key'].":";
		if (eregi("start",$_POST['clear_stage_key']) && eregi_replace("start","",$_POST['clear_stage_key']) != "") {
			$ERROR[] = "クリアーステージキーに「start」を入力した場合、他のステージキーは入力できません。";
		} elseif ($_POST['stage_key'] && eregi($check_stage_key,$clear_stage_key_)) {
			$ERROR[] = "クリアーステージキーに、登録されるステージのステージキーを入力しないでください。";
		}
	}

	// 2012/08/20 add start oda
	if ($_POST['surala_unit']) {
		$surala_unit_count = 0;
//		$surala_unit_list = explode("::",$_POST['surala_unit']);								// 2012/09/27 del oda
		$surala_unit_list = explode("::",change_unit_key_to_num_all($_POST['surala_unit']));	// 2012/09/27 add oda

		// ユニットIDに数値以外の値が入っていないかチェック
		$error_flag = false;
		for ($j=0; $j<count($surala_unit_list); $j++) {
			if (is_numeric($surala_unit_list[$j]) == false) {
				$ERROR[] = "すららで復習ユニットに指定したユニットは、不正な情報が混ざっています。";
				$error_flag = true;
			}
		}

		// ユニットIDが存在するかチェック
		if (!$error_flag) {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state='0' AND unit_num IN ('" . implode("','", $surala_unit_list) ."')";
			if ($result = $cdb->query($sql)) {
				$surala_unit_count = $cdb->num_rows($result);
			}

			if (count($surala_unit_list) > 0) {
				if ($surala_unit_count != count($surala_unit_list)) {
					$ERROR[] = "すららで復習ユニットに指定したユニットは、既に削除されているか、不正な情報が混ざっています。";
				}
			}
		}
	}
	// 2012/08/20 add end oda

	if ($_POST['signboard_flg'] == "1" && !$_POST['signboard_name']) {			// add oda 2013/11/15
		$ERROR[] = "生徒画面の看板表示内容が未入力です。";
	}

	//if (!$_POST['standard_study_time']) { $ERROR[] = "標準学習時間表示が未選択です。"; }		// 2012/08/20 add oda
	//if (!$_POST['study_time']) { $ERROR[] = "学習時間表示が未選択です。"; }					// 2012/08/20 add oda
	//if (!$_POST['remainder_unit']) { $ERROR[] = "残りユニット数表示が未選択です。"; }			// 2012/08/20 add oda
//	if (!($_POST['screen_display']=='0' || $_POST['screen_display']=='1')) { $ERROR[] = "弱点／単語リスト表示区分が未選択です。"; }		// 2012/08/20 add oda
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}

/**
 * 確認の為のHTML を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_DISPLAY,$L_ONOFF_TYPE,$L_SCREEN_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

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
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\" />\n";
		$sql = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='{$_POST[stage_num]}' AND state!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		$status_display = "";

    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    	$surala_unit = change_unit_num_to_key_all($surala_unit);

	}

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='$course_num' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_name_ = $list['course_name'];
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_STAGE_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$stage_num) { $stage_num = "---"; }
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>$stage_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name_);
	$INPUTS[STAGENAME] = array('result'=>'plane','value'=>$stage_name);
	$INPUTS[STAGEKEY] = array('result'=>'plane','value'=>$stage_key);
	$INPUTS[CLEARSTAGEKEY] = array('result'=>'plane','value'=>$clear_stage_key);
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS[SIGNBOARDFLG]  = array('result'=>'plane','value'=>$L_CHANGE_TYPE[$signboard_flg]);			// add oda 2013/11/15
	$INPUTS[SIGNBOARDNAME] = array('result'=>'plane','value'=>$signboard_name);							// add oda 2013/11/15
	$INPUTS['REMARKS']  = array('result'=>'plane', 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語
	// --- UPD20081008_1  単元名　追加
//    $wESC  =  htmlspecialchars( $stage_name2 );	//	del ookawara 2010/02/04
//    $INPUTS[STAGENAME2] = array('result'=>'plane','value'=>$wESC);	//	del ookawara 2010/02/04
	//	add ookawara 2010/02/04
	$stage_name2 = str_replace("&lt;","<",$stage_name2);
	$stage_name2 = str_replace("&gt;",">",$stage_name2);
    $INPUTS[STAGENAME2] = array('result'=>'plane','value'=>$stage_name2);
    // --- UPD20081008_1 ここまで

    // 2012/08/20 add start oda
    $INPUTS[SURALAUNIT] = array('result'=>'plane','value'=>$surala_unit);

	$INPUTS[SCREENDISPLAY] = array('result'=>'plane','value'=>$L_SCREEN_DISPLAY[$screen_display]);
    // 2012/08/20 add end oda

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\" />\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\" />\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\" />";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\" />\n";
	}
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "clear_stage_key" && $val) { $val = ":".$val.":"; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[update_time] = "now()";

	// 2012/08/20 add start oda
	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($INSERT_DATA['surala_unit']);			// 2012/09/27 add oda
//	$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], 0, 0, 0, 0, 0, $INSERT_DATA['surala_unit']);
	unset($INSERT_DATA['surala_unit']);

	//　ステータス表示区分の編集
	$status_display = "";
	$INSERT_DATA['status_display'] = $status_display;

	unset($INSERT_DATA['standard_study_time']);
	unset($INSERT_DATA['study_time']);
	unset($INSERT_DATA['remainder_unit']);
	// 2012/08/20 add end oda

	$ERROR = $cdb->insert(T_STAGE,$INSERT_DATA);

	if (!$ERROR) {
		$stage_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $stage_num;
		$where = " WHERE stage_num='$stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $stage_num, 0, 0, 0, 0, 0, $surala_unit);		// 2012/09/27 add oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB変更
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "詳細") {
		$sql  = "SELECT stage_key FROM ".T_STAGE.
				" WHERE stage_num='$_POST[stage_num]' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$check_stage_key = $list['stage_key'];
		}

		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "clear_stage_key" && $val) { $val = ":".$val.":"; }
			elseif ($key == "stage_key") { $$key = $val; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA[update_time] = "now()";

		// 2012/08/20 add start oda
		// すららで復習ユニット登録
		$surala_unit = change_unit_key_to_num_all($_POST['surala_unit']);
//		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0, $_POST['surala_unit']);		// 2012/09/27 del oda
		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0, $surala_unit);				// 2012/09/27 add oda
		unset($INSERT_DATA['surala_unit']);

		// すららで復習ユニット登録
		$status_display = "";
		$INSERT_DATA['status_display'] = $status_display;

		unset($INSERT_DATA['standard_study_time']);
		unset($INSERT_DATA['study_time']);
		unset($INSERT_DATA['remainder_unit']);
		// 2012/08/20 add end oda

		$where = " WHERE stage_num='$_POST[stage_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);

		if ($check_stage_key != $stage_key) {
			//	LESSON
			unset($INSERT_DATA);
			$sql  = "SELECT lesson_num, lesson_key FROM ".T_LESSON.
					" WHERE stage_num='$_POST[stage_num]' AND lesson_key!='';";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$lesson_num = $list['lesson_num'];
					$lesson_key = $list['lesson_key'];
					$lesson_key = ereg_replace("^$check_stage_key","$stage_key",$lesson_key);

					$INSERT_DATA[lesson_key] = $lesson_key;
					$INSERT_DATA[update_time] = "now()";
					$where = " WHERE lesson_num='$lesson_num' LIMIT 1;";

					$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);
				}
			}

			//	UNIT
			unset($INSERT_DATA);
			$sql  = "SELECT unit_num, unit_key FROM ".T_UNIT.
					" WHERE stage_num='$_POST[stage_num]' AND unit_key!='';";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$unit_num = $list['unit_num'];
					$unit_key = $list['unit_key'];
					$unit_key = ereg_replace("^$check_stage_key","$stage_key",$unit_key);

					$INSERT_DATA[unit_key] = $unit_key;
					$INSERT_DATA[update_time] = "now()";
					$where = " WHERE unit_num='$unit_num' LIMIT 1;";

					$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);
				}
			}
		}
	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[state] = 1;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE stage_num='$_POST[stage_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
		// 2012/08/22 add start oda
		// すららで復習ユニット登録
		$ERROR = delete_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
		// 2012/08/22 add end oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * STAGEを上がる機能
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

	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='$_POST[stage_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_course_num = $list['course_num'];
		$m_stage_num = $list['stage_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_stage_num || !$m_list_num) { $ERROR[] = "移動するステージ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE state!='1' AND course_num='$m_course_num' AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_stage_num = $list['stage_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_stage_num || !$c_list_num) { $ERROR[] = "移動されるステージ情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE stage_num='$m_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE stage_num='$c_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * STAGEを下がる機能
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

	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='$_POST[stage_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_course_num = $list['course_num'];
		$m_stage_num = $list['stage_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_stage_num || !$m_list_num) { $ERROR[] = "移動するステージ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE state!='1' AND course_num='$m_course_num' AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_stage_num = $list['stage_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_stage_num || !$c_list_num) { $ERROR[] = "移動されるステージ情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE stage_num='$m_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE stage_num='$c_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_STAGE,$INSERT_DATA,$where);
	}

	return $ERROR;
}
?>
