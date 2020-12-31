<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題設定/判定テスト
 *
 * 履歴
 * 2013/09/17 初期設定
 *
 * @author Azet
 */

// okabe

//"サービス"セレクトの非表示



/**
 * 判定テストHTMLを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function hantei_test_start() {
/*if (ACTION != "ht_export" && ACTION != "ht_import") {
 echo "MODE=".MODE.", ACTION=".ACTION;
// echo "<br/>sub_session:";
// print_r($_SESSION['sub_session']);
 echo "<hr/>";
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
}
*/
	define(DISABLE_SERVICE_NAME, 1);	//service_name  カラム処理スイッチ(=1ならば出力しない)

	// DB接続オブジェクト // add start 2017/05/24 yoshizawa
	$cdb = $GLOBALS['cdb'];

	// add start 2017/05/24 yoshizawa
	// 同時に処理がされたときに不正なデータができないように
	// トランザクションを追加して処理がぶつかることを防ぎます。
	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
	}
	// add end 2017/05/24 yoshizawa

	$PROC_RESULT = array();

	if (ACTION == "ht_add") {
		$ERROR = add();

	} elseif (ACTION == "ht_change") {	//登録済み判定テスト問題[変更]→[修正]ボタン押下時
		$ERROR = ht_change();

	} elseif (ACTION == "ht_delete") {	//削除→確認画面にて[削除]押下時
		$ERROR = ht_change();

	} elseif (ACTION == "sub_course_session") {
		$ERROR = sub_setpage_session();

	} elseif (ACTION == "sub_test_session") {	//既存のテスト問題を選択、を選択後にテストタイプを選んだ後
		$ERROR = sub_seltest_session();

	} elseif (ACTION == "ht_exist_check") {		//既存のテスト問題を選んで、[追加確認]を押したときの処理
		$ERROR = ht_test_exist_check();

	} elseif (ACTION == "ht_exist_add") {		//既存のテスト問題を、最終的に[登録]する処理
		$ERROR = ht_test_exist_add();

	} elseif (ACTION == "ht_surala_check") {	//既存のすらら問題を選んで、[追加確認]を押したときの処理
		$ERROR = ht_surala_add_check();

	} elseif (ACTION == "ht_surala_add") {		//既存のすらら問題を、最終的に[登録]する処理
		$ERROR = ht_surala_add_add();

	} elseif (ACTION == "ht_problem_check") {	//(新規登録) 入力フォームで [追加] 押下時
		ht_sub_session();
		$ERROR = ht_test_add_check();	//入力項目チェック

	} elseif (ACTION == "ht_problem_add") {		//(新規登録) 入力フォーム[追加]→確認画面で[登録]押下時
		$ERROR = ht_test_add_add();

	} elseif (ACTION == "ht_export") {			//エクスポート
		ht_csv_export();

	} elseif (ACTION == "ht_import") {			//インポート
		//list($ERROR, $PROC_RESULT) = ht_csv_import();
		list($ERROR, $PROC_RESULT) = ht_csv_problem_import();	// chenged 2013/09/17

	}

	// add start 2017/05/24 yoshizawa
	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
	}
	// add end 2017/05/24 yoshizawa

	$html .= ht_select_unit_view();


	if (MODE == "ht_view") {			//登録済み問題リスト画面の [変更]ボタンを押したとき
		$html .= view($ERROR);		//入力フォーム（修正フォームと共用）

	} elseif (MODE == "ht_add") {

		//if ($_SESSION['sub_session']['add_type']['problem_type'] == "surala") {			//'既存の' 'すらら問題' 登録
		if ($_SESSION['sub_session']['add_type']['problem_type'] != "test") {			//'既存の' 'すらら問題' 登録	// 2013/0913
			if (ACTION == "ht_surala_check") {
				if (!$ERROR) {
						//エラー無し
					$html .= ht_surala_add_check_html();
				} else {
						//エラー有り
					$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題選択フォーム表示時
				}
			} elseif (ACTION == "ht_surala_add") {
				if (!$ERROR) {
						//エラー無し
						//$html .= hantei_test_list($ERROR);
						$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題の登録後、stage,lesson,unitなど選択済みの画面へ戻す
				} else {
						//エラー有り
						$html .= hantei_test_list($ERROR, $PROC_RESULT);
				}
			} else {
				$html .= ht_surala_add_addform($ERROR);		//既存のすらら問題選択フォーム表示時
			}

		} elseif($_SESSION['sub_session']['add_type']['problem_type'] == "test") {		//'新規の' 'テスト問題' 登録
			if ($_SESSION['sub_session']['add_type']['add_type'] == "add") {
				if (ACTION == "ht_problem_check") {
					if (!$ERROR) {		//入力フォームで [追加] 押下時
						//エラー無し
						$html .= ht_test_add_check_html();
					} else {
						//エラー有り
						//$html .= test_add_addform($ERROR);
						$html .= addform($ERROR);
					}
				} elseif (ACTION == "ht_problem_add") {
					if (!$ERROR) { 		//入力フォーム[追加]→確認画面で[登録]押下時
						//エラー無し
						$html .= hantei_test_list($ERROR, $PROC_RESULT);
					} else {
						//エラー有り
						$html .= addform($ERROR);
					}
				} else {	//入力フォーム表示時
					$html .= addform($ERROR);
				}

			} elseif ($_SESSION['sub_session']['add_type']['add_type'] == "exist") {		//'既存の' '判定テスト問題' 登録
				if (ACTION == "ht_exist_check") {
					if (!$ERROR) {
						//エラー無し
						$html .= ht_test_exist_check_html();
					} else {
						//エラー有り
						$html .= ht_test_exist_addform($ERROR);
					}
				} elseif (ACTION == "ht_exist_add") {
					if (!$ERROR) {
						//エラー無し
						//$html .= $ERROR);
						//$html .= hantei_test_list($ERROR);
						$html .= ht_test_exist_addform($ERROR);		//既存の判定テスト問題登録後、既存問題選択ドロップダウンの画面へ戻す
					} else {
						//エラー有り
						$html .= hantei_test_list($ERROR, $PROC_RESULT);
					}
				} else {
					$html .= ht_test_exist_addform($ERROR);		//既存の判定テスト問題選択フォーム表示時
				}
			}
		}


	} elseif (MODE == "ht_delete") {
		if (ACTION == "ht_delete") {
			if (!$ERROR) {		//エラー無し
				$html .= hantei_test_list($ERROR, $PROC_RESULT);

			} else {			//エラー有り
				$html .= hantei_test_list($ERROR, $PROC_RESULT);

			}

		} else {
			$html .= ht_test_add_check_html();		//削除操作の初期画面
		}



	} else {
		$html .= hantei_test_list($ERROR, $PROC_RESULT);
	}

	return $html;
}




/**
 * ユニット選択HTMLを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_select_unit_view() {
//echo "*ht_select_unit_view(),"; // hantei_default_num=".$s_hantei_default_num."<br/>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_TYPE, $L_HANTEI_TYPE, $L_DESC, $L_PAGE_VIEW;
	global $L_TEST_ADD_TYPE, $L_TEST_PROBLEM_TYPE;

	unset($_SESSION['UPDATE']);	//add okabe 2013/09/13

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
			// upd start hasegawa 2017/12/25 AWS移設 ソート条件追加
			// " AND service_course_list.service_num ='".$s_service_num."';";
			" AND service_course_list.service_num ='".$s_service_num."'".
			" ORDER BY course.list_num;";
			// upd end hasegawa 2017/12/25 AWS移設 ソート条件追加

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
		$sql .= " ORDER BY hantei_ms_default.hantei_name";

		$sql .= ";";
		if ($result = $cdb->query($sql)) {
			$default_test_num_count = $cdb->num_rows($result);
			if ($default_test_num_count > 0) {
				$chk_flg = 0;
				while ($list = $cdb->fetch_assoc($result)) {
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



/**
 * POSTに対してsession設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function ht_sub_session() {
//echo "*ht_sub_session(),";
	if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type']; }
	return;
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
 * 判定テスト一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $PROC_RESULT
 * @return string HTML
 */
function hantei_test_list($ERROR, $PROC_RESULT) {	//判定名選択後の、判定テスト登録リスト表示
//echo "*hantei_test_list(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	global $L_HANTEI_PROBLEM_TYPE;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

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



	//サービスだけが選択されていたら、インポート/エクスポート操作を出来るようにする（表示する）
	if ($s_service_num > 0 && ($s_hantei_type < 1 || $s_hantei_type > 2)) {
		$sql  = "SELECT service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0'".
			" AND service_num='".$s_service_num."';";

		$service_name = "";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$service_name = $list['service_name'];
		}

		//	ファイルアップロード用仮設定
		//service_num から write_type を決定して返す
		$s_write_type = get_write_type_from_svc($s_service_num);
		$html .= ftp_dir_html($s_write_type);


		$html .= "<br><strong>サービス: ".$service_name."</strong><br>\n";
		$html .= "インポートする場合は、問題設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_import\">\n";
		$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$s_service_num."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_export\">\n";
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

	}


	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		foreach($L_TEST_ADD_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['add_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$add_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		if ($_SESSION['sub_session']['add_type']['add_type']) {
			$problem_type_html .= "<select name=\"problem_type\" onchange=\"submit();\" style=\"float:left;\">";
			foreach($L_HANTEI_PROBLEM_TYPE as $key => $val) {
				//if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && ($key === "surala" || $key === "toeic") ) { continue; }
				if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && ($key != "0" && $key != "test") ) { continue; }	// 2013/09/13
				if ($_SESSION['sub_session']['add_type']['problem_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
				$problem_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
			$problem_type_html .= "</select>\n";
		}

		if ($_SESSION['t_practice']['test_type'] == "hantei_test" && $s_hantei_default_num > 0) {
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
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_add\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "を<input type=\"submit\" value=\"登録\">\n";
				$html .= "</form>\n";
			}
			$_SESSION['sub_session']['add_type']['form_type'] = "";
		}
		$html .= "<br>";
	}


	//現在、登録されている問題リスト表示
	if ($s_hantei_default_num > 0) {

		$sql = "(SELECT hmdp.hantei_default_num,".
			" hmdp.disp_sort,".
			" hmdp.problem_table_type,".
			" hmp.problem_num,".
			" hmp.problem_type,".
			" hmp.form_type,".
			" hmp.standard_time".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp,".
			T_HANTEI_MS_PROBLEM." hmp".
			" WHERE hmdp.hantei_default_num = '".$s_hantei_default_num."'".
			" AND hmdp.mk_flg='0'".
			" AND hmdp.problem_table_type='2'".
			" AND hmdp.problem_num = hmp.problem_num".
			" AND hmp.mk_flg = '0')";
		$sql .= " UNION ALL ";
		$sql .= "(SELECT hmdp.hantei_default_num,".
			" hmdp.disp_sort,".
			" hmdp.problem_table_type,".
			" problem.problem_num,".
			" problem.problem_type,".
			" problem.form_type,".
			" problem.answer_time".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp,".
			T_PROBLEM." problem".
			" WHERE hmdp.hantei_default_num = '".$s_hantei_default_num."'".
			" AND hmdp.problem_table_type='1'".
			" AND hmdp.mk_flg='0'".
			" AND hmdp.problem_num = problem.problem_num".
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

			$sql = "(SELECT hmdp.hantei_default_num,".
				" hmdp.disp_sort,".
				" hmdp.problem_table_type,".
				" hmp.problem_num,".
				" '' AS course_name,".	//add okabe 2013/09/13 カラム数調整用Dummy
				" hmp.problem_type,".
				" hmp.form_type,".
				" hmp.standard_time".
				" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp,".
				T_HANTEI_MS_PROBLEM." hmp".
				" WHERE hmdp.hantei_default_num = '".$s_hantei_default_num."'".
				" AND hmdp.mk_flg='0'".
				" AND hmdp.problem_table_type='2'".
				" AND hmdp.problem_num = hmp.problem_num".
				" AND hmp.mk_flg = '0')";
			$sql .= " UNION ALL ";
			$sql .= "(SELECT hmdp.hantei_default_num,".
				" hmdp.disp_sort,".
				" hmdp.problem_table_type,".
				" problem.problem_num,".
				" course.course_name,".			//add okabe 2013/09/13
				" problem.problem_type,".
				" problem.form_type,".
				" problem.answer_time".
				" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp,".
				T_PROBLEM." problem".
				" LEFT JOIN ".T_COURSE." course".		//add okabe 2013/09/13
				" ON course.course_num = problem.course_num ".//AND scl.course_type = '1'".		//add okabe 2013/09/13
				" WHERE hmdp.hantei_default_num = '".$s_hantei_default_num."'".
				" AND hmdp.problem_table_type='1'".
				" AND hmdp.mk_flg='0'".
				" AND hmdp.problem_num = problem.problem_num".
				" AND problem.state = '0')";
			$sql .= " LIMIT ".$start.",".$page_view.";";

			//ページ数
			foreach ($L_PAGE_VIEW as $key => $val){
				if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
				$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
			}

			$sub_session_html = "";
			$disp_hantei_name = get_hantei_name($s_hantei_default_num);
			if (strlen($disp_hantei_name) > 0) {
				$sub_session_html .= "判定名「".$disp_hantei_name."」登録済み問題リスト<br/><br/>";
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
						//$table_type = "すらら問題";
						$table_type = $list['course_name'];
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
					$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('".$list['problem_table_type']."','".$list['problem_num']."','".$list['hantei_default_num']."'); return false;\">\n";
					$html .= "</form>\n";
					$html .= "</td>\n";

					if (!ereg("practice__view",$authority)
						&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
					) {

						if (!$list['default_test_num']) { $focus_default_test_num = ""; }
						else { $focus_default_test_num = $list['default_test_num']; }

						$html .= "<td>\n";
						$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_view\">\n";
						$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
						$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$list['hantei_default_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
						$html .= "<input type=\"submit\" id=\"problem_botton_".$focus_default_test_num."_".$list['problem_num']."\" value=\"変更\">\n";
						$html .= "</form>\n";
						$html .= "</td>\n";
					}

					if (!ereg("practice__del",$authority)
						&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
					) {
						$html .= "<td>\n";
						$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_delete\">\n";
						$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
						$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
						$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$list['hantei_default_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
						$html .= "<input type=\"submit\" value=\"削除\">\n";
						$html .= "</form>\n";
						$html .= "</td>\n";
					}
					$html .= "</tr>\n";
				}
				$html .= "</table>\n";

			}

		}

	}

	return $html;
}



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
	$s_page_view = "2";

	if (ACTION == "sub_session") {
		if (strlen($_POST['s_service_num'])) { $_SESSION['sub_session']['s_service_num'] = $_POST['s_service_num']; }
		if (strlen($_POST['s_hantei_type'])) { $_SESSION['sub_session']['s_hantei_type'] = $_POST['s_hantei_type']; }
		if (strlen($_POST['s_course_num']))	{ $_SESSION['sub_session']['s_course_num'] = $_POST['s_course_num']; }
		if (strlen($_POST['s_hantei_default_num']))	{ $_SESSION['sub_session']['s_hantei_default_num'] = $_POST['s_hantei_default_num']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	}
	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
		$hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
	}

	$html .= "<br/><br/>";
}



