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
            
            $this->RegisterVariableFloat("I-Anteil", "I-Anteil", "CTRL.Val"); //den dynamischen internen Reglerzustand I-Wert vom Typ CTRL.IVal anlegen
            $this->RegisterVariableFloat("Xi_old", "Xi_old", "CTRL.Val"); //Variable für den alten letzen Eingangswert einrichten
            $this->RegisterVariableBoolean("is_active", "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 
            
             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat("K_P", 1);
            $this->RegisterPropertyFloat("K_I", 0);
            $this->RegisterPropertyFloat("K_D", 0);

            $this->RegisterPropertyInteger("cycle_time", 1000);
            
            
            /* Create zyklischer Timer
            */
            $this->RegisterTimer ( "cycle_poller", 1000 /*ms*/, /*script*/"echo 'Hallo Welt'; " )
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            /* eigene Apply Changes
            */
            
            $this->SetTimerInterval("cycle_time", ReadPropertyInteger ( "cycle_time" ));
            
            
            
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function CTRL_SetEnable($switch) {
            //schaltet den Regler an oder aus
            SetValue(GetIDForIdent ( "is_active" ),$switch);
        }
    }
?>
