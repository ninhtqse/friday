<?php
/**
 * オプション
 *
 * 無し
 *
 * example:
 * php MOVE_ENTERPRISE.php 10.3.11.100 > move_enterprise.txt
 *
 * 法人単位のデータを収集し、移動先の分散DBに全て移動する
 *
 */
//コンフィグ start------------------------------
include("/data/bat/db_list.php");
include("/data/bat/db_table.php");
include("/data/bat/db_connect.php");

MoveEnterprise::print_mode(true);	// デバッグモード（OFFにする時は、コメントにする）

$dbs = array();

$ip_addr = $argv[1];

MoveEnterprise::debug_print("argv=".print_r($argv,true));
MoveEnterprise::debug_print("server_adder=".$ip_addr);
// 環境ごとに接続DBを設定します
switch ($ip_addr){
	// 開発Webサーバ
	case "10.3.11.100":
		$dbs['db01'] = $L_DB['srlctd3101'];		//開発コアDB -> SRLCK01
		$dbs['db02'] = $L_DB['srlctd3102'];		//開発コアDB -> SRLCK02
		$dbs['db03'] = $L_DB['srlctd3103'];		//開発コアDB -> SRLCK03
		$dbs['db04'] = $L_DB['srlctd3104'];		//開発コアDB -> SRLCK04
		$dbs['db05'] = $L_DB['srlctd3105'];		//開発コアDB -> SRLCK05
		$dbs['db06'] = $L_DB['srlctd3106'];		//開発コアDB -> SRLCK06
		$dbs['db07'] = $L_DB['srlctd3107'];		//開発コアDB -> SRLCK07
		$dbs['db08'] = $L_DB['srlctd3108'];		//開発コアDB -> SRLCK08
		$dbs['db09'] = $L_DB['srlctd3109'];		//開発コアDB -> SRLCK09
		$dbs['db10'] = $L_DB['srlctd3110'];		//開発コアDB -> SRLCK10
		$dbs['db11'] = $L_DB['srlctd3111'];		//開発コアDB -> SRLCK11
		$dbs['db12'] = $L_DB['srlctd3112'];		//開発コアDB -> SRLCK12
		$dbs['db13'] = $L_DB['srlctd3113'];		//開発コアDB -> SRLCK13
// 		$dbs['db14'] = $L_DB['srlctd3114'];		//開発コアDB -> SRLCK14
// 		$dbs['db15'] = $L_DB['srlctd3115'];		//開発コアDB -> SRLCK15
		$sougou_db   = $L_DB['srlctd03SOGO'];	//総合DB
		$togo_db     = $L_DB['srlcmd01'];		//統合DB（Entry）
		$game_db     = $L_DB['srlgkd01'];		//ゲーミフィケーションDB
		break;
	// 検証バッチサーバ
	case "10.2.41.10":
		$dbs['db01'] = $L_DB['srlctd0201'];		//検証分散DB#1
		$dbs['db02'] = $L_DB['srlctd0202'];		//検証分散DB#2
		$dbs['db03'] = $L_DB['srlctd0203'];		//検証分散DB#3
		$dbs['db04'] = $L_DB['srlctd0204'];		//検証分散DB#4
		$dbs['db05'] = $L_DB['srlctd0305'];		//検証分散DB#5
		$dbs['db06'] = $L_DB['srlctd0306'];		//検証分散DB#6
		$dbs['db07'] = $L_DB['srlctd0307'];		//検証分散DB#7
		$dbs['db08'] = $L_DB['srlctd0308'];		//検証分散DB#8
		$dbs['db09'] = $L_DB['srlctd0309'];		//検証分散DB#9
		$dbs['db10'] = $L_DB['srlctd0310'];		//検証分散DB#10
		$dbs['db11'] = $L_DB['srlctd0311'];		//検証分散DB#11
		$dbs['db12'] = $L_DB['srlctd0312'];		//検証分散DB#12
		$dbs['db13'] = $L_DB['srlctd0313'];		//検証分散DB#13
// 		$dbs['db14'] = $L_DB['srlctd0314'];		//検証分散DB#14
// 		$dbs['db15'] = $L_DB['srlctd0315'];		//検証分散DB#15
		$sougou_db   = $L_DB['srlctd0307'];		//総合DB : 検証分散DB#7
		$togo_db     = $L_DB['srlctd01'];		//統合DB（Entry）
		$game_db     = $L_DB['srlgsd01'];		//ゲーミフィケーションDB
		break;
	// 本番バッチサーバ
	case "10.1.41.10":
		$dbs['db01'] = $L_DB['srlchd02'];		//本番分散DB#1
		$dbs['db02'] = $L_DB['srlchd03'];		//本番分散DB#2
		$dbs['db03'] = $L_DB['srlchd04'];		//本番分散DB#3
		$dbs['db04'] = $L_DB['srlchd05'];		//本番分散DB#4
		$dbs['db05'] = $L_DB['srlchd06'];		//本番分散DB#5
		$dbs['db06'] = $L_DB['srlchd07'];		//本番分散DB#6
		$dbs['db07'] = $L_DB['srlchd08'];		//本番分散DB#7
		$dbs['db08'] = $L_DB['srlchd09'];		//本番分散DB#8
		$dbs['db09'] = $L_DB['srlchd10'];		//本番分散DB#9
		$dbs['db10'] = $L_DB['srlchd11'];		//本番分散DB#10
		$dbs['db11'] = $L_DB['srlchd12'];		//本番分散DB#11
		$dbs['db12'] = $L_DB['srlchd13'];		//本番分散DB#12
		$dbs['db13'] = $L_DB['srlchd14'];		//本番分散DB#13
// 		$dbs['db14'] = $L_DB['srlchd15'];		//本番分散DB#14
// 		$dbs['db15'] = $L_DB['srlchd16'];		//本番分散DB#15
		$sougou_db   = $L_DB['srlchd08'];		//総合DB : 本番分散DB#7
		$togo_db     = $L_DB['srlchd01'];		//統合DB（Entry）
		$game_db     = $L_DB['srlghd01'];		//ゲーミフィケーションDB
		break;
	default:
		break;
}


