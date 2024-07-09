<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxy Checker</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">HTTP Proxy Checker</h1>

        <h3>URL to Test (Optional)</h3>
        <input class="form-control" type="text" id="url" placeholder="https://www.youtube.com/watch?v=2vjPBrBU-TM">

        <h3 class="mt-3">Proxylist IP:PORT</h3>
        <textarea name="proxies" id="proxies" style="width:100%; height: 200px;" placeholder="127.0.0.1:3128 &#x0a;127.0.0.2:8080&#x0a;127.0.0.3:3333"></textarea>
        <button class="btn btn-primary btn-lg btn-block" onclick="parse_proxies()">Check</button>

        <table class="table mt-4 text-center">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Proxy</th>
                    <th scope="col">URL</th>
                    <th scope="col">Response time</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody id="result">
                
            </tbody>
        </table>

    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>