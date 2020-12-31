<?php
/**
 * すらら
 * ケイアイスター不動産 外部連携 設定情報 コアAPI用
 *
 * 2020/01/20 初期設定
 *
 * @author Azet
 */

//暗号化の情報 ケイアイスター不動産
define ("GENERAL_ID_HON","API9000003");			// 相互確認符丁文字列（本番用）
define ("GENERAL_ID_KEN","API9000003");			// 相互確認符丁文字列（検証用）
define ("GENERAL_PASS_HON_1","sBVqqLyIOwNTwm0E");	// 相互確認符丁パスフレーズ1（本番用）
define ("GENERAL_PASS_HON_2","9AuObU3TWthstJWF");	// 相互確認符丁パスフレーズ2（本番用）
define ("GENERAL_PASS_KEN_1","3G9MVmLG52gPWjOc");	// 相互確認符丁パスフレーズ1（検証用）
define ("GENERAL_PASS_KEN_2","wVKEl1Vgq0mFWFjT");	// 相互確認符丁パスフレーズ2（検証用）

//echo "*_chk_server_env=".$_chk_server_env."*<br>";
//host_name の変更(オプション) ※ログイン時のホスト名を、必要ならば環境別に指定します。
global $_chk_server_env, $protocol, $http_host;
//if ($_chk_server_env == "develop") { $http_host = ""; }
if ($_chk_server_env == "staging") { 
	$protocol = "https://";
	$http_host = "lms.stg.surala.jp";
}
//if ($_chk_server_env == "product") { $http_host = ""; }

//SSO他、各種URL
function set_urls() {
	global $protocol, $http_host;
	define ("GENERAL_LOGIN_ERROR_URL",$protocol.$http_host."/ext/keiai_login_error.htm");	// ログインエラー遷移先URL
	define ("GENERAL_LOGIN_URL",$protocol.$http_host."/ext/ext_login.php");		// ログイン遷移先URL		//add okabe 2020/01/20 外部連携ケイアイスター様対応
	define ("GENERAL_TEACHER_URL","/teacher/teacher.php");						// 先生遷移先URL
	define ("GENERAL_STUDENT_URL","/student/index.php");							// 生徒遷移先URL
	define ("GENERAL_TEACHER_TIMEOUT_URL",$protocol.$http_host."/ext/keiai_teacher_timeout.htm");	// 先生タイムアウト遷移先URL
	define ("GENERAL_TEACHER_LOGOUT_URL", $protocol.$http_host."/ext/keiai_teacher_logout.htm");	// 先生ログアウト遷移先URL
}


//■連携区分 は、config_base.php に記述

//連携区分値は、school_id から調べて、(特定の)変数に保持する。