// メイン処理
// 1.インスタンス移動対象の法人を総合DBから取得する
// 2.取得した法人のチェックを行う
//   移動先のDBインスタンスが同じではない。
//   移動元の分散DBに法人の情報が存在する
// 3.法人に紐づく情報を移転用のDBにコピーする
// 4.移転用のDBをmysqldumpする
// 5.dumpファイルを移転先のDBインスタンス移転用のDBにリストアする
// 6.移転先のテーブルにデータをINERTする
//   （INSERTの際、AUTOINCRIMENTの値を保持し、採番前の情報を取得できる様にする
// 7.再附番した番号を関連テーブルに更新する
// 8.統合DBのenterpriseとゲーミフィケーションのschool_infoのdbidを更新する
// 9.移動元のデータは削除フラグを立てる
// 10.移動先のLOGEVACを起動する

// 処理日
$today = date("Y-m-d");

//メイン処理 start------------------------------
MoveEnterprise::debug_print("[".date("Y-m-d H:i:s")."]");
MoveEnterprise::debug_print("::::::::::::::::::::バッチ実行開始::::::::::::::::::::");
MoveEnterprise::debug_print(print_r($sougou_db, true));
MoveEnterprise::debug_print(print_r($togo_db, true));

// データを移すテーブルを設定
MoveEnterprise::set_tables();

$sougou_connect_db = new connect_db();
$sougou_connect_db->set_db($sougou_db);
$ERROR = $sougou_connect_db->set_connect_db();
MoveEnterprise::set_sougou_db_connection($sougou_connect_db); //DBセット

if($ERROR){ MoveEnterprise::log(null, null, "DB接続失敗", $sougou_connect_db); exit(1); }

$togo_connect_db = new connect_db();
$togo_connect_db->set_db($togo_db);
$ERROR = $togo_connect_db->set_connect_db();
MoveEnterprise::set_togo_db_connection($togo_connect_db); //DBセット

if($ERROR){ MoveEnterprise::log(null, null, "DB接続失敗", $togo_connect_db); exit(1); }

$game_connect_db = new connect_db();
$game_connect_db->set_db($game_db);
$ERROR = $game_connect_db->set_connect_db();
MoveEnterprise::set_game_db_connection($game_connect_db); //DBセット

if($ERROR){ MoveEnterprise::log(null, null, "DB接続失敗", $game_connect_db); exit(1); }


// 移動対象の法人を取得する
$enterprise_list = MoveEnterprise::getHojinList($today);

// 対象のデータが存在しない場合は終了
if (is_array($enterprise_list) && count($enterprise_list) == 0) {
	MoveEnterprise::log(null, null, "対象レコード無し", $sougou_connect_db); exit(1);
}

