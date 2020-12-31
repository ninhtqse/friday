<?php
/**
 * ベンチャー・リンク　すらら
 *
 * molee等、各デバイスから問題表示確認ツールを使用できるようにadminにリンクを設置しました。
 *
 * 履歴
 * 2014/12/15 初期設定
 *
 * @author Azet
 */

// add yoshizawa

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	$html = "<br />\n<h3>問題表示確認ツール</h3><br />\n";
	$html .= "<a href=\"http://10.128.1.35/admin/_problem-checklist.php\" target=\"_blank\">http://10.128.1.35/admin/_problem-checklist.php</a><br />\n";

	$html .= "<br />\n<h3>縦書き確認ツール</h3><br />\n";
	$html .= "<a href=\"http://10.128.1.35/admin/_tategaki-kenshou-adminlist.php\" target=\"_blank\">http://10.128.1.35/admin/_tategaki-kenshou-adminlist.php</a><br />\n";

	// add start 2018/03/08 yoshizawa AWS環境判定
	if($_SERVER['SERVER_ADDR'] == '10.3.11.100'){
		$html = "<br />\n<h3>問題表示確認ツール</h3><br />\n";
		$html .= "<a href=\"/admin/_problem-checklist.php\" target=\"_blank\">/admin/_problem-checklist.php</a><br />\n";

		$html .= "<br />\n<h3>縦書き確認ツール</h3><br />\n";
		$html .= "<a href=\"/admin/_tategaki-kenshou-adminlist.php\" target=\"_blank\">/admin/_tategaki-kenshou-adminlist.php</a><br />\n";
	}
	// add end 2018/03/08 yoshizawa AWS環境判定

	return $html;

}
?>