<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class drawing_log_question_evac extends baseTable {

	public function getTableName() {
		return "drawing_log_question_evac";
	}

	/**
	 * 移動元のデータを抽出（evacデータは別クラスで処理します：AUTOINCRIMENTが無い為）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
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
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		return $del_list;
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
		$sql .= " ,t.mk_data = now() ";
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// 管理番号を置き換える必要あり
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  ifnull(mv8.new_drawing_log_id, t.drawing_log_id) as drawing_log_id";
		$sql .= " ,ifnull(mv9.new_question_num, t.question_num) as question_num";
		$sql .= " ,type_num";
		$sql .= " ,school_id";
		$sql .= " ,user_id";
		$sql .= " ,course_num";
		$sql .= " ,block_num";
		$sql .= " ,skill_num";
		$sql .= " ,review";
		$sql .= " ,move_flg";
		$sql .= " ,move_tts_id";
		$sql .= " ,move_date";
		$sql .= " ,mk_flg";
		$sql .= " ,mk_tts_id";
		$sql .= " ,mk_data";
		$sql .= " ,upd_syr_id";
		$sql .= " ,upd_tts_id";
		$sql .= " ,upd_date";
		$sql .= " ,ins_syr_id";
		$sql .= " ,ins_tts_id";
		$sql .= " ,ins_date";
		$sql .= " ,sys_bko";
		$sql .= "  from MV_".$this->getTableName(). " t ";
		$sql .= "  left join mv_8_drawing_log mv8 on mv8.drawing_log_id = t.drawing_log_id";
		$sql .= "  left join mv_9_question mv9 on mv9.question_num = t.question_num";

		return $sql;
	}

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		$sql  = " UPDATE MV_".$this->getTableName()." t ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '0' ";
		$sql .= " ,t.mk_tts_id = null ";
		$sql .= " ,t.mk_data = null ";					// mk_dateでは無いので注意
		$sql .= " WHERE t.mk_tts_id = 'mvent'";
		$sql .= ";";
		return $sql;
	}

}
