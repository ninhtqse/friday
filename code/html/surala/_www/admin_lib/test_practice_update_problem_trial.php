<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		問題設定(学力診断テスト) アップデート
 *
 * @author Azet
 */


/*
	基本情報
		本番バッチではms_test_default_problemの以下の値でグルーピングしてアップデートする。
			default_test_num =20	0以外
			mk_flg=0	固定

		変更テーブルは4テーブルとなり以下の手順で処理をしていく
			(問題)	※画像情報もここで取得する。
			コピー先の「ms_test_default_problem」の該当する情報を削除
			コピー元の「ms_test_default_problem」の該当する情報を作成し、コピー先にインサートする
			(問題マスタ)
			コピー先の「ms_test_problem」の該当する情報を削除
			コピー元の「ms_test_problem」の該当する情報を作成し、コピー先にインサートする
			(単元)
			コピー先の「book_unit_test_problem」の該当する情報を削除
			コピー元の「book_unit_test_problem」の該当する情報を作成し、コピー先にインサートする
			(LMS単元)
			コピー先の「book_unit_lms_unit」の該当する情報を削除（problem_table_type=1）
			コピー先の「book_unit_lms_unit」の該当する情報を削除（problem_table_type=2）
			コピー元の「book_unit_lms_unit」の該当する情報を作成し、コピー先にインサートする
			(学力Upナビ単元)
			コピー先の「upnavi_section_problem」の該当する情報を削除
			コピー元の「upnavi_section_problem」の該当する情報を作成し、コピー先にインサートする
*/

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return string HTML
 */
function action() {

	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		set_time_limit(0);	//	add ookawara 2012/08/20
		echo "更新開始\n";	//	add ookawara 2012/08/20
		echo str_pad(" ",4096)."<br>\n";	//	add ookawara 2012/08/20
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
		select_web();
	} elseif (ACTION == "view_session") {
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		$html .= update_end_html();
	} else {
		$html .= select_unit_view($ERROR);
	}

	return $html;
}


