<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題設定/数学検定
 *
 * 履歴
 * 2015/09/14 初期設定
 *
 * @author Azet
 */

// hasegawa



/**
 * 数学検定HTMLを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string 数学検定HTML
 */
function math_test_start() {
//echo '■MODE => '.MODE;
//echo '■ACTION => '.ACTION;

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

	$CHECK_DATA = array();

	if (ACTION == "mt_add") {
		$ERROR = add();

	} elseif (ACTION == "mt_change") {	//登録済み判定テスト問題[変更]→[修正]ボタン押下時
		$ERROR = mt_change($CHECK_DATA);

	} elseif (ACTION == "mt_delete") {	//削除→確認画面にて[削除]押下時
		$ERROR = mt_change($CHECK_DATA);

	} elseif (ACTION == "sub_course_session") {
		// セッション管理は/_www/admin_lib/test_problem.php
		//$ERROR = sub_setpage_session();

	} elseif (ACTION == "sub_test_session") {	//既存のテスト問題を選択、を選択後にテストタイプを選んだ後
		$ERROR = sub_seltest_session();

	} elseif (ACTION == "mt_exist_check") {		//既存のテスト問題を選んで、[追加確認]を押したときの処理
		$ERROR = mt_test_exist_check();

	} elseif (ACTION == "mt_exist_add") {		//既存のテスト問題を、最終的に[登録]する処理
		$ERROR = mt_test_exist_add($CHECK_DATA);

	} elseif (ACTION == "mt_surala_check") {	//既存のすらら問題を選んで、[追加確認]を押したときの処理
		$ERROR = mt_surala_add_check($CHECK_DATA);

	} elseif (ACTION == "mt_surala_add") {		//既存のすらら問題を、最終的に[登録]する処理
		$ERROR = mt_surala_add_add($CHECK_DATA);

	} elseif (ACTION == "mt_problem_add") {		//(新規登録) 入力フォーム[追加]→確認画面で[登録]押下時
		$ERROR = mt_test_add_add($CHECK_DATA);

	} elseif (ACTION == "mt_export") {			//エクスポート
		mt_csv_export();

	} elseif (ACTION == "mt_import") {			//インポート
		list($html,$ERROR) = mt_csv_import();
	}

	// add start 2017/05/24 yoshizawa
	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
	}
	// add end 2017/05/24 yoshizawa

	$html .= mt_select_unit_view();

	if (MODE == "mt_view") {			//登録済み問題リスト画面の [変更]ボタンを押したとき
		if (ACTION == "mt_problem_check") {
			mt_sub_session();

			$ERROR = mt_test_add_check();
			if (!$ERROR) {
				//エラー無し
				$html .= mt_test_add_check_html();
			} else {
				//エラー有り
				$html .= view($ERROR);
			}

		}elseif (ACTION == "mt_surala_check") {

			if (!$ERROR) {
				//エラー無し
				$html .= mt_surala_add_check_html();
			} else {
				//エラー有り
				$html .= view($ERROR);
			}

		}elseif(ACTION == "mt_change") {
			if (!$ERROR) {
				//エラー無し
				$html .= math_test_list($ERROR);
			} else {
				//エラー有り
				$html .= view($ERROR);
			}
		}else{
			$html .= view($ERROR);
		}

	} elseif (MODE == "mt_add") {
		if ($_SESSION['sub_session']['add_type']['problem_type'] != "test") {	//'既存の' 'すらら問題' 登録

			if (ACTION == "mt_surala_check") {
				if (!$ERROR) {
					//エラー無し
					$html .= mt_surala_add_check_html();
				} else {
					//エラー有り
					$html .= mt_surala_add_addform($ERROR);		//既存のすらら問題選択フォーム表示時
				}
			} elseif (ACTION == "mt_surala_add") {
				if (!$ERROR) {
					//エラー無し
					$html .= math_test_list($ERROR);
				} else {
					//エラー有り
					$html .= mt_surala_add_addform($ERROR);
				}
			} else {
				$html .= mt_surala_add_addform($ERROR);		//既存のすらら問題を登録 を押下後
			}
		} elseif ($_SESSION['sub_session']['add_type']['problem_type'] == "test") {		//'新規の' '数学検定問題' 登録
			if ($_SESSION['sub_session']['add_type']['add_type'] == "add") {
				if (ACTION == "mt_problem_check") {

					mt_sub_session();

					$ERROR = mt_test_add_check();

					if (!$ERROR) {		//入力フォームで [追加] 押下時
						//エラー無し
						$html .= mt_test_add_check_html();
					} else {
						//エラー有り
						$html .= addform($ERROR);
					}
				} elseif (ACTION == "mt_problem_add") {
					if (!$ERROR) { 		//入力フォーム[追加]→確認画面で[登録]押下時
						//エラー無し
						//$html .= math_test_list($ERROR, $PROC_RESULT);
						$html .= math_test_list($ERROR);
					} else {
						//エラー有り
						$html .= addform($ERROR);
					}
				} else {	//入力フォーム表示時
					$html .= addform($ERROR);
				}

			} elseif ($_SESSION['sub_session']['add_type']['add_type'] == "exist") {		//'既存の' '数学検定問題' 登録
				if (ACTION == "mt_exist_check") {
					if (!$ERROR) {
						//エラー無し
						$html .= mt_test_exist_check_html();
					} else {
						//エラー有り
						// /_www/admin_lib/test_problem.phpのfunctionを共通使用
						$html .= test_exist_addform($ERROR);
					}
				} elseif (ACTION == "mt_exist_add") {
					if (!$ERROR) {
						//エラー無し
						$html .= math_test_list($ERROR);
					} else {
						//エラー有り
						$html .= math_test_list($ERROR);
					}
				} else {
					// /_www/admin_lib/test_problem.phpのfunctionを共通使用
					$html .= test_exist_addform($ERROR);		//既存の問題選択フォーム表示時
				}
			}
		}
	} elseif (MODE == "mt_delete") {		//一覧の削除ボタンを押したとき

		if (ACTION == "mt_delete") {
			if (!$ERROR) {		//エラー無し
				$html .= math_test_list($ERROR);

			} else {			//エラー有り
				$html .= math_test_list($ERROR);

			}

		} else {
			if ($_POST['problem_table_type'] == 2){
				$html .= mt_test_add_check_html();		//一覧の削除ボタンを押下後最初に確認画面を表示

			}elseif ($_POST['problem_table_type'] == 1){
				$html .= mt_surala_add_check_html();

			}
		}
	} else {
		$html .= math_test_list($ERROR);

	}

	return $html;
}

/**
 * テストタイプ、級、単元選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string テストタイプ、級、単元選択HTML
 */
