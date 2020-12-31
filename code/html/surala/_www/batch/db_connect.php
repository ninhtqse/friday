<?php

// データベース接続保持変数
$cdb = null;	// 分散DB接続
$scdb = null;	// 総合DB接続

/**
 * ＤＢ接続クラス
 *
 * メソッド一覧
 * 1.set_db($VAL)
 * 2.set_connect_db()
 * 3.set_db_define()
 * 4.get_db()
 * 5.set_sogo_db_define()
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author azet
 */
class connect_db {

	var $dbhost = "";			// DBホスト
	var $dbname = "";			// DB名
	var $dbuser = "";			// DB接続ユーザ
	var $dbpasswd = "";			// DB接続パスワード
	var $dbport = "";			// DB接続ポート
	var $db = "";				// DB接続オブジェクト

	/**
	 * 接続設定
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param array $VAL DB接続情報配列(DBHOST:DBホスト,DBNAME:DB名,DBUSER:DB接続ユーザ,DBPASSWD:DB接続パスワード,DBPORT:DB接続ポート
	 */
	function set_db($VAL) {
		if ($VAL['DBHOST'])   { $this->dbhost   = $VAL['DBHOST']; }
		if ($VAL['DBNAME'])   { $this->dbname   = $VAL['DBNAME']; }
		if ($VAL['DBUSER'])   { $this->dbuser   = $VAL['DBUSER']; }
		if ($VAL['DBPASSWD']) { $this->dbpasswd = $VAL['DBPASSWD']; }
		if ($VAL['DBPORT'])   { $this->dbport   = $VAL['DBPORT']; }
	}

	/**
	 * DB接続
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @return  エラーメッセージ（エラーが存在するときのみ設定）
	 */
	function set_connect_db() {
		//if ($this->dbport){ $this->dbhost .= ":".$this->dbport; }		// 接続ポート番号は mysqliオブジェクト生成時の第5引数で設定する
		// ＤＢ接続クラス生成
		$this->db = @new mysqli($this->dbhost, $this->dbuser, $this->dbpasswd, $this->dbname, (int)$this->dbport);

		// クラス生成チェック
		if (!$this->db || $this->db->connect_errno != 0) {
			$ERROR[] = "DBに接続できません。";
		} else{
			// 文字コード設定
			$this->db->set_charset("utf8");				//update hasegawa ハイフンなしのutf8と指定しないと、正常に設定されない
		}
		return $ERROR;
	}

	/**
	 * escape処理								//add hasegawa
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $str エスケープ前の文字列
	 * @return string エスケープ済みの文字列
	 */
	function real_escape($str) {
		$str_w = $str;
		if ($this->db) {
			$str_w = $this->db->real_escape_string($str);
		}
		return $str_w;
	}

//  定数化できないので使用しない
//	/**
//	 * DB define設定
//	 *
//	 * AC:[C]共通 UC1:[C99]その他.
//	 *
//	 * @author azet
//	 */
//	function set_db_define() {
////		if ($this->db) {define('DB',$this->db); }			//保留
//		if ($this->dbname) { define('DBNAME',"$this->dbname"); }
//	}

// db接続オブジェクトは、プライベートプロパティとして使用するので、取得しない様にする
//	/**
//	 * DB 取得
//	 *
//	 * AC:[C]共通 UC1:[C99]その他.
//	 *
//	 * @author azet
//	 * @return object DBコネクションオブジェクト
//	 */
//	function get_db() {
//		return $this->db;
//	}

// db接続名は、プライベートプロパティとして使用するので、取得しない様にする
//	/**
//	 * DBNAME 取得
//	 *
//	 * AC:[C]共通 UC1:[C99]その他.
//	 *
//	 * @author azet
//	 * @return string DB名
//	 */
//	function get_dbname() {
//		return $this->dbname;
//	}

//  定数化できないので使用しない
//	/**
//	 * DB 総合サーバdefine設定
//	 *
//	 * AC:[C]共通 UC1:[C99]その他.
//	 *
//	 * @author azet
//	 */
//	function set_sogo_db_define() {
////		if ($this->db) { define('SDB',$this->db); }			//保留
//		if ($this->dbname) { define('SDBNAME',$this->dbname); }
//	}

	/**
	 * DB クローズ処理
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 */
	function close() {
		// DB クローズ処理
		if ($this->db) {
			$this->db->close();
		}
	}

	/**
	 * オートインクリメント値取得
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @return number オートインクリメント値
	 */
	function insert_id() {
		$auto_inc = 0;
		if ($this->db) {
			$auto_inc = $this->db->insert_id;
		}
		return $auto_inc;
	}

	/**
	 * 更新件数取得
	 * UPDATEまたはDELETE処理後の件数を取得する
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @return number オートインクリメント値
	 */
	function affected_rows() {
		$affected_rows = 0;
		if ($this->db) {
			$affected_rows = $this->db->affected_rows;
		}
		return $affected_rows;
	}

