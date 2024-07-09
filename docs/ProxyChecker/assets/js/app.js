function parse_proxies() {

    document.getElementById("result").innerHTML = '';        
    var proxies = document.getElementById("proxies").value;

    proxies = proxies.split("\n");
    proxies.forEach(check);
}

function check(proxy) {
    var proxy = proxy.split(":");
    var ip = proxy[0];
    var port = proxy[1];

    var result = document.getElementById("result");

    var url = document.getElementById("url").value;
    var post = "ip=" + ip + "&port=" + port + "&url=" + url;

    var query;

    if(window.XMLHttpRequest) {
        query = new XMLHttpRequest;
    }

    query.onreadystatechange = function() {
        if(query.readyState == 4 && query.status == 200) {
            result.innerHTML += query.responseText;
        }
    }

    query.open("POST", "proxy.php", true);
    query.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    query.send(post);
}