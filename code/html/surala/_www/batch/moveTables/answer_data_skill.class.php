<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class answer_data_skill extends baseTable {

	public function getTableName() {
		return "answer_data_skill";
	}

	/**
	 * 移動元のデータを抽出（evacデータは別クラスで処理します：AUTOINCRIMENTが無い為）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_3_student s ON ".$this->getTableName().".student_id = s.student_id";
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
		$sql .= " INNER JOIN mv_3_student m ON m.student_id = t.student_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		return $del_list;
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
		$sql .= " WHERE t.move_flg = 0";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	/**
	 * study_recordの番号を更新
	 *
	 * @see baseTable::extendInsert()
	 */
	public function extendInsert($db_saki) {

		// 新旧のstudy_record_numを取得する
		$sql = "";
		$sql .= " SELECT m.*, s.student_id FROM mv_7_study_record m ";
		$sql .= " INNER JOIN study_record s ON s.study_record_num = m.new_study_record_num ";
		$sql .= ";";
		$sql .= ";";

		$L_NEW_STUDY_RECORD_NUM = array();
		if($rs = $db_saki->query($sql)){
			while($list = $db_saki->fetch_assoc($rs)) {
				$L_NEW_STUDY_RECORD_NUM[$list['student_id']][$list['study_record_num']] = $list['new_study_record_num'];
			}
		}
		$db_saki->free_result($rs);

		// 変換するstudy_record_numが存在したら
		if (count($L_NEW_STUDY_RECORD_NUM) > 0) {

			// 変換対象データ抽出
			$sql = "";
			$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
			$sql .= " INNER JOIN mv_3_student s ON ".$this->getTableName().".student_id = s.student_id";
			$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
			$sql .= ";";

			if($rs = $db_saki->query($sql)){
				while($list = $db_saki->fetch_assoc($rs)) {

					// キー情報保持
					$student_id = $list['student_id'];
					$unit_num = $list['unit_num'];
					$block_num = $list['block_num'];
					$review = $list['review'];

					$answer_data_view = $list['drill_answer_data_view'];

					// 変換テーブルにて文字列置換を行う
					$henkan = false;
					foreach($L_NEW_STUDY_RECORD_NUM[$list['student_id']] AS $old => $new){
						$old_str_length = strlen($old);
						$new_str_length = strlen($new);
						$pattern = 's:9:"answer_id";s:'.$old_str_length.':"'.$old.'";';
						$replacement = 's:9:"answer_id";s:'.$new_str_length.':"'.$new.'";';
						if(preg_match("/".$pattern."/",$answer_data_view)){
							$answer_data_view = preg_replace("/".$pattern."/",$replacement,$answer_data_view);
							$henkan = true;
						}
					}

					// 文字列置換があったら、UPDATEする
					if ($henkan) {
						$sql  = " UPDATE ".$this->getTableName() ." ";
						$sql .= " SET  ";
						$sql .= "  drill_answer_data_view = '".$answer_data_view."' ";
						$sql .= " WHERE student_id = '".$student_id."'";
						$sql .= "   AND unit_num = '".$unit_num."'";
						$sql .= "   AND block_num = '".$block_num."'";
						$sql .= "   AND review = '".$review."'";
						$sql .= ";";
						$db_saki->exec_query($sql);
					}

					// 負荷が上がらない様に0.01秒待つ
					usleep(10000);

				}
			}
			$db_saki->free_result($rs);
		}
	}

}
