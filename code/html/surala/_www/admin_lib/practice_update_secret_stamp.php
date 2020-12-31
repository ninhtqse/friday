<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * シークレットイベント  プラクティスアップデート管理
 * 	プラクティスアップデートプログラム
 * 		スタンプ アップデート
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function sub_start($L_NAME) {
//function action() {
	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
		select_web();
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html();
	} else {
		$html = default_html($ERROR);
	}

	return $html;
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

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

	//	情報取得クエリー
	$sql  = "SELECT MAX(secret_calendar_image.upd_date) AS upd_date FROM ".T_SECRET_CALENDAR_IMAGE." secret_calendar_image;";
	$sql_cnt  = "SELECT DISTINCT secret_calendar_image.*  FROM ".T_SECRET_CALENDAR_IMAGE." secret_calendar_image;";

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
	if ($result = $connect_db->query($sql)) {
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

	//	画像ファイル
	$LOCAL_FILES = array();
	$img_last_local_time = 0;
	$img_last_local_cnt = 0;
	$img_last_remote_time = 0;
	$img_last_remote_cnt = 0;

	//	ファイル更新情報取得
	//	開示パズルデータ取得
	$PUZZLE_LIST = array();

	//	ローカルサーバー
	$PUZZLE_LIST[] = "calendar";
	$dir = MATERIAL_SECRET_DIR;		// "material/secret_event/"
	local_glob_secretevent($PUZZLE_LIST, $LOCAL_FILES, $img_last_local_time, $dir);
	$img_last_local_cnt = count($LOCAL_FILES['f']);
//print_r($PUZZLE_LIST); echo "<br>";
//print_r($LOCAL_FILES); echo "<br>";
//echo "img_last_local_cnt=".$img_last_local_cnt."<br>";
//echo "<hr>";

	//	閲覧WEBサーバー
	$dir = REMOTE_MATERIAL_SECRET_EVENT_DIR;	// "material/secret_event/"		右の内容を対象とする $PUZZLE_LIST[] = "calendar";
	remote_dir_time_secretevent($PUZZLE_LIST, $img_last_remote_time, $img_last_remote_cnt, $_SESSION['select_web'], $dir);

	$img_local_time = "データーがありません。";
	if ($img_last_local_time > 0) {
		$img_local_time = date("Y-m-d H:i:s", $img_last_local_time);
	}
	$img_remote_time = "データーがありません。";
	if ($img_last_remote_time > 0) {
		$img_remote_time = date("Y-m-d H:i:s", $img_last_remote_time);
	}
	$local_img_html  = $img_local_time;
	if ($img_last_local_cnt > 0) {
		$local_img_html .= " (".$img_last_local_cnt.")";
	}
	$remote_img_html = $img_remote_time;
	if ($img_last_remote_cnt > 0) {
		$remote_img_html .= " (".$img_last_remote_cnt.")";
	}


	if ($local_time || $remote_time) {
		$submit_msg = "スタンプ情報を検証へアップしますがよろしいですか？";

		$html .= "スタンプ情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>".T_SECRET_CALENDAR_IMAGE."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
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

		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";
		$html .= "</form>\n";
	} else {
		$html .= "スタンプ情報が設定されておりません。<br>\n";
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
 * @param array &$ERROR
 */
function update(&$ERROR) {

	global $L_CONTENTS_DB;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);		//'srlbtw21'(検証バッチ)
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$sql  = "SELECT * FROM ".T_SECRET_CALENDAR_IMAGE.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_SECRET_CALENDAR_IMAGE, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_SECRET_CALENDAR_IMAGE.";";
	$DELETE_SQL[] = $sql;

//echo "*update*".$tbw_db.", ".$tbw_dbname."<br>";
//print_r($INSERT_NAME); echo "<br><br>";
//print_r($INSERT_VALUE); echo "<br><br>";
//print_r($DELETE_SQL); echo "<br><br>";


	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return ;
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return ;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_SECRET_CALENDAR_IMAGE.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {

		echo "<br>\n";
		echo "ファイルアップロード開始<br>\n";
		flush();

		$PUZZLE_LIST = array();
		$PUZZLE_LIST[] = "calendar";

		//	画像ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_SECRET_DIR;		// "material/secret_event/"
		local_glob_secretevent($PUZZLE_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成  …格納ディレクトリが単一固定なので、フォルダ作成は不要
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;	// "/data/apprelease/Release/Contents/www/material/secret_event/"
		remote_set_dir_secretevent($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/", MATERIAL_SECRET_DIR);
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;
		remote_set_file_secretevent($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);
	}

	echo "<br>\n";
	echo "検証サーバーへデーター反映中<br>\n";
	flush();


	//	検証サーバー反映処理記録
	$update_num = 0;

	//	検証バッチから検証webへ
	$fileup = 0;
	$_SESSION['view_session']['fileup'] = intval($_POST['fileup']);
	if ($_SESSION['view_session']['fileup'] > 0)	{ $fileup = $_SESSION['view_session']['fileup']; }
	$send_data = " '".$fileup."'".
				 " '".$update_num."'";
	//$command = "ssh suralacore01@srlbtw21 ./HANTEITESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data;
	// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '2' '".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '2' '".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	exec($command);
//echo "command: ".$command."<br/>\n";	//@@@@@

	echo "<br>\n";
	echo "検証サーバーへデーター反映終了<br>\n";
	flush();


	//	ログ保存 --
	$mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			//" AND block_num='".$_SESSION['view_session']['image_category']."'".
			" ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$mate_upd_log_num = $list['mate_upd_log_num'];
	}

	if ($mate_upd_log_num < 1) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 0;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE update_mode='".MODE."'".
				 " AND state!='0'";
				 //" AND block_num='".$_SESSION['view_session']['image_category']."'";

		$ERROR = $cdb->update("mate_upd_log", $INSERT_DATA, $where);
	}

	if ($mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE mate_upd_log_num='".$mate_upd_log_num."'";

		$ERROR = $cdb->update("mate_upd_log", $INSERT_DATA, $where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = 0;
		$INSERT_DATA['stage_num'] = 0;
		$INSERT_DATA['lesson_num'] = 0;
		$INSERT_DATA['unit_num'] = 0;
		$INSERT_DATA['block_num'] = 0;
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert("mate_upd_log", $INSERT_DATA);
	}


	//	検証バッチDB切断
	$connect_db->close();

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function update_end_html() {

	$html  = "スタンプ情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>