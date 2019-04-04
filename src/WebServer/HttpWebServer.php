<?php
namespace SoerBot\WebServer;
    //composer require react/http:^0.8.4
    //composer require react/socket:^1.2
    use Psr\Http\Message\ServerRequestInterface;
    use React\EventLoop\Factory;
    use React\Http\Response;
    use React\Http\Server;
    use React\Socket\Server as Socket;

class HttpWebServer
{
    private $loop; 
    private $port;
    private $requestHandlers;

    private $server;
    private $socket;

    /**
     * readonly implements, thanks to:
     * - https://ttmm.io/tech/php-read-attributes/
     * 
     * Magic getter for our object.
     *
     * @param string $field
     * @throws UnexpectedValueException Throws an exception if the field is invalid.
     * @return mixed
     */
    public function __get( $field ) {
        switch( $field ) {
        case 'port':
            return $this->port;
        case 'server':
            return $this->server;
        default:
            $class = __CLASS__;
            throw new UnexpectedValueException( "Invalid property: {$class}->{$field}");
        }
    }

    /**
     * Constrictor: 
     * - create instance of this class
     *   - with paremeters ready to start new instance of server
     * 
     * @param $aLoop application's work loop, not started yet
     *  - instance of React\EventLoop\LoopInterface
     * @param $aPort listening port 
     *  - int 0..9999
     * @param $aRequestHandlers array of reguest handlers middleware, 
     *  - function() {{}
     *  - see examples at https://sergeyzhuk.me/2018/03/13/using-router-with-reactphp-http/
     * @throws UnexpectedValueException
     * @return HttpWebServer instance of
     */
    public function __construct($aLoop, $aPort, $aRequestHandlers)
    {
        if (!($aLoop instanceof \React\EventLoop\LoopInterface))
            throw new UnexpectedValueException();
        if (!\is_int($aPort) || $aPort<0 || $aPort>9999)
            throw new UnexpectedValueException();
        if (!\is_callable($aRequestHandlers) && !\is_array($aRequestHandlers))
            throw new UnexpectedValueException();

        $this->loop = $aLoop;
        $this->port = $aPort;
        $this->requestHandlers = $aRequestHandlers;
    }
    
    /**
     * StartServer: 
     * - starting server for listening defined(in constructor) port.
     * - the server will react on reguests with defined(in constructor) reguests handlers.
     * - probably should be started before application's work loop.Run()
     *  @return HttpWebServer class instance itself , with already started server
     */
    public function startServer(): HttpWebServer
    {
        $this->server = new Server($this->requestHandlers);
        $this->socket = new Socket($this->port, $this->loop);
        $this->server->listen( $this->socket);

        return $this;
    }
    
    /**
     * Create and Start Server
     * 
     * - Constrictor: 
     *   - create instance of this class
     * - StartServer: 
     *   - starting server for listening defined(in constructor) port.
     *   - the server will react on reguests with defined(in constructor) reguests handlers.
     *   - probably should be started before application's work loop.Run()
     * @param $aLoop application's work loop, not started yet
     *  - instance of React\EventLoop\LoopInterface
     * @param $aPort listening port 
     *  - int 0..9999
     * @param $aRequestHandlers array of reguest handlers middleware, 
     *  - function() {{}
     *  - see examples at https://sergeyzhuk.me/2018/03/13/using-router-with-reactphp-http/
     * @throws UnexpectedValueException
     * @return HttpWebServer class instance itself , with already started server
     */
    public static function createServer($aLoop, $aPort, $aRequestHandlers): HttpWebServer
    {
        $theHttpWebServer = new HttpWebServer($aLoop, $aPort, $aRequestHandlers);
        $theHttpWebServer->startServer();

        return $theHttpWebServer;
    }
}