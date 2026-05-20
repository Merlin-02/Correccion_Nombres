<?php
/**
 * Migración: crea la tabla `dictionary` y la puebla desde `personas`
 * o desde los arrays de nombres si `personas` está vacía.
 */

$config = parse_ini_file(__DIR__ . '/../config.ini');
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
$conn->set_charset('utf8mb4');

echo "Creando tabla dictionary...\n";
$conn->query("DROP TABLE IF EXISTS dictionary");
$conn->query("
    CREATE TABLE dictionary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        word_no_accent VARCHAR(100) NOT NULL,
        word_accented VARCHAR(100) NOT NULL,
        UNIQUE KEY idx_word_no_accent (word_no_accent)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$total = 0;

// 1. Extraer palabras únicas desde personas (si existe y tiene datos)
$check = $conn->query("SHOW TABLES LIKE 'personas'");
if ($check && $check->num_rows > 0) {
    $count = $conn->query("SELECT COUNT(*) AS c FROM personas")->fetch_assoc()['c'];
    if ($count > 0) {
        echo "Extrayendo palabras únicas desde personas ($count registros)...\n";

        $result = $conn->query("
            SELECT DISTINCT w_upper AS raw, w_upper_corrected AS corrected FROM (
                SELECT DISTINCT
                    UPPER(SUBSTRING_INDEX(SUBSTRING_INDEX(nombre_completo, ' ', n.digit+1), ' ', -1)) AS w_upper,
                    UPPER(SUBSTRING_INDEX(SUBSTRING_INDEX(correccion, ' ', n.digit+1), ' ', -1)) AS w_upper_corrected
                FROM personas
                JOIN (SELECT 0 digit UNION SELECT 1 UNION SELECT 2) n
                ON LENGTH(correccion) - LENGTH(REPLACE(correccion, ' ', '')) >= n.digit
            ) sub WHERE w_upper != ''
        ");

        $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word_no_accent, word_accented) VALUES (?, ?)");
        while ($row = $result->fetch_assoc()) {
            $noAccent = removeAccents(trim($row['raw']));
            $accented = trim($row['corrected']);
            if ($noAccent !== '' && $accented !== '') {
                $stmt->bind_param('ss', $noAccent, $accented);
                $stmt->execute();
                $total += $stmt->affected_rows;
            }
        }
        $stmt->close();
    }
}

// 2. Si no se extrajo nada, insertar palabras de los arrays
if ($total === 0) {
    echo "Poblando desde arrays de nombres...\n";
    $names = ['JOSÉ','MARÍA','JUAN','CARLOS','LUIS','ANA','MIGUEL','CARMEN','FRANCISCO','TERESA','ANTONIO','ISABEL','MANUEL','DOLORES','JAVIER','PILAR','RAFAEL','MARGARITA','PEDRO','SOFÍA','ÁNGEL','LAURA','DIEGO','ELENA','SERGIO','CRISTINA','PABLO','IRENE','VÍCTOR','RAQUEL','ANDRÉS','PATRICIA','DAVID','SARA','ALBERTO','RUTH','ALFREDO','MÓNICA','VICENTE','SILVIA','JOAQUÍN','NURIA','RAMÓN','BEATRIZ','FERNANDO','CELIA','ÓSCAR','MARTA','HÉCTOR','ÁLVARO','LUCÍA','ADRIÁN','PAULA','RUBÉN','CLAUDIA','IVÁN','ÁNGELA','JULIÁN','VERÓNICA','TOMÁS','INÉS','EDUARDO','NATALIA','JESÚS','ANDREA','EMILIO','CAMILA','ARTURO','VALERIA','ROBERTO','ALEJANDRA','RICARDO','GABRIELA','ALFONSO','LILIANA','GUILLERMO','ADRIANA','ENRIQUE','CAROLINA','MAURICIO','VANESSA','ESTEBAN','MARIANA','FELIPE','GLORIA','SEBASTIÁN','ROCIO','LEONARDO','CONCEPCIÓN','GERMÁN','ESPERANZA','RAÚL','MERCEDES','IGNACIO','NIEVES','SANTIAGO','EVA','DANIEL','ROSA','ALEJANDRO','LIDIA','HUGO','SUSANA','MARTÍN','AURORA','LÁZARO','PALOMA','NÉSTOR','AMANDA','FÉLIX','BEGOÑA','LUCAS','YOLANDA','GONZALO','CECILIA','JORGE','LUISA','MARIO','ÁGUEDA','MARCOS','NOELIA','GUSTAVO','MACARENA','SAMUEL','CATALINA','ÁNGELES','BENJAMÍN','FABIÁN','LEANDRO','CONSUELO','MATEO','ÁFRICA','NICOLÁS','ALBA','MAXIMILIANO','ÁGATA','EMILIANO','JULIETA','GABRIEL','VALENTÍN','NORMA','EZEQUIEL','FLORENCIA','DEMETRIO','LORENA','FAUSTO','HILARIO','GERVASIO','CONSTANZA','NAZARIO','EUGENIA','GAEL','SANDRA','ÁXEL','DIANA','ODÍN','TANIA','YAGO','SABRINA','FERNANDA','BAUTISTA','PAULINA','MATÍAS','JIMENA','THIAGO','VALENTINA','BENITO','ANTONIA','FAUSTINO','ESTEFANÍA','PONCIANO','GENOVEVA','SATURNINO','PETRONILA','EVARISTO','JOSAFAT','BASILISA','FLORENCIO','GERTRUDIS','APOLINAR','LEOCADIA','CRISPÍN','HERMINIA','BONIFACIO','EDUVIGES','RUTILIO','SEVERA','EULOGIO','ASCENSIÓN','JENARO','TRINIDAD','ABELARDO','ASUNCIÓN','ADOLFO','SOCORRO','HIGINIO','MILAGROS','VITO','ROSARIO','BELÉN','LUCIO','DÉBORA','EMETERIO','EVANGELINA','CIRIACO','LEONOR','DÁMASO','TEOFILA','SIXTO','MATILDE','HERMÓGENES','OFELIA','POLICARPO','RAMONA','CAYETANO','PRUDENCIA','CELESTINO','REMEDIOS','SALVADOR','AARÓN','ABIGAIL','ABEL','ADÁN','AGUSTÍN','ALICIA','ALONSO','AMAIA','ANÍBAL','ARACELI','ARÍSTIDES','AZUCENA','BALBINA','BARTOLOMÉ','BERNARDINO','BLANCA','CANDELARIA','CASIMIRO','CLOTILDE','CLEMENTE','CRUZ','DAMIÁN','DOROTEA','ELÍAS','ENCARNACIÓN','EULALIA','FILOMENA','GREGORIO','GUIOMAR','HERIBERTO','JACINTA','JERÓNIMO','JUSTINA','LAUREANO','LIBRADA','LISANDRO','LUCRECIA','MACEDONIO','MANUELA','NARCISO','NATIVIDAD','NORBERTO','OLIMPIA','PASCUAL','PAZ','PRISCILLA','PURIFICACIÓN','REFUGIO','ROGELIO','SALOMÉ','SEGUNDO','SERVANDO','SILVESTRE','TELÉSFORO','URSULA','VALERIANO','VICENTA','WILFRIDO','XIMENA','ZACARÍAS','ZENÓN','ZOILA','GARCÍA','RODRÍGUEZ','MARTÍNEZ','LÓPEZ','HERNÁNDEZ','GONZÁLEZ','PÉREZ','SÁNCHEZ','RAMÍREZ','TORRES','FLORES','RIVERA','GÓMEZ','DÍAZ','MORALES','ORTIZ','GUTIÉRREZ','CHÁVEZ','REYES','ÁLVAREZ','CASTILLO','JIMÉNEZ','VÁZQUEZ','ROMERO','MÉNDEZ','NAVARRO','RUIZ','DOMÍNGUEZ','DELGADO','MUÑOZ','FERNÁNDEZ','SANTOS','IBAÑEZ','VELÁZQUEZ','AGUILAR','SUÁREZ','CÁRDENAS','BARRERA','PARRA','MÁRQUEZ','CÁCERES','QUINTERO','NIETO','BÉJAR','MÚÑOZ','CÓRDOVA','VALDÉS','MIRANDA','GUZMÁN','PEÑA','GALLARDO','ARCINIEGA','BECERRA','BERMÚDEZ','BOLAÑOS','BURGOS','CALDERÓN','CAMACHO','CAMPOS','CARRANZA','CASARES','CÁSTRO','CERVANTES','CONTRERAS','CORONA','CORONADO','DE LA CRUZ','DE LA ROSA','DEL VALLE','DURÁN','ECHEVERRÍA','ENRÍQUEZ','ESCOBAR','ESPARZA','ESPINOZA','ESTÉVEZ','FUENTES','GALLEGOS','GAMBOA','GODOY','GUEVARA','HERRERA','HUERTA','LEAL','LEÓN','LOERA','LUJÁN','MALDONADO','MANRÍQUEZ','MATÍAS','MEDRANO','MELÉNDEZ','MONTERO','MONTOYA','NARVÁEZ','NÚÑEZ','OCAMPO','OLIVARES','ORDÓÑEZ','PACHECO','PALACIOS','PALOMINO','PEDRAZA','PELÁEZ','PIMENTEL','PONCE','PORTILLO','PRADO','QUEZADA','QUINTANILLA','QUIÑONES','RANGEL','RIOS','ROBLES','ROCHA','ROSALES','SALAZAR','SAMANIEGO','SANABRIA','SANDOVAL','SANTACRUZ','SANTIAGO','SERRANO','SOLANO','SOLÍS','SOSA','TAMAYO','TEJADA','TOVAR','TREVIÑO','TRUJILLO','ULLOA','VALADEZ','VALENZUELA','VALLADARES','VARELA','VELASCO','VÉLEZ','VERA','VIGIL','VILLALOBOS','VILLANUEVA','VILLARREAL','VILLEGAS','YÁÑEZ','ZAMORA','ZARATE','ZAVALA','ZEPEDA','HURTADO','QUIROZ','ACEVEDO','BAUTISTA','BENÍTEZ','CABRERA','CAMARENA','CARRILLO','CASTAÑEDA','COVARRUBIAS','ESPINOSA','FONSECA','GAONA','GUERRA','HINOJOSA','IBARRA','INFANTE','JUÁREZ','LARA','LOZANO','LUCERO','MACÍAS','MAGAÑA','MARÍN','MATAMOROS','MONREAL','MORA','MORENO','NEGRETE','OLGUÍN','OSORIO','PADILLA','PARTIDA','PIZANO','PLASCENCIA','PUENTE','RENDÓN','ROBLEDO','SALCEDO','SAMPERIO','SEDANO','SEGURA','SOTELO','TAPIA','URIAS','VALTIERRA','ZAYAS','ACUÑA','BAHENA','BARRIENTOS','BETANCOURT','BRAVO','CABRAL','CALVILLO','CARREÓN','CEDILLO','CORONEL','CUÉLLAR','DEANDO','DELEÓN','ESCOBEDO','FABIÁN','GALÁN','HARO','JARAMILLO','LOMELÍ','MADERA','MEJÍA','MELGAR','MOLINA','MONCADA','MURO','OLIVAS','PABLO','PAREDES','PECINA','PIÑA','QUIROGA','REGALADO','RIQUELME','RIVADENEIRA','ROJO','SALDAÑA','SALGADO','SISNEROS','TÉLLEZ','VALDIVIA','VENTURA','VIDAL','ZAMBRANO','ARRIAGA','BOBADILLA','BOTELLO','BUENROSTRO','CALLEJA','CANTÚ','CARRASCO','CASTELLANOS','CERDA','CHAVARRÍA','COLÍN','CORRAL','CUENCA','DELGADILLO','ESPINO','FERRER','GALINDO','GARIBAY','GIRÓN','HIGUERA','IZQUIERDO','LIZÁRRAGA','LLANOS','MONJARÁS','NAJERA','OCAÑA','OLMOS','ORELLANA','PATIÑO','PEDROZA','PULIDO','RAMOS','REYNOSO','RIOSECO','RUBIO','SALINAS','SAUCEDO','SEPÚLVEDA','TOLEDO','URIBE','VALLES','VILLASANA','ZÚÑIGA'];
    $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word_no_accent, word_accented) VALUES (?, ?)");
    foreach ($names as $word) {
        $noAccent = removeAccents($word);
        $stmt->bind_param('ss', $noAccent, $word);
        $stmt->execute();
        $total += $stmt->affected_rows;
    }
    $stmt->close();
}

$finalCount = $conn->query("SELECT COUNT(*) AS c FROM dictionary")->fetch_assoc()['c'];
echo "Migración completada: $finalCount palabras únicas en dictionary.\n";

$conn->close();

function removeAccents($str) {
    $from = ['Á','É','Í','Ó','Ú','Ü','Ñ','á','é','í','ó','ú','ü','ñ'];
    $to   = ['A','E','I','O','U','U','N','a','e','i','o','u','u','n'];
    return str_replace($from, $to, $str);
}