/**
 * 判定名の選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {
//echo "*select_menu(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_FORM_TYPE;

	$html .= "<br style=\"clear:left;\">\n";
	$html .= $msg;


	if ($_POST['select_reset'] || $_SESSION['SELECT_VIEW']['block_num'] != $_POST[block_num]) {
		unset($_SESSION['SELECT_VIEW']);
	} elseif ($_POST['action'] == "select") {
		foreach ($_POST AS $key => $val) {
			$$key = $val;
		}
		unset($_SESSION['SELECT_VIEW']);
	} elseif ($_SESSION['SELECT_VIEW']) {
		foreach ($_SESSION['SELECT_VIEW'] AS $key => $val) {
			$$key = $val;
		}
	}
	if ($_POST[block_num]) { $_SESSION['SELECT_VIEW']['block_num'] = $_POST[block_num]; }


	//	問題形式	view_form_type
	$l_view_form_type = "<select name=\"view_form_type\">\n";
	if ($view_form_type == "") { $selected = "selected"; } else { $selected = ""; }
	$l_view_form_type .= "<option value=\"\" $selected>----------</option>\n";
	$sql  = "SELECT form_type FROM ".T_PROBLEM.
			" WHERE course_num='$_POST[course_num]' AND block_num='$_POST[block_num]' AND state!='1'".
			" GROUP BY form_type ORDER BY form_type;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$form_type = $list['form_type'];
			if ($view_form_type == $form_type) { $selected = "selected"; } else { $selected = ""; }
			$l_view_form_type .= "<option value=\"$form_type\" $selected>{$L_FORM_TYPE[$form_type]}</option>\n";
		}
	}
	$l_view_unit_num .= "</select>\n";
	$_SESSION['SELECT_VIEW']['view_form_type'] = $view_form_type;
	$MENU[0][name] = "問題形式";
	$MENU[0][value] = $l_view_form_type;


	//	問題番号	view_display_number
	$_SESSION['SELECT_VIEW']['view_display_number'] = $view_display_number;
	$MENU[1][name] = "問題番号";
	$MENU[1][value] = "<input type=\"text\" size=\"10\" name=\"view_display_number\" value=\"$view_display_number\">\n";


	//	表示数	view_num
	if ($view_num == "") { $view_num = 10; }
	$l_view_num = "<select name=\"view_num\">\n";
	for ($i=1; $i<=10; $i++) {
		$val = $i * 10;
		if ($view_num == $val) { $selected = "selected"; } else { $selected = ""; }
		$l_view_num .= "<option value=\"{$val}\" $selected>{$val}</option>\n";
	}
	$l_view_num .= "</select>\n";
	$_SESSION['SELECT_VIEW']['view_num'] = $view_num;
	$MENU[2][name] = "表示数";
	$MENU[2][value] = $l_view_num;


	//	送信ボタン
	$MENU[4][name] = "&nbsp;";
	$MENU[4][value] = "<input type=\"submit\" value=\"絞込\"> <input type=\"submit\" name=\"select_reset\" value=\"リセット\">\n";

	foreach ($MENU AS $key => $VAL) {
		$name_ = $VAL[name];
		$values_ = $VAL[value];
		if (!$values_) { continue; }
		$form_menu .= "<td>$name_</td>";
		$form_cell .= "<td>$values_</td>";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"select\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= $form_menu;
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= $form_cell;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	$html .= "<br style=\"clear:left;\">\n";

	return $html;
}



/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
//echo "*addform(), ";
	global $L_PROBLEM_TYPE,$L_FORM_TYPE;

	$s_hantei_default_num = "0";
	if ($_SESSION['sub_session']) {
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
	}

	$html = "新規のテスト問題登録<br/>\n";

	if ($_SESSION['sub_session']['add_type']['form_type']) {
		$form_type = $_SESSION['sub_session']['add_type']['form_type'];
	} else {
		if ($_POST['form_type']) {
			$form_type = $_POST['form_type'];
		}
	}
	// add start hasegawa 2016/06/02 作図ツール
	if($_POST['drawing_type']) {
		$drawing_type = $_POST['drawing_type'];
	} else {
		$drawing_type = $_POST['option1'];
	}
	// add end hasegawa 2016/06/02

	$html .= select_ht_form_type();

	if (!$form_type || ($form_type == 13 && !$drawing_type)) {	// upd hasegawa 2016/06/01 作図ツール
		$html .= "<br/>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"$_POST[hantei_default_num]\">\n";

		$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
		$html .= "</form>\n";

		unset($_SESSION['UPDATE']);

	} else {
		//form_type が選択されている場合
		$_SESSION['sub_session']['add_type']['form_type'] = $form_type;

		$html .= view($ERROR);		//入力フォーム（修正フォームと共用）
	}

	return $html;
}


/**
 * 問題登録　テスト専用問題新規登録　フォームタイプ選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_ht_form_type() {
//echo "*select_ht_form_type(), ";
	global $L_FORM_TYPE, $L_DRAWING_TYPE;	// upd hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE追加

	unset($L_FORM_TYPE[14]);	// add hasegawa 2018/03/23 百マス計算
	unset($L_FORM_TYPE[15]);	// add kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //「書写」
	unset($L_FORM_TYPE[16]); //意味 //add kimura 2018/11/22 すらら英単語 _admin
	unset($L_FORM_TYPE[17]); //書く //add kimura 2018/11/22 すらら英単語 _admin

	// add start hasegawa 2016/06/02 作図ツール
	if($_POST['drawing_type']) {
		$drawing_type = $_POST['drawing_type'];
	} else {
		$drawing_type = $_POST['option1'];
	}
	// add end hasegawa 2016/06/02

	if (!$_SESSION['sub_session']['add_type']['form_type']) {
		//フォームタイプ
		$form_type_html = "<option value=\"0\">選択して下さい</option>\n";
		foreach($L_FORM_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['form_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$form_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		$select_menu = "<td>\n";
		$select_menu .= "<select name=\"form_type\" onchange=\"submit();\">".$form_type_html."</select>\n";
		$select_menu .= "</td>\n";

		$msg_html .= "登録する問題のフォームタイプを選択してください。<br/>";

		$html = "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"test_menu\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_form_type_session\">\n";
		$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
		$html .= "<table class=\"unit_form\">\n";
		$html .= "<tr class=\"unit_form_menu\">\n";
		$html .= "<td>フォームタイプ</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"unit_form_cell\">\n";
		$html .= $select_menu;
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
		//$html .= "<br>\n";

	// add start hasegawa 2016/06/01 作図ツール
	} elseif($_SESSION['sub_session']['select_course']['form_type'] == 13 && !$drawing_type){
		//フォームタイプ
		$drawing_type_html = "<option value=\"0\">選択して下さい</option>\n";
		foreach($L_DRAWING_TYPE as $key => $val) {
			// update start 2016/08/30 yoshizawa 作図ツール
			//if ($drawing_type == $key) { $selected = "selected"; } else { $selected = ""; }
			//$drawing_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			//
			if( $key == 1 || $key == 2 ){ $disabled = "disabled=\"disabled\""; }else{ $disabled = ""; }
			if ($drawing_type == $key) { $selected = "selected"; } else { $selected = ""; }
			$drawing_type_html .= "<option value=\"".$key."\" ".$selected." ".$disabled.">".$val."</option>\n";
			// update end 2016/08/30 yoshizawa 作図ツール

		}
		$select_menu = "<td>\n";
		$select_menu .= "<select name=\"drawing_type\" onchange=\"submit();\">".$drawing_type_html."</select>\n";
		$select_menu .= "</td>\n";

		$msg_html .= "登録する作図の問題種類を選択してください。<br/>";

		$html = "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"test_menu\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_form_type_session\">\n";
		$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
		$html .= "<table class=\"unit_form\">\n";
		$html .= "<tr class=\"unit_form_menu\">\n";
		$html .= "<td>問題種類</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"unit_form_cell\">\n";
		$html .= $select_menu;
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
	}
	// add end hasegawa 2016/06/01
	if ($msg_html) {
		$html .= "<br>\n";
		$html .= $msg_html;
	}

	return $html;
}



/**
 * 入力フォームおよび、登録済み問題リスト画面の [変更]ボタンを押したとき
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function view($ERROR) {
//echo "*view(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_SENTENCE_FLAG,$L_DRAWING_TYPE;// add oda $L_SENTENCE_FLAG 2012/12/26	// upd hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発
	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";

	} elseif (ACTION == "ht_add") {
		$html .= "<div class=\"small_error\">\n";
		$html .= "<br><strong>問題情報が追加されました。</strong>\n";
		$html .= "</div>\n";

	} elseif (ACTION == "ht_change") {
		$html .= "<div class=\"small_error\">\n";
		$html .= "<br><strong>問題情報が更新されました。</strong>\n";
		$html .= "</div>\n";
	}

	$problem_num = $_POST['problem_num'];	//add okabe 2013/09/03

	if (MODE == "ht_view" && !$ERROR) {
		$sql  = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hmdp.hantei_default_num='".$_POST[hantei_default_num]."'".
				" AND hmdp.mk_flg='0'".
				" AND hmdp.problem_num='".$_POST[problem_num]."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list=$cdb->fetch_assoc($result);
		}
		if ($list) {
			foreach ($list as $key => $val) {
				$val = ereg_replace("\n","//",$val);
				$val = ereg_replace("&nbsp;"," ",$val);
				$$key = $val;
			}


			if ($list['problem_table_type'] == 2) {	//判定テスト問題

				$sql = "SELECT * FROM ".T_HANTEI_MS_PROBLEM." hmp".
					" WHERE hmp.problem_num='".$_POST['problem_num']."'".
					" AND hmp.mk_flg='0'";
				if ($result = $cdb->query($sql)) {
					$list2 = $cdb->fetch_assoc($result);
					if ($list2) {
						foreach ($list2 as $key => $val) {
							$val = ereg_replace("\n","//",$val);
							$val = ereg_replace("&nbsp;"," ",$val);
							$$key = $val;
						}
					}
				}
			} elseif ($list['problem_table_type'] == 1) {	//すらら問題
				// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
				// $sql = "SELECT * ".
				// 	" FROM ".T_PROBLEM." problem".
				// 	" WHERE problem.problem_num='".$_POST['problem_num']."'".
				// 	" AND problem.state='0'";
				$sql = "SELECT".
					" p.problem_num, p.course_num, p.block_num, p.display_problem_num,".
					" p.problem_type, p.sub_display_problem_num, p.form_type, p.question,".
					" p.problem, p.voice_data, p.hint, p.explanation, p.answer_time,".
					" p.parameter, p.set_difficulty, p.hint_number, p.correct_number,".
					" p.clear_number, p.first_problem, p.latter_problem, p.selection_words,".
					" p.correct, p.option1, p.option2, p.option3, p.option4, p.option5,".
					" p.sentence_flag, pd.number_of_answers, pd.number_of_incorrect_answers,".
					" pd.correct_answer_rate, pd.auto_difficulty, p.error_msg,".
					" p.update_time, p.display, p.state".
					" FROM ".T_PROBLEM." p".
					" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num".
					" WHERE p.problem_num='".$_POST['problem_num']."'".
					" AND p.state='0'";
				// upd end hasegawa 2018/04/03
				if ($result = $cdb->query($sql)) {
					$list2 = $cdb->fetch_assoc($result);
				}
			}
		}

		$action = "ht_change";
		$button = "修正";


	} else {	// MODE == ht_add の場合も、ここにて処理
	//add okabe start 2013/09/03
		$sql  = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hmdp.hantei_default_num='".$_POST['hantei_default_num']."'".
				" AND hmdp.mk_flg='0'".
				" AND hmdp.problem_num='".$_POST['problem_num']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list=$cdb->fetch_assoc($result);
		}

	if ($list['problem_table_type'] == 1) {	//すらら問題
				$sql = "SELECT * ".
					" FROM ".T_PROBLEM." problem".
					" WHERE problem.problem_num='".$_POST['problem_num']."'".
					" AND problem.state='0'";
				if ($result = $cdb->query($sql)) {
					$list2 = $cdb->fetch_assoc($result);
				}
	} else {	//判定テスト問題
	//add okabe end 2013/09/03

		if ($_SESSION['sub_session']['add_type']['form_type']) { $form_type = $_SESSION['sub_session']['add_type']['form_type']; }

		//入力フォームのデータを $_SESSION['UPDATE'] から取り出しを試みる、無ければ $_POST から試みる
		if ($_SESSION['UPDATE']) {
			$display_problem_num = $_SESSION['UPDATE']['display_problem_num'];
			$problem_type = $_SESSION['UPDATE']['problem_type'];
			$form_type = $_SESSION['UPDATE']['form_type'];
			$question = $_SESSION['UPDATE']['question'];
			$problem = $_SESSION['UPDATE']['problem'];
			$voice_data = $_SESSION['UPDATE']['voice_data'];
			$hint = $_SESSION['UPDATE']['hint'];
			$explanation = $_SESSION['UPDATE']['explanation'];
			//$answer_time = $_SESSION['UPDATE']['answer_time'];
			$standard_time = $_SESSION['UPDATE']['standard_time'];
			$hint_number = $_SESSION['UPDATE']['hint_number'];
			$first_problem = $_SESSION['UPDATE']['first_problem'];
			$latter_problem = $_SESSION['UPDATE']['latter_problem'];
			$selection_words = $_SESSION['UPDATE']['selection_words'];
			$correct = $_SESSION['UPDATE']['correct'];
			$option1 = $_SESSION['UPDATE']['option1'];
			$option2 = $_SESSION['UPDATE']['option2'];
			$option3 = $_SESSION['UPDATE']['option3'];
			$option4 = $_SESSION['UPDATE']['option4'];
			$option5 = $_SESSION['UPDATE']['option5'];
			$display = $_SESSION['UPDATE']['display'];

		} elseif ($_POST) {
			foreach ($_POST as $key => $val) {
				$val = stripslashes($val);
				$val = ereg_replace("\n","//",$val);
				$val = ereg_replace("&nbsp;"," ",$val);
				$$key = $val;
			}
		}

	}	//add okabe 2013/09/03

		//$problem_num = $_POST['problem_num'];	//add okabe 2013/09/03

		if ($problem_num) {
			$action = "ht_change";
			$button = "修正";
			$mode_msg = "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";	//del okabe 2013/09/03
			$mode_msg = "<input type=\"hidden\" name=\"mode\" value=\"ht_view\">\n";	//add okabe 2013/09/03

		} else {
			$action = "ht_problem_check";
			$button = "追加確認";
		}
	}

	//	エラー作成
	if ($error_msg) {
		$ERROR_LIST = explode("//",$error_msg);
		if ($ERROR_LIST) {
			foreach ($ERROR_LIST AS $val) {
				list($key,$value) = explode("::",$val);
				if ($key == "" || !$value) { continue; }
				$ERROR[$key] = $value;
			}
		}
	}

	if ($ERROR) {
		foreach ($ERROR AS $key => $val) {
			if (!$key) { continue; }
			$key = strtoupper($key);
			$key_name = "ERROR".ereg_replace("[^A-Z1-9]","",$key);
			$val = "　<span class=\"small_error\">&lt;&lt;".$val."</span>\n";
			$INPUTS[$key_name] = array('result'=>'plane','value'=>$val);
		}
	}


	if ($list['problem_table_type'] == 2 || strlen($list['problem_table_type']) == 0) {		//判定テスト問題、新規登録の場合も含む

		if ($problem_num) {
			$INPUTS[PROBLEMNUM] = array('result'=>'plane','value'=>$problem_num."<input type=\"hidden\" name=\"problem_num\" value=\"".$problem_num."\">");
		} else {
			$INPUTS[PROBLEMNUM] = array('result'=>'plane','value'=>"---");
		}
		if ($disp_sort) { $display_problem_num = $disp_sort; }	//DB内では 'disp_sort'、submit時は 'display_problem_num' で処理。
		$INPUTS[DISPSORT] = array('type'=>'text','name'=>'display_problem_num','size'=>'10','value'=>$display_problem_num);

		// add start hasegawa 2016/06/02 作図ツール
		$drawing_html = "";
		if($_SESSION['sub_session']['add_type']['form_type'] ==13 || $form_type == 13) {
			if(!$drawing_type) { $drawing_type = $option1; }
			$INPUTS[DRAWINGTYPE] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
			$drawing_html = "<input type=\"hidden\" name=\"option1\" value=\"$drawing_type\">\n";
		}
		// add end hasegawa 2016/06/02

		$INPUTS[PROBLEMTYPE] = array('type'=>'select','name'=>'problem_type','array'=>$L_PROBLEM_TYPE,'check'=>$problem_type);
		$INPUTS[FORMTYPE] = array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);
		$INPUTS[QUESTION] = array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$question);
		$INPUTS[PROBLEM] = array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$problem);
		$INPUTS[VOICEDATA] = array('type'=>'text','name'=>'voice_data','value'=>$voice_data,'size'=>'50');
		$INPUTS[HINT] = array('type'=>'textarea','name'=>'hint','cols'=>'50','rows'=>'5','value'=>$hint);
		$INPUTS[EXPLANATION] = array('type'=>'textarea','name'=>'explanation','cols'=>'50','rows'=>'5','value'=>$explanation);
		//$INPUTS[ANSWERTIME] = array('type'=>'text','name'=>'answer_time','value'=>$answer_time);
		$INPUTS[ANSWERTIME] = array('type'=>'text','name'=>'standard_time','value'=>$standard_time);
		$INPUTS[PARAMETER] = array('type'=>'text','name'=>'parameter','value'=>$parameter,'size'=>'50');
		$INPUTS[SETDIFFICULTY] = array('type'=>'text','name'=>'set_difficulty','value'=>$set_difficulty);
		$INPUTS[HINTNUMBER] = array('type'=>'text','name'=>'hint_number','value'=>$hint_number);
		$INPUTS[CORRECTNUMBER] = array('type'=>'text','name'=>'correct_number','value'=>$correct_number);
		$INPUTS[CLEARNUMBER] = array('type'=>'text','name'=>'clear_number','value'=>$clear_number);
		$INPUTS[FIRSTPROBLEM] = array('type'=>'textarea','name'=>'first_problem','cols'=>'50','rows'=>'5','value'=>$first_problem);
		$INPUTS[LATTERPROBLEM] = array('type'=>'textarea','name'=>'latter_problem','cols'=>'50','rows'=>'5','value'=>$latter_problem);
		if ($form_type !=4 && $form_type != 8 && $form_type != 10) {
			$INPUTS[SELECTIONWORDS] = array('type'=>'text','name'=>'selection_words','value'=>$selection_words,'size'=>'50');
		} else {
			$INPUTS[SELECTIONWORDS] = array('type'=>'textarea','name'=>'selection_words','cols'=>'50','rows'=>'5','value'=>$selection_words);
		}
		$INPUTS[CORRECT] = array('type'=>'text','name'=>'correct','value'=>$correct,'size'=>'50');
		$INPUTS[OPTION1] = array('type'=>'text','name'=>'option1','value'=>$option1,'size'=>'50');
//		$INPUTS[OPTION2] = array('type'=>'text','name'=>'option2','value'=>$option2,'size'=>'50');	// del hasegawa 2016/10/26 入力フォームサイズ指定項目追加

		// ---- add start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		// form_type3 選択時はoption2の項目を解答欄行数・解答欄サイズにする
		if($form_type == 3 ) {				

			if($option2) {
				list($input_row,$input_size) = get_option2($option2);
			}

			// 解答欄行数
			$INPUTS['INPUTROW'] = array('type'=>'text','name'=>'input_row','value'=>$input_row,'size'=>'50');
			// 解答欄サイズ
			$INPUTS['INPUTSIZE'] = array('type'=>'text','name'=>'input_size','value'=>$input_size,'size'=>'50');
			$input_size_att = "<br><span style=\"color:red;\">※指定する場合は、最大40までの値を設定してください。</span>";
			$INPUTS['INPUTSIZEATT'] = array('result'=>'plane','value'=>$input_size_att);
		} else {
			$INPUTS['OPTION2'] = array('type'=>'text','name'=>'option2','value'=>$option2,'size'=>'50');
		}
		// ---- add end hasegawa 2016/10/26 入力フォームサイズ指定項目追加

		$INPUTS['OPTION3'] = array('type'=>'text','name'=>'option3','value'=>$option3,'size'=>'50');
		// add start karasawa 2019/07/19 BUD英語解析開発
		if($form_type == 3 || $form_type == 4 || $form_type == 10){	
			if ($option3 == "") { $option3 = "0"; }
			$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
			$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
			$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);	
		}
		// add end karasawa 2019/07/19
		$INPUTS[OPTION4] = array('type'=>'text','name'=>'option4','value'=>$option4,'size'=>'50');
		$INPUTS[OPTION5] = array('type'=>'text','name'=>'option5','value'=>$option5,'size'=>'50');

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
		$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

		$inc_file = "hantei_test_form_type_" . $form_type . ".htm";
		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file("$inc_file");
		$make_html->set_rep_cmd($INPUTS);

		$html .= "<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"problem_form\">\n";
		$html .= $mode_msg;
		$html .= "<input type=\"hidden\" name=\"action\" value=\"$action\">\n";
		$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"$_POST[hantei_default_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"form_type\" value=\"$form_type\">\n";
		$html .= $drawing_html;			// add hasegawa 2016/06/02 作図ツール
		$html .= $make_html->replace();
		$html .= "<input type=\"submit\" value=\"$button\">";
		if (MODE == "ht_add") {
			$html .= "<input type=\"button\" value=\"確認\" disabled>";
		} else {
			$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('2','".$problem_num."','".$_POST[hantei_default_num]."'); return false;\">";
		}
		$html .= "</form>\n";


	} elseif ($list['problem_table_type'] == 1) {

		//すらら問題の場合
		$html .= "<br/>すらら問題詳細<br/>\n";

		$disp_sort = "";
		$sql = "SELECT hmdp.disp_sort ".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hmdp.problem_num='".$_POST['problem_num']."'".
				" AND hmdp.problem_table_type='1'".
				" AND hmdp.hantei_default_num='".$_POST[hantei_default_num]."'".
				" AND hmdp.mk_flg='0'";
		if ($result = $cdb->query($sql)) {
			$list3 = $cdb->fetch_assoc($result);
			if ($list3) { $disp_sort = $list3['disp_sort']; }
		}


		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>問題管理番号</td>\n";
		$html .= "<td>表示順番号</td>\n";
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
		$html .= "<td>".$list2['problem_num']."</td>\n";
		$html .= "<td>".$disp_sort."</td>\n";
		$html .= "<td>".$L_PROBLEM_TYPE[$list2['problem_type']]."</td>\n";
		$html .= "<td>".$L_FORM_TYPE[$list2['form_type']]."</td>\n";
		$html .= "<td>".$list2['number_of_answers']."</td>\n";
		$html .= "<td>".$list2['number_of_incorrect_answers']."</td>\n";
		$html .= "<td>".$list2['correct_answer_rate']."%</td>\n";
		if ($list2['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
		$html .= "<td>".$error_msg."</td>\n";
		$html .= "<td>".$L_DISPLAY[$list2['display']]."</td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"ht_view\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list2['problem_num']."\">\n";
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('1','".$list2['problem_num']."','".$_POST[hantei_default_num]."'); return false;\"></td>\n";
		$html .= "</form>\n";
		$html .= "</tr>\n";

		$html .= "</table><br/>\n";


		//$tmp_disp_sort = "";	//入力フォームで入力、エラー再表示の場合。
		$tmp_disp_sort = "";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"problem_form\">\n";
		$html .= "表示順番号  <input type=\"text\" size=\"5\" name=\"disp_sort\" value=\"".$tmp_disp_sort."\">";

		$html .= "<br>\n";
		$html .= $mode_msg;
		$html .= "<input type=\"hidden\" name=\"action\" value=\"$action\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list2['problem_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$_POST[hantei_default_num]."\">\n";
		$html .= "<input type=\"hidden\" name=\"ht_problem_table_type\" value=\"1\">\n";

		$html .= "<input type=\"submit\" value=\"$button\">";
		$html .= "</form>\n";

	}

	$html .= "<br/>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"$_POST[hantei_default_num]\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	$html .= "</form>\n";

	return $html;
}



/**
 * 問題登録　テスト専用問題新規登録　登録確認フォーム  (ht_delete時も、このルーチンが呼ばれる)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_test_add_check_html() {
//echo "*ht_test_add_check_html()";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;	// add hasegawa 2016/06/02 作図ツール $L_DRAWING_TYPE追加
	global $L_HANTEI_PROBLEM_TYPE;
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ht_add") { $val = "ht_problem_add"; }
				elseif (MODE == "ht_view") { $val = "ht_change"; }
			}

			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}

	$problem_table_type = $_POST['problem_table_type'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }

		//-2013/09/17
		$DATA_SET = array();

		$DATA_SET['__info']['call_mode'] = 0;		// 0=入力フォーム、1:csv入力
		$DATA_SET['__info']['line_num'] = 0;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
		$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
		$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

		$DATA_SET['data']['display_problem_num'] = $_POST['display_problem_num'];
		$DATA_SET['data']['problem_type'] = $_POST['problem_type'];
		$DATA_SET['data']['form_type'] = $_POST['form_type'];
		$DATA_SET['data']['question'] = trim($_POST['question']);
		$DATA_SET['data']['problem'] = trim($_POST['problem']);
		$DATA_SET['data']['voice_data'] = trim($_POST['voice_data']);
		$DATA_SET['data']['hint'] = trim($_POST['hint']);
		$DATA_SET['data']['explanation'] = trim($_POST['explanation']);
		$DATA_SET['data']['standard_time'] = $_POST['standard_time'];
		$DATA_SET['data']['hint_number'] = $_POST['hint_number'];
		$DATA_SET['data']['first_problem'] = trim($_POST['first_problem']);
		$DATA_SET['data']['latter_problem'] = trim($_POST['latter_problem']);
		$DATA_SET['data']['selection_words'] = trim($_POST['selection_words']);
		$DATA_SET['data']['correct'] = trim($_POST['correct']);
		$DATA_SET['data']['option1'] = $_POST['option1'];
		$DATA_SET['data']['option2'] = $_POST['option2'];
		// form_type3の場合はoption2のデータを形成			// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($_POST['form_type'] == 3 && !$_POST['option2']) {
			$DATA_SET['data']['option2'] = make_option2($_POST['input_row'],$_POST['input_size']);
		}
		$DATA_SET['data']['option3'] = $_POST['option3'];
		$DATA_SET['data']['option4'] = $_POST['option4'];
		$DATA_SET['data']['option5'] = $_POST['option5'];
		$DATA_SET['data']['display'] = $_POST['display'];

		list($DATA_SET, $ERROR) = ht_test_check($DATA_SET, $ERROR);

		//入力データを変数に格納
		$display_problem_num = $DATA_SET['data']['display_problem_num'];
		$problem_type = $DATA_SET['data']['problem_type'];
		$form_type  = $DATA_SET['data']['form_type'];
		$question = $DATA_SET['data']['question'];
		$problem = $DATA_SET['data']['problem'];
		$voice_data  = $DATA_SET['data']['voice_data'];
		$hint = $DATA_SET['data']['hint'];
		$explanation = $DATA_SET['data']['explanation'];
		$standard_time = $DATA_SET['data']['standard_time'];
		$hint_number = $DATA_SET['data']['hint_number'];
		$first_problem = $DATA_SET['data']['first_problem'];
		$latter_problem = $DATA_SET['data']['latter_problem'];
		$selection_words = $DATA_SET['data']['selection_words'];
		$correct = $DATA_SET['data']['correct'];
		$option1 = $DATA_SET['data']['option1'];
		$option2 = $DATA_SET['data']['option2'];
		$option3 = $DATA_SET['data']['option3'];
		$option4 = $DATA_SET['data']['option4'];
		$option5 = $DATA_SET['data']['option5'];
		$display = $DATA_SET['data']['display'];

		//入力データをセッション情報に格納
		$_SESSION['UPDATE']['display_problem_num'] = $DATA_SET['data']['display_problem_num'];
		$_SESSION['UPDATE']['problem_type'] = $DATA_SET['data']['problem_type'];
		$_SESSION['UPDATE']['form_type']  = $DATA_SET['data']['form_type'];
		$_SESSION['UPDATE']['question'] = $DATA_SET['data']['question'];
		$_SESSION['UPDATE']['problem'] = $DATA_SET['data']['problem'];
		$_SESSION['UPDATE']['voice_data']  = $DATA_SET['data']['voice_data'];
		$_SESSION['UPDATE']['hint'] = $DATA_SET['data']['hint'];
		$_SESSION['UPDATE']['explanation'] = $DATA_SET['data']['explanation'];
		$_SESSION['UPDATE']['standard_time'] = $DATA_SET['data']['standard_time'];
		$_SESSION['UPDATE']['hint_number'] = $DATA_SET['data']['hint_number'];
		$_SESSION['UPDATE']['first_problem'] = $DATA_SET['data']['first_problem'];
		$_SESSION['UPDATE']['latter_problem'] = $DATA_SET['data']['latter_problem'];
		$_SESSION['UPDATE']['selection_words'] = $DATA_SET['data']['selection_words'];
		$_SESSION['UPDATE']['correct'] = $DATA_SET['data']['correct'];
		$_SESSION['UPDATE']['option1'] = $DATA_SET['data']['option1'];
		$_SESSION['UPDATE']['option2'] = $DATA_SET['data']['option2'];
		$_SESSION['UPDATE']['option3'] = $DATA_SET['data']['option3'];
		$_SESSION['UPDATE']['option4'] = $DATA_SET['data']['option4'];
		$_SESSION['UPDATE']['option5'] = $DATA_SET['data']['option5'];
		$_SESSION['UPDATE']['display'] = $DATA_SET['data']['display'];
		//-2013/09/17

	} else {
		if (MODE != "ht_delete") {
			$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"ht_change\">\n";
		} else {
			//削除操作時
			$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"ht_delete\">\n";
		}

		if ($problem_table_type == "2") {
			//判定テスト問題の場合
			$sql  = "SELECT *" .
				" FROM ". T_HANTEI_MS_PROBLEM ." hms" .
				" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hms.problem_num".
				" AND hmdp.hantei_default_num='".$_POST['hantei_default_num']."'".
				" AND hmdp.problem_num='".$_POST['problem_num']."'".
				" WHERE hmdp.mk_flg='0'".
				" AND hms.mk_flg='0' LIMIT 1";

		} elseif ($problem_table_type == "1") {
			//すらら問題の場合
			$sql  = "SELECT *" .
				" FROM ". T_PROBLEM ." problem" .
				" WHERE problem.problem_num='".$_POST['problem_num']."'".
				" AND problem.state='0' LIMIT 1";

		} else {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}


		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		$_SESSION['sub_session']['add_type']['form_type'] = $form_type;
		//hantei_ms_default_problem から disp_sort を使用する $list['disp_sort']
		$_SESSION['UPDATE']['display_problem_num'] = $disp_sort;
		$_SESSION['UPDATE']['display'] = $list['display'];

	}


	if (MODE != "ht_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";

	if (MODE != "ht_delete") {
		$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_HANTEI_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";

	} else {
		//if ($problem_table_type == "1") { $test_suit = "surala"; }
		if ($problem_table_type == "1") {	// 2013/09/13
			$test_suit = "1";
//			$test_suit = "8";
		}
		if ($problem_table_type == "2") { $test_suit = "test"; }
		$html .= $L_HANTEI_PROBLEM_TYPE[$test_suit].$button."<br>\n";
	}

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	if (!$_SESSION['sub_session']['add_type']['form_type']) {
		$html = "";

	} else {

		//$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
		$table_file = "hantei_test_form_type_".$_SESSION['sub_session']['add_type']['form_type'].".htm";
		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file($table_file);

		//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
		$INPUTS[PROBLEMNUM] 	= array('result'=>'plane','value'=>$_POST['problem_num']);
		//$INPUTS[PROBLEMNUM] 	= array('result'=>'plane','value'=>$_POST['problem_num']);
		if (MODE != "ht_delete") {
			$INPUTS[PROBLEMNUM] 	= array('result'=>'plane','value'=>"---");
		}

		// add start hasegawa 2016/06/02 作図ツール
		if($_SESSION['sub_session']['add_type']['form_type'] ==13) {
			$drawing_type = $option1;
			$INPUTS[DRAWINGTYPE] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
		}
		// add end hasegawa 2016/06/02

		$INPUTS[DISPSORT] 	= array('result'=>'plane','value'=>$_SESSION['UPDATE']['display_problem_num']);
		//$INPUTS[PROBLEMTYPE] 	= array('result'=>'plane','value'=>'----');
		$INPUTS[PROBLEMTYPE] 	= array('result'=>'plane','value'=>$L_PROBLEM_TYPE[$problem_type]);
		$INPUTS[FORMTYPE] 		= array('result'=>'plane','value'=>$L_FORM_TYPE[$_SESSION['sub_session']['add_type']['form_type']]);
		$INPUTS[QUESTION] 		= array('result'=>'plane','value'=>nl2br($question));
		$INPUTS[PROBLEM] 		= array('result'=>'plane','value'=>nl2br($problem));
		$INPUTS[VOICEDATA] 		= array('result'=>'plane','value'=>$voice_data);
		$INPUTS[HINT] 			= array('result'=>'plane','value'=>nl2br($hint));
		$INPUTS[EXPLANATION] 	= array('result'=>'plane','value'=>nl2br($explanation));
		//$INPUTS[ANSWERTIME] 	= array('result'=>'plane','value'=>$answer_time);
		$INPUTS[ANSWERTIME] 	= array('result'=>'plane','value'=>$standard_time);
		$INPUTS[HINTNUMBER]		= array('result'=>'plane','value'=>$hint_number);
		$INPUTS[PARAMETER] 		= array('result'=>'plane','value'=>$parameter);
		$INPUTS[FIRSTPROBLEM] 	= array('result'=>'plane','value'=>nl2br($first_problem));
		$INPUTS[LATTERPROBLEM] 	= array('result'=>'plane','value'=>nl2br($latter_problem));
		$INPUTS[SELECTIONWORDS] = array('result'=>'plane','value'=>$selection_words);
		$INPUTS[CORRECT] 		= array('result'=>'plane','value'=>$correct);
		$INPUTS[OPTION1] 		= array('result'=>'plane','value'=>$option1);
//		$INPUTS[OPTION2] 		= array('result'=>'plane','value'=>$option2);	// del start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		// ---- add start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		// form_type3 選択時はoption2の項目を解答欄行数・解答欄サイズにする
		if($_SESSION['sub_session']['add_type']['form_type'] == 3 ) {
			if($option2) {
				list($input_row,$input_size) = get_option2($option2);
			}

			// 解答欄行数
			$INPUTS['INPUTROW'] = array('result'=>'plane','value'=>$input_row);
			// 解答欄サイズ
			$INPUTS['INPUTSIZE'] = array('result'=>'plane','value'=>$input_size);
		} else {
			$INPUTS['OPTION2'] 	= array('result'=>'plane','value'=>$option2);
		}
		// ---- add end hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		$INPUTS[OPTION3] 		= array('result'=>'plane','value'=>$option3);
		$INPUTS[OPTION4] 		= array('result'=>'plane','value'=>$option4);
		$INPUTS[OPTION5] 		= array('result'=>'plane','value'=>$option5);
		$INPUTS[OPTION5] 		= array('result'=>'plane','value'=>$option5);
		// add start karasawa 2019/07/23 BUD英語解析開発
		if($form_type == 3 || $form_type == 4 || $form_type == 10){
			$INPUTS['OPTION3'] = array('result'=>'plane','value'=>$BUD_SELECT_LIST[$option3]);
		}
		// add end karasawa 2019/07/23
//echo "<pre>*";
//print_r($_POST);
//echo "</pre>";
		$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$_SESSION['UPDATE']['display']]);

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

	}

	$html .= "<br/>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
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
//echo "*add(), ";
	foreach($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "hantei_default_num") { continue; }
		$LINE[$key] = replace_word($val);
	}

	unset($C_ERROR);
	unset($S_ERROR);
	$regist_problem = new regist_problem();
	$C_ERROR = $regist_problem->check_data($LINE);

	//	エラー記録処理
	unset($error_msg);
	unset($LINE['error_msg']);
	if ($C_ERROR) {
		foreach ($C_ERROR AS $key => $val) {
			$LINE['error_msg'] .= $key."::".$val."\n";
			$_POST['error_msg'] .= $key."::".$val."\n";
		}
		$LINE['display'] = 2;
	}

	return $ERROR;

}


/**
 * 問題登録　テスト専用問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_test_add_add() {	//入力フォーム[追加]→確認画面で[登録]押下時
//echo "ht_test_add_add(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//入力データをセッション情報から取り出す。無ければ POSTデータからの取り出しを行う
	if ($_SESSION['UPDATE']) {
		$display_problem_num = $_SESSION['UPDATE']['display_problem_num'];
		$problem_type = $_SESSION['UPDATE']['problem_type'];
		$form_type = $_SESSION['UPDATE']['form_type'];
		$question = $_SESSION['UPDATE']['question'];
		$problem = $_SESSION['UPDATE']['problem'];
		$voice_data = $_SESSION['UPDATE']['voice_data'];
		$hint = $_SESSION['UPDATE']['hint'];
		$explanation = $_SESSION['UPDATE']['explanation'];
		//$answer_time = $_SESSION['UPDATE']['answer_time'];
		$standard_time = $_SESSION['UPDATE']['standard_time'];
		$hint_number = $_SESSION['UPDATE']['hint_number'];
		$first_problem = $_SESSION['UPDATE']['first_problem'];
		$latter_problem = $_SESSION['UPDATE']['latter_problem'];
		$selection_words = $_SESSION['UPDATE']['selection_words'];
		$correct = $_SESSION['UPDATE']['correct'];
		$option1 = $_SESSION['UPDATE']['option1'];
		$option2 = $_SESSION['UPDATE']['option2'];
		$option3 = $_SESSION['UPDATE']['option3'];
		$option4 = $_SESSION['UPDATE']['option4'];
		$option5 = $_SESSION['UPDATE']['option5'];
		$display = $_SESSION['UPDATE']['display'];

	} elseif ($_POST) {
		$display_problem_num = $_POST['display_problem_num'];
		$problem_type = $_POST['problem_type'];
		$form_type = $_POST['form_type'];
		$question = $_POST['question'];
		$problem = $_POST['problem'];
		$voice_data = $_POST['voice_data'];
		$hint = $_POST['hint'];
		$explanation = $_POST['explanation'];
		//$answer_time = $_POST['answer_time'];
		$standard_time = $_POST['standard_time'];
		$hint_number = $_POST['hint_number'];
		$first_problem = $_POST['first_problem'];
		$latter_problem = $_POST['latter_problem'];
		$selection_words = $_POST['selection_words'];
		$correct = $_POST['correct'];
		$option1 = $_POST['option1'];
		$option2 = $_POST['option2'];
		if($form_type == 3 && !$option2) {	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
			$option2 = make_option2($_POST['input_row'],$_POST['input_row']);
		}
		$option3 = $_POST['option3'];
		$option4 = $_POST['option4'];
		$option5 = $_POST['option5'];
		$display = $_POST['display'];

	}

	//判定テスト基本情報管理番号
	$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
	if ($s_hantei_default_num > 0) {

		$sql = "SELECT MAX(problem_num) AS max_num ".
			" FROM ".T_HANTEI_MS_PROBLEM;
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['max_num']) {
			$add_problem_num = $list['max_num'] + 1;
		} else {
			$add_problem_num = 1;
		}

		//hantei_ms_problem へ問題登録
		$INSERT_DATA = array();

		//hantei_default_num から style_course_num を取り出し、それより write_type を決定して返す
		$s_write_type = get_write_type($s_hantei_default_num);

		//画像ファイル、音声ファイルのファイル名変換とフォルダ作成
		list($question, $ERROR_SUB) = ht_img_convert($question, $add_problem_num, $s_write_type);
		list($question, $ERROR_SUB) = ht_voice_convert($question, $add_problem_num, $s_write_type);

		list($problem, $ERROR_SUB)	= ht_img_convert($problem, $add_problem_num, $s_write_type);
		list($problem, $ERROR_SUB) 	= ht_voice_convert($problem, $add_problem_num, $s_write_type);

		list($hint, $ERROR_SUB) 	= ht_img_convert($hint, $add_problem_num, $s_write_type);
		list($hint, $ERROR_SUB) 	= ht_voice_convert($hint, $add_problem_num, $s_write_type);

		list($explanation, $ERROR_SUB)	= ht_img_convert($explanation, $add_problem_num, $s_write_type);
		list($explanation, $ERROR_SUB)	= ht_voice_convert($explanation, $add_problem_num, $s_write_type);

		//form_type10,11のみselection_wordsの変換
		if ($form_type == 10 || $form_type == 11) {
			list($selection_words, $ERROR_SUB)	= ht_img_convert($selection_words, $add_problem_num, $s_write_type);
			list($selection_words, $ERROR_SUB)	= ht_voice_convert($selection_words, $add_problem_num, $s_write_type);
		}
		list($voice_data, $ERROR_SUB) = copy_default_voice_data($voice_data, $add_problem_num, $s_write_type);

		//$INSERT_DATA[standard_time]		 	= $answer_time;		//回答目安時間
		$INSERT_DATA[standard_time]		 	= $standard_time;		//回答目安時間
		$INSERT_DATA[problem_type]		 	= $problem_type;	//問題タイプ

		$INSERT_DATA[form_type]			 	= $form_type;		//出題形式
		$INSERT_DATA[question] 				= $question;		//質問文
		$INSERT_DATA[problem] 				= $problem;			//問題文
		$INSERT_DATA[voice_data] 			= $voice_data;		//音声ファイル名
		$INSERT_DATA[hint]				 	= $hint;			//ヒント
		$INSERT_DATA[explanation] 			= $explanation;		//解説文
		$INSERT_DATA[first_problem] 		= $first_problem;	//問題前半テキスト
		$INSERT_DATA[latter_problem] 		= $latter_problem;	//問題後半テキスト
		$INSERT_DATA[hint_number] 			= $hint_number;		//ヒント表示回数
		$INSERT_DATA[selection_words] 		= $selection_words;	//選択語句、問題テキスト
		$INSERT_DATA[correct] 				= $correct;			//正解
		$INSERT_DATA[option1] 				= $option1;			//option1
		$INSERT_DATA[option2] 				= $option2;			//option2
		$INSERT_DATA[option3] 				= $option3;			//option3
		$INSERT_DATA[option4] 				= $option4;			//option4
		$INSERT_DATA[option5] 				= $option5;			//option5
		$INSERT_DATA[display] 				= $display;			//表示（1：表示 2：非表示）
		$INSERT_DATA[ins_syr_id]			= "addline";
		$INSERT_DATA[ins_tts_id] 			= $_SESSION['myid']['id'];
		$INSERT_DATA[ins_date] 				= "now()";
		$INSERT_DATA[upd_syr_id]			= "addline";
		$INSERT_DATA[upd_tts_id] 			= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 				= "now()";

		// hantei_ms_problem テーブルへ格納
		$ERROR = $cdb->insert(T_HANTEI_MS_PROBLEM, $INSERT_DATA);

		if (!$ERROR) {
			//hantei_ms_problem に登録した problem_num (autoincrement) を取り出す
			$last_id = $cdb->insert_id();

			//hantei_ms_default_problem に登録
			$INSERT_DATA2 = array();
			$INSERT_DATA2[hantei_default_num]	= $s_hantei_default_num;	//判定テスト基本情報管理番号
			//add start okabe 2013/09/05
			$INSERT_DATA2[service_num] = $_SESSION['sub_session']['s_service_num'];
			$INSERT_DATA2[hantei_type] = $_SESSION['sub_session']['s_hantei_type'];
			$INSERT_DATA2[course_num] = $_SESSION['sub_session']['s_course_num'];
			//add end okabe 2013/09/05
			$INSERT_DATA2[problem_num]		 	= $last_id;					//問題管理番号
			$INSERT_DATA2[problem_table_type] 	= "2";						//問題テーブルタイプ（2：専用）
			$INSERT_DATA2[disp_sort] 			= $display_problem_num;		//表示順
			$INSERT_DATA2[ins_syr_id]			= "addline";
			$INSERT_DATA2[ins_tts_id] 			= $_SESSION['myid']['id'];
			$INSERT_DATA2[ins_date] 				= "now()";
			$INSERT_DATA2[upd_syr_id]			= "addline";
			$INSERT_DATA2[upd_tts_id] 			= $_SESSION['myid']['id'];
			$INSERT_DATA2[upd_date] 				= "now()";

			// hantei_ms_default_problem テーブルへ格納
			$ERROR = $cdb->insert(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA2);

			unset($INSERT_DATA2);

			if (!$ERROR) {	//登録正常終了
				$_SESSION['sub_session']['surala_regist']['status'] = "3";
				$_SESSION['sub_session']['surala_regist']['problem_num'] = $last_id;
				$_SESSION['sub_session']['surala_regist']['disp_sort'] = $display_problem_num;
			}

		}

		unset($INSERT_DATA);
	}

	return $ERROR;
}


/**
 * 問題登録　テスト専用問題新規登録　入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_test_add_check() {
//echo "*ht_test_add_check(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$DATA_SET = array();
	$DATA_SET['__info']['call_mode'] = 0;		// 0=入力フォーム、1:csv入力
	$DATA_SET['__info']['line_num'] = 0;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
	$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
	$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

	$DATA_SET['data']['display_problem_num'] = $_POST['display_problem_num'];
	$DATA_SET['data']['problem_type'] = $_POST['problem_type'];
	$DATA_SET['data']['form_type'] = $_POST['form_type'];
	$DATA_SET['data']['question'] = trim($_POST['question']);
	$DATA_SET['data']['problem'] = trim($_POST['problem']);
	$DATA_SET['data']['voice_data'] = trim($_POST['voice_data']);
	$DATA_SET['data']['hint'] = trim($_POST['hint']);
	$DATA_SET['data']['explanation'] = trim($_POST['explanation']);
	$DATA_SET['data']['standard_time'] = $_POST['standard_time'];
	$DATA_SET['data']['hint_number'] = $_POST['hint_number'];
	$DATA_SET['data']['first_problem'] = trim($_POST['first_problem']);
	$DATA_SET['data']['latter_problem'] = trim($_POST['latter_problem']);
	$DATA_SET['data']['selection_words'] = trim($_POST['selection_words']);
	$DATA_SET['data']['correct'] = trim($_POST['correct']);
	$DATA_SET['data']['option1'] = $_POST['option1'];
	$DATA_SET['data']['option2'] = $_POST['option2'];
	// form_type3の場合はoption2のデータを形成		// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
	if($_POST['form_type'] == 3 && !$_POST['option2']) {
		$DATA_SET['data']['option2'] = make_option2($_POST['input_row'],$_POST['input_size']);
	}
	$DATA_SET['data']['option3'] = $_POST['option3'];
	$DATA_SET['data']['option4'] = $_POST['option4'];
	$DATA_SET['data']['option5'] = $_POST['option5'];
	$DATA_SET['data']['display'] = $_POST['display'];

	$DATA_SET['data']['state'] = "0";	//$_POST['state']; //入力フォームでは 0

	list($DATA_SET, $ERROR) = ht_test_check($DATA_SET, $ERROR);

	//問題番号がすでに使われていないか確認
	//if (!$ERROR) {
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
		if (!$s_hantei_default_num) {
			$ERROR[] = "判定テスト基本情報管理番号が不正です。";

		} else {
			$sql = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hmdp.hantei_default_num='".$s_hantei_default_num."'".
				" AND hmdp.disp_sort='".$DATA_SET['data']['display_problem_num']."'".
				" AND hmdp.mk_flg='0';";
			$hantei_test_master_count = 0;
			if ($result = $cdb->query($sql)) {
				$hantei_test_master_count = $cdb->num_rows($result);
			}
			if ($hantei_test_master_count > 0) {
				//同じ番号が存在する
				$ERROR[] = "既に、同じ表示順番号が使用されています。";
			}
		}
	//}

	//入力データをセッション情報に格納
	$_SESSION['UPDATE']['display_problem_num'] = $DATA_SET['data']['display_problem_num'];
	$_SESSION['UPDATE']['problem_type'] = $DATA_SET['data']['problem_type'];
	$_SESSION['UPDATE']['form_type']  = $DATA_SET['data']['form_type'];
	$_SESSION['UPDATE']['question'] = $DATA_SET['data']['question'];
	$_SESSION['UPDATE']['problem'] = $DATA_SET['data']['problem'];
	$_SESSION['UPDATE']['voice_data']  = $DATA_SET['data']['voice_data'];
	$_SESSION['UPDATE']['hint'] = $DATA_SET['data']['hint'];
	$_SESSION['UPDATE']['explanation'] = $DATA_SET['data']['explanation'];
	$_SESSION['UPDATE']['standard_time'] = $DATA_SET['data']['standard_time'];
	$_SESSION['UPDATE']['hint_number'] = $DATA_SET['data']['hint_number'];
	$_SESSION['UPDATE']['first_problem'] = $DATA_SET['data']['first_problem'];
	$_SESSION['UPDATE']['latter_problem'] = $DATA_SET['data']['latter_problem'];
	$_SESSION['UPDATE']['selection_words'] = $DATA_SET['data']['selection_words'];
	$_SESSION['UPDATE']['correct'] = $DATA_SET['data']['correct'];
	$_SESSION['UPDATE']['option1'] = $DATA_SET['data']['option1'];
	$_SESSION['UPDATE']['option2'] = $DATA_SET['data']['option2'];
	$_SESSION['UPDATE']['option3'] = $DATA_SET['data']['option3'];
	$_SESSION['UPDATE']['option4'] = $DATA_SET['data']['option4'];
	$_SESSION['UPDATE']['option5'] = $DATA_SET['data']['option5'];
	$_SESSION['UPDATE']['display'] = $DATA_SET['data']['display'];
//echo "<pre>";print_r($_SESSION['UPDATE']);echo "</pre>";

	return $ERROR;
}


/**
 * 登録済みデータの修正（更新）処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_change() {
//echo "*ht_change(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$hantei_default_num = $_POST['hantei_default_num'];		//判定テスト管理番号
	$problem_num = $_POST['problem_num'];	//問題管理番号

	$display_problem_num = $_POST['display_problem_num'];	//表示順番号
	$display_problem_num = mb_convert_kana($display_problem_num,"asKV", "UTF-8");	//edit 2013/09/09
	$display_problem_num = trim($display_problem_num);

	if ($problem_num) {

		$INSERT_DATA = array();

		if (MODE == "ht_view") {

			if ($_POST['ht_problem_table_type'] == "1") {
				//すらら問題の場合

				$disp_sort = $_POST['disp_sort'];
				$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
				$disp_sort = trim($disp_sort);
				if (strlen($disp_sort) == 0) {
					$ERROR[] = "表示順番号が入力されていません。";
					return $ERROR;
				}
				if ($disp_sort < 1) {
					$ERROR[] = "表示順番号が不正です。";
					return $ERROR;
				}

				$sql = "SELECT hmdp.disp_sort FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
					" WHERE hmdp.hantei_default_num='".$hantei_default_num."'".
					" AND hmdp.problem_num='".$problem_num."'".
					" AND hmdp.mk_flg='0';";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);

					if ($list['disp_sort'] != $disp_sort) {
						//変更があった場合、他に同じ番号が無いかチェックする
						$sql = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
							" WHERE hmdp.hantei_default_num='".$hantei_default_num."'".
							" AND hmdp.disp_sort='".$disp_sort."'".
							" AND hmdp.mk_flg='0';";
						if ($result = $cdb->query($sql)) {
							if ($cdb->num_rows($result) > 0) {
								$ERROR[] = "同じ表示順番号が使われています。";
								return $ERROR;
							}
						}

						//表示順番号が変更された、hantei_ms_default_problem を update する
						$INSERT_DATA[disp_sort] = $disp_sort;
						$INSERT_DATA[upd_syr_id] = "updateline";
						$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];
						$INSERT_DATA[upd_date] 	= "now()";
						$where = " WHERE hantei_default_num='".$hantei_default_num."'".
							" AND problem_num='".$problem_num."';";

						$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA, $where);
						if ($ERROR) { return $ERROR; }
					}
				}
				unset($_SESSION['UPDATE']);	//add okabe 2013/09/03


			} else {
				//判定テスト問題か

				//表示順番号に変更があった場合、同じ番号が存在するか確認

				//入力データの正当性をチェックする。異常があれば更新しない。
				$DATA_SET = array();
				$DATA_SET['__info']['call_mode'] = 0;		// 0=入力フォーム、1:csv入力
				$DATA_SET['__info']['line_num'] = 0;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
				$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
				$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

				$DATA_SET['data']['display_problem_num'] = $_POST['display_problem_num'];
				$DATA_SET['data']['problem_type'] = $_POST['problem_type'];
				$DATA_SET['data']['form_type'] = $_POST['form_type'];
				$DATA_SET['data']['question'] = trim($_POST['question']);
				$DATA_SET['data']['problem'] = trim($_POST['problem']);
				$DATA_SET['data']['voice_data'] = trim($_POST['voice_data']);
				$DATA_SET['data']['hint'] = trim($_POST['hint']);
				$DATA_SET['data']['explanation'] = trim($_POST['explanation']);
				//$DATA_SET['data']['answer_time'] = $_POST['answer_time'];
				$DATA_SET['data']['standard_time'] = $_POST['standard_time'];
				$DATA_SET['data']['hint_number'] = $_POST['hint_number'];
				$DATA_SET['data']['first_problem'] = trim($_POST['first_problem']);
				$DATA_SET['data']['latter_problem'] = trim($_POST['latter_problem']);
				$DATA_SET['data']['selection_words'] = trim($_POST['selection_words']);
				$DATA_SET['data']['correct'] = trim($_POST['correct']);
				$DATA_SET['data']['option1'] = $_POST['option1'];
				$DATA_SET['data']['option2'] = $_POST['option2'];
				// form_type3の場合はoption2のデータを形成	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
				if($_POST['form_type'] == 3 && !$_POST['option2']) {
					$DATA_SET['data']['option2'] = make_option2($_POST['input_row'],$_POST['input_size']);
				}
				$DATA_SET['data']['option3'] = $_POST['option3'];
				$DATA_SET['data']['option4'] = $_POST['option4'];
				$DATA_SET['data']['option5'] = $_POST['option5'];
				$DATA_SET['data']['display'] = $_POST['display'];
				$DATA_SET['data']['state'] = "0";	//$_POST['state']; //入力フォームでは 0

				list($DATA_SET, $ERROR) = ht_test_check($DATA_SET, $ERROR);

				if (!$ERROR) {

					//現在のデータを取得
					$sql = "SELECT hmdp.disp_sort FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
						" WHERE hmdp.hantei_default_num='".$hantei_default_num."'".
						" AND hmdp.problem_num='".$problem_num."'".
						" AND hmdp.mk_flg='0';";
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
						if ($list['disp_sort'] != $display_problem_num) {
							//変更があった場合、他に同じ番号が無いかチェックする
							$sql = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
								" WHERE hmdp.hantei_default_num='".$hantei_default_num."'".
								" AND hmdp.disp_sort='".$display_problem_num."'".
								" AND hmdp.mk_flg='0';";
							if ($result = $cdb->query($sql)) {
								if ($cdb->num_rows($result) > 0) {
									$ERROR[] = "同じ表示順番号が使われています。";
									return $ERROR;
								}
							}

							//表示順番号が変更された、hantei_ms_default_problem を update する
							$INSERT_DATA[disp_sort] = $display_problem_num;
							$INSERT_DATA[upd_syr_id] = "updateline";
							$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];
							$INSERT_DATA[upd_date] 	= "now()";
							$where = " WHERE hantei_default_num='".$hantei_default_num."'".
								" AND problem_num='".$problem_num."';";

							$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA, $where);
							if ($ERROR) { return $ERROR; }
						}
					}

					//hantei_ms_problem テーブルを更新する
					$INSERT_DATA = array();

					//データを配列にセットする
					if ($_POST['problem_type']) { $INSERT_DATA[problem_type] = $_POST['problem_type']; }

					//$INSERT_DATA[display_problem_num] = $DATA_SET['data']['display_problem_num'];
					$INSERT_DATA[problem_type] = $DATA_SET['data']['problem_type'];
					//$INSERT_DATA[form_type] = $DATA_SET['data']['form_type'];
					$INSERT_DATA[question] = $DATA_SET['data']['question'];
					$INSERT_DATA[problem] = $DATA_SET['data']['problem'];
					$INSERT_DATA[voice_data] = $DATA_SET['data']['voice_data'];
					$INSERT_DATA[hint] = $DATA_SET['data']['hint'];
					$INSERT_DATA[explanation] = $DATA_SET['data']['explanation'];
					//$INSERT_DATA[answer_time] = $DATA_SET['data']['answer_time'];
					$INSERT_DATA[standard_time] = $DATA_SET['data']['standard_time'];
					$INSERT_DATA[hint_number] = $DATA_SET['data']['hint_number'];
					$INSERT_DATA[first_problem] = $DATA_SET['data']['first_problem'];
					$INSERT_DATA[latter_problem] = $DATA_SET['data']['latter_problem'];
					$INSERT_DATA[selection_words] = $DATA_SET['data']['selection_words'];
					$INSERT_DATA[correct] = $DATA_SET['data']['correct'];
					$INSERT_DATA[option1] = $DATA_SET['data']['option1'];
					$INSERT_DATA[option2] = $DATA_SET['data']['option2'];
					$INSERT_DATA[option3] = $DATA_SET['data']['option3'];
					$INSERT_DATA[option4] = $DATA_SET['data']['option4'];
					$INSERT_DATA[option5] = $DATA_SET['data']['option5'];
					$INSERT_DATA[display] = $DATA_SET['data']['display'];

					$INSERT_DATA[upd_syr_id] = "updateline";
					$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];
					$INSERT_DATA[upd_date] 	= "now()";
					$where = " WHERE problem_num='".$problem_num."';";

					$ERROR = $cdb->update(T_HANTEI_MS_PROBLEM, $INSERT_DATA, $where);

					if ($ERROR) { return $ERROR; }

					unset($_SESSION['UPDATE']);	//add okabe 2013/09/03

				} else {
					//入力チェックでエラー有り
					$_SESSION['UPDATE']['display_problem_num'] = $DATA_SET['data']['display_problem_num'];
					$_SESSION['UPDATE']['problem_type'] = $DATA_SET['data']['problem_type'];
					$_SESSION['UPDATE']['form_type'] = $DATA_SET['data']['form_type'];
					$_SESSION['UPDATE']['question'] = $DATA_SET['data']['question'];
					$_SESSION['UPDATE']['problem'] = $DATA_SET['data']['problem'];
					$_SESSION['UPDATE']['voice_data'] = $DATA_SET['data']['voice_data'];
					$_SESSION['UPDATE']['hint'] = $DATA_SET['data']['hint'];
					$_SESSION['UPDATE']['explanation'] = $DATA_SET['data']['explanation'];
					//$_SESSION['UPDATE']['answer_time'] = $DATA_SET['data']['answer_time'];
					$_SESSION['UPDATE']['standard_time'] = $DATA_SET['data']['standard_time'];
					$_SESSION['UPDATE']['hint_number'] = $DATA_SET['data']['hint_number'];
					$_SESSION['UPDATE']['first_problem'] = $DATA_SET['data']['first_problem'];
					$_SESSION['UPDATE']['latter_problem'] = $DATA_SET['data']['latter_problem'];
					$_SESSION['UPDATE']['selection_words'] = $DATA_SET['data']['selection_words'];
					$_SESSION['UPDATE']['correct'] = $DATA_SET['data']['correct'];
					$_SESSION['UPDATE']['option1'] = $DATA_SET['data']['option1'];
					$_SESSION['UPDATE']['option2'] = $DATA_SET['data']['option2'];
					$_SESSION['UPDATE']['option3'] = $DATA_SET['data']['option3'];
					$_SESSION['UPDATE']['option4'] = $DATA_SET['data']['option4'];
					$_SESSION['UPDATE']['option5'] = $DATA_SET['data']['option5'];
					$_SESSION['UPDATE']['display'] = $DATA_SET['data']['display'];
					$_SESSION['UPDATE']['state'] = $DATA_SET['data']['state'] ;	//$_POST['state']; //入力フォームでは 0
				}

			}	//すらら問題か判定テスト問題か


		} elseif (MODE == "ht_delete") {
			//hantei_ms_default_problem を更新する
			$INSERT_DATA = array();
			$INSERT_DATA[mk_flg] 		= 1;
			$INSERT_DATA[mk_tts_id] 	= $_SESSION['myid']['id'];
			$INSERT_DATA[mk_date] 		= "now()";
			$where = " WHERE problem_num='".$problem_num."'".
					" AND hantei_default_num='".$hantei_default_num."';";
			$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA, $where);
			if ($ERROR) { return $ERROR; }

		}
		unset($INSERT_DATA);

	} else {
		$ERROR[] = "問題管理番号を確認できません。";
	}

	if ($ERROR) { return $ERROR; }

	return $ERROR;
}


//---------------------------------------------


/**
 * 問題登録　既存テストから登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function ht_test_exist_addform($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;
	global $L_HANTEI_PROBLEM_TYPE;

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_HANTEI_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	$not_select = 0;	//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。その制御フラグ

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";

	} else {
		if ($_SESSION['sub_session']['surala_regist']['status'] == "2") {
			//判定テスト問題登録後、登録成功ならばメッセージ表示、（既存の判定テスト問題）
			//$d_surala_regist_problem_num = $_SESSION['sub_session']['surala_regist']['problem_num'];
			//$d_surala_regist_disp_sort = $_SESSION['sub_session']['surala_regist']['disp_sort'];
			$m_pnum_array = $_POST['m_pnum'];
			$m_snum_array = $_POST['m_snum'];
			$html .= "<div class=\"small_error\">\n";
			foreach($m_pnum_array as $key => $val) {
				$d_surala_regist_problem_num = $val;
				$d_surala_regist_disp_sort = $m_snum_array[$key];	//表示順番号のPOST値

				$html .= "<br/>管理番号".$d_surala_regist_problem_num."の問題を、表示順 ".$d_surala_regist_disp_sort."番に";
				$html .= "登録しました。";
			}
			$html .= "</div>\n";
			unset($_SESSION['sub_session']['surala_regist']['status']);
			$not_select = 1;
		}
	}
	$html .= ht_select_test();		//既存のテストタイプ選択ドロップダウン
	$where = "";


	$as_test_type = $_SESSION['sub_session']['seltest_course']['as_test_type'];
	$as_service_num = $_SESSION['sub_session']['seltest_course']['as_service_num'];
	$as_hantei_type = $_SESSION['sub_session']['seltest_course']['as_hantei_type'];
	$as_course_num = $_SESSION['sub_session']['seltest_course']['as_course_num'];
	$as_hantei_default_num = $_SESSION['sub_session']['seltest_course']['as_hantei_default_num'];
	$as_form_type = $_SESSION['sub_session']['seltest_course']['as_form_type'];
	$as_page_view = $_SESSION['sub_session']['seltest_course']['as_page_view'];

	if ($as_form_type) {	//出題形式で絞込み
		if ($as_form_type > 0) {
			$where .= " AND hmp.form_type='".$as_form_type."'";
		}
	}


	//$sub_sql = "";
	if ($as_hantei_type > 0 || $as_course_num > 0 || $as_hantei_default_num > 0) {
		//判定タイプ、コース、判定名で絞込みする場合
		$sql  = "SELECT count(DISTINCT hmp.problem_num) AS problem_count" .
			" FROM ". T_HANTEI_MS_DEFAULT. " hmd ".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM. " hmdp".
			" ON hmd.hantei_default_num=hmdp.hantei_default_num ".
			" LEFT JOIN ".T_HANTEI_MS_PROBLEM. " hmp".
			" ON hmp.problem_num=hmdp.problem_num ".
			" WHERE hmd.mk_flg='0'".
			" AND hmdp.mk_flg='0'".
			" AND hmp.mk_flg='0'".
			" AND hmd.service_num='".$as_service_num."'";
		if ($as_hantei_type > 0) {
			$sql .= " AND hmd.hantei_type='".$as_hantei_type."'";
		}
		if ($as_course_num > 0) {
			$sql .= " AND hmd.course_num='".$as_course_num."'";
		}
		if ($as_hantei_default_num > 0) {
			$sql .= " AND hmd.hantei_default_num='".$as_hantei_default_num."'";
		}
		$sql .= " AND hmdp.problem_table_type='2'".		//判定テスト問題
		$where.";";

	} elseif ($as_hantei_type == -1) {
		//未使用の問題をリスト
		$sql = "SELECT count(DISTINCT hmp.problem_num) AS problem_count ".
			" FROM ".T_HANTEI_MS_PROBLEM." hmp ".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
			" ON hmp.problem_num=hmdp.problem_num".
			" AND hmdp.mk_flg='0'".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd ".
			" ON hmd.hantei_default_num=hmdp.hantei_default_num".
			" WHERE hmp.mk_flg='0'".
			" AND hmd.hantei_default_num IS NULL".
			$where.";";

	} else {
		//全件（出題タイプ除く）取り出す条件
		$sql  = "SELECT count(*) AS problem_count" .
			" FROM ". T_HANTEI_MS_PROBLEM. " hmp ".
			" WHERE".
			" hmp.mk_flg='0'".
			$where.";";

	}


	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_count += $list['problem_count'];
		}
	}
	if (!$problem_count) {
		$html .= "<br>\n";
		$html .= "問題は登録されておりません。<br><br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}

	//表示数
	if ($as_page_view == "") { $as_page_view = "1"; }
	$page_view = $L_PAGE_VIEW[$as_page_view];

	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['select_course']['s_page']) {
		$page = $_SESSION['sub_session']['select_course']['s_page'];
	} else {
		$page = 1;
	}
	if ($max_page < $page) { $page = $max_page; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";
	$order = " ORDER BY hmp.problem_num";			// add hasegawa 2017/12/25 AWS移設 ソート条件追加

	if ($as_hantei_type > 0 || $as_course_num > 0 || $as_hantei_default_num > 0) {
		//判定タイプ、コース、判定名で絞込みする場合
		$sql  = "SELECT DISTINCT hmp.*" .
			" FROM ". T_HANTEI_MS_DEFAULT. " hmd ".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM. " hmdp".
			" ON hmd.hantei_default_num=hmdp.hantei_default_num ".
			" LEFT JOIN ".T_HANTEI_MS_PROBLEM. " hmp".
			" ON hmp.problem_num=hmdp.problem_num ".
			" WHERE hmd.mk_flg='0'".
			" AND hmdp.mk_flg='0'".
			" AND hmp.mk_flg='0'".
			" AND hmd.service_num='".$as_service_num."'";
		if ($as_hantei_type > 0) {
			$sql .= " AND hmd.hantei_type='".$as_hantei_type."'";
		}
		if ($as_course_num > 0) {
			$sql .= " AND hmd.course_num='".$as_course_num."'";
		}
		if ($as_hantei_default_num > 0) {
			$sql .= " AND hmd.hantei_default_num='".$as_hantei_default_num."'";
		}
		$sql .= " AND hmdp.problem_table_type='2'".		//判定テスト問題
		$where .
		" ORDER BY hmp.problem_num ".
		$limit;

	} elseif ($as_hantei_type == -1) {
		//未使用の問題をリスト
		$sql = "SELECT DISTINCT hmp.* ".
			" FROM ".T_HANTEI_MS_PROBLEM." hmp ".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
			" ON hmp.problem_num=hmdp.problem_num".
			" AND hmdp.mk_flg='0'".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd ".
			" ON hmd.hantei_default_num=hmdp.hantei_default_num".
			" WHERE hmp.mk_flg='0'".
			" AND hmd.hantei_default_num IS NULL".
			// $where. $limit;		// upd hasegawa 2017/12/25 AWS移設 ソート条件追加 $order追加
			$where. $order. $limit;

	} else {
		//全件（出題タイプ除く）取り出す条件
		$sql  = "SELECT *" .
			" FROM ". T_HANTEI_MS_PROBLEM. " hmp ".
			" WHERE ".
			" hmp.mk_flg='0'".
			// $where. $limit;		// upd hasegawa 2017/12/25 AWS移設 ソート条件追加 $order追加
			$where. $order. $limit;

	}
	if ($result = $cdb->query($sql)) {

		//	表示数	view_num
		$l_view_num = "<select name=\"as_page_view\">\n";
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($as_page_view == $key) { $sel = " selected"; } else { $sel = ""; }
			$l_view_num .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}
		$l_view_num .= "</select>\n";

		$html .= "<br>\n";

		$disp_hantei_name = get_hantei_name($_SESSION['sub_session']['s_hantei_default_num']);
		if (strlen($disp_hantei_name) > 0) {
			$html .= "判定名「".$disp_hantei_name."」に追加登録する問題を指定して下さい。<br/><br/>";
		}

		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_test_session\">\n";
		$html .= "<td>\n";
		$html .= "表示数 ".$l_view_num."\n";
		$html .= "<input type=\"submit\" value=\"Set\">\n";
		$html .= "</td>\n";
		$html .= "</form>\n";
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
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";	//下から移動
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		//$html .= "<td>&nbsp;</td>\n";
		$html .= "<td>No.</td>\n";
		$html .= "<td>追加</td>\n";
		$html .= "<td>表示順番号</td>\n";

		$html .= "<td>問題管理番号</td>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		$html .= "<th>回答目安時間</th>\n";
		$html .= "<th>確認</th>\n";
		$html .= "</tr>\n";
		$i = 0;

		while ($list = $cdb->fetch_assoc($result)) {
			//$newform = new form_parts();
			//$newform->set_form_type("radio");
			//$newform->set_form_name("problem");
			//$newform->set_form_id("problem_".$list['problem_num']);
			//$newform->set_form_check($_POST['problem_num']);
			//if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
			//	$newform->set_form_value("".$list['problem_num']."");
			//}
			//$newform->set_form_action(" onclick=\"set_test_problem_num('".$list['problem_num']."','2','".$list['standard_time']."','".$list['problem_point']."');\"");
			//$problem_btn = $newform->make();
			$table_type = "判定テスト問題";

			$html .= "<label for=\"problem_".$list['problem_num']."\">";
			$html .= "<tr class=\"member_form_cell\" >\n";
			//$html .= "<td>".$problem_btn."</td>\n";

			$html .= "<td align=\"center\">".($start+$i+1)."</td>\n";

			//問題選択チェックボックス
			$m_pnum_array = $_POST['m_pnum'];
			$checked_mark = "";
			if ($m_pnum_array[$i] != "" && !$not_select) { $checked_mark = "checked"; }
			$html .= "<td><input type=\"checkbox\" name=\"m_pnum[".$i."]\" value=\"".$list['problem_num']."\" ".$checked_mark."/></td>\n";

			//表示順番号テキストボックス
			$m_snum_array = $_POST['m_snum'];
			if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
				$html .= "<td><input type=\"text\" name=\"m_snum[]\" value=\"".$m_snum_array[$i]."\" size=\"5\" /></td>\n";
			} else {
				$html .= "<td><input type=\"text\" name=\"m_snum[]\" value=\"\" size=\"5\" /></td>\n";
			}
			$i = $i + 1;
			$html .= "<td>".$list['problem_num']."</td>\n";

			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>".$list['standard_time']."</td>\n";
			//$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			//$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			//$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('2','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
			//$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}

	$html .= "<br>\n";
	//$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";	//上へ移動
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_exist_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	//$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	//$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$_POST['problem_table_type']."\">\n";

	//$tmp_disp_sort = "";	//入力フォームで入力、エラー再表示の場合。
	//if ($_POST['disp_sort']) { $tmp_disp_sort = $_POST['disp_sort']; }
	//if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
	//	$html .= "表示順番号  <input type=\"text\" size=\"5\" name=\"disp_sort\" value=\"".$tmp_disp_sort."\"><br>";
	//} else {
	//	$html .= "表示順番号  <input type=\"text\" size=\"5\" name=\"disp_sort\" value=\"\"><br>";
	//}
	//$INPUTS[STANDARDTIME] 	= array('type'=>'text','name'=>'add_standard_time','size'=>'5','value'=>$_POST['add_standard_time']);

	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * コースリスト
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $service_num
 * @return array
 */
