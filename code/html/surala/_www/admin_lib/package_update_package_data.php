<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　プラクティスアップデート
 * サブプログラム	カテゴリー、ユニットキーアップデート
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $L_NAME
 * @return string HTML
 */
function sub_start($L_NAME) {

	if (ACTION == "update") {
		$ERROR = update();
	} elseif (ACTION == "db_session") {
		$ERROR = select_database();
	} elseif (ACTION == "view_session") {
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		$html .= update_end_html($L_NAME);
	} else {
		$html .= default_html($L_NAME,$ERROR);
	}

	return $html;
}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $L_NAME
 * @param array $ERROR
 * @return string HTML
 */
function default_html($L_NAME,$ERROR) {

	$html = "";

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= "</form>\n";

	unset($BASE_DATA);
	unset($MAIN_DATA);
	//サーバー情報取得
	if (!$_SESSION['select_db']) {
		unset($_SESSION['view_session']);
		return $html;
	}

	//	選択リスト
	$html .= select_course();

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br />\n";
	}

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	閲覧DB接続
	$ecdb = new connect_db();
	$ecdb->set_db($_SESSION['select_db']);
	$ERROR = $ecdb->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br />\n";
	}

	//	情報取得クエリー
	//	package_course
	$sql1 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."';";

	//	package_stage
	$sql2 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_STAGE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql2 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	$sql2 .= ";";

	//	package_lesson
	$sql3 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_LESSON.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql3 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql3 .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	$sql3 .= ";";

	//	package_unit
	$sql4 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_UNIT.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql4 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql4 .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql4 .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	$sql4 .= ";";

	//	package_block
	$sql5 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_BLOCK.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql5 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql5 .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql5 .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql5 .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	$sql5 .= ";";

	//	package_list
	$sql6 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql6 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql6 .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql6 .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql6 .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql6 .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql6 .= ";";

	//	package_unit_list
	$sql7 = "SELECT MAX(upd_date) AS upd_date FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql7 .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql7 .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql7 .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql7 .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql7 .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql7 .= ";";

	//	ローカルサーバー
	//	package_course
	$local1_html = "";
	$local1_time = "";
	if ($result = $cdb->query($sql1)) {
		$list = $cdb->fetch_assoc($result);
		$local1_time = $list['upd_date'];
	}
	if ($local1_time) {
		$local1_html = $local1_time;
	} else {
		$local1_html = "データーがありません。";
	}

	//	package_stage
	$local2_html = "";
	$local2_time = "";
	if ($result = $cdb->query($sql2)) {
		$list = $cdb->fetch_assoc($result);
		$local2_time = $list['upd_date'];
	}
	if ($local2_time) {
		$local2_html = $local2_time;
	} else {
		$local2_html = "データーがありません。";
	}

	//	package_lesson
	$local3_html = "";
	$local3_time = "";
	if ($result = $cdb->query($sql3)) {
		$list = $cdb->fetch_assoc($result);
		$local3_time = $list['upd_date'];
	}
	if ($local3_time) {
		$local3_html = $local3_time;
	} else {
		$local3_html = "データーがありません。";
	}

	//	package_unit
	$local4_html = "";
	$local4_time = "";
	if ($result = $cdb->query($sql4)) {
		$list = $cdb->fetch_assoc($result);
		$local4_time = $list['upd_date'];
	}
	if ($local4_time) {
		$local4_html = $local4_time;
	} else {
		$local4_html = "データーがありません。";
	}

	//	package_block
	$local5_html = "";
	$local5_time = "";
	if ($result = $cdb->query($sql5)) {
		$list = $cdb->fetch_assoc($result);
		$local5_time = $list['upd_date'];
	}
	if ($local5_time) {
		$local5_html = $local5_time;
	} else {
		$local5_html = "データーがありません。";
	}

	//	package_list
	$local6_html = "";
	$local6_time = "";
	if ($result = $cdb->query($sql6)) {
		$list = $cdb->fetch_assoc($result);
		$local6_time = $list['upd_date'];
	}
	if ($local6_time) {
		$local6_html = $local6_time;
	} else {
		$local6_html = "データーがありません。";
	}

	//	package_unit_list
	$local7_html = "";
	$local7_time = "";
	if ($result = $cdb->query($sql7)) {
		$list = $cdb->fetch_assoc($result);
		$local7_time = $list['upd_date'];
	}
	if ($local7_time) {
		$local7_html = $local7_time;
	} else {
		$local7_html = "データーがありません。";
	}


	// -- 閲覧DB
	//	package_course
	$remote1_html = "";
	$remote1_time = "";
	if ($result = $ecdb->query($sql1)) {
		$list = $ecdb->fetch_assoc($result);
		$remote1_time = $list['upd_date'];
	}
	if ($remote1_time) {
		$remote1_html = $remote1_time;
	} else {
		$remote1_html = "データーがありません。";
	}

	//	package_stage
	$remote2_html = "";
	$remote2_time = "";
	if ($result = $ecdb->query($sql2)) {
		$list = $ecdb->fetch_assoc($result);
		$remote2_time = $list['upd_date'];
	}
	if ($remote2_time) {
		$remote2_html = $remote2_time;
	} else {
		$remote2_html = "データーがありません。";
	}

	//	book_unit_test_problem
	$remote3_html = "";
	$remote3_time = "";
	if ($result = $ecdb->query($sql3)) {
		$list = $ecdb->fetch_assoc($result);
		$remote3_time = $list['upd_date'];
	}
	if ($remote3_time) {
		$remote3_html = $remote3_time;
	} else {
		$remote3_html = "データーがありません。";
	}

	//	package_unit
	$remote4_html = "";
	$remote4_time = "";
	if ($result = $ecdb->query($sql4)) {
		$list = $ecdb->fetch_assoc($result);
		$remote4_time = $list['upd_date'];
	}
	if ($remote4_time) {
		$remote4_html = $remote4_time;
	} else {
		$remote4_html = "データーがありません。";
	}

	//	package_unit
	$remote5_html = "";
	$remote5_time = "";
	if ($result = $ecdb->query($sql5)) {
		$list = $ecdb->fetch_assoc($result);
		$remote5_time = $list['upd_date'];
	}
	if ($remote5_time) {
		$remote5_html = $remote5_time;
	} else {
		$remote5_html = "データーがありません。";
	}

	//	package_unit
	$remote6_html = "";
	$remote6_time = "";
	if ($result = $ecdb->query($sql6)) {
		$list = $ecdb->fetch_assoc($result);
		$remote6_time = $list['upd_date'];
	}
	if ($remote6_time) {
		$remote6_html = $remote6_time;
	} else {
		$remote6_html = "データーがありません。";
	}

	//	package_unit
	$remote7_html = "";
	$remote7_time = "";
	if ($result = $ecdb->query($sql7)) {
		$list = $ecdb->fetch_assoc($result);
		$remote7_time = $list['upd_date'];
	}
	if ($remote7_time) {
		$remote7_html = $remote7_time;
	} else {
		$remote7_html = "データーがありません。";
	}

	if ($local1_time || $remote1_time || $local2_time || $remote2_time || $local3_time || $remote3_time || $local4_time || $remote4_time
		 || $local5_time || $remote5_time || $local6_time || $remote6_time || $local7_time || $remote7_time) {
		$submit_msg = "選択されたカテゴリー以下の速習コース情報を検証へアップしますがよろしいですか？";

		$html .= "<br>\n";
		$html .= "選択されたカテゴリー以下の速習コース情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>コース：".T_PACKAGE_COURSE."</td>\n";
		$html .= "<td>\n";
		$html .= $local1_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote1_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>ステージ：".T_PACKAGE_STAGE."</td>\n";
		$html .= "<td>\n";
		$html .= $local2_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote2_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>レッスン：".T_PACKAGE_LESSON."</td>\n";
		$html .= "<td>\n";
		$html .= $local3_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote3_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>ユニット：".T_PACKAGE_UNIT."</td>\n";
		$html .= "<td>\n";
		$html .= $local4_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote4_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>ブロック：".T_PACKAGE_BLOCK."</td>\n";
		$html .= "<td>\n";
		$html .= $local5_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote5_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>リスト：".T_PACKAGE_LIST."</td>\n";
		$html .= "<td>\n";
		$html .= $local6_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote6_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>ユニットキー：".T_PACKAGE_UNIT_LIST."</td>\n";
		$html .= "<td>\n";
		$html .= $local7_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote7_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "速習コース情報が設定されておりません。<br>\n";
	}

	//	閲覧DB切断
	$ecdb->close();

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function update() {

	global $L_CONTENTS_DB;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	検証バッチDB接続
	$kcdb = new connect_db();
	$kcdb->set_db($L_CONTENTS_DB['92']);
	$ERROR = $kcdb->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	//	コース
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_COURSE, $INSERT_NAME, $INSERT_VALUE);
	}

	//	ステージ
	$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_STAGE, $INSERT_NAME, $INSERT_VALUE);
	}

	//	レッスン
	$sql  = "SELECT * FROM ".T_PACKAGE_LESSON.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_LESSON, $INSERT_NAME, $INSERT_VALUE);
	}

	//	ユニット
	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	ブロック
	$sql  = "SELECT * FROM ".T_PACKAGE_BLOCK.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_BLOCK, $INSERT_NAME, $INSERT_VALUE);
	}

	//	リスト
	$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_LIST, $INSERT_NAME, $INSERT_VALUE);
	}

	//	ユニットリスト
	$sql  = "SELECT * FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql .= ";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_PACKAGE_UNIT_LIST, $INSERT_NAME, $INSERT_VALUE);
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$kcdb->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$kcdb->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$DELETE_SQL = array();
	//	コース
	$sql  = "DELETE FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."';";
	$DELETE_SQL[] = $sql;
	//	ステージ
	$sql  = "DELETE FROM ".T_PACKAGE_STAGE.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	レッスン
	$sql  = "DELETE FROM ".T_PACKAGE_LESSON.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ユニット
	$sql  = "DELETE FROM ".T_PACKAGE_UNIT.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ブロック
	$sql  = "DELETE FROM ".T_PACKAGE_BLOCK.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	リスト
	$sql  = "DELETE FROM ".T_PACKAGE_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ユニットリスト
	$sql  = "DELETE FROM ".T_PACKAGE_UNIT_LIST.
			" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		$err_flg = 0;
		foreach ($DELETE_SQL AS $sql) {
			if (!$kcdb->exec_query($sql)) {
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
			if (!$kcdb->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$kcdb->close();
			return $ERROR;
		}
	}

	//	検証バッチDBデーター追加
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				foreach ($INSERT_VALUE[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
							" (".$insert_name.") ".
							" VALUES".$values.";";
					if (!$kcdb->exec_query($sql)) {
						$ERROR[] = "SQL INSERT ERROR<br>$sql";
						$sql  = "ROLLBACK";
						if (!$kcdb->exec_query($sql)) {
							$ERROR[] = "SQL ROLLBACK ERROR";
						}
						$kcdb->close();
						return $ERROR;
					}
				}
			}
		}
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$kcdb->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$kcdb->close();
		return $ERROR;
	}

	//	テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_COURSE.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_STAGE.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_LESSON.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_UNIT.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_BLOCK.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_LIST.";";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ".T_PACKAGE_UNIT_LIST.";";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$kcdb->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$kcdb->close();

	//	検証バッチから検証webへ
	$send_data = " '".$_SESSION['view_session']['pk_course_num']."' '".$_SESSION['view_session']['pk_stage_num']."' '".$_SESSION['view_session']['pk_lesson_num']."' '".$_SESSION['view_session']['pk_unit_num']."' '".$_SESSION['view_session']['pk_block_num']."' '".$_SESSION['view_session']['pk_list_num']."'";
	// $command = "ssh suralacore01@srlbtw21 ./PACKAGECONTENTSUP.cgi '2' 'package_data'".$send_data; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/PACKAGECONTENTSUP.cgi '2' 'package_data'".$send_data; // add 2018/03/19 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/24 yamaguchi

	//	ログ保存 --
	unset($package_mate_upd_log_num);
	$sql  = "SELECT package_mate_upd_log_num FROM ".T_PACKAGE_MATE_UPD_LOG.
			" WHERE update_mode='package_data'".
			" AND pk_course_num='".$_SESSION['view_session']['pk_course_num']."'";
	if ($_SESSION['view_session']['pk_stage_num']) {
		$sql .= " AND pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'";
	}
	if ($_SESSION['view_session']['pk_lesson_num']) {
		$sql .= " AND pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'";
	}
	if ($_SESSION['view_session']['pk_unit_num']) {
		$sql .= " AND pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'";
	}
	if ($_SESSION['view_session']['pk_block_num']) {
		$sql .= " AND pk_block_num='".$_SESSION['view_session']['pk_block_num']."'";
	}
	if ($_SESSION['view_session']['pk_list_num']) {
		$sql .= " AND pk_list_num='".$_SESSION['view_session']['pk_list_num']."'";
	}
	$sql .= " AND state='1'".
			" ORDER BY regist_time DESC LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$package_mate_upd_log_num = $list['package_mate_upd_log_num'];
	}

	if ($package_mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['pk_course_num'] = $_SESSION['view_session']['pk_course_num'];
		$INSERT_DATA['pk_stage_num'] = $_SESSION['view_session']['pk_stage_num'];
		$INSERT_DATA['pk_lesson_num'] = $_SESSION['view_session']['pk_lesson_num'];
		$INSERT_DATA['pk_unit_num'] = $_SESSION['view_session']['pk_unit_num'];
		$INSERT_DATA['pk_block_num'] = $_SESSION['view_session']['pk_block_num'];
		$INSERT_DATA['pk_list_num'] = $_SESSION['view_session']['pk_list_num'];
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE package_mate_upd_log_num='".$package_mate_upd_log_num."'";

		$ERROR = $cdb->update(T_PACKAGE_MATE_UPD_LOG, $INSERT_DATA, $where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = 'package_data';
		$INSERT_DATA['pk_course_num'] = $_SESSION['view_session']['pk_course_num'];
		$INSERT_DATA['pk_stage_num'] = $_SESSION['view_session']['pk_stage_num'];
		$INSERT_DATA['pk_lesson_num'] = $_SESSION['view_session']['pk_lesson_num'];
		$INSERT_DATA['pk_unit_num'] = $_SESSION['view_session']['pk_unit_num'];
		$INSERT_DATA['pk_block_num'] = $_SESSION['view_session']['pk_block_num'];
		$INSERT_DATA['pk_list_num'] = $_SESSION['view_session']['pk_list_num'];
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert(T_PACKAGE_MATE_UPD_LOG, $INSERT_DATA);
	}

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $L_NAME
 * @return string HTML
 */
function update_end_html($L_NAME) {

	$html  = "<br>\n";
	$html .= "速習コース情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 選択リスト
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	//	検証中データー取得
	$PMUL = array();
	$sql  = "SELECT pk_course_num, pk_stage_num, pk_lesson_num, pk_unit_num, pk_block_num, pk_list_num".
			" FROM ".T_PACKAGE_MATE_UPD_LOG.
			" WHERE update_mode='package_data'".
			" AND state='1'";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$pk_course_num = $list['pk_course_num'];
			$pk_stage_num = $list['pk_stage_num'];
			$pk_lesson_num = $list['pk_lesson_num'];
			$pk_unit_num = $list['pk_unit_num'];
			$pk_block_num = $list['pk_block_num'];
			$pk_list_num = $list['pk_list_num'];

			if ($pk_course_num < 1) {
				continue;
			} elseif ($pk_stage_num < 1) {
				$PMUL[$pk_course_num] = 1;
			} elseif ($pk_lesson_num < 1) {
				$PMUL[$pk_course_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num] = 1;
			} elseif ($pk_unit_num < 1) {
				$PMUL[$pk_course_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num] = 1;
			} elseif ($pk_block_num < 1) {
				$PMUL[$pk_course_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num][$pk_unit_num] = 1;
			} elseif ($pk_list_num < 1) {
				$PMUL[$pk_course_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num][$pk_unit_num] = "";
				$PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num][$pk_unit_num][$pk_block_num] = 1;
			}

		}
	}

	//	コース
	$pk_course_num_count = 0;
	$pk_couse_num_html  = "";
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE mk_flg!='1'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$pk_course_num_count = $cdb->num_rows($result);
	}
	if ($pk_course_num_count < 1) {
		$html .= "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return $html;
	}
	$pk_couse_num_html .= "<option value=\"0\">選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		$selected = "";
		if ($_SESSION['view_session']['pk_course_num'] == $list['pk_course_num']) {
			$selected = "selected";
		}
		$pk_couse_num_html .= "<option value=\"".$list['pk_course_num']."\" ".$selected.">".$list['pk_course_name']."</option>\n";
	}

	$last_select_flg = 0;
	$pk_course_num = $_SESSION['view_session']['pk_course_num'];
	if ($PMUL[$pk_course_num] == 1) {
		$last_select_flg = 1;
	}

	if ($_SESSION['view_session']['pk_course_num'] > 0) {
		if ($last_select_flg == 1) {
			$pk_stage_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$pk_stage_num_count = 0;
			$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
					" WHERE pk_course_num='".$_SESSION['view_session']['pk_course_num']."'".
					" AND mk_flg!='1'".
					" ORDER BY list_num;";
			if ($result = $cdb->query($sql)) {
				$pk_stage_num_count = $cdb->num_rows($result);
			}
			if ($pk_stage_num_count < 1) {
				$pk_stage_num_html .= "<option value=\"\">--------</option>\n";
			} else {
				if ($_SESSION['view_session']['pk_course_num'] < 1) { $selected = "selected"; } else { $selected = ""; }
				$pk_stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$L_STAGE[$list['pk_stage_num']] = $list['pk_stage_name'];
					if ($_SESSION['view_session']['pk_stage_num'] == $list[pk_stage_num]) { $selected = "selected"; } else { $selected = ""; }
					$pk_stage_num_html .= "<option value=\"".$list['pk_stage_num']."\" ".$selected.">".$list['pk_stage_name']."(".$list['pk_stage_num'].")</option>\n";
				}
			}
		}
	} else {
		$pk_stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	$pk_stage_num = $_SESSION['view_session']['pk_stage_num'];
	if ($PMUL[$pk_course_num][$pk_stage_num] == 1) {
		$last_select_flg = 1;
	}

	if ($_SESSION['view_session']['pk_stage_num'] > 0) {
		if ($last_select_flg == 1) {
			$pk_lesson_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$pk_lesson_num_count = 0;
			$sql  = "SELECT * FROM ".T_PACKAGE_LESSON.
					" WHERE pk_stage_num='".$_SESSION['view_session']['pk_stage_num']."'".
					" AND mk_flg!='1'".
					" ORDER BY list_num;";
			if ($result = $cdb->query($sql)) {
				$pk_lesson_num_count = $cdb->num_rows($result);
			}
			if ($pk_lesson_num_count < 1) {
				$pk_lesson_num_html .= "<option value=\"\">--------</option>\n";
			} else {
				if ($_SESSION['view_session']['pk_stage_num'] < 1) { $selected = "selected"; } else { $selected = ""; }
				$pk_lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$L_LESSON[$list['pk_lesson_num']] = $list['pk_lesson_name'];
					if ($_SESSION['view_session']['pk_lesson_num'] == $list['pk_lesson_num']) { $selected = "selected"; } else { $selected = ""; }
					$pk_lesson_num_html .= "<option value=\"".$list['pk_lesson_num']."\" ".$selected.">".$list['pk_lesson_name']."(".$list['pk_lesson_num'].")</option>\n";
				}
			}
		}
	} else {
		$pk_lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	$pk_lesson_num = $_SESSION['view_session']['pk_lesson_num'];
	if ($PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num] == 1) {
		$last_select_flg = 1;
	}

	if ($_SESSION['view_session']['pk_lesson_num'] > 0) {
		if ($last_select_flg == 1) {
			$pk_unit_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$pk_unit_num_count = 0;
			$sql  = "SELECT * FROM ".T_PACKAGE_UNIT.
					" WHERE pk_lesson_num='".$_SESSION['view_session']['pk_lesson_num']."'".
					" AND mk_flg!='1'".
					" ORDER BY list_num;";
			if ($result = $cdb->query($sql)) {
				$pk_unit_num_count = $cdb->num_rows($result);
			}
			if ($pk_unit_num_count < 1) {
				$pk_unit_num_html .= "<option value=\"\">--------</option>\n";
			} else {
				if ($_SESSION['view_session']['pk_lesson_num'] < 1) { $selected = "selected"; } else { $selected = ""; }
				$pk_unit_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$L_UNIT[$list['pk_unit_num']] = $list['pk_unit_name'];
					if ($_SESSION['view_session']['pk_unit_num'] == $list['pk_unit_num']) { $selected = "selected"; } else { $selected = ""; }
					$pk_unit_num_html .= "<option value=\"".$list['pk_unit_num']."\" ".$selected.">".$list['pk_unit_name']."(".$list['pk_unit_num'].")</option>\n";
				}
			}
		}
	} else {
		$pk_unit_num_html .= "<option value=\"\">--------</option>\n";
	}

	$pk_unit_num = $_SESSION['view_session']['pk_unit_num'];
	if ($PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num][$pk_unit_num] == 1) {
		$last_select_flg = 1;
	}

	if ($_SESSION['view_session']['pk_unit_num'] > 0) {
		if ($last_select_flg == 1) {
			$pk_block_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$pk_block_num_count = 0;
			$sql  = "SELECT * FROM ".T_PACKAGE_BLOCK.
					" WHERE pk_unit_num='".$_SESSION['view_session']['pk_unit_num']."'".
					" AND mk_flg!='1'".
					" ORDER BY list_num;";
			if ($result = $cdb->query($sql)) {
				$pk_block_num_count = $cdb->num_rows($result);
			}
			if ($pk_block_num_count < 1) {
				$pk_block_num_html .= "<option value=\"\">--------</option>\n";
			} else {
				if ($_SESSION['view_session']['pk_unit_num'] < 1) { $selected = "selected"; } else { $selected = ""; }
				$pk_block_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$L_BLOCK[$list['pk_block_num']] = $list['pk_block_name'];
					if ($_SESSION['view_session']['pk_block_num'] == $list['pk_block_num']) { $selected = "selected"; } else { $selected = ""; }
					$pk_block_num_html .= "<option value=\"".$list['pk_block_num']."\" ".$selected.">".$list['pk_block_name']."(".$list['pk_block_num'].")</option>\n";
				}
			}
		}
	} else {
		$pk_block_num_html .= "<option value=\"\">--------</option>\n";
	}

	$pk_block_num = $_SESSION['view_session']['pk_block_num'];
	if ($PMUL[$pk_course_num][$pk_stage_num][$pk_lesson_num][$pk_unit_num][$pk_block_num] == 1) {
		$last_select_flg = 1;
	}

	if ($_SESSION['view_session']['pk_block_num'] > 0) {
		if ($last_select_flg == 1) {
			$pk_list_num_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$pk_listk_num_count = 0;
			$sql  = "SELECT * FROM ".T_PACKAGE_LIST.
					" WHERE pk_block_num='".$_SESSION['view_session']['pk_block_num']."'".
					" AND mk_flg!='1'".
					" ORDER BY list_num;";
			if ($result = $cdb->query($sql)) {
				$pk_listk_num_count = $cdb->num_rows($result);
			}
			if ($pk_listk_num_count < 1) {
				$pk_list_num_html .= "<option value=\"\">--------</option>\n";
			} else {
				if ($_SESSION['view_session']['pk_block_num'] < 1) { $selected = "selected"; } else { $selected = ""; }
				$pk_list_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$L_PACKAGE_LIST[$list['pk_list_num']] = $list['pk_list_name'];
					if ($_SESSION['view_session']['pk_list_num'] == $list['pk_list_num']) { $selected = "selected"; } else { $selected = ""; }
					$pk_list_num_html .= "<option value=\"".$list['pk_list_num']."\" ".$selected.">".$list['pk_list_name']."(".$list['pk_list_num'].")</option>\n";
				}
			}
		}
	} else {
		$pk_list_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "<td>レッスン</td>\n";
	$html .= "<td>ユニット</td>\n";
	$html .= "<td>ブロック</td>\n";
	$html .= "<td>リスト</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"pk_course_num\" onchange=\"submit_pk_course();\">\n";
	$html .= $pk_couse_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"pk_stage_num\" onchange=\"submit_pk_stage();\">\n";
	$html .= $pk_stage_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"pk_lesson_num\" onchange=\"submit_pk_lesson();\">\n";
	$html .= $pk_lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"pk_unit_num\" onchange=\"submit_pk_unit();\">\n";
	$html .= $pk_unit_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"pk_block_num\" onchange=\"submit_pk_block();\">\n";
	$html .= $pk_block_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"pk_list_num\" onchange=\"submit();\">\n";
	$html .= $pk_list_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";

	if ($_SESSION['view_session']['pk_course_num'] < 1) {
		$html .= "<br>\n";
		$html .= "アップする情報を選択してください。<br>\n";
		$html .= "選択した情報以下がアップされます。。<br>\n";
	}

	return $html;
}


