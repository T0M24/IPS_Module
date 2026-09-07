<?php
    // Klassendefinition
    class Versionspruefung extends IPSModule {
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();

            $this->RegisterVariableString("ActualVersion",$this->Translate("Current Version"));
            $this->RegisterVariableString("VerfuegbareVersion",$this->Translate("Available Version"));
            $this->RegisterPropertyInteger("UpdateIntervall",12);
            $this->RegisterAttributeInteger("LastUpdate",0);
 
            $this->RegisterTimer("Update",0,'VP_UpdateVersion('.$this->InstanceID . ');');
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();

            $this->SetValue("ActualVersion",IPS_GetKernelVersion());

            $this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateIntervall")*60*60*1000);

            $this->UpdateVersion();

        }

        public function GetConfigurationForm() { 
            $jsonForm = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $jsonForm["actions"][0]["caption"] = $this->getUpdateCaption();

            return json_encode($jsonForm);
        }
 
        function getUpdateCaption(){
            return ($this->translate("Last Updates")." : ".date("d.m.Y - H:i:s", $this->ReadAttributeInteger("LastUpdate")));
        }
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * VP_UpdateVersion($id);
        *
        */
        public function UpdateVersion() {

            $rawdata = file_get_contents('https://apt.symcon.de/dists/stable/win/binary-i386/Packages');
            $xml = simplexml_load_string($rawdata);
            $version = $xml->channel->item->enclosure->attributes('sparkle',true)->shortVersionString;
            $this->SetValue("VerfuegbareVersion",strval($version));// Selbsterstellter Code
            $updatetime=time();
            $this->WriteAttributeInteger("LastUpdate",$updatetime);
            $this->UpdateFormField("UpdateLabel","caption", $this->getUpdateCaption() );
        }
    }

