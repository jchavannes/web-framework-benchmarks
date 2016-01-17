# Web Framework Benchmarks

### Starting frameworks
```sh
$ frameworks/docker.sh
Building php5-raw...
php5-raw running successfully at: http://localhost:8091
Building php5-lumen...
php5-lumen running successfully at: http://localhost:8092
Building php5-zend...
php5-zend running successfully at: http://localhost:8093
Building nodejs-express...
nodejs-express running successfully at: http://localhost:8094
Building nodejs-restify...
nodejs-restify running successfully at: http://localhost:8095
Building nodejs-raw...
nodejs-raw running successfully at: http://localhost:8096
```

### Running benchmarks
```sh
$ php benchmark.php 
php-raw...
  namelookup_time:    0.01272ms
  connect_time:       0.0169ms
  pretransfer_time:   0.15021ms
  starttransfer_time: 0.44369ms
  > total_time:       0.45834ms
php-lumen...
  namelookup_time:    0.0154ms
  connect_time:       0.12485ms
  pretransfer_time:   0.21657ms
  starttransfer_time: 1.59991ms
  > total_time:       1.62368ms
php-zend...
  namelookup_time:    0.02763ms
  connect_time:       0.03064ms
  pretransfer_time:   0.13696ms
  starttransfer_time: 8.76788ms
  > total_time:       8.7952ms
nodejs-express...
  namelookup_time:    0.01175ms
  connect_time:       0.01408ms
  pretransfer_time:   0.03829ms
  starttransfer_time: 0.4771ms
  > total_time:       0.49089ms
nodejs-restify...
  namelookup_time:    0.01374ms
  connect_time:       0.01678ms
  pretransfer_time:   0.09767ms
  starttransfer_time: 0.48583ms
  > total_time:       0.49847ms
nodejs-raw...
  namelookup_time:    0.01026ms
  connect_time:       0.01282ms
  pretransfer_time:   0.0362ms
  starttransfer_time: 0.28495ms
  > total_time:       0.29861ms
```

### Stopping frameworks
```sh
$ frameworks/docker.sh -s
== Stop only ==
Stopping php5-raw (afb0419db67c)...
Stopping php5-lumen (aa3010118c9a)...
Stopping php5-zend (552422073fb7)...
Stopping nodejs-express (806b08c218f9)...
Stopping nodejs-restify (004998dab116)...
Stopping nodejs-raw (2ce025034a75)...
```
