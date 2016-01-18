# Web Framework Benchmarks

### Starting frameworks

```sh
$ ./run.php start
php5-raw running successfully at: http://localhost:8091
php5-lumen running successfully at: http://localhost:8092
php5-zend running successfully at: http://localhost:8093
nodejs-express running successfully at: http://localhost:8094
nodejs-restify running successfully at: http://localhost:8095
nodejs-raw running successfully at: http://localhost:8096
```

### Running benchmarks

```sh
$ ./run.php benchmark
Doing 100 calls per framework. Displaying averages.
php5-raw:           0.43085ms
php5-lumen:         1.96617ms
php5-zend:          9.50206ms
nodejs-express:     0.73769ms
nodejs-restify:     0.58084ms
nodejs-raw:         0.36937ms
```

### Stopping frameworks
```sh
$ ./run.php stop
Stopping php5-raw (fcd861c7bafc)...
Stopping php5-lumen (649ac7ac7175)...
Stopping php5-zend (6df23f22dfda)...
Stopping nodejs-express (df61541d8a0d)...
Stopping nodejs-restify (30778a2906f0)...
Stopping nodejs-raw (cc91fa1c1ddd)...
```
