<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　問題設定
 *
 * 履歴
 * 2010/12/22 初期設定
 *
 * @author Azet
 */

// hirano


/*---------------------------------------------
テスト設定　年度形成　テストが有ればその最古の年度を取得してその年度から

1600行付近　既存テストからの問題登録
確認ボタンから問題確認画面表示を専用問題にも対応させる
---------------------------------------------*/
include("../../_www/problem_lib/problem_regist.php");


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function start() {

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


	if (ACTION == "check") { $ERROR = check(); }
	elseif (ACTION == "test_check") { $ERROR = test_check(); }
	elseif (ACTION == "surala_check") { $ERROR = surala_add_check(); }
	elseif (ACTION == "problem_check") { $ERROR = test_add_check(); }
	elseif (ACTION == "exist_check") { $ERROR = test_exist_check(); }

	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "sub_session") { $ERROR = sub_session(); }
		elseif (ACTION == "sub_course_session") { $ERROR = sub_course_session(); }
		elseif (ACTION == "sub_test_session") { $ERROR = sub_test_session(); }
		elseif (ACTION == "view_session") { $ERROR = view_session(); }
		elseif (ACTION == "eng_word_view_session") { $ERROR = eng_word_view_session(); }	//add yamaguchi 2018/10/02 すらら英単語追加
		elseif (ACTION == "sub_list_session") { $ERROR = sub_list_session(); }
		elseif (ACTION == "sub_form_type_session") { $ERROR = sub_form_type_session(); }
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
		elseif (ACTION == "test_add") { $ERROR = test_add(); }
		elseif (ACTION == "test_change") { $ERROR = test_change(); }
		elseif (ACTION == "surala_add") { $ERROR = surala_add_add(); }
		elseif (ACTION == "problem_add") { $ERROR = test_add_add(); }
		elseif (ACTION == "exist_add") { $ERROR = test_exist_add(); }
	}

	// add start 2017/05/24 yoshizawa
	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cdb->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
	}
	// add end 2017/05/24 yoshizawa

	$html .= select_unit_view();
	if (MODE == "add") {
		// すらら問題登録
		if ($_SESSION['sub_session']['add_type']['problem_type'] == "surala") {
			if (ACTION == "surala_check") {
				if (!$ERROR) { $html .= surala_add_check_html(); }
				else { $html .= surala_add_addform($ERROR); }
			} elseif (ACTION == "surala_add") {
				if (!$ERROR) { $html .= problem_list($ERROR); }
				else { $html .= surala_add_addform($ERROR); }
			} else {
				$html .= surala_add_addform($ERROR);
			}
		// テスト専用問題登録
		} elseif($_SESSION['sub_session']['add_type']['problem_type'] == "test") {
			// 新規登録
			if ($_SESSION['sub_session']['add_type']['add_type'] == "add") {
				if (ACTION == "problem_check") {
					if (!$ERROR) { $html .= test_add_check_html(); }
					else { $html .= test_add_addform($ERROR); }
				} elseif (ACTION == "problem_add") {
					if (!$ERROR) { $html .= problem_list($ERROR); }
					else { $html .= test_add_addform($ERROR); }
				} else {
					$html .= test_add_addform($ERROR);
				}
			// 既存のテスト専用問題登録
			} elseif ($_SESSION['sub_session']['add_type']['add_type'] == "exist") {
				if (ACTION == "exist_check") {
					if (!$ERROR) { $html .= test_exist_check_html(); }
					else { $html .= test_exist_addform($ERROR); }
				} elseif (ACTION == "exist_add") {
					if (!$ERROR) { $html .= problem_list($ERROR); }
					else { $html .= test_exist_addform($ERROR); }
				} else {
					$html .= test_exist_addform($ERROR);
				}
			}
		}
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= problem_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "delete") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= problem_list($ERROR); }
			else { $html .= check_html(); }
		} else {
			$html .= check_html();
		}
	} elseif (MODE == "test_add") {
		if (ACTION == "test_check") {
			if (!$ERROR) { $html .= test_check_html(); }
			else { $html .= test_addform($ERROR); }
		} elseif (ACTION == "test_add") {
			if (!$ERROR) { $html .= problem_list($ERROR); }
			else { $html .= test_addform($ERROR); }
		} else {
			$html .= test_addform($ERROR);
		}
	} elseif (MODE == "test_view") {
		if (ACTION == "test_check") {
			if (!$ERROR) { $html .= test_check_html(); }
			else { $html .= test_viewform($ERROR); }
		} elseif (ACTION == "test_change") {
			if (!$ERROR) { $html .= problem_list($ERROR); }
			else { $html .= test_viewform($ERROR); }
		} else {
			$html .= test_viewform($ERROR);
		}
	} elseif (MODE == "test_del") {
		if (ACTION == "test_change") {
			if (!$ERROR) { $html .= problem_list($ERROR); }
			else { $html .= test_check_html(); }
		} else {
			$html .= test_check_html();
		}
	} else {
		//if ($_SESSION['t_practice']['gknn']&&($_SESSION['t_practice']['core_code'] || $_SESSION['t_practice']['default_test_num'])) {
		// 定期テスト 学力診断テスト
		// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
		//コースを未選択にしても表示できてしまうため、コース情報を追加
		// if (($_SESSION['t_practice']['test_type'] == 1 && $_SESSION['t_practice']['core_code']) || ($_SESSION['t_practice']['test_type'] == 4 && $_SESSION['t_practice']['gknn'])) {
		if (($_SESSION['t_practice']['test_type'] == 1 && $_SESSION['t_practice']['course_num'] && $_SESSION['t_practice']['core_code']) || ($_SESSION['t_practice']['test_type'] == 4 && $_SESSION['t_practice']['gknn'])) {
		// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
			$html .= problem_list($ERROR);
		}
		//add start okabe 2013/06/24
		// 判定テストは外部ファイルへ
		if ($_SESSION['t_practice']['test_type'] == "hantei_test" ) {
			include("../../_www/admin_lib/test_practice_hantei_test_problem.php");
			include("../../_www/admin_lib/test_check_problem_data.php");
			//$html = hantei_test_select_unit_view();
			//$html .= hantei_test_hanteimei_list($ERROR);
			$html = hantei_test_start();
		}
		//add end okabe 2013/06/24

		//数学検定は外部ファイルへ
		if ($_SESSION['t_practice']['test_type'] == 5) {
			include("../../_www/admin_lib/test_practice_math_test_problem.php");
//			include("../../_www/admin_lib/test_check_problem_data.php");
			$html = math_test_start();
		}
		//add hasegawa 2015/9/14 02_作業要件/34_数学検定/数学検定

		//add start yamaguchi 2018/10/02 すらら英単語追加
		//すらら英単語テスト
		if ($_SESSION['t_practice']['test_type'] == 6) {
			include("../../_www/admin_lib/test_practice_vocabulary_test_problem.php");
			$html = eng_word_test_start();
			//$html = eng_word_select_unit_view();
			//$html .= eng_word_test_list($ERROR);
		}
		//add end yamaguchi 2018/10/02
	}

	return $html;
}


/**
 * コース、学年、出版社、教科書選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function select_unit_view() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];// add hirose 2020/09/11 テスト標準化開発

	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_WRITE_TYPE;		// add ookawara 2012/07/29
	// global $L_GKNN_LIST_TYPE1;	// add 2015/02/16 yoshizawa 定期テスト高校版対応	// del karasawa 2020/12/22 定期テスト学年追加開発
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
	} elseif($_SESSION['t_practice']['test_type'] == 1) {
		$test_type1 = new TestStdCfgType1($cdb);
		$l_write_type = $test_type1->getTestUseCourseAdmin();
	// add end hirose 2020/12/15 テスト標準化開発 定期テスト
	} else {
		$l_write_type = $L_WRITE_TYPE;
	}
	// update end 2020/08/29 yoshizawa テスト標準化開発

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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
		// del 2015/02/16 yoshizawa 定期テスト高校版対応
		////学年
		//foreach($L_GKNN_LIST as $key => $val) {
		//	if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		//	$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		//}
		//----------------------------------------------
		// add 2015/02/16 yoshizawa 定期テスト高校版対応
		//学年
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

		//---------------------------
		// 定期テスト
		//---------------------------
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
					" WHERE ".
					" mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
					$where." ORDER BY disp_sort;";
				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}
				if (!$book_unit_count) {
					$book_unit_html = "<option value=\"0\">設定されていません</option>\n";
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
				//if (!$_SESSION['t_practice']['course_num'] || !$_SESSION['t_practice']['publishing_id'] || !$_SESSION['t_practice']['gknn']) {
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

		//--------------------------------
		// 学力診断テスト
		//--------------------------------
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
					/*
					$msg_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$msg_html .= "<input type=\"hidden\" name=\"mode\" value=\"test_add\">\n";
					$msg_html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
					$msg_html .= "<input type=\"submit\" value=\"新規テスト登録\" style=\"float:left;\">\n";
					$msg_html .= "</form>\n";
					*/
					if ($_SESSION['t_practice']['default_test_num']) {
						/*
						$msg_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
						$msg_html .= "<input type=\"hidden\" name=\"mode\" value=\"test_view\">\n";
						$msg_html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
						$msg_html .= "<input type=\"submit\" value=\"テスト設定\" style=\"float:left;\">\n";
						$msg_html .= "</form>\n";
						$msg_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
						$msg_html .= "<input type=\"hidden\" name=\"mode\" value=\"test_del\">\n";
						$msg_html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
						$msg_html .= "<input type=\"submit\" value=\"テスト削除\">\n";
						$msg_html .= "</form>\n";
						*/
					} else {
						unset($_SESSION['t_practice']['book_id']);
					//	$msg_html .= "<span style=\"clear: both;\">\n";
					//	$msg_html .= "テスト名を選択してください。</span>";
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
 * 教科書単元表示メニューセッション操作
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function view_session() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];		// add hasegawa 2016/09/29 国語文字数カウント

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
	//	if($_POST['book_id']) { $ERROR = auto_test_add(); }
	} elseif(strlen($_POST['default_test_num'])) {
		$_SESSION['t_practice']['default_test_num'] = $_POST['default_test_num'];
	}
	if ($_SESSION['t_practice']['book_id'] != $_POST['book_id']) {
		unset($_SESSION['t_practice']['book_unit_id']);
	} elseif(strlen($_POST['book_unit_id'])) {
		$_SESSION['t_practice']['book_unit_id'] = $_POST['book_unit_id'];
	}


	if (strlen($_POST['core_code'])) { $_SESSION['t_practice']['core_code'] = $_POST['core_code']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['t_practice']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['t_practice']['gknn'] = $_POST['gknn']; }
	if (strlen($_POST['course_num'])) {
		$_SESSION['t_practice']['course_num'] = $_POST['course_num'];
		// add start hasegawa 2016/09/29 国語文字数カウント
		// write_typeを取得する
		$write_type = "";
		$sql = "SELECT write_type FROM ".T_COURSE.
			" WHERE course_num = '".$_SESSION['t_practice']['course_num']."' AND display='1' AND state='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$write_type = $list['write_type'];
			}
		}
		$_SESSION['t_practice']['write_type'] = $write_type;
		// add end hasegawa 2016/09/29
	}

	if ($_SESSION['t_practice']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['t_practice']);
	}
	if (strlen($_POST['test_type'])) { $_SESSION['t_practice']['test_type'] = $_POST['test_type']; }
	$_SESSION['sub_session']['s_page'] = 1;

	// add satrt yoshizawa 2015/10/08 02_作業要件/34_数学検定/数学検定
	 // 数学検定の場合はコース固定
	// del start hirose 2020/09/21 テスト標準化開発
	// if ($_POST['test_type'] == '5') {
	// 	$_SESSION['t_practice']['course_num'] = '3';
	// }
	// del end hirose 2020/09/21 テスト標準化開発
	// add end yoshizawa 2015/10/08

	return $ERROR;
}


/**
 * 学力診断　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function test_addform($ERROR) {

	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	//年リスト
	$L_YEAR = year_list();
	// upd start hirose 2020/09/11 テスト標準化開発
	//コースリスト
	// $L_COURSE_LIST = course_list();
	$L_COURSE_LIST = get_course_name_array($_SESSION['t_practice']['course_num']);
	// upd end hirose 2020/09/11 テスト標準化開発
	//テストマスタリスト
	$L_BOOK_LIST = book_list($_SESSION['t_practice']['course_num'],$_SESSION['t_practice']['gknn']);

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"test_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_DEFAULT_FORM_4);

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

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COURSENUM'] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
	$INPUTS['GKNN'] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$_SESSION['t_practice']['gknn']]);
	$INPUTS['TESTNAME'] 		= array('type'=>'text','name'=>'test_name','size'=>'50','value'=>$_POST['test_name']);
	$INPUTS['LIMITTIME'] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$_POST['limit_time']);
	$INPUTS['BOOKID'] 			= array('type'=>'select','name'=>'book_id','array'=>$L_BOOK_LIST,'check'=>$_POST['book_id']);
	$INPUTS['KKNFROM'] 			= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS['KKNTO'] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS['KKNATT'] 			= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS['USRBKO'] 			= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 学力診断　修正フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function test_viewform($ERROR) {

	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql  = "SELECT *" . " FROM " . T_MS_TEST_DEFAULT .
			" WHERE mk_flg='0' AND default_test_num='".$_SESSION['t_practice']['default_test_num']."' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
			$html .= "<input type=\"submit\" value=\"戻る\">\n";
			$html .= "</form>\n";
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
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"test_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	if ($_SESSION['t_practice']['test_type'] == 1) {
		$set_file_name = TEST_DEFAULT_FORM_1;
	} elseif ($_SESSION['t_practice']['test_type'] == 4) {
		$set_file_name = TEST_DEFAULT_FORM_4;

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
	}
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

	// upd start hirose 2020/09/11 テスト標準化開発
	//コースリスト
	// $L_COURSE_LIST = course_list();
	$L_COURSE_LIST = get_course_name_array($_SESSION['t_practice']['course_num']);
	// upd end hirose 2020/09/11 テスト標準化開発
	//テストマスタリスト
	$L_BOOK_LIST = book_list($_SESSION['t_practice']['course_num'],$_SESSION['t_practice']['gknn']);

	$INPUTS['COURSENUM'] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
	$INPUTS['GKNN'] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$_SESSION['t_practice']['gknn']]);
	$INPUTS['TESTNAME'] 		= array('type'=>'text','name'=>'test_name','size'=>'50','value'=>$test_name);
	$INPUTS['LIMITTIME'] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$limit_time);
	$INPUTS['BOOKID'] 			= array('type'=>'select','name'=>'book_id','array'=>$L_BOOK_LIST,'check'=>$book_id);
	$INPUTS['KKNFROM'] 			= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS['KKNTO'] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS['KKNATT'] 			= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS['USRBKO'] 			= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 学力診断　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function test_check() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	//年リスト
	$L_YEAR = year_list();

	if (!$_POST['limit_time']) { $ERROR[] = "制限時間が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$_POST['limit_time'])) {
			$ERROR[] = "制限時間は半角数字で入力してください。";
		} elseif ($_POST['limit_time'] < 0) {
			$ERROR[] = "制限時間は0以下の指定はできません。";
		}
	}

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }

	if ($_SESSION['t_practice']['test_type'] == 4) {
		if (!$_POST['test_name']) { $ERROR[] = "テスト名が未入力です。"; }
		if (!$_POST['book_id']) { $ERROR[] = "テストマスタが未選択です。"; }

		if (!$_POST['from_year'] || !$_POST['from_month'] || !$_POST['from_day']) {
			$ERROR[] = "テスト期間 開始が未選択です。";
		} else {
			if (checkdate($L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']])) {
				//$from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']]));
				//if (date('Ymd') > $from_day) { $ERROR[] = "テスト期間 開始が不正です。入力された日付が過去になっています。"; }
			} else { $ERROR[] = "テスト期間 開始が不正です。選択された日付は存在しません。"; }
		}
		if (!$_POST['to_year'] || !$_POST['to_month'] || !$_POST['to_day']) {
			$ERROR[] = "テスト期間 終了が未選択です。";
		} else {
			if (checkdate($L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']])) {
				$to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']]));
				if (date('Ymd') > $to_day) { $ERROR[] = "テスト期間 終了が不正です。入力された日付が過去になっています。"; }
				elseif ($from_day > $to_day) { $ERROR[] = "テスト期間 終了が不正です。入力された日付がテスト期間 開始より過去になっています。"; }
			} else { $ERROR[] = "テスト期間 終了が不正です。選択された日付は存在しません。"; }
		}
	}

	return $ERROR;
}


/**
 * 学力診断　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function test_check_html() {

	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "test_add") { $val = "test_add"; }
				elseif (MODE == "test_view") { $val = "test_change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
//削除用処理
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"test_change\">\n";
		$sql  = "SELECT *" . " FROM " . T_MS_TEST_DEFAULT .
			" WHERE mk_flg='0' AND default_test_num='".$_SESSION['t_practice']['default_test_num']."' LIMIT 1;";

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
	}

	// upd start hirose 2020/09/11 テスト標準化開発
	//コースリスト
	// $L_COURSE_LIST = course_list();
	$L_COURSE_LIST = get_course_name_array($_SESSION['t_practice']['course_num']);
	// upd end hirose 2020/09/11 テスト標準化開発
	//テストマスタリスト
	$L_BOOK_LIST = book_list($_SESSION['t_practice']['course_num'],$_SESSION['t_practice']['gknn']);

	if (MODE != "test_del") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	if ($_SESSION['t_practice']['test_type'] == 1) {
		$set_file_name = TEST_DEFAULT_FORM_1;
	} elseif ($_SESSION['t_practice']['test_type'] == 4) {
		$set_file_name = TEST_DEFAULT_FORM_4;

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
	}
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COURSENUM'] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
	$INPUTS['GKNN'] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$_SESSION['t_practice']['gknn']]);
	$INPUTS['TESTNAME'] 		= array('result'=>'plane','value'=>$test_name);
	$INPUTS['LIMITTIME'] 		= array('result'=>'plane','value'=>$limit_time);
	$INPUTS['BOOKID'] 			= array('result'=>'plane','value'=>$L_BOOK_LIST[$book_id]);
	$INPUTS['KKNFROM'] 			= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS['KKNTO'] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS['USRBKO'] 			= array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	if (MODE != "test_del") {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 学力診断　新規登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function test_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA= array();

	//年リスト
	$L_YEAR = year_list();

	$sql = "SELECT MAX(default_test_num) AS max_num FROM " . T_MS_TEST_DEFAULT . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_num']) { $default_test_num = $list['max_num'] + 1; } else { $default_test_num = 1; }

	$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
	if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
		$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
	}
	$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
	if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
		$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
	}

	$INSERT_DATA['default_test_num'] 	= $default_test_num;
	$INSERT_DATA['test_type'] 			= 4;
	$INSERT_DATA['course_num'] 			= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['test_gknn'] 			= $_SESSION['t_practice']['gknn'];
	$INSERT_DATA['test_name']		 	= $_POST['test_name'];
	$INSERT_DATA['limit_time'] 			= $_POST['limit_time'];
	$INSERT_DATA['book_id']		 		= $_POST['book_id'];
	$INSERT_DATA['kkn_from']		 	= $kkn_from;
	$INSERT_DATA['kkn_to']		 		= $kkn_to;
	$INSERT_DATA['apply_level'] 		= 1;
	$INSERT_DATA['disp_sort'] 			= $default_test_num;
	//$INSERT_DATA[ins_syr_id] 			= ;
	$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_DEFAULT,$INSERT_DATA);

	$_SESSION['t_practice']['default_test_num'] = $default_test_num;

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 定期テスト　テスト作成
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function auto_test_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA= array();

	$L_CORE_CODE = core_code_list();

	$sql = "SELECT MAX(default_test_num) AS max_num FROM " . T_MS_TEST_DEFAULT . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_num']) { $default_test_num = $list['max_num'] + 1; } else { $default_test_num = 1; }

	$INSERT_DATA['test_type'] 		= 1;
	$INSERT_DATA['course_num'] 		= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['test_gknn'] 		= $_SESSION['t_practice']['gknn'];
	$INSERT_DATA['test_name']		= "定期テスト";
	$INSERT_DATA['limit_time'] 		= 50 * 60;
	$INSERT_DATA['book_id']		 	= $_SESSION['t_practice']['book_id'];
	$INSERT_DATA['apply_level'] 	= 1;

	$i = 0;
	foreach($L_CORE_CODE as $key => $val) {
		$sql = "SELECT * FROM ". T_MS_TEST_DEFAULT .
			" WHERE test_type='1'".
			" AND book_id='".$_SESSION['t_practice']['book_id']."'".
			" AND term_bnr_ccd='".$L_CORE_CODE[$key]['bnr_cd']."'".
			" AND term_kmk_ccd='".$L_CORE_CODE[$key]['kmk_cd']."' AND mk_flg='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if (!$list) {
			$INSERT_DATA['default_test_num'] 	= $default_test_num + $i;
			$INSERT_DATA['term_bnr_ccd'] 		= $L_CORE_CODE[$key]['bnr_cd'];
			$INSERT_DATA['term_kmk_ccd'] 		= $L_CORE_CODE[$key]['kmk_cd'];
			$INSERT_DATA['disp_sort'] 			= $default_test_num + $i;
//			$INSERT_DATA[ins_syr_id] 			= ;
			$INSERT_DATA['ins_tts_id'] 			= "System";
			$INSERT_DATA['ins_date'] 			= "now()";
			$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date'] 			= "now()";

			$SYS_ERROR[$key] = $cdb->insert(T_MS_TEST_DEFAULT,$INSERT_DATA);
			$i++;
		}
	}
	if(is_array($SYS_ERROR)) {
		foreach($SYS_ERROR as $key => $val) {
			if (!$SYS_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$SYS_ERROR[$key]);
		}
	}

	return $ERROR;
}


/**
 * 学力診断　修正・削除処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function test_change() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA= array();

	//年リスト
	$L_YEAR = year_list();

	if (MODE == "test_view") {
		if ($_SESSION['t_practice']['test_type'] == 4) {
			$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
			if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
				$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
			}
			$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
			if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
				$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
			}
			$INSERT_DATA['test_name']	 	= $_POST['test_name'];
			$INSERT_DATA['book_id']		 	= $_POST['book_id'];
			$INSERT_DATA['kkn_from']	 	= $kkn_from;
			$INSERT_DATA['kkn_to']		 	= $kkn_to;
		}
		$INSERT_DATA['limit_time'] 		= $_POST['limit_time'];
		$INSERT_DATA['usr_bko'] 		= $_POST['usr_bko'];
//		$INSERT_DATA[upd_syr_id] 		= ;
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
	} elseif (MODE == "test_del") {
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";
	}
	$where = " WHERE default_test_num='".$_SESSION['t_practice']['default_test_num']."' LIMIT 1;";

	$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);

	if (MODE == "test_del") { unset($_SESSION['t_practice']['default_test_num']); }

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 問題一覧絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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
 * リストの為にPOSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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
 * 問題一覧(定期テスト／学力診断テスト)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function problem_list($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION['myid']);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }
	unset($_SESSION['sub_session']['select_course']);

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

//	$html .= "<button onclick=\"problem_botton_focus('problem_botton_".$_SESSION['focus_num']."');\">TEST</button>";

	$html .= "<br>\n";
	$html .= "インポートする場合は、問題設定csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
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
		if ($_SESSION['t_practice']['test_type'] == 1
		||($_SESSION['t_practice']['test_type'] == 4 && $_SESSION['t_practice']['default_test_num'])) {
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
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "を<input type=\"submit\" value=\"登録\">\n";
				$html .= "</form>\n";
			}
		}
	}

	$img_ftp = FTP_URL."test_img/";
	$voice_ftp = FTP_URL."test_voice/";
	$test_img_ftp = FTP_TEST_URL."test_img/".$_SESSION['t_practice']['course_num']."/";
	$test_voice_ftp = FTP_TEST_URL."test_voice/".$_SESSION['t_practice']['course_num']."/";
	$html .= "<br><br>\n";

	$html .= FTP_EXPLORER_MESSAGE; //add hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応

	if ($_SESSION['t_practice']['course_num']) {
		$html .= "<a href=\"".$test_img_ftp."\" target=\"_blank\">テンポラリー画像フォルダー($test_img_ftp)</a><br>\n";
		$html .= "<a href=\"".$test_voice_ftp."\" target=\"_blank\">テンポラリー音声フォルダー($test_voice_ftp)</a><br>\n";
	}
	$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
	$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br>\n";

//	if ($_SESSION['t_practice']['book_unit_id']) {
//		$where = " AND butp.book_unit_id='".$_SESSION['t_practice']['book_unit_id']."'";
//	}

	if ($_SESSION['t_practice']['course_num']) {
		$join_and .= " AND mb.course_num='".$_SESSION['t_practice']['course_num']."'";
		$where .= " AND mtdp.course_num='".$_SESSION['t_practice']['course_num']."'";		// add oda 2016/11/18 データ絞り込みにより速度UP
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

// echo $sql."<hr><br>";
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
// echo $sql."<br><br>";
	$cdb->exec_query($sql);
	// add start oda 2016/11/17 sort_orderが重複している場合、メッセージを表示する
	$duplicate_flag = false;
	$duplicate_message = "";
	// 定期テストのときに重複判断を行う
	if ($_SESSION['t_practice']['test_type'] == 1) {

		$sql  = "SELECT ".
			"*".
			" FROM test_problem_list".
			" ORDER BY default_test_num,disp_sort;";
		if ($result = $cdb->query($sql)) {
			$disp_sort = "";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($disp_sort == $list['disp_sort']) {
					$duplicate_flag = true;
					break;
				}
				$disp_sort = $list['disp_sort'];
			}
		}
		if ($duplicate_flag) {
			$duplicate_message = "<br><span style=\"color: red;\">Noが重複しています。登録した問題を確認して下さい。</span><br>\n";
		}
	}
	// add end oda 2016/11/17

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY default_test_num,disp_sort".
		$limit;
//echo $sql;
	if ($result = $cdb->query($sql)) {
		$html .= $duplicate_message;		// add oda 2016/11/17 sort_orderが重複している場合、メッセージを表示する
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
			// upd end hirose2020/12/15 テスト標準化開発 定期テスト
			// upd end hirose 2020/10/01 テスト標準化開発
			$html .= "</form>\n";
			$html .= "</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				if (!$list['default_test_num']) { $focus_default_test_num = ""; }
				else { $focus_default_test_num = $list['default_test_num']; }
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$list['default_test_num']."\">\n";
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
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"delete\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$list['default_test_num']."\">\n";
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


/**
 * SESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 */
function sub_session() {
	if (strlen($_POST['add_type'])) { $_SESSION['sub_session']['add_type']['add_type'] = $_POST['add_type']; }
	if (strlen($_POST['problem_type'])) { $_SESSION['sub_session']['add_type']['problem_type'] = $_POST['problem_type']; }

	return;
}


/**
 * 問題登録　テスト専用問題新規登録　フォームタイプ選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function select_form_type() {
	global $L_FORM_TYPE ,$L_DRAWING_TYPE;	//add hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE追加

//	unset($L_FORM_TYPE[14]);	// add hasegawa 2018/03/01 百マス計算										// del oda 2020/09/18 テスト標準化
	unset($L_FORM_TYPE[15]);	// add kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //「書写」
	// add start yamaguchi 2018/10/11 すらら英単語追加 すらら英単語専用フォームタイプ限定
	if($_SESSION['t_practice']['test_type'] == 6) {
		foreach($L_FORM_TYPE as $key => $val) {
			if( $key != 16 && $key != 17 ){
				unset($L_FORM_TYPE[$key]);
			}
		}
	}
	//add start kimura 2018/11/22 すらら英単語 _admin
	else{
		unset($L_FORM_TYPE[16]); //意味
		unset($L_FORM_TYPE[17]); //書く
	}
	//add end   kimura 2018/11/22 すらら英単語 _admin
	// add end yamaguchi 2018/10/11

	if (!$_SESSION['sub_session']['select_course']['form_type']) {
		//フォームタイプ
		$form_type_html = "<option value=\"0\">選択して下さい</option>\n";
		foreach($L_FORM_TYPE as $key => $val) {
			if ($_SESSION['sub_session']['select_course']['form_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
			$form_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
		$select_menu = "<td>\n";
		$select_menu .= "<select name=\"form_type\" onchange=\"submit();\">".$form_type_html."</select>\n";
		$select_menu .= "</td>\n";

		$msg_html .= "登録する問題のフォームタイプを選択してください。";
	} else {
		$select_menu = "<td>".$L_FORM_TYPE[$_SESSION['sub_session']['select_course']['form_type']]."</td>\n";
	}

	// add hasegawa 2016/06/01 作図ツール
	$sel_drawing_menu="";
	if($_SESSION['sub_session']['select_course']['form_type'] == 13){

		$sel_drawing_menu = "<tr class=\"unit_form_menu\">\n";
		$sel_drawing_menu .= "<td>問題種類</td>\n";
		$sel_drawing_menu .= "</tr>\n";
		$sel_drawing_menu .= "<tr class=\"unit_form_cell\">\n";

		if(!$_POST['drawing_type']){
			$sel_drawing_html = "<option value=\"0\">選択して下さい</option>\n";
			foreach($L_DRAWING_TYPE as $key2 => $val2) {
				// update start 2016/08/30 yoshizawa 作図ツール
				//if ($_POST['drawing_type'] == $key2) { $selected2 = "selected"; } else { $selected2 = ""; }
				//$sel_drawing_html .= "<option value=\"".$key2."\" ".$selected2.">".$val2."</option>\n";
				//
				if( $key2 == 1 || $key2 == 2 ){ $disabled = "disabled=\"disabled\""; }else{ $disabled = ""; }
				if ($_POST['drawing_type'] == $key2) { $selected2 = "selected"; } else { $selected2 = ""; }
				$sel_drawing_html .= "<option value=\"".$key2."\" ".$selected2." ".$disabled.">".$val2."</option>\n";
				// update end 2016/08/30 yoshizawa 作図ツール
			}

			$sel_drawing_menu .= "<td>\n";
			$sel_drawing_menu .= "<select name=\"drawing_type\" onchange=\"submit();\">".$sel_drawing_html."</select>\n";
			$sel_drawing_menu .= "</td>\n";

			$msg_html .= "登録する作図の問題種類を選択してください。";
		}else{
			$sel_drawing_menu .= "<td>".$L_DRAWING_TYPE[$_POST['drawing_type']]."</td>\n";
		}

		$sel_drawing_menu .= "</tr>\n";

	}
	// add end hasegawa 2016/06/01

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
	$html .= $sel_drawing_menu;	// add hasegawa 2016/06/01 作図ツール
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
 * form_typeの為POSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 */
function sub_form_type_session() {
	if (strlen($_POST['form_type'])) { $_SESSION['sub_session']['select_course']['form_type'] = $_POST['form_type']; }

	return;
}


