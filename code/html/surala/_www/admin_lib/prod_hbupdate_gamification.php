<?PHP
/**
 * すらら
 *ゲーミフィケーションサーバ本番反映
 *
 * @author Azet
 */
// デプロイ開始後、jenkinsバッチ側で実行ログを記録する仕組みが追加されたのでデプロイロジックを変更します。
ignore_user_abort(true); // クライアントとの接続が切れても処理を続行する

////////////////////////////////////////////////////////////////////////////////
// 定数定義
////////////////////////////////////////////////////////////////////////////////
// Gameweb 作成用バッチ起動コマンド
$GAMEWEB_SETUP_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Gameweb_Setup/buildWithParameters'";
define("GAMEWEB_SETUP_API",$GAMEWEB_SETUP_API);
// Gamedb 本番リリースバッチ起動コマンド
$GAMEDB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Gamedb_Release/buildWithParameters'";
define("GAMEDB_RELEASE_API",$GAMEDB_RELEASE_API);
// Gameweb 本番リリースバッチ起動コマンド
$GAMEWEB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Gameweb_Release/buildWithParameters'";
define("GAMEWEB_RELEASE_API",$GAMEWEB_RELEASE_API);

// JOB判定識別子
define("GAMEWEB_SETUP_STR",'Gameweb_Setup');
define("GAMEDB_RELEASE_STR",'Gamedb_Release');
define("GAMEWEB_RELEASE_STR",'Gameweb_Release');

// 実行ログテーブル // 開発DB CONTENTS
define("T_DEPLOY_LOG","deploy_log");

// デプロイバッチ実行ログテーブル // 検証バッチDBと本番バッチDBに存在
define("T_DEPLOY_BATCH_LOG","deploy_batch_log");

////////////////////////////////////////////////////////////////////////////////
// 以下、ファンクション郡
////////////////////////////////////////////////////////////////////////////////
/**
 * スタートファンクション
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "deploy") {
		$ERROR = deploy();
	}
	// add start 2019/12/03 yoshizawa デプロイエラー対策
	else if (ACTION == "cancel"){
		// デプロイの実行ログを完了状態に更新
		$ERROR = cancel();
	}
	// add end 2019/12/03 yoshizawa デプロイエラー対策

	$html .= main_html($ERROR);

	return $html;
}

/**
 * 初期画面作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR エラー情報
 * @return string HTML
 */
