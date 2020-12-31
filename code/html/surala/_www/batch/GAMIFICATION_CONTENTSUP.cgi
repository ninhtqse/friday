#!/usr/bin/php -q
<?PHP
/*
	ゲーミフィケーション機能 検証、本番バッチアップロードシステム
*/

// ベースフォルダー
$BASE_DIR = "/data/home";
// すらら様admin
if (preg_match("/data\/home\/contents/", $_SERVER['SCRIPT_NAME'])){
 	$INCLUDE_BASE_DIR = "/data/home/contents";
// 開発Core
}else {
 	$INCLUDE_BASE_DIR = "/data/home";
}

define("BASE_DIR",$BASE_DIR);
define("INCLUDE_BASE_DIR",$INCLUDE_BASE_DIR);

// DBリスト取得
// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
include(INCLUDE_BASE_DIR."/_www/batch/db_list.php");
include(INCLUDE_BASE_DIR."/_www/batch/db_connect.php");
// upd end hasegawa 2018/03/29

// 検証バッチサーバーネーム
$btw_server_name = "srn-stg-batch-1";

// 本番バッチ
$bhw_server_name = "srlbhw11";

// ゲーミフィケーションテンプレートサーバーネーム
$tw_server_name = "srn-stg-gametemplate-1";

// ゲーミフィケーション検証webサーバーネーム
$ctw_server_name = "srn-stg-gameweb-1";

// 更新情報取得
$update_type = $argv[1];
$update_mode = $argv[2];

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

// 検証バッチサーバーDB接続
$bat_cd = new connect_db();
$bat_cd->set_db($L_DB["srlbtw21"]);
$ERROR = $bat_cd->set_connect_db();
if ($ERROR) {
	$ERROR[] = "3";
	write_error($ERROR);
	echo "3";
	exit;
}


$write[] = "HTTP_HOST".$_SERVER['HTTP_HOST'];
$write[] = $L_DB['srlbtw21']['DBNAME'];
$write[] = "INCLUDE_BASE_DIR".INCLUDE_BASE_DIR;
$write[] = "SCRIPT_NAME". $_SERVER['SCRIPT_NAME'];
write_error($write);

$update_type = $argv[1];
$update_mode = $argv[2];