//
Class GaibuRenkeiParametersCls {

	//受信パラメータ設定	//input
	static $api_in_params = array(
		"core_sso_op.php" => array(
			//論理名		=>	array(0実引数名		1物理名				2必須指定	3必須グループ番号指定	4型	5桁数	6チェックパターン	7デフォルト値	8みなしエラーコード	9備考
			"school_id"			=>	array("school_id",		"校舎ID",			1,	0,	"text",	10,	"NN",	"",	"INVALID_SCHOOLID",	""),	//★
			"student_id"		=>	array("student_id",		"ユーザーID(生徒)",	2,	1,	"text",	8,	"NN",	"",	"INVALID_USERID",	""),	//生徒ログインの時は必須
			"teacher_id"		=>	array("teacher_id",		"ユーザーID(先生)",	2,	1,	"text",	10,	"NN",	"",	"INVALID_USERID",	""),	//先生ログインの時は必須
			"identifyconfirm"	=>	array("identifyconfirm","相互確認符丁",		1,	0,	"text",	0,	"NN",	"",	"INVALID_IDENTIFY",	""),	//暗号化
				//※必須指定(1:必須、0:任意、2:必須グループで同じ値のいずれか１つが指定必要。
		),
		"core_sso.php" => array(
				//論理名		=>	array(0実引数名		1物理名				2必須指定	3必須グループ番号指定	4型	5桁数	6エラーコード	7備考
			"op"				=>	array("op",				"ワンタイムパスワード",	1,	0,	"text",	0,	"NN",	"",	"",	""),
			"identifyconfirm"	=>	array("identifyconfirm","相互確認符丁",			1,	0,	"text",	0,	"NN",	"",	"",	""),	//暗号化
		),
		/*	//エントリーAPI用は、/ent/SuralaEntry/Apl/extension/config_keiaistar.php に記載
		"entry_student.php" => array(
		"cans_student.php" => array(
		"abs_student.php" => array(
		"res_student.php" => array(
		他
		*/
	);

	//リプライパラメータ設定	//output
	static $api_out_params = array(
		"core_sso_op.php" => array(
				//論理名			物理名			必須指定	必須グループ番号指定	型	桁数	備考
			"op"		=>	array("ワンタイムパスワード",	1,		0,		"text",	0,	""),
			"result"	=>	array("結果コード",				1,		0,		"text",	0,	""),	//0:成功 0以外:失敗時のエラーコード
		),
		"core_sso.php" => array(
				//論理名			物理名			必須指定	必須グループ番号指定	型	桁数	備考
			"redirecturl"	=>	array("リダイレクト先",		1,		0,		"text",	0,	""),
		),
		/*	//エントリーAPI用は、/ent/SuralaEntry/Apl/extension/config_keiaistar.php に記載
		"entry_student.php" => array(
		"cans_student.php" => array(
		"abs_student.php" => array(
		"res_student.php" => array(
		他
		*/
	);


	//判定エラーコード設定	//error
	static $api_error_params = array(
		"core_sso_op.php" => array(
			//ネーム					モード	リターンコード
			"SUCCESS"			=>	array("-",		0	),	//正常
			"INVALID_IDENTIFY"	=>	array("-",		1	),	//相互確認符丁不正
			"INVALID_SCHOOLID"	=>	array("-",		10	),	//校舎ID不正：パラメータ未設定または存在しない校舎ID
			"INVALID_USERID"	=>	array("-",		20	),	//ユーザ名ID不正：パラメータ未設定または存在しない生徒IDまたは先生ID
			"DB_ERROR"			=>	array("-",		100	),	//DB接続・更新エラー
		),
		"core_sso.php" => array(
		),
		"core_group.php" => array(
			//ネーム					モード	リターンコード
			"SUCCESS"			=>	array("-",		0	),	//正常	登録/更新/削除
			"INVALID_IDENTIFY"	=>	array("-",		1	),	//相互確認符丁不正	登録/更新/削除
			"INVALID_SCHOOLID"	=>	array("-",		10	),	//校舎ID不正：パラメータ未設定または存在しない校舎ID	登録/更新/削除
			"INVALID_GROUPID"	=>	array("-",		20	),	//グループ名ID不正：パラメータ未設定または存在しないグループID	更新/削除
			"INVALID_GROUPNAME"	=>	array("-",		30	),	//クラス名(グループ名)未設定	更新
			"INVALID_EXECMODE"	=>	array("-",		40	),	//処理モード不正	登録/更新/削除
			"DB_ERROR"			=>	array("-",		100	),	//DB接続・更新エラー
		),
		"core_group_member.php" => array(
			//ネーム					モード	リターンコード
			"SUCCESS"			=>	array("-",		0	),	//正常
			"INVALID_IDENTIFY"	=>	array("-",		1	),	//相互確認符丁不正
			"INVALID_SCHOOLID"	=>	array("-",		10	),	//校舎ID不正：パラメータ未設定または存在しない校舎ID
			"INVALID_GROUPID"	=>	array("-",		20	),	//グループ名ID不正：パラメータ未設定または存在しないグループID
			"INVALID_USERID"	=>	array("-",		30	),	//ユーザID不正：パラメータ未設定または存在しない生徒ID
			"INVALID_LIMITOVER"	=>	array("-",		40	),	//生徒数上限値オーバー
			"DB_ERROR"			=>	array("-",		100	),	//DB接続・更新エラー
		),
		/*	//エントリーAPI用は、/ent/SuralaEntry/Apl/extension/config_chieru.php に記載
		"entry_student.php" => array(
		"entry_teacher.php" => array(
		"cans_student.php" => array(
		他
		*/ //エントリーAPI用は、/ent/SuralaEntry/Apl/extension/config_chieru.php に記載
	);


	//成績 送信先URL
	static $send_result_params = array(
		"unit"    => array(
						0 => array("inner_url" => array( 		//0: 内部の送信処理URL
									"develop" => "/ext/send_result_data.php",
									"staging" => "/ext/send_result_data.php",
									"product" => "/ext/send_result_data.php",	//TODO
									),
								"outer_url" => array(		//1: 外部接続先URL
									"develop" => "http://10.3.11.100/entry/support/unit_history.php", 
									"staging" => "https://dev2.kaila-dev.linkedbrain.jp/ext/surala/unitHistory", //ケイアイスター不動産様staging
								//	"staging" => "http://10.2.11.10/entry/support/unit_history.php", //for test
									"product" => "http://10.3.11.100/entry/support/unit_history.php", 	//TODO
									),
								"parameters" => array( 		//2: 送信データの項目情報
									"student_id"		=> array("ID",				"生徒ID",			1,	"text",		8	,	''),
									"subject"			=> array("SUBJECT",			"教科名",			1,	"text",		255,	'コース名'),
									"unit1"				=> array("UNIT1",			"単元(1)",			1,	"text",		255,	'ステージ名'),
									"unit2"				=> array("UNIT2",			"単元(2)",			1,	"text",		255,	'レッスン名'),
									"unit3"				=> array("UNIT3",			"単元(3)",			1,	"text",		255,	'ユニット名'),
									"unit_name"			=> array("UNIT_NAME",		"学習内容",			1,	"text",		255,	'ユニット単元名'),
									"study_count"		=> array("STUDY_COUNT",		"学習回数",			1,	"number",	10,		'同一単元を学習した時の回数'),
									"study_time"		=> array("STUDY_TIME",		"総学習時間",		1,	"number",	10,	'ユニットクリア時間（秒）'),
									"correct_rate"		=> array("CORRECT_RATE",	"正解率",			1,	"number",	10,	'端数切捨て'),
									"timeup_count"		=> array("TIMEUP_COUNT",	"タイムアップ回数",	1,	"number",	10,	'ドリルギブアップ回数'),
									"study_date"		=> array("STUDY_DATE",		"学習日",			1,	"text",		8,	'YYYYMMDD'),
									"study_date_time"	=> array("STUDY_DATE_TIME",	"学習時",			1,	"text",		6,	'HHmmSS'),
									),
								"add_params" => ""),			//3: 付加パラメータ
						1 => array(
								"subject" => "unit成績送信できませんでした。",
								"body" => "unit成績送信できなかったデータがあります。\nFooter\n",
								"csv_file_path"	=> "send_error/",
								"csv_file_prefix"	=> "unit_",
								"csv_order" => array(
									0	=> "[DATETIME]",	//固定指示
									1	=> "ID",
									2	=> "SUBJECT",
									3	=> "UNIT1",
									4	=> "UNIT2",
									5	=> "UNIT3",
									6	=> "UNIT_NAME",
									7	=> "STUDY_COUNT",
									8	=> "STUDY_TIME",
									9	=> "CORRECT_RATE",
									10	=> "TIMEUP_COUNT",
									11	=> "STUDY_DATE",
									12	=> "STUDY_DATE_TIME",
									13	=> "[RESPONSE_TEXT]",	//固定指示
								),
								"to_address" => array(
									"develop" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "dummy_inquiry@catchon.jp",
										),
									"staging" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "inquiry@catchon.jp",
										),
									"product" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "inquiry@catchon.jp",
										)
								)
							)
					),
		"test"    => array(
						0 => array("inner_url" => array( 		//0: 内部の送信処理URL
									"develop" => "/ext/send_result_data.php",
									"staging" => "/ext/send_result_data.php",
									"product" => "/ext/send_result_data.php",	//TODO
									),
								"outer_url" => array(		//1: 外部接続先URL
									"develop" => "http://10.3.11.100/entry/support/test_history.php", 
									"staging" => "https://dev2.kaila-dev.linkedbrain.jp/ext/surala/testHistory",  //ケイアイスター不動産様staging
								//	"staging" => "http://10.2.11.10/entry/support/test_history.php", //for test
									"product" => "http://10.3.11.100/entry/support/test_history.php", 	//TODO
									),
								"parameters" => array( 		//2: 送信データの項目情報
									"student_id"		=> array("ID",				"生徒ID",				1,	"text",		8,	''),
									"test_no"			=> array("TEST_NO",			"テストNo",				1,	"number",	7,	'先頭0詰め'),
									"test_date"			=> array("TEST_DATE",		"試験日",				1,	"text",		8,	'YYYYMMDD'),
									"test_date_time"	=> array("TEST_DATE_TIME",	"試験時",				1,	"text",		6,	'HHmmSS'),
									"subject"			=> array("SUBJECT",			"教科名",				1,	"text",		255,'教科名'),
									"score"				=> array("SCORE",			"得点",					1,	"number",	10,	'得点が設定されていない場合は正解率（端数切捨て）'),
									"study_time"		=> array("STUDY_TIME",		"解答時間",				1,	"number",	10,	'テスト時間（秒）'),
									"review_unit"		=> array("REVIEW_UNIT",		"科目別補強ポイント",	1,	"text",		255,'ユニットキー。複数存在する場合は「:」コロンで区切る'),
									),
								"add_params" => ""),			//3: 付加パラメータ
						1 => array(
								"subject" => "test成績送信できませんでした。",
								"body" => "test成績送信できなかったデータがあります。\nFooter\n",
								"csv_file_path"	=> "send_error/",
								"csv_file_prefix"	=> "test_",
								"csv_order" => array(
									0	=> "[DATETIME]",	//固定指示
									1	=> "ID",
									2	=> "TEST_NO",
									3	=> "TEST_DATE",
									4	=> "TEST_DATE_TIME",
									5	=> "SUBJECT",
									6	=> "SCORE",
									7	=> "STUDY_TIME",
									8	=> "REVIEW_UNIT",
									9	=> "[RESPONSE_TEXT]",	//固定指示
								),
								"to_address" => array(
									"develop" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "dummy_inquiry@catchon.jp",
										),
									"staging" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "inquiry@catchon.jp",
										),
									"product" => array(
										"mail_to"	=> "okabe@azet.jp",
										"cc"	=> "",
										"mail_from"	=> "inquiry@catchon.jp",
										)
								)
							)
					),
	);

}

?>