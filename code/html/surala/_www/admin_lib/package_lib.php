<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 速習コース共通ルーチン
 * 主にエラーチェックルーチン
 *
 * @author Azet
 */


/**
 * カテゴリー存在チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 * @param integer $pk_course_num=0
 * @param integer $pk_stage_num=0
 * @param integer $pk_lesson_num=0
 * @param integer $pk_unit_num=0
 * @param integer $pk_block_num=0
 */
function pakage_cat_check(&$ERROR, $pk_course_num=0, $pk_stage_num=0, $pk_lesson_num=0, $pk_unit_num=0, $pk_block_num=0) {

	$count = 0;
	//	リスト階層
	if ($pk_block_num > 0) {
		$count = pakage_list_check($pk_block_num);
		if ($count > 0) {
			$ERROR[] = "下層にカテゴリー（リスト）が登録されている為、カテゴリータイプを変更出来ません。";
		}
		return;
	}

	//	ブロック階層
	if ($pk_unit_num > 0) {
		$count = pakage_block_check($pk_unit_num);
		if ($count > 0) {
			$ERROR[] = "下層にカテゴリー（ブロック）が登録されている為、カテゴリータイプを変更出来ません。";
		}
		return;
	}

	//	ユニット階層
	if ($pk_lesson_num > 0) {
		$count = pakage_unit_check($pk_lesson_num);
		if ($count > 0) {
			$ERROR[] = "下層にカテゴリー（ユニット）が登録されている為、カテゴリータイプを変更出来ません。";
		}
		return;
	}

	//	レッスン階層
	if ($pk_stage_num > 0) {
		$count = pakage_lesson_check($pk_stage_num);
		if ($count > 0) {
			$ERROR[] = "下層にカテゴリー（レッスン）が登録されている為、カテゴリータイプを変更出来ません。";
		}
		return;
	}

	//	ステージ階層
	if ($pk_course_num > 0) {
		$count = pakage_stage_check($pk_course_num);
		if ($count > 0) {
			$ERROR[] = "下層にカテゴリー（ステージ）が登録されている為、カテゴリータイプを変更出来ません。";
		}
		return;
	}
}


/**
 * ステージ階層チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $pk_course_num
 * @return integer
 */
function pakage_stage_check($pk_course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$count = 0;
	$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_STAGE.
			" WHERE pk_course_num='".$pk_course_num."'".
			" AND mk_flg!='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}

	return $count;
}


/**
 * レッスン階層チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $pk_stage_num
 * @return integer
 */
function pakage_lesson_check($pk_stage_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$count = 0;
	$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_LESSON.
			" WHERE pk_stage_num='".$pk_stage_num."'".
			" AND mk_flg!='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}

	return $count;
}


/**
 * ユニット階層チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $pk_lesson_num
 * @return integer
 */
function pakage_unit_check($pk_lesson_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$count = 0;
	$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_UNIT.
			" WHERE pk_lesson_num='".$pk_lesson_num."'".
			" AND mk_flg!='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}

	return $count;
}


/**
 * ブロック階層チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $pk_unit_num
 * @return integer
 */
function pakage_block_check($pk_unit_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$count = 0;
	$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_BLOCK.
			" WHERE pk_unit_num='".$pk_unit_num."'".
			" AND mk_flg!='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}

	return $count;
}


/**
 * リスト階層チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $pk_block_num
 * @return integer
 */
function pakage_list_check($pk_block_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$count = 0;
	$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_LIST.
			" WHERE pk_block_num='".$pk_block_num."'".
			" AND mk_flg!='1';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}

	return $count;
}
?>