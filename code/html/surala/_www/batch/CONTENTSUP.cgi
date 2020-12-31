#!/usr/bin/php -q
<?PHP
/*

	検証、本番バッチアップロードシステム

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
$course_num = $argv[3];
$stage_num = $argv[4];
$lesson_num = $argv[5];
$unit_num = $argv[6];
$block_num = $argv[7];

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

//	検証バッチサーバーDB接続
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

if ($update_mode == "course_course") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_course_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CC1";
				write_error($ERROR);
				echo "CC1";
				exit;
			}
			//	DB削除
			$ERROR = course_course_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CC1D";
				write_error($ERROR);
				echo "CC1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_course_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CC2";
				write_error($ERROR);
				echo "CC2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_course_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CC3";
				write_error($ERROR);
				echo "CC3";
				exit;
			}
	}
} elseif ($update_mode == "course_stage") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_stage_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "4";
				write_error($ERROR);
				echo "4";
				exit;
			}
			//	DB削除
			$ERROR = course_stage_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "5";
				write_error($ERROR);
				echo "5";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_stage_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "6";
				write_error($ERROR);
				echo "6";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_stage_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "5d";
				write_error($ERROR);
				echo "5d";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_stage_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "7";
				write_error($ERROR);
				echo "7";
				exit;
			}
			//	web削除
			$ERROR = course_stage_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "8";
				write_error($ERROR);
				echo "8";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_stage_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "9";
			write_error($ERROR);
			echo "9";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = course_stage_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "8d";
				write_error($ERROR);
				echo "8d";
				exit;
			}
	}
} elseif ($update_mode == "course_lesson") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_lesson_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "10";
				write_error($ERROR);
				echo "10";
				exit;
			}
			//	DB削除
			$ERROR = course_lesson_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "11";
				write_error($ERROR);
				echo "11";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_lesson_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "12";
				write_error($ERROR);
				echo "12";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_lesson_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "11d";
				write_error($ERROR);
				echo "11d";
				exit;
			}
	}
} elseif ($update_mode == "course_unit") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_unit_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "13";
				write_error($ERROR);
				echo "13";
				exit;
			}
			//	DB削除
			$ERROR = course_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "14";
				write_error($ERROR);
				echo "14";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_unit_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "15";
				write_error($ERROR);
				echo "15";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "14d";
				write_error($ERROR);
				echo "14d";
				exit;
			}
	}
} elseif ($update_mode == "course_block") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_block_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "16";
				write_error($ERROR);
				echo "16";
				exit;
			}
			//	DB削除
			$ERROR = course_block_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "17";
				write_error($ERROR);
				echo "17";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_block_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "18";
				write_error($ERROR);
				echo "18";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_block_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "17d";
				write_error($ERROR);
				echo "17d";
				exit;
			}
	}
} elseif ($update_mode == "course_review_setting") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_review_setting_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "19";
				write_error($ERROR);
				echo "19";
				exit;
			}
			//	DB削除
			$ERROR = course_review_setting_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "20";
				write_error($ERROR);
				echo "20";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_review_setting_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "21";
				write_error($ERROR);
				echo "21";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_review_setting_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "20d";
				write_error($ERROR);
				echo "20d";
				exit;
			}
	}
} elseif ($update_mode == "course_skill") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_skill_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "22";
				write_error($ERROR);
				echo "22";
				exit;
			}
			//	DB削除
			$ERROR = course_skill_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "23";
				write_error($ERROR);
				echo "23";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_skill_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "24";
				write_error($ERROR);
				echo "24";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_skill_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "23d";
				write_error($ERROR);
				echo "23d";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_skill_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "25";
				write_error($ERROR);
				echo "25";
				exit;
			}
			//	web削除
			$ERROR = course_skill_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "26";
				write_error($ERROR);
				echo "26";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_skill_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "27";
			write_error($ERROR);
			echo "27";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = course_skill_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "26d";
				write_error($ERROR);
				echo "26d";
				exit;
			}
	}
} elseif ($update_mode == "course_chart") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_chart_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "28";
				write_error($ERROR);
				echo "28";
				exit;
			}
			//	DB削除
			$ERROR = course_chart_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "29";
				write_error($ERROR);
				echo "29";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_chart_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "30";
			write_error($ERROR);
			echo "30";
			exit;
		}
	} elseif ($update_type == 3) {
			//	ファイル削除
			$ERROR = course_chart_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "29d";
				write_error($ERROR);
				echo "29d";
				exit;
			}
	}
} elseif ($update_mode == "course_print") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_print_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "31";
				write_error($ERROR);
				echo "31";
				exit;
			}
			//	DB削除
			$ERROR = course_print_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "32";
				write_error($ERROR);
				echo "32";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_print_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "33";
			write_error($ERROR);
			echo "33";
			exit;
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_print_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "32d";
				write_error($ERROR);
				echo "32d";
				exit;
			}
	}
} elseif ($update_mode == "course_flash") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_flash_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "34";
				write_error($ERROR);
				echo "34";
				exit;
			}
			//	DB削除
			$ERROR = course_flash_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "35";
				write_error($ERROR);
				echo "35";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_flash_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "36";
			write_error($ERROR);
			echo "36";
			exit;
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_flash_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "35d";
				write_error($ERROR);
				echo "35d";
				exit;
			}
	}

} elseif ($update_mode == "course_unit_time") {

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_unit_time_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "53";
				write_error($ERROR);
				echo "53";
				exit;
			}
			//	DB削除
			$ERROR = course_unit_time_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "54";
				write_error($ERROR);
				echo "54";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_unit_time_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "55";
				write_error($ERROR);
				echo "55";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_unit_time_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "56d";
				write_error($ERROR);
				echo "56d";
				exit;
			}
	}

// add start oda 2020/01/14 別教科サジェスト
} elseif ($update_mode == "course_unit_suggest") {

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_unit_suggest_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "US1";
				write_error($ERROR);
				echo "US1";
				exit;
			}
			//	DB削除
			$ERROR = course_unit_suggest_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "US2";
				write_error($ERROR);
				echo "US2";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_unit_suggest_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "US3";
				write_error($ERROR);
				echo "US3";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_unit_suggest_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "US4";
				write_error($ERROR);
				echo "US4";
				exit;
			}
	}
// add end oda 2020/01/14 別教科サジェスト

} elseif ($update_mode == "problem") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = problem_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "37";
				write_error($ERROR);
				echo "37";
				exit;
			}
			//	DB削除
			$ERROR = problem_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "38";
				write_error($ERROR);
				echo "38";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = problem_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "39";
				write_error($ERROR);
				echo "39";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = problem_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "38d";
				write_error($ERROR);
				echo "38d";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = problem_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "40";
				write_error($ERROR);
				echo "40";
				exit;
			}
			//	web削除
			$ERROR = problem_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "41";
				write_error($ERROR);
				echo "41";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = problem_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "42";
			write_error($ERROR);
			echo "42";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = problem_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "41d";
				write_error($ERROR);
				echo "41d";
				exit;
			}
	}
} elseif ($update_mode == "manual") {

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = manual_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "42";
				write_error($ERROR);
				echo "42";
				exit;
			}
			//	web削除
			$ERROR = manual_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "43";
				write_error($ERROR);
				echo "43";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = manual_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "44";
			write_error($ERROR);
			echo "44";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = manual_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "43d";
				write_error($ERROR);
				echo "43d";
				exit;
			}
	}
} elseif ($update_mode == "palette") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = palette_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "45";
				write_error($ERROR);
				echo "45";
				exit;
			}
			//	web削除
			$ERROR = palette_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "46";
				write_error($ERROR);
				echo "46";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = palette_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "47";
			write_error($ERROR);
			echo "47";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = palette_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "48";
				write_error($ERROR);
				echo "48";
				exit;
			}
	}
} elseif ($update_mode == "qnaire") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = qnaire_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "49";
				write_error($ERROR);
				echo "49";
				exit;
			}
			//	DB削除
			$ERROR = qnaire_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "50";
				write_error($ERROR);
				echo "50";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = qnaire_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "51";
				write_error($ERROR);
				echo "51";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = qnaire_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "52d";
				write_error($ERROR);
				echo "52d";
				exit;
			}
	}

} elseif ($update_mode == "status_study_time") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = status_study_time_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "53";
				write_error($ERROR);
				echo "53";
				exit;
			}
			//	DB削除
			$ERROR = status_study_time_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "54";
				write_error($ERROR);
				echo "54";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = status_study_time_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "55";
				write_error($ERROR);
				echo "55";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = status_study_time_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "56d";
				write_error($ERROR);
				echo "56d";
				exit;
			}
	}
} elseif ($update_mode == "status_clear_unit") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = status_clear_unit_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "57";
				write_error($ERROR);
				echo "57";
				exit;
			}
			//	DB削除
			$ERROR = status_clear_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "58";
				write_error($ERROR);
				echo "58";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = status_clear_unit_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "59";
				write_error($ERROR);
				echo "59";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = status_clear_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "60d";
				write_error($ERROR);
				echo "60d";
				exit;
			}
	}

} elseif ($update_mode == "service") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = service_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV1";
				write_error($ERROR);
				echo "SV1";
				exit;
			}
			//	DB削除
			$ERROR = service_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV1D";
				write_error($ERROR);
				echo "SV1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = service_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV2";
				write_error($ERROR);
				echo "SV2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = service_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV3";
				write_error($ERROR);
				echo "SV3";
				exit;
			}
	}

//	翻訳情報
} elseif ($update_mode == "trans") {
	//	本番DB更新
	if ($update_type == 1) {
			//	本番バッチを更新
			$ERROR = trans_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV1";
				write_error($ERROR);
				echo "SV1";
				exit;
			}
			//	検証バッチDBを削除
			$ERROR = trans_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV1D";
				write_error($ERROR);
				echo "SV1D";
				exit;
			}

	//	検証分散DBを更新
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = trans_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SV2";
				write_error($ERROR);
				echo "SV2";
				exit;
			}
		}

	//	検証バッチDBを削除
	} elseif ($update_type == 3) {
		$ERROR = trans_db_del($btw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "SV3";
			write_error($ERROR);
			echo "SV3";
			exit;
		}
	}

} elseif ($update_mode == "ng_word") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = ng_word_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "NW1";
				write_error($ERROR);
				echo "NW1";
				exit;
			}
			//	DB削除
			$ERROR = ng_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "NW1D";
				write_error($ERROR);
				echo "NW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = ng_word_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "NW2";
				write_error($ERROR);
				echo "NW2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = ng_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "NW3";
				write_error($ERROR);
				echo "NW3";
				exit;
			}
	}
} elseif ($update_mode == "ms_ranking_point_def") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = ms_ranking_point_def_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "RPD1";
				write_error($ERROR);
				echo "RPD1";
				exit;
			}
			//	DB削除
			$ERROR = ms_ranking_point_def_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "RPD1D";
				write_error($ERROR);
				echo "RPD1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = ms_ranking_point_def_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "RPD2";
				write_error($ERROR);
				echo "RPD2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = ms_ranking_point_def_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "RPD3";
				write_error($ERROR);
				echo "RPD3";
				exit;
			}
	}
} elseif ($update_mode == "course_problem_lms_unit") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_problem_lms_unit_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CPLU1";
				write_error($ERROR);
				echo "CPLU1";
				exit;
			}
			//	DB削除
			$ERROR = course_problem_lms_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CPLU1D";
				write_error($ERROR);
				echo "CPLU1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_problem_lms_unit_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CPLU2";
				write_error($ERROR);
				echo "CPLU2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_problem_lms_unit_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CPLU3";
				write_error($ERROR);
				echo "CPLU3";
				exit;
			}
	}
} elseif ($update_mode == "course_ms_ranking_point") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_ms_ranking_point_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CRP1";
				write_error($ERROR);
				echo "CRP1";
				exit;
			}
			//	DB削除
			$ERROR = course_ms_ranking_point_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CRP1D";
				write_error($ERROR);
				echo "CRP1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_ms_ranking_point_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CRP2";
				write_error($ERROR);
				echo "CRP2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_ms_ranking_point_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CRP3";
				write_error($ERROR);
				echo "CRP3";
				exit;
			}
	}
} elseif ($update_mode == "course_menu_status") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_menu_status_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CMS1";
				write_error($ERROR);
				echo "CMS1";
				exit;
			}
			//	DB削除
			$ERROR = course_menu_status_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CMS1D";
				write_error($ERROR);
				echo "CMS1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_menu_status_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CMS2";
				write_error($ERROR);
				echo "CMS2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_menu_status_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CMS3";
				write_error($ERROR);
				echo "CMS3";
				exit;
			}
	}

} elseif ($update_mode == "sns_switch") {
echo $update_mode."\n";
	//	DB更新
	if ($update_type == 1) {
			$ERROR = sns_switch_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SNS1";
				write_error($ERROR);
				echo "SNS1";
				exit;
			}
			//	DB削除
			$ERROR = sns_switch_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SNS1D";
				write_error($ERROR);
				echo "SNS1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = sns_switch_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SNS2";
				write_error($ERROR);
				echo "SNS2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = sns_switch_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SNS3";
				write_error($ERROR);
				echo "SNS3";
				exit;
			}
	}

} elseif ($update_mode == "course_english_word") {
	//	ワード情報

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_english_word_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1";
				write_error($ERROR);
				echo "CEW1";
				exit;
			}
			//	DB削除
			$ERROR = course_english_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1D";
				write_error($ERROR);
				echo "CEW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_english_word_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW2";
				write_error($ERROR);
				echo "CEW2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_english_word_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW3D";
				write_error($ERROR);
				echo "CEW3D";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_english_word_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW1";
				write_error($ERROR);
				echo "CEWW1";
				exit;
			}
			//	web削除
			$ERROR = course_english_word_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW1D";
				write_error($ERROR);
				echo "CEWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_english_word_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "CEWW2";
			write_error($ERROR);
			echo "CEWW2";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = course_english_word_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW3D";
				write_error($ERROR);
				echo "CEWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);

} elseif ($update_mode == "course_one_point") {
	//	ワンポイント情報

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_one_point_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "COP1";
				write_error($ERROR);
				echo "COP1";
				exit;
			}
			//	DB削除
			$ERROR = course_one_point_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "COP1D";
				write_error($ERROR);
				echo "COP1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_one_point_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "COP2";
				write_error($ERROR);
				echo "COP2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_one_point_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "COP3";
				write_error($ERROR);
				echo "COP3";
				exit;
			}
	}

	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = course_one_point_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW1";
				write_error($ERROR);
				echo "CEWW1";
				exit;
			}
			//	web削除
			$ERROR = course_one_point_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW1D";
				write_error($ERROR);
				echo "CEWW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = course_one_point_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "CEWW2";
			write_error($ERROR);
			echo "CEWW2";
			exit;
		}
	} elseif ($update_type == 3) {
			//	web削除
			$ERROR = course_one_point_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEWW3D";
				write_error($ERROR);
				echo "CEWW3D";
				exit;
			}
	}

	//	処理終了処理
	$update_num = $argv['5'];
	mt_update_end($update_num);

} elseif ($update_mode == "course_game_collection") {
	//	ゲーム収集要素情報

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_game_collection_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1";
				write_error($ERROR);
				echo "CEW1";
				exit;
			}
			//	DB削除
			$ERROR = course_game_collection_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1D";
				write_error($ERROR);
				echo "CEW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_game_collection_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW2";
				write_error($ERROR);
				echo "CEW2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_game_collection_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW3D";
				write_error($ERROR);
				echo "CEW3D";
				exit;
			}
	}
//add start kimura 2019/07/16 生徒画面TOP改修 {{{
} elseif ($update_mode == "course_medal_setting") {
	//	ゲーム収集要素情報

	//	DB更新
	if ($update_type == 1) {
			$ERROR = course_medal_setting_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1";
				write_error($ERROR);
				echo "CEW1";
				exit;
			}
			//	DB削除
			$ERROR = course_medal_setting_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW1D";
				write_error($ERROR);
				echo "CEW1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = course_medal_setting_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW2";
				write_error($ERROR);
				echo "CEW2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = course_medal_setting_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "CEW3D";
				write_error($ERROR);
				echo "CEW3D";
				exit;
			}
	}
//add end   kimura 2019/07/16 生徒画面TOP改修 }}}
// add start hasegawa 2018/05/11 手書きV2対応
} elseif ($update_mode == "drill_tegaki_default") {
	//	DB更新
	if ($update_type == 1) {
			$ERROR = drill_tegaki_default_db($bhw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "DTD1";
				write_error($ERROR);
				echo "DTD1";
				exit;
			}
			//	DB削除
			$ERROR = drill_tegaki_default_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "DTD1D";
				write_error($ERROR);
				echo "DTD1D";
				exit;
			}
	} elseif ($update_type == 2) {
		foreach ($L_TSTC_DB AS $update_server_name) {
			$ERROR = drill_tegaki_default_db($update_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "DTD2";
				write_error($ERROR);
				echo "DTD2";
				exit;
			}
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = drill_tegaki_default_db_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "DTD3";
				write_error($ERROR);
				echo "DTD3";
				exit;
			}
	}
// add end hasegawa 2018/05/11
// add start hirose 2018/10/10 commonフォルダアップ機能追加
} elseif ($update_mode == "system_chart_common") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = system_chart_common_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SCC1";
				write_error($ERROR);
				echo "SCC1";
				exit;
			}
			//	DB削除
			$ERROR = system_chart_common_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SCC1D";
				write_error($ERROR);
				echo "SCC1D";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = system_chart_common_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "SCC2";
			write_error($ERROR);
			echo "SCC2";
			exit;
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = system_chart_common_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "SCC3D";
				write_error($ERROR);
				echo "SCC3D";
				exit;
			}
	}
} elseif ($update_mode == "lecture_common") {
	//	ファイル更新
	if ($update_type == 1) {
			$ERROR = lecture_common_web($btw_server_name,$tw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "LC1";
				write_error($ERROR);
				echo "LC1";
				exit;
			}
			//	DB削除
			$ERROR = lecture_common_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "LC1D";
				write_error($ERROR);
				echo "LC1D";
				exit;
			}
	} elseif ($update_type == 2) {
		$ERROR = lecture_common_web($btw_server_name,$ctw_server_name,$argv);
		if ($ERROR) {
			$ERROR[] = "LC2";
			write_error($ERROR);
			echo "LC2";
			exit;
		}
	} elseif ($update_type == 3) {
			//	DB削除
			$ERROR = lecture_common_web_del($btw_server_name,$argv);
			if ($ERROR) {
				$ERROR[] = "LC3D";
				write_error($ERROR);
				echo "LC3D";
				exit;
			}
	}
// add end hirose 2018/10/10 commonフォルダアップ機能追加
}

// DB切断
$bat_cd->close();

//	終了
echo "0\n";

exit;



//	コース基本情報DBアップロード
function course_course_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM course".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'course', $INSERT_NAME, $INSERT_VALUE);
	}

    //kaopiz 2020/11/18 CR admin external start
    //	データーベース更新 external
    $INSERT_NAME_EXTERNAL = array();
    $INSERT_VALUE_EXTERNAL = array();
    $sql  = "SELECT * FROM external_course".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
    if ($result = $bat_cd->query($sql)) {
        make_insert_query($result, 'external_course', $INSERT_NAME_EXTERNAL, $INSERT_VALUE_EXTERNAL);
    }
    //kaopiz 2020/11/18 CR admin external end


	//	アップ先DBデーター削除クエリー
	$sql  = "DELETE FROM course".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

    //kaopiz 2020/11/18 CR admin external start
    $sql  = "DELETE FROM external_course".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
    $DELETE_SQL[] = $sql;
    //kaopiz 2020/11/18 CR admin external end

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

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

    //kaopiz 2020/11/09 CR admin external start
    if ($INSERT_NAME_EXTERNAL && $INSERT_VALUE_EXTERNAL) {
        foreach ($INSERT_NAME_EXTERNAL AS $table_name => $insert_name) {
            if ($INSERT_VALUE_EXTERNAL[$table_name]) {
                $ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE_EXTERNAL[$table_name]);
                if ($ERROR) { return $ERROR; }
            }
        }
    }
    //kaopiz 2020/11/09 CR admin external end

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
	$sql = "OPTIMIZE TABLE course;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	コース基本情報DB削除
function course_course_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM course".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

    //kaopiz 2020/11/18 CR admin external start
    $sql  = "DELETE FROM external_course".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
    $DELETE_SQL[] = $sql;
    //kaopiz 2020/11/18 CR admin external end

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約を戻す
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}


//	ステージ基本情報DBアップロード
function course_stage_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT * FROM stage".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				$course_num = $list['course_num'];
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
					if ($key == "stage_num") { $COURSE_NUM[$course_num][$val] = $val; }
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
	$sql = "DELETE FROM stage WHERE course_num='".$cd->real_escape($update_course_num)."';";
	if (!$cd->query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO stage (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE stage;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証分散DB切断
	$cd->close();

	return $ERROR;
}

//	ステージ基本情報webアップロード
function course_stage_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	lesson.htm
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_STUDENT_TEMP_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_STUDENT_TEMP_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_STUDENT_TEMP_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_STUDENT_TEMP_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_STUDENT_TEMP_DIR;
		$remote_dir	= BASE_DIR.REMOTE_STUDENT_TEMP_DIR;
	}
	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	//	background.jpg	course.css
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_TEMP_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_TEMP_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_TEMP_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_TEMP_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	return $ERROR;
}

//	ステージ基本情報DB削除
function course_stage_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM stage WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}

//	ステージ基本情報web削除
function course_stage_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_course_num = $argv[3];

	//	lesson.htm
	$remote_dir = KBAT_DIR.REMOTE_STUDENT_TEMP_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	//	background.jpg	course.css
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	return $ERROR;
}



//	Lesson基本情報DBアップロード
function course_lesson_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT * FROM lesson".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
					if ($key == "stage_num") { $STAGE_NUM[$val] = $val; }
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
	if ($STAGE_NUM) {
		unset($stage_num_list);
		foreach ($STAGE_NUM AS $key => $val) {
			if ($stage_num_list) { $stage_num_list .= ","; }
			$stage_num_list .= $val;
		}
		$sql  = "DELETE FROM lesson".
				" WHERE course_num='".$cd->real_escape($update_course_num)."';";
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

	//	アップ先DBデーター追加
	if (!$ERROR && $COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO lesson (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE lesson;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	レッスン基本情報DB削除
function course_lesson_db_del($update_server_name,$argv) {

	$update_course_num = $argv[3];

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	データーベース
	$sql  = "SELECT * FROM lesson".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				foreach ($list AS $key => $val) {
					if ($key == "stage_num") { $STAGE_NUM[$val] = $val; }
				}
			}
		}
	}

	//	検証バッチDBデーター削除
	if ($STAGE_NUM) {
		unset($stage_num_list);
		foreach ($STAGE_NUM AS $key => $val) {
			if ($stage_num_list) { $stage_num_list .= ","; }
			$stage_num_list .= $val;
		}
		$sql  = "DELETE FROM lesson".
				" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL DELETE ERROR<br>$sql";
		}
	}

	return $ERROR;
}



//	ユニット基本情報DBアップロード
function course_unit_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT * FROM unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."'".
			" ORDER BY unit_num;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}
    //kaopiz 2020/11/18 CR admin external start
    $sql  = "SELECT * FROM external_unit".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."'".
        " ORDER BY unit_num;";
    if ($result = $bat_cd->query($sql)) {
        $flag = 0;
        while ($list=$bat_cd->fetch_assoc($result)) {
            if ($list) {
                if ($INSERT_VALUES_EXTERNAL) { $INSERT_VALUES_EXTERNAL .= ","; }
                unset($INSERT_VALUE);
                foreach ($list AS $key => $val) {
                    if ($flag != 1) {
                        if ($COLUMN_NAME_EXTERNAL) { $COLUMN_NAME_EXTERNAL .= ","; }
                        $COLUMN_NAME_EXTERNAL .= $key;
                    }
                    if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
                    $INSERT_VALUE .= "'".$val."'";
                }
                if ($COLUMN_NAME_EXTERNAL) { $flag = 1; }
                $INSERT_VALUES_EXTERNAL .= " (".$INSERT_VALUE.")";
            }
        }
    }
    //kaopiz 2020/11/18 CR admin external end

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";

    //kaopiz 2020/11/18 CR admin external start
    $sql_external  = "DELETE FROM external_unit".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
    if (!$cd->exec_query($sql_external)) {
        $ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql_external";
        $sql  = "ROLLBACK";
        if (!$cd->exec_query($sql)) {
            $ERROR[] = "SQL ROLLBACK ERROR";
        }
        $cd->close();
        return $ERROR;
    }
    //kaopiz 2020/11/18 CR admin external end

	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO unit (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

    //kaopiz 2020/11/18 CR admin external start
    if ($COLUMN_NAME_EXTERNAL && $INSERT_VALUES_EXTERNAL) {
        $sql = "INSERT INTO external_unit (".$COLUMN_NAME_EXTERNAL.") VALUES ".$INSERT_VALUES_EXTERNAL.";";
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
    //kaopiz 2020/11/18 CR admin external end

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE unit;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ユニット基本情報DB削除
function course_unit_db_del($update_server_name,$argv) {

	$update_course_num = $argv[3];

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql";
	}

    //kaopiz 2020/11/18 CR admin external start
    $sql_external  = "DELETE FROM external_unit".
        " WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
    if (!$bat_cd->exec_query($sql_external)) {
        $ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql_external";
    }
    //kaopiz 2020/11/18 CR admin external end

	return $ERROR;
}



//	ブロック基本情報DBアップロード
function course_block_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	unset($COLUMN_NAME);
	unset($INSERT_VALUES);
	$sql  = "SELECT * FROM block".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."'".
			" ORDER BY block_num;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
	$sql  = "DELETE FROM block".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
	if (!$cd->exec_query($sql)) {
		$ERRO[R] = "SQL TRUNCATE TABLE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO block (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE block;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ブロック基本情報DB削除
function course_block_db_del($update_server_name,$argv) {

	$update_course_num = $argv[3];

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM block".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql";
	}

	return $ERROR;
}



//	診断復習情報DBアップロード
function course_review_setting_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	診断復習情報　データーベース更新
	unset($COLUMN_NAME);
	unset($INSERT_VALUES);
	$sql  = "SELECT * FROM review_setting".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."'".
			" ORDER BY review_setting_num;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM review_setting".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO review_setting (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE review_setting;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	診断復習基本情報DB削除
function course_review_setting_db_del($update_server_name,$argv) {

	$update_course_num = $argv[3];

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM review_setting".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE TABLE ERROR<br>$sql";
	}

	return $ERROR;
}



//	スキル情報DBアップロード
function course_skill_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

//echo "update_server_name===>".$update_server_name."\n";
	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT * FROM skill".
			" WHERE state!='1' AND course_num='".$bat_cd->real_escape($update_course_num)."'".
			" ORDER BY list_num;";
	if ($result = $bat_cd->query($sql)) {
//echo "sql===>".$sql."\n";

		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
					if ($key == "skill_num") { $SKILL_NUM[$val] = $val; }
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM skill WHERE course_num='".$cd->real_escape($update_course_num)."';";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO skill (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE skill;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	スキル情報webアップロード
function course_skill_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	スキル音声ファイル
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	//	スキル画像ファイル
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	return $ERROR;
}

//	スキル情報DB削除
function course_skill_db_del($update_server_name,$argv) {

	$update_course_num = $argv[3];

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM skill WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}

//	スキル情報web削除
function course_skill_web_del($update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	スキル音声ファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	//	スキル画像ファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	return $ERROR;
}



//	体系図情報webアップロード
function course_chart_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	体系図ファイル
	if ($update_type == 1) { // 本番バッチアップ
		$local_dir_l	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/".$update_course_num;
		$mk_dir_l		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/";
		$remote_dir_l	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/";
		$local_dir_p	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/".$update_course_num;
		$mk_dir_p		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/";
		$remote_dir_p	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/";
		$local_dir_j	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/".$update_course_num;
		$mk_dir_j		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/";
		$remote_dir_j	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/";
		$local_dir_h	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/".$update_course_num;
		$mk_dir_h		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/";
		$remote_dir_h	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/";

	} else {
		$local_dir_l	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/".$update_course_num;
		$mk_dir_l		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."l/";
		$remote_dir_l	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."l/";
		$local_dir_p	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/".$update_course_num;
		$mk_dir_p		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."p/";
		$remote_dir_p	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."p/";
		$local_dir_j	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/".$update_course_num;
		$mk_dir_j		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."j/";
		$remote_dir_j	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."j/";
		$local_dir_h	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/".$update_course_num;
		$mk_dir_h		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."h/";
		$remote_dir_h	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."h/";

	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir_j;
	$command2  = "scp -rp ".$local_dir_j." suralacore01@".$update_server_name.":".$remote_dir_j;
	$command3  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir_h;
	$command4  = "scp -rp ".$local_dir_h." suralacore01@".$update_server_name.":".$remote_dir_h;
	$command5  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir_p;
	$command6  = "scp -rp ".$local_dir_p." suralacore01@".$update_server_name.":".$remote_dir_p;
	$command7  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir_l;
	$command8  = "scp -rp ".$local_dir_l." suralacore01@".$update_server_name.":".$remote_dir_l;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	exec("$command3",$LIST);
	exec("$command4",$LIST);

	exec("$command5",$LIST);
	exec("$command6",$LIST);

	exec("$command7",$LIST);
	exec("$command8",$LIST);

	return $ERROR;
}

//	体系図情報web削除
function course_chart_web_del($update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_course_num = $argv[3];

	//	体系図ファイル
	$remote_dir_l = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/".$update_course_num;
	$remote_dir_p = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/".$update_course_num;
	$remote_dir_j = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/".$update_course_num;
	$remote_dir_h = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/".$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir_j;
	$command2  = "rm -rf ".$remote_dir_h;
	$command3  = "rm -rf ".$remote_dir_p;
	$command4  = "rm -rf ".$remote_dir_l;

	exec("$command1",$LIST);
	exec("$command2",$LIST);
	exec("$command3",$LIST);
	exec("$command4",$LIST);

	return $ERROR;
}



//	まとめプリント情報webアップロード
function course_print_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	まとめプリントファイル
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PRINT_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_PRINT_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_PRINT_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PRINT_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_PRINT_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_PRINT_DIR;
	}

    // ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1",$LIST);
	exec("$command2",$LIST);

	return $ERROR;
}

//	まとめプリント情報web削除
function course_print_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	まとめプリントファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_PRINT_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	return $ERROR;
}



//	学習FLASH情報webアップロード
function course_flash_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];
	$update_stagee_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];

	//	学習FLASHファイル
	if ($update_type == 1) {
		// >>> add 2018/05/22 yoshizawa レクチャーアップ改修 確認用
		$remove_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num."/".$update_unit_num;
		// <<<
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num."/".$update_unit_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num;
	} else {
		// >>> add 2018/05/22 yoshizawa レクチャーアップ改修 確認用
		$remove_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num."/".$update_unit_num;
		// <<<
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num."/".$update_unit_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num;
	}

	// ローカルのリリースコンテンツをリモートに反映
	// >>> add 2018/05/22 yoshizawa レクチャーアップ改修 確認用
	$remove_command  = "ssh suralacore01@".$update_server_name." rm -rf ".$remove_dir;
	// <<<
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	// >>> add 2018/05/22 yoshizawa レクチャーアップ改修 確認用
	// 反映先のレクチャーを削除してからローカルのレクチャーをアップする。
	exec("$remove_command",$LIST);
	// <<<
	exec("$command1",$LIST);
	exec("$command2",$LIST);

	return $ERROR;
}

//	学習FLASH情報web削除
function course_flash_web_del($update_server_name,$argv) {

	global $L_DB;

	//	ファイル削除
	$update_type = $argv[1];
	$update_course_num = $argv[3];
	$update_stagee_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];

	//	学習FLASHファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$update_course_num."/".$update_stagee_num."/".$update_lesson_num."/".$update_unit_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1",$LIST);

	return $ERROR;
}



//	ユニット解答目安時間情報DBアップロード
function course_unit_time_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT * FROM package_standard_time".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
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
				$INSERT_VALUE[$num] = $insert_value;
				$num++;
				$i = 1;
				$insert_value = "";
			}
		}
		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
	$sql  = "DELETE FROM package_standard_time".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター追加
	if ($insert_name && count($INSERT_VALUE)) {
		foreach ($INSERT_VALUE AS $values) {
			$sql  = "INSERT INTO package_standard_time".
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
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO package_standard_time (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE package_standard_time;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ユニット解答目安時間情報DB削除
function course_unit_time_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM package_standard_time".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}


// add start oda 2020/01/14 別教科サジェスト
//	別教科サジェスト情報DBアップロード
function course_unit_suggest_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$sql  = "SELECT ".
			" * ".
			" FROM unit_suggest".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."'".
			";";
	if ($result = $bat_cd->query($sql)) {
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
				$INSERT_VALUE[$num] = $insert_value;
				$num++;
				$i = 1;
				$insert_value = "";
			}
		}
		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
	$sql  = "DELETE FROM unit_suggest".
			" WHERE course_num='".$_POST['course_num']."'".
			";";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター追加
	if ($insert_name && count($INSERT_VALUE)) {
		foreach ($INSERT_VALUE AS $values) {
			$sql  = "INSERT INTO unit_suggest".
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
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO unit_suggest (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE unit_suggest;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	別教科サジェスト情報DB削除
function course_unit_suggest_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM unit_suggest".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}
// add end oda 2020/01/14 別教科サジェスト


//	問題情報DBアップロード
function problem_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];
	$update_stage_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];
	$update_block_num = $argv[7];

	if ($update_course_num < 1 || $update_stage_num < 1 || $update_lesson_num < 1 || $update_unit_num < 1 || $update_block_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	//	検証バッチDB情報取得
	unset($LOCAL_PROBLEM);
	//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
	// $sql  = "SELECT problem_num, course_num, block_num, display_problem_num, problem_type,".
	// 		" sub_display_problem_num, form_type, question, problem, voice_data,".
	// 		" hint, explanation, answer_time, parameter, set_difficulty,".
	// 		" hint_number, correct_number, clear_number, first_problem, latter_problem,".
	// 		" selection_words, correct, option1, option2, option3,".
	// 		" option4, option5, sentence_flag, error_msg, update_time, display, state".
	//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
	$sql = "SELECT *". //add kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //problemテーブルに変更があっても今後直さなくていいようにselect *に変更
			" FROM problem".
			" WHERE block_num='".$bat_cd->real_escape($update_block_num)."';";
	if ($result = $bat_cd->query($sql)) {
		while ($list=$bat_cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			if ($list) {
				foreach ($list AS $key => $val) {
					$LOCAL_PROBLEM[$problem_num][$key] = $val;
				}
			}
		}
	}

    //kaopiz 2020/11/18 CR admin external start
    unset($LOCAL_PROBLEM_EXTERNAL);
    $sql = "SELECT *".
        " FROM external_problem".
        " WHERE block_num='".$bat_cd->real_escape($update_block_num)."';";
    if ($result = $bat_cd->query($sql)) {
        while ($list=$bat_cd->fetch_assoc($result)) {
            $problem_num = $list['problem_num'];
            if ($list) {
                foreach ($list AS $key => $val) {
                    $LOCAL_PROBLEM_EXTERNAL[$problem_num][$key] = $val;
                }
            }
        }
    }
    //kaopiz 2020/11/18 CR admin external end

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DB情報取得
	unset($REMOTE_PROBLEM);
	$sql  = "SELECT problem_num FROM problem".
			" WHERE block_num='".$cd->real_escape($update_block_num)."';";
	if ($result = $cd->query($sql)) {
		while ($list=$cd->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$REMOTE_PROBLEM[$problem_num] = $problem_num;
		}
	}

    //kaopiz 2020/11/18 CR admin external start
    unset($REMOTE_PROBLEM_EXTERNAL);
    $sql  = "SELECT problem_num FROM external_problem".
        " WHERE block_num='".$cd->real_escape($update_block_num)."';";
    if ($result = $cd->query($sql)) {
        while ($list=$cd->fetch_assoc($result)) {
            $problem_num = $list['problem_num'];
            $REMOTE_PROBLEM_EXTERNAL[$problem_num] = $problem_num;
        }
    }
    //kaopiz 2020/11/18 CR admin external end

	//	更新
	if ($LOCAL_PROBLEM) {
		unset($insert_name);
		unset($insert_value);
		// unset($check_problem_num); //del kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
		$flag = 0;
		$i = 1;
		$num = 1;
		unset($INSERT_VALUE);
		//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更
		// foreach ($LOCAL_PROBLEM AS $problem_num => $VALUES) {
		// 	if ($REMOTE_PROBLEM[$problem_num]) {	//	UPDATE
		// 		unset($upddate_value);
		// 		if ($VALUES) {
		// 			foreach ($VALUES AS $key => $val) {
		// 				if ($key == "problem_num") { continue; }
		// 				if ($upddate_value) { $upddate_value .= ", "; }
		// 				$upddate_value .= $key."='".addslashes($val)."'";
		// 			}
		// 			$sql = "UPDATE problem SET ".$upddate_value.
		// 						  " WHERE problem_num='".$problem_num."' LIMIT 1;\n";
		// 			if (!$cd->exec_query($sql)) {
		// 				$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		// 				$sql  = "ROLLBACK";
		// 				if (!$cd->exec_query($sql)) {
		// 					$ERROR[] = "SQL ROLLBACK ERROR";
		// 				}
		// 				$cd->close();
		// 				return $ERROR;
		// 			}
		// 		}
		// 	} elseif ($problem_num > 0) {	//	INSERT
		// 		if ($VALUES) {
		// 			unset($value);
		// 			if ($insert_value) { $insert_value .= ", "; }
		// 			if ($VALUES['problem_num']) {
		// 				if ($check_problem_num) { $check_problem_num .= ", "; }
		// 				$check_problem_num .= $VALUES['problem_num'];
		// 			}
		// 			foreach ($VALUES AS $key => $val) {
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
		// 		}
		// 		if ($i == 50) {
		// 			$INSERT_VALUE[$num] = $insert_value;
		// 			$num++;
		// 			$i = 1;
		// 			$insert_value = "";
		// 		}
		// 	}
		// 	unset($REMOTE_PROBLEM[$problem_num]);
		// }
		//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
		//add start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更
		foreach ($LOCAL_PROBLEM AS $problem_num => $VALUES) {
			if ($VALUES) {
				unset($value);
				if ($insert_value) { $insert_value .= ", "; }
				//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更したため、前もってDELETEする必要がなくなった
				// if ($VALUES['problem_num']) {
				// 	if ($check_problem_num) { $check_problem_num .= ", "; }
				// 	$check_problem_num .= $VALUES['problem_num'];
				// }
				//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
				foreach ($VALUES AS $key => $val) {
					if ($flag != 1) {
						if ($insert_name) { $insert_name .= ", "; }
						$insert_name .= $key;
					}
					if ($value) { $value .= ", "; }
					$value .= "'".addslashes($val)."'";
				}
				if ($value) {
					$insert_value .= "(".$value.")";
					$i++;
				}
				if ($insert_name) { $flag = 1; }
			}
			if ($i == 50) {
				$INSERT_VALUE[$num] = $insert_value;
				$num++;
				$i = 1;
				$insert_value = "";
			}
			unset($REMOTE_PROBLEM[$problem_num]);
		}
		//add end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }

		//	INSERT
		if ($insert_name && count($INSERT_VALUE)) {
			//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更したため、前もってDELETEする必要がなくなった
			//	ゴミデーター削除
			// if ($check_problem_num) {
			// 	$sql  = "DELETE FROM problem".
			// 			" WHERE problem_num IN (".$check_problem_num.")".
			// 			" AND block_num!='".$bat_cd->real_escape($update_block_num)."';";
			// 	if (!$cd->exec_query($sql)) {
			// 		$ERROR[] = "SQL DELETE ERROR<br>$sql";
			// 		$sql  = "ROLLBACK";
			// 		if (!$cd->exec_query($sql)) {
			// 			$ERROR[] = "SQL ROLLBACK ERROR";
			// 		}
			// 		$cd->close();
			// 		return $ERROR;
			// 	}
			// }
			//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応

			foreach ($INSERT_VALUE AS $values) {
				//update start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
				//$sql  = "INSERT INTO problem".
				$sql  = "REPLACE INTO problem".
				//update end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
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

		// update oda 2016/03/16 課題要望一覧No519
		// ２人以上で操作している場合、アップ済のユニットを選択してしまう可能性が有る。
		// その為、２重で処理が動作してしまう為、バッチサーバのデータが取得できない場合は
		// なにもしない様にif ($LOCAL_PROBLEM) {の中で処理する様に修正しました。
		//	削除
		if ($REMOTE_PROBLEM) {
			unset($del_problem_num);
			foreach ($REMOTE_PROBLEM AS $problem_num => $val) {
				if ($problem_num > 0) {
					if ($del_problem_num) { $del_problem_num .= ", "; }
					$del_problem_num .= $problem_num;
				}
			}
			if ($del_problem_num) {
				$sql  = "UPDATE problem SET".
						" display='2',".
						" state='1',".
						" update_time=now()".
						" WHERE problem_num IN (".$del_problem_num.");";
				if (!$cd->exec_query($sql)) {
					$ERROR[] = "SQL INSERT ERROR<br>$sql";
					if (!$cd->exec_query($sql)) {
						$ERROR[] = "SQL ROLLBACK ERROR";
					}
					$cd->close();
					return $ERROR;
				}
			}
		}
	}

    //kaopiz 2020/11/18 CR admin external start
    if ($LOCAL_PROBLEM_EXTERNAL) {
        unset($insert_name);
        unset($insert_value);
        $flag = 0;
        $i = 1;
        $num = 1;
        unset($INSERT_VALUE);
        foreach ($LOCAL_PROBLEM_EXTERNAL AS $problem_num => $VALUES) {
            if ($VALUES) {
                unset($value);
                if ($insert_value) { $insert_value .= ", "; }
                foreach ($VALUES AS $key => $val) {
                    if ($flag != 1) {
                        if ($insert_name) { $insert_name .= ", "; }
                        $insert_name .= $key;
                    }
                    if ($value) { $value .= ", "; }
                    $value .= "'".addslashes($val)."'";
                }
                if ($value) {
                    $insert_value .= "(".$value.")";
                    $i++;
                }
                if ($insert_name) { $flag = 1; }
            }
            if ($i == 50) {
                $INSERT_VALUE[$num] = $insert_value;
                $num++;
                $i = 1;
                $insert_value = "";
            }
            unset($REMOTE_PROBLEM_EXTERNAL[$problem_num]);
        }
        if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }

        //	INSERT
        if ($insert_name && count($INSERT_VALUE)) {
            foreach ($INSERT_VALUE AS $values) {
                $sql  = "REPLACE INTO external_problem".
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

        if ($REMOTE_PROBLEM_EXTERNAL) {
            unset($del_problem_num);
            foreach ($REMOTE_PROBLEM_EXTERNAL AS $problem_num => $val) {
                if ($problem_num > 0) {
                    if ($del_problem_num) { $del_problem_num .= ", "; }
                    $del_problem_num .= $problem_num;
                }
            }
            if ($del_problem_num) {
                $sql  = "UPDATE external_problem SET".
                    " display_problem_num='2',".
                    " upd_date=now()".
                    " WHERE problem_num IN (".$del_problem_num.");";
                if (!$cd->exec_query($sql)) {
                    $ERROR[] = "SQL INSERT ERROR<br>$sql";
                    if (!$cd->exec_query($sql)) {
                        $ERROR[] = "SQL ROLLBACK ERROR";
                    }
                    $cd->close();
                    return $ERROR;
                }
            }
        }
    }
    //kaopiz 2020/11/18 CR admin external end

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	// >>> 2017/06/26 del yoshizawa
	// プラクティスアップデートの実行時間がかかってしまうのでコメントにします。
	// 本番DBのダンプファイル反映時に作り直されるのでここでの最適化は不要といたします。
	// //	テーブル最適化	負荷がかかる為調査後利用するか決める。
	// $sql = "OPTIMIZE TABLE problem;";
	// if (!$cd->exec_query($sql)) {
	//	$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	// }
	// <<<

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	問題情報webアップロード
function problem_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];
	$update_stage_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];
	$update_block_num = $argv[7];

	//	問題音声ファイル
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	//	問題画像ファイル
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	return $ERROR;
}

//	問題情報DB削除
function problem_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];
	$update_stage_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];
	$update_block_num = $argv[7];

	if ($update_course_num < 1 || $update_stage_num < 1 || $update_lesson_num < 1 || $update_unit_num < 1 || $update_block_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM problem WHERE block_num='".$bat_cd->real_escape($update_block_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}

//	問題情報web削除
function problem_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];
	$update_stage_num = $argv[4];
	$update_lesson_num = $argv[5];
	$update_unit_num = $argv[6];
	$update_block_num = $argv[7];

	//	問題音声ファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);


	//	問題画像ファイル
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$update_course_num."/".$update_stage_num."/".$update_lesson_num."/".$update_unit_num."/".$update_block_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}



//	マニュアル情報アップロード
function manual_web($btw_server_name,$update_server_name,$argv) {

	$update_type = $argv[1];

	//	マニュアルファイルアップロード
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR."../";
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR."../";
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_MANUAL_DIR."../";
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_MANUAL_DIR."../";
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	return $ERROR;
}

//	マニュアル情報削除
function manual_web_del($update_server_name,$argv) {

	//	数式パレットファイル削除
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_MANUAL_DIR;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}



//	数式パレット情報アップロード
function palette_web($btw_server_name,$update_server_name,$argv) {

	$update_type = $argv[1];

	//	数式パレットファイルアップロード
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR."../";
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR."../";
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_PALETTE_DIR."../";
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_PALETTE_DIR."../";
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	return $ERROR;
}

//	数式パレット情報削除
function palette_web_del($update_server_name,$argv) {

	//	数式パレットファイル削除
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}



//	アンケート情報DBアップロード
function qnaire_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	//	アンケート基本情報
	unset($INSERT_VALUES1);
	unset($INSERT_VALUE1);
	unset($COLUMN_NAME1);
	$sql  = "SELECT * FROM qnaire;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES1) { $INSERT_VALUES1 .= ","; }
				unset($INSERT_VALUE1);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME1) { $COLUMN_NAME1 .= ","; }
						$COLUMN_NAME1 .= $key;
					}
					if ($INSERT_VALUE1) { $INSERT_VALUE1 .= ","; }
					$INSERT_VALUE1 .= "'".$val."'";
				}
				if ($COLUMN_NAME1) { $flag = 1; }
				$INSERT_VALUES1 .= " (".$INSERT_VALUE1.")";
			}
		}
	}
	//	アンケート項目詳細情報
	unset($INSERT_VALUES2);
	unset($INSERT_VALUE2);
	unset($COLUMN_NAME2);
	$sql  = "SELECT * FROM qnaireelements;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES2) { $INSERT_VALUES2 .= ","; }
				unset($INSERT_VALUE2);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME2) { $COLUMN_NAME2 .= ","; }
						$COLUMN_NAME2 .= $key;
					}
					if ($INSERT_VALUE2) { $INSERT_VALUE2 .= ","; }
					$INSERT_VALUE2 .= "'".$val."'";
				}
				if ($COLUMN_NAME2) { $flag = 1; }
				$INSERT_VALUES2 .= " (".$INSERT_VALUE2.")";
			}
		}
	}

	if (!$INSERT_VALUES1 && !$INSERT_VALUES2) {
		return $ERROR;
	}


	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アンケート基本情報削除
	$sql = "TRUNCATE TABLE qnaire;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アンケート項目詳細情報削除
	$sql = "TRUNCATE TABLE qnaireelements;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	アンケート基本情報追加
	if ($COLUMN_NAME1 && $INSERT_VALUES1) {
		$sql = "INSERT INTO qnaire (".$COLUMN_NAME1.") VALUES ".$INSERT_VALUES1.";";
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

	//	アンケート基本情報追加2
	if ($COLUMN_NAME2 && $INSERT_VALUES2) {
		$sql = "INSERT INTO qnaireelements (".$COLUMN_NAME2.") VALUES ".$INSERT_VALUES2.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE qnaire;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE qnaireelements;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	アンケート情報DB削除
function qnaire_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	アンケート基本情報削除
	$sql = "TRUNCATE TABLE qnaire;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	アンケート項目詳細情報削除
	$sql = "TRUNCATE TABLE qnaireelements;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	すららランキングステータス（学習時間）情報DBアップロード
function status_study_time_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	//	すららランキングステータス（学習時間）情報
	unset($INSERT_VALUES1);
	unset($INSERT_VALUE1);
	unset($COLUMN_NAME1);
	$sql  = "SELECT * FROM status_study_time;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES1) { $INSERT_VALUES1 .= ","; }
				unset($INSERT_VALUE1);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME1) { $COLUMN_NAME1 .= ","; }
						$COLUMN_NAME1 .= $key;
					}
					if ($INSERT_VALUE1) { $INSERT_VALUE1 .= ","; }
					$INSERT_VALUE1 .= "'".$val."'";
				}
				if ($COLUMN_NAME1) { $flag = 1; }
				$INSERT_VALUES1 .= " (".$INSERT_VALUE1.")";
			}
		}
	}

	if (!$INSERT_VALUES1) {
		return $ERROR;
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	すららランキングステータス（学習時間）情報削除
	$sql = "TRUNCATE TABLE status_study_time;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	すららランキングステータス（学習時間）情報追加
	if ($COLUMN_NAME1 && $INSERT_VALUES1) {
		$sql = "INSERT INTO status_study_time (".$COLUMN_NAME1.") VALUES ".$INSERT_VALUES1.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE status_study_time;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	すららランキングステータス（学習時間）情報DB削除
function status_study_time_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	すららランキングステータス（学習時間）情報削除
	$sql = "TRUNCATE TABLE status_study_time;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	すららランキングステータス（ユニットクリアー数）情報DBアップロード
function status_clear_unit_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	//	すららランキングステータス（ユニットクリアー数）情報
	unset($INSERT_VALUES1);
	unset($INSERT_VALUE1);
	unset($COLUMN_NAME1); $sql  = "SELECT * FROM status_clear_unit;";
	if ($result = $bat_cd->query($sql)) {
		$flag = 0;
		while ($list=$bat_cd->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES1) { $INSERT_VALUES1 .= ","; }
				unset($INSERT_VALUE1);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME1) { $COLUMN_NAME1 .= ","; }
						$COLUMN_NAME1 .= $key;
					}
					if ($INSERT_VALUE1) { $INSERT_VALUE1 .= ","; }
					$INSERT_VALUE1 .= "'".$val."'";
				}
				if ($COLUMN_NAME1) { $flag = 1; }
				$INSERT_VALUES1 .= " (".$INSERT_VALUE1.")";
			}
		}
	}

	if (!$INSERT_VALUES1) {
		return $ERROR;
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	すららランキングステータス（ユニットクリアー数）情報削除
	$sql = "TRUNCATE TABLE status_clear_unit;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$cd->close();
		return $ERROR;
	}

	//	すららランキングステータス（ユニットクリアー数）情報追加
	if ($COLUMN_NAME1 && $INSERT_VALUES1) {
		$sql = "INSERT INTO status_clear_unit (".$COLUMN_NAME1.") VALUES ".$INSERT_VALUES1.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$cd->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE status_clear_unit;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;

}

//	すららランキングステータス（ユニットクリアー数）情報DB削除
function status_clear_unit_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	すららランキングステータス（ユニットクリアー数）情報削除
	$sql = "TRUNCATE TABLE status_clear_unit;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL TRUNCATE ERROR<br>$sql";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	サービス基本情報DBアップロード
function service_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	//	サービス基本情報
	$sql  = "SELECT * FROM service;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'service', $INSERT_NAME, $INSERT_VALUE);
	}
	//	サービス・コース基本情報
	$sql  = "SELECT * FROM service_course_list;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'service_course_list', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	//	サービス基本情報
	$sql = "TRUNCATE TABLE service;";
	$DELETE_SQL['service'] = $sql;
	//	サービス・コース情報
	$sql = "TRUNCATE TABLE service_course_list;";
	$DELETE_SQL['service_course_list'] = $sql;


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

	//	検証バッチDBデーター削除
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

	//	外部キー制約セット
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
	$sql = "OPTIMIZE TABLE service;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	$sql = "OPTIMIZE TABLE service_course_list;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	サービス基本情報DB削除
function service_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	//	サービス基本情報
	$sql = "TRUNCATE TABLE service;";
	$DELETE_SQL['service'] = $sql;
	//	サービス・コース情報
	$sql = "TRUNCATE TABLE service_course_list;";
	$DELETE_SQL['service_course_list'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約セット
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	翻訳情報DBアップロード
function trans_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	//	更新情報クエリー
	//	翻訳ワード＆ページ関連情報
	$sql  = "SELECT * FROM trans_links;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'trans_links', $INSERT_NAME, $INSERT_VALUE);
	}
	//	翻訳ページ管理情報
	$sql  = "SELECT * FROM trans_pages;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'trans_pages', $INSERT_NAME, $INSERT_VALUE);
	}
	//	翻訳ワード管理情報
	$sql  = "SELECT * FROM trans_words;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'trans_words', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証DBデーター削除クエリー
	//	翻訳ワード＆ページ関連情報
	$sql = "TRUNCATE TABLE trans_links;";
	$DELETE_SQL['trans_links'] = $sql;
	//	翻訳ページ管理情報
	$sql = "TRUNCATE TABLE trans_pages;";
	$DELETE_SQL['trans_pages'] = $sql;
	//	翻訳ワード管理情報
	$sql = "TRUNCATE TABLE trans_words;";
	$DELETE_SQL['trans_words'] = $sql;


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

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//	外部キー制約セット
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
	//	翻訳ワード＆ページ関連情報
	$sql = "OPTIMIZE TABLE trans_links;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	//	翻訳ページ管理情報
	$sql = "OPTIMIZE TABLE trans_pages;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}
	//	翻訳ワード管理情報
	$sql = "OPTIMIZE TABLE trans_words;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	翻訳情報DB削除
function trans_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	//	翻訳ワード＆ページ関連情報
	$sql = "TRUNCATE TABLE trans_links;";
	$DELETE_SQL['trans_links'] = $sql;
	//	翻訳ページ管理情報
	$sql = "TRUNCATE TABLE trans_pages;";
	$DELETE_SQL['trans_pages'] = $sql;
	//	翻訳ワード管理情報
	$sql = "TRUNCATE TABLE trans_words;";
	$DELETE_SQL['trans_words'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約セット
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;

}



//	NGワード情報DBアップロード
function ng_word_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM ng_word;";
	if ($result = $bat_cd->exec_query($sql)) {
		make_insert_query($result, 'ng_word', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql = "TRUNCATE TABLE ng_word;";
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

	//	検証バッチDBデーター削除
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
	$sql = "OPTIMIZE TABLE ng_word;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;

}

//	NGワード情報DB削除
function ng_word_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "TRUNCATE TABLE ng_word;";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;

}



//	ランキングマスターデフォルト情報DBアップロード
function ms_ranking_point_def_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM ms_ranking_point".
			" WHERE course_num='0';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_ranking_point', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ms_ranking_point".
			" WHERE course_num='0';";
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

	//	アップ先DBデーター削除
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
	$sql = "OPTIMIZE TABLE ms_ranking_point;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ランキングマスターデフォルト情報DB削除
function ms_ranking_point_def_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM ms_ranking_point".
			" WHERE course_num='0';";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	ドリル問題すらら単元情報DBアップロード
function course_problem_lms_unit_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM problem_lms_unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'problem_lms_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM problem_lms_unit".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
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

	//	検証バッチDBデーター削除
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
	$sql = "OPTIMIZE TABLE problem_lms_unit;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ドリル問題すらら単元情報DB削除
function course_problem_lms_unit_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM problem_lms_unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	ランキングマスター情報DBアップロード
function course_ms_ranking_point_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM ms_ranking_point".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'ms_ranking_point', $INSERT_NAME, $INSERT_VALUE);
	}

	//	アップ先DBデーター削除クエリー
	$sql  = "DELETE FROM ms_ranking_point".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
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

	//	アップ先DBデーター削除
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
	$sql = "OPTIMIZE TABLE ms_ranking_point;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ランキングマスター情報DB削除
function course_ms_ranking_point_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM ms_ranking_point".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	メニューステータス情報DBアップロード
function course_menu_status_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	$sql  = "SELECT * FROM menu_status".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'menu_status', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM menu_status".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
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

	//	検証バッチDBデーター削除
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
	$sql = "OPTIMIZE TABLE ranking_point;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	更新先DB切断
	$cd->close();

	return $ERROR;
}

//	メニューステータス情報DB削除
function course_menu_status_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql  = "DELETE FROM menu_status".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;

}



//	SNS利用設定デフォルト情報DBアップロード
function sns_switch_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
echo $update_server_name."\n";
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	//	更新情報クエリー
	//	SNS利用設定デフォルト情報
	$sql  = "SELECT * FROM sns_switch WHERE school_id='default';";
echo $sql."\n";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'sns_switch', $INSERT_NAME, $INSERT_VALUE);
	}

	//	更新先DBデーター削除クエリー
	//	SNS利用設定デフォルト情報
	$sql = "DELETE FROM sns_switch WHERE school_id='default';";
	$DELETE_SQL['sns_switch'] = $sql;

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

	//	更新先DBデーター削除
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

	//	更新先DBデーター追加
	if ($INSERT_NAME && $INSERT_VALUE) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				$ERROR = insert_data($cd, $table_name, $insert_name, $INSERT_VALUE[$table_name]);
				if ($ERROR) { return $ERROR; }
			}
		}
	}

	//	外部キー制約セット
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
	$sql = "OPTIMIZE TABLE sns_switch;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	更新先DB切断
	$cd->close();

	return $ERROR;
}

//	SNS利用設定デフォルト情報DB削除
function sns_switch_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$bat_cd->close();
		return $ERROR;
	}

	//	更新元DBデーター削除
	//	SNS利用設定デフォルト情報
	$sql = "DELETE FROM sns_switch WHERE school_id='default';";
	$DELETE_SQL['sns_switch'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約セット
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}



//	ワード情報DBアップロード
function course_english_word_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	english_word
	$sql  = "SELECT * FROM english_word".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'english_word', $INSERT_NAME, $INSERT_VALUE);
	}

	//	english_word_problem
	$sql  = "SELECT * FROM english_word_problem".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'english_word_problem', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	//	english_word
	$sql  = "DELETE FROM english_word".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	english_word_problem
	$sql  = "DELETE FROM english_word_problem".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
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
	//	english_word
	$sql = "OPTIMIZE TABLE english_word;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	english_word_problem
	$sql = "OPTIMIZE TABLE english_word_problem;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DB切断
	$cd->close();

	return $ERROR;
}

//	ワード情報webアップロード
function course_english_word_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	word_img
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_WORD_IMG_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_WORD_IMG_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	if (file_exists($local_dir)) {
		exec("$command1", $LIST);
		exec("$command2", $LIST);
	}

	//	word_voice
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	if (file_exists($local_dir)) {
		exec("$command1", $LIST);
		exec("$command2", $LIST);
	}

	return $ERROR;
}

//	ワード情報DB削除
function course_english_word_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除

	//	english_word
	$sql = "DELETE FROM english_word WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	//	english_word_problem
	$sql = "DELETE FROM english_word_problem WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;
}

//	ワード情報web削除
function course_english_word_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_course_num = $argv[3];

	//	word_img
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_WORD_IMG_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	//	word_voice
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_WORD_VOICE_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}



//	ワンポイント情報DBアップロード
function course_one_point_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	//	one_point
	$sql  = "SELECT * FROM one_point".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'one_point', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	//	one_point
	$sql  = "DELETE FROM one_point".
			" WHERE course_num='".$cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
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
	//	one_point
	$sql = "OPTIMIZE TABLE one_point;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	更新先DB切断
	$cd->close();

	return $ERROR;
}



//	ワンポイント情報webアップロード
function course_one_point_web($btw_server_name,$update_server_name,$argv) {

	global $L_DB;

	//	ファイルアップ
	$update_type = $argv[1];
	$update_course_num = $argv[3];

	//	point_img
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_POINT_IMG_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_POINT_IMG_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	//	point_voice
	if ($update_type == 1) {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR.$update_course_num;
		$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR;
		$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR;
	} else {
		$local_dir	= KBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR.$update_course_num;
		$mk_dir		= BASE_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR;
		$remote_dir	= BASE_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR;
	}

	// ローカルのリリースコンテンツをリモートに反映
	$command1 = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
	$command2 = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;

	exec("$command1", $LIST);
	exec("$command2", $LIST);

	return $ERROR;
}

//	ワンポイント情報DB削除
function course_one_point_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	//	one_point
	$sql = "DELETE FROM one_point WHERE course_num = '".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//	ワンポイント情報web削除
function course_one_point_web_del($update_server_name,$argv) {

	//	ファイルアップ
	$update_course_num = $argv[3];

	//	point_img
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_POINT_IMG_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1  = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	//	point_voice
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_POINT_VOICE_DIR.$update_course_num;

	// ローカルのリリースコンテンツを削除
	$command1 = "rm -rf ".$remote_dir;

	exec("$command1", $LIST);

	return $ERROR;
}


//	ゲーム収集要素情報DBアップロード
function course_game_collection_db($update_server_name,$argv) {

	// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	更新先DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	game_collection
	$sql  = "SELECT * FROM game_collection".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'game_collection', $INSERT_NAME, $INSERT_VALUE);
	}

	//	game_collection_unit
	$sql  = "SELECT * FROM game_collection_unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'game_collection_unit', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	//	game_collection
	$sql  = "DELETE FROM game_collection".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	game_collection_unit
	$sql  = "DELETE FROM game_collection_unit".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
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
	//	game_collection
	$sql = "OPTIMIZE TABLE game_collection;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	game_collection_unit
	$sql = "OPTIMIZE TABLE game_collection_unit;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DB切断
	$cd->close();

	return $ERROR;
}

//	ゲーム収集要素情報DB削除
function course_game_collection_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除

	//	game_collection
	$sql = "DELETE FROM game_collection WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	//	game_collection_unit
	$sql = "DELETE FROM game_collection_unit WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}

//add start kimura 2019/07/16 生徒画面TOP改修 {{{
//メダル設定情報DBアップロード
/**
 * course_medal_setting_db
 *
 * @param  string $update_server_name
 * @param  array  $argv
 * @return array  $ERROR
 */
