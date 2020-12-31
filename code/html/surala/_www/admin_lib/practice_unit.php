<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　ユニット作成
 *
 * 履歴
 *   2008.10.09 画面・フィールド（「単元名」）追加
 *   2008.10.09 SQLインジェクション対策関数追加
 *   2009/05/25 メール送信用ユニット名unit_name3追加	Ozaki
 *   2012/09/26 ワンポイント解説の情報メンテナンス追加  oda
 *
 * @author Azet
 */

// 2008.10.09 画面・フィールド（「単元名」）追加		UPD20081009_1  K.Tezuka
// 2008.10.09 SQLインジェクション対策関数追加U			PD20081009_2  K.Tezuka
// 2009/05/25 メール送信用ユニット名unit_name3追加		Ozaki
// 2012/09/26 ワンポイント解説の情報メンテナンス追加	Oda

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
    // kaopiz 2020/08/20 speech start
    $GLOBALS['speechEvaluation'] = false;
    $ENGLISH_COURSE = 1;
    // kaopiz 2020/08/20 speech end

	//	ステージキー設定
	if ($_POST['lesson_num']) {
        $sql  = "SELECT lesson_key FROM ".T_LESSON.
            " WHERE lesson_num='".$_POST['lesson_num']."' AND state!='1' LIMIT 1;";
        if ($result = $cdb->query($sql)) {
            $list = $cdb->fetch_assoc($result);
            $lesson_key = $list['lesson_key'];
        }
        if ($lesson_key) { define("lesson_key",$lesson_key); }
    }
//    kaopiz 2020/08/20 speech start
    $course = getCourseByNum($_POST['course_num']);
    if ($course && ($course['write_type'] == $ENGLISH_COURSE)) { // kaopiz 2020/09/15 speech
        $GLOBALS['speechEvaluation'] = true;
    }
//    kaopiz 2020/08/20 speech end

	// チェック処理
	if (ACTION == "check") {
		$ERROR = check();
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	} elseif (ACTION == "block_check" || ACTION == "ドリル追加確認" || ACTION == "ドリル変更確認") {
		$ERROR = block_check();
	} elseif (ACTION == "確認") {
		$ERROR = review_check();
	} elseif (ACTION == "review_check" || ACTION == "スキル追加確認" || ACTION == "スキル変更確認") {
		$ERROR = review_check();
	} elseif (ACTION == "one_point_check" || ACTION == "ワンポイント解説追加確認" || ACTION == "ワンポイント解説変更確認") {		// 2012/09/26 add oda
		$ERROR = one_point_check();																						// 2012/09/26 add oda
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
		elseif (ACTION == "review_add") { list($ERROR,$msg) = review_add(); }
		elseif (ACTION == "review_change") { list($ERROR,$msg) = review_change(); }
		elseif (ACTION == "block_add") { $ERROR = block_add(); }
		elseif (ACTION == "block_change") { $ERROR = block_change(); }
		elseif (ACTION == "block_up") { $ERROR = block_up(); }
		elseif (ACTION == "block_down") { $ERROR = block_down(); }
		elseif (ACTION == "status_update") { $ERROR = status_update(); }			// 2012/08/28 add oda
		elseif (ACTION == "one_point_add") { $ERROR = one_point_add(); }			// 2012/09/26 add oda
		elseif (ACTION == "one_point_change") { $ERROR = one_point_change(); }		// 2012/09/26 add oda
		elseif (ACTION == "one_point_up") { $ERROR = one_point_up(); }				// 2012/09/26 add oda
		elseif (ACTION == "one_point_down") { $ERROR = one_point_down(); }			// 2012/09/26 add oda
	}

	list($html,$L_COURSE,$L_STAGE,$L_LESSON) = select_course();
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE,$L_LESSON);
		}
	// ドリル新規登録フォーム / ドリル変更登録フォーム / 確認画面 		// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	} elseif (MODE == "block_form") {

		if (ACTION == "block_check" || ACTION == "ドリル追加確認" || ACTION == "ドリル変更確認") {
			if (!$ERROR) { $html .= block_check_html($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= block_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "block_add") {
			if (!$ERROR) { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= block_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "block_change") {
			if (!$ERROR) { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= block_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= block_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		}
	// 削除確認画面
	} elseif (MODE == "block_delete") {
		if (ACTION == "block_change") {
			if (!$ERROR) { $html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= block_check_html($L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= block_check_html($L_COURSE,$L_STAGE,$L_LESSON);
		}

	// 2012/09/26 add start oda
	} elseif (MODE == "one_point_form") {
		if (ACTION == "one_point_check" || ACTION == "ワンポイント解説追加確認" || ACTION == "ワンポイント解説変更確認") {
			if (!$ERROR) {
				$html .= one_point_check_html($L_COURSE,$L_STAGE,$L_LESSON);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			}
		} elseif (ACTION == "one_point_add") {
			if (!$ERROR) {
				$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			}
		} elseif (ACTION == "one_point_change") {
			if (!$ERROR) {
				$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			}
		} else {
			$html .= one_point_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		}
	} elseif (MODE == "one_point_delete") {
		if (ACTION == "one_point_change") {
			if (!$ERROR) {
				$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
			} else {
				$html .= one_point_check_html($L_COURSE,$L_STAGE,$L_LESSON);
			}
		}elseif (ACTION == "back") {
			$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		} else {
			$html .= one_point_check_html($L_COURSE,$L_STAGE,$L_LESSON);
		}
	} elseif (MODE == "one_point_upload") {
		$ERROR = one_point_upload();
		$html .= viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
	// 2012/09/26 add end oda
	} elseif (MODE == "復習設定" || MODE == "review") {
		$html .= review_list_html($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
	} elseif (MODE == "review_form") {
		if (ACTION == "review_check" || ACTION == "スキル追加確認" || ACTION == "スキル変更確認") {
			if (!$ERROR) { $html .= review_check_html($L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= review_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "review_addform") {
			if (!$ERROR) { $html .= review_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= review_list_html($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} elseif (ACTION == "review_add") {
			if (!$ERROR) { $html .= review_list_html($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
			else { $html .= review_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON); }
		} else {
			$html .= review_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);
		}
	} elseif (MODE == "review_delete") {
		if (ACTION == "review_change") {	//	add ookawara 2012/06/05
			$html .= review_list_html($ERROR,$L_COURSE,$L_STAGE,$L_LESSON);	//	add ookawara 2012/06/05
		} else {	//	add ookawara 2012/06/05
			$html .= review_check_html($L_COURSE,$L_STAGE,$L_LESSON);
		}	//	add ookawara 2012/06/05
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
				$html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON);
			} else {
				$html .= status_viewform($ERROR);
			}
		} else {
			$html .= status_viewform($ERROR);
		}
	// 2012/08/28 add end oda
	} else {
		if ($_POST[course_num]&&$_POST[stage_num]&&$_POST[lesson_num]) {
			$html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON);
		}
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['course_num'] && $_POST['b_course_num'] && $_POST['b_course_num'] != $_POST['course_num']) { unset($_POST['stage_num']); }

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

	if ($_POST['stage_num']) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE stage_num='".$_POST['stage_num']."' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$lesson_num_html .= "<option value=\"\">ステージ内にLessonがありません。</option>\n";
		} else {
			if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_LESSON[$list['lesson_num']] = $list['lesson_name'];
				if ($_POST['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
				$lesson_num_html .= "<option value=\"{$list['lesson_num']}\" $selected>{$list['lesson_name']}</option>\n";
			}
		}
	} else {
		$lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "<td>Lesson</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"stage_num\" onchange=\"submit_stage();\">\n";
	$html .= $stage_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit();\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";

	if (!$_POST['course_num']) {
		$html .= "<br>\n";
		$html .= "ユニットを設定するコースを選択してください。<br>\n";
	} elseif ($_POST['course_num']&&!$_POST['stage_num']) {
		$html .= "<br>\n";
		$html .= "ユニットを設定するステージを選択してください。<br>\n";
	} elseif ($_POST['course_num']&&$_POST['stage_num']&&!$_POST['lesson_num']) {
		$html .= "<br>\n";
		$html .= "ユニットを設定するLessonを選択してください。<br>\n";
	}

	return array($html,$L_COURSE,$L_STAGE,$L_LESSON);
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
	$html .= "	<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "	<input type=\"submit\" value=\"ステータス編集\">\n";
	$html .= "</form>\n";

	// メニューステータス情報取得
	$sql  = "SELECT * FROM ".T_MENU_STATUS.
			" WHERE mk_flg = '0'".
			"   AND course_num = '".$_POST['course_num']."'".
			"   AND stage_num = '".$_POST['stage_num']."'".
			"   AND lesson_num = '".$_POST['lesson_num']."'".
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

	global $L_DISPLAY, $L_STATUS_SETUP;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//	レッスン名
	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='".$_POST['lesson_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$lesson_name = $list['lesson_name'];
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
				"   AND lesson_num = '".$_POST['lesson_num']."'".
				"   AND unit_num = '0'";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		} else {
			$course_num = $_POST['course_num'];
			$stage_num = $_POST['stage_num'];
			$lesson_num = $_POST['lesson_num'];
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

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"status_check\" />\n";
	$html .= "<input type=\"hidden\" name=\"menu_status_num\" value=\"".$menu_status_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\" />\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	$INPUTS['COURSENUM']       = array('result'=>'plane','value'=>$lesson_num);
	$INPUTS['COURSENAME']      = array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['COURSENAMETITLE'] = array('result'=>'plane','value'=>"レッスン名");
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
	$html .= "	<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "	<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

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

	//	レッスン名
	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num = '".$lesson_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$lesson_name = $list['lesson_name'];
	}

	$button = "更新";
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MENU_STATUS_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COURSENUM']      = array('result'=>'plane','value'=>$lesson_num);
	$INPUTS['COURSENAME']     = array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['COURSENAMETITLE'] = array('result'=>'plane','value'=>"レッスン名");
	$INPUTS['STATUS1']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status1]);
	$INPUTS['STATUS1COMMENT'] = array('result'=>'plane','value'=>$status1_comment);
	$INPUTS['STATUS2']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status2]);
	$INPUTS['STATUS2COMMENT'] = array('result'=>'plane','value'=>$status2_comment);
	$INPUTS['STATUS3']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status3]);
	$INPUTS['STATUS3COMMENT'] = array('result'=>'plane','value'=>$status3_comment);
	$INPUTS['STATUS4']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status4]);
	$INPUTS['STATUS4COMMENT'] = array('result'=>'plane','value'=>$status4_comment);
	$INPUTS['STATUS5']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status5]);
	$INPUTS['STATUS5COMMENT'] = array('result'=>'plane','value'=>$status5_comment);
	$INPUTS['STATUS6']        = array('result'=>'plane','value'=>$L_STATUS_SETUP[$status6]);
	$INPUTS['STATUS6COMMENT'] = array('result'=>'plane','value'=>$status6_comment);
	$INPUTS['DISPLAY']        = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\" />\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\" />\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\" />\n";
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
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

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
		if ($key == "action") {
			continue;
		}
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
 * ユニット選択一覧を作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function unit_list($L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY,$L_ONOFF_TYPE;
	global $L_ORDER_TYPE; // add 2018/05/24 yoshizawa 理科社会対応

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= menu_status_list();		// 2012/08/28 add oda

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$html .= "【ユニットリスト設定】<br>\n";

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"ユニット新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br>\n";
	}
	if ($_POST['stage_num']) {
		$where = "";
	}

	// add start 2018/05/21 yoshizawa 理科社会対応
	// レッスンに紐づくドリル情報を取得
	$block_num_list = "";
	$block_num_list = get_block_num_list($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], "");

	$L_SKILL_INFO = array();
	$L_PROBLEM_COUNT_INFO = array();
	if($block_num_list){
		// レッスンに紐づくドリル毎のスキル情報を事前に取得
		$L_SKILL_INFO = get_skill_info($_POST['course_num'], $block_num_list);

		// レッスンに紐づくドリルの問題数を取得
		$L_PROBLEM_COUNT_INFO = get_problem_count_info($block_num_list);

	}
	// add end 2018/05/21 yoshizawa 理科社会対応

	$sql  = "SELECT * FROM ".T_COURSE." a, ".T_STAGE." b, ".T_LESSON." c, ".T_UNIT." d" .
			" WHERE a.course_num=b.course_num AND b.stage_num=c.stage_num AND c.lesson_num=d.lesson_num" .
			" AND d.course_num='".$_POST['course_num']."' AND a.state!='1'" .
			" AND d.stage_num='".$_POST['stage_num']."' AND b.state!='1'" .
			" AND d.lesson_num='".$_POST['lesson_num']."' AND c.state!='1'" .
			" AND d.state!='1' ORDER BY d.list_num;";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているユニットは有りません。<br>\n";
			return $html;
		}
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th rowspan=\"2\">↑</th>\n";
			$html .= "<th rowspan=\"2\">↓</th>\n";
		}
		$html .= "<th rowspan=\"2\">登録番号</th>\n";
		$html .= "<th rowspan=\"2\">コース名</th>\n";
		$html .= "<th rowspan=\"2\">ユニット名</th>\n";
		$html .= "<th rowspan=\"2\">ユニットキー</th>\n";
        // --- UPD20081009_1  フィールド追加	change ookawara 2009/05/18
        $html .= "<th>単元名</th>\n";
        // --- UPD20081009_1  ここまで