function ht_course_list($service_num) {	//edit 2013/09/13

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[] = "選択して下さい";
	//$sql  = "SELECT * FROM ".T_COURSE. " WHERE state='0' ORDER BY list_num;";
	$sql  = "SELECT course.* FROM ".T_COURSE. " course".
		" LEFT JOIN ".T_SERVICE_COURSE_LIST." scl".
		" ON scl.course_num = course.course_num".
		" WHERE scl.service_num ='".$service_num."'".
		" AND course.state='0' ORDER BY list_num;";

	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
		}
	}
	return $L_COURSE_LIST;
}




/**
 * 問題登録　既存テストから登録　テスト選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_select_test() {
//echo "ht_select_test(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_FORM_TYPE, $L_HANTEI_TYPE;

	if ($_POST['test_type']) {
		if ($_POST['test_type'] != $_SESSION['sub_session']['select_course']['test_type']) {
			//もし、テストタイプが切り替えられたなら、以降のセレクトをクリアする
			unset($_SESSION['sub_session']['select_course']['course_num']);
			unset($_SESSION['sub_session']['select_course']['gknn']);
			unset($_SESSION['sub_session']['select_course']['default_test_num']);
		}
	}

	//テストタイプ
	unset($L_TEST_TYPE[2]);
	unset($L_TEST_TYPE[3]);
	$test_type_html .= "<option value=\"hantei_test\" selected>判定テスト</option>\n";
	$_SESSION['sub_session']['seltest_course']['as_test_type']="hantei_test";

	//絞込み選択値
	$as_test_type = $_SESSION['sub_session']['seltest_course']['as_test_type'];

	$as_service_num = "8";		//"サービス"セレクトの非表示

	$as_hantei_type = $_SESSION['sub_session']['seltest_course']['as_hantei_type'];
	$as_course_num = $_SESSION['sub_session']['seltest_course']['as_course_num'];
	$as_hantei_default_num = $_SESSION['sub_session']['seltest_course']['as_hantei_default_num'];
	$as_form_type = $_SESSION['sub_session']['seltest_course']['as_form_type'];
	$as_page_view = $_SESSION['sub_session']['seltest_course']['as_page_view'];

	//	サービス
	$service_html  = "";
	$chk_flg = 0;
	$sql  = "SELECT service_num, service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($as_service_num < 1) {
			$as_service_num = 0;
			$selected = "selected";
			//$msg_html = "サービスを選択してください。";
			$as_hantei_default_num = "0";
		}
		$service_html .= "<option value=\"0\" ".$selected.">選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$selected = "";
			if ($as_service_num == $list['service_num']) { $selected = "selected"; }
			$service_html .= "<option value=\"".$list['service_num']."\" ".$selected.">".$list['service_name']."</option>\n";
			$chk_flg = 1;
		}
	}
	if ($service_html == "" || $chk_flg == 0) {
		$service_html .= "<option value=\"0\">サービスが登録されておりません</option>\n";
		$as_service_num = 0;
	}

	//	判定タイプ
	$hantei_type_html = "";
	if ($as_service_num != 0) {
		if ($as_service_num > 0 && count($L_HANTEI_TYPE) > 1) {
			foreach ($L_HANTEI_TYPE AS $key => $val) {
				$selected = "";
				if ($as_hantei_type == $key) { $selected = "selected"; }
				$hantei_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
	$selected = "";
	if ($as_hantei_type == -1) { $selected ="selected"; }
	$hantei_type_html .= "<option value=\"-1\" ".$selected.">(未使用問題をリスト)</option>\n";
		}
	}
	if ($as_service_num > 0 && $as_hantei_type == 0) {
		$as_hantei_default_num = "0";
	}
	if ($hantei_type_html == "") {
		$hantei_type_html .= "<option value=\"0\">サービスを選択してください</option>\n";
		$as_hantei_type = 0;
	}


	//コース
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	$set_cnt = 0;
	if ($as_service_num != "0" && $as_hantei_type != "0") {	//サービスと判定タイプが選択されている場合
		$sql = "SELECT course.course_num, course.course_name".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, " .T_COURSE. " course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			// upd start hasegawa 2017/12/26 AWS移設 ソート条件追加
			// " AND service_course_list.service_num ='".$as_service_num."';";
			" AND service_course_list.service_num ='".$as_service_num."'".
			" ORDER BY course.list_num;";
			// upd end hasegawa 2017/12/26
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$selected = "";
				if ($list['course_num'] == $as_course_num) { $selected = "selected"; }
				$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
				$set_cnt += 1;
			}
		}
	}
	if ($set_cnt == 0 || $as_hantei_type == "-1") {
		unset($_SESSION['sub_session']['seltest_course']['as_course_num']);
		unset($_SESSION['sub_session']['seltest_course']['as_hantei_default_num']);
	}
	if ($as_service_num != "0" && $as_hantei_type == "2" && $as_course_num == "0") {
		$as_hantei_default_num = "0";
	}


	//判定名
	$hanteimei_html = "<option value=\"0\">選択して下さい</option>\n";
	if ($as_hantei_type == "1" || ($as_hantei_type == "2" && $as_course_num != "0")) {	//コース選択済み、または判定タイプをコース判定の場合
		//判定タイプが コース内判定 の場合は、コース選択可能にする
		$sql = "SELECT hantei_ms_default.hantei_default_num, hantei_ms_default.list_num, hantei_ms_default.hantei_name".
			" FROM ".T_HANTEI_MS_DEFAULT." hantei_ms_default ".
			" WHERE hantei_ms_default.mk_flg = '0'".
			" AND hantei_ms_default.service_num = '".$as_service_num."'".
			" AND hantei_ms_default.hantei_type = '".$as_hantei_type."'";
		if ($as_hantei_type == "2") { $sql .= " AND hantei_ms_default.course_num = '".$as_course_num."'"; }
		$sql .= " ORDER BY hantei_ms_default.hantei_name";

		$sql .= ";";
		if ($result = $cdb->query($sql)) {
			$default_test_num_count = $cdb->num_rows($result);
			if ($default_test_num_count > 0) {
				$chk_flg = 0;
				while ($list = $cdb->fetch_assoc($result)) {
					$selected = "";
					if ($list['hantei_default_num'] == $as_hantei_default_num) {
						$selected = "selected";
						$chk_flg = 1;
					}
					$hanteimei_html .= "<option value=\"".$list['hantei_default_num']."\" ".$selected.">".$list['hantei_name']."</option>\n";
				}
				if ($chk_flg == 0) {
					$as_hantei_default_num = "0";
				}
			} else {
				$as_hantei_default_num = "0";
			}
		}
	}


	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}

	//コース
	$sql  = "SELECT * FROM ".T_COURSE." WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$course_count = $cdb->num_rows($result);
	}
	if (!$course_count) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return $html;
	}

	//フォームタイプ
	$form_type_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach($L_FORM_TYPE as $key => $val) {
		if ($as_form_type == $key) { $selected = "selected"; } else { $selected = ""; }
		$form_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$form_type_html .= "</select>\n";


	$SEL_MENU_L = array();
	$SEL_MENU_L[0] = array('text'=>'テストタイプ', 'name'=>'as_test_type', 'onchange'=>'2', 'options'=>$test_type_html, 'Submit'=>'0');
	$SEL_MENU_L[2] = array('text'=>'判定タイプ', 'name'=>'as_hantei_type', 'onchange'=>'1', 'options'=>$hantei_type_html, 'Submit'=>'0');
	if ($as_hantei_type != "1") {
		//判定タイプが コース判定 の場合は、コース選択非表示
		if ($as_hantei_type != -1) {
			$SEL_MENU_L[3] = array('text'=>'コース', 'name'=>'as_course_num', 'onchange'=>'1', 'options'=>$couse_html, 'Submit'=>'0');
		}
	}

	if ($as_hantei_type != -1) {
		$SEL_MENU_L[4] = array('text'=>'判定名', 'name'=>'as_hantei_default_num', 'onchange'=>'1', 'options'=>$hanteimei_html, 'Submit'=>'0');
	}
	$SEL_MENU_L[5] = array('text'=>'出題形式', 'name'=>'as_form_type', 'onchange'=>'1', 'options'=>$form_type_html, 'Submit'=>'0');

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


	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"test_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_test_session\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"as_service_num\" value=\"8\">\n";	//"サービス"セレクトの非表示時は hidden で渡す

	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= $c_name_html;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= $sub_session_html;
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
 * POSTに対してsession設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function sub_seltest_session() {
	//既存のテスト問題を選択、を選択後にテストタイプを選んだ後
	if (strlen($_POST['test_type'])) { $_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type']; }
	if (strlen($_POST['test_type'])) { $_SESSION['sub_session']['select_course']['default_test_num'] = $_POST['default_test_num']; }
	if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['select_course']['form_type'] = $_POST['form_type']; }

	//絞込み操作
	if (strlen($_POST['as_test_type'])) { $_SESSION['sub_session']['seltest_course']['as_test_type'] = $_POST['as_test_type']; }
	if (strlen($_POST['as_service_num'])) { $_SESSION['sub_session']['seltest_course']['as_service_num'] = $_POST['as_service_num']; }
	if (strlen($_POST['as_hantei_type'])) { $_SESSION['sub_session']['seltest_course']['as_hantei_type'] = $_POST['as_hantei_type']; }
	if (strlen($_POST['as_course_num'])) { $_SESSION['sub_session']['seltest_course']['as_course_num'] = $_POST['as_course_num']; }
	if (strlen($_POST['as_hantei_default_num'])) { $_SESSION['sub_session']['seltest_course']['as_hantei_default_num'] = $_POST['as_hantei_default_num']; }
	if (strlen($_POST['as_form_type'])) { $_SESSION['sub_session']['seltest_course']['as_form_type'] = $_POST['as_form_type']; }
	if (strlen($_POST['as_page_view'])) { $_SESSION['sub_session']['seltest_course']['as_page_view'] = $_POST['as_page_view']; }

	return $ERROR;
}


//------------------


/**
 * 問題登録　すらら問題登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function ht_surala_add_addform($ERROR) {
//echo "ht_surala_add_addform()<br/>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;
	global $L_HANTEI_PROBLEM_TYPE;

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_HANTEI_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	$not_select = 0;	//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。その制御フラグ

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";

	} else {
		if ($_SESSION['sub_session']['surala_regist']['status'] == "1") {
			//すらら問題登録後、登録成功ならばメッセージ表示
			//$d_surala_regist_problem_num = $_SESSION['sub_session']['surala_regist']['problem_num'];
			//$d_surala_regist_disp_sort = $_SESSION['sub_session']['surala_regist']['disp_sort'];
			$m_pnum_array = $_POST['m_pnum'];
			$m_snum_array = $_POST['m_snum'];
			$html .= "<div class=\"small_error\">\n";
			foreach($m_pnum_array as $key => $val) {
				$d_surala_regist_problem_num = $val;
				$d_surala_regist_disp_sort = $m_snum_array[$key];	//表示順番号のPOST値

				$html .= "<br/>管理番号".$d_surala_regist_problem_num."の問題を、表示順 ".$d_surala_regist_disp_sort."番に";
				$html .= "登録しました。";
			}
			$html .= "</div>\n";
			unset($_SESSION['sub_session']['surala_regist']['status']);
			$not_select = 1;
		}
	}

	$html .= ht_select_course();
	if (!$_SESSION['sub_session']['select_course']['block_num']) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
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
		$html .= "問題は登録されておりません。<br><br>\n";
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
	if ($_SESSION['sub_session']['select_course']['s_page']) { $page = $_SESSION['sub_session']['select_course']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";

	// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
	// $sql  = "SELECT problem.problem_num," .
	// 	"problem.problem_type," .
	// 	"problem.display_problem_num," .
	// 	"problem.form_type," .
	// 	"problem.number_of_answers," .
	// 	"problem.error_msg," .
	// 	"problem.number_of_incorrect_answers," .
	// 	"problem.correct_answer_rate," .
	// 	"problem.display," .
	// 	"problem_att.standard_time".
	// 	" FROM ".T_PROBLEM." problem".
	// 	" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
	// 	" WHERE problem.state='0'".
	// 	" AND problem.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
	// 	" AND problem.block_num='".$_SESSION['sub_session']['select_course']['block_num']."'".
	// 	" ORDER BY problem.display_problem_num".$limit;
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
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num".
		" WHERE p.state='0'".
		" AND p.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
		" AND p.block_num='".$_SESSION['sub_session']['select_course']['block_num']."'".
		" ORDER BY p.display_problem_num".$limit;
	// upd end hasegawa 2018/04/03

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

		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";	//下から移動

		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>No.</td>\n";
		$html .= "<td>追加</td>\n";
		//$html .= "<td>no.</td>\n";
		$html .= "<td>表示順番号</td>\n";		// add okabe 2013/09/11
		$html .= "<td>すらら問題管理番号</td>\n";
		$html .= "<td>問題タイプ</td>\n";
		$html .= "<td>出題形式</td>\n";
		$html .= "<td>解答数</td>\n";
		$html .= "<td>不正解数</td>\n";
		$html .= "<td>正解率</td>\n";
		$html .= "<td>エラー</td>\n";
		$html .= "<td>表示・非表示</td>\n";
		$html .= "<td>確認</td>\n";
		$html .= "</tr>\n";
		$i = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			//$newform = new form_parts();
			//$newform->set_form_type("radio");
			//$newform->set_form_name("problem");
			//$newform->set_form_id("problem_".$list['problem_num']);
			//$newform->set_form_check($_POST['problem_num']);
			//if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
			//	$newform->set_form_value("".$list['problem_num']."");
			//}
			//$newform->set_form_action(" onclick=\"set_surala_problem_num('".$list['problem_num']."','".$list['standard_time']."');\"");
			//$problem_btn = $newform->make();

			//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //書写はテストに登録不能にする (2018/09/28時点)
			$form_attr = "";
			if($list['form_type'] == 15){
				$form_attr = "disabled";
				$problem_btn = preg_replace("/ /", " disabled ", $problem_btn, 1); //空白を" disabled "にかえる。最初の1回だけ。//form_partsに属性セット機能がなかったのでこれで。
			}
			//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応

			$html .= "<label for=\"problem_".$list['problem_num']."\">";
			$html .= "<tr class=\"member_form_cell\" >\n";
			//$html .= "<td>".$problem_btn."</td>\n";

			$html .= "<td align=\"center\">".($start+$i+1)."</td>\n";

			//問題選択チェックボックス
			$m_pnum_array = $_POST['m_pnum'];
			$checked_mark = "";
			if ($m_pnum_array[$i] != "" && !$not_select) { $checked_mark = "checked"; }
			//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//$html .= "<td><input type=\"checkbox\" name=\"m_pnum[".$i."]\" value=\"".$list['problem_num']."\" ".$checked_mark."/></td>\n";
			$html .= "<td><input {$form_attr} type=\"checkbox\" name=\"m_pnum[".$i."]\" value=\"".$list['problem_num']."\" ".$checked_mark."/></td>\n";
			//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応

			//表示順番号テキストボックス
			$m_snum_array = $_POST['m_snum'];
			if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
				//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
				//$html .= "<td><input type=\"text\" name=\"m_snum[]\" value=\"".$m_snum_array[$i]."\" size=\"5\" /></td>\n";
				$html .= "<td><input {$form_attr} type=\"text\" name=\"m_snum[]\" value=\"".$m_snum_array[$i]."\" size=\"5\" /></td>\n";
				//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			} else {
				//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
				//$html .= "<td><input type=\"text\" name=\"m_snum[]\" value=\"\" size=\"5\" /></td>\n";
				$html .= "<td><input {$form_attr} type=\"text\" name=\"m_snum[]\" value=\"\" size=\"5\" /></td>\n";
				//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			}
			$i = $i + 1;
			//$html .= "<td>".$list['display_problem_num']."</td>\n";

			$html .= "<td>".$list['problem_num']."</td>\n";
			$html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>".$list['number_of_answers']."</td>\n";
			$html .= "<td>".$list['number_of_incorrect_answers']."</td>\n";
			$html .= "<td>".$list['correct_answer_rate']."%</td>\n";
			if ($list['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
			$html .= "<td>".$error_msg."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			//$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			//$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			//$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('1','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
			$html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('1','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
			//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}

	$html .= "<br>\n";
	//$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";	//上へ移動
	$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_surala_check\">\n";
	//$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	//$html .= "<input type=\"hidden\" name=\"standard_time\" value=\"\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	//$tmp_disp_sort = "";	//入力フォームで入力、エラー再表示の場合。
	//if ($_POST['disp_sort']) { $tmp_disp_sort = $_POST['disp_sort']; }
	//if (!$not_select) {		//正常登録処理後は、ラジオボタンと表示順の数字を表示しない。
	//	$html .= "表示順番号  <input type=\"text\" size=\"5\" name=\"disp_sort\" value=\"".$tmp_disp_sort."\"><br>";
	//} else {
	//	$html .= "表示順番号  <input type=\"text\" size=\"5\" name=\"disp_sort\" value=\"\"><br>";
	//}

	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "</form>\n";

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_select_course() {
	//既存のすらら問題選択ページ用

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW;
	//コース
	$sql  = "SELECT course_num,course_name FROM " . T_COURSE .
			" WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if (!$max) {
		$html .= "コースを設定してからご利用下さい。<br>\n";
		return $html;
	}
	//$L_COURSE = ht_course_list();
	$L_COURSE = ht_course_list($_SESSION['sub_session']['add_type']['problem_type']);	//edit 2013/09/13
	if (!$_SESSION['sub_session']['select_course']['course_num']) { $_SESSION['sub_session']['select_course']['course_num'] = $_SESSION['t_practice']['course_num']; }
	$course_num_html = $L_COURSE[$_SESSION['sub_session']['select_course']['course_num']];

	foreach($L_COURSE as $key => $val) {
		if ($_SESSION['sub_session']['select_course']['course_num'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$course_num_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
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

		$sub_session_html = "";
		$disp_hantei_name = get_hantei_name($_SESSION['sub_session']['s_hantei_default_num']);
		if (strlen($disp_hantei_name) > 0) {
			$sub_session_html .= "判定名「".$disp_hantei_name."」に追加登録する問題を指定して下さい。<br/><br/>";
		}
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
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_course_session\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"s_page_view\" value=\"".$_SESSION['sub_session']['s_page_view']."\">\n";	//TOPリスト 件数/ページ
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

	$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
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
 * 問題登録　既存テストから登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_test_exist_check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;
	global $L_HANTEI_PROBLEM_TYPE;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ht_add") { $val = "ht_exist_add"; }
				//elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	}

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_HANTEI_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";


	$disp_sort = $_POST['disp_sort'];
	$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
	$disp_sort = trim($disp_sort);

	$table_type = "判定テスト専用";

	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	$html .= "<td>表示順番号</td>\n";
	$html .= "<td>問題管理番号</td>\n";
	$html .= "<th>問題種類</th>\n";
	$html .= "<th>出題形式</th>\n";
	$html .= "<th>回答目安時間</th>\n";
	$html .= "<th>確認</th>\n";
	$html .= "</tr>\n";


	if (MODE == "delete") { //削除 前準備
		$sql  = "SELECT *" .
			" FROM ". T_HANTEI_MS_PROBLEM. " hmp ".
			" WHERE hmp.mk_flg='0'".
			" AND hmp.problem_num='".$_POST['problem_num']."'";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		$html .= "<tr class=\"member_form_cell\" >\n";
		$html .= "<td>".$disp_sort."</td>\n";
		$html .= "<td>".$list['problem_num']."</td>\n";
		$html .= "<td>".$table_type."</td>\n";
		$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
		$html .= "<td>".$list['standard_time']."</td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('2','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";

		$html .= "<br>";
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



	} else {
		//登録 前準備
		$m_pnum_array = $_POST['m_pnum'];
		$m_snum_array = $_POST['m_snum'];
		$chk_dup_array = array();

		foreach ($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
			$each_problem_num = $val;

			$sql  = "SELECT *" .
				" FROM ". T_HANTEI_MS_PROBLEM. " hmp ".
				" WHERE hmp.mk_flg='0'".
				" AND hmp.problem_num='".$each_problem_num."'";

			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}

			$html .= "<tr class=\"member_form_cell\" >\n";
			$html .= "<td>".$each_disp_num."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";

			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>".$list['standard_time']."</td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('2','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
			$html .= "</form>\n";
			$html .= "</tr>\n";

		}
		$html .= "</table>\n";


		//他処理
		$html .= "<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
		$html .= $HIDDEN;
		$html .= "<input type=\"submit\" value=\"".$button."\">\n";
		//複数問題指定のPOST値の復元
		foreach($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
			$html .= "<input type=\"hidden\" name=\"m_pnum[".$key."]\" value=\"".$val."\">";	//すらら問題番号
			$html .= "<input type=\"hidden\" name=\"m_snum[".$key."]\" value=\"".$each_disp_num."\">";	//表示順番号
		}
		$html .= "</form>\n";


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

			//複数問題指定のPOST値の復元
			foreach($m_pnum_array as $key => $val) {
				$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
				$html .= "<input type=\"hidden\" name=\"m_pnum[".$key."]\" value=\"".$val."\">";	//すらら問題番号
				$html .= "<input type=\"hidden\" name=\"m_snum[".$key."]\" value=\"".$each_disp_num."\">";	//表示順番号
			}

		} else {
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		}
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";

	}

	$html .= "<br/>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 問題登録　既存テストから登録　項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_test_exist_check() {
//echo "ht_test_exist_check(),";
	global $L_PAGE_VIEW;

	$m_pnum_array = $_POST['m_pnum'];
	$m_snum_array = $_POST['m_snum'];
	$chk_dup_array = array();

	$as_page_view = $_SESSION['sub_session']['seltest_course']['as_page_view'];
	if ($as_page_view == "") { $as_page_view = "1"; }
	$page_view = $L_PAGE_VIEW[$as_page_view];
	if ($_SESSION['sub_session']['select_course']['s_page']) {
		$page = $_SESSION['sub_session']['select_course']['s_page'];
	} else {
		$page = 1;
	}
	$start = ($page - 1) * $page_view;

	// checkbox が checked されて POSTされたデータ (Value値が 問題番号）
	if (count($m_pnum_array) > 0) {
		$chk_snum_array = $m_snum_array;

		foreach($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値

			//１問ずつチェックする (今回の問題表示番号との重複も確認を)

			ht_test_exist_check_sub($ERROR, $val, $each_disp_num, $start+$key+1);
			unset($chk_snum_array[$key]);

			//この同じ入力フォーム内で、同じ表示順番号が入力されているかチェック
			if (in_array($each_disp_num, $chk_dup_array)) {
				if (strlen($each_disp_num) > 0) {
					$ERROR[] = ($start+$key+1).": 重複する表示順番号が入力されています。";
				}
			} else {
				array_push($chk_dup_array, $each_disp_num);
			}
		}

		//参照されなかった 表示順番号が残っていないか確認
		if (count($chk_snum_array) > 0) {
			$flg = 0;
			foreach($chk_snum_array as $key => $val) {
				if (strlen($chk_snum_array[$key]) > 0) { $flg = 1; }
			}
			if ($flg > 0) {
				$ERROR[] = "表示順番号が入力されていて、チェックされていない問題があります。";
			}
		}

	} else {
		//チェックされた件数がゼロ
		$ERROR[] = "問題を指定してください。";
	}

	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 * @param integer $problem_num
 * @param string $disp_sort
 * @param integer $line_no
 */
