<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		問題設定(すらら英単語テスト) アップデート
 *
 * @author Azet
 */

/*
	基本情報
		本番バッチではms_test_category2_problemの以下の値でグルーピングしてアップデートする。
			test_type_num='1'					// アップデート指定画面のテスト種類のリストボックスの値を利用（必須指定）
			test_category1_num='J2'				// アップデート指定画面のテスト種別1のリストボックスの値を利用（任意指定）
			test_category2_num='C000000001'		// アップデート指定画面のテスト種別2のリストボックスの値を利用（任意指定）

		変更テーブルは3テーブルとなり以下の手順で処理をしていく
			(問題)	※画像情報もここで取得する。
			コピー先の「ms_test_category2_problem」のの該当する情報を削除
			コピー元の「ms_test_category2_problem」の該当する情報を作成し、コピー先にREPLACE INTOで更新する
			(問題マスタ)
			コピー先の「ms_test_problem」のの該当する情報を削除
			コピー元の「ms_test_problem」の該当する情報を作成し、コピー先にREPLACE INTOで更新する
			(LMS単元)
			コピー先の「book_unit_lms_unit」の該当する情報を削除
			コピー元の「book_unit_lms_unit」の該当する情報を作成し、コピー先にインサートする（PKが無いのでINSERT処理を実行）

		プラクティスアップデートでは、削除データも反映するので、mk_flgやdisplay/stateの条件は追加しない。
		画像や音声ファイルをアップする問題番号取得の際は、mk_flgの判断を行う（削除データに対する画像や音声の修正が有ってもテストの出題対象にはならず、使用しない為）
*/


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function action() {

	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		set_time_limit(0);
		echo "更新開始\n";
		echo str_pad(" ",4096)."<br>\n";
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
 * テスト種類、テスト種別1、テスト種別2選択
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_unit_view($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TEST_TYPE = array();
	$L_TEST_TYPE = get_test_type(); // ms_test_typeのレコードを取得

	$L_TEST_CATEGORY1 = array();
	$L_TEST_CATEGORY1 = get_test_category1(); // ms_test_category1のレコードを取得

	$L_TEST_CATEGORY2 = array();
	$L_TEST_CATEGORY2 = get_test_category2(); // ms_test_category2のレコードを取得

	$html = "";
	if ($ERROR) {
		$html .= ERROR($ERROR);
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
			$test_type_num = $VALUES['test_type_num'];
			$test_category1_num = $VALUES['test_category1_num'];
			$test_category2_num = $VALUES['test_category2_num'];

			if ($test_type_num < 1) {
				continue;
			} elseif ($test_category1_num == "0" || $test_category1_num == "") {
				$PMUL[$test_type_num] = 1;
			} elseif ($test_category2_num == "0" || $test_category2_num == "") {
				$PMUL[$test_type_num][$test_category1_num] = 1;
			}
		}
	}

	// テスト種類
	$course_count = "";
	$test_type_num_html  = "";
	$test_type_num_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_TEST_TYPE AS $test_type_num => $L_TEST_TYPE_INFO) {

		if ($L_TEST_TYPE_INFO['test_type_name'] == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['view_session']['test_type_num'] == $test_type_num) {
			$selected = "selected";
		}
		$test_type_num_html .= "<option value=\"".$test_type_num."\" ".$selected.">".$L_TEST_TYPE_INFO['test_type_name']."</option>\n";
	}

	$last_select_flg = 0;
	$test_type_num = $_SESSION['view_session']['test_type_num'];
	if ($PMUL[$test_type_num] == 1) {
		$last_select_flg = 1;
	}

	// テスト種別1
	$test_category1_num_html = "";
	$test_category1_num_html .= "<option value=\"0\">選択して下さい</option>\n";
	if ($_SESSION['view_session']['test_type_num'] > 0) {
		if ($last_select_flg == 1) {
			$test_category1_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			foreach($L_TEST_CATEGORY1 as $test_category1_num => $L_TEST_CATEGORY1_INFO) {
				$selected = "";
				if ($_SESSION['view_session']['test_category1_num'] == $test_category1_num) {
					$selected = "selected";
				}
				$test_category1_num_html .= "<option value=\"".$test_category1_num."\" ".$selected.">".$L_TEST_CATEGORY1_INFO['test_category1_name']."</option>\n";
			}
		}
	} else {
		$test_category1_num_html .= "<option value=\"0\">--------</option>\n";
	}

	$last_select_flg = 0;
	$test_category1_num = $_SESSION['view_session']['test_category1_num'];
	if ($PMUL[$test_type_num][$test_category1_num] == 1) {
		$last_select_flg = 1;
	}

	//	テスト種別2
	$test_category2_num_html = "";
	$test_category2_num_html .= "<option value=\"0\">選択して下さい</option>\n";
	if ($_SESSION['view_session']['test_category1_num'] != "0" && $_SESSION['view_session']['test_category1_num'] != "") {
		if ($last_select_flg == 1) {
			$test_category2_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			foreach($L_TEST_CATEGORY2 as $test_category2_num => $L_TEST_CATEGORY2_INFO) {
				$selected = "";
				if ($_SESSION['view_session']['test_category2_num'] == $test_category2_num) {
					$selected = "selected";
				}
				$test_category2_num_html .= "<option value=\"".$test_category2_num."\" ".$selected.">".$L_TEST_CATEGORY2_INFO['test_category2_name']."</option>\n";
			}
		}
	} else {
		$test_category2_num_html .= "<option value=\"0\">--------</option>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>テスト種類</td>\n";
	$html .= "<td>テスト種別1</td>\n";
	$html .= "<td>テスト種別2</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_type_num\" onchange=\"submit();\">\n".$test_type_num_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_category1_num\" onchange=\"submit();\">\n".$test_category1_num_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_category2_num\" onchange=\"submit();\">\n".$test_category2_num_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if ($_SESSION['view_session']['test_type_num'] < 1) {
		$html .= "問題をアップする各項目を選択してください。<br>\n";
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}

	return $html;
}