function mt_select_unit_view() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_TEST_TYPE, $L_DESC, $L_PAGE_VIEW ,$L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	global $L_TEST_TYPE, $L_DESC, $L_PAGE_VIEW;
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発

	global $L_TEST_ADD_TYPE, $L_TEST_PROBLEM_TYPE;

	unset($_SESSION['UPDATE']);	//add okabe 2013/09/13

	$s_class_id = "select";
	$s_page_view = "2";
	$s_page = 1;

	if (ACTION == "sub_session") {
		if (strlen($_POST['s_class_id'])) { $_SESSION['sub_session']['s_class_id'] = $_POST['s_class_id']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
		if (strlen($_POST['add_type'])) { $_SESSION['sub_session']['add_type']['add_type'] = $_POST['add_type']; }
		if (strlen($_POST['problem_type'])) { $_SESSION['sub_session']['add_type']['problem_type'] = $_POST['problem_type']; }
		if (strlen($_POST['s_page']))	{ $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }
		// add start hirose 2020/09/21 テスト標準化開発
		if (strlen($_POST['s_class_id'])) { 
			$sql  = "SELECT ".
			 " c.course_num ".
			 " ,c.course_name ".
			 " FROM " . T_MATH_TEST_BOOK_INFO ." mb ".
			 " INNER JOIN " . T_COURSE ." c ON mb.course_num = c.course_num".
			 " WHERE mb.mk_flg='0'".
			 " AND c.state='0'".
			 " AND mb.class_id='".$_SESSION['sub_session']['s_class_id']."'".
			 " ;";
			// print $sql;
			if ($result = $cdb->query($sql)) {
				$class_info = $cdb->fetch_assoc($result);
			}
			if($class_info){
				$_SESSION['t_practice']['course_num'] = $class_info['course_num']; 
			}
		}
		// add end hirose 2020/09/21 テスト標準化開発
	}
	if (MODE == "set_problem_view") {
		$_SESSION['sub_session']['s_page'] = 1; 			//表示データ切り替えたらページを１にリセット
		$_SESSION['sub_session']['add_type']['add_type'] = "";
		$_SESSION['sub_session']['add_type']['problem_type'] = "";
		unset($_SESSION['sub_session']['select_class_id']);	//既存問題の登録途中で、画面が切り替えられたらリセット
	}

	// add start hirose 2020/09/22 テスト標準化開発
	if (ACTION == "view_session") {
		unset($_SESSION['sub_session']['s_class_id']);
	}
	// add end hirose 2020/09/22 テスト標準化開発


	if ($_SESSION['sub_session']['s_class_id']) {
		$s_class_id = $_SESSION['sub_session']['s_class_id'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
		$s_page = $_SESSION['sub_session']['s_page'];
	}

	if (strlen($_POST['form_type'])) {
		$_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type'];
	}
	// テストタイプ
	$test_type_html = "";
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}

	// 級
	$class_html  = "";
	$selected = "";
	foreach ($L_MATH_TEST_CLASS as $key=> $val) {
		$selected = "";
		if ($s_class_id == $key) {
			$selected = "selected";
		}
		$class_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	if($s_class_id == select){
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

	$html .= "<br/>";
	$s_page_save = $_SESSION['sub_session']['s_page'];
	$s_select_course = $_SESSION['sub_session']['select_course'];
	$s_page_view = $_SESSION['sub_session']['s_page_view'];
	$bak_as_test_type = $_SESSION['sub_session']['seltest_course']['as_test_type'];
	$bak_as_class_id = $_SESSION['sub_session']['seltest_course']['as_class_id'];
	$bak_as_book_unit_id = $_SESSION['sub_session']['seltest_course']['as_book_unit_id'];
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

	$_SESSION['sub_session']['s_class_id'] = $s_class_id;
	$_SESSION['sub_session']['s_page_view'] = $s_page_view;

	//既存の判定テスト問題登録、絞込みメニュー
	$_SESSION['sub_session']['seltest_course']['as_test_type'] = $bak_as_test_type;
	$_SESSION['sub_session']['seltest_course']['as_class_id'] = $bak_as_class_id;
	$_SESSION['sub_session']['seltest_course']['as_book_unit_id'] = $bak_as_book_unit_id;
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
function mt_sub_session() {
	if (strlen($_POST['form_type'])) {
		$_SESSION['sub_session']['add_type']['form_type'] = $_POST['form_type'];
	}
	return;
}



/**
 * 数学検定問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR エラーメッセージ一覧
 * @return string 数学検定問題一覧HTML
 */
function math_test_list($ERROR) {	//テストタイプ選択後、級を選択したら登録リスト表示

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	// global $L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	
	// add start 2020/09/15 phong テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発

	global $L_EXP_CHA_CODE;

	unset($_SESSION['sub_session']['select_class_id']);
	// add start hirose 2020/09/22 テスト標準化開発
	unset($_SESSION['sub_session']['select_course']['test_type']);
	unset($_SESSION['sub_session']['select_course']['course_num']);
	unset($_SESSION['sub_session']['select_course']['stage_num']);
	unset($_SESSION['sub_session']['select_course']['lesson_num']);
	unset($_SESSION['sub_session']['select_course']['unit_num']);
	unset($_SESSION['sub_session']['select_course']['block_num']);
	// add end hirose 2020/09/22 テスト標準化開発

	$html = "";
	$page = 1;

	if ($ERROR) {
		$html .= "<br/><div class=\"small_error\">\n";		//インポートエラーもここで表示(既に他のエラー時表示あり)
		$html .= ERROR($ERROR);
		$html .= "</div>\n";

	} else {
		if ($_SESSION['sub_session']['surala_regist']['status'] == "3") {
			//数学検定問題登録後、登録成功ならばメッセージ表示、
			$html .= "<div class=\"small_error\">\n";
			$html .= "新規問題を登録しました。";
			$html .= "</div>\n";
			unset($_SESSION['sub_session']['surala_regist']['status']);
		}
	}

	if ($_SESSION['sub_session']) {
		$s_class_id = $_SESSION['sub_session']['s_class_id'];
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

		//	ファイルアップロード用仮設定
		$img_ftp = FTP_URL."test_img/";
		$voice_ftp = FTP_URL."test_voice/";
		$test_img_ftp = FTP_TEST_URL."test_img/".$_SESSION['t_practice']['course_num']."/";
		$test_voice_ftp = FTP_TEST_URL."test_voice/".$_SESSION['t_practice']['course_num']."/";
		
		//add start hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
		$html .= FTP_EXPLORER_MESSAGE; 
		$html .= "<br>\n";
		//$html .= "<br><br>\n";
		//add end hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
		if ($s_class_id) {
			$html .= "<a href=\"".$test_img_ftp."\" target=\"_blank\">テンポラリー画像フォルダー($test_img_ftp)</a><br>\n";
			$html .= "<a href=\"".$test_voice_ftp."\" target=\"_blank\">テンポラリー音声フォルダー($test_voice_ftp)</a><br>\n";
		}
		$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
		$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br><br>\n";

		if ($s_class_id != "select" && $s_book_unit_id == 0){
			$html .="<br><strong>級: ".$class_name."</strong><br>\n";
		}

		$html .= "インポートする場合は、問題設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"mt_test_list\">\n"; // インポート後に一覧へ遷移するためのダミー"mode"
		$html .= "<input type=\"hidden\" name=\"action\" value=\"mt_import\">\n";
		$html .= "<input type=\"hidden\" name=\"class_id\" value=\"".$s_class_id."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"mt_export\">\n";
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

		// 新規登録ボタンは級が選択されているときのみ表示
		foreach($L_TEST_ADD_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['add_type']['add_type'] == $key) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$add_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		if ($_SESSION['sub_session']['add_type']['add_type']) {
			$problem_type_html .= "<select name=\"problem_type\" onchange=\"submit();\" style=\"float:left;\">";
			foreach($L_TEST_PROBLEM_TYPE as $key => $val) {
				if ($_SESSION['sub_session']['add_type']['add_type'] == "add" && ($key != "0" && $key != "test") ) { continue; }
				if ($_SESSION['sub_session']['add_type']['problem_type'] == $key) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$problem_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
			$problem_type_html .= "</select>\n";
		}

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
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"mt_add\">\n";
			$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
			$html .= "を<input type=\"submit\" value=\"登録\">\n";
			$html .= "</form>\n";
		}
		$_SESSION['sub_session']['add_type']['form_type'] = "";
		$html .= "<br>";

		$sql = " SELECT ". //すらら問題
				 "  butp.problem_num, ".
				 "  butp.problem_table_type, ".
				 "  p.form_type, ".
				 "  mpa.standard_time ".
				 "  ,mtcp.disp_sort ".// add hirose 2020/09/18 テスト標準化開発
				 " FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp ".
				 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$s_class_id."'".
				 " LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num = butp.problem_num".
				 // add start hirose 2020/09/18 テスト標準化開発
				 " LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = p.problem_num ".
				 " AND mtcp.class_id='".$s_class_id."' AND mtcp.problem_table_type='1' ".
				 // add end hirose 2020/09/18 テスト標準化開発
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
				 "  ,mtcp.disp_sort ".// add hirose 2020/09/18 テスト標準化開発
				 " FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$s_class_id."'".
				 " LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg = '0' AND mtp.problem_num = butp.problem_num".
				 // add start hirose 2020/09/18 テスト標準化開発
				 " LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = mtp.problem_num ".
				 " AND mtcp.class_id='".$s_class_id."' AND mtcp.problem_table_type='2' ".
				 // add ebd hirose 2020/09/18 テスト標準化開発
				 " WHERE butp.mk_flg='0'".
				 "   AND butp.problem_table_type='2'".
				 " GROUP BY problem_num ".
				 // upd start hirose 2020/09/18 テスト標準化開発
				//  " ORDER BY problem_num ";
				 " ORDER BY disp_sort ";
				 // upd end hirose 2020/09/18 テスト標準化開発
//echo $sql;
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count == 0) {
			$html .= "<br>\n";
			$html .= "問題は登録されておりません。<br>\n";
			return $html;
		} else {
			if (!isset($_SESSION['sub_session']['select_course']['s_page_view'])) {
				$_SESSION['sub_session']['select_course']['s_page_view'] = 1;
			}

			//表示数の選択
			if ($L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]) {
				$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']];
			} else {
				$page_view = $L_PAGE_VIEW[1];
			}
			$max_page = ceil($count/$page_view);

			if ($_SESSION['sub_session']['s_page']) {
				$page = $_SESSION['sub_session']['s_page'];
			} else {
				$page = 1;
			}

			if ($page > $max_page) {
				$page = $max_page;
			}
			$start = ($page - 1) * $page_view;
			$next = $page + 1;
			$back = $page - 1;

			$sql .= " LIMIT ".$start.",".$page_view.";";

			//ページ数
			foreach ($L_PAGE_VIEW as $key => $val){
				if ($_SESSION['sub_session']['select_course']['s_page_view'] == $key) {
					$sel = " selected";
				} else {
					$sel = "";
				}
				$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
			}
			$sub_session_html = "";
			if ($s_book_unit_id != 0) {
				$book_unit_name = get_book_unit_name($s_book_unit_id);
				$sub_session_html .= "単元名「".$book_unit_name."」登録済み問題リスト<br/><br/>";
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
			// upd start hirose 2020/09/22 テスト標準化開発
			//気づき修正　ここでMODEを使うと、既存すらら登録後、設定で再度登録画面に行ってしまう。
			// $sub_session_html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
			$sub_session_html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
			// upd end hirose 2020/09/22 テスト標準化開発
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
				$html .= "<th>No</th>\n";// add hirose 2020/09/17 テスト標準化開発
				$html .= "<th>問題種類</th>\n";
				$html .= "<th>出題形式</th>\n";
				$html .= "<th>回答目安時間</th>\n";
				$html .= "<th>出題単元ID</th>\n";
				$html .= "<th>採点単元ID</th>\n";
				$html .= "<th>確認</th>\n";
				$html .= "<th>詳細</th>\n";
				$html .= "<th>削除</th>\n";
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
					$html .= "<td>".$list['disp_sort']."</td>\n";// add hirose 2020/09/17 テスト標準化開発
					$html .= "<td>".$table_type."</td>\n";
					$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
					$html .= "<td>".$list['standard_time']."</td>\n";
					$html .= "<td>".get_syutsudai_tangen_id_string($s_class_id, $list['problem_num'])."</td>\n";
					$html .= "<td>".get_saiten_tangen_id_string($s_class_id, $list['problem_num'])."</td>\n";
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
					$html .= "<td>\n";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"mt_view\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
					$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					$html .= "<input type=\"submit\" value=\"変更\">\n";
					$html .= "</form>\n";
					$html .= "</td>\n";
					$html .= "<td>\n";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"mt_delete\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
					$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
					$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
					$html .= "<input type=\"submit\" value=\"削除\">\n";
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
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR エラーメッセージ一覧
 * @return string 新規登録画面HTML
 */
function addform($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE;

	// unset($L_FORM_TYPE[14]);	// add hasegawa 2018/03/23 百マス計算 // del hirose 2020/09/18 テスト標準化開発

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

	$html .= select_mt_form_type();

	if (!$form_type || ($form_type == 13 && !$drawing_type)) {	// upd hasegawa 2016/06/02 作図ツール
		$html .= "<br/>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";

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
 * 問題登録 テスト専用問題新規登録 フォームタイプ選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string フォームタイプ選択HTML
 */
function select_mt_form_type() {
//echo "*select_mt_form_type(), ";

	global $L_FORM_TYPE,$L_DRAWING_TYPE;	// upd hasegawa 2016/06/02 作図ツール $L_DRAWING_TYPE追加

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

	// add start hasegawa 2016/06/01 作図ツール
	} elseif($_SESSION['sub_session']['add_type']['form_type'] == 13 && !$drawing_type){
		//フォームタイプ
		$drawing_type_html = "<option value=\"0\">選択して下さい</option>\n";
		foreach($L_DRAWING_TYPE as $key => $val) {
			// update start 2016/08/30 yoshizawa 作図ツール
			// if ($drawing_type == $key) { $selected = "selected"; } else { $selected = ""; }
			// $drawing_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
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
		$html .= "<input type=\"hidden\" name=\"form_type\" value=\"".$_SESSION['sub_session']['add_type']['form_type']."\">\n";
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
 * 新規登録・変更フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR エラーメッセージ一覧
 * @return string 新規登録・変更フォームHTML
 */
function view($ERROR) {
//echo "*view(), ";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_SENTENCE_FLAG,$L_DRAWING_TYPE;	// add hasegawa 2016/06/02 作図ツール
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発

	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}


	//変更の場合(問題番号がある場合)
	if($_POST['problem_num']){

		$problem_table_type = $_POST['problem_table_type'];
		$problem_num = $_POST['problem_num'];

		$sql = "SELECT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				" WHERE butp.problem_num= '".$problem_num."'".
				" AND butp.mk_flg= '0'".
				" LIMIT 1";
		if ($result = $cdb->query($sql)) {
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$book_unit_id = $list['book_unit_id'];
			}
		}

		//テスト問題かすらら問題か判断
		if ($problem_table_type == 2) {	//テスト問題
			$sql = "SELECT * FROM ".T_MS_TEST_PROBLEM.
					" WHERE problem_num='".$problem_num."'".
					" AND mk_flg='0'";
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
			$action = "mt_problem_check";

		} elseif ($problem_table_type == 1) {	//すらら問題
			// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
			// $sql  = "SELECT *" .
			// 	" FROM ". T_PROBLEM ." p" .
			// 	" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num = p.problem_num".
			// 	" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.block_num = p.block_num  ".
			// 	" WHERE p.state='0'".
			// 	"   AND butp.problem_table_type='".$problem_table_type."'".
			// 	"   AND p.problem_num='".$problem_num."' LIMIT 1";
			$sql  = "SELECT p.problem_num, p.course_num, p.block_num, p.display_problem_num,".
				" p.problem_type, p.sub_display_problem_num, p.form_type, p.question,".
				" p.problem, p.voice_data, p.hint, p.explanation, p.answer_time,".
				" p.parameter, p.set_difficulty, p.hint_number, p.correct_number,".
				" p.clear_number, p.first_problem, p.latter_problem, p.selection_words,".
				" p.correct, p.option1, p.option2, p.option3, p.option4, p.option5,".
				" p.sentence_flag, pd.number_of_answers, pd.number_of_incorrect_answers,".
				" pd.correct_answer_rate, pd.auto_difficulty, p.error_msg,".
				" p.update_time, p.display, p.state".
				" ,mpa.standard_time".// add hirose 2020/09/21 テスト標準化開発
				" FROM ". T_PROBLEM ." p" .
				" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num = p.problem_num".
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.block_num = p.block_num  ".
				" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num  ".
				" WHERE p.state='0'".
				"   AND butp.problem_table_type='".$problem_table_type."'".
				"   AND p.problem_num='".$problem_num."' LIMIT 1";
			// upd end hasegawa 2018/04/03
			if ($result = $cdb->query($sql)) {
				$list2 = $cdb->fetch_assoc($result);
			}
			$action = "mt_surala_check";
		}

		$form_type  = $list2['form_type'];
		$question = $list2['question'];
		$problem = $list2['problem'];
		$voice_data = $list2['voice_data'];
		$hint = $list2['hint'];
		$explanation = $list2['explanation'];
		$standard_time = $list2['standard_time'];
		$parameter = $list2['parameter'];
		$first_problem = $list2['first_problem'];
		$latter_problem = $list2['latter_problem'];
		$selection_words = $list2['selection_words'];
		$correct = $list2['correct'];
		$option1 = $list2['option1'];
		$option2 = $list2['option2'];
		$option3 = $list2['option3'];
		$option4 = $list2['option4'];
		$option5 = $list2['option5'];

		// 採点単元
		$book_unit_id = get_book_unit($problem_table_type, $problem_num, $_SESSION['sub_session']['s_class_id']);
		// 出題単元
		$control_unit_id = get_control_unit_id($problem_table_type, $problem_num, $_SESSION['sub_session']['s_class_id']);
		// LMS単元
		$lms_unit_id = get_lms_book_unit($problem_table_type, $problem_num);

		$button = "修正確認";

	//新規登録の場合(問題番号がない場合)
	}else{
		if ($_SESSION['sub_session']['add_type']['form_type']) {
			$form_type = $_SESSION['sub_session']['add_type']['form_type'];
		}
		if ($_POST['problem_table_type']){
			$problem_table_type = $_POST['problem_table_type'];
		}else{
			$problem_table_type = 0;
		}

		$action = "mt_problem_check";
		$button = "追加確認";
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

	//既存のテスト問題の場合
	if ($problem_table_type == 2 || $problem_table_type == 0) {
		$inc_file = "math_test_problem_form_type_" . $form_type . ".htm";

	//既存のすらら問題の場合↓
	} elseif ($problem_table_type == 1) {

		$inc_file = MATH_TEST_PROBLEM_ADD_FORM;

		$html .= "<br>";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<td>no.</td>\n";
		$html .= "<td>出題形式</td>\n";
		$html .= "<td>解答数</td>\n";
		$html .= "<td>不正解数</td>\n";
		$html .= "<td>正解率</td>\n";
		$html .= "<td>エラー</td>\n";
		$html .= "<td>表示・非表示</td>\n";
		$html .= "<td>確認</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"member_form_cell\" >\n";
		$html .= "<td>".$list2['display_problem_num']."</td>\n";
		$html .= "<td>".$L_FORM_TYPE[$form_type]."</td>\n";
		$html .= "<td>".$list2['number_of_answers']."</td>\n";
		$html .= "<td>".$list2['number_of_incorrect_answers']."</td>\n";
		$html .= "<td>".$list2['correct_answer_rate']."%</td>\n";
		if ($list['error_msg']) {
			$error_msg = "エラー有";
		} else {
			$error_msg = "----";
		}
		$html .= "<td>".$error_msg."</td>\n";
		$html .= "<td>".$L_DISPLAY[$list2['display']]."</td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list2['problem_num']."\">\n";
		// upd start hirose 2020/10/01 テスト標準化開発
		// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$problem_table_type."','".$list2['problem_num']."')\"></td>\n";
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$problem_table_type."','".$list2['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\"></td>\n";
		// upd end hirose 2020/10/01 テスト標準化開発
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
	}

	$control_unit_id_att = "<br><span style=\"color:red;\">※設定したい出題単元(小問)のＩＤを入力して下さい。";
	$control_unit_id_att .= "<br>※複数設定できません。</span>";
	$book_unit_id_att = "<br><span style=\"color:red;\">※設定したい採点単元(結果グラフ単元)のＩＤを入力して下さい。";
	$book_unit_id_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したい復習ユニットのユニット番号を入力して下さい。";
	$lms_unit_att .= "<br>※複数設定する場合は、紐づく復習ユニットを「 :: 」区切りで入力して下さい。</span>";

	//問題管理番号
	if ($problem_num) {
		$INPUTS['PROBLEMNUM'] = array('result'=>'plane','value'=>$problem_num."<input type=\"hidden\" name=\"problem_num\" value=\"".$problem_num."\">");
	} else {
		$INPUTS['PROBLEMNUM'] = array('result'=>'plane','value'=>"---");
	}

	if(ACTION){
		$form_type  = $_POST['form_type'];
		$drawing_type  = $_POST['drawing_type']; // add hasegawa 2016/06/02 作図ツール
		$question = $_POST['question'];
		$problem = $_POST['problem'];
		$voice_data = $_POST['voice_data'];
		$hint = $_POST['hint'];
		$explanation = $_POST['explanation'];
		$_POST['standard_time'] = mb_convert_kana($_POST['standard_time'],"as", "UTF-8");
		$standard_time = $_POST['standard_time'];
		$parameter = $_POST['parameter'];
		$first_problem = $_POST['first_problem'];
		$latter_problem = $_POST['latter_problem'];
		$selection_words = $_POST['selection_words'];
		$correct = $_POST['correct'];
		$option1 = $_POST['option1'];
		$option2 = $_POST['option2'];
		// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($form_type == 3 && !$option2) {
			$input_row = $_POST['input_row'];
			$input_size = $_POST['input_size'];
		}
		// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加

		$option3 = $_POST['option3'];
		$option4 = $_POST['option4'];
		$option5 = $_POST['option5'];
		$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
		$control_unit_id = $_POST['control_unit_id'];
		$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
		$book_unit_id = $_POST['book_unit_id'];
		$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
		$lms_unit_id = $_POST['lms_unit_id'];
	}

	// add start hasegawa 2016/06/02 作図ツール
	$drawing_html = "";
	if($_SESSION['sub_session']['add_type']['form_type'] ==13 || $form_type == 13) {
		if(!$drawing_type) { $drawing_type = $option1; }
		$INPUTS['DRAWINGTYPE'] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
		$drawing_html = "<input type=\"hidden\" name=\"option1\" value=\"$drawing_type\">\n";
	}
	// add end hasegawa 2016/06/02

	$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	$INPUTS['FORMTYPE'] = array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);													//出題形式
	$INPUTS['QUESTION'] = array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$question);					//質問文
	$INPUTS['PROBLEM'] = array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$problem);						//問題文
	$INPUTS['VOICEDATA'] = array('type'=>'text','name'=>'voice_data','value'=>$voice_data,'size'=>'50');								//音声
	$INPUTS['HINT'] = array('type'=>'textarea','name'=>'hint','cols'=>'50','rows'=>'5','value'=>$hint);								//ヒント
	$INPUTS['EXPLANATION'] = array('type'=>'textarea','name'=>'explanation','cols'=>'50','rows'=>'5','value'=>$explanation);			//解説文
	$INPUTS['PARAMETER'] = array('type'=>'text','name'=>'parameter','value'=>$parameter,'size'=>'50');								//パラメータ
	$INPUTS['FIRSTPROBLEM'] = array('type'=>'textarea','name'=>'first_problem','cols'=>'50','rows'=>'5','value'=>$first_problem);		//問題前半
	$INPUTS['LATTERPROBLEM'] = array('type'=>'textarea','name'=>'latter_problem','cols'=>'50','rows'=>'5','value'=>$latter_problem);	//問題後半
	if ($form_type !=4 && $form_type != 8 && $form_type != 10) {
		$INPUTS['SELECTIONWORDS'] = array('type'=>'text','name'=>'selection_words','value'=>$selection_words,'size'=>'50');			//出題項目
	} else {
		$INPUTS['SELECTIONWORDS'] = array('type'=>'textarea','name'=>'selection_words','cols'=>'50','rows'=>'5','value'=>$selection_words);
	}
	$INPUTS['CORRECT'] = array('type'=>'text','name'=>'correct','value'=>$correct,'size'=>'50');
	$INPUTS['OPTION1'] = array('type'=>'text','name'=>'option1','value'=>$option1,'size'=>'50');
//	$INPUTS['OPTION2'] = array('type'=>'text','name'=>'option2','value'=>$option2,'size'=>'50');	// del hasegawa 2016/10/26 入力フォームサイズ指定項目追加
	// ----- add satrt hasegawa 入力フォームサイズ指定項目追加
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
	// ----- add end hasegawa 入力フォームサイズ指定項目追加

	$INPUTS['OPTION3'] = array('type'=>'text','name'=>'option3','value'=>$option3,'size'=>'50');
	// add start karasawa 2019/07/19 BUD英語解析開発
	// upd start hirose 2020/09/21 テスト標準化開発
	// if($form_type == 3 || $form_type == 4 || $form_type == 10){	
	if($form_type == 3 || $form_type == 4 || $form_type == 10 || $form_type == 14){	
	// upd end hirose 2020/09/21 テスト標準化開発
		if ($option3 == "") { $option3 = "0"; }
		$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
		$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
		$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);	
	}
	// add end karasawa 2019/07/19
	$INPUTS['OPTION4'] = array('type'=>'text','name'=>'option4','value'=>$option4,'size'=>'50');
	$INPUTS['OPTION5'] = array('type'=>'text','name'=>'option5','value'=>$option5,'size'=>'50');
	$INPUTS['STANDARDTIME'] 	= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$standard_time);
	$INPUTS['CONTROLUNITID'] 	= array('type'=>'text','name'=>'control_unit_id','size'=>'50','value'=>$control_unit_id);	//出題単元ID
	$INPUTS['CONTROLUNITIDATT'] 	= array('result'=>'plane','value'=>$control_unit_id_att);
	$INPUTS['BOOKUNITID'] 	= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$book_unit_id);	//採点単元ID
	$INPUTS['BOOKUNITIDATT'] 	= array('result'=>'plane','value'=>$book_unit_id_att);
	$INPUTS['LMSUNITID'] 	= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$lms_unit_id);			//LMSユニット
	$INPUTS['LMSUNITATT'] 	= array('result'=>'plane','value'=>$lms_unit_att);


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

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file("$inc_file");
	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"problem_form\">\n";
	$html .= $mode_msg;
	$html .= "<input type=\"hidden\" name=\"action\" value=\"$action\">\n";
	$html .= "<input type=\"hidden\" name=\"form_type\" value=\"".$form_type."\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$problem_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$problem_table_type."\">\n";
	$html .= $drawing_html;			// add hasegawa 2016/06/02 作図ツール
	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"$button\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";

	$html .= "<br/>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$problem_table_type."','".$problem_num."')\">\n";
	//新規登録時は確認ボタンを出さない
	if ($problem_num) {
		$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$problem_table_type."','".$problem_num."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\">\n";
	}
	// upd end hirose 2020/10/01 テスト標準化開発
	$html .= "</form>\n";


	return $html;

}

/**
 * 問題登録 テスト専用問題新規登録 登録確認フォーム
 *   (mt_delete時も、このルーチンが呼ばれる)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string 問題登録HTML
 */
function mt_test_add_check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;	// add hasegawa 2016/06/02 作図ツール $L_DRAWING_TYPE追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発
	$_POST['standard_time'] = mb_convert_kana($_POST['standard_time'],"as", "UTF-8");
	$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
	$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
	$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "mt_add") {
					$val = "mt_problem_add";
				} elseif (MODE == "mt_view") {
					$val = "mt_change";
				}
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	$problem_table_type = $_POST['problem_table_type'];

	if ($_POST['problem_num']) {
		$problem_num = $_POST['problem_num'];
	}

	if($_SESSION['sub_session']['s_class_id']){
		$class_id = $_SESSION['sub_session']['s_class_id'];
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }

		//入力データを変数に格納
		$form_type  = $_SESSION['UPDATE']['form_type'];
		$question = $_SESSION['UPDATE']['question'];
		$problem = $_SESSION['UPDATE']['problem'];
		$voice_data  = $_SESSION['UPDATE']['voice_data'];
		$hint = $_SESSION['UPDATE']['hint'];
		$explanation = $_SESSION['UPDATE']['explanation'];
		$first_problem = $_SESSION['UPDATE']['first_problem'];
		$latter_problem = $_SESSION['UPDATE']['latter_problem'];
		$selection_words = $_SESSION['UPDATE']['selection_words'];
		$correct = $_SESSION['UPDATE']['correct'];
		$option1 = $_SESSION['UPDATE']['option1'];
		$option2 = $_SESSION['UPDATE']['option2'];
		$option3 = $_SESSION['UPDATE']['option3'];
		$option4 = $_SESSION['UPDATE']['option4'];
		$option5 = $_SESSION['UPDATE']['option5'];
		$standard_time = $_SESSION['UPDATE']['standard_time'];
		$control_unit_id = $_SESSION['UPDATE']['control_unit_id'];
		$book_unit_id = $_SESSION['UPDATE']['book_unit_id'];
		$lms_unit_id = $_SESSION['UPDATE']['lms_unit_id'];

	} else {	//削除時の処理(ACTIONがない時)

		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"mt_delete\">\n";

		if ($problem_table_type == "2") {
			$sql = "SELECT *".
					" FROM ".T_MS_TEST_PROBLEM." mtp".
					" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num = '".$problem_num."' AND butp.mk_flg = '0'".
					" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.book_unit_id = butp.book_unit_id AND butp.mk_flg = '0'".
					" WHERE mtp.problem_num = '".$problem_num."'".
					"   AND mtp.mk_flg = '0' ".
					" LIMIT 1;";
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

		$book_unit_id = get_book_unit($problem_table_type, $problem_num, $class_id);

		$control_unit_id = get_control_unit_id($problem_table_type, $problem_num, $class_id);

		$lms_unit_id = get_lms_book_unit($problem_table_type, $problem_num);

		$HIDDEN .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"control_unit_id\" value=\"".$control_unit_id."\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"lms_unit_id\" value=\"".$lms_unit_id."\">\n";
	}

	if (MODE != "mt_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	if (!$form_type) {
		$html = "";

	} else {
		if($problem_table_type == 2 || $problem_table_type == 0){
			$table_file = "math_test_problem_form_type_".$form_type.".htm";
		}

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file($table_file);

		//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
		$INPUTS['PROBLEMNUM'] 	= array('result'=>'plane','value'=>$problem_num);
		if (MODE != "mt_delete") {
			$INPUTS['PROBLEMNUM'] 	= array('result'=>'plane','value'=>"---");
		}

		// add start hasegawa 2016/06/02 作図ツール
		if($_SESSION['sub_session']['add_type']['form_type'] ==13) {
			$drawing_type = $option1;
			$INPUTS[DRAWINGTYPE] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
		}
		// add end hasegawa 2016/06/02


		$INPUTS['PROBLEMNUM'] 		= array('result'=>'plane','value'=>$problem_num);
		$INPUTS['FORMTYPE'] 		= array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);
		$INPUTS['QUESTION'] 		= array('result'=>'plane','value'=>nl2br($question));
		$INPUTS['PROBLEM'] 			= array('result'=>'plane','value'=>nl2br($problem));
		$INPUTS['VOICEDATA'] 		= array('result'=>'plane','value'=>$voice_data);
		$INPUTS['HINT'] 			= array('result'=>'plane','value'=>nl2br($hint));
		$INPUTS['EXPLANATION']		= array('result'=>'plane','value'=>nl2br($explanation));
		$INPUTS['PARAMETER'] 		= array('result'=>'plane','value'=>$parameter);
		$INPUTS['FIRSTPROBLEM'] 	= array('result'=>'plane','value'=>nl2br($first_problem));
		$INPUTS['LATTERPROBLEM']	= array('result'=>'plane','value'=>nl2br($latter_problem));
		$INPUTS['SELECTIONWORDS']	= array('result'=>'plane','value'=>$selection_words);
		$INPUTS['CORRECT']			= array('result'=>'plane','value'=>$correct);
		$INPUTS['OPTION1']			= array('result'=>'plane','value'=>$option1);
//		$INPUTS['OPTION2']			= array('result'=>'plane','value'=>$option2);	// del start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		// ---- add start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		// form_type3 選択時はoption2の項目を解答欄行数・解答欄サイズにする
		if($form_type == 3 ) {
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
		$INPUTS['OPTION3'] 			= array('result'=>'plane','value'=>$option3);
		$INPUTS['OPTION4'] 			= array('result'=>'plane','value'=>$option4);
		$INPUTS['OPTION5']			= array('result'=>'plane','value'=>$option5);
		$INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$standard_time);
		$INPUTS['CONTROLUNITID'] 	= array('result'=>'plane','value'=>$control_unit_id);
		$INPUTS['BOOKUNITID']		= array('result'=>'plane','value'=>$book_unit_id);
		$INPUTS['LMSUNITID'] 		= array('result'=>'plane','value'=>$lms_unit_id);
		// add start karasawa 2019/07/23 BUD英語解析開発
		// upd start hirose 2020/09/21 テスト標準化開発
		// if($form_type == 3 || $form_type == 4 || $form_type == 10){
		if($form_type == 3 || $form_type == 4 || $form_type == 10 || $form_type == 14){
		// upd end hirose 2020/09/21 テスト標準化開発
			$INPUTS['OPTION3'] = array('result'=>'plane','value'=>$BUD_SELECT_LIST[$option3]);
		}
		// add end karasawa 2019/07/23

		$make_html->set_rep_cmd($INPUTS);

		$html .= "<br>";
		$html .= $make_html->replace();
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
		$html .= $HIDDEN;
		$html .= "<input type=\"submit\" value=\"".$button."\">\n";
		$html .= "</form>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$problem_table_type."\">\n";
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

//	$html .= "<br/>\n";
//	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
//	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
//	$html .= "<input type=\"submit\" value=\"登録済み問題リストへ戻る\">\n";
//	$html .= "</form>\n";

	return $html;
}