/**
 * 各カテゴリー選択セッションセット
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	$pk_course_num	= $_SESSION['view_session']['pk_course_num'];
	$pk_stage_num	= $_SESSION['view_session']['pk_stage_num'];
	$pk_lesson_num	= $_SESSION['view_session']['pk_lesson_num'];
	$pk_unit_num	= $_SESSION['view_session']['pk_unit_num'];
	$pk_block_num	= $_SESSION['view_session']['pk_block_num'];
	$pk_list_num	= $_SESSION['view_session']['pk_list_num'];

	unset($_SESSION['view_session']);

	if ($_POST['pk_course_num'] > 0) {
		$_SESSION['view_session']['pk_course_num'] = $_POST['pk_course_num'];
	} else {
		return;
	}

	if ($_POST['pk_course_num'] == $pk_course_num && $_POST['pk_stage_num'] > 0) {
		$_SESSION['view_session']['pk_stage_num'] = $_POST['pk_stage_num'];
	} else {
		return;
	}

	if ($_POST['pk_stage_num'] == $pk_stage_num && $_POST['pk_lesson_num'] > 0) {
		$_SESSION['view_session']['pk_lesson_num'] = $_POST['pk_lesson_num'];
	} else {
		return;
	}

	if ($_POST['pk_lesson_num'] == $pk_lesson_num && $_POST['pk_unit_num'] > 0) {
		$_SESSION['view_session']['pk_unit_num'] = $_POST['pk_unit_num'];
	} else {
		return;
	}

	if ($_POST['pk_unit_num'] == $pk_unit_num && $_POST['pk_block_num'] > 0) {
		$_SESSION['view_session']['pk_block_num'] = $_POST['pk_block_num'];
	} else {
		return;
	}

	if ($_POST['pk_block_num'] == $pk_block_num && $_POST['pk_list_num'] > 0) {
		$_SESSION['view_session']['pk_list_num'] = $_POST['pk_list_num'];			// update oda 2017/10/23 Mac対応 既存不具合 pk_list_num -> view_session
	} else {
		return;
	}
}
?>