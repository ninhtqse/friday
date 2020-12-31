<?
/**
 * ベンチャー・リンク　すらら
 *
 * 変換エンジン管理画面入り口
 *
 * 履歴
 * 2012/03/26 初期設定
 *
 * @author Azet
 */

// add simon
require_once(LOG_DIR."shared_lib/translator.class.php");

// AJAXリクエスト
if($_POST['ajax']) {
	require_once("translator/trans_data.php");
	exit();
}


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
	global $translator_languages, $languages_json_settings;

	//またはテンプレート表示
	ob_start();
	include('translator/main.php');
	$body = ob_get_contents();
	ob_end_clean();

	return $body;
}
