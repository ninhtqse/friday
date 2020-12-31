<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題設定/すらら英単語
 *
 * 履歴
 * 2018/09/28 初期設定 yamaguchi
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function eng_word_test_start() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

//echo '■MODE => '.MODE;
//echo '■ACTION => '.ACTION;
//echo '■problem_type => '.$_SESSION['sub_session']['add_type']['problem_type'];
//echo '■add_type => '.$_SESSION['sub_session']['add_type']['add_type'];

	// 同時に処理がされたときに不正なデータができないように
	// トランザクションを追加して処理がぶつかることを防ぎます。
	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
	}

	if (ACTION == "ewt_check") { $ERROR = ewt_check(); }
	elseif (ACTION == "ewt_surala_check") { $ERROR = ewt_surala_add_check(); }	//既存のすらら英単語問題を選んで、[追加確認]を押したときの処理
	elseif (ACTION == "ewt_problem_check") { $ERROR = ewt_test_add_check(); }	//新規のテスト問題を選んで、[追加確認]を押したときの処理
	elseif (ACTION == "ewt_exist_check") { $ERROR = ewt_test_exist_check(); }	//既存のテスト問題を選んで、[追加確認]を押したときの処理

	if (!$ERROR) {
		if (ACTION == "ewt_change") { $ERROR = ewt_change(); }	//登録済みテスト問題[変更]→[修正]ボタン押下時
		elseif (ACTION == "ewt_del") { $ERROR = ewt_change(); }		//削除→確認画面にて[削除]押下時
		elseif (ACTION == "ewt_sub_test_session") { $ERROR = ewt_sub_test_session(); }
		elseif (ACTION == "ewt_export") { $ERROR = ewt_csv_export(); }					//エクスポート
		elseif (ACTION == "ewt_import") { list($html,$ERROR) = ewt_csv_import(); }		//インポート
		elseif (ACTION == "ewt_surala_add") { $ERROR = ewt_surala_add_add(); }		//既存のすらら英単語問題を、最終的に[登録]する処理
		elseif (ACTION == "ewt_problem_add") { $ERROR = ewt_test_add_add(); }	//新規のテスト問題を、最終的に[登録]する処理
		elseif (ACTION == "ewt_exist_add") { $ERROR = ewt_test_exist_add(); }		//既存のテスト問題を、最終的に[登録]する処理
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
	}

	$html .= eng_word_select_unit_view();

	if (MODE == "ewt_add") {
		// 既存のすらら英単語問題登録
		if ($_SESSION['sub_session']['add_type']['problem_type'] == "surala") {
			if (ACTION == "ewt_surala_check") {
				if (!$ERROR) { $html .= ewt_surala_add_check_html(); }
				else { $html .= ewt_surala_add_addform($ERROR); }
			} elseif (ACTION == "ewt_surala_add") {
				if (!$ERROR) { $html .= eng_word_test_list($ERROR); }
				else { $html .= ewt_surala_add_addform($ERROR); }
			} else {
				$html .= ewt_surala_add_addform($ERROR);
			}
		// テスト専用問題登録
		} elseif($_SESSION['sub_session']['add_type']['problem_type'] == "test") {
			// 新規のテスト登録
			if ($_SESSION['sub_session']['add_type']['add_type'] == "add") {
				if (ACTION == "ewt_problem_check") {
					if (!$ERROR) { $html .= ewt_test_add_check_html(); }
					else { $html .= ewt_test_add_addform($ERROR); }
				} elseif (ACTION == "ewt_problem_add") {
					if (!$ERROR) { $html .= eng_word_test_list($ERROR); }
					else { $html .= ewt_test_add_addform($ERROR); }
				} else {
					$html .= ewt_test_add_addform($ERROR);
				}
			// 既存のテスト専用問題登録
			} elseif ($_SESSION['sub_session']['add_type']['add_type'] == "exist") {
				if (ACTION == "ewt_exist_check") {
					if (!$ERROR) { $html .= ewt_test_exist_check_html(); }
					else { $html .= ewt_test_exist_addform($ERROR); }
				} elseif (ACTION == "ewt_exist_add") {
					if (!$ERROR) { $html .= eng_word_test_list($ERROR); }
					else { $html .= ewt_test_exist_addform($ERROR); }
				} else {
					$html .= ewt_test_exist_addform($ERROR);
				}
			}
		}
	} elseif (MODE == "ewt_view") {
		if (ACTION == "ewt_check") {
			if (!$ERROR) { $html .= ewt_check_html(); }
			else { $html .= ewt_viewform($ERROR); }
		} elseif (ACTION == "ewt_change") {
			if (!$ERROR) { $html .= eng_word_test_list($ERROR); }
			else { $html .= ewt_viewform($ERROR); }
		} else {
			$html .= ewt_viewform($ERROR);
		}
	} elseif (MODE == "ewt_delete") {
		if (ACTION == "ewt_change") {
			if (!$ERROR) { $html .= eng_word_test_list($ERROR); }
			else { $html .= ewt_check_html(); }
		} else {
			$html .= ewt_check_html();
		}
	} else {
		$html .= eng_word_test_list($ERROR);
	}
		
	return $html;
}


/**
 * すらら英単語種類、英単語種別１、英単語種別２選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function eng_word_select_unit_view() {

	global $L_TEST_TYPE;

	
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TYPE_LIST = type_list();
	$L_CATEGORY1_LIST = category1_list($_SESSION['t_practice']['test_type_num']);
	$L_CATEGORY2_LIST = category2_list($_SESSION['t_practice']['test_type_num'],$_SESSION['t_practice']['test_category1_num']);
	
	//テストタイプ
	unset($L_TEST_TYPE[2]);
	unset($L_TEST_TYPE[3]);

	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}

	if (!$_SESSION['t_practice']['test_type']) {
		$msg_html .= "テストタイプを選択してください。";
	} else {
		//すらら英単語種類
		foreach ($L_TYPE_LIST AS $test_type_num => $test_type_name) {
			if ($test_type_name == "") {
				continue;
			}
			$selected = "";
			if ($_SESSION['t_practice']['test_type_num'] == $test_type_num) {
				$selected = "selected";
			}
			$type_html .= "<option value=\"".$test_type_num."\" ".$selected.">".$test_type_name."</option>\n";
		}
		
		foreach($L_CATEGORY1_LIST as $test_category1_num => $test_category1_name) {
			if ($test_category1_name == "") {
				continue;
			}
			$selected = "";
			if ($_SESSION['t_practice']['test_category1_num'] == $test_category1_num) {
				$selected = "selected";
			}
			$category1_html .= "<option value=\"".$test_category1_num."\" ".$selected.">".$test_category1_name."</option>\n";
		}
		
		foreach($L_CATEGORY2_LIST as $test_category2_num => $test_category2_name) {
			if ($test_category2_name == "") {
				continue;
			}
			$selected = "";
			if ($_SESSION['t_practice']['test_category2_num'] == $test_category2_num) {
				$selected = "selected";
			}
			$category2_html .= "<option value=\"".$test_category2_num."\" ".$selected.">".$test_category2_name."</option>\n";
		}
		
		if(!$_POST['test_action']){
			if (!$_SESSION['t_practice']['test_type_num'] || !$_SESSION['t_practice']['test_category1_num'] || !$_SESSION['t_practice']['test_category2_num'] ) {
				$msg_html .= "すらら英単語種類、すらら英単語種別１、すらら英単語種別２を選択してください。<br>\n";
			}
		}

		//----------------------------------------------

		$select_name .= "<td>すらら英単語種類</td>\n";
		$select_name .= "<td>すらら英単語種別１</td>\n";
		$select_name .= "<td>すらら英単語種別２</td>\n";

		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_type_num\" onchange=\"submit();\">".$type_html."</select>\n";
		$select_menu .= "</td>\n";
		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_category1_num\" onchange=\"submit();\">".$category1_html."</select>\n";
		$select_menu .= "</td>\n";
		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"test_category2_num\" onchange=\"submit();\">".$category2_html."</select>\n";
		$select_menu .= "</td>\n";


	}
	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"select_view_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"eng_word_view_session\">\n";
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
 * 問題一覧(すらら英単語テスト)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function eng_word_test_list($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	global $L_EXP_CHA_CODE;

	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }
	unset($_SESSION['sub_session']['select_course']);
	
	$_SESSION['t_practice']['course_num'] = 0;
	
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	
	// すらら英単語種類が未選択の場合は一覧を表示しない
	if (!$_SESSION['t_practice']['test_type_num']
		 || !$_SESSION['t_practice']['test_category1_num']
		 || !$_SESSION['t_practice']['test_category2_num']) {
		return $html;
	}
	
	//print_r($_SESSION['t_practice']);
	
	$html .= "<br>\n";
	$html .= "インポートする場合は、問題設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_import\">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_export\">\n";

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
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		foreach($L_TEST_ADD_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['add_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$add_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		if ($_SESSION['sub_session']['add_type']['add_type']) {
			$problem_type_html .= "<select name=\"problem_type\" onchange=\"submit();\" style=\"float:left;\">";
			foreach($L_EWT_TEST_PROBLEM_TYPE as $key => $val) {
				if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && $key === "surala") { continue; }
				if ($_SESSION['sub_session']['add_type']['problem_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$problem_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
			$problem_type_html .= "</select>\n";
		}
		if ($_SESSION['t_practice']['test_type'] == 6 && $_SESSION['t_practice']['test_category2_num']) {
			$html .= "<br>\n";
			$html .= "問題登録をする場合は、条件を選択して下さい。<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<select name=\"add_type\" onchange=\"submit();\" style=\"float:left;\">".$add_type_html."</select>\n";
			$html .= $problem_type_html;
			$html .= "</form>\n";
			if ($_SESSION['sub_session']['add_type']['add_type'] && $_SESSION['sub_session']['add_type']['problem_type']) {
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_add\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "を<input type=\"submit\" value=\"登録\">\n";
				$html .= "</form>\n";
			}
		}
	}

	$img_ftp = FTP_URL."test_img/";
	$voice_ftp = FTP_URL."test_voice/";
	// $test_img_ftp = FTP_TEST_URL."test_img/".$_SESSION['t_practice']['test_type_num']."/"; //del kimura 2018/11/22 すらら英単語 _admin
	// $test_voice_ftp = FTP_TEST_URL."test_voice/".$_SESSION['t_practice']['test_type_num']."/"; //del kimura 2018/11/22 すらら英単語 _admin
	$test_img_ftp = FTP_TEST_URL."vocabulary_img/"; //add kimura 2018/11/22 すらら英単語 _admin
	$test_voice_ftp = FTP_TEST_URL."vocabulary_voice/"; //add kimura 2018/11/22 すらら英単語 _admin
	$html .= "<br><br>\n";
	
	$html .= FTP_EXPLORER_MESSAGE;
	
	if ($_SESSION['t_practice']['test_type_num']) {
		$html .= "<a href=\"".$test_img_ftp."\" target=\"_blank\">テンポラリー画像フォルダー($test_img_ftp)</a><br>\n";
		$html .= "<a href=\"".$test_voice_ftp."\" target=\"_blank\">テンポラリー音声フォルダー($test_voice_ftp)</a><br>\n";
	}
	$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
	$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br>\n";

	if ($_SESSION['t_practice']['test_type_num']) {
		$where .= " AND mtcp.test_type_num='".$_SESSION['t_practice']['test_type_num']."'";
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$where .= " AND mtcp.test_category1_num='".$_SESSION['t_practice']['test_category1_num']."'";
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$where .= " AND mtcp.test_category2_num='".$_SESSION['t_practice']['test_category2_num']."'";
	}

	//del start kimura 2018/11/21 すらら英単語 _admin
	// $sql  = "SELECT count(DISTINCT mtcp.problem_num) AS problem_count" .
	// 	" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
	// 	" WHERE mtcp.mk_flg='0'".
	// 	$where.
	// 	" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_num,mtcp.problem_table_type".
	// 	"";
	//del end   kimura 2018/11/21 すらら英単語 _admin

//echo $sql."<hr><br>";
	//del start kimura 2018/11/21 すらら英単語 _admin
	// if ($result = $cdb->query($sql)) {
	// 		$problem_count = $cdb->num_rows($result);
	// 	while ($list = $cdb->fetch_assoc($result)) {
	// 		$problem_count += $list['problem_count'];
	// 	}
	// }
	//del end   kimura 2018/11/21 すらら英単語 _admin

	//add start kimura 2018/11/21 すらら英単語 _admin
	//------------------
	//一覧用のテンポラリー
	//------------------
	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
				 "SELECT ".
				 "mtcp.test_type_num,".
				 "mtcp.test_category1_num,".
				 "mtcp.test_category2_num,".
				 "mtcp.problem_table_type,".
				 "mtcp.problem_num,".
				 "p.form_type,".
				 "mtcp.list_num".
				 " FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
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
				 "mtp.form_type,".
				 "mtcp.list_num".
				 " FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
				 " LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtcp.problem_num".
				 " WHERE mtcp.mk_flg='0'".
				 " AND mtcp.problem_table_type='3'".	//TODO
				 "".
				 $where.
				 " GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
	$cdb->exec_query($sql);

	//総件数
	if($result = $cdb->query("SELECT * FROM test_problem_list")){
		$problem_count = $cdb->num_rows($result);
	}
	//add end   kimura 2018/11/21 すらら英単語 _admin

	if (!$problem_count) {
		$html .= "<br>\n";
		$html .= "<br style=\"clear:left;\">";
		$html .= "今現在登録されている問題は有りません。<br>\n";
		return $html;
	}
	
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
	
	$html .= select_list();

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['s_page'] && $_SESSION['sub_session']['s_page'] <= $max_page ) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";

//del start kimura 2018/11/21 すらら英単語 _admin 総件数と一覧tableの情報にズレができてしまうのでこのテンポラリーからすべてとるように上に移動
	// $sql = "CREATE TEMPORARY TABLE test_problem_list ".
	// 	"SELECT ".
	// 	"mtcp.test_type_num,".
	// 	"mtcp.test_category1_num,".
	// 	"mtcp.test_category2_num,".
	// 	"mtcp.problem_table_type,".
	// 	"mtcp.problem_num,".
	// 	"p.form_type,".
	// 	"mpa.standard_time,".
	// 	"mtcp.list_num".
	// 	" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
	// 	" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtcp.problem_num".
	// 	" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
	// 	" WHERE mtcp.mk_flg='0'".
	// 	" AND mtcp.problem_table_type='1'".
	// 	"".
	// 	$where.
	// 	" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num".
	// 	" UNION ALL ".
	// 	"SELECT ".
	// 	"mtcp.test_type_num,".
	// 	"mtcp.test_category1_num,".
	// 	"mtcp.test_category2_num,".
	// 	"mtcp.problem_table_type,".
	// 	"mtcp.problem_num,".
	// 	"mtp.form_type,".
	// 	"mtp.standard_time,".
	// 	"mtcp.list_num".
	// 	" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
	// 	" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtcp.problem_num".
	// 	" WHERE mtcp.mk_flg='0'".
	// 	" AND mtcp.problem_table_type='3'".	//TODO
	// 	"".
	// 	$where.
	// 	" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
//echo $sql."<br><br>";
	// $cdb->exec_query($sql);
//del end   kimura 2018/11/21 すらら英単語 _admin

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY test_type_num,test_category1_num,test_category2_num,list_num".
		$limit;
//echo $sql;
	if ($result = $cdb->query($sql)) {
		$html .= $duplicate_message;
		//add start hiros 2018/12/07 すらら英単語
		$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
		// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
		$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
		//add end hiros 2018/12/07 すらら英単語
		$html .= "<br>\n";
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
		$html .= "<th>No</th>\n";
		$html .= "<th>問題管理番号</th>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		// $html .= "<th>回答目安時間</th>\n"; //del kimura 2018/11/21 すらら英単語 _admin
		$html .= "<th>確認</th>\n";
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
		$j=$start;
		while ($list = $cdb->fetch_assoc($result)) {
			$j++;
			if ($list['problem_table_type'] == 1) {
				$table_type = "すらら英単語";
			} elseif ($list['problem_table_type'] == 3) { //TODO
				$table_type = "テスト専用";
			}
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$list['list_num']."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			// $html .= "<td>".$list['standard_time']."</td>\n"; //del kimura 2018/11/21 すらら英単語 _admin
			$html .= "<td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//echo $list['problem_table_type'];
			// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";	//TODO
			// >>> update 引数にtest_type_num,test_category1_num,test_category2_numを追加
			// $html .= "<input type=\"button\" value=\"確認\" onclick=\"vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";	//upd hirose 2018/12/07 すらら英単語
			$html .= "<input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['test_type_num']."','".$list['test_category1_num']."','".$list['test_category2_num']."')\">\n";
			// <<<
			$html .= "</form>\n";
			$html .= "</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				if (!$list['default_test_num']) { $focus_default_test_num = ""; }
				else { $focus_default_test_num = $list['default_test_num']; }
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$list['test_type_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$list['test_category1_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"test_category2_num\" value=\"".$list['test_category2_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
				$html .= "<input type=\"submit\" id=\"problem_botton_".$focus_default_test_num."_".$list['problem_num']."\" value=\"変更\">\n";
				$html .= "</form>\n";
				$html .= "</td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_delete\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$list['test_type_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$list['test_category1_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"test_category2_num\" value=\"".$list['test_category2_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
				$html .= "<input type=\"submit\" value=\"削除\">\n";
				$html .= "</form>\n";
				$html .= "</td>\n";
			}
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	}

	if ($_SESSION['focus_num']) {
		$html .= "<script type=\"text/javascript\">";
		$html .= "problem_botton_focus('problem_botton_".$_SESSION['focus_num']."');";
		$html .= "</script>\n";
	}

	return $html;
}

/*新規のテスト問題 >>>-------------------------------------------------------*/

