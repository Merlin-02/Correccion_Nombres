<?php
$config = parse_ini_file(__DIR__ . '/../config.ini');
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
$conn->set_charset('utf8mb4');

$firstNames = [
    'JOSÉ', 'MARÍA', 'JUAN', 'CARLOS', 'LUIS', 'ANA', 'MIGUEL', 'CARMEN',
    'FRANCISCO', 'TERESA', 'ANTONIO', 'ISABEL', 'MANUEL', 'DOLORES', 'JAVIER',
    'PILAR', 'RAFAEL', 'MARGARITA', 'PEDRO', 'SOFÍA', 'ÁNGEL', 'LAURA',
    'DIEGO', 'ELENA', 'SERGIO', 'CRISTINA', 'PABLO', 'IRENE', 'VÍCTOR',
    'RAQUEL', 'ANDRÉS', 'PATRICIA', 'DAVID', 'SARA', 'ALBERTO', 'RUTH',
    'ALFREDO', 'MÓNICA', 'VICENTE', 'SILVIA', 'JOAQUÍN', 'NURIA', 'RAMÓN',
    'BEATRIZ', 'FERNANDO', 'CELIA', 'ÓSCAR', 'MARTA', 'HÉCTOR', 'ÁLVARO',
    'LUCÍA', 'ADRIÁN', 'PAULA', 'RUBÉN', 'CLAUDIA', 'IVÁN', 'ÁNGELA',
    'JULIÁN', 'VERÓNICA', 'TOMÁS', 'INÉS', 'EDUARDO', 'NATALIA', 'JESÚS',
    'ANDREA', 'EMILIO', 'CAMILA', 'ARTURO', 'VALERIA', 'ROBERTO', 'ALEJANDRA',
    'RICARDO', 'GABRIELA', 'ALFONSO', 'LILIANA', 'GUILLERMO', 'ADRIANA',
    'ENRIQUE', 'CAROLINA', 'MAURICIO', 'VANESSA', 'ESTEBAN', 'MARIANA',
    'FELIPE', 'GLORIA', 'SEBASTIÁN', 'ROCIO', 'LEONARDO', 'CONCEPCIÓN',
    'GERMÁN', 'ESPERANZA', 'RAÚL', 'MERCEDES', 'IGNACIO', 'NIEVES',
    'SANTIAGO', 'EVA', 'DANIEL', 'ROSA', 'ALEJANDRO', 'LIDIA',
    'HUGO', 'SUSANA', 'MARTÍN', 'AURORA', 'LÁZARO', 'PALOMA',
    'NÉSTOR', 'AMANDA', 'FÉLIX', 'BEGOÑA', 'LUCAS', 'YOLANDA',
    'GONZALO', 'CECILIA', 'JORGE', 'ROCIO', 'LUISA', 'MARIO',
    'ÁGUEDA', 'MARCOS', 'NOELIA', 'GUSTAVO', 'MACARENA', 'SAMUEL',
    'CATALINA', 'ÁNGELES', 'JOAQUÍN', 'ANA', 'BENJAMÍN', 'CARMEN',
    'FABIÁN', 'PILAR', 'LEANDRO', 'CONSUELO', 'MATEO', 'ÁFRICA',
    'NICOLÁS', 'ALBA', 'MAXIMILIANO', 'ÁGATA', 'EMILIANO', 'JULIETA',
    'GABRIEL', 'AURORA', 'VALENTÍN', 'NORMA', 'EZEQUIEL', 'FLORENCIA',
    'DEMETRIO', 'LORENA', 'FAUSTO', 'LUCÍA', 'HILARIO', 'MARTA',
    'GERVASIO', 'CONSTANZA', 'NAZARIO', 'EUGENIA', 'GAEL', 'SANDRA',
    'ÁXEL', 'DIANA', 'ODÍN', 'TANIA', 'YAGO', 'SABRINA',
    'ANDRÉS', 'FERNANDA', 'BAUTISTA', 'PAULINA', 'MATÍAS', 'JIMENA',
    'THIAGO', 'VALENTINA', 'BENITO', 'ANTONIA', 'FAUSTINO',
    'ESTEFANÍA', 'PONCIANO', 'GENOVEVA', 'SATURNINO', 'PETRONILA',
    'EVARISTO', 'JOSAFAT', 'BASILISA', 'FLORENCIO', 'GERTRUDIS',
    'APOLINAR', 'LEOCADIA', 'CRISPÍN', 'HERMINIA', 'BONIFACIO',
    'EDUVIGES', 'RUTILIO', 'SEVERA', 'EULOGIO', 'ASCENSIÓN',
    'JENARO', 'TRINIDAD', 'ABELARDO', 'ASUNCIÓN', 'ADOLFO',
    'SOCORRO', 'HIGINIO', 'MILAGROS', 'VITO', 'ROSARIO',
    'FLORENCIO', 'BELÉN', 'LUCIO', 'DÉBORA', 'EMETERIO',
    'EVANGELINA', 'CIRIACO', 'LEONOR', 'DÁMASO', 'TEOFILA',
    'SIXTO', 'MATILDE', 'HERMÓGENES', 'OFELIA', 'POLICARPO',
    'RAMONA', 'CAYETANO', 'PRUDENCIA', 'CELESTINO', 'REMEDIOS',
    'SALVADOR', 'ÁGATA', 'AARÓN', 'ABIGAIL', 'ABEL', 'ADÁN',
    'AGUSTÍN', 'ALICIA', 'ALONSO', 'AMAIA', 'ANÍBAL', 'ARACELI',
    'ARÍSTIDES', 'AZUCENA', 'BALBINA', 'BARTOLOMÉ', 'BERNARDINO',
    'BLANCA', 'CANDELARIA', 'CASIMIRO', 'CLOTILDE', 'CLEMENTE',
    'CRUZ', 'DAMIÁN', 'DOROTEA', 'ELÍAS', 'ENCARNACIÓN',
    'EULALIA', 'FILOMENA', 'GREGORIO', 'GUIOMAR', 'HERIBERTO',
    'JACINTA', 'JERÓNIMO', 'JUSTINA', 'LAUREANO', 'LIBRADA',
    'LISANDRO', 'LUCRECIA', 'MACEDONIO', 'MANUELA', 'NARCISO',
    'NATIVIDAD', 'NORBERTO', 'OLIMPIA', 'PASCUAL', 'PAZ',
    'PRISCILLA', 'PURIFICACIÓN', 'REFUGIO', 'ROGELIO', 'SALOMÉ',
    'SEGUNDO', 'SERVANDO', 'SILVESTRE', 'TELÉSFORO', 'URSULA',
    'VALERIANO', 'VICENTA', 'WILFRIDO', 'XIMENA', 'ZACARÍAS',
    'ZENÓN', 'ZOILA'
];

