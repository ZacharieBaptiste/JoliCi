<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi\Log;

use Monolog\Formatter\FormatterInterface;

class SimpleFormatter implements FormatterInterface
{
    private $static = array();

    private $lineNumber = 0;

    /*
     * (non-PHPdoc) @see \Monolog\Formatter\FormatterInterface::format()
     */
    public function format(array $record)
    {
        if (isset($record['context']['clear-static'])) {
            $this->static = array();

            return "";
        }

        $message = $record['message'];

        if (isset($record['context']['static']) && $record['context']['static']) {
            $id      = $record['context']['static-id'];

            if (!isset($this->static[$id])) {
                $this->static[$id] = array(
                    'current_line' => $this->lineNumber,
                    'message'      => $message,
                );

                $message = sprintf("%s\n", $message);
            } else {
                $diff                         = ($this->lineNumber - $this->static[$id]['current_line']);
                $lastMessage                  = $this->static[$id]['message'];
                $this->static[$id]['message'] = $message;

                //Add space to replace old string in message
                if (mb_strlen($lastMessage) > mb_strlen($message)) {
                    $message = str_pad($message, mb_strlen($lastMessage), " ", STR_PAD_RIGHT);
                }

                $message = sprintf("\x0D\x1B[%sA%s\x1B[%sB\x0D", $diff, $message, $diff);
            }
        }

        if (preg_match('#\n#', $message)) {
            $this->lineNumber++;
        }

        return $message;
    }

    /*
     * (non-PHPdoc) @see \Monolog\Formatter\FormatterInterface::formatBatch()
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}