function main_html($ERROR){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";
	$html .= "<div style=\"margin: 10px 10px 50px 10px; padding: 10px; border: solid 1px #000000; font-size:14px;\">\n";
	$html .= "<h1>【<b>ゲーミフィケーション</b>本番リリースのデプロイフロー】</h1>\n";

	$html .= "<h2>①Gameweb_Setup</h2>\n";
	$html .= "<ul style=\"list-style-type: none\">\n";
	$html .= "<li>ゲーミフィケーションテンプレートサーバー（srn-stg-gametemplate-1）からamiを作成</li>\n";
	$html .= "<li>　↓</li>\n";
	$html .= "<li>amiを元に本番webサーバーを1台作成（ALB-b）</li>\n";
	$html .= "</ul>\n";

	$html .= "<h2>②Gamedb_Release</h2>\n";
	$html .= "<ul style=\"list-style-type: none\">\n";
	$html .= "<li>本番バッチDB(srn-prod-batchdb-1)から本番ゲーミフィケーションDB(srnprodgamedb1)にデータ反映</li>\n";
	$html .= "</ul>\n";

	$html .= "<h2>③Gameweb_Release</h2>\n";
	$html .= "<ul style=\"list-style-type: none\">\n";
	$html .= "<li>DNSで接続先を切り替え→ ALB-bにトラフィックを流す</li>\n";
	$html .= "<li> 　↓</li>\n";
	$html .= "<li>（※DNS切り替え時間確保のために15分間スリープ）</li>\n";
	$html .= "<li> 　↓</li>\n";
	$html .= "<li>切り替え後、旧本番環境のALB-aを削除</li>\n";
	$html .= "</ul>\n";

	$html .= "<br>\n";
	$html .= "<div>\n";
	$html .= "<h3>リリース処理時間</h3>\n";
	$html .= "<ul style=\"margin-left:10px;\">\n";
	$html .= "<li>リリース処理の開始から終了までは30分～40分ほどの時間がかかる見込みです。</li>\n";
	$html .= "</ul>\n";
	$html .= "</div>\n";

	$html .= "<div>\n";
	$html .= "<h3>複数アカウントからの操作制限</h3>\n";
	$html .= "<ul style=\"margin-left:10px;\">\n";
	$html .= "<li>リリース処理実行中は他のユーザーからのリリースは不可となります。</li>\n";
	$html .= "</ul>\n";
	$html .= "</div>\n";

	$html .= "</div>\n";

	// ---------------------------------------
	// 実行中のリリース処理がないかチェック。存在する場合は後続の処理を中断。
	// ---------------------------------------

	// 途中終了している場合は終了したJOBから再開する
	// deploy_log.state 0:未実施 10:実行中 20:完了（正常終了） 30:途中終了
	$unfinish_job = '';
	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state = '30' AND deploy_mode LIKE '%Game%' ORDER BY start_time DESC LIMIT 1;"; // 途中終了
	if ($result = $cdb->query($sql)) {
		if($list = $cdb->fetch_assoc($result)){
			$manager_id = $list['manager_id'];
			$unfinish_job = $list['deploy_mode'];
		}
	}
	
	// 途中終了している場合
	if( $unfinish_job != '' ){
		// 途中終了した本人の場合
		if($manager_id == $_SESSION['myid']['id']){

			// del start 2019/12/03 yoshizawa デプロイエラー対策
			// $html .= "<p>";
			// $html .= "<h3 style=\"color:red; font-weight:bold;\">".$unfinish_job."が途中終了しています。</h3>\n";
			// $html .= "ID　：　".$manager_id."　が実行したデプロイが途中終了しています。<br>\n";
			// $html .= "前回のデプロイが完了しておりませんので「ゲーミフィケーション本番リリースを再実行」ボタンで中断したところから再度デプロイを実行してください。<br>\n";
			// $html .= "再開後に再びエラーが発生した場合にはシステム管理者に問い合わせを行ってください。<br>\n";
			// $html .= "</p>";
			// // 終了カ所から実行する
			// // ボタン押下後に実行前のメッセージを追加
			// $onclick = "onclick=\"";
			// $onclick .= "if(confirm('途中終了しているバッチからゲーミフィケーション本番リリースを再開いたします。よろしいですか？')){";
			// $onclick .= " document.deploy_form.deploy_form_button.disabled = true; "; // 連打防止
			// $onclick .= " document.deploy_form.submit(); return false; ";
			// $onclick .= "}\"";
			// $html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
			// $html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
			// $html .= "<input type=\"hidden\" name=\"unfinish_job\" value=\"".$unfinish_job."\">\n";
			// $html .= "<input type=\"button\" value=\"ゲーミフィケーション本番リリースを再実行\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
			// $html .= "</form>\n";
			// del end 2019/12/03 yoshizawa デプロイエラー対策

			// add start 2019/12/03 yoshizawa デプロイエラー対策
			$html .= "<p>";
			$html .= "<h3 style=\"color:red; font-weight:bold;\">".$unfinish_job."が途中終了しています。</h3>\n";
			$html .= "ID　：　".$manager_id."　が実行したデプロイが途中終了しています。<br>\n";
			$html .= "エラーの原因をアゼットまたはハートビーツ様にお問い合わせの上、復旧作業を行ってください。<br>\n";
			$html .= "<br>\n";
			$html .= "エラーが解消しましたら以下のボタンで途中終了を解除して再度デプロイを実行してください。<br>\n";
			$html .= "</p>";
			// ボタン押下後に実行前のメッセージを追加
			$onclick = "onclick=\"";
			$onclick .= "if(confirm('途中終了しているデプロイを終了にして次のデプロイを行える様にいたします。よろしいですか？')){";
			$onclick .= " document.deploy_form.deploy_form_button.disabled = true; "; // 連打防止
			$onclick .= " document.deploy_form.submit(); return false; ";
			$onclick .= "}\"";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"cancel\">\n";
			$html .= "<input type=\"button\" value=\"途中終了を解除する\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
			$html .= "</form>\n";
			// add end 2019/12/03 yoshizawa デプロイエラー対策

		// 本人以外の場合
		} else {

			$html .= "<p>ID　：　".$manager_id."　のリリースが途中終了しております。前回のリリースが完了してから次のリリースを行ってください。</p>";

			$onclick = "";
			$disabled = "disabled=\"disabled\"";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
			$html .= "<input type=\"button\" value=\"ゲーミフィケーション本番リリースSTART\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
			$html .= "</form>\n";

		}

	} else {

		// 画面表示時に実行中のJOBをチェック。実行中のJOBがある場合はリリース不可とする。
		$disabled = "";
		$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state IN ('0','10') AND deploy_mode LIKE '%Game%' ORDER BY start_time DESC LIMIT 1;"; // 未実施or実行中
		if ($result = $cdb->query($sql)) {
			if($list = $cdb->fetch_assoc($result)){
				$manager_id = $list['manager_id'];
				$html .= "<p>ID　：　".$manager_id."　がゲーミフィケーションリリースを実行中です。現在のリリースが完了してから次のリリースを行ってください。</p>";
				$disabled = "disabled=\"disabled\"";
			}
		}

		// ボタン押下後に実行前のメッセージを追加
		$onclick = "onclick=\"";
		$onclick .= "if(confirm('ゲーミフィケーション本番リリースを実行いたします。よろしいですか？')){";
		$onclick .= " document.deploy_form.deploy_form_button.disabled = true; "; // 連打防止
		$onclick .= " document.deploy_form.submit(); return false; ";
		$onclick .= "}\"";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
		$html .= "<input type=\"button\" value=\"ゲーミフィケーション本番リリースSTART\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
		$html .= "</form>\n";
	}

	return $html;

}