$surnames = [
    'GARCÍA', 'RODRÍGUEZ', 'MARTÍNEZ', 'LÓPEZ', 'HERNÁNDEZ', 'GONZÁLEZ',
    'PÉREZ', 'SÁNCHEZ', 'RAMÍREZ', 'TORRES', 'FLORES', 'RIVERA',
    'GÓMEZ', 'DÍAZ', 'CRUZ', 'MORALES', 'ORTIZ', 'GUTIÉRREZ',
    'CHÁVEZ', 'REYES', 'ÁLVAREZ', 'CASTILLO', 'JIMÉNEZ', 'VÁZQUEZ',
    'ROMERO', 'MÉNDEZ', 'NAVARRO', 'RUIZ', 'DOMÍNGUEZ', 'DELGADO',
    'MUÑOZ', 'ÁLVAREZ', 'FERNÁNDEZ', 'SANTOS', 'IBAÑEZ', 'VELÁZQUEZ',
    'AGUILAR', 'SUÁREZ', 'CÁRDENAS', 'BARRERA', 'PARRA', 'MÁRQUEZ',
    'CÁCERES', 'QUINTERO', 'NIETO', 'BÉJAR', 'MÚÑOZ',
    'CÓRDOVA', 'VALDÉS', 'MIRANDA', 'GUZMÁN', 'PEÑA', 'GALLARDO',
    'ARCINIEGA', 'BECERRA', 'BERMÚDEZ', 'BOLAÑOS', 'BURGOS',
    'CALDERÓN', 'CAMACHO', 'CAMPOS', 'CARRANZA', 'CASARES',
    'CÁSTRO', 'CERVANTES', 'CONTRERAS', 'CORONA', 'CORONADO',
    'DE LA CRUZ', 'DE LA ROSA', 'DEL VALLE', 'DURÁN', 'ECHEVERRÍA',
    'ENRÍQUEZ', 'ESCOBAR', 'ESPARZA', 'ESPINOZA', 'ESTÉVEZ',
    'FUENTES', 'GALLEGOS', 'GAMBOA', 'GODOY', 'GUEVARA',
    'HERRERA', 'HUERTA', 'LEAL', 'LEÓN', 'LOERA', 'LUJÁN',
    'MALDONADO', 'MANRÍQUEZ', 'MATÍAS', 'MEDRANO', 'MELÉNDEZ',
    'MONTERO', 'MONTOYA', 'NARVÁEZ', 'NÚÑEZ', 'OCAMPO',
    'OLIVARES', 'ORDÓÑEZ', 'PACHECO', 'PALACIOS', 'PALOMINO',
    'PAZ', 'PEDRAZA', 'PELÁEZ', 'PIMENTEL', 'PONCE',
    'PORTILLO', 'PRADO', 'QUEZADA', 'QUINTANILLA', 'QUIÑONES',
    'RANGEL', 'RIOS', 'ROBLES', 'ROCHA', 'ROSALES',
    'SALAZAR', 'SAMANIEGO', 'SANABRIA', 'SANDOVAL', 'SANTACRUZ',
    'SANTIAGO', 'SERRANO', 'SOLANO', 'SOLÍS', 'SOSA',
    'TAMAYO', 'TEJADA', 'TOVAR', 'TREVIÑO', 'TRUJILLO',
    'ULLOA', 'VALADEZ', 'VALENZUELA', 'VALLADARES', 'VARELA',
    'VELASCO', 'VÉLEZ', 'VERA', 'VIGIL', 'VILLALOBOS',
    'VILLANUEVA', 'VILLARREAL', 'VILLEGAS', 'YÁÑEZ', 'ZAMORA',
    'ZARATE', 'ZAVALA', 'ZEPEDA', 'HURTADO', 'QUIROZ',
    'ACEVEDO', 'BAUTISTA', 'BENÍTEZ', 'CABRERA', 'CAMARENA',
    'CARRILLO', 'CASTAÑEDA', 'COVARRUBIAS', 'ESPINOSA', 'FONSECA',
    'GAONA', 'GUERRA', 'HINOJOSA', 'IBARRA', 'INFANTE',
    'JUÁREZ', 'LARA', 'LOZANO', 'LUCERO', 'MACÍAS',
    'MAGAÑA', 'MARÍN', 'MATAMOROS', 'MIRANDA', 'MONREAL',
    'MORA', 'MORENO', 'NEGRETE', 'OLGUÍN', 'OSORIO',
    'PADILLA', 'PARTIDA', 'PAZ', 'PIZANO', 'PLASCENCIA',
    'PRADO', 'PUENTE', 'RENDÓN', 'ROBLEDO', 'SALCEDO',
    'SAMPERIO', 'SEDANO', 'SEGURA', 'SOTELO', 'TAPIA',
    'URIAS', 'VALTIERRA', 'VÁZQUEZ', 'VILLARREAL', 'ZAYAS',
    'ACUÑA', 'BAHENA', 'BARRIENTOS', 'BETANCOURT', 'BRAVO',
    'CABRAL', 'CALVILLO', 'CARREÓN', 'CEDILLO', 'CERVANTES',
    'CORONEL', 'CUÉLLAR', 'DEANDO', 'DELEÓN', 'ESCOBEDO',
    'FABIÁN', 'GALÁN', 'HARO', 'JARAMILLO', 'LOMELÍ',
    'MADERA', 'MARTÍN', 'MEJÍA', 'MELGAR', 'MOLINA',
    'MONCADA', 'MURO', 'OLIVAS', 'PABLO', 'PAREDES',
    'PECINA', 'PIÑA', 'QUIROGA', 'REGALADO', 'RIQUELME',
    'RIVADENEIRA', 'ROJO', 'SALDAÑA', 'SALGADO', 'SISNEROS',
    'TÉLLEZ', 'VALDIVIA', 'VENTURA', 'VIDAL', 'ZAMBRANO',
    'ARRIAGA', 'BOBADILLA', 'BOTELLO', 'BUENROSTRO', 'CALLEJA',
    'CANTÚ', 'CARRASCO', 'CASTELLANOS', 'CERDA', 'CHAVARRÍA',
    'COLÍN', 'CORRAL', 'CUENCA', 'DELGADILLO', 'ESCOBAR',
    'ESPINO', 'FERRER', 'GALINDO', 'GARIBAY', 'GIRÓN',
    'HIGUERA', 'HUERTA', 'IZQUIERDO', 'LIZÁRRAGA', 'LLANOS',
    'MARTÍNEZ', 'MONJARÁS', 'NAJERA', 'NAVARRO', 'OCAÑA',
    'OLMOS', 'ORELLANA', 'PATIÑO', 'PEDROZA', 'PÉREZ',
    'PULIDO', 'QUINTERO', 'RAMOS', 'REYNOSO', 'RIOSECO',
    'RIVERA', 'RUBIO', 'RUIZ', 'SALINAS', 'SAMANIEGO',
    'SANDOVAL', 'SAUCEDO', 'SEPÚLVEDA', 'SOSA', 'TOLEDO',
    'URIBE', 'VALDÉS', 'VALLES', 'VILLASANA', 'ZÚÑIGA'
];

