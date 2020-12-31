#!/usr/bin/php -q
<?php
/*

	すらら速習コース検証、本番バッチアップロードシステム

*/
//	ベースフォルダー
// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
// switch ($_SERVER['SERVER_ADDR']){
// // 開発Core
// case "10.3.11.100":
// 	$BASE_DIR = "/data/home";
// 	break;
// // すらら様admin
// case "10.3.11.101":
// 	$BASE_DIR = "/data/home/contents";
// 	break;
// default:
// 	$BASE_DIR = "/data/home";
// 	break;
// }

$BASE_DIR = "/data/home";

// すらら様admin
if (preg_match("/data\/home\/contents/", $_SERVER['SCRIPT_NAME'])){
	$INCLUDE_BASE_DIR = "/data/home/contents";
// 開発Core
}else {
 	$INCLUDE_BASE_DIR = "/data/home";
}
// upd end hasegawa 2018/03/29

define("BASE_DIR",$BASE_DIR);
define("INCLUDE_BASE_DIR",$INCLUDE_BASE_DIR);	// add hasegawa 2018/03/29 AWS移設

//	DBリスト取得
// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
// include(BASE_DIR."/_www/batch/db_list.php");
// include(BASE_DIR."/_www/batch/db_connect.php");
include(INCLUDE_BASE_DIR."/_www/batch/db_list.php");
include(INCLUDE_BASE_DIR."/_www/batch/db_connect.php");
// upd end hasegawa 2018/03/29

//	検証バッチサーバーネーム
$btw_server_name = "srn-stg-batch-1";

// 本番バッチ
$bhw_server_name = "srlbhw11";

//	テンプレートサーバーネーム
$tw_server_name = "srn-stg-template-1";


//	検証コアwebサーバーネーム
$ctw_server_name = "srn-stg-coreweb-1";


//	更新情報取得
$update_type = $argv[1];
$update_mode = $argv[2];
$pk_course_num = $argv[3];
$pk_stage_num = $argv[4];
$pk_lesson_num = $argv[5];
$pk_unit_num = $argv[6];
$pk_block_num = $argv[7];
$pk_list_num = $argv[8];
$upd_tts_id = $argv[9];
$option1 = $argv[10];
$option2 = $argv[11];
$option3 = $argv[12];
$option4 = $argv[13];
$option5 = $argv[14];

if (!$argv[1]) {
	$ERROR[] = "1";
	write_error($ERROR);
	echo "1";
	exit;
}

if (!$argv[2]) {
	$ERROR[] = "2";
	write_error($ERROR);
	echo "2";
	exit;
}

$bat_cd = new connect_db();
$bat_cd -> set_db($L_DB["srlbtw21"]);
$ERROR = $bat_cd -> set_connect_db();
if ($ERROR) {
	set_error($ERROR, '3');
}

$update_type = $argv[1];
$update_mode = $argv[2];

//	本番バッチアップの時は、アップの変数も記録する。

if ($update_mode == "package_data") {	//	コードマスターアップ
	//	DB更新
	if ($update_type == 1) {
		$ERROR = package_data_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '201');
		}
		//	DB削除
		$ERROR = package_data_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '202d');
		}
		//	アップロードログアップ
		$ERROR = package_data_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '203');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = package_data_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '204');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = package_data_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '205d');
		}
	}
}

// DB切断
$bat_cd -> close();

//	終了
echo "0";

exit;



