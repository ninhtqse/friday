<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 外部連携 再送信バッチ
 *
 * @author Azet
 * 2020/02/28 okabe
 */


error_reporting(E_ALL & ~E_NOTICE);
//$BASE_DIR = "/data/home";
//$INCLUDE_BASE_DIR = "/data/home";
$INCLUDE_BASE_DIR = "/data/bat";	//@@@@@
//define("BASE_DIR", $BASE_DIR);
define("INCLUDE_BASE_DIR", $INCLUDE_BASE_DIR);
define("_WWW_DIR", "/data/bat/gib_rnki/");	//@@@@@

//	DBリスト取得
//include(INCLUDE_BASE_DIR."/_www/batch/db_list.php");
//include(INCLUDE_BASE_DIR."/_www/batch/db_connect.php");
include(INCLUDE_BASE_DIR."/db_connect.php");	//@@@@@

//環境別のDB接続
$_chk_server_env = "xxxxx";
switch ( getHostByName(getHostName()) ){

// 開発環境
case "10.3.11.100":
	//	総合サーバテスト用
	$L_DB['srlctd03SOGO']['NAME'] = '開発コアDB総合サーバテスト用';
	$L_DB['srlctd03SOGO']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlctd03SOGO']['DBUSER'] = 'axxkc01';
	$L_DB['srlctd03SOGO']['DBPASSWD'] = 'kc_01axx';
	$L_DB['srlctd03SOGO']['DBNAME'] = 'CONTENTS160';
	$L_DB['srlctd03SOGO']['DBPORT'] = '3306';
	$CON_DB = $L_DB['srlctd03SOGO'];
	$_chk_server_env = "develop";
	break;

// 検証(検証バッチ ⇒ 検証総合DB)
case "10.2.41.10":		// ⇒web coreではなくバッチサーバー
	//	検証分散DB#7
	$L_DB['srlctd0307']['NAME'] = '検証分散DB#7';
	$L_DB['srlctd0307']['DBHOST'] = 'srnstgcoredb7.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlctd0307']['DBUSER'] = 'axxsc01';
	$L_DB['srlctd0307']['DBPASSWD'] = 'sc_01axx';
	$L_DB['srlctd0307']['DBNAME'] = 'SRLCH07';
	$L_DB['srlctd0307']['DBPORT'] = '3306';
	$CON_DB = $L_DB['srlctd0307'];
	$_chk_server_env = "staging";
	break;

// 本番(本番バッチ ⇒ 本番総合DB)
case preg_match('/^10\.1\./',$_SERVER['SERVER_ADDR'])===1:		// ⇒web coreではなくバッチサーバー
	//	本番分散DB#7
	$L_DB['srlchd08']['NAME'] = '本番分散DB#7';
	$L_DB['srlchd08']['DBHOST'] = 'srnprodcoredb7.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlchd08']['DBUSER'] = 'srlhc01';
	$L_DB['srlchd08']['DBPASSWD'] = 'hc_01srl';
	$L_DB['srlchd08']['DBNAME'] = 'SRLCH07';
	$L_DB['srlchd08']['DBPORT'] = '3306';
	$CON_DB = $L_DB['srlchd08'];
	$_chk_server_env = "product";
	break;

default:
	unset($CON_DB);
	break;
}

//test
/*
echo getHostByName(getHostName())."<hr>";
if (isset($CON_DB)) {
	echo "<pre>";print_r($CON_DB);echo "</pre>";
}
*/


// DB接続
$bat_cd = new connect_db();
$bat_cd->set_db($CON_DB);
$ERROR = $bat_cd->set_connect_db();
if ($ERROR) {
	echo "<pre>";print_r($ERROR);echo "</pre>";
	exit;
}


//echo "connect ok! ".$_chk_server_env."s<br>";

main($bat_cd, $_chk_server_env);		//所定時間以内のNGを再送信する。
post_process($bat_cd, $_chk_server_env);		//所定時間経過したものはメール通知して、CSV用ファイルにアペンド。

//echo $ret;


//	DB切断
$bat_cd->close();

exit;


//-----

