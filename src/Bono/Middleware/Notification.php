<?php

namespace Bono\Middleware;

use Bono\App;
use Bono\Http\Context;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;

class Notification extends UtilCollection
{
    protected $app;

    protected $messages = [
        'error' => [
            '' => []
        ],
        'info' => [
            '' => []
        ],
    ];

    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;

        $options = Options::create([])
            ->merge($options);

        parent::__construct($options);
    }

    public function query(array $options)
    {
        $result = [];
        $levelMessages = $this->messages[$options['level']];
        if (isset($options['context'])) {
            if (isset($levelMessages[$options['context']])) {
                $result = $levelMessages[$options['context']];
            }
        } else {
            foreach ($levelMessages as $messages) {
                foreach ($messages as $message) {
                    $result[] = $message;
                }
            }
        }

        return $result;
    }

    public function render(array $options = null)
    {
        // unset($_SESSION['notification']);
        if (is_null($options)) {
            return $this->render(['level' => 'error']) . "\n" . $this->render(['level' => 'info']);
        }

        $messages = $this->query($options);
        // TODO should defined renderer?
        if (!empty($messages)) {
            $result = '<div class="alert '.$options['level'].'"><div><p>';
            foreach ($messages as $message) {
                $result .= '<span>'.$message['message'].'</span> ';
            }
            $result .= '</p><a href="#" class="close button warning button-outline"><i class="xn xn-close"></i>Close</a></div></div>';
            return $result;
        }
    }

    public function notify(array $message)
    {
        $level = isset($message['level']) ? $message['level'] : '';
        $context = isset($message['context']) ? $message['context'] : '';
        $this->messages[$level][$context][] = $message;
    }

    public function __invoke(Context $context, $next)
    {
        $context['@notification'] = $this;

        return $next($context);
    }
}