function course_medal_setting_db($update_server_name,$argv) {
// DB情報（更新先のＤＢ接続情報取得）
	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	更新先DB接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) { return $ERROR; }

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	unit_list_medal_setting
	$sql  = "SELECT * FROM unit_list_medal_setting".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'unit_list_medal_setting', $INSERT_NAME, $INSERT_VALUE);
	}

	//	DBデーター削除クエリー
	//	unit_list_medal_setting
	$sql  = "DELETE FROM unit_list_medal_setting".
			" WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DBデーター削除
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

	//	アップ先DBデーター追加
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
	//	unit_list_medal_setting
	$sql = "OPTIMIZE TABLE unit_list_medal_setting;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	アップ先DB切断
	$cd->close();

	return $ERROR;
}


//
/**ユニット一覧メダル情報DB削除
 *
 * @param  string $update_server_name
 * @param  array  $argv
 * @return array  $ERROR
 */
function course_medal_setting_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	$update_course_num = $argv[3];

	if ($update_course_num < 1) {
		$ERROR[] = "削除するコース情報が取得できません。";
		return $ERROR;
	}

	//	検証バッチDBデーター削除

	//	unit_list_medal_setting
	$sql = "DELETE FROM unit_list_medal_setting WHERE course_num='".$bat_cd->real_escape($update_course_num)."';";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL DELETE ERROR<br>$sql";
	}

	return $ERROR;

}
//add end   kimura 2019/07/16 生徒画面TOP改修 }}}

