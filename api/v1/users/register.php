<?php
/**
 * Name register.php
 * Stellt das Registrierungsferfahren bereit.
 */

use ejfrancis\BruteForceBlock;
// Wir benutzen User Class um nutzerabfragen zu ermöglichen
$users = new Users();
// Erstellen Sie eine Instanz der BruteForceBlock-Klasse ob ein Registrierungsversuch möglich ist
$BFBresponse = BruteForceBlock::getRegisterRequestStatus();
// Switch-Anweisung zur Abfrage des Register Request Status
switch ($BFBresponse['status']){
    // Falls es sicher ist
    case 'safe':
        // Der liste hinzufügen, dass ein Registrierungsversuch statt gefunden hat
        $BFBresponse = BruteForceBlock::addRegisterRequestAttempt(BruteForceBlock::GetRealUserIp());
        // Abfrage ob username und password gesendet wurden
        if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
            // Die Eingaben werden in variablen gespeichert
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $email = $_POST['email'] ?? null;

            // Prüfen ob der Username den richrlinien entspricht
            if(!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
                // Falls der Registrierungsversuch fehlschlägt, wird eine Fellernmeldung ausgegeben
                http_response_code(202);
                die(json_encode(array(
                    'status'=>'failure',			
                    'message' => ' Username is invalid',	
                    'code' => '4',
                ), JSON_PRETTY_PRINT));
            }

            // Prüfen ob die E-Mail Adresse den richrlinien entspricht
            if(!preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email)) {
                // Falls der Registrierungsversuch fehlschlägt, wird eine Fellernmeldung ausgegeben
                http_response_code(202);
                die(json_encode(array(
                    'status'=>'failure',			
                    'message' => ' E-Mail is invalid',	
                    'code' => '5',
                ), JSON_PRETTY_PRINT));
            }

            // Prüfen ob das Passwort den richrlinien entspricht
            if(!preg_match('/^[a-zA-Z0-9]{6,20}$/', $password)) {
                // Falls der Registrierungsversuch fehlschlägt, wird eine Fellernmeldung ausgegeben
                http_response_code(202);
                die(json_encode(array(
                    'status'=>'failure',			
                    'message' => ' Password is invalid',	
                    'code' => '6',
                ), JSON_PRETTY_PRINT));
            }

            // Abfragen ob die E-Mail nicht existiert, wird die Benutzername Prüfung durchgeführt
            if($users->emailCount($email) == 0) {
                // Abfragen ob der Benutzer nicht existiert, kann der registrierungsfortschritt fortgesetzt werden
                if($users->usernameCount($username) == 0) {
                    // Danach wird der user angelegt
                    $users->addUser($username, $password, $email);
                            
                    // Erfolgreich registriert
                    http_response_code(200);
                    die(json_encode(array
                    (
                        'status'=>'succes',		
                        'message' => 'Successfully registered',	
                        'code' => '201',
                    ), JSON_PRETTY_PRINT));

                } else {
                    // Falls der Registrierungsversuch fehlschlägt, wird eine Fellernmeldung ausgegeben
                    http_response_code(202);
                    die(json_encode(array(
                        'status'=>'failure',	
                        'message' => 'Username already exists',	
                        'code' => '10_005',
                    ), JSON_PRETTY_PRINT));
                }
            } else {
                // Falls der Registrierungsversuch fehlschlägt, wird eine Fellernmeldung ausgegeben
                http_response_code(202);
                die(json_encode(array(
                    'status'=>'failure',	
                    'message' => 'Email already exists',	
                    'code' => '10_005',
                ), JSON_PRETTY_PRINT));
            }
        } else {
            // Falls nicht gebe eine Fehlermeldung zurück
            http_response_code(202);
            die(json_encode(array(
			    'status'=>'failure',			
			    'message' => 'Field is missing',	
			    'code' => '1',
		        ), JSON_PRETTY_PRINT));
        }
    case 'error':
        //Fehler ist aufgetreten. Meldung zurückgeben
        $error_message = $BFBresponse['message'];
        // Allgemeiner Fehler
        http_response_code(500);
        die(json_encode(array(
            'status'=>'failure',			
            'message' => $error_message,
            'code' => '500',
            ), JSON_PRETTY_PRINT));
    case 'delay':
        //Erforderliche Zeitspanne bis zur nächsten Registrierung
        $remaining_delay_in_seconds = $BFBresponse['message'];
        http_response_code(203);
        die(json_encode(array(
            'status'=>'failure',			
            'message' => 'Request Blocked',	
            'code' => '203',
            'delay' => $remaining_delay_in_seconds,
            ), JSON_PRETTY_PRINT));
    case 'captcha':
        //Captcha required
        http_response_code(203);
        die(json_encode(array(
		    'status'=>'failure',			
		    'message' => 'Captcha required',		
		    'code' => '203',
		    ), JSON_PRETTY_PRINT));
    default:
}
?>