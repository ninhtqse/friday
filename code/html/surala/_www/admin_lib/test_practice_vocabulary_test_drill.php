<?php
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題検証/すらら英単語
 *
 * 履歴
 * 2018/11/21 初期設定
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
function vocabulary_test_select_unit_view() {

	if (ACTION == "view_session") { $ERROR = view_session_vocabulary(); }
	elseif (ACTION == "sub_list_session") { $ERROR = sub_list_session(); }

	$html .= make_select();
	if ($_SESSION['t_practice']['test_type'] == 6 && $_SESSION['t_practice']['test_category2_num']) {
		$html .= vocabulary_problem_list($ERROR);
	}

	return $html;
}

/**
 * コース、学年、出版社、教科書選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return string HTML
 */
function make_select() {


	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_WRITE_TYPE;
	global $L_GKNN_LIST_TYPE1;

	//テストタイプ
	unset($L_TEST_TYPE[2]);
	unset($L_TEST_TYPE[3]);
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	//pre($_SESSION['t_practice']);

	if (!$_SESSION['t_practice']['test_type']) {
		$msg_html .= "テストタイプを選択してください。";
	} else {
		//すらら英単語種類
		$L_TYPE_LIST = type_list();
		$type_html = "";
		foreach ($L_TYPE_LIST AS $course_num => $course_name) {
			if ($course_name == "") {
				continue;
			}
			$selected = "";
			if ($_SESSION['t_practice']['test_type_num'] == $course_num) {
				$selected = "selected";
			}
			$type_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
		}
		unset($L_TYPE_LIST);
		
		//すらら英単語種別１
		$L_CATEGORY1_LIST = category1_list($_SESSION['t_practice']['test_type_num']);
		$category1_html = "";
		foreach($L_CATEGORY1_LIST as $key => $val) {
			if ($_SESSION['t_practice']['test_category1_num'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$category1_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		unset($L_CATEGORY1_LIST);

		$select_name .= "<td>すらら英単語種類</td>\n";
		$select_name .= "<td>すらら英単語種別１</td>\n";

		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_type_num\" onchange=\"submit();\">".$type_html."</select>\n";
		$select_menu .= "</td>\n";
		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_category1_num\" onchange=\"submit();\">".$category1_html."</select>\n";
		$select_menu .= "</td>\n";
		
		//すらら英単語種別2
		$L_CATEGORY2_LIST = category2_list($_SESSION['t_practice']['test_type_num'],$_SESSION['t_practice']['test_category1_num']);
		$category2_html = "";
		foreach($L_CATEGORY2_LIST as $key => $val) {
			if ($_SESSION['t_practice']['test_category2_num'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$category2_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		unset($L_CATEGORY2_LIST);
		$select_name .= "<td>すらら英単語種別２</td>\n";

		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_category2_num\" onchange=\"submit();\">".$category2_html."</select>\n";
		$select_menu .= "</td>\n";

		if (!$_SESSION['t_practice']['test_category2_num']) {
			$msg_html .= "すらら英単語種類、すらら英単語種別１、すらら英単語種別２を選択してください。<br>\n";
		} 

	}
	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"select_view_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>テストタイプ</td>\n";
	$html .= $select_name;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_type\" onchange=\"document.select_view_menu.submit();\">".$test_type_html."</select>\n";
	$html .= "</td>\n";
	$html .= $select_menu;
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
 * 単元表示メニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array エラーの場合
 */
function view_session_vocabulary() {
	
	if (($_SESSION['t_practice']['test_type_num'] != $_POST['test_type_num'])){
		unset($_SESSION['t_practice']['test_category1_num']);
		unset($_SESSION['t_practice']['test_category2_num']);
		$_SESSION['t_practice']['test_type_num'] = $_POST['test_type_num'];
	}elseif($_SESSION['t_practice']['test_category1_num'] != $_POST['test_category1_num']){
		unset($_SESSION['t_practice']['test_category2_num']);
		$_SESSION['t_practice']['test_category1_num'] = $_POST['test_category1_num'];
	}elseif(strlen($_POST['test_category2_num'])){
		$_SESSION['t_practice']['test_category2_num'] = $_POST['test_category2_num'];
	}
	
	return $ERROR;
}



/**
 * 問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function vocabulary_problem_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	
	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	//未使用？削除
	//if ($authority) { $L_AUTHORITY = explode("::",$authority); }
	//pre($_SESSION['sub_session']);
	unset($_SESSION['sub_session']['select_course']);

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		foreach($L_TEST_ADD_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['add_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$add_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		if ($_SESSION['sub_session']['add_type']['add_type']) {
			$problem_type_html .= "<select name=\"problem_type\" onchange=\"submit();\" style=\"float:left;\">";
			foreach($L_TEST_PROBLEM_TYPE as $key => $val) {
				if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && $key === "surala") { continue; }
				if ($_SESSION['sub_session']['add_type']['problem_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$problem_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
			$problem_type_html .= "</select>\n";
		}
	}


	if ($_SESSION['t_practice']['test_type_num']) {
		$where .= " AND mtcp.test_type_num='".$_SESSION['t_practice']['test_type_num']."'";
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$where .= " AND mtcp.test_category1_num='".$_SESSION['t_practice']['test_category1_num']."'";
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$where .= " AND mtcp.test_category2_num='".$_SESSION['t_practice']['test_category2_num']."'";
	}
	
	
	//管理画面手書き切り替え機能追加
	if(isset($_COOKIE["tegaki_flag"])){
		$_SESSION['TEGAKI_FLAG'] = $_COOKIE["tegaki_flag"];
	}else{
		$_SESSION['TEGAKI_FLAG'] = 1;
	}
	$check = "checked";
	if($_SESSION['TEGAKI_FLAG'] == 0){
		$check = "";
	}
	$onchenge = "onclick=\"this.blur(); this.focus();\" onchange=\"update_tegaki_flg(this,'select_view_menu');\"";
	$html .= "<br><br>";
	$html .= "<div class=\"tegaki-switch\">";
	$html .= "<label>";
	$html .= "<input type=\"checkbox\" name=\"tegaki_control\" ".$check." ".$onchenge." class=\"tegaki-check\"><span class=\"swith-content\"></span><span class=\"swith-button\"></span>";
	$html .= "</label>";
	$html .= "</div>";
	
	
	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
		"SELECT ".
		"mtcp.test_type_num,".
		"mtcp.test_category1_num,".
		"mtcp.test_category2_num,".
		"mtcp.problem_table_type,".
		"mtcp.problem_num,".
		"mtcp.list_num,".
		"p.form_type".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		//$join_sql.
		" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtcp.problem_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_table_type='1'".
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num".
		" UNION ALL ".
		"SELECT ".
		"mtcp.test_type_num,".
		"mtcp.test_category1_num,".
		"mtcp.test_category2_num,".
		"mtcp.problem_table_type,".
		"mtcp.problem_num,".
		"mtcp.list_num,".
		"mtp.form_type".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		//$join_sql.
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num = mtcp.problem_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_table_type='3'".
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
// echo $sql."<br><br>";
	$cdb->exec_query($sql);
	
	//総件数
	if($result = $cdb->query("SELECT * FROM test_problem_list")){
		$problem_count = $cdb->num_rows($result);
	}
	
	$html .= select_list();
	
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";
	
	if (!$problem_count) {
		$html .= "<br>\n";
		$html .= "<br style=\"clear:left;\">";
		$html .= "今現在登録されている問題は有りません。<br>\n";
		return $html;
	}

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY test_type_num,test_category1_num,test_category2_num,list_num".
		$limit;
//	echo $sql;
	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		//add start hirose 2018/12/07 すらら英単語(音声自動再生)
		$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
		// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
		$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
		//add end hirose 2018/12/07 すらら英単語(音声自動再生)
		$html .= "<div style=\"float:left;\">登録問題数(".$problem_count."):PAGE[".$page."/".$max_page."]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_list_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_list_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if ($_SESSION['t_practice']['test_type'] == 4) {
			$html .= "<th>テストID</th>\n";
		}
		$html .= "<th>No</th>\n";
		$html .= "<th>問題管理番号</th>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		$html .= "<th>確認</th>\n";
		$html .= "</tr>\n";
		$j=$start;
		while ($list = $cdb->fetch_assoc($result)) {
			$j++;
			if ($list['problem_table_type'] == 1) {
				$table_type = "すらら英単語";
			} elseif ($list['problem_table_type'] == 3) {
				$table_type = "テスト専用";
			}
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$list['list_num']."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//add start hirose 2018/12/07 すらら英単語(音声自動再生)
			if ($list[form_type] == 16 || $list[form_type] == 17){
				// >>> update 引数にtest_type_num,test_category1_num,test_category2_numを追加
				// $html .= "<input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";
				$html .= "<input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['test_type_num']."','".$list['test_category1_num']."','".$list['test_category2_num']."')\"></td>\n";
				// <<<
			}else{
			//add end hirose 2018/12/07 すらら英単語(音声自動再生)
				// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";
				// >>> update 引数にtest_type_num,test_category1_num,test_category2_numを追加
				// $html .= "<input type=\"button\" value=\"確認\" onclick=\"vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";//upd hirose 2018/12/07 すらら英単語
				$html .= "<input type=\"button\" value=\"確認\" onclick=\"vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['test_type_num']."','".$list['test_category1_num']."','".$list['test_category2_num']."')\">\n";
				// <<<
			}//end hirose 2018/12/07 すらら英単語(音声自動再生)
			$html .= "</form>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	}


	return $html;
}


/**
 * すらら英単語種類の一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function type_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TYPE_LIST = array();
	$L_TYPE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_TYPE. " mtt ".
			" WHERE mtt.mk_flg='0' ".
			" ORDER BY mtt.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_TYPE_LIST[$list['test_type_num']] = $list['test_type_name'];
		}
	}
	return $L_TYPE_LIST;
}
/**
 * すらら英単語種別１の一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function category1_list($test_type_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CATEGORY1_LIST = array();
	$L_CATEGORY1_LIST[] = "選択して下さい";
	$sql  = "SELECT mtc1.* FROM ".T_MS_TEST_CATEGORY1. " mtc1 ".
			" INNER JOIN ".T_MS_TEST_TYPE." mtt ON mtc1.test_type_num = mtt.test_type_num ".
			" WHERE mtc1.mk_flg='0' ".
			"   AND mtc1.test_type_num='".$test_type_num."' ".
			"   AND mtt.mk_flg='0' ".
			" ORDER BY mtc1.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY1_LIST[$list['test_category1_num']] = $list['test_category1_name'];
		}
	}
	return $L_CATEGORY1_LIST;
}

/**
 * すらら英単語種別２の一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function category2_list($test_type_num,$test_category1_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CATEGORY2_LIST = array();
	$L_CATEGORY2_LIST[] = "選択して下さい";
	$sql  = "SELECT mtc2.* FROM ".T_MS_TEST_CATEGORY2. " mtc2 ".
			" INNER JOIN ".T_MS_TEST_CATEGORY1. " mtc1 ON mtc2.test_type_num = mtc1.test_type_num ".
			" AND mtc2.test_category1_num = mtc1.test_category1_num ".
			" INNER JOIN ".T_MS_TEST_TYPE." mtt ON mtc2.test_type_num = mtt.test_type_num ".
			" WHERE mtc2.mk_flg='0' ".
			"   AND mtc2.test_type_num='".$test_type_num."' ".
			"   AND mtc2.test_category1_num='".$test_category1_num."' ".
			"   AND mtc1.mk_flg='0' ".
			"   AND mtt.mk_flg='0' ".  
			" ORDER BY mtc2.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY2_LIST[$list['test_category2_num']] = $list['test_category2_name'];
		}
	}
	return $L_CATEGORY2_LIST;
}
?>
