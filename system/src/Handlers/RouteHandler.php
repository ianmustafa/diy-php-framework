<?php

namespace System\Handlers;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

class RouteHandler {
    protected $dispatcher;

    public function __construct(string $routerFilePath)
    {
        $this->dispatcher = new Dispatcher($this->getRoutes($routerFilePath));
    }

    /**
     * Ambil list route yang dari route file yang dipilih.
     *
     * @param string $routerFilePath
     * @return array
     */
    protected function getRoutes(string $routerFilePath): array
    {
        // Instansiasikan RouteCollector
        $routeCollector = new RouteCollector(new RouteParser, new DataGenerator);
        // Buat callback yang bertugas mengambil route list dari route file,
        // menggunakan RouteCollector sebagai parameter-nya
        call_user_func(function (RouteCollector $router) use ($routerFilePath) {
            include $routerFilePath;
        }, $routeCollector);

        return $routeCollector->getData();
    }

    /**
     * Lakukan parsing terhadap request untuk mengambil
     * URI dan HTTP method yang digunakan.
     *
     * @return array
     */
    protected function requestParser(): array
    {
        // Ambil HTTP method dan URI-nya
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Hapus query string dan decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        return [$httpMethod, $uri];
    }

    /**
     * Jalankan routing-nya.
     *
     * @param array $options
     * @return void
     */
    public function dispatch(array $options = []): void
    {
        // Gunakan opsi berikut jika opsi tidak diisi
        $options += [
            'namespacePrefix' => 'App\\Controllers',
        ];

        // Ambil info dari route yang berhasil di-parsing
        $routeInfo = $this->dispatcher->dispatch(...$this->requestParser());
        // Lakukan aksi berikut sesuai dengan info yang didapat
        switch ($routeInfo[0]) {
            // Handler ketika route yang dituju tidak ditemukan
            case Dispatcher::NOT_FOUND:
                echo '404 Not Found';
                break;

            // Handler ketika route yang dituju sudah ditemukan,
            // tapi HTTP method yang dipakai tidak sesuai
            case Dispatcher::METHOD_NOT_ALLOWED:
                // Ambil HTTP method yang diharapkan
                $expectedMethods = $routeInfo[1];
                echo "405 Method Not Allowed\nExpected methods: ";
                echo implode(',', $expectedMethods);
                break;

            // Handler ketika route sudah sesuai
            case Dispatcher::FOUND:
                // Ekstrak handler dan parameter-nya dari routeInfo
                [, $handler, $vars] = $routeInfo;
                
                // Jika handler berupa string, konversikan ke Fully-Qualified Class Name
                // dan sekaligus pisahkan nama class dan method berdasarkan tanda '@'
                if (is_string($handler)) {
                    $handler = explode('@', "{$options['namespacePrefix']}\\{$handler}");
                    // Ubah $handler[0] menjadi instance dari class
                    // yang akan dipakai sebagai handler
                    $handler[0] = new $handler[0];
                }

                // Jalankan handler-nya
                call_user_func_array($handler, $vars);
                break;
        }
    }
}