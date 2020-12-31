<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　数学検定グループ管理
 *
 * 履歴
 * 2015/10/02 初期設定
 *
 * @author Azet
 */

// hasegawa 2015/9/10 02_作業要件/34_数学検定/数学検定

//	統合DB接続情報（エントリー側に以下のチェックをしています）
//	・すでに申込生徒が存在しているか
//	・登録するパラメーターがエントリー側に存在しているかチェック

// update start oda 2020/09/21 テスト標準化 DB接続先をIPで判断する様に修正
// //$HEDATA = $L_DB['srlchd01'];	//	本番統合DB
// 	$HEDATA = $L_DB['srlctd01'];	//	検証統合DB
	switch ($_SERVER['HTTP_HOST']){
		//-------------------
		//azet開発環境 (テスト用DB)
		//-------------------
		case "13.114.235.64":
		case "10.3.11.100":

			$HEDATA = $L_DB['srlcmd01'];	//	開発統合DB
			break;
			//-------------------
			// すらら様開発環境
			//-------------------
		case "10.3.11.101":
			$HEDATA = $L_DB['srlchd01'];	//	本番統合DB

			break;
		default:
			$HEDATA = $L_DB['srlchd01'];	//	本番統合DB
			break;
	}
// update end oda 2020/09/21 テスト標準化 DB接続先をIPで判断する様に修正

//	コア分散サーバー（以下のチェックをしています）
//	・test_dataに該当グループIDの実績が存在しているかチェック

// update start oda 2020/09/21 テスト標準化 DB接続先をIPで判断する様に修正
//	本番コア分散DB
// $HCDB = array(	 1 => $L_DB['srlchd02']
// 				,2 => $L_DB['srlchd03']
// 				,3 => $L_DB['srlchd04']
// 				,4 => $L_DB['srlchd05']
// 				,5 => $L_DB['srlchd06']
// 				,6 => $L_DB['srlchd07']
// 				,7 => $L_DB['srlchd08']
// 				,8 => $L_DB['srlchd09']
// 				// add start oda 2020/07/21 スケールアウト インスタンス作成時解放
// 				,9 => $L_DB['srlchd10']
// 				,10 => $L_DB['srlchd10']
// 				,11 => $L_DB['srlchd11']
// 				,12 => $L_DB['srlchd12']
// 				,13 => $L_DB['srlchd13']
// // 				,14 => $L_DB['srlchd14']
// // 				,15 => $L_DB['srlchd15']
// 				// add end oda 2020/07/21 スケールアウト インスタンス作成時解放
// 			);
// //	検証コア分散DB
// $SCDB = array(	 1 => $L_DB['srlctd0201']
// 				,2 => $L_DB['srlctd0202']
// 				,3 => $L_DB['srlctd0203']
// 				,4 => $L_DB['srlctd0204']
// 				,5 => $L_DB['srlctd0305']
// 				,6 => $L_DB['srlctd0306']
// 				,7 => $L_DB['srlctd0307']
// 				,8 => $L_DB['srlctd0308']
// 				// add start oda 2020/07/21 スケールアウト インスタンス作成時解放
// 				,9 => $L_DB['srlctd0309']
// 				,10 => $L_DB['srlctd0310']
// 				,11 => $L_DB['srlctd0311']
// 				,12 => $L_DB['srlctd0312']
// 				,13 => $L_DB['srlctd0313']
// // 				,14 => $L_DB['srlctd0314']
// // 				,15 => $L_DB['srlctd0315']
// 				// add start oda 2020/07/21 スケールアウト インスタンス作成時解放
// 			);

	switch ($_SERVER['HTTP_HOST']){
		//-------------------
		//azet開発環境 (テスト用DB)
		//-------------------
		case "13.114.235.64":
		case "10.3.11.100":
			//	開発コア分散DB
			$HCDB = array(	 1 => $L_DB['srlctd3101']
							,2 => $L_DB['srlctd3102']
							,3 => $L_DB['srlctd3103']
							,4 => $L_DB['srlctd3104']
							,5 => $L_DB['srlctd3105']
							,6 => $L_DB['srlctd3106']
							,7 => $L_DB['srlctd3107']
							,8 => $L_DB['srlctd3108']
							,9 => $L_DB['srlctd3109']
							,10 => $L_DB['srlctd3110']
							,11 => $L_DB['srlctd3111']
							,12 => $L_DB['srlctd3112']
							,13 => $L_DB['srlctd3113']
// 							,14 => $L_DB['srlctd3114']
// 							,15 => $L_DB['srlctd3115']
			);
			break;
			//-------------------
			// すらら様開発環境
			//-------------------
		case "10.3.11.101":
			//	本番コア分散DB
			$HCDB = array(	 1 => $L_DB['srlchd02']
							,2 => $L_DB['srlchd03']
							,3 => $L_DB['srlchd04']
							,4 => $L_DB['srlchd05']
							,5 => $L_DB['srlchd06']
							,6 => $L_DB['srlchd07']
							,7 => $L_DB['srlchd08']
							,8 => $L_DB['srlchd09']
							,9 => $L_DB['srlchd10']
							,10 => $L_DB['srlchd11']
							,11 => $L_DB['srlchd12']
							,12 => $L_DB['srlchd13']
							,13 => $L_DB['srlchd14']
// 							,14 => $L_DB['srlchd15']
// 							,15 => $L_DB['srlchd16']
			);

			break;
		default:
			//	本番コア分散DB
			$HCDB = array(	 1 => $L_DB['srlchd02']
							,2 => $L_DB['srlchd03']
							,3 => $L_DB['srlchd04']
							,4 => $L_DB['srlchd05']
							,5 => $L_DB['srlchd06']
							,6 => $L_DB['srlchd07']
							,7 => $L_DB['srlchd08']
							,8 => $L_DB['srlchd09']
							,9 => $L_DB['srlchd10']
							,10 => $L_DB['srlchd11']
							,11 => $L_DB['srlchd12']
							,12 => $L_DB['srlchd13']
							,13 => $L_DB['srlchd14']
// 							,14 => $L_DB['srlchd15']
// 							,15 => $L_DB['srlchd16']
			);
			break;
	}
// update end oda 2020/09/21 テスト標準化 DB接続先をIPで判断する様に修正



$HCDATA = $HCDB;	//	本番コア分散DB
//	$HCDATA = $SCDB;	//	検証コア分散DB


//	統合サーバーエラーチェック基本設定
define('SCH_YEAR',		'12');
define('C48_ONCE',		'2');

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") { $ERROR = check(); }
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "sub_session") { $ERROR = sub_session(); }
		elseif (ACTION == "view_session") { $ERROR = view_session(); }
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= group_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= group_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= group_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= group_list($ERROR);
	}

	return $html;
}