//		$html .= "<th rowspan=\"2\">クリアーユニットキー</th>\n";

		$html .= "<th rowspan=\"2\">レクチャーを促す<br>学習時間</th>\n"; // add 2016/01/07 yoshizawa レクチャー閲覧を促す機能
		$html .= "<th rowspan=\"2\">レクチャーを促す<br>連続不正解回数</th>\n"; // add 2016/01/15 yoshizawa レクチャー閲覧を促す機能
		$html .= "<th rowspan=\"2\">表示・非表示</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th rowspan=\"2\">詳細</th>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th rowspan=\"2\">削除</th>\n";
		}

		// >>> del 2018/10/09 yoshizawa すらら英単語追加 未使用なのでコメントにします。
		// // 2012/10/02 add start oda
		// // 表示タイプ取得
		// $view_type = get_data_view_type($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], "");
		// // 2012/10/02 add end oda
		// <<<

		// update start 2018/05/15 yoshizawa 理科社会対応
		// // 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		// $html .= "<th colspan=\"6\">ドリル</th>\n"; // update 8→6 2016/01/15 yoshizawa レクチャー閲覧を促す機能
		// $html .= "</tr>\n";
		// $html .= "<tr class=\"course_form_menu\">\n";
		// $html .= "<th>メール用単元名</th>\n";	//	add ookawara 2009/05/18
		// $html .= "<th>学習タイプ</th>\n";
		// 2012/10/02 update start oda
		// $html .= "<th>基本正解率</th>\n";
		// $html .= "<th>最低学習問題数</th>\n";
		// $html .= "<th>ドリルクリアー正解率</th>\n";
		// $html .= "<th>難度利用タイプ</th>\n";
		// if ($view_type == "0") {
		// 	$html .= "<th>基本正解率</th>\n";
		// 	$html .= "<th>最低学習問題数</th>\n";
		// 	$html .= "<th>ドリルクリアー正解率</th>\n";
		// 	$html .= "<th>難度利用タイプ</th>\n";
		// } elseif ($view_type == "1") {
		// 	$html .= "<th>BGM</th>\n";
		// 	$html .= "<th>カウントダウン／タイマー表示</th>\n"; // update 2017/04/21 yoshizawa タイトル変更
		// 	$html .= "<th>すららで復習</th>\n";
		// 	$html .= "<th>ドリルクリア時間</th>\n";
		// }
		// // 2012/10/02 update end oda
		// $html .= "<th>表示・非表示</th>\n";
		// $html .= "</tr>\n";
		//
		// 表を作成開始
		$html .= "<th colspan=\"4\">ドリル</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>メール用単元名</th>\n";
		$html .= "<th>学習タイプ</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th>ドリル詳細</th>\n";
		$html .= "<th>スキル詳細</th>\n";
		$html .= "</tr>\n";
		// update end 2018/05/15 yoshizawa

		while ($list = $cdb->fetch_assoc($result)) {
			$material_flash_path = MATERIAL_FLASH_DIR . $list['course_num'] . "/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/" . $list['unit_num'] . "/";
			if (!file_exists($material_flash_path)) {
				@mkdir($material_flash_path, 0777);
				@chmod($material_flash_path, 0777);
			}

			$material_prob_path = MATERIAL_PROB_DIR . $list['course_num'] ."/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/" . $list['unit_num'] . "/";
			if (!file_exists($material_prob_path)) {
				@mkdir($material_prob_path,0777);
				@chmod($material_prob_path,0777);
			}

			$material_voice_path = MATERIAL_VOICE_DIR . $list['course_num'] ."/" . $list['stage_num'] . "/" . $list['lesson_num'] . "/" . $list['unit_num'] . "/";
			if (!file_exists($material_voice_path)) {
				@mkdir($material_voice_path,0777);
				@chmod($material_voice_path,0777);
			}

			$unit_num_ = $list['unit_num'];
			$list_num_ = $list['list_num'];
			$LINE[$list_num_] = $unit_num_;
			foreach ($list AS $KEY => $VAL) {
				$LIST[$unit_num_][$KEY] = $VAL;
			}
		}
		$i = 1;
		if ($LINE) {
			foreach ($LINE AS $VAL) {
				$up_submit = $down_submit = "&nbsp;";
				if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
				foreach ($LIST[$VAL] AS $key => $val) {
					$ca_name = $key . "_";
					$$ca_name = $val;
				}
				if ($parents_unit_num_ > 0) {
					$parents_unit_name = $LIST[$parents_unit_num_]['unit_name'];
				} else {
					$parents_unit_name = "親ユニット";
				}

				$sql = "SELECT * FROM " . T_BLOCK .
					" WHERE unit_num='$unit_num_' AND state!='1' ORDER BY list_num";
				unset($L_BLOCK);
				if ($result = $cdb->query($sql)) {
					$block_count = $cdb->num_rows($result);
					if ($block_count > 1) { $block_rows = " rowspan=\"$block_count\""; }
					else { $block_rows = ""; }
					while ($list = $cdb->fetch_assoc($result)) {
						$L_BLOCK[$list['block_num']] = $list;
					}
				}

				if ($clear_unit_key_) {
					$clear_unit_key_ = ereg_replace("^:|:$","",$clear_unit_key_);
				}

				// $html .= "<tr class=\"course_form_cell\">\n";						// del 2018/05/15 yoshizawa 理科社会対応
				$html .= "<tr class=\"course_form_cell\" style=\"font-size:14px;\">\n";	// add 2018/05/15 yoshizawa 理科社会対応
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST['course_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$stage_num_}\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$_POST['lesson_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$unit_num_}\">\n";
				if (!ereg("practice__view",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<td$block_rows>{$up_submit}</td>\n";
					$html .= "<td$block_rows>{$down_submit}</td>\n";
				}
				$html .= "<td$block_rows>{$unit_num_}</td>\n";
				$html .= "<td$block_rows>{$course_name_}</td>\n";
				$html .= "<td$block_rows>{$unit_name_}</td>\n";
				$html .= "<td$block_rows>{$unit_key_}</td>\n";
				//	add ookawara 2010/02/04
				$unit_name2_ = ereg_replace("&lt;","<",$unit_name2_);
				$unit_name2_ = ereg_replace("&gt;",">",$unit_name2_);
				// --- UPD20081009_1  単元名　追加	change ookawara 2009/05/18
				if (!$unit_name3_) { $unit_name3_ = "未設定"; }
				$html .= "<td$block_rows>{$unit_name2_}<hr>{$unit_name3_}</td>\n";
				$html .= "<td$block_rows>{$again_lecture_time_}秒</td>\n"; // add 2016/01/07 yoshizawa レクチャー閲覧を促す機能
				$html .= "<td$block_rows>{$again_lecture_count_}回</td>\n"; // add 2016/01/15 yoshizawa レクチャー閲覧を促す機能
				// --- UPD20081009_1
				//$html .= "<td$block_rows>{$clear_unit_key_}</td>\n";
				$display_ = $LIST[$unit_num_][display];
				$html .= "<td$block_rows>{$L_DISPLAY[$display_]}</td>\n";
				if (!ereg("practice__view",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<td$block_rows><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
				}
				if (!ereg("practice__del",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<td$block_rows><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
				}
				$html .= "</form>\n";

				if ($block_count) {
					$ii = 0;
					foreach ($L_BLOCK as $key => $block_line) {
						// update start 2018/05/15 yoshizawa 理科社会対応
						// if ($ii != 0) {
						// 	$html .= "</tr>\n";
						// 	$html .= "<tr class=\"course_form_cell\">\n";
						// }
						// if ($view_type == "0") {								// 2012/10/02 add oda
						// 	if ($block_line['block_type'] != 1) {
						// 		$block_line['rate'] = "--";
						// 		$block_line['lowest_study_number'] = "--";
						// 		$block_line['clear_rate'] = "--";
						// 		$block_line_difficulty_type = "--";
						// 	} else {
						// 		$block_line_difficulty_type = $L_DIFFICULTY_TYPE[$block_line['difficulty_type']];
						// 	}
						// 	$html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
						// 	$html .= "<td>{$block_line['rate']}%</td>\n";
						// 	$html .= "<td>{$block_line['lowest_study_number']}問</td>\n";
						// 	$html .= "<td>{$block_line['clear_rate']}%</td>\n";
						// 	$html .= "<td>{$block_line_difficulty_type}</td>\n";
						// 	$html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
						// // 2012/10/02 add start oda
						// } elseif ($view_type == "1") {
						// 	$surala_unit = get_surala_unit($_POST['course_num'], $stage_num_, $_POST['lesson_num'], $unit_num_, $block_line['block_num'], 0, 0);
						// 	$surala_unit = change_unit_num_to_key_all($surala_unit);
						// 	$html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
						// 	$html .= "<td>{$L_ONOFF_TYPE[$block_line['bgm_set']]}</td>\n";
						// 	$html .= "<td>{$L_ONOFF_TYPE[$block_line['count_down_set']]}</td>\n";
						// 	$html .= "<td>{$surala_unit}</td>\n";
						// 	$html .= "<td>{$block_line['clear_time']}</td>\n";
						// 	$html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
						// }
						// // 2012/10/02 add end oda
						//
						if ($ii != 0) {
							$html .= "</tr>\n";
							$html .= "<tr class=\"course_form_cell\" style=\"font-size:14px;\">\n";
						}
						// 通常ドリル／診断A／診断Bの場合
						if ( $block_line['block_type'] == "1" || $block_line['block_type'] == "2" || $block_line['block_type'] == "3" ) {
							if ($block_line['block_type'] != "1") {
								$block_line['rate'] = "--";
								$block_line['lowest_study_number'] = "--";
								$block_line['clear_rate'] = "--";
								$block_line_difficulty_type = "--";
							} else {
								$block_line_difficulty_type = $L_DIFFICULTY_TYPE[$block_line['difficulty_type']];
							}
							// 学習タイプ
							$html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
							// 表示・非表示
							$html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
							// ドリル詳細
							$html .= "<td>\n";
							$html .= "<span style=\"font-weight:bold;\">[基本正解率]</span>：{$block_line['rate']}%<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[最低学習問題数]</span>：{$block_line['lowest_study_number']}問<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[ドリルクリアー正解率]</span>：{$block_line['clear_rate']}%<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[難度利用タイプ]</span>：{$block_line_difficulty_type}<br>\n";
							$html .= "</td>\n";

							if ( $block_line['block_type'] == "2" || $block_line['block_type'] == "3" ) {
								// スキル詳細
								if(is_array($L_SKILL_INFO[$block_line['block_num']])){
									$html .= "<td>\n";
									foreach($L_SKILL_INFO[$block_line['block_num']] AS $key => $val){
										$review_setting_num = $val['review_setting_num'];
										$skil_num = $val['skill_num'];
										if($val['skill_name']){
											$skill_name = $val['skill_name'];
										} else {
											$skill_name = "未登録";
										}
										$skill_name = str_replace("&lt;","<",$skill_name);
										$skill_name = str_replace("&gt;",">",$skill_name);
										$stage_name = $val['stage_name'];
										$lesson_name = $val['lesson_name'];
										$unit_name = $val['unit_name'];
										$lesson_page = $val['lesson_page'];
										$html .= "<span style=\"font-weight:bold;\">◆登録番号</span>：".$review_setting_num."<br>\n";
										$html .= "<span style=\"font-weight:bold;\">[スキル番号]</span>：".$skil_num."<br>\n";
										$html .= "<span style=\"font-weight:bold;\">[スキル名]</span>：".$skill_name."<br>\n";
										$html .= "<span style=\"font-weight:bold;\">[復習レッスンユニット]</span>：";
										$html .= $stage_name." > ";
										$html .= $lesson_name." > ";
										$html .= $unit_name."<br>\n";
										$html .= "<span style=\"font-weight:bold;\">[復習レッスンページ]</span>：".$lesson_page."<br>\n";
									}
									$html .= "</td>\n";
								} else {
									$html .= "<td>\n";
									$html .= "<span style=\"font-weight:bold;\">スキル未登録</span>\n";
									$html .= "</td>\n";
								}
							} else {
								$html .= "<td>\n";
								$html .= "<span style=\"font-weight:bold;\">--</span>\n";
								$html .= "</td>\n";
							}

						// ドリルA／ドリルB／ドリルC／ドリルDの場合
						} else {

							$surala_unit = get_surala_unit($_POST['course_num'], $stage_num_, $_POST['lesson_num'], $unit_num_, $block_line['block_num'], 0, 0);
							$surala_unit = change_unit_num_to_key_all($surala_unit);

							$problem_count = "未登録";
							if($L_PROBLEM_COUNT_INFO[$block_line['block_num']]){ $problem_count = $L_PROBLEM_COUNT_INFO[$block_line['block_num']]; }

							// 学習タイプ
							$html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
							// 表示・非表示
							$html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
							// ドリル詳細
							$html .= "<td>\n";
							$html .= "<span style=\"font-weight:bold;\">[出題順序]</span>：{$L_ORDER_TYPE[$block_line['order_type']]}<br>\n";
							// update start oda 2018/06/12 出題数を追加・変更
							//$html .= "<span style=\"font-weight:bold;\">[出題数]</span>：{$problem_count}<br>\n";
							// ドリルBの時、出題数を表示
							// if ($block_line['block_type'] == "5") {									// del 2018/10/09 yoshizawa すらら英単語追加
							if ($block_line['block_type'] == "5" || $block_line['block_type'] == "8") {	// add 2018/10/09 yoshizawa すらら英単語追加
								$html .= "<span style=\"font-weight:bold;\">[出題数]</span>：{$block_line['lowest_study_number']}<br>\n";
							}
							$html .= "<span style=\"font-weight:bold;\">[登録問題数]</span>：{$problem_count}<br>\n";
							// update end oda 2018/06/12
							// update start 2018/10/09 yoshizawa すらら英単語追加
							// $html .= "<span style=\"font-weight:bold;\">[すららで復習]</span>：{$surala_unit}<br>\n";
							// $html .= "<span style=\"font-weight:bold;\">[ドリルクリア時間]</span>：{$block_line['clear_time']}<br>\n";
							if($block_line['block_type'] == "8"){
								// ドリルEでは設定項目がないので出さない
							} else {
								$html .= "<span style=\"font-weight:bold;\">[すららで復習]</span>：{$surala_unit}<br>\n";
								$html .= "<span style=\"font-weight:bold;\">[ドリルクリア時間]</span>：{$block_line['clear_time']}<br>\n";
							}
							// update end 2018/10/09 yoshizawa すらら英単語追加
							$html .= "</td>\n";
							// スキル詳細
							$html .= "<td>\n";
							$html .= "<span style=\"font-weight:bold;\">--</span>\n";
							$html .= "</td>\n";

						}
						// update end 2018/05/15 yoshizawa 理科社会対応

						$ii = 1;
					}
				} else {
					// update start 2018/05/15 yoshizawa 理科社会対応
					// // 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
					// $html .= "<td colspan=\"6\">ユニットドリル未登録</td>\n"; // update 8→6 2016/01/07 yoshizawa レクチャー閲覧を促す機能
					$html .= "<td colspan=\"4\">ユニットドリル未登録</td>\n";
					// update start 2018/05/15 yoshizawa 理科社会対応
				}

				$html .= "</tr>\n";
				++$i;
			}
		}
		$html .= "</table>\n";
		$html .= "クリアーユニットキーが登録されていないユニットは、表示順番に学習されます。<br>\n";
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
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function addform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {
//	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;										// 2012/08/21 del oda
	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;		// 2012/08/21 update oda
	global $L_SPEECH_EVALUATION; // kaopiz 2020/08/20 speech

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//    kaopiz 2020/08/20 del
//    $make_html->set_file(PRACTICE_UNIT_FORM);
//    kaopiz 2020/08/20 speech start
    if ($GLOBALS['speechEvaluation']) {
        $make_html->set_file(PRACTICE_UNIT_FORM_1);
    } else {
        $make_html->set_file(PRACTICE_UNIT_FORM);
    }
//    kaopiz 2020/08/20 speech end

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>"---");
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$_POST['lesson_num']]);
	$INPUTS['UNITNAME'] = array('type'=>'text','name'=>'unit_name','value'=>$_POST['unit_name']);
	$INPUTS['PARENTSUNITNUM'] = array('type'=>'select','name'=>'parents_unit_num','array'=>$L_UNIT,'check'=>$_POST['parents_unit_num']);
	$INPUTS['CLASSM'] = array('type'=>'select','name'=>'unit_type','array'=>$L_UNIT_TYPE,'check'=>$_POST['unit_type']);
//	$INPUTS[SETTINGTYPE] = array('type'=>'select','name'=>'setting_type','array'=>$L_SETTING_TYPE,'check'=>$_POST['setting_type']);
	$INPUTS['RATE'] = array('type'=>'text','name'=>'rate','size'=>'6','value'=>$_POST['rate']);
	$INPUTS['POOL'] = array('type'=>'text','name'=>'pool','size'=>'6','value'=>$_POST['pool']);
	$INPUTS['MAXPOOL'] = array('type'=>'text','name'=>'max_pool','size'=>'6','value'=>$_POST['max_pool']);
	$INPUTS['MINPOOL'] = array('type'=>'text','name'=>'min_pool','size'=>'6','value'=>$_POST['min_pool']);
	$INPUTS['POOLRATENUMBER'] = array('type'=>'text','name'=>'pool_rate_number','size'=>'6','value'=>$_POST['pool_rate_number']);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('type'=>'text','name'=>'lowest_study_number','size'=>'6','value'=>$_POST['lowest_study_number']);
	$INPUTS['CLEARRATENUMBER'] = array('type'=>'text','name'=>'clear_rate_number','size'=>'6','value'=>$_POST['clear_rate_number']);
	$INPUTS['CLEARRATE'] = array('type'=>'text','name'=>'clear_rate','size'=>'6','value'=>$_POST['clear_rate']);
	$INPUTS['UNITKEY'] = array('type'=>'text','name'=>'unit_key','size'=>'6','value'=>$_POST['unit_key']);

//    $wESC  =  htmlspecialchars( $unit_name2 );	//	del ookawara 2010/02/04
//    $INPUTS[UNITNAME2] = array('type'=>'text','name'=>'unit_name2','value'=>$wESC);	//	del ookawara 2010/02/04
    $INPUTS['UNITNAME2'] = array('type'=>'text','name'=>'unit_name2','value'=>$_POST['unit_name2'],'size'=>'50');	//	add ookawara 2010/02/04
	$INPUTS['UNITNAME3'] = array('type'=>'text','name'=>'unit_name3','value'=>$_POST['unit_name3'],'size'=>'50');

	$INPUTS['LEVEL'] = array('type'=>'text','name'=>'level','value'=>$_POST['level']);							// add oda 2012/10/19
	$INPUTS['STUDYCONTROL'] = array('type'=>'text','name'=>'study_control','value'=>$_POST['study_control']);		// add oda 2012/10/19
	// add start 2018/05/18 yoshizawa 学習コントロール制御
	$study_control_att = "<br><span style=\"color:red;\">※学習制御となるユニット番号を入力して下さい。";
	$study_control_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";
	$INPUTS['STUDYCONTROLATT'] 	= array('result'=>'plane','value'=>$study_control_att);
	// add start 2018/05/18 yoshizawa
	$INPUTS['LASTLEVEL'] = array('type'=>'text','name'=>'last_level','value'=>$_POST['last_level']);				// add oda 2012/10/31

	$INPUTS['CLEARUNITKEY'] = array('type'=>'text','name'=>'clear_unit_key','value'=>$_POST['clear_unit_key']);
	$lesson_key = lesson_key;
	$INPUTS['LESSONKEY'] = array('result'=>'plane','value'=>$lesson_key);

	// 2012/08/21 add start oda
    // 上位で設定されている場合は、入力フィールドを設定しない
    $upper_check_s = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    $upper_check_l = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
    if (strlen($upper_check_s) == 0 && strlen($upper_check_l) == 0) {
		$INPUTS['SURALAUNIT'] = array('type'=>'text','name'=>'surala_unit','value'=>$_POST['surala_unit']);
		$surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
		$surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_s) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_l) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Lesson）にて設定済です</span>";
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
	// // 2012/08/21 add end oda
	// del end oda 2017/05/15

	// 2012/08/27 add oda
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("medal_status");
	$newform->set_form_id("display4");
	$newform->set_form_check($_POST['medal_status']);
	$newform->set_form_value('1');
	$on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("medal_status");
	$newform->set_form_id("undisplay4");
	$newform->set_form_check($_POST['medal_status']);
	$newform->set_form_value('2');
	$off = $newform->make();
	$onoff = $on . "<label for=\"display4\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay4\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['MEDALSTATUS'] = array('result'=>'plane','value'=>$onoff);

	// update start oda 2017/05/15 未使用箇所削除 最終学習日付／最終正解率／最終学習時間は表示とする
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_date");
	// $newform->set_form_id("display5");
	// $newform->set_form_check($_POST['last_study_date']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_date");
	// $newform->set_form_id("undisplay5");
	// $newform->set_form_check($_POST['last_study_date']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display5\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay5\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTSTUDYDATE'] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_correct_count");
	// $newform->set_form_id("display6");
	// $newform->set_form_check($_POST['last_correct_count']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_correct_count");
	// $newform->set_form_id("undisplay6");
	// $newform->set_form_check($_POST['last_correct_count']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display6\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay6\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTCORRECTCOUNT'] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_time");
	// $newform->set_form_id("display7");
	// $newform->set_form_check($_POST['last_study_time']);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_time");
	// $newform->set_form_id("undisplay7");
	// $newform->set_form_check($_POST['last_study_time']);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display7\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay7\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTSTUDYTIME'] = array('result'=>'plane','value'=>$onoff);
	// del end oda 2017/05/15

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ranking_button");
	$newform->set_form_id("display8");
	$newform->set_form_check($_POST['ranking_button']);
	$newform->set_form_value('1');
	$on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ranking_button");
	$newform->set_form_id("undisplay8");
	$newform->set_form_check($_POST['ranking_button']);
	$newform->set_form_value('2');
	$off = $newform->make();
	$onoff = $on . "<label for=\"display8\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay8\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['RANKINGBUTTON'] = array('result'=>'plane','value'=>$onoff);
	// 2012/08/27 add end oda

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type1");
	$newform->set_form_check($_POST['difficulty_type']);
	$newform->set_form_value('1');
	$difficulty_type1 = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type2");
	$newform->set_form_check($_POST['difficulty_type']);
	$newform->set_form_value('2');
	$difficulty_type2 = $newform->make();
	$difficulty_type = $difficulty_type1 . "<label for=\"difficulty_type1\">{$L_DIFFICULTY_TYPE[1]}</label> / " . $difficulty_type2 . "<label for=\"difficulty_type2\">{$L_DIFFICULTY_TYPE[2]}</label>";
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$difficulty_type);
//    kaopiz 2020/08/20 speech start
	if ($GLOBALS['speechEvaluation']) {
		if(ACTION == '') {
            $externalCourse = findExternalCourseNotDelete($_POST['course_num']);
            if ($externalCourse) {
                $_POST['option1'] = $externalCourse['option1'];
            }
		}
        $INPUTS[OPTION1] = array('type' => 'select', 'name' => 'option1', 'array' => $L_SPEECH_EVALUATION, 'check' => $_POST['option1']);
    }
//    kaopiz 2020/08/20 speech end

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
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	//	ランキングパターン
	//	add ookawara 2012/12/05 start
	$mt_ranking_point_num = $_POST['mt_ranking_point_num'];
	$course_num = $_POST['course_num'];
	$rank_form = "";
	$rank_form = "<select name=\"mt_ranking_point_num\">\n";
	$sql  = "SELECT mt_ranking_point_num, pattern_name FROM ".T_MS_RANKING_POINT.
			" WHERE mk_flg='0'".
			"   AND course_num ='".$course_num."'".
			"   AND default_flag ='1'".						// add oda 2013/10/15
			" ORDER BY pattern_name".
			" ;";
	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($mt_ranking_point_num != "") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"\" ".$selected.">未設定</option>\n";
		$selected = "";
		if ($mt_ranking_point_num == "-1") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"-1\" ".$selected.">デフォルト（正解率＋時間）</option>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$mt_ranking_point_num_list = $list['mt_ranking_point_num'];
			$pattern_name = $list['pattern_name'];
			$selected = "";
			if ($mt_ranking_point_num == $mt_ranking_point_num_list) {
				$selected = " selected";
			}
			$rank_form .= "<option value=\"".$mt_ranking_point_num_list."\" ".$selected.">".$pattern_name."</option>\n";
		}
	}
	$rank_form .= "</select>\n";
	$INPUTS['RANKINGPOINTNUM'] = array('result'=>'plane','value'=>$rank_form);
	//	add ookawara 2012/12/05 end

	// add start 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	if (!is_numeric($_POST['again_lecture_time']) && $_POST['again_lecture_time'] == "" ) {
		$again_lecture_time = 60;
	} else {
		$again_lecture_time = $_POST['again_lecture_time'];
	}
	$INPUTS['AGAINLECTURETIME'] = array('type'=>'text','name'=>'again_lecture_time','size'=>'6','value'=>$again_lecture_time);

	if (!is_numeric($_POST['again_lecture_count']) && $_POST['again_lecture_count'] == "" ) {
		$again_lecture_count = 3;
	} else {
		$again_lecture_count = $_POST['again_lecture_count'];
	}
	$INPUTS['AGAINLECTURECOUNT'] = array('type'=>'text','name'=>'again_lecture_count','size'=>'6','value'=>$again_lecture_count);
	// add end 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	//upd start hirose 2018/11/28 すらら英単語
