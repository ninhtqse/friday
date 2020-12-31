<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理 判定テスト 問題検証
 *
 * 履歴
 * 2013/07/25 初期設定
 *
 * @author Azet
 */

// okabe
// test_drill.php ファイルをベースに。


/**
 * ユニット選択HTMLを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function hantei_test_select_unit_view() {
/*
//if (ACTION != "ht_export" && ACTION != "ht_import") {
 echo "MODE=".MODE.", ACTION=".ACTION;
// echo "<br/>sub_session:";
// print_r($_SESSION['sub_session']);
 echo "<hr/>";
//}
*/
	if (ACTION == "sub_course_session") {
		$ERROR = sub_setpage_session();

	//} elseif (ACTION == "ht_change") {	//登録済み判定テスト問題[変更]→[修正]ボタン押下時
	//	$ERROR = ht_change();

	//} elseif (ACTION == "ht_delete") {	//削除→確認画面にて[削除]押下時
	//	$ERROR = ht_change();

	//} elseif (ACTION == "ht_add") {
	//	$ERROR = add();

	//} elseif (ACTION == "sub_test_session") {	//既存のテスト問題を選択、を選択後にテストタイプを選んだ後
	//	$ERROR = sub_seltest_session();

	//} elseif (ACTION == "ht_exist_check") {		//既存のテスト問題を選んで、[追加確認]を押したときの処理
	//	$ERROR = ht_test_exist_check();

	//} elseif (ACTION == "ht_exist_add") {		//既存のテスト問題を、最終的に[登録]する処理
	//	$ERROR = ht_test_exist_add();

	//} elseif (ACTION == "ht_surala_check") {	//既存のすらら問題を選んで、[追加確認]を押したときの処理
	//	$ERROR = ht_surala_add_check();

	//} elseif (ACTION == "ht_surala_add") {		//既存のすらら問題を、最終的に[登録]する処理
	//	$ERROR = ht_surala_add_add();

	//} elseif (ACTION == "ht_problem_check") {	//(新規登録) 入力フォームで [追加] 押下時
	//	ht_sub_session();
	//	$ERROR = ht_test_add_check();	//入力項目チェック

	//} elseif (ACTION == "ht_problem_add") {		//(新規登録) 入力フォーム[追加]→確認画面で[登録]押下時
	//	$ERROR = ht_test_add_add();

	//} elseif (ACTION == "ht_export") {			//エクスポート
	//	ht_csv_export();

	//} elseif (ACTION == "ht_import") {			//インポート
	//	list($ERROR, $PROC_RESULT) = ht_csv_import();

	}


	$html .= ht_select_unit_view();


	//if (MODE == "ht_view") {			//登録済み問題リスト画面の [変更]ボタンを押したとき
	//	$html .= view($ERROR);		//入力フォーム（修正フォームと共用）

	//} elseif (MODE == "ht_add") {

	//	if ($_SESSION['sub_session']['add_type']['problem_type'] == "surala") {			//'既存の' 'すらら問題' 登録
	//		if (ACTION == "ht_surala_check") {
	//			if (!$ERROR) {
	//					//エラー無し
	//				$html .= ht_surala_add_check_html();
	//			} else {
	//					//エラー有り
	//				$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題選択フォーム表示時
	//			}
	//		} elseif (ACTION == "ht_surala_add") {
	//			if (!$ERROR) {
	//					//エラー無し
	//					//$html .= hantei_test_list($ERROR);
	//					$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題の登録後、stage,lesson,unitなど選択済みの画面へ戻す
	//			} else {
	//					//エラー有り
	//					$html .= hantei_test_list($ERROR);
	//			}
	//		} else {
	//			$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題選択フォーム表示時
	//		}

	//	} elseif($_SESSION['sub_session']['add_type']['problem_type'] == "test") {		//'新規の' 'テスト問題' 登録
	//		if ($_SESSION['sub_session']['add_type']['add_type'] == "add") {
	//			if (ACTION == "ht_problem_check") {
	//				if (!$ERROR) {		//入力フォームで [追加] 押下時
	//					//エラー無し
	//					$html .= ht_test_add_check_html();
	//				} else {
	//					//エラー有り
	//					//$html .= test_add_addform($ERROR);
	//					$html .= addform($ERROR);
	//				}
	//			} elseif (ACTION == "ht_problem_add") {
	//				if (!$ERROR) { 		//入力フォーム[追加]→確認画面で[登録]押下時
	//					//エラー無し
	//					$html .= hantei_test_list($ERROR);
	//				} else {
	//					//エラー有り
	//					$html .= addform($ERROR);
	//				}
	//			} else {	//入力フォーム表示時
	//				$html .= addform($ERROR);
	//			}

	//		} elseif ($_SESSION['sub_session']['add_type']['add_type'] == "exist") {		//'既存の' 'テスト問題' 登録
	//			if (ACTION == "ht_exist_check") {
	//				if (!$ERROR) {
	//					//エラー無し
	//					$html .= ht_test_exist_check_html();
	//				} else {
	//					//エラー有り
	//					$html .= ht_test_exist_addform($ERROR);
	//				}
	//			} elseif (ACTION == "ht_exist_add") {
	//				if (!$ERROR) {
	//					//エラー無し
	//					//$html .= $ERROR);
	//					//$html .= hantei_test_list($ERROR);
	//					$html .= ht_test_exist_addform($ERROR);		//既存の判定テスト問題登録後、既存問題選択ドロップダウンの画面へ戻す
	//				} else {
	//					//エラー有り
	//					$html .= hantei_test_list($ERROR);
	//				}
	//			} else {
	//				$html .= ht_test_exist_addform($ERROR);		//既存のテスト問題選択フォーム表示時
	//			}
	//		}
	//	}


	//} elseif (MODE == "ht_delete") {
	//	if (ACTION == "ht_delete") {
	//		if (!$ERROR) {		//エラー無し
	//			$html .= hantei_test_list($ERROR);

	//		} else {			//エラー有り
	//			$html .= hantei_test_list($ERROR);

	//		}

	//	} else {
	//		$html .= ht_test_add_check_html();		//削除操作の初期画面
	//	}



	//} else {
		$html .= hantei_test_list($ERROR, $PROC_RESULT);
	//}

	return $html;
}


