<?php

//================================================================= FUNCTION_S ============================

// ============================================================
// ========= key=value 形式の文字列を 分割する Start =========
// ============================================================

// テストデータ

$str = "user_key=ab404d6255ee822f7636be88c1752be4";

function Func_Get_Key_Value($val)
{
  $tmp_arr = explode("=", $val);
  return $tmp_arr;
}


$get_arr = Func_Get_Key_Value($str);

// ======== function 出力


// ============================================================
// ========= key=value 形式の文字列を 分割する END ===========
// ============================================================


// ====================================================================
// ========= 文字列の中の ダブルクォーテーション　を削除する =========
// ====================================================================

// 対象データ 文字列に　カンマが入っている ""
$test_01_data = '"user_key';

function Cut_Quoto($val)
{
  $target = array("\"", " ");
  // $cut_val = str_replace("\"", "", $val);
  $cut_val = str_replace($target, "", $val);
  return $cut_val;
}

$test_cut_01 = Cut_Quoto($test_01_data);


// ====================================================================
// ========================= function END =============================
// ====================================================================

function get_contents(String $target_url)
{
  file_get_contents($target_url);
  var_dump($http_response_header);
}

/*

関数 日付　取得　関数　get_to_date()

使いかた ===================>

// オブジェクト
$get_now_date = new get_now_date();
$get_now_arr = $get_now_date->get_to_date();


// === 日付ボックス　空用　
$go_now_sql = $get_now_arr['year'] . "-" . $get_now_arr['month'] . "-" . $get_now_arr['day'];

<====================

*/

// =========================== 日付取得　クラス
class get_now_date
{
  function get_to_date()
  {

    //====================== 現在時刻の取得
    $n_year = "";
    $n_month = "";
    $n_day = "";

    $n_hour = "";
    $n_minute = "";
    $n_second = "";

    $now = new DateTime();

    $now_tm = $now->format('Y-m-d H:i:s');

    //======== 日付の - 空白　: を取る
    $tt_0 = str_replace("-", "", $now_tm);
    $ttt_0 = str_replace(":", "", $tt_0);
    $now_tmp = str_replace(" ", "", $ttt_0);

    // ======== 切り出し 年　月　日
    $n_year = mb_substr($now_tmp, 0, 4);
    $n_month = mb_substr($now_tmp, 4, 2);
    $n_day = mb_substr($now_tmp, 6, 2);

    // ======= 時間切り出し
    $n_hour = mb_substr($now_tmp, 8, 2);
    $n_minute = mb_substr($now_tmp, 10, 2);
    $n_second = mb_substr($now_tmp, 12, 2);

    return array(

      'year' => $n_year,
      'month' => $n_month,
      'day' => $n_day,
      'hour' => $n_hour,
      'minute' => $n_minute,
      'second' => $n_second

    );
  } //======= END Function
}

/**
 *    arr_Log_Write
 *    $arr_target の配列が　空の要素の場合は $message の　値を入れる。  
 */
//
function Arr_Log_Write($arr_target, $arr_in, $message)
{
  // === ループ　回数
  $loop_num = count($arr_target);
  // === 配列の値が　空　の場合の 代入文字列
  $message = "値が空です";

  for ($i = 0; $i < $loop_num; $i++) {

    if (isset($arr_target[$i])) {

      if (!empty($arr_target[$i])) {
        $arr_in[$i] = $arr_target[$i];
      } else {
        $arr_in[$i] = $message;
      }
    }
  }

  return $arr_in;
}


/*
 @ arr_target_text の値を $arr_change_text へ置き換える
*/
function Log_Key_Name_Change($arr_target_text)
{
  $arr_new = [];
  $idx = 0;

  $arr_change_text = [
    "登録更新方法",
    "情報取得経路",
    "会社名",
    "部署",
    "役職",
    "苗字",
    "名前",
    "苗字（かな）",
    "名前（かな）",
    "郵便番号",
    "住所",
    "電話番号",
    "Fax番号",
    "メールアドレス"
  ];

  foreach ($arr_target_text as $arr_key) {

    $arr_new[$idx] = str_replace($arr_key[$idx], $arr_change_text[$idx], $arr_key[$idx]);

    $idx++;
  }

  return $arr_new;
}

/**
 *  @ $move_path のディレクトリ　へ　ファイルを　移動する 
 */
function Move_FILE($move_path)
{

  if ($handle = opendir("./")) {
    while (false !== ($entry = readdir($handle))) {
      // ファイル名が　「.txt」 だったら処理を実行
      if (strpos($entry, ".txt") !== false) {
        rename($entry, $move_path . $entry);
      } else {
        // 処理なし
      }
    }
    closedir($handle);
  }
}

/**
 * 
 *  LOG 用　出力 class
 *  * 
 */
class Log_output
{

  private $data;
  private $url;

  // ========= コンストラクター
  public function __construct($data, $url)
  {
    $this->data = $data;
    $this->url = $url;
  }

  function CREATE_LOG()
  {

    //=========================== ログファイルへ 書き込み　
    //　customer[lead_company_name]:::○○株式会社
    //  customer[department]:::営業部
    // 作成：夏目 智徹
    // ==========================  上記の書式で書き込み ==========================

    $output_data = [];
    $idx = 0;
    foreach ($this->data as $key => $value) {

      if ($value != "") {
        $output_data[$idx] = $key . ":::" . $value;
      } else {
        $output_data[$idx] = $key . ":::" .  "*** error ***";
      }

      $idx++;
    }
    // ==========================  上記の書式で書き込み ==========================

    if (strpos($this->url, "registration.json") !== false) {
      $this->url = "新規登録エラー（registration.json）";
    } else if (strpos($this->url, "upsert.json") !== false) {
      $this->url = "新規登録・更新エラー（upsert.json）";
    } else {
      $this->url = "削除処理エラー（delete.json）";
    }

    return array(
      'get_url' => $this->url,
      'log_0' => $output_data[0],
      'log_1' => $output_data[1],
      'log_2' => $output_data[2],
      'log_3' => $output_data[3],
      'log_4' => $output_data[4],
      'log_5' => $output_data[5],
      'log_6' => $output_data[6],
      'log_7' => $output_data[7],
      'log_8' => $output_data[8],
      'log_9' => $output_data[9],
      'log_10' => $output_data[10],
      'log_11' => $output_data[11],
      'log_12' => $output_data[12],
      'log_13' => $output_data[13],
      'log_14' => $output_data[14],
      'log_15' => $output_data[15],
      'log_16' => $output_data[16],
      'log_17' => $output_data[17],
      'log_18' => $output_data[18],
      'log_19' => $output_data[19],
      'log_20' => $output_data[20],
      'log_21' => $output_data[21],
      'log_22' => $output_data[22]
    );
  }
}
