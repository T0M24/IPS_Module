<?
    require_once (__DIR__ . "/../libs/VarProfileManagement.inc");
    // Klassendefinition
    class SECU_MAIN extends IPSModule {
 
     //Klassenkonstanten
    const IDENT_MODE         = 'MODE';
    const IDENT_CODE_L1         = 'Code4Level1';
    const IDENT_CODE_L2         = 'Code4Level2';
    const IDENT_CODE_L3         = 'Code4Level3';
    
    const IDENT_ACC_LEVEL_1         = 'access4Level1';
    const IDENT_ACC_LEVEL_2         = 'access4Level2';
    const IDENT_ACC_LEVEL_3         = 'access4Level3';
    const IDENT_NOACC_LEVEL_1         = 'No_access4Level1';
    const IDENT_NOACC_LEVEL_2         = 'No_access4Level2';
    const IDENT_NOACC_LEVEL_3         = 'Vo_access4Level3';
    
    const ID_CurrCode                = 'CurrCode'
    
    const VAR_PROFILE_PREFIX = "SECU.";
    const VAR_PROFILE_MODE = VAR_PROFILE_PREFIX."MODE";
    
    
    

    //Fehlercodes
    const STAT_WARNING = 201; /*Warnung*/
    
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
            if (!IPS_VariableProfileExists(self::VAR_PROFILE_MODE))    {
            			IPS_CreateVariableProfile     (self::VAR_PROFILE_MODE, 1);   //Typ:integer
            			IPS_SetVariableProfileAssociation (self::VAR_PROFILE_MODE, 0, "LOCKED", "Lock", #ff0033);
            			IPS_SetVariableProfileAssociation (self::VAR_PROFILE_MODE, 1, "UNLOCK_LEVEL1", "LockOpen", #009900);
            			IPS_SetVariableProfileAssociation (self::VAR_PROFILE_MODE, 2, "UNLOCK_LEVEL2", "LockOpen", #009900);
            			IPS_SetVariableProfileAssociation (self::VAR_PROFILE_MODE, 3, "UNLOCK_LEVEL3", "LockOpen", #009900);
            			IPS_SetVariableProfileDigits  (self::VAR_PROFILE_MODE,0);
            		}
 
 
            /* Create RegisterVariablen
            */
            
            $this->RegisterVariableInteger(self::IDENT_MODE,   self::IDENT_MODE,    self::VAR_PROFILE_MODE); 
            
            $this->RegisterVariableString(self::IDENT_CODE_L1, self::IDENT_CODE_L1, "");  
            $this->RegisterVariableString(self::IDENT_CODE_L2, self::IDENT_CODE_L2, "");  
            $this->RegisterVariableString(self::IDENT_CODE_L3, self::IDENT_CODE_L3, "");  

            $this->RegisterVariableBoolean(self::IDENT_ACC_LEVEL_1, self::IDENT_ACC_LEVEL_1, "");  
            $this->RegisterVariableBoolean(self::IDENT_ACC_LEVEL_2, self::IDENT_ACC_LEVEL_2, "");  
            $this->RegisterVariableBoolean(self::IDENT_ACC_LEVEL_3, self::IDENT_ACC_LEVEL_3, "");  
            $this->RegisterVariableBoolean(self::IDENT_NOACC_LEVEL_1, self::IDENT_NOACC_LEVEL_1, "");  
            $this->RegisterVariableBoolean(self::IDENT_NOACC_LEVEL_2, self::IDENT_NOACC_LEVEL_2, "");  
            $this->RegisterVariableBoolean(self::IDENT_NOACC_LEVEL_3, self::IDENT_NOACC_LEVEL_3, "");  

             
            /* Create Eigenschafts variablen
            */
            $this->RegisterVariableString(self::ID_CurrCode, self::ID_CurrCode, "");
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            $this->SetStatus ( 102 /*active*/ );
           
            /* eigene Apply Changes
            */

        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function SetCodeChar($char) {
            //fügt dem aktuellen Codefeld das Zeichen an
          $code  = GetValueString($this->GetIDForIdent ( self::ID_CurrCode));
          if (strlen($char)==1) 
          {
               $code=$code.$char;
          }
          else
          {
               switch ($char)
               {
                    case "-1": //das letzte Zeichen entfernen
                    $code=substr($code,0,strlen($code)-1);
                    break;
                    case "--": //komplett löschen
                    $code="";
                    break;
               }
          }
          SetValueString($this->GetIDForIdent ( self::ID_CurrCode),$code);                             
        }
        
        
       
    }
?>
