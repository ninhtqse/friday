<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		学力診断テスト単元 アップデート
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function action() {
	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
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
 * コース、学年、マスタ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_unit_view($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST;
	global $L_WRITE_TYPE;	//	add ookawara 2012/07/24

	// upd start hirose 2020/09/11 テスト標準化開発
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	$test_type4 = new TestStdCfgType4($cdb);
	$l_write_type = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/11 テスト標準化開発

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
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
			$publishing_id = $VALUES['publishing_id'];
			$gknn = $VALUES['gknn'];
			$book_id = $VALUES['book_id'];

			if ($course_num < 1) {
				continue;
			} elseif ($gknn == "0" || $gknn == "") {
				$PMUL[$course_num] = 1;
			} elseif ($book_id == "0" || $book_id == "") {
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

	//マスタ
	$book_html = "";
	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
		if ($last_select_flg == 1) {
			$book_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$book_count = 0;
			$sql  = "SELECT * FROM ".T_MS_BOOK." ms_book".
					" WHERE publishing_id='0'".
					" AND course_num='".$_SESSION['view_session']['course_num']."'".
					" AND gknn='".$_SESSION['view_session']['gknn']."'".
					" AND mk_flg='0' ".
					"ORDER BY disp_sort";
			if ($result = $cdb->query($sql)) {
				$book_count = $cdb->num_rows($result);
			}
			if (!$book_count) {
				unset($_SESSION['view_session']['book_id']);
				$book_html = "<option value=\"0\">設定されていません。</option>\n";
			} else {
				$book_html = "<option value=\"0\">選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$selected = "";
					if ($_SESSION['view_session']['book_id'] == $list['book_id']) {
						$selected = "selected";
					}
					$book_html .= "<option value=\"".$list['book_id']."\" ".$selected.">".$list['book_name']."</option>\n";
				}
			}
		}
	} else {
		$book_html .= "<option value=\"0\">--------</option>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>学年</td>\n";
	$html .= "<td>マスタ</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit();\">\n".$couse_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"gknn\" onchange=\"submit();\">\n".$gknn_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"book_id\" onchange=\"submit();\">\n".$book_html."</select>";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if (!$_SESSION['view_session']['course_num']) {
		$html .= "単元を設定する各項目を選択してください。<br>\n";
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}

	return $html;
}


/**
 * コース、学年、マスタ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	$course_num = $_SESSION['view_session']['course_num'];
	$gknn = $_SESSION['view_session']['gknn'];
	$book_id = $_SESSION['view_session']['book_id'];
	unset($_SESSION['view_session']);


	if ($_POST['course_num'] != "") {
		$_SESSION['view_session']['course_num'] = $_POST['course_num'];
	} else {
		return;
	}

	if ($_POST['course_num'] == $course_num && $_POST['gknn'] != "") {
		$_SESSION['view_session']['gknn'] = $_POST['gknn'];
	} else {
		return;
	}

	if ($_POST['gknn'] == $gknn && $_POST['book_id'] != "") {
		$_SESSION['view_session']['book_id'] = $_POST['book_id'];
	} else {
		return;
	}

}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
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
	$html .= "</form>\n";
	$html .= "<br />\n";

	unset($BASE_DATA);
	unset($MAIN_DATA);
	//サーバー情報取得
	if (!$_SESSION['select_db']) { return $html; }

	//	閲覧DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	情報取得クエリー
	$where = "";
	$serch_course = sprintf("%02d", $_SESSION['view_session']['course_num']);
	$serch_gknn = $_SESSION['view_session']['gknn'];
	$serch_book_id = $_SESSION['view_session']['book_id'];
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE ms_book_unit.book_id='".$_SESSION['view_session']['book_id']."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE ms_book_unit.book_id like '000".$serch_course.$serch_gknn."%'";
//		$where .= " WHERE substr(ms_book_unit.book_id, 1, 7)='000".$serch_course.$serch_gknn."'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE ms_book_unit.book_id like '000".$serch_course."%'";
//		$where .= " WHERE substr(ms_book_unit.book_id, 1, 5)='000".$serch_course."'";
	}
	$sql  = "SELECT MAX(ms_book_unit.upd_date) AS upd_date FROM ".T_MS_BOOK_UNIT." ms_book_unit".
			$where.";";
	$sql_cnt  = "SELECT DISTINCT ms_book_unit.* FROM ".T_MS_BOOK_UNIT." ms_book_unit".
				$where.";";

	//	ローカルサーバー
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
		$local_html = $local_time." (".$cnt.")";
	} else {
		$local_html = "データーがありません。";
	}

	// -- 閲覧DB
	$remote_html = "";
	$remote_time = "";
	$cnt = 0;
	if ($result = $connect_db->exec_query($sql)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_cnt)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote_time) {
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}

	if ($local_time || $remote_time) {
		$submit_msg = "学力診断テスト単元情報を検証へアップしますがよろしいですか？";

		$html .= "学力診断テスト単元情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "学力診断テスト単元情報が設定されておりません。<br>\n";
	}

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array エラーの場合
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_CONTENTS_DB;

	$db_data = $L_CONTENTS_DB['92'];
	//$db_data['DBNAME'] = "SRLBS99"; // add 2016/04/08 yoshizawa デバッグ用テーブル切り替え

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

	//	更新情報クエリー
	$where = "";
	$serch_course = sprintf("%02d", $_SESSION['view_session']['course_num']);
	$serch_gknn = $_SESSION['view_session']['gknn'];
	$serch_book_id = $_SESSION['view_session']['book_id'];
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE book_id='".$_SESSION['view_session']['book_id']."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE book_id like '000".$serch_course.$serch_gknn."%'";
//		$where .= " WHERE substr(book_id, 1, 7)='000".$serch_course.$serch_gknn."'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE book_id like '000".$serch_course."%'";
//		$where .= " WHERE substr(book_id, 1, 5)='000".$serch_course."'";
	}
	$sql  = "SELECT * FROM ".T_MS_BOOK_UNIT.
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_BOOK_UNIT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_MS_BOOK_UNIT.
			$where.";";
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

	if ($DELETE_SQL) {
		$err_flg = 0;
		foreach ($DELETE_SQL AS $sql) {
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

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_BOOK_UNIT.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();

	//	検証バッチから検証webへ
	$send_data = " '".$_SESSION['view_session']['course_num']."' '".$_SESSION['view_session']['gknn']."' '".$_SESSION['view_session']['book_id']."'";
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi

	//	ログ保存 --
	$test_mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			" AND course_num='".$_SESSION['view_session']['course_num']."'";
	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
		$sql .= " AND stage_num='".$_SESSION['view_session']['gknn']."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($_SESSION['view_session']['book_id'] != "0" && $_SESSION['view_session']['book_id'] != "") {
		$sql .= " AND lesson_num='".$_SESSION['view_session']['book_id']."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	$sql .=	" ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
	}

	if ($test_mate_upd_log_num < 1) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 0;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE update_mode='".MODE."'".
				 " AND course_num='".$_SESSION['view_session']['course_num']."'".
				 " AND state!='0'";
		if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
			$where .= " AND stage_num='".$_SESSION['view_session']['gknn']."'";
		}
		if ($_SESSION['view_session']['book_id'] != "0" && $_SESSION['view_session']['book_id'] != "") {
			$where .= " AND lesson_num='".$_SESSION['view_session']['book_id']."'";
		}
		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
	}

	if ($test_mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";
		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = $_SESSION['view_session']['course_num'];
		if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
			$INSERT_DATA['stage_num'] = $_SESSION['view_session']['gknn'];
		}
		if ($_SESSION['view_session']['book_id'] != "0" && $_SESSION['view_session']['book_id'] != "") {
			$INSERT_DATA['lesson_num'] = $_SESSION['view_session']['book_id'];
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$ERROR = $cdb->insert(T_TEST_MATE_UPD_LOG, $INSERT_DATA);
	}

}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function update_end_html() {

	$html  = "学力診断テスト単元情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
