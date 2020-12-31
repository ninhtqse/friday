<?
/**
 * ベンチャー・リンク　すらら
 *
 * AC:[A]管理者 UC1:[M99]その他.
 *
 * 問題確認ツールの設定ファイル
 *
 * @author Azet
 */

// DB設定名(bd_list.phpから)
//define('MY_REPORT_DB_SETTING', 'srlctw11');					// del oda 2015/07/21 DB接続先変更
//define('MY_CONTENT_DB_SETTING', 'srlctw11SOGO');				// del oda 2015/07/21 DB接続先変更
define('MY_REPORT_DB_SETTING', 'srlctd03');						// add oda 2015/07/21 DB接続先変更
define('MY_CONTENT_DB_SETTING', 'srlctd03cts');					// add oda 2015/07/21 DB接続先変更

// 1ページには問題の量
define('PROBS_BY_PAGE', 100);

// formのアクションの既定
define('BASE_PROBLEM_FORM_ACTION', '/admin/main_iframe.php');

// checklistのURL
define('BASE_CHECKLIST_URL', '/admin/_problem-checklist.php');
