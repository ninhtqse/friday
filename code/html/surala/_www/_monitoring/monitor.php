<?php
/**
 * ベンチャー・リンク　すらら
 *
 * データ　モニトリング　システム
 * table _monitoring
 *      id
 *      myname
 *      when (auto)
 *      url
 *      get
 *      post
 *      session_id
 *      session
 *
 * 履歴
 * 2014-04-10　初期設定
 * 2016-11-02  session_idを追加
 *
 * @author azet
 */

//very important function
require_once("json.php");

define('FORMAT_INDENT', "_");
define('FORMAT_LINE', "\n");

/**
 * db connection
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @return object mysql_link
 */
function monitor_connect() {

	global $L_DB;

    // -- DB 接続 --
    //we connect only to the 07 server
    $server = "srlctd03";
    //we then try to load relatively to us
    $folder = preg_replace("/_monitoring/i", "", dirname(__FILE__));
    @include("../../_www/db_list.php");

    if(!$L_DB) {
        _hidden_print("Server list file could not be loaded from {$folder}db_list.php!");
        return false;
        //we really give up now!
    }

    //print_r($L_DB[$server]);
    //print_r($_SERVER['SERVER_ADDR']."\n");
    $link = new mysqli($L_DB[$server]['DBHOST'], $L_DB[$server]['DBUSER'], $L_DB[$server]['DBPASSWD'], $L_DB[$server]['DBNAME'], $L_DB[$server]['DBPORT']);

    //print_r($link);

	// 文字コード設定
	$link->set_charset("utf8");				//ハイフンなしのutf8と指定しないと、正常に設定されない

    //no error?
    if($link->connect_errno != 0) {
        _hidden_print("MYSQL ERROR:".  $link->error);
        return false;
    }

    return $link;
}

/**
 * adding data into the monitoring table
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $name_ (optionnal) an identifier to retreive the correct line in the table later
 * @param string $memo_ (optionnal) a memo that can hold a LOT of data like full length queries
 * @return void
 */
function monitor($name_="", $memo_="")
{

    $link = monitor_connect();
    if(!$link) {
    	_hidden_print("Impossible to log $name_ due to monitor_connect() error!");
       return false;
    }

    //TODO, clean data from other days
    $link->query("DELETE FROM `_monitoring` WHERE date(`when`)<CURRENT_DATE");

    $insert = "INSERT INTO _monitoring SET ";
    $insert .= _append_query_set($link, 'myname', $name_, true);
    $insert .= _append_query_set($link, 'mymemo', $memo_);
    $insert .= _append_query_set($link, 'ip', $_SERVER['REMOTE_ADDR']);
    $insert .= _append_query_set($link, 'server_ip', $_SERVER['SERVER_ADDR']);
    $insert .= _append_query_set($link, 'url', $_SERVER['REQUEST_URI']);
    $insert .= _append_query_set($link, 'useragent', $_SERVER['HTTP_USER_AGENT']);
    $insert .= _append_query_set($link, 'get', serialize($_GET));
    $insert .= _append_query_set($link, 'post', serialize($_POST));
	$insert .= _append_query_set($link, 'session_id', session_id());
    $insert .= _append_query_set($link, 'session', serialize($_SESSION));
    $q1 = $link->query($insert);

    //we also keep track of user agents
    $q_agent = "REPLACE INTO _monitoring_agents SET ";
    $q_agent .= _append_query_set($link, 'ip', $_SERVER['REMOTE_ADDR'], true);
    $q_agent .= _append_query_set($link, 'server_ip', $_SERVER['SERVER_ADDR']);
    $q_agent .= _append_query_set($link, 'useragent', $_SERVER['HTTP_USER_AGENT']);
    $q2 = $link->query($q_agent);
    if(!$q2) {
        _hidden_print("Error while inserting user agent: " . $cdb->error);
        _hidden_print($q_agent);
    }

    $link->close();

    return $q1;
}


/**
 * モニトリング表示
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string AJAXで (default)
 * @param string $filters_ (format arg1[:val1],[arg2[:val2],]... )
 * @return mixed JSON string or Array
 */
function monitor_view($cdb, $method_ = 'ajax', $filters_="")
{
	$clause = "";
    $filter = "";
    $limit = "";

    //dealing with the filters
    if($filters_) {
        foreach(explode(",", $filters_) as $f) {
            //myip filter
            if($f=="myip") {
                $clause .= "and ip='".$_SERVER['REMOTE_ADDR']."'";
            }
            //last filter: return only the last entry
            elseif($f=="last") {
                $limit = " LIMIT 1";
            }
            //dynamic filter values
            elseif($f) {
                $filter = $f;    //maybe an append would be better
            }
        }
    }
//    print "filter:".$filter;

    //for now, we just display the requests of the dcurrent ay
    $rs = $cdb->query("SELECT * FROM _monitoring WHERE DATE(`when`)=CURRENT_DATE() $clause ORDER BY `when` DESC $limit");
    $answer = array();
    while($row = $cdb->fetch_assoc($rs)) {

        $session = unserialize($row['session']);

        //applying filters at display time only
        if($filter) {
            // | is the separator
            $fs = explode("|", $filter);
            if(is_array($fs)) {
                foreach($fs as $f) {
                    $session = _filter($session, $f);
                }
            } else {
                $session = _filter($filtered, $filter);
            }
        }

        $answer[] = array(
            "ip" => $row['ip']
            ,"url" => $row['url']
            ,"myname" => $row['myname']
            ,"mymemo" => $row['mymemo']
            ,"when" => $row['when']
            ,"useragent" => $row['useragent']
            ,"get" => format_data(unserialize($row['get']))
            ,'post' => format_data(unserialize($row['post']))
            ,'session' => format_data($session)
        );
    }

    // output format condition
    if($method_=='ajax') {
        return json_encode($answer);
    }
    //TODO other format of outputs if needed

    //defaut output
    return print_r($answer, 1);
}


