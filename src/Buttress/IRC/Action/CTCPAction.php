<?php
/**
 * Created by PhpStorm.
 * User: korvin
 * Date: 11/12/14
 * Time: 6:17 PM
 */

namespace Buttress\IRC\Action;

use Buttress\IRC\Connection\ConnectionInterface;
use Buttress\IRC\Message\GenericMessage;
use Buttress\IRC\Message\MessageInterface;
use Buttress\IRC\Message\PrivmsgMessage;

class CTCPAction implements ActionInterface
{

    protected $version;

    function __construct($version = 'buttress/irc v1.0.0')
    {
        $this->version = $version;
    }

    /**
     * Handle Connection
     *
     * @param ConnectionInterface $connection
     * @return mixed
     */
    public function handleConnect(ConnectionInterface $connection)
    {
        return;
    }

    /**
     * Handle messages
     *
     * @param MessageInterface $message
     * @return void
     */
    public function handleMessage(MessageInterface $message)
    {
        if ($message instanceof PrivmsgMessage) {
            $string = $message->getMessage();
            if (substr($string, 0, 1) === "\001") {
                $string = trim($string, " \001\n\t");

                list($type,) = array_pad(explode(' ', $string, 2), 2, '');
                list($nick, $user, $host) = $message->getUser();

                $message->getConnection()->log("CTCP \"{$type}\" from {$nick}!{$user}@{$host}", array($message));

                $params = array();
                switch ($type) {
                    case 'VERSION':
                        $params = array($nick, "\001VERSION {$this->version}\001");
                        break;
                    case 'PING':
                        $params = array($nick, $message->getMessage());
                        break;
                    case 'TIME':
                        $time = date(DATE_RFC1123);
                        $params = array($nick, "\001TIME {$time}\001");
                        break;
                }
                if ($params) {
                    $response = new GenericMessage('notice', '', $params);
                    $message->getConnection()->sendMessage($response);
                }
            }
        }
    }

}
