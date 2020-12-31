<?php
/**
 * すらら
 *
 * ゲーミフィケーション ログ出力
 *
 * @author Azet
 */
define("ERR_GAMIFICATION_LOG_EXPORT_DATE_EMPTY", "対象期間が入力されていません。");
define("ERR_GAMIFICATION_LOG_EXPORT_NO_SELECTION", "出力項目が選択されていません。");
define("ERR_GAMIFICATION_LOG_EXPORT_DATE_FORMAT_INVALID", "対象期間の形式が不正です。(「yyyy/mm/dd」の形式で入力してください。)");
define("ERR_GAMIFICATION_LOG_EXPORT_DATE_REVERSED", "期間（開始日）は期間（終了日）よりも前の日付を指定してください。");
define("ERR_GAMIFICATION_LOG_EXPORT_SCHOOL_ID_INVALID", "校舎IDの形式が不正です。");
define("ERR_GAMIFICATION_LOG_EXPORT_GAMIFICATION_DB_CONN", "ゲーミフィケーションデータベースに接続できません。");
define("ERR_GAMIFICATION_LOG_EXPORT_TOTAL_DB_CONN", "総合データベースに接続できません。");
define("ERR_GAMIFICATION_LOG_EXPORT_ENTRY_DB_CONN", "統合データベースに接続できません。");
define("ERR_GAMIFICATION_LOG_EXPORT_NODATA", "出力する内容がありません。");
define("ERR_GAMIFICATION_LOG_EXPORT_SYS_ERR", "システムエラー");

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	global $L_GAMIFICATION_LOG_DB;

	$INPUTS = array();
	$ERROR = $error_html = null;

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_LOG_EXPORT);

	//--------------------
	//[エクスポート]ボタン押下
	//--------------------
	if($_POST['action'] == "export"){

		//日付がセットされているか
		if(!isset($_POST['log_start_date']) || !isset($_POST['log_end_date'])){
			$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_DATE_EMPTY;
		}
		//選択項目が選ばれているか
		if(!isset($_POST['log_selection'])){
			$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_NO_SELECTION;
		}
		//どちらかの日付が入力されている
		if(isset($_POST['log_start_date']) || isset($_POST['log_end_date'])){
			//yyyy/mm/dd形式であるか
			if(!preg_match("/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST['log_start_date'])){
				$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_DATE_FORMAT_INVALID;
			}
			//yyyy/mm/dd形式であるか
			if(!preg_match("/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST['log_end_date'])){
				$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_DATE_FORMAT_INVALID;
			}
			//開始日が終了日をこえていないか
			if(strtotime($_POST['log_start_date']) > strtotime($_POST['log_end_date'])){
				$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_DATE_REVERSED;
			}
		}
		//絞り込み 校舎IDが数値のみで入力されていること
		if(isset($_POST['filter_school_id']) && strlen($_POST['filter_school_id']) > 0){
			if(!preg_match("/[0-9]+$/", $_POST['filter_school_id'])){
				$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_SCHOOL_ID_INVALID;
			}
		}
		
		//ゲーミフィケーションDB接続(プルダウンで選択したもの)
		if(!gamificaton_log_db_connect($_POST['dbselect'])){
			$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_GAMIFICATION_DB_CONN;
		}
		//総合DB接続
		if(!gamificaton_log_sougou_db_connect($_POST['dbselect'])){
			$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_TOTAL_DB_CONN;
		}
		//エントリーDB接続
		if(!gamificaton_log_entry_db_connect($_POST['dbselect'])){
			$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_ENTRY_DB_CONN;
		}

		//エラーなし //エクスポートCSV準備
		if($ERROR === null){
			$csv = prepare_export_csv($_POST);
 			//エクスポート成功 スクリプト終了
			if($csv !== false){

				$fname = time().".txt";
				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-disposition: attachment;filename=".$fname);
				if (stristr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
					header("Content-Type: text/octet-stream");
				} else {
					header("Content-Type: application/octet-stream;");
				}
				//close db connection
				if($GLOBALS['game_cdb']){ $GLOBALS['game_cdb']->close(); }
				if($GLOBALS['sougou_cdb']){ $GLOBALS['sougou_cdb']->close(); }
				if($GLOBALS['entry_cdb']){ $GLOBALS['entry_cdb']->close(); }
				unset($GLOBALS['game_cdb']);
				unset($GLOBALS['sougou_cdb']);
				unset($GLOBALS['entry_cdb']);

				echo $csv;
				exit(0);
				//-------------------------------------------------DONE-------------------------------------------------

 			//エクスポート失敗(0行) 通常ルーチンに続く
			}else{
				$ERROR[] = ERR_GAMIFICATION_LOG_EXPORT_NODATA;
			}
		}
	}

	//エラーあり //画面にエラー文字列を描画
	if(!empty($ERROR) && is_array($ERROR)){
		$error_html = "<div class=\"small_error\"><strong style=\"color:#ff0000\">エラー</strong><br>".implode("<br>", $ERROR)."</div>";
		$INPUTS['ERRORHTML'] = array("result"=>"plane", "value"=>$error_html);
	}

	//参照ゲーミフィケーションDBリスト
	$gdb_list = null;
	if(is_array($L_GAMIFICATION_LOG_DB)){
		foreach($L_GAMIFICATION_LOG_DB as $key => $DB){
			$gdb_list[$key] = $DB['NAME'];
		}
	}

	$dbselect_form_obj = new form_parts();
	$dbselect_form_obj->set_form_type("select");
	$dbselect_form_obj->set_form_name("dbselect");
	$dbselect_form_obj->set_form_array($gdb_list);
	$INPUTS['DBSELECT'] = array("result"=>"plane", "value"=>$dbselect_form_obj->make());

	//期間フォームデフォルト
	$sdate = date("Y/m/d", strtotime("first day of this month")); //月初
	$edate = date("Y/m/d", strtotime("now")); //本日

	//POSTされた日付けで期間フォームの値を上書き
	if(isset($_POST['log_start_date'])){
		$sdate = $_POST['log_start_date'];
	}
	if(isset($_POST['log_end_date'])){
		$edate = $_POST['log_end_date'];
	}

	$INPUTS['SDATE'] = array("result"=>"plane", "value"=>$sdate);
	$INPUTS['EDATE'] = array("result"=>"plane", "value"=>$edate);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	//close db connection
	if($GLOBALS['game_cdb']){ $GLOBALS['game_cdb']->close(); }
	if($GLOBALS['sougou_cdb']){ $GLOBALS['sougou_cdb']->close(); }
	if($GLOBALS['entry_cdb']){ $GLOBALS['entry_cdb']->close(); }
	unset($GLOBALS['game_cdb']);
	unset($GLOBALS['sougou_cdb']);
	unset($GLOBALS['entry_cdb']);

	return $html;
}

/**
 * 指定されたゲーミフィケーションDBに接続する
 * $GLOBALS変数を内部で設定します。
 * 
 * @param  string $db_key
 * @return boolean
 */
function gamificaton_log_db_connect($db_key){

	global $L_GAMIFICATION_LOG_DB;

	$gcdb = new connect_db();
	$gcdb->set_db($L_GAMIFICATION_LOG_DB[$db_key]);
	$ERROR = $gcdb->set_connect_db();

	// 接続できたらグローバル変数に設定する
	if(empty($ERROR)){
		$GLOBALS['game_cdb'] = $gcdb;
		return true;
	}else{
		return false;
	}
}

/**
 * 総合DBに接続する
 * $GLOBALS変数を内部で設定します。
 * 
 * @return boolean
 */
function gamificaton_log_sougou_db_connect($db_key){

	global $L_GAMIFICATION_LOG_DB_TOTAL;

	$total_cdb = new connect_db();
	$total_cdb->set_db($L_GAMIFICATION_LOG_DB_TOTAL[$db_key]);
	$ERROR = $total_cdb->set_connect_db();

	// 接続できたらグローバル変数に設定する
	if(empty($ERROR)){
		$GLOBALS['sougou_cdb'] = $total_cdb;
		return true;
	}else{
		return false;
	}
}

/**
 * 統合DBに接続する
 * $GLOBALS変数を内部で設定します。
 * 
 * @return boolean
 */
function gamificaton_log_entry_db_connect($db_key){

	global $L_GAMIFICATION_LOG_DB_ENT;

	$entry_cdb = new connect_db();
	$entry_cdb->set_db($L_GAMIFICATION_LOG_DB_ENT[$db_key]);
	$ERROR = $entry_cdb->set_connect_db();

	// 接続できたらグローバル変数に設定する
	if(empty($ERROR)){
		$GLOBALS['entry_cdb'] = $entry_cdb;
		return true;
	}else{
		return false;
	}
}

/**
 * ログ情報エクスポート準備
 * @param  array          $post $_POST
 * @return boolean|string
 */
function prepare_export_csv($post){

	$post['log_start_date'] = str_replace("/", "-", $post['log_start_date']);
	$post['log_end_date'] = str_replace("/", "-", $post['log_end_date']);

// echo '★Starttime=>'.date('H:i:s')."<br>";	
	//エントリー生徒情報をテンポラリーテーブルで作成する
	// if(!create_ent_student_info_tmp($post['filter_jyko'])){	// del 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力
	if(!create_ent_student_info_tmp($post['filter_jyko'], $post['filter_school_id'])){	// add 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力
		return ERR_GAMIFICATION_LOG_EXPORT_SYS_ERR."\n";
	}

	$make_csv_func = $post['log_selection']."_csv";
	$csv = function_exists($make_csv_func) ? $make_csv_func($post) : null; 
// echo '★Endtime=>'.date('H:i:s')."<br>";	

	if(empty($csv)){
		return false;
	}else{
		return $csv;
	}

}

// del start 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力
// /**
//  * エントリー生徒情報をテンポラリーテーブルで作成する
//  *
//  * @param string
//  */
// function create_ent_student_info_tmp($filter_jyko){

// 	$gcdb = $GLOBALS['game_cdb']; //対象ゲーミフィケーションDB
// 	$scdb = $GLOBALS['sougou_cdb']; //総合DB
// 	$ecdb = $GLOBALS['entry_cdb']; //エントリーDB

// 	//総合サーバーから新TOP利用校舎一覧を参照
// 	$sql = "SELECT school_id FROM ".T_CONFIG_USERS_SETTING;
// 	$sql.= " WHERE setting_type = '5' AND mk_flg = 0"; //新TOP利用校舎 setting_type:5

// 	$setting_type5_school_list = null;

// 	if ($result = $scdb->query($sql)) {
// 		if($scdb->num_rows($result) > 0){
// 			while ($list = $scdb->fetch_assoc($result)) {
// 				$setting_type5_school_list[] = $list['school_id'];
// 			}
// 		}
// 	}else{
// 		return false;
// 	}

// 	$where_school_ids = null;

// 	if(!empty($setting_type5_school_list)){
// 		$where_school_ids = "'".implode("','", $setting_type5_school_list)."'";
// 	}
// 	unset($sql);

// 	$sql = "SELECT";
// 	$sql.= "  student.student_id";
// 	$sql.= " ,student.knry_end_flg";
// 	$sql.= " ,student.secessionday";
// 	$sql.= " ,school.school_id";
// 	$sql.= " ,school.kmi_zksi";
// 	$sql.= " ,db_connect.db_id";
// 	$sql.= " ,tb_stu_jyko_jyotai.sito_jyti";
// 	$sql.= " FROM student student";
// 	$sql.= " INNER JOIN school school ON student.school_id = school.school_id";
// 	$sql.= "  AND school.mk_flg = 0";
// 	$sql.= " INNER JOIN tb_stu_jyko_jyotai tb_stu_jyko_jyotai ON student.student_id = tb_stu_jyko_jyotai.student_id";
// 	$sql.= "  AND tb_stu_jyko_jyotai.mk_flg = 0";
// 	$sql.= " INNER JOIN db_connect db_connect ON school.enterprise_id = db_connect.enterprise_id";
// 	$sql.= " WHERE 1=1";
// 	//NOTE:
// 	//  config_users_settingにsetting_type=5のレコードが一件も見つからなかった場合
// 	//  このWHERE条件がつかず全校舎が対象になります。
// 	if($where_school_ids){
// 		$sql.= "  AND school.school_id IN ({$where_school_ids})";
// 	}
// 	//「受講中」のチェックをつけた場合はの場合where条件を追加
// 	if ($filter_jyko == "1") {
// 		$sql .= " AND (".
// 			" (school.kmi_zksi='1' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
// 			" OR (school.kmi_zksi='2' AND (student.knry_end_flg!='1'))".
// 			" OR (school.kmi_zksi='3' AND (student.knry_end_flg!='1' AND tb_stu_jyko_jyotai.sito_jyti!='2'))".
// 			" OR (school.kmi_zksi='4' AND (student.knry_end_flg!='1' AND tb_stu_jyko_jyotai.sito_jyti!='2'))".
// 			" OR (school.kmi_zksi='5' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
// 			" OR (school.kmi_zksi='6' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
// 			" OR (school.kmi_zksi='8' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
// 			" OR (school.kmi_zksi='9' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
// 			" )";
// 	}

// 	$INSERT_DATAS = null;

// 	if ($result = $ecdb->query($sql)) {
// 		while ($list = $ecdb->fetch_assoc($result)) {
// 			$INSERT_DATAS[] = $list;
// 		}
// 	}else{
// 		return false;
// 	}

// 	unset($sql);

// 	//drop処理
// 	$sql = "DROP TEMPORARY TABLE IF EXISTS gamification_student_tmp";
// 	if(!$gcdb->exec_query($sql)){
// 		return false;
// 	}

// 	$sql = "CREATE TEMPORARY TABLE gamification_student_tmp (";
// 	$sql.= "  db_id        VARCHAR(10) NOT NULL";
// 	$sql.= " ,school_id    VARCHAR(10) NOT NULL";
// 	$sql.= " ,student_id   VARCHAR(10) NOT NULL";
// 	$sql.= " ,knry_end_flg TINYINT(4)  NULL";
// 	$sql.= " ,secessionday DATE        NULL";
// 	$sql.= " ,kmi_zksi     VARCHAR(10) NULL";
// 	$sql.= " ,sito_jyti    VARCHAR(10) NULL";
// 	$sql.= ")";
// 	if(!$gcdb->exec_query($sql)){
// 		return false;
// 	}

// 	$INSERT_DATAS2 = array();

// 	//100件ずつテンポラリーにインサート
// 	if(!empty($INSERT_DATAS)){
// 		// foreach($INSERT_DATAS as $value){	// upd hasegawa 2019/08/06 １件ずつインサートされてるので修正
// 		foreach($INSERT_DATAS as $key => $value){
// 			$INSERT_DATAS2[] = $value;
// 			if($key > 0 && ($key%100 == 0)){
// 				$gcdb->insert_all("gamification_student_tmp", $INSERT_DATAS2, 100);
// 				$INSERT_DATAS2 = array();
// 			}
// 		}
// 	}
// 	//余りをインサート
// 	if(!empty($INSERT_DATAS2)){
// 		if(count($INSERT_DATAS2) > 0){
// 			$gcdb->insert_all("gamification_student_tmp", $INSERT_DATAS2, 0);
// 		}
// 	}
// 	unset($INSERT_DATAS, $INSERT_DATAS2);

// 	//DEBUG
// 	// echo sql_to_html_table("SELECT * from gamification_student_tmp", $gcdb);
// 	return true;
// }
// del end 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力

// add start 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力
/**
 * エントリー生徒情報をテンポラリーテーブルで作成する
 *
 * @param string
 */
function create_ent_student_info_tmp($filter_jyko, $filter_school_id){

	$gcdb = $GLOBALS['game_cdb']; //対象ゲーミフィケーションDB
	$scdb = $GLOBALS['sougou_cdb']; //総合DB
	$ecdb = $GLOBALS['entry_cdb']; //エントリーDB

	// drop処理
	$sql = "DROP TEMPORARY TABLE IF EXISTS gamification_student_tmp";
	if(!$gcdb->exec_query($sql)){
		return false;
	}

	// create処理
	$sql = "CREATE TEMPORARY TABLE gamification_student_tmp (";
	$sql.= "  db_id        VARCHAR(10) NOT NULL";
	$sql.= " ,school_id    VARCHAR(10) NOT NULL";
	$sql.= " ,student_id   VARCHAR(10) NOT NULL";
	$sql.= " ,knry_end_flg TINYINT(4)  NULL";
	$sql.= " ,secessionday DATE        NULL";
	$sql.= " ,kmi_zksi     VARCHAR(10) NULL";
	$sql.= " ,sito_jyti    VARCHAR(10) NULL";
	$sql.= ")";
	if(!$gcdb->exec_query($sql)){
		return false;
	}

	//総合サーバーから新TOP利用校舎一覧を参照
	$sql = "SELECT school_id FROM ".T_CONFIG_USERS_SETTING;
	$sql.= " WHERE setting_type = '5' AND mk_flg = 0"; //新TOP利用校舎 setting_type:5

	$setting_type5_school_list = null;

	if ($result = $scdb->query($sql)) {
		if($scdb->num_rows($result) > 0){
			while ($list = $scdb->fetch_assoc($result)) {
				$setting_type5_school_list[] = $list['school_id'];
			}
		}
	}else{
		return false;
	}

	$where_school_ids = null;

	if(!empty($setting_type5_school_list)){
		$where_school_ids = "'".implode("','", $setting_type5_school_list)."'";
	}
	unset($sql);

	$sql = "SELECT";
	$sql.= "  student.student_id";
	$sql.= " ,student.knry_end_flg";
	$sql.= " ,student.secessionday";
	$sql.= " ,school.school_id";
	$sql.= " ,school.kmi_zksi";
	$sql.= " ,db_connect.db_id";
	$sql.= " ,tb_stu_jyko_jyotai.sito_jyti";
	$sql.= " FROM student student";
	$sql.= " INNER JOIN school school ON student.school_id = school.school_id";
	$sql.= "  AND school.mk_flg = 0";
	$sql.= " INNER JOIN tb_stu_jyko_jyotai tb_stu_jyko_jyotai ON student.student_id = tb_stu_jyko_jyotai.student_id";
	$sql.= "  AND tb_stu_jyko_jyotai.mk_flg = 0";
	$sql.= " INNER JOIN db_connect db_connect ON school.enterprise_id = db_connect.enterprise_id";
	$sql.= " WHERE 1=1";
	//NOTE:
	//  config_users_settingにsetting_type=5のレコードが一件も見つからなかった場合
	//  このWHERE条件がつかず全校舎が対象になります。
	if($where_school_ids){
		$sql.= "  AND school.school_id IN ({$where_school_ids})";
	}
	// 条件に校舎IDがある時は対象校舎のデータだけを抽出する様にいたします。
	else if(isset($filter_school_id) && $filter_school_id != ""){
		$sql.= "  AND school.school_id = '".$filter_school_id."'";
	}
	//「受講中」のチェックをつけた場合はの場合where条件を追加
	if ($filter_jyko == "1") {
		$sql .= " AND (".
			" (school.kmi_zksi='1' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='2' AND (student.knry_end_flg!='1'))".
			" OR (school.kmi_zksi='3' AND (student.knry_end_flg!='1' AND tb_stu_jyko_jyotai.sito_jyti!='2'))".
			" OR (school.kmi_zksi='4' AND (student.knry_end_flg!='1' AND tb_stu_jyko_jyotai.sito_jyti!='2'))".
			" OR (school.kmi_zksi='5' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='6' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='8' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='9' AND (tb_stu_jyko_jyotai.sito_jyti NOT IN (2,9) AND NOT (tb_stu_jyko_jyotai.sito_jyti='0' AND student.secessionday<CURRENT_DATE())))".
			" )";
	}

	$INSERT_DATAS = null;

	$count = 0;
	if ($result = $ecdb->query($sql)) {
		while ($list = $ecdb->fetch_assoc($result)) {
			$INSERT_DATAS[] = $list;
			$count++;
			// 100件ずつインサート
			if($count == 100){
				$gcdb->insert_all("gamification_student_tmp", $INSERT_DATAS, 100);
				// カウントと情報をリセット
				$count = 0;
				$INSERT_DATAS = array();
			}
		}
		//余りをインサート
		if(!empty($INSERT_DATAS)){
			if(count($INSERT_DATAS) > 0){
				$gcdb->insert_all("gamification_student_tmp", $INSERT_DATAS, 100);
			}
		}

	}else{
		return false;
	}

	unset($INSERT_DATAS);

	//DEBUG
	// echo sql_to_html_table("SELECT * from gamification_student_tmp", $gcdb);
	return true;

}
// add end 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力

/**
 * [CSV生成]アチーブエッグログ
 * 
 * @param  array  $post $_POST
 * @return string $csv
 */
function achieve_egg_log_csv($post){

	$csv = null;

	// $csv .= "★ログID\t"; //DEBUG
	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "アチーブ・エッグ選出日時\t";
	$csv .= "日付変更による選出か前の卵達成による選出か（0：日付変更によるもの 1：達成時によるもの）\t";
	$csv .= "アチーブ・エッグ1つ目のID\t";
	$csv .= "アチーブ・エッグ1つ目のポイント数\t";
	$csv .= "アチーブ・エッグ1つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ2つ目のID\t";
	$csv .= "アチーブ・エッグ2つ目のポイント数\t";
	$csv .= "アチーブ・エッグ2つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ3つ目のID\t";
	$csv .= "アチーブ・エッグ3つ目のポイント数\t";
	$csv .= "アチーブ・エッグ3つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ4つ目のID\t";
	$csv .= "アチーブ・エッグ4つ目のポイント数\t";
	$csv .= "アチーブ・エッグ4つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ5つ目のID\t";
	$csv .= "アチーブ・エッグ5つ目のポイント数\t";
	$csv .= "アチーブ・エッグ5つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ6つ目のID\t";
	$csv .= "アチーブ・エッグ6つ目のポイント数\t";
	$csv .= "アチーブ・エッグ6つ目の初期状態でのコメント\t";
	$csv .= "アチーブ・エッグ7つ目のID\t";
	$csv .= "アチーブ・エッグ7つ目のポイント数\t";
	$csv .= "アチーブ・エッグ7つ目の初期状態でのコメント\t";
	$csv .= "選択したアチーブエッグID（未選択の場合は0） \t";
	$csv .= "選択したアチーブエッグのポイント数（未選択の場合は0） \t";
	$csv .= "選択したアチーブエッグの初期状態でのコメント  \t";
	$csv .= "アチーブ・エッグセット日時\t";
	$csv .= "アチーブ・エッグ取りやめ日時\t";
	$csv .= "アチーブ・エッグ達成日時\t";
	$csv .= "手に入れたアバターID";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  ld.student_id";
	$sql.= " ,l.school_id";
	$sql.= " ,l.log_id";
	$sql.= " ,l.election_date";
	$sql.= " ,ld.achieve_egg_id";
	$sql.= " ,e.get_point";
	$sql.= " ,e.default_comment";
	$sql.= " FROM ".T_STUDENT_ELECTION_ACHIEVE_EGG_LOG_DETAIL." ld";
	$sql.= " INNER JOIN ".T_STUDENT_ELECTION_ACHIEVE_EGG_LOG." l";
	$sql.= "  ON ld.log_id = l.log_id";
	$sql.= "  AND l.move_flg = 0";//mk_flgはみない
	$sql.= " INNER JOIN ".T_GAMIFICATION_ACHIEVE_EGG." e";
	$sql.= "  ON ld.achieve_egg_id = e.achieve_egg_id";
	$sql.= " WHERE 1";
	$sql.= "  AND l.election_date BETWEEN '{$post['log_start_date']}' AND '{$post['log_end_date']}'";
	$sql.= "  AND l.move_flg = 0";//mk_flgはみない
	$sql.= " ORDER BY ld.log_id";

	//DEBUG
	// echo sql_to_html_table($sql, $gcdb);

	$log_detail_list = null;
	$log_detail_point_list = null;
	$log_detail_comment_list = null;

	if ($result = $gcdb->query($sql)) {
		while ($list = $gcdb->fetch_assoc($result)) {
			$log_detail_list[$list['school_id']][$list['student_id']][$list['log_id']][] = $list['achieve_egg_id'];
			$log_detail_point_list[$list['school_id']][$list['student_id']][$list['log_id']][$list['achieve_egg_id']] = $list['get_point'];
			$log_detail_comment_list[$list['school_id']][$list['student_id']][$list['log_id']][$list['achieve_egg_id']] = $list['default_comment'];
		}
		$gcdb->free_result($result);
	}

	$INSERT_DATAS = array();
	$i = 0;

	if(is_array($log_detail_list)){
		foreach($log_detail_list as $school_id => $student_id_list){
			if(is_array($student_id_list)){
				foreach($student_id_list as $student_id => $log_id_list){
					if(is_array($log_id_list)){
						foreach($log_id_list as $log_id => $achieve_egg_id_list){
							$INSERT_DATAS[$i]['school_id'] = $school_id;
							$INSERT_DATAS[$i]['student_id'] = $student_id;
							$INSERT_DATAS[$i]['log_id'] = $log_id;
							if(is_array($log_id_list)){
								for($n = 0; $n < 7; $n++){
									$egg_id = $achieve_egg_id_list[$n] ?: "NULL";
									//アチーブエッグIDはカウンター+1 (1～開始の為)
									$INSERT_DATAS[$i]['achieve_egg_id'.($n+1)] = $egg_id;
									$INSERT_DATAS[$i]['achieve_egg_id'.($n+1).'_get_point'] = $log_detail_point_list[$school_id][$student_id][$log_id][$egg_id] ?: "NULL";
									$INSERT_DATAS[$i]['achieve_egg_id'.($n+1).'_default_comment'] = $log_detail_comment_list[$school_id][$student_id][$log_id][$egg_id] ?: "NULL";
								}
							}
							$i++;
						}
					}
				}
			}
		}
	}
	unset($log_detail_list, $log_detail_point_list, $log_detail_comment_list); //gc
	
	//drop処理
	$sql = "DROP TEMPORARY TABLE log_detail_list_tmp";
	$gcdb->exec_query($sql);

	$sql = "CREATE TEMPORARY TABLE log_detail_list_tmp (";
	$sql.= " school_id                        VARCHAR(10)  NOT NULL";
	$sql.= " ,student_id                      VARCHAR(10)  NOT NULL";
	$sql.= " ,log_id                          INT(11)      NOT NULL";
	$sql.= " ,achieve_egg_id1                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id1_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id1_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id2                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id2_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id2_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id3                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id3_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id3_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id4                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id4_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id4_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id5                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id5_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id5_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id6                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id6_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id6_default_comment VARCHAR(255) NULL";
	$sql.= " ,achieve_egg_id7                 INT(11)      NULL";
	$sql.= " ,achieve_egg_id7_get_point       INT(11)      NULL";
	$sql.= " ,achieve_egg_id7_default_comment VARCHAR(255) NULL";
	$sql.= ")";
	$gcdb->exec_query($sql);

	//100件ずつテンポラリーにインサート
	$INSERT_DATAS2 = array();

	// foreach($INSERT_DATAS as $value){	// 1件ずつインサートされてるので修正
	foreach($INSERT_DATAS as $key => $value){
		$INSERT_DATAS2[] = $value;
		if($key > 0 && ($key%100 == 0)){
			$gcdb->insert_all("log_detail_list_tmp ", $INSERT_DATAS2, 100);
			$INSERT_DATAS2 = array();
		}
	}
	unset($INSERT_DATAS);

	//余りをインサート
	if(count($INSERT_DATAS2) > 0){
		// $gcdb->insert_all("log_detail_list_tmp", $INSERT_DATAS2, 0);	// del end 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力
		$gcdb->insert_all("log_detail_list_tmp", $INSERT_DATAS2, 100);	// add end 2020/08/21 yoshizawa ゲーミフィケーション学習ログ出力 0だと1件ずつインサートしてしまうので変更
	}
	unset($INSERT_DATAS2);

	//DEBUG
	// echo sql_to_html_table("select * from log_detail_list_tmp", $gcdb);

	$sql = "SELECT";
	// $sql.= "  log.log_id,"; //ログID //DEBUG
	$sql.= "  st.student_id"; //生徒ID
	$sql.= " ,st.school_id"; //校舎ID
	$sql.= " ,log.election_date"; //アチーブエッグ選出日時
	$sql.= " ,log.election_type"; //日付変更時セットか 再選出か
	$sql.= " ,log.achieve_egg_id1, log.achieve_egg_id1_get_point, log.achieve_egg_id1_default_comment"; //選出エッグ1情報
	$sql.= " ,log.achieve_egg_id2, log.achieve_egg_id2_get_point, log.achieve_egg_id2_default_comment"; //選出エッグ2情報
	$sql.= " ,log.achieve_egg_id3, log.achieve_egg_id3_get_point, log.achieve_egg_id3_default_comment"; //選出エッグ3情報
	$sql.= " ,log.achieve_egg_id4, log.achieve_egg_id4_get_point, log.achieve_egg_id4_default_comment"; //選出エッグ4情報
	$sql.= " ,log.achieve_egg_id5, log.achieve_egg_id5_get_point, log.achieve_egg_id5_default_comment"; //選出エッグ5情報
	$sql.= " ,log.achieve_egg_id6, log.achieve_egg_id6_get_point, log.achieve_egg_id6_default_comment"; //選出エッグ6情報
	$sql.= " ,log.achieve_egg_id7, log.achieve_egg_id7_get_point, log.achieve_egg_id7_default_comment"; //選出エッグ7情報
	$sql.= " ,IFNULL(i.achieve_egg_id, '0')"; //セットしたアチーブエッグID
	$sql.= " ,IFNULL(ae.get_point, '0')"; //セットしたアチーブエッグのポイント数
	$sql.= " ,ae.default_comment"; //セットしたアチーブエッグの初期コメント
	$sql.= " ,i.setting_date"; //セット日
	$sql.= " ,(";
	$sql.= "   CASE";
	$sql.= "    WHEN i.sys_bko = 'reset' THEN i.mk_date"; //別のたまごを育てたとき
	$sql.= "    WHEN i.sys_bko = 'change_settings' THEN i.mk_date"; //設定を変えたとき
	$sql.= "    ELSE NULL";
	$sql.= "   END";
	$sql.= "  ) AS abandon_date"; //セットしたアチーブエッグを取りやめした日
	$sql.= " ,i.meet_hatching_condition_date"; //割った日
	$sql.= " ,al.avatar_id"; //手に入れたアバターID
	$sql.= " FROM gamification_student_tmp st"; //エントリーから取得した生徒一覧に存在する生徒情報のみ
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN (";
	}else{
		$sql.= " INNER JOIN (";
	}
	$sql.= "     SELECT ";
	$sql.= "      l.student_id";
	$sql.= "     ,l.school_id";
	$sql.= "     ,l.log_id";
	$sql.= "     ,l.election_date"; //アチーブエッグ選出日時
	$sql.= "     ,l.election_type"; //日付変更時セットか 再選出か
	$sql.= "     ,ldlt.achieve_egg_id1, ldlt.achieve_egg_id1_get_point, ldlt.achieve_egg_id1_default_comment"; //選出エッグ1情報
	$sql.= "     ,ldlt.achieve_egg_id2, ldlt.achieve_egg_id2_get_point, ldlt.achieve_egg_id2_default_comment"; //選出エッグ2情報
	$sql.= "     ,ldlt.achieve_egg_id3, ldlt.achieve_egg_id3_get_point, ldlt.achieve_egg_id3_default_comment"; //選出エッグ3情報
	$sql.= "     ,ldlt.achieve_egg_id4, ldlt.achieve_egg_id4_get_point, ldlt.achieve_egg_id4_default_comment"; //選出エッグ4情報
	$sql.= "     ,ldlt.achieve_egg_id5, ldlt.achieve_egg_id5_get_point, ldlt.achieve_egg_id5_default_comment"; //選出エッグ5情報
	$sql.= "     ,ldlt.achieve_egg_id6, ldlt.achieve_egg_id6_get_point, ldlt.achieve_egg_id6_default_comment"; //選出エッグ6情報
	$sql.= "     ,ldlt.achieve_egg_id7, ldlt.achieve_egg_id7_get_point, ldlt.achieve_egg_id7_default_comment"; //選出エッグ7情報
	$sql.= "     FROM ".T_STUDENT_ELECTION_ACHIEVE_EGG_LOG." l";
	$sql.= "     INNER JOIN ".T_STUDENT_ELECTION_ACHIEVE_EGG_LOG_DETAIL." ld";
	$sql.= "      ON l.log_id = ld.log_id";
	$sql.= "     INNER JOIN log_detail_list_tmp ldlt"; //選出リスト詳細
	$sql.= "      ON  l.log_id = ldlt.log_id";
	$sql.= "     WHERE 1=1";
	$sql.= "      AND l.move_flg = 0";
	$sql.= "      AND l.election_date BETWEEN '{$post['log_start_date']}' AND '{$post['log_end_date']}'";
	$sql.= "     GROUP BY l.log_id";
	$sql.= " ) log ON st.student_id = log.student_id AND st.school_id = log.school_id";
	$sql.= " LEFT JOIN ".T_STUDENT_ACHIEVE_EGG_INFO." i"; //セットしたエッグの情報(選出後セットしていない場合もあり)
	$sql.= "  ON  log.log_id = i.election_achieve_egg_log_id";
	$sql.= "  AND log.student_id = i.student_id";
	$sql.= "  AND log.school_id = i.school_id";
	$sql.= " LEFT JOIN ".T_GAMIFICATION_ACHIEVE_EGG." ae"; //セットしたエッグがあればマスタ情報結合
	$sql.= "  ON  i.achieve_egg_id = ae.achieve_egg_id";
	$sql.= "  AND ae.mk_flg = 0";
	$sql.= " LEFT JOIN ".T_STUDENT_GET_AVATAR_LOG." al";  //セットしたエッグがあればマスタ情報結合
	$sql.= "  ON  log.log_id = al.election_achieve_egg_log_id";
	$sql.= "  AND i.achieve_egg_id = al.achieve_egg_id";

	$sql.= " WHERE 1=1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "    AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}
}