/**
 * 数学検定グループ　絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	// global $L_MATH_TEST_CLASS,$L_DESC,$L_PAGE_VIEW; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_DESC,$L_PAGE_VIEW;
	$test_type5 = new TestStdCfgType5($GLOBALS['cdb']);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発
	//級の絞込
	unset($L_MATH_TEST_CLASS[1]);
	foreach ($L_MATH_TEST_CLASS as $key => $val){
		if ($_SESSION['sub_session']['s_class'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_class_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	//昇順、降順
	foreach ($L_DESC as $key => $val){
		if ($_SESSION['sub_session']['s_desc'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_desc_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	//ページ数
	if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 2; }
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}

	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$sub_session_html .= "<td>\n";
	$sub_session_html .= "級 \n";
	$sub_session_html .= "<select name=\"s_class\">\n".$s_class_html."</select>\n";
	$sub_session_html .= "ソート \n";
	$sub_session_html .= "<select name=\"s_desc\">\n".$s_desc_html."</select>\n";
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

	return $html;
}


/**
 * 数学検定グループ　絞り込みメニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['s_class'])) { $_SESSION['sub_session']['s_class'] = $_POST['s_class']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}


/**
 * 数学検定グループ一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function group_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_PAGE_VIEW,$L_MATH_TEST_CLASS ,$L_DISPLAY; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_PAGE_VIEW,$L_DISPLAY;
	// add end 2020/09/15 phong テスト標準化開発

	global $L_EXP_CHA_CODE;

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	$html .= "インポートする場合は、数学検定グループcsvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
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
	$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
	$html .= "</form>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"グループ新規登録\">\n";
		$html .= "</form>\n";
	}
	$html .= select_menu();

	$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP . " ms_test_group" .
			" WHERE ms_test_group.mk_flg='0'".
			// upd start hirose 2020/09/11 テスト標準化開発
			// " AND ms_test_group.srvc_cd = 'STEST' ";
			" AND ms_test_group.class_id > '' ";
			// upd end hirose 2020/09/11 テスト標準化開発
	if ($_SESSION['sub_session']['s_class'] && $_SESSION['sub_session']['s_class'] != 'select') {
		$sql .= " AND ms_test_group.class_id = '".$_SESSION['sub_session']['s_class']."' ";
	}
	if ($result = $cdb->query($sql)) {
		$test_group_count = $cdb->num_rows($result);
	}
	if (!$test_group_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている数学検定グループは有りません。<br>\n";
		return $html;
	}

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
	$orderby = "ORDER BY ms_test_group.disp_sort ".$sort_key;
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($test_group_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する数学検定グループの詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録マスタ総数(".$test_group_count."):PAGE[".$page."/".$max_page."]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>グループID</th>\n";
		$html .= "<th>級</th>\n";
		$html .= "<th>グループ名(テスト名)</th>\n";
		$html .= "<th>受験期間</th>\n";
		$html .= "<th>申込期間</th>\n";
		$html .= "<th>受講サービス </th>\n";
		$html .= "<th>詳細コースCD</th>\n";
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

		while ($list=$cdb->fetch_assoc($result)) {
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if (!$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
			}
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"test_group_id\" value=\"".$list['test_group_id']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['test_group_id']."</td>\n";
			$html .= "<td>".$L_MATH_TEST_CLASS[$list['class_id']]."</td>\n";
			$html .= "<td>".$list['test_group_name']."</td>\n";
			$html .= "<td>".str_replace("-","/",$list['kkn_from'])."<br>".str_replace("-","/",$list['kkn_to'])."</td>\n";
			$html .= "<td>".str_replace("-","/",$list['mskm_kkn_from'])."<br>".str_replace("-","/",$list['mskm_kkn_to'])."</td>\n";
			$html .= "<td>".$list['srvc_cd']."</td>\n";
			$html .= "<td>".$list['sysi_crs_cd']."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
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
 * 数学検定グループ　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// global $L_MATH_TEST_CLASS ,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;
	$test_type5 = new TestStdCfgType5($GLOBALS['cdb']);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	global $ERROR_CODE,$ERROR_SUB_CODE;

	$INPUTS = array();

	//年リスト
	$L_YEAR = year_list();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['from_year']);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['from_month']);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['from_day']);
	$kkn_from .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($_POST['from_hour']);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['from_minute']);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['from_second']);
	$kkn_from .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['to_year']);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['to_month']);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['to_day']);
	$kkn_to .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($_POST['to_hour']);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['to_minute']);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['to_second']);
	$kkn_to .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['mskm_from_year']);
	$mskm_kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['mskm_from_month']);
	$mskm_kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['mskm_from_day']);
	$mskm_kkn_from .= $newform->make()."　";

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['mskm_to_year']);
	$mskm_kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['mskm_to_month']);
	$mskm_kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['mskm_to_day']);
	$mskm_kkn_to .= $newform->make()."　";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

	//コースリスト
	$L_COURSE_LIST = course_list();

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_GROUP_FORM);

	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("display");
		$newform->set_form_id("display_".$key);
		$newform->set_form_check($_POST['display']);
		$newform->set_form_value("".$key."");
		$display_btn = $newform->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[MTGROUPID] 		= array('result'=>'plane','value'=>"---");
	$INPUTS[MTGROUPNAME] 		= array('type'=>'text','name'=>'test_group_name','size'=>'50','value'=>$_POST['test_group_name']);
	$INPUTS[MTCLASSID]	 		= array('type'=>'select','name'=>'class_id','array'=>$L_MATH_TEST_CLASS ,'check'=>$_POST['class_id']);
	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS[KKNATT] 		= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS[MSKMKKNFROM] 	= array('result'=>'plane','value'=>$mskm_kkn_from);
	$INPUTS[MSKMKKNTO] 		= array('result'=>'plane','value'=>$mskm_kkn_to);
	$INPUTS[MSKMKKNATT] 	= array('result'=>'plane','value'=>$mskm_kkn_att);
	$INPUTS['SRVCCD']		= array('type'=>'text','name'=>'srvc_cd','size'=>'20','value'=>$_POST['srvc_cd']);// add hirose 2020/09/11 テスト標準化開発
	$INPUTS[SYSICRSCD]		= array('type'=>'text','name'=>'sysi_crs_cd','size'=>'20','value'=>$_POST['sysi_crs_cd']);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 数学検定グループ　修正フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MATH_TEST_CLASS ,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;  // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	$INPUTS = array();

	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_MS_BOOK_GROUP .
				" WHERE test_group_id='".$_POST['test_group_id']."'".
				// upd start hirose 2020/09/11 テスト標準化開発
				// " AND srvc_cd = 'STEST'".
				" AND class_id > '' ".
				// upd end hirose 2020/09/11 テスト標準化開発
				" AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		list($from_days,$from_time) = explode(" ",$kkn_from);
		list($from_year,$from_month,$from_day) = explode("-",$from_days);
		list($from_hour,$from_minute,$from_second) = explode(":",$from_time);
		list($to_days,$to_time) = explode(" ",$kkn_to);
		list($to_year,$to_month,$to_day) = explode("-",$to_days);
		list($to_hour,$to_minute,$to_second) = explode(":",$to_time);

		//	add ookawara 2011/10/14 start
		list($mskm_from_days,$mskm_from_time) = explode(" ",$mskm_kkn_from);
		list($mskm_from_year,$mskm_from_month,$mskm_from_day) = explode("-",$mskm_from_days);
		list($mskm_to_days,$mskm_to_time) = explode(" ",$mskm_kkn_to);
		list($mskm_to_year,$mskm_to_month,$mskm_to_day) = explode("-",$mskm_to_days);

	}
	//年リスト
	$L_YEAR = year_list();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($from_year);
	$kkn_from = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($from_month);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($from_day);
	$kkn_from .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($from_hour);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($from_minute);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($from_second);
	$kkn_from .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($to_year);
	$kkn_to = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($to_month);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($to_day);
	$kkn_to .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($to_hour);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($to_minute);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($to_second);
	$kkn_to .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($mskm_from_year);
	$mskm_kkn_from = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($mskm_from_month);
	$mskm_kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($mskm_from_day);
	$mskm_kkn_from .= $newform->make()."　";

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($mskm_to_year);
	$mskm_kkn_to = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($mskm_to_month);
	$mskm_kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("mskm_to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($mskm_to_day);
	$mskm_kkn_to .= $newform->make()."　";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

	//コースリスト
	$L_COURSE_LIST = course_list();

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_group_id\" value=\"".$test_group_id."\">\n";
	if (!$base_display) { $base_display = $display; }
	$html .= "<input type=\"hidden\" name=\"base_display\" value=\"".$base_display."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_GROUP_FORM);

	if (!$test_group_id) { $test_group_id = "---"; }
	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("display");
		$newform->set_form_id("display_".$key);
		$newform->set_form_check($display);
		$newform->set_form_value("".$key."");
		$display_btn = $newform->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}
	$INPUTS[MTGROUPID] 		= array('result'=>'plane','value'=>$test_group_id);
	$INPUTS[MTGROUPNAME] 		= array('type'=>'text','name'=>'test_group_name','size'=>'50','value'=>$test_group_name);
	$INPUTS[MTCLASSID] 			= array('type'=>'select','name'=>'class_id','array'=>$L_MATH_TEST_CLASS ,'check'=>$class_id);
	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS[KKNATT] 		= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS[MSKMKKNFROM] 		= array('result'=>'plane','value'=>$mskm_kkn_from);
	$INPUTS[MSKMKKNTO] 			= array('result'=>'plane','value'=>$mskm_kkn_to);
	$INPUTS[MSKMKKNATT] 		= array('result'=>'plane','value'=>$mskm_kkn_att);
	$INPUTS['SRVCCD']		= array('type'=>'text','name'=>'srvc_cd','size'=>'20','value'=>$srvc_cd);// add hirose 2020/09/11 テスト標準化開発
	$INPUTS[SYSICRSCD]		= array('type'=>'text','name'=>'sysi_crs_cd','size'=>'20','value'=>$sysi_crs_cd);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	// add start hirose 2020/09/12 テスト標準化開発
	$read_flg = check_application_status($test_group_id);
	if($read_flg){
		$INPUTS['SRVCCD'] += read_only_array();
		$INPUTS['SYSICRSCD'] += read_only_array();
		$html .= "申し込み済のテストのため、受講サービス/詳細コースコードは変更できません";
	}
	// add end hirose 2020/09/12 テスト標準化開発

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 数学検定グループ　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	global $HEDATA;

	//年リスト
	$L_YEAR = year_list();

	$_POST['test_group_name'] = mb_convert_kana($_POST['test_group_name'], 'asKV', 'UTF-8');
	$_POST['test_group_name'] = trim($_POST['test_group_name']);
	if (!$_POST['test_group_name']) {
		$ERROR[] = "グループ名が未入力です。";
	} elseif (strlen($_POST['test_group_name']) > 80) {
		$ERROR[] = "グループ名が長すぎます。半角80文字以内で記述して下さい。";
	} else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP.
					" WHERE mk_flg='0'".
					// upd start hirose 2020/09/11 テスト標準化開発
					// " AND srvc_cd = 'STEST'".
					" AND class_id > '' ".
					// upd end hirose 2020/09/11 テスト標準化開発
					" AND class_id='".$_POST['class_id']."'".
					" AND test_group_name='".$_POST['test_group_name']."'";
		} else {
			$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP.
					" WHERE mk_flg='0'".
					// upd start hirose 2020/09/11 テスト標準化開発
					// " AND srvc_cd = 'STEST'".
					" AND class_id > '' ".
					// upd end hirose 2020/09/11 テスト標準化開発
					" AND class_id='".$_POST['class_id']."'".
					" AND test_group_id!='".$_POST['test_group_id']."'".
					" AND test_group_name='".$_POST['test_group_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		//if ($count > 0) { $ERROR[] = "入力されたグループ名は既に登録されております。"; }
	}

	$class_r_flg = 0;
	$tc_class_id_flg = 1;
	if (!$_POST['class_id'] || $_POST['class_id'] == "select" ) {
		$ERROR[] = "級が未選択です。";
		$tc_class_id_flg = 1;
	} elseif ($_POST['class_id'] == 'R1'){
		$class_r_flg = 1;
	}

	if (!$_POST['from_year'] || !$_POST['from_month'] || !$_POST['from_day']) {
		$ERROR[] = "受験期間 開始が未選択です。";
	} else {
		if (checkdate($L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']])) {
			$from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']]));
		} else {
			$ERROR[] = "受験期間 開始が不正です。選択された日付は存在しません。";
		}
	}

	if (!$_POST['to_year'] || !$_POST['to_month'] || !$_POST['to_day']) {
		$ERROR[] = "受験期間 終了が未選択です。";
	} else {
		if (checkdate($L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']])) {
			$to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']]));
			if ($from_day > $to_day) {
				$ERROR[] = "受験期間 終了が不正です。入力された日付が受験期間 開始より過去になっています。";
			}
		} else {
			$ERROR[] = "受験期間 終了が不正です。選択された日付は存在しません。";
		}
	}

	//	申込期間 開始
	$mskm_from_flg = 0;
	if ($class_r_flg == 1 && ($_POST['mskm_from_year'] || $_POST['mskm_from_month'] || $_POST['mskm_from_day'])) {
			$ERROR[] = "申込期間 開始が不正です。練習ドリル選択時は申込期間を設定しないでください。";
	} elseif ($_POST['mskm_from_year'] || $_POST['mskm_from_month'] || $_POST['mskm_from_day']) {
		$mskm_from_flg = 1;
		if ($_POST['mskm_from_year'] && $_POST['mskm_from_month'] && $_POST['mskm_from_day']) {

			if (checkdate($L_MONTH[$_POST['mskm_from_month']],$L_DAY[$_POST['mskm_from_day']],$L_YEAR[$_POST['mskm_from_year']])) {
				$mskm_from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['mskm_from_month']],$L_DAY[$_POST['mskm_from_day']],$L_YEAR[$_POST['mskm_from_year']]));

				if ($mskm_from_day && $from_day && $mskm_from_day > $from_day) {
					$ERROR[] = "申込期間 開始が不正です。受験期間 開始後に申込期間 開始となっております。";
				}
			} else {
				$ERROR[] = "申込期間 開始が不正です。選択された日付は存在しません。";
			}
		} else {
			$ERROR[] = "申込期間 開始が不正です。日時を全て選択してください。";
		}
	} elseif ($class_r_flg != 1) {
		$ERROR[] = "申込期間 開始が入力されておりません。";
	}

	//	申込期間 終了
	if ($class_r_flg == 1 && ($_POST['mskm_to_year'] || $_POST['mskm_to_month'] || $_POST['mskm_to_day'])) {
		$ERROR[] = "申込期間 終了が不正です。練習ドリル選択時は申込期間を設定しないでください。";
	} elseif ($mskm_from_flg == 1) {
		if (!$_POST['mskm_to_year'] || !$_POST['mskm_to_month'] || !$_POST['mskm_to_day']) {
			$ERROR[] = "申込期間 終了が未選択です。";
		} else {
			if (checkdate($L_MONTH[$_POST['mskm_to_month']],$L_DAY[$_POST['mskm_to_day']],$L_YEAR[$_POST['mskm_to_year']])) {
				$mskm_to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['mskm_to_month']],$L_DAY[$_POST['mskm_to_day']],$L_YEAR[$_POST['mskm_to_year']]));

				if ($mskm_to_day > $to_day) {
					$ERROR[] = "申込期間 終了が不正です。受験期間 終了後に申込期間 終了となっております。";
				}
			} else {
				$ERROR[] = "申込期間 終了が不正です。選択された日付は存在しません。";
			}
		}
	}

	// add start hirose 2020/09/11 テスト標準化開発
	$tc_srvc_cd_flg = 1;
	$_POST['srvc_cd'] = mb_convert_kana($_POST['srvc_cd'], 'as', 'UTF-8');
	$_POST['srvc_cd'] = trim($_POST['srvc_cd']);
	if(!$_POST['srvc_cd']){
		$ERROR[] = "サービスCDが未入力です。";
		$tc_srvc_cd_flg = 0;
	}elseif(!preg_match('/^[a-zA-Z0-9]+$/',$_POST['srvc_cd'])){
		$ERROR[] = "サービスCDは半角英数字で記入してください";
		$tc_srvc_cd_flg = 0;
	} elseif (strlen($_POST['srvc_cd']) > 10) {
		$ERROR[] = "サービスCDが長すぎます。半角10文字以内で記述して下さい。";
		$tc_srvc_cd_flg = 0;
	}
	// add end hirose 2020/09/11 テスト標準化開発

	//	詳細コースCD
	$tc_sysi_crs_cd_flg = 1;
	$_POST['sysi_crs_cd'] = mb_convert_kana($_POST['sysi_crs_cd'], 'as', 'UTF-8');
	$_POST['sysi_crs_cd'] = trim($_POST['sysi_crs_cd']);
	if ($class_r_flg == 1 && $_POST['sysi_crs_cd']) {
		$ERROR[] = "練習ドリル選択時は詳細コースCDを入力しないでください。";
		$tc_sysi_crs_cd_flg = 0;
	} elseif ($mskm_from_flg == 1) {
		if (!$_POST['sysi_crs_cd']) {
			$ERROR[] = "詳細コースCDが未入力です。";
			$tc_sysi_crs_cd_flg = 0;
		} elseif (strlen($_POST['sysi_crs_cd']) > 10) {
			$ERROR[] = "詳細コースCDが長すぎます。半角10文字以内で記述して下さい。";
			$tc_sysi_crs_cd_flg = 0;
		}
	}

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未選択です。";
	}

	$_POST['usr_bko'] = mb_convert_kana($_POST['usr_bko'], 'asKV', 'UTF-8');	//	add ookawara 2011/10/18
	$_POST['usr_bko'] = trim($_POST['usr_bko']);	//	add ookawara 2011/10/18
	if (strlen($_POST['usr_bko']) > 255) {
		$ERROR[] = "備考が不正です。255文字以内で記述して下さい。";	// change ookawara 2011/10/18 mb_strlen → strlen
	}

	//	本番統合サーバーのデーターでチェック
	//	統合DB接続
    $connect_db_total = new connect_db();
    $connect_db_total->set_db($HEDATA);

	$ERROR2 = array(); // add 2015/11/06 yoshizawa array_mergeでエラーにならないように初期化します
    $ERROR2 = $connect_db_total->set_connect_db();
    if ($ERROR && $ERROR2){
    	$ERROR = array_merge($ERROR, $ERROR2);
    }

	//	統合サーバーでのエラーチェック
	$CHECK_DATA = array();
	if ($tc_class_id_flg == 1) {
		$CHECK_DATA['class_id'] = $_POST['class_id'];
	}
	// add start hirose 2020/09/11 テスト標準化開発
	if ($tc_srvc_cd_flg == 1) {
		$CHECK_DATA['srvc_cd'] = $_POST['srvc_cd'];
	}
	// add end hirose 2020/09/11 テスト標準化開発
	if ($tc_jyko_crs_cd_flg == 1) {
		$CHECK_DATA['jyko_crs_cd'] = $_POST['jyko_crs_cd'];
	}

	if ($tc_sysi_crs_cd_flg == 1) {
		$CHECK_DATA['sysi_crs_cd'] = $_POST['sysi_crs_cd'];
	}
	if ($_POST['display'] == 2 && $_POST['base_display'] == 1 && $_POST['test_group_id'] > 0) {
		$CHECK_DATA['test_group_id'] = $_POST['test_group_id'];
	}

	he_check_data($CHECK_DATA, 0, $connect_db_total, $ERROR);
	//$connect_db_total->close();

	return $ERROR;
}


/**
 * 数学検定グループ　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MATH_TEST_CLASS ,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd ene hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	global $HEDATA;

	$INPUTS = array();

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM " . T_MS_BOOK_GROUP .
				" WHERE test_group_id='".$_POST['test_group_id']."'".
				// upd start hirose 2020/09/11 テスト標準化開発
				// " AND srvc_cd = 'STEST'".
				" AND class_id > '' ".
				// upd end hirose 2020/09/11 テスト標準化開発
				" AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}

		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		$sql2 = "SELECT default_test_num FROM ".T_BOOK_GROUP_LIST." test_group_list".
			" WHERE test_group_list.test_group_id='".$test_group_id."'".
			" AND test_group_list.mk_flg='0'";
		if ($result2 = $cdb->query($sql2)) {
			while ($list2=$cdb->fetch_assoc($result2)) {
				$L_DEFAULT_TEST_NUM[] = $list2['default_test_num'];
			}
		}
		if (is_array($L_DEFAULT_TEST_NUM)) {
			$default_test_num = implode(" ",$L_DEFAULT_TEST_NUM);
		} else {
			$default_test_num = "&nbsp;";
		}

		list($from_days,$from_time) = explode(" ",$kkn_from);
		list($from_year,$from_month,$from_day) = explode("-",$from_days);
		list($from_hour,$from_minute,$from_second) = explode(":",$from_time);
		$from_month = sprintf("%01d",$from_month);
		$from_day = sprintf("%01d",$from_day);
		$from_hour = sprintf("%01d",$from_hour);
		$from_minute = sprintf("%01d",$from_minute);
		$from_second = sprintf("%01d",$from_second);
		list($to_days,$to_time) = explode(" ",$kkn_to);
		list($to_year,$to_month,$to_day) = explode("-",$to_days);
		list($to_hour,$to_minute,$to_second) = explode(":",$to_time);
		$to_month = sprintf("%01d",$to_month);
		$to_day = sprintf("%01d",$to_day);
		$to_hour = sprintf("%01d",$to_hour);
		$to_minute = sprintf("%01d",$to_minute);
		$to_second = sprintf("%01d",$to_second);

		list($mskm_from_year,$mskm_from_month,$mskm_from_day) = explode("-",$mskm_kkn_from);
		$mskm_from_month = sprintf("%01d",$mskm_from_month);
		$mskm_from_day = sprintf("%01d",$mskm_from_day);
		list($mskm_to_year,$mskm_to_month,$mskm_to_day) = explode("-",$mskm_kkn_to);
		$mskm_to_month = sprintf("%01d",$mskm_to_month);
		$mskm_to_day = sprintf("%01d",$mskm_to_day);

	}
	//コースリスト
	$L_COURSE_LIST = course_list();

	//年リスト
	$L_YEAR = year_list();

	$kkn_from = $L_YEAR[$from_year]." / ".$L_MONTH[$from_month]." / ".$L_DAY[$from_day];
	if ($from_hour || $from_minute || $from_second) {
		$kkn_from .= "　".$L_HOUR[$from_hour]." ： ".$L_MINUTE[$from_minute]." ： ".$L_MINUTE[$from_second];
	}
	$kkn_to = $L_YEAR[$to_year]." / ".$L_MONTH[$to_month]." / ".$L_DAY[$to_day];
	if ($to_hour || $to_minute || $to_second) {
		$kkn_to .= "　".$L_HOUR[$to_hour]." ： ".$L_MINUTE[$to_minute]." ： ".$L_MINUTE[$to_second];
	}

	$mskm_kkn_from = $L_YEAR[$mskm_from_year]." / ".$L_MONTH[$mskm_from_month]." / ".$L_DAY[$mskm_from_day];
	$mskm_kkn_to = $L_YEAR[$mskm_to_year]." / ".$L_MONTH[$mskm_to_month]." / ".$L_DAY[$mskm_to_day];

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	//	統合サーバーチェック
	//	統合DB接続
    $connect_db_total = new connect_db();
    $connect_db_total->set_db($HEDATA);
    $ERROR = $connect_db_total->set_connect_db();
    if ($ERROR) {
    	return $ERROR;
    }

	// 受講生徒存在チェック
	if (MODE == "削除") {
		$cnt = 0;
		$test_group_id = $connect_db_total->real_escape($test_group_id);
		// del start 2020/09/22 yoshizawa テスト標準化開発
		// $sql  = "select count(*) cnt from tb_stu_gtest_mskm where mk_flg='0'";
		// $sql .= " and test_group_id='$test_group_id'";
		// del end 2020/09/22 yoshizawa テスト標準化開発
		// add start 2020/09/22 yoshizawa テスト標準化開発
		// 数学検定の申し込み情報はtb_stu_mskm_crsに保持いたします。
		$sql  = "select count(*) cnt from tb_stu_mskm_crs where mk_flg='0'";
		$sql .= " and test_group_id='$test_group_id'";
		// add end 2020/09/22 yoshizawa テスト標準化開発
		if ($result = $connect_db_total->query($sql)) {
			$list = $connect_db_total->fetch_assoc($result);
			$cnt = $list['cnt'];
			$connect_db_total->close();
		}
		if ($cnt > '0') {
			$tougo_del_flg = 1;
		} elseif ($error_flg = check_user_bunsan_db($test_group_id, $i_msg, $ERROR)) {
			$tougo_del_flg = 1;
		}
		if ($tougo_del_flg == 1) {
			$html = "<br>\n";
			$html .= "生徒受講済の為、削除不可です。<br>\n";
		}
	}

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_GROUP_FORM);

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$test_group_id) { $test_group_id = "---"; }
	$INPUTS[MTGROUPID] 		= array('result'=>'plane','value'=>$test_group_id);
	$INPUTS[MTGROUPNAME] 		= array('result'=>'plane','value'=>$test_group_name);
	$INPUTS[MTCLASSID] 			= array('result'=>'plane','value'=>$L_MATH_TEST_CLASS[$class_id]);
	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS[MSKMKKNFROM] 		= array('result'=>'plane','value'=>$mskm_kkn_from);
	$INPUTS[MSKMKKNTO] 			= array('result'=>'plane','value'=>$mskm_kkn_to);
	$INPUTS[SRVCCD] 			= array('result'=>'plane','value'=>$srvc_cd);// add hirose 2020/09/11 テスト標準化開発
	$INPUTS[SYSICRSCD] 			= array('result'=>'plane','value'=>$sysi_crs_cd);
	$INPUTS[USRBKO] 		= array('result'=>'plane','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	if ($tougo_del_flg != 1) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
		$html .= $HIDDEN;
		$html .= "<input type=\"submit\" value=\"".$button."\">\n";
		$html .= "</form>";
	}
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	}
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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	$INSERT_DATA = array();

	// 年リスト
	$L_YEAR = year_list();

	$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
	if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
		$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
	}
	// update oda 2015/11/04 練習ドリルの場合、受験期間（終了）がクリアされてしまうので、条件を外した
//	if ($_POST['class_id'] != "R1") {
		$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
		if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
			$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
		}
//	} else {
//		$kkn_to = "0000-00-00 00:00:00";
//	}

	$mskm_kkn_from = "NULL";
	if ($_POST['mskm_from_year'] && $_POST['mskm_from_month'] && $_POST['mskm_from_day']) {
		$mskm_kkn_from = $L_YEAR[$_POST['mskm_from_year']]."-".$L_MONTH[$_POST['mskm_from_month']]."-".$L_DAY[$_POST['mskm_from_day']];
	}
	$mskm_kkn_to = "NULL";
	if ($_POST['mskm_to_year'] && $_POST['mskm_to_month'] && $_POST['mskm_to_day']) {
		$mskm_kkn_to = $L_YEAR[$_POST['mskm_to_year']]."-".$L_MONTH[$_POST['mskm_to_month']]."-".$L_DAY[$_POST['mskm_to_day']];
	}

	$jyko_crs_cd = "NULL";
	if ($_POST['jyko_crs_cd']) {
		$jyko_crs_cd = $_POST['jyko_crs_cd'];
	}

	// add start hirose 2020/09/12 テスト標準化開発
	$srvc_cd = "NULL";
	if ($_POST['srvc_cd']) {
		$srvc_cd = $_POST['srvc_cd'];
	}
	// add end hirose 2020/09/12 テスト標準化開発

	$sysi_crs_cd = "NULL";
	if ($_POST['sysi_crs_cd']) {
		$sysi_crs_cd = $_POST['sysi_crs_cd'];
	}

	$INSERT_DATA[class_id] 		= $_POST['class_id'];
	$INSERT_DATA[test_group_name] 	= $_POST['test_group_name'];
	$INSERT_DATA[kkn_from]		 	= $kkn_from;
	$INSERT_DATA[kkn_to]		 	= $kkn_to;
	$INSERT_DATA[mskm_kkn_from]	 	= $mskm_kkn_from;	//	add ookawara 2011/10/19
	$INSERT_DATA[mskm_kkn_to]	 	= $mskm_kkn_to;	//	add ookawara 2011/10/19
	// add start hirose 2020/09/11 テスト標準化開発
	// $INSERT_DATA[srvc_cd]	 	= "STEST";
	$INSERT_DATA[srvc_cd]		 	= $srvc_cd;
	// add end hirose 2020/09/11 テスト標準化開発
	$INSERT_DATA[jyko_crs_cd]	 	= 'NULL';	//	add ookawara 2011/10/19
	$INSERT_DATA[sysi_crs_cd]	 	= $sysi_crs_cd;
	$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_BOOK_GROUP,$INSERT_DATA);

	$new_id = $cdb->insert_id();
	$UPDATE_DATA['disp_sort'] 		= $new_id;
	$where = " WHERE test_group_id='".$new_id."'";
	$ERROR = $cdb->update(T_MS_BOOK_GROUP,$UPDATE_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	return $ERROR;
}


/**
 * DB更新・削除 処理 グループ　修正・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	$test_type5 = new TestStdCfgType5($GLOBALS['cdb']);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	//年リスト
	$L_YEAR = year_list();

	if (MODE == "詳細") {
		$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
		if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
			$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
		}
		$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
		if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
			$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
		}

		$mskm_kkn_from = "NULL";
		if ($_POST['mskm_from_year'] && $_POST['mskm_from_month'] && $_POST['mskm_from_day']) {
			$mskm_kkn_from = $L_YEAR[$_POST['mskm_from_year']]."-".$L_MONTH[$_POST['mskm_from_month']]."-".$L_DAY[$_POST['mskm_from_day']];
		}
		$mskm_kkn_to = "NULL";
		if ($_POST['mskm_to_year'] && $_POST['mskm_to_month'] && $_POST['mskm_to_day']) {
			$mskm_kkn_to = $L_YEAR[$_POST['mskm_to_year']]."-".$L_MONTH[$_POST['mskm_to_month']]."-".$L_DAY[$_POST['mskm_to_day']];
		}

		// add start hirose 2020/09/12 テスト標準化開発
		$srvc_cd = "NULL";
		if ($_POST['srvc_cd']) {
			$srvc_cd = $_POST['srvc_cd'];
		}
		// add end hirose 2020/09/12 テスト標準化開発

		$jyko_crs_cd = "NULL";
		$sysi_crs_cd = "NULL";
		if ($_POST['sysi_crs_cd']) {
			$sysi_crs_cd = $_POST['sysi_crs_cd'];
		}

		$INSERT_DATA[class_id] 	= $_POST['class_id'];
		$INSERT_DATA[test_group_name]		 	= $_POST['test_group_name'];
		$INSERT_DATA[kkn_from]		 	= $kkn_from;
		$INSERT_DATA[kkn_to]		 	= $kkn_to;
		$INSERT_DATA[mskm_kkn_from]	 	= $mskm_kkn_from;
		$INSERT_DATA[mskm_kkn_to]	 	= $mskm_kkn_to;
		// add start hirose 2020/09/11 テスト標準化開発
		// $INSERT_DATA[srvc_cd]	 	= "STEST";
		$INSERT_DATA[srvc_cd]		 	= $srvc_cd;
		// add ens hirose 2020/09/11 テスト標準化開発
		$INSERT_DATA[jyko_crs_cd]	 	= $jyko_crs_cd;
		$INSERT_DATA[sysi_crs_cd]	 	= $sysi_crs_cd;
		$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
		$INSERT_DATA[display] 			= $_POST['display'];
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
	}
	$where = " WHERE test_group_id='".$_POST['test_group_id']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * MS_TEST_GROUPを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	// 選択した対象の情報取得
	$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
			" WHERE test_group_id='".$_POST['test_group_id']."'".
			// upd start hirose 2020/09/11 テスト標準化開発
			// " AND srvc_cd = 'STEST' ".
			" AND class_id > '' ".
			// upd end hirose 2020/09/11 テスト標準化開発
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_group_id = $list['test_group_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_test_group_id || !$m_disp_sort) { $ERROR[] = "移動するグループ情報が取得できません。"; }

	// 変更対象の情報取得
	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
				 " WHERE mk_flg='0'".
				 // upd start hirose 2020/09/11 テスト標準化開発
				 //  " AND srvc_cd = 'STEST' ".
				 " AND class_id > '' ".
				 // upd end hirose 2020/09/11 テスト標準化開発
				 " AND disp_sort<'".$m_disp_sort."'".
				 " ORDER BY disp_sort DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_group_id = $list['test_group_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_test_group_id || !$c_disp_sort) { $ERROR[] = "移動されるグループ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $c_disp_sort;
		//$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE test_group_id='".$m_test_group_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $m_disp_sort;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE test_group_id='".$c_test_group_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * MS_TEST_GROUPを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
			" WHERE test_group_id='".$_POST['test_group_id']."'".
			// upd start hirose 2020/09/11 テスト標準化開発
			// " AND srvc_cd = 'STEST'".
			" AND class_id > '' ".
			// upd end hirose 2020/09/11 テスト標準化開発
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_group_id = $list['test_group_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_test_group_id || !$m_disp_sort) { $ERROR[] = "移動するグループ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
				" WHERE mk_flg='0'".
				  // add start hirose 2020/09/11 テスト標準化開発
				  //   " AND srvc_cd = 'STEST'".
				  " AND class_id > '' ".
				  // add end hirose 2020/09/11 テスト標準化開発
				 " AND disp_sort>'".$m_disp_sort."'".
				 " ORDER BY disp_sort LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_group_id = $list['test_group_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_test_group_id || !$c_disp_sort) { $ERROR[] = "移動されるグループ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $c_disp_sort;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE test_group_id='".$m_test_group_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $m_disp_sort;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE test_group_id='".$c_test_group_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * グループ　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['ms_test_group_math'],1);
	if ($ERROR) { return $ERROR; }
	$filename = "ms_test_group_math.csv";

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
 * グループ　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $L_CSV_COLUMN
 * @param $head_mode='1'
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

	$sql  = "SELECT ".
		"ms_test_group.test_group_id,".
		"ms_test_group.class_id,".
		"ms_test_group.test_group_name,".
		"ms_test_group.kkn_from,".
		"ms_test_group.kkn_to,".
		"ms_test_group.disp_sort,".
		"ms_test_group.mskm_kkn_from,".
		"ms_test_group.mskm_kkn_to,".
		"ms_test_group.srvc_cd,". // add hirose 2020/09/11 テスト標準化開発
		"ms_test_group.sysi_crs_cd,".
		"ms_test_group.display ".
		" FROM " . T_MS_BOOK_GROUP . " ms_test_group" .
		" WHERE ms_test_group.mk_flg='0'".
		//update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
		//" AND ms_test_group.srvc_cd = 'STEST';";
		// upd start hirose 2020/09/11 テスト標準化開発
		// " AND ms_test_group.srvc_cd = 'STEST'".
		" AND ms_test_group.class_id > '' ".
		// upd end hirose 2020/09/11 テスト標準化開発
		" ORDER BY ms_test_group.disp_sort". //表示順にソート
		" ;";
		//update end   kimura 2017/12/19

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$csv_line .= $list[$key].",";
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

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

	return array($csv_line,$ERROR);
}


/**
 * グループ　csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	// global $L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発
	global $HEDATA;

	$ERROR = array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "数学検定グループファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "数学検定グループファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "数学検定グループファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	//	統合DB接続
    $connect_db_total = new connect_db();
    $connect_db_total->set_db($HEDATA);
    $ERROR = $connect_db_total->set_connect_db();

    if ($ERROR) {
    	return $ERROR;
    }

	$ERROR = array();
	//登録数学検定グループ読込
	$L_IMPORT_LINE = file($file_tmp_name);
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

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
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode(",",$import_line);
		if (!is_array($L_VALUE)) {
			$ERROR[] = $i."行目のcsv入力値が不正です。<br>";
			continue;
		}
		foreach ($L_VALUE as $key => $val) {
			if (!$val) { continue; }
			$val = preg_replace("/^\"|\"$/","",$val);
			$val = trim($val);
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );
			if ( $code != 'UTF-8' ) {
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//--------------------------------------------------

			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			$val = trim($val);	//	add ookawara 2011/10/24
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}

		$CHECK_DATA['kkn_from'] = str_replace("/","-",$CHECK_DATA['kkn_from']);
		$CHECK_DATA['kkn_to'] = str_replace("/","-",$CHECK_DATA['kkn_to']);
		$CHECK_DATA['mskm_kkn_from'] = str_replace("/","-",$CHECK_DATA['mskm_kkn_from']);
		$CHECK_DATA['mskm_kkn_to'] = str_replace("/","-",$CHECK_DATA['mskm_kkn_to']);
		if (!$CHECK_DATA['display']) { $CHECK_DATA['display'] = 1; }

		$sql  = "SELECT ".
			"ms_test_group.test_group_id,".
			"ms_test_group.test_group_name,".
			"ms_test_group.disp_sort".
			" FROM " . T_MS_BOOK_GROUP . " ms_test_group" .
			" WHERE ms_test_group.test_group_id='".$CHECK_DATA['test_group_id']."'".
			// upd start hirose 2020/09/11 テスト標準化開発
			// " AND ms_test_group.srvc_cd = 'STEST'".
			" AND ms_test_group.class_id > '' ".
			// upd end hirose 2020/09/11 テスト標準化開発
			" AND ms_test_group.mk_flg='0'";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) { $ins_mode = "upd"; } else { $ins_mode = "add"; }

		//データチェック
		// upd start hirose 2020/09/12 テスト標準化開発
		// $DATA_ERROR[$i] = check_data($CHECK_DATA, $ins_mode, $i, $DISP_SORT);
		$exclusion_list = [];
		$DATA_ERROR[$i] = check_data($CHECK_DATA, $ins_mode, $i, $DISP_SORT,$exclusion_list);
		//すでに申し込まれている系のエラー時、そのCDの整合性のエラーを出す必要はないので削除
		if(!empty($exclusion_list)){
			foreach($exclusion_list as $key => $v){
				unset($CHECK_DATA[$key]);
			}
		}
		// upd end hirose 2020/09/12 テスト標準化開発

		//	統合サーバーでのエラーチェック
		he_check_data($CHECK_DATA, $i, $connect_db_total, $DATA_ERROR[$i]);
		//$connect_db_total->close();

		if ($DATA_ERROR[$i]) {
			$DATA_ERROR[$i][] = $i."行目 上記入力エラーでスキップしました。";
			continue;
		}

		$INSERT_DATA = $CHECK_DATA;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
			// $INSERT_DATA[srvc_cd] 		= "STEST";// del hirose 2020/09/11 テスト標準化開発
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			unset($INSERT_DATA['disp_sort']);	//	add ookawara 2011/10/27
			$SYS_ERROR[$i] = $cdb->insert(T_MS_BOOK_GROUP,$INSERT_DATA);
			if ($SYS_ERROR[$i]) {
				$SYS_ERROR[$i][] = $i."行目 上記入力エラーでスキップしました。";
				continue;
			}

			$test_group_id = $cdb->insert_id();

			unset($UPDATE_DATA);
			$UPDATE_DATA['disp_sort'] 		= $test_group_id;
			$where = " WHERE test_group_id='".$test_group_id."'";
			$SYS_ERROR[$i] = $cdb->update(T_MS_BOOK_GROUP,$UPDATE_DATA,$where);
			if ($SYS_ERROR[$i]) {
				$SYS_ERROR[$i][] = $i."行目 上記入力エラーでスキップしました。";
				continue;
			}
		} else {
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			$test_group_id = $INSERT_DATA['test_group_id'];
			$where = " WHERE test_group_id='".$test_group_id."' LIMIT 1;";
			unset($INSERT_DATA['test_group_id']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_BOOK_GROUP,$INSERT_DATA,$where);
			if ($SYS_ERROR[$i]) {
				$SYS_ERROR[$i][] = $i."行目 上記入力エラーでスキップしました。";
				continue;
			}
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
	}

	//各エラー結合
	if(is_array($DATA_ERROR)) {
		foreach($DATA_ERROR as $key => $val) {
			if (empty($DATA_ERROR[$key])) { continue; }
			$ERROR = array_merge($ERROR,$DATA_ERROR[$key]);
		}
	}
	if(is_array($SYS_ERROR)) {
		foreach($SYS_ERROR as $key => $val) {
			if (empty($SYS_ERROR[$key])) { continue; }
			$ERROR = array_merge($ERROR,$SYS_ERROR[$key]);
		}
	}
	if (!$ERROR) { $html = "<br>正常に全て登録が完了しました。"; }
	else { $html = "<br>エラーのある行数以外の登録が完了しました。"; }

	return array($html,$ERROR);
}


/**
 * グループ　csvインポートチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA
 * @param string $ins_mode
 * @param integer $line_num
 * @param array &$DISP_SORT
 * @param array &$exclusion_list 以降のプログラムでエラーチェックしないキー一覧
 * @return array エラーの場合
 */