/**
 * 問題登録　テスト専用問題新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function ewt_test_add_addform($ERROR) {

	global $L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_FORM_TYPE,$L_DRAWING_TYPE;

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= select_form_type();

	if (!$_SESSION['sub_session']['select_course']['form_type'] ) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_problem_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	//add start kimura 2018/11/21 すらら英単語 _admin
	//LMS単元フォーム
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を「 :: 」区切りで入力して下さい。";
	//add end   kimura 2018/11/21 すらら英単語 _admin

//form_type 1 2 3 4 5 8 10 11 13
	$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);

	$INPUTS['PROBLEMNUM'] 		= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['FORMTYPE'] 		= array('result'=>'plane','value'=>$L_FORM_TYPE[$_SESSION['sub_session']['select_course']['form_type']]);
	$INPUTS['QUESTION'] 		= array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$_POST['question']);//add hirose 2018/12/06 すらら英単語
	$INPUTS['PROBLEM'] 			= array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$_POST['problem']);
	$INPUTS['VOICEDATA'] 		= array('type'=>'text','name'=>'voice_data','size'=>'50','value'=>$_POST['voice_data']);
	$INPUTS['CORRECT'] 		= array('type'=>'text','name'=>'correct','size'=>'50','value'=>$_POST['correct']);
	$INPUTS['OPTION1'] 		= array('type'=>'text','name'=>'option1','size'=>'50','value'=>$_POST['option1']);
	$INPUTS['OPTION2'] 		= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$_POST['option2']);
	// $INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']); //del kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITID'] = array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']); //add kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITATT'] = array('result'=>'plane','value'=>$lms_unit_att); //add kimura 2018/11/21 すらら英単語 _admin
	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 問題登録　テスト専用問題新規登録　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_test_add_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['parameter'] && ereg("\[([^0-9])\]",$_POST['parameter'])) { $ERROR[] = "パラメーターが不正です。"; }

	$array_replace = new array_replace();
	if (!$_POST['correct'] && $_POST['correct'] !== "0") {
		$ERROR[] = "正解が確認できません。";
	} else {
		$correct_num = $array_replace->set_line($_POST['correct']);
		$correct = $array_replace->replace_line();
	}
	
	if($_SESSION['sub_session']['select_course']['form_type'] == 17 ){
		if ($_POST['option2'] == "") { $_POST['option2'] ="0"; }

		if ($_POST['option2'] !== "0" && $_POST['option2'] !== "1") {
			$ERROR[] = "シャッフル情報が不正です。";
		}
	}
	
	//del start kimura 2018/11/21 すらら英単語 _admin
	// if ($_POST['standard_time']) {
	// 	if (preg_match("/[^0-9]/",$_POST['standard_time'])) {
	// 		$ERROR[] = "回答目安時間は半角数字で入力してください。";
	// 	}
	// }
	//del end   kimura 2018/11/21 すらら英単語 _admin
	//add start kimura 2018/11/22 すらら英単語 _admin
	//LMS単元のバリデーション
	$lms_err_check_result = validate_lms_id_string($_POST['lms_unit_id']);
	if($lms_err_check_result !== 0){
		$ERROR[] = $lms_err_check_result;
	}
	//add end   kimura 2018/11/22 すらら英単語 _admin
	return $ERROR;
}


/**
 * 問題登録　テスト専用問題新規登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_test_add_check_html() {

	global $L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
	$sql  = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
	$tmp_write_type = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$tmp_write_type = $list['write_type'];
	}

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ewt_add") { $val = "ewt_problem_add"; }
				elseif (MODE == "ewt_view") { $val = "ewt_change"; }
			}
			if ($tmp_write_type == 15) {
				if ( $key == "problem" ) {
					$valx = $val;
				} else {
					$valx = mb_convert_kana($val, "asKV");
				}
			} else {
				$valx = mb_convert_kana($val, "asKV");
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$valx."\">\n";
		}
	}
	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num =$_SESSION['t_practice']['test_type_num'];
	} else {
		$test_type_num = 0;
	}
	
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num =$_SESSION['t_practice']['test_category1_num'];
	} else {
		$test_category1_num = 0;
	}
	
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num =$_SESSION['t_practice']['test_category2_num'];
	} else {
		$test_category2_num = 0;
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) {
			if ($tmp_write_type == 15) {
				if ( $key == "problem" ) {
					$valx = $val;
				} else {
					$valx = mb_convert_kana($val, "asKV");
				}
			} else {
				$valx = mb_convert_kana($val, "asKV");
			}
			$$key = $valx;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"ewt_change\">\n";
		$sql  = "SELECT *" .
			" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
			" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=ms_tp.problem_num".
			" AND mtcp.test_type_num='".$test_type_num."'".
			" AND mtcp.test_category1_num='".$test_category1_num."'".
			" AND mtcp.test_category2_num='".$test_category2_num."'".
			" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
			" WHERE ms_tp.mk_flg='0'".
			" AND ms_tp.problem_num='".$_POST['problem_num']."' LIMIT 1";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$val = str_replace("\n","//",$val);
			$val = str_replace("&nbsp;"," ",$val);
			$$key = replace_decode($val);
		}
		$_SESSION['sub_session']['select_course']['form_type'] = $form_type;

	}

	if (MODE != "ewt_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	//add start kimura 2018/11/28 すらら英単語 _admin
	if(isset($_POST['lms_unit_id'])){
		$lms_unit_id = $_POST['lms_unit_id'];
	}else{
		$lms_unit_id = get_lms_unit_ids($_POST['problem_num'], $_POST['problem_table_type']);
	}
	if(isset($_POST['lms_unit_id']) && $_POST['lms_unit_id'] == ""){ $lms_unit_id = "--"; } //未入力は--と出す
	//add end   kimura 2018/11/28 すらら英単語 _admin

	$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['PROBLEMNUM'] 	= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['FORMTYPE']		= array('result'=>'plane','value'=>$L_FORM_TYPE[$_SESSION['sub_session']['select_course']['form_type']]);
	$INPUTS['QUESTION']		= array('result'=>'plane','value'=>nl2br($question));//add hirose 2018/12/06 すらら英単語
	$INPUTS['PROBLEM']		= array('result'=>'plane','value'=>nl2br($problem));
	$INPUTS['VOICEDATA']		= array('result'=>'plane','value'=>$voice_data);
	$INPUTS['CORRECT']		= array('result'=>'plane','value'=>$correct);
	$INPUTS['OPTION1']		= array('result'=>'plane','value'=>$option1);
	$INPUTS['OPTION2'] = array('result'=>'plane','value'=>$option2);
	$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	$INPUTS['LMSUNITID'] = array('result'=>'plane','value'=>$lms_unit_id); //add kimura 2018/11/21 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>";
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	if (MODE != "ewt_delete") {
		$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	}

	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 問題登録　テスト専用問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_test_add_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT MAX(problem_num) AS max_num FROM " . T_MS_TEST_PROBLEM;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_num']) { $problem_num = $list['max_num'] + 1; } else { $problem_num = 1; }

	// $ERROR = problem_test_add($problem_num); //del kimura 2018/11/26 すらら英単語 _admin
	$ERROR = ewt_problem_test_add($problem_num); //add kimura 2018/11/26 すらら英単語 _admin
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
	}
	
	$L_DEFAULT_TEST_NUM[] = $default_test_num;
	$sql = "SELECT MAX(list_num) AS max_sort FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
		" WHERE test_type_num='".$test_type_num."' AND test_category1_num = '".$test_category1_num."'".
		" AND test_category2_num = '".$test_category2_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	$INSERT_DATA['test_type_num']		= $test_type_num;
	$INSERT_DATA['test_category1_num']	= $test_category1_num;
	$INSERT_DATA['test_category2_num']	= $test_category2_num;
	$INSERT_DATA['problem_num']			= $problem_num;
	$INSERT_DATA['problem_table_type']	= 3;	//TODO
	$INSERT_DATA['list_num']			= $disp_sort;
	$INSERT_DATA['ins_tts_id']			= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date']			= "now()";
	$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date']			= "now()";


	$ERROR = $cdb->insert(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA);
	if ($ERROR) { return $ERROR; }

	//add start kimura 2018/11/21 すらら英単語 _admin
	//LMS単元登録
	$ERROR = save_lms_ids($_POST['lms_unit_id'], $problem_num, 3);
	//add end   kimura 2018/11/21 すらら英単語 _admin

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * 問題登録　テスト専用問題新規登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param int $problem_num
 * @return array エラーの場合
 */
function ewt_problem_test_add($problem_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ins_data = array();
	$ins_data = $_POST;

	$array_replace = new array_replace();
	if ($_SESSION['sub_session']['select_course']['form_type'] == 16) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();
		$array_replace->set_line($ins_data['option2']);
		$ins_data['option2'] = $array_replace->replace_line();

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 17) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();
	}
	
	foreach ($ins_data AS $key => $val) {
		if ($key == "action"
		|| $key == "default_test_num"
		|| $key == "problem_point"
		|| $key == "book_unit_id"
		|| $key == "lms_unit_id"
		|| $key == "upnavi_section_num") { continue; }
		$INSERT_DATA[$key] = $val;
	}

	// list($INSERT_DATA['problem'],$ERROR) 		= img_convert($INSERT_DATA['problem'],$problem_num);
	// list($INSERT_DATA['problem'],$ERROR) 		= voice_convert($INSERT_DATA['problem'],$problem_num);
	list($INSERT_DATA['problem'],$ERROR) = vocabulary_img_convert($INSERT_DATA['problem'],$problem_num);
	list($INSERT_DATA['problem'],$ERROR) = vocabulary_voice_convert($INSERT_DATA['problem'],$problem_num);

	//del start kimura 2018/11/26 すらら英単語 _admin
	// if ($INSERT_DATA['voice_data']) {
	// 	$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
	// 	$dir_num = (floor($problem_num / 100) * 100) + 1;
	// 	$dir_num = sprintf("%07d",$dir_num);
	// 	$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
	// 	if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
	// 		if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
	// 			copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
	// 		}
	// 		$INSERT_DATA['voice_data'] 		= $problem_num."_".$INSERT_DATA['voice_data'];
	// 	} else {
	// 		if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
	// 			copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
	// 		}
	// 	}
	// }
	//del end   kimura 2018/11/26 すらら英単語 _admin
	//add start kimura 2018/11/26 すらら英単語 _admin
	if ($INSERT_DATA['voice_data']) {
		$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'])) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'], $dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
			}
			$INSERT_DATA['voice_data'] = $problem_num."_".$INSERT_DATA['voice_data'];
		} else {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'])) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
			}
		}
	}
	//add end   kimura 2018/11/26 すらら英単語 _admin

	$INSERT_DATA['problem_num'] 	= $problem_num;
	$INSERT_DATA['course_num'] 		= $_SESSION['t_practice']['course_num'];	//TODO
	$INSERT_DATA['form_type'] 		= $_SESSION['sub_session']['select_course']['form_type'];
	$INSERT_DATA['standard_time'] 		= $_POST['standard_time'];
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 		= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_PROBLEM,$INSERT_DATA);

	return $ERROR;
}

/*<<< 新規のテスト問題-------------------------------------------------------*/

/*既存のテスト問題 >>>-------------------------------------------------------*/


