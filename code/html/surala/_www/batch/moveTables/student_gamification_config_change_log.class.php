<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class student_gamification_config_change_log extends baseTable {

	public function getTableName() {
		return "student_gamification_config_change_log";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_3_student s ON ".$this->getTableName().".student_id = s.student_id";
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
		$del_list[] = $sql;
		return $del_list;
	}

	// AUTO_INCRIMENTが有るので、フィールドを抜いてINSERTする
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as log_id";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,student_id";
		$sql .= " ,change_date";
		$sql .= " ,change_type";
		$sql .= " ,change_syr_id";
		$sql .= " ,use_flg";
		$sql .= " ,restriction_type";
		$sql .= " ,restriction_time_one_time";
		$sql .= " ,restriction_time_one_day";
		$sql .= " ,useable_start";
		$sql .= " ,useable_end";
		$sql .= " ,unuseable_start";
		$sql .= " ,unuseable_end";
		$sql .= " ,release_type";
		$sql .= " ,mk_flg";
		$sql .= " ,mk_tts_id";
		$sql .= " ,mk_date";
		$sql .= " ,upd_syr_id";
		$sql .= " ,upd_tts_id";
		$sql .= " ,upd_date";
		$sql .= " ,ins_syr_id";
		$sql .= " ,ins_tts_id";
		$sql .= " ,ins_date";
		$sql .= " ,sys_bko";
		$sql .= "  from MV_".$this->getTableName();

		return $sql;
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
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	// extendInsert 単純移行なのでオーバライドしません
}