/**
 * コース、学年、テスト名選択
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 */
function select_unit_view($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST;
	global $L_WRITE_TYPE;

	// comment 2020/08/29 yoshizawa 一時コメント
	// プラクティスアップデートは段階リリースになるので、一時コメントいたします。
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	// upd start hirose 2020/09/11 テスト標準化開発
	// $l_write_type = $L_WRITE_TYPE;
	$test_type4 = new TestStdCfgType4($cdb);
	$l_write_type = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/11 テスト標準化開発
	// comment 2020/08/29 yoshizawa 一時コメント

	$html = "";

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}

	//	検証中データー取得
	$PMUL = array();
	$send_data = "";
	$sql  = "SELECT send_data".
			" FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1';";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$course_num = $VALUES['course_num'];
			$gknn = $VALUES['gknn'];
			$tgi_default_test_num = $VALUES['tgi_default_test_num'];

			if ($course_num < 1) {
				continue;
			} elseif ($gknn == "0" || $gknn == "") {
				$PMUL[$course_num] = 1;
			} elseif ($tgi_default_test_num == "0" || $tgi_default_test_num == "") {
				$PMUL[$course_num][$gknn] = 1;
			}
		}
	}

	//コース
	$course_count = "";
	$couse_html  = "";
	$couse_html .= "<option value=\"0\">選択して下さい</option>\n";
	//upd start hirose 2020/08/26 テスト標準化開発
	// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
	foreach ($l_write_type AS $course_num => $course_name) {
	//upd end hirose 2020/08/26 テスト標準化開発

		// del start oda 2020/02/27 理社対応
// 		// 理科社会プラクティスアップ無効化
// 		if($course_num == 15 || $course_num == 16){ continue; } // add 2018/05/18 yoshizawa 理科社会対応
		// del end oda 2020/02/27 理社対応

		if ($course_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['view_session']['course_num'] == $course_num) {
			$selected = "selected";
		}
		$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
	}

	$last_select_flg = 0;
	$course_num = $_SESSION['view_session']['course_num'];
	if ($PMUL[$course_num] == 1) {
		$last_select_flg = 1;
	}

	//学年
	$gknn_html = "";
	if ($_SESSION['view_session']['course_num'] > 0) {
		if ($last_select_flg == 1) {
			$gknn_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			foreach($L_GKNN_LIST as $key => $val) {
				$selected = "";
				if ($_SESSION['view_session']['gknn'] == $key) {
					$selected = "selected";
				}
				$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		}
	} else {
		$gknn_html .= "<option value=\"0\">--------</option>\n";
	}

	$last_select_flg = 0;
	$gknn = $_SESSION['view_session']['gknn'];
	if ($PMUL[$course_num][$gknn] == 1) {
		$last_select_flg = 1;
	}

	//	テスト名
	$tgi_default_test_num_html = "";
	if ($_SESSION['view_session']['gknn'] != "") {
		if ($last_select_flg == 1) {
			$tgi_default_test_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$default_test_num_count = 0;
			$sql  = "SELECT ms_td.default_test_num, CONCAT(ms_tg.test_group_name, ' ', ms_td.test_name) AS test_name, ms_tg.test_group_id".
					"  FROM ".T_MS_BOOK_GROUP." ms_tg" .
					" INNER JOIN ".T_BOOK_GROUP_LIST." tgl ON tgl.test_group_id=ms_tg.test_group_id".
					" AND tgl.mk_flg='0'".
					" INNER JOIN ".T_MS_TEST_DEFAULT." ms_td ON ms_td.default_test_num=tgl.default_test_num".
					" AND ms_td.mk_flg='0'".
					" WHERE ms_tg.mk_flg='0'".
					" AND ms_td.test_type='4'".
					" AND ms_td.course_num='".$_SESSION['view_session']['course_num']."'".
					" AND ms_tg.test_gknn='".$_SESSION['view_session']['gknn']."'".
					" ORDER BY ms_tg.disp_sort, tgl.disp_sort;";
			if ($result = $cdb->query($sql)) {
				$default_test_num_count = $cdb->num_rows($result);
			}
			if (!$default_test_num_count) {
				$tgi_default_test_num_html .= "<option value=\"0\">設定されていません。</option>\n";
			} else {
				$tgi_default_test_num_html .= "<option value=\"0\">選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$test_group_id = $list['test_group_id'];
					$default_test_num = $list['default_test_num'];
					$tgi_default_test_num = $test_group_id."_".$default_test_num;

					$selected = "";
					if ($_SESSION['view_session']['tgi_default_test_num'] == $tgi_default_test_num) {
						$selected = "selected";
					}
					$tgi_default_test_num_html .= "<option value=\"".$tgi_default_test_num."\" ".$selected.">".$list['test_name']."</option>\n";
				}
			}
		}
	} else {
		$tgi_default_test_num_html .= "<option value=\"0\">--------</option>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>学年</td>\n";
	$html .= "<td>テスト名</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit();\">\n".$couse_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"gknn\" onchange=\"submit();\">\n".$gknn_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"tgi_default_test_num\" onchange=\"submit();\">\n".$tgi_default_test_num_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if ($_SESSION['view_session']['course_num'] < 1) {
//	if ($_SESSION['view_session']['gknn'] == "0" || $_SESSION['view_session']['gknn'] == "") {
//	if ($_SESSION['view_session']['tgi_default_test_num'] == "0" || $_SESSION['view_session']['tgi_default_test_num'] == "") {
		$html .= "問題をアップする各項目を選択してください。<br>\n";
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}

	return $html;
}


