<?php
/**
 * 这段代码用来实现并行地请求
 * - 借助 curl_multi
 */
$url_array = ['http://app.sk.sigcms.com/T/s', 'http://app.sk.sigcms.com/T/s'];

class Concurrent_request
{
    private array $handlers;
    private int $total_count = 0;
    private int $return_count = 0;
    /** @var resource */
    private $mh; // curl multi resource

    public function __construct($url_array)
    {
        $this->handlers = $this->get_handlers($url_array);
    }

    public function get_handlers($array)
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

    public function start()
    {
        $this->mh = curl_multi_init();
        foreach ($this->handlers as $h) {
            curl_multi_add_handle($this->mh, $h);
            $this->total_count++;
        }

        do {
            echo 'start exec: ' . ($s = microtime(true)) . "\n";
            curl_multi_exec($this->mh, $running);
            curl_multi_select($this->mh); // 避免 100% cpu;select 不是只有请求返回时才会返回
            echo 'select return: ' . (microtime(true) - $s) . "\n";

            $info = curl_multi_info_read($this->mh);
            if ($info) {
                $this->process_result($info);
            }
        } while ($running);

        if ($this->return_count !== $this->total_count) {
            echo "multi not running, but not all result received\n";
        }
        while ($this->return_count !== $this->total_count) {
            if ($info = curl_multi_info_read($this->mh)) {
                $this->process_result($info);
            }
        }
    }

    private function process_result($info)
    {
        $this->return_count++;
        /**
         * info example：
         * array(3) {
         * 'msg' =>
         * int(1)
         * 'result' =>
         * int(0)
         * 'handle' =>
         * resource(7) of type (curl)
         * }
         */
        $handle = $info['handle'];
        if ($info['result']) { // fail
            $handle_info = curl_getinfo($handle);
            var_dump($handle_info['url']); // error url
            var_dump(curl_strerror($info['result'])); // error info

            // if continue to request failed url, curl_copy_handle will not work
            curl_multi_remove_handle($this->mh, $handle);
            curl_close($handle);
        } else { // success
            $handle_info = curl_getinfo($handle);
            $content = curl_multi_getcontent($handle); // get result

            // ... do something with result
            var_dump($handle_info['url']); // url
            var_dump(substr($content, 0, 100)); // content

//            sleep(5); // sleep will block all request
            curl_multi_remove_handle($this->mh, $handle);
            curl_close($handle);
        }
    }
}

(new Concurrent_request($url_array))->start();
