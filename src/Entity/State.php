<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of SDK within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Entity;

/**
 * Class State
 *
 * Provides access to all states available in the PayPal REST API.
 *
 * @package Wirecard\PaymentSdk\Entity
 * @since 3.4.0
 */
class State
{
    const ARGENTINA = "ARGENTINA";
    const BRAZIL = "BRAZIL";
    const CANADA = "CANADA";
    const INDIA = "INDIA";
    const INDONESIA = "INDONESIA";
    const ITALY = "ITALY";
    const JAPAN = "JAPAN";
    const MEXICO = "MEXICO";
    const THAILAND = "THAILAND";
    const UNITED_STATES = "US";

    private $country;
    private $name;
    private $code;

    private $statesMap = [
        self::ARGENTINA => [
            "buenos aires" => "BUENOS AIRES",
            "distrito federal" => "CIUDAD AUTÓNOMA DE BUENOS AIRES",
            "catamarca" => "CATAMARCA",
            "chaco" => "CHACO",
            "chubut" => "CHUBUT",
            "corrientes" => "CORRIENTES",
            "cordoba" => "CÓRDOBA",
            "entre rios" => "ENTRE RÍOS",
            "formosa" => "FORMOSA",
            "jujuy" => "JUJUY",
            "la pampa" => "LA PAMPA",
            "la rioja" => "LA RIOJA",
            "mendoza" => "MENDOZA",
            "misiones" => "MISIONES",
            "neuquen" => "NEUQUÉN",
            "rio negro" => "RÍO NEGRO",
            "salta" => "SALTA",
            "san juan" => "SAN JUAN",
            "san luis" => "SAN LUIS",
            "santa cruz" => "SANTA CRUZ",
            "santa fe" => "SANTA FE",
            "santiago del estero" => "SANTIAGO DEL ESTERO",
            "tierra del fuego" => "TIERRA DEL FUEGO",
            "tucuman" => "TUCUMÁN",
        ],

        self::BRAZIL => [
            "acre" => "AC",
            "alagoas" => "AL",
            "amapa" => "AP",
            "amazonas" => "AM",
            "bahia" => "BA",
            "ceara" => "CE",
            "distrito federal" => "DF",
            "espirito santo" => "ES",
            "goias" => "GO",
            "maranhao" => "MA",
            "mato grosso" => "MT",
            "mato grosso do sul" => "MS",
            "minas gerais" => "MG",
            "parana" => "PR",
            "paraiba" => "PB",
            "para" => "PA",
            "pernambuco" => "PE",
            "piaui" => "PI",
            "rio grande do norte" => "RN",
            "rio grande do sul" => "RS",
            "rio de janeiro" => "RJ",
            "rondonia" => "RO",
            "roraima" => "RR",
            "santa catarina" => "SC",
            "sergipe" => "SE",
            "sao paulo" => "SP",
            "tocantins" => "TO",
        ],

        self::CANADA => [
            "alberta" => "AB",
            "british columbia" => "BC",
            "manitoba" => "MB",
            "new brunswick" => "NB",
            "newfoundland and labrador" => "NL",
            "northwest territories" => "NT",
            "nova scotia" => "NS",
            "nunavut" => "NU",
            "ontario" => "ON",
            "prince edward island" => "PE",
            "quebec" => "QC",
            "saskatchewan" => "SK",
            "yukon" => "YT",
        ],

        self::INDIA => [
            "andaman and nicobar islands" => "Andaman and Nicobar Islands",
            "andhra pradesh" => "Andhra Pradesh",
            "army post office" => "APO",
            "arunachal pradesh" => "Arunachal Pradesh",
            "assam" => "Assam",
            "bihar" => "Bihar",
            "chandigarh" => "Chandigarh",
            "chhattisgarh" => "Chhattisgarh",
            "dadra and nagar haveli" => "Dadra and Nagar Haveli",
            "daman and diu" => "Daman and Diu",
            "goa" => "Goa",
            "gujarat" => "Gujarat",
            "haryana" => "Haryana",
            "himachal pradesh" => "Himachal Pradesh",
            "jammu and kashmir" => "Jammu and Kashmir",
            "jharkhand" => "Jharkhand",
            "karnataka" => "Karnataka",
            "kerala" => "Kerala",
            "lakshadweep" => "Lakshadweep",
            "madhya pradesh" => "Madhya Pradesh",
            "maharashtra" => "Maharashtra",
            "manipur" => "Manipur",
            "meghalaya" => "Meghalaya",
            "mizoram" => "Mizoram",
            "nagaland" => "Nagaland",
            "delhi" => "Delhi (NCT)",
            "odisha" => "Odisha",
            "puducherry" => "Puducherry",
            "punjab" => "Punjab",
            "rajasthan" => "Rajasthan",
            "sikkim" => "Sikkim",
            "tamil nadu" => "Tamil Nadu",
            "telangana" => "Telangana",
            "tripura" => "Tripura",
            "uttar pradesh" => "Uttar Pradesh",
            "uttarakhand" => "Uttarakhand",
            "west bengal" => "West Bengal",
        ],

        self::INDONESIA => [
            "bali" => "ID-BA",
            "bangka belitung" => "ID-BB",
            "banten" => "ID-BT",
            "bengkulu" => "ID-BE",
            "yogyakarta" => "ID-YO",
            "jakarta" => "ID-JK",
            "gorontalo" => "ID-GO",
            "jambi" => "ID-JA",
            "jawa barat" => "ID-JB",
            "jawa tengah" => "ID-JT",
            "jawa timur" => "ID-JI",
            "kalimantan barat" => "ID-KB",
            "kalimantan selatan" => "ID-KS",
            "kalimantan tengah" => "ID-KT",
            "kalimantan timur" => "ID-KI",
            "kalimantan utara" => "ID-KU",
            "kepulauan riau" => "ID-KR",
            "lampung" => "ID-LA",
            "maluku" => "ID-MA",
            "maluku utara" => "ID-MU",
            "nanggroe aceh darussalam" => "ID-AC",
            "nusa tenggara barat" => "ID-NB",
            "nusa tenggara timur" => "ID-NT",
            "papua" => "ID-PA",
            "papua barat" => "ID-PB",
            "riau" => "ID-RI",
            "sulawesi barat" => "ID-SR",
            "sulawesi selatan" => "ID-SN",
            "sulawesi tengah" => "ID-ST",
            "sulawesi tenggara" => "ID-SG",
            "sulawesi utara" => "ID-SA",
            "sumatera barat" => "ID-SB",
            "sumatera selatan" => "ID-SS",
            "sumatera utara" => "ID-SU",
        ],

        self::ITALY => [
            "agrigento" => "AG",
            "alessandria" => "AL",
            "ancona" => "AN",
            "aosta" => "AO",
            "arezzo" => "AR",
            "ascoli piceno" => "AP",
            "asti" => "AT",
            "avellino" => "AV",
            "bari" => "BA",
            "barletta-andria-trani" => "BT",
            "belluno" => "BL",
            "benevento" => "BN",
            "bergamo" => "BG",
            "biella" => "BI",
            "bologna" => "BO",
            "bolzano" => "BZ",
            "brescia" => "BS",
            "brindisi" => "BR",
            "cagliari" => "CA",
            "caltanissetta" => "CL",
            "campobasso" => "CB",
            "carbonia-iglesias" => "CI",
            "caserta" => "CE",
            "catania" => "CT",
            "catanzaro" => "CZ",
            "chieti" => "CH",
            "como" => "CO",
            "cosenza" => "CS",
            "cremona" => "CR",
            "crotone" => "KR",
            "cuneo" => "CN",
            "enna" => "EN",
            "fermo" => "FM",
            "ferrara" => "FE",
            "firenze" => "FI",
            "foggia" => "FG",
            "forli-cesena" => "FC",
            "frosinone" => "FR",
            "genova" => "GE",
            "gorizia" => "GO",
            "grosseto" => "GR",
            "imperia" => "IM",
            "isernia" => "IS",
            "l'aquila" => "AQ",
            "la spezia" => "SP",
            "latina" => "LT",
            "lecce" => "LE",
            "lecco" => "LC",
            "livorno" => "LI",
            "lodi" => "LO",
            "lucca" => "LU",
            "macerata" => "MC",
            "mantova" => "MN",
            "massa-carrara" => "MS",
            "matera" => "MT",
            "medio campidano" => "VS",
            "messina" => "ME",
            "milano" => "MI",
            "modena" => "MO",
            "monza e della brianza" => "MB",
            "napoli" => "NA",
            "novara" => "NO",
            "nuoro" => "NU",
            "ogliastra" => "OG",
            "olbia-tempio" => "OT",
            "oristano" => "OR",
            "padova" => "PD",
            "palermo" => "PA",
            "parma" => "PR",
            "pavia" => "PV",
            "perugia" => "PG",
            "pesaro e urbino" => "PU",
            "pescara" => "PE",
            "piacenza" => "PC",
            "pisa" => "PI",
            "pistoia" => "PT",
            "pordenone" => "PN",
            "potenza" => "PZ",
            "prato" => "PO",
            "ragusa" => "RG",
            "ravenna" => "RA",
            "reggio calabria" => "RC",
            "reggio emilia" => "RE",
            "rieti" => "RI",
            "rimini" => "RN",
            "roma" => "RM",
            "rovigo" => "RO",
            "salerno" => "SA",
            "sassari" => "SS",
            "savona" => "SV",
            "siena" => "SI",
            "siracusa" => "SR",
            "sondrio" => "SO",
            "taranto" => "TA",
            "teramo" => "TE",
            "terni" => "TR",
            "torino" => "TO",
            "trapani" => "TP",
            "trento" => "TN",
            "treviso" => "TV",
            "trieste" => "TS",
            "udine" => "UD",
            "varese" => "VA",
            "venezia" => "VE",
            "verbano-cusio-ossola" => "VB",
            "vercelli" => "VC",
            "verona" => "VR",
            "vibo valentia" => "VV",
            "vicenza" => "VI",
            "viterbo" => "VT",
        ],

        self::JAPAN => [
            "aichi" => "AICHI-KEN",
            "akita" => "AKITA-KEN",
            "aomori" => "AOMORI-KEN",
            "chiba" => "CHIBA-KEN",
            "ehime" => "EHIME-KEN",
            "fukui" => "FUKUI-KEN",
            "fukuoka" => "FUKUOKA-KEN",
            "fukushima" => "FUKUSHIMA-KEN",
            "gifu" => "GIFU-KEN",
            "gunma" => "GUNMA-KEN",
            "hiroshima" => "HIROSHIMA-KEN",
            "hokkaido" => "HOKKAIDO",
            "hyogo" => "HYOGO-KEN",
            "ibaraki" => "IBARAKI-KEN",
            "ishikawa" => "ISHIKAWA-KEN",
            "iwate" => "IWATE-KEN",
            "kagawa" => "KAGAWA-KEN",
            "kagoshima" => "KAGOSHIMA-KEN",
            "kanagawa" => "KANAGAWA-KEN",
            "kochi" => "KOCHI-KEN",
            "kumamoto" => "KUMAMOTO-KEN",
            "kyoto" => "KYOTO-FU",
            "mie" => "MIE-KEN",
            "miyagi" => "MIYAGI-KEN",
            "miyazaki" => "MIYAZAKI-KEN",
            "nagano" => "NAGANO-KEN",
            "nagasaki" => "NAGASAKI-KEN",
            "nara" => "NARA-KEN",
            "niigata" => "NIIGATA-KEN",
            "oita" => "OITA-KEN",
            "okayama" => "OKAYAMA-KEN",
            "okinawa" => "OKINAWA-KEN",
            "osaka" => "OSAKA-FU",
            "saga" => "SAGA-KEN",
            "saitama" => "SAITAMA-KEN",
            "shiga" => "SHIGA-KEN",
            "shimane" => "SHIMANE-KEN",
            "shizuoka" => "SHIZUOKA-KEN",
            "tochigi" => "TOCHIGI-KEN",
            "tokushima" => "TOKUSHIMA-KEN",
            "tokyo" => "TOKYO-TO",
            "tottori" => "TOTTORI-KEN",
            "toyama" => "TOYAMA-KEN",
            "wakayama" => "WAKAYAMA-KEN",
            "yamagata" => "YAMAGATA-KEN",
            "yamaguchi" => "YAMAGUCHI-KEN",
            "yamanashi" => "YAMANASHI-KEN",
        ],

        self::MEXICO => [
            "aguascalientes" => "AGS",
            "baja california" => "BC",
            "baja california sur" => "BCS",
            "campeche" => "CAMP",
            "chiapas" => "CHIS",
            "chihuahua" => "CHIH",
            "coahuila" => "COAH",
            "colima" => "COL",
            "distrito federal" => "DF",
            "durango" => "DGO",
            "estado de méxico" => "MEX",
            "guanajuato" => "GTO",
            "guerrero" => "GRO",
            "hidalgo" => "HGO",
            "jalisco" => "JAL",
            "michoacán" => "MICH",
            "morelos" => "MOR",
            "nayarit" => "NAY",
            "nuevo león" => "NL",
            "oaxaca" => "OAX",
            "puebla" => "PUE",
            "querétaro" => "QRO",
            "quintana roo" => "Q ROO",
            "san luis potosí" => "SLP",
            "sinaloa" => "SIN",
            "sonora" => "SON",
            "tabasco" => "TAB",
            "tamaulipas" => "TAMPS",
            "tlaxcala" => "TLAX",
            "veracruz" => "VER",
            "yucatán" => "YUC",
            "zacatecas" => "ZAC",
        ],

        self::THAILAND => [
            "amnat charoen" => "Amnat Charoen",
            "ang thong" => "Ang Thong",
            "bangkok" => "Bangkok",
            "bueng kan" => "Bueng Kan",
            "buri ram" => "Buri Ram",
            "chachoengsao" => "Chachoengsao",
            "chai nat" => "Chai Nat",
            "chaiyaphum" => "Chaiyaphum",
            "chanthaburi" => "Chanthaburi",
            "chiang mai" => "Chiang Mai",
            "chiang rai" => "Chiang Rai",
            "chon buri" => "Chon Buri",
            "chumphon" => "Chumphon",
            "kalasin" => "Kalasin",
            "kamphaeng phet" => "Kamphaeng Phet",
            "kanchanaburi" => "Kanchanaburi",
            "khon kaen" => "Khon Kaen",
            "krabi" => "Krabi",
            "lampang" => "Lampang",
            "lamphun" => "Lamphun",
            "loei" => "Loei",
            "lop buri" => "Lop Buri",
            "mae hong son" => "Mae Hong Son",
            "maha sarakham" => "Maha Sarakham",
            "mukdahan" => "Mukdahan",
            "nakhon nayok" => "Nakhon Nayok",
            "nakhon pathom" => "Nakhon Pathom",
            "nakhon phanom" => "Nakhon Phanom",
            "nakhon ratchasima" => "Nakhon Ratchasima",
            "nakhon sawan" => "Nakhon Sawan",
            "nakhon si thammarat" => "Nakhon Si Thammarat",
            "nan" => "Nan",
            "narathiwat" => "Narathiwat",
            "nong bua lamphu" => "Nong Bua Lamphu",
            "nong khai" => "Nong Khai",
            "nonthaburi" => "Nonthaburi",
            "pathum thani" => "Pathum Thani",
            "pattani" => "Pattani",
            "phang nga" => "Phang Nga",
            "phatthalung" => "Phatthalung",
            "phatthaya" => "Phatthaya",
            "phayao" => "Phayao",
            "phetchabun" => "Phetchabun",
            "phetchaburi" => "Phetchaburi",
            "phichit" => "Phichit",
            "phitsanulok" => "Phitsanulok",
            "phra nakhon si ayutthaya" => "Phra Nakhon Si Ayutthaya",
            "phrae" => "Phrae",
            "phuket" => "Phuket",
            "prachin buri" => "Prachin Buri",
            "prachuap khiri khan" => "Prachuap Khiri Khan",
            "ranong" => "Ranong",
            "ratchaburi" => "Ratchaburi",
            "rayong" => "Rayong",
            "roi et" => "Roi Et",
            "sa kaeo" => "Sa Kaeo",
            "sakon nakhon" => "Sakon Nakhon",
            "samut prakan" => "Samut Prakan",
            "samut sakhon" => "Samut Sakhon",
            "samut songkhram" => "Samut Songkhram",
            "saraburi" => "Saraburi",
            "satun" => "Satun",
            "si sa ket" => "Si Sa Ket",
            "sing buri" => "Sing Buri",
            "songkhla" => "Songkhla",
            "sukhothai" => "Sukhothai",
            "suphan buri" => "Suphan Buri",
            "surat thani" => "Surat Thani",
            "surin" => "Surin",
            "tak" => "Tak",
            "trang" => "Trang",
            "trat" => "Trat",
            "ubon ratchathani" => "Ubon Ratchathani",
            "udon thani" => "Udon Thani",
            "uthai thani" => "Uthai Thani",
            "uttaradit" => "Uttaradit",
            "yala" => "Yala",
            "yasothon" => "Yasothon",
        ],

        self::UNITED_STATES => [
            "alabama" => "AL",
            "alaska" => "AK",
            "arizona" => "AZ",
            "arkansas" => "AR",
            "california" => "CA",
            "colorado" => "CO",
            "connecticut" => "CT",
            "delaware" => "DE",
            "district of columbia" => "DC",
            "florida" => "FL",
            "georgia" => "GA",
            "hawaii" => "HI",
            "idaho" => "ID",
            "illinois" => "IL",
            "indiana" => "IN",
            "iowa" => "IA",
            "kansas" => "KS",
            "kentucky" => "KY",
            "louisiana" => "LA",
            "maine" => "ME",
            "maryland" => "MD",
            "massachusetts" => "MA",
            "michigan" => "MI",
            "minnesota" => "MN",
            "mississippi" => "MS",
            "missouri" => "MO",
            "montana" => "MT",
            "nebraska" => "NE",
            "nevada" => "NV",
            "new hampshire" => "NH",
            "new jersey" => "NJ",
            "new mexico" => "NM",
            "new york" => "NY",
            "north carolina" => "NC",
            "north dakota" => "ND",
            "ohio" => "OH",
            "oklahoma" => "OK",
            "oregon" => "OR",
            "pennsylvania" => "PA",
            "puerto rico" => "PR",
            "rhode island" => "RI",
            "south carolina" => "SC",
            "south dakota" => "SD",
            "tennessee" => "TN",
            "texas" => "TX",
            "utah" => "UT",
            "vermont" => "VT",
            "virginia" => "VA",
            "washington" => "WA",
            "west virginia" => "WV",
            "wisconsin" => "WI",
            "wyoming" => "WY",

            "armed forces americas" => "AA",
            "armed forces europe" => "AE",
            "armed forces pacific" => "AP",

            "american samoa" => "AS",
            "federated states of micronesia" => "FM",
            "guam" => "GU",
            "marshall islands" => "MH",
            "northern mariana islands" => "MP",
            "palau" => "PW",
            "virgin islands" => "VI",
        ],
    ];

    /**
     * @param string $country Set country to be used for determining state.
     * @since 3.4.0
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string Returns the currently set country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $name Set name of province/state
     * @since 3.4.0
     */
    public function setName($name)
    {
        $this->setCode($name);
        $this->name = $name;
    }

    /**
     * @return string The name of the currently set province/state
     * @since 3.4.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name sets the correct province code based on the provided province name.
     * @since 3.4.0
     */
    private function setCode($name)
    {
        $sanitizedStateName = strtolower(
            $this->stripAccents($name)
        );

        if (array_key_exists($sanitizedStateName, $this->statesMap[$this->country])) {
            $this->code = $this->statesMap[$this->country][$sanitizedStateName];
            return;
        }

        $this->code = null;
    }

    /**
     * @return string The code of the chosen state
     * @since 3.4.0
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $string A string containing various accents
     * @return string String written using only ASCII characters.
     * @since 3.4.0
     */
    private function stripAccents($string)
    {
        return strtr(
            utf8_decode($string),
            utf8_decode(
                'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'
            ),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }
}
