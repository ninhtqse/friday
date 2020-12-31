#!/usr/bin/php -q
<?PHP
/*

	判定テスト  検証、本番バッチアップロードシステム

	okabe 2013/08/02

*/

// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
// //	ベースフォルダー
// switch ($_SERVER['SERVER_ADDR']){
//// 開発Core
// case "10.3.11.100":
//	$BASE_DIR = "/data/home";
//	$ctw31_server_name = "srlctw11"; // 開発コアwebサーバーネーム	 // アップデートログリスト 更新処理追加
//	break;
// // すらら様admin
// case "10.3.11.101":
// 	$BASE_DIR = "/data/home/contents";
// 	$ctw31_server_name = "srlctd03cts"; // 開発コアwebサーバーネーム	 // アップデートログリスト 更新処理追加
// 	break;
// default:
// 	$BASE_DIR = "/data/home";
// 	$ctw31_server_name = "srlctw11"; // 開発コアwebサーバーネーム	 // アップデートログリスト 更新処理追加
// 	break;
// }

$BASE_DIR = "/data/home";

// すらら様admin
if (preg_match("/data\/home\/contents/", $_SERVER['SCRIPT_NAME'])){
	$INCLUDE_BASE_DIR = "/data/home/contents";
	$ctw31_server_name = "srlctd03cts"; 	// 開発コアwebサーバーネーム
// 開発Core
}else {
 	$INCLUDE_BASE_DIR = "/data/home";
	$ctw31_server_name = "srlctw11";	// 開発コアwebサーバーネーム
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

set_time_limit(0);

//      更新情報取得
$update_type = $argv[1];
$update_mode = $argv[2];
$service_num = $argv[3];
$hantei_type = $argv[4];
$course_num = $argv[5];
$hantei_default_num = $argv[6];
$fileup = $argv[7];
$update_num = $argv[8];

if (!$argv[1]) {
		$ERROR[] = "201";
		write_error($ERROR);
		echo "201";
		exit;
}

if (!$argv[2]) {
	$ERROR[] = "202";
	write_error($ERROR);
	echo "202";
	exit;
}

$bat_cd = new connect_db();
$bat_cd -> set_db($L_DB["srlbtw21"]);
$ERROR = $bat_cd -> set_connect_db();
if ($ERROR) {
	set_error($ERROR, '203');
}

if ($update_mode == "test_hantei_test_master") {	// 判定テストマスタアップ

	if ($update_type == 1) {
		//	本番バッチ反映
		//	DB更新
		$ERROR = test_hantei_test_master_db($bhw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '211');
		}

		//	DB削除
		$ERROR = test_hantei_test_master_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '212d');
		}

		//	アップロードログアップ
		$ERROR = test_hantei_test_master_db_log($bhw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '213');
		}
	} elseif ($update_type == 2) {
		//	検証サーバー反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			//	DB更新
			$ERROR = test_hantei_test_master_db($update_server_name, $argv);
			if ($ERROR) {
				set_error($ERROR, '214');
			}
		}
	} elseif ($update_type == 3) {
		//	検証バッチDB削除
		$ERROR = test_hantei_test_master_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '215d');
		}
	}
} elseif ($update_mode == "test_problem_hantei_test") {     //      問題設定(判定テスト)アップ

	if ($update_type == 1) {
		//	本番バッチ反映
		//	ファイルアップ
		$ERROR = hantei_test_problem_web($tw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '216');
		}

		//	検証バッチファイル削除
		//	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = hantei_test_problem_web_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '217d');
		}

		//	DB更新
		$ERROR = hantei_test_problem_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '218');
		}

		// 	DB削除
		//	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = hantei_test_problem_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '219d');
		}

		//      アップロードログアップ
		$ERROR = hantei_test_problem_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '220');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/12
		$ERROR = test_mate_upd_log_end($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '220d');
		}

	} elseif ($update_type == 2) {
		//	検証サーバー反映
		//	DB更新
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = hantei_test_problem_db($update_server_name, $argv);
			if ($ERROR) {
				set_error($ERROR, '221');
			}
		}

		//	ファイルアップ
		$ERROR = hantei_test_problem_web($ctw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '222');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/09
		$ERROR = test_problem_hantei_test_test_mate_upd_log_update($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '222-1');
		}

	} elseif ($update_type == 3) {
		//      検証バッチファイル削除
		//	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = hantei_test_problem_web_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '223d');
		}

		// 	DB削除
		//	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = hantei_test_problem_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '224d');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/12
		$ERROR = test_mate_upd_log_delete($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '225d');
		}
	}

	//      処理終了処理
	$update_num = $argv['8'];
	update_end($update_num);

}

