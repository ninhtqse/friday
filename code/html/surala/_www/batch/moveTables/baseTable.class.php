<?php

class baseTable
{

	var $enterprise_id = "";

	//初期設定
	function __construct($enterprise_id){
		$this->enterprise_id = $enterprise_id;
	}

	public function getTableName() {
		return "";		// 各クラステーブル名を記載する
	}

	public function getSQL() {
		return "";		// 各クラスで抽出のSQLを記載する
	}

	public function getDeleteSQL() {
		return "";		// 各クラスで抽出のSQLを記載する
	}

	/**
	 *  単純にSELECT/INSERTする場合はオーバライドは不要
	 *  AUTO_INCRIMENTを利用しているテーブルや、
	 *  特殊な場合は、各クラスでオーバライドする
	 */
	public function getSelectInsertSQL() {
		$sql  = "insert into ".$this->getTableName();
		$sql .= " select * from MV_".$this->getTableName();
		return $sql;
	}

	/**
	 * 拡張処理
	 * getSelectInsertSQL()を行わずにこちらを起動させる
	 * @param object $db_saki
	 */
	public function extendInsert($db_saki) {
	}

	/**
	 * 拡張処理
	 * getDeleteSQL()を行わずにこちらを起動させる
	 * @param object $db_saki
	 */
	public function extendDelete($db_saki) {
	}


	/**
	 * リランした時の削除フラグをクリアする処理
	 * (削除フラグを持っていないテーブル or 列名が違うテーブルはオーバーライドする)
	 */
	public function getUpdateMkflg() {
		$sql  = " UPDATE MV_".$this->getTableName()." t ";
		$sql .= " SET  ";
		$sql .= "  t.mk_flg = '0' ";
		$sql .= " ,t.mk_tts_id = null ";
		$sql .= " ,t.mk_date = null ";
		$sql .= " WHERE t.mk_tts_id = 'mvent'";
		$sql .= ";";
		return $sql;
	}


	/**
	 * 作業テーブル削除
	 * 後続のテーブルで利用する場合は、オーバーライドしてDROP文を返却する
	 * @param object $db_saki
	 */
	public function dropTable($db_saki) {

		$sql = "DROP TABLE IF EXISTS MV_".$this->getTableName()."; ";
//echo $sql."\n";
		$db_saki->exec_query($sql);

		return null;
	}

	/**
	 * データ削除処理
	 * 元データのデータに削除フラグを立てる
	 * @param object $db_moto
	 */
	public function deleteData($db_moto) {

	}

	/**
	 * データを抽出してDUMPして移動先に移動する
	 * @param array $db_moto_info DB接続情報（移動元）
	 * @param object $db_moto
	 */
	public function export($db_moto_info, $db_moto){

		// 移動用テーブルクリア
		$sql = "DROP TABLE IF EXISTS MV_".$this->getTableName()."; ";
//echo $sql."\n";
		$db_moto->exec_query($sql);

		$enterprise_list = array();
		$sql  = $this->getSQL();
		$sql .= " LIMIT 1;";
//echo $sql."\n";
		if($rs = $db_moto->query($sql)){
			while($list = $db_moto->fetch_assoc($rs)) {
				$enterprise_list[] = $list;
			}
		}
		$db_moto->free_result($rs);

		if (count($enterprise_list) > 0) {

			// 移動用テーブル作成
			$sql  = " CREATE TABLE MV_".$this->getTableName()." ";
			$sql .= $this->getSQL();
			$sql .= ";";
//echo $sql."\n";
			$db_moto->exec_query($sql);

			$this->dumpCommand($db_moto_info);

			// 移動用テーブルクリア
			$sql = "DROP TABLE IF EXISTS MV_".$this->getTableName()."; ";
			//echo $sql."\n";
			$db_moto->exec_query($sql);

			return true;
		}

		return false;
	}

	/**
	 * 移動先のデータを削除する
	 * @param object $db_saki
	 */
	public function delete($db_saki){

		// 移動用テーブルクリア
		$sql_list = $this->getDeleteSQL();

		if (is_array($sql_list) && count($sql_list) > 0) {
			foreach ($sql_list as $sql) {
//echo $sql."\n";
				$db_saki->exec_query($sql);
			}
		} else {
			$this->extendDelete($db_saki);
		}

		return;
	}

	/**
	 * 移動先のデータを追加する
	 * @param object $db_saki
	 */
	public function add($db_saki){

		$sql = $this->getUpdateMkflg();

		if ($sql) {
			//echo $sql."\n";
			$db_saki->exec_query($sql);
		}

		// SELECT->INSERTを実行する
		$sql = $this->getSelectInsertSQL();
		if ($sql) {
//echo $sql."\n";
			$db_saki->exec_query($sql);
		}
		// 特殊な処理を実行
		$this->extendInsert($db_saki);

		return;
	}

	/**
	 * DUMPコマンド発行
	 * @param array $db_info DB接続（移動元）
	 */
	public function dumpCommand($db_info) {

		$LIST = null;
		$ret = null;
		$return = array();

		$file = "/data/bat/move_enterprise/data/".$this->enterprise_id."_MV_".$this->getTableName().".sql";

		$commnad  = "mysqldump -u ".$db_info['DBUSER'];
		$commnad .= " -p".$db_info['DBPASSWD'];
		$commnad .= " -h ".$db_info['DBHOST'];
		$commnad .= " --single-transaction --quick --extended-insert ";
		$commnad .= " ".$db_info['DBNAME']." MV_".$this->getTableName();
		$commnad .= " > ".$file;

		exec($commnad, $LIST, $ret);

		$return[0] = $ret;
		$return[1] = $commnad;

		return $return;
	}



	/**
	 * リストアコマンド発行
	 * @param array $db_info DB接続（移動先）
	 */
	public function dumpRestore($db_info) {

		$LIST = null;
		$ret = null;
		$return = array();

		$file = "/data/bat/move_enterprise/data/".$this->enterprise_id."_MV_".$this->getTableName().".sql";

		// dumpファイルが存在したら処理開始
		if (file_exists($file)) {

			$commnad  = "mysql -u ".$db_info['DBUSER'];
			$commnad .= " -p".$db_info['DBPASSWD'];
			$commnad .= " -h ".$db_info['DBHOST'];
			$commnad .= " ".$db_info['DBNAME'];
			$commnad .= " < ".$file;

			exec($commnad, $LIST, $ret);

			$return[0] = $ret;
			$return[1] = $commnad;

			// 移動に使用したファイルは削除
			unlink($file);
		}

		return $return;
	}


}
