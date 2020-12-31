<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class test_data extends baseTable {

	public function getTableName() {
		return "test_data";
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
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_3_student m ON m.student_id = t.student_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}



	// getSelectInsertSQL 単純移行なのでオーバライドしません

	/**
	 * 目標の番号を更新
	 *
	 * @see baseTable::extendInsert()
	 */
	public function extendInsert($db_saki) {
		$sql  = " UPDATE ".$this->getTableName() ." t ";
		$sql .= " INNER JOIN mv_6_target_study_list mv6 ON t.target_id = mv6.target_study_list_id ";
		$sql .= " SET  ";
		$sql .= "  t.target_id = mv6.new_target_study_list_id ";
		$sql .= " WHERE student_id IN (SELECT student_id FROM mv_3_student)";
		$sql .= "   AND target_id > 0";
		$sql .= ";";
		$db_saki->exec_query($sql);
	}
}