/**
 * 問題登録　テスト専用問題新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function test_add_addform($ERROR) {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE,$L_DRAWING_TYPE;		// upd hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= select_form_type();

	if (!$_SESSION['sub_session']['select_course']['form_type'] || ($_SESSION['sub_session']['select_course']['form_type'] == 13 && !$_POST['drawing_type'])) {	// upd hasegawa 2016/06/01 作図ツール 判断追加
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"problem_check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

//form_type 1 2 3 4 5 8 10 11 13
	$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);

	$book_unit_att = "<br><span style=\"color:red;\">※設定したい教科書単元のＩＤを入力して下さい。";
//	$book_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$book_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を入力して下さい。";
//	$lms_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$lms_unit_att .= "<br>※複数の教科書単元ＩＤが設定されている場合、紐づくLMS単元を「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$lms_unit_att .= "<br>※一つの教科書単元ＩＤに複数LMS単元を設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$upnavi_section_att = "<br><span style=\"color:red;\">※設定したい学力Upナビ子単元のＩＤを入力して下さい。";	//	add koike 2012/06/12
	$upnavi_section_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add koike 2012/06/12
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if ($_SESSION['t_practice']['test_type'] == 4) {
		if ($_SESSION['t_practice']['default_test_num']) {
			list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
		} else {
			$default_test_num = 0;
		}
		$default_test_num_html = "<tr>\n";
		$default_test_num_html .= "<td class=\"member_form_menu\">テストID</td>\n";
		$default_test_num_html .= "<td class=\"member_form_cell\">".$default_test_num."</td>\n";
		$default_test_num_html .= "</tr>\n";
		$INPUTS['DEFAULTTESTNUM'] 	= array('result'=>'plane','value'=>$default_test_num_html);
	}

	// add start hasegawa 2016/05/01 作図ツール
	if($_SESSION['sub_session']['select_course']['form_type'] ==13 && $_POST['drawing_type']){
		$html .= "<input type=\"hidden\" name=\"drawing_type\" value=\"".$_POST['drawing_type']."\">\n";
		$INPUTS['DRAWINGTYPE'] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$_POST['drawing_type']]);
	}
	// add end hasegawa 2016/05/01

	$INPUTS['PROBLEMNUM'] 		= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['PROBLEMTYPE'] 		= array('result'=>'plane','value'=>'----');
	$INPUTS['FORMTYPE'] 		= array('result'=>'plane','value'=>$L_FORM_TYPE[$_SESSION['sub_session']['select_course']['form_type']]);
	$INPUTS['QUESTION'] 		= array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$_POST['question']);
	$INPUTS['PROBLEM'] 			= array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$_POST['problem']);
	$INPUTS['VOICEDATA'] 		= array('type'=>'text','name'=>'voice_data','size'=>'50','value'=>$_POST['voice_data']);
	$INPUTS['HINT'] 			= array('type'=>'textarea','name'=>'hint','cols'=>'50','rows'=>'5','value'=>$_POST['hint']);
	$INPUTS['EXPLANATION'] 		= array('type'=>'textarea','name'=>'explanation','cols'=>'50','rows'=>'5','value'=>$_POST['explanation']);
	$INPUTS['PARAMETER'] 		= array('type'=>'text','name'=>'parameter','size'=>'50','value'=>$_POST['parameter']);
	$INPUTS['FIRSTPROBLEM'] 	= array('type'=>'textarea','name'=>'first_problem','cols'=>'50','rows'=>'5','value'=>$_POST['first_problem']);
	$INPUTS['LATTERPROBLEM'] 	= array('type'=>'textarea','name'=>'latter_problem','cols'=>'50','rows'=>'5','value'=>$_POST['latter_problem']);

//	if ($form_type != 4 && $form_type != 8 && $form_type != 10) {	// upd hasegawa form_type 取得できてなかったので変更
	if ($_SESSION['sub_session']['select_course']['form_type'] != 4 && $_SESSION['sub_session']['select_course']['form_type'] != 8 && $_SESSION['sub_session']['select_course']['form_type'] != 10) {
		$INPUTS['SELECTIONWORDS'] = array('type'=>'text','name'=>'selection_words','size'=>'50','value'=>$_POST['selection_words']);
	} else {
		$INPUTS['SELECTIONWORDS'] = array('type'=>'textarea','name'=>'selection_words','cols'=>'50','rows'=>'5','value'=>$_POST['selection_words']);
	}
	$INPUTS['CORRECT'] 		= array('type'=>'text','name'=>'correct','size'=>'50','value'=>$_POST['correct']);
	$INPUTS['OPTION1'] 		= array('type'=>'text','name'=>'option1','size'=>'50','value'=>$_POST['option1']);
//	$INPUTS[OPTION2] 		= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$_POST['option2']);	// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加 下に移動
	$INPUTS['OPTION3'] 		= array('type'=>'text','name'=>'option3','size'=>'50','value'=>$_POST['option3']);
	$INPUTS['OPTION4'] 		= array('type'=>'text','name'=>'option4','size'=>'50','value'=>$_POST['option4']);
//	$INPUTS[OPTION5] 		= array('type'=>'text','name'=>'option5','size'=>'50','value'=>$_POST['option5']);	// del hasegawa 2016/09/29 国語文字数カウント 下に移動
	// add start hasegawa 2016/09/29 国語文字数カウント
	// form_type == 3 && write_type == 2の場合はOPTION5をCOUNTSETTINGに
	if($_SESSION['sub_session']['select_course']['form_type'] == 3) {

		// ---- add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// 解答欄行数
		$INPUTS['INPUTROW'] = array('type'=>'text','name'=>'input_row','value'=>$_POST['input_row'],'size'=>'50');
		// 解答欄サイズ
		$INPUTS['INPUTSIZE'] = array('type'=>'text','name'=>'input_size','value'=>$_POST['input_size'],'size'=>'50');
		$input_size_att = "<br><span style=\"color:red;\">※指定する場合は、最大40までの値を設定してください。</span>";
		$INPUTS['INPUTSIZEATT'] = array('result'=>'plane','value'=>$input_size_att);
		// ---- add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// if($_SESSION['t_practice']['write_type'] == 2) {												// del 2018/05/14 yoshizawa 理科社会対応
		if($_SESSION['t_practice']['write_type'] == 2 || $_SESSION['t_practice']['write_type'] == 16) {	// add 2018/05/14 yoshizawa 理科社会対応

			$option5 = $_POST['option5'];
			if ($option5== "") { $option5 = "false"; }
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_off");
			$newform->set_form_check($option5);
			$newform->set_form_value("false");
			$count_set_off = $newform->make();
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_on");
			$newform->set_form_check($option5);
			$newform->set_form_value("true");
			$count_set_on = $newform->make();

			$count_html = "<tr>\n";
			$count_html .= "<td class=\"member_form_menu\">文字数カウント設定</td>\n";
			$count_html .= "<td class=\"member_form_cell\">\n";
			$count_html .= $count_set_off . "<label for=\"count_set_off\">設定しない</label> / " . $count_set_on . "<label for=\"count_set_on\">設定する</label>";
			$count_html .= "</td>\n";
			$count_html .= "</tr>\n";
			$INPUTS['COUNTSETTING'] = array('result'=>'plane','value'=>$count_html);
		}
	} else {
		$INPUTS['OPTION2'] 		= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$_POST['option2']);	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加 下に移動
		$INPUTS['OPTION5'] 		= array('type'=>'text','name'=>'option5','size'=>'50','value'=>$_POST['option5']);
	}
	// add start hasegawa 2016/09/29
	// add start karasawa 2019/07/22 BUD英語解析開発
	// upd start hirose 2020/09/18 テスト標準化開発
	// if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10){
	if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10 || $_SESSION['sub_session']['select_course']['form_type'] == 14){
	// upd end hirose 2020/09/18 テスト標準化開発
		if ($option3 == "") { $option3 = "0"; }
		$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
		$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
		$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);
	}
	// add end karasawa 2019/07/22 BUD英語解析開発

		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$INPUTS['STANDARDTIME'] 	= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
		if ($_SESSION['t_practice']['test_type'] != 4) {
			$INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
		}else{
			$standard_time_html = "none_display";
			$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

		}
		//upd end 2018/06/06 yamaguchi
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('type'=>'text','name'=>'problem_point','size'=>'5','value'=>$_POST['problem_point']);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
	$INPUTS['BOOKUNITID'] 	= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$_POST['book_unit_id']);
	$INPUTS['BOOKUNITATT'] 	= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITID'] 	= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']);
//	$INPUTS[LMSUNITATT] 	= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITATT'] 	= array('result'=>'plane','value'=>$lms_unit_att);	// 2012/03/13 add ozaki
//	$INPUTS[UPNAVISECTIONID] 	= array('type'=>'text','name'=>'upnavi_section_id','size'=>'50','value'=>$_POST['upnavi_section_id']);		//	add koike 2012/06/12
	$INPUTS['UPNAVISECTIONNUM'] 	= array('type'=>'text','name'=>'upnavi_section_num','size'=>'50','value'=>$_POST['upnavi_section_num']);	//	update oda 2012/07/05
	$INPUTS['UPNAVISECTIONATT'] 	= array('result'=>'plane','value'=>$upnavi_section_att);	//	add koike 2012/06/12

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
function test_add_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['parameter'] && ereg("\[([^0-9])\]",$_POST['parameter'])) { $ERROR[] = "パラメーターが不正です。"; }

	// del start hasegawa 2016/09/29 write_typeはSESSIONに持つことにしたので、不要ロジック削除
	//	add ookawara 2013/10/30
	//	複数テキストボックスのエラーチェックでwrite_typeが必要な為取得・設定
/*
	if (!defined('write_type') && $_SESSION['course']['course_num'] > 0) {
		$write_type = 0;
		$sql  = "SELECT write_type FROM ".T_COURSE.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$write_type = $list['write_type'];
		}
		if ($write_type > 0) {
			define('write_type', $write_type);
		}
	}
*/	// del end hasegawa 2016/09/29

	//フォームタイプ別の項目チェック
	$array_replace = new array_replace();
	if ($_SESSION['sub_session']['select_course']['form_type'] == 1) {

		//if ($_POST['selection_words'] && $_POST['selection_words'] === "0") {		// del oda 2014/08/11 課題要望一覧No324
		if ($_POST['selection_words']) {											// add oda 2014/08/11 課題要望一覧No324
			$max_column = $selection_words_num = $array_replace->set_line($_POST['selection_words']);
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		} else {
			$correct_num = $array_replace->set_line($_POST['correct']);
			$correct = $array_replace->replace_line();
		}

		if (!$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "選択語句が確認できません。";
		} else {
			$option1_num = $array_replace->set_line($_POST['option1']);
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

		if ($_POST['option2'] == "") { $_POST['option2'] ="0"; }
		// update start oda 2014/08/11 課題要望一覧No324 0と1以外はエラーとする(100などを設定するとエラーにならない為です)
		//if (ereg("[^0-1]",$_POST['option2'])) { $ERROR[] = "シャッフル情報が不正です。"; }
		if ($_POST['option2'] !== "0" && $_POST['option2'] !== "1") {
			$ERROR[] = "シャッフル情報が不正です。";
		}
		// update end oda 2014/08/11

		if ($_POST['option3'] == "") { $_POST['option3'] = "0"; }
		if (ereg("[^0-9]",$_POST['option3']) || $_POST['option3'] == 1) { $ERROR[] = "選択項目数情報が不正です。"; }

		//if ($max_column > 1) {	// del oda 2014/08/11 課題要望一覧No324
		if ($max_column > 0) {		// add oda 2014/08/11 課題要望一覧No324 判断条件修正
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = "出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
			}
		}

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 2) {
		if ($_POST['selection_words']) {
			$max_column = $selection_words_num = $array_replace->set_line($_POST['selection_words']);
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		} else {
			$correct_num = $array_replace->set_line($_POST['correct']);
			$correct = $array_replace->replace_line();
		}

		if (!$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "選択語句が確認できません。";
		} else {
			$option1_num = $array_replace->set_line($_POST['option1']);
			$option1 = $array_replace->replace_line();

			$L_CORRECT = explode("\n",$correct);
			$L_OPTION1 = explode("\n",$option1);
			if ($L_CORRECT) {
				foreach($L_CORRECT as $key => $val) {
					if (preg_match("/&lt;&gt;/",$val)) {
						foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
					} elseif (preg_match("/<>/",$val)) {
						foreach (explode("<>",$val) as $word) { $L_ANS[] = trim($word); }
					} else {
						$L_ANS[] = trim($val);
					}
				}
			}
			if ($L_OPTION1) {
				foreach($L_OPTION1 as $key => $val) {
					if (preg_match("/&lt;&gt;/",$val)) {
						foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS1[] = trim($word); }
					} elseif (preg_match("/<>/",$val)) {
						foreach (explode("<>",$val) as $word) { $L_ANS1[] = trim($word); }
					} else {
						$L_ANS1[] = trim($val);
					}
					$hit = array_search($L_ANS[$key],$L_ANS1);
					if($hit === FALSE) {
						$ERROR[] = "選択語句内に正解が含まれておりません。";
						break;
					}
				}
			}
		}

		if ($_POST['option2'] == "") { $_POST['option2'] ="0"; }
		// update start oda 2014/08/11 課題要望一覧No324 0と1以外はエラーとする(100などを設定するとエラーにならない為です)
		//if (ereg("[^0-1]",$_POST['option2'])) { $ERROR[] = "シャッフル情報が不正です。"; }
		if ($_POST['option2'] !== "0" && $_POST['option2'] !== "1") {
			$ERROR[] = "シャッフル情報が不正です。";
		}
		// update end oda 2014/08/11

		//if ($max_column > 1) {	// del oda 2014/08/11 課題要望一覧No324
		if ($max_column > 0) {		// add oda 2014/08/11 課題要望一覧No324 判断条件修正
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = "出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
			}
		}

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 3) {

		if (!$_POST['correct'] && $_POST['correct'] !== "0" && !$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "正解、又はBUD正解が確認できません。";
			$ERROR[] = "正解、又はBUD正解が確認できません。";
		}

		if ($_POST['input_size'] || $_POST['input_size'] == 0) {	// add hasegawa 2016/11/14 入力フォームサイズ指定項目追加

			$L_INPUT_SIZE = array();
			$L_INPUT_SIZE = explode('//',$_POST['input_size']);
			$input_size_err_flg = 0;
			if(is_array($L_INPUT_SIZE)) {
				foreach($L_INPUT_SIZE as $input_size) {
					if ($input_size != "" && !($input_size > 0 && $input_size <= 40)) {
						$input_size_err_flg = 1;
					}
				}
			}
			if($input_size_err_flg == 1) {
				$ERROR[] = "解答欄サイズ(文字数)の値が不正です。";
			}
		}

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($_POST['option4']) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($_POST['option4']);
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

				if ($use_v1 && $use_v2) { $ERROR[] = "「手書き認識設定」が不正です。"; }
			}
		}
		// add end hasegawa 2018/05/15


	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 4) {
		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") { $ERROR[] = "問題テキストが確認できません。"; }

		if (!$_POST['correct'] && $_POST['correct'] !== "0" && !$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "正解、又はBUD正解が確認できません。";
			$ERROR[] = "正解、又はBUD正解が確認できません。";
		//	add ookawara 2013/10/30 start
		} elseif (!$ERROR) {
			include(LOG_DIR . "problem_lib/bud.php");
			$selection_words = $_POST['selection_words'];
			$option1 = $_POST['option1'];
			if ($option1 === "") {
				$option1 = $_POST['correct'];
			}

			if (!defined("form_type")) {
				define("form_type", 4);
			}

			$selection_words = preg_replace("/&lt;/", "<", $selection_words);
			$selection_words = preg_replace("/&gt;/", ">", $selection_words);
			$option1 = preg_replace("/&lt;/", "<", $option1);
			$option1 = preg_replace("/&gt;/", ">", $option1);

			$true_flg = 0;
//			$write_type = write_type;				// del hasegawa 2016/09/29 write_typeはSESSIONから取得
			$write_type = $_SESSION['t_practice']['write_type'];	// add hasegawa 2016/09/29
			$word_array = new make_word_array();
			$true_flg = $word_array->make_sel_word($selection_words, $option1, $write_type);
			if ($true_flg != 1) {
				$ERROR[] = "入力内容に不具合が有り正解を導き出せません。問題テキスト、正解、又はBUD正解を確認してください。";
			}
		//	add ookawara 2013/10/30 end
		}

		if ($_POST['option2'] == "") { $_POST['option2'] = "0"; }
		include_once("../../_www/problem_lib/space_checker.php");
		if (preg_match("/[^0-9]/",$_POST['option2']) || $_POST['option2'] > space_Checker::space_Decision($selection_words)) { $ERROR[] = "空白数が不正です。"; }  //upd 2017/04/10 yamaguchi 空白数の入力制限

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($_POST['option4']) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($_POST['option4']);
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

				if ($use_v1 && $use_v2) { $ERROR[] = "「手書き認識設定」が不正です。"; }
			}
		}
		// add end hasegawa 2018/05/15


	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 5) {
		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		}

		if ($_POST['option1'] == "") { $_POST['option1'] = "1"; }
		if (ereg("[^0-1]",$_POST['option1'])) { $ERROR[] = "解答ライン数が不正です。"; }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 8) {
		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") {
			$ERROR[] = "問題テキストが確認できません。";
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		}

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 10) {
		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") {
			$ERROR[] = "問題テキストが確認できません。";
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0" && !$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "正解、又はBUD正解が確認できません。";
			$ERROR[] = "正解、又はBUD正解が確認できません。";
		}

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($_POST['option4']) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($_POST['option4']);
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

				if ($use_v1 && $use_v2) { $ERROR[] = "「手書き認識設定」が不正です。"; }
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 11) {
		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") {
			$ERROR[] = "問題テキストが確認できません。";
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		}
	// add start hasegawa 2016/06/01 作図ツール
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 13) {

		if (!$_POST['drawing_type']) {
			$ERROR[] = "問題種類が確認できません";
		}

		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") {
			$ERROR[] = "問題テキストが確認できません。";
		}

		if (!$_POST['correct'] && $_POST['correct'] !== "0") {
			$ERROR[] = "正解が確認できません。";
		}

		if (!$_POST['option2']) {
			$ERROR[] = "作図問題パラメータが確認できません。";
		}
	// add start hirose 2020/09/18 テスト標準化開発
	//百ます計算
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 14) {
		
		if (!$_POST['selection_words'] && $_POST['selection_words'] !== "0") {
			$ERROR[] = "問題テキストが確認できません。";
		}
		if (!$_POST['correct'] && $_POST['correct'] !== "0" && !$_POST['option1'] && $_POST['option1'] !== "0") {
			$ERROR[] = "正解、又はBUD正解が確認できません。";
		}
	// add end hirose 2020/09/18 テスト標準化開発

	}
	// add end hasegawa 2016/06/01

	if ($_SESSION['t_practice']['test_type'] == "4") {
		if (!$_POST['problem_point']) { $ERROR[] = "配点が未入力です。"; }
		else {
			if (preg_match("/[^0-9]/",$_POST['problem_point'])) {
				$ERROR[] = "配点は半角数字で入力してください。";
			} elseif ($_POST['problem_point'] > 100) {
				$ERROR[] = "配点は100以下で入力してください。";
			}
		}
	}
	if ($_POST['standard_time']) {
		if (preg_match("/[^0-9]/",$_POST['standard_time'])) {
			$ERROR[] = "回答目安時間は半角数字で入力してください。";
		}
	}
	if ($_POST['book_unit_id']) {
		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:]/",$_POST['book_unit_id'])) {
			$ERROR[] = "教科書単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		$where = "";
		if ($_SESSION['t_practice']['core_code']) {
			list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
			if ($bnr_cd == "C000000001") {
//				$where = " AND term3_bnr_ccd='".$bnr_cd."' AND term3_kmk_ccd='".$kmk_cd."'";
			} elseif ($bnr_cd == "C000000002") {
//				$where = " AND term2_bnr_ccd='".$bnr_cd."' AND term2_kmk_ccd='".$kmk_cd."'";
			}
		}
		$L_BOOK_UNIT = explode("::",$_POST['book_unit_id']);
		$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";
		$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
				" WHERE mk_flg='0'".
//				" AND unit_end_flg='1'".
				" AND book_unit_id IN (".$in_book_unit_id.")".
//				" AND book_id='".$_SESSION['t_practice']['book_id']."'".
				$where.
				";";
//echo $sql."<br>";
		if ($result = $cdb->query($sql)) {
			$book_unit_count = $cdb->num_rows($result);
		}

		//入力した単元と存在している単元の数が違っていた場合エラー
		if ($book_unit_count != count($L_BOOK_UNIT)) {
			$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
		}

		// add start oda 2014/10/14 課題要望一覧No352 登録／修正時のチェック処理が抜けていたので追加
		// 教科書単元のコースが異なる場合はエラーとする
		$sql  = "SELECT mbu.book_unit_id, mb.course_num FROM " . T_MS_BOOK_UNIT ." mbu ".
				" INNER JOIN ".T_MS_BOOK. " mb ON mbu.book_id = mb.book_id AND mb.display = '1' AND mb.mk_flg = '0' ".
				" WHERE mbu.mk_flg='0'".
				"   AND mbu.book_unit_id IN (".$in_book_unit_id.")".
				";";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['course_num'] != $list['course_num']) {
					$ERROR[] = "設定されている教科書単元のコースが異なります。教科書単元ID = ".$list['book_unit_id'];
				}
			}
		}
		// add end oda 2014/10/14
	}

	if ($_POST['lms_unit_id']) {
		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:|&lt;&gt;|<>]/",$_POST['lms_unit_id'])) {
				$ERROR[] = "LMS単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		$L_LMS_UNIT = explode("::",$_POST['lms_unit_id']);
		foreach ($L_LMS_UNIT as $val) {
			if (!$val) { continue; }	// 2012/03/13 add ozaki

			unset($unit_num);	//	add 2015/01/06 yoshizawa 課題要望一覧No.405対応

			if (preg_match("/&lt;&gt;/",$val)) {
				$unit_num = explode("&lt;&gt;",$val);
			} elseif (preg_match("/<>/",$val)) {
				$unit_num = explode("<>",$val);
			} else {
				$unit_num[] = $val;
			}

			$in_lms_unit_id = "'".implode("','",$unit_num)."'";
			$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				" AND unit_num IN (".$in_lms_unit_id.")";
//echo $sql."<br>";
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}

			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($unit_num)) {
				$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}
		}
	}

	/*
	//	del ookawara 2012/03/15	教科書単元を設定してもLMS単元を登録しない場合が有る為
	// 2012/02/27 add ozaki
	if (count($L_BOOK_UNIT) != count($L_LMS_UNIT)) {
		$ERROR[] = "教科書単元とLMS単元の設定数が異なります。";
	}
	*/

	//	add koike 2012/06/14 start
	//if ($_POST['upnavi_section_num']) {		//	del ookawara 2012/11/27
	if ($_POST['upnavi_section_num'] != "") {	//	add ookawara 2012/11/27
		$in_upnavi_section_num = "";
		$_POST['upnavi_section_num'] = mb_convert_kana($_POST['upnavi_section_num'], "as", "UTF-8");
		$L_UPNAVI_SECTION = explode("::",$_POST['upnavi_section_num']);

		//	add ookawara 2012/11/27 start
		//	親単元毎に確認
		foreach ($L_UPNAVI_SECTION as $key => $val) {

			$val = trim($val);
			if ($val == "") {
				continue;
			}

			if ($val == "0") {
				$ERROR[] = "学力Upナビ子単元で「0」が設定されています。";
				break;
			}

			$in_upnavi_section_num = "";
			if (preg_match("/&lt;&gt;/",$val)) {
				$in_upnavi_section_num = preg_replace("/&lt;&gt;/", ",", $val);
			} elseif (preg_match("/<>/",$val)) {
				$in_upnavi_section_num = preg_replace("/<>/", ",", $val);
			} else {
				$in_upnavi_section_num = trim($val);
			}
			$USN_L = array();
			$USN_L = explode(",", $in_upnavi_section_num);

			$upnavi_section_count = 0;
			$usn_cnt = 0;
			$sql  = "SELECT upnavi_chapter_num, count(upnavi_section_num) AS usn_cnt FROM " . T_UPNAVI_SECTION .
					" WHERE mk_flg='0'".
					" AND course_num = '".$_SESSION['t_practice']['course_num']."'".
					" AND upnavi_section_num IN (".$in_upnavi_section_num.")".
					" GROUP BY upnavi_chapter_num;";
			if ($result = $cdb->query($sql)) {
				$upnavi_section_count = $cdb->num_rows($result);
				$list = $cdb->fetch_assoc($result);
				$usn_cnt = $list['usn_cnt'];
			}
			//親単元が違う場合、入力した単元数と存在している単元数が違っていた場合エラー
			if ($upnavi_section_count != 1 || $usn_cnt != count($USN_L)) {
				$ERROR[] = "学力Upナビ子単元で存在しない子単元の設定、またはコースが異なる子単元、または同じ子単元の重複登録しようとしています。";
				break;
			}
		}
		//	add ookawara 2012/11/27 end

	}
	//	add koike 2012/06/14 end

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
function test_add_check_html() {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;	// upd hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	//add okabe start 2018/09/05	理科問題の全角文字入力への対応。
	$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
	$sql  = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
	$tmp_write_type = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$tmp_write_type = $list['write_type'];
	}
	//add okabe end 2018/09/05	理科問題の全角文字入力への対応。

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "add") { $val = "problem_add"; }
				elseif (MODE == "view") { $val = "change"; }
			}
			//$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";	//del okabe 2018/09/05	理科問題の全角文字対応の際に、テスト問題の入力フォームから扱うときに半角変換していないことを発見。変換するように修正。
			//add okabe start 2018/09/05	理科問題の全角文字対応の際に、テスト問題の入力フォームから扱うときに半角変換していないことを発見。変換するように修正。
			if ($tmp_write_type == 15) {
				if ($key == "question" || $key == "problem" || $key == "hint" || $key == "explanation" || $key == "first_problem" || $key == "latter_problem") {
					$valx = $val;
				} else {
					$valx = mb_convert_kana($val, "asKV");
				}
			} else {
				$valx = mb_convert_kana($val, "asKV");
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$valx."\">\n";
			//add okabe end 2018/09/05
		}
	}
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}

	if (ACTION) {
		//foreach ($_POST as $key => $val) { $$key = $val; }	//del okabe 2018/09/05	理科問題の全角文字対応の際に、テスト問題の入力フォームから扱うときに半角変換していないことを発見。変換するように修正。
		//add okabe start 2018/09/05	理科問題の全角文字対応の際に、テスト問題の入力フォームから扱うときに半角変換していないことを発見。変換するように修正。
		foreach ($_POST as $key => $val) {
			if ($tmp_write_type == 15) {
				if ($key == "question" || $key == "problem" || $key == "hint" || $key == "explanation" || $key == "first_problem" || $key == "latter_problem") {
					$valx = $val;
				} else {
					$valx = mb_convert_kana($val, "asKV");
				}
			} else {
				$valx = mb_convert_kana($val, "asKV");
			}
			$$key = $valx;
		}
		//add okabe end 2018/09/05
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql  = "SELECT *" .
			" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=ms_tp.problem_num".
			" AND ms_tdp.default_test_num='".$default_test_num."'".
			" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
			" WHERE ms_tp.mk_flg='0'".
			" AND ms_tp.problem_num='".$_POST['problem_num']."' LIMIT 1";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$val = str_replace("\n","//",$val);		// add hasegawa 2016/10/25
			$val = str_replace("&nbsp;"," ",$val);		// add hasegawa 2016/10/25
			$$key = replace_decode($val);
		}
		$_SESSION['sub_session']['select_course']['form_type'] = $form_type;
		$sql  = "SELECT * FROM " .
			T_BOOK_UNIT_TEST_PROBLEM . " butp".
			" WHERE butp.mk_flg='0'".
			" AND butp.default_test_num='".$default_test_num."'".
			" AND butp.problem_table_type='".$_POST['problem_table_type']."'".
			" AND butp.problem_num='".$_POST['problem_num']."'".
			" ORDER BY butp.book_unit_id";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_BOOK_UNIT = array();
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['book_unit_id']) { $L_BOOK_UNIT[] = $list['book_unit_id']; }
			}
		}
		$book_unit_id = "";
		if (is_array($L_BOOK_UNIT)) {
			$book_unit_id = implode("::",$L_BOOK_UNIT);
		//	$HIDDEN .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$book_unit_id."\">\n";
		}
		$sql  = "SELECT bulu.* FROM " .
				T_BOOK_UNIT_LMS_UNIT . " bulu".
				" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM . " butp ON ".
					" bulu.book_unit_id = butp.book_unit_id".
					" AND bulu.problem_num = butp.problem_num".
					" AND bulu.problem_table_type = butp.problem_table_type".
					" AND butp.mk_flg=0".
					" AND butp.default_test_num='".$default_test_num."'".
				" WHERE bulu.mk_flg='0'".
				" AND bulu.problem_table_type='".$_POST['problem_table_type']."'".
				" AND bulu.problem_num='".$_POST['problem_num']."'".
				" ORDER BY bulu.book_unit_id,bulu.unit_num";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_UNIT_NUM = array();
			while ($list = $cdb->fetch_assoc($result)) {
//				if ($list['unit_num']) { $L_UNIT_NUM[] = $list['unit_num']; }
				if ($list['unit_num']) { $L_LMS_UNIT[$list['book_unit_id']][] .= $list['unit_num']; }
			}
		}
		$lms_unit_id = "";
		if (is_array($L_LMS_UNIT)) {
			$i = 0;
			foreach ($L_LMS_UNIT as $key => $val) {
				if ($i > 0) { $lms_unit_id .= "::"; }
				$lms_unit_id .= implode("<>",$val);
				$i = 1;
			}
		//	$HIDDEN .= "<input type=\"hidden\" name=\"lms_unit_id\" value=\"".$lms_unit_id."\">\n";
		}
//	add koike 2012/06/18 start
//  update start 2012/07/05 oda
//		$sql = "SELECT usp.`upnavi_section_id` FROM " .
//				T_UPNAVI_SECTION_PROBLEM . " usp".
//				" WHERE usp.problem_num='".$_POST['problem_num']."'".
//				" AND usp.mk_flg = '0';";
		$sql = "SELECT " .
				" usp.upnavi_section_num, ".
				" us.upnavi_chapter_num ".
				" FROM " . T_UPNAVI_SECTION_PROBLEM . " usp".
				" INNER JOIN ".T_UPNAVI_SECTION." us ON usp.upnavi_section_num = us.upnavi_section_num AND us.mk_flg = '0' ".
				" WHERE usp.problem_num='".$_POST['problem_num']."'".
				" AND usp.problem_table_type='".$_POST['problem_table_type']."'".		// 2012/07/26 add oda
				" AND usp.mk_flg = '0'".
				" ORDER BY usp.upnavi_section_num, us.upnavi_chapter_num;";	//	add ookawara 2012/11/27
//echo $sql."<br>\n";
// update end 2012/07/05 oda
			if ($result = $cdb->query($sql)) {
				$L_UPNAVI_SECTION = array();
				while ($list = $cdb->fetch_assoc($result)) {
					if($list['upnavi_section_num']) {$L_UPNAVI_SECTION[] .= $list['upnavi_section_num'];}
				}
			}
			$upnavi_section_num = "";
			if ($L_UPNAVI_SECTION) {
				$upnavi_section_num = implode("::",$L_UPNAVI_SECTION);
			}
//	add koike 2012/06/18 end

	}

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['PROBLEMNUM'] 	= array('result'=>'plane','value'=>$_POST['problem_num']);
	if ($_SESSION['t_practice']['test_type'] == 4) {
		if ($_SESSION['t_practice']['default_test_num']) {
			list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
			$_POST['default_test_num'] = $default_test_num;
		}
		$default_test_num_html = "<tr>\n";
		$default_test_num_html .= "<td class=\"member_form_menu\">テストID</td>\n";
		$default_test_num_html .= "<td class=\"member_form_cell\">".$_POST['default_test_num']."</td>\n";
		$default_test_num_html .= "</tr>\n";
		$INPUTS['DEFAULTTESTNUM'] 	= array('result'=>'plane','value'=>$default_test_num_html);
	}
	$INPUTS['PROBLEMNUM']		= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['PROBLEMTYPE']		= array('result'=>'plane','value'=>'----');
	$INPUTS['FORMTYPE']		= array('result'=>'plane','value'=>$L_FORM_TYPE[$_SESSION['sub_session']['select_course']['form_type']]);
	$INPUTS['DRAWINGTYPE'] 		= array('result'=>'plane','value'=>$L_DRAWING_TYPE[$_POST['drawing_type']]);	// add hasegawa 2016/06/01 作図ツール
	$INPUTS['QUESTION']		= array('result'=>'plane','value'=>nl2br($question));
	$INPUTS['PROBLEM']		= array('result'=>'plane','value'=>nl2br($problem));
	$INPUTS['VOICEDATA']		= array('result'=>'plane','value'=>$voice_data);
	$INPUTS['HINT']			= array('result'=>'plane','value'=>nl2br($hint));
	$INPUTS['EXPLANATION']		= array('result'=>'plane','value'=>nl2br($explanation));
	$INPUTS['PARAMETER']		= array('result'=>'plane','value'=>$parameter);
	$INPUTS['FIRSTPROBLEM']		= array('result'=>'plane','value'=>nl2br($first_problem));
	$INPUTS['LATTERPROBLEM']	= array('result'=>'plane','value'=>nl2br($latter_problem));
	$INPUTS['SELECTIONWORDS']	= array('result'=>'plane','value'=>$selection_words);
	$INPUTS['CORRECT']		= array('result'=>'plane','value'=>$correct);
	$INPUTS['OPTION1']		= array('result'=>'plane','value'=>$option1);
//	$INPUTS['OPTION2']		= array('result'=>'plane','value'=>$option2);	// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加
	$INPUTS['OPTION3']		= array('result'=>'plane','value'=>$option3);
	$INPUTS['OPTION4']		= array('result'=>'plane','value'=>$option4);
//	$INPUTS['OPTION5']		= array('result'=>'plane','value'=>$option5);	// del hasegawa 2016/09/29 国語文字数カウント

	// add start hasegawa 2016/09/29 国語文字数カウント
	// form_type == 3 && write_type == 2の場合はOPTION5をCOUNTSETTINGに
	if($_SESSION['sub_session']['select_course']['form_type'] == 3) {

		// add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		if($option2) {
			list($input_row,$input_size) = get_option2($option2);
		}

		// 解答欄行数
		$INPUTS['INPUTROW'] = array('result'=>'plane','value'=>$input_row);
		// 解答欄サイズ
		$INPUTS['INPUTSIZE'] = array('result'=>'plane','value'=>$input_size);

		// add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加

		// if($_SESSION['t_practice']['write_type'] == 2) {												// del 2018/05/14 yoshizawa 理科社会対応
		if($_SESSION['t_practice']['write_type'] == 2 || $_SESSION['t_practice']['write_type'] == 16) {	// add 2018/05/14 yoshizawa 理科社会対応

			$count_set = "設定しない";
			if($option5 == "true") {
				$count_set = "設定する";
			}
			$count_html = "<tr>\n";
			$count_html .= "<td class=\"member_form_menu\">文字数カウント設定</td>\n";
			$count_html .= "<td class=\"member_form_cell\">".$count_set."</td>\n";
			$count_html .= "</tr>\n";
			$INPUTS['COUNTSETTING'] = array('result'=>'plane','value'=>$count_html);
		}
	} else {
		$INPUTS['OPTION2'] = array('result'=>'plane','value'=>$option2);	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		$INPUTS['OPTION5'] = array('result'=>'plane','value'=>$option5);
	}
	// add start hasegawa 2016/09/29

	// add start karasawa 2019/07/23 BUD英語解析開発
	// upd start hirose 2020/09/18 テスト標準化開発
	// if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10 ){
	if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10  || $_SESSION['sub_session']['select_course']['form_type'] == 14){
	// upd end hirose 2020/09/18 テスト標準化開発
		$INPUTS['OPTION3'] = array('result'=>'plane','value'=>$BUD_SELECT_LIST[$option3]);
	}
	// add end karasawa 2019/07/23 BUD英語解析開発

	//upd start 2018/06/05 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	if ($_SESSION['t_practice']['test_type'] != 4) {
		$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	}else{
		$standard_time_html = "none_display";
		$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

	}
	//upd end 2018/06/05 yamaguchi

	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>$problem_point);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
	$INPUTS['BOOKUNITID'] 		= array('result'=>'plane','value'=>$book_unit_id);
	$INPUTS['LMSUNITID'] 		= array('result'=>'plane','value'=>$lms_unit_id);
//	$INPUTS[UPNAVISECTIONID] 	= array('result'=>'plane','value'=>$upnavi_section_id);		//	add koike 2012/06/12
	$INPUTS['UPNAVISECTIONNUM'] 	= array('result'=>'plane','value'=>$upnavi_section_num);		//	update oda 2012/07/05


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
 * 問題登録　テスト専用問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function test_add_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	$sql = "SELECT MAX(problem_num) AS max_num FROM " . T_MS_TEST_PROBLEM;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_num']) { $problem_num = $list['max_num'] + 1; } else { $problem_num = 1; }

	$ERROR = problem_test_add($problem_num);
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}
	$L_DEFAULT_TEST_NUM[] = $default_test_num;
	$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_TEST_DEFAULT_PROBLEM .
		" WHERE default_test_num='".$default_test_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	if ($_SESSION['t_practice']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
		$INSERT_DATA['term_bnr_ccd']	= $bnr_cd;
		$INSERT_DATA['term_kmk_ccd']	= $kmk_cd;
	}
	$INSERT_DATA['course_num']			= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['gknn']				= $_SESSION['t_practice']['gknn'];
	$INSERT_DATA['problem_point']		= $_POST['problem_point'];
	$INSERT_DATA['default_test_num']	= $default_test_num;
	$INSERT_DATA['problem_num']			= $problem_num;
	$INSERT_DATA['problem_table_type']	= 2;
	$INSERT_DATA['disp_sort']			= $disp_sort;
//	$INSERT_DATA[ins_syr_id] 			= ;
	$INSERT_DATA['ins_tts_id']			= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date']			= "now()";
	$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date']			= "now()";


	$ERROR = $cdb->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);
	if ($ERROR) { return $ERROR; }

	//教科書単元と問題の紐付け登録
//	$ERROR = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$problem_num,2,explode("//",$_POST['book_unit_id']));	//	del koike 2012/06/15
//	$ERROR = lms_unit_test_problem_add($problem_num,2,explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']));	//	del koike 2012/15

	book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$problem_num,2,explode("//",$_POST['book_unit_id']), $ERROR);
	lms_unit_test_problem_add($problem_num,2,explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']), $ERROR);
//	upnavi_section_test_problem_add($problem_num, $_POST['upnavi_section_num'], $ERROR);	//	add koike 2012/06/15	// 2012/07/26 del oda
	upnavi_section_test_problem_add($problem_num, $_POST['upnavi_section_num'],"2", $ERROR);							// 2012/07/26 add oda

	// del oda 2014/11/19 課題要望一覧No390
	//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
	//ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $problem_num, 2, $ERROR);

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
function problem_test_add($problem_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	$ins_data = array();
	$ins_data = $_POST;

	$array_replace = new array_replace();
	if ($_SESSION['sub_session']['select_course']['form_type'] == 1) {
		if ($ins_data['selection_words'] && $ins_data['selection_words'] === "0") {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 2) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 3) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

/*		// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		if ($ins_data['option2']) {
			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();
*/
		// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// form_type=3の場合は解答欄行数と解答欄サイズの値を<>で区切ってoption2に格納します。
		if(!$ins_data['option2']) {
			$ins_data['option2'] = make_option2($ins_data['input_row'],$ins_data['input_size']);

			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();

			unset($ins_data['input_row']);
			unset($ins_data['input_size']);
		}

		// if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		// del 2016/06/02 yoshizawa 手書き認識
		// 手書き認識の設定値パラメータを保持します。
		// すでにすらら問題でoption3を使用していたのでoption4を使用しています。
		//if ($ins_data['option4']) { unset($ins_data['option4']); }
		// <<<
		// if ($ins_data['option5']) { unset($ins_data['option5']); }		// del hasegawa 2016/09/29  国語文字数カウント
		if ($ins_data['option5'] !="true") { $ins_data['option5'] = ""; }	// add hasegawa 2016/09/29  国語文字数カウント

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 4) {
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 5) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['selection_words']) { unset($ins_data['selection_words']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 8) {
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 10) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		//if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del 2019/07/23 karasawa BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 11) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	// add start hasegawa 2016/06/01 作図ツール
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 13) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		// drawing_typeはoption1にセット
		$ins_data['option1'] = $ins_data['drawing_type'];
		unset($ins_data['drawing_type']);

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add start hirose 2020/09/21 テスト標準化開発
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 14) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['option4']) {
			$array_replace->set_line($ins_data['option4']);
			$ins_data['option4'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add end hirose 2020/09/21 テスト標準化開発
	}
	// add end hasegawa 2016/06/01
	// add start karasawa 2019/07/24 BUD英語解析開発
	// upd start hirose 2020/09/21 テスト標準化開発
	// if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10){
	if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10 || $CHECK_DATA['form_type'] == 14){
	// upd end hirose 2020/09/21 テスト標準化開発
		if ($ins_data['option3']) {
			$array_replace->set_line($ins_data['option3']);
			$ins_data['option3'] = $array_replace->replace_line();
		}
	}
	// add end karasawa 2019/07/24
	foreach ($ins_data AS $key => $val) {
		if ($key == "action"
		|| $key == "default_test_num"
		|| $key == "problem_point"
		|| $key == "book_unit_id"
		|| $key == "lms_unit_id"
		|| $key == "upnavi_section_num") { continue; }
		$INSERT_DATA[$key] = $val;
	}

	//画像名変換、フォルダ作成
	//音声名変換、フォルダ作成
	list($INSERT_DATA['question'],$ERROR) 	= img_convert($INSERT_DATA['question'],$problem_num);
	list($INSERT_DATA['question'],$ERROR) 	= voice_convert($INSERT_DATA['question'],$problem_num);

	list($INSERT_DATA['problem'],$ERROR) 		= img_convert($INSERT_DATA['problem'],$problem_num);
	list($INSERT_DATA['problem'],$ERROR) 		= voice_convert($INSERT_DATA['problem'],$problem_num);

	list($INSERT_DATA['hint'],$ERROR) 		= img_convert($INSERT_DATA['hint'],$problem_num);
	list($INSERT_DATA['hint'],$ERROR) 		= voice_convert($INSERT_DATA['hint'],$problem_num);

	list($INSERT_DATA['explanation'],$ERROR) 	= img_convert($INSERT_DATA['explanation'],$problem_num);
	list($INSERT_DATA['explanation'],$ERROR) 	= voice_convert($INSERT_DATA['explanation'],$problem_num);

	//form_type10,11のみselection_wordsの変換
	if ($INSERT_DATA['form_type'] == 10 || $INSERT_DATA['form_type'] == 11) {
		list($INSERT_DATA['selection_words'],$ERROR) 	= img_convert($INSERT_DATA['selection_words'],$problem_num);
		list($INSERT_DATA['selection_words'],$ERROR) 	= voice_convert($INSERT_DATA['selection_words'],$problem_num);
	}
	if ($INSERT_DATA['voice_data']) {
		$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
			}
			$INSERT_DATA['voice_data'] 		= $problem_num."_".$INSERT_DATA['voice_data'];
		} else {
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
			}
		}
	}
	$INSERT_DATA['problem_num'] 	= $problem_num;
	$INSERT_DATA['course_num'] 		= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['form_type'] 		= $_SESSION['sub_session']['select_course']['form_type'];
