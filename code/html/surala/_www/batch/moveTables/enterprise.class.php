<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class enterprise extends baseTable {

	public function getTableName() {
		return "enterprise";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_0_enterprise e ON ".$this->getTableName().".enterprise_id = e.enterprise_id";
		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_0_enterprise m ON m.enterprise_id = t.enterprise_id ";
		$del_list[] = $sql;
		return $del_list;
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_0_enterprise m ON m.enterprise_id = t.enterprise_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	// extendInsert 単純移行なのでオーバライドしません
}