/**
 * [CSV生成]アチーブエッグ学習ログ
 * 
 * @param  array  $post $_POST
 * @return string $csv
 */
function achieve_egg_study_log_csv($post){

	$csv = null;

	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "日時\t";
	$csv .= "クリアユニット番号   ※ ユニットをクリアした場合\t";
	$csv .= "テストタイプ（テストの場合）\t";
	$csv .= "学習時間（レクチャー・ドリル・ゲームorテストの学習時間の合計）\t";
	$csv .= "正解率（レクチャー）\t";
	$csv .= "正解率（ゲーム）\t";
	$csv .= "正解率（ドリルorテスト）\t";
	$csv .= "セットしているアチーブエッグID\t";
	$csv .= "アチーブ・エッグを孵化できたか（0：孵化できていない 1：孵化できた）\t";
	$csv .= "孵化できた場合の取得アバターID";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  st.student_id";
	$sql.= " ,st.school_id";
	$sql.= " ,l.log_date";
	$sql.= " ,l.unit_num";
	$sql.= " ,l.test_type_num";
	$sql.= " ,(l.flash_study_time + l.study_time + l.game_study_time) AS study_time"; //テストのときは合算すると自動的にテストの学習時間になる レクチャー0+ゲーム0+テスト時間
	$sql.= " ,ROUND(l.flash_correct_rate) AS flash_correct_rate";
	$sql.= " ,ROUND(l.game_correct_rate) AS game_correct_rate";
	$sql.= " ,ROUND(l.correct_rate) AS correct_rate";
	$sql.= " ,l.achieve_egg_id";
	$sql.= " ,l.meet_hatching_condition";
	$sql.= " ,al.avatar_id";
	$sql.= " FROM gamification_student_tmp st"; //エントリーから取得した生徒一覧に存在する生徒情報のみ
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_ACHIEVE_EGG_STUDY_LOG." l";
	$sql.= "  ON  st.student_id = l.student_id";
	$sql.= "  AND st.school_id = l.school_id";
	$sql.= " LEFT JOIN ".T_STUDENT_GET_AVATAR_LOG." al";
	$sql.= "  ON  st.student_id = l.student_id";
	$sql.= "  AND st.school_id = l.school_id";
	$sql.= "  AND l.election_achieve_egg_log_id = al.election_achieve_egg_log_id"; //1選出ログIDの中で割れるのは1つだけなので必ず(1:1)になる
	$sql.= "  AND l.mk_flg = 0";
	$sql.= "  AND l.move_flg = 0";
	$sql.= "  AND l.log_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1=1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}

}

