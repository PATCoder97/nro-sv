<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
if (empty($user) || empty($user['is_admin'])) {
    http_response_code(403);
    exit('Forbidden');
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    exit;
}
$output = '';
if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($CVH->connect_db(), $_POST['query']);

    if (empty($search)) {
        $query = "SELECT player.*, account.* 
                  FROM player 
                  INNER JOIN account ON player.account_id = account.id
                  ORDER BY account.id DESC";
        $result = mysqli_query($CVH->connect_db(), $query);
    } else {

        $columns = array('player.name', 'account.vnd', 'account.username', 'account.id', 'account.ip_address');
        $search_query = "SELECT player.*, account.* 
                     FROM player 
                     INNER JOIN account ON player.account_id = account.id
                     WHERE ";
        foreach ($columns as $column) {
            $search_query .= $column . " LIKE '%$search%' OR ";
        }
        $search_query = rtrim($search_query, "OR ");
        $search_query .= " ORDER BY account.id DESC";

        $result = mysqli_query($CVH->connect_db(), $search_query);
    }
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $output .= '<tr class="search-items">
                            <td>
                                        <div class="d-flex align-items-center">
                                            <div class="w-30px h-30px">
                                                <img src="/images/avatar/'. $row["head"].'.png" alt=""
                                                    class="ms-100 mh-100">
                                            </div>
                                            <div class="ms-3 flex-grow-1">
                                                <div class="fw-600 text-body">Tên:
                                                    '. $row["name"].'</div>
                                                <div class="fs-13px">TK:
                                                    '. $row["username"].'</div>
                                            </div>
                                        </div>
                                    </td>
                            <td>
                                <span class="usr-email-addr">
                                    ' . number_format($row["vnd"]) . 'đ
                                </span>
                            </td>
                            <td>
                                <span class="usr-location">
                                    ' . getBand($row["ban"]) . '
                                </span>
                            </td>
                            <td>
                                <span class="usr-ph-no">
                                    ' . getActive($row["active"]) . '
                                </span>
                            </td>
                            <td>
                                <span class="usr-ph-no">
                                    ' . $row["ip_address"] . '
                                </span>
                            </td>
                            <td>
                                <span class="usr-ph-no">
                                    ' . $row["create_time"] . '
                                </span>
                            </td>
                            <td>
                                <div class="action-btn">
                                    <a href="javascript:void(0)" onclick="del_(' . $row['account_id'] . ');" class="btn btn-sm btn-danger delete ms-2">
                                        <i class="fa fa-trash-alt fs-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>';
        }
    } else {
        $output .= '<tr class="text-center py-3">
        <td colspan="8">
            <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png"
                width="50" class="img-fluid">
            <p class="pt-3"><b>Không có dữ liệu</b></p>
        </td>
    </tr>';
    }
    echo $output;
}
?>
