<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	ワンポイント情報コース毎アップデート
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

	if (ACTION == "update") {
		set_time_limit(0);
		echo "更新開始\n";
		echo str_pad(" ",4096)."<br>\n";
		update($ERROR);
	} elseif (ACTION == "db_session") {
		$ERROR = select_database();
		$ERROR = select_web();
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html($L_NAME);
	} else {
		$html = default_html($L_NAME,$ERROR);
	}

	return $html;
}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @param array $ERROR
 * @return string HTML
 */
function default_html($L_NAME,$ERROR) {

	// DB接続オブジェクト
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

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	//	閲覧DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	情報取得クエリー
	$sql  = "SELECT MAX(upd_date) AS upd_date, count(*) AS cnt FROM ".T_ONE_POINT.
			" WHERE course_num='".$_POST['course_num']."'".
			";";


	//	ローカルサーバー
	$dsp_flg = 0;
	$local_html = "";
	$local_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$local_time = $list['upd_date'];
		$cnt = $list['cnt'];
	}
	if ($local_time) {
		$dsp_flg = 1;
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
		$cnt = $list['cnt'];
	}
	if ($remote_time) {
		$dsp_flg = 1;
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}


	//	ファイル更新情報取得
	//	画像ファイル
	$LOCAL_FILES = array();
	$img_last_local_time = 0;
	$img_last_local_cnt = 0;
	$img_last_remote_time = 0;
	$img_last_remote_cnt = 0;

	$set_dir = REMOTE_MATERIAL_POINT_IMG_DIR.$_POST['course_num']."/";
	//	ローカル
	get_file_data('', $set_dir, $img_last_local_cnt, $img_last_local_time);

	//	アップ先
	get_file_data($_SESSION['select_web'], $set_dir, $img_last_remote_cnt, $img_last_remote_time);

	$img_local_time = "データーがありません。";
	if ($img_last_local_time != "") {
		$img_local_time = $img_last_local_time;
	}
	$img_remote_time = "データーがありません。";
	if ($img_last_remote_time != "") {
		$img_remote_time = $img_last_remote_time;
	}
	$local_img_html = $img_local_time." (".$img_last_local_cnt.")";
	$remote_img_html = $img_remote_time." (".$img_last_remote_cnt.")";

	//	音声ファイル
	$LOCAL_FILES = array();
	$voice_last_local_time = 0;
	$voice_last_local_cnt = 0;
	$voice_last_remote_time = 0;
	$voice_last_remote_cnt = 0;

	$set_dir = REMOTE_MATERIAL_POINT_VOICE_DIR.$_POST['course_num']."/";
	//	ローカル
	get_file_data('', $set_dir, $voice_last_local_cnt, $voice_last_local_time);

	//	アップ先
	get_file_data($_SESSION['select_web'], $set_dir, $voice_last_remote_cnt, $voice_last_remote_time);

	$voice_local_time = "データーがありません。";
	if ($voice_last_local_time != "") {
		$voice_local_time = $voice_last_local_time;
	}
	$voice_remote_time = "データーがありません。";
	if ($voice_last_remote_time != "") {
		$voice_remote_time = $voice_last_remote_time;
	}
	$local_voice_html = $voice_local_time." (".$voice_last_local_cnt.")";
	$remote_voice_html = $voice_remote_time." (".$voice_last_remote_cnt.")";

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}

	if ($dsp_flg == 1) {
		$submit_msg = "ワンポイント情報を検証へアップしますがよろしいですか？";

		$html .= "ワンポイント情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";		// update oda 2013/10/16 文言変更
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>開発サーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題：".T_ONE_POINT."</td>\n";
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
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";	//	add ookawara 2012/08/08
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";		// update oda 2013/10/16 文言変更
		$html .= "</form>\n";
	} else {
		$html .= "ワンポイント情報が設定されておりません。<br>\n";
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
 * @return array エラーの場合
 */
function update() {

	global $L_CONTENTS_DB;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	時間がかかる為、表示ヘッダ表示
	flush();	//	add ookawara 2012/08/20

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
	$INSERT_NAME_DEL = array();
	$INSERT_VALUE_DEL = array();
	$DELETE_SQL = array();

	echo "<br>\n";
	echo "db情報取得中<br>\n";
	flush();

	//	更新情報クエリー
	$sql  = "SELECT * FROM ".T_ONE_POINT.
			" WHERE course_num='".$_POST['course_num']."';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_ONE_POINT, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_ONE_POINT.
			" WHERE course_num='".$_POST['course_num']."';";
	$DELETE_SQL['one_point'] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
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


	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				if ($last_table_name != $table_name) {
					//echo "<br>\n";
					//echo $table_name." 情報更新中<br>\n";
					//flush();
				}

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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return $ERROR;
	}

	echo "<br>\n";
	echo "db最適化中<br>\n";
	flush();

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_ONE_POINT.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}


	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {
		//	画像ファイル
		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		$local_dir = BASE_DIR.REMOTE_MATERIAL_POINT_IMG_DIR.$_POST['course_num']."/";
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR;
		//$command = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
		$command = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
//echo "command<>".$command."<br>\n";
		exec("$command",$LIST);


		//	音声ファイル
		echo "<br>\n";
		echo "音声ファイルアップロード開始<br>\n";
		flush();

		$local_dir = BASE_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR.$_POST['course_num']."/";
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR;
		//$command = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
		$command = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
//echo "command<>".$command."<br>\n";
		exec("$command",$LIST);
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
	$fileup = $_SESSION['view_session']['fileup'];
	if (!$fileup) { $fileup = "0"; }
	$send_data = " '".$_POST['course_num']."' '".$fileup."' '".$update_num."'";
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
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


	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND course_num='".$_POST['course_num']."'".
			" AND state='1'".
			" ORDER BY regist_time DESC LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$mate_upd_log_num = $list['mate_upd_log_num'];
	}

	if ($mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE mate_upd_log_num='".$mate_upd_log_num."'";

		$ERROR = $cdb->update('mate_upd_log',$INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = $_POST['course_num'];
		$INSERT_DATA['stage_num'] = $_POST['stage_num'];
		$INSERT_DATA['lesson_num'] = $_POST['lesson_num'];
		$INSERT_DATA['unit_num'] = $_POST['unit_num'];
		$INSERT_DATA['block_num'] = $_POST['block_num'];
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert('mate_upd_log',$INSERT_DATA);
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
function update_end_html($L_NAME) {

	$html  = $L_NAME['course_name']."のワンポイント情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>