/**
 * 問題登録　既存テストから登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function ewt_test_exist_addform($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	$html .= ewt_select_test();
	
	if ($_SESSION['sub_session']['select_course']['test_type_num']){
		$where .= " AND mtcp.test_type_num='".$_SESSION['sub_session']['select_course']['test_type_num']."'";
	}
	
	if ($_SESSION['sub_session']['select_course']['test_ctg1']){
		$where .= " AND mtcp.test_category1_num='".$_SESSION['sub_session']['select_course']['test_ctg1']."'";
	}
	
	if ($_SESSION['sub_session']['select_course']['test_ctg2']){
		$where .= " AND mtcp.test_category2_num='".$_SESSION['sub_session']['select_course']['test_ctg2']."'";
	}

	$sql  = "SELECT count(DISTINCT mtcp.problem_num) AS problem_count" .
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" WHERE mtcp.mk_flg='0'".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_num,mtcp.problem_table_type".
		"";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_count += $list['problem_count'];
		}
	}
	if (!$problem_count) {
		$html .= "<br>\n";
		$html .= "問題は登録されておりません。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}
	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['select_course']['s_page'] && $_SESSION['sub_session']['select_course']['s_page'] <= $max_page ) {
		$page = $_SESSION['sub_session']['select_course']['s_page'];
	} else {
		$page = 1;
	}
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$limit = " LIMIT ".$start.",".$page_view.";";

	$mpa_standard_time = "mpa.standard_time,";
	$mtp_standard_time = "mtp.standard_time,";
	
	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
		"SELECT ".
		"mtcp.test_type_num,".
		"mtcp.test_category1_num,".
		"mtcp.test_category2_num,".
		"mtcp.problem_table_type,".
		"mtcp.problem_num,".
		"p.form_type,".
		"mpa.standard_time,".
		"mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtcp.problem_num".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
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
		"mtp.form_type,".
		"mtp.standard_time,".
		"mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtcp.problem_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_table_type='3'".	//TODO
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
	$cdb->exec_query($sql);

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY test_type_num,test_category1_num,test_category2_num,list_num".
		$limit;
//echo '<br>'.$sql;

	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		
		$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
		// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
		$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800	
		$html .= "<div style=\"float:left;\">登録問題数(".$problem_count."):PAGE[".$page."/".$max_page."]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "<td>no</td>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		// $html .= "<th>回答目安時間</th>\n"; //del kimura 2018/11/21 すらら英単語 _admin
		$html .= "<th>確認</th>\n";
		$html .= "</tr>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("problem");
			$newform->set_form_id("problem_".$list['problem_num']);
			$newform->set_form_check($_POST['problem_num']);
			$newform->set_form_value("".$list['problem_num']."");
			$newform->set_form_action(" onclick=\"set_test_problem_num('".$list['problem_num']."','".$list['problem_table_type']."','".$list['standard_time']."','".$list['problem_point']."')\"");
			$problem_btn = $newform->make();
			if ($list['problem_table_type'] == 1) {
				$table_type = "すらら英単語";
			} elseif ($list['problem_table_type'] == 3) {	//TODO
				$table_type = "テスト専用";
			}

			$html .= "<label for=\"problem_".$list['problem_num']."\">";
			$html .= "<tr class=\"member_form_cell\" >\n";
			$html .= "<td>".$problem_btn."</td>\n";
			$html .= "<td>".$list['list_num']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			// $html .= "<td>".$list['standard_time']."</td>\n"; //del kimura 2018/11/21 すらら英単語 _admin
			
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//upd start hirose 2018/12/19 すらら英単語
//			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";	//TODO
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['test_type_num']."','".$list['test_category1_num']."','".$list['test_category2_num']."')\"></td>\n";
			//upd end hirose 2018/12/19 すらら英単語
			$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";
	// テスト選択、問題一覧、入力フォームまではここで共通使用。
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_exist_check\">\n";

	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$_POST['problem_table_type']."\">\n";

	//add start kimura 2018/11/22 すらら英単語 _admin
	//LMS単元フォーム
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を「 :: 」区切りで入力して下さい。";
	//add end   kimura 2018/11/22 すらら英単語 _admin

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_PROBLEM_ADD_FORM);
	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);

	// $INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'add_standard_time','size'=>'5','value'=>$_POST['add_standard_time']); //del kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITID'] = array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']); //add kimura 2018/11/22 すらら英単語 _admin
	$INPUTS['LMSUNITATT'] = array('result'=>'plane','value'=>$lms_unit_att); //add kimura 2018/11/22 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 問題登録　既存テストから登録　テスト選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_select_test() {

	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_MATH_TEST_CLASS;  

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//テストタイプ
	foreach($L_TEST_TYPE as $key => $val) {
		if( $key != '0' && $key != '6' ){
			unset($L_TEST_TYPE[$key]);
		}
	}
	
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['sub_session']['select_course']['test_type'] == $key) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}

	if (!$_SESSION['sub_session']['select_course']['test_type']) {
		$msg_html .= "テストタイプを選択してください。";
	} else {
		
		if ($_SESSION['sub_session']['select_course']['test_type'] == 6) {

			$L_TYPE_LIST = type_list();
			$L_CATEGORY1_LIST = category1_list($_SESSION['sub_session']['select_course']['test_type_num']);
			$L_CATEGORY2_LIST = category2_list($_SESSION['sub_session']['select_course']['test_type_num'],$_SESSION['sub_session']['select_course']['test_ctg1']);
		

			foreach ($L_TYPE_LIST AS $test_type_num => $test_type_name) {
				if ($_SESSION['sub_session']['select_course']['test_type_num'] == $test_type_num) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$type_html .= "<option value=\"".$test_type_num."\" ".$selected.">".$test_type_name."</option>\n";
			}
			
			foreach($L_CATEGORY1_LIST as $test_category1_num => $test_category1_name) {
				if ($_SESSION['sub_session']['select_course']['test_ctg1'] == $test_category1_num) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$ctg1_html .= "<option value=\"".$test_category1_num."\" ".$selected.">".$test_category1_name."</option>\n";
			}
			
			foreach($L_CATEGORY2_LIST as $test_category2_num => $test_category2_name) {
				if ($_SESSION['sub_session']['select_course']['test_ctg2'] == $test_category2_num) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$ctg2_html .= "<option value=\"".$test_category2_num."\" ".$selected.">".$test_category2_name."</option>\n";
			}
		
			$select_name .= "<td>すらら英単語種類</td>\n";
			$select_name .= "<td>すらら英単語種別１</td>\n";
			$select_name .= "<td>すらら英単語種別２</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"test_type_num\" onchange=\"submit();\">".$type_html."</select>\n";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"test_ctg1\" onchange=\"submit();\">".$ctg1_html."</select>\n";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"test_ctg2\" onchange=\"submit();\">".$ctg2_html."</select>\n";
			$select_menu .= "</td>\n";
		
		}
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"test_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_sub_test_session\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>テストタイプ</td>\n";
	$html .= $select_name;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_type\" onchange=\"submit();\">".$test_type_html."</select>\n";
	$html .= "</td>\n";
	$html .= $select_menu;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	if ($msg_html) {
		$html .= $msg_html;
	}

	return $html;
}


/**
 * テストの為にPOSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_sub_test_session() {

	if ($_SESSION['sub_session']['select_course']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['sub_session']['select_course']);
	}
	if (strlen($_POST['test_type'])) { $_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type']; }
	
	if (strlen($_POST['test_type_num'])) { $_SESSION['sub_session']['select_course']['test_type_num'] = $_POST['test_type_num']; }
	if (strlen($_POST['test_ctg1'])) { $_SESSION['sub_session']['select_course']['test_ctg1'] = $_POST['test_ctg1']; }
	if (strlen($_POST['test_ctg2'])) { $_SESSION['sub_session']['select_course']['test_ctg2'] = $_POST['test_ctg2']; }

	return $ERROR;
}


/**
 * 問題登録　既存テストから登録　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_test_exist_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	//del start kimura 2018/11/21 すらら英単語 _admin
	// if ($_POST['add_standard_time']) {
	// 	if (preg_match("/[^0-9]/",$_POST['add_standard_time'])) {
	// 		$ERROR[] = "回答目安時間は半角数字で入力してください。";
	// 	}
	// }
	//del end   kimura 2018/11/21 すらら英単語 _admin
	//add start kimura 2018/11/22 すらら英単語 _admin
	//LMS単元のバリデーション
	$lms_err_check_result = validate_lms_id_string($_POST['lms_unit_id']);
	if($lms_err_check_result !== 0){
		$ERROR[] = $lms_err_check_result;
	}
	//add end   kimura 2018/11/22 すらら英単語 _admin

	return $ERROR;
}


/**
 * 問題登録　既存テストから登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_test_exist_check_html() {

	global $L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800
	
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ewt_add") { $val = "ewt_exist_add"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	}

	$html .= "<br>\n";
	
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
	
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";
		
	if ($_SESSION['sub_session']['select_course']['test_type_num']){
		$where .= " AND mtcp.test_type_num='".$_SESSION['sub_session']['select_course']['test_type_num']."'";
	}
	
	if ($_SESSION['sub_session']['select_course']['test_ctg1']){
		$where .= " AND mtcp.test_category1_num='".$_SESSION['sub_session']['select_course']['test_ctg1']."'";
	}
	
	if ($_SESSION['sub_session']['select_course']['test_ctg2']){
		$where .= " AND mtcp.test_category2_num='".$_SESSION['sub_session']['select_course']['test_ctg2']."'";
	}
	
	$sql = 	"SELECT ".
		" mtcp.test_type_num,".
		" mtcp.test_category1_num,".
		" mtcp.test_category2_num,".
		" mtcp.problem_table_type,".
		" mtcp.problem_num,".
		" p.form_type,".
		" mpa.standard_time,".
		" mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtcp.problem_num".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_table_type='1'".
		" AND mtcp.problem_num='".$problem_num."'".
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
		"mtp.form_type,".
		"mtp.standard_time,".
		"mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtcp.problem_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_num='".$problem_num."'".
		" AND mtcp.problem_table_type='3'".	//TODO
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['problem_table_type'] == 1) {
		$table_type = "すらら英単語";
	} elseif ($list['problem_table_type'] == 3) {	//TODO
		$table_type = "テスト専用";
	}
	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	$html .= "<td>no</td>\n";
	$html .= "<th>問題種類</th>\n";
	$html .= "<th>出題形式</th>\n";
	// $html .= "<th>回答目安時間</th>\n"; //del kimura 2018/11/21 すらら英単語 _admin
	$html .= "<th>確認</th>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\" >\n";
	$html .= "<td>".$list['list_num']."</td>\n";
	$html .= "<td>".$table_type."</td>\n";
	$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
	// $html .= "<td>".$list['standard_time']."</td>\n"; //del kimura 2018/11/21 すらら英単語 _admin

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	//upd start hirose 2018/12/19 すらら英単語
//	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";	//TODO
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['test_type_num']."','".$list['test_category1_num']."','".$list['test_category2_num']."')\"></td>\n";	//TODO
	//upd end hirose 2018/12/19 すらら英単語
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_PROBLEM_ADD_FORM);

	if(isset($_POST['lms_unit_id']) && $_POST['lms_unit_id'] == ""){ $_POST['lms_unit_id'] = "--"; } //未入力は--と出す //add kimura 2018/11/28 すらら英単語 _admin

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	// $INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$add_standard_time); //del kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITID'] = array('result'=>'plane', 'value'=>$_POST['lms_unit_id']); //add kimura 2018/11/22 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>";
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 問題登録　既存テストから登録　登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_test_exist_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	if ($_POST['problem_table_type'] == 1) {
		$ERROR = problem_attribute_add($_SESSION['sub_session']['select_course']['block_num'],$_POST['add_standard_time']);
	} elseif ($_POST['problem_table_type'] == 3) {	//TODO
		$ERROR = problem_test_time_upd($_POST['problem_num'],$_POST['add_standard_time']);
	}
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
	}
	
	$sql = "SELECT MAX(list_num) AS max_sort FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
		" WHERE test_type_num='".$test_type_num."' ".
		"   AND test_category1_num='".$test_category1_num."' ".
		"   AND test_category2_num='".$test_category2_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	$sql = "SELECT * FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
		" WHERE test_type_num='".$test_type_num."' ".
		"   AND test_category1_num='".$test_category1_num."' ".
		"   AND test_category2_num='".$test_category2_num."' ".
		" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='".$_POST['problem_table_type']."'".
		";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	//登録されていればアップデート、無ければインサート
	if ($list) {
		//$INSERT_DATA['standard_time'] 	= $_POST['standard_time'];
		$INSERT_DATA['mk_flg'] 		= 0;
		$INSERT_DATA['mk_tts_id'] 	= "";
		$INSERT_DATA['mk_date'] 	= "0000-00-00 00:00:00";
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 	= "now()";

		$where = " WHERE test_type_num='".$test_type_num."' ".
				"   AND test_category1_num='".$test_category1_num."' ".
				"   AND test_category2_num='".$test_category2_num."' ".
				"   AND problem_num='".$_POST['problem_num']."' ".
				"   AND problem_table_type='".$_POST['problem_table_type']."' ".
				";";
		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA,$where);
	} else {

		$INSERT_DATA['test_type_num']	= $test_type_num;
		$INSERT_DATA['test_category1_num']	= $test_category1_num;
		$INSERT_DATA['test_category2_num']	= $test_category2_num;
		$INSERT_DATA['problem_num'] 		= $_POST['problem_num'];
		$INSERT_DATA['problem_table_type'] 	= $_POST['problem_table_type'];
		$INSERT_DATA['list_num'] 			= $disp_sort;
		$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 			= "now()";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA);
	}
	if ($ERROR) { return $ERROR; }

	$ERROR = save_lms_ids($_POST['lms_unit_id'], $_POST['problem_num'], $_POST['problem_table_type']); //add kimura 2018/11/28 すらら英単語 _admin
	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/*<<< 既存のテスト問題-------------------------------------------------------*/

/*既存のすらら英単語問題 >>>-------------------------------------------------*/

