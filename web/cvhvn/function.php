<?php

$root = $_SERVER['DOCUMENT_ROOT'];
date_default_timezone_set('Asia/Ho_Chi_Minh');

class System
{


    /***  Hàm gọi tự động các hàm khác  ***/
    public function __construct()
    {
        $this->connect_db();
    }


    /***   Kết Nối Database   ***/
    public function connect_db()
    {
        global $DB;
        $conn = mysqli_connect(
            $DB['SERVER'],
            $DB['USERNAME'],
            $DB['PASSWORD'],
            $DB['TABLE']
        ) or die("Không Thể Kết Nối Tới Cơ Sở Dữ Liệu!");
        $conn->set_charset("utf8");
        return $conn;
    }


    /*** TRUY VẤN SQL ***/

    public function query($sql)
    {
        $row = $this->connect_db()
            ->query($sql);
        return $row;
    }
    public function cong($table, $data, $sotien, $where)
    {
        $row = $this->connect_db()
            ->query("UPDATE `$table` SET `$data` = `$data` + '$sotien' WHERE $where ");
        return $row;
    }
    public function tru($table, $data, $sotien, $where)
    {
        $row = $this->connect_db()
            ->query("UPDATE `$table` SET `$data` = `$data` - '$sotien' WHERE $where ");
        return $row;
    }
    public function insert($table, $data)
    {
        $field_list = '';
        $value_list = '';
        foreach ($data as $key => $value) {
            $field_list .= ",$key";
            $value_list .= ",'" . mysqli_real_escape_string($this->connect_db(), $value) . "'";
        }
        $sql = 'INSERT INTO ' . $table . '(' . trim($field_list, ',') . ') VALUES (' . trim($value_list, ',') . ')';

        return mysqli_query($this->connect_db(), $sql);
    }
    
