<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class target_info_group extends baseTable {

// 	public function getTableName() {
// 		return "target_info_group";
// 	}

// 	/**
// 	 * 移動元のデータを抽出
// 	 */
// 	public function getSQL() {
// 		$sql = "";
// 		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
// 		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName()."_evac.school_id = s.school_id";
// 		$sql .= " WHERE ".$this->getTableName()."_evac.move_flg = 0";
// 		$sql .= " UNION ALL ";												// UNION ALLで順番になる
// 		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
// 		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName().".school_id = s.school_id";
// 		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
// 		return $sql;
// 	}

// 	/**
// 	 * 移動先のデータを消す(特殊な削除の為、extendDeleteで行う
// 	 */
// 	public function getDeleteSQL() {
// 		return null;
// 	}

// 	/**
// 	 * 移動先のデータを消す（念のため）
// 	 * 親データに紐づく子データを削除していく
// 	 */
// 	public function extendDelete($db_saki) {

// 		$sql  = " DELETE FROM target_info_test_choice ";
// 		$sql .= " WHERE target_info_list_id IN (";
// 		$sql .= "       SELECT til.target_info_list_id FROM target_info_list til ";
// 		$sql .= "       INNER JOIN mv_5_target_info_group tig ON tig.target_info_group_id = til.target_info_group_id ";
// 		$sql .= "      );";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql  = " DELETE FROM target_info_test_choice_evac ";
// 		$sql .= " WHERE target_info_list_id IN (";
// 		$sql .= "       SELECT til.target_info_list_id FROM target_info_list til ";
// 		$sql .= "       INNER JOIN mv_5_target_info_group tig ON tig.target_info_group_id = til.target_info_group_id ";
// 		$sql .= "      );";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql  = " DELETE FROM target_info_test_problem ";
// 		$sql .= " WHERE target_info_list_id IN (";
// 		$sql .= "       SELECT til.target_info_list_id FROM target_info_list til ";
// 		$sql .= "       INNER JOIN mv_5_target_info_group tig ON tig.target_info_group_id = til.target_info_group_id ";
// 		$sql .= "      );";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql  = " DELETE FROM target_info_test_problem_evac ";
// 		$sql .= " WHERE target_info_list_id IN (";
// 		$sql .= "       SELECT til.target_info_list_id FROM target_info_list til ";
// 		$sql .= "       INNER JOIN mv_5_target_info_group tig ON tig.target_info_group_id = til.target_info_group_id ";
// 		$sql .= "      )";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql  = " DELETE FROM target_info_list ";
// 		$sql .= " WHERE target_info_group_id IN (";
// 		$sql .= "       SELECT target_info_group_id FROM mv_5_target_info_group ";
// 		$sql .= "      )";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql  = " DELETE FROM target_info_list_evac ";
// 		$sql .= " WHERE target_info_group_id IN (";
// 		$sql .= "       SELECT target_info_group_id FROM mv_5_target_info_group ";
// 		$sql .= "      )";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql = "";
// 		$sql .= " DELETE FROM ".$this->getTableName()."_evac ";
// 		$sql .= " WHERE school_id IN (SELECT school_id FROM mv_1_school)";
// 		$sql .= "   AND move_flg = 0";
// echo $sql."\n";
// 		$db_saki->exec_query($sql);

// 		$sql = "";
// 		$sql .= " DELETE FROM ".$this->getTableName();
// 		$sql .= " WHERE school_id IN (SELECT school_id FROM mv_1_school)";
// 		$sql .= "   AND move_flg = 0";
// echo $sql."\n";

// 		$db_saki->exec_query($sql);

// 		return;
// 	}

// 	/**
// 	 * SELECT/INSERTは行わない
// 	 *
// 	 * @see baseTable::getSelectInsertSQL()
// 	 */
// 	public function getSelectInsertSQL() {
// 		return null;
// 	}

// 	/**
// 	 * INSERTしながら、採番した番号を管理テーブルに更新する
// 	 */
// 	public function extendInsert($db_saki) {

// 		// ID取得
// 		$sql  = " SELECT";
// 		$sql .= "   target_info_group_id ";
// 		$sql .= " FROM mv_5_target_info_group ";
// 		$sql .= ";";

// echo $sql."\n";

// 		if($rs = $db_saki->query($sql)){
// 			while($list = $db_saki->fetch_assoc($rs)) {
// 				$target_info_group_id = $list['target_info_group_id'];

// 				// 取得した管理番号をSELECT/INSERTする
// 				$sql  = "insert into ".$this->getTableName();
// 				$sql .= " select ";
// 				$sql .= "  0 as target_info_group_id";		// auto_incrementに0を指定すると附番してくれる
// 				$sql .= " ,school_id";
// 				$sql .= " ,object_cd";
// 				$sql .= " ,entry_cd";
// 				$sql .= " ,student_id";
// 				$sql .= " ,teacher_id";
// 				$sql .= " ,guardian_id";
// 				$sql .= " ,enterprise_id";
// 				$sql .= " ,target_group_name";
// 				$sql .= " ,biko";
// 				$sql .= " ,list_num";
// 				$sql .= " ,target_study_group_id";
// 				$sql .= " ,move_flg";
// 				$sql .= " ,move_tts_id";
// 				$sql .= " ,move_date";
// 				$sql .= " ,mk_flg";
// 				$sql .= " ,mk_tts_id";
// 				$sql .= " ,mk_date";
// 				$sql .= " ,upd_syr_id";
// 				$sql .= " ,upd_tts_id";
// 				$sql .= " ,upd_date";
// 				$sql .= " ,ins_syr_id";
// 				$sql .= " ,ins_tts_id";
// 				$sql .= " ,ins_date";
// 				$sql .= " ,sys_biko";
// 				$sql .= "  from MV_".$this->getTableName();
// 				$sql .= " where target_info_group_id = '".$target_info_group_id."'";
// 				$sql .= ";";
// echo $sql."\n";

// 				$db_saki->exec_query($sql);

// 				// 採番した番号を取得する
// 				$new_target_info_group_id = $db_saki->insert_id();

// 				// 取得した採番情報を管理番号に反映する
// 				$sql  = " UPDATE mv_5_target_info_group ";
// 				$sql .= " SET  ";
// 				$sql .= "  new_target_info_group_id = '".$new_target_info_group_id."' ";
// 				$sql .= " WHERE target_info_group_id = '".$target_info_group_id."'";
// 				$sql .= ";";
// 				$db_saki->exec_query($sql);
// echo $sql."\n";

// 				// 負荷が上がらない様に0.1秒待つ
// 				usleep(100000);

// 			}
// 		}


// 		return $sql;
// 	}

// 	/**
// 	 * 移動元のデータに削除フラグを立てる
// 	 */
// 	public function deleteData($db_moto) {
// 		$sql  = " UPDATE ".$this->getTableName();
// 		$sql .= " SET  ";
// 		$sql .= "  mk_flg = '1' ";
// 		$sql .= " ,mk_tts_id = 'mvent' ";
// 		$sql .= " ,mk_date = now() ";
// 		$sql .= " WHERE school_id IN (SELECT school_id FROM mv_1_school)";
// 		$sql .= "   AND move_flg = 0";
// 		$sql .= ";";
// 		$db_moto->exec_query($sql);

// 		$sql  = " UPDATE ".$this->getTableName()."_evac ";
// 		$sql .= " SET  ";
// 		$sql .= "  mk_flg = '1' ";
// 		$sql .= " ,mk_tts_id = 'mvent' ";
// 		$sql .= " ,mk_date = now() ";
// 		$sql .= " WHERE school_id IN (SELECT school_id FROM mv_1_school)";
// 		$sql .= "   AND move_flg = 0";
// 		$sql .= ";";
// 		$db_moto->exec_query($sql);
// 	}

// 	/**
// 	 * 作業テーブル削除
// 	 * 後続のテーブルで利用するので、後でDROPする
// 	 * @param object $db_saki
// 	 */
// 	public function dropTable($db_saki) {

// 		$sql = "DROP TABLE IF EXISTS MV_".$this->getTableName()."; ";

// 		return $sql;
// 	}

}
