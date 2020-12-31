<?php

require_once('/data/bat/moveTables/baseTable.class.php');

class study_record extends baseTable {

	public function getTableName() {
		return "study_record";
	}

	/**
	 * 移動元のデータを抽出
	 */
	public function getSQL() {
		$sql = "";
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
	 * 移動元のデータに削除フラグを立てる（削除フラグ無し）
	 */
// 	public function deleteData($db_moto) {
// 	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
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
		$sql .= "   count(study_record_num) as rec_count ";
		$sql .= " FROM mv_7_study_record ";
		$sql .= ";";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$rec_count = $list['rec_count'];
			}
		}

//echo $db_saki->dbname."\n";
//echo $sql."\n";

		// 移動先のstudy_recordのauto_incrimentの値を取得する
		$sql   = " SELECT auto_increment FROM information_schema.tables ";
		$sql  .= " WHERE table_schema='".$db_saki->dbname."' and table_name = '".$this->getTableName()."';";

//echo $sql."\n";

		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$start_point = $list['auto_increment'];
			}
		}
		$db_saki->free_result($rs);

		$end_point = $start_point + $rec_count;
		$auto_increment = $end_point + 100;			// 念のため、100件の余裕を取る

		// 移動先のstudy_recordのauto_incrimentの値を更新する
		$sql  = " ALTER TABLE ".$this->getTableName()." AUTO_INCREMENT=".$auto_increment.";";
		$db_saki->exec_query($sql);

//echo $sql."\n";

		// 配列クリア
		$study_record_num_list = array();

		// ID取得
		$sql  = " SELECT";
		$sql .= "   study_record_num ";
		$sql .= " FROM mv_7_study_record ";
		$sql .= " ORDER BY study_record_num ";
		$sql .= ";";
		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$study_record_num_list[] = $list['study_record_num'];
			}
		}
		$db_saki->free_result($rs);

//echo $sql."\n";

		// SELECT INSERTする 連番は変数を使用して附番する
		$sql  = "set @rownum = ".$start_point.";";
		$db_saki->exec_query($sql);

//echo $sql."\n";

		// 取得した管理番号をSELECT/INSERTする
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  @rownum:=@rownum+1 as study_record_num";
		$sql .= " ,db_id";
		$sql .= " ,school_id";
		$sql .= " ,student_id";
		$sql .= " ,cr_num";
		$sql .= " ,seat_num";
		$sql .= " ,class_m";
		$sql .= " ,course_num";
		$sql .= " ,unit_num";
		$sql .= " ,block_num";
		$sql .= " ,review";
		$sql .= " ,review_unit_num";
		$sql .= " ,problem_num";
		$sql .= " ,display_problem_num";
		$sql .= " ,answer";
		$sql .= " ,answer2";
		$sql .= " ,success";
		$sql .= " ,answer_time";
		$sql .= " ,again";
		$sql .= " ,passing";
		$sql .= " ,regist_time";
		$sql .= " ,hantei_default_num";
		$sql .= " ,move_flg";
		$sql .= " ,move_tts_id";
		$sql .= " ,move_date";
		$sql .= "  from MV_".$this->getTableName();
		$sql .= " ORDER BY study_record_num ";
		$sql .= ";";

		$db_saki->exec_query($sql);

//echo $sql."\n";

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
				$sql  = " UPDATE mv_7_study_record ";
				$sql .= " SET  ";

				$elt = "new_study_record_num = ELT(FIELD(study_record_num,";

				$elt_data = "),";

				$where = ") WHERE study_record_num IN (";
			}

			// キー部分追加
			$elt .= $study_record_num_list[$key_count].",";
			$where .= $study_record_num_list[$key_count].",";

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

//echo $sql."\n";

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
//echo $sql."\n";
		}
	}
}
