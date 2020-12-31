<?PHP
/**
 * すららネット
 *
 * プラクティスステージ管理　Lesson
 *
 * 変更履歴
 *     2008.10.09  画面・フィールド（「単元名」）追加
 *     2008.10.09  SQLインジェクション対策関数追加
 *
 * @author Azet
 */

// UPD20081009_1  K.Tezuka
// UPD20081009_2  K.Tezuka


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ステージキー設定
	if ($_POST[stage_num]) {
		$sql  = "SELECT stage_key FROM ".T_STAGE.
				" WHERE stage_num='$_POST[stage_num]' AND state!='1' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$stage_key = $list['stage_key'];
		}
		if ($stage_key) { define("stage_key",$stage_key); }
	}

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

	list($html,$L_COURSE,$L_STAGE) = select_course();
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= addform($ERROR,$L_COURSE,$L_STAGE);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= lesson_list($L_COURSE,$L_STAGE); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE);
		}
	// 2012/08/28 add start oda
	} elseif (MODE == "status_mod") {
		if (ACTION == "status_check") {
			if (!$ERROR) {
				$html .= status_check_html();
			} else {
				$html .= status_viewform($ERROR);
			}
		} elseif (ACTION == "status_update") {
			if (!$ERROR) {
				$html .= lesson_list($L_COURSE,$L_STAGE);
			} else {
				$html .= status_viewform($ERROR);
			}
		} else {
			$html .= status_viewform($ERROR);
		}
	// 2012/08/28 add end oda
	} else {
		if ($_POST[course_num]&&$_POST[stage_num]) {
			$html .= lesson_list($L_COURSE,$L_STAGE);
		}
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
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "	<input type=\"hidden\" name=\"mode\" value=\"status_mod\">\n";
	$html .= "	<input type=\"hidden\" name=\"menu_status_num\" value=\"".$_POST['menu_status_num']."\">\n";
	$html .= "	<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "	<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "	<input type=\"submit\" value=\"ステータス編集\">\n";
	$html .= "</form>\n";

	// メニューステータス情報取得
	$sql  = "SELECT * FROM ".T_MENU_STATUS.
	" WHERE mk_flg = '0'".
	"   AND course_num = '".$_POST['course_num']."'".
	"   AND stage_num = '".$_POST['stage_num']."'".
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
	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='".$_POST['stage_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$stage_name = $list['stage_name'];
		}
	}

	// 画面情報取得
	if ($action) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql  = "SELECT * FROM ".T_MENU_STATUS.
		" WHERE mk_flg = '0'".
		"   AND course_num = '".$_POST['course_num']."'".
		"   AND stage_num = '".$_POST['stage_num']."'".
		"   AND lesson_num = '0'".
		"   AND unit_num = '0'";

		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				if ($list) {
					foreach ($list as $key => $val) {
						$$key = replace_decode($val);
					}
				}
			} else {
				$course_num = $_POST['course_num'];
				$stage_num = $_POST['stage_num'];
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
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"status_check\" />\n";
	$html .= "<input type=\"hidden\" name=\"menu_status_num\" value=\"".$menu_status_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\" />\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	$INPUTS['COURSENUM']       = array('result'=>'plane','value'=>$stage_num);
	$INPUTS['COURSENAME']      = array('result'=>'plane','value'=>$stage_name);
	$INPUTS['COURSENAMETITLE'] = array('result'=>'plane','value'=>"ステージ名");
	$INPUTS['STATUS1']         = array('type'=>'select','name'=>'status1','array'=>$L_STATUS_SETUP,'check'=>$status1);
	$INPUTS['STATUS1COMMENT']  = array('type'=>'text','name'=>'status1_comment','value'=>$status1_comment);
	$INPUTS['STATUS2']         = array('type'=>'select','name'=>'status2','array'=>$L_STATUS_SETUP,'check'=>$status2);
	$INPUTS['STATUS2COMMENT']  = array('type'=>'text','name'=>'status2_comment','value'=>$status2_comment);
	$INPUTS['STATUS3']         = array('type'=>'select','name'=>'status3','array'=>$L_STATUS_SETUP,'check'=>$status3);
	$INPUTS['STATUS3COMMENT']  = array('type'=>'text','name'=>'status3_comment','value'=>$status3_comment);
	$INPUTS['STATUS4']         = array('type'=>'select','name'=>'status4','array'=>$L_STATUS_SETUP,'check'=>$status4);
	$INPUTS['STATUS4COMMENT']  = array('type'=>'text','name'=>'status4_comment','value'=>$status4_comment);
	$INPUTS['STATUS5']         = array('type'=>'select','name'=>'status5','array'=>$L_STATUS_SETUP,'check'=>$status5);
	$INPUTS['STATUS5COMMENT']  = array('type'=>'text','name'=>'status5_comment','value'=>$status5_comment);
	$INPUTS['STATUS6']         = array('type'=>'select','name'=>'status6','array'=>$L_STATUS_SETUP,'check'=>$status6);
	$INPUTS['STATUS6COMMENT']  = array('type'=>'text','name'=>'status6_comment','value'=>$status6_comment);

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
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "	<input type=\"submit\" value=\"変更確認\">";
	$html .= "	<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "	<input type=\"hidden\" name=\"mode\" value=\"back_menu\" />\n";
	$html .= "	<input type=\"hidden\" name=\"menu_status_num\" value=\"".$_POST['menu_status_num']."\" />\n";
	$html .= "	<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "	<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
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

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			if ($list) {
				foreach ($list as $key => $val) {
					$$key = replace_decode($val);
				}
			}
		}
	}

	//	ステージ名
	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num = '".$stage_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$stage_name = $list['stage_name'];
	}

	$button = "更新";
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COURSENUM']       = array('result'=>'plane','value'=>$stage_num);
	$INPUTS['COURSENAME']      = array('result'=>'plane','value'=>$stage_name);
	$INPUTS['COURSENAMETITLE'] = array('result'=>'plane','value'=>"ステージ名");
	$INPUTS['STATUS1']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status1]);
	$INPUTS['STATUS1COMMENT']  = array('result'=>'plane','value'=>$status1_comment);
	$INPUTS['STATUS2']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status2]);
	$INPUTS['STATUS2COMMENT']  = array('result'=>'plane','value'=>$status2_comment);
	$INPUTS['STATUS3']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status3]);
	$INPUTS['STATUS3COMMENT']  = array('result'=>'plane','value'=>$status3_comment);
	$INPUTS['STATUS4']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status4]);
	$INPUTS['STATUS4COMMENT']  = array('result'=>'plane','value'=>$status4_comment);
	$INPUTS['STATUS5']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status5]);
	$INPUTS['STATUS5COMMENT']  = array('result'=>'plane','value'=>$status5_comment);
	$INPUTS['STATUS6']         = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status6]);
	$INPUTS['STATUS6COMMENT']  = array('result'=>'plane','value'=>$status6_comment);
	$INPUTS['DISPLAY']         = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$stage_num\" />\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\" />\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">\n";
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
	// 2012/08/28 add oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;
	$ERROR = array();

	foreach ($_POST as $key => $val) {
		if ($key == "action") {
			continue;
		}
		$INSERT_DATA[$key] = "$val";
	}

	// データ存在チェック
	$sql  = "SELECT * FROM ".T_MENU_STATUS.
	" WHERE mk_flg = '0'".
	"   AND menu_status_num = '".$_POST['menu_status_num']."'";

	if ($result = $cdb->query($sql)) {
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
	}
	return $ERROR;
}