if ($update_mode == "character") {

	//	キャラクター情報アップデート

	//	DB更新
	if ($update_type == 1) {
			$ERROR = gamification_character_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GC1";
				write_error($ERROR);
				echo "GC1";
				exit;
			}
			//	DB削除
			$ERROR = gamification_character_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GC1D";
				write_error($ERROR);
				echo "GC1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_GAMIFICATION_TSTC_DB AS $update_server_name) {
			$ERROR = gamification_character_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GC2";
				write_error($ERROR);
				echo "GC2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = gamification_character_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GC3";
				write_error($ERROR);
				echo "GC3";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = gamification_character_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GCWW1";
				write_error($ERROR);
				echo "GCWW1";
				exit;
			}
			//	web削除
			$ERROR = gamification_character_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GCWW1D";
				write_error($ERROR);
				echo "GCWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
			$ERROR = gamification_character_web($btw_server_name,$ctw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GCWW2";
				write_error($ERROR);
				echo "GCWW2";
				exit;
			}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = gamification_character_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GCWW3D";
				write_error($ERROR);
				echo "GCWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);

} else if ($update_mode == "achieve_egg") {

	//	アチーブエッグ情報アップデート

	//	DB更新
	if ($update_type == 1) {
			$ERROR = gamification_achieve_egg_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAE1";
				write_error($ERROR);
				echo "GAE1";
				exit;
			}
			//	DB削除
			$ERROR = gamification_achieve_egg_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAE1D";
				write_error($ERROR);
				echo "GAE1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_GAMIFICATION_TSTC_DB AS $update_server_name) {
			$ERROR = gamification_achieve_egg_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAE2";
				write_error($ERROR);
				echo "GAE2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = gamification_achieve_egg_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAE3";
				write_error($ERROR);
				echo "GAE3";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = gamification_achieve_egg_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAEWW1";
				write_error($ERROR);
				echo "GAEWW1";
				exit;
			}
			//	web削除
			$ERROR = gamification_achieve_egg_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAEWW1D";
				write_error($ERROR);
				echo "GAEWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
			$ERROR = gamification_achieve_egg_web($btw_server_name,$ctw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAEWW2";
				write_error($ERROR);
				echo "GAEWW2";
				exit;
			}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = gamification_achieve_egg_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAEWW3D";
				write_error($ERROR);
				echo "GAEWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);

} else if ($update_mode == "avatar") {

	//	アバター情報アップデート

	//	DB更新
	if ($update_type == 1) {
			$ERROR = gamification_avatar_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GA1";
				write_error($ERROR);
				echo "GA1";
				exit;
			}
			//	DB削除
			$ERROR = gamification_avatar_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GA1D";
				write_error($ERROR);
				echo "GA1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_GAMIFICATION_TSTC_DB AS $update_server_name) {
			$ERROR = gamification_avatar_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GA2";
				write_error($ERROR);
				echo "GA2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = gamification_avatar_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GA3";
				write_error($ERROR);
				echo "GA3";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = gamification_avatar_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAWW1";
				write_error($ERROR);
				echo "GAWW1";
				exit;
			}
			//	web削除
			$ERROR = gamification_avatar_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAWW1D";
				write_error($ERROR);
				echo "GAWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
			$ERROR = gamification_avatar_web($btw_server_name,$ctw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAWW2";
				write_error($ERROR);
				echo "GAWW2";
				exit;
			}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = gamification_avatar_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GAWW3D";
				write_error($ERROR);
				echo "GAWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);

} else if ($update_mode == "item") {

	//	アイテム情報アップデート

	//	DB更新
	if ($update_type == 1) {
			$ERROR = gamification_item_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GI1";
				write_error($ERROR);
				echo "GC1";
				exit;
			}
			//	DB削除
			$ERROR = gamification_item_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GI1D";
				write_error($ERROR);
				echo "GI1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_GAMIFICATION_TSTC_DB AS $update_server_name) {
			$ERROR = gamification_item_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GI2";
				write_error($ERROR);
				echo "GI2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = gamification_item_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GI3";
				write_error($ERROR);
				echo "GI3";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = gamification_item_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GIWW1";
				write_error($ERROR);
				echo "GIWW1";
				exit;
			}
			//	web削除
			$ERROR = gamification_item_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GIWW1D";
				write_error($ERROR);
				echo "GIWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
			$ERROR = gamification_item_web($btw_server_name,$ctw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GIWW2";
				write_error($ERROR);
				echo "GIWW2";
				exit;
			}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = gamification_item_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "GIWW3D";
				write_error($ERROR);
				echo "GIWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);
}

// DB切断
$bat_cd->close();

//	終了
echo "0\n";

exit;

//	キャラクター情報DBアップロード
function gamification_character_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_character_id = $argv[3];

	if (!is_numeric($update_character_id)) {
		$ERROR[] = "更新するキャラクター情報が不正です。";
		return $ERROR;
	}

	// 検証ゲーミフィケーションDBへ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	if ($update_character_id > 0) {
		$where = " AND character_id = '".$bat_cd->real_escape($update_character_id)."';";
	}

	// キャラクターマスタ gamification_character
	$sql  = "SELECT * FROM gamification_character WHERE 1".$where;
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_character', $INSERT_NAME, $INSERT_VALUE);
	}

	// キャラクターレベル gamification_character_level
	$sql  = "SELECT * FROM gamification_character_level WHERE 1".$where;
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_character_level', $INSERT_NAME, $INSERT_VALUE);
	}

	// DBデーター削除クエリー
	// キャラクターマスタ gamification_character
	$sql  = "DELETE FROM gamification_character WHERE 1".$where;
	$DELETE_SQL[] = $sql;
	// キャラクターレベル gamification_character_level
	$sql  = "DELETE FROM gamification_character_level WHERE 1".$where;
	$DELETE_SQL[] = $sql;

	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	// アップ先DBデーター削除
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

	// アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	// トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	// テーブル最適化
	// キャラクターマスタ gamification_character
	$sql = "OPTIMIZE TABLE gamification_character;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	// キャラクターレベル gamification_character_level
	$sql = "OPTIMIZE TABLE gamification_character_level;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// 更新先DB切断
	$cd->close();

	return $ERROR;
}


