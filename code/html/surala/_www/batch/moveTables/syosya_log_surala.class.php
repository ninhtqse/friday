<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class syosya_log_surala extends baseTable {

	public function getTableName() {
		return "syosya_log_surala";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName()."_evac.school_id = s.school_id";
		$sql .= " WHERE ".$this->getTableName()."_evac.move_flg = 0";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName().".school_id = s.school_id";
		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;
		return $del_list;
	}

	// AUTO_INCRIMENTが有るので、フィールドを抜いてINSERTする
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as syosya_log_surala_num";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,log_type";
		$sql .= " ,school_id";
		$sql .= " ,student_id";
		$sql .= " ,course_num";
		$sql .= " ,stage_num";
		$sql .= " ,lesson_num";
		$sql .= " ,unit_num";
		$sql .= " ,block_num";
		$sql .= " ,problem_num";
		$sql .= " ,review";
		$sql .= " ,again";
		$sql .= " ,unique_id";
		$sql .= " ,handwriteing_type";
		$sql .= " ,stroke_info";
		$sql .= " ,answer_canvas_size";
		$sql .= " ,syosya_evaluation";
		$sql .= " ,syosya_log_write";
		$sql .= " ,evaluation_total_score";
		$sql .= " ,regist_time";
		$sql .= " ,question_num";
		$sql .= " ,move_flg";
		$sql .= " ,move_tts_id";
		$sql .= " ,move_date";
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
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);

		$sql  = " UPDATE ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
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
