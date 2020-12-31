<?php

class coreApiBase
{
	//property declare
	public $api_params;
	public $post_params;
	public $path_prefix;
	public $send_result_params;	//成績送信先URL(_www/ext_lib/config_xxxxx.php 内にの値は記述。

	public $GRenkei_Code = array(
		1 => "chieru",
		2 => "classi",
		3 => "keiaistar",
		//4 => "?????",
	);

	public $mdl_code = array(
		"chieru" => 1,
		"classi" => 2,
		"keiai" => 3,	//API要求(in)での論理名「処理区分」の物理名「mdl」の設定文字列から区分コードを得るためのもの。
		//4 => "?????",
	);

	//初期設定
	function __construct($path){
		$this->path_prefix = $path;
	}


	//外部連携先別の、設定ファイルを読み込む
	public function include_config($gib_name){
		$req_path = $this->path_prefix."config_".$gib_name.".php";
		if (file_exists($req_path)) {
			require_once($req_path);
			set_urls();
		} else {
			return 1;
		}

		$this->api_params['api_in_params'] = GaibuRenkeiParametersCls::$api_in_params;
		$this->api_params['api_out_params'] = GaibuRenkeiParametersCls::$api_out_params;
		$this->api_params['api_error_params'] = GaibuRenkeiParametersCls::$api_error_params;

		if (isset(GaibuRenkeiParametersCls::$send_result_params)) {
			$this->send_result_params = GaibuRenkeiParametersCls::$send_result_params;
		}
		return 0;
	}


	//外部連携先別の、設定ファイル内情報を取り出す
	public function get_config_infos($config_suit, $config_name){
		if (array_key_exists($config_suit, $this->api_params)) {
			if (array_key_exists($config_name, $this->api_params[$config_suit])) {
				return $this->api_params[$config_suit][$config_name];
			} else {
				return;
			}
		} else {
			return;
		}
	}



	//POSTされたデータをメンバ変数に格納
	public function get_data_params($params) {

		if(is_array($params)) {
			foreach($params as $key => $val) {
				if (is_array($val)) {
					$this->post_params[$key] = $val;
				} else {
					$val = trim($val);
					$this->post_params[$key] = htmlspecialchars($val);
				}
			}
		}
	}



	/**
	 * 受信した 相互確認符丁 データのチェック
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author Azet
	 * @param number $school_id	校舎ID
	 * @param number $identifyconfirm	相互確認符丁 データ
	 * @return number $status		エラーコード
	 * @return number $gib_rnki_kb		外部連携区分コード
	 */
	public function check_identifyconfirm($school_id, $identifyconfirm) {
		$status = 0;

		// 相互確認符丁
		if (!$identifyconfirm) { return array('1', null); }		//modefied 2020/01/22

		$chieru_id = "";
		$pass1 = "";
		$pass2 = "";

		if($school_id) {
			// 分散DB接続
			if (!isset($GLOBALS['cdb'])) {
//echo "connect_db_general_api: ".$school_id."<br>";
				$ERROR = connect_db_general_api($school_id);		//dadd okabe 2020/01/20 外部連携ケイアイスター様対応
			}
			if ($ERROR) { return array(1, $ERROR); }
//$GLOBALS['cdb']="xxx";
			$cdb = $GLOBALS['cdb'];	//test del
//echo "<pre>";print_r($cdb);echo "</pre><hr>";
		}

		//school_id から外部連携区分のコードを調べる
		$gib_rnki_kb = get_gaibu_renkei_kubun($school_id);

		if (array_key_exists($gib_rnki_kb, $this->GRenkei_Code) === true) {
			//$GRenkei_Code = array(
			//	1 => "CHIERU",
			//	2 => "Classi",
			//	3 => "KEIAISTAR",
			//	//4 => "?????",
			//);
			require_once($this->path_prefix."config_".$this->GRenkei_Code[$gib_rnki_kb].".php");
			list($renkei_id, $pass1, $pass2) = $this->set_encrytpt_key_infos();

		} else {
			//指定された school_id の設定されている外部連携区分コード(gib_rnky_kb)が、不正または存在しないものの場合
			return array('1', null);	//modefied add 2020/01/22
		}

		// 受信した認証文字列を初期ベクターと暗号化列に分ける
		$method = 'AES-256-CBC';
		$ivLength = openssl_cipher_iv_length($method);
		$encrypted = base64_decode($identifyconfirm);
		$iv = substr($encrypted, 0, $ivLength);
		$data = substr($encrypted, $ivLength);

		// 復号化
		$decrypted = openssl_decrypt($data, $method, $pass2, true, $iv);

		// 利用日時:校舎ID:チエルID:ハッシュ文字列
		list($request_time, $school_id, $check_renkei_id, $hash) = explode(":",$decrypted);

		// 1分以内かチェック
		$check_date = date("YmdHis",strtotime("-1 min"));
		if (!$request_time || strtotime($check_date) > strtotime($request_time)) {
			return array('1', null);	//modefied add 2020/01/22
		}

		// DB接続&校舎ID存在チェック
		$count = 0;
		if($school_id) {
			// 分散DB接続 $cdb
			if($cdb) {
				$sql = "SELECT COUNT(school_id) AS count FROM ".T_SCHOOL." WHERE school_id='".$school_id."' AND mk_flg='0'";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
					$count = $list['count'];
				}
			}
		}
//echo "*check_renkei_id=".$check_renkei_id."*<br>";