/**
 * ユニット選択画面を作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_select_unit_view() {
//function hantei_test_select_dropdown() {
//echo "*ht_select_unit_view(),"; // hantei_default_num=".$s_hantei_default_num."<br/>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_TYPE, $L_HANTEI_TYPE, $L_DESC, $L_PAGE_VIEW;
	global $L_TEST_ADD_TYPE, $L_TEST_PROBLEM_TYPE;

//echo "*****";
//echo "<".$_SESSION['sub_session']['s_page_view'].",".$_POST['s_page_view']."><br/>";

	$hantei_default_num = "0";
	$s_service_num = "0";
	$s_hantei_type = "0";
	$s_course_num = "0";
	$s_hantei_default_num = "0";
	$s_page_view = "2";

	$s_page = 1;

	if (ACTION == "sub_session") {
		if (strlen($_POST['s_service_num'])) { $_SESSION['sub_session']['s_service_num'] = $_POST['s_service_num']; }
		if (strlen($_POST['s_hantei_type'])) { $_SESSION['sub_session']['s_hantei_type'] = $_POST['s_hantei_type']; }
		if (strlen($_POST['s_course_num']))	{ $_SESSION['sub_session']['s_course_num'] = $_POST['s_course_num']; }
		if (strlen($_POST['s_hantei_default_num']))	{ $_SESSION['sub_session']['s_hantei_default_num'] = $_POST['s_hantei_default_num']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
		if (strlen($_POST['add_type'])) { $_SESSION['sub_session']['add_type']['add_type'] = $_POST['add_type']; }
		if (strlen($_POST['problem_type'])) { $_SESSION['sub_session']['add_type']['problem_type'] = $_POST['problem_type']; }
		//if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type']; }
		if (strlen($_POST['s_page']))	{ $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }
	}
	if (MODE == "set_problem_view") {
		$_SESSION['sub_session']['s_page'] = 1; 			//表示データ切り替えたらページを１にリセット
		$_SESSION['sub_session']['add_type']['add_type'] = "";
		$_SESSION['sub_session']['add_type']['problem_type'] = "";
		unset($_SESSION['sub_session']['select_course']);	//既存問題の登録途中で、画面が切り替えられたらリセット

		unset($_SESSION['sub_session']['seltest_course']);		//以下は既存の判定テスト問題の絞込み
		//unset($_SESSION['sub_session']['seltest_course']['as_test_type']);		//以下は既存の判定テスト問題の絞込み
		//unset($_SESSION['sub_session']['seltest_course']['as_service_num']);
		//unset($_SESSION['sub_session']['seltest_course']['as_hantei_type']);
		//unset($_SESSION['sub_session']['seltest_course']['as_course_num']);
		//unset($_SESSION['sub_session']['seltest_course']['as_hantei_default_num']);
		//unset($_SESSION['sub_session']['seltest_course']['as_form_type']);
		//unset($_SESSION['sub_session']['seltest_course']['as_page_view']);
	}

	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
		$s_page = $_SESSION['sub_session']['s_page'];	//@@@
	}
	if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type']; }
//echo "@".$_SESSION['sub_session']['s_page_view'].",".$_POST['s_page_view']."@<br/>";


//echo "****".$_SESSION['sub_session']['add_type']['form_type'].", ".$_POST['form_type']."<br>";
	//テストタイプ
	$test_type_html = "";
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}

	//	サービス
	$service_html  = "";
	$chk_flg = 0;
	$sql  = "SELECT service_num, service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0'".
			" ORDER BY list_num;";

	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($s_service_num < 1) {
			$selected = "selected";
			$msg_html = "サービスを選択してください。";
			$s_hantei_default_num = "0";
		}
		$service_html .= "<option value=\"0\" ".$selected.">選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$selected = "";
			if ($s_service_num == $list['service_num']) { $selected = "selected"; }
			$service_html .= "<option value=\"".$list['service_num']."\" ".$selected.">".$list['service_name']."</option>\n";
			$chk_flg = 1;
		}
	}
	if ($service_html == "" || $chk_flg == 0) {
		$service_html .= "<option value=\"0\">サービスが登録されておりません</option>\n";
		$s_service_num = 0;
	}


	//	判定タイプ
	$hantei_type_html  = "";
	if ($s_service_num != 0) {
		if ($s_service_num > 0 && count($L_HANTEI_TYPE) > 1) {
			foreach ($L_HANTEI_TYPE AS $key => $val) {
				$selected = "";
				if ($s_hantei_type == $key) { $selected = "selected"; }
				$hantei_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		}

	}
	if ($s_service_num > 0 && $s_hantei_type == 0) {
		$msg_html = "判定タイプを選択してください。";
		$s_hantei_default_num = "0";
	}

	if ($hantei_type_html == "") {
		$hantei_type_html .= "<option value=\"0\">サービスを選択してください</option>\n";
		$s_hantei_type = 0;
	}


	//コース
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	if ($s_service_num != "0" && $s_hantei_type != "0") {	//サービスと判定タイプが選択されている場合
		$sql = "SELECT course.course_num, course.course_name".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, " .T_COURSE. " course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			" AND service_course_list.service_num ='".$s_service_num."';";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$selected = "";
				if ($list['course_num'] == $s_course_num) { $selected = "selected"; }
				$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
			}
		}
	}
	if ($s_service_num != "0" && $s_hantei_type == "2" && $s_course_num == "0") {
		$msg_html = "コースを選択してください。";
		$s_hantei_default_num = "0";
	}


	//判定名
	$hanteimei_html = "<option value=\"0\">選択して下さい</option>\n";
	if ($s_hantei_type == "1" || ($s_hantei_type == "2" && $s_course_num != "0")) {	//コース選択済み、または判定タイプをコース判定の場合
		//判定タイプが コース内判定 の場合は、コース選択可能にする
		$sql = "SELECT hantei_ms_default.hantei_default_num, hantei_ms_default.list_num, hantei_ms_default.hantei_name".
			" FROM ".T_HANTEI_MS_DEFAULT." hantei_ms_default ".
			" WHERE hantei_ms_default.mk_flg = '0'".
			" AND hantei_ms_default.service_num = '".$s_service_num."'".
			" AND hantei_ms_default.hantei_type = '".$s_hantei_type."'";
		if ($s_hantei_type == "2") { $sql .= " AND hantei_ms_default.course_num = '".$s_course_num."'"; }
		$sql .=" ORDER BY hantei_ms_default.list_num";	// add hasegawa 2017/12/25 AWS移設 ソート条件追加
		$sql .= ";";
// echo $sql."<br/>";
		if ($result = $cdb->query($sql)) {
			$default_test_num_count = $cdb->num_rows($result);
			if ($default_test_num_count > 0) {
//print_r($result);
//echo $sql."<br/>";
				$chk_flg = 0;
				while ($list = $cdb->fetch_assoc($result)) {
//echo "* ".$list['hantei_default_num'].", ".$list['hantei_name']."<br/>";
					$selected = "";
					if ($list['hantei_default_num'] == $s_hantei_default_num) {
						$selected = "selected";
						$chk_flg = 1;
					}
					$hanteimei_html .= "<option value=\"".$list['hantei_default_num']."\" ".$selected.">".$list['hantei_name']."</option>\n";
				}
				if ($chk_flg == 0) {
					$s_hantei_default_num = "0";
				}
			} else {
				$msg_html = "今現在登録されている'判定名'は有りません。";
				$s_hantei_default_num = "0";
			}
		}
	}
	if ($s_hantei_default_num == "0" && ($s_hantei_type == "1" || ($s_hantei_type == "2" && $s_course_num != "0"))) {
		$msg_html = "判定名を選択してください。";
	}

//	//ページ数
//	if ($s_page_view < 0) { $s_page_view = 2; }
//	foreach ($L_PAGE_VIEW as $key => $val){
//		$selected = "";
//		if ($s_page_view == $key) { $selected = " selected"; }
//		$s_page_view_html .= "<option value=\"".$key."\"".$selected.">".$val."</option>\n";
//	}


	//メニュー組み立て情報
	$SEL_MENU_L = array();
	$SEL_MENU_L[0] = array('text'=>'テストタイプ', 'name'=>'test_type', 'onchange'=>'2', 'options'=>$test_type_html, 'Submit'=>'0');

	$SEL_MENU_L[1] = array('text'=>'サービス', 'name'=>'s_service_num', 'onchange'=>'1', 'options'=>$service_html, 'Submit'=>'0');
	$SEL_MENU_L[2] = array('text'=>'判定タイプ', 'name'=>'s_hantei_type', 'onchange'=>'1', 'options'=>$hantei_type_html, 'Submit'=>'0');
	if ($s_hantei_type != "1") {
		//判定タイプが コース判定 の場合は、コース選択非表示
		$SEL_MENU_L[3] = array('text'=>'コース', 'name'=>'s_course_num', 'onchange'=>'1', 'options'=>$couse_html, 'Submit'=>'0');
	}
	//$SEL_MENU_L[4] = array('text'=>'ソート', 'name'=>'s_desc', 'onchange'=>'0', 'options'=>$s_desc_html, 'Submit'=>'0');
	//$SEL_MENU_L[5] = array('text'=>'表示数', 'name'=>'s_page_view', 'onchange'=>'0', 'options'=>$s_page_view_html, 'Submit'=>'1');

	$SEL_MENU_L[6] = array('text'=>'判定名', 'name'=>'s_hantei_default_num', 'onchange'=>'1', 'options'=>$hanteimei_html, 'Submit'=>'0');

	//$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	//$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";


	ksort($SEL_MENU_L);

	//項目名,ドロップダウン
	$c_name_html = "";
	foreach ($SEL_MENU_L as $key => $SEL_MENU_ITEM_L) {
		$c_name_html .= "<td>".$SEL_MENU_ITEM_L['text']."</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<select name=\"".$SEL_MENU_ITEM_L['name']."\" ";
		if ($SEL_MENU_ITEM_L['onchange'] == 1) {
			$sub_session_html .= "onchange=\"submit();return false;\"";
		} elseif ($SEL_MENU_ITEM_L['onchange'] == 2) {
			$sub_session_html .= "onchange=\"hantei_problem_select();return false;\"";
		}
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
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	if ($msg_html) {
		$html .= "<br>\n";
		$html .= $msg_html;
		$html .= "<br>\n";
	}

	if ($s_hantei_default_num > 0) { $html .= "<br/>"; }

	//$s_page_save = $_SESSION['sub_session']['s_page'];
	$s_page_save = $_SESSION['sub_session']['s_page'];		//@@@
	$s_select_course = $_SESSION['sub_session']['select_course'];
	$s_page_view = $_SESSION['sub_session']['s_page_view'];
	$bak_as_test_type = $_SESSION['sub_session']['seltest_course']['as_test_type'];
	$bak_as_service_num = $_SESSION['sub_session']['seltest_course']['as_service_num'];
	$bak_as_hantei_type = $_SESSION['sub_session']['seltest_course']['as_hantei_type'];
	$bak_as_course_num = $_SESSION['sub_session']['seltest_course']['as_course_num'];
	$bak_as_hantei_default_num = $_SESSION['sub_session']['seltest_course']['as_hantei_default_num'];
	$bak_as_form_type = $_SESSION['sub_session']['seltest_course']['as_form_type'];
	$bak_as_page_view = $_SESSION['sub_session']['seltest_course']['as_page_view'];

	$add_type_add_type = $_SESSION['sub_session']['add_type']['add_type'];
	$add_type_problem_type = $_SESSION['sub_session']['add_type']['problem_type'];
	$add_type_form_type = $_SESSION['sub_session']['add_type']['form_type'];
	$bk_surala_regist_status = $_SESSION['sub_session']['surala_regist']['status'];	//既存すらら問題登録処理が成功なら1、判定問題成功なら2、新規問題なら3
	$bk_surala_regist_problem_num = $_SESSION['sub_session']['surala_regist']['problem_num'];
	$bk_surala_regist_disp_sort = $_SESSION['sub_session']['surala_regist']['disp_sort'];

	$_SESSION['sub_session'] = array();

	$_SESSION['sub_session']['add_type']['add_type'] = $add_type_add_type;
	$_SESSION['sub_session']['add_type']['problem_type'] = $add_type_problem_type;
	$_SESSION['sub_session']['add_type']['form_type'] = $add_type_form_type;

	$_SESSION['sub_session']['s_service_num'] = $s_service_num;
	$_SESSION['sub_session']['s_hantei_type'] = $s_hantei_type;
	$_SESSION['sub_session']['s_course_num'] = $s_course_num;
	$_SESSION['sub_session']['s_hantei_default_num'] = $s_hantei_default_num;
	//$_SESSION['sub_session']['s_desc'] = $s_desc;
	$_SESSION['sub_session']['s_page_view'] = $s_page_view;

	//既存の判定テスト問題登録、絞込みメニュー
	$_SESSION['sub_session']['seltest_course']['as_test_type'] = $bak_as_test_type;
	$_SESSION['sub_session']['seltest_course']['as_service_num'] = $bak_as_service_num;
	$_SESSION['sub_session']['seltest_course']['as_hantei_type'] = $bak_as_hantei_type;
	$_SESSION['sub_session']['seltest_course']['as_course_num'] = $bak_as_course_num;
	$_SESSION['sub_session']['seltest_course']['as_hantei_default_num'] = $bak_as_hantei_default_num;
	$_SESSION['sub_session']['seltest_course']['as_form_type'] = $bak_as_form_type;
	$_SESSION['sub_session']['seltest_course']['as_page_view'] = $bak_as_page_view;

	$_SESSION['sub_session']['surala_regist']['status'] = $bk_surala_regist_status;
	$_SESSION['sub_session']['surala_regist']['problem_num'] = $bk_surala_regist_problem_num;
	$_SESSION['sub_session']['surala_regist']['disp_sort'] = $bk_surala_regist_disp_sort;

	//表示ページ番号の情報は引き続き保持し、POSTされたページ番号があれば、それを代入する。
	if ($s_page_save > 0) { $_SESSION['sub_session']['s_page'] = $s_page_save; }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }	//一覧のページ操作があった場合(ページ番号格納)

	if ($s_select_course) { $_SESSION['sub_session']['select_course'] = $s_select_course; }

	return $html;

}


/*
function ht_sub_session() {
echo "*ht_sub_session(),";
	//$_SESSION['sub_session']['add_type']['add_type'] = "";
	//$_SESSION['sub_session']['add_type']['problem_type'] = "";
	//$_SESSION['sub_session']['select_course']['form_type'] = "";
	//if (strlen($_POST['add_type'])) { $_SESSION['sub_session']['add_type']['add_type'] = $_POST['add_type']; }
	//if (strlen($_POST['problem_type'])) { $_SESSION['sub_session']['add_type']['problem_type'] = $_POST['problem_type']; }
	if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type']; }
	return;
}
*/



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
 * 判定名選択後の、判定テスト登録リスト表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $PROC_RESULT
 */