//	キャラクター情報webアップロード
function gamification_character_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];
	$update_character_id = $argv[3];

	if (!is_numeric($update_character_id)) {
		$ERROR[] = "更新するキャラクター情報が不正です。";
		return $ERROR;
	}

	//	画像と音声
	if ($update_type == 1) {
		if ($update_character_id > 0) {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/".$update_character_id."/";
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_GAM_DIR."character/common/";
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_GAM_DIR."character/common/";
		} else {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/";
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR;
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR;
		}
	} else {
		if ($update_character_id > 0) {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/".$update_character_id."/";
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_GAM_DIR."character/common/";
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_GAM_DIR."character/common/";
		} else {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/";
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR;
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR;
		}
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);


	return $ERROR;
}

//	キャラクター情報DB削除
function gamification_character_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_character_id = $argv[3];

	if (!is_numeric($update_character_id)) {
		$ERROR[] = "更新するキャラクター情報が不正です。";
		return $ERROR;
	}

	$where = '';
	if ($update_character_id > 0) {
	    $where = " AND character_id = '".$bat_cd->real_escape($update_character_id)."'";
	}


	//	検証バッチDBデーター削除
	//	gamification_character
	$sql = "DELETE FROM gamification_character WHERE 1".$where;
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}
	//	gamification_character_level
	$sql = "DELETE FROM gamification_character_level WHERE 1".$where;
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//	キャラクター情報web削除
function gamification_character_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_character_id = $argv[3];

	//	画像・音声
	if ($update_character_id > 0) {
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/".$update_character_id."/";
	} else {
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_CHARACTER_DIR."common/";
	}

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}



//	アチーブエッグ情報DBアップロード
function gamification_achieve_egg_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	// 検証ゲーミフィケーションDBへ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();


	// アチーブエッグマスタ gamification_achieve_egg
	$sql  = "SELECT * FROM gamification_achieve_egg";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_achieve_egg', $INSERT_NAME, $INSERT_VALUE);
	}

	// DBデーター削除クエリー
	// アチーブエッグマスタ gamification_achieve_egg
	$sql  = "DELETE FROM gamification_achieve_egg";
	$DELETE_SQL[] = $sql;

	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	// アップ先DBデーター削除
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

	// アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	// トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	// テーブル最適化
	// アチーブエッグマスタ gamification_achieve_egg
	$sql = "OPTIMIZE TABLE gamification_achieve_egg;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// 更新先DB切断
	$cd->close();

	return $ERROR;
}


//	アチーブエッグ情報webアップロード
function gamification_achieve_egg_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];

	//	画像
	if ($update_type == 1) {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_GAM_DIR;
	} else {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_GAM_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
 	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

 	exec("$command1", $LIST);
	exec("$command2", $LIST);
	return $ERROR;
}

//	アチーブエッグ情報DB削除
function gamification_achieve_egg_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	//	gamification_achieve_egg
	$sql = "DELETE FROM gamification_achieve_egg";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//	アチーブエッグ情報web削除
function gamification_achieve_egg_web_del($update_server_name,$argv) {

	//	画像・音声
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}


//	アバター情報DBアップロード
function gamification_avatar_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	// 検証ゲーミフィケーションDBへ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();


	// アチーブエッグマスタ gamification_avatar
	$sql  = "SELECT * FROM gamification_avatar";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_avatar', $INSERT_NAME, $INSERT_VALUE);
	}

	// DBデーター削除クエリー
	// アチーブエッグマスタ gamification_avatar
	$sql  = "DELETE FROM gamification_avatar";
	$DELETE_SQL[] = $sql;

	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	// アップ先DBデーター削除
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

	// アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	// トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	// テーブル最適化
	// アチーブエッグマスタ gamification_avatar
	$sql = "OPTIMIZE TABLE gamification_avatar;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// 更新先DB切断
	$cd->close();

	return $ERROR;
}