/**
 * 問題登録 テスト専用問題新規登録時 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーメッセージ一覧
 */

function mt_test_add_check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$DATA_SET['__info']['call_mode'] = 0;		// 0=入力フォーム、1:csv入力
	$DATA_SET['__info']['line_num'] = 0;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
	$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
	$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

	$DATA_SET['data']['form_type'] = $_POST['form_type'];
	$DATA_SET['data']['question'] = trim($_POST['question']);
	$DATA_SET['data']['problem'] = trim($_POST['problem']);
	$DATA_SET['data']['voice_data'] = trim($_POST['voice_data']);
	$DATA_SET['data']['hint'] = trim($_POST['hint']);
	$DATA_SET['data']['explanation'] = trim($_POST['explanation']);
	$DATA_SET['data']['first_problem'] = trim($_POST['first_problem']);
	$DATA_SET['data']['latter_problem'] = trim($_POST['latter_problem']);
	$DATA_SET['data']['selection_words'] = trim($_POST['selection_words']);
	$DATA_SET['data']['correct'] = trim($_POST['correct']);
	$DATA_SET['data']['option1'] = $_POST['option1'];
	$DATA_SET['data']['option2'] = $_POST['option2'];
	// form_type3の場合はoption2のデータを形成		// add start hasegawa 2016/10/26 入力フォームサイズ指定項目追加
	if($_POST['form_type'] == 3 && !$_POST['option2']) {
		$DATA_SET['data']['option2'] = make_option2($_POST['input_row'],$_POST['input_size']);
	}
	$DATA_SET['data']['option3'] = $_POST['option3'];
	$DATA_SET['data']['option4'] = $_POST['option4'];
	$DATA_SET['data']['option5'] = $_POST['option5'];
	$DATA_SET['data']['standard_time'] = $_POST['standard_time'];
	$DATA_SET['data']['control_unit_id'] = $_POST['control_unit_id'];
	$DATA_SET['data']['book_unit_id'] = $_POST['book_unit_id'];
	$DATA_SET['data']['lms_unit_id'] = $_POST['lms_unit_id'];

	list($DATA_SET, $ERROR) = mt_test_check($DATA_SET, $ERROR);

	//入力データをセッション情報に格納
	$_SESSION['UPDATE']['form_type']  = $DATA_SET['data']['form_type'];
	$_SESSION['UPDATE']['question'] = $DATA_SET['data']['question'];
	$_SESSION['UPDATE']['problem'] = $DATA_SET['data']['problem'];
	$_SESSION['UPDATE']['voice_data']  = $DATA_SET['data']['voice_data'];
	$_SESSION['UPDATE']['hint'] = $DATA_SET['data']['hint'];
	$_SESSION['UPDATE']['explanation'] = $DATA_SET['data']['explanation'];
	$_SESSION['UPDATE']['first_problem'] = $DATA_SET['data']['first_problem'];
	$_SESSION['UPDATE']['latter_problem'] = $DATA_SET['data']['latter_problem'];
	$_SESSION['UPDATE']['selection_words'] = $DATA_SET['data']['selection_words'];
	$_SESSION['UPDATE']['correct'] = $DATA_SET['data']['correct'];
	$_SESSION['UPDATE']['option1'] = $DATA_SET['data']['option1'];
	$_SESSION['UPDATE']['option2'] = $DATA_SET['data']['option2'];
	$_SESSION['UPDATE']['option3'] = $DATA_SET['data']['option3'];
	$_SESSION['UPDATE']['option4'] = $DATA_SET['data']['option4'];
	$_SESSION['UPDATE']['option5'] = $DATA_SET['data']['option5'];
	$_SESSION['UPDATE']['standard_time'] = $DATA_SET['data']['standard_time'];
	$_SESSION['UPDATE']['book_unit_id'] = $DATA_SET['data']['book_unit_id'];
	$_SESSION['UPDATE']['control_unit_id'] = $DATA_SET['data']['control_unit_id'];
	$_SESSION['UPDATE']['lms_unit_id'] = $DATA_SET['data']['lms_unit_id'];

	return $ERROR;
}




/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーメッセージ一覧
 */
function add() {

	foreach($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "problem_num") { continue; }
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
 * 問題登録 テスト専用問題新規登録 DB登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA データ登録内容
 * @return array エラーメッセージ一覧
 */
function mt_test_add_add($CHECK_DATA) {			//入力フォーム[追加]→確認画面で[登録]押下時

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//csvの場合
	if ($CHECK_DATA) {
		if($CHECK_DATA['problem_num']) {
			$problem_num = $CHECK_DATA['problem_num'];
		}
		$class_id = $CHECK_DATA['class_id'];
		$problem_table_type = $CHECK_DATA['problem_table_type'];

		$form_type = $CHECK_DATA['form_type'];
		$question = $CHECK_DATA['question'];
		$problem = $CHECK_DATA['problem'];
		$voice_data = $CHECK_DATA['voice_data'];
		$hint = $CHECK_DATA['hint'];
		$parameter = $CHECK_DATA['parameter'];
		$explanation = $CHECK_DATA['explanation'];
		$first_problem = $CHECK_DATA['first_problem'];
		$latter_problem = $CHECK_DATA['latter_problem'];
		$selection_words = $CHECK_DATA['selection_words'];
		$correct = $CHECK_DATA['correct'];
		$option1 = $CHECK_DATA['option1'];
		$option2 = $CHECK_DATA['option2'];
		$option3 = $CHECK_DATA['option3'];
		$option4 = $CHECK_DATA['option4'];
		$option5 = $CHECK_DATA['option5'];
		$standard_time = $CHECK_DATA['standard_time'];
		$control_unit_id = $CHECK_DATA['control_unit_id'];
		$book_unit_id = $CHECK_DATA['book_unit_id'];
		$lms_unit_id = $CHECK_DATA['lms_unit_id'];
		$disp_sort = $CHECK_DATA['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

	} elseif ($_POST){

		if($_POST['problem_table_type'] == 0){
			$problem_table_type = 2;
		}else{
			$problem_table_type = $_POST['problem_table_type'];
		}

		$class_id = $_SESSION['sub_session']['s_class_id'];
		$form_type = $_POST['form_type'];
		$question = $_POST['question'];
		$problem = $_POST['problem'];
		$voice_data = $_POST['voice_data'];
		$hint = $_POST['hint'];
		$parameter = $_POST['parameter'];
		$explanation = $_POST['explanation'];
		$first_problem = $_POST['first_problem'];
		$latter_problem = $_POST['latter_problem'];
		$selection_words = $_POST['selection_words'];
		$correct = $_POST['correct'];
		$option1 = $_POST['option1'];
		$option2 = $_POST['option2'];
		// form_type3の場合はoption2のデータを形成	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($form_type == 3 && !$option2) {
			$option2 = make_option2($_POST['input_row'],$_POST['input_size']);
		}
		$option3 = $_POST['option3'];
		$option4 = $_POST['option4'];
		$option5 = $_POST['option5'];
 		$_POST['standard_time'] = mb_convert_kana($_POST['standard_time'],"as", "UTF-8");
		$standard_time = $_POST['standard_time'];
		$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
		$control_unit_id = $_POST['control_unit_id'];
		$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
		$book_unit_id =  $_POST['book_unit_id'];
		$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
		$lms_unit_id = $_POST['lms_unit_id'];
		$disp_sort = $_POST['disp_sort'];// add hirose 2020/09/17 テスト標準化開発
	}

	if ($problem_num =="" || $problem_num == 0){
		$sql = "SELECT MAX(problem_num) AS max_num ".
				" FROM ".T_MS_TEST_PROBLEM;
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['max_num']) {
			$add_problem_num = $list['max_num'] + 1;
		} else {
			$add_problem_num = 1;
		}
	} else {
		$add_problem_num = $problem_num;
	}

	//ms_test_problem へ問題登録
	$INSERT_DATA = array();

	//画像名変換、フォルダ作成
	//音声名変換、フォルダ作成
	list($question,$ERROR)	= img_convert($question,$add_problem_num);
	list($question,$ERROR)	= voice_convert($question,$add_problem_num);

	list($problem,$ERROR)	= img_convert($problem,$add_problem_num);
	list($problem,$ERROR)	= voice_convert($problem,$add_problem_num);

	list($hint,$ERROR)			= img_convert($hint,$add_problem_num);
	list($hint,$ERROR)			= voice_convert($hint,$add_problem_num);

	list($explanation,$ERROR)	= img_convert($explanation,$add_problem_num);
	list($explanation,$ERROR)	= voice_convert($explanation,$add_problem_num);

	//form_type10,11のみselection_wordsの変換
	if ($INSERT_DATA['form_type'] == 10 || $INSERT_DATA['form_type'] == 11) {
		list($selection_words,$ERROR) 	= img_convert($selection_words,$add_problem_num);
		list($selection_words,$ERROR) 	= voice_convert($selection_words,$add_problem_num);
	}

	list($voice_data, $ERROR_SUB) = math_copy_default_voice_data($voice_data,$add_problem_num);

	$INSERT_DATA['problem_type']		 	= 0;

	$INSERT_DATA['problem_num']				= $add_problem_num;
	// upd start hirose 2020/09/21 テスト標準化開発
	// $INSERT_DATA['course_num']				= 3;
	$INSERT_DATA['course_num']				= $_SESSION['t_practice']['course_num'];
	// upd end hirose 2020/09/21 テスト標準化開発
	$INSERT_DATA['standard_time']			= $standard_time;
	$INSERT_DATA['form_type']				= $form_type;
	$INSERT_DATA['question'] 				= $question;
	$INSERT_DATA['problem']					= $problem;
	$INSERT_DATA['voice_data']				= $voice_data;
	$INSERT_DATA['hint']					= $hint;
	$INSERT_DATA['parameter']				= $parameter;
	$INSERT_DATA['explanation']				= $explanation;
	$INSERT_DATA['first_problem']			= $first_problem;
	$INSERT_DATA['latter_problem']			= $latter_problem;
	$INSERT_DATA['selection_words']			= $selection_words;
	$INSERT_DATA['correct'] 				= $correct;			//正解
	$INSERT_DATA['option1'] 				= $option1;			//option1
	$INSERT_DATA['option2'] 				= $option2;			//option2
	$INSERT_DATA['option3'] 				= $option3;			//option3
	$INSERT_DATA['option4'] 				= $option4;			//option4
	$INSERT_DATA['option5'] 				= $option5;

	$INSERT_DATA['ins_syr_id']				= "addline";
	$INSERT_DATA['ins_tts_id'] 				= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 				= "now()";
	$INSERT_DATA['upd_syr_id']				= "addline";
	$INSERT_DATA['upd_tts_id'] 				= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 				= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_PROBLEM, $INSERT_DATA);

	if (!$ERROR){

		$_SESSION['sub_session']['surala_regist']['status'] = "3";
		$_SESSION['sub_session']['surala_regist']['problem_num'] = $add_problem_num;

		// 採点単元情報登録
		if ($book_unit_id) {
			mt_book_unit_test_problem_add($add_problem_num, $problem_table_type, $book_unit_id, $class_id, $ERROR);
		}

		// 出題単元情報登録
		if ($control_unit_id){
			// upd start hirose 2020/09/17 テスト標準化開発
			// math_test_control_problem_add($add_problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR);
			math_test_control_problem_add($add_problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR,$disp_sort);
			// upd end hirose 2020/09/17 テスト標準化開発
		}

		// LMS単元情報登録
		mt_lms_unit_test_problem_add($add_problem_num, $problem_table_type, $lms_unit_id, $ERROR);
	}

	return $ERROR;
}

/**
 * 登録済みデータの修正（更新）処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA データ登録内容
 * @return array エラーメッセージ一覧
 */
function mt_change($CHECK_DATA) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//csvの場合
	if ($CHECK_DATA){

		$class_id = $CHECK_DATA['class_id'];
		$problem_table_type = $CHECK_DATA['problem_table_type'];
		$problem_num = $CHECK_DATA['problem_num'];

		$form_type = $CHECK_DATA['form_type'];
		$question = $CHECK_DATA['question'];
		$problem = $CHECK_DATA['problem'];
		$voice_data = $CHECK_DATA['voice_data'];
		$hint = $CHECK_DATA['hint'];
		$parameter = $CHECK_DATA['parameter'];
		$explanation = $CHECK_DATA['explanation'];
		$first_problem = $CHECK_DATA['first_problem'];
		$latter_problem = $CHECK_DATA['latter_problem'];
		$selection_words = $CHECK_DATA['selection_words'];
		$correct = $CHECK_DATA['correct'];
		$option1 = $CHECK_DATA['option1'];
		$option2 = $CHECK_DATA['option2'];
		$option3 = $CHECK_DATA['option3'];
		$option4 = $CHECK_DATA['option4'];
		$option5 = $CHECK_DATA['option5'];
		$standard_time = $CHECK_DATA['standard_time'];
		$control_unit_id = $CHECK_DATA['control_unit_id'];
		$book_unit_id = $CHECK_DATA['book_unit_id'];
		$lms_unit_id = $CHECK_DATA['lms_unit_id'];
		$disp_sort = $CHECK_DATA['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

	} elseif ($_POST) {

		$class_id = $_SESSION['sub_session']['s_class_id'];
		$problem_table_type = $_POST['problem_table_type'];
		$problem_num = $_POST['problem_num'];

		$form_type = $_POST['form_type'];
		$question = $_POST['question'];
		$problem = $_POST['problem'];
		$voice_data = $_POST['voice_data'];
		$hint = $_POST['hint'];
		$parameter = $_POST['parameter'];
		$explanation = $_POST['explanation'];
		$first_problem = $_POST['first_problem'];
		$latter_problem = $_POST['latter_problem'];
		$selection_words = $_POST['selection_words'];
		$correct = $_POST['correct'];
		$option1 = $_POST['option1'];
		$option2 = $_POST['option2'];
		// form_type3の場合はoption2のデータを形成	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($form_type == 3 && !$option2) {
			$option2 = make_option2($_POST['input_row'],$_POST['input_size']);
		}
		$option3 = $_POST['option3'];
		$option4 = $_POST['option4'];
		$option5 = $_POST['option5'];
		$standard_time = $_POST['standard_time'];
		$control_unit_id = $_POST['control_unit_id'];
		$book_unit_id =  $_POST['book_unit_id'];
		$lms_unit_id = $_POST['lms_unit_id'];
		$disp_sort = $_POST['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

	}

	if ($problem_num) {
		$INSERT_DATA = array();

		if (MODE == "mt_view" || $CHECK_DATA ) {

			//すらら問題の場合
			// upd start hirose 2020/09/21 テスト標準化開発
			//修正フォームから修正したとき、ここに入らない
			// if ($CHECK_DATA['problem_type'] == "surala") {
			if ($CHECK_DATA['problem_type'] == "surala" || $problem_table_type == 1) {
			// upd end hirose 2020/09/21 テスト標準化開発

				$sql = "SELECT block_num FROM problem WHERE problem_num = '".$problem_num."' LIMIT 1 ;";
				if($result = $cdb->query($sql)){
					$list = $cdb->fetch_assoc($result);
					$block_num = $list['block_num'];
				}
				$ERROR = mt_problem_attribute_add($block_num, $standard_time);

			} else {//テスト問題の場合

				//ms_test_problemテーブルを更新する
				$INSERT_DATA['problem_type']		= 0;
				// upd start hirose 2020/09/21 テスト標準化開発
				// $INSERT_DATA['course_num']			= 3;
				$INSERT_DATA['course_num']			= $_SESSION['t_practice']['course_num'];
				// upd end hirose 2020/09/21 テスト標準化開発
				$INSERT_DATA['standard_time']		= $standard_time;
				$INSERT_DATA['form_type']			= $form_type;
				$INSERT_DATA['question'] 			= $question;
				$INSERT_DATA['problem']				= $problem;
				$INSERT_DATA['voice_data']			= $voice_data;
				$INSERT_DATA['hint']				= $hint;
				$INSERT_DATA['parameter']			= $parameter;
				$INSERT_DATA['explanation']			= $explanation;
				$INSERT_DATA['first_problem']		= $first_problem;
				$INSERT_DATA['latter_problem']		= $latter_problem;
				$INSERT_DATA['selection_words']		= $selection_words;
				$INSERT_DATA['correct'] 			= $correct;			//正解
				$INSERT_DATA['option1'] 			= $option1;			//option1
				$INSERT_DATA['option2'] 			= $option2;			//option2
				$INSERT_DATA['option3'] 			= $option3;			//option3
				$INSERT_DATA['option4'] 			= $option4;			//option4
				$INSERT_DATA['option5'] 			= $option5;

				$INSERT_DATA['upd_syr_id'] = "updateline";
				$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 	= "now()";

				$where = " WHERE problem_num='".$problem_num."';";
				$ERROR = $cdb->update(T_MS_TEST_PROBLEM, $INSERT_DATA, $where);
			}

			if(!$ERROR){

				// 採点単元修正
				if ($book_unit_id) {
					mt_book_unit_test_problem_add($problem_num, $problem_table_type, $book_unit_id, $class_id, $ERROR);
				}

				// 出題単元修正
				if ($control_unit_id) {
					// upd start hirose 2020/09/17 テスト標準化開発
					// math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR);
					math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR,$disp_sort);
					// upd end hirose 2020/09/17 テスト標準化開発
				}

				// LMS単元修正
				mt_lms_unit_test_problem_add($problem_num, $problem_table_type, $lms_unit_id, $ERROR);
			} else {
				return $ERROR;
			}
			unset($_SESSION['UPDATE']);

		} elseif (MODE == "mt_delete") {	//削除処理

			// 採点単元削除
			if ($book_unit_id) {

				$book_unit_id_work = str_replace("::", ",", $book_unit_id);

				$INSERT_DATA = array();
				$INSERT_DATA['mk_flg'] 		= 1;
				$INSERT_DATA['mk_tts_id'] 	= $_SESSION['myid']['id'];
				$INSERT_DATA['mk_date'] 	= "now()";

				$where = " WHERE problem_num='".$problem_num."'".
						 	" AND book_unit_id IN (".$book_unit_id_work.");";

				$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM, $INSERT_DATA, $where);

				if ($ERROR) { return $ERROR; }
			}

			// 出題単元削除
			if ($control_unit_id) {

				$INSERT_DATA = array();
				$INSERT_DATA['mk_flg'] 		= 1;
				$INSERT_DATA['mk_tts_id'] 	= $_SESSION['myid']['id'];
				$INSERT_DATA['mk_date'] 	= "now()";

				$where = " WHERE problem_num='".$problem_num."'".
						 "   AND class_id ='".$class_id."';";

				$ERROR = $cdb->update(T_MATH_TEST_CONTROL_PROBLEM, $INSERT_DATA, $where);
			}

			// LMS単元削除
			if ($lms_unit_id) {

				$INSERT_DATA = array();
				$INSERT_DATA['mk_flg'] 		= 1;
				$INSERT_DATA['mk_tts_id'] 	= $_SESSION['myid']['id'];
				$INSERT_DATA['mk_date'] 	= "now()";

				$where = " WHERE problem_num='".$problem_num."'".
						 ";";

				$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT, $INSERT_DATA, $where);

			}

		}
		unset($INSERT_DATA);

	} else {
		$ERROR[] = "問題管理番号を確認できません。";
	}

	if ($ERROR) { return $ERROR; }

	return $ERROR;
}

