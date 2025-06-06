<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tra cứu giao dịch</title>
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet" />
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
</head>

<body>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted">VNPAY DEMO</h3>
    </div>
    <div style="width: 100%;padding-top:0px;font-weight: bold;color: #333333">
        <h3>Querydr</h3>
    </div>
    <div style="width: 100%; border-bottom: 2px solid black; padding-bottom: 20px">
        <form action="/vnpay_php/vnpay_querydr.php" id="frmCreateOrder" method="post">
            <div class="form-group">
                <label for="txnRef">Mã GD thanh toán cần quy vấn (vnp_TxnRef):</label>
                <input class="form-control" id="txnRef" name="txnRef" type="text" value="" />
            </div>
            <div class="form-group">
                <label for="transactionDate">Thời gian khởi tạo GD thanh toán (vnp_TransactionDate):</label>
                <input class="form-control" id="transactionDate" name="transactionDate" type="text" placeholder="yyyyMMddHHmmss" value="" />
            </div>
            <input type="submit" class="btn btn-default" value="Querydr" />
        </form>
    </div>

    <?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    error_reporting(E_ALL & E_NOTICE & E_DEPRECATED);
    require_once "config_vnpay.php";

    function callApiAuth($method, $url, $data)
    {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $vnp_RequestId = rand(1, 10000);
        $vnp_Command = "querydr";
        $vnp_TxnRef = $_POST["txnRef"];
        $vnp_OrderInfo = "Query transaction";
        $vnp_TransactionDate = $_POST["transactionDate"];
        $vnp_CreateDate = date('YmdHis');
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $datarq = array(
            "vnp_RequestId" => $vnp_RequestId,
            "vnp_Version" => "2.1.0",
            "vnp_Command" => $vnp_Command,
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_TransactionDate" => $vnp_TransactionDate,
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_IpAddr" => $vnp_IpAddr
        );

        $format = '%s|%s|%s|%s|%s|%s|%s|%s|%s';

        $dataHash = sprintf(
            $format,
            $datarq['vnp_RequestId'],
            $datarq['vnp_Version'],
            $datarq['vnp_Command'],
            $datarq['vnp_TmnCode'],
            $datarq['vnp_TxnRef'],
            $datarq['vnp_TransactionDate'],
            $datarq['vnp_CreateDate'],
            $datarq['vnp_IpAddr'],
            $datarq['vnp_OrderInfo']
        );

        $checksum = hash_hmac('SHA512', $dataHash, $vnp_HashSecret);
        $datarq["vnp_SecureHash"] = $checksum;

        $txnData = callApiAuth("POST", $apiUrl, json_encode($datarq));
        $ispTxn = json_decode($txnData, true);

        echo "
        <div>
        <label>API Response:</label>
        <pre>
$txnData
        </pre>
        </div>";
    }
    ?>
</div>
</body>
</html>
