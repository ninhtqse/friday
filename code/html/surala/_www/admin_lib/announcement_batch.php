<?
/**
 * ベンチャー・リンク　すらら
 *
 * アナウンス管理　一括アナウンス送信
 *
 * @author Azet
 */


//定数定義
define("SERVER_NAME1", "10.128.1.36");		//すらら様コンテンツ開発
define("SERVER_NAME2", "10.128.1.35");		//Azetコンテンツ開発

define("SERVER_IP1", "10.3.11.101");		//すらら様コンテンツ開発 // add 2018/03/08 yoshizawa AWS環境判定
define("SERVER_IP2", "10.3.11.100");		//Azetコンテンツ開発 // add 2018/03/08 yoshizawa AWS環境判定

define("XFER_DISTRIBUTE_SERVER", "STAG");	//検証 分散サーバーへアップする。 ※Azet開発の場合は、DBの接続先は開発分散になる。
//define("XFER_DISTRIBUTE_SERVER", "PROD");	//本番 分散サーバーへアップする場合は、こちらを有効化にする。

/**
 * 一括アナウンス送信
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
//echo "*ACTION=".ACTION.", *MODE=".MODE."<br>";

	if (strlen(MODE) == 0) { unset($_SESSION['SELECT_VIEW']); }

	if (MODE == "create" || MODE == "create1" || MODE == "modoru_create") { $html .= create();
	} else if (MODE == "confirm" || MODE == "update_confirm") { $html .= confirm();
	} else if (MODE == "regist") {
		$html .= regist();
		$html .= history_list();
	} else if (MODE == "check") { $html .= check();
	} else if (MODE == "edit" || MODE == "modoru_edit")  { $html .= edit();
	} else if (MODE == "delete") { $html .= delete_confirm();
	} else if (MODE == "delete_exec") {
		$html .= delete_exec();
		$html .= history_list();
	} else if (MODE == "update_exec") {
		$html .= update_exec();
		$html .= history_list();
	} else if (MODE == "upload") {
		$html .= csv_upload();
		if (strlen($html) == 0) {
			$html .= create_html();
		}
	} else if (MODE == "show_test") {
		$html .= show_test();
	} else {	//"show_list"もここを通る
		if (MODE == "set_list_cnt") { //リストの表示件数
			set_view_num();
		}
		if (MODE == "next_page") {
			$tmp_disp_page = $_SESSION['SELECT_VIEW']['disp_page'];
			if ($_SESSION['SELECT_VIEW']['max_page'] >$tmp_disp_page) {
				$_SESSION['SELECT_VIEW']['disp_page'] = $tmp_disp_page + 1;
			}
		}
		if (MODE == "prev_page") {
			$tmp_disp_page = $_SESSION['SELECT_VIEW']['disp_page'];
			if ($tmp_disp_page > 1) {
				$_SESSION['SELECT_VIEW']['disp_page'] = $tmp_disp_page - 1;
			}
		}
		$html .= history_list();
	}

	$html .= set_scriptcss();

	return $html;
}


/**
 * リストの表示件数設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start2() {

	$honbu_announce_num = $_POST['honbu_announce_num'];

	$select_db = set_select_db("CONTENTSX");

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}


	$sql = "SELECT * FROM honbu_announce_data WHERE honbu_announce_num='".$honbu_announce_num."' AND display='1' AND mk_flg='0' ORDER BY upd_date DESC LIMIT 1;";
	$result = $connect_db->exec_query($sql);
	if ($result->num_rows == 0) {
		$msg = "該当するデータがは有りません。<br><br>";
	} else {
		$list=$connect_db->fetch_assoc($result);
//		$msg = "件名：${list['subject']}<br>";
		$msg .= "<hr>";
		$msg .= htmlspecialchars_decode($list['message'], ENT_QUOTES);
		$msg .= "<hr>";
	}


$html = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>確認画面</title>
</head>
<body>
<form action="/admin/announcement_batch.php" method="POST" name="form">
<input type="hidden" name="mode" value="show_test">
<input type="hidden" name="honbu_announce_num" value="'+honbu_announce_num+'">
${msg}
<br><input type="button" name="close" value="閉じる" onclick="window.close();return false;">
</form>
</body>
</html>
EOT;


	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * リストの表示件数設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function set_view_num() {
	if ($_POST['view_num']) {
		$_SESSION['SELECT_VIEW']['view_num'] = $_POST['view_num'];	//１ページの表示件数
		$_SESSION['SELECT_VIEW']['disp_page'] = 1;	//表示ページ番号
	}
}


/**
 * javascript/css 定義
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function set_scriptcss() {

	$script_css = <<<EOT
<script language="javascript">
<!--
function erase_school_id(id) {
	document.getElementById("school_list_1_"+id).innerHTML="&nbsp;";
	document.getElementById("school_list_2_"+id).innerHTML="&nbsp;";
	document.getElementById("school_list_3_"+id).innerHTML="";
}

function confirm_regist_exec() {
	if(window.confirm('登録して宜しいでしょうか？')){
		document.frm_reg.submit();
	}
}
function confirm_update_exec() {
	if(window.confirm('更新して宜しいでしょうか？')){
		document.frm_reg.submit();
	}
}
function confirm_delete_exec() {
	if(window.confirm('削除して宜しいでしょうか？')){
		document.frm_delete.submit();
	}
}
function add_lines() {
	var row_datacnt = document.getElementById("row_datacnt").value;

	var c = 0;
	while(c < 5) {
		c++;
		row_datacnt++;

		var table = document.getElementById("school_list_table");
		var row = table.insertRow(-1);
		var cell1 = row.insertCell(-1);
		var cell2 = row.insertCell(-1);
		var cell3 = row.insertCell(-1);
		var cell4 = row.insertCell(-1);

		row.className = "member_form_menu";
		cell1.innerHTML = "<input type=\"text\" name=\"school_id_"+row_datacnt+"\" value=\"\" onChange=\"erase_school_id("+row_datacnt+")\" >";
		cell2.innerHTML = "<span id=\"school_list_1_"+row_datacnt+"\">&nbsp;</span>";
		cell3.innerHTML = "<span id=\"school_list_2_"+row_datacnt+"\">&nbsp;</span>";
		cell4.innerHTML = "<span id=\"school_list_3_"+row_datacnt+"\"></span>";
		cell4.className = "schoolid_check";
	}
	document.getElementById("row_datacnt").value = row_datacnt;
}

function check_message(tinymce) {

	strTemp = 'width=780,height=300,scrollbars=no,resizable=yes,status=yes';
		if(tinymce) {
			var message = tinymce.get('message').getContent();
		}
		message = escape_html_mce(message);
		message = encodeURI(message);

		var win = window.open('', 'check_message', strTemp);
		var html = '<!DOCTYPE HTML>';
		html += '<html lang=\"ja\">';
		html += '<head>';
		html += '<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">';
		html += '<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">';
		html += '<title>アナウンス管理アナウンス確認</title>';
		html += '</head>';
		html += '<body>';
		html += '<form action=\"/teacher/announcement_check_message.php\" method=\"POST\" name=\"form\">';
		html += '<input type=\"hidden\" name=\"message\" value=\"' + message + '\">';
		html += '</form>';
		html += '</body>';
		html += '</html>';
		win.document.open();
		win.document.write(html);
		win.document.close();
		win.document.form.submit();
		win.focus();
}

-->
</script>

<style type="text/css">
.schoolid_check {
border:0px;
padding:0;
}
.member_formx {
font-size:12px;
border-collapse:collapse;
border:2px solid black;
}
.member_formx td {
border:2px solid black;
padding:2px;
}
.member_formx th {
border:2px solid black;
padding:2px;
}
.comment_desc {
font-size:12px;
}
#mceu_7 {
 width:500px;
}
</style>

EOT;

	return $script_css;
}

/**
 * スクリプト追加読み込み処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function more_scriptcss() {
	$html = "";
	$html .= "<script type=\"text/javascript\" src=\"/javascript/tinymce/js/tinymce/tinymce.min.js\"></script>\n";
	$html .= "<script type=\"text/javascript\" src=\"/javascript/tinymce/js/tinymce/plugins/paste/plugin.min.js\"></script>";
	$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/admin_calendar.css\" />";

	// upd start hasegawa 2017/09/19 課題要望No631 check_tags_mce,escape_html_mce追加
	// add oda 2016/07/29 課題要望一覧No543 ツールバーにcode(ソース表示)を追加
	// add hasegawa 2018/07/19 function check_tags_mce内 escape_style_mce, setContent処理追加・function escape_style_mce追加
	$html .= "<script type=\"text/javascript\">
				tinymce.init({
					selector: \"textarea#message\",
					theme: \"modern\",
					plugins: [
						\"autolink link textcolor code\"
					],
					language : \"ja\",
					add_unload_trigger: false,
					forced_root_block : false,

					toolbar1: \"undo redo | bold | forecolor backcolor | link | code\",
					toolbar2: false,

					elementpath: false,
					default_link_target: \"_blank\",
					target_list: false,
					link_title: false,
					menubar: false

				});

				function check_tags_mce(tinymce) {
					var mes = tinymce.get('message').getContent();
					mes = escape_style_mce(mes);
					tinymce.get('message').setContent(mes);
					mes = escape_html_mce(mes);
					document.announse_form.message2.value = mes;
					document.announse_form.submit();
				}

				function escape_html_mce(string) {
				  if(typeof string !== 'string') {
				    return string;
				  }
				  return string.replace(/&lt;|&gt;/g, function(match) {
				    return {
				      '&lt;': '【＜】',
				      '&gt;': '【＞】'
				    }[match]
				  });
				}

				function escape_style_mce(string) {

					if(typeof string !== 'string') {
						return string;
					}

					var m_replace_str;
					var add_input;
					var i = 0;

					return string.replace(/style=[\"'](.*?)[\"']/g, function(match) {

						m_replace_str = '【StyleReplacer'+i+'】';
						i++;
						if (document.announse_form) {
							add_input = document.createElement('input');
							add_input.setAttribute('type', 'hidden');
							add_input.setAttribute('name', m_replace_str);
							add_input.setAttribute('value', match);
							document.announse_form.appendChild(add_input);
						}
						return m_replace_str;
					});
				}
			</script>";
	// upd end hasegawa 2017/09/19

	return $html;
}

/**
 * 初期画面（新規アナウンス操作ボタン、登録済みリスト）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function history_list() {
//	global $L_DB;

	$html = "";

	if (!isset($_SESSION['SELECT_VIEW']['disp_page'])) {
		$_SESSION['SELECT_VIEW']['disp_page'] = 1;	//表示ページ番号
	}

	//$select_db = $L_DB['srlctd03SOGO'];
	$select_db = set_select_db("CONTENTSX");	//開発コアDB総合サーバテスト用 CONTENTS160

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	$html .= create_buttons();

	$sql = "SELECT * FROM honbu_announce_data WHERE display='1' AND mk_flg='0';";	//件数をチェック
	$result = $connect_db->exec_query($sql);
	$data_cnt = $result->num_rows;	//データ件数
	if ($data_cnt == 0) {
		$html .= "アナウンスデータはありません。";

	} else {

		//	表示数	view_num
		$view_num = $_SESSION['SELECT_VIEW']['view_num'];
		if (strlen($view_num) == 0) { $view_num = 10; }
		$l_view_num = "<select name=\"view_num\">\n";
		for ($i=1; $i<=10; $i++) {
			$val = $i * 10;
			if ($view_num == $val) { $selected = "selected"; } else { $selected = ""; }
			$l_view_num .= "<option value=\"{$val}\" $selected>{$val}</option>\n";
		}
		$l_view_num .= "</select>\n";
		$_SESSION['SELECT_VIEW']['view_num'] = $view_num;
		$MENU[2][name] = "表示数";
		$MENU[2][value] = $l_view_num;

		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_list_cnt\">\n";
		$html .= "<table border=\"0\">\n";
		$html .= "<tr>\n";
		$html .= "<td>表示数</td>\n";
		$html .= "<td>".$l_view_num."</td>\n";
		$html .= "<td><input type=\"submit\" name=\"set\" value=\"Set\"></td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";

		//データ数、表示ページの計算
		if ($data_cnt > 0) {
			$html .= "<br>登録件数&nbsp;(".$data_cnt."): ";
			$max_page = intval(($data_cnt - 1)/ $view_num)+1;
			$disp_page = $_SESSION['SELECT_VIEW']['disp_page'];
			$html .= "PAGE&nbsp;[".$disp_page."/".$max_page."]&nbsp;";

			if (!isset($_SESSION['SELECT_VIEW']['max_page'])) {
				$_SESSION['SELECT_VIEW']['max_page'] = $max_page;	//最大ページ番号
			}

		}

		//ページ切り替えボタン
		if ($disp_page > 1) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"display: inline\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"prev_page\">\n";
			$html .= "<input type=\"submit\" name=\"prev_page\" value=\"前のページ\">";
			$html .= "</form>";
		}
		if ($disp_page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"display: inline\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"next_page\">\n";
			$html .= "<input type=\"submit\" name=\"next_page\" value=\"次のページ\">";
			$html .= "</form>";
		}

		//１ページ分のデータを取得
		$limit_set = "LIMIT ".intval(($disp_page - 1) * $view_num).", ".$view_num;
		$sql = "SELECT * FROM honbu_announce_data WHERE display='1' AND mk_flg='0' ORDER BY honbu_announce_num ".$limit_set.";";
		$result = $connect_db->exec_query($sql);


		$html .= <<<EOT
<br>
<table class="member_formx">
<tr class="member_form_menu">
<th>&nbsp;</th>
<th>登録日</th>
<th>最終更新日</th>
<th colspan="3">表示期限</th>
<th>アナウンス</th>
<th>対象校舎数</th>
<th colspan="3">操作</th>
</tr>
EOT;

		while($list=$connect_db->fetch_assoc($result)) {
			//対象校舎数の取得
			$target_school = 0;
			$sql2 = "SELECT count(*) AS cnt FROM honbu_announce_school WHERE honbu_announce_num='${list['honbu_announce_num']}' AND display='1' AND mk_flg='0';";
//echo $sql2;
			$result2 = $connect_db->exec_query($sql2);
			if ($result2->num_rows > 0) {
				$list2 = $connect_db->fetch_assoc($result2);
				$target_school = $list2['cnt'];
			}

			$disp_ins_date = str_replace("-", "/", $list['ins_date']);

			$disp_update_date = str_replace("-", "/", $list['upd_date']);

			$disp_start_date = $list['start_date'];
			if ($disp_start_date == "0000-00-00") {
//				$disp_start_date = "(開始日指定なし)";
				$disp_start_date = "";
			}
			$disp_start_date = str_replace("-", "/", $disp_start_date);

			$disp_end_date = $list['end_date'];
			if ($disp_end_date == "0000-00-00") {
//				$disp_end_date = "(終了日指定なし)";
				$disp_end_date = "";
			}
			$disp_end_date = str_replace("-", "/", $disp_end_date);

			$tmp_subject = $list['subject'];
			if (strlen($tmp_subject) >20) {
				$tmp_subject = mb_substr($tmp_subject,0,10,"utf8") . "...";
			}
			$tmp_message = $list['message'];

			$message_no_tag = str_replace("&lt;","<",$tmp_message);			// add start hasegawa 2015/02/10 アナウンス管理改修
			$message_no_tag = str_replace("&gt;",">",$message_no_tag);
			$message_no_tag = strip_tags($message_no_tag);				// add end hasegawa 2015/02/10 アナウンス管理改修
			$message_no_tag = tinymce_tag_decode($message_no_tag);			// add hasegawa 2017/09/19 課題要望No631

			if (strlen($message_no_tag) >40) {
				$message_no_tag = mb_substr($message_no_tag,0,20,"utf8") . "...";
			}

			$html .= <<<EOT
<tr class="member_form_menu">
<td align="center">${list['honbu_announce_num']}</td>
<td>${disp_ins_date}</td>
<td>${disp_update_date}</td>
<td style="border-right-style:none; width:60px; text-align:center;">${disp_start_date}</td>
<td style="border-left-style:none; border-right-style:none; width:20px; text-align:center;">～</td>
<td style="border-left-style:none; width:60px; text-align:center;">${disp_end_date}</td>
<td>${message_no_tag}</td>
<td align="center">${target_school}</td>

<td>
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="honbu_announce_num" value="${list['honbu_announce_num']}">
<input type="submit" name="edit"  value="変更">
</form>
</td>

<td>
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="delete">
<input type="hidden" name="honbu_announce_num" value="${list['honbu_announce_num']}">
<input type="submit" name="delete" value="削除">
</form>
</td>

</tr>
EOT;
		}

		$html .= <<<EOT
</table>
<br>
EOT;

	}

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


//--------------------------------------------------------------------------------------------------
/**
 * [新規アナウンス]ボタン表示 機能のTOPページを出すときに呼ばれる
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function create_buttons() {

	unset($_SESSION['announce_batch']);

	$html = <<<EOT
<br>
<form action="$_SERVER[PHP_SELF]" method="POST" style="display: inline">
<input type="submit" name="new_announce" value="新規アナウンス">
<input type="hidden" name="mode" value="create">
</form>
<br>
<br>
EOT;

	return $html;
}


/**
 * 一括アナウンス送信
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function create() {

	if (MODE == "create") {		//通常の [新規アナウンス]
		unset($_SESSION['announce_batch']);
		$_SESSION['announce_batch']['datacnt'] = 5;
	}

	if (MODE == "create1") {	// [新規アナウンス(前回値設定)]
		$select_db = set_select_db("CONTENTSX");
		$connect_db = new connect_db();
		$connect_db->set_db($select_db);
		$ERROR = $connect_db->set_connect_db();
		if ($ERROR) {
			return $ERROR;
		}

		$select_db2 = set_select_db("TOGO");
		$connect_db2 = new connect_db();
		$connect_db2->set_db($select_db2);
		$ERROR = $connect_db2->set_connect_db();
		if ($ERROR) {
			$connect_db->close();
			return $ERROR;
		}

		//前回データの取得（有効なものがあれば）
		$sql = "SELECT * FROM honbu_announce_data WHERE display='1' AND mk_flg='0' ORDER BY ins_date DESC LIMIT 1;";
//echo "*sql=".$sql."<br>";
		$result = $connect_db->exec_query($sql);
		if ($result->num_rows == 0) {
			//該当するアナウンスデータがない場合、デフォルト動作
			unset($_SESSION['announce_batch']);
			$_SESSION['announce_batch']['datacnt'] = 5;

		} else {
			$list=$connect_db->fetch_assoc($result);

			unset($_SESSION['announce_batch']);
			if ($list['start_date'] != "0000-00-00") {
				$_SESSION['announce_batch']['start_date'] = str_replace("-", "/", $list['start_date']);
			} else {
				$_SESSION['announce_batch']['start_date'] = "";
			}
			if ($list['end_date'] != "0000-00-00") {
				$_SESSION['announce_batch']['end_date'] = str_replace("-", "/", $list['end_date']);
			} else {
				$_SESSION['announce_batch']['end_date'] = "";
			}
			$_SESSION['announce_batch']['subject'] = $list['subject'];

			$_SESSION['announce_batch']['message'] = $list['message'];

			// add start hasegawa 2017/09/19 課題要望No631
			if($_SESSION['announce_batch']['message']) {
				$_SESSION['announce_batch']['message'] = tinymce_tag_decode($_SESSION['announce_batch']['message']);
			}
			// add end hasegawa 2017/09/19

			$honbu_announce_num = $list['honbu_announce_num'];

			//校舎ID情報の取得
			$entity_cnt = 0;	//校舎IDの件数カウント
			$sql2 = "SELECT * FROM honbu_announce_school WHERE honbu_announce_num='${honbu_announce_num}' AND display='1' AND mk_flg='0';";
//echo $sql2."<br>";
			$result2 = $connect_db->exec_query($sql2);
			if ($result2->num_rows > 0) {
				while($list2 = $connect_db->fetch_assoc($result2)) {
					$school_id = $list2['school_id'];
					$db_id = $list2['db_id'];
					$sql3 = "SELECT school_name FROM school WHERE school_id='".$school_id."';";
					$result3 = $connect_db2->exec_query($sql3);
					if ($result3->num_rows > 0) {
						$list3 = $connect_db2->fetch_assoc($result3);
						$school_name = $list3['school_name'];

						$entity_cnt++;
						$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $school_id;
						$_SESSION['announce_batch']['db_id_'.$entity_cnt] = $db_id;
						$_SESSION['announce_batch']['school_name_'.$entity_cnt] = $school_name;

					} else {
						//校舎IDデータが見つかりません
					}

				}
				if ($entity_cnt < 5) {
					$entity_cnt = 5;
				}
				$_SESSION['announce_batch']['datacnt'] = $entity_cnt;

			} else {
				//校舎IDデータが見つかりません。
				unset($_SESSION['announce_batch']);
				$_SESSION['announce_batch']['datacnt'] = 5;
			}

		}

		//	閲覧DB切断
		$connect_db2->close();
		$connect_db->close();
	}

	$html = create_html();

	return $html;
}



//--------------------------------------------------------------------------------------------------
/**
 * 新規登録入力HTMLの組み立て
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function create_html() {

	$datacnt = $_SESSION['announce_batch']['datacnt'];
	$entity_cnt = $_SESSION['announce_batch']['entity_cnt'];

	$start_date = $_SESSION['announce_batch']['start_date'];
	if ($_POST['end_date']) { $end_date = $_POST['end_date']; }

	$end_date = $_SESSION['announce_batch']['end_date'];
	if ($_POST['start_date']) { $start_date = $_POST['start_date'];	}

	$err_msg_start_date = $_SESSION['announce_batch']['err_msg_start_date'];
	$err_msg_end_date = $_SESSION['announce_batch']['err_msg_end_date'];

	$subject = $_SESSION['announce_batch']['subject'];
	$message = $_SESSION['announce_batch']['message'];

	// add start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応 引数追加
	$REPLACER = array();
	if (is_array($_SESSION['announce_batch'])) {
		foreach($_SESSION['announce_batch'] as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$REPLACER[$key] = $val;
			}
		}
	}

	if ($_SESSION['announce_batch']['message2']) {
		$message = tinymce_tag_decode($_SESSION['announce_batch']['message2']);
		$message = tinymce_style_decode($message,$REPLACER);
	}

	// add end hasegawa 2018/07/19


	$err_msg_msg = $_SESSION['announce_batch']['err_msg_msg'];

	$error_detect = $_SESSION['announce_batch']['error_detect'];

	$html = "";

	$html .= more_scriptcss();	// add hasegawa 2016/02/01

if (MODE == "create") {	//更新update_confirm時の確認画面では、インポートを出さない
	$html .= <<<EOT
<br>
<form action="$_SERVER[PHP_SELF]" method="POST" style="display: inline">
<input type="submit" name="new_announce" value="前回値設定">
<input type="hidden" name="mode" value="create1">
</form>
<br>
<br>
校舎情報インポート:
<form enctype="multipart/form-data" method="post" action="$_SERVER[PHP_SELF]">
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<input type="file" name="csvfile" size="40">
<INPUT TYPE="hidden" NAME="data_type" VALUE="master">
<input type="hidden" name="mode" value="upload">
<input class="" type="submit" value="読込">
</form>
<!--
<span class="comment_desc">※文字コードは、シフトJIS で準備して下さい。</span><br>
-->
EOT;
}

	$html .= <<<EOT
<br>

<form action="$_SERVER[PHP_SELF]" method="POST" name="announse_form">

<table class="member_formx" id="school_list_table">
<tr class="member_form_menu">
<th>校舎ID</th>
<th>分散DB</th>
<th>校舎名</th>
EOT;

	if ($entity_cnt == 0 && $_POST['confirm']) {
		$html .= "<th class=\"schoolid_check\"><span style=\"color:red;\">入力データがありません。</span></th>";
	} else {
		$html .= "<th class=\"schoolid_check\"></th>";
	}

	$html .= "</tr>";

	$cnt = 1;
	$failsafe = 1000;
	while($cnt <= $datacnt && $failsafe > 0) {

		$html .= "<tr class=\"member_form_menu\">";

		$tmp_school_id = "";
		if (strlen($_SESSION['announce_batch']['school_id_'.$cnt]) > 0) {
			$tmp_school_id = $_SESSION['announce_batch']['school_id_'.$cnt];
		}
		$html .= "<td><input type=\"text\" name=\"school_id_${cnt}\" value=\"${tmp_school_id}\" onChange=\"erase_school_id(${cnt})\" ></td>";

		$tmp_db_id_id = "";
		if (strlen($_SESSION['announce_batch']['db_id_'.$cnt]) > 0) {
			$tmp_db_id_id = $_SESSION['announce_batch']['db_id_'.$cnt];
		}
		$html .= "<td><span id=\"school_list_1_${cnt}\">${tmp_db_id_id}&nbsp;</span></td>";

		$tmp_school_name = "";
		if (strlen($_SESSION['announce_batch']['school_name_'.$cnt]) > 0) {
			$tmp_school_name = $_SESSION['announce_batch']['school_name_'.$cnt];
		}
		$html .= "<td><span id=\"school_list_2_${cnt}\">${tmp_school_name}&nbsp;</span></td>";

		$tmp_msg = "";
		if (strlen($_SESSION['announce_batch']['school_id_errmsg_'.$cnt]) > 0) {
			$tmp_msg = $_SESSION['announce_batch']['school_id_errmsg_'.$cnt];
		}
		$html .= "<td class=\"schoolid_check\"><span id=\"school_list_3_${cnt}\">${tmp_msg}</span></td>";
		$html .= "</tr>";

		$cnt++;
		$failsafe--;
	}

	$html .="</table>";

	$html .= <<<EOT

<br>
<input type="button" name="add_line" value="入力+５" onclick="add_lines();return false;"><br>
<br>
表示期限：${err_msg_start_date}${err_msg_end_date}<br>
<input type="text" id="s_start_date" name="start_date" size="10" maxlength="10" value="${start_date}">
<input type="button" value="▼" onclick="dsp_calendar('s'); return false;"> ～
<input type="text" id="s_end_date" name="end_date" size="10" maxlength="10" value="${end_date}">
<input type="button" value="▼" onclick="dsp_calendar('e'); return false;"><br>
<div id="s_calendar" class="calendar_div" style="z-index:1">
</div>
<div id="e_calendar" class="calendar_div" style="z-index:1">
</div><span class="comment_desc">※入力形式: 2016/02/01</span><br>
<br>
アナウンス：${err_msg_msg}<br>
<textarea name="message" id="message" rows="5" cols="60">${message}</textarea><br>
<input type="button" value="アナウンスの確認" class="announ_but" onclick="check_message(tinymce);">
<span>※htmlタグを使用する際は必ず確認してください。</span><br><br>
<!--<input type="submit" name="confirm" value="新規登録内容を確認する">-->
<input type="button" onclick="check_tags_mce(tinymce);" name="confirm" value="新規登録内容を確認する">
<input type="hidden" name="mode" value="confirm">
<input type="hidden" id="row_datacnt" name="datacnt" value="${datacnt}">
<input type="hidden" name="message2" value="">
</form>

<!--
<input type="button" name="cancel" value="戻る" onclick="history.back();">
-->
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="list">
<input type="submit" name="confirm" value="戻る">
</form>
<br>
EOT;
	return $html;
}



//--------------------------------------------------------------------------------------------------
/**
 * 確認画面 入力データのチェック (チェック結果により、create_html() または confirm_html() へ進む)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function confirm() {
//	global $L_DB;

	$dup_check_ary = array();	//同じ校舎IDが入力されたかのチェック用

	unset($_SESSION['announce_batch']);

	$error_detect = 0;	//入力内容のチェック結果で、エラーがあれば1以上の値。

	$err_msg_start_date = "";
	$err_msg_end_date = "";
	$err_msg_msg = "";

	$datacnt = $_POST['datacnt'];	//データ入力行数

	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	$message2 = $_POST['message2'];	// add hasegawa 2017/09/19 課題要望No631

	if (MODE == "update_confirm") {
		$honbu_announce_num = $_POST['honbu_announce_num'];
	}

	//日付が８桁の場合は、分解する。
	if (strlen($start_date) == 8 && strpos($start_date, "/") === FALSE) {
		$start_date = substr($start_date, 0,4)."/".substr($start_date, 4,2)."/".substr($start_date, 6,2);
	}
	if (strlen($end_date) == 8 && strpos($end_date, "/") === FALSE) {
		$end_date = substr($end_date, 0,4)."/".substr($end_date, 4,2)."/".substr($end_date, 6,2);
	}
	//日付の区切りが - ならば / に置換する
	$start_date = str_replace("-", "/", $start_date);
	$end_date = str_replace("-", "/", $end_date);

	if (strlen($start_date) > 0) {
		if (!checkDateFormat($start_date)) {
			$err_msg_start_date = "<span style=\"color:red;\">正しい開始日付が入力されていません。</span>";
			$error_detect++;

//		} else {
//			if ($start_date < date("Y/m/d")) {	//過去日付チェック
//				$err_msg_start_date .= "<span style=\"color:red;\">開始日付が過去です。</span>";
//				$error_detect++;
//			}
		}
	}

	if (strlen($end_date) > 0) {
		if (!checkDateFormat($end_date)) {
			$err_msg_end_date .= "<span style=\"color:red;\">正しい終了日付が入力されていません。</span>";
			$error_detect++;
//		} else {
//			if ($end_date < date("Y/m/d")) {	//過去日付チェック
//				$err_msg_end_date .= "<span style=\"color:red;\">終了日付が過去です。</span>";
//				$error_detect++;
//			}
		}
	}

	if (strlen($start_date) > 0 && strlen($end_date) > 0) {
		if ($end_date < $start_date) {	//指定範囲がおかしいかチェック
			$err_msg_start_date .= "<span style=\"color:red;\">終了日付が開始日付より前です。</span>";
			$err_msg_end_date = "";
			$error_detect++;
		}
	}


	if (strlen($message) == 0) {
		$err_msg_msg .= "<span style=\"color:red;\">入力されていません。</span>";
		$error_detect++;
	}


	if ($datacnt < 1) {
		//<td colspan="3"><span style="color:red;">入力データがありません。</span></td>
		$error_detect++;
	} else {

		$cnt = 1;
		$failsafe = 1000;
		$entity_cnt = 0;
		while($cnt <= $datacnt && $failsafe > 0) {

			$school_id = $_POST['school_id_'.$cnt];

			if (strlen($school_id) > 0) {

				if (!in_array("${school_id}", $dup_check_ary, TRUE)) {
					//同じものが無い場合
					$dup_check_ary[] = "${school_id}";

					list($err, $dbid, $school_name) = check_school_id($school_id);
					if (count($err) == 0) {
						$entity_cnt++;
						$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $school_id;
						$_SESSION['announce_batch']['db_id_'.$entity_cnt] = $dbid;
						$_SESSION['announce_batch']['school_name_'.$entity_cnt] = $school_name;
						$_SESSION['announce_batch']['school_id_errmsg_'.$entity_cnt] = "";

					} else {
						$entity_cnt++;
						$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $school_id;
						$_SESSION['announce_batch']['db_id_'.$entity_cnt] = "";
						$_SESSION['announce_batch']['school_name_'.$entity_cnt] = "";
						$_SESSION['announce_batch']['school_id_errmsg_'.$entity_cnt] = $err[0];

						$error_detect++;
					}

				} else {	//if (!in_array("${school_id}", $dup_check_ary, TRUE)) {
					$entity_cnt++;
					$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $school_id;
					$_SESSION['announce_batch']['db_id_'.$entity_cnt] = "";
					$_SESSION['announce_batch']['school_name_'.$entity_cnt] = "";
					$_SESSION['announce_batch']['school_id_errmsg_'.$entity_cnt] = "<span style=\"color:red;\">同じ校舎IDは、すでに指定されています。</span>";

					$error_detect++;

				}	//if (!in_array("${school_id}", $dup_check_ary, TRUE)) {


			} else {
				//<td colspan="3">入力データがありません。</td>	//スキップ
			}

			$failsafe--;
			$cnt++;
		}	//while
		if ($entity_cnt == 0) {
			//<td colspan="3"><span style="color:red;">入力データがありません。</span></td>
			$error_detect++;
		}

		$_SESSION['announce_batch']['start_date'] = $start_date;
		$_SESSION['announce_batch']['err_msg_start_date'] = $err_msg_start_date;
		$_SESSION['announce_batch']['end_date'] = $end_date;
		$_SESSION['announce_batch']['err_msg_end_date'] = $err_msg_end_date;
		$_SESSION['announce_batch']['err_msg_msg'] = $err_msg_msg;
		$_SESSION['announce_batch']['subject'] = $subject;
		$_SESSION['announce_batch']['message'] = $message;
		$_SESSION['announce_batch']['message2'] = $message2;		// add hasegawa 2017/09/19 課題要望No631
		$_SESSION['announce_batch']['datacnt'] = $datacnt;
		$_SESSION['announce_batch']['entity_cnt'] = $entity_cnt;
		$_SESSION['announce_batch']['error_detect'] = $error_detect;

		// add start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
		if (is_array($_POST)) {
			foreach ($_POST as $key => $val) {

				if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
					$_SESSION['announce_batch'][$key] = $val;
				}
			}
		}
		// add end hasegawa 2018/07/19

		if (MODE == "update_confirm") {
			$_SESSION['announce_batch']['honbu_announce_num'] = $honbu_announce_num;
		}

		if ($error_detect == 0) {
				$html = confirm_html();	//エラー無ければ、確認画面へ

		} else {
			if (MODE == "confirm") {
				$html = create_html();	//エラーがあれば、再び入力画面へ
			}
			if (MODE == "update_confirm") {
				$html = edit_html();	//エラー無ければ、更新の入力画面へ
			}
		}

	}	//	if ($datacnt < 1) {

	return $html;
}


//--------------------------------------------------------------------------------------------------
/**
 * 確認画面HTMLの組み立て
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function confirm_html() {

	$start_date = $_SESSION['announce_batch']['start_date'];
	$err_msg_start_date = $_SESSION['announce_batch']['err_msg_start_date'];
	$end_date = $_SESSION['announce_batch']['end_date'];
	$err_msg_end_date = $_SESSION['announce_batch']['err_msg_end_date'];
	$err_msg_msg = $_SESSION['announce_batch']['err_msg_msg'];
	$subject = $_SESSION['announce_batch']['subject'];
	$message = $_SESSION['announce_batch']['message'];

	$entity_cnt = $_SESSION['announce_batch']['entity_cnt'];

	$html .= more_scriptcss();	// add hasegawa 2016/02/01
	$html .= <<<EOT
<form action="$_SERVER[PHP_SELF]" method="POST" name="frm_reg">
<br>
<table class="member_formx">
<tr class="member_form_menu">
<th>校舎ID</th>
<th>分散DB</th>
<th>校舎名</th>
</tr>
EOT;


	$cnt = 1;
	$failsafe = 1000;
	while($cnt <= $entity_cnt && $failsafe > 0) {

		$school_id = $_SESSION['announce_batch']['school_id_'.$cnt];
		$dbid = $_SESSION['announce_batch']['db_id_'.$cnt];
		$school_name = $_SESSION['announce_batch']['school_name_'.$cnt];

		$html .= <<<EOT
<tr class="member_form_menu">
<td>${school_id}
<input type="hidden" name="school_id_${cnt}" value="${school_id}">
</td>
<td>${dbid}
<input type="hidden" name="db_id_${cnt}" value="${dbid}">
</td>
<td>${school_name}</td>
</tr>
EOT;

		$cnt++;
		$failsafe--;
	}

	$html .= "</table>";
	$html .= "<br>";

	$period_disp1 = "表示期限： ";
	$period_disp2 = "";
	if (strlen($start_date) > 0) {
		$period_disp1 .= $start_date;
	} else {
		$period_disp1 .= "(開始日指定なし)";
	}
	$period_disp1 .= " ～ ";
	if (strlen($end_date) > 0) {
		$period_disp1 .= $end_date;
	} else {
		$period_disp1 .= "(終了日指定なし)";
	}
	$period_disp1 .= "<br>";
	if (strlen($err_msg_start_date) > 0) {
		$period_disp2 .= $err_msg_start_date."<br>";
	}
	if (strlen($err_msg_end_date) > 0) {
		$period_disp2 .= $err_msg_end_date."<br>";
	}
	$html .= $period_disp1;
	$html .= $period_disp2;

	$message_no_tag = str_replace("&lt;","<",$message);			// add start hasegawa 2015/02/12 アナウンス管理改修
	$message_no_tag = str_replace("&gt;",">",$message_no_tag);
	$message_no_tag = strip_tags($message_no_tag);				// add end hasegawa 2015/02/12 アナウンス管理改修

	// add start hasegawa 2017/09/15 課題要望No631
	$message2 = $_SESSION['announce_batch']['message2'];
	if (preg_match("/【＜】|【＞】/", $message2)) {
		// 先にtinymceが自動入力したタグをとる
		$message_no_tag = str_replace("&lt;","<",$message2);
		$message_no_tag = str_replace("&gt;",">",$message_no_tag);
		$message_no_tag = strip_tags($message_no_tag);
		// <>に直す
		$message_no_tag = tinymce_tag_decode($message_no_tag);
	}
	// add end hasegawa 2017/09/15

	$html.= <<<EOT
<br>
<input type="hidden" name="start_date" value="${start_date}" size="10">
<input type="hidden" name="end_date" value="${end_date}" size="10">
アナウンス： ${err_msg_msg}<br>
<textarea name="message" rows="5" cols="60" readonly>${message_no_tag}</textarea><br>
<input type="hidden" name="message" value="${message}">
<br>
EOT;

	// add start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
	$add_html = "";
	if (is_array($_SESSION['announce_batch'])) {
		foreach($_SESSION['announce_batch'] as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$add_html .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">";
			}
		}
	}
	// add end hasegawa 2018/07/19

//	$button_txt = "";
	if (MODE == "confirm") {

		//$html .= "<input type=\"submit\" name=\"regist\" value=\"登録する\">";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"regist\">";
		$html .= "<input type=\"hidden\" name=\"datacnt\" value=\"${entity_cnt}\">";
		$html .= "<input type=\"hidden\" name=\"message2\" value=\"${message2}\">";		// add hasegawa 2017/09/19 課題要望No631
		$html .= $add_html;						// add hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
		$html .= "</form>";
		$html .= "<input type=\"button\" name=\"regist\" value=\"登録する\" onclick=\"confirm_regist_exec();return false;\">";

	}
	if (MODE == "update_confirm") {

		$honbu_announce_num = $_SESSION['announce_batch']['honbu_announce_num'];
		//$html .= "<input type=\"submit\" name=\"update_exec\" value=\"更新する\">";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"update_exec\">";
		$html .= "<input type=\"hidden\" name=\"datacnt\" value=\"${entity_cnt}\">";
		$html .= "<input type=\"hidden\" name=\"honbu_announce_num\" value=\"${honbu_announce_num}\">";
		$html .= "<input type=\"hidden\" name=\"message2\" value=\"${message2}\">";		// add hasegawa 2017/09/19 課題要望No631
		$html .= $add_html;						// add hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
		$html .= "</form>";
		$html .= "<input type=\"button\" name=\"update_exec\" value=\"更新する\" onclick=\"confirm_update_exec();return false;\">";

	}

//	$html .= "<input type=\"button\" name=\"cancel\" value=\"戻る\" onclick=\"history.back();\">";
	if (MODE == "confirm") {	//"登録する
		$html .= <<<EOT
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="modoru_create">
EOT;
	}
	if (MODE == "update_confirm") {	//更新する
// upd hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応 (EOT内${add_html}追加）
		$html .= <<<EOT
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="hidden" name="mode" value="modoru_edit">
<input type="hidden" name="honbu_announce_num" value="${honbu_announce_num}">
<input type="hidden" name="start_date" value="${start_date}">
<input type="hidden" name="end_date" value="${end_date}">
<input type="hidden" name="subject" value="${subject}">
<input type="hidden" name="message" value="${message}">
<input type="hidden" name="message2" value="${message2}">
${add_html}
EOT;
	}

		$html .= <<<EOT
<input type="submit" name="confirm" value="戻る">
</form>
EOT;


	if ($_SERVER['HTTP_HOST'] == SERVER_NAME1) { 	//"10.128.1.36"	//すらら様コンテンツ開発
		if (XFER_DISTRIBUTE_SERVER == "STAG") {	//検証環境へのアップ
			$html .= "　(<span style=\"color: red;\">更新対象: 検証DB</span>)<br>";
		}
		if (XFER_DISTRIBUTE_SERVER == "PROD") {	//本番環境へのアップ
			//$html .= "　(<span style=\"color: blue;\">更新対象: 本番DB</span>)<br>";
		}
	}
	if ($_SERVER['HTTP_HOST'] == SERVER_NAME2) { 	//"10.128.1.35"	//Azet開発
		//$html .= "　(<span style=\"color: olive;\">更新対象: アゼット開発環境</span>)<br>";
	}

	// add start 2018/03/08 yoshizawa AWS環境判定
	if ($_SERVER['SERVER_ADDR'] == SERVER_IP1) { 	//"10.3.11.101"	//すらら様コンテンツ開発
		if (XFER_DISTRIBUTE_SERVER == "STAG") {	//検証環境へのアップ
			$html .= "　(<span style=\"color: red;\">更新対象: 検証DB</span>)<br>";
		}
		if (XFER_DISTRIBUTE_SERVER == "PROD") {	//本番環境へのアップ
			//$html .= "　(<span style=\"color: blue;\">更新対象: 本番DB</span>)<br>";
		}
	}
	if ($_SERVER['SERVER_ADDR'] == SERVER_IP2) { 	//"10.3.11.100"	//Azet開発
		//$html .= "　(<span style=\"color: olive;\">更新対象: アゼット開発環境</span>)<br>";
	}
	// add end 2018/03/08 yoshizawa AWS環境判定

	return $html;
}


//--------------------------------------------------------------------------------------------------
/**
 * 校舎IDのチェック、情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_school_id($school_id) {
	$err = array();
	$dbid = "";
	$school_name = "";

	$select_db = set_select_db("TOGO");	//統合サーバ接続

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return array($ERROR, $dbid, $school_name);
	}

	$sql = "select s.*, d.db_id  from school s".
	      " join db_connect d on s.enterprise_id=d.enterprise_id and d.mk_flg=0".
	      " join ms_sch_kiyk k on s.school_id = k.school_id".
	      " and k.start_ymd <= curdate() and curdate() <= k.end_ymd and k.mk_flg = '0'".
	      " and k.tb=(select max(tb) from ms_sch_kiyk k2 where s.school_id = k2.school_id and k2.mk_flg = 0)".
	      " where s.mk_flg=0 and s.school_id='".$school_id."';";

	$result = $connect_db->exec_query($sql);
	if ($result->num_rows > 0) {
		$list=$connect_db->fetch_assoc($result);
		$dbid = $list['db_id'];
		$school_name = $list['school_name'];

	} else {
		//該当データなし
		//$err[] = "該当する 校舎ID が見つかりません。(校舎ID:".$school_id.")";
		$err[] = "<span style=\"color:red;\">該当する 校舎ID が見つかりません。</span>";
		return array($err, $dbid, $school_name);
	}

	//	閲覧DB切断
	$connect_db->close();

	return array($err, $dbid, $school_name);
}



//--------------------------------------------------------------------------------------------------
/**
 * 登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function regist() {

	$html = "";

	$datacnt = $_POST['datacnt'];	//データ件数

	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	$message2 = $_POST['message2'];	// add hasegawa 2017/09/15  課題要望No631

	// upd start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
	// // add start hasegawa 2017/09/15  課題要望No631
	// if (preg_match("/【＜】|【＞】/", $message2)) {
	// 	$message = $message2;
	// }
	// // add end hasegawa 2017/09/15
	$REPLACER = array();
	if (is_array($_POST)) {
		foreach($_POST as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$REPLACER[$key] = $val;
			}
		}
	}
	if($message2) {
		$message = tinymce_style_decode($message2, $REPLACER);
	}
	// upd end hasegawa 2018/07/19

	if (strlen($message) == 0) {
		//$err_msg_msg = "<span style=\"color:red;\">入力されていません。</span>";
		$datacnt=0;
	}

	if ($datacnt < 1) {
		$html .= "<br><span style=\"color:red;\">入力データがありません。</span><br>";
		return $html;

	} else {

		$select_db = set_select_db("CONTENTSX");	//総合サーバ接続

		$connect_db = new connect_db();
		$connect_db->set_db($select_db);
		$ERROR = $connect_db->set_connect_db();
		if ($ERROR) {
			return $ERROR;
		}

		//基本データ登録
		$INSERT_DATA = array();
		$INSERT_DATA['start_date'] = $start_date;
		$INSERT_DATA['end_date'] = $end_date;
		$INSERT_DATA['subject'] = $subject;
		$INSERT_DATA['message'] = $message;
		$INSERT_DATA['display'] = 1;
		$INSERT_DATA['ins_syr_id']	= 'add';
		$INSERT_DATA['upd_syr_id']	= 'add';
		$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] = "now()";
		$INSERT_DATA['upd_date'] = "now()";

		$ERROR = $connect_db->insert("honbu_announce_data", $INSERT_DATA);	//データ登録処理
		if ($ERROR) {
			//echo "*error:".$ERROR[0];exit;
			return $ERROR;
		}

		$sql2 = "select last_insert_id() as last_id from honbu_announce_data;";
		$last_id = -1;
		$result2 = $connect_db->exec_query($sql2);
		if ($result2->num_rows > 0) {
			$list2=$connect_db->fetch_assoc($result2);
			$last_id = $list2['last_id'];
		}

		//校舎情報の登録
		$cnt = 1;
		$failsafe = 1000;
		$entity_cnt = 0;
		while($cnt <= $datacnt && $failsafe > 0) {

			$school_id = $_POST['school_id_'.$cnt];
			$db_id = $_POST['db_id_'.$cnt];
			$entity_cnt++;

			unset($INSERT_DATA);
			$INSERT_DATA = array();
			$INSERT_DATA['honbu_announce_num'] = $last_id;
			$INSERT_DATA['school_id'] = $school_id;
			$INSERT_DATA['db_id'] = $db_id;
			$INSERT_DATA['display'] = 1;
			$INSERT_DATA['ins_syr_id']	= 'add';
			$INSERT_DATA['upd_syr_id']	= 'add';
			$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['ins_date'] = "now()";
			$INSERT_DATA['upd_date'] = "now()";
			$ERROR = $connect_db->insert("honbu_announce_school", $INSERT_DATA);	//データ登録処理
			if ($ERROR) {
				return $ERROR;
			}

			$failsafe--;
			$cnt++;
		}	//while

		//	閲覧DB切断
		$connect_db->close();

	}

	$html .= "<br>登録しました。<br>";

//	$html .= xfer_distribute_db($last_id, "write");	//分散データベースへの反映
	$html .= xfer_distribute_db($last_id, "write");	//分散データベースへの反映


	return $html;
}



/**
 * 編集処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function edit() {
//	global $L_DB;

	$html = "";
	$honbu_announce_num = $_POST['honbu_announce_num'];
	$_SESSION['announce_batch']['honbu_announce_num'] = $honbu_announce_num;

	//登録済みデータの取得
	if ($honbu_announce_num > 0) {

		if (MODE == "modoru_edit") {	//編集画面から戻って来たとき
			$_SESSION['announce_batch']['start_date'] = $_POST['start_date'];
			$_SESSION['announce_batch']['end_date'] = $_POST['end_date'];
			$_SESSION['announce_batch']['subject'] = $_POST['subject'];
			$_SESSION['announce_batch']['message'] = $_POST['message'];
			// add start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
			$_SESSION['announce_batch']['message2'] = $_POST['message2'];
			if (is_array($_POST)) {
				foreach ($_POST as $key => $val) {
					if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
						$_SESSION['announce_batch'][$key] = $val;
					}
				}
			}
			// add end hasegawa 2017/09/15

			$html = edit_html();

		} else {	//if (MODE == "modoru_edit") {	//通常の編集開始

			$select_db = set_select_db("CONTENTSX");	//総合サーバ接続

			$connect_db = new connect_db();
			$connect_db->set_db($select_db);
			$ERROR = $connect_db->set_connect_db();
			if ($ERROR) {
				return $ERROR;
			}

			$select_db2 = set_select_db("TOGO");
			$connect_db2 = new connect_db();
			$connect_db2->set_db($select_db2);
			$ERROR = $connect_db2->set_connect_db();
			if ($ERROR) {
				$connect_db->close();
				return $ERROR;
			}

			//データの取得
			$sql = "SELECT * FROM honbu_announce_data WHERE honbu_announce_num=${honbu_announce_num} AND display='1' AND mk_flg='0';";
			$result = $connect_db->exec_query($sql);
			if ($result->num_rows == 0) {
				$html = "<br>アナウンスデータが見つかりません。(n=${honbu_announce_num})<br>";

			} else {
				//基本情報の取得
				$list=$connect_db->fetch_assoc($result);

				if ($list['start_date'] != "0000-00-00") {
					$_SESSION['announce_batch']['start_date'] = str_replace("-", "/", $list['start_date']);
				} else {
					$_SESSION['announce_batch']['start_date'] = "";
				}
				if ($list['end_date'] != "0000-00-00") {
					$_SESSION['announce_batch']['end_date'] = str_replace("-", "/", $list['end_date']);
				} else {
					$_SESSION['announce_batch']['end_date'] = "";
				}
				$_SESSION['announce_batch']['subject'] = $list['subject'];
				$_SESSION['announce_batch']['message'] = $list['message'];

				// add start hasegawa 2017/09/19 課題要望No631
				if($_SESSION['announce_batch']['message']) {
					$_SESSION['announce_batch']['message'] = tinymce_tag_decode($_SESSION['announce_batch']['message']);
				}
				// add end hasegawa 2017/09/19

				//校舎ID情報の取得
				$entity_cnt = 0;	//校舎IDの件数カウント
				$sql2 = "SELECT * FROM honbu_announce_school WHERE honbu_announce_num='${honbu_announce_num}' AND display='1' AND mk_flg='0';";
				$result2 = $connect_db->exec_query($sql2);
				if ($result2->num_rows > 0) {
					while($list2 = $connect_db->fetch_assoc($result2)) {
						$school_id = $list2['school_id'];
						$db_id = $list2['db_id'];

						$sql3 = "SELECT school_name FROM school WHERE school_id='".$school_id."';";
						$result3 = $connect_db2->exec_query($sql3);
						if ($result3->num_rows > 0) {
							$list3 = $connect_db2->fetch_assoc($result3);
							$school_name = $list3['school_name'];

							$entity_cnt++;
							$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $school_id;
							$_SESSION['announce_batch']['db_id_'.$entity_cnt] = $db_id;
							$_SESSION['announce_batch']['school_name_'.$entity_cnt] = $school_name;

						} else {
							$html = "<br>校舎IDデータが見つかりません。(n=${honbu_announce_num})<br>";
						}

					}
					$_SESSION['announce_batch']['entity_cnt'] = $entity_cnt;

				} else {
					$html = "<br>校舎IDデータが見つかりません。(n=${honbu_announce_num})<br>";
				}

				$html = edit_html();
			}

			//	閲覧DB切断
			$connect_db2->close();
			$connect_db->close();

		}	//if (MODE == "modoru_edit") {

	} else {
		$html = "<br>データが見つかりません。(n=${honbu_announce_num})<br>";
	}

	return $html;
}

/**
 * 編集画面HTMLの組み立て
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function edit_html() {

//echo "<hr>\n<pre>\n";


	$entity_cnt = $_SESSION['announce_batch']['entity_cnt'];
	$datacnt = 5;
	if ($datacnt < $entity_cnt) {
		$datacnt = $entity_cnt;
	}

	$start_date = $_SESSION['announce_batch']['start_date'];
	if ($_POST['end_date']) { $end_date = $_POST['end_date']; }

	$end_date = $_SESSION['announce_batch']['end_date'];
	if ($_POST['start_date']) { $start_date = $_POST['start_date'];	}

	$err_msg_start_date = $_SESSION['announce_batch']['err_msg_start_date'];
	$err_msg_end_date = $_SESSION['announce_batch']['err_msg_end_date'];

	$subject = $_SESSION['announce_batch']['subject'];
	$message = $_SESSION['announce_batch']['message'];

	// add start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
	if ($_SESSION['announce_batch']['message2']) {
		$message = $_SESSION['announce_batch']['message2'];
	}

	$REPLACER = array();
	if (is_array($_SESSION['announce_batch'])) {
		foreach ($_SESSION['announce_batch'] as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$REPLACER[$key] = $val;
			}
		}
	}

	$message = tinymce_tag_decode($message);
	$message = tinymce_style_decode($message,$REPLACER);
	// add end hasegawa 2017/09/15

	$err_msg_msg = $_SESSION['announce_batch']['err_msg_msg'];

	$honbu_announce_num = $_SESSION['announce_batch']['honbu_announce_num'];

	$html = more_scriptcss();	// add hasegawa 2016/02/01
	$html .= <<<EOT
<br>

<form action="$_SERVER[PHP_SELF]" method="POST" name="announse_form">

<table class="member_formx" id="school_list_table">
<tr class="member_form_menu">
<th>校舎ID</th>
<th>分散DB</th>
<th>校舎名</th>
EOT;

	if ($entity_cnt == 0) {
		$html .= "<th class=\"schoolid_check\"><span style=\"color:red;\">入力データがありません。</span></th>";
	} else {
		$html .= "<th class=\"schoolid_check\"></th>";
	}

	$html .= "</tr>";


	$cnt = 1;
	$failsafe = 1000;
	while($cnt <= $datacnt && $failsafe > 0) {

		$html .= "<tr class=\"member_form_menu\">";

		$tmp_school_id = "";
		if ($entity_cnt >= $cnt) {
			if (strlen($_SESSION['announce_batch']['school_id_'.$cnt]) > 0) {
				$tmp_school_id = $_SESSION['announce_batch']['school_id_'.$cnt];
			}
		}
		$html .= "<td><input type=\"text\" name=\"school_id_${cnt}\" value=\"${tmp_school_id}\" onChange=\"erase_school_id(${cnt})\"></td>";

		$tmp_db_id_id = "";
		if ($entity_cnt >= $cnt) {
			if (strlen($_SESSION['announce_batch']['db_id_'.$cnt]) > 0) {
				$tmp_db_id_id = $_SESSION['announce_batch']['db_id_'.$cnt];
			}
		}
		$html .= "<td><span id=\"school_list_1_${cnt}\">${tmp_db_id_id}&nbsp;</span></td>";

		$tmp_school_name = "";
		if ($entity_cnt >= $cnt) {
			if (strlen($_SESSION['announce_batch']['school_name_'.$cnt]) > 0) {
				$tmp_school_name = $_SESSION['announce_batch']['school_name_'.$cnt];
			}
		}
		$html .= "<td><span id=\"school_list_2_${cnt}\">${tmp_school_name}&nbsp;</span></td>";

		$tmp_msg = "";
		if (MODE == "update_confirm") {
			if (strlen($_SESSION['announce_batch']['school_id_errmsg_'.$cnt]) > 0) {
				$tmp_msg = $_SESSION['announce_batch']['school_id_errmsg_'.$cnt];
			}
		}
		$html .= "<td class=\"schoolid_check\"><span id=\"school_list_3_${cnt}\">${tmp_msg}</span></td>";
		$html .= "</tr>";

		$cnt++;
		$failsafe--;
	}

	$html .= <<<EOT
</table>
<br>
<input type="button" name="add_line" value="入力+５" onclick="add_lines();return false;"><br>
<br>
表示期限：${err_msg_start_date}${err_msg_end_date}<br>
<input type="text" id="s_start_date" name="start_date" size="10" maxlength="10" value="${start_date}">
<input type="button" value="▼" onclick="dsp_calendar('s'); return false;"> ～
<input type="text" id="s_end_date" name="end_date" size="10" maxlength="10" value="${end_date}">
<input type="button" value="▼" onclick="dsp_calendar('e'); return false;"><br>
<div id="s_calendar" class="calendar_div" style="z-index:1">
</div>
<div id="e_calendar" class="calendar_div" style="z-index:1">
</div>
<span class="comment_desc">※入力形式: 2016/02/01</span><br>
<br>
アナウンス：${err_msg_msg}<br>
<textarea name="message" id="message" rows="5" cols="60">${message}</textarea><br>
<input type="button" value="アナウンスの確認" class="announ_but" onclick="check_message(tinymce);">
<span>※htmlタグを使用する際は必ず確認してください。</span><br><br>
<!--<input type="submit" name="update_confirm" value="更新内容を確認する">-->
<input type="button" onclick= "check_tags_mce(tinymce);" name="update_confirm" value="更新内容を確認する">
<input type="hidden" name="mode" value="update_confirm">
<input type="hidden" id="row_datacnt" name="datacnt" value="${datacnt}">
<input type="hidden" name="honbu_announce_num" value="${honbu_announce_num}">
<input type="hidden" name="message2" value="">

</form>
<!--
<input type="button" name="cancel" value="戻る" onclick="history.back();">
-->
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="submit" name="cancel" value="戻る">
<input type="hidden" name="mode" value="show_list">
</form>

EOT;

	return $html;
}


//--------------------------------------------------------------------------------------------------
/**
 * 日付形式チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function checkDateFormat($hizuke) {
    return $hizuke === date("Y/m/d", strtotime($hizuke." 00:00:00"));
}



/**
 * アップデート処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function update_exec() {

	$html = "";

	$datacnt = $_POST['datacnt'];	//データ件数
	$honbu_announce_num = $_POST['honbu_announce_num'];

	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];

	// upd start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
	// // add start hasegawa 2017/09/15  課題要望No631
	// if($_POST['message']) {
	// 	$message2 = $_POST['message2'];
	// 	if (preg_match("/【＜】|【＞】/", $message2)) {
	// 		$message = $message2;
	// 	}
	// }
	// // add end hasegawa 2017/09/15
	$REPLACER = array();
	if (is_array($_POST)){
		foreach ($_POST as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$REPLACER[$key] = $val;
			}
		}
	}

	if ($_POST['message2']) {
		$message = tinymce_style_decode($_POST['message2'], $REPLACER);
	}
	// add end hasegawa 2018/07/19


	if (strlen($message) == 0) {
		//$err_msg_msg = "<span style=\"color:red;\">入力されていません。</span>";
		$datacnt=0;
	}

	if ($datacnt < 1) {
		$html .= "<br><span style=\"color:red;\">入力データがありません。</span><br>";
		return $html;

	} else {

		$honbu_announce_num = $_POST['honbu_announce_num'];

		$select_db = set_select_db("CONTENTSX");	//B総合サーバ接続

		$connect_db = new connect_db();
		$connect_db->set_db($select_db);
		$ERROR = $connect_db->set_connect_db();
		if ($ERROR) {
			return $ERROR;
		}

		//基本データの更新
		$INSERT_DATA = array();
		$INSERT_DATA['start_date'] = $start_date;
		$INSERT_DATA['end_date'] = $end_date;
		$INSERT_DATA['subject'] = $subject;
		$INSERT_DATA['message'] = $message;
		$INSERT_DATA['upd_syr_id']	= 'update';
		$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']	= "now()";
		$where  = " WHERE honbu_announce_num = '".$honbu_announce_num."'";
		$where .= " LIMIT 1;";

		$ERROR = $connect_db->update("honbu_announce_data", $INSERT_DATA, $where);
		if ($ERROR) {
			//echo "*error:".$ERROR[0];exit;
			return $ERROR;
		}

		//現状の校舎IDリストを取得する
		$check_school_id_ary = array();
		$school_id_ary = array();
		$sql = "SELECT * FROM honbu_announce_school WHERE honbu_announce_num='${honbu_announce_num}' AND display='1' AND mk_flg='0';";
		$result = $connect_db->exec_query($sql);
		if ($result->num_rows > 0) {
			while($list = $connect_db->fetch_assoc($result)) {
				$honbu_announce_school_num = $list['honbu_announce_school_num'];
				$school_id = $list['school_id'];
				$check_school_id_ary[] = $school_id;
				$school_id_ary[$school_id][0] = $honbu_announce_school_num;
				$school_id_ary[$school_id][1] = $list['db_id'];
				$school_id_ary[$school_id][2] = $list['display'];
				$school_id_ary[$school_id][3] = $list['mk_flg'];
				$school_id_ary[$school_id][4] = 0;	//使用中フラグ(=0)、POSTデータと比較して使用しているなら=1にする。
			}
		}

		//入力データと、DBの現状をチェックして差を確認する。
		//$datacnt = $_POST['datacnt'];	//データ件数
		$cnt = 1;
		$failsafe = 1000;
		while($cnt <= $datacnt && $failsafe > 0) {
			$post_school_id = $_POST['school_id_'.$cnt];
			$post_db_id = $_POST['db_id_'.$cnt];

			if(in_array($post_school_id, $check_school_id_ary)) {
				//有効なものが存在する
				$school_id_ary[$post_school_id][4] = 1;	//使用中フラグ(=0)、POSTデータと比較して使用するなら=1にする。
			} else {
				//有効なものが存在しない（追加する）
				//db_id を探す
				list($err, $dbid, $school_name) = check_school_id($school_id);
				if (count($err) == 0) {
					//正常に db_id が取得できたとき
					$check_school_id_ary[] = $post_school_id;
					$school_id_ary[$post_school_id][0] = -1;
					$school_id_ary[$post_school_id][1] = $dbid;
					$school_id_ary[$post_school_id][2] = 1;
					$school_id_ary[$post_school_id][3] = 0;
					$school_id_ary[$post_school_id][4] = 1;	//使用中フラグ(=1)、使用しないなら=0。

				} else {
					//何かエラーがあった場合
					//	閲覧DB切断
					$connect_db->close();

					$html .= "<br>".$err[0]."<br>";
					return $html;
				}

			}

			$cnt++;
			$failsafe--;
		}

	//結果を honbu_announce_school へ出力する
	foreach ($school_id_ary AS $tmp_school_id => $tmp_data) {
		if ($tmp_data[4] == 0) {	//１件を論理削除する
			unset($INSERT_DATA);
			$INSERT_DATA['display']	= '2';
			$INSERT_DATA['mk_flg']	= '1';
			$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date']	= "now()";
			$INSERT_DATA['upd_syr_id']	= 'del';
			$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']	= "now()";
			$where  = " WHERE honbu_announce_num = '".$honbu_announce_num."' AND honbu_announce_school_num = '".$tmp_data[0]."'";
			$where .= " LIMIT 1;";

			$ERROR = $connect_db->update("honbu_announce_school", $INSERT_DATA, $where);
			if ($ERROR) {
				//何かエラーがあった場合
				//	閲覧DB切断
				$connect_db->close();

				$html .= "<br>".$ERROR[0]."<br>";
				return $html;
			}
		}

		if ($tmp_data[0] == -1) {	//１件追加する
			unset($INSERT_DATA);
			$INSERT_DATA = array();
			$INSERT_DATA['honbu_announce_num'] = $honbu_announce_num;
			$INSERT_DATA['school_id'] = $tmp_school_id;
			$INSERT_DATA['db_id'] = $tmp_data[1];
			$INSERT_DATA['display'] = 1;
			$INSERT_DATA['ins_syr_id']	= 'add';
			$INSERT_DATA['upd_syr_id']	= 'add';
			$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['ins_date'] = "now()";
			$INSERT_DATA['upd_date'] = "now()";

			$ERROR = $connect_db->insert("honbu_announce_school", $INSERT_DATA);	//データ登録処理
			if ($ERROR) {
				//	閲覧DB切断
				$connect_db->close();

				$html .= "<br>".$ERROR[0]."<br>";
				return $html;
			}

		}

		if ($tmp_data[4] == 1) {	//１件の更新日のみをアップデートする
			unset($INSERT_DATA);
			$INSERT_DATA['upd_syr_id']	= 'update';
			$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']	= "now()";
			$where  = " WHERE honbu_announce_num = '".$honbu_announce_num."' AND honbu_announce_school_num = '".$tmp_data[0]."'";
			$where .= " LIMIT 1;";

			$ERROR = $connect_db->update("honbu_announce_school", $INSERT_DATA, $where);
			if ($ERROR) {
				//何かエラーがあった場合
				//	閲覧DB切断
				$connect_db->close();

				$html .= "<br>".$ERROR[0]."<br>";
				return $html;
			}
		}

	}
		//	閲覧DB切断
		$connect_db->close();

	}

	$html .= "<br>更新しました。<br>";

	$html .= xfer_distribute_db($honbu_announce_num,"write");		//分散データベースへの反映

	return $html;
}



/**
 * 削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function delete_confirm() {
	$html = delete_confirm_html();
	return $html;
}



/**
 * 削除前の確認画面
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function delete_confirm_html() {
	$html = "";
	$html .= more_scriptcss();	// add hasegawa 2016/02/01

	$honbu_announce_num = $_POST['honbu_announce_num'];

	//$select_db = $L_DB['srlctd03SOGO'];
	//$L_DB['srlctd03SOGO']['NAME'] = '開発コアDB総合サーバテスト用';
	//$L_DB['srlctd03SOGO']['DBNAME'] = 'CONTENTS160';
	$select_db = set_select_db("CONTENTSX");

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	$sql = "SELECT * FROM honbu_announce_data WHERE display='1' AND mk_flg='0' AND honbu_announce_num='".$honbu_announce_num."';";
	$result = $connect_db->exec_query($sql);
	if ($result->num_rows == 0) {
		//	閲覧DB切断
		$connect_db->close();

		$html = "<br>データがありません。<br>";
		return $html;
	}

	$list = $connect_db->fetch_assoc($result);
	$start_date = $list['start_date'];
	$end_date = $list['end_date'];
	$subject = $list['subject'];
	$message = $list['message'];

	$message_no_tag = str_replace("&lt;","<",$message);			// add start hasegawa 2015/02/12 アナウンス管理改修
	$message_no_tag = str_replace("&gt;",">",$message_no_tag);
	$message_no_tag = strip_tags($message_no_tag);				// add end hasegawa 2015/02/12 アナウンス管理改修
	$message_no_tag = tinymce_tag_decode($message_no_tag);			// add hasegawa 2017/09/19 課題要望No631

	$html .= <<<EOT
<form action="$_SERVER[PHP_SELF]" method="POST" name="frm_delete">
<br>
<table class="member_formx">
<tr class="member_form_menu">
<th>校舎ID</th>
<th>分散DB</th>
<th>校舎名</th>
</tr>
EOT;

	$sql2 = "SELECT * FROM honbu_announce_school WHERE display='1' AND mk_flg='0' AND honbu_announce_num='".$honbu_announce_num."';";
	$result2 = $connect_db->exec_query($sql2);
	if ($result2->num_rows == 0) {
		//	閲覧DB切断
		$connect_db->close();

		$html = "<br>データがありません。<br>";
		return $html;
	}

	while($list2 = $connect_db->fetch_assoc($result2)) {
		$school_id = $list2['school_id'];
		$db_id = $list2['db_id'];

		list($err, $dbidxx, $school_name) = check_school_id($school_id);
		if (count($err) != 0) {
			//	閲覧DB切断
			$connect_db->close();
			$html = "<br>校舎IDデータがありません。(id:${school_id})<br>";
			return $html;
		} else {
			$html .= <<<EOT
<tr class="member_form_menu">
<td>${school_id}</td>
<td>${db_id}</td>
<td>${school_name}</td>
</tr>
EOT;
		}

	}
	$html .= "</table>";
	$html .= "<br>";

	$period_disp1 = "表示期限： ";
	$period_disp2 = "";
	if (strlen($start_date) > 0 && $start_date !="0000-00-00") {
		$start_date = str_replace("-", "/", $start_date);
		$period_disp1 .= $start_date;
	} else {
		$period_disp1 .= "(開始日指定なし)";
	}
	$period_disp1 .= " ～ ";
	if (strlen($end_date) > 0 && $end_date !="0000-00-00") {
		$end_date = str_replace("-", "/", $end_date);
		$period_disp1 .= $end_date;
	} else {
		$period_disp1 .= "(終了日指定なし)";
	}
	$period_disp1 .= "<br>";
	$html .= $period_disp1;


	$html.= <<<EOT

<br>
アナウンス：<br>
<textarea name="message" rows="5" cols="60" readonly>${message_no_tag}</textarea><br>
<br>
EOT;

	//$html .= "<input type=\"submit\" name=\"delete_exec\" value=\"削除する\">";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"delete_exec\">";
	$html .= "<input type=\"hidden\" name=\"honbu_announce_num\" value=\"${honbu_announce_num}\">";
	$html .= "</form>";
	$html .= "<input type=\"button\" name=\"delete_exec\" value=\"削除する\" onclick=\"confirm_delete_exec();return false;\">";
	$html .= "<br>";

	$html .= <<<EOT
<form action="$_SERVER[PHP_SELF]" method="POST">
<input type="submit" name="cancel" value="戻る">
<input type="hidden" name="mode" value="show_list">
</form>
<br>
EOT;

	//	閲覧DB切断
	$connect_db->close();


	return $html;
}



/**
 * 削除処理の実行
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function delete_exec() {
	$honbu_announce_num = $_POST['honbu_announce_num'];

	$html = "";

	$html .= xfer_distribute_db($honbu_announce_num,"delete");		//先に分散データベースへの反映

	$select_db = set_select_db("CONTENTSX");	//総合サーバ接続

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	//基本情報の論理削除する
	$INSERT_DATA = array();
	$INSERT_DATA['display']	= '2';
	$INSERT_DATA['mk_flg']	= '1';
	$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
	$INSERT_DATA['mk_date']	= "now()";
	$INSERT_DATA['upd_syr_id']	= 'del';
	$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date']	= "now()";
	$where  = " WHERE honbu_announce_num = '".$honbu_announce_num."'";
	$where .= " LIMIT 1;";

	$ERROR = $connect_db->update("honbu_announce_data", $INSERT_DATA, $where);
	if ($ERROR) {
		//何かエラーがあった場合
		//	閲覧DB切断
		$connect_db->close();

		$html .= "<br>".$ERROR[0]."<br>";
		return $html;
	}

	//校舎ID情報を論理削除する
	$sql2 = "SELECT * FROM honbu_announce_school WHERE display='1' AND mk_flg='0' AND honbu_announce_num='".$honbu_announce_num."';";
	$result2 = $connect_db->exec_query($sql2);
	if ($result2->num_rows == 0) {
		//	閲覧DB切断
		$connect_db->close();

		$html = "<br>削除する校舎IDデータがありません。<br>";
		return $html;
	}

	while($list2 = $connect_db->fetch_assoc($result2)) {
		$honbu_announce_school_num = $list2['honbu_announce_school_num'];
		unset($INSERT_DATA);
		$INSERT_DATA['display']	= '2';
		$INSERT_DATA['mk_flg']	= '1';
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']	= "now()";
		$INSERT_DATA['upd_syr_id']	= 'del';
		$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']	= "now()";
		$where  = " WHERE honbu_announce_num = '".$honbu_announce_num."' AND honbu_announce_school_num = '".$honbu_announce_school_num."'";
		$where .= " LIMIT 1;";

		$ERROR = $connect_db->update("honbu_announce_school", $INSERT_DATA, $where);
		if ($ERROR) {
			//何かエラーがあった場合
			//	閲覧DB切断
			$connect_db->close();

			$html .= "<br>".$ERROR[0]."<br>";
			return $html;
		}
	}

	//	閲覧DB切断
	$connect_db->close();

	$html .= "<br>削除しました。<br>";

	return $html;
}



/**
 * 確認処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check() {
	$html = "<br>check();";

	return $html;
}



/**
 * データベース指定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function set_select_db($sel_db) {
	global $L_DB;

	unset($select_db);

	//すらら様コンテンツ開発
	$server_name_1 = SERVER_NAME1;	//"10.128.1.36"	//すらら様コンテンツ開発

	//Azetコンテンツ開発
	$server_name_2 = SERVER_NAME2;	//"10.128.1.35"	//Azetコンテンツ開発

	//すらら様コンテンツ開発
	$server_ip_1 = SERVER_IP1;	// add 2018/03/08 yoshizawa AWS環境判定

	//Azetコンテンツ開発
	$server_ip_2 = SERVER_IP2;	// add 2018/03/08 yoshizawa AWS環境判定

	//	リクエストURL
	$http_host = $_SERVER['HTTP_HOST'];

	//	サーバーIP
	$server_addr = $_SERVER['SERVER_ADDR'];	// add 2018/03/08 yoshizawa AWS環境判定


//echo "*".$http_host."*<br>";
//echo "*server_name_1=".$server_name_1."<br>";
//echo "*server_name_2=".$server_name_2."<br>";
//echo "*http_host=".$http_host."<br>";
//echo "*XFER_DISTRIBUTE_SERVER=".XFER_DISTRIBUTE_SERVER."<br>";

	// update start 2018/03/08 yoshizawa AWS環境判定
	// if ($http_host == $server_name_1) {	//すらら様コンテンツ開発
	if ($http_host == $server_name_1 || $server_addr == $server_ip_1) {	//すらら様コンテンツ開発
	// update end 2018/03/08 yoshizawa AWS環境判定

		if (XFER_DISTRIBUTE_SERVER == "PROD") {	//本番 分散サーバーへアップする。
			if ($sel_db == "CONTENTSX") {
				$select_db = $L_DB['srlctd03cts'];
				////	開発コアDB CONTENTS用(すらら様)
				//$L_DB['srlctd03cts']['NAME'] = '開発コアDB CONTENTS';
				//$L_DB['srlctd03cts']['DBHOST'] = '10.128.1.23';
				//$L_DB['srlctd03cts']['DBUSER'] = 'axxkc01';
				//$L_DB['srlctd03cts']['DBPASSWD'] = 'kc_01axx';
				//$L_DB['srlctd03cts']['DBNAME'] = 'CONTENTS';
				//$L_DB['srlctd03cts']['DBPORT'] = '3306';
			}

			if ($sel_db == "TOGO") {
				$select_db = $L_DB['srlchd01'];
				////	本番統合DB
				//$L_DB['srlchd01']['NAME'] = '本番統合DB';
				//$L_DB['srlchd01']['DBHOST'] = '10.128.1.1';
				//$L_DB['srlchd01']['DBUSER'] = 'axxhe01';
				//$L_DB['srlchd01']['DBPASSWD'] = 'he_01axx';
				//$L_DB['srlchd01']['DBNAME'] = 'SRLEH01';
				//$L_DB['srlchd01']['DBPORT'] = '3306';
			}

			if ($sel_db == "BUNSAN#1") {
				$select_db = $L_DB['srlchd02'];
				////	本番分散DB#1
				//$L_DB['srlchd02']['NAME'] = '本番分散DB#1';
				//$L_DB['srlchd02']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd02']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd02']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd02']['DBNAME'] = 'SRLCH01';
				//$L_DB['srlchd02']['DBPORT'] = '3301';
			}
			if ($sel_db == "BUNSAN#2") {
				$select_db = $L_DB['srlchd03'];
				////	本番分散DB#2
				//$L_DB['srlchd03']['NAME'] = '本番分散DB#2';
				//$L_DB['srlchd03']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd03']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd03']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd03']['DBNAME'] = 'SRLCH02';
				//$L_DB['srlchd03']['DBPORT'] = '3302';
			}
			if ($sel_db == "BUNSAN#3") {
				$select_db = $L_DB['srlchd04'];
				////	本番分散DB#3
				//$L_DB['srlchd04']['NAME'] = '本番分散DB#3';
				//$L_DB['srlchd04']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd04']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd04']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd04']['DBNAME'] = 'SRLCH03';
				//$L_DB['srlchd04']['DBPORT'] = '3303';
			}
			if ($sel_db == "BUNSAN#4") {
				$select_db = $L_DB['srlchd05'];
				////	本番分散DB#4
				//$L_DB['srlchd05']['NAME'] = '本番分散DB#4';
				//$L_DB['srlchd05']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd05']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd05']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd05']['DBNAME'] = 'SRLCH04';
				//$L_DB['srlchd05']['DBPORT'] = '3304';
			}
			if ($sel_db == "BUNSAN#5") {
				$select_db = $L_DB['srlchd06'];
				////	本番分散DB#5
				//$L_DB['srlchd06']['NAME'] = '本番分散DB#5';
				//$L_DB['srlchd06']['DBHOST'] = '10.128.1.3';
				//$L_DB['srlchd06']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd06']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd06']['DBNAME'] = 'SRLCH05';
				//$L_DB['srlchd06']['DBPORT'] = '3305';
			}
			if ($sel_db == "BUNSAN#6") {
				$select_db = $L_DB['srlchd07'];
				////	本番分散DB#6
				//$L_DB['srlchd07']['NAME'] = '本番分散DB#6';
				//$L_DB['srlchd07']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd07']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd07']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd07']['DBNAME'] = 'SRLCH06';
				//$L_DB['srlchd07']['DBPORT'] = '3306';
			}
			if ($sel_db == "BUNSAN#7") {
				$select_db = $L_DB['srlchd08'];
				////	本番分散DB#7
				//$L_DB['srlchd08']['NAME'] = '本番分散DB#7';
				//$L_DB['srlchd08']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd08']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd08']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd08']['DBNAME'] = 'SRLCH07';
				//$L_DB['srlchd08']['DBPORT'] = '3307';
			}
			if ($sel_db == "BUNSAN#8") {
				$select_db = $L_DB['srlchd09'];
				////	本番分散DB#8
				//$L_DB['srlchd09']['NAME'] = '本番分散DB#8';
				//$L_DB['srlchd09']['DBHOST'] = '10.128.1.2';
				//$L_DB['srlchd09']['DBUSER'] = 'srlhc01';
				//$L_DB['srlchd09']['DBPASSWD'] = 'hc_01srl';
				//$L_DB['srlchd09']['DBNAME'] = 'SRLCH08';
				//$L_DB['srlchd09']['DBPORT'] = '3308';
			}

			// add start oda 2020/07/21 スケールアウト インスタンス作成時解放
			if ($sel_db == "BUNSAN#9") {
				$select_db = $L_DB['srlchd10'];
			}
			if ($sel_db == "BUNSAN#10") {
				$select_db = $L_DB['srlchd11'];
			}
			if ($sel_db == "BUNSAN#11") {
				$select_db = $L_DB['srlchd12'];
			}
			if ($sel_db == "BUNSAN#12") {
				$select_db = $L_DB['srlchd13'];
			}
			if ($sel_db == "BUNSAN#13") {
				$select_db = $L_DB['srlchd14'];
			}
// 			if ($sel_db == "BUNSAN#14") {
// 				$select_db = $L_DB['srlchd15'];
// 			}
// 			if ($sel_db == "BUNSAN#15") {
// 				$select_db = $L_DB['srlchd16'];
// 			}
			// add end oda 2020/07/21 スケールアウト インスタンス作成時解放

		}	//if (XFER_DISTRIBUTE_SERVER == "PROD") {	//本番 分散サーバーへアップする。


		if (XFER_DISTRIBUTE_SERVER == "STAG") {	//検証 分散サーバーへアップする。
			if ($sel_db == "CONTENTSX") {
				$select_db = $L_DB['srlctd03cts'];
				////	開発コアDB CONTENTS用(すらら様)
				//$L_DB['srlctd03cts']['NAME'] = '開発コアDB CONTENTS';
				//$L_DB['srlctd03cts']['DBHOST'] = '10.128.1.23';
				//$L_DB['srlctd03cts']['DBUSER'] = 'axxkc01';
				//$L_DB['srlctd03cts']['DBPASSWD'] = 'kc_01axx';
				//$L_DB['srlctd03cts']['DBNAME'] = 'CONTENTS';
				//$L_DB['srlctd03cts']['DBPORT'] = '3306';
			}

			if ($sel_db == "TOGO") {
				$select_db = $L_DB['srlctd01'];
				////	検証統合DB
				//$L_DB['srlctd01']['NAME'] = '検証統合DB';
				//$L_DB['srlctd01']['DBHOST'] = '10.128.1.21';
				//$L_DB['srlctd01']['DBUSER'] = 'axxse01';
				//$L_DB['srlctd01']['DBPASSWD'] = 'se_01axx';
				//$L_DB['srlctd01']['DBNAME'] = 'SRLES01';	//	change ookawara 2011/10/19	SRLEH01 → SRLES01
				//$L_DB['srlctd01']['DBPORT'] = '3306';
			}

			if ($sel_db == "BUNSAN#1") {
				$select_db = $L_DB['srlctd0201'];
				///	検証分散DB#1
				//$/L_DB['srlctd0201']['NAME'] = '検証分散DB#1';
				//$L_DB['srlctd0201']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0201']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0201']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0201']['DBNAME'] = 'SRLCS01';
				//$L_DB['srlctd0201']['DBPORT'] = '3301';
			}
			if ($sel_db == "BUNSAN#2") {
				$select_db = $L_DB['srlctd0202'];
				////	検証分散DB#2
				//$L_DB['srlctd0202']['NAME'] = '検証分散DB#2';
				//$L_DB['srlctd0202']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0202']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0202']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0202']['DBNAME'] = 'SRLCS02';
				//$L_DB['srlctd0202']['DBPORT'] = '3302';
			}
			if ($sel_db == "BUNSAN#3") {
				$select_db = $L_DB['srlctd0203'];
				////	検証分散DB#3
				//$L_DB['srlctd0203']['NAME'] = '検証分散DB#3';
				//$L_DB['srlctd0203']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0203']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0203']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0203']['DBNAME'] = 'SRLCS03';
				//$L_DB['srlctd0203']['DBPORT'] = '3303';
			}
			if ($sel_db == "BUNSAN#4") {
				$select_db = $L_DB['srlctd0204'];
				////	検証分散DB#4
				//$L_DB['srlctd0204']['NAME'] = '検証分散DB#4';
				//$L_DB['srlctd0204']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0204']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0204']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0204']['DBNAME'] = 'SRLCS04';
				//$L_DB['srlctd0204']['DBPORT'] = '3304';
			}
			if ($sel_db == "BUNSAN#5") {
				$select_db = $L_DB['srlctd0305'];
				////	検証分散DB#5
				//$L_DB['srlctd0305']['NAME'] = '検証分散DB#5';
				//$L_DB['srlctd0305']['DBHOST'] = '10.128.1.23';
				//$L_DB['srlctd0305']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0305']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0305']['DBNAME'] = 'SRLCS05';
				//$L_DB['srlctd0305']['DBPORT'] = '3305';
			}
			if ($sel_db == "BUNSAN#6") {
				$select_db = $L_DB['srlctd0306'];
				////	検証分散DB#6
				//$L_DB['srlctd0306']['NAME'] = '検証分散DB#6';
				//$L_DB['srlctd0306']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0306']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0306']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0306']['DBNAME'] = 'SRLCS06';
				//$L_DB['srlctd0306']['DBPORT'] = '3306';
			}
			if ($sel_db == "BUNSAN#7") {
				$select_db = $L_DB['srlctd0307'];
				////	検証分散DB#7
				//$L_DB['srlctd0307']['NAME'] = '検証分散DB#7';
				//$L_DB['srlctd0307']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0307']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0307']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0307']['DBNAME'] = 'SRLCS07';
				//$L_DB['srlctd0307']['DBPORT'] = '3307';
			}
			if ($sel_db == "BUNSAN#8") {
				$select_db = $L_DB['srlctd0308'];
				////	検証分散DB#8
				//$L_DB['srlctd0308']['NAME'] = '検証分散DB#8';
				//$L_DB['srlctd0308']['DBHOST'] = '10.128.1.22';
				//$L_DB['srlctd0308']['DBUSER'] = 'axxsc01';
				//$L_DB['srlctd0308']['DBPASSWD'] = 'sc_01axx';
				//$L_DB['srlctd0308']['DBNAME'] = 'SRLCS08';
				//$L_DB['srlctd0308']['DBPORT'] = '3308';
			}

			// add start oda 2020/07/21 スケールアウト インスタンス作成時解放
			if ($sel_db == "BUNSAN#9") {
				$select_db = $L_DB['srlctd0309'];
			}
			if ($sel_db == "BUNSAN#10") {
				$select_db = $L_DB['srlctd0310'];
			}
			if ($sel_db == "BUNSAN#11") {
				$select_db = $L_DB['srlctd0311'];
			}
			if ($sel_db == "BUNSAN#12") {
				$select_db = $L_DB['srlctd0312'];
			}
			if ($sel_db == "BUNSAN#13") {
				$select_db = $L_DB['srlctd0313'];
			}
// 			if ($sel_db == "BUNSAN#14") {
// 				$select_db = $L_DB['srlctd0314'];
// 			}
// 			if ($sel_db == "BUNSAN#15") {
// 				$select_db = $L_DB['srlctd0315'];
// 			}
			// add end oda 2020/07/21 スケールアウト インスタンス作成時解放

		}	//if (XFER_DISTRIBUTE_SERVER == "STAG") {	//検証 分散サーバーへアップする。


	}

	// update start 2018/03/08 yoshizawa AWS環境判定
	// if ($http_host == $server_name_2) {	//azetコンテンツt開発
	if ($http_host == $server_name_2 || $server_addr == $server_ip_2) {	//azetコンテンツt開発
	// update end 2018/03/08 yoshizawa AWS環境判定

		if ($sel_db == "CONTENTSX") {
			$select_db = $L_DB['srlctd03SOGO'];
			//$L_DB['srlctd03SOGO']['NAME'] = '開発コアDB総合サーバテスト用';
			//$L_DB['srlctd03SOGO']['DBNAME'] = 'CONTENTS160';
		}

		if ($sel_db == "TOGO") {
			//	azet開発環境、テスト用統合サーバ SRLEK01
			$L_DB['srlctd03ek01']['NAME'] = 'azet開発環境テスト用統合サーバ';
			$L_DB['srlctd03ek01']['DBHOST'] = '10.128.1.23';
			$L_DB['srlctd03ek01']['DBUSER'] = 'axxkc01';
			$L_DB['srlctd03ek01']['DBPASSWD'] = 'kc_01axx';
			$L_DB['srlctd03ek01']['DBNAME'] = 'SRLEK01';
			$L_DB['srlctd03ek01']['DBPORT'] = '3306';
			if($server_addr == $server_ip_2){ $L_DB['srlctd03ek01']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com'; } // add 2018/03/08 yoshizawa AWS環境判定
			$select_db = $L_DB['srlctd03ek01'];
		}

		if ($sel_db == "BUNSAN#7") {
			$select_db = $L_DB['srlctd03'];
			//$L_DB['srlctd03']['NAME'] = '開発コアDB';
			//$L_DB['srlctd03']['DBHOST'] = '10.128.1.23';
			//$L_DB['srlctd03']['DBUSER'] = 'axxkc01';
			//$L_DB['srlctd03']['DBPASSWD'] = 'kc_01axx';
			//$L_DB['srlctd03']['DBNAME'] = 'SRLCK07';
			//$L_DB['srlctd03']['DBPORT'] = '3306';
		}

	}

	return $select_db;
}


/**
 * 分散データベースへの反映(登録更新、削除)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function xfer_distribute_db($honbu_announce_num, $edit_mode) {
	//$edit_mode: "write" "delete"

	$html = "";

	//指定 $honbu_announce_num データを、分散データベースへ反映させる
	$select_db = set_select_db("CONTENTSX");

	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}
/*
	//基本情報の取得
	$sql = "SELECT * FROM honbu_announce_data WHERE display='1' AND mk_flg='0' AND honbu_announce_num='".$honbu_announce_num."';";
	$result = $connect_db->exec_query($sql);
	if ($result->num_rows == 0) {
		//	閲覧DB切断
		$connect_db->close();

		$html = "<br>データがありません。<br>";
		return $html;
	}

	$list = $connect_db->fetch_assoc($result);
	$start_date = $list['start_date'];
	$end_date = $list['end_date'];
	$subject = $list['subject'];
	$message = $list['message'];

*/
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];

	// upd start hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
	// // add start hasegawa 2017/09/15 課題要望No631
	// $message2 = $_POST['message2'];
	// if (preg_match("/【＜】|【＞】/", $message2)) {
	// 	$message = $message2;
	// }
	// // add end hasegawa 2017/09/15
	$REPLACER = array();
	if (is_array($_POST)) {
		foreach($_POST as $key => $val) {
			if (preg_match('/^【StyleReplacer[0-9]+】$/', $key)) {
				$REPLACER[$key] = $val;
			}
		}
	}
	if($message2) {
		$message = tinymce_style_decode($message2, $REPLACER);
	}
	// upd end hasegawa 2018/07/19


	if($start_date==""){ $start_date="NULL"; }
	if($end_date==""){ $end_date="NULL"; }

	//校舎ID情報の取得
	$sql2 = "SELECT * FROM honbu_announce_school WHERE display='1' AND mk_flg='0' AND honbu_announce_num='".$honbu_announce_num."';";
	$result2 = $connect_db->exec_query($sql2);
	if ($result2->num_rows == 0) {
		//	閲覧DB切断
		$connect_db->close();

		$html = "<br>データがありません。<br>";
		return $html;
	}

	while($list2 = $connect_db->fetch_assoc($result2)) {
		$school_id = $list2['school_id'];
		$db_id = $list2['db_id'];

		$db_id_num = "";
		if (strlen($db_id) > 3) {
			$db_id_num = intval(substr($db_id, 2));
		}
		$sel_db2 = "BUNSAN#".$db_id_num;
		$select_db2 = set_select_db($sel_db2);

		$connect_db2 = new connect_db();
		$connect_db2->set_db($select_db2);
		$ERROR = $connect_db2->set_connect_db();
		if ($ERROR) {
			return $ERROR;
		}

		//$edit_mode が "write" の場合
		//校舎IDごとに、既にデータが有れば update、無ければ insert
		//$edit_mode が "delete" の場合
		//校舎IDごとに、既にデータが有れば mk_flg をセットする

		$sql3 = "SELECT * FROM announcement WHERE school_id='".$school_id."' AND address_id='".$school_id."' ".
				" AND display='1' AND state='0' AND honbu_announce_num='".$honbu_announce_num."';";
		$result3 = $connect_db2->exec_query($sql3);
		if ($result3->num_rows == 0) {
			//データが無い場合
			if ($edit_mode == "write") {
				//新規にデータを insert する

				$INSERT_DATA = array();
				$INSERT_DATA['school_id'] = $school_id;
				$INSERT_DATA['user_id'] = $_SESSION['myid']['id'];
				$INSERT_DATA['user_level'] = 1;	//本部から
				$INSERT_DATA['address_id'] = $school_id;	//指定校舎へアナウンス
				$INSERT_DATA['address_type'] = 1;	//全員へ
				$INSERT_DATA['address_level'] = 6;	//生徒へ
				$INSERT_DATA['start_date'] = $start_date;
				$INSERT_DATA['end_date'] = $end_date;
				$INSERT_DATA['honbu_announce_num'] = $honbu_announce_num;
				$INSERT_DATA['subject'] = $subject;
				$INSERT_DATA['message'] = $message;
				$INSERT_DATA['regist_date'] = "now()";
				$INSERT_DATA['update_date'] = "now()";
				$INSERT_DATA['display'] = 1;
				$INSERT_DATA['state'] = 0;

				$ERROR = $connect_db2->insert("announcement", $INSERT_DATA);	//データ登録処理
				if ($ERROR) {
					//	閲覧DB切断
					$connect_db2->close();

					$html .= "<br>".$ERROR[0]."<br>";
					return $html;
				}

			}

		} else {
			//データがある場合
			if ($edit_mode == "write") {
				//登録済みデータを update する

				$list3 = $connect_db2->fetch_assoc($result3);
				$announcement_num = $list3['announcement_num'];
				$INSERT_DATA = array();
				$INSERT_DATA['user_id'] = $_SESSION['myid']['id'];
				$INSERT_DATA['start_date'] = $start_date;
				$INSERT_DATA['end_date'] = $end_date;
				$INSERT_DATA['subject'] = $subject;
				$INSERT_DATA['message'] = $message;
				$INSERT_DATA['update_date'] = "now()";
				$where  = " WHERE announcement_num = '".$announcement_num."' AND honbu_announce_num = '".$honbu_announce_num."'";
				$where .= " LIMIT 1;";

				$ERROR = $connect_db2->update("announcement", $INSERT_DATA, $where);
				if ($ERROR) {
					//	閲覧DB切断
					$connect_db2->close();

					$html .= "<br>".$ERROR[0]."<br>";
					return $html;
				}

				//add start 開示期間をNULLにする場合 2016/01/28
				$set_cmd = "";
				if ($start_date == "0000-00-00") { $set_cmd = " start_date=NULL "; }
				if ($end_date == "0000-00-00") {
					if (strlen($set_cmd) > 0) { $set_cmd .= ", "; }
					$set_cmd .= " end_date=NULL ";
				}
				$sql4 = "UPDATE announcement set ".$set_cmd ." WHERE announcement_num='".$announcement_num."';";
				$result4 = $connect_db2->exec_query($sql4);
				//add end 開示期間をNULLにする場合 2016/01/28

			}

			if ($edit_mode == "delete") {
				//登録済みデータを 論理削除して 非表示 にする

				$list3 = $connect_db2->fetch_assoc($result3);
				$announcement_num = $list3['announcement_num'];
				$INSERT_DATA = array();
				$INSERT_DATA['user_id'] = $_SESSION['myid']['id'];
				$INSERT_DATA['update_date'] = "now()";
				$INSERT_DATA['display'] = 2;
				$INSERT_DATA['state'] = 1;
				$where  = " WHERE announcement_num = '".$announcement_num."' AND honbu_announce_num = '".$honbu_announce_num."'";
				$where .= " LIMIT 1;";

				$ERROR = $connect_db2->update("announcement", $INSERT_DATA, $where);
				if ($ERROR) {
					//	閲覧DB切断
					$connect_db2->close();

					$html .= "<br>".$ERROR[0]."<br>";
					return $html;
				}

			}

		}
		//	閲覧DB切断
		$connect_db2->close();

	}

	//	閲覧DB切断
	$connect_db->close();

	//$html = "対象の分散 xfer_distribute_db:".$honbu_announce_num.", mode:".$edit_mode."<br>";

	return $html;
}


