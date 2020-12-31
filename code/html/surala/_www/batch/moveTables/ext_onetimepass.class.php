<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class ext_onetimepass extends baseTable {

	public function getTableName() {
		return "ext_onetimepass";
	}

	/**
	 * 移動元のデータを抽出（evacデータが存在する場合は、UNIONします）
	 */
	public function getSQL() {
		$sql = "";
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
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_1_school m ON m.school_id = t.school_id ";
		$del_list[] = $sql;

		return $del_list;
	}

	// AUTO_INCRIMENTが有るので、フィールドを抜いてINSERTする
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select ";
		$sql .= "  0 as login_num";		// auto_incrementに0を指定すると附番してくれる
		$sql .= " ,user_id";
		$sql .= " ,school_id";
		$sql .= " ,user_type";
		$sql .= " ,gib_rnki_kb";
		$sql .= " ,pass";
		$sql .= " ,user_info";
		$sql .= " ,regist_time";
		$sql .= "  from MV_".$this->getTableName();

		return $sql;
	}

	/**
	 * 移動元のデータに削除フラグを立てる(削除フラグ無し)
	 */
// 	public function deleteData($db_moto) {
// 	}

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		return null;
	}

}