// DB切断
$bat_cd->close();

//      終了
echo "0";

exit;


//---------------------------------------------------------------------------------------------
//      判定テストマスタ情報 DBアップロード
function test_hantei_test_master_db($update_server_name, $argv) {
	global $L_DB;	// DB LIST

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];

	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//      データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = "";
	$where .= " WHERE hmd.service_num='".$bat_cd -> real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmd.hantei_type='".$bat_cd -> real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmd.course_num='".$bat_cd -> real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmd.hantei_default_num='".$bat_cd -> real_escape($hantei_default_num)."'";
	}

	// hantei_ms_break_layer
	$sql = "SELECT DISTINCT hmbl.* FROM hantei_ms_break_layer hmbl" .
			" LEFT JOIN hantei_ms_default hmd".
				" ON hmbl.hantei_default_num=hmd.hantei_default_num".
			$where . ";";
	if ($result =  $bat_cd->query($sql)) {
		make_insert_query($result, 'hantei_ms_break_layer', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //hantei_ms_break_layer
	$sql  = "DELETE hmbl FROM hantei_ms_break_layer hmbl".
			" LEFT JOIN hantei_ms_default hmd".
				" ON hmbl.hantei_default_num=hmd.hantei_default_num".
			$where.";";
	$DELETE_SQL['hantei_ms_break_layer'] = $sql;


	// hantei_ms_default
	$sql  = "SELECT * FROM hantei_ms_default hmd".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'hantei_ms_default', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //hantei_ms_default
	$sql  = "DELETE hmd FROM hantei_ms_default hmd".
			$where.";";
	$DELETE_SQL['hantei_ms_default'] = $sql;

	//      トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
				$sql  = "ROLLBACK";
				if (!$cd->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$cd->close();
				return $ERROR;
			}
		}
	}

	//      検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//      トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}


	//      テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE hantei_ms_break_layer;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE hantei_ms_default;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//      検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//      判定テストマスタ情報 DB削除
function test_hantei_test_master_db_del($update_server_name, $argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];


	//	削除クエリー
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = "";
	$where .= " WHERE hmd.service_num='".$bat_cd->real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmd.hantei_type='".$bat_cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmd.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmd.hantei_default_num='".$bat_cd->real_escape($hantei_default_num)."'";
	}


	//	削除クエリー  //hantei_ms_break_layer
	$sql  = "DELETE hmbl FROM hantei_ms_break_layer hmbl".
			" LEFT JOIN hantei_ms_default hmd".
				" ON hmbl.hantei_default_num=hmd.hantei_default_num".
			$where.";";
	$DELETE_SQL['hantei_ms_break_layer'] = $sql;

	//	削除クエリー  //hantei_ms_default
	$sql  = "DELETE hmd FROM hantei_ms_default hmd".
			$where.";";
	$DELETE_SQL['hantei_ms_default'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	return $ERROR;
}



