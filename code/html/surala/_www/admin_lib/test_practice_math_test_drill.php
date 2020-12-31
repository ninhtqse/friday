<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題検証/数学検定
 *
 * 履歴
 * 2015/09/14 初期設定
 *
 * @author Azet
 */



/**
 * 数学検定HTMLを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function math_test_select_unit_view() {

	// 「表示数」setボタン押下時
	if (ACTION == "sub_course_session") {
		$ERROR = sub_setpage_session();
	}

	$html = "";
	// テストタイプ、級、採点単元プルダウン
	$html .= math_select_unit_view();
	// 問題一覧作成
	$html .= math_test_list_drill();

	return $html;
}



/**
 * テストタイプ、級、単元選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function math_select_unit_view() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_TEST_TYPE, $L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発  
	global $L_TEST_TYPE;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	$s_class_id = "select";
	$s_book_unit_id = "0";
	$s_page_view = "2";
	$s_page = 1;
	if (ACTION == "view_session") {
		if (strlen($_POST['s_class_id'])) { $_SESSION['sub_session']['s_class_id'] = $_POST['s_class_id']; }
		if (strlen($_POST['s_book_unit_id']))	{ $_SESSION['sub_session']['s_book_unit_id'] = $_POST['s_book_unit_id']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
		if (strlen($_POST['s_page']))	{ $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }
	}
	if (MODE == "set_problem_view") {
		$_SESSION['sub_session']['s_page'] = 1; 			//表示データ切り替えたらページを１にリセット
		unset($_SESSION['sub_session']['select_class_id']);	//既存問題の登録途中で、画面が切り替えられたらリセット
	}

	if ($_SESSION['sub_session']['s_class_id']) {
		$s_class_id = $_SESSION['sub_session']['s_class_id'];
		$s_book_unit_id = $_SESSION['sub_session']['s_book_unit_id'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
		$s_page = $_SESSION['sub_session']['s_page'];
	}

	//  プルダウン作成
	//テストタイプ
	$test_type_html = "";
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	//	級
	$class_html  = "";
	$selected = "";
	foreach ($L_MATH_TEST_CLASS as $key=> $val) {
		$selected = "";
		if ($s_class_id == $key) { $selected = "selected"; }
		$class_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	if($s_class_id == "select"){
		$msg_html = "級を選択してください。";
	}

	//メニュー組み立て情報
	$SEL_MENU_L = array();
	$SEL_MENU_L[0] = array('text'=>'テストタイプ', 'name'=>'test_type', 'onchange'=>'2', 'options'=>$test_type_html, 'Submit'=>'0');
	$SEL_MENU_L[1] = array('text'=>'級', 'name'=>'s_class_id', 'onchange'=>'1', 'options'=>$class_html, 'Submit'=>'0');
	ksort($SEL_MENU_L);

	//項目名,ドロップダウン
	$c_name_html = "";
	foreach ($SEL_MENU_L as $key => $SEL_MENU_ITEM_L) {
		$c_name_html .= "<td>".$SEL_MENU_ITEM_L['text']."</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<select name=\"".$SEL_MENU_ITEM_L['name']."\" ";
		$sub_session_html .= "onchange=\"submit();return false;\"";
		$sub_session_html .= ">\n".$SEL_MENU_ITEM_L['options']."</select>\n";
		if ($SEL_MENU_ITEM_L['Submit']) { $sub_session_html .= "<input type=\"submit\" value=\"Set\">\n"; }	//「Set」ボタン表示
		$sub_session_html .= "</td>\n";
	}

	$html .= "<br><div id=\"mode_menu\">\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"frmSelTtype\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= $c_name_html;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	if ($msg_html) {
		$html .= "<br>\n";
		$html .= $msg_html;
		$html .= "<br>\n";
	}

	$html .= "<br/>";
	$s_page_save = $_SESSION['sub_session']['s_page'];
	$s_select_course = $_SESSION['sub_session']['select_course'];
	$s_page_view = $_SESSION['sub_session']['s_page_view'];

	$_SESSION['sub_session'] = array();

	$_SESSION['sub_session']['s_class_id'] = $s_class_id;
	$_SESSION['sub_session']['s_book_unit_id'] = $s_book_unit_id;
	$_SESSION['sub_session']['s_page_view'] = $s_page_view;

	//表示ページ番号の情報は引き続き保持し、POSTされたページ番号があれば、それを代入する。
	if ($s_page_save > 0) {
		$_SESSION['sub_session']['s_page'] = $s_page_save;
	}
	//一覧のページ操作があった場合(ページ番号格納)
	if (strlen($_POST['s_page'])) {
		$_SESSION['sub_session']['s_page'] = $_POST['s_page'];
	}
	if ($s_select_course) {
		$_SESSION['sub_session']['select_course'] = $s_select_course;
	}

	return $html;

}

/**
 * POSTに対してsession設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_setpage_session() {
	unset($_SESSION['sub_session']['s_page_view']);
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view'];
	}
	return;
}

/**
 * 数学検定問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function math_test_list_drill() {	//テストタイプ選択後、級を選択したら登録リスト表示

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_PAGE_VIEW,$L_FORM_TYPE,$L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_PAGE_VIEW,$L_FORM_TYPE;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発

	unset($_SESSION['sub_session']['select_class_id']);

	$html = "";
	$page = 1;
	if ($_SESSION['sub_session']) {
		$s_class_id = $_SESSION['sub_session']['s_class_id'];
		$s_book_unit_id = $_SESSION['sub_session']['s_book_unit_id'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
		$page = $_SESSION['sub_session']['s_page'];
	}

	//テストタイプが選択されていたら一覧表示
	if ($_SESSION['t_practice']['test_type'] == 5 && $s_class_id != "select") {
		foreach($L_MATH_TEST_CLASS as $key => $val) {
			if ($key == $s_class_id){
				$class_name = $val;
			}
		}

		if ($s_class_id != "select"){
			$html .="<br><strong>級: ".$class_name."</strong><br>\n";
		}

		//登録されている問題リスト表示
		$and = "";
		if ($s_book_unit_id != 0){
			// book_unit_idの存在をチェック
			$sql  = "SELECT bu.book_unit_id ".
					 " FROM ms_book_unit bu".
					 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id".
					 " AND mtbul.mk_flg = '0'".
					 " AND mtbul.class_id = '".$s_class_id."'".
					 " AND bu.book_unit_id = '".$s_book_unit_id."'".
					 " WHERE bu.mk_flg = '0';";
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			if ($book_unit_count > 0){
				$and = " AND butp.book_unit_id = '".$s_book_unit_id."'" ;
			}
		}
		$sql = " SELECT ". //すらら問題
				 "  butp.problem_num, ".
				 "  butp.problem_table_type, ".
				 "  p.form_type, ".
				 "  mpa.standard_time ".
				 " FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp ".
				 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$s_class_id."'".
				 " LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num = butp.problem_num".
				 " LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg = '0' AND mpa.block_num = p.block_num".
				 " WHERE butp.mk_flg='0'".
				 "   AND butp.problem_table_type='1'".
				 " GROUP BY problem_num".
				 " UNION ALL ".
				 " SELECT ". //テスト問題
				 "  butp.problem_num, ".
				 "  butp.problem_table_type, ".
				 "  mtp.form_type, ".
				 "  mtp.standard_time ".
				 " FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$s_class_id."'".
				 " LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg = '0' AND mtp.problem_num = butp.problem_num".
				 " WHERE butp.mk_flg='0'".
				 "   AND butp.problem_table_type='2'".
				 " GROUP BY problem_num ".
	 			 " ORDER BY problem_num ";
//echo $sql;

		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count == 0) {
			$html .= "<br>\n";
			$html .= "問題は登録されておりません。<br>\n";
			return $html;
		} else {
			if (!isset($_SESSION['sub_session']['s_page_view'])) {
				$_SESSION['sub_session']['s_page_view'] = 1;
			}
			//表示数の選択
			if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
				$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
			} else {
				$page_view = $L_PAGE_VIEW[0];
			}
			$max_page = ceil($count/$page_view);

			if ($_SESSION['sub_session']['s_page']) {
				$page = $_SESSION['sub_session']['s_page'];
			} else {
				$page = 1;
			}

			if ($page > $max_page) { $page = $max_page; }
			$start = ($page - 1) * $page_view;
			$next = $page + 1;
			$back = $page - 1;

			$sql .= " LIMIT ".$start.",".$page_view.";";

			//ページ数
			foreach ($L_PAGE_VIEW as $key => $val){
				if ($_SESSION['sub_session']['s_page_view'] == $key) {
					$sel = " selected";
				} else {
					$sel = "";
				}
				$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
			}
			$sub_session_html = "";
			
			
			//add start hirose 2018/05/01 管理画面手書き切り替え機能追加
			if(isset($_COOKIE["tegaki_flag"])){
				$_SESSION['TEGAKI_FLAG'] = $_COOKIE["tegaki_flag"];
			}else{
				$_SESSION['TEGAKI_FLAG'] = 1;
			}
			$check = "checked";
			if($_SESSION['TEGAKI_FLAG'] == 0){
				$check = "";
			}
			$onchenge = "onclick=\"this.blur(); this.focus();\" onchange=\"update_tegaki_flg(this,'frmSelTtype');\"";
			$sub_session_html .= "<div class=\"tegaki-switch\">";
			$sub_session_html .= "<label>";
			$sub_session_html .= "<input type=\"checkbox\" name=\"tegaki_control\" ".$check." ".$onchenge." class=\"tegaki-check\"><span class=\"swith-content\"></span><span class=\"swith-button\"></span>";
			$sub_session_html .= "</label>";
			$sub_session_html .= "</div>";
			//add end hirose 2018/05/01 管理画面手書き切り替え機能追加
			
			
			$sub_session_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
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

			$html .= $msg;

			//問題リスト表示
			if ($result = $cdb->query($sql)) {
				$html .= "<br>\n";
				$html .= "<div style=\"float:left;\">登録問題数(".$count."):PAGE[".$page."/".$max_page."]</div>\n";

				if ($back > 0) {
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_list_session\">\n";
					$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
					$html .= "<input type=\"submit\" value=\"前のページ\">";
					$html .= "</form>";
				}
				if ($page < $max_page) {
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
					$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_list_session\">\n";
					$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
					$html .= "<input type=\"submit\" value=\"次のページ\">";
					$html .= "</form>";
				}
				$html .= "<br style=\"clear:left;\">";
				$html .= "<table class=\"course_form\">\n";
				$html .= "<tr class=\"course_form_menu\">\n";
				$html .= "<th>問題管理番号</th>\n";
				$html .= "<th>問題種類</th>\n";
				$html .= "<th>出題形式</th>\n";
				$html .= "<th>回答目安時間</th>\n";
				$html .= "<th>出題単元ID</th>\n";
				$html .= "<th>採点単元ID</th>\n";
				$html .= "<th>確認</th>\n";
				$html .= "</tr>\n";
				$j=$start;
				while ($list = $cdb->fetch_assoc($result)) {
					$j++;
					if ($list['problem_table_type'] == 1) {
						$table_type = "すらら";
					} elseif ($list['problem_table_type'] == 2) {
						$table_type = "テスト専用";
					}
					$html .= "<tr class=\"course_form_cell\">\n";
					$html .= "<td>".$list['problem_num']."</td>\n";
					$html .= "<td>".$table_type."</td>\n";
					$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
					$html .= "<td>".$list['standard_time']."</td>\n";
					$html .= "<td>".get_syutsudai_tangen_id_string_drill($s_class_id, $list['problem_num'])."</td>\n";
					$html .= "<td>".get_saiten_tangen_id_string_drill($s_class_id, $list['problem_num'])."</td>\n";
					$html .= "<td>\n";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"mt_view\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					// upd start hirose 2020/10/01 テスト標準化開発
					// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";
					$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\">\n";
					// upd end hirose 2020/10/01 テスト標準化開発
					$html .= "</form>\n";
					$html .= "</td>\n";
					$html .= "</tr>\n";
				}
			}
			$html .= "</table>\n";
		}
	}
	return $html;
}


/**
 * 採点単元ＩＤ取得（カンマ区切り）
 * 一覧表示用の文字列取得処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $class_id 級ID
 * @param integer $problem_num プログラム番号
 * @return string 採点単元ＩＤ（カンマ区切り）
 */