	/**
	 * エラーNo取得
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @return number エラーNo
	 */
	function error_no() {
		$error_no = 0;
		if ($this->db) {
			$error_no = $this->db->connect_errno;
		}
		return $error_no;
	}

	/**
	 * エラー情報取得
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @return string エラーメッセージ
	 */
	function error_message() {
		$error_message = "";
		if ($this->db) {
			$error_message = $this->db->connect_error;
		}
		return $error_message;
	}

	/**
	 * クエリ発行（レコードセット返却）
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $sql SQL文
	 * @return object 結果セットオブジェクト
	 */
	function query($sql) {
		$result = NULL;
		if ($sql) {
			$result = $this->db->query($sql);
		}
		return $result;
	}

	/**
	 * クエリ発行（SQL実行のみ）
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $sql SQL文
	 * @return boolean true:クエリ成功 false:クエリ失敗
	 */
	function exec_query($sql) {
		$exec_flag = false;
		if ($sql) {
			$exec_flag = $this->db->query($sql);
		}
		return $exec_flag;
	}

	/**
	 * レコードセット取得
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param object $result 結果セットオブジェクト
	 * @return object レコードセット（データベースの１行の情報）
	 */
	function fetch_assoc($result) {
		$row = array();							// update oda 2016/02/22 初期値変更 NULL -> array();
		if ($result) {
			$row = $result->fetch_assoc();
		}
		return $row;
	}

	/**
	 * レコード件数取得
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param object $result 結果セットオブジェクト
	 * @return number レコード件数
	 */
	function num_rows($result) {
		$rows = 0;
		if ($result) {
			$rows = $result->num_rows;
		}
		return $rows;
	}

	/**
	 * データシーク（指定行番号まで移動する）
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param object $result 結果セットオブジェクト
	 * @param number $row_no 行番号
	 * @return object 結果セットオブジェクト（指定行まで移動済）
	 */
	function data_seek($result, $row_no) {
		if ($result) {
			$result->data_seek($row_no);
		}
		return $result;
	}

	/**
	 * 結果セットオブジェクト解放
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param object $result 結果セットオブジェクト
	 */
	function free_result($result) {
		if ($result) {
			mysqli_free_result($result);
		}
	}

	/**
	 * INSERT実行（1行登録用）
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $table_name テーブル名
	 * @param array $datas 配列のキーにカラム名を設定し、配列の値にDB更新内容を設定する
	 * @return array エラー情報
	 */
	function insert($table_name, $datas) {

		// SQL文生成開始
		$sql = "INSERT INTO $table_name";

		// テーブル名チェック
		if (!$table_name) {
			$ERROR[] = "The table name is not set.";
		}

		// 設定項目チェック
		if (is_array($datas)) {

			// SQL文生成
			$column = " (";
			$values = " VALUES(";
			$i = 0;
			foreach ($datas as $key => $val) {
				if ($i != "0") { $column .= ","; $values .= ","; }
				if ($val != "now()" && $val != "NULL") { $values .= "'"; }
				$column .= "$key";
				$values .= "$val";
				if ($val != "now()" && $val != "NULL") { $values .= "'"; }
				$i = 1;
			}
			$column .= ")";
			$values .= ")";
			$sql = $sql . $column . $values . ";";

		} else {
			$ERROR[] = "DATA ERROR";
		}

		// SQL文実行
		if (!$ERROR) {
			if (!$this->exec_query($sql)) {
				$ERROR[] = "SQL INSERT ERROR<br>$sql";
			}
		}

		return $ERROR;
	}

	/**
	 * UPDATE実行
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $table_name テーブル名
	 * @param array $datas 配列のキーにカラム名を設定し、配列の値にDB更新内容を設定する
	 * @param string $where 更新対象の検索条件
	 * @return array エラー情報
	 */
	function update($table_name, $datas, $where) {

		// SQL文生成開始
		$sql = "UPDATE $table_name SET";

		// テーブル名チェック
		if (!$table_name) { $ERROR[] = "The table name is not set."; }

		// 設定項目チェック
		if (is_array($datas)) {

			// SQL文生成
			$i = 0;
			foreach ($datas as $key => $val) {
				if ($i != "0") { $column = ","; }
				if ($val != "now()" && $val != "NULL") { $values = "'"; }
				$column .= "$key";
				$values .= "$val";
				if ($val != "now()" && $val != "NULL") { $values .= "'"; }
				$set_data .= " " . $column . "=" . $values;
				$i = 1;
				$column = $values = "";
			}
			$sql = $sql . $set_data . $where;

		} else {
			$ERROR[] = "DATA ERROR";
			print ($sql);
		}

		// SQL文実行
		if (!$ERROR) {
			if (!$this->exec_query($sql)) {
				$ERROR[] = "SQL UPDATE ERROR<br>$sql";
			}
		}
		return $ERROR;
	}