// 対象の法人ごとに処理を行う
foreach($enterprise_list as $enterprise){

	// 統合DBから法人のDB情報を取得する
	$ent_db_info = MoveEnterprise::getHojinInfo($enterprise['enterprise_id']);

	// 現在のDBと移動先のDBが同じならばエラー
	if ($enterprise['move_db_id'] == $ent_db_info['db_id']) {
		MoveEnterprise::saveDdata($enterprise['enterprise_id'], $today, "移動元と移動先が同じ");
		continue;	// 次の法人へ
	}

	MoveEnterprise::debug_print("========= 移動元DB ============");
	MoveEnterprise::debug_print(print_r($dbs[$ent_db_info['db_id']], true));
	MoveEnterprise::debug_print("========= 移動先DB ============");
	MoveEnterprise::debug_print(print_r($dbs[$enterprise['move_db_id']], true));

	// 移動元と移動先のDBコネクションを作成する
	$connect_moto_db = new connect_db();
	$connect_moto_db->set_db($dbs[$ent_db_info['db_id']]);
	$ERROR = $connect_moto_db->set_connect_db();
	if($ERROR){ self::log(null, null, "DB接続失敗", $connect_moto_db); exit(1); }
	MoveEnterprise::set_db_connection_moto($connect_moto_db); //DBセット

	$connect_saki_db = new connect_db();
	$connect_saki_db->set_db($dbs[$enterprise['move_db_id']]);
	$ERROR = $connect_saki_db->set_connect_db();
	if($ERROR){ self::log(null, null, "DB接続失敗", $connect_saki_db); exit(1); }
	MoveEnterprise::set_db_connection_saki($connect_saki_db); //DBセット

	// 移動元の法人ID／校舎ID／先生ID／保護者ID／生徒IDのテーブルを作成する
	MoveEnterprise::createKey($connect_moto_db, $enterprise['enterprise_id']);

	// 移動元の法人ID／校舎ID／先生ID／保護者ID／生徒IDのテーブルを転送する
	MoveEnterprise::exportKey($dbs[$ent_db_info['db_id']], $dbs[$enterprise['move_db_id']], $enterprise['enterprise_id']);

	// 移動元のデータ収集
	$after_drop_tables = array();
	$table_list = MoveEnterprise::get_tables();
	foreach($table_list as $table) {

		$class_file = "/data/bat/moveTables/".$table.".class.php";

		if (file_exists ( $class_file )) {
			include_once ($class_file);
			$tableObj = new $table ($enterprise['enterprise_id']);

			MoveEnterprise::debug_print("======tableName:".$tableObj->getTableName()." ===");

			// 移動元からデータを抽出
			if ($tableObj->export($dbs[$ent_db_info['db_id']], $connect_moto_db)) {
				// 移動先へデータを移動
				$tableObj->dumpRestore($dbs[$enterprise['move_db_id']]);

				// 移動先のデータを削除
				$tableObj->delete($connect_saki_db);

				// 移動先のデータを追加
				$tableObj->add($connect_saki_db);

				// 移動先のテーブルを削除
				$after_drop_table = $tableObj->dropTable($connect_saki_db);
				if ($after_drop_table) {
					$after_drop_tables[] = $after_drop_table;
				}

				// 移動元のデータを削除
				$tableObj->deleteData($connect_moto_db);

			}
			$tableObj = null;
		}
	}

	// 後で削除する作業テーブルをDROPする
	if (count($after_drop_tables) > 0) {
		foreach ($after_drop_tables as $value) {
			if ($value != "") {
				$connect_saki_db->exec_query($value);
			}
		}
	}

	// ゲーミフィケーションDBのdb_idを更新
	MoveEnterprise::updateGameDbId($enterprise['enterprise_id'], $enterprise['move_db_id']);

	// 統合DBのdb_idを更新
	MoveEnterprise::updateTogoDbId($enterprise['enterprise_id'], $enterprise['move_db_id']);

	// 終了処理
	MoveEnterprise::saveDdata($enterprise['enterprise_id'], $today, "正常終了");

	//デバッグ表示
	MoveEnterprise::debug_print("======enterprise_id:".$enterprise['enterprise_id']." 処理終了===");

}

MoveEnterprise::debug_print("::::::::::::::::::::バッチ実行終了::::::::::::::::::::");
MoveEnterprise::debug_print("[".date("Y-m-d H:i:s")."]");

exit(0);
//メイン処理   end------------------------------

//クラス start----------------------------------
class MoveEnterprise{

	private static $connect_db_moto   = null;
	private static $connect_db_saki   = null;
	private static $sougou_connect_db = null; //総合DB用コネクションオブジェクト
	private static $togo_connect_db   = null; //統合DB用コネクションオブジェクト（Entry）
	private static $game_connect_db   = null; //ゲーミフィケーション用コネクションオブジェクト

	private static $PRINT_MODE        = false; //echoを出すモード
	private static $query_history     = false; //実行したSQLを格納

	private static $table_list        = array();