//      判定テストマスタ情報 DBアップロードログアップ
function test_hantei_test_master_db_log($update_server_name, $argv) {
	global $L_DB;

	//指定パラメータ取り出し
	$update_mode = $argv[2];
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];

	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//対象データ群の指定
	$where = "";
	if ($hantei_type > 0) {
		$where .= " AND course_num='".$cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND stage_num='".$cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND lesson_num='".$cd->real_escape($hantei_default_num)."'";
	}

	//      同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND service_num='".$cd->real_escape($service_num)."'".
			$where.";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND service_num='".$cd->real_escape($service_num)."'".
				$where.";";
		if (!$cd->query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//      データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
		" (".
			"`test_mate_upd_log_num` ,".
			"`update_mode` ,".
			"`service_num` ,".
			"`course_num` ,".
			"`stage_num` ,".
			"`lesson_num` ,".
			"`send_data` ,".
			"`regist_time` ,".
			"`state` ,".
			"`upd_tts_id`".
		")".
		" VALUES (".
			"NULL ,".
			" '".$cd->real_escape($update_mode)."',".
			" '".$cd->real_escape($service_num)."',".
			" '".$cd->real_escape($hantei_type)."',".
			" '".$cd->real_escape($course_num)."',".
			" '".$cd->real_escape($hantei_default_num)."',".
			" '".$cd->real_escape($send_data_log)."',".
			" NOW( ) ,".
			" '1',".
			" '".$cd->real_escape($upd_tts_id)."'".
		");";
	if (!$cd->query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//      DB切断
	$cd->close();

	return $ERROR;
}


//---------------------------------------------------------------------------------------------
//      問題データ（判定テスト） DBアップロード
function hantei_test_problem_db($update_server_name, $argv) {
	global $L_DB;	// DB LIST

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];

	$cd= new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//      データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = "";
	$where .= " WHERE hmdp.service_num='".$bat_cd->real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmdp.hantei_type='".$bat_cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmdp.hantei_default_num='".$bat_cd->real_escape($hantei_default_num)."'";
	}


	//	hantei_ms_default_problem
	$sql  = "SELECT hmdp.* FROM hantei_ms_default_problem hmdp".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'hantei_ms_default_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //	hantei_ms_default_problem
	$sql  = "DELETE hmdp FROM hantei_ms_default_problem hmdp".
			$where.";";
	$DELETE_SQL['hantei_ms_default_problem'] = $sql;

	//      トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
				$sql  = "ROLLBACK";
				if (!$cd->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$cd->close();
				return $ERROR;
			}
		}
	}

	//      検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	hantei_ms_problem
	$sql  = "SELECT DISTINCT hmp.* FROM hantei_ms_problem hmp".
			" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'hantei_ms_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //	hantei_ms_problem
	$sql  = "DELETE hmp FROM hantei_ms_problem hmp".
			" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	$DELETE_SQL['hantei_ms_problem'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
				$sql  = "ROLLBACK";
				if (!$cd->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$cd->close();
				return $ERROR;
			}
		}
	}

	//      検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//      トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}


	//      テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE hantei_ms_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE hantei_ms_default_problem;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//      検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//      問題設定(判定テスト)削除
function hantei_test_problem_db_del($update_server_name,$argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];


	//      データーベース更新
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = "";
	$where .= " WHERE hmdp.service_num='".$bat_cd->real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmdp.hantei_type='".$bat_cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmdp.hantei_default_num='".$bat_cd->real_escape($hantei_default_num)."'";
	}

	//	削除クエリー
	//	hantei_ms_problem
	$sql  = "DELETE hmp FROM hantei_ms_problem hmp".
			" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	$DELETE_SQL['hantei_ms_problem'] = $sql;

	//	hantei_ms_default_problem
	$sql  = "DELETE hmdp FROM hantei_ms_default_problem hmdp".
			$where.";";
	$DELETE_SQL['hantei_ms_default_problem'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	return $ERROR;
}



//       問題設定(判定テスト) DBアップロードログアップ
function hantei_test_problem_db_log($update_server_name, $argv) {
	global $L_DB;

	//指定パラメータ取り出し
	$update_mode = $argv[2];
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];

	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//対象データ群の指定
	$where = "";
	if ($hantei_type > 0) {
		$where .= " AND course_num='".$cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND stage_num='".$cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND lesson_num='".$cd->real_escape($hantei_default_num)."'";
	}

	//      同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND service_num='".$cd->real_escape($service_num)."'".
			$where.";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND service_num='".$cd->real_escape($service_num)."'".
				$where.";";
		if (!$cd->query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//      データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
		" (".
			"`test_mate_upd_log_num` ,".
			"`update_mode` ,".
			"`service_num` ,".
			"`course_num` ,".
			"`stage_num` ,".
			"`lesson_num` ,".
			"`send_data` ,".
			"`regist_time` ,".
			"`state` ,".
			"`upd_tts_id`".
		")".
		" VALUES (".
			"NULL ,".
			" '".$cd->real_escape($update_mode)."',".
			" '".$cd->real_escape($service_num)."',".
			" '".$cd->real_escape($hantei_type)."',".
			" '".$cd->real_escape($course_num)."',".
			" '".$cd->real_escape($hantei_default_num)."',".
			" '".$cd->real_escape($send_data_log)."',".
			" NOW( ) ,".
			" '1',".
			" '".$cd->real_escape($upd_tts_id)."'".
		");";
	if (!$cd->query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//      DB切断
	$cd ->close();

	return $ERROR;
}



//      問題設定(判定テスト)webアップロード
function hantei_test_problem_web($update_server_name,$argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];


	//指定パラメータ取り出し
	$update_type = $argv[1];
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];
	$fileup = $argv['7'];

	if ($fileup != 1) {
		return;
	}

	//対象データ群の指定
	$where = " WHERE hmdp.service_num='".$bat_cd->real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmdp.hantei_type='".$bat_cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmdp.hantei_default_num='".$bat_cd->real_escape($hantei_default_num)."'";
	}

	//      問題ファイル取得
	$PROBLEM_LIST = array();
		$sql  = "SELECT DISTINCT hmp.problem_num FROM hantei_ms_problem hmp".
				" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
					" AND hmdp.problem_table_type='2'".
				$where.
				" AND hmp.mk_flg='0'".
				" ORDER BY hmp.problem_num;";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//      画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//      フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//      ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_IMG_DIR);

	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	//      音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//      フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//      ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_VOICE_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	return $ERROR;
}



