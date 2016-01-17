var restify = require('restify'),
    server = restify.createServer({
        name: 'Hello World'
    });

server.use(restify.gzipResponse());

var respond = function (req, res, next) {
    res.contentType = "text/plain";
    res.send(200, 'hello world');
    next();
};

var port = 80;

server.get('/', respond);
server.listen(port);

console.log('Starting Restify on port: ' + port);