	/**
	 * 移動対象のテーブルを設定
	 * 配列の順番に処理を行う
	 */
	public static function set_tables(){
		self::$table_list[] = "enterprise";
		self::$table_list[] = "school";
		self::$table_list[] = "guardian";
		self::$table_list[] = "student";
		self::$table_list[] = "tb_stu_gtest_mskm";
		self::$table_list[] = "tb_stu_jyko_jyotai";
		self::$table_list[] = "tb_stu_mskm_crs";
		self::$table_list[] = "teacher";

		// evacはメインテーブルと一緒に処理します（AUTO_INCRIMENTを振りなおす必要がある為）
		self::$table_list[] = "tb_stu_jyko_jyotai_daily_log";				// evacを含む
// 		self::$table_list[] = "tb_stu_jyko_jyotai_daily_log_evac";


// target_infoは移行しない。
// 		self::$table_list[] = "target_info_group";
// 		self::$table_list[] = "target_info_group_evac";
// 		self::$table_list[] = "target_info_list";
// 		self::$table_list[] = "target_info_list_evac";
// 		self::$table_list[] = "target_info_test_choice";
// 		self::$table_list[] = "target_info_test_choice_evac";
// 		self::$table_list[] = "target_info_test_problem";
// 		self::$table_list[] = "target_info_test_problem_evac";

		self::$table_list[] = "study_record";

		self::$table_list[] = "target_study_group";							// evacを含む
// 		self::$table_list[] = "target_study_group_evac";
		self::$table_list[] = "target_study_list";							// evacを含む
// 		self::$table_list[] = "target_study_list_evac";
		self::$table_list[] = "target_study_test_choice";					// evacを含む
// 		self::$table_list[] = "target_study_test_choice_evac";
		self::$table_list[] = "target_study_test_problem";					// evacを含む
// 		self::$table_list[] = "target_study_test_problem_evac";

		self::$table_list[] = "question";								// evacを含む
// 		self::$table_list[] = "question_evac";

		self::$table_list[] = "drawing_log";								// evacを含む
//		self::$table_list[] = "drawing_log_evac";
		self::$table_list[] = "drawing_log_question";
		self::$table_list[] = "drawing_log_question_evac";
		self::$table_list[] = "drawing_log_surala";
		self::$table_list[] = "drawing_log_surala_evac";
		self::$table_list[] = "drawing_log_test";
		self::$table_list[] = "drawing_log_test_evac";

		self::$table_list[] = "announcement";								// evacを含む
//		self::$table_list[] = "announcement_evac";
		self::$table_list[] = "answer_data_flash";
		self::$table_list[] = "answer_data_flash_evac";
		self::$table_list[] = "answer_data_game";
		self::$table_list[] = "answer_data_game_evac";

		self::$table_list[] = "answer_data_skill";
		self::$table_list[] = "answer_data_skill_evac";
		self::$table_list[] = "answer_data_unit";
		self::$table_list[] = "answer_data_unit_evac";

		self::$table_list[] = "syosya_log";							// evacを含む
// 		self::$table_list[] = "syosya_log_evac";
		self::$table_list[] = "syosya_log_surala";					// evacを含む
// 		self::$table_list[] = "syosya_log_surala_evac";
		self::$table_list[] = "syosya_log_test";					// evacを含む
// 		self::$table_list[] = "syosya_log_test_evac";
		self::$table_list[] = "cheating_log";								// evacを含む
//		self::$table_list[] = "cheating_log_evac";
		self::$table_list[] = "check_study_record";
// 		self::$table_list[] = "check_study_record_evac";					// evacを含む
// 		self::$table_list[] = "chieru_onetimepass";			// 未使用
// 		self::$table_list[] = "chieru_onetimepass_evac";	// 未使用
// 		self::$table_list[] = "classroom";					// 未使用
		self::$table_list[] = "cue";										// evacを含む
//		self::$table_list[] = "cue_evac";
		self::$table_list[] = "display_again_lecture_log";
//		self::$table_list[] = "display_again_lecture_log_evac";
		self::$table_list[] = "ext_onetimepass";
// 		self::$table_list[] = "external_surala_group";		// 未使用 Classiで利用予定だった
		self::$table_list[] = "external_surala_guardian";
		self::$table_list[] = "external_surala_school";
		self::$table_list[] = "external_surala_student";
		self::$table_list[] = "external_surala_teacher";
		self::$table_list[] = "finish_unit";
		self::$table_list[] = "finish_unit_daily_summary";
		self::$table_list[] = "finish_unit_daily_summary_evac";
		self::$table_list[] = "finish_unit_evac";
		self::$table_list[] = "hantei_data";
		self::$table_list[] = "hantei_data_evac";
		self::$table_list[] = "hantei_finish_data";
		self::$table_list[] = "hantei_finish_data_evac";
		self::$table_list[] = "loginlog";
		self::$table_list[] = "loginlog_evac";
		self::$table_list[] = "onetimepass";
		self::$table_list[] = "page_access_log_school";
		self::$table_list[] = "ranking";
// 		self::$table_list[] = "read_announcement";	// 未使用
		self::$table_list[] = "return_log";
		self::$table_list[] = "return_log_evac";
		self::$table_list[] = "school_general_switch";
		self::$table_list[] = "school_switch";
// 		self::$table_list[] = "seat_shortcut";				// 未使用
		self::$table_list[] = "set_test_type_1";
		self::$table_list[] = "set_test_type_4";
		self::$table_list[] = "start_skill";
		self::$table_list[] = "start_skill_evac";
		self::$table_list[] = "start_unit";
		self::$table_list[] = "start_unit_evac";
// 		self::$table_list[] = "student_affiliation";		// 統合DBなので移動無し
		self::$table_list[] = "student_approach_api_log";
		self::$table_list[] = "student_approach_info";
		self::$table_list[] = "student_approach_log";
		self::$table_list[] = "student_approach_log_evac";
		self::$table_list[] = "student_book";
		self::$table_list[] = "student_course_level";
		self::$table_list[] = "student_enterprise_daily_summary";
		self::$table_list[] = "student_enterprise_daily_summary_evac";
		self::$table_list[] = "student_gamification_config";
		self::$table_list[] = "student_gamification_config_change_log";
		self::$table_list[] = "student_gamification_restriction_log";			// evacを含む
// 		self::$table_list[] = "student_gamification_restriction_log_evac";
		self::$table_list[] = "student_gamification_use_log";
		self::$table_list[] = "student_gamification_use_log_evac";
		self::$table_list[] = "student_group";
		self::$table_list[] = "student_group_list";
		self::$table_list[] = "student_last_login_daily_summary";
		self::$table_list[] = "student_last_login_daily_summary_evac";
		self::$table_list[] = "student_loginlog";
		self::$table_list[] = "student_loginlog_daily_summary";
		self::$table_list[] = "student_loginlog_daily_summary_evac";
		self::$table_list[] = "student_loginlog_evac";
		self::$table_list[] = "student_loginlog_toeic";
		self::$table_list[] = "student_loginlog_toeic_evac";
		self::$table_list[] = "student_para";
		self::$table_list[] = "student_search_parameters";
		self::$table_list[] = "student_search_parameters_evac";
		self::$table_list[] = "student_stage_progress";
		self::$table_list[] = "study_flash_time";
		self::$table_list[] = "study_flash_time_evac";
		self::$table_list[] = "study_game_time";
		self::$table_list[] = "study_game_time_evac";
		self::$table_list[] = "study_skill_daily";
		self::$table_list[] = "study_skill_time";
		self::$table_list[] = "study_skill_time_evac";
		self::$table_list[] = "study_total_time";
		self::$table_list[] = "study_total_time_daily_summary";
		self::$table_list[] = "study_total_time_daily_summary_evac";
		self::$table_list[] = "study_total_time_evac";
		self::$table_list[] = "study_unit_time";
		self::$table_list[] = "study_unit_time_evac";
		self::$table_list[] = "teacher_enterprise_daily_summary";
		self::$table_list[] = "teacher_enterprise_daily_summary_evac";
		self::$table_list[] = "teacher_last_login_daily_summary";
		self::$table_list[] = "teacher_last_login_daily_summary_evac";
		self::$table_list[] = "teacher_loginlog";					// evacを含む
// 		self::$table_list[] = "teacher_loginlog_evac";
		self::$table_list[] = "teacher_progress_search";
		self::$table_list[] = "teacher_record";						// evacを含む
// 		self::$table_list[] = "teacher_record_evac";
		self::$table_list[] = "teacher_search_parameters";
		self::$table_list[] = "teacher_search_parameters_evac";
		self::$table_list[] = "teacher_use_student";
		self::$table_list[] = "test_data";
		self::$table_list[] = "test_data_0";
		self::$table_list[] = "test_data_choice";
		self::$table_list[] = "test_data_choice_evac";
		self::$table_list[] = "test_data_daily_summary";
		self::$table_list[] = "test_data_daily_summary_evac";
		self::$table_list[] = "test_data_daily_summary_vocabulary";
		self::$table_list[] = "test_data_daily_summary_vocabulary_evac";
		self::$table_list[] = "test_data_evac";
		self::$table_list[] = "test_data_problem";
		self::$table_list[] = "test_data_problem_evac";
		self::$table_list[] = "test_total_type1";
		self::$table_list[] = "test_total_type1_evac";
		self::$table_list[] = "test_total_type3";
		self::$table_list[] = "test_total_type3_evac";
		self::$table_list[] = "test_total_type3_option";
		self::$table_list[] = "test_total_type3_option_evac";
		self::$table_list[] = "test_total_type4";
		self::$table_list[] = "test_total_type4_evac";
		self::$table_list[] = "test_total_type5";
		self::$table_list[] = "test_total_type5_evac";
		self::$table_list[] = "test_total_type6";
		self::$table_list[] = "test_total_type6_evac";
		self::$table_list[] = "test_unit_setting";
		self::$table_list[] = "timeline_log";
		self::$table_list[] = "toeic_exam_score";
		self::$table_list[] = "toeic_exam_score_evac";
		self::$table_list[] = "trial_set_id";
		self::$table_list[] = "update_target_status_bat_log";
		self::$table_list[] = "weak_point_answer_count";
		self::$table_list[] = "weak_point_answer_count_evac";
		self::$table_list[] = "weak_point_drill";
		self::$table_list[] = "weak_point_test";

	}

