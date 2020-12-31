#!/usr/bin/php -q
<?PHP
/*

	すららテスト検証、本番バッチアップロードシステム

*/
//	ベースフォルダー
// upd start hasegawa 2018/03/29 AWS移設 SERVER_ADDRが空
// switch ($_SERVER['SERVER_ADDR']){
// // 開発Core
// case "10.3.11.100":
// 	$BASE_DIR = "/data/home";
// 	$ctw31_server_name = "srlctw11"; // 開発コアwebサーバーネーム	 // アップデートログリスト 更新処理追加
// 	break;
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

//	更新情報取得
$update_type = $argv[1];
$update_mode = $argv[2];
$course_num = $argv[3];
$stage_num = $argv[4];
$lesson_num = $argv[5];
$unit_num = $argv[6];
$block_num = $argv[7];
$option1 = $argv[8];
$option2 = $argv[9];
$option3 = $argv[10];
$option4 = $argv[11];
$option5 = $argv[12];

if (!$argv[1]) {
	$ERROR[] = "101";
	write_error($ERROR);
	echo "101";
	exit;
}

if (!$argv[2]) {
	$ERROR[] = "102";
	write_error($ERROR);
	echo "102";
	exit;
}

//	検証バッチサーバーDB接続
$bat_cd = new connect_db();
$bat_cd->set_db($L_DB["srlbtw21"]);
$ERROR = $bat_cd->set_connect_db();
if ($ERROR) {
	set_error($ERROR, '103');
}

$update_type = $argv[1];
$update_mode = $argv[2];

if ($update_mode == "test_publishing") {	//	出版社アップ
	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_publishing_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '111');
		}
		//	DB削除
		$ERROR = test_publishing_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '112d');
		}
		//	アップロードログアップ
		$ERROR = test_publishing_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '113');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_publishing_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '114');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_publishing_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '115d');
		}
	}
} elseif ($update_mode == "test_book") {	//	教科書アップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '116');
		}
		//	DB削除
		$ERROR = test_book_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '117d');
		}
		//	アップロードログアップ
		$ERROR = test_book_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '118');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '119');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '120d');
		}
	}
} elseif ($update_mode == "test_book_unit") {	//	教科書単元アップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_unit_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '121');
		}
		//	DB削除
		$ERROR = test_book_unit_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '122d');
		}
		//	アップロードログアップ
		$ERROR = test_book_unit_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '123');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_unit_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "124";
				write_error($ERROR);
				echo "124";
				exit;
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_unit_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '125d');
		}
	}
} elseif ($update_mode == "test_lms_problem_time") {	//	LMS問題回答目安時間アップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_lms_problem_time_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '126');
		}
		//	DB削除
		$ERROR = test_lms_problem_time_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '127d');
		}
		//	アップロードログアップ
		$ERROR = test_lms_problem_time_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '128');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_lms_problem_time_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '129');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_lms_problem_time_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '130d');
		}
	}
} elseif ($update_mode == "test_book_trial_list") {	//	学力診断テストマスタアップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_trial_list_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '131');
		}
		//	DB削除
		$ERROR = test_book_trial_list_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '132d');
		}
		//	アップロードログアップ
		$ERROR = test_book_trial_list_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '133');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_trial_list_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '134');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_trial_list_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '135d');
		}
	}
} elseif ($update_mode == "test_book_trial_group") {	//	学力診断テストグループアップ
	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_trial_group_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '136');
		}
		//	DB削除
		$ERROR = test_book_trial_group_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '137d');
		}
		//	アップロードログアップ
		$ERROR = test_book_trial_group_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '138');
		}
	} elseif ($update_type == 2) {
//		foreach ($L_TSTC_DB_ADD_E AS $update_server_name) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_trial_group_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '139');
			}
		}
		
		//	学力診断テストグループ情報統合検証DBアップロード
		$ERROR = test_book_trial_group_es_db('srlctd01',$argv);
		if ($ERROR) {
			set_error($ERROR, '139es');
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_trial_group_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '140d');
		}
	}
} elseif ($update_mode == "test_book_trial") {	//	学力診断テスト単元マスタアップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_trial_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '141');
		}
		//	DB削除
		$ERROR = test_book_trial_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '142d');
		}
		//	アップロードログアップ
		$ERROR = test_book_trial_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '143');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_trial_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '144');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_trial_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '145d');
		}
	}
} elseif ($update_mode == "test_book_trial_unit") {	//	学力診断テスト単元アップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_book_trial_unit_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '146');
		}
		//	DB削除
		$ERROR = test_book_trial_unit_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '147d');
		}
		//	アップロードログアップ
		$ERROR = test_book_trial_unit_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '148');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_book_trial_unit_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '149');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_book_trial_unit_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '150d');
		}
	}
} elseif ($update_mode == "test_problem") {	//	問題設定(定期テスト)アップ

	if ($update_type == 1) {
		//  本番バッチアップ
		//	ファイルアップ（音声ファイルや画像ファイルなど）
		$ERROR = test_problem_web($tw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '156');
		}

		//	検証バッチファイル削除
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '157d');
		}
		// 本番バッチＤＢ更新
		$ERROR = test_problem_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '151');
		}
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '152d');
		}

		//	アップロードログアップ
		$ERROR = test_problem_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '153');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/10
		$ERROR = test_mate_upd_log_end($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '160d');
		}
	} elseif ($update_type == 2) {
		// 検証バッチサーバから検証分散ＤＢに配布
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_problem_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '154');
			}
		}

		//	検証バッチサーバから検証Webにファイル（音声や画像）をアップ
		$ERROR = test_problem_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '158');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/09
		$ERROR = test_problem_test_mate_upd_log_update($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '160');
		}
	} elseif ($update_type == 3) {
		//	検証バッチファイル削除
		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '159d');
		}

		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '155d');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/10
		$ERROR = test_mate_upd_log_delete($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '161d');
		}
	}

	//	処理終了処理
	$update_num = $argv['7'];
	update_end($update_num);

} elseif ($update_mode == "test_problem_trial") {	//	問題設定(学力診断テスト)アップ

	if ($update_type == 1) {
		//	ファイル本番バッチアップ
		$ERROR = test_problem_trial_web($tw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '166');
		}

		//	検証バッチファイル削除
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_trial_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '167d');
		}

		$ERROR = test_problem_trial_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '161');
		}
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_trial_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '162d');
		}

		//	アップロードログアップ
		$ERROR = test_problem_trial_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '163');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/10
		$ERROR = test_mate_upd_log_end($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '160d');
		}
	} elseif ($update_type == 2) {
		//	DBアップ
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_problem_trial_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '164');
			}
		}

		//	ファイルアップ
		$ERROR = test_problem_trial_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '168');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/09
		$ERROR = test_problem_trial_test_mate_upd_log_update($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '170');
		}
	} elseif ($update_type == 3) {
		//	検証バッチファイル削除
		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_trial_web_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '169d');
		}

		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		//$ERROR = test_problem_trial_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '165d');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/10
		$ERROR = test_mate_upd_log_delete($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '160d');
		}
	}

	//	処理終了処理
	$update_num = $argv['7'];
	update_end($update_num);

} elseif ($update_mode == "test_ms_core_code") {	//	コードマスターアップ
	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_ms_core_code_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '171');
		}
		//	DB削除
		$ERROR = test_ms_core_code_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '172d');
		}
		//	アップロードログアップ
		$ERROR = test_ms_core_code_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '173');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_ms_core_code_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '174');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_ms_core_code_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '175d');
		}
	}
} elseif ($update_mode == "test_upnavi") {	//	学力Upナビ単元
	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_upnavi_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '176');
		}
		//	DB削除
		$ERROR = test_upnavi_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '177d');
		}
		//	アップロードログアップ
		$ERROR = test_upnavi_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '178');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_upnavi_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '179');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_upnavi_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '180d');
		}
	}
// add start yoshizwa 2015/09/16 02_作業要件/34_数学検定/数学検定
} elseif ($update_mode == "test_math_test_group") {	//	数学検定グループアップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_math_test_group_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '181');
		}
		//	DB削除
		$ERROR = test_math_test_group_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '182d');
		}
		//	アップロードログアップ
		$ERROR = test_math_test_group_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '183');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_math_test_group_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '184');
			}
		}
		//	数学検定グループ情報統合検証DBアップロード
		$ERROR = test_math_test_group_es_db('srlctd01',$argv);  // 検証中は開発の統合DBを更新
		if ($ERROR) {
			set_error($ERROR, '185es');
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_math_test_group_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '186d');
		}
	}

} elseif ($update_mode == "test_math_test_control_unit") {		//	数学検定出題単元アップ

		//	DB更新
		if ($update_type == 1) {
			$ERROR = test_math_test_control_unit_db($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '187');
			}
			//	DB削除
			$ERROR = test_math_test_control_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '188d');
			}
			//	アップロードログアップ
			$ERROR = test_math_test_control_unit_log($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '189');
			}
		} elseif ($update_type == 2) {
			foreach ($L_TSTC_DB AS $update_server_name) {
				$ERROR = test_math_test_control_unit_db($update_server_name,$argv);
				if ($ERROR) {
					set_error($ERROR, '190');
				}
			}
		} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = test_math_test_control_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '191d');
			}
		}

} elseif ($update_mode == "test_math_test_unit") {				//	数学検定採点単元アップ

		//	DB更新
		if ($update_type == 1) {
			$ERROR = test_math_test_unit_db($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '192');
			}
			//	DB削除
			$ERROR = test_math_test_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '193d');
			}
			//	アップロードログアップ
			$ERROR = test_math_test_unit_db_log($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '194');
			}
		} elseif ($update_type == 2) {
			foreach ($L_TSTC_DB AS $update_server_name) {
				$ERROR = test_math_test_unit_db($update_server_name,$argv);
				if ($ERROR) {
					set_error($ERROR, '195');
				}
			}
		} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = test_math_test_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '196d');
			}
		}

} elseif ($update_mode == "test_problem_math") {			//	問題設定（数学検定）アップ

	if ($update_type == 1) {
		//	ファイル本番バッチアップ
		$ERROR = test_problem_math_web($tw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '197');
		}
		//	検証バッチファイル削除
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '198d');
		}
		//$ERROR = test_problem_trial_db($bhw_server_name,$argv);
		$ERROR = test_problem_math_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '199');
		}
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '200d');
		}
		//	アップロードログアップ
		$ERROR = test_problem_math_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '201');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/12
		$ERROR = test_mate_upd_log_end($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '201d');
		}

		// $update_num = $argv['4'];	// del 2018/04/02 yoshizawa
		$update_num = $argv['5'];		// add 2018/04/02 yoshizawa

	} elseif ($update_type == 2) {
		//	DBアップ
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_problem_math_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '202');
			}
		}
		//	ファイルアップ
		$ERROR = test_problem_math_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '203');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/09
		$ERROR = test_problem_math_test_mate_upd_log_update($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '185');
		}

		$update_num = $argv['5'];

	} elseif ($update_type == 3) {
		//	検証バッチファイル削除
		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '204d');
		}
		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '205d');
		}

		//	すらら開発環境のアップデートログリスト更新		// add oda 2015/11/12
		$ERROR = test_mate_upd_log_delete($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '206d');
		}
	}

	//	処理終了処理
	update_end($update_num);

}
// add end yoshizwa 02_作業要件/34_数学検定/数学検定