function ht_test_exist_check_sub(&$ERROR, $problem_num, $disp_sort, $line_no) {
//echo "ht_test_exist_check_sub(),";
	//$problem_num = $_POST['problem_num'];
	//if ($problem_num == 0) {
	//	$ERROR[] = "問題を指定してください。";
	//}

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$errro_detect = 0;
	$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
	//$disp_sort = $_POST['disp_sort'];

	if (!$disp_sort) {
		$ERROR[] = $line_no.": 表示順番号が未入力です。";
		$errro_detect = 1;
	} else {
		$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
		$disp_sort = trim($disp_sort);

		if (preg_match("/[^0-9]/",$disp_sort)) {
			$ERROR[] = $line_no.": 表示順番号は半角数字で入力してください。";
			$errro_detect = 1;
		} elseif ($disp_sort < 1) {
			$ERROR[] = $line_no.": 表示順番号は1以上で入力してください。";
			$errro_detect = 1;
		}
	}

	if (!$errro_detect) {
		//指定問題が、すでに登録済み(hantei_ms_default_problemに登録済み）かチェック
		$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT_PROBLEM ." hmdp ".
			" WHERE hmdp.mk_flg='0'".
			" AND hmdp.problem_num='".$problem_num."'".
			" AND hmdp.problem_table_type='2'".		//判定テスト専用問題
			" AND hmdp.hantei_default_num='".$s_hantei_default_num."';";
		$exist_chk = 0;
		if ($result = $cdb->query($sql)) {
			$exist_chk = $cdb->num_rows($result);
		}
		if ($exist_chk > 0) {
			$ERROR[] = $line_no.": 指定した問題は、すでに登録済みです。";
			$errro_detect = 1;
		}

		if (!$errro_detect) {
			//入力された番号が既に使用済みかチェック
			$sql  = "SELECT * FROM " .
				T_HANTEI_MS_DEFAULT_PROBLEM ." hmdp ".
				" WHERE hmdp.mk_flg='0'".
				" AND hmdp.hantei_default_num='".$s_hantei_default_num."'".
				" AND hmdp.disp_sort='".$disp_sort."';";
			$exist_chk_disp_sort = 1;
			if ($result = $cdb->query($sql)) {
				$exist_chk_disp_sort = $cdb->num_rows($result);
			}
			if ($exist_chk_disp_sort > 0) {
				$ERROR[] = $line_no.": 指定した問題の表示順番号は既に使用されています。";
				$errro_detect = 1;
			}
		}
	}

	return; // $ERROR;
}



