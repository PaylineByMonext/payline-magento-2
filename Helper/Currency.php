<?php

namespace Monext\Payline\Helper;

/**
 * Class from Magento 1 module Monext Payline : Monext_Payline_Helper_Data
 */
class Currency
{
    /**
     * Currency codes (ISO 4217) supported by Payline
     * @var array
     */
    protected $_supportedCurrencyCodes       = array(
        'ALL' => '8', // Lek
        'DZD' => '12', // Algerian Dinar
        'ARS' => '32', // Argentine Peso
        'AUD' => '36', // Australian Dollar
        'BSD' => '44', // Bahamian Dollar
        'BHD' => '48', // Bahraini Dinar
        'BDT' => '50', // Taka
        'AMD' => '51', // Armenian Dram
        'BBD' => '52', // Barbados Dollar
        'BMD' => '60', // Bermudian Dollar (customarily known as Bermuda Dollar)
        'BTN' => '64', // Ngultrum
        'BOB' => '68', // Boliviano
        'BWP' => '72', // Pula
        'BZD' => '84', // Belize Dollar
        'SBD' => '90', // Solomon Islands Dollar
        'BND' => '96', // Brunei Dollar
        'MMK' => '104', // Kyat
        'BIF' => '108', // Burundi Franc
        'KHR' => '116', // Riel
        'CAD' => '124', // Canadian Dollar
        'CVE' => '132', // Cape Verde Escudo
        'KYD' => '136', // Cayman Islands Dollar
        'LKR' => '144', // Sri Lanka Rupee
        'CLP' => '152', // Chilean Peso
        'CNY' => '156', // Yuan Renminbi
        'COP' => '170', // Colombian Peso
        'KMF' => '174', // Comoro Franc
        'CRC' => '188', // Costa Rican Colon
        'HRK' => '191', // Croatian Kuna
        'CUP' => '192', // Cuban Peso
        'CYP' => '196', // Cyprus Pound
        'CZK' => '203', // Czech Koruna
        'DKK' => '208', // Danish Krone
        'DOP' => '214', // Dominican Peso
        'SVC' => '222', // El Salvador Colon
        'ETB' => '230', // Ethiopian Birr
        'ERN' => '232', // Nakfa
        'EEK' => '233', // Kroon
        'FKP' => '238', // Falkland Islands Pound
        'FJD' => '242', // Fiji Dollar
        'DJF' => '262', // Djibouti Franc
        'GMD' => '270', // Dalasi
        'GHC' => '288', // Cedi
        'GIP' => '292', // Gibraltar Pound
        'GTQ' => '320', // Quetzal
        'GNF' => '324', // Guinea Franc
        'GYD' => '328', // Guyana Dollar
        'HTG' => '332', // Gourde
        'HNL' => '340', // Lempira
        'HKD' => '344', // Hong Kong Dollar
        'HUF' => '348', // Forint
        'ISK' => '352', // Iceland Krona
        'INR' => '356', // Indian Rupee
        'IDR' => '360', // Rupiah
        'IRR' => '364', // Iranian Rial
        'IQD' => '368', // Iraqi Dinar
        'ILS' => '376', // New Israeli Sheqel
        'JMD' => '388', // Jamaican Dollar
        'JPY' => '392', // Yen
        'KZT' => '398', // Tenge
        'JOD' => '400', // Jordanian Dinar
        'KES' => '404', // Kenyan Shilling
        'KPW' => '408', // North Korean Won
        'KRW' => '410', // Won
        'KWD' => '414', // Kuwaiti Dinar
        'KGS' => '417', // Som
        'LAK' => '418', // Kip
        'LBP' => '422', // Lebanese Pound
        'LSL' => '426', // Loti
        'LVL' => '428', // Latvian Lats
        'LRD' => '430', // Liberian Dollar
        'LYD' => '434', // Libyan Dinar
        'LTL' => '440', // Lithuanian Litas
        'MOP' => '446', // Pataca
        'MWK' => '454', // Kwacha
        'MYR' => '458', // Malaysian Ringgit
        'MVR' => '462', // Rufiyaa
        'MTL' => '470', // Maltese Lira
        'MRO' => '478', // Ouguiya
        'MUR' => '480', // Mauritius Rupee
        'MXN' => '484', // Mexican Peso
        'MNT' => '496', // Tugrik
        'MDL' => '498', // Moldovan Leu
        'MAD' => '504', // Moroccan Dirham
        'OMR' => '512', // Rial Omani
        'NAD' => '516', // Namibian Dollar
        'NPR' => '524', // Nepalese Rupee
        'ANG' => '532', // Netherlands Antillian Guilder
        'AWG' => '533', // Aruban Guilder
        'VUV' => '548', // Vatu
        'NZD' => '554', // New Zealand Dollar
        'NIO' => '558', // Cordoba Oro
        'NGN' => '566', // Naira
        'NOK' => '578', // Norwegian Krone
        'PKR' => '586', // Pakistan Rupee
        'PAB' => '590', // Balboa
        'PGK' => '598', // Kina
        'PYG' => '600', // Guarani
        'PEN' => '604', // Nuevo Sol
        'PHP' => '608', // Philippine Peso
        'GWP' => '624', // Guinea-Bissau Peso
        'QAR' => '634', // Qatari Rial
        'ROL' => '642', // Old Leu
        'RUB' => '643', // Russian Ruble
        'RWF' => '646', // Rwanda Franc
        'SHP' => '654', // Saint Helena Pound
        'STD' => '678', // Dobra
        'SAR' => '682', // Saudi Riyal
        'SCR' => '690', // Seychelles Rupee
        'SLL' => '694', // Leone
        'SGD' => '702', // Singapore Dollar
        'SKK' => '703', // Slovak Koruna
        'VND' => '704', // Dong
        'SIT' => '705', // Tolar
        'SOS' => '706', // Somali Shilling
        'ZAR' => '710', // Rand
        'ZWD' => '716', // Zimbabwe Dollar
        'SZL' => '748', // Lilangeni
        'SEK' => '752', // Swedish Krona
        'CHF' => '756', // Swiss Franc
        'SYP' => '760', // Syrian Pound
        'THB' => '764', // Baht
        'TOP' => '776', // Pa'anga
        'TTD' => '780', // Trinidad and Tobago Dollar
        'AED' => '784', // UAE Dirham
        'TND' => '788', // Tunisian Dinar
        'TMM' => '795', // Manat
        'UGX' => '800', // Uganda Shilling
        'MKD' => '807', // Denar
        'EGP' => '818', // Egyptian Pound
        'GBP' => '826', // Pound Sterling
        'TZS' => '834', // Tanzanian Shilling
        'USD' => '840', // US Dollar
        'UYU' => '858', // Peso Uruguayo
        'UZS' => '860', // Uzbekistan Sum
        'VEB' => '862', // Bolivar
        'WST' => '882', // Tala
        'YER' => '886', // Yemeni Rial
        'ZMK' => '894', // Kwacha
        'TWD' => '901', // New Taiwan Dollar
        'SDG' => '938', // Sudanese Dinar
        'UYI' => '940', // Uruguay Peso en Unidades Indexadas
        'RSD' => '941', // Serbian Dinar
        'MZN' => '943', // Metical
        'AZN' => '944', // Azerbaijanian Manat
        'RON' => '946', // New Leu
        'CHE' => '947', // WIR Euro
        'CHW' => '948', // WIR Franc
        'TRY' => '949', // New Turkish Lira
        'XAF' => '950', // CFA Franc BEAC
        'XCD' => '951', // East Caribbean Dollar
        'XOF' => '952', // CFA Franc BCEAO
        'XPF' => '953', // CFP Franc
        'XBA' => '955', // Bond Markets Units European Composite Unit (EURCO)
        'XBB' => '956', // European Monetary Unit (E.M.U.-6)
        'XBC' => '957', // European Unit of Account 9(E.U.A.-9)
        'XBD' => '958', // European Unit of Account 17(E.U.A.-17)
        'XAU' => '959', // Gold
        'XDR' => '960', // SDR
        'XAG' => '961', // Silver
        'XPT' => '962', // Platinum
        'XTS' => '963', // Codes specifically reserved for testing purposes
        'XPD' => '964', // Palladium
        'SRD' => '968', // Surinam Dollar
        'MGA' => '969', // Malagascy Ariary
        'COU' => '970', // Unidad de Valor Real
        'AFN' => '971', // Afghani
        'TJS' => '972', // Somoni
        'AOA' => '973', // Kwanza
        'BYR' => '974', // Belarussian Ruble
        'BGN' => '975', // Bulgarian Lev
        'CDF' => '976', // Franc Congolais
        'BAM' => '977', // Convertible Marks
        'EUR' => '978', // Euro
        'MXV' => '979', // Mexican Unidad de Inversion (UID)
        'UAH' => '980', // Hryvnia
        'GEL' => '981', // Lari
        'BOV' => '984', // Mvdol
        'PLN' => '985', // Zloty
        'BRL' => '986', // Brazilian Real
        'CLF' => '990', // Unidades de formento
        'USN' => '997', // (Next day)
        'USS' => '998', // (Same day)
        'XXX' => '999' // The codes assigned for transactions where no currency is involved
    );
    
    /**
     * Check whether specified currency code is supported
     * @param string $code
     * @return bool
     */
    private function isCurrencyCodeSupported($code)
    {
        return array_key_exists($code, $this->_supportedCurrencyCodes);
    }

    /**
     * Returns the numeric currency code of the chosen currency
     * @param string $alphaCurrencyCode
     * @return string
     */
    public function getNumericCurrencyCode($alphaCurrencyCode)
    {
        if ($this->isCurrencyCodeSupported($alphaCurrencyCode)) {
            return $this->_supportedCurrencyCodes[$alphaCurrencyCode];
        } else {
            return '0000';
        }
    }
}