	/**
	 * 処理対象のテーブル情報を取得する
	 * @return array テーブル情報
	 */
	public static function get_tables(){
		return self::$table_list;
	}

	/**
	 * デバッグモード設定
	 * @param boolean $on true:デバッグモード false:本番モード
	 */
	public static function print_mode($on){
		self::$PRINT_MODE = $on;
	}

	/**
	 * DB接続オブジェクトセット(移動元)
	 * @param object $connect_db DB接続オブジェクト
	 */
	public static function set_db_connection_moto($connect_db_){
		self::$connect_db_moto = $connect_db_;
	}

	/**
	 * DB接続オブジェクトセット(移動先)
	 * @param object $connect_db DB接続オブジェクト
	 */
	public static function set_db_connection_saki($connect_db_){
		self::$connect_db_saki = $connect_db_;
	}

	/**
	 * 総合DB接続オブジェクトセット
	 * @param object $connect_db DB接続オブジェクト
	 */
	public static function set_sougou_db_connection($connect_db_){
		self::$sougou_connect_db = $connect_db_;
	}

	/**
	 * 統合DB(Entry)接続オブジェクトセット
	 * @param object $connect_db DB接続オブジェクト
	 */
	public static function set_togo_db_connection($connect_db_){
		self::$togo_connect_db = $connect_db_;
	}

	/**
	 * ゲーミフィケーション接続オブジェクトセット
	 * @param object $connect_db DB接続オブジェクト
	 */
	public static function set_game_db_connection($connect_db_){
		self::$game_connect_db = $connect_db_;
	}