/**
 * 問題登録　すらら英単語新規登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function ewt_surala_add_addform($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800
	
	$html .= "<br>\n";
	
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
	
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= ewt_select_course();
	
	if (!$_SESSION['sub_session']['select_course']['block_num']) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}

	$sql = "SELECT count(*) AS problem_count FROM ".T_PROBLEM.
		" WHERE state='0'".
		" AND course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
		" AND block_num='".$_SESSION['sub_session']['select_course']['block_num']."'";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if (!$list['problem_count']) {
		$html .= "<br>\n";
		$html .= "問題は登録されておりません。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}
	$problem_count = $list['problem_count'];

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['select_course']['s_page'] && $_SESSION['sub_session']['select_course']['s_page'] <= $max_page ) { $page = $_SESSION['sub_session']['select_course']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";

	$sql  = "SELECT p.problem_num," .
		"p.problem_type," .
		"p.display_problem_num," .
		"p.form_type," .
		"pd.number_of_answers," .
		"p.error_msg," .
		"pd.number_of_incorrect_answers," .
		"pd.correct_answer_rate," .
		"p.display," .
		"mpa.standard_time".
		" FROM ".T_PROBLEM." p".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.block_num=p.block_num".
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num = p.problem_num".
		" WHERE p.state='0'".
		" AND p.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
		" AND p.block_num='".$_SESSION['sub_session']['select_course']['block_num']."'".
		" ORDER BY p.display_problem_num".$limit;

	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">登録問題数(".$problem_count."):PAGE[".$page."/".$max_page."]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "<td>no.</td>\n";
		$html .= "<td>問題タイプ</td>\n";
		$html .= "<td>出題形式</td>\n";
		$html .= "<td>解答数</td>\n";
		$html .= "<td>不正解数</td>\n";
		$html .= "<td>正解率</td>\n";
		$html .= "<td>エラー</td>\n";
		$html .= "<td>表示・非表示</td>\n";
		$html .= "<td>確認</td>\n";
		$html .= "</tr>\n";
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("problem");
			$newform->set_form_id("problem_".$list['problem_num']);
			$newform->set_form_check($_POST['problem_num']);
			$newform->set_form_value("".$list['problem_num']."");
			$newform->set_form_action(" onclick=\"set_surala_problem_num('".$list['problem_num']."','".$list['standard_time']."')\"");
			$problem_btn = $newform->make();

			$form_attr = "";
			if($list['form_type'] == 15){
				$form_attr = "disabled";
				$problem_btn = preg_replace("/ /", " disabled ", $problem_btn, 1); //空白を" disabled "にかえる。最初の1回だけ。//form_partsに属性セット機能がなかったのでこれで。
			}

			$html .= "<label for=\"problem_".$list['problem_num']."\">";
			$html .= "<tr class=\"member_form_cell\" >\n";
			$html .= "<td>".$problem_btn."</td>\n";
			$html .= "<td>".$list['display_problem_num']."</td>\n";
			$html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>".$list['number_of_answers']."</td>\n";
			$html .= "<td>".$list['number_of_incorrect_answers']."</td>\n";
			$html .= "<td>".$list['correct_answer_rate']."%</td>\n";
			if ($list['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
			$html .= "<td>".$error_msg."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//upd start hirose 2018/12/19 すらら英単語
//			$html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
			$html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('1','".$list['problem_num']."','','','')\"></td>\n";
			//upd end hirose 2018/12/19 すらら英単語
			$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_surala_check\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	//add start kimura 2018/11/21 すらら英単語 _admin
	//LMS単元フォーム
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を「 :: 」区切りで入力して下さい。";
	//add end   kimura 2018/11/21 すらら英単語 _admin

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_PROBLEM_ADD_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);

	$INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
	$INPUTS['LMSUNITID'] = array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']); //add kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITATT'] = array('result'=>'plane','value'=>$lms_unit_att); //add kimura 2018/11/21 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_select_course() {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	//サービスからコースを割り出す
	$sql  = "SELECT sv.service_num,scl.course_num,co.course_name FROM ". T_SERVICE ." sv ".
			" LEFT JOIN ".T_SERVICE_COURSE_LIST ." scl ON scl.service_num = sv.service_num ".
			" LEFT JOIN ".T_COURSE ." co ON co.course_num = scl.course_num ".
			" WHERE sv.mk_flg='0' ".
			"   AND scl.mk_flg='0' ".
			"   AND sv.setup_type='4' ".	// upd hasegawa 2018/11/09 すらら英単語 テストのsetup_type→5に変更 // update 2018/11/19 yoshizawa 既存のすらら問題の登録なのでsetup_type→4に変更
			"   AND scl.course_type='1' ".
			"   ORDER BY sv.list_num , scl.service_course_list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	//echo $sql;
	if (!$max) {
		$html .= "コースを設定してからご利用下さい。<br>\n";
		return $html;
	}
	
	$course_num_html .= "<option value=\"0\">選択してください</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['course_num'] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$course_num_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
	}

	//ステージ
	$sql  = "SELECT stage_num,stage_name FROM " . T_STAGE .
			" WHERE state='0' AND course_num='".$_SESSION['sub_session']['select_course']['course_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$stage_max = $cdb->num_rows($result);
	}
	$stage_num_html .= "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
		$stage_num_html .= "<option value=\"".$list['stage_num']."\" ".$selected.">".$list['stage_name']."</option>\n";
	}

	//レッスン
	$sql = "SELECT lesson_num,lesson_name FROM " . T_LESSON .
		" WHERE state='0' AND stage_num='".$_SESSION['sub_session']['select_course']['stage_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$lesson_max = $cdb->num_rows($result);
	}
	$lesson_num_html .= "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
		$lesson_num_html .= "<option value=\"".$list['lesson_num']."\" ".$selected.">".$list['lesson_name']."</option>\n";
	}

	//ユニット
	$sql = "SELECT unit_num,unit_name FROM " . T_UNIT .
		" WHERE state='0' AND lesson_num='".$_SESSION['sub_session']['select_course']['lesson_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$unit_max = $cdb->num_rows($result);
	}
	$unit_html .= "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['unit_num'] == $list['unit_num']) { $selected = "selected"; } else { $selected = ""; }
		$unit_html .= "<option value=\"".$list['unit_num']."\" ".$selected.">".$list['unit_name']."</option>\n";
	}

	//ブロック
	$sql = "SELECT block_num, block_type, display, lowest_study_number FROM " . T_BLOCK .
		" WHERE state='0' AND unit_num='".$_SESSION['sub_session']['select_course']['unit_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$block_max = $cdb->num_rows($result);
	}
	$block_html = "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['block_num'] == $list['block_num']) { $selected = "selected"; } else { $selected = ""; }
		if ($list['block_type'] == 1) {
			$block_name = "ドリル";
		} elseif ($list['block_type'] == 2) {
			$block_name = "診断A";
		} elseif ($list['block_type'] == 3) {
			$block_name = "診断B";
		} elseif ($list['block_type'] == 4) {
			$block_name = "ドリルA";
		} elseif ($list['block_type'] == 5) {
			$block_name = "ドリルB";
		} elseif ($list['block_type'] == 6) {
			$block_name = "ドリルC";
		} elseif ($list['block_type'] == 7) {
			$block_name = "ドリルD";
		} elseif ($list['block_type'] == 8) {
			$block_name = "ドリルE";
		} else {
			$block_name = "";
		}
		if ($list['display'] == "2") { $block_name .= "(非表示)"; }
		$block_html .= "<option value=\"".$list['block_num']."\" ".$selected.">".$block_name."</option>\n";
	}
	if (!$_SESSION['sub_session']['select_course']['course_num']) {
		$msg .= "コース、ステージ、Lesson、ユニットを選択してください。<br>\n";
	} elseif (!$_SESSION['sub_session']['select_course']['stage_num']) {
		$msg = "ステージを選択してください。<br>\n";
		if (!$stage_max) { $msg = "ステージが設定されておりません。<br>\n"; }
	} elseif (!$_SESSION['sub_session']['select_course']['lesson_num']) {
		$msg = "Lessonを選択してください。<br>\n";
		if (!$lesson_max) { $msg = "Lessonが設定されておりません。<br>\n"; }
	} elseif (!$_SESSION['sub_session']['select_course']['unit_num']) {
		$msg = "ユニットを選択してください。<br>\n";
		if (!$unit_max) { $msg = "ユニットが設定されておりません。<br>\n"; }
	} elseif (!$_SESSION['sub_session']['select_course']['block_num']) {
		$msg = "ドリルを選択してください。<br>\n";
		if (!$block_max) { $msg = "ドリルが設定されておりません。<br>\n"; }
	} else {
		//ページ数
		if (!isset($_SESSION['sub_session']['select_course']['s_page_view'])) { $_SESSION['sub_session']['select_course']['s_page_view'] = 1; }
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($_SESSION['sub_session']['select_course']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}
		$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "表示数 <select name=\"s_page_view\">\n".$s_page_view_html."</select>\n";
		$sub_session_html .= "<input type=\"submit\" value=\"Set\">\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";

		$msg = "<br><div id=\"mode_menu\">\n";
		$msg .= "<table cellpadding=0 cellspacing=0>\n";
		$msg .= "<tr>\n";
		$msg .= $sub_session_html;
		$msg .= "</tr>\n";
		$msg .= "</table>\n";
		$msg .= "</div>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\">コース</td>\n";
	$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
	$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
	$html .= "<td class=\"stage_form_menu\">ユニット</td>\n";
	$html .= "<td class=\"stage_form_menu\">ドリル</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"stage_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit_stage()\">\n";
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
	$html .= "</form>\n";
	$html .= "<br style=\"clear:left;\">\n";
	$html .= $msg;
	return $html;
}


/**
 * 問題登録　すらら英単語新規登録　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_surala_add_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	//del start kimura 2018/11/21 すらら英単語 _admin
	// if ($_POST['standard_time']) {
	// 	if (preg_match("/[^0-9]/",$_POST['standard_time'])) {
	// 		$ERROR[] = "回答目安時間は半角数字で入力してください。";
	// 	}
	// }
	//del end   kimura 2018/11/21 すらら英単語 _admin
	//add start kimura 2018/11/22 すらら英単語 _admin
	//LMS単元のバリデーション
	$lms_err_check_result = validate_lms_id_string($_POST['lms_unit_id']);
	if($lms_err_check_result !== 0){ //正常終了0でなければエラー表示
		$ERROR[] = $lms_err_check_result;
	}
	//add end   kimura 2018/11/22 すらら英単語 _admin
	return $ERROR;
}


/**
 * 問題登録　すらら英単語問題新規登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_surala_add_check_html() {

	global $L_TEST_ADD_TYPE,$L_EWT_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// del karasawa 2019/11/19 課題要望800

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ewt_add") { $val = "ewt_surala_add"; }
				elseif (MODE == "ewt_view") { $val = "ewt_change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"ewt_change\">\n";

		$sql  = "SELECT *" .
			" FROM ". T_PROBLEM ." problem" .
			" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
			" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=problem.problem_num".
			" AND mtcp.test_type_num='".$test_type_num."' ".
			" AND mtcp.test_category1_num='".$test_category1_num."' ".
			" AND mtcp.test_category2_num='".$test_category2_num."' ".
			" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
			" WHERE problem.state='0'".
			" AND problem.problem_num='".$_POST['problem_num']."' LIMIT 1";
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

	if (MODE != "ewt_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
	
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_EWT_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$sql  = "SELECT p.problem_num," .
		"p.problem_type," .
		"p.display_problem_num," .
		"p.form_type," .
		"pd.number_of_answers," .
		"p.error_msg," .
		"pd.number_of_incorrect_answers," .
		"pd.correct_answer_rate," .
		"p.display" .
		" FROM ".T_PROBLEM." p".
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num = p.problem_num".
		" WHERE p.state='0'".
		" AND p.problem_num='".$problem_num."'".
		" ORDER BY p.display_problem_num;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	$html .= "<td>no.</td>\n";
	$html .= "<td>問題タイプ</td>\n";
	$html .= "<td>出題形式</td>\n";
	$html .= "<td>解答数</td>\n";
	$html .= "<td>不正解数</td>\n";
	$html .= "<td>正解率</td>\n";
	$html .= "<td>エラー</td>\n";
	$html .= "<td>表示・非表示</td>\n";
	$html .= "<td>確認</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\" >\n";
	$html .= "<td>".$list['display_problem_num']."</td>\n";
	$html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
	$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
	$html .= "<td>".$list['number_of_answers']."</td>\n";
	$html .= "<td>".$list['number_of_incorrect_answers']."</td>\n";
	$html .= "<td>".$list['correct_answer_rate']."%</td>\n";
	if ($list['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
	$html .= "<td>".$error_msg."</td>\n";
	$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	//upd start hirose 2018/12/19 すらら英単語
//	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('1','".$list['problem_num']."','','','')\"></td>\n";
	//upd end hirose 2018/12/19 すらら英単語
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";

	//add start kimura 2018/11/28 すらら英単語 _admin
	if(isset($_POST['lms_unit_id'])){
		$lms_unit_id = $_POST['lms_unit_id'];
	}else{
		$lms_unit_id = get_lms_unit_ids($_POST['problem_num'], $_POST['problem_table_type']);
	}
	if(isset($_POST['lms_unit_id']) && $_POST['lms_unit_id'] == ""){ $lms_unit_id = "--"; } //未入力は--と出す
	//add end   kimura 2018/11/28 すらら英単語 _admin

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_PROBLEM_ADD_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$standard_time);
	$INPUTS['LMSUNITID'] = array('result'=>'plane','value'=>$lms_unit_id); //add kimura 2018/11/21 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>";
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	if (MODE != "delete") {
		$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	}
	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 問題登録　すらら英単語問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_surala_add_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ERROR = problem_attribute_add($_SESSION['sub_session']['select_course']['block_num'],$_POST['standard_time']);
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
	}
	
	$sql = "SELECT MAX(list_num) AS max_sort FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
			" WHERE test_type_num='".$test_type_num."' ".
			"   AND test_category1_num='".$test_category1_num."' ".
			"   AND test_category2_num='".$test_category2_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	$sql = "SELECT * FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
			" WHERE test_type_num='".$test_type_num."' ".
			"   AND test_category1_num='".$test_category1_num."' ".
			"   AND test_category2_num='".$test_category2_num."' ".
			" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	//登録されていればアップデート、無ければインサート
	if ($list) {
		$INSERT_DATA['mk_flg'] 			= 0;
		$INSERT_DATA['mk_tts_id'] 		= "";
		$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		$where = " WHERE test_type_num='".$test_type_num."' ".
				"   AND test_category1_num='".$test_category1_num."' ".
				"   AND test_category2_num='".$test_category2_num."' ".
				"   AND problem_num='".$_POST['problem_num']."' ".
				"   AND problem_table_type='1' ".
				";";

		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA,$where);

	} else {

		$INSERT_DATA['test_type_num'] 	= $test_type_num;
		$INSERT_DATA['test_category1_num'] 	= $test_category1_num;
		$INSERT_DATA['test_category2_num'] 	= $test_category2_num;
		$INSERT_DATA['problem_num'] 		= $_POST['problem_num'];
		$INSERT_DATA['problem_table_type'] 	= 1;
		$INSERT_DATA['list_num'] 			= $disp_sort;
		$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 			= "now()";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA);
	}

	$ERROR = save_lms_ids($_POST['lms_unit_id'], $_POST['problem_num'], 1); //add kimura 2018/11/28 すらら英単語 _admin
	if ($ERROR) { return $ERROR; }

	//教科書単元と問題の紐付け登録

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/*<<< 既存のすらら英単語問題-------------------------------------------------*/

