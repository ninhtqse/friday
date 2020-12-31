<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class target_study_list extends baseTable {

	public function getTableName() {
		return "target_study_list";
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

		// 変数初期化
		$rec_count = 0;
		$start_point = 0;
		$end_point = 0;

		// 件数取得
		$sql  = " SELECT";
		$sql .= "   count(target_study_list_id) as rec_count ";
		$sql .= " FROM mv_6_target_study_list ";
		$sql .= ";";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$rec_count = $list['rec_count'];
			}
		}

// echo $db_saki->dbname."\n";
// echo $sql."\n";

		// 移動先のtarget_study_groupのauto_incrimentの値を取得する
		$sql   = " SELECT auto_increment FROM information_schema.tables ";
		$sql  .= " WHERE table_schema='".$db_saki->dbname."' and table_name = '".$this->getTableName()."';";

// echo $sql."\n";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$start_point = $list['auto_increment'];
			}
		}
		$db_saki->free_result($rs);

		$end_point = $start_point + $rec_count;
		$auto_increment = $end_point + 100;			// 念のため、100件の余裕を取る

		// 移動先のtarget_study_groupのauto_incrimentの値を更新する
		$sql  = " ALTER TABLE ".$this->getTableName()." AUTO_INCREMENT=".$auto_increment.";";
		$db_saki->exec_query($sql);

// echo $sql."\n";


		// 配列クリア
		$target_study_list_list = array();

		// ID取得
		$sql  = " SELECT";
		$sql .= "   target_study_list_id ";
		$sql .= " FROM mv_6_target_study_list ";
		$sql .= " ORDER BY target_study_list_id ";
		$sql .= ";";
		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$target_study_list_list[] = $list['target_study_list_id'];
			}
		}
		$db_saki->free_result($rs);

// echo $sql."\n";

		// SELECT INSERTする 連番は変数を使用して附番する
		$sql  = "set @rownum = ".$start_point.";";
		$db_saki->exec_query($sql);

// echo $sql."\n";

		// 取得した管理番号をSELECT/INSERTする
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  @rownum:=@rownum+1 as target_study_list_id";
		$sql .= " ,mvg.new_target_study_group_id as target_study_group_id";
		$sql .= " ,t.student_id";
		$sql .= " ,t.sgl_id";
		$sql .= " ,t.target_cd";
		$sql .= " ,t.gknn";
		$sql .= " ,t.course_num";
		$sql .= " ,t.stage_num";
		$sql .= " ,t.lesson_num";
		$sql .= " ,t.unit_num";
		$sql .= " ,t.unit_key";
		$sql .= " ,t.block_num";
		$sql .= " ,t.skill_num";
		$sql .= " ,t.package_course_num";
		$sql .= " ,t.package_stage_num";
		$sql .= " ,t.package_lesson_num";
		$sql .= " ,t.package_unit_num";
		$sql .= " ,t.package_block_num";
		$sql .= " ,t.package_list_num";
		$sql .= " ,t.group_category_id";
		$sql .= " ,t.book_id";
		$sql .= " ,t.test_group_id";
		$sql .= " ,t.default_test_num";
		$sql .= " ,t.class_id";
		$sql .= " ,t.test_type_num";
		$sql .= " ,t.test_category1_num";
		$sql .= " ,t.test_category2_num";
		$sql .= " ,t.limit_time";
		$sql .= " ,t.start_date";
		$sql .= " ,t.end_date";
		$sql .= " ,t.start_time";
		$sql .= " ,t.end_time";
		$sql .= " ,t.test_num";
		$sql .= " ,t.add_date";
		$sql .= " ,t.regist_date";
		$sql .= " ,t.option1";
		$sql .= " ,t.option2";
		$sql .= " ,t.start_page";
		$sql .= " ,t.end_page";
		$sql .= " ,t.keyword";
		$sql .= " ,t.list_num";
		$sql .= " ,0 as target_info_group_id";		// クリアする
		$sql .= " ,0 as target_info_list_id";		// クリアする
		$sql .= " ,t.target_info_group_apply_id";
		$sql .= " ,t.target_info_list_apply_id";
		$sql .= " ,t.teacher_edit";
		$sql .= " ,t.auto_flg";
		$sql .= " ,t.auto_correct_answer_rate_from";
		$sql .= " ,t.auto_correct_answer_rate_to";
		$sql .= " ,t.auto_never_learned";
		$sql .= " ,t.auto_not_add_target";
		$sql .= " ,t.auto_retest";
		$sql .= " ,t.auto_all_target";
		$sql .= " ,t.auto_target_period";
		$sql .= " ,t.auto_target_max_minutes";
		$sql .= " ,t.auto_target_date_from";
		$sql .= " ,t.auto_target_date_to";
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
		$sql .= " ORDER BY t.target_study_list_id ";
		$sql .= ";";

		$db_saki->exec_query($sql);

		// 連番の情報を関連テーブルに更新する
		$update_count = 0;
		$key_count = 0;
		$sql = "";
		$elt = "";
		$elt_data = "";
		$where = "";
		for ($i = $start_point; $i < $end_point; $i++) {

			// update準備
			if ($sql == "") {
				$sql  = " UPDATE mv_6_target_study_list ";
				$sql .= " SET  ";

				$elt = "new_target_study_list_id = ELT(FIELD(target_study_list_id,";

				$elt_data = "),";

				$where = ") WHERE target_study_list_id IN (";
			}

			// キー部分追加
			$elt .= $target_study_list_list[$key_count].",";
			$where .= $target_study_list_list[$key_count].",";

			// データ部分追加
			$elt_data .= "'".($i+1)."',";

			// 1000件ごとにUPDATE
			if ($update_count == 1000) {

				// 一番右のカンマを除去
				$elt = substr($elt, 0, -1);
				$where = substr($where, 0, -1);
				$elt_data = substr($elt_data, 0, -1);

				// SQL作成
				$sql = $sql.$elt.$elt_data.$where.");";
				// update実行
				$db_saki->exec_query($sql);

// echo $sql."\n";

				// 変数クリア
				$update_count = 0;
				$sql  = "";
				$elt = "";
				$elt_data = "";
				$where = "";

				// 負荷が上がらない様に0.1秒待つ
				usleep(100000);
			}
			$update_count++;
			$key_count++;
		}

		// 1000件未満の情報をUPDATE
		if ($update_count > 0) {
			// 一番右のカンマを除去
			$elt = substr($elt, 0, -1);
			$where = substr($where, 0, -1);
			$elt_data = substr($elt_data, 0, -1);

			// SQL作成
			$sql = $sql.$elt.$elt_data.$where.");";
			// update実行
			$db_saki->exec_query($sql);
// echo $sql."\n";
		}
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN target_study_group tsg ON tsg.target_study_group_id = t.target_study_group_id AND tsg.move_flg = 0 ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = tsg.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '1' ";
		$sql .= " ,t.mk_tts_id = 'mvent' ";
		$sql .= " ,t.mk_date = now() ";
		$sql .= ";";
		$db_moto->exec_query($sql);

		$sql  = " UPDATE ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN target_study_group tsg ON tsg.target_study_group_id = t.target_study_group_id AND tsg.move_flg = 0 ";
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