//	$INSERT_DATA[ins_syr_id] 		= ;
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 		= "now()";
	$ERROR = $cdb->insert(T_MS_TEST_PROBLEM,$INSERT_DATA);

	return $ERROR;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function select_course() {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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

	// $L_COURSE = course_list();// del hirose 2020/09/11 テスト標準化開発
	if (!$_SESSION['sub_session']['select_course']['course_num']) { $_SESSION['sub_session']['select_course']['course_num'] = $_SESSION['t_practice']['course_num']; }
	$L_COURSE = get_course_name_array($_SESSION['sub_session']['select_course']['course_num']);// add hirose 2020/09/11 テスト標準化開発
	$course_num_html = $L_COURSE[$_SESSION['sub_session']['select_course']['course_num']];
	$course_num_html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['select_course']['course_num']."\">\n";

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
		// add start hirose 2020/09/01 テスト標準化開発
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
		// add end hirose 2020/09/01 テスト標準化開発
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
//	$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
	$html .= $course_num_html;
//	$html .= "</select>\n";
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
 * コースの為POSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 */
function sub_course_session() {
	if ($_POST['course_num'] &&
	(($_SESSION['sub_session']['select_course']['course_num'] != $_POST['course_num'])
	|| ($_SESSION['sub_session']['select_course']['stage_num'] != $_POST['stage_num'])
	|| ($_SESSION['sub_session']['select_course']['lesson_num'] != $_POST['lesson_num'])
	|| ($_SESSION['sub_session']['select_course']['unit_num'] != $_POST['unit_num'])
	|| ($_SESSION['sub_session']['select_course']['block_num'] != $_POST['block_num'])
	//add start yamaguchi 2018/10/11 すらら英単語追加
	|| ($_SESSION['sub_session']['select_course']['test_type_num'] != $_POST['test_type_num'])
	|| ($_SESSION['sub_session']['select_course']['test_ctg1'] != $_POST['test_ctg1'])
	|| ($_SESSION['sub_session']['select_course']['test_ctg2'] != $_POST['test_ctg2'])
	//add start yamaguchi 2018/10/11
	)) {
		unset($_SESSION['sub_session']['select_course']['s_page_view']);
		unset($_SESSION['sub_session']['select_course']['s_page']);
	}
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['select_course']['s_page_view'] = $_POST['s_page_view'];
		unset($_SESSION['sub_session']['select_course']['s_page']);
	}
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['select_course']['s_page'] = $_POST['s_page']; }
	if (strlen($_POST['stage_num'])) { $_SESSION['sub_session']['select_course']['stage_num'] = $_POST['stage_num']; }
	if (strlen($_POST['lesson_num'])) { $_SESSION['sub_session']['select_course']['lesson_num'] = $_POST['lesson_num']; }
	if (strlen($_POST['unit_num'])) { $_SESSION['sub_session']['select_course']['unit_num'] = $_POST['unit_num']; }
	if (strlen($_POST['block_num'])) { $_SESSION['sub_session']['select_course']['block_num'] = $_POST['block_num']; }
 	if (strlen($_POST['course_num'])) {$_SESSION['sub_session']['select_course']['course_num'] = $_POST['course_num'];}
	//add start yamaguchi 2018/10/11 すらら英単語追加
	if (strlen($_POST['test_type_num'])){$_SESSION['sub_session']['select_course']['test_type_num'] = $_POST['test_type_num'];}
	if (strlen($_POST['test_ctg1'])){$_SESSION['sub_session']['select_course']['test_ctg1'] = $_POST['test_ctg1'];}
	if (strlen($_POST['test_ctg2'])){$_SESSION['sub_session']['select_course']['test_ctg2'] = $_POST['test_ctg2'];}
	//add start yamaguchi 2018/10/11

	return;
}


/**
 * 問題登録　すらら問題新規登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function surala_add_addform($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= select_course();
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
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num = p.problem_num".
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
			//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";	// del oda 2014/08/11
			//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";		// add oda 2014/08/11 課題要望一覧No330
			// upd start hirose 2020/10/01 テスト標準化開発
			// $html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";		// add oda 2014/08/11 課題要望一覧No330
			// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
			// $html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['t_practice']['default_test_num']."')\"></td>\n";		// add oda 2014/08/11 課題要望一覧No330
			if($_SESSION['t_practice']['test_type'] == 4){
				$id_ = $_SESSION['t_practice']['default_test_num'];
			}else{
				$id_ = $_SESSION['t_practice']['course_num'];
			}
			$html .= "<td><input {$form_attr} type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$id_."')\"></td>\n";
			// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
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
	$html .= "<input type=\"hidden\" name=\"action\" value=\"surala_check\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PROBLEM_ADD_FORM);

	$book_unit_att = "<br><span style=\"color:red;\">※設定したい教科書単元のＩＤを入力して下さい。";
//	$book_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$book_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を入力して下さい。";
//	$lms_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$lms_unit_att .= "<br>※複数の教科書単元ＩＤが設定されている場合、紐づくLMS単元を「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$upnavi_section_att = "<br><span style=\"color:red;\">※設定したい学力Upナビテスト子単元のＩＤを入力して下さい。";	//	add koike 2012/06/12
	$upnavi_section_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add koike 2012/06/12
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);

	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('type'=>'text','name'=>'problem_point','size'=>'5','value'=>$_POST['problem_point']);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$INPUTS['STANDARDTIME'] 		= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
		if ($_SESSION['t_practice']['test_type'] != 4) {
			$INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$_POST['standard_time']);
		}else{
			$standard_time_html = "none_display";
			$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

		}
		//upd end 2018/06/06 yamaguchi
	$INPUTS['BOOKUNITID'] 			= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$_POST['book_unit_id']);
	$INPUTS['BOOKUNITATT'] 			= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITID'] 			= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']);
//	$INPUTS[LMSUNITATT] 			= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITATT'] 			= array('result'=>'plane','value'=>$lms_unit_att);	// 2012/03/13 add ozaki
//	$INPUTS[UPNAVISECTIONID] 		= array('type'=>'text','name'=>'upnavi_section_id','size'=>'50','value'=>$_POST['upnavi_section_id']);	//	add koike 2012/06/12
	$INPUTS['UPNAVISECTIONNUM'] 	= array('type'=>'text','name'=>'upnavi_section_num','size'=>'50','value'=>$_POST['upnavi_section_num']);	//	update oda 2012/07/05
	$INPUTS['UPNAVISECTIONATT'] 	= array('result'=>'plane','value'=>$upnavi_section_att);	//	add koike 2012/06/12

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
 * 問題登録　すらら問題新規登録　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function surala_add_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	if ($_SESSION['t_practice']['test_type'] == "4") {
		if (!$_POST['problem_point']) { $ERROR[] = "配点が未入力です。"; }
		else {
			if (preg_match("/[^0-9]/",$_POST['problem_point'])) {
				$ERROR[] = "配点は半角数字で入力してください。";
			} elseif ($_POST['problem_point'] > 100) {
				$ERROR[] = "配点は100以下で入力してください。";
			}
		}
	}
	if ($_POST['standard_time']) {
		if (preg_match("/[^0-9]/",$_POST['standard_time'])) {
			$ERROR[] = "回答目安時間は半角数字で入力してください。";
		}
	}
	if ($_POST['book_unit_id']) {
		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:]/",$_POST['book_unit_id'])) {
			$ERROR[] = "教科書単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		if ($_SESSION['sub_session']['select_course']['book_id']) {
			$L_BOOK_UNIT = explode("::",$_POST['book_unit_id']);
			$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";
			$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0'".
	//				" AND unit_end_flg='1'".
					" AND book_unit_id IN (".$in_book_unit_id.")";
	//				" AND book_id='".$_SESSION['t_practice']['book_id']."'".$where;
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($L_BOOK_UNIT)) {
				$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}

			// add start oda 2014/10/14 課題要望一覧No352 登録／修正時のチェック処理が抜けていたので追加
			// 教科書単元のコースが異なる場合はエラーとする
			$sql  = "SELECT mbu.book_unit_id, mb.course_num FROM " . T_MS_BOOK_UNIT ." mbu ".
					" INNER JOIN ".T_MS_BOOK. " mb ON mbu.book_id = mb.book_id AND mb.display = '1' AND mb.mk_flg = '0' ".
					" WHERE mbu.mk_flg='0'".
					"   AND mbu.book_unit_id IN (".$in_book_unit_id.")".
					";";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					if ($_SESSION['t_practice']['course_num'] != $list['course_num']) {
						$ERROR[] = "設定されている教科書単元のコースが異なります。教科書単元ID = ".$list['book_unit_id'];
					}
				}
			}
			// add end oda 2014/10/14

		}
	} else {
		$ERROR[] = "教科書単元を設定してください。";
	}
	if ($_POST['lms_unit_id']) {
		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:|&lt;&gt;|<>]/",$_POST['lms_unit_id'])) {
				$ERROR[] = "LMS単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		$L_LMS_UNIT = explode("::",$_POST['lms_unit_id']);
		foreach ($L_LMS_UNIT as $val) {
			if (!$val) { continue; }	// 2012/03/13 add ozaki

			unset($unit_num);	//	add 2015/01/06 yoshizawa 課題要望一覧No.405対応

			if (preg_match("/&lt;&gt;/",$val)) {
				$unit_num = explode("&lt;&gt;",$val);
			} elseif (preg_match("/<>/",$val)) {
				$unit_num = explode("<>",$val);
			} else {
				$unit_num[] = trim($val);
			}
			$in_lms_unit_id = "'".implode("','",$unit_num)."'";
			$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				" AND unit_num IN (".$in_lms_unit_id.")";
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($unit_num)) {
				$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}
		}
	}

	/*
	//	del ookawara 2012/03/15	教科書単元を設定してもLMS単元を登録しない場合が有る為
	// 2012/02/27 add ozaki
	if (count($L_BOOK_UNIT) != count($L_LMS_UNIT)) {
		$ERROR[] = "教科書単元とLMS単元の設定数が異なります。";
	}
	*/

	//	add koike 2012/06/14 start
	//if ($_POST['upnavi_section_num']) {		//	del ookawara 2012/11/27
	if ($_POST['upnavi_section_num'] != "") {	//	add ookawara 2012/11/27
		$in_upnavi_section_num = "";
		$_POST['upnavi_section_num'] = mb_convert_kana($_POST['upnavi_section_num'], "as", "UTF-8");
		$L_UPNAVI_SECTION = explode("::",$_POST['upnavi_section_num']);

		//	add ookawara 2012/11/27 start
		//	親単元毎に確認
		foreach ($L_UPNAVI_SECTION as $key => $val) {
			$val = trim($val);

			if ($val == "") {
				continue;
			}

			if ($val == "0") {
				$ERROR[] = "学力Upナビ子単元で「0」が設定されています。";
				break;
			}

			$in_upnavi_section_num = "";
			if (preg_match("/&lt;&gt;/",$val)) {
				$in_upnavi_section_num = preg_replace("/&lt;&gt;/", ",", $val);
			} elseif (preg_match("/<>/",$val)) {
				$in_upnavi_section_num = preg_replace("/<>/", ",", $val);
			} else {
				$in_upnavi_section_num = trim($val);
			}
			$USN_L = array();
			$USN_L = explode(",", $in_upnavi_section_num);

			$upnavi_section_count = 0;
			$usn_cnt = 0;
			$sql  = "SELECT upnavi_chapter_num, count(upnavi_section_num) AS usn_cnt FROM " . T_UPNAVI_SECTION .
					" WHERE mk_flg='0'".
					" AND course_num = '".$_SESSION['t_practice']['course_num']."'".
					" AND upnavi_section_num IN (".$in_upnavi_section_num.")".
					" GROUP BY upnavi_chapter_num;";
			if ($result = $cdb->query($sql)) {
				$upnavi_section_count = $cdb->num_rows($result);
				$list = $cdb->fetch_assoc($result);
				$usn_cnt = $list['usn_cnt'];
			}
			//親単元が違う場合、入力した単元数と存在している単元数が違っていた場合エラー
			if ($upnavi_section_count != 1 || $usn_cnt != count($USN_L)) {
				$ERROR[] = "学力Upナビ子単元で存在しない子単元の設定、またはコースが異なる子単元、または同じ子単元の重複登録しようとしています。";
				break;
			}
		}
		//	add ookawara 2012/11/27 end

	}
	//	add koike 2012/06/14 end

	return $ERROR;
}


/**
 * 問題登録　すらら問題新規登録　確認フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function surala_add_check_html() {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "add") { $val = "surala_add"; }
				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}
	//upd start 2018/06/07 yamaguchi 学力診断テスト すらら問題削除画面 データ不取得修正
	//if ($_SESSION['t_practice']['default_test_num']) {
		//list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	if ($_POST['default_test_num']) {
		$default_test_num = $_POST['default_test_num'];
	//upd end 2018/06/07 yamaguchi
	} else {
		$default_test_num = 0;
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";

		$sql  = "SELECT *" .
			" FROM ". T_PROBLEM ." problem" .
			" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=problem.problem_num".
			" AND ms_tdp.default_test_num='".$default_test_num."'".
			" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
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
		$sql  = "SELECT * FROM " .
			T_BOOK_UNIT_TEST_PROBLEM . " butp".
			" WHERE butp.mk_flg='0'".
			" AND butp.default_test_num='".$default_test_num."'".
			" AND butp.problem_table_type='".$_POST['problem_table_type']."'".
			" AND butp.problem_num='".$_POST['problem_num']."'".
			" ORDER BY butp.book_unit_id";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_BOOK_UNIT = array();
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['book_unit_id']) { $L_BOOK_UNIT[] = $list['book_unit_id']; }
			}
		}
		$book_unit_id = "";
		if (is_array($L_BOOK_UNIT)) {
			$book_unit_id = implode("<>",$L_BOOK_UNIT);
		}

		$sql  = "SELECT bulu.* FROM " .
			T_BOOK_UNIT_LMS_UNIT . " bulu".
			" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM . " butp ON ".
				" bulu.book_unit_id = butp.book_unit_id".
				" AND bulu.problem_num = butp.problem_num".
				" AND bulu.problem_table_type = butp.problem_table_type".
				" AND butp.mk_flg=0".
				" AND butp.default_test_num='".$default_test_num."'".
			" WHERE bulu.mk_flg='0'".
			" AND bulu.problem_table_type='".$_POST['problem_table_type']."'".
			" AND bulu.problem_num='".$_POST['problem_num']."'".
			" ORDER BY bulu.book_unit_id,bulu.unit_num";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_UNIT_NUM = array();
			while ($list = $cdb->fetch_assoc($result)) {
//				if ($list['unit_num']) { $L_UNIT_NUM[] = $list['unit_num']; }
				if ($list['unit_num']) { $L_LMS_UNIT[$list['book_unit_id']][] .= $list['unit_num']; }
			}
		}
		$lms_unit_id = "";
		if (is_array($L_LMS_UNIT)) {
			$i = 0;
			foreach ($L_LMS_UNIT as $key => $val) {
				if ($i > 0) { $lms_unit_id .= "::"; }
				$lms_unit_id .= implode("<>",$val);
				$i = 1;
			}
		//	$HIDDEN .= "<input type=\"hidden\" name=\"lms_unit_id\" value=\"".$lms_unit_id."\">\n";
		}
//	add koike 2012/06/18 start
// update start 2012/07/05 oda
//		$sql = "SELECT usp.`upnavi_section_id` FROM " .
//				T_UPNAVI_SECTION_PROBLEM . " usp".
//				" WHERE usp.problem_num='".$_POST['problem_num']."'".
//				" AND usp.mk_flg = '0';";
		$sql = "SELECT " .
				"  usp.upnavi_section_num, " .
				"  us.upnavi_chapter_num " .
				" FROM " .T_UPNAVI_SECTION_PROBLEM . " usp".
				" INNER JOIN ".T_UPNAVI_SECTION." us ON usp.upnavi_section_num = us.upnavi_section_num AND us.mk_flg = '0' ".
				" WHERE usp.problem_num='".$_POST['problem_num']."'".
				" AND usp.problem_table_type='".$_POST['problem_table_type']."'".		// 2012/07/26 add oda
				" AND usp.mk_flg = '0'".
				" ORDER BY usp.upnavi_section_num, us.upnavi_chapter_num;";	//	add ookawara 2012/11/27
//echo $sql."<br>\n";
// update end 2012/07/05 oda
			if ($result = $cdb->query($sql)) {
				$L_UPNAVI_SECTION = array();
				while ($list = $cdb->fetch_assoc($result)) {
					if($list['upnavi_section_num']) {$L_UPNAVI_SECTION[] .= $list['upnavi_section_num'];}
				}
			}
			$upnavi_section_num = "";
			if ($L_UPNAVI_SECTION) {
				$upnavi_section_num = implode("::",$L_UPNAVI_SECTION);
			}
//	add koike 2012/06/18 end

	}

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']].$button."<br>\n";

	$html .= "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

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
	// upd end hasegawa 2018/04/03
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
	//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."')\"></td>\n";
	// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['t_practice']['default_test_num']."')\"></td>\n";
	if($_SESSION['t_practice']['test_type'] == 4){
		$id_ = $list['default_test_num'];
	}else{
		$id_ = $_SESSION['t_practice']['course_num'];
	}
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('1','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$id_."')\"></td>\n";
	// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
	// upd end hirose 2020/10/01 テスト標準化開発
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";


	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PROBLEM_ADD_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>$problem_point);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
	//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	if ($_SESSION['t_practice']['test_type'] != 4) {
		$INPUTS['STANDARDTIME']		= array('result'=>'plane','value'=>$standard_time);
	}else{
		$standard_time_html = "none_display";
		$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

	}
	//upd end 2018/06/06 yamaguchi
	$INPUTS['BOOKUNITID'] 			= array('result'=>'plane','value'=>$book_unit_id);
	$INPUTS['LMSUNITID'] 			= array('result'=>'plane','value'=>$lms_unit_id);
//	$INPUTS[UPNAVISECTIONID] 		= array('result'=>'plane','value'=>$upnavi_section_id);			//	add koike 2012/06/12
	$INPUTS['UPNAVISECTIONNUM'] 	= array('result'=>'plane','value'=>$upnavi_section_num);		//	add oda 2012/07/05


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
 * 問題登録　すらら問題新規登録　登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function surala_add_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	$ERROR = problem_attribute_add($_SESSION['sub_session']['select_course']['block_num'],$_POST['standard_time']);
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}
	$L_DEFAULT_TEST_NUM[] = $default_test_num;
	$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_TEST_DEFAULT_PROBLEM .
		" WHERE default_test_num='".$default_test_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	// update oda 2020/05/22 ms_test_default_problemの存在チェックに学年・学期・月度
// 	$sql = "SELECT * FROM " . T_MS_TEST_DEFAULT_PROBLEM .
// 		" WHERE default_test_num='".$default_test_num."'".
// 		" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='1';";
	if ($_SESSION['t_practice']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
		$INSERT_DATA['term_bnr_ccd']	= $bnr_cd;
		$INSERT_DATA['term_kmk_ccd'] 	= $kmk_cd;
		$where_add .= " AND term_bnr_ccd='".$bnr_cd."' ";
		$where_add .= " AND term_kmk_ccd='".$kmk_cd."' ";
	}

	$sql = "SELECT * FROM " . T_MS_TEST_DEFAULT_PROBLEM .
			" WHERE default_test_num='".$default_test_num."'".
			"   AND problem_num='".$_POST['problem_num']."'".
			"   AND problem_table_type='1'".
			"   AND gknn='".$_SESSION['t_practice']['gknn']."'".
			$where_add.
			";";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	//登録されていればアップデート、無ければインサート
	$INSERT_DATA['course_num'] 			= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['gknn'] 				= $_SESSION['t_practice']['gknn'];
	$INSERT_DATA['problem_point'] 		= $_POST['problem_point'];
	$where_add = "";
	if ($list) {
		$INSERT_DATA['mk_flg'] 			= 0;
		$INSERT_DATA['mk_tts_id'] 		= "";
		$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
//		$INSERT_DATA[upd_syr_id] 		= ;
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		// update start oda 2014/10/09 課題要望一覧No352 検索条件修正
		//$where = " WHERE default_test_num='".$default_test_num."'".
		//	" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='1';";
		$where = " WHERE default_test_num='".$default_test_num."'".
				" AND problem_num='".$_POST['problem_num']."' ".
				" AND problem_table_type='1' ".
				" AND gknn='".$_SESSION['t_practice']['gknn']."' ".
				$where_add.
				";";
		// update end oda 2014/10/09

		$ERROR = $cdb->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);

	} else {

		$INSERT_DATA['default_test_num'] 	= $default_test_num;
		$INSERT_DATA['problem_num'] 		= $_POST['problem_num'];
		$INSERT_DATA['problem_table_type'] 	= 1;
		$INSERT_DATA['disp_sort'] 			= $disp_sort;
//		$INSERT_DATA[ins_syr_id] 			= ;
		$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 			= "now()";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);
	}
	if ($ERROR) { return $ERROR; }

	//教科書単元と問題の紐付け登録
//	$ERROR = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],1,explode("//",$_POST['book_unit_id']));	//	del koike 2012/06/15
//	$ERROR = lms_unit_test_problem_add($_POST['problem_num'],1,explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']));	//	del koike 2012/06/15

	book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],1,explode("//",$_POST['book_unit_id']), $ERROR);
	lms_unit_test_problem_add($_POST['problem_num'],1,explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']), $ERROR);
//	upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'], $ERROR);	//	add koike 2012/06/15	// 2012/07/26 del oda
	upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'], "1", $ERROR);							// 2012/07/26 add oda

	// del oda 2014/11/19 課題要望一覧No390
	//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
	//ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $_POST['problem_num'], 1, $ERROR);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 問題登録　既存テストから登録　テスト選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */
function select_test() {

	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_GKNN_LIST_TYPE1;  // add 2015/02/16 yoshizawa 定期テスト高校版対応
	// global $L_MATH_TEST_CLASS; // add yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定      // del 2020/09/15 thanh テスト標準化開発

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start 2020/09/15 thanh テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 thanh テスト標準化開発

	// 学力診断テストに切り替えた時に定期テストの条件が残っているとデータが抽出できないのでunsetする
	if ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
		unset($_SESSION['sub_session']['select_course']['core_code']);
		unset($_SESSION['sub_session']['select_course']['publishing_id']);
	}

	//テストタイプ
	unset($L_TEST_TYPE[2]);
	unset($L_TEST_TYPE[3]);
	unset($L_TEST_TYPE[6]);	//add yamaguchi 2018/10/11 すらら英単語追加 英単語コース時以外英単語項目非表示
	// add start hirose 2020/09/21 テスト標準化開発
	$sql  = "SELECT ".
			 " mb.class_id ".
			 " FROM " . T_MATH_TEST_BOOK_INFO ." mb ".
			 " WHERE mb.mk_flg='0'".
			 " AND mb.course_num='".$_SESSION['t_practice']['course_num']."'".
			 " ;";
	// print $sql;
	$math_course_list = [];
	if ($result = $cdb->query($sql)) {
		while($class_info = $cdb->fetch_assoc($result)){
			$math_course_list[$class_info['class_id']] = true;
		}
	}
	// add end hirose 2020/09/21 テスト標準化開発
	// add start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	// 選択中のコースが数学以外の場合、数検を非表示とする。
	// upd start hirose 2020/09/21 テスト標準化開発
	// math_test_book_infoに選択しているコースが存在しなかったら、数学検定は削除
	// if ($_SESSION['t_practice']['course_num'] != '3') {
	if (empty($math_course_list)) {
	// upd end hirose 2020/09/21 テスト標準化開発
		unset($L_TEST_TYPE[5]);
	}
	// add end yoshizawa 2015/10/06
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

		// $L_COURSE = course_list();// del hirose 2020/09/11 テスト標準化開発

		if (!$_SESSION['sub_session']['select_course']['course_num']) {
			$_SESSION['sub_session']['select_course']['course_num'] = $_SESSION['t_practice']['course_num'];
		}
		$L_COURSE = get_course_name_array($_SESSION['sub_session']['select_course']['course_num']);// add hirose 2020/09/11 テスト標準化開発
		$couse_html = $L_COURSE[$_SESSION['sub_session']['select_course']['course_num']];
		$couse_html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['select_course']['course_num']."\">\n";

		// add 2015/02/16 yoshizawa 定期テスト高校版対応
		////学年
		//foreach($L_GKNN_LIST as $key => $val) {
		//	if ($_SESSION['sub_session']['select_course']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		//	$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		//}
		//----------------------------------------------

		// del 2015/02/16 yoshizawa 定期テスト高校版対応
		//学年
		if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
			foreach($L_GKNN_LIST_TYPE1 as $key => $val) {
				if ($_SESSION['sub_session']['select_course']['gknn'] == $key) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		// add start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		// 級選択プルダウン
		} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 5){
			foreach($L_MATH_TEST_CLASS as $key => $val) {
				// add start hirose 2020/09/21 テスト標準化開発
				//選択しているコース以外のものは削除
				if(empty($math_course_list[$key]) && $key != 'select'){
					continue;
				}
				// add end hirose 2020/09/21 テスト標準化開発
				if ($_SESSION['sub_session']['select_course']['class_id'] == $key) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$class_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		// add end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		} else {
			foreach($L_GKNN_LIST as $key => $val) {
				if ($_SESSION['sub_session']['select_course']['gknn'] == $key) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		}
		//----------------------------------------------

		// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		// 数学検定の場合の分岐を追加
		if ($_SESSION['sub_session']['select_course']['test_type'] != 5){
			$select_name .= "<td>コース</td>\n";
			$select_name .= "<td>学年</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= $couse_html;
			//$select_menu .= "<select name=\"course_num\" onchange=\"submit();\">".$couse_html."</select>\n";
			$select_menu .= "</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"gknn\" onchange=\"submit();\">".$gknn_html."</select>\n";
			$select_menu .= "</td>\n";

		// 数検を選んだ場合
		}else{
			$select_name .= "<td>級</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"class_id\" onchange=\"submit();\">".$class_html."</select>\n";
			$select_menu .= "</td>\n";
		}
		// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定

		if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
			//テスト時期
			$L_CORE_CODE = core_code_list();
			$core_code_html = "<option value=\"0\">選択して下さい</option>\n";
			foreach($L_CORE_CODE as $key => $val) {
				if ($_SESSION['t_practice']['core_code'] == $L_CORE_CODE[$key]['bnr_cd']."_".$L_CORE_CODE[$key]['kmk_cd']) { continue; }
				if ($_SESSION['sub_session']['select_course']['core_code'] == $L_CORE_CODE[$key]['bnr_cd']."_".$L_CORE_CODE[$key]['kmk_cd']) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$core_code_html .= "<option value=\"".$L_CORE_CODE[$key]['bnr_cd']."_".$L_CORE_CODE[$key]['kmk_cd']."\" ".$selected.">".
										  $L_CORE_CODE[$key]['bnr_nm']." ".$L_CORE_CODE[$key]['kmk_nm'].
										  "</option>\n";
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
				if ($_SESSION['sub_session']['select_course']['publishing_id'] == $list['publishing_id']) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$publishing_html .= "<option value=\"".$list['publishing_id']."\" ".$selected.">".$list['publishing_name']."</option>\n";
			}
			//教科書
			if ( $_SESSION['sub_session']['select_course']['course_num'] &&
 				$_SESSION['sub_session']['select_course']['publishing_id'] &&
 				$_SESSION['sub_session']['select_course']['gknn']) {
				$sql  = "SELECT * FROM " . T_MS_BOOK . " ms_book" .
					" WHERE publishing_id='".$_SESSION['sub_session']['select_course']['publishing_id']."'".
					" AND course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
					" AND gknn='".$_SESSION['sub_session']['select_course']['gknn']."'".
					" AND mk_flg='0' ORDER BY disp_sort";
				if ($result = $cdb->query($sql)) {
					$book_count = $cdb->num_rows($result);
				}
				if (!$book_count) {
					$book_html = "<option value=\"0\">設定されていません</option>\n";
				} else {
					$book_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['sub_session']['select_course']['book_id'] == $list['book_id']) {
							$selected = "selected";
						} else {
							$selected = "";
						}
						$book_html .= "<option value=\"".$list['book_id']."\" ".$selected.">".$list['book_name']."</option>\n";
					}
				}
			} else {
				$book_html .= "<option value=\"0\">--------</option>\n";
			}
			//単元
			if ($_SESSION['sub_session']['select_course']['book_id']) {
				$sql  = "SELECT *" . " FROM " . T_MS_BOOK_UNIT .
					 " WHERE mk_flg='0' AND book_id='".$_SESSION['sub_session']['select_course']['book_id']."'";
				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}
				if (!$book_unit_count) {
					$book_unit_html = "<option value=\"0\">設定されていません</option>\n";
				} else {
					$book_unit_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['sub_session']['select_course']['book_unit_id'] == $list['book_unit_id']) {
							$selected = "selected";
						} else {
							$selected = "";
						}
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
			if (!$_SESSION['sub_session']['select_course']['course_num'] || !$_SESSION['sub_session']['select_course']['gknn']) {
				$msg_html .= "学年を選択してください。<br>\n";
			} elseif (!$_SESSION['sub_session']['select_course']['book_id']) {
			//	$msg_html .= "教科書を選択してください。<br>\n";
			} elseif (!$_SESSION['sub_session']['select_course']['core_code']) {
				$msg_html .= "テスト時期を選択してください。<br>\n";
			} else {
				list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['sub_session']['select_course']['core_code']);
				$sql = "SELECT * FROM ". T_MS_TEST_DEFAULT .
						 " WHERE mk_flg='0' AND test_type='1'".
						 " AND default_test_num!='".$default_test_num."'".
						 " AND book_id='".$_SESSION['sub_session']['select_course']['book_id']."'".
						 " AND term_bnr_ccd='".$bnr_cd."'".
						 " AND term_kmk_ccd='".$kmk_cd."' LIMIT 1;";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				$_SESSION['sub_session']['select_course']['default_test_num'] = $list['default_test_num'];
			}
		} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
			//テストマスタ
			if ($_SESSION['sub_session']['select_course']['course_num'] && $_SESSION['sub_session']['select_course']['gknn']) {
				// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
				// テストは学年を持っていないのでSQLを変更する
				//$sql  = "SELECT * FROM " . T_MS_TEST_DEFAULT .
				//		  " WHERE mk_flg='0' AND test_type='4'".
				//		  " AND default_test_num!='".$default_test_num."'".
				//		  " AND course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
				//		  " AND test_gknn='".$_SESSION['sub_session']['select_course']['gknn']."'".
				//		  " ORDER BY disp_sort";
				$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP." ms_tg" .
						 " INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.test_group_id=ms_tg.test_group_id".
						 " AND tgl.mk_flg='0'".
						 " INNER JOIN ".T_MS_TEST_DEFAULT." ms_td ON ms_td.default_test_num=tgl.default_test_num".
						 " AND ms_td.mk_flg='0'".
						 " WHERE ms_tg.mk_flg='0'".
						 " AND ms_tg.test_gknn='".$_SESSION['sub_session']['select_course']['gknn']."'".
						 " AND ms_td.test_type='4'".
						 " AND ms_td.course_num='".$_SESSION['t_practice']['course_num']."'".
						 " GROUP BY ms_td.default_test_num".
						 " ORDER BY ms_tg.disp_sort,tgl.disp_sort";
				// update end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
				if ($result = $cdb->query($sql)) {
					$test_count = $cdb->num_rows($result);
				}
				if (!$test_count) {
					$test_html = "<option value=\"0\">設定されていません</option>\n";
				} else {
					$test_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['sub_session']['select_course']['default_test_num'] == $list['default_test_num']) {
							$selected = "selected";
						} else {
							$selected = "";
						}
						$test_html .= "<option value=\"".$list['default_test_num']."\" ".$selected.">".$list['test_name']."</option>\n";
					}
				}
			} else {
				$test_html .= "<option value=\"0\">--------</option>\n";
			}

			$select_name .= "<td>テスト名</td>\n";
			$select_menu .= "<td>\n";
			$select_menu .= "<select name=\"default_test_num\" onchange=\"submit();\">".$test_html."</select>\n";
			$select_menu .= "</td>\n";
			if (!$_SESSION['sub_session']['select_course']['course_num'] || !$_SESSION['sub_session']['select_course']['gknn']) {
				$msg_html .= "<span style=\"clear: both;\">\n";
				$msg_html .= "コース、学年を選択してください。<br></span>\n";
			} else {
				if (!$_SESSION['sub_session']['select_course']['default_test_num']) {
				//	$msg_html .= "<span style=\"clear: both;\">\n";
				//	$msg_html .= "テスト名を選択してください。<br></span>";
				}
			}
		// add start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		//数学検定の場合
		} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 5) {

			if ($_SESSION['sub_session']['select_course']['class_id'] != "select" ) {
				$sql  = "SELECT bu.book_unit_id, bu.book_unit_name".
						 " FROM ms_book_unit bu".
						 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id AND mtbul.mk_flg = '0'".
						 " AND mtbul.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'".
						 " WHERE bu.mk_flg = '0';";
				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}
				if (!$book_unit_count) {
					$book_unit_html = "<option value=\"0\">単元がありません</option>\n";
				} else {
					$book_unit_html = "<option value=\"0\">選択して下さい</option>\n";
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['sub_session']['select_course']['book_unit_id'] == $list['book_unit_id']) {
							$selected = "selected";
						} else {
							$selected = "";
						}
						$book_unit_html .= "<option value=\"".$list['book_unit_id']."\" ".$selected.">".$list['book_unit_name']."</option>\n";
					}
				}
			} else {
				$book_unit_html .= "<option value=\"0\">--------</option>\n";
				$msg_html .= "<span style=\"clear: both;\">\n";
				$msg_html .= "級を選択してください。<br></span>\n";
			}
		}
		// add end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"test_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_test_session\">\n";
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
function sub_test_session() {

	if (($_SESSION['sub_session']['select_course']['course_num'] != $_POST['course_num'])
	|| ($_SESSION['sub_session']['select_course']['publishing_id'] != $_POST['publishing_id'])
	|| ($_SESSION['sub_session']['select_course']['gknn'] != $_POST['gknn'])) {
		unset($_SESSION['sub_session']['select_course']['book_id']);
		unset($_SESSION['sub_session']['select_course']['default_test_num']);
	} elseif(strlen($_POST['book_id'])) {
		$_SESSION['sub_session']['select_course']['book_id'] = $_POST['book_id'];
	} elseif(strlen($_POST['default_test_num'])) {
		$_SESSION['sub_session']['select_course']['default_test_num'] = $_POST['default_test_num'];
	}
	if ($_SESSION['sub_session']['select_course']['book_id'] != $_POST['book_id']) {
		unset($_SESSION['sub_session']['select_course']['book_unit_id']);
	} elseif(strlen($_POST['book_unit_id'])) {
		$_SESSION['sub_session']['select_course']['book_unit_id'] = $_POST['book_unit_id'];
	}

	if (strlen($_POST['course_num'])) { $_SESSION['sub_session']['select_course']['course_num'] = $_POST['course_num']; }
	if (strlen($_POST['core_code'])) { $_SESSION['sub_session']['select_course']['core_code'] = $_POST['core_code']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['sub_session']['select_course']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['sub_session']['select_course']['gknn'] = $_POST['gknn']; }
	if ($_SESSION['sub_session']['select_course']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['sub_session']['select_course']);
	}
	if (strlen($_POST['test_type'])) { $_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type']; }

	// add start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	if($_SESSION['sub_session']['select_course']['test_type'] == 5){
		unset($_SESSION['sub_session']['select_course']);

		$_SESSION['sub_session']['select_course']['test_type'] = $_POST['test_type'];
		// $_SESSION['sub_session']['select_course']['course_num'] = 3; // del hirose 2020/09/22 テスト標準化開発
		if (strlen($_POST['class_id'])){
			$_SESSION['sub_session']['select_course']['class_id'] = $_POST['class_id'];
		}else {
			$_SESSION['sub_session']['select_course']['class_id'] = "select";
		}
		if (strlen($_POST['book_unit_id'])){
			$_SESSION['sub_session']['select_course']['book_unit_id'] = $_POST['book_unit_id'];
		}
		if(strlen($_POST['default_test_num'])) {
			$_SESSION['sub_session']['select_course']['default_test_num'] = $_POST['default_test_num'];
		}
	}
	// add end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定

	return $ERROR;
}


