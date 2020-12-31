<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　コース作成
 *
 * @author Azet
 */
// add start 2018/09/14 yoshizawa デプロイエラーチェック実装
// デプロイ開始後、jenkinsバッチ側で実行ログを記録する仕組みが追加されたのでデプロイロジックを変更します。

ignore_user_abort(true); // クライアントとの接続が切れても処理を続行する

// デバッグモード
// true:ON ダミージョブを実行します false:OFF 正規のデプロイジョブを実行します。
// ダミージョブはデバッグ用にハートビーツ様に作成いただいたジョブとなります（●●●_dummy）。実行すると、内部jobを実行せずjenkins管理画面上に進捗を表示して終了となります。
define("DEBUG_MODE",'false');

////////////////////////////////////////////////////////////////////////////////
// 定数定義
////////////////////////////////////////////////////////////////////////////////
// Coreweb 作成用バッチ起動コマンド
if(DEBUG_MODE == 'true'){
	$COREWEB_SETUP_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.stg.surala.jp:8080/job/Coreweb_Setup_dummy/buildWithParameters'";	// デバッグ用のダミージョブ
} else {
	$COREWEB_SETUP_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.stg.surala.jp:8080/job/Coreweb_Setup/buildWithParameters'";		// 本番ジョブ
}
define("COREWEB_SETUP_API",$COREWEB_SETUP_API);

// Coredb 本番リリースバッチ起動コマンド
if(DEBUG_MODE == 'true'){
	$COREDB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coredb_Release_dummy/buildWithParameters'";	// デバッグ用のダミージョブ
} else {
	$COREDB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coredb_Release/buildWithParameters'";		// 本番ジョブ
}
define("COREDB_RELEASE_API",$COREDB_RELEASE_API);

// Coreweb 本番リリースバッチ起動コマンド
if(DEBUG_MODE == 'true'){
	$COREWEB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coreweb_Release_dummy/buildWithParameters'";	// デバッグ用のダミージョブ
} else {
	$COREWEB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coreweb_Release/buildWithParameters'";			// 本番ジョブ
}
define("COREWEB_RELEASE_API",$COREWEB_RELEASE_API);

