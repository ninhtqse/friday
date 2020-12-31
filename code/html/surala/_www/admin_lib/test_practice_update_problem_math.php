<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		問題設定(数学検定) アップデート
 *
 * @author Azet
 */
// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定


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
		// アップデート処理
		set_time_limit(0);
		echo "更新開始\n";
		echo str_pad(" ",4096)."<br>\n";
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
		select_web();
	} elseif (ACTION == "view_session") {
		// セッション管理
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		// アップ完了画面
		$html .= update_end_html();
	} else {
		// 初期状態 級、採点単元選択
		$html .= select_unit_view($ERROR);
	}

	return $html;
}


/**
 * 級 採点単 選択
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 * @param array $ERROR
 */
function select_unit_view($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MATH_TEST_CLASS; // 級マスタ配列   // del 2020/09/15 thanh テスト標準化開発

	// add start 2020/09/15 thanh テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 thanh テスト標準化開発

	$html = "";

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}

	//	すでにプラクティスアップデートしているデータが存在するか確認
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
			$class_id = $VALUES['class_id'];

			if ($class_id) {
				$PMUL[$class_id] = 1;
			}
		}
	}

	// 級選択プルダウン
	$class_html = "";
	foreach($L_MATH_TEST_CLASS as $class_id_key => $val) {
		$selected = "";
		if ($_SESSION['view_session']['class_id'] == $class_id_key && $_SESSION['view_session']['class_id'] != "" ) {
			if ( $_SESSION['view_session']['class_id'] !== "select"  ) {
				$selected = "selected";
			}
		}
		$class_html .= "<option value=\"".$class_id_key."\" ".$selected.">".$val."</option>\n";
	}

	//$last_select_flg = 0;
	//$class_id = $_SESSION['view_session']['class_id'];
	//if ($PMUL[$class_id] == 1 && ($class_id === "0" || $class_id != "")) {
	//	$last_select_flg = 1;
	//}
	//// 採点単元名選択プルダウン
	//$book_unit_id_html = "";
	//if ($_SESSION['view_session']['class_id'] != "0" && $_SESSION['view_session']['class_id'] != "") {
	//	if ($last_select_flg == 1) {
	//		$book_unit_id_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
	//	} else {
	//		$book_unit_id_count = 0;
	//		$sql =  "SELECT mbu.* FROM ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ".
	//		           " LEFT JOIN ".T_MS_BOOK_UNIT." mbu ON mtbul.book_unit_id = mbu.book_unit_id ".
	//				 " WHERE mbu.mk_flg = '0' ";
	//		if ($_SESSION['view_session']['class_id'] != "0" && $_SESSION['view_session']['class_id'] != "") {
	//			$sql .= " AND class_id='".$_SESSION['view_session']['class_id']."' ";
	//		}
	//		$sql .= " ORDER BY mbu.disp_sort;";
	//		if ($result = $cdb->query($sql)) {
	//			$book_unit_id_count = $cdb->num_rows($result);
	//		}
	//		if ($book_unit_id_count < 1) {
	//			$book_unit_id_html .= "<option value=\"0\">グループが登録されておりません。</option>\n";
	//		} else {
	//			$book_unit_id_html .= "<option value=\"0\">選択して下さい</option>\n";
	//			while ($list = $cdb->fetch_assoc($result)) {
	//				$selected = "";
	//				if ($_SESSION['view_session']['book_unit_id'] == $list['book_unit_id']) {
	//					$selected = "selected";
	//				}
	//				$book_unit_id_html .= "<option value=\"".$list['book_unit_id']."\" ".$selected.">".$list['book_unit_name']."</option>\n";
	//			}
	//		}
	//	}
	//} else {
	//	$book_unit_id_html .= "<option value=\"0\">--------</option>\n";
	//}


	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>級</td>\n";
	//$html .= "<td>採点単元名</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"class_id\" onchange=\"submit();\">\n".$class_html."</select>\n";
	$html .= "</td>\n";
	//$html .= "<td>\n";
	//$html .= "<select name=\"book_unit_id\" onchange=\"submit();\">\n".$book_unit_id_html."</select>\n";
	//$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";
	if ($PMUL[$_SESSION['view_session']['class_id']] == 1) {
		$html .= $L_MATH_TEST_CLASS[$_SESSION['view_session']['class_id']]."はアップデート中の為選択出来ません。<br>\n";
		$html .= "<br />\n";

	} else if ($_SESSION['view_session']['class_id'] != "" && $_SESSION['view_session']['class_id'] != "select" ) {
		$html .= default_html($ERROR);

	} else {
		$html .= "問題をアップする各項目を選択してください。<br>\n";
		$html .= "<br />\n";
	}

	return $html;
}


