<?php
/**
 * ベンチャー・リンク　すらら
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
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
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
	}

	$html .= mode_menu();
	if (!MODE || MODE == "member_list") { $html .= list_display(); }
	elseif (MODE == "addform") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= list_display(); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= list_display(); }
			else { $html .= viewform($ERROR); }
		} elseif ($_POST[mf]) {
			$html .= check_html();
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "del") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= list_display(); }
			else { $html .= check_html(); }
		} else {
			$html .= check_html();
		}
	}
	return $html;
}

/**
 * モードを選択メニューを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function mode_menu() {
	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<td><input type=\"submit\" value=\"一覧表示\"></td>\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"addform\">\n";
	$html .= "<td><input type=\"submit\" value=\"新規登録\"></td>\n";
	$html .= "</form>\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

	return $html;
}

/**
 * 一覧作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function list_display() {

	global $L_MENU,$L_MENU_SUB,$L_MENU_MODE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM " .
		T_EMPLOYEE . " employee".
		" WHERE employee.state='0' ".
		" GROUP BY list_num ";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "登録社員レベルは未設定です。";
		return $html;
	}
	$i = 1;
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td style=\"text-align:center;\">↑</td>\n";
	$html .= "<td style=\"text-align:center;\">↓</td>\n";
	$html .= "<td>社員レベル</td>\n";
	$html .= "<td>詳細</td>\n";
	$html .= "<td>削除</td>\n";
	$html .= "</tr>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
		$up_submit = $down_submit = "&nbsp;";
		if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
		if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$list['kngn_lvl']."\">\n";
		$html .= "<td>".$up_submit."</td>\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$list['kngn_lvl']."\">\n";
		$html .= "<td>".$down_submit."</td>\n";
		$html .= "</form>\n";
		$html .= "<td>".$list['employee_name']."</td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		$html .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$list['kngn_lvl']."\">\n";
		$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
		$html .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$list['kngn_lvl']."\">\n";
		$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
		$html .= "</form>\n";
		$html .= "</tr>\n";
		$i++;
	}
	$html .= "</table>\n";
	return $html;
}

/**
 * 登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_MENU,$L_MENU_SUB,$L_MENU_MODE;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			if (is_array($val)) {
				foreach ($val as $VAL) { ${$key}[$VAL] = $VAL; }
			} else {
				$$key = $val;
			}
		}
	}
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// add start oda 2014/10/08 権限制御修正
	// コース情報を取得
	$L_COURSE_LIST = get_course_list();

	// コース情報が取得できたらコース情報をメニューに組み込む
	if (is_array($L_COURSE_LIST) && count($L_COURSE_LIST) > 0) {
		foreach ($L_COURSE_LIST as $key => $value) {
			foreach ($L_MENU as $main_key => $main_val) {
				if (!$main_key) { continue; }
				if ($main_key != "practice") { continue; }
				foreach ($L_MENU_SUB[$main_key] as $sub_key => $sub_val) {
					if (!$sub_key) { continue; }
					if ($sub_key != "import" && $sub_key != "export" && $sub_key != "view" && $sub_key != "drill" && $sub_key != "flash") { continue; }		// update oda 2015/03/11 Flash動作確認追加
					$L_MENU_MODE[$main_key][$sub_key]['course_'.$key] = $value;
				}
			}
		}
	}
	// add end oda 2014/10/08 権限制御修正

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">社員レベル名</td>\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"employee_name\" value=\"$employee_name\"></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<div id=\"tree\"><ul id=\"authority_list\">\n";
	foreach ($L_MENU as $main_key => $main_val) {
		if (!$main_key) { continue; }
		if ($employee_authority[$main_key]) { $checked = " checked"; } else { $checked = ""; }
		$html .= "<li id=\"li__".$main_key."\" class=\"plus_li\" style=\"margin-left:20px;\"><span class=\"cursor_pointer\" onclick=\"change_display('".$main_key."')\">" . $main_val . "</span>";
		$html .= "<input type=\"checkbox\" id=\"box__".$main_key."\" name=\"employee_authority[]\" value=\"".$main_key."\" onclick=\"change_box('".$main_key."')\"$checked>";
		$html .= "<ul id=\"".$main_key."\" style=\"display:none;\">\n";
		foreach ($L_MENU_SUB[$main_key] as $sub_key => $sub_val) {
			if (!$sub_key) { continue; }
			if ($employee_authority[$main_key."__".$sub_key]) { $checked = " checked"; } else { $checked = ""; }
			if (!is_array($L_MENU_MODE[$main_key][$sub_key])) {
				$html .= "<li class=\"ya_li\" style=\"margin-left:20px;\">" . $sub_val;
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."\"$checked>";
				$html .= "</li>\n";
				continue;
			} else {
				$html .= "<li id=\"li__".$main_key."__".$sub_key."\" class=\"plus_li\" style=\"margin-left:20px;\"><span class=\"cursor_pointer\" onclick=\"change_display('".$main_key."__".$sub_key."')\">" . $sub_val . "</span>\n";
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."\" onclick=\"change_box('".$main_key."__".$sub_key."')\"$checked>";
				$html .= "<ul id=\"".$main_key."__".$sub_key."\" style=\"display:none;\">\n";
			}
			foreach ($L_MENU_MODE[$main_key][$sub_key] as $key => $val) {
				if (!$key) { continue; }
				if ($employee_authority[$main_key."__".$sub_key."__".$key]) { $checked = " checked"; } else { $checked = ""; }
				$html .= "<li class=\"ya_li\" style=\"margin-left:20px;\">" . $val;
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."__".$key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."__".$key."\"$checked>";
				$html .= "</li>\n";
			}
			$html .= "</ul>\n";
			$html .= "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</li>\n";
	}
	$html .= "</ul></div>\n";
	$html .= "<input type=\"submit\" value=\"作成\">";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
/*
	$html .= "<hr>";
	$html .= "<img src=\"/admin/images/plus.gif\" alt=\"+\" style=\"cursor:pointer;margin-right:5px;\">";
	$html .= "<span style=\"width:150px;font-size:12px;\">基本管理</span>";
	$html .= "<input type=\"checkbox\">";
*/
//	$html .= "<input type=\"button\" value=\"CHECK STATUS\" onclick=\"change_box('basic_management')\"><div id=\"check_area\"></div>\n";
	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {
	if (!$_POST['employee_name']) { $ERROR[] = "社員レベル名を入力してください。"; }
	return $ERROR;
}