//      問題設定(判定テスト)web削除
function hantei_test_problem_web_del($update_server_name,$argv) {
	global $L_DB;
	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$update_type = $argv[1];
	$service_num = $argv['3'];
	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];
	$fileup = $argv['7'];

	if ($fileup != 1) {
		return;
	}

	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}

	//対象データ群の指定
	$where = " WHERE hmdp.service_num='".$bat_cd->real_escape($service_num)."'";
	if ($hantei_type > 0) {
		$where .= " AND hmdp.hantei_type='".$bat_cd->real_escape($hantei_type)."'";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$where .= " AND hmdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($hantei_default_num > 0) {
		$where .= " AND hmdp.hantei_default_num='".$bat_cd->real_escape($hantei_default_num)."'";
	}

	//      問題ファイル取得
	$PROBLEM_LIST = array();
		$sql  = "SELECT DISTINCT hmp.problem_num FROM hantei_ms_problem hmp".
				" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
					" AND hmdp.problem_table_type='2'".
				$where.
				" AND hmp.mk_flg='0'".
				" ORDER BY hmp.problem_num;";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);



	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_IMG_DIR);
	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);

	//	音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_VOICE_DIR);
	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);

	return;

}

//---------------------------------------------------------------------------------------------


// -- リモートサーバーフォルダー作成
function test_remote_set_dir($update_server_name, $dir, $FILE_NAME, &$ERROR) {

	//      作成
	if ($FILE_NAME) {
		foreach ($FILE_NAME['df'] AS $key => $val) {
			if ($key == "") {
				countinue;
			}
			$make_dir = $dir.$key;
			$command = "ssh suralacore01@".$update_server_name." mkdir -p ".$make_dir;

			exec("$command", $LIST);
		}
	}

	return;
}



// -- ローカルサーバーファイル情報取得
function test_local_glob($PROBLEM_LIST, &$LOCAL_FILES, &$last_local_time, $dir) {

	if (!$PROBLEM_LIST) { return; }

	foreach ($PROBLEM_LIST AS $problem_num) {
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$amari = $problem_num % 100;
		if ($amari < 1) {
			$dir_num -= 100;
		}

		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = $dir.$dir_num."/";
		$dir_name_ = preg_replace("/\//", "\/", $dir_name);
		$set_file_name = $dir_name.$problem_num."_*.*";
		foreach (glob("$set_file_name") AS $file_name) {
			$file_stamp = filemtime($file_name);
			if ($last_local_time < $file_stamp) {
				$last_local_time = $file_stamp;
			}
			$name = preg_replace("/$dir_name_/", "", $file_name);
			$LOCAL_FILES['f'][$name] = $file_stamp;
			$LOCAL_FILES['df'][$dir_num][] = $name;
		}
	}

}



