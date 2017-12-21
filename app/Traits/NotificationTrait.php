<?php namespace CVClient\Traits;

/*
use Gomoob\Pushwoosh\Client\Pushwoosh;
use Gomoob\Pushwoosh\Model\Notification\Notification;
use Gomoob\Pushwoosh\Model\Request\CreateMessageRequest;
use Gomoob\Pushwoosh\Model\Notification\Android;
*/

use DB;
use ElephantIO\Client,
    ElephantIO\Engine\SocketIO\Version1X;

trait NotificationTrait {

    /*
	function sendNotification($message, $destiny = [], $custom_data = []){

    	$pushwoosh = Pushwoosh::create()
    		->setApplication('C3B38-8D081')
    		->setAuth('C5eyQKx01skAdNreBMzfUptFMNPHaB5JJvcjclmrG0M4UwfE7o107gF1xFNhlKfncWIQzyMPhY4c3yPaO1a4');

    	$notification = Notification::create()->setContent($message)->setSendDate('now');

    	if(!empty($custom_data))
    		$notification->setData($custom_data);

    	if(!empty($destiny))
    		$notification->setDevices($destiny);

    	$request = CreateMessageRequest::create()
    				->addNotification($notification);

    	return $pushwoosh->createMessage($request);
	}
    */

    /**
     * Send message through websocket
     */
    function sendWSMessage($to, $params){

        // Save in DB
        try {
            $id = DB::table('notificacion')->insertGetId([
                'to' => $to,
                'params' => json_encode($params),
                'read' => 0
            ]);

            $params['id'] = $id;
            $params['read'] = 0;

            $client = new Client(new Version1X( env('NOTIFICATION_SERVER') ));
            $client->initialize();
            $destiny = ($to=='C'?'toClient':'emitSystem');
            $client->emit($destiny, $params);
            $client->close();   
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}