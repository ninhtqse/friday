<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題検証
 *
 * 履歴
 * 2011/03/12 初期設定
 *
 * @author Azet
 */

// hirano

include("../../_www/problem_lib/problem_regist.php");

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "view_session") { $ERROR = view_session(); }
	elseif (ACTION == "sub_list_session") { $ERROR = sub_list_session(); }

	$html .= select_unit_view();
	//if ($_SESSION['t_practice']['gknn']&&($_SESSION['t_practice']['core_code'] || $_SESSION['t_practice']['default_test_num'])) {
	// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
	//コースを未選択にしても表示できてしまうため、コース情報を追加
	// if (($_SESSION['t_practice']['test_type'] == 1 && $_SESSION['t_practice']['core_code']) || ($_SESSION['t_practice']['test_type'] == 4 && $_SESSION['t_practice']['gknn'])) {
	if (($_SESSION['t_practice']['test_type'] == 1 && $_SESSION['t_practice']['course_num'] && $_SESSION['t_practice']['core_code']) || ($_SESSION['t_practice']['test_type'] == 4 && $_SESSION['t_practice']['gknn'])) {
	// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
		$html .= problem_list($ERROR);
	}

	//add start okabe 2013/06/24
	if ($_SESSION['t_practice']['test_type'] == "hantei_test" ) {
		include("../../_www/admin_lib/test_practice_hantei_test_drill.php");
		$html = hantei_test_select_unit_view();
		$html .= toeic_test_problem_list($ERROR);
	}
	//add end okabe 2013/06/24

	// add start yoshizawa 2015/10/02 02_作業要件/34_数学検定/数学検定
	if ($_SESSION['t_practice']['test_type'] == 5 ) {
		include("../../_www/admin_lib/test_practice_math_test_drill.php");
		$html = math_test_select_unit_view();
	}
	//add end yoshizawa 2015/10/02

	//add start hirose 2018/11/21 すらら英単語
	if ($_SESSION['t_practice']['test_type'] == 6) {
		include("../../_www/admin_lib/test_practice_vocabulary_test_drill.php");
		$html = vocabulary_test_select_unit_view();
	}
	//add end hirose 2018/11/21 すらら英単語

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
function select_unit_view() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_WRITE_TYPE;		// add ookawara 2012/07/29
	// global $L_GKNN_LIST_TYPE1;	// add 2015/02/16 yoshizawa 定期テスト高校版対応 // del karasawa 2020/12/22 定期テスト学年追加開発
	global $L_GKNN_LIST_TYPE2;	// add karasawa 2020/12/22 定期テスト学年追加開発
	// update start 2020/08/29 yoshizawa テスト標準化開発
	// 定期テストは段階リリースになるため、学力診断テストのみ海外コースを選択可能といたします。
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	if ($_SESSION['t_practice']['test_type'] == 4) {
		// upd start hirose 2020/09/11 テスト標準化開発
		// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();
		$test_type4 = new TestStdCfgType4($cdb);
		$l_write_type = $test_type4->getTestUseCourseAdmin();
		// upd end hirose 2020/09/11 テスト標準化開発
	// add start hirose 2020/12/15 テスト標準化開発 定期テスト
	} elseif ($_SESSION['t_practice']['test_type'] == 1) {
		$test_type1 = new TestStdCfgType1($cdb);
		$l_write_type = $test_type1->getTestUseCourseAdmin();
	// add end hirose 2020/12/15 テスト標準化開発 定期テスト
	} else {
		$l_write_type = $L_WRITE_TYPE;
	}
	// update end 2020/08/29 yoshizawa テスト標準化開発

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
		// add start karasawa 2020/12/22 定期テスト学年追加開発
		$posted_course_num = 0;
		if($_SESSION['t_practice']['course_num']){$posted_course_num = $_SESSION['t_practice']['course_num'];}
		// add end karasawa 2020/12/22 定期テスト学年追加開発
		//コース
		//	add ookawara 2012/07/29 start
		$couse_html = "<option value=\"0\">選択して下さい</option>\n";
		//upd start hirose 2020/08/26 テスト標準化開発
		// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
		foreach ($l_write_type AS $course_num => $course_name) {
		//upd end hirose 2020/08/26 テスト標準化開発
			if ($course_name == "") {
				continue;
			}
			$selected = "";
			if ($_SESSION['t_practice']['course_num'] == $course_num) {
				$selected = "selected";
			}
			$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
		}
		//	add ookawara 2012/07/29 end

		// del 2015/02/16 yoshizawa 定期テスト高校版対応
		//学年
		//foreach($L_GKNN_LIST as $key => $val) {
		//	if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		//	$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		//}
		//----------------------------------------------
		// add 2015/02/16 yoshizawa 定期テスト高校版対応
		if ($_SESSION['t_practice']['test_type'] == 1) {

			// foreach($L_GKNN_LIST_TYPE1 as $key => $val) {	// del karasawa 2020/12/22 定期テスト学年追加開発
			foreach($L_GKNN_LIST_TYPE2[$posted_course_num] as $key => $val) {	// add karasawa 2020/12/22 定期テスト学年追加開発
				if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		} else {

			foreach($L_GKNN_LIST as $key => $val) {
				if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		}
		//----------------------------------------------

		$select_name .= "<td>コース</td>\n";
		$select_name .= "<td>学年</td>\n";

		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"course_num\" onchange=\"submit();\">".$couse_html."</select>\n";
		$select_menu .= "</td>\n";
		$select_menu .= "<td>\n";
		$select_menu .= "<select name=\"gknn\" onchange=\"submit();\">".$gknn_html."</select>\n";
		$select_menu .= "</td>\n";
		if ($_SESSION['t_practice']['test_type'] == 1) {
			//テスト時期
			$L_CORE_CODE = core_code_list();
			$core_code_html = "<option value=\"0\">選択して下さい</option>\n";
			foreach($L_CORE_CODE as $key => $val) {
				if ($_SESSION['t_practice']['core_code'] == $L_CORE_CODE[$key]['bnr_cd']."_".$L_CORE_CODE[$key]['kmk_cd']) { $selected = "selected"; } else { $selected = ""; }
				$core_code_html .= "<option value=\"".$L_CORE_CODE[$key]['bnr_cd']."_".$L_CORE_CODE[$key]['kmk_cd']."\" ".$selected.">".$L_CORE_CODE[$key]['bnr_nm']." ".$L_CORE_CODE[$key]['kmk_nm']."</option>\n";
			}
			//出版社
			$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!='0' ORDER BY disp_sort";
			if ($result = $cdb->query($sql)) {
				$publishing_count = $cdb->num_rows($result);
			}
			if (!$publishing_count) {
				$html = "<br>\n";
				$html .= "出版社が存在しません。設定してからご利用下さい。";
				return $html;
			}
			$publishing_html = "<option value=\"0\">選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['publishing_id'] == $list['publishing_id']) { $selected = "selected"; } else { $selected = ""; }
				$publishing_html .= "<option value=\"".$list['publishing_id']."\" ".$selected.">".$list['publishing_name']."</option>\n";
			}
			//教科書
			if ($_SESSION['t_practice']['course_num'] && $_SESSION['t_practice']['publishing_id'] && $_SESSION['t_practice']['gknn']) {
				$sql  = "SELECT * FROM " . T_MS_BOOK . " ms_book" .
					" WHERE publishing_id='".$_SESSION['t_practice']['publishing_id']."'".
					" AND course_num='".$_SESSION['t_practice']['course_num']."'".
					" AND gknn='".$_SESSION['t_practice']['gknn']."'".
					" AND mk_flg='0' ORDER BY disp_sort";
				if ($result = $cdb->query($sql)) {
					$book_count = $cdb->num_rows($result);
				}
				if (!$book_count) {
					$book_html = "<option value=\"0\">設定されていません</option>\n";
				} else {
					$book_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['t_practice']['book_id'] == $list['book_id']) { $selected = "selected"; } else { $selected = ""; }
						$book_html .= "<option value=\"".$list['book_id']."\" ".$selected.">".$list['book_name']."</option>\n";
					}
				}
			} else {
				$book_html .= "<option value=\"0\">--------</option>\n";
			}
			//単元
			if ($_SESSION['t_practice']['book_id']) {
				if ($_SESSION['t_practice']['core_code']) {
					list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
					if ($bnr_cd == "C000000001") {
						$where = " AND term3_kmk_ccd='".$kmk_cd."'";
					} elseif ($bnr_cd == "C000000002") {
						$where = " AND term2_kmk_ccd='".$kmk_cd."'";
					}
				}
				$sql  = "SELECT *" . " FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
					$where." ORDER BY disp_sort;";
				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}
				if (!$book_unit_count) {
					$book_unit_html = "<option value=\"0\">設定さfれていません</option>\n";
				} else {
					$book_unit_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['t_practice']['book_unit_id'] == $list['book_unit_id']) { $selected = "selected"; } else { $selected = ""; }
						$book_unit_html .= "<option value=\"".$list['book_unit_id']."\" ".$selected.">".$list['book_unit_name']."</option>\n";
					}
				}

			} else {
				$book_unit_html .= "<option value=\"0\">--------</option>\n";
			}
			$select_name .= "<td>テスト時期</td>\n";
			$select_name .= "<td>出版社</td>\n";
			$select_name .= "<td>教科書</td>\n";
			$select_name .= "<td>単元</td>\n";

			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"core_code\" onchange=\"submit();\">".$core_code_html."</select>\n";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"publishing_id\" onchange=\"submit();\">".$publishing_html."</select>\n";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"book_id\" onchange=\"submit();\">".$book_html."</select>";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"book_unit_id\" onchange=\"submit();\">".$book_unit_html."</select>";
			$select_menu .= "</td>\n";

			if (!$_POST['test_action']) {
//				if (!$_SESSION['t_practice']['course_num'] || !$_SESSION['t_practice']['publishing_id'] || !$_SESSION['t_practice']['gknn']) {
				if (!$_SESSION['t_practice']['course_num'] || !$_SESSION['t_practice']['gknn']) {
					$msg_html .= "コース、出版社、学年を選択してください。<br>\n";
				} elseif (!$_SESSION['t_practice']['core_code']) {
					$msg_html .= "テスト時期を選択してください。<br>\n";
					unset($_SESSION['t_practice']['default_test_num']);
				} elseif (!$_SESSION['t_practice']['book_id']) {
				//	$msg_html .= "教科書を選択してください。<br>\n";
					unset($_SESSION['t_practice']['default_test_num']);
				} else {
				}
			}

		} elseif ($_SESSION['t_practice']['test_type'] == 4) {
			//テストマスタ
			if ($_SESSION['t_practice']['course_num'] && $_SESSION['t_practice']['gknn']) {
				$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP." ms_tg" .
					" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.test_group_id=ms_tg.test_group_id".
					" AND tgl.mk_flg='0'".
					" INNER JOIN ".T_MS_TEST_DEFAULT." ms_td ON ms_td.default_test_num=tgl.default_test_num".
					" AND ms_td.mk_flg='0'".
					" WHERE ms_tg.mk_flg='0'".
					" AND ms_tg.test_gknn='".$_SESSION['t_practice']['gknn']."'".
					" AND ms_td.test_type='4'".
					" AND ms_td.course_num='".$_SESSION['t_practice']['course_num']."'".
					" ORDER BY ms_tg.disp_sort,tgl.disp_sort";
				if ($result = $cdb->query($sql)) {
					$test_count = $cdb->num_rows($result);
				}
				if (!$test_count) {
					$test_html = "<option value=\"0\">設定されていません</option>\n";
				} else {
					$test_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['t_practice']['default_test_num'] == $list['test_group_id']."_".$list['default_test_num']) { $selected = "selected"; } else { $selected = ""; }
						$test_html .= "<option value=\"".$list['test_group_id']."_".$list['default_test_num']."\" ".$selected.">".$list['test_group_name']." ".$list['test_name']."</option>\n";
					}
				}
			} else {
				$test_html .= "<option value=\"0\">--------</option>\n";
			}
			$select_name .= "<td>テスト名</td>\n";

			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"default_test_num\" onchange=\"submit();\">".$test_html."</select>\n";
			$select_menu .= "</td>\n";

			if (!$_POST['test_action']) {
				if (!$_SESSION['t_practice']['course_num'] || !$_SESSION['t_practice']['gknn']) {
					$msg_html .= "<span style=\"clear: both;\"><br>\n";
					$msg_html .= "コース、学年を選択してください。</span>\n";
				} else {
					if ($_SESSION['t_practice']['default_test_num']) {
					} else {
						unset($_SESSION['t_practice']['book_id']);
					}
				}
			}
		}
	}
	$html = "<br>\n";
	 // add start karasawa 2020/04/06 社会定期テスト対応開発
	if($_SESSION['t_practice']['test_type'] == 1 && $_SESSION['t_practice']['course_num'] == 16){
		$html .= "<span style = \"font-weight: bold;\">【社会のコースを登録する際の諸注意】</span><br>";		// add oda 2020/04/23 社会定期テスト対応開発
		$html .= "<span>※地理を登録/選択する場合は中学1年生、歴史を登録/選択する場合は中学2年生、公民を登録/選択する場合は中学3年生から登録/選択ください</span>";
	}
	// add end karasawa 2020/04/06 社会定期テスト対応開発
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
	$html .= "<select name=\"test_type\" onchange=\"document.select_view_menu.submit();\">".$test_type_html."</select>\n";		// update oda 2014/10/06 課題要望一覧No352 Javascriptエラー回避の為、document.select_view_menuを追加
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
function view_session() {
	if ($_SESSION['t_practice']['default_test_num'] != $_POST['default_test_num']) {
		unset($_SESSION['sub_session']['add_type']);
	}
	if (($_SESSION['t_practice']['course_num'] != $_POST['course_num'])
	|| ($_SESSION['t_practice']['publishing_id'] != $_POST['publishing_id'])
	|| ($_SESSION['t_practice']['gknn'] != $_POST['gknn'])) {
		unset($_SESSION['t_practice']['book_id']);
		unset($_SESSION['t_practice']['default_test_num']);
	} elseif(strlen($_POST['book_id'])) {
		$_SESSION['t_practice']['book_id'] = $_POST['book_id'];
	} elseif(strlen($_POST['default_test_num'])) {
		$_SESSION['t_practice']['default_test_num'] = $_POST['default_test_num'];
	}
	if ($_SESSION['t_practice']['book_id'] != $_POST['book_id']) {
		unset($_SESSION['t_practice']['book_unit_id']);
	} elseif(strlen($_POST['book_unit_id'])) {
		$_SESSION['t_practice']['book_unit_id'] = $_POST['book_unit_id'];
	}

	if (strlen($_POST['course_num'])) { $_SESSION['t_practice']['course_num'] = $_POST['course_num']; }
	if (strlen($_POST['core_code'])) { $_SESSION['t_practice']['core_code'] = $_POST['core_code']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['t_practice']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['t_practice']['gknn'] = $_POST['gknn']; }
	if ($_SESSION['t_practice']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['t_practice']);
	}

	if (strlen($_POST['test_type'])) { $_SESSION['t_practice']['test_type'] = $_POST['test_type']; }
	$_SESSION['sub_session']['s_page'] = 1;

	return $ERROR;
}

