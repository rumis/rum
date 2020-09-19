## Rum 

Rum是基于Swoole实现的高性能、自由扩展的PHP协程框架。

## Hello Rum

    <?php

    include_once('vendor/autoload.php');

    use Rum;
    use Rum\Request;
    use Rum\Response;

    $app = new Rum\Application([]);
    $app->get('/', function (Request $req, Response $res) {
        $res->string('Hello Rum');
    });

    $app->run(8000);
