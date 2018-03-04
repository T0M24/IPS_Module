<?
    require_once ('../lib/VarProfileManagement.inc');
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
    
    const VAR_PROFILE = "CTRL.Val";

    //Fehlercodes
    const STAT_TIMERWARNING = 201; /*Timerwert außerhalb Grenzen*/
    
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
            if (!IPS_VariableProfileExists(self::VAR_PROFILE))    {
            			IPS_CreateVariableProfile     (self::VAR_PROFILE, 2);   //Typ:float
            			IPS_SetVariableProfileValues  (self::VAR_PROFILE, -100, 100, 10);
            			IPS_SetVariableProfileDigits  (self::VAR_PROFILE,2);
            		}
 
 
            /* Create RegisterVariablen
            */
            
            $this->RegisterVariableFloat(self::IDENT_OUT,   self::IDENT_OUT,    self::VAR_PROFILE); 
            $this->RegisterVariableFloat(self::IDENT_IN,    self::IDENT_IN,     self::VAR_PROFILE); 
            $this->RegisterVariableFloat(self::IDENT_INOLD, self::IDENT_INOLD,  self::VAR_PROFILE); 
            
            $this->RegisterVariableBoolean(self::IDENT_ISACTIVE, "is_active", "~Switch"); //ist der Regler Aktiv oder nicht 

             
            /* Create Eigenschafts variablen
            */
            $this->RegisterPropertyFloat(self::IDPROP_K, 1);
            $this->RegisterPropertyFloat(self::IDPROP_TI, 20); /*Sekunden*/



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
            $K  = $this->ReadPropertyFloat( self::IDPROP_K);
            $Ti = $this->ReadPropertyFloat( self::IDPROP_TI);
            $Ts = $this->ReadPropertyInteger ( self::IDENT_CYCLE_TIME );
           
            
            $In      = GetValueFloat($this->GetIDForIdent ( self::IDENT_IN));
            $In_old  = GetValueFloat($In_old_ID = $this->GetIDForIdent ( self::IDENT_INOLD));
            
            $a0   = GetValueFloat($this->GetIDForIdent( self::IDPROP_A0));
            $a1   = GetValueFloat($this->GetIDForIdent( self::IDPROP_A1));
            $b1   = GetValueFloat($this->GetIDForIdent( self::IDPROP_B1));
            
            $out=$In_old + $a0*$In;
            $In_old=$a1*$In - $b1*$out;
            
            SetValueFloat($In_old_ID,$In_old); //und Speicher abspeichern
            SetValueFloat($this->GetIDForIdent (self::IDENT_OUT),$out); //und Ausgang abspeichern
            
        
        }
        
    }
?>
