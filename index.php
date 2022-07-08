<?php

// エラーを出力する
ini_set('display_errors', "On");

//タイムゾーンを日本の時間に設定
date_default_timezone_set('Asia/Tokyo');

// functions.php 読み込み
require(dirname(__FILE__) . '/functions.php');

// === ログファイル TEMP ディレクトリ作成
$directory_path  = "./Log_Tmp";
if (file_exists($directory_path)) {
    // ディレクトリ作成済み
} else {
    // ディレクトリ作成
    mkdir($directory_path, 0777);
}

//================ 現在時刻取得 ================
// 年 $year 
$now = date('Y:m:d:H:i:s'); //　現在時刻取得
$now_arr = explode(":", $now); // 配列へ格納

$now_year = $now_arr[0];   // 年
$now_month = $now_arr[1];  // 月
$now_day = $now_arr[2];    // 日
$now_hour = $now_arr[3];   // 時間
$now_minutes = $now_arr[4]; // 分
$now_seconds = $now_arr[5]; // 秒

//======================= POST 受信　DNPさん ========================

$str = "";

if (isset($_POST['REQUEST_CMD'])) {

    if (!empty($_POST['REQUEST_CMD'])) {

        $str = $_POST['REQUEST_CMD'];

        //======================== POSTデータ　復元処理 ========================
        // ↓↓↓　暗号化された文字列
        // $str = "4rHxdrPTEa8i6wHVq+BES4mVbx7dssFVVfAHmou7J0JStpDXR1Ig1Y/1ABA+vdjzTzZDaMrYYxuNrISI1Q6QLXhn+YArsnSoaOjPFepRMRKCyhUrQmzL6=";
        $decMsg = base64_decode($str);
        $decrypted = openssl_decrypt($decMsg, "aes-256-cbc", "EH379K14F90JR098", 1, "B44W3U285QF05R80");

        $arr_01 = explode("-d", $decrypted);

        // ========= 先頭の要素　、　最後の要素を取得 Start =========

        $farst = array_shift($arr_01); // 先頭 取得  curl -X POST  コマンド部分
        $farst_02 = array_shift($arr_01); // user_key
        $farst_03 = array_shift($arr_01); // user_secret
        $farst_04 = array_shift($arr_01); // company_key
        $farst_05 = array_shift($arr_01); // company_secret

        //===  function Func_Get_Key_Value() で分割
        $farst_02_arr = Func_Get_Key_Value($farst_02);
        $farst_03_arr = Func_Get_Key_Value($farst_03);
        $farst_04_arr = Func_Get_Key_Value($farst_04);
        $farst_05_arr = Func_Get_Key_Value($farst_05);

        $farst_02_str_01_tmp = $farst_02_arr[0]; // user_key
        $farst_02_str_02_tmp = $farst_02_arr[1];

        $farst_03_str_01_tmp = $farst_03_arr[0]; // user_secret
        $farst_03_str_02_tmp = $farst_03_arr[1];

        $farst_04_str_01_tmp = $farst_04_arr[0]; // company_key
        $farst_04_str_02_tmp = $farst_04_arr[1];

        $farst_05_str_01_tmp = $farst_05_arr[0]; // company_secret
        $farst_05_str_02_tmp = $farst_05_arr[1];

        //======================== ダブルクォーテーション 削除
        //=== user_key の key 取得
        $Get_user_key = Cut_Quoto($farst_02_str_01_tmp);
        //=== user_key の値取得
        $Get_user_key_val = Cut_Quoto($farst_02_str_02_tmp);


        //=== user_secret の key 取得
        $Get_user_secret = Cut_Quoto($farst_03_str_01_tmp);
        //=== user_secret の値取得
        $Get_user_secret_val = Cut_Quoto($farst_03_str_02_tmp);


        //=== company_key の key 取得
        $Get_company_key = Cut_Quoto($farst_04_str_01_tmp);
        //=== company_key の値取得
        $Get_company_key_val = Cut_Quoto($farst_04_str_02_tmp);


        $Get_company_secret = Cut_Quoto($farst_05_str_01_tmp);
        //=== company_key の値取得
        $Get_company_secret_val = Cut_Quoto($farst_05_str_02_tmp);

        //======================== ダブルクォーテーション 削除 END

        $last_obj = array_pop($arr_01); // 一番後ろの要素 取得

        $arr_last = explode(" ", $last_obj);

        //=== customer[custom:country]=値　を配列に挿入
        array_push($arr_01, $arr_last[1]);

        //======================== API 送信先の URL を格納
        $Post_URL = $arr_last[2];

        // ========= 先頭の要素　、　最後の要素を取得 END =========


        //================ データの　半角, 全角　空白除去 ================
        $index = 0;
        for ($i = 0; $i < count($arr_01); $i++) {
            $tmp = str_replace(array(" ", "　"), "", $arr_01[$i]);
        }

        //===   イコール（=） を　* アスタリスク * へ 置換
        for ($i = 0; $i < count($arr_01); $i++) {
            $tmp = str_replace(array("=", ""), "*", $arr_01[$i]);
            $arr_01[$i] = $tmp . "*";
        }

        // ===================================================================================
        // =============================== PODT データ　書式加工 
        // ===================================================================================

        // collection_route  のテストデータ　加工 2022/01/28  
        $arr_01[1] = str_replace("/", "-", $arr_01[1]);
        // print_r($arr_01);

        // 上で加工した、POST 用データを　文字列へ変換
        $str_02 = implode($arr_01);
        // print($str_02);

        // アスタリスク *  で、配列に分割する
        $arr_02 = explode("*", $str_02);
        // print($arr_02);


        $arr_key = [];
        $arr_val = [];


        $arr_test = [];
        // customer の文字列があった場合は、 index + 1 の値を　配列へ入れる。
        for ($i = 0; $i < count($arr_02); $i++) {

            // ========= cusutomer の値, eky を取得
            if (strpos($arr_02[$i], "customer") !== false) {
                $arr_key[$i] = str_replace(array("\r\n", "\r", "\n"), "", $arr_02[$i]);
                $arr_val[$i] = Cut_Quoto($arr_02[$i + 1]);
            }
        }

        //========= 配列の抜け番を無くす =========
        $arr_key = array_values($arr_key);
        $arr_val = array_values($arr_val);

        // ===================================================================================
        // =============================== PODT データ　テストデータ変更 
        // ===================================================================================

        // ================ テストデータ　値変更


        //====================================
        //=== 更新データ
        //====================================

        /*
$arr_val[0] = "email";
//$arr_val[0] = "hashcode";
$arr_val[1] = "2022/03/09";
$arr_val[4] = "POST テスト 自社 2022/03/09JIMJIMテスト株式会社";
$arr_val[5] = "POST";
$arr_val[6] = "POST";
$arr_val[7] = "POST 夏目（更新）0309";
$arr_val[8] = "既存データ0309";
$arr_val[9] = "なつめ POST";
$arr_val[10] = "こうしんでーた POST";
$arr_val[11] = "955-0092";
$arr_val[12] = "更新 新潟県新規テスト住所";
$arr_val[13] = "0256-1111-1111";
$arr_val[14] = "0256-5555-5555";
$arr_val[15] = "info@asano-metal.co.jp";
*/



        //====================================
        //=== 新規データ
        //====================================

        /*
$arr_val[0] = "email";
//$arr_val[0] = "hashcode";
$arr_val[1] = "2022/03/07";
$arr_val[4] = "新規JIMJIMテスト株式会社";
$arr_val[5] = "企画部";
$arr_val[6] = "部長";
$arr_val[7] = "夏目（新規）0307";
$arr_val[8] = "新規データ0307";
$arr_val[9] = "なつめ";
$arr_val[10] = "しんきでーた";
$arr_val[11] = "955-0092";
$arr_val[12] = "新規 新潟県新規テスト住所";
$arr_val[13] = "0256-22-3333";
$arr_val[14] = "0256-99-8888";
$arr_val[15] = "natsume@jimnet.co.jp";
*/


        // $arr_val[15] = "natsume@jimnet.co.jp";

        //=== 出力
        // print_r($arr_key); // プロパティ
        // print_r($arr_val); // 値

        // 出力
        /*
print_r($arr_key); // プロパティ
print_r($arr_val); // 値
*/

        $k = implode($arr_key);

        //============ [] 内の文字だけ取得する ***
        $str = $k;
        $pattern = '/\[.+?\]/';
        preg_match_all($pattern, $str, $key_match);

        for ($i = 0; $i < count($key_match); $i++) {

            $target = array('[', ']'); // [] 括弧 を削除
            #$tmp = str_replace(array('[', ']'), "", $key_match[$i]);
            $tmp = str_replace($target, "", $key_match[$i]);
            $arr_keys[$i] = $tmp;
        }

        // ========= 配列の index の　抜け番をなくして 0からにする#
        # $arr_keys = array_values($arr_keys);

        // カスタマー　キー coustomer 
        $arr_customer_key = [];
        for ($i = 0; $i < count($arr_val); $i++) {
            $arr_customer_key[$i] = "customer[" . $arr_keys[0][$i] . "]";
        }

        # print_r($arr_customer_key);

        $arr_customer_VAL = [];

        // === customer の key , value で　key,value 配列作成 array_combine ***
        $arr_customer_VAL = array_combine($arr_customer_key, $arr_val);

        // === user_key などの変数を key,value 配列へ変換

        //======================================== 下記のコメントアウトをしないと、APIの送信処理が成功しない curl 送信

        //==============　＊＊＊＊＊＊＊＊＊ エラー用テストデータ 本番は　以下　４つ　をコメントアウトする　＊＊＊＊＊＊＊＊
		
/*
        $Get_user_key_val = "";
        $Get_user_secret_val = "";
        $Get_company_key_val = "";
        $Get_company_secret_val = "";
*/


        $arr_user_conf = array(
            $Get_user_key => $Get_user_key_val,
            $Get_user_secret => $Get_user_secret_val,
            $Get_company_key => $Get_company_key_val,
            $Get_company_secret => $Get_company_secret_val
        );

        // ========== SATORI API への 送信データ　作成
        // ====== user_key などの配列と、 customer の配列を結合して、送信用データ作成
        $data_arr = array_merge($arr_user_conf, $arr_customer_VAL);


        // ====== 連想配列　へ 変換
        $Send_Data_Arr = [];
        $idx = 0;

        # print_r($data_arr);

        //=== ２次元配列を Json 形式前データ作成
        foreach ($data_arr as $key => $value) {

            $Send_Data_Arr[$idx][$key] = $value;

            $idx++;
        }


        // ****** Json に データを変換 ****** 使用しない
        $Send_Data = json_encode($Send_Data_Arr, JSON_UNESCAPED_UNICODE);

        //===================================== トライ 002 END ======================================

        //==================================== CURL 送信部分 Start ====================================

        ### curl 送信コード　流れ ###

        # セッション初期化
        $curl = curl_init();

        # // POST 送信
        curl_setopt($curl, CURLOPT_POST, true);

        # URL の指定  $Post_URL
        # curl_setopt($curl, CURLOPT_URL, '送信先URL');

        // 2022/02/22 テスト送信先　URL

        // 新規 URL
        // $Post_URL = "https://api.satr.jp/api/v4/public/customer/registration.json";

        curl_setopt($curl, CURLOPT_URL, $Post_URL);

        # 返り値の設定
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  // 証明書の検証を無効化
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);  // 証明書の検証を無効化 

        # ヘッダー情報の設定
        # curl_setopt($curl, CURLOPT_HTTPHEADER, array(コンテントタイプ));
        # SATORI用　コンテントタイプ　Content-Type： application/x-www-form-urlencoded

        # 送信データ
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data_arr));

        // header 情報
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept-Charset: UTF-8',
        ];
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        # 実行
        $output = curl_exec($curl);

        # HTTPステータスコード取得
        $httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        // エラーが発生したかどうかを確認します
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            // echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
        }

        # SATORI 連携　API への curl を閉じる
        curl_close($curl);

        //=== get_now_date クラスオブジェクト
        $get_now_date = new get_now_date();


        //=== $output スタータス　（SATORI のステータス取得）
        $output_result = mb_convert_encoding($output, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        // 連想配列へ変換
        $arr_json = json_decode($output_result, true);

        $output_result_i = $arr_json['status'];

        // エラーファイルが作られていれば True
        $Err_file_Flg = false;


        # POST 送信結果　判定
        if ($output_result_i == 200) {
            // リクエスト成功
            echo $output;
            // print $arr_json['status'];
            return;
        } else {
            //*********************************************************************************** */
            // *********************************** リクエスト失敗 ***********************************
            //*********************************************************************************** */

            // オブジェクト 日付オブジェクト
            $get_now_arr = $get_now_date->get_to_date();

            $year = $get_now_arr['year'];    // 年
            $month = $get_now_arr['month'];  // 月
            $day = $get_now_arr['day'];      // 日

            $hour = $get_now_arr['hour'];    // 時間
            $minute = $get_now_arr['minute']; // 分

            $file_name = "NG_list_" . $year . "_" . $month . $day . ".txt";

            // $now_day => int 変換
            $now_hour_i = (int) $now_hour;          // 時間
            $now_minutes_i = (int) $now_minutes;    // 分
            $now_seconds_i = (int) $now_seconds;    // 秒

            //============================================ ファイル名　分岐ロジック
            // === 日付($day)にプラス 1 をして ファイルを作成する

            if (!file_exists($file_name)) {

                if ($now_hour_i >= 17) {

                    if ($now_minutes_i > 0) {

                        print "日付プラス 1 ファイル";

                        // オブジェクト 日付オブジェクト
                        $get_now_arr = $get_now_date->get_to_date();

                        $file_year = $get_now_arr['year'];    // 年
                        $file_month = $get_now_arr['month'];  // 月
                        $file_day = $get_now_arr['day'];      // 日

                        // 日付をプライス１する
                        $file_day_i = (int)$file_day;
                        $file_day = $file_day_i + 1;

                        $file_hour = $get_now_arr['hour'];    // 時間
                        $file_minute = $get_now_arr['minute']; // 分

                        $day_t = date("d", strtotime("+1 day"));

                        $file_name = "NG_list_" . $file_year . "_" . $file_month . $day_t . ".txt";
                    }
                } else {

                    $get_now_arr = $get_now_date->get_to_date();

                    $file_year = $get_now_arr['year'];    // 年
                    $file_month = $get_now_arr['month'];  // 月
                    $file_day = $get_now_arr['day'];      // 日

                    $file_hour = $get_now_arr['hour'];    // 時間
                    $file_minute = $get_now_arr['minute']; // 分

                    $file_name = "NG_list_" . $file_year . "_" . $file_month . $file_day . ".txt";
                }
            } else {

                // ファイルが存在指定場合 処理なし

            }

            //======================================== ファイル作成処理 ========================================

            //================= ================= 
            //================= ログファイル用　.txt への挿入データ
            //================= ================= 


            //=================== .txt に入れる　log 用の値を取得 ======================
            $arr_tmp = [];
            // function Arr_Log_Write($arr_target, $arr_in, $message)
            $arr_logs_val = Arr_Log_Write($arr_val, $arr_tmp, "値が空です");
            // key value の形で配列を　２次元配列へ　マージ

            $arr_LOG_VAL = array_combine($arr_customer_key, $arr_logs_val);

            $text_change_key = [];
            $text_change_key = Log_Key_Name_Change($arr_val);


            //=================== .txt に入れる　log 用の値を取得 END ===================

            // ファイルの存在確認
            if (!file_exists($file_name)) {

                // ファイル作成
                touch($file_name);
                $file_num = 1;

                $file_header_title = $year . "年" . $month . "月" . $day . "日" . "<<< 送信エラー リスト >>>" . "\n";

                // Class Log_output , CREATE_LOG を呼び出し
                $test_arr = [];
                $log_output = new Log_output($data_arr, $Post_URL);
                $test_arr = $log_output->CREATE_LOG();

                // ログ項目
                $get_url = $test_arr['get_url'];
                $log_0 = $test_arr['log_0'];
                $log_1 = $test_arr['log_1'];
                $log_2 = $test_arr['log_2'];
                $log_3 = $test_arr['log_3'];
                $log_4 = $test_arr['log_4'];
                $log_5 = $test_arr['log_5'];
                $log_6 = $test_arr['log_6'];
                $log_7 = $test_arr['log_7'];
                $log_8 = $test_arr['log_8'];
                $log_9 = $test_arr['log_9'];
                $log_10 = $test_arr['log_10'];
                $log_11 = $test_arr['log_11'];
                $log_12 = $test_arr['log_12'];
                $log_13 = $test_arr['log_13'];
                $log_14 = $test_arr['log_14'];
                $log_15 = $test_arr['log_15'];
                $log_16 = $test_arr['log_16'];
                $log_17 = $test_arr['log_17'];
                $log_18 = $test_arr['log_18'];
                $log_19 = $test_arr['log_19'];
                $log_20 = $test_arr['log_20'];
                $log_21 = $test_arr['log_21'];
                $log_22 = $test_arr['log_22'];

                // ファイルが作成済みの場合は　書き込み
                file_put_contents($file_name, $file_header_title . "\n\n" . $file_num . "件：" . $get_url . "\n" .
                    "■ エラーコード：" . $arr_json['status'] . "   " . "■ エラーメッセージ：" . $arr_json['message'] . "\n" .
                    $log_0 . "\n" .
                    $log_1 . "\n" .
                    $log_2 . "\n" .
                    $log_3 . "\n" .
                    $log_4 . "\n" .
                    $log_5 . "\n" .
                    $log_6 . "\n" .
                    $log_7 . "\n" .
                    $log_8 . "\n" .
                    $log_9 . "\n" .
                    $log_10 . "\n" .
                    $log_11 . "\n" .
                    $log_12 . "\n" .
                    $log_13 . "\n" .
                    $log_14 . "\n" .
                    $log_15 . "\n" .
                    $log_16 . "\n" .
                    $log_17 . "\n" .
                    $log_18 . "\n" .
                    $log_19 . "\n" .
                    $log_20 . "\n" .
                    $log_21 . "\n" .
                    $log_22 . "\n" .
                    "-----------------------------------------------------------------------------" . "\n", FILE_APPEND);
            } else {

                // ファイルの行数を取得
                $lines = file($file_name);
                $count = count(file($file_name));

                // ファイルの中に「エラーコード」が何個含まれているか　カウント
                $file_temp_obj = file_get_contents($file_name);
                $file_count_num = substr_count($file_temp_obj, "エラーコード");

                // Class Log_output , CREATE_LOG を呼び出し
                $test_arr = [];
                $log_output = new Log_output($data_arr, $Post_URL);
                $test_arr = $log_output->CREATE_LOG();

                // ログ項目
                $get_url = $test_arr['get_url'];
                $log_0 = $test_arr['log_0'];
                $log_1 = $test_arr['log_1'];
                $log_2 = $test_arr['log_2'];
                $log_3 = $test_arr['log_3'];
                $log_4 = $test_arr['log_4'];
                $log_5 = $test_arr['log_5'];
                $log_6 = $test_arr['log_6'];
                $log_7 = $test_arr['log_7'];
                $log_8 = $test_arr['log_8'];
                $log_9 = $test_arr['log_9'];
                $log_10 = $test_arr['log_10'];
                $log_11 = $test_arr['log_11'];
                $log_12 = $test_arr['log_12'];
                $log_13 = $test_arr['log_13'];
                $log_14 = $test_arr['log_14'];
                $log_15 = $test_arr['log_15'];
                $log_16 = $test_arr['log_16'];
                $log_17 = $test_arr['log_17'];
                $log_18 = $test_arr['log_18'];
                $log_19 = $test_arr['log_19'];
                $log_20 = $test_arr['log_20'];
                $log_21 = $test_arr['log_21'];
                $log_22 = $test_arr['log_22'];


                // ファイルが作成済みの場合は　書き込み  
                file_put_contents($file_name, $file_count_num + 1 . "件：" . $get_url .
                    "■ エラーコード：" . $arr_json['status'] . "   " . "■ エラーメッセージ：" . $arr_json['message'] . "\n" .
                    $log_0 . "\n" .
                    $log_1 . "\n" .
                    $log_2 . "\n" .
                    $log_3 . "\n" .
                    $log_4 . "\n" .
                    $log_5 . "\n" .
                    $log_6 . "\n" .
                    $log_7 . "\n" .
                    $log_8 . "\n" .
                    $log_9 . "\n" .
                    $log_10 . "\n" .
                    $log_11 . "\n" .
                    $log_12 . "\n" .
                    $log_13 . "\n" .
                    $log_14 . "\n" .
                    $log_15 . "\n" .
                    $log_16 . "\n" .
                    $log_17 . "\n" .
                    $log_18 . "\n" .
                    $log_19 . "\n" .
                    $log_20 . "\n" .
                    $log_21 . "\n" .
                    $log_22 . "\n" .
                    "-----------------------------------------------------------------------------" . "\n", FILE_APPEND);
            }
            //======================================== ファイル作成処理 END ========================================

            echo $output;

            // ########### 0309_index.php 下記コードあり　（スラック送信部分） ###########
        }
    } else {
        print "POSTデータが空です。";
    }
} else {
    print "POSTデータがセットされていません。";
}