/**
 * 出題単元の取り出し
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param integer $problem_num 問題番号
 * @param integer $class_id 級ID
 * @return string 出題単元ID
 */
function get_control_unit_id($problem_table_type, $problem_num, $class_id){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//control_unit_id の取り出し
	$sql  = "SELECT mtcp.control_unit_id FROM " .T_MATH_TEST_CONTROL_PROBLEM ." mtcp".
			" INNER JOIN ". T_MATH_TEST_CONTROL_UNIT ." mtcu ON mtcp.control_unit_id = mtcu.control_unit_id AND mtcp.class_id = mtcu.class_id AND mtcu.mk_flg = '0'".
			" WHERE mtcp.mk_flg = '0'".
			"   AND mtcp.problem_num = '".$problem_num."'".
			"   AND mtcp.problem_table_type = '".$problem_table_type."'".
			"   AND mtcp.class_id = '".$class_id."'".
			" ORDER BY mtcu.control_unit_id".
			";";
//echo $sql;
	// 変数クリア
	$L_CONTROL_UNIT = array();

	$control_unit_id = "";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			if ($control_unit_id) { $control_unit_id .= "::"; }		// 出題単元は、複数紐づかない
			$control_unit_id .= $list['control_unit_id'];
		}
	}

	return $control_unit_id;
}

/**
 * 採点単元の取り出し
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param integer $problem_num 問題番号
 * @param integer $class_id 級ID
 * @return string 採点単元ID(::区切り)
 */
function get_book_unit($problem_table_type, $problem_num, $class_id){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT butp.* " .
			" FROM " .T_BOOK_UNIT_TEST_PROBLEM . " butp".
			" INNER JOIN " .T_MATH_TEST_BOOK_UNIT_LIST. " mtbul ON butp.book_unit_id = mtbul.book_unit_id AND mtbul.class_id = '".$class_id."' AND mtbul.mk_flg = 0 ".
			" WHERE butp.mk_flg = 0".
			"   AND butp.default_test_num = '0'".
			"   AND butp.problem_num = '".$problem_num."'".
			" ORDER BY butp.book_unit_id".
			";";
	$book_unit_id = "";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			if ($book_unit_id) { $book_unit_id .= "::"; }
			$book_unit_id .=  $list['book_unit_id'];
		}
	}

	return $book_unit_id;
}

/**
 * LMS単元の取り出し
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param integer $problem_num 問題番号
 * @return string LMS単元ID(::区切り)
 */
function get_lms_book_unit($problem_table_type, $problem_num){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT bulu.* " .
			" FROM " .T_BOOK_UNIT_LMS_UNIT . " bulu ".
			" WHERE bulu.mk_flg='0'".
			"   AND bulu.problem_table_type='".$problem_table_type."'".
			"   AND bulu.problem_num='".$problem_num."'".
			" ORDER BY bulu.book_unit_id,bulu.unit_num;";
//echo('$sql=>'.$sql."<br />\n");
	$lms_unit_id = "";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			if ($lms_unit_id) { $lms_unit_id .= "::"; }
			$lms_unit_id .= $list['unit_num'];
		}
	}

//echo('$lms_unit_id=>'.$lms_unit_id."<br />\n");
	return $lms_unit_id;
}

/**
 * 問題登録 LMS単元＿テスト問題登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_num 問題番号
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param string $book_unit_id 採点単元（::区切り）
 * @param integer $class_id 級ID
 * @param array $ERROR エラーメッセージ一覧（参照渡し）
 * @return array エラーメッセージ一覧
 */
function mt_book_unit_test_problem_add($problem_num, $problem_table_type, $book_unit_id, $class_id, &$ERROR) {

//$lms_unit_id 入力されたlms_unit_idの配列
//echo("===mt_book_unit_test_problem_add_strat========================<br />\n");
//echo('$book_unit_id=>');pre($book_unit_id);

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) { return; }

	// 修正前の採点単元を取得
	$sql   = "SELECT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM. " butp".
			"  INNER JOIN ".T_MS_BOOK_UNIT. " mbu ON butp.book_unit_id = mbu.book_unit_id ".
			"  INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST. " mtbul ON mtbul.book_unit_id = butp.book_unit_id ".
			"  WHERE butp.problem_table_type='".$problem_table_type."'".
			"    AND butp.problem_num='".$problem_num."'".
			"    AND mtbul.class_id='".$class_id."'";

//echo('$sql1=>');pre($sql);
	$L_BOOK_UNIT_TEST_PROBLEM = array();
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_BOOK_UNIT_TEST_PROBLEM[$list['book_unit_id']] = $list['book_unit_id'];
		}
	}
//echo('$L_BOOK_UNIT_TEST_PROBLEM1=>');pre($L_BOOK_UNIT_TEST_PROBLEM);

	// book_unit_idを配列に格納
	$book_unit_id_list = explode("::", $book_unit_id);

	if ($book_unit_id_list) {
		if (!is_array($book_unit_id_list)) {
			$BOOK_UNIT[0] = $book_unit_id;
		}else{
			$BOOK_UNIT = $book_unit_id_list;
		}

		foreach ($BOOK_UNIT as $book_unit_id_val){

			unset($INSERT_DATA);
			if (!$book_unit_id_val) { continue; }

			//登録されていればアップデート
			if ($L_BOOK_UNIT_TEST_PROBLEM[$book_unit_id_val]) {

				// 削除フラグをクリア
				$INSERT_DATA['mk_flg'] 			= 0;
				$INSERT_DATA['mk_tts_id'] 		= "";
				$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 		= "now()";

				$where = " WHERE  problem_table_type='".$problem_table_type."'".
	  							" AND problem_num='".$problem_num."'".
	  							" AND book_unit_id  = '".$book_unit_id_val."'";
				//echo('$where2=>');pre($where);
				$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA,$where);
				if ($ERROR) { return $ERROR; }
				//更新レコード分の値を削除
				unset($L_BOOK_UNIT_TEST_PROBLEM[$book_unit_id_val]);
			//登録されていなければインサート
			} else {
				$INSERT_DATA['book_unit_id'] 		= $book_unit_id_val;
				$INSERT_DATA['problem_num'] 		= $problem_num;
				$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
				$INSERT_DATA['default_test_num'] 	= 0;
				$INSERT_DATA['mk_flg'] 	= 0;
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";
				//echo('$INSERT_DATA=>');pre($INSERT_DATA);
				$ERROR = $cdb->insert(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

//echo('$L_BOOK_UNIT_TEST_PROBLEM2=>');pre($L_BOOK_UNIT_TEST_PROBLEM);
	//更新されていないものは削除
	if (is_array($L_BOOK_UNIT_TEST_PROBLEM)) {
		unset($INSERT_DATA);
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";
		foreach($L_BOOK_UNIT_TEST_PROBLEM as $book_unit_id_val) {
			//登録されている物で更新されていないレコードを削除
			$where = " WHERE  problem_table_type='".$problem_table_type."'".
						" AND problem_num='".$problem_num."'".
						" AND book_unit_id  =  ".$book_unit_id_val." ";
//echo('$where=>');pre($where);
			$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA,$where);
			if ($ERROR) { return $ERROR; }
		}
	}


	return $ERROR;
}



/**
 * 問題登録　出題単元登録・更新
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_num 問題番号
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param string $control_unit_id 出題単元（::区切り）
 * @param integer $class_id 級ID
 * @param array $ERROR エラーメッセージ一覧（参照渡し）
 * @return array エラーメッセージ一覧
 */
// upd start hirose 2020/09/17 テスト標準化開発
// function math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, &$ERROR) {
function math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, &$ERROR,$disp_sort=NULL) {
// upd end hirose 2020/09/17 テスト標準化開発

//$control_unit_id 入力されたcontrol_idの配列
//echo("===math_test_control_problem_add========================<br />\n");
//echo('$book_unit_id=>');pre($book_unit_id);

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) { return; }

	// 修正前の出題単元を抽出
	$sql =	"SELECT mtcp.control_unit_id ".
			" ,mtcp.mk_flg".// add hirose 2020/09/17 テスト標準化開発
			" FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp".
			" WHERE mtcp.problem_num = '".$problem_num."'".
			"   AND mtcp.class_id = '".$class_id."'".
			//" AND mtcp.mk_flg =0".	// 復活の場合もあるのでここでは無効フラグは見ない。
			";";