//	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語
	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$_POST['remarks']);
	//upd end hirose 2018/11/28 すらら英単語

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
/*
	$html .= "<b>クリアーユニットキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないユニットが有る場合<br>\n";
	$html .= "　クリアーしなければいけないユニットのユニットキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるユニットが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なユニットが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：S1L1U1::S1L2U3::S1L3U3<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;
}

/**
 * 表示フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function viewform($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {

//	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;										// 2012/08/21 del oda
	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;		// 2012/08/21 update oda
	global $L_ORDER_TYPE;																					// 2013/10/15 add koyama
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
    global $L_SPEECH_EVALUATION; // kaopiz 2020/08/20 speech
	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
		if (MODE != "詳細") {
			$sql = "SELECT * FROM " . T_UNIT .
				" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' LIMIT 1;";

			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
//            kaopiz 2020/08/20 speech start
            $externalUnit = findExternalUnitNotDelete($_POST[unit_num]);
            if ($externalUnit) {
                foreach ($externalUnit as $key => $val) {
                    $$key = replace_decode($val);
                }
            }
//            kaopiz 2020/08/20 speech end
			// update start oda 2017/05/15 未使用箇所削除
			// 2012/08/21 add start oda
			// $standard_study_time = "2";
			// $study_time = "2";
			// $remainder_unit = "2";
			$medal_status = "2";			// 2012/08/27 add oda
			// $last_study_date = "2";			// 2012/08/27 add oda
			// $last_correct_count = "2";		// 2012/08/27 add oda
			// $last_study_time = "2";			// 2012/08/27 add oda
			$ranking_button = "2";			// 2012/08/27 add oda

			if ($status_display) {
				$dispray_list = explode("<>",$status_display);
				for ($j=0; $j<count($dispray_list); $j++) {
					// if ($dispray_list[$j] == "standard_study_time") {
					// 	$standard_study_time = "1";
					// }
					// if ($dispray_list[$j] == "study_time") {
					// 	$study_time = "1";
					// }
					// if ($dispray_list[$j] == "remainder_unit") {
					// 	$remainder_unit = "1";
					// }
					// 2012/08/27 add start oda
					if ($dispray_list[$j] == "medal_status") {
						$medal_status = "1";
					}
					// if ($dispray_list[$j] == "last_study_date") {
					// 	$last_study_date = "1";
					// }
					// if ($dispray_list[$j] == "last_correct_count") {
					// 	$last_correct_count = "1";
					// }
					// if ($dispray_list[$j] == "last_study_time") {
					// 	$last_study_time = "1";
					// }
					if ($dispray_list[$j] == "ranking_button") {
						$ranking_button = "1";
					}
					// 2012/08/27 add end oda
				}
			}
			// update end oda 2017/05/15

			$status_display = "";

	    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
	    	$surala_unit = change_unit_num_to_key_all($surala_unit);
			// 2012/08/21 add end oda
		}
	} else {
		$sql = "SELECT * FROM " . T_UNIT .
			" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
//        kaopiz 2020/08/20 speech start
        $externalUnit = findExternalUnitNotDelete($_POST[unit_num]);
        if ($externalUnit) {
            foreach ($externalUnit as $key => $val) {
                $option1 = $externalUnit['option1'];
            }
        }
//        kaopiz 2020/08/20 speech end

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		if (!$s_stage_num) { $s_stage_num = $stage_num; }

		if ($clear_unit_key) {
			$clear_unit_key = ereg_replace("^:|:$","",$clear_unit_key);
		}

		// update start oda 2017/05/15 未使用箇所削除
		// 2012/08/21 add start oda
		// $standard_study_time = "2";
		// $study_time = "2";
		// $remainder_unit = "2";
		$medal_status = "2";			// 2012/08/27 add oda
		// $last_study_date = "2";			// 2012/08/27 add oda
		// $last_correct_count = "2";		// 2012/08/27 add oda
		// $last_study_time = "2";			// 2012/08/27 add oda
		$ranking_button = "2";			// 2012/08/27 add oda

		if ($status_display) {
			$dispray_list = explode("<>",$status_display);
			for ($j=0; $j<count($dispray_list); $j++) {
				// if ($dispray_list[$j] == "standard_study_time") {
				// 	$standard_study_time = "1";
				// }
				// if ($dispray_list[$j] == "study_time") {
				// 	$study_time = "1";
				// }
				// if ($dispray_list[$j] == "remainder_unit") {
				// 	$remainder_unit = "1";
				// }
				// 2012/08/27 add start oda
				if ($dispray_list[$j] == "medal_status") {
					$medal_status = "1";
				}
				// if ($dispray_list[$j] == "last_study_date") {
				// 	$last_study_date = "1";
				// }
				// if ($dispray_list[$j] == "last_correct_count") {
				// 	$last_correct_count = "1";
				// }
				// if ($dispray_list[$j] == "last_study_time") {
				// 	$last_study_time = "1";
				// }
				if ($dispray_list[$j] == "ranking_button") {
					$ranking_button = "1";
				}
				// 2012/08/27 add end oda
			}
		}
		// update end oda 2017/05/15

		$status_display = "";

		$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
		$surala_unit = change_unit_num_to_key_all($surala_unit);
		// 2012/08/21 add end oda

	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "ユニット詳細<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";					// 2012/11/16 add oda
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//    kaopiz 2020/08/20 del
//    $make_html->set_file(PRACTICE_UNIT_FORM);
//    kaopiz 2020/08/20 speech start
    if ($GLOBALS['speechEvaluation']) {
        $make_html->set_file(PRACTICE_UNIT_FORM_1);
    } else {
        $make_html->set_file(PRACTICE_UNIT_FORM);
    }
//    kaopiz 2020/08/20 speech end

	// start add 2020/05/27 yoshizawa 
	// inputタグの初期値でvalue=""にセットする文字列にダブルクォーテーションが含まれていると
	// 途中で切れてしまします。ダブルクォーテーションについては変換いたします。
	$unit_name = preg_replace("/\"/", "&quot;", $unit_name);
	$unit_name2 = preg_replace("/\"/", "&quot;", $unit_name2);
	$unit_name3 = preg_replace("/\"/", "&quot;", $unit_name3);
	$remarks = preg_replace("/\"/", "&quot;", $remarks);
	// end add 2020/05/27 yoshizawa 

	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>$unit_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$_POST['lesson_num']]);
	$INPUTS['UNITNAME'] = array('type'=>'text','name'=>'unit_name','value'=>$unit_name);
	$INPUTS['PARENTSUNITNUM'] = array('type'=>'select','name'=>'parents_unit_num','array'=>$L_UNIT,'check'=>$parents_unit_num);
	$INPUTS['CLASSM'] = array('type'=>'select','name'=>'unit_type','array'=>$L_UNIT_TYPE,'check'=>$unit_type);
//	$INPUTS[SETTINGTYPE] = array('type'=>'select','name'=>'setting_type','array'=>$L_SETTING_TYPE,'check'=>$setting_type);
	$INPUTS['RATE'] = array('type'=>'text','name'=>'rate','size'=>'6','value'=>$rate);
	$INPUTS['POOL'] = array('type'=>'text','name'=>'pool','size'=>'6','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('type'=>'text','name'=>'max_pool','size'=>'6','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('type'=>'text','name'=>'min_pool','size'=>'6','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('type'=>'text','name'=>'pool_rate_number','size'=>'6','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('type'=>'text','name'=>'lowest_study_number','size'=>'6','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('type'=>'text','name'=>'clear_rate_number','size'=>'6','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('type'=>'text','name'=>'clear_rate','size'=>'6','value'=>$clear_rate);
	$lesson_key = lesson_key;
	$unit_key = ereg_replace("^$lesson_key","",$unit_key);
	$INPUTS['UNITKEY'] = array('type'=>'text','name'=>'unit_key','value'=>$unit_key);
/*
	//	del ookawara 2010/02/04
    // --- UPD20081009_1  単元名　追加
    $wESC  =  htmlspecialchars( $unit_name2 );
    $INPUTS[UNITNAME2] = array('type'=>'text','name'=>'unit_name2','value'=>$wESC);
    // --- UPD20081009_1 ここまで
*/
	//	add ookawara 2010/02/04
	$unit_name2 = ereg_replace("&lt;","<",$unit_name2);
	$unit_name2 = ereg_replace("&gt;",">",$unit_name2);
    $INPUTS['UNITNAME2'] = array('type'=>'text','name'=>'unit_name2','value'=>$unit_name2,'size'=>'50');

	$INPUTS['UNITNAME3'] = array('type'=>'text','name'=>'unit_name3','value'=>$unit_name3,'size'=>'50');

	$INPUTS['LEVEL'] = array('type'=>'text','name'=>'level','value'=>$level);								// add oda 2012/10/19
	$INPUTS['STUDYCONTROL'] = array('type'=>'text','name'=>'study_control','value'=>$study_control);		// add oda 2012/10/19
	// add start 2018/05/18 yoshizawa 学習コントロール制御
	$study_control_att = "<br><span style=\"color:red;\">※学習制御となるユニット番号を入力して下さい。";
	$study_control_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";
	$INPUTS['STUDYCONTROLATT'] 	= array('result'=>'plane','value'=>$study_control_att);
	// add start 2018/05/18 yoshizawa
	$INPUTS['LASTLEVEL'] = array('type'=>'text','name'=>'last_level','value'=>$last_level);				// add oda 2012/10/31

	$INPUTS['CLEARUNITKEY'] = array('type'=>'text','name'=>'clear_unit_key','value'=>$clear_unit_key);
	$INPUTS['LESSONKEY'] = array('result'=>'plane','value'=>$lesson_key);

	// 2012/08/21 add start oda
    // 上位で設定されている場合は、入力フィールドを設定しない
    $upper_check_s = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    $upper_check_l = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
    if (strlen($upper_check_s) == 0 && strlen($upper_check_l) == 0) {
		$INPUTS['SURALAUNIT'] = array('type'=>'text','name'=>'surala_unit','value'=>$surala_unit);
		$surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
		$surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_s) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_l) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Lesson）にて設定済です</span>";
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
	// // 2012/08/21 add end oda
	// del end oda 2017/05/15

	// 2012/08/27 add oda
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("medal_status");
	$newform->set_form_id("display4");
	$newform->set_form_check($medal_status);
	$newform->set_form_value('1');
	$on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("medal_status");
	$newform->set_form_id("undisplay4");
	$newform->set_form_check($medal_status);
	$newform->set_form_value('2');
	$off = $newform->make();
	$onoff = $on . "<label for=\"display4\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay4\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['MEDALSTATUS'] = array('result'=>'plane','value'=>$onoff);

	// update start oda 2017/05/15 未使用箇所削除 最終学習日付／最終正解率／最終学習時間は表示とする
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_date");
	// $newform->set_form_id("display5");
	// $newform->set_form_check($last_study_date);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_date");
	// $newform->set_form_id("undisplay5");
	// $newform->set_form_check($last_study_date);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display5\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay5\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTSTUDYDATE'] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_correct_count");
	// $newform->set_form_id("display6");
	// $newform->set_form_check($last_correct_count);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_correct_count");
	// $newform->set_form_id("undisplay6");
	// $newform->set_form_check($last_correct_count);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display6\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay6\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTCORRECTCOUNT'] = array('result'=>'plane','value'=>$onoff);

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_time");
	// $newform->set_form_id("display7");
	// $newform->set_form_check($last_study_time);
	// $newform->set_form_value('1');
	// $on = $newform->make();
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("last_study_time");
	// $newform->set_form_id("undisplay7");
	// $newform->set_form_check($last_study_time);
	// $newform->set_form_value('2');
	// $off = $newform->make();
	// $onoff = $on . "<label for=\"display7\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay7\">{$L_ONOFF_TYPE[2]}</label>";
	// $INPUTS['LASTSTUDYTIME'] = array('result'=>'plane','value'=>$onoff);
	// update end oda 2017/05/15

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ranking_button");
	$newform->set_form_id("display8");
	$newform->set_form_check($ranking_button);
	$newform->set_form_value('1');
	$on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ranking_button");
	$newform->set_form_id("undisplay8");
	$newform->set_form_check($ranking_button);
	$newform->set_form_value('2');
	$off = $newform->make();
	$onoff = $on . "<label for=\"display8\">{$L_ONOFF_TYPE[1]}</label> / " . $off . "<label for=\"undisplay8\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['RANKINGBUTTON'] = array('result'=>'plane','value'=>$onoff);
	// 2012/08/27 add end oda

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type1");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('1');
	$difficulty_type1 = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type2");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('2');
	$difficulty_type2 = $newform->make();
	$difficulty_type = $difficulty_type1 . "<label for=\"difficulty_type1\">{$L_DIFFICULTY_TYPE[1]}</label> / " . $difficulty_type2 . "<label for=\"difficulty_type2\">{$L_DIFFICULTY_TYPE[2]}</label>";
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$difficulty_type);
//    kaopiz 2020/08/20 speech start
    if ($GLOBALS['speechEvaluation']) {
        $INPUTS[OPTION1] = array('type' => 'select', 'name' => 'option1', 'array' => $L_SPEECH_EVALUATION, 'check' => $option1);
    }
//    kaopiz 2020/08/20 speech end

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

	//	ランキングパターン
	//	add ookawara 2012/12/05 start
	$rank_form = "";
	$rank_form = "<select name=\"mt_ranking_point_num\">\n";
	$sql  = "SELECT mt_ranking_point_num, pattern_name FROM ".T_MS_RANKING_POINT.
			" WHERE mk_flg='0'".
			"   AND course_num ='".$course_num."'".
			"   AND default_flag ='1'".						// add oda 2013/10/15
			" ORDER BY pattern_name".
			" ;";
	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($mt_ranking_point_num != "") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"\" ".$selected.">未設定</option>\n";
		$selected = "";
		if ($mt_ranking_point_num == "-1") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"-1\" ".$selected.">デフォルト（正解率＋時間）</option>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$mt_ranking_point_num_list = $list['mt_ranking_point_num'];
			$pattern_name = $list['pattern_name'];

			$selected = "";
			if ($mt_ranking_point_num == $mt_ranking_point_num_list) {
				$selected = " selected";
			}
			$rank_form .= "<option value=\"".$mt_ranking_point_num_list."\" ".$selected.">".$pattern_name."</option>\n";
		}
	}
	//add start hirose 2019/11/06 別教科ユニットサジェスト開発
	if($stage_num && $_POST['lesson_num']){
		//確認中のユニットが英国数理社かチェック
		$sql = " SELECT course.course_num " .
				" FROM ".T_COURSE." course ".
				" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON course.course_num = scl.course_num AND scl.course_type = 1 AND scl.mk_flg = 0 ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.setup_type_sub = 1 AND sc.mk_flg = 0 ".
				" WHERE course.state='0'" .
//				"   AND course.display = 1" .
				"   AND course.course_num = '".$course_num."' ".
				" ;";
		$course_check = false;
		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)){
				if(!empty($list['course_num']) && $list['course_num'] != 12){
					$course_check = true;
					break;
				}
			}
		}
		//確認中のユニットにサジェストユニットが存在するか確認。
		$suggest_flg = false;
		if($course_check){
			$sql  = check_suggest_sql($_POST['unit_num']);
			if ($result = $cdb->query($sql)) {
				while($list = $cdb->fetch_assoc($result)){
					if(!empty($list['course_num']) && $list['course_num'] != 12 && $list['suggest_unit_num'] != $_POST['unit_num']){//すらら忍者算数は除く
						$suggest_flg = true;
						break;
					}
				}
			}
		}
	}
	//add end hirose 2019/11/06 別教科ユニットサジェスト開発
	$rank_form .= "</select>\n";
	$INPUTS['RANKINGPOINTNUM'] = array('result'=>'plane','value'=>$rank_form);
	//	add ookawara 2012/12/05 end

	// add start 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	$INPUTS['AGAINLECTURETIME'] = array('type'=>'text','name'=>'again_lecture_time','size'=>'6','value'=>$again_lecture_time);
	$INPUTS['AGAINLECTURECOUNT'] = array('type'=>'text','name'=>'again_lecture_count','size'=>'6','value'=>$again_lecture_count);
	// add end 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	$INPUTS['REMARKS']  = array('result'=>'form', 'type'=>'textarea', 'name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	//add start hirose 2019/11/05 別教科ユニットサジェスト開発
	if($suggest_flg){
		$html .= "<input type=\"button\" value=\"サジェスト確認\" onclick=\"check_unit_comp_win_open('".$course_num."', '".$_POST['unit_num']."','1')\">";
		$html .= "<input type=\"button\" value=\"サジェスト確認(新)\" onclick=\"check_unit_comp_win_open('".$course_num."', '".$_POST['unit_num']."','2')\">";
		$html .= "<input type=\"button\" value=\"サジェスト確認(ピタドリ)\" onclick=\"check_unit_comp_win_open('".$course_num."', '".$_POST['unit_num']."','3')\">";
	}
	//add end hirose 2019/11/05 別教科ユニットサジェスト開発