/**
 * コース、学年、テスト名選択セッションセット
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 */
function view_set_session() {

	$course_num = $_SESSION['view_session']['course_num'];
	$gknn = $_SESSION['view_session']['gknn'];
	$tgi_default_test_num = $_SESSION['view_session']['tgi_default_test_num'];
	list($test_group_id, $default_test_num) = split("_", $tgi_default_test_num);

	unset($_SESSION['view_session']);

	if ($_POST['course_num'] > 0) {
		$_SESSION['view_session']['course_num'] = $_POST['course_num'];
	} else {
		return;
	}

	if ($_POST['course_num'] == $course_num && $_POST['gknn'] != "") {
		$_SESSION['view_session']['gknn'] = $_POST['gknn'];
	} else {
		return;
	}

	if ($_POST['gknn'] == $gknn && $_POST['tgi_default_test_num'] != "") {
		$_SESSION['view_session']['tgi_default_test_num'] = $_POST['tgi_default_test_num'];
	} else {
		return;
	}

}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function default_html($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= select_web_menu();
	$html .= "</form>\n";
	$html .= "<br />\n";

	unset($BASE_DATA);
	unset($MAIN_DATA);
	//サーバー情報取得
	if (!$_SESSION['select_db']) { return $html; }
	if (!$_SESSION['select_web']) { return $html; }

	//	閲覧DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	クエリー
	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	list($test_group_id, $default_test_num) = split("_", $_SESSION['view_session']['tgi_default_test_num']);
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$default_test_num."'";
		$and .= " AND mtdp.default_test_num ='".$default_test_num."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$default_test_num."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($_SESSION['view_session']['course_num'] > 0) {
		$where .= " AND mtdp.course_num='".$_SESSION['view_session']['course_num']."'";
		$and .= " AND mtdp.course_num='".$_SESSION['view_session']['course_num']."'";
		$cn_where .= " AND mtp.course_num='".$_SESSION['view_session']['course_num']."'";
	}
	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
		$and .= " AND mtdp.gknn='".$_SESSION['view_session']['gknn']."'";
		$where .= " AND mtdp.gknn='".$_SESSION['view_session']['gknn']."'";
	}

	//	ms_test_default_problem
	$sql  = "SELECT MAX(mtdp.upd_date) AS upd_date FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$where.";";
	$sql_cnt  = "SELECT DISTINCT mtdp.* FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
				$where.";";

	//	ms_test_problem
	$sql2 = "SELECT MAX(mtp.upd_date) AS upd_date FROM ".T_MS_TEST_PROBLEM." mtp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	$sql2_cnt = "SELECT DISTINCT mtp.* FROM ".T_MS_TEST_PROBLEM." mtp".
				" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=mtp.problem_num".
					$and.
					" AND mtdp.problem_table_type='2'".
				" WHERE mtdp.problem_num>'0'".
				$cn_where.";";

	//	book_unit_test_problem
	$sql3_2 = "SELECT  MAX(butp.upd_date) AS upd_date FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	$sql3_1 = "SELECT  MAX(butp.upd_date) AS upd_date FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	$sql3_2_cnt = "SELECT DISTINCT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
						$and.
						" AND mtdp.problem_table_type='2'".
					" WHERE mtdp.problem_num>'0'".
					$dtn_where.
					" AND butp.problem_table_type='2';";
	$sql3_1_cnt = "SELECT DISTINCT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
						$and.
						" AND mtdp.problem_table_type='1'".
					" WHERE mtdp.problem_num>'0'".
					$dtn_where.
					" AND butp.problem_table_type='1';";

	//	book_unit_lms_unit
	$sql4_2 = "SELECT  MAX(bulu.upd_date) AS upd_date FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	$sql4_1 = "SELECT  MAX(bulu.upd_date) AS upd_date FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	$sql4_2_cnt = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
						$and.
						" AND mtdp.problem_table_type='2'".
					" WHERE mtdp.problem_num>'0'".
					" AND bulu.problem_table_type='2';";
	$sql4_1_cnt = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
						$and.
						" AND mtdp.problem_table_type='1'".
					" WHERE mtdp.problem_num>'0'".
					" AND bulu.problem_table_type='1';";

	//	upnavi_section_problem
	$sql5_2 = "SELECT MAX(usp.upd_date) AS upd_date FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	$sql5_1 = "SELECT MAX(usp.upd_date) AS upd_date FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	$sql5_2_cnt = "SELECT DISTINCT usp.* FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
						$and.
						" AND mtdp.problem_table_type='2'".
					" WHERE mtdp.problem_num>'0'".
					" AND usp.problem_table_type='2';";
	$sql5_1_cnt = "SELECT DISTINCT usp.* FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
					" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
						$and.
						" AND mtdp.problem_table_type='1'".
					" WHERE mtdp.problem_num>'0'".
					" AND usp.problem_table_type='1';";