//echo('$sql=>');pre($sql);
	$L_CONTROL_UNIT_LIST = array();
	$MK_DATA = [];// add hirose 2020/09/17 テスト標準化開発
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_CONTROL_UNIT_LIST[$list['control_unit_id']] = $list['control_unit_id'];
			$MK_DATA[$list['control_unit_id']] = $list['mk_flg'];// add hirose 2020/09/17 テスト標準化開発
		}
	}

	$CONTROL_UNIT = array();
	$control_unit_id_list = explode("::", $control_unit_id);

	if ($control_unit_id) {
		if (!is_array($control_unit_id_list)) {
			$CONTROL_UNIT[0] = $control_unit_id;
		}else{
			$CONTROL_UNIT = $control_unit_id_list;
		}

//echo('$L_CONTROL_UNIT_LIST2=>');pre($L_CONTROL_UNIT_LIST);
//echo('$CONTROL_UNIT2=>');pre($CONTROL_UNIT);

		foreach ($CONTROL_UNIT as $control_unit_id_val){

			unset($INSERT_DATA);
			if (!$control_unit_id_val) { continue; }

			//登録されていればアップデート
			if ($L_CONTROL_UNIT_LIST[$control_unit_id_val]) {

				// add start hirose 2020/09/17 テスト標準化開発
				if($disp_sort){
					$INSERT_DATA['disp_sort'] = $disp_sort;
				//無効フラグが立っていたデータを復活させるとき、すでにほかでdisp_sortの番号が使われている
				//可能性があるため、最大値を取得
				}elseif($MK_DATA[$control_unit_id_val]){
					$sql =  "SELECT MAX(mtcp.disp_sort) AS max_disp".
						" FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp".
						" WHERE mtcp.mk_flg = 0".
						" AND mtcp.class_id = '".$class_id."'".
						";";
						
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					if ($list['max_disp']) {
						$add_disp_sort = $list['max_disp'] + 1;
					} else {
						$add_disp_sort = 1;
					}
					$INSERT_DATA['disp_sort'] = $add_disp_sort;

				}
				// add end hirose 2020/09/17 テスト標準化開発
				$INSERT_DATA['mk_flg'] 			= 0;
				$INSERT_DATA['mk_tts_id'] 		= "";
				$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 		= "now()";

				$where = " WHERE control_unit_id='".$control_unit_id_val."'".
						"    AND class_id ='".$class_id."'".
						"    AND problem_table_type='".$problem_table_type."'".
						"    AND problem_num='".$problem_num."';";

				$ERROR = $cdb->update(T_MATH_TEST_CONTROL_PROBLEM,$INSERT_DATA,$where);
				if ($ERROR) { return $ERROR; }
				//更新レコード分の値を削除
				unset($L_CONTROL_UNIT_LIST[$control_unit_id_val]);

			//登録されていなければインサート
			} else {
				if(!$disp_sort){// add hirose 2020/09/17 テスト標準化開発
					$sql =  "SELECT MAX(mtcp.disp_sort) AS max_disp".
						" FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp".
						" WHERE mtcp.mk_flg = 0".
						" AND mtcp.class_id = '".$class_id."'".// add hirose 2020/09/17 テスト標準化開発
						";";
						
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					if ($list['max_disp']) {
						$add_disp_sort = $list['max_disp'] + 1;
					} else {
						$add_disp_sort = 1;
					}
				// add start hirose 2020/09/17 テスト標準化開発
				}else{
					$add_disp_sort = $disp_sort;
				}
				// add end hirose 2020/09/17 テスト標準化開発

				$INSERT_DATA['control_unit_id'] 	= $control_unit_id_val;
				$INSERT_DATA['problem_num'] 		= $problem_num;
				$INSERT_DATA['class_id'] 			= $class_id;
				$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
				$INSERT_DATA['disp_sort'] 			= $add_disp_sort;
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";

				$ERROR = $cdb->insert(T_MATH_TEST_CONTROL_PROBLEM,$INSERT_DATA);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

//echo('$L_CONTROL_UNIT_LIST3=>');pre($L_CONTROL_UNIT_LIST);
	//更新されていないものは削除
	if (is_array($L_CONTROL_UNIT_LIST)) {

		unset($INSERT_DATA);
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";

		foreach($L_CONTROL_UNIT_LIST as $val) {
			$where = " WHERE control_unit_id='".$val."'".
						" AND class_id='".$class_id."'".
						" AND problem_table_type='".$problem_table_type."'".
						" AND problem_num='".$problem_num."'".
						" AND mk_flg = 0;";
//echo('$where=>');pre($where);

			$ERROR = $cdb->update(T_MATH_TEST_CONTROL_PROBLEM,$INSERT_DATA,$where);
			if ($ERROR) { return $ERROR; }
		}
	}
}

/**
 * 問題登録　LMS単元＿テスト問題登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $problem_num 問題番号
 * @param integer $problem_table_type 1:既存の問題 2:テスト問題
 * @param string $lms_unit_id LMS単元（::区切り）
 * @param array $ERROR エラーメッセージ一覧（参照渡し）
 * @return array エラーメッセージ一覧
 */
function mt_lms_unit_test_problem_add($problem_num, $problem_table_type, $lms_unit_id, &$ERROR) {

	//$lms_unit_id 入力されたlms_unit_idの配列
	//echo("===mt_lms_unit_test_problem_add========================<br />\n");

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) { return; }

	//紐付けされている単元番号取得
	$sql   = "SELECT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT . " bulu".
	 		" WHERE bulu.problem_table_type='".$problem_table_type."'".
	 		" AND bulu.problem_num='".$problem_num."'".
	 		";";
	//echo $sql;

	if ($result = $cdb->query($sql)) {
		$L_LMS_UNIT_LIST = array();
		while ($list = $cdb->fetch_assoc($result)) {
			$L_LMS_UNIT_LIST[$list['unit_num']] = $list['unit_num'];
		}
	}
//echo('$L_LMS_UNIT_LIST=>');pre($L_LMS_UNIT_LIST);

	$lms_unit_id_list = explode("::", $lms_unit_id);

	if ($lms_unit_id) {
		if (!is_array($lms_unit_id_list)) {
			$LMS_UNIT[0] = $lms_unit_id ;
		} else {
			$LMS_UNIT = $lms_unit_id_list;
		}
//echo('$LMS_UNIT=>');pre($LMS_UNIT);

		foreach ($LMS_UNIT as $lms_unit_val){

			unset($INSERT_DATA);
			if (!$lms_unit_val) { continue; }

			//登録されていればアップデート
			if ($L_LMS_UNIT_LIST[$lms_unit_val]) {

				$INSERT_DATA['mk_flg'] 			= 0;
				$INSERT_DATA['mk_tts_id'] 		= "";
				$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 		= "now()";

				$where = " WHERE unit_num='".$lms_unit_val."'".
							"    AND problem_table_type='".$problem_table_type."'".
							"    AND problem_num='".$problem_num."';";
				//echo('$where1=>');pre($where);
				$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
				if ($ERROR) { return $ERROR; }

				//更新レコード分の値を削除
				unset($L_LMS_UNIT_LIST[$lms_unit_val]);

			//登録されていなければインサート
			} else {

				$INSERT_DATA['unit_num'] 			= $lms_unit_val;
				$INSERT_DATA['problem_num'] 		= $problem_num;
				$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";

				$ERROR = $cdb->insert(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

//echo('$L_LMS_UNIT_LIST=>');pre($L_LMS_UNIT_LIST);

	//登録されている物で更新されていないレコードを削除
	if (is_array($L_LMS_UNIT_LIST)) {

		unset($INSERT_DATA);
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";

		foreach($L_LMS_UNIT_LIST as $val) {
			$where = " WHERE unit_num='".$val."'".
						"    AND problem_table_type='".$problem_table_type."'".
						"    AND problem_num='".$problem_num."';";
//echo('$where=>');pre($where);
			$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
			if ($ERROR) { return $ERROR; }
		}
	}

	return $ERROR;
}

/**
 * エラーチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $DATA_SET 項目一覧
 * @param array $ERROR エラーメッセージ一覧
 * @return array 項目一覧／エラーメッセージ一覧
 */
function mt_test_check($DATA_SET, $ERROR) {

//print_r($DATA_SET);
//echo "<br/><br/>";
	//制御値
	// $DATA_SET['__info']['call_mode']		// 0=入力フォーム、1:csv入力
	// $DATA_SET['__info']['line_num']		// 行番号...エラーメッセージに付記するため
	// $DATA_SET['__info']['check_mode']	// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
	//											0:自動調整しない、1:する
	// $DATA_SET['__info']['store_mode']	// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納するかのスイッチ
	//											0:再格納しない、1:する
	// $DATA_SET['__info']['result']		// チェック結果を返す エラーがあれば1、エラー無しなら0
	//
	//入力データ群 (form_type 含む)
	// $DATA_SET['data']['パラメータ名']

	//入力パラメータ
	// display_problem_num  all must
	// form_type ...フォームタイプ判別
	// question 1,2,3,4,5,8
	// problem  option
	// voice_data  all
	// hint  option
	// explanation  all
	// answer_time  option  -> standard_time を使用
	// first_problem  4,5
	// latter_problem  4,5
	// selection_words  2複数,3複数,4,8,10,11
	// correct  all
	// option1
	// option2
	// option3
	// option4
	// option5
	// display  all

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//制御情報
	$line_num = "";
	if ($DATA_SET['__info']['call_mode'] == "1") {
		$line_num = line_number_format($DATA_SET['__info']['line_num']);	//行番号
	}
	$check_mode = $DATA_SET['__info']['check_mode'];	//データ型を自動調整(1:する)
	$store_mode = $DATA_SET['__info']['store_mode'];	//自動調整結果の再格納(1:する)
	$DATA_SET['__info']['result'] = 0;

	//共通項目のチェック
	$form_type = $DATA_SET['data']['form_type'];	//問題形式番号 1,2,3,4,5,10,11
	if ($check_mode) {
		$form_type = mb_convert_kana($form_type,"asKV", "UTF-8");
		$form_type = trim($form_type);
	}

	if (strlen($form_type) == 0 || preg_match("/[^0-9]/",$form_type)) {
		$ERROR[] = $line_num."出題形式が不正です(数値ではありません)。";
		$DATA_SET['__info']['result'] = 1;
	} else {
		if ($form_type != 1 && $form_type != 2 && $form_type != 3 && $form_type != 4 && $form_type != 5 && $form_type != 10 && $form_type != 11 && $form_type != 13 && $form_type != 14) { // add hasegawa 2016/06/02 作図ツールform_type13 // add hirose 2020/09/18 テスト標準化開発 form_type14追加
			// add start hasegawa 2018/03/23 百マス計算
			if ($form_type == 14) {
				$ERROR[] = $line_num."form_type14(百マス計算)はテストには登録できません。";
			//add start kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			}else if($form_type == 15) {
				$ERROR[] = $line_num."form_type15(書写)はテストには登録できません。";
			//add end   kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			//add start kimura 2018/11/22 すらら英単語 _admin
			}else if($form_type == 16){
				$ERROR[] = $line_num."form_type16(意味)はテストには登録できません。";
			}else if($form_type == 17){
				$ERROR[] = $line_num."form_type17(書く)はテストには登録できません。";
			//add end   kimura 2018/11/22 すらら英単語 _admin
			} else {
			// add end hasegawa 2018/03/23
				$ERROR[] = $line_num."出題形式が不正です。";
			} // add hasegawa 2018/03/23
			$DATA_SET['__info']['result'] = 1;
		}
	}

	//explanation  all
	$explanation = $DATA_SET['data']['explanation'];	//解説文

	$display = $DATA_SET['data']['display'];	//display
	if ($check_mode) {
		$display = mb_convert_kana($display,"asKV", "UTF-8");
		$display = trim($display);
	}

	//voice_data  all
	$voice_data = $DATA_SET['data']['voice_data'];	//音声データ
	if ($check_mode) {
		$voice_data = trim($voice_data);
	}

	//他データの取り出し
	//correct  all
	$correct = $DATA_SET['data']['correct'];	//正解
	if ($check_mode) {
		$correct = trim($correct);
	}

	//answer_time  option  -> standard_time を使用
	$standard_time = $DATA_SET['data']['standard_time'];	//解答時間
	if ($check_mode) {
		$standard_time = mb_convert_kana($standard_time,"asKV", "UTF-8");
		$standard_time = trim($standard_time);
	}
	if ($standard_time) {
		if (preg_match("/[^0-9]/",$standard_time)) {
			$ERROR[] = $line_num."回答目安時間は半角数字で入力してください。";
			$DATA_SET['__info']['result'] = 1;
		}
	}

	//selection_words  2複数,3複数,4,8,10,11
	$selection_words = $DATA_SET['data']['selection_words'];	//selection_words
	if ($check_mode) {
		$selection_words = trim($selection_words);
	}
	//option1
	$option1 = $DATA_SET['data']['option1'];	//option1
	if ($check_mode) {
		$option1 = trim($option1);
	}
	//option2
	$option2 = $DATA_SET['data']['option2'];	//option2
	if ($check_mode) {
		$option2 = trim($option2);
	}
	//option3
	$option3 = $DATA_SET['data']['option3'];	//option3
	if ($check_mode) {
		$option3 = trim($option3);
	}
	//option4
	$option4 = $DATA_SET['data']['option4'];	//option4
	if ($check_mode) {
		$option4 = trim($option4);
	}
	//option5
	$option5 = $DATA_SET['data']['option5'];	//option5
	if ($check_mode) {
		$option5 = trim($option5);
	}
	//first_problem  4,5
	$first_problem = $DATA_SET['data']['first_problem'];
	if ($check_mode) {
		$first_problem = trim($first_problem);
	}
	//latter_problem  4,5
	$latter_problem = $DATA_SET['data']['latter_problem'];
	if ($check_mode) {
		$latter_problem = trim($latter_problem);
	}

	//question  must:1,2,3,4,5,8
	$question = $DATA_SET['data']['question'];
	if ($check_mode) {
		$question = trim($question);
	}
	//problem  option
	$problem = $DATA_SET['data']['problem'];
	if ($check_mode) {
		$problem = trim($problem);
	}
	//hint  option
	$hint = $DATA_SET['data']['hint'];
	if ($check_mode) {
		$hint = trim($hint);
	}

	// 出題単元は、複数指定しない
	//control_unit_id
	$DATA_SET['data']['control_unit_id'] = mb_convert_kana($DATA_SET['data']['control_unit_id'],"as", "UTF-8");
	$control_unit_id = $DATA_SET['data']['control_unit_id'];
	if ($check_mode) {
		$control_unit_id = mb_convert_kana($control_unit_id,"asKV", "UTF-8");
		$control_unit_id = trim($control_unit_id);
	}

	if ($control_unit_id) {

		if (preg_match("/[^0-9]/",$control_unit_id)){
			$ERROR[] = "出題単元（小問）のＩＤが不正です。";
		} else {
			$sql  = "SELECT * FROM ".T_MATH_TEST_CONTROL_UNIT.
					" WHERE class_id = '".$_SESSION['sub_session']['s_class_id']."'".
					"   AND display = '1' ".
					"   AND mk_flg = '0'".
					"   AND control_unit_id = ".$control_unit_id."".
					";";

			if ($result = $cdb->query($sql)) {
				$control_unit_count = $cdb->num_rows($result);
			}

			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($control_unit_count == 0) {
				$ERROR[] = "級に存在しない出題単元（小問）のＩＤを登録しようとしています。";
			}
		}
	}else {
		$ERROR[] = "出題単元（小問）のＩＤが未入力です。";
	}

	//book_unit_id
	$DATA_SET['data']['book_unit_id'] = mb_convert_kana($DATA_SET['data']['book_unit_id'],"as", "UTF-8");
	$book_unit_id = $DATA_SET['data']['book_unit_id'];
	if ($check_mode) {
		$book_unit_id = mb_convert_kana($book_unit_id,"asKV", "UTF-8");
		$book_unit_id = trim($book_unit_id);
	}

	if ($book_unit_id) {
		$L_BOOK_UNIT = explode("::",$book_unit_id);

		foreach($L_BOOK_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "採点単元（結果グラフ単元）のＩＤが不正です。"; }
		}
		$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";

		$sql  = "SELECT * FROM ".T_MS_BOOK_UNIT." mbu".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
				" WHERE mtbul.class_id = '".$_SESSION['sub_session']['s_class_id']."'".
				" AND mbu.display = '1'".
				" AND mbu.mk_flg = '0'".
				" AND mbu.book_unit_id IN(".$in_book_unit_id.");";

		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}
		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_BOOK_UNIT)) {
			$ERROR[] = "同一採点単元（結果グラフ単元）のＩＤ、または級に存在しない採点単元（結果グラフ単元）のＩＤを登録しようとしています。";
		}
	}else {
		$ERROR[] = "採点単元（結果グラフ単元）のＩＤが未入力です。";
	}

	//lms_unit_id
	$DATA_SET['data']['lms_unit_id'] = mb_convert_kana($DATA_SET['data']['lms_unit_id'],"as", "UTF-8");
	$lms_unit_id = $DATA_SET['data']['lms_unit_id'];
	if ($check_mode) {
		$lms_unit_id = mb_convert_kana($lms_unit_id,"asKV", "UTF-8");
		$lms_unit_id = trim($lms_unit_id);
	}

	if ($lms_unit_id) {
		$L_LMS_UNIT = explode("::",$lms_unit_id);

		foreach($L_LMS_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "復習ユニット単元のＩＤが不正です。"; }
		}

		$in_lms_unit_id = "'".implode("','",$L_LMS_UNIT)."'";
		$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				// upd start hirose 2020/09/21 テスト標準化開発
				// " AND course_num='3'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				// upd end hirose 2020/09/21 テスト標準化開発
				" AND unit_num IN (".$in_lms_unit_id.")";
		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}

		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_LMS_UNIT)) {
			$ERROR[] = "同一復習ユニット単元のＩＤ、または数学以外の単元を設定しようとしています。";
		}
	}

	//フォームタイプ別の項目チェック
	$array_replace = new array_replace();

	if ($form_type == 1) {		//フォームタイプ１

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($selection_words) {
			$max_column = $selection_words_num = $array_replace->set_line($selection_words);
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$correct_num = $array_replace->set_line($correct);
			$correct = $array_replace->replace_line();
		}

		if (!$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."選択語句が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$option1_num = $array_replace->set_line($option1);
			$option1 = $array_replace->replace_line();

			$L_CORRECT = explode("\n",$correct);
			$L_OPTION1 = explode("\n",$option1);
			if ($L_OPTION1) {
				foreach($L_OPTION1 as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
					$hit = array_search($L_CORRECT[$key],$L_ANS);
					if($hit === FALSE) {
						$ERROR[] = $line_num."選択語句内に正解が含まれておりません。";
						$DATA_SET['__info']['result'] = 1;
						break;
					}
				}
			}
		}

		if (strlen(trim($option2)) == 0) {
			$ERROR[] = $line_num."シャッフル情報が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		if (ereg("[^0-1]",$option2)) {
			$ERROR[] = $line_num."シャッフル情報が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($check_mode) {
			$option3 = mb_convert_kana($option3, "asKV", "UTF-8");
			$option3 = trim($option3);
		}
		if (ereg("[^0-9]",$option3) || $option3 == 1) {
			$ERROR[] = $line_num."選択項目数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($max_column > 0) {
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = $line_num."出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				$DATA_SET['__info']['result'] = 1;
			}
		}

	} elseif ($form_type == 2) {		//フォームタイプ２

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($selection_words) {
			$max_column = $selection_words_num = $array_replace->set_line($selection_words );
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$correct_num = $array_replace->set_line($correct);
			$correct = $array_replace->replace_line();
		}

		if (!$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."選択語句が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$option1_num = $array_replace->set_line($option1);
			$option1 = $array_replace->replace_line();

			$L_CORRECT = explode("\n",$correct);
			$L_OPTION1 = explode("\n",$option1);
			if ($L_CORRECT) {
				foreach($L_CORRECT as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
				}
			}
			if ($L_OPTION1) {
				foreach($L_OPTION1 as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS1[] = trim($word); }
					$hit = array_search($L_ANS[$key],$L_ANS1);
					if($hit === FALSE) {
						$ERROR[] = $line_num."選択語句内に正解が含まれておりません。";
						$DATA_SET['__info']['result'] = 1;
						break;
					}
				}
			}
		}

		//if ($option2 == "") { $option2 ="0"; }
		if (strlen(trim($option2)) == 0) {
			$ERROR[] = $line_num."シャッフル情報が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		if (ereg("[^0-1]",$option2)) {
			$ERROR[] = $line_num."シャッフル情報が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($max_column > 1) {
		if ($max_column > 0) {
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = $line_num."出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				$DATA_SET['__info']['result'] = 1;
			}
		}

	} elseif ($form_type == 3) {	//フォームタイプ３

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}