/**
 * 級、単元名選択セッションセット
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける UC2:[3]学力診断テスト.
 *
 * @author Azet
 */
function view_set_session() {

	unset($_SESSION['view_session']);

	if ($_POST['class_id'] != "") {
		$_SESSION['view_session']['class_id'] = $_POST['class_id'];
	} else {
		return;
	}
	//if ($_POST['class_id'] != "0" && $_POST['class_id'] != "" && $_POST['book_unit_id'] != "") {
	//	$_SESSION['view_session']['book_unit_id'] = $_POST['book_unit_id'];
	//} else {
	//	return;
	//}

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
	$and = "";
	$and .= " AND mtcp.mk_flg = '0' ";
	if ($_SESSION['view_session']['class_id'] != "") {
		//	math_test_contorol_problemで級を絞り込みます。
		$and .= " AND mtcp.class_id='".$_SESSION['view_session']['class_id']."' ";
	}
	//if ($_SESSION['view_session']['book_unit_id'] != "0" && $_SESSION['view_session']['book_unit_id'] != "") {
	//	$and .= " AND butp.book_unit_id='".$_SESSION['view_session']['book_unit_id']."' ";
	//}

	//	book_unit_test_problem
	$sql_1 = "SELECT MAX(butp.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='1'".
				" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
				" AND butp.default_test_num='0' ".
				" AND butp.problem_table_type='1'".
				" WHERE 1 ".
				$and.";";
	$sql_2 = "SELECT MAX(butp.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='2'".
				" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
				" AND butp.default_test_num='0' ".
				" AND butp.problem_table_type='2'".
				" WHERE 1 ".
				$and.";";
	$sql_1_cnt = "SELECT DISTINCT butp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
					 " INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
					 " AND mtcp.problem_table_type='1'".
					 " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
					 " AND butp.default_test_num='0' ".
					 " AND butp.problem_table_type='1'".
					 " WHERE 1 ".
					 $and.";";
	$sql_2_cnt = "SELECT DISTINCT butp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
					 " INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
					 " AND mtcp.problem_table_type='2'".
					 " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
					 " AND butp.default_test_num='0' ".
					 " AND butp.problem_table_type='2'".
					 " WHERE 1 ".
					 $and.";";

	//	book_unit_lms_unit
	$sql2_1 = "SELECT MAX(bulu.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='1'".
				" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
				" AND bulu.problem_table_type='1'".
				" WHERE 1 ".
				$and.";";
	$sql2_2 = "SELECT MAX(bulu.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='2'".
				" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
				" AND bulu.problem_table_type='2'".
				" WHERE 1 ".
				$and.";";
	$sql2_1_cnt = "SELECT DISTINCT bulu.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='1'".
				" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
				" AND bulu.problem_table_type='1'".
				" WHERE 1 ".
				$and.";";
	$sql2_2_cnt = "SELECT DISTINCT bulu.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='2'".
				" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
				" AND bulu.problem_table_type='2'".
				" WHERE 1 ".
				$and.";";

	//	ms_test_problem
	$sql3 = "SELECT MAX(mtp.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='2'".
				" WHERE 1 ".
				$and.";";
	$sql3_cnt = "SELECT DISTINCT mtp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
				" AND mtcp.problem_table_type='2'".
				" WHERE 1 ".
				$and.";";

	//	math_test_contorol_problem
	$sql4_1 = "SELECT MAX(mtcp.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" WHERE 1 ".
				" AND mtcp.problem_table_type='1'".
				$and.";";
	$sql4_2 = "SELECT MAX(mtcp.upd_date) AS upd_date FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" WHERE 1 ".
				" AND mtcp.problem_table_type='2'".
				$and.";";
	$sql4_1_cnt = "SELECT DISTINCT mtcp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" WHERE 1 ".
				" AND mtcp.problem_table_type='1'".
				$and.";";
	$sql4_2_cnt = "SELECT DISTINCT mtcp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
				" WHERE 1 ".
				" AND mtcp.problem_table_type='2'".
				$and.";";

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
*/

	//	ローカルサーバー
	$dsp_flg = 0;

	//	book_unit_test_problem
	$local_html = "";
	$local_2_time = "";
	$local_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $cdb->query($sql_2)) {
		$list = $cdb->fetch_assoc($result);
		$local_2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql_1)) {
		$list = $cdb->fetch_assoc($result);
		$local_1_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql_2_cnt)) {
		$cnt_2 = $cdb->num_rows($result);
	}
	if ($result = $cdb->query($sql_1_cnt)) {
		$cnt_1 = $cdb->num_rows($result);
	}
	if ($local_2_time || $local_1_time) {
		$dsp_flg = 1;
		$local_time = $local_2_time;
		$cnt = $cnt_2;
		// テスト専用問題の更新時間と比較してすらら問題の更新時間の方が最近の場合
		// すらら問題のデータを表示する。
		if ($local_time < $local_1_time) {
			$local_time = $local_1_time;
			$cnt = $cnt_1;
		}
		$local_html = $local_time." (".$cnt.")";
	} else {
		$local_html = "データーがありません。";
	}

	//	book_unit_lms_unit
	$local2_html = "";
	$local2_2_time = "";
	$local2_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $cdb->query($sql2_2)) {
		$list = $cdb->fetch_assoc($result);
		$local2_2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql2_1)) {
		$list = $cdb->fetch_assoc($result);
		$local2_1_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql2_2_cnt)) {
		$cnt_2 = $cdb->num_rows($result);
	}
	if ($result = $cdb->query($sql2_1_cnt)) {
		$cnt_1 = $cdb->num_rows($result);
	}
	if ($local2_2_time || $local2_1_time) {
		$dsp_flg = 1;
		$local2_time = $local2_2_time;
		$cnt = $cnt_2;
		if ($local2_time < $local2_1_time) {
			$local2_time = $local2_1_time;
			$cnt = $cnt_1;
		}
		$local2_html = $local2_time." (".$cnt.")";
	} else {
		$local2_html = "データーがありません。";
	}

	//	ms_test_problem
	$local3_html = "";
	$local3_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql3)) {
		$list = $cdb->fetch_assoc($result);
		$local3_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql3_cnt)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local3_time) {
		$dsp_flg = 1;
		$local3_html = $local3_time." (".$cnt.")";
	} else {
		$local3_html = "データーがありません。";
	}

	//	math_test_contorol_problem
	$local4_html = "";
	$local4_html_2_time = "";
	$local4_html_1_time = "";
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
			$local4_time = $loca4_1_time;
			$cnt = $cnt_1;
		}
		$local4_html = $local4_time." (".$cnt.")";
	} else {
		$local4_html = "データーがありません。";
	}

	// -- 閲覧DB
	//	book_unit_test_problem
	$remote_html = "";
	$remote_2_time = "";
	$remote_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $connect_db->query($sql_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_1)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_1_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_2_cnt)) {
		$cnt_2 = $connect_db->num_rows($result);
	}
	if ($result = $connect_db->query($sql_1_cnt)) {
		$cnt_1 = $connect_db->num_rows($result);
	}
	if ($remote_2_time || $remote_1_time) {
		$dsp_flg = 1;
		$remote_time = $remote_2_time;
		$cnt = $cnt_2;
		if ($remote_time < $remote_1_time) {
			$remote_time = $remote_1_time;
			$cnt = $cnt_1;
		}
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}

	//	book_unit_lms_unit
	$remote2_html = "";
	$remote2_2_time = "";
	$remote2_1_time = "";
	$cnt = 0;
	$cnt_2 = 0;
	$cnt_1 = 0;
	if ($result = $connect_db->query($sql2_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote2_2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql2_1)) {
		$list = $connect_db->fetch_assoc($result);
		$remote2_1_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql2_2_cnt)) {
		$cnt_2 = $connect_db->num_rows($result);
	}
	if ($result = $connect_db->query($sql2_1_cnt)) {
		$cnt_1 = $connect_db->num_rows($result);
	}
	if ($remote2_2_time || $remote2_1_time) {
		$dsp_flg = 1;
		$remote2_time = $remote2_2_time;
		$cnt = $cnt_2;
		if ($remote2_time < $remote2_1_time) {
			$remote2_time = $remote2_1_time;
			$cnt = $cnt_1;
		}
		$remote2_html = $remote2_time." (".$cnt.")";
	} else {
		$remote2_html = "データーがありません。";
	}

	//	ms_test_problem
	$remote3_html = "";
	$remote3_time = "";
	$cnt = 0;
	if ($result = $connect_db->query($sql3)) {
		$list = $connect_db->fetch_assoc($result);
		$remote3_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql3_cnt)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote3_time) {
		$dsp_flg = 1;
		$remote3_html = $remote3_time." (".$cnt.")";
	} else {
		$remote3_html = "データーがありません。";
	}

	//	math_test_contorol_problem
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

	//	ファイル更新情報取得
	//	問題ファイル取得
	$PROBLEM_LIST = array();
	 $sql  = " SELECT DISTINCT mtp.problem_num FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp ".
				 " INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON butp.problem_num = mtp.problem_num ".
				 " AND butp.default_test_num='0'".
				 " AND butp.problem_table_type='2'".
				 " AND butp.mk_flg='0'".
				 " INNER JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtp.problem_num = mtcp.problem_num ".
				 " AND mtcp.problem_table_type='2'".
				 " AND mtcp.mk_flg='0'".
				 " WHERE 1 ".
				 $and.
				 " ORDER BY mtp.problem_num;";
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
		$submit_msg = "問題設定(数学検定)情報を検証へアップしますがよろしいですか？";

		$html .= "問題設定(数学検定)情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>採点単元関連情報：".T_BOOK_UNIT_TEST_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>復習ユニット関連情報：".T_BOOK_UNIT_LMS_UNIT."</td>\n";
		$html .= "<td>\n";
		$html .= $local2_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote2_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題マスタ：".T_MS_TEST_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local3_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote3_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>出題単元関連情報：".T_MATH_TEST_CONTROL_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local4_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote4_html;
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
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";
		$html .= "</form>\n";
		$html .= "※閲覧DB：検証バッチWeb、閲覧Web：検証バッチWebのデーターやファイルは、<br>\n　アップデートリストで本番にアップや削除をしてもデーターが消えないのでご注意ください。<br>\n";
	} else {
		$html .= "問題設定(数学検定)情報が設定されておりません。<br>\n";

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
	flush();

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
	$DELETE_SQL = array();
	$BOOK_UNIT_ID_ARRAY = array();
	$PROBLEM_NUM_ARRAY = array();

	//	クエリー
	$and = "";
	$and .= "AND mtcp.mk_flg = '0' ";
	if ($_SESSION['view_session']['class_id'] != "") {
		$and .= " AND mtcp.class_id='".$_SESSION['view_session']['class_id']."' ";
	}

	//	math_test_contorol_problem
	$sql = "SELECT DISTINCT mtcp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" WHERE 1 ".
			$and.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MATH_TEST_CONTROL_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}
	//	math_test_contorol_problem
	$sql = "DELETE mtcp FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;



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
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();
		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
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

	//	検証バッチDBデーター追加
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				//if ($last_table_name != $table_name) {
				//	//echo "<br>\n";
				//	//echo $table_name." 情報更新中<br>\n";
				//	//flush();
				//}
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
			$last_table_name = $table_name;
		}
	}


	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//インサートクエリ作成
	//	book_unit_test_problem
	$sql = "SELECT DISTINCT butp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
			" AND butp.default_test_num='0' ".
			" WHERE 1 ".
			$and.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_TEST_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}
	//	book_unit_lms_unit
	$sql = "SELECT DISTINCT bulu.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
			" WHERE 1 ".
			$and.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_LMS_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}
	//	ms_test_problem
	$sql = "SELECT DISTINCT mtp.* FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" WHERE 1 ".
			$and.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	// 削除クエリ作成
	// book_unit_test_problem
	$sql = "DELETE butp FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON mtp.problem_num = butp.problem_num ".
			" AND butp.default_test_num='0' ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;
	//	book_unit_lms_unit
	$sql = "DELETE bulu FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON mtp.problem_num = bulu.problem_num ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;
	//	ms_test_problem
	$sql = "DELETE mtp FROM ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ".
			" INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON mtcp.problem_num = mtp.problem_num ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;

	//	削除
	if ($DELETE_SQL) {
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();
		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
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
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				//if ($last_table_name != $table_name) {
				//	//echo "<br>\n";
				//	//echo $table_name." 情報更新中<br>\n";
				//	//flush();
				//}
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
			$last_table_name = $table_name;
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

	echo "<br>\n";
	echo "db最適化中<br>\n";
	flush();

	//	テーブル最適化
	//	book_unit_test_problem
	$sql = "OPTIMIZE TABLE ".T_BOOK_UNIT_TEST_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	//	book_unit_lms_unit
	$sql = "OPTIMIZE TABLE ".T_BOOK_UNIT_LMS_UNIT.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	//	ms_test_problem
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	//	math_test_question_problem
	$sql = "OPTIMIZE TABLE ".T_MATH_TEST_CONTROL_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {

		echo "<br>\n";
		echo "ファイルアップロード開始<br>\n";
		flush();

		//	問題ファイル取得
		$PROBLEM_LIST = array();
		$sql  = " SELECT DISTINCT mtp.problem_num FROM ".T_BOOK_UNIT_TEST_PROBLEM." butp ".
 				 " INNER JOIN ".T_MS_TEST_PROBLEM." mtp ON butp.problem_num = mtp.problem_num ".
 				 " AND butp.default_test_num='0'".
 				 " AND butp.problem_table_type='2'".
 				 " AND butp.mk_flg='0'".
 				 " INNER JOIN ".T_MATH_TEST_CONTROL_PROBLEM." mtcp ON mtp.problem_num = mtcp.problem_num ".
 				 " AND mtcp.problem_table_type='2'".
 				 " AND mtcp.mk_flg='0'".
 				 " WHERE 1 ".
 				 $and.
 				 " ORDER BY mtp.problem_num;";
		if ($result = $cdb->query($sql)) {
			while ($list=$cdb->fetch_assoc($result)) {
				$problem_num = $list['problem_num'];
				$PROBLEM_LIST[] = $problem_num;
			}
		}

//echo "<br>\n";
//echo "問題番号一覧<br>\n";
//echo pre($PROBLEM_LIST);
//flush();

		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		// MATERIAL_TEST_IMG_DIR:../material/test_img/
		// REMOTE_MATERIAL_TEST_IMG_DIR:/www/material/test_img/
		// KBAT_DIR:=>/data/apprelease/Release/Contents
 		// BASE_DIR:/data/home
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

		echo "<br>\n";
		echo "音声ファイルアップロード開始<br>\n";
		flush();

		// MATERIAL_TEST_VOICE_DIR:../material/test_voice/
		// REMOTE_MATERIAL_TEST_VOICE_DIR:/www/material/test_voice/
		// KBAT_DIR:=>/data/apprelease/Release/Contents
		// BASE_DIR:/data/home
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

	echo "<br>\n";
	echo "検証サーバーへデーター反映中<br>\n";
	flush();

	//	検証サーバー反映処理記録
	$update_num = 0;
	$sql  = "INSERT INTO test_update_check".
			" (type, start_time)".
			" VALUE('2', now());";
	if ($connect_db->exec_query($sql)) {
		$update_num = $connect_db->insert_id();
	}

	//	検証バッチから検証webへ
	if ($_POST['fileup'] != 1) {
		$_POST['fileup'] = "0";
	}
	$_SESSION['view_session']['fileup'] = $_POST['fileup'];
	$class_id = $_SESSION['view_session']['class_id'];
	if (!$class_id) {
		$class_id = "0";
	}
	//$book_unit_id = $_SESSION['view_session']['book_unit_id'];
	//if (!$book_unit_id) {
	//	$book_unit_id = "0";
	//}
	$fileup = $_SESSION['view_session']['fileup'];
	if (!$fileup) {
		$fileup = "0";
	}

	// update start oda 2015/11/10 プラクティスアップデート不具合修正
	//$send_data = " '".$class_id."' '".$fileup."' '".$update_num."'";
	//$command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data;
	$send_data = " '".$class_id."' '".$fileup."' '".$update_num."' '".$_SESSION['myid']['id']."'";
	//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/TESTCONTENTSUP.php '2' 'test_".MODE."'".$send_data;		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// update end oda 2015/11/10

	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command .= " > /dev/null &";
	exec($command);
	//echo $command."<br>\n";

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
// 			" AND state='1'";
// 	if ($_SESSION['view_session']['class_id'] != "0" && $_SESSION['view_session']['class_id'] != "") {
// 		$sql .= " AND course_num='".$_SESSION['view_session']['class_id']."'";
// 	} else {
// 		$sql .= " AND course_num IS NULL";
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
// 				 " AND state!='0'";
// 		 if ($_SESSION['view_session']['class_id'] != "0" && $_SESSION['view_session']['class_id'] != "") {
// 	 		$where .= " AND course_num='".$_SESSION['view_session']['class_id']."'";
// 	 	} else {
// 	 		$where .= " AND course_num IS NULL";
// 	 	}
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
// 		if ($_SESSION['view_session']['class_id']) {
// 			$INSERT_DATA['course_num'] = $_SESSION['view_session']['class_id'];
// 		}
// 		if ($_POST['fileup'] == "1") {
// 			$INSERT_DATA['stage_num'] = $_POST['fileup'];
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

	$html  = "問題設定(数学検定)情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