/**
 * CSV(tsv)アップロード処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function csv_upload() {
	$html = "";

	$DATAS =array();

	if (is_uploaded_file($_FILES["csvfile"]["tmp_name"])) {
		$file_tmp_name = $_FILES["csvfile"]["tmp_name"];
		$file_name = $_FILES["csvfile"]["name"];
		//拡張子を判定
		if (pathinfo($file_name, PATHINFO_EXTENSION) != 'csv') {
			$html .= "<br>拡張子が csv ファイルのみに対応しています。<br>";

		} else {

			//csvfile処理 start
			$cnt = 0;
			$modex = 0;
			$LIST = file($file_tmp_name);
			if ($LIST) {
				$i = 1;
				foreach ($LIST AS $VAL) {

					if (count(explode("\t",$VAL)) == 1 && $i == 1) { $modex = 1; }

					if ($modex == 1) {
						$VAL = trim($VAL, $character_mask = " \n\r\0\x0B");
						if (!isset($DATAS[1]['school_id'])) {
							$DATAS[1]['school_id'] = $VAL;
						} else {
							$DATAS[1]['school_id'] .= ",".$VAL;
						}

					} else {	//if ($modex == 1) {

						unset($LINE);
						$VAL = trim($VAL, $character_mask = " \n\r\0\x0B");
						if (!$VAL || !ereg("\t",$VAL)) { continue; }

						$code = judgeCharacterCode ( $VAL );
						if ( $code != 'UTF-8' ) {
							//$VAL = mb_convert_kana($VAL,"R","sjis-win");
							$VAL = replace_encode_sjis($VAL);
							$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
						}

						list(
							$LINE['subject'],$LINE['message'],$LINE['start_date'],$LINE['end_date'],$LINE['school_id']
						) = explode("\t",$VAL);
						if ($LINE) {
							if (count(explode("\t",$VAL)) != 5) {
								$html .= "<br>".$i.": 構造が正しくありません。<br>";
								break;
							}

							//入力データチェック
							//日付フォーマットチェック
							//echo "*".$LINE['start_date']."<br>";
							$tmp_start_date = $LINE['start_date'];
							if ($i > 1 && strlen($tmp_start_date) > 0) {
								$tmp_start_date = str_replace("-", "/", $tmp_start_date);
								if (!checkDateFormat($tmp_start_date)) {
									$html .= "<br>".$i.": 正しい開始日付が入力されていません。<br>";
									break;
								}
							}
							$LINE['start_date'] = $tmp_start_date;

							$tmp_end_date = $LINE['end_date'];
							if ($i > 1 && strlen($tmp_end_date) > 0) {
								$tmp_end_date = str_replace("-", "/", $tmp_end_date);
								if (!checkDateFormat($tmp_end_date)) {
									$html .= "<br>".$i.": 正しい終了日付が入力されていません。<br>";
									break;
								}
							}

							$LINE['end_date'] = $tmp_end_date;

							//school_id がカラをチェック
							if ($i > 1 && strlen($LINE['school_id']) == 0) {
								$html .= "<br>".$i.": school_idが指定されていません。<br>";
								break;
							}

							if ($i == 1 && $LINE['school_id']=="school_id") {
							//echo "skip:".$i;
							} else {

							if(strlen($LINE['subject']) > 0 && strlen($LINE['message']) > 0) {
								$cnt++;
							}
							if ($cnt > 1) {
								//$html .= "<br>１度に複数アナウンスは読み込めません。<br>";
								$html .= "<br>読み込めません。<br>";
								break;
							} else {

								$tmp_subject = $LINE['subject'];
								$tmp_message = $LINE['message'];
								$tmp_subject = ereg_replace("\"","&quot;",$tmp_subject);	//ダブルクォーテーションが消えてしまうので、消える前に変換する
								$tmp_message = ereg_replace("\"","&quot;",$tmp_message);	//ダブルクォーテーションが消えてしまうので、消える前に変換する

								if (isset($DATAS[$cnt]['subject'])) {
									//	$DATAS[$cnt][$key] = $valx;
									//echo " B<br>";
									$DATAS[$cnt]['school_id'] .= ",".$LINE['school_id'];

								} else {

									$DATAS[$cnt]['subject'] = $tmp_subject;
									$DATAS[$cnt]['message'] = $tmp_message;
									$DATAS[$cnt]['start_date'] = $LINE['start_date'];
									$DATAS[$cnt]['end_date'] = $LINE['end_date'];
									$DATAS[$cnt]['school_id'] = $LINE['school_id'];
									}

								}

							}	//if ($cnt > 1) {

						}	//if ($LINE) {

					}	//if ($modex == 1) {

					$i++;

				}	//foreach ($LIST AS $VAL) {
			}

			//csvfile処理 end

		}

	} else {
		$html .= "<br>アップロードされたファイルがありません。<br>";
	}

	$html .= read_datas($DATAS);

	if (strlen($html) > 0) {
		$html .= "<br>";
//		$html .= "<input type=\"button\" name=\"cancel\" value=\"戻る\" onclick=\"history.back();\">";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">";
		$html .= "<input type=\"submit\" name=\"cancel\" value=\"戻る\">";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"create\">";
		$html .= "</form>";

	//} else {
	}

	return $html;
}


/**
 * CSV(tsv)アップロード/データ読み込み
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function read_datas($DATAS) {
	$html = "";

	$select_db = set_select_db("TOGO");
	$connect_db = new connect_db();
	$connect_db->set_db($select_db);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$connect_db->close();
		return $ERROR;
	}

	//データ取得
	unset($_SESSION['announce_batch']);
	if (strlen($DATAS[1]['start_date']) > 0) {
		$_SESSION['announce_batch']['start_date'] = str_replace("-", "/", $DATAS[1]['start_date']);
	} else {
		$_SESSION['announce_batch']['start_date'] = "";
	}
	if (strlen($DATAS[1]['end_date']) > 0) {
		$_SESSION['announce_batch']['end_date'] = str_replace("-", "/", $DATAS[1]['end_date']);
	} else {
		$_SESSION['announce_batch']['end_date'] = "";
	}
	$_SESSION['announce_batch']['subject'] = $DATAS[1]['subject'];
	$_SESSION['announce_batch']['message'] = $DATAS[1]['message'];

	//$honbu_announce_num = $list['honbu_announce_num'];

	$tmp_school_id_ary = explode(",", $DATAS[1]['school_id']);
//echo "*".$DATAS[1]['school_id']."<br>";

	//校舎ID情報の取得
	$entity_cnt = 0;
	foreach($tmp_school_id_ary AS $keyx => $valx) {
//echo "*".$keyx." -> ".$valx."<br>";
		if (strlen(trim($valx)) > 0) {

			$dbid = "";
			$sql = "SELECT db_id FROM school_dbinfo_view WHERE school_id='".$valx."';";
			$result = $connect_db->exec_query($sql);
			if ($result->num_rows > 0) {
				$list=$connect_db->fetch_assoc($result);
				$dbid = $list['db_id'];
			} else {
				//該当データなし
				//	閲覧DB切断
				$connect_db->close();
				$html .= "<br>該当する 校舎ID が見つかりません。(校舎ID:".$valx.")<br>";
				return $html;
			}

			$school_name = "";
			$sql = "SELECT school_name FROM school WHERE school_id='".$valx."';";
			$result = $connect_db->exec_query($sql);
			if ($result->num_rows > 0) {
				$list=$connect_db->fetch_assoc($result);
				$school_name = $list['school_name'];
			} else {
				//該当データなし
				//	閲覧DB切断
				$connect_db->close();
				$html .= "<br>該当する 校舎ID が見つかりません。(校舎ID:".$valx.".)<br>";
				return $html;
			}

			$entity_cnt++;
			$_SESSION['announce_batch']['school_id_'.$entity_cnt] = $valx;
			$_SESSION['announce_batch']['db_id_'.$entity_cnt] = $dbid;
			$_SESSION['announce_batch']['school_name_'.$entity_cnt] = $school_name;

		}

	}

	if ($entity_cnt < 5) { $entity_cnt = 5; }
	$_SESSION['announce_batch']['datacnt'] = $entity_cnt;

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * 確認表示画面（別window）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function show_test() {
	$html = "";

	$html = "<br><input type=\"button\" name=\"close\" value=\"閉じる\" onlick=\"window.close();\">";

	return $html;
}


// add start hasegawa 2017/09/15  課題要望No631
/**
 * tinymceのタグ変換用文字列の変換
 *
 * AC:[T]管理者 UC1:[S04]Core管理機能
 *
 * @author azet
 * @param string $message	入力メッセージ文字列
 * @return string 		変換後メッセージ文字列
 */
