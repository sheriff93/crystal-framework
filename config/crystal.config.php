<?php
/**
 * Config file
 * Crystal Framework
 *
 * @author Krystian Biela <1bitam1@gmail.com>
 * @version 0.8
 * @copyright Copyright (c) Krystian Biela 2013
 */


//Konfiguracja ogólna
//***********************************
define('IS_DEVELOP', true); // false = wersja produkcyjna, true = wersja developerska( wyświetlane komunikaty błędów)
//Uwaga! - NIGDY NIE USTAWIAĆ "true" dla IS_DEVELOP GDY PROJEKT JEST W FAZIE PRODUKCYJNEJ!!!
define('REQ_VER', 50300); //required PHP version
//***********************************

//Ścieżki
//***********************************
define('_MAIN_PUBLIC_DIR', '/cf/');
define('_ROOT', 'http://'.$_SERVER['HTTP_HOST']._MAIN_PUBLIC_DIR);
define('_IMG', _ROOT.'img/');
//***********************************

//Konfiguracja bazy danych MySQL
//***********************************
define('DB_HOST', 'localhost');	//ADRES SERWERA
define('DB_USER', 'root');	//UŻYTKOWNIK
define('DB_PASS', 'root');	//HASŁO UŻYTKOWNIKA
define('DB_NAME', 'crystal'); //NAZWA BAZY DANYCH
//!!! dorobić port !!!
//***********************************

//Nazwa projektu
//***********************************
define('PROJECT_NAME', 'Crystal Framework Control Panel');
define('FRAMEWORK_VER', '0.8');
define('PROJECT_VER', '0.1');
//***********************************

//Mechanizm sesji i autoryzacja
//***********************************
define('COOKIE_NAME', 'CF_Session'); //nazwa ciasteczka
define('AUTH_ENABLED', 1); //włączanie mechanizmu sesji
define('USER_SALT', '8$(hwIPgh34)(*G&EW(d398ghUI93-*%gre*&bbv6(7y5hnou66(0&^yn'); //sól do wykorzystywana przez mechanizm autoryzacji
define('ID_LIMIT', 8);//co ile odsłon ma nastąpić zmiana identyfikatora sesji (częściej = bezpieczniej)
define('TOKEN_SALT', '7y&^H)*&T43nu4388n(*&B *&Yb87n8765wqo109-p*&'); //sól do tokena operacji
define('EMAIL_SALT', 'wiergq2j3480j2fiIOhjoojR2890389492BHCA20SDN 2u4TAK$%*&oSBVFBW*423HBY&*(t$vtb56vvVVggP-7Y3');
define('BASKET_SALT', '23rbTfr76FR%R&^Gq2vwqfnwbt%&*y76vYT103ry2b83fun89pY*(_&*%&(^c wvfbekfh*(7t');
//***********************************


//Konfiguracja parsowania żądania HTTP
//*****************************************
define('MODEL_PATH_INDEX', 1); 	//"głebokość" nazwy kontrolera w ścieżce(httpRequest), liczba oznacza za którym slashem znajduje się nazwa modelu kontrolera od zera
//*****************************************



//Klucze do zewnętrznych bibliotek
//****************************************

//****************************************