// -- リモートサーバーファイルアップロード
function test_remote_set_file($update_server_name, $local_dir, $remote_dir, $FILE_NAME, &$ERROR) {

	//      ファイルアップロード
	if ($FILE_NAME['df']) {
		foreach ($FILE_NAME['df'] AS $key => $FILE_LIST) {
			if ($FILE_LIST) {
				foreach ($FILE_LIST AS $file_name) {
					$local_file = $local_dir.$key."/".$file_name;
					$remote_file_dir = $remote_dir.$key."/";
					$command = "scp -rp '".$local_file."' suralacore01@".$update_server_name.":'".$remote_file_dir."'";
					exec("$command", $LIST);

				}
			}
		}

	}

	return;
}



// -- リモートサーバーファイル削除
function test_remote_del_file($update_server_name, $del_dir, $FILE_NAME, &$ERROR) {

	//	ファイルアップロード
	if ($FILE_NAME['df']) {
		$COM_LIST = array();
		foreach ($FILE_NAME['df'] AS $key => $FILE_LIST) {
			if ($FILE_LIST) {
				$i = 0;
				$com_line = "";
				foreach ($FILE_LIST AS $file_name) {
					$del_file = $del_dir.$key."/".$file_name;
					//$com_line .= " rm -f '".$del_file."'\n";
					$com_line .= " rm -f '".$del_file."'\n";

					$i++;
					if ($i >= 20) {
						$COM_LIST[] = $com_line;
						$com_line = "";
						$i = 0;
					}
				}
				if ($com_line) {
					$COM_LIST[] = $com_line;
				}
			}
		}
		if (count($COM_LIST) > 0) {
			foreach ($COM_LIST AS $com_line) {
				exec("$com_line", $LIST);
			}
		}
	}

	return;
}



//      反映終了処理記録
function update_end($update_num) {

        if ($update_num < 1) {
                return;
        }
	$bat_cd = $GLOBALS['bat_cd'];

        $sql  = "UPDATE test_update_check SET".
                        " state='1',".
                        " end_time=now()".
                        " WHERE update_num='".$bat_cd->real_escape($update_num)."';";
        @$bat_cd->exec_query($sql);
}

//---------------------------------------------------------------------------------------------

//      エラー記録処理
function write_error($ERROR) {

	foreach ($ERROR AS $val) {
		$error .= date("Ymdhis")."\t".$val."\n";
	}

	// $file = BASE_DIR."/_www/batch/error_HANTEITESTCONTENTS.log";		// upd hasegawa 2018/03/29 AWS移設
	$file = INCLUDE_BASE_DIR."/_www/batch/error_HANTEITESTCONTENTS.log";

	$OUT = fopen($file,"w");
	fwrite($OUT,$error);
	fclose($OUT);
	@chmod(0666,$file);
}



//      エラー設定
function set_error($ERROR, $num) {

	$ERROR[] = $num;
	write_error($ERROR);
	echo $num;
	exit;

}


//      インサート処理
function insert_data($cd, $table_name, $insert_name, $INSERT_VALUE) {

	foreach ($INSERT_VALUE AS $values) {
		$sql  = "INSERT INTO ".$table_name.
			" (".$insert_name.") ".
			" VALUES".$values.";";

		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL INSERT ERROR<br>$sql";
			$sql  = "ROLLBACK";
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$cd->close();
			return $ERROR;
		}
	}

}


//      インサートクエリー作成
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