	/**
	 * ログ出力メソッド
	 *
	 * @param string $function_name
	 * @param mixed $query_history
	 * @param mixed $connection
	 * @access public
	 * @return void
	 */
	public static function log($func_name, $sql, $message, $conn){
		$text = "";
		$text.= "[".date("Y-m-d H:i:s")."]\n";
		$text.= "対象DB:".$conn->dbhost." (".$conn->dbname.")\n";
		$text.= "ルーチン:".$func_name."\n";
		$text.= "エラーメッセージ:".$message."\n";
		$text.= "実行クエリ:".$sql."\n";
		$text.= "MySQLエラー:(コード:".$conn->db->errno.")".$conn->db->error."\n";
		$text.= "----------------------------------------------------------------\n";
		self::debug_print("エラー:".$conn->dbname."の処理を中止しました。");
		error_log($text, 3, "/data/bat/move_enterprise/".date("Ymd").".txt");
	}

	/**
	 * 移動対象の法人一覧を取得する
	 * @param string 処理日（本日）
	 * @return array 法人一覧
	 */
	public static function getHojinList($today) {

		$enterprise_list = array();

		// 法人一覧取得
		$sql  = " SELECT";
		$sql .= "  enterprise_id ";
		$sql .= " ,move_db_id ";
		$sql .= " FROM mv_enterprise ";
		$sql .= " WHERE exec_date = '".$today."'";
		$sql .= "   AND syrz_flg = '0'";
		$sql .= "   AND mk_flg = '0'";
		$sql .= ";";

		self::$query_history .= $sql.PHP_EOL;

		if($rs = self::$sougou_connect_db->query($sql)){
			while($list = self::$sougou_connect_db->fetch_assoc($rs)) {
				$enterprise_list[] = $list;
			}
		}
		self::$sougou_connect_db->free_result($rs);
		return $enterprise_list;
	}


	/**
	 * 法人の情報を取得する
	 * @param string $enterprise_id
	 * @return array 法人情報（存在しない場合はnull）
	 */
	public static function getHojinInfo($enterprise_id) {

		$enterprise = null;

		// DB接続先情報取得
		$sql  = " SELECT";
		$sql .= "   db_id ";
		$sql .= " FROM db_connect ";
		$sql .= " WHERE enterprise_id = '".$enterprise_id."'";
		$sql .= ";";

		self::$query_history .= $sql.PHP_EOL;

		if($rs = self::$togo_connect_db->query($sql)){
			while($list = self::$togo_connect_db->fetch_assoc($rs)) {
				$enterprise = $list;
			}
		}
		self::$togo_connect_db->free_result($rs);
		return $enterprise;
	}


