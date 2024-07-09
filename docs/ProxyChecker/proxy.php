<?php 

    $url = ($_POST['url']) ? trim($_POST['url']) : 'https://www.youtube.com/watch?v=2vjPBrBU-TM';
    $ip = ($_POST['ip']) ? trim($_POST['ip']) : '';
    $port = ($_POST['port']) ? trim($_POST['port']) : '';

    $proxy = $ip . ':' . $port;

 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_REFERER, '');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
    $result = curl_exec($ch);  
    $status = curl_getinfo($ch);

    echo "<tr>";
        echo "<td>" . $proxy . "</td>";
        echo "<td>" . $url . "</td>";
        if(curl_error($ch)) {
            echo "<td>" . round($status['total_time'], 2) . " seconds</td>";
            echo "<td style='color: red;'>HTTP " . $status['http_code'] . " Dead</td>";
        } else {
            if($status['http_code'] == 429) {
                echo "<td>" . round($status['total_time'], 2) . " seconds</td>";
                echo "<td style='color: red;'>HTTP " . $status['http_code'] . " Banned</td>";
            } elseif($status['http_code'] == 200) {
                echo "<td>" . round($status['total_time'], 2) . " seconds</td>";
                echo "<td style='color: green;'>HTTP " . $status['http_code'] . " OK</td>";
            } else {
                echo "<td>" . round($status['total_time'], 2) . " seconds</td>";
                echo "<td style='color: orange;'>HTTP " . $status['http_code'] . "</td>";
            }
        } 
    echo "</tr>";    
    curl_close($ch); 
    
?>