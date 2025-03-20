<?php

namespace App\Actions;

/**
 * MessageActionBase Class
 *
 * This base class is designed to facilitate the addition of different types of messages, such as warnings,
 * errors, success notifications, and notices, which are intended for display to end users.
 *
 * While this class provides structured messaging capabilities, child classes can also throw exceptions as a
 * standard mechanism to indicate critical errors or exceptional conditions.
 *
 */
class MessageActionBase
{


    // An array to hold the messages.
    protected $messages = [];


    /**
     * Retrieve all the messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    //--------------------------------------------
    // protected
    //--------------------------------------------
    /**
     * Add a warning message.
     *
     * @param string $message
     * @return void
     */
    protected function warning(string $message): void
    {
        $this->addMessage('warning', $message);
    }

    /**
     * Add an error message.
     *
     * @param string $message
     * @return void
     */
    protected function error(string $message): void
    {
        $this->addMessage('error', $message);
    }

    /**
     * Add a success message.
     *
     * @param string $message
     * @return void
     */
    protected function success(string $message): void
    {
        $this->addMessage('success', $message);
    }

    /**
     * Add a notice message.
     *
     * @param string $message
     * @return void
     */
    protected function notice(string $message): void
    {
        $this->addMessage('notice', $message);
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Generic function to add a message of a given type.
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    private function addMessage(string $type, string $message): void
    {
        $this->messages[] = [$type, $message];
    }
}

