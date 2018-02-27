<?

    // Klassendefinition
    class CTRL_PID extends IPSModule {
 
     //Klassenkonstanten
    const IDENT_IANTEIL = 'IAnteil';
    const IDENT_XI      = 'Xi';
    const IDENT_XIOLD   = 'Xiold';
    const IDENT_XSET    = 'XSet';
    const IDENT_Y       = 'Yout';
    
    const IDENT_ISACTIVE = 'isactive';
    
    const IDENT_CYCLE_TIME = 'cycle_time';
    const IDENT_CYCLE_POLLER = 'cycle_poller';

    const IDPROP_KP = 'KP';
    const IDPROP_KI = 'KI';
    const IDPROP_KD = 'KD';
    
    //Fehlercodes
    const STAT_TIMERWARNING = 201; /*Timerwert außerhalb Grenzen*/
    
//    const IDENT_ = '';       // Der Konstruktor des Moduls
        public static $InstanceID;

        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
            $this->$InstanceID=$InstanceID;
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
            $this->RegisterVariableFloat(self::IDENT_XI, "Xi", "CTRL.Val"); //Variable für den aktuellen Eingangswert einrichten
            $this->RegisterVariableFloat(self::IDENT_XSET, "X_Soll", "CTRL.Val"); //Variable für den aktuellen Eingangswert einrichten
            $this->RegisterVariableFloat(self::IDENT_Y, "Y_out", "CTRL.Val"); //Variable für den aktuellen Stellwert einrichten
            
            $this->RegisterVariableBoolean(self::IDENT_ISACTIVE, "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 
            
             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat(self::IDPROP_KP, 1);
            $this->RegisterPropertyFloat(self::IDPROP_KI, 0);
            $this->RegisterPropertyFloat(self::IDPROP_KD, 0);

            $this->RegisterPropertyInteger(self::IDENT_CYCLE_TIME, 1000);
            
            
            /* Create zyklischer Timer
            */
            $this->RegisterTimer (self::IDENT_CYCLE_POLLER, 1000 /*ms*/, /*script*/"CTRL_Calculate($this->$InstanceID); " );
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            $this->SetStatus ( 102 /*active*/ );
           
            /* eigene Apply Changes
            */
            $time= $this->ReadPropertyInteger ( self::IDENT_CYCLE_TIME );
            
            if($time < 200) {
              
              $time = 200; /*ms*/
              $this->SetStatus ( self::STAT_TIMERWARNING );
              
              }
            if($time > 3600*1000) {
              
              $time = 3600*1000;
              $this->SetStatus ( self::STAT_TIMERWARNING );
              
              }
            
            $this->SetTimerInterval(self::IDENT_CYCLE_POLLER,$time);
            
            
            
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
              $this->SetTimerInterval(self::IDENT_CYCLE_POLLER, $this->ReadPropertyInteger ( self::IDENT_CYCLE_TIME ));
              }
              else {
              $this->SetTimerInterval(self::IDENT_CYCLE_POLLER, 0);
              }
        }
        
        /* 
          Der eigentliche Regleralgorithmus
        */
        public function Calculate() {
        
             /*der Regler sollte zyklich aufgerufen werden, es ist dabei egal, wie groß der zeitliche Abstand ist,
             da der letzte Aufruf mit beachtet wird
             */
             
            //zuerst die Reglervariablen ermitteln
            
            $KP = ReadPropertyFloat( self::IDPROP_KP);
            $KI = ReadPropertyFloat( self::IDPROP_KI);
            $KD = ReadPropertyFloat( self::IDPROP_KD);
            
            $X_Ist      = GetValueFloat($this->GetIDForIdent ( self::IDENT_XI));
            $X_Ist_alt  = GetValueFloat($X_Ist_alt_ID = $this->GetIDForIdent ( self::IDENT_XIOLD));
            
            
            $X_Set      = GetValueFloat($this->GetIDForIdent ( self::IDENT_XSET));
            
            $x=$X_Set-$X_Ist;
            
            $I_ID       = $this->GetIDForIdent ( self::IDENT_IANTEIL);
            $I          = GetValueFloat($I_ID); //der aktuelle "innere" I-Anteil
            
            $dt = time() - IPS_GetVariable($I_ID)['VariableUpdated']; //Sekunden seit der letzten Berechnung
            
            $dx = ($X_Set-$X_Ist)-($X_Set-$X_Ist_alt);
            
            $yP = $KP*$x;
            
            $yI = $I + $KI*$x*$dt;
            
            $yD = $KD * ($dx/$dt);
            
            $y= $yP + $yI + $yD;
            
            SetValueFloat($this->GetIDForIdent ( self::IDENT_IANTEIL),$y);
            
            SetValueFloat($I_ID,$yI); //I-Anteil wieder abspeichern
            SetValueFloat($X_Ist_alt_ID,$X_Ist); //letzten Istwert abspeichern         
        
        }
        
    }
?>
