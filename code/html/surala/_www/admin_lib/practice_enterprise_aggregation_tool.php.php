<?php
/**
 * ベンチャー・リンク　すらら
 *
 * 法人用の集計データ作成ツールをadminに設置
 *
 * 履歴
 * 2014/12/15 初期設定
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if($_SERVER['SERVER_ADDR'] == '10.3.11.100'){
		$html = "<br />\n<h3>法人用の集計データ作成ツール</h3><br />\n";
		$html .= "<a href=\"/admin/_enterprise_aggregation_tool.php\" target=\"_blank\">/admin/_enterprise_aggregation_tool.php</a><br />\n";
	}

	return $html;

}
?>