//	ドリル手書きデフォルト情報DBアップロード
// add function hasegawa 2018/05/11 手書きV2対応
function drill_tegaki_default_db($update_server_name,$argv) {

	global $L_DB;

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	検証分散DB１～８へ接続
	$cd = new connect_db();
	$cd->set_db($L_DB[$update_server_name]);
	$ERROR = $cd->set_connect_db();
	if ($ERROR) {return $ERROR;}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	//	サービス基本情報
	$sql  = "SELECT * FROM drill_tegaki_default;";
	if ($result = $bat_cd->query($sql)) {
		make_insert_query($result, 'drill_tegaki_default', $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	//	サービス基本情報
	$sql = "TRUNCATE TABLE drill_tegaki_default;";
	$DELETE_SQL['drill_tegaki_default'] = $sql;


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

	//	検証バッチDBデーター削除
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

	//	外部キー制約セット
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
	$sql = "OPTIMIZE TABLE drill_tegaki_default;";
	if (!$cd->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
		$cd->close();
		return $ERROR;
	}

	//	検証バッチDB切断
	$cd->close();

	return $ERROR;
}

//	ドリル手書きデフォルト情報DB削除
// add function hasegawa 2018/05/11 手書きV2対応
function drill_tegaki_default_db_del($update_server_name,$argv) {

	// 検証バッチDB接続オブジェクト
	$bat_cd = $GLOBALS['bat_cd'];

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	//	ドリル手書きデフォルト基本情報
	$sql = "TRUNCATE TABLE drill_tegaki_default;";
	$DELETE_SQL['drill_tegaki_default'] = $sql;

	if ($DELETE_SQL) {
		foreach ($DELETE_SQL AS $sql) {
			if (!$bat_cd->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
			}
		}
	}

	//	外部キー制約セット
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$bat_cd->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$bat_cd->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$bat_cd->close();
		return $ERROR;
	}

	return $ERROR;
}


// add start hirose 2018/10/10 commonフォルダアップ機能追加
//	体系図共通部品情報webアップロード
function system_chart_common_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_dir = $argv[3]; //2階層目までのディレクトリ情報
	$update_dir_remote = $argv[4]; //1階層目までのディレクトリ情報
	$file_type = $argv[5]; //ファイルか、フォルダか

	if($update_dir){

		//	学習FLASHファイル
		if ($update_type == 1) {
			$remove_dir	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$remove_dir	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$local_dir	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir_remote;
			//$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir_remote;
		} else {
			$remove_dir	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$remove_dir	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$local_dir	= KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
			//$mk_dir		= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir_remote;
			//$remote_dir	= BASE_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir_remote;
		}

		if($file_type == 'f'){
			$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$remote_dir;
			$command2  = "scp -p ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;
	//print $command1.'<br>';
	//print $command2.'<br><br>';
			exec("$command1",$LIST);
			exec("$command2",$LIST);
		}elseif($file_type == 'd'){
			// ローカルのリリースコンテンツをリモートに反映
			$remove_command  = "ssh suralacore01@".$update_server_name." rm -rf ".$remove_dir;
			$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
			$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;


	//print $remove_command.'<br>';
	//print $command1.'<br>';
	//print $command2.'<br><br>';

			// 反映先のレクチャーを削除してからローカルのレクチャーをアップする。
			exec("$remove_command",$LIST);
			exec("$command1",$LIST);
			exec("$command2",$LIST);
		}
	} else {
		$ERROR[] = "There is no hierarchy";
	}

	return $ERROR;
}

