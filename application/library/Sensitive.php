<?php
class Sensitive {
 
    private static $instance = null;
 
    /**
     * 替换符号
     * @var string
     */
    private static $replaceSymbol = "*";
 
    /**
     * 敏感词树
     * @var array
     */
    private static $sensitiveWordTree = [];
 
    /**
     * 获取实例
     */
    public static function getInstance() {
        if (!(self::$instance instanceof DfaCheck)) {
            return self::$instance = new self;
        }
        return self::$instance;
    }
 
    /**
     * 添加敏感词，组成树结构。
     * 例如敏感词为：傻子是傻帽，白痴，傻蛋 这组词组成如下结构。
     * [
     *     [傻] => [
     *           [子]=>[
     *               [是]=>[
     *                  [傻]=>[
     *                      [帽]=>[false]
     *                  ]
     *              ]
     *          ],
     *          [蛋]=>[false]
     *      ],
     *      [白]=>[
     *          [痴]=>[false]
     *      ]
     *  ]
     * @param $file_path 敏感词库文件路径
     */
    public static function addSensitiveWords(string $file_path) {
		ini_set('memory_limit', -1);
        $text = self::readFile($file_path);
        foreach ($text as $key => $words) {
            $len = mb_strlen($words);
            $treeArr = &self::$sensitiveWordTree;
            for ($i = 0; $i < $len; $i++) {
                $word = mb_substr($words, $i, 1);
                //敏感词树结尾记录状态为false；
                if ($i + 1 == $len) {
                    $treeArr[$word]['end'] = false;
                }
                $treeArr = &$treeArr[$word] ?? false;
            }
        }
    }
 
    /**
     * 执行过滤
     * @param string $txt
     * @return string
     */
    public static function execFilter(string $txt): string {
        $wordList = self::searchWords($txt);
        if (empty($wordList))
            return $txt;
        return strtr($txt, $wordList);
    }
 
    /**
     * 搜索敏感词
     * @param string $txt
     * @return array
     */
    public static function searchWords(string $txt): array {
        $txtLength = mb_strlen($txt);
        $wordList = [];
        for ($i = 0; $i < $txtLength; $i++) {
            //检查字符是否存在敏感词树内,传入检查文本、搜索开始位置、文本长度
            $lenList = self::checkWordTree($txt, $i, $txtLength);
            foreach ($lenList as $key => $len) {
                if ($len > 0) {
                    //搜索出来的敏感词
                    $word = mb_substr($txt, $i, $len);
                    $wordList[$word] = str_repeat(self::$replaceSymbol, $len);   //存在敏感词，进行字符替换。
                    if (($key + 1) == count($lenList)) {
                        $i += $len;
                    }
                }
            }
        }
        return $wordList;
    }
 
    /**
     * 检查敏感词树是否合法
     * @param string $txt 检查文本
     * @param int $index 搜索文本位置索引
     * @param int $txtLength 文本长度
     * @return int 返回不合法字符个数
     */
    public static function checkWordTree(string $txt, int $index, int $txtLength): array {
        $treeArr = &self::$sensitiveWordTree;
        $wordLength = 0; //敏感字符个数
        $wordLengthArray = [];
        $flag = false;
        for ($i = $index; $i < $txtLength; $i++) {
            $txtWord = mb_substr($txt, $i, 1); //截取需要检测的文本，和词库进行比对
            //如果搜索字不存在词库中直接停止循环。
            if (!isset($treeArr[$txtWord])) {
                break;
            }
            $wordLength++;
            if (isset($treeArr[$txtWord]['end'])) {//检测还未到底
                $flag = true;
                $wordLengthArray[] = $wordLength;
            }
            $treeArr = &$treeArr[$txtWord]; //继续搜索下一层tree
        }
        //没有检测到敏感词，初始化字符长度
        $flag ?: $wordLength = 0;
        return $wordLengthArray;
    }
 
    /**
     * 读取文件内容
     * @param string $file_path
     * @return Generator
     */
    private static function readFile(string $file_path): Generator {
        $handle = fopen($file_path, 'r');
        while (!feof($handle)) {
            yield trim(fgets($handle));
        }
        fclose($handle);
    }
 
    private function __clone() {
        throw new \Exception("clone instance failed!");
    }
 
    private function __wakeup() {
        throw new \Exception("unserialize instance failed!");
    }
 
}
 
function getMillisecond() {
    list($microsecond, $time) = explode(' ', microtime()); //' '中间是一个空格
    return (float) sprintf('%.0f', (floatval($microsecond) + floatval($time)) * 1000);
}