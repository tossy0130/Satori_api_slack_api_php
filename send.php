<?php

// エラーを出力する
ini_set('display_errors', "On");
//タイムゾーンを日本の時間に設定
date_default_timezone_set('Asia/Tokyo');
// functions.php 読み込み
require(dirname(__FILE__) . '/functions.php');

//====================================　ファイル存在チェック ====================================
$result_file = [];
$result_file = glob("./*.txt");

if (is_array($result_file) && empty($result_file)) {

    // ====================================================== エラーなし　メッセージ送信
    $curl_send = curl_init();

    // 登録エラー無し メッセージ送信
    $message = [
        //"channel" => "#api-作成", // テスト 送信先 チャンネル指定
        "channel" => "#satori_api通知",
        "text" => "（本番 テスト）テスト送信 お疲れ様でした。本日登録エラーは、ありませんでした。",
    ];

    // 設定画面に表示されたWebhook URL
    // $url = "https://hooks.slack.com/services/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";
  

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'payload' => json_encode($message)
        ])
    ];

    # HTTPステータスコード取得
    // $httpcode = curl_getinfo($curl_slack, CURLINFO_RESPONSE_CODE);

    curl_setopt_array($curl_send, $options);
    $httpcode = curl_exec($curl_send);

    curl_close($curl_send);

    if (strpos($httpcode, "ok") !== false) {
        print("送信スタッツ:" . $httpcode . ":::********** スラックへのファイル送信OK :::" . "\n\n" .
            "メッセージ内容：：：" . "テスト送信 本日登録エラーは、ありませんでした。");
        return;
    } else {
        print($httpcode);
        return;
    }
} else {

    // ====================================================== エラーあり　ファイル添付メッセージ送信

    // ファイル名取得
    $Send_File_Name = mb_substr($result_file[0], 2);

    //================ 現在時刻取得 END ================
    // スラック API トークン

    // テストトークン
    //   $slacktoken = "xoxb-3173575395252-3163817438487-lpM9qHFlmNvjvmXQEzpanOTN";

    // テスト スラック ターゲットチャンネル（送信先）
    //  $channelId = 'general';
    // 本番　スラック ターゲットチャンネル （送信先）
    $channelId = 'satori_api通知';

    $header = array();
    $header[] = 'Content-Type: multipart/form-data';
    // $file = new CurlFile(dirname(__FILE__) . "/NG_list_2022_0302.txt");
    $file = new CurlFile(dirname(__FILE__) . "/" . $Send_File_Name);

    // 送信パラメーター設定
    $postitems =  array(
        'token' => $slacktoken,
        'channels' => $channelId,
        'file' =>  $file,
        'text' => "（本番チャンネルテスト）本日の登録エラーリストを送信しました。",
        'title' => "（本番チャンネルテスト）登録エラーリスト送信",
        'filename' => $Send_File_Name
    );

    $curl_slack = curl_init();
    curl_setopt($curl_slack, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_slack, CURLOPT_FAILONERROR, true);
    curl_setopt($curl_slack, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl_slack, CURLOPT_URL, "https://slack.com/api/files.upload");
    curl_setopt($curl_slack, CURLOPT_POST, 1);
    curl_setopt($curl_slack, CURLOPT_POSTFIELDS, $postitems);

    $body = curl_exec($curl_slack);

    # HTTPステータスコード取得
    $httpcode = curl_getinfo($curl_slack, CURLINFO_RESPONSE_CODE);

    $errno = curl_errno($curl_slack);
    $error = curl_error($curl_slack);
    curl_close($curl_slack);

    if ($httpcode == 200) {
        print("送信スタッツ:" . $httpcode . ":::********** スラックへのファイル送信OK（エラーあり ファイル添付） ********");

        //================== ファイル移動 /Log_Tmp 
        $log_path  = "./Log_Tmp/";
        // .txt ファイルがあったらログファイルを移動する function Move_FILE

        // *********** 本番はコメントアウトを外す
        // Move_FILE($log_path);

        return;
    } else {
        print($httpcode);
        return;
    }
}