// upd start hirose 2020/09/12 テスト標準化開発
// function check_data($CHECK_DATA, $ins_mode, $line_num, &$DISP_SORT) {
function check_data($CHECK_DATA, $ins_mode, $line_num, &$DISP_SORT,&$exclusion_list) {
// upd end hirose 2020/09/12 テスト標準化開発

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MATH_TEST_CLASS,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発

	//年リスト
	$L_YEAR = year_list();

	if ($CHECK_DATA['test_group_id'] && preg_match("/[^0-9]/", $CHECK_DATA['test_group_id'])) {
		$ERROR[] = $line_num."行目 グループIDが不正です。";
	}

	if (!$CHECK_DATA['test_group_name']) {
		$ERROR[] = $line_num."行目 グループ名が未入力です。";
	} elseif (strlen($CHECK_DATA['test_group_name']) > 80) {
		$ERROR[] = $line_num."行目 グループ名が長すぎます。半角80文字以内で記述して下さい。";
	}
 	//else {
	//	if ($ins_mode == "add") {
	//		$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
	//				" WHERE mk_flg='0'".
	//				" AND srvc_cd = 'STEST'".
	//				" AND class_id='".$CHECK_DATA['class_id']."'".
	//				" AND test_group_name='".$CHECK_DATA['test_group_name']."'";
	//	} else {
	//		$sql  = "SELECT * FROM " . T_MS_BOOK_GROUP .
	//				" WHERE mk_flg='0'".
	//				" AND srvc_cd = 'STEST'".
	//				" AND class_id='".$CHECK_DATA['class_id']."'".
	//				" AND test_group_id!='".$CHECK_DATA['test_group_id']."'".
	//				" AND test_group_name='".$CHECK_DATA['test_group_name']."'";
	//	}
	//	if ($result = $cdb->query($sql)) {
	//		$count = $cdb->num_rows($result);
	//	}
	//	if ($count > 0) { $ERROR[] = $line_num."行目 入力されたグループ名は既に登録されております。"; }
	//}

	$class_r_flg = 0;
	if (!$CHECK_DATA['class_id']) {
		$ERROR[] = $line_num."行目 級IDが未入力です。";
	} else {
		if(array_key_exists($CHECK_DATA['class_id'],$L_MATH_TEST_CLASS) == FALSE){
			$ERROR[] = $line_num."行目 級IDが不正です。";
		}
		if (preg_match("/R+[0-9]/", $CHECK_DATA['class_id'])) {
			// 練習ドリルの場合にはフラグを立てる
			$class_r_flg = 1;
		}
	}

	list($from_days,$from_time) = explode(" ",$CHECK_DATA['kkn_from']);
	list($from_year, $from_month, $from_day) = explode("-",$from_days);
	$from_year = $from_year * 1;
	$from_month = $from_month * 1;
	$from_day = $from_day * 1;
	list($from_hour,$from_minute,$from_second) = explode(":",$from_time);
	list($to_days,$to_time) = explode(" ",$CHECK_DATA['kkn_to']);
	list($to_year,$to_month,$to_day) = explode("-",$to_days);
	$to_year = $to_year * 1;
	$to_month = $to_month * 1;
	$to_day = $to_day * 1;
	list($to_hour,$to_minute,$to_second) = explode(":",$to_time);

	if (!$from_year || !$from_month || !$from_day) {
		$ERROR[] = $line_num."行目 受験期間 開始が未入力です。";
	} else {
		if (checkdate($L_MONTH[$from_month],$L_DAY[$from_day],$L_YEAR[$from_year])) {
			$from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$from_month],$L_DAY[$from_day],$L_YEAR[$from_year]));
		} else {
			$ERROR[] = $line_num."行目 受験期間 開始が不正です。選択された日付は存在しません。";
		}
	}

	if (!$to_year || !$to_month || !$to_day) {
		$ERROR[] = $line_num."行目 受験期間 終了が未入力です。";
	} else {
		if (checkdate($L_MONTH[$to_month],$L_DAY[$to_day],$L_YEAR[$to_year])) {
			$to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$to_month],$L_DAY[$to_day],$L_YEAR[$to_year]));
			if ($from_day > $to_day) {
				$ERROR[] = $line_num."行目 受験期間 終了が不正です。入力された日付が受験期間 開始より過去になっています。";
			}
		} else {
			$ERROR[] = $line_num."行目 受験期間 終了が不正です。選択された日付は存在しません。";
		}
	}

	//	申込期間 開始
	$mskm_from_flg = 0;
	list($mskm_from_year,$mskm_from_month,$mskm_from_day) = explode("-",$CHECK_DATA['mskm_kkn_from']);
	$mskm_from_year = $mskm_from_year * 1;
	$mskm_from_month = $mskm_from_month * 1;
	$mskm_from_day = $mskm_from_day * 1;
	if ($class_r_flg == 1 && ($mskm_from_year || $mskm_from_month || $mskm_from_day)) {
			$ERROR[] = $line_num."行目 申込期間 開始が不正です。練習ドリル選択時は申込期間を設定しないでください。";
	} elseif ($mskm_from_year || $mskm_from_month || $mskm_from_day) {
		$mskm_from_flg = 1;
		if ($mskm_from_year && $mskm_from_month && $mskm_from_day) {
			if (checkdate($L_MONTH[$mskm_from_month],$L_DAY[$mskm_from_day],$L_YEAR[$mskm_from_year])) {
				$mskm_from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$mskm_from_month],$L_DAY[$mskm_from_day],$L_YEAR[$mskm_from_year]));
				if ($mskm_from_day && $from_day && $mskm_from_day > $from_day) {
					$ERROR[] = $line_num."行目 申込期間 開始が不正です。受験期間 開始後に申込期間 開始となっております。";
				}
			} else {
				$ERROR[] = $line_num."行目 申込期間 開始が不正です。選択された日付は存在しません。";
			}
		} else {
			$ERROR[] = $line_num."行目 申込期間 開始が不正です。日付を確認してください。";
		}
	} elseif ($class_r_flg != 1) {
		$ERROR[] = $line_num."行目 申込期間 開始が入力されておりません。";
	}

	//	申込期間 終了
	list($mskm_to_year,$mskm_to_month,$mskm_to_day) = explode("-",$CHECK_DATA['mskm_kkn_to']);
	$mskm_to_year = $mskm_to_year * 1;
	$mskm_to_month = $mskm_to_month * 1;
	$mskm_to_day = $mskm_to_day * 1;
	if ($class_r_flg == 1 && ($mskm_to_year || $mskm_to_month || $mskm_to_day)) {
		$ERROR[] = $line_num."行目 申込期間 終了が不正です。練習ドリル選択時は申込期間を設定しないでください。";
	} elseif ($mskm_from_flg == 1) {
		list($mskm_to_year,$mskm_to_month,$mskm_to_day) = explode("-",$CHECK_DATA['mskm_kkn_to']);
		$mskm_to_year = $mskm_to_year * 1;
		$mskm_to_month = $mskm_to_month * 1;
		$mskm_to_day = $mskm_to_day * 1;

		if (!$mskm_to_year || !$mskm_to_month || !$mskm_to_day) {
			$ERROR[] = $line_num."行目 申込期間 終了が未入力です。";
		} else {
			if (checkdate($L_MONTH[$mskm_to_month],$L_DAY[$mskm_to_day],$L_YEAR[$mskm_to_year])) {
				$mskm_to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$mskm_to_month],$L_DAY[$mskm_to_day],$L_YEAR[$mskm_to_year]));
				if ($mskm_to_day > $to_day) {
					$ERROR[] = $line_num."行目 申込期間 終了が不正です。受験期間 終了後に申込期間 終了となっております。";
				}
			} else {
				$ERROR[] = $line_num."行目 申込期間 終了が不正です。入力された日付は存在しません。";
			}
		}
	}

	// add start hirose 2020/09/12 テスト標準化開発
	$check_array = ['srvc_cd'=> $CHECK_DATA['srvc_cd'],
					'jyko_crs_cd'=> $CHECK_DATA['jyko_crs_cd'],
					'sysi_crs_cd'=> $CHECK_DATA['sysi_crs_cd']
					];
	$check_diff_array = check_change_cd($CHECK_DATA['test_group_id'],$check_array);
	// add end hirose 2020/09/12 テスト標準化開発

	// add start hirose 2020/09/11 テスト標準化開発
	$CHECK_DATA['srvc_cd'] = mb_convert_kana($CHECK_DATA['srvc_cd'], 'as', 'UTF-8');
	$CHECK_DATA['srvc_cd'] = trim($CHECK_DATA['srvc_cd']);
	if(!empty($check_diff_array['srvc_cd'])){
		$ERROR[] = $line_num."行目 ".$check_diff_array['srvc_cd'];
		$exclusion_list['srvc_cd'] = true;
		unset($CHECK_DATA['srvc_cd']);
	}elseif(!$CHECK_DATA['srvc_cd']){
		$ERROR[] = $line_num."サービスCDが未入力です。";
		unset($CHECK_DATA['srvc_cd']);
	}elseif(!preg_match('/^[a-zA-Z0-9]+$/',$CHECK_DATA['srvc_cd'])){
		$ERROR[] = $line_num."サービスCDは半角英数字で記入してください";
		unset($CHECK_DATA['srvc_cd']);
	} elseif (strlen($CHECK_DATA['srvc_cd']) > 10) {
		$ERROR[] = $line_num."サービスCDが長すぎます。半角10文字以内で記述して下さい。";
		unset($CHECK_DATA['srvc_cd']);
	}
	// add end hirose 2020/09/11 テスト標準化開発

	//	詳細コースCD
	$CHECK_DATA['sysi_crs_cd'] = mb_convert_kana($CHECK_DATA['sysi_crs_cd'], 'as', 'UTF-8');
	$CHECK_DATA['sysi_crs_cd'] = trim($CHECK_DATA['sysi_crs_cd']);
	// 練習
	if ($class_r_flg == 1 && $CHECK_DATA['sysi_crs_cd']) {
		$ERROR[] = $line_num."行目 練習ドリル選択時は詳細コースCDを入力しないでください。";
		unset($CHECK_DATA['sysi_crs_cd']);
	} else if ($mskm_from_flg == 1) {
		// add start hirose 2020/09/12 テスト標準化開発
		if(!empty($check_diff_array['sysi_crs_cd'])){
			$ERROR[] = $line_num."行目 ".$check_diff_array['sysi_crs_cd'];
			$exclusion_list['sysi_crs_cd'] = true;
			unset($CHECK_DATA['sysi_crs_cd']);
		}else
		// add end hirose 2020/09/12 テスト標準化開発
		if (!$CHECK_DATA['sysi_crs_cd']) {
			$ERROR[] = $line_num."行目 詳細コースCDが未入力です。";
			unset($CHECK_DATA['sysi_crs_cd']);
		} elseif (strlen($CHECK_DATA['sysi_crs_cd']) > 10) {
			$ERROR[] = $line_num."行目 詳細コースCDが長すぎます。半角10文字以内で記述して下さい。";
			unset($CHECK_DATA['sysi_crs_cd']);
		}
	}

	if (!$CHECK_DATA['display']) {
		$ERROR[] = $line_num."行目 表示・非表示が未入力です。";
	} else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = $line_num."行目 表示・非表示は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = $line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}

	if ($CHECK_DATA['test_group_id'] && !$CHECK_DATA['disp_sort']) {
		$ERROR[] = $line_num."行目 表示順が未入力です。";
	} elseif (!$CHECK_DATA['test_group_id'] && $CHECK_DATA['disp_sort']) {
		$ERROR[] = $line_num."行目 表示順は新規登録時、値の指定をしないでください。";
	} elseif ($CHECK_DATA['disp_sort']) {
		if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
			$ERROR[] = $line_num."行目 表示順は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['disp_sort'] < 1) {
			$ERROR[] = $line_num."行目 表示順は1以上の数字を指定してください。";
		} else {
			$max = 0;
			$sql  = "SELECT MAX(test_group_id) AS max FROM ".T_MS_BOOK_GROUP.
					" WHERE mk_flg='0'".
					// upd start hirose 2020/09/11 テスト標準化開発
					// " AND srvc_cd = 'STEST';";
					" AND class_id > '' ;";
					// upd end hirose 2020/09/11 テスト標準化開発
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$max = $list['max'];
			}
			if ($max < $CHECK_DATA['disp_sort']) {
				$ERROR[] = $line_num."行目 表示順の最大値は、".$max."以下で設定してください。";
			} else {
				if ($DISP_SORT) {
					if (array_search($CHECK_DATA['disp_sort'], $DISP_SORT) !== FALSE) {
						$ERROR[] = $line_num."行目 表示順が他のグループIDで利用されております。";
					} else {
						$DISP_SORT[] = $CHECK_DATA['disp_sort'];
					}
				} else {
					$DISP_SORT[] = $CHECK_DATA['disp_sort'];
				}
			}
		}
	}

	return $ERROR;
}