// 所定時間以内のNGを再送信する。
function main($bat_cd, $_chk_server_env) {

	$now_date = new DateTime();
//echo $now_date->format('Y-m-d H:i:s')."\n";
	$range_datetime2 = $now_date->modify('-1 minutes')->format('Y-m-d H:i:s');
	$range_datetime1 = $now_date->modify('-12 hours')->format('Y-m-d H:i:s');

	//1分以上経過して12時間以内のデータを対象に、mk_flg=0 で、status が OK 以外のものをリストする。
	$sql = "SELECT * FROM ext_send_study ".
		" WHERE mk_flg = '0' ".
		" AND status != 'OK' ".
		" AND action_datetime > '".$range_datetime1."' ".
		" AND action_datetime < '".$range_datetime2."';";

	if ($result = $bat_cd->query($sql)) {
		$res_count = $bat_cd->num_rows($result);
	}
	if ($res_count > 0) {

		$cur_gib_rnki_kb = "xxx";

		while ($list = $bat_cd->fetch_assoc($result)) {

			$ext_send_study_num = $list['ext_send_study_num'];
			$gib_rnki_kb = $list['gib_rnki_kb'];
			$data_type = $list['data_type'];
			$send_data = unserialize($list['send_data']);
			$retry_status = $list['retry_status'];

			if ($retry_status != "OK") {	//すでに再送信して OK のものは除く

				$ng_flag = 0;	//１件ごとの設定ファイルデータが正しく扱えているか

				if ($cur_gib_rnki_kb != $gib_rnki_kb) {
					$cur_gib_rnki_kb = $gib_rnki_kb;
					//指定された外部接続の設定ファイルを読み込む
					
					require_once(_WWW_DIR.'base_api.class.php');
					unset($class_gib_base);
					$class_gib_base = new coreApiBase(_WWW_DIR);
					$gib_name = $class_gib_base->GRenkei_Code[$gib_rnki_kb];
					$class_gib_base->include_config($gib_name);
//echo "***gib_rnki_kb=".$gib_rnki_kb.", gib_name=".$gib_name.", _chk_server_env=".$_chk_server_env."*<br>";
//echo "<pre>";print_r($class_gib_base->send_result_params['unit']);echo "</pre><hr>";
//echo "<pre>";print_r($class_gib_base->api_params['api_in_params']);echo "</pre>";
//値が指示されていて、該当する外部連携にて成績送信が指定されている場合に実行する。
					if (!is_null($class_gib_base->send_result_params)) {
						$inc_file = _WWW_DIR.'gib_rnki_send_history.php';
						if (file_exists($inc_file)) {
							require_once($inc_file);
						} else {
							$ng_flag = 1;
						}
					} else {
						$ng_flag = 1;
					}
				}
//
				if ($ng_flag == 0) {	//１件ごとの設定ファイルデータが正しく扱えているか

					//指定レコードを、指定先urlへ送信する。
					$set_data = http_build_query($send_data);
					$url = $class_gib_base->send_result_params[$data_type][0]['outer_url'][$_chk_server_env];
//echo "ext_send_study_num=".$ext_send_study_num.", gib_rnki_kb=".$gib_rnki_kb.", ng_flag=".$ng_flag.", *inc_file=".$inc_file.", url=".$url."<br>";

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($ch, CURLOPT_TIMEOUT, 300);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $set_data);
					$str_result  = curl_exec($ch);	//@@@@@
//$str_result = "NG";
					curl_close($ch);

					//送信結果に応じてDBの再送ステータスを更新する
					$UPDATE_DATA = array();
					if (substr($str_result, 0, 2) == "OK") {
						$UPDATE_DATA['retry_status'] = "OK";
						$UPDATE_DATA['retry_response_text'] = "";
					} else {
						$UPDATE_DATA['retry_status'] = "NG";
						$UPDATE_DATA['retry_response_text'] = $str_result;
					}
					$UPDATE_DATA['retry_tts_id'] = "batch";
					$UPDATE_DATA['last_action_datetime'] = "now()";
					$where = " WHERE ext_send_study_num='".$ext_send_study_num."'";

//echo "*<pre>";print_r($UPDATE_DATA);echo "</pre>";
//echo "<pre>";print_r($where);echo "</pre><hr>";
					$ERROR = $bat_cd->update("ext_send_study", $UPDATE_DATA, $where);
				}
//
			
			}

//echo "<pre>";print_r($list);echo "</pre>";

		}
	}

	return;
}