/**
 * 問題登録　既存テストから登録　登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_test_exist_add() {
//echo "*ht_test_exist_add(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$m_pnum_array = $_POST['m_pnum'];
	$m_snum_array = $_POST['m_snum'];
	$chk_dup_array = array();

	foreach($m_pnum_array as $key => $val) {	//複数指定された問題の登録処理（繰り返し）
		$problem_num = $val;
		$disp_sort = $m_snum_array[$key];	//表示順番号のPOST値

		//$problem_num = $_POST['problem_num'];
		//$disp_sort = $_POST['disp_sort'];

		$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
		$disp_sort = trim($disp_sort);
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];

		if ($s_hantei_default_num > 0 && $problem_num > 0 && $disp_sort > 0) {
			//既存の判定テスト問題を、hantei_ms_default_problem で関連付ける
			$sql = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hantei_default_num='".$s_hantei_default_num."'".
				" AND problem_num='".$problem_num."'".
				" AND mk_flg='0'".
				" AND problem_table_type='2' LIMIT 1;";

			$exist_chk_data = 0;
			if ($result = $cdb->query($sql)) {
				$exist_chk_data = $cdb->num_rows($result);
			}
			if ($exist_chk_data > 0) {
				//既に登録データがある場合
				$ERROR[] = "指定した問題は、すでに登録済みです。";

			} else {
				//登録されていない場合
				$INSERT_DATA = array();
				$INSERT_DATA[hantei_default_num] 	= $s_hantei_default_num;
				//add start okabe 2013/09/05
				$INSERT_DATA[service_num] = $_SESSION['sub_session']['s_service_num'];
				$INSERT_DATA[hantei_type] = $_SESSION['sub_session']['s_hantei_type'];
				$INSERT_DATA[course_num] = $_SESSION['sub_session']['s_course_num'];
				//add end okabe 2013/09/05

				$INSERT_DATA[problem_num] 			= $problem_num;
				$INSERT_DATA[problem_table_type] 	= "2";
				$INSERT_DATA[disp_sort] 			= $disp_sort;

				$INSERT_DATA[ins_syr_id] 		= "addline";
				$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[ins_date] 			= "now()";
				$INSERT_DATA[upd_syr_id] 		= "addline";
				$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[upd_date] 			= "now()";
				$ERROR = $cdb->insert(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA);
				unset($INSERT_DATA);

				if (!$ERROR) {	//登録正常終了
					$_SESSION['sub_session']['surala_regist']['status'] = "2";
					$_SESSION['sub_session']['surala_regist']['problem_num'] = $problem_num;
					$_SESSION['sub_session']['surala_regist']['disp_sort'] = $disp_sort;
				}
			}

		}
	}
	return $ERROR;
}


/**
 * 問題登録　既存のすららテストから登録　項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_surala_add_check() {
//echo "ht_surala_add_check(),";
	global $L_PAGE_VIEW;

	$m_pnum_array = $_POST['m_pnum'];
	$m_snum_array = $_POST['m_snum'];
	$chk_dup_array = array();

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	if ($_SESSION['sub_session']['select_course']['s_page']) { $page = $_SESSION['sub_session']['select_course']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;

	// checkbox が checked されて POSTされたデータ (Value値が 問題番号）
	if (count($m_pnum_array) > 0) {
		$chk_snum_array = $m_snum_array;

		foreach($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値

			//１問ずつチェックする (今回の問題表示番号との重複も確認を)

			ht_surala_add_check_sub($ERROR, $val, $each_disp_num, $start+$key+1);
			unset($chk_snum_array[$key]);

			//この同じ入力フォーム内で、同じ表示順番号が入力されているかチェック
			if (in_array($each_disp_num, $chk_dup_array)) {
				if (strlen($each_disp_num) > 0) {
					$ERROR[] = ($start+$key+1).": 重複する表示順番号が入力されています。";
				}
			} else {
				array_push($chk_dup_array, $each_disp_num);
			}
		}

		//参照されなかった 表示順番号が残っていないか確認
		if (count($chk_snum_array) > 0) {
			$flg = 0;
			foreach($chk_snum_array as $key => $val) {
				if (strlen($chk_snum_array[$key]) > 0) { $flg = 1; }
			}
			if ($flg > 0) {
				$ERROR[] = "表示順番号が入力されていて、チェックされていない問題があります。";
			}
		}

	} else {
		//チェックされた件数がゼロ
		$ERROR[] = "問題を指定してください。";
	}

	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 * @param integer $problem_num
 * @param string $disp_sort
 * @param integer $line_no
 */