/**
 * デプロイ処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function deploy(){

	set_time_limit(0);	// 制限時間なし

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// ---------------------------------------
	// 本番バッチDBと検証バッチDBの接続オブジェクトを作成
	// ---------------------------------------
	global $L_CONTENTS_DB; // DB接続情報

//	// 検証バッチDBへ接続
//	$connect_db = new connect_db();
//	$connect_db->set_db($L_CONTENTS_DB['92']); // 検証バッチDB
//	$ERROR = $connect_db->set_connect_db();
//	if ($ERROR) {
//		echo "<br>";
//		echo "検証バッチDBに接続できません。<br>";
//		return;
//	} else {
//		$GLOBALS['sbcdb'] = $connect_db;		// DB接続情報をグローバル変数に設定
//	}
	

	// ★注意：$L_CONTENTS_DB['91']はazetの開発だとSRLBH99になっているので、
	// 強制的にSRLBH01を見に行くようにします。
	$L_CONTENTS_DB['91']['DBNAME'] = 'SRLBH01';

	// 本番バッチDBへ接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['91']); // 本番バッチDB
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		echo "<br>";
		echo "本番バッチDBに接続できません。<br>";
		return;
	} else {
		$GLOBALS['hbcdb'] = $connect_db;		// DB接続情報をグローバル変数に設定
	}

	echo "<br>";
	echo "本番リリース開始<br>";
	echo str_pad(" ",4096);


	// add start 2019/08/06 yoshizawa デプロイチェック対策
	echo "<br>";
	echo "実行中の本番バッチUPが存在しないか確認中です。<br>";
	echo str_pad(" ",4096);
	@flush();

	// 実行中のゲーミフィケーション管理の本番バッチアップがないか確認
	$sql = "SELECT * FROM gamification_mate_upd_log WHERE state = '3';"; // 本番バッチアップ実行中
	if ($result = $cdb->query($sql)) {
		if($cdb->num_rows($result) > 0){
			// メッセージを表示
			echo('<br><span style="color:red; font-weight:bold;">ゲーミフィケーション管理で実行中の本番バッチアップが存在するため、途中終了いたしました。</span><br>');
			echo str_pad(" ",4096);
			@flush();
			return;
		}
	}
	// add end 2019/08/06 yoshizawa デプロイチェック対策


	// ---------------------------------------
	// 途中終了から再開する場合、正常終了しているJOB情報を更新する
	// ---------------------------------------
	$L_JENKINS_EXEC = array();
	$L_JENKINS_EXEC[GAMEWEB_SETUP_STR] = 'true';
	$L_JENKINS_EXEC[GAMEDB_RELEASE_STR] = 'true';
	$L_JENKINS_EXEC[GAMEWEB_RELEASE_STR] = 'true';
	// del start 2019/12/03 yoshizawa デプロイエラー対策
	// if(isset($_POST['unfinish_job']) && $_POST['unfinish_job']){
	// 	// 途中終了から再開する場合、正常終了しているJOBは実行しない様にフラグをfalseにする。
	// 	$exec_flg = 'false';
	// 	if(is_array($L_JENKINS_EXEC)){
	// 		foreach($L_JENKINS_EXEC AS $job_name => $val){
	// 			if($job_name == $_POST['unfinish_job']){ $exec_flg = 'true'; }
	// 			if($exec_flg == 'false'){ $L_JENKINS_EXEC[$job_name] = 'false'; }
	// 		}
	// 	}
	// } else {
	// 	// ---------------------------------------
	// 	// 同時に別アカウントによる実行があった場合に後続の処理を中断させるため、最初にすべての実行ログを記録。
	// 	// ---------------------------------------
	// 	if(is_array($L_JENKINS_EXEC)){
	// 		foreach($L_JENKINS_EXEC AS $job_name => $val){
	// 			$INSERT_DATA = array();
	// 			$INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
	// 			$INSERT_DATA['deploy_mode'] = $job_name;
	// 			$INSERT_DATA['start_time'] = "now()";
	// 			$INSERT_DATA['state'] = "0";
	// 			$ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
	// 		}
	// 	}
	// }
	// del start 2019/12/03 yoshizawa デプロイエラー対策
	// add start 2019/12/03 yoshizawa デプロイエラー対策
	// ---------------------------------------
	// 同時に別アカウントによる実行があった場合に後続の処理を中断させるため、最初にすべての実行ログを記録。
	// ---------------------------------------
	if(is_array($L_JENKINS_EXEC)){
		foreach($L_JENKINS_EXEC AS $job_name => $val){
			$INSERT_DATA = array();
			$INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['deploy_mode'] = $job_name;
			$INSERT_DATA['start_time'] = "now()";
			$INSERT_DATA['state'] = "0";
			$ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
		}
	}
	// add end 2019/12/03 yoshizawa デプロイエラー対策

	echo "<br>";
	echo "実行中のJOBが存在しないか確認中です。<br>";
	echo str_pad(" ",4096);
	@flush();

	// 自分以外の先行デプロイがないか実行状況を確認
	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE manager_id != '".$_SESSION['myid']['id']."' AND state IN ('0','10') AND deploy_mode LIKE '%Game%';"; // 未実施or実行中
	for ($i=1; $i<=5; $i++) {

		if ($result = $cdb->query($sql)) {
			if($cdb->num_rows($result) > 0){
				// 先行デプロイが存在する場合は、自分のデプロイ実行ログを更新して終了
				$UPDATE_DATA = array();
				$UPDATE_DATA['end_time'] = "now()";
				$UPDATE_DATA['state'] = "20";
				$UPDATE_DATA['sys_bko'] = "実行中のゲーミフィケーションデプロイが存在するため、途中終了いたしました。";
				$where = " WHERE manager_id = '".$_SESSION['myid']['id']."' AND state = '0' AND deploy_mode LIKE '%Game%'";
				$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

				// メッセージを表示
				echo('<br>実行中のゲーミフィケーションデプロイが存在するため、途中終了いたしました。<br>');
				echo str_pad(" ",4096);
				@flush();
				return;
			}
		}
		echo "・";
		echo str_pad(" ",4096);
		@flush();
		sleep(2);

	}

	// ---------------------------------------
	// 順次jenkinsバッチを実行
	// ---------------------------------------

	// 開始前にdeploy_batch_logを無効化する(本番バッチDB／検証バッチDB)
	$ERROR = del_deploy_batch_log();
	
	// add start hasegawa 2019/07/26 JOB同時実行防止措置
	if ($ERROR || select_deploy_batch_log()) { 
		echo "<br>前処理でエラーが発生した為、デプロイを実行せずに終了します。<br>";
		return;
	}
	// add end hasegawa 2019/07/26

	// -----------------------------------
	// Coreweb_Setup ---------------------
	// -----------------------------------
	if($L_JENKINS_EXEC[GAMEWEB_SETUP_STR] == 'true'){
		$job_result = kick_jenkins_api(GAMEWEB_SETUP_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// -----------------------------------
	// Coredb_Release --------------------
	// -----------------------------------
	if($L_JENKINS_EXEC[GAMEDB_RELEASE_STR] == 'true'){
		$job_result = kick_jenkins_api(GAMEDB_RELEASE_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// -----------------------------------
	// Coreweb_Release -------------------
	// -----------------------------------
	if($L_JENKINS_EXEC[GAMEWEB_RELEASE_STR] == 'true'){
		$job_result = kick_jenkins_api(GAMEWEB_RELEASE_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// 終了時にdeploy_batch_logを無効化する(本番バッチDB／検証バッチDB)
	$ERROR = del_deploy_batch_log();

	echo "<br>";
	echo "ゲーミフィケーション本番リリース完了<br>";
	echo str_pad(" ",4096);

}

/**
 * jenkinsJOB開始／実行ログの記録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $mode 実行JOBの識別
 * @return string $result 実行結果
 */
