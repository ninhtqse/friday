<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　本番バッチサーバアップデート
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

	global $L_UNIT_TYPE,$L_UPDATE_NAME;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
	// AWS移設後はバックアップ時間の延長によるオープン遅延の心配がないため不要となります。
	// // add start oda 2017/09/25 本番バッチのアップ容量を確認可能とする
	// // 検証バッチ経由で本番バッチのディスクサイズを取得する
	// //   srlbtw21 /home/suralacore01/check_disk.sh
	// //   ↓
	// //   srlbhw11 /data/bat/check_disk.sh
	// //   ※このシェルでduコマンドを実行する
	// $max_disk_size = 2000.0;
	// exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk.sh", $output, $return_var);
	// if (is_array($output)) {
	// 	$disk_size = 0;
	// 	if ($output[0]) {
	// 		// 取得した情報はタブ区切りの為、分割する
	// 		$size_list = explode("\t", $output[0]);
	// 		// 分割した情報の先頭がディスク使用サイス（KB）
	// 		if (is_array($size_list)) {
	// 			$disk_size = $size_list[0];
	// 		}
	// 		// MBに変換（四捨五入）
	// 		$disk_size = round($disk_size / 1024, 1 );
	// 	}
	// }
	// $zan_disk_size = $max_disk_size - $disk_size;
	// $html .= "<br><br>\n";
	// $html .= "<table class=\"member_form\" style=\"display:inline-block;\">\n";
	// $html .= "<tr class=\"member_form_menu\">\n";
	// $html .= "<td>ファイルアップ制限値(MB)</td>\n";
	// $html .= "<td>ファイルアップ済サイズ(MB)</td>\n";
	// $html .= "<td>ファイルアップ可能サイズ(MB)</td>\n";
	// $html .= "</tr>\n";
	// $html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
	// $html .= "<td>".sprintf('%.1f',$max_disk_size)."</td>\n";
	// $html .= "<td>".sprintf('%.1f',$disk_size)."</td>\n";
	// $html .= "<td>".sprintf('%.1f',$zan_disk_size)."</td>\n";
	// $html .= "</tr>\n";
	// $html .= "</table>\n";
	// $html .= "<div style=\"display:inline-block; font-size:12px;\">ファイルアップ可能サイズが０になるまでアップ可能です。<br>※データベースのアップ量に制限はありません。</div><br>\n";
	// // add end oda 2017/09/25
	// del end 2018/03/19 yoshizawa AWSプラクティスアップデート


	$sql = "SELECT " .
		" *, " .
		" mate_upd_log.course_num as m_course_num, " .
		" mate_upd_log.stage_num as m_stage_num, " .
		" mate_upd_log.lesson_num as m_lesson_num, " .
		" mate_upd_log.unit_num as m_unit_num, " .
		" mate_upd_log.block_num as m_block_num " .
		" FROM mate_upd_log mate_upd_log" .
		" LEFT JOIN course course ON course.course_num=mate_upd_log.course_num" .
		" LEFT JOIN stage stage ON stage.stage_num=mate_upd_log.stage_num" .
		" LEFT JOIN lesson lesson ON lesson.lesson_num=mate_upd_log.lesson_num" .
		" LEFT JOIN unit unit ON unit.unit_num=mate_upd_log.unit_num" .
		" LEFT JOIN block block ON block.block_num=mate_upd_log.block_num" .
		// upd start hasegawa 2017/12/27 AWS移設 ソート条件追加
		// " WHERE mate_upd_log.state='1'";
		" WHERE mate_upd_log.state='1'".
		" ORDER BY mate_upd_log.regist_time";
		// upd end hasegawa 2017/12/27

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
	$html .= "<th>教科</th>\n";
	$html .= "<th>アップコンデートテンツ</th>\n";
	$html .= "<th>ステージ / レッスン / ユニット / ドリル</th>\n";
	// $html .= "<th>ファイルアップサイズ(MB)</th>\n";			// add start oda 2017/09/26 本番バッチのアップ容量を確認可能とする // del 2018/03/19 yoshizawa AWSプラクティスアップデート
	$html .= "<th>登録日時</th>\n";
	$html .= "<th>登録者ID</th>\n";
	$html .= "<th>本番バッチUP</th>\n";
	$html .= "<th>検証バッチ削除</th>\n";
	$html .= "</tr>\n";
	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {

        // >>> add 2019/04/02 yoshizawa  漢字学習コンテンツ すらら英単語
		// $service_num = get_service_num($list['m_course_num']);
        // <<<

		unset($s_l_u_b);
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
		if ($list['stage_name']) { $s_l_u_b .= $list['stage_name']; }
		if ($list['lesson_name']) { $s_l_u_b .= "/" . $list['lesson_name']; }
		if ($list['unit_name']) { $s_l_u_b .= "/" . $list['unit_name']; }
		if ($list['block_type']) { $s_l_u_b .= "/" . $L_UNIT_TYPE[$list['block_type']]; }
		//add start okabe 2013/12/02
		if ($list['update_mode'] == "secret_puzzle") {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$image_category = $VALUES['image_category'];
			if ($image_category > 0) {
				$s_l_u_b = "開示年月:".$image_category;
			} else {
				$s_l_u_b = "全件";
			}
		}
		//add end okabe 2013/12/02
		//add start hirose 2018/10/10 commonフォルダアップ機能追加
		elseif($list['update_mode'] == "system_chart_common" || $list['update_mode'] == "lecture_common"){
			$send_data = $list['send_data'];
			$send_data_dir = unserialize($send_data);
			if(isset($send_data_dir['dir'])){
				$s_l_u_b = $send_data_dir['dir'];
			}else{
				$s_l_u_b = '<span style = "color:red">不正なデータです</span>';
			}
		}
		//add end hirose 2018/10/10 commonフォルダアップ機能追加

		if (!$list['course_name']) { $list['course_name'] = "--"; }

		// add oda 2017/09/26
		// アップコンテンツ毎の使用ディスクサイズを取得する
		// $disk_size = file_size_check($list['update_mode'], $list['m_course_num'], $list['m_stage_num'], $list['m_lesson_num'], $list['m_unit_num'],  $list['m_block_num'] ); // del 2018/03/19 yoshizawa AWSプラクティスアップデート

		// add start oda 2015/11/09 プラクティスアップデート 不具合対応
		// 同一のリストの本番バッチUPを複数回行うと、ドリル単位で削除してしまう不具合が有る。
		// 画面では、１回実行したリストのボタンを押せない様に修正する
 		$onclick1  = "onclick=\"";
 		$onclick1 .= "if (confirm('".$L_UPDATE_NAME[$list['update_mode']]."を本番バッチサーバーにアップしてもよろしいでしょうか?')) {";
 		$onclick1 .= " document.honban_".$list['mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick1 .= " document.batch_del_".$list['mate_upd_log_num'].".batch_delete_button.disabled = true; ";
 		$onclick1 .= " document.honban_".$list['mate_upd_log_num'].".submit();";
 		$onclick1 .= "};";
 		$onclick1 .= " return false;\"";

		// add start 2019/04/02 yoshizawa  漢字学習コンテンツ すらら英単語 リリースまで本番反映は不可とします。
		// 漢字学習コンテンツサービスのコースの場合 or すらら英単語サービスのコースの場合
		// if($service_num == 15 || $service_num == 16){
		// 	$onclick1 = '';
		// 	$onclick1  = "onclick=\"alert('本番アップはできません。');\" return false;";
		// 	$onclick1 .= " disabled=\"disabled\"";
		// }
		// add end 2019/04/02 yoshizawa 漢字学習コンテンツ すらら英単語

		// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
		// // add start oda 2017/09/27 アップ容量が残サイズより大きい場合は、ボタンを非表示とする
 		// if ($disk_size > $zan_disk_size) {
 		// 	$onclick1  = "onclick=\"";
	 	// 	$onclick1 .= "alert('ファイルアップ可能サイズを超えています。');";
 		// 	$onclick1 .= " return false;\"";
		// }
		// // ファイルをアップするリストは何もしない
		// if ($list['update_mode'] == "course_stage"  || $list['update_mode'] == "course_skill"        || $list['update_mode'] == "course_chart"     ||
		// 	$list['update_mode'] == "course_print"  || $list['update_mode'] == "course_flash"        || $list['update_mode'] == "problem"          ||
		// 	$list['update_mode'] == "manual"        || $list['update_mode'] == "course_english_word" || $list['update_mode'] == "course_one_point" ||
		// 	$list['update_mode'] == "secret_puzzle" || $list['update_mode'] == "secret_stamp") {
		// }
		// // ファイルをアップしないリストは<br>に置き換える
		// else {
		// 	$disk_size = "<br>";
		// }
		// // add end oda 2017/09/27
		// del end 2018/03/19 yoshizawa AWSプラクティスアップデート

 		$onclick2  = "onclick=\"";
 		$onclick2 .= "if (confirm('".$L_UPDATE_NAME[$list['update_mode']]."を検証バッチサーバーから削除してもよろしいでしょうか?')) {";
 		$onclick2 .= " document.honban_".$list['mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick2 .= " document.batch_del_".$list['mate_upd_log_num'].".batch_delete_button.disabled = true; ";
		$onclick2 .= " document.batch_del_".$list['mate_upd_log_num'].".submit();";
 		$onclick2 .= "};";
 		$onclick2 .= " return false;\"";
 		// add end oda 2015/11/09

		$html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
		$html .= "<td>".$list['course_name']."</td>\n";
		$html .= "<td align=\"left\">".$L_UPDATE_NAME[$list['update_mode']]."</td>\n";
		$html .= "<td align=\"left\">".$s_l_u_b."</td>\n";
		// $html .= "<td align=\"right\">".$disk_size."</td>\n";					// add start oda 2017/09/26 本番バッチのアップ容量を確認可能とする // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$html .= "<td>".str_replace("-","/",$list['regist_time'])."</td>\n";
		$html .= "<td>".$list['upd_tts_id']."</td>\n";

		// update start oda 2018/11/26 プラクティスアップデート不具合 htmlタグを修正
// 		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";														// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"honban_".$list['mate_upd_log_num']."\">\n";			// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
// 		$html .= "<input type=\"hidden\" name=\"mate_upd_log_num\" value=\"".$list['mate_upd_log_num']."\">\n";
// 		//$html .= "<td><input type=\"submit\" value=\"本番バッチUP\" onclick=\"return confirm('".$L_UPDATE_NAME[$list['update_mode']]."を本番バッチサーバーにアップしてもよろしいでしょうか?')\"></td>\n";		// del oda 2015/11/09 プラクティスアップデート 不具合対応
//  		$html .= "<td><input type=\"button\" name=\"batch_up_button\" value=\"本番バッチUP\" ".$onclick1."></td>\n";					// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "</form>\n";
// 		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";														// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"batch_del_".$list['mate_upd_log_num']."\">\n";		// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
// 		$html .= "<input type=\"hidden\" name=\"mate_upd_log_num\" value=\"".$list['mate_upd_log_num']."\">\n";
// 		//$html .= "<td><input type=\"submit\" value=\"検証バッチ削除\" onclick=\"return confirm('".$L_UPDATE_NAME[$list['update_mode']]."を検証バッチサーバーから削除してもよろしいでしょうか?')\"></td>\n";			// del oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "<td><input type=\"button\" name=\"batch_delete_button\" value=\"検証バッチ削除\" ".$onclick2."></td>\n";				// add oda 2015/11/09 プラクティスアップデート 不具合対応
// 		$html .= "</form>\n";

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
		// update end oda 2018/11/26

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

	// update start oda 2018/11/26 プラクティスアップデート不具合 stateを参照し、存在チェックを行う様に修正
// 	//	更新情報取得
// 	//$sql  = "SELECT update_mode, course_num, stage_num, lesson_num, unit_num, block_num FROM mate_upd_log".			//del okabe 2013/11/29
// 	$sql = "SELECT update_mode, course_num, stage_num, lesson_num, unit_num, block_num, send_data FROM mate_upd_log".	//add okabe 2013/11/29
// 			" WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."' LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		foreach ($list AS $key => $val) {
// 			$$key = $val;
// 		}
// 	}

// 	//	データー移行・削除
// 	//$command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '1' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'";		//del okabe 2013/11/29
// 	//add okabe start 2013/11/29
// 	if ($update_mode == "secret_puzzle" || $update_mode == "secret_stamp") {
// 		$send_data = $list['send_data'];
// 		$VALUES = unserialize($send_data);
// 		$fileup = $VALUES['fileup'];
// 		//$command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$block_num."' '".$fileup."'";	//シークレットイベント  //del okabe 2013/12/02
// 		// add okabe start 2013/12/02
// 		if ($update_mode == "secret_puzzle") {
// 			// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$block_num."' '".$fileup."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$block_num."' '".$fileup."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		} else {
// 			// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$fileup."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$fileup."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		}
// 		// add okabe end 2013/12/02
// 	//add start hirose 2018/10/10 commonフォルダアップ機能追加
// 	}elseif($list['update_mode'] == "system_chart_common" || $list['update_mode'] == "lecture_common"){
// 		$send_data = $list['send_data'];
// 		$update_dir = unserialize($send_data);

// 		//初期化
// 		$update_dir_remote = "";
// 		$update_dir_full = "";
// 		$upd_dir_remote = array();
// 		$file_type = '';

// 		//配列のファイルタイプとディレクトリのチェック
// 		if($update_dir['dir'] && $update_dir['file_type']){
// 			$upd_dir_remote = explode("/", $update_dir['dir']);
// 			$file_type = $update_dir['file_type'];
// 		}
// 		//コピーコマンドはコピーするファイルの一つ上の階層までを指定するため、1階層目のみを取得する。
// 		//4階層分取れるので、末尾の2階層を取得する。
// 		if($upd_dir_remote[2] && $upd_dir_remote[3]){
// 			$update_dir_full = $upd_dir_remote[2].'/'.$upd_dir_remote[3];
// 			$update_dir_remote = $upd_dir_remote[2].'/';
// 		//1階層しかなかった場合
// 		}elseif($upd_dir_remote[2]){
// 			$update_dir_full = $upd_dir_remote[2];
// 		}
// 		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '1' '".$update_mode."' '".$update_dir_full."' '".$update_dir_remote."' '".$file_type."'";
// 	//add end hirose 2018/10/10 commonフォルダアップ機能追加
// 	} else {
// 		// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '1' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '1' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 	}
// 	//add okabe end 2013/11/29
// //echo $command."<br>\n";
// 	//upd start 2017/11/24 yamaguchi AWS移設
// 	//exec($command,&$LIST);
// 	exec($command,$LIST);
// 	//upd end 2017/11/24 yamaguchi
// //pre($LIST);

// 	$DELETE_DATA = null;
// 	unset($DELETE_DATA);
// 	$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
// 	$DELETE_DATA['regist_time'] = "now()";
// 	$DELETE_DATA['state'] = 2;
// 	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 	$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);

	// 変数クリア
	$ERROR = null;

	// 存在フラグクリア
	$exist_flg = false;

	//	更新情報取得
	$sql = "SELECT ".
			"  update_mode, ".
			"  course_num, ".
			"  stage_num, ".
			"  lesson_num, ".
			"  unit_num, ".
			"  block_num, ".
			"  send_data ".
			"  FROM mate_upd_log ".
			" WHERE mate_upd_log_num = '".$_POST['mate_upd_log_num']."'".
			"   AND state = '1'".
			" LIMIT 1";
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

		$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);
		// add end 2019/08/06 yoshizawa デプロイチェック対策

		//	シークレットイベント
		if ($update_mode == "secret_puzzle" || $update_mode == "secret_stamp") {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$fileup = $VALUES['fileup'];
			if ($update_mode == "secret_puzzle") {
				$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$block_num."' '".$fileup."'";
			} else {
				$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '1' '".$update_mode."' '".$fileup."'";
			}

		//add start hirose 2018/10/10 commonフォルダアップ機能追加
		}elseif($list['update_mode'] == "system_chart_common" || $list['update_mode'] == "lecture_common"){
			$send_data = $list['send_data'];
			$update_dir = unserialize($send_data);

			//初期化
			$update_dir_remote = "";
			$update_dir_full = "";
			$upd_dir_remote = array();
			$file_type = '';

			//配列のファイルタイプとディレクトリのチェック
			if($update_dir['dir'] && $update_dir['file_type']){
				$upd_dir_remote = explode("/", $update_dir['dir']);
				$file_type = $update_dir['file_type'];
			}
			//コピーコマンドはコピーするファイルの一つ上の階層までを指定するため、1階層目のみを取得する。
			//4階層分取れるので、末尾の2階層を取得する。
			if($upd_dir_remote[2] && $upd_dir_remote[3]){
				$update_dir_full = $upd_dir_remote[2].'/'.$upd_dir_remote[3];
				$update_dir_remote = $upd_dir_remote[2].'/';
				//1階層しかなかった場合
			}elseif($upd_dir_remote[2]){
				$update_dir_full = $upd_dir_remote[2];
			}
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '1' '".$update_mode."' '".$update_dir_full."' '".$update_dir_remote."' '".$file_type."'";
		//add end hirose 2018/10/10 commonフォルダアップ機能追加

		} else {
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '1' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'";
		}

		// コマンド実行
		exec($command,$LIST);

		// 更新情報を終了状態(state = 2)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 2;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);

	}
	// update end oda 2018/11/26

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
// 	//$sql  = "SELECT update_mode, course_num, stage_num, lesson_num, unit_num, block_num FROM mate_upd_log".			//del okabe 2013/11/29
// 	$sql  = "SELECT update_mode, course_num, stage_num, lesson_num, unit_num, block_num, send_data FROM mate_upd_log".	//add okabe 2013/11/29
// 			" WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."' LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		foreach ($list AS $key => $val) {
// 			$$key = $val;
// 		}
// 	}

// 	//	データー削除
// 	//$command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'";		//del okabe 2013/11/29
// 	//add okabe start 2013/11/29
// 	if ($update_mode == "secret_puzzle" || $update_mode == "secret_stamp") {
// 		$send_data = $list['send_data'];
// 		$VALUES = unserialize($send_data);
// 		$fileup = $VALUES['fileup'];
// 		//$command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$block_num."' '".$fileup."'";		// del okabe 2013/12/02
// 		// add okabe start 2013/12/02
// 		if ($update_mode == "secret_puzzle") {
// 			// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$block_num."' '".$fileup."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$block_num."' '".$fileup."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		} else {
// 			// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$fileup."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$fileup."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		}
// 		// add okabe end 2013/12/02
// 	//add start hirose 2018/10/10 commonフォルダアップ機能追加
// 	}elseif($list['update_mode'] == "system_chart_common" || $list['update_mode'] == "lecture_common"){
// 		$send_data = $list['send_data'];
// 		$update_dir = unserialize($send_data);
// 		//初期化
// 		$update_dir_remote = "";
// 		$update_dir_full = "";
// 		$upd_dir_remote = array();
// 		$file_type = '';

// 		//データベースの階層情報と、ファイルタイプを確認
// 		if($update_dir['dir'] && $update_dir['file_type']){
// 			$upd_dir_remote = explode("/", $update_dir['dir']);
// 			$file_type = $update_dir['file_type'];
// 		}
// 		//コピーコマンドはコピーするファイルの一つ上の階層までを指定するため、1階層目のみを取得する。
// 		//4階層分取れるので、末尾の2階層を取得する。
// 		if($upd_dir_remote[2] && $upd_dir_remote[3]){
// 			$update_dir_full = $upd_dir_remote[2].'/'.$upd_dir_remote[3];
// 			$update_dir_remote = $upd_dir_remote[2].'/';
// 		}elseif($upd_dir_remote[2]){
// 			$update_dir_full = $upd_dir_remote[2];
// 		}
// 		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '3' '".$update_mode."' '".$update_dir_full."' '".$update_dir_remote."' '".$file_type."'";
// 	//add end hirose 2018/10/10 commonフォルダアップ機能追加
// 	} else {
// 		// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'"; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
// 		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'"; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
// 	}
// 	//add okabe end 2013/11/29
// //echo $command."<br>\n";
// 	//upd start 2017/11/24 yamaguchi AWS移設
// 	//exec($command,&$LIST);
// 	exec($command,$LIST);
// 	//upd end 2017/11/24 yamaguchi
// //pre($LIST);

// 	$DELETE_DATA = null;
// 	unset($DELETE_DATA);
// 	$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
// 	$DELETE_DATA['regist_time'] = "now()";
// 	$DELETE_DATA['state'] = 0;
// 	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 	$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);


	// 変数クリア
	$ERROR = null;

	// 存在フラグクリア
	$exist_flg = false;

	// 更新情報取得
	$sql = "SELECT ".
			"  update_mode, ".
			"  course_num, ".
			"  stage_num, ".
			"  lesson_num, ".
			"  unit_num, ".
			"  block_num, ".
			"  send_data ".
			"  FROM mate_upd_log ".
			" WHERE mate_upd_log_num = '".$_POST['mate_upd_log_num']."'".
			"   AND state = '1'".
			" LIMIT 1";

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

		$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);
		// add end 2019/08/06 yoshizawa デプロイチェック対策

		// シークレットイベント
		if ($update_mode == "secret_puzzle" || $update_mode == "secret_stamp") {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$fileup = $VALUES['fileup'];
			if ($update_mode == "secret_puzzle") {
				$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$block_num."' '".$fileup."'";
			} else {
				$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '3' '".$update_mode."' '".$fileup."'";
			}

		//add start hirose 2018/10/10 commonフォルダアップ機能追加
		}elseif($list['update_mode'] == "system_chart_common" || $list['update_mode'] == "lecture_common"){
			$send_data = $list['send_data'];
			$update_dir = unserialize($send_data);
			//初期化
			$update_dir_remote = "";
			$update_dir_full = "";
			$upd_dir_remote = array();
			$file_type = '';

			//データベースの階層情報と、ファイルタイプを確認
			if($update_dir['dir'] && $update_dir['file_type']){
				$upd_dir_remote = explode("/", $update_dir['dir']);
				$file_type = $update_dir['file_type'];
			}
			//コピーコマンドはコピーするファイルの一つ上の階層までを指定するため、1階層目のみを取得する。
			//4階層分取れるので、末尾の2階層を取得する。
			if($upd_dir_remote[2] && $upd_dir_remote[3]){
				$update_dir_full = $upd_dir_remote[2].'/'.$upd_dir_remote[3];
				$update_dir_remote = $upd_dir_remote[2].'/';
			}elseif($upd_dir_remote[2]){
				$update_dir_full = $upd_dir_remote[2];
			}
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '3' '".$update_mode."' '".$update_dir_full."' '".$update_dir_remote."' '".$file_type."'";
		//add end hirose 2018/10/10 commonフォルダアップ機能追加

		} else {
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'";
		}

		// コマンド実行
		exec($command,$LIST);

		// 更新情報を削除状態(state = 0)にする
		$DELETE_DATA = null;
		unset($DELETE_DATA);
		$where = " WHERE mate_upd_log_num='".$_POST['mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 0;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->update('mate_upd_log',$DELETE_DATA,$where);
	}
	// update end oda 2018/11/26

	return $ERROR;
}

// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
// // add oda 2017/09/25 本番バッチのアップ容量を確認可能とする
// /**
//  * 検証バッチのディスクサイズチェック
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @param string $mode 処理モード
//  * @param int $course_num コース管理番号（省略可）
//  * @param int $stage_num ステージ管理番号（省略可）
//  * @param int $lesson_num レッスン管理番号（省略可）
//  * @param int $unit_num ユニット管理番号（省略可）
//  * @param int $block_num ドリル管理番号（省略可）
//  * @return int データサイズ（対象外またはエラーの時は0）
//  */
// function file_size_check($mode, $course_num = 0, $stage_num = 0, $lesson_num = 0, $unit_num = 0, $block_num = 0 ) {
//
// 	// パラメータ初期クリア
// 	$disk_size = 0;
//
// 	if ($mode == "course_stage") {
// 		// テンプレート情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_STUDENT_TEMP_DIR.$course_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// マテリアルのコース別情報（cssや画像など）
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR.$course_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_skill") {
// 		// 音声情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR.$course_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// 画像情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR.$course_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_chart") {
// 		// 小学校低学年
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/".$course_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// 小学校高学年
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/".$course_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// 中学
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/".$course_num, $output3, $return_var);
// 		$disk_size3 = conv_return_info($output3);
// 		// 高校
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/".$course_num, $output4, $return_var);
// 		$disk_size4 = conv_return_info($output4);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2 + $disk_size3 + $disk_size4) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_print") {
// 		// まとめプリント
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_PRINT_DIR.$course_num, $output, $return_var);
// 		$disk_size1 = conv_return_info($output);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_flash") {
// 		// レクチャー
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num, $output, $return_var);
// 		$disk_size1 = conv_return_info($output);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1) / 1024, 1 );
// 	}
// 	elseif ($mode == "problem") {
// 		// 音声情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$block_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// 画像情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$block_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "manual") {
// 		// マニュアル
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR, $output, $return_var);
// 		$disk_size1 = conv_return_info($output);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_english_word") {
// 		// 画像情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR.$course_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// 音声情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR.$course_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "course_one_point") {
// 		// 画像情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR.$course_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// 音声情報
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR.$course_num, $output2, $return_var);
// 		$disk_size2 = conv_return_info($output2);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "secret_puzzle") {
// 		// シークレットイベント
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/".$block_num, $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// MBに変換（四捨五入）
// 		$disk_size = round($disk_size1 / 1024, 1 );
// 	}
// 	elseif ($mode == "secret_stamp") {
// 		// シークレットイベント
// 		exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk_ken.sh ".KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."calendar/", $output1, $return_var);
// 		$disk_size1 = conv_return_info($output1);
// 		// MBに変換（四捨五入）
// 		$disk_size = round($disk_size1 / 1024, 1 );
// 	}
//
// 	return $disk_size;
// }
// // add oda 2017/09/25 本番バッチのアップ容量を確認可能とする
// del start 2018/03/19 yoshizawa AWSプラクティスアップデート

// /**
//  * 検証バッチのディスクサイズチェック
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @param array $output シェルコマンドの返却値
//  * @return int データサイズ（対象外またはエラーの時は0）
//  */
// function conv_return_info($output) {

// 	// パラメータ初期クリア
// 	$disk_size = 0;

// 	if (is_array($output)) {
// 		if ($output[0]) {
// 			// 取得した情報はタブ区切りの為、分割する
// 			$size_list = explode("\t", $output[0]);
// 			// 分割した情報の先頭がディスク使用サイス（KB）
// 			if (is_array($size_list)) {
// 				if ($size_list[0] != "du:") {			// 取得できない場合は"du: cannot access `/data/data/data': そのようなファイルやディレクトリはありません"を取得
// 					$disk_size = $size_list[0];
// 				}
// 			}
// 		}
// 	}

// 	return $disk_size;
// }
?>