/**
 * 問題一覧絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return string HTML
 */
function select_list() {
	global $L_DESC,$L_PAGE_VIEW;

	//ページ数
	if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 1; }
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}

	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_list_session\">\n";
	$sub_session_html .= "<td>\n";
	$sub_session_html .= "表示数 <select name=\"s_page_view\">\n".$s_page_view_html."</select>\n";
	$sub_session_html .= "<input type=\"submit\" value=\"Set\">\n";
	$sub_session_html .= "</td>\n";
	$sub_session_html .= "</form>\n";

	$html .= "<br style=\"clear:left;\">";
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
 * SESSION設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 */
function sub_list_session() {
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view'];
		unset($_SESSION['sub_session']['s_page']);
	}
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
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
function problem_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION['myid']);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }
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

//	if ($_SESSION['t_practice']['book_unit_id']) {
//		$where = " AND butp.book_unit_id='".$_SESSION['t_practice']['book_unit_id']."'";
//	}

	if ($_SESSION['t_practice']['course_num']) {
		$join_and .= " AND mb.course_num='".$_SESSION['t_practice']['course_num']."'";
		$where .= " AND mtdp.course_num='".$_SESSION['t_practice']['course_num']."'";		// add okabe 2018/06/12 test_problem.php と同じクエリーにするため追加(1395行あたり)。
	}
	if ($_SESSION['t_practice']['gknn']) {
		$join_and .= " AND mb.gknn='".$_SESSION['t_practice']['gknn']."'";
		$where .= " AND mtdp.gknn='".$_SESSION['t_practice']['gknn']."'";			// add oda 2014/10/14 課題要望一覧No352 学年を条件に追加
	}
	if ($_SESSION['t_practice']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
		$where .= " AND mtdp.term_bnr_ccd='".$bnr_cd."'";
		$where .= " AND mtdp.term_kmk_ccd='".$kmk_cd."'";
	}
	if ($_SESSION['t_practice']['publishing_id']) {
		$join_and .= " AND mb.publishing_id='".$_SESSION['t_practice']['publishing_id']."'";
	} else {
		if ($_SESSION['t_practice']['test_type'] == 1) {
			$join_and .= " AND mb.publishing_id!='0'";
		} elseif ($_SESSION['t_practice']['test_type'] == 4) {
			$join_and .= " AND mb.publishing_id='0'";
		}
	}
	if ($_SESSION['t_practice']['book_id']) {
		$where .= " AND mbu.book_id='".$_SESSION['t_practice']['book_id']."'";
	}
	if ($_SESSION['t_practice']['book_unit_id']) {
		$where .= " AND mbu.book_unit_id='".$_SESSION['t_practice']['book_unit_id']."'";
	}
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
		$where .= " AND mtdp.default_test_num='".$default_test_num."'";
	}
	if ($_SESSION['t_practice']['test_type'] == 1) {
		$join_sql = " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.mk_flg='0'".
			" AND mtdp.problem_table_type=butp.problem_table_type".
			" AND mtdp.problem_num=butp.problem_num".
			" AND mtdp.default_test_num=butp.default_test_num".
			" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.mk_flg='0'".
				" AND mbu.book_unit_id=butp.book_unit_id".
			" INNER JOIN ".T_MS_BOOK." mb ON mb.mk_flg='0' AND mb.book_id=mbu.book_id".$join_and;
	} elseif ($_SESSION['t_practice']['test_type'] == 4) {
		$join_sql = " INNER JOIN ".T_MS_TEST_DEFAULT." mtd ON mtd.mk_flg='0'".
				" AND mtd.default_test_num=mtdp.default_test_num".
				" AND mtd.course_num='".$_SESSION['t_practice']['course_num']."'".
			" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.mk_flg='0'".
				" AND tgl.default_test_num=mtd.default_test_num".
			" INNER JOIN ".T_MS_BOOK_GROUP." mtg ON mtg.mk_flg='0' AND mtg.test_group_id=tgl.test_group_id".
				" AND mtg.test_gknn='".$_SESSION['t_practice']['gknn']."'";

	}
	$sql  = "SELECT count(DISTINCT mtdp.problem_num) AS problem_count" .
		" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
		$join_sql.
		" WHERE mtdp.mk_flg='0'".
		$where.
		" GROUP BY mtdp.default_test_num,mtdp.problem_num,mtdp.problem_table_type".
		"";