function tinymce_tag_decode($message) {

	if (preg_match("/【＜】|【＞】/", $message)) {
		// <>に直す
		$message = str_replace("【＜】","&lt;",$message);
		$message = str_replace("【＞】","&gt;",$message);
	}

	return $message;
}
// add end hasegawa 2017/09/15

// add function hasegawa 2018/07/19 Edge styleタグPOSTの不具合対応
/**
 * tinymceのスタイル変換用文字列の変換
 *
 * AC:[T]先生 UC1:[S04]アナウンス管理.
 *
 * @author azet
 * @param string $message	入力メッセージ文字列
 * @param array $REPLACER	変換用配列
 * @return string 		変換後メッセージ文字列
 */
function tinymce_style_decode($message, $REPLACER) {

	if (!(count($REPLACER) > 0)) { return $message; }

	foreach($REPLACER as $key => $val) {
		$pattern= '/'.$key.'/';
		$message = preg_replace($pattern, $val, $message);
	}

	return $message;
}

/*
function start() {
function set_scriptcss() {
function history_list() {
function create_buttons() {
function create() {
function create_html() {
function confirm() {
function confirm_html() {
function check_school_id($school_id) {
function regist() {
function edit() {
function edit_html() {
function checkDateFormat($hizuke) {
function update_exec() {
function delete_confirm() {
function delete_confirm_html() {
function delete_exec() {
function check() {
function set_select_db($sel_db) {
function xfer_distribute_db($last_id) {
function csv_upload() {
function read_datas($DATAS) {
function show_test() {
*/


?>