/**
 * [CSV生成]キャラクター育成ログ
 *
 * 育成ログは毎晩前日のデータを前日日付で集計するので、当日分のデータはダウンロードできません。
 * 
 * @param  array  $post $_POST
 * @return string $csv
 */
function character_log_csv($post){

	//コア分散DBに接続して各データを取得してくる
	$core_study_data = get_study_data_from_core_dbs($post);

	$csv = null;

	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "集計日\t";
	$csv .= "その日にクリアしたユニット番号\t";
	$csv .= "その日の総学習時間（秒）\t";
	$csv .= "その日の合計クリアユニット数\t";
	$csv .= "集計時点の選択しているキャラクターID（未選択時：0）\t";
	$csv .= "集計時点の選択しているキャラクター名\t";
	$csv .= "集計時点の満腹度\t";
	$csv .= "集計時点の満腹度が0になるまでの残り時間（分）\t";
	$csv .= "集計時点の生徒の所持ポイント\t";
	$csv .= "集計時点のレベル\t";
	$csv .= "集計時点の経験値\t";
	$csv .= "集計時点のレベルアップまでの残り経験値\t";
	$csv .= "集計時点の一回あたりのエサポイント数\t";
	$csv .= "集計時点の一回あたりのトレーニングポイント数\t";
	$csv .= "集計時点の使用中アイテムID\t";
	$csv .= "集計時点の使用中アイテム名\t";
	$csv .= "その日使用したアイテムID\t";
	$csv .= "その日エサを与えた回数\t";
	$csv .= "その日のトレーニング回数\t";
	$csv .= "その日レベルアップした回数\t";
	$csv .= "その日削除したアイテムID\t";
	$csv .= "その日削除したアイテム名\t";
	$csv .= "キャラクターを育てはじめた日\t";
	$csv .= "キャラクターが死んだ日時（育成中のキャラクターが最後に死んだ日時）\t";
	$csv .= "キャラクターが復活した日時（育成中のキャラクターが最後に復活した日時）\t";
	$csv .= "その日使用制限がかかった日時\t";
	$csv .= "その日一時停止解除された日時";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  st.student_id";
	$sql.= " ,st.school_id";
	$sql.= " ,DATE(l.aggregation_date) AS aggregation_date";
	$sql.= " ,NULL AS 'unit_num_list'";
	$sql.= " ,NULL AS 'study_total_time'";
	$sql.= " ,NULL AS 'clear_unit_count'";
	$sql.= " ,l.character_id";
	$sql.= " ,l.name";
	$sql.= " ,l.full";
	$sql.= " ,l.character_remaining_time";
	$sql.= " ,l.point";
	$sql.= " ,l.character_level";
	$sql.= " ,l.experience";
	$sql.= " ,l.levelup_point";
	$sql.= " ,l.need_meal_point";
	$sql.= " ,l.training_point";
	$sql.= " ,l.item_id";
	$sql.= " ,l.item_name";
	$sql.= " ,l.daily_item_id";
	$sql.= " ,l.daily_meal_count";
	$sql.= " ,l.daily_training_count";
	$sql.= " ,l.daily_levelup_count";
	$sql.= " ,l.daily_delete_item_id";
	$sql.= " ,l.daily_delete_item_name";
	$sql.= " ,l.setting_date";
	$sql.= " ,l.last_death_date";
	$sql.= " ,l.last_revival_date";
	$sql.= " ,NULL AS 'restriction1_time_list'"; //制限された時間リスト
	$sql.= " ,NULL AS 'restriction2_time_list'"; //制限解除された時間リスト
	$sql.= " FROM gamification_student_tmp st"; //エントリーから取得した生徒一覧に存在する生徒情報のみ
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_CHARACTER_NURTURE_DAILY_LOG." l";
	$sql.= "  ON  st.student_id = l.student_id";
	$sql.= "  AND st.school_id = l.school_id";
	$sql.= "  AND l.mk_flg = 0";
	$sql.= "  AND l.move_flg = 0";
	$sql.= "  AND l.aggregation_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1=1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {

		if($gcdb->num_rows($result) == 0){ return null; }

		while ($list = $gcdb->fetch_assoc($result)) {

			if(is_array($list)){
				foreach($list as $k => $v){

					//一部キーについてはわざとNULLをSELECTさせておいて、phpで変数に取得しておいた値に書き換えて出力{{{
					//
					//その日にクリアしたユニット番号
					if($k == "unit_num_list"){
						$v = $core_study_data['finish_unit'][$list['school_id']][$list['student_id']][$list['aggregation_date']];
					}
					//その日の総学習時間（秒）
					if($k == "study_total_time"){
						$v = $core_study_data['study_total_time_daily_summary'][$list['school_id']][$list['student_id']][$list['aggregation_date']];
					}
					//その日の合計クリアユニット数
					if($k == "clear_unit_count"){
						$v = $core_study_data['finish_unit_daily_summary'][$list['school_id']][$list['student_id']][$list['aggregation_date']];
					}
					//その日使用制限がかかった日時
					if($k == "restriction1_time_list"){
						$v = $core_study_data['restriction1_log'][$list['school_id']][$list['student_id']][$list['aggregation_date']];
					}
					//その日一時停止解除された日時
					if($k == "restriction2_time_list"){
						$v = $core_study_data['restriction2_log'][$list['school_id']][$list['student_id']][$list['aggregation_date']];
					}
					//}}}

					$csv .= $v."\t";
				}
				$csv = rtrim($csv, "\t");
				$csv.= "\n";
			}
		}

		return $csv;
	}else{
		return null;
	}

}

