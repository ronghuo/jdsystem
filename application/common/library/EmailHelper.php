<?php
namespace app\common\library;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use app\common\library\Mylog;

class EmailHelper{

    public static function sendErrorMessage($messages){

        $emails = config('app.get_error_emails');
        $data = [
            'subject'=>'JD system error',
            'body'=>print_r($messages, 1),
            'to'=>$emails
        ];

//        print_r($data);
        self::send($data);
    }

    public static function send($data){

//        Mylog::write([
//            'send',
////            $data
//        ], 'email_hepler');
        //['base_uri' => 'http://service.88ljsm.com/works/']
        try{

            $client = new Client();
            $promise = $client->postAsync('http://service.88ljsm.com/works/email/send2', [
                'body'=>\GuzzleHttp\json_encode($data)
            ]);
            //Mylog::write(var_dump($promise), 'email_hepler');
            $promise->then(
                function (ResponseInterface $res) {
//                echo $res->getStatusCode() . "\n";
//                Mylog::write([
//                    'success',
//                    $res->getStatusCode(),
//                    $res->getBody()
//                ], 'email_hepler');
                },
                function (RequestException $e) {
                    Mylog::write([
                        'error',
                        $e->getMessage(),
                        $e->getRequest()->getMethod()
                    ], 'email_helper');
//                echo $e->getMessage() . "\n";
//                echo $e->getRequest()->getMethod();
                }
            );


            $promise->wait();

        }catch (\Exception $e){
            Mylog::write([
                'Exception',
                $e->getMessage()
            ], 'email_helper');
        }

    }

}