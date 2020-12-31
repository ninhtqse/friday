#!/usr/bin/php -q
<?PHP
/*

	シークレットイベント  検証、本番バッチアップロードシステム

	okabe 2013/11/29

*/
// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
//	ベースフォルダー
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


set_time_limit(0);

//      更新情報取得
$update_type = $argv[1];
$update_mode = $argv[2];
$block_num = $argv[3];
$fileup = $argv[4];
$update_num = $argv[5];

if (!$argv[1]) {
		$ERROR[] = "251";
		write_error($ERROR);
		echo "251";
		exit;
}

if (!$argv[2]) {
	$ERROR[] = "252";
	write_error($ERROR);
	echo "252";
	exit;
}

$bat_cd = new connect_db();
// $bat_cd->set_db($L_DB[$btw_server_name]);	// upd hasegawa 2018/03/29 AWS移設
$bat_cd->set_db($L_DB['srlbtw21']);
$ERROR = $bat_cd->set_connect_db();
if ($ERROR) {
	set_error($ERROR, '253');
}


if ($update_mode == "secret_puzzle") {	// シークレットイベント・パズル

	if ($update_type == 1) {
		//	本番バッチ反映
		//	DB更新
		$ERROR = secret_event_puzzle_db($bhw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '261');
		}

		//	webファイルアップ(画像ファイル、壁紙zipファイル)
		$ERROR = secret_event_puzzle_web($tw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '260w');
		}

		//	DB削除
		$ERROR = secret_event_puzzle_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '262d');
		}

	} elseif ($update_type == 2) {
		//	検証サーバー反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			//	DB更新
			$ERROR = secret_event_puzzle_db($update_server_name, $argv);
			if ($ERROR) {
				set_error($ERROR, '264');
			}
		}

	} elseif ($update_type == 3) {
		//	検証バッチDB削除
		$ERROR = secret_event_puzzle_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '266d');
		}
	}


	//	ファイル更新
	if ($update_type == 1) {
		
		//	web削除
		$ERROR = secret_event_puzzle_web_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '263w');
		}

	} elseif ($update_type == 2) {
		// 検証コアWeb ファイルアップ(画像ファイル、壁紙zipファイル)
		$ERROR = secret_event_puzzle_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '265w');
		}

	} elseif ($update_type == 3) {
		//	web削除
		$ERROR = secret_event_puzzle_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '267w');
		}
	}



} elseif ($update_mode == "secret_stamp") {     // シークレットイベント・スタンプ

	if ($update_type == 1) {
		//	本番バッチ反映
		//	DB更新
		$ERROR = secret_event_stamp_db($bhw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '271');
		}

		//	DB削除
		$ERROR = secret_event_stamp_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '272d');
		}

	} elseif ($update_type == 2) {
		//	検証サーバー反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			//	DB更新
			$ERROR = secret_event_stamp_db($update_server_name, $argv);
			if ($ERROR) {
				set_error($ERROR, '274');
			}
		}

	} elseif ($update_type == 3) {
		//	検証バッチDB削除
		$ERROR = secret_event_stamp_db_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '276d');
		}
	}


	//	ファイル更新
	if ($update_type == 1) {
		//	webファイルアップ(画像ファイル、壁紙zipファイル)
		$ERROR = secret_event_stamp_web($tw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '270w');
		}
		//	web削除
		$ERROR = secret_event_stamp_web_del($btw_server_name, $argv);
		if ($ERROR) {
			set_error($ERROR, '273w');
		}

	} elseif ($update_type == 2) {
		// 検証コアWeb ファイルアップ(画像ファイル、壁紙zipファイル)
		$ERROR = secret_event_stamp_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '275w');
		}

	} elseif ($update_type == 3) {
		//	web削除
		$ERROR = secret_event_stamp_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '277w');
		}
	}

}

	//      処理終了処理
//	update_end($update_num);	//@@@@@


// DB切断
$bat_cd->close();

//      終了
echo "0";

exit;