/**
 * 修正フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function ewt_viewform($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start karasawa 2019/11/19 課題要望800 
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	$_SESSION['focus_num'] = "_".$_POST['problem_num'];

	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num =$_SESSION['t_practice']['test_type_num'];
	}
	
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num =$_SESSION['t_practice']['test_category1_num'];
	}
	
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num =$_SESSION['t_practice']['test_category2_num'];
	}
	
	if (ACTION) {
		if ($_POST['problem_table_type'] == 1) {
			$sql  = "SELECT form_type" .
				" FROM ". T_PROBLEM ." problem" .
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
				" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=problem.problem_num".
				" AND mtcp.test_type_num='".$test_type_num."'".
				" AND mtcp.test_category1_num='".$test_category1_num."'".
				" AND mtcp.test_category2_num='".$test_category2_num."'".
				" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE problem.state='0'".
				" AND problem.problem_num='".$_POST['problem_num']."' LIMIT 1";
		} elseif ($_POST['problem_table_type'] == 3) {	//TODO
			$sql  = "SELECT form_type" .
				" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
				" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=ms_tp.problem_num".
				" AND mtcp.test_type_num='".$test_type_num."'".
				" AND mtcp.test_category1_num='".$test_category1_num."'".
				" AND mtcp.test_category2_num='".$test_category2_num."'".
				" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE ms_tp.mk_flg='0'".
				" AND ms_tp.problem_num='".$_POST['problem_num']."' LIMIT 1";
		}

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		$form_type = $list['form_type'];
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		if ($_POST['problem_table_type'] == 1) {
			$sql  = "SELECT *" .
				" FROM ". T_PROBLEM ." problem" .
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
				" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=problem.problem_num".
				" AND mtcp.test_type_num='".$test_type_num."'".
				" AND mtcp.test_category1_num='".$test_category1_num."'".
				" AND mtcp.test_category2_num='".$test_category2_num."'".
				" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE problem.state='0'".
				" AND problem.problem_num='".$_POST['problem_num']."' LIMIT 1";
		} elseif ($_POST['problem_table_type'] == 3) {	//TODO
			$sql  = "SELECT *" .
				" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
				" LEFT JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=ms_tp.problem_num".
				" AND mtcp.test_type_num='".$test_type_num."'".
				" AND mtcp.test_category1_num='".$test_category1_num."'".
				" AND mtcp.test_category2_num='".$test_category2_num."'".
				" AND mtcp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE ms_tp.mk_flg='0'".
				" AND ms_tp.problem_num='".$_POST['problem_num']."' LIMIT 1";
		}

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
			$html .= "<input type=\"submit\" value=\"戻る\">\n";
			$html .= "</form>\n";
			return $html;
		}
		foreach ($list as $key => $val) {
			$val = ereg_replace("\n","//",$val);
			$val = ereg_replace("&nbsp;"," ",$val);
				$$key = $val;
		}
		$_SESSION['sub_session']['select_course']['form_type'] = $form_type;
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";
	
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if ($_POST['problem_table_type'] == 1) {
		$table_file = TANGO_TEST_PROBLEM_ADD_FORM;

		$sql  = "SELECT ".
			" p.problem_num," .
			" p.problem_type," .
			" p.display_problem_num," .
			" p.form_type," .
			" pd.number_of_answers," .
			" p.error_msg," .
			" pd.number_of_incorrect_answers," .
			" pd.correct_answer_rate," .
			" p.display" .
			" FROM ".T_PROBLEM." p".
			" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num = p.problem_num".
			" WHERE p.state='0'".
			" AND p.problem_num='".$problem_num."'".
			" ORDER BY p.display_problem_num;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		$html .= "<br>";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>no.</td>\n";
		$html .= "<td>問題タイプ</td>\n";
		$html .= "<td>出題形式</td>\n";
		$html .= "<td>解答数</td>\n";
		$html .= "<td>不正解数</td>\n";
		$html .= "<td>正解率</td>\n";
		$html .= "<td>エラー</td>\n";
		$html .= "<td>表示・非表示</td>\n";
		$html .= "<td>確認</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"member_form_cell\" >\n";
		$html .= "<td>".$list['display_problem_num']."</td>\n";
		$html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
		$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
		$html .= "<td>".$list['number_of_answers']."</td>\n";
		$html .= "<td>".$list['number_of_incorrect_answers']."</td>\n";
		$html .= "<td>".$list['correct_answer_rate']."%</td>\n";
		if ($list['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
		$html .= "<td>".$error_msg."</td>\n";
		$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
		//upd start hirose 2018/12/19 すらら英単語
//		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$list['problem_num']."')\"></td>\n";	//TODO
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$_POST['problem_table_type']."','".$list['problem_num']."','".$test_type_num."','".$test_category1_num."','".$test_category2_num."')\"></td>\n";
		//upd end hirose 2018/12/19 すらら英単語
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<br>";
	} elseif ($_POST['problem_table_type'] == 3) {	//TODO
		$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ewt_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$_POST['default_test_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$_POST['problem_table_type']."\">\n";

	//add start kimura 2018/11/21 すらら英単語 _admin
	//LMS単元フォーム
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を「 :: 」区切りで入力して下さい。";
	if(isset($_POST['lms_unit_id'])){
		$lms_unit_id = $_POST['lms_unit_id'];
	}else{
		$lms_unit_id = get_lms_unit_ids($_POST['problem_num'], $_POST['problem_table_type']);
	}
	//add end   kimura 2018/11/21 すらら英単語 _admin

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);
	
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);

	$INPUTS['PROBLEMNUM']			= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['FORMTYPE']			= array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);
	$INPUTS['QUESTION']			= array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$question);//add hirose 2018/12/06 すらら英単語
	$INPUTS['PROBLEM']			= array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$problem);
	$INPUTS['VOICEDATA']			= array('type'=>'text','name'=>'voice_data','size'=>'50','value'=>$voice_data);
	$INPUTS['CORRECT']			= array('type'=>'text','name'=>'correct','size'=>'50','value'=>$correct);
	$INPUTS['OPTION1']			= array('type'=>'text','name'=>'option1','size'=>'50','value'=>$option1);
	$INPUTS['OPTION2']		= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$option2);
	$INPUTS['STANDARDTIME']			= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$standard_time);
	$INPUTS['LMSUNITID'] = array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$lms_unit_id); //add kimura 2018/11/21 すらら英単語 _admin
	$INPUTS['LMSUNITATT'] = array('result'=>'plane','value'=>$lms_unit_att); //add kimura 2018/11/21 すらら英単語 _admin

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ewt_view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	//upd start hirose 2018/12/19 すらら英単語
//	$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$_POST['problem_num']."')\">\n";	//TODO
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');vocabulary_problem_win_open('".$_POST['problem_table_type']."','".$_POST['problem_num']."','".$test_type_num."','".$test_category1_num."','".$test_category2_num."')\">\n";
	//upd end hirose 2018/12/19 すらら英単語
	$html .= "</form>\n";

	return $html;
}


/**
 * 修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_check() {
	if ($_POST['problem_table_type'] == 1) {
		$ERROR = ewt_surala_add_check();
	} elseif ($_POST['problem_table_type'] == 3) {	//TODO
		$ERROR = ewt_test_add_check();
	}
	return $ERROR;
}


/**
 * 修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function ewt_check_html() {
	if ($_POST['problem_table_type'] == 1) {
		$html = ewt_surala_add_check_html();
	} elseif ($_POST['problem_table_type'] == 3) {	//TODO
		$html = ewt_test_add_check_html();
	}

	return $html;
}


/**
 * 問題　修正・削除処理
 * 注）データ削除処理の時、ms_test_problemは別単元で使用している可能性が有る為、削除処理は行わない
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function ewt_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "ewt_view") {
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		if ($_POST['problem_table_type'] == 1) {
			$ERROR = problem_attribute_add($_POST['block_num'],$_POST['standard_time']);
		} elseif ($_POST['problem_table_type'] == 3) {	//TODO
			$ERROR = problem_test_upd($_POST['problem_num']);
		}
		if ($ERROR) { return $ERROR; }
	} elseif (MODE == "ewt_delete") {
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";
	}
	
	if ($_SESSION['t_practice']['test_type_num']) {
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
	}
	
	if ($_SESSION['t_practice']['test_category1_num']) {
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
	}
	
	if ($_SESSION['t_practice']['test_category2_num']) {
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
	}
	
	$where = " WHERE test_type_num='".$test_type_num."'".
				" AND test_category1_num='".$test_category1_num."'".
				" AND test_category2_num='".$test_category2_num."'".
				" AND problem_num='".$_POST['problem_num']."' ";
				" AND problem_table_type='".$_POST['problem_table_type']."'";


	$where .= " LIMIT 1;";

	$ERROR = $cdb->update(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA,$where);
	$ERROR = save_lms_ids($_POST['lms_unit_id'], $_POST['problem_num'], $_POST['problem_table_type']); //add kimura 2018/11/21 すらら英単語 _admin

	if ($ERROR) { return $ERROR; }

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * 問題登録　テスト専用問題更新処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @return array エラーの場合
 */
function ewt_problem_test_upd($problem_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ins_data = array();
	$ins_data = $_POST;
	$array_replace = new array_replace();

	if ($_SESSION['sub_session']['select_course']['form_type'] == 16) {

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();
		$array_replace->set_line($ins_data['option2']);
		$ins_data['option2'] = $array_replace->replace_line();

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 17) {

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

	}
	foreach ($ins_data AS $key => $val) {
		if (
			$key == "action"
		) { continue; }
		$INSERT_DATA[$key] = $val;
	}
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 		= "now()";

	$where = " WHERE problem_num='".$problem_num."';";

	$ERROR = $cdb->update(T_MS_TEST_PROBLEM,$INSERT_DATA,$where);

	return $ERROR;
}

/*エクスポート >>>------------------------------------------------------------*/

/**
 * 問題　csvエクスポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 */
function ewt_csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = ewt_make_csv($L_CSV_COLUMN['test_word_test_category2_problem'],1,1);
	if ($ERROR) { return $ERROR; }

	//テストタイプでファイル名を変える
	$filename = "test_word_test_problem_".$_SESSION['t_practice']['test_type_num']."_".$_SESSION['t_practice']['test_category1_num']."_".$_SESSION['t_practice']['test_category2_num'].".csv";

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
 * 問題　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode
 * @param mixed $csv_mode
 * @return array
 */
