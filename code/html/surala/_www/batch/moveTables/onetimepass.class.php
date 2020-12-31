<?php
require_once('/data/bat/moveTables/baseTable.class.php');

class onetimepass extends baseTable {

	public function getTableName() {
		return "onetimepass";
	}

	/**
	 * 移動元のデータを抽出（先生と保護者が存在する為、UNIONします）
	 */
	public function getSQL() {
		$sql = "";
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_2_teacher s ON ".$this->getTableName().".member_id = s.teacher_id";
		$sql .= " WHERE ".$this->getTableName().".move_flg = 0";
		$sql .= " UNION ALL ";												// UNION ALLで順番になる
		$sql .= " SELECT ".$this->getTableName().".* FROM ".$this->getTableName()." ".$this->getTableName()." ";
		$sql .= " INNER JOIN mv_4_guardian s ON ".$this->getTableName().".member_id = s.guardian_id";
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
		$sql .= " INNER JOIN mv_2_teacher m ON m.teacher_id = t.member_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		$sql = "";
		$sql .= " DELETE t FROM ".$this->getTableName()." t ";
		$sql .= " INNER JOIN mv_4_guardian m ON m.guardian_id = t.member_id ";
		$sql .= " WHERE t.move_flg = 0";
		$del_list[] = $sql;

		return $del_list;
	}

	/**
	 * 移動元のデータに削除フラグを立てる（削除フラグ無し）
	 */
// 	public function deleteData($db_moto) {
// 	}

	// getSelectInsertSQL 単純移行なのでオーバライドしません

	// extendInsert 単純移行なのでオーバライドしません

	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		return null;
	}

}