//echo $sql."<hr><br>";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_count += $list['problem_count'];
		}
	}
	if (!$problem_count) {
		$html .= "<br>\n";
		$html .= "<br style=\"clear:left;\">";
		$html .= "今現在登録されている問題は有りません。<br>\n";
		return $html;
	}


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
	$onchenge = "onclick=\"this.blur(); this.focus();\" onchange=\"update_tegaki_flg(this,'select_view_menu');\"";
	$html .= "<br><br>";
	$html .= "<div class=\"tegaki-switch\">";
	$html .= "<label>";
	$html .= "<input type=\"checkbox\" name=\"tegaki_control\" ".$check." ".$onchenge." class=\"tegaki-check\"><span class=\"swith-content\"></span><span class=\"swith-button\"></span>";
	$html .= "</label>";
	$html .= "</div>";
	//add end hirose 2018/05/01 管理画面手書き切り替え機能追加


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

	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
		"SELECT ".
		"mtdp.default_test_num,".
		"mtdp.problem_table_type,".
		"mtdp.problem_num,".
		"p.form_type,".
		"mpa.standard_time,".
		"mtdp.problem_point,".
		// add start hirose 2020/10/01 テスト標準化開発
		"mtdp.gknn,".
		"mtdp.term_bnr_ccd,".
		"mtdp.term_kmk_ccd,".
		// add end hirose 2020/10/01 テスト標準化開発
		"mtdp.disp_sort".
		" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
		$join_sql.
		" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtdp.problem_num".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
		" WHERE mtdp.mk_flg='0'".
		" AND mtdp.problem_table_type='1'".
		"".
		$where.
		" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num".
		" UNION ALL ".
		"SELECT ".
		"mtdp.default_test_num,".
		"mtdp.problem_table_type,".
		"mtdp.problem_num,".
		"mtp.form_type,".
		"mtp.standard_time,".
		"mtdp.problem_point,".
		// add start hirose 2020/10/01 テスト標準化開発
		"mtdp.gknn,".
		"mtdp.term_bnr_ccd,".
		"mtdp.term_kmk_ccd,".
		// add end hirose 2020/10/01 テスト標準化開発
		"mtdp.disp_sort".
		" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
		$join_sql.
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
		" WHERE mtdp.mk_flg='0'".
		" AND mtdp.problem_table_type='2'".
		"".
		$where.
		" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