function hantei_test_list($ERROR, $PROC_RESULT) {
//echo "*hantei_test_list(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	global $L_HANTEI_PROBLEM_TYPE;

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION['myid']);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }
	unset($_SESSION['sub_session']['select_course']);

	$html = "";
	$page = 1;

	if ($ERROR) {
		$html .= "<br/><div class=\"small_error\">\n";		//インポートエラーもここで表示(既に他のエラー時表示あり)
		$html .= ERROR($ERROR);
		$html .= "</div>\n";

	} else {
		if ($_SESSION['sub_session']['surala_regist']['status'] == "3") {
			//判定テスト問題登録後、登録成功ならばメッセージ表示、新規の判定テスト問題登録
			//$d_surala_regist_problem_num = $_SESSION['sub_session']['surala_regist']['problem_num'];
			$d_surala_regist_disp_sort = $_SESSION['sub_session']['surala_regist']['disp_sort'];
			$html .= "<div class=\"small_error\">\n";
			$html .= "新規問題を、表示順 ".$d_surala_regist_disp_sort."番に";
			$html .= "登録しました。";
			$html .= "</div>\n";
			unset($_SESSION['sub_session']['surala_regist']['status']);
		}
	}

	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
		$page = $_SESSION['sub_session']['s_page'];
	}

	if ($PROC_RESULT) {	//インポート結果レポート
		$html .= "<div class=\"small_error\">\n";
		$html .= REPORT($PROC_RESULT);
		$html .= "</div>\n";
	}


	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		foreach($L_TEST_ADD_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['add_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$add_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		if ($_SESSION['sub_session']['add_type']['add_type']) {
			$problem_type_html .= "<select name=\"problem_type\" onchange=\"submit();\" style=\"float:left;\">";
			//foreach($L_TEST_PROBLEM_TYPE as $key => $val) {
			foreach($L_HANTEI_PROBLEM_TYPE as $key => $val) {
				if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && $key === "surala") { continue; }
				if ($_SESSION['sub_session']['add_type']['problem_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$problem_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
			$problem_type_html .= "</select>\n";
		}
	}


	if ($s_hantei_default_num > 0) {

//	//	ファイルアップロード用仮設定
//		//$flash_ftp = FTP_URL."flash/".$s_course_num."/";
//		$img_ftp = FTP_URL."hantei_img/".$s_hantei_default_num."/";
//		$voice_ftp = FTP_URL."hantei_voice/".$s_hantei_default_num."/";
//		//$print_ftp = FTP_URL."print/".$s_course_num."/";
//		//$tmp_ftp = FTP_URL."template/".$s_course_num."/";
//		//$chart_j_ftp = FTP_URL."chart/j/".$s_course_num."/";
//		//$chart_h_ftp = FTP_URL."chart/h/".$s_course_num."/";
//
//		$html .= "<br>\n";
//		//$html .= "<a href=\"".$flash_ftp."\" target=\"_blank\">FLASHフォルダー($flash_ftp)</a><br>\n";
//		$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
//		$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br>\n";
//		//$html .= "<a href=\"".$tmp_ftp."\" target=\"_blank\">テンプレートフォルダー($tmp_ftp)</a><br>\n";
//		//$html .= "<a href=\"".$print_ftp."\" target=\"_blank\">まとめプリントフォルダー($print_ftp)</a><br>\n";
//		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
//		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
//		$html .= "<br>\n";


		//判定テスト問題、すらら問題 それぞれを一括取得
		$sql = "(SELECT hantei_ms_default_problem.hantei_default_num,".
			" hantei_ms_default_problem.disp_sort,".
			" hantei_ms_default_problem.problem_table_type,".
			" hantei_ms_problem.problem_num,".
			" hantei_ms_problem.problem_type,".
			" hantei_ms_problem.form_type,".
			" hantei_ms_problem.standard_time".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hantei_ms_default_problem,".
			T_HANTEI_MS_PROBLEM." hantei_ms_problem".
			" WHERE hantei_ms_default_problem.hantei_default_num = '".$s_hantei_default_num."'".
			" AND hantei_ms_default_problem.mk_flg='0'".
			" AND hantei_ms_default_problem.problem_num = hantei_ms_problem.problem_num".
			" AND hantei_ms_problem.mk_flg = '0')";
		$sql .= " UNION ALL ";
		$sql .= "(SELECT hantei_ms_default_problem.hantei_default_num,".
			" hantei_ms_default_problem.disp_sort,".
			" hantei_ms_default_problem.problem_table_type,".
			" problem.problem_num,".
			" problem.problem_type,".
			" problem.form_type,".
			" problem.answer_time".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hantei_ms_default_problem,".
			T_PROBLEM." problem".
			" WHERE hantei_ms_default_problem.hantei_default_num = '".$s_hantei_default_num."'".
			" AND hantei_ms_default_problem.mk_flg='0'".
			" AND hantei_ms_default_problem.problem_num = problem.problem_num".
			" AND problem.state = '0');";
		if ($result = $cdb->query($sql)) {
			$default_test_num_count = $cdb->num_rows($result);
		}

		if ($default_test_num_count == 0) {
			$html .= "<br>\n";
			$html .= "問題は登録されておりません。<br>\n";
			return $html;

		} else {
			if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 1; }

			//表示数の選択
			if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
				$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
			} else {
				$page_view = $L_PAGE_VIEW[0];
			}
			$max_page = ceil($default_test_num_count/$page_view);


			if ($_SESSION['sub_session']['s_page']) {
				$page = $_SESSION['sub_session']['s_page'];
			} else {
				$page = 1;
			}

			if ($page > $max_page) { $page = $max_page; }
			$start = ($page - 1) * $page_view;
			$next = $page + 1;
			$back = $page - 1;


			$sql = "(SELECT hantei_ms_default_problem.hantei_default_num,".
				" hantei_ms_default_problem.disp_sort,".
				" hantei_ms_default_problem.problem_table_type,".
				" hantei_ms_problem.problem_num,".
				" hantei_ms_problem.problem_type,".
				" hantei_ms_problem.form_type,".
				" hantei_ms_problem.standard_time".
				" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hantei_ms_default_problem,".
				T_HANTEI_MS_PROBLEM." hantei_ms_problem".
				" WHERE hantei_ms_default_problem.hantei_default_num = '".$s_hantei_default_num."'".
				" AND hantei_ms_default_problem.mk_flg='0'".
				" AND hantei_ms_default_problem.problem_num = hantei_ms_problem.problem_num".
				" AND hantei_ms_problem.mk_flg = '0')";
			$sql .= " UNION ALL ";
			$sql .= "(SELECT hantei_ms_default_problem.hantei_default_num,".
				" hantei_ms_default_problem.disp_sort,".
				" hantei_ms_default_problem.problem_table_type,".
				" problem.problem_num,".
				" problem.problem_type,".
				" problem.form_type,".
				" problem.answer_time".
				" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hantei_ms_default_problem,".
				T_PROBLEM." problem".
				" WHERE hantei_ms_default_problem.hantei_default_num = '".$s_hantei_default_num."'".
				" AND hantei_ms_default_problem.mk_flg='0'".
				" AND hantei_ms_default_problem.problem_num = problem.problem_num".
				" AND problem.state = '0')";
			$sql .= " ORDER BY disp_sort";	// add hasegawa 2017/12/25 AWS移設 ソート条件追加
			$sql .= " LIMIT ".$start.",".$page_view.";";

//echo "*".$sql."<br/>";

//echo "***page=".$page.", page_view=".$page_view.", max_page=".$max_page."<br/>".$sql."<br/>";

			//ページ数
//			if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 1; }

			foreach ($L_PAGE_VIEW as $key => $val){
				if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
				$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
			}

			$sub_session_html = "";
			//$disp_hantei_name = get_hantei_name($s_hantei_default_num);
			//if (strlen($disp_hantei_name) > 0) {
			//	$sub_session_html .= "判定名「".$disp_hantei_name."」登録済み問題リスト<br/><br/>";
			//}
			
			
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
				$html .= "<div style=\"float:left;\">登録問題数(".$default_test_num_count."):PAGE[".$page."/".$max_page."]</div>\n";

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
				$html .= "<th>表示順番号</th>\n";
				$html .= "<th>問題種類</th>\n";
				$html .= "<th>出題形式</th>\n";
				$html .= "<th>回答目安時間</th>\n";
				$html .= "<th>確認</th>\n";

				//if (!ereg("practice__view",$authority)
				//	&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
				//) {
				//	$html .= "<th>詳細</th>\n";
				//}

				//if (!ereg("practice__del",$authority)
				//	&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
				//) {
				//	$html .= "<th>削除</th>\n";
				//}

				$html .= "</tr>\n";
				$j=$start;
				while ($list = $cdb->fetch_assoc($result)) {
					$j++;
					if ($list['problem_table_type'] == 1) {
						$table_type = "すらら問題";
					} elseif ($list['problem_table_type'] == 2) {
						$table_type = "判定テスト問題";
					}
					$html .= "<tr class=\"course_form_cell\">\n";
					$html .= "<td>".$list['problem_num']."</td>\n";
					$html .= "<td>".$list['disp_sort']."</td>\n";
					$html .= "<td>".$table_type."</td>\n";
					$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
					$html .= "<td>".$list['standard_time']."</td>\n";
					$html .= "<td>\n";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_view\">\n";
					$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$list['hantei_default_num']."\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					//$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."'); return false;\">\n";
					$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['hantei_default_num']."'); return false;\">\n";
					$html .= "</form>\n";
					$html .= "</td>\n";

					//if (!ereg("practice__view",$authority)
					//	&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
					//) {

					//	if (!$list['default_test_num']) { $focus_default_test_num = ""; }
					//	else { $focus_default_test_num = $list['default_test_num']; }

					//	$html .= "<td>\n";
					//	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					//	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_view\">\n";
					//	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
					//	$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$list['hantei_default_num']."\">\n";
					//	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					//	$html .= "<input type=\"submit\" id=\"problem_botton_".$focus_default_test_num."_".$list['problem_num']."\" value=\"変更\">\n";
					//	$html .= "</form>\n";
					//	$html .= "</td>\n";
					//}

					//if (!ereg("practice__del",$authority)
					//	&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
					//) {
					//	$html .= "<td>\n";
					//	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					//	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_delete\">\n";
					//	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
					//	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
					//	$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$list['hantei_default_num']."\">\n";
					//	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					//	$html .= "<input type=\"submit\" value=\"削除\">\n";
					//	$html .= "</form>\n";
					//	$html .= "</td>\n";
					//}
					$html .= "</tr>\n";
				}
				$html .= "</table>\n";

			}

		}

	}