/**
 * テスト種類、テスト種別1、テスト種別2選択セッションセット
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function view_set_session() {

	$test_type_num = $_SESSION['view_session']['test_type_num'];
	$test_category1_num = $_SESSION['view_session']['test_category1_num'];
	$test_category2_num = $_SESSION['view_session']['test_category2_num'];

	unset($_SESSION['view_session']);

	if ($_POST['test_type_num'] > 0) {
		$_SESSION['view_session']['test_type_num'] = $_POST['test_type_num'];
	} else {
		return;
	}

	if ($_POST['test_type_num'] == $test_type_num && $_POST['test_category1_num'] != "") {
		$_SESSION['view_session']['test_category1_num'] = $_POST['test_category1_num'];
	} else {
		return;
	}

	if ($_POST['test_category1_num'] == $test_category1_num && $_POST['test_category2_num'] != "") {
		$_SESSION['view_session']['test_category2_num'] = $_POST['test_category2_num'];
	} else {
		return;
	}

}



/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function default_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$_POST['test_type_num']."\">\n";
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

	//	絞込クエリー情報
	//	情報取得クエリー
	$where = "";
	if ($_SESSION['view_session']['test_type_num'] > "0") {
		$where .= " AND mtcp.test_type_num='".$_SESSION['view_session']['test_type_num']."'";
	}
	if ($_SESSION['view_session']['test_category1_num'] != "0" && $_SESSION['view_session']['test_category1_num'] != "") {
		$where .= " AND mtcp.test_category1_num='".$_SESSION['view_session']['test_category1_num']."'";
	}
	if ($_SESSION['view_session']['test_category2_num'] != "0" && $_SESSION['view_session']['test_category2_num'] != "") {
		$where .= " AND mtcp.test_category2_num='".$_SESSION['view_session']['test_category2_num']."'";

	}

	//	ms_test_category2_problem
	$sql  = "SELECT MAX(mtcp.upd_date) AS upd_date FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
			" WHERE 1 ".
			$where.
			";";

	$sql_cnt  = "SELECT mtcp.* FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
			" WHERE 1 ".
			$where.
			";";

	//	ms_test_problem
	$sql2 = "SELECT MAX(mtp.upd_date) AS upd_date FROM ".T_MS_TEST_PROBLEM." mtp".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=mtp.problem_num".
				" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
				$where.
			" WHERE mtcp.problem_num>0;";

	$sql2_cnt = "SELECT DISTINCT mtp.* FROM ".T_MS_TEST_PROBLEM." mtp".
				" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=mtp.problem_num".
					" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
					$where.
				" WHERE mtcp.problem_num>0;";

	//	book_unit_lms_unit
	$sql3_2 = "SELECT MAX(bulu.upd_date) AS upd_date FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
				" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
				$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='3';"; // すらら英単語テスト専用問題

	$sql3_1 = "SELECT MAX(bulu.upd_date) AS upd_date FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
				" AND mtcp.problem_table_type='1'".
				$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='1';";

	$sql3_2_cnt = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
				" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
					" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
					$where.
				" WHERE mtcp.problem_num>0".
				" AND bulu.problem_table_type='3';"; // すらら英単語テスト専用問題
	$sql3_1_cnt = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
				" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
					" AND mtcp.problem_table_type='1'".
					$where.
				" WHERE mtcp.problem_num>0".
				" AND bulu.problem_table_type='1';";

//echo "sql3_2=".$sql3_2."<br>";
//echo "sql3_1=".$sql3_1."<br>";
//echo "sql3_2_cnt=".$sql3_2_cnt."<br>";
//echo "sql3_1_cnt=".$sql3_1_cnt."<br>";

/*
echo $sql."<br>\n<br>\n";
echo $sql_cnt."<br>\n<br>\n";
echo $sql2."<br>\n<br>\n";
echo $sql2_cnt."<br>\n<br>\n";
echo $sql3_2."<br>\n<br>\n";
echo $sql3_2_cnt."<br>\n<br>\n";
echo $sql3_1."<br>\n<br>\n";
echo $sql3_1_cnt."<br>\n<br>\n";
*/

	//	ローカルサーバー
	$dsp_flg = 0;
	//	ms_test_category2_problem
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

	//	book_unit_lms_unit
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

	// -- 閲覧DB
	//	ms_test_category2_problem
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

	//	book_unit_lms_unit
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

	//	ファイル更新情報取得
	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$sql  = "SELECT DISTINCT mtcp.problem_num FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
			" WHERE 1 ".
			$where.
			" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
			" AND mtcp.mk_flg='0'".
			" ORDER BY mtcp.problem_num;";
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
		$submit_msg = "問題設定(すらら英単語テスト)情報を検証へアップしますがよろしいですか？";

		$html .= "問題設定(すらら英単語テスト)情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"pupform\">\n";
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
		$html .= "<td>問題管理：".T_MS_TEST_CATEGORY2_PROBLEM."</td>\n";
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
		$html .= "<td>LMS単元：".T_BOOK_UNIT_LMS_UNIT."</td>\n";
		$html .= "<td>\n";
		$html .= $local3_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote3_html;
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
		$html .= "問題設定(すらら英単語テスト)情報が設定されておりません。<br>\n";

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
 * AC:[A]管理者 UC1:[L06]勉強をする.
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

	$db_data = $L_CONTENTS_DB['92']; // 10.3.11.100の場合は'SRLBS99'

	//	検証バッチDB接続
	$connect_db = new connect_db();
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
	if ($_SESSION['view_session']['test_type_num'] > 0) {
		$where .= " AND mtcp.test_type_num='".$_SESSION['view_session']['test_type_num']."'";
	}
	if ($_SESSION['view_session']['test_category1_num'] != "0" && $_SESSION['view_session']['test_category1_num'] != "") {
		$where .= " AND mtcp.test_category1_num='".$_SESSION['view_session']['test_category1_num']."'";
	}
	if ($_SESSION['view_session']['test_category2_num'] != "0" && $_SESSION['view_session']['test_category2_num'] != "") {
		$where .= " AND mtcp.test_category2_num='".$_SESSION['view_session']['test_category2_num']."'";
	}

	echo "<br>\n";
	echo "db情報取得中<br>\n";
	flush();

	//	登録データー取得->REPLACE作成用情報生成(PKやUNIQUEが有るテーブルが対象です）
	//	ms_test_category2_problem
	$sql  = "SELECT DISTINCT mtcp.* FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ".
			" WHERE 1 ".
			$where.
			";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_CATEGORY2_PROBLEM, $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	ms_test_problem
	$sql  = "SELECT DISTINCT mtp.* FROM ".T_MS_TEST_PROBLEM." mtp ".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=mtp.problem_num ".
				" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
			$where.
			" WHERE mtcp.problem_num>0;";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_PROBLEM, $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	登録データー取得->INSERT作成用情報生成(PKやUNIQUEが無いテーブルが対象です）
	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
			" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
			$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='3';"; // すらら英単語テスト専用問題
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_LMS_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "SELECT DISTINCT bulu.* FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
			" AND mtcp.problem_table_type='1'".
			$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_UNIT_LMS_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー(PKやUNIQUEが無いテーブルが対象です）
	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "DELETE bulu FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
			" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
			$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='3';"; // すらら英単語テスト専用問題
	$DELETE_SQL['book_unit_lms_unit_3'] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM ".T_BOOK_UNIT_LMS_UNIT." bulu".
			" INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp ON mtcp.problem_num=bulu.problem_num".
			" AND mtcp.problem_table_type='1'".
			$where.
			" WHERE mtcp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL['book_unit_lms_unit_1'] = $sql;

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

	//	削除処理
	if ($DELETE_SQL) {

		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();

		foreach ($DELETE_SQL AS $table_name => $sql) {
			if (!$connect_db->exec_query($sql)) {
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
				$sql  = "ROLLBACK";
				if (!$connect_db->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$connect_db->close();
				return $ERROR;
			}
		}
	}

	//	検証バッチDBデーター追加
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	// REPLACE処理（対象テーブル：ms_test_category2_problem/ms_test_problem）
	if (count($REPLACE_NAME) && count($REPLACE_VALUE)) {
		foreach ($REPLACE_NAME AS $table_name => $replace_name) {
			if ($REPLACE_VALUE[$table_name]) {
				foreach ($REPLACE_VALUE[$table_name] AS $values) {
					$sql  = "REPLACE INTO ".$table_name.
					" (".$replace_name.") ".
					" VALUES".$values.";";
					if (!$connect_db->exec_query($sql)) {
						$ERROR[] = "SQL REPLACE ERROR<br>$sql";

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

	// INSERT処理（対象テーブル：book_unit_lms_unit）
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
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
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_CATEGORY2_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_BOOK_UNIT_LMS_UNIT.";";
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
		$sql  = "SELECT DISTINCT mtcp.problem_num FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mtcp".
				" WHERE 1 ".
				$where.
				" AND mtcp.problem_table_type='3'". // すらら英単語テスト専用問題
				" AND mtcp.mk_flg='0'".
				" ORDER BY mtcp.problem_num;";
		if ($result = $cdb->query($sql)) {
			while ($list=$cdb->fetch_assoc($result)) {
				$problem_num = $list['problem_num'];
				$PROBLEM_LIST[] = $problem_num;
			}
		}

		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		//	画像ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_TEST_IMG_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		// /data/apprelease/Release/Contents/www/material/test_img/
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		// /data/home/www/material/template/test_img/
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
		// /data/apprelease/Release/Contents/www/material/test_img/
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
		test_remote_set_file($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

		echo "<br>\n";
		echo "音声ファイルアップロード開始<br>\n";
		flush();

		//	音声ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_TEST_VOICE_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		// /data/apprelease/Release/Contents/www/material/test_voice/
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		// /data/home/www/material/template/test_voice/
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
		// /data/apprelease/Release/Contents/www/material/test_voice/
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
	if ($_POST['fileup'] != 1) { $_POST['fileup'] = "0"; }
	$_SESSION['view_session']['fileup'] = $_POST['fileup'];
	$test_category1_num = $_SESSION['view_session']['test_category1_num'];
	if (!$test_category1_num) { $test_category1_num = "0"; }
	$test_category2_num = $_SESSION['view_session']['test_category2_num'];
	if (!$test_category2_num) { $test_category2_num = "0"; }
	$fileup = $_SESSION['view_session']['fileup'];
	if (!$fileup) { $fileup = "0"; }

	$send_data = " '".$_SESSION['view_session']['test_type_num']."' '".$test_category1_num."' '".$test_category2_num."' '".$fileup."' '".$update_num."' '".$_SESSION['myid']['id']."'";
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data;
	$command .= " > /dev/null &";
	exec($command);

	if ($update_num > 0) {
		$end_flg = 1;
		for ($i=0; $i<=600; $i++) {
			$sql  = "SELECT state FROM test_update_check".
					" WHERE update_num='".$update_num."';";
			if ($result = $connect_db->query($sql)) {
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

	//	検証バッチDB切断
	$connect_db->close();

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function update_end_html() {

	$html  = "問題設定(すらら英単語テスト)情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * テスト種類を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function get_test_type() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TEST_TYPE = array();

	// 本番反映後に取り下げる可能性があるので、削除フラグは条件に入れない。
	$sql = "SELECT * FROM ".T_MS_TEST_TYPE." WHERE mk_flg = '0' ORDER BY list_num ASC;";

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$L_TEST_TYPE[$list['test_type_num']] = $list;
		}
	}

	return $L_TEST_TYPE;

}

/**
 * テスト種別1を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function get_test_category1() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TEST_CATEGORY1 = array();

	if($_SESSION['view_session']['test_type_num'] > '0'){
		// 本番反映後に取り下げる可能性があるので、削除フラグは条件に入れない。
		$sql  = "SELECT ms_test_category1.* ".
				"FROM ".T_MS_TEST_CATEGORY1." ms_test_category1 ".
				"INNER JOIN ".T_MS_TEST_TYPE." ms_test_type ON ms_test_category1.test_type_num = ms_test_type.test_type_num ".
				"WHERE ms_test_category1.mk_flg = '0' ".
				" AND ms_test_type.test_type_num = '".$_SESSION['view_session']['test_type_num']."' ".
				" AND ms_test_type.mk_flg = '0' ".
				" ORDER BY ms_test_category1.list_num ASC ".
				";";
		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)) {
				$L_TEST_CATEGORY1[$list['test_category1_num']] = $list;
			}
		}
	}

	return $L_TEST_CATEGORY1;

}

/**
 * テスト種別2を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function get_test_category2() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TEST_CATEGORY2 = array();

	if($_SESSION['view_session']['test_type_num'] > '0' && $_SESSION['view_session']['test_category1_num'] > '0'){
		// 本番反映後に取り下げる可能性があるので、削除フラグは条件に入れない。
		$sql  = "SELECT ms_test_category2.* ".
				"FROM ".T_MS_TEST_CATEGORY2." ms_test_category2 ".
				"INNER JOIN ".T_MS_TEST_CATEGORY1." ms_test_category1 ON ms_test_category2.test_type_num = ms_test_category1.test_type_num ".
				" AND ms_test_category2.test_category1_num = ms_test_category1.test_category1_num ".
				"INNER JOIN ".T_MS_TEST_TYPE." ms_test_type ON ms_test_category1.test_type_num = ms_test_type.test_type_num ".
				"WHERE ms_test_category2.mk_flg = '0' ".
				" AND ms_test_category1.test_category1_num = '".$_SESSION['view_session']['test_category1_num']."' ".
				" AND ms_test_category1.mk_flg = '0' ".
				" AND ms_test_type.test_type_num = '".$_SESSION['view_session']['test_type_num']."' ".
				" AND ms_test_type.mk_flg = '0' ".
				" ORDER BY ms_test_category2.list_num ASC ".
				";";
		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)) {
				$L_TEST_CATEGORY2[$list['test_category2_num']] = $list;
			}
		}
	}

	return $L_TEST_CATEGORY2;

}
?>