/**
 * [CSV生成]チーム学習ログ
 *
 * @param  array  $post $_POST
 * @return string $csv
 */
function team_log_csv($post){

	$csv = null;

	$csv .= "集計日時\t";
	$csv .= "チームID\t";
	$csv .= "チーム名\t";
	$csv .= "集計理由（1作成/2変更/3解散/4ボーナス獲得/5ボーナス獲得失敗）\t";
	$csv .= "集計時点のチーム内人数\t";
	$csv .= "上限人数\t";
	$csv .= "集計時点のチームの目標アチーブエッグ数\t";
	$csv .= "チームに所属する生徒ID\t";
	$csv .= "チームに所属する生徒の校舎ID\t";
	$csv .= "現在のチーム目標が作成されてからの学習したユニット番号\t";
	$csv .= "現在のチーム目標が作成されてからの総学習時間（秒）\t";
	$csv .= "現在のチーム目標が作成されてからの合計クリアユニット数\t";
	$csv .= "現在のチーム目標が作成されてからの合計ログイン回数\t";
	$csv .= "集計時点のチーム・ボーナスによる獲得ポイント数";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  ins_date";
	$sql.= " ,team_id";
	$sql.= " ,team_name";
	$sql.= " ,log_type";
	$sql.= " ,team_member_count";
	$sql.= " ,upper_limit";
	$sql.= " ,team_bonus_target_achieve_egg_count";
	$sql.= " ,student_id";
	$sql.= " ,school_id";
	$sql.= " ,study_unit_num";
	$sql.= " ,study_time";
	$sql.= " ,clear_unit_count";
	$sql.= " ,login_count";
	$sql.= " ,team_bonus_point";
	$sql.= " FROM ".T_TEAM_LOG;
	$sql.= " WHERE 1";
	$sql.= "  AND mk_flg = 0 AND move_flg = 0";
	$sql.= "  AND mk_flg = 0 AND move_flg = 0";
	$sql.= "  AND ins_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}
}