function removeAccents($str) {
    $from = ['Á','É','Í','Ó','Ú','Ü','Ñ','á','é','í','ó','ú','ü','ñ'];
    $to   = ['A','E','I','O','U','U','N','a','e','i','o','u','u','n'];
    return str_replace($from, $to, $str);
}

function generateFullName($firstNames, $surnames) {
    $fn = $firstNames[array_rand($firstNames)];
    $sn1 = $surnames[array_rand($surnames)];
    $sn2 = $surnames[array_rand($surnames)];
    $corrected = $fn . ' ' . $sn1 . ' ' . $sn2;
    $noAccent = removeAccents($corrected);
    return [$noAccent, $corrected];
}

$conn->query("TRUNCATE TABLE personas");

$stmt = $conn->prepare("INSERT INTO personas (nombre_completo, correccion) VALUES (?, ?)");
$totalNames = 50000;
$batchSize = 500;
$inserted = 0;

echo "Generando $totalNames nombres...\n";

$conn->begin_transaction();
for ($i = 1; $i <= $totalNames; $i++) {
    [$noAccent, $corrected] = generateFullName($firstNames, $surnames);
    $stmt->bind_param('ss', $noAccent, $corrected);
    $stmt->execute();

    if ($i % $batchSize === 0) {
        $conn->commit();
        echo "Insertados $i nombres...\n";
        $conn->begin_transaction();
    }
}
$conn->commit();

echo "Completado: $totalNames nombres insertados.\n";

$stmt->close();
$conn->close();