// add start 2018/11/26 yoshizawa すらら英単語
elseif ($update_mode == "test_vocabulary_test_type") {	//	すらら英単語種類

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_vocabulary_test_type_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '207');
		}
		//	DB削除
		$ERROR = test_vocabulary_test_type_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '208d');
		}
		//	アップロードログアップ
		$ERROR = test_vocabulary_test_type_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '204');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_vocabulary_test_type_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '205');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_vocabulary_test_type_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '206d');
		}
	}

} elseif ($update_mode == "test_vocabulary_test_category1") {	//	すらら英単語種別1

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_vocabulary_test_category1_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '207');
		}
		//	DB削除
		$ERROR = test_vocabulary_test_category1_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '208d');
		}
		//	アップロードログアップ
		$ERROR = test_vocabulary_test_category1_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '209');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_vocabulary_test_category1_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '210');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_vocabulary_test_category1_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '211d');
		}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = test_vocabulary_test_category1_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '212d');
			}
			// del 2018/11/26 yoshizawa マスタアップの順番によってアップの対象コンテンツが消えてしまうためcssは削除しない。
			// //	web削除
			// $ERROR = test_vocabulary_test_category1_web_del($btw_server_name,$argv);
			// if ($ERROR) {
			// 	set_error($ERROR, '213d');
			// }
	} elseif ($update_type == 2) {
		$ERROR = test_vocabulary_test_category1_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '214');
		}
	} elseif ($update_type == 3) {
			// del 2018/11/26 yoshizawa マスタアップの順番によってアップの対象コンテンツが消えてしまうためcssは削除しない。
			//	web削除
			// $ERROR = test_vocabulary_test_category1_web_del($btw_server_name,$argv);
			// if ($ERROR) {
			// 	set_error($ERROR, '215d');
			// }
	}

} elseif ($update_mode == "test_vocabulary_test_category2") {	//	すらら英単語テスト種別2アップ

	//	DB更新
	if ($update_type == 1) {
		$ERROR = test_vocabulary_test_category2_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '216');
		}
		//	DB削除
		$ERROR = test_vocabulary_test_category2_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '217d');
		}
		//	アップロードログアップ
		$ERROR = test_vocabulary_test_category2_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '218');
		}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_vocabulary_test_category2_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '219');
			}
		}
	} elseif ($update_type == 3) {
		//	DB削除
		$ERROR = test_vocabulary_test_category2_db_del($btw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '220d');
		}
	}

} elseif ($update_mode == "test_problem_vocabulary") {	//	問題設定(すらら英単語テスト)アップ

	if ($update_type == 1) {
		//  本番バッチアップ
		//	ファイルアップ（音声ファイルや画像ファイルなど）
		$ERROR = test_problem_vocabulary_web($tw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '221');
		}

		//	検証バッチファイル削除
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '222d');
		}
		// 本番バッチＤＢ更新
		$ERROR = test_problem_vocabulary_db($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '223');
		}
		//	DB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '224d');
		}

		//	アップロードログアップ
		$ERROR = test_problem_vocabulary_db_log($bhw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '225');
		}

		//	すらら開発環境のアップデートログリスト更新	
		$ERROR = test_mate_upd_log_end($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '226d');
		}
	} elseif ($update_type == 2) {

		// 検証バッチサーバから検証分散ＤＢに配布
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = test_problem_vocabulary_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '227');
			}
		}

		//	検証バッチサーバから検証Webにファイル（音声や画像）をアップ
		$ERROR = test_problem_vocabulary_web($ctw_server_name,$argv);
		if ($ERROR) {
			set_error($ERROR, '228');
		}

		//	すらら開発環境のアップデートログリスト更新	
		$ERROR = test_problem_vocabulary_test_mate_upd_log_update($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '229');
		}
	} elseif ($update_type == 3) {
		//	検証バッチファイル削除
		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '230d');
		}

		//	検証バッチDB削除	複数のテストで同じ問題を出題していて、本番バッチ時に問題が上がらなくなる為削除しない
		if ($ERROR) {
			set_error($ERROR, '231d');
		}

		//	すらら開発環境のアップデートログリスト更新	
		$ERROR = test_mate_upd_log_delete($ctw31_server_name, $update_mode, $argv);
		if ($ERROR) {
			set_error($ERROR, '232d');
		}
	}

	//	処理終了処理
	$update_num = $argv['7'];
	update_end($update_num);


// add start 2019/05/21 yoshizawa すらら英単語テストワード管理
} elseif ($update_mode == "test_english_word") {	//	ワード情報

	//	DB更新
	if ($update_type == 1) {
			// 本番バッチDBに反映
			$ERROR = course_english_word_db($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '233');
			}
			// 検証バッチDB削除
			$ERROR = course_english_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '234');
			}
			//	アップロードログアップ
			$ERROR = test_english_word_db_log($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '235');
			}
	} elseif ($update_type == 2) {
		// 検証分散DB１～８へ反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_english_word_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '236');
			}
		}
	} elseif ($update_type == 3) {
			// 検証バッチDB削除
			$ERROR = course_english_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '237');
			}
	}

// add end 2019/05/21 yoshizawa すらら英単語テストワード管理
}
// add end 2018/11/26 yoshizawa すらら英単語


// add start okabe 2020/09/05 テスト標準化
// start 標準化カテゴリー standard_category
elseif ($update_mode == "test_standard_category") {	// 標準化カテゴリ

	//	DB更新
	if ($update_type == 1) {
			// 本番バッチDBに反映
			$ERROR = standard_category_db($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '238');
			}
			// 検証バッチDB削除
			$ERROR = standard_category_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '239');
			}
			//	アップロードログアップ
			$ERROR = standard_category_db_log($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '240');
			}
	} elseif ($update_type == 2) {
		// 各検証分散DBへ反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = standard_category_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '241');
			}
		}
	} elseif ($update_type == 3) {
			// 検証バッチDB削除
			$ERROR = standard_category_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '242');
			}
	}

}
// start 数学検定情報 math_test_book_info
elseif ($update_mode == "test_math_test_book_info") {	// 数学検定情報

	//	DB更新
	if ($update_type == 1) {
			// 本番バッチDBに反映
			$ERROR = math_test_book_info_db($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '243');
			}
			// 検証バッチDB削除
			$ERROR = math_test_book_info_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '244');
			}
			//	アップロードログアップ
			$ERROR = math_test_book_info_db_log($bhw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '245');
			}
	} elseif ($update_type == 2) {
		// 各検証分散DBへ反映
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = math_test_book_info_db($update_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '246');
			}
		}
	} elseif ($update_type == 3) {
			// 検証バッチDB削除
			$ERROR = math_test_book_info_db_del($btw_server_name,$argv);
			if ($ERROR) {
				set_error($ERROR, '247');
			}
	}

}
// add end okabe 2020/09/05 テスト標準化



// DB切断
$bat_cd->close();

//	終了
echo "0";

exit;

//---------------------------------------------------------------------------------------------
//	出版社情報DBアップロード
function test_publishing_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_publishing;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_publishing', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_publishing;";
	$DELETE_SQL[] = $sql;


	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_publishing;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	出版社情報DB削除
function test_publishing_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM ms_publishing;";
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



//	出版社情報DBアップロードログアップ
function test_publishing_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];

	//	DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	教科書情報DBアップロード
function test_book_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
		$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_book".
			" WHERE publishing_id!='0'".
			" AND course_num='".$bat_cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$bat_cd->real_escape($gknn)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_book', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	$sql  = "DELETE FROM ms_book".
			" WHERE publishing_id!='0'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$cd->real_escape($gknn)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_book;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	教科書情報DB削除
function test_book_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_book".
			" WHERE publishing_id!='0'".
			" AND course_num='".$bat_cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$bat_cd->real_escape($gknn)."'";
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



//	教科書情報DBアップロードログアップ
function test_book_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}
/*
	if ($gknn == "") {
		$ERROR[] = "Not gknn";
		return $ERROR;
	}
*/

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	$sql .=	";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		if ($gknn != "0" && $gknn != "") {
			$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
		} else {
			$sql .= " AND stage_num IS NULL";
		}
		$sql .=	";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	教科書単元情報DBアップロード
function test_book_unit_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$publishing_id = $argv['4'];
	$gknn = $argv['5'];
	$book_id = $argv['6'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	$serch_course = sprintf("%02d", $course_num);
	$serch_publishing_id = sprintf("%03d", $publishing_id);
	$serch_gknn = $cd->real_escape($gknn);
	$serch_book_id = $cd->real_escape($book_id);
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE book_id='".$serch_book_id."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE book_id like '".$serch_publishing_id.$serch_course.$serch_gknn."%'";
	} elseif ($serch_publishing_id != "0" && $serch_publishing_id != "") {
		$where .= " WHERE book_id like '".$serch_publishing_id.$serch_course."%'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE substr(book_id, 4, 2)='".$serch_course."'";
		$where .= " AND book_id NOT LIKE '000%'";
	}
	$sql  = "SELECT * FROM ms_book_unit".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_book_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_book_unit".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_book_unit;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	教科書単元情報DB削除
function test_book_unit_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$publishing_id = $argv['4'];
	$gknn = $argv['5'];
	$book_id = $argv['6'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$where = "";
	$serch_course = sprintf("%02d", $course_num);
	$serch_publishing_id = sprintf("%03d", $publishing_id);
	$serch_gknn = $bat_cd->real_escape($gknn);
	$serch_book_id = $bat_cd->real_escape($book_id);
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE book_id='".$serch_book_id."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE book_id like '".$serch_publishing_id.$serch_course.$serch_gknn."%'";
	} elseif ($serch_publishing_id != "0" && $serch_publishing_id != "") {
		$where .= " WHERE book_id like '".$serch_publishing_id.$serch_course."%'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE substr(book_id, 4, 2)='".$serch_course."'";
		$where .= " AND book_id NOT LIKE '000%'";
	}

	$sql  = "DELETE FROM ms_book_unit".
			$where.";";
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