/**
 * [CSV生成]アバターログ
 *
 * @param  array  $post $_POST
 * @return string $csv
 */
function avatar_log_csv($post){

	$csv = null;

	$csv .= "集計日\t";
	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "アバターID\t";
	$csv .= "所持数";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  l.aggregation_date";
	$sql.= " ,st.student_id";
	$sql.= " ,st.school_id";
	$sql.= " ,l.avatar_id";
	$sql.= " ,l.quantity";
	$sql.= " FROM gamification_student_tmp st";
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_AVATAR_INFO_DAILY_LOG." l";
	$sql.= "  ON st.student_id = l.student_id";
	$sql.= "  AND l.mk_flg = 0";
	$sql.= "  AND l.aggregation_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}

}

/**
 * [CSV生成]アイテムログ
 *
 * @param  array  $post $_POST
 * @return string $csv
 */
function item_log_csv($post){

	$csv = null;

	$csv .= "集計日\t";
	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "アイテムID\t";
	$csv .= "所持数";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  l.aggregation_date";
	$sql.= " ,st.student_id";
	$sql.= " ,st.school_id";
	$sql.= " ,l.item_id";
	$sql.= " ,l.quantity";
	$sql.= " FROM gamification_student_tmp st";
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_ITEM_INFO_DAILY_LOG." l";
	$sql.= "  ON st.student_id = l.student_id";
	$sql.= "  AND l.mk_flg = 0";
	$sql.= "  AND l.aggregation_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}
}

