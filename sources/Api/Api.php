<?php

namespace IPS\printfulintegration;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

use function defined;
use function header;

/**
 * Api Class
 *
 * @mixin \IPS\printfulintegration\Api
 */
class _Api extends \IPS\Patterns\Singleton
{

    public $apiUrl = 'https://api.printful.com/';
    public $requestUrl;

    protected static $instance = NULL;

    // Countries to which printful can do deliveries
    public static $countries = [
        'AD' => [
            'name' => "Andorra",
            'states' => []
        ],
        'AE' => [
            'name' => "United Arab Emirates",
            'states' => []
        ],
        'AF' => [
            'name' => "Afghanistan",
            'states' => []
        ],
        'AG' => [
            'name' => "Antigua and Barbuda",
            'states' => []
        ],
        'AI' => [
            'name' => "Anguilla",
            'states' => []
        ],
        'AL' => [
            'name' => "Albania",
            'states' => []
        ],
        'AM' => [
            'name' => "Armenia",
            'states' => []
        ],
        'AN' => [
            'name' => "Netherlands Antilles",
            'states' => []
        ],
        'AO' => [
            'name' => "Angola",
            'states' => []
        ],
        'AQ' => [
            'name' => "Antarctica",
            'states' => []
        ],
        'AR' => [
            'name' => "Argentina",
            'states' => []
        ],
        'AS' => [
            'name' => "American Samoa",
            'states' => []
        ],
        'AT' => [
            'name' => "Austria",
            'states' => []
        ],
        'AU' => [
            'name' => "Australia",
            'states' => [
                'Australian Capital Territory' => "ACT",
                'New South Wales' => "NSW",
                'Northern Territory' => "NT",
                'Queensland' => "QLD",
                'South Australia' => "SA",
                'Tasmania' => "TAS",
                'Victoria' => "VIC",
                'Western Australia' => "WA",
            ]
        ],
        'AW' => [
            'name' => "Aruba",
            'states' => []
        ],
        'AZ' => [
            'name' => "Azerbaijan",
            'states' => []
        ],
        'BA' => [
            'name' => "Bosnia and Herzegovina",
            'states' => []
        ],
        'BB' => [
            'name' => "Barbados",
            'states' => []
        ],
        'BD' => [
            'name' => "Bangladesh",
            'states' => []
        ],
        'BE' => [
            'name' => "Belgium",
            'states' => []
        ],
        'BF' => [
            'name' => "Burkina Faso",
            'states' => []
        ],
        'BG' => [
            'name' => "Bulgaria",
            'states' => []
        ],
        'BH' => [
            'name' => "Bahrain",
            'states' => []
        ],
        'BI' => [
            'name' => "Burundi",
            'states' => []
        ],
        'BJ' => [
            'name' => "Benin",
            'states' => []
        ],
        'BM' => [
            'name' => "Bermuda",
            'states' => []
        ],
        'BN' => [
            'name' => "Brunei Darussalam",
            'states' => []
        ],
        'BO' => [
            'name' => "Bolivia",
            'states' => []
        ],
        'BR' => [
            'name' => "Brazil",
            'states' => []
        ],
        'BS' => [
            'name' => "Bahamas",
            'states' => []
        ],
        'BT' => [
            'name' => "Bhutan",
            'states' => []
        ],
        'BV' => [
            'name' => "Bouvet Island",
            'states' => []
        ],
        'BW' => [
            'name' => "Botswana",
            'states' => []
        ],
        'BY' => [
            'name' => "Belarus",
            'states' => []
        ],
        'BZ' => [
            'name' => "Belize",
            'states' => []
        ],
        'CA' => [
            'name' => "Canada",
            'states' => [
                'Alberta' => "AB",
                'British Columbia' => "BC",
                'Manitoba' => "MB",
                'New Brunswick' => "NB",
                'Newfoundland and Labrador' => "NL",
                'Nova Scotia' => "NS",
                'Northwest Territories' => "NT",
                'Nunavut' => "NU",
                'Ontario' => "ON",
                'Prince Edward Island' => "PE",
                'Quebec' => "QC",
                'Saskatchewan' => "SK",
                'Yukon' => "YT",
            ]
        ],
        'CC' => [
            'name' => "Cocos (Keeling) Islands",
            'states' => []
        ],
        'CD' => [
            'name' => "Congo, the Democratic Republic of the",
            'states' => []
        ],
        'CF' => [
            'name' => "Central African Republic",
            'states' => []
        ],
        'CG' => [
            'name' => "Congo",
            'states' => []
        ],
        'CH' => [
            'name' => "Switzerland",
            'states' => []
        ],
        'CI' => [
            'name' => "Cote D'Ivoire",
            'states' => []
        ],
        'CK' => [
            'name' => "Cook Islands",
            'states' => []
        ],
        'CL' => [
            'name' => "Chile",
            'states' => []
        ],
        'CM' => [
            'name' => "Cameroon",
            'states' => []
        ],
        'CN' => [
            'name' => "China",
            'states' => []
        ],
        'CO' => [
            'name' => "Colombia",
            'states' => []
        ],
        'CR' => [
            'name' => "Costa Rica",
            'states' => []
        ],
        'CU' => [
            'name' => "Cuba, Republic of",
            'states' => []
        ],
        'CV' => [
            'name' => "Cape Verde",
            'states' => []
        ],
        'CW' => [
            'name' => "Curacao",
            'states' => []
        ],
        'CX' => [
            'name' => "Christmas Island",
            'states' => []
        ],
        'CY' => [
            'name' => "Cyprus",
            'states' => []
        ],
        'CZ' => [
            'name' => "Czech Republic",
            'states' => []
        ],
        'DE' => [
            'name' => "Germany",
            'states' => []
        ],
        'DJ' => [
            'name' => "Djibouti",
            'states' => []
        ],
        'DK' => [
            'name' => "Denmark",
            'states' => []
        ],
        'DM' => [
            'name' => "Dominica",
            'states' => []
        ],
        'DO' => [
            'name' => "Dominican Republic",
            'states' => []
        ],
        'DZ' => [
            'name' => "Algeria",
            'states' => []
        ],
        'EC' => [
            'name' => "Ecuador",
            'states' => []
        ],
        'EE' => [
            'name' => "Estonia",
            'states' => []
        ],
        'EG' => [
            'name' => "Egypt",
            'states' => []
        ],
        'EH' => [
            'name' => "Western Sahara",
            'states' => []
        ],
        'ER' => [
            'name' => "Eritrea",
            'states' => []
        ],
        'ES' => [
            'name' => "Spain",
            'states' => []
        ],
        'ET' => [
            'name' => "Ethiopia",
            'states' => []
        ],
        'FI' => [
            'name' => "Finland",
            'states' => []
        ],
        'FJ' => [
            'name' => "Fiji",
            'states' => []
        ],
        'FK' => [
            'name' => "Falkland Islands (Malvinas)",
            'states' => []
        ],
        'FM' => [
            'name' => "Micronesia, Federated States of",
            'states' => []
        ],
        'FO' => [
            'name' => "Faroe Islands",
            'states' => []
        ],
        'FR' => [
            'name' => "France",
            'states' => []
        ],
        'GA' => [
            'name' => "Gabon",
            'states' => []
        ],
        'GB' => [
            'name' => "United Kingdom",
            'states' => []
        ],
        'GD' => [
            'name' => "Grenada",
            'states' => []
        ],
        'GE' => [
            'name' => "Georgia",
            'states' => []
        ],
        'GF' => [
            'name' => "French Guiana",
            'states' => []
        ],
        'GG' => [
            'name' => "Guernsey",
            'states' => []
        ],
        'GH' => [
            'name' => "Ghana",
            'states' => []
        ],
        'GI' => [
            'name' => "Gibraltar",
            'states' => []
        ],
        'GL' => [
            'name' => "Greenland",
            'states' => []
        ],
        'GM' => [
            'name' => "Gambia",
            'states' => []
        ],
        'GN' => [
            'name' => "Guinea",
            'states' => []
        ],
        'GP' => [
            'name' => "Guadeloupe",
            'states' => []
        ],
        'GQ' => [
            'name' => "Equatorial Guinea",
            'states' => []
        ],
        'GR' => [
            'name' => "Greece",
            'states' => []
        ],
        'GS' => [
            'name' => "South Georgia and the South Sandwich Islands",
            'states' => []
        ],
        'GT' => [
            'name' => "Guatemala",
            'states' => []
        ],
        'GU' => [
            'name' => "Guam",
            'states' => []
        ],
        'GW' => [
            'name' => "Guinea-Bissau",
            'states' => []
        ],
        'GY' => [
            'name' => "Guyana",
            'states' => []
        ],
        'HK' => [
            'name' => "Hong Kong",
            'states' => []
        ],
        'HM' => [
            'name' => "Heard Island and Mcdonald Islands",
            'states' => []
        ],
        'HN' => [
            'name' => "Honduras",
            'states' => []
        ],
        'HR' => [
            'name' => "Croatia",
            'states' => []
        ],
        'HT' => [
            'name' => "Haiti",
            'states' => []
        ],
        'HU' => [
            'name' => "Hungary",
            'states' => []
        ],
        'ID' => [
            'name' => "Indonesia",
            'states' => []
        ],
        'IE' => [
            'name' => "Ireland",
            'states' => []
        ],
        'IL' => [
            'name' => "Israel",
            'states' => []
        ],
        'IM' => [
            'name' => "Isle of Man",
            'states' => []
        ],
        'IN' => [
            'name' => "India",
            'states' => []
        ],
        'IO' => [
            'name' => "British Indian Ocean Territory",
            'states' => []
        ],
        'IQ' => [
            'name' => "Iraq",
            'states' => []
        ],
        'IR' => [
            'name' => "Iran, Islamic Republic of",
            'states' => []
        ],
        'IS' => [
            'name' => "Iceland",
            'states' => []
        ],
        'IT' => [
            'name' => "Italy",
            'states' => []
        ],
        'JE' => [
            'name' => "Jersey",
            'states' => []
        ],
        'JM' => [
            'name' => "Jamaica",
            'states' => []
        ],
        'JO' => [
            'name' => "Jordan",
            'states' => []
        ],
        'JP' => [
            'name' => "Japan",
            'states' => [
                'Hokkaido' => "01",
                'Aomori' => "02",
                'Iwate' => "03",
                'Miyagi' => "04",
                'Akita' => "05",
                'Yamagata' => "06",
                'Fukushima' => "07",
                'Ibaraki' => "08",
                'Tochigi' => "09",
                'Gunma' => "10",
                'Saitama' => "11",
                'Chiba' => "12",
                'Tokyo' => "13",
                'Kanagawa' => "14",
                'Niigata' => "15",
                'Toyama' => "16",
                'Ishikawa' => "17",
                'Fukui' => "18",
                'Yamanashi' => "19",
                'Nagano' => "20",
                'Gifu' => "21",
                'Shizuoka' => "22",
                'Aichi' => "23",
                'Mie' => "24",
                'Shiga' => "25",
                'Kyoto' => "26",
                'Osaka' => "27",
                'Hyogo' => "28",
                'Nara' => "29",
                'Wakayama' => "30",
                'Tottori' => "31",
                'Shimane' => "32",
                'Okayama' => "33",
                'Hiroshima' => "34",
                'Yamaguchi' => "35",
                'Tokushima' => "36",
                'Kagawa' => "37",
                'Ehime' => "38",
                'Kochi' => "39",
                'Fukuoka' => "40",
                'Saga' => "41",
                'Nagasaki' => "42",
                'Kumamoto' => "43",
                'Oita' => "44",
                'Miyazaki' => "45",
                'Kagoshima' => "46",
                'Okinawa' => "47",
            ]
        ],
        'KE' => [
            'name' => "Kenya",
            'states' => []
        ],
        'KG' => [
            'name' => "Kyrgyzstan",
            'states' => []
        ],
        'KH' => [
            'name' => "Cambodia",
            'states' => []
        ],
        'KI' => [
            'name' => "Kiribati",
            'states' => []
        ],
        'KM' => [
            'name' => "Comoros",
            'states' => []
        ],
        'KN' => [
            'name' => "Saint Kitts and Nevis",
            'states' => []
        ],
        'KP' => [
            'name' => "Korea, Democratic People's Republic of",
            'states' => []
        ],
        'KR' => [
            'name' => "Korea, Republic of",
            'states' => []
        ],
        'KW' => [
            'name' => "Kuwait",
            'states' => []
        ],
        'KY' => [
            'name' => "Cayman Islands",
            'states' => []
        ],
        'KZ' => [
            'name' => "Kazakhstan",
            'states' => []
        ],
        'LA' => [
            'name' => "Lao People's Democratic Republic",
            'states' => []
        ],
        'LB' => [
            'name' => "Lebanon",
            'states' => []
        ],
        'LC' => [
            'name' => "Saint Lucia",
            'states' => []
        ],
        'LI' => [
            'name' => "Liechtenstein",
            'states' => []
        ],
        'LK' => [
            'name' => "Sri Lanka",
            'states' => []
        ],
        'LR' => [
            'name' => "Liberia",
            'states' => []
        ],
        'LS' => [
            'name' => "Lesotho",
            'states' => []
        ],
        'LT' => [
            'name' => "Lithuania",
            'states' => []
        ],
        'LU' => [
            'name' => "Luxembourg",
            'states' => []
        ],
        'LV' => [
            'name' => "Latvia",
            'states' => []
        ],
        'LY' => [
            'name' => "Libyan Arab Jamahiriya",
            'states' => []
        ],
        'MA' => [
            'name' => "Morocco",
            'states' => []
        ],
        'MC' => [
            'name' => "Monaco",
            'states' => []
        ],
        'MD' => [
            'name' => "Moldova, Republic of",
            'states' => []
        ],
        'ME' => [
            'name' => "Montenegro",
            'states' => []
        ],
        'MF' => [
            'name' => "Sint Maarten",
            'states' => []
        ],
        'MG' => [
            'name' => "Madagascar",
            'states' => []
        ],
        'MH' => [
            'name' => "Marshall Islands",
            'states' => []
        ],
        'MK' => [
            'name' => "Macedonia, the Former Yugoslav Republic of",
            'states' => []
        ],
        'ML' => [
            'name' => "Mali",
            'states' => []
        ],
        'MM' => [
            'name' => "Myanmar",
            'states' => []
        ],
        'MN' => [
            'name' => "Mongolia",
            'states' => []
        ],
        'MO' => [
            'name' => "Macao",
            'states' => []
        ],
        'MP' => [
            'name' => "Northern Mariana Islands",
            'states' => []
        ],
        'MQ' => [
            'name' => "Martinique",
            'states' => []
        ],
        'MR' => [
            'name' => "Mauritania",
            'states' => []
        ],
        'MS' => [
            'name' => "Montserrat",
            'states' => []
        ],
        'MT' => [
            'name' => "Malta",
            'states' => []
        ],
        'MU' => [
            'name' => "Mauritius",
            'states' => []
        ],
        'MV' => [
            'name' => "Maldives",
            'states' => []
        ],
        'MW' => [
            'name' => "Malawi",
            'states' => []
        ],
        'MX' => [
            'name' => "Mexico",
            'states' => []
        ],
        'MY' => [
            'name' => "Malaysia",
            'states' => []
        ],
        'MZ' => [
            'name' => "Mozambique",
            'states' => []
        ],
        'NA' => [
            'name' => "Namibia",
            'states' => []
        ],
        'NC' => [
            'name' => "New Caledonia",
            'states' => []
        ],
        'NE' => [
            'name' => "Niger",
            'states' => []
        ],
        'NF' => [
            'name' => "Norfolk Island",
            'states' => []
        ],
        'NG' => [
            'name' => "Nigeria",
            'states' => []
        ],
        'NI' => [
            'name' => "Nicaragua",
            'states' => []
        ],
        'NL' => [
            'name' => "Netherlands",
            'states' => []
        ],
        'NO' => [
            'name' => "Norway",
            'states' => []
        ],
        'NP' => [
            'name' => "Nepal",
            'states' => []
        ],
        'NR' => [
            'name' => "Nauru",
            'states' => []
        ],
        'NU' => [
            'name' => "Niue",
            'states' => []
        ],
        'NZ' => [
            'name' => "New Zealand",
            'states' => []
        ],
        'OM' => [
            'name' => "Oman",
            'states' => []
        ],
        'PA' => [
            'name' => "Panama",
            'states' => []
        ],
        'PE' => [
            'name' => "Peru",
            'states' => []
        ],
        'PF' => [
            'name' => "French Polynesia",
            'states' => []
        ],
        'PG' => [
            'name' => "Papua New Guinea",
            'states' => []
        ],
        'PH' => [
            'name' => "Philippines",
            'states' => []
        ],
        'PK' => [
            'name' => "Pakistan",
            'states' => []
        ],
        'PL' => [
            'name' => "Poland",
            'states' => []
        ],
        'PM' => [
            'name' => "Saint Pierre and Miquelon",
            'states' => []
        ],
        'PN' => [
            'name' => "Pitcairn",
            'states' => []
        ],
        'PR' => [
            'name' => "Puerto Rico",
            'states' => []
        ],
        'PS' => [
            'name' => "Palestinian Territory, Occupied",
            'states' => []
        ],
        'PT' => [
            'name' => "Portugal",
            'states' => []
        ],
        'PW' => [
            'name' => "Palau",
            'states' => []
        ],
        'PY' => [
            'name' => "Paraguay",
            'states' => []
        ],
        'QA' => [
            'name' => "Qatar",
            'states' => []
        ],
        'RE' => [
            'name' => "Reunion",
            'states' => []
        ],
        'RO' => [
            'name' => "Romania",
            'states' => []
        ],
        'RS' => [
            'name' => "Serbia",
            'states' => []
        ],
        'RU' => [
            'name' => "Russian Federation",
            'states' => []
        ],
        'RW' => [
            'name' => "Rwanda",
            'states' => []
        ],
        'SA' => [
            'name' => "Saudi Arabia",
            'states' => []
        ],
        'SB' => [
            'name' => "Solomon Islands",
            'states' => []
        ],
        'SC' => [
            'name' => "Seychelles",
            'states' => []
        ],
        'SD' => [
            'name' => "Sudan",
            'states' => []
        ],
        'SE' => [
            'name' => "Sweden",
            'states' => []
        ],
        'SG' => [
            'name' => "Singapore",
            'states' => []
        ],
        'SH' => [
            'name' => "Saint Helena",
            'states' => []
        ],
        'SI' => [
            'name' => "Slovenia",
            'states' => []
        ],
        'SJ' => [
            'name' => "Svalbard and Jan Mayen",
            'states' => []
        ],
        'SK' => [
            'name' => "Slovakia",
            'states' => []
        ],
        'SL' => [
            'name' => "Sierra Leone",
            'states' => []
        ],
        'SM' => [
            'name' => "San Marino",
            'states' => []
        ],
        'SN' => [
            'name' => "Senegal",
            'states' => []
        ],
        'SO' => [
            'name' => "Somalia",
            'states' => []
        ],
        'SR' => [
            'name' => "Suriname",
            'states' => []
        ],
        'ST' => [
            'name' => "Sao Tome and Principe",
            'states' => []
        ],
        'SV' => [
            'name' => "El Salvador",
            'states' => []
        ],
        'SY' => [
            'name' => "Syrian Arab Republic",
            'states' => []
        ],
        'SZ' => [
            'name' => "Swaziland",
            'states' => []
        ],
        'TC' => [
            'name' => "Turks and Caicos Islands",
            'states' => []
        ],
        'TD' => [
            'name' => "Chad",
            'states' => []
        ],
        'TF' => [
            'name' => "French Southern Territories",
            'states' => []
        ],
        'TG' => [
            'name' => "Togo",
            'states' => []
        ],
        'TH' => [
            'name' => "Thailand",
            'states' => []
        ],
        'TJ' => [
            'name' => "Tajikistan",
            'states' => []
        ],
        'TK' => [
            'name' => "Tokelau",
            'states' => []
        ],
        'TL' => [
            'name' => "Timor-Leste",
            'states' => []
        ],
        'TM' => [
            'name' => "Turkmenistan",
            'states' => []
        ],
        'TN' => [
            'name' => "Tunisia",
            'states' => []
        ],
        'TO' => [
            'name' => "Tonga",
            'states' => []
        ],
        'TR' => [
            'name' => "Turkey",
            'states' => []
        ],
        'TT' => [
            'name' => "Trinidad and Tobago",
            'states' => []
        ],
        'TV' => [
            'name' => "Tuvalu",
            'states' => []
        ],
        'TW' => [
            'name' => "Taiwan",
            'states' => []
        ],
        'TZ' => [
            'name' => "Tanzania",
            'states' => []
        ],
        'UA' => [
            'name' => "Ukraine",
            'states' => []
        ],
        'UG' => [
            'name' => "Uganda",
            'states' => []
        ],
        'UM' => [
            'name' => "United States Minor Outlying Islands",
            'states' => []
        ],
        'US' => [
            'name' => "United States",
            'states' => [
                'Armed Forces Americas (except Canada)' => "AA",
                'Armed Forces' => "AE",
                'Alaska' => "AK",
                'Alabama' => "AL",
                'Armed Forces Pacific' => "AP",
                'Arkansas' => "AR",
                'American Samoa' => "AS",
                'Arizona' => "AZ",
                'California' => "CA",
                'Colorado' => "CO",
                'Connecticut' => "CT",
                'District of Columbia' => "DC",
                'Delaware' => "DE",
                'Florida' => "FL",
                'Federated States of Micronesia' => "FM",
                'Georgia' => "GA",
                'Guam' => "GU",
                'Hawaii' => "HI",
                'Iowa' => "IA",
                'Idaho' => "ID",
                'Illinois' => "IL",
                'Indiana' => "IN",
                'Kansas' => "KS",
                'Kentucky' => "KY",
                'Louisiana' => "LA",
                'Massachusetts' => "MA",
                'Maryland' => "MD",
                'Maine' => "ME",
                'Marshall Islands' => "MH",
                'Michigan' => "MI",
                'Minnesota' => "MN",
                'Missouri' => "MO",
                'Northern Mariana Islands' => "MP",
                'Mississippi' => "MS",
                'Montana' => "MT",
                'North Carolina' => "NC",
                'North Dakota' => "ND",
                'Nebraska' => "NE",
                'New Hampshire' => "NH",
                'New Jersey' => "NJ",
                'New Mexico' => "NM",
                'Nevada' => "NV",
                'New York' => "NY",
                'Ohio' => "OH",
                'Oklahoma' => "OK",
                'Oregon' => "OR",
                'Pennsylvania' => "PA",
                'Puerto Rico' => "PR",
                'Palau' => "PW",
                'Rhode Island' => "RI",
                'South Carolina' => "SC",
                'South Dakota' => "SD",
                'Tennessee' => "TN",
                'Texas' => "TX",
                'Utah' => "UT",
                'Virginia' => "VA",
                'Virgin Islands' => "VI",
                'Vermont' => "VT",
                'Washington' => "WA",
                'Wisconsin' => "WI",
                'West Virginia' => "WV",
                'Wyoming' => "WY",
            ]
        ],
        'UY' => [
            'name' => "Uruguay",
            'states' => []
        ],
        'UZ' => [
            'name' => "Uzbekistan",
            'states' => []
        ],
        'VA' => [
            'name' => "Vatican City",
            'states' => []
        ],
        'VC' => [
            'name' => "Saint Vincent and the Grenadines",
            'states' => []
        ],
        'VE' => [
            'name' => "Venezuela",
            'states' => []
        ],
        'VG' => [
            'name' => "Virgin Islands, British",
            'states' => []
        ],
        'VI' => [
            'name' => "Virgin Islands, U.S.",
            'states' => []
        ],
        'VN' => [
            'name' => "Vietnam",
            'states' => []
        ],
        'VU' => [
            'name' => "Vanuatu",
            'states' => []
        ],
        'WF' => [
            'name' => "Wallis and Futuna",
            'states' => []
        ],
        'WS' => [
            'name' => "Samoa",
            'states' => []
        ],
        'YE' => [
            'name' => "Yemen",
            'states' => []
        ],
        'YT' => [
            'name' => "Mayotte",
            'states' => []
        ],
        'ZA' => [
            'name' => "South Africa",
            'states' => []
        ],
        'ZM' => [
            'name' => "Zambia",
            'states' => []
        ],
        'ZW' => [
            'name' => "Zimbabwe",
            'states' => []
        ],
    ];