/*
	$html .= "<b>クリアーユニットキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないユニットが有る場合<br>\n";
	$html .= "　クリアーしなければいけないユニットのユニットキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるユニットが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なユニットが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：S1L1U1::S1L2U3::S1L3U3<br>\n";
	$html .= "<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<hr>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "ユニット登録ドリル<br>\n";

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_add",$_SESSION['authority'])===FALSE)) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"block_form\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"block_addform\">\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "<input type=\"submit\" value=\"ドリル新規登録\">\n";
		$html .= "</form><br>\n";
	}

	$block_num_list = ""; // add 2018/05/24 yoshizawa 理科社会対応
	$sql = "SELECT * FROM " . T_BLOCK .
		" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' ORDER BY list_num";

	if ($result = $cdb->query($sql)) {
		$block_count = $cdb->num_rows($result);
		if ($block_count > 1) { $block_rows = " rowspan=\"$block_count\""; }
		else { $block_rows = ""; }
		while ($list = $cdb->fetch_assoc($result)) {
			$L_BLOCK[$list['block_num']] = $list;
			if($block_num_list){ $block_num_list .= ","; }	// add 2018/05/24 yoshizawa 理科社会対応
			$block_num_list .= "'".$list['block_num']."'";	// add 2018/05/24 yoshizawa 理科社会対応
		}
	}

	// add start 2018/05/24 yoshizawa 理科社会対応
	// レッスンに紐づくドリル情報を取得
	$L_SKILL_INFO = array();
	$L_PROBLEM_COUNT_INFO = array();
	if($block_num_list){
		// レッスンに紐づくドリル毎のスキル情報を事前に取得
		$L_SKILL_INFO = get_skill_info($_POST['course_num'], $block_num_list);

		// レッスンに紐づくドリルの問題数を取得
		$L_PROBLEM_COUNT_INFO = get_problem_count_info($block_num_list);
	}
	// add end 2018/05/24 yoshizawa 理科社会対応

	if ($block_count == 0) {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "現在ユニットドリル登録はありません。<br>\n";
	} else {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "登録ドリル<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}

		// >>> del 2018/10/09 yoshizawa すらら英単語追加 未使用なのでコメントにします。
		// // 2012/10/02 add start oda
		// // 表示タイプ取得
		// $view_type = get_data_view_type($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num']);
		// // 2012/10/02 add end oda
		// <<<

		// update start 2018/05/15 yoshizawa 理科社会対応
		// // 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		// $html .= "<th>ドリル<br>番号</th>\n";
		// $html .= "<th>学習タイプ</th>\n";
		// // 2012/10/02 update start oda
		// //		$html .= "<th>基本正解率</th>\n";
		// // //$html .= "<th>最低学習問題数</th>\n";	//	del ookawara 2012/06/11
		// // $html .= "<th>最低学習問題数/出題数</th>\n";	//	add ookawara 2012/06/11
		// // $html .= "<th>ドリルクリアー正解率</th>\n";
		// // $html .= "<th>難度利用タイプ</th>\n";
		// if ($view_type == "0") {
		// 	$html .= "<th>基本正解率</th>\n";
		// 	$html .= "<th>最低学習問題数/出題数</th>\n";
		// 	$html .= "<th>ドリルクリアー正解率</th>\n";
		// 	$html .= "<th>難度利用タイプ</th>\n";
		// } elseif ($view_type == "1") {
		// 	$html .= "<th>BGM</th>\n";
		// 	$html .= "<th>カウントダウン／タイマー表示</th>\n";	// update 2017/04/21 yoshizawa タイトル変更
		// 	$html .= "<th>出題順序</th>\n";						// add koyama 2013/10/18 割合・速さはかせ対応
		// 	$html .= "<th>すららで復習</th>\n";
		// 	$html .= "<th>ドリルクリア時間</th>\n";
		// }
		// // 2012/10/02 update end oda
		//
		$html .= "<th>ドリル<br>番号</th>\n";
		$html .= "<th>学習タイプ</th>\n";
		$html .= "<th>ドリル詳細</th>\n";
		$html .= "<th>スキル詳細</th>\n";
		// update end 2018/05/15 yoshizawa

		$html .= "<th>表示・非表示</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__review",$_SESSION['authority'])===FALSE)) {
				$html .= "<th>復習設定</th>\n";
			}
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		$i = 1;
		foreach ($L_BLOCK as $key => $block_line) {
			$material_prob_path = MATERIAL_PROB_DIR . $block_line['course_num'] ."/" . $block_line['stage_num'] . "/" . $block_line['lesson_num'] . "/" . $block_line['unit_num'] . "/" . $block_line['block_num'] . "/";
			if (!file_exists($material_prob_path)) {
				@mkdir($material_prob_path,0777);
				@chmod($material_prob_path,0777);
			}

			$material_voice_path = MATERIAL_VOICE_DIR . $block_line['course_num'] ."/" . $block_line['stage_num'] . "/" . $block_line['lesson_num'] . "/" . $block_line['unit_num'] . "/" . $block_line['block_num'] . "/";
			if (!file_exists($material_voice_path)) {
				@mkdir($material_voice_path,0777);
				@chmod($material_voice_path,0777);
			}

			//if ($block_line[block_type] != 1) {	//	del ookawara 2012/06/11
			if ($block_line['block_type'] == 2 || $block_line['block_type'] == 3) {	//	add ookawara 2012/06/11
				$submit = "<input type=\"submit\" value=\"復習設定\">";
			} else {
				$submit = "----";
			}
			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) { $up_submit = "<input type=\"submit\" value=\"↑\">\n"; }
			if ($i < $block_count) { $down_submit = "<input type=\"submit\" value=\"↓\">\n"; }

			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<tr class=\"course_form_cell\">\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"action\" value=\"block_up\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_line['block_num']}\">\n";
				$html .= "<td>$up_submit</td>\n";
				$html .= "</form>\n";

				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"action\" value=\"block_down\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_line['block_num']}\">\n";
				$html .= "<td>$down_submit</td>\n";
				$html .= "</form>\n";
			}

			if ($block_line['block_type'] != 1) {	//	del ookawara 2012/06/11
			//if ($block_line['block_type'] != 2 && $block_line['block_type'] != 3) {	//	add ookawara 2012/06/11
				$block_line['rate'] = "--";
				// if ($block_line['block_type'] != 5) {	// upd hasegawa 2018/02/23 百マス計算
				if ($block_line['block_type'] != 5 && $block_line['block_type'] != 7) {
					$block_line['lowest_study_number'] = "--";
				}
				$block_line['clear_rate'] = "--";
				$block_line_difficulty_type = "--";
			} else {
				$block_line_difficulty_type = $L_DIFFICULTY_TYPE[$block_line['difficulty_type']];
			}
			// update start 2018/05/15 yoshizawa 理科社会対応
			// $html .= "<td>{$block_line['block_num']}</td>\n";
			// $html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
			// if ($view_type == "0") {												// 2012/10/02 add oda
			// 	$html .= "<td>{$block_line['rate']}%</td>\n";
			// 	$html .= "<td>{$block_line['lowest_study_number']}問</td>\n";
			// 	$html .= "<td>{$block_line['clear_rate']}%</td>\n";
			// 	$html .= "<td>{$block_line_difficulty_type}</td>\n";
			// // 2012/10/02 add start oda
			// } elseif ($view_type == "1") {
			// 	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $block_line['block_num'], 0, 0);
			// 	$surala_unit = change_unit_num_to_key_all($surala_unit);
			// 	$html .= "<td>{$L_ONOFF_TYPE[$block_line['bgm_set']]}</td>\n";			// BGM
			// 	$html .= "<td>{$L_ONOFF_TYPE[$block_line['count_down_set']]}</td>\n";	// カウントダウン／タイマー表示
			// 	$html .= "<td>{$L_ORDER_TYPE[$block_line['order_type']]}</td>\n";		// 出題順序			 add koyama 2013/10/11 割合・速さはかせ対応
			// 	$html .= "<td>{$surala_unit}</td>\n";									// すららで復習
			// 	$html .= "<td>{$block_line['clear_time']}</td>\n";						// ドリルクリア時間
			// }
			// // 2012/10/02 add end oda
			// $html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
			//
			$html .= "<td>{$block_line['block_num']}</td>\n";
			$html .= "<td>{$L_UNIT_TYPE[$block_line['block_type']]}</td>\n";
			// 通常ドリル／診断A／診断Bの場合
			if ( $block_line['block_type'] == "1" || $block_line['block_type'] == "2" || $block_line['block_type'] == "3" ) {
				$html .= "<td>\n";
				$html .= "<span style=\"font-weight:bold;\">[基本正解率]</span>：{$block_line['rate']}%<br>\n";
				$html .= "<span style=\"font-weight:bold;\">[最低学習問題数/出題数]</span>：{$block_line['lowest_study_number']}問<br>\n";
				$html .= "<span style=\"font-weight:bold;\">[ドリルクリアー正解率]</span>：{$block_line['clear_rate']}%<br>\n";
				$html .= "<span style=\"font-weight:bold;\">[難度利用タイプ]</span>：{$block_line_difficulty_type}<br>\n";
				$html .= "</td>\n";

				if ( $block_line['block_type'] == "2" || $block_line['block_type'] == "3" ) {

					// スキル詳細
					if(is_array($L_SKILL_INFO[$block_line['block_num']])){
						$html .= "<td>\n";
						foreach($L_SKILL_INFO[$block_line['block_num']] AS $key => $val){
							$review_setting_num = $val['review_setting_num'];
							$skil_num = $val['skill_num'];
							if($val['skill_name']){
								$skill_name = $val['skill_name'];
							} else {
								$skill_name = "未登録";
							}
							$skill_name = str_replace("&lt;","<",$skill_name);
							$skill_name = str_replace("&gt;",">",$skill_name);
							$stage_name = $val['stage_name'];
							$lesson_name = $val['lesson_name'];
							$unit_name = $val['unit_name'];
							$lesson_page = $val['lesson_page'];
							$html .= "<span style=\"font-weight:bold;\">◆登録番号</span>：".$review_setting_num."<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[スキル番号]</span>：".$skil_num."<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[スキル名]</span>：".$skill_name."<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[復習レッスンユニット]</span>：";
							$html .= $stage_name." > ";
							$html .= $lesson_name." > ";
							$html .= $unit_name."<br>\n";
							$html .= "<span style=\"font-weight:bold;\">[復習レッスンページ]</span>：".$lesson_page."<br>\n";
						}
						$html .= "</td>\n";
					} else {
						$html .= "<td>\n";
						$html .= "<span style=\"font-weight:bold;\">スキル未登録</span>\n";
						$html .= "</td>\n";
					}
				} else {
					$html .= "<td>\n";
					$html .= "<span style=\"font-weight:bold;\">--</span>\n";
					$html .= "</td>\n";
				}

			// ドリルA／ドリルB／ドリルC／ドリルDの場合
			} else {
				$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $block_line['block_num'], 0, 0);
				$surala_unit = change_unit_num_to_key_all($surala_unit);

				$problem_count = "未登録";
				if($L_PROBLEM_COUNT_INFO[$block_line['block_num']]){ $problem_count = $L_PROBLEM_COUNT_INFO[$block_line['block_num']]; }
				// ドリル詳細
				$html .= "<td>\n";
				$html .= "<span style=\"font-weight:bold;\">[出題順序]</span>：{$L_ORDER_TYPE[$block_line['order_type']]}<br>\n";
				// update start oda 2018/06/12 出題数を追加・変更
				//$html .= "<span style=\"font-weight:bold;\">[出題数]</span>：{$problem_count}<br>\n";
				// ドリルBの時、出題数を表示
				// if ($block_line['block_type'] == "5") {									// del 2018/10/09 yoshizawa すらら英単語追加
				if ($block_line['block_type'] == "5" || $block_line['block_type'] == "8") {	// add 2018/10/09 yoshizawa すらら英単語追加
					$html .= "<span style=\"font-weight:bold;\">[出題数]</span>：{$block_line['lowest_study_number']}<br>\n";
				}
				$html .= "<span style=\"font-weight:bold;\">[登録問題数]</span>：{$problem_count}<br>\n";
				// update end oda 2018/06/12
				// update start 2018/10/09 yoshizawa すらら英単語追加
				// $html .= "<span style=\"font-weight:bold;\">[すららで復習]</span>：{$surala_unit}<br>\n";
				// $html .= "<span style=\"font-weight:bold;\">[ドリルクリア時間]</span>：{$block_line['clear_time']}<br>\n";
				if($block_line['block_type'] == "8"){
					// ドリルEでは設定項目がないので出さない
				} else {
					$html .= "<span style=\"font-weight:bold;\">[すららで復習]</span>：{$surala_unit}<br>\n";
					$html .= "<span style=\"font-weight:bold;\">[ドリルクリア時間]</span>：{$block_line['clear_time']}<br>\n";
				}
				// update end 2018/10/09 yoshizawa すらら英単語追加
				$html .= "</td>\n";
				// スキル詳細
				$html .= "<td>\n";
				$html .= "<span style=\"font-weight:bold;\">--</span>\n";
				$html .= "</td>\n";
			}
			$html .= "<td>{$L_DISPLAY[$block_line['display']]}</td>\n";
			// update start 2018/05/15 yoshizawa 理科社会対応

			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"block_form\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_line['block_num']}\">\n";
				$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
				$html .= "</form>\n";

				if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__review",$_SESSION['authority'])===FALSE)) {
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"review\">\n";
					$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
					$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_line['block_num']}\">\n";
					$html .= "<td>$submit</td>\n";
					$html .= "</form>\n";
				}
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"block_delete\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_line['block_num']}\">\n";
				$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
				$html .= "</form>\n";
			}
			$html .= "</tr>\n";
			$i++;
		}
		$html .= "</table><br>\n";
	}

	$html .= "<br>\n";

	// 2012/09/26 add oda
	$html .= "ワンポイント解説<br>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_add",$_SESSION['authority'])===FALSE)) {
		$html .= "<table>\n";
		$html .= "<tr>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_form\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_addform\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"ワンポイント解説新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
		//$html .= "<td>\n";
		//$html .= "<form action=\"./one_point_make_csv.php\" method=\"POST\">";
		//$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		//$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		//$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		//$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"ワンポイント解説ダウンロード\">";
		//$html .= "</form>";
		//$html .= "</td>\n";
		//----------------------------------------------------------------
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_upload\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"one_point_file\">\n";
		$html .= "<input type=\"submit\" value=\"ワンポイント解説アップロード\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<br>\n";

		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		$html .= "<form action=\"./one_point_make_csv.php\" method=\"POST\">";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、ダウンロードボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";

			$html .= $expList;
		}
		$html .= "<input type=\"submit\" value=\"ワンポイント解説ダウンロード\">";
		$html .= "<br /><br />\n";

		$html .= "</form>";
		//-------------------------------------------------

		$one_point_img_ftp = FTP_URL."point_img/".$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";
		$one_point_voice_ftp = FTP_URL."point_voice/".$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";

		$html .= FTP_EXPLORER_MESSAGE; //add hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応

		$html .= "<a href=\"".$one_point_img_ftp."\" target=\"_blank\">ポイント解説画像フォルダー($one_point_img_ftp)</a><br>\n";
		$html .= "<a href=\"".$one_point_voice_ftp."\" target=\"_blank\">ポイント解説音声フォルダー($one_point_voice_ftp)</a><br>\n";
		$html .= "<br>\n";
	}

	$sql  = "SELECT * FROM ".T_ONE_POINT." op ".
			" WHERE op.mk_flg = '0' ".
			"   AND op.unit_num   = '".$_POST['unit_num']."'".
			" ORDER BY op.list_num";

	if ($result = $cdb->query($sql)) {
		$one_point_count = $cdb->num_rows($result);
	}
	if ($one_point_count == 0) {
		$html .= "現在、ポイント解説の登録はありません。<br><br>\n";
	} else {
		// イメージ・音声格納ディレクトリ作成
		create_directory();

		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";

		if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}

		$html .= "<th>ワンポイント<br>解説番号</th>\n";
		$html .= "<th>解説タイトル</th>\n";
		$html .= "<th>解説内容</th>\n";
		$html .= "<th>すらら復習ユニット</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th>確認</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		$i = 1;
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$html .= "<tr class=\"member_form_cell\">\n";

				$up_submit = $down_submit = "&nbsp;";
				if ($i != 1) {
					$up_submit = "<input type=\"submit\" value=\"↑\">\n";
				}
				if ($i < $one_point_count) {
					$down_submit = "<input type=\"submit\" value=\"↓\">\n";
				}
				if (!ereg("practice__view",$authority)
						&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_up\">\n";
					$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
					$html .= "<td>".$up_submit."</td>\n";
					$html .= "</form>\n";

					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_down\">\n";
					$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
					$html .= "<td>".$down_submit."</td>\n";
					$html .= "</form>\n";
				}

				$surala_unit = get_surala_unit($list['course_num'], $list['stage_num'], $list['lesson_num'], $list['unit_num'], 0, 0, $list['one_point_num']);
				$surala_unit = change_unit_num_to_key_all($surala_unit);

				$one_point_commentary = rtrim($list['one_point_commentary']);
				$one_point_commentary = replace_decode($one_point_commentary);
				$one_point_commentary = tag_convert($one_point_commentary, 20);
				if (mb_strlen($one_point_commentary, "UTF-8") > 19) {
					$one_point_commentary .= "...";
				}

				$html .= "<td>".$list['one_point_num']."</td>\n";
				$html .= "<td>".replace_decode($list['study_title'])."</td>\n";
				$html .= "<td>".$one_point_commentary."</td>\n";
				$html .= "<td>".$surala_unit."</td>\n";
				$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
				$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_one_point_win_open('".$_POST['unit_num']."', '".$list['one_point_num']."', '0')\"></td>\n";

				if (!ereg("practice__view",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<td>";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_form\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_updateform\">\n";
					$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
					$html .= "<input type=\"submit\" value=\"詳細\">\n";
					$html .= "</form>\n";
					$html .= "</td>";
				}
				if (!ereg("practice__del",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_del",$_SESSION['authority'])===FALSE))
				) {
					$html .= "<td>";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_delete\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"\">\n";
					$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
					$html .= "<input type=\"submit\" value=\"削除\">\n";
					$html .= "</form>\n";
					$html .= "</td>";
				}
				$html .= "</tr>\n";
				$i++;
			}
		}
		$html .= "</table>\n";
		$html .= "<br>\n";
	}
	// 2012/09/26 add oda

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"ユニット一覧へ戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

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

	if (!$_POST['course_num']) { $ERROR[] = "登録するユニットのコース情報が確認できません。"; }
	if (!$_POST['stage_num']) { $ERROR[] = "ステージ情報が確認できません。"; }

	if (!$_POST['unit_name']) { $ERROR[] = "ユニット名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."' AND unit_name='".$_POST['unit_name']."'";
		} else {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."'" .
					" AND unit_num!='".$_POST['unit_num']."' AND unit_name='".$_POST['unit_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたユニット名は既に登録されております。"; }
	}
	$lesson_key = lesson_key;
	$_POST['unit_key'] = mb_convert_kana($_POST['unit_key'],"as","UTF-8");
	$_POST['unit_key'] = trim($_POST['unit_key']);
	if (!$_POST['unit_key']) { $ERROR[] = "ユニットキーが未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."' AND unit_key='".$lesson_key.$_POST['unit_key']."'";
		} else {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."'" .
					" AND unit_num!='".$_POST['unit_num']."' AND unit_key='".$lesson_key.$_POST['unit_key']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたユニットキーは既に登録されております。"; }
	}
	if ($_POST['clear_unit_key']) {
		$_POST['clear_unit_key'] = mb_convert_kana($_POST['clear_unit_key'],"as","UTF-8");
		$_POST['clear_unit_key'] = trim($_POST['clear_unit_key']);
		$clear_unit_key_ = ":".$_POST['clear_unit_key'].":";
		$check_unit_key = ":".$lesson_key.$_POST['unit_key'].":";
		if (eregi("start",$_POST['clear_unit_key']) && eregi_replace("start","",$_POST['clear_unit_key']) != "") {
			$ERROR[] = "クリアーユニットキーに「start」を入力した場合、他のユニットキーは入力できません。";
		} elseif ($_POST['unit_key'] && eregi($check_unit_key,$clear_unit_key_)) {
			$ERROR[] = "クリアーユニットキーに、登録されるユニットのユニットキーを入力しないでください。";
		}
	}

	// 2012/10/23 add start oda
	if ($_POST['study_control']) {
		$study_control_count = 0;
		$study_control_list = explode("&lt;&gt;",$_POST['study_control']);

		// 学習制御に数値以外の値が入っていないかチェック
		$error_flag = false;
		for ($j=0; $j<count($study_control_list); $j++) {
			if (is_numeric($study_control_list[$j]) == false) {
				$ERROR[] = "学習制御に指定したユニットは、不正な情報が混ざっています。";
				$error_flag = true;
			}
		}

		// ユニットIDが存在するかチェック
		if (!$error_flag) {
			$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE state='0' AND unit_num IN ('" . implode("','", $study_control_list) ."')";
			if ($result = $cdb->query($sql)) {
				$study_control_count = $cdb->num_rows($result);
			}

			if (count($study_control_list) > 0) {
				if ($study_control_count != count($study_control_list)) {
					$ERROR[] = "学習制御に指定したユニットは、既に削除されているか、重複または不正な情報が混ざっています。";
				}
			}
		}
	}
	// 2012/10/23 add end oda

	// 2012/08/21 add start oda
	if ($_POST['surala_unit']) {
		$surala_unit_count = 0;
		$surala_unit_list = explode("::",change_unit_key_to_num_all($_POST['surala_unit']));
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
	// 2012/08/21 add end oda

	// if (!$_POST['standard_study_time']) { $ERROR[] = "標準学習時間表示が未選択です。"; }		// 2012/08/21 add oda		// del start oda 2017/05/15 未使用箇所削除
	// if (!$_POST['study_time']) { $ERROR[] = "学習時間表示が未選択です。"; }					// 2012/08/21 add oda		// del start oda 2017/05/15 未使用箇所削除
	// if (!$_POST['remainder_unit']) { $ERROR[] = "残りユニット数表示が未選択です。"; }			// 2012/08/21 add oda	// del start oda 2017/05/15 未使用箇所削除
	if (!$_POST['medal_status']) { $ERROR[] = "メダル表示が未選択です。"; }					// 2012/08/27 add oda
	// if (!$_POST['last_study_date']) { $ERROR[] = "最終学習日表示が未選択です。"; }			// 2012/08/27 add oda		// del start oda 2017/05/15 未使用箇所削除
	// if (!$_POST['last_correct_count']) { $ERROR[] = "最終正解率表示が未選択です。"; }			// 2012/08/27 add oda	// del start oda 2017/05/15 未使用箇所削除
	// if (!$_POST['last_study_time']) { $ERROR[] = "最終学習時間表示が未選択です。"; }			// 2012/08/27 add oda		// del start oda 2017/05/15 未使用箇所削除
	if (!$_POST['ranking_button']) { $ERROR[] = "ランキングボタン表示が未選択です。"; }			// 2012/08/27 add oda
	// add start 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	if ($_POST['again_lecture_time'] == "") {
		$ERROR[] = "レクチャーを促す学習時間が未入力です。";
	} else if (is_numeric($_POST['again_lecture_time']) == false) {
		$ERROR[] = "レクチャーを促す学習時間には数値を設定してください。";
	}
	if ($_POST['again_lecture_count'] == "") {
		$ERROR[] = "レクチャーを促す連続不正解回数が未入力です。";
	} else if (is_numeric($_POST['again_lecture_count']) == false) {
		$ERROR[] = "レクチャーを促す連続不正解回数には数値を設定してください。";
	}
	// add end 2016/01/15 yoshizawa レクチャー閲覧を促す機能

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}


/**
 * 入力チェック処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function block_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) { $ERROR[] = "登録するユニットのコース情報が確認できません。"; }
	if (!$_POST['stage_num']) { $ERROR[] = "ステージ情報が確認できません。"; }
	if (!$_POST['block_type']) { $ERROR[] = "学習タイプが未選択です。"; }
	else {
		if ($_POST['unit_type'] == 3) {
			$_POST['setting_type'] = 1;
		} else {
			$_POST['setting_type'] = 2;
		}
	}
	if ($_POST['block_type'] == 1) {
		if ($_POST['block_type'] == 1) {
			if (!$_POST['rate']) { $ERROR[] = "基本正解率が未入力です。"; }
			elseif ($_POST['rate'] > 100) { $ERROR[] = "基本正解率の最大値は「100」です。"; }
			$check = 0;
			if (!$_POST['pool']) { $ERROR[] = "基準プールサイズが未入力です。"; $check = 1; }
			if (!$_POST['max_pool']) { $ERROR[] = "最大プールサイズが未入力です。"; $check = 1; }
			if (!$_POST['min_pool']) { $ERROR[] = "最小プールサイズが未入力です。"; $check = 1; }
			if ($check == 1) {
				if ($_POST['pool'] > $_POST['max_pool'] || $_POST['pool'] < $_POST['min_pool'] || $_POST['max_pool'] < $_POST['min_pool']) { $ERROR[] = "プールサイズが不正です。"; }
			}
		}
		if (!$_POST['pool_rate_number']) { $ERROR[] = "プールサイズ正解率計算直近問題数が未入力です。"; }
		if (!$_POST['lowest_study_number']) { $ERROR[] = "最低学習問題数が未入力です。"; }
		if (!$_POST['clear_rate_number']) { $ERROR[] = "ドリルクリアー正解率計算直近問題数が未入力です。"; }
		if (!$_POST['clear_rate']) { $ERROR[] = "ドリルクリアー正解率が未入力です。"; }
		elseif ($_POST['clear_rate'] > 100) { $ERROR[] = "ドリルクリアー正解率の最大値は「100」です。"; }
		if (!$_POST['difficulty_type']) { $ERROR[] = "難度利用タイプが未入力です。"; }

	//	add ookawara 2012/07/06 start
	// } elseif ($_POST['block_type'] == 4) {									// 2012/08/22 del oda
	// } elseif ($_POST['block_type'] == 4 || $_POST['block_type'] == 5) {		// 2012/08/22 add oda	// upd hasegawa 2018/02/23 百マス計算
	} elseif ($_POST['block_type'] == 4 || $_POST['block_type'] == 5 || $_POST['block_type'] == 7) {

		if (!$_POST['bgm_set']) { $ERROR[] = "ＢＧＭ設定が未選択です。"; }				// 2012/08/22 add oda
		if (!$_POST['count_down_set']) { $ERROR[] = "カウントダウン／タイマー表示が未選択です。"; }		// 2012/08/22 add oda  // update 2017/04/21 yoshizawa タイトル変更

	//	add ookawara 2012/07/06 end

	}

	// if ($_POST['block_type'] == 4 || $_POST['block_type'] == 5 || $_POST['block_type'] == 6) {		// add koyama 2013/10/22	// upd hasegawa 2018/02/23 百マス計算
	if ($_POST['block_type'] == 4
		|| $_POST['block_type'] == 5
		|| $_POST['block_type'] == 6
		|| $_POST['block_type'] == 7
		|| $_POST['block_type'] == 8 // add 2018/10/09 yoshizawa すらら英単語追加
	) {
		// 出題順序チェック------------------------------------------- add koyama 2013/10/18
		if (!$_POST['order_type']) {
			$ERROR[] = "出題順序が未選択です。";
		}
		//------------------------------------------------------------ add koyama 2013/10/18
	}

	// >>> add 2018/10/09 yoshizawa すらら英単語追加
	if ($_POST['block_type'] == 8){
		// 次問題への移行時間をチェック
		if (!$_POST['switching_time']) {
			$ERROR[] = "次問題への移行時間が未入力です。";
		} else if( !preg_match('/^([1-9]\d*|0)(\.\d+)?$/',$_POST['switching_time']) ) {
			$ERROR[] = "次問題への移行時間に不正な値が含まれています。";
		}
	}
	// <<<

	// 2012/08/22 add start oda
	if ($_POST['surala_unit']) {
		$surala_unit_count = 0;
		$surala_unit_list = explode("::",change_unit_key_to_num_all($_POST['surala_unit']));
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
	// 2012/08/22 add end oda

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}

/**
 * 確認の為のHTML を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function check_html($L_COURSE,$L_STAGE,$L_LESSON) {

//	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;									// 2012/08/21 del oda
	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY,$L_STATUS_SETUP,$L_ONOFF_TYPE;	// 2012/08/21 update oda
	global $L_SPEECH_EVALUATION; // kaopiz 2020/08/20 speech
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
		$sql = "SELECT * FROM ".T_UNIT.
			" WHERE unit_num='{$_POST['unit_num']}' AND state!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			if ($key == "stage_num") { $key = "s_stage_num"; }
			$$key = replace_decode($val);
		}
//        kaopiz 2020/08/20 speech start
        $externalUnit = findExternalUnitNotDelete($_POST[unit_num]);
        if ($externalUnit) {
            foreach ($externalUnit as $key => $val) {
                $$key = replace_decode($val);
            }
        }
//        kaopiz 2020/08/20 speech end
		// update start oda 2017/05/15 未使用箇所削除
		// 2012/08/21 add start oda
		// $standard_study_time = "2";
		// $study_time = "2";
		// $remainder_unit = "2";
		$medal_status = "2";			// 2012/08/27 add oda
		// $last_study_date = "2";			// 2012/08/27 add oda
		// $last_correct_count = "2";		// 2012/08/27 add oda
		// $last_study_time = "2";			// 2012/08/27 add oda
		$ranking_button = "2";			// 2012/08/27 add oda

		if ($status_display) {
			$dispray_list = explode("<>",$status_display);
			for ($j=0; $j<count($dispray_list); $j++) {
				// if ($dispray_list[$j] == "standard_study_time") {
				// 	$standard_study_time = "1";
				// }
				// if ($dispray_list[$j] == "study_time") {
				// 	$study_time = "1";
				// }
				// if ($dispray_list[$j] == "remainder_unit") {
				// 	$remainder_unit = "1";
				// }
				//2012/08/27 add start oda
				if ($dispray_list[$j] == "medal_status") {
					$medal_status = "1";
				}
				// if ($dispray_list[$j] == "last_study_date") {
				// 	$last_study_date = "1";
				// }
				// if ($dispray_list[$j] == "last_correct_count") {
				// 	$last_correct_count = "1";
				// }
				// if ($dispray_list[$j] == "last_study_time") {
				// 	$last_study_time = "1";
				// }
				if ($dispray_list[$j] == "ranking_button") {
					$ranking_button = "1";
				}
				// 2012/08/27 add end oda
			}
		}
		// update end oda 2017/05/15

		$status_display = "";

    	$surala_unit = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
    	$surala_unit = change_unit_num_to_key_all($surala_unit);
		// 2012/08/21 add end oda
	}

	//	add ookawara 2012/12/05 start
	$rank_form = "未設定";
	if ($mt_ranking_point_num == "-1") {
		$rank_form = "デフォルト（正解率＋時間）";
	} else {
		if ($mt_ranking_point_num) {
			$sql  = "SELECT mt_ranking_point_num, pattern_name FROM ".T_MS_RANKING_POINT.
					" WHERE mk_flg='0'".
					" AND default_flag ='1'".						// add oda 2013/10/15
					" AND mt_ranking_point_num = '".$mt_ranking_point_num."'".
					" ;";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$rank_form = $list['pattern_name'];
				}
			}
		}
	}
	$INPUTS['RANKINGPOINTNUM'] = array('result'=>'plane','value'=>$rank_form);
	//	add ookawara 2012/12/05 end


	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//    kaopiz 2020/08/20 del
//    $make_html->set_file(PRACTICE_UNIT_FORM);
//    kaopiz 2020/08/20 speech start
	if($GLOBALS['speechEvaluation']) {
        $make_html->set_file(PRACTICE_UNIT_FORM_1);
    } else {
        $make_html->set_file(PRACTICE_UNIT_FORM);
    }
//    kaopiz 2020/08/20 speech end

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>$unit_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$_POST['lesson_num']]);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	$INPUTS['PARENTSUNITNUM'] = array('result'=>'plane','value'=>$parents_unit_name_);
	$INPUTS['CLASSM'] = array('result'=>'plane','value'=>$L_UNIT_TYPE[$unit_type]);
//	$INPUTS[SETTINGTYPE] = array('result'=>'plane','value'=>$L_SETTING_TYPE[$setting_type]);
	$INPUTS['RATE'] = array('result'=>'plane','value'=>$rate);
	$INPUTS['POOL'] = array('result'=>'plane','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('result'=>'plane','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('result'=>'plane','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('result'=>'plane','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('result'=>'plane','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('result'=>'plane','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('result'=>'plane','value'=>$clear_rate);
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$L_DIFFICULTY_TYPE[$difficulty_type]);
	$INPUTS['UNITKEY'] = array('result'=>'plane','value'=>$unit_key);
	//	add ookawara 2010/02/04
	$unit_name2 = str_replace("&lt;","<",$unit_name2);
	$unit_name2 = str_replace("&gt;",">",$unit_name2);
	$INPUTS['UNITNAME2'] = array('result'=>'plane','value'=>$unit_name2);

	$INPUTS['UNITNAME3'] = array('result'=>'plane','value'=>$unit_name3);

	$INPUTS['LEVEL'] = array('result'=>'plane','value'=>$level);						// add oda 2012/10/19
	$INPUTS['STUDYCONTROL'] = array('result'=>'plane','value'=>$study_control);			// add oda 2012/10/19
	$INPUTS['LASTLEVEL'] = array('result'=>'plane','value'=>$last_level);				// add oda 2012/10/31

	$INPUTS['CLEARUNITKEY'] = array('result'=>'plane','value'=>$clear_unit_key);
	$lesson_key = lesson_key;
	$INPUTS['LESSONKEY'] = array('result'=>'plane','value'=>$lesson_key);
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	// 2012/08/21 add start oda
	$INPUTS['SURALAUNIT'] = array('result'=>'plane','value'=>$surala_unit);

	// del start oda 2017/05/15 未使用箇所削除
	// $INPUTS[STANDARDSTUDYTIME] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$standard_study_time]);
	// $INPUTS[STUDYTIME] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$study_time]);
	// $INPUTS[REMAINDERUNIT] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$remainder_unit]);
	// 2012/08/21 add end oda
	// del end oda 2017/05/15

	// 2012/08/27 add start oda
	$INPUTS['MEDALSTATUS'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$medal_status]);

	// del start oda 2017/05/15 未使用箇所削除
	// $INPUTS['LASTSTUDYDATE'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$last_study_date]);
	// $INPUTS['LASTCORRECTCOUNT'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$last_correct_count]);
	// $INPUTS['LASTSTUDYTIME'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$last_study_time]);
	// del end oda 2017/05/15

	$INPUTS['RANKINGBUTTON'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$ranking_button]);
	// 2012/08/27 add end oda
	// add start 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	$INPUTS['AGAINLECTURETIME'] = array('result'=>'plane','value'=>$again_lecture_time);
	$INPUTS['AGAINLECTURECOUNT'] = array('result'=>'plane','value'=>$again_lecture_count);
	// add end 2016/01/15 yoshizawa レクチャー閲覧を促す機能
	$INPUTS['REMARKS']  = array('result'=>'plane', 'value'=>$remarks); //add kimura 2018/10/31 すらら英単語
    $INPUTS[OPTION1] = array('result'=>'plane','value'=>$L_SPEECH_EVALUATION[$option1]); // kaopiz 2020/08/20 speech
	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
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
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

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
		if ($key == "mode") { continue; }								// 2012/11/16 add oda
		if ($key == "action") { continue; }
        if ($key == "option1") { continue; } // kaopiz 2020/08/20 speech
        elseif ($key == "s_stage_num") { $key = "stage_num"; }
		elseif ($key == "unit_key") { $val = lesson_key.$val; }
		elseif ($key == "clear_unit_key" && $val) { $val = ":".$val.":"; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['update_time'] = "now()";

	// 2012/08/21 add start oda
	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($_POST['surala_unit']);
	unset($INSERT_DATA['surala_unit']);

	//　ステータス表示区分の編集
	$status_display = "";

	// del start oda 2017/05/15 未使用箇所削除
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

	// unset($INSERT_DATA['standard_study_time']);
	// unset($INSERT_DATA['study_time']);
	// unset($INSERT_DATA['remainder_unit']);
	// // 2012/08/21 add end oda
	// del end oda 2017/05/15

	// 2012/08/27 add start oda
	if ($INSERT_DATA['medal_status'] == "1") {
		if ($status_display) {
			$status_display .= "<>";
		}
		$status_display .= "medal_status";
	}
	unset($INSERT_DATA['medal_status']);

	// update start oda 2017/05/15 未使用箇所削除 最終学習日付／最終正解率／最終学習時間は表示とする
	// if ($INSERT_DATA['last_study_date'] == "1") {
	// 	if ($status_display) {
	// 		$status_display .= "<>";
	// 	}
	// 	$status_display .= "last_study_date";
	// }
	// unset($INSERT_DATA['last_study_date']);

	// if ($INSERT_DATA['last_correct_count'] == "1") {
	// 	if ($status_display) {
	// 		$status_display .= "<>";
	// 	}
	// 	$status_display .= "last_correct_count";
	// }
	// unset($INSERT_DATA['last_correct_count']);

	// if ($INSERT_DATA['last_study_time'] == "1") {
	// 	if ($status_display) {
	// 		$status_display .= "<>";
	// 	}
	// 	$status_display .= "last_study_time";
	// }
	// unset($INSERT_DATA['last_study_time']);

	if ($status_display) {
		$status_display .= "<>";
	}
	$status_display .= "last_study_date";
	$status_display .= "<>last_correct_count";
	$status_display .= "<>last_study_time";
	// update end oda 2017/05/15

	if ($INSERT_DATA['ranking_button'] == "1") {
		if ($status_display) {
			$status_display .= "<>";
		}
		$status_display .= "ranking_button";
	}
	unset($INSERT_DATA['ranking_button']);
	// 2012/08/27 add end oda

	$INSERT_DATA['status_display'] = $status_display;

	$ERROR = $cdb->insert(T_UNIT,$INSERT_DATA);

	if (!$ERROR) {
		$unit_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $unit_num;
		$where = " WHERE unit_num='".$unit_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);

		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $unit_num, 0, 0, 0, $surala_unit);
//        kaopiz 2020/08/20 speech start
        $option1 = isset($_POST['option1']) ? $_POST['option1'] : null;
        if (!$ERROR && $option1 !== null) {
            $ERROR = insert_external_unit($unit_num, $_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['option1']);
        }
//        kaopiz 2020/08/20 speech end
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

    $externalUnitTable = T_EXTERNAL_UNIT; // kaopiz 2020/08/20 speech

	$action = ACTION;

	if (MODE == "詳細") {
		foreach ($_POST as $key => $val) {
			if ($key == "mode") { continue; }								// 2012/11/16 add oda
			if ($key == "action") { continue; }
			if ($key == "option1") { continue; } // kaopiz 2020/08/20 speech
			elseif ($key == "stage_num") { continue; }
			elseif ($key == "s_stage_num") { $key = "stage_num"; }
			elseif ($key == "unit_key") { $val = lesson_key.$val; }
			elseif ($key == "clear_unit_key" && $val) { $val = ":".$val.":"; }
            // --- UPD20081009_2 SQLインジェクション対策
			// $INSERT_DATA[$key] = "$val";
			$INSERT_DATA[$key] = sqlGuard( $val );
            // --- UPD20081009_2 ここまで
		}
		$INSERT_DATA['update_time'] = "now()";

		// 2012/08/21 add start oda
		// すららで復習ユニット登録
		$surala_unit = change_unit_key_to_num_all($_POST['surala_unit']);
		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0, $surala_unit);
		unset($INSERT_DATA['surala_unit']);

		// すららで復習ユニット登録
		$status_display = "";

		// del start oda 2017/05/15 未使用箇所削除
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

		// unset($INSERT_DATA['standard_study_time']);
		// unset($INSERT_DATA['study_time']);
		// unset($INSERT_DATA['remainder_unit']);
		// // 2012/08/21 add end oda
		// del end oda 2017/05/15

		// 2012/08/27 add start oda
		if ($INSERT_DATA['medal_status'] == "1") {
			if ($status_display) {
				$status_display .= "<>";
			}
			$status_display .= "medal_status";
		}
		unset($INSERT_DATA['medal_status']);

		// update start oda 2017/05/15 未使用箇所削除 最終学習日付／最終正解率／最終学習時間は表示とする
		// if ($INSERT_DATA['last_study_date'] == "1") {
		// 	if ($status_display) {
		// 		$status_display .= "<>";
		// 	}
		// 	$status_display .= "last_study_date";
		// }
		// unset($INSERT_DATA['last_study_date']);

		// if ($INSERT_DATA['last_correct_count'] == "1") {
		// 	if ($status_display) {
		// 		$status_display .= "<>";
		// 	}
		// 	$status_display .= "last_correct_count";
		// }
		// unset($INSERT_DATA['last_correct_count']);

		// if ($INSERT_DATA['last_study_time'] == "1") {
		// 	if ($status_display) {
		// 		$status_display .= "<>";
		// 	}
		// 	$status_display .= "last_study_time";
		// }
		// unset($INSERT_DATA['last_study_time']);

		if ($status_display) {
			$status_display .= "<>";
		}
		$status_display .= "last_study_date";
		$status_display .= "<>last_correct_count";
		$status_display .= "<>last_study_time";
		// update end oda 2017/05/15

		if ($INSERT_DATA['ranking_button'] == "1") {
			if ($status_display) {
				$status_display .= "<>";
			}
			$status_display .= "ranking_button";
		}
		unset($INSERT_DATA['ranking_button']);
		// 2012/08/27 add end oda

		$INSERT_DATA['status_display'] = $status_display;

		$where = " WHERE unit_num='".$_POST['unit_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA['display'] = 2;
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE unit_num='".$_POST['unit_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);

		// 2012/08/22 add start oda
		// すららで復習ユニット登録
		$ERROR = delete_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
		// 2012/08/22 add end oda
	}
//    kaopiz 2020/08/20 speech start
    if (!$ERROR && $GLOBALS['speechEvaluation']) {
        $currentUserId = $_SESSION['myid']['id'];
        $option1 = isset($_POST['option1']) ? $_POST['option1'] : null;
        $externalUnit = findExternalUnit($_POST['unit_num']);

        if (MODE == "詳細" && !$externalUnit) {
            $ERROR = insert_external_unit($_POST['unit_num'], $_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $option1);
        } elseif ($externalUnit) {
            if (MODE == "詳細") {
                $INSERT_DATA2['option1'] = $option1;
                $INSERT_DATA2['upd_tts_id'] = $currentUserId;
                $INSERT_DATA2['upd_date'] = "now()";
                $INSERT_DATA2['move_flg'] = 0;
            } elseif (MODE == "削除") {
                $INSERT_DATA2['move_flg'] = 1;
                $INSERT_DATA2['move_tts_id'] = $currentUserId;
                $INSERT_DATA2['move_date'] = "now()";
            }
            $where = " WHERE unit_num='" . $_POST['unit_num'] . "' LIMIT 1;";
            $ERROR = $cdb->update($externalUnitTable, $INSERT_DATA2, $where);
        }
    }
//    kaopiz 2020/08/20 speech end

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

	$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE unit_num='".$_POST['unit_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_course_num = $list['course_num'];
			$m_lesson_num = $list['lesson_num'];
			$m_unit_num = $list['unit_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_unit_num || !$m_list_num) { $ERROR[] = "移動するユニット情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state!='1' AND lesson_num='".$m_lesson_num."' AND list_num<'".$m_list_num."'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_unit_num = $list['unit_num'];
				$c_list_num = $list['list_num'];
			}
		}
	}
	if (!$c_unit_num || !$c_list_num) { $ERROR[] = "移動されるユニット情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE unit_num='".$m_unit_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE unit_num='".$c_unit_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);
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

	$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE unit_num='".$_POST['unit_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_course_num = $list['course_num'];
			$m_lesson_num = $list['lesson_num'];
			$m_unit_num = $list['unit_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_unit_num || !$m_list_num) { $ERROR[] = "移動するユニット情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state!='1' AND lesson_num='".$m_lesson_num."' AND list_num>'".$m_list_num."'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_unit_num = $list['unit_num'];
				$c_list_num = $list['list_num'];
			}
		}
	}
	if (!$c_unit_num || !$c_list_num) { $ERROR[] = "移動されるユニット情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE unit_num='".$m_unit_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE unit_num='".$c_unit_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_UNIT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * 復習一覧表示の為
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function review_list_html($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "診断復習設定<br>\n";

	//	ユニット名
	$sql  = "SELECT unit_name FROM ".T_UNIT.
			" WHERE unit_num='{$_POST['unit_num']}' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$unit_name = $list['unit_name'];
		}
	}

	$html .= "<table  class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>ユニット名</td>\n";
	$html .= "<td class=\"unit_form_cell\">{$unit_name}</td>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "<td>ドリル番号</td>\n";
	$html .= "<td class=\"unit_form_cell\"> {$_POST['block_num']} </td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"review_form\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"review_addform\">\n";
	$html .= "<input type=\"submit\" value=\"スキル新規登録\">\n";
	$html .= "</form><br>\n";

	//	登録スキルデーター読み込み
	unset($max);
	$sql  = "SELECT * FROM ".T_REVIEW_SETTING.
			" WHERE block_num='{$_POST['block_num']}'" .
			" AND state!='1' ORDER BY skill_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if ($max) {
		//	スキル名取得
		unset($L_SKILL_NAME);
		$sql  = "SELECT list_num, skill_name FROM ".T_SKILL.
				" WHERE course_num='".$_POST['course_num']."' AND state!='1';";
		if ($result2 = $cdb->query($sql)) {
			while ($list2 = $cdb->fetch_assoc($result2)) {
				$L_SKILL_NAME[$list2['list_num']] = $list2['skill_name'];
			}
		}

		$html .= "<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>スキル番号</th>\n";
		$html .= "<th>スキル名</th>\n";
		$html .= "<th>基本<br>正解率</th>\n";
		$html .= "<th>最低学習<br>問題数</th>\n";
		$html .= "<th>ドリルクリアー<br>正解率</th>\n";
		$html .= "<th>難度利用<br>タイプ</th>\n";
		$html .= "<th>問題閾値</th>\n";
		$html .= "<th>復習レッスン<br>ユニット</th>\n";
		$html .= "<th>復習レッスン<br>ページ</th>\n";
		if (!ereg("practice__view",$authority)) {
			$html .= "<th>詳細</th>\n";
		}
		if (!ereg("practice__del",$authority)) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($list AS $key => $val) {
				$$key = $val;
			}

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"review_form\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$course_num}\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$stage_num}\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$lesson_num}\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$unit_num}\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_num}\">\n";
			$html .= "<input type=\"hidden\" name=\"review_setting_num\" value=\"{$review_setting_num}\">\n";

			$skill_name = $L_SKILL_NAME[$skill_num];
			if (!$skill_name) { $skill_name = "未登録"; }

			//	戻りユニット名
			$sql  = "SELECT n_stage.stage_name, n_lesson.lesson_name, n_unit.unit_name FROM ".
					T_STAGE." n_stage, ".T_LESSON." n_lesson,".T_UNIT." n_unit".
					" WHERE n_stage.stage_num=n_unit.stage_num".
					" AND n_lesson.lesson_num=n_unit.lesson_num".
					" AND n_unit.unit_num='{$review_unit_num}' LIMIT 1";

			$return_stage_name = "";	// add 2018/05/25 yoshizawa 後続のリストに影響がないように変数初期化
			$return_lesson_name = "";	// add 2018/05/25 yoshizawa 後続のリストに影響がないように変数初期化
			$return_unit_name = "";		// add 2018/05/25 yoshizawa 後続のリストに影響がないように変数初期化
			if ($result3 = $cdb->query($sql)) {
				if ($list3 = $cdb->fetch_assoc($result3)) {
					$return_stage_name = $list3['stage_name'];
					$return_lesson_name = $list3['lesson_name'];
					$return_unit_name = $list3['unit_name'];
				}
			}

			$html .= "<td>{$review_setting_num}</td>\n";
			$html .= "<td>{$skill_num}</td>\n";
			$skill_name = str_replace("&lt;","<",$skill_name);
			$skill_name = str_replace("&gt;",">",$skill_name);
			$html .= "<td>{$skill_name}</td>\n";
			$html .= "<td>{$rate}%</td>\n";
			$html .= "<td>{$lowest_study_number}問</td>\n";
			$html .= "<td>{$clear_rate}%</td>\n";
			$html .= "<td>{$L_DIFFICULTY_TYPE[$difficulty_type]}</td>\n";
			$html .= "<td>{$threshold}</td>\n";
			$html .= "<td>{$return_stage_name} > {$return_lesson_name} > {$return_unit_name}</td>\n";
			$html .= "<td>{$lesson_page}</td>\n";

			$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
			$html .= "</form>\n";

			if (!ereg("practice__del",$authority)) {
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"review_delete\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$course_num}\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$stage_num}\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$lesson_num}\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$unit_num}\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_num}\">\n";
				$html .= "<input type=\"hidden\" name=\"review_setting_num\" value=\"{$review_setting_num}\">\n";
				$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
				$html .= "</form>\n";
			}
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	} else {
		$html .= "<br>\n";
		$html .= "今現在登録されているスキルは有りません。<br>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST['course_num']}\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$_POST['stage_num']}\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$_POST['lesson_num']}\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST['unit_num']}\">\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "<input type=\"submit\" value=\"ドリル一覧へ戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;
}

/**
 * 復習フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function review_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ユニットネーム
	$sql = "SELECT * FROM " . T_UNIT .
		" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' LIMIT 1;";

	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);

	$unit_name = $list['unit_name'];

	if ($_POST['review_setting_num']) {
		$html .= "スキル変更登録フォーム<br>\n";
	} else {
		$html .= "スキル新規登録フォーム<br>\n";
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
//		if (!$giveup_time) { 	$giveup_time = 10; }	//	del ookawara 2009/07/27
		if (!$giveup_time) { 	$giveup_time = 30; }	//	add ookawara 2009/07/27
		if (!$giveup_ans_num) {
			$giveup_ans_num = 20;	//	add ookawara 2009/07/27
//			if ($course_num == "2") { $giveup_ans_num = 15; } else { $giveup_ans_num = 20; }	//	del ookawara 2009/07/27
		}
	} else {
		if ($_POST['review_setting_num']) {
			$sql = "SELECT * FROM " . T_REVIEW_SETTING .
				" WHERE review_setting_num='".$_POST['review_setting_num']."' AND state!='1' LIMIT 1;";
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
		if ($review_unit_num) {
			$sql  = "SELECT stage_num, lesson_num FROM ".T_UNIT.
					" WHERE unit_num='{$review_unit_num}' LIMIT 1;";
			$result = $cdb->query($sql);
			if ($list = $cdb->fetch_assoc($result)) {
				$return_stage_num = $list['stage_num'];
				$return_lesson_num = $list['lesson_num'];
			}
		}
	}

	if ($msg) {
		$html .= $msg."<br>\n";
		$html .= "<br>\n";
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	//	学習タイプ
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"review\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"review_addform\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$block_num."\">\n";
	if ($review_setting_num) {
		$html .= "<input type=\"hidden\" name=\"review_setting_num\" value=\"".$review_setting_num."\">\n";
	}

	//スキル名
	$skill_name = "未登録";
	if ($skill_num) {
		$sql  = "SELECT skill_name FROM ".T_SKILL.
				" WHERE list_num='$skill_num' AND course_num='".$_POST['course_num']."'" .
				" AND state!='1' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$skill_name = $list['skill_name'];
			}
		}
	}

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_BLOCK_REVIEW_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$review_setting_num) { $new_num = "---"; } else { $new_num = $review_setting_num; }
	$INPUTS['REVIEWNUM'] = array('result'=>'plane','value'=>$new_num);
	$INPUTS['SKILLNUM'] = array('type'=>'text','name'=>'skill_num','size'=>'6','value'=>$skill_num);
	$skill_name = str_replace("&lt;","<",$skill_name);
	$skill_name = str_replace("&gt;",">",$skill_name);
	$INPUTS['SKILLNAME'] = array('result'=>'plane','value'=>$skill_name);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$course_num]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$stage_num]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$lesson_num]);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$block_num);
	$INPUTS['RATE'] = array('type'=>'text','name'=>'rate','size'=>'6','value'=>$rate);
	$INPUTS['POOL'] = array('type'=>'text','name'=>'pool','size'=>'6','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('type'=>'text','name'=>'max_pool','size'=>'6','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('type'=>'text','name'=>'min_pool','size'=>'6','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('type'=>'text','name'=>'pool_rate_number','size'=>'6','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('type'=>'text','name'=>'lowest_study_number','size'=>'6','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('type'=>'text','name'=>'clear_rate_number','size'=>'6','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('type'=>'text','name'=>'clear_rate','size'=>'6','value'=>$clear_rate);
	$INPUTS['THRESHOLD'] = array('type'=>'text','name'=>'threshold','size'=>'6','value'=>$threshold);
	$INPUTS['GIVEUPTIME'] = array('type'=>'text','name'=>'giveup_time','size'=>'6','value'=>$giveup_time);
	$INPUTS['GIVEUPNUMBER'] = array('type'=>'text','name'=>'giveup_ans_num','size'=>'6','value'=>$giveup_ans_num);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type1");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('1');
	$difficulty_type1 = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type2");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('2');
	$difficulty_type2 = $newform->make();
	$difficulty_type = $difficulty_type1 . "<label for=\"difficulty_type1\">{$L_DIFFICULTY_TYPE[1]}</label> / " . $difficulty_type2 . "<label for=\"difficulty_type2\">{$L_DIFFICULTY_TYPE[2]}</label>";
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$difficulty_type);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$indisplay = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $indisplay . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);
	//	ステージ
	$return_unit_html .= "<select name=\"return_stage_num\" onchange=\"submit_return_stage();\">\n";
	if (!$return_stage_num) { $selected = "selected"; } else { $selected = ""; }
	$stage_num_html = "<option value=\"\" $selected></option>\n";
	$sql  = "SELECT stage_num, stage_name FROM ".T_STAGE.
			" WHERE course_num='".$course_num."' AND state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$stage_num_ = $list['stage_num'];
			$stage_name_ = $list['stage_name'];
			if ($return_stage_num == $stage_num_) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"{$stage_num_}\" $selected>{$stage_name_}</option>\n";
		}
	}
	$return_unit_html .= $stage_num_html;
	$return_unit_html .= "</select>\n";
	//	コース
	$return_unit_html .= "<select name=\"return_lesson_num\" onchange=\"submit_return_lesson();\">\n";
	if (!$return_lesson_num) { $selected = "selected"; } else { $selected = ""; }
	$lesson_num_html = "<option value=\"\" $selected></option>\n";
	if ($return_stage_num) {
		$sql  = "SELECT lesson_num, lesson_name FROM ".T_LESSON.
				" WHERE stage_num='$return_stage_num' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$lesson_num_ = $list['lesson_num'];
				$lesson_name_ = $list['lesson_name'];
				if ($return_lesson_num == $lesson_num_) { $selected = "selected"; } else { $selected = ""; }
				$lesson_num_html .= "<option value=\"{$lesson_num_}\" $selected>{$lesson_name_}</option>\n";
			}
		}
	}
	$return_unit_html .= $lesson_num_html;
	$return_unit_html .= "</select>\n";
	//	ユニット
	$return_unit_html .= "<select name=\"review_unit_num\">\n";
	if (!$review_unit_num) { $selected = "selected"; } else { $selected = ""; }
	$unit_num_html = "<option value=\"\" $selected></option>\n";
	if ($return_lesson_num) {
		$sql  = "SELECT unit_num, unit_name FROM ".T_UNIT.
				" WHERE lesson_num='$return_lesson_num' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$unit_num_ = $list['unit_num'];
				$unit_name_ = $list['unit_name'];
				if ($review_unit_num == $unit_num_) { $selected = "selected"; } else { $selected = ""; }
				$unit_num_html .= "<option value=\"{$unit_num_}\" $selected>{$unit_name_}</option>\n";
			}
		}
	}
	$return_unit_html .= $unit_num_html;
	$return_unit_html .= "</select>\n";
	$INPUTS['RETURNSTATUS'] = array('result'=>'plane','value'=>$return_unit_html);

	$newform = new form_parts();
	$newform->set_form_type("text");
	$newform->set_form_name("lesson_page");
	$newform->set_form_size("20");
	$newform->set_form_value($lesson_page);
	$lesson_page_html = $newform->make();
	$lesson_page_html .= "複数ある場合は、カンマ区切りで入力してください。";
	$INPUTS['LESSONPAGE'] = array('result'=>'plane','value'=>$lesson_page_html);
//	$INPUTS[LESSONPAGE] = array('type'=>'text','name'=>'lesson_page','size'=>'10','value'=>$lesson_page);

	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	if ($review_setting_num) {
		$html .= "<input type=\"submit\" name=\"action\" value=\"スキル変更確認\">\n";
	} else {
		$html .= "<input type=\"submit\" name=\"action\" value=\"スキル追加確認\">\n";
	}
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "<input type=\"button\" value=\"FLASH確認\" onclick=\"check_review_flash_win_open()\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"review\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$_POST[course_num]}\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"{$_POST[stage_num]}\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"{$_POST[lesson_num]}\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"{$_POST[unit_num]}\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"{$_POST[block_num]}\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;
}

/**
 * 復習確認
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function review_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) { $ERROR[] = "登録するユニットのコース情報が確認できません。"; }
	if (!$_POST['stage_num']) { $ERROR[] = "ステージ情報が確認できません。"; }
	if (!$_POST['lesson_num']) { $ERROR[] = "レッスン情報が確認できません。"; }
	if (!$_POST['unit_num']) { $ERROR[] = "ユニット情報が確認できません。"; }
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	if (!$_POST['block_num']) { $ERROR[] = "ドリル情報が確認できません。"; }
	if (!$_POST['skill_num']) { $ERROR[] = "スキル番号が確認できません。"; }
	else {
		if ($_POST['review_setting_num']) { $where = " AND review_setting_num!='{$_POST['review_setting_num']}'"; }
		$sql  = "SELECT * FROM ".T_REVIEW_SETTING.
				" WHERE block_num='{$_POST['block_num']}'" .
				" AND skill_num='{$_POST['skill_num']}'" .
				$where.
				" AND state!='1';";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if ($max > 0) { $ERROR[] = "入力されたスキル番号はすでに登録されております。"; }
	}
	if (!$_POST['rate']) { $ERROR[] = "基本正解率が未入力です。"; }
	elseif ($_POST['rate'] > 100) { $ERROR[] = "基本正解率の最大値は「100」です。"; }
	$check = 0;
	if (!$_POST['pool']) { $ERROR[] = "基準プールサイズが未入力です。"; $check = 1; }
	if (!$_POST['max_pool']) { $ERROR[] = "最大プールサイズが未入力です。"; $check = 1; }
	if (!$_POST['min_pool']) { $ERROR[] = "最小プールサイズが未入力です。"; $check = 1; }
	if ($check == 1) {
		if ($_POST['pool'] > $_POST['max_pool'] || $_POST['pool'] < $_POST['min_pool'] || $_POST['max_pool'] < $_POST['min_pool']) { $ERROR[] = "プールサイズが不正です。"; }
		if (!$_POST['pool_rate_number']) { $ERROR[] = "プールサイズ正解率計算直近問題数が未入力です。"; }
		if (!$_POST['lowest_study_number']) { $ERROR[] = "最低学習問題数が未入力です。"; }
		if (!$_POST['clear_rate_number']) { $ERROR[] = "ドリルクリアー正解率計算直近問題数が未入力です。"; }
		if (!$_POST['clear_rate']) { $ERROR[] = "ドリルクリアー正解率が未入力です。"; }
		elseif ($_POST['clear_rate'] > 100) { $ERROR[] = "ドリルクリアー正解率の最大値は「100」です。"; }
		if (!$_POST['difficulty_type']) { $ERROR[] = "難度利用タイプが未入力です。"; }
	}
	if (!$_POST['threshold']) { $ERROR[] = "問題閾値が入力されておりません。"; }
//	if (!$_POST['review_unit_num']) { $ERROR[] = "戻りユニットが選択されておりません。"; }
	if ($_POST['review_unit_num'] && !$_POST['lesson_page']) { $ERROR[] = "レッスンページが入力されておりません。"; }
	elseif ($_POST['lesson_page'] && ereg("[^0-9\,]",$_POST['lesson_page'])) { $ERROR[] = "レッスンページが不正です。"; }
//	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}

/**
 * 復習確認のため
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function review_check_html($L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (ACTION == "スキル追加確認") { $val = "review_add"; }
				elseif (ACTION == "スキル変更確認") { $val = "review_change"; }
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
		//	add ookawara 2012/06/05 start
		$action = "change";
		if (MODE == "review_delete") {
			$action = "review_change";
		}
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"".$action."\">\n";
		//	add ookawara 2012/06/05 end
		//$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		if ($_POST['review_setting_num']) {
			$sql = "SELECT * FROM " . T_REVIEW_SETTING .
				" WHERE review_setting_num='".$_POST['review_setting_num']."' AND state!='1' LIMIT 1;";

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
		if ($review_unit_num) {
			$sql  = "SELECT stage_num, lesson_num FROM ".T_UNIT.
					" WHERE unit_num='{$review_unit_num}' LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				if ($list = $cdb->fetch_assoc($result)) {
					$return_stage_num = $list['stage_num'];
					$return_lesson_num = $list['lesson_num'];
				}
			}
		}
	}

	//	ユニット名
	$sql  = "SELECT unit_name FROM ".T_UNIT.
			" WHERE unit_num='{$_POST['unit_num']}' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$unit_name = $list['unit_name'];
		}
	}

	//スキル名
	$skill_name = "未登録";
	if ($skill_num) {
		$sql  = "SELECT skill_name FROM ".T_SKILL.
				" WHERE list_num='".$skill_num."' AND course_num='".$_POST['course_num']."'" .
				" AND state!='1' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$skill_name = $list['skill_name'];
			}
		}
	}

	//	戻りユニットステージ名
	unset($return_unit_name);
	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE stage_num='".$return_stage_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$return_unit_name = $list['stage_name']." > ";
		}
	}
	//	戻りユニットレッスン名
	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='$return_lesson_num' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$return_unit_name .= $list['lesson_name']." > ";
		}
	}
	//	戻りユニット名
	$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE unit_num='".$review_unit_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$return_unit_name .= $list['unit_name'];
		}
	}

	if (MODE != "review_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_BLOCK_REVIEW_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$review_setting_num) { $new_num = "---"; } else { $new_num = $review_setting_num; }
	$INPUTS['REVIEWNUM'] = array('result'=>'plane','value'=>$new_num);
	$INPUTS['SKILLNUM'] = array('result'=>'plane','value'=>$skill_num);
	$skill_name = str_replace("&lt;","<",$skill_name);
	$skill_name = str_replace("&gt;",">",$skill_name);
	$INPUTS['SKILLNAME'] = array('result'=>'plane','value'=>$skill_name);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$course_num]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$stage_num]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$lesson_num]);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$block_num);
	$INPUTS['RATE'] = array('result'=>'plane','value'=>$rate);
	$INPUTS['POOL'] = array('result'=>'plane','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('result'=>'plane','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('result'=>'plane','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('result'=>'plane','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('result'=>'plane','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('result'=>'plane','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('result'=>'plane','value'=>$clear_rate);
	$INPUTS['THRESHOLD'] = array('result'=>'plane','value'=>$threshold);
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$L_DIFFICULTY_TYPE[$difficulty_type]);
	$INPUTS['GIVEUPTIME'] = array('result'=>'plane','value'=>$giveup_time);
	$INPUTS['GIVEUPNUMBER'] = array('result'=>'plane','value'=>$giveup_ans_num);
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);
	$INPUTS['RETURNSTATUS'] = array('result'=>'plane','value'=>$return_unit_name);
	$INPUTS['LESSONPAGE'] = array('result'=>'plane','value'=>$lesson_page);
	$INPUTS['REMARKS'] = array('result'=>'plane','value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	//if ($action) {	//	del ookawara 2012/06/05
	if ($action || MODE == "review_delete") {	//	add ookawara 2012/06/05
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
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$mode = "review_form";
	if (MODE == "review_delete") {
		$mode = "review";
	}
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= $HIDDEN2;
	//$html .= "<input type=\"hidden\" name=\"mode\" value=\"review_form\">\n";	//	del ookawara 2012/06/05
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$mode."\">\n";	//	add ookawara 2012/06/05
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;

}


/**
 * 復習のDB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function review_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "mode") { continue; }
		elseif ($key == "action") { continue; }
		elseif ($key == "return_stage_num") { continue; }
		elseif ($key == "return_lesson_num") { continue; }
		elseif ($key == "s_stage_num") { $key = "stage_num"; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['update_time'] = "now()";

	$ERROR = $cdb->insert(T_REVIEW_SETTING,$INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	return array($ERROR,$msg);
}

/**
 * 復習のDB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function review_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "review_form") {
		foreach ($_POST as $key => $val) {
			if ($key == "mode") { continue; }
			elseif ($key == "action") { continue; }
			elseif ($key == "course_num") { continue; }
			elseif ($key == "stage_num") { continue; }
			elseif ($key == "lesson_num") { continue; }
			elseif ($key == "unit_num") { continue; }
			elseif ($key == "review_setting_num") { continue; }
			elseif ($key == "return_stage_num") { continue; }
			elseif ($key == "return_lesson_num") { continue; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE review_setting_num='".$_POST['review_setting_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_REVIEW_SETTING,$INSERT_DATA,$where);

	//} elseif (MODE == "review_del") {	//	del ookawara 2012/06/05
	} elseif (MODE == "review_delete") {	//	add ookawara 2012/06/05
		$INSERT_DATA['display'] = 2;
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['update_time'] = "now()";
		//$where = " WHERE review_setting_num='$review_setting_num' LIMIT 1;";
		$where = " WHERE review_setting_num='".$_POST['review_setting_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_REVIEW_SETTING,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	if (!$ERROR) {
		$msg = "登録完了いたしました。";
	}

	return array($ERROR,$msg);
}


/**
 * BLOCK新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function block_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {

//	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;						// 2012/08/22 del oda
	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_ONOFF_TYPE,$L_DISPLAY;		// 2012/08/22 add oda
	global $L_ORDER_TYPE;// 2013/10/11 add koyama

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ユニットネーム
	$sql = "SELECT * FROM " . T_UNIT .
		" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$unit_name = $list[unit_name];
		}
	}


	if ($_POST['block_num']) {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "ドリル変更登録フォーム<br>\n";
	} else {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "ドリル新規登録フォーム<br>\n";
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// add start hirose 2018/11/28 すらら英単語
	$service_num = "";
	if(isset($_POST['course_num'])){
		$service_num = get_service_num($_POST['course_num']);
	}
	// add end hirose 2018/11/28 すらら英単語

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
//		if (!$giveup_time) { 	$giveup_time = 10; }	//	del ookawara 2009/07/27
		if (!$giveup_time) { 	$giveup_time = 30; }	//	add ookawara 2009/07/27
		if (!$giveup_ans_num) {
			$giveup_ans_num = 20;	//	add ookawara 2009/07/27
//			if ($course_num == "2") { $giveup_ans_num = 15; } else { $giveup_ans_num = 20; }	//	del ookawara 2009/07/27
		}

		// add 2018/11/20 yoshizawa すらら英単語
		// del start hirose 2018/11/28 すらら英単語
		// ACTIONが無い場合も、$service_numを使用していたため、上に移動。
//		$service_num = "";
//		$service_num = get_service_num($course_num);
		// del end hirose 2018/11/28 すらら英単語
		// すらら英単語サービスのコースの場合はドリルタイプE（block_type = 8）のみを選択可能とします。
		if($service_num == '16'){ $block_type = 8; }
		// add 2018/11/20 yoshizawa すらら英単語

	} else {
		if ($_POST[block_num]) {
			$sql = "SELECT * FROM " . T_BLOCK .
				" WHERE block_num='".$_POST['block_num']."' AND state!='1' LIMIT 1;";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。$sql";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}

			// 2012/08/22 add start oda
    		$surala_unit = get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, $block_num, 0, 0);
    		$surala_unit = change_unit_num_to_key_all($surala_unit);
			// 2012/08/22 add end oda
		}
	}

	//	学習タイプ フォーム
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"block_addform\">\n";
//	$html .= "<input type=\"hidden\" name=\"action\" value=\"block_check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">\n";
	if ($block_num) {
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$block_num."\">\n";
	}

	// 学習タイプの値によってテンプレートファイルを決定
	if (!$block_type) { $block_type = 1; }
	$PRACTICE_BLOCK_FORM = "practice_block_form_type_".$block_type.".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($PRACTICE_BLOCK_FORM);


//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$block_num) { $new_num = "---"; } else { $new_num = $block_num; }
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$new_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$course_num]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$stage_num]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$lesson_num]);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);

	// 学習タイプ 変更でサブミットさせる
	$block_type_select = "<select name=\"block_type\" onChange=\"submit();\">\n";
	if ($L_UNIT_TYPE) {
		foreach ($L_UNIT_TYPE AS $key => $val) {

			// add start 2018/11/20 yoshizawa すらら英単語
			if($service_num == '16'){
				// すらら英単語サービスのコースの場合、ドリルEのみが登録可能となります。
				if($key != '8'){ continue; }
			} else {
				// すらら英単語サービス以外のコースでドリルEが登録できない様に制御します。
				if($key == '8'){ continue; }
			}
			// add end 2018/11/20 yoshizawa すらら英単語

			if ($key < 1) { continue; }
			if ($block_type == $key) { $selected = "selected"; }
			else { $selected = ""; }
			$block_type_select .= "<option value=\"{$key}\" $selected>{$val}</option>\n";
		}
	}
	$block_type_select .= "</select>\n";

	$INPUTS['CLASSM'] = array('result'=>'plane','value'=>$block_type_select);
//	$INPUTS[CLASSM] = array('type'=>'select','name'=>'block_type','array'=>$L_UNIT_TYPE,'check'=>$block_type);
	$INPUTS['RATE'] = array('type'=>'text','name'=>'rate','size'=>'6','value'=>$rate);
	$INPUTS['POOL'] = array('type'=>'text','name'=>'pool','size'=>'6','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('type'=>'text','name'=>'max_pool','size'=>'6','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('type'=>'text','name'=>'min_pool','size'=>'6','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('type'=>'text','name'=>'pool_rate_number','size'=>'6','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('type'=>'text','name'=>'lowest_study_number','size'=>'6','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('type'=>'text','name'=>'clear_rate_number','size'=>'6','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('type'=>'text','name'=>'clear_rate','size'=>'6','value'=>$clear_rate);
	$INPUTS['CLEARTIME'] = array('type'=>'text','name'=>'clear_time','size'=>'6','value'=>$clear_time);
	$INPUTS['THRESHOLD'] = array('type'=>'text','name'=>'threshold','size'=>'6','value'=>$threshold);
	$INPUTS['GIVEUPTIME'] = array('type'=>'text','name'=>'giveup_time','size'=>'6','value'=>$giveup_time);
	$INPUTS['GIVEUPNUMBER'] = array('type'=>'text','name'=>'giveup_ans_num','size'=>'6','value'=>$giveup_ans_num);
	$INPUTS['BLOCKCLEARMSG'] = array('type'=>'textarea','name'=>'block_clear_msg','cols'=>'27','rows'=>'6','value'=>$block_clear_msg);
	$INPUTS['SWITCHINGTIME'] = array('type'=>'text','name'=>'switching_time','size'=>'6','value'=>$switching_time); // add 2018/10/09 yoshizawa すらら英単語追加

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type1");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('1');
	$difficulty_type1 = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("difficulty_type");
	$newform->set_form_id("difficulty_type2");
	$newform->set_form_check($difficulty_type);
	$newform->set_form_value('2');
	$difficulty_type2 = $newform->make();
	$difficulty_type = $difficulty_type1 . "<label for=\"difficulty_type1\">{$L_DIFFICULTY_TYPE[1]}</label> / " . $difficulty_type2 . "<label for=\"difficulty_type2\">{$L_DIFFICULTY_TYPE[2]}</label>";
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$difficulty_type);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$indisplay = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $indisplay . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	// update oda 2012/08/22 start

	$rank_form = "";

	$rank_form = "<select name=\"mt_ranking_point_num\">\n";
	$sql  = "SELECT mt_ranking_point_num, pattern_name FROM ".T_MS_RANKING_POINT.
			" WHERE mk_flg='0'".
			"   AND course_num ='".$course_num."'".
			"   AND default_flag ='1'".						// add oda 2013/10/15
			" ORDER BY pattern_name".
			" ;";
	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($mt_ranking_point_num != "") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"\" ".$selected.">デフォルト（正解率）</option>\n";
		$selected = "";
		if ($mt_ranking_point_num == "-1") {
			$selected = " selected";
		}
		$rank_form .= "<option value=\"-1\" ".$selected.">デフォルト（正解率＋時間）</option>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$mt_ranking_point_num_list = $list['mt_ranking_point_num'];
			$pattern_name = $list['pattern_name'];

			$selected = "";
			if ($mt_ranking_point_num == $mt_ranking_point_num_list) {
				$selected = " selected";
			}
			$rank_form .= "<option value=\"".$mt_ranking_point_num_list."\" ".$selected.">".$pattern_name."</option>\n";
		}
	}
	$rank_form .= "</select>\n";

	$INPUTS['RANKINGPOINTNUM'] = array('result'=>'plane','value'=>$rank_form);	// ランキングパターン
	//	add ookawara 2012/07/06 end
	//	update oda 2012/08/22 end

	// 2012/08/22 add start oda
    // 上位で設定されている場合は、入力フィールドを設定しない
    $upper_check_s = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    $upper_check_l = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
    $upper_check_u = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
    if (strlen($upper_check_s) == 0 && strlen($upper_check_l) == 0 && strlen($upper_check_u) == 0) {
		$INPUTS['SURALAUNIT'] = array('type'=>'text','name'=>'surala_unit','size'=>'40','value'=>$surala_unit);
		$surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
		$surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_s) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_l) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Lesson）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    } elseif (strlen($upper_check_u) > 0) {
    	$INPUTS['SURALAUNIT'] = array('type'=>'hidden','name'=>'surala_unit','value'=>"");
    	$surala_unit_att = "<span style=\"color:red;\">上位階層（Unit）にて設定済です</span>";
    	$INPUTS['SURALAUNITATT'] 	= array('result'=>'plane','value'=>$surala_unit_att);
    }
	// BGM
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("bgm_set");
	$newform->set_form_id("bgm_on");
	$newform->set_form_check($bgm_set);
	$newform->set_form_value('1');
	$bgm_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("bgm_set");
	$newform->set_form_id("bgm_off");
	$newform->set_form_check($bgm_set);
	$newform->set_form_value('2');
	$bgm_off = $newform->make();
	$bgm = $bgm_on . "<label for=\"bgm_on\">{$L_ONOFF_TYPE[1]}</label> / " . $bgm_off . "<label for=\"bgm_off\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['BGMSET'] = array('result'=>'plane','value'=>$bgm);
	// カウントダウン／タイマー表示
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("count_down_set");
	$newform->set_form_id("count_down_on");
	$newform->set_form_check($count_down_set);
	$newform->set_form_value('1');
	$count_down_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("count_down_set");
	$newform->set_form_id("count_down_off");
	$newform->set_form_check($count_down_set);
	$newform->set_form_value('2');
	$count_down_off = $newform->make();
	$count_down = $count_down_on . "<label for=\"count_down_on\">{$L_ONOFF_TYPE[1]}</label> / " . $count_down_off . "<label for=\"count_down_off\">{$L_ONOFF_TYPE[2]}</label>";
	$INPUTS['COUNTDOWNSET'] = array('result'=>'plane','value'=>$count_down);
	// 2012/08/22 add end oda

	//----------------------------------------------------------------------------
	// 出題順序										add start koyama 2013/10/18
	//----------------------------------------------------------------------------
	// 順番
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("order_type");
	$newform->set_form_id("order_type_seq");
	$newform->set_form_check($order_type);
	$newform->set_form_value('1');
	$order_type_seq = $newform->make();
	// ランダム
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("order_type");
	$newform->set_form_id("order_type_random");
	$newform->set_form_check($order_type);
	$newform->set_form_value('2');
	$order_type_random = $newform->make();

	$order_type = $order_type_seq . "<label for=\"order_type_seq\">{$L_ORDER_TYPE[1]}</label> / " . $order_type_random . "<label for=\"order_type_random\">{$L_ORDER_TYPE[2]}</label>";
	$INPUTS['ORDERTYPE'] = array('result'=>'plane','value'=>$order_type);
	// -----------------------------------------------add end koyama 2013/10/18

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	if ($block_num) {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "<input type=\"submit\" name=\"action\" value=\"ドリル変更確認\">\n";
	} else {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
		$html .= "<input type=\"submit\" name=\"action\" value=\"ドリル追加確認\">\n";
	}
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "<input type=\"submit\" value=\"ドリル一覧へ戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加
	return $html;
}



/**
 * ドリル追加・更新・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function block_check_html($L_COURSE,$L_STAGE,$L_LESSON) {

//	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_DISPLAY;					// 2012/08/22 del oda
	global $L_UNIT_TYPE,$L_SETTING_TYPE,$L_DIFFICULTY_TYPE,$L_ONOFF_TYPE,$L_DISPLAY;	// 2012/08/22 add oda
	global $L_ORDER_TYPE;// 2013/10/15 add koyama

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
				if (ACTION == "ドリル追加確認") { $val = "block_add"; }
				else { $val = "block_change"; }
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
		$sql = "SELECT * FROM " . T_UNIT .
			" WHERE unit_num='".$_POST['unit_num']."' AND state!='1' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$unit_name = $list[unit_name];
			}
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"block_change\">\n";
		$sql = "SELECT * FROM " . T_BLOCK . " a," . T_UNIT . " b" .
			" WHERE block_num='{$_POST['block_num']}' AND a.state!='1'" .
			" AND a.unit_num=b.unit_num AND b.state!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			if ($key == "stage_num") { $key = "s_stage_num"; }
			$$key = replace_decode($val);
		}

		// 2012/08/22 add start oda
   		$surala_unit = get_surala_unit($course_num, $s_stage_num, $lesson_num, $unit_num, $block_num, 0, 0);
   		$surala_unit = change_unit_num_to_key_all($surala_unit);
		// 2012/08/22 add end oda
	}

	// update oda 2012/08/22 start

	$rank_form = "---";
	$rank_form = "デフォルト（正解率）";
	if ($mt_ranking_point_num == "-1") {
		$rank_form = "デフォルト（正解率＋時間）";
	} else {
		if ($mt_ranking_point_num) {
			$sql  = "SELECT mt_ranking_point_num, pattern_name FROM ".T_MS_RANKING_POINT.
					" WHERE mk_flg='0'".
					" AND mt_ranking_point_num = '".$mt_ranking_point_num."'".
					"   AND default_flag ='1'".						// add oda 2013/10/15
					" ;";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$rank_form = $list['pattern_name'];
				}
			}
		}
	}
	// update oda 2012/08/22 end

	if (MODE != "block_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$PRACTICE_BLOCK_FORM = "practice_block_form_type_".$block_type.".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($PRACTICE_BLOCK_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$block_num) { $block_num = "---"; }
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$block_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$L_COURSE[$_POST['course_num']]);
	$INPUTS['STAGENAME'] = array('result'=>'plane','value'=>$L_STAGE[$_POST['stage_num']]);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$L_LESSON[$_POST['lesson_num']]);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	$INPUTS['PARENTSUNITNUM'] = array('result'=>'plane','value'=>$parents_unit_name_);
	$INPUTS['CLASSM'] = array('result'=>'plane','value'=>$L_UNIT_TYPE[$block_type]);
//	$INPUTS[SETTINGTYPE] = array('result'=>'plane','value'=>$L_SETTING_TYPE[$setting_type]);
	$INPUTS['RATE'] = array('result'=>'plane','value'=>$rate);
	$INPUTS['POOL'] = array('result'=>'plane','value'=>$pool);
	$INPUTS['MAXPOOL'] = array('result'=>'plane','value'=>$max_pool);
	$INPUTS['MINPOOL'] = array('result'=>'plane','value'=>$min_pool);
	$INPUTS['POOLRATENUMBER'] = array('result'=>'plane','value'=>$pool_rate_number);
	$INPUTS['LOWESTSTUDYNUMBER'] = array('result'=>'plane','value'=>$lowest_study_number);
	$INPUTS['CLEARRATENUMBER'] = array('result'=>'plane','value'=>$clear_rate_number);
	$INPUTS['CLEARRATE'] = array('result'=>'plane','value'=>$clear_rate);
	$INPUTS['CLEARTIME'] = array('result'=>'plane','value'=>$clear_time);
	$INPUTS['THRESHOLD'] = array('result'=>'plane','value'=>$threshold);
	$INPUTS['DIFFICULTYTYPE'] = array('result'=>'plane','value'=>$L_DIFFICULTY_TYPE[$difficulty_type]);
	$INPUTS['GIVEUPTIME'] = array('result'=>'plane','value'=>$giveup_time);
	$INPUTS['GIVEUPNUMBER'] = array('result'=>'plane','value'=>$giveup_ans_num);
	$block_clear_msg = nl2br($block_clear_msg);
	$INPUTS['BLOCKCLEARMSG'] = array('result'=>'plane','value'=>$block_clear_msg);
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
//	$INPUTS['RANKINGRATE'] = array('result'=>'plane','value'=>$rank_rate_form);		//	add ookawara 2012/07/06
//	$INPUTS['RANKINGTAIME'] = array('result'=>'plane','value'=>$rank_time_form);	//	add ookawara 2012/07/06
	$INPUTS['RANKINGPOINTNUM'] = array('result'=>'plane','value'=>$rank_form);		//  add oda 2012/08/22

	// 2012/08/22 add start oda
	$INPUTS['SURALAUNIT'] = array('result'=>'plane','value'=>$surala_unit);
	$INPUTS['BGMSET'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$bgm_set]);
	$INPUTS['COUNTDOWNSET'] = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$count_down_set]);
	// 2012/08/22 add end oda

	$INPUTS['ORDERTYPE'] = array('result'=>'plane','value'=>$L_ORDER_TYPE[$order_type]);	// 出題順序 add koyama 2013/10/15 割合・速さはかせ対応
	$INPUTS['SWITCHINGTIME'] = array('result'=>'plane','value'=>$switching_time);							// add 2018/10/09 yoshizawa すらら英単語追加

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	}
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;
}

/**
 * BLOCKのDB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function block_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "s_stage_num") { $key = "stage_num"; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['update_time'] = "now()";

	// 2012/08/22 add start oda
	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($INSERT_DATA['surala_unit']);
	unset($INSERT_DATA['surala_unit']);
	// 2012/08/22 add end oda

	$ERROR = $cdb->insert(T_BLOCK,$INSERT_DATA);

	if (!$ERROR) {
		$block_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $block_num;
		$where = " WHERE block_num='".$block_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);
		// 2012/08/22 add start oda
		// すららで復習ユニット登録
		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'], $block_num, 0, 0, $surala_unit);
		unset($INSERT_DATA['surala_unit']);
		// 2012/08/22 add end oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	return $ERROR;
}

/**
 * BLOCKのDB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function block_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "block_form") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "stage_num") { continue; }
			elseif ($key == "s_stage_num") { $key = "stage_num"; }
			$INSERT_DATA[$key] = "$val";
		}

		// 2012/08/22 add start oda
		// すららで復習ユニット登録
		$surala_unit = change_unit_key_to_num_all($_POST['surala_unit']);
		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $_POST['block_num'], 0, 0, $surala_unit);
		unset($INSERT_DATA['surala_unit']);
		// 2012/08/22 add end oda

		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$_POST['block_num']."' LIMIT 1;";
		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);

	} elseif (MODE == "block_delete") {
		$INSERT_DATA['display'] = 2;
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$_POST['block_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);

		// 2012/08/22 add start oda
		// すららで復習ユニット削除
		$ERROR = delete_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $_POST['block_num'], 0, 0);
		// 2012/08/22 add end oda
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * BLOCKを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function block_up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_BLOCK .
			" WHERE block_num='".$_POST['block_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_unit_num = $list['unit_num'];
			$m_block_num = $list['block_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_unit_num || !$m_list_num) { $ERROR[] = "移動するユニット情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_BLOCK .
			" WHERE state!='1' AND unit_num='".$m_unit_num."' AND list_num<'".$m_list_num."'" .
			" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_block_num = $list['block_num'];
				$c_list_num = $list['list_num'];
			}
		}
	}
	if (!$c_block_num || !$c_list_num) { $ERROR[] = "移動されるユニット情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$m_block_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$c_block_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * BLOCKを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function block_down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_BLOCK .
			" WHERE block_num='".$_POST['block_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_unit_num = $list['unit_num'];
			$m_block_num = $list['block_num'];
			$m_list_num = $list['list_num'];
		}
	}
	if (!$m_block_num || !$m_list_num) { $ERROR[] = "移動するユニット情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_BLOCK .
			" WHERE state!='1' AND unit_num='".$m_unit_num."' AND list_num>'".$m_list_num."'" .
			" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_block_num = $list['block_num'];
				$c_list_num = $list['list_num'];
			}
		}
	}
	if (!$c_block_num || !$c_list_num) { $ERROR[] = "移動されるユニット情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$m_block_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['update_time'] = "now()";
		$where = " WHERE block_num='".$c_block_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_BLOCK,$INSERT_DATA,$where);
	}

	return $ERROR;
}

// 2012/09/26 add start oda

/**
 * ワンポイント解説チェック処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function one_point_check_html($L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (ACTION == "ワンポイント解説追加確認") {
					$val = "one_point_add";
				} else {
					$val = "one_point_change";
				}
			}
			// 改行変換
			if ($key == "one_point_commentary") {
				$val = eregi_replace("\r","",$val);
				$val = eregi_replace("\n","<br>",$val);
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
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"one_point_change\">\n";
		$sql = "SELECT * FROM " . T_ONE_POINT .
				" WHERE mk_flg = '0'" .
				"   AND one_point_num = '".$_POST['one_point_num']."'" .
				"   LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			if ($key == "stage_num") { $key = "s_stage_num"; }
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
		// すららで復習の情報取得
		$surala_unit = get_surala_unit($course_num, $s_stage_num, $lesson_num, $unit_num, 0, 0, $one_point_num);
		$surala_unit = change_unit_num_to_key_all($surala_unit);
	}

	// 改行変換
	$one_point_commentary = eregi_replace("\r","",$one_point_commentary);
	$one_point_commentary = eregi_replace("\n","<br>",$one_point_commentary);

	if (MODE != "one_point_delete") {
		$button = "登録";
	} else {
		$button = "削除";
	}
	$html .= "<br>確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ONE_POINT_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$one_point_num) { $one_point_num = "---"; }
	$INPUTS['ONEPOINTNUM']			= array('result'=>'plane','value'=>$one_point_num);
	$INPUTS['STUDYTITLE']			= array('result'=>'plane','value'=>$study_title);
	$INPUTS['ONEPOINTCOMMENTARY']	= array('result'=>'plane','value'=>$one_point_commentary);
	$INPUTS['SURALAUNIT']			= array('result'=>'plane','value'=>$surala_unit);
	$INPUTS['DISPLAY']				= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加

	return $html;
}


/**
 * ワンポイント解説入力フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function one_point_form($ERROR,$L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['one_point_num']) {
		$html .= "<br>ワンポイント解説変更フォーム<br>\n";
	} else {
		$html .= "<br>ワンポイント解説新規登録フォーム<br>\n";
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (ACTION != "one_point_updateform") {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		if ($_POST['one_point_num']) {
			$sql = "SELECT * FROM " . T_ONE_POINT .
					" WHERE one_point_num = '".$_POST['one_point_num']."' AND mk_flg = '0' LIMIT 1;";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
				$$key = ereg_replace("\"","&quot;",$$key);
			}
			// すららで復習の情報取得
			$surala_unit = get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, 0, 0, $one_point_num);
			$surala_unit = change_unit_num_to_key_all($surala_unit);
		}
	}

	//	html生成
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"one_point_form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_addform\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">\n";
	if ($one_point_num) {
		$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$one_point_num."\">\n";
	}

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ONE_POINT_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$one_point_num) {
		$new_num = "---";
	} else {
		$new_num = $one_point_num;
	}
	$INPUTS['ONEPOINTNUM'] = array('result'=>'plane','value'=>$new_num);
	$INPUTS['STUDYTITLE']         = array('type'=>'text','name'=>'study_title','size'=>'40','value'=>$study_title);
	$INPUTS['ONEPOINTCOMMENTARY'] = array('type'=>'textarea','name'=>'one_point_commentary','cols'=>'37','rows'=>'10','value'=>$one_point_commentary);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$indisplay = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $indisplay . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $undisplay . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	// update start oda 2013/08/06 ワンポイント解説の場合は、上位のチェックを行わない
	// 上位で設定されている場合は、入力フィールドを設定しない
	$INPUTS['SURALAUNIT']		= array('type'=>'text','name'=>'surala_unit','size'=>'40','value'=>$surala_unit);
	$surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
	$surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS['SURALAUNITATT']	= array('result'=>'plane','value'=>$surala_unit_att);
	// update end oda 2013/08/06

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	if ($one_point_num) {
		$html .= "<input type=\"submit\" name=\"action\" value=\"ワンポイント解説変更確認\">\n";
	} else {
		$html .= "<input type=\"submit\" name=\"action\" value=\"ワンポイント解説追加確認\">\n";
	}

	$html .= "<input type=\"reset\" value=\"クリア\"><br><br>\n";
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_one_point_win_open('".$unit_num."', '".$one_point_num."', '1')\">\n";		// 2012/10/22 add oda
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"ワンポイント解説一覧へ戻る\">\n";
	$html .= "</form>\n";
	$html .= "<br><br>\n";		// add oda 2018/06/12 ブラウザによりURLが左下に表示してしまう為、改行を追加
	return $html;
}

/**
 * ONE_POINTのDB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") {
			continue;
		} elseif ($key == "s_stage_num") {
			$key = "stage_num";
		}
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['ins_syr_id']		= "add";
	$INSERT_DATA['ins_date']		= "now()";
	$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id']		= "add";
	$INSERT_DATA['upd_date']		= "now()";
	$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($INSERT_DATA['surala_unit']);
	unset($INSERT_DATA['surala_unit']);

	$ERROR = $cdb->insert(T_ONE_POINT,$INSERT_DATA);

	if (!$ERROR) {
		$one_point_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $one_point_num;
		$where = " WHERE one_point_num = '".$one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'], 0, 0, $one_point_num, $surala_unit);
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}

	return $ERROR;
}

/**
 * ONE_POINTのDB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "one_point_form") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				continue;
			} elseif ($key == "stage_num") {
				continue;
			} elseif ($key == "s_stage_num") {
				$key = "stage_num";
			}
			$INSERT_DATA[$key] = "$val";
		}

		$surara_unit = change_unit_key_to_num_all($_POST['surala_unit']);

		// すららで復習ユニット登録
		$ERROR = update_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, $_POST['one_point_num'], $surara_unit);
		unset($INSERT_DATA['surala_unit']);

		$INSERT_DATA['upd_syr_id']		= "update";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$_POST['one_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

	} elseif (MODE == "one_point_delete") {
		$INSERT_DATA['display']		= "2";
		$INSERT_DATA['mk_flg']		= "1";
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
		$where = " WHERE one_point_num='".$_POST['one_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

		// すららで復習ユニット削除
		$ERROR = delete_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, $_POST['one_point_num']);
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}

/**
 * ONE_POINTを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_ONE_POINT .
			" WHERE one_point_num = '".$_POST['one_point_num']."' AND mk_flg = '0' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_unit_num			= $list['unit_num'];
			$m_one_point_num	= $list['one_point_num'];
			$m_list_num			= $list['list_num'];
		}
	}
	if (!$m_unit_num || !$m_list_num) {
		$ERROR[] = "移動するワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_ONE_POINT .
				" WHERE unit_num = '".$m_unit_num."' AND mk_flg = '0' AND list_num < '".$m_list_num."'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_one_point_num	= $list['one_point_num'];
				$c_list_num			= $list['list_num'];
			}
		}
	}
	if (!$c_one_point_num || !$c_list_num) {
		$ERROR[] = "移動されるワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $c_list_num;
		$INSERT_DATA['upd_syr_id']		= "up";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$m_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $m_list_num;
		$INSERT_DATA['upd_syr_id']		= "up";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$c_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * ONE_POINTを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_ONE_POINT .
			" WHERE one_point_num = '".$_POST['one_point_num']."' AND mk_flg = '0' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$m_unit_num			= $list['unit_num'];
			$m_one_point_num	= $list['one_point_num'];
			$m_list_num			= $list['list_num'];
		}
	}
	if (!$m_one_point_num || !$m_list_num) {
		$ERROR[] = "移動するワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_ONE_POINT .
				" WHERE unit_num = '".$m_unit_num."' AND mk_flg = '0' AND list_num > '".$m_list_num."'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$c_one_point_num	= $list['one_point_num'];
				$c_list_num			= $list['list_num'];
			}
		}
	}
	if (!$c_one_point_num || !$c_list_num) {
		$ERROR[] = "移動されるワンポイント解説情報が取得できません。";
	}
	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $c_list_num;
		$INSERT_DATA['upd_syr_id']		= "down";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$m_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $m_list_num;
		$INSERT_DATA['upd_syr_id']		= "down";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$c_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) {
		$ERROR[] = "コース情報が確認できません。";
	}
	if (!$_POST['stage_num']) {
		$ERROR[] = "ステージ情報が確認できません。";
	}
	if (!$_POST['lesson_num']) {
		$ERROR[] = "レッスン情報が確認できません。";
	}
	if (!$_POST['unit_num']) {
		$ERROR[] = "ユニット情報が確認できません。";
	}

	if (!$_POST['study_title']) {
		$ERROR[] = "解説タイトルが未入力です。";
	}
	if (!$_POST['one_point_commentary']) {
		$ERROR[] = "解説内容が未入力です。";
	}

	if ($_POST['surala_unit']) {
		$surala_unit_count = 0;
		$surala_unit_list = explode("::",change_unit_key_to_num_all($_POST['surala_unit']));

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

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}


/**
 * ONE_POINTのアップロードの処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_upload() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) {
		$ERROR[] = "コースが確認できません。";
	}
	if (!$_POST['stage_num']) {
		$ERROR[] = "ステージが確認できません。";
	}
	if (!$_POST['lesson_num']) {
		$ERROR[] = "レッスンが確認できません。";
	}
	if (!$_POST['unit_num']) {
		$ERROR[] = "ユニットが確認できません。";
	}

	$file_name = $_FILES['one_point_file']['name'];
	$file_tmp_name = $_FILES['one_point_file']['tmp_name'];
	$file_error = $_FILES['one_point_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "ワンポイント解説ファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) {
			unlink($file_tmp_name);
		}
		return $ERROR;
	}

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) { $i++;  continue; }
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) { continue; }
			$file_data = explode("\t",$VAL);
			// 項目が全て設定されている場合
			if (count($file_data) == 10) {
				$LINE['one_point_num'] = $file_data[0];
				$LINE['unit_name'] = $file_data[3];					// 2012/10/22 add oda
				$LINE['study_title'] = $file_data[5];
				$LINE['one_point_commentary'] = $file_data[6];
				$LINE['surala_unit'] = $file_data[7];
				$LINE['display'] = $file_data[8];
				$LINE['mk_flg'] = $file_data[9];
			// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 9) {
				$LINE['one_point_num'] = "0";
				$LINE['unit_name'] = $file_data[2];					// 2012/10/22 add oda
				$LINE['study_title'] = $file_data[4];
				$LINE['one_point_commentary'] = $file_data[5];
				$LINE['surala_unit'] = $file_data[6];
				$LINE['display'] = $file_data[7];
				$LINE['mk_flg'] = $file_data[8];
			} else {
				$ERROR[] = $i."行目　データが不正です。";
				continue;
			}

//			list(
//				$LINE['one_point_num'],$LINE['list_num'],
//				$LINE['study_title'],$LINE['one_point_commentary'],
//				$LINE['surala_unit'],$LINE['display'],$LINE['mk_flg']) = explode("\t",$VAL);
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
						//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						//----------------------------------------------------------------
						//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
						//	データの文字コードがUTF-8だったら変換処理をしない
						$code = judgeCharacterCode ( $val );
						if ( $code != 'UTF-8' ) {
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						}
						//--------------------------------------------------

						$LINE[$key] = replace_word($val);
					}
				}
			}

			$LINE['course_num'] = $_POST['course_num'];
			$LINE['stage_num']  = $_POST['stage_num'];
			$LINE['lesson_num'] = $_POST['lesson_num'];
			$LINE['unit_num']   = $_POST['unit_num'];

			// 内容チェック
			$error_flg = false;
			if (!$LINE['unit_name']) {
				$ERROR[] = $i."行目　ユニット名が未入力です。";
				$error_flg = true;
			} else {
				$lesson_name = "";
				$sql = "SELECT lesson_name FROM " . T_LESSON .
						" WHERE state = '0'".
						"   AND lesson_num = '".$_POST['lesson_num']."'".
						";";
				if ($result = $cdb->query($sql)) {
					while ($list = $cdb->fetch_assoc($result)) {
						$lesson_name = $list['lesson_name'];
					}
				}
				if ($lesson_name != $LINE['unit_name']) {
					$ERROR[] = $i."行目　ユニット名（Lesson名)が異なります。";
					$error_flg = true;
				}
			}
			if (!$LINE['study_title']) {
				$ERROR[] = $i."行目　解説タイトルが未入力です。";
				$error_flg = true;
			}
			if (!$LINE['one_point_commentary']) {
				$ERROR[] = $i."行目　解説内容が未入力です。";
				$error_flg = true;
			}
			if (!$LINE['display']) {
				$ERROR[] = $i."行目　表示区分が未入力です。";
				$error_flg = true;
			}

			$surala_unit_list = array();
			if ($LINE['surala_unit']) {

				$surala_unit_count = 0;
				$surala_unit_list = explode("::",change_unit_key_to_num_all($LINE['surala_unit']));
				// ユニットIDに数値以外の値が入っていないかチェック
				$surala_error_flag = false;
				for ($j=0; $j<count($surala_unit_list); $j++) {
					if (is_numeric($surala_unit_list[$j]) == false) {
						$ERROR[] = "すららで復習ユニットに指定したユニットは、不正な情報が混ざっています。";
						$surala_error_flag = true;
						$error_flg = true;
					}
				}

				// ユニットIDが存在するかチェック
				if (!$surala_error_flag) {
					$sql  = "SELECT * FROM ".T_UNIT.
							" WHERE state='0' AND unit_num IN ('" . implode("','", $surala_unit_list) ."')";
					if ($result = $cdb->query($sql)) {
						$surala_unit_count = $cdb->num_rows($result);
					}

					if (count($surala_unit_list) > 0) {
						if ($surala_unit_count != count($surala_unit_list)) {
							$ERROR[] = "すららで復習ユニットに指定したユニットは、既に削除されているか、不正な情報が混ざっています。";
							$error_flg = true;
						}
					}
				}
			}

			if (!$LINE['mk_flg']) {
				$LINE['mk_flg'] = "0";
			}

			// エラーの時は強制的に非表示とする。
			if ($error_flg) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " . T_ONE_POINT .
					" WHERE one_point_num = '".$LINE['one_point_num']."';";
			$one_point_count = 0;
			if ($result = $cdb->query($sql)) {
				$one_point_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($one_point_count == 0) {
				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $_POST['course_num'];
				$INSERT_DATA['stage_num']				= $_POST['stage_num'];
				$INSERT_DATA['lesson_num']				= $_POST['lesson_num'];
				$INSERT_DATA['unit_num']				= $_POST['unit_num'];

				$INSERT_DATA['study_title']				= $LINE['study_title'];
				$INSERT_DATA['one_point_commentary']	= $LINE['one_point_commentary'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				$INSERT_DATA['ins_syr_id']		= "add";
				$INSERT_DATA['ins_date']		= "now()";
				$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']		= "add";
				$INSERT_DATA['upd_date']		= "now()";
				$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

				$ERROR_INSERT = $cdb->insert(T_ONE_POINT,$INSERT_DATA);

				if (!$ERROR_INSERT) {
					$one_point_num = $cdb->insert_id();
					$INSERT_DATA['list_num'] = $one_point_num;
					$where = " WHERE one_point_num = '".$one_point_num."' LIMIT 1;";

					$ERROR_INSERT = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

					$ERROR_INSERT = update_surala_unit(
							$INSERT_DATA['course_num'], $INSERT_DATA['stage_num'],
							$INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'],
							0, 0, $one_point_num, implode("::",$surala_unit_list));
				}

			// 更新処理
			} else {

				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $_POST['course_num'];
				$INSERT_DATA['stage_num']				= $_POST['stage_num'];
				$INSERT_DATA['lesson_num']				= $_POST['lesson_num'];
				$INSERT_DATA['unit_num']				= $_POST['unit_num'];

				$INSERT_DATA['study_title']				= $LINE['study_title'];
				$INSERT_DATA['one_point_commentary']	= $LINE['one_point_commentary'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				if ($LINE['mk_flg'] == "0") {
					$INSERT_DATA['upd_syr_id']		= "update";
					$INSERT_DATA['upd_date']		= "now()";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
				} else {
					$INSERT_DATA['display']			= "2";
					$INSERT_DATA['mk_flg']			= "1";
					$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']			= "now()";
					$INSERT_DATA['upd_syr_id']      = "del";									// 2012/11/05 add oda
					$INSERT_DATA['upd_tts_id']      = $_SESSION['myid']['id'];					// 2012/11/05 add oda
					$INSERT_DATA['upd_date']        = "now()";									// 2012/11/05 add oda
				}
				$where = " WHERE one_point_num = '".$LINE['one_point_num']."' LIMIT 1;";

				$ERROR_INSERT = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

				$ERROR_INSERT = update_surala_unit(
						$INSERT_DATA['course_num'], $INSERT_DATA['stage_num'],
						$INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'],
						0, 0, $LINE['one_point_num'], implode("::",$surala_unit_list));

			}

			$i++;
		}
	}

	// アップロードファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}

	return $ERROR;
}

// 2012/09/27 add oda

/**
 * 文字列変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $word
 * @return string
 */
