<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class question extends baseTable {

	public function getTableName() {
		return "question";
	}

	/**
	 * 移動元のデータを抽出（evacデータが存在する場合は、UNIONします）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName()."_evac.school_id = s.school_id";
		$sql .= " WHERE ".$this->getTableName()."_evac.move_flg = 0";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
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
		$sql .= "   question_num ";
		$sql .= " FROM mv_9_question ";
		$sql .= ";";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$question_num = $list['question_num'];

				// 取得した管理番号をSELECT/INSERTする
				$sql  = "insert into ".$this->getTableName();
				$sql .= " select ";
				$sql .= "  0 as question_num";		// auto_incrementに0を指定すると附番してくれる
				$sql .= " ,thread_num";
				$sql .= " ,type_num";
				$sql .= " ,school_id";
				$sql .= " ,user_id";
				$sql .= " ,course_num";
				$sql .= " ,unit_num";
				$sql .= " ,block_num";
				$sql .= " ,skill_num";
				$sql .= " ,review";
				$sql .= " ,drill_count";
				$sql .= " ,page_info";
				$sql .= " ,average_time";
				$sql .= " ,average_rate";
				$sql .= " ,comp_flg";
				$sql .= " ,problem_num";
				$sql .= " ,answer";
				$sql .= " ,answer2";
				$sql .= " ,subject";
				$sql .= " ,message";
				$sql .= " ,files";
				$sql .= " ,regist_time";
				$sql .= " ,update_time";
				$sql .= " ,state";
				$sql .= " ,move_flg";
				$sql .= " ,move_tts_id";
				$sql .= " ,move_date";
				$sql .= " ,mk_flg";
				$sql .= " ,mk_tts_id";
				$sql .= " ,mk_date";
				$sql .= "  from MV_".$this->getTableName();
				$sql .= " where question_num = '".$question_num."'";
				$sql .= ";";

				$db_saki->exec_query($sql);

				// 採番した番号を取得する
				$new_question_num = $db_saki->insert_id();

				// 取得した採番情報を管理番号に反映する
				$sql  = " UPDATE mv_9_question ";
				$sql .= " SET  ";
				$sql .= "  new_question_num = '".$new_question_num."' ";
				$sql .= " WHERE question_num = '".$question_num."'";
				$sql .= ";";
				$db_saki->exec_query($sql);

				// 負荷が上がらない様に0.01秒待つ
				usleep(10000);

			}
		}
		$db_saki->free_result($rs);

		// スレッドの番号を更新する
		$sql  = " UPDATE ".$this->getTableName() ." t ";
		$sql .= " INNER JOIN mv_9_question mv9 ON t.thread_num = mv9.question_num ";
		$sql .= " SET  ";
		$sql .= "  t.thread_num = mv9.new_question_num ";
		$sql .= " WHERE t.school_id IN (SELECT school_id FROM mv_1_school)";
		$sql .= "   AND t.thread_num > 0";
		$sql .= ";";
		$db_saki->exec_query($sql);

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

}