/**
 * 問題登録　既存テストから登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function test_exist_addform($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INPUTS = array();

	$html .= "<br>\n";
	$html .= $L_TEST_ADD_TYPE[$_SESSION['sub_session']['add_type']['add_type']].$L_TEST_PROBLEM_TYPE[$_SESSION['sub_session']['add_type']['problem_type']]."登録<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	$html .= select_test();
	// 2011/06/13 upd oz
	if (($_SESSION['sub_session']['select_course']['test_type'] == 1 && !$_SESSION['sub_session']['select_course']['core_code'])
		|| ($_SESSION['sub_session']['select_course']['test_type'] == 4 && !$_SESSION['sub_session']['select_course']['default_test_num'])) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
		return $html;
	}
	if ($_SESSION['sub_session']['select_course']['course_num']) {
		$join_and .= " AND mb.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'";
	}
	if ($_SESSION['sub_session']['select_course']['gknn']) {
		$join_and .= " AND mb.gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";
		$where .= " AND mtdp.gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";			// add oda 2014/10/14 課題要望一覧No352 学年を条件に追加
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
		// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		//$where .= " AND mbu.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		if ($_SESSION['sub_session']['select_course']['test_type'] != 5) {
			$where .= " AND mbu.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}else{
			$where .= " AND butp.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}
		// update end yoshizawa 2015/10/06
	}
	if ($_SESSION['sub_session']['select_course']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['sub_session']['select_course']['default_test_num']);
//		$where .= " AND mtdp.default_test_num='".$default_test_num."'";	//2011/06/13 del oz
		$where .= " AND mtdp.default_test_num='".$_SESSION['sub_session']['select_course']['default_test_num']."'";	//2011/06/13 add oz
	}
	// add start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	if ($_SESSION['sub_session']['select_course']['class_id'] != "select"){
		$and_class_id =" AND mtbu.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'";
	}
	// add end yoshizawa 2015/10/06

	// 定期テスト
	if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
		$join_sql = " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.mk_flg='0'".
			" AND mtdp.problem_table_type=butp.problem_table_type".
			" AND mtdp.problem_num=butp.problem_num".
			" AND mtdp.default_test_num=butp.default_test_num".
			" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.mk_flg='0'".
				" AND mbu.book_unit_id=butp.book_unit_id".
			" INNER JOIN ".T_MS_BOOK." mb ON mb.mk_flg='0' AND mb.book_id=mbu.book_id".$join_and;
	// 学力診断テスト
	} else if ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
		$join_sql = " INNER JOIN ".T_MS_TEST_DEFAULT." mtd ON mtd.mk_flg='0'".
				" AND mtd.default_test_num=mtdp.default_test_num".
				" AND mtd.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
			" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.mk_flg='0'".
				" AND tgl.default_test_num=mtd.default_test_num".
			" INNER JOIN ".T_MS_BOOK_GROUP." mtg ON mtg.mk_flg='0' AND mtg.test_group_id=tgl.test_group_id".
				" AND mtg.test_gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";
	}
	// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	//$sql  = "SELECT count(DISTINCT mtdp.problem_num) AS problem_count" .
	//	" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
	//	$join_sql.
	//	" WHERE mtdp.mk_flg='0'".
	//	$where.
	//	" GROUP BY mtdp.default_test_num,mtdp.problem_num,mtdp.problem_table_type".
	//	"";
	//数学検定
	if ($_SESSION['sub_session']['select_course']['test_type'] == 5) {
		if ($_SESSION['sub_session']['select_course']['class_id'] != "select"){
			$sql = 	"SELECT count(DISTINCT butp.problem_num) AS problem_count".
				" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbu ON mtbu.book_unit_id = butp.book_unit_id".
				$and_class_id.
				" AND mtbu.mk_flg= '0'".
				" WHERE butp.mk_flg = '0'".
				$where.
				" GROUP BY butp.book_unit_id,butp.problem_num,butp.problem_table_type".
				"";
		}
	//それ以外
	} else {
		$sql  = "SELECT count(DISTINCT mtdp.problem_num) AS problem_count" .
			" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$join_sql.
			" WHERE mtdp.mk_flg='0'".
			$where.
			" GROUP BY mtdp.default_test_num,mtdp.problem_num,mtdp.problem_table_type".
			"";
	}
	// update end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
//echo $sql."<hr><br>";

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
	if ($_SESSION['sub_session']['select_course']['s_page']) {
		$page = $_SESSION['sub_session']['select_course']['s_page'];
	} else {
		$page = 1;
	}
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";
	//add start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
	if ($_SESSION['t_practice']['test_type'] != 4 && $_SESSION['sub_session']['select_course']['test_type'] != 4) {
		$mpa_standard_time = "mpa.standard_time,";
		$mtp_standard_time = "mtp.standard_time,";
	}
	//add end 2018/06/08 yamaguchi


	// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	if($_SESSION['sub_session']['select_course']['test_type'] == 5) {
		if ($_SESSION['sub_session']['select_course']['class_id'] != "select"){
			$sql = 	"CREATE TEMPORARY TABLE test_problem_list ".
				//すらら問題
				" SELECT".
				" butp.book_unit_id,".
				" butp.problem_num,".
				" butp.problem_table_type,".
				" p.form_type,".
				" mtcp.disp_sort,".
				" mpa.standard_time".
				" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0'".
				" AND mtbul.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'".
				" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num = butp.problem_num".
				" LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = p.problem_num".
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg = '0' AND mpa.block_num = p.block_num".
				" WHERE butp.mk_flg='0'".
				" AND butp.problem_table_type='1'".
				"".
				 $where.
				" GROUP BY butp.book_unit_id,butp.problem_table_type,butp.problem_num".
				" UNION ALL ".
				//テスト問題
				" SELECT".
				" butp.book_unit_id,".
				" butp.problem_num,".
				" butp.problem_table_type,".
				" mtp.form_type,".
				" mtcp.disp_sort,".
				" mtp.standard_time".
				" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON butp.book_unit_id = mbu.book_unit_id AND mbu.mk_flg = '0'".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mtbul.book_unit_id = butp.book_unit_id AND mtbul.mk_flg = '0'".
				" AND mtbul.class_id = '".$_SESSION['sub_session']['select_course']['class_id']."'".
				" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg = '0' AND mtp.problem_num = butp.problem_num".
				" LEFT JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtcp.mk_flg = '0' AND mtcp.problem_num = mtp.problem_num".
				" WHERE butp.mk_flg='0'".
				" AND butp.problem_table_type='2'".
				"".
				$where.
				" GROUP BY butp.book_unit_id,butp.problem_table_type,butp.problem_num;";
//echo "<br>".$sql;
			$cdb->exec_query($sql);

			$sql  = "SELECT ".
				"*".
				" FROM test_problem_list".
				" ORDER BY disp_sort".
				$limit;
		}
	//それ以外のテスト
	} else{
		$sql = "CREATE TEMPORARY TABLE test_problem_list ".
			"SELECT ".
			"mtdp.default_test_num,".
			"mtdp.problem_table_type,".
			"mtdp.problem_num,".
			"p.form_type,".
			//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
			//"mpa.standard_time,".
			$mpa_standard_time.
			//upd end 2018/06/08 yamaguchi
			"mtdp.problem_point,".
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
			//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
			//"mtp.standard_time,".
			$mtp_standard_time.
			//upd end 2018/06/08 yamaguchi
			"mtdp.problem_point,".
			"mtdp.disp_sort".
			" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$join_sql.
			" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
			" WHERE mtdp.mk_flg='0'".
			" AND mtdp.problem_table_type='2'".
			"".
			$where.
			" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
		$cdb->exec_query($sql);

		$sql  = "SELECT ".
			"*".
			" FROM test_problem_list".
			" ORDER BY default_test_num,disp_sort".
			$limit;
	}
	// update end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定

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
		$html .= "<td>no</td>\n";
		$html .= "<th>問題種類</th>\n";
		$html .= "<th>出題形式</th>\n";
		//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$html .= "<th>回答目安時間</th>\n";

		if ($_SESSION['t_practice']['test_type'] != 4 && $_SESSION['sub_session']['select_course']['test_type'] != 4) {
			$html .= "<th>回答目安時間</th>\n";
		}
		//upd end 2018/06/08 yamaguchi
		$html .= "<th>配点</th>\n";
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
				$table_type = "すらら";
			} elseif ($list['problem_table_type'] == 2) {
				$table_type = "テスト専用";
			}

			$html .= "<label for=\"problem_".$list['problem_num']."\">";
			$html .= "<tr class=\"member_form_cell\" >\n";
			$html .= "<td>".$problem_btn."</td>\n";
			$html .= "<td>".$list['disp_sort']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
			//$html .= "<td>".$list['standard_time']."</td>\n";

			if ($_SESSION['t_practice']['test_type'] != 4 && $_SESSION['sub_session']['select_course']['test_type'] != 4) {
				$html .= "<td>".$list['standard_time']."</td>\n";
			}
			//upd end 2018/06/08 yamaguchi

			$html .= "<td>".$list['problem_point']."</td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			// upd start hirose 2020/10/01 テスト標準化開発
			// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";
			$disp_id = $_SESSION['t_practice']['default_test_num'];
			if($_SESSION['t_practice']['test_type'] == 5){
				$disp_id = $_SESSION['sub_session']['s_class_id'];
			// add start hirose 2020/12/15 テスト標準化開発 定期テスト
			}elseif($_SESSION['t_practice']['test_type'] == 4){
				$disp_id = $_SESSION['t_practice']['default_test_num'];
			}else{
				$disp_id = $_SESSION['t_practice']['course_num'];
			// add end hirose 2020/12/15 テスト標準化開発 定期テスト
			}
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$disp_id."')\"></td>\n";
			// upd end hirose 2020/10/01 テスト標準化開発
			$html .= "</form>\n";
			$html .= "</tr>\n";
			$html .= "</label>";
		}
		$html .= "</table>\n";
	}


	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_add_from\" id=\"problem_add_from\">\n";
	// update start yoshizawa 2015/10/08 02_作業要件/34_数学検定/数学検定
	// テスト選択、問題一覧、入力フォームまではここで共通使用。
	// エラーチェック、確認画面、登録処理は数学検定用の外部ファイルにて行う。
	if($_SESSION['t_practice']['test_type'] == 5) {
		$html .= "<input type=\"hidden\" name=\"action\" value=\"mt_exist_check\">\n";
	} else {
		$html .= "<input type=\"hidden\" name=\"action\" value=\"exist_check\">\n";
	}
	// update start yoshizawa 2015/10/08
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$_POST['problem_table_type']."\">\n";

	// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	if($_SESSION['t_practice']['test_type'] == 5) {
		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(MATH_TEST_PROBLEM_ADD_FORM);

		$control_unit_id_att = "<br><span style=\"color:red;\">※設定したい出題単元(小問)のＩＤを入力して下さい。";
		$control_unit_id_att .= "<br>※複数設定できません。</span>";
		$book_unit_id_att = "<br><span style=\"color:red;\">※設定したい採点単元(結果グラフ単元)のＩＤを入力して下さい。";
		$book_unit_id_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$lms_unit_att = "<br><span style=\"color:red;\">※設定したい復習ユニットのユニット番号を入力して下さい。";
		$lms_unit_att .= "<br>※複数設定する場合は、紐づく復習ユニットを「 :: 」区切りで入力して下さい。</span>";

		$INPUTS['PROBLEMPOINT']			= array('result'=>'plane','value'=>'--');
		$INPUTS['STANDARDTIME']			= array('type'=>'text','name'=>'add_standard_time','size'=>'5','value'=>$_POST['add_standard_time']);
		$INPUTS['CONTROLUNITID']		= array('type'=>'text','name'=>'control_unit_id','size'=>'50','value'=>$_POST['control_unit_id']);
		$INPUTS['CONTROLUNITIDATT']		= array('result'=>'plane','value'=>$control_unit_id_att);
		// POSTだと定期テストの単元が入ってしまいます。
		if( ACTION == 'mt_exist_check' || ACTION == 'back' ){ $book_unit_id = $_POST['book_unit_id']; }
		$INPUTS['BOOKUNITID']			= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$book_unit_id);
		$INPUTS['BOOKUNITIDATT']		= array('result'=>'plane','value'=>$book_unit_id_att);
		$INPUTS['LMSUNITID']			= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']);
		$INPUTS['LMSUNITATT']			= array('result'=>'plane','value'=>$lms_unit_att);
	} else {
		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(TEST_PROBLEM_ADD_FORM);

		$book_unit_att = "<br><span style=\"color:red;\">※設定したい教科書単元のＩＤを入力して下さい。";
		//$book_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
		$book_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
		$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を入力して下さい。";
		//$lms_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
		$lms_unit_att .= "<br>※複数の教科書単元ＩＤが設定されている場合、紐づくLMS単元を「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
		$upnavi_section_att = "<br><span style=\"color:red;\">※設定したい学力Upナビ子単元のＩＤを入力して下さい。";	//	add koike 2012/06/12
		$upnavi_section_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add koike 2012/06/12
		//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
		if ($_SESSION['t_practice']['test_type'] == "4") {
			$INPUTS['PROBLEMPOINT'] 	= array('type'=>'text','name'=>'add_problem_point','size'=>'5','value'=>$_POST['add_problem_point']);
		} else {
			$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
		}
		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$INPUTS['STANDARDTIME'] 	= array('type'=>'text','name'=>'add_standard_time','size'=>'5','value'=>$_POST['add_standard_time']);
		if ($_SESSION['t_practice']['test_type'] != 4) {
			$INPUTS['STANDARDTIME']		= array('type'=>'text','name'=>'add_standard_time','size'=>'5','value'=>$_POST['add_standard_time']);
		}else{
			$standard_time_html = "none_display";
			$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

		}
		//upd end 2018/06/06 yamaguchi
		$INPUTS['BOOKUNITID'] 	= array('type'=>'text','name'=>'add_book_unit_id','size'=>'50','value'=>$_POST['add_book_unit_id']);
		$INPUTS['BOOKUNITATT'] 	= array('result'=>'plane','value'=>$book_unit_att);
		$INPUTS['LMSUNITID'] 	= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$_POST['lms_unit_id']);
		//$INPUTS[LMSUNITATT] 	= array('result'=>'plane','value'=>$book_unit_att);
		$INPUTS['LMSUNITATT'] 	= array('result'=>'plane','value'=>$lms_unit_att);	// 2012/03/13 add ozaki
		//$INPUTS[UPNAVISECTIONID] 	= array('type'=>'text','name'=>'upnavi_section_id','size'=>'50','value'=>$_POST['upnavi_section_id']);	//	add koike 2012/06/12
		$INPUTS['UPNAVISECTIONNUM'] 	= array('type'=>'text','name'=>'upnavi_section_num','size'=>'50','value'=>$_POST['upnavi_section_num']);	//	add oda 2012/07/05
		$INPUTS['UPNAVISECTIONATT'] 	= array('result'=>'plane','value'=>$upnavi_section_att);	//	add koike 2012/06/12
	}
	// update end yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定

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
 * 問題登録　既存テストから登録　必須項目チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function test_exist_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['problem_num']) { $ERROR[] = "登録問題が未選択です。"; }
	if ($_SESSION['t_practice']['test_type'] == "4") {
		if (!$_POST['add_problem_point']) { $ERROR[] = "配点が未入力です。"; }
		else {
			if (preg_match("/[^0-9]/",$_POST['add_problem_point'])) {
				$ERROR[] = "配点は半角数字で入力してください。";
			} elseif ($_POST['add_problem_point'] > 100) {
				$ERROR[] = "配点は100以下で入力してください。";
			}
		}
	}
	if ($_POST['add_standard_time']) {
		if (preg_match("/[^0-9]/",$_POST['add_standard_time'])) {
			$ERROR[] = "回答目安時間は半角数字で入力してください。";
		}
	}
	if ($_POST['add_book_unit_id']) {

		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:]/",$_POST['add_book_unit_id'])) {
			$ERROR[] = "教科書単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		if ($_SESSION['sub_session']['select_course']['core_code']) {
			list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['sub_session']['select_course']['core_code']);
			if ($bnr_cd == "C000000001") {
//				$where = " AND term3_bnr_ccd='".$bnr_cd."' AND term3_kmk_ccd='".$kmk_cd."'";
			} elseif ($bnr_cd == "C000000002") {
//				$where = " AND term2_bnr_ccd='".$bnr_cd."' AND term2_kmk_ccd='".$kmk_cd."'";
			}
		}
		$L_BOOK_UNIT = explode("::",$_POST['add_book_unit_id']);
		$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";
		if ($_SESSION['sub_session']['select_course']['book_id']) {
			$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0'".
//					" AND unit_end_flg='1'".
					" AND book_unit_id IN (".$in_book_unit_id.")".
//					" AND book_id='".$_SESSION['sub_session']['select_course']['book_id']."'".
				$where;
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($L_BOOK_UNIT)) {
				$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}

			// add start oda 2014/10/14 課題要望一覧No352 登録／修正時のチェック処理が抜けていたので追加
			// 教科書単元のコースが異なる場合はエラーとする
			$sql  = "SELECT mbu.book_unit_id, mb.course_num FROM " . T_MS_BOOK_UNIT ." mbu ".
					" INNER JOIN ".T_MS_BOOK. " mb ON mbu.book_id = mb.book_id AND mb.display = '1' AND mb.mk_flg = '0' ".
					" WHERE mbu.mk_flg='0'".
					"   AND mbu.book_unit_id IN (".$in_book_unit_id.")".
					";";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					if ($_SESSION['t_practice']['course_num'] != $list['course_num']) {
						$ERROR[] = "設定されている教科書単元のコースが異なります。教科書単元ID = ".$list['book_unit_id'];
					}
				}
			}
			// add end oda 2014/10/14

		}
//	} else {
//		$ERROR[] = "教科書単元を設定してください。";
	}
	if ($_POST['lms_unit_id']) {
		//add start hasegawa 2015/11/17 数値以外指定時のエラーチェック
		if (preg_match("/[^0-9|:|&lt;&gt;|<>]/",$_POST['lms_unit_id'])) {
				$ERROR[] = "LMS単元は半角数字で入力してください";
		}
		//add end hasegawa 2015/11/17 数値以外指定時のエラーチェック

		$L_LMS_UNIT = explode("::",$_POST['lms_unit_id']);
		foreach ($L_LMS_UNIT as $val) {
			if (!$val) { continue; }	// 2012/03/13 add ozaki

			unset($unit_num);	//	add 2015/01/06 yoshizawa 課題要望一覧No.405対応

			if (preg_match("/&lt;&gt;/",$val)) {
				$unit_num = explode("&lt;&gt;",$val);
			} elseif (preg_match("/<>/",$val)) {
				$unit_num = explode("<>",$val);
			} else {
				$unit_num[] = trim($val);
			}
			$in_lms_unit_id = "'".implode("','",$unit_num)."'";
			$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				" AND unit_num IN (".$in_lms_unit_id.")";
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($unit_num)) {
				$ERROR[] = "同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}
		}
	}
	/*
	//	del ookawara 2012/03/15	教科書単元を設定してもLMS単元を登録しない場合が有る為
	// 2012/02/27 add ozaki
	if (count($L_BOOK_UNIT) != count($L_LMS_UNIT)) {
		$ERROR[] = "教科書単元とLMS単元の設定数が異なります。";
	}
	*/
	//	add koike 2012/06/15 start
	//if ($_POST['upnavi_section_num']) {		//	del ookawara 2012/11/27
	if ($_POST['upnavi_section_num'] != "") {	//	add ookawara 2012/11/27
		$in_upnavi_section_num = "";
		$_POST['upnavi_section_num'] = mb_convert_kana($_POST['upnavi_section_num'], "as", "UTF-8");
		$L_UPNAVI_SECTION = explode("::",$_POST['upnavi_section_num']);

		//	add ookawara 2012/11/27 start
		//	親単元毎に確認
		foreach ($L_UPNAVI_SECTION as $key => $val) {
			$val = trim($val);

			if ($val == "") {
				continue;
			}

			if ($val == "0") {
				$ERROR[] = "学力Upナビ子単元で「0」が設定されています。";
				break;
			}

			$in_upnavi_section_num = "";
			if (preg_match("/&lt;&gt;/",$val)) {
				$in_upnavi_section_num = preg_replace("/&lt;&gt;/", ",", $val);
			} elseif (preg_match("/<>/",$val)) {
				$in_upnavi_section_num = preg_replace("/<>/", ",", $val);
			} else {
				$in_upnavi_section_num = trim($val);
			}
			$USN_L = array();
			$USN_L = explode(",", $in_upnavi_section_num);

			$upnavi_section_count = 0;
			$usn_cnt = 0;
			$sql  = "SELECT upnavi_chapter_num, count(upnavi_section_num) AS usn_cnt FROM " . T_UPNAVI_SECTION .
					" WHERE mk_flg='0'".
					" AND course_num = '".$_SESSION['t_practice']['course_num']."'".
					" AND upnavi_section_num IN (".$in_upnavi_section_num.")".
					" GROUP BY upnavi_chapter_num;";
			if ($result = $cdb->query($sql)) {
				$upnavi_section_count = $cdb->num_rows($result);
				$list = $cdb->fetch_assoc($result);
				$usn_cnt = $list['usn_cnt'];
			}
			//親単元が違う場合、入力した単元数と存在している単元数が違っていた場合エラー
			if ($upnavi_section_count != 1 || $usn_cnt != count($USN_L)) {
				$ERROR[] = "学力Upナビ子単元で存在しない子単元の設定、またはコースが異なる子単元、または同じ子単元の重複登録しようとしています。";
				break;
			}
		}
		//	add ookawara 2012/11/27 end
	}
	//	add koike 2012/06/15 end



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
function test_exist_check_html() {

	global $L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INPUTS = array();

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "add") { $val = "exist_add"; }
//				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
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
		$where .= " AND mtdp.gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";			// add oda 2014/10/14 課題要望一覧No352 学年を条件に追加
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
		// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
		//$where .= " AND mbu.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		if ($_SESSION['sub_session']['select_course']['test_type'] != 5) {
			$where .= " AND mbu.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}else{
			$where .= " AND butp.book_unit_id='".$_SESSION['sub_session']['select_course']['book_unit_id']."'";
		}
		// update end yoshizawa 2015/10/06

	}
	if ($_SESSION['sub_session']['select_course']['default_test_num']) {
		// update yoshizawa 2015/10/07 ここで保持しているのはdefault_test_numだけなのでそのまま使用します。
		//list($test_group_id,$default_test_num) = explode("_",$_SESSION['sub_session']['select_course']['default_test_num']);
		//$where .= " AND mtdp.default_test_num='".$default_test_num."'";
		$where .= " AND mtdp.default_test_num='".$_SESSION['sub_session']['select_course']['default_test_num']."'";
		// update yoshizawa 2015/10/07
	}
	if ($_SESSION['sub_session']['select_course']['test_type'] == 1) {
		$join_sql = " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.mk_flg='0'".
			" AND mtdp.problem_table_type=butp.problem_table_type".
			" AND mtdp.problem_num=butp.problem_num".
			" AND mtdp.default_test_num=butp.default_test_num".
			" INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.mk_flg='0'".
				" AND mbu.book_unit_id=butp.book_unit_id".
			" INNER JOIN ".T_MS_BOOK." mb ON mb.mk_flg='0' AND mb.book_id=mbu.book_id".$join_and;
	} elseif ($_SESSION['sub_session']['select_course']['test_type'] == 4) {
		$join_sql = " INNER JOIN ".T_MS_TEST_DEFAULT." mtd ON mtd.mk_flg='0'".
				" AND mtd.default_test_num=mtdp.default_test_num".
				" AND mtd.course_num='".$_SESSION['sub_session']['select_course']['course_num']."'".
			" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.mk_flg='0'".
				" AND tgl.default_test_num=mtd.default_test_num".
			" INNER JOIN ".T_MS_BOOK_GROUP." mtg ON mtg.mk_flg='0' AND mtg.test_group_id=tgl.test_group_id".
				" AND mtg.test_gknn='".$_SESSION['sub_session']['select_course']['gknn']."'";

	}

	// update start yoshizawa 2015/10/06 02_作業要件/34_数学検定/数学検定
	//$sql  = "SELECT ".
	//	"mtdp.default_test_num,".
	//	"mtdp.problem_table_type,".
	//	"mtdp.problem_num,".
	//	"mtp.form_type,".
	//	"mtp.standard_time,".
	//	"mtdp.problem_point,".
	//	"mtdp.disp_sort".
	//	" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
	//	$join_sql.
	//	" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
	//	" WHERE mtdp.mk_flg='0'".
	//	" AND mtdp.problem_table_type='2'".
	//	" AND mtdp.problem_num='".$problem_num."'".
	//	"".
	//	$where.
	//	" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
	//数学検定
	if ($_SESSION['sub_session']['select_course']['test_type'] == 5) {
		if ($_SESSION['sub_session']['select_course']['class_id'] != "select"){
			$sql = 	"SELECT mtbu.disp_sort,butp.problem_table_type,mtp.form_type,mtp.standard_time ".
				" FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbu ON mtbu.book_unit_id = butp.book_unit_id".
				$and_class_id.
				" AND mtbu.mk_flg= '0'".
				" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND butp.problem_num=mtp.problem_num".
				" WHERE butp.mk_flg = '0'".
 				" AND butp.problem_num = '".$problem_num."' ".
				$where.
				" GROUP BY butp.book_unit_id,butp.problem_num,butp.problem_table_type".
				"";
		}
	//それ以外
	}else{

		// upd hasegawa 2016/06/01 既存のテスト問題にすららの問題が登録されていた場合、出ていなかったので修正
/*		$sql  = "SELECT ".
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
			" AND mtdp.problem_table_type='2'".
			" AND mtdp.problem_num='".$problem_num."'".
			"".
			$where.
			" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
*/
		$sql = 	"SELECT ".
			"mtdp.default_test_num,".
			"mtdp.problem_table_type,".
			"mtdp.problem_num,".
			"p.form_type,".
			"mpa.standard_time,".
			"mtdp.problem_point,".
			"mtdp.disp_sort".
			" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$join_sql.
			" LEFT JOIN ".T_PROBLEM." p ON p.state='0' AND p.problem_num=mtdp.problem_num".
			" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." mpa ON mpa.mk_flg='0' AND mpa.block_num=p.block_num".
			" WHERE mtdp.mk_flg='0'".
			" AND mtdp.problem_table_type='1'".
			" AND mtdp.problem_num='".$problem_num."'".
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
			"mtdp.disp_sort".
			" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$join_sql.
			" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
			" WHERE mtdp.mk_flg='0'".
			" AND mtdp.problem_num='".$problem_num."'".
			" AND mtdp.problem_table_type='2'".
			"".
			$where.
			" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
		// add end hasegawa 2016/06/01
	}
	// update end yoshizawa 2015/10/06

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
	//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$html .= "<th>回答目安時間</th>\n";

	if ($_SESSION['t_practice']['test_type'] != 4 && $_SESSION['sub_session']['select_course']['test_type'] != 4) {
		$html .= "<th>回答目安時間</th>\n";
	}
	//upd end 2018/06/08 yamaguchi
	$html .= "<th>配点</th>\n";
	$html .= "<th>確認</th>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\" >\n";
	$html .= "<td>".$list['disp_sort']."</td>\n";
	$html .= "<td>".$table_type."</td>\n";
	$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
	//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$html .= "<td>".$list['problem_point']."</td>\n";

	if ($_SESSION['t_practice']['test_type'] != 4 && $_SESSION['sub_session']['select_course']['test_type'] != 4) {
		//$html .= "<td>".$list['problem_point']."</td>\n";//del 2018/10/10 yamaguchi
		$html .= "<td>".$list['standard_time']."</td>\n";//add 2018/10/10 yamaguchi		既存不具合
	}
	//upd end 2018/06/08 yamaguchi
	//$html .= "<td>".$list['standard_time']."</td>\n";//del 2018/10/10 yamaguchi
	$html .= "<td>".$list['problem_point']."</td>\n";//add 2018/10/10 yamaguchi		既存不具合

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\"></td>\n";
	// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
	// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['t_practice']['default_test_num']."')\"></td>\n";
	if($_SESSION['t_practice']['test_type'] == 4){
		$disp_id = $_SESSION['t_practice']['default_test_num'];
	}else{
		$disp_id = $_SESSION['t_practice']['course_num'];
	}
	$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$disp_id."')\"></td>\n";
	// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
	// upd end hirose 2020/10/01 テスト標準化開発
	$html .= "</form>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";


	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PROBLEM_ADD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>$add_problem_point);
	} else {
		$INPUTS['PROBLEMPOINT'] 	= array('result'=>'plane','value'=>'--');
	}
	//upd start 2018/06/08 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$add_standard_time);
	if ($_SESSION['t_practice']['test_type'] != 4) {
		$INPUTS['STANDARDTIME'] 	= array('result'=>'plane','value'=>$add_standard_time);
	}else{
		$standard_time_html = "none_display";

		$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);
	}
	//upd end 2018/06/08 yamaguchi
	$INPUTS['BOOKUNITID'] 	= array('result'=>'plane','value'=>$add_book_unit_id);

	$INPUTS['LMSUNITID'] 	= array('result'=>'plane','value'=>$lms_unit_id);				 // add 2015/02/20 yoshziawa
	$INPUTS['UPNAVISECTIONNUM'] 	= array('result'=>'plane','value'=>$upnavi_section_num); // add 2015/02/20 yoshziawa

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
function test_exist_add() {

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	if ($_POST['problem_table_type'] == 1) {
		$ERROR = problem_attribute_add($_SESSION['sub_session']['select_course']['block_num'],$_POST['add_standard_time']);
	} elseif ($_POST['problem_table_type'] == 2) {
		$ERROR = problem_test_time_upd($_POST['problem_num'],$_POST['add_standard_time']);
	}
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}
	$L_DEFAULT_TEST_NUM[] = $default_test_num;
	$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_TEST_DEFAULT_PROBLEM .
		" WHERE default_test_num='".$default_test_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	$where_add = " AND gknn='".$_SESSION['t_practice']['gknn']."' ";
	if ($_SESSION['t_practice']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
		$INSERT_DATA['term_bnr_ccd']	= $bnr_cd;
		$INSERT_DATA['term_kmk_ccd']	= $kmk_cd;
		$where_add .= " AND term_bnr_ccd='".$bnr_cd."' ";
		$where_add .= " AND term_kmk_ccd='".$kmk_cd."' ";
	}

	$sql = "SELECT * FROM " . T_MS_TEST_DEFAULT_PROBLEM .
		" WHERE default_test_num='".$default_test_num."'".
		" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='".$_POST['problem_table_type']."'".
		$where_add.
		";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	//登録されていればアップデート、無ければインサート
	$INSERT_DATA['course_num'] 			= $_SESSION['t_practice']['course_num'];
	$INSERT_DATA['gknn'] 				= $_SESSION['t_practice']['gknn'];
	$INSERT_DATA['problem_point'] 		= $_POST['add_problem_point'];
	if ($list) {
		$INSERT_DATA['mk_flg'] 		= 0;
		$INSERT_DATA['mk_tts_id'] 	= "";
		$INSERT_DATA['mk_date'] 	= "0000-00-00 00:00:00";
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 	= "now()";

		// update start oda 2014/10/09 課題要望一覧No352 検索条件修正
		//$where = " WHERE default_test_num='".$default_test_num."'".
		//	" AND problem_num='".$_POST['problem_num']."' AND problem_table_type='".$_POST['problem_table_type']."';";
		$where = " WHERE default_test_num='".$default_test_num."'".
				" AND problem_num='".$_POST['problem_num']."' ".
				" AND problem_table_type='".$_POST['problem_table_type']."' ".
				$where_add.
				";";
		// update end oda 2014/10/09

		$ERROR = $cdb->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);
	} else {

		$INSERT_DATA['default_test_num']	= $default_test_num;
		$INSERT_DATA['problem_num'] 		= $_POST['problem_num'];
		$INSERT_DATA['problem_table_type'] 	= $_POST['problem_table_type'];
		$INSERT_DATA['disp_sort'] 			= $disp_sort;
//		$INSERT_DATA[ins_syr_id] 			= ;
		$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 			= "now()";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);
	}
	if ($ERROR) { return $ERROR; }

	//教科書単元と問題の紐付け登録
	//$ERROR = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],$_POST['problem_table_type'],$_POST['add_book_unit_id']);
	//$ERROR = lms_unit_test_problem_add($_POST['problem_num'],$_POST['problem_table_type'],$_POST['lms_unit_id'],$_POST['add_book_unit_id']);
	//$ERROR = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['book_unit_id']));	//	del ookawara 2012/06/15
	//$ERROR = lms_unit_test_problem_add($_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']));	//	del ookawara 2012/06/15

	book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['add_book_unit_id']), $ERROR);				// update $_POST['book_unit_id']→$_POST['add_book_unit_id'] 2015/02/20/ yoshizawa
	lms_unit_test_problem_add($_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['lms_unit_id']),explode("//",$_POST['add_book_unit_id']), $ERROR);	// update $_POST['book_unit_id']→$_POST['add_book_unit_id'] 2015/02/20/ yoshizawa
//	upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'], $ERROR);	//	add koike 2012/06/15	// 2012/07/26 del oda
	upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'],$_POST['problem_table_type'], $ERROR);	// 2012/07/26 add oda

	// del oda 2014/11/19 課題要望一覧No390
	//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
	//ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $_POST['problem_num'], $_POST['problem_table_type'], $ERROR);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 問題登録　テスト用問題マスタ属性登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $block_num
 * @param string $standard_time
 * @return array エラーの場合
 */
function problem_attribute_add($block_num,$standard_time) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

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

	// add start 2020/05/28 yoshizawa
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
	// add end 2020/05/28 yoshizawa

	$sql = "SELECT * FROM " . T_MS_PROBLEM_ATTRIBUTE .
		" WHERE mk_flg='0' AND block_num='".$block_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	//登録されていればアップデート、無ければインサート
	$INSERT_DATA['standard_time'] 	= $standard_time;
	if ($list) {
//		$INSERT_DATA[upd_syr_id] 		= ;
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
//		$INSERT_DATA[ins_syr_id] 		= ;
		$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 		= "now()";
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		$ERROR = $cdb->insert(T_MS_PROBLEM_ATTRIBUTE,$INSERT_DATA);
	}

	return $ERROR;
}