function replace_word($word) {
	$word = mb_convert_kana($word,"asKV","UTF-8");
	$word = ereg_replace("<>","&lt;&gt;",$word);
	$word = trim($word);
	$word = eregi_replace("\r","",$word);
	$word = eregi_replace("\n","<br>",$word);
	$word = replace_encode($word);

	return $word;
}

// 2012/09/28 add oda

/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function create_directory() {

	// ポイント解説イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_POINT_IMG_DIR;
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $_POST['course_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $_POST['course_num']."/".$_POST['stage_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}

	// ポイント解説音声保管ディレクトリ作成
	$material_voice_path = MATERIAL_POINT_VOICE_DIR;
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $_POST['course_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $_POST['course_num']."/".$_POST['stage_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
}
// 2012/09/26 add end oda

// del start 2018/10/09 yoshizawa すらら英単語追加 未使用なのでコメントにします。
// /**
//  * データ表示情報取得
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * return値が0の時は、通常のブロック内容を表示。1の時は、別途内容を表示する
//  * @author Azet
//  * @param integer $course_num
//  * @param integer $stage_num
//  * @param integer $lesson_num
//  * @param integer $unit_num
//  * @return integer
//  */
// function get_data_view_type($course_num, $stage_num, $lesson_num, $unit_num) {
//
// 	// DB接続オブジェクト
// 	$cdb = $GLOBALS['cdb'];
//
// 	// 変数クリア
// 	$data_view_type = "0";
// 	$block_type = "";
// 	$where = "";
//
// 	// SQL条件生成
// 	if ($course_num) {
// 		$where .= "   AND course_num = '".$course_num."'";
// 	}
// 	if ($stage_num) {
// 		$where .= "   AND stage_num = '".$stage_num."'";
// 	}
// 	if ($lesson_num) {
// 		$where .= "   AND lesson_num = '".$lesson_num."'";
// 	}
// 	if ($unit_num) {
// 		$where .= "   AND unit_num = '".$unit_num."'";
// 	}
//
// 	// ブロックタイプ取得（先頭１件のみ）
// 	$sql = "SELECT block_type FROM " . T_BLOCK .
// 			" WHERE state = '0'".
// 			$where.
// 			" ORDER BY list_num LIMIT 1;";
// 	if ($result = $cdb->query($sql)) {
// 		while ($list = $cdb->fetch_assoc($result)) {
// 			$block_type = $list['block_type'];
// 		}
// 	}
//
// 	// ブロックタイプがドリルＡ／ドリルＢ／ドリルＣ／ドリルDの時は１を設定
// 	// if ($block_type == "4" || $block_type == "5" || $block_type == "6") {	// upd hasegawa 2018/02/23 百マス計算
// 	if ($block_type == "4" || $block_type == "5" || $block_type == "6" || $block_type == "7") {
// 		$data_view_type = "1";
// 	}
//
// 	return $data_view_type;
// }
// del end 2018/10/09 yoshizawa すらら英単語追加 未使用なのでコメントにします。

// add start 2018/05/24 yoshizawa 理科社会対応
/**
 * レッスン以下のブロック情報を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @return string $block_num_list
 */
function get_block_num_list($course_num, $stage_num, $lesson_num, $unit_num=""){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$where = "";
	if($unit_num > 0){ " AND bl.unit_num = '".$unit_num."' "; }

	$sql  = "SELECT ".
			" bl.block_num ".
			"FROM ".T_COURSE." co ".
			"INNER JOIN ".T_STAGE." st ON co.course_num = st.course_num ".
			" AND st.state != '1' ".
			"INNER JOIN ".T_LESSON." le ON st.stage_num = le.stage_num ".
			" AND le.state != '1' ".
			"INNER JOIN ".T_UNIT." un ON le.stage_num = un.stage_num ".
			" AND un.state != '1' ".
			"INNER JOIN ".T_BLOCK." bl ON un.unit_num = bl.unit_num ".
			" AND bl.state != '1' ".
			"WHERE co.state != '1' ".
			" AND co.course_num = '".$course_num."' ".
			" AND st.stage_num = '".$stage_num."' ".
			" AND le.lesson_num = '".$lesson_num."' ".
			$where.
			";";
	$block_num_list = "";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if($block_num_list){ $block_num_list .= ","; }
			$block_num_list .= "'".$list['block_num']."'";
		}
	}

	return $block_num_list;

}

