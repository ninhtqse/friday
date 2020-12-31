<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class return_log extends baseTable {

	public function getTableName() {
		return "return_log";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_3_student s ON ".$this->getTableName().".student_id = s.student_id";
		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_3_student m ON m.student_id = t.student_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;
		return $del_list;
	}

	/**
	 * 移動元のデータに削除フラグを立てる（削除フラグ無し）
	 */
// 	public function deleteData($db_moto) {
// 	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	// extendInsert 単純移行なのでオーバライドしません

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		return null;
	}
}