/*
echo $sql."<br>\n<br>\n";
echo $sql_cnt."<br>\n<br>\n";
echo $sql2."<br>\n<br>\n";
echo $sql2_cnt."<br>\n<br>\n";
echo $sql3_2."<br>\n<br>\n";
echo $sql3_2_cnt."<br>\n<br>\n";
echo $sql3_1."<br>\n<br>\n";
echo $sql3_1_cnt."<br>\n<br>\n";
echo $sql4_2."<br>\n<br>\n";
echo $sql4_2_cnt."<br>\n<br>\n";
echo $sql4_1."<br>\n<br>\n";
echo $sql4_1_cnt."<br>\n<br>\n";
echo $sql5_2."<br>\n<br>\n";
echo $sql5_2_cnt."<br>\n<br>\n";
echo $sql5_1."<br>\n<br>\n";
echo $sql5_1_cnt."<br>\n<br>\n";
*/

	//	ローカルサーバー
	$dsp_flg = 0;
	//	ms_test_default_problem
	$local_html = "";
	$local_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$local_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql_cnt)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local_time) {
		$dsp_flg = 1;
		$local_html = $local_time." (".$cnt.")";
	} else {
		$local_html = "データーがありません。";
	}

	//	ms_test_problem
	$local2_html = "";
	$local2_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql2)) {
		$list = $cdb->fetch_assoc($result);
		$local2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql2_cnt)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local2_time) {
		$dsp_flg = 1;
		$local2_html = $local2_time." (".$cnt.")";
	} else {
		$local2_html = "データーがありません。";
	}

	//	book_unit_test_problem
	$local3_html = "";
	$local3_2_time = "";
	$local3_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $cdb->query($sql3_2)) {
		$list = $cdb->fetch_assoc($result);
		$local3_2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql3_1)) {
		$list = $cdb->fetch_assoc($result);
		$local3_1_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql3_2_cnt)) {
		$cnt_2 = $cdb->num_rows($result);
	}
	if ($result = $cdb->query($sql3_1_cnt)) {
		$cnt_1 = $cdb->num_rows($result);
	}
	if ($local3_2_time || $local3_1_time) {
		$dsp_flg = 1;
		$local3_time = $local3_2_time;
		$cnt = $cnt_2;
		if ($local3_time < $local3_1_time) {
			$local3_time = $local3_1_time;
			$cnt = $cnt_1;
		}
		$local3_html = $local3_time." (".$cnt.")";
	} else {
		$local3_html = "データーがありません。";
	}

	//	book_unit_lms_unit
	$local4_html = "";
	$local4_2_time = "";
	$local4_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $cdb->query($sql4_2)) {
		$list = $cdb->fetch_assoc($result);
		$local4_2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql4_1)) {
		$list = $cdb->fetch_assoc($result);
		$local4_1_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql4_2_cnt)) {
		$cnt_2 = $cdb->num_rows($result);
	}
	if ($result = $cdb->query($sql4_1_cnt)) {
		$cnt_1 = $cdb->num_rows($result);
	}
	if ($local4_2_time || $local4_1_time) {
		$dsp_flg = 1;
		$local4_time = $local4_2_time;
		$cnt = $cnt_2;
		if ($local4_time < $local4_1_time) {
			$local4_time = $local4_1_time;
			$cnt = $cnt_1;
		}
		$local4_html = $local4_time." (".$cnt.")";
	} else {
		$local4_html = "データーがありません。";
	}

	//	upnavi_section_problem
	$local5_html = "";
	$local5_2_time = "";
	$local5_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $cdb->query($sql5_2)) {
		$list = $cdb->fetch_assoc($result);
		$local5_2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql5_1)) {
		$list = $cdb->fetch_assoc($result);
		$local5_1_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql5_2_cnt)) {
		$cnt_2 = $cdb->num_rows($result);
	}
	if ($result = $cdb->query($sql5_1_cnt)) {
		$cnt_1 = $cdb->num_rows($result);
	}
	if ($local5_2_time || $local5_1_time) {
		$dsp_flg = 1;
		$local5_time = $local5_2_time;
		$cnt = $cnt_2;
		if ($local5_time < $local5_1_time) {
			$local5_time = $local5_1_time;
			$cnt = $cnt_1;
		}
		$local5_html = $local5_time." (".$cnt.")";
	} else {
		$local5_html = "データーがありません。";
	}

	// -- 閲覧DB
	//	ms_test_default_problem
	$remote_html = "";
	$remote_time = "";
	$cnt = 0;
	if ($result = $connect_db->query($sql)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_cnt)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote_time) {
		$dsp_flg = 1;
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}

	//	ms_test_problem
	$remote2_html = "";
	$remote2_time = "";
	$cnt = 0;
	if ($result = $connect_db->query($sql2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql2_cnt)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote2_time) {
		$dsp_flg = 1;
		$remote2_html = $remote2_time." (".$cnt.")";
	} else {
		$remote2_html = "データーがありません。";
	}

	//	book_unit_test_problem
	$remote3_html = "";
	$remote3_2_time = "";
	$remote3_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $connect_db->query($sql3_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote3_2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql3_1)) {
		$list = $connect_db->fetch_assoc($result);
		$remote3_1_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql3_2_cnt)) {
		$cnt_2 = $connect_db->num_rows($result);
	}
	if ($result = $connect_db->query($sql3_1_cnt)) {
		$cnt_1 = $connect_db->num_rows($result);
	}
	if ($remote3_2_time || $remote3_1_time) {
		$dsp_flg = 1;
		$remote3_time = $remote3_2_time;
		$cnt = $cnt_2;
		if ($remote3_time < $remote3_1_time) {
			$remote3_time = $remote3_1_time;
			$cnt = $cnt_1;
		}
		$remote3_html = $remote3_time." (".$cnt.")";
	} else {
		$remote3_html = "データーがありません。";
	}

	//	book_unit_lms_unit
	$remote4_html = "";
	$remote4_2_time = "";
	$remote4_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $connect_db->query($sql4_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote4_2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql4_1)) {
		$list = $connect_db->fetch_assoc($result);
		$remote4_1_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql4_2_cnt)) {
		$cnt_2 = $connect_db->num_rows($result);
	}
	if ($result = $connect_db->query($sql4_1_cnt)) {
		$cnt_1 = $connect_db->num_rows($result);
	}
	if ($remote4_2_time || $remote4_1_time) {
		$dsp_flg = 1;
		$remote4_time = $remote4_2_time;
		$cnt = $cnt_2;
		if ($remote4_time < $remote4_1_time) {
			$remote4_time = $remote4_1_time;
			$cnt = $cnt_1;
		}
		$remote4_html = $remote4_time." (".$cnt.")";
	} else {
		$remote4_html = "データーがありません。";
	}

	//	upnavi_section_problem
	$remote5_html = "";
	$remote5_2_time = "";
	$remote5_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $connect_db->query($sql5_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote5_2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql5_1)) {
		$list = $connect_db->fetch_assoc($result);
		$remote5_1_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql5_2_cnt)) {
		$cnt_2 = $connect_db->num_rows($result);
	}
	if ($result = $connect_db->query($sql5_1_cnt)) {
		$cnt_1 = $connect_db->num_rows($result);
	}
	if ($remote5_2_time || $remote5_1_time) {
		$dsp_flg = 1;
		$remote5_time = $remote5_2_time;
		$cnt = $cnt_2;
		if ($remote5_time < $remote5_1_time) {
			$remote5_time = $remote5_1_time;
			$cnt = $cnt_1;
		}
		$remote5_html = $remote5_time." (".$cnt.")";
	} else {
		$remote5_html = "データーがありません。";
	}


	//	ファイル更新情報取得
	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$sql  = "SELECT DISTINCT mtdp.problem_num FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$where.
			" AND mtdp.problem_table_type='2'".
			" AND mtdp.mk_flg='0'".
			" ORDER BY mtdp.problem_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$img_last_local_time = 0;
	$img_last_local_cnt = 0;
	$img_last_remote_time = 0;
	$img_last_remote_cnt = 0;
	$dir = MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $img_last_local_time, $dir);
	$img_last_local_cnt = count($LOCAL_FILES['f']);
	$dir = REMOTE_MATERIAL_TEST_IMG_DIR;
	test_remote_dir_time($LOCAL_FILES, $img_last_remote_time, $img_last_remote_cnt, $_SESSION['select_web'], $dir);
	$img_local_time = "データーがありません。";
	if ($img_last_local_time > 0) {
		$img_local_time = date("Y-m-d H:i:s", $img_last_local_time);
	}
	$img_remote_time = "データーがありません。";
	if ($img_last_remote_time > 0) {
		$img_remote_time = date("Y-m-d H:i:s", $img_last_remote_time);
	}
	$local_img_html = $img_local_time." (".$img_last_local_cnt.")";
	$remote_img_html = $img_remote_time." (".$img_last_remote_cnt.")";

	//	音声ファイル
	$LOCAL_FILES = array();
	$voice_last_local_time = 0;
	$voice_last_local_cnt = 0;
	$voice_last_remote_time = 0;
	$voice_last_remote_cnt = 0;
	$dir = MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $voice_last_local_time, $dir);
	$voice_last_local_cnt = count($LOCAL_FILES['f']);
	$dir = REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_remote_dir_time($LOCAL_FILES, $voice_last_remote_time, $voice_last_remote_cnt, $_SESSION['select_web'], $dir);

	$voice_local_time = "データーがありません。";
	if ($voice_last_local_time > 0) {
		$voice_local_time = date("Y-m-d H:i:s", $voice_last_local_time);
	}
	$voice_remote_time = "データーがありません。";
	if ($voice_last_remote_time > 0) {
		$voice_remote_time = date("Y-m-d H:i:s", $voice_last_remote_time);
	}
	$local_voice_html = $voice_local_time." (".$voice_last_local_cnt.")";
	$remote_voice_html = $voice_remote_time." (".$voice_last_remote_cnt.")";

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}

	if ($dsp_flg == 1) {
		$submit_msg = "問題設定(診断テスト)情報を検証へアップしますがよろしいですか？";

		$html .= "問題設定(診断テスト)情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";	//	add  name=\"pupform\" ookawara 2012/08/08
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		//$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";	//	del ookawara 2012/08/08
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";	//	add ookawara 2012/08/08
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";	//	add ookawara 2012/08/08		// update oda 2013/10/16 文言変更
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題：".T_MS_TEST_DEFAULT_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題マスタ：".T_MS_TEST_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local2_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote2_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>単元：".T_BOOK_UNIT_TEST_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local3_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote3_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>LMS単元：".T_BOOK_UNIT_LMS_UNIT."</td>\n";
		$html .= "<td>\n";
		$html .= $local4_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote4_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>学力Upナビ単元：".T_UPNAVI_SECTION_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local5_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote5_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>画像ファイル</td>\n";
		$html .= "<td>\n";
		$html .= $local_img_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_img_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>音声ファイル</td>\n";
		$html .= "<td>\n";
		$html .= $local_voice_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_voice_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		//$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";	//	del ookawara 2012/08/08
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";	//	add ookawara 2012/08/08
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";		// update oda 2013/10/16 文言変更
		$html .= "</form>\n";
		$html .= "※閲覧DB：検証バッチWeb、閲覧Web：検証バッチWebのデーターやファイルは、<br>\n　アップデートリストで本番にアップや削除をしてもデーターが消えないのでご注意ください。<br>\n";	//	add ookawara 2012/12/07
	} else {
		$html .= "問題設定(診断テスト)情報が設定されておりません。<br>\n";

		//	閲覧DB切断
		$connect_db->close();

		return $html;
	}

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array &$ERROR
 * @return array エラーの場合
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_CONTENTS_DB;

	//	時間がかかる為、表示ヘッダ表示
	flush();	//	add ookawara 2012/08/20

	$db_data = $L_CONTENTS_DB['92'];
	//$db_data['DBNAME'] = "SRLBS99";				// 2015/11/10 oda デバッグ用テーブル切り替え

	//	検証バッチDB接続
	$connect_db = new connect_db();
	//$connect_db->set_db($L_CONTENTS_DB['92']);
	$connect_db->set_db($db_data);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$INSERT_NAME_DEL = array();
	$INSERT_VALUE_DEL = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	list($test_group_id, $default_test_num) = split("_", $_SESSION['view_session']['tgi_default_test_num']);
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$default_test_num."'";
		$and .= " AND mtdp.default_test_num ='".$default_test_num."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$default_test_num."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($_SESSION['view_session']['course_num'] > 0) {
		$where .= " AND mtdp.course_num='".$_SESSION['view_session']['course_num']."'";
		$and .= " AND mtdp.course_num='".$_SESSION['view_session']['course_num']."'";
		$cn_where .= " AND mtp.course_num='".$_SESSION['view_session']['course_num']."'";
	}
	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
		$and .= " AND mtdp.gknn='".$_SESSION['view_session']['gknn']."'";
		$where .= " AND mtdp.gknn='".$_SESSION['view_session']['gknn']."'";
	}

	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "db情報取得中<br>\n";
	flush();

	//	ms_test_default_problem
	$sql  = "SELECT DISTINCT mtdp.* FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_DEFAULT_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}
	$INSERT_NAME_DEL = $INSERT_NAME;
	$INSERT_VALUE_DEL = $INSERT_VALUE;

	//	ms_test_problem
	$sql  = "SELECT DISTINCT mtp.* FROM ".T_MS_TEST_PROBLEM." mtp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_test_problem	problem_table_type='2'
	$sql  = "SELECT DISTINCT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_TEST_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_test_problem problem_table_type='1'
	$sql  = "SELECT DISTINCT butp.* FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_TEST_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit problem_table_type='2'
	$sql  = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_LMS_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_LMS_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "SELECT DISTINCT usp.* FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_UPNAVI_SECTION_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "SELECT DISTINCT usp.* FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_UPNAVI_SECTION_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}


	//	検証バッチDBデーター削除クエリー
	//	ms_test_problem
	$sql  = "DELETE mtp FROM ".T_MS_TEST_PROBLEM." mtp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	$DELETE_SQL['ms_test_problem'] = $sql;

	//	book_unit_test_problem	problem_table_type='2'
	$sql  = "DELETE butp FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	$DELETE_SQL['book_unit_test_problem_2'] = $sql;

	//	book_unit_test_problem	problem_table_type='1'
	$sql  = "DELETE butp FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	$DELETE_SQL['book_unit_test_problem_1'] = $sql;

	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "DELETE bulu FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	$DELETE_SQL['book_unit_lms_unit_2'] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL['book_unit_lms_unit_1'] = $sql;

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "DELETE usp FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	$DELETE_SQL['upnavi_section_problem_2'] = $sql;

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "DELETE usp FROM ".T_UPNAVI_SECTION_PROBLEM." usp".
			" LEFT JOIN ".T_MS_TEST_DEFAULT_PROBLEM." mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	$DELETE_SQL['upnavi_section_problem_1'] = $sql;

	//	ms_test_default_problem
	$sql  = "DELETE mtdp FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
			$where.";";
	$DELETE_SQL['ms_test_default_problem'] = $sql;


	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return ;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$connect_db->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$connect_db->close();
		return $ERROR;
	}

	//	削除
	if ($DELETE_SQL) {

		//	add ookawara 2012/08/20
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();

		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {	//	add $table_name => ookawara 2012/08/20
			if (!$connect_db->exec_query($sql)) {
				// update start 2016/04/12 yoshizawa プラクティスアップデートエラー対応
				//$ERROR[] = "SQL DELETE ERROR<br>$sql";
				// トランザクション中は対象のレコードがロックします。
				// プラクティスアップデートが同時に実行された場合にはエラーメッセージを返します。
				global $L_TRANSACTION_ERROR_MESSAGE;
				$error_no = $connect_db->error_no_func();
				if($error_no == 1213){
					$ERROR[] = $L_TRANSACTION_ERROR_MESSAGE[$error_no];
				} else {
					$ERROR[] = "SQL DELETE ERROR<br>$sql";
				}
				// update end 2016/04/12
				$err_flg = 1;
			}
		}
		if ($err_flg == 1) {
			$sql  = "ROLLBACK";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$connect_db->close();
			return $ERROR;
		}
	}


	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	//	ms_test_default_problem DBデーター追加
	if (count($INSERT_NAME_DEL) && count($INSERT_VALUE_DEL)) {
		foreach ($INSERT_NAME_DEL AS $table_name => $insert_name) {
			if ($INSERT_VALUE_DEL[$table_name]) {
				foreach ($INSERT_VALUE_DEL[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
							" (".$insert_name.") ".
							" VALUES".$values.";";
					if (!$connect_db->exec_query($sql)) {
						$ERROR[] = "SQL INSERT ERROR<br>$sql";
						$sql  = "ROLLBACK";
						if (!$connect_db->exec_query($sql)) {
							$ERROR[] = "SQL ROLLBACK ERROR";
						}
						$connect_db->close();
						return $ERROR;
					}
				}
			}
		}
	}

	//	再削除
	if ($DELETE_SQL) {

		//	add ookawara 2012/08/20
		echo "<br>\n";
		echo "db情報再削除中<br>\n";
		flush();

		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {	//	add $table_name => ookawara 2012/08/20

			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
				$err_flg = 1;
			}
		}
		if ($err_flg == 1) {
			$sql  = "ROLLBACK";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$connect_db->close();
			return $ERROR;
		}
	}

	//	検証バッチDBデーター追加

	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";	//	add ookawara 2012/08/20
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				//	add ookawara 2012/08/20 start
				if ($last_table_name != $table_name) {
					//echo "<br>\n";
					//echo $table_name." 情報更新中<br>\n";
					//flush();
				}
				//	add ookawara 2012/08/20 end

				foreach ($INSERT_VALUE[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
							" (".$insert_name.") ".
							" VALUES".$values.";";
					if (!$connect_db->exec_query($sql)) {
						$ERROR[] = "SQL INSERT ERROR<br>$sql";
						$sql  = "ROLLBACK";
						if (!$connect_db->exec_query($sql)) {
							$ERROR[] = "SQL ROLLBACK ERROR";
						}
						$connect_db->close();
						return $ERROR;
					}
				}
			}
			$last_table_name = $table_name;	//	add ookawara 2012/08/20
		}
	}

	//	外部キー制約設定
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$connect_db->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$connect_db->close();
		return $ERROR;
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return ;
	}

	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "db最適化中<br>\n";
	flush();

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_DEFAULT_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_BOOK_UNIT_TEST_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_BOOK_UNIT_LMS_UNIT.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_UPNAVI_SECTION_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {

		//	add ookawara 2012/08/20
		echo "<br>\n";
		echo "ファイルアップロード開始<br>\n";
		flush();

		//	問題ファイル取得
		$PROBLEM_LIST = array();
		$sql  = "SELECT DISTINCT mtdp.problem_num FROM ".T_MS_TEST_DEFAULT_PROBLEM." mtdp".
				$where.
				" AND mtdp.problem_table_type='2'".
				" AND mtdp.mk_flg='0'".
				" ORDER BY mtdp.problem_num;";
		if ($result = $cdb->query($sql)) {
			while ($list=$cdb->fetch_assoc($result)) {
				$problem_num = $list['problem_num'];
				$PROBLEM_LIST[] = $problem_num;
			}
		}

		//	add ookawara 2012/08/20
		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		//	画像ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_TEST_IMG_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
		test_remote_set_file($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

		//	add ookawara 2012/08/20
		echo "<br>\n";
		echo "音声ファイルアップロード開始<br>\n";
		flush();

		//	音声ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_TEST_VOICE_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
		test_remote_set_file($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);
	}

	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "検証サーバーへデーター反映中<br>\n";
	flush();

	//	検証サーバー反映処理記録
	//	add ookawara 2012/08/21
	$update_num = 0;
	$sql  = "INSERT INTO test_update_check".
			" (type, start_time)".
			" VALUE('2', now());";
	if ($connect_db->exec_query($sql)) {
		$update_num = $connect_db->insert_id();
	}

	//	検証バッチから検証webへ
	if ($_POST['fileup'] != 1) { $_POST['fileup'] = "0"; }
	$_SESSION['view_session']['fileup'] = $_POST['fileup'];
	$gknn = $_SESSION['view_session']['gknn'];
	if (!$gknn) { $gknn = "0"; }
	if (!$default_test_num) { $default_test_num = "0"; }
	$fileup = $_SESSION['view_session']['fileup'];
	if (!$fileup) { $fileup = "0"; }

	// update start oda 2015/11/10 プラクティスアップデート不具合修正
	//$send_data = " '".$_SESSION['view_session']['course_num']."' '".$gknn."' '".$default_test_num."' '".$fileup."' '".$update_num."'";
	//$command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data;
	$send_data = " '".$_SESSION['view_session']['course_num']."' '".$gknn."' '".$default_test_num."' '".$fileup."' '".$update_num."' '".$_SESSION['myid']['id']."' '0' '".$test_group_id."'";
	//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/TESTCONTENTSUP.php '2' 'test_".MODE."'".$send_data;		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// update end oda 2015/11/10

	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	$command .= " > /dev/null &";	//	add ookawara 2012/08/21
	//echo "command=".$command."<br>\n";
	//flush();
	//exec($command,&$LIST);	//	del ookawara 2012/08/21
	exec($command);	//	add ookawara 2012/08/21 // dev 2016/04/13


	//	add ookawara 2012/08/21 start
	if ($update_num > 0) {
		$end_flg = 1;
		for ($i=0; $i<=600; $i++) {
			$sql  = "SELECT state FROM test_update_check".
					" WHERE update_num='".$update_num."';";
			if ($result = $connect_db->exec_query($sql)) {
				$list = $connect_db->fetch_assoc($result);
				$state = $list['state'];
			}

			if ($state != 0) {
				$end_flg = 0;
				break;
			}

			echo "・";
			flush();
			sleep(2);
		}

		echo "<br>\n";
		flush();
	}

	if ($end_flg == 1) {
		echo "<br>\n";
		echo "反映処理が完了しておりませんがタイムアウト防止の為次の処理に進みます。<br>\n";
		flush();
	}
	//	add ookawara 2012/08/21 end


	//	add ookawara 2012/08/20
	echo "<br>\n";
	echo "検証サーバーへデーター反映終了<br>\n";
	flush();

	/* del start oda 2015/11/10 プラクティスアップデート不具合修正 ログ更新はcgiにて行う
// 	//	ログ保存 --
// 	$test_mate_upd_log_num = "";
// 	$SEND_DATA_LOG = $_SESSION['view_session'];
// 	$send_data_log = serialize($SEND_DATA_LOG);
// 	$send_data_log = addslashes($send_data_log);
// 	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
// 			" WHERE update_mode='".MODE."'".
// 			" AND state='1'".
// 			" AND course_num='".$_SESSION['view_session']['course_num']."'";
// 	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
// 		$sql .= " AND stage_num='".$_SESSION['view_session']['gknn']."'";
// 	} else {
// 		$sql .= " AND stage_num IS NULL";
// 	}
// 	if ($default_test_num != "0" && $default_test_num != "") {
// 		$sql .= " AND lesson_num='".$default_test_num."'";
// 	} else {
// 		$sql .= " AND lesson_num IS NULL";
// 	}
// 	$sql .=	" AND send_data='".$send_data_log."'".
// 			" ORDER BY regist_time DESC".
// 			" LIMIT 1;";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
// 	}

// 	if ($test_mate_upd_log_num < 1) {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['state'] = 0;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
// 		$where = " WHERE update_mode='".MODE."'".
// 				 " AND course_num='".$_SESSION['view_session']['course_num']."'".
// 				 " AND state!='0'";
// 		if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
// 			$where .= " AND stage_num='".$_SESSION['view_session']['gknn']."'";
// 		}
// 		if ($default_test_num != "0" && $default_test_num != "") {
// 			$where .= " AND lesson_num='".$default_test_num."'";
// 		}

// 		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
// 	}

// 	if ($test_mate_upd_log_num) {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['send_data'] = $send_data_log;
// 		$INSERT_DATA['state'] = 1;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
// 		$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";

// 		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
// 	} else {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['update_mode'] = MODE;
// 		$INSERT_DATA['course_num'] = $_SESSION['view_session']['course_num'];
// 		if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
// 			$INSERT_DATA['stage_num'] = $_SESSION['view_session']['gknn'];
// 		}
// 		if ($default_test_num != "0" && $default_test_num != "") {
// 			$INSERT_DATA['lesson_num'] = $default_test_num;
// 		}
// 		if ($_POST['fileup'] == "1") {
// 			$INSERT_DATA['unit_num'] = $_POST['fileup'];
// 		}
// 		$INSERT_DATA['send_data'] = $send_data_log;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['state'] = 1;
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 		$ERROR = $cdb->insert(T_TEST_MATE_UPD_LOG, $INSERT_DATA);
// 	}
	*/

	//	検証バッチDB切断
	$connect_db->close();

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @return string HTML
 */
function update_end_html() {

	$html  = "問題設定(診断テスト)情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
