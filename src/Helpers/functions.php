<?php

if(!function_exists('responseReturn')) {
    /**
     * 接口统一输出
     * @param array $configCode
     * @param array $data
     * @param string $code
     * @param string $msg
     * @param int $is_return
     * @return false|string|void
     */
    function responseReturn(array $configCode, array $data=[], string $code='', string $msg='', int $is_return=0)
    {
        $message = $msg ?: ($configCode ? $configCode[1]: '服务异常');
        $status = !empty($configCode[0]) ? $configCode[0] : ($code ?: 500);

        if($is_return){
            return json_encode([
                'status' => $status,
                'msg' => $message,
                'data' => $data
            ]);
        }

        echo json_encode([
            'status' => $status,
            'msg' => $message,
            'data' => $data
        ]);
        die;
    }
}


/**
 * 生成订单号
 */
if(!function_exists('getOrderSn')) {
    function getOrderSn(): string
    {
        return date('Ymd')
            . substr(microtime(), 2, 5)
            . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}

if(!function_exists('generateTree')){
    /**
     * 构建树
     * @param $data
     * @param int $parentId
     * @return array
     */
    function generateTree($data, int $parentId = 0): array
    {
        $tree = [];
        foreach ($data as $key => $value){
            if($value['pid'] == $parentId){
                $value['children'] = generateTree($data , $value['id']);
                $tree[] = $value;
            }
        }
        return $tree;
    }
}


if(!function_exists('rmInvalidValues')) {
    /**
     * 去除二维数组无效字段
     * @param array $data
     * @param array $field
     * @return array
     */
    function rmInvalidValues(array $data,array $field): array
    {
        foreach ($data as &$datum) {
            if($diff = array_diff(array_keys($datum),$field)){
                //比较的数组，比需要的字段少，有交集
                if(array_intersect ($diff , $field)){
                    continue;
                }
                //删除多余字段
                foreach ($diff as $d){
                    unset($datum[$d]);
                }
            }
        }
        return $data;
    }
}


if(!function_exists('FormatTime')) {
    /**
     * 时间戳格式化
     * @param $time
     * @param string $format
     * @return false|string
     */
    function FormatTime($time, string $format='Y-m-d H:i:s')
    {
        return empty($time) ? '' : date($format, $time);
    }
}


if(!function_exists('getLastLevelSubcategories')) {
    /**
     * 获取最后一子集id
     * @param $parentId
     * @param $data
     * @param array $subcategories
     * @return array
     */
    function getLastLevelSubcategories($parentId, $data , array &$subcategories=[]): array
    {
        $flag = 1;
        foreach ($data as  $value) {
            if($value['pid'] == $parentId){
                getLastLevelSubcategories( $value['id'],$data,$subcategories);
                $flag = 0;
            }
        }
        //没有子集
        if($flag){
            $subcategories[] =$parentId;
        }
        return $subcategories;
    }
}

if(!function_exists('curl_request')){
    /**     　　　
     * @desc curl 请求     　　　　　　
     * @return 'url请求地址 '    　　　　　　
     * @return 'method 请求方法（POST,GET,PUT）等'
     * @return 'postfields 上传值'
     * @return 'ssl 是否开启https'
     * @return 'headers 请求头部信息'
     */
    function curl_request($url,$method="POST", $postfields = null, $ssl=false, $headers=array(),$timeOut = 30,$isReturn=true){
        # curl完成初始化
        $curl = curl_init();
        # curl 选项设置
        curl_setopt($curl, CURLOPT_URL, $url); //需要获取的URL地址
        $user_agent = '';
        $method = strtoupper($method);
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $tmpdatastr);
                }
                break;
            case "PUT" :
                curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($curl, CURLOPT_POSTFIELDS,$tmpdatastr);
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
                break;
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);   # 在HTTP请求中包含一个"User-Agent: "头的字符串，声明用什么浏览器来打开目标网页

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);     # TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。

        curl_setopt($curl, CURLOPT_AUTOREFERER, true);        # TRUE 时将根据 Location: 重定向时，自动设置 header 中的Referer:信息。

        curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);              # 设置超时时间

        curl_setopt($curl, CURLOPT_ENCODING, '');
        # HTTP请求头中"Accept-Encoding: "的值。 这使得能够解码响应的内容。 支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，会发送所有支持的编码类型

        if($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  # 设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
        }

        # SSL相关，https需开启
        if ($ssl) {
            curl_setopt($curl, CURLOPT_CAINFO, '/cert/ca.crt');  # CA 证书地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);    # 禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            # 设置为 1 是检查服务器SSL证书中是否存在一个公用名；设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配；0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）。
            # 公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)
        }else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);    # 禁用后cURL将终止从服务端进行验证，默认为 true
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        curl_setopt($curl, CURLOPT_HEADER, false);             # 是否处理响应头，启用时会将头文件的信息作为数据流输出

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);      # TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。

        # 执行 curl 会话
        $response = curl_exec($curl);

        if (false === $response && $isReturn) {
            responseReturn([500, '服务器异常'],[curl_error($curl)]);
        }

        #关闭 curl会话
        curl_close($curl);
        return $response;
    }
}