	/**
	 * INSERT実行(複数行)
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $table_name テーブル名
	 * @param array $datas インサートデータ情報 $datas[レコード件数][列名] = 値
	 * @param number $inc_rows インサート件数 1以上を指定する事（0を指定すると１回で全てのデータをインサートしちゃいます）
	 * @return array エラー情報
	 */
	function insert_all($table_name, $datas, $inc_rows ){

		// テーブル名チェック
		if (!$table_name) {
				$ERROR[] = "The table name is not set.";
			}

		// データチェック
		if (!is_array($datas)) {
				$ERROR[] = "DATA ERROR";
		}else{

			$rec_count = 0;
			$values = "";


			foreach($datas as $data => $value){

				if ($values != "") { $values .= ","; }
				else { $values = " VALUES"; }

				$i = 0;
				$values .= "(";

				//データの行を生成
				foreach($value as $val){
					if ($i != "0") { $values .= ","; }
					if ($val != "now()" && $val != "NULL") { $values .= "'"; }
					$values .= "$val";
					if ($val != "now()" && $val != "NULL") { $values .= "'"; }
					$i = 1;
				}
				$values .= ")";
				$rec_count++;

				// $inc_rows件毎にインサートする
				if($rec_count >= $inc_rows){

					$sql = "INSERT INTO $table_name";
					$i = 0;

					// カラムの生成
					$columns = " (";
					foreach($datas[0] as $col => $value){
						if ($i != "0") { $columns .= ","; }
						$columns .= "$col";
						$i = 1;
					}
					$columns .= ")";
					$sql = $sql . $columns . $values . ";";

					// SQL文実行
					if (!$ERROR) {
						if (!$this->exec_query($sql)) {
							$ERROR[] = "SQL INSERT ERROR<br>$sql";
						}
					}

					$sql = "";
					$columns = "";
					$values = "";
					$rec_count = 0;

				}
			}

			//$inc_rows件以下の残りをインサートする
			if ($rec_count > 0) {

					$sql = "INSERT INTO $table_name";
					$i = 0;
					$columns = " (";
					foreach($datas[0] as $col => $value){
						if ($i != "0") { $columns .= ","; }
						$columns .= "$col";
						$i = 1;
					}
					$columns .= ")";
					$sql = $sql . $columns . $values . ";";
					// SQL文実行
					if (!$ERROR) {
						if (!$this->exec_query($sql)) {
							$ERROR[] = "SQL INSERT ERROR<br>$sql";
						}
					}
			}
		}
		return $ERROR;
	}

	/**
	 * DELETE実行
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $table_name テーブル名
	 * @param string $where 削除対象の検索条件
	 * @return array エラー情報
	 */
	function delete($table_name,$where) {

		// SQL文生成開始
		$sql = "DELETE FROM $table_name";

		// テーブル名チェック
		if (!$table_name) { $ERROR[] = "The table name is not set."; }

		// 検索条件が有る時はDELETE文を生成する
		// 検索条件が無い時はTRANCATE文を生成する
		if ($where) {
			$sql .= $where;
			$code = "DELETE";
		} else {
			$sql = "TRUNCATE TABLE $table_name";
			$code = "TRUNCATE";
		}

		// SQL文実行
		if (!$ERROR) {
			if (!$this->exec_query($sql)) {
				$ERROR[] = "SQL $code ERROR<br>$sql";
			}
		}

		return $ERROR;
	}

	/**
	 * REPLACE実行
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author azet
	 * @param string $table_name テーブル名
	 * @param array $datas 配列のキーにカラム名を設定し、配列の値にDB更新内容を設定する
	 * @return array エラー情報
	 */
	function replace($table_name,$datas) {

		// SQL文生成開始
		$sql = "REPLACE INTO $table_name";

		// テーブル名チェック
		if (!$table_name) { $ERROR[] = "The table name is not set."; }

		// 設定項目チェック
		if (is_array($datas)) {

			// SQL文生成
			$column = " (";
			$values = " VALUES(";
			$i = 0;
			foreach ($datas as $key => $val) {
				if ($i != "0") { $column .= ","; $values .= ","; }
				if ($val != "now()" && $val != "NULL") { $values .= "'"; }
				$column .= "$key";
				$values .= "$val";
				if ($val != "now()" && $val != "NULL") { $values .= "'"; }
				$i = 1;
			}
			$column .= ")";
			$values .= ")";
			$sql = $sql . $column . $values . ";";

		} else {
			$ERROR[] = "DATA ERROR";
		}

		// SQL文実行
		if (!$ERROR) {
			if (!$this->exec_query($sql)) {
				$ERROR[] = "SQL REPLACE ERROR<br>$sql";
			}
		}

		return $ERROR;
	}

}
?>