function ewt_make_csv($L_CSV_COLUMN,$head_mode='1',$csv_mode='1') {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if($key == "test_type_num" || $key == "test_category1_num" || $key == "test_category2_num"){ continue; } //add kimura 2018/11/21 すらら英単語 _admin
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= "\"".$head_name."\",";
	}
	$csv_line .= "\n";

	if ($_SESSION['t_practice']['test_type_num']) {
		$where .= " AND mtcp.test_type_num='".$_SESSION['t_practice']['test_type_num']."'";
	}
	if ($_SESSION['t_practice']['test_category1_num']) {
		$where .= " AND mtcp.test_category1_num='".$_SESSION['t_practice']['test_category1_num']."'";
	}
	if ($_SESSION['t_practice']['test_category2_num']) {
		$where .= " AND mtcp.test_category2_num='".$_SESSION['t_practice']['test_category2_num']."'";
	}
	
	
	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
		"SELECT ".
		"mtcp.test_type_num,".
		"mtcp.test_category1_num,".
		"mtcp.test_category2_num,".
		"mtcp.problem_table_type,".
		"mtcp.problem_num,".
		"p.form_type,".
		"mpa.standard_time,".
		"mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtcp.problem_num".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
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
		"mtp.form_type,".
		"mtp.standard_time,".
		"mtcp.list_num".
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtcp.problem_num".
		" WHERE mtcp.mk_flg='0'".
		" AND mtcp.problem_table_type='3'".	//TODO
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
//echo $sql."<br><br>";
	$cdb->exec_query($sql);

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY test_type_num,test_category1_num,test_category2_num,list_num;";
//echo $sql."<br><br>";
	$L_CSV = array();
	$L_CSV_LINE = array();
	if ($result = $cdb->query($sql)) {
		//add start kimura 2018/11/22 すらら英単語 _admin
		//紐づいているLMS単元をstringの一行にしてphp配列にもっておく
		$problem_list = array();
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_list[] = $list['problem_num'];
		}
		$result = $cdb->data_seek($result, 0);
		$sql = "SELECT problem_num, problem_table_type, unit_num";
		$sql.= " FROM ".T_BOOK_UNIT_LMS_UNIT;
		$sql.= " WHERE mk_flg = '0'";
		$sql.= " AND problem_num IN (".implode(",", $problem_list).")";
		$sql.= " AND problem_table_type IN (1, 3)";

		$lms_list = array();
		if($res = $cdb->query($sql)){
			while($row = $cdb->fetch_assoc($res)){
				//string型を連結していく
				//すでにインデックスがあれば連結
				if(isset($lms_list[$row['problem_table_type']][$row['problem_num']])){
					$lms_list[$row['problem_table_type']][$row['problem_num']].= "::".$row['unit_num'];
				//初出現の場合は代入
				}else{
					$lms_list[$row['problem_table_type']][$row['problem_num']] = $row['unit_num'];
				}
			}
		}
		//どの問題にどのLMS単元が紐づいているかの情報
		//$lms_list[テーブルタイプ][問題管理番号] = LMS単元::LMS単元::LMS単元...

		//add end   kimura 2018/11/22 すらら英単語 _admin
		$j=0;
		while ($list = $cdb->fetch_assoc($result)) {
			if ($list['problem_table_type'] != $old_problem_table_type || $list['problem_num'] != $old_problem_num) {
				if ($list['problem_table_type'] != $old_problem_table_type) {
					$old_problem_table_type = $list['problem_table_type'];
				}
				if ($list['problem_num'] != $old_problem_num) {
					$old_problem_num = $list['problem_num'];
				}
				$j++;
			}
			$i = 0;

			foreach ($L_CSV_COLUMN as $key => $val) {
				if ($key == "list_num") {
					if ($L_CSV[$j]['list_num']) {
						$L_CSV[$j]['list_num'] .= "//";
					}
					$L_CSV[$j]['list_num'] .= $list['list_num'];
				} elseif ($key == "problem_type") {
					
					if ($list['problem_table_type'] == 1) {
						$table_type = "surala";

					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$table_type = "test";

						$sql2  = "SELECT ".
							"p.form_type,".
							"p.question,".
							"p.problem,".
							"p.voice_data,".
							"p.hint,".
							"p.explanation,".
							"p.parameter,".
							"p.first_problem,".
							"p.latter_problem,".
							"p.selection_words,".
							"p.correct,".
							"p.option1,".
							"p.option2,".
							"p.option3,".
							"p.option4,".
							"p.option5".
							" FROM " . T_MS_TEST_PROBLEM ." p".
							" WHERE p.problem_num='".$list['problem_num']."'".
							" AND p.mk_flg='0'";
						if ($result2 = $cdb->query($sql2)) {
							$problem_data = $cdb->fetch_assoc($result2);
							foreach ($problem_data as $key => $val) {
								$problem_data[$key] = ereg_replace("&quot;","\"\"",$problem_data[$key]);
								$problem_data[$key] = ereg_replace("\n","//",$problem_data[$key]);
								$problem_data[$key] = ereg_replace("&lt;","<",$problem_data[$key]);
								$problem_data[$key] = ereg_replace("&gt;",">",$problem_data[$key]);
								$problem_data[$key] = ereg_replace("&#65374;","～",$problem_data[$key]);
							}
						}
						$block_num = "";
					}
					
					$L_CSV[$j]['problem_type'] = $table_type;
				} elseif ($key == "test_type") {
					if ($list['problem_table_type'] == 1) {
						$test_type = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$test_type = "";
					}
					$test_type = $_SESSION['t_practice']['test_type'];
					$L_CSV[$j]['test_type'] = $test_type;
				} elseif ($key == "form_type") {
					if ($list['problem_table_type'] == 1) {
						$form_type = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$form_type = $problem_data['form_type'];
					}
					$L_CSV[$j]['form_type'] = $form_type;
				} elseif ($key == "problem") {
					if ($list['problem_table_type'] == 1) {
						$problem = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$problem = $problem_data['problem'];
					}
					$L_CSV[$j]['problem'] = $problem;
				} elseif ($key == "voice_data") {
					if ($list['problem_table_type'] == 1) {
						$voice_data = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$voice_data = $problem_data['voice_data'];
					}
					$L_CSV[$j]['voice_data'] = $voice_data;
				} elseif ($key == "correct") {
					if ($list['problem_table_type'] == 1) {
						$correct = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$correct = $problem_data['correct'];
					}
					$L_CSV[$j]['correct'] = $correct;
				} elseif ($key == "option1") {
					if ($list['problem_table_type'] == 1) {
						$option1 = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$option1 = $problem_data['option1'];
					}
					$L_CSV[$j]['option1'] = $option1;
				} elseif ($key == "option2") {
					if ($list['problem_table_type'] == 1) {
						$option2 = "";
					} elseif ($list['problem_table_type'] == 3) {	//TODO
						$option2 = $problem_data['option2'];
					}
					$L_CSV[$j]['option2'] = $option2;
				} elseif ($key == "standard_time") {
					$L_CSV[$j]['standard_time'] = $list['standard_time'];
				//add start kimura 2018/11/22 すらら英単語 _admin
				} elseif ($key == "lms_unit_id") {
					$L_CSV[$j]['lms_unit_id'] = $lms_list[$list['problem_table_type']][$list['problem_num']];
				//add end   kimura 2018/11/22 すらら英単語 _admin
				//add start hirose 2018/12/07 すらら英単語
				} elseif ($key == "question") {
					if ($list['problem_table_type'] == 3) {
						$question = $problem_data['question'];
					} else{
						$question = "";
					}
					$L_CSV[$j]['question'] = $question;
				//add end hirose 2018/12/07 すらら英単語
				} else {
					$L_CSV[$j][$key] = $list[$key];
				}
				$i++;
			}
		}
		$cdb->free_result($result);
		foreach ($L_CSV as $line_num => $line_val) {
			foreach ($line_val as $material_key => $material_val) {
				if($material_key == "test_type_num" || $material_key == "test_category1_num" || $material_key == "test_category2_num"){ continue; } //add kimura 2018/11/21 すらら英単語 _admin
				if ($L_CSV_LINE[$line_num]) {
					 $L_CSV_LINE[$line_num] .= ",";
				}
				if (ereg(",",$material_val)||ereg("\"",$material_val)) {
					$L_CSV_LINE[$line_num] .= "\"".$material_val."\"";
				} else {
					$L_CSV_LINE[$line_num] .= $material_val;
				}
			}
		}
		$csv_line .= implode("\n",$L_CSV_LINE);
	}

	$csv_line .= "\n";

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
//pre($_SESSION);
//pre($L_CSV_COLUMN);
//pre($csv_line);
//exit;

	return array($csv_line,$ERROR);
}

/*<<< エクスポート------------------------------------------------------------*/


/*インポート >>>-------------------------------------------------------------*/

/**
 * 問題　csvインポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array
 */
function ewt_csv_import() {
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR =array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	// ファイルチェック
	if (!$file_tmp_name) {
		$ERROR[] = "問題ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "問題ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "問題ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return array($html,$ERROR);
	}

	// テスト問題読込
	$L_IMPORT_LINE = array();

	$handle = fopen($file_tmp_name,"r");

	// php5の不具合でfgetcsvで日本語が文字化けしてしまう。
	// setlocaleでロケールを設定して対応する。
	$line1 = "";
	$judgeCharacterCode = "";
	$judge_handle = fopen($file_tmp_name,"r");
	while(!feof($judge_handle)){
    	$line1 = fgets($judge_handle,1000);
		// １行目は無視する
		if ($j > 0) {
			// 1バイト文字のみの場合には”ASCII”と判定されます。
			$judgeCharacterCode = mb_detect_encoding($line1);
			if($judgeCharacterCode == 'SJIS'){
				setlocale(LC_ALL, 'ja_JP.SJIS');
				break;
			} else if($judgeCharacterCode == 'UTF-8') {
				setlocale(LC_ALL, 'ja_JP.UTF-8');
				break;
			}
		}
		$j++;
	}

	$i = 0;
	while(!feof($handle)){
    	$str = fgetcsv($handle,10000);
		if ($i == 0) {
			$L_LIST_NAME = $str;
		} else {
			$L_IMPORT_LINE[$i] = $str;
		}
		$i++;
	}
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	$ERROR = array();

	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {

		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);
		unset($L_DEFAULT_TEST_NUM);
		unset($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE);
		unset($L_MS_TEST_PROBLEM_INS_MODE);

		$empty_check = preg_replace("/,/","",$L_IMPORT_LINE[$i]);
		if (count($L_IMPORT_LINE[$i]) == 0) {
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode(",",$import_line);
		if (!is_array($L_VALUE)) {
			$ERROR[] = $i."行目のcsv入力値が不正なのでスキップしました。<br>";
			continue;
		}
		foreach ($L_IMPORT_LINE[$i] as $key => $val) {

			if ($L_LIST_NAME[$key] === "") { continue; }
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);

			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );

			if ( $code != 'UTF-8' ) {
				// 「集合A」や「集合B」など、1バイト文字と2バイト文字が複合している場合に
				// 特殊文字が誤った変換をする。対策として半角英字を全角に変換する。
				// ※オプション「A」で英数字を変換すると「<」と「>」まで全角にしてしまうため
				// 「R」で英字のみ全角にする。

				$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
				$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
				$tmpx_write_type = 0;
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
					$tmpx_write_type = $list['write_type'];
				}
				$val = mb_convert_kana($val,"R","sjis-win");

				//--------------------------------------------------------------------------
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			}else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//--------------------------------------------------
			//カナ変換

			$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
			$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
			$tmpx_write_type = 0;
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$tmpx_write_type = $list['write_type'];
			}
			$val = mb_convert_kana($val,"asKVn","UTF-8");


			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;

		}
		
		//del kimura 2018/11/21 すらら英単語 _admin {{{
		// if ($CHECK_DATA['test_type_num'] == $_SESSION['t_practice']['test_type_num']) {
		// 	$test_type_num = $CHECK_DATA['test_type_num'];
		// } else {
		// 	$ERROR[] = $i."行目のテスト種類IDが不正なのでスキップしました。<br>";
		// 	continue;
		// }

		// if ($CHECK_DATA['test_category1_num'] == $_SESSION['t_practice']['test_category1_num']) {
		// 	$test_category1_num = $CHECK_DATA['test_category1_num'];
		// } else {
		// 	$ERROR[] = $i."行目のテスト種別１IDが不正なのでスキップしました。<br>";
		// 	continue;
		// }

		// if ($CHECK_DATA['test_category2_num'] == $_SESSION['t_practice']['test_category2_num']) {
		// 	$test_category2_num = $CHECK_DATA['test_category2_num'];
		// } else {
		// 	// $ERROR[] = $i."テスト種別２IDが不正なのでスキップしました。<br>"; //del kimura 2018/11/21 すらら英単語 _admin
		// 	$ERROR[] = $i."行目のテスト種別２IDが不正なのでスキップしました。<br>"; //add kimura 2018/11/21 すらら英単語 _admin
		// 	continue;
		// }
		//del end   kimura 2018/11/21 すらら英単語 _admin }}}
		//add start kimura 2018/11/21 すらら英単語 _admin
		$test_type_num = $_SESSION['t_practice']['test_type_num'];
		$test_category1_num = $_SESSION['t_practice']['test_category1_num'];
		$test_category2_num = $_SESSION['t_practice']['test_category2_num'];
		//add end   kimura 2018/11/21 すらら英単語 _admin
		
		//add start hirose 2018/11/29 すらら英単語
		if ($CHECK_DATA['problem_type'] == "surala") {
			$CHECK_DATA['problem_table_type'] = 1;
		} elseif ($CHECK_DATA['problem_type'] == "test") {
			$CHECK_DATA['problem_table_type'] = 3;
		} else {
			$ERROR[] = $i."行目の問題タイプが不正なのでスキップしました。<br>";
			continue;
		}
		//add end hirose 2018/11/29 すらら英単語

		if($CHECK_DATA['problem_num']){
			$sql = "SELECT problem_num ,problem_table_type ".
				" FROM ". T_MS_TEST_CATEGORY2_PROBLEM .
				" WHERE test_type_num='".$test_type_num."' ".
				"   AND test_category1_num='".$test_category1_num."' ".
				// "   AND test_category2_num='".$test_category2_num."';"; //del kimura 2018/11/21 すらら英単語 _admin
				"   AND test_category2_num='".$test_category2_num."'". //add kimura 2018/11/21 すらら英単語 _admin
				"   AND problem_table_type='".$CHECK_DATA['problem_table_type']."'". //add hirose 2018/11/29 すらら英単語
				"   AND problem_num='".$CHECK_DATA['problem_num']."' LIMIT 1;";
//			print $sql.'<br>';
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['problem_num']) {
				$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_'.$list['problem_num']] = "upd";
				$problem_table_type = $list['problem_table_type'];
			} else {
				$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_0'] = "add";
			}
		}else{
			$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_0'] = "add";
		}


		$sql = "SELECT problem_num".
			" FROM ". T_MS_TEST_PROBLEM .
			 " WHERE problem_num='".$CHECK_DATA['problem_num']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list2 = $cdb->fetch_assoc($result);
		}
		if ($list2['problem_num']) {
			$L_MS_TEST_PROBLEM_INS_MODE = "upd";
		} else {
			$L_MS_TEST_PROBLEM_INS_MODE = "add";
		}

		//del start hirose 2018/11/29 すらら英単語
		//problem_table_typeも検索条件に含めないといけないため、上に移動
//		if ($CHECK_DATA['problem_type'] == "surala") {
//			$CHECK_DATA['problem_table_type'] = 1;
//		} elseif ($CHECK_DATA['problem_type'] == "test") {
//			$CHECK_DATA['problem_table_type'] = 3;	//TODO
//		} else {
//			$ERROR[] = $i."行目の問題タイプが不正なのでスキップしました。<br>";
//			continue;
//		}
		//del end hirose 2018/11/29 すらら英単語

		if ( $CHECK_DATA['problem_type'] == "surala" ) {
			//	問題が存在するかチェック
			$DATA_ERROR[$i] = ewt_check_data_surala($CHECK_DATA,$ins_mode,$i);

		} else if ( $CHECK_DATA['problem_type'] == "test" ) {
			$DATA_ERROR[$i] = ewt_check_data($CHECK_DATA,$ins_mode,$i);

		}

		//add start kimura 2018/12/03 すらら英単語 _admin
		//テスト専用で新規追加でlist_numがあるとき
 		if ($CHECK_DATA['problem_type'] == "test") {
			if($L_MS_TEST_PROBLEM_INS_MODE == "add" && $CHECK_DATA['list_num']){
				//list_numは不要とする
				$DATA_ERROR[$i] = array($i."行目 表示順は新規登録時、値の指定をしないでください。");
				continue;
			}
		}

		//list_numの重複チェック
		if($list_num_err = ewt_check_list_num($test_type_num, $test_category1_num, $test_category2_num, $CHECK_DATA['problem_num'], $CHECK_DATA['list_num'], $list_num_ok_arr)){
			$DATA_ERROR[$i] = array($i."行目の".$list_num_err);
			continue;
		}
		//add end   kimura 2018/12/03 すらら英単語 _admin


		if ($DATA_ERROR[$i]) { continue; }