/**
 * コース一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function course_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_COURSE. " WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
		}
	}
	return $L_COURSE_LIST;
}

/**
 * 年一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function year_list() {

	$L_YEAR = array();
	$L_YEAR[] = "----";

	$start_year = 2011;
	$end_year = date("Y") + 3;
	for ($i=$start_year; $i<$end_year; $i++) {
		$L_YEAR[$i] = $i;
	}
	$L_YEAR['2999'] = 2999;

	return $L_YEAR;
}


/**
 * 統合サーバーでのエラーチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA
 * @param integer $i
 * @param string $connect_db_total
 * @param array &$ERROR
 */
function he_check_data($CHECK_DATA, $i, $connect_db_total, &$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$i_msg = "";
	if ($i > 0) {
		$i_msg = $i."行目 ";
	}

	$class_id = $CHECK_DATA['class_id'];
	$srvc_cd = $CHECK_DATA['srvc_cd'];// add hirose 2020/09/11 テスト標準化開発
	$jyko_crs_cd = $CHECK_DATA['jyko_crs_cd'];
	$sysi_crs_cd = $CHECK_DATA['sysi_crs_cd'];
	$test_group_id = $CHECK_DATA['test_group_id'];

	if ($CHECK_DATA['display'] == 2 && $test_group_id > 0) {
		$sql  = "SELECT display FROM ".T_MS_BOOK_GROUP.
				" WHERE test_group_id='".$test_group_id."'".
				// upd start hirose 2020/09/11 テスト標準化開発
				// " AND srvc_cd = 'STEST'".
				" AND class_id > '' ".
				// upd end hirose 2020/09/11 テスト標準化開発
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$display = $list['display'];
		}
		if ($display == 2) { $test_group_id = 0; }
	} else {
		$test_group_id = 0;
	}

	$class_r_flg = 0;
	if (preg_match("/R+[0-9]/", $class_id)) {
		$class_r_flg = 1;
	}

	if ($class_r_flg != 1) {
		// 詳細コースコードチェック
		$sysi_error_flg = 0;
		if ($sysi_crs_cd) {
			$sysi_crs_cd = $connect_db_total->real_escape($sysi_crs_cd);
			$sql = "select count(*) cnt from ms_jyko_crs_ucwk u".
					" where sysi_crs_cd = '$sysi_crs_cd'";
			$result = $connect_db_total->query($sql);
			$list = $connect_db_total->fetch_assoc($result);
			if (!$list || $list['cnt'] == '0') {
				$ERROR[] = $i_msg."詳細コースCDの値に誤りがあります。";
				$sysi_error_flg = 1;
			}
		}
	}

	// add start hirose 2020/09/12 テスト標準化開発
	if($srvc_cd){
		//サービスコードチェック
		$srvc_cd = $connect_db_total->real_escape($srvc_cd);
		$sql = " select count(*) cnt from ms_cd_nm ".
				" where bnr_cd = '81' ".
				" and cd_value = 'STEST' ".//ここのSTESTは固定
				" and komk_cd = '".$srvc_cd."' ";

		// print $sql;
		$result = $connect_db_total->query($sql);
		$list = $connect_db_total->fetch_assoc($result);
		if (!$list || $list['cnt'] == '0') {
			$ERROR[] = $i_msg."サービスCDの値に誤りがあります。";
		}
	}
	// add end hirose 2020/09/12 テスト標準化開発

	//	登録ユーザーチェック
	if ($test_group_id > 0) {
		$cnt = 0;
		$test_group_id = $connect_db_total->real_escape($test_group_id);
		// del start 2020/09/22 yoshizawa テスト標準化開発
		// $sql  = "select count(*) cnt from tb_stu_gtest_mskm where mk_flg='0'";
		// $sql .= " and test_group_id='$test_group_id'";
		// del end 2020/09/22 yoshizawa テスト標準化開発
		// add start 2020/09/22 yoshizawa テスト標準化開発
		// 数学検定の申し込み情報はtb_stu_mskm_crsに保持いたします。
		$sql  = "select count(*) cnt from tb_stu_mskm_crs where mk_flg='0'";
		$sql .= " and test_group_id='$test_group_id'";
		// add end 2020/09/22 yoshizawa テスト標準化開発
		if ($result = $connect_db_total->query($sql)) {
			$list = $connect_db_total->fetch_assoc($result);
			$cnt = $list['cnt'];
		}
		if ($cnt > '0') {
			$tougo_del_flg = 1;
			$ERROR[] = $i_msg."生徒受講済の為、非表示に出来ません。\n";
		} else {
			check_user_bunsan_db($test_group_id, $i_msg, $ERROR);
		}
	}

}


