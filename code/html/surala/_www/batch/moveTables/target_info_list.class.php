<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class target_info_list extends baseTable {

// 	public function getTableName() {
// 		return "target_info_list";
// 	}

// 	/**
// 	 * 移動元のデータを抽出
// 	 */
// 	public function getSQL() {
// 		$sql = "";
// 		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
// 		$sql .= " INNER JOIN mv_5_target_info_group s ON ".$this->getTableName()."_evac.target_info_group_id = s.target_info_group_id";
// 		$sql .= " UNION ALL ";												// UNION ALLで順番になる
// 		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName();
// 		$sql .= " INNER JOIN mv_5_target_info_group s ON ".$this->getTableName().".target_info_group_id = s.target_info_group_id";
// 		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
// 		return $sql;
// 	}

// 	/**
// 	 * 移動先のデータはtarget_info_groupで消す
// 	 */
// 	public function getDeleteSQL() {
// 		return null;
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

// 		// 校舎ID取得
// 		$sql  = " SELECT";
// 		$sql .= "   target_info_list_id ";
// 		$sql .= " FROM mv_5_target_info_list ";
// 		$sql .= ";";

// echo $sql."\n";

// 		if($rs = $db_saki->query($sql)){
// 			while($list = $db_saki->fetch_assoc($rs)) {
// 				$target_info_list_id = $list['target_info_list_id'];

// 				// 取得した管理番号をSELECT/INSERTする
// 				$sql  = "insert into ".$this->getTableName();
// 				$sql .= " select ";
// 				$sql .= "  0 as target_info_list_id";		// auto_incrementに0を指定すると附番してくれる
// 				$sql .= " ,mv5.new_target_info_group_id";
// 				$sql .= " ,mvt.target_cd";
// 				$sql .= " ,mvt.gknn";
// 				$sql .= " ,mvt.course_num";
// 				$sql .= " ,mvt.stage_num";
// 				$sql .= " ,mvt.lesson_num";
// 				$sql .= " ,mvt.unit_num";
// 				$sql .= " ,mvt.unit_key";
// 				$sql .= " ,mvt.block_num";
// 				$sql .= " ,mvt.skill_num";
// 				$sql .= " ,mvt.package_course_num";
// 				$sql .= " ,mvt.package_stage_num";
// 				$sql .= " ,mvt.package_lesson_num";
// 				$sql .= " ,mvt.package_unit_num";
// 				$sql .= " ,mvt.package_block_num";
// 				$sql .= " ,mvt.package_list_num";
// 				$sql .= " ,mvt.group_category_id";
// 				$sql .= " ,mvt.book_id";
// 				$sql .= " ,mvt.test_group_id";
// 				$sql .= " ,mvt.default_test_num";
// 				$sql .= " ,mvt.class_id";
// 				$sql .= " ,mvt.test_type_num";
// 				$sql .= " ,mvt.test_category1_num";
// 				$sql .= " ,mvt.test_category2_num";
// 				$sql .= " ,mvt.limit_time";
// 				$sql .= " ,mvt.start_date";
// 				$sql .= " ,mvt.end_date";
// 				$sql .= " ,mvt.start_time";
// 				$sql .= " ,mvt.end_time";
// 				$sql .= " ,mvt.test_num";
// 				$sql .= " ,mvt.option1";
// 				$sql .= " ,mvt.option2";
// 				$sql .= " ,mvt.start_page";
// 				$sql .= " ,mvt.end_page";
// 				$sql .= " ,mvt.keyword";
// 				$sql .= " ,mvt.list_num";
// 				$sql .= " ,mvt.target_study_group_id";
// 				$sql .= " ,mvt.target_study_group_id";


// 				$sql .= " ,mvt.move_flg";
// 				$sql .= " ,mvt.move_tts_id";
// 				$sql .= " ,mvt.move_date";
// 				$sql .= " ,mvt.mk_flg";
// 				$sql .= " ,mvt.mk_tts_id";
// 				$sql .= " ,mvt.mk_date";
// 				$sql .= " ,mvt.upd_syr_id";
// 				$sql .= " ,mvt.upd_tts_id";
// 				$sql .= " ,mvt.upd_date";
// 				$sql .= " ,mvt.ins_syr_id";
// 				$sql .= " ,mvt.ins_tts_id";
// 				$sql .= " ,mvt.ins_date";
// 				$sql .= " ,mvt.sys_biko";
// 				$sql .= "  from MV_".$this->getTableName()." mvt ";
// 				$sql .= " inner join mv_5_target_info_group mv5 ON mv5.target_info_group_id = mvt.target_info_group_id ";
// 				$sql .= " where mvt.target_info_list_id = '".$target_info_list_id."'";
// 				$sql .= ";";
// echo $sql."\n";

// 				$db_saki->exec_query($sql);

// 				// 採番した番号を取得する
// 				$new_target_info_list_id = $db_saki->insert_id();

// 				// 取得した採番情報を管理番号に反映する
// 				$sql  = " UPDATE mv_5_target_info_list ";
// 				$sql .= " SET  ";
// 				$sql .= "  new_target_info_list_id = '".$new_target_info_list_id."' ";
// 				$sql .= " WHERE target_info_list_id = '".$target_info_list_id."'";
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
// 		$sql .= " WHERE target_info_list_id IN (SELECT target_info_list_id FROM mv_5_target_info_list)";
// 		$sql .= "   AND move_flg = 0";
// 		$sql .= ";";
// 		$db_moto->exec_query($sql);

// 		$sql  = " UPDATE ".$this->getTableName()."_evac ";
// 		$sql .= " SET  ";
// 		$sql .= "  mk_flg = '1' ";
// 		$sql .= " ,mk_tts_id = 'mvent' ";
// 		$sql .= " ,mk_date = now() ";
// 		$sql .= " WHERE target_info_list_id IN (SELECT target_info_list_id FROM mv_5_target_info_list)";
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