// JOB判定識別子
if(DEBUG_MODE == 'true'){
	define("COREWEB_SETUP_STR",'Coreweb_Setup_dummy');
	define("COREDB_RELEASE_STR",'Coredb_Release_dummy');
	define("COREWEB_RELEASE_STR",'Coreweb_Release_dummy');
} else {
	define("COREWEB_SETUP_STR",'Coreweb_Setup');
	define("COREDB_RELEASE_STR",'Coredb_Release');
	define("COREWEB_RELEASE_STR",'Coreweb_Release');
}

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

	if(DEBUG_MODE == 'true'){
		echo "<style> #maintenance{ margin:5px; padding:10px; background-color:#ffffff; color:#000000; font-size:30px; font-family:メイリオ; }</style>";
		echo "<div id='maintenance'>デバッグモードに切り替わっています。<br></div>";
		echo('■■■COREWEB_SETUP_API=>'.COREWEB_SETUP_API."<br />\n");
		echo('■■■COREDB_RELEASE_API=>'.COREDB_RELEASE_API."<br />\n");
		echo('■■■COREWEB_RELEASE_API=>'.COREWEB_RELEASE_API."<br />\n");
	}

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
	$html .= "<h1>【本番リリースのデプロイフロー】</h1>\n";

	$html .= "<h2>①Coreweb_Setup</h2>\n";
	$html .= "<ul style=\"list-style-type: none\">\n";
	$html .= "<li>テンプレートサーバー（srn-stg-template-1）からamiを作成</li>\n";
	$html .= "<li>　↓</li>\n";
	$html .= "<li>amiを元に本番webサーバーを4台作成（ALB-b）</li>\n";
	$html .= "</ul>\n";

	$html .= "<h2>②Coredb_Release</h2>\n";
	$html .= "<ul style=\"list-style-type: none\">\n";
	$html .= "<li>本番バッチDB(srn-prod-batchdb-1)から本番分散DB(srn-prod-coredb-1～8)にデータ反映</li>\n";
	$html .= "</ul>\n";

	$html .= "<h2>③Coreweb_Release</h2>\n";
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
// 	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state = '30' ORDER BY start_time DESC LIMIT 1;"; // 途中終了	// upd hasegawa 2019/07/24 生徒TOP改修
	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state = '30' AND deploy_mode LIKE '%Core%' ORDER BY start_time DESC LIMIT 1;"; // 途中終了
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
			// $html .= "前回のデプロイが完了しておりませんので「本番リリースを再実行」ボタンで中断したところから再度デプロイを実行してください。<br>\n";
			// $html .= "再開後に再びエラーが発生した場合にはシステム管理者に問い合わせを行ってください。<br>\n";
			// $html .= "</p>";
			// // 終了カ所から実行する
			// // ボタン押下後に実行前のメッセージを追加
			// $onclick = "onclick=\"";
			// $onclick .= "if(confirm('途中終了しているバッチから本番リリースを再開いたします。よろしいですか？')){";
			// $onclick .= " document.deploy_form.deploy_form_button.disabled = true; "; // 連打防止
			// $onclick .= " document.deploy_form.submit(); return false; ";
			// $onclick .= "}\"";
			// $html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
			// $html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
			// $html .= "<input type=\"hidden\" name=\"unfinish_job\" value=\"".$unfinish_job."\">\n";
			// $html .= "<input type=\"button\" value=\"本番リリースを再実行\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
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
			// add start 2019/12/03 yoshizawa デプロイエラー対策

		// 本人以外の場合
		} else {

			$html .= "<p>ID　：　".$manager_id."　のリリースが途中終了しております。前回のリリースが完了してから次のリリースを行ってください。</p>";

			$onclick = "";
			$disabled = "disabled=\"disabled\"";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
			$html .= "<input type=\"button\" value=\"本番リリースSTART\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
			$html .= "</form>\n";

		}

	} else {

		// 画面表示時に実行中のJOBをチェック。実行中のJOBがある場合はリリース不可とする。
		$disabled = "";
		// $sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state IN ('0','10') ORDER BY start_time DESC LIMIT 1;"; // 未実施or実行中	// upd hasegawa 2019/07/24 生徒TOP改修
		$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state IN ('0','10') AND deploy_mode LIKE '%Core%' ORDER BY start_time DESC LIMIT 1;"; // 未実施or実行中
		if ($result = $cdb->query($sql)) {
			if($list = $cdb->fetch_assoc($result)){
				$manager_id = $list['manager_id'];
				$html .= "<p>ID　：　".$manager_id."　がリリースを実行中です。現在のリリースが完了してから次のリリースを行ってください。</p>";
				$disabled = "disabled=\"disabled\"";
			}
		}

		// ボタン押下後に実行前のメッセージを追加
		$onclick = "onclick=\"";
		$onclick .= "if(confirm('本番リリースを実行いたします。よろしいですか？')){";
		$onclick .= " document.deploy_form.deploy_form_button.disabled = true; "; // 連打防止
		$onclick .= " document.deploy_form.submit(); return false; ";
		$onclick .= "}\"";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"deploy_form\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
		$html .= "<input type=\"button\" value=\"本番リリースSTART\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
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

	$ERROR = null;	// add hasegawa 2019/07/26 JOB同時実行防止措置

	// add 2019/07/26 yoshizawa
	// 10.3.11.100から接続した場合は接続DBがSRLBS99／SRLBH99になっているので切り替えます。
	if(DEBUG_MODE == 'true'){
		$L_CONTENTS_DB['92']['DBNAME'] = 'SRLBS01';
		$L_CONTENTS_DB['91']['DBNAME'] = 'SRLBH01';
	}

	// 検証バッチDBへ接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']); // 検証バッチDB
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		echo "<br>";
		echo "検証バッチDBに接続できません。<br>";
		return;
	} else {
		$GLOBALS['sbcdb'] = $connect_db;		// DB接続情報をグローバル変数に設定
	}
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

	// 実行中のプラクティスステージ管理の本番バッチアップがないか確認
	$sql = "SELECT * FROM mate_upd_log WHERE state = '3';"; // 本番バッチアップ実行中
	if ($result = $cdb->query($sql)) {
		if($cdb->num_rows($result) > 0){
			// メッセージを表示
			echo('<br><span style="color:red; font-weight:bold;">プラクティスステージ管理で実行中の本番バッチアップが存在するため、途中終了いたしました。</span><br>');
			echo str_pad(" ",4096);
			@flush();
			return;
		}
	}
	// 実行中のテスト用プラクティス管理の本番バッチアップがないか確認
	$sql = "SELECT * FROM test_mate_upd_log WHERE state = '3';"; // 本番バッチアップ実行中
	if ($result = $cdb->query($sql)) {
		if($cdb->num_rows($result) > 0){
			// メッセージを表示
			echo('<br><span style="color:red; font-weight:bold;">テスト用プラクティス管理で実行中の本番バッチアップが存在するため、途中終了いたしました。</span><br>');
			echo str_pad(" ",4096);
			@flush();
			return;
		}
	}
	// 実行中の速習用プラクティス管理の本番バッチアップがないか確認
	$sql = "SELECT * FROM package_mate_upd_log WHERE state = '3';"; // 本番バッチアップ実行中
	if ($result = $cdb->query($sql)) {
		if($cdb->num_rows($result) > 0){
			// メッセージを表示
			echo('<br><span style="color:red; font-weight:bold;">速習用プラクティス管理で実行中の本番バッチアップが存在するため、途中終了いたしました。</span><br>');
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
	$L_JENKINS_EXEC[COREWEB_SETUP_STR] = 'true';
	$L_JENKINS_EXEC[COREDB_RELEASE_STR] = 'true';
	$L_JENKINS_EXEC[COREWEB_RELEASE_STR] = 'true';
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
	// del end 2019/12/03 yoshizawa デプロイエラー対策
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
	// $sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE manager_id != '".$_SESSION['myid']['id']."' AND state IN ('0','10');"; // 未実施or実行中	// upd hasegawa 2019/07/24 生徒TOP改修
	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE manager_id != '".$_SESSION['myid']['id']."' AND state IN ('0','10') AND deploy_mode LIKE '%Core%';"; // 未実施or実行中
	for ($i=1; $i<=5; $i++) {

		if ($result = $cdb->query($sql)) {
			if($cdb->num_rows($result) > 0){
				// 先行デプロイが存在する場合は、自分のデプロイ実行ログを更新して終了
				$UPDATE_DATA = array();
				$UPDATE_DATA['end_time'] = "now()";
				$UPDATE_DATA['state'] = "20";
				$UPDATE_DATA['sys_bko'] = "実行中のデプロイが存在するため、途中終了いたしました。";
				// $where = " WHERE manager_id = '".$_SESSION['myid']['id']."' AND state = '0'";// upd hasegawa 2019/07/24 生徒TOP改修
				$where = " WHERE manager_id = '".$_SESSION['myid']['id']."' AND state = '0' AND deploy_mode LIKE '%Core%'";
				$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

				// メッセージを表示
				echo('<br>実行中のデプロイが存在するため、途中終了いたしました。<br>');
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
	
	if($L_JENKINS_EXEC[COREWEB_SETUP_STR] == 'true'){
		$job_result = kick_jenkins_api(COREWEB_SETUP_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// -----------------------------------
	// Coredb_Release --------------------
	// -----------------------------------
	if($L_JENKINS_EXEC[COREDB_RELEASE_STR] == 'true'){
		$job_result = kick_jenkins_api(COREDB_RELEASE_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// -----------------------------------
	// Coreweb_Release -------------------
	// -----------------------------------
	if($L_JENKINS_EXEC[COREWEB_RELEASE_STR] == 'true'){
		$job_result = kick_jenkins_api(COREWEB_RELEASE_STR);
		if($job_result !== 'SUCCESS'){ return; }
	}

	// 終了時にdeploy_batch_logを無効化する(本番バッチDB／検証バッチDB)
	$ERROR = del_deploy_batch_log();

	echo "<br>";
	echo "本番リリース完了<br>";
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

	// >>> del 2018/09/19 yoshizawa
	// // 実行ログを記録
	// $INSERT_DATA = array();
	// $INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
	// $INSERT_DATA['deploy_mode'] = $mode;
	// $INSERT_DATA['start_time'] = "now()";
	// $INSERT_DATA['state'] = "0";
	// $ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
	// //登録idを取得
	// $deploy_id = $cdb->insert_id();
	// <<<

	// >>> add 2018/09/19 yoshizawa
	if ( $mode ) {
		$UPDATE_DATA = array();
		$UPDATE_DATA['start_time'] = "now()";
		$UPDATE_DATA['state'] = "10";
		$where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND deploy_mode = '".$mode."' AND state IN ('0','30')"; // 未実施or途中終了
		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
	}
	// <<< add 2018/09/19 yoshizawa


	$job_result = '';
	$job_result = exec_jenkins_job($mode); // jenkins jobを実行
// $result = 'SUCCESS';

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
		// $where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND state IN ('0','10')"; // 未実施or実行中	// upd hasegawa 2019/07/24 生徒TOP改修
		$where = "WHERE manager_id = '".$_SESSION['myid']['id']."' AND state IN ('0','10') AND deploy_mode LIKE '%Core%'"; // 未実施or実行中
		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

		// 終了時にdeploy_batch_logを無効化する
		// $ERROR = del_deploy_batch_log();	// >>> del 2018/09/19 yoshizawa

		return $job_result;

	}

	// >>> del 2018/09/19 yoshizawa
	// if ( $deploy_id ) {
	// 	$UPDATE_DATA['end_time'] = "now()";
	// 	$UPDATE_DATA['state'] = "1";
	// 	$where = " WHERE deploy_id=".$deploy_id;
	// 	$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
	// }
	// <<< del 2018/09/19 yoshizawa

	// >>> add 2018/09/19 yoshizawa
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
	if ($mode == COREWEB_SETUP_STR){
		$ERROR = exec(COREWEB_SETUP_API,$ERROR,$RETURN);
		// $vpc = "stg";

	} else if ($mode == COREDB_RELEASE_STR) {
		$ERROR = exec(COREDB_RELEASE_API,$ERROR,$RETURN);
		// $vpc = "prod";

	} else if ($mode == COREWEB_RELEASE_STR) {
		$ERROR = exec(COREWEB_RELEASE_API,$ERROR,$RETURN);
		// $vpc = "prod";

	}

	// 実行中のチェックロジックをjenkinsコマンドからバッチDBのレコード参照に変更
	// 本番/検証バッチDBに接続
	if ($mode == COREWEB_SETUP_STR){
		// 検証バッチDBへ接続オブジェクトを設定
		$bcdb = $GLOBALS['sbcdb'];

	} else if ($mode == COREDB_RELEASE_STR || $mode == COREWEB_RELEASE_STR){
		// 本番バッチDBへ接続オブジェクトを設定
		$bcdb = $GLOBALS['hbcdb'];

	}

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
		// if($null_count > 10){ $job_result = 'UNCONFIRMED'; break; }	// del 2019/04/18 yoshizawa 課題要望No752
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
	// 検証バッチDB接続オブジェクト
	$sbcdb = $GLOBALS['sbcdb'];

	$UPDATE_DATA = array();
	$UPDATE_DATA['mk_flg'] = "1";
	$UPDATE_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
	$UPDATE_DATA['mk_date'] = "now()";
	// $where = " WHERE mk_flg='0' ";// upd hasegawa 2019/07/24 生徒TOP改修
	$where = " WHERE mk_flg='0' AND deploy_mode LIKE '%Core%' ";
	
	// 本番バッチDBのバッチログテーブルを無効化
	$ERROR = $hbcdb->update(T_DEPLOY_BATCH_LOG,$UPDATE_DATA,$where);
	// 検証バッチDBのバッチログテーブルを無効化
	$ERROR = $sbcdb->update(T_DEPLOY_BATCH_LOG,$UPDATE_DATA,$where);

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
	// 検証バッチDB接続オブジェクト
	$sbcdb = $GLOBALS['sbcdb'];
	
	// $sql = "SELECT * FROM ".T_DEPLOY_BATCH_LOG." WHERE mk_flg='0' ";	// del 2019/07/26 yoshizawa
	$sql = "SELECT * FROM ".T_DEPLOY_BATCH_LOG." WHERE mk_flg='0' AND deploy_mode LIKE '%Core%' ";	// add 2019/07/26 yoshizawa
	if ($result = $sbcdb->query($sql)) {
		if($sbcdb->num_rows($result) > 0){
			return true;
		}
	}
	if ($result = $hbcdb->query($sql)) {
		if($hbcdb->num_rows($result) > 0){
			return true;
		}
	}

	return false;

}
// add end 2018/09/14 yoshizawa デプロイエラーチェック実装

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
	$where = "WHERE state != '20' AND deploy_mode LIKE '%Core%' "; // 完了以外
	$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);

}
// add end 2019/12/03 yoshizawa デプロイエラー対策

// del start 2018/09/14 yoshizawa デプロイエラーチェック実装
// // 監視APIから返却される配列
// // Array
// // (
// //     [0] => {
// //     [1] =>   "name" : "Coreweb_Release",
// //     [2] =>   "color" : "blue_anime"
// //     [3] => }
// // )
// //
// // Coreweb_Setup
// // Coredb_Release
// // Coreweb_Release
// // DNS切り替え→15分スリープ→旧本番環境を削除
//
// ignore_user_abort(true); // クライアントとの接続が切れても処理を続行する
//
// // -----------------------------------
// // 定数定義 ----------------------------
// // -----------------------------------
// // jenkinsJOBの実行状況を取得するAPI起動コマンド（本番環境）
// // $PROD_COMPUTER_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'batch.prod.surala.jp:8080/computer/api/json' -d pretty=true -d 'tree=computer[executors[currentExecutable[fullDisplayName]]]'";
// // >>> update 2018/08/30 yoshizawa 1つ前のJOBでエラーが発生していると'red_anime'となって実行状況を取得できないので、grep条件を変更する。
// // $PROD_COMPUTER_API = "curl -s -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' \"batch.prod.surala.jp:8080/api/json\" -d pretty=true -d \"tree=jobs[name,color]\" |grep blue_anime -B1";
// $PROD_COMPUTER_API = "curl -s -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' \"batch.prod.surala.jp:8080/api/json\" -d pretty=true -d \"tree=jobs[name,color]\" |grep _anime -B1";
// // <<<
// define("PROD_COMPUTER_API",$PROD_COMPUTER_API);
//
// // jenkinsJOBの実行状況を取得するAPI起動コマンド（検証環境）
// // $STG_COMPUTER_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'batch.stg.surala.jp:8080/computer/api/json' -d pretty=true -d 'tree=computer[executors[currentExecutable[fullDisplayName]]]'";
// // >>> update 2018/08/30 yoshizawa 1つ前のJOBでエラーが発生していると'red_anime'となって実行状況を取得できないので、grep条件を変更する。
// // $STG_COMPUTER_API = "curl -s -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' \"batch.stg.surala.jp:8080/api/json\" -d pretty=true -d \"tree=jobs[name,color]\" |grep blue_anime -B1";
// $STG_COMPUTER_API = "curl -s -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' \"batch.stg.surala.jp:8080/api/json\" -d pretty=true -d \"tree=jobs[name,color]\" |grep _anime -B1";
// // <<<
// define("STG_COMPUTER_API",$STG_COMPUTER_API);
//
// // Coreweb 作成用バッチ起動コマンド
// $COREWEB_SETUP_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.stg.surala.jp:8080/job/Coreweb_Setup/buildWithParameters'";
// define("COREWEB_SETUP_API",$COREWEB_SETUP_API);
//
// // Coredb 本番リリースバッチ起動コマンド
// $COREDB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coredb_Release/buildWithParameters'";
// define("COREDB_RELEASE_API",$COREDB_RELEASE_API);
//
// // Coreweb 本番リリースバッチ起動コマンド
// $COREWEB_RELEASE_API = "curl -X POST --user 'batchadmin:Srpw4pJ0fW8cgKSYQXE5' 'http://batch.prod.surala.jp:8080/job/Coreweb_Release/buildWithParameters'";
// define("COREWEB_RELEASE_API",$COREWEB_RELEASE_API);
//
// // 実行ログテーブル
// define("T_DEPLOY_LOG","deploy_log");
//
//
// /**
//  * HTMLを作成する機能
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @return string HTML
//  */
// function start() {
//
// 	if (ACTION == "deploy") {
// 		$ERROR = deploy();
// 	}
//
// 	$html .= main_html($ERROR);
//
// 	return $html;
// }
//
//
// function main_html($ERROR){
//
// 	// DB接続オブジェクト
// 	$cdb = $GLOBALS['cdb'];
//
// //pre(get_deploy_progress('prod'));
// 	$html = "";
// 	$html .= "<div style=\"margin: 10px 10px 50px 10px; padding: 10px; border: solid 1px #000000; font-size:14px;\">\n";
// 	$html .= "<h1>【本番リリースのデプロイフロー】</h1>\n";
//
// 	$html .= "<h2>①Coreweb_Setup</h2>\n";
// 	$html .= "<ul style=\"list-style-type: none\">\n";
// 	$html .= "<li>テンプレートサーバー（srn-stg-template-1）からamiを作成</li>\n";
// 	$html .= "<li>　↓</li>\n";
// 	$html .= "<li>amiを元に本番webサーバーを4台作成（ALB-b）</li>\n";
// 	$html .= "</ul>\n";
//
// 	$html .= "<h2>②Coredb_Release</h2>\n";
// 	$html .= "<ul style=\"list-style-type: none\">\n";
// 	$html .= "<li>本番バッチDB(srn-prod-batchdb-1)から本番分散DB(srn-prod-coredb-1～8)にデータ反映</li>\n";
// //	$html .= "<li> 　↓</li>\n";
// //	$html .= "<li>（※DB反映時間確保のために10分間スリープ）</li>\n";
// 	$html .= "</ul>\n";
//
// 	$html .= "<h2>③Coreweb_Release</h2>\n";
// 	$html .= "<ul style=\"list-style-type: none\">\n";
// 	$html .= "<li>DNSで接続先を切り替え→ ALB-bにトラフィックを流す</li>\n";
// 	$html .= "<li> 　↓</li>\n";
// 	$html .= "<li>（※DNS切り替え時間確保のために15分間スリープ）</li>\n";
// 	$html .= "<li> 　↓</li>\n";
// 	$html .= "<li>切り替え後、旧本番環境のALB-aを削除</li>\n";
// 	$html .= "</ul>\n";
//
// 	$html .= "<br>\n";
// 	$html .= "<div>\n";
// 	$html .= "<h3>リリース処理時間</h3>\n";
// 	$html .= "<ul style=\"margin-left:10px;\">\n";
// 	$html .= "<li>リリース処理の開始から終了までは30分～40分ほどの時間がかかる見込みです。</li>\n";
// 	$html .= "</ul>\n";
// 	$html .= "</div>\n";
//
// 	$html .= "<div>\n";
// 	$html .= "<h3>複数アカウントからの操作制限</h3>\n";
// 	$html .= "<ul style=\"margin-left:10px;\">\n";
// 	$html .= "<li>リリース処理実行中は他のユーザーからのリリースは不可となります。</li>\n";
// //	$html .= "<li>リリース処理時間を確保するため、最後のリリースから20分間はリリース不可とします。</li>\n";
// 	$html .= "</ul>\n";
// 	$html .= "</div>\n";
//
// 	$html .= "</div>\n";
//
// 	// -----------------------------------
// 	// 実行中のリリース処理がないかチェック。 -------------
// 	// 存在する場合は実行不可とする。 ---------------
// 	// -----------------------------------
//
// 	$disabled = "";
//
// 	// 画面表示時に実行中のJOBをチェック。実行中のJOBがある場合はリリース不可とする。
// 	$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE state = '0' ORDER BY start_time DESC LIMIT 1;";
// 	if ($result = $cdb->query($sql)) {
// 		if($list = $cdb->fetch_assoc($result)){
// 			$html .= "<p>【".$list['manager_id']."】がリリースを実行中です。現在のリリースが完了してから次のリリースを行ってください。</p>";
// 			$disabled = "disabled=\"disabled\"";
// 		}
// 	}
//
// //	if(!$disabled){
// //		// 前回のリリースからDNSが完全に切り替わるまで15分スリープしているので、最後のリリースから20分はリリース不可とします。
// //		$now_time_ago = date("Y-m-d H:i:s",strtotime("-20 minute"));
// //		$sql = "SELECT * FROM ".T_DEPLOY_LOG." WHERE start_time > '".$now_time_ago."' ORDER BY start_time DESC LIMIT 1;";
// //		if ($result = $cdb->query($sql)) {
// //			if($list = $cdb->fetch_assoc($result)){
// //				$start_time_after = date("Y-m-d H:i:s",strtotime($list['start_time'] . "+20 minute"));
// //				$html .= "<p>";
// //				$html .= "前回の本番リリースが実行中のため、完了するまでは次のリリースができません。<br>";
// //				$html .= "次回リリース可能時間　：　".$start_time_after."";
// //				$html .= "</p>";
// //				$disabled = "disabled=\"disabled\"";
// //			}
// //		}
// //	}
//
// 	$onclick = "onclick=\"document.deploy_form.deploy_form_button.disabled = true; document.deploy_form.submit(); return false;\""; // 連打防止
// 	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"deploy_form\">\n";
// 	$html .= "<input type=\"hidden\" name=\"action\" value=\"deploy\">\n";
// 	$html .= "<input type=\"button\" value=\"本番リリースSTART\" name=\"deploy_form_button\" ".$onclick." ".$disabled.">\n";
// 	$html .= "</form>\n";
//
// 	return $html;
//
// }
//
// function deploy(){
//
// 	// DB接続オブジェクト
// 	$cdb = $GLOBALS['cdb'];
//
// 	set_time_limit(0);	// 制限時間なし
//
// 	echo "<br>";
// 	echo "本番リリース開始<br>";
// 	echo str_pad(" ",4096);
//
// 	echo "<br>";
// 	echo "実行中のJOBが存在しないか確認中です。<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
// 	$job_name = "";
// 	$error_flg = false;
//
// 	// 先行JOBがないか実行状況を確認
// 	for ($i=1; $i<=5; $i++) {
//
// 		// prodの実行状況を取得
// 		list($error_flg,$job_name) = check_deploy_running('prod');
// 		if($error_flg === true){
// 			echo('<br>実行中のJOBが存在するため、リリースを開始できません。<br>');
// 			echo str_pad(" ",4096);
// 			@flush();
// 			return;
// 		}
//
// 		// stgの実行状況を取得
// 		list($error_flg,$job_name) = check_deploy_running('stg');
// 		if($error_flg === true){
// 			echo('<br>実行中のJOBが存在するため、リリースを開始できません。<br>');
// 			echo str_pad(" ",4096);
// 			@flush();
// 			return;
// 		}
//
// 		echo "・";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		sleep(2);
//
// 	}
//
//
// 	// -----------------------------------
// 	// Coreweb_Setup ---------------------
// 	// -----------------------------------
//
// 	echo "<br>";
// 	echo "Coreweb_Setup START<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
// 	// 実行ログを記録
// 	$INSERT_DATA = array();
// 	$INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
// 	$INSERT_DATA['deploy_mode'] = "Coreweb_Setup";
// 	$INSERT_DATA['start_time'] = "now()";
// 	$INSERT_DATA['state'] = "0";
// 	$ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
// 	//登録idを取得
// 	$deploy_id = $cdb->insert_id();
//
// 	$exec_flg = jenkins_api_exec('Coreweb_Setup'); // jenkinsAPIを実行
//
// 	if($exec_flg === false){
// 		echo "<br>";
// 		echo "JOBの実行を確認できないため途中終了します。";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		return;
// 	}
//
// 	if ( $deploy_id ) {
// 		$UPDATE_DATA['end_time'] = "now()";
// 		$UPDATE_DATA['state'] = "1";
// 		$where = " WHERE deploy_id=".$deploy_id;
// 		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
// 	}
//
// 	echo "<br>";
// 	echo "Coreweb_Setup END<br>";
// 	echo "<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
//
// 	// -----------------------------------
// 	// Coredb_Release --------------------
// 	// -----------------------------------
//
// 	echo "<br>";
// 	echo "Coredb_Release START<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
// 	// 実行ログを記録
// 	$INSERT_DATA = array();
// 	$INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
// 	$INSERT_DATA['deploy_mode'] = "Coredb_Release";
// 	$INSERT_DATA['start_time'] = "now()";
// 	$INSERT_DATA['state'] = "0";
// 	$ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
// 	//登録idを取得
// 	$deploy_id = $cdb->insert_id();
//
// 	$exec_flg = jenkins_api_exec('Coredb_Release'); // jenkinsAPIを実行
//
// //	$ERROR = exec(COREDB_RELEASE_API,$ERROR,$RETURN);
// //	$exec_flg =true;
// //	for ($i=1; $i<=300; $i++) {
// //		echo "・";
// //		echo str_pad(" ",4096);
// //		@flush();
// //		sleep(2);
// //	}
//
// 	if($exec_flg === false){
// 		echo "<br>";
// 		echo "JOBの実行を確認できないため途中終了します。";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		return;
// 	}
//
// 	if ( $deploy_id ) {
// 		$UPDATE_DATA['end_time'] = "now()";
// 		$UPDATE_DATA['state'] = "1";
// 		$where = " WHERE deploy_id=".$deploy_id;
// 		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
// 	}
//
// 	echo "<br>";
// 	echo "Coredb_Release END<br>";
// 	echo "<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
//
//
// 	// -----------------------------------
// 	// Coreweb_Release -------------------
// 	// -----------------------------------
//
// 	echo "<br>";
// 	echo "Coreweb_Release START<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
// 	// 実行ログを記録
// 	$INSERT_DATA = array();
// 	$INSERT_DATA['manager_id'] = $_SESSION['myid']['id'];
// 	$INSERT_DATA['deploy_mode'] = "Coreweb_Release";
// 	$INSERT_DATA['start_time'] = "now()";
// 	$INSERT_DATA['state'] = "0";
// 	$ERROR = $cdb->insert(T_DEPLOY_LOG,$INSERT_DATA);
// 	//登録idを取得
// 	$deploy_id = $cdb->insert_id();
//
// 	$exec_flg = jenkins_api_exec('Coreweb_Release'); // jenkinsAPIを実行
//
// 	if($exec_flg === false){
// 		echo "<br>";
// 		echo "JOBの実行を確認できないため途中終了します。";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		return;
// 	}
//
// 	if ( $deploy_id ) {
// 		$UPDATE_DATA['end_time'] = "now()";
// 		$UPDATE_DATA['state'] = "1";
// 		$where = " WHERE deploy_id=".$deploy_id;
// 		$ERROR = $cdb->update(T_DEPLOY_LOG,$UPDATE_DATA,$where);
// 	}
//
// 	echo "<br>";
// 	echo "Coreweb_Release END<br>";
// 	echo "<br>";
// 	echo str_pad(" ",4096);
// 	@flush();
//
// 	echo "<br>";
// 	echo "本番リリース完了<br>";
// //	echo "ここから15分間スリープして旧本番環境を削除します。<br>";
// 	echo str_pad(" ",4096);
//
// }
//
// // JOBの実行状況を取得
// function get_deploy_progress($vpc){
//
// 	if($vpc == 'prod') {
// 		$ERROR = exec(PROD_COMPUTER_API,$RETURN);
//
// 	} else if($vpc == 'stg') {
// 		$ERROR = exec(STG_COMPUTER_API,$RETURN);
//
// 	}
//
// 	return $RETURN;
//
// }
//
// // JOBの実行状況をチェック
// // 実行中のJOBが存在する場合はエラーとJOB名を返す
// function check_deploy_running($vpc){
//
// 	$error_flg = false;
// 	$job_name = "";
//
// 	$DEPLOY_STATE_LIST = array();
// 	if($vpc == 'prod') {
// 		$DEPLOY_STATE_LIST = get_deploy_progress('prod');
//
// 	} else if($vpc == 'stg') {
// 		$DEPLOY_STATE_LIST = get_deploy_progress('stg');
//
// 	}
//
// // 	// 実行中のJOBがあれば処理を終了させる。
// // 	if(!empty($DEPLOY_STATE_LIST)){
// //
// // 		$error_flg = true;
// // 		$job_name = "";
// //
// // 		echo $vpc.'の実行状況が取得できませんでした。<br>';
// // 		return;
// // 	}
//
// 	// 実行中のJOBを確認
// 	if(is_array($DEPLOY_STATE_LIST)){
// 		foreach( $DEPLOY_STATE_LIST AS $key => $var ){
// 			// 実行JOBがある場合
// 			// if( preg_match("/fullDisplayName/",$var) ){
// 			if( preg_match("/name/",$var) && preg_match("/Coreweb_Setup|Coredb_Release|Coreweb_Release/",$var) ){
// 				$error_flg = true;
// 				$job_name = $var;
// 				break;
// 			}
// 		}
// 	}
//
// 	return array($error_flg,$job_name);
//
// }
//
// // JOBを実行
// function jenkins_api_exec($mode){
//
// 	// 必須パラメータチェック
// 	if(!$mode){
// 		echo "<br>";
// 		echo "API実行モードが取得できません。<br>";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		return;
// 	}
//
// 	// jenkinsAPIを実行
// 	if ($mode == "Coreweb_Setup"){
// 		$ERROR = exec(COREWEB_SETUP_API,$ERROR,$RETURN);
// 		$vpc = "stg";
//
// 	} else if ($mode == "Coredb_Release") {
// 		$ERROR = exec(COREDB_RELEASE_API,$ERROR,$RETURN);
// 		$vpc = "prod";
//
// 	} else if ($mode == "Coreweb_Release") {
// 		$ERROR = exec(COREWEB_RELEASE_API,$ERROR,$RETURN);
// 		$vpc = "prod";
//
// 	}
//
// 	// jenkinsを起動後、開始と終了をチェック
// 	// 実行状況の監視APIで進捗状況を判断します。
// 	// ただし、実行JOBが終了後に次のJOBの実行ログが取得できるできるまでには数秒のタイムラグが存在するので、
// 	// 完全に完了したかどうかは設定秒NULLが続いたかどうかで判断する。
// 	// ※MAX30分でタイムアウト
// 	$exec_flg = false;
// 	$null_count = 0;
// 	for ($i=1; $i<=900; $i++) {
//
// 		$DEPLOY_STATE_LIST = array();
// 		$DEPLOY_STATE_LIST = get_deploy_progress($vpc);
//
// 		// 実行中のJOBを確認
// 		$job_name = "";
// 		foreach( $DEPLOY_STATE_LIST AS $key => $var ){
// 			// 実行JOBがある場合
// 			// if( preg_match("/fullDisplayName/",$var) ){
// 			if( preg_match("/name/",$var) && preg_match("/".$mode."/",$var) ){
// 				$exec_flg = true; // JOBを実行したことを確認
// 				$job_name = $var;
// 			}
//
// 		}
// 		if(!$job_name){
// 			// 実行JOBがなければカウントアップ
// 			$null_count++;
// 		} else {
// 			// 実行JOBがあればカウントリセット
// 			$null_count = 0;
// 		}
//
// 		// NULLが10回以上続いた場合はJOBが終了したと判断する。
// 		// JOBが実行されたかは$exec_flgで判断。
// 		if($null_count > 10){ break; }
//
// 		echo "・";
// 		echo str_pad(" ",4096);
// 		@flush();
// 		sleep(2);
//
// 	}
//
// 	return $exec_flg;
//
// }
// del end 2018/09/14 yoshizawa デプロイエラーチェック実装
?>