/**
 * 問題登録　テスト用問題回答目安時間登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @param string $standard_time
 * @return array エラーの場合
 */
function problem_test_time_upd($problem_num,$standard_time) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	$INSERT_DATA['standard_time'] 	= $standard_time;
//	$INSERT_DATA[upd_syr_id] 		= ;
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 		= "now()";

	$where = " WHERE problem_num='".$problem_num."';";

	$ERROR = $cdb->update(T_MS_TEST_PROBLEM,$INSERT_DATA,$where);

	return $ERROR;
}


/**
 * 問題登録　教科書単元＿テスト問題登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $L_DEFAULT_TEST_NUM
 * @param integer $problem_num
 * @param integer $problem_table_type
 * @param integer $book_unit_id
 * @param array $ERROR
 * @return array エラーの場合
 */
function book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$problem_num,$problem_table_type,$book_unit_id, &$ERROR) {	//	add &$ERROR ookawara 2012/06/15

	global $L_DEL_BOOK_UNIT_LIST;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	add ookawara 2012/06/15
	if ($ERROR) {
		return;
	}

	//紐付けされている単元番号取得
	foreach ($L_DEFAULT_TEST_NUM as $key => $default_test_num) {
		$L_BOOK_UNIT_LIST = array();
		$sql  = "SELECT butp.* FROM " .
			T_BOOK_UNIT_TEST_PROBLEM . " butp".
			" LEFT JOIN ".T_MS_BOOK_UNIT." ms_bu ON ms_bu.book_unit_id=butp.book_unit_id".
			" WHERE".
			" butp.default_test_num='".$default_test_num."'".
			// " AND ms_bu.unit_end_flg='1'".
			" AND butp.problem_table_type='".$problem_table_type."'".
			" AND butp.problem_num='".$problem_num."'";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$L_BOOK_UNIT_LIST[] = $list['book_unit_id'];
			}
		}

		if ($book_unit_id) {
			if (!is_array($book_unit_id)) {
				$book_unit_id[0] = $book_unit_id;
			}
			$L_BOOK_UNIT = explode("::",$book_unit_id[$key]);
			foreach($L_BOOK_UNIT as $val) {

				$INSERT_DATA = array();
				$array_key = array_keys($L_BOOK_UNIT_LIST,$val);
				//登録されていればアップデート
				if (isset($array_key[0])) {
					$INSERT_DATA['mk_flg'] 			= 0;
					$INSERT_DATA['mk_tts_id'] 		= "";
					$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
					// $INSERT_DATA[upd_syr_id] 		= ;
					$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 		= "now()";

					$where = " WHERE book_unit_id='".$val."'".
						" AND default_test_num='".$default_test_num."'".
						" AND problem_table_type='".$problem_table_type."'".
						" AND problem_num='".$problem_num."';";

					$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA,$where);

					if ($ERROR) { return $ERROR; }
					//更新レコード分の値を削除
					unset($L_BOOK_UNIT_LIST[$array_key[0]]);
				//登録されていなければインサート
				} else {
					if (!$val) { continue; }
					$INSERT_DATA['book_unit_id'] 		= $val;
					$INSERT_DATA['problem_num'] 		= $problem_num;
					$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
					$INSERT_DATA['default_test_num'] 	= $default_test_num;
					// $INSERT_DATA[ins_syr_id] 			= ;
					$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['ins_date'] 			= "now()";
					$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 			= "now()";

					$ERROR = $cdb->insert(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA);
					if ($ERROR) { return $ERROR; }
				}
			}
		}

		//登録されている物で更新されていないレコードを削除
		if (is_array($L_BOOK_UNIT_LIST)) {

			unset($INSERT_DATA);
			$INSERT_DATA['mk_flg'] 			= 1;
			$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date'] 		= "now()";
			$L_DEL_BOOK_UNIT_LIST = $L_BOOK_UNIT_LIST;	// 2012/02/28 add ozaki
			foreach($L_BOOK_UNIT_LIST as $val) {
				// $where = " WHERE book_unit_id='".$val."' AND default_test_num='".$default_test_num."' AND problem_num='".$problem_num."';";
				$where = " WHERE book_unit_id='".$val."'".
					" AND default_test_num='".$default_test_num."'".
					" AND problem_table_type='".$problem_table_type."'".
					" AND problem_num='".$problem_num."';";

				$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA,$where);
				if ($ERROR) { return $ERROR; }
			}
		}
	}
	return $ERROR;
}


/**
 * 問題登録　LMS単元＿テスト問題登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @param integer $problem_table_type
 * @param integer $lms_unit_id
 * @param integer $book_unit_id
 * @param array $ERROR
 * @return array エラーの場合
 */
function lms_unit_test_problem_add($problem_num,$problem_table_type,$lms_unit_id,$book_unit_id, &$ERROR) {	//	add &$ERROR ookawara 2012/06/15

	global $L_DEL_BOOK_UNIT_LIST;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	add ookawara 2012/06/15
	if ($ERROR) {
		return;
	}

//echo "problem_num = " . $problem_num . "<br>";
//echo "problem_table_type = " . $problem_table_type . "<br>";
//echo "book_unit_id = " . $book_unit_id . "<br>";
//echo "lms_unit_id  = " . $lms_unit_id . "<br>";
	if (!is_array($book_unit_id)) {
		$book_unit_id[0] = $book_unit_id;
	}
	if (!is_array($lms_unit_id)) {
		$lms_unit_id[0] = $lms_unit_id;
	}

	foreach ($book_unit_id as $book_unit_id_key => $book_unit_id_val) {
//echo "book_unit_id_key = " . $book_unit_id_key . "<br>";
//echo "book_unit_id_val = " . $book_unit_id_val . "<br>";
		$L_LMS_UNIT_LIST = array();
		$L_LMS_UNIT_LIST2 = array();
		$l_book_unit_id1 = explode("::",$book_unit_id_val);
		foreach ($l_book_unit_id1 as $l_book_unit_id1_val) {
			if (preg_match("/&lt;&gt;/",$l_book_unit_id1_val)) {
				$l_book_unit_id2 = explode("&lt;&gt;",$l_book_unit_id1_val);
				foreach ($l_book_unit_id2 as $l_book_unit_id2_val) {
					$L_LMS_UNIT_LIST2[] = trim($l_book_unit_id2_val);
				}
			} elseif (preg_match("/<>/",$l_book_unit_id1_val)) {
				$l_book_unit_id2 = explode("<>",$l_book_unit_id1_val);
				foreach ($l_book_unit_id2 as $l_book_unit_id2_val) {
					$L_LMS_UNIT_LIST2[] = trim($l_book_unit_id2_val);
				}
			} else {
				$L_LMS_UNIT_LIST2[] = $l_book_unit_id1_val;
			}
		}
		$in_book_unit_id = implode(",",$L_LMS_UNIT_LIST2);

		//紐付けされている単元番号取得
		$sql   = "SELECT bulu.* FROM ";
		$sql  .= T_BOOK_UNIT_LMS_UNIT . " bulu";
		$sql  .= " WHERE ";
		$sql  .= "     bulu.problem_table_type='".$problem_table_type."'";
		$sql  .= " AND bulu.problem_num='".$problem_num."'";
		$sql  .= " AND bulu.book_unit_id IN (".$in_book_unit_id.")";

		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$L_LMS_UNIT_LIST[$list['book_unit_id']][] = $list['unit_num'];
			}
		}

		if (!$L_LMS_UNIT_LIST) {
			$L_LMS_UNIT_LIST[0] = array();
		}
		if ($lms_unit_id) {
	//		$L_LMS_UNIT = explode("&lt;&gt;",$lms_unit_id);		// 2012/02/27 del ozaki
			$L_LMS_UNIT = explode("::",$lms_unit_id[$book_unit_id_key]);		// 2012/02/27 add ozaki
			$L_BOOK_UNIT = explode("::",$book_unit_id[$book_unit_id_key]);	// 2012/02/17 add ozaki

			foreach($L_LMS_UNIT as $key => $val) {
				$INSERT_DATA = array();

				unset($lms_unit_num);	//	add 2015/01/08 yoshizawa 課題要望一覧No.405対応

				if (preg_match("/&lt;&gt;/",$val)) {
					$lms_unit_num = explode("&lt;&gt;",$val);
				} elseif (preg_match("/<>/",$val)) {
					$lms_unit_num = explode("<>",$val);
				} else {
					$lms_unit_num[] = trim($val);
				}
				foreach ($lms_unit_num as $unit_num) {
					unset($INSERT_DATA);
					unset($array_key);	// 2012/03/14 add ozaki
					if (is_array($L_LMS_UNIT_LIST[$L_BOOK_UNIT[$key]])) {
						$array_key = array_keys($L_LMS_UNIT_LIST[$L_BOOK_UNIT[$key]],$unit_num);
					}

					//登録されていればアップデート
					if (isset($array_key[0])) {
						$INSERT_DATA['mk_flg'] 			= 0;
						$INSERT_DATA['mk_tts_id'] 		= "";
						$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
			//			$INSERT_DATA[upd_syr_id] 		= ;
						$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
						$INSERT_DATA['upd_date'] 			= "now()";

						$where = " WHERE unit_num='".$unit_num."'".
							" AND book_unit_id='".$L_BOOK_UNIT[$key]."'".
							" AND problem_num='".$problem_num."';";

						$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
						if ($ERROR) { return $ERROR; }
						//更新レコード分の値を削除
//						unset($L_LMS_UNIT_LIST[$L_BOOK_UNIT[$key]][$array_key[0]]);	// 2013/03/13 del ozaki
						foreach ($array_key as $del_key) {
							unset($L_LMS_UNIT_LIST[$L_BOOK_UNIT[$key]][$del_key]);
						}
					//登録されていなければインサート
					} else {
						if (!$unit_num) { continue; }
						$INSERT_DATA['book_unit_id'] 		= $L_BOOK_UNIT[$key];		// 2012/02/27 add ozaki
						$INSERT_DATA['unit_num'] 			= $unit_num;
						$INSERT_DATA['problem_num'] 		= $problem_num;
						$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
			//			$INSERT_DATA[ins_syr_id] 			= ;
						$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
						$INSERT_DATA['ins_date'] 			= "now()";
						$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
						$INSERT_DATA['upd_date'] 			= "now()";

						$ERROR = $cdb->insert(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA);
						if ($ERROR) { return $ERROR; }
					}
				}
			}
		}

		//登録されている物で更新されていないレコードを削除
		if (is_array($L_LMS_UNIT_LIST)) {

			unset($INSERT_DATA);
			$INSERT_DATA['mk_flg'] 			= 1;
			$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date'] 		= "now()";
			foreach($L_LMS_UNIT_LIST as $key => $val) {
				foreach ($val as $unit_num) {
//					$where = " WHERE unit_num='".$unit_num."' AND problem_num='".$problem_num."';";	// 2012/03/13 del ozaki
					$where = " WHERE book_unit_id='".$key."' AND unit_num='".$unit_num."' AND problem_num='".$problem_num."';";

					$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
					if ($ERROR) { return $ERROR; }
				}
			}
		}
	}

	// book_unit_test_problem を読み込み、削除対象か判断する。		// 2012/07/27 add oda
	//   データを更新する際、削除対象のbook_unit_idが空白になってしまうので、
	//   problem_numとproblem_table_typeを使用しbook_unit_lms_unitテーブルを参照し、
	//   book_unit_test_problemテーブルとの関連をチェックし、削除対象の場合は、削除処理を行う。
	check_book_unit_test_problem($problem_num, $problem_table_type, $ERROR);

	return $ERROR;
}


/**
 * 問題登録　学力Upナビ子単元＿テスト問題登録処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @param integer $upnavi_section_num
 * @param integer $problem_table_type
 * @param array $ERROR
 */
function upnavi_section_test_problem_add($problem_num, $upnavi_section_num, $problem_table_type, &$ERROR) {		// 2012/07/26 add oda
//function upnavi_section_test_problem_add($problem_num, $upnavi_section_num, &$ERROR) {						// 2012/07/26 del oda
	//	add koike 2012/06/15 start
	// 追加　学力Upナビ登録

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		return;
	}
	$SECTION_ID = array();
	$SECTION_ID_WORK = array();
	//	default_test_num毎に複数登録されていた場合の回避処理
	//	一つの問題にはdefault_test_num毎にupnavi_section_numがそれぞれ違う値が設定される事が無い為
	//	add ookawara 2012/11/14 start
	if (preg_match("/\/\//", $upnavi_section_num)) {
		$USN_L = explode("//",$upnavi_section_num);
		foreach ($USN_L AS $line) {
			$CHK_SECTION_ID_WORK_L = explode("::", $line);
			$CHK_SECTION_ID_WORK = array_merge($CHK_SECTION_ID_WORK, $CHK_SECTION_ID_WORK_L);
		}
		$CHK_SECTION_ID_WORK = array_unique($CHK_SECTION_ID_WORK);
		sort($CHK_SECTION_ID_WORK);
		$upnavi_section_num = implode("::",$CHK_SECTION_ID_WORK);
	}
	//	add ookawara 2012/11/14 end

	//	add ookawara 2012/11/27 start
	$SECTION_ID_WORK = explode("::", $upnavi_section_num);
	$SECTION_ID_WORK = array_unique($SECTION_ID_WORK);
	//	add ookawara 2012/11/27 end

	for ($i=0; $i < count($SECTION_ID_WORK); $i++) {
		if (preg_match("/&lt;&gt;/",$SECTION_ID_WORK[$i])) {
			$WORK_LIST = explode("&lt;&gt;", $SECTION_ID_WORK[$i]);
			for ($j=0; $j < count($WORK_LIST); $j++) {
				$SECTION_ID[] = $WORK_LIST[$j];
			}
		} elseif (preg_match("/<>/",$SECTION_ID_WORK[$i])) {
			$WORK_LIST = explode("<>", $SECTION_ID_WORK[$i]);
			for ($j=0; $j < count($WORK_LIST); $j++) {
				$SECTION_ID[] = $WORK_LIST[$j];
			}
		} else {
			$SECTION_ID[] = $SECTION_ID_WORK[$i];
		}
	}
	if (count($SECTION_ID) < 1) {
		return;
	}

	//	登録済みの情報取得
	$UPDATE_LIST = array();
	$DELETE_LIST = array();
//	$DELETE_LIST[] = "0";	//	add & del ookawara 2012/11/27
	$sql = "SELECT usp.upnavi_section_num FROM " .
			T_UPNAVI_SECTION_PROBLEM . " usp".
			" INNER JOIN ".T_UPNAVI_SECTION." us ON usp.upnavi_section_num = us.upnavi_section_num AND us.mk_flg = '0' ".
			" WHERE usp.problem_num='".$problem_num."'".
			" AND usp.problem_table_type='".$problem_table_type."'".			// 2012/07/26 add oda
			" AND usp.mk_flg = '0'".
			" ORDER BY usp.upnavi_section_num, us.upnavi_chapter_num;";	//	add ookawara 2012/11/27
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$check_upnavi_section_num = $list['upnavi_section_num'];

			if (array_search($check_upnavi_section_num, $SECTION_ID) === false) {
				$DELETE_LIST[] = $check_upnavi_section_num;
			} else {
				$UPDATE_LIST[] = $check_upnavi_section_num;
			}

			$key  = array_search($check_upnavi_section_num, $SECTION_ID);
			if (empty($key) && $SECTION_ID[$key] != $check_upnavi_section_num ){
				$DELETE_LIST[] = $check_upnavi_section_num;
			} else if ($key >= 0) {
				$UPDATE_LIST[] = $check_upnavi_section_num;
				UNSET($SECTION_ID[$key]);
			}
		}
	}

	//	add ookawara 2012/11/27 start
	$SECTION_ID = array_unique($SECTION_ID);
	$UPDATE_LIST = array_unique($UPDATE_LIST);
	$DELETE_LIST = array_unique($DELETE_LIST);
	//	add ookawara 2012/11/27 end

	//	更新処理
	if (count($UPDATE_LIST) > 0) {
		foreach ($UPDATE_LIST AS $section_num) {
			//if ($section_num == "") { continue; }	//	del ookawara 2012/11/27
			if ($section_num < 1) { continue; }	//	add ookawara 2012/11/27
			$INSERT_DATA = array();
			$INSERT_DATA['upnavi_section_num']	= $section_num;
			$INSERT_DATA['problem_num'] 		= $problem_num;
			$INSERT_DATA['problem_table_type'] 	= $problem_table_type;			// 2012/07/26 add oda
			$INSERT_DATA['upd_syr_id']			= 'updateline';
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']			= "now()";
			$where  = " WHERE problem_num='".$problem_num."'";
			$where .= " AND upnavi_section_num='".$section_num."'";

			$ERROR = $cdb->update(T_UPNAVI_SECTION_PROBLEM, $INSERT_DATA, $where);
		}
		if ($ERROR) { return $ERROR; }
	}
	//	削除処理
	if (count($DELETE_LIST) > 0) {
		foreach ($DELETE_LIST AS $section_num) {
			if ($section_num == "") { continue; }
			//	掃除をする為$section_numの値が0でも処理ができる様に！

			$INSERT_DATA = array();
			$INSERT_DATA['mk_flg'] 		= 1;
			$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date']		= "now()";
			$where  = " WHERE problem_num='".$problem_num."'";
			$where .= " AND upnavi_section_num='".$section_num."'";

			$ERROR = $cdb->update(T_UPNAVI_SECTION_PROBLEM, $INSERT_DATA, $where);
		}
		if ($ERROR) { return $ERROR; }
	}
	//	インサート処理
	if (count($SECTION_ID) > 0) {
		foreach ($SECTION_ID AS $section_num) {
			//if ($section_num == "") { continue; }	//	del ookawara 2012/11/27
			if ($section_num < 1) { continue; }	//	add ookawara 2012/11/27
			$INSERT_DATA = array();
			$INSERT_DATA['upnavi_section_num']		= $section_num;
			$INSERT_DATA['problem_num'] 			= $problem_num;
			$INSERT_DATA['problem_table_type']		= $problem_table_type;			// 2012/07/26 add oda
			$INSERT_DATA['upd_syr_id']				= 'addline';					// 2012/07/30 add oda
			$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];		// 2012/07/30 add oda
			$INSERT_DATA['upd_date']				= "now()";						// 2012/07/30 add oda
			$INSERT_DATA['ins_syr_id']				= 'addline';					// 2012/07/30 add oda
			$INSERT_DATA['ins_tts_id'] 				= $_SESSION['myid']['id'];
			$INSERT_DATA['ins_date'] 				= "now()";

			$ERROR = $cdb->insert(T_UPNAVI_SECTION_PROBLEM,$INSERT_DATA);
		}
		if ($ERROR) { return $ERROR; }
	}

}
//	add koike 2012/06/15 end


/**
 * 修正フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_DRAWING_TYPE;	// upd hasegawa 2016/06/01 作図ツール $L_DRAWING_TYPE 追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['default_test_num']) {
		$_SESSION['focus_num'] = $_POST['default_test_num']."_".$_POST['problem_num'];
	} else {
		$_SESSION['focus_num'] = "_".$_POST['problem_num'];
	}

	if (ACTION) {
		if ($_POST['problem_table_type'] == 1) {
			$sql  = "SELECT form_type" .
				" FROM ". T_PROBLEM ." problem" .
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
				" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=problem.problem_num".
				" AND ms_tdp.default_test_num='".$default_test_num."'".
				" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE problem.state='0'".
				" AND problem.problem_num='".$_POST['problem_num']."' LIMIT 1";
		} elseif ($_POST['problem_table_type'] == 2) {
			$sql  = "SELECT form_type" .
				" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
				" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=ms_tp.problem_num".
				" AND ms_tdp.default_test_num='".$default_test_num."'".
				" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE ms_tp.mk_flg='0'".
				" AND ms_tp.problem_num='".$_POST['problem_num']."' LIMIT 1";
		}

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		$form_type = $list['form_type'];
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$default_test_num = $_POST['default_test_num'];
		if ($_POST['problem_table_type'] == 1) {
			$sql  = "SELECT *" .
				" FROM ". T_PROBLEM ." problem" .
				" LEFT JOIN ".T_MS_PROBLEM_ATTRIBUTE." problem_att ON problem_att.block_num=problem.block_num".
				" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=problem.problem_num".
				" AND ms_tdp.default_test_num='".$default_test_num."'".
				" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
				" WHERE problem.state='0'".
				" AND problem.problem_num='".$_POST['problem_num']."' LIMIT 1";
		} elseif ($_POST['problem_table_type'] == 2) {
			$sql  = "SELECT *" .
				" FROM ". T_MS_TEST_PROBLEM ." ms_tp" .
				" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." ms_tdp ON ms_tdp.problem_num=ms_tp.problem_num".
				" AND ms_tdp.default_test_num='".$default_test_num."'".
				" AND ms_tdp.problem_table_type='".$_POST['problem_table_type']."'".
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
//			$val = ereg_replace("&lt;","<",$val);	//	2011/04/19 del ozaki
//			$val = ereg_replace("&gt;",">",$val);	//	2011/04/19 del ozaki
//			$$key = replace_decode($val);			//	2011/04/19 del ozaki
				$$key = $val;	//	2011/04/19 add ozaki
		}
		$_SESSION['sub_session']['select_course']['form_type'] = $form_type;

		$sql  = "SELECT butp.* FROM " .
			T_BOOK_UNIT_TEST_PROBLEM . " butp".
			" INNER JOIN ".T_MS_BOOK_UNIT." ms_bu ON ms_bu.book_unit_id=butp.book_unit_id".
			" WHERE ms_bu.mk_flg='0' AND butp.mk_flg='0'".
//			" AND ms_bu.book_id='".$_SESSION['t_practice']['book_id']."'".
//			" AND ms_bu.unit_end_flg='1'".
			$where.
			" AND butp.problem_table_type='".$_POST['problem_table_type']."'".
			" AND butp.default_test_num='".$default_test_num."'".
			" AND butp.problem_num='".$_POST['problem_num']."'".
			" ORDER BY butp.book_unit_id";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_BOOK_UNIT = array();
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['book_unit_id']) { $L_BOOK_UNIT[] .= $list['book_unit_id']; }
			}
		}
		$book_unit_id = "";
		if (is_array($L_BOOK_UNIT)) {
			$book_unit_id = implode("::",$L_BOOK_UNIT);
			$L_BOOK_UNIT2 = $L_BOOK_UNIT;
		}
		$sql  = "SELECT bulu.* FROM " .
			T_BOOK_UNIT_LMS_UNIT . " bulu".
			" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM . " butp ON ".
				" bulu.book_unit_id = butp.book_unit_id".
				" AND bulu.problem_num = butp.problem_num".
				" AND bulu.problem_table_type = butp.problem_table_type".
				" AND butp.mk_flg=0".
				" AND butp.default_test_num='".$default_test_num."'".
			" WHERE bulu.mk_flg='0'".
			" AND bulu.problem_table_type='".$_POST['problem_table_type']."'".
			" AND bulu.problem_num='".$_POST['problem_num']."'".
			" GROUP BY bulu.book_unit_id,bulu.unit_num".	// 2012/03/13 add ozaki
			" ORDER BY bulu.book_unit_id,bulu.unit_num";	// 2012/02/27 add ozaki
		if ($result = $cdb->query($sql)) {
			$L_BOOK_UNIT = array();
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['unit_num']) { $L_LMS_UNIT[$list['book_unit_id']][] .= $list['unit_num']; }
			}
		}
		$lms_unit_id = "";
		if (is_array($L_LMS_UNIT)) {
			$i = 0;
//			foreach ($L_LMS_UNIT as $key => $val) {
			foreach ($L_BOOK_UNIT2 as $key => $val) {
				if ($i > 0) { $lms_unit_id .= "::"; }
				if (is_array($L_LMS_UNIT[$val])) {
					$lms_unit_id .= implode("<>",$L_LMS_UNIT[$val]);
				}
				$i = 1;
			}
		}
//	add koike 2012/06/18 start
	$sql = "SELECT " .
			"   usp.upnavi_section_num, ".
			"   us.upnavi_chapter_num ".
			" FROM " .T_UPNAVI_SECTION_PROBLEM . " usp".
			" INNER JOIN ".T_UPNAVI_SECTION." us ON usp.upnavi_section_num = us.upnavi_section_num AND us.mk_flg = '0' ".
			" WHERE usp.problem_num='".$_POST['problem_num']."'".
			" AND usp.mk_flg = '0'".
			" ORDER BY usp.upnavi_section_num, us.upnavi_chapter_num;";	//	add ookawara 2012/11/27
//echo $sql."<br>\n";

		//	add ookawara 2012/11/27 start
		$UCN_L = array();
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$upnavi_chapter_num = $list['upnavi_chapter_num'];
				$upnavi_section_num = $list['upnavi_section_num'];
				$UCN_L[$upnavi_chapter_num][] = $upnavi_section_num;
			}
		}
		$upnavi_section_num = "";
		if (is_array($UCN_L)) {
			foreach ($UCN_L AS $upnavi_chapter_num => $upnavi_section_num_list) {
				if ($upnavi_section_num != "") {
					$upnavi_section_num .= "::";
				}
				$upnavi_section_num .= implode("&lt;&gt;",$upnavi_section_num_list);
			}
		}
		//	add ookawara 2012/11/27 end
//echo "upnavi_section_num<>".$upnavi_section_num."<br>\n";
//echo "upnavi_section_id".$upnavi_section_id;
//	add koike 2012/06/18 end
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if ($_POST['problem_table_type'] == 1) {
		$table_file = TEST_PROBLEM_ADD_FORM;

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
		// upd end hasegawa 2018/04/03

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
		// upd start hirose 2020/10/01 テスト標準化開発
		// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$list['problem_num']."')\"></td>\n";	//upd hasegawa $list['problem_table_type']を$_POST['problem_table_type']に変更
		// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
		// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['t_practice']['default_test_num']."')\"></td>\n";	//upd hasegawa $list['problem_table_type']を$_POST['problem_table_type']に変更
		if($_SESSION['t_practice']['test_type'] == 4){
			$disp_id = $_SESSION['t_practice']['default_test_num'];
		}else{
			$disp_id = $_SESSION['t_practice']['course_num'];
		}
		$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$list['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$disp_id."')\"></td>\n";
		// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
		// upd end hirose 2020/10/01 テスト標準化開発
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<br>";
	} elseif ($_POST['problem_table_type'] == 2) {
		$table_file = "test_problem_form_type_".$_SESSION['sub_session']['select_course']['form_type'].".htm";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$_POST['default_test_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_POST['problem_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_table_type\" value=\"".$_POST['problem_table_type']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($table_file);

	$book_unit_att = "<br><span style=\"color:red;\">※設定したい教科書単元のＩＤを入力して下さい。";
//	$book_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$book_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$lms_unit_att = "<br><span style=\"color:red;\">※設定したいLMS単元のユニット番号を入力して下さい。";
//	$lms_unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";	//	del ookawara 2012/03/13
	$lms_unit_att .= "<br>※複数の教科書単元ＩＤが設定されている場合、紐づくLMS単元を「 :: 」区切りで入力して下さい。</span>";	//	add ookawara 2012/03/13
	$upnavi_section_att = "<br><span style=\"color:red;\">※設定したい学力Upナビ子単元のＩＤを入力して下さい。";	//	add koike 2012/06/12
	$upnavi_section_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add koike 2012/06/12
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);

	if ($_SESSION['t_practice']['test_type'] == 4) {
		$default_test_num_html = "<tr>\n";
		$default_test_num_html .= "<td class=\"member_form_menu\">テストID</td>\n";
		$default_test_num_html .= "<td class=\"member_form_cell\">".$_POST['default_test_num']."</td>\n";
		$default_test_num_html .= "</tr>\n";
		$INPUTS['DEFAULTTESTNUM'] 	= array('result'=>'plane','value'=>$default_test_num_html);
	}

	// add start hasegawa  2016/06/01 作図ツール
	if($form_type == 13) {
		$drawing_type = "";
		if($_POST['drawing_type']) {
			$drawing_type = $_POST['drawing_type'];
		}else {
			$drawing_type = $option1;
		}
		$INPUTS['DRAWINGTYPE'] 		= array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
		$html .= "<input type=\"hidden\" name=\"drawing_type\" value=\"".$drawing_type."\">\n";
	}
	// add end hasegawa  2016/06/01

	$INPUTS['PROBLEMNUM']			= array('result'=>'plane','value'=>$_POST['problem_num']);
	$INPUTS['PROBLEMTYPE']			= array('result'=>'plane','value'=>'----');
	$INPUTS['FORMTYPE']			= array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);
	$INPUTS['QUESTION']			= array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$question);
	$INPUTS['PROBLEM']			= array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$problem);
	$INPUTS['VOICEDATA']			= array('type'=>'text','name'=>'voice_data','size'=>'50','value'=>$voice_data);
	$INPUTS['HINT']				= array('type'=>'textarea','name'=>'hint','cols'=>'50','rows'=>'5','value'=>$hint);
	$INPUTS['EXPLANATION']			= array('type'=>'textarea','name'=>'explanation','cols'=>'50','rows'=>'5','value'=>$explanation);
	$INPUTS['PARAMETER']			= array('type'=>'text','name'=>'parameter','size'=>'50','value'=>$parameter);
	$INPUTS['FIRSTPROBLEM']			= array('type'=>'textarea','name'=>'first_problem','cols'=>'50','rows'=>'5','value'=>$first_problem);
	$INPUTS['LATTERPROBLEM']		= array('type'=>'textarea','name'=>'latter_problem','cols'=>'50','rows'=>'5','value'=>$latter_problem);
	if ($form_type != 4 && $form_type != 8 && $form_type != 10) {
		$INPUTS['SELECTIONWORDS'] 	= array('type'=>'text','name'=>'selection_words','size'=>'50','value'=>$selection_words);
	} else {
		$INPUTS['SELECTIONWORDS'] 	= array('type'=>'textarea','name'=>'selection_words','cols'=>'50','rows'=>'5','value'=>$selection_words);
	}
	$INPUTS['CORRECT']			= array('type'=>'text','name'=>'correct','size'=>'50','value'=>$correct);
	$INPUTS['OPTION1']			= array('type'=>'text','name'=>'option1','size'=>'50','value'=>$option1);
//	$INPUTS['OPTION2']			= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$option2);	// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加
	$INPUTS['OPTION3']			= array('type'=>'text','name'=>'option3','size'=>'50','value'=>$option3);
	$INPUTS['OPTION4']			= array('type'=>'text','name'=>'option4','size'=>'50','value'=>$option4);
//	$INPUTS['OPTION5']			= array('type'=>'text','name'=>'option5','size'=>'50','value'=>$option5);	// del hasegawa 2016/09/29 国語文字数カウント

	// add start hasegawa 2016/09/29 国語文字数カウント
	// form_type == 3 && write_type == 2の場合はOPTION5をCOUNTSETTINGに
	if($form_type == 3) {

		// ---- add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// 解答欄行数と解答欄サイズの項目はoption2に<>区切りで格納されています
		if($option2) {
			list($input_row,$input_size) = get_option2($option2);
		}

		$INPUTS['INPUTROW'] = array('type'=>'text','name'=>'input_row','size'=>'50','value'=>$input_row);
		$INPUTS['INPUTSIZE'] = array('type'=>'text','name'=>'input_size','size'=>'50','value'=>$input_size);
		$input_size_att = "<br><span style=\"color:red;\">※指定する場合は、最大40までの値を設定してください。</span>";
		$INPUTS['INPUTSIZEATT'] = array('result'=>'plane','value'=>$input_size_att);

		// ---- add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加

		// if($_SESSION['t_practice']['write_type'] == 2) {												// del 2018/05/14 yoshizawa 理科社会対応
		if($_SESSION['t_practice']['write_type'] == 2 || $_SESSION['t_practice']['write_type'] == 16) {	// add 2018/05/14 yoshizawa 理科社会対応

			if ($option5 == "") { $option5 = "false"; }
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_off");
			$newform->set_form_check($option5);
			$newform->set_form_value("false");
			$count_set_off = $newform->make();
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_on");
			$newform->set_form_check($option5);
			$newform->set_form_value("true");
			$count_set_on = $newform->make();

			$count_html = "<tr>\n";
			$count_html .= "<td class=\"member_form_menu\">文字数カウント設定</td>\n";
			$count_html .= "<td class=\"member_form_cell\">\n";
			$count_html .= $count_set_off . "<label for=\"count_set_off\">設定しない</label> / " . $count_set_on . "<label for=\"count_set_on\">設定する</label>";
			$count_html .= "</td>\n";
			$count_html .= "</tr>\n";
			$INPUTS['COUNTSETTING'] = array('result'=>'plane','value'=>$count_html);
		}
	} else {
		$INPUTS['OPTION2']		= array('type'=>'text','name'=>'option2','size'=>'50','value'=>$option2);	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		$INPUTS['OPTION5'] 		= array('type'=>'text','name'=>'option5','size'=>'50','value'=>$option5);

	}
	// add start hasegawa 2016/09/29
	// add start karasawa 2019/07/23 BUD英語解析開発
	// upd start hirose 2020/09/18 テスト標準化開発
	// if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10){
	if($_SESSION['sub_session']['select_course']['form_type'] == 3 || $_SESSION['sub_session']['select_course']['form_type'] == 4 || $_SESSION['sub_session']['select_course']['form_type'] == 10 || $_SESSION['sub_session']['select_course']['form_type'] == 14){
	// upd end hirose 2020/09/18 テスト標準化開発
		if ($option3 == "") { $option3 = "0"; }
		$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
		$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
		$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);
	}
	// add end karasawa 2019/07/23 BUD英語解析開発
	//upd start 2018/06/05 yamaguchi 学力診断テスト画面 回答目安時間非表示
	//$INPUTS['STANDARDTIME']			= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$standard_time);
	if ($_SESSION['t_practice']['test_type'] != 4) {
		$INPUTS['STANDARDTIME']			= array('type'=>'text','name'=>'standard_time','size'=>'5','value'=>$standard_time);
	}else{
		$standard_time_html = "none_display";
		$INPUTS['STANDARDTIMEHTML'] 	= array('result'=>'plane','value'=>$standard_time_html);

	}
	//upd end 2018/06/05 yamaguchi
	if ($_SESSION['t_practice']['test_type'] == "4") {
		$INPUTS['PROBLEMPOINT']		= array('type'=>'text','name'=>'problem_point','size'=>'5','value'=>$problem_point);
	} else {
		$INPUTS['PROBLEMPOINT']		= array('result'=>'plane','value'=>'--');
	}
	$INPUTS['BOOKUNITID']			= array('type'=>'text','name'=>'book_unit_id','size'=>'50','value'=>$book_unit_id);
	$INPUTS['BOOKUNITATT']			= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITID']			= array('type'=>'text','name'=>'lms_unit_id','size'=>'50','value'=>$lms_unit_id);
//	$INPUTS[LMSUNITATT] 			= array('result'=>'plane','value'=>$book_unit_att);
	$INPUTS['LMSUNITATT']			= array('result'=>'plane','value'=>$lms_unit_att);	// 2012/03/13 add ozaki
//	$INPUTS[UPNAVISECTIONID] 		= array('type'=>'text','name'=>'upnavi_section_id','size'=>'50','value'=>$upnavi_section_id);	//	add koike 2012/06/12
	$INPUTS['UPNAVISECTIONNUM']		= array('type'=>'text','name'=>'upnavi_section_num','size'=>'50','value'=>$upnavi_section_num);	//	add oda 2012/07/05
	$INPUTS['UPNAVISECTIONATT']		= array('result'=>'plane','value'=>$upnavi_section_att);	//	add koike 2012/06/12

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
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
	// upd start hirose 2020/10/01 テスト標準化開発
	// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$_POST['problem_num']."')\">\n";
	// upd start hirose 2020/12/15 テスト標準化開発 定期テスト
	// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$_POST['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$_SESSION['t_practice']['default_test_num']."')\">\n";
	if($_SESSION['t_practice']['test_type'] == 4){
		$disp_id = $_SESSION['t_practice']['default_test_num'];
	}else{
		$disp_id = $_SESSION['t_practice']['course_num'];
	}
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$_POST['problem_table_type']."','".$_POST['problem_num']."','".$_SESSION['t_practice']['test_type']."','".$disp_id."')\">\n";
	// upd end hirose 2020/12/15 テスト標準化開発 定期テスト
	// upd end hirose 2020/10/01 テスト標準化開発
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
function check() {
	if ($_POST['problem_table_type'] == 1) {
		$ERROR = surala_add_check();
	} elseif ($_POST['problem_table_type'] == 2) {
		$ERROR = test_add_check();
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
function check_html() {
	if ($_POST['problem_table_type'] == 1) {
		$html = surala_add_check_html();
	} elseif ($_POST['problem_table_type'] == 2) {
		$html = test_add_check_html();
	}

	return $html;
}


/**
 * 教科書単元　修正・削除処理
 * 注）データ削除処理の時、ms_test_problemは別単元で使用している可能性が有る為、削除処理は行わない
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = array();

	if (MODE == "view") {
		$INSERT_DATA['problem_point'] 	= $_POST['problem_point'];
//		$INSERT_DATA[upd_syr_id] 		= ;
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		if ($_POST['problem_table_type'] == 1) {
			$ERROR = problem_attribute_add($_POST['block_num'],$_POST['standard_time']);
		} elseif ($_POST['problem_table_type'] == 2) {
			$ERROR = problem_test_upd($_POST['problem_num']);
		}
		if ($ERROR) { return $ERROR; }
	} elseif (MODE == "delete") {
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";
	}
/*
	if ($_SESSION['t_practice']['default_test_num']) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
	} else {
		$default_test_num = 0;
	}
*/
	$default_test_num = $_POST['default_test_num'];

	$L_DEFAULT_TEST_NUM[] = $default_test_num;
	$where = " WHERE default_test_num='".$default_test_num."'".
				" AND problem_num='".$_POST['problem_num']."' ";
				" AND problem_table_type='".$_POST['problem_table_type']."'";

	if ($_SESSION['t_practice']['gknn']) {
		$where .= " AND gknn='".$_SESSION['t_practice']['gknn']."'";
	}

	if ($_SESSION['t_practice']['core_code']) {
		list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
//		$where .= " AND mtdp.term_bnr_ccd='".$bnr_cd."'";	//	del koike 2012/06/20
//		$where .= " AND mtdp.term_kmk_ccd='".$kmk_cd."'";	//	del koike 2012/06/20
		$where .= " AND term_bnr_ccd='".$bnr_cd."'";	//	add koike 2012/06/20
		$where .= " AND term_kmk_ccd='".$kmk_cd."'";	//	add koike 2012/06/20
	}
	$where .= " LIMIT 1;";

	$ERROR = $cdb->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);


	//教科書単元と問題の紐付け登録
	//$ERROR = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['book_unit_id']));	//	del $ERROR ookawara 2012/06/15
	//$ERROR = lms_unit_test_problem_add($_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']));	//	del $ERROR ookawara 2012/06/15
	book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['book_unit_id']), $ERROR);	//	add $ERROR ookawara 2012/06/15
	lms_unit_test_problem_add($_POST['problem_num'],$_POST['problem_table_type'],explode("//",$_POST['lms_unit_id']),explode("//",$_POST['book_unit_id']), $ERROR);	//	add $ERROR ookawara 2012/06/15
	//upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'], $ERROR);	//	add koike 2012/06/15	//  2012/07/26 del oda
	upnavi_section_test_problem_add($_POST['problem_num'], $_POST['upnavi_section_num'],$_POST['problem_table_type'], $ERROR);	//	2012/07/26 add oda

	// del oda 2014/11/19 課題要望一覧No390
	//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
	//ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $_POST['problem_num'], $_POST['problem_table_type'], $ERROR);

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
function problem_test_upd($problem_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();
	$ins_data = array();
	$ins_data = $_POST;
	$array_replace = new array_replace();

	if ($_SESSION['sub_session']['select_course']['form_type'] == 1) {
		if ($ins_data['selection_words'] && $ins_data['selection_words'] === "0") {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 2) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 3) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}