/**
 * コース選択の為
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return array($html,$L_COURSE);
	} else {
		if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			$course_name_ = $list['course_name'];
			$L_COURSE[$course_num_] = $course_name_;
			if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			$couse_num_html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
		}
	}

	if ($_POST['course_num']) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE course_num='".$_POST['course_num']."' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$stage_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STAGE[$list['stage_num']] = $list['stage_name'];
				if ($_POST['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
				$stage_num_html .= "<option value=\"{$list['stage_num']}\" $selected>{$list['stage_name']}</option>\n";
			}
		}
	} else {
		$stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"stage_num\" onchange=\"submit();\">\n";
	$html .= $stage_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";

	if (!$_POST['course_num']) {
		$html .= "<br>\n";
		$html .= "Lessonを設定するコースを選択してください。<br>\n";
	} elseif ($_POST['course_num']&&!$_POST['stage_num']) {
		$html .= "<br>\n";
		$html .= "Lessonを設定するステージを選択してください。<br>\n";
	}

	return array($html,$L_COURSE,$L_STAGE);
}


/**
 * レッスン選択の為
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function lesson_list($L_COURSE,$L_STAGE) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= menu_status_list();		// 2012/08/28 add oda

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$html .= "【Lessonリスト設定】 <br>\n";

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"Lesson新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br>\n";
		$html .= "今現在登録されているLessonは有りません。<br>\n";
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
	$html .= "<th>Lesson名</th>\n";
	$html .= "<th>Lessonキー</th>\n";
        // --- UPD20081009_1  フィールド追加
	$html .= "<th>単元名</th>\n";
        // --- UPD20081009_1  ここまで
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
		$material_flash_path = MATERIAL_FLASH_DIR . $_POST['course_num'] . "/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/";
		if (!file_exists($material_flash_path)) {
			@mkdir($material_flash_path, 0777);
			@chmod($material_flash_path, 0777);
		}

		$material_prob_path = MATERIAL_PROB_DIR . $_POST['course_num'] ."/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/";
		if (!file_exists($material_prob_path)) {
			@mkdir($material_prob_path,0777);
			@chmod($material_prob_path,0777);
		}

		$material_voice_path = MATERIAL_VOICE_DIR . $_POST['course_num'] ."/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/";
		if (!file_exists($material_voice_path)) {
			@mkdir($material_voice_path,0777);
			@chmod($material_voice_path,0777);
		}

		$stage_num = $list['stage_num'];
		$list_num = $list['list_num'];
		$LINE[$list_num] = $stage_num;
		foreach ($list AS $KEY => $VAL) {
			$$KEY = $VAL;
		}

		$up_submit = $down_submit = "&nbsp;";
		if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
		if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

		$html .= "<tr class=\"stage_form_cell\">\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST['course_num']}\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$stage_num}\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$lesson_num}\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td>{$up_submit}</td>\n";
			$html .= "<td>{$down_submit}</td>\n";
		}
		$html .= "<td>{$lesson_num}</td>\n";
		$html .= "<td>{$L_COURSE[$_POST['course_num']]}</td>\n";
		$html .= "<td>{$L_STAGE[$stage_num]}</td>\n";
		$html .= "<td>{$lesson_name}</td>\n";
		$html .= "<td>{$lesson_key}</td>\n";


		//	add ookawara 2010/02/04
		$lesson_name2 = str_replace("&lt;","<",$lesson_name2);
		$lesson_name2 = str_replace("&gt;",">",$lesson_name2);
        // --- UPD20081009_1  単元名　追加
		$html .= "<td>{$lesson_name2}</td>\n";
        // --- UPD20081009_1
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
 * @return string HTML
 */