//---------------------------------------------------------------------------------------------
//	速習コース情報DBアップロード
function package_data_db($update_server_name, $argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	$pk_course_num = $argv[3];
	$pk_stage_num = $argv[4];
	$pk_lesson_num = $argv[5];
	$pk_unit_num = $argv[6];
	$pk_block_num = $argv[7];
	$pk_list_num = $argv[8];
	if ($pk_course_num < 1) {
		$ERROR[] = "Not pk_course_num";
		return $ERROR;
	}

	$cd = new connect_db();
	$cd -> set_db($L_DB[$update_server_name]);
	$ERROR = $cd ->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}


	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	//	コース
	$sql  = "SELECT * FROM package_course".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."';";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_course', $INSERT_NAME, $INSERT_VALUE);
	}

	//	ステージ
	$sql  = "SELECT * FROM package_stage".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_stage', $INSERT_NAME, $INSERT_VALUE);
	}

	//	レッスン
	$sql  = "SELECT * FROM package_lesson".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd -> real_escape($pk_lesson_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_lesson', $INSERT_NAME, $INSERT_VALUE);
	}

	//	ユニット
	$sql  = "SELECT * FROM package_unit".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd -> real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd -> real_escape($pk_unit_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	ブロック
	$sql  = "SELECT * FROM package_block".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd -> real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd -> real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd -> real_escape($pk_block_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_block', $INSERT_NAME, $INSERT_VALUE);
	}

	//	リスト
	$sql  = "SELECT * FROM package_list".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd -> real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd -> real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd -> real_escape($pk_block_num)."'";
	}
	if ($pk_list_num) {
		$sql .= " AND pk_list_num='".$bat_cd -> real_escape($pk_list_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_list', $INSERT_NAME, $INSERT_VALUE);
	}

	//	ユニットリスト
	$sql  = "SELECT * FROM package_unit_list".
			" WHERE pk_course_num='".$bat_cd -> real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd -> real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd -> real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd -> real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd -> real_escape($pk_block_num)."'";
	}
	if ($pk_list_num) {
		$sql .= " AND pk_list_num='".$bat_cd -> real_escape($pk_list_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd ->query($sql)) {
		make_insert_query($result, 'package_unit_list', $INSERT_NAME, $INSERT_VALUE);
	}


	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd ->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd ->close();
		return $ERROR;
	}

	//	本番バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = replace_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_course;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_stage;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_lesson;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_unit;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_block;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_list;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE package_unit_list;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//	速習コース情報DB削除
