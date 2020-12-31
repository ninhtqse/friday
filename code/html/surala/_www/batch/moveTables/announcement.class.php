<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class announcement extends baseTable {

	public function getTableName() {
		return "announcement";
	}

	/**
	 * 移動元のデータを抽出（evacデータが存在する場合は、UNIONします）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName()."_evac.* FROM ".$this->getTableName()."_evac ".$this->getTableName()."_evac ";
		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName()."_evac.school_id = s.school_id";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_1_school s ON ".$this->getTableName().".school_id = s.school_id";

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
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$del_list[] = $sql;

		return $del_list;
	}

	// AUTO_INCRIMENTが有るので、フィールドを抜いてINSERTする
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as announcement_num";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,school_id";
		$sql .= " ,user_id";
		$sql .= " ,user_level";
		$sql .= " ,address_id";
		$sql .= " ,address_type";
		$sql .= " ,address_level";
		$sql .= " ,start_date";
		$sql .= " ,end_date";
		$sql .= " ,honbu_announce_num";
		$sql .= " ,subject";
		$sql .= " ,message";
		$sql .= " ,regist_date";
		$sql .= " ,update_date";
		$sql .= " ,display";
		$sql .= " ,state";
		$sql .= "  from MV_".$this->getTableName();

		return $sql;
	}

	/**
	 * 移動元のデータに削除フラグを立てる
	 */
	public function deleteData($db_moto) {
		$sql  = " UPDATE ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.state = '1' ";
		$sql .= ";";
		$db_moto->exec_query($sql);

		$sql  = " UPDATE ".$this->getTableName()."_evac t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$sql .= " SET  ";
		$sql .= "  t.state = '1' ";
		$sql .= ";";
		$db_moto->exec_query($sql);
	}

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		$sql  = " UPDATE MV_".$this->getTableName()." t ";
		$sql .= " SET  ";
		$sql .= "  t.state = '0' ";
		$sql .= ";";
		return $sql;
	}

}