/**
 * [CSV生成]プレゼントログ
 *
 * @param  array  $post $_POST
 * @return string $csv
 */
function present_log_csv($post){

	$csv = null;

	$csv .= "集計日時\t";
	$csv .= "プレゼントを贈った生徒ID\t";
	$csv .= "受け取った生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "アイテムorアバター(0:アバター1:アイテム)\t";
	$csv .= "ID";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  l.regist_date";
	$sql.= " ,st.student_id"; //送り主
	$sql.= " ,l.present_student_id"; //送り先
	$sql.= " ,st.school_id";
	$sql.= " ,l.present_type";
	$sql.= " ,(";
	$sql.= "   CASE";
	$sql.= "   WHEN l.present_type = 0 THEN l.avatar_id ";
	$sql.= "   WHEN l.present_type = 1 THEN l.item_id ";
	$sql.= "   ELSE '0'";
	$sql.= "   END";
	$sql.= " ) AS pid";
	$sql.= " FROM gamification_student_tmp st";
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_PRESENT_LOG." l";
	$sql.= "  ON st.student_id = l.student_id";
	$sql.= "  AND st.school_id = l.school_id";
	$sql.= "  AND l.mk_flg = 0 AND move_flg = 0";
	$sql.= "  AND l.regist_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}

}

/**
 * [CSV生成]ポイントログ
 * 
 * @param  array  $post $_POST
 * @return string $csv
 */
function point_log_csv($post){

	$csv = null;

	$csv .= "集計日時\t";
	$csv .= "生徒ID\t";
	$csv .= "校舎ID\t";
	$csv .= "出力理由\t";
	$csv .= "ポイント\t";
	$csv .= "アイテムID/アチーブエッグID";
	$csv .= "\n";

	$gcdb = $GLOBALS['game_cdb'];

	$sql = "SELECT";
	$sql.= "  l.regist_date";
	$sql.= " ,st.student_id";
	$sql.= " ,st.school_id";
	$sql.= " ,(";
	$sql.= "  CASE";
	$sql.= "   WHEN log_type = 'training' THEN 'トレーニング'";
	$sql.= "   WHEN log_type = 'meal' THEN 'エサ'";
	$sql.= "   WHEN log_type = 'buy' THEN 'ショップ購入'";
	$sql.= "   WHEN log_type = 'buy_present' THEN 'ショップ→プレゼント'";
	$sql.= "   WHEN log_type = 'achieve_egg' THEN 'アチーブエッグ達成'";
	$sql.= "   WHEN log_type = 'team_bonus' THEN 'チームボーナス達成'";
	$sql.= "   ELSE ''";
	$sql.= "   END";
	$sql.= "  ) log_type_jpn";
	$sql.= " ,l.point";
	$sql.= " ,(";
	$sql.= "   CASE";
	$sql.= "   WHEN l.log_type = 0 THEN l.avatar_id ";
	$sql.= "   WHEN l.log_type = 1 THEN l.item_id ";
	$sql.= "   ELSE '0'";
	$sql.= "   END";
	$sql.= " ) AS pid";
	$sql.= " FROM gamification_student_tmp st";
	if(isset($post['include_nostudy'])){
		$sql.= " LEFT JOIN ";
	}else{
		$sql.= " INNER JOIN ";
	}
	$sql.= " ".T_STUDENT_POINT_LOG." l";
	$sql.= "  ON st.student_id = l.student_id";
	$sql.= "  AND l.mk_flg = 0";
	$sql.= "  AND l.regist_date BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
	$sql.= " WHERE 1";
	if(isset($post['filter_school_id']) && $post['filter_school_id'] != ""){
		$sql.= "  AND st.school_id = '".$post['filter_school_id']."'"; 
	}

	if ($result = $gcdb->query($sql)) {
		if($gcdb->num_rows($result) == 0){ return null; }
		$csv .= gamification_log_build_csv_body($gcdb, $result);
		return $csv;
	}else{
		return null;
	}

}

/**
 * コア側学習データを各分散DBから取得してくる
 * 
 * @param  array $post            $_POST
 * @return array $core_study_data
 */