/**
 * コア分散サーバーで既にグループの利用者がいないかチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $test_group_id
 * @param string $i_msg
 * @param array &$ERROR
 */
function check_user_bunsan_db($test_group_id, $i_msg, &$ERROR) {

	global $HCDATA;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$check_flg = 0;
	foreach ($HCDATA AS $VAL) {
		//	本番分散サーバー接続
	    $connect_db_total = new connect_db();
	    $connect_db_total->set_db($VAL);
	    $ERROR = $connect_db_total->set_connect_db();
	    if ($ERROR) {
			$ERROR[] = "分散DBに接続できませんでした。";
			return 1;
	    }
		$count = 0;
		$sql  = "SELECT count(*) AS count FROM ".T_TEST_DATA.
				" WHERE test_group_id='".$test_group_id."'".
				" AND mk_flg='0';";
		if ($result = $connect_db_total->query($sql)) {
			$list = $connect_db_total->fetch_assoc($result);
			$count = $list['count'];
		}
		if ($count > 0) {
			$check_flg = 1;
			break;
		}
	}

	if ($check_flg == 1) {
		$ERROR[] = $i_msg."生徒受講済の為、非表示に出来ません。";
		return 1;
	}
}

// add start hirose 2020/09/12 テスト標準化開発
/**
 * 登録情報と、変更情報の変更チェック
 *
 * @param [int] $test_group_id_
 * @param [array] $check_data_
 * @return array[]
 */