    public static $currencies = array( 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYN', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SPL*', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW', 'ZWD' );

    public function __construct() {
        $this->requestUrl = \IPS\Http\Url::external( $this->apiUrl );
    }

    /**
     * Get products list from prtinful store
     * 
     * @param integer|NULL $offset Result set offset, if null offset will be set as 0
     * @param integer|NULL $limit Number of items per page, if null limit will be set as 20
     * 
     * @return Array
     */
    public function getProducts($offset = NULL, $limit = NULL, $search = NULL) {

        $this->requestUrl = $this->requestUrl->setPath('/store/products');

        if( !empty( $offset ) && \is_integer( $offset ) ) {
            $this->requestUrl = $this->requestUrl->setQueryString('offset', $offset);
        }

        if( !empty( $limit ) && \is_integer( $limit ) ) {
            $this->requestUrl = $this->requestUrl->setQueryString('limit', $limit);
        }

        if( !empty( $limit ) && \is_integer( $limit ) ) {
            $this->requestUrl = $this->requestUrl->setQueryString('search', $search);
        }

        return $this->requestUrl->request()->setHeaders([
			'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
		])->get()->decodeJson();
    }

    public function apiKey() {

        return $this->requestUrl->setPath('/store')->request()->setHeaders([
			'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
		])->get()->decodeJson()['code'] === 200;
    }

    public function store() {
        return $this->requestUrl->setPath('/store')->request()->setHeaders([
			'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
		])->get()->decodeJson()['result'];
    }

    public function isImported( $id ) {
        try {
            return !!\IPS\Db::i()->select('id', 'printfulintegration_products', ['printful_id=?', $id])->first();
        } catch( \UnderflowException $e ) {}

        return FALSE;
    }

    public function isImportedByCommerceId( $id ) {
        try {
            return !!\IPS\Db::i()->select('printful_id', 'printfulintegration_products', ['commerce_id=?', $id])->first();
        } catch( \UnderflowException $e ) {}

        return FALSE;
    }

    public function getProduct( $id ) {

        return $this->requestUrl->setPath( '/store/products/' . $id )->request()->setHeaders([
			'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
		])->get()->decodeJson()['result'];
    }

    public function shippingRate( $recipient, $items, $currency = 'EUR' ) {

        return $this->requestUrl->setPath('/shipping/rates')->request()->setHeaders([
			'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
		])->post(json_encode([
            'recipient' => $recipient,
            'items' => $items,
            'currency' => $currency,
        ]))->decodeJson()['result'];
    }

    public function variantId( $sync_id ) {
        return $this->requestUrl->setPath('/store/variants/' . $sync_id )->request()->setHeaders([
            'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
        ])->get()->decodeJson()['result']['product']['variant_id'];
    }

    public function createOrder( $recipient, $items, $invoiceId, $shipping, $currency ) {
        return $this->requestUrl->setPath('/orders')->request()->setHeaders([
            'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
        ])->post(json_encode(array(
            'external_id' => $invoiceId,
            'shipping' => $shipping,
            'recipient' => $recipient,
            'items' => $items,
            'retail_costs' => array(
                'currency' => $currency,
            )
        )))->decodeJson()['result'];
    }

    public function confirmOrder( $id ) {
        return $this->requestUrl->setPath("/orders/${id}/confirm")->request()->setHeaders([
            'Authorization' => "Basic " . \base64_encode( \IPS\Settings::i()->printful_api_key )
        ])->post()->decodeJson()['result'];
    }

}

