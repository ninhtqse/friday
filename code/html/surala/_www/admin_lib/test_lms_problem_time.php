<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　LMS問題解答目安時間
 *
 * 履歴
 * 2010/12/06 初期設定
 *
 * @author Azet
 */

// hirano

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "sub_session") { $ERROR = sub_session(); }
	elseif (ACTION == "unit_update") { $ERROR = unit_update(); }
	elseif (ACTION == "export") { $ERROR = csv_export(); }
	elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }

	$html .= select_menu();
	if ($_SESSION['t_practice']['course_num']) {
		$html .= lms_problem_list($ERROR);
	}

	return $html;
}

/**
 * 絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_UNIT_TYPE;
	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29
	global $L_SERVICES_SYOU_TEST; //add kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応

	$flag = 0;
	//コース
	//	add ookawara 2012/07/29 start
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	//del start kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
	// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
	// 	if ($course_name == "") {
	// 		continue;
	// 	}
	// 	$selected = "";
	// 	if ($_SESSION['t_practice']['course_num'] == $course_num) {
	// 		$selected = "selected";
	// 	}
	// 	$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
	// }
	//	add ookawara 2012/07/29 end
	////del end   kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
	//add start kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応 サービスで見るように変更します
	$sql = " SELECT c.course_num, c.course_name";
	$sql.= " FROM ".T_SERVICE." s";
	$sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc ON s.service_num = sc.service_num";
	$sql.= " INNER JOIN ".T_COURSE." c ON sc.course_num = c.course_num";
	//upd start hirose 2020/07/15 学習管理画面リニューアル　その他サービスの小テスト対応
	// $sql.= " WHERE s.service_num IN (".implode(",", $L_SERVICES_SYOU_TEST).")";
	$sql.= " WHERE s.mk_flg = 0";
	$sql.= " AND sc.mk_flg = 0";
	$sql.= " AND s.setup_type = 1";
	$sql.= " AND(";
	$sql.= "  (s.setup_type_sub = 1 AND sc.course_type = 1)";
	$sql.= "  OR (s.setup_type_sub = 2 AND sc.course_type = 1)";
	$sql.= " )";
	//upd end hirose 2020/07/15 学習管理画面リニューアル　その他サービスの小テスト対応

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)){
			$selected = "";
			if ($_SESSION['t_practice']['course_num'] == $list['course_num']) {
				$selected = "selected";
			}
			$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
		}
	}
	//add end   kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応

	if ($_SESSION['t_practice']['course_num']) {
		$sql  = "SELECT * FROM ".T_STAGE." WHERE state!='1' AND course_num='".$_SESSION['t_practice']['course_num']."' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$stage_count = $cdb->num_rows($result);
		}
		if (!$stage_count) {
			$stage_html = "<option value=\"0\">設定されていません</option>\n";
		} else {
			$stage_html = "<option value=\"0\">選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
				$stage_html .= "<option value=\"".$list['stage_num']."\" ".$selected.">".$list['stage_name']."</option>\n";
			}
		}
	} else {
		$stage_html .= "<option value=\"0\">--------</option>\n";
	}

	if ($_SESSION['t_practice']['stage_num']) {
		$sql  = "SELECT * FROM ".T_LESSON." WHERE state!='1' AND stage_num='".$_SESSION['t_practice']['stage_num']."' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$lesson_count = $cdb->num_rows($result);
		}
		if (!$lesson_count) {
			$lesson_html = "<option value=\"0\">設定されていません</option>\n";
		} else {
			$lesson_html = "<option value=\"0\">選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
				$lesson_html .= "<option value=\"".$list['lesson_num']."\" ".$selected.">".$list['lesson_name']."</option>\n";
			}
		}
	} else {
		$lesson_html .= "<option value=\"0\">--------</option>\n";
	}

	if ($_SESSION['t_practice']['lesson_num']) {
		$sql  = "SELECT * FROM ".T_UNIT." WHERE state!='1' AND lesson_num='".$_SESSION['t_practice']['lesson_num']."' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$unit_count = $cdb->num_rows($result);
		}
		if (!$unit_count) {
			$unit_html = "<option value=\"0\">設定されていません</option>\n";
		} else {
			$unit_html = "<option value=\"0\">選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['unit_num'] == $list['unit_num']) { $selected = "selected"; } else { $selected = ""; }
				$unit_html .= "<option value=\"".$list['unit_num']."\" ".$selected.">".$list['unit_name']."</option>\n";
			}
		}
	} else {
		$unit_html .= "<option value=\"0\">--------</option>\n";
	}

	if ($_SESSION['t_practice']['unit_num']) {
		$sql  = "SELECT block_num, block_type, display FROM ".T_BLOCK." WHERE state!='1' AND unit_num='".$_SESSION['t_practice']['unit_num']."' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$block_count = $cdb->num_rows($result);
		}
		if (!$block_count) {
			$block_html = "<option value=\"0\">設定されていません</option>\n";
		} else {
			$block_html = "<option value=\"0\">選択して下さい</option>\n";
			$drill_num = 1;
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['block_num'] == $list['block_num']) { $selected = "selected"; } else { $selected = ""; }
				$block_name = $L_UNIT_TYPE[$list['block_type']];
				if ($list['display'] == "2") { $block_name .= "(非表示)"; }
				$block_html .= "<option value=\"".$list['block_num']."\" ".$selected.">".$block_name."</option>\n";
				$drill_num++;
			}
		}
	} else {
		$block_html .= "<option value=\"0\">--------</option>\n";
	}
	if (!$_SESSION['t_practice']['course_num']) {
		$msg_html .= "コースを選択してください。<br>\n";
	} elseif (!$_SESSION['t_practice']['stage_num']) {
		$msg_html .= "ステージを選択してください。<br>\n";
	} elseif (!$_SESSION['t_practice']['lesson_num']) {
		$msg_html .= "Lessonを選択してください。<br>\n";
	} elseif (!$_SESSION['t_practice']['unit_num']) {
		$msg_html .= "ユニットを選択してください。<br>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"select_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_block\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "<td>Lesson</td>\n";
	$html .= "<td>ユニット</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit();\">".$couse_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"stage_num\" onchange=\"submit();\">".$stage_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"lesson_num\" onchange=\"submit();\">".$lesson_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"unit_num\" onchange=\"submit();\">".$unit_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	if ($msg_html) {
		$html .= "<br>\n";
		$html .= $msg_html;
	}

	return $html;
}

/**
 * SESSION情報設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if ($_SESSION['t_practice']['course_num'] != $_POST['course_num']) {
		unset($_SESSION['t_practice']);
	} elseif ($_SESSION['t_practice']['stage_num'] != $_POST['stage_num']) {
		unset($_SESSION['t_practice']['stage_num']);
		unset($_SESSION['t_practice']['lesson_num']);
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
	} elseif ($_SESSION['t_practice']['lesson_num'] != $_POST['lesson_num']) {
		unset($_SESSION['t_practice']['lesson_num']);
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
	} elseif ($_SESSION['t_practice']['unit_num'] != $_POST['unit_num']) {
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
	}

	if($_SESSION['t_practice']['unit_num']) {
		if (strlen($_POST['block_num'])) { $_SESSION['t_practice']['block_num'] = $_POST['block_num']; }
	}
	if($_SESSION['t_practice']['lesson_num']) {
		if (strlen($_POST['unit_num'])) { $_SESSION['t_practice']['unit_num'] = $_POST['unit_num']; }
	}
	if($_SESSION['t_practice']['stage_num']) {
		if (strlen($_POST['lesson_num'])) { $_SESSION['t_practice']['lesson_num'] = $_POST['lesson_num']; }
	}
	if ($_SESSION['t_practice']['course_num']) {
		if (strlen($_POST['stage_num'])) { $_SESSION['t_practice']['stage_num'] = $_POST['stage_num']; }
	}
	if (strlen($_POST['course_num'])) { $_SESSION['t_practice']['course_num'] = $_POST['course_num']; }

	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}

/**
 * 問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function lms_problem_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_BOOK_ORDER,$L_DESC,$L_PAGE_VIEW,$L_CSV_COLUMN;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	} elseif (ACTION == "unit_update") {
		$html .= "<div>\n";
		$html .= "変更が完了しました。";
		$html .= "</div>\n";
	}

	//ページ数
	if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 2; }
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}


	$html .= "<br>\n";
//	if ($_SESSION['t_practice']['lesson_num']) {
		$html .= "インポートする場合は、テスト用問題マスタ属性csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
//	}
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
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

	if (!$_SESSION['t_practice']['unit_num']) { return $html; }

	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['t_practice']['course_num']."\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_SESSION['t_practice']['stage_num']."\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_SESSION['t_practice']['lesson_num']."\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_SESSION['t_practice']['unit_num']."\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_SESSION['t_practice']['block_num']."\">\n";
	$sub_session_html .= "<td>\n";
	$sub_session_html .= "表示数 <select name=\"s_page_view\">\n".$s_page_view_html."</select>\n";
	$sub_session_html .= "<input type=\"submit\" value=\"Set\">\n";
	$sub_session_html .= "</td>\n";
	$sub_session_html .= "</form>\n";

	$html .= "<br><div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</div>\n";

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND block.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND block.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND block.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	if ($_SESSION['t_practice']['unit_num']) {
		$where .= " AND block.unit_num='".$_SESSION['t_practice']['unit_num']."'";
	}
	if ($_SESSION['t_practice']['block_num']) {
		$where .= " AND block.block_num='".$_SESSION['t_practice']['block_num']."'";
	}
	$sql  = "SELECT * FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" LEFT JOIN ".T_PROBLEM." problem ON problem.block_num=block.block_num".
		" AND problem.state='0'".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.block_num=problem.block_num".
		" AND att.mk_flg='0'".
		" WHERE block.state='0' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		" AND block.display='1'".
		$where.
		" GROUP BY block.block_num".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num";
	if ($result = $cdb->query($sql)) {
		$lms_problem_count = $cdb->num_rows($result);
	}
	if (!$lms_problem_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている単元は有りません。<br>\n";
		return $html;
	}

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($lms_problem_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql  = "SELECT problem.course_num,unit.unit_num,unit.unit_key,block.block_num,att.standard_time FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" LEFT JOIN ".T_PROBLEM." problem ON problem.block_num=block.block_num".
		" AND problem.state='0'".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.block_num=problem.block_num".
		" AND att.mk_flg='0'".
		" WHERE block.state='0' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		" AND block.display='1'".
		$where.
		" GROUP BY block.block_num".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num".
		" LIMIT ".$start.",".$page_view.";";
	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		//$html .= "修正する場合は、修正する教科書の変更ボタンを押してください。<br>\n";			// del oda 2014/08/08
		$html .= "修正する場合は、変更ボタンを押してください。<br>\n";						// add oda 2014/08/08 課題要望一覧No298 文言修正
		//$html .= "<div style=\"float:left;\">登録教科書総数(".$lms_problem_count."):PAGE[".$page."/".$max_page."]</div>\n";			// del oda 2014/08/08
		$html .= "<div style=\"float:left;\">登録回答目安時間総数(".$lms_problem_count."):PAGE[".$page."/".$max_page."]</div>\n";		// add oda 2014/08/08 課題要望一覧No298 文言修正
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['t_practice']['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_SESSION['t_practice']['stage_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_SESSION['t_practice']['lesson_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_SESSION['t_practice']['unit_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_SESSION['t_practice']['block_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['t_practice']['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_SESSION['t_practice']['stage_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_SESSION['t_practice']['lesson_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_SESSION['t_practice']['unit_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_SESSION['t_practice']['block_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<br style=\"clear:left;\">";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"unit_update\">\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>ユニットキー</th>\n";
		$html .= "<th>ユニット番号</th>\n";
		$html .= "<th>ブロック番号</th>\n";
		$html .= "<th>目安時間</th>\n";
		$html .= "</tr>\n";
		$drill_num = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN['lms_problem_time'] as $key => $val) {
				if ($key == "unit_key") {
					if ($list['unit_num'] == $unit_num) { $drill_num++; }
					else { $drill_num = 1; }
					$unit_key = $list[$key]."_".$drill_num;
					$unit_num = $list['unit_num'];
				} else {
					$$key = $list[$key];
				}
			}
			if (!isset($standard_time)) { $standard_time = "未設定"; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$unit_key."</td>\n";
			$html .= "<td>".$unit_num."</td>\n";
			$html .= "<td>".$block_num."</td>\n";
			$html .= "<td><input type=\"text\" name=\"standard_time_".$block_num."\" size=\"3\" value=\"".$standard_time."\"></td>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
		$html .= "<br>\n";
		$html .= "<input type=\"submit\" value=\"変更\">\n";
		$html .= "</form>\n";

		$cdb->free_result($result);
	}

	return $html;
}


/**
 * ユニット一括変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function unit_update() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND block.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND block.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND block.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	if ($_SESSION['t_practice']['unit_num']) {
		$where .= " AND block.unit_num='".$_SESSION['t_practice']['unit_num']."'";
	}
	if ($_SESSION['t_practice']['block_num']) {
		$where .= " AND block.block_num='".$_SESSION['t_practice']['block_num']."'";
	}

	$sql  = "SELECT ".
		"block.block_num,".
		"block.course_num,".
		"block.stage_num,".
		"block.lesson_num,".
		"block.unit_num,".
		"IF(att.block_num IS NULL,'add','upd') AS mode".
		" FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" LEFT JOIN ".T_PROBLEM." problem ON problem.block_num=block.block_num".
		" AND problem.state='0'".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.block_num=problem.block_num".
		" AND att.mk_flg='0'".
		" WHERE block.state='0' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		" AND block.display='1'".
		$where.
		" GROUP BY block.block_num".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_INS_BLOCK_LIST[$list['block_num']] = $list['mode'];
		}
	}

	foreach ($L_INS_BLOCK_LIST as $key => $val) {
		//レコードがあればアップデート、無ければインサート
		unset($INSERT_DATA);
		//目安時間に数字以外が入っていた場合、スキップ
		if (preg_match("/[^0-9]/",$_POST['standard_time_'.$key])) { continue; }
		$INSERT_DATA['course_num'] = $_SESSION['t_practice']['course_num'];
		$INSERT_DATA['stage_num'] = $_SESSION['t_practice']['stage_num'];
		$INSERT_DATA['lesson_num'] = $_SESSION['t_practice']['lesson_num'];
		$INSERT_DATA['unit_num'] = $_SESSION['t_practice']['unit_num'];
		$INSERT_DATA['standard_time'] = $_POST['standard_time_'.$key];
		if ($val == "add") {
			$INSERT_DATA['block_num'] 		= $key;
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$INS_ERROR[$key] = $cdb->insert(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE block_num='".$key."' LIMIT 1;";
			$INS_ERROR[$key] = $cdb->update(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA,$where);
		}
	}
	//エラー結合
	if(is_array($INS_ERROR)) {
		foreach($INS_ERROR as $key => $val) {
			if (!$INS_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$INS_ERROR[$key]);
		}
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

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['lms_problem_time'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_problem_attribute_".$_SESSION['t_practice']['course_num'].".csv";

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
 * csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_CSV_COLUMN コラム名
 * @param string $head_mode='1' boolのように
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1') {

	// 分散DB接続オブジェクト
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
		$csv_line .= $head_name.",";
	}
	$csv_line .= "\n";


/*	$sql  = "SELECT * FROM " . T_PROBLEM ." problem".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.problem_num=problem.problem_num".
		" AND att.mk_flg='0'".
		" WHERE problem.state='0' AND problem.block_num='".$block_num."'";
*/
	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND block.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND block.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND block.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	if ($_SESSION['t_practice']['unit_num']) {
		$where .= " AND block.unit_num='".$_SESSION['t_practice']['unit_num']."'";
	}
	if ($_SESSION['t_practice']['block_num']) {
		$where .= " AND block.block_num='".$_SESSION['t_practice']['block_num']."'";
	}
	$sql  = "SELECT problem.course_num,unit.unit_num,unit.unit_key,block.block_num,att.standard_time FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" LEFT JOIN ".T_PROBLEM." problem ON problem.block_num=block.block_num".
		" AND problem.state='0'".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.block_num=problem.block_num".
		" AND att.mk_flg='0'".
		" WHERE block.state='0' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		" AND block.display='1'".
		$where.
		" GROUP BY block.block_num".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num";
	if ($result = $cdb->query($sql)) {
		$drill_num = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				if ($key == "unit_key") {
					if ($list['unit_num'] == $unit_num) { $drill_num++; }
					else { $drill_num = 1; }
					$csv_line .= $list[$key]."_".$drill_num.",";
					$unit_num = $list['unit_num'];
				} else {
					$csv_line .= $list[$key].",";
				}
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	$file_num = preg_replace("/[^0-9]/","",$file_name);
	if (!$file_tmp_name) {
		$ERROR[] = "テスト用問題マスタ属性ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "テスト用問題マスタ属性ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "テスト用問題マスタ属性ファイルがアップロードされませんでした。";
	}
	//	del 2014/12/10 yoshizawa 課題要望No.397対応 -----------------------
	//	ファイル名ではなくCSVデータのコースでチェックを行う(下に新規で作成)
	//if ($_SESSION['t_practice']['course_num'] != $file_num) {
	//	$ERROR[] = "選択しているコースとファイルのコースが違っています。";
	//}
	//---------------------------------------------------------------------


	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return array($html,$ERROR);
	}

	$ERROR = array();

	//登録ブロック読込
	$L_IMPORT_LINE = file($file_tmp_name);
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//登録ブロック番号、登録問題リスト取得
	list($L_INS_BLOCK_LIST,$L_PARENT_NUM) = ins_block_list();

	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);

		$import_line = trim($L_IMPORT_LINE[$i]);
		$empty_check = preg_replace("/,/","",$import_line);
		if (!$empty_check) {
			$ERROR[$i] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode(",",$import_line);
		if (!is_array($L_VALUE)) {
			$ERROR[$i] = $i."行目のcsv入力値が不正です。<br>";
			continue;
		}
		//	add 2014/12/10 yoshizawa 課題要望No.397対応 -------------------------------------------------------
		if ( $_SESSION['t_practice']['course_num'] != $L_VALUE[0] ) {
			$ERROR[$i] = $i."行目の選択しているコースとファイルのコースが違っているのでスキップしました。<br>";
			continue;
		}
		//-----------------------------------------------------------------------------------------------------
		foreach ($L_VALUE as $key => $val) {
		//	if ($val === "") { continue; }
			$val = preg_replace("/^\"|\"$/","",$val);
			$val = trim($val);
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
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}

		//目安時間がなければスキップ
		//if (!$CHECK_DATA['standard_time']) {				//	del 2014/12/10 yoshizawa 課題要望No.397対応 0はFALSE判定になってしまうため
		if ( strval($CHECK_DATA['standard_time']) == "" ) {	//	add 2014/12/10 yoshizawa
			continue;
		}

		//登録ブロック番号でなければスキップ
		if (!$L_INS_BLOCK_LIST[$CHECK_DATA['block_num']]) {
			continue;
		}

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$i);
		if ($DATA_ERROR[$i]) { continue; }

		$INSERT_DATA['course_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['course_num'];
		$INSERT_DATA['stage_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['stage_num'];
		$INSERT_DATA['lesson_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['lesson_num'];
		$INSERT_DATA['unit_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['unit_num'];
		$INSERT_DATA['standard_time'] = $CHECK_DATA['standard_time'];

		//レコードがあればアップデート、無ければインサート
		if ($L_INS_BLOCK_LIST[$CHECK_DATA['block_num']] == "add") {
			$INSERT_DATA['block_num'] 	= $CHECK_DATA['block_num'];
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$INS_ERROR[$key] = $cdb->insert(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE block_num='".$CHECK_DATA['block_num']."' LIMIT 1;";
			$INS_ERROR[$key] = $cdb->update(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA,$where);
		}

		if(is_array($INS_ERROR)) {
			foreach($INS_ERROR as $key => $val) {
				if (!$INS_ERROR[$key]) { continue; }
				$SYS_ERROR[$i] = array_merge($SYS_ERROR[$i],$INS_ERROR[$key]);
			}
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
	}
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
 * データ確認
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data($CHECK_DATA,$line_num) {
	if (!$CHECK_DATA['course_num']) { $ERROR[] = $line_num."行目 コースが未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['course_num'])) {
			$ERROR[] = $line_num."行目 コースは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['course_num'] < 1) {
			$ERROR[] = $line_num."行目 コースは1以下の数字の指定はできません。";
		}
	}
	if (!$CHECK_DATA['block_num']) { $ERROR[] = $line_num."行目 ブロック番号が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['block_num'])) {
			$ERROR[] = $line_num."行目 ブロック番号は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['block_num'] < 1) {
			$ERROR[] = $line_num."行目 ブロック番号は1以下の数字の指定はできません。";
		}
	}
//	if (!$CHECK_DATA['standard_time']) { $ERROR[] = $line_num."行目 回答目安時間が未入力です。"; }
//	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
			$ERROR[] = $line_num."行目 回答目安時間は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['standard_time'] < 1) {
			$ERROR[] = $line_num."行目 回答目安時間は1以下の数字の指定はできません。";
		}
//	}
	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ins_block_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND block.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND block.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND block.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	if ($_SESSION['t_practice']['unit_num']) {
		$where .= " AND block.unit_num='".$_SESSION['t_practice']['unit_num']."'";
	}
	if ($_SESSION['t_practice']['block_num']) {
		$where .= " AND block.block_num='".$_SESSION['t_practice']['block_num']."'";
	}

	$sql  = "SELECT ".
		"block.block_num,".
		"block.course_num,".
		"block.stage_num,".
		"block.lesson_num,".
		"block.unit_num,".
		"IF(att.block_num IS NULL,'add','upd') AS mode".
		" FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" LEFT JOIN ".T_PROBLEM." problem ON problem.block_num=block.block_num".
		" AND problem.state='0'".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.block_num=problem.block_num".
		" AND att.mk_flg='0'".
		" WHERE block.state='0' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		" AND block.display='1'".
		$where.
		" GROUP BY block.block_num".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_INS_BLOCK_LIST[$list['block_num']] = $list['mode'];
			$L_PARENT_NUM[$list['block_num']]['course_num'] = $list['course_num'];
			$L_PARENT_NUM[$list['block_num']]['stage_num'] = $list['stage_num'];
			$L_PARENT_NUM[$list['block_num']]['lesson_num'] = $list['lesson_num'];
			$L_PARENT_NUM[$list['block_num']]['unit_num'] = $list['unit_num'];
		}
	}
	return array($L_INS_BLOCK_LIST,$L_PARENT_NUM);
}

?>
