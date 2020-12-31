<?
/**
 * ベンチャー・リンク　すらら
 *
 * アナウンス管理　ログ管理
 *
 * @author Azet
 */

/**
 * HTMLコンテンツを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
	}

	$html .= mode_menu();
	if (!MODE) { $html .= member_list(); }
	elseif (MODE == "del") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= check_html($ERROR); }
		} else {
			$html .= check_html($ERROR);
		}
	} elseif (MODE == "member_list") { $html .= member_list(); }
	return $html;
}

/**
 * モード選択メニュ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function mode_menu() {
	$html = <<<EOT
<script type="javascript/text">
function mode_send(mode) {
submit();
}
</script>
<div id="mode_menu">
<table cellpadding=0 cellspacing=0>
<tr>
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="member_list">
<td><input type="submit" value="一覧表示"></td>
</form>
</tr>
</table><br>
</div>

EOT;
	return $html;
}

/**
 * メンバーのリストコンテンツのHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE state!='1'";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if (!$max) {
		$html .= "現在、アナウンスログは存在しません。";
		return $html;
	}

	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>&nbsp;</td>\n";
	$html .= "<td>表示</td>\n";
	$html .= "<td>非表示</td>\n";
	$html .= "<td>合計</td>\n";
	$html .= "<td>&nbsp;</td>\n";
	$html .= "</tr>\n";

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE display='1' AND state!='1'";

	$result = $cdb->query($sql);
	$max_all_1 = $cdb->num_rows($result);

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE display='2' AND state!='1'";

	$result = $cdb->query($sql);
	$max_all_2 = $cdb->num_rows($result);

	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= "<td>アナウンス全体</td>\n";
	$html .= "<td>".$max_all_1."件</td>\n";
	$html .= "<td>".$max_all_2."件</td>\n";
	$html .= "<td>".$max."件</td>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
	$html .= "<input type=\"hidden\" name=\"select\" value=\"all\">\n";
	$html .= "<td><input type=\"submit\" value=\"一括削除\"></td>\n";
	$html .= "</form>\n";
	$html .= "</tr>\n";

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE address_level='5' AND display='1' AND state!='1'";
	$result = $cdb->query($sql);
	$teacher_1 = $cdb->num_rows($result);

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE address_level='5' AND display='2' AND state!='1'";
	$result = $cdb->query($sql);
	$teacher_2 = $cdb->num_rows($result);

	$teacher_all = $teacher_1 + $teacher_2 ;

	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= "<td>先生アナウンス</td>\n";
	$html .= "<td>".$teacher_1."件</td>\n";
	$html .= "<td>".$teacher_2."件</td>\n";
	$html .= "<td>".$teacher_all."件</td>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
	$html .= "<input type=\"hidden\" name=\"select\" value=\"teacher\">\n";
	$html .= "<td><input type=\"submit\" value=\"一括削除\"></td>\n";
	$html .= "</form>\n";
	$html .= "</tr>\n";

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE address_level='6' AND display='1' AND state!='1'";
	$result = $cdb->query($sql);
	$student_1 = $cdb->num_rows($result);

	$sql = "SELECT announcement_num FROM " .
		T_ANNOUNCEMENT . " WHERE address_level='6' AND display='2' AND state!='1'";

	$result = $cdb->query($sql);
	$student_2 = $cdb->num_rows($result);

	$student_all = $student_1 + $student_2 ;

	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= "<td>生徒アナウンス</td>\n";
	$html .= "<td>".$student_1."件</td>\n";
	$html .= "<td>".$student_2."件</td>\n";
	$html .= "<td>".$student_all."件</td>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
	$html .= "<input type=\"hidden\" name=\"select\" value=\"student\">\n";
	$html .= "<td><input type=\"submit\" value=\"一括削除\"></td>\n";
	$html .= "</form>\n";
	$html .= "</tr>\n";

	$html .= "</table>\n";

	return $html;
}

/**
 * 確認フォームを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function check_html($ERROR) {

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "addform") { $val = "add"; }
				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
			$$key = $val;
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
	}

	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	if ($select == "all") { $del_target = "全体"; }
	elseif ($select == "teacher") { $del_target = "先生"; }
	elseif ($select == "student") { $del_target = "生徒"; }


	$html = "確認画面：".$del_target."のアナウンスを本当に一括削除してもよろしいですか？<br><br>";

	if ($ERROR) { $html .= ERROR($ERROR); }

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"radio\" id=\"choice1\" name=\"choice\" value=\"1\"><label for=\"choice1\">全部削除</label> / \n";
	$html .= "<input type=\"radio\" id=\"choice2\" name=\"choice\" value=\"2\"><label for=\"choice2\">表示中を削除</label> / \n";
	$html .= "<input type=\"radio\" id=\"choice3\" name=\"choice\" value=\"3\"><label for=\"choice3\">非表示中を削除</label> <br>\n";
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"clear:left\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";

/*	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	}*/
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * T_ANNOUNCEMENTに変更する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーならば
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (!$_POST["choice"]) { $ERROR[] = "削除する内容を選択してください。"; }
	if ($ERROR) { return $ERROR; }

	if (MODE == "del") {
		$INSERT_DATA[state] = "1";
		$INSERT_DATA[update_date] = "now()";
		if ($_POST[select] == "all") {
			if ($_POST[choice] == "1") {
				$where = " WHERE state='0';";
			} elseif ($_POST[choice] == "2") {
				$where = " WHERE display='1' AND state='0';";
			} elseif ($_POST[choice] == "3") {
				$where = " WHERE display='2' AND state='0';";
			}
		} elseif ($_POST[select] == "teacher") {
			if ($_POST[choice] == "1") {
				$where = " WHERE address_level='5' AND state='0';";
			} elseif ($_POST[choice] == "2") {
				$where = " WHERE address_level='5' AND display='1' AND state='0';";
			} elseif ($_POST[choice] == "3") {
				$where = " WHERE address_level='5' AND display='2' AND state='0';";
			}
		} elseif ($_POST[select] == "student") {
			if ($_POST[choice] == "1") {
				$where = " WHERE address_level='6' AND state='0';";
			} elseif ($_POST[choice] == "2") {
				$where = " WHERE address_level='6' AND display='1' AND state='0';";
			} elseif ($_POST[choice] == "3") {
				$where = " WHERE address_level='6' AND display='2' AND state='0';";
			}
		}
		$ERROR = $cdb->update(T_ANNOUNCEMENT,$INSERT_DATA,$where);

	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}
?>