    public function lastInsertId()
    {
        return mysqli_insert_id($this->connect_db());
    }
    public function update($table, $data, $where)
    {
        $sql = '';
        foreach ($data as $key => $value) {
            $sql .= "$key = '" . mysqli_real_escape_string($this->connect_db(), $value) . "',";
        }
        $sql = 'UPDATE ' . $table . ' SET ' . trim($sql, ',') . ' WHERE ' . $where;
        return mysqli_query($this->connect_db(), $sql);
    }
    public function update_value($table, $data, $where, $value1)
    {
        $sql = '';
        foreach ($data as $key => $value) {
            $sql .= "$key = '" . mysqli_real_escape_string($this->connect_db(), $value) . "',";
        }
        $sql = 'UPDATE ' . $table . ' SET ' . trim($sql, ',') . ' WHERE ' . $where . ' LIMIT ' . $value1;
        return mysqli_query($this->connect_db(), $sql);
    }
    public function remove($table, $where)
    {
        $sql = "DELETE FROM $table WHERE $where";
        return mysqli_query($this->connect_db(), $sql);
    }
    public function get_list($sql)
    {
        $result = mysqli_query($this->connect_db(), $sql);
        if (!$result) {
            die('Câu truy vấn bị sai');
        }
        $return = array();
        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $return[] = $row;
            }
        }
        mysqli_free_result($result);
        return $return;
    }
    public function get_row($sql)
    {
        $result = mysqli_query($this->connect_db(), $sql);
        if (!$result) {
            die('Câu truy vấn bị sai');
        }
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        if ($row) {
            return $row;
        }
        return false;
    }
    public function get_value($sql)
    {
        $result = $this->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_row();
            return $row[0];
        }
        return null;
    }

    public function num_rows($sql)
    {
        $result = mysqli_query($this->connect_db(), $sql);
        if (!$result) {
            die('Câu truy vấn bị sai');
        }
        $row = mysqli_num_rows($result);
        mysqli_free_result($result);
        if ($row) {
            return $row;
        }
        return false;
    }


    public function check_account_exist($username)
    {
        $sql = "SELECT * FROM account WHERE username = '$username'";
        $result = mysqli_query($this->connect_db(), $sql);
        if (mysqli_num_rows($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function check_email_exist($email)
    {
        $conn = $this->connect_db();
        $sql = "SELECT * FROM account WHERE JSON_EXTRACT(email, '$.email') = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            return true;
        } else {
            return false;
        }
    }

    public function check_username_email($username, $email){
    $conn = $this->connect_db();
    $stmt = $conn->prepare("SELECT * FROM account WHERE `username` = ? AND JSON_EXTRACT(email, '$.email') = ? AND JSON_EXTRACT(email, '$.verify') = 'true'");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return true;
    } else {
        return false;
    }
}


    public function taoMK($length = 12) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        $matkhau = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $matkhau - 1)];
        }
        return $password;
    }
    


    /***   Anti SQL Injection - Chỉ nhận dạng Số   ***/
    public function anti_sql($number)
    {
        $id = isset($number) ? (string) (int) $number : false;
        $id = isset($number) ? $number : false;
        $id = str_replace("/[^0-9]/", "", $id);
        return $id;
    }

    public function count($table, $where = null) {
        $sql = "SELECT COUNT(*) as total FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = mysqli_query($this->connect_db(), $sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row["total"];
        } else {
            return 0;
        }
    }

    public function tongdoanhthu() {
        $sql = "SELECT SUM(amount_real) AS total FROM cvh_recharge WHERE status = 1";
        $result = mysqli_query($this->connect_db(), $sql);
    
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return ($row['total'] >= 0) ? $row['total'] : 0;
        } else {
            return 0;
        }
    }

    public function TKhomnay() {
        $todayDate = date("Y-m-d");
        $startTime = $todayDate . " 00:00:00";
        $endTime = $todayDate . " 23:59:59";
    
        $sql = "SELECT COUNT(*) AS total FROM account WHERE create_time BETWEEN '$startTime' AND '$endTime'";
        $result = mysqli_query($this->connect_db(), $sql);
    
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }


    public function DThomnay() {
        $todayDate = date("d/m/Y");
        $startTime = "00:00:00 " . $todayDate;
        $endTime = "23:59:59 " . $todayDate;
    
        $sql = "SELECT SUM(amount_real) AS total FROM cvh_recharge WHERE status = 1 AND STR_TO_DATE(time, '%H:%i:%s %d/%m/%Y') BETWEEN STR_TO_DATE('$startTime', '%H:%i:%s %d/%m/%Y') AND STR_TO_DATE('$endTime', '%H:%i:%s %d/%m/%Y')";
        $result = mysqli_query($this->connect_db(), $sql);
    
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return ($row['total'] >= 0) ? $row['total'] : 0;
        } else {
            return 0;
        }
    }
    
    
    public function setting($id)
    {
        $result = mysqli_query($this->connect_db(), "SELECT * FROM `cvh_setting` WHERE `id`='" . $id . "'");
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $row;
    }

    public function getEmail($data, $key) {
        $data = json_decode($data, true);
        if (array_key_exists($key, $data)) {
            return $data[$key];
        } else {
            return false;
        }
    }

    public function CheckOnline($user){
        $conn = $this->connect_db();
        
        if (is_numeric($user)) {
            $stmt = $conn->prepare("SELECT last_time_login, last_time_logout FROM account WHERE id = ?");
            $stmt->bind_param("i", $user);
        } else {
            $stmt = $conn->prepare("SELECT last_time_login, last_time_logout FROM account WHERE username = ?");
            $stmt->bind_param("s", $user);
        }
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $login_time = strtotime($row['last_time_login']);
            $logout_time = strtotime($row['last_time_logout']);
            
            if ($login_time > $logout_time) {
                echo "/images/online.png";
            } else {
                echo "/images/offline.png";
            }
        } else {
            echo "/images/offline.png";
        }
        
        $stmt->close();
        $conn->close();
    }
    
    


    public function player($account_id)
    {
        $result = mysqli_query($this->connect_db(), "SELECT * FROM `player` WHERE `account_id`='" . $account_id . "'");
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $row;
    }

    public function canUserPost($userId)
    {
        $currentDate = date('Y-m-d');

        $conn = $this->connect_db();

        $sql = "SELECT COUNT(*) as post_count FROM cvh_baiviet WHERE poster = ? AND DATE(created) = ?";
        $result = $conn->prepare($sql);
        $result->bind_param("is", $userId, $currentDate);
        $result->execute();
        $result->bind_result($postCount);
        $result->fetch();

        return $postCount < 3;
    }

    public function get_account_by_username($username)
    {
        $result = mysqli_query($this->connect_db(), "SELECT * FROM account WHERE username = '$username'");

        if (mysqli_num_rows($result) > 0) {

            $account = mysqli_fetch_assoc($result);

        } else {

            $account = array();

        }
        return $account;
    }


    /***   kiểm tra đăng nhập   ***/
    public function check_user($user, $pass)
    {
        $user = str_replace('"', "\"", $user);
        $user = str_replace("'", "\'", $user);
        $pass = str_replace('"', "\"", $pass);
        $pass = str_replace("'", "\'", $pass);

        $result = mysqli_query($this->connect_db(), "SELECT * FROM `account` WHERE `username`='" . $user . "' AND `password`='" . $pass . "' ");
        $rowcount = mysqli_num_rows($result);
        if ($rowcount > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function LoginAD($user, $pass)
    {
        $user = str_replace('"', "\"", $user);
        $user = str_replace("'", "\'", $user);
        $pass = str_replace('"', "\"", $pass);
        $pass = str_replace("'", "\'", $pass);
        $result = mysqli_query($this->connect_db(), "SELECT * FROM `account` WHERE `username`='" . $user . "' AND `pass2`='" . md5($pass) . "' ");
        $rowcount = mysqli_num_rows($result);
        if ($rowcount > 0) {
            return true;
        } else {
            return false;
        }
    }

    /***   kiểm tra người dùng đã có trên hệ thống chưa    ***/
    public function check_user_register($user)
    {
        $user = str_replace('"', "\"", $user);
        $user = str_replace("'", "\'", $user);

        $result = mysqli_query($this->connect_db(), "SELECT * FROM `account` WHERE `username`='" . $user . "'");
        $rowcount = mysqli_num_rows($result);
        if ($rowcount > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPost($id)
    {

        $result = mysqli_query($this->connect_db(), "SELECT * FROM `cvh_baiviet` WHERE `id`='" . $id . "'");
        $rowcount = mysqli_num_rows($result);
        if ($rowcount > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getToken()
    {
        if (isset($_COOKIE['token'])) {
            return $_COOKIE['token'];
        } else {
            return null;
        }
    }

    public function Check($token)
    {

        $result = mysqli_query($this->connect_db(), "SELECT * FROM `account`");
        $row = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $tokenget = $token;
            $tokendata = $this->Token($row['username'], $row['password']);

            if ($tokenget == $tokendata) {
                return $row;
            }
        }

        return null;
    }

    public function post_card($request_id, $telco, $pin, $serial, $amount, $partner_id, $partner_key)
    {

        $partner_id = "2300001209";
        $partner_key = "e0695769614ebb8b89332fab99479d98";

        $data = array(
            'telco' => $telco,
            'code' => $pin,
            'serial' => $serial,
            'amount' => $amount,
            'request_id' => $request_id,
            'partner_id' => $partner_id,
            'sign' => md5($partner_key . $pin . $serial),
            'command' => 'charging',
            'callback_url' => 'https://venus.meliodas.info.vn/callback'
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://gachthefast.com/chargingws/v2?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }


    /***  chuyển đổi 0h:00 phút ngày hôm nay sang dạng timestamp    ***/
    public function time_today()
    {
        $date = date("d-m-Y 00:00");
        $timestamp = strtotime($date);
        return $timestamp;
    }

    /***   xác định mốc thời gian cho trước    ***/
    public function time_ago($time)
    {
        $time_difference = time() - $time;

        if ($time_difference < 1) {
            return "vừa xong";
        }
        $condition = [12 * 30 * 24 * 60 * 60 => "năm", 30 * 24 * 60 * 60 => "tháng", 24 * 60 * 60 => "ngày", 60 * 60 => "giờ", 60 => "phút", 1 => "giây",];

        foreach ($condition as $secs => $str) {
            $d = $time_difference / $secs;

            if ($d >= 1) {
                $t = round($d);
                return $t . " " . $str . ($t > 1 ? "" : "") . " trước";
            }
        }
    }

    /***   lấy url request hiện tại    ***/
    public function PageURL()
    {
        $pageURL = "http";
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }

        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    /***   thu gọn chuỗi    ***/
    public function cat_chuoi($string = "", $size = 100, $link = "...")
    {
        $string = strip_tags(trim($string));
        $strlen = strlen($string);
        $str = substr($string, $size, 20);
        $exp = explode(" ", $str);
        $sum = count($exp);
        $yes = "";
        for ($i = 0; $i < $sum; $i++) {
            if ($yes == "") {
                $a = strlen($exp[$i]);
                if ($a == 0) {
                    $yes = "no";
                    $a = 0;
                }
                if ($a >= 1 && $a <= 12) {
                    $yes = "no";
                    $a;
                }
                if ($a > 12) {
                    $yes = "no";
                    $a = 12;
                }
            }
        }
        $sub = substr($string, 0, $size + $a);
        if ($strlen - $size > 0) {
            $sub .= $link;
        }
        return $sub;
    }

    /***   rewrite text sang dạng url    ***/
    public function rewrite($text)
    {
        $text = html_entity_decode(trim($text), ENT_QUOTES, "UTF-8");
        $text = str_replace(" ", "-", $text);
        $text = str_replace("--", "-", $text);
        $text = str_replace("@", "-", $text);
        $text = str_replace("/", "-", $text);
        $text = str_replace("\\", "-", $text);
        $text = str_replace(":", "", $text);
        $text = str_replace("\"", "", $text);
        $text = str_replace("'", "", $text);
        $text = str_replace("<", "", $text);
        $text = str_replace(">", "", $text);
        $text = str_replace(",", "", $text);
        $text = str_replace("?", "", $text);
        $text = str_replace(";", "", $text);
        $text = str_replace(".", "", $text);
        $text = str_replace("[", "", $text);
        $text = str_replace("]", "", $text);
        $text = str_replace("(", "", $text);
        $text = str_replace(")", "", $text);
        $text = str_replace("́", "", $text);
        $text = str_replace("̀", "", $text);
        $text = str_replace("̃", "", $text);
        $text = str_replace("̣", "", $text);
        $text = str_replace("̉", "", $text);
        $text = str_replace("*", "", $text);
        $text = str_replace("!", "", $text);
        $text = str_replace("$", "-", $text);
        $text = str_replace("&", "-and-", $text);
        $text = str_replace("%", "", $text);
        $text = str_replace("#", "", $text);
        $text = str_replace("^", "", $text);
        $text = str_replace("=", "", $text);
        $text = str_replace("+", "", $text);
        $text = str_replace("~", "", $text);
        $text = str_replace("`", "", $text);
        $text = str_replace("--", "-", $text);
        $text = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $text);
        $text = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $text);
        $text = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $text);
        $text = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $text);
        $text = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $text);
        $text = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $text);
        $text = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $text);
        $text = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $text);
        $text = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $text);
        $text = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $text);
        $text = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $text);
        $text = preg_replace("/(đ)/", "d", $text);
        $text = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $text);
        $text = preg_replace("/(đ)/", "d", $text);
        $text = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $text);
        $text = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $text);
        $text = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $text);
        $text = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $text);
        $text = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $text);
        $text = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $text);
        $text = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $text);
        $text = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $text);
        $text = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $text);
        $text = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $text);
        $text = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $text);
        $text = preg_replace("/(Đ)/", "D", $text);
        $text = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $text);
        $text = preg_replace("/(Đ)/", "D", $text);
        $text = strtolower($text);
        return $text;
    }

    /***   Chuyển chuỗi sang văn bản không có các kí tự    ***/
    public function antil_text($text)
    {
        $text = html_entity_decode(trim($text), ENT_QUOTES, "UTF-8");
        //$text=str_replace(" ","-", $text);
        //$text=str_replace("--","-", $text);
        //$text=str_replace("@","-",$text);
        //$text=str_replace("/","-",$text);
        //$text=str_replace("\\","-",$text);
        $text = str_replace(":", "", $text);
        $text = str_replace("\"", "", $text);
        $text = str_replace("'", "", $text);
        $text = str_replace("<", "", $text);
        $text = str_replace(">", "", $text);
        $text = str_replace(",", "", $text);
        $text = str_replace("?", "", $text);
        $text = str_replace(";", "", $text);
        $text = str_replace(".", "", $text);
        $text = str_replace("[", "", $text);
        $text = str_replace("]", "", $text);
        $text = str_replace("(", "", $text);
        $text = str_replace(")", "", $text);
        $text = str_replace("́", "", $text);
        $text = str_replace("̀", "", $text);
        $text = str_replace("̃", "", $text);
        $text = str_replace("̣", "", $text);
        $text = str_replace("̉", "", $text);
        $text = str_replace("*", "", $text);
        $text = str_replace("!", "", $text);
        //$text=str_replace("$","-",$text);
        //$text=str_replace("&","-and-",$text);
        $text = str_replace("%", "", $text);
        $text = str_replace("#", "", $text);
        $text = str_replace("^", "", $text);
        $text = str_replace("=", "", $text);
        $text = str_replace("+", "", $text);
        $text = str_replace("~", "", $text);
        $text = str_replace("`", "", $text);
        //$text=str_replace("--","-",$text);
        $text = strtolower($text);
        return $text;
    }
    /***   kiểm ra chuỗi con có trong chuỗi mẹ hay không    ***/
    public function tim_chuoi($str, $chuoi)
    {
        if (strpos($str, $chuoi) !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function dectect_tiengviet($string)
    {
        $tiengviet = ["à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ", "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ", "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ", "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ", "đ", "Đ", "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ", "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ", "ì", "í", "ị", "ỉ", "ĩ", "Ì", "Í", "Ị", "Ỉ", "Ĩ", "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ", "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ", "ỳ", "ý", "ỵ", "ỷ", "ỹ", "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",];

        foreach ($tiengviet as $key) {
            if ($this->tim_chuoi($string, $key) == true) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function compact_string($string, $length = 5, $replace)
    {
        $compact = substr($string, 0, $length);
        $compact = $compact . $replace;
        return $compact;
    }

    /***  kiểm tra chuỗi có phải dạng số hay không    ***/
    public function check_int($data)
    {
        if (is_int($data) === true) {
            return true;
        }
        if (is_string($data) === true && is_numeric($data) === true) {
            return strpos($data, ".") === false;
        }
    }

    /***   xác định mốc thời gian từ dạng ngày tháng    ***/
    public function timeAgo($timestamp)
    {
        $currentTime = time();
        $timeDifference = $currentTime - $timestamp;

        $seconds = $timeDifference;
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);

        if ($seconds <= 60) {
            return "Vừa xong";
        } else if ($minutes <= 60) {
            return "$minutes phút trước";
        } else if ($hours <= 24) {
            return "$hours giờ trước";
        } else if ($days <= 7) {
            return "$days ngày trước";
        } else {
            return date("d-m-Y", $timestamp);
        }
    }


    /***   hàm Craw hoặc Post dữ liệu sử dụng CUrl   ***/
    public function curl($url, $data)
    {
        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "");
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $page = curl_exec($ch);
        curl_close($ch);
        return $page;
    }

    /***   kiểm tra thiết bị đang request có phải điện thoại hay không - copy từ mã nguồn wordpress   ***/
    public function is_mobile()
    {
        if (empty($_SERVER["HTTP_USER_AGENT"])) {
            $is_mobile = false;
        } elseif (
            strpos($_SERVER["HTTP_USER_AGENT"], "Mobile") !== false || // many mobile devices (all iPhone, iPad, etc.)
            strpos($_SERVER["HTTP_USER_AGENT"], "Android") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Silk/") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Kindle") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "BlackBerry") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Opera Mini") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Opera Mobi") !== false
        ) {
            $is_mobile = true;
        } else {
            $is_mobile = false;
        }

        return $is_mobile;
    }

    /***   rút gọn chuỗi    ***/
    public function cut_str($str, $max)
    {
        $str = trim($str);
        if (strlen($str) > $max) {
            $s_pos = strpos($str, " ");
            $cut = $s_pos === false || $s_pos > $max;
            $str = wordwrap($str, $max, ";;", $cut);
            $str = explode(";;", $str);
            $str = $str[0] . "...";
        }
        return $str;
    }

    /***   tạo ra chuỗi ngẫu nhiên gồm cả số và chữ (tạo token)    ***/
    public function generateToken()
    {
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        return $token;
    }


    public function Upanh($image_path, $file_type)
    {
        $client_id = 'c957f559086c4ea';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            array(
                'image' => base64_encode(file_get_contents($image_path)),
                'type' => $file_type
                // 'image' => curl_file_create($image_path, $file_type),
                // 'type' => 'file',
                // 'disable_conversion' => true,
            )
        );
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        return $response['data']['link'];
    }

    public function FormatString($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    }

    public function Ex($status = false, $message = '', $data = [])
    {
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function LimitString($string, $min, $max)
    {
        if (strlen($string) > $max || strlen($string) < $min) {
            return false;
        } else {
            return true;
        }
    }
    public function Token($username, $password)
    {
        $salt = 'VENUS2025';
        $hash = md5($username . $salt . $password . $salt . 'nrovenusieucapvutruadminmeliodasdeptraivaicalonhehe');
        return $hash;
    }

    public function addBuy($id, $uid, $time, $status){
        $sql = "SELECT * FROM cvh_sell_item WHERE id = $id";
        $result = $this->query($sql);
    
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $users_buy = json_decode($row["users_buy"], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($users_buy)) {
                $new_user_buy = array(
                    
                    "uid" => $uid,
                    "status" => $status,
                    "time" => time()
                );
                array_unshift($users_buy, $new_user_buy);
                $userbuy = json_encode($users_buy, JSON_UNESCAPED_UNICODE);
                $updateSql = "UPDATE cvh_sell_item SET users_buy = '$userbuy' WHERE id = $id";
                $cvhvn = $this->query($updateSql);
    
                return $cvhvn === true;
            }
        }
    
        return false;
    }



    public function addCMT($post_id, $noidung, $player)
    {
        $sql = "SELECT comments FROM cvh_baiviet WHERE id = $post_id";
        $result = $this->query($sql);
    
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $comments = json_decode($row["comments"], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($comments)) {
                $newComment = ["account_id" => $player, "noidung" => $noidung, "time" => time()];
                array_unshift($comments, $newComment);
                $jsonComments = json_encode($comments, JSON_UNESCAPED_UNICODE);
                $updateSql = "UPDATE cvh_baiviet SET comments = '$jsonComments' WHERE id = $post_id";
                $cvhvn = $this->query($updateSql);
    
                return $cvhvn === true;
            }
        }
    
        return false;
    }
    
    


    public function xss($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        $ducthanhit = htmlspecialchars(addslashes(trim($data)));

        return $ducthanhit;
    }

    /***   hàm phân trang khi gọi dữ kiệu config tại index  - Copy từ Hoàng Minh Thuận ***/
    function phantrang($url, $start, $total, $kmess) {
        $out[] = '<ul class="pagination pagination-sm mb-0 ">';
        $neighbors = 2;
        if ($start >= $total) $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        else $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        $base_link = '<li class="page-item"><a class="page-link" href="' . strtr($url, array('%' => '%%')) . 'page=%d' . '"> %s </a></li>';
        $out[] = $start == 0 ? '' : sprintf($base_link, $start / $kmess, '&lt;');
        if ($start > $kmess * $neighbors) $out[] = sprintf($base_link, 1, '1');
        if ($start > $kmess * ($neighbors + 1)) $out[] = '<li class="page-item"><a class="page-link" href="#">...</a></li>';
        for ($nCont = $neighbors;$nCont >= 1;$nCont--) if ($start >= $kmess * $nCont) {
            $tmpStart = $start - $kmess * $nCont;
            $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
        }
        $out[] = '<li class="page-item active"><a class="page-link">' . ($start / $kmess + 1) . '</a></li>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;
        for ($nCont = 1;$nCont <= $neighbors;$nCont++) if ($start + $kmess * $nCont <= $tmpMaxPages) {
            $tmpStart = $start + $kmess * $nCont;
            $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
        }
        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages) $out[] = '<li class="page-item"><a class="page-link" href="#">...</a></li>';
        if ($start + $kmess * $neighbors < $tmpMaxPages) $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess + 1);
        if ($start + $kmess < $total) {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf($base_link, $display_page, '&gt;');
        }
        $out[] = '</ul>';
        return implode('', $out);
    
    }

    // CSRF Token functions using cvh_sessions table
    public function generateCSRFToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $conn = $this->connect_db();
        
        // Xóa token cũ của user này
        $stmt = $conn->prepare("DELETE FROM cvh_sessions WHERE user_id = ? AND token LIKE 'csrf_%'");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Thêm token mới
        $stmt = $conn->prepare("INSERT INTO cvh_sessions (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
        $csrf_token = 'csrf_' . $token;
        $stmt->bind_param('iss', $user_id, $csrf_token, $expires_at);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result ? $csrf_token : false;
    }
    
    public function validateCSRFToken($user_id, $token) {
        if (empty($token) || empty($user_id)) {
            return false;
        }
        
        $conn = $this->connect_db();
        $stmt = $conn->prepare("SELECT id FROM cvh_sessions WHERE user_id = ? AND token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->bind_param('is', $user_id, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $valid = $result && $result->num_rows > 0;
        $stmt->close();
        
        return $valid;
    }
    
    public function getCSRFToken($user_id) {
        $conn = $this->connect_db();
        $stmt = $conn->prepare("SELECT token FROM cvh_sessions WHERE user_id = ? AND token LIKE 'csrf_%' AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['token'];
        }
        
        $stmt->close();
        return $this->generateCSRFToken($user_id);
    }
    
    public function cleanupExpiredCSRFTokens() {
        $conn = $this->connect_db();
        $stmt = $conn->prepare("DELETE FROM cvh_sessions WHERE token LIKE 'csrf_%' AND expires_at <= NOW()");
        $stmt->execute();
        $stmt->close();
    }
    





}