/**
 * ブロックスキル情報を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $block_num_list
 * @return array $L_SKILL_INFO
 */
function get_skill_info($course_num, $block_num_list){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_SKILL_INFO = array();

	$sql  = "SELECT ".
			"rs.review_setting_num ".
			",rs.skill_num ".
			",rs.lesson_page ".
			",sk.skill_name ".
			",st.stage_name ".
			",le.lesson_name ".
			",un.unit_name ".
			",rs.block_num ".
			"FROM ".T_REVIEW_SETTING." rs ".
			"LEFT JOIN ".T_SKILL." sk ON rs.skill_num = sk.list_num ".
			" AND sk.state != '1' ".
			" AND sk.course_num = '".$course_num."' ".
			"LEFT JOIN ".T_UNIT." un ON rs.review_unit_num = un.unit_num ".
			"LEFT JOIN ".T_LESSON." le ON un.lesson_num = le.lesson_num ".
			"LEFT JOIN ".T_STAGE." st ON le.stage_num = st.stage_num ".
			"WHERE rs.block_num IN (".$block_num_list.") ".
			" AND rs.state != '1' ".
			";";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_SKILL_INFO[$list['block_num']][] = $list;
		}
	}

	return $L_SKILL_INFO;

}

/**
 * ブロックの問題数を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $block_num_list
 * @return array $L_PROBLEM_COUNT_INFO
 */
