<?

    // Klassendefinition
    class CTRL_PID extends IPSModule {
 
     //Klassenkonstanten
    const IDENT_IANTEIL = 'IAnteil';
    const IDENT_XIOLD   = 'Xiold';
    const IDENT_ISACTIVE = 'isactive';
    const IDENT_CYCLETIME = 'cycletime';
    const IDENT_CYCLE_POLLER = 'cycle_poller';

    const IDPROP_KP = 'KP';
    const IDPROP_KI = 'KI';
    const IDPROP_KD = 'KD';
    
//    const IDENT_ = '';       // Der Konstruktor des Moduls
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
            
            $this->RegisterVariableFloat(self::IDENT_IANTEIL, "I-Anteil", "CTRL.Val"); //den dynamischen internen Reglerzustand I-Wert vom Typ CTRL.IVal anlegen
            $this->RegisterVariableFloat(self::IDENT_XIOLD, "Xi_old", "CTRL.Val"); //Variable für den alten letzen Eingangswert einrichten
            $this->RegisterVariableBoolean(self::IDENT_ISACTIVE, "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 
            
             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat(self::IDPROP_KP, 1);
            $this->RegisterPropertyFloat(self::IDPROP_KI, 0);
            $this->RegisterPropertyFloat(self::IDPROP_KD, 0);

            $this->RegisterPropertyInteger(self::IDENT_CYCLETIME, 1000);
            
            
            /* Create zyklischer Timer
            */
            $this->RegisterTimer (self::IDENT_CYCLE_POLLER, 1000 /*ms*/, /*script*/"echo 'Hallo Welt'; " );
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            /* eigene Apply Changes
            */
            
            $this->SetTimerInterval(self::IDENT_CYCLE_POLLER, $this->ReadPropertyInteger ( self::IDENT_CYCLETIME ));
            
            
            
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
            SetValue($this->GetIDForIdent ( self::IDENT_ISACTIVE ),$switch);
            
            if ($switch) {
              $this->SetTimerInterval(self::IDENT_CYCLE_POLLER, $this->ReadPropertyInteger ( self::IDENT_CYCLETIME ));
              }
              else {
              $this->SetTimerInterval(self::IDENT_CYCLE_POLLER, 0);
              }
        }
    }
?>