	/**
	 * 抽出用のテーブルを作成する
	 * @param object $db 移動元のDBコネクション
	 * @param string $enterprise_id
	 */
	public static function createKey($db, $enterprise_id) {


		// 法人IDテーブル作成
		MoveEnterprise::debug_print("====== 法人IDテーブル作成 mv_0_enterprise ===");
		$sql = "DROP TABLE IF EXISTS mv_0_enterprise; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 法人IDテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_0_enterprise ";
		$sql .= " SELECT";
		$sql .= "   enterprise_id ";
		$sql .= " FROM enterprise ";
		$sql .= " WHERE enterprise_id = '".$enterprise_id."'";
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 法人IDテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		// 校舎IDテーブル作成
		MoveEnterprise::debug_print("====== 校舎IDテーブル作成 mv_1_school ===");
		$sql = "DROP TABLE IF EXISTS mv_1_school; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 校舎IDテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_1_school ";
		$sql .= " SELECT";
		$sql .= "   school_id ";
		$sql .= " FROM school ";
		$sql .= " WHERE enterprise_id = '".$enterprise_id."'";
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 校舎IDテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		// 先生IDテーブル作成
		MoveEnterprise::debug_print("====== 先生IDテーブル作成 mv_2_teacher ===");
		$sql = "DROP TABLE IF EXISTS mv_2_teacher; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 先生IDテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_2_teacher ";
		$sql .= " SELECT";
		$sql .= "   t.teacher_id ";
		$sql .= " FROM teacher t ";
		$sql .= " INNER JOIN mv_1_school s ON t.school_id = s.school_id ";
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 先生IDテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		// 生徒IDテーブル作成
		MoveEnterprise::debug_print("====== 生徒IDテーブル作成 mv_3_student ===");
		$sql = "DROP TABLE IF EXISTS mv_3_student; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 生徒IDテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_3_student ";
		$sql .= " SELECT";
		$sql .= "   st.student_id ";
		$sql .= " FROM student st ";
		$sql .= " INNER JOIN mv_1_school s ON st.school_id = s.school_id ";
		$sql .= " WHERE st.move_flg = 0";	// 転校した生徒は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 生徒IDテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}


		// 保護者IDテーブル作成
		MoveEnterprise::debug_print("====== 保護者IDテーブル作成 mv_4_guardian ===");
		$sql = "DROP TABLE IF EXISTS mv_4_guardian; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 保護者IDテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_4_guardian ";
		$sql .= " SELECT";
		$sql .= "   g.guardian_id ";
		$sql .= " FROM guardian g ";
		$sql .= " INNER JOIN mv_1_school s ON g.school_id = s.school_id ";
		$sql .= " WHERE g.move_flg = 0";		// 転校した保護者は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 保護者IDテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

// 		// 目標テーブル作成
// 		$sql = "DROP TABLE IF EXISTS mv_5_target_info_group; ";
// 		$db->exec_query($sql);

// 		$sql  = " CREATE TABLE mv_5_target_info_group ";
// 		$sql .= " SELECT";
// 		$sql .= "   t.target_info_group_id ";
// 		$sql .= "  ,0 as new_target_info_group_id ";
// 		$sql .= " FROM target_info_group t ";
// 		$sql .= " INNER JOIN mv_1_school s ON s.school_id = t.school_id ";
// 		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
// 		$sql .= ";";
// 		$db->exec_query($sql);

// 		// 目標テーブル作成
// 		$sql = "DROP TABLE IF EXISTS mv_5_target_info_list; ";
// 		$db->exec_query($sql);

// 		$sql  = " CREATE TABLE mv_5_target_info_list ";
// 		$sql .= " SELECT";
// 		$sql .= "   t.target_info_list_id ";
// 		$sql .= "  ,0 as new_target_info_list_id ";
// 		$sql .= " FROM target_info_list t ";
// 		$sql .= " INNER JOIN mv_5_target_info_group g ON g.target_info_group_id = t.target_info_group_id ";
// 		$sql .= ";";
// 		$db->exec_query($sql);

		// 目標実績テーブル１作成
		MoveEnterprise::debug_print("====== 目標実績テーブル作成1 mv_6_target_study_group ===");
		$sql = "DROP TABLE IF EXISTS mv_6_target_study_group; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 目標実績テーブル作成1 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_6_target_study_group ";
		$sql .= " SELECT";
		$sql .= "   t.target_study_group_id ";
		$sql .= "  ,0 as new_target_study_group_id ";
		$sql .= " FROM target_study_group_evac t ";
		$sql .= " INNER JOIN mv_1_school s ON s.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= " UNION";
		$sql .= " SELECT";
		$sql .= "   t.target_study_group_id ";
		$sql .= "  ,0 as new_target_study_group_id ";
		$sql .= " FROM target_study_group t ";
		$sql .= " INNER JOIN mv_1_school s ON s.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 目標実績テーブル作成1 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}


		// 目標実績テーブル２作成
		MoveEnterprise::debug_print("====== 目標実績テーブル作成2 mv_6_target_study_list ===");
		$sql = "DROP TABLE IF EXISTS mv_6_target_study_list; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 目標実績テーブル作成2 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_6_target_study_list ";
		$sql .= " SELECT";
		$sql .= "   t.target_study_list_id ";
		$sql .= "  ,0 as new_target_study_list_id ";
		$sql .= " FROM target_study_list_evac t ";
		$sql .= " INNER JOIN mv_6_target_study_group g ON g.target_study_group_id = t.target_study_group_id ";
		$sql .= " UNION";
		$sql .= " SELECT";
		$sql .= "   t.target_study_list_id ";
		$sql .= "  ,0 as new_target_study_list_id ";
		$sql .= " FROM target_study_list t ";
		$sql .= " INNER JOIN mv_6_target_study_group g ON g.target_study_group_id = t.target_study_group_id ";
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== 目標実績テーブル作成2 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}


		// study_recordテーブル作成
		MoveEnterprise::debug_print("====== study_recordテーブル作成 mv_7_study_record ===");
		$sql = "DROP TABLE IF EXISTS mv_7_study_record; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== study_recordテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_7_study_record ";
		$sql .= " SELECT";
		$sql .= "   t.study_record_num ";
		$sql .= "  ,0 as new_study_record_num ";
		$sql .= " FROM study_record t ";
		$sql .= " INNER JOIN mv_1_school s ON s.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== study_recordテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		// drawing_logテーブル作成
		MoveEnterprise::debug_print("====== drawing_logテーブル作成 mv_8_drawing_log ===");
		$sql = "DROP TABLE IF EXISTS mv_8_drawing_log; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== drawing_logテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_8_drawing_log ";
		$sql .= " SELECT";
		$sql .= "   t.drawing_log_id ";
		$sql .= "  ,0 as new_drawing_log_id ";
		$sql .= " FROM drawing_log_surala t ";
		$sql .= " INNER JOIN mv_7_study_record s ON s.study_record_num = t.study_record_num  ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= " UNION";
		$sql .= " SELECT";
		$sql .= "   t.drawing_log_id ";
		$sql .= "  ,0 as new_drawing_log_id ";
		$sql .= " FROM drawing_log_test t ";
		$sql .= " INNER JOIN mv_3_student s ON s.student_id = t.student_id ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== drawing_logテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		// questionテーブル作成
		MoveEnterprise::debug_print("====== questionテーブル作成 mv_9_question ===");
		$sql = "DROP TABLE IF EXISTS mv_9_question; ";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== drawing_logテーブル作成 DROP エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