function get_study_data_from_core_dbs($post){
	
	global $L_GAMIFICATION_LOG_DB_CORE;

	$core_study_data = null;

	$gcdb = $GLOBALS['game_cdb'];

	//DB <=> 校舎
	$db_school_list = null;

	$sql = "SELECT DISTINCT db_id, school_id FROM gamification_student_tmp";

	if ($result = $gcdb->query($sql)) {
		while ($list = $gcdb->fetch_assoc($result)) {
			$db_school_list[$list['db_id']][] = $list['school_id'];
		}
		$gcdb->free_result($result);
	}
	unset($sql, $result, $list);

	if(!empty($db_school_list)){
		if(is_array($db_school_list)){
			foreach($db_school_list as $db_id => $school_id_list){
				if(!empty($school_id_list)){
					if(is_array($school_id_list)){

						$cdb = new connect_db();
						$cdb->set_db($L_GAMIFICATION_LOG_DB_CORE[$post['dbselect']][$db_id]);
						$ERROR = $cdb->set_connect_db();
						if(!empty($ERROR)){
							return null;
						}

						//その日にクリアしたユニット番号
						$sql = "SELECT DISTINCT st.school_id, st.student_id, fu.unit_num, DATE(regist_time) regist_date";
						$sql.= " FROM ".T_FINISH_UNIT." fu";
						$sql.= " INNER JOIN ".T_STUDENT." st";
						$sql.= "  ON st.student_id = fu.student_id";
						// add start hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策
						$sql.= "  LEFT JOIN (";
						$sql.= "    SELECT sut.student_id, sut.unit_num, sut.review, MAX(sut.giveup) AS giveup";
						$sql.= "    FROM ".T_STUDY_UNIT_TIME . " sut";
						$sql.= "    INNER JOIN ".T_STUDENT." st2 ON st2.student_id = sut.student_id";
						$sql.= "    WHERE sut.state = '0' AND sut.move_flg = '0'";
						$sql.= "    AND st2.school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "    GROUP BY sut.student_id, sut.unit_num, sut.review ORDER BY NULL";
						$sql.= "  ) giveup_tmp ON giveup_tmp.student_id = fu.student_id AND giveup_tmp.unit_num = fu.unit_num AND giveup_tmp.review = fu.review ";
						//add end hasegawa 2019/11/06
						$sql.= " WHERE 1";
						$sql.= "  AND st.school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND st.mk_flg = 0";
						$sql.= "  AND fu.regist_time BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
						$sql.= "  AND fu.move_flg = 0";
						$sql.= "  AND fu.clear_state = 0";
						$sql.= "  AND fu.giveup_block_num IS NULL";
						$sql.= "  AND fu.course_num NOT IN (5,7,10,6)";				// add hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策 TOEIC/英単語熟語マスタ以外
						$sql.= "  AND (giveup_tmp.giveup = '0' OR giveup_tmp.giveup IS NULL)";	// add hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策

						//,でユニット番号を連結した文字列でデータ保持
						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$core_study_data['finish_unit'][$list['school_id']][$list['student_id']][$list['regist_date']] .= $list['unit_num'].",";
							}
							$cdb->free_result($result);
						}

						// del start hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策 ↓に移動
						// //最後の,を取り除く
						// if(!empty($core_study_data['finish_unit']) && is_array($core_study_data['finish_unit'])){
						// 	foreach($core_study_data['finish_unit'] as $fu_school_id => $fu_student_list){
						// 		if(!empty($fu_student_list) && is_array($fu_student_list)){
						// 			foreach($fu_student_list as $fu_student_id => $fu_date_list){
						// 				if(!empty($fu_date_list) && is_array($fu_date_list)){
						// 					foreach($fu_date_list as $fu_date => $unit_nums_string){
						// 						$core_study_data['finish_unit'][$fu_school_id][$fu_student_id][$fu_date] = rtrim($unit_nums_string, ",");
						// 					}
						// 				}
						// 			}
						// 		}
						// 	}
						// }
						// del start hasegawa 2019/11/06

						unset($sql, $list, $result);

						//その日の総学習時間（秒）
						$sql = "SELECT school_id, student_id, study_date, study_time";
						$sql.= " FROM ".T_STUDY_TOTAL_TIME_DAILY_SUMMARY;
						$sql.= " WHERE school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND study_date BETWEEN '{$post['log_start_date']}' AND '{$post['log_end_date']}'";

						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$core_study_data['study_total_time_daily_summary'][$list['school_id']][$list['student_id']][$list['study_date']] = $list['study_time'];
							}
							$cdb->free_result($result);
						}

						unset($sql, $list, $result);

						//その日の合計クリアユニット数(TOEIC以外)
						$sql = "SELECT school_id, student_id, study_date, SUM(clear_unit_count) AS clear_unit_count";
						$sql.= " FROM ".T_FINISH_UNIT_DAILY_SUMMARY;
						$sql.= " WHERE school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND study_date BETWEEN '{$post['log_start_date']}' AND '{$post['log_end_date']}'";
						$sql.= "  AND course_num NOT IN(5,7,10)";
						// $sql.= " GROUP BY school_id, student_id";	// upd hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策 // 日ごとに取れてない
						$sql.= " GROUP BY school_id, student_id, study_date";
						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$core_study_data['finish_unit_daily_summary'][$list['school_id']][$list['student_id']][$list['study_date']] = $list['clear_unit_count'];
							}
							$cdb->free_result($result);
						}
						unset($sql, $list, $result);

						// add start hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策
						// その日のクリアユニット(TOEIC)
						// 指定期間内に学習したユニットを日ごとに取得
						$sql = " SELECT st.school_id, fu.* FROM ".T_FINISH_UNIT." fu "
							." INNER JOIN ".T_STUDENT." st ON st.student_id = fu.student_id"
							." AND st.move_flg = 0 AND st.mk_flg = 0"
							." AND st.school_id IN ('".implode("','", $school_id_list)."')"
							." WHERE fu.course_num IN (5,7,10)"
							." AND fu.clear_state = 0"
							." AND DATE(fu.regist_time) BETWEEN '{$post['log_start_date']}' AND '{$post['log_end_date']}'"
							." AND fu.state = 0 AND fu.move_flg = 0"
							.";";

						$L_TOEIC_RESULTS = array();
						$L_TOEIC_CLEAR = array();

						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$L_TOEIC_RESULTS[$list['school_id']][$list['student_id']][$list['unit_num']][] = $list['regist_time'];
							}
							$cdb->free_result($result);
						}
						unset($sql, $list, $result);

						if (count($L_TOEIC_RESULTS) > 0) {

							$L_TOEIC_LESSON = array();
							$L_TOEIC_UNIT = array();

							// TOEICのマスタ情報を取得
							$sql = " SELECT lesson_num, unit_num FROM ".T_UNIT
								." WHERE state='0' AND display='1'"
								." AND course_num IN ('5', '7', '10');";

							if ($result = $cdb->query($sql)) {
								while ($list = $cdb->fetch_assoc($result)) {
									$L_TOEIC_LESSON[$list['unit_num']] = $list['lesson_num'];
									$L_TOEIC_UNIT[$list['lesson_num']][$list['unit_num']] = $list['unit_num'];
								}
								$cdb->free_result($result);
							}
							unset($sql, $list, $result);

							if (count($L_TOEIC_LESSON) > 0 && count($L_TOEIC_UNIT) > 0) {

								foreach ($L_TOEIC_RESULTS as $school_id => $v1) {
									foreach ($v1 as $student_id => $v2) {
										foreach ($v2 as $unit_num => $v3) {
											foreach ($v3 as $regist_time) {

												// クリアしたユニットのクリア日時以前で、
												// LESSON内のユニットを全てクリアしているかをチェックする。
												$check_lesson = $L_TOEIC_LESSON[$unit_num];
												$clear_flg = true;
												$clear_date = date("Y-m-d", strtotime($regist_time));

												foreach ($L_TOEIC_UNIT[$check_lesson] as $check_unit) {
													// 自分自身だったらスキップ
													if ($check_unit == $unit_num) {
														continue;
													}
													// 一つでもクリアがなければ終了
													if (!$L_TOEIC_RESULTS[$school_id][$student_id][$check_unit]) {
														$clear_flg = false;
														break;
													}

													$time_check = false;
													foreach ($L_TOEIC_RESULTS[$school_id][$student_id][$check_unit] as $check_regist_time)  {

														if (strtotime($regist_time) > strtotime($check_regist_time)) {
															$time_check = ture;
															break;
														}
													}
													if (!$time_check) {
														$clear_flg = false;  
														break;
													}
												}

												// レッスン内ユニットのクリアが確認できた場合は加算
												// （同日中に同じレッスンをクリアしている場合も1とする）
												if ($clear_flg) {
													$L_TOEIC_CLEAR[$school_id][$student_id][$clear_date][$check_lesson] = 1;
												}
											}
										}
									}
								}
							}
						}

						if (count($L_TOEIC_CLEAR)>0) {
							foreach($L_TOEIC_CLEAR as $school_id => $v1) {
								foreach($v1 as $student_id => $v2) {
									foreach($v2 as $clear_date => $v3) {
										foreach($v3 as $lesson_num => $v4) {

											// finish_unit
											$core_study_data['finish_unit'][$school_id][$student_id][$clear_date] .= 'toeic:'.$lesson_num.",";

											// finish_unit_daily_summary
											if (!$core_study_data['finish_unit_daily_summary'][$school_id][$student_id][$clear_date]) {
												$core_study_data['finish_unit_daily_summary'][$school_id][$student_id][$clear_date] = 1;
											} else {
												$core_study_data['finish_unit_daily_summary'][$school_id][$student_id][$clear_date]++;
											}

										}
									}
								}
							}
						}
						unset($L_TOEIC_CLEAR);

						// その日のクリアユニット 英単語熟語マスタ
						$sql = "SELECT DISTINCT st.school_id, st.student_id, fu.unit_num, DATE(regist_time) regist_date";
						$sql.= " FROM ".T_FINISH_UNIT." fu";
						$sql.= " INNER JOIN ".T_STUDENT." st";
						$sql.= "  ON st.student_id = fu.student_id";
						// add start hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策
						$sql.= "  LEFT JOIN (";
						$sql.= "    SELECT sut.student_id, sut.unit_num, sut.review, SUM(sut.answer_count) AS answer_count, SUM(sut.correct_count) AS correct_count";
						$sql.= "    FROM ".T_STUDY_UNIT_TIME . " sut";
						$sql.= "    INNER JOIN ".T_STUDENT." st2 ON st2.student_id = sut.student_id";
						$sql.= "    WHERE sut.state = '0' AND sut.move_flg = '0'";
						$sql.= "    AND st2.school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "    GROUP BY sut.student_id, sut.unit_num, sut.review ORDER BY NULL";
						$sql.= "  ) clear_tmp ON clear_tmp.student_id = fu.student_id AND clear_tmp.unit_num = fu.unit_num AND clear_tmp.review = fu.review ";
						//add end hasegawa 2019/11/06
						$sql.= " WHERE 1";
						$sql.= "  AND st.school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND st.mk_flg = 0";
						$sql.= "  AND fu.regist_time BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";
						$sql.= "  AND fu.move_flg = 0";
						$sql.= "  AND fu.clear_state = 0";
						$sql.= "  AND fu.giveup_block_num IS NULL";
						$sql.= "  AND fu.course_num IN (6)";				// add hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策 TOEIC/英単語熟語マスタ以外
						$sql.= "  AND clear_tmp.answer_count = clear_tmp.correct_count";	// add hasegawa 2019/11/06 すらら学習時間・クリアユニット数不正対策

						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$core_study_data['finish_unit'][$list['school_id']][$list['student_id']][$list['regist_date']] .= $list['unit_num'].",";
							}
							$cdb->free_result($result);
						}
						unset($sql, $list, $result);


						//最後の,を取り除く
						if(!empty($core_study_data['finish_unit']) && is_array($core_study_data['finish_unit'])){
							foreach($core_study_data['finish_unit'] as $fu_school_id => $fu_student_list){
								if(!empty($fu_student_list) && is_array($fu_student_list)){
									foreach($fu_student_list as $fu_student_id => $fu_date_list){
										if(!empty($fu_date_list) && is_array($fu_date_list)){
											foreach($fu_date_list as $fu_date => $unit_nums_string){
												$core_study_data['finish_unit'][$fu_school_id][$fu_student_id][$fu_date] = rtrim($unit_nums_string, ",");
											}
										}
									}
								}
							}
						}
						// add end hasegawa 2019/11/06

						//その日使用制限がかかった日時
						$sql = "SELECT school_id, student_id, DATE(regist_time) regist_date, regist_time";
						$sql.= " FROM ".T_STUDENT_GAMIFICATION_RESTRICTION_LOG." fu";
						$sql.= " WHERE 1";
						$sql.= "  AND school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND restriction_type = 1";
						$sql.= "  AND mk_flg = 0";
						$sql.= "  AND move_flg = 0";
						$sql.= "  AND regist_time BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";

						//,でユニット番号を連結した文字列でデータ保持
						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								$core_study_data['restriction1_log'][$list['school_id']][$list['student_id']][$list['regist_date']] .= $list['regist_time'].",";
							}
							$cdb->free_result($result);
						}
						//最後の,を取り除く
						if(!empty($core_study_data['restriction1_log']) && is_array($core_study_data['restriction1_log'])){
							foreach($core_study_data['restriction1_log'] as $rs_school_id => $rs_student_list){
								if(!empty($rs_student_list) && is_array($rs_student_list)){
									foreach($rs_student_list as $rs_student_id => $rs_date_list){
										if(!empty($rs_date_list) && is_array($rs_date_list)){
											foreach($rs_date_list as $rs_date => $regist_time_string){
												$core_study_data['restriction1_log'][$rs_school_id][$rs_student_id][$rs_date] = rtrim($regist_time_string, ",");
											}
										}
									}
								}
							}
						}
						unset($sql, $list, $result);

						//その日一時停止解除された日時
						$sql = "SELECT school_id, student_id, DATE(regist_time) regist_date, regist_time";
						$sql.= " FROM ".T_STUDENT_GAMIFICATION_RESTRICTION_LOG." fu";
						$sql.= " WHERE 1";
						$sql.= "  AND school_id IN ('".implode("','", $school_id_list)."')";
						$sql.= "  AND restriction_type = 2";
						$sql.= "  AND mk_flg = 0";
						$sql.= "  AND move_flg = 0";
						$sql.= "  AND regist_time BETWEEN '{$post['log_start_date']} 00:00:00' AND '{$post['log_end_date']} 23:59:59'";

						//,でユニット番号を連結した文字列でデータ保持
						if ($result = $cdb->query($sql)) {
							while ($list = $cdb->fetch_assoc($result)) {
								// upd start hasegawa 2019/08/06 生徒画面TOP改修_ログ出力
								// $core_study_data['restriction2_log'][$list['school_id']][$list['student_id']][$list['regist_date']] .= $list['regist_time'].",";
								// 解除されたログはその日の初回ログイン時に入ってしまうので、その日のうちに制限されていなければ出力しない
								if ($core_study_data['restriction1_log'][$list['school_id']][$list['student_id']][$list['regist_date']]) {
									$restriction1_log_arr = array();
									$time_exist = false;
									$restriction1_log_arr = explode(',',$core_study_data['restriction1_log'][$list['school_id']][$list['student_id']][$list['regist_date']]);
									foreach($restriction1_log_arr as $k => $v) {
										$restriction1_log_arr[$k] = strtotime($v);
									}
									sort($restriction1_log_arr);
									if ($restriction1_log_arr[0] < strtotime($list['regist_time'])) {
										$time_exist = true;
									}

									if ($time_exist) {
										$core_study_data['restriction2_log'][$list['school_id']][$list['student_id']][$list['regist_date']] .= $list['regist_time'].",";
									}
								}
								// upd end hasegawa 2019/08/06
								
							}
							$cdb->free_result($result);
						}

						//最後の,を取り除く
						if(!empty($core_study_data['restriction2_log']) && is_array($core_study_data['restriction2_log'])){
							foreach($core_study_data['restriction2_log'] as $rs_school_id => $rs_student_list){
								if(!empty($rs_student_list) && is_array($rs_student_list)){
									foreach($rs_student_list as $rs_student_id => $rs_date_list){
										if(!empty($rs_date_list) && is_array($rs_date_list)){
											foreach($rs_date_list as $rs_date => $regist_time_string){
												$core_study_data['restriction2_log'][$rs_school_id][$rs_student_id][$rs_date] = rtrim($regist_time_string, ",");
											}
										}
									}
								}
							}
						}
						unset($sql, $list, $result);


						$cdb->close();
						unset($cdb);
					}
				}
			}
		}
	}

	return $core_study_data;
}

