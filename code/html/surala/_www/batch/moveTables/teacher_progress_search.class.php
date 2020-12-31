<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class teacher_progress_search extends baseTable {

	public function getTableName() {
		return "teacher_progress_search";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_2_teacher s ON ".$this->getTableName().".teacher_id = s.teacher_id";
		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_2_teacher m ON m.teacher_id = t.teacher_id ";
		$del_list[] = $sql;
		return $del_list;
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_2_teacher m ON m.teacher_id = t.teacher_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	// extendInsert 単純移行なのでオーバライドしません
}