/*		// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		if ($ins_data['option2']) {
			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();
		}
*/
		// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// form_type=3の場合は解答欄行数と解答欄サイズの値を<>で区切ってoption2に格納します。
		if(!$ins_data['option2']) {
			$ins_data['option2'] = make_option2($ins_data['input_row'],$ins_data['input_size']);

			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();

			unset($ins_data['input_row']);
			unset($ins_data['input_size']);
		}

		// if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		// del 2016/06/02 yoshizawa 手書き認識
		// 手書き認識の設定値パラメータを保持します。
		// すでにすらら問題でoption3を使用していたのでoption4を使用しています。
		//if ($ins_data['option4']) { unset($ins_data['option4']); }
		// <<<
		// if ($ins_data['option5']) { unset($ins_data['option5']); }		// del hasegawa 2016/09/29 国語文字数カウント
		if ($ins_data['option5'] != "true") { $ins_data['option5'] = ""; }	// add hasegawa 2016/10/11 国語文字数カウント true が設定されているときのみ登録

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 4) {
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 5) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['selection_words']) { unset($ins_data['selection_words']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 8) {
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 10) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		//if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del 2019/07/23 karasawa BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 11) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add start hasegawa 2016/06/01 作図ツール
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 13) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		// drawing_typeはoption1にセット
		$ins_data['option1'] = $ins_data['drawing_type'];
		unset($ins_data['drawing_type']);

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add end hasegawa 2016/06/01
	// add start hirose 2020/09/21 テスト標準化開発
	} elseif ($_SESSION['sub_session']['select_course']['form_type'] == 14) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['option4']) {
			$array_replace->set_line($ins_data['option4']);
			$ins_data['option4'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add end hirose 2020/09/21 テスト標準化開発
	}
	// add start karasawa 2019/07/24 BUD英語解析開発
	// upd start hirose 2020/09/21 テスト標準化開発
	// if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10){
	if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10 || $CHECK_DATA['form_type'] == 14){
	// upd end hirose 2020/09/21 テスト標準化開発
		if ($ins_data['option3']) {
			$array_replace->set_line($ins_data['option3']);
			$ins_data['option3'] = $array_replace->replace_line();
		}
	}
	// add end karasawa 2019/07/24
	foreach ($ins_data AS $key => $val) {
		if (
			$key == "action"
			 || $key == "problem_point"
			 || $key == "default_test_num"
			 || $key == "book_unit_id"
			 || $key == "lms_unit_id"
			 || $key == "upnavi_section_num"	//	add koike 2012/06/15
			 || $key == "problem_table_type"
		) { continue; }
		$INSERT_DATA[$key] = $val;
	}
//	$INSERT_DATA[upd_syr_id] 		= ;
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 		= "now()";

	$where = " WHERE problem_num='".$problem_num."';";

	$ERROR = $cdb->update(T_MS_TEST_PROBLEM,$INSERT_DATA,$where);

	return $ERROR;
}


/**
 * 教科書単元　csvエクスポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 */
function csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['problem'.$_SESSION['t_practice']['test_type']],1,1);
	if ($ERROR) { return $ERROR; }

	//テストタイプでファイル名を変える
	if ($_SESSION['t_practice']['test_type'] == 1) {
		$filename = "test_problem_".$_SESSION['t_practice']['core_code'].".csv";
	} elseif ($_SESSION['t_practice']['test_type'] == 4) {
		list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
		$filename = "test_problem_".$default_test_num.".csv";
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
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode
 * @param mixed $csv_mode
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1',$csv_mode='1') {

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
		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//$csv_line .= "\"".$head_name."\",";
		if ($head_name == "standard_time") {
			if ($_SESSION['t_practice']['test_type'] != 4) {
				$csv_line .= "\"".$head_name."\",";
			}
		}else{
			$csv_line .= "\"".$head_name."\",";
		}
		//upd end 2018/06/06 yamaguchi
	}
	$csv_line .= "\n";
	if ($_SESSION['t_practice']['course_num']) {
		$join_and .= " AND mb.course_num='".$_SESSION['t_practice']['course_num']."'";
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
		$orderby = " ORDER BY disp_sort;";
	} elseif ($_SESSION['t_practice']['test_type'] == 4) {
		$join_sql = " INNER JOIN ".T_MS_TEST_DEFAULT." mtd ON mtd.mk_flg='0'".
				" AND mtd.default_test_num=mtdp.default_test_num".
				" AND mtd.course_num='".$_SESSION['t_practice']['course_num']."'".
			" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.mk_flg='0'".
				" AND tgl.default_test_num=mtd.default_test_num".
			" INNER JOIN ".T_MS_BOOK_GROUP." mtg ON mtg.mk_flg='0' AND mtg.test_group_id=tgl.test_group_id".
				" AND mtg.test_gknn='".$_SESSION['t_practice']['gknn']."'";
		if ($_SESSION['t_practice']['default_test_num']) {
			$orderby = " ORDER BY disp_sort;";
		} else {
			$orderby = " ORDER BY problem_num,problem_table_type,default_test_num;";
		}
	}
	$sql = "CREATE TEMPORARY TABLE test_problem_list ".
		"SELECT ".
		"mtdp.default_test_num,".
		"mtdp.problem_table_type,".
		"mtdp.problem_num,".
		"p.form_type,".
		"mpa.standard_time,".
		"mtdp.problem_point,".
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
		"mtdp.disp_sort".
		" FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
		$join_sql.
		" LEFT JOIN ".T_MS_TEST_PROBLEM." mtp ON mtp.mk_flg='0' AND mtp.problem_num=mtdp.problem_num".
		" WHERE mtdp.mk_flg='0'".
		" AND mtdp.problem_table_type='2'".
		"".
		$where.
		" GROUP BY mtdp.default_test_num,mtdp.problem_table_type,mtdp.problem_num;";
//echo $sql."<br><br>";
	$cdb->exec_query($sql);

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		$orderby;
//echo $sql."<br><br>";
	$L_CSV = array();
	$L_CSV_LINE = array();
	if ($result = $cdb->query($sql)) {
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
				if ($key == "default_test_num") {
					if ($L_CSV[$j]['default_test_num']) {
//						$L_CSV[$j]['default_test_num'] .= "<>";	// 2012/03/01 del ozaki
						$L_CSV[$j]['default_test_num'] .= "//";	// 2012/03/01 add ozaki
					}
					$L_CSV[$j]['default_test_num'] .= $list['default_test_num'];
				} elseif ($key == "disp_sort") {
					if ($L_CSV[$j]['disp_sort']) {
//						$L_CSV[$j]['disp_sort'] .= "<>";	// 2012/03/01 del ozaki
						$L_CSV[$j]['disp_sort'] .= "//";	// 2012/03/01 add ozaki
					}
					if ($_SESSION['t_practice']['test_type'] == 1) {
						$L_CSV[$j]['disp_sort'] .= $j;
					} elseif ($_SESSION['t_practice']['test_type'] == 4) {
						if ($_SESSION['t_practice']['default_test_num']) {
							$L_CSV[$j]['disp_sort'] .= $j;
						} else {
							$L_CSV[$j]['disp_sort'] .= $list['disp_sort'];
						}
					}
				} elseif ($key == "problem_point") {
					if ($L_CSV[$j]['problem_point']) {
//						$L_CSV[$j]['problem_point'] .= "<>";	// 2012/03/01 del ozaki
						$L_CSV[$j]['problem_point'] .= "//";	// 2012/03/01 add ozaki
					}
					$L_CSV[$j]['problem_point'] .= $list['problem_point'];
				} elseif ($key == "problem_type") {
					if ($list['problem_table_type'] == 1) {
						$table_type = "surala";
					} elseif ($list['problem_table_type'] == 2) {
						$table_type = "test";
					}
					$L_CSV[$j]['problem_type'] = $table_type;
				} elseif ($key == "block_num") {
					if ($list['problem_table_type'] == 1) {
						$sql2  = "SELECT ".
							"p.block_num," .
							"p.display_problem_num," .
							"unit.unit_num," .
							"unit.unit_key" .
							" FROM " . T_PROBLEM ." p".
							" INNER JOIN ".T_BLOCK." block ON block.block_num=p.block_num AND block.state='0'".
							" INNER JOIN ".T_UNIT." unit ON block.unit_num=unit.unit_num AND unit.state='0'".
							" WHERE p.problem_num='".$list['problem_num']."'".
							" AND p.state='0'";
						if ($result2 = $cdb->query($sql2)) {
							$list2 = $cdb->fetch_assoc($result2);
						}
						$block_num = $list2['block_num'];
						$display_problem_num = $list2['display_problem_num'];
						$unit_key = $list2['unit_key'];
					} elseif ($list['problem_table_type'] == 2) {
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
					$L_CSV[$j]['block_num'] = $block_num;
				} elseif ($key == "unit_key") {
					if ($list['problem_table_type'] == 1) {
						$unit_key_html = $unit_key."_".get_drill_count($list2['unit_num'],$block_num);
					} elseif ($list['problem_table_type'] == 2) {
						$unit_key_html = "";
					}
					$L_CSV[$j]['unit_key_html'] = $unit_key_html;
				} elseif ($key == "display_problem_num") {
					$L_CSV[$j]['display_problem_num'] = $j;
					// add start oda 2016/11/17 定期テストの場合は、disp_sortを出力する(重複の問題をチェックする為にdisp_sortを出力する)
					if ($_SESSION['t_practice']['test_type'] == 1) {
						$L_CSV[$j]['display_problem_num'] = $list['disp_sort'];
					}
					// add end oda 2016/11/17
				} elseif ($key == "book_unit_id") {
					if ($_SESSION['t_practice']['test_type'] == 1) {
						$where = " AND butp.default_test_num='0'";
					} elseif ($_SESSION['t_practice']['test_type'] == 4) {
						if ($_SESSION['t_practice']['default_test_num']) {
							list($test_group_id,$default_test_num) = explode("_",$_SESSION['t_practice']['default_test_num']);
//							$where = " AND butp.default_test_num='".$default_test_num."'";	// 2012/03/01 del ozaki
							$where = " AND butp.default_test_num='".$list['default_test_num']."'";	// 2012/03/01 add ozaki
						} else {
//							$where = " AND butp.default_test_num!='0'";	// 2012/03/01 del ozaki
							$where = " AND butp.default_test_num='".$list['default_test_num']."'";	// 2012/03/01 add ozaki
						}
					}
					$sql2 = "SELECT * FROM " .
						T_BOOK_UNIT_TEST_PROBLEM . " butp".
						" WHERE butp.mk_flg='0'".
						" AND butp.problem_table_type='".$list['problem_table_type']."'".
						" AND butp.problem_num='".$list['problem_num']."'".
						$where.
						" ORDER BY butp.book_unit_id";	// 2012/02/27 add ozaki
//						echo $sql2."<br><br>";
					if ($result2 = $cdb->query($sql2)) {
						$L_BOOK_UNIT = array();
						while ($list2 = $cdb->fetch_assoc($result2)) {
							if ($list2['book_unit_id']) { $L_BOOK_UNIT[$list2['book_unit_id']] = $list2['book_unit_id']; }
						}
					}
					if (isset($L_CSV[$j]['book_unit_id'])) {
						$L_CSV[$j]['book_unit_id'] .= "//";
					}
					$book_unit_id = "";
					if (is_array($L_BOOK_UNIT)) {
						$book_unit_id = implode("::",$L_BOOK_UNIT);
						$L_CSV[$j]['book_unit_id'] .= $book_unit_id;
					}
				} elseif ($key == "unit_num") {
					$sql2 = "SELECT * FROM " .
						T_BOOK_UNIT_LMS_UNIT . " bulu".
					" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM . " butp ON ".
						" bulu.book_unit_id = butp.book_unit_id".
						" AND bulu.problem_num = butp.problem_num".
						" AND bulu.problem_table_type = butp.problem_table_type".
						" AND butp.mk_flg=0".
//						" AND butp.default_test_num='".$default_test_num."'".	// 2012/03/01 del ozaki
						" AND butp.default_test_num='".$list['default_test_num']."'".	// 2012/03/01 add ozaki
					" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=bulu.unit_num AND unit.state='0' AND unit.display='1'".
						" WHERE bulu.mk_flg='0'".
						" AND bulu.problem_table_type='".$list['problem_table_type']."'".
						" AND bulu.problem_num='".$list['problem_num']."'".
					" ORDER BY bulu.book_unit_id,bulu.unit_num";	// 2012/02/27 add ozaki
//						echo $sql2."<br><br>";
					if ($result2 = $cdb->query($sql2)) {
						$L_LMS_UNIT = array();
						$L_LMS_UNIT_KEY = array();
						while ($list2 = $cdb->fetch_assoc($result2)) {
							if ($list2['unit_num']) { $L_LMS_UNIT[$list2['book_unit_id']][$list2['unit_num']] = $list2['unit_num']; }
							if ($list2['unit_key']) { $L_LMS_UNIT_KEY[$list2['book_unit_id']][$list2['unit_key']] = $list2['unit_key']; }
						}
					}
					if (isset($L_CSV[$j]['unit_num'])) {
						$L_CSV[$j]['unit_num'] .= "//";
					}
					$unit_num = "";
					if (is_array($L_LMS_UNIT)) {
						$k=0;
						foreach ($L_BOOK_UNIT as $key => $val) {
							if ($k > 0) { $unit_num .= "::"; }
							if (is_array($L_LMS_UNIT[$val])) {
								$unit_num .= implode("<>",$L_LMS_UNIT[$val]);
							}
							$k = 1;
						}
						$L_CSV[$j]['unit_num'] .= $unit_num;
					}
//pre($j." == ".$L_CSV[$j]['unit_num']);
				} elseif ($key == "lms_unit_id") {
					$lms_unit_id = "";
					if (is_array($L_LMS_UNIT_KEY)) {
						$k=0;
						foreach ($L_LMS_UNIT_KEY as $l_lms_unit_key) {
							if ($k > 0) { $lms_unit_id .= "::"; }
							$lms_unit_id .= implode("<>",$l_lms_unit_key);
							$k = 1;
						}
						$L_CSV[$j]['lms_unit_id'] = $lms_unit_id;
					}
				//	add koike 2012/06/20 start
				} elseif ($key == "upnavi_section_num") {

					$sql2 = " SELECT " .
							"  usp.upnavi_section_num, " .
							"  us.upnavi_chapter_num " .
							" FROM ".T_UPNAVI_SECTION_PROBLEM." usp ".
							" INNER JOIN ".T_UPNAVI_SECTION." us ON usp.upnavi_section_num = us.upnavi_section_num AND us.mk_flg = '0' ".
							" WHERE usp.mk_flg='0'".
							"  AND  usp.problem_num='".$list['problem_num']."'".
							" ORDER BY us.upnavi_chapter_num, usp.upnavi_section_num ;";

					//	add ookawara 2012/11/27 start
					$UCN_L = array();
					$upnavi_chapter_num = "";
					$upnavi_section_num = "";
					if ($result2 = $cdb->query($sql2)) {
						while ($list2 = $cdb->fetch_assoc($result2)) {
							$set_upnavi_chapter_num = $list2['upnavi_chapter_num'];
							$set_upnavi_section_num = $list2['upnavi_section_num'];
							$UCN_L[$set_upnavi_chapter_num][] = $set_upnavi_section_num;
						}
					}
					if (is_array($UCN_L)) {
						foreach ($UCN_L AS $set_upnavi_chapter_num => $upnavi_section_num_list) {
							if ($upnavi_chapter_num != "") {
								$upnavi_chapter_num .= "::";
							}
							$upnavi_chapter_num .= $set_upnavi_chapter_num;

							if ($upnavi_section_num != "") {
								$upnavi_section_num .= "::";
							}
							//$upnavi_section_num_list = array_unique($upnavi_section_num_list);	// 課題要望一覧No317 重複データでも出力する（画面の表示と同じにする）
							$upnavi_section_num .= implode("&lt;&gt;",$upnavi_section_num_list);
						}
					}

					$L_CSV[$j]['upnavi_chapter_num'] = $upnavi_chapter_num;
					$L_CSV[$j]['upnavi_section_num'] = $upnavi_section_num;
					//	add ookawara 2012/11/27 end

				} elseif ($key == "upnavi_chapter_num") {	// 2012/07/04 add oda upnavi_section_numでセットしているので無視
				//	add koike 2012/06/20 end
				} elseif ($key == "test_type") {
					if ($list['problem_table_type'] == 1) {
						$test_type = "";
					} elseif ($list['problem_table_type'] == 2) {
						$test_type = "";
					}
					$test_type = $_SESSION['t_practice']['test_type'];
					$L_CSV[$j]['test_type'] = $test_type;
				} elseif ($key == "term") {
					if ($list['problem_table_type'] == 1) {
						$term = "";
					} elseif ($list['problem_table_type'] == 2) {
						$term = "";
					}
					$L_CSV[$j]['term'] = $term;
				} elseif ($key == "term3_kmk_ccd") {
					if ($_SESSION['t_practice']['test_type'] == "1") {
						list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
						if ($bnr_cd == "C000000001") {
							$L_CSV[$j]['term3_kmk_ccd'] = $kmk_cd;
						} else {
							$L_CSV[$j]['term3_kmk_ccd'] = "";
						}
					} else {
						$L_CSV[$j]['term3_kmk_ccd'] = $kmk_cd;
					}
				} elseif ($key == "term2_kmk_ccd") {
					if ($_SESSION['t_practice']['test_type'] == "1") {
						list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
						if ($bnr_cd == "C000000002") {
							$L_CSV[$j]['term2_kmk_ccd'] = $kmk_cd;
						} else {
							$L_CSV[$j]['term2_kmk_ccd'] = "";
						}
					} else {
						$L_CSV[$j]['term2_kmk_ccd'] = "";
					}
				} elseif ($key == "book_id") {
					if ($list['problem_table_type'] == 1) {
						$book_id = "";
					} elseif ($list['problem_table_type'] == 2) {
						$book_id = "";
					}
					$L_CSV[$j]['book_id'] = $book_id;
				} elseif ($key == "display_problem_num") {
					if ($list['problem_table_type'] == 1) {
						$display_problem_num = $display_problem_num;
					} elseif ($list['problem_table_type'] == 2) {
						$display_problem_num = "";
					}
					$L_CSV[$j]['display_problem_num'] = $display_problem_num;
				} elseif ($key == "form_type") {
					if ($list['problem_table_type'] == 1) {
						$form_type = "";
					} elseif ($list['problem_table_type'] == 2) {
						$form_type = $problem_data['form_type'];
					}
					$L_CSV[$j]['form_type'] = $form_type;
				} elseif ($key == "question") {
					if ($list['problem_table_type'] == 1) {
						$question = "";
					} elseif ($list['problem_table_type'] == 2) {
						$question = $problem_data['question'];
					}
					$L_CSV[$j]['question'] = $question;
				} elseif ($key == "problem") {
					if ($list['problem_table_type'] == 1) {
						$problem = "";
					} elseif ($list['problem_table_type'] == 2) {
						$problem = $problem_data['problem'];
					}
					$L_CSV[$j]['problem'] = $problem;
				} elseif ($key == "voice_data") {
					if ($list['problem_table_type'] == 1) {
						$voice_data = "";
					} elseif ($list['problem_table_type'] == 2) {
						$voice_data = $problem_data['voice_data'];
					}
					$L_CSV[$j]['voice_data'] = $voice_data;
				} elseif ($key == "hint") {
					if ($list['problem_table_type'] == 1) {
						$hint = "";
					} elseif ($list['problem_table_type'] == 2) {
						$hint = $problem_data['hint'];
					}
					$L_CSV[$j]['hint'] = $hint;
				} elseif ($key == "explanation") {
					if ($list['problem_table_type'] == 1) {
						$explanation = "";
					} elseif ($list['problem_table_type'] == 2) {
						$explanation = $problem_data['explanation'];
					}
					$L_CSV[$j]['explanation'] = $explanation;
				} elseif ($key == "parameter") {
					if ($list['problem_table_type'] == 1) {
						$parameter = "";
					} elseif ($list['problem_table_type'] == 2) {
						$parameter = $problem_data['parameter'];
					}
					$L_CSV[$j]['parameter'] = $parameter;
				} elseif ($key == "first_problem") {
					if ($list['problem_table_type'] == 1) {
						$first_problem = "";
					} elseif ($list['problem_table_type'] == 2) {
						$first_problem = $problem_data['first_problem'];
					}
					$L_CSV[$j]['first_problem'] = $first_problem;
				} elseif ($key == "latter_problem") {
					if ($list['problem_table_type'] == 1) {
						$latter_problem = "";
					} elseif ($list['problem_table_type'] == 2) {
						$latter_problem = $problem_data['latter_problem'];
					}
					$L_CSV[$j]['latter_problem'] = $latter_problem;
				} elseif ($key == "selection_words") {
					if ($list['problem_table_type'] == 1) {
						$selection_words = "";
					} elseif ($list['problem_table_type'] == 2) {
						$selection_words = $problem_data['selection_words'];
					}
					$L_CSV[$j]['selection_words'] = $selection_words;
				} elseif ($key == "correct") {
					if ($list['problem_table_type'] == 1) {
						$correct = "";
					} elseif ($list['problem_table_type'] == 2) {
						$correct = $problem_data['correct'];
					}
					$L_CSV[$j]['correct'] = $correct;
				} elseif ($key == "option1") {
					if ($list['problem_table_type'] == 1) {
						$option1 = "";
					} elseif ($list['problem_table_type'] == 2) {
						$option1 = $problem_data['option1'];
					}
					$L_CSV[$j]['option1'] = $option1;
				} elseif ($key == "option2") {
					if ($list['problem_table_type'] == 1) {
						$option2 = "";
					} elseif ($list['problem_table_type'] == 2) {
						$option2 = $problem_data['option2'];
					}
					$L_CSV[$j]['option2'] = $option2;
				} elseif ($key == "option3") {
					if ($list['problem_table_type'] == 1) {
						$option3 = "";
					} elseif ($list['problem_table_type'] == 2) {
						$option3 = $problem_data['option3'];
					}
					$L_CSV[$j]['option3'] = $option3;
				} elseif ($key == "option4") {
					if ($list['problem_table_type'] == 1) {
						$option4 = "";
					} elseif ($list['problem_table_type'] == 2) {
						$option4 = $problem_data['option4'];
					}
					$L_CSV[$j]['option4'] = $option4;
 				} elseif ($key == "option5") {
					if ($list['problem_table_type'] == 1) {
						$option5 = "";
					} elseif ($list['problem_table_type'] == 2) {
						$option5 = $problem_data['option5'];
					}
					$L_CSV[$j]['option5'] = $option5;
				//add start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
				} elseif ($key == "standard_time") {
					if ($_SESSION['t_practice']['test_type'] != 4) {
						$L_CSV[$j]['standard_time'] = $list['standard_time'];
					}
				//add end 2018/06/06 yamaguchi
				} else {
					$L_CSV[$j][$key] = $list[$key];
				}
				$i++;
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
//pre($_SESSION);
//pre($L_CSV_COLUMN);
//pre($csv_line);
//exit;

	return array($csv_line,$ERROR);
}


/**
 * 問題　csvインポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array
 */
function csv_import() {
	//global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR =array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	// ファイルチェック
	if (!$file_tmp_name) {
		$ERROR[] = "問題ファイルファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "教科書単元ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "教科書単元ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return array($html,$ERROR);
	}

	// テスト問題読込
	$L_IMPORT_LINE = array();

	//$L_IMPORT_LINE = file($file_tmp_name);
	$handle = fopen($file_tmp_name,"r");

	// add start yoshizawa 2015/10/09
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
	// add end yoshizawa 2015/10/09

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

/*
	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	if (is_array($L_LIST_NAME)) {
		foreach ($L_LIST_NAME as $key => $val) {
			$L_LIST_NAME[$key] = trim($val);
			$L_LIST_NAME[$key] = replace_encode_sjis($L_LIST_NAME[$key]);
			$L_LIST_NAME[$key] = mb_convert_encoding($L_LIST_NAME[$key],"UTF-8","sjis-win");
		}
	}
*/

	$ERROR = array();
	$DATA_ERROR = array();
	$SYS_ERROR = null;

	//２行目以降＝登録データを形成
	for ($i = 1; $i <= count($L_IMPORT_LINE);$i++) {

		// 最終行読み飛ばし
		if (is_array($L_IMPORT_LINE[$i]) == false) {
			continue;
		}

		// 空行読み飛ばし
		$empty_check = preg_replace("/,/","",$L_IMPORT_LINE[$i]);
		if (count($empty_check) == 1) {
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}

		$L_VALUE = array();
		$CHECK_DATA = array();
		$INSERT_DATA = array();
		$L_DEFAULT_TEST_NUM = array();
		$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE = array();
		$L_MS_TEST_PROBLEM_INS_MODE = "";
//		$L_VALUE = explode(",",$import_line);
// 		$L_VALUE = explode(",",$import_line);
// 		if (!is_array($L_VALUE)) {
// 			$ERROR[] = $i."行目のcsv入力値が不正なのでスキップしました。<br>";
// 			continue;
// 		}

		foreach ($L_IMPORT_LINE[$i] as $key => $val) {

//			if ($val === "") { continue; }
			if ($L_LIST_NAME[$key] === "") { continue; }
			//	$val = preg_replace("/^\"|\"$/","",$val);
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);

			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$val = replace_encode_sjis($val);
			//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );

			if ( $code != 'UTF-8' ) {
				// add 2015/03/30 yoshizawa 課題要望一覧No.427 問題インポートで文字化け対応
				// 「集合A」や「集合B」など、1バイト文字と2バイト文字が複合している場合に
				// 特殊文字が誤った変換をする。対策として半角英字を全角に変換する。
				// ※オプション「A」で英数字を変換すると「<」と「>」まで全角にしてしまうため
				// 「R」で英字のみ全角にする。

				//add start okabe 2018/08/27  理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のために一旦、文字間に制御コード垂直タブ0x0bを入れる）
				$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
				$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
				$tmpx_write_type = 0;
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
					$tmpx_write_type = $list['write_type'];
				}
				if ($tmpx_write_type != 15) {
					//理科以外は従来と同じ
					$val = mb_convert_kana($val,"R","sjis-win");

				} else {
					//理科問題の場合
					//	12"form_type",
					//	13"question",
					//	14"problem",
					//	16"hint",
					//	17"explanation",
					//	19"first_problem",
					//	20"latter_problem",
					if ($L_LIST_NAME[$key] !== "question" && $L_LIST_NAME[$key] !== "problem" && $L_LIST_NAME[$key] !== "hint"
							&& $L_LIST_NAME[$key] !== "explanation"  && $L_LIST_NAME[$key] !== "first_problem"  && $L_LIST_NAME[$key] !== "latter_problem") {
						//特定のカラム以外は対処せず従来同様に処理させる
						$val = mb_convert_kana($val,"R","sjis-win");

					} else {
						//以下は、文字間へ制御コード0x0bを入れる処理
						$tmp_moji_ary = array();
						$tmp_fail_safe = 10000;
						$tmp_ps = 0;
						while($tmp_fail_safe > 0 && $tmp_ps < mb_strlen($val, "sjis-win")) {
							$tmp_moji_ary[] = mb_substr($val, $tmp_ps, 1, "sjis-win");
							$tmp_ps++;
							$tmp_fail_safe = $tmp_fail_safe - 1;
						}
						$val = implode("\x0b\x0b", $tmp_moji_ary);	//制御コード垂直タブ0x0bを文字間に2個入れて文字列を復元する
					}
				}
				//add end okabe 2018/08/27

				//--------------------------------------------------------------------------
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
			//$val = mb_convert_kana($val,"asKVn","UTF-8");	//del okabe 2018/08/22  理科問題 全角文字扱い

			//add start okabe 2018/08/27  理科問題 全角文字扱い（特定カラムで、文字間へ便宜的に追加した制御コード垂直タブ0x0bを削除して戻す）
			$tmpx_course_num = intval($_SESSION['t_practice']['course_num']);
			$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
			$tmpx_write_type = 0;
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$tmpx_write_type = $list['write_type'];
			}
			if ($tmpx_write_type != 15) {
				//理科 以外の場合（従来通り）
				$val = mb_convert_kana($val,"asKVn","UTF-8");

			} else {
				//理科問題の場合
				//	12"form_type",
				//	13"question",
				//	14"problem",
				//	16"hint",
				//	17"explanation",
				//	19"first_problem",
				//	20"latter_problem",
				if ($L_LIST_NAME[$key] !== "question" && $L_LIST_NAME[$key] !== "problem" && $L_LIST_NAME[$key] !== "hint"
						&& $L_LIST_NAME[$key] !== "explanation"  && $L_LIST_NAME[$key] !== "first_problem"  && $L_LIST_NAME[$key] !== "latter_problem" ) {
					//特定のカラム以外は対処せず従来同様に処理させる
					$val = mb_convert_kana($val,"asKVn","UTF-8");

				} else {
					//文字間に加えた2個の制御コード垂直タブ0x0bを消す
					$tmp_val = explode("\x0b\x0b", $val);
					$val = implode("", $tmp_val);
				}
			}
			//add end okabe 2018/08/27

			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;

		}
		if ($_SESSION['t_practice']['test_type'] == 1) {
			if ($CHECK_DATA['test_type'] != "1") {
				$ERROR[] = $i."行目のテストタイプが不正なのでスキップしました。<br>";
			}
			$L_DEFAULT_TEST_NUM[] = 0;
			if ($L_DEFAULT_TEST_NUM) {
				if (!$CHECK_DATA['book_unit_id']) {
					for ($j=0;$j<count($L_DEFAULT_TEST_NUM)-1;$j++) {
						$CHECK_DATA['book_unit_id'] .= "//";
					}
				}
				if (!$CHECK_DATA['unit_num']) {
					for ($j=0;$j<count($L_DEFAULT_TEST_NUM)-1;$j++) {
						$CHECK_DATA['unit_num'] .= "//";
					}
				}
			}
//echo " CHECK_DATA['book_unit_id']=".$CHECK_DATA['book_unit_id']." CHECK_DATA['unit_num']=".$CHECK_DATA['unit_num']."<br>";
			$CHECK_DATA['book_unit_id'] = explode("//",$CHECK_DATA['book_unit_id']);
			$CHECK_DATA['unit_num'] = explode("//",$CHECK_DATA['unit_num']);
//echo "L_DEFAULT_TEST_NUM_count=".count($L_DEFAULT_TEST_NUM)." CHECK_DATA['book_unit_id']=".count($CHECK_DATA['book_unit_id'])."<br>";
			if (count($L_DEFAULT_TEST_NUM) != count($CHECK_DATA['book_unit_id'])) {
				$ERROR[] = $i."行目の教科書単元の設定数が不正なのでスキップしました。<br>";
//				$ERROR[] = $i."行目の教科書単元の設定数が不正なのでスキップしました。".count($L_DEFAULT_TEST_NUM)." = ".count($CHECK_DATA['book_unit_id'])."<br>";
				continue;
			}
//echo "L_DEFAULT_TEST_NUM_count=".count($L_DEFAULT_TEST_NUM)." CHECK_DATA['unit_num']=".count($CHECK_DATA['unit_num'])."<br>";
			if (count($L_DEFAULT_TEST_NUM) != count($CHECK_DATA['unit_num'])) {
				$ERROR[] = $i."行目のLMS単元の設定数が不正なのでスキップしました。<br>";
//				$ERROR[] = $i."行目のLMS単元の設定数が不正なのでスキップしました。".count($L_DEFAULT_TEST_NUM)." = ".count($CHECK_DATA['unit_num'])."<br>";
				continue;
			}

			$L_DISP_SORT[0] = $CHECK_DATA['display_problem_num'];	// ソート順をdisplay_problem_num順にする

		} elseif ($_SESSION['t_practice']['test_type'] == 4) {
			if ($CHECK_DATA['test_type'] != "4") {
				$ERROR[] = $i."行目のテストタイプが不正なのでスキップしました。<br>";
			}
			$L_DEFAULT_TEST_NUM = explode("//",$CHECK_DATA['default_test_num']);
			$L_PROBLEM_POINT = explode("//",$CHECK_DATA['problem_point']);
			$L_DISP_SORT = explode("//",$CHECK_DATA['disp_sort']);

			if ($L_DEFAULT_TEST_NUM) {
				if (!$CHECK_DATA['book_unit_id']) {
					for ($j=0;$j<count($L_DEFAULT_TEST_NUM)-1;$j++) {
						$CHECK_DATA['book_unit_id'] .= "//";
					}
				}
				if (!$CHECK_DATA['unit_num']) {
					for ($j=0;$j<count($L_DEFAULT_TEST_NUM)-1;$j++) {
						$CHECK_DATA['unit_num'] .= "//";
					}
				}
			}
			$CHECK_DATA['book_unit_id'] = explode("//",$CHECK_DATA['book_unit_id']);
			$CHECK_DATA['unit_num'] = explode("//",$CHECK_DATA['unit_num']);
//echo "L_DEFAULT_TEST_NUM_count=".count($L_DEFAULT_TEST_NUM)." CHECK_DATA['book_unit_id']=".count($CHECK_DATA['book_unit_id'])."<br>";
			if (count($L_DEFAULT_TEST_NUM) != count($CHECK_DATA['book_unit_id'])) {
//				$ERROR[] = $i."行目の教科書単元の設定数が不正なのでスキップしました。".count($L_DEFAULT_TEST_NUM)." = ".count($CHECK_DATA['book_unit_id'])."<br>";
				$ERROR[] = $i."行目の教科書単元の設定数が不正なのでスキップしました。<br>";
				continue;
			}
			if (count($L_DEFAULT_TEST_NUM) != count($CHECK_DATA['unit_num'])) {
//				$ERROR[] = $i."行目のLMS単元の設定数が不正なのでスキップしました。".count($L_DEFAULT_TEST_NUM)." = ".count($CHECK_DATA['unit_num'])."<br>";
				$ERROR[] = $i."行目のLMS単元の設定数が不正なのでスキップしました。<br>";
				continue;
			}

			// 配点のセット数とテストの数が違う場合、配点を最初のものに共通化
			if (count($L_DEFAULT_TEST_NUM) != count($L_PROBLEM_POINT)) {
				$same_problem_point = $L_PROBLEM_POINT[0];
				unset($L_PROBLEM_POINT);
				foreach ($L_DEFAULT_TEST_NUM as $default_test_key => $default_test_num) {
					$L_PROBLEM_POINT[$default_test_key] = $same_problem_point;
				}
			}
		}

		foreach ($L_DEFAULT_TEST_NUM as $default_test_num) {
			if ($CHECK_DATA['problem_num']) {

				// update start oda 2014/11/19 課題要望一覧No390 データ存在チェックに学年と学期コードを追加
				//$sql = "SELECT default_test_num,problem_num,problem_table_type,disp_sort".
				//	" FROM ". T_MS_TEST_DEFAULT_PROBLEM .
				//	" WHERE default_test_num='".$default_test_num."'".
				//	" AND problem_num='".$CHECK_DATA['problem_num']."'".
				//	" AND mk_flg='0' LIMIT 1;";

				// 画面のSELECTの値から、学年とテスト時期を取得する
				$gknn_check = $_SESSION['t_practice']['gknn'];
				$term_bnr_ccd_check	= "";
				$term_kmk_ccd_check	= "";
				if ($_SESSION['t_practice']['core_code']) {
					list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
					$term_bnr_ccd_check	= $bnr_cd;
					$term_kmk_ccd_check	= $kmk_cd;
				}

				// CSVの学期コードを再設定する
				// 画面のSELECTを優先するのであれば、以下のif文をコメントにする
				if ($term_bnr_ccd_check == "C000000001") {
					$term_kmk_ccd_check = $CHECK_DATA['term3_kmk_ccd'];
				} elseif ($term_bnr_ccd_check == "C000000002") {
					$term_kmk_ccd_check = $CHECK_DATA['term2_kmk_ccd'];
				}

				$sql = "SELECT default_test_num,problem_num,problem_table_type,disp_sort".
						" FROM ". T_MS_TEST_DEFAULT_PROBLEM .
						" WHERE default_test_num='".$default_test_num."'".
						"   AND problem_num='".$CHECK_DATA['problem_num']."'".
						"   AND gknn='".$gknn_check."'".
						"   AND term_bnr_ccd='".$term_bnr_ccd_check."'".
						"   AND term_kmk_ccd='".$term_kmk_ccd_check."'".
						"   LIMIT 1;";
				//		"   AND mk_flg='0' LIMIT 1;";	// 削除フラグを参照すると復活ができないので、削除フラグも参照する
				// update end oda 2014/11/19

				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				$problem_num = $list['problem_num'];
				if ($problem_num) {
					$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$default_test_num] = "upd";
					$problem_table_type = $list['problem_table_type'];
				} else {
					$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$default_test_num] = "add";
				}
			} else {
				$L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$default_test_num] = "add";
			}
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

		if ($CHECK_DATA['problem_type'] == "surala") {
			$CHECK_DATA['problem_table_type'] = 1;
		} elseif ($CHECK_DATA['problem_type'] == "test") {
			$CHECK_DATA['problem_table_type'] = 2;
		} else {
			$ERROR[] = $i."行目の問題タイプが不正なのでスキップしました。<br>";
			continue;
		}

		//データチェック
		//$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);	//	del 2015/01/13 yoshizawa 課題要望一覧No.400 下に新規で作成
		//------------------------------------------------------------------------------------------
		//	add 2015/01/13 yoshizawa 課題要望一覧No.400
		//	すらら問題の際にエラーチェックが正常に行われないため、チェックロジックを別々にします。
		//------------------------------------------------------------------------------------------
		if ( $CHECK_DATA['problem_type'] == "surala" ) {
			//	問題が存在するかチェック
			$DATA_ERROR[$i] = check_data_surala($CHECK_DATA,"",$i);

		} else if ( $CHECK_DATA['problem_type'] == "test" ) {
			$DATA_ERROR[$i] = check_data($CHECK_DATA,"",$i);

		}

		if ($DATA_ERROR[$i]) { continue; }

		// テスト専用問題登録処理
		if ($CHECK_DATA['problem_type'] == "test") {
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
			$DATA_ERROR[$i] = problem_test_csv($problem_num,$CHECK_DATA,$L_MS_TEST_PROBLEM_INS_MODE);
			if ($DATA_ERROR[$i]) { continue; }
			foreach ($L_DEFAULT_TEST_NUM as $key => $default_test_num) {
				if (!$L_NEW_DISP_SORT[$default_test_num]) { $L_NEW_DISP_SORT[$default_test_num]++; }
				$INSERT_DATA['problem_point'] 		= $L_PROBLEM_POINT[$key];
				$INSERT_DATA['course_num'] 			= $_SESSION['t_practice']['course_num'];
				$INSERT_DATA['gknn'] 				= $_SESSION['t_practice']['gknn'];
				$INSERT_DATA['default_test_num'] 	= $default_test_num;
				if ($_SESSION['t_practice']['core_code']) {
					list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
					$INSERT_DATA['term_bnr_ccd']	= $bnr_cd;
					$INSERT_DATA['term_kmk_ccd']	= $kmk_cd;
				}

				// add start oda 2014/11/19 課題要望一覧No390 CSVの学期コードを優先して代入する。
				// CSVの学期コードを再設定する
				// 画面のSELECTを優先するのであれば、以下のif文をコメントにする
				if ($INSERT_DATA['term_bnr_ccd'] == "C000000001") {
					$INSERT_DATA['term_kmk_ccd'] = $CHECK_DATA['term3_kmk_ccd'];
				} elseif ($INSERT_DATA['term_bnr_ccd'] == "C000000002") {
					$INSERT_DATA['term_kmk_ccd'] = $CHECK_DATA['term2_kmk_ccd'];
				}
				// add end oda 2014/11/19

				if ($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$default_test_num] == "add") {
					if (!$L_DISP_SORT[$key]) {
						$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_TEST_DEFAULT_PROBLEM .
							" WHERE default_test_num='".$default_test_num."';";
						if ($result = $cdb->query($sql)) {
							$list = $cdb->fetch_assoc($result);
						}
						if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }
					} else {
						$disp_sort = $L_DISP_SORT[$key];
					}
					$INSERT_DATA['disp_sort'] 			= $disp_sort;
					$INSERT_DATA['problem_num'] 		= $problem_num;
					$INSERT_DATA['problem_table_type'] 	= 2;
				//	$INSERT_DATA[ins_syr_id] 			= ;
					$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['ins_date'] 			= "now()";
					$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 			= "now()";

					$DATA_ERROR[$i] = $cdb->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);

					if ($DATA_ERROR[$i]) { continue; }
				} elseif ($L_MS_TEST_DEFAULT_PROBLEM_INS_MODE[$default_test_num] == "upd") {
					if ($L_DISP_SORT[$key]) {
						$INSERT_DATA['disp_sort'] 	= $L_DISP_SORT[$key];
					}
					$INSERT_DATA['mk_flg'] 			= 0;
					$INSERT_DATA['mk_tts_id'] 		= "";
					$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
					$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 		= "now()";

					// add start oda 2014/10/14 課題要望一覧No352 UPDATE条件に学年と学期コードと月度を追加
					$where_add = " AND gknn='".$_SESSION['t_practice']['gknn']."'";
					if ($INSERT_DATA['term_bnr_ccd']) {
						$where_add .= " AND term_bnr_ccd = '".$INSERT_DATA['term_bnr_ccd']."'";
					}
					if ($INSERT_DATA['term_kmk_ccd']) {
						$where_add .= " AND term_kmk_ccd = '".$INSERT_DATA['term_kmk_ccd']."'";
					}
					// add end oda 2014/10/14

					$where = " WHERE default_test_num='".$default_test_num."'".
							" AND problem_table_type='2'".
							" AND problem_num='".$problem_num."'".
							$where_add.									// add oda 2014/10/14 課題要望一覧No352 条件を追加
							";";
					$DATA_ERROR[$i] = $cdb->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);
					if ($DATA_ERROR[$i]) { continue; }
				}
			}

			//教科書単元と問題の紐付け登録
			$ERRORS = array();	//	add koike 2012/06/15
			$DATA_ERROR[$i] = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$problem_num,2,$CHECK_DATA['book_unit_id'], $ERRORS);	//	add $ERRORS ookawara 2012/06/15
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 教科書単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			$DATA_ERROR[$i] = lms_unit_test_problem_add($problem_num,2,$CHECK_DATA['unit_num'],$CHECK_DATA['book_unit_id'], $ERRORS);	//	add $ERRORS ookawara 2012/06/15
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 LMS単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			//$DATA_ERROR[$i] = upnavi_section_test_problem_add($CHECK_DATA['problem_num'], $CHECK_DATA['upnavi_section_num'], $ERRORS);	//	add koike 2012/06/15	// 2012/07/26 del oda
			//$DATA_ERROR[$i] = upnavi_section_test_problem_add($CHECK_DATA['problem_num'], $CHECK_DATA['upnavi_section_num'], "2", $ERRORS);	// 2012/07/26 add oda	// 2012/07/30 del oda
			$DATA_ERROR[$i] = upnavi_section_test_problem_add($problem_num, $CHECK_DATA['upnavi_section_num'], "2", $ERRORS);										// 2012/07/30 add oda
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 学力Upナビ子単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			// del oda 2014/11/19 課題要望一覧No390
			//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
			//$DATA_ERROR[$i] = ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $problem_num, 2, $ERROR);

			if ($DATA_ERROR[$i]) { continue; }
		// すらら問題登録処理
		} elseif ($CHECK_DATA['problem_type'] == "surala") {

			if (!$CHECK_DATA['problem_num']) {
				// update start oda 2020/05/22 すららの問題番号を指定していない場合はエラー
			//add start okabe 2020/05/01 定期テストアップ不具合(内野様ご報告障害)
// 				$sql = "SELECT MAX(problem_num) AS max_num FROM ".T_PROBLEM;
// 				if ($result = $cdb->query($sql)) {
// 					$list = $cdb->fetch_assoc($result);
// 				}
// 				if ($list['max_num']) { $problem_num = $list['max_num'] + 1; } else { $problem_num = 1; }
				$SYS_ERROR[$i][] = $i."行目 システムエラー：問題番号[".$CHECK_DATA['problem_num']."]取得に失敗しました。<br>";
				continue;
			} else {
			//start end okabe 2020/05/01
			// update end oda 2020/05/22

				$sql = "SELECT problem_num FROM ".T_PROBLEM.
					" WHERE state='0'".
					" AND block_num='".$CHECK_DATA['block_num']."'".
					" AND problem_num='".$CHECK_DATA['problem_num']."'".
					"";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if ($list['problem_num']) {
					$problem_num = $list['problem_num'];
				} else {
					$SYS_ERROR[$i][] = $i."行目 システムエラー：問題番号[".$CHECK_DATA['problem_num']."]取得に失敗しました。<br>";
					continue;
				}
			}

			foreach ($L_DEFAULT_TEST_NUM as $key => $default_test_num) {
				if (!$CHECK_DATA['disp_sort']) {
					$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_TEST_DEFAULT_PROBLEM .
						" WHERE default_test_num='".$default_test_num."';";
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
					}
					if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }
				} else {
					$disp_sort = $L_DISP_SORT[$key];
				}

				// update start oda 2020/05/22 MS_TEST_DEFAULT_PROBLEMのデータ存在チェック条件変更
