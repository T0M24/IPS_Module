<?
    // Klassendefinition
    class CTRL_PID extends IPSModule {
 
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();
            
            /* Create Variablenprofile
            */
            if (!IPS_VariableProfileExists("CTRL.Val")) {
            			IPS_CreateVariableProfile("CTRL.Val", 2);   //Typ:float
            			IPS_SetVariableProfileValues("CTRL.Val", -100, 100, 10);
            		}
 
 
            /* Create RegisterVariablen
            */
            
            $this->RegisterVariableFloat("IAnteil", "I-Anteil", "CTRL.Val"); //den dynamischen internen Reglerzustand I-Wert vom Typ CTRL.IVal anlegen
            $this->RegisterVariableFloat("Xiold", "Xi_old", "CTRL.Val"); //Variable für den alten letzen Eingangswert einrichten
            $this->RegisterVariableBoolean("isactive", "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 
            
             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat("KP", 1);
            $this->RegisterPropertyFloat("KI", 0);
            $this->RegisterPropertyFloat("KD", 0);

            $this->RegisterPropertyInteger("cycletime", 1000);
            
            
            /* Create zyklischer Timer
            */
            $this->RegisterTimer ( "cycle_poller", 1000 /*ms*/, /*script*/"echo 'Hallo Welt'; " );
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            /* eigene Apply Changes
            */
            
            $this->SetTimerInterval("cycle_poller", $this->ReadPropertyInteger ( "cycletime" ));
            
            
            
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function SetEnable($switch) {
            //schaltet den Regler an oder aus
            SetValue(GetIDForIdent ( "is_active" ),$switch);
            
            if ($switch) {
              $this->SetTimerInterval("cycle_poller", $this->ReadPropertyInteger ( "cycletime" ));
              }
              else {
              $this->SetTimerInterval("cycle_poller", 0);
              }
        }
    }
?>
