<?

    // Klassendefinition
    class CTRL_PT1 extends IPSModule {
 
     //Klassenkonstanten
    const IDENT_IN      = 'IN';
    const IDENT_INOLD   = 'INold';
    const IDENT_OUT     = 'Yout';
    
    const IDENT_ISACTIVE = 'isactive';
    
    const IDENT_CYCLE_TIME = 'cycle_time';
    const IDENT_CYCLE_POLLER = 'cycle_poller';

    const IDPROP_K = 'K';
    const IDPROP_TI = 'Ti';
    
   /* const IDPROP_A0 = 'A0';
    const IDPROP_A1 = 'A1';
    const IDPROP_B1 = 'B1';
    */

    //Fehlercodes
    const STAT_TIMERWARNING = 201; /*Timerwert außerhalb Grenzen*/
    
//    const IDENT_ = '';       // Der Konstruktor des Moduls
       

        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
            static $a0=0.0;
            static $a1=0.0;
            static $b1=0.0;
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
            
            $this->RegisterVariableFloat(self::IDENT_OUT,   self::IDENT_OUT, "CTRL.Val"); 
            $this->RegisterVariableFloat(self::IDENT_IN,    self::IDENT_IN, "CTRL.Val"); 
            $this->RegisterVariableFloat(self::IDENT_INOLD, self::IDENT_INOLD, "CTRL.Val"); 
            
            $this->RegisterVariableBoolean(self::IDENT_ISACTIVE, "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 
            
             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat(self::IDPROP_K, 1);
            $this->RegisterPropertyFloat(self::IDPROP_TI, 20); /*Sekunden*/

           /* $this->RegisterPropertyFloat(self::IDPROP_A0, 0); //die drei Koeffizienten für die Berechnung
            $this->RegisterPropertyFloat(self::IDPROP_A1, 0);
            $this->RegisterPropertyFloat(self::IDPROP_B1, 0);
*/

            $this->RegisterPropertyInteger(self::IDENT_CYCLE_TIME, 1000);
            
            
            /* Create zyklischer Timer
            */
            $this->RegisterTimer (self::IDENT_CYCLE_POLLER, 1000 /*ms*/, /*script*/'CTRL_Calculate($_IPS[\'TARGET\']);' );
 
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

            $this->calc_Koeff();
            
            
            
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
              
            $this->calc_Koeff();
        }
        
        public function calc_Koeff() {
            
            /*
              berechnet aus den Faktoren die Koeffizienten (bilineare Transformation)
            */
            $K  = $this->ReadPropertyFloat( self::IDPROP_K);
            $Ti = $this->ReadPropertyFloat( self::IDPROP_TI);
            $Ts = $this->ReadPropertyInteger ( self::IDENT_CYCLE_TIME );
            
            $this->a0 = ($K*$Ts) / ($Ts + 2*$Ti); 
            $this->a1 = $this->a0; 
            $this->b1 = ($Ts - 2*$Ti) / ($Ts + 2*$Ti);
            
            }
        
        /* 
          Der eigentliche Regleralgorithmus
        */
        public function Calculate() {
        
             /*der Regler sollte zyklich aufgerufen werden, es ist dabei egal, wie groß der zeitliche Abstand ist,
             da der letzte Aufruf mit beachtet wird
             */
             
            //zuerst die Reglervariablen ermitteln
            
            
            $In      = GetValueFloat($this->GetIDForIdent ( self::IDENT_IN));
            $In_old  = GetValueFloat($In_old_ID = $this->GetIDForIdent ( self::IDENT_INOLD));
            
            $out=$In_old + $this->a0*$In;
            $In_old=$this->a1*$In - $this->b1*$out;
            
            SetValueFloat($In_old_ID,$In_old); //und Speicher abspeichern
            SetValueFloat($this->GetIDForIdent (self::IDENT_OUT),$out); //und Ausgang abspeichern
            
        
        }
        
    }
?>
