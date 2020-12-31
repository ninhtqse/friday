<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class target_study_test_choice extends baseTable {

	public function getTableName() {
		return "target_study_test_choice";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_6_target_study_list s ON ".$this->getTableName()."_evac.target_study_list_id = s.target_study_list_id";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
		$sql .= " INNER JOIN mv_6_target_study_list s ON ".$this->getTableName().".target_study_list_id = s.target_study_list_id";
		return $sql;
	}

	/**
	 * 移動先のデータを消す(特殊な削除の為、extendDeleteで行う
	 */
	public function getDeleteSQL() {
		return null;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 * 親データに紐づく子データを削除していく
	 * (target_study_groupで行っているのでここでは何もしない)
	 */
	public function extendDelete($db_saki) {
		return;
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

		// 取得した管理番号をSELECT/INSERTする
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as target_study_test_choice_id";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,mvg.new_target_study_group_id as target_study_group_id";
		$sql .= " ,mvl.new_target_study_list_id as target_study_list_id";
		$sql .= " ,t.test_type";
		$sql .= " ,t.test_mode";
		$sql .= " ,t.book_unit_id";
		$sql .= " ,t.gknn";
		$sql .= " ,t.bnr_cd";
		$sql .= " ,t.kmk_cd";
		$sql .= " ,t.course_num";
		$sql .= " ,t.stage_num";
		$sql .= " ,t.lesson_num";
		$sql .= " ,t.unit_num";
		$sql .= " ,t.block_num";
		$sql .= " ,0 as target_info_group_id";				// クリアする
		$sql .= " ,0 as target_info_list_id";				// クリアする
		$sql .= " ,0 as target_info_test_choice_id";		// クリアする
		$sql .= " ,t.target_info_group_apply_id";
		$sql .= " ,t.target_info_list_apply_id";
		$sql .= " ,t.mk_flg";
		$sql .= " ,t.mk_tts_id";
		$sql .= " ,t.mk_date";
		$sql .= " ,t.upd_syr_id";
		$sql .= " ,t.upd_tts_id";
		$sql .= " ,t.upd_date";
		$sql .= " ,t.ins_syr_id";
		$sql .= " ,t.ins_tts_id";
		$sql .= " ,t.ins_date";
		$sql .= " ,t.sys_biko";
		$sql .= "  from MV_".$this->getTableName()." t " ;
		$sql .= " inner join mv_6_target_study_group mvg ON mvg.target_study_group_id = t.target_study_group_id";
		$sql .= " inner join mv_6_target_study_list mvl ON mvl.target_study_list_id = t.target_study_list_id";
		$sql .= ";";

		$db_saki->exec_query($sql);

	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN target_study_list tsl ON tsl.target_study_list_id = t.target_study_list_id ";
		$sql .= " INNER JOIN target_study_group tsg ON tsg.target_study_group_id = tsl.target_study_group_id AND tsg.move_flg = 0";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = tsg.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= ";";
		$db_moto->exec_query($sql);

		$sql  = " UPDATE ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN target_study_list tsl ON tsl.target_study_list_id = t.target_study_list_id ";
		$sql .= " INNER JOIN target_study_group tsg ON tsg.target_study_group_id = tsl.target_study_group_id AND tsg.move_flg = 0";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = tsg.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= ";";
		$db_moto->exec_query($sql);

		return;
	}

	/**
	 * 作業テーブル削除
	 * 後続のテーブルで利用するので、後でDROPする
	 * @param object $db_saki
	 */
	public function dropTable($db_saki) {

		$sql = "DROP TABLE IF EXISTS MV_".$this->getTableName()."; ";

		return $sql;
	}

}