//	アバター情報webアップロード
function gamification_avatar_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];

	//	画像
	if ($update_type == 1) {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_AVATAR_DIR;
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_GAM_AVATAR_DIR;
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_GAM_DIR;
	} else {
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_AVATAR_DIR;
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_GAM_AVATAR_DIR;
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_GAM_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
 	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

 	exec("$command1", $LIST);
	exec("$command2", $LIST);


	return $ERROR;
}

//	アバター情報DB削除
function gamification_avatar_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	//	gamification_avatar
	$sql = "DELETE FROM gamification_avatar";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//	アバター情報web削除
function gamification_avatar_web_del($update_server_name,$argv) {

	//	画像
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_AVATAR_DIR;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}


//	アイテム情報DBアップロード
function gamification_item_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	// 検証ゲーミフィケーションDBへ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	// アイテムマスタ gamification_item
	$sql  = "SELECT * FROM gamification_item WHERE 1";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_item', $INSERT_NAME, $INSERT_VALUE);
	}

	// アイテムキャラクター表示位置 gamification_item_character_position
	$sql  = "SELECT * FROM gamification_item_character_position WHERE 1";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'gamification_item_character_position', $INSERT_NAME, $INSERT_VALUE);
	}

	// DBデーター削除クエリー
	// アイテムマスタ gamification_item
	$sql  = "DELETE FROM gamification_item WHERE 1";
	$DELETE_SQL[] = $sql;
	// アイテムキャラクター表示位置 gamification_item_character_position
	$sql  = "DELETE FROM gamification_item_character_position WHERE 1";
	$DELETE_SQL[] = $sql;

	// トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	// アップ先DBデーター削除
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

	// アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	// トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	// テーブル最適化
	// アイテムマスタ gamification_item
	$sql = "OPTIMIZE TABLE gamification_item;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	// アイテムキャラクター表示位置 gamification_item_character_position
	$sql = "OPTIMIZE TABLE gamification_item_character_position;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// 更新先DB切断
	$cd->close();

	return $ERROR;
}


//	アイテム情報webアップロード
function gamification_item_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];

	//	画像と音声
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_ITEM_DIR;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_GAM_ITEM_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_GAM_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_GAM_ITEM_DIR;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_GAM_ITEM_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_GAM_DIR;
	}

echo $local_dir;
echo $remote_dir;



	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	return $ERROR;
}

//	アイテム情報DB削除
function gamification_item_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	//	gamification_item
	$sql = "DELETE FROM gamification_item WHERE 1";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}
	//	gamification_item_character_position
	$sql = "DELETE FROM gamification_item_character_position WHERE 1";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//	アイテム情報web削除
function gamification_item_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_character_id = $argv[3];

	//	画像
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_ITEM_DIR;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}


//	エラー記録処理
function write_error($ERROR) {

	foreach ($ERROR AS $val) {
		$error .= date("Ymdhis")."\t".$val."\n";
	}

	$file = BASE_DIR."/_www/batch/error_GAMIFICATION_CONTENTS.log";
	$OUT = fopen($file,"w");
	fwrite($OUT,$error);
	fclose($OUT);
	@chmod(0666,$file);

}


//	インサート処理
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
			return $ERROR;
		}
	}
}


//	インサートクエリー作成
function make_insert_query($result, $table_name, &$INSERT_NAME, &$INSERT_VALUE) {

	// 検証バッチDB接続オブジェクト
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

//	反映終了処理記録
function mt_update_end($update_num) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_num < 1) {
		return;
	}

	$sql  = "UPDATE test_update_check SET".
			" state='1',".
			" end_time=now()".
			" WHERE update_num='".$bat_cd->real_escape($update_num)."';";
	@$bat_cd->exec_query($sql);
}
?>