function ht_surala_add_check_sub(&$ERROR, $problem_num, $disp_sort, $line_no) {
//echo "ht_surala_add_check_sub(),";
	//$problem_num = $_POST['problem_num'];
	//if ($problem_num == 0) {
	//	$ERROR[] = "問題を指定してください。";
	//}

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$errro_detect = 0;
	$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];
	//$disp_sort = $_POST['disp_sort'];

	//if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	//if ($_SESSION['t_practice']['test_type'] == "4") {
	if (!$disp_sort) {
		$ERROR[] = $line_no.": 表示順番号が未入力です。";
		$errro_detect = 1;
	} else {
		$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
		$disp_sort = trim($disp_sort);

		if (preg_match("/[^0-9]/",$disp_sort)) {
			$ERROR[] = $line_no.": 表示順番号は半角数字で入力してください。";
			$errro_detect = 1;
		} elseif ($disp_sort < 1) {
			$ERROR[] = $line_no.": 表示順番号は1以上で入力してください。";
			$errro_detect = 1;
		}
	}

	if (!$errro_detect) {
		//指定問題が、すでに登録済み(hantei_ms_default_problemに登録済み）かチェック
		$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT_PROBLEM ." hmdp ".
			" WHERE hmdp.mk_flg='0'".
			" AND hmdp.problem_num='".$problem_num."'".
			" AND hmdp.problem_table_type='1'".		//すらら問題
			" AND hmdp.hantei_default_num='".$s_hantei_default_num."';";
		$exist_chk = 0;
		if ($result = $cdb->query($sql)) {
			$exist_chk = $cdb->num_rows($result);
		}
		if ($exist_chk > 0) {
			$ERROR[] = $line_no.": 指定した問題は、すでに登録済みです。";
			$errro_detect = 1;
		}

		if (!$errro_detect) {
			//入力された番号が既に使用済みかチェック
			$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT_PROBLEM ." hmdp ".
				" WHERE hmdp.mk_flg='0'".
				" AND hmdp.hantei_default_num='".$s_hantei_default_num."'".
				" AND hmdp.disp_sort='".$disp_sort."';";
			$exist_chk_disp_sort = 1;
			if ($result = $cdb->query($sql)) {
				$exist_chk_disp_sort = $cdb->num_rows($result);
			}
			if ($exist_chk_disp_sort > 0) {
				$ERROR[] = $line_no.": 指定した問題の表示順番号は既に使用されています。";
				$errro_detect = 1;
			}
		}
	}

	return;// $ERROR;
}