//	体系図共通部品情報web削除
function system_chart_common_web_del($update_server_name,$argv) {

	global $L_DB;

	//	ファイル削除
	$update_dir = $argv[3]; //2階層目までのディレクトリ情報

	if($update_dir){
		//	学習FLASHファイル
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common/".$update_dir;
		//$remote_dir = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."common_test/".$update_dir;

		// ローカルのリリースコンテンツを削除
		$command1  = "rm -rf ".$remote_dir;

//print $command1.'<br>';
		exec("$command1",$LIST);
	} else {
		$ERROR[] = "There is no hierarchy";
	}

	return $ERROR;
}


//	レクチャー共通部品情報webアップロード
function lecture_common_web($btw_server_name,$update_server_name,$argv) {

	//	ファイルアップ
	$update_type = $argv[1];
	$update_dir = $argv[3]; //2階層目までのディレクトリ情報
	$update_dir_remote = $argv[4]; //1階層目までのディレクトリ情報
	$file_type = $argv[5]; //ファイルか、フォルダか

	//	学習FLASHファイル
	if($update_dir){
		if ($update_type == 1) {
			$remove_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$remove_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$mk_dir		= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir_remote;
			//$remote_dir	= HBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir_remote;
		} else {
			$remove_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$remove_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$local_dir	= KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$mk_dir		= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
			//$mk_dir		= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$remote_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir_remote;
			//$remote_dir	= BASE_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir_remote;
		}

		if($file_type == 'f'){
			$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$remote_dir;
			$command2  = "scp -p ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;
			exec("$command1",$LIST);
			exec("$command2",$LIST);
		}elseif($file_type == 'd'){
			// ローカルのリリースコンテンツをリモートに反映
			$remove_command  = "ssh suralacore01@".$update_server_name." rm -rf ".$remove_dir;
			$command1  = "ssh suralacore01@".$update_server_name." mkdir -p ".$mk_dir;
			$command2  = "scp -rp ".$local_dir." suralacore01@".$update_server_name.":".$remote_dir;


	//print $remove_command;
	//print $command1;
	//print $command2;

			exec("$remove_command",$LIST);
			exec("$command1",$LIST);
			exec("$command2",$LIST);
		}
	} else {
		$ERROR[] = "There is no hierarchy";
	}

	return $ERROR;
}

//	レクチャー共通部品情報web削除
function lecture_common_web_del($update_server_name,$argv) {

	global $L_DB;

	//	ファイル削除
	$update_dir = $argv[3]; //2階層目までのディレクトリ情報

	if($update_dir){
		//	学習FLASHファイル
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
		//$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;

		// ローカルのリリースコンテンツを削除
		$command1  = "rm -rf ".$remote_dir;

//print $command1;
		exec("$command1",$LIST);
	} else {
		$ERROR[] = "There is no hierarchy";
	}

	return $ERROR;
}


// add end hirose 2018/10/10 commonフォルダアップ機能追加


//	エラー記録処理
function write_error($ERROR) {

	foreach ($ERROR AS $val) {
		$error .= date("Ymdhis")."\t".$val."\n";
	}

	$file = BASE_DIR."/_www/batch/error_CONTENTS.log";
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