function kick_jenkins_api($mode){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	echo "<br>";
	echo $mode." START<br>";
	echo str_pad(" ",4096);
	@flush();

	if ( $mode ) {
		$UPDATE_DATA = array();
		$UPDATE_DATA['start_time'] = "now()";
		$UPDATE_DATA['state'] = "10";
		$where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND deploy_mode = '".$mode."' AND state IN ('0','30')"; // 未実施or途中終了
		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
	}


	$job_result = '';
	$job_result = exec_jenkins_job($mode); // jenkins jobを実行

	if($job_result !== 'SUCCESS'){

		$message = '';
		// JOBエラー
		if($job_result === 'FAILURE'){
			$message = $mode."でエラーが発生したため途中終了します。";

		// JOBの実行が未確認
		} else {
			$message = $mode."の実行を確認できないため途中終了します。";

		}
		echo "<br>";
		echo $message;
		echo str_pad(" ",4096);
		@flush();

		// 処理の終了時にdeploy_logの状態を途中終了に更新する
		$UPDATE_DATA = array();
		$UPDATE_DATA['end_time'] = "now()";
		$UPDATE_DATA['state'] = "30"; // 途中終了
		$UPDATE_DATA['sys_bko'] = $message;
		$where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND state IN ('0','10') AND deploy_mode LIKE '%Game%' "; // 未実施or実行中
		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

		// 終了時にdeploy_batch_logを無効化する
		// $ERROR = del_deploy_batch_log();	// >>> del 2018/09/19 yoshizawa

		return $job_result;

	}

	if ( $mode ) {
		$UPDATE_DATA = array();
		$UPDATE_DATA['end_time'] = "now()";
		$UPDATE_DATA['state'] = "20";
		$where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND deploy_mode = '".$mode."' AND state IN ('10','30')"; // 実行中or途中終了

		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
	}
	// <<< add 2018/09/19 yoshizawa

	echo "<br>";
	echo $mode." END<br>";
	echo "<br>";
	echo str_pad(" ",4096);
	@flush();

	return $job_result;

}