//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------
//      パズルデータ DBアップロード
function secret_event_puzzle_db($update_server_name, $argv) {
	global $L_DB;	// DB LIST

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$image_category = $argv['3'];	//block_num経由
	if (strlen($image_category) == 0) {
		$ERROR[] = "no image_category";
		return $ERROR;
	}

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
	$where = " WHERE";
	if ($image_category != "0") {	//全件選択ではない場合
		$where .= " secret_puzzle_image.image_category='".$bat_cd -> real_escape($image_category)."' AND";
	}
	$where .= " secret_puzzle_image.mk_flg='0';";

	//	secret_puzzle_image
	$sql  = "SELECT secret_puzzle_image.* FROM secret_puzzle_image secret_puzzle_image".$where;
//echo "*image_category=".$image_category."\n".$sql."\n"; exit ;
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'secret_puzzle_image', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //	hantei_ms_problem
	$sql  = "DELETE secret_puzzle_image FROM secret_puzzle_image".$where;
	$DELETE_SQL['secret_puzzle_image'] = $sql;
//print_r($DELETE_SQL); echo "\n"; exit;

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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE secret_puzzle_image;";
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




//---------------------------------------------------------------------------------------------
//      パズルデータ DB削除
function secret_event_puzzle_db_del($update_server_name, $argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$image_category = $argv['3'];	//block_num経由
	if (strlen($image_category) == 0) {
		$ERROR[] = "no image_category";
		return $ERROR;
	}

	//	削除クエリー
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = " WHERE";
	if ($image_category != "0") {	//全件選択ではない場合
		$where .= " secret_puzzle_image.image_category='".$bat_cd->real_escape($image_category)."' AND";
	}
	$where .= " secret_puzzle_image.mk_flg='0';";


	//	削除クエリー
	$sql  = "DELETE secret_puzzle_image FROM secret_puzzle_image secret_puzzle_image".$where;
	$DELETE_SQL['secret_puzzle_image'] = $sql;
//print_r($DELETE_SQL); echo "\n"; exit;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	return $ERROR;
}




//---------------------------------------------------------------------------------------------
//      パズル画像ファイル Web アップロード
function secret_event_puzzle_web($update_server_name,$argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	//指定パラメータ取り出し
	$update_type = $argv[1];
	$image_category = $argv['3'];	//block_num経由
	if (strlen($image_category) == 0) {
		$ERROR[] = "no image_category";
		return $ERROR;
	}
	$fileup = $argv[4];

	if ($fileup != 1) {
		return;
	}

	//対象データ群の指定
	$where = " WHERE";
	if ($image_category != "0") {	//全件選択ではない場合
		$where .= " secret_puzzle_image.image_category='".$bat_cd->real_escape($image_category)."' AND";
	}
	$where .= " secret_puzzle_image.mk_flg='0';";

	//      問題ファイル取得
	$PUZZLE_IMAGE_LIST = array();
	$sql = "SELECT secret_puzzle_image.image_category".
		" FROM secret_puzzle_image secret_puzzle_image".
		" WHERE secret_puzzle_image.display = 1".
		" AND secret_puzzle_image.mk_flg = 0".
		" ORDER BY secret_puzzle_image.image_category DESC;";
//$PUZZLE_IMAGE_LIST[] ="201311";		//echo "*sql=".$sql."\n";exit;
	if ($result = $bat_cd->query($sql)) {
		while ($list = $bat_cd->fetch_assoc($result)) {
			$PUZZLE_IMAGE_LIST[] = $list['image_category'];
		}
	}
//print_r($PUZZLE_IMAGE_LIST); echo "\n\n";

	//      画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";		// "material/secret_event/"
	test_local_glob_secretevent($PUZZLE_IMAGE_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//      フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);
//echo "*update_type=".$update_type."\ndir=".$dir."\nremote_dir=".$remote_dir."\nupdate_server_name=".$update_server_name."\n";

	//      ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_SECRET_EVENT_DIR."puzzle/");
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);
//echo "*update_type=".$update_type."\nlocal_dir=".$local_dir."\nremote_dir=".$remote_dir."\nupdate_server_name=".$update_server_name."\n";

	return $ERROR;
}




//---------------------------------------------------------------------------------------------
//      web 削除 パズル画像ファイル
function secret_event_puzzle_web_del($update_server_name, $argv) {
	global $L_DB;


	//指定パラメータ取り出し
	$update_type = $argv[1];
	$image_category = $argv['3'];	//block_num経由
	if (strlen($image_category) == 0) {
		$ERROR[] = "no image_category";
		return $ERROR;
	}
	$fileup = $argv[4];

	if ($fileup != 1) {
		return;
	}


	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
	if ($image_category > 0) {
		$remote_dir .= $image_category."/";
	}

	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return;

}





//---------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------
//      スタンプデータ DBアップロード
function secret_event_stamp_db($update_server_name, $argv) {
	global $L_DB;	// DB LIST

	$bat_cd = $GLOBALS['bat_cd'];

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
	$where = " WHERE";
	$where .= " secret_calendar_image.mk_flg='0';";

	//	secret_calendar_image
	$sql  = "SELECT secret_calendar_image.* FROM secret_calendar_image secret_calendar_image".$where;
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'secret_calendar_image', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー  //	hantei_ms_problem
	$sql  = "DELETE secret_calendar_image FROM secret_calendar_image ".$where;
	$DELETE_SQL['secret_calendar_image'] = $sql;
//print_r($DELETE_SQL); echo "\n";


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
//print_r($INSERT_VALUE); echo "\n"; $ERROR=array();

	//      トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}


	//      テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE secret_puzzle_image;";
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




//---------------------------------------------------------------------------------------------
//      スタンプデータ DB削除
function secret_event_stamp_db_del($update_server_name, $argv) {
	global $L_DB;

	$bat_cd = $GLOBALS['bat_cd'];

	//	削除クエリー
	$DELETE_SQL = array();

	//対象データ群の指定
	$where = " WHERE";
	$where .= " secret_calendar_image.mk_flg='0';";

	//	削除クエリー
	$sql  = "DELETE secret_calendar_image FROM secret_calendar_image ".$where;
	$DELETE_SQL['secret_calendar_image'] = $sql;
//print_r($DELETE_SQL); echo "\n";$ERROR=FALSE;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	return $ERROR;
}




//---------------------------------------------------------------------------------------------
//      スタンプ画像ファイル Web アップロード
function secret_event_stamp_web($update_server_name,$argv) {
	global $L_DB;

	//指定パラメータ取り出し
	$update_type = $argv[1];
	$fileup = $argv[3];

	if ($fileup != 1) {
		return;
	}

	$local_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."calendar/";
	if ($update_type == 1) {
		$mk_dir = HBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;
	} else {
		$mk_dir = BASE_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR;
	}

	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	if (file_exists($local_dir)) {
		exec("$command1", $LIST);
		exec("$command2", $LIST);
	}

	return $ERROR;
}




//---------------------------------------------------------------------------------------------
//      web 削除 スタンプ画像ファイル
function secret_event_stamp_web_del($update_server_name, $argv) {
	global $L_DB;


	//指定パラメータ取り出し
	$update_type = $argv[1];
	$fileup = $argv[3];

	if ($fileup != 1) {
		return;
	}

	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."calendar/";

	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

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
function test_local_glob_secretevent($DIR_LIST, &$LOCAL_FILES, &$last_local_time, $dir) {

	if (!$DIR_LIST) { return; }

	foreach ($DIR_LIST AS $each_dir_name) {
		$dir_name = $dir.$each_dir_name."/";
		$dir_name_ = preg_replace("/\//", "\/", $dir_name);
		//$set_file_name = $dir_name.$problem_num."*.*";
		$set_file_name = $dir_name."*.*";
		foreach (glob("$set_file_name") AS $file_name) {
			$file_stamp = filemtime($file_name);
			if ($last_local_time < $file_stamp) {
				$last_local_time = $file_stamp;
			}
			$name = preg_replace("/$dir_name_/", "", $file_name);
			$LOCAL_FILES['f'][$name] = $file_stamp;
			//$LOCAL_FILES['df'][$dir_num][] = $name;
			$LOCAL_FILES['df'][$each_dir_name][] = $name;
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
//echo "com_line=".$com_line."\n";
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

	// $file = "./error.log";	// upd hasegawa 2018/03/29 AWS移設
	$file = INCLUDE_BASE_DIR."/_www/batch/error_SECRETEVENT.log";

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
?>
