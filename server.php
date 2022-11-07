<?php declare(strict_types=1,ticks=1);

// Binding socket
$port = 80;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$socketBound = socket_bind($socket,'0.0.0.0', $port) or  die("Can't bind socket\n");
$socketListens = socket_listen($socket,0) or die("Failed to listen on the socket \n");

// Some error handling on CTRL+C
$signalHandler = function() use($socket) {
    echo "\nClosing socket\n";
    socket_close($socket);
    exit(1);
};
pcntl_signal(SIGINT,$signalHandler);

// Taking off!
echo "Listening on 0.0.0.0:$port\n";
while(true){
    // Nonblocking waiting
    $read = [$socket];
    $gotSomething = socket_select($read,$write,$except,0,100);
    if(!$gotSomething){
        continue;
    }

    // Request processing
    $incoming = socket_accept($socket);
    echo "Got incoming connection: \n";
    $input = socket_read($incoming, 30000) or die("Could not read input\n");
    print_r($input);
    $content = "<b>Hello TCP/IP!</b>";
    $contentLength = mb_strlen($content);
    $date =  gmdate("D, d M Y H:i:s", time())." GMT";
    $header = [];
    $header[] = "HTTP/1.0 200 OK";
    $header[] = "Date: $date";
    $header[] = "Server: Go Fuck Yourself Server";
    $header[] = "Content-Length: $contentLength";
    $header[] = "Content-Type: text/html";
    $response = join("\r\n",$header)."\r\n\r\n".$content;
    echo "Response: \n";
    print_r($response);
    echo "\n";
    socket_write($incoming,$response) or die("Couldn't write output\n");
    socket_close($incoming);
}