		$sql  = " CREATE TABLE mv_9_question ";
		$sql .= " SELECT";
		$sql .= "   t.question_num ";
		$sql .= "  ,0 as new_question_num ";
		$sql .= " FROM question t ";
		$sql .= " INNER JOIN mv_1_school s ON s.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";		// 転校した生徒の目標は条件から抜く
		$sql .= ";";
		if (!$db->exec_query($sql)) {
			MoveEnterprise::debug_print("====== drawing_logテーブル作成 CREATE エラー ".$db->error_no()." ".$db->error_message());
			return false;
		}

	}

	/**
	 * ゲーミフィケーションのdb_idを更新する
	 */
	public static function updateGameDbId($enterprise_id, $db_id) {

		$school_id_list = array();

		// 校舎ID取得
		$sql  = " SELECT";
		$sql .= "   school_id ";
		$sql .= " FROM school ";
		$sql .= " WHERE enterprise_id = '".$enterprise_id."'";
		$sql .= ";";

		if($rs = self::$togo_connect_db->query($sql)){
			while($list = self::$togo_connect_db->fetch_assoc($rs)) {
				$school_id_list[] = $list['school_id'];
			}
		}

		// 対象校舎が存在したら、db_idを更新する
		if (count($school_id_list) > 0) {
			$sql  = " UPDATE school_info ";
			$sql .= " SET  ";
			$sql .= "  db_id = '".$db_id."' ";
			$sql .= " ,upd_syr_id = 'mvent' ";
			$sql .= " ,upd_tts_id = 'mvent' ";
			$sql .= " ,upd_date = now() ";
			$sql .= " WHERE school_id IN ('".implode("','", $school_id_list)."')";
			$sql .= ";";
			self::$game_connect_db->exec_query($sql);
		}

	}

	/**
	 * 統合のenterpriseに紐づくdb_idを更新する
	 */
	public static function updateTogoDbId($enterprise_id, $db_id) {

		$sql  = " UPDATE db_connect ";
		$sql .= " SET  ";
		$sql .= "  db_id = '".$db_id."' ";
		$sql .= " ,upd_syr_id = 'mvent' ";
		$sql .= " ,upd_tts_id = 'mvent' ";
		$sql .= " ,upd_date = now() ";
		$sql .= " WHERE enterprise_id = '".$enterprise_id."'";
		$sql .= ";";
		self::$togo_connect_db->exec_query($sql);

	}

	/**
	 * 抽出用のテーブルを転送する
	 * @param object $db_moto 移動元のDBコネクション
	 * @param object $db_saki 移動先のDBコネクション
	 * @param string $enterprise_id
	 */
	public static function exportKey($db_moto, $db_saki,  $enterprise_id) {

		$LIST = null;
		$ret = null;

		$file = "/data/bat/move_enterprise/data/".$enterprise_id."_MV_key.sql";

		$commnad  = "mysqldump -u ".$db_moto['DBUSER'];
		$commnad .= " -p".$db_moto['DBPASSWD'];
		$commnad .= " -h ".$db_moto['DBHOST'];
		$commnad .= " --single-transaction --quick --extended-insert ";
		$commnad .= " ".$db_moto['DBNAME'];
		$commnad .= " mv_0_enterprise";
		$commnad .= " mv_1_school";
		$commnad .= " mv_2_teacher";
		$commnad .= " mv_3_student";
		$commnad .= " mv_4_guardian";
// 		$commnad .= " mv_5_target_info_group";
// 		$commnad .= " mv_5_target_info_list";
		$commnad .= " mv_6_target_study_group";
		$commnad .= " mv_6_target_study_list";
		$commnad .= " mv_7_study_record";
		$commnad .= " mv_8_drawing_log";
		$commnad .= " mv_9_question";
		$commnad .= " > ".$file;

		exec($commnad, $LIST, $ret);

		$commnad  = "mysql -u ".$db_saki['DBUSER'];
		$commnad .= " -p".$db_saki['DBPASSWD'];
		$commnad .= " -h ".$db_saki['DBHOST'];
		$commnad .= " ".$db_saki['DBNAME'];
		$commnad .= " < ".$file;

		exec($commnad, $LIST, $ret);

		// 移動に使用したファイルは削除
		if (file_exists($file)) {
			unlink($file);
		}

	}

	/**
	 * 処理後のメッセージをテーブルに更新する
	 * @param string $enterprise_id 法人ID
	 * @param string $today 処理日
	 * @param string $message 処理結果
	 */
	public static function saveDdata($enterprise_id, $today, $message) {

		// 移動一覧の情報を更新する
		$sql = "UPDATE mv_enterprise SET";
		$sql.= "  syrz_flg = 1";
		$sql.= " ,usr_bko = '".$message."'";
		$sql.= " ,upd_syr_id = 'mvent'";
		$sql.= " ,upd_tts_id = '9999999999'";
		$sql.= " ,upd_date = NOW()";
		$sql.= " WHERE 1";
		$sql.= "  AND exec_date = '".$today."'";
		$sql.= "  AND enterprise_id = '".$enterprise_id."'";
		$sql.= "  AND syrz_flg = '0'";

		if(!self::$sougou_connect_db->exec_query($sql)){
			self::log("saveDdata", $sql, $message, self::$sougou_connect_db);
		}
		return;
	}

	/**
	 * echo表示モードがonなら文字列を表示する。
	 */
	public static function debug_print($line){
		if(self::$PRINT_MODE){ echo $line."\n"; }
	}

}
//クラス end--------------------------------
