<?php

declare(strict_types=1);
	class ArchFilter extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyBoolean("enablearchiv",false); //ist die Archivierung aktiv oder nicht
			$this->RegisterPropertyBoolean("enablereduction",false); //ist die Reduzierung aktiv
			$this->RegisterPropertyInteger("loggingvariableID",0); //die Variable, welche geloggt werden soll
			
			//ein Event anlegen, welches Aktualisierungen der Variablen erfasst
			$this->RegisterAttributeInteger("VariableEventID",IPS_CreateEvent(0));
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();

			//das Event löschen
			IPS_DeleteEvent($this->ReadAttributeInteger("VariableEventID"));
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			//die Instanz an den richtigen Ort verschieben
			if($this->ReadPropertyInteger("loggingvariableID")!=0){
				IPS_SetParent($this->InstanceID,$this->ReadPropertyInteger("loggingvariableID"));
			}
			
			//das Ganze Eventgedöns
			$evid=$this->ReadAttributeInteger("VariableEventID");
			IPS_SetEventTrigger($evid,0,$this->ReadPropertyInteger("loggingvariableID")); //die Auslösevariable festlegen
			IPS_SetParent($evid,$this->InstanceID); //und das Event unter die Instanz verschieben
			IPS_SetEventActive ($evid, $this->ReadPropertyBoolean("enablearchiv"));
			$this->SetArchiveBit(); //Archivierung aktivieren oder ausschalten

			//und das Formular anpassen
			$this->OnChangeExecute();
			
		}
		public function GetConfigurationForm() { 
            $jsonForm = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $jsonForm["elements"][1]["visible"] = $this->ReadPropertyBoolean("enablearchiv");

            return json_encode($jsonForm);
		}

		function SetArchiveBit() {
			$instance = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}'); //die GUID des ArchivHandlers
			$archiveID = $instance[0]; 
			$ID=$this->ReadPropertyInteger("loggingvariableID");
			if ($ID>=0){
				AC_SetLoggingStatus ($archiveID,
					$ID, 
					$this->ReadPropertyBoolean("enablearchiv"));
			}
			
		}
		function OnChangeExecute() {
			//this->ApplyChanges($id);
            if ( $this->ReadPropertyBoolean("enablearchiv") ) {
				//echo "enable";
                $this->UpdateFormField("enablereduction", "visible", true);
            } else {
				//echo "disable";
                $this->UpdateFormField("enablereduction", "visible", false);
            }
        }
	}