if(!function_exists('formatSizeUnits')) {
    /**
     * 格式化 大小
     * @param $size
     * @return string
     */
    function formatSizeUnits($size): string
    {
        $kb = 1024;          // Kilobyte
        $mb = 1024 * $kb;    // Megabyte
        $gb = 1024 * $mb;    // Gigabyte
        $tb = 1024 * $gb;    // Terabyte
        if($size < $kb)
            return $size.'B';

        else if($size < $mb)
            return round($size/$kb,2).'KB';

        else if($size < $gb)
            return round($size/$mb,2).'MB';

        else if($size < $tb)
            return round($size/$gb,2).'GB';

        else
            return round($size/$tb,2).'TB';
    }
}


if (!function_exists('generateUUIDv4')) {
    /**
     * 生产uuid
     * @throws Random\RandomException
     */
    function generateUUIDv4() {
        // 生成一个随机的16字节序列
        $data = random_bytes(16);

        // 将字节序列转换为十六进制表示
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // 设置版本为4（二进制中的1000）
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // 设置变体为2（二进制中的10000000）

        // 转换为8-4-4-4-12格式的UUID字符串
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}


if (!function_exists('GetDayTime')) {
    /**
     * 获取阶段时间
     * @param int $dateType
     * @return array
     */
    function GetDayTime(int $dateType = 0,$t=1): array
    {
        $time = time();    //当前时间戳
        $now_day = date('w', $time);  //当前是周几

        //获取周一
        $monday_str = $time - ($now_day - 1) * 86400;
        $monday = strtotime(date('Y-m-d', $monday_str));

        //获取周日
        $sunday_str = $time + (7 - $now_day + 1) * 86400;
        $sunday = strtotime(date('Y-m-d', $sunday_str));
        //月初
        $monthFirstDay = strtotime(date('Y-m'));
        //月末
        $monthLastDay = strtotime(date('Y-m-t')) + 86400;

        //每年初
        $yearFirstDay = strtotime(date('Y-01-01'));
        //每年底
        $yearLastDay = strtotime(date('Y-01-01', strtotime('+1 year')));

        //上月末
        $lastMonthEndDay = strtotime(date('Y-m-t', strtotime('-1 month')));
        $lastMonthFirstDay = strtotime(date('Y-m-1', strtotime('-1 month')));

        //当天凌晨时间戳
        $nowEndDay = strtotime(date('Y-m-d',$time));
        //几天前的时间戳
        $nowStartDay = $nowEndDay - 86400 * $t;

        switch ($dateType) {
            case 1:
                $start_at = $monday;
                $end_at = $sunday;
                break;
            case 2:
                $start_at = $monthFirstDay;
                $end_at = $monthLastDay;
                break;
            case 3:
                $start_at = $yearFirstDay;
                $end_at = $yearLastDay;
                break;
            case 4:
                $start_at = $lastMonthFirstDay;
                $end_at = $lastMonthEndDay;
                break;
            case 5:
                $start_at = $nowStartDay;
                $end_at = $nowEndDay;
                break;
            default:
                return compact('monday', 'sunday', 'monthFirstDay', 'monthLastDay', 'yearFirstDay', 'yearLastDay');
        }
        return compact('start_at', 'end_at');
    }
}


if (!function_exists('modbus_crc16')) {
    /**
     * 计算modbus crc16
     * @param $data
     * @return string
     */
    function modbus_crc16($data)
    {
        $crc = 0xFFFF; // 初始值
        $poly = 0xA001; // 生成多项式
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($data[$i]); // 逐字节异或

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x0001) { // 如果最低位是1
                    $crc = ($crc >> 1) ^ $poly; // 右移一位并与生成多项式异或
                } else {
                    $crc >>= 1; // 否则只右移一位
                }
            }
        }

        return sprintf("%04X", $crc); // 返回16进制字符串，确保是4位
    }
}

if(!function_exists('pregCalculation')) {
    function pregCalculation($str)
    {
        if(empty($str)){
            return 0;
        }
        // 使用正则表达式匹配数字
        preg_match_all('/\d+/', $str, $matches);
        // 提取匹配到的数字
        $numbers = $matches[0];
        // 初始化运算结果
        $result = 0;
        // 遍历数字，并根据运算符进行计算
        $i = 0;
        $operator = '';
        while ($i < count($numbers)) {
            $num = $numbers[$i];

            // 判断当前字符是否为运算符
            if ($num === '+' || $num === '-' || $num === '*' || $num === '/') {
                $operator = $num;
                $i++;
                continue;
            }
            // 根据上一个运算符进行计算
            if ($operator === '+') {
                $result = bcadd($result, $num, 2);
            } elseif ($operator === '-') {
                $result = bcsub($result, $num, 2);
            } elseif ($operator === '*') {
                $result = bcmul($result, $num, 2);
            } elseif ($operator === '/') {
                $result = bcdiv($result, $num, 2);
            }

            $i++;
        }
        return $result;
    }
}


if (!function_exists('getParents')) {
    /**
     * 根据子类id的所获取有父类
     * @param $data
     * @param $id
     * @return array
     */
    function getParents($data, $id): array
    {
        $tree = array();
        foreach ($data as $item) {
            if ($item['id'] == $id) {
                if ($item['pid'] > 0)
                    $tree = array_merge($tree, getParents($data, $item['pid']));
                $tree[] = $item;
                break;
            }
        }
        return $tree;
    }
}