/**
 * jenkinsJOB開始／実行状況の監視
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $mode 実行JOBの識別
 * @return string $result 実行結果
 */
function exec_jenkins_job($mode){

	// 必須パラメータチェック
	if(!$mode){
		echo "<br>";
		echo "API実行モードが取得できません。<br>";
		echo str_pad(" ",4096);
		@flush();
		return;
	}

	// jenkinsAPIを実行
	if ($mode == GAMEWEB_SETUP_STR){
		$ERROR = exec(GAMEWEB_SETUP_API,$ERROR,$RETURN);
		// $vpc = "stg";

	} else if ($mode == GAMEDB_RELEASE_STR) {
		$ERROR = exec(GAMEDB_RELEASE_API,$ERROR,$RETURN);
		// $vpc = "prod";

	} else if ($mode == GAMEWEB_RELEASE_STR) {
		$ERROR = exec(GAMEWEB_RELEASE_API,$ERROR,$RETURN);
		// $vpc = "prod";

	}

	// 実行中のチェックロジックをjenkinsコマンドからバッチDBのレコード参照に変更
	// 本番バッチDBへ接続オブジェクトを設定(Gameはすべてprodで実行される)
	$bcdb = $GLOBALS['hbcdb'];

	// JOB実行ログを取得
	$sql = "SELECT * FROM ".T_DEPLOY_BATCH_LOG." WHERE deploy_mode LIKE '".$mode."' AND mk_flg = '0' ORDER BY regist_time DESC LIMIT 1;";

	// jenkinsを起動後、開始と終了をチェック
	// ただし、実行JOBが終了後に次のJOBの実行ログが取得できるできるまでには数秒のタイムラグが存在するので、
	// 完全に完了したかどうかは設定秒NULLが続いたかどうかで判断する。※MAX1時間でタイムアウト
	$exec_flg = true;
	$null_count = 0;
	// for ($i=1; $i<=900; $i++) {		// del 2019/04/18 yoshizawa 課題要望No752
	for ($i=1; $i<=1800; $i++) {		// add 2019/04/18 yoshizawa 課題要望No752

		$state_mode = '';
		$job_result = '';
		if ($result = $bcdb->query($sql)) {
			while ($list = $bcdb->fetch_assoc($result)) {
				// jenkinsの実行ログを取得
				$state_mode = $list['state_mode'];
				$job_result = $list['result'];

			}
		}

		if($state_mode == 'start'){
			// 実行JOBがあればカウントリセット
			$null_count = 0;

		} else if($state_mode == 'end' && $job_result == 'SUCCESS'){
			// 終了ログを確認した時点で終了
			break;

		} else {
			// 実行JOBがなければカウントアップ
			$null_count++;

		}

		// JOBが失敗している場合は途中終了。
		if($job_result == 'FAILURE'){ break; }

		// NULLが10回以上続いた場合はJOBの実行が確認できないため、未実行と判断する。
		if($null_count > 150){ $job_result = 'UNCONFIRMED'; break; }	// add 2019/04/18 yoshizawa 課題要望No752

		echo "・";
		echo str_pad(" ",4096);
		@flush();
		sleep(2);

	}

	return $job_result;

}

