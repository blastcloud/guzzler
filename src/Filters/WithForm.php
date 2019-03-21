<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

class WithForm extends Base implements With
{
    use Helpers;

    protected $form = [];
    protected $exclusive = false;

    public function withFormField($key, $value)
    {
        $this->form[$key] = $value;
    }

    public function withForm(array $form, bool $exclusive = false)
    {
        foreach ($form as $key => $value) {
            $this->withFormField($key, $value);
        }

        $this->exclusive = $exclusive;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            parse_str($call['request']->getBody(), $parsed);
            return $this->testFields($this->form, $parsed, $this->exclusive);
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "Form: (Exclusive: {$e}) "
            .json_encode($this->form, JSON_PRETTY_PRINT);
    }
}