function addform($ERROR,$L_COURSE,$L_STAGE) {

//	global $L_DISPLAY;										// 2012/08/20 del oda
	global $L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;		// 2012/08/20 update oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='".$_POST['course_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$course_name_ = $list['course_name'];
		}
	}

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_LESSON_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>"---");
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['UNITGROUPNAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSON'] = array('type'=>'text','name'=>'lesson_name','value'=>$_POST['lesson_name']);
	$INPUTS['LESSONKEY'] = array('type'=>'text','name'=>'lesson_key','value'=>$_POST['lesson_key']);
	$stage_key = stage_key;
	$INPUTS['STAGEKEY'] = array('result'=>'plane','value'=>$stage_key);
    $INPUTS['LESSONNAME2'] = array('type'=>'text','name'=>'lesson_name2','size'=>'50','value'=>$_POST['lesson_name2']);	//	add ookawara 2010/02/04

    // 2012/08/20 add start oda
    // 上位で設定されている場合は、入力フィールドを設定しない
    $upper_check = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    if (strlen($upper_check) == 0) {
	    $INPUTS['SURALAUNIT'] = array('type'=>'text','name'=>'surala_unit','size'=>'40','value'=>$_POST['surala_unit']);
	    $surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
	    $surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	    $INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } else {
	    $INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','size'=>'40','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
	    $INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    }

	// del start oda 2017/05/15 未使用箇所削除
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("standard_study_time");
	// $newform->set_form_id("display1");
	// $newform->set_form_check($_POST['standard_study_time']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("standard_study_time");
	// $newform->set_form_id("undisplay1");
	// $newform->set_form_check($_POST['standard_study_time']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display1\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay1\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[STANDARDSTUDYTIME] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("study_time");
	// $newform->set_form_id("display2");
	// $newform->set_form_check($_POST['study_time']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("study_time");
	// $newform->set_form_id("undisplay2");
	// $newform->set_form_check($_POST['study_time']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display2\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay2\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[STUDYTIME] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("remainder_unit");
	// $newform->set_form_id("display3");
	// $newform->set_form_check($_POST['remainder_unit']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("remainder_unit");
	// $newform->set_form_id("undisplay3");
	// $newform->set_form_check($_POST['remainder_unit']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display3\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay3\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[REMAINDERUNIT] = array('result'=>'plane','value'=>$onoff);
    // 2012/08/20 add end oda
	// del end oda 2017/05/15

	//upd start hirose 2018/11/28 すらら英単語
