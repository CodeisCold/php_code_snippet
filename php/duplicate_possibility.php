<?php

/**
 * 假设每个样本的可能取值个数为m，每个值的概率相等， 求n个样本中不重复的概率
 */
$n = 10000; // 桶的数量
$m = ~(1 << 63); // 每个桶的可能取值个数

// 计算不重复的排列组合 m!/(m-n)!
$non_dup_count = 1;
$o = $m;
for ($i = 1; $i <= $n; $i++) {
    if ($i % 1000 === 0) {
        echo '计算不重复的排列组合: ' . $i . PHP_EOL;
    }
    $non_dup_count = bcmul($non_dup_count, $o);
    $o--;
}

// 计算所有的排列组合 m^n
$all_count = 1;
for ($i = 1; $i <= $n; $i++) {
    if ($i % 1000 === 0) {
        echo '计算所有的排列组合: ' . $i . PHP_EOL;
    }
    $all_count = bcmul($all_count, $m);
}

var_dump(bcdiv($non_dup_count, $all_count, 40));