//
//	問題アップ 本番バッチUP時のアップデートリストログ更新
//
function test_mate_upd_log_end($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	$myid = $argv['9'];
	$test_mate_upd_log_num = $argv['10'];

	if ($test_mate_upd_log_num < 1) {
		$ERROR[] = "Not test_mate_upd_log_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$myid = $cd->real_escape($myid);
	$test_mate_upd_log_num = $cd->real_escape($test_mate_upd_log_num);

	unset($DELETE_DATA);
	$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";
	$DELETE_DATA['regist_time'] = "now()";
	$DELETE_DATA['state'] = 2;
	$DELETE_DATA['upd_tts_id'] = $myid;
	$ERROR = $cd->update('test_mate_upd_log',$DELETE_DATA,$where);

	return $ERROR;

}

//
//	問題アップ 検証バッチ削除時のアップデートリストログ更新
//
function test_mate_upd_log_delete($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	$myid = $argv['7'];
	$test_mate_upd_log_num = $argv['8'];

	if ($test_mate_upd_log_num < 1) {
		$ERROR[] = "Not test_mate_upd_log_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$myid = $cd->real_escape($myid);
	$test_mate_upd_log_num = $cd->real_escape($test_mate_upd_log_num);

	unset($DELETE_DATA);
	$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";
	$DELETE_DATA['regist_time'] = "now()";
	$DELETE_DATA['state'] = 0;
	$DELETE_DATA['upd_tts_id'] = $myid;
	$ERROR = $cd->update('test_mate_upd_log',$DELETE_DATA,$where);

	return $ERROR;

}

// add start oda 2015/11/09 アップデートログリスト 更新処理追加
//
//	判定テスト 問題アップ時のアップデートリストログ更新
//
function test_problem_hantei_test_test_mate_upd_log_update($update_server_name, $mode, $argv) {

	global $L_DB;	// DB LIST

	// パラメータ取得
	$service_num = $argv['3'];
	$hantei_type = $argv['4'];
	$course_num = $argv['5'];
	$hantei_default_num = $argv['6'];
	$fileup = $argv['7'];
	$myid = $argv['9'];

	if ($service_num < 1) {
		$ERROR[] = "Not service_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$service_num = $cd->real_escape($service_num);
	$hantei_type = $cd->real_escape($hantei_type);
	$course_num = $cd->real_escape($course_num);
	$hantei_default_num = $cd->real_escape($hantei_default_num);
	$fileup = $cd->real_escape($fileup);
	$myid = $cd->real_escape($myid);

	// send_data_log生成
	if ($hantei_type == "1") {
		$SEND_DATA_LOG = array(
			'service_num' => $service_num,
			'hantei_type' => $hantei_type,
			'hantei_default_num' => $hantei_default_num,
			'fileup' => $fileup
		);
	} else if ($hantei_type == "2") {
		$SEND_DATA_LOG = array(
			'service_num' => $service_num,
			'hantei_type' => $hantei_type,
			'course_num' => $course_num,
			'hantei_default_num' => $hantei_default_num,
			'fileup' => $fileup
		);
	}
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);

	// モード取得
	$mode = str_replace("test_", "", $mode);

	//	ログ存在チェック
	$test_mate_upd_log_num = 0;

	$sql  = "SELECT test_mate_upd_log_num FROM test_mate_upd_log ".
			" WHERE update_mode='".$mode."'".
			" AND state='1'".
			" AND service_num='".$service_num."'";
	if ($hantei_type > 0) {
		$sql .= " AND course_num='".$hantei_type."'";
	} else {
		$sql .= " AND course_num IS NULL";
	}
	if ($hantei_type == 2 && $course_num > 0) {
		$sql .= " AND stage_num='".$course_num."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($hantei_default_num > 0) {
		$sql .= " AND lesson_num='".$hantei_default_num."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	$sql .=	" ORDER BY regist_time DESC".
			" LIMIT 1;";

	if ($result = $cd->query($sql)) {
		$list = $cd->fetch_assoc($result);
		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
	}

	// 存在しない場合、同一条件のレコードのstateを全て0にする
	if ($test_mate_upd_log_num < 1) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 0;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $myid;
		$where = " WHERE update_mode='".$mode."'".
				 " AND state!='0'".
				 " AND service_num='".$service_num."'";
		if ($hantei_type > 0) {
			$where .= " AND course_num='".$hantei_type."'";
		}
		if ($course_num > 0) {
			$where .= " AND stage_num='".$course_num."'";
		}
		if ($hantei_default_num > 0) {
			$where .= " AND lesson_num='".$hantei_default_num."'";
		}

		$ERROR = $cd->update("test_mate_upd_log", $INSERT_DATA, $where);
	}

	if ($test_mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $myid;
		$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";

		$ERROR = $cd->update("test_mate_upd_log", $INSERT_DATA, $where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = $mode;
		$INSERT_DATA['service_num'] = $service_num;
		if ($hantei_type > 0) {
			$INSERT_DATA['course_num'] = $hantei_type;
		}
		if ($course_num > 0) {
			$INSERT_DATA['stage_num'] = $course_num;
		}
		if ($hantei_default_num > 0) {
			$INSERT_DATA['lesson_num'] = $hantei_default_num;
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $myid;

		$ERROR = $cd->insert("test_mate_upd_log", $INSERT_DATA);
	}

	return $ERROR;

}
// add end oda 2015/11/09

?>