//	教科書単元情報DBアップロードログアップ
function test_book_unit_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$publishing_id = $argv['4'];
	$gknn = $argv['5'];
	$book_id = $argv['6'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	if ($publishing_id > 0) {
		$sql .= " AND stage_num='".$cd->real_escape($publishing_id)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND lesson_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	if ($book_id != "0" && $book_id != "") {
		$sql .= " AND unit_num='".$cd->real_escape($book_id)."'";
	} else {
		$sql .= " AND unit_num IS NULL";
	}
	$sql .=	";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		if ($publishing_id > 0) {
			$sql .= " AND stage_num='".$cd->real_escape($publishing_id)."'";
		} else {
			$sql .= " AND stage_num IS NULL";
		}
		if ($gknn != "0" && $gknn != "") {
			$sql .= " AND lesson_num='".$cd->real_escape($gknn)."'";
		} else {
			$sql .= " AND lesson_num IS NULL";
		}
		if ($book_id != "0" && $book_id != "") {
			$sql .= " AND unit_num='".$cd->real_escape($book_id)."'";
		} else {
			$sql .= " AND unit_num IS NULL";
		}
		$sql .=	";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`lesson_num` ,".
				"`unit_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($publishing_id)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($book_id)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	LMS問題回答目安時間情報DBアップロード
function test_lms_problem_time_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_problem_attribute".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_problem_attribute', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_problem_attribute".
			" WHERE course_num='".$cd->real_escape($course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_problem_attribute;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	LMS問題回答目安時間情報DB削除
function test_lms_problem_time_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_problem_attribute".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."';";
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



//	LMS問題回答目安時間情報DBアップロードログアップ
function test_lms_problem_time_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	学力診断テストマスタ情報DBアップロード
function test_book_trial_list_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_test_default".
			" WHERE test_type='4'".
			" AND course_num='".$bat_cd->real_escape($course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_default', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	$sql  = "DELETE FROM ms_test_default".
			" WHERE test_type='4'".
			" AND course_num='".$bat_cd->real_escape($course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_default;";
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



//	学力診断テストマスタ情報DB削除
function test_book_trial_list_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_default".
			" WHERE test_type='4'".
			" AND course_num='".$bat_cd->real_escape($course_num)."';";
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



//	学力診断テストマスタ情報DBアップロードログアップ
function test_book_trial_list_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	学力診断テストグループ情報DBアップロード
function test_book_trial_group_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$gknn = $argv['3'];
	$test_group_id = $argv['4'];

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($gknn != "" && $gknn != "0") {
		$WHERE[] = " mbg.test_gknn='".$bat_cd->real_escape($gknn)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'GTEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id = '' "; // add 2020/09/12 cuong テスト標準化開発

	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	test_group_list
	$sql  = "SELECT bgl.* FROM test_group_list bgl".
			" LEFT JOIN ms_test_group mbg ON mbg.test_group_id=bgl.test_group_id".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'test_group_list', $INSERT_NAME, $INSERT_VALUE);
	}
	//	ms_test_group
	$sql  = "SELECT mbg.* FROM ms_test_group mbg".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_group', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	//	test_group_list
	$sql  = "DELETE bgl FROM test_group_list bgl".
			" LEFT JOIN ms_test_group mbg ON mbg.test_group_id=bgl.test_group_id".
			$where.";";
	$DELETE_SQL[] = $sql;
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE test_group_list;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_group;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	学力診断テストグループ情報統合検証DBアップロード
function test_book_trial_group_es_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$gknn = $argv['3'];
	$test_group_id = $argv['4'];

	//	統合検証ＤＢ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($gknn != "" && $gknn != "0") {
		$WHERE[] = " mbg.test_gknn='".$bat_cd->real_escape($gknn)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'GTEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id = '' "; // add 2020/09/12 cuong テスト標準化開発

	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}

	//	ms_test_group
	$sql  = "SELECT mbg.* FROM ms_test_group mbg".
			$where.";";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'ms_test_group', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ms_test_group;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	学力診断テストグループ情報DB削除
function test_book_trial_group_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$gknn = $argv['3'];
	$test_group_id = $argv['4'];

	//	削除クエリー
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($gknn != "" && $gknn != "0") {
		$WHERE[] = " mbg.test_gknn='".$bat_cd->real_escape($gknn)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'GTEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id = '' "; // add 2020/09/12 cuong テスト標準化開発

	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	test_group_list
	$sql  = "DELETE bgl FROM test_group_list bgl".
			" LEFT JOIN ms_test_group mbg ON mbg.test_group_id=bgl.test_group_id".
			$where.";";
	$DELETE_SQL[] = $sql;
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
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



//	学力診断テストグループ情報DBアップロードログアップ
function test_book_trial_group_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$gknn = $argv['3'];
	$test_group_id = $argv['4'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];

	//	本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'";
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND course_num='".$cd->real_escape($gknn)."'";
	}
	if ($test_group_id > 0) {
		$sql .= " AND stage_num='".$cd->real_escape($test_group_id)."'";
	} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'";
		if ($gknn != "" && $gknn != "0") {
			$sql .= " AND course_num='".$cd->real_escape($gknn)."'";
		}
		if ($test_group_id > 0) {
			$sql .= " AND stage_num='".$cd->real_escape($test_group_id)."'";
		} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($test_group_id)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();
	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	学力診断テスト単元マスタ情報DBアップロード
function test_book_trial_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_book".
			" WHERE publishing_id='0'".
			" AND course_num='".$bat_cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$bat_cd->real_escape($gknn)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_book', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	$sql  = "DELETE FROM ms_book".
			" WHERE publishing_id='0'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$cd->real_escape($gknn)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_book;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	学力診断テスト単元マスタ情報DB削除
function test_book_trial_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	DBデーター削除クエリー
	$sql  = "DELETE FROM ms_book".
			" WHERE publishing_id='0'".
			" AND course_num='".$bat_cd->real_escape($course_num)."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND gknn='".$bat_cd->real_escape($gknn)."'";
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



//	学力診断テスト単元マスタ情報DBアップロードログアップ
function test_book_trial_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	// update start 2016/04/12 プラクティスアップデートエラー対応
	//$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
	//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
	//		" AND state='1'";
	//		" AND course_num='".$cd->real_escape($course_num)."'";
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		// update start 2016/04/12 プラクティスアップデートエラー対応
		//$sql  = "UPDATE test_mate_upd_log SET".
		//		" state='3'".
		//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
		//		" AND state='1'";
		//		" AND course_num='".$cd->real_escape($course_num)."'";
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		// update end 2016/04/12 プラクティスアップデートエラー対応
		if ($gknn != "" && $gknn != "0") {
			$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
		} else {
			$sql .= " AND stage_num IS NULL";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	学力診断テスト単元情報DBアップロード
function test_book_trial_unit_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$book_id = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	$serch_course = sprintf("%02d", $course_num);
	$serch_gknn = $bat_cd->real_escape($gknn);
	$serch_book_id = $bat_cd->real_escape($book_id);
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE book_id='".$serch_book_id."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE book_id like '000".$serch_course.$serch_gknn."%'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE book_id like '000".$serch_course."%'";
	}
	$sql  = "SELECT * FROM ms_book_unit".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_book_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_book_unit".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_book_unit;";
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



//	学力診断テスト単元情報DB削除
function test_book_trial_unit_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$book_id = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$where = "";
	$serch_course = sprintf("%02d", $course_num);
	$serch_gknn = $bat_cd->real_escape($gknn);
	$serch_book_id = $bat_cd->real_escape($book_id);
	if ($serch_book_id != "0" && $serch_book_id != "") {
		$where .= " WHERE book_id='".$serch_book_id."'";
	} elseif ($serch_gknn != "0" && $serch_gknn != "") {
		$where .= " WHERE book_id like '000".$serch_course.$serch_gknn."%'";
	} elseif ($serch_course != "0" && $serch_course != "") {
		$where .= " WHERE book_id like '000".$serch_course."%'";
	}

	$sql  = "DELETE FROM ms_book_unit".
			$where.";";
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



//	学力診断テスト単元情報DBアップロードログアップ
function test_book_trial_unit_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$book_id = $argv['5'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	// update start 2016/04/12 プラクティスアップデートエラー対応
	//$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
	//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
	//		" AND state='1'";
	//		" AND course_num='".$cd->real_escape($course_num)."'";
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($book_id != "0" && $book_id != "") {
		$sql .= " AND lesson_num='".$cd->real_escape($book_id)."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		// update start 2016/04/12 プラクティスアップデートエラー対応
		//$sql  = "UPDATE test_mate_upd_log SET".
		//		" state='3'".
		//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
		//		" AND state='1'";
		//		" AND course_num='".$cd->real_escape($course_num)."'";
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($book_id != "0" && $book_id != "") {
		$sql .= " AND lesson_num='".$cd->real_escape($book_id)."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($book_id)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	問題設定(定期テスト)アップロード
function test_problem_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$core_code = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	if ($course_num > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}
	if ($core_code != "0" && $core_code != "") {
		list($term_bnr_ccd,$term_kmk_ccd) = explode("_", $core_code);
		$where .= " AND mtdp.term_bnr_ccd='".$bat_cd->real_escape($term_bnr_ccd)."'";
		$where .= " AND mtdp.term_kmk_ccd='".$bat_cd->real_escape($term_kmk_ccd)."'";
	}

	// update start oda 2014/10/17 課題要望一覧No352 ＤＢ更新処理変更（ms_test_default_problemのPK修正の為）

	//	登録データー取得->REPLACE作成用情報生成(PKやUNIQUEが有るテーブルが対象です）
	//	ms_test_default_problem
	$sql  = "SELECT DISTINCT mtdp.* FROM ms_test_default_problem mtdp ".
			" WHERE mtdp.default_test_num='0' ".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_default_problem', $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	ms_test_problem
	$sql  = "SELECT DISTINCT mtp.* FROM ms_test_problem mtp ".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=mtp.problem_num ".
			" AND mtdp.default_test_num='0' ".
			$where.
			" WHERE mtdp.problem_num>0;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_problem', $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	book_unit_test_problem
	$sql  = "SELECT DISTINCT butp.* FROM book_unit_test_problem butp ".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num ".
			" AND mtdp.default_test_num='0' ".
			$where.
			" WHERE mtdp.problem_num>0 ".
			"   AND butp.default_test_num='0'".
			";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'book_unit_test_problem', $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	登録データー取得->INSERT作成用情報生成(PKやUNIQUEが無いテーブルが対象です）
	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='2'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='2';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "SELECT DISTINCT usp.* FROM upnavi_section_problem usp".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='2'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND usp.problem_table_type='2';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'upnavi_section_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "SELECT DISTINCT usp.* FROM upnavi_section_problem usp".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND usp.problem_table_type='1';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'upnavi_section_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー(PKやUNIQUEが無いテーブルが対象です）
	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='2'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='2';";
	$DELETE_SQL['book_unit_lms_unit_2'] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL['book_unit_lms_unit_1'] = $sql;

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='2'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND usp.problem_table_type='2';";
	$DELETE_SQL['upnavi_section_problem_2'] = $sql;

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
			" AND mtdp.default_test_num='0'".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND usp.problem_table_type='1';";
	$DELETE_SQL['upnavi_section_problem_1'] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	削除処理
	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $table_name => $sql) {
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

	// REPLACE処理（対象テーブル：ms_test_default_problem/ms_test_problem/book_unit_test_problem）
	if (count($REPLACE_NAME) && count($REPLACE_VALUE)) {
		foreach ($REPLACE_NAME AS $table_name => $replace_name) {
			if ($REPLACE_VALUE[$table_name]) {
				foreach ($REPLACE_VALUE[$table_name] AS $values) {
					$sql  = "REPLACE INTO ".$table_name.
							" (".$replace_name.") ".
							" VALUES ".$values.";";
					if (!$cd->exec_query($sql)) {
						$ERROR[] = "SQL REPLACE ERROR<br>$sql";

						$sql  = "ROLLBACK";
						if (!$cd->exec_query($sql)) {
							$ERROR[] = "SQL ROLLBACK ERROR";
						}
						$cd->close();
						return $ERROR;
					}
				}
			}
		}
	}

	// INSERT処理（対象テーブル：book_unit_lms_unit/upnavi_section_problem）
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				foreach ($INSERT_VALUE[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
							" (".$insert_name.") ".
							" VALUES ".$values.";";
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
		}
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}
	// update end oda 2014/10/17 課題要望一覧No352

	//	テーブル最適化
	$OPTIMIZE_SQL = array();
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_default_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_lms_unit;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE upnavi_section_problem;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}


////	問題設定(定期テスト)削除		2014/10/17 oda 未使用です。
//function test_problem_db_del($update_server_name,$argv) {
//	global $L_DB;
//
//	$course_num = $argv['3'];
//	$gknn = $argv['4'];
//	$core_code = $argv['5'];
//	if ($course_num < 1) {
//		$ERROR[] = "Not course_num";
//		return $ERROR;
//	}
//
//	//	検証バッチDB接続
//	$connect_db = new connect_db();
//	$connect_db->set_db($L_DB[$update_server_name]);
//	$ERROR = $connect_db->set_connect_db();
//	if ($ERROR) {
//		return $ERROR;
//	} else {
//		$sel_db = $connect_db->get_db();
//		$sel_dbname = $connect_db->get_dbname();
//	}
//
//	//	データーベース更新
//	$DELETE_SQL = array();
//
//	$where = "";
//	if ($course_num > 0) {
//		$where .= " AND mtdp.course_num='".$course_num."'";
//	}
//	if ($gknn != "0" && $gknn != "") {
//		$where .= " AND mtdp.gknn='".$gknn."'";
//	}
//	if ($core_code != "0" && $core_code != "") {
//		list($term_bnr_ccd,$term_kmk_ccd) = split("_", $core_code);
//		$where .= " AND mtdp.term_bnr_ccd='".$term_bnr_ccd."'";
//		$where .= " AND mtdp.term_kmk_ccd='".$term_kmk_ccd."'";
//	}
//
//	//	検証バッチDBデーター削除クエリー
//	//	ms_test_problem
//	$sql  = "DELETE mtp FROM ms_test_problem mtp".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=mtp.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='2'".
//				$where.
//			" WHERE mtdp.problem_num>0;";
//	$DELETE_SQL[] = $sql;
//
//	//	book_unit_test_problem	problem_table_type='2'
//	$sql  = "DELETE butp FROM book_unit_test_problem butp".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='2'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND butp.default_test_num='0'".
//			" AND butp.problem_table_type='2';";
//	$DELETE_SQL[] = $sql;
//
//	//	book_unit_test_problem	problem_table_type='1'
//	$sql  = "DELETE butp FROM book_unit_test_problem butp".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='1'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND butp.default_test_num='0'".
//			" AND butp.problem_table_type='1';";
//	$DELETE_SQL[] = $sql;
//
//	//	book_unit_lms_unit	problem_table_type='2'
//	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='2'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND bulu.problem_table_type='2';";
//	$DELETE_SQL[] = $sql;
//
//	//	book_unit_lms_unit	problem_table_type='1'
//	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='1'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND bulu.problem_table_type='1';";
//	$DELETE_SQL[] = $sql;
//
//	//	upnavi_section_problem	problem_table_type='2'
//	$sql  = "DELETE usp FROM upnavi_section_problem usp".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='2'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND usp.problem_table_type='2';";
//	$DELETE_SQL[] = $sql;
//
//	//	upnavi_section_problem	problem_table_type='1'
//	$sql  = "DELETE usp FROM upnavi_section_problem usp".
//			" INNER JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
//				" AND mtdp.default_test_num='0'".
//				" AND mtdp.problem_table_type='1'".
//				$where.
//			" WHERE mtdp.problem_num>0".
//			" AND usp.problem_table_type='1';";
//	$DELETE_SQL[] = $sql;
//
//	//	ms_test_default_problem
//	$sql  = "DELETE mtdp FROM ms_test_default_problem mtdp".
//			" WHERE mtdp.default_test_num='0'".
//			$where.";";
//	$DELETE_SQL[] = $sql;
//
//	if ($DELETE_SQL) {
//		foreach ($DELETE_SQL AS $sql) {
//			if (!mysql_query($sql,DB)) {
//				$ERROR[] = "SQL DELETE ERROR<br>$sql";
//			}
//		}
//	}
//
//	return $ERROR;
//}



//	問題設定(定期テスト)情報DBアップロードログアップ
function test_problem_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$core_code = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	// update start 2016/04/12 プラクティスアップデートエラー対応
	//$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
	//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
	//		" AND state='1'";
	//		" AND course_num='".$cd->real_escape($course_num)."'";
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
	}
	if ($core_code != "" && $core_code != "0") {
		$sql .= " AND lesson_num='".$cd->real_escape($core_code)."'";
	} else {
		$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		// update start 2016/04/12 プラクティスアップデートエラー対応
		//$sql  = "UPDATE test_mate_upd_log SET".
		//		" state='3'".
		//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
		//		" AND state='1'";
		//		" AND course_num='".$cd->real_escape($course_num)."'";
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		// update end 2016/04/12 プラクティスアップデートエラー対応
		if ($gknn != "" && $gknn != "0") {
			$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
		} else {
			$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
		}
		if ($core_code != "" && $core_code != "0") {
			$sql .= " AND lesson_num='".$cd->real_escape($core_code)."'";
		} else {
			$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($core_code)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(定期テスト)webアップロード
function test_problem_web($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$update_type = $argv[1];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$core_code = $argv['5'];
	$fileup = $argv['6'];

	if ($fileup != 1) {
		return;
	}

	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$where = "";
	if ($course_num > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}
	if ($core_code != "0" && $core_code != "") {
		list($term_bnr_ccd,$term_kmk_ccd) = split("_", $core_code);
		$where .= " AND mtdp.term_bnr_ccd='".$bat_cd->real_escape($term_bnr_ccd)."'";
		$where .= " AND mtdp.term_kmk_ccd='".$bat_cd->real_escape($term_kmk_ccd)."'";
	}
	$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
			" WHERE mtdp.default_test_num='0'".
			$where.
			" AND mtdp.problem_table_type='2'".
			" AND mtdp.mk_flg='0'".
			" ORDER BY mtdp.problem_num;";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	//	音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	return $ERROR;
}



////	問題設定(定期テスト)web削除		2014/10/17 oda 未使用です。
//function test_problem_web_del($update_server_name,$argv) {
//	global $L_DB;
//
//	$update_type = $argv[1];
//	$course_num = $argv['3'];
//	$gknn = $argv['4'];
//	$core_code = $argv['5'];
//	$fileup = $argv['6'];
//
//	if ($fileup != 1) {
//		return;
//	}
//
//	if ($course_num < 1) {
//		$ERROR[] = "Not course_num";
//		return $ERROR;
//	}
//
//	//	問題ファイル取得
//	$PROBLEM_LIST = array();
//	$where = "";
//	if ($course_num > 0) {
//		$where .= " AND mtdp.course_num='".$course_num."'";
//	}
//	if ($gknn != "0" && $gknn != "") {
//		$where .= " AND mtdp.gknn='".$gknn."'";
//	}
//	if ($core_code != "0" && $core_code != "") {
//		list($term_bnr_ccd,$term_kmk_ccd) = split("_", $core_code);
//		$where .= " AND mtdp.term_bnr_ccd='".$term_bnr_ccd."'";
//		$where .= " AND mtdp.term_kmk_ccd='".$term_kmk_ccd."'";
//	}
//	$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
//			" WHERE mtdp.default_test_num='0'".
//			$where.
//			" AND mtdp.problem_table_type='2'".
//			" AND mtdp.mk_flg='0'".
//			" ORDER BY mtdp.problem_num;";
//	if ($result = mysql_db_query(DBNAME, $sql, DB)) {
//		while ($list=mysql_fetch_array($result,MYSQL_ASSOC)) {
//			$problem_num = $list['problem_num'];
//			$PROBLEM_LIST[] = $problem_num;
//		}
//	}
//
//	//	画像ファイル
//	$LOCAL_FILES = array();
//	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
//	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);
//
//	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
//	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);
//
//	//	音声ファイル
//	$LOCAL_FILES = array();
//	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
//	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);
//
//	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
//	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);
//
//	return;
//}



//---------------------------------------------------------------------------------------------
//	問題設定(学力診断テスト)アップロード
function test_problem_trial_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$and .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($course_num > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$and .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$cn_where .= " AND mtp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$and .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}

	//	ms_test_default_problem
	$sql  = "SELECT DISTINCT mtdp.* FROM ms_test_default_problem mtdp".
			$where.";";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'ms_test_default_problem', $INSERT_NAME, $INSERT_VALUE);
	}
	$INSERT_NAME_DEL = $INSERT_NAME;
	$INSERT_VALUE_DEL = $INSERT_VALUE;

	//	ms_test_problem
	$sql  = "SELECT DISTINCT mtp.* FROM ms_test_problem mtp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'ms_test_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_test_problem	problem_table_type='2'
	$sql  = "SELECT DISTINCT butp.* FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'book_unit_test_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_test_problem	problem_table_type='1'
	$sql  = "SELECT DISTINCT butp.* FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'book_unit_test_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "SELECT DISTINCT usp.* FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'upnavi_section_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "SELECT DISTINCT usp.* FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'upnavi_section_problem', $INSERT_NAME, $INSERT_VALUE);
	}


	//	検証バッチDBデーター削除クエリー
	//	ms_test_problem
	$sql  = "DELETE mtp FROM ms_test_problem mtp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	$DELETE_SQL[] = $sql;

	//	book_unit_test_problem	problem_table_type='2'
	$sql  = "DELETE butp FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	book_unit_test_problem	problem_table_type='1'
	$sql  = "DELETE butp FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	ms_test_default_problem
	$sql  = "DELETE mtdp FROM ms_test_default_problem mtdp".
			$where.";";
	$DELETE_SQL[] = $sql;


	//	トランザクション開始
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

	//	ms_test_default_problem DBデーター追加
	if ($INSERT_NAME_DEL && $INSERT_VALUE_DEL) {
		foreach ($INSERT_NAME_DEL AS $table_name => $insert_name) {
			if ($INSERT_VALUE_DEL[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE_DEL[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_default_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_lms_unit;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE upnavi_section_problem;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(学力診断テスト)削除
function test_problem_trial_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	データーベース更新
	$DELETE_SQL = array();

	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$and .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($book_id > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$and .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$cn_where .= " AND mtp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$and .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}

	//	検証バッチDBデーター削除クエリー
	//	ms_test_problem
	$sql  = "DELETE mtp FROM ms_test_problem mtp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=mtp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$cn_where.";";
	$DELETE_SQL[] = $sql;

	//	book_unit_test_problem	problem_table_type='2'
	$sql  = "DELETE butp FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	book_unit_test_problem	problem_table_type='1'
	$sql  = "DELETE butp FROM book_unit_test_problem butp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=butp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			$dtn_where.
			" AND butp.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	book_unit_lms_unit	problem_table_type='2'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=bulu.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	upnavi_section_problem	problem_table_type='2'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='2'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='2';";
	$DELETE_SQL[] = $sql;

	//	upnavi_section_problem	problem_table_type='1'
	$sql  = "DELETE usp FROM upnavi_section_problem usp".
			" LEFT JOIN ms_test_default_problem mtdp ON mtdp.problem_num=usp.problem_num".
				$and.
				" AND mtdp.problem_table_type='1'".
			" WHERE mtdp.problem_num>'0'".
			" AND usp.problem_table_type='1';";
	$DELETE_SQL[] = $sql;

	//	ms_test_default_problem
	$sql  = "DELETE mtdp FROM ms_test_default_problem mtdp".
			$where.";";
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



//	問題設定(学力診断テスト)情報DBアップロードログアップ
function test_problem_trial_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	DB接続
	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	// update start 2016/04/12 プラクティスアップデートエラー対応
	//$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
	//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
	//		" AND state='1'";
	//		" AND course_num='".$cd->real_escape($course_num)."'";
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($gknn != "" && $gknn != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
	} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
	}
	if ($default_test_num != "" && $default_test_num != "0") {
		$sql .= " AND lesson_num='".$cd->real_escape($default_test_num)."'";
	} else {
		$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		// update start 2016/04/12 プラクティスアップデートエラー対応
		//$sql  = "UPDATE test_mate_upd_log SET".
		//		" state='3'".
		//		" WHERE update_mode='".$cd->real_escape($update_mode)."'".
		//		" AND state='1'";
		//		" AND course_num='".$cd->real_escape($course_num)."'";
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		// update start 2016/04/12 プラクティスアップデートエラー対応
		if ($gknn != "" && $gknn != "0") {
			$sql .= " AND stage_num='".$cd->real_escape($gknn)."'";
		} else {
			$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
		}
		if ($default_test_num != "" && $default_test_num != "0") {
			$sql .= " AND lesson_num='".$cd->real_escape($default_test_num)."'";
		} else {
			$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($gknn)."',".
				" '".$cd->real_escape($default_test_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(学力診断テスト)webアップロード
function test_problem_trial_web($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$update_type = $argv[1];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	$fileup = $argv['6'];

	if ($fileup != 1) {
		return;
	}

	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$and .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($course_num > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$and .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$cn_where .= " AND mtp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$and .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}
	$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
			$where.
			" AND mtdp.problem_table_type='2'".
			" AND mtdp.mk_flg='0'".
			" ORDER BY mtdp.problem_num;";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	//	音声ファイル
	$LOCAL_FILES = array();
	//	$dir = KBAT_DIR.MATERIAL_TEST_VOICE_DIR;
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	return $ERROR;
}



//	問題設定(学力診断テスト)web削除
function test_problem_trial_web_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$update_type = $argv[1];
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	$fileup = $argv['6'];

	if ($fileup != 1) {
		return;
	}

	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";
	if ($default_test_num != "0" && $default_test_num != "") {
		$where .= " WHERE mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$and .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
		$dtn_where .= " AND mtdp.default_test_num ='".$bat_cd->real_escape($default_test_num)."'";
	} else {
		$where .= " WHERE mtdp.default_test_num>'0'";
		$and .= " AND mtdp.default_test_num>'0'";
		$dtn_where .= " AND mtdp.default_test_num>'0'";
	}
	if ($course_num > 0) {
		$where .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$and .= " AND mtdp.course_num='".$bat_cd->real_escape($course_num)."'";
		$cn_where .= " AND mtp.course_num='".$bat_cd->real_escape($course_num)."'";
	}
	if ($gknn != "0" && $gknn != "") {
		$and .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
		$where .= " AND mtdp.gknn='".$bat_cd->real_escape($gknn)."'";
	}
	$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
			$where.
			" AND mtdp.problem_table_type='2'".
			" AND mtdp.mk_flg='0'".
			" ORDER BY mtdp.problem_num;";
	if ($result = $bat_cd->exec_query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);

	//	音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	$del_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
	test_remote_del_file($update_server_name, $del_dir, $LOCAL_FILES, $ERROR);

	return;
}



//---------------------------------------------------------------------------------------------
//	コードマスター情報DBアップロード
function test_ms_core_code_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_core_code;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_core_code', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_core_code;";
	$DELETE_SQL[] = $sql;


	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->db_close();
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_core_code;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	コードマスター情報DB削除
function test_ms_core_code_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM ms_core_code;";
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



//	コードマスター情報DBアップロードログアップ
function test_ms_core_code_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//---------------------------------------------------------------------------------------------
//	学力Upナビ単元情報DBアップロード
function test_upnavi_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	親単元
	$sql  = "SELECT * FROM upnavi_chapter".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'upnavi_chapter', $INSERT_NAME, $INSERT_VALUE);
	}

	//	子単元
	$sql  = "SELECT * FROM upnavi_section".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'upnavi_section', $INSERT_NAME, $INSERT_VALUE);
	}


	//	検証バッチDBデーター削除クエリー
	//	親単元
	$sql  = "DELETE FROM upnavi_chapter".
			" WHERE course_num='".$cd->real_escape($course_num)."';";
	$DELETE_SQL[] = $sql;
	//	子単元
	$sql  = "DELETE FROM upnavi_section".
			" WHERE course_num='".$cd->real_escape($course_num)."'";
	$sql .= ";";
	$DELETE_SQL[] = $sql;


	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE upnavi_chapter;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE upnavi_section;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	学力Upナビ単元情報DB削除
function test_upnavi_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$course_num = $argv['3'];
	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	//	親単元
	$sql  = "DELETE FROM upnavi_chapter".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."';";
	$DELETE_SQL[] = $sql;
	//	子単元
	$sql  = "DELETE FROM upnavi_section".
			" WHERE course_num='".$bat_cd->real_escape($course_num)."'";
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



//	学力Upナビ単元情報DBアップロードログアップ
function test_upnavi_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv[2];
	$course_num = $argv['3'];
	$send_data = $argv[8];
	$upd_tts_id = $argv[9];

	//	本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($course_num)."'";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($course_num)."'";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($course_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}

//---------------------------------------------------------------------------------------------
// add end yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
//	数学検定グループ情報DBアップロード
function test_math_test_group_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	$test_group_id = $argv['4'];

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($class_id != "" && $class_id != "0") {
		$WHERE[] = " mbg.class_id='".$bat_cd->real_escape($class_id)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'STEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id > '' "; // add 2020/09/11 cuong テスト標準化開発

	//$WHERE[] = " mbg.mk_flg='0'";
	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	ms_test_group
	$sql  = "SELECT mbg.* FROM ms_test_group mbg".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_group', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_group;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	数学検定グループ情報統合検証DBアップロード
function test_math_test_group_es_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	$test_group_id = $argv['4'];

	//	統合検証ＤＢ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($class_id != "" && $class_id != "0") {
		$WHERE[] = " mbg.class_id='".$bat_cd->real_escape($class_id)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'STEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id > '' "; // add 2020/09/11 cuong テスト標準化開発

	//$WHERE[] = " mbg.mk_flg='0'";
	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}

	//	ms_test_group
	$sql  = "SELECT mbg.* FROM ms_test_group mbg".
			$where.";";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'ms_test_group', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ms_test_group;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	数学検定テストグループ情報DB削除
function test_math_test_group_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	$test_group_id = $argv['4'];

	//	削除クエリー
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($class_id != "" && $class_id != "0") {
		$WHERE[] = " mbg.class_id='".$bat_cd->real_escape($class_id)."'";
	}
	if ($test_group_id > 0) {
		$WHERE[] = " mbg.test_group_id='".$bat_cd->real_escape($test_group_id)."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'STEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定 // del 2020/09/11 cuong テスト標準化開発
	$WHERE[] = " mbg.class_id > '' "; // add 2020/09/11 cuong テスト標準化開発
	//$WHERE[] = " mbg.mk_flg='0'";

	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	ms_test_group
	$sql  = "DELETE mbg FROM ms_test_group mbg".
			$where.";";
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



//	数学検定グループ情報DBアップロードログアップ
function test_math_test_group_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$class_id = $argv['3'];
	$test_group_id = $argv['4'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];

	//	本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'";
	if ($class_id != "" && $class_id != "0") {
		$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
	}
	if ($test_group_id > 0) {
		$sql .= " AND stage_num='".$cd->real_escape($test_group_id)."'";
	} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'";
		if ($class_id != "" && $class_id != "0") {
			$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
		}
		if ($test_group_id > 0) {
			$sql .= " AND stage_num='".$cd->real_escape($test_group_id)."'";
		} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($class_id)."',".
				" '".$cd->real_escape($test_group_id)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();
	return $ERROR;
}

//数学検定出題単元情報DBアップロード
function test_math_test_control_unit_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($class_id != "" && $class_id != "0") {
		$WHERE[] = " mtcu.class_id='".$bat_cd->real_escape($class_id)."'";
	}

	//$WHERE[] = " mtcu.mk_flg='0'";
	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}

	//	math_test_control_unit
	$sql  = "SELECT mtcu.* FROM math_test_control_unit mtcu".
			$where.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'math_test_control_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	//	math_test_control_unit
	$sql  = "DELETE mtcu FROM math_test_control_unit mtcu".
			$where.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE math_test_control_unit;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}


//	数学検定出題単元情報DB削除
function test_math_test_control_unit_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	//$math_group_id = $argv['4'];

	//	削除クエリー
	$DELETE_SQL = array();
	$where = "";
	$WHERE = array();
	if ($class_id != "" && $class_id != "0") {
		$WHERE[] = " mtcu.class_id='".$bat_cd->real_escape($class_id)."'";
	}

	//$WHERE[] = " mtcu.mk_flg='0'";
	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	math_test_control_unit
	$sql  = "DELETE mtcu FROM math_test_control_unit mtcu".
			$where.";";
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



//	数学検定出題単元情報DBアップロードログアップ
function test_math_test_control_unit_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$class_id = $argv['3'];
	//$math_group_id = $argv['4'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];

	//	本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'";
	if ($class_id != "" && $class_id != "0") {
		$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
	}

	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'";
		if ($class_id != "" && $class_id != "0") {
			$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
		}

		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($class_id)."',".
				" 'NULL',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();
	return $ERROR;
}

//	数学検定採点単元情報DBアップロード
function test_math_test_unit_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	//if ($class_id === "" ) {
	//	$ERROR[] = "Not class_id";
	//	return $ERROR;
	//}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$where = "";
	$and = "";
	$class_id = $bat_cd->real_escape($class_id);
	if ($class_id != "0" && $class_id != "") {
		$where .= " WHERE 1";
		$where .= " AND mtbul.class_id='".$class_id."'";
	} else {
		$where .= " WHERE 1";
	}
	//$and .= " AND mbu.display='1'";
	//$and .= " AND mbu.mk_flg='0'";

	/* math_test_book_unit_list */
	$sql  = "SELECT mtbul.* FROM math_test_book_unit_list mtbul".
			 " INNER JOIN ms_book_unit mbu ON mtbul.book_unit_id = mbu.book_unit_id ".
			 $where.
			 $and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'math_test_book_unit_list', $INSERT_NAME, $INSERT_VALUE);
	}
	/* ms_book_unit */
	$sql  = "SELECT mbu.* FROM ms_book_unit mbu".
			 " INNER JOIN math_test_book_unit_list mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
			 $where.
			 $and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_book_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	$sql  = "DELETE mbu FROM ms_book_unit mbu".
			 " INNER JOIN math_test_book_unit_list mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
			 $where.
			 $and.";";
	 $DELETE_SQL[] = $sql;
	 $sql  = "DELETE mtbul FROM math_test_book_unit_list mtbul".
 			 $where;
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE math_test_book_unit_list;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_book_unit;";
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



//	数学検定採点単元情報DB削除
function test_math_test_unit_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	更新情報クエリー
	$class_id = $argv['3'];
	$where = "";
	$and = "";
	//if( $class_id === "" ){
	//	$ERROR[] = "Not class_id";
	//	return ;
	//}

	$class_id = $bat_cd->real_escape($class_id);
	if ($class_id != "0" && $class_id != "") {
		$where .= " WHERE 1";
		$where .= " AND mtbul.class_id='".$class_id."'";
	} else {
		$where .= " WHERE 1";
	}
	//$and .= " AND mbu.display='1'";
	//$and .= " AND mbu.mk_flg='0'";

	$sql  = "DELETE mbu FROM ms_book_unit mbu".
			 " INNER JOIN math_test_book_unit_list mtbul ON mbu.book_unit_id = mtbul.book_unit_id ".
			 $where.
			 $and.";";
	 $DELETE_SQL[] = $sql;
	 $sql  = "DELETE mtbul FROM math_test_book_unit_list mtbul".
 			 $where;
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



//	数学検定採点単元情報DBアップロードログアップ
function test_math_test_unit_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$class_id = $argv['3'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];
	//if ($class_id === "" ) {
	//	$ERROR[] = "Not class_id";
	//	return $ERROR;
	//}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	$and = "";
	if($class_id){
		$and = " AND course_num='".$cd->real_escape($class_id)."' ";
	}

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			$and.
			" AND state='1'";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}
	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				$and.
				" AND state='1'";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($class_id)."',".
				" 'NULL',".
				" 'NULL',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(数学検定)アップロード
function test_problem_math_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$class_id = $argv['3'];
	if ($class_id == "") {
		$ERROR[] = "Not class_id";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	クエリー
	$and = "";
	//$and .= " AND mtcp.mk_flg = '0' ";
	if ($class_id) {
		$and .= " AND mtcp.class_id='".$class_id."' ";
	}

	// 先にmath_test_control_problemをアップする。他のテーブルは、math_test_control_problemを基準にdelete/insertを行っている為。
	//インサートクエリ作成
	//	math_test_control_problem
	$sql = "SELECT DISTINCT mtcp.* FROM math_test_control_problem mtcp ".
			" WHERE 1 ".
			$and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, math_test_control_problem, $INSERT_NAME, $INSERT_VALUE);
	}
	//	math_test_control_problem
	$sql = "DELETE mtcp FROM math_test_control_problem mtcp ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	// DBデータ削除
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

	//	検証バッチDBデーター追加
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

	//	book_unit_test_problem
	$sql = "SELECT DISTINCT butp.* FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN book_unit_test_problem butp ON mtp.problem_num = butp.problem_num ".
			" AND butp.default_test_num='0' ".
			" WHERE 1 ".
			$and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, book_unit_test_problem, $INSERT_NAME, $INSERT_VALUE);
	}
	//	book_unit_lms_unit
	$sql = "SELECT DISTINCT bulu.* FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN book_unit_lms_unit bulu ON mtp.problem_num = bulu.problem_num ".
			" WHERE 1 ".
			$and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, book_unit_lms_unit, $INSERT_NAME, $INSERT_VALUE);
	}
	//	ms_test_problem
	$sql = "SELECT DISTINCT mtp.* FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" WHERE 1 ".
			$and.";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, ms_test_problem, $INSERT_NAME, $INSERT_VALUE);
	}

	$DELETE_SQL = array();
	// 削除クエリ作成
	// book_unit_test_problem
	$sql = "DELETE butp FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN book_unit_test_problem butp ON mtp.problem_num = butp.problem_num ".
			" AND butp.default_test_num='0' ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;
	//	book_unit_lms_unit
	$sql = "DELETE bulu FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" INNER JOIN book_unit_lms_unit bulu ON mtp.problem_num = bulu.problem_num ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;
	//	ms_test_problem
	$sql = "DELETE mtp FROM math_test_control_problem mtcp ".
			" INNER JOIN ms_test_problem mtp ON mtcp.problem_num = mtp.problem_num ".
			" WHERE 1 ".
			$and.";";
	$DELETE_SQL[] = $sql;

	// DBデータ削除
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_lms_unit;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_problem;";
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE math_test_control_problem;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(数学検定)情報DBアップロードログアップ
function test_problem_math_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$class_id = $argv['3'];

	if ($class_id == "") {
		$ERROR[] = "Not class_id";
		return $ERROR;
	}

	//	DB接続
	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'";
	if ($class_id != "" && $class_id != "0") {
		$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
	} else {
		$sql .= " AND (course_num IS NULL OR course_num='' OR course_num='0')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'";
		if ($class_id != "" && $class_id != "0") {
			$sql .= " AND course_num='".$cd->real_escape($class_id)."'";
		} else {
			$sql .= " AND (course_num IS NULL OR course_num='' OR course_num='0')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($class_id)."',".
				"NULL ,".
				"NULL ,".
				"NULL ,".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



//	問題設定(数学検定)webアップロード
function test_problem_math_web($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$update_type = $argv['1'];
	$class_id= $argv['3'];
	$fileup = $argv['4'];
	if ($fileup != 1) {
		return;
	}
	if ($class_id == "") {
		$ERROR[] = "Not lass_id";
		return $ERROR;
	}


	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$and = "";
	if ($class_id) {
		$and .= " AND mtcp.class_id='".$class_id."' ";
	}
	$sql  = " SELECT DISTINCT mtp.problem_num FROM book_unit_test_problem butp ".
			 " INNER JOIN ms_test_problem mtp ON butp.problem_num = mtp.problem_num ".
			 " AND butp.default_test_num='0'".
			 " AND butp.problem_table_type='2'".
			 " AND butp.mk_flg='0'".
			 " INNER JOIN math_test_control_problem mtcp ON mtp.problem_num = mtcp.problem_num ".
			 " AND mtcp.problem_table_type='2'".
			 " AND mtcp.mk_flg='0'".
			 " WHERE 1 ".
			 $and.
			 " ORDER BY mtp.problem_num;";

	if ($result = $bat_cd->query($sql)) {
			while ($list=$bat_cd->fetch_assoc($result)) {
				$problem_num = $list['problem_num'];
				$PROBLEM_LIST[] = $problem_num;
			}
	}
	// KBAT_DIR:/data/apprelease/Release/Contents
	// HBAT_DIR:/data/home
	// BASE_DIR:/data/home
	// REMOTE_MATERIAL_TEST_IMG_DIR:/www/material/test_img/
	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	// REMOTE_MATERIAL_TEST_VOICE_DIR:/www/material/test_voice/
	//	音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	return $ERROR;
}
// add end yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定

//---------------------------------------------------------------------------------------------

//	エラー記録処理
function write_error($ERROR) {

	foreach ($ERROR AS $val) {
		$error .= date("Ymdhis")."\t".$val."\n";
	}

	// $file = "./test_error.log";										// del 2018/11/27 yoshizawa すらら英単語 ログファイルを作成
	$file = INCLUDE_BASE_DIR."/_www/batch/error_TESTCONTENTSUP.log";	// add 2018/11/27 yoshizawa すらら英単語
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

// //	INSERTクエリー作成
// function make_insert_data($sql, $dbname, $db, &$insert_name, &$INSERT_VALUE) {
// 	$insert_name = "";
// 	$INSERT_VALUE = array();
// 	$flag = 0;
// 	$i = 1;
// 	$num = 1;
// 	$insert_value = "";
// 	if ($result = mysql_db_query($dbname, $sql, $db)) {
// 		while ($list=mysql_fetch_array($result,MYSQL_ASSOC)) {
// 			if ($insert_value) { $insert_value .= ", "; }
// 			unset($value);
// 			foreach ($list AS $key => $val) {
// 				if ($flag != 1) {
// 					if ($insert_name) { $insert_name .= ", "; }
// 					$insert_name .= $key;
// 				}
// 				if ($value) { $value .= ", "; }
// 				$value .= "'".addslashes($val)."'";
// 			}
// 			if ($value) {
// 				$insert_value .= "(".$value.")";
// 				$i++;
// 			}
// 			if ($insert_name) { $flag = 1; }
// 			if ($i == 50) {
// 				$INSERT_VALUE[$num] = $insert_value;
// 				$num++;
// 				$i = 1;
// 				$insert_value = "";
// 			}
// 		}
// 		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }
// 	}
// }


//	インサート処理
function insert_data($db, $table_name, $insert_name, $INSERT_VALUE) {

	foreach ($INSERT_VALUE AS $values) {
		$sql  = "INSERT INTO ".$table_name.
				" (".$insert_name.") ".
				" VALUES".$values.";";
		if (!$db->exec_query($sql)) {
			$ERROR[] = "SQL INSERT ERROR<br>$sql";
			$sql  = "ROLLBACK";
			if (!$db->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$db->close();
			return $ERROR;
		}
	}

}


//	インサートクエリー作成
function make_insert_query($result, $table_name, &$INSERT_NAME, &$INSERT_VALUE) {

	// 検証バッチサーバ接続情報取得
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
//echo '■ディレクトリ：'.$dir_name;
//echo '■ファイル名：'.$set_file_name;
}


// -- リモートサーバーフォルダー作成
function test_remote_set_dir($update_server_name, $dir, $FILE_NAME, &$ERROR) {

	//	作成
	if ($FILE_NAME) {
		foreach ($FILE_NAME['df'] AS $key => $val) {
			if ($key == "") {
				countinue;
			}
			$make_dir = $dir.$key;
			$command = "ssh suralacore01@".$update_server_name." mkdir -p ".$make_dir;
// echo $command."<br>\n";
			exec("$command", $LIST);
		}
	}

	return;
}


// -- リモートサーバーファイルアップロード
function test_remote_set_file($update_server_name, $local_dir, $remote_dir, $FILE_NAME, &$ERROR) {

	//	ファイルアップロード
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


// -- リモートサーバーファイルアップロード
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
					// $com_line .= "ssh suralacore01@".$update_server_name." rm -f '".$del_file."'\n";

					// ローカルのリリースコンテンツを削除
					$com_line .= "rm -f '".$del_file."'\n";

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


//	反映終了処理記録
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


// add start oda 2015/11/09 アップデートログリスト 更新処理追加

//
//	問題アップ（共通） 本番バッチUP時のアップデートリストログ更新
//		定期テスト／学力診断テスト／判定テスト／数学検定／すらら英単語テスト
//
function test_mate_upd_log_end($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	if ($mode == "test_problem_math") {				// 数学検定の問題アップ時は、パラメータの位置が変わる(空のパラメータが詰まる)
		// $myid = $argv['5'];					// del 2018/04/02 yoshizawa
		// $test_mate_upd_log_num = $argv['6'];	// del 2018/04/02 yoshizawa
		$myid = $argv['6'];						// add 2018/04/02 yoshizawa
		$test_mate_upd_log_num = $argv['7'];	// add 2018/04/02 yoshizawa

	} else {
		$myid = $argv['8'];
		$test_mate_upd_log_num = $argv['9'];
	}

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
//	問題アップ（共通） 検証バッチ削除時のアップデートリストログ更新
//		定期テスト／学力診断テスト／判定テスト／数学検定
//
function test_mate_upd_log_delete($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	if ($mode == "test_problem_math") {				// 数学検定の問題アップ時は、パラメータの位置が変わる(空のパラメータが詰まる)
		$myid = $argv['4'];
		$test_mate_upd_log_num = $argv['5'];
	} else {
		$myid = $argv['8'];
		$test_mate_upd_log_num = $argv['9'];
	}

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

//
//	定期テスト 問題アップ時のアップデートリストログ更新
//
function test_problem_test_mate_upd_log_update($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$core_code = $argv['5'];
	$fileup = $argv['6'];
	$myid = $argv['8'];

	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$course_num = $cd->real_escape($course_num);
	$gknn = $cd->real_escape($gknn);
	$core_code = $cd->real_escape($core_code);
	$fileup = $cd->real_escape($fileup);
	$myid = $cd->real_escape($myid);

	// send_data_log生成
	$SEND_DATA_LOG = array(
		'course_num' => $course_num,
		'gknn' => $gknn,
		'core_code' => $core_code,
		'fileup' => $fileup
	);
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);

	// モード取得
	$mode = str_replace("test_", "", $mode);

	//	ログ存在チェック
	$test_mate_upd_log_num = 0;

	$sql  = "SELECT test_mate_upd_log_num FROM test_mate_upd_log ".
			" WHERE update_mode='".$mode."'".
			" AND state='1'".
			" AND course_num='".$course_num."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND stage_num='".$gknn."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($core_code != "0" && $core_code != "") {
		$sql .= " AND lesson_num='".$core_code."'";
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
				 " AND course_num='".$course_num."'";
		if ($gknn != "0" && $gknn != "") {
			$where .= " AND stage_num='".$gknn."'";
		}
		if ($core_code != "0" && $core_code != "") {
			$where .= " AND lesson_num='".$core_code."'";
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
		$INSERT_DATA['course_num'] = $course_num;
		if ($gknn != "0" && $gknn != "") {
			$INSERT_DATA['stage_num'] = $gknn;
		}
		if ($core_code != "0" && $core_code != "") {
			$INSERT_DATA['lesson_num'] = $core_code;
		}
		if ($fileup == "1") {
			$INSERT_DATA['unit_num'] = $fileup;
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $myid;

		$ERROR = $cd->insert("test_mate_upd_log", $INSERT_DATA);
	}

	return $ERROR;

}

//
//	学力診断テスト 問題アップ時のアップデートリストログ更新
//
function test_problem_trial_test_mate_upd_log_update($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得

	$course_num = $argv['3'];
	$gknn = $argv['4'];
	$default_test_num = $argv['5'];
	$fileup = $argv['6'];
	$myid = $argv['8'];
	$test_group_id = $argv['10'];

	if ($course_num < 1) {
		$ERROR[] = "Not course_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$course_num = $cd->real_escape($course_num);
	$gknn = $cd->real_escape($gknn);
	$default_test_num = $cd->real_escape($default_test_num);
	$fileup = $cd->real_escape($fileup);
	$test_group_id = $cd->real_escape($test_group_id);
	$myid = $cd->real_escape($myid);

	// send_data_log生成
	$SEND_DATA_LOG = array(
		'course_num' => $course_num,
		'gknn' => $gknn,
		'tgi_default_test_num' => $test_group_id ."_".$default_test_num,
		'fileup' => $fileup
	);
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);

	// モード取得
	$mode = str_replace("test_", "", $mode);

	//	ログ存在チェック
	$test_mate_upd_log_num = 0;

	$sql  = "SELECT test_mate_upd_log_num FROM test_mate_upd_log ".
			" WHERE update_mode='".$mode."'".
			" AND state='1'".
			" AND course_num='".$course_num."'";
	if ($gknn != "0" && $gknn != "") {
		$sql .= " AND stage_num='".$gknn."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($default_test_num != "0" && $default_test_num != "") {
		$sql .= " AND lesson_num='".$default_test_num."'";
	} else {
		$sql .= " AND lesson_num IS NULL";
	}
	$sql .=	" AND send_data='".$send_data_log."'".
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
				 " AND course_num='".$course_num."'";
		if ($gknn != "0" && $gknn != "") {
			$where .= " AND stage_num='".$gknn."'";
		}
		if ($default_test_num != "0" && $default_test_num != "") {
			$where .= " AND lesson_num='".$default_test_num."'";
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
		$INSERT_DATA['course_num'] = $course_num;
		if ($gknn != "0" && $gknn != "") {
			$INSERT_DATA['stage_num'] = $gknn;
		}
		if ($default_test_num != "0" && $default_test_num != "") {
			$INSERT_DATA['lesson_num'] = $default_test_num;
		}
		if ($fileup == "1") {
			$INSERT_DATA['unit_num'] = $fileup;
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $myid;

		$ERROR = $cd->insert("test_mate_upd_log", $INSERT_DATA);
	}

	return $ERROR;

}


//
//	数学検定 問題アップ時のアップデートリストログ更新
//
function test_problem_math_test_mate_upd_log_update($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得

	$class_id = $argv['3'];
	$fileup = $argv['4'];
	$myid = $argv['6'];

	if ($class_id == "") {
		$ERROR[] = "Not class_id";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$class_id = $cd->real_escape($class_id);
	$fileup = $cd->real_escape($fileup);
	$myid = $cd->real_escape($myid);

	// send_data_log生成
	$SEND_DATA_LOG = array(
		'class_id' => $class_id,
		'fileup' => $fileup
	);
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);

	// モード取得
	$mode = str_replace("test_", "", $mode);

	//	ログ存在チェック
	$test_mate_upd_log_num = 0;

	$sql  = "SELECT test_mate_upd_log_num FROM test_mate_upd_log ".
			" WHERE update_mode='".$mode."'".
			" AND state='1'".
			" AND course_num='".$class_id."'";

	$sql .=	" AND send_data='".$send_data_log."'".
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
				 " AND course_num='".$class_id."'";

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
		$INSERT_DATA['course_num'] = $class_id;
		if ($fileup == "1") {
			$INSERT_DATA['stage_num'] = $fileup;
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

// add start 2018/11/26 yoshizawa すらら英単語
//	すらら英単語テスト種類情報DBアップロード
function test_vocabulary_test_type_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_test_type;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_type', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_type;";
	$DELETE_SQL[] = $sql;


	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_type;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	すらら英単語テスト種類情報DB削除
function test_vocabulary_test_type_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM ms_test_type;";
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



//	すらら英単語テスト種類情報DBアップロードログアップ
function test_vocabulary_test_type_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];

	//	DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}

//	すらら英単語テスト種別1マスタ情報DBアップロード
function test_vocabulary_test_category1_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$test_type_num = $argv['3'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_test_category1".
			" WHERE test_type_num='".$bat_cd->real_escape($test_type_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_category1', $INSERT_NAME, $INSERT_VALUE);
	}

	//	削除クエリー
	$sql  = "DELETE FROM ms_test_category1".
			" WHERE test_type_num='".$bat_cd->real_escape($test_type_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_category1;";
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

//	すらら英単語テスト種別1マスタ情報DB削除
function test_vocabulary_test_category1_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$test_type_num = $argv['3'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_category1".
			" WHERE test_type_num='".$bat_cd->real_escape($test_type_num)."';";
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

//	すらら英単語テスト種別1マスタ情報DBアップロードログアップ
function test_vocabulary_test_category1_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$test_type_num = $argv['3'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($test_type_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}


// ---

//	すらら英単語テスト種別1webアップロード
function test_vocabulary_test_category1_web($btw_server_name,$update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	ファイルアップ
	$update_type = $argv['1'];
	$write_type = $argv['4'];
	if ($write_type < 1) {
		$ERROR[] = "Not write_type";
		return $ERROR;
	}

	//	background.jpg	course.css
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	return $ERROR;
}

//	すらら英単語テスト種別1web削除
function test_vocabulary_test_category1_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$test_type_num = $argv['3'];
	$write_type = $argv['4'];
	if ($write_type < 1) {
		$ERROR[] = "Not write_type";
		return $ERROR;
	}

	//	background.jpg	course.css
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/".$write_type;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	return $ERROR;

}

//	すらら英単語テスト種別2情報DBアップロード
function test_vocabulary_test_category2_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
		$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM ms_test_category2".
			" WHERE test_type_num='".$bat_cd->real_escape($test_type_num)."'";
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$sql .= " AND test_category1_num='".$bat_cd->real_escape($test_category1_num)."'";
	}
	$sql .= ";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_category2', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_category2".
			" WHERE test_type_num='".$cd->real_escape($test_type_num)."'";
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$sql .= " AND test_category1_num='".$cd->real_escape($test_category1_num)."'";
	}
	$sql .= ";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_category2;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	すらら英単語テスト種別2情報DB削除
function test_vocabulary_test_category2_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_category2".
			" WHERE test_type_num='".$bat_cd->real_escape($test_type_num)."'";
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$sql .= " AND test_category1_num='".$bat_cd->real_escape($test_category1_num)."'";
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



//	すらら英単語テスト種別2情報DBアップロードログアップ
function test_vocabulary_test_category2_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."'";
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$sql .= " AND stage_num='".$cd->real_escape($test_category1_num)."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	$sql .=	";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($test_type_num)."'";
		if ($test_category1_num != "0" && $test_category1_num != "") {
			$sql .= " AND stage_num='".$cd->real_escape($test_category1_num)."'";
		} else {
			$sql .= " AND stage_num IS NULL";
		}
		$sql .=	";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`stage_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($test_type_num)."',".
				" '".$cd->real_escape($test_category1_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}

//	問題設定(すらら英単語テスト)アップロード
function test_problem_vocabulary_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	$test_category2_num = $argv['5'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$where = "";
	if ($test_type_num > 0) {
		$where .= " AND mtdp.test_type_num='".$bat_cd->real_escape($test_type_num)."'";
	}
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$where .= " AND mtdp.test_category1_num='".$bat_cd->real_escape($test_category1_num)."'";
	}
	if ($test_category2_num != "0" && $test_category2_num != "") {
		$where .= " AND mtdp.test_category2_num='".$bat_cd->real_escape($test_category2_num)."'";

	}

	//	登録データー取得->REPLACE作成用情報生成(PKやUNIQUEが有るテーブルが対象です）
	//	ms_test_category2_problem
	$sql  = "SELECT DISTINCT mtdp.* FROM ms_test_category2_problem mtdp ".
			" WHERE 1 ".
			$where.
			";";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_category2_problem', $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	ms_test_problem
	$sql  = "SELECT DISTINCT mtp.* FROM ms_test_problem mtp ".
			" INNER JOIN ms_test_category2_problem mtdp ON mtdp.problem_num=mtp.problem_num ".
			" AND mtdp.problem_table_type='3'".
			$where.
			" WHERE mtdp.problem_num>0;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_problem', $REPLACE_NAME, $REPLACE_VALUE);
	}

	//	登録データー取得->INSERT作成用情報生成(PKやUNIQUEが無いテーブルが対象です）
	//	book_unit_lms_unit	problem_table_type='3'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_category2_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.problem_table_type='3'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='3';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "SELECT DISTINCT bulu.* FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_category2_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'book_unit_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー(PKやUNIQUEが無いテーブルが対象です）-----------------------------------------
	//	book_unit_lms_unit	problem_table_type='3'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_category2_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.problem_table_type='3'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='3';";
	$DELETE_SQL['book_unit_lms_unit_2'] = $sql;

	//	book_unit_lms_unit	problem_table_type='1'
	$sql  = "DELETE bulu FROM book_unit_lms_unit bulu".
			" INNER JOIN ms_test_category2_problem mtdp ON mtdp.problem_num=bulu.problem_num".
			" AND mtdp.problem_table_type='1'".
			$where.
			" WHERE mtdp.problem_num>0".
			" AND bulu.problem_table_type='1';";
	$DELETE_SQL['book_unit_lms_unit_1'] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	削除処理
	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $table_name => $sql) {
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

	// REPLACE処理（対象テーブル：ms_test_category2_problem/ms_test_problem/book_unit_test_problem）
	if (count($REPLACE_NAME) && count($REPLACE_VALUE)) {
		foreach ($REPLACE_NAME AS $table_name => $replace_name) {
			if ($REPLACE_VALUE[$table_name]) {
				foreach ($REPLACE_VALUE[$table_name] AS $values) {
					$sql  = "REPLACE INTO ".$table_name.
							" (".$replace_name.") ".
							" VALUES ".$values.";";
					if (!$cd->exec_query($sql)) {
						$ERROR[] = "SQL REPLACE ERROR<br>$sql";

						$sql  = "ROLLBACK";
						if (!$cd->exec_query($sql)) {
							$ERROR[] = "SQL ROLLBACK ERROR";
						}
						$cd->close();
						return $ERROR;
					}
				}
			}
		}
	}

	// INSERT処理（対象テーブル：book_unit_lms_unit/upnavi_section_problem）
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				foreach ($INSERT_VALUE[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
							" (".$insert_name.") ".
							" VALUES ".$values.";";
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
		}
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}
	// update end oda 2014/10/17 課題要望一覧No352

	//	テーブル最適化
	$OPTIMIZE_SQL = array();
//	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_category2_problem;";
//	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE ms_test_problem;";
//	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE book_unit_lms_unit;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	問題設定(すらら英単語テスト)情報DBアップロードログアップ
function test_problem_vocabulary_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	$test_category2_num = $argv['5'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	検証分散DB1から8接続または本番バッチ
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."'";
	// update end 2016/04/12 プラクティスアップデートエラー対応
	if ($test_category1_num != "" && $test_category1_num != "0") {
		$sql .= " AND stage_num='".$cd->real_escape($test_category1_num)."'";
	} else {
		$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
	}
	if ($test_category2_num != "" && $test_category2_num != "0") {
		$sql .= " AND lesson_num='".$cd->real_escape($test_category2_num)."'";
	} else {
		$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
	}
	$sql .= ";";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
				" AND state='1'".
				" AND course_num='".$cd->real_escape($test_type_num)."'";
		if ($test_category1_num != "" && $test_category1_num != "0") {
			$sql .= " AND stage_num='".$cd->real_escape($test_category1_num)."'";
		} else {
			$sql .= " AND (stage_num IS NULL OR stage_num='' OR stage_num='0')";
		}
		if ($test_category2_num != "" && $test_category2_num != "0") {
			$sql .= " AND lesson_num='".$cd->real_escape($test_category2_num)."'";
		} else {
			$sql .= " AND (lesson_num IS NULL OR lesson_num='' OR lesson_num='0')";
		}
		$sql .= ";";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
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
				" '".$cd->real_escape($test_type_num)."',".
				" '".$cd->real_escape($test_category1_num)."',".
				" '".$cd->real_escape($test_category2_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}

//	問題設定(すらら英単語テスト)webアップロード
function test_problem_vocabulary_web($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	$update_type = $argv['1']; // 1:本番バッチアップ 2:検証バッチアップ 3:検証バッチ削除
	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	$test_category2_num = $argv['5'];
	$fileup = $argv['6'];

	if ($fileup != 1) {
		return;
	}

	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$where = "";
	if ($test_type_num > 0) {
		$where .= " AND mtcp.test_type_num='".$bat_cd->real_escape($test_type_num)."'";
	}
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$where .= " AND mtcp.test_category1_num='".$bat_cd->real_escape($test_category1_num)."'";
	}
	if ($test_category2_num != "0" && $test_category2_num != "") {
		$where .= " AND mtcp.test_category2_num='".$bat_cd->real_escape($test_category2_num)."'";
	}
	$sql  = "SELECT DISTINCT mtcp.problem_num FROM ms_test_category2_problem mtcp".
			" WHERE 1 ".
			$where.
			" AND mtcp.problem_table_type='3'".
			" AND mtcp.mk_flg='0'".
			" ORDER BY mtcp.problem_num;";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	// HBAT_DIR /data/apprelease/Release/Contents
	// BASE_DIR /data/home
	// REMOTE_MATERIAL_TEST_IMG_DIR /www/material/test_img
	// REMOTE_MATERIAL_TEST_VOICE_DIR /www/material/test_voice

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_IMG_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	//	音声ファイル
	$LOCAL_FILES = array();
	$dir = KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

	//	フォルダー作成
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_dir($update_server_name, $remote_dir, $LOCAL_FILES, $ERROR);

	//	ファイルアップ
	$local_dir = KBAT_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEST_VOICE_DIR);
	if ($update_type == 1) {
		$remote_dir = HBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	} else {
		$remote_dir = BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR;
	}
	test_remote_set_file($update_server_name, $local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

	return $ERROR;
}

//
//	すらら英単語テスト 問題アップ時のアップデートリストログ更新
//
function test_problem_vocabulary_test_mate_upd_log_update($update_server_name, $mode, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// パラメータ取得
	$test_type_num = $argv['3'];
	$test_category1_num = $argv['4'];
	$test_category2_num = $argv['5'];
	$fileup = $argv['6'];
	$myid = $argv['8'];

	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	// すらら様開発環境に接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// パラメータにSQLエスケープ処理実行
	$test_type_num = $cd->real_escape($test_type_num);
	$test_category1_num = $cd->real_escape($test_category1_num);
	$test_category2_num = $cd->real_escape($test_category2_num);
	$fileup = $cd->real_escape($fileup);
	$myid = $cd->real_escape($myid);

	// send_data_log生成
	$SEND_DATA_LOG = array(
		'test_type_num' => $test_type_num,
		'test_category1_num' => $test_category1_num,
		'test_category2_num' => $test_category2_num,
		'fileup' => $fileup
	);
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);

	// モード取得
	$mode = str_replace("test_", "", $mode);

	//	ログ存在チェック
	$test_mate_upd_log_num = 0;

	$sql  = "SELECT test_mate_upd_log_num FROM test_mate_upd_log ".
			" WHERE update_mode='".$mode."'".
			" AND state='1'".
			" AND course_num='".$test_type_num."'";
	if ($test_category1_num != "0" && $test_category1_num != "") {
		$sql .= " AND stage_num='".$test_category1_num."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	if ($test_category2_num != "0" && $test_category2_num != "") {
		$sql .= " AND lesson_num='".$test_category2_num."'";
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
				 " AND course_num='".$test_type_num."'";
		if ($test_category1_num != "0" && $test_category1_num != "") {
			$where .= " AND stage_num='".$test_category1_num."'";
		}
		if ($test_category2_num != "0" && $test_category2_num != "") {
			$where .= " AND lesson_num='".$test_category2_num."'";
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
		$INSERT_DATA['course_num'] = $test_type_num;
		if ($test_category1_num != "0" && $test_category1_num != "") {
			$INSERT_DATA['stage_num'] = $test_category1_num;
		}
		if ($test_category2_num != "0" && $test_category2_num != "") {
			$INSERT_DATA['lesson_num'] = $test_category2_num;
		}
		if ($fileup == "1") {
			$INSERT_DATA['unit_num'] = $fileup;
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $myid;

		$ERROR = $cd->insert("test_mate_upd_log", $INSERT_DATA);
	}

	return $ERROR;

}
// add end 2018/11/26 yoshizawa すらら英単語
// add start 2019/05/21 yoshizawa すらら英単語テストワード管理
//	ワード情報DBアップロード
function course_english_word_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_test_type_num = $argv[3];

	if ($update_test_type_num < 1) {
		$ERROR[] = "更新するテスト種類番号が取得できません。";
		return $ERROR;
	}

	// 検証分散DB１～８ or 本番バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	// english_word
	$sql    = "SELECT ew.* FROM english_word ew ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'english_word', $INSERT_NAME, $INSERT_VALUE);
	}
	// english_word_problem
	$sql    = "SELECT ewp.* FROM english_word ew ".
			" INNER JOIN english_word_problem ewp ON ew.english_word_num = ewp.english_word_num ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'english_word_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	// DBデーター削除クエリー
	// ※english_wordで関連付けてenglish_word_problemを削除するため、
	// english_word_problem→english_wordの順で削除しないといけません。
	// english_word_problem
	$sql    = "DELETE ewp FROM english_word ew ".
			" INNER JOIN english_word_problem ewp ON ew.english_word_num = ewp.english_word_num ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
	$DELETE_SQL[] = $sql;
	// english_word
	$sql    = "DELETE ew FROM english_word ew ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
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
	// english_word
	$sql = "OPTIMIZE TABLE english_word;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	// english_word_problem
	$sql = "OPTIMIZE TABLE english_word_problem;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// アップ先DB切断
	$cd->close();

	return $ERROR;
}

//	ワード情報DB削除
function course_english_word_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_test_type_num = $argv[3];

	if ($update_test_type_num < 1) {
		$ERROR[] = "削除するテスト種類番号が取得できません。";
		return $ERROR;
	}

	// 検証バッチDBデーター削除
	// ※english_wordで関連付けてenglish_word_problemを削除するため、
	// english_word_problem→english_wordの順で削除しないといけません。
	// english_word_problem
	$sql  = "DELETE ewp FROM english_word ew ".
			" INNER JOIN english_word_problem ewp ON ew.english_word_num = ewp.english_word_num ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}
	// english_word
	$sql    = "DELETE ew FROM english_word ew ".
			"WHERE ew.test_type_num='".$bat_cd->real_escape($update_test_type_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}


//// add start okabe 2020/09/05

// 標準化カテゴリアップロード
function standard_category_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	// 検証分散DB１～８ or 本番バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	// ms_test_standard_category
	$sql    = "SELECT * FROM ms_test_standard_category;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_test_standard_category', $INSERT_NAME, $INSERT_VALUE);
	}
	// test_standard_category_relation
	$sql    = "SELECT * FROM test_standard_category_relation;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'test_standard_category_relation', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_test_standard_category;";
	$DELETE_SQL[] = $sql;
	$sql  = "DELETE FROM test_standard_category_relation;";
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
	// english_word
	$sql = "OPTIMIZE TABLE ms_test_standard_category;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	// english_word_problem
	$sql = "OPTIMIZE TABLE test_standard_category_relation;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	// アップ先DB切断
	$cd->close();

	return $ERROR;
}

// 標準化カテゴリ 削除
function standard_category_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM ms_test_standard_category;";
	$DELETE_SQL[] = $sql;
	$sql = "DELETE FROM test_standard_category_relation;";
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

//	標準化カテゴリ アップロードログアップ
function standard_category_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];

	//	DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='standard_category'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='standard_category'".
				" AND state='1';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" 'standard_category',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}



// 数学検定情報

//	数学検定情報DBアップロード
function math_test_book_info_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	$sql  = "SELECT * FROM math_test_book_info;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'math_test_book_info', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM math_test_book_info;";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
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

	//	検証バッチDBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
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
	$OPTIMIZE_SQL[] = "OPTIMIZE TABLE math_test_book_info;";
	if ($OPTIMIZE_SQL) {
		foreach ($OPTIMIZE_SQL AS $sql) {
			if (!$cd->exec_query($sql)) {
				$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
			}
		}
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}



//	数学検定情報DB削除
function math_test_book_info_db_del($update_server_name,$argv) {

	// 検証バッチサーバ接続情報取得
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM math_test_book_info;";
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

//	数学検定情報Bアップロードログアップ
function math_test_book_info_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];

	//	DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='math_test_book_info'".
			" AND state='1';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='math_test_book_info'".
				" AND state='1';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" 'math_test_book_info',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}

//

//// add end okabe 2020/09/05



//	すらら英単語テスト単語管理情報DBアップロードログアップ
function test_english_word_db_log($update_server_name, $argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	$update_mode = $argv['2'];
	$test_type_num = $argv['3'];
	$send_data = $argv['8'];
	$upd_tts_id = $argv['9'];
	if ($test_type_num < 1) {
		$ERROR[] = "Not test_type_num";
		return $ERROR;
	}

	// 本番バッチDB
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	同じアップがあるかチェック
	$count = 0;
	$sql  = "SELECT count(*) AS count FROM test_mate_upd_log".
			" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."';";
	if ($result = $cd->query($sql)) {
		$count = $cd->num_rows($result);
	}

	if ($count > 0) {
		$sql  = "UPDATE test_mate_upd_log SET".
				" state='3'".
				" WHERE update_mode='".$cd->real_escape($update_mode)."'".
			" AND state='1'".
			" AND course_num='".$cd->real_escape($test_type_num)."';";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		}
	}

	//	データーベース更新
	$sql  = "INSERT INTO test_mate_upd_log".
			" (".
				"`test_mate_upd_log_num` ,".
				"`update_mode` ,".
				"`course_num` ,".
				"`send_data` ,".
				"`regist_time` ,".
				"`state` ,".
				"`upd_tts_id`".
			")".
			" VALUES (".
				"NULL ,".
				" '".$cd->real_escape($update_mode)."',".
				" '".$cd->real_escape($test_type_num)."',".
				" '".$cd->real_escape($send_data)."',".
				" NOW( ) ,".
				" '1',".
				" '".$cd->real_escape($upd_tts_id)."'".
			");";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL INSERT ERROR<br>$sql";
	}

	//	DB切断
	$cd->close();

	return $ERROR;
}
// add end 2019/05/21 yoshizawa すらら英単語テストワード管理
?>