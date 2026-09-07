<?php

declare(strict_types=1);

class HelloWorld extends IPSModuleStrict
{
    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyString('Greeting', 'Hello');
        $this->RegisterPropertyString('Name', 'World');
        $this->RegisterPropertyInteger('Interval', 0);

        $this->RegisterTimer('SayHelloTimer', 0, 'HW_SayHello($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $this->RegisterVariableString('Message', $this->Translate('Message'), '', 10);
        $this->RegisterVariableInteger('Counter', $this->Translate('Counter'), '', 20);

        $this->RegisterVariableBoolean('Active', $this->Translate('Active'), '~Switch', 30);
        $this->EnableAction('Active');

        $this->SetTimerInterval('SayHelloTimer', $this->ReadPropertyInteger('Interval') * 1000);

        $this->SetStatus(102);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'Active':
                $this->SetValue('Active', (bool) $Value);
                break;

            default:
                throw new Exception(sprintf($this->Translate('Invalid Ident: %s'), $Ident));
        }
    }

    /**
     * Schreibt den konfigurierten Gruß in die Variable "Message" und zählt "Counter" hoch.
     * Aufruf aus Skripten: HW_SayHello($id);
     */
    public function SayHello(): string
    {
        $message = sprintf(
            '%s, %s!',
            $this->ReadPropertyString('Greeting'),
            $this->ReadPropertyString('Name')
        );

        $this->SetValue('Message', $message);
        $this->SetValue('Counter', $this->GetValue('Counter') + 1);
        $this->LogMessage($message, KL_MESSAGE);

        return $message;
    }

    /**
     * Setzt den Zähler zurück. Aufruf aus Skripten: HW_ResetCounter($id);
     */
    public function ResetCounter(): void
    {
        $this->SetValue('Counter', 0);
    }
}