/**
 * Lấy đường dẫn icon cho item dựa trên icon_id
 * @param int $icon_id ID của icon trong database
 * @return string Đường dẫn đến file ảnh
 */
function getItemIcon($icon_id) {
    // Nếu không có icon_id hoặc icon_id = 0, trả về icon mặc định
    if (empty($icon_id) || $icon_id == 0) {
        return 'https://cdn-icons-png.flaticon.com/128/3144/3144484.png';
    }
    
    // Đường dẫn thư mục game icon
    $game_icon_path = "C:/Users/Administrator/Desktop/VENUS/data/girlkun/icon/x4";
    
    // Thử các định dạng file khác nhau
    $icon_formats = ['png', 'jpg', 'jpeg', 'gif'];
    
    // 1. Kiểm tra trong thư mục game trước (ưu tiên cao nhất)
    foreach ($icon_formats as $format) {
        $game_file = $game_icon_path . "/{$icon_id}.{$format}";
        if (file_exists($game_file)) {
            // Tạo symlink hoặc copy file để web có thể truy cập
            $web_path = "/images/game_icons/{$icon_id}.{$format}";
            $web_full_path = $_SERVER['DOCUMENT_ROOT'] . $web_path;
            
            // Tạo thư mục nếu chưa có
            $web_dir = dirname($web_full_path);
            if (!is_dir($web_dir)) {
                mkdir($web_dir, 0755, true);
            }
            
            // Copy file từ game sang web (nếu chưa có)
            if (!file_exists($web_full_path)) {
                copy($game_file, $web_full_path);
            }
            
            return $web_path;
        }
    }
    
    // 2. Kiểm tra trong thư mục web
    $web_icon_paths = [
        "/images/items/{$icon_id}.png",
        "/images/items/{$icon_id}.jpg", 
        "/images/items/{$icon_id}.jpeg",
        "/images/items/{$icon_id}.gif",
        "/images/{$icon_id}.png",
        "/images/{$icon_id}.jpg",
        "/images/{$icon_id}.jpeg", 
        "/images/{$icon_id}.gif"
    ];
    
    foreach ($web_icon_paths as $path) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
        if (file_exists($full_path)) {
            return $path;
        }
    }
    
    // Nếu không tìm thấy file, trả về icon mặc định
    return 'https://cdn-icons-png.flaticon.com/128/3144/3144484.png';
}