/**
 * 問題登録　すらら問題新規登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function ht_surala_add_check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;
	global $L_HANTEI_PROBLEM_TYPE;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "ht_add") { $val = "ht_surala_add"; }
				//elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
	}

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";

	$disp_hantei_name = get_hantei_name($_SESSION['sub_session']['s_hantei_default_num']);
	if (strlen($disp_hantei_name) > 0) {
		$html .= "判定名「".$disp_hantei_name."」<br/><br/>";
	}

//echo "**".$_SESSION['sub_session']['add_type']['problem_type']."<br>";
	//$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_HANTEI_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";

	$html .= "<td>表示順番号</td>\n";
	$html .= "<td>問題管理番号</td>\n";

	$html .= "<td>問題タイプ</td>\n";
	$html .= "<td>出題形式</td>\n";
	$html .= "<td>解答数</td>\n";
	$html .= "<td>不正解数</td>\n";
	$html .= "<td>正解率</td>\n";
	$html .= "<td>エラー</td>\n";
	$html .= "<td>表示・非表示</td>\n";
	$html .= "<td>確認</td>\n";
	$html .= "</tr>\n";

	if (MODE == "delete") { //削除 前準備
		// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
		// $sql  = "SELECT problem_num," .
		// 	"problem_type," .
		// 	"display_problem_num," .
		// 	"form_type," .
		// 	"number_of_answers," .
		// 	"error_msg," .
		// 	"number_of_incorrect_answers," .
		// 	"correct_answer_rate," .
		// 	"display" .
		// 	" FROM ".T_PROBLEM.
		// 	" WHERE state='0'".
		// 	" AND problem_num='".$problem_num."'".
		// 	" ORDER BY display_problem_num";
		$sql  = "SELECT ".
			"p.problem_num," .
			"p.problem_type," .
			"p.display_problem_num," .
			"p.form_type," .
			"pd.number_of_answers," .
			"p.error_msg," .
			"pd.number_of_incorrect_answers," .
			"pd.correct_answer_rate," .
			"p.display" .
			" FROM ".T_PROBLEM." p".
			" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num".
			" WHERE p.state='0'".
			" AND p.problem_num='".$problem_num."'".
			" ORDER BY p.display_problem_num";
		// upd end hasegawa 2018/04/03

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		$html .= "<tr class=\"member_form_cell\" >\n";
		$html .= "<td>".$_POST['disp_sort']."</td>\n";
		$html .= "<td>".$list['problem_num']."</td>\n";

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
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('1','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";

		$html .= "<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
		$html .= $HIDDEN;
		$html .= "<input type=\"submit\" value=\"".$button."\">\n";
		$html .= "</form>\n";
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



	} else {

		//登録 前準備
		$m_pnum_array = $_POST['m_pnum'];
		$m_snum_array = $_POST['m_snum'];
		$chk_dup_array = array();

		foreach ($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
			$each_problem_num = $val;

			// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
			// $sql  = "SELECT problem_num," .
			// 	"problem_type," .
			// 	"display_problem_num," .
			// 	"form_type," .
			// 	"number_of_answers," .
			// 	"error_msg," .
			// 	"number_of_incorrect_answers," .
			// 	"correct_answer_rate," .
			// 	"display" .
			// 	" FROM ".T_PROBLEM.
			// 	" WHERE state='0'".
			// 	" AND problem_num='".$each_problem_num."'".
			// 	" ORDER BY display_problem_num";
			$sql  = "SELECT ".
				"p.problem_num," .
				"p.problem_type," .
				"p.display_problem_num," .
				"p.form_type," .
				"pd.number_of_answers," .
				"p.error_msg," .
				"pd.number_of_incorrect_answers," .
				"pd.correct_answer_rate," .
				"p.display" .
				" FROM ".T_PROBLEM." p".
				" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num".
				" WHERE p.state='0'".
				" AND p.problem_num='".$each_problem_num."'".
				" ORDER BY p.display_problem_num";
			// upd end hasegawa 2018/04/03

			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}

			$html .= "<tr class=\"member_form_cell\" >\n";

			$html .= "<td>".$each_disp_num."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";

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
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_hantei_problem_win_open('1','".$list['problem_num']."','".$_SESSION['sub_session']['s_hantei_default_num']."'); return false;\"></td>\n";
			$html .= "</form>\n";
			$html .= "</tr>\n";

		}
		$html .= "</table>\n";

		//他処理
		$html .= "<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
		$html .= $HIDDEN;
		$html .= "<input type=\"submit\" value=\"".$button."\">\n";
		//複数問題指定のPOST値の復元
		foreach($m_pnum_array as $key => $val) {
			$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
			$html .= "<input type=\"hidden\" name=\"m_pnum[".$key."]\" value=\"".$val."\">";	//すらら問題番号
			$html .= "<input type=\"hidden\" name=\"m_snum[".$key."]\" value=\"".$each_disp_num."\">";	//表示順番号
		}
		$html .= "</form>\n";


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

			//複数問題指定のPOST値の復元
			foreach($m_pnum_array as $key => $val) {
				$each_disp_num = $m_snum_array[$key];	//表示順番号のPOST値
				$html .= "<input type=\"hidden\" name=\"m_pnum[".$key."]\" value=\"".$val."\">";	//すらら問題番号
				$html .= "<input type=\"hidden\" name=\"m_snum[".$key."]\" value=\"".$each_disp_num."\">";	//表示順番号
			}

		} else {
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		}

		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";

	}

	$html .= "<br/>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 問題登録　既存すらら問題　登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function ht_surala_add_add() {
//echo "*ht_surala_add_add(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$m_pnum_array = $_POST['m_pnum'];
	$m_snum_array = $_POST['m_snum'];
	$chk_dup_array = array();

	foreach($m_pnum_array as $key => $val) {	//複数指定された問題の登録処理（繰り返し）
		$problem_num = $val;
		$disp_sort = $m_snum_array[$key];	//表示順番号のPOST値

		//$problem_num = $_POST['problem_num'];
		//$disp_sort = $_POST['disp_sort'];

		$disp_sort = mb_convert_kana($disp_sort,"asKV", "UTF-8");
		$disp_sort = trim($disp_sort);
		$s_hantei_default_num = $_SESSION['sub_session']['s_hantei_default_num'];

		if ($s_hantei_default_num > 0 && $problem_num > 0 && $disp_sort > 0) {

			$sql = "SELECT * FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" WHERE hantei_default_num='".$s_hantei_default_num."'".
				" AND problem_num='".$problem_num."'".
				" AND mk_flg='0'".
				" AND problem_table_type='1' LIMIT 1;";

			$exist_chk_data = 0;
			if ($result = $cdb->query($sql)) {
				$exist_chk_data = $cdb->num_rows($result);
			}

			if ($exist_chk_data > 0) {
				$ERROR[] = "指定した問題は、すでに登録済みです。";

			} else {
				//登録されてないので、データ登録(insert)する
				$INSERT_DATA = array();
				$INSERT_DATA[hantei_default_num] 	= $s_hantei_default_num;
				//add start okabe 2013/09/05
				$INSERT_DATA[service_num] = $_SESSION['sub_session']['s_service_num'];
				$INSERT_DATA[hantei_type] = $_SESSION['sub_session']['s_hantei_type'];
				$INSERT_DATA[course_num] = $_SESSION['sub_session']['s_course_num'];
				//add end okabe 2013/09/05

				$INSERT_DATA[problem_num] 			= $problem_num;
				$INSERT_DATA[problem_table_type] 	= "1";
				$INSERT_DATA[disp_sort] 			= $disp_sort;

				$INSERT_DATA[ins_syr_id] 		= "addline";
				$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[ins_date] 			= "now()";
				$INSERT_DATA[upd_syr_id] 		= "addline";
				$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
				$INSERT_DATA[upd_date] 			= "now()";

				$ERROR = $cdb->insert(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA);

				unset($INSERT_DATA);

				if (!$ERROR) {	//登録正常終了
					$_SESSION['sub_session']['surala_regist']['status'] = "1";
					$_SESSION['sub_session']['surala_regist']['problem_num'] = $problem_num;
					$_SESSION['sub_session']['surala_regist']['disp_sort'] = $disp_sort;
				}

			}

		}
	}

	return $ERROR;
}


/**
 * 判定既定名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $hantei_default_num
 * @return string
 */
