var http = require('http');

const PORT=80;

var server = http.createServer(function (request, response){
    response.end('hello world');
});

server.listen(PORT, function() {
    console.log("Server listening on: http://localhost:%s", PORT);
});