/*		// del hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if (preg_match("/[^0-9]/",$option2)) {
			$ERROR[] = $line_num."解答欄行数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}
*/
		// add satrt hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($option2) {
			$input_row = "";
			$input_size = "";

			if (preg_match("/&lt;&gt;/",$option2)) {
				list($input_row,$input_size) = explode("&lt;&gt;",$option2);
			} else {
				$input_row = $option2;
			}
			if (preg_match("{[^0-9/]}",$input_row)) {
				$ERROR[] = $line_num."解答欄行数が不正です。";
				$DATA_SET['__info']['result'] = 1;
			}

			$input_size_err_flg = 0;
			if (preg_match("{[^0-9/]}",$input_size)) {
				$input_size_err_flg = 1;
			} else {
				$L_INPUT_SIZE = array();
				$L_INPUT_SIZE = explode('//',$input_size);
				if(is_array($L_INPUT_SIZE)) {
					foreach($L_INPUT_SIZE as $val) {
						if ($val !="" && !($val > 0 && $val <= 40)) {
							$input_size_err_flg = 1;
						}
					}
				}
			}

			if($input_size_err_flg == 1) {
				$ERROR[] = $line_num."解答欄サイズが不正です。";
				$DATA_SET['__info']['result'] = 1;
			}
		}
		// add end hasegawa 2016/10/26 入力フォームサイズ指定項目追加

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($form_type == 4) {	//フォームタイプ４

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		include_once("../../_www/problem_lib/space_checker.php");
		if (preg_match("/[^0-9]/",$option2) && strlen($option2) != 0 || $option2 > space_Checker::space_Decision($selection_words)) {
			$ERROR[] = $line_num."空白数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} elseif ($option2 < 0) {
			$ERROR[] = $line_num."空白数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} //upd 2017/04/10 yamaguchi 空白数の入力制限

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($form_type == 5) {		//フォームタイプ５

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (strlen(trim($option1)) == 0) {
			$ERROR[] = $line_num."解答ライン数が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option1 = mb_convert_kana($option1, "asKV", "UTF-8");
			$option1 = trim($option1);
		}
		if (ereg("[^0-9]",$option1)) {
			$ERROR[] = $line_num."解答ライン数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} elseif ($option1 < 1) {
			$ERROR[] = $line_num."解答ライン数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

	} elseif ($form_type == 10) {	//フォームタイプ１０
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($form_type == 11) {		//フォームタイプ１１
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}
	// add start hasegawa 2016/06/02 作図ツール
	} elseif ($form_type == 13) {		//フォームタイプ13
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$option1) {
			$ERROR[] = $line_num."問題種類が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$option2) {
			$ERROR[] = $line_num."作図問題パラメータが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}
	// add end hasegawa 2016/06/02
	// add start hirose 2020/09/18 テスト標準化開発
	} elseif ($form_type == 14) {		//フォームタイプ14
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}
	// add end hirose 2020/09/18 テスト標準化開発

	}

	if ($store_mode) {
		//データの再格納
		$DATA_SET['data']['display_problem_num'] = $display_problem_num;
		$DATA_SET['data']['form_type']  = $form_type;
		$DATA_SET['data']['question'] = $question;
		$DATA_SET['data']['problem'] = $problem;
		$DATA_SET['data']['voice_data']  = $voice_data;
		$DATA_SET['data']['hint'] = $hint;
		$DATA_SET['data']['explanation'] = $explanation;
		$DATA_SET['data']['standard_time'] = $standard_time;
		$DATA_SET['data']['first_problem'] = $first_problem;
		$DATA_SET['data']['latter_problem'] = $latter_problem ;
		$DATA_SET['data']['selection_words'] = $selection_words;
		$DATA_SET['data']['correct'] = $correct;
		$DATA_SET['data']['option1'] = $option1;
		$DATA_SET['data']['option2'] = $option2;
		$DATA_SET['data']['option3'] = $option3;
		$DATA_SET['data']['option4'] = $option4;
		$DATA_SET['data']['option5'] = $option5;
		$DATA_SET['data']['book_unit_id'] = $book_unit_id;
		$DATA_SET['data']['control_unit_id'] = $control_unit_id;
		$DATA_SET['data']['lms_unit_id'] = $lms_unit_id;
	}

	return array($DATA_SET, $ERROR);
}

/**
 * 行番号の表示形式設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param number $number 行番号
 * @return string :を付加した行番号
 */
function line_number_format($number) {
	return $number.": ";
}

/**
 * テストの為にPOSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array エラーメッセージ一覧
 */
function sub_seltest_session() {

	if ( ($_SESSION['sub_session']['select_course']['publishing_id'] != $_POST['publishing_id']) ||
 		($_SESSION['sub_session']['select_course']['gknn'] != $_POST['gknn'])) {
		unset($_SESSION['sub_session']['select_course']['book_id']);
		unset($_SESSION['sub_session']['select_course']['default_test_num']);

	} elseif (strlen($_POST['book_id'])) {
		$_SESSION['sub_session']['select_course']['book_id'] = $_POST['book_id'];

	} elseif (strlen($_POST['default_test_num'])) {
		$_SESSION['sub_session']['select_course']['default_test_num'] = $_POST['default_test_num'];
	}

	if ($_SESSION['sub_session']['select_course']['book_id'] != $_POST['book_id']) {
		unset($_SESSION['sub_session']['select_course']['book_unit_id']);

	} elseif (strlen($_POST['book_unit_id'])) {
		$_SESSION['sub_session']['select_course']['book_unit_id'] = $_POST['book_unit_id'];
	}

	if (strlen($_POST['core_code'])) { $_SESSION['sub_session']['select_course']['core_code'] = $_POST['core_code']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['sub_session']['select_course']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['sub_session']['select_course']['gknn'] = $_POST['gknn']; }

	if ($_SESSION['sub_session']['select_course']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['sub_session']['select_course']);
	}

	if (strlen($_POST['test_type'])) { $_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type']; }

	if($_POST['test_type'] == 5){
		unset($_SESSION['sub_session']['select_course']);
		$_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type'];

		if (strlen($_POST['class_id'])){
			$_SESSION['sub_session']['select_course']['class_id'] = $_POST['class_id'];
		} else {
			$_SESSION['sub_session']['select_course']['class_id'] = "select";
		}
		if (strlen($_POST['book_unit_id'])){
			$_SESSION['sub_session']['select_course']['book_unit_id'] = $_POST['book_unit_id'];
		}
	}

	return $ERROR;
}

/**
 * 問題登録　既存テストから登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string 確認フォームHTML
 */
function mt_test_exist_check_html() {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "mt_add") { $val = "mt_exist_add"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	$problem_table_type = $_POST['problem_table_type'];
	$book_unit_id = $_POST['book_unit_id'];
	if ($_POST['problem_num']) {
		$problem_num = $_POST['problem_num'];
	}

	if($_SESSION['sub_session']['s_class_id']){
		$class_id = $_SESSION['sub_session']['s_class_id'];
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	}

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	if ($_SESSION['sub_session']['select_course']['course_num']) {
		$join_and .= " AND mb.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'";
	}
	if ($_SESSION['sub_session']['select_course']['gknn']) {
		$join_and .= " AND mb.gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";
		$where .= " AND mtdp.gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";
	}
	if ($_SESSION['sub_session']['select_course']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['sub_session']['select_course']['core_code']);
		$where .= " AND mtdp.term_bnr_ccd='".$bnr_cd."'";
		$where .= " AND mtdp.term_kmk_ccd='".$kmk_cd."'";
	}
	if ($_SESSION['sub_session']['select_course']['publishing_id']) {
		$join_and .= " AND mb.publishing_id='".$_SESSION['sub_session']['select_course']['publishing_id']."'";
	} else {
		if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
			$join_and .= " AND mb.publishing_id!='0'";
		} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
			$join_and .= " AND mb.publishing_id='0'";
		}
	}
	if ($_SESSION['sub_session']['select_course']['book_id']) {
		$where .= " AND mbu.book_id='".$_SESSION['sub_session']['select_course']['book_id']."'";
	}
	if ($_SESSION['sub_session']['select_course']['book_unit_id']) {
		if ($_SESSION['sub_session']['select_course']['test_type'] != 5) {
			$where .= " AND mbu.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}else{
			$where .= " AND butp.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}
	}
	if ($_SESSION['sub_session']['select_course']['default_test_num']) {
		// ここで保持しているのはdefault_test_numだけなのでそのまま使用します。
		$where .= " AND mtdp.default_test_num='".$_SESSION['sub_session']['select_course']['default_test_num']."'";
	}
	if ($_SESSION['sub_session']['select_course']['class_id'] != "select"){
		$and_class_id =" AND mtbu.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'";
	}

	if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
		$join_sql =   " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.mk_flg='0'".
						" AND mtdp.problem_table_type=butp.problem_table_type".
						" AND mtdp.problem_num=butp.problem_num".
						" AND mtdp.default_test_num=butp.default_test_num".
						" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.mk_flg='0'".
						" AND mbu.book_unit_id=butp.book_unit_id".
						" INNER JOIN ".T_MS_BOOK." mb ON mb.mk_flg='0'".
						" AND mb.book_id=mbu.book_id".
						$join_and;
	} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
		$join_sql = " INNER JOIN ".T_MS_TEST_DEFAULT." mtd ON mtd.mk_flg='0'".
					   " AND mtd.default_test_num=mtdp.default_test_num".
					   " AND mtd.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
					   " INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.mk_flg='0'".
					   " AND tgl.default_test_num=mtd.default_test_num".
					   " INNER JOIN ".T_MS_BOOK_GROUP." mtg ON mtg.mk_flg='0'".
					   " AND mtg.test_group_id=tgl.test_group_id".
					   " AND mtg.test_gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";
	}

	// 数学検定のテスト問題の場合
	if ($_SESSION['sub_session']['select_course']['test_type'] == 5){

		if($problem_table_type == 1){	//すらら問題
			$sql = " SELECT".
					"  butp.book_unit_id,".
					"  butp.problem_num,".
					"  butp.problem_table_type,".
					"  p.form_type,".
					"  mpa.standard_time".
					" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
					" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
					" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'".
					" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num = butp.problem_num".
					" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg = '0' AND mpa.block_num = p.block_num".
					" WHERE butp.mk_flg='0'".
					"   AND butp.problem_table_type='1'".
					"   AND butp.problem_num = '".$problem_num."'".
				 	$where;
		}elseif($problem_table_type == 2){		//テスト問題
			$sql = " SELECT".
					"  butp.book_unit_id,".
					"  butp.problem_num,".
					"  butp.problem_table_type,".
					"  mtp.form_type,".
					"  mtp.standard_time".
					" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
					" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
					" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'".
					" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg = '0' AND mtp.problem_num = butp.problem_num".
					" WHERE butp.mk_flg='0'".
					"   AND butp.problem_table_type='2'".
					"   AND butp.problem_num = '".$problem_num."'".
					$where;
		}

	// 数学検定以外
	}else{
		$sql = "SELECT ".
				"mtdp.default_test_num,".
				"mtdp.problem_table_type,".
				"mtdp.problem_num,".
				"mtp.form_type,".
				"mtp.standard_time,".
				"mtdp.problem_point,".
				"mtdp.disp_sort".
				" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
				$join_sql.
				" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
				" WHERE mtdp.mk_flg='0'".
				"   AND mtdp.problem_table_type='2'".
				"   AND mtdp.problem_num='".$problem_num."'".
				$where;
	}

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['problem_table_type'] == 1) {
		$table_type = "すらら";
	} elseif ($list['problem_table_type'] == 2) {
		$table_type = "テスト専用";
	}
	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	$html .= "<td>no</td>\n";
	$html .= "<th>問題種類</th>\n";
	$html .= "<th>出題形式</th>\n";
	$html .= "<th>回答目安時間</th>\n";
	$html .= "<th>配点</th>\n";
	$html .= "<th>確認</th>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\" >\n";
	$html .= "<td>".$list['disp_sort']."</td>\n";
	$html .= "<td>".$table_type."</td>\n";
	$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
	$html .= "<td>".$list['standard_time']."</td>\n";
	$html .= "<td>".$list['problem_point']."</td>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\"></td>\n";
	// upd end hirose 2020/10/01 テスト標準化開発
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";


	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_PROBLEM_ADD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>$add_problem_point);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
	$INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$add_standard_time);
	$INPUTS['CONTROLUNITID'] 	= array('result'=>'plane','value'=>$control_unit_id);
	$INPUTS['BOOKUNITID'] 	= array('result'=>'plane','value'=>$book_unit_id);
	$INPUTS['LMSUNITID'] 	= array('result'=>'plane','value'=>$lms_unit_id);

	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>";
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$list['problem_table_type']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
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
 * 問題登録　既存テストから登録　エラーチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーメッセージ一覧
 */
function mt_test_exist_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }

	//standard_time
	$_POST['add_standard_time'] = mb_convert_kana($_POST['add_standard_time'],"as", "UTF-8");
	if ($_POST['add_standard_time']) {
		if (preg_match("/[^0-9]/",$_POST['add_standard_time'])) {
			$ERROR[] = "回答目安時間は半角数字で入力してください。";
		}
	}


	//control_unit_id
	$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
	$control_unit_id = $_POST['control_unit_id'];
	if ($control_unit_id) {

		if (preg_match("/[^0-9]/",$control_unit_id)){
			$ERROR[] = "出題単元（小問）のＩＤが不正です。";
		} else {

			$sql  = "SELECT * FROM ".T_MATH_TEST_CONTROL_UNIT.
					" WHERE class_id = '".$_SESSION['sub_session']['s_class_id']."'".
					"   AND display = '1' ".
					"   AND mk_flg = '0'".
					"   AND control_unit_id = ".$control_unit_id."".
					";";

			if ($result = $cdb->query($sql)) {
				$control_unit_count = $cdb->num_rows($result);
			}

			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($control_unit_count == 0) {
				$ERROR[] = "級に存在しない出題単元（小問）のＩＤを登録しようとしています。";
			}
		}
	} else  {
		$ERROR[] = "出題単元（小問）のＩＤが未入力です。";
	}


	//book_unit_id
	$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
	$book_unit_id = $_POST['book_unit_id'];
	if ($book_unit_id) {
		$L_BOOK_UNIT = explode("::",$book_unit_id);

		foreach($L_BOOK_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "採点単元（結果グラフ単元）のＩＤが不正です。"; }
		}
		$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";

		$sql  = "SELECT * FROM ".T_MS_BOOK_UNIT." mbu".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
				" WHERE mtbul.class_id = '".$_SESSION['sub_session']['s_class_id']."'".
				" AND mbu.display = '1'".
				" AND mbu.mk_flg = '0'".
				" AND mbu.book_unit_id IN(".$in_book_unit_id.");";

		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}
		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_BOOK_UNIT)) {
			$ERROR[] = "同一採点単元（結果グラフ単元）のＩＤ、または級に存在しない採点単元（結果グラフ単元）のＩＤを登録しようとしています。";
		}
	}else {
		$ERROR[] = "採点単元（結果グラフ単元）のＩＤが未入力です。";
	}


	//lms_unit_id
	$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
	$lms_unit_id = $_POST['lms_unit_id'];
	if ($_POST['lms_unit_id']) {
		$L_LMS_UNIT = explode("::",$lms_unit_id);

		foreach($L_LMS_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "復習ユニット単元のＩＤが不正です。"; }
		}

		$in_lms_unit_id = "'".implode("','",$L_LMS_UNIT)."'";
		$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				// upd start hirose 2020/09/21 テスト標準化開発
				// " AND course_num='3'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				// upd end hirose 2020/09/21 テスト標準化開発
				" AND unit_num IN (".$in_lms_unit_id.")";
		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}

		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_LMS_UNIT)) {
			$ERROR[] = "同一復習ユニット単元のＩＤ、または数学以外の単元を設定しようとしています。";
		}
	}

	return $ERROR;
}

/**
 * 問題登録　既存テストから登録　登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA チェックデータ
 * @return array エラーメッセージ一覧
 */
function mt_test_exist_add($CHECK_DATA) {

//echo "*mt_test_exist_add(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($CHECK_DATA){
		$class_id = $CHECK_DATA['class_id'];
		$problem_num = $CHECK_DATA['problem_num'];
		$problem_table_type = $CHECK_DATA['problem_table_type'];
		$control_unit_id = $CHECK_DATA['control_unit_id'];
		$book_unit_id = $CHECK_DATA['book_unit_id'];
		$lms_unit_id = $CHECK_DATA['lms_unit_id'];
		$disp_sort = $CHECK_DATA['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

	} elseif ($_POST){
		$class_id = $_SESSION['sub_session']['s_class_id'];
		$problem_num = $_POST['problem_num'];
		$problem_table_type = $_POST['problem_table_type'];
		$control_unit_id = $_POST['control_unit_id'];
		$book_unit_id = $_POST['book_unit_id'];
		$lms_unit_id = $_POST['lms_unit_id'];
		$disp_sort = $_POST['disp_sort'];// add hirose 2020/09/17 テスト標準化開発
	}

	if ($problem_num) {
		//book_unit_test_problemテーブルへ登録
		mt_book_unit_test_problem_add($problem_num, $problem_table_type, $book_unit_id, $class_id, $ERROR);

		//math_test_question_problemテーブルへ登録
		// upd start hirose 2020/09/17 テスト標準化開発
		// math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR);
		math_test_control_problem_add($problem_num, $problem_table_type, $control_unit_id, $class_id, $ERROR,$disp_sort);
		// upd end hirose 2020/09/17 テスト標準化開発

		//book_unit_lms_unitテーブルへ登録
		mt_lms_unit_test_problem_add($problem_num, $problem_table_type, $lms_unit_id, $ERROR);
	}

	return $ERROR;
}



/**
 * 問題登録　すらら問題新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $ERROR エラーメッセージ一覧
 * @return string すらら問題画面HTML
 */
function mt_surala_add_addform($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$book_unit_id = $_SESSION['sub_session']['s_book_unit_id'];

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].
				$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= mt_select_course();
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
			// upd start hirose 2020/09/21 テスト標準化開発
			// " AND course_num='3'".
			" AND course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
			// upd end hirose 2020/09/21 テスト標準化開発
			" AND block_num='".$_SESSION['sub_session']['select_course']['block_num']."'";
// echo $sql;
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

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['select_course']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}
	$max_page = ceil($problem_count/$page_view);
	if ($_SESSION['sub_session']['select_course']['s_page']) {
		$page = $_SESSION['sub_session']['select_course']['s_page'];
	} else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";

	// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
	// $sql  = "SELECT problem.problem_num," .
	// 		 "problem.problem_type," .
	// 		 "problem.display_problem_num," .
	// 		 "problem.form_type," .
	// 		 "problem.number_of_answers," .
	// 		 "problem.error_msg," .
	// 		 "problem.number_of_incorrect_answers," .
	// 		 "problem.correct_answer_rate," .
	// 		 "problem.display," .
	// 		 "problem_att.standard_time".
	// 		 " FROM ".T_PROBLEM." problem".
	// 		 " LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
	// 		 " WHERE problem.state='0'".
	// 		 " AND problem.course_num='3'".
	// 		 " AND problem.block_num='".$_SESSION['sub_session']['select_course']['block_num']."'".
	// 		 " ORDER BY problem.display_problem_num".$limit;
	$sql  = "SELECT ".
		" p.problem_num," .
		" p.problem_type," .
		" p.display_problem_num," .
		" p.form_type," .
		" pd.number_of_answers," .
		" p.error_msg," .
		" pd.number_of_incorrect_answers," .
		" pd.correct_answer_rate," .
		" p.display," .
		" mpa.standard_time".
		" FROM ".T_PROBLEM." p".
		" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.block_num=p.block_num".
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num  ".
		" WHERE p.state='0'".
		// upd start hirose 2020/09/21 テスト標準化開発
		// " AND p.course_num='3'".
		" AND p.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
		// upd end hirose 2020/09/21 テスト標準化開発
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
			//$newform->set_form_check($list['problem_num']);
			$newform->set_form_check($_POST['problem_num']);//upd yamaguchi 2017/10/30 Mac対応
			$newform->set_form_value("".$list['problem_num']."");
			$newform->set_form_action(" onclick=\"set_surala_problem_num('".$list['problem_num']."','".$list['standard_time']."')\"");
			$problem_btn = $newform->make();

			//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //書写はテストに登録不能にする (2018/09/28時点)
			$form_attr = "";
			if($list['form_type'] == 15){
				$form_attr = "disabled";
				$problem_btn = preg_replace("/ /", " disabled ", $problem_btn, 1); //空白を" disabled "にかえる。最初の1回だけ。//form_partsに属性セット機能がなかったのでこれで。
			}
			//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応

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
			//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
			// upd start hirose 2020/10/01 テスト標準化開発
			// $html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
			$html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\"></td>\n";
			// upd end hirose 2020/10/01 テスト標準化開発
			//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"mt_surala_check\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_PROBLEM_ADD_FORM);

	$control_unit_id_att = "<br><span style=\"color:red;\">※設定したい出題単元(小問)のＩＤを入力して下さい。";
	$control_unit_id_att .= "<br>※複数設定できません。</span>";
	$book_unit_id_att = "<br><span style=\"color:red;\">※設定したい採点単元(結果グラフ単元)のＩＤを入力して下さい。";
	$book_unit_id_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したい復習ユニットのユニット番号を入力して下さい。";
	$lms_unit_att .= "<br>※複数設定する場合は、紐づく復習ユニットを「 :: 」区切りで入力して下さい。</span>";


	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('type'=>'text','name'=>'problem_point','size'=>'5','value'=>$_POST['problem_point']);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}

	$_POST['standard_time'] = mb_convert_kana($_POST['standard_time'],"as", "UTF-8");
	$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
	$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
	$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
	$INPUTS['STANDARDTIME']			= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
	$INPUTS['CONTROLUNITID']		= array('type'=>'text','name'=>'control_unit_id','size'=>'50','value'=>$_POST['control_unit_id']);
	$INPUTS['CONTROLUNITIDATT'] 	= array('result'=>'plane','value'=>$control_unit_id_att);
	$INPUTS['BOOKUNITID']			= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$_POST['book_unit_id']);
	$INPUTS['BOOKUNITIDATT'] 		= array('result'=>'plane','value'=>$book_unit_id_att);
	$INPUTS['LMSUNITID']			= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']);
	$INPUTS['LMSUNITATT']			= array('result'=>'plane','value'=>$lms_unit_att);

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
 * 問題登録　すらら問題新規登録　エラーチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $CHECK_DATA チェックデータ
 * @return array エラーメッセージ一覧
 */
function mt_surala_add_check($CHECK_DATA) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($CHECK_DATA){
		$class_id = $CHECK_DATA['class_id'];
		$book_unit_id = $CHECK_DATA['book_unit_id'];
		$control_unit_id = $CHECK_DATA['control_unit_id'];
		$standard_time = $CHECK_DATA['standard_time'];
		$lms_unit_id = $CHECK_DATA['lms_unit_id'];

	} elseif ($_POST) {
		$class_id = $_SESSION['sub_session']['s_class_id'];
		$book_unit_id = $_POST['book_unit_id'];
		$control_unit_id = $_POST['control_unit_id'];
		$standard_time = $_POST['standard_time'];
		$lms_unit_id = $_POST['lms_unit_id'];

		if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	}

	//standard_time
	if ($standard_time) {
		$standard_time = mb_convert_kana($standard_time,"as", "UTF-8");
		if (preg_match("/[^0-9]/",$standard_time)) {
			$ERROR[] = "回答目安時間は半角数字で入力してください。";
		}
	}

	// 出題単元は、複数指定しない
	//control_unit_id
	if ($control_unit_id) {
		$control_unit_id = mb_convert_kana($control_unit_id,"as", "UTF-8");
		if (preg_match("/[^0-9]/",$control_unit_id)){
			$ERROR[] = "出題単元（小問）のＩＤが不正です。";
		} else {

			$sql  = "SELECT * FROM ".T_MATH_TEST_CONTROL_UNIT.
			" WHERE class_id = '".$_SESSION['sub_session']['s_class_id']."'".
			"   AND display = '1' ".
			"   AND mk_flg = '0'".
			"   AND control_unit_id = ".$control_unit_id."".
			";";

			if ($result = $cdb->query($sql)) {
				$control_unit_count = $cdb->num_rows($result);
			}

			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($control_unit_count == 0) {
				$ERROR[] = "級に存在しない出題単元（小問）のＩＤを登録しようとしています。";
			}

		}

	}else {
		$ERROR[] = "出題単元（小問）のＩＤが未入力です。";
	}

	//book_unit_id
	if ($book_unit_id) {
		$book_unit_id = mb_convert_kana($book_unit_id,"as", "UTF-8");
		$L_BOOK_UNIT = explode("::",$book_unit_id);

		foreach($L_BOOK_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "採点単元（結果グラフ単元）のＩＤが不正です。"; }
		}
		$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";

		$sql  = "SELECT * FROM ".T_MS_BOOK_UNIT." mbu".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
				" WHERE mtbul.class_id = '".$_SESSION['sub_session']['s_class_id']."'".
				" AND mbu.display = '1'".
				" AND mbu.mk_flg = '0'".
				" AND mbu.book_unit_id IN(".$in_book_unit_id.");";

		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}
		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_BOOK_UNIT)) {
			$ERROR[] = "同一採点単元（結果グラフ単元）のＩＤ、または級に存在しない採点単元（結果グラフ単元）のＩＤを登録しようとしています。";
		}
	}else {
		$ERROR[] = "採点単元（結果グラフ単元）のＩＤが未入力です。";
	}

	//lms_unit_id
	if ($lms_unit_id) {
		$lms_unit_id = mb_convert_kana($lms_unit_id,"as", "UTF-8");
		$L_LMS_UNIT = explode("::",$lms_unit_id);

		foreach($L_LMS_UNIT as $key => $val){
			if (preg_match("/[^0-9]/",$val)){ $ERROR[] = "復習ユニット単元のＩＤが不正です。"; }
		}

		$in_lms_unit_id = "'".implode("','",$L_LMS_UNIT)."'";
		$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				// upd start hirose 2020/09/21 テスト標準化開発
				// " AND course_num='3'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				// upd end hirose 2020/09/21 テスト標準化開発
				" AND unit_num IN (".$in_lms_unit_id.")";
		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}

		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_LMS_UNIT)) {
			$ERROR[] = "同一復習ユニット単元のＩＤ、または数学以外の単元を設定しようとしています。";
		}
	}

	return $ERROR;
}