//	echo $sql."<br><br>";
	$cdb->exec_query($sql);

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY default_test_num,disp_sort".
		$limit;
//	echo $sql;
	if ($result = $cdb->query($sql)) {
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
		if ($_SESSION['t_practice']['test_type'] == 4) {
			$html .= "<th>テストID</th>\n";
		}
		$html .= "<th>No</th>\n";
		$html .= "<th>問題管理番号</th>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		//upd start 2018/06/05 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$html .= "<th>回答目安時間</th>\n";
		if ($_SESSION['t_practice']['test_type'] != 4) {
			$html .= "<th>回答目安時間</th>\n";
		}
		//upd end 2018/06/05 yamaguchi
		if ($_SESSION['t_practice']['test_type'] == "4") {
			$html .= "<th>配点</th>\n";
		}
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
			if ($_SESSION['t_practice']['test_type'] == 4) {
				$html .= "<td>".$list['default_test_num']."</td>\n";
			}
			$html .= "<td>".$list['disp_sort']."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			//upd start 2018/06/05 yamaguchi 学力診断テスト画面 回答目安時間非表示
			//$html .= "<td>".$list['standard_time']."</td>\n";
			if ($_SESSION['t_practice']['test_type'] != 4) {
				$html .= "<td>".$list['standard_time']."</td>\n";
			}
			//upd end 2018/06/05 yamaguchi

			if ($_SESSION['t_practice']['test_type'] == "4") {
				$html .= "<td>".$list['problem_point']."</td>\n";
			}
			$html .= "<td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			// upd start hirose 2020/10/01 テスト標準化開発
			// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";
			// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
			// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$list['default_test_num']."')\">\n";
			if($_SESSION['t_practice']['test_type'] == 4){
				$id_ = $list['default_test_num'];
			}else{
				$id_ = $_SESSION['t_practice']['course_num'];
			}
			$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$id_."')\">\n";
			// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
			// upd end hirose 2020/10/01 テスト標準化開発
			$html .= "</form>\n";
			$html .= "</td>\n";
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

/**
 * コアコード一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array
 */
function core_code_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CORE_CODE = array();
	$sql  = "SELECT * FROM ".T_MS_CORE_CODE . " WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
			$L_CORE_CODE[$i]['bnr_cd'] = $list['bnr_cd'];
			$L_CORE_CODE[$i]['kmk_cd'] = $list['kmk_cd'];
			$L_CORE_CODE[$i]['bnr_nm'] = $list['bnr_nm'];
			$L_CORE_CODE[$i]['kmk_nm'] = $list['kmk_nm'];
			$i++;
		}
	}
	return $L_CORE_CODE;
}