/**
 * _monitoringテーブルから掃除
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @return string "OK" 又は "ERROR"
 */
function monitor_clear($cdb) {

	$ok = $cdb->query("DELETE FROM _monitoring WHERE ip='".$_SERVER['REMOTE_ADDR']."'");

    return $ok?"OK":"ERROR";
}


/**
 * statsデータ
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $method_
 * @param string $filters_
 * @return mixed JSON string or Array
 */
function monitor_stats($cdb, $method_= 'ajax', $filters_="") {

	$answer = array();

    $rs = $cdb->query("SELECT useragent, COUNT(useragent) as users from `_monitoring_agents` GROUP BY useragent ORDER BY users DESC, useragent ASC");
    $lines_count = $rs->num_rows;

    while($row = $rs->fetch_assoc()) {
        //adding some pre-calculated values to the row data
        $row['percents'] = 100 * $row['users'] / $lines_count;

        $answer[] = $row;
    }

    // output format condition
    if($method_=='ajax') {
        return json_encode($answer);
    }
    //TODO other format of outputs if needed

    //defaut output
    return print_r($answer, 1);
}


///////////////////////////////////////////////////////////////////////////////
//                  この下を見なくてもいいと思います＾＾
///////////////////////////////////////////////////////////////////////////////


/**
 * simple print function to hide in comments prints
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param Object $p_
 */
function _hidden_print($p_) {
    print "<!--";
    print_r($p_);
    print "-->\n";
}


/**
 * preparing a string to set mysql key/value pairs
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $k_ mysql column name
 * @param string $v_ data to insert
 * @param boolean $first_ is it the first inserted field?
 * @return string to append to the query
 */
function _append_query_set($link, $k_, $v_, $first_=false) {

	//second argument or plus?
    if(!$first_) {
        $append = ", ";
    }
    else {
        $append = "";
    }

    $append .= "`$k_`=\"".mysqli_real_escape_string($link, $v_)."\"";

    return $append;
}


/**
 * Filter function that removes entries from the answer
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param array $ans_
 * @param string $f_ (if starts by - (minus), it excludes entries)
 */
function _filter($ans_, $f_) {

    $final = Array();
    if(is_array($ans_)) {
        foreach($ans_ as $k => $v) {

            if(strpos($f_, '-')===0) {
                //we truncate the first - sign
                $filter = substr($f_, 1);
                //exclusive filter
                if(preg_match('/'.$filter.'/i', $k)) {
                    $final[$k] = "[filtered]";
                    continue;
                } else {
                    if(is_array($v)) {
                        $final[$k] = _filter($v, $f_);
                    } else {
                        $final[$k] = $v;
                    }
                }
            }
            else {
                //inclusive filter
                if(preg_match('/'.$f_.'/i', $k)) {
                    //simple value?
                    $final[$k] = $v;
                }
                elseif(is_array($v)) {
                    //array? we need to look deeper
                    $parsed = _filter($v, $f_);
                    if($parsed) {
                        //found something
                        $final[$k] = $parsed;
                    }
                }
            }

        }   // end foreach
    }
    return $final;
}


//////////////////////// AJAX related ////////////////////////

/**
 * JSONデータ準備
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $k_
 * @param string $v_
 * @return string
 */
function _make_json_entry($k_, $v_)
{
    $v = $v_;   //apply filters hehe if needed
    return "\"$k_\":$v";
}

/**
 * simple recursive loop o display data
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @param mixed $o_
 * @param integer $loop_    counts the indentations
 * @return string
 */
function format_data($o_, $loop_ = 0) {
    $d = "";

    if (is_object($o_) || is_array($o_)) {
        foreach ($o_ as $k => $v) {
            if (is_object($v) || is_array($v)) {
                $d.= str_repeat(FORMAT_INDENT, $loop_) . "$k = " . FORMAT_LINE;
                $d .= format_data($v, $loop_ + 1);
            } else {
                $d.= str_repeat(FORMAT_INDENT, $loop_) . "$k = $v" . FORMAT_LINE;
            }
        }
    } else {
        $d .= str_repeat(FORMAT_INDENT, $loop_) . $o_ . FORMAT_LINE;
    }

    return $d;
}


////////////////////////////// BENCHMARK RELATED ////////////////////////////


/**
 * BenchMarkクラス
 *
 * メソッド一覧
 * 1. microtime_float()
 * 2. start()
 * 3. stop()
 *
 * @author Azet
 */
class Benchmark {

    var $start_time;

	/**
	 * 時間フォーマット
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author Azet
	 * @return float
	 */
		//update start kimura 2017/12/05 AWS移設 静的メソッドの明示
    static function microtime_float() {
    //function microtime_float() {
		//update end kimura 2017/12/05
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

	/**
	 * ベンチマークスタート
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author Azet
	 */
    function start() {
        $this->start_time = Benchmark::microtime_float();
    }

	/**
	 * ベンチマークストップ
	 *
	 * AC:[C]共通 UC1:[C99]その他.
	 *
	 * @author Azet
	 * @return float スタートからストップまでの時間
	 */
    function stop() {
        return Benchmark::microtime_float() - $this->start_time;
    }

}