function get_problem_count_info($block_num_list){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_PROBLEM_COUNT_INFO = array();

	$sql  = "SELECT ".
			"co.course_num, ".
			"st.stage_num, ".
			"le.lesson_num, ".
			"un.unit_num, ".
			"bk.block_num, ".
			"COUNT(*) AS problem_count ".
			"FROM ".T_PROBLEM." pr ".
			"INNER JOIN ".T_BLOCK." bk ON pr.block_num = bk.block_num AND bk.state='0' ".
			"INNER JOIN ".T_UNIT." un ON bk.unit_num = un.unit_num AND un.state='0' ".
			"INNER JOIN ".T_LESSON." le ON un.lesson_num = le.lesson_num AND le.state='0' ".
			"INNER JOIN ".T_STAGE." st ON le.stage_num = st.stage_num AND st.state='0' ".
			"INNER JOIN ".T_COURSE." co ON st.course_num = co.course_num AND co.state='0' ".
			"WHERE pr.block_num IN (".$block_num_list.") ".
			"AND pr.state='0' ".
			"GROUP BY pr.block_num ".
			"ORDER BY NULL ".
			";";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_PROBLEM_COUNT_INFO[$list['block_num']] = $list['problem_count'];
		}
	}

	return $L_PROBLEM_COUNT_INFO;

}
// add end 2018/05/24 yoshizawa 理科社会対応
?>
