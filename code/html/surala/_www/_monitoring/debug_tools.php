<?php
/**
 * ベンチャー・リンク　すらら
 *
 * 開発中の為にDEBUGツール
 *
 * 履歴
 * 2014-08-01 初期設定
 *
 * @author Azet
 */

//define("SHADOW_MODE", false);	// trueでTEMPORARY TABLEがテストのテーブルにコピーされます



/**
 * 無限ロープ防ぐ機能
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param array $l_
*/
function debug_limit($l_) {
	// add simon

	static $count = 0;

	if($count<5) {
		print_r($l_);
	}

	++$count;
}


/**
 * temporaryテーブルを見られるように
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $table_ 何コーピしたいテーブル
*/
function shadowTemporaryTable($table_) {

	// DB接続
	$cdb = $GLOBALS['cdb'];

	// add simon 2014-08-01
	if(SHADOW_MODE!==true) return;
	$cdb->exec_query("DROP TABLE IF EXISTS __tests_simon_{$table_}");
	$cdb->exec_query("CREATE TABLE __tests_simon_{$table_} SELECT * from {$table_}");
}

