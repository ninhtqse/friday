<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　アップデートリスト
 *
 * @author Azet
 */


$L_PACKAGE_UPDATE_NAME = array(
							'default' => '----------',
							'package_data' => '速習コース情報'
						);


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "update") {
			// update start 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
			// $ERROR = update();
			if( check_deploy_running("Core") ){
				$html .= "<p style=\"color:red; font-weight:bold;\">";
				$html .= "現在、デプロイが実行されているため「本番バッチUP」できませんでした。<br>";
				$html .= "デプロイが完了してから「本番バッチUP」してください。";
				$html .= "</p>";
			} else {
				$ERROR = update();
			}
			// update end 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
		} elseif (ACTION == "delete") {
			$ERROR = delete();
		}
	}

	$html .= update_list();
	return $html;
}


/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function update_list() {

	global $L_PACKAGE_UPDATE_NAME;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT *, package_mate_upd_log.upd_tts_id AS user_id FROM package_mate_upd_log package_mate_upd_log" .
		" LEFT JOIN package_course package_course ON package_course.pk_course_num=package_mate_upd_log.pk_course_num" .
		" LEFT JOIN package_stage package_stage ON package_stage.pk_stage_num=package_mate_upd_log.pk_stage_num" .
		" LEFT JOIN package_lesson package_lesson ON package_lesson.pk_lesson_num=package_mate_upd_log.pk_lesson_num" .
		" LEFT JOIN package_unit package_unit ON package_unit.pk_unit_num=package_mate_upd_log.pk_unit_num" .
		" LEFT JOIN package_block package_block ON package_block.pk_block_num=package_mate_upd_log.pk_block_num" .
		" LEFT JOIN package_list package_list ON package_list.pk_list_num=package_mate_upd_log.pk_list_num" .
		" WHERE package_mate_upd_log.state='1'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br style=\"clear:left;\">";
		$html .= "アップデート予定情報がありません。";
		return $html;
	}
	$html .= "<br style=\"clear:left;\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<th>アップコンデートテンツ</th>\n";
	$html .= "<th>カテゴリー情報</th>\n";
	$html .= "<th>登録日時</th>\n";
	$html .= "<th>登録者ID</th>\n";
	$html .= "<th>本番バッチUP</th>\n";
	$html .= "<th>検証バッチ削除</th>\n";
	$html .= "</tr>\n";
	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {
		unset($s_l_u_b);
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
		if ($list['pk_course_name']) { $s_l_u_b .= $list['pk_course_name']; }
		if ($list['pk_stage_name']) { $s_l_u_b .= "/" . $list['pk_stage_name']; }
		if ($list['pk_lesson_name']) { $s_l_u_b .= "/" . $list['pk_lesson_name']; }
		if ($list['pk_unit_name']) { $s_l_u_b .= "/" . $list['pk_unit_name']; }
		if ($list['pk_block_name']) { $s_l_u_b .= "/" . $list['pk_block_name']; }
		if ($list['pk_list_name']) { $s_l_u_b .= "/" . $list['pk_list_name']; }

		if (!$list['pk_course_name']) { $list['pk_course_name'] = "--"; }

		// add start oda 2015/11/09 プラクティスアップデート 不具合対応
		// 同一のリストの本番バッチUPを複数回行うと、ドリル単位で削除してしまう不具合が有る。
		// 画面では、１回実行したリストのボタンを押せない様に修正する
 		$onclick1  = "onclick=\"";
 		$onclick1 .= "if (confirm('".$L_PACKAGE_UPDATE_NAME[$list['update_mode']]."を本番バッチサーバーにアップしてもよろしいでしょうか?')) {";
 		$onclick1 .= " document.honban_".$list['package_mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick1 .= " document.batch_del_".$list['package_mate_upd_log_num'].".batch_delete_button.disabled = true; ";
 		$onclick1 .= " document.honban_".$list['package_mate_upd_log_num'].".submit();";
 		$onclick1 .= "};";
 		$onclick1 .= " return false;\"";

 		$onclick2  = "onclick=\"";
 		$onclick2 .= "if (confirm('".$L_PACKAGE_UPDATE_NAME[$list['update_mode']]."を検証バッチサーバーから削除してもよろしいでしょうか?')) {";
 		$onclick2 .= " document.honban_".$list['package_mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick2 .= " document.batch_del_".$list['package_mate_upd_log_num'].".batch_delete_button.disabled = true; ";
		$onclick2 .= " document.batch_del_".$list['package_mate_upd_log_num'].".submit();";
 		$onclick2 .= "};";
 		$onclick2 .= " return false;\"";
 		// add end oda 2015/11/09

		$html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
		$html .= "<td align=\"left\">".$L_PACKAGE_UPDATE_NAME[$list['update_mode']]."</td>\n";
		$html .= "<td align=\"left\">".$s_l_u_b."</td>\n";
		$html .= "<td>".str_replace("-","/",$list['regist_time'])."</td>\n";
		$html .= "<td>".$list['user_id']."</td>\n";

		// update start oda 2018/11/26 プラクティスアップデート不具合 htmlタグを修正
// 		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";															// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"honban_".$list['package_mate_upd_log_num']."\">\n";		// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
// 		$html .= "<input type=\"hidden\" name=\"package_mate_upd_log_num\" value=\"".$list['package_mate_upd_log_num']."\">\n";
// 		//$html .= "<td><input type=\"submit\" value=\"本番バッチUP\" onclick=\"return confirm('".$L_PACKAGE_UPDATE_NAME[$list['update_mode']]."を本番バッチサーバーにアップしてもよろしいでしょうか?')\"></td>\n";	// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<td><input type=\"button\" name=\"batch_up_button\" value=\"本番バッチUP\" ".$onclick1."></td>\n";						// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "</form>\n";
// 		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";															// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"batch_del_".$list['package_mate_upd_log_num']."\">\n";	// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
// 		$html .= "<input type=\"hidden\" name=\"package_mate_upd_log_num\" value=\"".$list['package_mate_upd_log_num']."\">\n";
// 		//$html .= "<td><input type=\"submit\" value=\"検証バッチ削除\" onclick=\"return confirm('".$L_PACKAGE_UPDATE_NAME[$list['update_mode']]."を検証バッチサーバーから削除してもよろしいでしょうか?')\"></td>\n";		// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<td><input type=\"button\" name=\"batch_delete_button\" value=\"検証バッチ削除\" ".$onclick2."></td>\n";					// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "</form>\n";
		$html .= "<td>";
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"honban_".$list['package_mate_upd_log_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"package_mate_upd_log_num\" value=\"".$list['package_mate_upd_log_num']."\">\n";
		$html .= "<input type=\"button\" name=\"batch_up_button\" value=\"本番バッチUP\" ".$onclick1.">\n";
		$html .= "</form>";
		$html .= "</td>\n";
		$html .= "<td>";
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"batch_del_".$list['package_mate_upd_log_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
		$html .= "<input type=\"hidden\" name=\"package_mate_upd_log_num\" value=\"".$list['package_mate_upd_log_num']."\">\n";
		$html .= "<input type=\"button\" name=\"batch_delete_button\" value=\"検証バッチ削除\" ".$onclick2."></td>\n";
		$html .= "</form>";
		$html .= "</td>\n";
		// update end oda 2018/11/26

		$html .= "</tr>\n";
	}
	$html .= "</table>\n";
	return $html;
}


/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function check() {
	return $ERROR;
}


/**
 * 本番バッチUP処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function update() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// update start oda 2018/11/26 プラクティスアップデート不具合 stateを参照し、存在チェックを行う様に修正
// 	//	更新情報取得
// 	$sql  = "SELECT update_mode, pk_course_num, pk_stage_num, pk_lesson_num, pk_unit_num, pk_block_num, pk_list_num".
// 			" FROM package_mate_upd_log".
// 			" WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'".
// 			" LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		$update_mode = $list['update_mode'];
// 		$pk_course_num = $list['pk_course_num'];
// 		$pk_stage_num = $list['pk_stage_num'];
// 		$pk_lesson_num = $list['pk_lesson_num'];
// 		$pk_unit_num = $list['pk_unit_num'];
// 		$pk_block_num = $list['pk_block_num'];
// 		$pk_list_num = $list['pk_list_num'];
// 	}

// 	//	データー移行・削除
// 	// $command = "ssh suralacore01@srlbtw21 ./PACKAGECONTENTSUP.cgi '1' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."' '".$_SESSION['myid']['id']."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/PACKAGECONTENTSUP.cgi '1' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."' '".$_SESSION['myid']['id']."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// //echo $command."<br>\n";
// 	//upd start 2017/11/24 yamaguchi AWS移設
// 	//exec($command,&$LIST);
// 	exec($command,$LIST);
// 	//upd end 2017/11/24 yamaguchi

// 	//	本番バッチ更新データー追加
// 	$INSERT_DATA = array();
// 	$INSERT_DATA['update_mode'] = $update_mode;
// 	$INSERT_DATA['pk_course_num'] = $pk_course_num;
// 	$INSERT_DATA['pk_stage_num'] = $pk_stage_num;
// 	$INSERT_DATA['pk_lesson_num'] = $pk_lesson_num;
// 	$INSERT_DATA['pk_unit_num'] = $pk_unit_num;
// 	$INSERT_DATA['pk_block_num'] = $pk_block_num;
// 	$INSERT_DATA['pk_list_num'] = $pk_list_num;
// 	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
// 	insert_hb_log($INSERT_DATA, $ERROR);

// 	//	ログ更新
// 	$DELETE_DATA = array();
// 	$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
// 	$DELETE_DATA['regist_time'] = "now()";
// 	$DELETE_DATA['state'] = 2;
// 	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 	$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);


	// 変数クリア
	$ERROR = null;

	//	更新情報取得
	$sql  = "SELECT ".
			"  update_mode, ".
			"  pk_course_num, ".
			"  pk_stage_num, ".
			"  pk_lesson_num, ".
			"  pk_unit_num, ".
			"  pk_block_num, ".
			"  pk_list_num ".
			" FROM package_mate_upd_log".
			" WHERE package_mate_upd_log_num = '".$_POST['package_mate_upd_log_num']."'".
			"   AND state = '1'".
			" LIMIT 1";

	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {

			// add start 2019/08/06 yoshizawa デプロイチェック対策
			// 更新情報を終了状態(state = 3)にする
			$DELETE_DATA = array();
			$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
			$DELETE_DATA['start_time'] = "now()";
			$DELETE_DATA['state'] = 3;
			$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

			$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);
			// add end 2019/08/06 yoshizawa デプロイチェック対策

			// アップデートデータが読み込めたら処理開始
			// （複数画面で操作した場合にstateが変わってしまっている可能性がある為）
			$update_mode = $list['update_mode'];
			$pk_course_num = $list['pk_course_num'];
			$pk_stage_num = $list['pk_stage_num'];
			$pk_lesson_num = $list['pk_lesson_num'];
			$pk_unit_num = $list['pk_unit_num'];
			$pk_block_num = $list['pk_block_num'];
			$pk_list_num = $list['pk_list_num'];

			// データー移行・削除
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/PACKAGECONTENTSUP.cgi '1' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."' '".$_SESSION['myid']['id']."'";

			// コマンド実行
			exec($command,$LIST);

			// 本番バッチ更新データー追加
			$INSERT_DATA = array();
			$INSERT_DATA['update_mode'] = $update_mode;
			$INSERT_DATA['pk_course_num'] = $pk_course_num;
			$INSERT_DATA['pk_stage_num'] = $pk_stage_num;
			$INSERT_DATA['pk_lesson_num'] = $pk_lesson_num;
			$INSERT_DATA['pk_unit_num'] = $pk_unit_num;
			$INSERT_DATA['pk_block_num'] = $pk_block_num;
			$INSERT_DATA['pk_list_num'] = $pk_list_num;
			$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
			insert_hb_log($INSERT_DATA, $ERROR);

			// 更新情報を終了状態(state = 2)にする
			$DELETE_DATA = array();
			$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
			$DELETE_DATA['regist_time'] = "now()";
			$DELETE_DATA['state'] = 2;
			$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

			$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);
		}
	}

	return $ERROR;
}


/**
 * 検証バッチ削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function delete() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// update start oda 2018/11/26 プラクティスアップデート不具合 stateを参照し、存在チェックを行う様に修正
// 	//	更新情報取得
// 	$sql  = "SELECT update_mode, pk_course_num, pk_stage_num, pk_lesson_num, pk_unit_num, pk_block_num, pk_list_num FROM package_mate_upd_log".
// 			" WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."' LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		foreach ($list AS $key => $val) {
// 			$$key = $val;
// 		}
// 	}

// 	//	データー削除
// 	// $command = "ssh suralacore01@srlbtw21 ./PACKAGECONTENTSUP.cgi '3' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/PACKAGECONTENTSUP.cgi '3' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// //echo $command."<br>\n";
// 	//upd start 2017/11/24 yamaguchi AWS移設
// 	//exec($command,&$LIST);
// 	exec($command,$LIST);
// 	//upd end 2017/11/24 yamaguchi
// //pre($LIST);

// 	unset($DELETE_DATA);
// 	$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
// 	$DELETE_DATA['regist_time'] = "now()";
// 	$DELETE_DATA['state'] = 0;
// 	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 	$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);


	// 変数クリア
	$ERROR = null;

	// 存在フラグクリア
	$exist_flg = false;

	//	更新情報取得
	$sql  = "SELECT ".
			"  update_mode, ".
			"  pk_course_num, ".
			"  pk_stage_num, ".
			"  pk_lesson_num, ".
			"  pk_unit_num, ".
			"  pk_block_num, ".
			"  pk_list_num ".
			" FROM package_mate_upd_log".
			" WHERE package_mate_upd_log_num = '".$_POST['package_mate_upd_log_num']."'".
			"   AND state = '1'".
			" LIMIT 1";

	//	更新情報取得
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			foreach ($list AS $key => $val) {
				$$key = $val;
			}
			$exist_flg = true;			// 更新ログが存在したらバッチを実行する（複数画面で操作した場合にstateが変わってしまっている可能性がある為）
		}
	}

	// 更新情報が存在したら
	if ($exist_flg) {

		// add start 2019/08/06 yoshizawa デプロイチェック対策
		// 処理の開始時間を記録する
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
		$DELETE_DATA['start_time'] = "now()";
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);
		// add end 2019/08/06 yoshizawa デプロイチェック対策

		//	データー削除
		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/PACKAGECONTENTSUP.cgi '3' '".$update_mode."' '".$pk_course_num."' '".$pk_stage_num."' '".$pk_lesson_num."' '".$pk_unit_num."' '".$pk_block_num."' '".$pk_list_num."'";

		// コマンド実行
		exec($command,$LIST);

		// 更新情報を削除状態(state = 0)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE package_mate_upd_log_num='".$_POST['package_mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 0;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('package_mate_upd_log',$DELETE_DATA,$where);

	}

	return $ERROR;
}


/**
 * 本番バッチ更新データー追加
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $INSERT_DATA
 * @param array &$ERROR
 */
function insert_hb_log($INSERT_DATA, &$ERROR) {

	global $L_DB;

	if (!$INSERT_DATA) {
		return;
	}

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_DB['srlbhw11']);
	$ERROR = $connect_db->set_connect_db();

// 	if ($ERROR) {
// 		$html .= ERROR($ERROR);
// 	}

	$insert_name = "";
	$values = "";
	foreach ($INSERT_DATA AS $key => $value) {
		if ($insert_name) {
			$insert_name .= ",";
			$values .= ",";
		}
		$insert_name .= $key;
		$values .= "'".$value.",";
	}
	$insert_name .= ", regist_data";
	$values .= ", now()";

	$sql  = "INSERT INTO package_mate_upd_log".
			" (".$insert_name.") ".
			" VALUES (".$values.");";
//echo $sql;
	if (!$connect_db->query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
//	$connect_db->db_close();

}
?>