function get_saiten_tangen_id_string_drill($class_id, $problem_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$saiten_tangen = "";

	$sql = "SELECT butp.book_unit_id " .
			 " FROM ".T_BOOK_UNIT_TEST_PROBLEM ." butp ".
			 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST . " mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' ".
			 " WHERE butp.problem_num = '".$problem_num."'".
			 "   AND mtbul.class_id ='".$class_id."' ".
			 "   AND butp.mk_flg ='0' ;";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($saiten_tangen) { $saiten_tangen .= ","; }
			$saiten_tangen .= $list['book_unit_id'];
		}
	}

	return $saiten_tangen;
}

/**
 * 出題単元ＩＤ取得（カンマ区切り）
 * 一覧表示用の文字列取得処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $class_id 級ID
 * @param integer $problem_num プログラム番号
 * @return string 出題単元ＩＤ（カンマ区切り）
 */
function get_syutsudai_tangen_id_string_drill($class_id, $problem_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$syutugen_tangen = "";

	$sql = "SELECT control_unit_id " .
			 " FROM ".T_MATH_TEST_CONTROL_PROBLEM .
			 " WHERE problem_num = '".$problem_num."'".
			 "   AND class_id = '".$class_id."'".
			 "   AND mk_flg ='0' ;";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($syutugen_tangen) { $syutugen_tangen .= ","; }
			$syutugen_tangen .= $list['control_unit_id'];
		}
	}

	return $syutugen_tangen;

}

/**
* 採点単元取得
*
* AC:[A]管理者 UC1:[M01]Core管理機能.
*
* @author Azet
* @param integer $s_book_unit_id 単元ID
* @return array エラーの場合
*/
function get_book_unit_name($s_book_unit_id){
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$book_unit_name = "";
	$sql = "SELECT book_unit_name".
		" FROM ".T_MS_BOOK_UNIT.
		" WHERE mk_flg = '0'".
		" AND book_unit_id = '".$s_book_unit_id."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$book_unit_name = $list['book_unit_name'];
	}
	return $book_unit_name;
}
?>