// 所定時間経過したものはメール通知して、CSV用ファイルにアペンド。
function post_process($bat_cd, $_chk_server_env) {

	$now_date = new DateTime();
//echo $now_date->format('Y-m-d H:i:s')."\n";
	$range_datetime3 = $now_date->modify('-12 hours')->format('Y-m-d H:i:s');
//$range_datetime3 = $now_date->modify('-1 hours')->format('Y-m-d H:i:s');	//@@@@@

	//12時間を経過し、mk_flg=0 で、status が OK 以外のものをリストする。
	$sql = "SELECT * FROM ext_send_study ".
		" WHERE mk_flg = '0' ".
		" AND status != 'OK' ".
		" AND retry_status != 'OK' ".
		" AND create_datetime < '".$range_datetime3."';";
//echo $sql;

	if ($result = $bat_cd->query($sql)) {
		$res_count = $bat_cd->num_rows($result);
	}
	if ($res_count > 0) {

		$cur_gib_rnki_kb = "xxx";

		while ($list = $bat_cd->fetch_assoc($result)) {

			$ext_send_study_num = $list['ext_send_study_num'];
			$gib_rnki_kb = $list['gib_rnki_kb'];
			$data_type = $list['data_type'];
			$send_data = unserialize($list['send_data']);
			$create_datetime = $list['create_datetime'];
			$retry_status = $list['retry_status'];

			$retry_response_text = $list['retry_response_text'];
			$last_action_datetime = $list['last_action_datetime'];

			$ng_flag = 0;	//１件ごとの設定ファイルデータが正しく扱えているか

			if ($cur_gib_rnki_kb != $gib_rnki_kb) {
				$cur_gib_rnki_kb = $gib_rnki_kb;
				//指定された外部接続の設定ファイルを読み込む
				
				require_once(_WWW_DIR.'base_api.class.php');
				unset($class_gib_base);
				$class_gib_base = new coreApiBase(_WWW_DIR);
				$gib_name = $class_gib_base->GRenkei_Code[$gib_rnki_kb];
				$class_gib_base->include_config($gib_name);
//echo "***gib_rnki_kb=".$gib_rnki_kb.", gib_name=".$gib_name.", _chk_server_env=".$_chk_server_env."*<br>";
//echo "<pre>";print_r($class_gib_base->send_result_params['unit']);echo "</pre><hr>";
//echo "<pre>";print_r($class_gib_base->api_params['api_in_params']);echo "</pre>";
//値が指示されていて、該当する外部連携にて成績送信が指定されている場合に実行する。
				if (!is_null($class_gib_base->send_result_params)) {
					$inc_file = _WWW_DIR.'gib_rnki_send_history.php';
					if (file_exists($inc_file)) {
						require_once($inc_file);
					} else {
						$ng_flag = 1;
					}
				} else {
					$ng_flag = 1;
				}
			}

			if ($ng_flag == 0) {	//１件ごとの設定ファイルデータが正しく扱えているか

//echo "post_process: ext_send_study_num=".$ext_send_study_num.", ng_flag=".$ng_flag.", gib_rnki_kb=".$gib_rnki_kb.", create_datetime=".$create_datetime.", inc_file=".$inc_file.", zzz=".print_r($class_gib_base->send_result_params[$data_type][1], true)."\n";

				//メール送信（１件で１通送る） ※１通のメールに該当分を纏めて送信するならば要修正？
				$subject = $class_gib_base->send_result_params[$data_type][1]['subject'];

				$body = $class_gib_base->send_result_params[$data_type][1]['subject'];
				$body = str_replace("%%SEND_DATA%%", print_r($send_data, true), $body);
				$body = str_replace("%%RESPONSE_TEXT%%", $retry_response_text,  $body);
				$body = str_replace("%%LAST_DATETIME%%", $last_action_datetime, $body);

//				$mail_to = $class_gib_base->send_result_params[$data_type][1]['mail_to'];
//				$cc = $class_gib_base->send_result_params[$data_type][1]['cc'];
//				$mail_from = $class_gib_base->send_result_params[$data_type][1]['mail_from'];
				$mail_to = $class_gib_base->send_result_params[$data_type][1]['to_address'][$_chk_server_env]['mail_to'];
				$cc = $class_gib_base->send_result_params[$data_type][1]['to_address'][$_chk_server_env]['cc'];
				$mail_from = $class_gib_base->send_result_params[$data_type][1]['to_address'][$_chk_server_env]['mail_from'];

//echo "*".$subject.", ".$body.", ".$mail_to.", ".$cc.", ".$mail_from."\n";
				$mail_res = notify_mail($subject, $body, $mail_to, $cc, $mail_from);
//$mail_res = true;

				//csvファイルへの出力
				$file_path = $class_gib_base->send_result_params[$data_type][1]['csv_file_path'];
				$file_prefix = $class_gib_base->send_result_params[$data_type][1]['csv_file_prefix'];
				
				$now_date = new DateTime();
				$fname = $file_path.$gib_name."_".$file_prefix.$now_date->format('Ymd').".csv";

				$one_stream = "";
				foreach($class_gib_base->send_result_params[$data_type][1]['csv_order'] as $k => $v) {
					if ($v == "[DATETIME]") {
						$one_stream .= "\"".$last_action_datetime."\",";
					} else if ($v == "[RESPONSE_TEXT]") {
						$one_stream .= "\"".$retry_response_text."\",";
					} else {
						$one_data = str_replace("\"", "\"\"", $send_data[$v]);
						$one_stream .= "\"".$one_data."\",";
					}
				}

				$xfp = fopen($fname, "a");
				fwrite($xfp, mb_convert_encoding($one_stream, "SJIS", "UTF-8")."\n");
				fclose($xfp);
				chmod($fname, 0666);
//echo $fname."\n";
//echo $one_stream."\n";
//echo "<pre>";print_r($mail_res);echo "</pre>\n";	//@@@@@

				//ステータスの更新 (メール送信フラグと日時をセット、mk_flg をセット)
				$UPDATE_DATA = array();
				if ($mail_res) {
					$UPDATE_DATA['mail_notify_status'] = 1;	//正常送信
				} else {
					$UPDATE_DATA['mail_notify_status'] = 2;	//送信エラー
				}
				$UPDATE_DATA['mail_notify_tts_id'] = "batch";
				$UPDATE_DATA['mail_address'] = $mail_to;
				$UPDATE_DATA['mail_notify_datetime'] = "now()";

				$UPDATE_DATA['mk_flg'] = 1;
				$UPDATE_DATA['mk_tts_id'] = "batch";
				$UPDATE_DATA['mk_date'] = "now()";

				$where = " WHERE ext_send_study_num='".$ext_send_study_num."'";

//echo "*<pre>";print_r($UPDATE_DATA);echo "</pre>";
//echo "<pre>";print_r($where);echo "</pre><hr>";
				$ERROR = $bat_cd->update("ext_send_study", $UPDATE_DATA, $where);	//@@@@@
//echo "<pre>";print_r($ERROR);echo "</pre><hr>";

			}

		}
	}


}



function notify_mail($subject, $body, $mail_to, $cc, $mail_from) {

	// メール送信
	mb_language("ja");
	mb_internal_encoding("UTF-8");
	$headers =  "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";
	if ($cc) { $headers .= "Cc: ".$cc."\r\n"; }
	$headers .= "From: ".mb_encode_mimeheader(mb_convert_encoding($mail_from,'ISO-2022-JP-MS','UTF-8'))."\r\n";

	$subject = mb_convert_encoding($subject, "ISO-2022-JP-MS","UTF-8");
	$subject = mb_encode_mimeheader($subject);
	$body = mb_convert_encoding($body, 'ISO-2022-JP-MS', 'UTF-8');

//echo $mail_to."\n".$subject."\n".$body."\n".$headers."  '-f '".$mail_from."\n";
	return mail($mail_to, $subject, $body, $headers , '-f '.$mail_from);

}