function check_change_cd($test_group_id_,$check_data_){

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if(!empty($test_group_id_)){
		$sql  = "SELECT ".
				" srvc_cd ".
				" ,jyko_crs_cd ".
				" ,sysi_crs_cd ".
				" FROM ".T_MS_BOOK_GROUP.
				" WHERE test_group_id='".$test_group_id_."'".
				" AND class_id > '' ".
				" LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			//もし、該当データが登録されていない場合、比較する必要がない
			if(empty($list)){ return $ERROR; }
			else{
				$srvc_cd_db = $list['srvc_cd'];
				$jyko_cd_db = $list['jyko_crs_cd'];
				$sysi_cd_db = $list['sysi_crs_cd'];

				$srvc_cd_new = "";
				if(!empty($check_data_['srvc_cd'])){
					$srvc_cd_new = mb_convert_kana($check_data_['srvc_cd'], 'as', 'UTF-8');
					$srvc_cd_new = trim($srvc_cd_new);
				}
				if($srvc_cd_db != $srvc_cd_new){
					$ERROR['srvc_cd'] = "すでに申し込みがされているテストのため、サービスCDは変更できません。";
				}

				$jyko_cd_new = "";
				if(!empty($check_data_['jyko_crs_cd'])){
					$jyko_cd_new = mb_convert_kana($check_data_['jyko_crs_cd'], 'as', 'UTF-8');
					$jyko_cd_new = trim($jyko_cd_new);
				}
				if($jyko_cd_db != $jyko_cd_new){
					$ERROR['jyko_crs_cd'] = "すでに申し込みがされているテストのため、受講コースCDは変更できません。";
				}

				$sysi_cd_new = "";
				if(!empty($check_data_['sysi_crs_cd'])){
					$sysi_cd_new = mb_convert_kana($check_data_['sysi_crs_cd'], 'as', 'UTF-8');
					$sysi_cd_new = trim($sysi_cd_new);
				}
				if($sysi_cd_db != $sysi_cd_new){
					$ERROR['sysi_crs_cd'] = "すでに申し込みがされているテストのため、詳細コースCDは変更できません。";
				}

				//それぞれの値が異なっているが、申し込み済ではない場合、変更可能なのでエラーを削除する
				if(!empty($ERROR)){
					if(!check_application_status($test_group_id_)){
						$ERROR = [];
					}
				}

			}
		}
	}
	return $ERROR;

}