/**
 * deploy_batch_logテーブルの論理削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array $ERROR エラー情報
 */
function del_deploy_batch_log(){

	// 本番バッチDB接続オブジェクト
	$hbcdb = $GLOBALS['hbcdb'];

	$UPDATE_DATA = array();
	$UPDATE_DATA['mk_flg'] = "1";
	$UPDATE_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
	$UPDATE_DATA['mk_date'] = "now()";
	$where = " WHERE mk_flg='0' AND deploy_mode LIKE '%Game%' ";

	// 本番バッチDBのバッチログテーブルを無効化
	$ERROR = $hbcdb->update(T_DEPLOY_BATCH_LOG,$UPDATE_DATA,$where);

	return $ERROR;

}
// add function hasegawa 2019/07/26 JOB同時実行防止措置
/**
 * deploy_batch_logから有効行が存在するかチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return bool
 */
function select_deploy_batch_log(){

	// 本番バッチDB接続オブジェクト
	$hbcdb = $GLOBALS['hbcdb'];
	
	$sql = "SELECT * FROM ".T_DEPLOY_BATCH_LOG." WHERE mk_flg='0' AND deploy_mode LIKE '%Game%' ";
	if ($result = $hbcdb->query($sql)) {
		if($hbcdb->num_rows($result) > 0){
			return true;
		}
	}

	return false;

}

// add start 2019/12/03 yoshizawa デプロイエラー対策
/**
 * deploy_batch_logを終了状態にする
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function cancel(){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$UPDATE_DATA = array();
	$UPDATE_DATA['state'] = "20";
	$UPDATE_DATA['end_time'] = "now()";
	$where = "WHERE state != '20' AND deploy_mode LIKE '%Game%' "; // 完了以外
	$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

}
// add end 2019/12/03 yoshizawa デプロイエラー対策