// 				if ($problem_num > 0) {	//add okabe 2020/05/01 定期テストアップ不具合(内野様ご報告障害)
// 				$sql = "SELECT * FROM " . T_MS_TEST_DEFAULT_PROBLEM .
// 					" WHERE default_test_num='".$default_test_num."'".
// 					" AND problem_num='".$problem_num."' AND problem_table_type='1';";

				list($bnr_cd,$kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);

				if ($bnr_cd == "C000000001") {
					$kmk_cd = $CHECK_DATA['term3_kmk_ccd'];
				} elseif ($bnr_cd == "C000000002") {
					$kmk_cd = $CHECK_DATA['term2_kmk_ccd'];
				}

				$sql = "SELECT * FROM " . T_MS_TEST_DEFAULT_PROBLEM .
						" WHERE default_test_num='".$default_test_num."'".
						" AND problem_num='".$problem_num."'".
						" AND problem_table_type='1'".
						" AND gknn='". $_SESSION['t_practice']['gknn']."'".
						" AND term_bnr_ccd='". $bnr_cd."'".
						" AND term_kmk_ccd='". $kmk_cd."'".
						";";

				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}


				$INSERT_DATA['problem_point'] 		= $L_PROBLEM_POINT[$key];
				$INSERT_DATA['course_num'] 			= $_SESSION['t_practice']['course_num'];
				$INSERT_DATA['gknn'] 				= $_SESSION['t_practice']['gknn'];
// 				if ($_SESSION['t_practice']['core_code']) {
					$INSERT_DATA['term_bnr_ccd']	= $bnr_cd;
					$INSERT_DATA['term_kmk_ccd']	= $kmk_cd;
// 				}
// 				} else { unset($list);	}	//if ($problem_num > 0) {	//add okabe 2020/05/01 定期テストアップ不具合(内野様ご報告障害)

				//add start okabe 2020/05/01 定期テストアップ不具合(内野様ご報告障害)
				// CSVの学期コードを再設定する
				// 画面のSELECTを優先するのであれば、以下のif文をコメントにする
// 				if ($INSERT_DATA['term_bnr_ccd'] == "C000000001") {
// 					$INSERT_DATA['term_kmk_ccd'] = $CHECK_DATA['term3_kmk_ccd'];
// 				} elseif ($INSERT_DATA['term_bnr_ccd'] == "C000000002") {
// 					$INSERT_DATA['term_kmk_ccd'] = $CHECK_DATA['term2_kmk_ccd'];
// 				}
				//add end okabe 2020/05/01

				//登録されていればアップデート、無ければインサート
				if ($list) {
					if ($L_DISP_SORT[$key]) {
						$INSERT_DATA['disp_sort'] 	= $L_DISP_SORT[$key];
					}
					$INSERT_DATA['mk_flg'] 			= 0;
					$INSERT_DATA['mk_tts_id'] 		= "";
					$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
				//	$INSERT_DATA[upd_syr_id] 		= ;
					$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 			= "now()";

					$where = " WHERE default_test_num='".$default_test_num."'".
							" AND problem_num='".$problem_num."'".
							" AND problem_table_type='1'".
							" AND gknn='". $_SESSION['t_practice']['gknn']."'".
							" AND term_bnr_ccd='". $bnr_cd."'".
							" AND term_kmk_ccd='". $kmk_cd."'";

					$DATA_ERROR[$i] = $cdb->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);
				} else {
					$INSERT_DATA['default_test_num'] 	= $default_test_num;
					$INSERT_DATA['problem_num'] 		= $problem_num;
					$INSERT_DATA['problem_table_type'] 	= 1;
					$INSERT_DATA['disp_sort'] 			= $disp_sort;
				//	$INSERT_DATA[ins_syr_id] 			= ;
					$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['ins_date'] 			= "now()";
					$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date'] 			= "now()";

					$DATA_ERROR[$i] = $cdb->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);
				}
				// update end oda 2020/05/22

				// >>> add 2020/07/28 yoshizawa テスト問題登録エラーロジック修正
				$DATA_ERROR[$i] = problem_attribute_add($CHECK_DATA['block_num'],$CHECK_DATA['standard_time']);
				// <<<

				if ($DATA_ERROR[$i]) { continue; }

				// del start 2020/07/28 yoshizawa テスト問題登録エラーロジック修正
				// // add start 2020/05/27 yoshizawa インポート時でも標準時間を更新いたします。
				// $ERROR = problem_attribute_add($CHECK_DATA['block_num'],$CHECK_DATA['standard_time']);
				// // add start 2020/05/27 yoshizawa
				// del end 2020/07/28 yoshizawa テスト問題登録エラーロジック修正

			}
			//教科書単元と問題の紐付け登録
			$ERRORS = array();	//	add koike 2012/06/15
			$DATA_ERROR[$i] = book_unit_test_problem_add($L_DEFAULT_TEST_NUM,$problem_num,1,$CHECK_DATA['book_unit_id'], $ERRORS);	//	add $ERRORS ookawara 2012/06/15
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 教科書単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			$DATA_ERROR[$i] = lms_unit_test_problem_add($problem_num,1,$CHECK_DATA['unit_num'],$CHECK_DATA['book_unit_id'], $ERRORS);	//	add $ERRORS ookawara 2012/06/15
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 LMS単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			//$DATA_ERROR[$i] = upnavi_section_test_problem_add($CHECK_DATA['problem_num'], $CHECK_DATA['upnavi_section_num'], $ERRORS);	//	add koike 2012/06/15	// 2012/07/26 del oda
			//$DATA_ERROR[$i] = upnavi_section_test_problem_add($CHECK_DATA['problem_num'], $CHECK_DATA['upnavi_section_num'], "1", $ERRORS);	// 2012/07/26 add oda	// 2012/07/30 del oda
			$DATA_ERROR[$i] = upnavi_section_test_problem_add($problem_num, $CHECK_DATA['upnavi_section_num'], "1", $ERRORS);										// 2012/07/30 add oda
			if ($DATA_ERROR[$i]) { $DATA_ERROR[$i][] = $i."行目 学力Upナビ子単元の登録エラーによりスキップしました。<br>"; continue; }	// add 2020/05/27 yoshizawa 問題インポートエラーのチェックを追加

			// del oda 2014/11/19 課題要望一覧No390
			//// add oda 2014/10/07 課題要望一覧No352 ms_test_default_problem補完処理追加
			//$DATA_ERROR[$i] = ms_test_default_problem_complement($L_DEFAULT_TEST_NUM, $problem_num, 1, $ERROR);

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
function check_data($CHECK_DATA,$ins_mode,$line_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ERROR = null;

	if (!$CHECK_DATA['problem_type']) {
		$ERROR[] = $line_num."行目　問題種類が確認できません。";
	}
	if ($_SESSION['t_practice']['test_type'] == "4") {
		if (!$CHECK_DATA['default_test_num']) {
			$ERROR[] = $line_num."行目　テストIDが確認できません。";
		}
	}
	//2011/05/30 add ozaki
	if ($CHECK_DATA['form_type'] != "1"
	 && $CHECK_DATA['form_type'] != "2"
	 && $CHECK_DATA['form_type'] != "3"
	 && $CHECK_DATA['form_type'] != "4"
	 && $CHECK_DATA['form_type'] != "5"
	 && $CHECK_DATA['form_type'] != "8"
	 && $CHECK_DATA['form_type'] != "10"
	 && $CHECK_DATA['form_type'] != "11"
	// upd start hirose 2020/09/18 テスト標準化開発
	// && $CHECK_DATA['form_type'] != "13") {		// add hasegawa 2016/06/02 作図ツール
	&& $CHECK_DATA['form_type'] != "13"
	&& $CHECK_DATA['form_type'] != "14"
	) {	
	// upd end hirose 2020/09/18 テスト標準化開発

			// add start hasegawa 2018/03/01 百マス計算
			if ($CHECK_DATA['form_type'] == "14") {
				$ERROR[] = $line_num."行目　form_type14(百マス計算)はテストには登録できません。";
			//add start kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			}else if($CHECK_DATA['form_type'] == 15){
				$ERROR[] = $line_num."form_type15(書写)はテストには登録できません。";
			//add end   kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			//add start kimura 2018/11/22 すらら英単語 _admin
			}else if($CHECK_DATA['form_type'] == 16){
				$ERROR[] = $line_num."form_type16(意味)はテストには登録できません。";
			}else if($CHECK_DATA['form_type'] == 17){
				$ERROR[] = $line_num."form_type17(書く)はテストには登録できません。";
			//add end   kimura 2018/11/22 すらら英単語 _admin
			} else {
			// add end hasegawa 2018/03/01
				$ERROR[] = $line_num."行目　form_typeが不正です。".$CHECK_DATA['form_type'];
			} // add hasegawa 2018/03/01 百マス計算
	}

	if ($CHECK_DATA['problem_type'] == "surala") {
//		if (!$CHECK_DATA['disp_sort']) { $ERROR[] = $line_num."行目 登録問題が未選択です。"; }
		if ($_SESSION['t_practice']['test_type'] == "4") {
			if (!$CHECK_DATA['problem_point']) { $ERROR[] = $line_num."行目 配点が未入力です。"; }
			else {
				foreach (explode("//",$CHECK_DATA['problem_point']) as $val) {
					if (preg_match("/[^0-9]/",$val)) {
						$ERROR[] = $line_num."行目 配点は半角数字で入力してください。";
						break;
					} elseif ($val > 100) {
						$ERROR[] = $line_num."行目 配点は100以下で入力してください。";
						break;
					}
				}
			}
		}
		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//if ($CHECK_DATA['standard_time']) {
		//	if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		//		$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
		//	}
		//}
		if ($_SESSION['t_practice']['test_type'] != 4) {
			if ($CHECK_DATA['standard_time']) {
				if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
					$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
				}
			}
		}
		//upd end 2018/06/06 yamaguchi
		if ($CHECK_DATA['book_unit_id']) {
			$L_BOOK_UNIT = explode("::",$CHECK_DATA['book_unit_id']);
			$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT)."'";
			$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0'".
//					" AND unit_end_flg='1'".
					" AND book_unit_id IN (".$in_book_unit_id.")";
//					" AND book_id='".$_SESSION['t_practice']['book_id']."'".$where;
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($L_BOOK_UNIT)) {
				$ERROR[] = $line_num."行目 同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}

			// add start oda 2014/10/14 課題要望一覧No352 登録／修正時のチェック処理が抜けていたので追加
			// 教科書単元のコースが異なる場合はエラーとする
			$sql  = "SELECT mbu.book_unit_id, mb.course_num FROM " . T_MS_BOOK_UNIT ." mbu ".
					" INNER JOIN ".T_MS_BOOK. " mb ON mbu.book_id = mb.book_id AND mb.display = '1' AND mb.mk_flg = '0' ".
					" WHERE mbu.mk_flg='0'".
					"   AND mbu.book_unit_id IN (".$in_book_unit_id.")".
					";";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					if ($_SESSION['t_practice']['course_num'] != $list['course_num']) {
						$ERROR[] = "設定されている教科書単元のコースが異なります。教科書単元ID = ".$list['book_unit_id'];
					}
				}
			}
			// add end oda 2014/10/14
		}
		if ($CHECK_DATA['unit_num']) {

			unset($L_LMS_UNIT);	//	add 2015/01/08 yoshizawa 課題要望一覧No.405対応

			if (preg_match("/&lt;&gt;/",$CHECK_DATA['unit_num'])) {
				$L_LMS_UNIT = explode("&lt;&gt;",$CHECK_DATA['unit_num']);
			} elseif (preg_match("/<>/",$CHECK_DATA['unit_num'])) {
				$L_LMS_UNIT = explode("<>",$CHECK_DATA['unit_num']);
			} else {
				$L_LMS_UNIT[] = trim($CHECK_DATA['unit_num']);
			}

			if (!$L_LMS_UNIT) { continue; }	// 2012/03/13 add ozaki
			$in_lms_unit_id = "'".implode("','",$L_LMS_UNIT)."'";
			$sql  = "SELECT * FROM " . T_UNIT .
				" WHERE state='0' AND display='1'".
				" AND course_num='".$_SESSION['t_practice']['course_num']."'".
				" AND unit_num IN (".$in_lms_unit_id.")";
			if ($result = $cdb->query($sql)) {
				$book_unit_count = $cdb->num_rows($result);
			}
			//入力した単元と存在している単元の数が違っていた場合エラー
			if ($book_unit_count != count($L_LMS_UNIT)) {
				$ERROR[] = $line_num."行目 同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
			}
		}
	} elseif ($CHECK_DATA['problem_type'] == "test") {
//		if (!$CHECK_DATA['disp_sort']) { $ERROR[] = $line_num."行目 登録問題が未選択です。"; }

		if ($CHECK_DATA['parameter'] && ereg("\[([^0-9])\]",$CHECK_DATA['parameter'])) { $ERROR[] = $line_num."行目 パラメーターが不正です。"; }

		//フォームタイプ別の項目チェック
		$array_replace = new array_replace();
		if ($CHECK_DATA['form_type'] == 1) {
			//if ($CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] === "0") {	// del oda 2014/08/11 課題要望一覧No324
			if ($CHECK_DATA['selection_words']) {												// add oda 2014/08/11 課題要望一覧No324
				$max_column = $selection_words_num = $array_replace->set_line($CHECK_DATA['selection_words']);
			}

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
			// update start oda 2014/08/11 課題要望一覧No324 0と1以外はエラーとする(100などを設定するとエラーにならない為です)
			//if (ereg("[^0-1]",$CHECK_DATA['option2'])) { $ERROR[] = "シャッフル情報が不正です。"; }
			if ($CHECK_DATA['option2'] !== "0" && $CHECK_DATA['option2'] !== "1") {
				$ERROR[] = "シャッフル情報が不正です。";
			}
			// update end oda 2014/08/11

			if ($CHECK_DATA['option3'] == "") { $CHECK_DATA['option3'] = "0"; }
			if (ereg("[^0-9]",$CHECK_DATA['option3']) || $CHECK_DATA['option3'] == 1) { $ERROR[] = "選択項目数情報が不正です。"; }

			//if ($max_column > 1) {	// del oda 2014/08/11 課題要望一覧No324
			if ($max_column > 0) {		// add oda 2014/08/11 課題要望一覧No324 判断条件修正
				if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
					$ERROR[] = "出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				}
			}

		} elseif ($CHECK_DATA['form_type'] == 2) {
			if ($CHECK_DATA['selection_words']) {
				$max_column = $selection_words_num = $array_replace->set_line($CHECK_DATA['selection_words']);
			}

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
				if ($L_CORRECT) {
					foreach($L_CORRECT as $key => $val) {
						if (preg_match("/&lt;&gt;/",$val)) {
							foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
						} elseif (preg_match("/<>/",$val)) {
							foreach (explode("<>",$val) as $word) { $L_ANS[] = trim($word); }
						} else {
							$L_ANS[] = trim($val);
						}
					}
				}
				if ($L_OPTION1) {
					foreach($L_OPTION1 as $key => $val) {
						if (preg_match("/&lt;&gt;/",$val)) {
							foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS1[] = trim($word); }
						} elseif (preg_match("/<>/",$val)) {
							foreach (explode("<>",$val) as $word) { $L_ANS1[] = trim($word); }
						} else {
							$L_ANS1[] = trim($val);
						}
						$hit = array_search($L_ANS[$key],$L_ANS1);
						if($hit === FALSE) {
							$ERROR[] = "選択語句内に正解が含まれておりません。";
							break;
						}
					}
				}
			}

			if ($CHECK_DATA['option2'] == "") { $CHECK_DATA['option2'] ="0"; }
			// update start oda 2014/08/11 課題要望一覧No324 0と1以外はエラーとする(100などを設定するとエラーにならない為です)
			//if (ereg("[^0-1]",$CHECK_DATA['option2'])) { $ERROR[] = "シャッフル情報が不正です。"; }
			if ($CHECK_DATA['option2'] !== "0" && $CHECK_DATA['option2'] !== "1") {
				$ERROR[] = "シャッフル情報が不正です。";
			}
			// update end oda 2014/08/11

			//if ($max_column > 1) {	// del oda 2014/08/11 課題要望一覧No324
			if ($max_column > 0) {		// add oda 2014/08/11 課題要望一覧No324 判断条件修正
				if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
					$ERROR[] = "出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				}
			}

		} elseif ($CHECK_DATA['form_type'] == 3) {
			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0" && !$CHECK_DATA['option1'] && $CHECK_DATA['option1'] !== "0") {
				$ERROR[] = "正解、又はBUD正解が確認できません。";
				$ERROR[] = "正解、又はBUD正解が確認できません。";
			}
			// add start hasegawa 2016/11/14 入力フォームサイズ指定項目追加
			// input_sizeが40以上の場合はエラーメッセージを表示
			$CHECK_DATA['option2'] = str_replace("&lt;","<",$CHECK_DATA['option2']);
			$CHECK_DATA['option2'] = str_replace("&gt;",">",$CHECK_DATA['option2']);

			if (preg_match("/<>/",$CHECK_DATA['option2'])) {
				list($input_row,$input_size) = explode("<>",$CHECK_DATA['option2']);

				$L_INPUT_SIZE = array();
				$L_INPUT_SIZE = explode('//',$input_size);
				$input_size_err_flg = 0;
				if(is_array($L_INPUT_SIZE)) {
					foreach($L_INPUT_SIZE as $val) {
						if ($val != "" && !($val > 0 && $val <= 40)) {
							$input_size_err_flg = 1;
						}
					}
				}
				if($input_size_err_flg == 1) {
					$ERROR[] = "解答欄サイズ(文字数)の値が不正です。";
				}
			}
			// add end hasegawa 2016/11/14
		} elseif ($CHECK_DATA['form_type'] == 4) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") { $ERROR[] = "問題テキストが確認できません。"; }

			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0" && !$CHECK_DATA['option1'] && $CHECK_DATA['option1'] !== "0") {
				$ERROR[] = "正解、又はBUD正解が確認できません。";
				$ERROR[] = "正解、又はBUD正解が確認できません。";
			}

			if ($CHECK_DATA['option2'] == "") { $CHECK_DATA['option2'] = "0"; }
			include_once("../../_www/problem_lib/space_checker.php");
			if (preg_match("/[^0-9]/",$CHECK_DATA['option2']) || $CHECK_DATA['option2'] > space_Checker::space_Decision($CHECK_DATA['selection_words'])) { $ERROR[] = "空白数が不正です。"; } //upd 2017/04/10 yamaguchi 空白数の入力制限

		} elseif ($CHECK_DATA['form_type'] == 5) {
			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0") {
				$ERROR[] = "正解が確認できません。";
			}

			if ($CHECK_DATA['option1'] == "") { $CHECK_DATA['option1'] = "1"; }
			if (ereg("[^0-1]",$CHECK_DATA['option1'])) { $ERROR[] = "解答ライン数が不正です。"; }

		} elseif ($CHECK_DATA['form_type'] == 8) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") {
				$ERROR[] = "問題テキストが確認できません。";
			}

			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0") {
				$ERROR[] = "正解が確認できません。";
			}

		} elseif ($CHECK_DATA['form_type'] == 10) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") {
				$ERROR[] = "問題テキストが確認できません。";
			}

			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0" && !$CHECK_DATA['option1'] && $CHECK_DATA['option1'] !== "0") {
				$ERROR[] = "正解、又はBUD正解が確認できません。";
				$ERROR[] = "正解、又はBUD正解が確認できません。";
			}

		} elseif ($CHECK_DATA['form_type'] == 11) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") {
				$ERROR[] = "問題テキストが確認できません。";
			}

			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0") {
				$ERROR[] = "正解が確認できません。";
			}
		// add start hasegawa 2016/06/02 作図ツール
		} elseif ($CHECK_DATA['form_type'] == 13) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") {
				$ERROR[] = "問題テキストが確認できません。";
			}

			if (!$CHECK_DATA['correct']) {
				$ERROR[] = "正解が確認できません。";
			}

			if (!$CHECK_DATA['option1']) {
				$ERROR[] = "問題種類が確認できません。";
			}

			if (preg_match("/[^0-9]/",$CHECK_DATA['option1'])) {
				$ERROR[] = "問題種類は半角英数字で入力してください。";
			}

			if (!$CHECK_DATA['option2']) {
				$ERROR[] = "作図問題パラメータが確認できません。";
			}
		// add end hasegawa 2016/06/02
		// add start hirose 2020/09/18 テスト標準化開発
		//百ます計算
		} elseif ($CHECK_DATA['form_type'] == 14) {
			if (!$CHECK_DATA['selection_words'] && $CHECK_DATA['selection_words'] !== "0") {
				$ERROR[] = "問題テキストが確認できません。";
			}
			if (!$CHECK_DATA['correct'] && $CHECK_DATA['correct'] !== "0" && !$CHECK_DATA['option1'] && $CHECK_DATA['option1'] !== "0") {
				$ERROR[] = "正解、又はBUD正解が確認できません。";
			}
		// add end hirose 2020/09/18 テスト標準化開発
		}
		if ($_SESSION['t_practice']['test_type'] == "4") {
			if (!$CHECK_DATA['problem_point']) { $ERROR[] = $line_num."行目 配点が未入力です。"; }
			else {
				foreach (explode("//",$CHECK_DATA['problem_point']) as $val) {
					if (preg_match("/[^0-9]/",$val)) {
						$ERROR[] = $line_num."行目 配点は半角数字で入力してください。";
						break;
					} elseif ($val > 100) {
						$ERROR[] = $line_num."行目 配点は100以下で入力してください。";
						break;
					}
				}
			}
		}
		//upd start 2018/06/06 yamaguchi 学力診断テスト画面 回答目安時間非表示
		//if ($CHECK_DATA['standard_time']) {
		//	if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
		//		$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
		//	}
		//}
		if ($_SESSION['t_practice']['test_type'] != 4) {
			if ($CHECK_DATA['standard_time']) {
				if (preg_match("/[^0-9]/",$CHECK_DATA['standard_time'])) {
					$ERROR[] = $line_num."行目 回答目安時間は半角数字で入力してください。";
				}
			}
		}
		//upd end 2018/06/06 yamaguchi
		if ($CHECK_DATA['book_unit_id']) {

			foreach ($CHECK_DATA['book_unit_id'] as $key => $l_book_unit_id) {
//				$L_BOOK_UNIT = explode("::",$CHECK_DATA['book_unit_id']);

				$L_BOOK_UNIT[$key] = explode("::",$l_book_unit_id);
				if (!$l_book_unit_id) { continue; }
				$in_book_unit_id = "'".implode("','",$L_BOOK_UNIT[$key])."'";
				$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
						" WHERE mk_flg='0'".
//						" AND unit_end_flg='1'".
						" AND book_unit_id IN (".$in_book_unit_id.")";
//						" AND book_id='".$_SESSION['t_practice']['book_id']."'".$where;
				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}
				//入力した単元と存在している単元の数が違っていた場合エラー
				if ($book_unit_count != count($L_BOOK_UNIT[$key])) {
					$ERROR[] = $line_num."行目 同一単元ＩＤ、または設定されている教科書に存在しない単元を設定しようとしています。";
				}

				// add start oda 2014/10/14 課題要望一覧No352 登録／修正時のチェック処理が抜けていたので追加
				// 教科書単元のコースが異なる場合はエラーとする
				$sql  = "SELECT mbu.book_unit_id, mb.course_num FROM " . T_MS_BOOK_UNIT ." mbu ".
						" INNER JOIN ".T_MS_BOOK. " mb ON mbu.book_id = mb.book_id AND mb.display = '1' AND mb.mk_flg = '0' ".
						" WHERE mbu.mk_flg='0'".
						"   AND mbu.book_unit_id IN (".$in_book_unit_id.")".
						";";
				if ($result = $cdb->query($sql)) {
					while ($list = $cdb->fetch_assoc($result)) {
						if ($_SESSION['t_practice']['course_num'] != $list['course_num']) {
							$ERROR[] = "設定されている教科書単元のコースが異なります。教科書単元ID = ".$list['book_unit_id'];
						}
					}
				}
				// add end oda 2014/10/14
			}
		}
	}