/**
 * mysqlの結果からCSV(値部分)を生成
 * 厳密にはTSV...
 * 
 * @param  object $gcdb   ゲーミフィケーションDB接続オブジェクト
 * @param  object $result mysqliリザルトセット
 * @return string $csv
 */
function gamification_log_build_csv_body($gcdb, $result){

	$csv = "";
	while ($list = $gcdb->fetch_assoc($result)) {
		if(is_array($list)){
			foreach($list as $v){
				$csv .= $v."\t";
			}
			$csv = rtrim($csv, "\t");
			$csv.= "\n";
		}
	}
	return $csv;
}

//-------------------------------------------------

//select結果ををhtmlでみる
function sql_to_html_table($sql, $cdb){
	if(!preg_match("/^\s*SELECT/i", $sql)){ return -1; }
	$html = "<table border=1 style='background:#fff; border-collapse:collapse; font-size:10px;'>";
	if(!$res = $cdb->query($sql)){ return $cdb->db->error; }
	$finfo = $res->fetch_fields();
	$html.="<tr style=background:palegreen>";
	foreach ($finfo as $val) { $html.="<td>".$val->table.".".$val->name."</td>"; } //upd 2019/05/15
	$html.="</tr>";
	// while ($list = $cdb->fetch_assoc($res)) { //del 2019/05/15
	while ($list = $res->fetch_row()) { //upd 2019/05/15
		$html.="<tr>";
		foreach($list as $val){ $html.="<td>{$val}</td>"; }
		$html.="</tr>";
	}
	$html.="</table>";
	return $html;
}