//echo $_SESSION['t_practice']['test_type']."***<br/>";

	return $html;
}








//-------------以降は、まだ未整理


/**
 * 問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 */
function hantei_test_hanteimei_list($ERROR) {
//echo "*hantei_test_hanteimei_list(), ";
	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_UNIT_TYPE;

	$html = "";

	$hantei_default_num = "0";
	$s_service_num = "0";
	$s_hantei_type = "0";
	$s_course_num = "0";
	//$s_desc = "0";
	$s_page_view = "2";

	if (ACTION == "sub_session") {
		if (strlen($_POST['s_service_num'])) { $_SESSION['sub_session']['s_service_num'] = $_POST['s_service_num']; }
		if (strlen($_POST['s_hantei_type'])) { $_SESSION['sub_session']['s_hantei_type'] = $_POST['s_hantei_type']; }
		if (strlen($_POST['s_course_num']))	{ $_SESSION['sub_session']['s_course_num'] = $_POST['s_course_num']; }
		if (strlen($_POST['s_hantei_default_num']))	{ $_SESSION['sub_session']['s_hantei_default_num'] = $_POST['s_hantei_default_num']; }
//		if (strlen($_POST['s_desc']))		 { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	}
	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
		$hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
//		$s_desc = $_SESSION['sub_session']['s_desc'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
	}

//echo "service_num=".$s_service_num.", hantei_type=".$s_hantei_type.", course_num=".$s_course_num.", hantei_default_num=".$hantei_default_num."<br/>";

		$html .= "<br/><br/>";
//	}

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
function toeic_test_problem_list($ERROR) {
	$html = "";
	return $html;
}
?>