//	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語
	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$_POST['remarks']);
	//upd end hirose 2018/11/28 すらら英単語

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
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"lesson_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 表示の為
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function viewform($ERROR,$L_COURSE,$L_STAGE) {

//	global $L_DISPLAY;										// 2012/08/20 del oda
	global $L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;		// 2012/08/20 update oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='".$_POST['lesson_num']."' AND state!='1' LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。<br>$sql";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		}

		// del start oda 2017/05/15 未使用箇所削除
		// // 2012/08/20 add start oda
		// $standard_study_time = "2";
		// $study_time = "2";
		// $remainder_unit = "2";

		// if ($status_display) {
		// 	$dispray_list = explode("<>",$status_display);
		// 	for ($j=0; $j<count($dispray_list); $j++) {
		// 		if ($dispray_list[$j] == "standard_study_time") {
		// 			$standard_study_time = "1";
		// 		}
		// 		if ($dispray_list[$j] == "study_time") {
		// 			$study_time = "1";
		// 		}
		// 		if ($dispray_list[$j] == "remainder_unit") {
		// 			$remainder_unit = "1";
		// 		}
		// 	}
		// }
		// del end oda 2017/05/15

		$status_display = "";

    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
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

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$lesson_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_LESSON_FORM);

	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>$lesson_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['UNITGROUPNAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSON'] = array('type'=>'text','name'=>'lesson_name','value'=>$lesson_name);
	$stage_key = stage_key;
	$lesson_key = ereg_replace("^$stage_key","",$lesson_key);
	$INPUTS['LESSONKEY'] = array('type'=>'text','name'=>'lesson_key','value'=>$lesson_key);
	$INPUTS['STAGEKEY'] = array('result'=>'plane','value'=>$stage_key);

	//	add ookawara 2010/02/04
	$lesson_name2 = str_replace("&lt;","<",$lesson_name2);
	$lesson_name2 = str_replace("&gt;",">",$lesson_name2);
    $INPUTS['LESSONNAME2'] = array('type'=>'text','name'=>'lesson_name2','size'=>'50','value'=>$lesson_name2);

    // 2012/08/20 add start oda
    // 上位で設定されている場合は、入力フィールドを設定しない
    $upper_check = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    if (strlen($upper_check) == 0) {
	    $INPUTS['SURALAUNIT'] = array('type'=>'text','name'=>'surala_unit','size'=>'40','value'=>$surala_unit);
	    $surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
	    $surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	    $INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } else {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','size'=>'40','value'=>'');
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    }

	// del start oda 2017/05/15 未使用箇所削除
    // $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("standard_study_time");
	// $newform->set_form_id("display1");
	// $newform->set_form_check($standard_study_time);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("standard_study_time");
	// $newform->set_form_id("undisplay1");
	// $newform->set_form_check($standard_study_time);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display1\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay1\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[STANDARDSTUDYTIME] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("study_time");
	// $newform->set_form_id("display2");
	// $newform->set_form_check($study_time);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("study_time");
	// $newform->set_form_id("undisplay2");
	// $newform->set_form_check($study_time);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display2\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay2\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[STUDYTIME] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("remainder_unit");
	// $newform->set_form_id("display3");
	// $newform->set_form_check($remainder_unit);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("remainder_unit");
	// $newform->set_form_id("undisplay3");
	// $newform->set_form_check($remainder_unit);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display3\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay3\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS[REMAINDERUNIT] = array('result'=>'plane','value'=>$onoff);
    // 2012/08/20 add end oda
	// del end oda 2017/05/15
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
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
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

	if (!$_POST['course_num']) { $ERROR[] = "登録するLessonのコース情報が確認できません。"; }
	if (!$_POST['stage_num']) { $ERROR[] = "登録するLessonのステージ情報が確認できません。"; }

	if (!$_POST['lesson_name']) { $ERROR[] = "Lesson名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_LESSON.
					" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' AND lesson_name='".$_POST['lesson_name']."'";
		} else {
			$sql  = "SELECT * FROM ".T_LESSON.
					" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' AND lesson_num!='".$_POST['lesson_num']."'" .
					" AND lesson_name='".$_POST['lesson_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
//		if ($count > 0) { $ERROR[] = "入力されたステージ名は既に登録されております。"; }	//	del ookawara 2010/02/04
		if ($count > 0) { $ERROR[] = "入力されたLesson名は既に登録されております。"; }	//	add ookawara 2010/02/04
	}
	$stage_key = stage_key;
	$_POST['lesson_key'] = mb_convert_kana($_POST['lesson_key'],"as","UTF-8");
	$_POST['lesson_key'] = trim($_POST['lesson_key']);
	if (!$_POST['lesson_key']) { $ERROR[] = "Lessonキーが未入力です。"; }
	else {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_LESSON.
					" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' AND lesson_key='".$stage_key.$_POST['lesson_key']."'";
		} else {
			$sql  = "SELECT * FROM ".T_LESSON.
					" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' AND lesson_num!='".$_POST['lesson_num']."'" .
					" AND lesson_key='".$stage_key.$_POST['lesson_key']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたLessonキーは既に登録されております。"; }
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

	//if (!$_POST['standard_study_time']) { $ERROR[] = "標準学習時間表示が未選択です。"; }		// 2012/08/20 add oda	// del oda 2017/05/15 未使用箇所削除
	//if (!$_POST['study_time']) { $ERROR[] = "学習時間表示が未選択です。"; }					// 2012/08/20 add oda	// del oda 2017/05/15 未使用箇所削除
	//if (!$_POST['remainder_unit']) { $ERROR[] = "残りユニット数表示が未選択です。"; }			// 2012/08/20 add oda	// del oda 2017/05/15 未使用箇所削除
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}


/**
 * 確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @return string HTML
 */
function check_html($L_COURSE,$L_STAGE) {

//	global $L_DISPLAY;									// 2012/08/20 del oda
	global $L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;	// 2012/08/20 update oda

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
		$sql = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='".$_POST['lesson_num']."' AND state!='1' LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		}

		// del start oda 2017/05/15 未使用箇所削除
		// // 2012/08/20 add start oda
		// $standard_study_time = "2";
		// $study_time = "2";
		// $remainder_unit = "2";
		// if ($status_display) {
		// 	$dispray_list = explode("<>",$status_display);
		// 	for ($j=0; $j<count($dispray_list); $j++) {
		// 		if ($dispray_list[$j] == "standard_study_time") {
		// 			$standard_study_time = "1";
		// 		}
		// 		if ($dispray_list[$j] == "study_time") {
		// 			$study_time = "1";
		// 		}
		// 		if ($dispray_list[$j] == "remainder_unit") {
		// 			$remainder_unit = "1";
		// 		}
		// 	}
		// }
		// del end oda 2017/05/15 未使用箇所削除

		$status_display = "";

    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
    	$surala_unit = change_unit_num_to_key_all($surala_unit);				// 2012/09/27 add oda
		// 2012/08/20 add end oda
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_LESSON_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$lesson_num) { $lesson_num = "---"; }
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>$lesson_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['UNITGROUPNAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSON'] = array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['LESSONKEY'] = array('result'=>'plane','value'=>$lesson_key);
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$stage_key = stage_key;
	$INPUTS['STAGEKEY'] = array('result'=>'plane','value'=>$stage_key);
	//	add ookawara 2010/02/04
	$lesson_name2 = str_replace("&lt;","<",$lesson_name2);
	$lesson_name2 = str_replace("&gt;",">",$lesson_name2);
	$INPUTS['LESSONNAME2'] = array('result'=>'plane','value'=>$lesson_name2);

	// 2012/08/20 add start oda
	$INPUTS['SURALAUNIT'] = array('result'=>'plane','value'=>$surala_unit);
	$INPUTS['REMARKS']  = array('result'=>'plane', 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語

	//$INPUTS[STANDARDSTUDYTIME] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$standard_study_time]);		// del oda 2017/05/15 未使用箇所削除
	//$INPUTS[STUDYTIME] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$study_time]);						// del oda 2017/05/15 未使用箇所削除
	//$INPUTS[REMAINDERUNIT] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$remainder_unit]);				// del oda 2017/05/15 未使用箇所削除
	// 2012/08/20 add end oda

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$stage_num\">\n";
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
		elseif ($key == "lesson_key") { $val = stage_key.$val; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['update_time'] = "now()";

	// 2012/08/20 add start oda
	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($INSERT_DATA['surala_unit']);			// 2012/09/27 add oda
//	$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], 0, 0, 0, 0, $INSERT_DATA['surala_unit']);	// 2012/09/27 del oda
	unset($INSERT_DATA['surala_unit']);

	// updatel start oda 2017/05/15 未使用箇所削除
	// //　ステータス表示区分の編集
	// $status_display = "";
	// if ($INSERT_DATA['standard_study_time'] == "1") {
	// 	$status_display .= "standard_study_time";
	// }
	// if ($INSERT_DATA['study_time'] == "1") {
	// 	if ($status_display) {
	// 		$status_display .= "<>";
	// 	}
	// 	$status_display .= "study_time";
	// }
	// if ($INSERT_DATA['remainder_unit'] == "1") {
	// 	if ($status_display) {
	// 		$status_display .= "<>";
	// 	}
	// 	$status_display .= "remainder_unit";
	// }
	// $INSERT_DATA['status_display'] = $status_display;

	// unset($INSERT_DATA['standard_study_time']);
	// unset($INSERT_DATA['study_time']);
	// unset($INSERT_DATA['remainder_unit']);
	// 2012/08/20 add end oda

	$status_display = "";
	$INSERT_DATA['status_display'] = $status_display;
	// update end oda 2017/05/15

	$ERROR = $cdb->insert(T_LESSON,$INSERT_DATA);

	if (!$ERROR) {
		$lesson_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $lesson_num;
		$where = " WHERE lesson_num='$lesson_num' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);

		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $lesson_num, 0, 0, 0, 0, $surala_unit);		// 2012/09/27 update oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

// --- UPD20081009_2
/**
 * SQLインジェクション対策
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
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
		$sql  = "SELECT lesson_key FROM ".T_LESSON.
				" WHERE  lesson_num='".$_POST['lesson_num']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$check_lesson_key = $list['lesson_key'];
			}
		}

		$INSERT_DATA['lesson_name'] = $_POST['lesson_name'];
		$lesson_key = stage_key.$_POST['lesson_key'];
		$INSERT_DATA['lesson_key'] = $lesson_key;
        // --- UPD20081009_1  単元名　追加
        $INSERT_DATA['lesson_name2'] = sqlGuard( $_POST['lesson_name2'] );
        // --- UPD20081009_1 ここまで
		$INSERT_DATA['display'] = $_POST['display'];
		$INSERT_DATA['update_time'] = "now()";
		$INSERT_DATA['remarks'] = $_POST['remarks']; //add kimura 2018/10/31 すらら英単語

		// 2012/08/20 add start oda
		// すららで復習ユニット登録
		$surala_unit = change_unit_key_to_num_all($_POST['surala_unit']);			// 2012/09/27 add oda
//		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0, $_POST['surala_unit']);
		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0, $surala_unit);		// 2012/09/27 add oda
		unset($INSERT_DATA['surala_unit']);

		// updatel start oda 2017/05/15 未使用箇所削除
		// // すららで復習ユニット登録
		// $status_display = "";
		// if ($_POST['standard_study_time'] == "1") {
		// 	$status_display .= "standard_study_time";
		// }
		// if ($_POST['study_time'] == "1") {
		// 	if ($status_display) { $status_display .= "<>"; }
		// 	$status_display .= "study_time";
		// }
		// if ($_POST['remainder_unit'] == "1") {
		// 	if ($status_display) { $status_display .= "<>"; }
		// 	$status_display .= "remainder_unit";
		// }
		// $INSERT_DATA['status_display'] = $status_display;

		// unset($INSERT_DATA['standard_study_time']);
		// unset($INSERT_DATA['study_time']);
		// unset($INSERT_DATA['remainder_unit']);
		// // 2012/08/20 add end oda

		$status_display = "";
		$INSERT_DATA['status_display'] = $status_display;
		// update end oda 2017/05/15

		$where = " WHERE lesson_num='".$_POST['lesson_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);

		unset($INSERT_DATA);
		if ($check_lesson_key != $lesson_key) {
			$sql  = "SELECT unit_num, unit_key FROM ".T_UNIT.
					" WHERE lesson_num='".$_POST['lesson_num']."' AND unit_key!='';";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$unit_num = $list['unit_num'];
					$unit_key = $list['unit_key'];
					$unit_key = ereg_replace("^$check_lesson_key","$lesson_key",$unit_key);

					$INSERT_DATA['unit_key'] = $unit_key;
					$INSERT_DATA['update_time'] = "now()";
					$where = " WHERE unit_num='$unit_num' LIMIT 1;";

					$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);

				}
			}
		}
	} elseif (MODE == "削除") {
		$INSERT_DATA['display'] = 2;
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE lesson_num='".$_POST['lesson_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);

		// 2012/08/22 add start oda
		// すららで復習ユニット登録
		$ERROR = delete_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
		// 2012/08/22 add end oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * 順番変更（上へ移動）
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

	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='".$_POST['lesson_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_lesson_num = $list['lesson_num'];
			$m_stage_num = $list['stage_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_lesson_num || !$m_list_num) { $ERROR[] = "移動するLesson情報が取得できません。"; }
	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE state!='1' AND stage_num='$m_stage_num' AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_lesson_num = $list['lesson_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_lesson_num || !$c_list_num) { $ERROR[] = "移動されるステージ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE lesson_num='$m_lesson_num' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE lesson_num='$c_lesson_num' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * 順番変更（下へ移動）
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

	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='".$_POST['lesson_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_lesson_num = $list['lesson_num'];
			$m_stage_num = $list['stage_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_stage_num || !$m_list_num) { $ERROR[] = "移動するLesson情報が取得できません。"; }
	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE state!='1' AND stage_num='$m_stage_num' AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_lesson_num = $list['lesson_num'];
				$c_list_num = $list['list_num'];
			}
		}
	}
	if (!$c_lesson_num || !$c_list_num) { $ERROR[] = "移動されるLesson情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE lesson_num='$m_lesson_num' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE lesson_num='$c_lesson_num' LIMIT 1;";

		$ERROR = $cdb->update(T_LESSON,$INSERT_DATA,$where);
	}
	return $ERROR;
}
?>
