#include <string.h>
#include <stdio.h>
#include <stdlib.h>

// kmp
// https://en.wikipedia.org/wiki/Knuth%E2%80%93Morris%E2%80%93Pratt_algorithm#Description_of_pseudocode_for_the_search_algorithm

// s 被搜索的字符串；w 要寻找的字符串
char S[] = "128378AB164ABACABABA891476376427197281730";
//char W[] = "123443212311";
char W[] = "ABACABABA";

int* getIndexArr(char* w);
int* getIndexArr2(char* w);

int main() {
    int *t;
    int i, j, len;

    t = getIndexArr2(W);

    len = strlen(W);
    // 输出 t
    for (i = 0; i <= len; i++) {
        printf("%d\t", t[i]);
    }
    printf("\n");

    // 开始匹配
    i = 0;
    for (j = 0; j < strlen(S); ) {
        if (S[j] == W[i]) {
            i++;
            j++;
            if (i == len) {
                printf("found. index: %d\n", j - len + 1);
                break;
            }
            continue;
        } else {
             i = t[i];
             if (i < 0) {
                 i++;
                 j++;
             }
        }
    }
}

// 改进版 - from 维基百科
int* getIndexArr2(char* w) {
    int *t;
    int pos, len, cnd; // pos: t 下标， cnd: w[0]-w[pos-1] 的最长公共子串的下一个字符的索引. {prefix}cnd....{suffix}pos  (其中 prefix==suffix)

    len = strlen(w);
    t = (int *)malloc(len + 1);

    t[0] = -1;
    cnd = 0;
    for (pos = 1; pos < len; pos++) {
        if (w[pos] == w[cnd]) { // T[pos] 表示 s[pos] != w[pos] 后，s[pos] 下次与 w[T[pos]] 比较；如果 w[pos] == w[cnd], 即 s[pos] != w[cnd], 所以 t[pos] 设为 cnd 是多余的，直接设置为 t[cnd]
            t[pos] = t[cnd];
        } else {
            t[pos] = cnd;
            while (cnd >= 0 && w[pos] != w[cnd]) { // 这里是为了计算 T[pos+1] 时的 最长公共子串的 最后一个字符的索引
                cnd = t[cnd];
            }
        }

        cnd++;
    }
    t[pos] = cnd;

    return t;
}

// 最长公共子串: 假设字符串 {prefix}...{suffix}，如果 prefix 与 suffix 相同且最长，prefix/suffix 就是最长公共子串
// T[i] 表示 s[i] != w[i] 后，s[i] 下次与 w[T[i]] 比较；T 的最后一位 T[len(w)] 用于多次匹配，在只匹配一次时用不到
int* getIndexArr(char* w) {
    int *t;
    int i, len, pre;

    len = strlen(w);
    t = (int *)malloc(len + 1);

    // 获取跳转表 T，此时 T[i] 表示 w[0]-w[i-1] 的最长公共子串的长度
    t[0] = -1;
    for (i = 1; i <= len; i++) {
        // T[i] 可以在 T[i-1] 的基础上计算得到:
        //      if (w[T[i-1]] == w[i]) T[i] = T[i-1] + 1
        //      else 继续以 w[0]-w[T[i-1]] 的最长公共子串 为基础进行比较
        pre = t[i - 1];
        while (pre >= 0 && w[pre] != w[i-1]) {
            pre = t[pre];
        }

        if (pre < 0) {
            t[i] = 0;
        } else {
            t[i] = pre + 1;
        }
    }

    // 这一步可以省略，只是为了提高匹配效率
    // 改进跳转表 T，使得 T[i] 表示 s[i] != w[i] 后，s[i] 下次与 w[T[i]] 比较. 如果 w[i] == w[T[i]], 则 T[i] 应当改为 T[T[i]]
    for (i = 1; i <= len; i++) {
        pre = t[i];
        while (pre >= 0 && w[pre] == w[i]) {
            pre = t[pre];
        }
        t[i] = pre;
    }

    return t;
}