function get_hantei_name($hantei_default_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$hantei_name = "";
	$sql = "SELECT hmd.hantei_name".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
		" WHERE hmd.mk_flg = '0'".
		" AND hmd.hantei_default_num = '".$hantei_default_num."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$hantei_name = $list['hantei_name'];
	}
	return $hantei_name;
}


/**
 * マスター＆問題データ csv出力
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function ht_csv_export() {
	global $L_CSV_COLUMN;

	$sel_service_num = $_SESSION['sub_session']['s_service_num'];	//出力するサービス番号

	//list($csv_line,$ERROR) = ht_make_csv($L_CSV_COLUMN['hantei_test'], $sel_service_num, 1, 1);		//in test_check_problem_data.php
	list($csv_line,$ERROR) = ht_make_csv_problem($L_CSV_COLUMN['hantei_test_problem'], $sel_service_num, 1, 1);		//in test_check_problem_data.php
	if ($ERROR) { return $ERROR; }

	//$filename = "hantei_test_master_problem_".$sel_service_num.".csv";
	$filename = "hantei_test_problem_".$sel_service_num.".csv";

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
 * デバッグ用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $arg
 */
function test_echo($arg) {
	echo "<br/>-----<br/>";
	print_r($arg);
	echo "<br/>-----<br/>";
}

?>
