<?php
/**
 * 这段代码用来实现并行地轮询多个url 。当某个url返回结果时，能即时再次发起请求。
 * - 借助 curl_multi 的多线程
 */
$url_array = ['http://google.com', 'http://www.baidu.com', 'http://163.com'];

/**
 * get handlers for loop
 * @param $array
 * @return array
 */
function get_handlers($array)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $array[0],
    ]);

    $handlers = [$ch];
    for ($i = 1; $i < count($array); $i++) {
        $ch_copy = curl_copy_handle($ch);
        curl_setopt($ch_copy, CURLOPT_URL, $array[$i]);
        $handlers[] = $ch_copy;
    }
    return $handlers;
}

$mh = curl_multi_init();

$handlers = get_handlers($url_array);
foreach ($handlers as $h) {
    curl_multi_add_handle($mh, $h);
}

// loop
$running = null; // unused
do {
    curl_multi_exec($mh, $running);
    $info = curl_multi_info_read($mh);
    if ($info) {
        /**
         * info example：
         * array(3) {
            'msg' =>
            int(1)
            'result' =>
            int(0)
            'handle' =>
            resource(7) of type (curl)
           }
         */
        $handle = $info['handle'];
        if ($info['result']) { // fail
            $handle_info = curl_getinfo($handle);
            var_dump($handle_info['url']); // error url
            var_dump(curl_strerror($info['result'])); // error info

            // if continue to request failed url, curl_copy_handle will not work
            curl_multi_remove_handle($mh, $handle);
            curl_close($handle);
        } else { // success
            $handle_info = curl_getinfo($handle);
            $content = curl_multi_getcontent($handle); // get result

            // ... do something with result
            var_dump($handle_info['url']); // url
            var_dump(substr($content, 0, 100)); // content

//            sleep(5); // sleep will block all request
            $handle_copy = curl_copy_handle($handle); // copy handle
            curl_multi_remove_handle($mh, $handle);
            curl_close($handle);
            curl_multi_add_handle($mh, $handle_copy); // continue loop
        }
    }
} while (1);