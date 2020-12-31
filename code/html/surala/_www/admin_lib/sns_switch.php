<?
/**
 * ベンチャー・リンク　すらら
 *
 * 応援機能ボタン設定
 *
 * 履歴
 * 2013/01/29 初期設定
 *
 * @author Azet
 */

// add koyama

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (MODE == "add") {
		add($ERROR);							// DB登録処理
	}

	$html .= default_html($ERROR);				// 初期画面

	return $html;
}


/**
 * 初期画面
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function default_html($ERROR) {
	// グローバル変数
	global $SNS_TYPE;
	global $GAMEN;
	global $ON_OFF;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ON/OFF情報取得
	$sql =  " SELECT sns_switch_data FROM ".T_SNS_SWITCH.
			" WHERE school_id ='default' AND mk_flg='0';";

	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);
	if(!$list){
		//$ERROR = "ON/OFF情報を取得できませんでした。";
	}
	$SNS_DATA = unserialize($list[sns_switch_data]);

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	} elseif (!$ERROR && MODE == "add"){
		$html .= "<br>\n";
		$html .= "登録しました。<br>\n";
	} else {
		$html .= "<br>\n";
		$html .= "各項目のON/OFFを切り替えて登録ボタンを押してください。<br>\n";
	}
	$html .= "<br>\n";

	//テーブル表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";

	$html .= "<table class=\"sns_form_table\" border=solid>\n";
	$html .= "<tr class=\"sns_form_menu\">\n";

	$html .= "<th width=100px; rowspan='2'>画面</th>\n";
	foreach ($SNS_TYPE AS $val) {
		$html .= "<th width=100px;>".$val."</th>\n";
	}

	$html .= "</tr>\n";

	$html .= "<tr class=\"sns_form_menu\">\n";
	foreach ($SNS_TYPE AS $val) {
		$html .= "<th>ON/OFF</th>\n";
	}
	$html .= "</tr>\n";

	foreach ($GAMEN AS $g_key => $g_val) {
		$html .= "<tr class=\"sns_form_cell\">\n";
		$html .= "<td>".$g_val."</td>\n";

		foreach ($SNS_TYPE AS $s_key => $s_val) {
			$name = $s_key."_".$g_key;
			$html .= "<td class=\"sns_form_td\">\n";
			$html .= "<select name=\"".$name."\">\n";

			foreach ($ON_OFF AS $key => $val) {
				$selected = "";
				if ($SNS_DATA[$name] == $key) {
					$selected = " selected";
				}
				$html .= "<option value=\"".$key."\"".$selected.">".$val."</option>\n";
			}

			$html .= "</select>\n";
			$html .= "</td>\n";
		}
		$html .= "</tr>\n";
	}

	$html .= "</table>\n";

	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
	$html .= "<input type=\"submit\" value=\"登録\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add(&$ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//レコード有無チェック
	$sql =  " SELECT COUNT(*) AS cnt  FROM ".T_SNS_SWITCH.
			" WHERE school_id ='default' AND mk_flg='0';";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$cnt = $list['cnt'];
	}

	foreach ($_POST as $key => $val) {
		if ($key == "mode") {
			 continue;
		}
		$SWITCH_DATA[$key] = "$val";
	}
	$serial_data = serialize($SWITCH_DATA);  // シリアル化

	//レコードが無ければ登録、有れば更新
	if($cnt < 1){

		$INSERT_DATA[school_id] = "default";
		$INSERT_DATA[sns_switch_data] = $cdb->real_escape($serial_data);
		$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[ins_tts_id] = $_SESSION['myid']['id'];
		$INSERT_DATA[ins_date] = "now()";

		// ＤＢ登録処理
		$ERROR = $cdb->insert(T_SNS_SWITCH,$INSERT_DATA);

	}else{

		$INSERT_DATA[sns_switch_data] = $cdb->real_escape($serial_data);
		$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] = "now()";

		//  ＤＢ変更処理
		$where = " WHERE school_id ='default' LIMIT 1;";
		$ERROR = $cdb->update(T_SNS_SWITCH,$INSERT_DATA,$where);

	}

}
?>