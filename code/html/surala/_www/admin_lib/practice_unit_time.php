<?
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　ユニット別目安学習時間設定
 *
 * 履歴
 * 2012/01/10 初期設定
 *
 * @author Azet
 */

// ozaki

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

	global $L_UNIT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$flag = 0;
	$sql  = "SELECT * FROM ".T_COURSE." WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$course_count = $cdb->num_rows($result);
	}
	if (!$course_count) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return $html;
	}
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['t_practice']['course_num'] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
	}

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

	if (!$_SESSION['t_practice']['course_num']) {
		$msg_html .= "コースを選択してください。<br>\n";
	} elseif (!$_SESSION['t_practice']['stage_num']) {
		$msg_html .= "ステージを選択してください。<br>\n";
	//} elseif (!$_SESSION['t_practice']['lesson_num']) {	//	del ookawara 2012/05/10
		//$msg_html .= "Lessonを選択してください。<br>\n";	//	del ookawara 2012/05/10
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
 * SESSION設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if ($_SESSION['t_practice']['course_num'] != $_POST['course_num']) {
		unset($_SESSION['t_practice']);
		unset($_SESSION['sub_session']['s_page']);	//	add ookawara 2012/05/10
	} elseif ($_SESSION['t_practice']['stage_num'] != $_POST['stage_num']) {
		unset($_SESSION['t_practice']['stage_num']);
		unset($_SESSION['t_practice']['lesson_num']);
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
		unset($_SESSION['sub_session']['s_page']);	//	add ookawara 2012/05/10
	} elseif ($_SESSION['t_practice']['lesson_num'] != $_POST['lesson_num']) {
		unset($_SESSION['t_practice']['lesson_num']);
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
		unset($_SESSION['sub_session']['s_page']);	//	add ookawara 2012/05/10
	} elseif ($_SESSION['t_practice']['unit_num'] != $_POST['unit_num']) {
		unset($_SESSION['t_practice']['unit_num']);
		unset($_SESSION['t_practice']['block_num']);
		unset($_SESSION['sub_session']['s_page']);	//	add ookawara 2012/05/10
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

	global $L_BOOK_ORDER,$L_DESC,$L_PAGE_VIEW,$L_CSV_COLUMN;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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
		//$html .= "インポートする場合は、テスト用問題マスタ属性csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";			// del oda 2014/08/06 課題要望一覧No285
		//$html .= "インポートする場合は、ユニット回答目安時間設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";			// add oda 2014/08/06 課題要望一覧No285 文言が違うので、修正	// del oda 2016/11/11 課題要望一覧No566
		$html .= "インポートする場合は、ユニット別目安学習時間設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";			// add oda 2016/11/11 課題要望一覧No566 文言修正
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

	//if (!$_SESSION['t_practice']['lesson_num']) { return $html; }	//	del ookawara 2012/05/10
	if (!$_SESSION['t_practice']['stage_num']) { return $html; }	//	add ookawara 2012/05/10

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
		$where .= " AND unit.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND unit.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND unit.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	$sql  = "SELECT * FROM " . T_UNIT ." unit".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=unit.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=unit.lesson_num".
		" LEFT JOIN ".T_PACKAGE_STANDARD_TIME." pst ON pst.unit_num=unit.unit_num".
		" AND pst.mk_flg='0'".
		" WHERE unit.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		$where.
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num";
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

	//update start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業↓　
	//$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time FROM " . T_UNIT ." unit".
	$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time,pst.ps_standard_time FROM " . T_UNIT ." unit".
	//update end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業↓　
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=unit.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=unit.lesson_num".
		" LEFT JOIN ".T_PACKAGE_STANDARD_TIME." pst ON pst.unit_num=unit.unit_num".
		" AND pst.mk_flg='0'".
		" WHERE unit.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		$where.
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num".
		" LIMIT ".$start.",".$page_view.";";
	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		//$html .= "修正する場合は、修正する教科書の変更ボタンを押してください。<br>\n";			// del oda 2014/08/06 課題要望一覧No285
		$html .= "修正する場合は、変更ボタンを押してください。<br>\n";						// add oda 2014/08/06 課題要望一覧No285 文言修正
		//$html .= "<div style=\"float:left;\">登録教科書総数(".$lms_problem_count."):PAGE[".$page."/".$max_page."]</div>\n";			// del oda 2014/08/06 課題要望一覧No285
		//$html .= "<div style=\"float:left;\">登録ユニット回答目安時間総数(".$lms_problem_count."):PAGE[".$page."/".$max_page."]</div>\n";	// add oda 2014/08/06 課題要望一覧No285 文言修正	// del oda 2016/11/11 課題要望一覧No566
		$html .= "<div style=\"float:left;\">登録ユニット別目安学習時間総数(".$lms_problem_count."):PAGE[".$page."/".$max_page."]</div>\n";	// add oda 2016/11/11 課題要望一覧No566 文言修正
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['t_practice']['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_SESSION['t_practice']['stage_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_SESSION['t_practice']['lesson_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_SESSION['t_practice']['unit_num']."\">\n";
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
		//update start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業　
		//$html .= "<th>目安時間</th>\n";
		$html .= "<th>目安時間(すらら)</th>\n"; 
		//update end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業　
		$html .= "<th>目安時間(ピタドリ)</th>\n"; //add  hirose 2017/10/17 ピタドリ解答目安時間切り分け作業　
		$html .= "</tr>\n";
		$drill_num = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			//update start hirose 2017/10/24 ピタドリ解答目安時間切り分け作業
			//foreach ($L_CSV_COLUMN['lms_problem_time'] as $key => $val) {
			foreach ($L_CSV_COLUMN['unit_time'] as $key => $val) {
			//update end hirose 2017/10/24 ピタドリ解答目安時間切り分け作業
				$$key = $list[$key];
			}
			if (!isset($standard_time)) { $standard_time = "未設定"; }
			if (!isset($ps_standard_time)) { $ps_standard_time = "未設定"; } //add hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$unit_key."</td>\n";
			$html .= "<td>".$unit_num."</td>\n";
			$html .= "<td><input type=\"text\" name=\"standard_time_".$unit_num."\" size=\"3\" value=\"".$standard_time."\"></td>\n";
			//add hirose 2017/10/17 ピタドリ解答目安時間切り分け作業↓　
			$html .= "<td><input type=\"text\" name=\"ps_standard_time_".$unit_num."\" size=\"3\" value=\"".$ps_standard_time."\"></td>\n";
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();


	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND unit.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND unit.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND unit.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time,pst.ps_standard_time,".
		"IF(pst.unit_num IS NULL,'add','upd') AS mode".
		" FROM " . T_UNIT ." unit".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=unit.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=unit.lesson_num".
		" LEFT JOIN ".T_PACKAGE_STANDARD_TIME." pst ON pst.unit_num=unit.unit_num".
		" AND pst.mk_flg='0'".
		" WHERE unit.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		$where.
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_INS_UNIT_LIST[$list['unit_num']] = $list['mode'];
			$L_INS_UNIT_KEY[$list['unit_num']] = $list['unit_key'];	// 2012/03/27 add ozaki
			// add start hirose 2017/10/17 POST値全てがインサートに該当してしまうための対策
			$L_INS_UNIT_STANDARD_TIME[$list['unit_num']] = $list['standard_time'];	
			$L_INS_UNIT_PS_STANDARD_TIME[$list['unit_num']] = $list['ps_standard_time'];	
			// add end hirose 2017/10/17 POST値全てがインサートに該当してしまうための対策
			
		}
	}

	foreach ($L_INS_UNIT_LIST as $key => $val) {
		//レコードがあればアップデート、無ければインサート
		unset($INSERT_DATA);
		//目安時間に数字以外が入っていた場合、スキップ
		if (preg_match("/[^0-9]/",$_POST['standard_time_'.$key]) && preg_match("/[^0-9]/",$_POST['ps_standard_time_'.$key])) { continue; }	// add hasegawa 2017/10/31 ピタドリ解答目安時間切り分け作業
		// if (preg_match("/[^0-9]/",$_POST['ps_standard_time_'.$key])) { continue; }// add hirose 2017/10/17 ピタドリ解答目安時間切り分け作業  // del hasegawa 2017/10/31

		//	POSTで送られて来た変数でなければスキップ
		//	add ookawara 2012/05/10
		$hen_name = 'standard_time_'.$key;
		if (!isset($_POST[$hen_name])) {
			continue;
		}
		
		// add start hirose 2017/10/17 POST値全てがインサートに該当してしまうための対策
		//すららとピタドリの目安時間がDB情報と同じだったらスキップ
		$ps_hen_name = 'ps_standard_time_'.$key;
		if ($L_INS_UNIT_STANDARD_TIME[$key] == $_POST[$hen_name] && $L_INS_UNIT_PS_STANDARD_TIME[$key] == $_POST[$ps_hen_name]) {
			continue;
		}
		// add end hirose 2017/10/17 POST値全てがインサートに該当してしまうための対策

		$INSERT_DATA['course_num'] = $_SESSION['t_practice']['course_num'];
//		$INSERT_DATA['stage_num'] = $_SESSION['t_practice']['stage_num'];
//		$INSERT_DATA['lesson_num'] = $_SESSION['t_practice']['lesson_num'];
		$INSERT_DATA['unit_key'] = $L_INS_UNIT_KEY[$key];	// 2012/03/27 add ozaki
		$INSERT_DATA['standard_time'] = $_POST['standard_time_'.$key];
		$INSERT_DATA['ps_standard_time'] = $_POST['ps_standard_time_'.$key]; //add hirose 2017/10/17 ピタドリ解答目安時間切り分け作業

		if ($val == "add") {
			$INSERT_DATA['unit_num'] 		= $key;
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$INS_ERROR[$key] = $cdb->insert(T_PACKAGE_STANDARD_TIME,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";
			$where = " WHERE unit_num='".$key."' LIMIT 1;";
			
			$INS_ERROR[$key] = $cdb->update(T_PACKAGE_STANDARD_TIME,$INSERT_DATA,$where);
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
 * プロセス完了(exit)
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['unit_time'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "unit_time_".$_SESSION['t_practice']['course_num'].".csv";

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
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1') {

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
		$csv_line .= $head_name.",";
	}
	$csv_line .= "\n";


/*	$sql  = "SELECT * FROM " . T_PROBLEM ." problem".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." att ON att.problem_num=problem.problem_num".
		" AND att.mk_flg='0'".
		" WHERE problem.state='0' AND problem.block_num='".$block_num."'";
*/
	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND unit.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND unit.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND unit.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	//update start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
	//$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time,".
	$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time,pst.ps_standard_time,".
	//update end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		"IF(pst.unit_num IS NULL,'add','upd') AS mode".
		" FROM " . T_UNIT ." unit".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=unit.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=unit.lesson_num".
		" LEFT JOIN ".T_PACKAGE_STANDARD_TIME." pst ON pst.unit_num=unit.unit_num".
		" AND pst.mk_flg='0'".
		" WHERE unit.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		$where.
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num";

	if ($result = $cdb->query($sql)) {
		$drill_num = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$csv_line .= $list[$key].",";
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	global $L_CSV_COLUMN;//add hirose 2017/11/22 ピタドリ解答目安切り分け作業

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	$file_num = preg_replace("/[^0-9]/","",$file_name);
	if (!$file_tmp_name) {
		$ERROR[] = "ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		//$ERROR[] = "テスト用問題マスタ属性ファイルの一部分のみしかアップロードされませんでした。";		// del oda 2014/08/06 課題要望一覧No285
		//$ERROR[] = "ユニット回答目安時間設定ファイルの一部分のみしかアップロードされませんでした。";		// add oda 2014/08/06 課題要望一覧No285 文言修正	// del oda 2016/11/11 課題要望一覧No566
		$ERROR[] = "ユニット別目安学習時間設定ファイルの一部分のみしかアップロードされませんでした。";		// add oda 2016/11/11 課題要望一覧No566 文言修正
	} elseif ($file_error == 4) {
		//$ERROR[] = "テスト用問題マスタ属性ファイルがアップロードされませんでした。";		// del oda 2014/08/06 課題要望一覧No285
		//$ERROR[] = "ユニット回答目安時間設定ファイルがアップロードされませんでした。";	// add oda 2014/08/06 課題要望一覧No285 文言修正	// del oda 2016/11/11 課題要望一覧No566
		$ERROR[] = "ユニット別目安学習時間設定ファイルがアップロードされませんでした。";		// add oda 2016/11/11 課題要望一覧No566 文言修正
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
$INS_ERROR = array();
$DATA_ERROR = array();
$SYS_ERROR = array();

	//登録ブロック読込
	$L_IMPORT_LINE = file($file_tmp_name);
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//登録ブロック番号、登録問題リスト取得
	list($L_INS_UNIT_LIST,$L_PARENT_NUM) = ins_unit_list();

	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	//add start hirose 2017/11/22 ピタドリ解答目安切り分け作業
	$inport_list_count = count(array_filter($L_LIST_NAME)); 
	$list_count = count($L_CSV_COLUMN['unit_time']);
	if($inport_list_count != $list_count){
		$ERROR = "CSVファイルの項目数が一致しません<br>";
		$html = "<br>インポートできませんでした。";
	}
	if(!$ERROR){
	//add end hirose 2017/11/22 ピタドリ解答目安切り分け作業
	
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
				if ($L_LIST_NAME[$key] === "") { continue; }
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
			//add start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
			if ( strval($CHECK_DATA['ps_standard_time']) == "" ) {
				continue;
			}
			//add end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業

			//登録ユニット番号でなければスキップ
			if (!$L_INS_UNIT_LIST[$CHECK_DATA['unit_num']]) {
				continue;
			}
			//データチェック
			$DATA_ERROR[$i] = check_data($CHECK_DATA,$i);
			if ($DATA_ERROR[$i]) { continue; }

			$INSERT_DATA['course_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['course_num'];
	//		$INSERT_DATA['stage_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['stage_num'];
	//		$INSERT_DATA['lesson_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['lesson_num'];
	//		$INSERT_DATA['unit_num'] = $L_PARENT_NUM[$CHECK_DATA['block_num']]['unit_num'];
			$INSERT_DATA['unit_key'] = $CHECK_DATA['unit_key'];		//2012/03/27 add ozaki
			$INSERT_DATA['standard_time'] = $CHECK_DATA['standard_time'];
			$INSERT_DATA['ps_standard_time'] = $CHECK_DATA['ps_standard_time']; //add hirose 2017/10/17 ピタドリ解答目安時間切り分け作業

			//レコードがあればアップデート、無ければインサート

			if ($L_INS_UNIT_LIST[$CHECK_DATA['unit_num']] == "add") {
				$INSERT_DATA['unit_num'] 	= $CHECK_DATA['unit_num'];
	//			$INSERT_DATA[ins_syr_id] 		= ;
				$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[ins_date] 			= "now()";
				$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[upd_date] 			= "now()";

				$INS_ERROR[$key] = $cdb->insert(T_PACKAGE_STANDARD_TIME,$INSERT_DATA);
			} else {
	//			$INSERT_DATA[upd_syr_id] 		= ;
				$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[upd_date] 			= "now()";

				$where = " WHERE unit_num='".$CHECK_DATA['unit_num']."' LIMIT 1;";
				$INS_ERROR[$key] = $cdb->update(T_PACKAGE_STANDARD_TIME,$INSERT_DATA,$where);
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
	
	}//add hirose 2017/11/22 ピタドリ解答目安切り分け作業

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
/*
	if (!$CHECK_DATA['block_num']) { $ERROR[] = $line_num."行目 ブロック番号が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['block_num'])) {
			$ERROR[] = $line_num."行目 ブロック番号は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['block_num'] < 1) {
			$ERROR[] = $line_num."行目 ブロック番号は1以下の数字の指定はできません。";
		}
	}
*/
//	if (!$CHECK_DATA['standard_time']) { $ERROR[] = $line_num."行目 回答目安時間が未入力です。"; }
//	else {
		// del start hasegawa 2017/10/31 ピタドリ解答目安時間切り分け作業
		// // if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		// 	//update start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// 	//$ERROR[] = $line_num."行目 回答目安時間は数字以外の指定はできません。";
		// 	$ERROR[] = $line_num."行目 回答目安時間(すらら)は数字以外の指定はできません。";
		// 	//update end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// } elseif ($CHECK_DATA['standard_time'] < 1) {
		// 	//update start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// 	//$ERROR[] = $line_num."行目 回答目安時間は1以下の数字の指定はできません。";
		// 	$ERROR[] = $line_num."行目 回答目安時間(すらら)は1以下の数字の指定はできません。";
		// 	//update end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// }
		// //add start hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// if (preg_match("/[^0-9]/",$CHECK_DATA['ps_standard_time'])) {
		// 	$ERROR[] = $line_num."行目 回答目安時間(ピタドリ)は数字以外の指定はできません。";
		// } elseif ($CHECK_DATA['ps_standard_time'] < 1) {
		// 	$ERROR[] = $line_num."行目 回答目安時間(ピタドリ)は1以下の数字の指定はできません。";
		// }
		// //add end hirose 2017/10/17 ピタドリ解答目安時間切り分け作業
		// del end hasegawa 2017/10/31
//	}

	// add start hasegawa 2017/10/31 ピタドリ解答目安時間切り分け作業
	if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		$ERROR[] = $line_num."行目 回答目安時間(すらら)は数字以外の指定はできません。";
	}
	if (preg_match("/[^0-9]/",$CHECK_DATA['ps_standard_time'])) {
		$ERROR[] = $line_num."行目 回答目安時間(ピタドリ)は数字以外の指定はできません。";
	}
	// add end hasegawa 2017/10/31

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function ins_unit_list() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND unit.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['t_practice']['stage_num']) {
		$where .= " AND unit.stage_num='".$_SESSION['t_practice']['stage_num']."'";
	}
	if ($_SESSION['t_practice']['lesson_num']) {
		$where .= " AND unit.lesson_num='".$_SESSION['t_practice']['lesson_num']."'";
	}
	$sql  = "SELECT unit.course_num,unit.unit_num,unit.unit_key,pst.standard_time,".
		"IF(pst.unit_num IS NULL,'add','upd') AS mode".
		" FROM " . T_UNIT ." unit".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=unit.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=unit.lesson_num".
		" LEFT JOIN ".T_PACKAGE_STANDARD_TIME." pst ON pst.unit_num=unit.unit_num".
		" AND pst.mk_flg='0'".
		" WHERE unit.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0'".
		" AND lesson.state='0'".
		" AND unit.state='0'".
		" AND stage.display='1'".
		" AND lesson.display='1'".
		" AND unit.display='1'".
		$where.
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_INS_UNIT_LIST[$list['unit_num']] = $list['mode'];
			$L_PARENT_NUM[$list['block_num']]['course_num'] = $list['course_num'];
			$L_PARENT_NUM[$list['block_num']]['stage_num'] = $list['stage_num'];
			$L_PARENT_NUM[$list['block_num']]['lesson_num'] = $list['lesson_num'];
			$L_PARENT_NUM[$list['block_num']]['unit_num'] = $list['unit_num'];
		}
	}
	return array($L_INS_UNIT_LIST,$L_PARENT_NUM);
}
?>