/**
 * コース一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array
 */
function course_list() {
	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[0] = "選択して下さい";

	//	add ookawara 2012/07/29 start
	foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
		if ($course_name == "") {
			continue;
		}
		$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
	}
	//	add ookawara 2012/07/29 end

	return $L_COURSE_LIST;
}

/**
 * 本一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $course_num
 * @param string $gknn
 * @return array
 */
function book_list($course_num,$gknn) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_BOOK_LIST = array();
	if (!$course_num || !$gknn) {
		$L_BOOK_LIST[0] = "------";
		return $L_BOOK_LIST;
	}
	$L_BOOK_LIST[0] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_BOOK.
		" WHERE mk_flg='0' AND publishing_id='0' AND course_num='".$course_num."' AND gknn='".$gknn."' ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_BOOK_LIST[$list['book_id']] = $list['book_name'];
		}
	}
	return $L_BOOK_LIST;
}

/**
 * ユニットの本の対して問題数
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $unit_num
 * @param integer $block_num
 * @return integer
 */
function get_drill_count($unit_num,$block_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT block.*,unit.* FROM " . T_BLOCK ." block".
		" LEFT JOIN ".T_STAGE." stage ON stage.stage_num=block.stage_num".
		" LEFT JOIN ".T_LESSON." lesson ON lesson.lesson_num=block.lesson_num".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=block.unit_num".
		" WHERE block.state='0' AND stage.display='1' AND block.course_num='".$_SESSION['t_practice']['course_num']."'".
		" AND stage.state='0' AND stage.display='1'".
		" AND lesson.state='0' AND stage.display='1'".
		" AND unit.state='0' AND stage.display='1'".
		" AND block.unit_num='".$unit_num."'".
		" ORDER BY stage.list_num,lesson.list_num,unit.list_num,block.list_num";
	if ($result = $cdb->query($sql)) {
		$drill_num = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			$drill_num++;
			if ($list['block_num'] == $block_num) { break; }
		}
		$cdb->free_result($result);
	}
	return $drill_num;
}

/**
 * 年の一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array
 */
function year_list() {
	$L_YEAR = array();
	$L_YEAR[] = "----";
//T_MS_TEST_DEFAULTのkkn_fromの最古年数を抜き出してその年から
	for($i = 0;$i < 3; $i++) {
		$year = date("Y",mktime(0, 0, 0, 1, 1, date('Y') + $i));
		$L_YEAR[$year] = $year;
	}
	return $L_YEAR;
}

?>