/*
	pre("---------------------------");
	pre($line_num);
	pre($CHECK_DATA['unit_num']);
	pre("---------------------------");
*/
	if ($CHECK_DATA['unit_num']) {
		unset($L_LMS_UNIT);
		foreach ($CHECK_DATA['unit_num'] as $key => $l_unit_num) {
//			$L_LMS_UNIT = explode("::",$CHECK_DATA['unit_num']);
			if (!$l_unit_num) { continue; }	//	add ookawara 2012/03/15
			$L_LMS_UNIT[$key] = explode("::",$l_unit_num);
			foreach ($L_LMS_UNIT[$key] as $val) {

				unset($unit_num);

				if (preg_match("/&lt;&gt;/",$val)) {
					$unit_num = explode("&lt;&gt;",$val);
				} elseif (preg_match("/<>/",$val)) {
					$unit_num = explode("<>",$val);
				} else {
					$unit_num[] = trim($val);
				}

				if (!$val) { continue; }
				$in_lms_unit_id = "'".implode("','",$unit_num)."'";
				$sql  = "SELECT * FROM " . T_UNIT .
					" WHERE state='0' AND display='1'".
					" AND course_num='".$_SESSION['t_practice']['course_num']."'".
					" AND unit_num IN (".$in_lms_unit_id.")";

				if ($result = $cdb->query($sql)) {
					$book_unit_count = $cdb->num_rows($result);
				}

				//入力した単元と存在している単元の数が違っていた場合エラー
				if ($book_unit_count != count($unit_num)) {
					$ERROR[] = $line_num."行目 同一単元ＩＤ、または設定されているLMS単元に存在しない単元を設定しようとしています。".$sql;
				}
			}
		}
	}

	// 2012/07/06 add start oda
	if ($CHECK_DATA['upnavi_section_num']) {

		// 子単元番号を加工し、コース情報を取得する。
		$upnavi_section_list_1 = explode("::", $CHECK_DATA['upnavi_section_num']);

		for ($i=0; $i < count($upnavi_section_list_1); $i++) {
			if (preg_match("/&lt;&gt;/",$upnavi_section_list_1[$i])) {
				$WORK_LIST = explode("&lt;&gt;", $upnavi_section_list_1[$i]);
				for ($j=0; $j < count($WORK_LIST); $j++) {
					$upnavi_section_list[] = $WORK_LIST[$j];
				}
			} elseif (preg_match("/<>/",$upnavi_section_list_1[$i])) {
				$WORK_LIST = explode("<>", $upnavi_section_list_1[$i]);
				for ($j=0; $j < count($WORK_LIST); $j++) {
					$upnavi_section_list[] = $WORK_LIST[$j];
				}
			} else {
				$upnavi_section_list[] = $upnavi_section_list_1[$i];
			}
		}
		$in_upnavi_section_num = implode(",",$upnavi_section_list);

		// コース情報取得SQL
		$sql  = "SELECT course_num FROM ". T_UPNAVI_SECTION .
				" WHERE mk_flg='0' AND display='1'".
				" AND upnavi_section_num IN (".$in_upnavi_section_num.");";

		// 画面で指定しているコースと異なる場合は、エラーとする。
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['course_num'] != $_SESSION['t_practice']['course_num']) {
//echo $sql."<br>";
					$ERROR[] = $line_num."行目 コースが異なる子単元を設定しようとしています。";
				}
			}
		}

		// add start oda 2014/08/11 課題要望一覧No323 存在しない子単元IDを指定した場合は、エラー
		if (is_array($upnavi_section_list)) {
			foreach ($upnavi_section_list as $upnavi_section_key => $upnavi_section_value) {

				$sql  = "SELECT course_num FROM ". T_UPNAVI_SECTION .
						" WHERE mk_flg='0' AND display='1'".
						" AND upnavi_section_num = '".$upnavi_section_value."';";

				// マスタに存在しない場合は、エラーとする
				$exist_flag = 0;
				if ($result = $cdb->query($sql)) {
					while ($list = $cdb->fetch_assoc($result)) {
						$exist_flag = 1;
						break;
					}
				}
				if ($exist_flag == 0) {
					$ERROR[] = $line_num."行目 指定した子単元はマスタに存在しません。";
				}
			}

		}
		// add end oda 2014/08/11 課題要望一覧No323

	}
	// 2012/07/06 add end oda

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
function check_data_surala($CHECK_DATA,$ins_mode,$line_num) {
//	add 2015/01/13 yoshizawa 課題要望一覧No.400

	$ERROR = null;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "";
	$sql .= "SELECT * FROM ".T_PROBLEM." ";
	$sql .= "WHERE problem_num = '".$CHECK_DATA['problem_num']."' ";
	$sql .= "AND block_num = '".$CHECK_DATA['block_num']."' ";
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
function problem_test_csv($problem_num,$CHECK_DATA,$ins_mode) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	$ins_data = array();
	$ins_data = $CHECK_DATA;
	$array_replace = new array_replace();
	if ($CHECK_DATA['form_type'] == 1) {
		if ($ins_data['selection_words'] && $ins_data['selection_words'] === "0") {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 2) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['option1']);
		$ins_data['option1'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 3) {
		if ($ins_data['selection_words']) {
			$array_replace->set_line($ins_data['selection_words']);
			$ins_data['selection_words'] = $array_replace->replace_line();
		}
		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}
		if ($ins_data['option2']) {
			$array_replace->set_line($ins_data['option2']);
			$ins_data['option2'] = $array_replace->replace_line();
		}

		// if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発

		// del 2016/06/02 yoshizawa 手書き認識
		// 手書き認識の設定値パラメータを保持します。
		// すでにすらら問題でoption3を使用していたのでoption4を使用しています。
		// if ($ins_data['option4']) { unset($ins_data['option4']); }
		// <<<
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 4) {
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 5) {
		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['selection_words']) { unset($ins_data['selection_words']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 8) {
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 10) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		if ($ins_data['correct']) {
			$array_replace->set_line($ins_data['correct']);
			$ins_data['correct'] = $array_replace->replace_line();
		}
		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		//if ($ins_data['option1']) { unset($ins_data['option1']); }  // del 2016/06/02 yoshizawa
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		//if ($ins_data['option3']) { unset($ins_data['option3']); } // del karasawa 2019/07/23 BUD英語解析開発
		//if ($ins_data['option4']) { unset($ins_data['option4']); } // del 2016/06/02 yoshizawa 手書き認識
		if ($ins_data['option5']) { unset($ins_data['option5']); }

	} elseif ($CHECK_DATA['form_type'] == 11) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option1']) { unset($ins_data['option1']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option3']) { unset($ins_data['option3']); }
		if ($ins_data['option4']) { unset($ins_data['option4']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add start hirose 2020/09/21 テスト標準化開発
	} elseif ($CHECK_DATA['form_type'] == 14) {
		$array_replace->set_line($ins_data['selection_words']);
		$ins_data['selection_words'] = $array_replace->replace_line();

		$array_replace->set_line($ins_data['correct']);
		$ins_data['correct'] = $array_replace->replace_line();

		if ($ins_data['option1']) {
			$array_replace->set_line($ins_data['option1']);
			$ins_data['option1'] = $array_replace->replace_line();
		}

		if ($ins_data['option4']) {
			$array_replace->set_line($ins_data['option4']);
			$ins_data['option4'] = $array_replace->replace_line();
		}

		if ($ins_data['first_problem']) { unset($ins_data['first_problem']); }
		if ($ins_data['latter_problem']) { unset($ins_data['latter_problem']); }
		if ($ins_data['option2']) { unset($ins_data['option2']); }
		if ($ins_data['option5']) { unset($ins_data['option5']); }
	// add end hirose 2020/09/21 テスト標準化開発
	}
	// add start karasawa 2019/07/24 BUD英語解析開発
	// upd start hirose 2020/09/21 テスト標準化開発
	// if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10){
	if($CHECK_DATA['form_type'] == 3 || $CHECK_DATA['form_type'] == 4 || $CHECK_DATA['form_type'] == 10 || $CHECK_DATA['form_type'] == 14){
	// upd end hirose 2020/09/21 テスト標準化開発
		if ($ins_data['option3']) {
			$array_replace->set_line($ins_data['option3']);
			$ins_data['option3'] = $array_replace->replace_line();
		}
	}
	// add end karasawa 2019/07/24

	foreach ($ins_data AS $key => $val) {
		if ($key == "action"
			|| $key == "default_test_num"
			|| $key == "problem_table_type"
			|| $key == "term3_kmk_ccd"
			|| $key == "term2_kmk_ccd"
			|| $key == "problem_point"
			|| $key == "book_unit_id"
			|| $key == "problem_type"
			|| $key == "unit_key"
			|| $key == "block_num"
			|| $key == "disp_sort"
			|| $key == "unit_num"
			|| $key == "lms_unit_id"
			|| $key == "upnavi_section_num"	//	add koike 2012/06/20
			|| $key == "upnavi_chapter_num"		//	add oda 2012/07/04
			|| $key == "test_type"
			|| $key == "term"
			|| $key == "book_id"
			|| $key == "display_problem_num"
			|| $key == ""
			|| $key == "sentence_flag"	//	add ookawara 2013/02/08
		) { continue; }
		$INSERT_DATA[$key] = $cdb->real_escape($val);
	}

	if ($ins_mode == "add") {

		//画像名変換、フォルダ作成
		//音声名変換、フォルダ作成
		list($INSERT_DATA['question'],$ERROR) 	= img_convert($INSERT_DATA['question'],$problem_num);
		list($INSERT_DATA['question'],$ERROR) 	= voice_convert($INSERT_DATA['question'],$problem_num);

		list($INSERT_DATA['problem'],$ERROR) 		= img_convert($INSERT_DATA['problem'],$problem_num);
		list($INSERT_DATA['problem'],$ERROR) 		= voice_convert($INSERT_DATA['problem'],$problem_num);

		list($INSERT_DATA['hint'],$ERROR) 		= img_convert($INSERT_DATA['hint'],$problem_num);
		list($INSERT_DATA['hint'],$ERROR) 		= voice_convert($INSERT_DATA['hint'],$problem_num);

		list($INSERT_DATA['explanation'],$ERROR) 	= img_convert($INSERT_DATA['explanation'],$problem_num);
		list($INSERT_DATA['explanation'],$ERROR) 	= voice_convert($INSERT_DATA['explanation'],$problem_num);

		//form_type10,11のみselection_wordsの変換
		if ($INSERT_DATA['form_type'] == 10 || $INSERT_DATA['form_type'] == 11) {
			list($INSERT_DATA['selection_words'],$ERROR) 	= img_convert($INSERT_DATA['selection_words'],$problem_num);
			list($INSERT_DATA['selection_words'],$ERROR) 	= voice_convert($INSERT_DATA['selection_words'],$problem_num);
		}

		if ($INSERT_DATA['voice_data']) {
			$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
			$dir_num = (floor($problem_num / 100) * 100) + 1;
			$dir_num = sprintf("%07d",$dir_num);
			$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
			if (!ereg("^".$problem_num."_",$INSERT_DATA['voice_data'])) {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$problem_num."_".$INSERT_DATA['voice_data']);
				}
				$INSERT_DATA['voice_data'] 		= $problem_num."_".$INSERT_DATA['voice_data'];
			} else {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'])) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$INSERT_DATA['voice_data'],$dir_name.$INSERT_DATA['voice_data']);
				}
			}
		}

		$INSERT_DATA['problem_num']		= $problem_num;
		$INSERT_DATA['course_num'] 		= $_SESSION['t_practice']['course_num'];
	//	$INSERT_DATA[ins_syr_id] 		= ;
		$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date']		= "now()";
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";

		$ERROR = $cdb->insert(T_MS_TEST_PROBLEM,$INSERT_DATA);

	} elseif ($ins_mode == "upd") {
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE problem_num='".$problem_num."';";

		$ERROR = $cdb->update(T_MS_TEST_PROBLEM,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * CORE_CODE一覧
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array
 */
function core_code_list() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CORE_CODE = array();

	// upd hasegawa 2017/12/26 AWS移設 ソート条件追加
	// $sql  = "SELECT * FROM ".T_MS_CORE_CODE . " WHERE mk_flg='0';";
	$sql  = "SELECT * FROM ".T_MS_CORE_CODE . " WHERE mk_flg='0'".
		" ORDER BY bnr_cd, kmk_cd;";
	// upd end 2017/12/26

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
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array
 */
function course_list() {

	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29

	$l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[0] = "選択して下さい";

	//upd start hirose 2020/08/26 テスト標準化開発
	// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
	foreach ($l_write_type AS $course_num => $course_name) {
	//upd end hirose 2020/08/26 テスト標準化開発
		if ($course_name == "") {
			continue;
		}
		//upd start yamaguchi 2018/10/09 使用箇所のコースが表示されていなかったので修正
		//$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
		$L_COURSE_LIST[$course_num] = $course_name;
		//upd end yamaguchi 2018/10/09
	}

	return $L_COURSE_LIST;
}

// add start hirose 2020/09/11 テスト標準化開発
/**
 * コースIDに結びつくコース名を取得
 *
 * @param [int] $course_num_
 * @return array course_num => couse_name
 */
function get_course_name_array($course_num_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = " SELECT " .
			" c.course_num " .
			" ,c.course_name " .
			" FROM " .T_COURSE. " c ".
			" WHERE c.course_num = '".$course_num_."'".
			"   AND c.state = '0' " .
			";";
	// print $sql;


	$COURSE_NAME = [];

	if ($result = $cdb->query($sql)) {
		if($list = $cdb->fetch_assoc($result)) {
			$COURSE_NAME[$list['course_num']] = $list['course_name'];
		}
	}

	return $COURSE_NAME;
}
// add end hirose 2020/09/11 テスト標準化開発

/**
 * BOOK一覧
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $course_num
 * @param string $gknn
 * @return array
 */
function book_list($course_num,$gknn) {

	// DB接続オブジェクト
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
		while ($list = $cdb->fetch_assoc($result)) {
			$L_BOOK_LIST[$list['book_id']] = $list['book_name'];
		}
	}
	return $L_BOOK_LIST;
}


/**
 * blockに対してドリル数
 * @author azet
 * @param integer $unit_num
 * @param integer $block_num
 * @return integer
 */
function get_drill_count($unit_num,$block_num) {

	// DB接続オブジェクト
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
 * 学年一覧
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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


/**
 * 画像TAGからIMGタグに変更
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param string $problem
 * @param integer $problem_num
 * @return array
 */
function img_convert($problem,$problem_num) {
	preg_match_all("|\[!IMG=(.*)!\]|U",$problem,$L_IMG_LIST);
	if (isset($L_IMG_LIST[1][0])) {
		//フォルダ作成
		$ERROR = dir_maker(MATERIAL_TEST_IMG_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_IMG_DIR.$dir_num."/";
		foreach ($L_IMG_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				if (file_exists(MATERIAL_TEST_DEF_IMG_DIR.$_SESSION['t_practice']['course_num']."/".$val)) {
					copy(MATERIAL_TEST_DEF_IMG_DIR.$_SESSION['t_practice']['course_num']."/".$val,$dir_name.$val);
				}
				continue;
			}
			$problem = preg_replace("/".$val."/",$problem_num."_".$val,$problem);
			if (file_exists(MATERIAL_TEST_DEF_IMG_DIR.$_SESSION['t_practice']['course_num']."/".$val)) {
				copy(MATERIAL_TEST_DEF_IMG_DIR.$_SESSION['t_practice']['course_num']."/".$val,$dir_name.$problem_num."_".$val);
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
function voice_convert($voice,$problem_num) {
	preg_match_all("|\[!VOICE=(.*)!\]|U",$voice,$L_VOICE_LIST);

	if (isset($L_VOICE_LIST[1][0])) {
		//フォルダ作成
		$ERROR = dir_maker(MATERIAL_TEST_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_TEST_VOICE_DIR.$dir_num."/";
		foreach ($L_VOICE_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$val)) {
					copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$val,$dir_name.$val);
				}
				continue;
			}
			$voice = preg_replace("/".$val."/",$problem_num."_".$val,$voice);
			if (file_exists(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$val)) {
				copy(MATERIAL_TEST_DEF_VOICE_DIR.$_SESSION['t_practice']['course_num']."/".$val,$dir_name.$problem_num."_".$val);
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
function dir_maker($dir,$problem_num,$num) {
	$dir_num = (floor($problem_num / $num) * $num) + 1;
	$dir_num = sprintf("%07d",$dir_num);
	$dir_name = $dir.$dir_num."/";
	$ERROR = dir_make($dir_name);

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
function dir_make($dir_name,$mode=0777) {
	if (!file_exists($dir_name)) {
		if (!mkdir($dir_name,$mode)) {
			$ERROR[] = $dir_name." のフォルダーが作成できませんでした。";
		}
		@chmod($dir_name,$mode);
	}
	return $ERROR;
}

/**
 * book_unit_lms_unit の 削除判断処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param integer $problem_num
 * @param mixed $problem_table_type
 * @param array $ERROR
 */
function check_book_unit_test_problem($problem_num, $problem_table_type, &$ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 配列初期化
	$BOOK_UNIT_LMS_UNIT_LIST = array();
	$INSERT_DATA = array();

	// プログラム番号とプログラムテーブル種類でLMS管理テーブルを読み込む（削除フラグが０のデータが対象）
	$sql   = "SELECT ";
	$sql  .= "  bulu.problem_num, ";
	$sql  .= "  bulu.book_unit_id, ";
	$sql  .= "  bulu.unit_num, ";
	$sql  .= "  bulu.problem_table_type ";
	$sql  .= " FROM " . T_BOOK_UNIT_LMS_UNIT . " bulu";
	$sql  .= " WHERE ";
	$sql  .= "     bulu.problem_table_type='".$problem_table_type."'";
	$sql  .= " AND bulu.problem_num='".$problem_num."'";
	$sql  .= " AND bulu.mk_flg ='0'";

	$i = 0;
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_num']        = $list['problem_num'];
			$BOOK_UNIT_LMS_UNIT_LIST[$i]['book_unit_id']       = $list['book_unit_id'];
			$BOOK_UNIT_LMS_UNIT_LIST[$i]['unit_num']           = $list['unit_num'];
			$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_table_type'] = $list['problem_table_type'];
			$i++;
		}
	}

	for ($i=0; $i<count($BOOK_UNIT_LMS_UNIT_LIST);$i++) {
		// 関連する データを検索し、削除フラグを判断する（抽出条件には削除フラグは含めない）
		$sql  = "SELECT butp.mk_flg FROM " .
				T_BOOK_UNIT_TEST_PROBLEM . " butp ".
				" WHERE butp.book_unit_id = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['book_unit_id']."' ".
				"   AND butp.problem_table_type = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_table_type']."'".
				"   AND butp.problem_num = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_num']."'";

		// 複数行あるので、対象データが全て削除済の場合は、削除フラグは１のままにする
		$del_flag = 1;
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($list['mk_flg'] == 0) {
					$del_flag = 0;
				}
			}
		}

		if ($del_flag == 1) {
			$INSERT_DATA = array();
			$INSERT_DATA['mk_flg'] 			= 1;
			$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date'] 		= "now()";
			$where  = " WHERE problem_num        = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_num']."'";
			$where .= "   AND book_unit_id       = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['book_unit_id']."'";
			$where .= "   AND unit_num           = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['unit_num']."'";
			$where .= "   AND problem_table_type = '".$BOOK_UNIT_LMS_UNIT_LIST[$i]['problem_table_type']."'";

			$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
			if ($ERROR) { return; }
		}
	}

	return;
}


/**
 * 解答欄行数と解答欄サイズをoption2として出力する
 * (form_type3で使用)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @param string $input_row 	解答欄行数
 * @param string $input_size	解答欄サイズ
 * @return string $option2
 */

function make_option2($input_row,$input_size) {	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加

	$option2 = "";

	if($input_row) {
		$option2 = $input_row;
	}
	if($input_size) {
		$option2 .= "&lt;&gt;".$input_size;
	}

	return $option2;
}
/**
 * optionから2解答欄行数と解答欄サイズを取得する
 * (form_type3で使用)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet

 * @return string $option2
 * @return array 		解答欄行数,解答欄サイズ
 */

function get_option2($option2) {	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加

	$input_row = "";
	$input_size = "";

	$option2 = str_replace("&lt;","<",$option2);
	$option2 = str_replace("&gt;",">",$option2);

	if (preg_match("/<>/",$option2)) {
		list($input_row,$input_size) = explode("<>",$option2);
	} else {
		$input_row = $option2;
	}

	return array($input_row, $input_size);
}

// del oda 2014/11/19 課題要望一覧No390
//    教科書単元で保持している月度コードは全て11:３学期制中間になっているので、以下のロジックを流すと全て
//    ３学期制中間のみ有効となり、２学期中間や３学期期末のデータが参照できなくなる。
//    その為、以下のロジックは呼ばない様にする事と、functionをコメントアウトする。
//// ------------------------------------------------------------------------------------
////  add oda 2014/10/07 課題要望一覧No352
////	問題登録　テスト問題情報補完処理
////    問題登録時に教科書単元を紐づけた場合、関連するテスト問題テーブルに不足している関連を追加登録する処理
//// ------------------------------------------------------------------------------------
//function ms_test_default_problem_complement($L_DEFAULT_TEST_NUM,$problem_num,$problem_table_type, &$ERROR) {
//
//	// エラーが存在する場合は、処理中止
//	if ($ERROR) { return; }
//
//	// 定期テスト以外は、ロジックを実行しない
//	if ($_SESSION['t_practice']['test_type'] != 1) { return; }
//
//	//紐付けされている単元番号取得
//	foreach ($L_DEFAULT_TEST_NUM as $key => $default_test_num) {
//
//		$CHECK_MS_TEST_DEFAULT_PROBLEM = array();
//		$CHECK_MS_TEST_DEFAULT_PROBLEM_KEYS = array();
//		$term_bnr_ccd = "";
//		$term_kmk_cd = "";
//		$problem_point = "0";
//
//		// 初期値は画面のリストボックスから取得する
//		if ($_SESSION['t_practice']['core_code']) {
//			list($term_bnr_ccd,$term_kmk_cd) = explode("_",$_SESSION['t_practice']['core_code']);
//		}
//
//		// 既に登録済のms_test_default_problemテーブルを読み、３学期制か２学期制か判断する情報を取得する（削除データも含める）
//		$sql  = "SELECT " .
//				"  mtdp.default_test_num, ".
//				"  mtdp.problem_num, ".
//				"  mtdp.problem_table_type, ".
//				"  mtdp.gknn, ".
//				"  mtdp.term_bnr_ccd, ".
//				"  mtdp.term_kmk_ccd, ".
//				"  mtdp.problem_point ".
//				" FROM ". T_MS_TEST_DEFAULT_PROBLEM . " mtdp ".
//				" WHERE mtdp.default_test_num='".$default_test_num."'".
//				"   AND mtdp.problem_table_type='".$problem_table_type."'".
//				"   AND mtdp.problem_num='".$problem_num."'".
//				";";
//		if ($result = mysql_db_query(DBNAME,$sql)) {
//			while ($list = mysql_fetch_array($result,MYSQL_ASSOC)) {
//				$CHECK_MS_TEST_DEFAULT_PROBLEM[] = $list;		// データ格納用の配列に格納
//				$CHECK_MS_TEST_DEFAULT_PROBLEM_KEYS[] = $list['gknn'].$list['term_bnr_ccd'].$list['term_kmk_ccd'];		// 存在チェック用の配列に格納
//				$term_bnr_ccd = $list['term_bnr_ccd'];			// 学期コード取得
//				$problem_point = $list['problem_point'];		// 配点
//			}
//		}
//
//		//echo $sql."<br>";
//
//		// 学年と学期の月度を取得する
//		$L_BOOK_UNIT_LIST = array();
//		$sql  = "SELECT " .
//				" ms_b.gknn, ".
//				" ms_bu.term3_kmk_ccd, ".
//				" ms_bu.term2_kmk_ccd ".
//				" FROM ". T_BOOK_UNIT_TEST_PROBLEM . " butp ".
//				" INNER JOIN ".T_MS_BOOK_UNIT." ms_bu ON ms_bu.book_unit_id = butp.book_unit_id AND ms_bu.display = '1' AND ms_bu.mk_flg = '0' ".
//				" INNER JOIN ".T_MS_BOOK." ms_b ON ms_bu.book_id = ms_b.book_id AND ms_b.display = '1' AND ms_b.mk_flg = '0' ".
//				" WHERE butp.mk_flg = '0' ".
//				"   AND butp.default_test_num='".$default_test_num."'".
//				"   AND butp.problem_table_type='".$problem_table_type."'".
//				"   AND butp.problem_num='".$problem_num."'".
//				";";
//		if ($result = mysql_db_query(DBNAME,$sql)) {
//			while ($list = mysql_fetch_array($result,MYSQL_ASSOC)) {
//				$L_BOOK_UNIT_LIST[] = $list;
//				$L_BOOK_UNIT_LIST_KEYS[] = $list['gknn'];		// 存在チェック用の配列に格納（学期コードと月度は後で付加する）
//			}
//		}
//
//		//echo $sql."<br>";
//
//		// 削除判断用配列に情報をコピーする
//		$CHECK_MS_TEST_DEFAULT_PROBLEM_2 = $CHECK_MS_TEST_DEFAULT_PROBLEM;
//
//		// 取得した教科書の学年と月度で情報検索し、存在しない場合は、INSERTする。存在する場合はUPDATEする。
//		if (is_array($L_BOOK_UNIT_LIST_KEYS) && count($L_BOOK_UNIT_LIST_KEYS) > 0) {
//
//			foreach($L_BOOK_UNIT_LIST_KEYS as $book_unit_list_key => $book_unit_list_value) {
//
//				unset($INSERT_DATA);
//
//				// 3学期制か2学期制の判断を行う
//				$work_term_bnr_ccd = "";
//				$work_term_kmk_ccd = "";
//				if ($term_bnr_ccd == "C000000001") {
//					$work_term_bnr_ccd = "C000000001";
//					$work_term_kmk_ccd = $L_BOOK_UNIT_LIST[$book_unit_list_key]['term3_kmk_ccd'];
//				}
//				if ($term_bnr_ccd == "C000000002") {
//					$work_term_bnr_ccd = "C000000002";
//					$work_term_kmk_ccd = $L_BOOK_UNIT_LIST[$book_unit_list_key]['term2_kmk_ccd'];
//				}
//
//				// 存在チェック（存在しない場合はfalseで、存在する場合は配列のキー情報（0から始まるので、存在しない場合のチェックは===演算子で行う）
//				$check_key = array_search($book_unit_list_value.$work_term_bnr_ccd.$work_term_kmk_ccd,$CHECK_MS_TEST_DEFAULT_PROBLEM_KEYS);
//
//				// 存在しない場合はインサート
//				if ($check_key === false) {
//
//					if (!$book_unit_list_value) { continue; }
//
//					$CHECK_MS_TEST_DEFAULT_PROBLEM_KEYS[] = $book_unit_list_value.$work_term_bnr_ccd.$work_term_kmk_ccd;
//
//					$max_disp_sort = "0";
//
//					$sql  = "SELECT " .
//							"  MAX(mtdp.disp_sort) as max_disp_sort ".
//							" FROM ". T_MS_TEST_DEFAULT_PROBLEM . " mtdp ".
//							";";
//					if ($result = mysql_db_query(DBNAME,$sql)) {
//						while ($list = mysql_fetch_array($result,MYSQL_ASSOC)) {
//							$max_disp_sort = $list['max_disp_sort'];
//						}
//					}
//
//					//echo $sql."<br>";
//
//					$INSERT_DATA['default_test_num'] 	= $default_test_num;
//					$INSERT_DATA['problem_num'] 		= $problem_num;
//					$INSERT_DATA['problem_table_type'] 	= $problem_table_type;
//					$INSERT_DATA['course_num']			= $_SESSION['t_practice']['course_num'];
//					$INSERT_DATA['gknn'] 				= $book_unit_list_value;
//					$INSERT_DATA['term_bnr_ccd'] 		= $work_term_bnr_ccd;
//					$INSERT_DATA['term_kmk_ccd'] 		= $work_term_kmk_ccd;
//					$INSERT_DATA['problem_point'] 		= $problem_point;
//					$INSERT_DATA['disp_sort'] 			= $max_disp_sort + 1;
//					$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
//					$INSERT_DATA['ins_date'] 			= "now()";
//					$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
//					$INSERT_DATA['upd_date'] 			= "now()";
//
//					$query = new sql_query();
//					$ERROR = $query->insert(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA);
//					if ($ERROR) { return $ERROR; }
//
//				// 登録済の場合はアップデート
//				} else {
//
//					$INSERT_DATA['mk_flg'] 			= 0;
//					$INSERT_DATA['mk_tts_id'] 		= "";
//					$INSERT_DATA['mk_date'] 		= "0000-00-00 00:00:00";
//					$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
//					$INSERT_DATA['upd_date'] 		= "now()";
//
//					$where = " WHERE default_test_num = '".$default_test_num."'";
//					$where .= "  AND problem_table_type = '".$problem_table_type."'";
//					$where .= "  AND problem_num = '".$problem_num."'";
//					$where .= "  AND gknn = '".$book_unit_list_value."'";
//					$where .= "  AND term_bnr_ccd = '".$work_term_bnr_ccd."'";
//					$where .= "  AND term_kmk_ccd = '".$work_term_kmk_ccd."'";
//					$where .= ";";
//
//					$query = new sql_query();
//					$ERROR = $query->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);
//
//					if ($ERROR) { return $ERROR; }
//
//					//更新レコード分の値を削除
//					unset($CHECK_MS_TEST_DEFAULT_PROBLEM_2[$check_key]);
//				}
//			}
//		}
//
//		//登録されている物で更新されていないレコードを削除
//		if (is_array($CHECK_MS_TEST_DEFAULT_PROBLEM_2) && count($CHECK_MS_TEST_DEFAULT_PROBLEM_2) > 0) {
//
//			unset($INSERT_DATA);
//			$INSERT_DATA['mk_flg'] 			= 1;
//			$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
//			$INSERT_DATA['mk_date'] 		= "now()";
//
//			foreach($CHECK_MS_TEST_DEFAULT_PROBLEM_2 as $val) {
//
//				$where = " WHERE default_test_num = '".$default_test_num."'";
//				$where .= "  AND problem_table_type = '".$problem_table_type."'";
//				$where .= "  AND problem_num = '".$problem_num."'";
//				$where .= "  AND gknn = '".$val['gknn']."'";
//				$where .= "  AND term_bnr_ccd = '".$val['term_bnr_ccd']."'";
//				$where .= "  AND term_kmk_ccd = '".$val['term_kmk_ccd']."'";
//				$where .= ";";
//
//				$query = new sql_query();
//				$ERROR = $query->update(T_MS_TEST_DEFAULT_PROBLEM,$INSERT_DATA,$where);
//				if ($ERROR) { return $ERROR; }
//			}
//		}
//	}
//	return $ERROR;
//}

//add start yamaguchi 2018/10/02 すらら英単語追加
/*すらら英単語-----------------------------------------------------------*/

/**
 * すらら英単語種類、英単語種別１、英単語種別２選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return string HTML
 */

/*function eng_word_select_unit_view() {

	global $L_TEST_TYPE;


	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TYPE_LIST = type_list();
	$L_CATEGORY1_LIST = category1_list();
	$L_CATEGORY2_LIST = category2_list();

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
*/

/**
 * すらら英単語種類・テスト種別表示メニューセッション操作
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author azet
 * @return array エラーの場合
 */
function eng_word_view_session() {

	if (strlen($_POST['test_type'])) { $_SESSION['t_practice']['test_type'] = $_POST['test_type']; }
	if (strlen($_POST['test_type_num'])) { $_SESSION['t_practice']['test_type_num'] = $_POST['test_type_num']; }
	if (strlen($_POST['test_category1_num'])) { $_SESSION['t_practice']['test_category1_num'] = $_POST['test_category1_num']; }
	if (strlen($_POST['test_category2_num'])) { $_SESSION['t_practice']['test_category2_num'] = $_POST['test_category2_num']; }
	//$_SESSION['sub_session']['s_page'] = 1;


	return $ERROR;
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
/*function eng_word_test_list($ERROR) {

	global $L_PAGE_VIEW,$L_TEST_ADD_TYPE,$L_TEST_PROBLEM_TYPE,$L_FORM_TYPE;
	global $L_EXP_CHA_CODE;

	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }
	unset($_SESSION['sub_session']['select_course']);

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
			foreach($L_TEST_PROBLEM_TYPE as $key => $val) {
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
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
				$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$html .= "を<input type=\"submit\" value=\"登録\">\n";
				$html .= "</form>\n";
			}
		}
	}

	$img_ftp = FTP_URL."test_img/";
	$voice_ftp = FTP_URL."test_voice/";
	$test_img_ftp = FTP_TEST_URL."test_img/".$_SESSION['t_practice']['test_type_num']."/";
	$test_voice_ftp = FTP_TEST_URL."test_voice/".$_SESSION['t_practice']['test_type_num']."/";
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

	$sql  = "SELECT count(DISTINCT mtcp.problem_num) AS problem_count" .
		" FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
		" WHERE mtcp.mk_flg='0'".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_num,mtcp.problem_table_type".
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
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;
	$limit = " LIMIT ".$start.",".$page_view.";";

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
		" AND mtcp.problem_table_type='2'".
		"".
		$where.
		" GROUP BY mtcp.test_type_num,mtcp.test_category1_num,mtcp.test_category2_num,mtcp.problem_table_type,mtcp.problem_num;";
//echo $sql."<br><br>";
	$cdb->exec_query($sql);

	$sql  = "SELECT ".
		"*".
		" FROM test_problem_list".
		" ORDER BY test_type_num,test_category1_num,test_category2_num,list_num".
		$limit;
//echo $sql;
	if ($result = $cdb->query($sql)) {
		$html .= $duplicate_message;
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
				$table_type = "すらら";
			} elseif ($list['problem_table_type'] == 2) {
				$table_type = "テスト専用";
			}
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$list['list_num']."</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";
			$html .= "<td>".$table_type."</td>\n";
			$html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
			$html .= "<td>".$list['standard_time']."</td>\n";
			$html .= "<td>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_3('".$list['problem_table_type']."','".$list['problem_num']."')\">\n";
			$html .= "</form>\n";
			$html .= "</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				if (!$list['default_test_num']) { $focus_default_test_num = ""; }
				else { $focus_default_test_num = $list['default_test_num']; }
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
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
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"delete\">\n";
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
*/
/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
/*function type_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TYPE_LIST = array();
	$L_TYPE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_TYPE. " WHERE mk_flg='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_TYPE_LIST[$list['test_type_num']] = $list['test_type_name'];
		}
	}
	return $L_TYPE_LIST;
}
*/
/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
/*function category1_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CATEGORY1_LIST = array();
	$L_CATEGORY1_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_CATEGORY1. " WHERE mk_flg='0'AND test_type_num='".$_SESSION['t_practice']['test_type_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY1_LIST[$list['test_category1_num']] = $list['test_category1_name'];
		}
	}
	return $L_CATEGORY1_LIST;
}
*/
/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
/*function category2_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CATEGORY2_LIST = array();
	$L_CATEGORY2_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_CATEGORY2. " WHERE mk_flg='0' AND test_type_num='".$_SESSION['t_practice']['test_type_num']."' AND test_category1_num='".$_SESSION['t_practice']['test_category1_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY2_LIST[$list['test_category2_num']] = $list['test_category2_name'];
		}
	}
	return $L_CATEGORY2_LIST;
}
*/


/*-----------------------------------------------------------------------*/
//add end yamaguchi 2018/10/02

?>
