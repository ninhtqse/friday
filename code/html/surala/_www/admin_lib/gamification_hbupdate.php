<?PHP
/**
 * すらら
 *
 * ゲーミフィケーション管理　本番バッチサーバアップデート
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
function start() {

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "update") {
			// update start 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
			// $ERROR = update();
			if( check_deploy_running("Game") ){
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

	$html .= member_list();
	return $html;
}

/**
 * アップデートリスト一覧処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	global $L_UPDATE_MODE_GAMIFICATION;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM gamification_mate_upd_log" .
		" WHERE state='1'".
		" ORDER BY regist_time";
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
	$html .= "<th>マスタ名</th>\n";
	$html .= "<th>登録日時</th>\n";
	$html .= "<th>登録者ID</th>\n";
	$html .= "<th>本番バッチUP</th>\n";
	$html .= "<th>検証バッチ削除</th>\n";
	$html .= "</tr>\n";
	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {

		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }

		$master_name = get_master_name($list['update_mode'], $list['master_id']);		
		
		$onclick1  = "onclick=\"";
 		$onclick1 .= "if (confirm('".$L_UPDATE_MODE_GAMIFICATION[$list['update_mode']]."を本番バッチサーバーにアップしてもよろしいでしょうか?')) {";
 		$onclick1 .= " document.honban_".$list['mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick1 .= " document.batch_del_".$list['mate_upd_log_num'].".batch_delete_button.disabled = true; ";
 		$onclick1 .= " document.honban_".$list['mate_upd_log_num'].".submit();";
 		$onclick1 .= "};";
 		$onclick1 .= " return false;\"";

 		$onclick2  = "onclick=\"";
 		$onclick2 .= "if (confirm('".$L_UPDATE_MODE_GAMIFICATION[$list['update_mode']]."を検証バッチサーバーから削除してもよろしいでしょうか?')) {";
 		$onclick2 .= " document.honban_".$list['mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick2 .= " document.batch_del_".$list['mate_upd_log_num'].".batch_delete_button.disabled = true; ";
		$onclick2 .= " document.batch_del_".$list['mate_upd_log_num'].".submit();";
 		$onclick2 .= "};";
 		$onclick2 .= " return false;\"";

		$html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
		$html .= "<td align=\"left\">".$L_UPDATE_MODE_GAMIFICATION[$list['update_mode']]."</td>\n";
		$html .= "<td align=\"left\">".$master_name."</td>\n";
		$html .= "<td>".str_replace("-","/",$list['regist_time'])."</td>\n";
		$html .= "<td>".$list['upd_tts_id']."</td>\n";

		$html .= "<td>";
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"honban_".$list['mate_upd_log_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"mate_upd_log_num\" value=\"".$list['mate_upd_log_num']."\">\n";
		$html .= "<input type=\"button\" name=\"batch_up_button\" value=\"本番バッチUP\" ".$onclick1.">";
		$html .= "</form>";
		$html .= "</td>\n";
		$html .= "<td>";
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"batch_del_".$list['mate_upd_log_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
		$html .= "<input type=\"hidden\" name=\"mate_upd_log_num\" value=\"".$list['mate_upd_log_num']."\">\n";
		$html .= "<input type=\"button\" name=\"batch_delete_button\" value=\"検証バッチ削除\" ".$onclick2.">";
		$html .= "</form>";
		$html .= "</td>\n";

		$html .= "</tr>\n";
	}
	$html .= "</table>\n";
	return $html;
}

/**
 * チェック処理（ダミー）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {
	return $ERROR;
}

/**
 * 本番バッチアップ処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function update() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 変数クリア
	$ERROR = null;

	// 存在フラグクリア
	$exist_flg = false;

	// 更新情報取得
	$sql = "SELECT"
		." update_mode, "
		." master_id, "
		." send_data "
		." FROM gamification_mate_upd_log "
		." WHERE mate_upd_log_num = '".$_POST['mate_upd_log_num']."'"
		." AND state = '1'"
		." LIMIT 1";
	
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
		// 更新情報を実行中状態(state = 3)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['start_time'] = "now()";
		$DELETE_DATA['state'] = 3;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('gamification_mate_upd_log',$DELETE_DATA,$where);
		// add end 2019/08/06 yoshizawa デプロイチェック対策

		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/GAMIFICATION_CONTENTSUP.cgi '1' '".$update_mode."' '".$master_id."'";

		// コマンド実行
		exec($command,$LIST);

		// 更新情報を終了状態(state = 2)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 2;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('gamification_mate_upd_log',$DELETE_DATA,$where);

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

	// 変数クリア
	$ERROR = null;

	// 存在フラグクリア
	$exist_flg = false;

	// 更新情報取得
	$sql = "SELECT"
		." update_mode, "
		." master_id,  "
		." send_data  "
		." FROM gamification_mate_upd_log "
		." WHERE mate_upd_log_num = '".$_POST['mate_upd_log_num']."'"
		." AND state = '1'"
		." LIMIT 1";

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
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['start_time'] = "now()";
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('gamification_mate_upd_log', $DELETE_DATA, $where);
		// add end 2019/08/06 yoshizawa デプロイチェック対策

		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/GAMIFICATION_CONTENTSUP.cgi '3' '".$update_mode."' '".$master_id."'";
		// コマンド実行
		exec($command,$LIST);

		// 更新情報を削除状態(state = 0)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 0;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('gamification_mate_upd_log', $DELETE_DATA, $where);
	}
	// update end oda 2018/11/26

	return $ERROR;
}

/**
 * アップデートコンテンツより、アップ対象のマスタ名を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function get_master_name($update_mode, $master_id) {
	
	global  $L_GAM_ITEM_CLASS;
	
	$cdb = $GLOBALS['cdb'];
	$master_name = '';
	
	switch ($update_mode) {
		case 'character':
			$sql = "SELECT character_name FROM ".T_GAMIFICATION_CHARACTER." WHERE character_id = '".$master_id."' LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$master_name = $list['character_name'];
			}
			break;
		case 'achieve_egg':
			break;
		case 'avatar':
			break;
		case 'item':
			break;
	}
	return $master_name;
}