/**
 * 該当テストの申し込み状態を確認
 *
 * @param [int] $test_group_id_
 * @return bool
 */
function check_application_status($test_group_id_){

	global $HEDATA;

	//	統合DB接続
    $connect_db_total = new connect_db();
    $connect_db_total->set_db($HEDATA);
    $ERROR = $connect_db_total->set_connect_db();

	$cnt = 0;
	$test_group_id = $connect_db_total->real_escape($test_group_id_);
	// del start 2020/09/22 yoshizawa テスト標準化開発
	// $sql  = "select count(*) cnt from tb_stu_gtest_mskm where mk_flg='0'";
	// $sql .= " and test_group_id='$test_group_id'";
	// del end 2020/09/22 yoshizawa テスト標準化開発
	// add start 2020/09/22 yoshizawa テスト標準化開発
	// 数学検定の申し込み情報はtb_stu_mskm_crsに保持いたします。
	$sql  = "select count(*) cnt from tb_stu_mskm_crs where mk_flg='0'";
	$sql .= " and test_group_id='$test_group_id'";
	// add end 2020/09/22 yoshizawa テスト標準化開発
	if ($result = $connect_db_total->query($sql)) {
		$list = $connect_db_total->fetch_assoc($result);
		$cnt = $list['cnt'];
	}
	$return_result = false;
	if($cnt>0){
		$return_result = true;
	}

	return $return_result;
}
/**
 * disabledに似せたCSSを作成する用の配列を取得
 *
 * @return array
 */
function read_only_array(){
	return array(
		'action' => 'readonly',
		'style' => 'background-color:#eee;',
	);
}
// add end hirose 2020/09/12 テスト標準化開発
?>