/**
 * 表示フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_MENU,$L_MENU_SUB,$L_MENU_MODE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			if (is_array($val)) {
				foreach ($val as $VAL) { ${$key}[$VAL] = $VAL; }
			} else {
				$$key = $val;
			}
		}
	} else {
		$sql = "SELECT " .
			"employee.employee_name,employee_authority.authority" .
			" FROM " .
			T_EMPLOYEE . " employee".
				" LEFT JOIN " . T_EMPLOYEE_AUTHORITY . " employee_authority".
				" ON employee.kngn_lvl=employee_authority.kngn_lvl AND employee_authority.state='0'" .
			" WHERE employee.state='0'" .
			" AND employee.kngn_lvl='".$_POST['kngn_lvl']."'" .
			" ORDER BY employee_authority.authority";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($list as $key => $val) {
				if ($key == "authority") {
					$employee_authority[$val] = $val;
					continue;
				}
				$$key = replace_decode($val);
			}
		}
	}
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// add start oda 2014/10/08 権限制御修正
	// コース情報を取得
	$L_COURSE_LIST = get_course_list();

	// コース情報が取得できたらコース情報をメニューに組み込む
	if (is_array($L_COURSE_LIST) && count($L_COURSE_LIST) > 0) {
		foreach ($L_COURSE_LIST as $key => $value) {
			foreach ($L_MENU as $main_key => $main_val) {
				if (!$main_key) { continue; }
				if ($main_key != "practice") { continue; }
				foreach ($L_MENU_SUB[$main_key] as $sub_key => $sub_val) {
					if (!$sub_key) { continue; }
					if ($sub_key != "import" && $sub_key != "export" && $sub_key != "view" && $sub_key != "drill" && $sub_key != "flash") { continue; }		// update oda 2015/03/11 Flash動作確認追加
					$L_MENU_MODE[$main_key][$sub_key]['course_'.$key] = $value;
				}
			}
		}
	}
	// add end oda 2014/10/08 権限制御修正

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">";
	$html .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$_POST['kngn_lvl']."\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">社員レベル名</td>\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"employee_name\" value=\"".$employee_name."\"></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<div id=\"tree\"><ul id=\"authority_list\">\n";
	foreach ($L_MENU as $main_key => $main_val) {
		if (!$main_key) { continue; }
		if ($employee_authority[$main_key]) { $checked = " checked"; } else { $checked = ""; }
		$html .= "<li id=\"li__".$main_key."\" class=\"plus_li\" style=\"margin-left:20px;\"><span class=\"cursor_pointer\" onclick=\"change_display('".$main_key."')\">" . $main_val . "</span>";
		$html .= "<input type=\"checkbox\" id=\"box__".$main_key."\" name=\"employee_authority[]\" value=\"".$main_key."\" onclick=\"change_box('".$main_key."')\"$checked>";
		$html .= "<ul id=\"".$main_key."\" style=\"display:none;\">\n";
		foreach ($L_MENU_SUB[$main_key] as $sub_key => $sub_val) {
			if (!$sub_key) { continue; }
			if ($employee_authority[$main_key."__".$sub_key]) { $checked = " checked"; } else { $checked = ""; }
			if (!is_array($L_MENU_MODE[$main_key][$sub_key])) {
				$html .= "<li class=\"ya_li\" style=\"margin-left:20px;\">" . $sub_val;
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."\"$checked>";
				$html .= "</li>\n";
				continue;
			} else {
				$html .= "<li id=\"li__".$main_key."__".$sub_key."\" class=\"plus_li\" style=\"margin-left:20px;\"><span class=\"cursor_pointer\" onclick=\"change_display('".$main_key."__".$sub_key."')\">" . $sub_val . "</span>\n";
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."\" onclick=\"change_box('".$main_key."__".$sub_key."')\"$checked>";
				$html .= "<ul id=\"".$main_key."__".$sub_key."\" style=\"display:none;\">\n";
			}
			foreach ($L_MENU_MODE[$main_key][$sub_key] as $key => $val) {
				if (!$key) { continue; }
				if ($employee_authority[$main_key."__".$sub_key."__".$key]) { $checked = " checked"; } else { $checked = ""; }
				$html .= "<li class=\"ya_li\" style=\"margin-left:20px;\">" . $val;
				$html .= "<input type=\"checkbox\" id=\"box__".$main_key."__".$sub_key."__".$key."\" name=\"employee_authority[]\" value=\"".$main_key."__".$sub_key."__".$key."\"$checked>";
				$html .= "</li>\n";
			}
			$html .= "</ul>\n";
			$html .= "</li>\n";
		}
		$html .= "</ul>\n";
		$html .= "</li>\n";
	}
	$html .= "</ul></div>\n";
	$html .= "<input type=\"submit\" value=\"作成\">";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_MENU,$L_MENU_SUB,$L_MENU_MODE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;
	if ($action) {
		if ($_POST) {
			foreach ($_POST as $key => $val) {
				if ($key == "action") {
					if (MODE == "addform") { $val = "add"; }
					elseif (MODE == "view") { $val = "change"; }
				}
				if (is_array($val)) {
					foreach ($val as $VAL) {
						${$key}[$VAL] = $VAL;
						$HIDDEN .= "<input type=\"hidden\" name=\"".$key."[]\" value=\"".$VAL."\">\n";
					}
				} else {
					$$key = $val;
					$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
				}
			}
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"kngn_lvl\" value=\"".$_POST['kngn_lvl']."\">\n";
		$sql = "SELECT " .
			"employee.employee_name,employee_authority.authority" .
			" FROM " .
			T_EMPLOYEE . " employee".
				" LEFT JOIN " . T_EMPLOYEE_AUTHORITY . " employee_authority".
				" ON employee.kngn_lvl=employee_authority.kngn_lvl AND employee_authority.state='0'" .
			" WHERE employee.state='0'" .
			" AND employee.kngn_lvl='".$_POST['kngn_lvl']."'" .
			" ORDER BY employee_authority.authority";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($list as $key => $val) {
				if ($key == "authority") {
					$employee_authority[$val] = $val;
					continue;
				}
				$$key = replace_decode($val);
			}
		}
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// add start oda 2014/10/08 権限制御修正
	// コース情報を取得
	$L_COURSE_LIST = get_course_list();

	// コース情報が取得できたらコース情報をメニューに組み込む
	if (is_array($L_COURSE_LIST) && count($L_COURSE_LIST) > 0) {
		foreach ($L_COURSE_LIST as $key => $value) {
			foreach ($L_MENU as $main_key => $main_val) {
				if (!$main_key) { continue; }
				if ($main_key != "practice") { continue; }
				foreach ($L_MENU_SUB[$main_key] as $sub_key => $sub_val) {
					if (!$sub_key) { continue; }
					if ($sub_key != "import" && $sub_key != "export" && $sub_key != "view" && $sub_key != "drill" && $sub_key != "flash") { continue; }		// update oda 2015/03/11 Flash動作確認追加
					$L_MENU_MODE[$main_key][$sub_key]['course_'.$key] = $value;
				}
			}
		}
	}
	// add end oda 2014/10/08 権限制御修正

	if (!$employee_authority) {
		$employee_authority_html = "禁止権限なし";
	} else {
		foreach ($employee_authority as $val) {
			$authority_key = explode("__",$val);
			if (count($authority_key) == 1) {
				$employee_authority_html .= "[".$L_MENU[$authority_key[0]]."]<br>";
			} elseif (count($authority_key) == 2) {
				$employee_authority_html .= "[".$L_MENU[$authority_key[0]]."] =&gt; [".$L_MENU_SUB[$authority_key[0]][$authority_key[1]]."]<br>";
			} elseif (count($authority_key) == 3) {
				$employee_authority_html .= "[".$L_MENU[$authority_key[0]]."] =&gt; [".$L_MENU_SUB[$authority_key[0]][$authority_key[1]]."] =&gt; [".$L_MENU_MODE[$authority_key[0]][$authority_key[1]][$authority_key[2]]."]<br>";
			}
		}
	}
	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">社員レベル名</td>\n";
	$html .= "<td class=\"member_form_cell\">".$employee_name."</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">禁止権限</td>\n";
	$html .= "<td class=\"member_form_cell\">".$employee_authority_html."</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	if ($action) {
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
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

//	$html .= "<input type=\"button\" value=\"CHECK STATUS\" onclick=\"change_box('basic_management')\"><div id=\"check_area\"></div>\n";
	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			if (is_array($val)) {
				foreach ($val as $VAL) {
					${$key}[$VAL] = $VAL;
				}
			} else {
				$INSERT_DATA[$key] = $val;
			}
		}
	}

	$sql = "SELECT MAX(kngn_lvl) AS last_lvl FROM " . T_EMPLOYEE;

	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);

	$INSERT_DATA['kngn_lvl'] = $list['last_lvl'] + 1;

	$ERROR = $cdb->insert(T_EMPLOYEE,$INSERT_DATA);
	if ($ERROR) { return $ERROR; }

	$where = " WHERE kngn_lvl='".$INSERT_DATA['kngn_lvl']."' LIMIT 1;";
	$INSERT_DATA['list_num'] = $INSERT_DATA['kngn_lvl'];

	$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);
	if ($ERROR) { return $ERROR; }

	if ($employee_authority) {
		foreach ($employee_authority as $val) {

			$INSERT_DATA2['kngn_lvl'] = $INSERT_DATA['kngn_lvl'];
			$INSERT_DATA2['authority'] = $val;
			$ERROR = $cdb->insert(T_EMPLOYEE_AUTHORITY,$INSERT_DATA2);
		}
	}
	return $ERROR;
}

/**
 * DB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "view") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "kngn_lvl") { continue; }
			if (is_array($val)) {
				foreach ($val as $VAL) {
					${$key}[$VAL] = $VAL;
				}
			} else {
				$INSERT_DATA[$key] = $val;
			}
		}
		$where = " WHERE kngn_lvl='".$_POST['kngn_lvl']."' LIMIT 1;";

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);

		$where = " WHERE kngn_lvl='".$_POST['kngn_lvl']."' AND state='0';";
		$UPDATE_DATA['state'] = 1;

		$ERROR = $cdb->update(T_EMPLOYEE_AUTHORITY,$UPDATE_DATA,$where);

		if ($employee_authority) {
			foreach ($employee_authority as $val) {
				$INSERT_DATA2['kngn_lvl'] = $_POST['kngn_lvl'];
				$INSERT_DATA2['authority'] = $val;
				$ERROR = $cdb->insert(T_EMPLOYEE_AUTHORITY,$INSERT_DATA2);
			}
		}
	} elseif (MODE == "del") {
		$where = " WHERE kngn_lvl='".$_POST['kngn_lvl']."' LIMIT 1;";
		$INSERT_DATA[state] = 1;

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);

		$where = " WHERE kngn_lvl='".$_POST['kngn_lvl']."' AND state='0';";
		$UPDATE_DATA[state] = 1;

		$ERROR = $cdb->update(T_EMPLOYEE_AUTHORITY,$UPDATE_DATA,$where);
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * コース情報を取得し配列を返却する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function get_course_list() {
// add oda 2014/10/08

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 返却用配列クリア
	$L_COURSE_LIST = array();

	//$sql = "SELECT course_num, course_name FROM " . T_COURSE . " WHERE display = '1' and state = '0' ";	// del oda 2018/07/12 非表示コースも表示する
	$sql = "SELECT course_num, course_name FROM " . T_COURSE . " WHERE state = '0' ";						// add oda 2018/07/12 非表示コースも表示する
	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {
		$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
	}

	return $L_COURSE_LIST;
}

// add start oda 2015/09/28 課題要望一覧No486 Up/Downの機能が動作しない
/**
 * 権限行アップ機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_EMPLOYEE.
			" WHERE kngn_lvl = '".$_POST['kngn_lvl']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_kngn_lvl = $list['kngn_lvl'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_kngn_lvl || !$m_list_num) {
		$ERROR[] = "権限情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_EMPLOYEE.
				" WHERE state = '0' AND list_num < '".$m_list_num."'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_kngn_lvl = $list['kngn_lvl'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_kngn_lvl || !$c_list_num) {
		$ERROR[] = "権限情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $c_list_num;
		$where = " WHERE kngn_lvl = '".$m_kngn_lvl."' LIMIT 1;";

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $m_list_num;
		$where = " WHERE kngn_lvl = '".$c_kngn_lvl."' LIMIT 1;";

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * 権限行ダウン機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_EMPLOYEE.
			" WHERE kngn_lvl='".$_POST['kngn_lvl']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_kngn_lvl = $list['kngn_lvl'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_kngn_lvl || !$m_list_num) {
		$ERROR[] = "権限情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_EMPLOYEE.
				" WHERE state = '0' AND list_num > '".$m_list_num."'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_kngn_lvl = $list['kngn_lvl'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_kngn_lvl || !$c_list_num) {
		$ERROR[] = "権限情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $c_list_num;
		$where = " WHERE kngn_lvl = '".$m_kngn_lvl."' LIMIT 1;";

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $m_list_num;
		$where = " WHERE kngn_lvl = '".$c_kngn_lvl."' LIMIT 1;";

		$ERROR = $cdb->update(T_EMPLOYEE,$INSERT_DATA,$where);
	}

	return $ERROR;
}
// add end oda 2015/09/28
?>