		if ($count < 1) { return array('1', null); }	//modefied add 2020/01/22

		// チエルIDチェック
		if ($check_renkei_id != $renkei_id) {
			return array('1', null);	//modefied add 2020/01/22
		}

		return array($status, $gib_rnki_kb);	//modefied add 2020/01/22
	}



	// 設定ファイルに指定されているパラメータで、出力パートを組み立てる。
	public function output_parameters($api_name, $params) {
		$tmp_outpput = GaibuRenkeiParametersCls::$api_out_params;

		$in_loop_flag = 0;
		$skip_flag = 0;
		$output = array();
		foreach($tmp_outpput[$api_name] as $k => $v) {

			$loop_tag = preg_match('/^\((.*)\)$/', $k, $matches);
			if ($loop_tag === 1) {
				if ($matches[1] == "members") { $in_loop_flag = 1; $skip_flag = 1; }
				if ($matches[1] == "/members") { $in_loop_flag = 0; $skip_flag = 2; }
			}

			if ($in_loop_flag == 0) {
				if ($skip_flag == 2) {
					$skip_flag = 0;
				} else {
					$output[$k] = $params[$k];
				}

			} else {
				if ($skip_flag == 0) {
					$output["members"] = $params["members"];

				} else {
					$skip_flag = 0;
				}
			}

		}

		return $output;
	}



	/**
	 * 環境別の暗号化情報を返す
	 *
	 * AC:[S]生徒 UC1:[L99]その他.
	 *
	 * @author azet
	 * @return string 外部接続先別の暗号関係の情報
	 */
	public function set_encrytpt_key_infos() {

		$chieru_id = "";
		$pass1 = "";
		$pass2 = "";

		switch ($_SERVER['SERVER_ADDR']){
			// 開発・検証サーバー
			case "10.3.11.100":
			case "10.2.11.10":
			case preg_match('/^10\.2\./',$_SERVER['SERVER_ADDR'])===1:		// add oda 2020/10/15 検証環境の判断を変更
				$gib_rnki_id = GENERAL_ID_KEN;
				$pass1 = GENERAL_PASS_KEN_1;
				$pass2 = GENERAL_PASS_KEN_2;

				break;
			// 本番サーバー
			// 本番環境はAWSのautoscalingで切り替えるためIPが固定になりません。
			// このため別途サーバーIPの第二オクテットでprodか判断します。
			case preg_match('/^10\.1\./',$_SERVER['SERVER_ADDR'])===1:

				$gib_rnki_id = GENERAL_ID_HON;
				$pass1 = GENERAL_PASS_HON_1;
				$pass2 = GENERAL_PASS_HON_2;

				break;
			default:
				break;
		}

		return array($gib_rnki_id, $pass1, $pass2);
	}



	// postされたデータのチェック（設定ファイルから入力項目の有無、「いずれか１つ」等の付帯条件もチェックする）
	public function input_parameter_check($api_name) {		//$school_id, $execmode, $group_id, $group_name) {

		$res = 0;	//"SUCCESS";

		$tmp_input = GaibuRenkeiParametersCls::$api_in_params;
		//Class GaibuRenkeiParametersCls {
		//	//受信パラメータ設定	//input
		//	static $api_in_params = array(
		//		"core_sso_op.php" => array(
		$tmp_error = GaibuRenkeiParametersCls::$api_error_params;

		$error_total_ary = array();
		$error_detect = array();
		$max_items = 0;		//必須の指定値は１からだけど、その最大値
		$keta_over = 0;		//パラメータの長さ制限を超過しているとき、その必須グループ番号を格納

		//パラメータファイルで指定されたキーを１つずつ、post されたパラメータに存在するか確認
		$in_loop_flg = 0;	//(members)～(/members) グルーピング内であることを保持するフラグ

		foreach($tmp_input[$api_name] as $k => $v) {

			$loop_tag = preg_match('/^\((.*)\)$/', $v[0], $matches);

			if ($loop_tag == 1) {	//ループ開始または終了指示の場合。
				if (strtolower($matches[1]) == "members") {
					$in_loop_flg = 1;
				}
				if (strtolower($matches[1]) == "/members") {
					$in_loop_flg = 0;
				}
			}

			if ($in_loop_flg != 0) {
				//ループ内 (members)～(/members) の対象データをチェック
				//追加 members ～ /members

				if (array_key_exists("members", $this->post_params)) {
					$_chk_datas = $this->post_params['members'];	//members内のデータ取り出し
					$exist_check_flag = false;		//postされていないときは false

					foreach($_chk_datas as $kz => $vz) {
						if (array_key_exists($v[0], $vz)) {
							//POSTされたデータを確認し、内容がカラかチェックする。
							if (strlen($vz[$v[0]]) == 0) {
								$exist_check_flag = 0;		//postされた内容がカラ
							} else {
								$exist_check_flag = true;	//データが存在する
							}
						}
					}

					if ($max_items == 0) { $max_items = 1; }

					$req_opt = $v[2];	//必須指定 0|1|2|etc
					if ($req_opt == 1) {	//必須の場合
						if ($exist_check_flag === false || $exist_check_flag === 0) {
							$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
						}
						//文字長の制限超過
						if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
							$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
						}
					}

					else if ($req_opt == 0) {	//必須ではない場合
						if ($exist_check_flag === false || $exist_check_flag === 0) {
							//入力なければ何もしない。
						} else {
							//文字長の制限超過
							if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
								$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
							}
						}
					}

					else if($req_opt == 2) {	//いずれか１つは設定されていること
						if ($max_items < 2) { $max_items = 2; }
						$opt_group = $v[3];	//必須グループ番号 1|2|3|etc

						if ($exist_check_flag === true) {
							//指定されたパラメータのデータが存在すること(カラではなく)
							$error_detect[2][$opt_group] = true;

							//文字長の制限超過
							if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
								$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_USERID"など
								$keta_over = $v[3];	//必須グループ番号を格納
							}

						} else {
							//指定されたパラメータのデータが存在しないか、またはカラ。
							if (array_key_exists(2, $error_detect)) {
								//チェック用の配列に、該当する判別用データが無ければ flase を入れる。
								if (!array_key_exists($opt_group, $error_detect[2])) {
									$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
								}
							} else {
								$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
							}
						}

					}



				} else {
					//postされたデータが存在しない場合
					$req_opt = $v[2];	//必須指定 0|1|2|etc
					if ($req_opt == 1) {	//必須の場合
						if ($exist_check_flag === false || $exist_check_flag === 0) {
							$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
						}
					}

				}


			} else {	//	if ($in_loop_flg != 0) {
				//通常チェック
				$exist_check_flag = false;		//postされていないときは false
				//パラメータファイルで指定された１つ $v[0] のデータが存在するか確認
				if (array_key_exists($v[0] ,$this->post_params)) {
					//POSTされたデータを確認し、内容がカラかチェックする。
					if (strlen($this->post_params[$v[0]]) == 0) {
						$exist_check_flag = 0;		//postされた内容がカラ
					} else {
						$exist_check_flag = true;
					}
				}

				if ($max_items == 0) { $max_items = 1; }

				$req_opt = $v[2];	//必須指定 0|1|2|etc
				if ($req_opt == 1) {	//必須の場合
					if ($exist_check_flag === false || $exist_check_flag === 0) {
						$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
					}
					//文字長の制限超過
					if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
						$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
					}
				}

				else if ($req_opt == 0) {	//必須ではない場合
					if ($exist_check_flag === false || $exist_check_flag === 0) {
						//入力なければ何もしない。
					} else {
						//文字長の制限超過
						if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
							$error_detect[1] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
						}
					}
				}

				else if($req_opt == 2) {	//いずれか１つは設定されていること
					if ($max_items < 2) { $max_items = 2; }
					$opt_group = $v[3];	//必須グループ番号 1|2|3|etc

					if ($exist_check_flag === true) {
						//指定されたパラメータのデータが存在すること(カラではなく)
						$error_detect[2][$opt_group] = true;
//----
//形式
//$error_detect[2][$opt_group] = true
//Array
//(
//    [2] => Array
//        (
//            [1] => (true)		←$opt_group = 1 の場合
//        )
//)
//----
						//文字長の制限超過
						if (intval($v[5]) > 0 && strlen($this->post_params[$v[0]]) > intval($v[5])) {
							$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_USERID"など
							$keta_over = $v[3];	//必須グループ番号を格納
						}

					} else {
						//指定されたパラメータのデータが存在しないか、またはカラ。
						if (array_key_exists(2, $error_detect)) {
							//チェック用の配列に、該当する判別用データが無ければ flase を入れる。
							if (!array_key_exists($opt_group, $error_detect[2])) {
								$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
							}
						} else {
							$error_detect[2][$opt_group] = $v[8];	//エラーコード 定数"INVALID_SCHOOLID"など
						}
					}

				}

			}	//if ($in_loop_flg != 0) {

		}	//foreach($tmp_input[$api_name] as $k => $v) {

		$i = 1;
		$fail_safe = 100;
		while($i <= $max_items && $fail_safe > 0) {

			if (is_array($error_detect[$i])) {
				//必須指定 1以外のチェック用

				if (count($error_detect[$i]) > 0) {
					//指示エラ―がある場合
					foreach($error_detect[$i] as $k2 => $v2) {
						if ($v2 !== true) {
							$error_total_ary[$i] = $v2;
						}
					}
					if($keta_over == $i) {	//桁オーバーしたものがあるのが、この必須グループの場合はエラー扱いにする
						$error_total_ary[$i] = $v2;
					}
				}

			} else {
				//必須指定１のチェック用
				$error_total_ary[$i] = $error_detect[$i];
			}

			$i++;
			$fail_safe--;
		}


		if (count($error_total_ary) > 0) {
			foreach($error_total_ary as $k3 => $v3) {

			if (array_key_exists($v3, $tmp_error[$api_name])) {
					$res = $tmp_error[$api_name][$v3][1];
				} else {
					if (strlen($tmp_error[$api_name][$v3]) > 0) {
						$res = "999";
					}
				}
			}
		}

		return $res;
	}


}

?>