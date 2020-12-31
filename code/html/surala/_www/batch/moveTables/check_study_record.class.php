<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class check_study_record extends baseTable {

	public function getTableName() {
		return "check_study_record";
	}

	/**
	 * 移動元のデータを抽出（evacデータが存在する場合は、UNIONします）
	 * ※対象テーブルのキーはstudent_idだが実際のデータはteacher_idが格納されているので、
	 * 　JOINするテーブルはmv_2_teacherです
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_2_teacher s ON ".$this->getTableName()."_evac.student_id = s.teacher_id";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_2_teacher s ON ".$this->getTableName().".student_id = s.teacher_id";

		return $sql;
	}

	/**
	 * 移動先のデータを消す（念のため）
	 */
	public function getDeleteSQL() {
		$del_list = array();
		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN mv_2_teacher m ON m.teacher_id = t.teacher_id ";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_2_teacher m ON m.teacher_id = t.teacher_id ";
		$del_list[] = $sql;

		return $del_list;
	}

	// AUTO_INCRIMENTが有るので、フィールドを抜いてINSERTする
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as check_study_record_num";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,db_id";
		$sql .= " ,school_id";
		$sql .= " ,student_id";
		$sql .= " ,cr_num";
		$sql .= " ,seat_num";
		$sql .= " ,class_m";
		$sql .= " ,course_num";
		$sql .= " ,unit_num";
		$sql .= " ,block_num";
		$sql .= " ,review_unit_num";
		$sql .= " ,problem_num";
		$sql .= " ,display_problem_num";
		$sql .= " ,answer";
		$sql .= " ,success";
		$sql .= " ,answer_time";
		$sql .= " ,again";
		$sql .= " ,passing";
		$sql .= " ,regist_time";
		$sql .= " ,review";
		$sql .= "  from MV_".$this->getTableName();

		return $sql;
	}

	/**
	 * 移動元のデータに削除フラグを立てる(削除フラグは無し)
	 */
	//public function deleteData($db_moto) {
	//}

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		return null;
	}
}