//pre($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE);
		// テスト専用問題登録処理
		if ($CHECK_DATA['problem_type'] == "test") {
			//対応するproblem tableを更新。ms_test_category2_problemに結び付くproblem_numを取得
			if ($L_MS_TEST_PROBLEM_INS_MODE == "add") {
				if (!$CHECK_DATA['problem_num']) {
					$sql = "SELECT MAX(problem_num) AS max_num FROM ".T_MS_TEST_PROBLEM;
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					if ($list['max_num']) { $problem_num = $list['max_num'] + 1; } else { $problem_num = 1; }
				}
			} elseif ($L_MS_TEST_PROBLEM_INS_MODE == "upd") {
				$sql = "SELECT problem_num FROM ".T_MS_TEST_PROBLEM.
						" WHERE problem_num='".$CHECK_DATA['problem_num']."'".
						" AND course_num='".$_SESSION['t_practice']['course_num']."'";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if (!$list['problem_num']) {
					$ERROR[] = $i."行目 入力された問題番号は、別教科で利用されております。";
					$problem_num = "";
					continue;
				} else {
					$problem_num = $CHECK_DATA['problem_num'];
				}
			}
			if (!$problem_num) {
				$ERROR[] = $i."行目 問題番号の取得に失敗しました。";
				continue;
			}
			//add start kimura 2018/11/22 すらら英単語 _admin
			$lms_id_err = validate_lms_id_string($CHECK_DATA['lms_unit_id']);
			if($lms_id_err === 0){
				save_lms_ids($CHECK_DATA['lms_unit_id'], $CHECK_DATA['problem_num'], 3); //add kimura 2018/11/22 すらら英単語 _admin //LMS単元更新
			}else{
				$DATA_ERROR[$i] = array( 0 => $i."行目".$lms_id_err);
			}
			if ($DATA_ERROR[$i]) { continue; }
			//add end   kimura 2018/11/22 すらら英単語 _admin
			$DATA_ERROR[$i] = ewt_problem_test_csv($problem_num,$CHECK_DATA,$L_MS_TEST_PROBLEM_INS_MODE);
			if ($DATA_ERROR[$i]) { continue; }

			$INSERT_DATA['test_type_num'] = $test_type_num;
			$INSERT_DATA['test_category1_num'] = $test_category1_num;
			$INSERT_DATA['test_category2_num'] = $test_category2_num;

//			if ($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_0'] == "add") { //del hirose 2018/11/29 すらら英単語　addでもupdでも共通にち$disp_sortを使用するため移動
				//upd start hirose 2018/11/29 すらら英単語
//				if (!$L_DISP_SORT[$key]) {
				if (!$CHECK_DATA['list_num']) {
				//upd end hirose 2018/11/29 すらら英単語
					$sql = "SELECT MAX(list_num) AS max_sort FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
						" WHERE test_type_num='".$test_type_num."' ".
						"   AND test_category1_num='".$test_category1_num."' ".
						"   AND test_category2_num='".$test_category2_num."';";
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }
				} else {
					//upd start hirose 2018/11/29 すらら英単語
//					$disp_sort = $L_DISP_SORT[$key];
					$disp_sort = $CHECK_DATA['list_num'];
					//upd end hirose 2018/11/29 すらら英単語
				}
//					print $disp_sort;
			if ($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_0'] == "add") { //add hirose 2018/11/29 すらら英単語
				
				$INSERT_DATA['list_num'] 			= $disp_sort;
				$INSERT_DATA['problem_num'] 		= $problem_num;
				$INSERT_DATA['problem_table_type'] 	= 3;	//TODO
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";
				
				 
				$DATA_ERROR[$i] = $cdb->insert(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA);

				if ($DATA_ERROR[$i]) { continue; }
			} elseif ($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$test_category2_num.'_'.$CHECK_DATA['problem_num']] == "upd") {
				//upd start hirose 2018/11/29 すらら英単語
//				if ($L_DISP_SORT[$key]) {
//					$INSERT_DATA['list_num'] 	= $L_DISP_SORT[$key];
//				}
				$INSERT_DATA['list_num'] 	= $disp_sort;
				//upd end hirose 2018/11/29 すらら英単語
				$INSERT_DATA['mk_flg'] 			= 0;
				$INSERT_DATA['mk_tts_id'] 		= "";
				$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 		= "now()";

				//del start hirose 2018/11/29 すらら英単語
				//T_MS_TEST_CATEGORY2_PROBLEMに以下のカラムなしのため、削除
//				$where_add = " AND gknn='".$_SESSION['t_practice']['gknn']."'";
//				if ($INSERT_DATA['term_bnr_ccd']) {
//					$where_add .= " AND term_bnr_ccd = '".$INSERT_DATA['term_bnr_ccd']."'";
//				}
//				if ($INSERT_DATA['term_kmk_ccd']) {
//					$where_add .= " AND term_kmk_ccd = '".$INSERT_DATA['term_kmk_ccd']."'";
//				}
				//del start hirose 2018/11/29 すらら英単語
				$where = " WHERE test_type_num='".$test_type_num."' ".
						"   AND test_category1_num='".$test_category1_num."' ".
//						"   AND test_category2_num='".$test_category2_num."';";
						"   AND test_category2_num='".$test_category2_num."'". //upd hirose 2018/11/29 すらら英単語
						" AND problem_table_type='3'".	//TODO
						" AND problem_num='".$problem_num."'".
						";";
//						print '<br>'.$where.'<br>';
				$DATA_ERROR[$i] = $cdb->update(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA,$where);
				if ($DATA_ERROR[$i]) { continue; }
			}

			//教科書単元と問題の紐付け登録
			$ERRORS = array();

			if ($DATA_ERROR[$i]) { continue; }
		// すらら問題登録処理
		} elseif ($CHECK_DATA['problem_type'] == "surala") {

			//add start kimura 2018/11/22 すらら英単語 _admin
			$lms_id_err = validate_lms_id_string($CHECK_DATA['lms_unit_id']);
			if($lms_id_err === 0){
				save_lms_ids($CHECK_DATA['lms_unit_id'], $CHECK_DATA['problem_num'], 1); //add kimura 2018/11/22 すらら英単語 _admin //LMS単元更新
			}else{
				$DATA_ERROR[$i] = array( 0 => $i."行目".$lms_id_err);
			}
			if ($DATA_ERROR[$i]) { continue; }
			//add end   kimura 2018/11/22 すらら英単語 _admin

			// if (!$CHECK_DATA['problem_num']) { //del kimura 2018/11/21 すらら英単語 _admin
			if ($CHECK_DATA['problem_num'] != "") { //add kimura 2018/11/21 すらら英単語 _admin
				$sql = "SELECT problem_num FROM ".T_PROBLEM.
					" WHERE state='0'".
					" AND problem_num='".$CHECK_DATA['problem_num']."'".
					"";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if ($list['problem_num']) { 
					$problem_num = $list['problem_num']; 
				}else {
					$SYS_ERROR[$i][] = $i."行目 システムエラー：問題番号[".$CHECK_DATA['problem_num']."]取得に失敗しました。<br>";
					continue;
				}
			}

			if (!$CHECK_DATA['list_num']) {
				$sql = "SELECT MAX(list_num) AS max_sort FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
						" WHERE test_type_num='".$test_type_num."' ".
						"   AND test_category1_num='".$test_category1_num."' ".
						"   AND test_category2_num='".$test_category2_num."';";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }
			} else {
				//upd start hirose 2018/11/29 すらら英単語
//				$disp_sort = $L_DISP_SORT[$key];
				$disp_sort = $CHECK_DATA['list_num'];
				//upd end hirose 2018/11/29 すらら英単語
			}
			$sql = "SELECT * FROM " . T_MS_TEST_CATEGORY2_PROBLEM .
				" WHERE test_type_num='".$test_type_num."' ".
				"   AND test_category1_num='".$test_category1_num."' ".
				"   AND test_category2_num='".$test_category2_num."' ".
				"   AND problem_num='".$problem_num."' AND problem_table_type='1';";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}

			//登録されていればアップデート、無ければインサート
			if ($list) {
				//upd start hirose 2018/11/29 すらら英単語
//				if ($L_DISP_SORT[$key]) {
//					$INSERT_DATA['list_num'] 	= $L_DISP_SORT[$key];
//				}
				$INSERT_DATA['list_num'] 	= $disp_sort;
				//upd end hirose 2018/11/29 すらら英単語
				$INSERT_DATA['mk_flg'] 			= 0;
				$INSERT_DATA['mk_tts_id'] 		= "";
				$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";

				// $where = " WHERE default_test_num='".$default_test_num."'". //del kimura 2018/11/21 すらら英単語 _admin
				// 	" AND problem_num='".$problem_num."' AND problem_table_type='1';"; //del kimura 2018/11/21 すらら英単語 _admin
				//add start kimura 2018/11/21 すらら英単語 _admin
				//ﾌﾟﾗｲﾏﾘ全部指定してｱｯﾌﾟﾃﾞｰﾄ
				$where = " WHERE 1";
				$where.= "  AND test_type_num = '".$test_type_num."'";
				$where.= "  AND test_category1_num = '".$test_category1_num."'";
				$where.= "  AND test_category2_num = '".$test_category2_num."'";
				$where.= "  AND problem_table_type = '1'"; //1: すらら用
				$where.= "  AND problem_num = '".$problem_num."'";
				//add end   kimura 2018/11/21 すらら英単語 _admin

				$DATA_ERROR[$i] = $cdb->update(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA,$where);
			//インサート
			} else {
				$INSERT_DATA['test_type_num'] = $test_type_num; //add kimura 2018/11/21 すらら英単語 _admin //テスト種類
				$INSERT_DATA['test_category1_num'] = $test_category1_num; //add kimura 2018/11/21 すらら英単語 _admin //テスト種別1
				$INSERT_DATA['test_category2_num'] = $test_category2_num; //add kimura 2018/11/21 すらら英単語 _admin //テスト種別2
				$INSERT_DATA['problem_num'] 		= $problem_num;
				$INSERT_DATA['problem_table_type'] 	= 1;
				$INSERT_DATA['list_num'] 			= $disp_sort;
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";
				$DATA_ERROR[$i] = $cdb->insert(T_MS_TEST_CATEGORY2_PROBLEM,$INSERT_DATA);
			}
			if ($DATA_ERROR[$i]) { continue; }

			$ERRORS = array();

			if ($DATA_ERROR[$i]) { continue; }
		}

		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }

	}
//pre($PARENT_BOOK_UNIT_ID);
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
	if (!$ERROR) {
		$html = "<br>正常に全て登録が完了しました。";
	} else {
		$html = "<br>エラーのある行数以外の登録が完了しました。";
	}

	return array($html,$ERROR);
}


/**
 * csvインポートチェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $CHECK_DATA
 * @param string $ins_mode (未使用)
 * @param integer $line_num
 * @return array エラーの場合
 */
function ewt_check_data($CHECK_DATA,$ins_mode,$line_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$CHECK_DATA['problem_type']) {
		$ERROR[] = $line_num."行目　問題種類が確認できません。";
	}
	if ($CHECK_DATA['form_type'] != "16"
	 && $CHECK_DATA['form_type'] != "17") {
		$ERROR[] = $line_num."行目　form_type16(意味)・form_type17(書く)以外はすらら英単語テストには登録できません。";
	}

	if ($CHECK_DATA['problem_type'] == "surala") {
		//del start kimura 2018/11/21 すらら英単語 _admin
		// if ($CHECK_DATA['standard_time']) {
		// 	if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		// 		$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
		// 	}
		// }
		//del end   kimura 2018/11/21 すらら英単語 _admin
	} elseif ($CHECK_DATA['problem_type'] == "test") {

		if ($CHECK_DATA['parameter'] && ereg("\[([^0-9])\]",$CHECK_DATA['parameter'])) { $ERROR[] = $line_num."行目 パラメーターが不正です。"; }

		//フォームタイプ別の項目チェック
		$array_replace = new array_replace();
		if ($CHECK_DATA['form_type'] == 16) {

			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0") {
				$ERROR[] = "正解が確認できません。";
			} else {
				$correct_num = $array_replace->set_line($CHECK_DATA['correct']);
				$correct = $array_replace->replace_line();
			}

			if (!$CHECK_DATA['option1'] && $CHECK_DATA['option1'] !== "0") {
				$ERROR[] = "選択語句が確認できません。";
			} else {
				$option1_num = $array_replace->set_line($CHECK_DATA['option1']);
				$option1 = $array_replace->replace_line();

				$L_CORRECT = explode("\n",$correct);
				$L_OPTION1 = explode("\n",$option1);
				if ($L_OPTION1) {
					foreach($L_OPTION1 as $key => $val) {
						if (preg_match("/&lt;&gt;/",$val)) {
							foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
						} elseif (preg_match("/<>/",$val)) {
							foreach (explode("<>",$val) as $word) { $L_ANS[] = trim($word); }
						} else {
							$L_ANS[] = trim($val);
						}
						$hit = array_search($L_CORRECT[$key],$L_ANS);
						if($hit === FALSE) {
							$ERROR[] = "選択語句内に正解が含まれておりません。";
							break;
						}
					}
				}
			}

			if ($CHECK_DATA['option2'] == "") { $CHECK_DATA['option2'] ="0"; }
			if ($CHECK_DATA['option2'] !== "0" && $CHECK_DATA['option2'] !== "1") {
				$ERROR[] = "シャッフル情報が不正です。";
			}

			if ($max_column > 0) {
				if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
					$ERROR[] = "出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				}
			}

		} elseif ($CHECK_DATA['form_type'] == 17) {
			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0") {
				$ERROR[] = "正解が確認できません。";
			} else {
				$correct_num = $array_replace->set_line($CHECK_DATA['correct']);
				$correct = $array_replace->replace_line();
			}

		}
		//del kimura 2018/11/21 すらら英単語 _admin
		// if ($CHECK_DATA['standard_time']) {
		// 	if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		// 		$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
		// 	}
		// }
		//del end   kimura 2018/11/21 すらら英単語 _admin

	}
/*
	pre("---------------------------");
	pre($line_num);
	pre($CHECK_DATA['unit_num']);
	pre("---------------------------");
*/
	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}


/**
 * csvインポートチェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $CHECK_DATA
 * @param string $ins_mode (未使用)
 * @param integer $line_num
 * @return array エラーの場合
 */
function ewt_check_data_surala($CHECK_DATA,$ins_mode,$line_num) {
//	add 2015/01/13 yoshizawa 課題要望一覧No.400

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "";
	$sql .= "SELECT * FROM ".T_PROBLEM." ";
	$sql .= "WHERE problem_num = '".$CHECK_DATA['problem_num']."' ";
	// $sql .= "AND block_num = '".$CHECK_DATA['block_num']."' "; //del kimura 2018/11/21 すらら英単語 _admin
	//add start kimura 2018/11/21 すらら英単語 _admin
	if(isset($CHECK_DATA['block_num'])){
		$sql .= "AND block_num = '".$CHECK_DATA['block_num']."' ";
	}
	//add end   kimura 2018/11/21 すらら英単語 _admin
	$sql .= "AND display = '1' ";
	$sql .= "AND state = '0';";
//echo $sql."<br />";
	$list = "";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);

	}

	if ( !is_array($list) ) {
		$ERROR[] = $line_num."行目 問題が存在しない、または無効となっています。";

	}
	if ( $ERROR ) {
		$ERROR[] = $line_num."行目 上記入力エラーでスキップしました。";

	}

	return $ERROR;

}