/**
 * 問題登録　すらら問題新規登録・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return string 確認フォームHTML
 */
function mt_surala_add_check_html() {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$_POST['standard_time'] = mb_convert_kana($_POST['standard_time'],"as", "UTF-8");
	$_POST['control_unit_id'] = mb_convert_kana($_POST['control_unit_id'],"as", "UTF-8");
	$_POST['book_unit_id'] = mb_convert_kana($_POST['book_unit_id'],"as", "UTF-8");
	$_POST['lms_unit_id'] = mb_convert_kana($_POST['lms_unit_id'],"as", "UTF-8");
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "mt_add") { $val = "mt_surala_add"; }
				elseif (MODE == "mt_view") { $val = "mt_change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	$problem_table_type = $_POST['problem_table_type'];
	$book_unit_id = $_POST['book_unit_id'];
	if ($_POST['problem_num']) {
		$problem_num = $_POST['problem_num'];
	}

	if($_SESSION['sub_session']['s_class_id']){
		$class_id = $_SESSION['sub_session']['s_class_id'];
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }

	} else {//削除の場合
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"mt_delete\">\n";

		$sql  = "SELECT *" .
				 " FROM ". T_PROBLEM ." p" .
				 " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num = p.problem_num".
				 " LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.block_num = p.block_num AND butp.book_unit_id = '".$book_unit_id."' AND butp.problem_table_type='".$problem_table_type."'".
				 " WHERE p.state='0'".
				 "   AND p.problem_num='".$problem_num."' ".
				 " LIMIT 1";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		$control_unit_id = get_control_unit_id($problem_table_type, $problem_num, $class_id );

		$book_unit_id = get_book_unit($problem_table_type, $problem_num, $class_id);

		$lms_unit_id = get_lms_book_unit($problem_table_type, $problem_num);

		$HIDDEN .= "<input type=\"hidden\" name=\"control_unit_id\" value=\"".$control_unit_id."\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"lms_unit_id\" value=\"".$lms_unit_id."\">\n";

	}

	if (MODE != "mt_delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
	// $sql  = "SELECT problem_num," .
	// 		 "problem_type," .
	// 		 "display_problem_num," .
	// 		 "form_type," .
	// 		 "number_of_answers," .
	// 		 "error_msg," .
	// 		 "number_of_incorrect_answers," .
	// 		 "correct_answer_rate," .
	// 		 "display" .
	// 		 " FROM ".T_PROBLEM.
	// 		 " WHERE state='0'".
	// 		 "   AND problem_num='".$problem_num."'".
	// 		 " ORDER BY display_problem_num";
	$sql  = "SELECT p.problem_num," .
			" p.problem_type," .
			" p.display_problem_num," .
			" p.form_type," .
			" pd.number_of_answers," .
			" p.error_msg," .
			" pd.number_of_incorrect_answers," .
			" pd.correct_answer_rate," .
			" p.display" .
			" FROM ".T_PROBLEM. " p".
			" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num=p.problem_num  ".
			" WHERE p.state='0'".
			"   AND p.problem_num='".$problem_num."'".
			" ORDER BY p.display_problem_num;";

	// upd end hasegawa 2018/04/03
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	$html .= "<br>";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	$html .= "<td>no.</td>\n";
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
	$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
	$html .= "<td>".$list['number_of_answers']."</td>\n";
	$html .= "<td>".$list['number_of_incorrect_answers']."</td>\n";
	$html .= "<td>".$list['correct_answer_rate']."%</td>\n";
	if ($list['error_msg']) {
		$error_msg = "エラー有";
	} else {
		$error_msg = "----";
	}
	$html .= "<td>".$error_msg."</td>\n";
	$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['sub_session']['s_class_id']."')\"></td>\n";
	// upd end hirose 2020/10/01 テスト標準化開発
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";


	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_PROBLEM_ADD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT']		= array('result'=>'plane','value'=>$problem_point);
	} else {
		$INPUTS['PROBLEMPOINT']		= array('result'=>'plane','value'=>'--');
	}

	$standard_time = mb_convert_kana($standard_time,"as", "UTF-8");
	$control_unit_id = mb_convert_kana($control_unit_id,"as", "UTF-8");
	$book_unit_id = mb_convert_kana($book_unit_id,"as", "UTF-8");
	$lms_unit_id = mb_convert_kana($lms_unit_id,"as", "UTF-8");
	$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	$INPUTS['CONTROLUNITID']	= array('result'=>'plane','value'=>$control_unit_id);
	$INPUTS['BOOKUNITID']		= array('result'=>'plane','value'=>$book_unit_id);
	$INPUTS['LMSUNITID']		= array('result'=>'plane','value'=>$lms_unit_id);

	$make_html->set_rep_cmd($INPUTS);

	$html .= "<br>";
	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
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
 * 問題登録　すらら問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $CHECK_DATA チェックデータ
 * @return array エラーメッセージ一覧
 */
function mt_surala_add_add($CHECK_DATA) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($CHECK_DATA){
		$class_id = $CHECK_DATA['class_id'];
		$problem_num = $CHECK_DATA['problem_num'];
		$control_unit_id = $CHECK_DATA['control_unit_id'];
		$lms_unit_id = $CHECK_DATA['lms_unit_id'];
		$book_unit_id = $CHECK_DATA['book_unit_id'];
		$standard_time = $CHECK_DATA['standard_time'];
		$disp_sort = $CHECK_DATA['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

	} elseif ($_POST) {
		if($_SESSION['sub_session']['s_class_id']){
			$class_id = $_SESSION['sub_session']['s_class_id'];
		} else {
			$class_id = $_POST['s_class_id'];
		}
		$problem_num = $_POST['problem_num'];
		$control_unit_id = $_POST['control_unit_id'];
		$lms_unit_id = $_POST['lms_unit_id'];
		$book_unit_id = $_POST['book_unit_id'];
		$standard_time = $_POST['standard_time'];
		$disp_sort = $_POST['disp_sort'];// add hirose 2020/09/17 テスト標準化開発
	}

	if ($problem_num > 0) {
		//book_unit_test_problemテーブルへ登録
		mt_book_unit_test_problem_add($problem_num, '1', $book_unit_id, $class_id, $ERROR);

		//math_test_question_problemテーブルへ登録
		// upd start hirose 2020/09/17 テスト標準化開発
		// math_test_control_problem_add($problem_num, '1', $control_unit_id, $class_id, $ERROR);
		math_test_control_problem_add($problem_num, '1', $control_unit_id, $class_id, $ERROR, $disp_sort);
		// upd end hirose 2020/09/17 テスト標準化開発

		//book_unit_lms_unitテーブルへ登録
		mt_lms_unit_test_problem_add($problem_num, '1', $lms_unit_id, $ERROR);
	}

	if(!$ERROR){
		//block_num取得
		if ($_SESSION['sub_session']['select_course']['block_num']){
			$block_num = $_SESSION['sub_session']['select_course']['block_num'];
		} elseif($CHECK_DATA['block_num']) {
			$block_num = $CHECK_DATA['block_num'];
		}else{
			$sql = "SELECT block_num FROM problem WHERE problem_num = '".$problem_num."' LIMIT 1 ;";
			if($result = $cdb->query($sql)){
				$list = $cdb->fetch_assoc($result);
				$block_num = $list['block_num'];
			}
		}
		$ERROR = mt_problem_attribute_add($block_num,$standard_time);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 問題登録　テスト用問題マスタ属性登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer $block_num ドリル番号
 * @param string $standard_time 回答目安時間
 * @return array エラーメッセージ一覧
 */
function mt_problem_attribute_add($block_num,$standard_time) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$block_num) {
		$sql = "SELECT ".
				"block.course_num,".
				"block.stage_num,".
				"block.lesson_num,".
				"block.unit_num,".
				"block.block_num".
				" FROM " . T_PROBLEM ." p".
				" INNER JOIN ".T_BLOCK." block ON block.block_num=p.block_num".
				" WHERE problem_num='".$_POST['problem_num']."';";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		$course_num = $list['course_num'];
		$stage_num = $list['stage_num'];
		$lesson_num = $list['lesson_num'];
		$unit_num = $list['unit_num'];
		$block_num = $list['block_num'];
	}

	// add start hirose 2020/09/22 テスト標準化開発
	// CSVインポート時に問題番号がなく、コース番号／ステージ番号／レッスン番号／ユニット番号が取得できないので
	// 必要な情報がなければブロック番号から取得いたします。
	if($block_num > 0){
		if( !$course_num || !$stage_num || !$lesson_num || !$unit_num ){

			$sql = "SELECT * FROM ".T_BLOCK." WHERE block_num = '".$block_num."';";

			if ($result = $cdb->query($sql)) {
				if($list = $cdb->fetch_assoc($result)){
					$course_num = $list['course_num'];
					$stage_num = $list['stage_num'];
					$lesson_num = $list['lesson_num'];
					$unit_num = $list['unit_num'];
					$block_num = $list['block_num'];
				}
			}

		}
	}
	// add end hirose 2020/09/22 テスト標準化開発

	$sql = "SELECT * FROM " . T_MS_PROBLEM_ATTRIBUTE .
			" WHERE mk_flg='0'".
			" AND block_num='".$block_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	//登録されていればアップデート、無ければインサート
	$INSERT_DATA['standard_time'] 	= $standard_time;
	if ($list) {
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE block_num='".$block_num."';";

		$ERROR = $cdb->update(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA,$where);
	} else {
		$INSERT_DATA['block_num'] 		= $block_num;
		$INSERT_DATA['course_num'] 		= $course_num;
		$INSERT_DATA['stage_num'] 		= $stage_num;
		$INSERT_DATA['lesson_num'] 		= $lesson_num;
		$INSERT_DATA['unit_num'] 		= $unit_num;
		$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 		= "now()";
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		$ERROR = $cdb->insert(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA);
	}

	return $ERROR;
}

/**
 * すらら問題登録用コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param string $s_book_unit_id 教科書単元ID
 * @return string 教科書単元名
 */
function mt_select_course() {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//コース
	$course_num_html = "";
	// add start hirose 2020/09/21 テスト標準化開発
	$sql  = "SELECT course_name FROM " . T_COURSE .
			 " WHERE state='0'".
			 " AND course_num='".$_SESSION['t_practice']['course_num']."'".
			 " ;";
	if ($result = $cdb->query($sql)) {
		$c_info = $cdb->fetch_assoc($result);
		if($c_info){
			$course_num_html = $c_info['course_name'];
		}
	}
	$course_num_html .= '<input type="hidden" name="course_num" value="'.$_SESSION['t_practice']['course_num'].'">';
	// add end hirose 2020/09/21 テスト標準化開発


	//ステージ
	$sql  = "SELECT stage_num,stage_name FROM " . T_STAGE .
			 " WHERE state='0'".
			 // upd start hirose 2020/09/21 テスト標準化開発
			//  " AND course_num='3'".
			 " AND course_num='".$_SESSION['t_practice']['course_num']."'".
			 // upd end hirose 2020/09/21 テスト標準化開発
			 " ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$stage_max = $cdb->num_rows($result);
	}
	$stage_num_html .= "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['stage_num'] == $list['stage_num']) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$stage_num_html .= "<option value=\"".$list['stage_num']."\" ".$selected.">".$list['stage_name']."</option>\n";
	}

	//レッスン
	$sql = "SELECT lesson_num,lesson_name FROM " . T_LESSON .
			" WHERE state='0'".
			" AND stage_num='".$_SESSION['sub_session']['select_course']['stage_num']."'".
			" ORDER BY list_num;";
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
			 " WHERE state='0'".
			 " AND lesson_num='".$_SESSION['sub_session']['select_course']['lesson_num']."'".
			 " ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$unit_max = $cdb->num_rows($result);
	}
	$unit_html .= "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['unit_num'] == $list['unit_num']) { $selected = "selected"; } else { $selected = ""; }
		$unit_html .= "<option value=\"".$list['unit_num']."\" ".$selected.">".$list['unit_name']."</option>\n";
	}

	//ブロック
	$sql = "SELECT block_num, block_type, display, lowest_study_number".
			" FROM " . T_BLOCK .
			" WHERE state='0'".
			" AND unit_num='".$_SESSION['sub_session']['select_course']['unit_num']."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$block_max = $cdb->num_rows($result);
	}
	$block_html = "<option value=\"0\">----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['sub_session']['select_course']['block_num'] == $list['block_num']) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		if ($list['block_type'] == 1) {
			$block_name = "ドリル";
		} elseif ($list['block_type'] == 2) {
			$block_name = "診断A";
		} elseif ($list['block_type'] == 3) {
			$block_name = "診断B";
		// add start hirose 2020/09/21 テスト標準化開発
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
		// add end hirose 2020/09/21 テスト標準化開発
		}
		if ($list['display'] == "2") { $block_name .= "(非表示)"; }
		$block_html .= "<option value=\"".$list['block_num']."\" ".$selected.">".$block_name."</option>\n";
	}
	if (!$_SESSION['sub_session']['select_course']['stage_num']) {
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
			if ($_SESSION['sub_session']['select_course']['s_page_view'] == $key) {
				$sel = " selected";
			} else {
				$sel = "";
			}
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
	$html .= $course_num_html;
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

function get_book_unit_name($s_book_unit_id){

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$book_unit_name = "";
	$sql = "SELECT book_unit_name".
			" FROM ".T_MS_BOOK_UNIT.
			" WHERE mk_flg = '0'".
			" AND book_unit_id = '".$s_book_unit_id."'".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$book_unit_name = $list['book_unit_name'];
	}
	return $book_unit_name;
}


/**
 * 教科書単元　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 */
function mt_csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = mt_make_csv($L_CSV_COLUMN['math_test_problem'],1,1);
	if ($ERROR) { return $ERROR; }

	//級ごとにファイル名を変える
	if ($_SESSION['t_practice']['test_type'] == 5) {
		$filename = "math_test_problem_".$_SESSION['sub_session']['s_class_id'].".csv";
	}

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
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param array $L_CSV_COLUMN CSVカラム情報一覧
 * @param string $head_mode ヘッダモード
 * @param string $csv_mode CSVモード
 * @return array csvパラメータ
 */
function mt_make_csv($L_CSV_COLUMN, $head_mode='1', $csv_mode='1') {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$class_id = $_SESSION['sub_session']['s_class_id'];
	if($_SESSION['sub_session']['s_book_unit_id']){
		$book_unit_id = $_SESSION['sub_session']['s_book_unit_id'];
	}

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}

	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= "\"".$head_name."\",";
	}
	$csv_line .= "\n";

	$sql = " SELECT". //すらら問題
			"  mtbul.class_id,".
			"  butp.book_unit_id,".
			"  butp.problem_num,".
			"  butp.problem_table_type,".
			"  mpa.standard_time,".
			"  mtcp.disp_sort,".
			"  p.block_num,".
			"  p.form_type,".
			"  p.question,".
			"  p.problem,".
			"  p.voice_data,".
			"  p.hint,".
			"  p.explanation,".
			"  p.parameter,".
			"  p.first_problem,".
			"  p.latter_problem,".
			"  p.selection_words,".
			"  p.correct,".
			"  p.option1,".
			"  p.option2,".
			"  p.option3,".
			"  p.option4,".
			"  p.option5".
			" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
			" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$class_id."'".
			" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num = butp.problem_num".
			" LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = p.problem_num ".
			" AND mtcp.class_id='".$class_id."' AND mtcp.problem_table_type='1' ".// add hirose 2020/09/17 テスト標準化開発　違う級の問題と結びつくことがあるため条件追加
			" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg = '0' AND mpa.block_num = p.block_num".
			" WHERE butp.mk_flg='0'".
			"   AND butp.problem_table_type='1'".
			" GROUP BY problem_num".
			" UNION ALL ".
			//テスト問題
		 	" SELECT".
		 	"  mtbul.class_id,".
		 	"  butp.book_unit_id,".
		 	"  butp.problem_num,".
		 	"  butp.problem_table_type,".
		 	"  mtp.standard_time,".
		 	"  mtcp.disp_sort,".
		 	"  Null AS 'block_num',".
		 	"  mtp.form_type,".
		 	"  mtp.question,".
		 	"  mtp.problem,".
		 	"  mtp.voice_data,".
		 	"  mtp.hint,".
		 	"  mtp.explanation,".
		 	"  mtp.parameter,".
		 	"  mtp.first_problem,".
		 	"  mtp.latter_problem,".
		 	"  mtp.selection_words,".
		 	"  mtp.correct,".
		 	"  mtp.option1,".
		 	"  mtp.option2,".
		 	"  mtp.option3,".
		 	"  mtp.option4,".
		 	"  mtp.option5".
			" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
			" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0' AND mtbul.class_id = '".$class_id."'".
			" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg = '0' AND mtp.problem_num = butp.problem_num".
			" LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = mtp.problem_num ".
			" AND mtcp.class_id='".$class_id."' AND mtcp.problem_table_type='2' ".// add hirose 2020/09/17 テスト標準化開発
			" WHERE butp.mk_flg='0'".
			"   AND butp.problem_table_type='2'".
			" GROUP BY problem_num".
			" ORDER BY problem_num ";