function package_data_db_del($update_server_name,$argv) {

	global $L_DB;
	$bat_cd = $GLOBALS['bat_cd'];

	$pk_course_num = $argv[3];
	$pk_stage_num = $argv[4];
	$pk_lesson_num = $argv[5];
	$pk_unit_num = $argv[6];
	$pk_block_num = $argv[7];
	$pk_list_num = $argv[8];
	if ($pk_course_num < 1) {
		$ERROR[] = "Not pk_course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$DELETE_SQL = array();
	//	コース
	$sql  = "DELETE FROM package_course".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."';";
	$DELETE_SQL[] = $sql;
	//	ステージ
	$sql  = "DELETE FROM package_stage".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	レッスン
	$sql  = "DELETE FROM package_lesson".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd->real_escape($pk_lesson_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ユニット
	$sql  = "DELETE FROM package_unit".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd->real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd->real_escape($pk_unit_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ブロック
	$sql  = "DELETE FROM package_block".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd->real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd->real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd->real_escape($pk_block_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	リスト
	$sql  = "DELETE FROM package_list".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd->real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd->real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd->real_escape($pk_block_num)."'";
	}
	if ($pk_list_num) {
		$sql .= " AND pk_list_num='".$bat_cd->real_escape($pk_list_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;
	//	ユニットリスト
	$sql  = "DELETE FROM package_unit_list".
			" WHERE pk_course_num='".$bat_cd->real_escape($pk_course_num)."'";
	if ($pk_stage_num) {
		$sql .= " AND pk_stage_num='".$bat_cd->real_escape($pk_stage_num)."'";
	}
	if ($pk_lesson_num) {
		$sql .= " AND pk_lesson_num='".$bat_cd->real_escape($pk_lesson_num)."'";
	}
	if ($pk_unit_num) {
		$sql .= " AND pk_unit_num='".$bat_cd->real_escape($pk_unit_num)."'";
	}
	if ($pk_block_num) {
		$sql .= " AND pk_block_num='".$bat_cd->real_escape($pk_block_num)."'";
	}
	if ($pk_list_num) {
		$sql .= " AND pk_list_num='".$bat_cd->real_escape($pk_list_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	return $ERROR;
}


//	速習コース情報DBアップロードログアップ
function package_data_db_log($update_server_name, $argv) {
	global $L_DB;

	$update_mode = $argv[2];
	$pk_course_num = $argv[3];
	$pk_course_num = $argv[3];
	$pk_stage_num = $argv[4];
	$pk_lesson_num = $argv[5];
	$pk_unit_num = $argv[6];
	$pk_block_num = $argv[7];
	$pk_list_num = $argv[8];
	$upd_tts_id = $argv[9];
	if ($pk_course_num < 1) {
		$ERROR[] = "Not pk_course_num";
		return $ERROR;
	}

	//	DB接続
	$cd = new connect_db();
	$cd -> set_db($L_DB[$update_server_name]);
	$ERROR = $cd ->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}


	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM package_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND pk_course_num='".$cd->real_escape($pk_course_num)."'".
			" AND pk_stage_num='".$cd->real_escape($pk_stage_num)."'".
			" AND pk_lesson_num='".$cd->real_escape($pk_lesson_num)."'".
			" AND pk_unit_num='".$cd->real_escape($pk_unit_num)."'".
			" AND pk_block_num='".$cd->real_escape($pk_block_num)."'".
			" AND pk_list_num='".$cd->real_escape($pk_list_num)."'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE package_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND pk_course_num='".$cd->real_escape($pk_course_num)."'".
				" AND pk_stage_num='".$cd->real_escape($pk_stage_num)."'".
				" AND pk_lesson_num='".$cd->real_escape($pk_lesson_num)."'".
				" AND pk_unit_num='".$cd->real_escape($pk_unit_num)."'".
				" AND pk_block_num='".$cd->real_escape($pk_block_num)."'".
				" AND pk_list_num='".$cd->real_escape($pk_list_num)."'".
				" AND state='1';";
		if (!$cd->query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO package_mate_upd_log".
			" (".
				"`package_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`pk_course_num` ,".
				"`pk_stage_num` ,".
				"`pk_lesson_num` ,".
				"`pk_unit_num` ,".
				"`pk_block_num` ,".
				"`pk_list_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($pk_course_num)."',".
				" '".$cd->real_escape($pk_stage_num)."',".
				" '".$cd->real_escape($pk_lesson_num)."',".
				" '".$cd->real_escape($pk_unit_num)."',".
				" '".$cd->real_escape($pk_block_num)."',".
				" '".$cd->real_escape($pk_list_num)."',".
				" NULL ,".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}


	//	DB切断
	$cd ->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------

//	エラー記録処理
function write_error($ERROR) {

	foreach ($ERROR AS $val) {
		$error .= date("Ymdhis")."\t".$val."\n";
	}

	$file = "./error.log";
	$OUT = fopen($file,"w");
	fwrite($OUT,$error);
	fclose($OUT);
	@chmod(0666,$file);
}



//	エラー設定
function set_error($ERROR, $num) {

	$ERROR[] = $num;
	write_error($ERROR);
	echo $num;
	exit;

}

//	リプレース処理
function replace_data($cd, $table_name, $insert_name, $INSERT_VALUE) {

	foreach ($INSERT_VALUE AS $values) {
		$sql  = "REPLACE INTO ".$table_name.
				" (".$insert_name.") ".
				" VALUES".$values.";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL REPLACE ERROR<br>$sql";
			$sql  = "ROLLBACK";
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			return $ERROR;
		}
	}

}


//	インサートクエリー作成
function make_insert_query($result, $table_name, &$INSERT_NAME, &$INSERT_VALUE) {

	$bat_cd = $GLOBALS['bat_cd'];

	$flag = 0;
	$i = 0;
	$num = 0;
	$insert_name = "";
	$insert_value = "";
	while ($list=$bat_cd->fetch_assoc($result)) {
		$value = "";
		if ($insert_value) { $insert_value .= ", "; }

		foreach ($list AS $key => $val) {
			if ($flag != 1) {
				if ($insert_name) { $insert_name .= ","; }
				$insert_name .= $key;
			}
			if ($value) { $value .= ","; }
			$value .= "'".addslashes($val)."'";
		}
		if ($value) {
			$insert_value .= "(".$value.")";
			$i++;
		}
		if ($insert_name) { $flag = 1; }
		if ($i == 50) {
			$INSERT_VALUE[$table_name][] = $insert_value;
			$num++;
			$i = 1;
			$insert_value = "";
		}
	}
	if ($insert_name) { $INSERT_NAME[$table_name] = $insert_name; }
	if ($insert_value) { $INSERT_VALUE[$table_name][] = $insert_value; }
}

?>
