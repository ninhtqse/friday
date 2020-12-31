<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class drawing_log extends baseTable {

	public function getTableName() {
		return "drawing_log";
	}

	/**
	 * 移動元のデータを抽出（evacデータが存在する場合は、UNIONします）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_8_drawing_log m ON m.drawing_log_id = ".$this->getTableName()."_evac.drawing_log_id ";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_8_drawing_log m ON m.drawing_log_id = ".$this->getTableName().".drawing_log_id ";
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
		$sql .= " INNER JOIN drawing_log_surala dls ON dls.drawing_log_id = t.drawing_log_id ";
		$sql .= " INNER JOIN study_record s ON s.study_record_num = dls.study_record_num  ";
		$sql .= " INNER JOIN mv_1_school mv ON mv.school_id = s.school_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN drawing_log_surala dls ON dls.drawing_log_id = t.drawing_log_id ";
		$sql .= " INNER JOIN study_record s ON s.study_record_num = dls.study_record_num  ";
		$sql .= " INNER JOIN mv_1_school mv ON mv.school_id = s.school_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN drawing_log_test dlt ON dlt.drawing_log_id = t.drawing_log_id ";
		$sql .= " INNER JOIN mv_3_student mv ON mv.student_id = dlt.student_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN drawing_log_test dlt ON dlt.drawing_log_id = t.drawing_log_id ";
		$sql .= " INNER JOIN mv_3_student mv ON mv.student_id = dlt.student_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		return $del_list;
	}

	/**
	 * SELECT/INSERTは行わない
	 *
	 * @see baseTable::getSelectInsertSQL()
	 */
	public function getSelectInsertSQL() {
		return null;
	}

	/**
	 * INSERTしながら、採番した番号を管理テーブルに更新する
	 */
	public function extendInsert($db_saki) {

		// ID取得
		$sql  = " SELECT";
		$sql .= "   drawing_log_id ";
		$sql .= " FROM mv_8_drawing_log ";
		$sql .= ";";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$drawing_log_id = $list['drawing_log_id'];

				// 取得した管理番号をSELECT/INSERTする
				$sql  = "insert into ".$this->getTableName();
				$sql .= " select ";
				$sql .= "  0 as drawing_log_id";		// auto_incrementに0を指定すると附番してくれる
				$sql .= " ,student_id";
				$sql .= " ,problem_type";
				$sql .= " ,drawing_answer";
				$sql .= " ,drawing_message";
				$sql .= " ,answer_error_flg";
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
				$sql .= " where drawing_log_id = '".$drawing_log_id."'";
				$sql .= ";";

				$db_saki->exec_query($sql);

				// 採番した番号を取得する
				$new_drawing_log_id = $db_saki->insert_id();

				// 取得した採番情報を管理番号に反映する
				$sql  = " UPDATE mv_8_drawing_log ";
				$sql .= " SET  ";
				$sql .= "  new_drawing_log_id = '".$new_drawing_log_id."' ";
				$sql .= " WHERE drawing_log_id = '".$drawing_log_id."'";
				$sql .= ";";
				$db_saki->exec_query($sql);

				// 負荷が上がらない様に0.01秒待つ
				usleep(10000);

			}
		}
		$db_saki->free_result($rs);
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_8_drawing_log m ON m.drawing_log_id = t.drawing_log_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);

		$sql  = " UPDATE ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN mv_8_drawing_log m ON m.drawing_log_id = t.drawing_log_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

}