// echo $sql;

	$L_CSV = array();
	$L_CSV_LINE = array();
	if ($result = $cdb->query($sql)) {

		$j=0;
		while ($list = $cdb->fetch_assoc($result)) {
			$exp_data = $list;
			foreach ($list as $key => $val) {
				$exp_data[$key] = preg_replace("/&quot;/","\"\"",$exp_data[$key]);
				$exp_data[$key] = preg_replace("/\n/","//",$exp_data[$key]);
				$exp_data[$key] = preg_replace("/&lt;/","<",$exp_data[$key]);
				$exp_data[$key] = preg_replace("/&gt;/",">",$exp_data[$key]);
				$exp_data[$key] = preg_replace("/&#65374;/","～",$exp_data[$key]);
			}
			if ($exp_data['problem_table_type'] != $old_problem_table_type || $exp_data['problem_num'] != $old_problem_num) {
				if ($exp_data['problem_table_type'] != $old_problem_table_type) {
					$old_problem_table_type = $exp_data['problem_table_type'];
				}
				if ($exp_data['problem_num'] != $old_problem_num) {
					$old_problem_num = $exp_data['problem_num'];
				}
				$j++;
			}

			foreach ($L_CSV_COLUMN as $key => $val) {
				//問題番号
				if ($key == "problem_num") {
					$L_CSV[$j]['problem_num'] = $exp_data['problem_num'];
				//級
				} elseif ($key == "class_id") {
					$L_CSV[$j]['class_id'] = $exp_data['class_id'];
				//問題タイプ
				} elseif ($key == "problem_type") {
					if ($exp_data['problem_table_type'] == 1) {
						$table_type = "surala";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$table_type = "test";
					}
					$L_CSV[$j]['problem_type'] = $table_type;
				//すらら用ブロック番号
				} elseif ($key == "block_num") {
					if ($exp_data['problem_table_type'] == 1){
						$block_num = $exp_data['block_num'];
					} elseif ($exp_data['problem_table_type'] == 2){
						$block_num = "";
					}
					$L_CSV[$j]['block_num'] = $block_num;

				//採点単元
				} elseif ($key == "book_unit_id") {
					$L_CSV[$j]['book_unit_id'] = $exp_data['book_unit_id'];
					$book_unit_id = get_book_unit($exp_data['problem_table_type'], $exp_data['problem_num'], $class_id);
					$L_CSV[$j]['book_unit_id'] = $book_unit_id;
				//回答目安時間
				} elseif ($key == "standard_time") {
					$L_CSV[$j]['standard_time'] = $exp_data['standard_time'];
				//出題単元
				} elseif ($key == "control_unit_id") {
					$control_unit_id = "";
					$control_unit_id = get_control_unit_id($exp_data['problem_table_type'], $exp_data['problem_num'], $class_id);
					$L_CSV[$j]['control_unit_id'] = $control_unit_id;
				//LMS単元
				} elseif ($key == "lms_unit_id") {
					$lms_unit_id = "";
					$lms_unit_id = get_lms_book_unit($exp_data['problem_table_type'], $exp_data['problem_num']);
					$L_CSV[$j]['lms_unit_id'] = $lms_unit_id;
				//出題形式
				} elseif ($key == "form_type") {
					if ($exp_data['problem_table_type'] == 1) {
						$form_type = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$form_type = $exp_data['form_type'];
					}
					$L_CSV[$j]['form_type'] = $form_type;
				//質問
				} elseif ($key == "question") {
					if ($exp_data['problem_table_type'] == 1) {
						$question = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$question = $exp_data['question'];
					}
					$L_CSV[$j]['question'] = $question;
				//問題
				} elseif ($key == "problem") {
					if ($exp_data['problem_table_type'] == 1) {
						$problem = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$problem = $exp_data['problem'];
					}
					$L_CSV[$j]['problem'] = $problem;
				//音声
				} elseif ($key == "voice_data") {
					if ($exp_data['problem_table_type'] == 1) {
						$voice_data = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$voice_data = $exp_data['voice_data'];
					}
					$L_CSV[$j]['voice_data'] = $voice_data;
				//ヒント
				} elseif ($key == "hint") {
					if ($exp_data['problem_table_type'] == 1) {
						$hint = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$hint = $exp_data['hint'];
					}
					$L_CSV[$j]['hint'] = $hint;
				//解説
				} elseif ($key == "explanation") {
					if ($exp_data['problem_table_type'] == 1) {
						$explanation = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$explanation = $exp_data['explanation'];
					}
					$L_CSV[$j]['explanation'] = $explanation;
				//パラメーター
				} elseif ($key == "parameter") {
					if ($exp_data['problem_table_type'] == 1) {
						$parameter = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$parameter = $exp_data['parameter'];
					}
					$L_CSV[$j]['parameter'] = $parameter;
				//問題前半テキスト
				} elseif ($key == "first_problem") {
					if ($exp_data['problem_table_type'] == 1) {
						$first_problem = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$first_problem = $exp_data['first_problem'];
					}
					$L_CSV[$j]['first_problem'] = $first_problem;
				//問題後半テキスト
				} elseif ($key == "latter_problem") {
					if ($exp_data['problem_table_type'] == 1) {
						$latter_problem = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$latter_problem = $exp_data['latter_problem'];
					}
					$L_CSV[$j]['latter_problem'] = $latter_problem;
				//出題項目
				} elseif ($key == "selection_words") {
					if ($exp_data['problem_table_type'] == 1) {
						$selection_words = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$selection_words = $exp_data['selection_words'];
					}
					$L_CSV[$j]['selection_words'] = $selection_words;
				//正解
				} elseif ($key == "correct") {
					if ($exp_data['problem_table_type'] == 1) {
						$correct = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$correct = $exp_data['correct'];
					}
					$L_CSV[$j]['correct'] = $correct;
				//option1
				} elseif ($key == "option1") {
					if ($exp_data['problem_table_type'] == 1) {
						$option1 = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$option1 = $exp_data['option1'];
					}
					$L_CSV[$j]['option1'] = $option1;
				//option2
				} elseif ($key == "option2") {
					if ($exp_data['problem_table_type'] == 1) {
						$option2 = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$option2 = $exp_data['option2'];
					}
					$L_CSV[$j]['option2'] = $option2;
				//option3
				} elseif ($key == "option3") {
					if ($exp_data['problem_table_type'] == 1) {
						$option3 = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$option3 = $exp_data['option3'];
					}
					$L_CSV[$j]['option3'] = $option3;
				//option4
				} elseif ($key == "option4") {
					if ($exp_data['problem_table_type'] == 1) {
						$option4 = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$option4 = $exp_data['option4'];
					}
					$L_CSV[$j]['option4'] = $option4;
				//option5
				} elseif ($key == "option5") {
					if ($exp_data['problem_table_type'] == 1) {
						$option5 = "";
					} elseif ($exp_data['problem_table_type'] == 2) {
						$option5 = $exp_data['option5'];
					}
					$L_CSV[$j]['option5'] = $option5;
				} else {
					$L_CSV[$j][$key] = $exp_data[$key];
				}
			}
		}

		$cdb->free_result($result);
		foreach ($L_CSV as $line_num => $line_val) {
			foreach ($line_val as $material_key => $material_val) {
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

	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}

	return array($csv_line,$ERROR);
}



/**
 * 問題 csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return array 結果画面html/エラーメッセージ一覧
 */
function mt_csv_import() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR =array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	// ファイルチェック
	if (!$file_tmp_name) {
		$ERROR[] = "問題（数学検定）ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "問題（数学検定）ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "問題（数学検定）ファイルがアップロードされませんでした。";
	}

	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return array($html,$ERROR);
	}

	// テスト問題読込
	$L_IMPORT_LINE = array();

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

	$handle = fopen($file_tmp_name,"r");

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
				$val = mb_convert_kana($val,"R","sjis-win");
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;

		}

		//class_idのチェック
		if ($CHECK_DATA['class_id']) {
			if ($_SESSION['sub_session']['s_class_id'] != $CHECK_DATA['class_id']) {
				$ERROR[] = $i."行目の級IDが不正なのでスキップしました。<br>";
			}
		}else {
			$CHECK_DATA['class_id'] = $_SESSION['sub_session']['s_class_id'];
		}

		// add start 2017/05/22 yoshizawa
		// 登録データにproblem_numフィールド（int）の最大値（2147483647）を超える値が設定されていると
		// インサートされる際に2147483647となっています。
		// 既存のテスト問題と同じように問題番号が指定されるときは更新、
		// 空の際は新規登録になるようにここで存在チェックを行う。
		if ($CHECK_DATA['problem_num']) {
			if ($CHECK_DATA['problem_type'] == "test") {
				$sql = "SELECT problem_num FROM ".T_MS_TEST_PROBLEM.
						" WHERE problem_num='".$CHECK_DATA['problem_num']."'";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if (!$list['problem_num']) {
					$ERROR[] = $i."行目 問題番号に該当するテスト問題が存在しません。";
					continue;
				}
			} else if ($CHECK_DATA['problem_type'] == "surala") {
				$sql = "SELECT problem_num FROM ".T_PROBLEM.
					" WHERE problem_num='".$CHECK_DATA['problem_num']."'";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if (!$list['problem_num']) {
					$ERROR[] = $i."行目 問題番号に該当するすらら問題が存在しません。";
					continue;
				}
			}
		}
		// ddd end 2017/05/22 yoshizawa

		//problem_table_type のチェック
		if ($CHECK_DATA['problem_type'] == "surala") {
			$CHECK_DATA['problem_table_type'] = 1;
		} elseif ($CHECK_DATA['problem_type'] == "test") {
			$CHECK_DATA['problem_table_type'] = 2;
		} else {
			$ERROR[] = $i."行目の問題タイプが不正なのでスキップしました。<br>";
			continue;
		}

		// add start hirose 2020/09/17 テスト標準化開発
		if($CHECK_DATA['disp_sort']){
			if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
				$ERROR[] = $i."行目のNoは半角数字で入力してください。";
				continue;
			}else{
				$where = "";
				if($CHECK_DATA['problem_num']){
					$where = " AND mtcp.problem_num != '".$CHECK_DATA['problem_num']."'";
				}
				$sql =  "SELECT count(*) AS row_count".
						" FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp".
						" WHERE mtcp.mk_flg = 0".
						" AND mtcp.class_id = '".$CHECK_DATA['class_id']."'".
						" AND mtcp.disp_sort = '".$CHECK_DATA['disp_sort']."'".
						$where.
						";";
						
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
					if($list['row_count']>0){
						$ERROR[] = $i."行目のNoはすでに使用されています。";
						continue;
					}
				}
			}
		}
		// add end hirose 2020/09/17 テスト標準化開発

		//problem_numのチェック
		if ($CHECK_DATA['problem_num']) {
			if($CHECK_DATA['problem_table_type'] == 2) {
				$join_sql = " INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.problem_num = '".$CHECK_DATA['problem_num']."'";
				$where = " AND butp.problem_num = mtp.problem_num";
			} elseif ($CHECK_DATA['problem_table_type'] == 1) {
				$where = " AND butp.problem_num = '".$CHECK_DATA['problem_num']."'";
			}

			$sql = "SELECT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				$join_sql.
				" WHERE butp.book_unit_id = '".$CHECK_DATA['book_unit_id']."'".
				$where.
				" AND butp.problem_table_type = '".$CHECK_DATA['problem_table_type']."'".
				" AND butp.mk_flg = 0 LIMIT 1 ;";

			if ($result = $cdb->query($sql)) {
				$list2 = $cdb->fetch_assoc($result);
			}
			if ($list2['problem_num']) {
				$L_MS_TEST_PROBLEM_INS_MODE = "upd";
			} else {
				$L_MS_TEST_PROBLEM_INS_MODE = "add";
			}

		} else {

			if($CHECK_DATA['problem_table_type'] == 1){
				$ERROR[] = $i."行目すらら問題の場合は問題番号を指定してください。<br>";
				continue;
			}elseif($CHECK_DATA['problem_table_type'] == 2) {
				$L_MS_TEST_PROBLEM_INS_MODE = "add";
			}

		 }
		// テスト専用問題登録処理
		if ($CHECK_DATA['problem_type'] == "test") {
			//データチェック
			$DATA_SET['__info']['call_mode'] = 1;		// 0=入力フォーム、1:csv入力
			$DATA_SET['__info']['line_num'] = $i;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
			$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
			$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

			$DATA_SET['data']['form_type'] = $CHECK_DATA['form_type'];
			$DATA_SET['data']['question'] = trim($CHECK_DATA['question']);
			$DATA_SET['data']['problem'] = trim($CHECK_DATA['problem']);
			$DATA_SET['data']['voice_data'] = trim($CHECK_DATA['voice_data']);
			$DATA_SET['data']['hint'] = trim($CHECK_DATA['hint']);
			$DATA_SET['data']['parameter'] = trim($CHECK_DATA['parameter']);
			$DATA_SET['data']['explanation'] = trim($CHECK_DATA['explanation']);
			$DATA_SET['data']['first_problem'] = trim($CHECK_DATA['first_problem']);
			$DATA_SET['data']['latter_problem'] = trim($CHECK_DATA['latter_problem']);
			$DATA_SET['data']['selection_words'] = trim($CHECK_DATA['selection_words']);
			$DATA_SET['data']['correct'] = trim($CHECK_DATA['correct']);
			$DATA_SET['data']['option1'] = $CHECK_DATA['option1'];
			$DATA_SET['data']['option2'] = $CHECK_DATA['option2'];
			$DATA_SET['data']['option3'] = $CHECK_DATA['option3'];
			$DATA_SET['data']['option4'] = $CHECK_DATA['option4'];
			$DATA_SET['data']['option5'] = $CHECK_DATA['option5'];
			$DATA_SET['data']['standard_time'] = $CHECK_DATA['standard_time'];
			$DATA_SET['data']['control_unit_id'] = $CHECK_DATA['control_unit_id'];
			$DATA_SET['data']['book_unit_id'] = $CHECK_DATA['book_unit_id'];
			$DATA_SET['data']['lms_unit_id'] = $CHECK_DATA['lms_unit_id'];
			$DATA_SET['data']['disp_sort'] = $CHECK_DATA['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

			$ERROR_DUMMY = array();
			list($DATA_SET, $DATA_ERROR[$i]) = mt_test_check($DATA_SET, $ERROR_DUMMY);
			if ($DATA_ERROR[$i]) {
				$DATA_ERROR[$i][] = $i."行目 上記エラーによりスキップしました。1<br>";
				continue;
			}

			$CHECK_DATA['form_type'] = $DATA_SET['data']['form_type'];
			$CHECK_DATA['question'] = $DATA_SET['data']['question'];
			$CHECK_DATA['problem'] = $DATA_SET['data']['problem'];
			$CHECK_DATA['voice_data'] = $DATA_SET['data']['voice_data'];
			$CHECK_DATA['hint'] = $DATA_SET['data']['hint'];
			$CHECK_DATA['parameter'] = $DATA_SET['data']['parameter'];
			$CHECK_DATA['explanation'] = $DATA_SET['data']['explanation'];
			$CHECK_DATA['first_problem'] = $DATA_SET['data']['first_problem'];
			$CHECK_DATA['latter_problem'] = $DATA_SET['data']['latter_problem'];
			$CHECK_DATA['selection_words'] = $DATA_SET['data']['selection_words'];
			$CHECK_DATA['correct'] = $DATA_SET['data']['correct'];
			$CHECK_DATA['option1'] = $DATA_SET['data']['option1'];
			$CHECK_DATA['option2'] = $DATA_SET['data']['option2'];
			$CHECK_DATA['option3'] = $DATA_SET['data']['option3'];
			$CHECK_DATA['option4'] = $DATA_SET['data']['option4'];
			$CHECK_DATA['option5'] = $DATA_SET['data']['option5'];
			$CHECK_DATA['standard_time'] = $DATA_SET['data']['standard_time'];
			$CHECK_DATA['control_unit_id'] = $DATA_SET['data']['control_unit_id'];
			$CHECK_DATA['book_unit_id'] = $DATA_SET['data']['book_unit_id'];
			$CHECK_DATA['lms_unit_id'] = $DATA_SET['data']['lms_unit_id'];
			$CHECK_DATA['disp_sort'] = $DATA_SET['data']['disp_sort'];// add hirose 2020/09/17 テスト標準化開発

			if ($L_MS_TEST_PROBLEM_INS_MODE == "add") {

				if ($CHECK_DATA['problem_num']) {
					//ms_test_tableに問題番号があるか確認
					$sql = "SELECT problem_num FROM ".T_MS_TEST_PROBLEM.
							" WHERE problem_num='".$CHECK_DATA['problem_num']."'".
							// upd start hirose 2020/09/21 テスト標準化開発
							// " AND course_num='3'";
							" AND course_num='".$_SESSION['t_practice']['course_num']."'";
							// upd end hirose 2020/09/21 テスト標準化開発

					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					//新規登録
					if (!$list['problem_num']) {
						$SYS_ERROR[$i] = mt_test_add_add($CHECK_DATA);
					//既存のテスト登録の場合
					}else {
						$SYS_ERROR[$i] = mt_test_exist_add($CHECK_DATA);
						$SYS_ERROR[$i] = mt_change($CHECK_DATA);
					}
				// CSVインポートにて問題番号がない場合は新規登録します。
				} else {
					$SYS_ERROR[$i] = mt_test_add_add($CHECK_DATA);
				}

			//更新
			} elseif ($L_MS_TEST_PROBLEM_INS_MODE == "upd") {
				$SYS_ERROR[$i] = mt_change($CHECK_DATA);
			}

		// すらら問題登録処理
		} elseif ($CHECK_DATA['problem_type'] == "surala") {

			if ($CHECK_DATA['problem_num']) {
				//問題番号があるか確認
				$sql = "SELECT problem_num FROM ".T_PROBLEM.
						" WHERE problem_num='".$CHECK_DATA['problem_num']."'".
						// upd start hirose 2020/09/21 テスト標準化開発
						// " AND course_num='3'";
						" AND course_num='".$_SESSION['t_practice']['course_num']."'";
						// upd end hirose 2020/09/21 テスト標準化開発

				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if (!$list['problem_num']) {
					$ERROR[] = $i."行目 入力された問題番号は不正です。";
					continue;
				}
			}
			$DATA_ERROR[$i] = $ERROR;

			//データチェック
			$DATA_ERROR[$i] = mt_surala_add_check($CHECK_DATA);
			if ($DATA_ERROR[$i]) {
				$DATA_ERROR[$i][] = $i."行目 上記エラーによりスキップしました。<br>";
				continue;
			}

			//登録
			if ($L_MS_TEST_PROBLEM_INS_MODE == "add"){
				$SYS_ERROR[$i] = mt_surala_add_add($CHECK_DATA);
			//更新
			} elseif($L_MS_TEST_PROBLEM_INS_MODE == "upd") {
				$SYS_ERROR[$i] = mt_change($CHECK_DATA);
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
	if (!$ERROR) {
		$html = "<br>正常に全て登録が完了しました。";
	} else {
		$html = "<br>エラーのある行数以外の登録が完了しました。";
	}

	return array($html,$ERROR);
}


/**
 * voice_data既定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $voice_data 音声データ
 * @param integer $problem_num プログラム番号
 * @return array 音声データ／エラーメッセージ
 */
function math_copy_default_voice_data($voice_data,$problem_num) {

	if ($voice_data) {
		$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		if (!ereg("^".$problem_num."_",$voice_data)) {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$voice_data)) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$voice_data,$dir_name.$problem_num."_".$voice_data);
			}
			$voice_data 		= $problem_num."_".$voice_data;
		} else {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$voice_data)) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$voice_data,$dir_name.$voice_data);
			}
		}
	}

	return array($voice_data, $ERROR);

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
function get_saiten_tangen_id_string($class_id, $problem_num) {

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
function get_syutsudai_tangen_id_string($class_id, $problem_num) {

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
