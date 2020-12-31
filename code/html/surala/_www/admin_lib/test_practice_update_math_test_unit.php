<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		数学検定採点単元 アップデート
 *
 * @author Azet
 */
// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function action() {

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

	// global $L_MATH_TEST_CLASS;// del 2020/09/15 thanh テスト標準化開発

	// add start 2020/09/15 thanh テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 thanh テスト標準化開発

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	$L_MATH_TEST_CLASS[0] = "全て";

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
			$class_id = $VALUES['class_id'];

			if ($class_id === "0") {
				$PMUL[$class_id] = 1;
				$L_MATH_TEST_CLASS['0'] = "全て（アップデート中の為、他を選択出来ません）";
			} elseif ($class_id != "" ) {
				$PMUL[$class_id] = 1;
			}
		}
	}


	// 級
	$class_html = "";
	if ($PMUL['0'] == 1) {
		$class_html .= "<option value=\"\">全てアップデート中の為選択出来ません</option>\n";
	} else {
		foreach($L_MATH_TEST_CLASS as $key => $val) {
			$selected = "";
			if( $PMUL[$key] == 1 ){
				$class_html .= "<option value=\"\">".$val."はアップデート中の為選択出来ません</option>\n";
				continue;
			}
			if ($_SESSION['view_session']['class_id'] == strval($key) && $_SESSION['view_session']['class_id'] != "" ) {
				if ( $_SESSION['view_session']['class_id'] !== "select"  ) {
					$selected = "selected";
				}
			}
			$class_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
		}
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>級</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"class_id\" onchange=\"submit();\">\n".$class_html."</select>";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if ($_SESSION['view_session']['class_id'] != "" && $_SESSION['view_session']['class_id'] != "select" ) {
		$html .= default_html($ERROR);
	} else {
		$html .= "単元を設定する級を選択してください。<br>\n";
		$html .= "<br />\n";
	}

	return $html;

}


/**
 * 級の選択情報をSESSIONに保持
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	unset($_SESSION['view_session']);

	if ($_POST['class_id'] != "" ) {
		$_SESSION['view_session']['class_id'] = $_POST['class_id'];
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
	$class_id = $_SESSION['view_session']['class_id'];
	if ($class_id != "0" && $class_id != "") {
		$where .= " WHERE mtbul.class_id='".$class_id."'";
	}
	$sql  = "SELECT MAX(mbu.upd_date) AS upd_date FROM ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul".
			 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mtbul.book_unit_id = mbu.book_unit_id ".
			 $where.
			 " AND mbu.display='1'".
			 " AND mbu.mk_flg='0';";
	$sql_cnt  = "SELECT DISTINCT mbu.* FROM ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul".
				  " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mtbul.book_unit_id = mbu.book_unit_id ".
				  $where.
				  " AND mbu.display='1'".
	 			  " AND mbu.mk_flg='0';";

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
		$submit_msg = "数学検定採点単元情報を検証へアップしますがよろしいですか？";

		$html .= "数学検定採点単元情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
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
		$html .= "<td>採点単元（結果グラフ単元名）マスタ：".T_MS_BOOK_UNIT."</td>\n";
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
		$html .= "数学検定採点単元情報が設定されておりません。<br>\n";
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

	//	検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);
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
	$and = "";
	$class_id = $_SESSION['view_session']['class_id'];
	if( $class_id === "" ){
		$ERROR[] = "class_idが存在しません";
		$connect_db->close();
		return ;
	}
	if ($class_id != "0" && $class_id != "") {
		$where .= " WHERE 1";
		$where .= " AND mtbul.class_id='".$class_id."'";
	} else {
		$where .= " WHERE 1";
	}
	$and .= " AND mbu.display='1'";
	$and .= " AND mbu.mk_flg='0'";

	/* math_test_book_unit_list */
	$sql  = "SELECT mtbul.* FROM ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul".
			 " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mtbul.book_unit_id = mbu.book_unit_id ".
			 $where.
			 $and.";";
	if ($result = $cdb->query($sql)) {
 		make_insert_query($result, T_MATH_TEST_BOOK_UNIT_LIST, $INSERT_NAME, $INSERT_VALUE);
 	}
	/* ms_book_unit */
	$sql  = "SELECT mbu.* FROM ".T_MS_BOOK_UNIT." mbu".
			 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
			 $where.
			 $and.";";
	if ($result = $cdb->query($sql)) {
 		make_insert_query($result, T_MS_BOOK_UNIT, $INSERT_NAME, $INSERT_VALUE);
 	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE mbu FROM ".T_MS_BOOK_UNIT." mbu".
			 " INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
			 $where.
			 $and.";";
	$DELETE_SQL[] = $sql;
	$sql  = "DELETE mtbul FROM ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul".
			 $where;
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
	$sql = "OPTIMIZE TABLE ".T_MATH_TEST_BOOK_UNIT_LIST.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	$sql = "OPTIMIZE TABLE ".T_MS_BOOK_UNIT.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();

	//	検証バッチから検証webへ
	$send_data = " '".$_SESSION['view_session']['class_id']."'";
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi
//echo $command;

	//	ログ保存 --
	$test_mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1'";
			if ( $_SESSION['view_session']['class_id'] != "" ) {
				$sql .= " AND course_num='".$_SESSION['view_session']['class_id']."'";
			} else {
				$sql .= " AND course_num IS NULL";
			}
	$sql .=	" ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
	}

	/*
	// プラクティスアップデート済みのデータが存在しない場合
	// 存在しないレコードのstateを０にする？？？詳細不明
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
	*/

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
		$INSERT_DATA['course_num'] = $_SESSION['view_session']['class_id'];
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

	$html  = "数学検定採点単元情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