/**
 * 問題のCSV確認
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @param array $CHECK_DATA
 * @param string $ins_mode
 * @return array エラーの場合
 */
function ewt_problem_test_csv($problem_num,$CHECK_DATA,$ins_mode) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($CHECK_DATA['lms_unit_id']); //add kimura 2018/11/22 すらら英単語 _admin

	$ins_data = array();
	$ins_data = $CHECK_DATA;
	$array_replace = new array_replace();
	if ($CHECK_DATA['form_type'] == 16) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();
		if ($ins_data['option2']) {
			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();
		}

	} elseif ($CHECK_DATA['form_type'] == 17) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['option2']) {
			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();
		}

	}
	foreach ($ins_data AS $key => $val) {
		if ($key == "action"
			|| $key == "test_type_num"
			|| $key == "test_category1_num"
			|| $key == "test_category2_num"
			|| $key == "problem_type"
			|| $key == "list_num"
			|| $key == "problem_table_type"
			|| $key == "test_type"
			|| $key == ""
		) { continue; }
		$INSERT_DATA[$key] = $cdb->real_escape($val);
	}

	if ($ins_mode == "add") {
		

		//画像名変換、フォルダ作成
		//音声名変換、フォルダ作成

		// list($INSERT_DATA['problem'],$ERROR) 		= img_convert($INSERT_DATA['problem'],$problem_num); //del kimura 2018/11/26 すらら英単語 _admin
		// list($INSERT_DATA['problem'],$ERROR) 		= voice_convert($INSERT_DATA['problem'],$problem_num); //del kimura 2018/11/26 すらら英単語 _admin
		list($INSERT_DATA['problem'], $ERROR) = vocabulary_img_convert($INSERT_DATA['problem'], $problem_num); //add kimura 2018/11/26 すらら英単語 _admin
		list($INSERT_DATA['problem'], $ERROR) = vocabulary_voice_convert($INSERT_DATA['problem'], $problem_num); //add kimura 2018/11/26 すらら英単語 _admin
		list($INSERT_DATA['voice_data'], $ERROR) = vocabulary_voice_convert($INSERT_DATA['voice_data'],$problem_num); //add kimura 2018/11/26 すらら英単語 _admin

		//del start kimura 2018/11/26 すらら英単語 _admin
		// if ($INSERT_DATA['voice_data']) {
		// 	$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		// 	$dir_num = (floor($problem_num / 100) * 100) + 1;
		// 	$dir_num = sprintf("%07d",$dir_num);
		// 	$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		// 	if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
		// 		if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
		// 			copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
		// 		}
		// 		$INSERT_DATA['voice_data'] 		= $problem_num."_".$INSERT_DATA['voice_data'];
		// 	} else {
		// 		if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
		// 			copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
		// 		}
		// 	}
		// }
		//del end   kimura 2018/11/26 すらら英単語 _admin
		//add start kimura 2018/11/26 すらら英単語 _admin
		if ($INSERT_DATA['voice_data']) {
			$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
			$dir_num = (floor($problem_num / 100) * 100) + 1;
			$dir_num = sprintf("%07d",$dir_num);
			$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
			if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'])) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'], $dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
				}
				$INSERT_DATA['voice_data'] 		= $problem_num."_".$INSERT_DATA['voice_data'];
			} else {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'])) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
				}
			}
		}
		//add end   kimura 2018/11/26 すらら英単語 _admin

		$INSERT_DATA['problem_num']		= $problem_num;
		$INSERT_DATA['standard_time']	= $ins_data['standard_time'];
		$INSERT_DATA['course_num'] 		= $_SESSION['t_practice']['course_num'];	//TODO
		$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date']		= "now()";
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_PROBLEM,$INSERT_DATA);

	} elseif ($ins_mode == "upd") {
		$INSERT_DATA['standard_time']	= $CHECK_DATA['standard_time'];
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE problem_num='".$problem_num."';";

		$ERROR = $cdb->update(T_MS_TEST_PROBLEM,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/*<<< インポート-------------------------------------------------------------*/

	$sql  = "SELECT sv.service_num,scl.course_num,co.course_name FROM ". T_SERVICE ." sv ".
			" LEFT JOIN ".T_SERVICE_COURSE_LIST ." scl ON scl.service_num = sv.service_num ".
			" LEFT JOIN ".T_COURSE ." co ON co.course_num = scl.course_num ".
			" WHERE sv.mk_flg='0' ".
			"   AND scl.mk_flg='0' ".
			// "   AND sv.setup_type='4' ".	// upd hasegawa 2018/11/09 すらら英単語 テストのsetup_type→5に変更
			"   AND sv.setup_type='5' ".
			"   AND scl.course_type='1' ".
			"   ORDER BY sv.list_num , scl.service_course_list_num;";

/**
 * 一覧を作成する機能
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
			//del start kimura 2018/11/21 すらら英単語 _admin
			// " LEFT JOIN ".T_SERVICE_COURSE_LIST ." scl ON scl.course_num = mtt.test_type_num ".
			// "       AND scl.course_type = '4' ".
			// " LEFT JOIN ".T_SERVICE ." sv ON scl.service_num = sv.service_num ".
			// // "       AND sv.setup_type = '4' ".	// upd hasegawa 2018/11/09 すらら英単語 テストのsetup_type→5に変更
			// "       AND sv.setup_type = '5' ".
			//del end   kimura 2018/11/21 すらら英単語 _admin
			" WHERE mtt.mk_flg='0' ".
			//del start kimura 2018/11/21 すらら英単語 _admin
			// "   AND scl.mk_flg='0' ".
			// "   AND sv.mk_flg='0' ".
			//del end   kimura 2018/11/21 すらら英単語 _admin
			" ORDER BY mtt.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_TYPE_LIST[$list['test_type_num']] = $list['test_type_name'];
		}
	}
	return $L_TYPE_LIST;
}

/**
 * 一覧を作成する機能
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
			//del start kimura 2018/11/21 すらら英単語 _admin
			// " LEFT JOIN ".T_SERVICE_COURSE_LIST ." scl ON scl.course_num = mtt.test_type_num ".
			// "       AND scl.course_type = '4' ".
			// " LEFT JOIN ".T_SERVICE ." sv ON scl.service_num = sv.service_num ".
			//del end   kimura 2018/11/21 すらら英単語 _admin
			// "       AND sv.setup_type = '4' ".	// upd hasegawa 2018/11/09 すらら英単語 テストのsetup_type→5に変更
			// "       AND sv.setup_type = '5' ". //del kimura 2018/11/21 すらら英単語 _admin
			" WHERE mtc1.mk_flg='0' ".
			"   AND mtt.mk_flg='0' ".
			//del start kimura 2018/11/21 すらら英単語 _admin
			// "   AND scl.mk_flg='0' ".
			// "   AND sv.mk_flg='0' ".
			//del end   kimura 2018/11/21 すらら英単語 _admin
			"   AND mtc1.test_type_num='".$test_type_num."' ".
			" ORDER BY mtc1.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY1_LIST[$list['test_category1_num']] = $list['test_category1_name'];
		}
	}
	return $L_CATEGORY1_LIST;
}

/**
 * 一覧を作成する機能
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
			//del start kimura 2018/11/21 すらら英単語 _admin
			// " LEFT JOIN ".T_SERVICE_COURSE_LIST ." scl ON scl.course_num = mtt.test_type_num ".
			// "       AND scl.course_type = '4' ".
			// " LEFT JOIN ".T_SERVICE ." sv ON scl.service_num = sv.service_num ".
			//del end   kimura 2018/11/21 すらら英単語 _admin
			// "       AND sv.setup_type = '4' ".	// upd hasegawa 2018/11/09 すらら英単語 テストのsetup_type→5に変更
			// "       AND sv.setup_type = '5' ". //del kimura 2018/11/21 すらら英単語 _admin
			" WHERE mtc2.mk_flg='0' ". //del kimura 2018/11/21 すらら英単語 _admin
			"   AND mtc1.mk_flg='0' ". //del kimura 2018/11/20 すらら英単語 _admin
			"   AND mtt.mk_flg='0' ". //del kimura 2018/11/20 すらら英単語 _admin
			// "   AND scl.mk_flg='0' ". //del kimura 2018/11/20 すらら英単語 _admin
			// "   AND sv.mk_flg='0' ". //del kimura 2018/11/21 すらら英単語 _admin
			"   AND mtc2.test_type_num='".$test_type_num."' ".
			"   AND mtc2.test_category1_num='".$test_category1_num."' ".
			" ORDER BY mtc2.list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY2_LIST[$list['test_category2_num']] = $list['test_category2_name'];
		}
	}
	return $L_CATEGORY2_LIST;
}

//add start kimura 2018/11/21 すらら英単語 _admin

/**
 * get_lms_unit_ids 
 * 
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return string  LMS単元::区切りの文字列
 */
function get_lms_unit_ids($problem_num, $problem_table_type){

	$cdb = $GLOBALS['cdb'];
	$lms_unit_id_string = "";

	$sql = "";
	$sql.= "SELECT unit_num FROM ".T_BOOK_UNIT_LMS_UNIT;
	$sql.= " WHERE problem_num = '".$problem_num."'";
	$sql.= " AND problem_table_type = '".$problem_table_type."'";
	$sql.= " AND book_unit_id = '0'"; //教科書単元なし
	$sql.= " AND mk_flg = '0'";
	$sql.= " ORDER BY unit_num"; //ユニット番号小さい順左からにしておきます。

	if($result = $cdb->query($sql)){
		while($list = $cdb->fetch_assoc($result)){
			$lms_unit_id_string.= $list['unit_num']."::";
		}
	}

	$lms_unit_id_string = rtrim($lms_unit_id_string, "::");
	return $lms_unit_id_string;
}
//add end   kimura 2018/11/21 すらら英単語 _admin

//add start kimura 2018/11/22 すらら英単語 _admin
/**
 * 画像TAGからIMGタグに変更[単語用]
 *
 * @param string $problem
 * @param integer $problem_num
 * @return array
 */
function vocabulary_img_convert($problem,$problem_num) {
	preg_match_all("|\[!IMG=(.*)!\]|U",$problem,$L_IMG_LIST);
	if (isset($L_IMG_LIST[1][0])) {
		//フォルダ作成
		$ERROR = vocabulary_dir_maker(MATERIAL_TEST_IMG_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_IMG_DIR.$dir_num."/";
		foreach ($L_IMG_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				if (file_exists(MATERIAL_TEST_DEF_IMG_DIR_VOCABULARY.$val)) {
					copy(MATERIAL_TEST_DEF_IMG_DIR_VOCABULARY.$val,$dir_name.$val);
				}
				continue;
			}
			$problem = preg_replace("/".$val."/",$problem_num."_".$val,$problem);
			if (file_exists(MATERIAL_TEST_DEF_IMG_DIR_VOCABULARY.$val)) {
				copy(MATERIAL_TEST_DEF_IMG_DIR_VOCABULARY.$val,$dir_name.$problem_num."_".$val);
			}
		}
	}
	return array($problem,$ERROR);
}

/**
 * 音声TAG変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param string $voice
 * @param integer $problem_num
 * @return array
 */
function vocabulary_voice_convert($voice,$problem_num) {
	preg_match_all("|\[!VOICE=(.*)!\]|U",$voice,$L_VOICE_LIST);
	if (isset($L_VOICE_LIST[1][0])) {
		//フォルダ作成
		$ERROR = vocabulary_dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		foreach ($L_VOICE_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$val)) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$val,$dir_name.$val);
				}
				continue;
			}
			$voice = preg_replace("/".$val."/",$problem_num."_".$val,$voice);
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$val)) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR_VOCABULARY.$val,$dir_name.$problem_num."_".$val);
			}
		}
	}
	return array($voice,$ERROR);
}

/**
 * フォルダーを作成
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param string $dir
 * @param integer $problem_num
 * @param integer $num
 * @return array エラーの場合
 */
function vocabulary_dir_maker($dir,$problem_num,$num) {
	$dir_num = (floor($problem_num / $num) * $num) + 1;
	$dir_num = sprintf("%07d",$dir_num);
	$dir_name = $dir.$dir_num."/";
	$ERROR = vocabulary_dir_make($dir_name);

	return $ERROR;
}

/**
 * ディレクトリ生成
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param string $dir_name
 * @param integer $mode
 * @return array エラーの場合
 */
function vocabulary_dir_make($dir_name,$mode=0777) {
	if (!file_exists($dir_name)) {
		if (!mkdir($dir_name,$mode)) {
			$ERROR[] = $dir_name." のフォルダーが作成できませんでした。";
		}
		@chmod($dir_name,$mode);
	}
	return $ERROR;
}
//add end   kimura 2018/11/22 すらら英単語 _admin

//add start kimura 2018/12/03 すらら英単語 _admin
function ewt_check_list_num($test_type_num, $test_category1_num, $test_category2_num, $problem_num, $list_num){
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$ERROR = "";
	$rows = 0; //取得した行数

	$sql = "";
	$sql.= " SELECT problem_num, problem_table_type, list_num";
	$sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM;
	$sql.= " WHERE 1";
	$sql.= "  AND list_num = '".$list_num."'";
	// if(!empty($list_num_ok_arr)){
	// 	$sql.= ",".implode(",",$list_num_ok_arr);
	// }
	// $sql.= ")";
	// $sql.= "  AND list_num = '".$list_num."'";
	$sql.= "  AND test_type_num = '".$test_type_num."'";
	$sql.= "  AND test_category1_num = '".$test_category1_num."'";
	$sql.= "  AND test_category2_num = '".$test_category2_num."'";
	$sql.= "  AND problem_num != '".$problem_num."'"; //自身以外で登録しようとしたlist_numを使っているレコードがあるか？
	$sql.= "  AND mk_flg='0';";
	//すらら問題/テスト専用問題はlist_num共有なのでテーブルタイプは判断しない

	if ($result = $cdb->query($sql)) {
		$rows = $cdb->num_rows($result);
	}
	if($rows > 0){
		$ERROR = "list_numはすでに使用されているためスキップしました。<br>";
	}
	return $ERROR;
}
//add end   kimura 2018/12/03 すらら英単語 _admin
?>
